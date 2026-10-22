<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    private string $routeKey = 'route-co-loa';

    public function up(): void
    {
        // ─── 1. Tuyến đường: Đường Cổ Loa ───────────────────────────────────
        $routeData = [
            'route_key'    => $this->routeKey,
            'name'         => 'Tuyến Đường 4.0 Đường Cổ Loa',
            'village_key'  => 'co-loa',
            'village_name' => 'Đường Cổ Loa',
            'length'       => '2.0km',
            'color'        => '#7C3AED',
            'anim_class'   => 'route-path-animated-2',
            'path_coords'  => json_encode([
                [21.1249, 105.8793],
                [21.1260, 105.8810],
                [21.1271, 105.8828],
                [21.1282, 105.8845],
                [21.1293, 105.8862],
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

        // ─── 2. Danh sách 39 Hộ kinh doanh ──────────────────────────────────
        $businesses = [
            ['name' => 'Nguyễn Hồng Nguyên',       'type' => 'thuc-pham', 'hang' => 'Hoa quả',                'phone' => '0915459785', 'bank_account' => '1008035887',     'bank_name' => 'VietinBank'],
            ['name' => 'Nguyễn Quang Việt',         'type' => 'thuc-pham', 'hang' => 'Kem',                    'phone' => '0967489880', 'bank_account' => '0380553576',     'bank_name' => 'BIDV'],
            ['name' => 'Nguyễn Tiến Hưng',          'type' => 'tap-hoa',   'hang' => 'Tạp hóa',               'phone' => '0969739166', 'bank_account' => '2141705458',     'bank_name' => 'BIDV'],
            ['name' => 'Nguyễn Thị Tâm',            'type' => 'tap-hoa',   'hang' => 'Sành sứ',               'phone' => '0338063868', 'bank_account' => '18263688688',    'bank_name' => 'TPBank'],
            ['name' => 'Đào Duy Luật',               'type' => 'dich-vu',   'hang' => 'Sửa chữa điện thoại',  'phone' => '0904051108', 'bank_account' => '0904051108',     'bank_name' => 'MB'],
            ['name' => 'Lê Thị Mùi',                'type' => 'y-te',      'hang' => 'Thuốc tây',             'phone' => '0339586838', 'bank_account' => '0339586838',     'bank_name' => 'MB'],
            ['name' => 'Nguyễn Thị Hồng',           'type' => 'thoi-trang','hang' => 'Đồ thể thao',           'phone' => '0912031979', 'bank_account' => '3140235000762',  'bank_name' => 'Agribank'],
            ['name' => 'Đào Thị Dung',               'type' => 'tap-hoa',   'hang' => 'Tạp hóa',               'phone' => '0972512650', 'bank_account' => '060410115811',   'bank_name' => 'VPBank'],
            ['name' => 'Nguyễn Thị Thanh Hương',    'type' => 'y-te',      'hang' => 'Thuốc tây',             'phone' => '0934577386', 'bank_account' => '1062703343',     'bank_name' => 'Vietcombank'],
            ['name' => 'Nguyễn Thị Bẩy',            'type' => 'y-te',      'hang' => 'Thuốc đông y',          'phone' => '0368736641', 'bank_account' => '2610213000000009','bank_name' => 'MoMo'],
            ['name' => 'Tái Đại Diệp',               'type' => 'y-te',      'hang' => 'Thuốc đông y',          'phone' => '0987199569', 'bank_account' => '10883868',        'bank_name' => 'VietinBank'],
            ['name' => 'Nguyễn Thị Bích Phương',    'type' => 'tap-hoa',   'hang' => 'Tạp hóa',               'phone' => '0984985016', 'bank_account' => '27211999',        'bank_name' => 'MB'],
            ['name' => 'Nguyễn Huỳnh Đức',          'type' => 'dich-vu',   'hang' => 'Sửa chữa điện thoại',  'phone' => '0856383333', 'bank_account' => '00000000755684', 'bank_name' => 'PCB'],
            ['name' => 'Lại Trung Kiên',             'type' => 'quan-an',   'hang' => 'Trà chanh',             'phone' => '0899493333', 'bank_account' => '2207098878',     'bank_name' => 'BIDV'],
            ['name' => 'Nguyễn Thị Hương Chà',      'type' => 'thuc-pham', 'hang' => 'Hoa tươi',              'phone' => '0974335560', 'bank_account' => '0974335560',     'bank_name' => 'MB'],
            ['name' => 'Hoàng Văn Tiến',             'type' => 'dich-vu',   'hang' => 'Kim khí',               'phone' => '0988545391', 'bank_account' => '1915869124728',  'bank_name' => 'MB'],
            ['name' => 'Nguyễn Thị Thanh Hằng',     'type' => 'tap-hoa',   'hang' => 'Đồ lưu niệm',           'phone' => '0963989985', 'bank_account' => '4519756868',     'bank_name' => 'Techcombank'],
            ['name' => 'Nguyễn Thị Mai Hoa',        'type' => 'quan-an',   'hang' => 'Bia hơi',               'phone' => '0961553672', 'bank_account' => '202310664',      'bank_name' => 'VPBank'],
            ['name' => 'Vũ Văn Vương',               'type' => 'quan-an',   'hang' => 'Hàng cơm',              'phone' => '0353286528', 'bank_account' => '0353286528',     'bank_name' => 'MB'],
            ['name' => 'Nguyễn Mạnh Dũng',          'type' => 'dich-vu',   'hang' => 'Cắt tóc',               'phone' => '0961916634', 'bank_account' => '576141017',      'bank_name' => 'VPBank'],
            ['name' => 'Châu Ngọc Hải',              'type' => 'dich-vu',   'hang' => 'Photocopi',             'phone' => '0989149275', 'bank_account' => '07417446364163', 'bank_name' => 'CCB'],
            ['name' => 'Châu Ngọc Minh',             'type' => 'dich-vu',   'hang' => 'Rửa xe',                'phone' => '0987715122', 'bank_account' => '2602416700000019','bank_name' => 'MoMo'],
            ['name' => 'Lê Thị Lý',                  'type' => 'tap-hoa',   'hang' => 'Bỉm sữa',               'phone' => '0976898765', 'bank_account' => '14371746666',    'bank_name' => 'Vietcombank'],
            ['name' => 'Nguyễn Tiến Quang',         'type' => 'dich-vu',   'hang' => 'Nhựa Tiền Phong',       'phone' => '0973101277', 'bank_account' => '21410000355638', 'bank_name' => 'BIDV'],
            ['name' => 'Trần Thị Hoa',               'type' => 'quan-an',   'hang' => 'Vịt quay',              'phone' => '0977542791', 'bank_account' => '0977542791',     'bank_name' => 'MB'],
            ['name' => 'Nguyễn Thị Thư',             'type' => 'dich-vu',   'hang' => 'Làm đẹp',               'phone' => '0339551684', 'bank_account' => '0801000388319',  'bank_name' => 'Agribank'],
            ['name' => 'Vũ Thị Huyền Trang',        'type' => 'y-te',      'hang' => 'Thuốc tây',             'phone' => '0978760687', 'bank_account' => '888800533686',   'bank_name' => 'VietinBank'],
            ['name' => 'Nguyễn Thị Đoàn',           'type' => 'tap-hoa',   'hang' => 'Tạp hóa',               'phone' => '0976960003', 'bank_account' => '19034552431017', 'bank_name' => 'Techcombank'],
            ['name' => 'Vũ Văn Tươi',                'type' => 'tap-hoa',   'hang' => 'Tạp hóa',               'phone' => '0978028450', 'bank_account' => '106876875469',   'bank_name' => 'VietinBank'],
            ['name' => 'Nguyễn Thị Hậu',            'type' => 'quan-an',   'hang' => 'Vịt quay',              'phone' => '0975854089', 'bank_account' => '1903255567',     'bank_name' => 'BIDV'],
            ['name' => 'Ngô Thị Ngọc',               'type' => 'dich-vu',   'hang' => 'Vật liệu xây dựng',    'phone' => '0852672222', 'bank_account' => '1903241634019',  'bank_name' => 'Techcombank'],
            ['name' => 'Đỗ Huy Tùng',                'type' => 'dich-vu',   'hang' => 'Kim khí',               'phone' => '0973132919', 'bank_account' => '8440011324435',  'bank_name' => 'MB'],
            ['name' => 'Vũ Văn Hoàng',               'type' => 'quan-an',   'hang' => 'Bánh mỳ',               'phone' => '0973513226', 'bank_account' => '0369270707',     'bank_name' => 'MB'],
            ['name' => 'Nguyễn Thị Hoàng Oanh',     'type' => 'dich-vu',   'hang' => 'Phun xăm',              'phone' => '0989029077', 'bank_account' => '1903241634019',  'bank_name' => 'Techcombank'],
            ['name' => 'Lê Thu Thủy',                'type' => 'tap-hoa',   'hang' => 'Tạp hóa',               'phone' => '0387016638', 'bank_account' => '1903241634019',  'bank_name' => 'Techcombank'],
            ['name' => 'Lê Thanh Thái',              'type' => 'dich-vu',   'hang' => 'Cắt tóc',               'phone' => '0384978117', 'bank_account' => '71783842004',    'bank_name' => 'LPBank'],
            ['name' => 'Lê Thị Nguyệt',              'type' => 'y-te',      'hang' => 'Thuốc tây',             'phone' => '0966927380', 'bank_account' => '00000000871919', 'bank_name' => 'Techcombank'],
            ['name' => 'Đào Thị Hồng Vân',          'type' => 'quan-an',   'hang' => 'Bánh cuốn',             'phone' => '0329991973', 'bank_account' => '3140205124193',  'bank_name' => 'Agribank'],
            ['name' => 'Nguyễn Anh Dũng',           'type' => 'dich-vu',   'hang' => 'Rửa xe',                'phone' => '0394594251', 'bank_account' => '2615414800000063','bank_name' => 'MoMo'],
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
                'village_key'  => 'co-loa',
                'village_name' => 'Đường Cổ Loa',
                'type'         => $b['type'],
                'rating'       => 4.8,
                'address'      => 'Đường Cổ Loa, Xã Cổ Loa, Huyện Đông Anh, Hà Nội',
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
            '0915459785','0967489880','0969739166','0338063868','0904051108',
            '0339586838','0912031979','0972512650','0934577386','0368736641',
            '0987199569','0984985016','0856383333','0899493333','0974335560',
            '0988545391','0963989985','0961553672','0353286528','0961916634',
            '0989149275','0987715122','0976898765','0973101277','0977542791',
            '0339551684','0978760687','0976960003','0978028450','0975854089',
            '0852672222','0973132919','0973513226','0989029077','0387016638',
            '0384978117','0966927380','0329991973','0394594251',
        ];

        DB::table('route_businesses')->where('route_key', $this->routeKey)->delete();
        DB::table('digital_routes')->where('route_key', $this->routeKey)->delete();
        // Không xóa users để tránh mất dữ liệu liên quan
    }
};
