<?php

namespace App\Http\Controllers;

// Added: Import Controller base class
use App\Http\Controllers\Controller;

// Added: Import Mail class and facades
use App\Mail\PhotoMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

// Note: QrCode facade import - ensure endroid/qr-code package is installed
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrController extends Controller
{
    /**
     * Send QR code with photo link via email.
     */
    public function sendQr(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'photo_url' => 'required|url',
            'session_id' => 'required|string',
        ]);

        try {
            // Generate QR code
            $qrCode = QrCode::format('png')
                ->size(300)
                ->generate($request->photo_url);

            // Send email
            Mail::to($request->email)->send(new PhotoMail(
                $request->photo_url,
                $request->session_id,
                $qrCode
            ));

            return response()->json([
                'success' => true,
                'message' => 'QR code sent successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send QR code: ' . $e->getMessage(),
            ], 500);
        }
    }
}
