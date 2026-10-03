<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Quét và xóa sạch 100% tất cả các bình luận / đánh giá rác do Security Scanner / Bot tạo ra trên toàn bộ các kết nối CSDL.
     */
    public function up(): void
    {
        $connections = ['mysql', 'mysql_education', 'mysql_market', 'mysql_stay', 'mysql_wellness', 'mysql_culture'];

        foreach ($connections as $conn) {
            try {
                // 1. Dọn dẹp bảng users rác
                if (Schema::connection($conn)->hasTable('users')) {
                    $spamUserIds = DB::connection($conn)->table('users')
                        ->whereRaw('LOWER(name) LIKE ?', ['%hfjnuiyz%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%acunetix%'])
                        ->orWhereRaw('LOWER(name) LIKE ?', ['%sqlmap%'])
                        ->orWhereRaw('LOWER(email) LIKE ?', ['%hfjnuiyz%'])
                        ->pluck('id');

                    if ($spamUserIds->isNotEmpty()) {
                        if (Schema::connection($conn)->hasTable('comments')) {
                            DB::connection($conn)->table('comments')->whereIn('user_id', $spamUserIds)->delete();
                        }
                        DB::connection($conn)->table('users')->whereIn('id', $spamUserIds)->delete();
                    }
                }

                // 2. Dọn dẹp bảng comments
                if (Schema::connection($conn)->hasTable('comments')) {
                    DB::connection($conn)->table('comments')
                        ->where(function ($q) {
                            $q->whereRaw('LOWER(guest_name) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(guest_name) LIKE ?', ['%acunetix%'])
                              ->orWhereRaw('LOWER(guest_name) LIKE ?', ['%sqlmap%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%passwd%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%esi:include%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%bxss.me%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%rpb.png%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%sleep(%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%redirtest%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%9999256%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%1be7d4csvy0%'])
                              ->orWhereRaw('content LIKE ?', ['%..%'])
                              ->orWhereRaw('content LIKE ?', ['%${%'])
                              ->orWhereRaw('content LIKE ?', ['%!(%'])
                              ->orWhereRaw('content LIKE ?', ['%^(%'])
                              ->orWhere('content', '"()')
                              ->orWhere('content', '1')
                              ->orWhere('content', ')');
                        })
                        ->delete();
                }

                // 3. Dọn dẹp bảng reviews
                if (Schema::connection($conn)->hasTable('reviews')) {
                    DB::connection($conn)->table('reviews')
                        ->where(function ($q) {
                            $q->whereRaw('LOWER(user_name) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(user_name) LIKE ?', ['%acunetix%'])
                              ->orWhereRaw('LOWER(user_name) LIKE ?', ['%sqlmap%'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%passwd%'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%esi:include%'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%bxss.me%'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%sleep(%'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%redirtest%'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%9999256%'])
                              ->orWhereRaw('comment LIKE ?', ['%..%'])
                              ->orWhereRaw('comment LIKE ?', ['%${%'])
                              ->orWhereRaw('comment LIKE ?', ['%!(%']);
                        })
                        ->delete();
                }
            } catch (\Throwable $e) {
                // Tiếp tục quét các kết nối khác nếu có lỗi kết nối đơn lẻ
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không khôi phục dữ liệu rác
    }
};
