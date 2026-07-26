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
            // Check for Service Account JSON file first (Firebase V1 API recommended by Google)
            $serviceAccountPath = env('FCM_CREDENTIALS_PATH', storage_path('app/firebase-service-account.json'));

            if (file_exists($serviceAccountPath)) {
                $serviceAccount = json_decode(file_get_contents($serviceAccountPath), true);
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
            openssl_sign($signatureInput, $signature, $serviceAccount['private_key'], 'SHA256');
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
