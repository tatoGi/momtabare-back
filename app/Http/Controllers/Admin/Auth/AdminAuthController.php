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

                // Remove this line after debugging
                return response()->json([
                    'success' => true,
                    'message' => 'Authentication successful',
                    'user' => $user,
                    'intended' => route('admin.dashboard', ['locale' => app()->getLocale()])
                ]);
            }

            // If we get here, authentication failed
            Log::warning('Authentication failed', [
                'email' => $credentials['email'],
                'error' => 'Invalid credentials'
            ]);

            // For debugging - return JSON response
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed',
                'errors' => ['email' => 'Invalid credentials'],
                'debug' => [
                    'credentials' => $credentials,
                    'remember' => $remember,
                    'auth_check' => Auth::check(),
                    'session_id' => session()->getId()
                ]
            ], 401);

            // This will be used after debugging
            // return back()->withErrors([
            //     'email' => __('auth.failed'),
            // ])->withInput($request->only('email', 'remember'));
        } catch (\Exception $e) {
            Log::error('Login error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred during login',
                'error' => $e->getMessage()
            ], 500);
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