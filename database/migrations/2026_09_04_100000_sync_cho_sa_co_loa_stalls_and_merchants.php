<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $marketId = 19; // Chợ Sa (Cổ Loa)

        // 1. Cập nhật thông tin Chợ Sa (Cổ Loa) trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Sa (Cổ Loa)',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Thôn Chợ, Xã Cổ Loa, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Sa Cổ Loa
        $bql = User::where('email', 'bql.chosa@foodmap.vn')->orWhere('email', 'bql.choSaCoLoa@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Sa Cổ Loa',
                'email' => 'bql.chosa@foodmap.vn',
                'phone' => '0123654019',
                'password' => Hash::make('123456@'),
                'role' => 'seller',
                'status' => 'active',
                'eatery_id' => $marketId,
                'avatar' => '🏛️',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $bql->update([
                'name' => 'Ban Quản lý Chợ Sa Cổ Loa',
                'email' => 'bql.chosa@foodmap.vn',
                'phone' => '0123654019',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Dọn dẹp tài khoản cũ/trùng lặp thuộc Chợ Sa
        $sellerEmailsToKeep = ['bql.chosa@foodmap.vn'];
        for ($i = 1; $i <= 22; $i++) {
            $sellerEmailsToKeep[] = "seller.saCoLoa.{$i}@foodmap.vn";
        }
        User::where('eatery_id', $marketId)
            ->where(function($q) use ($sellerEmailsToKeep) {
                $q->whereNotIn('email', $sellerEmailsToKeep)->orWhereNull('email')->orWhere('email', '');
            })
            ->delete();

        // 4. Danh sách 22 hộ kinh doanh chuẩn từ file Excel TH DS HỘ KINH DOANH TẠI CHỢ DÂN SINH CHỢ SA-CỔ LOA.xlsx
        $merchants = [
            1 => [
                'stt' => 1,
                'name' => 'NGUYỄN THỊ LIÊN',
                'stall_name' => 'Gian hàng Thịt bò NGUYỄN THỊ LIÊN (Hộ 1)',
                'item_name' => 'Thịt bò',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Nhập lại',
                'phone' => '0373492433',
                'bank_name' => 'VietinBank',
                'bank_account' => '101881043869',
                'qr_bin' => '970415',
            ],
            2 => [
                'stt' => 2,
                'name' => 'NGUYỄN THỊ DIÊN',
                'stall_name' => 'Gian hàng Thịt gia cầm NGUYỄN THỊ DIÊN (Hộ 2)',
                'item_name' => 'Thịt gia cầm',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Tự chăn nuôi',
                'phone' => '0983587978',
                'bank_name' => 'VietinBank',
                'bank_account' => '10983797440',
                'qr_bin' => '970415',
            ],
            3 => [
                'stt' => 3,
                'name' => 'NGUYỄN THỊ ĐIỆP',
                'stall_name' => 'Gian hàng Tai, mũi lợn NGUYỄN THỊ ĐIỆP (Hộ 3)',
                'item_name' => 'Tai, mũi lợn',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Cơ sở Độ Phượng - xóm Nhồi Dưới Cổ Loa',
                'phone' => '0973259722',
                'bank_name' => 'VietinBank',
                'bank_account' => '107881857131',
                'qr_bin' => '970415',
            ],
            4 => [
                'stt' => 4,
                'name' => 'NGUYỄN THỊ TƯ',
                'stall_name' => 'Gian hàng Thịt lợn NGUYỄN THỊ TƯ (Hộ 4)',
                'item_name' => 'Thịt lợn',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Lò mổ',
                'phone' => '0349194588',
                'bank_name' => 'Agribank',
                'bank_account' => '3140205549',
                'qr_bin' => '970405',
            ],
            5 => [
                'stt' => 5,
                'name' => 'NGUYỄN THỊ THU THANH',
                'stall_name' => 'Gian hàng Giò chả NGUYỄN THỊ THU THANH (Hộ 5)',
                'item_name' => 'Giò chả',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Cơ sở chế biến Nghị Được',
                'phone' => '0917171887',
                'bank_name' => 'TPBank',
                'bank_account' => '25663686868',
                'qr_bin' => '970423',
            ],
            6 => [
                'stt' => 6,
                'name' => 'PHẠM THỊ THỊNH',
                'stall_name' => 'Gian hàng Giò chả PHẠM THỊ THỊNH (Hộ 6)',
                'item_name' => 'Giò chả',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Nhập lại',
                'phone' => '034087959',
                'bank_name' => 'Agribank',
                'bank_account' => '3140205197',
                'qr_bin' => '970405',
            ],
            7 => [
                'stt' => 7,
                'name' => 'NGUYỄN THỊ DUNG',
                'stall_name' => 'Gian hàng Thịt lợn NGUYỄN THỊ DUNG (Hộ 7)',
                'item_name' => 'Thịt lợn',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Chợ Yên Thường',
                'phone' => '0989198085',
                'bank_name' => 'BIDV',
                'bank_account' => '882449007',
                'qr_bin' => '970418',
            ],
            8 => [
                'stt' => 8,
                'name' => 'NGUYỄN THỊ THỜI',
                'stall_name' => 'Gian hàng Thịt lợn NGUYỄN THỊ THỜI (Hộ 8)',
                'item_name' => 'Thịt lợn',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Cơ sở CM Minh Dục Tú',
                'phone' => '097603568',
                'bank_name' => null,
                'bank_account' => null,
                'qr_bin' => null,
            ],
            9 => [
                'stt' => 9,
                'name' => 'NGUYỄN THỊ LIÊN',
                'stall_name' => 'Gian hàng Hàng khô NGUYỄN THỊ LIÊN (Hộ 9)',
                'item_name' => 'Hàng khô',
                'price' => 0,
                'unit' => null,
                'origin' => 'Các đại lý nhỏ lẻ',
                'phone' => '096681972',
                'bank_name' => 'VietinBank',
                'bank_account' => '101881617150',
                'qr_bin' => '970415',
            ],
            10 => [
                'stt' => 10,
                'name' => 'PHẠM THỊ THANH LÝ',
                'stall_name' => 'Gian hàng Trái cây PHẠM THỊ THANH LÝ (Hộ 10)',
                'item_name' => 'Trái cây',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Chợ Núi',
                'phone' => '0338986173',
                'bank_name' => 'MBBank',
                'bank_account' => '999212349999',
                'qr_bin' => '970422',
            ],
            11 => [
                'stt' => 11,
                'name' => 'NGUYỄN THỊ HÈ',
                'stall_name' => 'Gian hàng Trái cây NGUYỄN THỊ HÈ (Hộ 11)',
                'item_name' => 'Trái cây',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Chợ đầu mối',
                'phone' => '0978430476',
                'bank_name' => 'Agribank',
                'bank_account' => '3140205141',
                'qr_bin' => '970405',
            ],
            12 => [
                'stt' => 12,
                'name' => 'NGUYỄN THỊ THỦY',
                'stall_name' => 'Gian hàng Hàng mã NGUYỄN THỊ THỦY (Hộ 12)',
                'item_name' => 'Hàng mã',
                'price' => 0,
                'unit' => null,
                'origin' => 'Bắc Ninh',
                'phone' => '0388968700',
                'bank_name' => 'BIDV',
                'bank_account' => '16010000339603',
                'qr_bin' => '970418',
            ],
            13 => [
                'stt' => 13,
                'name' => 'NGUYỄN THỊ NĂM',
                'stall_name' => 'Gian hàng Rau củ quả NGUYỄN THỊ NĂM (Hộ 13)',
                'item_name' => 'Rau củ quả',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Chợ Vân Trì',
                'phone' => '0977611282',
                'bank_name' => 'Nam A Bank',
                'bank_account' => '388968700',
                'qr_bin' => '970428',
            ],
            14 => [
                'stt' => 14,
                'name' => 'NGUYỄN THỊ THÀNH',
                'stall_name' => 'Gian hàng Đồ ăn NGUYỄN THỊ THÀNH (Hộ 14)',
                'item_name' => 'Đồ ăn',
                'price' => 0,
                'unit' => null,
                'origin' => 'Nhà làm',
                'phone' => '0386597816',
                'bank_name' => 'Agribank',
                'bank_account' => '51696116868',
                'qr_bin' => '970405',
            ],
            15 => [
                'stt' => 15,
                'name' => 'ĐẶNG THỊ THOA',
                'stall_name' => 'Gian hàng Rau củ quả ĐẶNG THỊ THOA (Hộ 15)',
                'item_name' => 'Rau củ quả',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Nhập lại',
                'phone' => '0966561592',
                'bank_name' => null,
                'bank_account' => null,
                'qr_bin' => null,
            ],
            16 => [
                'stt' => 16,
                'name' => 'NGUYỄN THỊ NGUYÊN',
                'stall_name' => 'Gian hàng Hoa tươi NGUYỄN THỊ NGUYÊN (Hộ 16)',
                'item_name' => 'Hoa tươi',
                'price' => 0,
                'unit' => 'bó',
                'origin' => 'Mê Linh',
                'phone' => '0388434171',
                'bank_name' => 'Techcombank',
                'bank_account' => '190371089711012',
                'qr_bin' => '970407',
            ],
            17 => [
                'stt' => 17,
                'name' => 'CAO THỊ MINH',
                'stall_name' => 'Gian hàng Thịt bò CAO THỊ MINH (Hộ 17)',
                'item_name' => 'Thịt bò',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Lò mổ',
                'phone' => '096234758',
                'bank_name' => 'Vietcombank',
                'bank_account' => '0961000023623',
                'qr_bin' => '970436',
            ],
            18 => [
                'stt' => 18,
                'name' => 'NGUYỄN THỊ ĐIỆP',
                'stall_name' => 'Gian hàng Cá, hải sản NGUYỄN THỊ ĐIỆP (Hộ 18)',
                'item_name' => 'Cá, hải sản',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Nhập lại',
                'phone' => '0373785715',
                'bank_name' => null,
                'bank_account' => null,
                'qr_bin' => null,
            ],
            19 => [
                'stt' => 19,
                'name' => 'NGUYỄN THỊ DUNG',
                'stall_name' => 'Gian hàng Thịt lợn NGUYỄN THỊ DUNG (Hộ 19)',
                'item_name' => 'Thịt lợn',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Yên Thường',
                'phone' => '0989198085',
                'bank_name' => 'BIDV',
                'bank_account' => '8824490077',
                'qr_bin' => '970418',
            ],
            20 => [
                'stt' => 20,
                'name' => 'PHẠM THỊ THỊNH',
                'stall_name' => 'Gian hàng Giò chả PHẠM THỊ THỊNH (Hộ 20)',
                'item_name' => 'Giò chả',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Tự sản xuất',
                'phone' => '0354087959',
                'bank_name' => 'Agribank',
                'bank_account' => '3140205197',
                'qr_bin' => '970405',
            ],
            21 => [
                'stt' => 21,
                'name' => 'NGÔ KIM THÀNH',
                'stall_name' => 'Gian hàng Thịt lợn NGÔ KIM THÀNH (Hộ 21)',
                'item_name' => 'Thịt lợn',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Nhà dân',
                'phone' => '0365027772',
                'bank_name' => 'MBBank',
                'bank_account' => '0365027772',
                'qr_bin' => '970422',
            ],
            22 => [
                'stt' => 22,
                'name' => 'NGUYỄN THỊ LỢI',
                'stall_name' => 'Gian hàng Bún tươi NGUYỄN THỊ LỢI (Hộ 22)',
                'item_name' => 'Bún tươi',
                'price' => 0,
                'unit' => 'kg',
                'origin' => 'Yên Viên',
                'phone' => '0352401196',
                'bank_name' => 'TPBank',
                'bank_account' => '10352401196',
                'qr_bin' => '970423',
            ],
        ];

        // Xóa toàn bộ sạp cũ thuộc Chợ Sa để nạp lại chuẩn xác 22 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 3050 + $stt;
            $email = "seller.saCoLoa.{$stt}@foodmap.vn";
            $phone = $m['phone'] ?: '';
            $name = $m['name'];
            $stallName = $m['stall_name'];

            // Xử lý tạo QR VietQR
            $qrUrl = null;
            if (!empty($m['bank_account']) && !empty($m['qr_bin'])) {
                $qrUrl = "https://api.vietqr.io/image/{$m['qr_bin']}-{$m['bank_account']}-compact.png?accountName=" . urlencode($name);
            }

            // Xử lý tạo / cập nhật User
            $user = User::where('email', $email)->first();
            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: 'Cần cập nhật thông tin',
                    'password' => Hash::make('123456@'),
                    'role' => 'seller',
                    'status' => 'active',
                    'eatery_id' => $marketId,
                    'stall_id' => $stallId,
                    'bank_name' => $m['bank_name'],
                    'bank_account' => $m['bank_account'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $user->update([
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone ?: 'Cần cập nhật thông tin',
                    'role' => 'seller',
                    'status' => 'active',
                    'eatery_id' => $marketId,
                    'stall_id' => $stallId,
                    'bank_name' => $m['bank_name'],
                    'bank_account' => $m['bank_account'],
                ]);
                $userId = $user->id;
            }

            // Xử lý tạo Gian hàng
            $descParts = [];
            if (!empty($m['origin'])) $descParts[] = "Nguồn gốc: {$m['origin']}";
            $descParts[] = "Hộ kinh doanh tại Chợ Sa, Cổ Loa";
            $desc = implode('. ', $descParts) . '.';

            DB::table('ocop_products')->insert([
                'id' => $stallId,
                'eatery_id' => $marketId,
                'user_id' => $userId,
                'stall_name' => $stallName,
                'name' => $m['item_name'],
                'seller_name' => $name,
                'seller_phone' => $phone ?: 'Cần cập nhật thông tin',
                'price' => $m['price'],
                'unit' => $m['unit'],
                'description' => $desc,
                'bank_name' => $m['bank_name'],
                'bank_account' => $m['bank_account'],
                'bank_holder' => !empty($m['bank_account']) ? $name : null,
                'qr_code_path' => $qrUrl,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
