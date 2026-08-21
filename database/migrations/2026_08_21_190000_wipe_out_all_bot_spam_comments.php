<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Quét sạch 100% tất cả các bình luận rác spam (nội dung '1', '1BE7D4CSVY0', HfJNUIYZ,...)
     */
    public function up(): void
    {
        // 1. Tìm tất cả user rác tên 'HfJNUIYZ' (không phân biệt hoa thường)
        $spamUsers = DB::table('users')
            ->whereRaw('LOWER(name) LIKE ?', ['%hfjnuiyz%'])
            ->orWhereRaw('LOWER(email) LIKE ?', ['%hfjnuiyz%'])
            ->pluck('id');

        // 2. Xóa bình luận thuộc về các user rác
        if ($spamUsers->isNotEmpty()) {
            DB::table('comments')->whereIn('user_id', $spamUsers)->delete();
            DB::table('users')->whereIn('id', $spamUsers)->delete();
        }

        // 3. Xóa tất cả bình luận có guest_name chứa HfJNUIYZ
        DB::table('comments')
            ->whereRaw('LOWER(guest_name) LIKE ?', ['%hfjnuiyz%'])
            ->delete();

        // 4. Xóa tất cả bình luận rác có nội dung '1', '1BE7D4CSVY0', chứa mã độc bot hoặc rác ngẫu nhiên
        DB::table('comments')
            ->where('content', '1')
            ->orWhere('content', '1BE7D4CSVY0')
            ->orWhere('content', ')')
            ->orWhereRaw('content LIKE ?', ['%!(O&&!*%'])
            ->orWhereRaw('LOWER(content) LIKE ?', ['%hfjnuiyz%'])
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không khôi phục dữ liệu rác
    }
};
