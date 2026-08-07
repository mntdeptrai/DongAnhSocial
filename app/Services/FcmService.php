<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send FCM Push Notification to a target device token.
     * Supports both Firebase V1 (Service Account JSON) and Legacy Server Key.
     */
    public static function sendNotification(?string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (empty($fcmToken)) {
            return false;
        }

        try {
            // Auto detect Service Account JSON file in storage/app/ or storage/app/private/
            $customPath = env('FCM_CREDENTIALS_PATH');
            $primaryPath = storage_path('app/private/firebase-service-account.json');
            $secondaryPath = storage_path('app/firebase-service-account.json');

            $serviceAccountPath = null;
            if ($customPath && file_exists($customPath)) {
                $serviceAccountPath = $customPath;
            } else if (file_exists($primaryPath)) {
                $serviceAccountPath = $primaryPath;
            } else if (file_exists($secondaryPath)) {
                $serviceAccountPath = $secondaryPath;
            }

            $serviceAccount = null;
            $envJson = env('FCM_SERVICE_ACCOUNT_JSON');
            if (!empty($envJson)) {
                $serviceAccount = is_array($envJson) ? $envJson : json_decode($envJson, true);
            } else if ($serviceAccountPath && file_exists($serviceAccountPath)) {
                $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
            }

            if (!empty($serviceAccount) && is_array($serviceAccount)) {
                $projectId = $serviceAccount['project_id'] ?? null;

                if ($projectId) {
                    $token = self::getAccessToken($serviceAccount);
                    if ($token) {
                        $response = Http::withToken($token)
                            ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                                'message' => [
                                    'token' => $fcmToken,
                                    'notification' => [
                                        'title' => $title,
                                        'body'  => $body,
                                    ],
                                    'android' => [
                                        'priority' => 'high',
                                        'notification' => [
                                            'sound' => 'default',
                                            'channel_id' => 'dong_anh_social_channel',
                                            'priority' => 'HIGH',
                                            'visibility' => 'PUBLIC',
                                        ],
                                    ],
                                    'data' => array_map('strval', array_merge([
                                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                                    ], $data)),
                                ],
                            ]);

                        if ($response->successful()) {
                            Log::info('🔥 FCM V1 Push Notification Sent Successfully', ['to' => substr($fcmToken, 0, 15) . '...']);
                            return true;
                        }
                    }
                }
            }

            // Fallback to Legacy Server Key HTTP API if set
            $serverKey = config('services.firebase.server_key') ?? env('FCM_SERVER_KEY');
            if ($serverKey && $serverKey !== 'your_firebase_server_key') {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'to' => $fcmToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                        'sound' => 'default',
                        'badge' => 1,
                    ],
                    'data' => array_merge([
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    ], $data),
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    Log::info('FCM Push Notification Sent Successfully', ['to' => substr($fcmToken, 0, 15) . '...']);
                    return true;
                }
            } else {
                Log::info('FCM Notification skipped: Add firebase-service-account.json or FCM_SERVER_KEY to .env');
            }
        } catch (\Throwable $e) {
            Log::error('FCM Notification exception: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Generate OAuth2 Access Token from Service Account JSON.
     */
    private static function getAccessToken(array $serviceAccount): ?string
    {
        try {
            $now = time();
            $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = base64_encode(json_encode([
                'iss'   => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'exp'   => $now + 3600,
                'iat'   => $now,
            ]));

            $signatureInput = $header . '.' . $payload;
            $privateKey = str_replace('\n', "\n", $serviceAccount['private_key']);
            openssl_sign($signatureInput, $signature, $privateKey, 'SHA256');
            $jwt = $signatureInput . '.' . base64_encode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }
        } catch (\Throwable $e) {
            Log::error('FCM getAccessToken error: ' . $e->getMessage());
        }
        return null;
    }
}
