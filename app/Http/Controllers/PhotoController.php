<?php

namespace App\Http\Controllers;

// Added: Import Controller base class
use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\PhotoboothSession;
use App\Models\SinglePhoto;
use App\Models\StripPhotoOriginal;
use App\Services\PhotoService;
use App\Services\StripGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class PhotoController extends Controller
{
    protected PhotoService $photoService;
    protected StripGeneratorService $stripService;

    public function __construct(PhotoService $photoService, StripGeneratorService $stripService)
    {
        $this->photoService = $photoService;
        $this->stripService = $stripService;
    }

    /**
     * Save single photo from camera.
     */
    public function saveSinglePhoto(Request $request)
    {
        $request->validate([
            'image' => 'required|string',
            'session_id' => 'required|string',
        ]);

        try {
            $session = PhotoboothSession::where('id', $request->session_id)
                ->where('user_id', Auth::id())
                ->first();

            // 🔒 Validasi session
            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session tidak valid atau sudah direset',
                ], 400);
            }

            // 🔒 Optional: cek status session
            if ($session->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Session sudah tidak aktif',
                ], 400);
            }

            $photo = $this->photoService->savePhoto(
                $request->image,
                $session->id, // pakai dari object, lebih aman
                Auth::id(),
                $request->metadata ?? []
            );

            return response()->json([
                'success' => true,
                'photo' => $photo,
                'message' => 'Photo saved successfully',
            ]);

        } catch (\Throwable $e) { // 🔥 lebih aman dari Exception
            Log::error('Save photo error', [
                'error' => $e->getMessage(),
                'session_id' => $request->session_id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save photo',
            ], 500);
        }
    }

    /**
     * Generate and save strip.
     */
    public function saveStrip(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        try {
            // Fixed: Use Auth facade for proper type inference
            $strip = $this->stripService->generateStrip(
                $request->session_id,
                Auth::id()
            );

            if (!$strip) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough photos to create strip. Need 4 photos.',
                ], 400);
            }

            // Fixed: Use Auth facade for proper type inference
            // Save as strip photo original
            $this->photoService->saveStripPhotoOriginal(
                $strip->storage_path,
                $request->session_id,
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'strip' => $strip,
                'message' => 'Strip generated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate strip: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get photos by session.
     */
    public function getPhotosBySession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $sessionId = $request->session_id;

        // Fixed: Use Auth facade for all authentication checks
        // Check if user has access to this session
        if (Auth::check() && !Auth::user()->isSuperAdmin()) {
            // Guest can only see their own photos
            $singlePhotos = SinglePhoto::where('session_id', $sessionId)
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            $stripPhotos = Photo::where('session_id', $sessionId)
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Admin or unauthenticated session-based access
            $singlePhotos = SinglePhoto::where('session_id', $sessionId)
                ->orderBy('created_at', 'desc')
                ->get();

            $stripPhotos = Photo::where('session_id', $sessionId)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'single_photos' => $singlePhotos,
            'strip_photos' => $stripPhotos,
        ]);
    }

    /**
     * Get all photos (admin only).
     */
    /**
     * Get all photos (admin only).
     * Fixed: Use Auth facade for authentication
     */
    public function getAllPhotos(Request $request)
    {
        // Fixed: Use Auth facade for proper type inference
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $perPage = $request->get('per_page', 20);

        $singlePhotos = SinglePhoto::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        $stripPhotos = Photo::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'single_photos' => $singlePhotos,
            'strip_photos' => $stripPhotos,
        ]);
    }

    /**
     * Delete photo.
     */
    /**
     * Delete photo.
     * Fixed: Use Auth facade for authentication
     */
    public function deletePhoto(Request $request, $id)
    {
        // Fixed: Use Auth facade for proper type inference
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $type = $request->get('type', 'single');
        $deleted = $this->photoService->deletePhoto($id, $type);

        if ($deleted) {
            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Photo not found',
        ], 404);
    }

    /**
     * Get sessions list.
     */
    /**
     * Get sessions list.
     * Fixed: Use Auth facade for authentication
     */
    public function getSessions(Request $request)
    {
        // Fixed: Use Auth facade for proper type inference
        if (!Auth::check() || !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $sessions = SinglePhoto::select('session_id')
            ->selectRaw('COUNT(*) as photo_count')
            ->selectRaw('MAX(created_at) as last_activity')
            ->groupBy('session_id')
            ->orderBy('last_activity', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Get queue for printing.
     */
    public function getQueue(Request $request)
    {
        $queue = Photo::where('queue_number', '>', 0)
            ->orderBy('queue_number', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'queue' => $queue,
        ]);
    }

    /**
     * Add to print queue.
     */
    public function addToQueue(Request $request, $id)
    {
        $request->validate([
            'type' => 'required|in:single,strip',
        ]);

        $model = $request->type === 'single' ? SinglePhoto::class : Photo::class;
        $photo = $model::find($id);

        if (!$photo) {
            return response()->json([
                'success' => false,
                'message' => 'Photo not found',
            ], 404);
        }

        // Get next queue number
        $maxQueue = Photo::max('queue_number');
        $photo->queue_number = $maxQueue + 1;
        $photo->save();

        return response()->json([
            'success' => true,
            'message' => 'Added to print queue',
            'queue_number' => $photo->queue_number,
        ]);
    }
}
