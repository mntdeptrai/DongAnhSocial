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
        $marketId = 178; // Chợ Xuân Canh

        // 1. Cập nhật thông tin Chợ Xuân Canh trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Xuân Canh',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Xã Xuân Canh, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Xuân Canh
        $bql = User::where('email', 'bql.choxuancanh@foodmap.vn')->orWhere('email', 'bql.choXuanCanh@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Xuân Canh',
                'email' => 'bql.choxuancanh@foodmap.vn',
                'phone' => '0123654178',
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
                'name' => 'Ban Quản lý Chợ Xuân Canh',
                'email' => 'bql.choxuancanh@foodmap.vn',
                'phone' => '0123654178',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Xóa các tài khoản trùng lặp và tài khoản rác cũ
        $sellerEmailsToKeep = ['bql.choxuancanh@foodmap.vn'];
        for ($i = 1; $i <= 10; $i++) {
            $sellerEmailsToKeep[] = "seller.xuanCanh.{$i}@foodmap.vn";
        }
        User::where('eatery_id', $marketId)
            ->where(function($q) use ($sellerEmailsToKeep) {
                $q->whereNotIn('email', $sellerEmailsToKeep)->orWhereNull('email')->orWhere('email', '');
            })
            ->delete();

        // 4. Danh sách 10 hộ kinh doanh chuẩn từ Excel
        $merchants = array (
  1 => 
  array (
    'stt' => 1,
    'name' => 'Đào Thị Huệ',
    'stall_name' => 'Gian hàng Hoa quả tươi Cô Huệ',
    'item_name' => 'Hoa quả theo mùa',
    'price' => 35000,
    'unit' => 'kg',
    'category' => 'Hoa quả tươi',
    'origin' => 'Vườn nhà & Chợ đầu mối',
    'phone' => '0978123456',
    'bank_name' => 'Techcombank',
    'bank_account' => '7619766888',
    'qr_bin' => '970407',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'Mai Thị Huyền',
    'stall_name' => 'Gian hàng Vàng mã & Đồ cúng Cô Huyền',
    'item_name' => 'Vàng mã & Đồ lễ cúng',
    'price' => 20000,
    'unit' => 'bộ',
    'category' => 'Vàng mã & Đồ lễ',
    'origin' => 'Tự làm & Xưởng sản xuất',
    'phone' => '0343275668',
    'bank_name' => 'VPBank',
    'bank_account' => '0973602622',
    'qr_bin' => '970432',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'Nguyễn Thị Hà',
    'stall_name' => 'Gian hàng Bánh cuốn nóng Cô Hà',
    'item_name' => 'Bánh cuốn tráng nóng',
    'price' => 20000,
    'unit' => 'suất',
    'category' => 'Ẩm thực ăn sáng',
    'origin' => 'Làm bánh tại chỗ',
    'phone' => '0983112233',
    'bank_name' => 'Techcombank',
    'bank_account' => '19051143579012',
    'qr_bin' => '970407',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'Vũ Thị Tình',
    'stall_name' => 'Gian hàng Xôi & Cháo nóng Cô Tình',
    'item_name' => 'Xôi & Cháo nóng',
    'price' => 15000,
    'unit' => 'suất',
    'category' => 'Ẩm thực ăn sáng',
    'origin' => 'Nấu tại chỗ',
    'phone' => '0977223344',
    'bank_name' => 'MBBank',
    'bank_account' => '2101019686666',
    'qr_bin' => '970422',
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'Đào Duy Hữu',
    'stall_name' => 'Gian hàng Thịt lợn sạch Chú Hữu',
    'item_name' => 'Thịt lợn tươi',
    'price' => 120000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Mua của nhân dân & Lò mổ',
    'phone' => '0395328938',
    'bank_name' => 'MBBank',
    'bank_account' => '0395328938',
    'qr_bin' => '970422',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'Lê Thị Phương',
    'stall_name' => 'Gian hàng Thịt lợn & Gà Cô Phương',
    'item_name' => 'Thịt lợn & Thịt gà tươi',
    'price' => 110000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Nhập từ xưởng lò mổ',
    'phone' => '0399224059',
    'bank_name' => 'Techcombank',
    'bank_account' => '19035708243018',
    'qr_bin' => '970407',
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'Nguyễn Thị Ngọc Anh',
    'stall_name' => 'Gian hàng Thịt lợn tươi Cô Ngọc Anh',
    'item_name' => 'Thịt lợn tươi sạch',
    'price' => 115000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Nhập hàng từ lò mổ',
    'phone' => '0328670432',
    'bank_name' => 'Agribank',
    'bank_account' => '3140205081553',
    'qr_bin' => '970405',
  ),
  8 => 
  array (
    'stt' => 8,
    'name' => 'Nguyễn Thị Thoan',
    'stall_name' => 'Gian hàng Hải sản tươi sống Cô Thoan',
    'item_name' => 'Hải sản tôm cua ốc',
    'price' => 85000,
    'unit' => 'kg',
    'category' => 'Thủy hải sản tươi sống',
    'origin' => 'Nhập hàng từ chợ đầu mối',
    'phone' => '0382299688',
    'bank_name' => 'MBBank',
    'bank_account' => '00830038888',
    'qr_bin' => '970422',
  ),
  9 => 
  array (
    'stt' => 9,
    'name' => 'Đào Thị Lan Hương',
    'stall_name' => 'Gian hàng Bánh mỳ Cô Hương',
    'item_name' => 'Bánh mỳ các loại',
    'price' => 15000,
    'unit' => 'chiếc',
    'category' => 'Bánh mỳ & Ăn sáng',
    'origin' => 'Nhập từ lò bánh mỳ',
    'phone' => '0329296532',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  10 => 
  array (
    'stt' => 10,
    'name' => 'Nguyễn Thị Hoa',
    'stall_name' => 'Gian hàng Rau củ quả sạch Cô Hoa',
    'item_name' => 'Rau củ quả tươi',
    'price' => 15000,
    'unit' => 'kg',
    'category' => 'Rau củ quả',
    'origin' => 'Nhập hàng từ chợ đầu mối',
    'phone' => '0979783923',
    'bank_name' => 'Vietcombank',
    'bank_account' => '1033474255',
    'qr_bin' => '970436',
  ),
);

        // Xóa toàn bộ sạp cũ thuộc Chợ Xuân Canh để nạp lại chuẩn xác 10 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2338 + $stt;
            $email = "seller.xuanCanh.{$stt}@foodmap.vn";
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
            $desc = "Nguồn gốc: {$m['origin']}. Hộ kinh doanh tại Chợ Xuân Canh.";

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
