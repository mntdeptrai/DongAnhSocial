<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;

class GoogleDriveHelper
{
    /**
     * Upload an uploaded file to Google Drive and return the direct streamable URL.
     * Fallbacks to local storage if Google Drive credentials are not fully set.
     *
     * @param UploadedFile $file
     * @param string $folderName Subfolder under public_path for fallback (e.g., 'videos', 'eateries', 'dishes')
     * @return string Direct streamable link (Google Drive or local path)
     */
    public static function upload(UploadedFile $file, string $folderName = 'general')
    {
        $clientId = env('GOOGLE_DRIVE_CLIENT_ID');
        $clientSecret = env('GOOGLE_DRIVE_CLIENT_SECRET');
        $refreshToken = env('GOOGLE_DRIVE_REFRESH_TOKEN');
        $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $safeName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $extension;

        // Fallback condition if config is missing
        if (empty($clientId) || empty($clientSecret) || empty($refreshToken)) {
            Log::info("Google Drive credentials not set. Falling back to local storage.");
            return self::saveLocal($file, $folderName, $safeName);
        }

        try {
            // 1. Get Access Token from Refresh Token
            $client = new \GuzzleHttp\Client();
            $tokenResponse = $client->post('https://oauth2.googleapis.com/token', [
                'form_params' => [
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'refresh_token' => $refreshToken,
                    'grant_type' => 'refresh_token',
                ]
            ]);

            $tokenData = json_decode($tokenResponse->getBody(), true);
            if (!isset($tokenData['access_token'])) {
                throw new \Exception("Could not retrieve access token from Google API.");
            }
            $accessToken = $tokenData['access_token'];

            // 2. Upload file via Google Drive Multipart API
            $metadata = [
                'name' => $safeName
            ];
            if (!empty($folderId)) {
                $metadata['parents'] = [$folderId];
            }

            $multipart = [
                [
                    'name' => 'metadata',
                    'contents' => json_encode($metadata),
                    'headers' => ['Content-Type' => 'application/json; charset=UTF-8']
                ],
                [
                    'name' => 'file',
                    'contents' => fopen($file->getRealPath(), 'r'),
                    'headers' => ['Content-Type' => $file->getMimeType()]
                ]
            ];

            $uploadResponse = $client->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                ],
                'multipart' => $multipart
            ]);

            $uploadData = json_decode($uploadResponse->getBody(), true);
            if (!isset($uploadData['id'])) {
                throw new \Exception("Upload failed. File ID not returned from Google Drive.");
            }
            $fileId = $uploadData['id'];

            // 3. Make the uploaded file publicly readable
            $client->post("https://www.googleapis.com/drive/v3/files/{$fileId}/permissions", [
                'headers' => [
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type' => 'application/json'
                ],
                'json' => [
                    'role' => 'reader',
                    'type' => 'anyone'
                ]
            ]);

            // 4. Construct direct public streamable URL
            $streamUrl = "https://drive.google.com/uc?export=download&id=" . $fileId;
            Log::info("Successfully uploaded file to Google Drive: " . $streamUrl);
            return $streamUrl;

        } catch (\Exception $e) {
            Log::error("Google Drive Upload Error: " . $e->getMessage() . " | Falling back to local storage.");
            return self::saveLocal($file, $folderName, $safeName);
        }
    }

    /**
     * Save the file to public local path and return local relative path.
     */
    private static function saveLocal(UploadedFile $file, string $folderName, string $fileName)
    {
        $destPath = public_path('uploads/' . $folderName);
        if (!file_exists($destPath)) {
            mkdir($destPath, 0777, true);
        }
        $file->move($destPath, $fileName);
        return '/uploads/' . $folderName . '/' . $fileName;
    }
}
