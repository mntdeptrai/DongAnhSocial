<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Eatery;
use App\Models\Category;
use App\Models\Commune;
use Illuminate\Support\Str;

class OcopHeritageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $category = Category::where('slug', 'dong-anh-market')->first();
        if (!$category) {
            return;
        }

        $defaultCommune = Commune::first();

        $ocopData = [
            [
                'name' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
                'address' => 'Thôn Lộc Hà, xã Đông Anh, thành phố Hà Nội',
                'description' => 'Sản phẩm OCOP 4 sao: Đông trùng hạ thảo tươi, Đông trùng hạ thảo khô, Đông trùng hạ thảo ký chủ nhộng tằm (Chứng nhận năm 2022).',
                'latitude' => 21.0945,
                'longitude' => 105.8672,
                'commune_name' => 'Mai Lâm',
                'image_path' => 'https://dongtrunghathaokovi.com/wp-content/uploads/2021/04/dong-trung-ha-thao-kovi.jpg'
            ],
            [
                'name' => 'Công ty TNHH Hoàng Chiến Thắng',
                'address' => 'Thôn Đông Ngàn, xã Đông Anh, thành phố Hà Nội',
                'description' => 'Sản phẩm OCOP đa dạng: Bánh gạo lứt, Bánh vừng vòng, Bánh sampa, Bánh nhện vừng, Bánh Vòng Dừa, Bánh vừng Cookies, Bánh gạo thơm (Đạt chứng nhận liên tục 2022-2025).',
                'latitude' => 21.1098,
                'longitude' => 105.8612,
                'commune_name' => 'Đông Hội',
                'image_path' => 'https://bizweb.dktcdn.net/100/356/102/files/banh-dong-ngan-dong-anh.jpg?v=1626071477148'
            ],
            [
                'name' => 'Tương Việt Hùng - HTX dịch vụ nông nghiệp thôn Đoài',
                'address' => 'Thôn Đoài, xã Đông Anh, thành phố Hà Nội',
                'description' => 'Sản phẩm OCOP đặc trưng truyền thống: Tương Việt Hùng (Chứng nhận năm 2022). Tương nếp thơm ngon, đậm đà bản sắc cố đô.',
                'latitude' => 21.1444,
                'longitude' => 105.8752,
                'commune_name' => 'Việt Hùng',
                'image_path' => 'https://file1.dangcongsan.vn/data/0/images/2021/11/04/phuongthuy/tuong-viet-hung-1.jpg'
            ],
            [
                'name' => 'Bánh ngọt Thuý Quyên',
                'address' => 'Thôn Đông Ngàn, xã Đông Anh, thành phố Hà Nội',
                'description' => 'Sản phẩm OCOP: Bánh xốp vừng, Bánh sampa, Bánh trứng nhện (Chứng nhận năm 2023). Đậm đà hương vị bánh kẹo Đông Ngàn.',
                'latitude' => 21.1090,
                'longitude' => 105.8610,
                'commune_name' => 'Đông Hội',
                'image_path' => 'https://hanoimoi.com.vn/Uploads/Images/tuandiep/2020/09/20/banh.jpg'
            ],
            [
                'name' => 'Rượu Long Tửu (HKD Thạo Loan)',
                'address' => 'Thôn Xuân Canh, xã Đông Anh, thành phố Hà Nội',
                'description' => 'Sản phẩm OCOP lừng danh: Rượu gạo nếp Long Tửu, Rượu dâu, Rượu mơ, Rượu Bạch cúc (Chứng nhận liên tục 2023-2025). Tinh túy men lá.',
                'latitude' => 21.1030,
                'longitude' => 105.8450,
                'commune_name' => 'Xuân Canh',
                'image_path' => 'https://file3.qdnd.vn/data/images/0/2021/12/30/vuhuyen/longtuu.jpg'
            ],
            [
                'name' => 'HTX Cổ Loa',
                'address' => 'Trung tâm Cổ Loa, xã Đông Anh, thành phố Hà Nội',
                'description' => 'Sản phẩm OCOP nông sản sạch: Hành lá, khoai tây, bí đỏ, lạc nhân (Chứng nhận 2024-2025).',
                'latitude' => 21.1167,
                'longitude' => 105.8667,
                'commune_name' => 'Cổ Loa',
                'image_path' => 'https://media.thanglong.chinhphu.vn/Images/2021/04/23/nongsan.jpg'
            ],
            [
                'name' => 'Gạo nếp cái hoa vàng Dục Tú',
                'address' => 'Xã Đông Anh, thành phố Hà Nội',
                'description' => 'Sản phẩm OCOP: Gạo nếp cái hoa vàng (HTX dịch vụ nông nghiệp Dục Tú - Chứng nhận 2024). Hạt tròn, dẻo thơm.',
                'latitude' => 21.1345,
                'longitude' => 105.9082,
                'commune_name' => 'Dục Tú',
                'image_path' => 'https://media.songkhoe.vn/2019/11/04/nep-cai-hoa-vang-1.jpg'
            ],
            [
                'name' => 'Cơ sở sản xuất thực phẩm Liêm Hiệp',
                'address' => 'Thôn Thượng, xã Đông Anh, thành phố Hà Nội',
                'description' => 'Sản phẩm OCOP: Giò lụa, Chả lụa (Chứng nhận 2025). Thơm ngon, an toàn vệ sinh thực phẩm.',
                'latitude' => 21.1440,
                'longitude' => 105.8475,
                'commune_name' => 'Uy Nỗ',
                'image_path' => 'https://cdn.tgdd.vn/Files/2019/08/16/1188358/cach-lam-gio-lua-thom-ngon-tai-nha-202112311456100155.jpg'
            ]
        ];

        foreach ($ocopData as $data) {
            $commune = Commune::where('name', 'like', '%'.$data['commune_name'].'%')->first() ?? $defaultCommune;

            $eatery = Eatery::updateOrCreate(
                ['name' => $data['name']],
                [
                    'category_id' => $category->id,
                    'commune_id' => $commune->id,
                    'slug' => Str::slug($data['name']),
                    'address' => $data['address'],
                    'latitude' => $data['latitude'] + (rand(-10,10)/10000), 
                    'longitude' => $data['longitude'] + (rand(-10,10)/10000),
                    'rating' => 4.9,
                    'price_range' => '50.000đ - 500.000đ',
                    'opening_hours' => '07:00 - 18:00',
                    'description' => $data['description'],
                    'image_path' => $data['image_path'],
                    'status' => 'active'
                ]
            );

            // Seed products for each OCOP eatery
            $products = [];
            $slug = Str::slug($data['name']);
            if (str_contains($slug, 'kovi')) {
                $products = [
                    [
                        'name' => 'Đông trùng hạ thảo tươi KOVI',
                        'price' => 150000,
                        'description' => 'Nuôi cấy công nghệ sinh học tiên tiến, giàu hoạt chất cordycepin tốt cho sức khỏe.',
                        'image_path' => 'https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '4 sao',
                    ],
                    [
                        'name' => 'Đông trùng hạ thảo sấy thăng hoa',
                        'price' => 600000,
                        'description' => 'Sấy thăng hoa giữ nguyên 99% dược chất và hình dáng tự nhiên, bảo quản tiện lợi lâu dài.',
                        'image_path' => 'https://images.unsplash.com/photo-1611080626919-7cf5a9dbab5b?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '4 sao',
                    ],
                ];
            } elseif (str_contains($slug, 'hoang-chien-thang')) {
                $products = [
                    [
                        'name' => 'Bánh gạo lứt Đông Ngàn',
                        'price' => 35000,
                        'description' => 'Làm từ gạo lứt đỏ Điện Biên, giòn rụm, ngọt nhẹ, tốt cho người ăn kiêng.',
                        'image_path' => 'https://images.unsplash.com/photo-1590080875515-8a3a8dc5735e?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '4 sao',
                    ],
                    [
                        'name' => 'Bánh nhện vừng đặc sản',
                        'price' => 25000,
                        'description' => 'Bánh vừng giòn xốp truyền thống Đông Ngàn gắn liền với ký ức tuổi thơ.',
                        'image_path' => 'https://images.unsplash.com/photo-1558961309-dbdf71799fad?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '4 sao',
                    ],
                ];
            } elseif (str_contains($slug, 'tuong-viet-hung')) {
                $products = [
                    [
                        'name' => 'Tương nếp Việt Hùng (Chum đất)',
                        'price' => 45000,
                        'description' => 'Tương ủ thủ công từ gạo nếp cái hoa vàng và đỗ tương sạch lên men tự nhiên trong chum sành.',
                        'image_path' => 'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '3 sao',
                    ],
                ];
            } elseif (str_contains($slug, 'thuy-quyen')) {
                $products = [
                    [
                        'name' => 'Bánh xốp vừng Thúy Quyên',
                        'price' => 30000,
                        'description' => 'Thơm mùi vừng rang, giòn tan trong miệng, ngọt dịu thích hợp uống kèm trà.',
                        'image_path' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '3 sao',
                    ],
                ];
            } elseif (str_contains($slug, 'long-tuu')) {
                $products = [
                    [
                        'name' => 'Rượu nếp cái hoa vàng hạ thổ',
                        'price' => 120000,
                        'description' => 'Chưng cất từ gạo nếp và men lá thuốc bắc, hạ thổ chum sành trên 12 tháng êm dịu thơm nồng.',
                        'image_path' => 'https://images.unsplash.com/photo-1569529465841-df8201374ad5?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '4 sao',
                    ],
                    [
                        'name' => 'Rượu mơ má đào Long Tửu',
                        'price' => 150000,
                        'description' => 'Ủ từ quả mơ má đào Mộc Châu tươi ngon cùng rượu nếp sạch, vị chua ngọt dễ uống.',
                        'image_path' => 'https://images.unsplash.com/photo-1527661591475-527312dd65f5?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '4 sao',
                    ],
                ];
            } elseif (str_contains($slug, 'co-loa')) {
                $products = [
                    [
                        'name' => 'Khoai tây sạch VietGAP Cổ Loa',
                        'price' => 25000,
                        'description' => 'Trồng trên đất phù sa bãi bồi Cổ Loa, củ vàng đều, nhiều tinh bột, dẻo thơm.',
                        'image_path' => 'https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '3 sao',
                    ],
                ];
            } elseif (str_contains($slug, 'duc-tu')) {
                $products = [
                    [
                        'name' => 'Gạo nếp cái hoa vàng Dục Tú',
                        'price' => 180000,
                        'description' => 'Túi 5kg gạo nếp thuần chủng vùng Dục Tú, hạt tròn mẩy bóng, thổi xôi cực dẻo và thơm lâu.',
                        'image_path' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '4 sao',
                    ],
                ];
            } elseif (str_contains($slug, 'liem-hiep')) {
                $products = [
                    [
                        'name' => 'Giò lụa đặc sản Liêm Hiệp (1kg)',
                        'price' => 180000,
                        'description' => 'Thịt heo tươi giã tay truyền thống, không hàn the, không chất bảo quản, thơm mùi lá chuối tự nhiên.',
                        'image_path' => 'https://images.unsplash.com/photo-1608897013039-887f21d8c804?auto=format&fit=crop&w=800&q=80',
                        'star_rating' => '3 sao',
                    ],
                ];
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('ocop_products')) {
                foreach ($products as $prod) {
                    \App\Models\OcopProduct::updateOrCreate(
                        ['eatery_id' => $eatery->id, 'name' => $prod['name']],
                        $prod
                    );
                }
            }
        }
    }
}
