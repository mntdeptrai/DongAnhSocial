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
        $marketId = 22; // Chợ Dục Tú

        // 1. Cập nhật thông tin Chợ Dục Tú trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Dục Tú',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Thôn Dục Tú, Xã Dục Tú, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Dục Tú
        $bql = User::where('email', 'bql.choductu@foodmap.vn')->orWhere('email', 'bql.choDucTu@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Dục Tú',
                'email' => 'bql.choductu@foodmap.vn',
                'phone' => '0123654022',
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
                'name' => 'Ban Quản lý Chợ Dục Tú',
                'email' => 'bql.choductu@foodmap.vn',
                'phone' => '0123654022',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Xóa các tài khoản trùng lặp và tài khoản rác cũ
        $sellerEmailsToKeep = ['bql.choductu@foodmap.vn'];
        for ($i = 1; $i <= 26; $i++) {
            $sellerEmailsToKeep[] = "seller.ducTu.{$i}@foodmap.vn";
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
    'name' => 'Chu văn Đức',
    'stall_name' => 'Gian hàng Thịt Lợn Chu văn Đức (Hộ 1)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Nhập lại',
    'phone' => '0832994678',
    'bank_name' => 'nhân hàng MB',
    'bank_account' => '0384297357',
    'qr_bin' => '970422',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'Đào thị Hoa',
    'stall_name' => 'Gian hàng Thịt Lợn Đào thị Hoa (Hộ 2)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'lò mổ',
    'phone' => '0386979179',
    'bank_name' => 'Ngân hàng  VTB',
    'bank_account' => '0379126028',
    'qr_bin' => '970415',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'Nguyễn thị Duyên',
    'stall_name' => 'Gian hàng Thịt Lợn Nguyễn thị Duyên (Hộ 3)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'lò mổ',
    'phone' => '0987884782',
    'bank_name' => 'Ngân hàng BIDV',
    'bank_account' => '21243149195',
    'qr_bin' => '970418',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'Nguyễn thị Ninh',
    'stall_name' => 'Gian hàng Thịt Lợn Nguyễn thị Ninh (Hộ 4)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'nhập lại',
    'phone' => '0358102710',
    'bank_name' => 'Ngân hàng  VTB7',
    'bank_account' => '37799389',
    'qr_bin' => '970415',
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'Phạm Thị Chung',
    'stall_name' => 'Gian hàng Thịt Lợn Phạm Thị Chung (Hộ 5)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'lò mổ',
    'phone' => '1037040799786',
    'bank_name' => 'ngân hàngPG BANK',
    'bank_account' => '1037040799786',
    'qr_bin' => '970430',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'Phạm Ngọc ánh',
    'stall_name' => 'Gian hàng Giò Chả Phạm Ngọc ánh (Hộ 6)',
    'item_name' => 'giò chả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'lấy thịt tại chợ',
    'phone' => '0987982035',
    'bank_name' => 'ngân hàng viêttinbank',
    'bank_account' => '1018839091141',
    'qr_bin' => '970415',
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'Đào Thị Bích',
    'stall_name' => 'Gian hàng Giò Chả Đào Thị Bích (Hộ 7)',
    'item_name' => 'giò chả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'lấy thịt tại chợ',
    'phone' => '0379114184',
    'bank_name' => 'ngân hàng MB',
    'bank_account' => '06569868',
    'qr_bin' => '970422',
  ),
  8 => 
  array (
    'stt' => 8,
    'name' => 'Nguyễn Thị Hậu',
    'stall_name' => 'Gian hàng Thịt Bò Nguyễn Thị Hậu (Hộ 8)',
    'item_name' => 'thịt bò',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Lò mổ',
    'phone' => '0379899175',
    'bank_name' => 'ngân hàng MB',
    'bank_account' => '2379899175',
    'qr_bin' => '970422',
  ),
  9 => 
  array (
    'stt' => 9,
    'name' => 'NGUYỄN THỊ HƯNG',
    'stall_name' => 'Gian hàng Thịt Chó NGUYỄN THỊ HƯNG (Hộ 9)',
    'item_name' => 'thịt chó',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'lò mổ',
    'phone' => '0396782916',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  10 => 
  array (
    'stt' => 10,
    'name' => 'phạm thị chung',
    'stall_name' => 'Gian hàng Thịt Lợn phạm thị chung (Hộ 10)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'lò mổ',
    'phone' => '03793451781',
    'bank_name' => 'ngân hàng MB',
    'bank_account' => '0393451781',
    'qr_bin' => '970422',
  ),
  11 => 
  array (
    'stt' => 11,
    'name' => 'cửa hàng tạp hóa',
    'stall_name' => 'Gian hàng Tạp Hóa cửa hàng tạp hóa (Hộ 11)',
    'item_name' => 'tạp hóa',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'các nhà phân phối',
    'phone' => '0974100925',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  12 => 
  array (
    'stt' => 12,
    'name' => 'cửa hàng tạp hóa',
    'stall_name' => 'Gian hàng Tạp Hóa cửa hàng tạp hóa (Hộ 12)',
    'item_name' => 'tạp hóa',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'các nhà phân phối',
    'phone' => '0984475860',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  13 => 
  array (
    'stt' => 13,
    'name' => 'cửa hàng điện tử',
    'stall_name' => 'Gian hàng Sửa Chữa Điện Tử cửa hàng điện tử (Hộ 13)',
    'item_name' => 'sửa chữa điện tử',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'mua bán đồ điệntử',
    'phone' => '094431531',
    'bank_name' => 'ngân hàng viêttinbank',
    'bank_account' => '108006429943',
    'qr_bin' => '970415',
  ),
  14 => 
  array (
    'stt' => 14,
    'name' => 'nguyễn thị thanh',
    'stall_name' => 'Gian hàng Thịt Lợn nguyễn thị thanh (Hộ 14)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'lò mổ',
    'phone' => '0348916748',
    'bank_name' => 'ngân hàng MB',
    'bank_account' => '0348916748',
    'qr_bin' => '970422',
  ),
  15 => 
  array (
    'stt' => 15,
    'name' => 'Nguyễn thị Miên',
    'stall_name' => 'Gian hàng Bánh ,Xôi Nguyễn thị Miên (Hộ 15)',
    'item_name' => 'bánh ,xôi',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Nhà tự nấu',
    'phone' => '0399732974',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  16 => 
  array (
    'stt' => 16,
    'name' => 'nguyễn thị Tân',
    'stall_name' => 'Gian hàng Xôi ,Bánh nguyễn thị Tân (Hộ 16)',
    'item_name' => 'xôi ,bánh',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Nhà tự nấu',
    'phone' => '0794048313',
    'bank_name' => 'ngân hàng BIDV',
    'bank_account' => '2141420539',
    'qr_bin' => '970418',
  ),
  17 => 
  array (
    'stt' => 17,
    'name' => 'nguyễn thị Hoa',
    'stall_name' => 'Gian hàng Thtj nguyễn thị Hoa (Hộ 17)',
    'item_name' => 'thtj',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'lò mổ',
    'phone' => '0338996018',
    'bank_name' => 'ngân hàngBIDV',
    'bank_account' => '8842260542',
    'qr_bin' => '970418',
  ),
  18 => 
  array (
    'stt' => 18,
    'name' => 'lê văn Minh',
    'stall_name' => 'Gian hàng Bún lê văn Minh (Hộ 18)',
    'item_name' => 'bún',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Tự nấu',
    'phone' => '0989402092',
    'bank_name' => 'ngân hàng AB BANK',
    'bank_account' => '1051014113023',
    'qr_bin' => '970425',
  ),
  19 => 
  array (
    'stt' => 19,
    'name' => 'cửa hàng tạp hóa',
    'stall_name' => 'Gian hàng Tạp Hóa cửa hàng tạp hóa (Hộ 19)',
    'item_name' => 'tạp hóa',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'các nhà phân phối',
    'phone' => '0982719907',
    'bank_name' => 'ngân hàng BIDV',
    'bank_account' => '21110002053195',
    'qr_bin' => '970418',
  ),
  20 => 
  array (
    'stt' => 20,
    'name' => 'cửa hàng điện nước sướng cầm',
    'stall_name' => 'Gian hàng Đồ Điện + Ống Nước cửa hàng điện nước sướng cầm (Hộ 20)',
    'item_name' => 'đồ điện + ống nước',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'các nhà phân phối',
    'phone' => '',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  21 => 
  array (
    'stt' => 21,
    'name' => 'cửa hàng tạp hóa',
    'stall_name' => 'Gian hàng Hàng Xén cửa hàng tạp hóa (Hộ 21)',
    'item_name' => 'hàng xén',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ từ sơn',
    'phone' => '',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  22 => 
  array (
    'stt' => 22,
    'name' => 'Đỗ Xuân Qúy',
    'stall_name' => 'Gian hàng Giò Chả Đỗ Xuân Qúy (Hộ 22)',
    'item_name' => 'giò chả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Tự làm',
    'phone' => '0983328983',
    'bank_name' => 'ngân hàng BIDV',
    'bank_account' => '8801351367',
    'qr_bin' => '970418',
  ),
  23 => 
  array (
    'stt' => 23,
    'name' => 'Trần Văn Trung',
    'stall_name' => 'Gian hàng Hoa Quả Trần Văn Trung (Hộ 23)',
    'item_name' => 'hoa quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ',
    'phone' => '0987785821',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  24 => 
  array (
    'stt' => 24,
    'name' => 'Phạm Thị Nhàn',
    'stall_name' => 'Gian hàng Hoa Quả Phạm Thị Nhàn (Hộ 24)',
    'item_name' => 'hoa quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'các nhà phân phối',
    'phone' => '0338291962',
    'bank_name' => 'ngân hàng BIDV',
    'bank_account' => '21110002053195',
    'qr_bin' => '970418',
  ),
  25 => 
  array (
    'stt' => 25,
    'name' => 'Đỗ Thị Dung',
    'stall_name' => 'Gian hàng Thịt Gà Đỗ Thị Dung (Hộ 25)',
    'item_name' => 'thịt gà',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'Chợ từ sơn',
    'phone' => '0384166360',
    'bank_name' => 'ngân hàng MB',
    'bank_account' => '0384166360',
    'qr_bin' => '970422',
  ),
  26 => 
  array (
    'stt' => 26,
    'name' => 'nguyễn thị Thanh',
    'stall_name' => 'Gian hàng Thịt Lợn, nguyễn thị Thanh (Hộ 26)',
    'item_name' => 'thịt lợn,',
    'price' => 0,
    'unit' => NULL,
    'origin' => 'lò mổ',
    'phone' => '0348916748',
    'bank_name' => 'ngân hàng MB',
    'bank_account' => '0348916748',
    'qr_bin' => '970422',
  ),
);

        // Xóa toàn bộ sạp cũ thuộc Chợ Dục Tú để nạp lại chuẩn xác 26 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2853 + $stt;
            $email = "seller.ducTu.{$stt}@foodmap.vn";
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
            $descParts[] = "Hộ kinh doanh tại Chợ Dục Tú";
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
