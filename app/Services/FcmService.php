<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Send FCM Push Notification to a target device token.
     *
     * @param string|null $fcmToken
     * @param string $title
     * @param string $body
     * @param array $data Additional payload data
     * @return bool
     */
    public static function sendNotification(?string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (empty($fcmToken)) {
            return false;
        }

        try {
            $serverKey = config('services.firebase.server_key') ?? env('FCM_SERVER_KEY');
            
            // Legacy / Direct HTTP API endpoint
            if ($serverKey) {
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
                } else {
                    Log::warning('FCM Push Notification failed', ['status' => $response->status(), 'response' => $response->body()]);
                }
            } else {
                Log::info('FCM Notification skipped: FCM_SERVER_KEY not set in .env');
            }
        } catch (\Throwable $e) {
            Log::error('FCM Notification exception: ' . $e->getMessage());
        }

        return false;
    }
}
