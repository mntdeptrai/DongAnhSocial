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
     * @param int $maxDimension Maximum width/height for image resizing (default 1200px)
     * @return string  Public URL via R2_PUBLIC_URL domain
     */
    public static function upload(UploadedFile $file, string $folder = 'general', int $maxDimension = 1200): string
    {
        $mimeType = $file->getClientMimeType();
        $isImage = str_starts_with($mimeType, 'image/');
        $extension = $file->getClientOriginalExtension() ?: 'jpg';
        $safeName  = $folder . '/' . time() . '_' . Str::random(8) . '.' . $extension;

        $resizedContent = null;
        if ($isImage) {
            try {
                $resizedContent = self::resizeImageGd($file->getRealPath(), $mimeType, $maxDimension);
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
     * Batch upload multiple UploadedFile instances to Cloudflare R2.
     * Supports images (auto-resized) and videos (original quality).
     *
     * @param array $files Array of UploadedFile objects
     * @param string $folder Subfolder inside bucket
     * @param int $maxDimension Maximum dimension for image resize
     * @return array Array of uploaded media descriptors
     */
    public static function uploadMultiple(array $files, string $folder = 'general', int $maxDimension = 1200): array
    {
        $results = [];
        foreach ($files as $file) {
            if (!($file instanceof UploadedFile) || !$file->isValid()) {
                continue;
            }
            $mimeType = $file->getClientMimeType();
            $fileType = str_starts_with($mimeType, 'video/') ? 'video' : 'image';
            $url = self::upload($file, $folder, $maxDimension);

            $results[] = [
                'original_name' => $file->getClientOriginalName(),
                'stored_name'   => basename($url),
                'url'           => $url,
                'size'          => $file->getSize(),
                'mime_type'     => $mimeType,
                'file_type'     => $fileType,
            ];
        }
        return $results;
    }

    /**
     * Upload a single chunk of a large video file and automatically merge into R2 when complete.
     * Reduces server bandwidth spikes & memory overload for large video uploads.
     *
     * @param UploadedFile $chunk Incoming chunk file
     * @param string $uploadId Unique upload session identifier
     * @param int $chunkIndex 0-indexed chunk position
     * @param int $totalChunks Total number of chunks expected
     * @param string $folder Subfolder inside R2 bucket
     * @return array Status payload with progress % or final R2 URL
     */
    public static function uploadChunk(UploadedFile $chunk, string $uploadId, int $chunkIndex, int $totalChunks, string $folder = 'videos'): array
    {
        $safeUploadId = preg_replace('/[^a-zA-Z0-9_\-]/', '', $uploadId);
        $tempDir = storage_path('app/chunks/' . $safeUploadId);
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }

        $chunkFileName = 'chunk_' . sprintf('%04d', $chunkIndex);
        $chunk->move($tempDir, $chunkFileName);

        $receivedFiles = glob($tempDir . '/chunk_*');
        $receivedCount = count($receivedFiles);

        if ($receivedCount >= $totalChunks) {
            $extension = strtolower($chunk->getClientOriginalExtension()) ?: 'mp4';
            $finalFilename = time() . '_' . Str::random(8) . '.' . $extension;
            $mergedPath = $tempDir . '/' . $finalFilename;

            $out = fopen($mergedPath, 'wb');
            for ($i = 0; $i < $totalChunks; $i++) {
                $partFile = $tempDir . '/chunk_' . sprintf('%04d', $i);
                if (file_exists($partFile)) {
                    $in = fopen($partFile, 'rb');
                    stream_copy_to_stream($in, $out);
                    fclose($in);
                }
            }
            fclose($out);

            $safeName = $folder . '/' . $finalFilename;
            $content = file_get_contents($mergedPath);

            try {
                Storage::disk('r2')->put($safeName, $content, 'public');
                $finalUrl = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $safeName;
            } catch (\Throwable $e) {
                Log::error('[R2Helper] Chunk merge R2 upload failed: ' . $e->getMessage());
                $destDir = public_path('uploads/' . $folder);
                if (!file_exists($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                rename($mergedPath, $destDir . '/' . $finalFilename);
                $finalUrl = '/uploads/' . $folder . '/' . $finalFilename;
            }

            // Cleanup temp chunk files
            array_map('unlink', glob("$tempDir/*"));
            @rmdir($tempDir);

            return [
                'completed'     => true,
                'progress'      => 100,
                'url'           => $finalUrl,
                'stored_name'   => $finalFilename,
                'total_chunks'  => $totalChunks
            ];
        }

        $progress = round(($receivedCount / $totalChunks) * 100, 2);
        return [
            'completed'       => false,
            'progress'        => $progress,
            'received_chunks' => $receivedCount,
            'total_chunks'    => $totalChunks
        ];
    }

    /**
     * Slice a large video into small binary segment files (e.g. 5MB per segment)
     * and upload each segment to R2 for fast chunked video streaming load.
     *
     * @param UploadedFile $file The video file
     * @param string $folder Subfolder inside R2 bucket
     * @param int $chunkSizeBytes Chunk size in bytes (default 5MB = 5,242,880 bytes)
     * @return array Segment URLs and master metadata
     */
    public static function splitVideoIntoSegments(UploadedFile $file, string $folder = 'videos', int $chunkSizeBytes = 5242880): array
    {
        $realPath = $file->getRealPath();
        $fileSize = filesize($realPath);

        // If file is smaller than chunk size, upload as single video
        if ($fileSize <= $chunkSizeBytes) {
            $singleUrl = self::upload($file, $folder);
            return [
                'is_chunked'     => false,
                'total_segments' => 1,
                'master_url'     => $singleUrl,
                'segments'       => [$singleUrl]
            ];
        }

        $extension = strtolower($file->getClientOriginalExtension()) ?: 'mp4';
        $baseName  = time() . '_' . Str::random(8);
        $handle    = fopen($realPath, 'rb');
        $segmentUrls = [];
        $partIndex   = 0;

        while (!feof($handle)) {
            $buffer = fread($handle, $chunkSizeBytes);
            if ($buffer === false || strlen($buffer) === 0) {
                break;
            }

            $segFilename = $baseName . '_seg_' . sprintf('%03d', $partIndex) . '.' . $extension;
            $safeName    = $folder . '/' . $segFilename;

            try {
                Storage::disk('r2')->put($safeName, $buffer, 'public');
                $segUrl = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $safeName;
            } catch (\Throwable $e) {
                Log::error('[R2Helper] Segment R2 upload failed: ' . $e->getMessage());
                $destDir = public_path('uploads/' . $folder);
                if (!file_exists($destDir)) {
                    mkdir($destDir, 0755, true);
                }
                file_put_contents($destDir . '/' . $segFilename, $buffer);
                $segUrl = '/uploads/' . $folder . '/' . $segFilename;
            }

            $segmentUrls[] = $segUrl;
            $partIndex++;
        }
        fclose($handle);

        // Create master descriptor JSON for playlist playback
        $masterMetadata = [
            'is_chunked'     => true,
            'total_segments' => count($segmentUrls),
            'chunk_size_mb'  => round($chunkSizeBytes / (1024 * 1024), 2),
            'segments'       => $segmentUrls
        ];

        $masterFilename = $baseName . '_master.json';
        $safeMasterName = $folder . '/' . $masterFilename;

        try {
            Storage::disk('r2')->put($safeMasterName, json_encode($masterMetadata, JSON_PRETTY_PRINT), 'public');
            $masterUrl = rtrim(env('R2_PUBLIC_URL'), '/') . '/' . $safeMasterName;
        } catch (\Throwable $e) {
            $masterUrl = $segmentUrls[0];
        }

        return [
            'is_chunked'     => true,
            'total_segments' => count($segmentUrls),
            'master_url'     => $masterUrl,
            'segments'       => $segmentUrls
        ];
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
