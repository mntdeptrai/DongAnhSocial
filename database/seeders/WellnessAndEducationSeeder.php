<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WellnessAndEducationSeeder extends Seeder
{
    public function run(): void
    {
        $catWellness = Category::where('slug', 'wellness-care')->first();
        $catEducation = Category::where('slug', 'smart-education-map')->first();

        if (!$catWellness || !$catEducation) {
            return;
        }

        $defaultCommune = Commune::first();

        // 1. Wellness & Care data
        $wellnessData = [
            [
                'name' => 'Bệnh viện Đa khoa Đông Anh',
                'address' => 'Số 21 Đường Cao Lỗ, Uy Nỗ, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6', // Uy Nỗ/Thị trấn
                'phone' => '02438832445',
                'opening_hours' => 'Mở cửa cả ngày (24/7)',
                'latitude' => 21.1355,
                'longitude' => 105.8445,
                'price_range' => 'Bảo hiểm & Dịch vụ',
                'description' => 'Bệnh viện đa khoa hạng I tuyến xã, cung cấp dịch vụ khám chữa bệnh chất lượng cao, trang thiết bị hiện đại và đội ngũ y bác sĩ tận tâm phục vụ nhân dân Đông Anh.',
                'image_path' => 'https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Trung tâm Y tế Xã Đông Anh',
                'address' => 'Tổ 2, Thị trấn Đông Anh, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438832263',
                'opening_hours' => '08:00 - 17:00',
                'latitude' => 21.1390,
                'longitude' => 105.8470,
                'price_range' => 'Theo quy định y tế',
                'description' => 'Cơ sở y tế dự phòng, tiêm chủng chất lượng cao, quản lý các trạm y tế xã/thị trấn và chăm sóc sức khỏe cộng đồng toàn diện.',
                'image_path' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        // 2. Education data (Chính xác 18 trường & danh sách các trường sáp nhập thành phần từ File Excel PA3)
        $educationData = [
            // A. SẮP XẾP CÁC TRƯỜNG MẦM NON (06 TRƯỜNG)
            [
                'name' => 'MN Phúc Lộc',
                'address' => 'Xã Phúc Lộc, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830001',
                'opening_hours' => '07:00 - 17:30',
                'latitude' => 21.1685,
                'longitude' => 105.8920,
                'price_range' => 'Công lập chuẩn (30 lớp)',
                'description' => 'Trường MN Phúc Lộc theo PA3: Sáp nhập từ Mầm non Phúc Lộc (16 lớp, 405 HS) và Mầm non Sao Mai (14 lớp, 354 HS). Tổng quy mô 30 lớp, 759 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'MN Cổ Loa',
                'address' => 'Xã Cổ Loa, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830002',
                'opening_hours' => '07:00 - 17:30',
                'latitude' => 21.1398,
                'longitude' => 105.8655,
                'price_range' => 'Công lập chuẩn (36 lớp)',
                'description' => 'Trường MN Cổ Loa theo PA3: Sáp nhập từ Mầm non Cổ Loa (18 lớp, 564 HS) và Mầm non Thành Loa (18 lớp, 383 HS). Tổng quy mô 36 lớp, 947 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'MN Mai Lâm',
                'address' => 'Xã Mai Lâm, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830003',
                'opening_hours' => '07:00 - 17:30',
                'latitude' => 21.1085,
                'longitude' => 105.8820,
                'price_range' => 'Công lập chuẩn (32 lớp)',
                'description' => 'Trường MN Mai Lâm theo PA3: Sáp nhập từ Mầm non Thái Bình (18 lớp, 521 HS) và Mầm non Mai Lâm (14 lớp, 345 HS). Tổng quy mô 32 lớp, 866 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1576495199011-87b3f6c21dbb?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'MN Việt Hùng',
                'address' => 'Xã Việt Hùng, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830004',
                'opening_hours' => '07:00 - 17:30',
                'latitude' => 21.1480,
                'longitude' => 105.8750,
                'price_range' => 'Công lập chuẩn (55 lớp)',
                'description' => 'Trường MN Việt Hùng theo PA3: Sáp nhập từ Mầm non Dục Nội (17 lớp, 525 HS), Mầm non Việt Hùng (12 lớp, 268 HS) và Mầm non Dục Tú (26 lớp, 647 HS). Tổng quy mô 55 lớp, 1.440 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'MN Uy Nỗ',
                'address' => 'Xã Uy Nỗ, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830005',
                'opening_hours' => '07:00 - 17:30',
                'latitude' => 21.1390,
                'longitude' => 105.8500,
                'price_range' => 'Công lập chuẩn (33 lớp)',
                'description' => 'Trường MN Uy Nỗ theo PA3: Sáp nhập từ Mầm non Uy Nỗ A (15 lớp, 420 HS) và Mầm non Uy Nỗ (18 lớp, 490 HS). Tổng quy mô 33 lớp, 910 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'MN Đông Hội',
                'address' => 'Xã Đông Hội, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830006',
                'opening_hours' => '07:00 - 17:30',
                'latitude' => 21.0950,
                'longitude' => 105.8710,
                'price_range' => 'Công lập chuẩn (48 lớp)',
                'description' => 'Trường MN Đông Hội theo PA3: Sáp nhập từ Mầm non Đông Hội (28 lớp, 875 HS) và Mầm non Xuân Canh (20 lớp, 589 HS). Tổng quy mô 48 lớp, 1.464 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=800&q=80',
            ],

            // B. SẮP XẾP CÁC TRƯỜNG TIỂU HỌC (03 TRƯỜNG)
            [
                'name' => 'TH An Dương Vương',
                'address' => 'Thôn Gia Lương, Xã Việt Hùng, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830007',
                'opening_hours' => '07:30 - 17:00',
                'latitude' => 21.1420,
                'longitude' => 105.8690,
                'price_range' => 'Công lập chuẩn (28 lớp)',
                'description' => 'Trường TH An Dương Vương giữ nguyên quy mô theo PA3 (28 lớp, 1.009 học sinh). Trường CLC.',
                'image_path' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'TH Đông Hội',
                'address' => 'Xã Đông Hội, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830008',
                'opening_hours' => '07:30 - 17:00',
                'latitude' => 21.0930,
                'longitude' => 105.8680,
                'price_range' => 'Công lập chuẩn (55 lớp)',
                'description' => 'Trường TH Đông Hội theo PA3: Sáp nhập từ Tiểu học Đông Hội (38 lớp, 1.887 HS) và Tiểu học Xuân Canh (17 lớp, 539 HS). Tổng quy mô 55 lớp, 2.426 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'TH Việt Hùng',
                'address' => 'Xã Việt Hùng, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830009',
                'opening_hours' => '07:30 - 17:00',
                'latitude' => 21.1450,
                'longitude' => 105.8780,
                'price_range' => 'Công lập chuẩn (40 lớp)',
                'description' => 'Trường TH Việt Hùng theo PA3: Sáp nhập từ Tiểu học Việt Hùng (21 lớp, 667 HS) và Tiểu học Việt Hùng 2 (19 lớp, 570 HS). Tổng quy mô 40 lớp, 1.237 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1564981797816-1043664bf78d?auto=format&fit=crop&w=800&q=80',
            ],

            // C. SẮP XẾP CÁC TRƯỜNG THCS (04 TRƯỜNG)
            [
                'name' => 'THCS Nguyễn Huy Tưởng',
                'address' => 'Tổ 4, Thị trấn Đông Anh, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830010',
                'opening_hours' => '07:00 - 17:00',
                'latitude' => 21.1360,
                'longitude' => 105.8450,
                'price_range' => 'Công lập chất lượng cao (30 lớp)',
                'description' => 'Trường THCS Nguyễn Huy Tưởng giữ nguyên quy mô theo PA3 (30 lớp, 1.294 học sinh). Trường CLC.',
                'image_path' => 'https://images.unsplash.com/photo-1541829070764-84a7d30dd3f3?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'THCS Ngô Quyền',
                'address' => 'Thị trấn Đông Anh, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830011',
                'opening_hours' => '07:00 - 17:00',
                'latitude' => 21.1380,
                'longitude' => 105.8420,
                'price_range' => 'Công lập chuẩn (25 lớp)',
                'description' => 'Trường THCS Ngô Quyền giữ nguyên quy mô theo PA3 (25 lớp, 996 học sinh).',
                'image_path' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'THCS An Dương Vương',
                'address' => 'Xã Vân Hà, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830012',
                'opening_hours' => '07:00 - 17:00',
                'latitude' => 21.1650,
                'longitude' => 105.8890,
                'price_range' => 'Công lập chuẩn (50 lớp)',
                'description' => 'Trường THCS An Dương Vương theo PA3: Sáp nhập từ THCS An Dương Vương (19 lớp, 739 HS) và THCS Việt Hùng (31 lớp, 1.281 HS). Tổng quy mô 50 lớp, 2.020 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'THCS Xuân Canh',
                'address' => 'Xã Xuân Canh, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830013',
                'opening_hours' => '07:00 - 17:00',
                'latitude' => 21.0890,
                'longitude' => 105.8560,
                'price_range' => 'Công lập chuẩn (45 lớp)',
                'description' => 'Trường THCS Xuân Canh theo PA3: Sáp nhập từ THCS Đông Hội (31 lớp, 1.112 HS) và THCS Xuân Canh (14 lớp, 497 HS). Tổng quy mô 45 lớp, 1.609 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80',
            ],

            // D. SẮP XẾP CÁC TRƯỜNG NHIỀU CẤP HỌC (05 TRƯỜNG)
            [
                'name' => 'Trường liên cấp Mai Lâm',
                'address' => 'Xã Mai Lâm, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830014',
                'opening_hours' => '07:00 - 17:00',
                'latitude' => 21.1050,
                'longitude' => 105.8800,
                'price_range' => 'Công lập liên cấp (50 lớp)',
                'description' => 'Trường liên cấp Mai Lâm theo PA3: Hợp nhất từ Tiểu học Ngô Tất Tố (29 lớp, 928 HS) và THCS Mai Lâm (21 lớp, 712 HS). Tổng quy mô 50 lớp, 1.640 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1562774053-701939374585?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Trường liên cấp Cổ Loa',
                'address' => 'Xã Cổ Loa, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830015',
                'opening_hours' => '07:00 - 17:00',
                'latitude' => 21.1380,
                'longitude' => 105.8620,
                'price_range' => 'Công lập liên cấp (52 lớp)',
                'description' => 'Trường liên cấp Cổ Loa theo PA3: Hợp nhất từ Tiểu học Cổ Loa (30 lớp, 1.166 HS) và THCS Cổ Loa (22 lớp, 928 HS). Tổng quy mô 52 lớp, 2.094 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Trường liên cấp Đào Duy Tùng',
                'address' => 'Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830016',
                'opening_hours' => '07:00 - 17:00',
                'latitude' => 21.1410,
                'longitude' => 105.8550,
                'price_range' => 'Công lập liên cấp (50 lớp)',
                'description' => 'Trường liên cấp Đào Duy Tùng theo PA3: Hợp nhất từ Tiểu học Đào Duy Tùng (31 lớp, 1.016 HS) và THCS Đào Duy Tùng (19 lớp, 701 HS). Tổng quy mô 50 lớp, 1.717 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Trường liên cấp Dục Tú',
                'address' => 'Xã Dục Tú, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830017',
                'opening_hours' => '07:00 - 17:00',
                'latitude' => 21.1250,
                'longitude' => 105.8950,
                'price_range' => 'Công lập liên cấp (60 lớp)',
                'description' => 'Trường liên cấp Dục Tú theo PA3: Hợp nhất từ Tiểu học Dục Tú (29 lớp, 1.109 HS) và THCS Dục Tú (31 lớp, 1.189 HS). Tổng quy mô 60 lớp, 2.298 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1592280771190-3e2e4d571952?auto=format&fit=crop&w=800&q=80',
            ],
            [
                'name' => 'Trường liên cấp Uy Nỗ',
                'address' => 'Xã Uy Nỗ, Đông Anh, Hà Nội',
                'commune_slug' => 'to-dan-pho-so-6',
                'phone' => '02438830018',
                'opening_hours' => '07:00 - 17:00',
                'latitude' => 21.1390,
                'longitude' => 105.8500,
                'price_range' => 'Công lập liên cấp (50 lớp)',
                'description' => 'Trường liên cấp Uy Nỗ theo PA3: Hợp nhất từ Tiểu học Uy Nỗ (29 lớp, 892 HS) và THCS Uy Nỗ (21 lớp, 601 HS). Tổng quy mô 50 lớp, 1.493 học sinh.',
                'image_path' => 'https://images.unsplash.com/photo-1577896851231-70ef18881754?auto=format&fit=crop&w=800&q=80',
            ],
        ];

        // Seed Wellness & Care
        foreach ($wellnessData as $data) {
            $commune = Commune::where('slug', $data['commune_slug'])->first() ?? $defaultCommune;
            $eatery = Eatery::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'category_id' => $catWellness->id,
                    'commune_id' => $commune->id,
                    'address' => $data['address'],
                    'phone' => $data['phone'],
                    'opening_hours' => $data['opening_hours'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'price_range' => $data['price_range'],
                    'description' => $data['description'],
                    'image_path' => $data['image_path'],
                    'status' => 'active',
                    'rating' => 4.7,
                ]
            );

            // Seed wellness services
            $services = [];
            $slug = $eatery->slug;
            if (str_contains($slug, 'benh-vien-da-khoa')) {
                $services = [
                    [
                        'name' => 'Khám bệnh tổng quát',
                        'price' => 150000,
                        'description' => 'Khám sức khỏe tổng quát định kỳ bởi các bác sĩ chuyên khoa giàu kinh nghiệm.',
                        'image_path' => 'https://images.unsplash.com/photo-1579684389782-64d84b5e901a?auto=format&fit=crop&w=800&q=80',
                        'duration' => '30 - 45 phút',
                    ],
                    [
                        'name' => 'Khám bệnh theo yêu cầu',
                        'price' => 300000,
                        'description' => 'Khám tự nguyện chất lượng cao, ưu tiên quy trình nhanh gọn, bác sĩ Trưởng khoa/Phó khoa trực tiếp khám.',
                        'image_path' => 'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=800&q=80',
                        'duration' => '30 phút',
                    ],
                    [
                        'name' => 'Chụp cắt lớp vi tính (CT Scanner)',
                        'price' => 1200000,
                        'description' => 'Chẩn đoán hình ảnh kỹ thuật cao bằng máy chụp đa dãy thế hệ mới, kết quả chính xác nhanh chóng.',
                        'image_path' => 'https://images.unsplash.com/photo-1516062423079-7ca13cdc7f5a?auto=format&fit=crop&w=800&q=80',
                        'duration' => '20 phút',
                    ],
                ];
            } elseif (str_contains($slug, 'trung-tam-y-te')) {
                $services = [
                    [
                        'name' => 'Tiêm chủng vắc xin trọn gói',
                        'price' => 500000,
                        'description' => 'Gói tiêm chủng đầy đủ vắc xin phòng bệnh cho trẻ em và người lớn, vắc xin bảo quản chuẩn GSP.',
                        'image_path' => 'https://images.unsplash.com/photo-1605647540924-852290f6b0d5?auto=format&fit=crop&w=800&q=80',
                        'duration' => '15 phút',
                    ],
                    [
                        'name' => 'Khám sức khỏe định kỳ / Học lái xe / Đi làm',
                        'price' => 180000,
                        'description' => 'Xét nghiệm và khám lâm sàng cấp giấy chứng nhận sức khỏe nhanh chóng, chuẩn quy định Bộ Y tế.',
                        'image_path' => 'https://images.unsplash.com/photo-1505751172876-fa1923c5c528?auto=format&fit=crop&w=800&q=80',
                        'duration' => '60 phút',
                    ],
                ];
            } elseif (str_contains($slug, 'sen-spa')) {
                $services = [
                    [
                        'name' => 'Massage Body đá nóng trị liệu',
                        'price' => 350000,
                        'description' => 'Giảm căng cơ, phục hồi sinh lực bằng đá núi lửa bazan kết hợp tinh dầu thảo dược thiên nhiên.',
                        'image_path' => 'https://images.unsplash.com/photo-1600334089648-b0d9d3028eb2?auto=format&fit=crop&w=800&q=80',
                        'duration' => '60 phút',
                    ],
                    [
                        'name' => 'Xông hơi thảo dược & Ngâm chân thuốc bắc',
                        'price' => 150000,
                        'description' => 'Đào thải độc tố cơ thể qua da, lưu thông khí huyết, đem lại giấc ngủ ngon và sâu hơn.',
                        'image_path' => 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?auto=format&fit=crop&w=800&q=80',
                        'duration' => '45 phút',
                    ],
                    [
                        'name' => 'Chăm sóc da mặt chuyên sâu bằng Nhân sâm',
                        'price' => 450000,
                        'description' => 'Cung cấp dưỡng chất từ sâm tươi Hàn Quốc, trẻ hóa làn da, nâng cơ mặt và mờ thâm nám.',
                        'image_path' => 'https://images.unsplash.com/photo-1512290923902-8a9f81dc236c?auto=format&fit=crop&w=800&q=80',
                        'duration' => '75 phút',
                    ],
                ];
            } elseif (str_contains($slug, 'thu-cuc-clinic')) {
                $services = [
                    [
                        'name' => 'Trị mụn công nghệ cao Blue Light',
                        'price' => 500000,
                        'description' => 'Sử dụng ánh sáng xanh triệt tiêu vi khuẩn gây mụn, gom cồi mụn nhanh và ngăn ngừa sẹo thâm.',
                        'image_path' => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=800&q=80',
                        'duration' => '60 phút',
                    ],
                    [
                        'name' => 'Nâng cơ trẻ hóa da Hifu Liftera',
                        'price' => 1800000,
                        'description' => 'Sóng siêu âm hội tụ cường độ cao giúp tái tạo collagen, làm săn chắc cơ mặt chảy xệ không xâm lấn.',
                        'image_path' => 'https://images.unsplash.com/photo-1616394584738-fc6e612e71b9?auto=format&fit=crop&w=800&q=80',
                        'duration' => '90 phút',
                    ],
                ];
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('wellness_services')) {
                foreach ($services as $srv) {
                    \App\Models\WellnessService::updateOrCreate(
                        ['eatery_id' => $eatery->id, 'name' => $srv['name']],
                        $srv
                    );
                }
            }
        }

        // Seed Education
        Eatery::where('category_id', $catEducation->id)->delete();
        foreach ($educationData as $data) {
            $commune = Commune::where('slug', $data['commune_slug'])->first() ?? $defaultCommune;
            $eatery = Eatery::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'name' => $data['name'],
                    'category_id' => $catEducation->id,
                    'commune_id' => $commune->id,
                    'address' => $data['address'],
                    'phone' => $data['phone'],
                    'opening_hours' => $data['opening_hours'],
                    'latitude' => $data['latitude'],
                    'longitude' => $data['longitude'],
                    'price_range' => $data['price_range'],
                    'description' => $data['description'],
                    'image_path' => $data['image_path'],
                    'status' => 'active',
                    'rating' => 4.8,
                ]
            );

            // Seed education programs
            $programs = [];
            $slug = $eatery->slug;
            if (str_contains($slug, 'lien-ha')) {
                $programs = [
                    [
                        'name' => 'Hệ đào tạo chuẩn THPT Quốc gia',
                        'description' => 'Chương trình giáo dục phổ thông theo khung chuẩn của Bộ Giáo dục và Đào tạo, định hướng tốt nghiệp và xét tuyển đại học.',
                        'image_path' => 'https://images.unsplash.com/photo-1427504494785-3a9ca7044f45?auto=format&fit=crop&w=800&q=80',
                        'duration' => '3 năm',
                        'tuition_fee' => 'Học phí công lập chuẩn',
                    ],
                    [
                        'name' => 'Khối lớp học chất lượng cao (A0, A1, D1)',
                        'description' => 'Tập trung ôn luyện chuyên sâu các môn thi khối A0 (Toán, Lý, Hóa), A1 (Toán, Lý, Anh) và D1 (Toán, Văn, Anh) với giáo viên đầu ngành.',
                        'image_path' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80',
                        'duration' => '3 năm',
                        'tuition_fee' => 'Học phí công lập + Phụ phí ôn tập',
                    ],
                ];
            } elseif (str_contains($slug, 'co-loa')) {
                $programs = [
                    [
                        'name' => 'Hệ đào tạo THPT chính quy chuẩn quốc gia',
                        'description' => 'Giáo dục toàn diện văn hóa kết hợp với các chương trình tìm hiểu di sản lịch sử Cổ Loa, rèn luyện kỹ năng sống.',
                        'image_path' => 'https://images.unsplash.com/photo-1546410531-bb4caa6b424d?auto=format&fit=crop&w=800&q=80',
                        'duration' => '3 năm',
                        'tuition_fee' => 'Học phí công lập chuẩn',
                    ],
                    [
                        'name' => 'Lớp chọn ngoại ngữ (Tiếng Anh - Tiếng Trung tăng cường)',
                        'description' => 'Tập trung nâng cao năng lực giao tiếp và cam kết chuẩn đầu ra IELTS từ 5.5 trở lên hoặc HSK tương đương.',
                        'image_path' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80',
                        'duration' => '3 năm',
                        'tuition_fee' => '500.000đ / tháng học bổ trợ',
                    ],
                ];
            } elseif (str_contains($slug, 'thpt-nguyen-huy-tuong')) {
                $programs = [
                    [
                        'name' => 'Khối chuyên Khoa học Tự nhiên',
                        'description' => 'Lớp chuyên sâu Toán, Vật Lý, Hóa Học, Sinh Học định hướng thi học sinh giỏi cấp Thành phố và Quốc gia.',
                        'image_path' => 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?auto=format&fit=crop&w=800&q=80',
                        'duration' => '3 năm',
                        'tuition_fee' => 'Học phí công lập chuẩn',
                    ],
                    [
                        'name' => 'Khối chuyên Khoa học Xã hội & Nhân văn',
                        'description' => 'Lớp chuyên Ngữ Văn, Lịch Sử, Địa Lý và Tiếng Anh phát triển tư duy xã hội và kỹ năng tranh biện.',
                        'image_path' => 'https://images.unsplash.com/photo-1497633762265-9d179a990aa6?auto=format&fit=crop&w=800&q=80',
                        'duration' => '3 năm',
                        'tuition_fee' => 'Học phí công lập chuẩn',
                    ],
                ];
            } elseif (str_contains($slug, 'thcs-nguyen-huy-tuong')) {
                $programs = [
                    [
                        'name' => 'Hệ THCS Chất lượng cao trọng điểm',
                        'description' => 'Mô hình lớp chất lượng cao mũi nhọn của xã, cơ sở vật chất hiện đại, học 2 buổi/ngày định hướng thi vào các trường THPT chuyên.',
                        'image_path' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80',
                        'duration' => '4 năm',
                        'tuition_fee' => '1.200.000đ / tháng',
                    ],
                    [
                        'name' => 'Câu lạc bộ Kỹ năng sống & STEM',
                        'description' => 'Chương trình phát triển toàn diện kỹ năng mềm, thuyết trình trước đám đông kết hợp lắp ráp robot và lập trình STEM cơ bản.',
                        'image_path' => 'https://images.unsplash.com/photo-1564981797816-1043664bf78d?auto=format&fit=crop&w=800&q=80',
                        'duration' => 'Học theo học kỳ (10 tháng)',
                        'tuition_fee' => '300.000đ / tháng tự nguyện',
                    ],
                ];
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('education_programs')) {
                foreach ($programs as $prog) {
                    \App\Models\EducationProgram::updateOrCreate(
                        ['eatery_id' => $eatery->id, 'name' => $prog['name']],
                        $prog
                    );
                }
            }
        }
    }
}
