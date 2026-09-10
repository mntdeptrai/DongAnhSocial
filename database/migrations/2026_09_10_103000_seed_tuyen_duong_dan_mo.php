<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    private string $routeKey = 'route-dan-mo';

    public function up(): void
    {
        // ─── 1. Tuyến đường: Đường Đản Mỗ ───────────────────────────────────
        $routeData = [
            'route_key'    => $this->routeKey,
            'name'         => 'Tuyến Đường 4.0 Đường Đản Mỗ',
            'village_key'  => 'dan-mo',
            'village_name' => 'Đường Đản Mỗ',
            'length'       => '1.0km',
            'color'        => '#0EA5E9',
            'anim_class'   => 'route-path-animated-1',
            'path_coords'  => json_encode([
                [21.1352, 105.8458],
                [21.1360, 105.8470],
                [21.1368, 105.8482],
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

        // ─── 2. Danh sách 11 Hộ kinh doanh ─────────────────────────────────
        $businesses = [
            [
                'name'         => 'Nguyễn Khang',
                'type'         => 'tap-hoa',
                'hang'         => 'Tạp hóa',
                'phone'        => '0362021786',
                'bank_account' => '0362021786',
                'bank_name'    => 'VietinBank',
            ],
            [
                'name'         => 'Nguyễn Thị Bích Liên',
                'type'         => 'quan-an',
                'hang'         => 'Đồ ăn chín - thực phẩm',
                'phone'        => '0348887237',
                'bank_account' => '2141946114',
                'bank_name'    => 'BIDV',
            ],
            [
                'name'         => 'Trần Ngọc Lợi',
                'type'         => 'quan-an',
                'hang'         => 'Bia hơi Hà Nội',
                'phone'        => '0944386577',
                'bank_account' => '632308337',
                'bank_name'    => 'VPBank',
            ],
            [
                'name'         => 'Trần Ngọc Út',
                'type'         => 'tap-hoa',
                'hang'         => 'Tạp hóa',
                'phone'        => '0379645818',
                'bank_account' => '022145698',
                'bank_name'    => 'VietBank',
            ],
            [
                'name'         => 'Trần Thị Mai',
                'type'         => 'thuc-pham',
                'hang'         => 'Bán thịt lợn',
                'phone'        => '0368373274',
                'bank_account' => '0368373274',
                'bank_name'    => 'MB',
            ],
            [
                'name'         => 'Phan Thị Quỳnh',
                'type'         => 'thuc-pham',
                'hang'         => 'Bán rau',
                'phone'        => '0346338147',
                'bank_account' => '0345338147',
                'bank_name'    => 'MB',
            ],
            [
                'name'         => 'Phan Thị Kiều',
                'type'         => 'quan-an',
                'hang'         => 'Bán nước giải khát',
                'phone'        => '0363432638',
                'bank_account' => '0351000949321',
                'bank_name'    => 'Vietcombank',
            ],
            [
                'name'         => 'Nguyễn Thị Hiệp',
                'type'         => 'tap-hoa',
                'hang'         => 'Tạp hóa',
                'phone'        => '0979694985',
                'bank_account' => '9979694985',
                'bank_name'    => 'Techcombank',
            ],
            [
                'name'         => 'Phạm Thị Huế',
                'type'         => 'quan-an',
                'hang'         => 'Bán Bún',
                'phone'        => '0965462218',
                'bank_account' => '020098137999',
                'bank_name'    => 'Sacombank',
            ],
            [
                'name'         => 'Trần Ngọc Hiếu',
                'type'         => 'dich-vu',
                'hang'         => 'Cắt tóc, gội đầu',
                'phone'        => '0962413707',
                'bank_account' => '17019666666',
                'bank_name'    => 'Techcombank',
            ],
            [
                'name'         => 'Ngô Thị Hương',
                'type'         => 'tap-hoa',
                'hang'         => 'Tạp hóa',
                'phone'        => '0961711256',
                'bank_account' => '8848117441',
                'bank_name'    => 'BIDV',
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
                'quan-an'   => 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=600&q=80',
                'tap-hoa'   => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80',
                'thuc-pham' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=600&q=80',
                'thoi-trang'=> 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?auto=format&fit=crop&w=600&q=80',
                'y-te'      => 'https://images.unsplash.com/photo-1584308666744-24d5c474f2ae?auto=format&fit=crop&w=600&q=80',
                'dich-vu'   => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=600&q=80',
            ];

            $rbData = [
                'route_key'    => $this->routeKey,
                'name'         => $b['name'],
                'owner'        => $b['name'],
                'village_key'  => 'dan-mo',
                'village_name' => 'Đường Đản Mỗ',
                'type'         => $b['type'],
                'rating'       => 4.8,
                'address'      => 'Đường Đản Mỗ, Xã Đông Anh, Hà Nội',
                'phone'        => $phone,
                'bank_account' => $b['bank_account'],
                'bank_name'    => $b['bank_name'],
                'menu'         => json_encode([$b['hang']], JSON_UNESCAPED_UNICODE),
                'image_url'    => $typeImages[$b['type']] ?? 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?auto=format&fit=crop&w=600&q=80',
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
        $phones = [
            '0362021786','0348887237','0944386577','0379645818','0368373274',
            '0346338147','0363432638','0979694985','0965462218','0962413707','0961711256',
        ];

        DB::table('route_businesses')->where('route_key', $this->routeKey)->delete();
        DB::table('digital_routes')->where('route_key', $this->routeKey)->delete();
        // Không xóa users để tránh mất dữ liệu liên quan
    }
};
