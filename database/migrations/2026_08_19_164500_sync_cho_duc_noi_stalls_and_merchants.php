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
        $marketId = 21; // Chợ Dục Nội

        // 1. Cập nhật thông tin Chợ Dục Nội trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Dục Nội',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Thôn Dục Nội, Xã Việt Hùng, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Dục Nội
        $bql = User::where('email', 'bql.choDucNoi@foodmap.vn')->orWhere('email', 'bql.choducnoi@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Dục Nội (Nguyễn Khắc Biên)',
                'email' => 'bql.choducnoi@foodmap.vn',
                'phone' => '0123654021',
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
                'name' => 'Ban Quản lý Chợ Dục Nội (Nguyễn Khắc Biên)',
                'email' => 'bql.choducnoi@foodmap.vn',
                'phone' => '0123654021',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Xóa các tài khoản trùng lặp và tài khoản rác không có email
        $sellerEmailsToKeep = ['bql.choducnoi@foodmap.vn'];
        for ($i = 1; $i <= 11; $i++) {
            $sellerEmailsToKeep[] = "seller.ducNoi.{$i}@foodmap.vn";
        }
        User::where('eatery_id', $marketId)
            ->where(function($q) use ($sellerEmailsToKeep) {
                $q->whereNotIn('email', $sellerEmailsToKeep)->orWhereNull('email')->orWhere('email', '');
            })
            ->delete();

        // 4. Danh sách 11 hộ kinh doanh chuẩn từ Excel
        $merchants = array (
  1 => 
  array (
    'stt' => 1,
    'name' => 'Quầy thuốc Trung Hiếu 2',
    'stall_name' => 'Quầy thuốc Trung Hiếu 2',
    'item_name' => 'Thuốc tân dược',
    'price' => 50000,
    'unit' => 'hộp',
    'category' => 'Thuốc tân dược & Dược phẩm',
    'phone' => '0975624998',
    'bank_name' => 'BIDV',
    'bank_account' => '8807196124',
    'qr_bin' => '970418',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'Nhà Thuốc Hoàng Long',
    'stall_name' => 'Nhà Thuốc Hoàng Long',
    'item_name' => 'Dụng cụ y tế & Mỹ phẩm',
    'price' => 30000,
    'unit' => 'sản phẩm',
    'category' => 'Thuốc, Y tế & Mỹ phẩm',
    'phone' => '0981872297',
    'bank_name' => 'BIDV',
    'bank_account' => 'V3SM2024005860',
    'qr_bin' => '970418',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'Ngô Văn Linh',
    'stall_name' => 'Gian hàng Tạp hoá Chú Linh',
    'item_name' => 'Bánh kẹo & Tạp hoá',
    'price' => 20000,
    'unit' => 'gói',
    'category' => 'Tạp hoá tổng hợp',
    'phone' => '0974915596',
    'bank_name' => 'Techcombank',
    'bank_account' => '19072079042015',
    'qr_bin' => '970407',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'Nguyễn Công Lục',
    'stall_name' => 'Gian hàng Hàng gia dụng Chú Lục',
    'item_name' => 'Hàng đồ dùng đa năng gia đình',
    'price' => 25000,
    'unit' => 'món',
    'category' => 'Hàng gia dụng đa năng',
    'phone' => '0989892424',
    'bank_name' => 'MoMo / MBBank',
    'bank_account' => '0989892424',
    'qr_bin' => '970422',
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'Giày dép Linh Trang',
    'stall_name' => 'Gian hàng Giày dép Linh Trang',
    'item_name' => 'Giày dép nam nữ',
    'price' => 120000,
    'unit' => 'đôi',
    'category' => 'Giày dép thời trang',
    'phone' => '0966813762',
    'bank_name' => 'Techcombank',
    'bank_account' => '19032666997026',
    'qr_bin' => '970407',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'Công Thị Nhẫn',
    'stall_name' => 'Gian hàng Thời trang & May mặc Cô Nhẫn',
    'item_name' => 'Quần áo may mặc & Hàng da',
    'price' => 150000,
    'unit' => 'bộ',
    'category' => 'May mặc, Giày dép & Hàng da',
    'phone' => '0396882886',
    'bank_name' => 'BIDV',
    'bank_account' => '8858480785',
    'qr_bin' => '970418',
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'Nguyễn Công Đa',
    'stall_name' => 'Gian hàng Tạp hoá Chú Đa',
    'item_name' => 'Tạp hoá & Bánh kẹo',
    'price' => 15000,
    'unit' => 'gói',
    'category' => 'Tạp hoá tổng hợp',
    'phone' => '0972233572',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  8 => 
  array (
    'stt' => 8,
    'name' => 'Tạp hoá Lân Tịnh',
    'stall_name' => 'Gian hàng Tạp hoá Lân Tịnh',
    'item_name' => 'Tạp hoá bánh kẹo',
    'price' => 20000,
    'unit' => 'gói',
    'category' => 'Tạp hoá bánh kẹo',
    'phone' => '0392031313',
    'bank_name' => 'BIDV',
    'bank_account' => '2142773634',
    'qr_bin' => '970418',
  ),
  9 => 
  array (
    'stt' => 9,
    'name' => 'Hanoximex',
    'stall_name' => 'Gian hàng Hanoximex Dục Nội',
    'item_name' => 'Quần áo phụ kiện Hanoximex',
    'price' => 100000,
    'unit' => 'bộ',
    'category' => 'Quần áo phụ kiện',
    'phone' => '0988158056',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  10 => 
  array (
    'stt' => 10,
    'name' => 'Nguyễn Công Mạc',
    'stall_name' => 'Gian hàng Đồ điện nước Chú Mạc',
    'item_name' => 'Đồ điện nước gia đình',
    'price' => 45000,
    'unit' => 'món',
    'category' => 'Đồ điện nước gia đình',
    'phone' => '0346036574',
    'bank_name' => 'MBBank',
    'bank_account' => '0385055346',
    'qr_bin' => '970422',
  ),
  11 => 
  array (
    'stt' => 11,
    'name' => 'Cửa hàng đồ khô Vân Khoa',
    'stall_name' => 'Cửa hàng đồ khô Vân Khoa',
    'item_name' => 'Mộc nhĩ, măng khô & Đồ khô',
    'price' => 30000,
    'unit' => 'gói',
    'category' => 'Đồ khô truyền thống',
    'phone' => '0358908488',
    'bank_name' => 'MoMo / MBBank',
    'bank_account' => '0358908488',
    'qr_bin' => '970422',
  ),
);

        // Xóa toàn bộ sạp cũ thuộc Chợ Dục Nội để nạp lại chuẩn xác 11 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2363 + $stt;
            $email = "seller.ducNoi.{$stt}@foodmap.vn";
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
            $desc = "Ngành hàng: {$m['category']}. Hộ kinh doanh tại Chợ Dục Nội, Việt Hùng.";

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
