<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checkin;
use App\Models\Eatery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * SseApiController - Triển khai giao thức Server-Sent Events (SSE) (One-way Streaming)
 * 
 * Server tự động đẩy (push) các sự kiện thời gian thực (đơn hàng mới, check-in mới, thông báo)
 * xuống Mobile App & Web mà không cần Client phải liên tục gửi request polling.
 */
class SseApiController extends Controller
{
    /**
     * Luồng stream sự kiện realtime qua SSE
     * GET /api/v1/stream/events
     */
    public function streamEvents(Request $request)
    {
        $response = new StreamedResponse(function () use ($request) {
            $lastEventId = 0;
            $userId = $request->query('user_id');

            // Thiết lập headers cho SSE stream
            echo "Content-Type: text/event-stream\n";
            echo "Cache-Control: no-cache\n";
            echo "Connection: keep-alive\n";
            echo "X-Accel-Buffering: no\n\n"; // Tắt buffer của Nginx để stream lập tức

            $secondsToStream = 30; // Giới hạn stream thời gian chạy mỗi connection (thường kết nối lại)
            $startTime = time();

            while (true) {
                // Kiểm tra kết nối phía client đã đóng chưa
                if (connection_aborted()) {
                    break;
                }

                // Hạn chế timeout vô hạn
                if ((time() - $startTime) > $secondsToStream) {
                    echo "event: close\n";
                    echo "data: {\"message\": \"Reconnect time limit reached\"}\n\n";
                    ob_flush();
                    flush();
                    break;
                }

                // 1. Kiểm tra checkin mới trong hệ thống
                $latestCheckin = Checkin::with(['user', 'eatery'])
                    ->where('id', '>', $lastEventId)
                    ->orderBy('id', 'desc')
                    ->first();

                if ($latestCheckin) {
                    $lastEventId = $latestCheckin->id;
                    $payload = json_encode([
                        'id' => $latestCheckin->id,
                        'user_name' => $latestCheckin->user->name ?? 'Ai đó',
                        'eatery_name' => $latestCheckin->eatery->name ?? 'Cơ sở',
                        'message' => $latestCheckin->comment ?? 'vừa checkin',
                        'time' => $latestCheckin->created_at->toIso8601String()
                    ], JSON_UNESCAPED_UNICODE);

                    echo "event: checkin\n";
                    echo "data: {$payload}\n\n";
                }

                // 2. Gửi sự kiện nhịp tim để duy trì kết nối (Heartbeat ping)
                echo "event: ping\n";
                echo "data: {\"time\": " . time() . "}\n\n";

                // Flush buffer đẩy dữ liệu đi ngay
                ob_flush();
                flush();

                // Chờ 3 giây trước lần quét tiếp theo
                sleep(3);
            }
        });

        return $response;
    }
}
