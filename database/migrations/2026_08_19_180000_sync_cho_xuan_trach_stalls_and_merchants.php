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
        $marketId = 177; // Chợ Xuân Trạch

        // 1. Cập nhật thông tin Chợ Xuân Trạch trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Xuân Trạch',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Thôn Xuân Trạch, Xã Xuân Canh, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Xuân Trạch
        $bql = User::where('email', 'bql.choxuantrach@foodmap.vn')->orWhere('email', 'bql.choXuanTrach@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Xuân Trạch',
                'email' => 'bql.choxuantrach@foodmap.vn',
                'phone' => '0123654177',
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
                'name' => 'Ban Quản lý Chợ Xuân Trạch',
                'email' => 'bql.choxuantrach@foodmap.vn',
                'phone' => '0123654177',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Xóa các tài khoản trùng lặp và tài khoản rác cũ
        $sellerEmailsToKeep = ['bql.choxuantrach@foodmap.vn'];
        for ($i = 1; $i <= 7; $i++) {
            $sellerEmailsToKeep[] = "seller.xuantrach.{$i}@foodmap.vn";
        }
        User::where('eatery_id', $marketId)
            ->where(function($q) use ($sellerEmailsToKeep) {
                $q->whereNotIn('email', $sellerEmailsToKeep)->orWhereNull('email')->orWhere('email', '');
            })
            ->delete();

        // 4. Danh sách 7 hộ kinh doanh chuẩn từ Excel
        $merchants = array (
  1 => 
  array (
    'stt' => 1,
    'name' => 'Nguyễn Thị Hơn',
    'stall_name' => 'Gian hàng Bún tươi Cô Hơn',
    'item_name' => 'Bún tươi',
    'price' => 15000,
    'unit' => 'kg',
    'category' => 'Bún & Bánh phở',
    'origin' => 'Thôn Mạch Tràng',
    'phone' => '0392272686',
    'bank_name' => 'MBBank',
    'bank_account' => '5555576096666',
    'qr_bin' => '970422',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'Nguyễn Thị Oanh',
    'stall_name' => 'Gian hàng Rau đậu tươi Cô Oanh',
    'item_name' => 'Rau đậu tươi',
    'price' => 5000,
    'unit' => 'mớ',
    'category' => 'Rau củ & Đậu',
    'origin' => 'Xuân Trạch',
    'phone' => '0983636570',
    'bank_name' => 'VietinBank',
    'bank_account' => '108885050179',
    'qr_bin' => '970415',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'Đào Thị Thu Hoài',
    'stall_name' => 'Gian hàng Thịt lợn tươi Cô Hoài',
    'item_name' => 'Thịt lợn tươi',
    'price' => 120000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Phù Lỗ',
    'phone' => '0817356594',
    'bank_name' => 'Vietcombank',
    'bank_account' => '1035333116',
    'qr_bin' => '970436',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'Nguyễn Thị Nguyệt',
    'stall_name' => 'Gian hàng Thịt lợn sạch Cô Nguyệt',
    'item_name' => 'Thịt lợn sạch',
    'price' => 110000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Mua trong dân',
    'phone' => '0386351225',
    'bank_name' => 'MBBank',
    'bank_account' => '1969666999',
    'qr_bin' => '970422',
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'Lê Thị Kiều',
    'stall_name' => 'Gian hàng Chả cá nóng Cô Kiều',
    'item_name' => 'Chả cá thơm ngon',
    'price' => 100000,
    'unit' => 'kg',
    'category' => 'Thực phẩm chế biến',
    'origin' => 'Tự sản xuất',
    'phone' => '0377573726',
    'bank_name' => 'Techcombank',
    'bank_account' => '19024093048013',
    'qr_bin' => '970407',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'Nguyễn Kim Ngọc',
    'stall_name' => 'Gian hàng Thịt bò tươi Cô Ngọc',
    'item_name' => 'Thịt bò tươi',
    'price' => 270000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Cổ Điển',
    'phone' => '0386326355',
    'bank_name' => 'MBBank',
    'bank_account' => '0386326355',
    'qr_bin' => '970422',
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'Nguyễn Thị Thơm',
    'stall_name' => 'Gian hàng Thịt lợn sạch Cô Thơm',
    'item_name' => 'Thịt lợn Phù Lỗ',
    'price' => 115000,
    'unit' => 'kg',
    'category' => 'Thịt tươi sống',
    'origin' => 'Phù Lỗ',
    'phone' => '0982769366',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
);

        // Xóa toàn bộ sạp cũ thuộc Chợ Xuân Trạch để nạp lại chuẩn xác 7 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2254 + $stt;
            $email = "seller.xuantrach.{$stt}@foodmap.vn";
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
            $desc = "Nguồn gốc: {$m['origin']}. Hộ kinh doanh tại Chợ Xuân Trạch, Xuân Canh.";

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
