<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\DigitalRoute;
use App\Models\RouteBusiness;
use App\Models\Eatery;
use App\Models\OcopProduct;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // =========================================================================
        // PHẦN 1: TUYẾN ĐƯỜNG 4.0 XUÂN CANH (Nạp vào bảng digital_routes & route_businesses)
        // =========================================================================
        $route = DigitalRoute::updateOrCreate(
            ['route_key' => 'route-xuan-canh'],
            [
                'name'         => 'Tuyến 6: Tuyến Đường 4.0 Xuân Canh',
                'village_key'  => 'xuan-canh',
                'village_name' => 'Thôn Xuân Canh',
                'length'       => '1.5km',
                'color'        => '#8B5CF6',
                'anim_class'   => 'route-path-animated-3',
                'path_coords'  => [
                    [21.0850, 105.8600],
                    [21.0862, 105.8615],
                    [21.0871, 105.8628],
                    [21.0880, 105.8640]
                ]
            ]
        );

        $routeBusinesses = [
            [
                'route_key' => 'route-xuan-canh',
                'name' => 'Cửa Hàng Tạp Hóa Lê Thị Diệp',
                'owner' => 'Lê Thị Diệp',
                'village_key' => 'xuan-canh',
                'village_name' => 'Thôn Xuân Canh',
                'type' => 'tap-hoa',
                'rating' => 4.8,
                'address' => 'Đường Xuân Canh, Thôn Xuân Canh, Đông Anh, Hà Nội',
                'phone' => '0388345695',
                'bank_account' => '19038393052013',
                'bank_name' => 'Techcombank',
                'is_open' => true,
                'menu' => ['Tạp hóa tổng hợp gia đình', 'Nước giải khát bánh kẹo'],
                'image_url' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=420&h=230&fit=crop&auto=format',
                'lat' => 21.0850,
                'lng' => 105.8600
            ],
            [
                'route_key' => 'route-xuan-canh',
                'name' => 'Tạp Hóa Trương Hữu Sơn',
                'owner' => 'Trương Hữu Sơn',
                'village_key' => 'xuan-canh',
                'village_name' => 'Thôn Xuân Canh',
                'type' => 'tap-hoa',
                'rating' => 4.7,
                'address' => 'Đường Xuân Canh, Thôn Xuân Canh, Đông Anh, Hà Nội',
                'phone' => '0983467066',
                'bank_account' => '21410002728254',
                'bank_name' => 'BIDV',
                'is_open' => true,
                'menu' => ['Tạp hóa gia đình', 'Gia vị & đồ khô'],
                'image_url' => 'https://images.unsplash.com/photo-1542838132-92c53300491e?w=420&h=230&fit=crop&auto=format',
                'lat' => 21.0858,
                'lng' => 105.8610
            ],
            [
                'route_key' => 'route-xuan-canh',
                'name' => 'Tạp Hóa Nguyễn Thị Xuân',
                'owner' => 'Nguyễn Thị Xuân',
                'village_key' => 'xuan-canh',
                'village_name' => 'Thôn Xuân Canh',
                'type' => 'tap-hoa',
                'rating' => 4.6,
                'address' => 'Đường Xuân Canh, Thôn Xuân Canh, Đông Anh, Hà Nội',
                'phone' => '0384545761',
                'bank_account' => '0964761655',
                'bank_name' => 'MB Bank',
                'is_open' => true,
                'menu' => ['Nước giải khát bánh kẹo', 'Vật dụng thiết yếu'],
                'image_url' => 'https://images.unsplash.com/photo-1578916171728-46686eac8d58?w=420&h=230&fit=crop&auto=format',
                'lat' => 21.0865,
                'lng' => 105.8620
            ],
            [
                'route_key' => 'route-xuan-canh',
                'name' => 'Cửa Hàng Cắt Tóc Hải Yến',
                'owner' => 'Nguyễn Thị Hải Yến',
                'village_key' => 'xuan-canh',
                'village_name' => 'Thôn Xuân Canh',
                'type' => 'dich-vu',
                'rating' => 4.9,
                'address' => 'Đường Xuân Canh, Thôn Xuân Canh, Đông Anh, Hà Nội',
                'phone' => '0972353250',
                'bank_account' => '0972353250',
                'bank_name' => 'MB Bank',
                'is_open' => true,
                'menu' => ['Cắt tóc nam nữ', 'Uốn gội nhuộm tạo kiểu'],
                'image_url' => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?w=420&h=230&fit=crop&auto=format',
                'lat' => 21.0872,
                'lng' => 105.8630
            ],
            [
                'route_key' => 'route-xuan-canh',
                'name' => 'Cửa Hàng Đồ Mã Mai Thị Huyền',
                'owner' => 'Mai Thị Huyền',
                'village_key' => 'xuan-canh',
                'village_name' => 'Thôn Xuân Canh',
                'type' => 'thoi-trang',
                'rating' => 4.7,
                'address' => 'Đường Xuân Canh, Thôn Xuân Canh, Đông Anh, Hà Nội',
                'phone' => '0343275668',
                'bank_account' => '0973602622',
                'bank_name' => 'VPBank',
                'is_open' => true,
                'menu' => ['Đồ mã lễ tết', 'Vàng mã hương trầm'],
                'image_url' => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=420&h=230&fit=crop&auto=format',
                'lat' => 21.0880,
                'lng' => 105.8640
            ],
        ];

        foreach ($routeBusinesses as $b) {
            $user = null;
            if (!empty($b['owner'])) {
                $emailSlug = Str::slug($b['owner']);
                $phoneVal = !empty($b['phone']) ? $b['phone'] : null;

                if ($phoneVal) {
                    $user = User::where('phone', $phoneVal)->first();
                }

                if (!$user) {
                    $email = $emailSlug . ($phoneVal ? '.' . $phoneVal : '.' . rand(1000, 9999)) . '@donganhsocial.vn';
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name'         => $b['owner'],
                            'username'     => $emailSlug . ($phoneVal ? '_' . $phoneVal : '_' . rand(1000, 9999)),
                            'email'        => $email,
                            'phone'        => $phoneVal,
                            'password'     => Hash::make('12345678'),
                            'role'         => 'seller',
                            'status'       => 'active',
                            'bank_account' => $b['bank_account'] ?? null,
                            'bank_name'    => $b['bank_name'] ?? null,
                        ]
                    );
                }
                $b['user_id'] = $user->id;
            }

            RouteBusiness::updateOrCreate(
                ['route_key' => $b['route_key'], 'name' => $b['name']],
                $b
            );
        }

        // Xóa tuyến đường chợ nếu lỡ tạo lầm trong bảng tuyến đường 4.0
        DigitalRoute::whereIn('route_key', ['route-cho-duc-noi', 'route-cho-xuan-canh'])->delete();
        RouteBusiness::whereIn('route_key', ['route-cho-duc-noi', 'route-cho-xuan-canh'])->delete();

        // =========================================================================
        // PHẦN 2: CHỢ DÂN SINH XUÂN CANH (Nạp gian hàng vào bảng eateries & ocop_products)
        // =========================================================================
        $marketEatery = Eatery::where('name', 'like', '%Chợ Xuân Canh%')->first();
        if (!$marketEatery) {
            $marketEatery = Eatery::create([
                'name'          => 'Chợ Dân Sinh Xuân Canh',
                'slug'          => 'cho-dan-sinh-xuan-canh',
                'address'       => 'Xã Xuân Canh, Đông Anh, Hà Nội',
                'status'        => 'active',
                'is_featured'   => true,
                'rating'        => 4.8,
                'latitude'      => 21.0855,
                'longitude'     => 105.8605,
            ]);
        }

        $stalls = [
            [
                'stall_name'   => 'Gian Hàng Hoa Quả Đào Thị Huệ',
                'seller_name'  => 'Đào Thị Huệ',
                'seller_phone' => '',
                'name'         => 'Hoa quả tươi vườn nhà & Chợ đầu mối',
                'price'        => 50000,
                'unit'         => 'kg',
                'description'  => 'Vừa sản xuất từ vườn nhà vừa nhập từ chợ đầu mối. STK Techcombank: 7619766888',
                'image_path'   => 'https://images.unsplash.com/photo-1619566636858-adf3ef46400b?w=420&h=230&fit=crop&auto=format',
                'star_rating'  => 5,
                'bank_account' => '7619766888',
                'bank_name'    => 'Techcombank',
            ],
            [
                'stall_name'   => 'Gian Hàng Hàng Mã Mai Thị Huyền',
                'seller_name'  => 'Mai Thị Huyền',
                'seller_phone' => '0343275668',
                'name'         => 'Hàng mã gia đình tự làm & xưởng sản xuất',
                'price'        => 30000,
                'unit'         => 'bộ',
                'description'  => 'Vừa tự làm tại nhà và vừa nhập từ xưởng sản xuất. STK VPBank: 0973602622',
                'image_path'   => 'https://images.unsplash.com/photo-1526170375885-4d8ecf77b99f?w=420&h=230&fit=crop&auto=format',
                'star_rating'  => 5,
                'bank_account' => '0973602622',
                'bank_name'    => 'VPBank',
            ],
            [
                'stall_name'   => 'Gian Hàng Bánh Cuốn Nguyễn Thị Hà',
                'seller_name'  => 'Nguyễn Thị Hà',
                'seller_phone' => '',
                'name'         => 'Bánh cuốn tráng nóng tại chỗ',
                'price'        => 25000,
                'unit'         => 'đĩa',
                'description'  => 'Bánh cuốn tráng nóng tại chợ. STK Techcombank: 19051143579012',
                'image_path'   => 'https://images.unsplash.com/photo-1597345637412-9fd611e758f3?w=420&h=230&fit=crop&auto=format',
                'star_rating'  => 5,
                'bank_account' => '19051143579012',
                'bank_name'    => 'Techcombank',
            ],
            [
                'stall_name'   => 'Gian Hàng Xôi Cháo Vũ Thị Tình',
                'seller_name'  => 'Vũ Thị Tình',
                'seller_phone' => '',
                'name'         => 'Xôi xéo, xôi ngô, cháo sườn',
                'price'        => 20000,
                'unit'         => 'bát',
                'description'  => 'Nấu ăn tại chỗ nóng hổi. STK MB Bank: 2101019686666',
                'image_path'   => 'https://images.unsplash.com/photo-1597345637412-9fd611e758f3?w=420&h=230&fit=crop&auto=format',
                'star_rating'  => 5,
                'bank_account' => '2101019686666',
                'bank_name'    => 'MB Bank',
            ],
            [
                'stall_name'   => 'Gian Hàng Thịt Lợn Đào Duy Hữu',
                'seller_name'  => 'Đào Duy Hữu',
                'seller_phone' => '0395328938',
                'name'         => 'Thịt lợn tươi sạch ba chỉ suôn non',
                'price'        => 120000,
                'unit'         => 'kg',
                'description'  => 'Mua của nhân dân và nhập từ lò xưởng sạch. STK MB Bank: 0395328938',
                'image_path'   => 'https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=420&h=230&fit=crop&auto=format',
                'star_rating'  => 5,
                'bank_account' => '0395328938',
                'bank_name'    => 'MB Bank',
            ],
            [
                'stall_name'   => 'Gian Hàng Thịt Lợn Gà Lê Thị Phương',
                'seller_name'  => 'Lê Thị Phương',
                'seller_phone' => '0399224059',
                'name'         => 'Thịt lợn & Gà ta tươi ngon làm sạch',
                'price'        => 130000,
                'unit'         => 'kg',
                'description'  => 'Mua nhập hàng từ xưởng lò mổ an toàn. STK Techcombank: 19035708243018',
                'image_path'   => 'https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=420&h=230&fit=crop&auto=format',
                'star_rating'  => 5,
                'bank_account' => '19035708243018',
                'bank_name'    => 'Techcombank',
            ],
            [
                'stall_name'   => 'Gian Hàng Thịt Lợn Nguyễn Thị Ngọc Anh',
                'seller_name'  => 'Nguyễn Thị Ngọc Anh',
                'seller_phone' => '0328670432',
                'name'         => 'Thịt lợn tươi nguyên con mỡ lá thịt nạc',
                'price'        => 125000,
                'unit'         => 'kg',
                'description'  => 'Nhập hàng trực tiếp từ lò mổ. STK Agribank: 3140205081553',
                'image_path'   => 'https://images.unsplash.com/photo-1604719312566-8912e9227c6a?w=420&h=230&fit=crop&auto=format',
                'star_rating'  => 5,
                'bank_account' => '3140205081553',
                'bank_name'    => 'Agribank',
            ],
            [
                'stall_name'   => 'Gian Hàng Hải Sản Nguyễn Thị Thoan',
                'seller_name'  => 'Nguyễn Thị Thoan',
                'seller_phone' => '0382299688',
                'name'         => 'Hải sản tôm cua ốc cua đồng tươi sống',
                'price'        => 150000,
                'unit'         => 'kg',
                'description'  => 'Nhập hàng từ chợ đầu mối hải sản tươi. STK MB Bank: 00830038888',
                'image_path'   => 'https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=420&h=230&fit=crop&auto=format',
                'star_rating'  => 5,
                'bank_account' => '00830038888',
                'bank_name'    => 'MB Bank',
            ],
            [
                'stall_name'   => 'Gian Hàng Bánh Mỳ Đào Thị Lan Hương',
                'seller_name'  => 'Đào Thị Lan Hương',
                'seller_phone' => '0329296532',
                'name'         => 'Bánh mỳ pate thịt nướng trứng ruốc',
                'price'        => 20000,
                'unit'         => 'cái',
                'description'  => 'Bánh mỳ pate thịt nướng trứng ruốc nóng hổi tại chợ.',
                'image_path'   => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=420&h=230&fit=crop&auto=format',
                'star_rating'  => 5,
                'bank_account' => null,
                'bank_name'    => null,
            ],
            [
                'stall_name'   => 'Gian Hàng Rau Củ Quả Nguyễn Thị Hoa',
                'seller_name'  => 'Nguyễn Thị Hoa',
                'seller_phone' => '0979783923',
                'name'         => 'Rau củ quả tươi xanh theo mùa',
                'price'        => 15000,
                'unit'         => 'kg',
                'description'  => 'Rau củ quả tươi ngon thu hoạch từ vườn. STK Vietcombank: 1033474255',
                'image_path'   => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=420&h=230&fit=crop&auto=format',
                'star_rating'  => 5,
                'bank_account' => '1033474255',
                'bank_name'    => 'Vietcombank',
            ],
        ];

        foreach ($stalls as $s) {
            if (!empty($s['seller_name'])) {
                $emailSlug = Str::slug($s['seller_name']);
                $phoneVal  = !empty($s['seller_phone']) ? $s['seller_phone'] : null;

                $user = null;
                if ($phoneVal) {
                    $user = User::where('phone', $phoneVal)->first();
                }

                if (!$user) {
                    $email = $emailSlug . ($phoneVal ? '.' . $phoneVal : '.' . rand(1000, 9999)) . '@donganhsocial.vn';
                    $user = User::firstOrCreate(
                        ['email' => $email],
                        [
                            'name'         => $s['seller_name'],
                            'username'     => $emailSlug . ($phoneVal ? '_' . $phoneVal : '_' . rand(1000, 9999)),
                            'email'        => $email,
                            'phone'        => $phoneVal,
                            'password'     => Hash::make('12345678'),
                            'role'         => 'seller',
                            'status'       => 'active',
                            'bank_account' => $s['bank_account'] ?? null,
                            'bank_name'    => $s['bank_name'] ?? null,
                        ]
                    );
                } else {
                    $user->update([
                        'bank_account' => $s['bank_account'] ?? $user->bank_account,
                        'bank_name'    => $s['bank_name'] ?? $user->bank_name,
                    ]);
                }
            }

            OcopProduct::updateOrCreate(
                ['eatery_id' => $marketEatery->id, 'stall_name' => $s['stall_name']],
                [
                    'eatery_id'    => $marketEatery->id,
                    'stall_name'   => $s['stall_name'],
                    'seller_name'  => $s['seller_name'],
                    'seller_phone' => $s['seller_phone'],
                    'name'         => $s['name'],
                    'price'        => $s['price'],
                    'unit'         => $s['unit'],
                    'description'  => $s['description'],
                    'image_path'   => $s['image_path'],
                    'star_rating'  => $s['star_rating'],
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        RouteBusiness::where('route_key', 'route-xuan-canh')->delete();
        DigitalRoute::where('route_key', 'route-xuan-canh')->delete();

        $marketEatery = Eatery::where('name', 'like', '%Chợ Xuân Canh%')->first();
        if ($marketEatery) {
            OcopProduct::where('eatery_id', $marketEatery->id)->delete();
        }
    }
};
