<?php

namespace Database\Seeders;

use App\Models\Commune;
use Illuminate\Database\Seeder;

/**
 * CommuneSeeder — Tạo dữ liệu 36 Thôn mới đã được gộp sáp nhập của huyện Đông Anh
 *
 * Chạy: php artisan db:seed --class=CommuneSeeder
 */
class CommuneSeeder extends Seeder
{
    public function run(): void
    {
        $communes = [
            // I. 10 Thôn giữ nguyên
            ['id' => 1,  'name' => 'Thôn Đông Trù',  'slug' => 'thon-dong-tru'],
            ['id' => 3,  'name' => 'Thôn Lực Canh',  'slug' => 'thon-luc-canh'],
            ['id' => 4,  'name' => 'Thôn Xuân Trạch', 'slug' => 'thon-xuan-trach'],
            ['id' => 6,  'name' => 'Thôn Mạch Tràng', 'slug' => 'thon-mach-trang'],
            ['id' => 8,  'name' => 'Thôn Lộc Hà',     'slug' => 'thon-loc-ha'],
            ['id' => 9,  'name' => 'Thôn Lý Nhân',   'slug' => 'thon-ly-nhan'],
            ['id' => 10, 'name' => 'Thôn Lại Đà',    'slug' => 'thon-lai-da'],
            ['id' => 11, 'name' => 'Thôn Đông Ngàn', 'slug' => 'thon-dong-ngan'],
            ['id' => 17, 'name' => 'Thôn Mai Hiên',  'slug' => 'thon-mai-hien'],
            ['id' => 16, 'name' => 'Thôn Hội Phụ',   'slug' => 'thon-hoi-phu'],

            // II. 5 Thôn giữ nguyên & Đổi tên
            ['id' => 2,  'name' => 'Thôn Cổ Vân',    'slug' => 'thon-co-van'],
            ['id' => 7,  'name' => 'Thôn Dục Nội',   'slug' => 'thon-duc-noi'],
            ['id' => 13, 'name' => 'Thôn Việt Hùng', 'slug' => 'thon-viet-hung'],
            ['id' => 20, 'name' => 'Thôn Thượng Thư','slug' => 'thon-thuong-thu'],
            ['id' => 29, 'name' => 'Thôn Thượng Oai','slug' => 'thon-thuong-oai'],

            // III. 21 Thôn mới từ sáp nhập
            ['id' => 18, 'name' => 'Thôn Cổ Loa',    'slug' => 'thon-co-loa'],
            ['id' => 15, 'name' => 'Thôn Đa Vang',   'slug' => 'thon-da-vang'],
            ['id' => 27, 'name' => 'Thôn Cầu Cả',    'slug' => 'thon-cau-ca'],
            ['id' => 21, 'name' => 'Thôn Hồng Lạc',  'slug' => 'thon-hong-lac'],
            ['id' => 30, 'name' => 'Thôn Thục Vương','slug' => 'thon-thuc-vuong'],
            ['id' => 31, 'name' => 'Thôn Hùng Sơn',  'slug' => 'thon-hung-son'],
            ['id' => 32, 'name' => 'Thôn Oai Nỗ',    'slug' => 'thon-oai-no'],
            ['id' => 33, 'name' => 'Thôn Uy Nỗ',     'slug' => 'thon-uy-no'],
            ['id' => 34, 'name' => 'Thôn Cường Nỗ',  'slug' => 'thon-cuong-no'],
            ['id' => 35, 'name' => 'Thôn Đông Anh',  'slug' => 'thon-dong-anh'],
            ['id' => 36, 'name' => 'Thôn Uy Sơn',    'slug' => 'thon-uy-son'],
            ['id' => 25, 'name' => 'Thôn Đản Mỗ',    'slug' => 'thon-dan-mo'],
            ['id' => 5,  'name' => 'Thôn Gia Lương', 'slug' => 'thon-gia-luong'],
            ['id' => 28, 'name' => 'Thôn Xuân Canh', 'slug' => 'thon-xuan-canh'],
            ['id' => 26, 'name' => 'Thôn Thượng Lộc','slug' => 'thon-thuong-loc'],
            ['id' => 12, 'name' => 'Thôn Thái Bình', 'slug' => 'thon-thai-binh'],
            ['id' => 14, 'name' => 'Thôn Mai Lâm',   'slug' => 'thon-mai-lam'],
            ['id' => 22, 'name' => 'Thôn Đồng Dầu',  'slug' => 'thon-dong-dau'],
            ['id' => 24, 'name' => 'Thôn Phúc Hậu',  'slug' => 'thon-phuc-hau'],
            ['id' => 19, 'name' => 'Thôn Dục Tú',    'slug' => 'thon-duc-tu'],
            ['id' => 23, 'name' => 'Thôn Đông Hội',  'slug' => 'thon-dong-hoi'],
        ];

        foreach ($communes as $com) {
            Commune::updateOrCreate(
                ['id' => $com['id']],
                $com
            );
        }
    }
}
