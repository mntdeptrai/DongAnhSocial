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
        $marketId = 32; // Chợ Mạch Tràng

        // 1. Đảm bảo thông tin Chợ Mạch Tràng chính xác
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Mạch Tràng',
            'category_id' => 8, // Chợ truyền thống
            'address' => '4V48+XPM, Thôn Mạch Tràng, Xã Cổ Loa, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / cập nhật tài khoản BQL Chợ Mạch Tràng
        $bqlUser = User::where('email', 'bql.chomachtrang@foodmap.vn')->first();
        if (!$bqlUser) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Mạch Tràng',
                'email' => 'bql.chomachtrang@foodmap.vn',
                'phone' => '0987654321',
                'password' => Hash::make('123456@'),
                'role' => 'seller',
                'status' => 'active',
                'eatery_id' => $marketId,
                'avatar' => '🏛️',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $bqlUser->update([
                'name' => 'Ban Quản lý Chợ Mạch Tràng',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Danh sách 17 hộ kinh doanh chuẩn từ file Excel Chợ mạch tràng.xlsx
        $merchants = [
            1 => [
                'name' => 'Nguyễn Thị Sinh',
                'phone' => '0965194462',
                'stall_name' => 'Gian hàng Ăn uống Cô Sinh',
                'bank_name' => 'MBBank',
                'bank_account' => '0965194462',
                'bank_bin' => '970422',
                'has_qr' => true,
                'items' => [
                    ['id' => 100, 'name' => 'Bún Riêu cua', 'price' => 20000, 'unit' => 'bát', 'origin' => 'Mua trong làng, tự sản xuất'],
                    ['id' => 101, 'name' => 'Bún Chả', 'price' => 20000, 'unit' => 'bát', 'origin' => 'Mua trong làng, tự sản xuất'],
                ]
            ],
            2 => [
                'name' => 'Đào Thị Súc',
                'phone' => '0386394957',
                'stall_name' => 'Gian hàng Rau củ sạch Cô Súc',
                'bank_name' => 'MBBank',
                'bank_account' => '0386394957',
                'bank_bin' => '970422',
                'has_qr' => true,
                'items' => [
                    ['id' => 200, 'name' => 'Cà chua', 'price' => 25000, 'unit' => 'kg', 'origin' => 'Mua trong làng, chợ Dâu'],
                    ['id' => 201, 'name' => 'Cà rốt', 'price' => 20000, 'unit' => 'kg', 'origin' => 'Mua trong làng, chợ Dâu'],
                ]
            ],
            3 => [
                'name' => 'Trần Thị Thuyên',
                'phone' => '0393410845',
                'stall_name' => 'Gian hàng Thực phẩm khô Cô Thuyên',
                'bank_name' => null,
                'bank_account' => null,
                'bank_bin' => null,
                'has_qr' => false,
                'items' => [
                    ['id' => 300, 'name' => 'Miến', 'price' => 60000, 'unit' => 'kg', 'origin' => 'Mua chợ Tó'],
                    ['id' => 301, 'name' => 'Mộc nhĩ', 'price' => 120000, 'unit' => 'kg', 'origin' => 'Mua chợ Tó'],
                ]
            ],
            4 => [
                'name' => 'Dương Thị Đảm',
                'phone' => '0368734245',
                'stall_name' => 'Gian hàng Hoa quả Cô Đảm',
                'bank_name' => 'MBBank',
                'bank_account' => '0368734245',
                'bank_bin' => '970422',
                'has_qr' => true,
                'items' => [
                    ['id' => 400, 'name' => 'Dưa hấu', 'price' => 25000, 'unit' => 'kg', 'origin' => 'Chợ Long Biên'],
                    ['id' => 401, 'name' => 'Nhãn', 'price' => 30000, 'unit' => 'kg', 'origin' => 'Chợ Long Biên'],
                ]
            ],
            5 => [
                'name' => 'Đặng Thị Vui',
                'phone' => '0968525536',
                'stall_name' => 'Gian hàng Rau củ Cô Vui',
                'bank_name' => 'TPBank',
                'bank_account' => '00005867700',
                'bank_bin' => '970423',
                'has_qr' => true,
                'items' => [
                    ['id' => 500, 'name' => 'Đỗ xanh', 'price' => 20000, 'unit' => 'kg', 'origin' => 'Chợ Vân Trì'],
                    ['id' => 501, 'name' => 'Hành Tây', 'price' => 20000, 'unit' => 'kg', 'origin' => 'Chợ Vân Trì'],
                ]
            ],
            6 => [
                'name' => 'Đào Thị Mai',
                'phone' => '0973810892',
                'stall_name' => 'Gian hàng Thịt tươi Cô Mai',
                'bank_name' => 'Techcombank',
                'bank_account' => '2003198099',
                'bank_bin' => '970407',
                'has_qr' => true,
                'items' => [
                    ['id' => 600, 'name' => 'Thịt bò', 'price' => 250000, 'unit' => 'kg', 'origin' => 'Chợ đầu mối Bắc Thăng Long'],
                    ['id' => 601, 'name' => 'Sáo bò', 'price' => 80000, 'unit' => 'kg', 'origin' => 'Chợ đầu mối Bắc Thăng Long'],
                ]
            ],
            7 => [
                'name' => 'Hoàng Thị Bắc',
                'phone' => '0989429862',
                'stall_name' => 'Gian hàng Thực phẩm Cô Bắc',
                'bank_name' => 'MBBank',
                'bank_account' => '8319736868',
                'bank_bin' => '970422',
                'has_qr' => true,
                'items' => [
                    ['id' => 700, 'name' => 'Thịt lợn', 'price' => 110000, 'unit' => 'kg', 'origin' => 'Tự sản xuất'],
                    ['id' => 701, 'name' => 'Thịt gà', 'price' => 110000, 'unit' => 'kg', 'origin' => 'Tự sản xuất'],
                ]
            ],
            8 => [
                'name' => 'Lê Thị Hà',
                'phone' => '0973756280',
                'stall_name' => 'Gian hàng Ăn sáng Cô Hà',
                'bank_name' => 'MBBank',
                'bank_account' => '0973756280',
                'bank_bin' => '970422',
                'has_qr' => true,
                'items' => [
                    ['id' => 800, 'name' => 'Xôi', 'price' => 10000, 'unit' => 'gói', 'origin' => 'Tự sản xuất'],
                    ['id' => 801, 'name' => 'Bánh mì', 'price' => 10000, 'unit' => 'cái', 'origin' => 'Tự sản xuất'],
                ]
            ],
            9 => [
                'name' => 'Nguyễn Thị Hạnh',
                'phone' => '0975598024',
                'stall_name' => 'Gian hàng Thịt sạch Cô Hạnh',
                'bank_name' => 'MBBank',
                'bank_account' => '0975598024',
                'bank_bin' => '970422',
                'has_qr' => true,
                'items' => [
                    ['id' => 900, 'name' => 'Thịt lợn thăn sấn', 'price' => 120000, 'unit' => 'kg', 'origin' => 'Mua trong làng'],
                    ['id' => 901, 'name' => 'Thịt lợn nạc vai', 'price' => 110000, 'unit' => 'kg', 'origin' => 'Mua trong làng'],
                ]
            ],
            10 => [
                'name' => 'Nguyễn Thị Kim',
                'phone' => '0384665182',
                'stall_name' => 'Gian hàng Bánh cuốn & Bún Cô Kim',
                'bank_name' => 'VietinBank',
                'bank_account' => '105880816002',
                'bank_bin' => '970415',
                'has_qr' => true,
                'items' => [
                    ['id' => 1000, 'name' => 'Bánh cuốn', 'price' => 40000, 'unit' => 'kg', 'origin' => 'Mua trong làng, tự sản xuất'],
                    ['id' => 1001, 'name' => 'Bún', 'price' => 15000, 'unit' => 'kg', 'origin' => 'Mua trong làng, tự sản xuất'],
                ]
            ],
            11 => [
                'name' => 'Đào Thị Lệ',
                'phone' => '0372213861',
                'stall_name' => 'Gian hàng Thực phẩm Cô Lệ',
                'bank_name' => 'MBBank',
                'bank_account' => '0372213861',
                'bank_bin' => '970422',
                'has_qr' => true,
                'items' => [
                    ['id' => 1100, 'name' => 'Lòng lợn', 'price' => 70000, 'unit' => 'kg', 'origin' => 'Mua trong làng, tự sản xuất'],
                    ['id' => 1101, 'name' => 'Thịt má đào', 'price' => 150000, 'unit' => 'kg', 'origin' => 'Mua trong làng, tự sản xuất'],
                ]
            ],
            12 => [
                'name' => 'Nguyễn Thị Mai',
                'phone' => '0356363290',
                'stall_name' => 'Gian hàng Ẩm thực Cô Mai',
                'bank_name' => 'MBBank',
                'bank_account' => '0356363290',
                'bank_bin' => '970422',
                'has_qr' => true,
                'items' => [
                    ['id' => 1200, 'name' => 'Bún thịt chó', 'price' => 35000, 'unit' => 'bát', 'origin' => 'Tự sản xuất'],
                    ['id' => 1201, 'name' => 'Chả thịt chó', 'price' => 120000, 'unit' => 'đĩa', 'origin' => 'Tự sản xuất'],
                ]
            ],
            13 => [
                'name' => 'Nguyễn Thị Bốn',
                'phone' => '0394883286',
                'stall_name' => 'Gian hàng Rau củ sạch Cô Bốn',
                'bank_name' => null,
                'bank_account' => null,
                'bank_bin' => null,
                'has_qr' => false,
                'items' => [
                    ['id' => 1300, 'name' => 'Giá đỗ', 'price' => 17000, 'unit' => 'kg', 'origin' => 'Mua trong làng, tự sản xuất'],
                    ['id' => 1301, 'name' => 'Rau muống', 'price' => 4000, 'unit' => 'mớ', 'origin' => 'Mua trong làng, tự sản xuất'],
                ]
            ],
            14 => [
                'name' => 'Cao Thị Huê',
                'phone' => '0336505025',
                'stall_name' => 'Gian hàng Gạo sạch Cô Huê',
                'bank_name' => 'Techcombank',
                'bank_account' => '19037409177011',
                'bank_bin' => '970407',
                'has_qr' => true,
                'items' => [
                    ['id' => 1400, 'name' => 'Gạo ST25', 'price' => 15000, 'unit' => 'kg', 'origin' => 'Cơ sở sản xuất gạo sạch Hải Tiến'],
                    ['id' => 1401, 'name' => 'Gạo Khang dân', 'price' => 14000, 'unit' => 'kg', 'origin' => 'Cơ sở sản xuất gạo sạch Hải Tiến'],
                ]
            ],
            15 => [
                'name' => 'Nguyễn Thị Tuyến',
                'phone' => '0398214886',
                'stall_name' => 'Gian hàng Hoa quả Cô Tuyến',
                'bank_name' => 'MBBank',
                'bank_account' => '001212071985',
                'bank_bin' => '970422',
                'has_qr' => true,
                'items' => [
                    ['id' => 1500, 'name' => 'Dưa hấu', 'price' => 25000, 'unit' => 'kg', 'origin' => 'Chợ Long Biên'],
                    ['id' => 1501, 'name' => 'Dứa', 'price' => 15000, 'unit' => 'quả', 'origin' => 'Chợ Long Biên'],
                ]
            ],
            16 => [
                'name' => 'Nguyễn Thị Hòa',
                'phone' => '0977203965',
                'stall_name' => 'Gian hàng Đặc sản Nem Cô Hòa',
                'bank_name' => 'MBBank',
                'bank_account' => '0977203965',
                'bank_bin' => '970422',
                'has_qr' => true,
                'items' => [
                    ['id' => 1600, 'name' => 'Nem tai thính', 'price' => 200000, 'unit' => 'kg', 'origin' => 'Mua trong làng, tự sản xuất'],
                    ['id' => 1601, 'name' => 'Nem bì', 'price' => 80000, 'unit' => 'kg', 'origin' => 'Mua trong làng, tự sản xuất'],
                ]
            ],
            17 => [
                'name' => 'Đào Thị Hà',
                'phone' => '0986131738',
                'stall_name' => 'Gian hàng Thực phẩm Cô Hà',
                'bank_name' => 'Techcombank',
                'bank_account' => '19037126588013',
                'bank_bin' => '970407',
                'has_qr' => true,
                'items' => [
                    ['id' => 1700, 'name' => 'Cá bống vàng khô', 'price' => 130000, 'unit' => 'kg', 'origin' => 'Chợ Tó'],
                    ['id' => 1701, 'name' => 'Xúc xích', 'price' => 50000, 'unit' => 'túi 500g', 'origin' => 'Chợ Tó'],
                ]
            ],
        ];

        // Dọn dẹp tài khoản legacy rác
        $legacyEmails = [
            'cosinh@foodmap.vn', 'cosuc@foodmap.vn', 'cothuyen@foodmap.vn', 'comai@foodmap.vn',
            'minhmnt@concho.com', 'duong-thi-dam@foodmap.vn', 'dang-thi-vui@foodmap.vn',
            'hoang-thi-bac@foodmap.vn', 'le-thi-ha@foodmap.vn', 'nguyen-thi-kim@foodmap.vn',
            'dao-thi-le@foodmap.vn', 'nguyen-thi-mai@foodmap.vn', 'nguyen-thi-bon@foodmap.vn',
            'cao-thi-hue@foodmap.vn', 'nguyen-thi-tuyen@foodmap.vn', 'dao-thi-ha@foodmap.vn'
        ];
        User::whereIn('email', $legacyEmails)->delete();
        User::where('email', 'like', 'seller.machTrang.2%')->delete();

        foreach ($merchants as $stt => $m) {
            $email = "seller.machTrang.{$stt}@foodmap.vn";
            $mainStallId = $m['items'][0]['id'];

            // 1. Tạo / cập nhật User
            $user = User::where('email', $email)->first();

            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $m['name'],
                    'email' => $email,
                    'phone' => $m['phone'],
                    'password' => Hash::make('123456@'),
                    'role' => 'seller',
                    'status' => 'active',
                    'eatery_id' => $marketId,
                    'stall_id' => $mainStallId,
                    'bank_name' => $m['bank_name'],
                    'bank_account' => $m['bank_account'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $user->update([
                    'name' => $m['name'],
                    'email' => $email,
                    'phone' => $m['phone'],
                    'role' => 'seller',
                    'status' => 'active',
                    'eatery_id' => $marketId,
                    'stall_id' => $mainStallId,
                    'bank_name' => $m['bank_name'],
                    'bank_account' => $m['bank_account'],
                ]);
                $userId = $user->id;
            }

            // 2. Tạo QR URL nếu có tài khoản ngân hàng
            $qrUrl = null;
            if ($m['has_qr'] && !empty($m['bank_bin']) && !empty($m['bank_account'])) {
                $encodedName = urlencode($m['name']);
                $qrUrl = "https://api.vietqr.io/image/{$m['bank_bin']}-{$m['bank_account']}-compact.png?accountName={$encodedName}";
            }

            // 3. Cập nhật hoặc chèn từng mặt hàng của tiểu thương
            foreach ($m['items'] as $item) {
                $existing = DB::table('ocop_products')->where('id', $item['id'])->first();
                $data = [
                    'eatery_id' => $marketId,
                    'user_id' => $userId,
                    'stall_name' => $m['stall_name'],
                    'seller_name' => $m['name'],
                    'seller_phone' => $m['phone'],
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'unit' => $item['unit'],
                    'description' => "Nguồn gốc: " . $item['origin'],
                    'bank_name' => $m['bank_name'],
                    'bank_account' => $m['bank_account'],
                    'bank_holder' => $m['name'],
                    'qr_code_path' => $qrUrl,
                    'updated_at' => now(),
                ];

                if ($existing) {
                    DB::table('ocop_products')->where('id', $item['id'])->update($data);
                } else {
                    $data['id'] = $item['id'];
                    $data['created_at'] = now();
                    DB::table('ocop_products')->insert($data);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
