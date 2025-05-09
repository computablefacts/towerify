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
use App\Events\BeginVulnsScan;
use App\Events\EndPortsScan;
use App\Events\EndVulnsScan;
use App\Events\RebuildLatestEventsCache;
use App\Events\RebuildPackagesList;
use App\Helpers\SshKeyPair;
use App\Http\Middleware\RedirectIfNotSubscribed;
use App\Jobs\DownloadDebianSecurityBugTracker;
use App\Listeners\EndVulnsScanListener;
use App\Mail\AuditReport;
use App\Models\Asset;
use App\Models\Honeypot;
use App\Models\Port;
use App\Models\Scan;
use App\Models\YnhServer;
use App\Models\YnhTrial;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
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

            \App\Events\CreateAsset::dispatch($user, $server->ip(), true, [$server->name]);
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

    $script = ($server->platform === OsqueryPlatformEnum::WINDOWS) ? '# Windows: no local metric' : '# Linux: no local metric';

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

Route::get('/performa/user/login/{performa_domain}', function (string $performa_domain, \Illuminate\Http\Request $request) {

    $shouldLogout = $request->input('logout', 0);
    if (1 == $shouldLogout) {
        Auth::logout();
        return redirect()->route('home');
    }

    /** @var User $user */
    $user = Auth::user();
    $usernames = collect(config('towerify.performa.whitelist.usernames'))->map(fn(string $username) => $username . '@')->toArray();
    $domains = collect(config('towerify.performa.whitelist.domains'))->map(fn(string $domain) => '@' . $domain)->toArray();

    if ($user
        && ($user->performa_domain === $performa_domain
            || (Str::startsWith($user->email, $usernames) && Str::endsWith($user->email, $domains))
        )
    ) {
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
Route::get('/dispatch/job/{job}/{trialId?}', function (string $job, ?int $trialId = null) {

    /** @var User $user */
    $user = Auth::user();
    $usernames = collect(config('towerify.telescope.whitelist.usernames'))->map(fn(string $username) => $username . '@')->toArray();
    $domains = collect(config('towerify.telescope.whitelist.domains'))->map(fn(string $domain) => '@' . $domain)->toArray();

    if (Str::startsWith($user->email, $usernames) && Str::endsWith($user->email, $domains)) {
        try {
            if ($job === 'dl_debian_security_bug_tracker') {
                DownloadDebianSecurityBugTracker::dispatch();
            } elseif ($job === 'rebuild_packages_list') {
                RebuildPackagesList::sink();
            } elseif ($job === 'rebuild_latest_events_cache') {
                RebuildLatestEventsCache::sink();
            } elseif ($job === 'resend_trial_email') {
                /** @var YnhTrial $trial */
                $trial = YnhTrial::findOrFail($trialId);
                EndVulnsScanListener::sendEmailReport($trial);
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
})->middleware([RedirectIfNotSubscribed::class]);

Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::user()) {
        return redirect()->route('home');
    }
    return redirect()->route('the-cyber-brief', ['lang' => 'fr']);
})->middleware([RedirectIfNotSubscribed::class]);

Auth::routes();
Route::post('/login/email', 'Auth\LoginController@loginEmail')->name('login.email');
Route::get('/login/password', 'Auth\LoginController@showLoginPasswordForm')->name('login.password');

Route::get('/home', 'HomeController@index')->name('home')->middleware([RedirectIfNotSubscribed::class]);

Route::get('/terms', 'TermsController@show')->name('terms');

Route::get('/reset-password', function () {
    $email = \Illuminate\Support\Facades\Auth::user()->email;
    Auth::logout();
    return redirect()->route('password.request', ['email' => $email]);
})->middleware(['auth', RedirectIfNotSubscribed::class])->name('reset-password');

Route::get('/notifications/{notification}/dismiss', function (\Illuminate\Notifications\DatabaseNotification $notification, \Illuminate\Http\Request $request) {
    \Illuminate\Notifications\DatabaseNotification::query()
        ->whereNull('read_at')
        ->whereJsonContains('data->group', $notification->data['group'])
        ->get()
        ->each(fn($notif) => $notif->markAsRead());
})->middleware(['auth', RedirectIfNotSubscribed::class]);

Route::get('/events/{osquery}/dismiss', function (\App\Models\YnhOsquery $osquery, \Illuminate\Http\Request $request) {
    /** @var YnhServer $server */
    $server = YnhServer::find($osquery->ynh_server_id);
    if ($server) {
        $osquery->dismissed = true;
        $osquery->save();
        return response()->json(['success' => "The event has been dismissed!"]);
    }
    return response()->json(['error' => "Unknown event."], 500);
})->middleware(['auth', RedirectIfNotSubscribed::class]);

Route::group(['prefix' => 'ynh', 'as' => 'ynh.'], function () {
    Route::group(['prefix' => 'servers', 'as' => 'servers.'], function () {
        Route::get('', 'YnhServerController@create')->name('create');
        Route::get('{server}/edit', 'YnhServerController@index')->name('edit');
        Route::post('{server}/edit', 'YnhServerController@index')->name('edit');
        Route::post('{server}/backup', 'YnhServerController@createBackup')->name('create-backup');
        Route::get('{server}/backup/{backup}', 'YnhServerController@downloadBackup')->name('download-backup');
    });
    Route::group(['prefix' => 'users', 'as' => 'users.'], function () {
        Route::get('{user}/toggle-gets-audit-report', 'UserController@toggleGetsAuditReport')->name('toggle-gets-audit-report');
    });
})->middleware([RedirectIfNotSubscribed::class]);

Route::group(['prefix' => 'shop', 'as' => 'product.'], function () {
    Route::get('index', 'ProductController@index')->name('index');
    Route::get('c/{taxonomyName}/{taxon}', 'ProductController@index')->name('category');
    Route::get('p/{slug}', 'ProductController@show')->name('show');
    Route::get('p/{slug}/{taxon}', 'ProductController@show')->name('show-with-taxon');
})->middleware([RedirectIfNotSubscribed::class]);

Route::group(['prefix' => 'cart', 'as' => 'cart.'], function () {
    Route::get('show', 'CartController@show')->name('show');
    Route::post('add/{product}', 'CartController@add')->name('add');
    Route::post('adv/{masterProductVariant}', 'CartController@addVariant')->name('add-variant');
    Route::post('update/{cart_item}', 'CartController@update')->name('update');
    Route::post('remove/{cart_item}', 'CartController@remove')->name('remove');
})->middleware([RedirectIfNotSubscribed::class]);

Route::group(['prefix' => 'checkout', 'as' => 'checkout.'], function () {
    Route::get('show', 'CheckoutController@show')->name('show');
    Route::post('submit', 'CheckoutController@submit')->name('submit');
})->middleware([RedirectIfNotSubscribed::class]);

Route::get('/plans', 'StripeController@plan')->name('plans');
Route::get('/subscribe', 'StripeController@subscribe')->name('subscribe');
Route::get('/subscribe/success/{tx_id}', 'StripeController@subscribed')->name('subscribed');
Route::get('/customer-portal', 'StripeController@customerPortal')->middleware('auth')->name('customer-portal');
Route::get('/invitation', fn() => new \App\Mail\Invitation(\App\Models\Invitation::query()->latest()->first()))->middleware('auth');

Route::group(['prefix' => 'cyber-check', 'as' => 'cyber-check.'], function () {

    Route::get('/', fn() => redirect()->route('cyber-check.cywise.onboarding', [
        'hash' => Str::random(128),
        'step' => 1,
    ]));
    Route::match(['get', 'post'], '/{hash}/step/{step}', 'CywiseController@onboarding')->name('cywise.onboarding');
    Route::post('/{hash}/discovery', 'CywiseController@discovery')->name('cywise.discovery');

})->middleware(['auth']);

Route::group(['prefix' => 'cyber-advisor', 'as' => 'cyber-advisor.'], function () {

    Route::match(['get', 'post'], '/', 'CywiseController@onboarding2')->name('cywise2.onboarding');

})->middleware(['auth']);

Route::get('/the-cyber-brief', 'TheCyberBriefController@index')->name('the-cyber-brief');

Route::post('/llm1', 'CyberBuddyController@llm1')->middleware('auth');

Route::post('/llm2', 'CyberBuddyController@llm2')->middleware('auth');

Route::get('/templates', 'CyberBuddyController@templates')->middleware('auth');

Route::post('/templates', 'CyberBuddyController@saveTemplate')->middleware('auth');

Route::delete('/templates/{id}', 'CyberBuddyController@deleteTemplate')->middleware('auth');

Route::get('/files', 'CyberBuddyController@files')->middleware('auth');

Route::delete('/files/{id}', 'CyberBuddyController@deleteFile')->middleware('auth');

Route::get('/files/stream/{secret}', 'CyberBuddyController@streamFile');

Route::get('/files/download/{secret}', 'CyberBuddyController@downloadFile');

Route::post('/files/one', 'CyberBuddyController@uploadOneFile')->middleware('auth:sanctum');

Route::post('/files/many', 'CyberBuddyController@uploadManyFiles')->middleware('auth:sanctum');

Route::get('/collections', 'CyberBuddyController@collections')->middleware('auth');

Route::delete('/collections/{id}', 'CyberBuddyController@deleteCollection')->middleware('auth');

Route::post('/collections/{id}', 'CyberBuddyController@saveCollection')->middleware('auth');

Route::delete('/chunks/{id}', 'CyberBuddyController@deleteChunk')->middleware('auth');

Route::post('/chunks/{id}', 'CyberBuddyController@saveChunk')->middleware('auth');

Route::delete('/prompts/{id}', 'CyberBuddyController@deletePrompt')->middleware('auth');

Route::post('/prompts/{id}', 'CyberBuddyController@savePrompt')->middleware('auth');

Route::delete('/conversations/{id}', 'CyberBuddyController@deleteConversation')->middleware('auth');

Route::delete('/frameworks/{id}', 'CyberBuddyController@unloadFramework')->middleware('auth');

Route::post('/frameworks/{id}', 'CyberBuddyController@loadFramework')->middleware('auth');

Route::get('/cyber-buddy', 'CyberBuddyController@showPage')->middleware('auth');

Route::get('/cyber-buddy/chat', 'CyberBuddyController@showChat');

Route::match(['get', 'post'], 'botman', 'CyberBuddyController@handle');

Route::group([
    'prefix' => 'tables',
], function () {
    Route::get('/', 'CyberBuddyController@listTables')->name('list-tables');
    Route::post('/columns', 'CyberBuddyController@listTablesColumns')->name('list-tables-columns');
    Route::post('/import', 'CyberBuddyController@importTables')->name('import-tables');
    Route::get('/available', 'CyberBuddyController@availableTables')->name('available-tables');
    Route::post('/query', 'CyberBuddyController@queryTables')->name('query-tables');
    Route::post('/prompt-to-query', 'CyberBuddyController@promptToTablesQuery')->name('prompt-to-tables-query');
})->middleware(['auth']);

Route::group([
    'prefix' => 'assistant',
], function () {
    Route::get('/', 'CyberBuddyNextGenController@showAssistant');
    Route::post('/converse', 'CyberBuddyNextGenController@converse');
})->middleware(['auth', 'throttle:15,1']);

Route::get('/cyber-todo/{hash}', 'CyberTodoController@show');

Route::get('/audit-report', fn() => AuditReport::create()['report'])->middleware('auth');

/** @deprecated */
Route::post('am/api/v2/public/ports-scan/{uuid}', function (string $uuid, \Illuminate\Http\Request $request) {

    /** @var Scan $scan */
    $scan = Scan::where('ports_scan_id', $uuid)->first();

    if (!$scan) {
        return response('Unknown scan', 500)
            ->header('Content-Type', 'text/plain');
    }

    /** @var Asset $asset */
    $asset = $scan->asset()->first();

    if (!$asset) {
        return response('Unknown asset', 500)
            ->header('Content-Type', 'text/plain');
    }
    if ($request->has('task_result')) {
        EndPortsScan::dispatch(Carbon::now(), $asset, $scan, $request->get('task_result', []));
    } else {
        /* BEGIN COPY/PASTE FROM EndPortsScanListener.php */

        // Legacy stuff: if no port is open, create a dummy one that will be marked as closed by the vulns scanner
        $port = Port::create([
            'scan_id' => $scan->id,
            'hostname' => "localhost",
            'ip' => "127.0.0.1",
            'port' => 666,
            'protocol' => "tcp",
        ]);

        $scan->ports_scan_ends_at = \Carbon\Carbon::now();
        $scan->save();

        BeginVulnsScan::dispatch($scan, $port);

        /* END COPY/PASTE FROM EndPortsScanListener.php */
    }
    return response("ok", 200)
        ->header('Content-Type', 'text/plain');
})->middleware(['auth', 'throttle:240,1']);

/** @deprecated */
Route::post('am/api/v2/public/vulns-scan/{uuid}', function (string $uuid, \Illuminate\Http\Request $request) {

    if (!$request->has('task_result')) {
        return response('Missing task result', 500)
            ->header('Content-Type', 'text/plain');
    }

    /** @var Scan $scan */
    $scan = Scan::where('vulns_scan_id', $uuid)->first();

    if (!$scan) {
        return response('Unknown scan', 500)
            ->header('Content-Type', 'text/plain');
    }

    EndVulnsScan::dispatch(Carbon::now(), $scan, $request->get('task_result', []));

    return response("ok", 200)
        ->header('Content-Type', 'text/plain');
})->middleware(['auth', 'throttle:240,1']);

/** @deprecated */
Route::post('am/api/v2/public/honeypots/{dns}', function (string $dns, \Illuminate\Http\Request $request) {

    if (!$request->hasFile('data')) {
        return response('Missing attachment', 500)
            ->header('Content-Type', 'text/plain');
    }

    $file = $request->file('data');

    if (!$file->isValid()) {
        return response('Invalid attachment', 500)
            ->header('Content-Type', 'text/plain');
    }

    $honeypot = Honeypot::where('dns', $dns)->first();

    if (!$honeypot) {
        return response('Unknown honeypot', 500)
            ->header('Content-Type', 'text/plain');
    }

    $filename = $file->getClientOriginalName();
    $timestamp = Carbon::createFromFormat('Y.m.d_H.i.s', \Illuminate\Support\Str::substr($filename, \Illuminate\Support\Str::position($filename, '-access.') + 8, 19));
    $events = collect(implode(gzfile($file->getRealPath())))
        ->flatMap(fn(string $line) => json_decode(trim($line), true));

    if ($events->isEmpty()) {
        return response('ok (empty file)', 200)
            ->header('Content-Type', 'text/plain');
    }

    \App\Events\IngestHoneypotsEvents::dispatch($timestamp, $dns, $events->toArray());

    return response("ok ({$events->count()} events in file)", 200)
        ->header('Content-Type', 'text/plain');
})->middleware(['auth', 'throttle:240,1']);

/**
 * We need to add password requirements to invitation form
 * So we extends Konekt\AppShell\Http\Controllers\PublicInvitationController
 * Then we need to change the routes to use our controller
 *
 * See: vendor/konekt/appshell/src/resources/routes/public.php
 */
Route::get('pub/invitation/{hash}', [
    'uses' => 'PasswordRequirementsPublicInvitationController@show',
    'as' => 'appshell.public.invitation.show'
])->where('hash', '[A-Za-z0-9]+');

Route::post('pub/invitation/accept', [
    'uses' => 'PasswordRequirementsPublicInvitationController@accept',
    'as' => 'appshell.public.invitation.accept'
]);
