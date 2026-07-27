<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Cập nhật user_id của Chợ Mạch Tràng (eatery_id = 32) thành user 2 (Ban Quản lý Chợ Mạch Tràng)
DB::connection('mysql_market')->table('eateries')->where('id', 32)->update(['user_id' => 2]);
echo "Updated Chợ Mạch Tràng (id=32) user_id to 2 (Ban Quản lý Chợ Mạch Tràng)\n";

// 2. Cập nhật user_id cho Chợ Tó, Chợ Trung Tâm, Chợ Sa, Chợ Mai Lâm
DB::connection('mysql_market')->table('eateries')->where('id', 17)->update(['user_id' => 7]);
DB::connection('mysql_market')->table('eateries')->where('id', 18)->update(['user_id' => 8]);
DB::connection('mysql_market')->table('eateries')->where('id', 19)->update(['user_id' => 9]);
DB::connection('mysql_market')->table('eateries')->where('id', 20)->update(['user_id' => 10]);

// 3. Lấy tất cả 34 gian hàng của Chợ Mạch Tràng (eatery_id = 32)
$stallsMachTrang = DB::connection('mysql_market')->table('ocop_products')->where('eatery_id', 32)->get();
echo "Found " . $stallsMachTrang->count() . " stalls in Chợ Mạch Tràng (eatery_id=32):\n";

foreach ($stallsMachTrang as $s) {
    echo "  - Stall ID: {$s->id} | Name: {$s->stall_name} | Seller: {$s->seller_name} | Phone: {$s->seller_phone}\n";
    
    // Cập nhật hoặc liên kết với user seller nếu có
    $phone = trim($s->seller_phone);
    if ($phone && $phone !== 'Cần cập nhật thông tin') {
        $user = DB::table('users')->where('phone', $phone)->first();
        if ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'stall_id' => $s->id,
                'eatery_id' => 32,
                'role' => 'seller'
            ]);
            DB::connection('mysql_market')->table('ocop_products')->where('id', $s->id)->update([
                'user_id' => $user->id
            ]);
            echo "      -> Linked existing User ID {$user->id} ({$user->name})\n";
        }
    }
}
