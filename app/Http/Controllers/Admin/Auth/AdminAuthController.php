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

<<<<<<< HEAD
        if (Auth::guard('web')->attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->route('admin.dashboard', app()->getLocale());
        }
=======
       if (Auth::guard('web')->attempt($credentials)) {
          
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard', app()->getLocale());
            }
>>>>>>> 49ce187 (changes)

        Log::warning('Login failed', ['email' => $request->email]);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
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
