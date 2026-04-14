<?php

namespace App\Http\Controllers;

// Added: Import Controller base class
use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\PhotoboothSession;
use App\Models\SinglePhoto;
use App\Services\EmailService;
use App\Services\PhotoService;
use App\Services\QrCodeService;
use App\Services\StripGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PhotoUploadController extends Controller
{
    protected PhotoService $photoService;
    protected StripGeneratorService $stripService;
    protected EmailService $emailService;
    protected QrCodeService $qrCodeService;

    public function __construct(
        PhotoService $photoService,
        StripGeneratorService $stripService,
        EmailService $emailService,
        QrCodeService $qrCodeService
    ) {
        $this->photoService = $photoService;
        $this->stripService = $stripService;
        $this->emailService = $emailService;
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Upload photo to session.
     *
     * @route POST /api/photos/upload
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|exists:photobooth_sessions,session_id',
            'images'     => 'required|array',
            'images.*'   => 'required|string', // Base64 string
            'metadata'   => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors'  => $validator->errors(),
                'message' => 'Validation failed',
            ], 422);
        }

        try {
            $session = PhotoboothSession::where('session_id', $request->session_id)->first();

            // Pastikan session masih aktif/processing
            if ($session->status === 'completed') {
                return response()->json(['success' => false, 'message' => 'Session already finished'], 403);
            }

            $savedPhotos = [];

            foreach ($request->images as $base64Image) {
                // Hitung nomor foto secara dinamis berdasarkan jumlah di DB + 1
                $currentCount = $session->photo_count;

                $meta = $request->metadata ?? [];
                $meta['photo_number'] = $currentCount + 1;

                $photo = $this->photoService->savePhoto(
                    $base64Image,
                    $request->session_id,
                    Auth::id(),
                    $meta
                );

                // Increment count di session
                $session->increment('photo_count');
                $session->update(['last_activity' => now()]);

                $savedPhotos[] = [
                    'id'  => $photo->id,
                    'url' => $photo->url,
                ];
            }

            return response()->json([
                'success' => true,
                'photos'  => $savedPhotos,
                'message' => 'Photo saved successfully',
            ], 201);

        } catch (\Exception $e) {
            Log::error('Photo auto-save failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function delete(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string|exists:photobooth_sessions,session_id',
            'index'      => 'required|integer'
        ]);

        try {
            $session = PhotoboothSession::where('session_id', $request->session_id)->first();
            $targetNumber = $request->index + 1;

            $photo = SinglePhoto::where('session_id', $request->session_id)
                                ->where('queue_number', $targetNumber)
                                ->first();

           if ($photo) {
                // 1. Hapus file fisik menggunakan nama kolom yang benar: 'storage_path'
                if (!empty($photo->storage_path)) {
                    Storage::disk('public')->delete($photo->storage_path);
                }

                // 2. Hapus thumbnail jika kolomnya ada
                if (!empty($photo->thumbnail_path)) {
                    Storage::disk('public')->delete($photo->thumbnail_path);
                }

                // 3. Hapus record dari database
                $photo->delete();

                // 4. Update count di session
                $session->decrement('photo_count');

                // 5. Re-order queue_number agar tetap urut
                $remainingPhotos = $session->photos()->orderBy('queue_number')->get();
                foreach ($remainingPhotos as $idx => $p) {
                    $p->update(['queue_number' => $idx + 1]);
                }

                return response()->json(['success' => true, 'message' => 'Photo deleted successfully']);
            }

            return response()->json(['success' => false, 'message' => 'Photo not found'], 404);

        } catch (\Exception $e) {
            Log::error('Delete photo failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error: ' . $e->getMessage()], 500);
        }
    }

    public function clearSession(Request $request)
    {
        $request->validate([
            'session_id' => 'required|string|exists:photobooth_sessions,session_id',
        ]);

        try {
            $session = PhotoboothSession::where('session_id', $request->session_id)->first();

            // 1. Ambil semua foto dalam session ini (Model SinglePhoto)
            $photos = SinglePhoto::where('session_id', $request->session_id)->get();

            foreach ($photos as $photo) {
                // Hapus file original & thumbnail di storage
                if (!empty($photo->storage_path)) {
                    Storage::disk('public')->delete($photo->storage_path);
                }
                if (!empty($photo->thumbnail_path)) {
                    Storage::disk('public')->delete($photo->thumbnail_path);
                }
                // Hapus record di tabel single_photos
                $photo->delete();
            }

            // 2. Reset counter photo_count kembali ke 0
            $session->update([
                'photo_count' => 0,
                'last_activity' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Gallery reset for current session'
            ]);

        } catch (\Exception $e) {
            Log::error('Retake all failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to clear gallery'], 500);
        }
    }

    /**
     * Get photos by session ID.
     *
     * @route GET /api/photos?sessionId={sessionId}
     */
    public function getPhotosBySession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sessionId' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $sessionId = $request->query('sessionId');

        // Session isolation: only get photos from this session
        $singlePhotos = SinglePhoto::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($photo) {
                // Fixed: Use Storage facade instead of backslash prefix
                return [
                    'id' => $photo->id,
                    'url' => $photo->url,
                    'thumbnail_url' => $photo->thumbnail_path
                        ? Storage::disk('public')->url($photo->thumbnail_path)
                        : $photo->url,
                    'filename' => $photo->filename,
                    'metadata' => $photo->metadata,
                    'created_at' => $photo->created_at,
                ];
            });

        $stripPhotos = Photo::where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($photo) {
                return [
                    'id' => $photo->id,
                    'url' => $photo->url,
                    'filename' => $photo->filename,
                    'metadata' => $photo->metadata,
                    'created_at' => $photo->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'session_id' => $sessionId,
            'photos' => $singlePhotos,
            'strips' => $stripPhotos,
            'total_photos' => $singlePhotos->count(),
            'total_strips' => $stripPhotos->count(),
        ]);
    }

    /**
     * Save final edited strip from canvas.
     *
     * @route POST /api/strip/save
     */
    public function saveStrip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|exists:photobooth_sessions,session_id',
            'image' => 'required|string', // base64 PNG from canvas
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Save strip image
            $image = str_replace('data:image/png;base64,', '', $request->image);
            $image = str_replace(' ', '+', $image);
            $imageData = base64_decode($image);

            // Fixed: Use Str helper instead of backslash prefix
            $filename = 'strip_final_' . time() . '_' . Str::random(10) . '.png';
            $storagePath = "photos/{$request->session_id}/strips/{$filename}";

            // Fixed: Use Storage facade instead of backslash prefix
            Storage::disk('public')->put($storagePath, $imageData);

            // Fixed: Use Auth facade for proper type inference
            // Save to database
            $strip = Photo::create([
                'url' => Storage::disk('public')->url($storagePath),
                'user_id' => Auth::id(),
                'session_id' => $request->session_id,
                'storage_path' => $storagePath,
                'filename' => $filename,
                'metadata' => array_merge(
                    ['type' => 'final_strip', 'edited' => true],
                    $request->metadata ?? []
                ),
                'queue_number' => 0,
                'paid' => false,
            ]);

            // Update session
            $session = PhotoboothSession::where('session_id', $request->session_id)->first();
            if ($session) {
                $session->update([
                    'strip_generated' => true,
                    'strip_path' => $storagePath,
                    'last_activity' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'strip' => [
                    'id' => $strip->id,
                    'url' => $strip->url,
                    'filename' => $strip->filename,
                    'session_id' => $strip->session_id,
                    'created_at' => $strip->created_at,
                ],
                'message' => 'Strip saved successfully',
            ], 201);

        } catch (\Exception $e) {
            // Fixed: Use Log facade instead of backslash prefix
            Log::error('Strip save failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save strip: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send photos via email.
     *
     * @route POST /api/photos/send-email
     */
    public function sendEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string|exists:photobooth_sessions,session_id',
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $session = PhotoboothSession::where('session_id', $request->session_id)->first();

            if (!$session) {
                return response()->json([
                    'success' => false,
                    'message' => 'Session not found',
                ], 404);
            }

            // Generate preview URL
            $previewUrl = url("/preview?session={$request->session_id}");

            // Generate QR code base64
            $qrCodeBase64 = $this->qrCodeService->generateQrCodeBase64($previewUrl);

            // Send email
            $sent = $this->emailService->sendPhotoEmail(
                $request->email,
                $request->session_id,
                $previewUrl,
                $qrCodeBase64
            );

            if ($sent) {
                return response()->json([
                    'success' => true,
                    'message' => 'Email sent successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email',
            ], 500);

        } catch (\Exception $e) {
            // Fixed: Use Log facade instead of backslash prefix
            Log::error('Email sending failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }
}
