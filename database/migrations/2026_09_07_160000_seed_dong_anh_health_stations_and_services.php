<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Eatery;
use App\Models\Category;
use App\Models\WellnessService;
use App\Models\Commune;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $wellnessCat = Category::where('slug', 'wellness-care')->first();
        if (!$wellnessCat) {
            return;
        }

        $facilitiesData = [
            // 1. Trạm Y tế xã Đông Anh (Trạm chính)
            [
                'account' => [
                    'username' => 'tramytexadonganh',
                    'email' => 'tramytexadonganh@donganh.gov.vn',
                    'name' => 'Trạm Y Tế Xã Đông Anh',
                    'phone' => '0389928304',
                ],
                'eatery' => [
                    'name' => 'Trạm Y tế xã Đông Anh',
                    'slug' => 'tram-y-te-xa-dong-anh',
                    'commune_slug' => 'thon-cuong-no',
                    'address' => 'Thôn Cường Nỗ, Xã Đông Anh, Hà Nội',
                    'phone' => '0389 928 304',
                    'opening_hours' => 'Trực cấp cứu 24/7 | Khám hành chính: 07:30 - 17:00',
                    'price_range' => 'Khám BHYT / Miễn phí tiêm chủng mở rộng',
                    'latitude' => 21.139304,
                    'longitude' => 105.853405,
                    'description' => 'Trạm Y tế xã Đông Anh thành lập theo Quyết định 01/QĐ-UBND ngày 01/7/2025 của UBND xã Đông Anh. Trạm thực hiện nhiệm vụ khám chữa bệnh BHYT, phòng chống dịch bệnh, tiêm chủng vắc xin và quản lý sức khỏe cộng đồng với 03 khoa, 02 phòng và 06 điểm Y tế trực thuộc. Tổng diện tích trạm chính là 3.674m², thực hiện 413 dịch vụ kỹ thuật được Sở Y tế Hà Nội phê duyệt (QĐ 2050/QĐ-SYT).',
                    'image_path' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80',
                    'storytelling_data' => [
                        'heritage_year' => 'Quyết định 01/QĐ-UBND (01/7/2025)',
                        'area' => '3.674 m²',
                        'staff_total' => '84 người (80 viên chức, 04 hợp đồng)',
                        'doctors_count' => 10,
                        'pharmacists_count' => 9,
                        'nurses_count' => 23,
                        'physicians_count' => 17,
                        'midwives_count' => 10,
                        'approved_services' => 413,
                        'contacts' => [
                            ['name' => 'Nguyễn Thu Hà', 'role' => 'Giám đốc TYT', 'phone' => '0389928304'],
                            ['name' => 'Nguyễn Thị Hậu', 'role' => 'Phó giám đốc TYT', 'phone' => '0936534226'],
                            ['name' => 'Ngô Thị Bích Liên', 'role' => 'Tổ trưởng Khám Chữa Bệnh (KCB, Lao, HIV)', 'phone' => '0976551863'],
                            ['name' => 'Lê Đàm Hải Yến', 'role' => 'Tổ trưởng Tổ phòng bệnh', 'phone' => '0363551036'],
                            ['name' => 'Nguyễn Thị Duyên', 'role' => 'Tổ trưởng Hành chính', 'phone' => '0388320979'],
                            ['name' => 'Trần Phương', 'role' => 'Đầu mối phòng chống dịch bệnh', 'phone' => '0356418799'],
                            ['name' => 'Trương Thị Hồng Hạnh', 'role' => 'Đầu mối an toàn thực phẩm', 'phone' => '0968969168'],
                        ],
                    ],
                ],
                'services' => [
                    [
                        'name' => 'Sơ Cấp Cứu Ban Đầu & Trực Khẩn Cấp 24/7',
                        'duration' => 'Trực 24/7',
                        'price' => 0,
                        'description' => 'Tiếp nhận sơ cấp cứu ban đầu, xử trí chấn thương, chuyển tuyến an toàn cho người dân trên địa bàn xã.',
                    ],
                    [
                        'name' => 'Khám Chữa Bệnh BHYT & Quản Lý Bệnh Mãn Tính',
                        'duration' => '07:30 - 17:00',
                        'price' => 0,
                        'description' => 'Khám chữa bệnh theo tuyến Bảo hiểm y tế, theo dõi và phát thuốc định kỳ cho bệnh nhân Tăng huyết áp, Đái tháo đường.',
                    ],
                    [
                        'name' => 'Tiêm Chủng Vắc Xin Mở Rộng & Giám Sát Dịch Bệnh',
                        'duration' => 'Theo lịch định kỳ',
                        'price' => 0,
                        'description' => 'Tiêm vắc xin phòng bệnh cho trẻ em dưới 1 tuổi, phụ nữ mang thai và triển khai công tác phòng chống dịch bệnh.',
                    ],
                    [
                        'name' => 'Khám Sức Khỏe Sinh Sản & Y Học Cổ Truyền',
                        'duration' => '08:00 - 16:30',
                        'price' => 0,
                        'description' => 'Tư vấn sức khỏe sinh sản, chăm sóc bà mẹ trẻ em, châm cứu, bấm huyệt trị liệu y học cổ truyền.',
                    ],
                ],
            ],

            // 2. Điểm Y Tế Mai Lâm
            [
                'account' => [
                    'username' => 'diemytemailam',
                    'email' => 'diemytemailam@donganh.gov.vn',
                    'name' => 'Điểm Y Tế Mai Lâm',
                    'phone' => '0984053265',
                ],
                'eatery' => [
                    'name' => 'Điểm Y tế Mai Lâm - Trạm Y tế xã Đông Anh',
                    'slug' => 'diem-y-te-mai-lam',
                    'commune_slug' => 'thon-mai-lam',
                    'address' => 'Thôn Mai Lâm, Xã Đông Anh, Hà Nội',
                    'phone' => '0984 053 265',
                    'opening_hours' => '07:30 - 17:00 (Trực sơ cấp cứu 24/7)',
                    'price_range' => 'Khám BHYT / Miễn phí tiêm chủng mở rộng',
                    'latitude' => 21.1215,
                    'longitude' => 105.8850,
                    'description' => 'Điểm Y tế Mai Lâm trực thuộc Trạm Y tế xã Đông Anh. Đơn vị thực hiện 273 dịch vụ kỹ thuật y tế được Sở Y tế Hà Nội phê duyệt (QĐ 2102/QĐ-SYT). Cơ sở có diện tích 2.925,3m² với đội ngũ 08 nhân viên y tế tận tâm, đáp ứng nhu cầu chăm sóc sức khỏe ban đầu cho nhân dân thôn Mai Lâm.',
                    'image_path' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=800&q=80',
                    'storytelling_data' => [
                        'area' => '2.925,3 m²',
                        'staff_count' => 8,
                        'approved_services' => 273,
                        'head' => 'Hoàng Thị Minh Huệ (Trưởng điểm Y tế Mai Lâm - 0984 053 265)',
                    ],
                ],
                'services' => [
                    [
                        'name' => 'Khám BHYT Ban Đầu & Tiêm Chủng Mở Rộng Mai Lâm',
                        'duration' => '07:30 - 17:00',
                        'price' => 0,
                        'description' => 'Khám bệnh BHYT tuyến xã, theo dõi sức khỏe bà mẹ trẻ em và tiêm vắc xin định kỳ cho trẻ tại thôn Mai Lâm.',
                    ],
                ],
            ],

            // 3. Điểm Y Tế Đông Hội
            [
                'account' => [
                    'username' => 'diemytedonghoi',
                    'email' => 'diemytedonghoi@donganh.gov.vn',
                    'name' => 'Điểm Y Tế Đông Hội',
                    'phone' => '0986312946',
                ],
                'eatery' => [
                    'name' => 'Điểm Y tế Đông Hội - Trạm Y tế xã Đông Anh',
                    'slug' => 'diem-y-te-dong-hoi',
                    'commune_slug' => 'thon-hoi-phu',
                    'address' => 'Thôn Hội Phụ, Xã Đông Anh, Hà Nội',
                    'phone' => '0986 312 946',
                    'opening_hours' => '07:30 - 17:00 (Trực sơ cấp cứu 24/7)',
                    'price_range' => 'Khám BHYT / Miễn phí tiêm chủng mở rộng',
                    'latitude' => 21.1090,
                    'longitude' => 105.8750,
                    'description' => 'Điểm Y tế Đông Hội trực thuộc Trạm Y tế xã Đông Anh. Tọa lạc tại Thôn Hội Phụ với diện tích 3.793m², 09 nhân viên y tế, thực hiện 214 dịch vụ kỹ thuật y tế phê duyệt theo QĐ 2102/QĐ-SYT của Sở Y tế Hà Nội.',
                    'image_path' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80',
                    'storytelling_data' => [
                        'area' => '3.793 m²',
                        'staff_count' => 9,
                        'approved_services' => 214,
                        'head' => 'Lưu Đức Hướng (Trưởng điểm Y tế Đông Hội - 0986 312 946)',
                    ],
                ],
                'services' => [
                    [
                        'name' => 'Sơ Cấp Cứu Ban Đầu & Khám BHYT Thôn Hội Phụ',
                        'duration' => '07:30 - 17:00',
                        'price' => 0,
                        'description' => 'Tiếp nhận sơ cứu ban đầu, tư vấn phòng chống dịch bệnh và tiêm chủng mở rộng tại khu vực Đông Hội.',
                    ],
                ],
            ],

            // 4. Điểm Y Tế Xuân Canh
            [
                'account' => [
                    'username' => 'diemytexuancanh',
                    'email' => 'diemytexuancanh@donganh.gov.vn',
                    'name' => 'Điểm Y Tế Xuân Canh',
                    'phone' => '0353781083',
                ],
                'eatery' => [
                    'name' => 'Điểm Y tế Xuân Canh - Trạm Y tế xã Đông Anh',
                    'slug' => 'diem-y-te-xuan-canh',
                    'commune_slug' => 'thon-xuan-canh',
                    'address' => 'Thôn Xuân Canh, Xã Đông Anh, Hà Nội',
                    'phone' => '0353 781 083',
                    'opening_hours' => '07:30 - 17:00 (Trực sơ cấp cứu 24/7)',
                    'price_range' => 'Khám BHYT / Miễn phí tiêm chủng mở rộng',
                    'latitude' => 21.1150,
                    'longitude' => 105.8620,
                    'description' => 'Điểm Y tế Xuân Canh trực thuộc Trạm Y tế xã Đông Anh. Tọa lạc tại Thôn Xuân Canh với diện tích 3.106,9m², 07 nhân viên y tế, thực hiện 432 dịch vụ kỹ thuật y tế phê duyệt theo QĐ 2102/QĐ-SYT của Sở Y tế Hà Nội.',
                    'image_path' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=800&q=80',
                    'storytelling_data' => [
                        'area' => '3.106,9 m²',
                        'staff_count' => 7,
                        'approved_services' => 432,
                        'head' => 'Trịnh Thị Kiên (Trưởng điểm Y tế Xuân Canh - 0353 781 083)',
                    ],
                ],
                'services' => [
                    [
                        'name' => 'Khám Chữa Bệnh BHYT & Chăm Sóc Sức Khỏe Xuân Canh',
                        'duration' => '07:30 - 17:00',
                        'price' => 0,
                        'description' => 'Cung cấp 432 dịch vụ kỹ thuật y tế cơ sở, tiêm chủng phòng bệnh và theo dõi bệnh nhân mãn tính tại Xuân Canh.',
                    ],
                ],
            ],

            // 5. Điểm Y Tế Cổ Loa
            [
                'account' => [
                    'username' => 'diemytecoloa',
                    'email' => 'diemytecoloa@donganh.gov.vn',
                    'name' => 'Điểm Y Tế Cổ Loa',
                    'phone' => '0982240767',
                ],
                'eatery' => [
                    'name' => 'Điểm Y tế Cổ Loa - Trạm Y tế xã Đông Anh',
                    'slug' => 'diem-y-te-co-loa',
                    'commune_slug' => 'thon-hong-lac',
                    'address' => 'Thôn Hùng Lạc, Xã Đông Anh, Hà Nội',
                    'phone' => '0982 240 767',
                    'opening_hours' => '07:30 - 17:00 (Trực sơ cấp cứu 24/7)',
                    'price_range' => 'Khám BHYT / Miễn phí tiêm chủng mở rộng',
                    'latitude' => 21.1180,
                    'longitude' => 105.8950,
                    'description' => 'Điểm Y tế Cổ Loa trực thuộc Trạm Y tế xã Đông Anh. Tọa lạc tại Thôn Hùng Lạc, diện tích 2.019m², 09 nhân viên y tế, thực hiện 386 dịch vụ kỹ thuật y tế phê duyệt theo QĐ 2102/QĐ-SYT.',
                    'image_path' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80',
                    'storytelling_data' => [
                        'area' => '2.019 m²',
                        'staff_count' => 9,
                        'approved_services' => 386,
                        'head' => 'Đặng Đạo Khánh (Trưởng điểm Y tế Cổ Loa - 0982 240 767)',
                    ],
                ],
                'services' => [
                    [
                        'name' => 'Sơ Cấp Cứu Ban Đầu & Tiêm Chủng Mở Rộng Cổ Loa',
                        'duration' => '07:30 - 17:00',
                        'price' => 0,
                        'description' => 'Khám bệnh BHYT, xử trí cấp cứu khẩn cấp, tiêm chủng vắc xin và quản lý sức khỏe nhân dân vùng Cổ Loa.',
                    ],
                ],
            ],

            // 6. Điểm Y Tế Việt Hùng
            [
                'account' => [
                    'username' => 'diemyteviethung',
                    'email' => 'diemyteviethung@donganh.gov.vn',
                    'name' => 'Điểm Y Tế Việt Hùng',
                    'phone' => '0392121971',
                ],
                'eatery' => [
                    'name' => 'Điểm Y tế Việt Hùng - Trạm Y tế xã Đông Anh',
                    'slug' => 'diem-y-te-viet-hung',
                    'commune_slug' => 'thon-viet-hung',
                    'address' => 'Thôn Việt Hùng, Xã Đông Anh, Hà Nội',
                    'phone' => '0392 121 971',
                    'opening_hours' => '07:30 - 17:00 (Trực sơ cấp cứu 24/7)',
                    'price_range' => 'Khám BHYT / Miễn phí tiêm chủng mở rộng',
                    'latitude' => 21.1450,
                    'longitude' => 105.8750,
                    'description' => 'Điểm Y tế Việt Hùng trực thuộc Trạm Y tế xã Đông Anh. Tọa lạc tại Thôn Việt Hùng, diện tích 976m², 09 nhân viên y tế, thực hiện 281 dịch vụ kỹ thuật y tế được phê duyệt (QĐ 2102/QĐ-SYT).',
                    'image_path' => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=800&q=80',
                    'storytelling_data' => [
                        'area' => '976 m²',
                        'staff_count' => 9,
                        'approved_services' => 281,
                        'head' => 'Nguyễn Thị Minh Tâm (Trưởng điểm Y tế Việt Hùng - 0392 121 971)',
                    ],
                ],
                'services' => [
                    [
                        'name' => 'Khám BHYT & Quản Lý Dịch Bệnh Việt Hùng',
                        'duration' => '07:30 - 17:00',
                        'price' => 0,
                        'description' => 'Khám bệnh BHYT cơ sở, tiêm chủng vắc xin định kỳ và truyền thông sức khỏe cho bà con thôn Việt Hùng.',
                    ],
                ],
            ],

            // 7. Điểm Y Tế Dục Tú
            [
                'account' => [
                    'username' => 'diemyteductu',
                    'email' => 'diemyteductu@donganh.gov.vn',
                    'name' => 'Điểm Y Tế Dục Tú',
                    'phone' => '0983647560',
                ],
                'eatery' => [
                    'name' => 'Điểm Y tế Dục Tú - Trạm Y tế xã Đông Anh',
                    'slug' => 'diem-y-te-duc-tu',
                    'commune_slug' => 'thon-duc-tu',
                    'address' => 'Thôn Dục Tú, Xã Đông Anh, Hà Nội',
                    'phone' => '0983 647 560',
                    'opening_hours' => '07:30 - 17:00 (Trực sơ cấp cứu 24/7)',
                    'price_range' => 'Khám BHYT / Miễn phí tiêm chủng mở rộng',
                    'latitude' => 21.1550,
                    'longitude' => 105.9050,
                    'description' => 'Điểm Y tế Dục Tú trực thuộc Trạm Y tế xã Đông Anh. Tọa lạc tại Thôn Dục Tú, diện tích 1.917m², 10 nhân viên y tế, thực hiện 386 dịch vụ kỹ thuật y tế được phê duyệt (QĐ 2102/QĐ-SYT).',
                    'image_path' => 'https://images.unsplash.com/photo-1516549655169-df83a0774514?auto=format&fit=crop&w=800&q=80',
                    'storytelling_data' => [
                        'area' => '1.917 m²',
                        'staff_count' => 10,
                        'approved_services' => 386,
                        'head' => 'Nguyễn Thị Ninh (Phụ trách điểm Y tế Dục Tú - 0983 647 560)',
                    ],
                ],
                'services' => [
                    [
                        'name' => 'Khám BHYT & Sơ Cứu Khẩn Cấp Dục Tú',
                        'duration' => '07:30 - 17:00',
                        'price' => 0,
                        'description' => 'Cung cấp 386 dịch vụ kỹ thuật y tế cơ sở, tiêm chủng phòng bệnh và theo dõi bệnh nhân mãn tính tại Dục Tú.',
                    ],
                ],
            ],

            // 8. Tổ Y Tế Uy Nỗ
            [
                'account' => [
                    'username' => 'toyteuyno',
                    'email' => 'toyteuyno@donganh.gov.vn',
                    'name' => 'Tổ Y Tế Uy Nỗ',
                    'phone' => '0984418575',
                ],
                'eatery' => [
                    'name' => 'Tổ Y tế Uy Nỗ - Trạm Y tế xã Đông Anh',
                    'slug' => 'to-y-te-uy-no',
                    'commune_slug' => 'thon-uy-no',
                    'address' => 'Thôn Uy Nỗ, Xã Đông Anh, Hà Nội',
                    'phone' => '0984 418 575',
                    'opening_hours' => '07:30 - 17:00',
                    'price_range' => 'Khám BHYT / Miễn phí tiêm chủng mở rộng',
                    'latitude' => 21.1350,
                    'longitude' => 105.8600,
                    'description' => 'Tổ Y tế Uy Nỗ thuộc Trạm Y tế xã Đông Anh. Phụ trách sơ cấp cứu ban đầu, theo dõi sức khỏe và giám sát dịch bệnh tại thôn Uy Nỗ.',
                    'image_path' => 'https://images.unsplash.com/photo-1584515979956-d9f6e5d09982?auto=format&fit=crop&w=800&q=80',
                    'storytelling_data' => [
                        'head' => 'Trương Thị Hồng (Phụ trách tổ Y tế Uy Nỗ - 0984 418 575)',
                    ],
                ],
                'services' => [
                    [
                        'name' => 'Sơ Cấp Cứu & Theo Dõi Dịch Bệnh Uy Nỗ',
                        'duration' => '07:30 - 17:00',
                        'price' => 0,
                        'description' => 'Theo dõi sức khỏe nhân dân, sơ cấp cứu ban đầu và tiêm vắc xin phòng bệnh thôn Uy Nỗ.',
                    ],
                ],
            ],

            // 9. Bệnh viện Đa khoa Đông Anh
            [
                'account' => [
                    'username' => 'benhviendakhoadonganh',
                    'email' => 'benhviendakhoadonganh@donganh.gov.vn',
                    'name' => 'Bệnh Viện Đa Khoa Đông Anh',
                    'phone' => '02438832445',
                ],
                'eatery' => [
                    'name' => 'Bệnh viện Đa khoa Đông Anh',
                    'slug' => 'benh-vien-da-khoa-dong-anh-5fwWa',
                    'commune_slug' => 'thon-cuong-no',
                    'address' => '48 Đ. Đản Dị, Đông Anh, Hà Nội, Việt Nam',
                    'phone' => '024 3883 2445',
                    'opening_hours' => 'Trực cấp cứu 24/7 (Khám hành chính: 07:00 - 17:00)',
                    'price_range' => 'Khám BHYT / 38.700đ - 1.200.000đ',
                    'latitude' => 21.139304,
                    'longitude' => 105.853405,
                    'description' => 'Bệnh viện Đa khoa Đông Anh là cơ sở y tế hạng I tuyến huyện thuộc Sở Y tế Hà Nội. Bệnh viện được trang bị hệ thống máy móc hiện đại (Chụp CT Scanner, Siêu âm 4D, Hệ thống xét nghiệm tự động, Phẫu thuật nội soi), quy mô 500+ giường bệnh, hỗ trợ cấp cứu 24/24h cho nhân dân huyện Đông Anh và khu vực lân cận.',
                    'image_path' => 'https://images.unsplash.com/photo-1519494026892-80bbd2d6fd0d?auto=format&fit=crop&w=800&q=80',
                ],
                'services' => [
                    [
                        'name' => 'Khám Cấp Cứu & Sơ Cứu Ban Đầu 24/7',
                        'duration' => '24/7 Khẩn cấp',
                        'price' => 0,
                        'description' => 'Tiếp nhận và xử trí các trường hợp cấp cứu, chấn thương, đột quỵ 24/24 giờ tất cả các ngày trong tuần.',
                    ],
                    [
                        'name' => 'Khám BHYT & Khám Chuyên Khoa (Nội, Ngoại, Nhi, Sản)',
                        'duration' => '07:00 - 17:00',
                        'price' => 38700,
                        'description' => 'Khám và điều trị theo tuyến Bảo hiểm y tế, khám chuyên khoa sâu với đội ngũ bác sĩ giàu kinh nghiệm.',
                    ],
                    [
                        'name' => 'Chẩn Đoán Hình Ảnh & Xét Nghiệm (CT Scanner, Siêu Âm 4D)',
                        'duration' => '15 - 30 phút',
                        'price' => 500000,
                        'description' => 'Chụp cắt lớp vi tính (CT Scanner), chụp X-quang kỹ thuật số, siêu âm tim/mạch máu, xét nghiệm sinh hóa máu tự động.',
                    ],
                    [
                        'name' => 'Khám Sức Khỏe Định Kỳ & Giấy Khám Lái Xe / Học Tập',
                        'duration' => '45 - 60 phút',
                        'price' => 250000,
                        'description' => 'Khám sức khỏe tổng quát cấp giấy xác nhận cho người lao động, học sinh sinh viên, đổi giấy phép lái xe.',
                    ],
                ],
            ],

            // 10. VNVC Đông Anh
            [
                'account' => [
                    'username' => 'vnvcdonganh',
                    'email' => 'vnvc.donganh@vnvc.vn',
                    'name' => 'Trung Tâm Tiêm Chủng VNVC Đông Anh',
                    'phone' => '02871026595',
                ],
                'eatery' => [
                    'name' => 'Trung tâm tiêm chủng VNVC Đông Anh',
                    'slug' => 'trung-tam-tiem-chung-vnvc-dong-anh-O8AdL',
                    'commune_slug' => 'thon-dong-anh',
                    'address' => 'Số 23, Tổ 4, Thị trấn Đông Anh, Hà Nội',
                    'phone' => '028 7102 6595',
                    'opening_hours' => '07:30 - 17:00 (Tất cả các ngày trong tuần)',
                    'price_range' => '100.000đ - 2.500.000đ',
                    'latitude' => 21.1380,
                    'longitude' => 105.8480,
                    'description' => 'Trung tâm tiêm chủng VNVC Đông Anh cung cấp đầy đủ các loại vắc xin thế hệ mới cho trẻ em và người lớn, quy trình tiêm chủng an toàn, bảo quản vắc xin đạt chuẩn GSP quốc tế.',
                    'image_path' => 'https://images.unsplash.com/photo-1618961734760-466979ce35b0?auto=format&fit=crop&w=800&q=80',
                ],
                'services' => [
                    [
                        'name' => 'Tiêm Chủng Vắc Xin Cho Trẻ Em & Trẻ Sơ Sinh',
                        'duration' => '30 - 45 phút',
                        'price' => 350000,
                        'description' => 'Vắc xin 6 trong 1, Phế cầu, Rota virus, Cúm, Sởi - Quai bị - Rubella đầy đủ xuất xứ chính hãng.',
                    ],
                    [
                        'name' => 'Tiêm Chủng Cho Người Lớn & Phụ Nữ Mang Thai',
                        'duration' => '30 phút',
                        'price' => 550000,
                        'description' => 'Gói tiêm vắc xin HPV, Viêm gan B, Thủy đậu, Cúm mùa, Phế cầu dành cho người lớn.',
                    ],
                ],
            ],
        ];

        foreach ($facilitiesData as $item) {
            // 1. Create or update User Account
            $accData = $item['account'];
            $user = User::where('email', $accData['email'])
                ->orWhere('username', $accData['username'])
                ->first();

            if (!$user) {
                $user = User::create([
                    'username' => $accData['username'],
                    'name' => $accData['name'],
                    'email' => $accData['email'],
                    'phone' => $accData['phone'],
                    'password' => Hash::make('Password123'),
                    'role' => 'health_station',
                    'is_verified' => true,
                ]);
            } else {
                $user->update([
                    'name' => $accData['name'],
                    'phone' => $accData['phone'],
                    'role' => 'seller',
                    'is_verified' => true,
                ]);
            }

            // 2. Commune ID lookup
            $eateryData = $item['eatery'];
            $commune = Commune::where('slug', $eateryData['commune_slug'])->first();

            // 3. Create or update Eatery record
            $eatery = Eatery::where('slug', $eateryData['slug'])->first();
            if (!$eatery) {
                $eatery = Eatery::create([
                    'user_id' => $user->id,
                    'category_id' => $wellnessCat->id,
                    'commune_id' => $commune?->id,
                    'name' => $eateryData['name'],
                    'slug' => $eateryData['slug'],
                    'address' => $eateryData['address'],
                    'phone' => $eateryData['phone'],
                    'opening_hours' => $eateryData['opening_hours'],
                    'price_range' => $eateryData['price_range'],
                    'latitude' => $eateryData['latitude'],
                    'longitude' => $eateryData['longitude'],
                    'description' => $eateryData['description'],
                    'image_path' => $eateryData['image_path'],
                    'status' => 'active',
                    'is_featured' => true,
                    'rating' => 5.0,
                    'storytelling_data' => $eateryData['storytelling_data'] ?? null,
                ]);
            } else {
                $eatery->update([
                    'user_id' => $user->id,
                    'category_id' => $wellnessCat->id,
                    'commune_id' => $commune?->id ?: $eatery->commune_id,
                    'name' => $eateryData['name'],
                    'address' => $eateryData['address'],
                    'phone' => $eateryData['phone'],
                    'opening_hours' => $eateryData['opening_hours'],
                    'price_range' => $eateryData['price_range'],
                    'description' => $eateryData['description'],
                    'image_path' => $eateryData['image_path'] ?: $eatery->image_path,
                    'status' => 'active',
                    'storytelling_data' => $eateryData['storytelling_data'] ?? $eatery->storytelling_data,
                ]);
            }

            // 4. Create wellness services
            if (isset($item['services']) && is_array($item['services'])) {
                foreach ($item['services'] as $svcData) {
                    WellnessService::firstOrCreate(
                        [
                            'eatery_id' => $eatery->id,
                            'name' => $svcData['name'],
                        ],
                        [
                            'duration' => $svcData['duration'],
                            'price' => $svcData['price'],
                            'description' => $svcData['description'],
                            'image_path' => $eatery->image_path,
                        ]
                    );
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
