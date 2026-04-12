<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class EmailService
{
    protected Client $client;
    protected ?string $apiKey;
    protected string $fromEmail;
    protected string $fromName;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://api.resend.com/',
            'timeout' => 30,
        ]);

        $this->apiKey = config('services.resend.api_key');
        $this->fromEmail = config('mail.from.address', 'noreply@photobooth.local');
        $this->fromName = config('mail.from.name', 'Photobooth App');
    }

    /**
     * Send photo email with QR code via Resend API.
     */
    public function sendPhotoEmail(string $toEmail, string $sessionId, string $photoUrl, string $qrCodeBase64): bool
    {
        // Check if API key is configured
        if (empty($this->apiKey)) {
            Log::warning('Email service not configured. API key is missing.');
            return false;
        }

        try {
            $html = $this->buildEmailHtml($sessionId, $photoUrl, $qrCodeBase64);

            $response = $this->client->post('emails', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'from' => "{$this->fromName} <{$this->fromEmail}>",
                    'to' => [$toEmail],
                    'subject' => '📸 Your Photobooth Photos - ' . $this->fromName,
                    'html' => $html,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode === 200 || $statusCode === 201) {
                Log::info("Email sent successfully to {$toEmail}");
                return true;
            }

            Log::error("Failed to send email. Status: {$statusCode}");
            return false;

        } catch (GuzzleException $e) {
            Log::error('Email sending failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Build email HTML content.
     */
    protected function buildEmailHtml(string $sessionId, string $photoUrl, string $qrCodeBase64): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 40px 30px;
        }
        .qr-section {
            text-align: center;
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .qr-section img {
            max-width: 300px;
            border: 5px solid #667eea;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .button {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            margin: 20px 0;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .session-info {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            padding: 20px;
            color: #777;
            font-size: 14px;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📸 Your Photobooth Memories!</h1>
        </div>

        <div class="content">
            <p style="font-size: 18px;">Hello! 👋</p>

            <p>Thank you for using <strong>{$this->fromName}</strong>! Your amazing photos are ready to view and download.</p>

            <div class="qr-section">
                <p style="margin-bottom: 20px;"><strong>Scan this QR code to access your photos:</strong></p>
                <img src="data:image/png;base64,{$qrCodeBase64}" alt="QR Code">
            </div>

            <div style="text-align: center;">
                <p><strong>Or click the button below:</strong></p>
                <a href="{$photoUrl}" class="button">🎉 View My Photos</a>
            </div>

            <div class="session-info">
                <p style="margin: 0;"><strong>Session ID:</strong> <code>{$sessionId}</code></p>
                <p style="margin: 5px 0 0 0;"><small>Keep this ID to retrieve your photos later</small></p>
            </div>

            <p style="color: #e74c3c; font-weight: bold;">⚠️ Important: Your photos will be available for 30 days. Make sure to download them!</p>

            <p>Enjoy your memories! 💖</p>
        </div>

        <div class="footer">
            <p>© {$this->fromName} | All rights reserved</p>
            <p style="font-size: 12px; margin-top: 10px;">This is an automated email. Please do not reply.</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
}
