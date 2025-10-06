<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    /**
     * Show the admin login form.
     *
     * @return \Illuminate\View\View
     */
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
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);


            $credentials = $request->only('email', 'password');
            $remember = $request->filled('remember');


            if (Auth::attempt($credentials, $remember)) {
                $user = Auth::user();

                $request->session()->regenerate();

                return redirect()->intended(route('admin.dashboard', ['locale' => app()->getLocale()]));
            }

            // If we get here, authentication failed
            Log::warning('Authentication failed', [
                'email' => $credentials['email'],
                'error' => 'Invalid credentials'
            ]);

            return back()->withErrors([
                'email' => __('auth.failed'),
            ])->withInput($request->only('email', 'remember'));
        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'email' => 'An error occurred during login. Please try again.',
            ])->withInput($request->only('email', 'remember'));
        }
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