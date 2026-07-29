<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];

        foreach ($connections as $conn) {
            try {
                $db = DB::connection($conn);
                if (!Schema::connection($conn)->hasTable('eateries')) {
                    continue;
                }

                $db->transaction(function() use ($db) {
                    $eateries = $db->table('eateries')
                        ->where('description', 'like', '%PA3%')
                        ->orWhere('description', 'like', '%pa3%')
                        ->get();

                    foreach ($eateries as $eat) {
                        $desc = $eat->description;
                        $newDesc = str_replace([
                            ' theo PA3:', 
                            ' theo PA3: ', 
                            ' theo PA3 ', 
                            ' theo PA3', 
                            'theo PA3:', 
                            'theo PA3: ', 
                            'theo PA3 ', 
                            'theo PA3',
                            ' theo pa3:', 
                            ' theo pa3: ', 
                            ' theo pa3 ', 
                            ' theo pa3', 
                            'theo pa3:', 
                            'theo pa3: ', 
                            'theo pa3 ', 
                            'theo pa3'
                        ], [
                            ':', 
                            ': ', 
                            ' ', 
                            '', 
                            ':', 
                            ': ', 
                            ' ', 
                            '',
                            ':', 
                            ': ', 
                            ' ', 
                            '', 
                            ':', 
                            ': ', 
                            ' ', 
                            ''
                        ], $desc);

                        if ($newDesc !== $desc) {
                            $db->table('eateries')
                                ->where('id', $eat->id)
                                ->update(['description' => $newDesc]);
                        }
                    }
                });
            } catch (\Throwable $e) {
                // Ignore connection errors if database/connection is not configured or available
            }
        }
    }

    public function down(): void
    {
        // No rollback needed for data cleanup
    }
};
