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
        $marketId = 25; // Chợ Mai Hiên

        // 1. Cập nhật thông tin Chợ Mai Hiên trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Mai Hiên',
            'category_id' => 8, // Chợ truyền thống
            'address' => 'Thôn Mai Hiên, Xã Mai Lâm, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Mai Hiên
        $bql = User::where('email', 'bql.chomaihien@foodmap.vn')->orWhere('email', 'bql.choMaiHien@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Mai Hiên (Nguyễn Văn Hải)',
                'email' => 'bql.chomaihien@foodmap.vn',
                'phone' => '0123654025',
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
                'name' => 'Ban Quản lý Chợ Mai Hiên (Nguyễn Văn Hải)',
                'email' => 'bql.chomaihien@foodmap.vn',
                'phone' => '0123654025',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Xóa các tài khoản trùng lặp và tài khoản rác
        $sellerEmailsToKeep = ['bql.chomaihien@foodmap.vn'];
        for ($i = 1; $i <= 41; $i++) {
            $sellerEmailsToKeep[] = "seller.maihien.{$i}@foodmap.vn";
        }
        User::where('eatery_id', $marketId)
            ->where(function($q) use ($sellerEmailsToKeep) {
                $q->whereNotIn('email', $sellerEmailsToKeep)->orWhereNull('email')->orWhere('email', '');
            })
            ->delete();

        // 4. Danh sách 41 hộ kinh doanh chuẩn từ Excel
        $merchants = array (
  1 => 
  array (
    'stt' => 1,
    'name' => 'Lê Thị Hưng',
    'stall_name' => 'Gian hàng Tôm cá biển tươi Lê Thị Hưng',
    'item_name' => 'Tôm cá biển tươi',
    'price' => 70000.0,
    'unit' => '1 kg',
    'price_raw' => '70.000/ 1 kg',
    'origin' => 'Chợ đầu mối Long Biên',
    'phone' => '0389556641',
    'bank_name' => 'MB',
    'bank_account' => '0389556641',
    'qr_bin' => '970422',
  ),
  2 => 
  array (
    'stt' => 2,
    'name' => 'Nguyễn Thị Uyên',
    'stall_name' => 'Gian hàng Cá tươi Nguyễn Thị Uyên',
    'item_name' => 'Cá tươi',
    'price' => 70000.0,
    'unit' => '1 kg',
    'price_raw' => '70.000/ 1 kg',
    'origin' => 'Chợ đầu mối Long Biên',
    'phone' => '0326600385',
    'bank_name' => 'MB',
    'bank_account' => '0326600385',
    'qr_bin' => '970422',
  ),
  3 => 
  array (
    'stt' => 3,
    'name' => 'Bùi Thị Kim Thu',
    'stall_name' => 'Gian hàng Rau xanh Bùi Thị Kim Thu',
    'item_name' => 'Rau xanh',
    'price' => 5000.0,
    'unit' => '1 mớ',
    'price_raw' => '5.000/ 1 mớ',
    'origin' => 'Tự sản xuất',
    'phone' => '0376682087',
    'bank_name' => 'BIDV',
    'bank_account' => '8841091900',
    'qr_bin' => '970418',
  ),
  4 => 
  array (
    'stt' => 4,
    'name' => 'Nguyễn Thị Hợp',
    'stall_name' => 'Gian hàng Thịt bò, lợn, gà Nguyễn Thị Hợp',
    'item_name' => 'Thịt bò, lợn, gà',
    'price' => 100000.0,
    'unit' => '1 kg',
    'price_raw' => '100.000/ 1 kg',
    'origin' => 'Chợ đầu mối Yên Thường',
    'phone' => '0977944760',
    'bank_name' => 'Vietcombank',
    'bank_account' => '1002521968',
    'qr_bin' => '970436',
  ),
  5 => 
  array (
    'stt' => 5,
    'name' => 'Nguyễn Thị Hạnh',
    'stall_name' => 'Gian hàng Giò chả chín Nguyễn Thị Hạnh',
    'item_name' => 'Giò chả chín',
    'price' => 150000.0,
    'unit' => '1 kg',
    'price_raw' => '150.000/ 1 kg',
    'origin' => 'Tự sản xuất',
    'phone' => '0965742278',
    'bank_name' => 'BIDV',
    'bank_account' => '2141979192',
    'qr_bin' => '970418',
  ),
  6 => 
  array (
    'stt' => 6,
    'name' => 'Nguyễn Thị Anh',
    'stall_name' => 'Gian hàng Bún phở ăn sáng Nguyễn Thị Anh',
    'item_name' => 'Bún phở ăn sáng',
    'price' => 20000.0,
    'unit' => '1 bát',
    'price_raw' => '20.000/ 1 bát',
    'origin' => 'Tự sản xuất',
    'phone' => '0989919368',
    'bank_name' => 'Viettinbank',
    'bank_account' => '102600261282',
    'qr_bin' => '970415',
  ),
  7 => 
  array (
    'stt' => 7,
    'name' => 'Nguyễn Thị Quý',
    'stall_name' => 'Gian hàng Giò chả chín Nguyễn Thị Quý',
    'item_name' => 'Giò chả chín',
    'price' => 150000.0,
    'unit' => '1 kg',
    'price_raw' => '150.000/ 1 kg',
    'origin' => 'Tự sản xuất',
    'phone' => '0394320242',
    'bank_name' => 'Viettinbank',
    'bank_account' => '008896702377',
    'qr_bin' => '970415',
  ),
  8 => 
  array (
    'stt' => 8,
    'name' => 'Nguyễn Thị Vui',
    'stall_name' => 'Gian hàng Xôi Nguyễn Thị Vui',
    'item_name' => 'Xôi',
    'price' => 5000.0,
    'unit' => '1 gói',
    'price_raw' => '5.000/ 1 gói',
    'origin' => 'Tự sản xuất',
    'phone' => '0369163894',
    'bank_name' => 'Vietcombank',
    'bank_account' => '1369163894',
    'qr_bin' => '970436',
  ),
  9 => 
  array (
    'stt' => 9,
    'name' => 'Nguyễn Thị Hoan',
    'stall_name' => 'Gian hàng Hạt rau giống Nguyễn Thị Hoan',
    'item_name' => 'Hạt rau giống',
    'price' => 10000.0,
    'unit' => '1 gói',
    'price_raw' => '10.000/ 1 gói',
    'origin' => 'Tự sản xuất',
    'phone' => '0989091638',
    'bank_name' => 'MB',
    'bank_account' => '030723939',
    'qr_bin' => '970422',
  ),
  10 => 
  array (
    'stt' => 10,
    'name' => 'Chu Thị Hiền',
    'stall_name' => 'Gian hàng Hoa tươi Chu Thị Hiền',
    'item_name' => 'Hoa tươi',
    'price' => 10000.0,
    'unit' => '1 bông',
    'price_raw' => '10.000/ 1 bông',
    'origin' => 'Chợ đầu mối Mê Linh',
    'phone' => '0366503582',
    'bank_name' => 'BIDV',
    'bank_account' => '2142806512',
    'qr_bin' => '970418',
  ),
  11 => 
  array (
    'stt' => 11,
    'name' => 'Đỗ Thị Bích Thủy',
    'stall_name' => 'Gian hàng Bánh cuốn Đỗ Thị Bích Thủy',
    'item_name' => 'Bánh cuốn',
    'price' => 12000.0,
    'unit' => '1 suất',
    'price_raw' => '12.000/ 1 suất',
    'origin' => 'Tự sản xuất',
    'phone' => '0982277653',
    'bank_name' => 'Viettinbank',
    'bank_account' => '012878704648',
    'qr_bin' => '970415',
  ),
  12 => 
  array (
    'stt' => 12,
    'name' => 'Ngô Thị Xuân Tươi',
    'stall_name' => 'Gian hàng Hoa quả Ngô Thị Xuân Tươi',
    'item_name' => 'Hoa quả',
    'price' => 10000.0,
    'unit' => '1 kg',
    'price_raw' => '10.000/ 1 kg',
    'origin' => 'Chợ đầu mối Long Biên',
    'phone' => '0383979228',
    'bank_name' => 'An Bình',
    'bank_account' => '0801029200081',
    'qr_bin' => '970425',
  ),
  13 => 
  array (
    'stt' => 13,
    'name' => 'Hoàng Thị Liên',
    'stall_name' => 'Gian hàng Rau, đậu Hoàng Thị Liên',
    'item_name' => 'Rau, đậu',
    'price' => 5000.0,
    'unit' => '1 mớ',
    'price_raw' => '5.000đ/ 1 mớ',
    'origin' => 'Tự sản xuất',
    'phone' => '0976989930',
    'bank_name' => 'Techcombank',
    'bank_account' => '220985666666',
    'qr_bin' => '970422',
  ),
  14 => 
  array (
    'stt' => 14,
    'name' => 'Nguyễn Thị Nhi',
    'stall_name' => 'Gian hàng Tôm cá biển Nguyễn Thị Nhi',
    'item_name' => 'Tôm cá biển',
    'price' => 70000.0,
    'unit' => '1 kg',
    'price_raw' => '70.000/ 1 kg',
    'origin' => 'Chợ đầu mối Long Biên',
    'phone' => '0979756203',
    'bank_name' => 'Techcombank',
    'bank_account' => '0916061980',
    'qr_bin' => '970422',
  ),
  15 => 
  array (
    'stt' => 15,
    'name' => 'Cao Thị Xuyền',
    'stall_name' => 'Gian hàng Thịt bò tươi Cao Thị Xuyền',
    'item_name' => 'Thịt bò tươi',
    'price' => 100000.0,
    'unit' => '1 kg',
    'price_raw' => '100.000/ 1 kg',
    'origin' => 'Chợ đầu mối Yên Thường',
    'phone' => '',
    'bank_name' => 'MB',
    'bank_account' => '0978718936',
    'qr_bin' => '970422',
  ),
  16 => 
  array (
    'stt' => 16,
    'name' => 'Trần Thị Thao',
    'stall_name' => 'Gian hàng Rau củ quả Trần Thị Thao',
    'item_name' => 'Rau củ quả',
    'price' => 5000.0,
    'unit' => '1 mớ',
    'price_raw' => '5.000đ/ 1 mớ',
    'origin' => 'Chợ đầu mối Yên Thường',
    'phone' => '',
    'bank_name' => 'PVcombank',
    'bank_account' => '760098648962',
    'qr_bin' => '970422',
  ),
  17 => 
  array (
    'stt' => 17,
    'name' => 'Tạ Thị Hậu',
    'stall_name' => 'Gian hàng Trứng Tạ Thị Hậu',
    'item_name' => 'Trứng',
    'price' => 5000.0,
    'unit' => '1 quả',
    'price_raw' => '5.000/ 1 quả',
    'origin' => 'Tự sản xuất',
    'phone' => '0983468083',
    'bank_name' => 'BIDV',
    'bank_account' => '2143302208',
    'qr_bin' => '970418',
  ),
  18 => 
  array (
    'stt' => 18,
    'name' => 'Nguyễn Thị Xuân Hương',
    'stall_name' => 'Gian hàng Rau xanh Nguyễn Thị Xuân Hương',
    'item_name' => 'Rau xanh',
    'price' => 5000.0,
    'unit' => '1 mớ',
    'price_raw' => '5.000đ/ 1 mớ',
    'origin' => 'Tự sản xuất',
    'phone' => '0342360718',
    'bank_name' => 'Viettinbank',
    'bank_account' => '09887838784',
    'qr_bin' => '970415',
  ),
  19 => 
  array (
    'stt' => 19,
    'name' => 'Nguyễn Thị Tuy',
    'stall_name' => 'Gian hàng Rau xanh Nguyễn Thị Tuy',
    'item_name' => 'Rau xanh',
    'price' => 5000.0,
    'unit' => '1 mớ',
    'price_raw' => '5.000đ/ 1 mớ',
    'origin' => 'Chợ đầu mối Yên Thường',
    'phone' => '0328882265',
    'bank_name' => 'Viettinbank',
    'bank_account' => '032888265',
    'qr_bin' => '970415',
  ),
  20 => 
  array (
    'stt' => 20,
    'name' => 'Hồ Thị Thanh',
    'stall_name' => 'Gian hàng Cá biển Hồ Thị Thanh',
    'item_name' => 'Cá biển',
    'price' => 70000.0,
    'unit' => '1 kg',
    'price_raw' => '70.000/ 1 kg',
    'origin' => 'Chợ đầu mối Long Biên',
    'phone' => '0975982677',
    'bank_name' => 'MB',
    'bank_account' => '050010423306',
    'qr_bin' => '970422',
  ),
  21 => 
  array (
    'stt' => 21,
    'name' => 'Đặng Thị Thắng',
    'stall_name' => 'Gian hàng Hoa quả Đặng Thị Thắng',
    'item_name' => 'Hoa quả',
    'price' => 10000.0,
    'unit' => '1 kg',
    'price_raw' => '10.000/ 1 kg',
    'origin' => 'Chợ đầu mối Long Biên',
    'phone' => '0332348903',
    'bank_name' => 'Viettinbank',
    'bank_account' => '0333234893',
    'qr_bin' => '970415',
  ),
  22 => 
  array (
    'stt' => 22,
    'name' => 'Nguyễn Thị Lành',
    'stall_name' => 'Gian hàng Rau quả Nguyễn Thị Lành',
    'item_name' => 'Rau quả',
    'price' => 5000.0,
    'unit' => '1 mớ',
    'price_raw' => '5.000đ/ 1 mớ',
    'origin' => 'Chợ đầu mối Vân Trì',
    'phone' => '0375889930',
    'bank_name' => 'Vietcombank',
    'bank_account' => '0375889930',
    'qr_bin' => '970436',
  ),
  23 => 
  array (
    'stt' => 23,
    'name' => 'Nguyễn Thị Mai',
    'stall_name' => 'Gian hàng Rau củ quả Nguyễn Thị Mai',
    'item_name' => 'Rau củ quả',
    'price' => 5000.0,
    'unit' => '1 mớ',
    'price_raw' => '5.000đ/ 1 mớ',
    'origin' => 'Chợ đầu mối Vân Trì',
    'phone' => '0973716170',
    'bank_name' => 'Techcombank',
    'bank_account' => '6868683210',
    'qr_bin' => '970422',
  ),
  24 => 
  array (
    'stt' => 24,
    'name' => 'Tống Thị Thanh',
    'stall_name' => 'Gian hàng Hải sản biển Tống Thị Thanh',
    'item_name' => 'Hải sản biển',
    'price' => 70000.0,
    'unit' => '1 kg',
    'price_raw' => '70.000/ 1 kg',
    'origin' => 'Chợ đầu mối Long Biên',
    'phone' => '0396687418',
    'bank_name' => 'MB',
    'bank_account' => '020035036769',
    'qr_bin' => '970422',
  ),
  25 => 
  array (
    'stt' => 25,
    'name' => 'Đỗ Đức Quý',
    'stall_name' => 'Gian hàng Bún Đỗ Đức Quý',
    'item_name' => 'Bún',
    'price' => 10000.0,
    'unit' => '1 kg',
    'price_raw' => '10.000/ 1 kg',
    'origin' => 'Tự sản xuất',
    'phone' => '0982737592',
    'bank_name' => 'MB',
    'bank_account' => '0982737592',
    'qr_bin' => '970422',
  ),
  26 => 
  array (
    'stt' => 26,
    'name' => 'Ngô Thị Kim Hoa',
    'stall_name' => 'Gian hàng Thịt lợn Ngô Thị Kim Hoa',
    'item_name' => 'Thịt lợn',
    'price' => 100000.0,
    'unit' => '1 kg',
    'price_raw' => '100.000/ 1 kg',
    'origin' => 'Chợ đầu mối Yên Thường',
    'phone' => '0981935380',
    'bank_name' => 'Vietcombank',
    'bank_account' => '1006568888',
    'qr_bin' => '970436',
  ),
  27 => 
  array (
    'stt' => 27,
    'name' => 'Nguyễn Thị Nhung',
    'stall_name' => 'Gian hàng Thịt bò Nguyễn Thị Nhung',
    'item_name' => 'Thịt bò',
    'price' => 200000.0,
    'unit' => '1 kg',
    'price_raw' => '200.000/ 1 kg',
    'origin' => 'Chợ đầu mối Yên Thường',
    'phone' => '0902255239',
    'bank_name' => 'MSB',
    'bank_account' => '0902285239',
    'qr_bin' => '970426',
  ),
  28 => 
  array (
    'stt' => 28,
    'name' => 'Đào Thị Thảo',
    'stall_name' => 'Gian hàng Quần áo Đào Thị Thảo',
    'item_name' => 'Quần áo',
    'price' => 100000.0,
    'unit' => '1 bộ',
    'price_raw' => '100.000/ 1 bộ',
    'origin' => 'Chợ đầu mối Ninh Hiệp',
    'phone' => '0973494985',
    'bank_name' => 'Techcombank',
    'bank_account' => '0000838627',
    'qr_bin' => '970422',
  ),
  29 => 
  array (
    'stt' => 29,
    'name' => 'Nguyễn Thị Đường',
    'stall_name' => 'Gian hàng Hàng khô Nguyễn Thị Đường',
    'item_name' => 'Hàng khô',
    'price' => 0,
    'unit' => 'kg',
    'price_raw' => '',
    'origin' => 'Chợ đầu mối Đồng Xuân',
    'phone' => '0339960625',
    'bank_name' => 'Vietcombank',
    'bank_account' => '939960625',
    'qr_bin' => '970436',
  ),
  30 => 
  array (
    'stt' => 30,
    'name' => 'Nguyễn Thị Mai',
    'stall_name' => 'Gian hàng Cá tươi Nguyễn Thị Mai',
    'item_name' => 'Cá tươi',
    'price' => 70000.0,
    'unit' => '1 kg',
    'price_raw' => '70.000/ 1 kg',
    'origin' => 'Chợ đầu mối Long Biên',
    'phone' => '0964148372',
    'bank_name' => 'Techcombank',
    'bank_account' => '999367683456',
    'qr_bin' => '970422',
  ),
  31 => 
  array (
    'stt' => 31,
    'name' => 'Nguyễn Thị Kim Liên',
    'stall_name' => 'Gian hàng Cá, trứng Nguyễn Thị Kim Liên',
    'item_name' => 'Cá, trứng',
    'price' => 5000.0,
    'unit' => '1 quả',
    'price_raw' => '5.000/ 1 quả',
    'origin' => 'Chợ đầu mối Yên Thường',
    'phone' => '0339262593',
    'bank_name' => 'BIDV',
    'bank_account' => '2141006337',
    'qr_bin' => '970418',
  ),
  32 => 
  array (
    'stt' => 32,
    'name' => 'Đặng Thị Hồng Thắm',
    'stall_name' => 'Gian hàng Rau tươi Đặng Thị Hồng Thắm',
    'item_name' => 'Rau tươi',
    'price' => 5000.0,
    'unit' => '1 mớ',
    'price_raw' => '5.000/ 1 mớ',
    'origin' => 'Chợ đầu mối Yên Thường',
    'phone' => '0385088635',
    'bank_name' => 'BIDV',
    'bank_account' => '2142866501',
    'qr_bin' => '970418',
  ),
  33 => 
  array (
    'stt' => 33,
    'name' => 'Trịnh Thị Dùng',
    'stall_name' => 'Gian hàng Bánh đúc Trịnh Thị Dùng',
    'item_name' => 'Bánh đúc',
    'price' => 10000.0,
    'unit' => '1 bát',
    'price_raw' => '10.000/ 1 bát',
    'origin' => 'Tự sản xuất',
    'phone' => '0356534100',
    'bank_name' => 'BIDV',
    'bank_account' => '8894616324',
    'qr_bin' => '970418',
  ),
  34 => 
  array (
    'stt' => 34,
    'name' => 'Trần Thị Hằng',
    'stall_name' => 'Gian hàng Gà, vịt Trần Thị Hằng',
    'item_name' => 'Gà, vịt',
    'price' => 100000.0,
    'unit' => '1 kg',
    'price_raw' => '100.000/ 1 kg',
    'origin' => 'Chợ đầu mối Thái Nguyên',
    'phone' => '0362128435',
    'bank_name' => 'MB',
    'bank_account' => '019751980',
    'qr_bin' => '970422',
  ),
  35 => 
  array (
    'stt' => 35,
    'name' => 'Nguyễn Ngọc Duy',
    'stall_name' => 'Gian hàng Gà, vịt Nguyễn Ngọc Duy',
    'item_name' => 'Gà, vịt',
    'price' => 100000.0,
    'unit' => '1 kg',
    'price_raw' => '100.000/ 1 kg',
    'origin' => 'Chợ đầu mối Thái Nguyên',
    'phone' => '0975439156',
    'bank_name' => 'AB Bank',
    'bank_account' => '3140205141939',
    'qr_bin' => '970425',
  ),
  36 => 
  array (
    'stt' => 36,
    'name' => 'Đặng Thị Vui',
    'stall_name' => 'Gian hàng Rau xanh Đặng Thị Vui',
    'item_name' => 'Rau xanh',
    'price' => 5000.0,
    'unit' => '1 mớ',
    'price_raw' => '5.000/ 1 mớ',
    'origin' => 'Chợ đầu mối Vân Trì',
    'phone' => '0968525536',
    'bank_name' => 'Tiền Phong',
    'bank_account' => '00005867700',
    'qr_bin' => '970423',
  ),
  37 => 
  array (
    'stt' => 37,
    'name' => 'Trần Thị Uyên',
    'stall_name' => 'Gian hàng Thịt lợn Trần Thị Uyên',
    'item_name' => 'Thịt lợn',
    'price' => 100000.0,
    'unit' => '1 kg',
    'price_raw' => '100.000/ 1 kg',
    'origin' => 'Chợ đầu mối Yên Thường',
    'phone' => '0983903257',
    'bank_name' => 'Đông Nam Á',
    'bank_account' => '000007105724',
    'qr_bin' => '970440',
  ),
  38 => 
  array (
    'stt' => 38,
    'name' => 'Nguyễn Thị Hường',
    'stall_name' => 'Gian hàng Hoa quả Nguyễn Thị Hường',
    'item_name' => 'Hoa quả',
    'price' => 10000.0,
    'unit' => '1 kg',
    'price_raw' => '10.000/ 1 kg',
    'origin' => 'Chợ đầu mối Long Biên',
    'phone' => '0964834468',
    'bank_name' => 'Công thương',
    'bank_account' => '0972942398',
    'qr_bin' => '970415',
  ),
  39 => 
  array (
    'stt' => 39,
    'name' => 'Nguyễn Thị Hiếu',
    'stall_name' => 'Gian hàng Quần áo Nguyễn Thị Hiếu',
    'item_name' => 'Quần áo',
    'price' => 100000.0,
    'unit' => '1 bộ',
    'price_raw' => '100.000/ 1 bộ',
    'origin' => 'Chợ đầu mối Ninh Hiệp',
    'phone' => '0944448388',
    'bank_name' => 'BIDV',
    'bank_account' => '2142735892',
    'qr_bin' => '970418',
  ),
  40 => 
  array (
    'stt' => 40,
    'name' => 'Nguyễn Thị Hoài',
    'stall_name' => 'Gian hàng Thịt lợn Nguyễn Thị Hoài',
    'item_name' => 'Thịt lợn',
    'price' => 100000.0,
    'unit' => '1 kg',
    'price_raw' => '100.000/ 1 kg',
    'origin' => 'Chợ đầu mối Yên Thường',
    'phone' => '0976083485',
    'bank_name' => 'MB',
    'bank_account' => '680986906069',
    'qr_bin' => '970422',
  ),
  41 => 
  array (
    'stt' => 41,
    'name' => 'Nguyễn Thị Bình',
    'stall_name' => 'Gian hàng Rau củ quả Nguyễn Thị Bình',
    'item_name' => 'Rau củ quả',
    'price' => 5000.0,
    'unit' => '1 mớ',
    'price_raw' => '5.000/ 1 mớ',
    'origin' => 'Chợ đầu mối Long Biên',
    'phone' => '0368741594',
    'bank_name' => 'MB',
    'bank_account' => '793533535',
    'qr_bin' => '970422',
  ),
);

        // Xóa toàn bộ sạp cũ thuộc Chợ Mai Hiên để nạp lại chuẩn xác 41 sạp
        DB::table('ocop_products')->where('eatery_id', $marketId)->delete();

        foreach ($merchants as $stt => $m) {
            $stallId = 2261 + $stt;
            $email = "seller.maihien.{$stt}@foodmap.vn";
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
            $desc = "Nguồn gốc: {$m['origin']}. Hộ kinh doanh tại Chợ Mai Hiên, Mai Lâm. Đạt cam kết ATTP.";

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
