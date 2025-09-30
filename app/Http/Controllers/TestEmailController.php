<?php

namespace App\Http\Controllers;

use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Mail;

class TestEmailController extends Controller
{
    public function testEmail()
    {
        try {
            $to = 'tato.laperashvili95@gmail.com'; // Replace with your test email

            Mail::raw('This is a test email from Laravel', function (Message $message) use ($to) {
                $message->to($to)
                    ->subject('Test Email from Laravel');
            });

            return response()->json(['message' => 'Test email sent successfully!']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to send test email',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
