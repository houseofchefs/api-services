-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 08, 2023 at 08:04 PM
-- Server version: 8.0.30
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `house_of_chef_old`
--

-- --------------------------------------------------------

--
-- Table structure for table `address`
--

CREATE TABLE `address` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL,
  `place_id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `door_no` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lanmark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address_line` varchar(250) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Other',
  `pincode` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,6) NOT NULL,
  `guard` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `address`
--

INSERT INTO `address` (`id`, `user_id`, `place_id`, `door_no`, `lanmark`, `address_line`, `address_type`, `pincode`, `latitude`, `longitude`, `guard`, `created_at`, `updated_at`) VALUES
(3, 1, 'ChIJLSX6PalZ7TkRVcJ7MhCcFbI', '06', 'Vinayagar Temple', '0612 Roadchef Take Away, Boring Patliputra Road, Patliputra Colony, Patna, Bihar, India', 'Other', '626111', -7.21535000, 25.219465, 'cook', '2023-05-02 11:44:31', '2023-05-02 11:44:31'),
(4, 1, 'ChIJLSX6PalZ7TkRVcJ7MhCcFbI', '3/1', NULL, '0612 Roadchef Take Away, Boring Patliputra Road, Patliputra Colony, Patna, Bihar, India', 'Other', '641035', 10.99739500, 436.992359, 'customer', '2023-05-07 07:01:54', '2023-05-07 07:01:54'),
(5, 1, 'ChIJLSX6PalZ7TkRVcJ7MhCcFbI', '3/1', NULL, '0612 Roadchef Take Away, Boring Patliputra Road, Patliputra Colony, Patna, Bihar, India', 'Other', '641035', 10.99739500, 436.992359, 'customer', '2023-05-07 07:24:41', '2023-05-07 07:24:41'),
(6, 1, 'ChIJLSX6PalZ7TkRVcJ7MhCcFbI', '3/1', NULL, '0612 Roadchef Take Away, Boring Patliputra Road, Patliputra Colony, Patna, Bihar, India', 'Home', '641035', 10.99739500, 436.992359, 'customer', '2023-05-07 07:25:07', '2023-05-07 07:25:07');

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int DEFAULT NULL,
  `bank_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `holder_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_number` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_type` int NOT NULL,
  `ifsc_code` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `user_id`, `bank_name`, `holder_name`, `account_number`, `account_type`, `ifsc_code`, `guard`, `created_at`, `updated_at`) VALUES
(3, 1, 'CUB', 'Ramanathan', '1234678951', 1, 'CUB0120120', 'cook', '2023-05-02 11:44:31', '2023-05-02 11:44:31');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `cook_id` int NOT NULL,
  `quantity` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int NOT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(2, 'Briyani', 2, 1, 1, '2023-05-03 12:28:46', '2023-05-03 12:28:46'),
(3, 'Dosa', 2, 1, 1, '2023-05-05 11:21:58', '2023-05-05 11:21:58'),
(4, 'Ice-Cream', 2, 1, 1, '2023-05-05 11:22:09', '2023-05-05 11:22:09');

-- --------------------------------------------------------

--
-- Table structure for table `categories_has_slot`
--

