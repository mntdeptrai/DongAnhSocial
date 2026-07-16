-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 16, 2026 at 12:16 PM
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
-- Database: `dong_anh_culture_hub`
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
(1, 'ĐÔNG ANH FOOD MAP', 'dong-anh-food-map', '🍲', 'Bản đồ ẩm thực Đông Anh - Bún phở, lẩu nướng, quán cafe,...', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(2, 'Stay in Đông Anh', 'stay-in-dong-anh', '🏨', 'Nhà nghỉ, khách sạn, biệt thự, homestay và các khu nghỉ dưỡng tiện nghi tại Đông Anh.', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(3, 'Wellness & Care', 'wellness-care', '🏥', 'Hệ thống cơ sở y tế, phòng khám, chăm sóc sức khỏe và spa thư giãn hàng đầu Đông Anh.', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(4, 'Đông Anh Market', 'dong-anh-market', '🛍️', 'Nơi hội tụ các sản phẩm OCOP, đặc sản địa phương, chợ truyền thống và trung tâm mua sắm sầm uất.', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(5, 'Smart Education Map', 'smart-education-map', '🏫', 'Hệ thống trường học và cơ sở giáo dục chất lượng cao trên địa bàn huyện Đông Anh.', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(6, 'Hành trình di sản', 'hanh-trinh-di-san', '🏛️', 'Kết nối hành trình khám phá di tích lịch sử và văn hóa thông qua nền tảng Donganh360.vn.', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(7, 'Discover Dong Anh Community & Culture Hub', 'discover-dong-anh-community-culture-hub', '🏛️', 'Khám phá hệ thống thiết chế văn hóa - thể thao Đông Anh: Nhà văn hóa, nhà thi đấu, trung tâm triển lãm, nhà văn hóa thôn và tổ dân phố.', '2026-06-01 03:58:08', '2026-06-01 03:58:08');

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
(1, 'Thôn Đông Trù', 'thon-dong-tru', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(2, 'Thôn Đoài', 'thon-doai', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(3, 'Thôn Lực Canh', 'thon-luc-canh', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(4, 'Thôn Xuân Trạch', 'thon-xuan-trach', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(5, 'Thôn Gia Lộc', 'thon-gia-loc', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(6, 'Thôn Mạch Tràng', 'thon-mach-trang', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(7, 'Thôn Trung', 'thon-trung', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(8, 'Thôn Lộc Hà', 'thon-loc-ha', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(9, 'Thôn Lý Nhân', 'thon-ly-nhan', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(10, 'Thôn Lại Đà', 'thon-lai-da', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(11, 'Thôn Đông Ngàn', 'thon-dong-ngan', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(12, 'Thôn Thái Bình', 'thon-thai-binh', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(13, 'Thôn Đông', 'thon-dong', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(14, 'Thôn Tiên Hội', 'thon-tien-hoi', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(15, 'Thôn Du Nội', 'thon-du-noi', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(16, 'Thôn Hội Phụ', 'thon-hoi-phu', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(17, 'Thôn Mai Hiên', 'thon-mai-hien', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(18, 'Thôn Vang', 'thon-vang', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(19, 'Thôn Xuân Canh', 'thon-xuan-canh', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(20, 'Thôn Thượng (CL)', 'thon-thuong-cl', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(21, 'Thôn Nhồi Dưới', 'thon-nhoi-duoi', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(22, 'Thôn Dục Tú 1', 'thon-duc-tu-1', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(23, 'Thôn Trong', 'thon-trong', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(24, 'Thôn Trung Thôn', 'thon-trung-thon', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(25, 'Thôn Đản Mỗ', 'thon-dan-mo', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(26, 'Thôn Văn Thượng', 'thon-van-thuong', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(27, 'Thôn Cầu Cả', 'thon-cau-ca', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(28, 'Thôn Lê Xá', 'thon-le-xa', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(29, 'Thôn Thượng (UN)', 'thon-thuong-un', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(30, 'Thôn Đồng Dầu', 'thon-dong-dau', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(31, 'Thôn Chợ (CL)', 'thon-cho-cl', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(32, 'Thôn Dục Tú 3', 'thon-duc-tu-3', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(33, 'Thôn Thạc Quả', 'thon-thac-qua', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(34, 'Thôn Du Ngoại', 'thon-du-ngoai', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(35, 'Thôn Phan Xá', 'thon-phan-xa', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(36, 'Thôn Ngoài', 'thon-ngoai', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(37, 'Thôn Đản Dị', 'thon-dan-di', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(38, 'Thôn Phúc Hậu 2', 'thon-phuc-hau-2', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(39, 'Thôn Vạn Lộc', 'thon-van-loc', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(40, 'Thôn Gà', 'thon-ga', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(41, 'Tổ dân phố số 6', 'to-dan-pho-so-6', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(42, 'Thôn Đài Bi', 'thon-dai-bi', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(43, 'Thôn Hậu', 'thon-hau', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(44, 'Thôn Mít', 'thon-mit', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(45, 'Thôn Phúc Hậu 1', 'thon-phuc-hau-1', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(46, 'Thôn Dục Tú 2', 'thon-duc-tu-2', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(47, 'Thôn Sằn', 'thon-san', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(48, 'Thôn Dõng', 'thon-dong-d', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(49, 'Thôn Chùa', 'thon-chua', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(50, 'Thôn Văn Tinh', 'thon-van-tinh', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(51, 'Tổ dân phố số 4', 'to-dan-pho-so-4', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(52, 'Thôn Nghĩa Vũ', 'thon-nghia-vu', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(53, 'Thôn Hương', 'thon-huong', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(54, 'Tổ dân phố số 35 (+ Khu vực 382, Khu vực X89)', 'to-dan-pho-so-35', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(55, 'Thôn Nhồi Trên', 'thon-nhoi-tren', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(56, 'Thôn Phúc Thọ', 'thon-phuc-tho', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(57, 'Thôn Phúc Lộc', 'thon-phuc-loc', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(58, 'Tổ dân phố số 38', 'to-dan-pho-so-38', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(59, 'Tổ dân phố số 39', 'to-dan-pho-so-39', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(60, 'Thôn Nghĩa Lại', 'thon-nghia-lai', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(61, 'Thôn Ấp Tó', 'thon-ap-to', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(62, 'Thôn Bãi (UN)', 'thon-bai-un', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(63, 'Thôn Chợ (UN)', 'thon-cho-un', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(64, 'Tổ dân phố số 3', 'to-dan-pho-so-3', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(65, 'Tổ dân phố số 2', 'to-dan-pho-so-2', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(66, 'Tổ dân phố số 1', 'to-dan-pho-so-1', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(67, 'Thôn Lan Trì', 'thon-lan-tri', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(68, 'Thôn Lương Quán', 'thon-luong-quan', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(69, 'Tổ dân phố số 37', 'to-dan-pho-so-37', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(70, 'Tổ dân phố số 36 (+ Khu vực Công trình 6)', 'to-dan-pho-so-36', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(71, 'Khu tập thể Địa chất', 'khu-tap-the-dia-chat', '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(72, 'Thôn Phố Chợ', 'thon-pho-cho', '2026-06-01 03:58:08', '2026-06-01 03:58:08');

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

--
-- Dumping data for table `cultural_activities`
--

INSERT INTO `cultural_activities` (`id`, `eatery_id`, `name`, `type`, `price`, `unit`, `discount_note`, `description`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 7, 'Bắn nỏ, làm bỏng chủ, oản xôi lá mít dâng vua, đúc các hiện vật tiêu biểu xưởng thủ công Âu Lạc', 'experience', 1000000.00, '10 người', NULL, 'Bắn nỏ là biểu tượng cho công nghệ quân sự đỉnh cao của Nhà nước Âu Lạc, được minh chứng qua truyền thuyết nỏ thần An Dương Vương.  Kỹ sư Vũ Đình Thanh đã phục dựng thành công mẫu nỏ dài 2m, sử dụng vuốt rùa làm lẫy nỏ và ống chứa tên, có khả năng bắn đồng loạt hàng chục mũi tên đồng Cổ Loa (mô phỏng công nghệ flechette) với tầm xa lên đến 1.000m, phản ánh sức mạnh bảo vệ chủ quyền và trình độ chế tạo vũ khí tinh xảo của người Việt cổ. \r\n\r\nLàm bỏng chủ (bánh chưng/bánh giầy) và oản xôi lá mít dâng vua liên quan đến thời kỳ Nhà nước Văn Lang (tiền thân và giai đoạn đầu của cộng đồng cư dân Âu Lạc).  Theo truyền thuyết, Lang Liêu đã sáng tạo ra bánh chưng (hình vuông, tượng đất) và bánh giầy (hình tròn, tượng trời) từ gạo nếp để dâng vua Hùng, thể hiện nền văn minh nông nghiệp lúa nước và triết lý âm dương, cha mẹ sinh thành.  Các nghi lễ dâng cúng này khẳng định vai trò của nông nghiệp trong đời sống kinh tế và văn hóa tâm linh của cư dân vùng sông Hồng.\r\n\r\nĐúc các hiện vật tiêu biểu tại các xưởng thủ công Âu Lạc (như Cổ Loa) đã tạo ra những vũ khí và đồ dùng bằng đồng sắc bén, bền chắc.  Các hiện vật khảo cổ gồm mũi tên đồng, lẫy nỏ, rìu chiến và thạp đồng cho thấy trình độ luyện kim phát triển cao, phục vụ cả cho sản xuất nông nghiệp (cày, hái) và chiến đấu. Sự hiện diện của các mũi tên đồng Cổ Loa là bằng chứng vật chất xác thực cho cơ chế hoạt động của nỏ thần và sự thịnh vượng của kỹ thuật chế tác kim loại trong giai đoạn này', NULL, '2026-06-03 16:28:53', '2026-06-03 16:28:53'),
(2, 7, 'Tham quan di tích lịch sử thành cổ loa', 'ticket', 30000.00, '1 người', 'trường hợp học sinh 15 tuổi trở lên, sinh viên , người già 60 tuổi trở lên vé =50%', 'tham quan khu di tích Cổ Loa', NULL, '2026-06-03 16:29:55', '2026-06-03 16:29:55'),
(3, 7, 'Dịch vụ dâng hương', 'service', 1000000.00, '1 người', NULL, NULL, NULL, '2026-06-03 16:30:22', '2026-06-03 16:30:22'),
(4, 7, 'Dịch vụ thuyết minh', 'service', 150000.00, 'đoàn 30 người trở xuống', NULL, NULL, NULL, '2026-06-03 16:30:50', '2026-06-03 16:30:50'),
(5, 7, 'đặt lễ: lẵng hoa, quả; xôi, gà', 'service', NULL, '1 người', NULL, NULL, NULL, '2026-06-03 16:31:04', '2026-06-03 16:31:04');

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
(1, NULL, 'Trung Tâm Triển Lãm Quốc Gia VEC', 'trung-tam-trien-lam-quoc-gia-vec-LXQB3', 7, 4, 'Trung tâm Triển lãm Quốc gia Việt Nam (VEC) còn gọi là Trung tâm Hội chợ Triển lãm Quốc gia, tọa lạc tại huyện Đông Anh, Hà Nội, là tổ hợp triển lãm lớn nhất Đông Nam Á và thuộc top 10 thế giới.  Công trình do Tập đoàn Vingroup làm chủ đầu tư, chính thức khánh thành vào ngày 19/8/2025 sau gần 10 tháng thi công với tổng vốn đầu tư hơn 7.000 tỷ đồng. \r\n\r\nQuy mô: Tổng diện tích hơn 900.000 m² (90 ha), trong đó diện tích triển lãm trong nhà khoảng 304.000 m². \r\nKiến trúc: Điểm nhấn là Tòa nhà triển lãm Kim Quy với mái vòm thép nặng 24.000 tấn, cao 56m, lấy cảm hứng từ truyền thuyết Cổ Loa. \r\nCơ sở hạ tầng: Bao gồm 9 đại sảnh, 4 sân triển lãm ngoài trời, Trung tâm Hội nghị VinPalace Cổ Loa, khách sạn 5 sao và khu ẩm thực.\r\nSự kiện nổi bật: Là nơi diễn ra Triển lãm Thành tựu đất nước mừng 80 năm Quốc khánh (từ 28/8 đến 5/9/2025, sau đó kéo dài đến 15/9).', 'Trung Tâm Triển Lãm Việt Nam, Đường Cầu Tứ Liên Trường Sa, Đông Anh, Hà Nội, Việt Nam', '0399294268', NULL, 21.086561, 105.862793, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780303679_gPFjdL3q.jpg', 0, 5.00, 'active', '2026-06-01 08:48:00', '2026-06-01 08:48:00'),
(2, NULL, 'Nhà thi đấu đa năng Huyện Đông Anh', 'nha-thi-dau-da-nang-huyen-dong-anh-yeeKX', 7, 36, NULL, '4VM2+JPH, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.134067, 105.851844, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780305758_xEPnCgEm.png', 0, 5.00, 'active', '2026-06-01 09:22:39', '2026-06-01 09:22:39'),
(3, NULL, 'Nhà văn hoá thôn Bãi', 'nha-van-hoa-thon-bai-hpO73', 7, 62, NULL, 'Trước ĐD trụ sở Hội người mù Đông Anh 30m - đường, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.130658, 105.866739, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780305879_WaL346CN.png', 0, 5.00, 'active', '2026-06-01 09:24:40', '2026-06-01 09:24:40'),
(4, NULL, 'Khu liên hợp thể thao Cộng Đồng Thư Cưu', 'khu-lien-hop-the-thao-cong-dong-thu-cuu-xOObx', 7, 20, NULL, '4V9J+X39, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.119902, 105.880229, '30.000đ - 100.000đ', 'https://media.xadonganh.com/eateries/1780387594_BXCerCeF.png', 0, 5.00, 'active', '2026-06-02 08:06:36', '2026-06-02 08:06:36'),
(5, NULL, 'Trung Tâm Hội Nghị VinPalace Cổ Loa', 'trung-tam-hoi-nghi-vinpalace-co-loa-CzEBR', 7, 4, 'Trung tâm Hội nghị VinPalace Ocean City\r\nVinPalace Cổ Loa là một phần của chuỗi trung tâm tổ chức sự kiện MICE, tiệc cưới và giải trí sang trọng thuộc thương hiệu Vinpearl (Tập đoàn Vingroup).  Đây là trung tâm hội nghị quốc tế lớn, chuyên cung cấp các dịch vụ văn hóa, nghệ thuật và tổ chức sự kiện quy mô. \r\n\r\n📍 Vị trí: Dự án tọa lạc tại khu vực Cổ Loa, Đông Anh, Hà Nội.\r\n\r\n📞 Thông tin liên hệ:\r\n\r\nWebsite: vinpalace.com.vn (Trang chủ) hoặc vinpalace.com.vn/vpl-coloa (Trang chi tiết Cổ Loa).\r\nĐiện thoại: +84 949 898 222 (Số hotline chung của VinPalace).', '3VJ6+JPQ, Đông Hội, Đông Anh, Hà Nội, Việt Nam', '+84 949 898 222 (', '09:00 -22:00', 21.081539, 105.861784, NULL, 'https://media.xadonganh.com/eateries/1780388728_d3qwaT0a.png', 0, 5.00, 'active', '2026-06-02 08:25:30', '2026-06-02 08:25:30'),
(6, NULL, 'Bảo Tàng Trưng Bày Cổ Vật Thành Cổ Loa', 'bao-tang-trung-bay-co-vat-thanh-co-loa-PQHoo', 7, 49, 'Tại Khu di tích Cổ Loa (huyện Đông Anh, Hà Nội), các hiện vật và tư liệu lịch sử của Thành Cổ Loa được trưng bày tập trung tại Nhà trưng bày nằm ở thôn Chùa, xã Cổ Loa.  Nơi đây giới thiệu 100 tài liệu, hình ảnh và các hiện vật khảo cổ quý giá, bao gồm đồ đồng, gốm, công cụ lao động và vũ khí từ thời kỳ nhà nước Âu Lạc và các giai đoạn văn hóa Phùng Nguyên, Đông Sơn.', '4V6F+H9F khu di tích, Đông Anh, Hà Nội 100000, Việt Nam', '0903450228', '08:00–17:00', 21.111537, 105.873361, NULL, 'https://media.xadonganh.com/eateries/1780392421_hfPQH7HB.png', 1, 5.00, 'active', '2026-06-02 09:27:04', '2026-06-02 09:27:04'),
(7, NULL, 'Khu Di Tích Cổ Loa', 'khu-di-tich-co-loa-P3Zjk', 7, 49, 'Khu di tích Cổ Loa là di tích quốc gia đặc biệt, tọa lạc tại các xã Cổ Loa, Dục Tú, Việt Hùng và Uy Nỗ thuộc huyện Đông Anh, Hà Nội, cách trung tâm thủ đô khoảng 17 km về phía bắc.  Đây là tòa thành cổ nhất, có quy mô lớn nhất và cấu trúc độc đáo nhất trong lịch sử xây dựng thành lũy của người Việt cổ, từng là kinh đô của nhà nước Âu Lạc dưới thời An Dương Vương (thế kỷ III TCN) và nước Đại Việt dưới thời Ngô Quyền (thế kỷ X). \r\n\r\nĐặc điểm kiến trúc và lịch sử:\r\n\r\nCấu trúc: Thành được xây dựng kiểu vòng ốc, hiện còn lại 3 vòng thành hào khép kín đắp bằng đất với tổng chiều dài trên 16.000m: Thành Ngoại (chu vi ~8.000m), Thành Trung (chu vi ~6.500m) và Thành Nội (chu vi ~1.650m, hình chữ nhật).  Cả ba vòng đều có hào nước bao quanh.\r\nDi tích tiêu biểu: Khu di tích bao gồm khoảng 60 di tích (7 di tích cấp quốc gia), nổi bật là Đền Thượng (thờ An Dương Vương), Đình Ngự Triều Di Quy, Giếng Ngọc, Am Bà Chúa (thờ Mỵ Châu) và Chùa Cổ Loa. \r\nGiá trị văn hóa: Khu di tích gắn liền với huyền thoại nỏ thần Kim Quy và mối tình bi thương Mỵ Châu – Trọng Thủy.  Lễ hội Cổ Loa được tổ chức vào ngày 6 tháng Giêng âm lịch hàng năm.\r\nDi chỉ khảo cổ: Nơi đây lưu giữ dấu tích của các nền văn hóa Sơn Vi, Phùng Nguyên và Đông Sơn, phản ánh quá trình phát triển liên tục của dân tộc Việt từ thời tiền sử.', '4V6F+H9F khu di tích, Đông Anh, Hà Nội 100000, Việt Nam', '0904983944', '08:00–17:00', 21.111537, 105.873361, NULL, 'https://media.xadonganh.com/eateries/1780392624_j4N12gl8.png', 1, 5.00, 'active', '2026-06-02 09:30:26', '2026-06-02 09:30:26'),
(8, NULL, 'Nhà văn hóa xã Đông Anh', 'nha-van-hoa-xa-dong-anh-jGFlE', 7, 35, NULL, '4VR2+88H, Đông Anh, Hà Nội, Việt Nam', NULL, NULL, 21.140817, 105.850848, NULL, 'https://media.xadonganh.com/eateries/1783420787_viWZg2RL.jpg', 1, 5.00, 'active', '2026-07-07 10:36:11', '2026-07-07 10:39:48');

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
(1, 'Nguyễn Văn Admin', 'admin@foodmap.vn', NULL, '$2y$12$bq0BcP/nAmD744tWPS.A8.JVHZxWbHSeinthBoC0GJLIj0gHGFUBu', 'admin', '👨💼', '0901234567', 'active', NULL, '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(2, 'Trần Thị Bích', 'seller@foodmap.vn', NULL, '$2y$12$tAnnZMlGVt33Fah78y9RHOocNyEG/uTkpqiPRlSYM069bR5dkFAui', 'seller', '👨🍳', '0912345678', 'active', NULL, '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(3, 'Thực Thần Đông Anh', 'user@foodmap.vn', NULL, '$2y$12$mLcd7n9WekwhkSwnzS1o1OnR1pIm12ItA.zU2SodvCBiHwxa/hlKW', 'user', '🧑', '0987654321', 'active', NULL, '2026-06-01 03:58:08', '2026-06-01 03:58:08'),
(4, 'Thành viên Đông Anh', 'member@foodmap.vn', NULL, '$2y$12$yJCEtSRLAaJmeGoZmZrqhemaOLNoMentnGXY0vcdZ9hjMRCMQRjL6', 'user', '👧', '0977665544', 'active', NULL, '2026-06-01 03:58:08', '2026-06-01 03:58:08');

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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
