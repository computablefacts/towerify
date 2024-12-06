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

use App\Enums\OsqueryPlatformEnum;
use App\Events\RebuildPackagesList;
use App\Helpers\SshKeyPair;
use App\Http\Middleware\Subscribed;
use App\Models\YnhServer;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/** @deprecated */
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

Route::get('/setup/script', function (\Illuminate\Http\Request $request) {

    $token = $request->input('api_token');

    if (!$token) {
        return response('Missing token', 403)
            ->header('Content-Type', 'text/plain');
    }

    /** @var \Laravel\Sanctum\PersonalAccessToken $token */
    $token = \Laravel\Sanctum\PersonalAccessToken::findToken($token);

    if (!$token) {
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

    $name = $request->input('server_name');

    if (!$name) {
        return response('Invalid server name', 500)
            ->header('Content-Type', 'text/plain');
    }

    $platform = $request->input('platform');

    if ($platform) {
        $platform = OsqueryPlatformEnum::tryFrom($platform);
    } else {
        $platform = OsqueryPlatformEnum::LINUX;
    }

    if (!$platform) {
        return response('Invalid platform name', 500)
            ->header('Content-Type', 'text/plain');
    }

    $server = \App\Models\YnhServer::where('ip_address', $ip)
        ->where('name', $name)
        ->first();

    if (!$server) {

        $server = \App\Models\YnhServer::where('ip_address_v6', $ip)
            ->where('name', $name)
            ->first();

        if (!$server) {

            $server = YnhServer::create([
                'name' => $name,
                'ip_address' => $ip,
                'user_id' => $user->id,
                'secret' => Str::random(30),
                'is_ready' => false,
                'is_frozen' => true,
                'added_with_curl' => true,
                'platform' => $platform,
            ]);

            \App\Modules\AdversaryMeter\Events\CreateAsset::dispatch($user, $server->ip(), true, [$server->name]);
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

    // 1. In the browser, go to "https://app.towerify.io" and login using your user account
    // 2. On the server, run as root: curl -s https://app.towerify.io/setup/script?api_token=<token>&server_ip=<ip>&server_name=<name> | bash
    $installScript = ($server->platform === OsqueryPlatformEnum::WINDOWS) ? \App\Models\YnhOsquery::monitorWindowsServer($server) : \App\Models\YnhOsquery::monitorLinuxServer($server);

    return response($installScript, 200)
        ->header('Content-Type', 'text/plain');
})->middleware(['throttle:6,1']);

Route::get('/update/{secret}', function (string $secret, \Illuminate\Http\Request $request) {

    $server = \App\Models\YnhServer::where('secret', $secret)->first();

    if (!$server) {
        return response('Unknown server', 500)
            ->header('Content-Type', 'text/plain');
    }

    $installScript = ($server->platform === OsqueryPlatformEnum::WINDOWS) ? \App\Models\YnhOsquery::monitorWindowsServer($server) : \App\Models\YnhOsquery::monitorLinuxServer($server);

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
            ->filter(fn($event) => $event && count($event) > 0)
            ->all();

        \App\Events\ProcessLogalertPayload::dispatch($server, $events);

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

Route::get('/localmetrics/{secret}', function (string $secret, \Illuminate\Http\Request $request) {

    $server = \App\Models\YnhServer::where('secret', $secret)->first();

    if (!$server) {
        return response('Unknown server', 500)
            ->header('Content-Type', 'text/plain');
    }

    $script = ($server->platform === OsqueryPlatformEnum::WINDOWS) ? \App\Models\YnhOsquery::monitorLocalMetricsWindows($server) : '# TODO';

    return response($script, 200)
        ->header('Content-Type', 'text/plain');
})->middleware('throttle:6,1');

Route::get('/logparser/{secret}', function (string $secret, \Illuminate\Http\Request $request) {

    $server = \App\Models\YnhServer::where('secret', $secret)->first();

    if (!$server) {
        return response('Unknown server', 500)
            ->header('Content-Type', 'text/plain');
    }

    $config = ($server->platform === OsqueryPlatformEnum::WINDOWS) ? \App\Models\YnhOsquery::configLogParserWindows($server) : \App\Models\YnhOsquery::configLogParserLinux($server);

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

    $filename = $file->getClientOriginalName();

    if ($filename === "apache.txt.gz" || $filename === "nginx.txt.gz") {

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

        // Do not chunk because logs are managed as a whole!
        \App\Events\ProcessLogparserPayload::dispatch($server, $logs);

    } else if ($filename === "osquery.jsonl.gz") {

        $logs = collect(gzfile($file->getRealPath()))
            ->map(fn(string $line) => json_decode(trim($line), true));

        if ($logs->isEmpty()) {
            return response('ok (empty file)', 200)
                ->header('Content-Type', 'text/plain');
        }

        $logs->chunk(1000)->each(function ($chunk) use ($server) {
            \App\Events\ProcessLogalertPayloadEx::dispatch($server, $chunk->toArray());
        });

    } else {
        return response('Invalid attachment', 500)
            ->header('Content-Type', 'text/plain');
    }
    return response("ok ({$logs->count()} rows in file)", 200)
        ->header('Content-Type', 'text/plain');
});

Route::get('/performa/{secret}', function (string $secret, \Illuminate\Http\Request $request) {

    $server = \App\Models\YnhServer::where('secret', $secret)->first();

    if (!$server) {
        return response('Unknown server', 500)
            ->header('Content-Type', 'text/plain');
    }

    $config = \App\Models\YnhOsquery::configPerforma($server);

    return response($config, 200)
        ->header('Content-Type', 'text/plain');
})->middleware('throttle:6,1');

Route::get('/performa/user/login', function (\Illuminate\Http\Request $request) {

    $shouldLogout = $request->input('logout', 0);
    if (1 == $shouldLogout) {
        Auth::logout();
        return redirect()->route('home');
    }

    /** @var User $user */
    $user = Auth::user();

    if ($user) {
        return response()->json([
            'code' => 0,
            'username' => $user->ynhUsername(),
            'user' => [
                'full_name' => $user->name,
                'email' => $user->email,
                'privileges' => [
                    'admin' => $user->canManageServers() ? 1 : 0,
                ],
            ],
        ]);
    }

    return response()->json([
        'code' => 0,
        'location' => route('login') . '?redirect_to=',
    ]);

})->middleware('throttle:6,1');

/** @deprecated */
Route::get('/dispatch/{job}', function (string $job) {

    /** @var User $user */
    $user = Auth::user();
    $usernames = collect(config('towerify.telescope.whitelist.usernames'))->map(fn(string $username) => $username . '@')->toArray();
    $domains = collect(config('towerify.telescope.whitelist.domains'))->map(fn(string $domain) => '@' . $domain)->toArray();

    if (Str::startsWith($user->email, $usernames) && Str::endsWith($user->email, $domains)) {
        try {
            if ($job === 'dl_debian_security_bug_tracker') {
                \App\Jobs\DownloadDebianSecurityBugTracker::dispatch();
            } elseif ($job === 'rebuild_packages_list') {
                RebuildPackagesList::sink();
            }
        } catch (\Exception $exception) {
            Log::error($exception->getMessage());
        }
        return response('ok', 200)->header('Content-Type', 'text/plain');
    }
    return response('Unauthorized', 403)->header('Content-Type', 'text/plain');
})->middleware('auth');

Route::get('', function () {
    if (\Illuminate\Support\Facades\Auth::user()) {
        return redirect()->route('home');
    }
    return redirect()->route('the-cyber-brief', ['lang' => 'fr']);
})->middleware([Subscribed::class]);

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::user()) {
        return redirect()->route('home');
    }
    return redirect()->route('the-cyber-brief', ['lang' => 'fr']);
})->middleware([Subscribed::class]);

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home')->middleware([Subscribed::class]);

Route::get('/terms', 'TermsController@show')->name('terms');

Route::post('/reset-password', function () {
    $email = \Illuminate\Support\Facades\Auth::user()->email;
    return view('auth.passwords.email', compact('email'));
})->middleware(['auth', Subscribed::class])->name('reset-password');

Route::get('/notifications/{notification}/dismiss', function (\Illuminate\Notifications\DatabaseNotification $notification, \Illuminate\Http\Request $request) {
    \Illuminate\Notifications\DatabaseNotification::query()
        ->whereNull('read_at')
        ->whereJsonContains('data->group', $notification->data['group'])
        ->get()
        ->each(fn($notif) => $notif->markAsRead());
})->middleware(['auth', Subscribed::class]);

Route::get('/events/{osquery}/dismiss', function (\App\Models\YnhOsquery $osquery, \Illuminate\Http\Request $request) {
    /** @var YnhServer $server */
    $server = YnhServer::find($osquery->ynh_server_id);
    if ($server) {
        $osquery->dismissed = true;
        $osquery->save();
        return response()->json(['success' => "The event has been dismissed!"]);
    }
    return response()->json(['error' => "Unknown event."], 500);
})->middleware(['auth', Subscribed::class]);

Route::group(['prefix' => 'ynh', 'as' => 'ynh.'], function () {
    Route::group(['prefix' => 'servers', 'as' => 'servers.'], function () {
        Route::get('', 'YnhServerController@create')->name('create');
        Route::delete('{server}', 'YnhServerController@delete')->name('delete');
        Route::get('{server}/edit', 'YnhServerController@index')->name('edit');
        Route::post('{server}/edit', 'YnhServerController@index')->name('edit');
        Route::post('{server}/pull-server-infos', 'YnhServerController@pullServerInfos')->name('pull-server-infos');
        Route::post('{server}/test-ssh-connection', 'YnhServerController@testSshConnection')->name('test-ssh-connection');
        Route::post('{server}/configure', 'YnhServerController@configure')->name('configure');
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
})->middleware([Subscribed::class]);

Route::group(['prefix' => 'shop', 'as' => 'product.'], function () {
    Route::get('index', 'ProductController@index')->name('index');
    Route::get('c/{taxonomyName}/{taxon}', 'ProductController@index')->name('category');
    Route::get('p/{slug}', 'ProductController@show')->name('show');
    Route::get('p/{slug}/{taxon}', 'ProductController@show')->name('show-with-taxon');
})->middleware([Subscribed::class]);

Route::group(['prefix' => 'cart', 'as' => 'cart.'], function () {
    Route::get('show', 'CartController@show')->name('show');
    Route::post('add/{product}', 'CartController@add')->name('add');
    Route::post('adv/{masterProductVariant}', 'CartController@addVariant')->name('add-variant');
    Route::post('update/{cart_item}', 'CartController@update')->name('update');
    Route::post('remove/{cart_item}', 'CartController@remove')->name('remove');
})->middleware([Subscribed::class]);

Route::group(['prefix' => 'checkout', 'as' => 'checkout.'], function () {
    Route::get('show', 'CheckoutController@show')->name('show');
    Route::post('submit', 'CheckoutController@submit')->name('submit');
})->middleware([Subscribed::class]);

Route::get('/plans', 'StripeController@plan')->name('plans');
Route::get('/subscribe', 'StripeController@subscribe')->name('subscribe');
Route::get('/subscribe/success/{tx_id}', 'StripeController@subscribed')->name('subscribed');
Route::get('/customer-portal', 'StripeController@customerPortal')->middleware('auth')->name('customer-portal');

