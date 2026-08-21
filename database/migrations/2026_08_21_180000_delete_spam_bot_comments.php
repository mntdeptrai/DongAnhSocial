<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Xóa sạch tất cả các bình luận spam rác do bot / HfJNUIYZ tạo ra trên cơ sở dữ liệu.
     */
    public function up(): void
    {
        // 1. Xóa tất cả bình luận có tên người dùng / khách vãng lai chứa 'HfJNUIYZ'
        DB::table('comments')
            ->where('guest_name', 'LIKE', '%HfJNUIYZ%')
            ->delete();

        // 2. Xóa các người dùng rác có tên 'HfJNUIYZ' (nếu bot đã tạo tài khoản)
        $spamUserIds = DB::table('users')
            ->where('name', 'LIKE', '%HfJNUIYZ%')
            ->pluck('id');

        if ($spamUserIds->isNotEmpty()) {
            DB::table('comments')
                ->whereIn('user_id', $spamUserIds)
                ->delete();

            DB::table('users')
                ->whereIn('id', $spamUserIds)
                ->delete();
        }

        // 3. Xóa các bình luận rác chứa mẫu mã bot spam (ví dụ: 1BE7D4CSVY0, !(O&&!*,...)
        DB::table('comments')
            ->where('content', '1BE7D4CSVY0')
            ->orWhere('content', 'LIKE', '%!(O&&!*%')
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không khôi phục lại bình luận rác
    }
};
