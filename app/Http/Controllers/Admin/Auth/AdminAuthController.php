<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

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
        return view('auth.login'); // adjust if your view path differs
    }

    /**
     * Handle an authentication attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    /**
 * Handle an authentication attempt.
 *
 * @param  \App\Http\Requests\Auth\LoginRequest  $request
 * @return \Illuminate\Http\RedirectResponse
 */
public function login(LoginRequest $request)
{
    // The request is already validated by LoginRequest
    $credentials = $request->only('email', 'password');
    
    // Find user by email
    $user = User::where('email', $credentials['email'])->first();
    
    // Check if user exists and password matches
    if ($user && Hash::check($credentials['password'], $user->password)) {
    
        
        // Manually log the user in
        Auth::login($user, $request->boolean('remember'));
        
        
        // Redirect to admin dashboard
        return redirect()->intended(route('admin.dashboard', app()->getLocale()));
    }
    
    // If authentication fails
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
     * @param \Illuminate\Http\Request $request
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
