<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Helpers\VietnameseSeoHelper;

return new class extends Migration
{
    public function up(): void
    {
        $schoolsData = [
            'mn-phuc-loc' => [
                'mergedSchool' => [
                    'name' => 'Trường Mầm non Phúc Lộc',
                    'address' => 'Thôn Phúc Lộc, Xã Đông Anh, Hà Nội',
                    'phone' => '024 3883 0001',
                    'principal' => 'Cô Đỗ Thị Hậu',
                    'board' => [
                        ['role' => 'HT Mầm non Phúc Lộc:', 'name' => 'Cô Đỗ Thị Hậu'],
                        ['role' => 'Phó HT Mầm non Phúc Lộc:', 'name' => 'Cô Vương Thị Huyền'],
                        ['role' => 'Phó HT Mầm non Sao Mai:', 'name' => 'Cô Lê Thị Thúy Hà']
                    ],
                    'classes' => 30,
                    'students' => 759,
                    'ratio' => '25.3 HS/lớp (Quy mô chuẩn)',
                    'photo' => 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=800&q=80',
                    'qrCode' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/mn-phuc-loc',
                    'lat' => 21.1685,
                    'lng' => 105.8920
                ],
                'components' => [
                    [
                        'name' => 'Trường Mầm non Phúc Lộc (Cũ)',
                        'address' => 'Khu A, Thôn Phúc Lộc, Xã Đông Anh',
                        'principal' => 'Cô Nguyễn Thị Hoa',
                        'phone' => '024 3883 1111',
                        'classes' => 16,
                        'students' => 405,
                        'lat' => 21.1662,
                        'lng' => 105.8895,
                        'photo' => 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=600&q=80'
                    ],
                    [
                        'name' => 'Trường Mầm non Sao Mai',
                        'address' => 'Khu B, Thôn Phúc Lộc, Xã Đông Anh',
                        'principal' => 'Cô Trần Thị Mai',
                        'phone' => '024 3883 2222',
                        'classes' => 14,
                        'students' => 354,
                        'lat' => 21.1710,
                        'lng' => 105.8950,
                        'photo' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=600&q=80'
                    ]
                ],
                'distanceText' => '2.3 km',
                'durationText' => '6 phút'
            ],
            'mn-co-loa' => [
                'mergedSchool' => [
                    'name' => 'Trường Mầm non Cổ Loa',
                    'address' => 'Thôn Chùa, Xã Cổ Loa, Đông Anh, Hà Nội',
                    'phone' => '024 3883 0002',
                    'principal' => 'Cô Nguyễn Thị Nhàn',
                    'board' => [
                        ['role' => 'HT Mầm non Cổ Loa:', 'name' => 'Cô Nguyễn Thị Nhàn'],
                        ['role' => 'HT Mầm non Thành Loa:', 'name' => 'Cô Nguyễn Thị Thu Trang'],
                        ['role' => 'Phó HT Mầm non Thành Loa:', 'name' => 'Cô Đào Thị Kim Yến']
                    ],
                    'classes' => 36,
                    'students' => 947,
                    'ratio' => '26.3 HS/lớp',
                    'photo' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=800&q=80',
                    'qrCode' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/mn-co-loa',
                    'lat' => 21.1398,
                    'lng' => 105.8655
                ],
                'components' => [
                    [
                        'name' => 'Trường Mầm non Cổ Loa (Đơn vị 1)',
                        'address' => 'Xóm Chùa, Xã Cổ Loa, Đông Anh',
                        'principal' => 'Cô Lê Thị Nga',
                        'phone' => '024 3883 0002',
                        'classes' => 18,
                        'students' => 564,
                        'lat' => 21.1380,
                        'lng' => 105.8620,
                        'photo' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=600&q=80'
                    ],
                    [
                        'name' => 'Trường Mầm non Thành Loa',
                        'address' => 'Xóm Chợ, Xã Cổ Loa, Đông Anh',
                        'principal' => 'Cô Phạm Thị Yến',
                        'phone' => '024 3883 0022',
                        'classes' => 18,
                        'students' => 383,
                        'lat' => 21.1415,
                        'lng' => 105.8690,
                        'photo' => 'https://images.unsplash.com/photo-1576495199011-87b3f6c21dbb?auto=format&fit=crop&w=600&q=80'
                    ]
                ],
                'distanceText' => '1.8 km',
                'durationText' => '5 phút'
            ],
            'mn-mai-lam' => [
                'mergedSchool' => [
                    'name' => 'Trường Mầm non Mai Lâm',
                    'address' => 'Thôn Mai Lâm, Xã Mai Lâm, Đông Anh, Hà Nội',
                    'phone' => '024 3883 0003',
                    'principal' => 'Cô Đỗ Thị Kim',
                    'board' => [
                        ['role' => 'HT Mầm non Thái Bình:', 'name' => 'Cô Đỗ Thị Kim'],
                        ['role' => 'Phó HT Mầm non Mai Lâm:', 'name' => 'Cô Hoàng Thị Thu']
                    ],
                    'classes' => 32,
                    'students' => 866,
                    'ratio' => '27.1 HS/lớp',
                    'photo' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=80',
                    'qrCode' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/mn-mai-lam',
                    'lat' => 21.1250,
                    'lng' => 105.8900
                ],
                'components' => [
                    [
                        'name' => 'Trường Mầm non Thái Bình',
                        'address' => 'Thôn Thái Bình, Xã Mai Lâm',
                        'principal' => 'Cô Đỗ Thị Kim',
                        'phone' => '024 3883 0003',
                        'classes' => 18,
                        'students' => 521,
                        'lat' => 21.1230,
                        'lng' => 105.8870,
                        'photo' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=600&q=80'
                    ],
                    [
                        'name' => 'Trường Mầm non Mai Lâm (Cũ)',
                        'address' => 'Thôn Mai Lâm, Xã Mai Lâm',
                        'principal' => 'Cô Hoàng Thị Thu',
                        'phone' => '024 3883 0033',
                        'classes' => 14,
                        'students' => 345,
                        'lat' => 21.1270,
                        'lng' => 105.8930,
                        'photo' => 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?auto=format&fit=crop&w=600&q=80'
                    ]
                ],
                'distanceText' => '2.1 km',
                'durationText' => '5 phút'
            ],
            'mn-viet-hung' => [
                'mergedSchool' => [
                    'name' => 'Trường Mầm non Việt Hùng',
                    'address' => 'Thôn Dục Nội, Xã Việt Hùng, Đông Anh, Hà Nội',
                    'phone' => '024 3883 0004',
                    'principal' => 'Cô Ngô Thị Bích',
                    'board' => [
                        ['role' => 'HT Mầm non Dục Nội:', 'name' => 'Cô Ngô Thị Bích'],
                        ['role' => 'HT Mầm non Việt Hùng:', 'name' => 'Cô Vũ Thị Hà'],
                        ['role' => 'HT Mầm non Dục Tú:', 'name' => 'Cô Trịnh Thị Luyến']
                    ],
                    'classes' => 55,
                    'students' => 1440,
                    'ratio' => '26.1 HS/lớp',
                    'photo' => 'https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=800&q=80',
                    'qrCode' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/mn-viet-hung',
                    'lat' => 21.1550,
                    'lng' => 105.8800
                ],
                'components' => [
                    [
                        'name' => 'Trường Mầm non Dục Nội',
                        'address' => 'Thôn Dục Nội, Xã Việt Hùng',
                        'principal' => 'Cô Ngô Thị Bích',
                        'phone' => '024 3883 0004',
                        'classes' => 17,
                        'students' => 525,
                        'lat' => 21.1530,
                        'lng' => 105.8770,
                        'photo' => 'https://images.unsplash.com/photo-1588072432836-e10032774350?auto=format&fit=crop&w=600&q=80'
                    ],
                    [
                        'name' => 'Trường Mầm non Việt Hùng (Cũ)',
                        'address' => 'Thôn Lương Quán, Xã Việt Hùng',
                        'principal' => 'Cô Vũ Thị Hà',
                        'phone' => '024 3883 0044',
                        'classes' => 12,
                        'students' => 268,
                        'lat' => 21.1570,
                        'lng' => 105.8830,
                        'photo' => 'https://images.unsplash.com/photo-1544717305-2782549b5136?auto=format&fit=crop&w=600&q=80'
                    ],
                    [
                        'name' => 'Trường Mầm non Dục Tú',
                        'address' => 'Thôn Dục Tú, Xã Việt Hùng',
                        'principal' => 'Cô Trịnh Thị Luyến',
                        'phone' => '024 3883 0045',
                        'classes' => 26,
                        'students' => 647,
                        'lat' => 21.1590,
                        'lng' => 105.8850,
                        'photo' => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?auto=format&fit=crop&w=600&q=80'
                    ]
                ],
                'distanceText' => '3.4 km',
                'durationText' => '8 phút'
            ],
            'th-dong-hoi' => [
                'mergedSchool' => [
                    'name' => 'Trường Tiểu học Đông Hội',
                    'address' => 'Thôn Hội Phụ, Xã Đông Hội, Đông Anh, Hà Nội',
                    'phone' => '024 3883 0005',
                    'principal' => 'Thầy Lê Văn Hùng',
                    'board' => [
                        ['role' => 'HT Tiểu học Đông Hội:', 'name' => 'Thầy Lê Văn Hùng'],
                        ['role' => 'HT Tiểu học Xuân Canh:', 'name' => 'Cô Nguyễn Thị Lý']
                    ],
                    'classes' => 55,
                    'students' => 2426,
                    'ratio' => '44.1 HS/lớp',
                    'photo' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=800&q=80',
                    'qrCode' => 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://donganh.hanoi.gov.vn/th-dong-hoi',
                    'lat' => 21.1100,
                    'lng' => 105.8750
                ],
                'components' => [
                    [
                        'name' => 'Trường Tiểu học Đông Hội (Cũ)',
                        'address' => 'Thôn Hội Phụ, Xã Đông Hội',
                        'principal' => 'Thầy Lê Văn Hùng',
                        'phone' => '024 3883 0005',
                        'classes' => 38,
                        'students' => 1887,
                        'lat' => 21.1080,
                        'lng' => 105.8730,
                        'photo' => 'https://images.unsplash.com/photo-1523240795612-9a054b0db644?auto=format&fit=crop&w=600&q=80'
                    ],
                    [
                        'name' => 'Trường Tiểu học Xuân Canh',
                        'address' => 'Thôn Xuân Canh, Xã Xuân Canh',
                        'principal' => 'Cô Nguyễn Thị Lý',
                        'phone' => '024 3883 0055',
                        'classes' => 17,
                        'students' => 539,
                        'lat' => 21.1120,
                        'lng' => 105.8770,
                        'photo' => 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?auto=format&fit=crop&w=600&q=80'
                    ]
                ],
                'distanceText' => '2.5 km',
                'durationText' => '6 phút'
            ]
        ];

        $connections = ['mysql', 'mysql_education'];
        foreach ($connections as $conn) {
            try {
                if (!DB::connection($conn)->getSchemaBuilder()->hasTable('eateries')) {
                    continue;
                }

                foreach ($schoolsData as $slug => $data) {
                    $jsonContent = json_encode($data, JSON_UNESCAPED_UNICODE);
                    $stdName = VietnameseSeoHelper::standardizeSchoolName($data['mergedSchool']['name']);

                    DB::connection($conn)->table('eateries')
                        ->where('slug', $slug)
                        ->orWhere('name', 'LIKE', '%' . str_replace('-', ' ', $slug) . '%')
                        ->update([
                            'name' => $stdName,
                            'storytelling_data' => $jsonContent
                        ]);
                }

                // Standardize all school names in DB
                $schools = DB::connection($conn)->table('eateries')
                    ->where('name', 'LIKE', '%MN%')
                    ->orWhere('name', 'LIKE', '%TH%')
                    ->get();

                foreach ($schools as $sch) {
                    $newName = VietnameseSeoHelper::standardizeSchoolName($sch->name);
                    if ($newName !== $sch->name) {
                        DB::connection($conn)->table('eateries')
                            ->where('id', $sch->id)
                            ->update(['name' => $newName]);
                    }
                }
            } catch (\Exception $e) {
                // Connection errors ignored if database missing
            }
        }
    }

    public function down(): void
    {
        // Revert storytelling data
    }
};
