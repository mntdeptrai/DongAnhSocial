<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\EateryController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FoodTourController;
use App\Http\Controllers\SocialHubController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Ở đây định nghĩa toàn bộ đường dẫn (routes) của Bản đồ số Ẩm thực Đông Anh.
|
*/

// --- USER SIDE ROUTES (Giao diện người dùng) ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tim-kiem', [SearchController::class, 'search'])->name('search');
Route::get('/checkin', [HomeController::class, 'checkinFeed'])->name('checkin.feed');
Route::post('/checkin', [HomeController::class, 'storeCheckin'])->name('checkin.store');
Route::post('/comments', [\App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
Route::get('/api/eateries/search', [HomeController::class, 'searchEateries'])->name('api.eateries.search');
Route::get('/api/eateries/nearby', [HomeController::class, 'nearbyEateries'])->name('api.eateries.nearby');
Route::get('/api/checkins/latest', [HomeController::class, 'latestCheckins'])->name('api.checkins.latest');
Route::post('/api/checkins/{id}/react', [HomeController::class, 'reactToCheckin'])->name('api.checkins.react');

// URL Thân thiện chuẩn SEO Google cho địa điểm ẩm thực & đặc sản
Route::get('/dia-diem/{slug}', [EateryController::class, 'show'])->name('eatery.show');
Route::post('/dia-diem/reviews/{id}', [EateryController::class, 'storeReview'])->name('eatery.review.store');

// SEO Sitemap Route
Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');

// API Video Reels đặc sản Đông Anh (Tóp Tóp Food Tour)
Route::get('/api/videos', [HomeController::class, 'getVideos'])->name('api.videos');
Route::post('/api/videos/{id}/like', [HomeController::class, 'likeVideo'])->name('api.videos.like');

// --- FOOD TOUR JOURNEY ROUTES (Trải nghiệm hành trình ẩm thực) ---
Route::get('/food-tours', [FoodTourController::class, 'index'])->name('food-tours.index');
Route::get('/exp-corner', [FoodTourController::class, 'cookingIndex'])->name('cooking-tours.index');

Route::middleware(['auth'])->group(function () {
    Route::get('/food-tours/create', [FoodTourController::class, 'create'])->name('food-tours.create');
    Route::post('/food-tours', [FoodTourController::class, 'store'])->name('food-tours.store');
    Route::get('/food-tour/{slug}/edit', [FoodTourController::class, 'edit'])->name('food-tours.edit');
    Route::put('/food-tour/{slug}', [FoodTourController::class, 'update'])->name('food-tours.update');
    Route::delete('/food-tour/{slug}', [FoodTourController::class, 'destroy'])->name('food-tours.destroy');
    Route::post('/food-tour/{slug}/share', [FoodTourController::class, 'share'])->name('food-tours.share');
    
    // Quản lý thông tin tài khoản cá nhân & Lộ trình của tôi
    Route::get('/profile', [AuthController::class, 'profile'])->name('profile');
    Route::put('/profile', [AuthController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/avatar', [AuthController::class, 'updateAvatar'])->name('profile.avatar');
    Route::put('/profile/password', [AuthController::class, 'changePassword'])->name('profile.password');
    Route::post('/profile/password/send-otp', [AuthController::class, 'sendOtp'])->name('profile.password.send-otp')->middleware('throttle:5,1');
    Route::post('/user/heartbeat', [AuthController::class, 'heartbeat'])->name('user.heartbeat');

    // --- SOCIAL HUB ROUTES (Inertia + React) ---
    Route::get('/social', [SocialHubController::class, 'index'])->name('social.index');
    Route::get('/social/friends/presence', [SocialHubController::class, 'getFriendsPresence'])->name('social.friends.presence');
    Route::post('/social/location', [SocialHubController::class, 'updateLocation'])->name('social.location');
    Route::get('/social/nearby', [SocialHubController::class, 'getNearby'])->name('social.nearby');
    Route::post('/social/friends', [SocialHubController::class, 'sendFriendRequest'])->name('social.friends.send');
    Route::post('/social/friends/{id}/accept', [SocialHubController::class, 'acceptFriendRequest'])->name('social.friends.accept');
    Route::post('/social/friends/{id}/decline', [SocialHubController::class, 'declineFriendRequest'])->name('social.friends.decline');
    Route::get('/social/messages/{friendId}', [SocialHubController::class, 'getMessages'])->name('social.messages.get');
    Route::post('/social/messages', [SocialHubController::class, 'sendMessage'])->name('social.messages.send')->middleware('throttle:60,1');
    Route::get('/social/search', [SocialHubController::class, 'searchUsers'])->name('social.search');
    Route::get('/social/recent-chats', [SocialHubController::class, 'getRecentChats'])->name('social.recent-chats');
    
    // AI Food Tour Generation (authenticated and rate-limited)
    Route::post('/api/food-tours/generate-ai', [FoodTourController::class, 'generateAI'])
        ->middleware('throttle:3,1')
        ->name('api.food-tours.generate-ai');
});

Route::get('/food-tour/{slug}', [FoodTourController::class, 'show'])->name('food-tours.show');
Route::post('/api/food-tours/{id}/diary', [FoodTourController::class, 'storeDiary'])->name('api.food-tours.store-diary');


// --- SHOPPING CART & CHECKOUT ROUTES (Giỏ hàng & Đặt hàng online) ---
use App\Http\Controllers\GioHangController;
use App\Http\Controllers\CheckoutController;

Route::get('/cart', [GioHangController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [GioHangController::class, 'store'])->name('cart.add');
Route::put('/cart/update/{id}', [GioHangController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{id}', [GioHangController::class, 'destroy'])->name('cart.remove');
Route::post('/cart/clear', [GioHangController::class, 'clear'])->name('cart.clear');

Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/payment/{id}', [CheckoutController::class, 'payment'])->name('checkout.payment');
    Route::post('/checkout/payment/{id}/process', [CheckoutController::class, 'processPayment'])->name('checkout.process-payment');
    Route::get('/checkout/success/{id}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/orders', [CheckoutController::class, 'ordersList'])->name('orders.index');
    Route::get('/orders/{id}', [CheckoutController::class, 'show'])->name('orders.show');
    
    Route::get('/api/orders', [CheckoutController::class, 'apiOrdersList'])->name('api.orders.index');
    Route::get('/api/orders/{id}', [CheckoutController::class, 'apiOrdersShow'])->name('api.orders.show');
    Route::post('/api/orders/{id}/cancel', [CheckoutController::class, 'cancel'])->name('api.orders.cancel');
    Route::post('/api/orders/{id}/reorder', [CheckoutController::class, 'reorder'])->name('api.orders.reorder');
    Route::post('/api/orders/{id}/review', [CheckoutController::class, 'review'])->name('api.orders.review');
});


// --- AUTHENTICATION ROUTES (Đăng nhập / Đăng ký) ---
Route::get('/auth/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1'); // max 10 lần/phút
Route::get('/auth/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:5,1'); // max 5 lần/phút
Route::post('/auth/register/send-otp', [AuthController::class, 'sendRegisterOtp'])->name('register.send-otp')->middleware('throttle:5,1');
Route::get('/auth/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::post('/auth/forgot-password/send-otp', [AuthController::class, 'sendForgotPasswordOtp'])->name('password.send-otp')->middleware('throttle:5,1');
Route::post('/auth/forgot-password/reset', [AuthController::class, 'resetPassword'])->name('password.update');
Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');


// --- ADMIN SIDE ROUTES (Giao diện quản trị viên) ---
// Bắt buộc đăng nhập (auth) và có vai trò phù hợp trước khi vào bất kỳ trang nào trong khu vực quản trị
Route::prefix('admin')->middleware(['auth', 'role:admin,seller'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/eateries/create', [AdminController::class, 'createEatery'])->name('admin.eatery.create');
    Route::post('/eateries', [AdminController::class, 'storeEatery'])->name('admin.eatery.store');
    Route::get('/eateries/{slug}/edit', [AdminController::class, 'editEatery'])->name('admin.eatery.edit');
    Route::put('/eateries/{slug}', [AdminController::class, 'updateEatery'])->name('admin.eatery.update');
    Route::delete('/eateries/{slug}', [AdminController::class, 'destroyEatery'])->name('admin.eatery.destroy');
    Route::post('/parse-google-maps', [AdminController::class, 'parseGoogleMapsUrl'])->name('admin.parse-google-maps');
    
    // Quản lý Thực đơn & Món ăn đặc trưng
    Route::post('/dishes', [AdminController::class, 'storeDish'])->name('admin.dish.store');
    Route::put('/dishes/{id}', [AdminController::class, 'updateDish'])->name('admin.dish.update');
    Route::post('/dishes/{id}/toggle-signature', [AdminController::class, 'toggleSignatureDish'])->name('admin.dish.toggle-signature');
    Route::delete('/dishes/{id}', [AdminController::class, 'destroyDish'])->name('admin.dish.destroy');

    // Quản lý Góc trải nghiệm & Hoạt động văn hóa
    Route::post('/cultural-activities', [AdminController::class, 'storeCulturalActivity'])->name('admin.cultural-activity.store');
    Route::put('/cultural-activities/{id}', [AdminController::class, 'updateCulturalActivity'])->name('admin.cultural-activity.update');
    Route::delete('/cultural-activities/{id}', [AdminController::class, 'destroyCulturalActivity'])->name('admin.cultural-activity.destroy');

    // Quản lý Sản phẩm OCOP (Chợ Đông Anh)
    Route::post('/ocop-products', [AdminController::class, 'storeOcopProduct'])->name('admin.ocop-product.store');
    Route::put('/ocop-products/{id}', [AdminController::class, 'updateOcopProduct'])->name('admin.ocop-product.update');
    Route::delete('/ocop-products/{id}', [AdminController::class, 'destroyOcopProduct'])->name('admin.ocop-product.destroy');

    // Quản lý Phòng nghỉ (Stay in Đông Anh)
    Route::post('/rooms', [AdminController::class, 'storeRoom'])->name('admin.room.store');
    Route::put('/rooms/{id}', [AdminController::class, 'updateRoom'])->name('admin.room.update');
    Route::delete('/rooms/{id}', [AdminController::class, 'destroyRoom'])->name('admin.room.destroy');

    // Quản lý Dịch vụ y tế & Spa (Wellness & Care)
    Route::post('/wellness-services', [AdminController::class, 'storeWellnessService'])->name('admin.wellness-service.store');
    Route::put('/wellness-services/{id}', [AdminController::class, 'updateWellnessService'])->name('admin.wellness-service.update');
    Route::delete('/wellness-services/{id}', [AdminController::class, 'destroyWellnessService'])->name('admin.wellness-service.destroy');

    // Quản lý Chương trình đào tạo (Trường học)
    Route::post('/education-programs', [AdminController::class, 'storeEducationProgram'])->name('admin.education-program.store');
    Route::put('/education-programs/{id}', [AdminController::class, 'updateEducationProgram'])->name('admin.education-program.update');
    Route::delete('/education-programs/{id}', [AdminController::class, 'destroyEducationProgram'])->name('admin.education-program.destroy');

    // Quản lý Video Reels đặc sản
    Route::post('/videos', [AdminController::class, 'storeVideo'])->name('admin.video.store');
    Route::put('/videos/{id}', [AdminController::class, 'updateVideo'])->name('admin.video.update');
    Route::delete('/videos/{id}', [AdminController::class, 'destroyVideo'])->name('admin.video.destroy');

    // Fallback GET routes chống lỗi 405 Method Not Allowed khi truy cập trực tiếp bằng phương thức GET
    Route::get('/dishes', fn() => redirect()->route('admin.dashboard'));
    Route::get('/cultural-activities', fn() => redirect()->route('admin.dashboard'));
    Route::get('/ocop-products', fn() => redirect()->route('admin.dashboard'));
    Route::get('/rooms', fn() => redirect()->route('admin.dashboard'));
    Route::get('/wellness-services', fn() => redirect()->route('admin.dashboard'));
    Route::get('/education-programs', fn() => redirect()->route('admin.dashboard'));
    Route::get('/videos', fn() => redirect()->route('admin.dashboard'));
    Route::post('/videos/{id}/approve', [AdminController::class, 'approveVideo'])->name('admin.video.approve');
    Route::post('/videos/{id}/reject', [AdminController::class, 'rejectVideo'])->name('admin.video.reject');

    // Quản lý Minh Bạch An Toàn & Truy Xuất Thực Phẩm (Trust Hub)
    Route::post('/trust/certificate', [AdminController::class, 'storeFoodSafetyCertificate'])->name('admin.trust.certificate.store');
    Route::post('/trust/logs', [AdminController::class, 'storeDailyFoodLog'])->name('admin.trust.logs.store');
    Route::delete('/trust/logs/{id}', [AdminController::class, 'destroyDailyFoodLog'])->name('admin.trust.logs.destroy');
    Route::post('/trust/contracts', [AdminController::class, 'storeFoodSupplyContract'])->name('admin.trust.contracts.store');
    Route::delete('/trust/contracts/{id}', [AdminController::class, 'destroyFoodSupplyContract'])->name('admin.trust.contracts.destroy');
    Route::post('/trust/invoices', [AdminController::class, 'storePurchaseInvoice'])->name('admin.trust.invoices.store');
    Route::delete('/trust/invoices/{id}', [AdminController::class, 'destroyPurchaseInvoice'])->name('admin.trust.invoices.destroy');

    // Quản lý đánh giá của khách hàng
    Route::delete('/reviews/{id}', [AdminController::class, 'destroyReview'])->name('admin.review.destroy');
    Route::post('/reviews/{id}/reply', [AdminController::class, 'replyReview'])->name('admin.review.reply');

    // Quản lý Ảnh thực tế của cơ sở (Gallery)
    Route::post('/eatery-photos', [AdminController::class, 'storeEateryPhoto'])->name('admin.eatery-photo.store');
    Route::delete('/eatery-photos/{id}', [AdminController::class, 'destroyEateryPhoto'])->name('admin.eatery-photo.destroy');

    // Quản lý tài khoản User (Chỉ dành cho Admin tối cao)
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/users', [AdminController::class, 'indexUsers'])->name('admin.users.index');
        Route::get('/users/create', [AdminController::class, 'createUser'])->name('admin.users.create');
        Route::post('/users', [AdminController::class, 'storeUser'])->name('admin.users.store');
        Route::get('/users/{id}', [AdminController::class, 'showUser'])->name('admin.users.show');
        Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('admin.users.edit');
        Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('admin.users.update');
        Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('admin.users.destroy');
        Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.toggle-status');
    });
});