CREATE TABLE `categories_has_slot` (
  `id` int NOT NULL,
  `category_id` int NOT NULL,
  `slot_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories_has_slot`
--

INSERT INTO `categories_has_slot` (`id`, `category_id`, `slot_id`) VALUES
(1, 2, 56),
(2, 2, 55),
(3, 3, 54),
(4, 3, 55),
(5, 4, 54),
(6, 4, 55);

-- --------------------------------------------------------

--
-- Table structure for table `cooks`
--

CREATE TABLE `cooks` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_id` int DEFAULT NULL,
  `bank_id` int DEFAULT NULL,
  `gender` int DEFAULT NULL,
  `latitude` decimal(10,6) DEFAULT NULL,
  `longitude` decimal(10,6) DEFAULT NULL,
  `status` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cooks`
--

INSERT INTO `cooks` (`id`, `name`, `email`, `mobile`, `password`, `address_id`, `bank_id`, `gender`, `latitude`, `longitude`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Shahul', NULL, '7092462702', '$2y$10$nE97FwolW8Z0ySolgo16ZOjYmBoruLjEdn5VSOUmOUjFEHiP3RWTS', NULL, NULL, NULL, NULL, NULL, 2, '2023-04-18 08:53:14', '2023-04-18 08:53:14');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `referral_code` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int NOT NULL DEFAULT '0',
  `address_id` int DEFAULT NULL,
  `gender` int DEFAULT NULL,
  `signup_with` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `mobile`, `password`, `referral_code`, `points`, `address_id`, `gender`, `signup_with`, `created_at`, `updated_at`) VALUES
(1, 'Prabhu GM', NULL, '7092462701', '$2y$10$Y36.D0nocQJU44LsFYOeHuOp5jVjsZ70wwowDqApcDvC9kPeMrY1K', '07ISML', 0, 5, NULL, NULL, '2023-05-06 23:06:32', '2023-05-08 11:32:45');

-- --------------------------------------------------------

--
-- Table structure for table `discounts`
--

CREATE TABLE `discounts` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `percentage` bigint NOT NULL,
  `description` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` int NOT NULL,
  `expire_at` datetime NOT NULL,
  `status` int NOT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `discounts`
--

INSERT INTO `discounts` (`id`, `name`, `category_id`, `vendor_id`, `percentage`, `description`, `image`, `type`, `expire_at`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'Good Friday Offer!!', 3, 1, 20, 'Good Friday Offer each items discount with 20%', 'discount/image.jpg', 25, '2023-05-14 15:32:04', 2, 1, 1, '2023-05-07 10:02:04', '2023-05-07 10:02:04'),
(2, 'Good Friday Offer!!', 2, 0, 45, 'Good Friday Offer each items discount with 20%', 'discount/image.jpg', 25, '2023-05-14 15:32:26', 2, 1, 1, '2023-05-07 10:02:26', '2023-05-07 10:02:26'),
(3, 'Good Friday Offer!!', 4, 0, 35, 'Good Friday Offer each items discount with 20%', 'discount/image.jpg', 25, '2023-05-14 15:32:46', 2, 1, 1, '2023-05-07 10:02:46', '2023-05-07 10:02:46');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `ingredients`
--

CREATE TABLE `ingredients` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `calories` float NOT NULL,
  `fat` float NOT NULL,
  `carbohydrates` float NOT NULL,
  `protein` float NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ingredients`
--

INSERT INTO `ingredients` (`id`, `name`, `description`, `calories`, `fat`, `carbohydrates`, `protein`, `created_at`, `updated_at`) VALUES
(1, 'Rice', NULL, 10, 10, 10, 10, NULL, NULL),
(2, 'Sugar', NULL, 10, 10, 10, 10, NULL, NULL),
(3, 'Tomato', NULL, 10, 10, 10, 10, NULL, NULL),
(4, 'Potato', NULL, 10, 10, 10, 10, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `menus`
--

CREATE TABLE `menus` (
  `id` int NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `vendor_id` int NOT NULL,
  `category_id` int NOT NULL,
  `description` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `isPreOrder` tinyint(1) NOT NULL,
  `isDaily` tinyint(1) NOT NULL,
  `image` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `type` int NOT NULL,
  `min_quantity` int NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `rating` bigint NOT NULL DEFAULT '0',
  `ucount` bigint NOT NULL DEFAULT '0',
  `status` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menus`
--

INSERT INTO `menus` (`id`, `name`, `vendor_id`, `category_id`, `description`, `isPreOrder`, `isDaily`, `image`, `type`, `min_quantity`, `price`, `rating`, `ucount`, `status`, `created_at`, `updated_at`) VALUES
(3, 'Sakkarai Pongal', 1, 2, 'Test Description', 0, 1, 'menu/image.jpeg', 14, 3, 50.00, 0, 0, 6, '2023-05-05 12:02:40', '2023-05-07 17:16:59'),
(5, 'Sakkarai Pongal', 1, 4, 'Test Description', 1, 0, 'menu/image.jpeg', 14, 3, 50.00, 0, 0, 6, '2023-05-05 12:08:24', '2023-05-07 17:27:26'),
(6, 'Sakkarai Pongal', 1, 3, 'Test Description', 0, 0, 'menu/image.jpeg', 14, 3, 50.00, 0, 0, 6, '2023-05-07 11:05:52', '2023-05-07 17:29:38');

-- --------------------------------------------------------

--
-- Table structure for table `menu_available_days`
--

CREATE TABLE `menu_available_days` (
  `id` int NOT NULL,
  `menu_id` int NOT NULL,
  `day` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_available_days`
--

INSERT INTO `menu_available_days` (`id`, `menu_id`, `day`) VALUES
(1, 5, 0),
(2, 5, 3),
(3, 5, 5),
(4, 6, 0),
(5, 6, 3),
(6, 6, 5);

-- --------------------------------------------------------

--
-- Table structure for table `menu_has_ingredients`
--

CREATE TABLE `menu_has_ingredients` (
  `id` int NOT NULL,
  `menu_id` int NOT NULL,
  `ingredient_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_has_ingredients`
--

INSERT INTO `menu_has_ingredients` (`id`, `menu_id`, `ingredient_id`) VALUES
(1, 3, 1),
(2, 3, 2),
(5, 5, 1),
(6, 5, 2),
(7, 6, 1),
(8, 6, 2);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2019_08_19_000000_create_failed_jobs_table', 1),
(3, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(4, '2023_03_25_110702_create_address_table', 1),
(5, '2023_03_25_111030_create_banks_table', 1),
(6, '2023_03_25_111344_create_orders_table', 1),
(7, '2023_03_25_113347_create_products_table', 1),
(8, '2023_03_25_113629_create_categories_table', 1),
(9, '2023_03_25_113933_create_discounts_table', 1),
(10, '2023_03_25_114204_create_rider_activities_table', 1),
(11, '2023_03_25_114536_create_vehicles_table', 1),
(12, '2023_03_25_114602_create_modules_table', 1),
(13, '2023_03_28_134459_create_verification_code_table', 1),
(14, '2023_04_01_162538_create_cooks_table', 1),
(15, '2023_04_01_162619_create_customers_table', 1),
(16, '2023_04_01_162627_create_riders_table', 1),
(17, '2023_04_01_171139_create_ingredients_table', 1),
(18, '2023_04_01_171154_create_order_details_table', 1),
(19, '2023_04_01_215034_create_permission_tables', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(2, 'App\\Models\\Cook', 1),
(4, 'App\\Models\\Customers', 1),
(7, 'App\\Models\\Staff', 1),
(1, 'App\\Models\\User', 1),
(5, 'App\\Models\\User', 2),
(5, 'App\\Models\\User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE `modules` (
  `id` bigint UNSIGNED NOT NULL,
  `module_id` int DEFAULT NULL,
  `module_name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(180) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `guard_name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'api',
  `status` int NOT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modules`
--

INSERT INTO `modules` (`id`, `module_id`, `module_name`, `module_code`, `description`, `guard_name`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Common Status', 'MT01', 'Common Status', 'api', 1, 1, 1, NULL, NULL),
(2, 1, 'Active', 'CS01', 'Active', 'api', 1, 1, 1, NULL, NULL),
(3, 1, 'Inactive', 'CS02', 'Inactive', 'api', 1, 1, 1, NULL, NULL),
(4, NULL, 'Menu Status', 'MT02', 'Menu Status', 'api', 1, 1, 1, NULL, NULL),
(5, 4, 'Hold', 'MS01', 'Menu initial status', 'api', 1, 1, 1, NULL, NULL),
(6, 4, 'Approved', 'MS02', 'Approved status by admin', 'api', 1, 1, 1, NULL, NULL),
(7, 4, 'Delete', 'MS03', 'Menu Delete', 'api', 1, 1, 1, NULL, NULL),
(8, 4, 'Off', 'MS04', 'Cook can able to turn off the menu', 'api', 1, 1, 1, NULL, NULL),
(9, NULL, 'Gender', 'MT03', 'Gender Main Module', 'api', 1, 1, 1, NULL, NULL),
(10, 9, 'Male', 'GS01', 'Male - Gender', 'api', 1, 1, 1, NULL, NULL),
(11, 9, 'Female', 'GS02', 'Female - Gender', 'api', 1, 1, 1, NULL, NULL),
(12, 9, 'Transgender', 'GS03', 'Gender - Transgender', 'api', 1, 1, 1, NULL, NULL),
(13, NULL, 'Food Type', 'MT04', 'Food Category', 'api', 1, 1, 1, NULL, NULL),
(14, 13, 'Veg', 'FT01', 'Veg', 'api', 1, 1, 1, NULL, NULL),
(15, 13, 'Non-Veg', 'FT02', 'Non Veg', 'api', 1, 1, 1, NULL, NULL),
(16, NULL, 'Order Status', 'MT05', 'Maintain Order Status', 'api', 1, 1, 1, NULL, NULL),
(17, 16, 'Success', 'OS01', 'Initial Status for Order create', 'api', 1, 1, 1, NULL, NULL),
(21, 16, 'Progress', 'OS02', 'Cook process the Order Status', 'api', 1, 1, 1, NULL, NULL),
(22, 16, 'Canceled', 'OS03', 'Cancel Order', 'api', 1, 1, 1, NULL, NULL),
(23, 16, 'Delivered', 'OS04', 'End point of Order', 'api', 1, 1, 1, NULL, NULL),
(24, NULL, 'Festival Offer', 'MT06', 'Offer Discounts and Counpons ', 'api', 1, 1, 1, NULL, NULL),
(25, 24, 'By Week', 'FO01', '7', 'api', 1, 1, 1, NULL, NULL),
(26, 24, 'Per Day', 'FO02', '1', 'api', 1, 1, 1, NULL, NULL),
(27, 24, 'Full Month', 'FO03', '28', 'api', 1, 1, 1, NULL, NULL),
(28, NULL, 'Payment Method', 'MT07', 'Method of payment', 'api', 1, 1, 1, NULL, NULL),
(29, 28, 'UPI', 'PM01', NULL, 'api', 1, 1, 1, NULL, NULL),
(30, 28, 'Credit Card', 'PM02', NULL, 'api', 1, 1, 1, NULL, NULL),
(31, 28, 'Debit Card', 'PM03', NULL, 'api', 1, 1, 1, NULL, NULL),
(32, 28, 'Cash', 'PM04', NULL, 'api', 1, 1, 1, NULL, NULL),
(33, NULL, 'Payment Status', 'MT08', NULL, 'api', 1, 1, 1, NULL, NULL),
(34, 33, 'Initiated', 'PS01', NULL, 'api', 1, 1, 1, NULL, NULL),
(35, 33, 'Failed', 'PS02', NULL, 'api', 1, 1, 1, NULL, NULL),
(36, 33, 'Success', 'PS03', NULL, 'api', 1, 1, 1, NULL, NULL),
(37, NULL, 'Account Type', 'MT08', NULL, 'api', 1, 1, 1, NULL, NULL),
(38, 37, 'Savings Account', 'AT01', NULL, 'api', 1, 1, 1, NULL, NULL),
(39, 37, 'Current Account', 'AT02', NULL, 'api', 1, 1, 1, NULL, NULL),
(40, 37, 'Fixed Deposit Account', 'AT03', NULL, 'api', 1, 1, 1, NULL, NULL),
(41, 37, 'Recurring Deposit Account', 'AT04', NULL, 'api', 1, 1, 1, NULL, NULL),
(42, 37, 'NRI Account', 'AT05', NULL, 'api', 1, 1, 1, NULL, NULL),
(43, 37, 'Demat Account', 'AT06', NULL, 'api', 1, 1, 1, NULL, NULL),
(44, 37, 'Salary Account', 'AT07', NULL, 'api', 1, 1, 1, NULL, NULL),
(45, 37, 'Joint Account', 'AT08', NULL, 'api', 1, 1, 1, NULL, NULL),
(46, 37, 'Senior Citizen Account', 'AT09', NULL, 'api', 1, 1, 1, NULL, NULL),
(47, 37, 'Women\'s Account', 'AT10', NULL, 'api', 1, 1, 1, NULL, NULL),
(48, 37, 'Minor\'s Account', 'AT11', NULL, 'api', 1, 1, 1, NULL, NULL),
(49, 37, 'Pension Account', 'AT12', NULL, 'api', 1, 1, 1, NULL, NULL),
(50, 37, 'Cash Credit Account', 'AT13', NULL, 'api', 1, 1, 1, NULL, NULL),
(51, 37, 'Overdraft Account', 'AT14', NULL, 'api', 1, 1, 1, NULL, NULL),
(52, 37, 'Loan Account', 'AT15', NULL, 'api', 1, 1, 1, NULL, NULL),
(53, NULL, 'Time Slots', 'MT09', NULL, 'api', 1, 1, 1, NULL, NULL),
(54, 53, '05:00-11:59', 'TS01', NULL, 'api', 1, 1, 1, NULL, NULL),
(55, 53, '12:00-15:59', 'TS02', NULL, 'api', 1, 1, 1, NULL, NULL),
(56, 53, '16:00-19:59', 'TS03', NULL, 'api', 1, 1, 1, NULL, NULL),
(57, 53, '20:00-04:59', 'TS04', NULL, 'api', 1, 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint UNSIGNED NOT NULL,
  `order_no` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_id` int NOT NULL,
  `vendor_id` int NOT NULL,
  `address_id` int DEFAULT NULL,
  `rider_id` int DEFAULT NULL,
  `price` decimal(8,2) NOT NULL,
  `discount` int DEFAULT NULL,
  `order_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `rider_picked_at` date DEFAULT NULL,
  `cook_deliver_at` date DEFAULT NULL,
  `rider_deliver_at` date DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longtitude` decimal(11,6) DEFAULT NULL,
  `status` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `order_no`, `customer_id`, `vendor_id`, `address_id`, `rider_id`, `price`, `discount`, `order_at`, `rider_picked_at`, `cook_deliver_at`, `rider_deliver_at`, `latitude`, `longtitude`, `status`, `created_at`, `updated_at`) VALUES
(1, 'HOC00001', 1, 1, 1, NULL, 50.00, 0, '2023-05-08 19:45:47', NULL, NULL, NULL, 11.03622508, 77.015259, 17, '2023-05-08 14:15:47', '2023-05-08 14:15:47');

-- --------------------------------------------------------

--
-- Table structure for table `order_details`
--

CREATE TABLE `order_details` (
  `id` bigint UNSIGNED NOT NULL,
  `order_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `feedback` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ratings` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_details`
--

INSERT INTO `order_details` (`id`, `order_id`, `menu_id`, `feedback`, `ratings`, `created_at`, `updated_at`) VALUES
(1, 1, 1, NULL, NULL, '2023-05-08 14:15:47', '2023-05-08 14:15:47');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `payment_method` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_status` int NOT NULL,
  `transaction_id` varchar(50) DEFAULT NULL,
  `reference_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pre_bookings`
--

CREATE TABLE `pre_bookings` (
  `id` int NOT NULL,
  `booking_date` datetime NOT NULL,
  `address_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `items` int NOT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pre_bookings`
--

INSERT INTO `pre_bookings` (`id`, `booking_date`, `address_id`, `customer_id`, `price`, `items`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(2, '2023-05-08 15:05:01', 1, 1, 100.00, 3, 11.036225082729, 77.015259494287, '2023-05-08 09:52:10', '2023-05-08 09:52:10');

-- --------------------------------------------------------

--
-- Table structure for table `pre_booking_details`
--

CREATE TABLE `pre_booking_details` (
  `id` int NOT NULL,
  `booking_id` int NOT NULL,
  `menu_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pre_booking_details`
--

INSERT INTO `pre_booking_details` (`id`, `booking_id`, `menu_id`) VALUES
(1, 2, 3),
(2, 2, 5);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `vendor_id` int NOT NULL,
  `image` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `units` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` int NOT NULL,
  `rating` bigint NOT NULL DEFAULT '0',
  `ucount` bigint NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `vendor_id`, `image`, `units`, `price`, `status`, `rating`, `ucount`, `created_at`, `updated_at`) VALUES
(1, 'Idly Poti', 'Idly Poti', 1, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTobhPjaeg4qF-sWYCcH3VQYGRy63zlOATsKbNt1n9yrhOsKnmcOJkREGQV61HXn4WJMeE&usqp=CAU', '1 kg', 100.00, 2, 0, 0, '2023-05-07 05:05:24', '2023-05-07 19:01:57'),
(3, 'Idly Poti', 'Idly Poti', 1, 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTobhPjaeg4qF-sWYCcH3VQYGRy63zlOATsKbNt1n9yrhOsKnmcOJkREGQV61HXn4WJMeE&usqp=CAU', '1 kg', 100.00, 2, 0, 0, '2023-05-07 13:17:49', '2023-05-07 13:17:49');

-- --------------------------------------------------------

--
-- Table structure for table `riders`
--

CREATE TABLE `riders` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `gender` int DEFAULT NULL,
  `address_id` int DEFAULT NULL,
  `bank_id` int DEFAULT NULL,
  `vehicle_id` int DEFAULT NULL,
  `status` int NOT NULL DEFAULT '2',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rider_activities`
--

CREATE TABLE `rider_activities` (
  `id` bigint UNSIGNED NOT NULL,
  `rider_id` int NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date DEFAULT NULL,
  `order_count` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super-admin', 'admin', '2023-04-01 16:29:56', '2023-04-01 16:29:56'),
(2, 'cook', 'cook', '2023-04-01 16:29:56', '2023-04-01 16:29:56'),
(3, 'rider', 'rider', '2023-04-01 16:29:56', '2023-04-01 16:29:56'),
(4, 'customer', 'customer', '2023-04-01 16:29:56', '2023-04-01 16:29:56'),
(5, 'admin', 'admin', '2023-04-17 16:38:02', '2023-04-17 16:38:02'),
(6, 'vendor-admin', 'cook', NULL, NULL),
(7, 'executive', 'cook', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int NOT NULL,
  `name` varchar(60) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` bigint NOT NULL,
  `password` varchar(250) COLLATE utf8mb4_general_ci NOT NULL,
  `vendor_id` int NOT NULL,
  `status` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `email`, `mobile`, `password`, `vendor_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Siva', 'siva@gmail.com', 7845123690, '$2y$10$37wu9t6ITBG3zlzwJB0zWu2ldNSuWWSvmbXCsX6Nr8u8NH8jTWRHW', 1, 2, '2023-05-04 13:12:54', '2023-05-04 13:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(60) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `password`, `created_at`, `updated_at`) VALUES
(1, 'Ramanathan', NULL, '7092462701', '$2y$10$OEe2gJpn43aVAat3.mc/zed8fIXo.dO8fySZtHbUnXg3VhzbNn6RK', '2023-04-17 11:18:08', '2023-04-17 11:18:08'),
(2, 'Karthick', NULL, '7598825487', '$2y$10$rYzxIQSkfDE.TY0ab0I8dOBhadqu20UUUhUaxGV53czX4febRx12G', '2023-04-17 11:43:18', '2023-04-17 11:43:18'),
(3, 'Ramanathan', 'ramanathan@gmail.com', '7092462706', '$2y$10$9JS9gjlhy78V3BWG1.yKJuPS1TDhni/.cBsEgSgoRnxvJn6X1zAWC', '2023-05-08 11:07:46', '2023-05-08 11:07:46');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `id` bigint UNSIGNED NOT NULL,
  `reg_no` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` bigint NOT NULL,
  `insurance_number` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int NOT NULL,
  `created_by` int NOT NULL,
  `updated_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int NOT NULL,
  `name` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `mobile` bigint NOT NULL,
  `gst_no` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address_id` int DEFAULT NULL,
  `bank_id` int NOT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL,
  `subscription` int DEFAULT NULL,
  `subscription_expire_at` datetime DEFAULT NULL,
  `status` int NOT NULL DEFAULT '2',
  `rating` bigint NOT NULL DEFAULT '0',
  `ucount` bigint NOT NULL DEFAULT '0',
  `created_by` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `email`, `mobile`, `gst_no`, `address_id`, `bank_id`, `latitude`, `longitude`, `subscription`, `subscription_expire_at`, `status`, `rating`, `ucount`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Ramanathan', 'ramanathan@gmail.com', 7092462701, 'GSTIN123456789', 3, 3, 11.036225082728693, 77.01525949428739, NULL, NULL, 2, 0, 0, 1, '2023-05-02 11:44:31', '2023-05-07 18:41:32');

-- --------------------------------------------------------

--
-- Table structure for table `verification_code`
--

CREATE TABLE `verification_code` (
  `id` bigint UNSIGNED NOT NULL,
  `mobile` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `otp` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expired_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `verification_code`
--

INSERT INTO `verification_code` (`id`, `mobile`, `otp`, `guard`, `expired_at`, `created_at`, `updated_at`) VALUES
(1, '7092462701', '782374', 'admin', '2023-04-26 18:54:17', '2023-04-26 13:19:17', '2023-04-26 13:19:17'),
(2, '7598825487', '170885', 'admin', '2023-04-26 18:55:50', '2023-04-26 13:20:50', '2023-04-26 13:20:50'),
(3, '7092462701', '701799', 'admin', '2023-05-01 16:15:13', '2023-05-01 10:40:13', '2023-05-01 10:40:13'),
(4, '7092462701', '975892', 'admin', '2023-05-02 14:21:54', '2023-05-02 08:46:54', '2023-05-02 08:46:54'),
(5, '7092462701', '796183', 'customer', '2023-05-07 04:41:32', '2023-05-06 23:06:32', '2023-05-06 23:06:32'),
(6, '7092462701', '102577', 'customer', '2023-05-07 04:57:55', '2023-05-06 23:22:55', '2023-05-06 23:22:55'),
(7, '7092462701', '608384', 'customer', '2023-05-08 16:47:29', '2023-05-08 11:12:29', '2023-05-08 11:12:29');

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

CREATE TABLE `wishlists` (
  `id` int NOT NULL,
  `type` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_id` int NOT NULL,
  `menu_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `type`, `customer_id`, `menu_id`) VALUES
(2, 'menu', 1, 5),
(3, 'menu', 1, 3),
(4, 'vendor', 1, 1),
(5, 'product', 1, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `address`
--
ALTER TABLE `address`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories_has_slot`
--
ALTER TABLE `categories_has_slot`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cooks`
--
ALTER TABLE `cooks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cooks_mobile_unique` (`mobile`),
  ADD UNIQUE KEY `cooks_email_unique` (`email`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `customers_mobile_unique` (`mobile`),
  ADD UNIQUE KEY `customers_email_unique` (`email`);

--
-- Indexes for table `discounts`
--
ALTER TABLE `discounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_available_days`
--
ALTER TABLE `menu_available_days`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_has_ingredients`
--
ALTER TABLE `menu_has_ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `modules`
--
ALTER TABLE `modules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_details`
--
ALTER TABLE `order_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `pre_bookings`
--
ALTER TABLE `pre_bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pre_booking_details`
--
ALTER TABLE `pre_booking_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riders`
--
ALTER TABLE `riders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `riders_mobile_unique` (`mobile`),
  ADD UNIQUE KEY `riders_email_unique` (`email`);

--
-- Indexes for table `rider_activities`
--
ALTER TABLE `rider_activities`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_mobile_unique` (`mobile`),
  ADD UNIQUE KEY `users_email_unique` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `verification_code`
--
ALTER TABLE `verification_code`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `address`
--
ALTER TABLE `address`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories_has_slot`
--
ALTER TABLE `categories_has_slot`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `cooks`
--
ALTER TABLE `cooks`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `discounts`
--
ALTER TABLE `discounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `menus`
--
ALTER TABLE `menus`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `menu_available_days`
--
ALTER TABLE `menu_available_days`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `menu_has_ingredients`
--
ALTER TABLE `menu_has_ingredients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `modules`
--
ALTER TABLE `modules`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_details`
--
ALTER TABLE `order_details`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pre_bookings`
--
ALTER TABLE `pre_bookings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pre_booking_details`
--
ALTER TABLE `pre_booking_details`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `riders`
--
ALTER TABLE `riders`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rider_activities`
--
ALTER TABLE `rider_activities`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `verification_code`
--
ALTER TABLE `verification_code`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `wishlists`
--
ALTER TABLE `wishlists`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
