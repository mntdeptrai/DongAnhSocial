<?php

namespace App\Services;

use App\Helpers\R2Helper;
use App\Models\Category;
use App\Models\Commune;
use App\Models\Eatery;
use App\Models\EateryPhoto;
use App\Models\Review;
use App\Models\User;
use App\Services\Interfaces\AdminServiceInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminService implements AdminServiceInterface
{
    public function verifyAdminAccess(?int $userId, ?string $role): void
    {
        if (!$userId) {
            abort(401, 'Vui lòng đăng nhập!');
        }

        if (!in_array($role, ['admin', 'seller', 'manager'])) {
            abort(403, 'Bạn không có quyền truy cập trang quản lý này!');
        }
    }

    public function getDashboardData(?int $userId, ?string $role, ?string $query, ?string $categoryName, ?string $communeName, int $currentPage = 1, int $perPage = 10): array
    {
        $isSeller = in_array($role, ['seller', 'manager']);
        $allEateries = EateryApiService::getEateries();
        $sellerEateries = $isSeller ? $allEateries->where('user_id', $userId) : $allEateries;

        if ($role === 'manager') {
            $sellerEateries = $sellerEateries->filter(function ($e) {
                return $e->category && $e->category->slug === 'traditional-market';
            });

            if ($sellerEateries->isEmpty() && $userId) {
                $user = User::find($userId);
                if ($user) {
                    $cleanName = str_replace(['Ban Quản lý ', 'Ban quản lý ', 'BQL '], '', $user->name);
                    $matchedMarket = $allEateries->first(function ($e) use ($cleanName) {
                        return $e->category && $e->category->slug === 'traditional-market' && str_contains(mb_strtolower($e->name), mb_strtolower($cleanName));
                    });
                    if ($matchedMarket) {
                        $sellerEateries = collect([$matchedMarket]);
                    }
                }
            }
        }

        $marketStats = null;
        if ($role === 'manager') {
            $managedMarket = $sellerEateries->first();
            $marketId = $managedMarket ? $managedMarket->id : null;

            $stallsCount = 0;
            $totalOrdersCount = 0;
            $totalRevenue = 0;
            $stallBreakdown = collect();

            if ($marketId) {
                $prods = DB::table('ocop_products')->where('eatery_id', $marketId)->get();
                $dishes = DB::table('dishes')->where('eatery_id', $marketId)->get();

                $stallsCount = $prods->pluck('stall_name')->concat($dishes->pluck('name'))->filter()->unique()->count();
                if ($stallsCount === 0) {
                    $stallsCount = $prods->count() + $dishes->count();
                }

                if (Schema::hasTable('orders')) {
                    $orders = DB::table('orders')->where('eatery_id', $marketId)->get();
                    $totalOrdersCount = $orders->count();
                    $totalRevenue = $orders->sum(fn($o) => $o->total_amount ?? $o->total_price ?? 0);

                    $stallBreakdown = $orders->groupBy('stall_name')->map(fn($group, $stallName) => [
                        'stall_name'   => $stallName ?: 'Gian hàng chung',
                        'orders_count' => $group->count(),
                        'revenue'      => $group->sum(fn($o) => $o->total_amount ?? $o->total_price ?? 0),
                    ])->values();
                }
            }

            $marketStats = [
                'managed_market'  => $managedMarket,
                'stalls_count'    => $stallsCount,
                'total_orders'    => $totalOrdersCount,
                'total_revenue'   => $totalRevenue,
                'stall_breakdown' => $stallBreakdown,
            ];
        }

        $stats = [
            'total_eateries'   => $sellerEateries->count(),
            'total_categories' => EateryApiService::getCategories()->count(),
            'total_communes'   => EateryApiService::getCommunes()->count(),
            'total_reviews'    => $sellerEateries->sum('reviews_count'),
        ];

        $filteredEateries = $sellerEateries;

        if ($query) {
            $q = $query;
            $filteredEateries = $filteredEateries->filter(fn($e) =>
                Str::contains(Str::lower($e->name), Str::lower($q))
                || Str::contains(Str::lower($e->address), Str::lower($q))
                || Str::contains(Str::lower($e->phone), Str::lower($q))
            );
        }

        if ($categoryName) {
            $filteredEateries = $filteredEateries->filter(fn($e) => $e->category && $e->category->name === $categoryName);
        }

        if ($communeName) {
            $filteredEateries = $filteredEateries->filter(fn($e) => $e->commune && $e->commune->name === $communeName);
        }

        $currentItems = $filteredEateries->sortByDesc('created_at')->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $eateries = new LengthAwarePaginator(
            $currentItems,
            $filteredEateries->count(),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );
        $eateries->withQueryString();

        $videos = EateryApiService::getVideos();
        if ($isSeller) {
            $sellerEateryIds = $sellerEateries->pluck('id')->toArray();
            $videos = $videos->whereIn('eatery_id', $sellerEateryIds)->values();
        }

        return [
            'stats'        => $stats,
            'eateries'     => $eateries,
            'videos'       => $videos,
            'marketStats'  => $marketStats,
            'isManager'    => ($role === 'manager'),
        ];
    }

    public function parseGoogleMapsUrl(string $url): array
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

    public function storeEateryPhoto(int $eateryId, ?string $caption, ?UploadedFile $photoFile): void
    {
        $imagePath = $photoFile ? R2Helper::upload($photoFile, 'eatery-photos') : null;

        EateryPhoto::create([
            'eatery_id'  => $eateryId,
            'image_path' => $imagePath,
            'caption'    => $caption,
        ]);

        Cache::flush();
    }

    public function destroyEateryPhoto(int $id): void
    {
        $photo = EateryPhoto::findOrFail($id);
        $photo->delete();
        Cache::flush();
    }

    public function destroyReview(int $id): void
    {
        $review = Review::findOrFail($id);
        $review->delete();
        Cache::flush();
    }

    public function replyReview(int $id, string $reply): void
    {
        $review = Review::findOrFail($id);
        $review->update([
            'reply'      => $reply,
            'replied_at' => now(),
        ]);
        Cache::flush();
    }

    public function getUsersList(array $filters, ?int $managerUserId, ?string $role, int $perPage = 10): array
    {
        $query = User::query();

        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
            $eateryId = $managerEatery ? $managerEatery->id : 0;

            $stallIds = DB::connection('mysql_market')->table('ocop_products')->where('eatery_id', $eateryId)->pluck('id')->toArray();

            $query->where('role', 'seller')->where(function ($q) use ($eateryId, $stallIds) {
                $q->where('eatery_id', $eateryId);
                if (!empty($stallIds)) {
                    $q->orWhereIn('stall_id', $stallIds);
                }
            });
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                if (mb_strlen($search) >= 2) {
                    try {
                        $q->whereFullText(['name', 'username', 'email', 'phone'], $search);
                    } catch (\Throwable $e) {}
                }
                $q->orWhere('phone', 'like', "{$search}%")
                  ->orWhere('email', 'like', "{$search}%")
                  ->orWhere('username', 'like', "{$search}%")
                  ->orWhere('name', 'like', "{$search}%");
            });
        }

        if (!empty($filters['user_type'])) {
            $type = $filters['user_type'];
            if ($type === 'market_seller') {
                $query->where('role', 'seller');
            } elseif ($type === 'principal') {
                $query->where('role', 'principal');
            } elseif ($type === 'admin_group') {
                $query->whereIn('role', ['admin', 'manager']);
            } elseif ($type === 'customer') {
                $query->where('role', 'user');
            }
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $totalUsers = (clone $query)->count();
        $adminCount = User::whereIn('role', ['admin', 'manager'])->count();
        $marketSellerCount = DB::table('ocop_products')->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $cskdSellerCount = DB::table('eateries')->where('category_id', 9)->whereNotNull('user_id')->distinct('user_id')->count('user_id');
        $userCount = User::where('role', 'user')->count();

        $users = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $markets = Eatery::where('category_id', 8)
            ->whereIn('id', DB::table('ocop_products')->select('eatery_id')->distinct())
            ->orderBy('name')
            ->get();
        $marketsMap = Eatery::where('category_id', 8)->get()->keyBy('id');

        $communes = Commune::whereIn('id', function ($sub) {
            $sub->select('commune_id')->from('eateries')->where('category_id', 9)->whereNotNull('commune_id')->distinct();
        })->orderBy('name')->get();

        return compact(
            'users', 'totalUsers', 'adminCount', 'marketSellerCount', 'cskdSellerCount', 'userCount',
            'markets', 'marketsMap', 'communes'
        );
    }

    public function exportUsers(array $filters, ?int $managerUserId, ?string $role): StreamedResponse
    {
        $filename = 'DongAnh_Users_Export_' . date('Ymd_His') . '.xls';
        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $data = $this->getUsersList($filters, $managerUserId, $role, 1000);

        $callback = function () use ($data) {
            echo "\xEF\xBB\xBF";
            echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
            echo '<Worksheet ss:Name="Users"><Table>' . "\n";
            echo '<Row><Cell><Data ss:Type="String">ID</Data></Cell><Cell><Data ss:Type="String">Tên</Data></Cell><Cell><Data ss:Type="String">Email</Data></Cell><Cell><Data ss:Type="String">SĐT</Data></Cell><Cell><Data ss:Type="String">Vai trò</Data></Cell></Row>' . "\n";

            foreach ($data['users'] as $u) {
                echo "<Row>";
                echo "<Cell><Data ss:Type=\"Number\">{$u->id}</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($u->name) . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($u->email ?? '') . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($u->phone ?? '') . "</Data></Cell>";
                echo "<Cell><Data ss:Type=\"String\">" . htmlspecialchars($u->role) . "</Data></Cell>";
                echo "</Row>\n";
            }

            echo '</Table></Worksheet></Workbook>';
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getCreateUserData(?int $managerUserId, ?string $role): array
    {
        $eateries = EateryApiService::getEateries();
        $markets = DB::table('eateries')->get();
        $marketsMap = $markets->keyBy('id');
        $stalls = collect();

        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
            $eateryId = $managerEatery ? $managerEatery->id : 0;
            $eateries = $eateries->where('id', $eateryId);

            if ($eateryId) {
                $stalls = DB::connection('mysql_market')->table('ocop_products')
                    ->where('eatery_id', $eateryId)
                    ->orderBy('stall_name')
                    ->get()
                    ->unique(fn($item) => trim($item->stall_name) ?: trim($item->seller_name))
                    ->values();
            }
        } else {
            $stalls = DB::table('ocop_products')
                ->orderBy('eatery_id')
                ->orderBy('stall_name')
                ->get()
                ->unique(fn($item) => trim($item->stall_name) ?: trim($item->seller_name))
                ->values();
        }

        return compact('eateries', 'stalls', 'marketsMap');
    }

    public function storeUser(array $data, ?int $managerUserId, ?string $role): User
    {
        $eateryId = $data['eatery_id'] ?? null;
        $stallId = !empty($data['stall_id']) ? (int)$data['stall_id'] : null;

        if ($stallId) {
            $stall = DB::table('ocop_products')->where('id', $stallId)->first();
            if ($stall) {
                $eateryId = $stall->eatery_id;
            }
        }

        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
            $eateryId = $managerEatery ? $managerEatery->id : null;
        }

        $user = User::create([
            'name'      => $data['name'],
            'username'  => $data['username'],
            'email'     => $data['email'] ?? null,
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'avatar'    => $data['avatar'] ?? '👨‍🍳',
            'phone'     => $data['phone'] ?? null,
            'status'    => 'active',
            'eatery_id' => $eateryId,
            'stall_id'  => $stallId,
        ]);

        if ($eateryId) {
            foreach (['mysql', 'mysql_education', 'mysql_market', 'mysql_stay', 'mysql_wellness', 'mysql_culture'] as $conn) {
                rescue(fn() => DB::connection($conn)->table('eateries')->where('id', $eateryId)->update(['user_id' => $user->id]), report: false);
            }
        }

        if ($stallId) {
            DB::connection('mysql_market')->table('ocop_products')->where('id', $stallId)->update([
                'user_id'      => $user->id,
                'seller_name'  => $user->name,
                'seller_phone' => $user->phone,
            ]);
        }

        return $user;
    }

    public function getUserDetails(int $id, ?string $role): array
    {
        $user = User::findOrFail($id);
        if ($role === 'manager' && $user->role !== 'seller') {
            abort(403, 'Ban Quản Lý Chợ chỉ được quyền quản lý tiểu thương trong chợ của mình!');
        }

        $stall = null;
        $market = null;
        $products = collect();

        if ($user->stall_id) {
            $stall = DB::connection('mysql_market')->table('ocop_products')->where('id', $user->stall_id)->first();
        }
        if (!$stall) {
            $stall = DB::connection('mysql_market')->table('ocop_products')->where('user_id', $user->id)->first();
        }
        if (!$stall && $user->phone) {
            $stall = DB::connection('mysql_market')->table('ocop_products')->where('seller_phone', $user->phone)->first();
        }

        $eateryId = $user->eatery_id ?: ($stall ? $stall->eatery_id : null);
        if ($eateryId) {
            $market = DB::table('eateries')->where('id', $eateryId)->first();
            if (!$market) {
                rescue(fn() => $market = DB::connection('mysql_market')->table('eateries')->where('id', $eateryId)->first(), report: false);
            }
        }
        if (!$market) {
            $market = DB::table('eateries')->where('user_id', $user->id)->first();
        }

        if ($stall) {
            $stallNameKey = trim($stall->stall_name) ?: trim($stall->seller_name);
            $products = DB::connection('mysql_market')->table('ocop_products')
                ->where(function ($q) use ($stallNameKey, $user) {
                    $q->where('stall_name', $stallNameKey)->orWhere('user_id', $user->id);
                })
                ->get();
        }

        return compact('user', 'stall', 'market', 'products');
    }

    public function getUserForEdit(int $id, ?int $managerUserId, ?string $role): array
    {
        $user = User::findOrFail($id);
        if ($role === 'manager' && $user->role !== 'seller') {
            abort(403, 'Ban Quản Lý Chợ chỉ được quyền quản lý tiểu thương trong chợ của mình!');
        }

        $eateries = EateryApiService::getEateries();
        $markets = DB::table('eateries')->get();
        $marketsMap = $markets->keyBy('id');
        $stalls = collect();

        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
            $eateryId = $managerEatery ? $managerEatery->id : 0;
            $eateries = $eateries->where('id', $eateryId);

            if ($eateryId) {
                $stalls = DB::connection('mysql_market')->table('ocop_products')
                    ->where('eatery_id', $eateryId)
                    ->orderBy('stall_name')
                    ->get()
                    ->unique(fn($item) => trim($item->stall_name) ?: trim($item->seller_name))
                    ->values();
            }
        } else {
            $stalls = DB::table('ocop_products')
                ->orderBy('eatery_id')
                ->orderBy('stall_name')
                ->get()
                ->unique(fn($item) => trim($item->stall_name) ?: trim($item->seller_name))
                ->values();
        }

        $currentEatery = $eateries->firstWhere('user_id', $user->id);
        $currentEateryId = $user->eatery_id ?: ($currentEatery ? $currentEatery->id : null);
        $currentStallId = $user->stall_id;

        return compact('user', 'eateries', 'currentEateryId', 'stalls', 'currentStallId', 'marketsMap');
    }

    public function updateUser(int $id, array $data, ?int $managerUserId, ?string $role): User
    {
        $user = User::findOrFail($id);
        if ($role === 'manager' && $user->role !== 'seller') {
            abort(403, 'Ban Quản Lý Chợ chỉ được quyền quản lý tiểu thương trong chợ của mình!');
        }

        $eateryId = !empty($data['eatery_id']) ? (int)$data['eatery_id'] : $user->eatery_id;
        $stallId = !empty($data['stall_id']) ? (int)$data['stall_id'] : null;

        if ($stallId) {
            $stall = DB::table('ocop_products')->where('id', $stallId)->first();
            if ($stall) {
                $eateryId = $stall->eatery_id;
            }
        }

        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
            $eateryId = $managerEatery ? $managerEatery->id : $user->eatery_id;
        }

        $updateData = [
            'name'      => $data['name'],
            'username'  => $data['username'],
            'email'     => $data['email'] ?? null,
            'role'      => $data['role'],
            'avatar'    => $data['avatar'] ?? '👨‍🍳',
            'phone'     => $data['phone'] ?? null,
            'status'    => $data['status'] ?? 'active',
            'eatery_id' => $eateryId,
            'stall_id'  => $stallId,
        ];

        if ($role === 'admin' && isset($data['is_verified'])) {
            $updateData['is_verified'] = (bool) $data['is_verified'];
        }

        if (!empty($data['password'])) {
            $updateData['password'] = Hash::make($data['password']);
        }

        $user->update($updateData);

        if ($stallId) {
            DB::table('ocop_products')->where('id', $stallId)->update(['user_id' => $user->id]);
            DB::connection('mysql_market')->table('ocop_products')->where('id', $stallId)->update([
                'user_id'      => $user->id,
                'seller_name'  => $user->name,
                'seller_phone' => $user->phone,
            ]);
        }

        if ($eateryId) {
            foreach (['mysql', 'mysql_education', 'mysql_market', 'mysql_stay', 'mysql_wellness', 'mysql_culture'] as $conn) {
                rescue(fn() => DB::connection($conn)->table('eateries')->where('id', $eateryId)->update(['user_id' => $user->id]), report: false);
            }
        }

        return $user;
    }

    public function destroyUser(int $id, ?int $currentUserId, ?string $role): string
    {
        $user = User::findOrFail($id);
        if ($role === 'manager' && $user->role !== 'seller') {
            abort(403, 'Ban Quản Lý Chợ chỉ được quyền quản lý tiểu thương trong chợ của mình!');
        }

        if ($user->id === $currentUserId) {
            throw new \RuntimeException('Bạn không được phép tự xóa tài khoản của chính mình!');
        }

        DB::connection('mysql_market')->table('ocop_products')->where('user_id', $user->id)->update(['user_id' => null]);
        $userName = $user->name;
        $user->delete();

        return $userName;
    }

    public function toggleUserStatus(int $id, ?int $currentUserId, ?string $role): string
    {
        $user = User::findOrFail($id);
        if ($role === 'manager' && $user->role !== 'seller') {
            abort(403, 'Ban Quản Lý Chợ chỉ được quyền quản lý tiểu thương trong chợ của mình!');
        }

        if ($user->id === $currentUserId) {
            throw new \RuntimeException('Bạn không thể tự vô hiệu hóa tài khoản của chính mình!');
        }

        $user->status = $user->status === 'active' ? 'disabled' : 'active';
        $user->save();

        return $user->status === 'active' ? 'Kích hoạt tài khoản thành công!' : 'Đã vô hiệu hóa tài khoản thành công!';
    }

    public function storeAnnouncement(int $eateryId, array $data, ?int $userId, ?string $userRole): void
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        $eatery = null;
        $connName = null;
        foreach ($connections as $conn) {
            $e = DB::connection($conn)->table('eateries')->where('id', $eateryId)->first();
            if ($e) {
                $eatery = $e;
                $connName = $conn;
                break;
            }
        }

        if (!$eatery) {
            abort(404, 'Chợ / Cơ sở không tồn tại!');
        }

        if (in_array($userRole, ['seller', 'manager']) && $eatery->user_id !== $userId) {
            $user = User::find($userId);
            $cleanName = $user ? str_replace(['Ban Quản lý ', 'Ban quản lý ', 'BQL '], '', $user->name) : '';
            if (!$cleanName || !str_contains(mb_strtolower($eatery->name), mb_strtolower($cleanName))) {
                abort(403, 'Bạn không có quyền đăng bản tin số cho Chợ này!');
            }
        }

        $existing = json_decode($eatery->announcements ?? '[]', true) ?: [];
        $newId = count($existing) > 0 ? (max(array_column($existing, 'id') ?: [0]) + 1) : 1;

        $item = [
            'id'         => $newId,
            'tag'        => $data['tag'],
            'title'      => $data['title'],
            'content'    => $data['content'],
            'time'       => $data['time'] ?: 'Mới cập nhật',
            'color'      => $data['color'] ?: '#10B981',
            'created_at' => now()->format('H:i d/m/Y'),
        ];

        array_unshift($existing, $item);

        DB::connection($connName)->table('eateries')->where('id', $eateryId)->update([
            'announcements' => json_encode($existing, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function destroyAnnouncement(int $eateryId, int $announcementId, ?int $userId, ?string $userRole): void
    {
        $connections = ['mysql', 'mysql_stay', 'mysql_wellness', 'mysql_market', 'mysql_education', 'mysql_culture'];
        $eatery = null;
        $connName = null;
        foreach ($connections as $conn) {
            $e = DB::connection($conn)->table('eateries')->where('id', $eateryId)->first();
            if ($e) {
                $eatery = $e;
                $connName = $conn;
                break;
            }
        }

        if (!$eatery) abort(404, 'Chợ / Cơ sở không tồn tại!');

        $existing = json_decode($eatery->announcements ?? '[]', true) ?: [];
        $filtered = array_values(array_filter($existing, fn($item) => (int)($item['id'] ?? 0) !== (int)$announcementId));

        DB::connection($connName)->table('eateries')->where('id', $eateryId)->update([
            'announcements' => json_encode($filtered, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function getStallsData(array $filters, ?int $managerUserId, ?string $role, int $page = 1, int $perPage = 10): array
    {
        $managerEatery = null;
        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
        }

        $query = DB::connection('mysql_market')->table('ocop_products');
        if ($role === 'manager' && $managerEatery) {
            $query->where('eatery_id', $managerEatery->id);
        }

        $allStalls = (clone $query)->get();

        $totalHoKinhDoanh = $allStalls->unique(fn($item) => trim($item->stall_name) ?: trim($item->seller_name))->count();
        $totalMatHang = $allStalls->count();
        $hoCoQr = round($totalHoKinhDoanh * 0.88);
        $hoThanhToanSoPct = 88;
        $hoSmartphone = round($totalHoKinhDoanh * 0.88);
        $hoTkNganHang = round($totalHoKinhDoanh * 0.88);

        $catStats = ['Ăn uống' => 0, 'Rau củ' => 0, 'Thực phẩm khô' => 0, 'Thịt tươi' => 0, 'Khác' => 0];
        foreach ($allStalls as $st) {
            $sName = mb_strtolower($st->stall_name . ' ' . $st->name);
            if (str_contains($sName, 'ăn') || str_contains($sName, 'bún') || str_contains($sName, 'chả') || str_contains($sName, 'bánh') || str_contains($sName, 'ẩm thực')) {
                $catStats['Ăn uống']++;
            } elseif (str_contains($sName, 'rau') || str_contains($sName, 'hoa quả') || str_contains($sName, 'nông sản')) {
                $catStats['Rau củ']++;
            } elseif (str_contains($sName, 'khô') || str_contains($sName, 'gạo') || str_contains($sName, 'miến')) {
                $catStats['Thực phẩm khô']++;
            } elseif (str_contains($sName, 'thịt') || str_contains($sName, 'lợn') || str_contains($sName, 'bò') || str_contains($sName, 'gà')) {
                $catStats['Thịt tươi']++;
            } else {
                $catStats['Khác']++;
            }
        }

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "{$search}%")
                  ->orWhere('slug', 'like', "{$search}%")
                  ->orWhere('stall_name', 'like', "{$search}%")
                  ->orWhere('seller_name', 'like', "{$search}%")
                  ->orWhere('seller_phone', 'like', "{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $query->where('stall_name', 'like', "{$filters['category']}%");
        }

        if (!empty($filters['market_id'])) {
            $query->where('eatery_id', (int)$filters['market_id']);
        }

        $rawProducts = $query->orderBy('id', 'desc')->get();

        $markets = DB::table('eateries')
            ->whereIn('id', DB::table('ocop_products')->select('eatery_id')->distinct())
            ->orderBy('name')
            ->get();
        $marketsMap = $markets->keyBy('id');

        $groupedStalls = $rawProducts->groupBy(fn($item) => trim($item->stall_name) ?: trim($item->seller_name))
            ->map(function ($prods, $stallKey) use ($marketsMap) {
                $first = $prods->first();
                $imagePath = $prods->whereNotNull('image_path')->filter(fn($p) => !empty($p->image_path))->first()?->image_path ?: $first->image_path;
                $bankItem = $prods->filter(fn($p) => !empty($p->bank_account) && !empty($p->bank_name))->first()
                         ?: $prods->filter(fn($p) => !empty($p->bank_account))->first()
                         ?: $first;
                $phoneItem = $prods->filter(fn($p) => !empty($p->seller_phone) && $p->seller_phone !== 'Cần cập nhật thông tin')->first() ?: $first;
                $marketObj = $marketsMap[$first->eatery_id] ?? null;

                return (object)[
                    'id'             => $first->id,
                    'stall_name'     => $first->stall_name ?: $stallKey,
                    'seller_name'    => $first->seller_name ?: $stallKey,
                    'seller_phone'   => $phoneItem->seller_phone,
                    'bank_name'      => $bankItem->bank_name,
                    'bank_account'   => $bankItem->bank_account,
                    'image_path'     => $imagePath,
                    'eatery_id'      => $first->eatery_id,
                    'eatery_name'    => $marketObj ? $marketObj->name : null,
                    'eatery_slug'    => $marketObj ? $marketObj->slug : null,
                    'products'       => $prods,
                    'products_count' => $prods->count(),
                ];
            })->values();

        $stalls = new LengthAwarePaginator(
            $groupedStalls->slice(($page - 1) * $perPage, $perPage)->values(),
            $groupedStalls->count(),
            $perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return compact(
            'stalls', 'managerEatery', 'markets', 'marketsMap',
            'totalHoKinhDoanh', 'totalMatHang', 'hoCoQr', 'hoThanhToanSoPct',
            'hoSmartphone', 'hoTkNganHang', 'catStats'
        );
    }

    public function getCreateStallData(?int $managerUserId, ?string $role): array
    {
        $managerEatery = null;
        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
        }

        $markets = DB::connection('mysql_market')->table('eateries')->get();
        $existingStallNames = DB::connection('mysql_market')
            ->table('ocop_products')
            ->whereNotNull('stall_name')
            ->where('stall_name', '!=', '')
            ->pluck('stall_name')
            ->unique()
            ->values();

        return compact('markets', 'managerEatery', 'existingStallNames');
    }

    public function storeStall(array $data, ?int $managerUserId, ?string $role, ?UploadedFile $qrFile, ?string $qrUrl, array $imageFiles, ?UploadedFile $singleImage, ?string $singleImageUrl, ?string $multipleImageUrls): void
    {
        $eateryId = $data['eatery_id'];
        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
            if ($managerEatery) {
                $eateryId = $managerEatery->id;
            }
        }

        $bankName = trim($data['bank_name'] ?? '');
        $bankAccount = trim($data['bank_account'] ?? '');
        $bankHolder = mb_strtoupper(trim($data['bank_holder'] ?? $data['seller_name'] ?? ''));

        $qrCodePath = null;
        if ($qrFile) {
            $qrCodePath = R2Helper::upload($qrFile, 'qrcodes');
        } elseif (!empty($qrUrl)) {
            $qrCodePath = $qrUrl;
        } elseif (!empty($bankAccount)) {
            $bankCodeMap = [
                'MBBANK' => 'MB', 'MB' => 'MB', 'VIETCOMBANK' => 'VCB', 'VCB' => 'VCB',
                'AGRIBANK' => 'VBA', 'VBA' => 'VBA', 'TECHCOMBANK' => 'TCB', 'TCB' => 'TCB',
                'BIDV' => 'BIDV', 'VPBANK' => 'VPB', 'VPB' => 'VPB', 'VIETINBANK' => 'CTG',
                'CTG' => 'CTG', 'TPBANK' => 'TPB', 'TPB' => 'TPB', 'SACOMBANK' => 'STB', 'STB' => 'STB',
            ];
            $cleanBankKey = strtoupper(str_replace([' ', 'NGÂN HÀNG', 'NH'], '', $bankName));
            $bankCode = $bankCodeMap[$cleanBankKey] ?? 'MB';
            $qrCodePath = "https://img.vietqr.io/image/{$bankCode}-{$bankAccount}-compact.png?accountName=" . urlencode($bankHolder) . "&addInfo=" . urlencode("TT " . ($data['stall_name'] ?? ''));
        }

        $imagePaths = [];
        if (!empty($imageFiles)) {
            foreach ($imageFiles as $imgFile) {
                if ($imgFile && $imgFile->isValid()) {
                    $imagePaths[] = R2Helper::upload($imgFile, 'ocop');
                }
            }
        } elseif ($singleImage) {
            $imagePaths[] = R2Helper::upload($singleImage, 'ocop');
        }

        if (!empty($multipleImageUrls)) {
            $urls = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $multipleImageUrls)));
            foreach ($urls as $u) {
                if (!empty($u)) $imagePaths[] = $u;
            }
        } elseif (!empty($singleImageUrl)) {
            $imagePaths[] = $singleImageUrl;
        }

        $imagePaths = array_values(array_unique(array_filter($imagePaths)));
        $imagePath = count($imagePaths) > 1 ? json_encode($imagePaths, JSON_UNESCAPED_SLASHES) : (!empty($imagePaths) ? $imagePaths[0] : null);

        DB::connection('mysql_market')->table('ocop_products')->insert([
            'eatery_id'    => $eateryId,
            'stall_name'   => $data['stall_name'] ?? $data['name'],
            'seller_name'  => $data['seller_name'] ?? 'Chợ Văn hóa Du lịch Cổ Loa',
            'seller_phone' => $data['seller_phone'] ?? 'Ban Quản lý Chợ Cổ Loa',
            'bank_name'    => $bankName,
            'bank_account' => $bankAccount,
            'bank_holder'  => $bankHolder,
            'qr_code_path' => $qrCodePath,
            'name'         => $data['name'],
            'price'        => $data['price'] ?? null,
            'unit'         => $data['unit'] ?? null,
            'star_rating'  => $data['star_rating'] ?? 'OCOP / Đặc sản',
            'description'  => $data['description'] ?? null,
            'image_path'   => $imagePath,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        Cache::flush();
    }

    public function getStallForEdit(int $id, ?int $managerUserId, ?string $role): array
    {
        $stall = DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->first();
        if (!$stall) abort(404, 'Gian hàng không tồn tại!');

        $managerEatery = null;
        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
            if ($managerEatery && $stall->eatery_id != $managerEatery->id) {
                abort(403, 'Bạn không có quyền chỉnh sửa gian hàng của chợ người khác!');
            }
        }

        $markets = DB::connection('mysql_market')->table('eateries')->get();
        return compact('stall', 'markets', 'managerEatery');
    }

    public function updateStall(int $id, array $data, ?int $managerUserId, ?string $role, ?UploadedFile $qrFile, ?string $qrUrl, array $imageFiles, ?UploadedFile $singleImage, ?string $singleImageUrl, ?string $multipleImageUrls): void
    {
        $stall = DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->first();
        if (!$stall) abort(404, 'Gian hàng không tồn tại!');

        $eateryId = $data['eatery_id'];
        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
            if ($managerEatery) {
                $eateryId = $managerEatery->id;
            }
        }

        $newImagePaths = [];
        if (!empty($imageFiles)) {
            foreach ($imageFiles as $imgFile) {
                if ($imgFile && $imgFile->isValid()) {
                    $newImagePaths[] = R2Helper::upload($imgFile, 'stalls');
                }
            }
        } elseif ($singleImage) {
            $newImagePaths[] = R2Helper::upload($singleImage, 'stalls');
        }

        if (!empty($multipleImageUrls)) {
            $urls = array_filter(array_map('trim', preg_split('/[\r\n,]+/', $multipleImageUrls)));
            foreach ($urls as $u) {
                if (!empty($u)) $newImagePaths[] = $u;
            }
        } elseif (!empty($singleImageUrl)) {
            $newImagePaths[] = $singleImageUrl;
        }

        $newImagePaths = array_values(array_unique(array_filter($newImagePaths)));
        $imagePath = !empty($newImagePaths) ? (count($newImagePaths) > 1 ? json_encode($newImagePaths, JSON_UNESCAPED_SLASHES) : $newImagePaths[0]) : $stall->image_path;

        $bankName = trim($data['bank_name'] ?? '');
        $bankAccount = trim($data['bank_account'] ?? '');
        $bankHolder = mb_strtoupper(trim($data['bank_holder'] ?? $data['seller_name'] ?? ''));

        $qrCodePath = null;
        if ($qrFile) {
            $qrCodePath = R2Helper::upload($qrFile, 'qrcodes');
        } elseif (!empty($qrUrl)) {
            $qrCodePath = $qrUrl;
        } elseif (!empty($bankAccount)) {
            $bankCodeMap = [
                'MBBANK' => 'MB', 'MB' => 'MB', 'VIETCOMBANK' => 'VCB', 'VCB' => 'VCB',
                'AGRIBANK' => 'VBA', 'VBA' => 'VBA', 'TECHCOMBANK' => 'TCB', 'TCB' => 'TCB',
                'BIDV' => 'BIDV', 'VPBANK' => 'VPB', 'VPB' => 'VPB', 'VIETINBANK' => 'CTG',
                'CTG' => 'CTG', 'TPBANK' => 'TPB', 'TPB' => 'TPB', 'SACOMBANK' => 'STB', 'STB' => 'STB',
            ];
            $cleanBankKey = strtoupper(str_replace([' ', 'NGÂN HÀNG', 'NH'], '', $bankName));
            $bankCode = $bankCodeMap[$cleanBankKey] ?? 'MB';
            $qrCodePath = "https://img.vietqr.io/image/{$bankCode}-{$bankAccount}-compact.png?accountName=" . urlencode($bankHolder) . "&addInfo=" . urlencode("TT " . ($data['stall_name'] ?? ''));
        }

        DB::connection('mysql_market')
            ->table('ocop_products')
            ->where('eatery_id', $eateryId)
            ->where('stall_name', $stall->stall_name)
            ->update([
                'stall_name'   => $data['stall_name'] ?? $stall->stall_name,
                'seller_name'  => $data['seller_name'] ?? $stall->seller_name,
                'seller_phone' => $data['seller_phone'] ?? 'Cần cập nhật thông tin',
                'bank_name'    => $bankName ?: null,
                'bank_account' => $bankAccount ?: null,
                'bank_holder'  => $bankHolder ?: null,
                'qr_code_path' => $qrCodePath ?: $stall->qr_code_path,
                'updated_at'   => now(),
            ]);

        DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->update([
            'name'        => $data['name'],
            'price'       => $data['price'] ?? null,
            'unit'        => $data['unit'] ?? null,
            'star_rating' => $data['star_rating'] ?? null,
            'description' => $data['description'] ?? null,
            'image_path'  => $imagePath,
            'updated_at'  => now(),
        ]);

        Cache::flush();
    }

    public function destroyStall(int $id, ?int $managerUserId, ?string $role): void
    {
        $stall = DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->first();
        if (!$stall) abort(404, 'Gian hàng không tồn tại!');

        if ($role === 'manager') {
            $managerEatery = DB::connection('mysql_market')->table('eateries')->where('user_id', $managerUserId)->first()
                          ?: EateryApiService::getEateries()->firstWhere('user_id', $managerUserId);
            if ($managerEatery && $stall->eatery_id != $managerEatery->id) {
                abort(403, 'Bạn không có quyền xóa gian hàng của chợ người khác!');
            }
        }

        DB::connection('mysql_market')->table('ocop_products')->where('id', $id)->delete();
        Cache::flush();
    }

    public function resolveReport(int $id, string $action, ?int $adminId, string $duration, ?string $note): string
    {
        ModerationService::resolveTicket($id, $action, $adminId, $duration, $note);

        if ($action === 'remove') {
            return 'Đã gỡ bỏ nội dung vi phạm thành công!';
        } elseif ($action === 'ban') {
            return "Đã thực thi khóa tài khoản tác giả ({$duration}) và gỡ bài viết!";
        } elseif ($action === 'dismiss') {
            return 'Đã bác bỏ báo cáo và xác nhận nội dung an toàn!';
        }

        return 'Đã xử lý báo cáo vi phạm thành công!';
    }
}
