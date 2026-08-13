<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('categories')) {
            DB::table('categories')->updateOrInsert(
                ['slug' => 'co-so-kinh-doanh'],
                [
                    'name'        => 'Cơ sở kinh doanh, Doanh nghiệp',
                    'icon'        => '🏪',
                    'description' => 'Cơ sở kinh doanh độc lập, cửa hàng, siêu thị mini, doanh nghiệp và dịch vụ bán lẻ trên địa bàn.',
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('categories')) {
            DB::table('categories')->where('slug', 'co-so-kinh-doanh')->delete();
        }
    }
};
