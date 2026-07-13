<?php

namespace Database\Seeders;

use App\Models\Commune;
use Illuminate\Database\Seeder;

/**
 * CommuneSeeder — Chỉ tạo dữ liệu 72 Thôn & Tổ dân phố Đông Anh theo yêu cầu
 *
 * Chạy: php artisan db:seed --class=CommuneSeeder
 */
class CommuneSeeder extends Seeder
{
    public function run(): void
    {
        $communes = [
            ['name' => 'Thôn Đông Trù', 'slug' => 'thon-dong-tru'],
            ['name' => 'Thôn Đoài', 'slug' => 'thon-doai'],
            ['name' => 'Thôn Lực Canh', 'slug' => 'thon-luc-canh'],
            ['name' => 'Thôn Xuân Trạch', 'slug' => 'thon-xuan-trach'],
            ['name' => 'Thôn Gia Lộc', 'slug' => 'thon-gia-loc'],
            ['name' => 'Thôn Mạch Tràng', 'slug' => 'thon-mach-trang'],
            ['name' => 'Thôn Trung', 'slug' => 'thon-trung'],
            ['name' => 'Thôn Lộc Hà', 'slug' => 'thon-loc-ha'],
            ['name' => 'Thôn Lý Nhân', 'slug' => 'thon-ly-nhan'],
            ['name' => 'Thôn Lại Đà', 'slug' => 'thon-lai-da'],
            ['name' => 'Thôn Đông Ngàn', 'slug' => 'thon-dong-ngan'],
            ['name' => 'Thôn Thái Bình', 'slug' => 'thon-thai-binh'],
            ['name' => 'Thôn Đông', 'slug' => 'thon-dong'],
            ['name' => 'Thôn Tiên Hội', 'slug' => 'thon-tien-hoi'],
            ['name' => 'Thôn Du Nội', 'slug' => 'thon-du-noi'],
            ['name' => 'Thôn Hội Phụ', 'slug' => 'thon-hoi-phu'],
            ['name' => 'Thôn Mai Hiên', 'slug' => 'thon-mai-hien'],
            ['name' => 'Thôn Vang', 'slug' => 'thon-vang'],
            ['name' => 'Thôn Xuân Canh', 'slug' => 'thon-xuan-canh'],
            ['name' => 'Thôn Thượng (CL)', 'slug' => 'thon-thuong-cl'],
            ['name' => 'Thôn Nhồi Dưới', 'slug' => 'thon-nhoi-duoi'],
            ['name' => 'Thôn Dục Tú 1', 'slug' => 'thon-duc-tu-1'],
            ['name' => 'Thôn Trong', 'slug' => 'thon-trong'],
            ['name' => 'Thôn Trung Thôn', 'slug' => 'thon-trung-thon'],
            ['name' => 'Thôn Đản Mỗ', 'slug' => 'thon-dan-mo'],
            ['name' => 'Thôn Văn Thượng', 'slug' => 'thon-van-thuong'],
            ['name' => 'Thôn Cầu Cả', 'slug' => 'thon-cau-ca'],
            ['name' => 'Thôn Lê Xá', 'slug' => 'thon-le-xa'],
            ['name' => 'Thôn Thượng (UN)', 'slug' => 'thon-thuong-un'],
            ['name' => 'Thôn Đồng Dầu', 'slug' => 'thon-dong-dau'],
            ['name' => 'Thôn Chợ (CL)', 'slug' => 'thon-cho-cl'],
            ['name' => 'Thôn Dục Tú 3', 'slug' => 'thon-duc-tu-3'],
            ['name' => 'Thôn Thạc Quả', 'slug' => 'thon-thac-qua'],
            ['name' => 'Thôn Du Ngoại', 'slug' => 'thon-du-ngoai'],
            ['name' => 'Thôn Phan Xá', 'slug' => 'thon-phan-xa'],
            ['name' => 'Thôn Ngoài', 'slug' => 'thon-ngoai'],
            ['name' => 'Thôn Đản Dị', 'slug' => 'thon-dan-di'],
            ['name' => 'Thôn Phúc Hậu 2', 'slug' => 'thon-phuc-hau-2'],
            ['name' => 'Thôn Vạn Lộc', 'slug' => 'thon-van-loc'],
            ['name' => 'Thôn Gà', 'slug' => 'thon-ga'],
            ['name' => 'Tổ dân phố số 6', 'slug' => 'to-dan-pho-so-6'],
            ['name' => 'Thôn Đài Bi', 'slug' => 'thon-dai-bi'],
            ['name' => 'Thôn Hậu', 'slug' => 'thon-hau'],
            ['name' => 'Thôn Mít', 'slug' => 'thon-mit'],
            ['name' => 'Thôn Phúc Hậu 1', 'slug' => 'thon-phuc-hau-1'],
            ['name' => 'Thôn Dục Tú 2', 'slug' => 'thon-duc-tu-2'],
            ['name' => 'Thôn Sằn', 'slug' => 'thon-san'],
            ['name' => 'Thôn Dõng', 'slug' => 'thon-dong-d'],
            ['name' => 'Thôn Chùa', 'slug' => 'thon-chua'],
            ['name' => 'Thôn Văn Tinh', 'slug' => 'thon-van-tinh'],
            ['name' => 'Tổ dân phố số 4', 'slug' => 'to-dan-pho-so-4'],
            ['name' => 'Thôn Nghĩa Vũ', 'slug' => 'thon-nghia-vu'],
            ['name' => 'Thôn Hương', 'slug' => 'thon-huong'],
            ['name' => 'Tổ dân phố số 35 (+ Khu vực 382, Khu vực X89)', 'slug' => 'to-dan-pho-so-35'],
            ['name' => 'Thôn Nhồi Trên', 'slug' => 'thon-nhoi-tren'],
            ['name' => 'Thôn Phúc Thọ', 'slug' => 'thon-phuc-tho'],
            ['name' => 'Thôn Phúc Lộc', 'slug' => 'thon-phuc-loc'],
            ['name' => 'Tổ dân phố số 38', 'slug' => 'to-dan-pho-so-38'],
            ['name' => 'Tổ dân phố số 39', 'slug' => 'to-dan-pho-so-39'],
            ['name' => 'Thôn Nghĩa Lại', 'slug' => 'thon-nghia-lai'],
            ['name' => 'Thôn Ấp Tó', 'slug' => 'thon-ap-to'],
            ['name' => 'Thôn Bãi (UN)', 'slug' => 'thon-bai-un'],
            ['name' => 'Thôn Chợ (UN)', 'slug' => 'thon-cho-un'],
            ['name' => 'Tổ dân phố số 3', 'slug' => 'to-dan-pho-so-3'],
            ['name' => 'Tổ dân phố số 2', 'slug' => 'to-dan-pho-so-2'],
            ['name' => 'Tổ dân phố số 1', 'slug' => 'to-dan-pho-so-1'],
            ['name' => 'Thôn Lan Trì', 'slug' => 'thon-lan-tri'],
            ['name' => 'Thôn Lương Quán', 'slug' => 'thon-luong-quan'],
            ['name' => 'Tổ dân phố số 37', 'slug' => 'to-dan-pho-so-37'],
            ['name' => 'Tổ dân phố số 36 (+ Khu vực Công trình 6)', 'slug' => 'to-dan-pho-so-36'],
            ['name' => 'Khu tập thể Địa chất', 'slug' => 'khu-tap-the-dia-chat'],
            ['name' => 'Thôn Phố Chợ', 'slug' => 'thon-pho-cho'],
        ];

        foreach ($communes as $com) {
            Commune::firstOrCreate(
                ['slug' => $com['slug']],
                $com
            );
        }
    }
}
