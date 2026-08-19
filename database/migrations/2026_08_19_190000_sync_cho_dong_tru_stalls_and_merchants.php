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
        $marketId = 31; // Chợ Đông Trù

        // 1. Cập nhật thông tin Chợ Đông Trù trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Đông Trù',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Thôn Đông Trù, Xã Đông Hội, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Đông Trù
        $bql = User::where('email', 'bql.chodongtru@foodmap.vn')->orWhere('email', 'bql.choDongTru@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Đông Trù',
                'email' => 'bql.chodongtru@foodmap.vn',
                'phone' => '0123654031',
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
                'name' => 'Ban Quản lý Chợ Đông Trù',
                'email' => 'bql.chodongtru@foodmap.vn',
                'phone' => '0123654031',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Xóa các tài khoản trùng lặp và tài khoản rác cũ
        $sellerEmailsToKeep = ['bql.chodongtru@foodmap.vn'];
        for ($i = 1; $i <= 38; $i++) {
            $sellerEmailsToKeep[] = "seller.dongTru.{$i}@foodmap.vn";
        }
        User::where('eatery_id', $marketId)
            ->where(function($q) use ($sellerEmailsToKeep) {
                $q->whereNotIn('email', $sellerEmailsToKeep)->orWhereNull('email')->orWhere('email', '');
            })
            ->delete();

        // 4. Danh sách 38 hộ kinh doanh chuẩn từ file Word
        $merchants = array (
  1 => 
  array (
    'stt' => 1,
    'name' => 'Nguyễn Minh Đức',
    'stall_name' => 'Gian hàng Thịt Lợn Nguyễn Minh Đức (Hộ 1)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0332806472',
    'bank_name' => 'MBBank',
    'bank_account' => '5680155678888',
    'qr_bin' => '970422',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'Phạm Thị Hương',
    'stall_name' => 'Gian hàng Thịt Lợn Phạm Thị Hương (Hộ 2)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0398131650',
    'bank_name' => 'VietinBank',
    'bank_account' => '039131650',
    'qr_bin' => '970415',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'Nguyễn Thị Luyến',
    'stall_name' => 'Gian hàng Thịt Lợn Nguyễn Thị Luyến (Hộ 3)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0913800665',
    'bank_name' => 'MBBank',
    'bank_account' => '0913800665',
    'qr_bin' => '970422',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'Nguyễn Thị Ngọc Anh',
    'stall_name' => 'Gian hàng Thịt Lợn Nguyễn Thị Ngọc Anh (Hộ 4)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0982223685',
    'bank_name' => 'MBBank',
    'bank_account' => '2505197488',
    'qr_bin' => '970422',
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'Phạm Thị Hương',
    'stall_name' => 'Gian hàng Thịt Lợn Phạm Thị Hương (Hộ 5)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0974642984',
    'bank_name' => 'BIDV',
    'bank_account' => '8826054138',
    'qr_bin' => '970418',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'Quản Thị Nga',
    'stall_name' => 'Gian hàng Thịt Lợn Quản Thị Nga (Hộ 6)',
    'item_name' => 'thịt lợn',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0374471031',
    'bank_name' => 'MBBank',
    'bank_account' => '19074458522019',
    'qr_bin' => '970422',
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'Nguyễn Thị Hoa',
    'stall_name' => 'Gian hàng Gà Thịt Nguyễn Thị Hoa (Hộ 7)',
    'item_name' => 'Gà thịt',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0987475728',
    'bank_name' => 'MBBank',
    'bank_account' => '0987475728',
    'qr_bin' => '970422',
  ),
  8 => 
  array (
    'stt' => 8,
    'name' => 'Đào Thị Thi',
    'stall_name' => 'Gian hàng Gà Thịt Đào Thị Thi (Hộ 8)',
    'item_name' => 'Gà thịt',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0972536451',
    'bank_name' => 'BIDV',
    'bank_account' => '2141698210',
    'qr_bin' => '970418',
  ),
  9 => 
  array (
    'stt' => 9,
    'name' => 'Nguyễn Thị Nhung',
    'stall_name' => 'Gian hàng Thịt Bò Nguyễn Thị Nhung (Hộ 9)',
    'item_name' => 'Thịt bò',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0386390823',
    'bank_name' => 'MBBank',
    'bank_account' => '281019768888',
    'qr_bin' => '970422',
  ),
  10 => 
  array (
    'stt' => 10,
    'name' => 'Nguyễn Thị Mùi',
    'stall_name' => 'Gian hàng Gà Thịt Nguyễn Thị Mùi (Hộ 10)',
    'item_name' => 'Gà thịt',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0961887967',
    'bank_name' => 'BIDV',
    'bank_account' => '23430002186852',
    'qr_bin' => '970418',
  ),
  11 => 
  array (
    'stt' => 11,
    'name' => 'Lê Thị Thủy',
    'stall_name' => 'Gian hàng Thit Bò Lê Thị Thủy (Hộ 11)',
    'item_name' => 'Thit bò',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0982512088',
    'bank_name' => 'MBBank',
    'bank_account' => '19030591856011',
    'qr_bin' => '970422',
  ),
  12 => 
  array (
    'stt' => 12,
    'name' => 'Nguyễn Thi Thu Hương',
    'stall_name' => 'Gian hàng Giò Chả Nguyễn Thi Thu Hương (Hộ 12)',
    'item_name' => 'Giò chả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0355373840',
    'bank_name' => 'Vietcombank',
    'bank_account' => '19037664835012',
    'qr_bin' => '970436',
  ),
  13 => 
  array (
    'stt' => 13,
    'name' => 'Nguyễn Thị Vi',
    'stall_name' => 'Gian hàng Giò Chả Nguyễn Thị Vi (Hộ 13)',
    'item_name' => 'Giò chả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0369903276',
    'bank_name' => 'VietBank',
    'bank_account' => '1030369403267',
    'qr_bin' => '970433',
  ),
  14 => 
  array (
    'stt' => 14,
    'name' => 'Tô Hồng Thắm',
    'stall_name' => 'Gian hàng Bún Phở Bánh Mì Tô Hồng Thắm (Hộ 14)',
    'item_name' => 'Bún phở bánh mì',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0343431643',
    'bank_name' => 'VietinBank',
    'bank_account' => '103874783338',
    'qr_bin' => '970415',
  ),
  15 => 
  array (
    'stt' => 15,
    'name' => 'Nguyễn Thị Lý',
    'stall_name' => 'Gian hàng Thịt Bò Nguyễn Thị Lý (Hộ 15)',
    'item_name' => 'Thịt bò',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0982782517',
    'bank_name' => 'MBBank',
    'bank_account' => '12319738888',
    'qr_bin' => '970422',
  ),
  16 => 
  array (
    'stt' => 16,
    'name' => 'Hoàng Đình Quy',
    'stall_name' => 'Gian hàng Giò Chả Hoàng Đình Quy (Hộ 16)',
    'item_name' => 'Giò chả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0357876655',
    'bank_name' => 'MBBank',
    'bank_account' => '6666181198888',
    'qr_bin' => '970422',
  ),
  17 => 
  array (
    'stt' => 17,
    'name' => 'Hoàng Thị Loan',
    'stall_name' => 'Gian hàng Thịt Bò Hoàng Thị Loan (Hộ 17)',
    'item_name' => 'Thịt bò',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0982782517',
    'bank_name' => 'MBBank',
    'bank_account' => '981978888',
    'qr_bin' => '970422',
  ),
  18 => 
  array (
    'stt' => 18,
    'name' => 'Đào Thị Kim Anh',
    'stall_name' => 'Gian hàng Gà Thịt Đào Thị Kim Anh (Hộ 18)',
    'item_name' => 'Gà thịt',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0338547232',
    'bank_name' => 'MBBank',
    'bank_account' => '626821061973',
    'qr_bin' => '970422',
  ),
  19 => 
  array (
    'stt' => 19,
    'name' => 'Mai Thị Khiến',
    'stall_name' => 'Gian hàng Hàng Khô Mai Thị Khiến (Hộ 19)',
    'item_name' => 'Hàng khô',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0359752241',
    'bank_name' => 'VietinBank',
    'bank_account' => '103881513430',
    'qr_bin' => '970415',
  ),
  20 => 
  array (
    'stt' => 20,
    'name' => 'Mai Thị Hải Anh',
    'stall_name' => 'Gian hàng Hàng Khô Mai Thị Hải Anh (Hộ 20)',
    'item_name' => 'Hàng khô',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0977571496',
    'bank_name' => 'MBBank',
    'bank_account' => '19028122913018',
    'qr_bin' => '970422',
  ),
  21 => 
  array (
    'stt' => 21,
    'name' => 'Giàng Thị Hoa',
    'stall_name' => 'Gian hàng Hoa Giàng Thị Hoa (Hộ 21)',
    'item_name' => 'hoa',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0983631760',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  22 => 
  array (
    'stt' => 22,
    'name' => 'Nguyễn Thị Ngà',
    'stall_name' => 'Gian hàng Hoa Nguyễn Thị Ngà (Hộ 22)',
    'item_name' => 'hoa',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0984278928',
    'bank_name' => 'MBBank',
    'bank_account' => '9909842321',
    'qr_bin' => '970422',
  ),
  23 => 
  array (
    'stt' => 23,
    'name' => 'Nguyễn Thị Thanh Nhài',
    'stall_name' => 'Gian hàng Trứng Nguyễn Thị Thanh Nhài (Hộ 23)',
    'item_name' => 'trứng',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0362375073',
    'bank_name' => 'VietinBank',
    'bank_account' => '107873464983',
    'qr_bin' => '970415',
  ),
  24 => 
  array (
    'stt' => 24,
    'name' => 'Nguyễn Thị Lan Anh',
    'stall_name' => 'Gian hàng Rau Củ Quả Nguyễn Thị Lan Anh (Hộ 24)',
    'item_name' => 'Rau củ quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0341853392',
    'bank_name' => NULL,
    'bank_account' => NULL,
    'qr_bin' => NULL,
  ),
  25 => 
  array (
    'stt' => 25,
    'name' => 'Nguyễn Thị Mơ',
    'stall_name' => 'Gian hàng Rau Củ Quả Nguyễn Thị Mơ (Hộ 25)',
    'item_name' => 'Rau củ quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0376513891',
    'bank_name' => 'MBBank',
    'bank_account' => '9693999991',
    'qr_bin' => '970422',
  ),
  26 => 
  array (
    'stt' => 26,
    'name' => 'Trần Thị Hà',
    'stall_name' => 'Gian hàng Rau Củ Quả Trần Thị Hà (Hộ 26)',
    'item_name' => 'Rau củ quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0376205851',
    'bank_name' => 'MBBank',
    'bank_account' => '020018872052',
    'qr_bin' => '970422',
  ),
  27 => 
  array (
    'stt' => 27,
    'name' => 'Nguyễn Thị Thu Thủy',
    'stall_name' => 'Gian hàng Rau Củ Quả Nguyễn Thị Thu Thủy (Hộ 27)',
    'item_name' => 'Rau củ quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '034945805',
    'bank_name' => 'VietinBank',
    'bank_account' => '0131295378',
    'qr_bin' => '970415',
  ),
  28 => 
  array (
    'stt' => 28,
    'name' => 'Ngô Thị Liễu',
    'stall_name' => 'Gian hàng Tôm Cua Cá Ngô Thị Liễu (Hộ 28)',
    'item_name' => 'Tôm cua cá',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0986720552',
    'bank_name' => 'MBBank',
    'bank_account' => '0986720552',
    'qr_bin' => '970422',
  ),
  29 => 
  array (
    'stt' => 29,
    'name' => 'Lương Thanh Sơn',
    'stall_name' => 'Gian hàng Tôm Cua Cá Lương Thanh Sơn (Hộ 29)',
    'item_name' => 'Tôm cua cá',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0976152423',
    'bank_name' => 'VietinBank',
    'bank_account' => '0976152423',
    'qr_bin' => '970415',
  ),
  30 => 
  array (
    'stt' => 30,
    'name' => 'Bùi Thị Mừng',
    'stall_name' => 'Gian hàng Gà Chim Bùi Thị Mừng (Hộ 30)',
    'item_name' => 'Gà chim',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0914937351',
    'bank_name' => 'BIDV',
    'bank_account' => '2202985957',
    'qr_bin' => '970418',
  ),
  31 => 
  array (
    'stt' => 31,
    'name' => 'Nguyễn Thị Duyên',
    'stall_name' => 'Gian hàng Giò Chả Nguyễn Thị Duyên (Hộ 31)',
    'item_name' => 'Giò chả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0975079351',
    'bank_name' => 'MBBank',
    'bank_account' => '19038356983',
    'qr_bin' => '970422',
  ),
  32 => 
  array (
    'stt' => 32,
    'name' => 'Lê Kim Huệ',
    'stall_name' => 'Gian hàng Hoa Quả Lê Kim Huệ (Hộ 32)',
    'item_name' => 'Hoa quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0968396193',
    'bank_name' => 'BIDV',
    'bank_account' => '2203151690',
    'qr_bin' => '970418',
  ),
  33 => 
  array (
    'stt' => 33,
    'name' => 'Nguyễn Thị Hương',
    'stall_name' => 'Gian hàng Giò Chả Nguyễn Thị Hương (Hộ 33)',
    'item_name' => 'Giò chả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0977221883',
    'bank_name' => 'MBBank',
    'bank_account' => '0977221883',
    'qr_bin' => '970422',
  ),
  34 => 
  array (
    'stt' => 34,
    'name' => 'Lê Thị Nga',
    'stall_name' => 'Gian hàng Hoa Quả Lê Thị Nga (Hộ 34)',
    'item_name' => 'Hoa quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0357899376',
    'bank_name' => 'MBBank',
    'bank_account' => '3140205822114',
    'qr_bin' => '970422',
  ),
  35 => 
  array (
    'stt' => 35,
    'name' => 'Tạ Đăng Hoa',
    'stall_name' => 'Gian hàng Cá Tạ Đăng Hoa (Hộ 35)',
    'item_name' => 'Cá',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0375764139',
    'bank_name' => 'TPBank',
    'bank_account' => '0375764139',
    'qr_bin' => '970423',
  ),
  36 => 
  array (
    'stt' => 36,
    'name' => 'Nguyễn Thị Vân',
    'stall_name' => 'Gian hàng Bún, Bánh Nguyễn Thị Vân (Hộ 36)',
    'item_name' => 'Bún, bánh',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0986757334',
    'bank_name' => 'VietBank',
    'bank_account' => '000000748860',
    'qr_bin' => '970433',
  ),
  37 => 
  array (
    'stt' => 37,
    'name' => 'Trương Thị Bảo Oanh',
    'stall_name' => 'Gian hàng Hoa Quả Trương Thị Bảo Oanh (Hộ 37)',
    'item_name' => 'Hoa quả',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0986133144',
    'bank_name' => 'BIDV',
    'bank_account' => '2140906090',
    'qr_bin' => '970418',
  ),
  38 => 
  array (
    'stt' => 38,
    'name' => 'Nguyễn Thị Hiếu',
    'stall_name' => 'Gian hàng Tạp Hóa Nguyễn Thị Hiếu (Hộ 38)',
    'item_name' => 'Tạp hóa',
    'price' => 0,
    'unit' => NULL,
    'origin' => '',
    'phone' => '0332525291',
    'bank_name' => 'BIDV',
    'bank_account' => '2143868001',
    'qr_bin' => '970418',
  ),
);

        // Xóa toàn bộ sạp cũ thuộc Chợ Đông Trù để nạp lại chuẩn xác 38 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2950 + $stt;
            $email = "seller.dongTru.{$stt}@foodmap.vn";
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
            $descParts[] = "Hộ kinh doanh tại Chợ Đông Trù, Đông Hội";
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
