<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind Nong San catalog & order inquiry interfaces (SOLID - Dependency Inversion Principle)
        $this->app->bind(
            \App\Contracts\NongSanCatalogInterface::class,
            \App\Services\NongSanCatalogService::class
        );

        $this->app->bind(
            \App\Contracts\OrderInquiryServiceInterface::class,
            \App\Services\OrderInquiryService::class
        );

        $this->app->bind(
            \App\Contracts\ModerationServiceInterface::class,
            \App\Services\ModerationService::class
        );

        $this->app->bind(
            \App\Contracts\CommentServiceInterface::class,
            \App\Services\CommentService::class
        );

        $this->app->bind(
            \App\Contracts\OcopProductServiceInterface::class,
            \App\Services\OcopProductService::class
        );

        $this->app->bind(
            \App\Contracts\SearchServiceInterface::class,
            \App\Services\SearchService::class
        );

        $this->app->bind(
            \App\Contracts\YouTubeAuthServiceInterface::class,
            \App\Services\YouTubeAuthService::class
        );

        $this->app->bind(
            \App\Contracts\ManagerOrderServiceInterface::class,
            \App\Services\ManagerOrderService::class
        );

        $this->app->bind(
            \App\Contracts\CartServiceInterface::class,
            \App\Services\CartService::class
        );

        $this->app->bind(
            \App\Contracts\MarketStallServiceInterface::class,
            \App\Services\MarketStallService::class
        );

        $this->app->bind(
            \App\Contracts\EateryServiceInterface::class,
            \App\Services\EateryService::class
        );

        $this->app->bind(
            \App\Contracts\MarketChatServiceInterface::class,
            \App\Services\MarketChatService::class
        );

        $this->app->bind(
            \App\Contracts\HealthStationServiceInterface::class,
            \App\Services\HealthStationService::class
        );

        $this->app->bind(
            \App\Contracts\ExperienceCornerServiceInterface::class,
            \App\Services\ExperienceCornerService::class
        );

        $this->app->bind(
            \App\Contracts\FoodTourServiceInterface::class,
            \App\Services\FoodTourService::class
        );

        $this->app->bind(
            \App\Contracts\LiveStreamServiceInterface::class,
            \App\Services\LiveStreamService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\CheckoutServiceInterface::class,
            \App\Services\CheckoutService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\BusinessManagementServiceInterface::class,
            \App\Services\BusinessManagementService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\SchoolManagementServiceInterface::class,
            \App\Services\SchoolManagementService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\AuthServiceInterface::class,
            \App\Services\AuthService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\SocialHubServiceInterface::class,
            \App\Services\SocialHubService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\VendorServiceInterface::class,
            \App\Services\VendorService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\HomeServiceInterface::class,
            \App\Services\HomeService::class
        );

        $this->app->bind(
            \App\Services\Interfaces\AdminServiceInterface::class,
            \App\Services\AdminService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Tự động ép tất cả các đường dẫn (CSS, JS, links) thành HTTPS khi chạy qua Ngrok
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Đăng ký Rate Limiter cho tải lên tệp tin (tối đa 5 yêu cầu/phút mỗi IP)
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Bạn đã gửi quá nhiều yêu cầu tải lên. Vui lòng thử lại sau 1 phút.'
                    ], 429, $headers);
                });
        });

        // Đăng ký các Observers cho hệ thống Bản đồ số ẩm thực
        \App\Models\Checkin::observe(\App\Observers\CheckinObserver::class);
        \App\Models\FoodTourDiary::observe(\App\Observers\FoodTourDiaryObserver::class);
        \App\Models\Eatery::observe(\App\Observers\EateryObserver::class);
        \App\Models\Dish::observe(\App\Observers\DishObserver::class);
        \App\Models\Review::observe(\App\Observers\ReviewObserver::class);
        \App\Models\ReviewVideo::observe(\App\Observers\ReviewVideoObserver::class);
        \App\Models\FoodSafetyCertificate::observe(\App\Observers\TrustHubObserver::class);
        \App\Models\DailyFoodLog::observe(\App\Observers\TrustHubObserver::class);
        \App\Models\FoodSupplyContract::observe(\App\Observers\TrustHubObserver::class);
        \App\Models\PurchaseInvoice::observe(\App\Observers\TrustHubObserver::class);
        \App\Models\Comment::observe(\App\Observers\CommentObserver::class);

        // Đăng ký Blade Directive @linkify cho tự động nhận diện URL, Email, Phone
        \Illuminate\Support\Facades\Blade::directive('linkify', function ($expression) {
            return "<?php echo \App\Helpers\TextHelper::linkify($expression); ?>";
        });
    }
}
