<?php

namespace App\Services;

/**
 * EateryApiService - Gateway Facade
 * 
 * Đóng vai trò Facade trung tâm ủy nhiệm (delegation) các cuộc gọi tĩnh
 * sang các Domain Services chuyên trách tương ứng, đảm bảo 100% tương thích ngược
 * với toàn bộ hệ thống routes, controllers, API và Blade views.
 */
class EateryApiService
{
    // =========================================================================
    // 1. Eatery & Public Discovery (Ủy nhiệm cho EateryService)
    // =========================================================================

    public static function getCategories()
    {
        return app(EateryService::class)->getCategories();
    }

    public static function getCommunes()
    {
        return app(EateryService::class)->getCommunes();
    }

    public static function countEateries(?string $categorySlug = null, array $filters = []): int
    {
        return app(EateryService::class)->countEateries($categorySlug, $filters);
    }

    public static function getEateries(?string $categorySlug = null, array $filters = [])
    {
        return app(EateryService::class)->getEateries($categorySlug, $filters);
    }

    public static function fetchEateriesFromCategory(string $categorySlug, array $filters = [])
    {
        return app(EateryService::class)->getEateries($categorySlug, $filters);
    }

    public static function getEateryBySlug(string $slug)
    {
        return app(EateryService::class)->getEateryBySlug($slug);
    }

    public static function fetchEateryBySlug(?string $categorySlug, string $slug)
    {
        return app(EateryService::class)->fetchEateryBySlug($categorySlug, $slug);
    }

    public static function createEatery(string $categorySlug, array $data)
    {
        return app(EateryService::class)->create($data, $categorySlug);
    }

    public static function updateEatery(string $categorySlug, $id, array $data)
    {
        return app(EateryService::class)->update($id, $data, $categorySlug);
    }

    public static function deleteEatery(string $categorySlug, $id): bool
    {
        return app(EateryService::class)->delete($categorySlug, $id);
    }

    public static function storeEateryPhoto(array $data)
    {
        return app(EateryService::class)->storeEateryPhoto($data);
    }

    public static function deleteEateryPhoto(int $id): bool
    {
        return app(EateryService::class)->deleteEateryPhoto($id);
    }

    public static function storeReview(?string $categorySlug, $eateryId, array $data)
    {
        return app(EateryService::class)->storeReview($categorySlug, $eateryId, $data);
    }

    public static function deleteReview($id): bool
    {
        return app(EateryService::class)->deleteReview($id);
    }

    public static function replyReview($id, string $reply)
    {
        return app(EateryService::class)->replyReview($id, $reply);
    }

    public static function hydrateEatery($data)
    {
        return app(EateryService::class)->hydrateEatery($data);
    }

    // =========================================================================
    // 2. Dishes (Ủy nhiệm cho DishService)
    // =========================================================================

    public static function storeDish(array $data)
    {
        return app(DishService::class)->storeDish($data);
    }

    public static function updateDish($id, array $data)
    {
        return app(DishService::class)->update($id, $data);
    }

    public static function toggleSignatureDish($id)
    {
        return app(DishService::class)->toggleSignature($id);
    }

    public static function deleteDish($id): bool
    {
        return app(DishService::class)->delete($id);
    }

    // =========================================================================
    // 3. OCOP Products (Ủy nhiệm cho OcopProductService)
    // =========================================================================

    public static function getOcopProducts(array $filters = [])
    {
        return app(OcopProductService::class)->getOcopProducts($filters);
    }

    public static function storeOcopProduct(array $data)
    {
        return app(OcopProductService::class)->storeOcopProduct($data);
    }

    public static function updateOcopProduct($id, array $data)
    {
        return app(OcopProductService::class)->updateOcopProduct($id, $data);
    }

    public static function deleteOcopProduct($id): bool
    {
        return app(OcopProductService::class)->delete($id);
    }

    // =========================================================================
    // 4. Accommodation Rooms (Ủy nhiệm cho RoomService)
    // =========================================================================

    public static function storeRoom(array $data)
    {
        return app(RoomService::class)->storeRoom($data);
    }

    public static function updateRoom($id, array $data)
    {
        return app(RoomService::class)->updateRoom($id, $data);
    }

    public static function deleteRoom($id): bool
    {
        return app(RoomService::class)->delete($id);
    }

    // =========================================================================
    // 5. Wellness & Medical (Ủy nhiệm cho WellnessMapService)
    // =========================================================================

    public static function storeWellnessService(array $data)
    {
        return app(WellnessMapService::class)->storeWellnessService($data);
    }

    public static function updateWellnessService($id, array $data)
    {
        return app(WellnessMapService::class)->updateWellnessService($id, $data);
    }

    public static function deleteWellnessService($id): bool
    {
        return app(WellnessMapService::class)->delete($id);
    }

