<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EateryApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\UserApiController;
use App\Http\Controllers\Api\SellerApiController;
use App\Http\Controllers\Api\PrincipalApiController;
use App\Http\Controllers\Api\ManagerApiController;
use App\Http\Controllers\Api\AdminApiController;
use App\Http\Controllers\Api\CheckinApiController;
use App\Http\Controllers\Api\UploadApiController;
use App\Http\Controllers\GioHangController;
use App\Http\Controllers\SocialHubController;

/*
|--------------------------------------------------------------------------
| API Routes — Restful Mobile App & Web API System (DongAnhSocial)
|--------------------------------------------------------------------------
|
| Hệ thống API phân chia rõ ràng theo 5 Phân quyền (Role):
|  1. User (Thành viên / Khách hàng)
|  2. Seller (Tiểu thương / Chủ gian hàng / OCOP / Ẩm thực)
|  3. Principal (Hiệu trưởng / Quản lý trường học)
|  4. Manager (Ban Quản lý Chợ / Quản lý địa phương)
|  5. Admin (Quản trị viên toàn hệ thống)
|
*/

Route::prefix('v1')->group(function () {

    // -----------------------------------------------------------------------
    // 0. AUTHENTICATION & TOKEN MANAGEMENT (Xác thực Mobile Sanctum)
    // -----------------------------------------------------------------------
    Route::post('/auth/token', [AuthApiController::class, 'issueToken']);
    Route::post('/auth/login', [AuthApiController::class, 'apiLogin'])->middleware('throttle:10,1');
    Route::post('/auth/register', [AuthApiController::class, 'apiRegister'])->middleware('throttle:5,1');

    // -----------------------------------------------------------------------
    // 1. PUBLIC DATA ROUTES (Danh mục, Địa điểm, Food Tours, Videos Reels)
    // -----------------------------------------------------------------------
    Route::get('/categories', [EateryApiController::class, 'getCategories']);
    Route::get('/communes', [EateryApiController::class, 'getCommunes']);
    Route::get('/market-products', [EateryApiController::class, 'getMarketProducts']);
    Route::get('/notifications', [EateryApiController::class, 'getAppNotifications']);
    Route::match(['get', 'post'], '/notifications/read', [EateryApiController::class, 'markAppNotificationsRead']);
    Route::get('/newsfeed', [EateryApiController::class, 'getNewsfeed']);
    Route::get('/exp-corner', [EateryApiController::class, 'getExpCorner']);
    Route::post('/posts', [EateryApiController::class, 'storePost']);
    Route::post('/reactions/toggle', [EateryApiController::class, 'toggleReaction']);
    Route::get('/videos', [EateryApiController::class, 'getVideos']);
    Route::post('/videos/{id}/like', [EateryApiController::class, 'likeVideo'])->middleware('throttle:30,1');

    Route::get('/food-tours', [EateryApiController::class, 'getFoodTours']);
    Route::get('/food-tours/{slug}', [EateryApiController::class, 'getFoodTour']);

    Route::get('/{category}/eateries', [EateryApiController::class, 'index']);
    Route::get('/{category}/eateries/{slug}', [EateryApiController::class, 'show']);

    // Checkins Feed (Xem công khai & Đăng checkin)
    Route::get('/checkins/feed', [CheckinApiController::class, 'getFeed']);
    Route::post('/checkins', [CheckinApiController::class, 'storeCheckin']);
    Route::post('/checkins/comments', [CheckinApiController::class, 'storeComment']);
    Route::post('/checkins/{id}/react', [CheckinApiController::class, 'reactToCheckin']);

    // Cart API (Giỏ hàng đồng bộ Web & App Mobile)
    Route::get('/cart', [GioHangController::class, 'index']);
    Route::post('/cart/add', [GioHangController::class, 'store']);
    Route::put('/cart/update/{id}', [GioHangController::class, 'update']);
    Route::delete('/cart/remove/{id}', [GioHangController::class, 'destroy']);
    Route::post('/cart/clear', [GioHangController::class, 'clear']);

    // Protocols bổ trợ (GraphQL, RPC, SSE, Stream, Webhooks)
    Route::post('/graphql', [\App\Http\Controllers\Api\GraphQLApiController::class, 'query']);
    Route::post('/rpc', [\App\Http\Controllers\Api\RpcApiController::class, 'handle']);
    Route::get('/stream/events', [\App\Http\Controllers\Api\SseApiController::class, 'streamEvents']);
    Route::post('/stream/ai/generate-tour', [\App\Http\Controllers\Api\StreamApiController::class, 'streamAiTour']);
    Route::post('/webhooks/payment', [\App\Http\Controllers\Api\WebhookApiController::class, 'handlePayment']);
    Route::post('/webhooks/sync-stall', [\App\Http\Controllers\Api\WebhookApiController::class, 'syncStall']);

    // -----------------------------------------------------------------------
    // PROTECTED ROUTES — YÊU CẦU XÁC THỰC (Sanctum Token hoặc Web Session)
    // -----------------------------------------------------------------------
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::post('/auth/token/revoke', [AuthApiController::class, 'revokeToken']);
        Route::post('/upload', [UploadApiController::class, 'upload'])->middleware('throttle:uploads');
        Route::post('/upload-chunk', [UploadApiController::class, 'uploadChunk'])->middleware('throttle:uploads');
        Route::post('/profile/avatar', [\App\Http\Controllers\AuthController::class, 'updateAvatar']);
        Route::post('/profile/cover', [\App\Http\Controllers\AuthController::class, 'updateCoverPhoto']);

        // FCM Notification
        Route::post('/user/fcm-token', [SocialHubController::class, 'updateFcmToken']);

        // Chat & Social
        Route::get('/friends', [SocialHubController::class, 'getFriends']);
        Route::get('/messages/{friendId}', [SocialHubController::class, 'getMessages']);
        Route::post('/messages', [SocialHubController::class, 'sendMessage']);
        Route::get('/social/unread-check', [SocialHubController::class, 'checkUnread']);
        Route::get('/search/all', [SocialHubController::class, 'searchAll']);

        // ===================================================================
        // 👤 ROLE 1: USER API (Thành Viên / Khách Hàng)
        // ===================================================================
        Route::prefix('user')->group(function () {
            Route::get('/profile', [UserApiController::class, 'getProfile']);
            Route::put('/profile', [UserApiController::class, 'updateProfile']);
            Route::post('/change-password', [UserApiController::class, 'changePassword']);
            Route::get('/orders', [UserApiController::class, 'getMyOrders']);
            Route::get('/orders/{id}', [UserApiController::class, 'getOrderDetail']);
            Route::get('/checkins', [UserApiController::class, 'getMyCheckins']);
            Route::get('/posts', [UserApiController::class, 'getMyPosts']);
        });

        // ===================================================================
        // 🏪 ROLE 2: SELLER API (Tiểu Thương / Chủ Gian Hàng / OCOP / Ẩm Thực)
        // ===================================================================
        Route::prefix('seller')->group(function () {
            Route::get('/dashboard-data', [SellerApiController::class, 'getSellerDashboardData']);
            Route::get('/profile', [SellerApiController::class, 'getSellerProfile']);
            Route::post('/profile', [SellerApiController::class, 'updateSellerProfile']);
            Route::get('/orders', [SellerApiController::class, 'getSellerOrders']);
            Route::post('/orders/{id}/status', [SellerApiController::class, 'updateOrderStatus']);
            Route::post('/dishes', [SellerApiController::class, 'storeDish']);
            Route::put('/dishes/{id}', [SellerApiController::class, 'updateDish']);
            Route::delete('/dishes/{id}', [SellerApiController::class, 'deleteDish']);
        });

        // ===================================================================
        // 🏫 ROLE 3: PRINCIPAL API (Hiệu Trưởng / Quản Lý Trường Học)
        // ===================================================================
        Route::prefix('principal')->group(function () {
            Route::get('/dashboard-data', [PrincipalApiController::class, 'getPrincipalDashboardData']);
            Route::get('/posts', [PrincipalApiController::class, 'getSchoolPosts']);
            Route::post('/posts', [PrincipalApiController::class, 'storeSchoolPost']);
            Route::delete('/posts/{id}', [PrincipalApiController::class, 'deleteSchoolPost']);
            Route::post('/education-programs', [PrincipalApiController::class, 'storeEducationProgram']);
            Route::delete('/education-programs/{id}', [PrincipalApiController::class, 'deleteEducationProgram']);
        });

        // ===================================================================
        // 🏛️ ROLE 4: MANAGER API (Ban Quản Lý Chợ / Quản Lý Địa Phương)
        // ===================================================================
        Route::prefix('manager')->group(function () {
            Route::get('/dashboard-data', [ManagerApiController::class, 'getManagerDashboardData']);
            Route::post('/bulletins', [ManagerApiController::class, 'storeManagerBulletin']);
            Route::post('/stalls/{id}/status', [ManagerApiController::class, 'updateStallStatus']);
            Route::post('/stalls/{id}/attp-check', [ManagerApiController::class, 'attpCheck']);
        });

        // ===================================================================
        // ⚡ ROLE 5: ADMIN API (Quản Trị Viên Toàn Hệ Thống)
        // ===================================================================
        Route::prefix('admin')->group(function () {
            Route::get('/dashboard', [AdminApiController::class, 'getAdminDashboardData']);
            Route::get('/users', [AdminApiController::class, 'getAdminUsers']);
            Route::post('/users', [AdminApiController::class, 'storeUser']);
            Route::put('/users/{id}', [AdminApiController::class, 'updateUserWeb']);
            Route::delete('/users/{id}', [AdminApiController::class, 'deleteUser']);
            Route::post('/users/{id}/role', [AdminApiController::class, 'updateUserRole']);
            Route::post('/users/{id}/toggle-status', [AdminApiController::class, 'toggleUserStatus']);

            Route::post('/eateries', [AdminApiController::class, 'storeEatery']);
            Route::put('/eateries/{id}', [AdminApiController::class, 'updateEatery']);
            Route::post('/eateries/{id}/toggle-featured', [AdminApiController::class, 'toggleEateryFeatured']);
            Route::delete('/eateries/{id}', [AdminApiController::class, 'deleteEatery']);

            Route::post('/categories', [AdminApiController::class, 'storeCategory']);
            Route::delete('/reviews/{id}', [AdminApiController::class, 'deleteReview']);
        });
    });

    // Backwards compatibility public/session endpoints
    Route::middleware(['auth'])->group(function () {
        Route::post('/auth/logout', [AuthApiController::class, 'apiLogout']);
        Route::post('/food-tours/generate-ai', [EateryApiController::class, 'generateAITour']);
        Route::post('/food-tours/{id}/diary', [EateryApiController::class, 'storeFoodTourDiary']);
    });
});
