-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 29, 2026 at 10:59 AM
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
-- Database: `tailor`
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
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `reference` varchar(120) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `reference`, `address`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Ali Ahmed', '03001234567', 'abdullah', NULL, NULL, '2026-06-29 02:23:38', '2026-06-29 03:45:16'),
(3, 'abdul', '030984444444', 'afridi', NULL, NULL, '2026-06-29 02:25:36', '2026-06-29 03:05:02');

-- --------------------------------------------------------

--
-- Table structure for table `design_options`
--

CREATE TABLE `design_options` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `category` enum('stitch','cuff_kaaj','extra','style','button') NOT NULL,
  `code` varchar(40) NOT NULL,
  `name_en` varchar(80) DEFAULT NULL,
  `name_ur` varchar(80) NOT NULL,
  `icon` varchar(40) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` smallint(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `design_options`
--

INSERT INTO `design_options` (`id`, `category`, `code`, `name_en`, `name_ur`, `icon`, `is_default`, `sort_order`) VALUES
(1, 'stitch', 'silky_single', 'Silky thread single', 'سلکی تار سنگل', NULL, 0, 1),
(2, 'stitch', 'silky_double', 'Silky thread double', 'سلکی تار ڈبل', NULL, 0, 2),
(3, 'stitch', 'chowka', 'Chowka stitch', 'چوکا سلائی', NULL, 0, 3),
(4, 'stitch', 'double', 'Double stitch', 'ڈبل سلائی', NULL, 0, 4),
(5, 'stitch', 'zanjeeri', 'Zanjeeri stitch', 'زنجیری سلائی', NULL, 1, 5),
(6, 'stitch', 'pair', 'Pair stitch', 'پیر سلائی', NULL, 0, 6),
(7, 'cuff_kaaj', 'cuff_1pleat', 'Cuff one pleat', 'کف میں ایک پلیٹ', 'i-cuff', 1, 1),
(8, 'cuff_kaaj', 'cuff_nopleat', 'No cuff pleat', 'کف پلیٹ نہیں', 'i-cuff', 0, 2),
(9, 'cuff_kaaj', 'chaak_kaaj', 'Chaak butti kaaj', 'چاک بٹی کاج', NULL, 1, 3),
(10, 'cuff_kaaj', 'kaaj_5', '5 kaaj on butti', 'بٹی میں 5 کاج', NULL, 1, 4),
(11, 'extra', 'shalwar_pocket', 'Shalwar pocket', 'شلوار جیب', NULL, 1, 1),
(12, 'extra', 'btn_from_shop', 'Buttons from shop', 'بٹن دکان سے', NULL, 0, 2),
(13, 'extra', 'no_name', 'No name tag', 'نام نہیں', NULL, 0, 3),
(14, 'extra', 'make_drawing', 'Make drawing', 'ڈرائنگ کرنا', 'i-pen', 0, 4),
(15, 'style', 'khal_been', 'Khal been collar', 'خل بین', 'i-collar', 1, 1),
(16, 'style', 'half_cuff', 'Half cuff', 'ھاف کف', 'i-cuff', 0, 2),
(17, 'style', 'single_bais', 'Single bais', 'شنگل بیس', 'i-bais', 1, 3),
(18, 'style', 'round_side', 'Round side', 'گول سھے', 'i-daman', 0, 4),
(19, 'style', 'seedha', 'Straight', 'سیدھا', 'i-len', 1, 5),
(20, 'style', 'zail_patti', 'Zail patti', 'ذیل پٹی', 'i-vest', 0, 6),
(21, 'style', 'round_sleeve', 'Round sleeve', 'گول بازو', 'i-sleeve', 0, 7),
(22, 'style', 'cup_sleeve', 'Cup sleeve', 'کپ بازو', 'i-sleeve', 0, 8),
(23, 'button', 'double_chaak', 'Double chaak', 'ڈبل چاک', NULL, 1, 1),
(24, 'button', 'two_button', 'Two buttons', 'دو بٹن', NULL, 1, 2),
(25, 'button', 'three_button', 'Three buttons', 'تین بٹن', NULL, 0, 3);

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
-- Table structure for table `garment_types`
--

CREATE TABLE `garment_types` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name_en` varchar(60) NOT NULL,
  `name_ur` varchar(60) NOT NULL,
  `icon` varchar(40) DEFAULT NULL,
  `sort_order` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `garment_types`