    // =========================================================================
    // 6. Education Programs (Ủy nhiệm cho EducationProgramService)
    // =========================================================================

    public static function storeEducationProgram(array $data)
    {
        return app(EducationProgramService::class)->storeEducationProgram($data);
    }

    public static function updateEducationProgram($id, array $data)
    {
        return app(EducationProgramService::class)->updateEducationProgram($id, $data);
    }

    public static function deleteEducationProgram($id): bool
    {
        return app(EducationProgramService::class)->delete($id);
    }

    // =========================================================================
    // 7. Cultural & Heritage (Ủy nhiệm cho CulturalActivityService)
    // =========================================================================

    public static function getAllCulturalActivities()
    {
        return app(CulturalActivityService::class)->getAllCulturalActivities();
    }

    public static function storeCulturalActivity(array $data)
    {
        return app(CulturalActivityService::class)->storeCulturalActivity($data);
    }

    public static function updateCulturalActivity($id, array $data)
    {
        return app(CulturalActivityService::class)->updateCulturalActivity($id, $data);
    }

    public static function deleteCulturalActivity($id): bool
    {
        return app(CulturalActivityService::class)->delete($id);
    }

    // =========================================================================
    // 8. Video Reviews (Ủy nhiệm cho ReviewVideoService)
    // =========================================================================

    public static function getVideos()
    {
        return app(ReviewVideoService::class)->getVideos();
    }

    public static function likeVideo(int $id): bool
    {
        return app(ReviewVideoService::class)->like($id);
    }

    public static function storeVideo(array $data)
    {
        return app(ReviewVideoService::class)->storeVideo($data);
    }

    public static function updateVideo(int $id, array $data)
    {
        return app(ReviewVideoService::class)->updateVideo($id, $data);
    }

    public static function approveVideo(int $id)
    {
        return app(ReviewVideoService::class)->approve($id);
    }

    public static function rejectVideo(int $id)
    {
        return app(ReviewVideoService::class)->reject($id);
    }

    public static function deleteVideo(int $id): bool
    {
        return app(ReviewVideoService::class)->delete($id);
    }

    // =========================================================================
    // 9. Trust Hub - Safety & Traceability (Ủy nhiệm cho TrustHubService)
    // =========================================================================

    public static function storeFoodSafetyCertificate(array $data)
    {
        return app(TrustHubService::class)->storeFoodSafetyCertificate($data);
    }

    public static function storeDailyFoodLog(array $data)
    {
        return app(TrustHubService::class)->storeDailyFoodLog($data);
    }

    public static function deleteDailyFoodLog(int $id): bool
    {
        return app(TrustHubService::class)->deleteDailyLog($id);
    }

    public static function storeFoodSupplyContract(array $data)
    {
        return app(TrustHubService::class)->storeFoodSupplyContract($data);
    }

    public static function deleteFoodSupplyContract(int $id): bool
    {
        return app(TrustHubService::class)->deleteContract($id);
    }

    public static function storePurchaseInvoice(array $data)
    {
        return app(TrustHubService::class)->storePurchaseInvoice($data);
    }

    public static function deletePurchaseInvoice(int $id): bool
    {
        return app(TrustHubService::class)->deleteInvoice($id);
    }

    // =========================================================================
    // 10. Food Tours (Ủy nhiệm cho FoodTourService)
    // =========================================================================

    public static function getFoodTours(?string $mood = null)
    {
        return app(FoodTourService::class)->getFoodTours($mood);
    }

    public static function getFoodTourBySlug(string $slug)
    {
        return app(FoodTourService::class)->getFoodTourBySlug($slug);
    }

    public static function generateAITour($budget, $mood)
    {
        return app(FoodTourService::class)->generateAITour($budget, $mood);
    }

    public static function storeFoodTourDiary($id, array $data)
    {
        return app(FoodTourService::class)->storeFoodTourDiary($id, $data);
    }

    // =========================================================================
    // 11. Auth & Users (Ủy nhiệm cho UserService)
    // =========================================================================

    public static function apiLogin(string $email, string $password): array
    {
        return app(UserService::class)->apiLogin($email, $password);
    }

    public static function apiRegister(array $data): array
    {
        return app(UserService::class)->apiRegister($data);
    }

    public static function apiLogout(): bool
    {
        return app(UserService::class)->apiLogout();
    }

    public static function getUsers()
    {
        return app(UserService::class)->getUsers();
    }

    public static function storeUser(array $data)
    {
        return app(UserService::class)->storeUser($data);
    }

    public static function updateUser($id, array $data)
    {
        return app(UserService::class)->updateUser($id, $data);
    }

    public static function deleteUser($id): bool
    {
        return app(UserService::class)->deleteUser($id);
    }

    public static function toggleUserStatus($id): array
    {
        return app(UserService::class)->toggleUserStatus($id);
    }
}
