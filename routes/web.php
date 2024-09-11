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
use App\Models\YnhNginxLogs;
use App\Models\YnhServer;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
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
})->middleware('throttle:30,1');

Route::get('/setup/token', function (\Illuminate\Http\Request $request) {

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
})->middleware(['auth', 'throttle:6,1']);

Route::get('/setup/script', function (\Illuminate\Http\Request $request) {

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

    // 1. In the browser, go to "https://app.towerify.io" and login using your user account.
    // 2. In the browser, go to "https://app.towerify.io/setup/token" to get a user-specific cURL token.
    // 3. On the server, run:
    //    3.1 curl -s https://app.towerify.io/setup/script?api_token=<token>&server_ip=<ip>&server_name=<name> >install.sh
    //    3.2 chmod +x install.sh
    //    3.3 ./install.sh
    //    3.4 rm install.sh
    $installScript = \App\Models\YnhOsquery::monitorServer($server);

    return response($installScript, 200)
        ->header('Content-Type', 'text/plain');
})->middleware(['auth', 'throttle:6,1']);

Route::get('/update/{secret}', function (string $secret, \Illuminate\Http\Request $request) {

    $server = \App\Models\YnhServer::where('secret', $secret)->first();

    if (!$server) {
        return response('Unknown server', 500)
            ->header('Content-Type', 'text/plain');
    }

    $installScript = \App\Models\YnhOsquery::monitorServer($server);

    return response($installScript, 200)
        ->header('Content-Type', 'text/plain');
})->middleware('throttle:6,1');

