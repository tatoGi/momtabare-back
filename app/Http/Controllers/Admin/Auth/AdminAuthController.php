<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminAuthController extends Controller
{
    public function index()
    {

        return view('auth.login');
    }

    public function store(Request $request)
    {
        Log::info('Login attempt', [
            'email' => $request->email,
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent')
        ]);
    
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
    
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            Log::info('Login successful', [
                'user_id' => Auth::id(),
                'session_id' => session()->getId()
            ]);
    
            return redirect()->intended(route('admin.dashboard', app()->getLocale()));
        }
    
        Log::warning('Login failed', [
            'email' => $request->email,
            'error' => 'Invalid credentials'
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

        return redirect()->route('admin.login.dashboard', app()->getLocale());
    }
}
