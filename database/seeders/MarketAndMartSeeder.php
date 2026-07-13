<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketAndMartSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lấy danh mục Đông Anh Market
        $marketCategory = Category::where('slug', 'dong-anh-market')->first();
        if (!$marketCategory) {
            $marketCategory = Category::create([
                'name'        => 'Đông Anh Market',
                'slug'        => 'dong-anh-market',
                'icon'        => '🛍️',
                'description' => 'Nơi hội tụ các sản phẩm OCOP, đặc sản địa phương, chợ truyền thống và trung tâm mua sắm sầm uất.',
            ]);
        }

        $categories = [
            'cho-truyen-thong' => $marketCategory,
            'cho-dan-sinh' => $marketCategory,
            'sieu-thi' => $marketCategory,
            'cua-hang-tien-ich' => $marketCategory,
        ];

        // Tọa độ trung tâm các khu vực chính ở Đông Anh để làm mốc offset
        $coords = [
            'thi-tran-dong-anh' => ['lat' => 21.1362, 'lng' => 105.8455],
            'co-loa'             => ['lat' => 21.1158, 'lng' => 105.8751],
            'mai-lam'           => ['lat' => 21.0955, 'lng' => 105.8850],
            'duc-tu'            => ['lat' => 21.1301, 'lng' => 105.8950],
            'xuan-canh'         => ['lat' => 21.0850, 'lng' => 105.8600],
            'dong-hoi'          => ['lat' => 21.0805, 'lng' => 105.8902],
            'uy-no'             => ['lat' => 21.1345, 'lng' => 105.8652],
            'viet-hung'         => ['lat' => 21.1495, 'lng' => 105.8785],
            'tien-duong'        => ['lat' => 21.1480, 'lng' => 105.8300],
            'vinh-ngoc'         => ['lat' => 21.1080, 'lng' => 105.8320],
        ];

        // Hàm lấy hoặc tạo Commune động
        $getCommuneId = function (string $name, string $slug) {
            return Commune::firstOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            )->id;
        };

        // Hàm sinh toạ độ ngẫu nhiên gần một khu vực mốc
        $getRandomCoords = function (string $regionKey) use ($coords) {
            $base = $coords[$regionKey] ?? $coords['thi-tran-dong-anh'];
            $latOffset = (mt_rand(-150, 150) / 10000);
            $lngOffset = (mt_rand(-150, 150) / 10000);
            return [
                'latitude'  => $base['lat'] + $latOffset,
                'longitude' => $base['lng'] + $lngOffset,
            ];
        };

        // Dữ liệu các chợ truyền thống
        $traditionalMarkets = [
            ['name' => 'Chợ TT Đông Anh', 'addr' => 'Thị trấn Đông Anh', 'com_name' => 'Thị trấn Đông Anh', 'com_slug' => 'thi-tran-dong-anh', 'reg' => 'thi-tran-dong-anh'],
            ['name' => 'Chợ Tó', 'addr' => 'Uy Nỗ, Đông Anh', 'com_name' => 'Uy Nỗ', 'com_slug' => 'uy-no', 'reg' => 'uy-no'],
            ['name' => 'Chợ Sa (Cổ Loa)', 'addr' => 'Cổ Loa, Đông Anh', 'com_name' => 'Cổ Loa', 'com_slug' => 'co-loa', 'reg' => 'co-loa'],
            ['name' => 'Chợ Mai Lâm', 'addr' => 'Mai Lâm, Đông Anh', 'com_name' => 'Mai Lâm', 'com_slug' => 'mai-lam', 'reg' => 'mai-lam'],
            ['name' => 'Chợ Dục Nội', 'addr' => 'Việt Hùng, Đông Anh', 'com_name' => 'Việt Hùng', 'com_slug' => 'viet-hung', 'reg' => 'viet-hung'],
            ['name' => 'Chợ Dục Tú', 'addr' => 'Dục Tú, Đông Anh', 'com_name' => 'Dục Tú', 'com_slug' => 'duc-tu', 'reg' => 'duc-tu'],
            ['name' => 'Chợ văn hoá Du lịch Cổ Loa', 'addr' => 'Cổ Loa, Đông Anh', 'com_name' => 'Cổ Loa', 'com_slug' => 'co-loa', 'reg' => 'co-loa'],
        ];

        // Dữ liệu chợ dân sinh
        $localMarkets = [
            ['name' => 'Chợ Du Nội', 'addr' => 'Du Nội, Mai Lâm, Đông Anh', 'com_name' => 'Mai Lâm', 'com_slug' => 'mai-lam', 'reg' => 'mai-lam'],
            ['name' => 'Chợ Mai Hiên', 'addr' => 'Mai Hiên, Mai Lâm, Đông Anh', 'com_name' => 'Mai Lâm', 'com_slug' => 'mai-lam', 'reg' => 'mai-lam'],
            ['name' => 'Chợ Lực Canh', 'addr' => 'Lực Canh, Xuân Canh, Đông Anh', 'com_name' => 'Xuân Canh', 'com_slug' => 'xuan-canh', 'reg' => 'xuan-canh'],
            ['name' => 'Chợ Xuân Canh', 'addr' => 'Xuân Canh, Đông Anh', 'com_name' => 'Xuân Canh', 'com_slug' => 'xuan-canh', 'reg' => 'xuan-canh'],
            ['name' => 'Chợ Nhồi Dưới', 'addr' => 'Nhồi Dưới, Cổ Loa, Đông Anh', 'com_name' => 'Cổ Loa', 'com_slug' => 'co-loa', 'reg' => 'co-loa'],
            ['name' => 'Chợ Lý Nhân', 'addr' => 'Lý Nhân, Dục Tú, Đông Anh', 'com_name' => 'Dục Tú', 'com_slug' => 'duc-tu', 'reg' => 'duc-tu'],
            ['name' => 'Chợ Dày Da', 'addr' => 'Tổ 35, 36 Thị trấn Đông Anh', 'com_name' => 'Thị trấn Đông Anh', 'com_slug' => 'thi-tran-dong-anh', 'reg' => 'thi-tran-dong-anh'],
            ['name' => 'Chợ Đông Trù', 'addr' => 'Đông Trù, Đông Hội, Đông Anh', 'com_name' => 'Đông Hội', 'com_slug' => 'dong-hoi', 'reg' => 'dong-hoi'],
            ['name' => 'Chợ Mạch Tràng', 'addr' => 'Mạch Tràng, Cổ Loa, Đông Anh', 'com_name' => 'Cổ Loa', 'com_slug' => 'co-loa', 'reg' => 'co-loa'],
        ];

        // Siêu thị
        $supermarkets = [
            ['name' => 'Siêu thị Lan Chi', 'addr' => 'Thị trấn Đông Anh, Đông Anh', 'com_name' => 'Thị trấn Đông Anh', 'com_slug' => 'thi-tran-dong-anh', 'reg' => 'thi-tran-dong-anh'],
        ];

        // Cửa hàng tiện ích
        $convenienceStores = [
            ['name' => 'Winmart+ Thôn Chợ', 'addr' => 'Thôn Chợ, Tiên Dương, Đông Anh', 'com_name' => 'Tiên Dương', 'com_slug' => 'tien-duong', 'reg' => 'tien-duong'],
            ['name' => 'Amyly Mart Thôn Chợ', 'addr' => 'Thôn Chợ, Tiên Dương, Đông Anh', 'com_name' => 'Tiên Dương', 'com_slug' => 'tien-duong', 'reg' => 'tien-duong'],
            ['name' => 'Winmart+ Thôn Nhồi Dưới', 'addr' => 'Thôn Nhồi Dưới, Cổ Loa, Đông Anh', 'com_name' => 'Cổ Loa', 'com_slug' => 'co-loa', 'reg' => 'co-loa'],
            ['name' => 'Winmart+ Thôn Trung', 'addr' => 'Thôn Trung, Vĩnh Ngọc, Đông Anh', 'com_name' => 'Vĩnh Ngọc', 'com_slug' => 'vinh-ngoc', 'reg' => 'vinh-ngoc'],
            ['name' => 'Amily Mart Thôn Trung', 'addr' => 'Thôn Trung, Vĩnh Ngọc, Đông Anh', 'com_name' => 'Vĩnh Ngọc', 'com_slug' => 'vinh-ngoc', 'reg' => 'vinh-ngoc'],
            ['name' => 'Winmart+ Cao Lỗ', 'addr' => 'Cao Lỗ, Uy Nỗ, Đông Anh', 'com_name' => 'Uy Nỗ', 'com_slug' => 'uy-no', 'reg' => 'uy-no'],
            ['name' => 'Winmart+ Tổ 4', 'addr' => 'Tổ 4, Thị trấn Đông Anh', 'com_name' => 'Thị trấn Đông Anh', 'com_slug' => 'thi-tran-dong-anh', 'reg' => 'thi-tran-dong-anh'],
            ['name' => 'Winmart+ Ấp Tó', 'addr' => 'Ấp Tó, Uy Nỗ, Đông Anh', 'com_name' => 'Uy Nỗ', 'com_slug' => 'uy-no', 'reg' => 'uy-no'],
            ['name' => 'Winmart+ Thôn Ngoài', 'addr' => 'Thôn Ngoài, Uy Nỗ, Đông Anh', 'com_name' => 'Uy Nỗ', 'com_slug' => 'uy-no', 'reg' => 'uy-no'],
            ['name' => 'Winmart+ Thôn Thượng', 'addr' => 'Thôn Thượng, Uy Nỗ, Đông Anh', 'com_name' => 'Uy Nỗ', 'com_slug' => 'uy-no', 'reg' => 'uy-no'],
            ['name' => 'Winmart+ Tổ 37', 'addr' => 'Tổ 37, Thị trấn Đông Anh', 'com_name' => 'Thị trấn Đông Anh', 'com_slug' => 'thi-tran-dong-anh', 'reg' => 'thi-tran-dong-anh'],
            ['name' => 'Winmart+ Lộc Hà', 'addr' => 'Lộc Hà, Mai Lâm, Đông Anh', 'com_name' => 'Mai Lâm', 'com_slug' => 'mai-lam', 'reg' => 'mai-lam'],
            ['name' => 'Winmart+ Du Ngoại', 'addr' => 'Du Ngoại, Mai Lâm, Đông Anh', 'com_name' => 'Mai Lâm', 'com_slug' => 'mai-lam', 'reg' => 'mai-lam'],
            ['name' => 'Winmart+ Tiên Hội', 'addr' => 'Tiên Hội, Đông Hội, Đông Anh', 'com_name' => 'Đông Hội', 'com_slug' => 'dong-hoi', 'reg' => 'dong-hoi'],
            ['name' => 'Winmart+ Lại Đà', 'addr' => 'Lại Đà, Đông Hội, Đông Anh', 'com_name' => 'Đông Hội', 'com_slug' => 'dong-hoi', 'reg' => 'dong-hoi'],
            ['name' => 'Winmart+ Park 2 Đông Hội (Nhánh 1)', 'addr' => 'Park 2 Đông Hội, Đông Anh', 'com_name' => 'Đông Hội', 'com_slug' => 'dong-hoi', 'reg' => 'dong-hoi'],
            ['name' => 'Winmart+ Park 2 Đông Hội', 'addr' => 'Park 2 Đông Hội, Đông Anh', 'com_name' => 'Đông Hội', 'com_slug' => 'dong-hoi', 'reg' => 'dong-hoi'],
            ['name' => 'Winmart+ Dục Tú 3', 'addr' => 'Dục Tú 3, Dục Tú, Đông Anh', 'com_name' => 'Dục Tú', 'com_slug' => 'duc-tu', 'reg' => 'duc-tu'],
        ];

        // 2. Import Chợ truyền thống
        foreach ($traditionalMarkets as $index => $item) {
            $slug = Str::slug($item['name']);
            $c = $getRandomCoords($item['reg']);
            Eatery::firstOrCreate(
                ['slug' => $slug],
                [
                    'name'          => $item['name'],
                    'category_id'   => $categories['cho-truyen-thong']->id,
                    'commune_id'    => $getCommuneId($item['com_name'], $item['com_slug']),
                    'address'       => $item['addr'],
                    'description'   => "Khu chợ truyền thống sầm uất tại " . $item['com_name'] . ", nơi lưu giữ hồn quê Đông Anh qua hàng chục năm và nổi tiếng với nhiều món ăn bình dân độc đáo.",
                    'latitude'      => $c['latitude'],
                    'longitude'     => $c['longitude'],
                    'price_range'   => '10.000đ - 150.000đ',
                    'image_path'    => 'https://images.unsplash.com/photo-1543083503-4c904c1f7193?auto=format&fit=crop&w=400&q=80', // Ảnh chợ quê
                    'status'        => 'active',
                    'rating'        => 4.2 + ($index % 5) / 10,
                    'is_featured'   => $index === 0, // Featured chợ Đông Anh
                ]
            );
        }

        // 3. Import Chợ dân sinh
        foreach ($localMarkets as $index => $item) {
            $slug = Str::slug($item['name']);
            $c = $getRandomCoords($item['reg']);
            Eatery::firstOrCreate(
                ['slug' => $slug],
                [
                    'name'          => $item['name'],
                    'category_id'   => $categories['cho-dan-sinh']->id,
                    'commune_id'    => $getCommuneId($item['com_name'], $item['com_slug']),
                    'address'       => $item['addr'],
                    'description'   => "Khu chợ dân sinh nhộn nhịp vào buổi sớm tại thôn " . $item['name'] . ". Trải nghiệm mua sắm mộc mạc và khám phá văn hóa ẩm thực quà quê dân dã.",
                    'latitude'      => $c['latitude'],
                    'longitude'     => $c['longitude'],
                    'price_range'   => '5.000đ - 100.000đ',
                    'image_path'    => 'https://images.unsplash.com/photo-1488459718432-01055e67e1f5?auto=format&fit=crop&w=400&q=80', // Ảnh rau quả chợ quê
                    'status'        => 'active',
                    'rating'        => 4.0 + ($index % 6) / 10,
                ]
            );
        }

        // 4. Import Siêu thị
        foreach ($supermarkets as $index => $item) {
            $slug = Str::slug($item['name']);
            $c = $getRandomCoords($item['reg']);
            Eatery::firstOrCreate(
                ['slug' => $slug],
                [
                    'name'          => $item['name'],
                    'category_id'   => $categories['sieu-thi']->id,
                    'commune_id'    => $getCommuneId($item['com_name'], $item['com_slug']),
                    'address'       => $item['addr'],
                    'description'   => "Siêu thị mua sắm tổng hợp tiện ích và hiện đại hàng đầu Đông Anh, phục vụ hàng ngàn hộ gia đình mỗi ngày với chất lượng dịch vụ cao.",
                    'latitude'      => $c['latitude'],
                    'longitude'     => $c['longitude'],
                    'price_range'   => '50.000đ - 1.000.000đ',
                    'image_path'    => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=400&q=80', // Ảnh siêu thị
                    'status'        => 'active',
                    'rating'        => 4.5,
                    'is_featured'   => true,
                ]
            );
        }

        // 5. Import Cửa hàng tiện ích
        foreach ($convenienceStores as $index => $item) {
            $slug = Str::slug($item['name']);
            $c = $getRandomCoords($item['reg']);
            Eatery::firstOrCreate(
                ['slug' => $slug],
                [
                    'name'          => $item['name'],
                    'category_id'   => $categories['cua-hang-tien-ich']->id,
                    'commune_id'    => $getCommuneId($item['com_name'], $item['com_slug']),
                    'address'       => $item['addr'],
                    'description'   => "Cửa hàng mua sắm tiện ích và nhanh chóng nằm tại " . $item['addr'] . ", mở cửa liên tục phục vụ nhu cầu đời sống của người dân địa phương và du khách.",
                    'latitude'      => $c['latitude'],
                    'longitude'     => $c['longitude'],
                    'price_range'   => '10.000đ - 200.000đ',
                    'image_path'    => 'https://images.unsplash.com/photo-1604719312566-8912e9227c6a?auto=format&fit=crop&w=400&q=80', // Ảnh cửa hàng tiện lợi
                    'status'        => 'active',
                    'rating'        => 4.3 + ($index % 3) / 10,
                ]
            );
        }
    }
}
