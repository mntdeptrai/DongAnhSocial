<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Sao lưu bảng hiện tại trước khi xử lý (nếu chưa có)
        if (!DB::getSchemaBuilder()->hasTable('communes_backup_72')) {
            DB::statement("CREATE TABLE communes_backup_72 AS SELECT * FROM communes");
        }
        if (!DB::getSchemaBuilder()->hasTable('eateries_backup_2026')) {
            DB::statement("CREATE TABLE eateries_backup_2026 AS SELECT * FROM eateries");
        }

        // 2. Định nghĩa cấu trúc 36 Thôn mới và các Thôn/TDP cũ tương ứng
        $mapping = [
            // I. 10 Thôn giữ nguyên
            ['name' => 'Thôn Đông Trù',  'slug' => 'thon-dong-tru',  'old_slugs' => ['thon-dong-tru']],
            ['name' => 'Thôn Lực Canh',  'slug' => 'thon-luc-canh',  'old_slugs' => ['thon-luc-canh']],
            ['name' => 'Thôn Xuân Trạch', 'slug' => 'thon-xuan-trach', 'old_slugs' => ['thon-xuan-trach']],
            ['name' => 'Thôn Mạch Tràng', 'slug' => 'thon-mach-trang', 'old_slugs' => ['thon-mach-trang']],
            ['name' => 'Thôn Lộc Hà',     'slug' => 'thon-loc-ha',     'old_slugs' => ['thon-loc-ha']],
            ['name' => 'Thôn Lý Nhân',   'slug' => 'thon-ly-nhan',   'old_slugs' => ['thon-ly-nhan']],
            ['name' => 'Thôn Lại Đà',    'slug' => 'thon-lai-da',    'old_slugs' => ['thon-lai-da']],
            ['name' => 'Thôn Đông Ngàn', 'slug' => 'thon-dong-ngan', 'old_slugs' => ['thon-dong-ngan']],
            ['name' => 'Thôn Mai Hiên',  'slug' => 'thon-mai-hien',  'old_slugs' => ['thon-mai-hien']],
            ['name' => 'Thôn Hội Phụ',   'slug' => 'thon-hoi-phu',   'old_slugs' => ['thon-hoi-phu']],

            // II. 5 Thôn giữ nguyên & Đổi tên
            ['name' => 'Thôn Cổ Vân',    'slug' => 'thon-co-van',    'old_slugs' => ['thon-doai']],
            ['name' => 'Thôn Dục Nội',   'slug' => 'thon-duc-noi',   'old_slugs' => ['thon-trung']],
            ['name' => 'Thôn Việt Hùng', 'slug' => 'thon-viet-hung', 'old_slugs' => ['thon-dong']],
            ['name' => 'Thôn Thượng Thư','slug' => 'thon-thuong-thu','old_slugs' => ['thon-thuong-cl']],
            ['name' => 'Thôn Thượng Oai','slug' => 'thon-thuong-oai','old_slugs' => ['thon-thuong-un']],

            // III. 21 Thôn mới từ sáp nhập
            ['name' => 'Thôn Cổ Loa',    'slug' => 'thon-co-loa',    'old_slugs' => ['thon-chua', 'thon-mit', 'thon-cho-cl']],
            ['name' => 'Thôn Đa Vang',   'slug' => 'thon-da-vang',   'old_slugs' => ['thon-vang', 'thon-pho-cho']],
            ['name' => 'Thôn Cầu Cả',    'slug' => 'thon-cau-ca',    'old_slugs' => ['thon-san', 'thon-cau-ca']],
            ['name' => 'Thôn Hồng Lạc',  'slug' => 'thon-hong-lac',  'old_slugs' => ['thon-nhoi-duoi', 'thon-nhoi-tren', 'thon-huong']],
            ['name' => 'Thôn Thục Vương','slug' => 'thon-thuc-vuong','old_slugs' => ['thon-ga', 'thon-lan-tri', 'thon-dong-d']],
            ['name' => 'Thôn Hùng Sơn',  'slug' => 'thon-hung-son',  'old_slugs' => ['thon-dai-bi', 'thon-nghia-lai', 'thon-phuc-loc']],
            ['name' => 'Thôn Oai Nỗ',    'slug' => 'thon-oai-no',    'old_slugs' => ['thon-hau', 'to-dan-pho-so-38', 'to-dan-pho-so-39', 'thon-bai-un']],
            ['name' => 'Thôn Uy Nỗ',     'slug' => 'thon-uy-no',     'old_slugs' => ['thon-ngoai', 'thon-cho-un', 'thon-trong']],
            ['name' => 'Thôn Cường Nỗ',  'slug' => 'thon-cuong-no',  'old_slugs' => ['thon-dan-di', 'thon-phan-xa']],
            ['name' => 'Thôn Đông Anh',  'slug' => 'thon-dong-anh',  'old_slugs' => ['to-dan-pho-so-3', 'to-dan-pho-so-1', 'to-dan-pho-so-2', 'to-dan-pho-so-4']],
            ['name' => 'Thôn Uy Sơn',    'slug' => 'thon-uy-son',    'old_slugs' => ['to-dan-pho-so-35', 'to-dan-pho-so-36', 'to-dan-pho-so-37', 'thon-ap-to']],
            ['name' => 'Thôn Đản Mỗ',    'slug' => 'thon-dan-mo',    'old_slugs' => ['thon-dan-mo', 'to-dan-pho-so-6']],
            ['name' => 'Thôn Gia Lương', 'slug' => 'thon-gia-luong', 'old_slugs' => ['thon-gia-loc', 'thon-luong-quan']],
            ['name' => 'Thôn Xuân Canh', 'slug' => 'thon-xuan-canh', 'old_slugs' => ['thon-xuan-canh', 'thon-van-tinh']],
            ['name' => 'Thôn Thượng Lộc','slug' => 'thon-thuong-loc','old_slugs' => ['thon-van-thuong', 'thon-van-loc']],
            ['name' => 'Thôn Thái Bình', 'slug' => 'thon-thai-binh', 'old_slugs' => ['thon-thai-binh', 'thon-phuc-tho', 'thon-le-xa']],
            ['name' => 'Thôn Mai Lâm',   'slug' => 'thon-mai-lam',   'old_slugs' => ['thon-du-noi', 'thon-du-ngoai', 'khu-tap-the-dia-chat']],
            ['name' => 'Thôn Đồng Dầu',  'slug' => 'thon-dong-dau',  'old_slugs' => ['thon-dong-dau', 'thon-nghia-vu']],
            ['name' => 'Thôn Phúc Hậu',  'slug' => 'thon-phuc-hau',  'old_slugs' => ['thon-thac-qua', 'thon-phuc-hau-1', 'thon-phuc-hau-2']],
            ['name' => 'Thôn Dục Tú',    'slug' => 'thon-duc-tu',    'old_slugs' => ['thon-duc-tu-1', 'thon-duc-tu-2', 'thon-duc-tu-3']],
            ['name' => 'Thôn Đông Hội',  'slug' => 'thon-dong-hoi',  'old_slugs' => ['thon-tien-hoi', 'thon-trung-thon']],
        ];

        // 3. Tiến hành xử lý từng Thôn mới
        $keptCommuneIds = [];

        foreach ($mapping as $group) {
            $newName = $group['name'];
            $newSlug = $group['slug'];
            $searchSlugs = array_unique(array_merge($group['old_slugs'], [$newSlug]));

            // Tìm tất cả record trùng old_slugs hoặc newSlug
            $oldRecords = DB::table('communes')->whereIn('slug', $searchSlugs)->get();
            if ($oldRecords->isEmpty()) {
                // Nếu chưa có trong DB, tạo mới
                $primaryId = DB::table('communes')->insertGetId([
                    'name' => $newName,
                    'slug' => $newSlug,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $keptCommuneIds[] = $primaryId;
                continue;
            }

            // Ưu tiên chọn record có slug = $newSlug nếu có, nếu không lấy record đầu tiên
            $primaryRecord = $oldRecords->firstWhere('slug', $newSlug) ?? $oldRecords->first();
            $primaryId = $primaryRecord->id;
            $keptCommuneIds[] = $primaryId;

            // Lấy toàn bộ ID cũ cần gộp
            $allOldIds = $oldRecords->pluck('id')->toArray();
            $redundantOldIds = array_diff($allOldIds, [$primaryId]);

            // Cập nhật tất cả eateries từ các ID cũ về ID đại diện chính NÀY
            DB::table('eateries')
                ->whereIn('commune_id', $allOldIds)
                ->update(['commune_id' => $primaryId]);

            // Xóa các thôn dôi dư TRƯỚC KHI update slug để tránh trùng UNIQUE slug constraint
            if (!empty($redundantOldIds)) {
                DB::table('communes')->whereIn('id', $redundantOldIds)->delete();
            }

            // Cập nhật tên và slug cho bản ghi đại diện chính
            DB::table('communes')
                ->where('id', $primaryId)
                ->update([
                    'name' => $newName,
                    'slug' => $newSlug,
                    'updated_at' => now(),
                ]);
        }

        // 4. Xóa tất cả các bản ghi thôn dôi dư không nằm trong danh sách 36 ID đại diện
        DB::table('communes')
            ->whereNotIn('id', $keptCommuneIds)
            ->delete();
    }

    public function down(): void
    {
        // Khôi phục lại dữ liệu cũ từ bảng backup nếu rollback
        if (DB::getSchemaBuilder()->hasTable('communes_backup_72')) {
            DB::statement("DELETE FROM communes");
            DB::statement("INSERT INTO communes SELECT * FROM communes_backup_72");
            DB::statement("DROP TABLE communes_backup_72");
        }

        if (DB::getSchemaBuilder()->hasTable('eateries_backup_2026')) {
            DB::statement("DELETE FROM eateries");
            DB::statement("INSERT INTO eateries SELECT * FROM eateries_backup_2026");
            DB::statement("DROP TABLE eateries_backup_2026");
        }
    }
};
