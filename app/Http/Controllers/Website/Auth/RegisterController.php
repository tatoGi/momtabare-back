<?php

namespace App\Http\Controllers\Website\Auth;

use App\Http\Controllers\Controller;
use App\Models\WebUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function register(Request $request)
    {

        try {
            $request->validate([
                'first_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:web_users',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $verificationToken = Str::random(60);

            $user = WebUser::create([
                'first_name' => $request->first_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'email_verification_token' => $verificationToken,
                'email_verified_at' => config('app.env') === 'testing' ? now() : null,
                'is_active' => false,
            ]);

            if (config('app.env') !== 'testing') {
                // Send verification email in non-testing environments
                $user->sendEmailVerificationNotification();

                return response()->json([
                    'success' => true,
                    'message' => 'Registration successful! Please check your email to verify your account.',
                    'requires_verification' => true,
                ], 201);
            }

            // In testing mode, auto-login the user
            Auth::guard('webuser')->login($user);

            return response()->json([
                'success' => true,
                'message' => 'User registered and verified successfully (testing mode)',
                'user' => $user,
                'redirect' => '/',
                'verification_token' => $verificationToken, // Only for testing
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
