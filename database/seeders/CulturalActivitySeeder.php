<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Eatery;
use App\Models\Category;
use App\Models\Commune;
use App\Models\CulturalActivity;

class CulturalActivitySeeder extends Seeder
{
    public function run(): void
    {
        $cat = Category::where('slug', 'hanh-trinh-di-san')->first();
        if (!$cat) {
            $cat = Category::create([
                'name' => 'Hành trình di sản',
                'slug' => 'hanh-trinh-di-san',
                'icon' => '🏛️',
                'description' => 'Kết nối hành trình khám phá di tích lịch sử và văn hóa thông qua nền tảng Donganh360.vn.'
            ]);
        }

        $commune = Commune::where('slug', 'thon-mach-trang')->first() ?? Commune::first();

        // Create Khu di tích Cổ Loa
        $eatery = Eatery::updateOrCreate(
            ['slug' => 'khu-di-tich-co-loa'],
            [
                'name' => 'Khu di tích Cổ Loa',
                'category_id' => $cat->id,
                'commune_id' => $commune ? $commune->id : 1,
                'address' => 'Xã Cổ Loa, Xã Đông Anh, Hà Nội',
                'phone' => '02438833333',
                'opening_hours' => '07:30 - 17:00',
                'latitude' => 21.1158,
                'longitude' => 105.8751,
                'price_range' => null, // Non-commercial, hide general price range
                'rating' => 4.9,
                'description' => 'Di tích lịch sử Quốc gia đặc biệt Cổ Loa là tòa thành cổ nhất, quy mô lớn vào bậc nhất của lịch sử dân tộc Việt Nam, gắn liền với truyền thuyết dựng nước của Vua An Dương Vương.',
                'image_path' => 'https://images.unsplash.com/photo-1596422846543-75c6fc18a523?auto=format&fit=crop&w=800&q=80',
                'status' => 'active',
                'is_featured' => true,
            ]
        );

        $activities = [
            [
                'name' => 'Hoạt động trải nghiệm cho khách (Bắn nỏ, làm bỏng chủ, oản xôi lá mít dâng vua, đúc các hiện vật tiêu biểu xưởng thủ công Âu Lạc)',
                'type' => 'experience',
                'price' => 1000000.00,
                'unit' => 'đoàn (10 người)',
                'discount_note' => null,
                'description' => 'Các hoạt động trải nghiệm thực tế giúp du khách hóa thân thành cư dân Âu Lạc cổ, tự tay bắn nỏ liên châu, làm bỏng chủ truyền thống, giã oản xôi lá mít và tham gia xưởng đúc hiện vật đồng tiêu biểu.',
                'image_path' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'name' => 'Vé tham quan di tích',
                'type' => 'ticket',
                'price' => 30000.00,
                'unit' => 'vé',
                'discount_note' => 'Học sinh từ 15 tuổi trở lên, sinh viên, người cao tuổi (từ 60 tuổi) được giảm 50% giá vé',
                'description' => 'Vé vào cổng tham quan khu di tích đền thờ An Dương Vương, giếng Ngọc, đình Cổ Loa và các khu di tích phụ cận.',
                'image_path' => 'https://images.unsplash.com/photo-1577083552431-6e5fd01aa342?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'name' => 'Dịch vụ dâng hương trọn gói',
                'type' => 'service',
                'price' => 1000000.00,
                'unit' => 'lượt dâng hương',
                'discount_note' => null,
                'description' => 'Dịch vụ chuẩn bị bài cúng, hỗ trợ sắp lễ và tổ chức dâng hương trang nghiêm tại đền Thượng thờ Vua An Dương Vương.',
                'image_path' => 'https://images.unsplash.com/photo-1528127269322-539801943592?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'name' => 'Dịch vụ thuyết minh di tích',
                'type' => 'service',
                'price' => 150000.00,
                'unit' => 'đoàn (tối đa 30 người)',
                'discount_note' => null,
                'description' => 'Thuyết minh viên chuyên nghiệp tại điểm đồng hành kể câu chuyện lịch sử, kiến trúc thành Cổ Loa và truyền thuyết nỏ thần.',
                'image_path' => 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?auto=format&fit=crop&w=800&q=80'
            ],
            [
                'name' => 'Dịch vụ đặt lễ (Lẵng hoa, quả; xôi, gà dâng hương)',
                'type' => 'service',
                'price' => null,
                'unit' => 'theo yêu cầu lễ',
                'discount_note' => null,
                'description' => 'Ban quản lý di tích nhận chuẩn bị giúp dịch vụ đặt lễ cúng bao gồm: lẵng hoa tươi, mâm ngũ quả, xôi gấc, gà trống luộc cánh tiên dâng lên đức vua.',
                'image_path' => 'https://images.unsplash.com/photo-1534447677768-be436bb09401?auto=format&fit=crop&w=800&q=80'
            ]
        ];

        foreach ($activities as $act) {
            CulturalActivity::updateOrCreate(
                ['eatery_id' => $eatery->id, 'name' => $act['name']],
                $act
            );
        }
    }
}
