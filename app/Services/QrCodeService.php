<?php

namespace App\Services;

// Added: Import Endroid QR Code library classes
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;

// Added: Import facades
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QrCodeService
{
    /**
     * Generate QR code for session.
     */
    public function generateQrCode(string $sessionId, string $url): array
    {
        try {
            // Create QR code (compatible with endroid/qr-code v6)
            $qrCode = QrCode::create($url)
                ->setSize(400)
                ->setMargin(10);

            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            // Save to storage
            $filename = "qr_{$sessionId}_" . time() . '.png';
            $path = "qrcodes/{$filename}";

            Storage::disk('public')->put($path, $result->getString());

            return [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'data_uri' => $result->getDataUri(),
            ];
        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate QR code as base64 string.
     */
    public function generateQrCodeBase64(string $url): string
    {
        try {
            $qrCode = QrCode::create($url)
                ->setSize(300)
                ->setMargin(10);

            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            return base64_encode($result->getString());
        } catch (\Exception $e) {
            Log::error('QR Code generation failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
