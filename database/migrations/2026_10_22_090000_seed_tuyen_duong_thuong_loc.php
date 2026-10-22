<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    private string $routeKey = 'route-thuong-loc';

    public function up(): void
    {
        // ─── 1. Tuyến đường: Tuyến Đường Thượng Lộc ──────────────────────────
        $routeData = [
            'route_key'    => $this->routeKey,
            'name'         => 'Tuyến Đường 4.0 Tuyến Thượng Lộc',
            'village_key'  => 'thuong-loc',
            'village_name' => 'Đường Thượng Lộc',
            'length'       => '1.2km',
            'color'        => '#D97706',
            'anim_class'   => 'route-path-animated-3',
            'path_coords'  => json_encode([
                [21.1145, 105.8545],
                [21.1130, 105.8535],
                [21.1115, 105.8525],
                [21.1095, 105.8515],
            ]),
            'created_at'   => now(),
            'updated_at'   => now(),
        ];

        $existing = DB::table('digital_routes')->where('route_key', $this->routeKey)->first();
        if ($existing) {
            DB::table('digital_routes')->where('route_key', $this->routeKey)->update($routeData);
        } else {
            DB::table('digital_routes')->insert($routeData);
        }

        // ─── 2. Danh sách 11 Hộ kinh doanh Tuyến Thượng Lộc ──────────────────
        $businesses = [
            [
                'name'         => 'Đặng Thị Kim Loan',
                'type'         => 'thuc-pham',
                'hang'         => 'Bán thịt lợn',
                'phone'        => '0325701681',
                'bank_account' => '3352298082',
                'bank_name'    => 'Techcombank',
                'origin'       => 'Mổ lợn Trong Dân',
                'lat'          => 21.1145,
                'lng'          => 105.8545,
            ],
            [
                'name'         => 'Nguyễn Thị Băng',
                'type'         => 'thuc-pham',
                'hang'         => 'Bán giò chả',
                'phone'        => '0385769442',
                'bank_account' => '3385769442',
                'bank_name'    => 'Techcombank',
                'origin'       => null,
                'lat'          => 21.1140,
                'lng'          => 105.8542,
            ],
            [
                'name'         => 'Hoàng Thị Biên',
                'type'         => 'quan-an',
                'hang'         => 'Bán xôi sáng',
                'phone'        => '0902359956',
                'bank_account' => '3399288268',
                'bank_name'    => 'Techcombank',
                'origin'       => null,
                'lat'          => 21.1135,
                'lng'          => 105.8539,
            ],
            [
                'name'         => 'Đào Thị Chỉ',
                'type'         => 'thuc-pham',
                'hang'         => 'Bán rau',
                'phone'        => '0339284889',
                'bank_account' => '0979967902',
                'bank_name'    => 'MB Bank',
                'origin'       => 'Chợ Vân Trì',
                'lat'          => 21.1130,
                'lng'          => 105.8535,
            ],
            [
                'name'         => 'Hoàng Thị Kết',
                'type'         => 'tap-hoa',
                'hang'         => 'Bán tạp hóa',
                'phone'        => '0962943111',
                'bank_account' => '2519558888',
                'bank_name'    => 'Techcombank',
                'origin'       => null,
                'lat'          => 21.1125,
                'lng'          => 105.8532,
            ],
            [
                'name'         => 'Nguyễn Thị Liễu',
                'type'         => 'tap-hoa',
                'hang'         => 'Bán tạp hóa',
                'phone'        => '0972853460',
                'bank_account' => '2219561956',
                'bank_name'    => 'Techcombank',
                'origin'       => null,
                'lat'          => 21.1120,
                'lng'          => 105.8529,
            ],
            [
                'name'         => 'Nguyễn Thị Dư',
                'type'         => 'tap-hoa',
                'hang'         => 'Bán tạp hóa',
                'phone'        => '0374458281',
                'bank_account' => '10522752453017',
                'bank_name'    => 'Techcombank',
                'origin'       => null,
                'lat'          => 21.1115,
                'lng'          => 105.8525,
            ],
            [
                'name'         => 'Đặng Thị Ánh',
                'type'         => 'thoi-trang',
                'hang'         => 'Bán đồ lót',
                'phone'        => '0983644696',
                'bank_account' => '100197375',
                'bank_name'    => 'Techcombank',
                'origin'       => 'Chợ Ninh Hiệp',
                'lat'          => 21.1110,
                'lng'          => 105.8522,
            ],
            [
                'name'         => 'Nguyễn Thị Thu Mai',
                'type'         => 'y-te',
                'hang'         => 'Bán thuốc tây',
                'phone'        => '0393855525',
                'bank_account' => '0976466420',
                'bank_name'    => 'VPBank',
                'origin'       => null,
                'lat'          => 21.1105,
                'lng'          => 105.8519,
            ],
            [
                'name'         => 'Lê Thị Lan Hương',
                'type'         => 'quan-an',
                'hang'         => 'Bán xôi sáng',
                'phone'        => '0386824848',
                'bank_account' => '3386824848',
                'bank_name'    => 'Techcombank',
                'origin'       => null,
                'lat'          => 21.1100,
                'lng'          => 105.8517,
            ],
            [
                'name'         => 'Đồng Thị Vinh',
                'type'         => 'thuc-pham',
                'hang'         => 'Bán giò chả',
                'phone'        => '0934253425',
                'bank_account' => '9934253425',
                'bank_name'    => 'Vietcombank',
                'origin'       => null,
                'lat'          => 21.1095,
                'lng'          => 105.8515,
            ],
        ];

        $defaultPassword = Hash::make('12345678');

        foreach ($businesses as $b) {
            $phone = $b['phone'];

            // ── Tạo hoặc lấy User ──────────────────────────────────────────
            $user = DB::table('users')->where('phone', $phone)->first();
            if (!$user) {
                $userId = DB::table('users')->insertGetId([
                    'name'        => $b['name'],
                    'username'    => $phone,
                    'email'       => null,
                    'phone'       => $phone,
                    'password'    => $defaultPassword,
                    'role'        => 'seller',
                    'status'      => 'active',
                    'is_verified' => true,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            } else {
                $userId = $user->id;
            }

            // ── Tạo hoặc cập nhật RouteBusiness ───────────────────────────
            $rb = DB::table('route_businesses')->where('phone', $phone)->first();
            $typeImages = [
                'quan-an'    => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=600&q=80',
                'tap-hoa'    => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80',
                'thuc-pham'  => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80',
                'thoi-trang' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=600&q=80',
                'y-te'       => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=600&q=80',
                'dich-vu'    => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=600&q=80',
            ];

            $rbData = [
                'route_key'    => $this->routeKey,
                'name'         => $b['name'],
                'owner'        => $b['name'],
                'village_key'  => 'thuong-loc',
                'village_name' => 'Đường Thượng Lộc',
                'type'         => $b['type'],
                'rating'       => 4.8,
                'address'      => 'Đường Thượng Lộc, Thôn Thượng Lộc, Huyện Đông Anh, Hà Nội',
                'phone'        => $phone,
                'bank_account' => $b['bank_account'],
                'bank_name'    => $b['bank_name'],
                'menu'         => json_encode([$b['hang']], JSON_UNESCAPED_UNICODE),
                'image_url'    => $typeImages[$b['type']] ?? 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80',
                'lat'          => $b['lat'],
                'lng'          => $b['lng'],
                'is_open'      => true,
                'user_id'      => $userId,
                'updated_at'   => now(),
            ];

            if ($rb) {
                DB::table('route_businesses')->where('id', $rb->id)->update($rbData);
            } else {
                DB::table('route_businesses')->insert(array_merge($rbData, ['created_at' => now()]));
            }
        }
    }

    public function down(): void
    {
        DB::table('route_businesses')->where('route_key', $this->routeKey)->delete();
        DB::table('digital_routes')->where('route_key', $this->routeKey)->delete();
        // Không xóa users để tránh mất dữ liệu liên quan
    }
};
