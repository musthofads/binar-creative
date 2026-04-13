<?php

namespace App\Http\Controllers;

// Added: Import Controller base class
use App\Http\Controllers\Controller;

// Added: Import models and services
use App\Models\PhotoboothSession;
use App\Services\QrCodeService;

// Added: Import Request and facades
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SessionController extends Controller
{
    protected QrCodeService $qrCodeService;

    public function __construct(QrCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function initSession(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'package' => 'required|in:basic,bestie,ramean',
        ]);

        $sessionId = (string) Str::uuid();
        session()->put('photo_session_id', $sessionId);

        // Simpan ke database termasuk nama dan paket
        PhotoboothSession::create([
            'session_id' => $sessionId,
            'user_id' => Auth::id(),
            'customer_name' => $request->customer_name,
            'access_password' => rand(100000, 999999),
            'package_type' => $request->package,
            'photo_count' => 0,
            'strip_generated' => false,
            'last_activity' => now(),
            'metadata' => [
                'device' => $request->header('User-Agent')
            ],
        ]);

        return response()->json([
            'success' => true,
            'session_id' => $sessionId
        ]);
    }

    /**
     * Create new photobooth session.
     *
     * @route POST /api/session/create
     */
    public function create(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'template_type' => 'nullable|string|in:classic_strip,triple_strip,grid_2x3,grid_4x4,single_photo',
            'required_photos' => 'nullable|integer|min:1|max:6',
            'metadata' => 'nullable|array',
        ]);

        try {
            // Generate unique session ID and code
            $sessionId = Str::uuid()->toString();
            $sessionCode = strtoupper(Str::random(6));

            // Generate QR code URL
            $qrUrl = url("/preview?session={$sessionId}");
            $qrData = $this->qrCodeService->generateQrCode($sessionId, $qrUrl);

            // Determine required photos based on template
            $templateType = $request->template_type ?? 'grid_4x4';
            $requiredPhotos = $request->required_photos ?? match($templateType) {
                'classic_strip' => 4,
                'triple_strip' => 3,
                'grid_2x3' => 6,
                'grid_4x4' => 4,
                'single_photo' => 1,
                default => 4,
            };

            // Create session
            $session = PhotoboothSession::create([
                'session_id' => $sessionId,
                'session_code' => $sessionCode,
                'template_type' => $templateType,
                'required_photos' => $requiredPhotos,
                'qr_code_path' => $qrData['path'],
                'qr_code_url' => $qrData['url'],
                'user_id' => $request->user_id ?? Auth::id(),
                'metadata' => $request->metadata ?? [],
                'last_activity' => now(),
            ]);

            return response()->json([
                'success' => true,
                'session' => [
                    'session_id' => $session->session_id,
                    'session_code' => $session->session_code,
                    'template_type' => $session->template_type,
                    'required_photos' => $session->required_photos,
                    'qr_code_url' => $session->qr_code_url,
                    'qr_data_uri' => $qrData['data_uri'],
                    'preview_url' => $qrUrl,
                    'created_at' => $session->created_at,
                ],
                'message' => 'Session created successfully',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Session creation failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create session: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get session details.
     *
     * @route GET /api/session/{sessionId}
     */
    public function show(string $sessionId)
    {
        $session = PhotoboothSession::where('session_id', $sessionId)
            ->with(['photos', 'stripPhotos'])
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        // Update last activity
        $session->update(['last_activity' => now()]);

        return response()->json([
            'success' => true,
            'session' => [
                'session_id' => $session->session_id,
                'qr_code_url' => $session->qr_code_url,
                'photo_count' => $session->photo_count,
                'strip_generated' => $session->strip_generated,
                'strip_path' => $session->strip_path,
                'metadata' => $session->metadata,
                'created_at' => $session->created_at,
                'last_activity' => $session->last_activity,
            ],
        ]);
    }

    /**
     * Update session activity.
     *
     * @route PATCH /api/session/{sessionId}/activity
     */
    public function updateActivity(string $sessionId)
    {
        $session = PhotoboothSession::where('session_id', $sessionId)->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        $session->update(['last_activity' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Activity updated',
        ]);
    }

    /**
     * Delete session and all related photos.
     * Fixed: Use Auth facade for authentication
     *
     * @route DELETE /api/session/{sessionId}
     */
    public function destroy(string $sessionId)
    {
        // Fixed: Use Auth facade for proper type inference
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $session = PhotoboothSession::where('session_id', $sessionId)->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Session not found',
            ], 404);
        }

        // Delete all photos in session
        $session->photos()->delete();
        $session->stripPhotos()->delete();

        // Delete session
        $session->delete();

        return response()->json([
            'success' => true,
            'message' => 'Session deleted successfully',
        ]);
    }
}
