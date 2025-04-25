<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

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
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginEmail(Request $request)
    {
        // TODO: check email domain to redirect to the corresponding IdP if needed

        $request->validate(['email' => 'required|email']);
        session(['login_email' => $request->email]);
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
}
