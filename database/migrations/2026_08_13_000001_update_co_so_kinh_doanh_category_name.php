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
            DB::table('categories')->where('slug', 'co-so-kinh-doanh')->update([
                'name' => 'Cơ sở kinh doanh, Doanh nghiệp',
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('categories')) {
            DB::table('categories')->where('slug', 'co-so-kinh-doanh')->update([
                'name' => 'Cơ sở kinh doanh',
                'updated_at' => now(),
            ]);
        }
    }
};
