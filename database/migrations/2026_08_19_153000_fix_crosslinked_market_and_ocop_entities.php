<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Eatery;
use App\Models\OcopProduct;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Gỡ bỏ hoàn toàn gán nhầm user_id trên 28 sản phẩm OCOP (IDs 1..28)
        $ocopProducts = DB::table('ocop_products')->whereBetween('id', [1, 28])->get();
        foreach ($ocopProducts as $p) {
            $ownerUser = User::where('eatery_id', $p->eatery_id)
                ->where('role', 'seller')
                ->where('email', 'not like', '%seller.%')
                ->first();

            DB::table('ocop_products')->where('id', $p->id)->update([
                'user_id' => $ownerUser ? $ownerUser->id : null,
                'updated_at' => now(),
            ]);
        }

        // 2. Chuẩn hóa Chợ Mai Lâm (Eatery ID: 20) - 24 Hộ kinh doanh (Stalls 2233..2254)
        for ($i = 1; $i <= 24; $i++) {
            $stallId = 2232 + $i;
            $user = User::where('email', "seller.mailam.{$i}@foodmap.vn")->first();
            if ($user) {
                $user->update([
                    'eatery_id' => 20,
                    'stall_id'  => $stallId,
                    'role'      => 'seller',
                    'status'    => 'active',
                ]);
                DB::table('ocop_products')->where('id', $stallId)->update([
                    'user_id'   => $user->id,
                    'eatery_id' => 20,
                ]);
            }
        }

        // 3. Chuẩn hóa Chợ Xuân Trạch (Eatery ID: 177) - 7 Hộ kinh doanh (Stalls 2255..2261)
        for ($i = 1; $i <= 7; $i++) {
            $stallId = 2254 + $i;
            $user = User::where('email', "seller.xuantrach.{$i}@foodmap.vn")->first();
            if ($user) {
                $user->update([
                    'eatery_id' => 177,
                    'stall_id'  => $stallId,
                    'role'      => 'seller',
                    'status'    => 'active',
                ]);
                DB::table('ocop_products')->where('id', $stallId)->update([
                    'user_id'   => $user->id,
                    'eatery_id' => 177,
                ]);
            }
        }

        // 4. Chuẩn hóa Chợ Mai Hiên (Eatery ID: 25) - 41 Hộ kinh doanh (Stalls 2262..2302)
        for ($i = 1; $i <= 41; $i++) {
            $stallId = 2261 + $i;
            $user = User::where('email', "seller.maihien.{$i}@foodmap.vn")->first();
            if ($user) {
                $user->update([
                    'eatery_id' => 25,
                    'stall_id'  => $stallId,
                    'role'      => 'seller',
                    'status'    => 'active',
                ]);
                DB::table('ocop_products')->where('id', $stallId)->update([
                    'user_id'   => $user->id,
                    'eatery_id' => 25,
                ]);
            }
        }

        // 5. Chuẩn hóa Chợ Nhồi Dưới (Eatery ID: 28) - 26 Hộ kinh doanh (Stalls 2303..2328)
        for ($i = 1; $i <= 26; $i++) {
            $stallId = 2302 + $i;
            $user = User::where('email', "seller.nhoiduoi.{$i}@foodmap.vn")->first();
            if ($user) {
                $user->update([
                    'eatery_id' => 28,
                    'stall_id'  => $stallId,
                    'role'      => 'seller',
                    'status'    => 'active',
                ]);
                DB::table('ocop_products')->where('id', $stallId)->update([
                    'user_id'   => $user->id,
                    'eatery_id' => 28,
                ]);
            }
        }

        // 6. Chuẩn hóa Chợ Xuân Canh (Eatery ID: 178) - 10 Hộ kinh doanh (Stalls 2339..2348)
        for ($i = 1; $i <= 10; $i++) {
            $stallId = 2338 + $i;
            $user = User::where('email', "seller.xuanCanh.{$i}@foodmap.vn")->first();
            if ($user) {
                $user->update([
                    'eatery_id' => 178,
                    'stall_id'  => $stallId,
                    'role'      => 'seller',
                    'status'    => 'active',
                ]);
                DB::table('ocop_products')->where('id', $stallId)->update([
                    'user_id'   => $user->id,
                    'eatery_id' => 178,
                ]);
            }
        }

        // 7. Chuẩn hóa Chợ Du Nội (Eatery ID: 24) - 15 Hộ kinh doanh (Stalls 2349..2363)
        for ($i = 1; $i <= 15; $i++) {
            $stallId = 2348 + $i;
            $user = User::where('email', "seller.duNoi.{$i}@foodmap.vn")->first();
            if ($user) {
                $user->update([
                    'eatery_id' => 24,
                    'stall_id'  => $stallId,
                    'role'      => 'seller',
                    'status'    => 'active',
                ]);
                DB::table('ocop_products')->where('id', $stallId)->update([
                    'user_id'   => $user->id,
                    'eatery_id' => 24,
                ]);
            }
        }

        // 8. Chuẩn hóa Chợ Dục Nội (Eatery ID: 21) - 11 Hộ kinh doanh (Stalls 2364..2374)
        for ($i = 1; $i <= 11; $i++) {
            $stallId = 2363 + $i;
            $user = User::where('email', "seller.ducNoi.{$i}@foodmap.vn")->first();
            if ($user) {
                $user->update([
                    'eatery_id' => 21,
                    'stall_id'  => $stallId,
                    'role'      => 'seller',
                    'status'    => 'active',
                ]);
                DB::table('ocop_products')->where('id', $stallId)->update([
                    'user_id'   => $user->id,
                    'eatery_id' => 21,
                ]);
            }
        }

        // 9. Xóa các sạp trùng lặp rác (nếu có)
        DB::table('ocop_products')->whereIn('id', array_merge(range(2446, 2453), range(2454, 2464)))->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