Route::post('/logalert/{secret}', function (string $secret, \Illuminate\Http\Request $request) {

    // See https://github.com/jhuckaby/logalert?tab=readme-ov-file#web-hook-alerts for details
    $payload = $request->all();
    $validator = Validator::make(
        $payload,
        [
            'name' => 'required|string',
            'file' => 'required|string|min:1|max:255',
            'date' => 'required|string',
            'hostname' => 'required|string',
            'lines' => 'required|array|min:1|max:500',
        ]
    );
    if ($validator->fails()) {
        return new JsonResponse([
            'status' => 'failure',
            'message' => 'payload validation failed',
            'payload' => $payload,
        ], 200, ['Access-Control-Allow-Origin' => '*']);
    }
    try {

        $server = \App\Models\YnhServer::where('secret', $secret)->first();

        if (!$server) {
            return new JsonResponse([
                'status' => 'failure',
                'message' => 'server not found',
                'payload' => $payload,
            ], 200, ['Access-Control-Allow-Origin' => '*']);
        }

        $events = collect($request->input('lines'))
            ->map(fn($line) => $line ? json_decode($line, true) : [])
            ->filter(fn($event) => count($event) > 0)
            ->all();
        $nbEventsAdded = $server->addOsqueryEvents($events);

        // \Illuminate\Support\Facades\Log::debug('LogAlert - nb_events_added=' . $nbEventsAdded);

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

Route::get('/logalert/{secret}', function (string $secret, \Illuminate\Http\Request $request) {

    $server = \App\Models\YnhServer::where('secret', $secret)->first();

    if (!$server) {
        return response('Unknown server', 500)
            ->header('Content-Type', 'text/plain');
    }

    $config = json_encode(\App\Models\YnhOsquery::configLogAlert($server));

    return response($config, 200)
        ->header('Content-Type', 'text/plain');
})->middleware('throttle:6,1');

Route::get('/osquery/{secret}', function (string $secret, \Illuminate\Http\Request $request) {

    $server = \App\Models\YnhServer::where('secret', $secret)->first();

    if (!$server) {
        return response('Unknown server', 500)
            ->header('Content-Type', 'text/plain');
    }

    $config = json_encode(\App\Models\YnhOsquery::configOsquery());

    return response($config, 200)
        ->header('Content-Type', 'text/plain');
})->middleware('throttle:6,1');

Route::get('/logparser/{secret}', function (string $secret, \Illuminate\Http\Request $request) {

    $server = \App\Models\YnhServer::where('secret', $secret)->first();

    if (!$server) {
        return response('Unknown server', 500)
            ->header('Content-Type', 'text/plain');
    }

    $config = \App\Models\YnhOsquery::configLogParser($server);

    return response($config, 200)
        ->header('Content-Type', 'text/plain');
})->middleware('throttle:6,1');

Route::post('/logparser/{secret}', function (string $secret, \Illuminate\Http\Request $request) {

    if (!$request->hasFile('data')) {
        return response('Missing attachment', 500)
            ->header('Content-Type', 'text/plain');
    }

    $file = $request->file('data');

    if (!$file->isValid()) {
        return response('Invalid attachment', 500)
            ->header('Content-Type', 'text/plain');
    }

    $server = \App\Models\YnhServer::where('secret', $secret)->first();

    if (!$server) {
        return response('Unknown server', 500)
            ->header('Content-Type', 'text/plain');
    }

    $logs = collect(gzfile($file->getRealPath()))
        ->map(fn(string $line) => Str::of(trim($line))->split('/\s+/'))
        ->filter(fn(Collection $lines) => $lines->count() === 3 && filter_var($lines->last(), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6))
        ->map(fn(Collection $lines) => [
            'count' => $lines->first(),
            'service' => $lines->get(1),
            'ip' => YnhServer::expandIp($lines->last()),
        ]);

    if ($logs->isEmpty()) {
        return response('ok (empty file)', 200)
            ->header('Content-Type', 'text/plain');
    }

    $toId = $server->id;
    $toIp = $server->ip();
    $fromId = [];

    foreach ($logs as $countServiceAndIp) {

        $count = $countServiceAndIp['count'];
        $service = $countServiceAndIp['service'];
        $fromIp = $countServiceAndIp['ip'];

        if (!array_key_exists($fromIp, $fromId)) {
            $fromServer = YnhServer::where('ip_address', $fromIp)->first();
            if ($fromServer) {
                $fromId[$fromIp] = $fromServer->id;
            } else {
                $fromServer = YnhServer::where('ip_address_v6', $fromIp)->first();
                if ($fromServer) {
                    $fromId[$fromIp] = $fromServer->id;
                }
            }
        }

        YnhNginxLogs::updateOrCreate([
            'from_ip_address' => $fromIp,
            'to_ynh_server_id' => $toId,
            'service' => $service,
        ], [
            'from_ynh_server_id' => $fromId[$fromIp] ?? null,
            'to_ynh_server_id' => $toId,
            'from_ip_address' => $fromIp,
            'to_ip_address' => $toIp,
            'service' => $service,
            'weight' => $count,
            'updated' => true,
        ]);
    }
    DB::transaction(function () use ($server) {
        YnhNginxLogs::where('to_ynh_server_id', $server->id)
            ->where('updated', false)
            ->delete();
        YnhNginxLogs::where('to_ynh_server_id', $server->id)
            ->update(['updated' => false]);
    });
    return response("ok ({$logs->count()} rows in file)", 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('', function () {
    if (\Illuminate\Support\Facades\Auth::user()) {
        return redirect('/home');
    }
    return redirect('/the-cyber-brief');
});

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::user()) {
        return redirect('/home');
    }
    return redirect('/the-cyber-brief');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/the-cyber-brief', 'TheCyberBriefController@index')->name('the-cyber-brief');

Route::post('/reset-password', function () {
    $email = \Illuminate\Support\Facades\Auth::user()->email;
    return view('auth.passwords.email', compact('email'));
})->middleware('auth')->name('reset-password');

Route::get('/notifications/{notification}/dismiss', function (\Illuminate\Notifications\DatabaseNotification $notification, \Illuminate\Http\Request $request) {
    \Illuminate\Notifications\DatabaseNotification::query()
        ->whereNull('read_at')
        ->whereJsonContains('data->group', $notification->data['group'])
        ->get()
        ->each(fn($notif) => $notif->markAsRead());
})->middleware('auth');

Route::group(['prefix' => 'ynh', 'as' => 'ynh.'], function () {
    Route::group(['prefix' => 'servers', 'as' => 'servers.'], function () {
        Route::get('', 'YnhServerController@create')->name('create');
        Route::delete('{server}', 'YnhServerController@delete')->name('delete');
        Route::get('{server}/edit', 'YnhServerController@index')->name('edit');
        Route::post('{server}/edit', 'YnhServerController@index')->name('edit');
        Route::post('{server}/pull-server-infos', 'YnhServerController@pullServerInfos')->name('pull-server-infos');
        Route::post('{server}/test-ssh-connection', 'YnhServerController@testSshConnection')->name('test-ssh-connection');
        Route::post('{server}/configure', 'YnhServerController@configure')->name('configure');
        Route::post('{server}/monitor-server', 'YnhServerController@monitorServer')->name('monitor-server');
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
