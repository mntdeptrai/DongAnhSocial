<?php

namespace Database\Seeders;

use App\Models\Eatery;
use App\Models\Review;
use Illuminate\Database\Seeder;

/**
 * ReviewSeeder — Tạo dữ liệu mẫu bảng reviews
 *
 * Chạy: php artisan db:seed --class=ReviewSeeder
 *
 * LƯU Ý: Dùng firstOrCreate theo (eatery_id + user_name) để không tạo trùng bình luận.
 * Mọi review bạn đã thêm từ frontend/backend đều được giữ nguyên.
 */
class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $eat = fn(string $slug) => Eatery::where('slug', $slug)->value('id');

        $reviews = [
            // ── Bún Mạch Tràng Cổ Loa ─────────────────────────────────────────
            [
                'eatery_id' => $eat('bun-mach-trang-co-loa'),
                'user_name' => 'Nguyễn Minh Hiếu',
                'rating'    => 5,
                'comment'   => 'Bún ngon tuyệt vời! Sợi bún màu đục ngà chuẩn tự nhiên không hóa chất tẩy rửa. Thịt xào nghệ ở đây nêm rất vừa vặn, thơm đặc trưng. Nhất định sẽ quay lại!',
            ],
            [
                'eatery_id' => $eat('bun-mach-trang-co-loa'),
                'user_name' => 'Trần Khánh Vy',
                'rating'    => 4,
                'comment'   => 'Đúng vị truyền thống Cổ Loa luôn. Không gian quán hơi nhỏ và đông vào tầm sáng nhưng phục vụ rất nhanh nhẹn, chu đáo.',
            ],

            // ── Nhà hàng Sinh Thái Lộc Vừng ───────────────────────────────────
            [
                'eatery_id' => $eat('nha-hang-sinh-thai-loc-vung'),
                'user_name' => 'Hoàng Đức Long',
                'rating'    => 5,
                'comment'   => 'Không gian cực kỳ đỉnh rộng rãi, mát mẻ thích hợp cho gia đình họp mặt cuối tuần. Lẩu riêu cua đồng siêu nhiều gạch, vị chua thanh từ bỗng rượu cực cuốn miệng!',
            ],
            [
                'eatery_id' => $eat('nha-hang-sinh-thai-loc-vung'),
                'user_name' => 'Vũ Thị Ngọc',
                'rating'    => 4,
                'comment'   => 'Món ăn đồng quê ngon, cá sông tươi sống bắt tại bể. Giá cả có hơi cao hơn mặt bằng chung một chút nhưng hoàn toàn xứng đáng với chất lượng phục vụ và view hồ Vân Trì.',
            ],

            // ── Cà Phê Gió Vĩnh Ngọc ─────────────────────────────────────────
            [
                'eatery_id' => $eat('ca-phe-gio-vinh-ngoc'),
                'user_name' => 'Phan Nhật Minh',
                'rating'    => 5,
                'comment'   => 'Quán cafe có view đẹp nhất Đông Anh! Đứng ngắm cầu Nhật Tân lên đèn lộng lẫy và hóng gió sông Hồng cực chill. Cà phê trứng nướng béo bùi ngon xuất sắc.',
            ],

            // ── Cháo se Gia Truyền Liên Hà ────────────────────────────────────
            [
                'eatery_id' => $eat('chao-se-gia-truyen-lien-ha'),
                'user_name' => 'Lê Thanh Bình',
                'rating'    => 5,
                'comment'   => 'Cháo se đặc sản gia truyền Liên Hà ăn rất dẻo thơm và lạ miệng. Ăn kèm thịt băm phi hành củ khô thơm phưng phức, làm ấm bụng buổi chiều se lạnh cực kỳ thích.',
            ],

            // ── Phở Bò Gia Truyền Cao Lỗ ──────────────────────────────────────
            [
                'eatery_id' => $eat('pho-bo-gia-truyen-cao-lo'),
                'user_name' => 'Phạm Văn Nam',
                'rating'    => 4,
                'comment'   => 'Nước dùng phở ngọt xương thanh nhẹ, phở tái lăn nhiều hành thơm lừng mùi tỏi phi giòn tan. Địa điểm ăn sáng ruột của mình ở Đông Anh.',
            ],

            // ── Khách Sạn Đông Anh Luxury Hotel ───────────────────────────────
            [
                'eatery_id' => $eat('khach-san-dong-anh-luxury-hotel'),
                'user_name' => 'Kim Jin-woo (Du khách Hàn Quốc)',
                'rating'    => 5,
                'comment'   => 'Khách sạn rất sạch sẽ, hiện đại và nhân viên lễ tân vô cùng thân thiện hỗ trợ nhiệt tình. Vị trí nằm ngay trung tâm thị trấn cực kỳ thuận tiện đi lại ăn uống xung quanh.',
            ],

            // ── CÁC ĐÁNH GIÁ CÓ ẢNH/VIDEO HOẶC KHÔNG CÓ SAO/BÌNH LUẬN ───────
            [
                'eatery_id' => $eat('bun-mach-trang-co-loa'),
                'user_name' => 'Reviewer Ẩm Thực',
                'rating'    => null, // Không đánh giá sao
                'comment'   => 'Bát bún rất đầy đặn, nước dùng ngon. Xem ảnh mình chụp nhé.',
                'media'     => [
                    ['file_path' => 'https://placehold.co/600x400/20b2aa/ffffff?text=Bun+Mach+Trang', 'file_type' => 'image']
                ]
            ],
            [
                'eatery_id' => $eat('nha-hang-sinh-thai-loc-vung'),
                'user_name' => 'Vlog Đông Anh',
                'rating'    => 5,
                'comment'   => null, // Không có bình luận
                'media'     => [
                    ['file_path' => 'https://www.w3schools.com/html/mov_bbb.mp4', 'file_type' => 'video']
                ]
            ],
            [
                'eatery_id' => $eat('ca-phe-gio-vinh-ngoc'),
                'user_name' => 'Thích Ngắm Cảnh',
                'rating'    => null, // Không sao
                'comment'   => null, // Không bình luận
                'media'     => [
                    ['file_path' => 'https://placehold.co/600x400/ff6f00/ffffff?text=View+Ho+Song+Hong', 'file_type' => 'image'],
                    ['file_path' => 'https://placehold.co/600x400/4a90e2/ffffff?text=Cafe+Trung', 'file_type' => 'image']
                ]
            ],
        ];

        foreach ($reviews as $rev) {
            if (! $rev['eatery_id']) {
                $this->command->warn("Skipped review by '{$rev['user_name']}': eatery not found.");
                continue;
            }

            $media = $rev['media'] ?? [];
            unset($rev['media']); // Remove media array before inserting to reviews table

            $review = Review::firstOrCreate(
                ['eatery_id' => $rev['eatery_id'], 'user_name' => $rev['user_name']],
                $rev
            );

            // Seed media if available and not already seeded
            if (!empty($media) && $review->media()->count() === 0) {
                foreach ($media as $m) {
                    $review->media()->create($m);
                }
            }
        }
    }
}
