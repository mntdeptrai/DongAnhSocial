<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connections = ['mysql', 'mysql_education', 'sqlite'];

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('live_streams')) {
                    if (!Schema::connection($conn)->hasColumn('live_streams', 'code')) {
                        Schema::connection($conn)->table('live_streams', function (Blueprint $table) {
                            $table->string('code', 32)->nullable()->unique()->after('id');
                        });
                    }

                    // Generate unique code for existing rows
                    $rows = DB::connection($conn)->table('live_streams')->whereNull('code')->orWhere('code', '')->get();
                    foreach ($rows as $row) {
                        $code = 'live-' . Str::lower(Str::random(8));
                        DB::connection($conn)->table('live_streams')->where('id', $row->id)->update(['code' => $code]);
                    }
                }
            } catch (\Throwable $ex) {
                // Ignore connection errors if table/db is not present
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql', 'mysql_education', 'sqlite'];

        foreach ($connections as $conn) {
            try {
                if (Schema::connection($conn)->hasTable('live_streams') && Schema::connection($conn)->hasColumn('live_streams', 'code')) {
                    Schema::connection($conn)->table('live_streams', function (Blueprint $table) {
                        $table->dropColumn('code');
                    });
                }
            } catch (\Throwable $ex) {}
        }
    }
};
