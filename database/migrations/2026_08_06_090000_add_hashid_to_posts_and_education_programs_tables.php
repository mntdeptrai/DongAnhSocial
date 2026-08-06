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
        $connections = ['mysql', 'mysql_education'];
        $tables = ['posts', 'education_programs', 'checkins'];

        foreach ($connections as $conn) {
            foreach ($tables as $tbl) {
                try {
                    if (Schema::connection($conn)->hasTable($tbl)) {
                        if (!Schema::connection($conn)->hasColumn($tbl, 'hashid')) {
                            Schema::connection($conn)->table($tbl, function (Blueprint $table) {
                                $table->string('hashid', 16)->nullable()->unique()->after('id');
                            });
                        }

                        // Generate unique hashid for existing rows
                        $rows = DB::connection($conn)->table($tbl)->whereNull('hashid')->orWhere('hashid', '')->get();
                        foreach ($rows as $row) {
                            $hashid = Str::lower(Str::random(10));
                            DB::connection($conn)->table($tbl)->where('id', $row->id)->update(['hashid' => $hashid]);
                        }
                    }
                } catch (\Throwable $ex) {
                    // Ignore connection errors if table/db is not present
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connections = ['mysql', 'mysql_education'];
        $tables = ['posts', 'education_programs', 'checkins'];

        foreach ($connections as $conn) {
            foreach ($tables as $tbl) {
                try {
                    if (Schema::connection($conn)->hasTable($tbl) && Schema::connection($conn)->hasColumn($tbl, 'hashid')) {
                        Schema::connection($conn)->table($tbl, function (Blueprint $table) {
                            $table->dropColumn('hashid');
                        });
                    }
                } catch (\Throwable $ex) {}
            }
        }
    }
};
