<?php

namespace App\Services;

use App\Helpers\R2Helper;
use App\Models\MarketMessage;
use App\Models\OcopProduct;
use App\Models\RouteBusiness;
use App\Models\User;
use App\Services\Interfaces\VendorServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class VendorService implements VendorServiceInterface
{
    public function verifyVendor(?string $role): void
    {
        if (!in_array($role, ['seller', 'admin', 'manager'])) {
            abort(403, 'Bạn không có quyền truy cập Kênh Điều Hành Chủ Gian Hàng!');
        }
    }

    public function getVendorStallContext(?int $userId, ?string $userPhone, ?string $userName, ?string $activeEntityKey = null): array
    {
        $db = DB::connection('mysql_market');
        $user = $userId ? User::find($userId) : null;

        $stallRecord = null;
        $market = null;
        $businessEatery = null;
        $managedEntities = [];

        // 1a. Tìm Gian hàng Chợ (ocop_products)
        if ($user && $user->stall_id) {
            $stallRecord = $db->table('ocop_products')->where('id', $user->stall_id)->first();
        }
        if (!$stallRecord && $userId) {
            $stallRecord = $db->table('ocop_products')->where('user_id', $userId)->first();
        }
        if (!$stallRecord && !empty($userPhone)) {
            $stallRecord = $db->table('ocop_products')->where('seller_phone', $userPhone)->first();
        }

        if ($stallRecord) {
            $market = $db->table('eateries')->where('id', $stallRecord->eatery_id)->first();
            $managedEntities[] = [
                'key'         => 'stall_' . $stallRecord->id,
                'type'        => 'market_stall',
                'badge'       => '🛒 Gian hàng Chợ',
                'name'        => $stallRecord->stall_name,
                'sub'         => $market ? ('🏛️ ' . $market->name) : 'Chợ truyền thống',
                'id'          => $stallRecord->id,
                'market_id'   => $market ? $market->id : null,
                'market_slug' => $market ? $market->slug : '',
            ];
        }

        // 1b. Tìm Cơ sở kinh doanh / Doanh nghiệp / Cơ sở y tế / Trường học độc lập
        if ($userId) {
            $businessEatery = $db->table('eateries')->where('user_id', $userId)->first();
        }
        if (!$businessEatery && !empty($userPhone)) {
            $businessEatery = $db->table('eateries')->where('phone', $userPhone)->first();
        }
        if (!$businessEatery && $user && $user->eatery_id) {
            $businessEatery = $db->table('eateries')->where('id', $user->eatery_id)->first();
        }

        if ($businessEatery) {
            $catRec = $db->table('categories')->where('id', $businessEatery->category_id)->first();
            $cSlug = $catRec ? $catRec->slug : '';
            $badge = '🏢 Hộ kinh doanh / Doanh nghiệp';
            if ($cSlug === 'wellness-care') {
                $badge = '🩺 Cơ sở Y tế & Chăm sóc sức khỏe';
            } elseif ($cSlug === 'smart-education-map') {
                $badge = '🏫 Trường học & Giáo dục';
            } elseif ($cSlug === 'stay-in-dong-anh') {
                $badge = '🏨 Cơ sở Lưu trú & Khách sạn';
            } elseif ($cSlug === 'dong-anh-food-map') {
                $badge = '🍽️ Nhà hàng & Quán ăn';
            }

            $managedEntities[] = [
                'key'   => 'business_' . $businessEatery->id,
                'type'  => 'business',
                'badge' => $badge,
                'name'  => $businessEatery->name,
                'sub'   => '📍 ' . ($businessEatery->address ?: 'Đông Anh, Hà Nội'),
                'id'    => $businessEatery->id,
                'slug'  => $businessEatery->slug,
            ];
        }

        // 1c. Tuyến đường 4.0
        $routeBusinesses = $user ? $user->getRouteBusinesses() : collect();
        if ($routeBusinesses && $routeBusinesses->count() > 0) {
            foreach ($routeBusinesses as $rb) {
                $managedEntities[] = [
                    'key'   => 'route_' . $rb->id,
                    'type'  => 'route',
                    'badge' => '🛣️ Tuyến đường 4.0',
                    'name'  => $rb->name,
                    'sub'   => '🛣️ Tuyến ' . $rb->village_name,
                    'id'    => $rb->id,
                ];
            }
        }

        // Active Entity Key
        if (!$activeEntityKey || !collect($managedEntities)->contains('key', $activeEntityKey)) {
            $activeEntityKey = !empty($managedEntities) ? $managedEntities[0]['key'] : null;
        }

        // Bước 2: Cấu hình dữ liệu hiển thị theo Active Entity
        $isBusinessMode = str_starts_with($activeEntityKey ?? '', 'business_');
        $eatery = null;
        $stallName = null;
        $eateryId = 1;

        if ($isBusinessMode && $businessEatery) {
            $eatery = $businessEatery;
            $stallName = $businessEatery->name;
            $eateryId = $businessEatery->id;
        } elseif ($stallRecord && $market) {
            $eatery = $market;
            $stallName = $stallRecord->stall_name;
            $eateryId = $market->id;
        } elseif ($businessEatery) {
            $eatery = $businessEatery;
            $stallName = $businessEatery->name;
            $eateryId = $businessEatery->id;
        } else {
            $eatery = $db->table('eateries')->first();
            $stallName = $eatery ? $eatery->name : 'Gian hàng Số của tôi';
            $eateryId = $eatery ? $eatery->id : 1;
        }

        $sellerName = $userName ?: ($eatery ? $eatery->name : 'Chủ gian hàng');
        $sellerPhone = $userPhone ?: ($eatery ? $eatery->phone : '');

        $category = null;
        if ($eatery && $eatery->category_id) {
            $category = $db->table('categories')->where('id', $eatery->category_id)->first();
        }
        $categorySlug = $category ? $category->slug : '';
        $isOcopSeller = ($categorySlug === 'dong-anh-market');

        $products = collect();
        if ($stallRecord && !$isBusinessMode) {
            $products = $db->table('ocop_products')
                ->where('eatery_id', $eateryId)
                ->where('stall_name', $stallName)
                ->get();
        } elseif ($isBusinessMode && $businessEatery) {
            if ($categorySlug === 'wellness-care' && Schema::hasTable('wellness_services')) {
                $services = $db->table('wellness_services')->where('eatery_id', $businessEatery->id)->get();
                $products = $services->map(fn($s) => (object)[
                    'id'           => $s->id,
                    'eatery_id'    => $businessEatery->id,
                    'stall_name'   => $businessEatery->name,
                    'seller_name'  => $businessEatery->name,
                    'seller_phone' => $businessEatery->phone,
                    'name'         => $s->name,
                    'price'        => $s->price,
                    'unit'         => 'dịch vụ y tế',
                    'description'  => $s->description,
                    'image_path'   => $s->image_path,
                    'star_rating'  => null,
                ]);
            } else {
                $dishes = $db->table('dishes')->where('eatery_id', $businessEatery->id)->get();
                $products = $dishes->map(fn($d) => (object)[
                    'id'           => $d->id,
                    'eatery_id'    => $businessEatery->id,
                    'stall_name'   => $businessEatery->name,
                    'seller_name'  => $businessEatery->name,
                    'seller_phone' => $businessEatery->phone,
                    'name'         => $d->name,
                    'price'        => $d->price,
                    'unit'         => 'mặt hàng',
                    'description'  => $d->description,
                    'image_path'   => $d->image_path,
                    'star_rating'  => null,
                ]);
            }
        } else {
            $products = $db->table('ocop_products')->where('eatery_id', $eateryId)->get();
        }
        $primaryProduct = $products->first();

        return [
            'stallName'       => $stallName,
            'sellerName'      => $sellerName,
            'sellerPhone'     => $sellerPhone,
            'eateryId'        => $eateryId,
            'market'          => $market ?: $eatery,
            'businessEatery'  => $businessEatery,
            'stallRecord'     => $stallRecord,
            'managedEntities' => $managedEntities,
            'activeEntityKey' => $activeEntityKey,
            'isBusinessMode'  => $isBusinessMode,
            'products'        => $products,
            'isOcopSeller'    => $isOcopSeller,
            'primaryProduct'  => $primaryProduct,
            'categorySlug'    => $categorySlug,
            'routeBusinesses' => $routeBusinesses,
        ];
    }

    public function updateBusinessProfile(array $context, array $data, ?UploadedFile $image, ?int $userId): void
    {
        $businessEatery = $context['businessEatery'];
        if (!$businessEatery) {
            throw new \RuntimeException('Không tìm thấy thông tin Cơ sở kinh doanh thuộc tài khoản này!');
        }

        $updateData = [
            'name'          => $data['name'],
            'phone'         => $data['phone'] ?? null,
            'address'       => $data['address'] ?? null,
            'opening_hours' => $data['opening_hours'] ?? null,
            'price_range'   => $data['price_range'] ?? null,
            'description'   => $data['description'] ?? null,
            'updated_at'    => now(),
        ];

        if ($image) {
            $updateData['image_path'] = R2Helper::upload($image, 'eateries');
        }

        DB::table('eateries')->where('id', $businessEatery->id)->update($updateData);

        if (!empty($data['phone']) && $userId) {
            User::where('id', $userId)->update([
                'phone' => preg_replace('/[^0-9]/', '', $data['phone']),
            ]);
        }

        Cache::flush();
    }

    public function getDashboardData(array $context): array
    {
        $productsCount = $context['products']->count();
        $ordersCount = 0;
        $totalRevenue = 0;
        $recentOrders = collect();

        if (Schema::hasTable('orders')) {
            $orderQuery = DB::table('orders');
            $orderQuery = $this->applyVendorOrderQuery($orderQuery, $context);

            $recentOrders = (clone $orderQuery)->latest()->take(10)->get();
            $ordersCount = (clone $orderQuery)->count();
            $totalRevenue = (clone $orderQuery)->where('status', '!=', 'cancelled')->sum('total_amount');
        }

        return [
            'productsCount' => $productsCount,
            'ordersCount'   => $ordersCount,
            'totalRevenue'  => $totalRevenue,
            'recentOrders'  => $recentOrders,
        ];
    }

    public function updateDossier(int $eateryId, array $eateryData, array $ocopData): void
    {
        if (!empty($eateryData)) {
            $eateryData['updated_at'] = now();
            DB::connection('mysql_market')->table('eateries')->where('id', $eateryId)->update($eateryData);
        }

        if (!empty($ocopData)) {
            $ocopData['updated_at'] = now();
            DB::connection('mysql_market')->table('ocop_products')->where('eatery_id', $eateryId)->update($ocopData);
        }

        Cache::flush();
    }

    public function storeProduct(array $context, array $data, ?UploadedFile $imageFile): void
    {
        $imagePath = $imageFile ? R2Helper::upload($imageFile, 'products') : null;
        $numericPrice = $this->parsePriceToDecimal($data['price']);

        $description = $data['description'] ?? '';
        if (!empty($data['origin'])) {
            $originText = 'Nguồn gốc: ' . trim($data['origin']);
            $description = $description ? ($originText . '. ' . $description) : $originText;
        }

        $existingStallImg = DB::connection('mysql_market')->table('ocop_products')
            ->where('eatery_id', $context['eateryId'])
            ->where('stall_name', $context['stallName'])
            ->whereNotNull('stall_image')
            ->value('stall_image');

        DB::connection('mysql_market')->table('ocop_products')->insert([
            'eatery_id'    => $context['eateryId'],
            'stall_name'   => $context['stallName'],
            'seller_name'  => $context['sellerName'],
            'seller_phone' => $context['sellerPhone'],
            'stall_image'  => $existingStallImg ?: null,
            'name'         => $data['name'],
            'price'        => $numericPrice,
            'unit'         => $data['unit'] ?? 'kg',
            'star_rating'  => $data['star_rating'] ?? null,
            'image_path'   => $imagePath,
            'description'  => $description,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        Cache::flush();
    }

    public function updateProduct(int $id, array $data, ?UploadedFile $imageFile): bool
    {
        $product = DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->first();
        if (!$product) {
            return false;
        }

        $imagePath = $imageFile ? R2Helper::upload($imageFile, 'products') : $product->image_path;
        $numericPrice = $this->parsePriceToDecimal($data['price']);

        $description = $data['description'] ?? '';
        if (!empty($data['origin'])) {
            $originText = 'Nguồn gốc: ' . trim($data['origin']);
            $description = $description ? ($originText . '. ' . $description) : $originText;
        }

        $updateData = [
            'name'        => $data['name'],
            'price'       => $numericPrice,
            'description' => $description,
            'image_path'  => $imagePath,
            'updated_at'  => now(),
        ];
        if (!empty($data['unit'])) {
            $updateData['unit'] = $data['unit'];
        }
        if (!empty($data['star_rating'])) {
            $updateData['star_rating'] = $data['star_rating'];
        }

        DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->update($updateData);

        Cache::flush();
        return true;
    }

    public function destroyProduct(int $id): void
    {
        DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->delete();
        Cache::flush();
    }

    public function getOrders(array $context, int $perPage = 20)
    {
        if (!Schema::hasTable('orders')) {
            return collect();
        }

        $query = DB::table('orders');
        $query = $this->applyVendorOrderQuery($query, $context);
        $orders = $query->latest()->paginate($perPage);

        $orderIds = $orders->pluck('id');
        $allItems = DB::table('order_items')
            ->whereIn('order_id', $orderIds)
            ->get()
            ->groupBy('order_id');

        $orders->each(function ($ord) use ($allItems) {
            $ord->items = $allItems->get($ord->id, collect());
        });

        return $orders;
    }

    public function showOrder(int $orderId, array $context, string $userRole)
    {
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) {
            return null;
        }

        $hasAccess = false;
        if (!empty($context['stallName']) && $order->stall_name === $context['stallName']) {
            $hasAccess = true;
        }
        if (!empty($context['eateryId']) && (int)$order->eatery_id === (int)$context['eateryId']) {
            $hasAccess = true;
        }
        if (!$hasAccess && $userRole !== 'admin') {
            abort(403, 'Gian hàng của bạn không có quyền xem đơn hàng này!');
        }

        $order->items = DB::table('order_items')->where('order_id', $order->id)->get();
        return $order;
    }

    public function updateOrderStatus(int $orderId, string $status, ?string $cancelReason = null): string
    {
        if (Schema::hasTable('orders')) {
            $updateData = [
                'status'     => $status,
                'updated_at' => now(),
            ];

            if ($status === 'confirmed') {
                $updateData['confirmed_at'] = now();
            } elseif ($status === 'preparing' || $status === 'processing') {
                $updateData['preparing_at'] = now();
            } elseif ($status === 'ready') {
                $updateData['ready_at'] = now();
            } elseif ($status === 'shipping') {
                $updateData['shipping_at'] = now();
            } elseif ($status === 'completed') {
                $updateData['completed_at'] = now();
            } elseif ($status === 'cancelled') {
                $updateData['cancelled_at'] = now();
                $updateData['cancelled_by'] = 'seller';
                if ($cancelReason) {
                    $updateData['cancel_reason'] = trim($cancelReason);
                }
            }

            DB::table('orders')->where('id', $orderId)->update($updateData);
        }

        $msgMap = [
            'confirmed'  => '✅ Đã xác nhận đơn hàng!',
            'preparing'  => '⚡ Đã chuyển đơn hàng sang trạng thái: Đang chuẩn bị hàng!',
            'processing' => '⚡ Đã chuyển đơn hàng sang trạng thái: Đang chuẩn bị hàng!',
            'ready'      => '🏪 Đã chuyển đơn hàng sang trạng thái: Sẵn sàng tại sạp (Chờ khách lấy)!',
            'shipping'   => '🛵 Đã chuyển đơn hàng sang trạng thái: Đang giao hàng!',
            'completed'  => '🎉 Đã hoàn thành đơn hàng thành công!',
            'cancelled'  => '❌ Đã từ chối / hủy đơn hàng!',
        ];

        return $msgMap[$status] ?? 'Đã cập nhật trạng thái đơn hàng!';
    }

    public function getOrdersJson(array $context): array
    {
        $rawOrders = collect();
        if (Schema::hasTable('orders')) {
            $query = DB::table('orders');
            $query = $this->applyVendorOrderQuery($query, $context);
            $rawOrders = $query->latest()->limit(50)->get();

            $orderIds = $rawOrders->pluck('id');
            $allItems = DB::table('order_items')
                ->whereIn('order_id', $orderIds)
                ->get()
                ->groupBy('order_id');

            $rawOrders = $rawOrders->map(function ($ord) use ($allItems) {
                $ord->items = $allItems->get($ord->id, collect())->values();
                $ord->created_at_formatted = Carbon::parse($ord->created_at)->format('H:i d/m/Y');
                return $ord;
            });
        }

        return [
            'success'   => true,
            'stall'     => $context['stallName'],
            'orders'    => $rawOrders->values(),
            'polled_at' => now()->toDateTimeString(),
            'count'     => $rawOrders->count(),
        ];
    }

    public function getProfileData(array $context, ?int $userId): array
    {
        return [
            'user'           => $userId ? User::find($userId) : null,
            'primaryProduct' => $context['primaryProduct'],
        ];
    }

    public function updateProfile(array $context, array $data, ?int $userId, ?UploadedFile $stallImage, ?string $stallImageUrl, ?UploadedFile $avatarFile, ?string $avatarUrl): void
    {
        $db = DB::connection('mysql_market');
        $user = $userId ? User::find($userId) : null;

        $sellerName  = trim($data['seller_name']);
        $sellerPhone = trim($data['seller_phone']);
        $stallName   = trim($data['stall_name'] ?? '') ?: $context['stallName'];
        $address     = trim($data['address'] ?? '');
        $mapLink     = trim($data['map_link'] ?? '');
        $latitude    = isset($data['latitude']) && $data['latitude'] !== '' ? (float)$data['latitude'] : null;
        $longitude   = isset($data['longitude']) && $data['longitude'] !== '' ? (float)$data['longitude'] : null;

        if (!empty($mapLink)) {
            list($autoLat, $autoLng) = $this->parseGoogleMapsUrl($mapLink);
            if ($autoLat !== null && $autoLng !== null) {
                $latitude = $autoLat;
                $longitude = $autoLng;
            }
        }

        $bankName    = trim($data['bank_name'] ?? '');
        $bankAccount = trim($data['bank_account'] ?? '');
        $bankHolder  = trim($data['bank_holder'] ?? '');
        $origin      = trim($data['origin'] ?? '');
        $attp        = trim($data['attp'] ?? '');
        $description = trim($data['description'] ?? '');

        $qrCodeUrl = null;
        if (!empty($bankName) && !empty($bankAccount)) {
            $holderUpper = mb_strtoupper($bankHolder ?: $sellerName, 'UTF-8');
            $addInfo = "TT " . Str::slug($stallName, ' ');
            $qrCodeUrl = "https://img.vietqr.io/image/{$bankName}-{$bankAccount}-compact.png?accountName=" . urlencode($holderUpper) . "&addInfo=" . urlencode($addInfo);
        }

        $stallImagePath = null;
        if ($stallImage) {
            $stallImagePath = R2Helper::upload($stallImage, 'stalls');
        } elseif (!empty($stallImageUrl)) {
            $stallImagePath = trim($stallImageUrl);
        }

        $ownerAvatarPath = null;
        if ($avatarFile) {
            $ownerAvatarPath = R2Helper::upload($avatarFile, 'avatars');
        } elseif (!empty($avatarUrl)) {
            $ownerAvatarPath = trim($avatarUrl);
        }

        // 1. Cập nhật User
        if ($user) {
            $userUpdate = [
                'name'         => $sellerName,
                'phone'        => $sellerPhone,
                'bank_account' => $bankAccount ?: null,
                'bank_name'    => $bankName ?: null,
                'updated_at'   => now(),
            ];
            if ($ownerAvatarPath) {
                $userUpdate['avatar'] = $ownerAvatarPath;
            }
            DB::table('users')->where('id', $user->id)->update($userUpdate);

            try {
                $routeUpdate = [
                    'user_id'      => $user->id,
                    'owner'        => $sellerName,
                    'phone'        => $sellerPhone,
                    'bank_account' => $bankAccount ?: null,
                    'bank_name'    => $bankName ?: null,
                    'updated_at'   => now(),
                ];
                if ($ownerAvatarPath) {
                    $routeUpdate['image_url'] = $ownerAvatarPath;
                }
                RouteBusiness::where('user_id', $user->id)
                    ->orWhere('phone', $sellerPhone)
                    ->update($routeUpdate);
            } catch (\Exception $ex) {}
        }

        // 2. Cập nhật ocop_products
        $productQuery = $db->table('ocop_products')->where('eatery_id', $context['eateryId']);
        if ($user) {
            $productQuery->where(function ($q) use ($user, $sellerPhone, $context) {
                $q->where('user_id', $user->id)
                  ->orWhere('seller_phone', $sellerPhone)
                  ->orWhere('stall_name', $context['stallName']);
            });
        } else {
            $productQuery->where('stall_name', $context['stallName']);
        }

        // Cập nhật Cơ sở kinh doanh (eateries) nếu ở chế độ Doanh nghiệp
        if (!empty($context['isBusinessMode']) && !empty($context['businessEatery'])) {
            $eateryUpdate = [
                'name'       => $stallName,
                'phone'      => $sellerPhone,
                'updated_at' => now(),
            ];
            if (!empty($address)) $eateryUpdate['address'] = $address;
            if ($latitude !== null) $eateryUpdate['latitude'] = $latitude;
            if ($longitude !== null) $eateryUpdate['longitude'] = $longitude;
            if ($stallImagePath) $eateryUpdate['image_path'] = $stallImagePath;
            $db->table('eateries')->where('id', $context['businessEatery']->id)->update($eateryUpdate);
        }

        $ocopData = [
            'stall_name'   => $stallName,
            'seller_name'  => $sellerName,
            'seller_phone' => $sellerPhone,
            'bank_name'    => $bankName ?: null,
            'bank_account' => $bankAccount ?: null,
            'bank_holder'  => $bankHolder ?: null,
            'qr_code_path' => $qrCodeUrl,
            'updated_at'   => now(),
        ];
        if ($stallImagePath) {
            $ocopData['stall_image'] = $stallImagePath;
        }

        if (!empty($origin) || !empty($attp) || !empty($description)) {
            $descParts = [];
            if (!empty($origin)) {
                $descParts[] = "Nguồn gốc: " . $origin;
            }
            if (!empty($attp)) {
                $descParts[] = "Cam kết ATTP: " . $attp;
            }
            if (!empty($description)) {
                $descParts[] = $description;
            }
            $ocopData['description'] = implode('. ', $descParts);
        }

        $productQuery->update($ocopData);
        Cache::flush();
    }

    public function getChatConversations(array $context, ?int $sellerUserId): array
    {
        $stallName = $context['stallName'];

        $customerUserIds = MarketMessage::where(function ($q) use ($stallName) {
                $q->where('private_stall_name', $stallName)
                  ->orWhere('stall_name', $stallName);
            })
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $sellerUserId)
            ->pluck('user_id')
            ->merge(
                MarketMessage::where('stall_name', $stallName)
                    ->whereNotNull('private_user_id')
                    ->where('private_user_id', '!=', $sellerUserId)
                    ->pluck('private_user_id')
            )
            ->unique()
            ->filter();

        $conversations = [];
        foreach ($customerUserIds as $cId) {
            $cust = User::find($cId);
            $lastMsg = MarketMessage::where(function ($q) use ($stallName, $cId) {
                    $q->where(function ($sub) use ($stallName, $cId) {
                        $sub->where('private_stall_name', $stallName)->where('user_id', $cId);
                    })->orWhere(function ($sub) use ($stallName, $cId) {
                        $sub->where('stall_name', $stallName)->where('private_user_id', $cId);
                    });
                })
                ->latest('id')
                ->first();
                
            if ($cust && $lastMsg) {
                $conversations[] = [
                    'customer_id'         => $cust->id,
                    'customer_name'       => $cust->name,
                    'customer_phone'      => $cust->phone ?? '',
                    'avatar_char'         => mb_substr($cust->name, 0, 1, 'UTF-8'),
                    'last_message'        => $lastMsg->message_text ?: ($lastMsg->image_path ? '📷 [Hình ảnh]' : ($lastMsg->product_id ? '🏷️ [Sản phẩm đính kèm]' : '...')),
                    'last_time'           => $lastMsg->created_at ? $lastMsg->created_at->diffForHumans() : 'Vừa xong',
                    'last_time_formatted' => $lastMsg->created_at ? $lastMsg->created_at->format('H:i d/m') : '',
                    'last_msg_id'         => $lastMsg->id,
                    'is_from_customer'    => ($lastMsg->user_id == $cust->id),
                ];
            }
        }

        usort($conversations, fn($a, $b) => $b['last_msg_id'] <=> $a['last_msg_id']);
        return $conversations;
    }

    public function getChatMessages(array $context, string $room, ?int $customerId, ?int $sellerUserId): array
    {
        $stallName = $context['stallName'];
        $eateryId = $context['eateryId'];

        $query = MarketMessage::where('eatery_id', $eateryId);

        if ($room === 'private') {
            if (!$customerId) {
                return [];
            }

            $query->where(function ($q) use ($stallName, $customerId) {
                $q->where(function ($sub) use ($stallName, $customerId) {
                    $sub->where('private_stall_name', $stallName)
                        ->where('user_id', $customerId);
                })
                ->orWhere(function ($sub) use ($stallName, $customerId) {
                    $sub->where('stall_name', $stallName)
                        ->where('private_user_id', $customerId);
                });
            });
        } else {
            $query->whereNull('private_stall_name')->whereNull('private_user_id');
        }

        $messages = $query->orderBy('id', 'asc')->take(80)->get();

        return $messages->map(function ($msg) use ($sellerUserId, $stallName) {
            $productData = null;
            if ($msg->product_id && $msg->product) {
                $p = $msg->product;
                $productData = [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'price'      => (float)$p->price,
                    'image'      => $p->image_path ? asset($p->image_path) : 'https://placehold.co/80x80/00A86B/ffffff?text=Product',
                    'stall_name' => $p->stall_name,
                ];
            }

            $dateGroup = 'Lịch sử';
            if ($msg->created_at->isToday()) {
                $dateGroup = 'Hôm nay';
            } elseif ($msg->created_at->isYesterday()) {
                $dateGroup = 'Hôm qua';
            } else {
                $dateGroup = $msg->created_at->format('d/m/Y');
            }

            $isOwn = ($msg->sender_role === 'merchant' && $msg->stall_name === $stallName) || ($msg->user_id == $sellerUserId);

            return [
                'id'             => $msg->id,
                'sender_name'    => $msg->sender_name,
                'sender_role'    => $msg->sender_role,
                'stall_name'     => $msg->stall_name,
                'message_text'   => $msg->message_text,
                'image_url'      => $msg->image_path ? asset($msg->image_path) : null,
                'product'        => $productData,
                'time_formatted' => $msg->created_at->format('H:i'),
                'is_own'         => $isOwn,
                'date_group'     => $dateGroup,
            ];
        })->toArray();
    }

    public function sendChatMessage(array $context, ?int $userId, array $data, ?UploadedFile $imageFile): array
    {
        $stallName = $context['stallName'];
        $sellerName = $context['sellerName'] ?? 'Chủ sạp';
        $eateryId = $context['eateryId'];

        $room = $data['room'] ?? 'private';
        $customerId = $data['customer_id'] ?? null;

        $imagePath = $imageFile ? R2Helper::upload($imageFile, 'market_chat') : null;

        $messageText = $data['message_text'] ?? null;
        if (empty($messageText) && !empty($data['product_id'])) {
            $product = OcopProduct::on('mysql_market')->find($data['product_id']);
            if ($product) {
                $messageText = "Sạp gửi bạn thông tin sản phẩm: {$product->name}";
            }
        }

        $message = MarketMessage::create([
            'eatery_id'          => $eateryId,
            'user_id'            => $userId,
            'sender_name'        => "Chủ sạp {$sellerName}",
            'sender_role'        => 'merchant',
            'stall_name'         => $stallName,
            'message_text'       => $messageText ?: '',
            'image_path'         => $imagePath,
            'product_id'         => $data['product_id'] ?? null,
            'private_stall_name' => ($room === 'private') ? $stallName : null,
            'private_user_id'    => ($room === 'private' && $customerId) ? (int)$customerId : null,
        ]);

        return [
            'id'             => $message->id,
            'sender_name'    => $message->sender_name,
            'sender_role'    => 'merchant',
            'stall_name'     => $stallName,
            'message_text'   => $message->message_text,
            'image_url'      => $message->image_path ? asset($message->image_path) : null,
            'time_formatted' => $message->created_at->format('H:i'),
            'is_own'         => true,
        ];
    }

    public function getChatUnreadCount(array $context, ?int $sellerUserId): array
    {
        $stallName = $context['stallName'];

        $latestMsg = MarketMessage::where('private_stall_name', $stallName)
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $sellerUserId)
            ->latest('id')
            ->first();

        return [
            'success'        => true,
            'stall_name'     => $stallName,
            'latest_message' => $latestMsg ? [
                'id'                   => $latestMsg->id,
                'sender_name'          => $latestMsg->sender_name,
                'user_id'              => $latestMsg->user_id,
                'message_text'         => $latestMsg->message_text ?: '📷 [Hình ảnh]',
                'created_at_formatted' => $latestMsg->created_at ? $latestMsg->created_at->diffForHumans() : 'Vừa xong',
            ] : null,
        ];
    }

    private function applyVendorOrderQuery($query, array $context)
    {
        $stallName = $context['stallName'] ?? null;
        $eateryId = $context['eateryId'] ?? null;
        $isBusinessMode = $context['isBusinessMode'] ?? false;

        if ($stallName && !$isBusinessMode) {
            $query->where('stall_name', $stallName);
            if (!empty($eateryId)) {
                $query->where('eatery_id', $eateryId);
            }
        } elseif (!empty($eateryId)) {
            $query->where('eatery_id', $eateryId);
            if (!empty($stallName)) {
                $query->where(function ($sub) use ($stallName) {
                    $sub->whereNull('stall_name')
                        ->orWhere('stall_name', '')
                        ->orWhere('stall_name', $stallName);
                });
            }
        }

        return $query;
    }

    private function parsePriceToDecimal($rawPrice): float
    {
        if (is_numeric($rawPrice)) {
            return (float) $rawPrice;
        }
        $cleaned = preg_replace('/[^\d,.]/', '', (string)$rawPrice);
        $cleaned = str_replace(['.', ','], '', $cleaned);
        return (float) $cleaned ?: 0;
    }

    private function parseGoogleMapsUrl($url): array
    {
        if (empty($url)) return [null, null];

        if (str_contains($url, 'maps.app.goo.gl') || str_contains($url, 'goo.gl/maps')) {
            $headers = @get_headers($url, 1);
            if (isset($headers['Location'])) {
                $url = is_array($headers['Location']) ? (is_array(end($headers['Location'])) ? end(end($headers['Location'])) : end($headers['Location'])) : $headers['Location'];
            }
        }

        if (preg_match('/@(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        if (preg_match('/!3d(-?\d+\.\d+)!4d(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        if (preg_match('/[?&](?:q|ll)=(-?\d+\.\d+),(-?\d+\.\d+)/', $url, $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        if (preg_match('/^(-?\d+\.\d+)\s*,\s*(-?\d+\.\d+)$/', trim($url), $m)) {
            return [(float)$m[1], (float)$m[2]];
        }

        return [null, null];
    }
}
