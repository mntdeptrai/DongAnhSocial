<?php

namespace Database\Seeders;

use App\Models\Dish;
use App\Models\Eatery;
use Illuminate\Database\Seeder;

/**
 * DishSeeder — Tạo dữ liệu mẫu bảng dishes
 *
 * Chạy: php artisan db:seed --class=DishSeeder
 *
 * LƯU Ý QUAN TRỌNG:
 *  - Seeder resolve eatery_id bằng slug (không hardcode ID) → an toàn với mọi thứ tự seed.
 *  - Dùng firstOrCreate theo (eatery_id + name) → không tạo trùng món ăn.
 *  - Dữ liệu món ăn bạn đã thêm/sửa từ backend KHÔNG bị ảnh hưởng.
 */
class DishSeeder extends Seeder
{
    public function run(): void
    {
        // Helper: tìm eatery_id theo slug, trả về null nếu không tìm thấy
        $eat = fn(string $slug) => Eatery::where('slug', $slug)->value('id');

        $dishes = [
            // ── Bún Mạch Tràng Cổ Loa ─────────────────────────────────────────
            [
                'eatery_id'   => $eat('bun-mach-trang-co-loa'),
                'name'        => 'Bún Mạch Tràng Trộn Thịt Xào Nghệ',
                'price'       => 35000,
                'description' => 'Sợi bún Mạch Tràng màu ngà dai dẻo trộn chung với thịt lợn ba chỉ xào nghệ vàng ươm thơm lừng cùng rau thơm, lạc rang.',
                'image_path'  => 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=300&q=80',
                'is_signature' => true,
            ],
            [
                'eatery_id'   => $eat('bun-mach-trang-co-loa'),
                'name'        => 'Bún Mạch Tràng Sườn Dọc Mùng',
                'price'       => 40000,
                'description' => 'Bún nước nóng hổi kèm sườn non ninh nhừ, mọc thịt heo viên thơm nấm hương và dọc mùng giòn sần sật.',
                'image_path'  => 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=300&q=80',
                'is_signature' => false,
            ],

            // ── Nhà hàng Sinh Thái Lộc Vừng ───────────────────────────────────
            [
                'eatery_id'   => $eat('nha-hang-sinh-thai-loc-vung'),
                'name'        => 'Nồi Lẩu Riêu Cua Sườn Sụn Bắp Bò',
                'price'       => 350000,
                'description' => 'Nước lẩu cua đồng đặc trưng gạch cua xịn béo ngậy nấu giấm bỗng chua thanh, nhúng kèm bắp bò tươi, sườn sụn giòn rụm và các loại rau đồng quê.',
                'image_path'  => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=300&q=80',
                'is_signature' => true,
            ],
            [
                'eatery_id'   => $eat('nha-hang-sinh-thai-loc-vung'),
                'name'        => 'Cá Lăng Sông Hồng Nướng Muối Ớt',
                'price'       => 280000,
                'description' => 'Cá lăng tươi được đánh bắt trực tiếp dưới sông Hồng, tẩm ướp muối ớt gia vị nướng than hoa vàng giòn lớp da, thịt cá ngọt dai.',
                'image_path'  => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?auto=format&fit=crop&w=300&q=80',
                'is_signature' => true,
            ],

            // ── Cà Phê Gió Vĩnh Ngọc ─────────────────────────────────────────
            [
                'eatery_id'   => $eat('ca-phe-gio-vinh-ngoc'),
                'name'        => 'Cà Phê Trứng Nướng Đặc Biệt',
                'price'       => 35000,
                'description' => 'Cốt cà phê phin nguyên chất Robusta đậm đà phủ lớp kem trứng béo ngậy được khò lửa nướng thơm phức như bánh flan.',
                'image_path'  => 'https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=300&q=80',
                'is_signature' => true,
            ],
            [
                'eatery_id'   => $eat('ca-phe-gio-vinh-ngoc'),
                'name'        => 'Trà Đào Cam Sả Thảo Mộc',
                'price'       => 30000,
                'description' => 'Trà đào thanh mát quyện vị cam vàng ngọt lịm và hương sả nồng nàn thơm dễ chịu.',
                'image_path'  => 'https://images.unsplash.com/photo-1497515114629-f71d768fd07c?auto=format&fit=crop&w=300&q=80',
                'is_signature' => false,
            ],

            // ── Cháo se Gia Truyền Liên Hà ────────────────────────────────────
            [
                'eatery_id'   => $eat('chao-se-gia-truyen-lien-ha'),
                'name'        => 'Bát Cháo Se Thịt Băm Hành Phi',
                'price'       => 25000,
                'description' => 'Bát cháo nóng hôi hổi với những sợi bột se dai thơm, thịt nạc băm xào hành phi đậm vị cùng hạt tiêu sọ cay nồng.',
                'image_path'  => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=300&q=80',
                'is_signature' => true,
            ],

            // ── Phở Bò Gia Truyền Cao Lỗ ──────────────────────────────────────
            [
                'eatery_id'   => $eat('pho-bo-gia-truyen-cao-lo'),
                'name'        => 'Bát Phở Bò Tái Lăn Cao Lỗ',
                'price'       => 45000,
                'description' => 'Thịt bò tươi xào tái nhanh trên lửa lớn với tỏi thơm lừng trước khi xếp lên bánh phở trắng mịn và tưới nước dùng trong vắt, béo ngọt.',
                'image_path'  => 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=300&q=80',
                'is_signature' => true,
            ],

            // ── Khách Sạn Đông Anh Luxury Hotel ───────────────────────────────
            [
                'eatery_id'   => $eat('khach-san-dong-anh-luxury-hotel'),
                'name'        => 'Phòng Superior Double View Trung Tâm',
                'price'       => 500000,
                'description' => 'Hạng phòng Superior dành cho 2 người với 1 giường đôi lớn, ban công thoáng rộng nhìn ra ngã tư Cao Lỗ sầm uất.',
                'image_path'  => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=300&q=80',
                'is_signature' => true,
            ],

            // ── Tiệm Lẩu Nướng Cổ Loa Hội Quán ────────────────────────────────
            [
                'eatery_id'   => $eat('tiem-lau-nuong-co-loa-hoi-quan'),
                'name'        => 'Set Nướng Ngói Bò Ba Chỉ Sốt Trứng Muối',
                'price'       => 199000,
                'description' => 'Thịt ba chỉ bò Mỹ tẩm sốt đậm đà được nướng trực tiếp trên miếng ngói đất nung giữ trọn độ ngọt của thịt, chấm kèm sốt trứng muối dẻo quánh.',
                'image_path'  => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=300&q=80',
                'is_signature' => true,
            ],
        ];

        foreach ($dishes as $dish) {
            // Bỏ qua nếu eatery_id không tồn tại trong DB (tránh lỗi FK constraint)
            if (! $dish['eatery_id']) {
                $this->command->warn("Skipped dish '{$dish['name']}': eatery not found.");
                continue;
            }

            Dish::firstOrCreate(
                ['eatery_id' => $dish['eatery_id'], 'name' => $dish['name']],
                $dish
            );
        }
    }
}
