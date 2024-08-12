<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Helpers\SshKeyPair;
use App\Models\YnhServer;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

Route::get('catalog', function () {
    \Illuminate\Support\Facades\Auth::login(\App\User::where('email', config('towerify.admin.email'))->firstOrFail());
    $apps = \App\Models\Product::orderBy('name')
        ->get()
        ->map(function (\App\Models\Product $product) {
            return \App\Helpers\ProductOrProductVariant::create($product);
        })
        ->filter(function (\App\Helpers\ProductOrProductVariant $product) {
            return $product->isApplication();
        })
        ->map(function (\App\Helpers\ProductOrProductVariant $product) {
            return [
                'name' => $product->getName(),
                'description' => $product->product()->description,
                'image' => $product->getThumbnailUrl(),
                'status' => $product->product()->state->value(),
            ];
        })
        ->values();
    return new JsonResponse($apps, 200, ['Access-Control-Allow-Origin' => '*']);
});

Route::get('setup/token', function (\Illuminate\Http\Request $request) {

    /** @var User $user */
    $user = $request->user();

    if (!$user->canManageServers()) {
        return new JsonResponse([
            'status' => 'failure',
            'message' => 'User must be able to manage servers.',
            'user' => $user,
        ], 200, ['Access-Control-Allow-Origin' => '*']);
    }

    // Upon first connection, generate a user-specific 'system' token.
    // This token will enable the user to configure servers using curl.
    /** @var \Laravel\Sanctum\PersonalAccessToken $token */
    $token = $user->tokens->where('name', 'system')->first();
    $plainTextToken = null;

    if (!$token) {
        $token = $user->createToken('system', ['setup.sh']);
        if (!$token) {
            return new JsonResponse([
                'status' => 'failure',
                'message' => 'The token could not be generated.',
                'user' => $user,
            ], 200, ['Access-Control-Allow-Origin' => '*']);
        }
        $plainTextToken = $token->plainTextToken;
        $token = $token?->accessToken;
    }
    if ($token->cant('setup.sh')) {
        $token->abilities = array_merge($token->abilities, ['setup.sh']);
        $token->save();
    }
    if (!$plainTextToken) {
        return new JsonResponse([
            'status' => 'success',
            'message' => 'A \'system\' token has already been generated. Please, reuse it.',
            'token' => null,
        ], 200, ['Access-Control-Allow-Origin' => '*']);
    }
    return new JsonResponse([
        'status' => 'success',
        'message' => 'The \'system\' token has been generated.',
        'token' => $plainTextToken,
    ], 200, ['Access-Control-Allow-Origin' => '*']);
})->middleware('auth');

