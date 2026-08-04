<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\RouteBusiness;
use App\Models\Eatery;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fetch all route businesses across all 8 routes
        $businesses = RouteBusiness::all();

        foreach ($businesses as $b) {
            $rawPhone = preg_replace('/[^0-9]/', '', $b->phone ?? '');
            
            if (!empty($rawPhone) && strlen($rawPhone) >= 8) {
                $username = $rawPhone;
                $phoneVal = $rawPhone;
            } else {
                $phoneVal = null;
                $baseUser = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', \Illuminate\Support\Str::ascii($b->owner ?: $b->name)));
                if (empty($baseUser)) {
                    $baseUser = 'cuahang' . $b->id;
                }
                $username = $baseUser;
                $counter = 1;
                while (User::where('username', $username)->where('id', '!=', $b->user_id)->exists()) {
                    $username = $baseUser . $counter;
                    $counter++;
                }
            }

            $user = null;
            if ($b->user_id) {
                $user = User::find($b->user_id);
            }
            if (!$user && $phoneVal) {
                $user = User::where('phone', $phoneVal)->first();
            }
            if (!$user) {
                $user = User::where('username', $username)->first();
            }

            if (!$user) {
                $user = User::create([
                    'name'         => $b->owner ?: $b->name,
                    'username'     => $username,
                    'email'        => null,
                    'phone'        => $phoneVal,
                    'password'     => Hash::make('12345678'),
                    'role'         => 'seller',
                    'status'       => 'active',
                    'bank_account' => $b->bank_account,
                    'bank_name'    => $b->bank_name,
                ]);
            } else {
                $user->update([
                    'name'         => $b->owner ?: $user->name,
                    'username'     => $username,
                    'phone'        => $phoneVal ?: $user->phone,
                    'bank_account' => $b->bank_account ?: $user->bank_account,
                    'bank_name'    => $b->bank_name ?: $user->bank_name,
                    'role'         => 'seller',
                ]);
            }

            // Link route business to user account
            $b->update([
                'user_id' => $user->id,
                'phone'   => $phoneVal ?: $b->phone
            ]);

            // Link to eatery if matching by phone
            $eatery = Eatery::where('phone', $rawPhone)->first();
            if ($eatery) {
                $eatery->update(['user_id' => $user->id]);
                $user->update(['eatery_id' => $eatery->id]);
            }

            // Link to market stall (ocop_products) if matching seller_phone
            $stall = DB::table('ocop_products')->where('seller_phone', $rawPhone)->first();
            if ($stall) {
                $user->update(['stall_id' => $stall->id]);
            }
        }

        // Clean up any orphan seller users without linked stores
        $allSellers = User::where('role', 'seller')->get();
        foreach ($allSellers as $u) {
            $hasRouteBiz = RouteBusiness::where('user_id', $u->id)->exists();
            $hasStall = $u->stall_id || DB::table('ocop_products')->where('user_id', $u->id)->exists();
            $hasEatery = $u->eatery_id || Eatery::where('user_id', $u->id)->exists();

            if (!$hasRouteBiz && !$hasStall && !$hasEatery) {
                $u->delete();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        RouteBusiness::query()->update(['user_id' => null]);
    }
};
