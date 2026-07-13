<?php

namespace Database\Seeders;

use App\Models\Eatery;
use App\Models\ReviewVideo;
use Illuminate\Database\Seeder;

/**
 * ReviewVideoSeeder — Tạo dữ liệu mẫu bảng review_videos
 *
 * Chạy: php artisan db:seed --class=ReviewVideoSeeder
 *
 * LƯU Ý: Dùng firstOrCreate theo video_url để không tạo trùng video.
 * Mọi video bạn đã thêm/duyệt từ backend đều được giữ nguyên.
 */
class ReviewVideoSeeder extends Seeder
{
    public function run(): void
    {
        $eat = fn(string $slug) => Eatery::where('slug', $slug)->value('id');

        $videos = [
            [
                'eatery_id'      => $eat('bun-mach-trang-co-loa'),
                'user_id'        => 1, // Admin
                'title'          => 'Cực phẩm Bún Mạch Tràng Cổ Loa gia truyền trăm năm 🍜',
                'video_url'      => 'https://www.tiktok.com/@vtv24official/video/7274092659345755393',
                'video_type'     => 'tiktok',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=400&q=80',
                'likes_count'    => 1250,
                'status'         => 'approved',
            ],
            [
                'eatery_id'      => $eat('ca-phe-gio-vinh-ngoc'),
                'user_id'        => 1, // Admin
                'title'          => 'Ngắm trọn Cầu Nhật Tân lung linh tại Cà Phê Gió ☕🌅',
                'video_url'      => 'https://www.tiktok.com/@nhatkyanchoi/video/7249123891048828165',
                'video_type'     => 'tiktok',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=400&q=80',
                'likes_count'    => 840,
                'status'         => 'approved',
            ],
            [
                'eatery_id'      => $eat('nha-hang-sinh-thai-loc-vung'),
                'user_id'        => 2, // Seller
                'title'          => 'Review hồ Vân Trì và cá sông siêu khủng tại Lộc Vừng 🐟🌳',
                'video_url'      => 'https://www.tiktok.com/@schannell.vn/video/7292348574163914002',
                'video_type'     => 'tiktok',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?auto=format&fit=crop&w=400&q=80',
                'likes_count'    => 2150,
                'status'         => 'approved',
            ],
            [
                'eatery_id'      => $eat('nha-hang-sinh-thai-loc-vung'),
                'user_id'        => 2, // Seller
                'title'          => 'Thực hư món lẩu riêu cua đồng tại hồ Vân Trì 🦀🍲',
                'video_url'      => 'https://www.tiktok.com/@foodreviewer/video/7281092671295755123',
                'video_type'     => 'tiktok',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80',
                'likes_count'    => 150,
                'status'         => 'pending',
            ],
        ];

        foreach ($videos as $vid) {
            if (! $vid['eatery_id']) {
                $this->command->warn("Skipped video '{$vid['title']}': eatery not found.");
                continue;
            }

            ReviewVideo::firstOrCreate(
                ['video_url' => $vid['video_url']],
                $vid
            );
        }
    }
}
