<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use Illuminate\Database\Seeder;

/**
 * EaterySeeder — Tạo dữ liệu mẫu bảng eateries
 *
 * Chạy: php artisan db:seed --class=EaterySeeder
 */
class EaterySeeder extends Seeder
{
    public function run(): void
    {
        // Resolve foreign keys bằng slug (không hardcode ID)
        $cat = fn(string $slug) => Category::where('slug', $slug)->value('id');
        $com = fn(string $slug) => Commune::where('slug', $slug)->value('id');

        $eateries = [
            [
                'name'          => 'Bún Mạch Tràng Cổ Loa',
                'slug'          => 'bun-mach-trang-co-loa',
                'user_id'       => 2,
                'category_id'   => $cat('dong-anh-food-map'),
                'commune_id'    => $com('thon-mach-trang'),
                'description'   => 'Bún Mạch Tràng nổi tiếng khắp vùng Cổ Loa với những sợi bún màu ngà tự nhiên, dai ngon dẻo thơm do không dùng chất tẩy đường và được làm bằng công thức gia truyền từ ngàn đời nay. Ăn kèm thịt xào nghệ, chả rươi hoặc nước dùng thanh ngọt thì ngon tuyệt cú mèo.',
                'address'       => 'Thôn Mạch Tràng, Xã Cổ Loa, Huyện Đông Anh, Hà Nội',
                'phone'         => '0987654321',
                'opening_hours' => '06:00 - 21:00',
                'latitude'      => 21.1182,
                'longitude'     => 105.8394,
                'price_range'   => '30.000đ - 70.000đ',
                'image_path'    => 'https://images.unsplash.com/photo-1591814468924-caf88d1232e1?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => true,
                'rating'        => 4.80,
                'status'        => 'active',
            ],
            [
                'name'          => 'Nhà hàng Sinh Thái Lộc Vừng',
                'slug'          => 'nha-hang-sinh-thai-loc-vung',
                'category_id'   => $cat('dong-anh-food-map'),
                'commune_id'    => $com('thon-dong'),
                'description'   => 'Nằm bên bờ hồ Vân Trì lộng gió, Nhà hàng Lộc Vừng sở hữu không gian sinh thái sân vườn cực kỳ rộng lớn, thoáng mát với những rặng lộc vừng rủ bóng mát mẻ. Chuyên các món ăn đồng quê, cá sông tươi sống, lẩu riêu cua bắp bò phục vụ gia đình, hội nhóm họp mặt.',
                'address'       => 'Khu đầm Vân Trì, Xã Vân Nội, Huyện Đông Anh, Hà Nội',
                'phone'         => '0912345678',
                'opening_hours' => '09:00 - 22:30',
                'latitude'      => 21.1458,
                'longitude'     => 105.8164,
                'price_range'   => '150.000đ - 450.000đ',
                'image_path'    => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => true,
                'rating'        => 4.60,
                'status'        => 'active',
            ],
            [
                'name'          => 'Cà Phê Gió Vĩnh Ngọc',
                'slug'          => 'ca-phe-gio-vinh-ngoc',
                'category_id'   => $cat('dong-anh-food-map'),
                'commune_id'    => $com('thon-dong-tru'),
                'description'   => 'Nằm ngay sát chân cầu Nhật Tân trên bờ đê Vĩnh Ngọc thơ mộng, Gió Cafe là điểm check-in cực hot tại Đông Anh. Khách hàng vừa có thể thưởng thức ly cà phê trứng thơm ngậy vừa ngắm trọn vẹn hoàng hôn buông xuống sông Hồng tuyệt đẹp và cầu Nhật Tân lung linh về đêm.',
                'address'       => 'Đường bờ đê Vĩnh Ngọc, Xã Vĩnh Ngọc, Huyện Đông Anh, Hà Nội',
                'phone'         => '0933445566',
                'opening_hours' => '07:00 - 23:00',
                'latitude'      => 21.1092,
                'longitude'     => 105.8326,
                'price_range'   => '25.000đ - 65.000đ',
                'image_path'    => 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => true,
                'rating'        => 4.70,
                'status'        => 'active',
            ],
            [
                'name'          => 'Cháo se Gia Truyền Liên Hà',
                'slug'          => 'chao-se-gia-truyen-lien-ha',
                'category_id'   => $cat('dong-anh-food-map'),
                'commune_id'    => $com('thon-dai-bi'),
                'description'   => 'Món cháo se trứ danh của làng Đại Vĩ, xã Liên Hà được nấu hết sức công phu. Gạo nếp cái hoa vàng được ngâm, xay nhuyễn rồi lọc lấy bột khô, sau đó được se bằng tay thành từng sợi nhỏ dài như sợi bánh canh rồi thả vào nồi nước dùng hầm từ xương ống ngọt lịm kèm thịt băm phi thơm hành.',
                'address'       => 'Đầu làng Đại Vĩ, Xã Liên Hà, Huyện Đông Anh, Hà Nội',
                'phone'         => '0977889900',
                'opening_hours' => '14:30 - 19:30',
                'latitude'      => 21.1524,
                'longitude'     => 105.8924,
                'price_range'   => '20.000đ - 40.000đ',
                'image_path'    => 'https://images.unsplash.com/photo-1547592180-85f173990554?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => false,
                'rating'        => 4.50,
                'status'        => 'active',
            ],
            [
                'name'          => 'Phở Bò Gia Truyền Cao Lỗ',
                'slug'          => 'pho-bo-gia-truyen-cao-lo',
                'category_id'   => $cat('dong-anh-food-map'),
                'commune_id'    => $com('to-dan-pho-so-6'),
                'description'   => 'Tiệm phở bò nổi tiếng lâu năm ngay trung tâm thị trấn Đông Anh. Nước dùng được hầm hoàn toàn từ xương bò trong suốt 18 tiếng kèm các loại thảo mộc quế, hồi, thảo quả tạo ra vị ngọt thanh tự nhiên. Miếng thịt bò tái lăn mềm ngọt thơm lừng mùi tỏi phi.',
                'address'       => 'Số 88 Cao Lỗ, Thị trấn Đông Anh, Hà Nội',
                'phone'         => '0243123456',
                'opening_hours' => '05:30 - 13:30',
                'latitude'      => 21.1352,
                'longitude'     => 105.8458,
                'price_range'   => '40.000đ - 65.000đ',
                'image_path'    => 'https://images.unsplash.com/photo-1582878826629-29b7ad1cdc43?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => false,
                'rating'        => 4.40,
                'status'        => 'active',
            ],
            [
                'name'          => 'Khách Sạn Đông Anh Luxury Hotel',
                'slug'          => 'khach-san-dong-anh-luxury-hotel',
                'category_id'   => $cat('stay-in-dong-anh'),
                'commune_id'    => $com('to-dan-pho-so-6'),
                'description'   => 'Đông Anh Luxury Hotel là khách sạn cao cấp bậc nhất tại trung tâm huyện Đông Anh, đạt tiêu chuẩn 3 sao quốc tế. Với hệ thống phòng ốc sang trọng, đầy đủ tiện nghi điều hòa, tủ lạnh, tivi thông minh cùng bãi đỗ xe rộng rãi, chúng tôi cam kết mang lại kỳ nghỉ tuyệt vời và ấm cúng cho khách lưu trú.',
                'address'       => 'Số 120 Đường Cao Lỗ, Thị trấn Đông Anh, Hà Nội',
                'phone'         => '0243987654',
                'opening_hours' => 'Mở cửa cả ngày (24/7)',
                'latitude'      => 21.1325,
                'longitude'     => 105.8492,
                'price_range'   => '450.000đ - 900.000đ',
                'image_path'    => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => true,
                'rating'        => 4.60,
                'status'        => 'active',
            ],
            [
                'name'          => 'Tiệm Lẩu Nướng Cổ Loa Hội Quán',
                'slug'          => 'tiem-lau-nuong-co-loa-hoi-quan',
                'category_id'   => $cat('dong-anh-food-map'),
                'commune_id'    => $com('thon-mach-trang'),
                'description'   => 'Tiệm lẩu nướng nằm sát đền Cổ Loa cổ kính, là điểm đến ẩm thực quen thuộc của thực khách phương xa khi về vãn cảnh đền. Nhà hàng nổi tiếng với phong cách nướng ngói độc đáo cùng nước lẩu Thái chua cay thơm nồng đậm đà. Đồ ăn tươi rói và giá cả cực kỳ hợp túi tiền.',
                'address'       => 'Đường trước cổng Đền Cổ Loa, Xã Cổ Loa, Huyện Đông Anh, Hà Nội',
                'phone'         => '0966778899',
                'opening_hours' => '10:00 - 22:30',
                'latitude'      => 21.1154,
                'longitude'     => 105.8415,
                'price_range'   => '120.000đ - 250.000đ',
                'image_path'    => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80',
                'is_featured'   => false,
                'rating'        => 4.30,
                'status'        => 'active',
            ],
        ];

        foreach ($eateries as $eat) {
            $eatery = Eatery::firstOrCreate(
                ['slug' => $eat['slug']],
                $eat
            );

            if ($eatery->slug === 'khach-san-dong-anh-luxury-hotel') {
                $rooms = [
                    [
                        'name' => 'Phòng Standard (Tiêu chuẩn)',
                        'price' => 450000,
                        'description' => 'Không gian ấm cúng, đầy đủ tiện nghi cơ bản cho 2 người, phù hợp khách đi công tác ngắn ngày.',
                        'image_path' => 'https://images.unsplash.com/photo-1598928506311-c55ded91a20c?auto=format&fit=crop&w=800&q=80',
                        'bed_type' => '1 Giường đôi (Queen Size)',
                        'capacity' => 2,
                    ],
                    [
                        'name' => 'Phòng Deluxe (Thượng hạng)',
                        'price' => 650000,
                        'description' => 'Tầm nhìn hướng phố tuyệt đẹp, có ban công đón gió tự nhiên, nội thất gỗ sang trọng, tiện nghi cao cấp.',
                        'image_path' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=80',
                        'bed_type' => '1 Giường đôi cỡ lớn (King Size)',
                        'capacity' => 2,
                    ],
                    [
                        'name' => 'Phòng Suite Family (Gia đình)',
                        'price' => 900000,
                        'description' => 'Phòng gia đình cực kỳ rộng rãi bao gồm 1 phòng khách nhỏ, bồn tắm nằm cao cấp, thích hợp kỳ nghỉ gia đình.',
                        'image_path' => 'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=800&q=80',
                        'bed_type' => '2 Giường đôi lớn',
                        'capacity' => 4,
                    ],
                ];

                if (\Illuminate\Support\Facades\Schema::hasTable('rooms')) {
                    foreach ($rooms as $room) {
                        \App\Models\Room::updateOrCreate(
                            ['eatery_id' => $eatery->id, 'name' => $room['name']],
                            $room
                        );
                    }
                }
            }
        }
    }
}
