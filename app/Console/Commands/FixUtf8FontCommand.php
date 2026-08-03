<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FixUtf8FontCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:utf8';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tự động sửa lỗi font tiếng Việt (ký tự ?) trong toàn bộ cơ sở dữ liệu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Đang tiến hành sửa lỗi font chữ tiếng Việt trong Cơ sở dữ liệu...');

        // 1. Sửa Bảng Categories
        $this->fixCategories();

        // 2. Sửa Bảng Communes
        $this->fixCommunes();

        // 3. Sửa Bảng Eateries (Địa điểm, Trường học, Chợ)
        $this->fixEateries();

        // 4. Sửa Bảng Stalls (Gian hàng chợ)
        $this->fixStalls();

        // 5. Sửa Bảng Education Programs (Bài viết)
        $this->fixEducationPrograms();

        $this->info('✨ Đã sửa xong toàn bộ lỗi font tiếng Việt!');
        return Command::SUCCESS;
    }

    private function fixCategories()
    {
        $map = [
            1 => ['name' => 'Ăn uống', 'description' => 'Nhà hàng, quán ăn, trà sữa, cafe và điểm hẹn ẩm thực ngon tại Đông Anh'],
            2 => ['name' => 'Vui chơi', 'description' => 'Khu vui chơi, giải trí, trung tâm thương mại và điểm tham quan hấp dẫn'],
            3 => ['name' => 'Wellness & Care', 'description' => 'Hệ thống cơ sở y tế, phòng khám, chăm sóc sức khỏe và spa thư giãn hàng đầu Đông Anh'],
            4 => ['name' => 'Đặc sản OCOP', 'description' => 'Nơi hội tụ các sản phẩm OCOP, quà lưu niệm độc đáo, đặc sản địa phương mang đậm hồn quê Đông Anh'],
            5 => ['name' => 'Smart Education Map', 'description' => 'Hệ thống trường học và cơ sở giáo dục chất lượng cao trên địa bàn xã Đông Anh'],
            6 => ['name' => 'Hành trình di sản', 'description' => 'Kết nối hành trình khám phá di tích lịch sử và văn hóa thông qua nền tảng Donganh360.vn'],
            7 => ['name' => 'Khám phá Văn hóa & Thể thao', 'description' => 'Khám phá hệ thống thiết chế văn hóa - thể thao Đông Anh: Nhà văn hóa, nhà thi đấu, trung tâm triển lãm'],
            8 => ['name' => 'Chợ truyền thống', 'description' => 'Khám phá các chợ truyền thống nhộn nhịp mang đậm hồn quê Đông Anh'],
        ];

        foreach ($map as $id => $data) {
            DB::table('categories')->where('id', $id)->update($data);
        }
        $this->info('✔ Đã sửa xong bảng Categories');
    }

    private function fixCommunes()
    {
        $communes = DB::table('communes')->get();
        foreach ($communes as $c) {
            $name = $c->name;
            if (str_contains($name, '?')) {
                $cleanName = match ($c->slug ?? '') {
                    'thon-dong-tru' => 'Thôn Đông Trù',
                    'thon-luc-canh' => 'Thôn Lộc Canh',
                    'thon-xuan-trach' => 'Thôn Xuân Trạch',
                    'thon-mach-trang' => 'Thôn Mạch Tràng',
                    'thon-luc-ho' => 'Thôn Lộc Hà',
                    'thon-hung-son' => 'Thôn Hùng Sơn',
                    'xuan-canh' => 'Xã Xuân Canh',
                    'co-loa' => 'Xã Cổ Loa',
                    'mai-lam' => 'Xã Mai Lâm',
                    'dong-anh' => 'Xã Đông Anh',
                    default => Str::title(str_replace('-', ' ', $c->slug ?? ''))
                };

                DB::table('communes')->where('id', $c->id)->update(['name' => $cleanName]);
            }
        }
        $this->info('✔ Đã sửa xong bảng Communes');
    }

    private function fixEateries()
    {
        $eateries = DB::table('eateries')->get();

        $eateryNameMap = [
            'mn-phuc-loc' => 'Trường Mầm non Phúc Lộc',
            'mam-non-phuc-loc' => 'Trường Mầm non Phúc Lộc',
            'lau-ech-huyen-anh-LLH5m' => 'Lẩu Ếch Huyền Anh',
            'quan-nuong-rang-tre-YtaI3' => 'Quán Nướng Rặng Tre',
            'nha-hang-huong-bien-pZS6m' => 'Nhà Hàng Hương Biển',
            'che-ngoc-thach-nxzLF' => 'Chè Ngọc Thạch',
            'trung-nguyen-e-coffee-dong-hoi-XST1K' => 'Trung Nguyên E-Coffee Đông Hội',
            'nha-van-hoa-xa-dong-anh-jGFlE' => 'Nhà văn hóa xã Đông Anh',
            'diep-linh-plaza-w9XP7' => 'Diệp Linh Plaza',
            'truong-quay-co-loa-uRtgp' => 'Trường Quay Cổ Loa',
            'cong-vien-xu-so-than-tien-vinwonders-global-gate-co-loa-dong-anh-ha-noi-OQovb' => 'Công Viên Xứ Sở Thần Tiên VinWonders',
            'tram-y-te-xuan-canh-1NRU9' => 'Trạm y tế Xuân Canh',
            'benh-vien-da-khoa-dong-anh-5fwWa' => 'Bệnh viện Đa khoa Đông Anh',
            'trung-tam-tiem-chung-vnvc-dong-anh-O8AdL' => 'Trung tâm tiêm chủng VNVC Đông Anh',
            'cho-to-pa3MD' => 'Chợ Tở',
            'cho-trung-tam-dong-anh-P2ft0' => 'Chợ Trung Tâm Đông Anh',
            'cho-sa-co-loa-mf0hh' => 'Chợ Sa (Cổ Loa)',
            'cho-mai-lam-Fg3D9' => 'Chợ Mai Lâm',
            'cho-duc-noi-pIEle' => 'Chợ Dục Nội',
            'cho-duc-tu-3-TD0C7' => 'Chợ Dục Tú 3',
            'cho-van-hoa-du-lich-co-loa-3Tkmb' => 'Chợ văn hoá Du lịch Cổ Loa',
            'cho-du-noi-kItF3' => 'Chợ Du Nội',
            'cho-mai-hien-etlwa' => 'Chợ Mai Hiên',
            'cho-luc-canh-bU73w' => 'Chợ Lộc Canh',
            'cho-xuan-canh-d8ZrN' => 'Chợ Xuân Canh',
            'cho-nhoi-duoi-rQBih' => 'Chợ Nhội Dưới (Chợ Hùng Lộc)',
            'cho-ly-nhan-tgo5z' => 'Chợ Lý Nhân',
            'cho-day-da-tifcw' => 'Chợ Dây Da',
            'cho-dong-tru-YT5K4' => 'Chợ Đông Trù',
            'cho-mach-trang-oefHf' => 'Chợ Mạch Tràng',
            'sieu-thi-lan-chi-dong-anh-t0bez' => 'Siêu Thị Lan Chi Đông Anh',
            'htx-nong-nghiep-duoc-lieu-cong-nghe-cao-kovi-QBWB4' => 'HTX nông nghiệp dược liệu công nghệ cao KOVI',
            'hkd-tran-van-tan-v2rPM' => 'Hộ kinh doanh Trần Văn Tấn',
            'cong-ty-tnhh-hoang-chien-thang-IV8Bf' => 'Công ty TNHH Hoàng Chiến Thắng',
            'htx-dich-vu-nong-nghiep-thon-doai-jDNEK' => 'HTX dịch vụ nông nghiệp thôn Đoài',
        ];

        foreach ($eateries as $e) {
            $name = $e->name;
            $address = $e->address;
            $slug = $e->slug;

            $updateData = [];

            if (isset($eateryNameMap[$slug])) {
                $updateData['name'] = $eateryNameMap[$slug];
            } else if (str_contains($name, '?')) {
                $fixedName = $this->replaceQuestionMarkWords($name);
                $updateData['name'] = $fixedName;
            }

            if ($address && str_contains($address, '?')) {
                $updateData['address'] = str_replace(
                    ['??ng Anh', 'H? N?i', 'Vi?t Nam', 'T? ', 'Th?n ', 'x? '],
                    ['Đông Anh', 'Hà Nội', 'Việt Nam', 'Tổ ', 'Thôn ', 'xã '],
                    $address
                );
                $updateData['address'] = $this->replaceQuestionMarkWords($updateData['address']);
            }

            if (!empty($updateData)) {
                DB::table('eateries')->where('id', $e->id)->update($updateData);
            }
        }
        $this->info('✔ Đã sửa xong bảng Eateries');
    }

    private function fixStalls()
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('stalls')) {
            $this->warn('⚠ Bảng Stalls chưa tồn tại trong DB, bỏ qua.');
            return;
        }

        $stalls = DB::table('stalls')->get();

        foreach ($stalls as $st) {
            $name = $st->name ?? '';
            $ownerName = $st->owner_name ?? '';
            $desc = $st->description ?? '';

            $updateData = [];

            if (str_contains($name, '?')) {
                $updateData['name'] = $this->replaceQuestionMarkWords($name);
            }
            if ($ownerName && str_contains($ownerName, '?')) {
                $updateData['owner_name'] = $this->replaceQuestionMarkWords($ownerName);
            }
            if ($desc && str_contains($desc, '?')) {
                $updateData['description'] = $this->replaceQuestionMarkWords($desc);
            }

            if (!empty($updateData)) {
                DB::table('stalls')->where('id', $st->id)->update($updateData);
            }
        }
        $this->info('✔ Đã sửa xong bảng Stalls');
    }

    private function fixEducationPrograms()
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('education_programs')) {
            $this->warn('⚠ Bảng Education Programs chưa tồn tại trong DB, bỏ qua.');
            return;
        }

        $programs = DB::table('education_programs')->get();
        foreach ($programs as $prog) {
            $name = $prog->name ?? '';
            $desc = $prog->description ?? '';

            $updateData = [];
            if ($name && str_contains($name, '?')) {
                $updateData['name'] = $this->replaceQuestionMarkWords($name);
            }
            if ($desc && str_contains($desc, '?')) {
                $updateData['description'] = $this->replaceQuestionMarkWords($desc);
            }

            if (!empty($updateData)) {
                DB::table('education_programs')->where('id', $prog->id)->update($updateData);
            }
        }
        $this->info('✔ Đã sửa xong bảng Education Programs');
    }

    private function replaceQuestionMarkWords($text)
    {
        if (!$text) return $text;

        $replacements = [
            'Gian h?ng' => 'Gian hàng',
            'B?n t??i' => 'Bún tươi',
            'Rau ??u t??i' => 'Rau tươi',
            'Th?t l?n t??i' => 'Thịt lợn tươi',
            'Th?t g?' => 'Thịt gà',
            'Th?t b?' => 'Thịt bò',
            'Ch? ' => 'Chợ ',
            'Nguy?n' => 'Nguyễn',
            'Th?' => 'Thị',
            'H?n' => 'Hân',
            '??o' => 'Đào',
            'Ho?i' => 'Hoài',
            'Tr??ng' => 'Trường',
            'M?m non' => 'Mầm non',
            'Ph?c L?c' => 'Phúc Lộc',
            'Th?n H?ng S?n' => 'Thôn Hùng Sơn',
            'Th?n ??ng Tr?' => 'Thôn Đông Trù',
            'Th?n L?c Canh' => 'Thôn Lộc Canh',
            'Th?n Xu?n Tr?ch' => 'Thôn Xuân Trạch',
            'Th?n M?ch Tr?ng' => 'Thôn Mạch Tràng',
            '??ng Anh' => 'Đông Anh',
            'H? N?i' => 'Hà Nội',
            'Vi?t Nam' => 'Việt Nam',
            'L?u ?ch' => 'Lẩu Ếch',
            'Qu?n N??ng' => 'Quán Nướng',
            'Nh? H?ng' => 'Nhà Hàng',
            'Bi?n' => 'Biển',
            'H??ng' => 'Hương',
            'Ng?c Th?ch' => 'Ngọc Thạch',
            'B?nh vi?n ?a khoa' => 'Bệnh viện Đa khoa',
            'Tr?m y t?' => 'Trạm y tế',
            'Xu?n Canh' => 'Xuân Canh',
            'Di?p Linh' => 'Diệp Linh',
            'C? Loa' => 'Cổ Loa',
            'Mai L?m' => 'Mai Lâm',
            'Mai Hi?n' => 'Mai Hiên',
            'D?c N?i' => 'Dục Nội',
            'Nh?i D??i' => 'Nhội Dưới',
            'L? Nh?n' => 'Lý Nhân',
            '??ng Tr?' => 'Đông Trù',
            'M?ch Tr?ng' => 'Mạch Tràng',
            'Lan Chi' => 'Lan Chi',
            'n?ng nghi?p' => 'nông nghiệp',
            'c?ng ngh? cao' => 'công nghệ cao',
            'H? kinh doanh' => 'Hộ kinh doanh',
            'Tr?n V?n T?n' => 'Trần Văn Tấn',
            'Ho?ng Chi?n Th?ng' => 'Hoàng Chiến Thắng',
            'th?n ?o?i' => 'thôn Đoài',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $text);
    }
}