--

INSERT INTO `garment_types` (`id`, `code`, `name_en`, `name_ur`, `icon`, `sort_order`) VALUES
(1, 'kameez', 'Kameez', 'قمیض', 'i-kameez', 1),
(2, 'waistcoat', 'Waistcoat', 'واسکٹ', 'i-vest', 2);

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
-- Table structure for table `measurement_points`
--

CREATE TABLE `measurement_points` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `garment_type_id` tinyint(3) UNSIGNED NOT NULL,
  `code` varchar(40) NOT NULL,
  `name_en` varchar(60) NOT NULL,
  `name_ur` varchar(60) NOT NULL,
  `icon` varchar(40) DEFAULT NULL,
  `sort_order` smallint(6) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `measurement_points`
--

INSERT INTO `measurement_points` (`id`, `garment_type_id`, `code`, `name_en`, `name_ur`, `icon`, `sort_order`) VALUES
(1, 1, 'length', 'Length', 'لمبائی', 'i-len', 1),
(2, 1, 'shoulder', 'Shoulder', 'تیرہ', 'i-shoulder', 2),
(3, 1, 'sleeve', 'Sleeve', 'بازو', 'i-sleeve', 3),
(4, 1, 'chest', 'Chest', 'چھاتی', 'i-chest', 4),
(5, 1, 'waist', 'Waist', 'کمر', 'i-waist', 5),
(6, 1, 'daman', 'Daman', 'دامن', 'i-daman', 6),
(7, 1, 'collar', 'Collar', 'کالر', 'i-collar', 7),
(8, 1, 'shalwar', 'Shalwar', 'شلوار', 'i-shalwar', 8),
(9, 1, 'pancha', 'Pancha', 'پانچہ', 'i-pancha', 9),
(10, 2, 'length', 'Length', 'لمبائی', 'i-len', 1),
(11, 2, 'shoulder', 'Shoulder', 'تیرہ', 'i-shoulder', 2),
(12, 2, 'sleeve', 'Sleeve', 'بازو', 'i-sleeve', 3),
(13, 2, 'chest', 'Chest', 'چھاتی', 'i-chest', 4),
(14, 2, 'waist', 'Waist', 'کمر', 'i-waist', 5),
(15, 2, 'bais', 'Bais', 'بیس', 'i-bais', 6),
(16, 2, 'collar', 'Collar', 'کالر', 'i-collar', 7);

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
(4, '2024_01_01_000001_create_customers_table', 2),
(5, '2024_01_01_000002_create_garment_types_table', 2),
(6, '2024_01_01_000003_create_measurement_points_table', 2),
(7, '2024_01_01_000004_create_design_options_table', 2),
(8, '2024_01_01_000005_create_orders_table', 2),
(9, '2024_01_01_000006_create_order_garments_table', 2),
(10, '2024_01_01_000007_create_order_measurements_table', 2),
(11, '2024_01_01_000008_create_order_design_options_table', 2),
(12, '2026_06_29_075519_add_advance_paid_to_orders_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_no` varchar(20) NOT NULL,
  `customer_id` bigint(20) UNSIGNED NOT NULL,
  `booking_date` date DEFAULT NULL,
  `delivery_date` date DEFAULT NULL,
  `quantity` smallint(6) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `advance_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `colour_note` varchar(150) DEFAULT NULL,
  `extra_notes` text DEFAULT NULL,
  `status` enum('pending','stitching','ready','delivered','returned','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_no`, `customer_id`, `booking_date`, `delivery_date`, `quantity`, `price`, `advance_paid`, `colour_note`, `extra_notes`, `status`, `created_at`, `updated_at`) VALUES
