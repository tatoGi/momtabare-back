<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\WebUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * POST /login
     * Body: { email?: string, phone_number?: string (not supported yet), password: string }
     */
    /**
     * POST /register
     * Body: { first_name: string, email: string, password: string, password_confirmation: string }
     */
    public function sendRegistrationEmail(Request $request)
    {

        $request->validate([
            'email' => 'required|email|unique:web_users,email',
        ]);

        $verificationCode = rand(100000, 999999);
        $emailVerificationToken = Str::random(60);

        // Create user with temporary hashed password
        $temporaryPassword = Str::random(16); // Generate a random temporary password
        $user = WebUser::create([
            'email' => $request->email,
            'password' => Hash::make($temporaryPassword), // Hash the temporary password
            'verification_code' => $verificationCode,
            'verification_expires_at' => Carbon::now()->addMinutes(15),
            'email_verification_token' => $emailVerificationToken,
        ]);

        // Send verification email with code
        Mail::send('emails.verification', [
            'verificationCode' => $verificationCode,
            'email' => $user->email,
        ], function (Message $message) use ($user) {
            $message->to($user->email)
                ->subject('Your Verification Code');
        });

        return response()->json([
            'message' => 'Verification code sent to your email',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Verify email with verification code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmailCode(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:web_users,id',
            'verification_code' => 'required|string|size:6',
        ]);

        $user = WebUser::find($request->user_id);

        // Check if verification code matches and is not expired
        if ($user->verification_code !== $request->verification_code) {
            return response()->json([
                'message' => 'Invalid verification code.',
                'errors' => [
                    'verification_code' => ['The verification code is invalid.'],
                ],
            ], 422);
        }

        if (Carbon::now()->gt($user->verification_expires_at)) {
            return response()->json([
                'message' => 'Verification code has expired.',
                'errors' => [
                    'verification_code' => ['The verification code has expired.'],
                ],
            ], 422);
        }

        // Mark email as verified
        $user->email_verified_at = now();
        $user->verification_code = null;
        $user->verification_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Email verified successfully',
            'user_id' => $user->id,
        ]);
    }

    /**
     * Complete user registration after email verification
     *
     * @return \Illuminate\Http\JsonResponse
     */
    /**
     * Complete user registration after email verification
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeRegistration(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:web_users,id',
            'password' => 'required|min:6|confirmed',
            'first_name' => 'required|string|max:255',
            'surname' => 'nullable|string|max:255',
        ]);

        $user = WebUser::findOrFail($request->user_id);

        // Update user with registration details
        $user->update([
            'first_name' => $request->first_name,
            'surname' => $request->surname,
            'password' => Hash::make($request->password),
            'email_verified_at' => now(),
        ]);

        // Create new auth token
        $token = $user->createToken('auth_token')->plainTextToken;

        // Create a response with the token
        $response = [
            'success' => true,
            'message' => 'Registration completed successfully',
            'data' => [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => 60 * 24 * 7, // 1 week in minutes
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'surname' => $user->surname,
                    'email' => $user->email,
                    'email_verified' => true,
                ],
            ],
        ];

        // Return with the token in the response
        return response()->json($response)->withHeaders([
            'Authorization' => 'Bearer '.$token,
            'Access-Control-Expose-Headers' => 'Authorization',
        ]);
    }

    /**
     * POST /login
     * Body: { email?: string, phone_number?: string (not supported yet), password: string }
     */
    public function login(Request $request)
    {

        // Check if user is already authenticated via token
        if ($request->bearerToken()) {
            $tokenUser = $request->user('sanctum');
            if ($tokenUser) {
                // User is already authenticated via token, return current user data
                return response()->json([
                    'success' => true,
                    'message' => 'Already logged in',
                    'data' => [
                        'token' => $request->bearerToken(),
                        'token_type' => 'Bearer',
                        'user' => [
                            'id' => $tokenUser->id,
                            'first_name' => $tokenUser->first_name,
                            'surname' => $tokenUser->surname,
                            'email' => $tokenUser->email,
                            'email_verified' => true,
                            'is_retailer' => $tokenUser->is_retailer,
                            'retailer_status' => $tokenUser->retailer_status,
                            'retailer_requested_at' => $tokenUser->retailer_requested_at?->toISOString(),
                        ],
                    ],
                ]);
            }
        }

        // Check if this is a token-based login from registration
        if ($request->has('registration_token') && $request->has('user_id')) {
            $user = WebUser::find($request->input('user_id'));

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
            }

            // Get the user's most recent token
            $token = $user->tokens()->latest()->first();

            if (! $token || ! hash_equals($token->token, hash('sha256', $request->input('registration_token')))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired registration token.',
                ], 401);
            }
        } else {
            // Standard email/password login
            $data = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            $credentials = [
                'email' => $data['email'],
                'password' => $data['password'],
            ];

            if (! Auth::guard('webuser')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid login credentials.',
                ], 401);
            }

            $user = Auth::guard('webuser')->user();
        }

        if (! $user instanceof WebUser) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Check if email is verified
        if (! $user->email_verified_at) {
            return response()->json([
                'message' => 'Please verify your email address before logging in.',
                'requires_verification' => true,
            ], 403);
        }

        $request->session()->regenerate();

        // Create a new API token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Logged in successfully',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'first_name' => $user->first_name,
                    'surname' => $user->surname,
                    'email' => $user->email,
                    'email_verified' => true,
                ],
            ],
        ]);
    }

    /**
     * POST /logout
     */
    public function logout(Request $request)
    {
        Auth::guard('webuser')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * GET /me - returns the authenticated user
     * Handles both web session and API token authentication
     */
    public function me(Request $request)
    {
        // Try to get the authenticated user
        $user = $request->user('sanctum');

        // If no user found with the current guard, try the other guard
        if (! $user) {
            $currentGuard = Auth::getDefaultDriver();
            $otherGuard = $currentGuard === 'web' ? 'sanctum' : 'web';

            if (Auth::guard($otherGuard)->check()) {
                $user = Auth::guard($otherGuard)->user();

                // If we found a user with the other guard, log them in with the current guard
                if ($user) {
                    Auth::guard($currentGuard)->login($user);
                }
            }
        }

        if (! $user) {
            Log::warning('Unauthenticated access to /me endpoint', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'has_bearer_token' => $request->bearerToken() ? true : false,
                'previous_url' => $request->headers->get('referer'),
            ]);

            return response()->json([
                'message' => 'Unauthenticated',
                'auth_check' => false,
                'user_id' => null,
            ], 401);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'surname' => $user->surname,
                'email' => $user->email,
                'email_verified' => $user->hasVerifiedEmail(),
                'phone' => $user->phone,
                'personal_id' => $user->personal_id,
                'birth_date' => $user->birth_date?->toDateString(),
                'gender' => $user->gender,
                'is_retailer' => $user->is_retailer,
                'is_active' => $user->is_active,
                'retailer_status' => $user->retailer_status,
                'retailer_requested_at' => $user->retailer_requested_at?->toISOString(),
                'avatar' => $user->avatar,
                'avatar_url' => $user->avatar ? asset('storage/'.$user->avatar) : null,
                'email_verified_at' => $user->email_verified_at?->toISOString(),
                'created_at' => $user->created_at?->toISOString(),
                'updated_at' => $user->updated_at?->toISOString(),
            ],
        ]);
    }

    /**
     * GET /verify-email/{token} - Verify user's email
     */
    /**
     * Handle email verification from form submission (POST request)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmailFromForm(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        return $this->verifyEmail($request->token);
    }

    /**
     * Verify user's email with token (GET request)
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyEmail($token)
    {
        $user = WebUser::where('email_verification_token', $token)->first();

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification token.',
            ], 400);
        }

        if ($user->hasVerifiedEmail() || $user->email_verified_at) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
            ]);
        }

        $user->markEmailAsVerified();

        // Log the user in
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ],
            'redirect' => '/profile',
        ]);
    }

    /**
     * POST /resend-verification - Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:web_users,email',
        ]);

        $user = WebUser::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => true,
                'message' => 'Email already verified.',
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent.',
        ]);
    }
}
