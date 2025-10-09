<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AdminAuthController extends Controller
{
    protected $redirectTo = '/admin/dashboard';

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('webuser')->attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            Log::info('Admin logged in', [
                'id' => Auth::guard('webuser')->id(),
                'email' => $credentials['email'],
                'session_id' => Session::getId(),
                'ip' => $request->ip(),
            ]);

            return redirect()->route('admin.dashboard', ['locale' => app()->getLocale()]);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::guard('webuser')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login', ['locale' => app()->getLocale()])
            ->with('status', __('You have been logged out.'));
    }
}
