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
        $marketId = 24; // Chợ Du Nội

        // 1. Cập nhật thông tin Chợ Du Nội trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Du Nội',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Thôn Du Nội, Xã Mai Lâm, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Du Nội
        $bql = User::where('email', 'bql.choDuNoi@foodmap.vn')->orWhere('email', 'bql.chodunoi@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Du Nội (Nguyễn Trung Tuyến)',
                'email' => 'bql.chodunoi@foodmap.vn',
                'phone' => '0123654024',
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
                'name' => 'Ban Quản lý Chợ Du Nội (Nguyễn Trung Tuyến)',
                'email' => 'bql.chodunoi@foodmap.vn',
                'phone' => '0123654024',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Xóa các tài khoản trùng lặp và tài khoản rác
        $sellerEmailsToKeep = ['bql.chodunoi@foodmap.vn'];
        for ($i = 1; $i <= 15; $i++) {
            $sellerEmailsToKeep[] = "seller.duNoi.{$i}@foodmap.vn";
        }
        User::where('eatery_id', $marketId)
            ->where(function($q) use ($sellerEmailsToKeep) {
                $q->whereNotIn('email', $sellerEmailsToKeep)->orWhereNull('email')->orWhere('email', '');
            })
            ->delete();

        // 4. Danh sách 15 hộ kinh doanh chuẩn từ file khảo sát Word
        $merchants = array (
  1 => 
  array (
    'stt' => 1,
    'name' => 'Nguyễn Thu Hương',
    'stall_name' => 'Gian hàng Giò chả Cô Hương',
    'item_name' => 'Giò chả lợn nạc sạch',
    'price' => 150000,
    'unit' => 'kg',
    'category' => 'Giò chả & Chế biến thịt',
    'phone' => '0984414963',
    'bank_name' => 'BIDV',
    'bank_account' => '2141149807',
    'qr_bin' => '970418',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'Nguyễn Thị Hải',
    'stall_name' => 'Gian hàng Hoa quả tươi Cô Hải',
    'item_name' => 'Hoa quả tươi nhập vườn',
    'price' => 35000,
    'unit' => 'kg',
    'category' => 'Hoa quả tươi',
    'phone' => '0373640782',
    'bank_name' => 'MBBank',
    'bank_account' => '0373640782',
    'qr_bin' => '970422',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'Nguyễn Thị Mai Phương',
    'stall_name' => 'Gian hàng Đồ khô Cô Phương',
    'item_name' => 'Hàng đồ khô tổng hợp',
    'price' => 25000,
    'unit' => 'gói',
    'category' => 'Đồ khô truyền thống',
    'phone' => '0349326098',
    'bank_name' => 'VietinBank',
    'bank_account' => '109873382914',
    'qr_bin' => '970415',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'Nguyễn Thị Ánh Tuyết',
    'stall_name' => 'Gian hàng Đậu & Dưa cà Cô Tuyết',
    'item_name' => 'Đậu phụ tươi & Dưa cà',
    'price' => 10000,
    'unit' => 'bìa',
    'category' => 'Đậu phụ, Dưa cà & Muối',
    'phone' => '0983149364',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'Trịnh Thị Đức',
    'stall_name' => 'Gian hàng Bách hóa Cô Đức',
    'item_name' => 'Hàng bách hóa tiêu dùng',
    'price' => 20000,
    'unit' => 'món',
    'category' => 'Bách hóa tiêu dùng',
    'phone' => '0988001122',
    'bank_name' => 'Vietcombank',
    'bank_account' => '9912423626',
    'qr_bin' => '970436',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'Nguyễn Thị Bình',
    'stall_name' => 'Gian hàng Bún & Bánh phở Cô Bình',
    'item_name' => 'Bún tươi',
    'price' => 15000,
    'unit' => 'kg',
    'category' => 'Bún tươi & Bánh phở',
    'phone' => '0988264914',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'Vũ Thị Tuyết',
    'stall_name' => 'Gian hàng Đậu & Trứng tươi Cô Tuyết',
    'item_name' => 'Đậu mơ & Trứng tươi',
    'price' => 4000,
    'unit' => 'bìa',
    'category' => 'Đậu mơ & Trứng gia cầm',
    'phone' => '0327129264',
    'bank_name' => 'MBBank',
    'bank_account' => '0362004199',
    'qr_bin' => '970422',
  ),
  8 => 
  array (
    'stt' => 8,
    'name' => 'Đỗ Thị Quyên',
    'stall_name' => 'Gian hàng Tôm cá tươi Cô Quyên',
    'item_name' => 'Tôm cá tươi sống',
    'price' => 80000,
    'unit' => 'kg',
    'category' => 'Thủy hải sản tươi sống',
    'phone' => '0359317064',
    'bank_name' => 'BIDV',
    'bank_account' => '88890772078',
    'qr_bin' => '970418',
  ),
  9 => 
  array (
    'stt' => 9,
    'name' => 'Kiều Thị Minh Hằng',
    'stall_name' => 'Gian hàng Rau củ quả sạch Cô Hằng',
    'item_name' => 'Rau xanh củ quả tươi',
    'price' => 15000,
    'unit' => 'mớ',
    'category' => 'Rau củ quả tươi',
    'phone' => '0338308780',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  10 => 
  array (
    'stt' => 10,
    'name' => 'Bùi Thị Hưng',
    'stall_name' => 'Gian hàng Rau củ quả sạch Cô Hưng',
    'item_name' => 'Rau củ quả sạch VietGAP',
    'price' => 18000,
    'unit' => 'kg',
    'category' => 'Rau củ quả tươi',
    'phone' => '0974470200',
    'bank_name' => 'BIDV',
    'bank_account' => '8836066333',
    'qr_bin' => '970418',
  ),
  11 => 
  array (
    'stt' => 11,
    'name' => 'Nguyễn Thị Chúc',
    'stall_name' => 'Gian hàng Rau củ quả sạch Cô Chúc',
    'item_name' => 'Nông sản củ quả tươi',
    'price' => 20000,
    'unit' => 'kg',
    'category' => 'Rau củ quả tươi',
    'phone' => '0362649566',
    'bank_name' => 'MoMo / MBBank',
    'bank_account' => '0362649566',
    'qr_bin' => '970422',
  ),
  12 => 
  array (
    'stt' => 12,
    'name' => 'Nguyễn Thị Út',
    'stall_name' => 'Gian hàng Rau củ quả sạch Cô Út',
    'item_name' => 'Rau ăn lá tươi xanh',
    'price' => 15000,
    'unit' => 'mớ',
    'category' => 'Rau củ quả tươi',
    'phone' => '0379806257',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  13 => 
  array (
    'stt' => 13,
    'name' => 'Hoàng Thị Ánh',
    'stall_name' => 'Gian hàng Thịt Cô Ánh',
    'item_name' => 'Thịt lợn nạc mông tươi',
    'price' => 120000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'phone' => '0988956892',
    'bank_name' => 'Techcombank',
    'bank_account' => '9988956892',
    'qr_bin' => '970407',
  ),
  14 => 
  array (
    'stt' => 14,
    'name' => 'Nguyễn Thị Thi',
    'stall_name' => 'Gian hàng Cá khô & Nước mắm Cô Thi',
    'item_name' => 'Cá khô chiên & Nước mắm',
    'price' => 50000,
    'unit' => 'gói',
    'category' => 'Thực phẩm khô & Nước mắm',
    'phone' => '0968670568',
    'bank_name' => 'VietinBank',
    'bank_account' => '10102537966',
    'qr_bin' => '970415',
  ),
  15 => 
  array (
    'stt' => 15,
    'name' => 'Nguyễn Thị Tuyền',
    'stall_name' => 'Gian hàng Đậu & Trứng tươi Cô Tuyền',
    'item_name' => 'Đậu rán sẵn & Trứng gà',
    'price' => 5000,
    'unit' => 'bìa',
    'category' => 'Đậu phụ & Trứng gia cầm',
    'phone' => '0395471881',
    'bank_name' => 'VietinBank',
    'bank_account' => '104869383563',
    'qr_bin' => '970415',
  ),
);

        // Xóa toàn bộ sạp cũ thuộc Chợ Du Nội để nạp lại chuẩn xác 15 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2348 + $stt;
            $email = "seller.duNoi.{$stt}@foodmap.vn";
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
            $desc = "Ngành hàng: {$m['category']}. Hộ kinh doanh tại Chợ Du Nội, Mai Lâm.";

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
