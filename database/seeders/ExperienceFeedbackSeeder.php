<?php

namespace Database\Seeders;

use App\Models\ExperienceFeedback;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ExperienceFeedbackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Don't duplicate if already seeded
        if (ExperienceFeedback::count() > 0) {
            return;
        }

        $defaultFeedbacks = [
            [
                'author_name'   => 'Lê Thu Mai (Gia đình)',
                'activity_name' => 'Tour trải nghiệm trồng rau tại Cổ Loa',
                'rating'        => 5,
                'is_verified'   => true,
                'helpful_count' => 28,
                'comment'       => 'Hai bé nhà mình thích mê! Lần đầu tiên các con được mặc áo bà ba đi ủng lội vườn, tự tay gieo hạt và gánh nước tưới rau. Rau mang về xào thịt bò ngọt lịm. Chắc chắn sẽ quay lại vào mùa gieo củ tiếp theo!',
                'image_url'     => 'https://images.unsplash.com/photo-1592417817098-8f3d69109853?auto=format&fit=crop&w=600&q=80',
                'host_reply'    => [
                    'author'  => 'HTX Nông Nghiệp Sinh Thái Cổ Loa',
                    'comment' => 'Cảm ơn gia đình chị Mai! Vườn rau luôn sẵn sàng chào đón hai bé quay lại trải nghiệm mùa thu hoạch bắp cải nhé ạ ❤️',
                ],
                'status'        => 'approved',
                'created_at'    => now()->subDay(),
            ],
            [
                'author_name'   => 'Trần Tuấn Anh (CLB Khám Phá)',
                'activity_name' => 'Game Mật Mã Loa Thành',
                'rating'        => 5,
                'is_verified'   => true,
                'helpful_count' => 34,
                'comment'       => 'Game cuốn dã man! Nhóm mình 6 đứa chạy khắp 3 vòng hào thành giải mật thư, vừa vận động vừa hiểu thêm bao nhiêu tích sử của An Dương Vương và nỏ thần. Cuối game còn được tặng huy hiệu lưu niệm rất xịn.',
                'image_url'     => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80',
                'host_reply'    => [
                    'author'  => 'BQL Di Tích & Đội Trọng Tài Loa Thành',
                    'comment' => 'Chúc mừng team Tuấn Anh đã giải phá kỷ lục trạm Giếng Ngọc xuất sắc trong 1h45p! Hẹn gặp lại team trong bản đồ mở rộng sắp tới.',
                ],
                'status'        => 'approved',
                'created_at'    => now()->subDays(3),
            ],
            [
                'author_name'   => 'Hoàng Đình Khang & Vợ',
                'activity_name' => 'Ăn tối tại nhà dân Cổ Loa',
                'rating'        => 5,
                'is_verified'   => true,
                'helpful_count' => 42,
                'comment'       => 'Một trải nghiệm ấm áp và bình yên vô cùng. Gia đình bác chủ nhà nhiệt tình xởi lởi, mâm cơm quê có gà hấp lá chanh, canh cua đồng và cà pháo chuẩn vị Bắc xưa. Cảm giác như được về quê thăm ông bà vậy!',
                'image_url'     => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80',
                'host_reply'    => [
                    'author'  => 'Bác Thu (Chủ nhà dân thôn Chùa)',
                    'comment' => 'Bác gửi lời chúc sức khỏe hai cháu nhé! Lần tới ghé Đông Anh nhớ nhắn trước để bác chuẩn bị thêm cá chép om dưa mộc mạc nhé.',
                ],
                'status'        => 'approved',
                'created_at'    => now()->subDays(5),
            ],
            [
                'author_name'   => 'Nguyễn Thị Bích Ngọc',
                'activity_name' => 'Tự tay làm bún Mạch Tràng cổ truyền',
                'rating'        => 5,
                'is_verified'   => true,
                'helpful_count' => 19,
                'comment'       => 'Tự tay xoay cối xay bột gạo ngâm chua mới thấy các cụ ngày xưa kỳ công thế nào. Bún Mạch Tràng ép ra nước sôi vớt lên ăn ngay thơm phức, không hề có hàn the hay chất bảo quản. Rất đáng thử!',
                'image_url'     => 'https://images.unsplash.com/photo-1556910103-1c02745aae4d?auto=format&fit=crop&w=600&q=80',
                'host_reply'    => null,
                'status'        => 'approved',
                'created_at'    => now()->subDays(7),
            ],
            [
                'author_name'   => 'Đặng Văn Hùng (Nhóm Phượt Thủ Đô)',
                'activity_name' => 'Bắn nỏ liên châu Âu Lạc',
                'rating'        => 5,
                'is_verified'   => true,
                'helpful_count' => 25,
                'comment'       => 'Lẫy nỏ bắn có lực và rất chính xác! Anh hướng dẫn viên chỉ cách ngắm bắn bia cỏ theo kỹ thuật phục dựng nỏ thần An Dương Vương. Cả nhóm thi đấu bắn bia rồi tự tay đúc tiền đồng lưu niệm cực kỳ hào hứng.',
                'image_url'     => 'https://images.unsplash.com/photo-1518709268805-4e9042af9f23?auto=format&fit=crop&w=600&q=80',
                'host_reply'    => [
                    'author'  => 'Tổ Hướng Dẫn Trải Nghiệm Cổ Loa',
                    'comment' => 'Cảm ơn bạn Hùng! Nỏ thần là niềm tự hào quân sự ngàn năm của ông cha, rất vui khi các bạn trẻ đón nhận nhiệt tình như vậy.',
                ],
                'status'        => 'approved',
                'created_at'    => now()->subDays(10),
            ],
            [
                'author_name'   => 'Bùi Thanh Hương (GV Ngữ Văn)',
                'activity_name' => 'Tham quan di tích lịch sử Thành Cổ Loa',
                'rating'        => 5,
                'is_verified'   => true,
                'helpful_count' => 31,
                'comment'       => 'Đoàn học sinh khối 7 của trường rất xúc động khi nghe cô thuyết minh viên truyền cảm hứng về truyền thuyết Trọng Thủy - Mỵ Châu và hào thành xoắn ốc. Một tiết học lịch sử thực địa tuyệt vời hơn bất kỳ sách vở nào!',
                'image_url'     => null,
                'host_reply'    => null,
                'status'        => 'approved',
                'created_at'    => now()->subDays(14),
            ],
            [
                'author_name'   => 'Phạm Minh Đức (Nhiếp ảnh gia)',
                'activity_name' => 'Ăn tối tại nhà dân Cổ Loa',
                'rating'        => 5,
                'is_verified'   => true,
                'helpful_count' => 22,
                'comment'       => 'Không gian sân vườn dưới gốc nhãn cổ thụ rợp bóng mát, ánh đèn vàng ấm áp và mâm cơm gia đình mộc mạc đậm đà. Mình chụp được rất nhiều khoảnh khắc bình dị và xúc động của nếp nhà Bắc Bộ xưa.',
                'image_url'     => 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=600&q=80',
                'host_reply'    => null,
                'status'        => 'approved',
                'created_at'    => now()->subDays(16),
            ],
            [
                'author_name'   => 'Vũ Hoàng Yến (CLB Sống Xanh)',
                'activity_name' => 'Tour trải nghiệm trồng rau tại Cổ Loa',
                'rating'        => 5,
                'is_verified'   => true,
                'helpful_count' => 17,
                'comment'       => 'Mô hình nông nghiệp tuần hoàn và sinh thái rất bài bản. Bác nông dân chỉ dẫn từ cách ủ phân hữu cơ đến cách thu hoạch nhẹ tay để cây tiếp tục đâm chồi. Rau tươi ngọt tự nhiên mang về ai cũng khen.',
                'image_url'     => null,
                'host_reply'    => null,
                'status'        => 'approved',
                'created_at'    => now()->subDays(20),
            ],
        ];

        // Also import existing JSON feedbacks if any
        $filePath = storage_path('app/exp_feedbacks.json');
        if (File::exists($filePath)) {
            $jsonFeedbacks = json_decode(File::get($filePath), true) ?: [];
            foreach ($jsonFeedbacks as $fb) {
                $defaultFeedbacks[] = [
                    'author_name'   => $fb['author_name'] ?? 'Du khách',
                    'activity_name' => $fb['activity_name'] ?? 'Trải nghiệm bản địa',
                    'rating'        => $fb['rating'] ?? 5,
                    'is_verified'   => true,
                    'helpful_count' => rand(5, 20),
                    'comment'       => $fb['comment'] ?? '',
                    'image_url'     => $fb['image_url'] ?? null,
                    'host_reply'    => null,
                    'status'        => 'approved',
                    'created_at'    => now(),
                ];
            }
        }

        foreach ($defaultFeedbacks as $data) {
            ExperienceFeedback::create($data);
        }
    }
}
