-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2026 at 09:51 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dong_anh_market`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `cart_id` bigint(20) UNSIGNED NOT NULL,
  `dish_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ocop_product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(1, 'ĐÔNG ANH FOOD MAP', 'dong-anh-food-map', '🍲', 'Bản đồ ẩm thực Đông Anh - Bún phở, lẩu nướng, quán cafe,...', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(2, 'Stay in Đông Anh', 'stay-in-dong-anh', '🏨', 'Nhà nghỉ, khách sạn, biệt thự, homestay và các khu nghỉ dưỡng tiện nghi tại Đông Anh.', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(3, 'Wellness & Care', 'wellness-care', '🏥', 'Hệ thống cơ sở y tế, phòng khám, chăm sóc sức khỏe và spa thư giãn hàng đầu Đông Anh.', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(4, 'Đặc sản OCOP', 'dong-anh-market', '🛍️', 'Nơi hội tụ các sản phẩm OCOP, quà lưu niệm độc đáo, đặc sản địa phương mang đậm hồn quê Đông Anh.', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(5, 'Smart Education Map', 'smart-education-map', '🏫', 'Hệ thống trường học và cơ sở giáo dục chất lượng cao trên địa bàn huyện Đông Anh.', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(6, 'Hành trình di sản', 'hanh-trinh-di-san', '🏛️', 'Kết nối hành trình khám phá di tích lịch sử và văn hóa thông qua nền tảng Donganh360.vn.', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(7, 'Discover Dong Anh Community & Culture Hub', 'discover-dong-anh-community-culture-hub', '🏛️', 'Khám phá hệ thống thiết chế văn hóa - thể thao Đông Anh: Nhà văn hóa, nhà thi đấu, trung tâm triển lãm, nhà văn hóa thôn và tổ dân phố.', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(8, 'Chợ truyền thống', 'traditional-market', '🏪', 'Khám phá các chợ truyền thống nhộn nhịp mang đậm hồn quê Đông Anh.', '2026-07-23 02:17:57', '2026-07-23 02:17:57');

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
(1, 'Thôn Đông Trù', 'thon-dong-tru', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(2, 'Thôn Đoài', 'thon-doai', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(3, 'Thôn Lực Canh', 'thon-luc-canh', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(4, 'Thôn Xuân Trạch', 'thon-xuan-trach', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(5, 'Thôn Gia Lộc', 'thon-gia-loc', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(6, 'Thôn Mạch Tràng', 'thon-mach-trang', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(7, 'Thôn Trung', 'thon-trung', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(8, 'Thôn Lộc Hà', 'thon-loc-ha', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(9, 'Thôn Lý Nhân', 'thon-ly-nhan', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(10, 'Thôn Lại Đà', 'thon-lai-da', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(11, 'Thôn Đông Ngàn', 'thon-dong-ngan', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(12, 'Thôn Thái Bình', 'thon-thai-binh', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(13, 'Thôn Đông', 'thon-dong', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(14, 'Thôn Tiên Hội', 'thon-tien-hoi', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(15, 'Thôn Du Nội', 'thon-du-noi', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(16, 'Thôn Hội Phụ', 'thon-hoi-phu', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(17, 'Thôn Mai Hiên', 'thon-mai-hien', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(18, 'Thôn Vang', 'thon-vang', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(19, 'Thôn Xuân Canh', 'thon-xuan-canh', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(20, 'Thôn Thượng (CL)', 'thon-thuong-cl', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(21, 'Thôn Nhồi Dưới', 'thon-nhoi-duoi', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(22, 'Thôn Dục Tú 1', 'thon-duc-tu-1', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(23, 'Thôn Trong', 'thon-trong', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(24, 'Thôn Trung Thôn', 'thon-trung-thon', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(25, 'Thôn Đản Mỗ', 'thon-dan-mo', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(26, 'Thôn Văn Thượng', 'thon-van-thuong', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(27, 'Thôn Cầu Cả', 'thon-cau-ca', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(28, 'Thôn Lê Xá', 'thon-le-xa', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(29, 'Thôn Thượng (UN)', 'thon-thuong-un', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(30, 'Thôn Đồng Dầu', 'thon-dong-dau', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(31, 'Thôn Chợ (CL)', 'thon-cho-cl', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(32, 'Thôn Dục Tú 3', 'thon-duc-tu-3', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(33, 'Thôn Thạc Quả', 'thon-thac-qua', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(34, 'Thôn Du Ngoại', 'thon-du-ngoai', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(35, 'Thôn Phan Xá', 'thon-phan-xa', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(36, 'Thôn Ngoài', 'thon-ngoai', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(37, 'Thôn Đản Dị', 'thon-dan-di', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(38, 'Thôn Phúc Hậu 2', 'thon-phuc-hau-2', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(39, 'Thôn Vạn Lộc', 'thon-van-loc', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(40, 'Thôn Gà', 'thon-ga', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(41, 'Tổ dân phố số 6', 'to-dan-pho-so-6', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(42, 'Thôn Đài Bi', 'thon-dai-bi', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(43, 'Thôn Hậu', 'thon-hau', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(44, 'Thôn Mít', 'thon-mit', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(45, 'Thôn Phúc Hậu 1', 'thon-phuc-hau-1', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(46, 'Thôn Dục Tú 2', 'thon-duc-tu-2', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(47, 'Thôn Sằn', 'thon-san', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(48, 'Thôn Dõng', 'thon-dong-d', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(49, 'Thôn Chùa', 'thon-chua', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(50, 'Thôn Văn Tinh', 'thon-van-tinh', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(51, 'Tổ dân phố số 4', 'to-dan-pho-so-4', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(52, 'Thôn Nghĩa Vũ', 'thon-nghia-vu', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(53, 'Thôn Hương', 'thon-huong', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(54, 'Tổ dân phố số 35 (+ Khu vực 382, Khu vực X89)', 'to-dan-pho-so-35', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(55, 'Thôn Nhồi Trên', 'thon-nhoi-tren', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(56, 'Thôn Phúc Thọ', 'thon-phuc-tho', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(57, 'Thôn Phúc Lộc', 'thon-phuc-loc', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(58, 'Tổ dân phố số 38', 'to-dan-pho-so-38', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(59, 'Tổ dân phố số 39', 'to-dan-pho-so-39', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(60, 'Thôn Nghĩa Lại', 'thon-nghia-lai', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(61, 'Thôn Ấp Tó', 'thon-ap-to', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(62, 'Thôn Bãi (UN)', 'thon-bai-un', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(63, 'Thôn Chợ (UN)', 'thon-cho-un', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(64, 'Tổ dân phố số 3', 'to-dan-pho-so-3', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(65, 'Tổ dân phố số 2', 'to-dan-pho-so-2', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(66, 'Tổ dân phố số 1', 'to-dan-pho-so-1', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(67, 'Thôn Lan Trì', 'thon-lan-tri', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(68, 'Thôn Lương Quán', 'thon-luong-quan', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(69, 'Tổ dân phố số 37', 'to-dan-pho-so-37', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(70, 'Tổ dân phố số 36 (+ Khu vực Công trình 6)', 'to-dan-pho-so-36', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(71, 'Khu tập thể Địa chất', 'khu-tap-the-dia-chat', '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(72, 'Thôn Phố Chợ', 'thon-pho-cho', '2026-06-01 03:58:03', '2026-06-01 03:58:03');

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
  `announcements` longtext DEFAULT NULL,
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

INSERT INTO `eateries` (`id`, `user_id`, `name`, `slug`, `category_id`, `commune_id`, `description`, `announcements`, `address`, `phone`, `opening_hours`, `latitude`, `longitude`, `price_range`, `image_path`, `is_featured`, `rating`, `status`, `created_at`, `updated_at`) VALUES
(1, 7, 'Chợ Tó', 'cho-to-pa3MD', 8, 61, NULL, NULL, '4VP4+V46, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.138354, 105.85725, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780287931_mywq2Yvo.png', 0, 5.00, 'active', '2026-06-01 04:24:43', '2026-06-01 07:44:12'),
(2, 8, 'Chợ Trung Tâm Đông Anh', 'cho-trung-tam-dong-anh-P2ft0', 8, 57, NULL, NULL, '4VP4+V46, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.137701, 105.845718, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780288137_UXjj2A68.png', 0, 5.00, 'active', '2026-06-01 04:28:59', '2026-06-05 08:25:35'),
(3, 9, 'Chợ Sa (Cổ Loa)', 'cho-sa-co-loa-mf0hh', 8, 72, 'Chợ Sa trước thuộc thôn Chợ, nhưng nay đã tách ra lập thành một đơn vị hành chính độc lập trực thuộc xã Cổ Loa. Tuy là một đơn vị hành chính mới được hình thành song cái tên chợ Sa đã có từ lâu đời và vốn rất quen thuộc với cư dân  nơi đây.\r\n\r\nTên chợ Sa được dân gian giải thích vì đây là nơi đặt sa bàn kinh thành của vua An Dương Vương. Chợ Sa nằm trên bãi Sa của sông Thiếp (Hoàng Giang) bên tả ngạn, phía nam, bên ngoài thành ngoại Cổ Loa. Chợ họp ngay trên khu đất cao mỗi tháng 6 phiên vào các ngày 1, 6, 11, 16, 21 và 26 âm lịch. Đây tương truyền là điểm buôn bán của đô thị Cổ Loa ngày trước.\r\n\r\n Câu ca dao về các phiên chợ liền kề trong vùng:\r\n\r\n“Chợ Dâu là câu chợ Tó\r\n\r\nChợ Tó bó chợ Dọc\r\n\r\nChợ Dọc cọc chợ Sa\r\n\r\nChợ Sa là xà chợ Cói\r\n\r\nChợ Cói là bói chợ Dâu”.\r\n\r\nNhư vậy, có thể nói rằng vùng đất Cổ Loa xưa từ khi được chọn làm quốc đô, đến nay đã trở thành tụ điểm dân cư tập trung đông đúc. Các hoạt động kinh tế được đẩy mạnh hơn, một số nghề thủ công, đặc biệt là nghề đúc đồng vươn lên đỉnh cao của nghề đúc thời cổ. Cổ Loa xưa nổi bật lên là đô thị quan trọng thời cổ đại tuy yếu tố “thị”  chưa rõ ràng song được thể hiện ở một hệ thống chợ tiêu biểu là Chợ Sa. Dần dần yếu tố thị ngày càng phát triển hình thành nên cả một khu phố chợ Sa như ngày nay.', NULL, '4V5H+V3 Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.109657, 105.877639, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780300693_Vg4Wau60.jpeg', 0, 5.00, 'active', '2026-06-01 07:58:15', '2026-06-01 07:59:52'),
(4, 10, 'Chợ Mai Lâm', 'cho-mai-lam-Fg3D9', 8, 15, NULL, NULL, '3WQ2+H6J, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.088971, 105.900505, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780301331_M3Ijvbtr.jpg', 0, 5.00, 'active', '2026-06-01 08:08:51', '2026-06-01 08:08:51'),
(5, NULL, 'Chợ Dục Nội', 'cho-duc-noi-pIEle', 8, 7, NULL, NULL, '4VQG+G6 Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.138754, 105.875604, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780301489_jZdGGxdd.png', 0, 5.00, 'active', '2026-06-01 08:11:31', '2026-06-01 08:11:31'),
(6, NULL, 'Chợ Dục Tú	3', 'cho-duc-tu-3-TD0C7', 8, 32, NULL, NULL, '4V8W+V69, Unnamed Road, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.117158, 105.895619, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780301601_joTT7VRd.png', 0, 5.00, 'active', '2026-06-01 08:13:24', '2026-06-01 08:13:24'),
(7, NULL, 'Chợ văn hoá Du lịch Cổ Loa', 'cho-van-hoa-du-lich-co-loa-3Tkmb', 8, 49, NULL, NULL, '4V6C+VH, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.112188, 105.871437, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780301782_REOzXNar.png', 0, 5.00, 'active', '2026-06-01 08:16:24', '2026-06-01 08:16:24'),
(8, NULL, 'Chợ Du Nội', 'cho-du-noi-kItF3', 8, 15, NULL, NULL, '3VRV+7X9, Du Nội, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.090664, 105.89497, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780302003_72HMN6r3.png', 0, 5.00, 'active', '2026-06-01 08:20:06', '2026-06-01 08:20:06'),
(9, NULL, 'Chợ Mai Hiên', 'cho-mai-hien-etlwa', 8, 17, NULL, NULL, '3VPQ+JCW, Mai Hiên, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.086616, 105.888751, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780302105_7UHJjEYA.png', 0, 5.00, 'active', '2026-06-01 08:21:47', '2026-06-01 08:21:47'),
(10, NULL, 'Chợ Lực Canh', 'cho-luc-canh-bU73w', 8, 3, NULL, NULL, 'Lực Canh , Đông Anh, Hà Nội', NULL, NULL, 21.093673, 105.850313, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780302505_yK6ILuDh.png', 0, 5.00, 'active', '2026-06-01 08:28:27', '2026-06-01 08:28:27'),
(11, NULL, 'Chợ Xuân Canh', 'cho-xuan-canh-d8ZrN', 8, 4, NULL, NULL, '3VW2+FC9, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.096157, 105.851096, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780302611_ShFQdlxK.png', 0, 5.00, 'active', '2026-06-01 08:30:13', '2026-06-01 08:30:13'),
(12, NULL, 'Chợ Nhồi Dưới', 'cho-nhoi-duoi-rQBih', 8, 21, NULL, NULL, 'Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.117566, 105.870562, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780302727_alzO2Ff2.png', 0, 5.00, 'active', '2026-06-01 08:32:09', '2026-06-01 08:32:09'),
(13, NULL, 'Chợ Lý Nhân', 'cho-ly-nhan-tgo5z', 8, 9, NULL, NULL, 'Lý Nhân, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.106588, 105.886567, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780303997_yrbu9E5Z.png', 0, 5.00, 'active', '2026-06-01 08:53:18', '2026-06-01 08:53:18'),
(14, NULL, 'Chợ Dày Da', 'cho-day-da-tifcw', 8, 54, NULL, NULL, '4VR8+FF3, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.14115, 105.866177, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780304125_eR6yLod0.png', 0, 5.00, 'active', '2026-06-01 08:55:26', '2026-06-01 08:55:26'),
(15, NULL, 'Chợ Đông Trù', 'cho-dong-tru-YT5K4', 8, 1, NULL, NULL, '3VGG+RJ4, Đông Trù, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.077005, 105.876592, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780304221_EyQwimeV.png', 0, 5.00, 'active', '2026-06-01 08:57:02', '2026-06-01 08:57:02'),
(16, 2, 'Chợ Mạch Tràng', 'cho-mach-trang-oefHf', 8, 6, NULL, '[{\"id\":4,\"tag\":\"Thông Báo mới\",\"title\":\"Chợ nghỉ\",\"content\":\"Hôm nay các bà con nghỉ đi bay\",\"time\":\"Mới cập nhật\",\"color\":\"#ef4444\",\"created_at\":\"16:29 24\\/07\\/2026\"},{\"id\":1,\"tag\":\"🛡️ KIỂM ĐỊNH ATTP\",\"time\":\"Mới cập nhật\",\"title\":\"100% sạp đạt chuẩn ATTP Tháng 7\\/2026\",\"content\":\"Đoàn kiểm tra liên ngành đã nghiệm thu chất lượng nguồn gốc nông sản & vệ sinh quầy hàng.\",\"color\":\"#10B981\"},{\"id\":2,\"tag\":\"🧼 VỆ SINH ĐỊNH KỲ\",\"time\":\"18h00 Chủ Nhật\",\"title\":\"Lịch phun khử khuẩn toàn chợ\",\"content\":\"BQL tiến hành dọn vệ sinh tổng thể & phun tiêu độc khử khuẩn định kỳ vào cuối tuần.\",\"color\":\"#0ea5e9\"},{\"id\":3,\"tag\":\"🎪 SỰ KIỆN NÔNG SẢN\",\"time\":\"Sáng Thứ 7\",\"title\":\"Phiên Chợ Nông Sản Sạch Đông Anh\",\"content\":\"Quy tụ các hợp tác xã nông sản sạch, rau VietGAP & OCOP giá ưu đãi tại Khối B.\",\"color\":\"#f59e0b\"}]', '4V48+XPM, Cổ Loa, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.107475, 105.866782, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780304292_4x03RlDT.png', 0, 5.00, 'active', '2026-06-01 08:58:13', '2026-07-24 08:26:33'),
(17, NULL, 'Siêu Thị Lan Chi Đông Anh', 'sieu-thi-lan-chi-dong-anh-t0bez', 8, 64, NULL, NULL, 'KhốI 2A QL3, Đông Anh, Hà Nội, Việt Nam', '0916603888', NULL, 21.147096, 105.846628, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780304373_s0wSKT2J.png', 0, 5.00, 'active', '2026-06-01 08:59:34', '2026-06-01 08:59:34'),
(19, NULL, 'HTX nông nghiệp dược liệu công nghệ cao KOVI', 'htx-nong-nghiep-duoc-lieu-cong-nghe-cao-kovi-QBWB4', 4, 8, 'HTX nông nghiệp dược liệu công nghệ cao KOVI; địa chỉ Thôn Lộc Hà, xã Đông Anh; tên sản phẩm OCOP: Đông trùng hạ thảo tươi, Đông trùng hạ thảo khô, Đông trùng hạ thảo ký chủ nhộng tằm theo QĐ số 2008/QĐ/UBND ngày 07/04/2023.', NULL, '3VVM+38H, Lộc Hà, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.092694, 105.883345, '30.000 - 100.000', 'https://media.xadonganh.com/eateries/1781776354_GHX1lRWO.png', 1, 5.00, 'active', '2026-06-18 09:52:36', '2026-06-18 09:52:36'),
(20, NULL, 'HKD Trần Văn Tân', 'hkd-tran-van-tan-v2rPM', 4, 33, 'HKD Trần Văn Tân; địa chỉ Thôn Thạc Quả, xã Đông Anh; tên sản phẩm OCOP: Tượng phật Đại Thế Chí Bồ Tát, Song ngưu sinh tài theo QĐ số 2008/QĐ/UBND ngày 07/04/2023.', NULL, 'Thôn Thạc Quả, xã Đông Anh', NULL, NULL, 21.124558, 105.906103, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 03:48:39', '2026-06-19 03:48:59'),
(21, NULL, 'Công ty TNHH Hoàng Chiến Thắng', 'cong-ty-tnhh-hoang-chien-thang-IV8Bf', 4, 11, 'Công ty TNHH Hoàng Chiến Thắng; địa chỉ Thôn Đông Ngàn, xã Đông Anh; tên sản phẩm OCOP: Bánh gạo lứt, Bánh vừng vòng theo QĐ số 2008/QĐ/UBND ngày 07/04/2023', NULL, 'Số 29,xóm mít, Đông Ngàn, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.069422, 105.867833, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 03:52:08', '2026-06-19 03:52:08'),
(22, NULL, 'HTX dịch vụ nông nghiệp thôn Đoài', 'htx-dich-vu-nong-nghiep-thon-doai-jDNEK', 4, 2, 'HTX dịch vụ nông nghiệp thôn Đoài; địa chỉ Thôn Đoài, xã Đông Anh; tên sản phẩm OCOP: Tương Việt Hùng theo QĐ số 2008/QĐ/UBND ngày 07/04/2023', NULL, '4VQC+VR Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.139694, 105.872072, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 03:56:13', '2026-06-19 03:56:13'),
(23, NULL, 'HKD sản xuất và kinh doanh bánh ngọt Thuý Quyên', 'hkd-san-xuat-va-kinh-doanh-banh-ngot-thuy-quyen-hnGLZ', 4, 11, 'HKD sản xuất và kinh doanh bánh ngọt Thuý Quyên; địa chỉ Thôn Đông Ngàn, xã Đông Anh; tên sản phẩm OCOP: Bánh xốp vừng, Bánh sampa, Bánh trứng nhện theo QĐ số 560/QĐ/UBND ngày 17/01/2021.', NULL, 'Đông Ngàn ,Đông Anh ,Hà Nội', NULL, NULL, 21.070403, 105.866461, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 03:58:23', '2026-06-19 03:58:39'),
(24, NULL, 'HKD Thạo Loan', 'hkd-thao-loan-8PeUP', 4, 19, '. HKD Thạo Loan; địa chỉ Thôn Xuân Canh, xã Đông Anh; tên sản phẩm OCOP: Rượu gạo nếp Long Tửu, Rượu dâu Long tửu theo QĐ số 560/QĐ/UBND ngày 17/01/2021.', NULL, 'Thôn Xuân Canh, xã Đông Anh', NULL, NULL, 21.081878, 105.847897, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 04:01:36', '2026-06-19 04:01:36'),
(25, NULL, 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa', 'htx-dich-vu-nong-nghiep-kinh-doanh-tong-hop-co-loa-sXHK5', 4, 31, 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa; địa chỉ: Trung tâm Cổ Loa, xã Đông Anh; tên sản phẩm OCOP: hành lá, khoai tây theo QĐ số 560/QĐ/UBND ngày 17/01/2021', NULL, 'Trung tâm Cổ Loa, xã Đông Anh', NULL, NULL, 21.111982, 105.875818, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 04:06:49', '2026-06-19 04:06:49'),
(26, NULL, 'Cơ sở sản xuất thực phẩm Liêm Hiệp', 'o-so-san-xuat-thuc-pham-liem-hiep-O8FRl', 4, 20, 'Cơ sở sản xuất thực phẩm Liêm Hiệp; địa chỉ: thôn Thượng, xã Đông Anh; tên sản phẩm OCOP: Giò lụa, Chả lụa', NULL, 'thôn Thượng, xã Đông Anh', NULL, NULL, 21.123023, 105.876738, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 04:17:55', '2026-06-19 04:19:22');

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

-- --------------------------------------------------------

--
-- Table structure for table `food_tour_diaries`
--

CREATE TABLE `food_tour_diaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `food_tour_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `rating` int(11) NOT NULL DEFAULT 5,
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
-- Table structure for table `market_messages`
--

CREATE TABLE `market_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sender_name` varchar(255) NOT NULL,
  `sender_role` varchar(255) NOT NULL DEFAULT 'user',
  `stall_name` varchar(255) DEFAULT NULL,
  `message_text` text NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `private_stall_name` varchar(255) DEFAULT NULL,
  `private_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
(20, '2026_06_02_160000_create_cultural_activities_table', 2),
(21, '2026_06_02_180000_create_cultural_activities_all_connections', 3),
(22, '2026_06_03_145536_add_user_id_to_food_tours_table', 3),
(23, '2026_06_03_171918_create_password_otps_table', 3),
(24, '2026_06_19_100954_create_eatery_photos_table', 4),
(25, '2026_06_22_100528_add_indexes_to_tables', 5),
(26, '2026_06_05_080000_add_heritage_fields_to_ocop_products_table', 6),
(27, '2026_07_07_103715_create_personal_access_tokens_table', 6),
(28, '2026_07_07_105840_create_social_and_chat_tables', 6),
(29, '2026_07_07_161700_add_food_tour_id_to_messages_table', 6),
(30, '2026_07_07_162019_add_media_to_messages_table', 6),
(31, '2026_07_16_000000_create_shopping_cart_and_checkout_tables', 6),
(32, '2026_07_16_160108_add_is_reviewed_to_orders_table', 6),
(33, '2026_07_21_120000_add_seller_fields_to_ocop_products_table', 6),
(34, '2026_07_23_150000_add_stall_name_to_orders_table', 6),
(35, '2026_07_23_160000_create_market_messages_table', 6),
(36, '2026_07_23_170000_add_private_chat_to_market_messages_table', 6),
(37, '2026_07_23_180000_add_image_path_to_market_messages_table', 6),
(38, '2026_07_23_190000_add_stall_name_to_reviews_table', 6);

-- --------------------------------------------------------

--
-- Table structure for table `ocop_products`
--

CREATE TABLE `ocop_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `stall_name` varchar(255) DEFAULT NULL,
  `seller_name` varchar(255) DEFAULT NULL,
  `seller_phone` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
  `unit` varchar(30) DEFAULT NULL COMMENT 'Đơn vị tính (kg, bát, đĩa, mớ...)',
  `description` varchar(255) DEFAULT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `star_rating` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `heritage_year` varchar(255) DEFAULT NULL,
  `story` text DEFAULT NULL,
  `artisans` text DEFAULT NULL,
  `fun_fact` text DEFAULT NULL,
  `audio_narrative` text DEFAULT NULL,
  `ingredients` text DEFAULT NULL,
  `timeline` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ocop_products`
--

INSERT INTO `ocop_products` (`id`, `eatery_id`, `stall_name`, `seller_name`, `seller_phone`, `name`, `price`, `unit`, `description`, `image_path`, `star_rating`, `created_at`, `updated_at`, `heritage_year`, `story`, `artisans`, `fun_fact`, `audio_narrative`, `ingredients`, `timeline`) VALUES
(2, 19, NULL, NULL, NULL, 'Đông trùng hạ thảo tươi', NULL, 'kg', NULL, NULL, NULL, '2026-06-18 09:53:35', '2026-06-18 10:23:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 19, NULL, NULL, NULL, 'Đông trùng hạ thảo khô', NULL, 'kg', NULL, NULL, NULL, '2026-06-18 09:54:16', '2026-06-18 10:23:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 19, NULL, NULL, NULL, 'Đông trùng hạ thảo ký chủ nhộng tằm', NULL, 'kg', NULL, NULL, NULL, '2026-06-18 10:23:48', '2026-06-18 10:23:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 20, NULL, NULL, NULL, 'Tượng phật Đại Thế Chí Bồ Tát', NULL, 'kg', NULL, NULL, '4 sao', '2026-06-19 03:49:26', '2026-07-21 04:21:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 20, NULL, NULL, NULL, 'Song ngưu sinh tài', NULL, 'kg', NULL, NULL, '4 sao', '2026-06-19 03:49:40', '2026-07-21 04:21:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 21, NULL, NULL, NULL, 'Bánh gạo lứt', NULL, 'cái', NULL, NULL, '4 sao', '2026-06-19 03:52:32', '2026-07-21 04:19:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 21, NULL, NULL, NULL, 'Bánh vừng vòng', NULL, 'cái', NULL, NULL, '3 sao', '2026-06-19 03:52:43', '2026-07-21 04:19:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 22, NULL, NULL, NULL, 'Tương Việt Hùng', NULL, 'kg', NULL, 'https://media.xadonganh.com/ocop/1784607252_WLu4ciXg.png', '3 sao', '2026-06-19 03:56:45', '2026-07-21 04:14:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 23, NULL, NULL, NULL, 'Bánh xốp vừng', NULL, 'cái', NULL, NULL, '3 sao', '2026-06-19 03:58:55', '2026-07-21 04:18:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 23, NULL, NULL, NULL, 'Bánh sampa', NULL, 'cái', NULL, NULL, '3 sao', '2026-06-19 03:59:04', '2026-07-21 04:18:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 23, NULL, NULL, NULL, 'Bánh trứng nhện', NULL, 'cái', NULL, NULL, '3 sao', '2026-06-19 03:59:15', '2026-07-21 04:18:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 24, NULL, NULL, NULL, 'Rượu gạo nếp Long Tửu', NULL, 'kg', NULL, NULL, '3 sao', '2026-06-19 04:01:53', '2026-07-21 04:17:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 24, NULL, NULL, NULL, 'Rượu dâu Long tửu', NULL, 'kg', NULL, NULL, '3 sao', '2026-06-19 04:02:04', '2026-07-21 04:17:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 25, NULL, NULL, NULL, 'hành lá', NULL, 'kg', NULL, NULL, '3 sao', '2026-06-19 04:07:33', '2026-07-21 04:16:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 25, NULL, NULL, NULL, 'khoai tây', NULL, 'kg', NULL, NULL, '3 sao', '2026-06-19 04:07:45', '2026-07-21 04:16:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 21, NULL, NULL, NULL, 'Bánh Vòng Dừa', NULL, 'quả', NULL, NULL, '3 sao', '2026-06-19 04:13:08', '2026-07-21 04:20:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 24, NULL, NULL, NULL, 'Rượu mơ Long Tửu', NULL, 'kg', NULL, NULL, '3 sao', '2026-06-19 04:13:32', '2026-07-21 04:17:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 24, NULL, NULL, NULL, 'Rượu Bạch cúc Long Tửu', NULL, 'kg', NULL, NULL, '3 sao', '2026-06-19 04:13:44', '2026-07-21 04:17:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 21, NULL, NULL, NULL, 'Bánh vừng Cookies', NULL, 'cái', NULL, NULL, '3 sao', '2026-06-19 04:14:51', '2026-07-21 04:20:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 21, NULL, NULL, NULL, 'Bánh gạo thơm', NULL, 'cái', NULL, NULL, '3 sao', '2026-06-19 04:15:01', '2026-07-21 04:20:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 25, NULL, NULL, NULL, 'Bí đỏ', NULL, 'kg', NULL, NULL, '3 sao', '2026-06-19 04:15:48', '2026-07-21 04:16:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 25, NULL, NULL, NULL, 'Lạc nhân', NULL, 'kg', NULL, NULL, '3 sao', '2026-06-19 04:15:58', '2026-07-21 04:16:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 26, NULL, NULL, NULL, 'Giò lụa', NULL, 'đĩa', NULL, NULL, NULL, '2026-06-19 04:18:17', '2026-06-19 04:18:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 26, NULL, NULL, NULL, 'Chả lụa', 50000.00, 'đĩa', NULL, NULL, NULL, '2026-06-19 04:18:27', '2026-07-21 03:58:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 20, NULL, NULL, NULL, 'Kim ngưu sinh tài', NULL, 'kg', NULL, NULL, '4 sao', '2026-07-21 04:34:28', '2026-07-21 04:34:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 20, NULL, NULL, NULL, 'Cá chép hoá rồng', NULL, 'kg', NULL, NULL, '4 sao', '2026-07-21 04:34:38', '2026-07-21 04:34:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(100, 16, 'Gian hàng Ăn uống Cô Sinh', 'Nguyễn Thị Sinh', '0987654321', 'Bún Riêu cua', 20000.00, 'bát', 'Nguồn gốc: Mua trong làng. Hỗ trợ thanh toán VietQR ngân hàng MB: 0965194462. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(101, 16, 'Gian hàng Ăn uống Cô Sinh', 'Nguyễn Thị Sinh', '0987654321', 'Bún Chả', 20000.00, 'bát', 'Nguồn gốc: Mua trong làng. Hỗ trợ thanh toán VietQR ngân hàng MB: 0965194462. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(200, 16, 'Gian hàng Rau củ sạch Cô Sức', 'Đào Thị Sức', '0987111222', 'Cà chua', 25000.00, 'kg', 'Nguồn gốc: Mua trong làng, chợ Dâu. Hỗ trợ thanh toán VietQR ngân hàng MB: 0386394957. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(201, 16, 'Gian hàng Rau củ sạch Cô Sức', 'Đào Thị Sức', '0987111222', 'Cà rốt', 20000.00, 'kg', 'Nguồn gốc: Mua trong làng, chợ Dâu. Hỗ trợ thanh toán VietQR ngân hàng MB: 0386394957. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(300, 16, 'Gian hàng Thực phẩm khô Cô Thuyên', 'Trần Thị Thuyên', '0987333444', 'Miến', 60000.00, 'kg', 'Nguồn gốc: Mua chợ Tó. Hỗ trợ thanh toán VietQR ngân hàng tiền mặt. Không sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(301, 16, 'Gian hàng Thực phẩm khô Cô Thuyên', 'Trần Thị Thuyên', '0987333444', 'Mộc nhĩ', 120000.00, 'kg', 'Nguồn gốc: Mua chợ Tó. Hỗ trợ thanh toán VietQR ngân hàng tiền mặt. Không sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(400, 16, 'Gian hàng Hoa quả Cô Đảm', 'Dương Thị Đảm', '0368734245', 'Dưa hấu', 25000.00, 'kg', 'Nguồn gốc: Chợ Long Biên. Hỗ trợ thanh toán VietQR ngân hàng MB: 0368734245. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(401, 16, 'Gian hàng Hoa quả Cô Đảm', 'Dương Thị Đảm', '0368734245', 'Nhãn', 30000.00, 'kg', 'Nguồn gốc: Chợ Long Biên. Hỗ trợ thanh toán VietQR ngân hàng MB: 0368734245. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(500, 16, 'Gian hàng Rau củ Cô Vui', 'Đặng Thị Vui', '0968525536', 'Đỗ xanh', 20000.00, 'kg', 'Nguồn gốc: Chợ Vân Trì. Hỗ trợ thanh toán VietQR ngân hàng TPBank: 00005867700. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(501, 16, 'Gian hàng Rau củ Cô Vui', 'Đặng Thị Vui', '0968525536', 'Hành Tây', 20000.00, 'kg', 'Nguồn gốc: Chợ Vân Trì. Hỗ trợ thanh toán VietQR ngân hàng TPBank: 00005867700. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(600, 16, 'Gian hàng Thịt tươi Cô Mai', 'Đào Thị Mai', '0987555666', 'Thịt bò', 250000.00, 'kg', 'Nguồn gốc: Chợ đầu mối Bắc Thăng Long. Hỗ trợ thanh toán VietQR ngân hàng Techcombank: 2003198099. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(601, 16, 'Gian hàng Thịt tươi Cô Mai', 'Đào Thị Mai', '0987555666', 'Sách bò', 80000.00, 'kg', 'Nguồn gốc: Chợ đầu mối Bắc Thăng Long. Hỗ trợ thanh toán VietQR ngân hàng Techcombank: 2003198099. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(700, 16, 'Gian hàng Thực phẩm Cô Bắc', 'Hoàng Thị Bắc', '0989429862', 'Thịt lợn', 110000.00, 'kg', 'Nguồn gốc: Tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng MB: 8319736868. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(701, 16, 'Gian hàng Thực phẩm Cô Bắc', 'Hoàng Thị Bắc', '0989429862', 'Thịt gà', 110000.00, 'kg', 'Nguồn gốc: Tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng MB: 8319736868. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(800, 16, 'Gian hàng Ăn sáng Cô Hà', 'Lê Thị Hà', '0973756280', 'Xôi', 10000.00, 'phần', 'Nguồn gốc: Tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng MB: 0973756280. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(801, 16, 'Gian hàng Ăn sáng Cô Hà', 'Lê Thị Hà', '0973756280', 'Bánh mì', 10000.00, 'cái', 'Nguồn gốc: Tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng MB: 0973756280. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(900, 16, 'Gian hàng Thịt sạch Cô Hạnh', 'Nguyễn Thị Hạnh', '0975598024', 'Thịt lợn thăn sấn', 120000.00, 'kg', 'Nguồn gốc: Mua trong làng. Hỗ trợ thanh toán VietQR ngân hàng MB: 0975598024. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(901, 16, 'Gian hàng Thịt sạch Cô Hạnh', 'Nguyễn Thị Hạnh', '0975598024', 'Thịt lợn nạc vai', 110000.00, 'kg', 'Nguồn gốc: Mua trong làng. Hỗ trợ thanh toán VietQR ngân hàng MB: 0975598024. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1000, 16, 'Gian hàng Bánh cuốn & Bún Cô Kim', 'Nguyễn Thị Kim', '0384665182', 'Bánh cuốn', 40000.00, 'kg', 'Nguồn gốc: Mua trong làng, tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng Vietinbank: 105880816002. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1001, 16, 'Gian hàng Bánh cuốn & Bún Cô Kim', 'Nguyễn Thị Kim', '0384665182', 'Bún', 15000.00, 'kg', 'Nguồn gốc: Mua trong làng, tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng Vietinbank: 105880816002. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1100, 16, 'Gian hàng Thực phẩm Cô Lệ', 'Đào Thị Lệ', '0372213861', 'Lòng lợn', 70000.00, 'kg', 'Nguồn gốc: Mua trong làng, tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng MB: 0372213861. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1101, 16, 'Gian hàng Thực phẩm Cô Lệ', 'Đào Thị Lệ', '0372213861', 'Thịt má đào', 150000.00, 'kg', 'Nguồn gốc: Mua trong làng, tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng MB: 0372213861. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1200, 16, 'Gian hàng Ẩm thực Cô Mai', 'Nguyễn Thị Mai', '0356363290', 'Bún thịt chó', 35000.00, 'bát', 'Nguồn gốc: Tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng MB: 0356363290. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1201, 16, 'Gian hàng Ẩm thực Cô Mai', 'Nguyễn Thị Mai', '0356363290', 'Chả thịt chó', 120000.00, 'đĩa', 'Nguồn gốc: Tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng MB: 0356363290. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1300, 16, 'Gian hàng Rau củ sạch Cô Bốn', 'Nguyễn Thị Bốn', '0394883286', 'Giá đỗ', 17000.00, 'kg', 'Nguồn gốc: Mua trong làng, tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng tiền mặt. Không sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1301, 16, 'Gian hàng Rau củ sạch Cô Bốn', 'Nguyễn Thị Bốn', '0394883286', 'Rau muống', 4000.00, 'mớ', 'Nguồn gốc: Mua trong làng, tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng tiền mặt. Không sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1400, 16, 'Gian hàng Gạo sạch Cô Huê', 'Cao Thị Huê', '0336505025', 'Gạo ST25', 15000.00, 'kg', 'Nguồn gốc: Cơ sở sản xuất gạo sạch Hải Tiến. Hỗ trợ thanh toán VietQR ngân hàng Techcombank: 19037409177011. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1401, 16, 'Gian hàng Gạo sạch Cô Huê', 'Cao Thị Huê', '0336505025', 'Gạo Khang dân', 14000.00, 'kg', 'Nguồn gốc: Cơ sở sản xuất gạo sạch Hải Tiến. Hỗ trợ thanh toán VietQR ngân hàng Techcombank: 19037409177011. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1500, 16, 'Gian hàng Hoa quả Cô Tuyên', 'Nguyễn Thị Tuyên', '0398214886', 'Dưa hấu', 25000.00, 'kg', 'Nguồn gốc: Chợ Long Biên. Hỗ trợ thanh toán VietQR ngân hàng MB: 001212071985. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1501, 16, 'Gian hàng Hoa quả Cô Tuyên', 'Nguyễn Thị Tuyên', '0398214886', 'Dừa', 15000.00, 'quả', 'Nguồn gốc: Chợ Long Biên. Hỗ trợ thanh toán VietQR ngân hàng MB: 001212071985. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1600, 16, 'Gian hàng Đặc sản Giò chả Cô Hòa', 'Nguyễn Thị Hòa', '0977203965', 'Nem tai thính', 200000.00, 'kg', 'Nguồn gốc: Mua trong làng, tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng MB: 0977203965. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1601, 16, 'Gian hàng Đặc sản Giò chả Cô Hòa', 'Nguyễn Thị Hòa', '0977203965', 'Nem bì', 80000.00, 'kg', 'Nguồn gốc: Mua trong làng, tự sản xuất. Hỗ trợ thanh toán VietQR ngân hàng MB: 0977203965. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1700, 16, 'Gian hàng Thực phẩm Cô Hà', 'Đào Thị Hà', '0986131738', 'Cá bống vàng khô', 130000.00, 'kg', 'Nguồn gốc: Chợ Tó. Hỗ trợ thanh toán VietQR ngân hàng Techcombank: 19037126588013. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1701, 16, 'Gian hàng Thực phẩm Cô Hà', 'Đào Thị Hà', '0986131738', 'Xúc xích', 50000.00, 'túi 500g', 'Nguồn gốc: Chợ Tó. Hỗ trợ thanh toán VietQR ngân hàng Techcombank: 19037126588013. Có sử dụng smartphone.', NULL, '4 sao', '2026-07-23 02:35:07', '2026-07-23 02:35:07', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1938, 2, 'Gian hàng Hoa tươi Cô Ngà', 'Nguyễn Thị Ngà( Thanh)', 'Cần cập nhật thông tin', 'Hoa tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1939, 2, 'Gian hàng Hoa tươi Cô Huyền', 'Nguyễn Thị Thanh Huyền', 'Cần cập nhật thông tin', 'Hoa tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1940, 2, 'Gian hàng Hoa tươi Cô Hương', 'Trịnh Thị Hương', 'Cần cập nhật thông tin', 'Hoa tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1941, 2, 'Gian hàng Hoa quả Cô Nhung', 'Phạm Thị Nhung', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1942, 2, 'Gian hàng Hoa quả Cô Phượng', 'Phạm Thị Phượng', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1943, 2, 'Gian hàng Hoa quả Cô Ngần', 'Trần Thị Ngần', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1944, 2, 'Gian hàng Hoa quả Cô Hằng', 'Đặng Thúy Hằng', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1945, 2, 'Gian hàng Hoa quả Cô Loan', 'Hoàng Thị Loan', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1946, 2, 'Gian hàng Hoa quả Cô Nên', 'Nguyễn Thị Nên', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1947, 2, 'Gian hàng Chè & Nước giải khát Cô Thu', 'Nguyễn Thị Thu', 'Cần cập nhật thông tin', 'Chè & Nước giải khát', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1948, 2, 'Gian hàng Hoa quả Cô Thùy', 'Nguyễn Thị Thùy', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1949, 2, 'Gian hàng Hoa quả Cô Huyền', 'Đinh Thị Thanh Huyền', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1950, 2, 'Gian hàng Hoa quả Cô Thơ', 'Bùi Thị Thơ', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1951, 2, 'Gian hàng Hoa quả Cô Mý', 'Lê Thị Mý', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1952, 2, 'Gian hàng Hoa quả Cô Thu', 'Ngô Thị Thu', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1953, 2, 'Gian hàng Hoa quả Cô Hạnh', 'Lê Thị Hạnh', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1954, 2, 'Gian hàng Hoa quả Cô Hạnh (Hộ 17)', 'Đinh Thị Hồng Hạnh', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1955, 2, 'Gian hàng Hoa quả Cô Mùi', 'Đỗ Thị Mùi', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1956, 2, 'Gian hàng Vịt quay Cô Tú', 'Hoàng Thị Tú', 'Cần cập nhật thông tin', 'Vịt quay', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1957, 2, 'Gian hàng Ăn uống Cô Quế', 'Nguyễn Thị Quế', 'Cần cập nhật thông tin', 'Ăn uống', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1958, 2, 'Gian hàng Ăn uống Cô Huyền', 'Nguyễn Thị Lệ Huyền', 'Cần cập nhật thông tin', 'Ăn uống', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1959, 2, 'Gian hàng Ăn uống Cô Lan', 'Trịnh Thị Lan', 'Cần cập nhật thông tin', 'Ăn uống', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1960, 2, 'Gian hàng Ăn uống Cô Huế', 'Ngô Thành Huế', 'Cần cập nhật thông tin', 'Ăn uống', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1961, 2, 'Gian hàng Ăn uống Cô Hậu', 'Tạ Thị Hậu', 'Cần cập nhật thông tin', 'Ăn uống', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1962, 2, 'Gian hàng Đậu & Nông sản Cô Hằng', 'Nguyễn Thúy Hằng', 'Cần cập nhật thông tin', 'Đậu & Nông sản', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1963, 2, 'Gian hàng Đậu & Nông sản Chú Đại', 'Trần Văn Đại', 'Cần cập nhật thông tin', 'Đậu & Nông sản', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1964, 2, 'Gian hàng Đậu & Nông sản Cô Chính', 'Nguyễn Đăng Chính', 'Cần cập nhật thông tin', 'Đậu & Nông sản', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1965, 2, 'Gian hàng Rau xanh sạch Cô Lý', 'Lê Thị Lý( Lê Thị Núi)', 'Cần cập nhật thông tin', 'Rau xanh sạch', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1966, 2, 'Gian hàng Măng & Rau củ Cô Thúy', 'Đặng Thị Thúy', 'Cần cập nhật thông tin', 'Măng & Rau củ', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1967, 2, 'Gian hàng Rau xanh sạch Cô Phượng', 'Đào Thị Phượng', 'Cần cập nhật thông tin', 'Rau xanh sạch', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1968, 2, 'Gian hàng Rau xanh sạch Cô Nở', 'Hoàng Thị Nở', 'Cần cập nhật thông tin', 'Rau xanh sạch', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1969, 2, 'Gian hàng Rau xanh sạch Cô Huyền', 'Nguyễn Thị Huyền', 'Cần cập nhật thông tin', 'Rau xanh sạch', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1970, 2, 'Gian hàng Cua xay tươi Cô Nhung', 'Nguyễn Thị Nhung', 'Cần cập nhật thông tin', 'Cua xay tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1971, 2, 'Gian hàng Rau xanh sạch Cô Nụ', 'Ngô Thị Nụ', 'Cần cập nhật thông tin', 'Rau xanh sạch', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1972, 2, 'Gian hàng Rau gia vị Cô Ngoan', 'Hoàng Thị Ngoan', 'Cần cập nhật thông tin', 'Rau gia vị', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1973, 2, 'Gian hàng Rau xanh sạch Cô Thao', 'Trần Thị Thao', 'Cần cập nhật thông tin', 'Rau xanh sạch', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1974, 2, 'Gian hàng Gia cầm Cô Dậu', 'Tô Quốc Dậu', 'Cần cập nhật thông tin', 'Gia cầm', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1975, 2, 'Gian hàng Gia cầm Cô Nhuận', 'Nguyễn Thị Nhuận', 'Cần cập nhật thông tin', 'Gia cầm', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1976, 2, 'Gian hàng Gia cầm Chú Thủy', 'Trần Văn Thủy', 'Cần cập nhật thông tin', 'Gia cầm', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1977, 2, 'Gian hàng Gia cầm Cô Bách', 'Phạm Thị Bách', 'Cần cập nhật thông tin', 'Gia cầm', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1978, 2, 'Gian hàng Gia cầm Cô Thủy', 'Trần Đình Thủy (Thảo)', 'Cần cập nhật thông tin', 'Gia cầm', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1979, 2, 'Gian hàng Gia cầm Chú Hòa', 'Trần Văn Hòa', 'Cần cập nhật thông tin', 'Gia cầm', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1980, 2, 'Gian hàng Gia cầm Cô Xuyến', 'Nguyễn Thị Xuyến', 'Cần cập nhật thông tin', 'Gia cầm', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1981, 2, 'Gian hàng Hải sản tươi Cô Lâm', 'Hoàng Ngọc Lâm', 'Cần cập nhật thông tin', 'Hải sản tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1982, 2, 'Gian hàng Cá tươi Cô Tú', 'Phạm Thị Tú', 'Cần cập nhật thông tin', 'Cá tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1983, 2, 'Gian hàng Cua xay tươi Cô Trang', 'Trần Thị Kiều Trang ( Mai)', 'Cần cập nhật thông tin', 'Cua xay tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1984, 2, 'Gian hàng Tôm & Cá tươi Cô Thoan', 'Nguyễn Thị Thoan', 'Cần cập nhật thông tin', 'Tôm & Cá tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1985, 2, 'Gian hàng Cá tươi Cô Phượng', 'Diệp Thị Phượng', 'Cần cập nhật thông tin', 'Cá tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1986, 2, 'Gian hàng Cá tươi Cô Dự', 'Nguyễn Thị Dự', 'Cần cập nhật thông tin', 'Cá tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1987, 2, 'Gian hàng Cá tươi Cô Nền', 'Trần Thị Nền', 'Cần cập nhật thông tin', 'Cá tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1988, 2, 'Gian hàng Hoa tươi Cô Ninh', 'Đồng Thị Ninh', 'Cần cập nhật thông tin', 'Hoa tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1989, 2, 'Gian hàng Bánh & Đồ ăn vặt Cô Mùi', 'Ngô Thị Mùi', 'Cần cập nhật thông tin', 'Bánh & Đồ ăn vặt', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1990, 2, 'Gian hàng Bánh & Đồ ăn vặt Cô Dung', 'Nguyễn Thị Dung', 'Cần cập nhật thông tin', 'Bánh & Đồ ăn vặt', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1991, 2, 'Gian hàng Sửa chữa quần áo Cô Duy', 'Hoàng Thị Duy', 'Cần cập nhật thông tin', 'Sửa chữa quần áo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1992, 2, 'Gian hàng Sửa chữa quần áo Cô Quỳnh', 'Hoàng Thị Quỳnh', 'Cần cập nhật thông tin', 'Sửa chữa quần áo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1993, 2, 'Gian hàng Thực phẩm khô Cô Tuyên', 'Vũ Thị Tuyên', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1994, 2, 'Gian hàng Đồ gia dụng Cô Duyên', 'Hoàng Thị Duyên', 'Cần cập nhật thông tin', 'Đồ gia dụng', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1995, 2, 'Gian hàng Thực phẩm khô Cô Tho', 'Lương Thị Tho', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1996, 2, 'Gian hàng Bánh quấn Cô Khuy', 'Ngô Thị Khuy', 'Cần cập nhật thông tin', 'Bánh quấn', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1997, 2, 'Gian hàng Rau xanh sạch Cô Thuật', 'Nguyễn Thị Thuật', 'Cần cập nhật thông tin', 'Rau xanh sạch', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1998, 2, 'Gian hàng Thực phẩm khô Cô Hải', 'Trần Thị Hải', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(1999, 2, 'Gian hàng Bánh quấn Cô Nự', 'Đồng Thị Nự', 'Cần cập nhật thông tin', 'Bánh quấn', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2000, 2, 'Gian hàng Hải sản tươi Chú Sơn', 'Phạm Văn Sơn', 'Cần cập nhật thông tin', 'Hải sản tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2001, 2, 'Gian hàng Hoa quả Cô Nụ', 'Nguyễn Thị Nụ', 'Cần cập nhật thông tin', 'Hoa quả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2002, 2, 'Gian hàng Thịt lợn tươi Cô Ánh', 'Nguyễn Thị Ánh', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2003, 2, 'Gian hàng Thịt gia cầm Cô Anh', 'Đỗ Thị Ngọc Anh', 'Cần cập nhật thông tin', 'Thịt gia cầm', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2004, 2, 'Gian hàng Thịt lợn tươi Cô Chuyên', 'Nguyễn Thị Chuyên', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2005, 2, 'Gian hàng Thịt lợn tươi Cô Hường', 'Nguyễn Thị Hường', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2006, 2, 'Gian hàng Thịt bò tươi Cô Hạ', 'Lê Thị Hạ', 'Cần cập nhật thông tin', 'Thịt bò tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2007, 2, 'Gian hàng Thịt lợn tươi Cô Diện', 'Trần Thị Diện', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2008, 2, 'Gian hàng Thịt lợn tươi Cô Lý', 'Trịnh Thị Lý', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2009, 2, 'Gian hàng Thịt lợn tươi Cô Luân', 'Nguyễn Thị Luân', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2010, 2, 'Gian hàng Thịt lợn tươi Cô Tâm', 'Nguyễn Thị Thanh Tâm', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2011, 2, 'Gian hàng Thịt lợn tươi Cô Vân', 'Hoàng Thị Thúy Vân', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2012, 2, 'Gian hàng Thịt lợn tươi Cô Định', 'Đặng Thị Định', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2013, 2, 'Gian hàng Thịt lợn tươi Cô Tư', 'Lê Đức Tư', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2014, 2, 'Gian hàng Thịt lợn tươi Cô Quyên', 'Nguyễn Thị Quyên', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2015, 2, 'Gian hàng Thịt lợn tươi Cô Bắc', 'Nguyễn Thị Bắc', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2016, 2, 'Gian hàng Thịt lợn tươi Cô Thi', 'Nguyễn Thị Thi ( Minh)', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2017, 2, 'Gian hàng Thịt bò tươi Cô Thu', 'Nguyễn Thị Thu', 'Cần cập nhật thông tin', 'Thịt bò tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2018, 2, 'Gian hàng Thịt lợn tươi Cô Hải', 'Nguyễn Thị Hồng Hải', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2019, 2, 'Gian hàng Thịt bò tươi Cô Tươi', 'Nguyễn Thị Tươi', 'Cần cập nhật thông tin', 'Thịt bò tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2020, 2, 'Gian hàng Thịt lợn tươi Cô Tiến', 'Ngô Thị Tiến', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2021, 2, 'Gian hàng Thịt lợn tươi Cô Vân (Hộ 84)', 'Đinh Thị Vân', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2022, 2, 'Gian hàng Thịt lợn tươi Cô Luyên', 'Nguyễn Thị Luyên', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2023, 2, 'Gian hàng Thịt lợn tươi Cô Mai', 'Nguyễn Thị Mai', 'Cần cập nhật thông tin', 'Thịt lợn tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2024, 2, 'Gian hàng Hải sản tươi Cô Dung', 'Bùi Thị Dung', 'Cần cập nhật thông tin', 'Hải sản tươi', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2025, 2, 'Gian hàng Giò chả Cô Lan', 'Nguyễn Thị Lan', 'Cần cập nhật thông tin', 'Giò chả', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2026, 2, 'Gian hàng Tạp hóa Cô Hường', 'Nguyễn Thị Lệ Hường', 'Cần cập nhật thông tin', 'Tạp hóa', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2027, 2, 'Gian hàng Cắt tóc Chú Dương', 'Trần Văn Dương', 'Cần cập nhật thông tin', 'Cắt tóc', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2028, 2, 'Gian hàng Gội đầu & Làm đẹp Cô Thủy', 'Nguyễn Thị Thủy', 'Cần cập nhật thông tin', 'Gội đầu & Làm đẹp', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2029, 2, 'Gian hàng Gội đầu & Làm đẹp Cô Lương', 'Đỗ Thị Lương', 'Cần cập nhật thông tin', 'Gội đầu & Làm đẹp', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2030, 2, 'Gian hàng Phụ kiện Cô Hương', 'Thái Thị Hương', 'Cần cập nhật thông tin', 'Phụ kiện', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2031, 2, 'Gian hàng Tạp hóa & Bánh kẹo Chú Thái', 'Nguyễn Văn Thái', 'Cần cập nhật thông tin', 'Tạp hóa & Bánh kẹo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2032, 2, 'Gian hàng Tạp hóa Cô Nga', 'Lê Thị Thu Nga', 'Cần cập nhật thông tin', 'Tạp hóa', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2033, 2, 'Gian hàng Đồ gia dụng Cô Oanh', 'Trần Thị Kim Oanh', 'Cần cập nhật thông tin', 'Đồ gia dụng', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2034, 2, 'Gian hàng Đồ gia dụng Cô Thu', 'Nguyễn Thị Xuân Thu', 'Cần cập nhật thông tin', 'Đồ gia dụng', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2035, 2, 'Gian hàng Gội đầu & Làm đẹp Cô Liên', 'Hoàng Thị Liên', 'Cần cập nhật thông tin', 'Gội đầu & Làm đẹp', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2036, 2, 'Gian hàng Tạp hóa & Bánh kẹo Cô Mai', 'Nguyễn Thị Phương Mai', 'Cần cập nhật thông tin', 'Tạp hóa & Bánh kẹo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2037, 2, 'Gian hàng Tạp hóa & Bánh kẹo Cô Hương', 'Ngô Thị Hương', 'Cần cập nhật thông tin', 'Tạp hóa & Bánh kẹo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2038, 2, 'Gian hàng Thực phẩm khô Cô Thủy', 'Phạm Thị Thu Thủy', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2039, 2, 'Gian hàng Thực phẩm khô Cô Tường', 'Nguyễn Thị Tường', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2040, 2, 'Gian hàng Thực phẩm khô Cô Giang', 'Nguyễn Thị Giang', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2041, 2, 'Gian hàng Thực phẩm khô Cô Quyên', 'Vương Thị Quyên', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2042, 2, 'Gian hàng Thực phẩm khô Cô Hằng', 'Hoàng Thị Lệ Hằng', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2043, 2, 'Gian hàng Thực phẩm khô Cô Hiên', 'Lê Thị Hiên', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2044, 2, 'Gian hàng Thực phẩm khô Cô Mùi', 'Nguyễn Thị Mùi', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2045, 2, 'Gian hàng Đồ gia dụng Cô Thanh', 'Trần Thị Kim Thanh', 'Cần cập nhật thông tin', 'Đồ gia dụng', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2046, 2, 'Gian hàng Bún & Cơm Cô Trang', 'Hoàng Thị Thu Trang', 'Cần cập nhật thông tin', 'Bún & Cơm', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2047, 2, 'Gian hàng Quần áo Cô Hoa', 'Nguyễn Thị Hoa', 'Cần cập nhật thông tin', 'Quần áo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2048, 2, 'Gian hàng Phở gà Cô Hồng', 'Nguyễn Thị Ánh Hồng', 'Cần cập nhật thông tin', 'Phở gà', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2049, 2, 'Gian hàng Cần cập nhật thông tin Cô Nhượng', 'Nguyễn Thị Nhượng', 'Cần cập nhật thông tin', 'Cần cập nhật thông tin', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2050, 2, 'Gian hàng Bún cá rô đồng Cô Anh', 'Nguyễn Vân Anh', 'Cần cập nhật thông tin', 'Bún cá rô đồng', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2051, 2, 'Gian hàng Cơm bình dân Chú Nguyên', 'Nguyễn Văn Nguyên', 'Cần cập nhật thông tin', 'Cơm bình dân', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2052, 2, 'Gian hàng Ăn vặt Cô Thanh', 'Tô Thị Mai Thanh', 'Cần cập nhật thông tin', 'Ăn vặt', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2053, 2, 'Gian hàng Tạp hóa Cô Trang', 'Nguyễn Huyền Trang', 'Cần cập nhật thông tin', 'Tạp hóa', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2054, 2, 'Gian hàng Văn phòng phẩm Cô Thành', 'Nguyễn Trung Thành', 'Cần cập nhật thông tin', 'Văn phòng phẩm', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2055, 2, 'Gian hàng Cà phê Chú Quyền', 'Vương Văn Quyền', 'Cần cập nhật thông tin', 'Cà phê', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:52:25', '2026-07-24 03:52:25', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2056, 1, 'Gian hàng Chè & Ốc Cô Anh', 'Nguyễn Vân Anh', '0817298828', 'Chè & Ốc', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2057, 1, 'Gian hàng Quần áo Cô Dung', 'Nguyễn Thuỳ Dung', '0986702543', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2058, 1, 'Gian hàng Quần áo Cô Bích', 'Cao Thị Bích', 'Cần cập nhật thông tin', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2059, 1, 'Gian hàng Túi xách & Giày dép Cô Hương', 'Nguyễn Thị Thanh Hương', '0852122158', 'Túi xách & Giày dép', 0.00, 'cần cập nhật', 'Địa chỉ: Tổ 12 Thị trấn Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2060, 1, 'Gian hàng Túi xách & Giày dép Cô Hạnh', 'Cao Thị Hạnh', '0344838471', 'Túi xách & Giày dép', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2061, 1, 'Gian hàng Quần áo Cô Hằng', 'Hoàng Thu Hằng', '0984420605', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Chợ, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2062, 1, 'Gian hàng Quần áo Cô Lạng', 'Cao Thị Lạng', '0352528808', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2063, 1, 'Gian hàng Quần áo Cô Biên', 'Trần Thị Biên', '0388998974', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2064, 1, 'Gian hàng Quần áo Cô Thuỳ', 'Trần Thị Thuỳ', '0982456057', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2065, 1, 'Gian hàng Quần áo Cô Huyền', 'Ngô Thị Thanh Huyền', 'Cần cập nhật thông tin', 'Quần áo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2066, 1, 'Gian hàng Quần áo Cô Dậu', 'Đình Thị Dậu', '0866602536', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2067, 1, 'Gian hàng Quần áo Cô Chanh', 'Trần Thị Chanh', '0345244268', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2068, 1, 'Gian hàng Quần áo Cô Vui', 'Hoàng Thị Vui', '0397430343', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL);
INSERT INTO `ocop_products` (`id`, `eatery_id`, `stall_name`, `seller_name`, `seller_phone`, `name`, `price`, `unit`, `description`, `image_path`, `star_rating`, `created_at`, `updated_at`, `heritage_year`, `story`, `artisans`, `fun_fact`, `audio_narrative`, `ingredients`, `timeline`) VALUES
(2069, 1, 'Gian hàng Vải Cô Ninh', 'Hữu Thị Ninh', '0972435795', 'Vải', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2070, 1, 'Gian hàng Vải Cô May', 'Nguyễn Thị May', 'Cần cập nhật thông tin', 'Vải', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2071, 1, 'Gian hàng Quần áo Cô Hiệp', 'Hoàng Thị Hiệp', '0365468539', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2072, 1, 'Gian hàng Quần áo Cô Thơ', 'Ngô Thị Thơ', '0971853093', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2073, 1, 'Gian hàng Quần áo Cô Luyến', 'Lê Thị Kim Luyến', '0984846228', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Nguyên khê, Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2074, 1, 'Gian hàng Phụ kiện thời trang Cô Anh', 'Nguyễn Thị Lan Anh', '0396163430', 'Phụ kiện thời trang', 0.00, 'cần cập nhật', 'Địa chỉ: Cổ dương, Tiên Dương. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2075, 1, 'Gian hàng Quần áo Cô Oanh', 'Lê Thị Kim Oanh', '0382822945', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2076, 1, 'Gian hàng Quần áo Cô Lan', 'Nguyễn Thị Lan', '0366555476', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Bến, Kim Nõ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2077, 1, 'Gian hàng Quần áo Cô Quyên', 'Đặng Thị Quyên', '0984036228', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Văn Thượng, Xuân Canh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2078, 1, 'Gian hàng Quần áo Cô Thu', 'Vũ Thị Thu', '0984386612', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Cầu Cả, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2079, 1, 'Gian hàng Quần áo Cô Ngọc', 'Nguyễn Thị Lan Ngọc', '0989418822', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2080, 1, 'Gian hàng Quần áo Cô Yến', 'Hoàng Thị Yến', '0962556170', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2081, 1, 'Gian hàng Quần áo Cô Hường', 'Đặng Thị Hường', '0985561955', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2082, 1, 'Gian hàng Quần áo Cô Uyên', 'Lê Thị Kim Uyên', '0335297460', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Đản Dị, Uy nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2083, 1, 'Gian hàng Quần áo Cô Luyên', 'Hoàng Thị Luyên', '0984391816', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2084, 1, 'Gian hàng Quần áo Cô Sự', 'Trần Thị Sự', '0936237905', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2085, 1, 'Gian hàng Quần áo Cô Hằng (Hộ 30)', 'Hoàng Thị Hằng', '0355372364', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2086, 1, 'Gian hàng Quần áo Cô Ngân', 'Đỗ Thị Ngân', '0914608365', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2087, 1, 'Gian hàng Quần áo Cô Sen', 'Đặng Thị Sen', '0862364980', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2088, 1, 'Gian hàng Quần áo Cô Oanh (Hộ 33)', 'Dương Thị Kim Oanh', '094990668', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2089, 1, 'Gian hàng Quần áo Cô Oanh (Hộ 34)', 'Dương Thị Kim Oanh', '0824345186', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2090, 1, 'Gian hàng Quần áo Cô Tuyết', 'Hoàng Thị Tuyết', '0359590053', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2091, 1, 'Gian hàng Quần áo Cô Cánh', 'Cao Thị Ngọc Cánh', '0392124929', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2092, 1, 'Gian hàng Quần áo Cô Phương', 'Vương Thị Phương', '0386039893', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2093, 1, 'Gian hàng Quần áo Cô Hanh', 'Hoàng Thị Hanh', '0972462305', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2094, 1, 'Gian hàng Quần áo Cô Miền', 'Nguyễn Cao Miền', '0915021004', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2095, 1, 'Gian hàng Quần áo Cô Hoài', 'Hoàng Thị Hoài', '0363694384', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2096, 1, 'Gian hàng Quần áo Cô Hương', 'Hoàng Thị Hương', '0916541565', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Phố Tó, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2097, 1, 'Gian hàng Quần áo Cô Hạnh', 'Lê Thị Hiền Hạnh', '0966149960', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Văn Thượng, Xuân Canh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2098, 1, 'Gian hàng Quần áo Cô Phượng', 'Đặng Thị Phượng', '0366488172', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Phúc Lộc, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2099, 1, 'Gian hàng Quần áo Cô Nguyện', 'Đặng Thị Nguyện', '0969101744', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2100, 1, 'Gian hàng Quần áo Cô Hiền', 'Đinh Thị Thu Hiền', '0983168407', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Lỗ Khê, Liên Hà. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2101, 1, 'Gian hàng Quần áo Cô Hằng (Hộ 46)', 'Lê Thị Thuý Hằng', '0384708099', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Đản Mỗ, Uy nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2102, 1, 'Gian hàng Quần áo Cô Thuý', 'Trần Thị Thuý', '0983753532', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Chùa, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2103, 1, 'Gian hàng Quần áo Cô Quyến', 'Trần Thị Quyến', '0989252741', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Mít, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2104, 1, 'Gian hàng Quần áo Cô Lương', 'Hoàng Thị Lương', '0976842867', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2105, 1, 'Gian hàng Quần áo Cô Ngọc (Hộ 50)', 'Đăng Thị Ngọc', '0888520490', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Bãi, Oai nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2106, 1, 'Gian hàng Quần áo Cô Hậu', 'Nguyễn Thị Hậu', '0932245339', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2107, 1, 'Gian hàng Quần áo Cô Yêm', 'Nguyễn Thị Yêm', '0964638571', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Cầu Cả, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2108, 1, 'Gian hàng Quần áo Cô Thực', 'Nguyễn Thị Thực', '0389394320', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2109, 1, 'Gian hàng Quần áo Cô Mây', 'Vương Thị Mây', '0372693796', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2110, 1, 'Gian hàng Quần áo Cô Nga', 'Đặng Thị Kim Nga', '0369961688', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2111, 1, 'Gian hàng Quần áo Cô Liêm', 'Nguyễn Thị Kiều Liêm', '0833824908', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2112, 1, 'Gian hàng Quần áo Chú Khôi', 'Nguyễn Hoa Khôi ( Nguyễn Văn Huy)', '0333250673', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Cầu Cả, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2113, 1, 'Gian hàng Quần áo Cô Phương (Hộ 58)', 'Nguyễn Thị Lan Phương', '0368882408', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2114, 1, 'Gian hàng Quần áo Cô Dung (Hộ 59)', 'Nguyễn Thị Dung', 'Cần cập nhật thông tin', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Cầu Cả, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2115, 1, 'Gian hàng Quần áo Cô Thạch', 'Trần Thị Thạch', '0987505772', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Mít, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2116, 1, 'Gian hàng Quần áo Cô Ánh', 'Lê Thị Ánh', 'Cần cập nhật thông tin', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Phúc Lộc, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2117, 1, 'Gian hàng Mỹ phẩm & Làm đẹp Cô Dung', 'Nguyễn Thị Dung', '0362632703', 'Mỹ phẩm & Làm đẹp', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2118, 1, 'Gian hàng Cần cập nhật thông tin Cô Tuyết', 'Đào Thị Tuyết', 'Cần cập nhật thông tin', 'Cần cập nhật thông tin', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2119, 1, 'Gian hàng Thiết bị điện & nước Cô Hạnh', 'Trịnh Thị Bích Hạnh', '0947929898', 'Thiết bị điện & nước', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2120, 1, 'Gian hàng Thiết bị điện & nước Cô Đìa', 'Mạc Thị Đìa', '0966579651', 'Thiết bị điện & nước', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2121, 1, 'Gian hàng Thiết bị điện & nước Cô Vượng', 'Vũ Thị Bích Vượng ( Bích)', '0813359650', 'Thiết bị điện & nước', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2122, 1, 'Gian hàng Thiết bị điện & nước Cô Thuỷ', 'Nguyễn Thị Thuỷ', '0912354500', 'Thiết bị điện & nước', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2123, 1, 'Gian hàng Cần cập nhật thông tin Cô Phú', 'Phạm Đắc Phú', '0969749180', 'Cần cập nhật thông tin', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2124, 1, 'Gian hàng Chăn ga gối đệm Cô Hường', 'Dương Thu Hường', '0904023416', 'Chăn ga gối đệm', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Vang, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2125, 1, 'Gian hàng Chăn ga gối đệm Cô Thêm', 'Nguyễn Thị Thêm', '0389941783', 'Chăn ga gối đệm', 0.00, 'cần cập nhật', 'Địa chỉ: Tổ 10 Thị trấn Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2126, 1, 'Gian hàng Cần cập nhật thông tin Chú Yến', 'Phạm Ngọc Yến', '0988443042', 'Cần cập nhật thông tin', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2127, 1, 'Gian hàng Quần áo Cô Xuyên', 'Nguyễn Thị Xuyên', '0973687780', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Phố Tó, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2128, 1, 'Gian hàng Quần áo Cô Quảng', 'Tô Xuân Quảng', '0904028728', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2129, 1, 'Gian hàng Quần áo Cô Hồng', 'Nguyễn Thị Thu Hồng', '0944381966', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2130, 1, 'Gian hàng Giầy dép Cô Dung', 'Đặng Thị Dung', '0366381588', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2131, 1, 'Gian hàng Vali & Túi xách Cô Thuỷ', 'Nguyễn Thị Thanh Thuỷ', 'Cần cập nhật thông tin', 'Vali & Túi xách', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2132, 1, 'Gian hàng Quần áo & Giầy dép Cô Hà', 'Ngô Thị Thu Hà', '0943308573', 'Quần áo & Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2133, 1, 'Gian hàng Giầy dép Cô Mạnh', 'Nguyễn Thị Mạnh', '0338552081', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2134, 1, 'Gian hàng Giầy dép Chú Hải', 'Nguyễn Đức Hải', '0987264291', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Phố Tó, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2135, 1, 'Gian hàng Đồ gia dụng Cô Nhụ', 'Hà Bích Nhụ', '0966213424', 'Đồ gia dụng', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2136, 1, 'Gian hàng Mỹ phẩm & Làm đẹp Cô Dung (Hộ 81)', 'Nguyễn Thị Kim Dung', '0869106908', 'Mỹ phẩm & Làm đẹp', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trongi Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2137, 1, 'Gian hàng Quần áo Cô Nga (Hộ 82)', 'Đinh Thị Nguyệt Nga', '0948412046', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2138, 1, 'Gian hàng Quần áo Cô Hà', 'Nguyễn Thị Thu Hà', '0975734457', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Chùa, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2139, 1, 'Gian hàng Quần áo Cô Hương (Hộ 84)', 'Nguyễn Thị Thu Hương', '0356783153', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Chùa, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2140, 1, 'Gian hàng Quần áo Cô Huệ', 'Đặng Thị Huệ', '0393073486', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Thôn Đìa, Nam Hồng, Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2141, 1, 'Gian hàng May rèm cửa Cô Vọng', 'Đặng Thị Vọng', 'Cần cập nhật thông tin', 'May rèm cửa', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2142, 1, 'Gian hàng Thực phẩm đông lạnh Cô Thiện', 'Nguyễn Thị Thiện', 'Cần cập nhật thông tin', 'Thực phẩm đông lạnh', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2143, 1, 'Gian hàng Quần áo Cô Mỹ', 'Hoàng Thị Mỹ', 'Cần cập nhật thông tin', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2144, 1, 'Gian hàng Hoa tươi Chú Tâm', 'Đặng Bá Tâm', '0978225513', 'Hoa tươi', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2145, 1, 'Gian hàng Chăn ga gối đệm Cô Anh', 'Nguyễn Thị Lan Anh', 'Cần cập nhật thông tin', 'Chăn ga gối đệm', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2146, 1, 'Gian hàng Đồ gia dụng Cô Quyền', 'Nguyễn Thị Quyền', '0976998219', 'Đồ gia dụng', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2147, 1, 'Gian hàng Túi xách & Giày dép Cô Huyền', 'Nguyễn Thị Huyền', '0359305824', 'Túi xách & Giày dép', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2148, 1, 'Gian hàng Quần áo Cô Kiểm', 'Cao Thị Kiểm', '0986546215', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Giao Tác, Liên Hà. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2149, 1, 'Gian hàng Quần áo Cô Yến (Hộ 94)', 'Vũ Thị Yến', '0978835792', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2150, 1, 'Gian hàng Quần áo Cô Bích (Hộ 95)', 'Nguyễn Thị Bích', '0904602000', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Tuân Lề, Tiên Dương. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2151, 1, 'Gian hàng Quần áo Cô Thu (Hộ 96)', 'Vương Thị Thu', '0975706358', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2152, 1, 'Gian hàng Quần áo Cô Nga (Hộ 97)', 'Hoàng Thị Nga', '0969625705', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Đài Bi, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2153, 1, 'Gian hàng Quần áo Cô Nhung', 'Vương Thị Nhung', 'Cần cập nhật thông tin', 'Quần áo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2154, 1, 'Gian hàng Quần áo Cô Tứ', 'Nguyễn Thị Tứ', '0338380461', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2155, 1, 'Gian hàng Quần áo Cô Loan', 'Cao Thị Mai Loan', '0355784642', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Dục Nội, Việt Hùng, Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2156, 1, 'Gian hàng Quần áo Cô Mến', 'Nguyễn Thị Mến', '0366222934', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2157, 1, 'Gian hàng Quần áo Cô Thắm', 'Đào Thị Hồng Thắm', '0978001098', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Gà, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2158, 1, 'Gian hàng Quần áo Cô Hương (Hộ 103)', 'Vương Thị Hương', '0376204511', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Tổ 1, Thị Trấn Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2159, 1, 'Gian hàng Cần cập nhật thông tin Cô Mơ', 'Nguyễn Thị Mơ', '0377797368', 'Cần cập nhật thông tin', 0.00, 'cần cập nhật', 'Địa chỉ: Phan Xá, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2160, 1, 'Gian hàng Cần cập nhật thông tin Cô Minh', 'Chu Thị Minh', '0338438256', 'Cần cập nhật thông tin', 0.00, 'cần cập nhật', 'Địa chỉ: Lỗ Khê, Liên Hà. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2161, 1, 'Gian hàng Giầy dép Cô Hiền', 'Nguyễn Thị Hiền', '0393069113', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2162, 1, 'Gian hàng Cần cập nhật thông tin Cô Thuỷ', 'Phạm Thị Thanh Thuỷ', '0984510582', 'Cần cập nhật thông tin', 0.00, 'cần cập nhật', 'Địa chỉ: Dục Nội, Việt Hùng, Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2163, 1, 'Gian hàng Thực phẩm khô Cô Đông', 'Cao Thị Đông', 'Cần cập nhật thông tin', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Địa chỉ: Dục Nội, Việt Hùng, Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2164, 1, 'Gian hàng Thực phẩm khô Cô Chăm', 'Nguyễn Thị Chăm', '0971167496', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Địa chỉ: Khu Đoài, Dục Nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2165, 1, 'Gian hàng Quần áo Cô An', 'Nguyễn Thị An', '0849862356', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2166, 1, 'Gian hàng Thực phẩm khô Cô Hương', 'Nguyễn Thị Hương', '0399574394', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Địa chỉ: Khu Trung, Dục Nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2167, 1, 'Gian hàng Quần áo Cô Nga (Hộ 112)', 'Đỗ Thị Thuý Nga', '0962572407', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2168, 1, 'Gian hàng Thực phẩm khô Cô Hà', 'Nguyễn Thị Thanh Hà', '0352458637', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Địa chỉ: Cầu Cả, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2169, 1, 'Gian hàng Thực phẩm khô Cô Phượng', 'Đặng Thị Phượng', '0375286097', 'Thực phẩm khô', 0.00, 'cần cập nhật', 'Địa chỉ: Khu Đoài, Dục Nội, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2170, 1, 'Gian hàng Quần áo Cô Hà (Hộ 115)', 'Đăng Thị Hằng Hà', '0373391992', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2171, 1, 'Gian hàng Quần áo Cô Dương', 'Nguyễn Thuỳ Dương', '0974382681', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Đài Bi, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2172, 1, 'Gian hàng Quần áo Cô Nhung (Hộ 117)', 'Hà Thị Nhung', '0969569022', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2173, 1, 'Gian hàng Quần áo Cô Nhàn', 'Đào Thị Nhàn', 'Cần cập nhật thông tin', 'Quần áo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2174, 1, 'Gian hàng Quần áo Cô Mây (Hộ 119)', 'Nguyễn Thị Mây', '0339461518', 'Quần áo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2175, 1, 'Gian hàng Quần áo Cô Hạnh (Hộ 120)', 'Nguyễn Thị Hạnh', '0332239386', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2176, 1, 'Gian hàng Quần áo Cô Tuyết (Hộ 121)', 'Nguyễn Thị Tuyết', '0912213090', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2177, 1, 'Gian hàng Quần áo Cô Hường (Hộ 122)', 'Nguyễn Thị Hường', '0986526790', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2178, 1, 'Gian hàng Quần áo Cô Hoàn', 'Nguyễn Thị Hoàn', '0983573301', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2179, 1, 'Gian hàng Đồ sành sứ Cô Bích', 'Nguyễn Thị Ngọc Bích', '0987479019', 'Đồ sành sứ', 0.00, 'cần cập nhật', 'Địa chỉ: Đài Bi, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2180, 1, 'Gian hàng Quần áo Cô Hải', 'Hoàng Thị Thanh Hải', '0977528581', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2181, 1, 'Gian hàng Giầy dép Cô Tiện', 'Hoàng Thị Tiện', '0337953208', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2182, 1, 'Gian hàng Giầy dép Cô Hương', 'Hoàng Thị Hương', '0358719003', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Đài Bi, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2183, 1, 'Gian hàng Giầy dép Cô Hoài', 'Nguyễn Thị Thu Hoài', '0943679223', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Phúc Lộc , Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2184, 1, 'Gian hàng Giầy dép Cô Hà', 'Nguyễn Thị Thanh Hà', '0983763745', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Tổ 13 Thị trấn Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2185, 1, 'Gian hàng Giầy dép Cô Sáu', 'Nguyễn Thị Sáu', '0983891814', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Lực Canh, Xuân Canh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2186, 1, 'Gian hàng Giầy dép Chú Vui', 'Đặng Bá Vui', '0987325310', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2187, 1, 'Gian hàng Quần áo Cô Loan (Hộ 132)', 'Vương Thị Loan', '0766115683', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2188, 1, 'Gian hàng Quần áo Cô Hoa', 'Nguyễn Thị Hoa', '0989424128', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2189, 1, 'Gian hàng Quần áo Cô Ngọc (Hộ 134)', 'Nguyễn Thị Bích Ngọc', '0373569412', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Hậu, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2190, 1, 'Gian hàng Quần áo Cô Hương (Hộ 135)', 'Nguyễn Thị Lan Hương', '0914880166', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Phan Xá, Uy nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2191, 1, 'Gian hàng Quần áo Cô Hà (Hộ 136)', 'Nguyễn Thị Hà', '0386317299', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2192, 1, 'Gian hàng Quần áo Chú Đương', 'Đồng Đạo Đương', '0976842867', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Cầu Cả, cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2193, 1, 'Gian hàng Quần áo Cô Nhinh', 'Nguyễn Thị Nhinh', '0961235568', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: XómTrong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2194, 1, 'Gian hàng Quần áo Chú Hiếu', 'Nguyễn Văn Hiếu', '0983573530', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Đài Bi, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2195, 1, 'Gian hàng Chè & Ốc Cô Giang', 'Hoàng Thị Giang', '0968015437', 'Chè & Ốc', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2196, 1, 'Gian hàng Mũ & Nón Cô Gấm', 'Hoàng Thị Gấm', '0987942827', 'Mũ & Nón', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2197, 1, 'Gian hàng Mỹ phẩm & Làm đẹp Cô Nga', 'Vương Thị Nga', '0984088357', 'Mỹ phẩm & Làm đẹp', 0.00, 'cần cập nhật', 'Địa chỉ: Đản Mỗ, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2198, 1, 'Gian hàng Quần áo Cô Lượng', 'Nguyễn Thị Lượng', '0343041477', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2199, 1, 'Gian hàng Giầy dép Cô Chí', 'Ngô Thị Chí', '0944387011', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Lực Canh, Xuân Canh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2200, 1, 'Gian hàng Quần áo Cô Trang', 'Đặng Thị Đoan Trang', '0966061925', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2201, 1, 'Gian hàng Giầy dép Cô Hà (Hộ 146)', 'Nguyễn Thị Thu Hà', '0984032159', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Nhì, Vân Nội. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2202, 1, 'Gian hàng Quần áo & Giầy dép Cô Nhung', 'Nguyễn Kim Nhung', '0977765472', 'Quần áo & Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Phố Tó, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2203, 1, 'Gian hàng Quần áo Cô Ngà', 'Nguyễn Thị Ngà', '0395227031', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Mạch Tràng, Cổ Loa. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2204, 1, 'Gian hàng Quần áo Cô Hiền (Hộ 149)', 'Nguyễn Thị Hiền', 'Cần cập nhật thông tin', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2205, 1, 'Gian hàng Quần áo Cô Liên', 'Nguyễn Thị Huệ Nguyễn Thị Liên', '0974587468', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Ngoài, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2206, 1, 'Gian hàng Quần áo Cô Phượng (Hộ 152)', 'Trần Thị Phượng', '0912453159', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Đản Dị, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2207, 1, 'Gian hàng Quần áo Cô Lưu', 'Trần Thị Lưu', '0394404547', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Phú Khu, Văn Lang, Hưng Hà, Thái Bình. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2208, 1, 'Gian hàng Quần áo Cô Tuyết (Hộ 154)', 'Vũ Thị Tuyết', '0915664588', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Tổ 38 Thị Trấn Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2209, 1, 'Gian hàng Quần áo Cô Phượng (Hộ 155)', 'Hưũ Thị Phượng', '0372281467', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Khu Trung, Việt Hùng. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2210, 1, 'Gian hàng Quần áo Chú Hà', 'Nguyễn Việt Hà', '0979327671', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Cổ Dương, Tiên Dương. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2211, 1, 'Gian hàng Mỹ phẩm & Làm đẹp Cô Ngọc', 'Nguyễn Thị Ngọc', '0334793850', 'Mỹ phẩm & Làm đẹp', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm trong, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2212, 1, 'Gian hàng Quần áo Chú Hòa', 'Nguyễn Việt Hòa', '0986228463', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Tổ 39 Thị Trấn Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2213, 1, 'Gian hàng Giầy dép Cô Vượng', 'Trịnh Thị Vượng', '0978860278', 'Giầy dép', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Thượng, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2214, 1, 'Gian hàng Mũ & Nón Chú Lương', 'Nguyễn Ngọc Lương', '0966537177', 'Mũ & Nón', 0.00, 'cần cập nhật', 'Địa chỉ: Tổ 36 Thị Trấn Đông Anh. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2215, 1, 'Gian hàng Mũ & Nón Chú Ngọc', 'Nguyễn Minh Ngọc', '0906435499', 'Mũ & Nón', 0.00, 'cần cập nhật', 'Địa chỉ: Xóm Trong,Uy Nỗ- ĐA. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2216, 1, 'Gian hàng Quần áo Cô Vóc', 'Hoàng Thị Vóc( Nhung)', '0366555696', 'Quần áo', 0.00, 'cần cập nhật', 'Địa chỉ: Phan Xá, Uy Nỗ. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2217, 1, 'Gian hàng Phụ kiện thời trang Cô Anh (Hộ 163)', 'Hoàng Thị Tú Anh', '0977973410', 'Phụ kiện thời trang', 0.00, 'cần cập nhật', 'Địa chỉ: Đình Trung, Xuân Nộn. Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2218, 1, 'Gian hàng Tạp hóa & Bánh kẹo Chú Tuyến', 'Nguyễn Văn Tuyến', 'Cần cập nhật thông tin', 'Tạp hóa & Bánh kẹo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2219, 1, 'Gian hàng Tạp hóa & Bánh kẹo Cô Thân', 'Lê Thị Thân', 'Cần cập nhật thông tin', 'Tạp hóa & Bánh kẹo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2220, 1, 'Gian hàng Tạp hóa & Bánh kẹo Cô Hoà', 'Đỗ Thị Hoà', 'Cần cập nhật thông tin', 'Tạp hóa & Bánh kẹo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2221, 1, 'Gian hàng Tạp hóa & Bánh kẹo Cô Minh', 'Nguyễn Thị Hồng Minh', 'Cần cập nhật thông tin', 'Tạp hóa & Bánh kẹo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2222, 1, 'Gian hàng Tạp hóa & Bánh kẹo Cô Hà', 'Nguyễn Thị Hà', 'Cần cập nhật thông tin', 'Tạp hóa & Bánh kẹo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2223, 1, 'Gian hàng Tạp hóa & Bánh kẹo Cô Thuý', 'Hoàng Thị Thuý', 'Cần cập nhật thông tin', 'Tạp hóa & Bánh kẹo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2224, 1, 'Gian hàng Tạp hóa & Bánh kẹo Cô Oanh', 'Trần Thị Kim Oanh', 'Cần cập nhật thông tin', 'Tạp hóa & Bánh kẹo', 0.00, 'cần cập nhật', 'Nguồn gốc: Cần cập nhật thông tin.', NULL, NULL, '2026-07-24 03:57:57', '2026-07-24 03:57:57', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `category_slug` varchar(255) NOT NULL,
  `stall_name` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_phone` varchar(255) NOT NULL,
  `shipping_address` text NOT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(255) NOT NULL DEFAULT 'COD',
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `is_reviewed` tinyint(1) NOT NULL DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `dish_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ocop_product_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(12,2) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_otps`
--

CREATE TABLE `password_otps` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(255) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `method` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
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
  `stall_name` varchar(255) DEFAULT NULL,
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

INSERT INTO `reviews` (`id`, `eatery_id`, `stall_name`, `user_name`, `rating`, `comment`, `seller_reply`, `created_at`, `updated_at`) VALUES
(1, 16, 'Gian hàng Ăn uống Cô Sinh', 'Thành viên Đông Anh', 5, 'sạch sẽ, an toàn', NULL, '2026-07-23 09:18:54', '2026-07-23 09:18:54'),
(2, 16, 'Gian hàng Rau củ Cô Vui', 'Thành viên Đông Anh', 5, 'quá tuyêth', NULL, '2026-07-23 11:06:14', '2026-07-23 11:06:14'),
(3, 16, 'Gian hàng Ăn sáng Cô Hà', 'Khách vãng lai', 5, 'jkkkkk', NULL, '2026-07-24 01:18:11', '2026-07-24 01:18:11'),
(4, 16, 'Gian hàng Ăn sáng Cô Hà', 'Khách vãng lai', 5, 'tuyệt vời', NULL, '2026-07-24 01:21:40', '2026-07-24 01:21:40');

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
(1, 4, '/storage/reviews/C1IQJslKACNwqqYyow65XoQHN7LSzYVSjSttO6pK.png', 'image', '2026-07-24 01:21:42', '2026-07-24 01:21:42'),
(2, 4, '/storage/reviews/XRfW8JCZZvWnpQbv9xONB7LwKBILqe4IbVoCFMUU.png', 'image', '2026-07-24 01:21:42', '2026-07-24 01:21:42'),
(3, 4, '/storage/reviews/hwXR5B8SXGSKjEHNCmypNaMqI03xT4o4r7JzpXHd.png', 'image', '2026-07-24 01:21:42', '2026-07-24 01:21:42'),
(4, 4, '/storage/reviews/a2Zv0x4o2Jv2bvUXEiBZfvlURDJmA9mY7LCFW8A0.png', 'image', '2026-07-24 01:21:42', '2026-07-24 01:21:42');

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

--
-- Dumping data for table `review_videos`
--

INSERT INTO `review_videos` (`id`, `eatery_id`, `user_id`, `title`, `video_url`, `video_type`, `thumbnail_path`, `likes_count`, `status`, `created_at`, `updated_at`) VALUES
(1, 16, 2, 'chợ mạch tràng', 'https://youtu.be/3WL6DQ9Yhp4?si=jUL2-nPIaOqJ2ml4', 'youtube_shorts', 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=400&q=80', 0, 'approved', '2026-07-24 08:49:47', '2026-07-24 08:51:44');

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
(1, 'Nguyễn Văn Admin', 'admin@foodmap.vn', NULL, '$2y$12$vONlT9t5npaVaNQtmW1hmOR//o7N0arUZEFVmBhDGBrBvVur9s1Wi', 'admin', '👨‍💼', '0901234567', 'active', NULL, NULL, NULL, NULL, '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(2, 'Ban Quản lý Chợ Mạch Tràng', 'seller@foodmap.vn', NULL, '$2y$12$N2v2YkVl2czS6Ku4jc8q.OQ9kA6gTxETIVf.sl1IfOudXETLmZr86', 'manager', '👨‍🍳', '0912345678', 'active', NULL, NULL, NULL, NULL, '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(3, 'Thực Thần Đông Anh', 'user@foodmap.vn', NULL, '$2y$12$.MpwCkj6A4IiqvRlKLvPJuyKAqge7Cgoy6NOgsTrNEnA83RtDhay6', 'user', '🧑', '0987654321', 'active', NULL, NULL, NULL, NULL, '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(4, 'Thành viên Đông Anh', 'member@foodmap.vn', NULL, '$2y$12$y7hhX7LchfAD1Kx/CH9ud.Wv4DjLSJ6Z/LhTWBNms4q.LsaptpM8K', 'user', '👧', '0977665544', 'active', NULL, NULL, NULL, NULL, '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(7, 'Ban Quản lý Chợ Tố', 'bql.choto@foodmap.vn', NULL, '$2y$12$0IeykWbmfz5QFPml5Yfj.e5MvFwJz4FXFRiiQsFqFHq/BxT6gRI8C', 'manager', NULL, NULL, 'active', NULL, NULL, NULL, NULL, '2026-07-24 08:35:18', '2026-07-24 08:35:18'),
(8, 'Ban Quản lý Chợ Trung Tâm Đông Anh', 'bql.chotrungtam@foodmap.vn', NULL, '$2y$12$bX8Sco9284vQJTSDnvd2euoDgvH3EHwZQCboKe6nag92sphQlj8me', 'manager', NULL, NULL, 'active', NULL, NULL, NULL, NULL, '2026-07-24 08:35:18', '2026-07-24 08:35:18'),
(9, 'Ban Quản lý Chợ Sa Cổ Loa', 'bql.chosa@foodmap.vn', NULL, '$2y$12$O8VmPog9KQ4Xe/obKd5pyOUE7NcworkbIhLlWI9kfh8tOefDO3QM2', 'manager', NULL, NULL, 'active', NULL, NULL, NULL, NULL, '2026-07-24 08:35:19', '2026-07-24 08:35:19'),
(10, 'Ban Quản lý Chợ Mai Lâm', 'bql.chomailam@foodmap.vn', NULL, '$2y$12$Ux4LZCZS7jc3/Z9fE.A8tubZahvwQHJq59rqcle/Sc34UZLPd0TbC', 'manager', NULL, NULL, 'active', NULL, NULL, NULL, NULL, '2026-07-24 08:35:19', '2026-07-24 08:35:19'),
(11, 'Nguyễn Thị Sinh', 'cosinh@foodmap.vn', NULL, '$2y$12$07qqscUqykOc4hQWUCCOk.gqi1H7CcgosrevyZEt6jPssIr9sjR0.', 'seller', NULL, '0987654321', 'active', NULL, NULL, NULL, NULL, '2026-07-24 08:44:53', '2026-07-24 08:44:53'),
(12, 'Đào Thị Sức', 'cosuc@foodmap.vn', NULL, '$2y$12$CpidK.GmUDHQnwQg0WSsneU4reb19Ga4mzAQPXP0.sjaU5gMr0D2u', 'seller', NULL, '0987111222', 'active', NULL, NULL, NULL, NULL, '2026-07-24 08:44:53', '2026-07-24 08:44:53'),
(13, 'Trần Thị Thuyên', 'cothuyen@foodmap.vn', NULL, '$2y$12$Ndm1W/NjbcgRyTvCXMslpeh46vIImORX4CIdTIauzJ2OYmDyOtZda', 'seller', NULL, '0987333444', 'active', NULL, NULL, NULL, NULL, '2026-07-24 08:44:53', '2026-07-24 08:44:53'),
(14, 'Đào Thị Mai', 'comai@foodmap.vn', NULL, '$2y$12$k3shq8Fd3SCkmlIUGFMcjOTGLyUGH.MX.8przeiSFa7ItQNSV4jk6', 'seller', NULL, '0987555666', 'active', NULL, NULL, NULL, NULL, '2026-07-24 08:44:53', '2026-07-24 08:44:53');

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
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carts_user_id_index` (`user_id`),
  ADD KEY `carts_session_id_index` (`session_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_items_cart_id_foreign` (`cart_id`),
  ADD KEY `cart_items_dish_id_index` (`dish_id`),
  ADD KEY `cart_items_ocop_product_id_index` (`ocop_product_id`);

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
-- Indexes for table `market_messages`
--
ALTER TABLE `market_messages`
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
-- Indexes for table `ocop_products`
--
ALTER TABLE `ocop_products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ocop_products_eatery_id_foreign` (`eatery_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `orders_user_id_index` (`user_id`),
  ADD KEY `orders_eatery_id_index` (`eatery_id`),
  ADD KEY `orders_status_index` (`status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_items_order_id_foreign` (`order_id`);

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
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payments_order_id_foreign` (`order_id`);

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
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `checkins`
--
ALTER TABLE `checkins`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_tour_diaries`
--
ALTER TABLE `food_tour_diaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_tour_stops`
--
ALTER TABLE `food_tour_stops`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `market_messages`
--
ALTER TABLE `market_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `ocop_products`
--
ALTER TABLE `ocop_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2225;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_otps`
--
ALTER TABLE `password_otps`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_invoices`
--
ALTER TABLE `purchase_invoices`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `review_media`
--
ALTER TABLE `review_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `review_videos`
--
ALTER TABLE `review_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `ocop_products`
--
ALTER TABLE `ocop_products`
  ADD CONSTRAINT `ocop_products_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

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
