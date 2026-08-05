<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Eatery;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Lấy danh sách ID các trường học CHƯA được phân công tài khoản người dùng
        $unassignedSchoolIds = [];

        $connections = ['mysql_education', 'mysql'];

        foreach ($connections as $conn) {
            try {
                $schools = Eatery::on($conn)
                    ->whereHas('category', function($q) {
                        $q->where('slug', 'smart-education-map');
                    })
                    ->get();

                foreach ($schools as $sch) {
                    $hasUser = false;
                    if ($sch->user_id) {
                        $user = User::find($sch->user_id);
                        if ($user) {
                            $hasUser = true;
                        }
                    }
                    if (!$hasUser) {
                        $unassignedSchoolIds[] = $sch->id;
                    }
                }
            } catch (\Throwable $ex) {}
        }

        $unassignedSchoolIds = array_unique(array_filter($unassignedSchoolIds));

        if (!empty($unassignedSchoolIds)) {
            foreach ($connections as $conn) {
                try {
                    DB::connection($conn)
                        ->table('education_programs')
                        ->whereIn('eatery_id', $unassignedSchoolIds)
                        ->delete();

                    DB::connection($conn)
                        ->table('posts')
                        ->whereIn('eatery_id', $unassignedSchoolIds)
                        ->delete();
                } catch (\Throwable $ex) {}
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback needed for sample data cleanup
    }
};
