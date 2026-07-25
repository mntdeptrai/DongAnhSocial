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

        // 2. Education data (empty to delete all schools)
        $educationData = [];

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
