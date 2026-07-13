<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class R2Helper
{
    /**
     * Upload an UploadedFile to Cloudflare R2 and return the public URL.
     * Automatically resizes the image if it is an image.
     *
     * @param UploadedFile $file
     * @param string $folder  Subfolder inside the bucket (e.g. 'eateries', 'checkins')
     * @return string  Public URL via R2_PUBLIC_URL domain
     */
    public static function upload(UploadedFile $file, string $folder = 'general'): string
    {
        $mimeType = $file->getClientMimeType();
        $isImage = str_starts_with($mimeType, 'image/');
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $safeName  = $folder . '/' . time() . '_' . Str::random(8) . '.' . $extension;

        $resizedContent = null;
        if ($isImage) {
            try {
                $resizedContent = self::resizeImageGd($file->getRealPath(), $mimeType, 1200);
            } catch (\Throwable $e) {
                Log::warning('[R2Helper] Resize image failed, using original: ' . $e->getMessage());
            }
        }

        $content = $resizedContent !== null ? $resizedContent : file_get_contents($file->getRealPath());

        try {
            Storage::disk('r2')->put($safeName, $content, 'public');
            return rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $safeName;
        } catch (\Throwable $e) {
            Log::error('[R2Helper] Upload failed: ' . $e->getMessage());
            // Fallback to local public storage
            return self::fallbackLocal($file, $folder, $resizedContent);
        }
    }

    /**
     * Upload raw binary content (e.g. decoded base64) to R2 and return public URL.
     * Automatically resizes the image if it is an image.
     *
     * @param string $binaryContent  Raw file bytes
     * @param string $extension      File extension (jpg, png, …)
     * @param string $folder         Subfolder inside the bucket
     * @return string  Public URL
     */
    public static function uploadRaw(string $binaryContent, string $extension, string $folder = 'general'): string
    {
        $safeName = $folder . '/' . time() . '_' . Str::random(8) . '.' . $extension;

        $resizedContent = null;
        try {
            $resizedContent = self::resizeImageBinaryGd($binaryContent, $extension, 1200);
        } catch (\Throwable $e) {
            Log::warning('[R2Helper] uploadRaw resize failed: ' . $e->getMessage());
        }

        $content = $resizedContent !== null ? $resizedContent : $binaryContent;

        try {
            Storage::disk('r2')->put($safeName, $content, 'public');
            return rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $safeName;
        } catch (\Throwable $e) {
            Log::error('[R2Helper] uploadRaw failed: ' . $e->getMessage());
            // Fallback to local public path
            $dir = public_path('uploads/' . $folder);
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            $fileName = time() . '_' . Str::random(8) . '.' . $extension;
            file_put_contents($dir . '/' . $fileName, $content);
            return '/uploads/' . $folder . '/' . $fileName;
        }
    }

    /**
     * Fallback: save to local public/uploads when R2 is unavailable.
     */
    private static function fallbackLocal(UploadedFile $file, string $folder, $resizedContent = null): string
    {
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $fileName  = time() . '_' . Str::random(8) . '.' . $extension;
        $destPath  = public_path('uploads/' . $folder);

        if (!file_exists($destPath)) {
            mkdir($destPath, 0755, true);
        }

        if ($resizedContent !== null) {
            file_put_contents($destPath . '/' . $fileName, $resizedContent);
        } else {
            $file->move($destPath, $fileName);
        }
        return '/uploads/' . $folder . '/' . $fileName;
    }

    /**
     * Resize raw image binary using GD.
     */
    private static function resizeImageBinaryGd(string $binaryContent, string $extension, int $maxDimension = 1200)
    {
        $extension = strtolower($extension);
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return false;
        }

        $sourceImage = @imagecreatefromstring($binaryContent);
        if (!$sourceImage) {
            return false;
        }

        $origWidth = imagesx($sourceImage);
        $origHeight = imagesy($sourceImage);
        if (!$origWidth || !$origHeight) {
            imagedestroy($sourceImage);
            return false;
        }

        $width = $origWidth;
        $height = $origHeight;

        if ($origWidth > $maxDimension || $origHeight > $maxDimension) {
            if ($origWidth > $origHeight) {
                $width = $maxDimension;
                $height = (int) round(($origHeight * $maxDimension) / $origWidth);
            } else {
                $height = $maxDimension;
                $width = (int) round(($origWidth * $maxDimension) / $origHeight);
            }
        }

        $targetImage = imagecreatetruecolor($width, $height);

        // Preserve transparency
        if (in_array($extension, ['png', 'gif'])) {
            imagealphablending($targetImage, false);
            imagesavealpha($targetImage, true);
            $transparent = imagecolorallocatealpha($targetImage, 255, 255, 255, 127);
            imagefilledrectangle($targetImage, 0, 0, $width, $height, $transparent);
        }

        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0, 0, 0, 0,
            $width, $height,
            $origWidth, $origHeight
        );

        ob_start();
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                imagejpeg($targetImage, null, 80); // 80% quality
                break;
            case 'png':
                imagepng($targetImage, null, 7); // compression level 0-9
                break;
            case 'gif':
                imagegif($targetImage);
                break;
            case 'webp':
                imagewebp($targetImage, null, 80); // 80% quality
                break;
        }

        $resizedBinary = ob_get_clean();

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return $resizedBinary;
    }

    /**
     * Resize an image file using GD to a maximum dimension, preserving aspect ratio.
     */
    private static function resizeImageGd(string $sourcePath, string $mimeType, int $maxDimension = 1200)
    {
        $extension = 'jpg';
        switch ($mimeType) {
            case 'image/jpeg':
            case 'image/jpg':
                $extension = 'jpg';
                break;
            case 'image/png':
                $extension = 'png';
                break;
            case 'image/gif':
                $extension = 'gif';
                break;
            case 'image/webp':
                $extension = 'webp';
                break;
            default:
                return false;
        }

        $binaryContent = @file_get_contents($sourcePath);
        if (!$binaryContent) {
            return false;
        }

        return self::resizeImageBinaryGd($binaryContent, $extension, $maxDimension);
    }
}
