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
        DB::table('eateries')
            ->where('id', 33)
            ->orWhere('slug', 'sieu-thi-lan-chi-dong-anh-t0bez')
            ->orWhere('name', 'like', '%Lan Chi%')
            ->update([
                'status' => 'inactive',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('eateries')
            ->where('id', 33)
            ->orWhere('slug', 'sieu-thi-lan-chi-dong-anh-t0bez')
            ->orWhere('name', 'like', '%Lan Chi%')
            ->update([
                'status' => 'active',
                'updated_at' => now(),
            ]);
    }
};
