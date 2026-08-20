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
        // 1. Lấy toàn bộ 1.184 Cơ sở kinh doanh / Doanh nghiệp (Category 9)
        $cskdList = Eatery::where('category_id', 9)->get();

        foreach ($cskdList as $eatery) {
            $rawPhone = preg_replace('/[^0-9]/', '', $eatery->phone ?? '');
            if (empty($rawPhone) || strlen($rawPhone) < 8) {
                continue;
            }

            // Chuẩn hóa số điện thoại 10 chữ số nếu có thể
            if (!str_starts_with($rawPhone, '0') && strlen($rawPhone) <= 10) {
                $rawPhone = '0' . $rawPhone;
            }

            // Tìm User tương ứng theo phone hoặc username
            $user = User::where('phone', $rawPhone)
                ->orWhere('username', $rawPhone)
                ->first();

            // Nếu chưa có User nào, tạo mới User cho CSKD này
            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'name' => $eatery->name,
                    'username' => $rawPhone,
                    'email' => null,
                    'phone' => $rawPhone,
                    'password' => Hash::make('12345678'),
                    'role' => 'seller',
                    'status' => 'active',
                    'is_verified' => true,
                    'eatery_id' => $eatery->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $userId = $user->id;

                // Nếu user này chưa được gán eatery_id và chưa có stall_id (tức không phải tiểu thương chợ), gán eatery_id
                if (empty($user->eatery_id) && empty($user->stall_id)) {
                    $user->update([
                        'eatery_id' => $eatery->id,
                        'is_verified' => true,
                    ]);
                }
            }

            // Cập nhật eatery.user_id = user->id để liên kết 2 chiều
            DB::table('eateries')->where('id', $eatery->id)->update([
                'user_id' => $userId,
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
