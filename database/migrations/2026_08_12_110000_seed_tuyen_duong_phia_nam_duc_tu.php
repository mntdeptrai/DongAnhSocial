<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\DigitalRoute;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm Tuyến đường số 4.0: Tuyến Đường Phía Nam Dục Tú (Nhà ông Liên - Nhà ông Vũ)
        $routeKey = 'route-phia-nam-duc-tu';
        
        $routeData = [
            'route_key'    => $routeKey,
            'name'         => 'Tuyến 9: Tuyến Đường 4.0 Phía Nam Dục Tú',
            'village_key'  => 'duc-tu',
            'village_name' => 'Thôn Dục Tú',
            'length'       => '1.0km',
            'color'        => '#F43F5E',
            'anim_class'   => 'route-path-animated-1',
            'path_coords'  => json_encode([
                [21.1145, 105.8940],
                [21.1160, 105.8955],
                [21.1175, 105.8970],
                [21.1190, 105.8985]
            ]),
            'created_at'   => now(),
            'updated_at'   => now(),
        ];

        $existing = DB::table('digital_routes')->where('route_key', $routeKey)->first();
        if ($existing) {
            DB::table('digital_routes')->where('route_key', $routeKey)->update($routeData);
        } else {
            DB::table('digital_routes')->insert($routeData);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('digital_routes')->where('route_key', 'route-phia-nam-duc-tu')->delete();
    }
};
