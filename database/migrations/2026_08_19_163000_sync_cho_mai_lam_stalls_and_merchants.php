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
        $marketId = 20; // Chợ Mai Lâm

        // 1. Cập nhật thông tin Chợ Mai Lâm trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Mai Lâm',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Thôn Du Nội, Xã Mai Lâm, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Mai Lâm
        $bql = User::where('email', 'bql.chomailam@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Mai Lâm',
                'email' => 'bql.chomailam@foodmap.vn',
                'phone' => '0123654020',
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
                'name' => 'Ban Quản lý Chợ Mai Lâm',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Xóa các tài khoản trùng lặp và tài khoản thừa không thuộc 18 hộ khảo sát
        $sellerEmailsToKeep = [];
        for ($i = 1; $i <= 18; $i++) {
            $sellerEmailsToKeep[] = "seller.mailam.{$i}@foodmap.vn";
        }
        User::where('eatery_id', $marketId)
            ->where('email', '!=', 'bql.chomailam@foodmap.vn')
            ->whereNotIn('email', $sellerEmailsToKeep)
            ->delete();

        // 4. Danh sách 18 hộ kinh doanh chuẩn từ Excel
        $merchants = array (
  1 => 
  array (
    'stt' => 1,
    'name' => 'Nguyễn Thị Phương Dung',
    'stall_name' => 'Gian hàng Thịt Lợn Cô Dung',
    'item_name' => 'Thịt Lợn tươi',
    'price' => 110000,
    'unit' => 'kg',
    'origin' => 'Chợ đầu mối, Chợ Tó',
    'phone' => '0985815760',
    'bank_name' => 'Vietcombank',
    'bank_account' => '096100027867',
    'qr_bin' => '970436',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'Trần Thị Thoa',
    'stall_name' => 'Gian hàng Rau tươi sạch Cô Thoa',
    'item_name' => 'Rau tươi',
    'price' => 5000,
    'unit' => 'mớ',
    'origin' => 'Mua trong làng',
    'phone' => '0394065874',
    'bank_name' => 'VietinBank',
    'bank_account' => '98907746',
    'qr_bin' => '970415',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'Nguyễn Thị Luận',
    'stall_name' => 'Gian hàng Thịt gà tươi Cô Luận',
    'item_name' => 'Thịt Gà',
    'price' => 110000,
    'unit' => 'kg',
    'origin' => 'Mua trong làng',
    'phone' => '0378308895',
    'bank_name' => 'Sacombank',
    'bank_account' => '51104101509',
    'qr_bin' => '970403',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'Nguyễn Kim Dung',
    'stall_name' => 'Gian hàng Thịt gà tươi Cô Dung',
    'item_name' => 'Thịt Gà thả vườn',
    'price' => 110000,
    'unit' => 'kg',
    'origin' => 'Mua trong làng',
    'phone' => '0983578282',
    'bank_name' => 'MBBank',
    'bank_account' => '1928288888888',
    'qr_bin' => '970422',
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'Bùi Thị Cảnh',
    'stall_name' => 'Gian hàng Rau củ sạch Cô Cảnh',
    'item_name' => 'Rau củ sạch',
    'price' => 5000,
    'unit' => 'mớ',
    'origin' => 'Mua trong làng',
    'phone' => '0344836629',
    'bank_name' => 'Momo / MBBank',
    'bank_account' => '0344836629',
    'qr_bin' => '970422',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'Hoàng Thị Cúc',
    'stall_name' => 'Gian hàng Thịt lợn tươi Cô Cúc',
    'item_name' => 'Thịt Lợn sạch',
    'price' => 110000,
    'unit' => 'kg',
    'origin' => 'Mua chợ trung tâm',
    'phone' => '0987208907',
    'bank_name' => 'BIDV',
    'bank_account' => '0503615',
    'qr_bin' => '970418',
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'Nguyễn Hồng Thái',
    'stall_name' => 'Gian hàng Chè & Giải khát Chú Thái',
    'item_name' => 'Chè giải nhiệt',
    'price' => 15000,
    'unit' => 'cốc',
    'origin' => 'Tự nấu',
    'phone' => '0339447482',
    'bank_name' => 'Momo / MBBank',
    'bank_account' => '0339447482',
    'qr_bin' => '970422',
  ),
  8 => 
  array (
    'stt' => 8,
    'name' => 'Nguyễn Thị Hà',
    'stall_name' => 'Gian hàng Thịt gà tươi Cô Hà',
    'item_name' => 'Thịt Gà làm sẵn',
    'price' => 110000,
    'unit' => 'kg',
    'origin' => 'Mua trong làng',
    'phone' => '0386990516',
    'bank_name' => 'BIDV',
    'bank_account' => '1000625',
    'qr_bin' => '970418',
  ),
  9 => 
  array (
    'stt' => 9,
    'name' => 'Nguyễn Thị Lan Anh',
    'stall_name' => 'Gian hàng Trứng vịt tươi Cô Anh',
    'item_name' => 'Trứng vịt tươi',
    'price' => 3500,
    'unit' => 'quả',
    'origin' => 'Lò ấp trứng trong xã',
    'phone' => '0976290982',
    'bank_name' => 'Techcombank',
    'bank_account' => '5023111975',
    'qr_bin' => '970407',
  ),
  10 => 
  array (
    'stt' => 10,
    'name' => 'Đỗ Thị Huyền',
    'stall_name' => 'Gian hàng Trứng gà tươi Cô Huyền',
    'item_name' => 'Trứng gà tươi',
    'price' => 4000,
    'unit' => 'quả',
    'origin' => 'Lò ấp trứng trong xã',
    'phone' => '0364139460',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  11 => 
  array (
    'stt' => 11,
    'name' => 'Nguyễn Thị Lợi',
    'stall_name' => 'Gian hàng Miến & Hàng khô Cô Lợi',
    'item_name' => 'Miến khô',
    'price' => 60000,
    'unit' => 'kg',
    'origin' => 'Chợ Tó',
    'phone' => '0358095614',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  12 => 
  array (
    'stt' => 12,
    'name' => 'Nguyễn Thị Hằng',
    'stall_name' => 'Gian hàng Hoa quả Cô Hằng',
    'item_name' => 'Dưa hấu',
    'price' => 25000,
    'unit' => 'kg',
    'origin' => 'Chợ Long Biên',
    'phone' => '0373640782',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  13 => 
  array (
    'stt' => 13,
    'name' => 'Nguyễn Thị Dung',
    'stall_name' => 'Gian hàng Giò chả lợn sạch Cô Dung',
    'item_name' => 'Giò chả lợn',
    'price' => 140000,
    'unit' => 'kg',
    'origin' => 'Tự sản xuất',
    'phone' => '0366108598',
    'bank_name' => 'MBBank',
    'bank_account' => '0366108598',
    'qr_bin' => '970422',
  ),
  14 => 
  array (
    'stt' => 14,
    'name' => 'Âu Thị Tiến',
    'stall_name' => 'Gian hàng Giò chả đặc sản Cô Tiến',
    'item_name' => 'Giò chả đặc sản',
    'price' => 140000,
    'unit' => 'kg',
    'origin' => 'Tự sản xuất',
    'phone' => '0372526532',
    'bank_name' => 'MSB',
    'bank_account' => '03101010837952',
    'qr_bin' => '970426',
  ),
  15 => 
  array (
    'stt' => 15,
    'name' => 'Nguyễn Thị Gái',
    'stall_name' => 'Gian hàng Bún tươi Cô Gái',
    'item_name' => 'Bún tươi',
    'price' => 15000,
    'unit' => 'kg',
    'origin' => 'Mua trong làng',
    'phone' => '',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  16 => 
  array (
    'stt' => 16,
    'name' => 'Vũ Thị Anh',
    'stall_name' => 'Gian hàng Tạp hoá tổng hợp Cô Anh',
    'item_name' => 'Miến & Tạp hoá',
    'price' => 60000,
    'unit' => 'kg',
    'origin' => 'Chợ Tó',
    'phone' => '0978146494',
    'bank_name' => 'MBBank',
    'bank_account' => '19039634492016',
    'qr_bin' => '970422',
  ),
  17 => 
  array (
    'stt' => 17,
    'name' => 'Nguyễn Thị Hạnh',
    'stall_name' => 'Gian hàng Quần áo thời trang Cô Hạnh',
    'item_name' => 'Quần áo thời trang',
    'price' => 100000,
    'unit' => 'bộ',
    'origin' => 'Chợ Ninh Hiệp',
    'phone' => '0399007418',
    'bank_name' => 'Techcombank',
    'bank_account' => '221010666688',
    'qr_bin' => '970407',
  ),
  18 => 
  array (
    'stt' => 18,
    'name' => 'Lê Thị Hà',
    'stall_name' => 'Gian hàng Thời trang nữ & Trẻ em Cô Hà',
    'item_name' => 'Quần áo thời trang nữ',
    'price' => 100000,
    'unit' => 'bộ',
    'origin' => 'Chợ Ninh Hiệp',
    'phone' => '0869510478',
    'bank_name' => 'MBBank',
    'bank_account' => '0869510478',
    'qr_bin' => '970422',
  ),
);

        // Xóa toàn bộ sạp cũ thuộc Chợ Mai Lâm để nạp lại chuẩn xác 18 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2232 + $stt;
            $email = "seller.mailam.{$stt}@foodmap.vn";
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
            $desc = "Nguồn gốc: {$m['origin']}. Đạt tiêu chuẩn vệ sinh an toàn thực phẩm.";

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