(1, '1', 1, '2026-01-12', '2012-12-21', 1, 1200.00, 1000.00, NULL, NULL, 'pending', '2026-06-29 02:23:38', '2026-06-29 03:03:37'),
(2, '6618', 1, '2026-01-12', '2012-12-21', 1, 1300.00, 0.00, NULL, NULL, 'pending', '2026-06-29 02:24:25', '2026-06-29 02:24:25'),
(4, '2', 3, '2026-01-12', '2012-12-21', 1, 1200.00, 0.00, NULL, NULL, 'pending', '2026-06-29 02:25:36', '2026-06-29 02:25:36'),
(5, '3', 3, '2026-01-12', '2012-12-21', 1, 1200.00, 0.00, NULL, NULL, 'pending', '2026-06-29 02:32:01', '2026-06-29 02:32:01'),
(6, '10', 1, '2026-01-12', '2012-12-21', 1, 1200.00, 0.00, NULL, NULL, 'pending', '2026-06-29 02:34:26', '2026-06-29 02:34:26'),
(7, '6619', 1, '2026-07-03', '2026-07-10', 1, 1300.00, 0.00, NULL, NULL, 'pending', '2026-06-29 03:05:51', '2026-06-29 03:05:51'),
(8, '6620', 1, '2026-06-08', '2026-06-03', 1, 1200.00, 0.00, NULL, NULL, 'pending', '2026-06-29 03:45:16', '2026-06-29 03:45:16');

-- --------------------------------------------------------

--
-- Table structure for table `order_design_options`
--

CREATE TABLE `order_design_options` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `design_option_id` smallint(5) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_design_options`
--

INSERT INTO `order_design_options` (`id`, `order_id`, `design_option_id`) VALUES
(1, 1, 5),
(2, 1, 7),
(3, 1, 9),
(4, 1, 10),
(5, 1, 11),
(6, 1, 15),
(7, 1, 17),
(8, 1, 19),
(9, 1, 23),
(10, 1, 24),
(11, 2, 5),
(12, 2, 7),
(13, 2, 9),
(14, 2, 10),
(15, 2, 11),
(16, 2, 15),
(17, 2, 17),
(18, 2, 19),
(19, 2, 23),
(20, 2, 24),
(21, 4, 5),
(22, 4, 7),
(23, 4, 9),
(24, 4, 10),
(25, 4, 11),
(26, 4, 15),
(27, 4, 17),
(28, 4, 19),
(29, 4, 23),
(30, 4, 24),
(33, 5, 15),
(36, 5, 23),
(37, 5, 24),
(38, 6, 5),
(39, 6, 7),
(40, 6, 9),
(41, 6, 10),
(42, 6, 11),
(43, 6, 15),
(44, 6, 17),
(45, 6, 19),
(46, 6, 23),
(47, 6, 24),
(48, 7, 5),
(49, 7, 7),
(50, 7, 9),
(51, 7, 10),
(52, 7, 11),
(53, 7, 15),
(54, 7, 17),
(55, 7, 19),
(56, 7, 23),
(57, 7, 24),
(58, 8, 5),
(59, 8, 7),
(60, 8, 9),
(61, 8, 10),
(62, 8, 11),
(63, 8, 15),
(64, 8, 17),
(65, 8, 19),
(66, 8, 23),
(67, 8, 24);

-- --------------------------------------------------------

--
-- Table structure for table `order_garments`
--

CREATE TABLE `order_garments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `garment_type_id` tinyint(3) UNSIGNED NOT NULL,
  `quantity` smallint(6) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_garments`
--

INSERT INTO `order_garments` (`id`, `order_id`, `garment_type_id`, `quantity`) VALUES
(1, 1, 1, 1),
(2, 1, 2, 1),
(3, 2, 1, 1),
(4, 2, 2, 1),
(5, 4, 1, 1),
(6, 4, 2, 1),
(7, 5, 1, 1),
(8, 5, 2, 1),
(9, 6, 1, 1),
(10, 6, 2, 1),
(11, 7, 1, 1),
(12, 7, 2, 1),
(13, 8, 1, 1),
(14, 8, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `order_measurements`
--

CREATE TABLE `order_measurements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_garment_id` bigint(20) UNSIGNED NOT NULL,
  `measurement_point_id` smallint(5) UNSIGNED NOT NULL,
  `value` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_measurements`
