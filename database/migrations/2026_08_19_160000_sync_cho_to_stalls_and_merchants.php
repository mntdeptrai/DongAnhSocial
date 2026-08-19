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
        $marketId = 17; // Chợ Tó

        // 1. Cập nhật thông tin Chợ Tó trong eateries
        DB::table('eateries')->where('id', $marketId)->update([
            'name' => 'Chợ Tó',
            'category_id' => 8, // Chợ truyền thống
            'address' => '4VP4+V46, Thị trấn Đông Anh, Huyện Đông Anh, Hà Nội',
            'updated_at' => now(),
        ]);

        // 2. Tạo / Cập nhật tài khoản Ban Quản lý Chợ Tó
        $bql = User::where('email', 'bql.choto@foodmap.vn')->first();
        if (!$bql) {
            DB::table('users')->insert([
                'name' => 'Ban Quản lý Chợ Tó',
                'email' => 'bql.choto@foodmap.vn',
                'phone' => '0123654888',
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
                'name' => 'Ban Quản lý Chợ Tó',
                'eatery_id' => $marketId,
                'role' => 'seller',
                'status' => 'active',
            ]);
        }

        // 3. Đọc dữ liệu 169 hộ kinh doanh Chợ Tó
        $dataFile = base_path('cho_to_merchants_data.php');
        if (file_exists($dataFile)) {
            $merchants = require $dataFile;
        } else {
            return;
        }

        // Xóa các gian hàng duplicate rác 2407..2431
        DB::table('ocop_products')->where('eatery_id', $marketId)->whereBetween('id', [2407, 2431])->delete();

        foreach ($merchants as $stt => $m) {
            $email = "seller.choto.{$stt}@foodmap.vn";
            $phone = $m['phone'] ?: '';
            $name = $m['name'] ?: "Hộ kinh doanh số {$stt}";
            $cat = $m['category'] ?: 'Bách hóa tổng hợp';

            // Tạo mã QR VietQR nếu có số điện thoại
            $hasPhone = !empty($phone);
            $bankName = $hasPhone ? 'MBBank' : null;
            $bankAccount = $hasPhone ? $phone : null;
            $qrUrl = $hasPhone ? "https://api.vietqr.io/image/970422-{$phone}-compact.png?accountName=" . urlencode($name) : null;

            // Xử lý tạo / cập nhật User theo email duy nhất
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
                    'bank_name' => $bankName,
                    'bank_account' => $bankAccount,
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
                    'bank_name' => $bankName,
                    'bank_account' => $bankAccount,
                ]);
                $userId = $user->id;
            }

            // Xử lý tạo / cập nhật Gian hàng
            $stallName = "Gian hàng " . $cat . " " . $name;
            $descParts = [];
            if (!empty($m['address'])) $descParts[] = "Địa chỉ: " . $m['address'];
            if (!empty($m['birth_year'])) $descParts[] = "Năm sinh: " . $m['birth_year'];
            if (!empty($m['cccd'])) $descParts[] = "CCCD: " . $m['cccd'];
            $desc = implode(' | ', $descParts);

            // Tìm sạp đã có trong DB
            $stall = DB::table('ocop_products')->where('eatery_id', $marketId)
                ->where(function($q) use ($name, $phone) {
                    if (!empty($phone)) {
                        $q->where('seller_phone', $phone)->orWhere('seller_name', $name);
                    } else {
                        $q->where('seller_name', $name);
                    }
                })
                ->first();

            $stallData = [
                'eatery_id' => $marketId,
                'user_id' => $userId,
                'stall_name' => $stallName,
                'name' => $cat,
                'seller_name' => $name,
                'seller_phone' => $phone ?: 'Cần cập nhật thông tin',
                'description' => $desc,
                'bank_name' => $bankName,
                'bank_account' => $bankAccount,
                'bank_holder' => $hasPhone ? $name : null,
                'qr_code_path' => $qrUrl,
                'updated_at' => now(),
            ];

            if ($stall) {
                DB::table('ocop_products')->where('id', $stall->id)->update($stallData);
                $stallId = $stall->id;
            } else {
                $stallData['created_at'] = now();
                $stallId = DB::table('ocop_products')->insertGetId($stallData);
            }

            // Gán stall_id cho User
            DB::table('users')->where('id', $userId)->update(['stall_id' => $stallId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
