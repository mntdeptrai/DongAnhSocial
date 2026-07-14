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
    Route::post('/auth/token', [\App\Http\Controllers\Api\CheckinApiController::class, 'issueToken']);
    Route::get('/checkins/feed', [\App\Http\Controllers\Api\CheckinApiController::class, 'getFeed']);
    Route::post('/checkins', [\App\Http\Controllers\Api\CheckinApiController::class, 'storeCheckin']);
    Route::post('/checkins/comments', [\App\Http\Controllers\Api\CheckinApiController::class, 'storeComment']);
    Route::post('/checkins/{id}/react', [\App\Http\Controllers\Api\CheckinApiController::class, 'reactToCheckin']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/token/revoke', [\App\Http\Controllers\Api\CheckinApiController::class, 'revokeToken']);
        
        // Chat routes for mobile
        Route::get('/friends', [\App\Http\Controllers\SocialHubController::class, 'getFriends']);
        Route::get('/messages/{friendId}', [\App\Http\Controllers\SocialHubController::class, 'getMessages']);
        Route::post('/messages', [\App\Http\Controllers\SocialHubController::class, 'sendMessage']);
        
        // Checkin history for mobile
        Route::get('/checkins/my', [\App\Http\Controllers\Api\CheckinApiController::class, 'getMyCheckins']);
    });

    // -----------------------------------------------------------------------
    // PUBLIC ROUTES — Không cần đăng nhập (chỉ đọc dữ liệu)
    // -----------------------------------------------------------------------
    Route::get('/categories', [EateryApiController::class, 'getCategories']);
    Route::get('/communes', [EateryApiController::class, 'getCommunes']);

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
    Route::post('/auth/login', [EateryApiController::class, 'apiLogin'])->middleware('throttle:10,1');
    Route::post('/auth/register', [EateryApiController::class, 'apiRegister'])->middleware('throttle:5,1');

    // -----------------------------------------------------------------------
    // PROTECTED ROUTES — Bắt buộc đăng nhập (auth web session)
    // -----------------------------------------------------------------------
    Route::middleware(['auth'])->group(function () {

        // Tải lên tệp đa phương tiện (Tải lên nhiều ảnh/video tối đa 500MB)
        Route::post('/upload', [\App\Http\Controllers\Api\UploadApiController::class, 'upload'])->middleware('throttle:uploads');

        // Logout
        Route::post('/auth/logout', [EateryApiController::class, 'apiLogout']);

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
            Route::get('/users', [EateryApiController::class, 'getUsers']);
            Route::post('/users', [EateryApiController::class, 'storeUser']);
            Route::put('/users/{id}', [EateryApiController::class, 'updateUser']);
            Route::delete('/users/{id}', [EateryApiController::class, 'destroyUser']);
            Route::post('/users/{id}/toggle-status', [EateryApiController::class, 'toggleUserStatus']);
        });
    });
});
