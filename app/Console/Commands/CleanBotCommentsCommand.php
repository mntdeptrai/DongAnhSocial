<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanBotCommentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:bot-comments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quét sạch 100% tất cả các bình luận, đánh giá và tài khoản do bot / security scanner tạo ra trên tất cả các kết nối CSDL.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Đang bắt đầu dọn dẹp sạch toàn bộ bình luận & tài khoản bot / scanner...');

        $connections = ['mysql', 'mysql_education', 'mysql_market', 'mysql_stay', 'mysql_wellness', 'mysql_culture'];
        $totalDeletedComments = 0;
        $totalDeletedUsers = 0;
        $totalDeletedReviews = 0;

        foreach ($connections as $conn) {
            $this->line("--------------------------------------------------");
            $this->info("🔍 Đang kiểm tra kết nối: [{$conn}]");

            try {
                // 1. Quét & xóa tài khoản người dùng bot
                if (Schema::connection($conn)->hasTable('users')) {
                    $spamUsers = DB::connection($conn)->table('users')
                        ->where(function ($q) {
                            $q->whereRaw('LOWER(name) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(email) LIKE ?', ['%hfjnuiyz%'])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%acunetix%'])
                              ->orWhereRaw('LOWER(name) LIKE ?', ['%sqlmap%']);
                            if (Schema::connection($conn)->hasColumn('users', 'username')) {
                                $q->orWhereRaw('LOWER(username) LIKE ?', ['%hfjnuiyz%']);
                            }
                        })
                        ->pluck('id');

                    if ($spamUsers->isNotEmpty()) {
                        if (Schema::connection($conn)->hasTable('comments')) {
                            $delC = DB::connection($conn)->table('comments')->whereIn('user_id', $spamUsers)->delete();
                            $totalDeletedComments += $delC;
                            $this->warn("  👉 Đã xóa {$delC} bình luận gắn với các user bot trên [{$conn}].");
                        }
                        if (Schema::connection($conn)->hasTable('checkins')) {
                            DB::connection($conn)->table('checkins')->whereIn('user_id', $spamUsers)->delete();
                        }
                        if (Schema::connection($conn)->hasTable('posts')) {
                            DB::connection($conn)->table('posts')->whereIn('user_id', $spamUsers)->delete();
                        }
                        $delU = DB::connection($conn)->table('users')->whereIn('id', $spamUsers)->delete();
                        $totalDeletedUsers += $delU;
                        $this->warn("  👉 Đã xóa {$delU} tài khoản bot trên [{$conn}].");
                    }
                }

                // 2. Quét & xóa bình luận rác theo nội dung và tên khách
                if (Schema::connection($conn)->hasTable('comments')) {
                    $delComments = DB::connection($conn)->table('comments')
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
                              ->orWhere('content', '(');
                        })
                        ->delete();

                    $totalDeletedComments += $delComments;
                    if ($delComments > 0) {
                        $this->warn("  👉 Đã xóa {$delComments} bình luận rác bot/scanner trên [{$conn}].");
                    } else {
                        $this->info("  ✅ Không còn bình luận rác nào trên [{$conn}].");
                    }
                }

                // 3. Quét & xóa reviews rác
                if (Schema::connection($conn)->hasTable('reviews')) {
                    $delReviews = DB::connection($conn)->table('reviews')
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

                    $totalDeletedReviews += $delReviews;
                    if ($delReviews > 0) {
                        $this->warn("  👉 Đã xóa {$delReviews} đánh giá rác trên [{$conn}].");
                    }
                }
            } catch (\Throwable $e) {
                $this->error("  ⚠️ Lỗi khi quét [{$conn}]: " . $e->getMessage());
            }
        }

        $this->line("==================================================");
        $this->info("🎉 HOÀN TẤT DỌN DẸP!");
        $this->info("📊 Tổng số tài khoản bot đã xóa: {$totalDeletedUsers}");
        $this->info("📊 Tổng số bình luận bot đã xóa: {$totalDeletedComments}");
        $this->info("📊 Tổng số đánh giá rác đã xóa: {$totalDeletedReviews}");

        return Command::SUCCESS;
    }
}
