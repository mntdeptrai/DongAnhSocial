<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use Illuminate\Database\Seeder;

class CultureHubSeeder extends Seeder
{
    public function run(): void
    {
        $cat = fn(string $slug) => Category::where('slug', $slug)->value('id');
        $com = fn(string $slug) => Commune::where('slug', $slug)->value('id');

        $cultureHubCategory = 'discover-dong-anh-community-culture-hub';

        $establishments = [
            [
                'name'          => 'Nhà văn hóa Huyện Đông Anh',
                'slug'          => 'nha-van-hoa-huyen-dong-anh',
                'category_id'   => $cat($cultureHubCategory),
                'commune_id'    => $com('to-dan-pho-so-6'),
                'description'   => 'Trung tâm hoạt động văn hóa nghệ thuật của huyện Đông Anh. Nơi tổ chức các sự kiện chính trị, chương trình văn nghệ, triển lãm ảnh và câu lạc bộ nghệ thuật cộng đồng.',
                'address'       => 'Số 2 Đường Cao Lỗ, Thị trấn Đông Anh, Hà Nội',
                'phone'         => '02438832156',
                'opening_hours' => '07:30 - 21:30',
                'latitude'      => 21.1375,
                'longitude'     => 105.8445,
                'price_range'   => 'Miễn phí vào cửa',
                'image_path'    => 'https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => true,
                'rating'        => 5.0,
                'status'        => 'active',
            ],
            [
                'name'          => 'Nhà thi đấu Thể thao Đông Anh',
                'slug'          => 'nha-thi-dau-the-thao-dong-anh',
                'category_id'   => $cat($cultureHubCategory),
                'commune_id'    => $com('to-dan-pho-so-6'),
                'description'   => 'Thiết chế thể thao hiện đại hàng đầu Đông Anh, trang bị khán đài và cơ sở vật chất tiêu chuẩn quốc gia. Nơi tập luyện và tổ chức các giải đấu bóng chuyền, cầu lông, bóng bàn, futsal chuyên nghiệp.',
                'address'       => 'Đường Cao Lỗ, Thị trấn Đông Anh, Hà Nội',
                'phone'         => '02438835567',
                'opening_hours' => '05:00 - 22:00',
                'latitude'      => 21.1340,
                'longitude'     => 105.8475,
                'price_range'   => 'Tùy theo giải đấu / dịch vụ',
                'image_path'    => 'https://images.unsplash.com/photo-1519766304817-4f37bda74a27?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => true,
                'rating'        => 5.0,
                'status'        => 'active',
            ],
            [
                'name'          => 'Trung tâm Triển lãm Đông Anh',
                'slug'          => 'trung-tam-trien-lam-dong-anh',
                'category_id'   => $cat($cultureHubCategory),
                'commune_id'    => $com('thon-dong'),
                'description'   => 'Không gian trưng bày, giới thiệu sản phẩm làng nghề, sinh vật cảnh, thành tựu phát triển kinh tế xã hội và quảng bá OCOP của huyện Đông Anh.',
                'address'       => 'Xã Vân Nội, Huyện Đông Anh, Hà Nội',
                'phone'         => '02438839012',
                'opening_hours' => '08:00 - 17:00',
                'latitude'      => 21.1485,
                'longitude'     => 105.8190,
                'price_range'   => 'Miễn phí vào cửa',
                'image_path'    => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => false,
                'rating'        => 5.0,
                'status'        => 'active',
            ],
            [
                'name'          => 'Nhà văn hóa thôn Mạch Tràng',
                'slug'          => 'nha-van-hoa-thon-mach-trang',
                'category_id'   => $cat($cultureHubCategory),
                'commune_id'    => $com('thon-mach-trang'),
                'description'   => 'Không gian hội họp cộng đồng của thôn Mạch Tràng, xã Cổ Loa. Tổ chức sinh hoạt chi bộ, lễ hội làng nghề bún truyền thống, giao lưu bóng chuyền hơi người cao tuổi.',
                'address'       => 'Thôn Mạch Tràng, Xã Cổ Loa, Huyện Đông Anh, Hà Nội',
                'phone'         => null,
                'opening_hours' => 'Mở cửa theo lịch sinh hoạt thôn',
                'latitude'      => 21.1190,
                'longitude'     => 105.8385,
                'price_range'   => 'Miễn phí',
                'image_path'    => 'https://images.unsplash.com/photo-1577083552431-6e5fd01aa342?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => false,
                'rating'        => 5.0,
                'status'        => 'active',
            ],
            [
                'name'          => 'Nhà văn hóa thôn Đại Vĩ',
                'slug'          => 'nha-van-hoa-thon-dai-vi',
                'category_id'   => $cat($cultureHubCategory),
                'commune_id'    => $com('thon-dai-bi'),
                'description'   => 'Nơi sinh hoạt văn hóa dân gian của bà con thôn Đại Vĩ, xã Liên Hà. Gắn liền với các phong trào văn nghệ quần chúng và lễ hội làng nghề đồ gỗ mỹ nghệ truyền thống.',
                'address'       => 'Thôn Đại Vĩ, Xã Liên Hà, Huyện Đông Anh, Hà Nội',
                'phone'         => null,
                'opening_hours' => 'Mở cửa theo lịch sinh hoạt thôn',
                'latitude'      => 21.1530,
                'longitude'     => 105.8910,
                'price_range'   => 'Miễn phí',
                'image_path'    => 'https://images.unsplash.com/photo-1596422846543-75c6fc18a523?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => false,
                'rating'        => 5.0,
                'status'        => 'active',
            ],
        ];

        foreach ($establishments as $est) {
            Eatery::firstOrCreate(
                ['slug' => $est['slug']],
                $est
            );
        }
    }
}
