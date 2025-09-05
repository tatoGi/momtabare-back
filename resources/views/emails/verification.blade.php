<!DOCTYPE html>
<html>
<head>
    <title>Email Verification Code</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
        }
        .code {
            font-size: 24px;
            font-weight: bold;
            letter-spacing: 2px;
            padding: 10px 20px;
            background-color: #f5f5f5;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
        }
        .expiry {
            color: #666;
            font-size: 14px;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Verification Code</h2>
        <p>Hello,</p>
        <p>Your verification code is:</p>
        <div class="code">{{ $verificationCode }}</div>
        <p class="expiry">This code will expire in 15 minutes.</p>
        <p>If you didn't request this code, you can safely ignore this email.</p>
        <p>Best regards,<br>Your App Team</p>
    </div>
</body>
</html>
