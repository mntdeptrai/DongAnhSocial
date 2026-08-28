<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Quét và xóa sạch 100% tất cả các bình luận / đánh giá rác do Security Scanner / Bot Acunetix / Command Injection tạo ra.
     */
    public function up(): void
    {
        $connections = ['mysql', 'mysql_education', 'mysql_market', 'mysql_stay', 'mysql_wellness', 'mysql_culture'];

        foreach ($connections as $conn) {
            try {
                // 1. Quét & xóa tài khoản người dùng bot
                if (Schema::connection($conn)->hasTable('users')) {
                    $spamUsers = DB::connection($conn)->table('users')
                        ->where(function ($q) {
                            $q->whereRaw('LOWER(name) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(email) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%acunetix%'])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%sqlmap%']);
                        })
                        ->pluck('id');

                    if ($spamUsers->isNotEmpty()) {
                        if (Schema::connection($conn)->hasTable('comments')) {
                            DB::connection($conn)->table('comments')->whereIn('user_id', $spamUsers)->delete();
                        }
                        if (Schema::connection($conn)->hasTable('checkins')) {
                            DB::connection($conn)->table('checkins')->whereIn('user_id', $spamUsers)->delete();
                        }
                        if (Schema::connection($conn)->hasTable('posts')) {
                            DB::connection($conn)->table('posts')->whereIn('user_id', $spamUsers)->delete();
                        }
                        DB::connection($conn)->table('users')->whereIn('id', $spamUsers)->delete();
                    }
                }

                // 2. Quét & xóa bình luận rác theo nội dung và tên khách
                if (Schema::connection($conn)->hasTable('comments')) {
                    DB::connection($conn)->table('comments')
                        ->where(function ($q) {
                            $q->whereRaw('LOWER(guest_name) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(guest_name) LIKE ?', ['%acunetix%'])
                              ->orWhereRaw('LOWER(guest_name) LIKE ?', ['%sqlmap%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%echo %'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%zgnrlq%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%bosujf%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%tnazcm%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%hulhnr%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%xyu|%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%passwd%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%esi:include%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%bxss.me%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%rpb.png%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%sleep(%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%benchmark(%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%waitfor%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%redirtest%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%9999256%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%10000284%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%1be7d4csvy0%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%expr %'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%assert(%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%base64_decode%'])
                              ->orWhereRaw('LOWER(content) LIKE ?', ['%print(md5%'])
                              ->orWhereRaw('content LIKE ?', ['%..%'])
                              ->orWhereRaw('content LIKE ?', ['%${%'])
                              ->orWhereRaw('content LIKE ?', ['%#{%'])
                              ->orWhereRaw('content LIKE ?', ['%!(%'])
                              ->orWhereRaw('content LIKE ?', ['%^(%'])
                              ->orWhere('content', '"()')
                              ->orWhere('content', '\'"()')
                              ->orWhere('content', '\'"')
                              ->orWhere('content', '1')
                              ->orWhere('content', ')')
                              ->orWhere('content', '(')
                              ->orWhere('content', 'comments')
                              ->orWhere('content', 'comments/.')
                              ->orWhere('content', 'comments/');
                        })
                        ->delete();
                }

                // 3. Quét & xóa reviews rác
                if (Schema::connection($conn)->hasTable('reviews')) {
                    DB::connection($conn)->table('reviews')
                        ->where(function ($q) {
                            $q->whereRaw('LOWER(user_name) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(user_name) LIKE ?', ['%acunetix%'])
                              ->orWhereRaw('LOWER(user_name) LIKE ?', ['%sqlmap%'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%echo %'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%zgnrlq%'])
                              ->orWhereRaw('LOWER(comment) LIKE ?', ['%bosujf%'])
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
                // Tiếp tục quét các kết nối khác
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
