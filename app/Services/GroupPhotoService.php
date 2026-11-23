<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\SimaksiRegistration;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GroupPhotoService
{
    protected string $disk = 'public';

    /**
     * Process, watermark, compress and store group photo for a registration.
     */
    public function processAndStore(SimaksiRegistration $registration, UploadedFile $file): string
    {
        $filePath = $file->getRealPath();
        $mime = $file->getMimeType();

        // 1. Create GD image resource based on mime type
        $image = match ($mime) {
            'image/jpeg', 'image/jpg' => @imagecreatefromjpeg($filePath),
            'image/png' => @imagecreatefrompng($filePath),
            'image/webp' => @imagecreatefromwebp($filePath),
            default => null,
        };

        if (! $image) {
            // Fallback attempt with string data
            $fileData = file_get_contents($filePath);
            $image = @imagecreatefromstring($fileData);
        }

        if (! $image) {
            throw new \RuntimeException('Format gambar tidak didukung atau berkas rusak. Gunakan format JPG, PNG, atau WebP.');
        }

        // 2. Auto-rotate based on EXIF orientation if available (common in mobile cameras)
        if (function_exists('exif_read_data') && in_array($mime, ['image/jpeg', 'image/jpg'])) {
            try {
                $exif = @exif_read_data($filePath);
                if (! empty($exif['Orientation'])) {
                    $image = match ($exif['Orientation']) {
                        3 => imagerotate($image, 180, 0),
                        6 => imagerotate($image, -90, 0),
                        8 => imagerotate($image, 90, 0),
                        default => $image,
                    };
                }
            } catch (\Throwable $e) {
                // Ignore exif reading failure
            }
        }

        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        // 3. Proportional downscale if too large (standardize max width: 1400px)
        $maxWidth = 1400;
        if ($origWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) round(($origHeight / $origWidth) * $newWidth);

            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);
            imagedestroy($image);
            $image = $resized;
            $width = $newWidth;
            $height = $newHeight;
        } else {
            $width = $origWidth;
            $height = $origHeight;
        }

        // 4. Apply Watermark Overlay
        $this->applyWatermark($image, $width, $height, $registration);

        // 5. Prepare target storage directory: group_photos/YYYY/MM
        $now = Carbon::now();
        $dir = 'group_photos/'.$now->format('Y/m');
        if (! Storage::disk($this->disk)->exists($dir)) {
            Storage::disk($this->disk)->makeDirectory($dir);
        }

        // Remove old group photo file if exists
        if ($registration->group_photo_path && Storage::disk($this->disk)->exists($registration->group_photo_path)) {
            Storage::disk($this->disk)->delete($registration->group_photo_path);
        }

        $cleanCode = preg_replace('/[^A-Za-z0-9_-]/', '', $registration->registration_code);
        $fileName = $cleanCode.'_'.time().'.webp';
        $relativeStoragePath = $dir.'/'.$fileName;
        $fullSavePath = Storage::disk($this->disk)->path($relativeStoragePath);

        // Save as WebP with 82% quality (great compression, sharp text)
        $saved = imagewebp($image, $fullSavePath, 82);
        imagedestroy($image);

        if (! $saved) {
            throw new \RuntimeException('Gagal menyimpan berkas foto regu ke penyimpanan server.');
        }

        // 6. Update registration record
        $registration->update([
            'group_photo_path' => $relativeStoragePath,
            'group_photo_uploaded_at' => $now,
        ]);

        return $relativeStoragePath;
    }

    /**
     * Draw modern dark overlay with SAR metadata on the image canvas.
     */
    protected function applyWatermark(&$image, int $width, int $height, SimaksiRegistration $registration): void
    {
        $bannerHeight = max(100, (int) round($height * 0.16));
        $startY = $height - $bannerHeight;

        // Banner background: semi-transparent black
        imagealphablending($image, true);
        $blackAlpha = imagecolorallocatealpha($image, 10, 14, 20, 24); // ~90% opacity dark slate
        imagefilledrectangle($image, 0, $startY, $width, $height, $blackAlpha);

        // Top accent line: flame orange
        $orange = imagecolorallocate($image, 255, 87, 34);
        imagefilledrectangle($image, 0, $startY, $width, $startY + 4, $orange);

        // Colors
        $white = imagecolorallocate($image, 255, 255, 255);
        $emerald = imagecolorallocate($image, 52, 211, 153);
        $amber = imagecolorallocate($image, 251, 191, 36);
        $gray = imagecolorallocate($image, 203, 213, 225);

        // Font discovery
        $fontBold = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $fontRegular = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $hasTtf = function_exists('imagettftext') && file_exists($fontBold);

        $expProfile = AppSetting::getExpeditionProfile();
        $mountain = $expProfile['mountain_name'] ?? 'Gunung Cikuray';
        $expName = $expProfile['expedition_name'] ?? 'SAMSARA EXPEDITION';

        $timestampStr = Carbon::now()->translatedFormat('d M Y, H:i').' WIB';
        $line1 = "{$expName} - {$mountain} • BASECAMP CHECK-IN";
        $line2 = "REG: {$registration->registration_code} • KETUA: {$registration->leader_name} ({$registration->total_climbers} PENDAKI)";
        $line3 = "WAKTU: {$timestampStr} • MANIFEST TERVALIDASI RESMI";

        if ($hasTtf) {
            $baseFontSize = max(11, (int) round($width * 0.016));
            $fontSizeL1 = $baseFontSize + 2;
            $fontSizeL2 = $baseFontSize + 3;
            $fontSizeL3 = $baseFontSize;

            $paddingX = max(18, (int) round($width * 0.025));
            $y1 = $startY + (int) ($bannerHeight * 0.32);
            $y2 = $startY + (int) ($bannerHeight * 0.64);
            $y3 = $startY + (int) ($bannerHeight * 0.90);

            // Line 1: Orange Tag
            imagettftext($image, $fontSizeL1, 0, $paddingX, $y1, $orange, $fontBold, $line1);

            // Line 2: Bold White
            imagettftext($image, $fontSizeL2, 0, $paddingX, $y2, $white, $fontBold, $line2);

            // Line 3: Emerald verification
            imagettftext($image, $fontSizeL3, 0, $paddingX, $y3, $emerald, $fontRegular, $line3);
        } else {
            // Built-in GD font fallback
            $paddingX = 15;
            imagestring($image, 4, $paddingX, $startY + 12, $line1, $orange);
            imagestring($image, 5, $paddingX, $startY + 35, $line2, $white);
            imagestring($image, 4, $paddingX, $startY + 65, $line3, $emerald);
        }
    }

    /**
     * Purge group photos older than retention days (default 7 days).
     */
    public function purgeExpiredPhotos(int $days = 7): int
    {
        $cutoff = Carbon::now()->subDays($days);

        // Find registrations completed or with return_date older than cutoff
        $expiredRegistrations = SimaksiRegistration::whereNotNull('group_photo_path')
            ->where(function ($q) use ($cutoff) {
                $q->where(function ($sub) use ($cutoff) {
                    $sub->where('status', 'completed')
                        ->where('checked_out_at', '<=', $cutoff);
                })->orWhere(function ($sub) use ($cutoff) {
                    $sub->where('return_date', '<=', $cutoff);
                })->orWhere(function ($sub) use ($cutoff) {
                    $sub->where('group_photo_uploaded_at', '<=', $cutoff)
                        ->whereIn('status', ['completed', 'rejected']);
                });
            })
            ->get();

        $purgedCount = 0;
        foreach ($expiredRegistrations as $reg) {
            try {
                if ($reg->group_photo_path && Storage::disk($this->disk)->exists($reg->group_photo_path)) {
                    Storage::disk($this->disk)->delete($reg->group_photo_path);
                }

                $reg->update([
                    'group_photo_path' => null,
                ]);

                $purgedCount++;
            } catch (\Throwable $e) {
                Log::warning("Gagal membersihkan foto regu SIMAKSI {$reg->registration_code}: ".$e->getMessage());
            }
        }

        return $purgedCount;
    }
}
