<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: #fff; padding: 30px; border-radius: 8px; max-width: 500px; margin: auto; }
        .status { font-size: 24px; font-weight: bold; text-align: center; padding: 15px; border-radius: 8px; margin: 20px 0; }
        .pending { background: #fff3cd; color: #856404; }
        .progress { background: #cce5ff; color: #004085; }
        .resolved { background: #d4edda; color: #155724; }
        .complaint-number { font-size: 18px; color: #666; text-align: center; }
        .footer { color: #999; font-size: 12px; text-align: center; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hello, {{ $name }}!</h2>
        <p>Your complaint status has been updated.</p>
        <div class="complaint-number">{{ $complainNumber }}</div>
        <div class="status {{ $statusClass }}">{{ $status }}</div>
        <p><strong>Title:</strong> {{ $title }}</p>
        @if($status === 'Resolved')
        <p>Your complaint has been resolved. You can now rate our service.</p>
        @elseif($status === 'In Progress')
        <p>Your complaint is currently being handled by our team.</p>
        @endif
        <div class="footer">Complaint Management System</div>
    </div>
</body>
</html>