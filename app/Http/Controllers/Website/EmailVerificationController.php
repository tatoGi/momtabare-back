<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\WebUser;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $token)
    {
        $user = WebUser::where('email_verification_token', $token)->first();

        if (! $user) {
            return response()->json([
                'message' => 'Invalid verification token.',
                'success' => false,
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
                'success' => true,
            ]);
        }

        $user->markEmailAsVerified();

        // Generate an access token for the user
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Email verified successfully.',
            'success' => true,
            'token' => $token,
            'redirect' => '/profile', // Or your profile URL
        ]);
    }

    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:web_users,email',
        ]);

        $user = WebUser::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email already verified.',
                'success' => true,
            ]);
        }

        // Generate new verification token if not exists
        if (! $user->email_verification_token) {
            $user->email_verification_token = Str::random(60);
            $user->save();
        }

        // Send verification email
        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Verification email resent.',
            'success' => true,
        ]);
    }
}
