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
        Log::info('Login attempt', ['email' => $request->email, 'ip' => $request->ip()]);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            Log::info('Login successful', ['user_id' => Auth::id()]);
            //changes made here
           return redirect()->route('admin.dashboard', ['locale' => app()->getLocale()]);
        }

        Log::warning('Login failed', ['email' => $request->email]);
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login.dashboard' , app()->getLocale());
    }
}
