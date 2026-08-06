<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connections = ['mysql', 'mysql_education'];
        $imagePaths = [
            'https://media.xadonganh.com/checkins/1783421404_fL8vKZS7.jpg',
            'https://media.xadonganh.com/checkins/1784012829_DBUBnXj3.jpg'
        ];

        foreach ($connections as $conn) {
            try {
                // Xóa reactions liên quan đến 2 checkins này
                $checkinIds = DB::connection($conn)
                    ->table('checkins')
                    ->whereIn('id', [1, 2])
                    ->orWhereIn('image_path', $imagePaths)
                    ->pluck('id')
                    ->toArray();

                if (!empty($checkinIds)) {
                    DB::connection($conn)
                        ->table('checkin_reactions')
                        ->where('reactionable_type', 'checkin')
                        ->whereIn('reactionable_id', $checkinIds)
                        ->delete();

                    DB::connection($conn)
                        ->table('comments')
                        ->whereIn('commentable_type', ['checkin', 'App\\Models\\Checkin'])
                        ->whereIn('commentable_id', $checkinIds)
                        ->delete();

                    DB::connection($conn)
                        ->table('checkins')
                        ->whereIn('id', $checkinIds)
                        ->delete();
                }
            } catch (\Throwable $ex) {
                // Bỏ qua nếu bảng không tồn tại trên kết nối
            }
        }

        // Xóa file thực tế trên Cloudflare R2 storage và local fallback
        foreach ($imagePaths as $url) {
            $relativePath = ltrim(parse_url($url, PHP_URL_PATH), '/'); // ví dụ: checkins/1783421404_fL8vKZS7.jpg
            if ($relativePath) {
                try {
                    if (\Illuminate\Support\Facades\Storage::disk('r2')->exists($relativePath)) {
                        \Illuminate\Support\Facades\Storage::disk('r2')->delete($relativePath);
                    }
                } catch (\Throwable $ex) {
                    \Illuminate\Support\Facades\Log::warning('[Migration] Could not delete R2 file: ' . $relativePath . ' - ' . $ex->getMessage());
                }

                // Kiểm tra và xóa thêm nếu có ở local storage
                $localPath = public_path('uploads/' . $relativePath);
                if (file_exists($localPath)) {
                    @unlink($localPath);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
