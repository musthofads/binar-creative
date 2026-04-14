<?php

namespace App\Services;

use App\Models\SinglePhoto;
use App\Models\StripPhotoOriginal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PhotoService
{

    public function savePhoto(string $base64Image, string $sessionId, ?int $userId = null, array $metadata = []): SinglePhoto
    {
        // 1. Deteksi format gambar secara dinamis (PNG/JPG/WEBP)
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $extension = strtolower($type[1]);
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
        } else {
            $extension = 'png';
        }

        // 2. Decode data gambar
        $imageData = base64_decode(str_replace(' ', '+', $base64Image));
        if ($imageData === false) {
            throw new \Exception('Gagal melakukan decode data gambar.');
        }

        // 3. Logika Direktori Tanggal
        $datePath = date('Ymd');

        // 4. Generate nama file
        // Saya juga hilangkan prefix 'single_' di nama file agar konsisten dengan permintaanmu
        $filename = time() . '_' . Str::random(10) . '.' . $extension;

        // Struktur folder: photos/20260412/{sessionId}/{filename}
        $storagePath = "photos/{$datePath}/{$sessionId}/{$filename}";

        // 5. Simpan ke Storage
        Storage::disk('public')->put($storagePath, $imageData);

        // 6. Generate thumbnail
        $thumbnailPath = $this->generateThumbnail($storagePath, $sessionId, $extension);

        // 7. Simpan ke Database
        return SinglePhoto::create([
            'url'            => Storage::disk('public')->url($storagePath),
            'user_id'        => $userId,
            'session_id'     => $sessionId,
            'storage_path'   => $storagePath,
            'filename'       => $filename,
            'thumbnail_path' => $thumbnailPath,
            'metadata'       => $metadata,
            'queue_number'   => $metadata['photo_number'] ?? 0,
            'paid'           => false,
        ]);
    }

    /**
     * Save a single photo from base64 data.
     */
    public function saveSinglePhoto(string $base64Image, string $sessionId, ?int $userId = null, array $metadata = []): SinglePhoto
    {
        // 1. Perbaikan: Cara dinamis menghapus prefix base64 (bisa JPEG maupun PNG)
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $base64Image = substr($base64Image, strpos($base64Image, ',') + 1);
            $extension = strtolower($type[1]); // jpeg, png, etc
        } else {
            // Jika tidak ada prefix, asumsikan extension dari metadata atau default
            $extension = 'jpg';
        }

        // 2. Perbaikan karakter spasi
        $image = str_replace(' ', '+', $base64Image);
        $imageData = base64_decode($image);

        // Validasi apakah decode berhasil
        if ($imageData === false) {
            throw new \Exception('Gagal melakukan decode data gambar.');
        }

        // 3. Generate nama file dengan ekstensi yang sesuai
        $filename = 'single_' . time() . '_' . Str::random(10) . '.' . $extension;
        $storagePath = "photos/{$sessionId}/singles/{$filename}";

        // Save to storage
        Storage::disk('public')->put($storagePath, $imageData);

        // Generate thumbnail (Pastikan fungsi ini juga mendukung ekstensi file dinamis)
        $thumbnailPath = $this->generateThumbnail($storagePath, $sessionId);

        // Save to database
        return SinglePhoto::create([
            'url' => Storage::disk('public')->url($storagePath),
            'user_id' => $userId,
            'session_id' => $sessionId,
            'storage_path' => $storagePath,
            'filename' => $filename,
            'thumbnail_path' => $thumbnailPath,
            'metadata' => $metadata,
            'queue_number' => $metadata['photo_number'] ?? 0, // Ambil dari metadata jika ada
            'paid' => false,
        ]);
    }

    /**
     * Save a strip photo original.
     */
    public function saveStripPhotoOriginal(string $stripPath, string $sessionId, ?int $userId = null, array $metadata = []): StripPhotoOriginal
    {
        $filename = basename($stripPath);

        return StripPhotoOriginal::create([
            'url' => Storage::disk('public')->url($stripPath),
            'user_id' => $userId,
            'session_id' => $sessionId,
            'storage_path' => $stripPath,
            'filename' => $filename,
            'thumbnail_path' => null,
            'metadata' => $metadata,
            'queue_number' => 0,
            'paid' => false,
        ]);
    }

    /**
     * Generate thumbnail for image.
     */
    protected function generateThumbnail(string $imagePath, string $sessionId): ?string
    {
        try {
            $fullPath = Storage::disk('public')->path($imagePath);

            // --- KOREKSI UTAMA ---
            // Gunakan imagecreatefromstring untuk deteksi format otomatis (JPG/PNG)
            $fileContent = file_get_contents($fullPath);
            $source = imagecreatefromstring($fileContent);

            if (!$source) {
                \Illuminate\Support\Facades\Log::error('Gagal membaca gambar. Format tidak didukung: ' . $fullPath);
                return null;
            }

            $width = imagesx($source);
            $height = imagesy($source);

            // Sesuai koreksi sebelumnya: Tinggi maksimal 200px agar UI tetap proporsional
            $thumbnailHeight = 200;
            $thumbnailWidth = (int)(($thumbnailHeight / $height) * $width);

            $thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

            // Support transparansi jika sewaktu-waktu ada PNG masuk
            imagealphablending($thumbnail, false);
            imagesavealpha($thumbnail, true);

            imagecopyresampled(
                $thumbnail, $source,
                0, 0, 0, 0,
                $thumbnailWidth, $thumbnailHeight,
                $width, $height
            );

            $thumbnailFilename = 'thumb_' . basename($imagePath);
            $thumbnailPath = "photos/{$sessionId}/thumbnails/{$thumbnailFilename}";
            $thumbnailFullPath = Storage::disk('public')->path($thumbnailPath);

            // Pastikan folder exists
            $dir = dirname($thumbnailFullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Simpan sebagai JPEG (lebih ringan untuk thumbnail)
            // Gunakan imagejpeg karena source aslinya adalah JPEG
            imagejpeg($thumbnail, $thumbnailFullPath, 85);

            imagedestroy($source);
            imagedestroy($thumbnail);

            return $thumbnailPath;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Thumbnail generation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get photos by session.
     */
    public function getPhotosBySession(string $sessionId)
    {
        return SinglePhoto::where('session_id', $sessionId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    /**
     * Delete photo.
     */
    public function deletePhoto(int $photoId, string $type = 'single'): bool
    {
        $model = $type === 'single' ? SinglePhoto::class : StripPhotoOriginal::class;
        $photo = $model::find($photoId);

        if (!$photo) {
            return false;
        }

        // Delete files from storage
        Storage::disk('public')->delete($photo->storage_path);
        if ($photo->thumbnail_path) {
            Storage::disk('public')->delete($photo->thumbnail_path);
        }

        // Delete from database
        $photo->delete();

        return true;
    }
}
