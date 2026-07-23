-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 23, 2026 at 10:03 AM
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
(8, 'Chợ truyền thống', 'traditional-market', '🏪', 'Khám phá các chợ truyền thống nhộn nhịp mang đậm hồn quê Đông Anh.', '2026-06-01 03:58:03', '2026-06-01 03:58:03');

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
(1, NULL, 'Chợ Tó', 'cho-to-pa3MD', 8, 61, NULL, '4VP4+V46, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.138354, 105.85725, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780287931_mywq2Yvo.png', 0, 5.00, 'active', '2026-06-01 04:24:43', '2026-06-01 07:44:12'),
(2, NULL, 'Chợ Trung Tâm Đông Anh', 'cho-trung-tam-dong-anh-P2ft0', 8, 57, NULL, '4VP4+V46, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.137701, 105.845718, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780288137_UXjj2A68.png', 0, 5.00, 'active', '2026-06-01 04:28:59', '2026-06-05 08:25:35'),
(3, NULL, 'Chợ Sa (Cổ Loa)', 'cho-sa-co-loa-mf0hh', 8, 72, 'Chợ Sa trước thuộc thôn Chợ, nhưng nay đã tách ra lập thành một đơn vị hành chính độc lập trực thuộc xã Cổ Loa. Tuy là một đơn vị hành chính mới được hình thành song cái tên chợ Sa đã có từ lâu đời và vốn rất quen thuộc với cư dân  nơi đây.\r\n\r\nTên chợ Sa được dân gian giải thích vì đây là nơi đặt sa bàn kinh thành của vua An Dương Vương. Chợ Sa nằm trên bãi Sa của sông Thiếp (Hoàng Giang) bên tả ngạn, phía nam, bên ngoài thành ngoại Cổ Loa. Chợ họp ngay trên khu đất cao mỗi tháng 6 phiên vào các ngày 1, 6, 11, 16, 21 and 26 âm lịch. Đây tương truyền là điểm buôn bán của đô thị Cổ Loa ngày trước.\r\n\r\n Câu ca dao về các phiên chợ liền kề trong vùng:\r\n\r\n“Chợ Dâu là câu chợ Tó\r\n\r\nChợ Tó bó chợ Dọc\r\n\r\nChợ Dọc cọc chợ Sa\r\n\r\nChợ Sa là xà chợ Cói\r\n\r\nChợ Cói là bói chợ Dâu”.\r\n\r\nNhư vậy, có thể nói rằng vùng đất Cổ Loa xưa từ khi được chọn làm quốc đô, đến nay đã trở thành tụ điểm dân cư tập trung đông đúc. Các hoạt động kinh tế được đẩy mạnh hơn, một số nghề thủ công, đặc biệt là nghề đúc đồng vươn lên đỉnh cao của nghề đúc thời cổ. Cổ Loa xưa nổi bật lên là đô thị quan trọng thời cổ đại tuy yếu tố “thị”  chưa rõ ràng song được thể hiện ở một hệ thống chợ tiêu biểu là Chợ Sa. Dần dần yếu tố thị ngày càng phát triển hình thành nên cả một khu phố chợ Sa như ngày nay.', '4V5H+V3 Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.109657, 105.877639, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780300693_Vg4Wau60.jpeg', 0, 5.00, 'active', '2026-06-01 07:58:15', '2026-06-01 07:59:52'),
(4, NULL, 'Chợ Mai Lâm', 'cho-mai-lam-Fg3D9', 8, 15, NULL, '3WQ2+H6J, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.088971, 105.900505, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780301331_M3Ijvbtr.jpg', 0, 5.00, 'active', '2026-06-01 08:08:51', '2026-06-01 08:08:51'),
(5, NULL, 'Chợ Dục Nội', 'cho-duc-noi-pIEle', 8, 7, NULL, '4VQG+G6 Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.138754, 105.875604, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780301489_jZdGGxdd.png', 0, 5.00, 'active', '2026-06-01 08:11:31', '2026-06-01 08:11:31'),
(6, NULL, 'Chợ Dục Tú	3', 'cho-duc-tu-3-TD0C7', 8, 32, NULL, '4V8W+V69, Unnamed Road, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.117158, 105.895619, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780301601_joTT7VRd.png', 0, 5.00, 'active', '2026-06-01 08:13:24', '2026-06-01 08:13:24'),
(7, NULL, 'Chợ văn hoá Du lịch Cổ Loa', 'cho-van-hoa-du-lich-co-loa-3Tkmb', 8, 49, NULL, '4V6C+VH, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.112188, 105.871437, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780301782_REOzXNar.png', 0, 5.00, 'active', '2026-06-01 08:16:24', '2026-06-01 08:16:24'),
(8, NULL, 'Chợ Du Nội', 'cho-du-noi-kItF3', 8, 15, NULL, '3VRV+7X9, Du Nội, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.090664, 105.89497, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780302003_72HMN6r3.png', 0, 5.00, 'active', '2026-06-01 08:20:06', '2026-06-01 08:20:06'),
(9, NULL, 'Chợ Mai Hiên', 'cho-mai-hien-etlwa', 8, 17, NULL, '3VPQ+JCW, Mai Hiên, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.086616, 105.888751, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780302105_7UHJjEYA.png', 0, 5.00, 'active', '2026-06-01 08:21:47', '2026-06-01 08:21:47'),
(10, NULL, 'Chợ Lực Canh', 'cho-luc-canh-bU73w', 8, 3, NULL, 'Lực Canh , Đông Anh, Hà Nội', NULL, NULL, 21.093673, 105.850313, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780302505_yK6ILuDh.png', 0, 5.00, 'active', '2026-06-01 08:28:27', '2026-06-01 08:28:27'),
(11, NULL, 'Chợ Xuân Canh', 'cho-xuan-canh-d8ZrN', 8, 4, NULL, '3VW2+FC9, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.096157, 105.851096, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780302611_ShFQdlxK.png', 0, 5.00, 'active', '2026-06-01 08:30:13', '2026-06-01 08:30:13'),
(12, NULL, 'Chợ Nhồi Dưới', 'cho-nhoi-duoi-rQBih', 8, 21, NULL, 'Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.117566, 105.870562, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780302727_alzO2Ff2.png', 0, 5.00, 'active', '2026-06-01 08:32:09', '2026-06-01 08:32:09'),
(13, NULL, 'Chợ Lý Nhân', 'cho-ly-nhan-tgo5z', 8, 9, NULL, 'Lý Nhân, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.106588, 105.886567, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780303997_yrbu9E5Z.png', 0, 5.00, 'active', '2026-06-01 08:53:18', '2026-06-01 08:53:18'),
(14, NULL, 'Chợ Dày Da', 'cho-day-da-tifcw', 8, 54, NULL, '4VR8+FF3, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.14115, 105.866177, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780304125_eR6yLod0.png', 0, 5.00, 'active', '2026-06-01 08:55:26', '2026-06-01 08:55:26'),
(15, NULL, 'Chợ Đông Trù', 'cho-dong-tru-YT5K4', 8, 1, NULL, '3VGG+RJ4, Đông Trù, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.077005, 105.876592, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780304221_EyQwimeV.png', 0, 5.00, 'active', '2026-06-01 08:57:02', '2026-06-01 08:57:02'),
(16, NULL, 'Chợ Mạch Tràng', 'cho-mach-trang-oefHf', 8, 6, NULL, '4V48+XPM, Cổ Loa, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.107475, 105.866782, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780304292_4x03RlDT.png', 0, 5.00, 'active', '2026-06-01 08:58:13', '2026-06-01 08:58:13'),
(17, NULL, 'Siêu Thị Lan Chi Đông Anh', 'sieu-thi-lan-chi-dong-anh-t0bez', 8, 64, NULL, 'KhốI 2A QL3, Đông Anh, Hà Nội, Việt Nam', '0916603888', NULL, 21.147096, 105.846628, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780304373_s0wSKT2J.png', 0, 5.00, 'active', '2026-06-01 08:59:34', '2026-06-01 08:59:34'),
(19, NULL, 'HTX nông nghiệp dược liệu công nghệ cao KOVI', 'htx-nong-nghiep-duoc-lieu-cong-nghe-cao-kovi-QBWB4', 4, 8, 'HTX nông nghiệp dược liệu công nghệ cao KOVI; địa chỉ Thôn Lộc Hà, xã Đông Anh; tên sản phẩm OCOP: Đông trùng hạ thảo tươi, Đông trùng hạ thảo khô, Đông trùng hạ thảo ký chủ nhộng tằm theo QĐ số 2008/QĐ/UBND ngày 07/04/2023.', '3VVM+38H, Lộc Hà, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.092694, 105.883345, '30.000 - 100.000', 'https://media.xadonganh.com/eateries/1781776354_GHX1lRWO.png', 1, 5.00, 'active', '2026-06-18 09:52:36', '2026-06-18 09:52:36'),
(20, NULL, 'HKD Trần Văn Tân', 'hkd-tran-van-tan-v2rPM', 4, 33, 'HKD Trần Văn Tân; địa chỉ Thôn Thạc Quả, xã Đông Anh; tên sản phẩm OCOP: Tượng phật Đại Thế Chí Bồ Tát, Song ngưu sinh tài theo QĐ số 2008/QĐ/UBND ngày 07/04/2023.', 'Thôn Thạc Quả, xã Đông Anh', NULL, NULL, 21.124558, 105.906103, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 03:48:39', '2026-06-19 03:48:59'),
(21, NULL, 'Công ty TNHH Hoàng Chiến Thắng', 'cong-ty-tnhh-hoang-chien-thang-IV8Bf', 4, 11, 'Công ty TNHH Hoàng Chiến Thắng; địa chỉ Thôn Đông Ngàn, xã Đông Anh; tên sản phẩm OCOP: Bánh gạo lứt, Bánh vừng vòng theo QĐ số 2008/QĐ/UBND ngày 07/04/2023', 'Số 29,xóm mít, Đông Ngàn, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.069422, 105.867833, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 03:52:08', '2026-06-19 03:52:08'),
(22, NULL, 'HTX dịch vụ nông nghiệp thôn Đoài', 'htx-dich-vu-nong-nghiep-thon-doai-jDNEK', 4, 2, 'HTX dịch vụ nông nghiệp thôn Đoài; địa chỉ Thôn Đoài, xã Đông Anh; tên sản phẩm OCOP: Tương Việt Hùng theo QĐ số 2008/QĐ/UBND ngày 07/04/2023', '4VQC+VR Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.139694, 105.872072, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 03:56:13', '2026-06-19 03:56:13'),
(23, NULL, 'HKD sản xuất và kinh doanh bánh ngọt Thuý Quyên', 'hkd-san-xuat-va-kinh-doanh-banh-ngot-thuy-quyen-hnGLZ', 4, 11, 'HKD sản xuất và kinh doanh bánh ngọt Thuý Quyên; địa chỉ Thôn Đông Ngàn, xã Đông Anh; tên sản phẩm OCOP: Bánh xốp vừng, Bánh sampa, Bánh trứng nhện theo QĐ số 560/QĐ/UBND ngày 17/01/2021.', 'Đông Ngàn ,Đông Anh ,Hà Nội', NULL, NULL, 21.070403, 105.866461, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 03:58:23', '2026-06-19 03:58:39'),
(24, NULL, 'HKD Thạo Loan', 'hkd-thao-loan-8PeUP', 4, 19, '. HKD Thạo Loan; địa chỉ Thôn Xuân Canh, xã Đông Anh; tên sản phẩm OCOP: Rượu gạo nếp Long Tửu, Rượu dâu Long tửu theo QĐ số 560/QĐ/UBND ngày 17/01/2021.', 'Thôn Xuân Canh, xã Đông Anh', NULL, NULL, 21.081878, 105.847897, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 04:01:36', '2026-06-19 04:01:36'),
(25, NULL, 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa', 'htx-dich-vu-nong-nghiep-kinh-doanh-tong-hop-co-loa-sXHK5', 4, 31, 'HTX dịch vụ nông nghiệp kinh doanh tổng hợp Cổ Loa; địa chỉ: Trung tâm Cổ Loa, xã Đông Anh; tên sản phẩm OCOP: hành lá, khoai tây theo QĐ số 560/QĐ/UBND ngày 17/01/2021', 'Trung tâm Cổ Loa, xã Đông Anh', NULL, NULL, 21.111982, 105.875818, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 04:06:49', '2026-06-19 04:06:49'),
(26, NULL, 'Cơ sở sản xuất thực phẩm Liêm Hiệp', 'o-so-san-xuat-thuc-pham-liem-hiep-O8FRl', 4, 20, 'Cơ sở sản xuất thực phẩm Liêm Hiệp; địa chỉ: thôn Thượng, xã Đông Anh; tên sản phẩm OCOP: Giò lụa, Chả lụa', 'thôn Thượng, xã Đông Anh', NULL, NULL, 21.123023, 105.876738, '30.000 - 100.000', NULL, 1, 5.00, 'active', '2026-06-19 04:17:55', '2026-06-19 04:19:22');

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
(25, '2026_06_22_100528_add_indexes_to_tables', 5);

-- --------------------------------------------------------

--
-- Table structure for table `ocop_products`
--

CREATE TABLE `ocop_products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `eatery_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(12,2) DEFAULT NULL,
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

INSERT INTO `ocop_products` (`id`, `eatery_id`, `name`, `price`, `description`, `image_path`, `star_rating`, `created_at`, `updated_at`, `heritage_year`, `story`, `artisans`, `fun_fact`, `audio_narrative`, `ingredients`, `timeline`) VALUES
(2, 19, 'Đông trùng hạ thảo tươi', NULL, NULL, NULL, NULL, '2026-06-18 09:53:35', '2026-06-18 10:23:58', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 19, 'Đông trùng hạ thảo khô', NULL, NULL, NULL, NULL, '2026-06-18 09:54:16', '2026-06-18 10:23:53', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(4, 19, 'Đông trùng hạ thảo ký chủ nhộng tằm', NULL, NULL, NULL, NULL, '2026-06-18 10:23:48', '2026-06-18 10:23:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(5, 20, 'Tượng phật Đại Thế Chí Bồ Tát', NULL, NULL, NULL, '4 sao', '2026-06-19 03:49:26', '2026-07-21 04:21:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(6, 20, 'Song ngưu sinh tài', NULL, NULL, NULL, '4 sao', '2026-06-19 03:49:40', '2026-07-21 04:21:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(7, 21, 'Bánh gạo lứt', NULL, NULL, NULL, '4 sao', '2026-06-19 03:52:32', '2026-07-21 04:19:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(8, 21, 'Bánh vừng vòng', NULL, NULL, NULL, '3 sao', '2026-06-19 03:52:43', '2026-07-21 04:19:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(9, 22, 'Tương Việt Hùng', NULL, NULL, 'https://media.xadonganh.com/ocop/1784607252_WLu4ciXg.png', '3 sao', '2026-06-19 03:56:45', '2026-07-21 04:14:13', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(10, 23, 'Bánh xốp vừng', NULL, NULL, NULL, '3 sao', '2026-06-19 03:58:55', '2026-07-21 04:18:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(11, 23, 'Bánh sampa', NULL, NULL, NULL, '3 sao', '2026-06-19 03:59:04', '2026-07-21 04:18:36', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(12, 23, 'Bánh trứng nhện', NULL, NULL, NULL, '3 sao', '2026-06-19 03:59:15', '2026-07-21 04:18:40', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(13, 24, 'Rượu gạo nếp Long Tửu', NULL, NULL, NULL, '3 sao', '2026-06-19 04:01:53', '2026-07-21 04:17:27', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(14, 24, 'Rượu dâu Long tửu', NULL, NULL, NULL, '3 sao', '2026-06-19 04:02:04', '2026-07-21 04:17:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(15, 25, 'hành lá', NULL, NULL, NULL, '3 sao', '2026-06-19 04:07:33', '2026-07-21 04:16:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 25, 'khoai tây', NULL, NULL, NULL, '3 sao', '2026-06-19 04:07:45', '2026-07-21 04:16:33', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(17, 21, 'Bánh Vòng Dừa', NULL, NULL, NULL, '3 sao', '2026-06-19 04:13:08', '2026-07-21 04:20:48', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(18, 24, 'Rượu mơ Long Tửu', NULL, NULL, NULL, '3 sao', '2026-06-19 04:13:32', '2026-07-21 04:17:34', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 24, 'Rượu Bạch cúc Long Tửu', NULL, NULL, NULL, '3 sao', '2026-06-19 04:13:44', '2026-07-21 04:17:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(20, 21, 'Bánh vừng Cookies', NULL, NULL, NULL, '3 sao', '2026-06-19 04:14:51', '2026-07-21 04:20:15', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(21, 21, 'Bánh gạo thơm', NULL, NULL, NULL, '3 sao', '2026-06-19 04:15:01', '2026-07-21 04:20:56', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(22, 25, 'Bí đỏ', NULL, NULL, NULL, '3 sao', '2026-06-19 04:15:48', '2026-07-21 04:16:43', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(23, 25, 'Lạc nhân', NULL, NULL, NULL, '3 sao', '2026-06-19 04:15:58', '2026-07-21 04:16:39', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(24, 26, 'Giò lụa', NULL, NULL, NULL, NULL, '2026-06-19 04:18:17', '2026-06-19 04:18:17', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(25, 26, 'Chả lụa', 50000.00, NULL, NULL, NULL, '2026-06-19 04:18:27', '2026-07-21 03:58:32', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(26, 20, 'Kim ngưu sinh tài', NULL, NULL, NULL, '4 sao', '2026-07-21 04:34:28', '2026-07-21 04:34:28', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(27, 20, 'Cá chép hoá rồng', NULL, NULL, NULL, '4 sao', '2026-07-21 04:34:38', '2026-07-21 04:34:38', NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `role`, `avatar`, `phone`, `status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Nguyễn Văn Admin', 'admin@foodmap.vn', NULL, '$2y$12$vONlT9t5npaVaNQtmW1hmOR//o7N0arUZEFVmBhDGBrBvVur9s1Wi', 'admin', '👨‍💼', '0901234567', 'active', NULL, '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(2, 'Trần Thị Bích', 'seller@foodmap.vn', NULL, '$2y$12$ocp4aq.Rb4pBeInAiNPEHuF8inYREg7.tAk5poQifxHFABlME2cVO', 'seller', '👨‍🍳', '0912345678', 'active', NULL, '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(3, 'Thực Thần Đông Anh', 'user@foodmap.vn', NULL, '$2y$12$.MpwCkj6A4IiqvRlKLvPJuyKAqge7Cgoy6NOgsTrNEnA83RtDhay6', 'user', '🧑', '0987654321', 'active', NULL, '2026-06-01 03:58:03', '2026-06-01 03:58:03'),
(4, 'Thành viên Đông Anh', 'member@foodmap.vn', NULL, '$2y$12$y7hhX7LchfAD1Kx/CH9ud.Wv4DjLSJ6Z/LhTWBNms4q.LsaptpM8K', 'user', '👧', '0977665544', 'active', NULL, '2026-06-01 03:58:03', '2026-06-01 03:58:03');

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
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `ocop_products`
--
ALTER TABLE `ocop_products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `password_otps`
--
ALTER TABLE `password_otps`
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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_media`
--
ALTER TABLE `review_media`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `review_videos`
--
ALTER TABLE `review_videos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
-- Constraints for table `ocop_products`
--
ALTER TABLE `ocop_products`
  ADD CONSTRAINT `ocop_products_eatery_id_foreign` FOREIGN KEY (`eatery_id`) REFERENCES `eateries` (`id`) ON DELETE CASCADE;

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
