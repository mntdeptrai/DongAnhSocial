<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\EateryApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ở đây định nghĩa toàn bộ các API endpoint giao tiếp giữa các phân hệ (Food Map,
| Stay, Wellness, Market, Education) thông qua EateryApiController.
|
| BẢO MẬT:
|  - Route GET công khai: categories, communes, eateries, videos (chỉ đọc)
|  - Route ghi (POST/PUT/DELETE): bắt buộc đăng nhập qua session web (auth)
|
*/

Route::prefix('v1')->group(function () {

    // -----------------------------------------------------------------------
    // MOBILE LOCKET APP ROUTES (Xác thực qua Sanctum & Hỗ trợ Khách vãng lai)
    // -----------------------------------------------------------------------
    Route::post('/auth/token', [\App\Http\Controllers\Api\AuthApiController::class, 'issueToken']);
    Route::get('/checkins/feed', [\App\Http\Controllers\Api\CheckinApiController::class, 'getFeed']);
    Route::post('/checkins', [\App\Http\Controllers\Api\CheckinApiController::class, 'storeCheckin']);
    Route::post('/checkins/comments', [\App\Http\Controllers\Api\CheckinApiController::class, 'storeComment']);
    Route::post('/checkins/{id}/react', [\App\Http\Controllers\Api\CheckinApiController::class, 'reactToCheckin']);

    // Synchronized Cart API endpoints for Mobile App & Web
    Route::get('/cart', [\App\Http\Controllers\GioHangController::class, 'index']);
    Route::post('/cart/add', [\App\Http\Controllers\GioHangController::class, 'store']);
    Route::put('/cart/update/{id}', [\App\Http\Controllers\GioHangController::class, 'update']);
    Route::delete('/cart/remove/{id}', [\App\Http\Controllers\GioHangController::class, 'destroy']);
    Route::post('/cart/clear', [\App\Http\Controllers\GioHangController::class, 'clear']);

    // -----------------------------------------------------------------------
    // FLEXIBLE / MULTI-PROTOCOL API ENDPOINTS
    // -----------------------------------------------------------------------
    // 1. GraphQL API (Flexible query endpoint)
    Route::post('/graphql', [\App\Http\Controllers\Api\GraphQLApiController::class, 'query']);

    // 2. JSON-RPC 2.0 API (Action-oriented / Batch calls)
    Route::post('/rpc', [\App\Http\Controllers\Api\RpcApiController::class, 'handle']);

    // 3. Server-Sent Events (SSE - One-way real-time events)
    Route::get('/stream/events', [\App\Http\Controllers\Api\SseApiController::class, 'streamEvents']);

    // 4. Streaming API (Chunked progressive response for AI)
    Route::post('/stream/ai/generate-tour', [\App\Http\Controllers\Api\StreamApiController::class, 'streamAiTour']);

    // 5 & 6. Webhooks (Event-driven third-party callbacks)
    Route::post('/webhooks/payment', [\App\Http\Controllers\Api\WebhookApiController::class, 'handlePayment']);
    Route::post('/webhooks/sync-stall', [\App\Http\Controllers\Api\WebhookApiController::class, 'syncStall']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/token/revoke', [\App\Http\Controllers\Api\AuthApiController::class, 'revokeToken']);
        
        // FCM Token update
        Route::post('/user/fcm-token', [\App\Http\Controllers\SocialHubController::class, 'updateFcmToken']);

        // Chat routes for mobile
        Route::get('/friends', [\App\Http\Controllers\SocialHubController::class, 'getFriends']);
        Route::get('/messages/{friendId}', [\App\Http\Controllers\SocialHubController::class, 'getMessages']);
        Route::post('/messages', [\App\Http\Controllers\SocialHubController::class, 'sendMessage']);
        
        // Unread check for native background polling
        Route::get('/social/unread-check', [\App\Http\Controllers\SocialHubController::class, 'checkUnread']);
        
        // Checkin history for mobile
        Route::get('/checkins/my', [\App\Http\Controllers\Api\CheckinApiController::class, 'getMyCheckins']);

        // Admin Management APIs (Đầy đủ 100% chức năng như Web Admin)
        Route::get('/admin/users', [\App\Http\Controllers\Api\AdminApiController::class, 'getAdminUsers']);
        Route::post('/admin/users', [\App\Http\Controllers\Api\AdminApiController::class, 'storeUser']);
        Route::delete('/admin/users/{id}', [\App\Http\Controllers\Api\AdminApiController::class, 'deleteUser']);
        Route::post('/admin/users/{id}/role', [\App\Http\Controllers\Api\AdminApiController::class, 'updateUserRole']);

        Route::get('/admin/dashboard', [\App\Http\Controllers\Api\AdminApiController::class, 'getAdminDashboardData']);
        Route::post('/admin/eateries', [\App\Http\Controllers\Api\AdminApiController::class, 'storeEatery']);
        Route::put('/admin/eateries/{id}', [\App\Http\Controllers\Api\AdminApiController::class, 'updateEatery']);
        Route::post('/admin/eateries/{id}/toggle-featured', [\App\Http\Controllers\Api\AdminApiController::class, 'toggleEateryFeatured']);
        Route::delete('/admin/eateries/{id}', [\App\Http\Controllers\Api\AdminApiController::class, 'deleteEatery']);

        Route::delete('/admin/reviews/{id}', [\App\Http\Controllers\Api\AdminApiController::class, 'deleteReview']);
        Route::post('/admin/categories', [\App\Http\Controllers\Api\AdminApiController::class, 'storeCategory']);
    });

    // -----------------------------------------------------------------------
    // PUBLIC ROUTES — Không cần đăng nhập (chỉ đọc dữ liệu)
    // -----------------------------------------------------------------------
    Route::get('/categories', [EateryApiController::class, 'getCategories']);
    Route::get('/communes', [EateryApiController::class, 'getCommunes']);
    Route::get('/market-products', [EateryApiController::class, 'getMarketProducts']);
    Route::get('/notifications', [EateryApiController::class, 'getAppNotifications']);
    Route::get('/seller/profile', [\App\Http\Controllers\Api\SellerApiController::class, 'getSellerProfile']);
    Route::post('/seller/profile', [\App\Http\Controllers\Api\SellerApiController::class, 'updateSellerProfile']);
    Route::get('/seller/orders', [\App\Http\Controllers\Api\SellerApiController::class, 'getSellerOrders']);
    Route::get('/seller/dashboard-data', [\App\Http\Controllers\Api\SellerApiController::class, 'getSellerDashboardData']);
    Route::post('/seller/dishes', [\App\Http\Controllers\Api\SellerApiController::class, 'storeDish']);
    Route::delete('/seller/dishes/{id}', [\App\Http\Controllers\Api\SellerApiController::class, 'deleteDish']);
    Route::post('/seller/orders/{id}/status', [\App\Http\Controllers\Api\SellerApiController::class, 'updateOrderStatus']);

    Route::get('/manager/dashboard-data', [\App\Http\Controllers\Api\ManagerApiController::class, 'getManagerDashboardData']);
    Route::post('/manager/bulletins', [\App\Http\Controllers\Api\ManagerApiController::class, 'storeManagerBulletin']);
    Route::post('/manager/stalls/{id}/status', [\App\Http\Controllers\Api\ManagerApiController::class, 'updateStallStatus']);

    // Videos Reels đặc sản (xem công khai)
    Route::get('/videos', [EateryApiController::class, 'getVideos']);
    Route::post('/videos/{id}/like', [EateryApiController::class, 'likeVideo'])->middleware('throttle:30,1');

    // Đọc dữ liệu địa điểm theo danh mục
    Route::get('/{category}/eateries', [EateryApiController::class, 'index']);
    Route::get('/{category}/eateries/{slug}', [EateryApiController::class, 'show']);

    // Food Tours (xem công khai)
    Route::get('/food-tours', [EateryApiController::class, 'getFoodTours']);
    Route::get('/food-tours/{slug}', [EateryApiController::class, 'getFoodTour']);

    // Auth API
    Route::post('/auth/login', [\App\Http\Controllers\Api\AuthApiController::class, 'apiLogin'])->middleware('throttle:10,1');
    Route::post('/auth/register', [\App\Http\Controllers\Api\AuthApiController::class, 'apiRegister'])->middleware('throttle:5,1');

    // -----------------------------------------------------------------------
    // PROTECTED ROUTES — Bắt buộc đăng nhập (auth web session)
    // -----------------------------------------------------------------------
    Route::middleware(['auth'])->group(function () {

        // Tải lên tệp đa phương tiện (Tải lên nhiều ảnh/video tối đa 500MB)
        Route::post('/upload', [\App\Http\Controllers\Api\UploadApiController::class, 'upload'])->middleware('throttle:uploads');
        Route::post('/upload-chunk', [\App\Http\Controllers\Api\UploadApiController::class, 'uploadChunk'])->middleware('throttle:uploads');

        // Logout
        Route::post('/auth/logout', [\App\Http\Controllers\Api\AuthApiController::class, 'apiLogout']);

        // CRUD Eateries / Địa điểm (chỉ admin/seller)
        Route::post('/{category}/eateries', [EateryApiController::class, 'store']);
        Route::put('/{category}/eateries/{id}', [EateryApiController::class, 'update']);
        Route::delete('/{category}/eateries/{id}', [EateryApiController::class, 'destroy']);

        // Reviews & Đánh giá
        Route::post('/{category}/eateries/{id}/reviews', [EateryApiController::class, 'storeReview']);
        Route::delete('/reviews/{id}', [EateryApiController::class, 'destroyReview']);
        Route::post('/reviews/{id}/reply', [EateryApiController::class, 'replyReview']);

        // CRUD Dishes (Thực đơn / Món ăn)
        Route::post('/dishes', [EateryApiController::class, 'storeDish']);
        Route::put('/dishes/{id}', [EateryApiController::class, 'updateDish']);
        Route::post('/dishes/{id}/toggle-signature', [EateryApiController::class, 'toggleSignatureDish']);
        Route::delete('/dishes/{id}', [EateryApiController::class, 'destroyDish']);

        // Video management (Admin/Seller)
        Route::post('/videos', [EateryApiController::class, 'storeVideo']);
        Route::put('/videos/{id}', [EateryApiController::class, 'updateVideo']);
        Route::delete('/videos/{id}', [EateryApiController::class, 'destroyVideo']);
        Route::post('/videos/{id}/approve', [EateryApiController::class, 'approveVideo']);
        Route::post('/videos/{id}/reject', [EateryApiController::class, 'rejectVideo']);

        // Trust Hub Management (Certificates, contracts, invoices, logs)
        Route::post('/trust/certificate', [EateryApiController::class, 'storeFoodSafetyCertificate']);
        Route::post('/trust/logs', [EateryApiController::class, 'storeDailyFoodLog']);
        Route::delete('/trust/logs/{id}', [EateryApiController::class, 'destroyDailyFoodLog']);
        Route::post('/trust/contracts', [EateryApiController::class, 'storeFoodSupplyContract']);
        Route::delete('/trust/contracts/{id}', [EateryApiController::class, 'destroyFoodSupplyContract']);
        Route::post('/trust/invoices', [EateryApiController::class, 'storePurchaseInvoice']);
        Route::delete('/trust/invoices/{id}', [EateryApiController::class, 'destroyPurchaseInvoice']);

        // CRUD Rooms (Stay - Phòng nghỉ)
        Route::post('/rooms', [EateryApiController::class, 'storeRoom']);
        Route::put('/rooms/{id}', [EateryApiController::class, 'updateRoom']);
        Route::delete('/rooms/{id}', [EateryApiController::class, 'destroyRoom']);

        // CRUD Wellness Services (Wellness - Dịch vụ sức khỏe)
        Route::post('/wellness-services', [EateryApiController::class, 'storeWellnessService']);
        Route::put('/wellness-services/{id}', [EateryApiController::class, 'updateWellnessService']);
        Route::delete('/wellness-services/{id}', [EateryApiController::class, 'destroyWellnessService']);

        // CRUD OCOP Products (Market - Sản phẩm OCOP)
        Route::post('/ocop-products', [EateryApiController::class, 'storeOcopProduct']);
        Route::put('/ocop-products/{id}', [EateryApiController::class, 'updateOcopProduct']);
        Route::delete('/ocop-products/{id}', [EateryApiController::class, 'destroyOcopProduct']);

        // CRUD Education Programs (Education - Chương trình đào tạo)
        Route::post('/education-programs', [EateryApiController::class, 'storeEducationProgram']);
        Route::put('/education-programs/{id}', [EateryApiController::class, 'updateEducationProgram']);
        Route::delete('/education-programs/{id}', [EateryApiController::class, 'destroyEducationProgram']);

        // CRUD Cultural Activities (Heritage/Culture - Hoạt động văn hóa)
        Route::post('/cultural-activities', [EateryApiController::class, 'storeCulturalActivity']);
        Route::put('/cultural-activities/{id}', [EateryApiController::class, 'updateCulturalActivity']);
        Route::delete('/cultural-activities/{id}', [EateryApiController::class, 'destroyCulturalActivity']);

        // Food Tour AI & Diary (cần đăng nhập)
        Route::post('/food-tours/generate-ai', [EateryApiController::class, 'generateAITour']);
        Route::post('/food-tours/{id}/diary', [EateryApiController::class, 'storeFoodTourDiary']);

        // User Management — Chỉ Admin được thực hiện
        Route::middleware(['role:admin'])->group(function () {
            Route::get('/users', [\App\Http\Controllers\Api\AdminApiController::class, 'getUsers']);
            Route::post('/users', [\App\Http\Controllers\Api\AdminApiController::class, 'storeUserWeb']);
            Route::put('/users/{id}', [\App\Http\Controllers\Api\AdminApiController::class, 'updateUserWeb']);
            Route::delete('/users/{id}', [\App\Http\Controllers\Api\AdminApiController::class, 'destroyUser']);
            Route::post('/users/{id}/toggle-status', [\App\Http\Controllers\Api\AdminApiController::class, 'toggleUserStatus']);
        });
    });
});
