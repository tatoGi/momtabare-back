<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AdminAuthController extends Controller
{
    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/admin/dashboard';

    /**
     * Show the admin login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => 'boolean',
        ]);

        if (Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $request->filled('remember')
        )) {
            dd(Session::getId(), session()->all());

            $request->session()->regenerate();
            
            // Log the successful login
            Log::info('Admin login successful', [
                'user_id' => Auth::id(),
                'email' => $credentials['email'],
                'session_id' => Session::getId(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return redirect()->intended(route('admin.dashboard', ['locale' => app()->getLocale()]));
        }

        // Log failed login attempt
        Log::warning('Admin login failed', [
            'email' => $credentials['email'],
            'session_id' => Session::getId(),
            'ip' => $request->ip(),
        ]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }


    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login', ['locale' => app()->getLocale()])
            ->with('status', __('You have been logged out.'));
    }
}