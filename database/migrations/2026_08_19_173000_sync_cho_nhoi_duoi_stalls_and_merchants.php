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
        $marketId = 28; // Chợ Nhồi Dưới

        // 1. Cập nhật thông tin Chợ Nhồi Dưới trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Nhồi Dưới',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Thôn Nhồi Dưới, Xã Cổ Loa, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Nhồi Dưới
        $bql = User::where('email', 'bql.chonhoiduoi@foodmap.vn')->orWhere('email', 'bql.choNhoiDuoi@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Nhồi Dưới',
                'email' => 'bql.chonhoiduoi@foodmap.vn',
                'phone' => '0123654028',
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
                'name' => 'Ban Quản lý Chợ Nhồi Dưới',
                'email' => 'bql.chonhoiduoi@foodmap.vn',
                'phone' => '0123654028',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Xóa các tài khoản trùng lặp và tài khoản rác
        $sellerEmailsToKeep = ['bql.chonhoiduoi@foodmap.vn'];
        for ($i = 1; $i <= 26; $i++) {
            $sellerEmailsToKeep[] = "seller.nhoiduoi.{$i}@foodmap.vn";
        }
        User::where('eatery_id', $marketId)
            ->where(function($q) use ($sellerEmailsToKeep) {
                $q->whereNotIn('email', $sellerEmailsToKeep)->orWhereNull('email')->orWhere('email', '');
            })
            ->delete();

        // 4. Danh sách 26 hộ kinh doanh chuẩn từ file Word
        $merchants = array (
  1 => 
  array (
    'stt' => 1,
    'name' => 'Lương Thị Hoạch',
    'stall_name' => 'Gian hàng Thịt tươi sống Cô Hoạch',
    'item_name' => 'Thịt tươi sống',
    'price' => 120000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Nhập chợ Cổ Điển',
    'phone' => '0973411644',
    'bank_name' => 'Techcombank',
    'bank_account' => '19037769179013',
    'qr_bin' => '970407',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'Nguyễn Thị Nga',
    'stall_name' => 'Gian hàng Thịt tươi sống Cô Nga',
    'item_name' => 'Thịt tươi sống',
    'price' => 120000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Nhập Phù Đổng',
    'phone' => '0914997650',
    'bank_name' => 'Techcombank',
    'bank_account' => '10936426386017',
    'qr_bin' => '970407',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'Trương Anh Chiến',
    'stall_name' => 'Gian hàng Rau củ quả Chú Chiến',
    'item_name' => 'Rau củ quả',
    'price' => 15000,
    'unit' => 'kg',
    'category' => 'Rau củ quả',
    'origin' => 'Nhập chợ Vân Trì',
    'phone' => '0376363320',
    'bank_name' => 'VietinBank',
    'bank_account' => '014880313145',
    'qr_bin' => '970415',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'Hoàng Thị Nụ',
    'stall_name' => 'Gian hàng Thịt gà tươi Cô Nụ',
    'item_name' => 'Thịt gà tươi',
    'price' => 110000,
    'unit' => 'kg',
    'category' => 'Gia cầm tươi sống',
    'origin' => 'Bắc Thăng Long',
    'phone' => '0373291201',
    'bank_name' => 'VietinBank',
    'bank_account' => '105887215310',
    'qr_bin' => '970415',
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'Trần Thị Huyền',
    'stall_name' => 'Gian hàng Thịt lợn sạch Cô Huyền',
    'item_name' => 'Thịt lợn tươi',
    'price' => 110000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Nhập Chợ Tó',
    'phone' => '0989788397',
    'bank_name' => 'VietinBank',
    'bank_account' => '100887214928',
    'qr_bin' => '970415',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'Hoàng Thị Hải',
    'stall_name' => 'Gian hàng Thịt bò Cô Hải',
    'item_name' => 'Thịt bò tươi',
    'price' => 220000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Nhập Chợ Tó',
    'phone' => '0397707097',
    'bank_name' => 'Techcombank',
    'bank_account' => '04368106237966',
    'qr_bin' => '970407',
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'Nguyễn Thị Điệp',
    'stall_name' => 'Gian hàng Thịt gà tươi Cô Điệp',
    'item_name' => 'Thịt gà làm sẵn',
    'price' => 110000,
    'unit' => 'kg',
    'category' => 'Gia cầm tươi sống',
    'origin' => 'Nhập Yên Thường',
    'phone' => '0385321368',
    'bank_name' => 'Agribank',
    'bank_account' => '3140205033675',
    'qr_bin' => '970405',
  ),
  8 => 
  array (
    'stt' => 8,
    'name' => 'Hoàng Thị Thoa',
    'stall_name' => 'Gian hàng Nem giò Cô Thoa',
    'item_name' => 'Nem giò truyền thống',
    'price' => 130000,
    'unit' => 'kg',
    'category' => 'Giò chả & Nem',
    'origin' => 'Tự sản xuất',
    'phone' => '0352297033',
    'bank_name' => 'MBBank',
    'bank_account' => '0352297033',
    'qr_bin' => '970422',
  ),
  9 => 
  array (
    'stt' => 9,
    'name' => 'Trương Quốc Bảo',
    'stall_name' => 'Gian hàng Trứng gà sạch Chú Bảo',
    'item_name' => 'Trứng gà ta',
    'price' => 4500,
    'unit' => 'quả',
    'category' => 'Trứng gia cầm',
    'origin' => 'Nhập Mạch Tràng',
    'phone' => '0372600593',
    'bank_name' => 'Agribank',
    'bank_account' => '3140205100477',
    'qr_bin' => '970405',
  ),
  10 => 
  array (
    'stt' => 10,
    'name' => 'Nguyễn Thị Sắc',
    'stall_name' => 'Gian hàng Bún tươi Cô Sắc',
    'item_name' => 'Bún tươi sợi nhỏ',
    'price' => 15000,
    'unit' => 'kg',
    'category' => 'Bún & Bánh phở',
    'origin' => 'Tự sản xuất',
    'phone' => '0352210757',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  11 => 
  array (
    'stt' => 11,
    'name' => 'Nguyễn Thị Xô',
    'stall_name' => 'Gian hàng Trứng gà vịt Cô Xô',
    'item_name' => 'Trứng gà vịt',
    'price' => 4000,
    'unit' => 'quả',
    'category' => 'Trứng gia cầm',
    'origin' => 'Nhập Mạch Tràng',
    'phone' => '0327906583',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  12 => 
  array (
    'stt' => 12,
    'name' => 'Hoàng Thị Luyện',
    'stall_name' => 'Gian hàng Thịt lợn Cô Luyện',
    'item_name' => 'Thịt lợn tươi ngon',
    'price' => 110000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Nhập Chợ Tó',
    'phone' => '0352298969',
    'bank_name' => 'ABBank',
    'bank_account' => '0801022396064',
    'qr_bin' => '970425',
  ),
  13 => 
  array (
    'stt' => 13,
    'name' => 'Hoàng Thị Dung',
    'stall_name' => 'Gian hàng Rau củ quả Cô Dung',
    'item_name' => 'Rau củ quả sạch',
    'price' => 15000,
    'unit' => 'kg',
    'category' => 'Rau củ quả',
    'origin' => 'Nhập Chợ Tó',
    'phone' => '',
    'bank_name' => 'VietinBank',
    'bank_account' => '108887215400',
    'qr_bin' => '970415',
  ),
  14 => 
  array (
    'stt' => 14,
    'name' => 'Hoàng Thị Phượng',
    'stall_name' => 'Gian hàng Hoa quả tươi Cô Phượng',
    'item_name' => 'Hoa quả các loại',
    'price' => 30000,
    'unit' => 'kg',
    'category' => 'Hoa quả tươi',
    'origin' => 'Nhập Chợ Long Biên',
    'phone' => '0365653598',
    'bank_name' => 'VietinBank',
    'bank_account' => '104887214988',
    'qr_bin' => '970415',
  ),
  15 => 
  array (
    'stt' => 15,
    'name' => 'Đặng Thị Duyên',
    'stall_name' => 'Gian hàng Hoa quả nhập Cô Duyên',
    'item_name' => 'Hoa quả mùa vụ',
    'price' => 35000,
    'unit' => 'kg',
    'category' => 'Hoa quả tươi',
    'origin' => 'Chợ Long Biên',
    'phone' => '0977269680',
    'bank_name' => 'Agribank',
    'bank_account' => '3140205881346',
    'qr_bin' => '970405',
  ),
  16 => 
  array (
    'stt' => 16,
    'name' => 'Nguyễn Thị Vóc',
    'stall_name' => 'Gian hàng Giò chả sạch Cô Vóc',
    'item_name' => 'Giò chả lợn nạc',
    'price' => 140000,
    'unit' => 'kg',
    'category' => 'Giò chả & Nem',
    'origin' => 'Tự sản xuất',
    'phone' => '0987658260',
    'bank_name' => 'VietinBank',
    'bank_account' => '0987658260',
    'qr_bin' => '970415',
  ),
  17 => 
  array (
    'stt' => 17,
    'name' => 'Hoàng Thị Hồng',
    'stall_name' => 'Gian hàng Thịt lợn sạch Cô Hồng',
    'item_name' => 'Thịt lợn sạch',
    'price' => 110000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Nhập Phượng Độ',
    'phone' => '0977640220',
    'bank_name' => 'ABBank',
    'bank_account' => '10071971',
    'qr_bin' => '970425',
  ),
  18 => 
  array (
    'stt' => 18,
    'name' => 'Hoàng Hữu Thống',
    'stall_name' => 'Gian hàng Cá tươi sống Chú Thống',
    'item_name' => 'Cá sông cá biển tươi sống',
    'price' => 75000,
    'unit' => 'kg',
    'category' => 'Thủy hải sản tươi sống',
    'origin' => 'Nhập Từ Sơn',
    'phone' => '0972528718',
    'bank_name' => 'MBBank',
    'bank_account' => '0972528718',
    'qr_bin' => '970422',
  ),
  19 => 
  array (
    'stt' => 19,
    'name' => 'Chu Thị Đính',
    'stall_name' => 'Gian hàng Bánh mỳ & Ăn sáng Cô Đính',
    'item_name' => 'Bánh mỳ kẹp & Bánh mỳ nóng',
    'price' => 15000,
    'unit' => 'chiếc',
    'category' => 'Bánh mỳ & Ăn sáng',
    'origin' => 'Nhập Phù Đổng',
    'phone' => '0397544954',
    'bank_name' => 'Techcombank',
    'bank_account' => '3397544954',
    'qr_bin' => '970407',
  ),
  20 => 
  array (
    'stt' => 20,
    'name' => 'Trịnh Thị Yến',
    'stall_name' => 'Gian hàng Bánh cuốn nóng Cô Yến',
    'item_name' => 'Bánh cuốn nóng truyền thống',
    'price' => 20000,
    'unit' => 'suất',
    'category' => 'Ẩm thực ăn sáng',
    'origin' => 'Nhập Mạch Tràng',
    'phone' => '0395933364',
    'bank_name' => 'VIB',
    'bank_account' => '036294703',
    'qr_bin' => '970441',
  ),
  21 => 
  array (
    'stt' => 21,
    'name' => 'Chu Thị Bình',
    'stall_name' => 'Gian hàng Bánh cuốn gia truyền Cô Bình',
    'item_name' => 'Bánh cuốn chả',
    'price' => 20000,
    'unit' => 'suất',
    'category' => 'Ẩm thực ăn sáng',
    'origin' => 'Nhập Mạch Tràng',
    'phone' => '0983967069',
    'bank_name' => 'Agribank',
    'bank_account' => '8502205234302',
    'qr_bin' => '970405',
  ),
  22 => 
  array (
    'stt' => 22,
    'name' => 'Phạm Hương Quỳnh',
    'stall_name' => 'Gian hàng Tạp hóa tổng hợp Cô Quỳnh',
    'item_name' => 'Bách hóa tiêu dùng',
    'price' => 30000,
    'unit' => 'món',
    'category' => 'Tạp hóa tổng hợp',
    'origin' => 'Nhập Hà Nội',
    'phone' => '0989974112',
    'bank_name' => 'TPBank',
    'bank_account' => '12122288888',
    'qr_bin' => '970423',
  ),
  23 => 
  array (
    'stt' => 23,
    'name' => 'Nguyễn Ngọc Lan',
    'stall_name' => 'Gian hàng Hàng khô Cô Lan',
    'item_name' => 'Miến, mộc nhĩ & Đồ khô',
    'price' => 45000,
    'unit' => 'gói',
    'category' => 'Đồ khô truyền thống',
    'origin' => 'Nhập Nam Hạnh',
    'phone' => '0396202153',
    'bank_name' => 'MBBank',
    'bank_account' => '0396202153',
    'qr_bin' => '970422',
  ),
  24 => 
  array (
    'stt' => 24,
    'name' => 'Vũ Bá Hân',
    'stall_name' => 'Gian hàng Đậu phụ sạch Chú Hân',
    'item_name' => 'Đậu phụ gia truyền',
    'price' => 4000,
    'unit' => 'bìa',
    'category' => 'Đậu phụ',
    'origin' => 'Tự sản xuất',
    'phone' => '0979733025',
    'bank_name' => 'ABBank',
    'bank_account' => '0979733025',
    'qr_bin' => '970425',
  ),
  25 => 
  array (
    'stt' => 25,
    'name' => 'Trương Thị Thu',
    'stall_name' => 'Gian hàng Bánh cuốn Cô Thu',
    'item_name' => 'Bánh cuốn',
    'price' => 15000,
    'unit' => 'suất',
    'category' => 'Ẩm thực ăn sáng',
    'origin' => 'Tự sản xuất',
    'phone' => '0392942832',
    'bank_name' => 'MBBank',
    'bank_account' => '0392942832',
    'qr_bin' => '970422',
  ),
  26 => 
  array (
    'stt' => 26,
    'name' => 'Hoàng Xuân Thiệu',
    'stall_name' => 'Gian hàng Tạp hóa gia đình Chú Thiệu',
    'item_name' => 'Tạp hóa gia đình',
    'price' => 50000,
    'unit' => 'món',
    'category' => 'Tạp hóa tổng hợp',
    'origin' => 'Nhập công ty Tín Nghĩa',
    'phone' => '0345723077',
    'bank_name' => 'Techcombank',
    'bank_account' => '19036934170011',
    'qr_bin' => '970407',
  ),
);

        // Xóa toàn bộ sạp cũ thuộc Chợ Nhồi Dưới để nạp lại chuẩn xác 26 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2302 + $stt;
            $email = "seller.nhoiduoi.{$stt}@foodmap.vn";
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
            $desc = "Nguồn gốc: {$m['origin']}. Hộ kinh doanh tại Chợ Nhồi Dưới, Cổ Loa.";

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
