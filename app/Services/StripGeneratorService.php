<?php

namespace App\Services;

// Added: Import models
use App\Models\SinglePhoto;
use App\Models\Photo;

// Added: Import facades
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class StripGeneratorService
{
    protected int $stripWidth = 1800;
    protected int $stripHeight = 1200;
    protected int $photoWidth = 400;
    protected int $photoHeight = 600;
    protected int $spacing = 50;

    /**
     * Generate strip based on template type.
     */
    public function generateStripByTemplate(string $sessionId, string $templateType, ?int $userId = null): ?Photo
    {
        return match($templateType) {
            'classic_strip' => $this->generateClassicStrip($sessionId, $userId, 4),
            'triple_strip' => $this->generateClassicStrip($sessionId, $userId, 3),
            'grid_2x3' => $this->generateGridStrip($sessionId, $userId, 2, 3),
            'grid_4x4' => $this->generateGridStrip($sessionId, $userId, 2, 2),
            'single_photo' => $this->generateSinglePhoto($sessionId, $userId),
            default => $this->generateStrip($sessionId, $userId),
        };
    }

    /**
     * Generate classic vertical strip.
     */
    public function generateClassicStrip(string $sessionId, ?int $userId = null, int $photoCount = 4): ?Photo
    {
        $singlePhotos = SinglePhoto::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->limit($photoCount)
            ->get();

        if ($singlePhotos->count() < $photoCount) {
            return null;
        }

        try {
            // Dimensions for classic strip
            $stripWidth = 800;
            $stripHeight = $photoCount * 600 + ($photoCount + 1) * 50; // 600px per photo + spacing
            $photoWidth = 700;
            $photoHeight = 550;
            $spacing = 50;

            $strip = imagecreatetruecolor($stripWidth, $stripHeight);
            $white = imagecolorallocate($strip, 255, 255, 255);
            imagefill($strip, 0, 0, $white);

            $y = $spacing;
            foreach ($singlePhotos as $singlePhoto) {
                $this->addPhotoToCanvas($strip, $singlePhoto->storage_path, $spacing, $y, $photoWidth, $photoHeight);
                $y += $photoHeight + $spacing;
            }

            return $this->saveStrip($strip, $sessionId, $userId, $singlePhotos->count(), 'classic_strip');
        } catch (\Exception $e) {
            Log::error('Classic strip generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate grid strip.
     */
    public function generateGridStrip(string $sessionId, ?int $userId = null, int $cols = 2, int $rows = 2): ?Photo
    {
        $photoCount = $cols * $rows;
        $singlePhotos = SinglePhoto::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->limit($photoCount)
            ->get();

        if ($singlePhotos->count() < $photoCount) {
            return null;
        }

        try {
            // Dimensions for grid
            $spacing = 40;
            $photoWidth = 600;
            $photoHeight = 600;
            $stripWidth = $cols * $photoWidth + ($cols + 1) * $spacing;
            $stripHeight = $rows * $photoHeight + ($rows + 1) * $spacing;

            $strip = imagecreatetruecolor($stripWidth, $stripHeight);
            $white = imagecolorallocate($strip, 255, 255, 255);
            imagefill($strip, 0, 0, $white);

            $index = 0;
            for ($row = 0; $row < $rows; $row++) {
                for ($col = 0; $col < $cols; $col++) {
                    if ($index >= $singlePhotos->count()) break 2;

                    $x = $spacing + $col * ($photoWidth + $spacing);
                    $y = $spacing + $row * ($photoHeight + $spacing);

                    $this->addPhotoToCanvas($strip, $singlePhotos[$index]->storage_path, $x, $y, $photoWidth, $photoHeight);
                    $index++;
                }
            }

            return $this->saveStrip($strip, $sessionId, $userId, $singlePhotos->count(), "grid_{$cols}x{$rows}");
        } catch (\Exception $e) {
            Log::error('Grid strip generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate single photo.
     */
    public function generateSinglePhoto(string $sessionId, ?int $userId = null): ?Photo
    {
        $singlePhoto = SinglePhoto::where('session_id', $sessionId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$singlePhoto) {
            return null;
        }

        try {
            $stripWidth = 1200;
            $stripHeight = 1600;
            $photoWidth = 1100;
            $photoHeight = 1500;
            $spacing = 50;

            $strip = imagecreatetruecolor($stripWidth, $stripHeight);
            $white = imagecolorallocate($strip, 255, 255, 255);
            imagefill($strip, 0, 0, $white);

            $this->addPhotoToCanvas($strip, $singlePhoto->storage_path, $spacing, $spacing, $photoWidth, $photoHeight);

            return $this->saveStrip($strip, $sessionId, $userId, 1, 'single_photo');
        } catch (\Exception $e) {
            Log::error('Single photo generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Add photo to canvas.
     */
    protected function addPhotoToCanvas($canvas, string $storagePath, int $x, int $y, int $width, int $height): void
    {
        $photoPath = Storage::disk('public')->path($storagePath);

        if (!file_exists($photoPath)) {
            return;
        }

        // Detect image type
        $imageInfo = getimagesize($photoPath);
        $source = match($imageInfo[2] ?? IMAGETYPE_PNG) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($photoPath),
            IMAGETYPE_PNG => imagecreatefrompng($photoPath),
            default => imagecreatefrompng($photoPath),
        };

        if (!$source) {
            return;
        }

        imagecopyresampled(
            $canvas,
            $source,
            $x,
            $y,
            0,
            0,
            $width,
            $height,
            imagesx($source),
            imagesy($source)
        );

        imagedestroy($source);
    }

    /**
     * Save strip to storage and database.
     */
    protected function saveStrip($strip, string $sessionId, ?int $userId, int $photoCount, string $type): Photo
    {
        $filename = 'strip_' . $type . '_' . time() . '_' . Str::random(10) . '.png';
        $storagePath = "photos/{$sessionId}/strips/{$filename}";
        $fullPath = Storage::disk('public')->path($storagePath);

        // Create directory if not exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        imagepng($strip, $fullPath);
        imagedestroy($strip);

        // Save to database
        return Photo::create([
            'url' => Storage::disk('public')->url($storagePath),
            'user_id' => $userId,
            'session_id' => $sessionId,
            'storage_path' => $storagePath,
            'filename' => $filename,
            'thumbnail_path' => null,
            'metadata' => [
                'type' => $type,
                'photo_count' => $photoCount,
            ],
            'queue_number' => 0,
            'paid' => false,
        ]);
    }

    /**
     * Generate strip from single photos.
     */
    public function generateStrip(string $sessionId, ?int $userId = null): ?Photo
    {
        // Get all single photos for this session
        $singlePhotos = SinglePhoto::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->limit(4)
            ->get();

        if ($singlePhotos->count() < 4) {
            return null;
        }

        try {
            // Create strip canvas
            $strip = imagecreatetruecolor($this->stripWidth, $this->stripHeight);
            $white = imagecolorallocate($strip, 255, 255, 255);
            imagefill($strip, 0, 0, $white);

            // Add each photo to strip
            $x = $this->spacing;
            foreach ($singlePhotos as $index => $singlePhoto) {
                $photoPath = Storage::disk('public')->path($singlePhoto->storage_path);

                if (!file_exists($photoPath)) {
                    continue;
                }

                $source = imagecreatefrompng($photoPath);
                if (!$source) {
                    continue;
                }

                // Calculate Y position (center vertically)
                $y = ($this->stripHeight - $this->photoHeight) / 2;

                // Resize and copy photo to strip
                imagecopyresampled(
                    $strip,
                    $source,
                    $x,
                    (int)$y,
                    0,
                    0,
                    $this->photoWidth,
                    $this->photoHeight,
                    imagesx($source),
                    imagesy($source)
                );

                imagedestroy($source);
                $x += $this->photoWidth + $this->spacing;
            }

            // Save strip
            $filename = 'strip_' . time() . '_' . Str::random(10) . '.png';
            $storagePath = "photos/{$sessionId}/strips/{$filename}";
            $fullPath = Storage::disk('public')->path($storagePath);

            // Create directory if not exists
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            imagepng($strip, $fullPath);
            imagedestroy($strip);

            // Save to database
            $photo = Photo::create([
                'url' => Storage::disk('public')->url($storagePath),
                'user_id' => $userId,
                'session_id' => $sessionId,
                'storage_path' => $storagePath,
                'filename' => $filename,
                'thumbnail_path' => null,
                'metadata' => [
                    'type' => 'strip',
                    'photo_count' => $singlePhotos->count(),
                ],
                'queue_number' => 0,
                'paid' => false,
            ]);

            return $photo;
        } catch (\Exception $e) {
            // Fixed: Use Log facade instead of backslash prefix
            Log::error('Strip generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate strip with custom layout.
     */
    public function generateCustomStrip(array $photoPaths, string $sessionId, ?int $userId = null): ?Photo
    {
        try {
            $strip = imagecreatetruecolor($this->stripWidth, $this->stripHeight);
            $white = imagecolorallocate($strip, 255, 255, 255);
            imagefill($strip, 0, 0, $white);

            $x = $this->spacing;
            foreach ($photoPaths as $photoPath) {
                if (!file_exists($photoPath)) {
                    continue;
                }

                $source = imagecreatefrompng($photoPath);
                if (!$source) {
                    continue;
                }

                $y = ($this->stripHeight - $this->photoHeight) / 2;

                imagecopyresampled(
                    $strip,
                    $source,
                    $x,
                    (int)$y,
                    0,
                    0,
                    $this->photoWidth,
                    $this->photoHeight,
                    imagesx($source),
                    imagesy($source)
                );

                imagedestroy($source);
                $x += $this->photoWidth + $this->spacing;
            }

            $filename = 'strip_custom_' . time() . '_' . Str::random(10) . '.png';
            $storagePath = "photos/{$sessionId}/strips/{$filename}";
            $fullPath = Storage::disk('public')->path($storagePath);

            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            imagepng($strip, $fullPath);
            imagedestroy($strip);

            return Photo::create([
                'url' => Storage::disk('public')->url($storagePath),
                'user_id' => $userId,
                'session_id' => $sessionId,
                'storage_path' => $storagePath,
                'filename' => $filename,
                'thumbnail_path' => null,
                'metadata' => [
                    'type' => 'custom_strip',
                    'photo_count' => count($photoPaths),
                ],
                'queue_number' => 0,
                'paid' => false,
            ]);
        } catch (\Exception $e) {
            // Fixed: Use Log facade instead of backslash prefix
            Log::error('Custom strip generation failed: ' . $e->getMessage());
            return null;
        }
    }
}
