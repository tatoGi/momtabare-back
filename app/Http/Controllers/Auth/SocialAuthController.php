<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\WebUser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->stateless()->redirect();
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback(): JsonResponse
    {
        try {
            $facebookUser = Socialite::driver('facebook')->stateless()->user();

            // Check if user exists with this facebook_id
            $user = WebUser::where('facebook_id', $facebookUser->getId())->first();

            if ($user) {
                // User exists with Facebook ID - log them in
                $token = $user->createToken('facebook-auth')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Login successful',
                    'user' => $user,
                    'token' => $token,
                ]);
            }

            // Check if user exists with this email
            $user = WebUser::where('email', $facebookUser->getEmail())->first();

            if ($user) {
                // User exists with email - link Facebook account
                $user->update([
                    'facebook_id' => $facebookUser->getId(),
                    'avatar' => $facebookUser->getAvatar(),
                ]);

                $token = $user->createToken('facebook-auth')->plainTextToken;

                return response()->json([
                    'success' => true,
                    'message' => 'Facebook account linked successfully',
                    'user' => $user,
                    'token' => $token,
                ]);
            }

            // Create new user with Facebook data
            $names = $this->parseFullName($facebookUser->getName());

            $user = WebUser::create([
                'first_name' => $names['first_name'],
                'surname' => $names['surname'],
                'email' => $facebookUser->getEmail(),
                'facebook_id' => $facebookUser->getId(),
                'avatar' => $facebookUser->getAvatar(),
                'password' => Hash::make(Str::random(16)), // Random password for OAuth users
                'email_verified_at' => now(), // Facebook email is verified
                'is_active' => true,
            ]);

            $token = $user->createToken('facebook-auth')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Registration successful',
                'user' => $user,
                'token' => $token,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Facebook authentication failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse full name into first_name and surname
     */
    private function parseFullName(string $fullName): array
    {
        $parts = explode(' ', trim($fullName), 2);

        return [
            'first_name' => $parts[0] ?? '',
            'surname' => $parts[1] ?? '',
        ];
    }
}