Route::get('setup/script', function (\Illuminate\Http\Request $request) {

    $token = $request->input('api_token');

    if (!$token) {
        return response('Missing token', 403)
            ->header('Content-Type', 'text/plain');
    }

    /** @var \Laravel\Sanctum\PersonalAccessToken $token */
    $token = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

    if (!$token || $token->cant('setup.sh')) {
        return response('Invalid token', 403)
            ->header('Content-Type', 'text/plain');
    }

    /** @var User $user */
    $user = $token->tokenable;
    Auth::login($user);

    $ip = $request->input('server_ip');

    if (!$ip) {
        return response('Invalid IP address', 500)
            ->header('Content-Type', 'text/plain');
    }

    $server = \App\Models\YnhServer::where('ip_address', $ip)->first();

    if (!$server) {
        $server = \App\Models\YnhServer::where('ip_address_v6', $ip)->first();
        if (!$server) {
            $name = $request->input('server_name', "cURL/{$ip}");
            $server = YnhServer::create([
                'name' => $name,
                'ip_address' => $ip,
                'user_id' => $user->id,
                'secret' => Str::random(30),
                'is_ready' => false,
                'is_frozen' => true,
                'added_with_curl' => true,
            ]);
            $server->save();
        }
    }
    if ($server->is_ready) {
        return response('The server is already configured', 500)
            ->header('Content-Type', 'text/plain');
    }
    if (!$server->secret) {
        $server->secret = Str::random(30);
    }
    if (!$server->ssh_port) {
        $server->ssh_port = 22;
    }
    if (!$server->ssh_username) {
        $server->ssh_username = 'twr_admin';
    }
    if (!$server->ssh_public_key || !$server->ssh_private_key) {

        $keys = new SshKeyPair();
        $keys->init();

        $server->ssh_public_key = $keys->publicKey();
        $server->ssh_private_key = $keys->privateKey();
    }

    $server->is_ready = true;
    $server->is_frozen = false;
    $server->added_with_curl = true;
    $server->save();

    // Check if the current server belongs to the current user
    $installScript = <<<EOT
#!/bin/bash

if [ ! -f /etc/osquery/osquery.conf ]; then

    wget https://pkg.osquery.io/deb/osquery_5.11.0-1.linux_amd64.deb
    apt install ./osquery_5.11.0-1.linux_amd64.deb
    rm osquery_5.11.0-1.linux_amd64.deb
    osqueryctl start osqueryd

    apt install git -y
    
    git clone https://github.com/palantir/osquery-configuration.git
    cp osquery-configuration/Classic/Servers/Linux/* /etc/osquery/
    cp -r osquery-configuration/Classic/Servers/Linux/packs/ /etc/osquery/
    osqueryctl restart osqueryd
    rm -rf osquery-configuration/
    
    # apt remove git -y
    # apt purge git -y
fi

apt install tmux -y
sudo -H -u root bash -c 'tmux kill-ses -t forward-results'
sudo -H -u root bash -c 'tmux kill-ses -t forward-snapshots'

if [ -f /etc/osquery/forward-results.sh ]; then
  rm -f /etc/osquery/forward-results.sh
fi
if [ -f /etc/osquery/forward-snapshots.sh ]; then
  rm -f /etc/osquery/forward-snapshots.sh
fi

cat /etc/osquery/osquery.conf | \
  jq $'del(.schedule.socket_events)' | \
  jq $'del(.schedule.network_interfaces_snapshot)' | \
  jq $'del(.schedule.process_events)' | \
  jq $'.schedule.packages_available_snapshot += {query:"SELECT name, version, source FROM deb_packages;",interval:86400,snapshot:true}' | \
  jq $'.schedule.memory_available_snapshot += {query:"select printf(\'%.2f\',((memory_total - memory_available) * 1.0)/1073741824) as used_space_gb, printf(\'%.2f\',(1.0 * memory_available / 1073741824)) as space_left_gb, printf(\'%.2f\',(1.0 * memory_total / 1073741824)) as total_space_gb, printf(\'%.2f\',(((memory_total - memory_available) * 1.0)/1073741824)/(1.0 * memory_total / 1073741824)) * 100 as \'%_used\', printf(\'%.2f\',(1.0 * memory_available / 1073741824)/(1.0 * memory_total / 1073741824)) * 100 as \'%_available\' from memory_info;",interval:300,snapshot:true}' | \
  jq $'.schedule.disk_available_snapshot += {query:"select printf(\'%.2f\',((blocks - blocks_available * 1.0) * blocks_size)/1073741824) as used_space_gb, printf(\'%.2f\',(1.0 * blocks_available * blocks_size / 1073741824)) as space_left_gb, printf(\'%.2f\',(1.0 * blocks * blocks_size / 1073741824)) as total_space_gb, printf(\'%.2f\',(((blocks - blocks_available * 1.0) * blocks_size)/1073741824)/(1.0 * blocks * blocks_size / 1073741824)) * 100 as \'%_used\', printf(\'%.2f\',(1.0 * blocks_available * blocks_size / 1073741824)/(1.0 * blocks * blocks_size / 1073741824)) * 100 as \'%_available\' from mounts where path = \'/\';",interval:300,snapshot:true}' \
  >/etc/osquery/osquery2.conf

mv -f /etc/osquery/osquery2.conf /etc/osquery/osquery.conf
osqueryctl restart osqueryd

cat <(fgrep -i -v 'rm /var/log/osquery/osqueryd.results.log /var/log/osquery/osqueryd.snapshots.log' <(crontab -l)) <(echo '0 1 * * 0 rm /var/log/osquery/osqueryd.results.log /var/log/osquery/osqueryd.snapshots.log') | crontab -

TVAR1=$(cat <<SETVAR
tail -F /var/log/osquery/osqueryd.results.log | jq -c 'select(.columns == null or .columns.cmdline == null or (.columns.cmdline | contains("tail -F /var/log/osquery/osqueryd.results.log") | not)) | {ip:"{$server->ip_address}",secret:"{$server->secret}",events:[.]}' | while read -r LINE; do curl -s -H "Content-Type: application/json" -XPOST https://app.towerify.io/metrics --data-binary "\\\$LINE"; done >/dev/null
SETVAR
)
sudo -H -u root bash -c 'tmux new-session -A -d -s forward-results'
tmux send-keys -t forward-results "\$TVAR1" C-m

TVAR2=$(cat <<SETVAR
tail -F /var/log/osquery/osqueryd.snapshots.log | jq -c 'select(.columns == null or .columns.cmdline == null or (.columns.cmdline | contains("tail -F /var/log/osquery/osqueryd.snapshots.log") | not)) | {ip:"{$server->ip_address}",secret:"{$server->secret}",events:[.]}' | while read -r LINE; do curl -s -H "Content-Type: application/json" -XPOST https://app.towerify.io/metrics --data-binary "\\\$LINE"; done >/dev/null
SETVAR
)
sudo -H -u root bash -c 'tmux new-session -A -d -s forward-snapshots'
tmux send-keys -t forward-snapshots "\$TVAR2" C-m

EOT;

    // 1. In the browser, go to "https://towerify.io/setup/token" to get a user-specific cURL token.
    // 2. On the server, run "bash <(curl -s https://towerify.io/setup/script?api_token=<token>&server_ip=<ip>&server_name=<name>)"
    return response($installScript, 200)
        ->header('Content-Type', 'text/plain');
});

Route::post('metrics', function (\Illuminate\Http\Request $request) {
    try {

        // {ip:"<ip address>", secret:"<secret>", events:[<events>]}
        $payload = $request->all();
        $validator = Validator::make(
            $payload,
            [
                'ip' => 'required|ip',
                'secret' => 'required|string|min:1|max:50',
                // 'events' => 'required|array|min:1|max:500',
                // 'events.*.name' => 'required|string',
                // 'events.*.hostIdentifier' => 'required|string',
                // 'events.*.calendarTime' => 'required|string',
                // 'events.*.unixTime' => 'required|integer',
                // 'events.*.epoch' => 'required|integer',
                // 'events.*.counter' => 'required|integer',
                // 'events.*.numerics' => 'required|boolean',
                // 'events.*.columns' => 'required|array|min:1',
                // 'events.*.action' => 'required|string',
            ]
        );
        if ($validator->fails()) {
            return new JsonResponse([
                'status' => 'failure',
                'message' => 'payload validation failed',
                'payload' => $payload,
            ], 200, ['Access-Control-Allow-Origin' => '*']);
        }

        $server = \App\Models\YnhServer::where('ip_address', $request->input('ip'))
            ->where('secret', $request->input('secret'))
            ->first();

        if (!$server) {
            return new JsonResponse([
                'status' => 'failure',
                'message' => 'server not found',
                'payload' => $payload,
            ], 200, ['Access-Control-Allow-Origin' => '*']);
        }

        $nbEventsAdded = $server->addOsqueryEvents($request->input('events'));

        // \Illuminate\Support\Facades\Log::debug('Metrics - nb_events_added=' . $nbEventsAdded);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error($e);
        return new JsonResponse([
            'status' => 'failure',
            'message' => $e->getMessage(),
            'payload' => $payload,
        ], 200, ['Access-Control-Allow-Origin' => '*']);
    }
    return new JsonResponse(['status' => 'success'], 200, ['Access-Control-Allow-Origin' => '*']);
});

Route::get('', function () {
    if (\Illuminate\Support\Facades\Auth::user()) {
        return redirect('/home');
    }
    return redirect('/shop/index');
});

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::user()) {
        return redirect('/home');
    }
    return redirect('/shop/index');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::post('/reset-password', function () {
    $email = \Illuminate\Support\Facades\Auth::user()->email;
    return view('auth.passwords.email', compact('email'));
})->middleware('auth')->name('reset-password');

Route::group(['prefix' => 'ynh', 'as' => 'ynh.'], function () {
    Route::group(['prefix' => 'servers', 'as' => 'servers.'], function () {
        Route::get('', 'YnhServerController@create')->name('create');
        Route::delete('{server}', 'YnhServerController@delete')->name('delete');
        Route::get('{server}/edit', 'YnhServerController@index')->name('edit');
        Route::post('{server}/edit', 'YnhServerController@index')->name('edit');
        Route::post('{server}/pull-server-infos', 'YnhServerController@pullServerInfos')->name('pull-server-infos');
        Route::post('{server}/test-ssh-connection', 'YnhServerController@testSshConnection')->name('test-ssh-connection');
        Route::post('{server}/configure', 'YnhServerController@configure')->name('configure');
        Route::post('{server}/install-osquery', 'YnhServerController@installOsquery')->name('install-osquery');
        Route::post('{server}/backup', 'YnhServerController@createBackup')->name('create-backup');
        Route::get('{server}/backup/{backup}', 'YnhServerController@downloadBackup')->name('download-backup');
        Route::post('{server}/execute-shell-command', 'YnhServerController@executeShellCommand')->name('execute-shell-command');
        Route::delete('{server}/apps/{application}', 'YnhServerController@uninstallApp')->name('uninstall-app');
        Route::post('{server}/orders/{ynhOrder}', 'YnhServerController@installApp')->name('install-app');
        Route::post('{server}/users/{ynhUser}/permissions/{perm}', 'YnhServerController@addUserPermission')->name('add-user-permission');
        Route::delete('{server}/users/{ynhUser}/permissions/{perm}', 'YnhServerController@removeUserPermission')->name('remove-user-permission');
        Route::post('{server}/twr-users/{user}/permissions/{perm}', 'YnhServerController@addTwrUserPermission')->name('add-twr-user-permission');
    });
    Route::group(['prefix' => 'invitations', 'as' => 'invitations.'], function () {
        Route::post('create', 'YnhInvitationController@create')->name('create');
    });
});

Route::group(['prefix' => 'shop', 'as' => 'product.'], function () {
    Route::get('index', 'ProductController@index')->name('index');
    Route::get('c/{taxonomyName}/{taxon}', 'ProductController@index')->name('category');
    Route::get('p/{slug}', 'ProductController@show')->name('show');
});

Route::group(['prefix' => 'cart', 'as' => 'cart.'], function () {
    Route::get('show', 'CartController@show')->name('show');
    Route::post('add/{product}', 'CartController@add')->name('add');
    Route::post('adv/{masterProductVariant}', 'CartController@addVariant')->name('add-variant');
    Route::post('update/{cart_item}', 'CartController@update')->name('update');
    Route::post('remove/{cart_item}', 'CartController@remove')->name('remove');
});

Route::group(['prefix' => 'checkout', 'as' => 'checkout.'], function () {
    Route::get('show', 'CheckoutController@show')->name('show');
    Route::post('submit', 'CheckoutController@submit')->name('submit');
});

Route::group(['prefix' => 'payment/eup', 'as' => 'payment.euplatesc.return.'], function () {
    Route::post('frontend', 'EuplatescReturnController@frontend')->name('frontend');
    Route::post('silent', 'EuplatescReturnController@silent')->name('silent');
});

Route::group(['prefix' => 'payment/netopia', 'as' => 'payment.netopia.'], function () {
    Route::post('confirm', 'NetopiaReturnController@confirm')->name('confirm');
    Route::get('return', 'NetopiaReturnController@return')->name('return');
});

Route::group(['prefix' => 'payment/paypal', 'as' => 'payment.paypal.'], function () {
    Route::get('return', 'PaypalReturnController@return')->name('return');
    Route::get('cancel', 'PaypalReturnController@cancel')->name('cancel');
    Route::any('webhook', 'PaypalReturnController@webhook')->name('webhook');
});

Route::group(['prefix' => 'payment/simplepay', 'as' => 'payment.simplepay.'], function () {
    Route::get('return', 'SimplepayReturnController@return')->name('return');
    Route::post('silent', 'SimplepayReturnController@silent')->name('silent');
});

Route::group(['prefix' => 'payment/mollie', 'as' => 'payment.mollie.'], function () {
    Route::get('{paymentId}/return', 'MollieController@return')->name('return');
    Route::post('webhook', 'MollieController@webhook')->name('webhook');
});

Route::group(['prefix' => 'payment/adyen', 'as' => 'payment.adyen.'], function () {
    Route::post('{paymentId}/submit', 'AdyenController@submit')->name('submit');
    Route::post('webhook', 'AdyenController@webhook')->name('webhook');
});

Route::group(['prefix' => 'payment/braintree', 'as' => 'payment.braintree.'], function () {
    Route::post('{paymentId}/submit', 'BraintreeController@submit')->name('submit');
});

Route::group(['prefix' => 'payment/stripe', 'as' => 'payment.stripe.'], function () {
    Route::post('webhook', 'StripeReturnController@webhook');
});