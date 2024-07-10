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

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

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