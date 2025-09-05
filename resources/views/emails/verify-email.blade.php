<!DOCTYPE html>
<html>
<head>
    <title>Verify Your Email Address</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 20px 0;
            background-color: #3490dc;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .button:hover {
            background-color: #2779bd;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <h2>Verify Your Email Address</h2>
    <p>Thank you for registering! Please click the button below to verify your email address.</p>
    
    <a href="{{ $url }}" class="button">Verify Email Address</a>
    
    <p>If you did not create an account, no further action is required.</p>
    
    <div class="footer">
        <p>If you're having trouble clicking the "Verify Email Address" button, copy and paste the URL below into your web browser:</p>
        <p>{{ $url }}</p>
    </div>
</body>
</html>
