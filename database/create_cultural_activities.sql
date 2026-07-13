-- ============================================================
-- Chạy lệnh SQL này trên 2 database để tạo bảng cultural_activities
-- Thực hiện trên server production (aaPanel hoặc SSH)
-- ============================================================

-- 1. Trên database: food_map (kết nối mysql) — cho danh mục "Hành trình di sản"
USE food_map;
CREATE TABLE IF NOT EXISTS `cultural_activities` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `eatery_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(255) NULL,
    `price` DECIMAL(12, 2) NULL,
    `unit` VARCHAR(255) NULL,
    `discount_note` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `image_path` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    INDEX `cultural_activities_eatery_id_index` (`eatery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Trên database: dong_anh_culture_hub (kết nối mysql_culture) — cho danh mục "Thiết chế văn hóa"
USE dong_anh_culture_hub;
CREATE TABLE IF NOT EXISTS `cultural_activities` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `eatery_id` BIGINT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `type` VARCHAR(255) NULL,
    `price` DECIMAL(12, 2) NULL,
    `unit` VARCHAR(255) NULL,
    `discount_note` VARCHAR(255) NULL,
    `description` TEXT NULL,
    `image_path` VARCHAR(255) NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    INDEX `cultural_activities_eatery_id_index` (`eatery_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Hoặc chạy migration qua artisan trên server:
--   php artisan migrate --path=database/migrations/2026_06_02_180000_create_cultural_activities_all_connections.php
-- ============================================================
