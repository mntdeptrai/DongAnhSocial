-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 16, 2026 at 12:20 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `food_map`
--
CREATE DATABASE IF NOT EXISTS `food_map` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `food_map`;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('donganh-discovery-cache-356a192b7913b04c54574d18c28d46e6395428ab', 'i:7;', 1783942011),
('donganh-discovery-cache-356a192b7913b04c54574d18c28d46e6395428ab:timer', 'i:1783942011;', 1783942011),
('donganh-discovery-cache-b78df75fdb6b65b62c18e7b62bb3230b2979cc2f', 'i:1;', 1783996814),
('donganh-discovery-cache-b78df75fdb6b65b62c18e7b62bb3230b2979cc2f:timer', 'i:1783996814;', 1783996814);

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `icon`, `description`, `created_at`, `updated_at`) VALUES
(1, 'ĐÔNG ANH FOOD MAP', 'dong-anh-food-map', '🍲', 'Bản đồ ẩm thực Đông Anh - Bún phở, lẩu nướng, quán cafe,...', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(2, 'Stay in Đông Anh', 'stay-in-dong-anh', '🏨', 'Nhà nghỉ, khách sạn, biệt thự, homestay và các khu nghỉ dưỡng tiện nghi tại Đông Anh.', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(3, 'Wellness & Care', 'wellness-care', '🏥', 'Hệ thống cơ sở y tế, phòng khám, chăm sóc sức khỏe và spa thư giãn hàng đầu Đông Anh.', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(4, 'Đông Anh Market', 'dong-anh-market', '🛍️', 'Nơi hội tụ các sản phẩm OCOP, đặc sản địa phương, chợ truyền thống và trung tâm mua sắm sầm uất.', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(5, 'Smart Education Map', 'smart-education-map', '🏫', 'Hệ thống trường học và cơ sở giáo dục chất lượng cao trên địa bàn xã Đông Anh.', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(6, 'Hành trình di sản', 'hanh-trinh-di-san', '🏛️', 'Kết nối hành trình khám phá di tích lịch sử và văn hóa thông qua nền tảng Donganh360.vn.', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(7, 'Discover Dong Anh Community & Culture Hub', 'discover-dong-anh-community-culture-hub', '🏛️', 'Khám phá hệ thống thiết chế văn hóa - thể thao Đông Anh: Nhà văn hóa, nhà thi đấu, trung tâm triển lãm, nhà văn hóa thôn và tổ dân phố.', '2026-06-01 03:57:56', '2026-06-01 03:57:56');

-- --------------------------------------------------------

--
-- Table structure for table `checkins`
--

CREATE TABLE `checkins` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `eatery_id` bigint(20) UNSIGNED DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
  `comment` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'published',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `checkins`
--

INSERT INTO `checkins` (`id`, `user_id`, `eatery_id`, `guest_name`, `rating`, `comment`, `image_path`, `status`, `created_at`, `updated_at`) VALUES
(3, 1, 9, NULL, 5, NULL, 'https://media.xadonganh.com/checkins/1783421404_fL8vKZS7.jpg', 'published', '2026-07-07 10:50:04', '2026-07-07 10:50:04'),
(4, 1, 9, NULL, 5, 'âu kê', 'https://media.xadonganh.com/checkins/1784012829_DBUBnXj3.jpg', 'published', '2026-07-14 07:07:10', '2026-07-14 07:07:10');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `guest_name` varchar(255) DEFAULT NULL,
  `commentable_id` bigint(20) UNSIGNED NOT NULL,
  `commentable_type` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `communes`
--

CREATE TABLE `communes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `communes`
--

INSERT INTO `communes` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Thôn Đông Trù', 'thon-dong-tru', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(2, 'Thôn Đoài', 'thon-doai', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(3, 'Thôn Lực Canh', 'thon-luc-canh', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(4, 'Thôn Xuân Trạch', 'thon-xuan-trach', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(5, 'Thôn Gia Lộc', 'thon-gia-loc', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(6, 'Thôn Mạch Tràng', 'thon-mach-trang', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(7, 'Thôn Trung', 'thon-trung', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(8, 'Thôn Lộc Hà', 'thon-loc-ha', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(9, 'Thôn Lý Nhân', 'thon-ly-nhan', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(10, 'Thôn Lại Đà', 'thon-lai-da', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(11, 'Thôn Đông Ngàn', 'thon-dong-ngan', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(12, 'Thôn Thái Bình', 'thon-thai-binh', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(13, 'Thôn Đông', 'thon-dong', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(14, 'Thôn Tiên Hội', 'thon-tien-hoi', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(15, 'Thôn Du Nội', 'thon-du-noi', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(16, 'Thôn Hội Phụ', 'thon-hoi-phu', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(17, 'Thôn Mai Hiên', 'thon-mai-hien', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(18, 'Thôn Vang', 'thon-vang', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(19, 'Thôn Xuân Canh', 'thon-xuan-canh', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(20, 'Thôn Thượng (CL)', 'thon-thuong-cl', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(21, 'Thôn Nhồi Dưới', 'thon-nhoi-duoi', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(22, 'Thôn Dục Tú 1', 'thon-duc-tu-1', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(23, 'Thôn Trong', 'thon-trong', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(24, 'Thôn Trung Thôn', 'thon-trung-thon', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(25, 'Thôn Đản Mỗ', 'thon-dan-mo', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(26, 'Thôn Văn Thượng', 'thon-van-thuong', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(27, 'Thôn Cầu Cả', 'thon-cau-ca', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(28, 'Thôn Lê Xá', 'thon-le-xa', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(29, 'Thôn Thượng (UN)', 'thon-thuong-un', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(30, 'Thôn Đồng Dầu', 'thon-dong-dau', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(31, 'Thôn Chợ (CL)', 'thon-cho-cl', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(32, 'Thôn Dục Tú 3', 'thon-duc-tu-3', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(33, 'Thôn Thạc Quả', 'thon-thac-qua', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(34, 'Thôn Du Ngoại', 'thon-du-ngoai', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(35, 'Thôn Phan Xá', 'thon-phan-xa', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(36, 'Thôn Ngoài', 'thon-ngoai', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(37, 'Thôn Đản Dị', 'thon-dan-di', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(38, 'Thôn Phúc Hậu 2', 'thon-phuc-hau-2', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(39, 'Thôn Vạn Lộc', 'thon-van-loc', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(40, 'Thôn Gà', 'thon-ga', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(41, 'Tổ dân phố số 6', 'to-dan-pho-so-6', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(42, 'Thôn Đài Bi', 'thon-dai-bi', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(43, 'Thôn Hậu', 'thon-hau', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(44, 'Thôn Mít', 'thon-mit', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(45, 'Thôn Phúc Hậu 1', 'thon-phuc-hau-1', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(46, 'Thôn Dục Tú 2', 'thon-duc-tu-2', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(47, 'Thôn Sằn', 'thon-san', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(48, 'Thôn Dõng', 'thon-dong-d', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(49, 'Thôn Chùa', 'thon-chua', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(50, 'Thôn Văn Tinh', 'thon-van-tinh', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(51, 'Tổ dân phố số 4', 'to-dan-pho-so-4', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(52, 'Thôn Nghĩa Vũ', 'thon-nghia-vu', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(53, 'Thôn Hương', 'thon-huong', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(54, 'Tổ dân phố số 35 (+ Khu vực 382, Khu vực X89)', 'to-dan-pho-so-35', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(55, 'Thôn Nhồi Trên', 'thon-nhoi-tren', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(56, 'Thôn Phúc Thọ', 'thon-phuc-tho', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(57, 'Thôn Phúc Lộc', 'thon-phuc-loc', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(58, 'Tổ dân phố số 38', 'to-dan-pho-so-38', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(59, 'Tổ dân phố số 39', 'to-dan-pho-so-39', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(60, 'Thôn Nghĩa Lại', 'thon-nghia-lai', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(61, 'Thôn Ấp Tó', 'thon-ap-to', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(62, 'Thôn Bãi (UN)', 'thon-bai-un', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(63, 'Thôn Chợ (UN)', 'thon-cho-un', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(64, 'Tổ dân phố số 3', 'to-dan-pho-so-3', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(65, 'Tổ dân phố số 2', 'to-dan-pho-so-2', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(66, 'Tổ dân phố số 1', 'to-dan-pho-so-1', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(67, 'Thôn Lan Trì', 'thon-lan-tri', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(68, 'Thôn Lương Quán', 'thon-luong-quan', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(69, 'Tổ dân phố số 37', 'to-dan-pho-so-37', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(70, 'Tổ dân phố số 36 (+ Khu vực Công trình 6)', 'to-dan-pho-so-36', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(71, 'Khu tập thể Địa chất', 'khu-tap-the-dia-chat', '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(72, 'Thôn Phố Chợ', 'thon-pho-cho', '2026-06-01 03:57:56', '2026-06-01 03:57:56');

-- --------------------------------------------------------

--
-- Table structure for table `cultural_activities`
--

CREATE TABLE `cultural_activities` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) DEFAULT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `discount_note` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_food_logs`
--

CREATE TABLE `daily_food_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `log_date` date NOT NULL,
  `checker_name` varchar(255) NOT NULL,
  `ingredients_origin` text NOT NULL,
  `storage_condition` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'compliant',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dishes`
--

CREATE TABLE `dishes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `description` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_signature` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `eateries`
--

CREATE TABLE `eateries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `category_id` bigint(20) UNSIGNED NOT NULL,
  `commune_id` bigint(20) UNSIGNED NOT NULL,
  `description` text DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `opening_hours` varchar(255) DEFAULT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `price_range` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `rating` decimal(3,2) NOT NULL DEFAULT 5.00,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `eateries`
--

INSERT INTO `eateries` (`id`, `user_id`, `name`, `slug`, `category_id`, `commune_id`, `description`, `address`, `phone`, `opening_hours`, `latitude`, `longitude`, `price_range`, `image_path`, `is_featured`, `rating`, `status`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Lẩu Ếch Huyền Anh', 'lau-ech-huyen-anh-LLH5m', 1, 64, NULL, '139 Tổ 3 Khu 3ha, Đông Anh, Hà Nội, Việt Nam', '091 613 44 13', NULL, 21.136907, 105.84724, '100.000đ - 200.000đ', 'https://media.xadonganh.com/eateries/1780303157_jWsTYyEF.jpg', 1, 2.00, 'active', '2026-06-01 08:39:17', '2026-06-03 08:18:18'),
(2, NULL, 'Quán Nướng rặng tre', 'quan-nuong-rang-tre-YtaI3', 1, 42, NULL, '239 Đào Duy Tùng, Đài Bi, Đông Anh, Hà Nội, Việt Nam', '0981526368', NULL, 21.125768, 105.849931, '100.000đ - 200.000đ', 'https://media.xadonganh.com/eateries/1780387284_5u7WnAg7.jpg', 1, 5.00, 'active', '2026-06-02 08:01:25', '2026-06-02 08:01:25'),
(3, NULL, 'Nhà Hàng Hương Biển', 'nha-hang-huong-bien-pZS6m', 1, 20, NULL, 'Thành Cổ Loa, Đông Anh, Hà Nội, Việt Nam', '0986997699', NULL, 21.119446, 105.880144, '100.000đ - 200.000đ', 'https://media.xadonganh.com/eateries/1780387451_J5vtlPOo.png', 0, 5.00, 'active', '2026-06-02 08:04:14', '2026-06-02 08:04:14'),
(4, NULL, 'Highlands Coffee Cao Lỗ Đông Anh Hà Nội', 'highlands-coffee-cao-lo-dong-anh-ha-noi-gQTuX', 1, 57, 'https://www.highlandscoffee.com.vn/', '4RQW+VG, Đông Anh, Hà Nội 10000, Việt Nam', NULL, '06:00 - 23:00', 21.139688, 105.846312, '1đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780388105_fhC8fhg9.png', 1, 5.00, 'active', '2026-06-02 08:15:07', '2026-06-02 08:15:07'),
(5, NULL, 'Chè Ngọc Thạch', 'che-ngoc-thach-nxzLF', 1, 37, NULL, '110 Đ. Đản Dị, Đông Anh, Hà Nội, Việt Nam', NULL, '06:00 - 22:00', 21.142239, 105.854858, '18.000đ - 45.000đ', 'https://media.xadonganh.com/eateries/1780388457_TgcRK6g4.png', 1, 5.00, 'active', '2026-06-02 08:20:59', '2026-06-02 08:20:59'),
(6, NULL, 'Trung Nguyên E - Coffee Đông Hội', 'trung-nguyen-e-coffee-dong-hoi-XST1K', 1, 16, NULL, '183 Đông Hội, Hội Phụ, Đông Anh, Hà Nội, Việt Nam', '0393551982', NULL, 21.085167, 105.872088, '1đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780388973_k7SLTa6i.png', 0, 5.00, 'active', '2026-06-02 08:29:36', '2026-06-02 08:29:36'),
(9, NULL, 'Nhà văn hóa xã Đông Anh', 'nha-van-hoa-xa-dong-anh-jGFlE', 7, 35, NULL, '4VR2+88H, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.140817, 105.850848, NULL, 'https://media.xadonganh.com/eateries/1783420787_viWZg2RL.jpg', 0, 5.00, 'active', '2026-07-07 10:50:04', '2026-07-07 10:50:04');

-- --------------------------------------------------------

--
-- Table structure for table `eatery_photos`
--

CREATE TABLE `eatery_photos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `sort_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_safety_certificates`
--

CREATE TABLE `food_safety_certificates` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `certificate_number` varchar(255) NOT NULL,
  `issued_by` varchar(255) NOT NULL,
  `issued_at` date NOT NULL,
  `expired_at` date NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_supply_contracts`
--

CREATE TABLE `food_supply_contracts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `items_supplied` varchar(255) NOT NULL,
  `signed_at` date NOT NULL,
  `expired_at` date NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_tours`
--

CREATE TABLE `food_tours` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `duration` varchar(255) NOT NULL,
  `distance` varchar(255) NOT NULL,
  `budget` varchar(255) NOT NULL,
  `difficulty` varchar(255) NOT NULL,
  `best_time` varchar(255) NOT NULL,
  `popularity` varchar(255) NOT NULL DEFAULT 'Cao',
  `mood` varchar(255) NOT NULL DEFAULT 'chill',
  `thumbnail` varchar(255) DEFAULT NULL,
  `story` text DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'saved',
  `is_ai_generated` tinyint(1) NOT NULL DEFAULT 0,
  `shared_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `food_tours`
--

INSERT INTO `food_tours` (`id`, `user_id`, `name`, `slug`, `description`, `duration`, `distance`, `budget`, `difficulty`, `best_time`, `popularity`, `mood`, `thumbnail`, `story`, `status`, `is_ai_generated`, `shared_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(3, NULL, 'Hành trình Hương vị Cổ Loa - Khám phá Chợ & Ẩm thực OCOP Đông Anh', 'hanh-trinh-huong-vi-co-loa-kham-pha-cho-am-thuc-ocop-dong-anh-8cd9f', 'Đắm mình vào không khí chợ truyền thống, khám phá những sản phẩm OCOP tinh hoa và kết thúc với món chè ngọt ngào đặc trưng của vùng đất Đông Anh.', '2.5 giờ', '5.0 km', '350.000đ', '✨ Lộ trình AI', '17:00 - 21:00', 'Mới tạo', 'cheap', 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?auto=format&fit=crop&w=800&q=80', 'Để trải nghiệm trọn vẹn bản sắc văn hóa và ẩm thực Đông Anh với ngân sách \'cheap\', chúng ta sẽ bắt đầu tại Chợ Sa cổ kính, nơi nhịp sống địa phương và câu chuyện lịch sử hòa quyện. Tiếp đó, khám phá hương vị OCOP độc đáo từ sản phẩm được chứng nhận của vùng. Cuối cùng, một bát chè truyền thống sẽ là điểm dừng chân hoàn hảo để kết thúc hành trình.', 'draft', 1, NULL, NULL, '2026-06-24 06:47:28', '2026-06-24 06:47:28');

-- --------------------------------------------------------

--
-- Table structure for table `food_tour_diaries`
--

CREATE TABLE `food_tour_diaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `food_tour_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `completed_stops` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`completed_stops`)),
  `stop_reviews` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`stop_reviews`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_tour_stops`
--

CREATE TABLE `food_tour_stops` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `food_tour_id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `stop_order` int(11) NOT NULL DEFAULT 1,
  `stop_story` text DEFAULT NULL,
  `estimated_time` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `food_tour_stops`
--

INSERT INTO `food_tour_stops` (`id`, `food_tour_id`, `eatery_id`, `stop_order`, `stop_story`, `estimated_time`, `created_at`, `updated_at`) VALUES
(11, 3, 3, 1, 'Dạo quanh Chợ Sa (Cổ Loa) vào phiên chợ để cảm nhận không khí buôn bán tấp nập, tìm mua các loại nông sản tươi ngon đặc trưng của vùng như hành lá, khoai tây (có thể là sản phẩm OCOP từ HTX Cổ Loa) và hòa mình vào nét văn hóa địa phương. Bạn có thể mua một ít trái cây hoặc đồ ăn vặt bình dân với chi phí khoảng 50.000 - 80.000 VNĐ.', '45 phút', '2026-06-24 06:47:28', '2026-06-24 06:47:28');

-- --------------------------------------------------------

--
-- Table structure for table `friendships`
--

CREATE TABLE `friendships` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `friend_id` bigint(20) UNSIGNED NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `friendships`
--

INSERT INTO `friendships` (`id`, `user_id`, `friend_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 1, 'accepted', '2026-07-13 08:03:32', '2026-07-13 08:03:41');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jobs`
--

INSERT INTO `jobs` (`id`, `queue`, `payload`, `attempts`, `reserved_at`, `available_at`, `created_at`) VALUES
(14, 'default', '{\"uuid\":\"d8aa80b1-62d9-4d06-bd8b-95e1e31a069b\",\"displayName\":\"App\\\\Events\\\\MessageSent\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\MessageSent\\\":1:{s:7:\\\"message\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Message\\\";s:2:\\\"id\\\";i:22;s:9:\\\"relations\\\";a:3:{i:0;s:6:\\\"sender\\\";i:1;s:8:\\\"receiver\\\";i:2;s:8:\\\"foodTour\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1783938930,\"delay\":null}', 0, NULL, 1783938930, 1783938930),
(15, 'default', '{\"uuid\":\"97a38d9f-b3ca-44d5-9157-b9d87bc1e36c\",\"displayName\":\"App\\\\Events\\\\MessageSent\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\MessageSent\\\":1:{s:7:\\\"message\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Message\\\";s:2:\\\"id\\\";i:23;s:9:\\\"relations\\\";a:3:{i:0;s:6:\\\"sender\\\";i:1;s:8:\\\"receiver\\\";i:2;s:8:\\\"foodTour\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1783941951,\"delay\":null}', 0, NULL, 1783941951, 1783941951),
(16, 'default', '{\"uuid\":\"7f39f1b2-1eb7-4cd0-808a-8acc24548c4f\",\"displayName\":\"App\\\\Events\\\\MessageSent\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\MessageSent\\\":1:{s:7:\\\"message\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Message\\\";s:2:\\\"id\\\";i:24;s:9:\\\"relations\\\";a:3:{i:0;s:6:\\\"sender\\\";i:1;s:8:\\\"receiver\\\";i:2;s:8:\\\"foodTour\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1783941952,\"delay\":null}', 0, NULL, 1783941952, 1783941952),
(17, 'default', '{\"uuid\":\"fd07d172-146d-4e71-8cef-edc44f64ce6f\",\"displayName\":\"App\\\\Events\\\\MessageSent\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\MessageSent\\\":1:{s:7:\\\"message\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Message\\\";s:2:\\\"id\\\";i:25;s:9:\\\"relations\\\";a:3:{i:0;s:6:\\\"sender\\\";i:1;s:8:\\\"receiver\\\";i:2;s:8:\\\"foodTour\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1783941952,\"delay\":null}', 0, NULL, 1783941952, 1783941952),
(18, 'default', '{\"uuid\":\"7bdc02e7-0d54-4992-9f03-675a05ec64b1\",\"displayName\":\"App\\\\Events\\\\MessageSent\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\MessageSent\\\":1:{s:7:\\\"message\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Message\\\";s:2:\\\"id\\\";i:26;s:9:\\\"relations\\\";a:3:{i:0;s:6:\\\"sender\\\";i:1;s:8:\\\"receiver\\\";i:2;s:8:\\\"foodTour\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1783941952,\"delay\":null}', 0, NULL, 1783941952, 1783941952),
(19, 'default', '{\"uuid\":\"3ffd6c34-c391-45cc-a672-114448cbaa86\",\"displayName\":\"App\\\\Events\\\\MessageSent\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\MessageSent\\\":1:{s:7:\\\"message\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Message\\\";s:2:\\\"id\\\";i:27;s:9:\\\"relations\\\";a:3:{i:0;s:6:\\\"sender\\\";i:1;s:8:\\\"receiver\\\";i:2;s:8:\\\"foodTour\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1783941953,\"delay\":null}', 0, NULL, 1783941953, 1783941953),
(20, 'default', '{\"uuid\":\"2b1d22fa-48b2-4377-97ec-bfb343dc2d5d\",\"displayName\":\"App\\\\Events\\\\MessageSent\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\MessageSent\\\":1:{s:7:\\\"message\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Message\\\";s:2:\\\"id\\\";i:28;s:9:\\\"relations\\\";a:3:{i:0;s:6:\\\"sender\\\";i:1;s:8:\\\"receiver\\\";i:2;s:8:\\\"foodTour\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1783941953,\"delay\":null}', 0, NULL, 1783941953, 1783941953),
(21, 'default', '{\"uuid\":\"9bd76245-4e09-4d8c-ad08-dfa88b609b22\",\"displayName\":\"App\\\\Events\\\\MessageSent\",\"job\":\"Illuminate\\\\Queue\\\\CallQueuedHandler@call\",\"maxTries\":null,\"maxExceptions\":null,\"failOnTimeout\":false,\"backoff\":null,\"timeout\":null,\"retryUntil\":null,\"data\":{\"commandName\":\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\",\"command\":\"O:38:\\\"Illuminate\\\\Broadcasting\\\\BroadcastEvent\\\":17:{s:5:\\\"event\\\";O:22:\\\"App\\\\Events\\\\MessageSent\\\":1:{s:7:\\\"message\\\";O:45:\\\"Illuminate\\\\Contracts\\\\Database\\\\ModelIdentifier\\\":5:{s:5:\\\"class\\\";s:18:\\\"App\\\\Models\\\\Message\\\";s:2:\\\"id\\\";i:29;s:9:\\\"relations\\\";a:3:{i:0;s:6:\\\"sender\\\";i:1;s:8:\\\"receiver\\\";i:2;s:8:\\\"foodTour\\\";}s:10:\\\"connection\\\";s:5:\\\"mysql\\\";s:15:\\\"collectionClass\\\";N;}}s:5:\\\"tries\\\";N;s:7:\\\"timeout\\\";N;s:7:\\\"backoff\\\";N;s:13:\\\"maxExceptions\\\";N;s:23:\\\"deleteWhenMissingModels\\\";b:1;s:10:\\\"connection\\\";N;s:5:\\\"queue\\\";N;s:12:\\\"messageGroup\\\";N;s:12:\\\"deduplicator\\\";N;s:5:\\\"delay\\\";N;s:11:\\\"afterCommit\\\";N;s:10:\\\"middleware\\\";a:0:{}s:7:\\\"chained\\\";a:0:{}s:15:\\\"chainConnection\\\";N;s:10:\\\"chainQueue\\\";N;s:19:\\\"chainCatchCallbacks\\\";N;}\",\"batchId\":null},\"createdAt\":1783941953,\"delay\":null}', 0, NULL, 1783941953, 1783941953);

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sender_id` bigint(20) UNSIGNED NOT NULL,
  `receiver_id` bigint(20) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_type` varchar(255) DEFAULT NULL,
  `food_tour_id` bigint(20) UNSIGNED DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `message`, `media_path`, `media_type`, `food_tour_id`, `is_read`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'alo', NULL, NULL, NULL, 1, '2026-07-13 08:03:52', '2026-07-13 08:04:13'),
(2, 1, 4, 'alo', NULL, NULL, NULL, 1, '2026-07-13 08:04:03', '2026-07-13 08:04:13'),
(3, 1, 4, 'alo', NULL, NULL, NULL, 1, '2026-07-13 08:04:18', '2026-07-13 08:04:35'),
(4, 4, 1, 'tuất', NULL, NULL, NULL, 1, '2026-07-13 08:04:19', '2026-07-13 08:04:22'),
(5, 4, 1, 'tuất', NULL, NULL, NULL, 1, '2026-07-13 08:04:27', '2026-07-13 08:05:06'),
(6, 1, 4, 'tuấn anh', NULL, NULL, NULL, 1, '2026-07-13 08:04:34', '2026-07-13 08:04:35'),
(7, 4, 1, 'tuất', NULL, NULL, NULL, 1, '2026-07-13 08:04:42', '2026-07-13 08:05:06'),
(8, 1, 4, 'tuấn anh', NULL, NULL, NULL, 1, '2026-07-13 08:12:19', '2026-07-13 08:22:35'),
(9, 1, 4, 'loo', NULL, NULL, NULL, 1, '2026-07-13 08:22:23', '2026-07-13 08:22:35'),
(10, 1, 4, 'lô', NULL, NULL, NULL, 1, '2026-07-13 08:22:32', '2026-07-13 08:22:35'),
(11, 4, 1, 'm là con tuất', NULL, NULL, NULL, 1, '2026-07-13 08:22:41', '2026-07-13 08:22:46'),
(12, 1, 4, 'lô', NULL, NULL, NULL, 1, '2026-07-13 08:26:19', '2026-07-13 08:27:00'),
(13, 4, 1, '...', NULL, NULL, NULL, 1, '2026-07-13 08:27:04', '2026-07-13 08:27:41'),
(14, 1, 4, '...', NULL, NULL, NULL, 1, '2026-07-13 08:32:38', '2026-07-13 08:32:40'),
(15, 1, 4, 'alo', NULL, NULL, NULL, 1, '2026-07-13 08:32:42', '2026-07-13 08:32:43'),
(16, 1, 4, 'tao là đẹp trai', NULL, NULL, NULL, 1, '2026-07-13 08:32:48', '2026-07-13 08:32:49'),
(17, 4, 1, '.', NULL, NULL, NULL, 1, '2026-07-13 08:33:08', '2026-07-13 08:33:10'),
(18, 1, 4, '.', NULL, NULL, NULL, 1, '2026-07-13 08:38:16', '2026-07-13 08:38:19'),
(19, 1, 4, '.', NULL, NULL, NULL, 1, '2026-07-13 08:38:22', '2026-07-13 08:38:34'),
(20, 4, 1, '.', NULL, NULL, NULL, 1, '2026-07-13 08:38:37', '2026-07-13 08:38:38'),
(21, 4, 1, '....', NULL, NULL, NULL, 1, '2026-07-13 08:38:49', '2026-07-13 08:38:50'),
(22, 1, 4, 'lô', NULL, NULL, NULL, 0, '2026-07-13 10:35:30', '2026-07-13 10:35:30'),
(23, 1, 4, '.', NULL, NULL, NULL, 0, '2026-07-13 11:25:51', '2026-07-13 11:25:51'),
(24, 1, 4, '.', NULL, NULL, NULL, 0, '2026-07-13 11:25:52', '2026-07-13 11:25:52'),
(25, 1, 4, '.', NULL, NULL, NULL, 0, '2026-07-13 11:25:52', '2026-07-13 11:25:52'),
(26, 1, 4, '.', NULL, NULL, NULL, 0, '2026-07-13 11:25:52', '2026-07-13 11:25:52'),
(27, 1, 4, '..', NULL, NULL, NULL, 0, '2026-07-13 11:25:53', '2026-07-13 11:25:53'),
(28, 1, 4, '.', NULL, NULL, NULL, 0, '2026-07-13 11:25:53', '2026-07-13 11:25:53'),
(29, 1, 4, '.', NULL, NULL, NULL, 0, '2026-07-13 11:25:53', '2026-07-13 11:25:53');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_05_18_000001_create_food_map_tables', 1),
(5, '2026_05_18_000002_add_role_to_users_table', 1),
(6, '2026_05_19_000002_create_review_videos_table', 1),
(7, '2026_05_20_000001_create_food_tours_tables', 1),
(8, '2026_05_20_000002_create_food_tour_diaries_table', 1),
(9, '2026_05_21_013645_add_heritage_dossier_to_eateries_table', 1),
(10, '2026_05_21_022457_alter_reviews_make_columns_nullable_and_create_review_media_table', 1),
(11, '2026_05_21_023903_alter_food_tour_diaries_rating_nullable', 1),
(12, '2026_05_21_034027_add_status_and_is_ai_to_food_tours_table', 1),
(13, '2026_05_21_035808_add_sharing_fields_to_food_tours_table', 1),
(14, '2026_05_22_000001_create_food_safety_tables', 1),
(15, '2026_05_25_000001_add_management_fields_to_users_table', 1),
(16, '2026_05_25_141144_add_seller_reply_to_reviews_table', 1),
(17, '2026_05_25_201154_create_additional_category_tables', 1),
(18, '2026_05_28_000001_create_checkins_table', 1),
(19, '2026_05_28_000002_create_comments_table', 1),
(22, '2026_06_02_160000_create_cultural_activities_table', 2),
(23, '2026_06_02_180000_create_cultural_activities_all_connections', 3),
(24, '2026_06_03_145536_add_user_id_to_food_tours_table', 3),
(25, '2026_06_03_171918_create_password_otps_table', 3),
(26, '2026_06_19_100954_create_eatery_photos_table', 4),
(27, '2026_06_22_100528_add_indexes_to_tables', 5),
(28, '2026_06_05_080000_add_heritage_fields_to_ocop_products_table', 6),
(29, '2026_07_07_103715_create_personal_access_tokens_table', 6),
(30, '2026_07_07_105840_create_social_and_chat_tables', 6),
(31, '2026_07_07_161700_add_food_tour_id_to_messages_table', 6),
(32, '2026_07_07_162019_add_media_to_messages_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `password_otps`
--

CREATE TABLE `password_otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL,
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_otps`
--

INSERT INTO `password_otps` (`id`, `email`, `otp`, `expires_at`, `used_at`, `created_at`, `updated_at`) VALUES
(1, 'trongminh10022004@gmail.com', '451802', '2026-06-03 16:38:52', '2026-06-03 16:36:28', '2026-06-03 16:34:52', '2026-06-03 16:36:28'),
(2, 'trongminh10022004@gmail.com', '322417', '2026-06-03 17:31:58', NULL, '2026-06-03 17:27:58', '2026-06-03 17:27:58'),
(3, 'trongminh10022004@gmail.com', '334502', '2026-06-05 02:53:07', '2026-06-05 02:50:08', '2026-06-05 02:49:07', '2026-06-05 02:50:08'),
(4, 'trongminh10022004@gmail.com', '600826', '2026-06-05 02:59:51', NULL, '2026-06-05 02:55:51', '2026-06-05 02:55:51'),
(5, 'trongminh10022004@gmail.com', '345222', '2026-06-05 03:02:20', NULL, '2026-06-05 02:58:20', '2026-06-05 02:58:20'),
(6, 'trongminh10022004@gmail.com', '598298', '2026-06-05 03:07:09', NULL, '2026-06-05 03:03:09', '2026-06-05 03:03:09'),
(7, 'trongminh10022004@gmail.com', '636653', '2026-06-05 03:09:09', NULL, '2026-06-05 03:05:09', '2026-06-05 03:05:09'),
(8, 'trongminh10022004@gmail.com', '943234', '2026-06-05 03:10:04', NULL, '2026-06-05 03:06:04', '2026-06-05 03:06:04'),
(9, 'trongminh10022004@gmail.com', '530046', '2026-06-05 03:13:51', NULL, '2026-06-05 03:09:51', '2026-06-05 03:09:51'),
(10, 'trongminh10022004@gmail.com', '570612', '2026-06-05 07:42:33', '2026-06-05 07:39:02', '2026-06-05 07:38:33', '2026-06-05 07:39:02'),
(11, 'trongminh10022004@gmail.com', '392764', '2026-06-18 09:51:12', '2026-06-18 09:48:16', '2026-06-18 09:47:12', '2026-06-18 09:48:16'),
(12, 'ntphuong.haui@gmail.com', '482489', '2026-06-29 08:03:39', NULL, '2026-06-29 07:59:39', '2026-06-29 07:59:39');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'mobile_flutter', '7a6efaadb4728f03d09a962dbbc657c407d84fd8ed4ff69b462668bad162da9f', '[\"*\"]', '2026-07-14 04:06:44', NULL, '2026-07-14 04:03:52', '2026-07-14 04:06:44'),
(2, 'App\\Models\\User', 1, 'mobile_flutter', '2d84184f2ac783646ea67f2fd68966d484ae81b822beaeeef4ad061e9070a151', '[\"*\"]', '2026-07-15 14:45:46', NULL, '2026-07-14 04:12:13', '2026-07-15 14:45:46');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_invoices`
--

CREATE TABLE `purchase_invoices` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `invoice_number` varchar(255) DEFAULT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `invoice_date` date NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `items_summary` text NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `user_name` varchar(255) NOT NULL,
  `rating` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `seller_reply` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `eatery_id`, `user_name`, `rating`, `comment`, `seller_reply`, `created_at`, `updated_at`) VALUES
(1, 5, 'Nguyễn Văn Admin', 5, 'kkkkkk', NULL, '2026-06-03 08:18:16', '2026-06-03 08:18:16'),
(2, 3, 'Nguyễn Văn Admin', 5, 'kkkkk', NULL, '2026-06-03 08:18:17', '2026-06-03 08:18:17'),
(3, 1, 'Nguyễn Văn Admin', 2, 'yyyyy', NULL, '2026-06-03 08:18:18', '2026-06-03 08:18:18');

-- --------------------------------------------------------

--
-- Table structure for table `review_media`
--

CREATE TABLE `review_media` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `review_id` bigint(20) UNSIGNED NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(255) NOT NULL DEFAULT 'image',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `review_media`
--

INSERT INTO `review_media` (`id`, `review_id`, `file_path`, `file_type`, `created_at`, `updated_at`) VALUES
(1, 1, 'https://media.xadonganh.com/diaries/1780474696_9rrHXMQl.jpeg', 'image', '2026-06-03 08:18:16', '2026-06-03 08:18:16'),
(2, 2, 'https://media.xadonganh.com/diaries/1780474696_wMpsaqCW.jpeg', 'image', '2026-06-03 08:18:17', '2026-06-03 08:18:17'),
(3, 3, 'https://media.xadonganh.com/diaries/1780474697_lWceTJab.png', 'image', '2026-06-03 08:18:18', '2026-06-03 08:18:18');

-- --------------------------------------------------------

--
-- Table structure for table `review_videos`
--

CREATE TABLE `review_videos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `video_url` text NOT NULL,
  `video_type` varchar(255) NOT NULL DEFAULT 'local',
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `likes_count` int(11) NOT NULL DEFAULT 0,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('6HiuzldBdjVDXyV7wL7uOj7S1VCmbNtcP8L6DWsW', NULL, '172.236.228.229', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibm5icThZUkM3Q29ydFY2ZDJVaU9SZE8xMjNjd09IWFNuSko2QlpJayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784167597),
('6YviK7j4MhNAYop0xciy6jjvVeX4esjOQV4QsB3s', NULL, '103.131.71.168', 'Mozilla/5.0 (compatible; coccocbot-web/1.0; +http://help.coccoc.com/searchengine)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWEhYSUFwVVlZbUdISDM1ZUx5dndkak4wZVlySDZ6S1lRV3dEblRwWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDk6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tL2Zvb2QtdG91cnMiO3M6NToicm91dGUiO3M6MTY6ImZvb2QtdG91cnMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784148256),
('7Tp1iNoojf1TGJ3nnWkPVXtkgBM9ohXKZDlKVcsQ', NULL, '110.249.202.243', 'Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; https://zhanzhang.toutiao.com/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMlgyeWcyOFVKNjFlVVRaeWJIUDIwbWdQSjBKOWRzT0RmZk5mWlJZciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODM6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tLz9jYXQ9ZGlzY292ZXItZG9uZy1hbmgtY29tbXVuaXR5LWN1bHR1cmUtaHViIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784152017),
('8AsKE1GqOVUCrOK2GCWj8OJfY5sfIvgL5cuW2wty', NULL, '65.49.1.232', 'Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiR0tzckpYOWlTOG9KTUJDcnNtdUJHQXRwRlh2SXBVdk5KWVY5SldRRyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784172054),
('AnrHLUdFsJXa88oClIUkEm4uakw0woNH285dwO1J', NULL, '113.171.83.165', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/150.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU0NFY3A1em95emRZMjh6UDU3RkE2QzVZSU5OUElZZERvYlRhWm5rcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784168901),
('At05DTR7zJNbLj6sX6oV4KtOCEZuyhpxvtE1QUKk', NULL, '221.238.130.234', 'Mozilla/5.0 (Linux; Android 12; redroid12_arm64 Build/SQ1D.220205.004; wv) AppleWebKit/537.36 (KHTML, like Gecko) Version/4.0 Chrome/122.0.6261.119 Mobile Safari/537.36 uni-app', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicGRHZ0owdnZLeXNOYWlLQ1FTU0FUTDZ5ZjJZYUF4VEljaFRJWDd3dCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly9kb25nYW5oZGlzY292ZXJ5LnhhZG9uZ2FuaC5jb20iO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784150411),
('axUW1HlNX2MikP4iDpG5byIOg0pzkZAKM0au4L0e', NULL, '46.151.178.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSUY2NWp4bU9oU25jcGFrTnlzaDM5anFNOVZBb2dSZW5mWXQ1ekR3ZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784162760),
('c9DQwbk89icbQs64rlFCt9QpPqaWDX9hNA8uz3F3', NULL, '54.222.237.96', 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.1988.1697 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiRjY1dGtCOUZHV0hpZ3pUdk5NbnNtTEZmTFU4UTFNcWtOSHRDSHRyRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDk6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tL2Zvb2QtdG91cnMiO3M6NToicm91dGUiO3M6MTY6ImZvb2QtdG91cnMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784163738),
('CaymkukeY4AEaNx5gFAjE8fzLxM9QsEhuQt5BDsq', NULL, '45.205.1.223', 'Mozilla/5.0 (compatible; FlowIQLabsBot/1.0; +https://flowiq-labs.com/scanning-info)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiQkRyazFQYlhnUEVzdHJmWkhFYXExNmd1Mm5hcXA3Y0J4Rm12amdqWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784173739),
('D2cJgsNUDnAsaYkjeVZADPNk2N6p6ecnipfFKp9e', NULL, '46.151.178.13', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiN3k1WEhGUkJKbVRYRUlSdWFDYjU5cVR6UE5VNjhIeFFyYTFUZURjUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784162758),
('D875AgTx4KknRZoO9tcAL6k6ymM14VQcuaKje6Oo', NULL, '103.131.71.88', 'Mozilla/5.0 (compatible; coccocbot-web/1.0; +http://help.coccoc.com/searchengine)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiazN4VlJRRHg1Z21LTkZVN1F3N1ZzeDZzd2NMOGFxdHNaZFdTQ0FOUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTc6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tLz9jYXQ9d2VsbG5lc3MtY2FyZSI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1784167376),
('dTRICwaJOuzWSkprsB9fmHLKhkfkd06kjRXnV5Pt', NULL, '39.182.13.106', 'Mozilla/5.0 (Linux; Android 8.0; Pixel 2 Build/OPD3.170816.012) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2557.1916 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiTDRJeTlLM29HWkNWbjZWSE9kTTBHRENVb3FIT3ZtalBjejBMRzRHcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Njg6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tL2RpYS1kaWVtL2NoZS1uZ29jLXRoYWNoLW54ekxGIjtzOjU6InJvdXRlIjtzOjExOiJlYXRlcnkuc2hvdyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784163723),
('EBZUSsV3rUe889luprat5gWu6qcpmRHkTe7GcZVa', NULL, '65.49.1.235', 'Mozilla/5.0 (X11; Linux x86_64; rv:140.0) Gecko/20100101 Firefox/140.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicU8wUXd3aGlLMUNvVzVTOUV0UXhIcTVoVFhWYkFpcFNTWmMwRGFONCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784172684),
('fgubL8qjdKuvz7oh4c2Gy320hZlZVQQ6lXLFdX38', NULL, '120.55.58.176', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicjhKdUhZa0t0a3hRamJyT3RSSmVaOXd6MnhLell3d3ZERXdOWTVLQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784155270),
('Fz8x67P4wtLIkxO1mrkSfmbb3NSF75gmrC2hM71C', NULL, '66.240.223.208', 'Mozilla/5.0 zgrab/0.x', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYVhucmx5WGM1ZWQ4ZjUyZFljcjB3UkZ1S3Z6SE5FT2gzTklZWHBidSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784166151),
('gMg7i1iTwJfZIL5c4lc2MXJyNdQCGhf118eN7BKr', NULL, '110.249.202.119', 'Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; https://zhanzhang.toutiao.com/)', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiQzhGbmtSb0pFMnpqSDNreDlSN0MyMFBJMmtXeGFxWEhVRzJMdlhOZiI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo1NjoiaHR0cHM6Ly9kb25nYW5oZGlzY292ZXJ5LnhhZG9uZ2FuaC5jb20vZm9vZC10b3Vycy9jcmVhdGUiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czo0OToiaHR0cHM6Ly9kb25nYW5oZGlzY292ZXJ5LnhhZG9uZ2FuaC5jb20vYXV0aC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784151821),
('HH4DEVkONckzAnNh9Rgr9dT3IS7oomlZqHBJlU8u', NULL, '110.249.201.30', 'Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; https://zhanzhang.toutiao.com/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYnhGVlRFZEdWWk1BcndTdjZnRlNIV1RUYk5Ebm1NTzdDSlpOVG1JNiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Njg6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tL2RpYS1kaWVtL2NoZS1uZ29jLXRoYWNoLW54ekxGIjtzOjU6InJvdXRlIjtzOjExOiJlYXRlcnkuc2hvdyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784151845),
('ISyd7CYM5C2B9BsxeNK6xwYXr4UsgqkJ2HFucgPC', NULL, '103.131.71.168', 'Mozilla/5.0 (compatible; coccocbot-web/1.0; +http://help.coccoc.com/searchengine)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidm1uQ2t3SDVTc0x5SlNCQkFoMUlJOEhoR2p4T1BqYnRuMmp4S1g2WiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784148254),
('j4ZzZ1nN8tfJEzQBTPTBICTP4lxwfoOLOp85RdVb', NULL, '110.249.202.215', 'Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; https://zhanzhang.toutiao.com/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiMGxSSzNlcmxIVFlBUzZPd0NMcmpFclRoRnAyNUVIWjRKVGhKZWVJcyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDk6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tL2Zvb2QtdG91cnMiO3M6NToicm91dGUiO3M6MTY6ImZvb2QtdG91cnMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784152042),
('ldZ5dlddkeQjii9VQJDAcLypjbTem8mtW4z0fVre', NULL, '52.80.239.113', 'Mozilla/5.0 (Linux; Android 5.0; SM-G900P Build/LRX21T) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.7199.1180 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiSVFlUk1UbkExNzN4ZU5kWllqWFd6VldUeGFjRXo1blJxdWVBZEhaQiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784163813),
('MoQVzc59aIvmb3MqnNW8iXGpF6K1dg0tIsprZQr6', NULL, '54.223.17.51', 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.4199.1453 Mobile Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiZUV2dndITzlEMDZjTUszS1MxeUVKZkpCWW8xdlZRclBuazlYSWtlSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6ODM6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tLz9jYXQ9ZGlzY292ZXItZG9uZy1hbmgtY29tbXVuaXR5LWN1bHR1cmUtaHViIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784163713),
('mZp9GwTLNx6YyMz4J13ly4QmFb31QKs57vlksLgD', NULL, '52.167.144.166', 'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; bingbot/2.0; +http://www.bing.com/bingbot.htm) Chrome/116.0.1938.76 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoic0sxeHR2aDBrSWtnYmNod1RJaVNCRTl5a0JkZk13c0xoUHk5Q0hjRyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Njg6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tL2RpYS1kaWVtL2NoZS1uZ29jLXRoYWNoLW54ekxGIjtzOjU6InJvdXRlIjtzOjExOiJlYXRlcnkuc2hvdyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784150379),
('VMMu0QGspjtok1OUNbClI14Yx5UG9eSIep9nOsTu', NULL, '20.65.194.61', 'Mozilla/5.0 zgrab/0.x', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoidWR4WlFqZXpSd25vVTc4dndmVXdYdXpKQWZUVVVFc0V3dTVXellJSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784163599),
('VPhwMFgOvfD5YHEQfzWqMKl3z52OFMlgef1a98Sn', NULL, '110.249.202.98', 'Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; https://zhanzhang.toutiao.com/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiVHdWVks5Vm5HdjNkWFNxV1I1dndVeWZsak9lSkRXc2ZUb3Ryc3RLaCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784151944),
('W2QhYUQ9evrS7CHhwpv9MlhP3kDQMFZ5TQIJS8TY', NULL, '45.79.128.205', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWVhudHBqNmVKTjlGUEhFbVNwcjR1SDBDZ0tMVjREWGRMWTF3VEtWSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784174544),
('wa6R6PbFPBpZO3exFIjiQ31U1qF93cMiuSl2zBVn', NULL, '110.249.202.98', 'Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; https://zhanzhang.toutiao.com/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiU05jcmJ5WGd1ZjQxTlFlSnRObjJJSTdYT3R3em1jd0w0TWppa2FuZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTM6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tL2RpYS1kaWVtL2hpZ2hsYW5kcy1jb2ZmZWUtY2FvLWxvLWRvbmctYW5oLWhhLW5vaS1nUVR1WCI7czo5OiJyb3V0ZSI7czoxMToiZWF0ZXJ5LnNob3ciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784151800),
('WvIxikOOozbbbPJm5FsbVA5tPvC6fn4h6yh13Qzu', NULL, '43.128.156.124', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoicTVMbzFVMEhWbVJDa01XZ0FiRWtHR3BldXlyZ0R2TWd6UVY2ZzJkbSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784152722),
('Y0xdkcFS5rEfaCkLlJVMqRo5oYxLKsr7GuEcA4Jw', NULL, '54.222.250.85', 'Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; spider-feedback@bytedance.com)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoibVlKWDVFdlRiWUlIeFJNd1B4dkRMNmdLWkxUcmZZRlNhQnBMb29KWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzg6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tIjtzOjU6InJvdXRlIjtzOjQ6ImhvbWUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784148161),
('yeoNEkIiOYVVvs5AUepYJOrGhFzacL93ohnY9ooe', NULL, '43.164.196.244', 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiUlpFQUI3bFZxblZGcjlhVnFFVDVTQXlHTTZpU0h3N3pmVTNFck01aSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784166788),
('Z4ZfvmZiQnm8tzSSRw0xSyhH37nKPC6PyzAwuasr', NULL, '110.249.201.8', 'Mozilla/5.0 (Linux; Android 5.0) AppleWebKit/537.36 (KHTML, like Gecko) Mobile Safari/537.36 (compatible; Bytespider; https://zhanzhang.toutiao.com/)', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieldKd3E0VW5rY1AwenJSTjVnVmd1SjV1V2ZYM2pkb2pEc3h4VEhBUyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDk6Imh0dHBzOi8vZG9uZ2FuaGRpc2NvdmVyeS54YWRvbmdhbmguY29tL2V4cC1jb3JuZXIiO3M6NToicm91dGUiO3M6MTk6ImNvb2tpbmctdG91cnMuaW5kZXgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1784151869),
('ZBg3858SeWcZYdcTGNciFM8lk3AR28tcjZdGJqfK', NULL, '172.236.228.198', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 13_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoieVF2eDRsbUJNaHR2NHRjZjRoRG56dkxIS1R3RTd2a3g0NXg0Y0xxVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjI6Imh0dHBzOi8vMjIxLjEzMi4yMS4xNzgiO3M6NToicm91dGUiO3M6NDoiaG9tZSI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1784171761);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL DEFAULT 'user',
  `avatar` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'active',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `avatar`, `phone`, `status`, `latitude`, `longitude`, `last_active_at`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Minh Đẹp Trai', 'trongminh10022004@gmail.com', NULL, '$2y$12$3tEWAoEcVuRY13yxeLSz3.vJd4FjWnndanCgFD404yZ3kCq3av1jy', 'admin', '👨💼', '0867796312', 'active', 21.14200000, 105.85000000, '2026-07-14 05:16:34', NULL, '2026-06-01 03:57:56', '2026-07-14 05:16:34'),
(2, 'Trần Thị Bích', 'seller@foodmap.vn', NULL, '$2y$12$IbNGBwd.dX03izgLuFSPnOVXiywjlx.FHb0We13Mjhs9puOW5Egbe', 'seller', '👨🍳', '0912345678', 'active', NULL, NULL, NULL, NULL, '2026-06-01 03:57:56', '2026-06-01 03:57:56'),
(3, 'Thực Thần Đông Anh', 'trongminh100022004@gmail.com', NULL, '$2y$12$Kw0y350bZNx2gZbqVZjsPOwWB54Nibng/sOQ6wVWn9DqN/jObCege', 'user', '🧑', '0987654321', 'active', NULL, NULL, NULL, NULL, '2026-06-01 03:57:56', '2026-06-03 11:05:05'),
(4, 'Thành viên Đông Anh', 'member@foodmap.vn', NULL, '$2y$12$qx4dgdJY0oqPXr/NvJiBWetoToVtUVb0sNis.HN1XAh2kBVvO9V26', 'user', '👧', '0977665544', 'active', 21.14200000, 105.85000000, '2026-07-14 04:07:30', NULL, '2026-06-01 03:57:56', '2026-07-14 04:07:30'),
(5, 'Bùi Văn An', 'trongminh20004@gmail.com', NULL, '$2y$12$UuEKORO419RzemWK7QTXieq7EKayGrUEXycOj8ilKqBwpiDM8olQu', 'user', '🧑', '0867796312', 'active', NULL, NULL, NULL, NULL, '2026-06-03 15:48:11', '2026-06-03 15:48:11'),
(6, 'tuấn và anh', 'cr719852004@gmail.com', NULL, '$2y$12$trPqgjD/V.9XGMcsipqN7eIYloiGhqZdQi7KP44aWcjOyBvUGBtBy', 'admin', '🧑', '0000000000', 'active', NULL, NULL, NULL, NULL, '2026-06-19 04:10:25', '2026-06-19 04:10:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categories_slug_unique` (`slug`),
  ADD KEY `categories_name_index` (`name`);

--
-- Indexes for table `checkins`
--
ALTER TABLE `checkins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `checkins_user_id_foreign` (`user_id`),
  ADD KEY `checkins_eatery_id_foreign` (`eatery_id`),
  ADD KEY `checkins_status_index` (`status`),
  ADD KEY `checkins_created_at_index` (`created_at`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `comments_user_id_foreign` (`user_id`),
  ADD KEY `comments_commentable_id_commentable_type_index` (`commentable_id`,`commentable_type`),
  ADD KEY `comments_created_at_index` (`created_at`);

--
-- Indexes for table `communes`
--
ALTER TABLE `communes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `communes_slug_unique` (`slug`),
  ADD KEY `communes_name_index` (`name`);

--
-- Indexes for table `cultural_activities`
--
ALTER TABLE `cultural_activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cultural_activities_eatery_id_foreign` (`eatery_id`);

--
-- Indexes for table `daily_food_logs`
--
ALTER TABLE `daily_food_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `daily_food_logs_eatery_id_foreign` (`eatery_id`),
  ADD KEY `daily_food_logs_log_date_index` (`log_date`);

--
-- Indexes for table `dishes`
--
ALTER TABLE `dishes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `dishes_eatery_id_foreign` (`eatery_id`),
  ADD KEY `dishes_name_index` (`name`),
  ADD KEY `dishes_is_signature_index` (`is_signature`);

--
-- Indexes for table `eateries`
--
ALTER TABLE `eateries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `eateries_slug_unique` (`slug`),
  ADD KEY `eateries_user_id_foreign` (`user_id`),
  ADD KEY `eateries_category_id_foreign` (`category_id`),
  ADD KEY `eateries_commune_id_foreign` (`commune_id`),
  ADD KEY `eateries_name_index` (`name`),
  ADD KEY `eateries_is_featured_index` (`is_featured`),
  ADD KEY `eateries_status_index` (`status`),
  ADD KEY `eateries_latitude_longitude_index` (`latitude`,`longitude`);

--
-- Indexes for table `eatery_photos`
--
ALTER TABLE `eatery_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `eatery_photos_eatery_id_index` (`eatery_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `food_safety_certificates`
--
ALTER TABLE `food_safety_certificates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_safety_certificates_eatery_id_foreign` (`eatery_id`),
  ADD KEY `food_safety_certificates_expired_at_index` (`expired_at`);

--
-- Indexes for table `food_supply_contracts`
--
ALTER TABLE `food_supply_contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_supply_contracts_eatery_id_foreign` (`eatery_id`),
  ADD KEY `food_supply_contracts_signed_at_index` (`signed_at`);

--
-- Indexes for table `food_tours`
--
ALTER TABLE `food_tours`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `food_tours_slug_unique` (`slug`),
  ADD KEY `food_tours_slug_index` (`slug`),
  ADD KEY `food_tours_mood_index` (`mood`),
  ADD KEY `food_tours_status_index` (`status`),
  ADD KEY `food_tours_is_ai_generated_index` (`is_ai_generated`),
  ADD KEY `food_tours_expires_at_index` (`expires_at`),
  ADD KEY `food_tours_user_id_foreign` (`user_id`),
  ADD KEY `food_tours_shared_at_index` (`shared_at`);

--
-- Indexes for table `food_tour_diaries`
--
ALTER TABLE `food_tour_diaries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_tour_diaries_food_tour_id_foreign` (`food_tour_id`),
  ADD KEY `food_tour_diaries_user_id_foreign` (`user_id`),
  ADD KEY `food_tour_diaries_created_at_index` (`created_at`);

--
-- Indexes for table `food_tour_stops`
--
ALTER TABLE `food_tour_stops`
  ADD PRIMARY KEY (`id`),
  ADD KEY `food_tour_stops_eatery_id_foreign` (`eatery_id`),
  ADD KEY `food_tour_stops_food_tour_id_stop_order_index` (`food_tour_id`,`stop_order`);

--
-- Indexes for table `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `friendships_user_id_friend_id_unique` (`user_id`,`friend_id`),
  ADD KEY `friendships_friend_id_foreign` (`friend_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `messages_sender_id_foreign` (`sender_id`),
  ADD KEY `messages_receiver_id_foreign` (`receiver_id`),
  ADD KEY `messages_food_tour_id_foreign` (`food_tour_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_otps`
--
ALTER TABLE `password_otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `password_otps_email_index` (`email`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `purchase_invoices_eatery_id_foreign` (`eatery_id`),
  ADD KEY `purchase_invoices_invoice_date_index` (`invoice_date`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reviews_eatery_id_foreign` (`eatery_id`),
  ADD KEY `reviews_rating_index` (`rating`);

--
-- Indexes for table `review_media`
--
ALTER TABLE `review_media`
  ADD PRIMARY KEY (`id`),
  ADD KEY `review_media_review_id_foreign` (`review_id`),
  ADD KEY `review_media_file_type_index` (`file_type`);

--
-- Indexes for table `review_videos`
--
ALTER TABLE `review_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `review_videos_eatery_id_foreign` (`eatery_id`),
  ADD KEY `review_videos_user_id_foreign` (`user_id`),
  ADD KEY `review_videos_status_index` (`status`),
  ADD KEY `review_videos_video_type_index` (`video_type`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_index` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `checkins`
--
ALTER TABLE `checkins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `communes`
--
ALTER TABLE `communes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `cultural_activities`
--
ALTER TABLE `cultural_activities`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_food_logs`
--
ALTER TABLE `daily_food_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dishes`
--
ALTER TABLE `dishes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eateries`
--
ALTER TABLE `eateries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `eatery_photos`
--
ALTER TABLE `eatery_photos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_safety_certificates`
--
ALTER TABLE `food_safety_certificates`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_supply_contracts`
--
ALTER TABLE `food_supply_contracts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_tours`
--
ALTER TABLE `food_tours`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `food_tour_diaries`
--
ALTER TABLE `food_tour_diaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `food_tour_stops`
--
ALTER TABLE `food_tour_stops`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `password_otps`
--
ALTER TABLE `password_otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `review_media`
--
ALTER TABLE `review_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `review_videos`
--
ALTER TABLE `review_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `checkins`
--
ALTER TABLE `checkins`
  ADD CONSTRAINT `checkins_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `checkins_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cultural_activities`
--
ALTER TABLE `cultural_activities`
  ADD CONSTRAINT `cultural_activities_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `daily_food_logs`
--
ALTER TABLE `daily_food_logs`
  ADD CONSTRAINT `daily_food_logs_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `dishes`
--
ALTER TABLE `dishes`
  ADD CONSTRAINT `dishes_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `eateries`
--
ALTER TABLE `eateries`
  ADD CONSTRAINT `eateries_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eateries_commune_id_foreign` FOREIGN KEY (`commune_id`) REFERENCES `communes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `eateries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `food_safety_certificates`
--
ALTER TABLE `food_safety_certificates`
  ADD CONSTRAINT `food_safety_certificates_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `food_supply_contracts`
--
ALTER TABLE `food_supply_contracts`
  ADD CONSTRAINT `food_supply_contracts_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `food_tours`
--
ALTER TABLE `food_tours`
  ADD CONSTRAINT `food_tours_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `food_tour_diaries`
--
ALTER TABLE `food_tour_diaries`
  ADD CONSTRAINT `food_tour_diaries_food_tour_id_foreign` FOREIGN KEY (`food_tour_id`) REFERENCES `food_tours` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `food_tour_diaries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `food_tour_stops`
--
ALTER TABLE `food_tour_stops`
  ADD CONSTRAINT `food_tour_stops_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `food_tour_stops_food_tour_id_foreign` FOREIGN KEY (`food_tour_id`) REFERENCES `food_tours` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `friendships`
--
ALTER TABLE `friendships`
  ADD CONSTRAINT `friendships_friend_id_foreign` FOREIGN KEY (`friend_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendships_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_food_tour_id_foreign` FOREIGN KEY (`food_tour_id`) REFERENCES `food_tours` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `messages_receiver_id_foreign` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  ADD CONSTRAINT `purchase_invoices_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_media`
--
ALTER TABLE `review_media`
  ADD CONSTRAINT `review_media_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `review_videos`
--
ALTER TABLE `review_videos`
  ADD CONSTRAINT `review_videos_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `review_videos_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
