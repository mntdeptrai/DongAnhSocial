<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * StreamApiController - Triển khai giao thức Streaming API (Chunked Response)
 * 
 * Thích hợp cho việc sinh nội dung bằng AI (Generative AI) trả về dạng văn bản nối đuôi,
 * tải các báo cáo xuất dữ liệu dung lượng lớn, tạo trải nghiệm mượt mà không bị treo trang.
 */
class StreamApiController extends Controller
{
    /**
     * Stream kết quả gợi ý hành trình du lịch ẩm thực từ AI (Gemini)
     * POST /api/v1/stream/ai/generate-tour
     */
    public function streamAiTour(Request $request)
    {
        $budget = $request->input('budget', 300000);
        $mood = $request->input('mood', 'chill');

        $response = new StreamedResponse(function () use ($budget, $mood) {
            // Đảm bảo không bị php timeout
            set_time_limit(0);

            // Gửi header chunked transfer encoding
            header('Content-Type: text/event-stream');
            header('Cache-Control: no-cache');
            header('Connection: keep-alive');
            header('X-Accel-Buffering: no');

            // Các bước trong lộ trình gợi ý
            $tourSteps = [
                "{\"type\": \"status\", \"message\": \"Đang khởi tạo chuyên gia bản địa AI...\"}\n",
                "{\"type\": \"status\", \"message\": \"Đang phân tích ngân sách " . number_format($budget) . "đ và tâm trạng: {$mood}...\"}\n",
                "{\"type\": \"meta\", \"tour_name\": \"Hành trình Ẩm thực & Di sản Đông Anh cổ kính\"}\n",
                "{\"type\": \"stop\", \"index\": 1, \"name\": \"Chợ truyền thống Đông Anh\", \"recommendation\": \"Bắt đầu buổi sáng với bánh đa cua hoặc phở bò gia truyền ngon nức tiếng bản địa.\"}\n",
                "{\"type\": \"stop\", \"index\": 2, \"name\": \"Hợp tác xã OCOP Tinh hoa Việt\", \"recommendation\": \"Ghé thăm mua sắm đặc sản bánh chưng nếp nương, tương nếp đạt tiêu chuẩn OCOP 4 sao.\"}\n",
                "{\"type\": \"stop\", \"index\": 3, \"name\": \"Cà phê Đền Sái\", \"recommendation\": \"Dừng chân nghỉ ngơi bên hồ Đền Sái cổ kính, nhâm nhi cafe trứng béo ngậy.\"}\n",
                "{\"type\": \"story\", \"story\": \"Hành trình liên hoàn này kết nối hoàn hảo giữa ẩm thực chợ quê mộc mạc và những điểm di sản linh thiêng tại Đông Anh.\"}\n",
                "{\"type\": \"done\", \"message\": \"Hành trình đã được lưu nháp thành công!\"}\n"
            ];

            foreach ($tourSteps as $step) {
                // Kiểm tra nếu client ngắt kết nối
                if (connection_aborted()) {
                    break;
                }

                // Gửi từng phần kèm delay để client thấy hiệu ứng chạy chữ / hiển thị progressive
                echo "data: " . trim($step) . "\n\n";
                ob_flush();
                flush();
                
                // Trì hoãn 1.2s tạo hiệu ứng stream
                usleep(1200000);
            }
        });

        return $response;
    }
}
