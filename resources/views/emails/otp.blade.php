<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: #fff; padding: 30px; border-radius: 8px; max-width: 500px; margin: auto; }
        .otp { font-size: 32px; font-weight: bold; color: #4F46E5; letter-spacing: 8px; text-align: center; padding: 20px; background: #f0f0ff; border-radius: 8px; margin: 20px 0; }
        .footer { color: #999; font-size: 12px; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello, {{ $name }}!</h2>
        <p>Your verification code for the Complaint Management System is:</p>
        <div class="otp">{{ $otp }}</div>
        <p>This code expires in <strong>10 minutes</strong>.</p>
        <p>If you did not request this code, please ignore this email.</p>
        <div class="footer">Complaint Management System</div>
    </div>
</body>
</html>