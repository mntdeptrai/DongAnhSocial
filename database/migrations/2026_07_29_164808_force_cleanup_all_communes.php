<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];

        $mapping = [
            // I. 10 Thôn giữ nguyên
            ['id' => 1,  'name' => 'Thôn Đông Trù',  'slug' => 'thon-dong-tru',  'old_slugs' => ['thon-dong-tru']],
            ['id' => 3,  'name' => 'Thôn Lực Canh',  'slug' => 'thon-luc-canh',  'old_slugs' => ['thon-luc-canh']],
            ['id' => 4,  'name' => 'Thôn Xuân Trạch', 'slug' => 'thon-xuan-trach', 'old_slugs' => ['thon-xuan-trach']],
            ['id' => 6,  'name' => 'Thôn Mạch Tràng', 'slug' => 'thon-mach-trang', 'old_slugs' => ['thon-mach-trang']],
            ['id' => 8,  'name' => 'Thôn Lộc Hà',     'slug' => 'thon-loc-ha',     'old_slugs' => ['thon-loc-ha']],
            ['id' => 9,  'name' => 'Thôn Lý Nhân',   'slug' => 'thon-ly-nhan',   'old_slugs' => ['thon-ly-nhan']],
            ['id' => 10, 'name' => 'Thôn Lại Đà',    'slug' => 'thon-lai-da',    'old_slugs' => ['thon-lai-da']],
            ['id' => 11, 'name' => 'Thôn Đông Ngàn', 'slug' => 'thon-dong-ngan', 'old_slugs' => ['thon-dong-ngan']],
            ['id' => 17, 'name' => 'Thôn Mai Hiên',  'slug' => 'thon-mai-hien',  'old_slugs' => ['thon-mai-hien']],
            ['id' => 16, 'name' => 'Thôn Hội Phụ',   'slug' => 'thon-hoi-phu',   'old_slugs' => ['thon-hoi-phu']],

            // II. 5 Thôn giữ nguyên & Đổi tên
            ['id' => 2,  'name' => 'Thôn Cổ Vân',    'slug' => 'thon-co-van',    'old_slugs' => ['thon-doai']],
            ['id' => 7,  'name' => 'Thôn Dục Nội',   'slug' => 'thon-duc-noi',   'old_slugs' => ['thon-trung']],
            ['id' => 13, 'name' => 'Thôn Việt Hùng', 'slug' => 'thon-viet-hung', 'old_slugs' => ['thon-dong']],
            ['id' => 20, 'name' => 'Thôn Thượng Thư','slug' => 'thon-thuong-thu','old_slugs' => ['thon-thuong-cl']],
            ['id' => 29, 'name' => 'Thôn Thượng Oai','slug' => 'thon-thuong-oai','old_slugs' => ['thon-thuong-un']],

            // III. 21 Thôn mới từ sáp nhập
            ['id' => 18, 'name' => 'Thôn Cổ Loa',    'slug' => 'thon-co-loa',    'old_slugs' => ['thon-chua', 'thon-mit', 'thon-cho-cl']],
            ['id' => 15, 'name' => 'Thôn Đa Vang',   'slug' => 'thon-da-vang',   'old_slugs' => ['thon-vang', 'thon-pho-cho']],
            ['id' => 27, 'name' => 'Thôn Cầu Cả',    'slug' => 'thon-cau-ca',    'old_slugs' => ['thon-san', 'thon-cau-ca']],
            ['id' => 21, 'name' => 'Thôn Hồng Lạc',  'slug' => 'thon-hong-lac',  'old_slugs' => ['thon-nhoi-duoi', 'thon-nhoi-tren', 'thon-huong']],
            ['id' => 30, 'name' => 'Thôn Thục Vương','slug' => 'thon-thuc-vuong','old_slugs' => ['thon-ga', 'thon-lan-tri', 'thon-dong-d']],
            ['id' => 31, 'name' => 'Thôn Hùng Sơn',  'slug' => 'thon-hung-son',  'old_slugs' => ['thon-dai-bi', 'thon-nghia-lai', 'thon-phuc-loc']],
            ['id' => 32, 'name' => 'Thôn Oai Nỗ',    'slug' => 'thon-oai-no',    'old_slugs' => ['thon-hau', 'to-dan-pho-so-38', 'to-dan-pho-so-39', 'thon-bai-un']],
            ['id' => 33, 'name' => 'Thôn Uy Nỗ',     'slug' => 'thon-uy-no',     'old_slugs' => ['thon-ngoai', 'thon-cho-un', 'thon-trong']],
            ['id' => 34, 'name' => 'Thôn Cường Nỗ',  'slug' => 'thon-cuong-no',  'old_slugs' => ['thon-dan-di', 'thon-phan-xa']],
            ['id' => 35, 'name' => 'Thôn Đông Anh',  'slug' => 'thon-dong-anh',  'old_slugs' => ['to-dan-pho-so-3', 'to-dan-pho-so-1', 'to-dan-pho-so-2', 'to-dan-pho-so-4']],
            ['id' => 36, 'name' => 'Thôn Uy Sơn',    'slug' => 'thon-uy-son',    'old_slugs' => ['to-dan-pho-so-35', 'to-dan-pho-so-36', 'to-dan-pho-so-37', 'thon-ap-to']],
            ['id' => 25, 'name' => 'Thôn Đản Mỗ',    'slug' => 'thon-dan-mo',    'old_slugs' => ['thon-dan-mo', 'to-dan-pho-so-6']],
            ['id' => 5,  'name' => 'Thôn Gia Lương', 'slug' => 'thon-gia-luong', 'old_slugs' => ['thon-gia-loc', 'thon-luong-quan']],
            ['id' => 28, 'name' => 'Thôn Xuân Canh', 'slug' => 'thon-xuan-canh', 'old_slugs' => ['thon-xuan-canh', 'thon-van-tinh']],
            ['id' => 26, 'name' => 'Thôn Thượng Lộc','slug' => 'thon-thuong-loc','old_slugs' => ['thon-van-thuong', 'thon-van-loc']],
            ['id' => 12, 'name' => 'Thôn Thái Bình', 'slug' => 'thon-thai-binh', 'old_slugs' => ['thon-thai-binh', 'thon-phuc-tho', 'thon-le-xa']],
            ['id' => 14, 'name' => 'Thôn Mai Lâm',   'slug' => 'thon-mai-lam',   'old_slugs' => ['thon-du-noi', 'thon-du-ngoai', 'khu-tap-the-dia-chat']],
            ['id' => 22, 'name' => 'Thôn Đồng Dầu',  'slug' => 'thon-dong-dau',  'old_slugs' => ['thon-dong-dau', 'thon-nghia-vu']],
            ['id' => 24, 'name' => 'Thôn Phúc Hậu',  'slug' => 'thon-phuc-hau',  'old_slugs' => ['thon-thac-qua', 'thon-phuc-hau-1', 'thon-phuc-hau-2']],
            ['id' => 19, 'name' => 'Thôn Dục Tú',    'slug' => 'thon-duc-tu',    'old_slugs' => ['thon-duc-tu-1', 'thon-duc-tu-2', 'thon-duc-tu-3']],
            ['id' => 14, 'name' => 'Thôn Đông Hội',  'slug' => 'thon-dong-hoi',  'old_slugs' => ['thon-tien-hoi', 'thon-trung-thon']],
        ];

        foreach ($connections as $conn) {
            try {
                $db = DB::connection($conn);
                $db->getPdo();
            } catch (\Throwable $e) {
                continue;
            }

            $db->transaction(function() use ($db, $mapping) {
                $keptIds = [];

                // 1. Cập nhật eateries từ ID cũ sang ID mới
                foreach ($mapping as $group) {
                    $newSlug = $group['slug'];
                    $targetId = $group['id'];
                    $searchSlugs = array_unique(array_merge($group['old_slugs'], [$newSlug]));

                    // Lấy tất cả records cũ trùng slugs
                    $oldRecords = $db->table('communes')->whereIn('slug', $searchSlugs)->get();
                    if ($oldRecords->isEmpty()) {
                        continue;
                    }

                    $allOldIds = $oldRecords->pluck('id')->toArray();

                    // Cập nhật eateries
                    $db->table('eateries')
                        ->whereIn('commune_id', $allOldIds)
                        ->update(['commune_id' => $targetId]);

                    $keptIds[] = $targetId;
                }

                // 2. Xóa các communes dôi dư (không nằm trong danh sách 36 thôn mới)
                $db->table('communes')
                    ->whereNotIn('id', $keptIds)
                    ->delete();

                // 3. Cập nhật lại hoặc chèn mới 36 thôn chuẩn
                foreach ($mapping as $group) {
                    $targetId = $group['id'];
                    $name = $group['name'];
                    $slug = $group['slug'];

                    $exists = $db->table('communes')->where('id', $targetId)->exists();
                    if ($exists) {
                        $db->table('communes')->where('id', $targetId)->update([
                            'name' => $name,
                            'slug' => $slug,
                            'updated_at' => now()
                        ]);
                    } else {
                        $db->table('communes')->insert([
                            'id' => $targetId,
                            'name' => $name,
                            'slug' => $slug,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // No rollback required for force cleanup
    }
};
