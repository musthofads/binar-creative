<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 30px -30px;
        }
        .qr-code {
            text-align: center;
            margin: 30px 0;
        }
        .qr-code img {
            max-width: 300px;
            border: 5px solid #667eea;
            border-radius: 10px;
        }
        .button {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            color: #777;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📸 Your Photobooth Memories!</h1>
        </div>

        <p>Hello!</p>

        <p>Thank you for using <strong>MemoriesEnd Photobooth</strong>! Your photos are ready to download.</p>

        <p>Scan the QR code below with your phone to view and download your photos:</p>

        <div class="qr-code">
            <img src="data:image/png;base64,{{ base64_encode($qrCode) }}" alt="QR Code">
        </div>

        <p style="text-align: center;">Or click the button below:</p>

        <p style="text-align: center;">
            <a href="{{ $photoUrl }}" class="button">View My Photos</a>
        </p>

        <p><strong>Session ID:</strong> <code>{{ $sessionId }}</code></p>

        <p>Your photos will be available for 30 days. Make sure to download them!</p>

        <div class="footer">
            <p>© {{ date('Y') }} MemoriesEnd Photobooth. All rights reserved.</p>
            <p>This is an automated email, please do not reply.</p>
        </div>
    </div>
</body>
</html>
