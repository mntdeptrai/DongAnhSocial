<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Eatery;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $marketId = 29; // Chợ Lý Nhân

        // 1. Cập nhật thông tin Chợ Lý Nhân trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Lý Nhân',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Thôn Lý Nhân, Xã Dục Tú, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Lý Nhân
        $bql = User::where('email', 'bql.cholynhan@foodmap.vn')->orWhere('email', 'bql.choLyNhan@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Lý Nhân',
                'email' => 'bql.cholynhan@foodmap.vn',
                'phone' => '0123654029',
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
                'name' => 'Ban Quản lý Chợ Lý Nhân',
                'email' => 'bql.cholynhan@foodmap.vn',
                'phone' => '0123654029',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Xóa các tài khoản trùng lặp và tài khoản rác cũ
        $sellerEmailsToKeep = ['bql.cholynhan@foodmap.vn'];
        for ($i = 1; $i <= 26; $i++) {
            $sellerEmailsToKeep[] = "seller.lyNhan.{$i}@foodmap.vn";
        }
        User::where('eatery_id', $marketId)
            ->where(function($q) use ($sellerEmailsToKeep) {
                $q->whereNotIn('email', $sellerEmailsToKeep)->orWhereNull('email')->orWhere('email', '');
            })
            ->delete();

        // 4. Danh sách 26 hộ kinh doanh chuẩn từ Excel
        $merchants = array (
  1 => 
  array (
    'stt' => 1,
    'name' => 'LÝ THỊ LA',
    'stall_name' => 'Gian hàng Bánh Mỳ, Rau Quả LÝ THỊ LA (Hộ 1)',
    'item_name' => 'Bánh mỳ, rau quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Nhập lại',
    'phone' => '0392486364',
    'bank_name' => 'Vietcombank',
    'bank_account' => '0351000932466',
    'qr_bin' => '970436',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'NGUYỄN THỊ THÀNH',
    'stall_name' => 'Gian hàng Cá, Tôm NGUYỄN THỊ THÀNH (Hộ 2)',
    'item_name' => 'cá, tôm',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ Long biên',
    'phone' => '0349325973',
    'bank_name' => 'Bac a bank',
    'bank_account' => '190001060013322',
    'qr_bin' => '970409',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'TRẦN ANH TÚ',
    'stall_name' => 'Gian hàng Thịt Gà TRẦN ANH TÚ (Hộ 3)',
    'item_name' => 'thịt gà',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ từ sơn',
    'phone' => '0963652280',
    'bank_name' => 'Techcombank',
    'bank_account' => '4067888888',
    'qr_bin' => '970422',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'HOÀNG THỊ XUÂN',
    'stall_name' => 'Gian hàng Hoa Quả HOÀNG THỊ XUÂN (Hộ 4)',
    'item_name' => 'hoa quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ từ sơn',
    'phone' => '0965591311',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'NGUYỄN THỊ LỆ',
    'stall_name' => 'Gian hàng Bánh Kẹo NGUYỄN THỊ LỆ (Hộ 5)',
    'item_name' => 'bánh kẹo',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'các nhà phân phối',
    'phone' => '0983660277',
    'bank_name' => 'Vietcombank',
    'bank_account' => '10447857321',
    'qr_bin' => '970436',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'TRẦN THỊ THOAN',
    'stall_name' => 'Gian hàng Thịt Lợn TRẦN THỊ THOAN (Hộ 6)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Lò mổ',
    'phone' => '0369219145',
    'bank_name' => 'MBBank',
    'bank_account' => '8832140693',
    'qr_bin' => '970422',
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'NGUYỄN THỊ TÂN',
    'stall_name' => 'Gian hàng Thịt Lợn NGUYỄN THỊ TÂN (Hộ 7)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Lò mổ',
    'phone' => '0326487793',
    'bank_name' => 'Agribank',
    'bank_account' => '3140205167201',
    'qr_bin' => '970405',
  ),
  8 => 
  array (
    'stt' => 8,
    'name' => 'HOÀNG THỊ TRANG',
    'stall_name' => 'Gian hàng Thịt Lợn HOÀNG THỊ TRANG (Hộ 8)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Lò mổ',
    'phone' => '0978449378',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  9 => 
  array (
    'stt' => 9,
    'name' => 'NGUYỄN THỊ HƯNG',
    'stall_name' => 'Gian hàng Hàng Xén NGUYỄN THỊ HƯNG (Hộ 9)',
    'item_name' => 'hàng xén',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ đồng xuân',
    'phone' => '0342627517',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  10 => 
  array (
    'stt' => 10,
    'name' => 'NGUYỄN THỊ THU',
    'stall_name' => 'Gian hàng Rau NGUYỄN THỊ THU (Hộ 10)',
    'item_name' => 'rau',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ đầu mối',
    'phone' => '0971996296',
    'bank_name' => 'Techcmbank',
    'bank_account' => '2251989999',
    'qr_bin' => '970422',
  ),
  11 => 
  array (
    'stt' => 11,
    'name' => 'Đỗ THỊ LINH',
    'stall_name' => 'Gian hàng Tạp Hóa Đỗ THỊ LINH (Hộ 11)',
    'item_name' => 'tạp hóa',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'các nhà phân phối',
    'phone' => '0974100925',
    'bank_name' => 'Techcombank',
    'bank_account' => '787519719999',
    'qr_bin' => '970422',
  ),
  12 => 
  array (
    'stt' => 12,
    'name' => 'NGUYỄN THỊ THU',
    'stall_name' => 'Gian hàng Tạp Hóa NGUYỄN THỊ THU (Hộ 12)',
    'item_name' => 'tạp hóa',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'các nhà phân phối',
    'phone' => '0984475860',
    'bank_name' => 'Techcombank',
    'bank_account' => '19076106268026',
    'qr_bin' => '970422',
  ),
  13 => 
  array (
    'stt' => 13,
    'name' => 'NGUYỄN THỊ HÒA',
    'stall_name' => 'Gian hàng Thịt Bò NGUYỄN THỊ HÒA (Hộ 13)',
    'item_name' => 'thịt bò',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Nhập lại',
    'phone' => '0977611282',
    'bank_name' => 'Vietinbank',
    'bank_account' => '102006343861',
    'qr_bin' => '970415',
  ),
  14 => 
  array (
    'stt' => 14,
    'name' => 'ĐẶNG THỊ DUNG',
    'stall_name' => 'Gian hàng Thịt Bò ĐẶNG THỊ DUNG (Hộ 14)',
    'item_name' => 'thịt bò',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Nhập lại',
    'phone' => '0374373319',
    'bank_name' => 'Techcombank',
    'bank_account' => '0374373319',
    'qr_bin' => '970422',
  ),
  15 => 
  array (
    'stt' => 15,
    'name' => 'ĐỖ THỊ TẠO',
    'stall_name' => 'Gian hàng Cháo ĐỖ THỊ TẠO (Hộ 15)',
    'item_name' => 'cháo',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Nhà tự nấu',
    'phone' => '0966561592',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  16 => 
  array (
    'stt' => 16,
    'name' => 'HOÀNG THỊ NGA',
    'stall_name' => 'Gian hàng Xôi, Cháo HOÀNG THỊ NGA (Hộ 16)',
    'item_name' => 'xôi, cháo',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Nhà tự nấu',
    'phone' => '0988671375',
    'bank_name' => 'vietinbank',
    'bank_account' => '0988671375',
    'qr_bin' => '970415',
  ),
  17 => 
  array (
    'stt' => 17,
    'name' => 'HOÀNG THỊ KHÁNH',
    'stall_name' => 'Gian hàng Xôi HOÀNG THỊ KHÁNH (Hộ 17)',
    'item_name' => 'xôi',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Nhà tự nấu',
    'phone' => '0396531026',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  18 => 
  array (
    'stt' => 18,
    'name' => 'NGUYỄN THỊ THÚY',
    'stall_name' => 'Gian hàng Bánh NGUYỄN THỊ THÚY (Hộ 18)',
    'item_name' => 'bánh',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Tự nấu',
    'phone' => '0373775715',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  19 => 
  array (
    'stt' => 19,
    'name' => 'ĐÀM THỊ HÒA',
    'stall_name' => 'Gian hàng Quần Áo ĐÀM THỊ HÒA (Hộ 19)',
    'item_name' => 'quần áo',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Nhập thanh lý',
    'phone' => '0358922392',
    'bank_name' => 'Vietinbank',
    'bank_account' => '109001047307',
    'qr_bin' => '970415',
  ),
  20 => 
  array (
    'stt' => 20,
    'name' => 'NGUYỄN THỊ HẰNG',
    'stall_name' => 'Gian hàng Quần Áo NGUYỄN THỊ HẰNG (Hộ 20)',
    'item_name' => 'quần áo',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ linh hiệp',
    'phone' => '0971931389',
    'bank_name' => 'Vietcombank',
    'bank_account' => '0961000028139',
    'qr_bin' => '970436',
  ),
  21 => 
  array (
    'stt' => 21,
    'name' => 'HOÀNG THỊ THU HÀ',
    'stall_name' => 'Gian hàng Hàng Xén HOÀNG THỊ THU HÀ (Hộ 21)',
    'item_name' => 'hàng xén',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ từ sơn',
    'phone' => '0986891123',
    'bank_name' => 'Techcom bank',
    'bank_account' => '19037105006014',
    'qr_bin' => '970407',
  ),
  22 => 
  array (
    'stt' => 22,
    'name' => 'NGUYỄN THỊ  HÀ',
    'stall_name' => 'Gian hàng Giò Chả NGUYỄN THỊ  HÀ (Hộ 22)',
    'item_name' => 'giò chả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Tự làm',
    'phone' => '0982331422',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  23 => 
  array (
    'stt' => 23,
    'name' => 'TRẦN THỊ TUYẾT',
    'stall_name' => 'Gian hàng Hoa Quả TRẦN THỊ TUYẾT (Hộ 23)',
    'item_name' => 'hoa quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ',
    'phone' => '0987785821',
    'bank_name' => 'MB Bank',
    'bank_account' => '09877785821',
    'qr_bin' => '970422',
  ),
  24 => 
  array (
    'stt' => 24,
    'name' => 'HOÀNG THỊ HÒA',
    'stall_name' => 'Gian hàng Hoa Quả HOÀNG THỊ HÒA (Hộ 24)',
    'item_name' => 'hoa quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ từ sơn',
    'phone' => '0338291962',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  25 => 
  array (
    'stt' => 25,
    'name' => 'NGUYỄN THỊ CHẤT',
    'stall_name' => 'Gian hàng Thịt Gà NGUYỄN THỊ CHẤT (Hộ 25)',
    'item_name' => 'thịt gà',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ từ sơn',
    'phone' => '0975381284',
    'bank_name' => 'MB bank',
    'bank_account' => '2906061983',
    'qr_bin' => '970422',
  ),
  26 => 
  array (
    'stt' => 26,
    'name' => 'TRẦN THỊ TÌNH',
    'stall_name' => 'Gian hàng Thịt Lợn, Giò Chả TRẦN THỊ TÌNH (Hộ 26)',
    'item_name' => 'thịt lợn, giò chả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ Long giò bán thịt iên',
    'phone' => '0357162025',
    'bank_name' => 'MB',
    'bank_account' => '789034456',
    'qr_bin' => '970422',
  ),
);

        // Xóa toàn bộ sạp cũ thuộc Chợ Lý Nhân để nạp lại chuẩn xác 26 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2697 + $stt;
            $email = "seller.lyNhan.{$stt}@foodmap.vn";
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
            $descParts[] = "Hộ kinh doanh tại Chợ Lý Nhân, Dục Tú";
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
