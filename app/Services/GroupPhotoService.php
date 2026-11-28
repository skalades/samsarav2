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
     * Draw professional watermark banner with SAMSARA branding on the bottom of the image.
     *
     * Layout (bottom banner, 2-panel):
     *  ┌─────────────────────────────────────────────────────────────────────┐
     *  │ ░ ACCENT LINE (orange) ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░ │
     *  │  [LOGO PANEL]  │  [INFO PANEL]                                      │
     *  │   SAMSARA      │  REG: xxx  KETUA: yyy                              │
     *  │   ⛰ MTN NAME  │  WAKTU: ...  MANIFEST TERVALIDASI                 │
     *  └─────────────────────────────────────────────────────────────────────┘
     */
    protected function applyWatermark(&$image, int $width, int $height, SimaksiRegistration $registration): void
    {
        // --- Sizing ---
        $bannerHeight = max(110, (int) round($height * 0.18));
        $startY       = $height - $bannerHeight;

        // --- Colours ---
        $bgDark    = imagecolorallocatealpha($image, 8, 10, 16, 20);   // ~92 % opaque near-black
        $bgPanel   = imagecolorallocatealpha($image, 20, 25, 35, 30);  // slightly lighter panel bg
        $orange    = imagecolorallocate($image, 255, 85, 30);
        $orangeDim = imagecolorallocate($image, 180, 55, 15);
        $white     = imagecolorallocate($image, 255, 255, 255);
        $offWhite  = imagecolorallocate($image, 220, 225, 232);
        $emerald   = imagecolorallocate($image, 52, 211, 153);
        $divider   = imagecolorallocate($image, 60, 70, 90);

        imagealphablending($image, true);

        // Full banner background
        imagefilledrectangle($image, 0, $startY, $width, $height, $bgDark);

        // Top accent line (orange, 5 px)
        imagefilledrectangle($image, 0, $startY, $width, $startY + 5, $orange);

        // --- Font paths ---
        $fontBold    = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $fontRegular = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');
        $hasTtf      = function_exists('imagettftext') && file_exists($fontBold) && file_exists($fontRegular);

        // --- Expedition profile ---
        $expProfile = AppSetting::getExpeditionProfile();
        $mountain   = strtoupper($expProfile['mountain_name'] ?? 'GUNUNG CIKURAY');
        $expName    = strtoupper($expProfile['expedition_name'] ?? 'SAMSARA EXPEDITION');

        // --- Content strings ---
        $reg         = $registration->registration_code;
        $leaderName  = strtoupper($registration->leader_name);
        $totalPeople = (int) $registration->total_climbers;
        $timestamp   = Carbon::now()->translatedFormat('d M Y, H:i') . ' WIB';

        $logoLine1 = $expName;
        $logoLine2 = '⛰ ' . $mountain;

        $infoLine1 = "REG: {$reg}  •  KETUA: {$leaderName} ({$totalPeople} PENDAKI)";
        $infoLine2 = "WAKTU: {$timestamp}";
        $infoLine3 = '✔ MANIFEST TERVALIDASI RESMI';

        if ($hasTtf) {
            // --- Adaptive font sizes ---
            $base          = max(10, (int) round($width / 90));   // ~15–17 px at 1400 px wide
            $sizeLogoMain  = $base + 4;
            $sizeLogoSub   = $base + 1;
            $sizeInfoMain  = $base + 2;
            $sizeInfoSub   = $base;
            $sizeVerify    = $base - 1;

            // --- Logo panel width: 28 % of image ---
            $logoPanelW = (int) round($width * 0.28);
            $dividerX   = $logoPanelW;

            // Logo panel subtle background
            imagefilledrectangle($image, 0, $startY + 6, $dividerX - 1, $height, $bgPanel);

            // Vertical divider line
            imagefilledrectangle($image, $dividerX, $startY + 6, $dividerX + 2, $height, $divider);

            // --- Logo panel: centred horizontally in $logoPanelW ---
            $innerPadX = max(14, (int) round($width * 0.015));

            // Measure logo line 1 to centre it
            $bb1 = imagettfbbox($sizeLogoMain, 0, $fontBold, $logoLine1);
            $tw1 = abs($bb1[4] - $bb1[0]);
            $lx1 = max($innerPadX, (int) round(($logoPanelW - $tw1) / 2));

            $bb2 = imagettfbbox($sizeLogoSub, 0, $fontRegular, $logoLine2);
            $tw2 = abs($bb2[4] - $bb2[0]);
            $lx2 = max($innerPadX, (int) round(($logoPanelW - $tw2) / 2));

            // Vertical placement (vertically centred in banner)
            $contentH = $bannerHeight - 6;    // minus accent line
            $ly1 = $startY + 6 + (int) round($contentH * 0.42);
            $ly2 = $startY + 6 + (int) round($contentH * 0.76);

            // Orange stroke effect for logo main line (draw bg copy 1 px offset)
            imagettftext($image, $sizeLogoMain, 0, $lx1 + 1, $ly1 + 1, $orangeDim, $fontBold, $logoLine1);
            imagettftext($image, $sizeLogoMain, 0, $lx1,     $ly1,     $orange,    $fontBold, $logoLine1);

            // Sub line: mountain name (off-white)
            imagettftext($image, $sizeLogoSub, 0, $lx2, $ly2, $offWhite, $fontRegular, $logoLine2);

            // --- Info panel ---
            $infoPadX = $dividerX + max(16, (int) round($width * 0.018));

            $iy1 = $startY + 6 + (int) round($contentH * 0.32);
            $iy2 = $startY + 6 + (int) round($contentH * 0.60);
            $iy3 = $startY + 6 + (int) round($contentH * 0.86);

            // Line 1: REG + KETUA (white bold)
            imagettftext($image, $sizeInfoMain, 0, $infoPadX, $iy1, $white, $fontBold, $infoLine1);

            // Line 2: timestamp (off-white regular)
            imagettftext($image, $sizeInfoSub, 0, $infoPadX, $iy2, $offWhite, $fontRegular, $infoLine2);

            // Line 3: verification badge (emerald)
            imagettftext($image, $sizeVerify, 0, $infoPadX, $iy3, $emerald, $fontBold, $infoLine3);

        } else {
            // --- GD built-in font fallback (no TTF) ---
            $px = 14;
            $sy = $startY + 10;
            imagestring($image, 5, $px, $sy,       $logoLine1, $orange);
            imagestring($image, 3, $px, $sy + 22,  $logoLine2, $offWhite);
            imagestring($image, 4, $px + (int)round($width * 0.28) + 10, $sy,      $infoLine1, $white);
            imagestring($image, 3, $px + (int)round($width * 0.28) + 10, $sy + 22, $infoLine2, $offWhite);
            imagestring($image, 3, $px + (int)round($width * 0.28) + 10, $sy + 44, $infoLine3, $emerald);
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
