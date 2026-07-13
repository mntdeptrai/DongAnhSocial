<?php

namespace App\Console\Commands;

use App\Models\FoodTour;
use Illuminate\Console\Command;

class CleanupAITours extends Command
{
    /**
     * Lệnh xoá các bản nháp AI Tour cũ hơn 24 giờ mà chưa được user lưu.
     */
    protected $signature = 'ai:cleanup-tours {--hours=24 : Số giờ tối thiểu trước khi xoá}';

    protected $description = 'Tự động xoá các AI Tour ở trạng thái draft cũ hơn X giờ (mặc định 24h)';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $cutoff = now()->subHours($hours);

        $query = FoodTour::where('is_ai_generated', true)
            ->where('status', 'draft')
            ->where('created_at', '<', $cutoff);

        $count = $query->count();

        if ($count === 0) {
            $this->info("✅ Không có AI Tour nháp nào cần xoá.");
            return Command::SUCCESS;
        }

        // Xoá vĩnh viễn (CASCADE sẽ tự xoá food_tour_stops liên quan)
        $query->delete();

        $this->info("🗑️  Đã xoá {$count} AI Tour bản nháp (cũ hơn {$hours} giờ).");
        \Illuminate\Support\Facades\Log::info("CleanupAITours: Đã xoá {$count} AI draft tours cũ hơn {$hours}h.");

        return Command::SUCCESS;
    }
}