--

INSERT INTO `order_measurements` (`id`, `order_garment_id`, `measurement_point_id`, `value`) VALUES
(1, 1, 1, '1'),
(2, 1, 2, '11'),
(3, 1, 3, '1'),
(4, 1, 4, '1'),
(5, 1, 5, '1'),
(6, 1, 6, '1'),
(7, 1, 7, '1'),
(8, 1, 8, '1'),
(9, 1, 9, '1'),
(10, 2, 10, '1'),
(11, 2, 11, '1'),
(12, 2, 12, '1'),
(13, 2, 13, '1'),
(14, 2, 14, '1'),
(15, 2, 15, '1'),
(16, 2, 16, NULL),
(17, 3, 1, NULL),
(18, 3, 2, NULL),
(19, 3, 3, NULL),
(20, 3, 4, NULL),
(21, 3, 5, NULL),
(22, 3, 6, NULL),
(23, 3, 7, NULL),
(24, 3, 8, NULL),
(25, 3, 9, NULL),
(26, 4, 10, NULL),
(27, 4, 11, NULL),
(28, 4, 12, NULL),
(29, 4, 13, NULL),
(30, 4, 14, NULL),
(31, 4, 15, NULL),
(32, 4, 16, NULL),
(33, 5, 1, '2'),
(34, 5, 2, '2'),
(35, 5, 3, '2'),
(36, 5, 4, '2'),
(37, 5, 5, '2'),
(38, 5, 6, '2'),
(39, 5, 7, '2'),
(40, 5, 8, '2'),
(41, 5, 9, '2'),
(42, 6, 10, '2'),
(43, 6, 11, '2'),
(44, 6, 12, '2'),
(45, 6, 13, '2'),
(46, 6, 14, '2'),
(47, 6, 15, '22'),
(48, 6, 16, '222'),
(49, 7, 1, '3'),
(50, 7, 2, '3'),
(51, 7, 3, '33'),
(52, 7, 4, '3'),
(53, 7, 5, '3'),
(54, 7, 6, '3'),
(55, 7, 7, '3'),
(56, 7, 8, '3'),
(57, 7, 9, NULL),
(58, 8, 10, '3'),
(59, 8, 11, '3'),
(60, 8, 12, '3'),
(61, 8, 13, '3'),
(62, 8, 14, '3'),
(63, 8, 15, '3'),
(64, 8, 16, NULL),
(65, 9, 1, '10'),
(66, 9, 2, '10'),
(67, 9, 3, '10'),
(68, 9, 4, NULL),
(69, 9, 5, NULL),
(70, 9, 6, NULL),
(71, 9, 7, NULL),
(72, 9, 8, NULL),
(73, 9, 9, NULL),
(74, 10, 10, NULL),
(75, 10, 11, NULL),
(76, 10, 12, NULL),
(77, 10, 13, NULL),
(78, 10, 14, NULL),
(79, 10, 15, NULL),
(80, 10, 16, NULL),
(81, 11, 1, NULL),
(82, 11, 2, NULL),
(83, 11, 3, NULL),
(84, 11, 4, NULL),
(85, 11, 5, NULL),
(86, 11, 6, NULL),
(87, 11, 7, NULL),
(88, 11, 8, NULL),
(89, 11, 9, NULL),
(90, 12, 10, NULL),
(91, 12, 11, NULL),
(92, 12, 12, NULL),
(93, 12, 13, NULL),
(94, 12, 14, NULL),
(95, 12, 15, NULL),
(96, 12, 16, NULL),
(97, 13, 1, NULL),
(98, 13, 2, NULL),
(99, 13, 3, NULL),
(100, 13, 4, NULL),
(101, 13, 5, NULL),
(102, 13, 6, NULL),
(103, 13, 7, NULL),
(104, 13, 8, NULL),
(105, 13, 9, NULL),
(106, 14, 10, NULL),
(107, 14, 11, NULL),
(108, 14, 12, NULL),
(109, 14, 13, NULL),
(110, 14, 14, NULL),
(111, 14, 15, NULL),
(112, 14, 16, NULL);

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
('ItLk4f2G7RtBpdNeyO0PIknqsbklg3lNnNXiCcFz', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYkJMTHA0RnZ3TndRajU1MHBIVzVXa0VaVk1JTjJiekFpTTVoVmhzZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1782715121);

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
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Test User', 'test@example.com', '2026-06-29 01:37:47', '$2y$12$d6d/.MghsR8TjjGVrHvVwOBW4Ci7NUpw4qzo4e5DGa5hIfeDHXag2', 'a5KvHn2e9Z', '2026-06-29 01:37:47', '2026-06-29 01:37:47');

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
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customers_name_index` (`name`),
  ADD KEY `customers_phone_index` (`phone`);

--
-- Indexes for table `design_options`
--
ALTER TABLE `design_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `design_options_category_code_unique` (`category`,`code`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `garment_types`
--
ALTER TABLE `garment_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `garment_types_code_unique` (`code`);

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
-- Indexes for table `measurement_points`
--
ALTER TABLE `measurement_points`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `measurement_points_garment_type_id_code_unique` (`garment_type_id`,`code`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `orders_order_no_unique` (`order_no`),
  ADD KEY `orders_customer_id_index` (`customer_id`),
  ADD KEY `orders_status_index` (`status`),
  ADD KEY `orders_delivery_date_index` (`delivery_date`);

--
-- Indexes for table `order_design_options`
--
ALTER TABLE `order_design_options`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_design_options_order_id_design_option_id_unique` (`order_id`,`design_option_id`),
  ADD KEY `order_design_options_design_option_id_foreign` (`design_option_id`);

--
-- Indexes for table `order_garments`
--
ALTER TABLE `order_garments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_garments_order_id_garment_type_id_unique` (`order_id`,`garment_type_id`),
  ADD KEY `order_garments_garment_type_id_index` (`garment_type_id`);

--
-- Indexes for table `order_measurements`
--
ALTER TABLE `order_measurements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_measurements_order_garment_id_measurement_point_id_unique` (`order_garment_id`,`measurement_point_id`),
  ADD KEY `order_measurements_measurement_point_id_foreign` (`measurement_point_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

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
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `design_options`
--
ALTER TABLE `design_options`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `garment_types`
--
ALTER TABLE `garment_types`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `measurement_points`
--
ALTER TABLE `measurement_points`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `order_design_options`
--
ALTER TABLE `order_design_options`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `order_garments`
--
ALTER TABLE `order_garments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `order_measurements`
--
ALTER TABLE `order_measurements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `measurement_points`
--
ALTER TABLE `measurement_points`
  ADD CONSTRAINT `measurement_points_garment_type_id_foreign` FOREIGN KEY (`garment_type_id`) REFERENCES `garment_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`);

--
-- Constraints for table `order_design_options`
--
ALTER TABLE `order_design_options`
  ADD CONSTRAINT `order_design_options_design_option_id_foreign` FOREIGN KEY (`design_option_id`) REFERENCES `design_options` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_design_options_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_garments`
--
ALTER TABLE `order_garments`
  ADD CONSTRAINT `order_garments_garment_type_id_foreign` FOREIGN KEY (`garment_type_id`) REFERENCES `garment_types` (`id`),
  ADD CONSTRAINT `order_garments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_measurements`
--
ALTER TABLE `order_measurements`
  ADD CONSTRAINT `order_measurements_measurement_point_id_foreign` FOREIGN KEY (`measurement_point_id`) REFERENCES `measurement_points` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_measurements_order_garment_id_foreign` FOREIGN KEY (`order_garment_id`) REFERENCES `order_garments` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
