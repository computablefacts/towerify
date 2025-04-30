<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Saml2Tenant;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectTo()
    {
        // if ($this->guard()->user()->type->isAdmin()) {
        // return config('konekt.app_shell.ui.url');
        // }
        return '/home';
    }

    /**
     * Handle a login request with email only to the application.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $email = $request->input('email');
        session(['login_email' => $email]);

        $samlTenant = Saml2Tenant::firstFromDomain(Str::after($email, '@'));
        if ($samlTenant) {
            return redirect()->intended(saml_url($this->redirectTo(), $samlTenant->uuid));
        }

        return redirect()->route('login.password');
    }

    /**
     * Show the application's login form to ask for the password only.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function showLoginPasswordForm()
    {
        if (!session('login_email')) {
            return redirect()->route('login');
        }

        return view('auth.login_password', ['email' => session('login_email')]);
    }

    /**
     * Log the user out of the application.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        if ($uuid = saml_tenant_uuid()) {
            Log::debug('[SAML2 Authentication] Logout', ['uuid' => $uuid, 'email' => Auth::user()->email]);
            return redirect(URL::route('saml.logout', ['uuid' => $uuid]));
        }

        $this->guard()->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if ($response = $this->loggedOut($request)) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([], 204)
            : redirect('/');
    }

}
