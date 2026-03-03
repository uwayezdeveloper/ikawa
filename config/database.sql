-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Feb 26, 2026 at 09:08 AM
-- Server version: 11.4.7-MariaDB-cll-lve
-- PHP Version: 8.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tabrw_gihangacoffee`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `location_type_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `payment_mode_id` int(11) NOT NULL,
  `account_name` varchar(150) NOT NULL,
  `account_number` varchar(100) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `location_type_id`, `location_id`, `payment_mode_id`, `account_name`, `account_number`, `balance`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1, 'Bank of Kigali', '123456', 3333320.00, 'active', '2026-02-05 12:34:32', '2026-02-25 14:15:00'),
(2, 3, 1, 1, 'Cash', 'Cash', 6.00, 'active', '2026-02-05 12:35:08', '2026-02-23 09:32:05'),
(3, 1, 2, 2, 'Bank of Kigali', '1234567', 0.00, 'active', '2026-02-05 12:47:30', '2026-02-24 09:25:01'),
(4, 1, 2, 1, 'Cash', 'Cash', 0.00, 'active', '2026-02-05 12:47:44', '2026-02-24 09:47:57'),
(5, 1, 1, 7, 'ncba kinazi', 'nbca12345', 10510.00, 'active', '2026-02-13 13:44:31', '2026-02-13 13:49:50');

-- --------------------------------------------------------

--
-- Table structure for table `account_transactions`
--

CREATE TABLE `account_transactions` (
  `id` int(11) NOT NULL,
  `transaction_number` varchar(50) NOT NULL,
  `account_id` int(11) NOT NULL,
  `transaction_type` enum('credit','debit') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance_before` decimal(15,2) NOT NULL,
  `balance_after` decimal(15,2) NOT NULL,
  `reference_type` varchar(50) NOT NULL COMMENT 'supplier_advance, purchase, sale, transfer, adjustment',
  `reference_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_transactions`
--

INSERT INTO `account_transactions` (`id`, `transaction_number`, `account_id`, `transaction_type`, `amount`, `balance_before`, `balance_after`, `reference_type`, `reference_id`, `description`, `transaction_date`, `created_by`, `created_at`) VALUES
(2, 'TXN-202602-0001', 3, 'debit', 3.00, 3.00, 0.00, 'stock_receive', 1, 'Stock Payment: 556666.0000 units to supplier', '2026-02-24', 1, '2026-02-24 09:25:01'),
(3, 'TXN-202602-0002', 4, 'debit', 9.00, 96.00, 87.00, 'stock_receive', 3, 'Stock Payment: 3.0000 units to supplier', '2026-02-24', 1, '2026-02-24 09:27:15'),
(4, 'TXN-202602-0003', 4, 'debit', 87.00, 87.00, 0.00, 'stock_receive', 4, 'Stock Payment: 100.0000 units to supplier', '2026-02-24', 1, '2026-02-24 09:47:57');

-- --------------------------------------------------------

--
-- Table structure for table `category_types`
--

CREATE TABLE `category_types` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category_types`
--

INSERT INTO `category_types` (`id`, `category_id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 4, 'Non Floatant', 'Non Floatant', 'active', '2026-02-05 08:05:51', '2026-02-05 08:05:51'),
(2, 4, 'Floatant', 'Floatant', 'active', '2026-02-05 08:06:05', '2026-02-05 08:06:05'),
(3, 3, 'A', 'A', 'active', '2026-02-05 08:06:26', '2026-02-05 08:06:26'),
(4, 3, 'B', 'B', 'active', '2026-02-05 08:06:38', '2026-02-05 08:06:38');

-- --------------------------------------------------------

--
-- Table structure for table `category_type_units`
--

CREATE TABLE `category_type_units` (
  `id` int(11) NOT NULL,
  `category_type_id` int(11) NOT NULL,
  `measurement_unit_id` int(11) NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `category_type_units`
--

INSERT INTO `category_type_units` (`id`, `category_type_id`, `measurement_unit_id`, `is_default`, `created_at`) VALUES
(1, 2, 3, 1, '2026-02-24 09:00:37'),
(2, 1, 3, 1, '2026-02-24 09:00:54'),
(3, 1, 4, 0, '2026-02-24 09:25:01'),
(4, 3, 3, 1, '2026-02-24 09:55:31'),
(5, 4, 3, 1, '2026-02-24 09:55:44');

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `client_type` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `tin_number` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `name`, `client_type`, `phone`, `email`, `address`, `contact_person`, `contact_phone`, `tin_number`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 'TITAN TECH HUB Ltd', 'company', '+250785114760', 'desengejeanmaurice@gmail.com', 'kigali-rwanda\r\n\r\n-', 'uwimana christian', '0784103864', NULL, NULL, 'active', '2026-02-12 10:20:20', '2026-02-12 10:20:20'),
(2, 'UWAYEZU Jean Felix', 'company', '+250789736352', 'wedplanner@gmail.com', 'Nyamirambo\r\nKinyinya', 'UWAYEZU Jean Felix', '0789736352', NULL, 'werfghj', 'active', '2026-02-18 06:38:28', '2026-02-18 06:38:28');

-- --------------------------------------------------------

--
-- Table structure for table `client_identifier_values`
--

CREATE TABLE `client_identifier_values` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `identifier_id` int(11) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_identifier_values`
--

INSERT INTO `client_identifier_values` (`id`, `client_id`, `identifier_id`, `value`, `created_at`, `updated_at`) VALUES
(1, 2, 3, 'we3456', '2026-02-18 06:38:29', '2026-02-18 06:38:29');

-- --------------------------------------------------------

--
-- Table structure for table `client_types`
--

CREATE TABLE `client_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `identifier` varchar(50) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_types`
--

INSERT INTO `client_types` (`id`, `name`, `description`, `identifier`, `status`, `created_at`, `updated_at`) VALUES
(6, 'Individual', '', 'individual', 'active', '2026-02-12 10:07:23', '2026-02-12 10:07:23'),
(7, 'Company', '', 'company', 'active', '2026-02-12 10:07:33', '2026-02-12 10:08:09');

-- --------------------------------------------------------

--
-- Table structure for table `client_type_identifiers`
--

CREATE TABLE `client_type_identifiers` (
  `id` int(11) NOT NULL,
  `client_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `client_type_identifiers`
--

INSERT INTO `client_type_identifiers` (`id`, `client_type_id`, `name`, `is_required`, `status`, `created_at`, `updated_at`) VALUES
(3, 7, 'Tin Number', 0, 'active', '2026-02-12 10:07:43', '2026-02-12 10:07:43'),
(4, 6, 'Phone Number', 0, 'active', '2026-02-12 10:08:00', '2026-02-12 10:08:00');

-- --------------------------------------------------------

--
-- Table structure for table `company`
--

CREATE TABLE `company` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `company`
--

INSERT INTO `company` (`id`, `full_name`, `short_name`, `email`, `phone`, `address`, `logo`, `created_at`, `updated_at`) VALUES
(1, 'Gihanga Coffee', 'GC', 'info@gihangacoffee.com', '+250 784103864', 'Kigali, Rwanda', 'logo_1770215571.webp', '2026-02-04 14:14:57', '2026-02-04 14:32:51');

-- --------------------------------------------------------

--
-- Table structure for table `jwt_tokens`
--

CREATE TABLE `jwt_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token_hash` varchar(64) NOT NULL,
  `type` enum('access','refresh') DEFAULT 'access',
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jwt_tokens`
--

INSERT INTO `jwt_tokens` (`id`, `user_id`, `token_hash`, `type`, `expires_at`, `revoked_at`, `created_at`) VALUES
(1, 1, '3dacd5b29cf6b79252c3a22b1f63e67c8146a2338d944280d6afd62e7a97e75d', 'refresh', '2026-02-11 12:07:45', NULL, '2026-02-04 12:07:45'),
(2, 1, '81c067a674c82e5e5e86b0e645d67373ccf41f705c8f4c39343915cd2283d6f3', 'refresh', '2026-02-11 12:38:30', NULL, '2026-02-04 12:38:30'),
(3, 1, '363882877b35ec827723b74591553ae9afa26f44ed275081a1c9d62281b3effc', 'refresh', '2026-02-11 13:04:57', NULL, '2026-02-04 13:04:57'),
(4, 1, '7d6bef08f6646453a22f8e0b15eba9386ae85368b6c425acf7ac3c2610898cb1', 'refresh', '2026-02-11 13:09:26', NULL, '2026-02-04 13:09:26'),
(5, 1, '079dc93b05f94322e40c8b57a9bd33cc37c6d2422563e476f347e53ffd13b9e2', 'refresh', '2026-02-11 13:13:18', NULL, '2026-02-04 13:13:18'),
(6, 1, '427e314cbf8e5e55473d89a38a76cfbdd94d16383884fa5cd3d8412f74e2e879', 'refresh', '2026-02-11 13:22:21', NULL, '2026-02-04 13:22:21'),
(7, 1, '9508f0c31ab6db4613f5f18a0777216e64bbe83147a30a0923a0f6632f94b68b', 'refresh', '2026-02-11 13:24:45', NULL, '2026-02-04 13:24:45'),
(8, 1, 'b12b7b205cde1c9fdfc83a574dc64b7b1377ad664d69f7d96af2389c42b598c1', 'refresh', '2026-02-11 13:27:10', NULL, '2026-02-04 13:27:10'),
(9, 1, 'fc1a1fdbf6761415dc6e91e05dbf29080f3f52125b60abe6539608d80a5cc881', 'refresh', '2026-02-11 13:29:25', NULL, '2026-02-04 13:29:25'),
(10, 1, 'e1bcc0082ab701bdd83fe02634e725fa395d150d741662aed22c58130a051985', 'refresh', '2026-02-12 07:04:10', NULL, '2026-02-05 07:04:10'),
(11, 1, 'b23fee848709b594cc2368fcb57c37a8fb76c1d9f7ef4bcded4437b790b39b8d', 'refresh', '2026-02-12 11:29:45', NULL, '2026-02-05 11:29:45'),
(12, 2, '5792442463517324efb5b1a0cb2b5e7693f30a6eca1877e67cf3aaba17dc6083', 'refresh', '2026-02-12 11:30:09', NULL, '2026-02-05 11:30:09'),
(13, 1, 'cbc003e3afa219618a5ace10b4d23489fdf0ea5edf314facd9b47d5337a86d19', 'refresh', '2026-02-12 12:22:37', NULL, '2026-02-05 12:22:37'),
(14, 1, 'c86299dca42da83434a29ddb4c6257ce1048730c1e0c444b9747934a9560c1b2', 'refresh', '2026-02-12 14:09:45', NULL, '2026-02-05 13:09:45'),
(15, 1, '15fd59d495c617e74de06c7e727a0d4cf474959a0e30b42b56c46b59398fd7d3', 'refresh', '2026-02-12 14:10:32', NULL, '2026-02-05 13:10:32'),
(16, 1, 'b0b1c0f9879a38dc8942368562d5563993309152efdff8e30dbb97a7967be06d', 'refresh', '2026-02-12 14:18:39', NULL, '2026-02-05 13:18:39'),
(17, 1, '69dc579ab67f063be20a595845f3d82e70e136e8d03ce556569397f196c1f89e', 'refresh', '2026-02-14 08:51:49', NULL, '2026-02-07 07:51:49'),
(18, 1, '2786de3356f0b4afa9b9f7c4557622829fa8c31c9181a8c72c72aa9927b6f3b3', 'refresh', '2026-02-14 09:36:54', NULL, '2026-02-07 08:36:54'),
(19, 1, 'd87a67b62bde4caab0dcdd0f95a11477993ab714b7883f87cd862b8d69b521a0', 'refresh', '2026-02-14 13:06:18', NULL, '2026-02-07 12:06:18'),
(20, 1, '4d845d8b0d454366706d9352240588a24a2d1f38297898ce82b723f561439c2c', 'refresh', '2026-02-16 07:37:04', NULL, '2026-02-09 06:37:04'),
(21, 1, 'f8425776a5ee101e202fba45479d279d6538994d8b45460ce59c456cdfda8e03', 'refresh', '2026-02-16 07:38:28', NULL, '2026-02-09 06:38:28'),
(22, 1, '2315b9b3924165f431cb7b52447204ce8c52df6bb31fa916ccf14dc965e391ea', 'refresh', '2026-02-18 08:59:59', NULL, '2026-02-11 07:59:59'),
(23, 1, '88e5ed9ad82b41171ce6c7269fb034ffeb735b459c9c66dc8f4fc9e479a454c4', 'refresh', '2026-02-20 09:00:34', NULL, '2026-02-13 08:00:34'),
(24, 1, '54cec48c9e7f6d31bea6b8707e9208d35b291c48187c3084ef2dc057fe68d6b6', 'refresh', '2026-02-20 10:25:21', NULL, '2026-02-13 09:25:21'),
(25, 1, '6149480a4bff7d861925fa13d21e2e65328ff136624169b0116a6564f2779e9f', 'refresh', '2026-02-20 13:04:58', NULL, '2026-02-13 12:04:58'),
(26, 1, '6fed53dfc69c26e6eeb6b2539196e444ae341f721bc149c244740a5ab677120b', 'refresh', '2026-02-20 13:08:19', NULL, '2026-02-13 12:08:19'),
(27, 1, '1320d58ae9fa91232c49420ea437f86fab2ed164b10373000ca9134e52629466', 'refresh', '2026-02-20 14:39:07', NULL, '2026-02-13 13:39:07'),
(28, 1, '37141707e72063e0848637fea5f9c091d69d7a1521180586b9a9fc33f01d22e1', 'refresh', '2026-02-20 14:40:36', NULL, '2026-02-13 13:40:36'),
(29, 1, 'fa46dde462753de40c9964bfcde2d584a8c8a332b9665aa12d3aaf1de993ad90', 'refresh', '2026-02-21 08:56:46', NULL, '2026-02-14 07:56:46'),
(30, 1, '7f050c3202dfe3c0f5b75fea89b00b3c3d40a96aa73bd6e88ff320da66ae6480', 'refresh', '2026-02-21 08:57:02', NULL, '2026-02-14 07:57:02'),
(31, 1, 'eb405cd984cd6d4487e2bffd79d096c6429710dfde9111335d781128d4431da3', 'refresh', '2026-02-21 08:58:23', NULL, '2026-02-14 07:58:23'),
(32, 1, '3f9caa9e3673abef43b17ff7eb44e0e7dc3663dd80e14572fd8832b1072ebafc', 'refresh', '2026-02-21 09:04:48', NULL, '2026-02-14 08:04:48'),
(33, 1, '5ad429ee4de6007ac76dfd33ec3843b9ae3d431011874e271c3b5bb1bf2bb0ad', 'refresh', '2026-02-21 11:59:27', NULL, '2026-02-14 10:59:27'),
(34, 1, '71e962b032ccf6f2cb2bc0e30f17fcd1638e67be2ac94e2aa4c477bb44510af6', 'refresh', '2026-02-21 12:52:34', NULL, '2026-02-14 11:52:34'),
(35, 1, '4bac61d149b26fcd247116a4d055a03d6e80755f4035aa1c101d70c84af7e390', 'refresh', '2026-02-21 13:01:56', NULL, '2026-02-14 12:01:56'),
(36, 1, 'd31f892e92e6418829db08c5f39689a9d20d6663ffa404e0e4dbad508a68ea6f', 'refresh', '2026-02-21 14:46:26', NULL, '2026-02-14 13:46:26'),
(37, 1, '571101904e200adc1d38d81f24db7782cd85ee76ca6e6a768fba88eed853cf69', 'refresh', '2026-02-21 15:08:28', NULL, '2026-02-14 14:08:28'),
(38, 1, '4b99719b0deb0f3d3e7aa0631ed128a0d0e05bf36b1507a88d8c335e7ad48e1c', 'refresh', '2026-02-21 15:34:31', NULL, '2026-02-14 14:34:31'),
(39, 1, '5df040da675d323a3ec46485f51be85fa7008f722cf228eea62fe1e3813e535a', 'refresh', '2026-02-23 07:15:04', NULL, '2026-02-16 06:15:04'),
(40, 1, 'c05239d7cd57cbf76e38c5be176a54afe31b8981ac0764afad6d080c9ecf7ba5', 'refresh', '2026-02-23 08:15:58', NULL, '2026-02-16 07:15:58'),
(41, 3, '33c974f0b436d5d28b8c2cea8aa3b9dcbd07484976f431441e4a79cbfd56a23e', 'refresh', '2026-02-23 08:16:57', NULL, '2026-02-16 07:16:57'),
(42, 1, '84c67d02b9291a4a5d425c9ee3b6b2384c4ba251208297d70edf701618302089', 'refresh', '2026-02-23 08:27:34', NULL, '2026-02-16 07:27:34'),
(43, 1, '6c016f2a70673970738d91eaa246d1e6cdf89dd0fb3a388492531a05cd00c5ff', 'refresh', '2026-02-23 08:39:18', NULL, '2026-02-16 07:39:18'),
(44, 1, '001a1d2aee1ec3fa9f124bc0346c35e819a55c537781e42945a57c73f322d6bc', 'refresh', '2026-02-23 10:35:08', NULL, '2026-02-16 09:35:08'),
(45, 1, '39b8a4a1ef361ab82bba6dd7dd8e562922453102e35bc8e3de8012d3fdf521f9', 'refresh', '2026-02-23 10:37:39', NULL, '2026-02-16 09:37:39'),
(46, 1, '3458d03b5152e1d8a4bd1d96f95c7317dcb8e8d7ecfb50b3cd83b316358951fb', 'refresh', '2026-02-23 11:20:39', NULL, '2026-02-16 10:20:39'),
(47, 1, '6ddf92449c2b852ee64aed62d80985ec3b9c4dfe8d35b1910c878ac7bdb1910c', 'refresh', '2026-02-23 11:24:01', NULL, '2026-02-16 10:24:01'),
(48, 1, '4371c02dc1e915391030d6cf14a8e555d096028e9758824eebd4c681910e8112', 'refresh', '2026-02-23 11:25:37', NULL, '2026-02-16 10:25:37'),
(49, 1, 'fc67d2950253f1760d91ecbdd9edb9c87ef946d01bd350815043211601631137', 'refresh', '2026-02-23 11:51:12', NULL, '2026-02-16 10:51:12'),
(50, 1, '20a31b7f9dbc190a9102dfeac5b1d70281f126c09dd3c0468e4afaaaa9f532da', 'refresh', '2026-02-23 13:37:23', NULL, '2026-02-16 12:37:23'),
(51, 1, '45ba3ca6dbdb4ee0285d4c7632e369ea6dcfba9fa766201de4c18745c19d75c9', 'refresh', '2026-02-23 14:32:44', NULL, '2026-02-16 13:32:44'),
(52, 1, 'e9b15b99888b15e919089cbdf6f014a859c8fbdcad30d9595a3acc0436386c1d', 'refresh', '2026-02-24 14:12:39', NULL, '2026-02-17 13:12:39'),
(53, 1, '5c31d4f778aed7b02d52b125522da9760b1618ba34254ec37f28330af0cab08d', 'refresh', '2026-02-24 14:59:28', NULL, '2026-02-17 13:59:28'),
(54, 1, '1118e5047a6dec1a22038bb64013885c56ff8682efd0d44d891c10bab38f5bf4', 'refresh', '2026-02-25 07:37:32', NULL, '2026-02-18 06:37:32'),
(55, 1, '887bb2d118018eef15c9280314c0df4f7edc1e9687a710481fc39ffec44d6dc9', 'refresh', '2026-02-25 09:45:33', NULL, '2026-02-18 08:45:33'),
(56, 1, '8c9b308a26ae5fde7adffa7af4d331dfbe3616d3a58a767c556fade84c4dd9de', 'refresh', '2026-02-25 10:00:13', NULL, '2026-02-18 09:00:13'),
(57, 1, 'cdcabe369b57648b98813197e7f4588fdde889cd383eeb6780fe269fe5664447', 'refresh', '2026-02-27 08:55:45', NULL, '2026-02-20 07:55:45'),
(58, 1, 'e9b6cfee44381c79fea04b7310e5ed6485877cd65c829edf8a66730dad156b5f', 'refresh', '2026-03-02 09:35:23', NULL, '2026-02-23 08:35:23'),
(59, 1, 'e22f533bb77634aeddd6563795556d9845c213da60da74d6cc26ed35b8fb35b7', 'refresh', '2026-03-02 10:28:43', NULL, '2026-02-23 09:28:43'),
(60, 1, '9667dfecc010a750df45c0d908610f227b913c8fae366de9496844aa499a7b25', 'refresh', '2026-03-02 13:10:51', NULL, '2026-02-23 12:10:51'),
(61, 1, '2cdb0b993a68a16a1b10e1a0cfe2f04128295678fed53e7e254b3e4438547f96', 'refresh', '2026-03-02 14:22:50', NULL, '2026-02-23 13:22:50'),
(62, 1, '89ea43837f1db723e23c32418cb3048a67df25414352d525b2d6eb077457af21', 'refresh', '2026-03-03 09:25:43', NULL, '2026-02-24 08:25:43'),
(63, 1, 'fcdaaa7ba86e33cecd2cc5384e5120e560f98f492b90c54f715341359baf2494', 'refresh', '2026-03-03 14:19:04', NULL, '2026-02-24 13:19:04'),
(64, 1, '4303e0e1947b4f037e81d6ba91d40572374f4bd18a3d283af947952902d821a9', 'refresh', '2026-03-04 08:12:53', NULL, '2026-02-25 07:12:53'),
(65, 3, 'ddc93cc5735301c95decd777de727592c822d1431d3a63699ff6d36c04ccdb11', 'refresh', '2026-03-04 09:55:41', NULL, '2026-02-25 08:55:41'),
(66, 1, '90a2acd6ad3d47dcb443df9cd8bfe1d4e8fb8af787328f6d97ddb594cd65dcdc', 'refresh', '2026-03-04 13:32:03', NULL, '2026-02-25 12:32:03'),
(67, 1, 'd3d38fda3c15c68d16bd216c17d2ed5325b9dcccac496d1bfdeffc9dc00a0848', 'refresh', '2026-03-04 15:11:27', NULL, '2026-02-25 14:11:27'),
(68, 1, '27f315c278cc78aa1f69e5f486a8cd1dc335c3a12507aa4797e383326d35da88', 'refresh', '2026-03-05 07:54:41', NULL, '2026-02-26 06:54:41');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `location_type_id` int(11) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`, `description`, `location_type_id`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Ruli cws', 'Ruli cws', 2, 'active', '2026-02-16 10:53:45', '2026-02-16 10:53:45'),
(2, 'gakenke cws', 'gakenke cws', 1, 'active', '2026-02-24 09:01:38', '2026-02-24 09:01:38');

-- --------------------------------------------------------

--
-- Table structure for table `location_types`
--

CREATE TABLE `location_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `location_types`
--

INSERT INTO `location_types` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Station', 'Station', 'active', '2026-02-04 14:38:16', '2026-02-04 14:53:31'),
(2, 'Warehouse', 'Warehouse', 'active', '2026-02-04 14:38:33', '2026-02-04 14:38:33'),
(3, 'HQ', 'HQ', 'active', '2026-02-04 14:38:33', '2026-02-04 14:38:33');

-- --------------------------------------------------------

--
-- Table structure for table `location_type_categories`
--

CREATE TABLE `location_type_categories` (
  `id` int(11) NOT NULL,
  `location_type_id` int(11) NOT NULL,
  `product_category_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `location_type_categories`
--

INSERT INTO `location_type_categories` (`id`, `location_type_id`, `product_category_id`, `created_at`) VALUES
(1, 1, 4, '2026-02-05 08:43:04'),
(2, 2, 2, '2026-02-05 08:43:15'),
(3, 2, 3, '2026-02-05 08:43:15'),
(4, 3, 3, '2026-02-13 12:10:34');

-- --------------------------------------------------------

--
-- Table structure for table `measurement_units`
--

CREATE TABLE `measurement_units` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(20) NOT NULL,
  `base_unit_id` int(11) DEFAULT NULL,
  `conversion_factor` decimal(20,10) DEFAULT 1.0000000000,
  `rank` int(11) DEFAULT 0,
  `step_threshold` decimal(20,2) DEFAULT 1000.00,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `measurement_units`
--

INSERT INTO `measurement_units` (`id`, `name`, `symbol`, `base_unit_id`, `conversion_factor`, `rank`, `step_threshold`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Gram', 'g', NULL, 1.0000000000, 2, 1000.00, 'Base unit for weight measurement', 'active', '2026-02-05 08:12:42', '2026-02-10 07:13:49'),
(2, 'Milligram', 'mg', 1, 0.0010000000, 1, 1000.00, '1 mg = 0.001 g', 'active', '2026-02-05 08:12:42', '2026-02-10 07:13:49'),
(3, 'Kilogram', 'kg', 1, 1000.0000000000, 3, 1000.00, '1 kg = 1000 g', 'active', '2026-02-05 08:12:42', '2026-02-12 12:27:10'),
(4, 'Ton', 't', 1, 1000000.0000000000, 4, NULL, '1 t = 1,000,000 g', 'active', '2026-02-05 08:12:42', '2026-02-10 07:13:49');

-- --------------------------------------------------------

--
-- Table structure for table `non_exploitable_types`
--

CREATE TABLE `non_exploitable_types` (
  `eploi_id` int(11) NOT NULL,
  `exploi_name` varchar(50) NOT NULL,
  `exploi_desc` varchar(50) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `non_exploitable_types`
--

INSERT INTO `non_exploitable_types` (`eploi_id`, `exploi_name`, `exploi_desc`, `status`) VALUES
(1, 'test non', 'edfvfvfv', 1),
(2, 'werg', 'erfghngh', 1),
(3, 'testserver', 'ertgh', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_modes`
--

CREATE TABLE `payment_modes` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_modes`
--

INSERT INTO `payment_modes` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Cash', 'Cash payment', 'active', '2026-02-05 12:19:39', '2026-02-05 12:19:39'),
(2, 'Bank Transfer', 'Bank transfer payment', 'active', '2026-02-05 12:19:39', '2026-02-05 12:19:39'),
(3, 'Mobile Money', 'Mobile money payment (MTN, Airtel)', 'active', '2026-02-05 12:19:39', '2026-02-05 12:19:39'),
(4, 'Cheque', 'Cheque payment', 'active', '2026-02-05 12:19:39', '2026-02-05 12:19:39'),
(5, 'Credit Card', 'Credit/Debit card payment', 'active', '2026-02-05 12:19:39', '2026-02-05 12:19:39'),
(7, 'bank kinazi', 'bank kinazi', 'active', '2026-02-13 13:42:18', '2026-02-13 13:42:18'),
(8, 'momo kinazi', 'momo kinazi', 'active', '2026-02-13 13:42:32', '2026-02-13 13:42:32');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `module` varchar(50) DEFAULT 'general',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `slug`, `description`, `module`, `status`, `created_at`, `updated_at`) VALUES
(1, 'View Dashboard', 'view-dashboard', 'Access to view dashboard', 'dashboard', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(2, 'View Users', 'view-users', 'Access to view users list', 'users', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(3, 'Create Users', 'create-users', 'Access to create new users', 'users', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(4, 'Edit Users', 'edit-users', 'Access to edit users', 'users', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(5, 'Delete Users', 'delete-users', 'Access to delete users', 'users', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(6, 'View Roles', 'view-roles', 'Access to view roles list', 'roles', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(7, 'Create Roles', 'create-roles', 'Access to create new roles', 'roles', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(8, 'Edit Roles', 'edit-roles', 'Access to edit roles', 'roles', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(9, 'Delete Roles', 'delete-roles', 'Access to delete roles', 'roles', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(10, 'View Permissions', 'view-permissions', 'Access to view permissions', 'permissions', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(11, 'Manage Permissions', 'manage-permissions', 'Access to manage role permissions', 'permissions', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(12, 'View Settings', 'view-settings', 'Access to view settings', 'settings', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(13, 'Manage Settings', 'manage-settings', 'Access to manage settings', 'settings', 'active', '2026-02-04 11:51:48', '2026-02-04 11:51:48'),
(20, 'View Company', 'view-company', 'Can view company settings', 'general', 'active', '2026-02-04 13:48:01', '2026-02-04 13:48:01'),
(21, 'Create Company', 'create-company', 'Can create company settings', 'general', 'active', '2026-02-04 13:48:01', '2026-02-04 13:48:01'),
(22, 'Edit Company', 'edit-company', 'Can edit company settings', 'general', 'active', '2026-02-04 13:48:01', '2026-02-04 13:48:01'),
(23, 'Delete Company', 'delete-company', 'Can delete company settings', 'general', 'active', '2026-02-04 13:48:01', '2026-02-04 13:48:01'),
(24, 'View Location Types', 'view-location-types', 'Can view location types', 'general', 'active', '2026-02-04 13:48:01', '2026-02-04 13:48:01'),
(25, 'Create Location Types', 'create-location-types', 'Can create location types', 'general', 'active', '2026-02-04 13:48:01', '2026-02-04 13:48:01'),
(26, 'Edit Location Types', 'edit-location-types', 'Can edit location types', 'general', 'active', '2026-02-04 13:48:01', '2026-02-04 13:48:01'),
(27, 'Delete Location Types', 'delete-location-types', 'Can delete location types', 'general', 'active', '2026-02-04 13:48:01', '2026-02-04 13:48:01'),
(28, 'View Locations', 'view-locations', 'Can view locations', 'general', 'active', '2026-02-04 13:57:39', '2026-02-04 13:57:39'),
(29, 'Create Locations', 'create-locations', 'Can create locations', 'general', 'active', '2026-02-04 13:57:39', '2026-02-04 13:57:39'),
(30, 'Edit Locations', 'edit-locations', 'Can edit locations', 'general', 'active', '2026-02-04 13:57:39', '2026-02-04 13:57:39'),
(31, 'Delete Locations', 'delete-locations', 'Can delete locations', 'general', 'active', '2026-02-04 13:57:39', '2026-02-04 13:57:39'),
(32, 'View Product Categories', 'view-product-categories', 'Can view product categories', 'general', 'active', '2026-02-05 06:47:44', '2026-02-05 06:47:44'),
(33, 'Create Product Categories', 'create-product-categories', 'Can create product categories', 'general', 'active', '2026-02-05 06:47:44', '2026-02-05 06:47:44'),
(34, 'Edit Product Categories', 'edit-product-categories', 'Can edit product categories', 'general', 'active', '2026-02-05 06:47:44', '2026-02-05 06:47:44'),
(35, 'Delete Product Categories', 'delete-product-categories', 'Can delete product categories', 'general', 'active', '2026-02-05 06:47:44', '2026-02-05 06:47:44'),
(40, 'view-category-types', 'view-category-types', 'View category types', 'products', 'active', '2026-02-05 06:59:30', '2026-02-05 06:59:30'),
(41, 'create-category-types', 'create-category-types', 'Create category types', 'products', 'active', '2026-02-05 06:59:30', '2026-02-05 06:59:30'),
(42, 'edit-category-types', 'edit-category-types', 'Edit category types', 'products', 'active', '2026-02-05 06:59:30', '2026-02-05 06:59:30'),
(43, 'delete-category-types', 'delete-category-types', 'Delete category types', 'products', 'active', '2026-02-05 06:59:30', '2026-02-05 06:59:30'),
(44, 'view-measurement-units', 'view-measurement-units', 'View measurement units', 'products', 'active', '2026-02-05 07:12:47', '2026-02-05 07:12:47'),
(45, 'create-measurement-units', 'create-measurement-units', 'Create measurement units', 'products', 'active', '2026-02-05 07:12:47', '2026-02-05 07:12:47'),
(46, 'edit-measurement-units', 'edit-measurement-units', 'Edit measurement units', 'products', 'active', '2026-02-05 07:12:47', '2026-02-05 07:12:47'),
(47, 'delete-measurement-units', 'delete-measurement-units', 'Delete measurement units', 'products', 'active', '2026-02-05 07:12:47', '2026-02-05 07:12:47'),
(48, 'view-type-unit-assignments', 'view-type-unit-assignments', 'View category type unit assignments', 'products', 'active', '2026-02-05 07:20:48', '2026-02-05 07:20:48'),
(49, 'manage-type-unit-assignments', 'manage-type-unit-assignments', 'Manage category type unit assignments', 'products', 'active', '2026-02-05 07:20:48', '2026-02-05 07:20:48'),
(50, 'view-location-categories', 'view-location-categories', 'View location type category assignments', 'settings', 'active', '2026-02-05 07:33:09', '2026-02-05 07:33:09'),
(51, 'manage-location-categories', 'manage-location-categories', 'Manage location type category assignments', 'settings', 'active', '2026-02-05 07:33:09', '2026-02-05 07:33:09'),
(56, 'view-supplier-types', 'view-supplier-types', 'View supplier types', 'suppliers', 'active', '2026-02-05 08:04:05', '2026-02-05 08:04:05'),
(57, 'create-supplier-types', 'create-supplier-types', 'Create supplier types', 'suppliers', 'active', '2026-02-05 08:04:05', '2026-02-05 08:04:05'),
(58, 'edit-supplier-types', 'edit-supplier-types', 'Edit supplier types', 'suppliers', 'active', '2026-02-05 08:04:05', '2026-02-05 08:04:05'),
(59, 'delete-supplier-types', 'delete-supplier-types', 'Delete supplier types', 'suppliers', 'active', '2026-02-05 08:04:05', '2026-02-05 08:04:05'),
(60, 'view-suppliers', 'view-suppliers', 'View suppliers', 'suppliers', 'active', '2026-02-05 08:19:00', '2026-02-05 08:19:00'),
(61, 'create-suppliers', 'create-suppliers', 'Create suppliers', 'suppliers', 'active', '2026-02-05 08:19:00', '2026-02-05 08:19:00'),
(62, 'edit-suppliers', 'edit-suppliers', 'Edit suppliers', 'suppliers', 'active', '2026-02-05 08:19:00', '2026-02-05 08:19:00'),
(63, 'delete-suppliers', 'delete-suppliers', 'Delete suppliers', 'suppliers', 'active', '2026-02-05 08:19:00', '2026-02-05 08:19:00'),
(64, 'View Payment Modes', 'view-payment-modes', 'Can view payment modes', 'finance', 'active', '2026-02-05 11:19:39', '2026-02-05 11:19:39'),
(65, 'Create Payment Modes', 'create-payment-modes', 'Can create payment modes', 'finance', 'active', '2026-02-05 11:19:39', '2026-02-05 11:19:39'),
(66, 'Edit Payment Modes', 'edit-payment-modes', 'Can edit payment modes', 'finance', 'active', '2026-02-05 11:19:39', '2026-02-05 11:19:39'),
(67, 'Delete Payment Modes', 'delete-payment-modes', 'Can delete payment modes', 'finance', 'active', '2026-02-05 11:19:39', '2026-02-05 11:19:39'),
(68, 'View Accounts', 'view-accounts', 'Can view accounts', 'finance', 'active', '2026-02-05 11:29:14', '2026-02-05 11:29:14'),
(69, 'Create Accounts', 'create-accounts', 'Can create accounts', 'finance', 'active', '2026-02-05 11:29:14', '2026-02-05 11:29:14'),
(70, 'Edit Accounts', 'edit-accounts', 'Can edit accounts', 'finance', 'active', '2026-02-05 11:29:14', '2026-02-05 11:29:14'),
(71, 'Delete Accounts', 'delete-accounts', 'Can delete accounts', 'finance', 'active', '2026-02-05 11:29:14', '2026-02-05 11:29:14'),
(72, 'View Supplier Records', 'view-supplier-records', 'View supplier purchase records', 'supplier-records', 'active', '2026-02-14 13:33:37', '2026-02-14 13:33:37'),
(73, 'Create Supplier Records', 'create-supplier-records', 'Create supplier purchase records', 'supplier-records', 'active', '2026-02-14 13:33:37', '2026-02-14 13:33:37'),
(74, 'Edit Supplier Records', 'edit-supplier-records', 'Edit supplier purchase records', 'supplier-records', 'active', '2026-02-14 13:33:37', '2026-02-14 13:33:37'),
(75, 'Delete Supplier Records', 'delete-supplier-records', 'Delete supplier purchase records', 'supplier-records', 'active', '2026-02-14 13:33:37', '2026-02-14 13:33:37'),
(76, 'View Supplier Advances', 'view-supplier-advances', 'View supplier advance payments', 'supplier-advances', 'active', '2026-02-14 13:33:37', '2026-02-14 13:33:37'),
(77, 'Create Supplier Advances', 'create-supplier-advances', 'Create supplier advance payments', 'supplier-advances', 'active', '2026-02-14 13:33:37', '2026-02-14 13:33:37'),
(78, 'Edit Supplier Advances', 'edit-supplier-advances', 'Edit supplier advance payments', 'supplier-advances', 'active', '2026-02-14 13:33:37', '2026-02-14 13:33:37'),
(79, 'Delete Supplier Advances', 'delete-supplier-advances', 'Delete supplier advance payments', 'supplier-advances', 'active', '2026-02-14 13:33:37', '2026-02-14 13:33:37'),
(80, 'Approve Supplier Advances', 'approve-supplier-advances', 'Approve or reject supplier advances', 'supplier-advances', 'active', '2026-02-14 13:33:37', '2026-02-14 13:33:37'),
(81, 'View Account Transactions', 'view-account-transactions', 'View account transactions', 'account-transactions', 'active', '2026-02-16 08:10:49', '2026-02-16 08:10:49'),
(82, 'Create Account Transactions', 'create-account-transactions', 'Create account transactions', 'account-transactions', 'active', '2026-02-16 08:10:49', '2026-02-16 08:10:49'),
(83, 'Edit Account Transactions', 'edit-account-transactions', 'Edit account transactions', 'account-transactions', 'active', '2026-02-16 08:10:49', '2026-02-16 08:10:49'),
(84, 'Delete Account Transactions', 'delete-account-transactions', 'Delete account transactions', 'account-transactions', 'active', '2026-02-16 08:10:49', '2026-02-16 08:10:49'),
(85, 'View Station Finances', 'view-station-finances', 'View station financial reports', 'station-finances', 'active', '2026-02-16 08:17:18', '2026-02-16 08:17:18'),
(86, 'Pay Supplier Payables', 'pay-supplier-payables', 'Process supplier payments', 'station-finances', 'active', '2026-02-16 08:17:18', '2026-02-16 08:17:18'),
(87, 'View Stock Receives', 'view-stock-receives', 'View stock receive records', 'stock-receives', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(88, 'Create Stock Receives', 'create-stock-receives', 'Create stock receive records', 'stock-receives', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(89, 'Edit Stock Receives', 'edit-stock-receives', 'Edit stock receive records', 'stock-receives', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(90, 'Delete Stock Receives', 'delete-stock-receives', 'Delete stock receive records', 'stock-receives', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(91, 'Approve Stock Receives', 'approve-stock-receives', 'Approve or reject stock receives', 'stock-receives', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(92, 'View Stock Transfers', 'view-stock-transfers', 'View stock transfer records', 'stock-transfers', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(93, 'Create Stock Transfers', 'create-stock-transfers', 'Create stock transfer records', 'stock-transfers', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(94, 'Edit Stock Transfers', 'edit-stock-transfers', 'Edit stock transfer records', 'stock-transfers', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(95, 'Delete Stock Transfers', 'delete-stock-transfers', 'Delete stock transfer records', 'stock-transfers', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(96, 'Approve Stock Transfers', 'approve-stock-transfers', 'Approve or reject stock transfers', 'stock-transfers', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(97, 'View Warehouse Incoming', 'view-warehouse-incoming', 'View incoming warehouse transfers', 'warehouse', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(98, 'View Warehouse Stock', 'view-warehouse-stock', 'View warehouse stock levels', 'warehouse', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(99, 'Receive Warehouse Transfers', 'receive-warehouse-transfers', 'Receive transfers into warehouse', 'warehouse', 'active', '2026-02-16 08:30:40', '2026-02-16 08:30:40'),
(100, 'View Production', 'view-production', 'View production records', 'production', 'active', '2026-02-16 08:55:06', '2026-02-16 08:55:06'),
(101, 'Create Production', 'create-production', 'Create new production batches', 'production', 'active', '2026-02-16 08:55:06', '2026-02-16 08:55:06'),
(102, 'Edit Production', 'edit-production', 'Edit production records', 'production', 'active', '2026-02-16 08:55:06', '2026-02-16 08:55:06'),
(103, 'Delete Production', 'delete-production', 'Delete production records', 'production', 'active', '2026-02-16 08:55:06', '2026-02-16 08:55:06'),
(104, 'Complete Production', 'complete-production', 'Mark production as complete', 'production', 'active', '2026-02-16 08:55:06', '2026-02-16 08:55:06'),
(105, 'property', 'property', 'property', 'finance', 'active', '2026-02-05 11:19:39', '2026-02-05 11:19:39');

-- --------------------------------------------------------

--
-- Table structure for table `processing_steps`
--

CREATE TABLE `processing_steps` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `step_order` int(11) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `processing_steps`
--

INSERT INTO `processing_steps` (`id`, `name`, `description`, `step_order`, `status`, `created_at`, `updated_at`) VALUES
(16, 'Receive Patchment', 'Receive Patchment', 1, 'active', '2026-02-11 07:15:12', '2026-02-11 07:15:12'),
(17, 'Milling', 'Milling', 2, 'active', '2026-02-11 07:15:36', '2026-02-11 07:15:36'),
(18, 'Sorting', 'Sorting', 3, 'active', '2026-02-11 07:15:52', '2026-02-11 07:15:52'),
(19, 'Mixing', 'Mixing', 4, 'active', '2026-02-11 07:16:06', '2026-02-11 07:16:06'),
(20, 'Soleing', 'Soleing', 5, 'active', '2026-02-11 07:16:21', '2026-02-11 07:16:21');

-- --------------------------------------------------------

--
-- Table structure for table `production`
--

CREATE TABLE `production` (
  `id` int(11) NOT NULL,
  `production_number` varchar(50) NOT NULL,
  `location_id` int(11) NOT NULL COMMENT 'Station location',
  `supplier_id` int(11) DEFAULT NULL,
  `input_category_type_unit_id` int(11) NOT NULL,
  `input_quantity` decimal(15,4) NOT NULL,
  `input_unit_price` decimal(15,2) DEFAULT 0.00,
  `output_category_type_unit_id` int(11) DEFAULT NULL,
  `output_quantity` decimal(15,4) DEFAULT NULL,
  `output_unit_price` decimal(15,2) DEFAULT NULL,
  `production_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production`
--

INSERT INTO `production` (`id`, `production_number`, `location_id`, `supplier_id`, `input_category_type_unit_id`, `input_quantity`, `input_unit_price`, `output_category_type_unit_id`, `output_quantity`, `output_unit_price`, `production_date`, `notes`, `status`, `created_by`, `completed_by`, `completed_at`, `created_at`, `updated_at`) VALUES
(1, 'PRD-202602-0001', 2, 335, 1, 3.0000, 3.00, 5, 3.0000, 3.00, '2026-02-24', '2efv', 'completed', 1, 1, '2026-02-24 11:16:37', '2026-02-24 09:52:16', '2026-02-24 10:16:37');

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Green Coffee', 'Green Coffee', 'active', '2026-02-05 07:55:59', '2026-02-05 07:55:59'),
(3, 'Parchment', 'Parchment', 'active', '2026-02-05 07:56:39', '2026-02-05 07:56:39'),
(4, 'cherries', 'cherries', 'active', '2026-02-05 07:56:49', '2026-02-05 07:56:49');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'Full system access with all permissions', 'active', '2026-02-04 12:39:33', '2026-02-04 13:27:31'),
(2, 'Finance Officer', 'Finance Officer', 'active', '2026-02-05 12:50:09', '2026-02-05 12:50:09'),
(3, 'Account Officer', 'Account Officer', 'active', '2026-02-05 12:50:55', '2026-02-05 12:50:55'),
(4, 'Station Manager', 'Station Manager', 'active', '2026-02-05 12:51:39', '2026-02-05 12:51:39');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(531, 1, 1, '2026-02-05 11:25:57'),
(532, 1, 65, '2026-02-05 11:25:57'),
(533, 1, 67, '2026-02-05 11:25:57'),
(534, 1, 66, '2026-02-05 11:25:57'),
(535, 1, 64, '2026-02-05 11:25:57'),
(536, 1, 21, '2026-02-05 11:25:57'),
(537, 1, 25, '2026-02-05 11:25:57'),
(538, 1, 29, '2026-02-05 11:25:57'),
(539, 1, 33, '2026-02-05 11:25:57'),
(540, 1, 23, '2026-02-05 11:25:57'),
(541, 1, 31, '2026-02-05 11:25:57'),
(542, 1, 35, '2026-02-05 11:25:57'),
(543, 1, 22, '2026-02-05 11:25:57'),
(544, 1, 26, '2026-02-05 11:25:57'),
(545, 1, 30, '2026-02-05 11:25:57'),
(546, 1, 20, '2026-02-05 11:25:57'),
(547, 1, 24, '2026-02-05 11:25:57'),
(548, 1, 28, '2026-02-05 11:25:57'),
(549, 1, 32, '2026-02-05 11:25:57'),
(550, 1, 11, '2026-02-05 11:25:57'),
(551, 1, 10, '2026-02-05 11:25:57'),
(552, 1, 41, '2026-02-05 11:25:57'),
(553, 1, 45, '2026-02-05 11:25:57'),
(554, 1, 43, '2026-02-05 11:25:57'),
(555, 1, 47, '2026-02-05 11:25:57'),
(556, 1, 42, '2026-02-05 11:25:57'),
(557, 1, 46, '2026-02-05 11:25:57'),
(558, 1, 49, '2026-02-05 11:25:57'),
(559, 1, 40, '2026-02-05 11:25:57'),
(560, 1, 44, '2026-02-05 11:25:57'),
(561, 1, 48, '2026-02-05 11:25:57'),
(562, 1, 7, '2026-02-05 11:25:57'),
(563, 1, 9, '2026-02-05 11:25:57'),
(564, 1, 8, '2026-02-05 11:25:57'),
(565, 1, 6, '2026-02-05 11:25:57'),
(566, 1, 13, '2026-02-05 11:25:57'),
(567, 1, 51, '2026-02-05 11:25:57'),
(568, 1, 12, '2026-02-05 11:25:57'),
(569, 1, 50, '2026-02-05 11:25:57'),
(570, 1, 57, '2026-02-05 11:25:57'),
(571, 1, 61, '2026-02-05 11:25:57'),
(572, 1, 59, '2026-02-05 11:25:57'),
(573, 1, 63, '2026-02-05 11:25:57'),
(574, 1, 58, '2026-02-05 11:25:57'),
(575, 1, 62, '2026-02-05 11:25:57'),
(576, 1, 56, '2026-02-05 11:25:57'),
(577, 1, 60, '2026-02-05 11:25:57'),
(578, 1, 3, '2026-02-05 11:25:57'),
(579, 1, 5, '2026-02-05 11:25:57'),
(580, 1, 4, '2026-02-05 11:25:57'),
(581, 1, 2, '2026-02-05 11:25:57'),
(582, 1, 69, '2026-02-05 11:29:14'),
(583, 1, 71, '2026-02-05 11:29:14'),
(584, 1, 70, '2026-02-05 11:29:14'),
(585, 1, 68, '2026-02-05 11:29:14'),
(589, 1, 27, '2026-02-14 13:34:14'),
(590, 1, 34, '2026-02-14 13:34:14'),
(591, 1, 72, '2026-02-14 13:34:14'),
(592, 1, 73, '2026-02-14 13:34:14'),
(593, 1, 74, '2026-02-14 13:34:14'),
(594, 1, 75, '2026-02-14 13:34:14'),
(595, 1, 76, '2026-02-14 13:34:14'),
(596, 1, 77, '2026-02-14 13:34:14'),
(597, 1, 78, '2026-02-14 13:34:14'),
(598, 1, 79, '2026-02-14 13:34:14'),
(599, 1, 80, '2026-02-14 13:34:14'),
(600, 1, 1, '2026-02-16 08:11:48'),
(601, 1, 2, '2026-02-16 08:11:48'),
(602, 1, 3, '2026-02-16 08:11:48'),
(603, 1, 4, '2026-02-16 08:11:48'),
(604, 1, 5, '2026-02-16 08:11:48'),
(605, 1, 6, '2026-02-16 08:11:48'),
(606, 1, 7, '2026-02-16 08:11:48'),
(607, 1, 8, '2026-02-16 08:11:48'),
(608, 1, 9, '2026-02-16 08:11:48'),
(609, 1, 10, '2026-02-16 08:11:48'),
(610, 1, 11, '2026-02-16 08:11:48'),
(611, 1, 12, '2026-02-16 08:11:48'),
(612, 1, 13, '2026-02-16 08:11:48'),
(613, 1, 20, '2026-02-16 08:11:48'),
(614, 1, 21, '2026-02-16 08:11:48'),
(615, 1, 22, '2026-02-16 08:11:48'),
(616, 1, 23, '2026-02-16 08:11:48'),
(617, 1, 24, '2026-02-16 08:11:48'),
(618, 1, 25, '2026-02-16 08:11:48'),
(619, 1, 26, '2026-02-16 08:11:48'),
(620, 1, 27, '2026-02-16 08:11:48'),
(621, 1, 28, '2026-02-16 08:11:48'),
(622, 1, 29, '2026-02-16 08:11:48'),
(623, 1, 30, '2026-02-16 08:11:48'),
(624, 1, 31, '2026-02-16 08:11:48'),
(625, 1, 32, '2026-02-16 08:11:48'),
(626, 1, 33, '2026-02-16 08:11:48'),
(627, 1, 34, '2026-02-16 08:11:48'),
(628, 1, 35, '2026-02-16 08:11:48'),
(629, 1, 40, '2026-02-16 08:11:48'),
(630, 1, 41, '2026-02-16 08:11:48'),
(631, 1, 42, '2026-02-16 08:11:48'),
(632, 1, 43, '2026-02-16 08:11:48'),
(633, 1, 44, '2026-02-16 08:11:48'),
(634, 1, 45, '2026-02-16 08:11:48'),
(635, 1, 46, '2026-02-16 08:11:48'),
(636, 1, 47, '2026-02-16 08:11:48'),
(637, 1, 48, '2026-02-16 08:11:48'),
(638, 1, 49, '2026-02-16 08:11:48'),
(639, 1, 50, '2026-02-16 08:11:48'),
(640, 1, 51, '2026-02-16 08:11:48'),
(641, 1, 56, '2026-02-16 08:11:48'),
(642, 1, 57, '2026-02-16 08:11:48'),
(643, 1, 58, '2026-02-16 08:11:48'),
(644, 1, 59, '2026-02-16 08:11:48'),
(645, 1, 60, '2026-02-16 08:11:48'),
(646, 1, 61, '2026-02-16 08:11:48'),
(647, 1, 62, '2026-02-16 08:11:48'),
(648, 1, 63, '2026-02-16 08:11:48'),
(649, 1, 64, '2026-02-16 08:11:48'),
(650, 1, 65, '2026-02-16 08:11:48'),
(651, 1, 66, '2026-02-16 08:11:48'),
(652, 1, 67, '2026-02-16 08:11:48'),
(653, 1, 68, '2026-02-16 08:11:48'),
(654, 1, 69, '2026-02-16 08:11:48'),
(655, 1, 70, '2026-02-16 08:11:48'),
(656, 1, 71, '2026-02-16 08:11:48'),
(657, 1, 72, '2026-02-16 08:11:48'),
(658, 1, 73, '2026-02-16 08:11:48'),
(659, 1, 74, '2026-02-16 08:11:48'),
(660, 1, 75, '2026-02-16 08:11:48'),
(661, 1, 76, '2026-02-16 08:11:48'),
(662, 1, 77, '2026-02-16 08:11:48'),
(663, 1, 78, '2026-02-16 08:11:48'),
(664, 1, 79, '2026-02-16 08:11:48'),
(665, 1, 80, '2026-02-16 08:11:48'),
(666, 1, 81, '2026-02-16 08:11:48'),
(667, 1, 82, '2026-02-16 08:11:48'),
(668, 1, 83, '2026-02-16 08:11:48'),
(669, 1, 84, '2026-02-16 08:11:48'),
(727, 1, 1, '2026-02-16 08:17:31'),
(728, 1, 2, '2026-02-16 08:17:31'),
(729, 1, 3, '2026-02-16 08:17:31'),
(730, 1, 4, '2026-02-16 08:17:31'),
(731, 1, 5, '2026-02-16 08:17:31'),
(732, 1, 6, '2026-02-16 08:17:31'),
(733, 1, 7, '2026-02-16 08:17:31'),
(734, 1, 8, '2026-02-16 08:17:31'),
(735, 1, 9, '2026-02-16 08:17:31'),
(736, 1, 10, '2026-02-16 08:17:31'),
(737, 1, 11, '2026-02-16 08:17:31'),
(738, 1, 12, '2026-02-16 08:17:31'),
(739, 1, 13, '2026-02-16 08:17:31'),
(740, 1, 20, '2026-02-16 08:17:31'),
(741, 1, 21, '2026-02-16 08:17:31'),
(742, 1, 22, '2026-02-16 08:17:31'),
(743, 1, 23, '2026-02-16 08:17:31'),
(744, 1, 24, '2026-02-16 08:17:31'),
(745, 1, 25, '2026-02-16 08:17:31'),
(746, 1, 26, '2026-02-16 08:17:31'),
(747, 1, 27, '2026-02-16 08:17:31'),
(748, 1, 28, '2026-02-16 08:17:31'),
(749, 1, 29, '2026-02-16 08:17:31'),
(750, 1, 30, '2026-02-16 08:17:31'),
(751, 1, 31, '2026-02-16 08:17:31'),
(752, 1, 32, '2026-02-16 08:17:31'),
(753, 1, 33, '2026-02-16 08:17:31'),
(754, 1, 34, '2026-02-16 08:17:31'),
(755, 1, 35, '2026-02-16 08:17:31'),
(756, 1, 40, '2026-02-16 08:17:31'),
(757, 1, 41, '2026-02-16 08:17:31'),
(758, 1, 42, '2026-02-16 08:17:31'),
(759, 1, 43, '2026-02-16 08:17:31'),
(760, 1, 44, '2026-02-16 08:17:31'),
(761, 1, 45, '2026-02-16 08:17:31'),
(762, 1, 46, '2026-02-16 08:17:31'),
(763, 1, 47, '2026-02-16 08:17:31'),
(764, 1, 48, '2026-02-16 08:17:31'),
(765, 1, 49, '2026-02-16 08:17:31'),
(766, 1, 50, '2026-02-16 08:17:31'),
(767, 1, 51, '2026-02-16 08:17:31'),
(768, 1, 56, '2026-02-16 08:17:31'),
(769, 1, 57, '2026-02-16 08:17:31'),
(770, 1, 58, '2026-02-16 08:17:31'),
(771, 1, 59, '2026-02-16 08:17:31'),
(772, 1, 60, '2026-02-16 08:17:31'),
(773, 1, 61, '2026-02-16 08:17:31'),
(774, 1, 62, '2026-02-16 08:17:31'),
(775, 1, 63, '2026-02-16 08:17:31'),
(776, 1, 64, '2026-02-16 08:17:31'),
(777, 1, 65, '2026-02-16 08:17:31'),
(778, 1, 66, '2026-02-16 08:17:31'),
(779, 1, 67, '2026-02-16 08:17:31'),
(780, 1, 68, '2026-02-16 08:17:31'),
(781, 1, 69, '2026-02-16 08:17:31'),
(782, 1, 70, '2026-02-16 08:17:31'),
(783, 1, 71, '2026-02-16 08:17:31'),
(784, 1, 72, '2026-02-16 08:17:31'),
(785, 1, 73, '2026-02-16 08:17:31'),
(786, 1, 74, '2026-02-16 08:17:31'),
(787, 1, 75, '2026-02-16 08:17:31'),
(788, 1, 76, '2026-02-16 08:17:31'),
(789, 1, 77, '2026-02-16 08:17:31'),
(790, 1, 78, '2026-02-16 08:17:31'),
(791, 1, 79, '2026-02-16 08:17:31'),
(792, 1, 80, '2026-02-16 08:17:31'),
(793, 1, 81, '2026-02-16 08:17:31'),
(794, 1, 82, '2026-02-16 08:17:31'),
(795, 1, 83, '2026-02-16 08:17:31'),
(796, 1, 84, '2026-02-16 08:17:31'),
(797, 1, 85, '2026-02-16 08:17:31'),
(798, 1, 86, '2026-02-16 08:17:31'),
(854, 1, 1, '2026-02-16 08:30:52'),
(855, 1, 2, '2026-02-16 08:30:52'),
(856, 1, 3, '2026-02-16 08:30:52'),
(857, 1, 4, '2026-02-16 08:30:52'),
(858, 1, 5, '2026-02-16 08:30:52'),
(859, 1, 6, '2026-02-16 08:30:52'),
(860, 1, 7, '2026-02-16 08:30:52'),
(861, 1, 8, '2026-02-16 08:30:52'),
(862, 1, 9, '2026-02-16 08:30:52'),
(863, 1, 10, '2026-02-16 08:30:52'),
(864, 1, 11, '2026-02-16 08:30:52'),
(865, 1, 12, '2026-02-16 08:30:52'),
(866, 1, 13, '2026-02-16 08:30:52'),
(867, 1, 20, '2026-02-16 08:30:52'),
(868, 1, 21, '2026-02-16 08:30:52'),
(869, 1, 22, '2026-02-16 08:30:52'),
(870, 1, 23, '2026-02-16 08:30:52'),
(871, 1, 24, '2026-02-16 08:30:52'),
(872, 1, 25, '2026-02-16 08:30:52'),
(873, 1, 26, '2026-02-16 08:30:52'),
(874, 1, 27, '2026-02-16 08:30:52'),
(875, 1, 28, '2026-02-16 08:30:52'),
(876, 1, 29, '2026-02-16 08:30:52'),
(877, 1, 30, '2026-02-16 08:30:52'),
(878, 1, 31, '2026-02-16 08:30:52'),
(879, 1, 32, '2026-02-16 08:30:52'),
(880, 1, 33, '2026-02-16 08:30:52'),
(881, 1, 34, '2026-02-16 08:30:52'),
(882, 1, 35, '2026-02-16 08:30:52'),
(883, 1, 40, '2026-02-16 08:30:52'),
(884, 1, 41, '2026-02-16 08:30:52'),
(885, 1, 42, '2026-02-16 08:30:52'),
(886, 1, 43, '2026-02-16 08:30:52'),
(887, 1, 44, '2026-02-16 08:30:52'),
(888, 1, 45, '2026-02-16 08:30:52'),
(889, 1, 46, '2026-02-16 08:30:52'),
(890, 1, 47, '2026-02-16 08:30:52'),
(891, 1, 48, '2026-02-16 08:30:52'),
(892, 1, 49, '2026-02-16 08:30:52'),
(893, 1, 50, '2026-02-16 08:30:52'),
(894, 1, 51, '2026-02-16 08:30:52'),
(895, 1, 56, '2026-02-16 08:30:52'),
(896, 1, 57, '2026-02-16 08:30:52'),
(897, 1, 58, '2026-02-16 08:30:52'),
(898, 1, 59, '2026-02-16 08:30:52'),
(899, 1, 60, '2026-02-16 08:30:52'),
(900, 1, 61, '2026-02-16 08:30:52'),
(901, 1, 62, '2026-02-16 08:30:52'),
(902, 1, 63, '2026-02-16 08:30:52'),
(903, 1, 64, '2026-02-16 08:30:52'),
(904, 1, 65, '2026-02-16 08:30:52'),
(905, 1, 66, '2026-02-16 08:30:52'),
(906, 1, 67, '2026-02-16 08:30:52'),
(907, 1, 68, '2026-02-16 08:30:52'),
(908, 1, 69, '2026-02-16 08:30:52'),
(909, 1, 70, '2026-02-16 08:30:52'),
(910, 1, 71, '2026-02-16 08:30:52'),
(911, 1, 72, '2026-02-16 08:30:52'),
(912, 1, 73, '2026-02-16 08:30:52'),
(913, 1, 74, '2026-02-16 08:30:52'),
(914, 1, 75, '2026-02-16 08:30:52'),
(915, 1, 76, '2026-02-16 08:30:52'),
(916, 1, 77, '2026-02-16 08:30:52'),
(917, 1, 78, '2026-02-16 08:30:52'),
(918, 1, 79, '2026-02-16 08:30:52'),
(919, 1, 80, '2026-02-16 08:30:52'),
(920, 1, 81, '2026-02-16 08:30:52'),
(921, 1, 82, '2026-02-16 08:30:52'),
(922, 1, 83, '2026-02-16 08:30:52'),
(923, 1, 84, '2026-02-16 08:30:52'),
(924, 1, 85, '2026-02-16 08:30:52'),
(925, 1, 86, '2026-02-16 08:30:52'),
(926, 1, 87, '2026-02-16 08:30:52'),
(927, 1, 88, '2026-02-16 08:30:52'),
(928, 1, 89, '2026-02-16 08:30:52'),
(929, 1, 90, '2026-02-16 08:30:52'),
(930, 1, 91, '2026-02-16 08:30:52'),
(931, 1, 92, '2026-02-16 08:30:52'),
(932, 1, 93, '2026-02-16 08:30:52'),
(933, 1, 94, '2026-02-16 08:30:52'),
(934, 1, 95, '2026-02-16 08:30:52'),
(935, 1, 96, '2026-02-16 08:30:52'),
(936, 1, 97, '2026-02-16 08:30:52'),
(937, 1, 98, '2026-02-16 08:30:52'),
(938, 1, 99, '2026-02-16 08:30:52'),
(981, 1, 1, '2026-02-16 08:55:24'),
(982, 1, 2, '2026-02-16 08:55:24'),
(983, 1, 3, '2026-02-16 08:55:24'),
(984, 1, 4, '2026-02-16 08:55:24'),
(985, 1, 5, '2026-02-16 08:55:24'),
(986, 1, 6, '2026-02-16 08:55:24'),
(987, 1, 7, '2026-02-16 08:55:24'),
(988, 1, 8, '2026-02-16 08:55:24'),
(989, 1, 9, '2026-02-16 08:55:24'),
(990, 1, 10, '2026-02-16 08:55:24'),
(991, 1, 11, '2026-02-16 08:55:24'),
(992, 1, 12, '2026-02-16 08:55:24'),
(993, 1, 13, '2026-02-16 08:55:24'),
(994, 1, 20, '2026-02-16 08:55:24'),
(995, 1, 21, '2026-02-16 08:55:24'),
(996, 1, 22, '2026-02-16 08:55:24'),
(997, 1, 23, '2026-02-16 08:55:24'),
(998, 1, 24, '2026-02-16 08:55:24'),
(999, 1, 25, '2026-02-16 08:55:24'),
(1000, 1, 26, '2026-02-16 08:55:24'),
(1001, 1, 27, '2026-02-16 08:55:24'),
(1002, 1, 28, '2026-02-16 08:55:24'),
(1003, 1, 29, '2026-02-16 08:55:24'),
(1004, 1, 30, '2026-02-16 08:55:24'),
(1005, 1, 31, '2026-02-16 08:55:24'),
(1006, 1, 32, '2026-02-16 08:55:24'),
(1007, 1, 33, '2026-02-16 08:55:24'),
(1008, 1, 34, '2026-02-16 08:55:24'),
(1009, 1, 35, '2026-02-16 08:55:24'),
(1010, 1, 40, '2026-02-16 08:55:24'),
(1011, 1, 41, '2026-02-16 08:55:24'),
(1012, 1, 42, '2026-02-16 08:55:24'),
(1013, 1, 43, '2026-02-16 08:55:24'),
(1014, 1, 44, '2026-02-16 08:55:24'),
(1015, 1, 45, '2026-02-16 08:55:24'),
(1016, 1, 46, '2026-02-16 08:55:24'),
(1017, 1, 47, '2026-02-16 08:55:24'),
(1018, 1, 48, '2026-02-16 08:55:24'),
(1019, 1, 49, '2026-02-16 08:55:24'),
(1020, 1, 50, '2026-02-16 08:55:24'),
(1021, 1, 51, '2026-02-16 08:55:24'),
(1022, 1, 56, '2026-02-16 08:55:24'),
(1023, 1, 57, '2026-02-16 08:55:24'),
(1024, 1, 58, '2026-02-16 08:55:24'),
(1025, 1, 59, '2026-02-16 08:55:24'),
(1026, 1, 60, '2026-02-16 08:55:24'),
(1027, 1, 61, '2026-02-16 08:55:24'),
(1028, 1, 62, '2026-02-16 08:55:24'),
(1029, 1, 63, '2026-02-16 08:55:24'),
(1030, 1, 64, '2026-02-16 08:55:24'),
(1031, 1, 65, '2026-02-16 08:55:24'),
(1032, 1, 66, '2026-02-16 08:55:24'),
(1033, 1, 67, '2026-02-16 08:55:24'),
(1034, 1, 68, '2026-02-16 08:55:24'),
(1035, 1, 69, '2026-02-16 08:55:24'),
(1036, 1, 70, '2026-02-16 08:55:24'),
(1037, 1, 71, '2026-02-16 08:55:24'),
(1038, 1, 72, '2026-02-16 08:55:24'),
(1039, 1, 73, '2026-02-16 08:55:24'),
(1040, 1, 74, '2026-02-16 08:55:24'),
(1041, 1, 75, '2026-02-16 08:55:24'),
(1042, 1, 76, '2026-02-16 08:55:24'),
(1043, 1, 77, '2026-02-16 08:55:24'),
(1044, 1, 78, '2026-02-16 08:55:24'),
(1045, 1, 79, '2026-02-16 08:55:24'),
(1046, 1, 80, '2026-02-16 08:55:24'),
(1047, 1, 81, '2026-02-16 08:55:24'),
(1048, 1, 82, '2026-02-16 08:55:24'),
(1049, 1, 83, '2026-02-16 08:55:24'),
(1050, 1, 84, '2026-02-16 08:55:24'),
(1051, 1, 85, '2026-02-16 08:55:24'),
(1052, 1, 86, '2026-02-16 08:55:24'),
(1053, 1, 87, '2026-02-16 08:55:24'),
(1054, 1, 88, '2026-02-16 08:55:24'),
(1055, 1, 89, '2026-02-16 08:55:24'),
(1056, 1, 90, '2026-02-16 08:55:24'),
(1057, 1, 91, '2026-02-16 08:55:24'),
(1058, 1, 92, '2026-02-16 08:55:24'),
(1059, 1, 93, '2026-02-16 08:55:24'),
(1060, 1, 94, '2026-02-16 08:55:24'),
(1061, 1, 95, '2026-02-16 08:55:24'),
(1062, 1, 96, '2026-02-16 08:55:24'),
(1063, 1, 97, '2026-02-16 08:55:24'),
(1064, 1, 98, '2026-02-16 08:55:24'),
(1065, 1, 99, '2026-02-16 08:55:24'),
(1066, 1, 100, '2026-02-16 08:55:24'),
(1067, 1, 101, '2026-02-16 08:55:24'),
(1068, 1, 102, '2026-02-16 08:55:24'),
(1069, 1, 103, '2026-02-16 08:55:24'),
(1070, 1, 104, '2026-02-16 08:55:24');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `sale_number` varchar(50) NOT NULL,
  `location_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `sale_date` date NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(15,2) DEFAULT 0.00,
  `discount_amount` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_status` enum('pending','partial','paid') DEFAULT 'pending',
  `amount_paid` decimal(15,2) DEFAULT 0.00,
  `account_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('draft','confirmed','cancelled') DEFAULT 'draft',
  `created_by` int(11) NOT NULL,
  `confirmed_by` int(11) DEFAULT NULL,
  `confirmed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `sale_number`, `location_id`, `client_id`, `sale_date`, `subtotal`, `tax_amount`, `discount_amount`, `total_amount`, `payment_status`, `amount_paid`, `account_id`, `notes`, `status`, `created_by`, `confirmed_by`, `confirmed_at`, `created_at`, `updated_at`) VALUES
(1, 'SL202602250001', 1, 2, '2026-02-25', 10000.00, 0.00, 0.00, 10000.00, 'pending', 0.00, NULL, 'tgttt', 'confirmed', 1, 1, '2026-02-25 10:57:17', '2026-02-25 09:33:13', '2026-02-25 09:57:17');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_category_id` int(11) NOT NULL,
  `category_type_unit_id` int(11) NOT NULL,
  `processing_step_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `sell_quantity` decimal(15,4) DEFAULT NULL,
  `sell_unit_id` int(11) DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_category_id`, `category_type_unit_id`, `processing_step_id`, `supplier_id`, `quantity`, `sell_quantity`, `sell_unit_id`, `unit_price`, `total_price`, `notes`, `created_at`) VALUES
(1, 1, 4, 1, 16, 886, 10.0000, 10.0000, 3, 1000.00, 10000.00, NULL, '2026-02-25 09:33:13');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(50) DEFAULT 'text',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'Gihanga Coffee', 'text', '2026-02-04 14:01:52', '2026-02-04 14:01:52'),
(2, 'company_short_name', 'GC', 'text', '2026-02-04 14:01:52', '2026-02-04 14:01:52'),
(3, 'company_email', 'info@gihangacoffee.com', 'text', '2026-02-04 14:01:52', '2026-02-04 14:01:52'),
(4, 'company_phone', '+250 788 000 000', 'text', '2026-02-04 14:01:52', '2026-02-04 14:01:52'),
(5, 'company_address', 'Kigali, Rwanda', 'textarea', '2026-02-04 14:01:52', '2026-02-04 14:01:52'),
(6, 'company_logo', 'logo.png', 'image', '2026-02-04 14:01:52', '2026-02-04 14:01:52');

-- --------------------------------------------------------

--
-- Table structure for table `stock_receives`
--

CREATE TABLE `stock_receives` (
  `id` int(11) NOT NULL,
  `receive_number` varchar(20) DEFAULT NULL,
  `location_type_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `product_category_id` int(11) NOT NULL,
  `category_type_unit_id` int(11) NOT NULL,
  `processing_step_id` int(11) DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `quantity_in_kg` decimal(15,4) DEFAULT NULL,
  `unit_price` decimal(15,2) NOT NULL,
  `price_per_kg` decimal(15,4) DEFAULT NULL,
  `total_price` decimal(15,2) NOT NULL,
  `receive_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','approved','cancelled') DEFAULT 'pending',
  `payment_method` enum('advance','account') DEFAULT NULL,
  `account_id` int(11) DEFAULT NULL,
  `advance_id` int(11) DEFAULT NULL,
  `advance_amount` decimal(15,2) DEFAULT 0.00,
  `account_amount` decimal(15,2) DEFAULT 0.00,
  `payable_amount` decimal(15,2) DEFAULT 0.00,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_receives`
--

INSERT INTO `stock_receives` (`id`, `receive_number`, `location_type_id`, `location_id`, `supplier_id`, `product_category_id`, `category_type_unit_id`, `processing_step_id`, `quantity`, `quantity_in_kg`, `unit_price`, `price_per_kg`, `total_price`, `receive_date`, `notes`, `status`, `payment_method`, `account_id`, `advance_id`, `advance_amount`, `account_amount`, `payable_amount`, `created_by`, `approved_by`, `approved_at`, `created_at`, `updated_at`) VALUES
(1, 'RCV-202602-0001', 1, 2, 402, 4, 2, NULL, 556666.0000, 556666.0000, 5.00, 5.0000, 2783330.00, '2026-02-24', 'ertgh', 'approved', 'account', 3, NULL, 0.00, 3.00, 2783327.00, 1, 1, '2026-02-24 11:25:01', '2026-02-24 09:13:00', '2026-02-24 09:25:01'),
(2, 'RCV-202602-0002', 1, 2, 335, 4, 1, NULL, 3.0000, 3.0000, 3.00, 3.0000, 9.00, '2026-02-24', 'erfgh', 'approved', NULL, NULL, NULL, 0.00, 0.00, 9.00, 1, 1, '2026-02-24 11:26:01', '2026-02-24 09:25:54', '2026-02-24 09:26:01'),
(3, 'RCV-202602-0003', 1, 2, 773, 4, 1, NULL, 3.0000, 3.0000, 3.00, 3.0000, 9.00, '2026-02-24', 'tyk', 'approved', 'account', 4, NULL, 0.00, 9.00, 0.00, 1, 1, '2026-02-24 11:27:15', '2026-02-24 09:27:07', '2026-02-24 09:27:15'),
(4, 'RCV-202602-0004', 1, 2, 356, 4, 1, NULL, 100.0000, 100.0000, 100.00, 100.0000, 10000.00, '2026-02-24', '', 'approved', 'account', 4, NULL, 0.00, 87.00, 9913.00, 1, 1, '2026-02-24 11:47:57', '2026-02-24 09:47:49', '2026-02-24 09:47:57'),
(5, 'RCV-202602-0005', 2, 1, 335, 3, 4, 18, 4.0000, 4.0000, 4.00, 4.0000, 16.00, '2026-02-25', 'dfgh', 'pending', NULL, 2, NULL, 0.00, 0.00, 0.00, 1, NULL, NULL, '2026-02-25 09:03:59', '2026-02-25 09:03:59');

-- --------------------------------------------------------

--
-- Table structure for table `stock_summary`
--

CREATE TABLE `stock_summary` (
  `id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `processing_step_id` int(11) DEFAULT NULL,
  `source_location_id` int(11) DEFAULT NULL,
  `product_category_id` int(11) NOT NULL,
  `category_type_unit_id` int(11) NOT NULL,
  `total_quantity` decimal(15,4) NOT NULL DEFAULT 0.0000,
  `total_value` decimal(15,2) NOT NULL DEFAULT 0.00,
  `avg_unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `last_receive_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_summary`
--

INSERT INTO `stock_summary` (`id`, `location_id`, `supplier_id`, `processing_step_id`, `source_location_id`, `product_category_id`, `category_type_unit_id`, `total_quantity`, `total_value`, `avg_unit_price`, `last_receive_date`, `created_at`, `updated_at`) VALUES
(1, 2, 402, NULL, NULL, 4, 3, 556.6660, 2783330.00, 5.00, '2026-02-24', '2026-02-24 09:25:01', '2026-02-24 09:25:01'),
(3, 2, 773, NULL, NULL, 4, 1, 3.0000, 9.00, 3.00, '2026-02-24', '2026-02-24 09:27:15', '2026-02-24 09:27:15'),
(4, 2, 356, NULL, NULL, 4, 1, 1.0000, 100.00, 100.00, '2026-02-24', '2026-02-24 09:47:57', '2026-02-25 07:15:14'),
(6, 1, 886, 16, 2, 3, 5, 0.0000, 66.00, 22.00, '2026-02-25', '2026-02-25 07:24:22', '2026-02-25 09:19:54'),
(7, 1, 886, 16, 2, 4, 1, 89.0000, 3916.00, 44.00, '2026-02-25', '2026-02-25 07:31:56', '2026-02-25 09:57:17'),
(8, 1, 886, 17, NULL, 3, 4, 2.0000, 0.00, 0.00, '2026-02-25', '2026-02-25 09:19:54', '2026-02-25 09:19:54');

-- --------------------------------------------------------

--
-- Table structure for table `stock_transfers`
--

CREATE TABLE `stock_transfers` (
  `id` int(11) NOT NULL,
  `transfer_number` varchar(20) DEFAULT NULL,
  `from_location_type_id` int(11) NOT NULL,
  `from_location_id` int(11) NOT NULL,
  `to_location_type_id` int(11) NOT NULL,
  `to_location_id` int(11) NOT NULL,
  `product_category_id` int(11) NOT NULL,
  `category_type_id` int(11) DEFAULT NULL,
  `measurement_unit_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `category_type_unit_id` int(11) NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `unit_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(15,2) NOT NULL DEFAULT 0.00,
  `transfer_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','in_transit','received','cancelled') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `received_by` int(11) DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_transfers`
--

INSERT INTO `stock_transfers` (`id`, `transfer_number`, `from_location_type_id`, `from_location_id`, `to_location_type_id`, `to_location_id`, `product_category_id`, `category_type_id`, `measurement_unit_id`, `supplier_id`, `category_type_unit_id`, `quantity`, `unit_price`, `total_price`, `transfer_date`, `notes`, `status`, `created_by`, `approved_by`, `approved_at`, `received_by`, `received_at`, `created_at`, `updated_at`) VALUES
(1, 'TRF-202602-0001', 1, 2, 2, 1, 3, 4, 3, 335, 5, 3.0000, 22.00, 66.00, '2026-02-25', 'fv', 'received', 1, 1, '2026-02-25 08:14:18', 1, '2026-02-25 08:24:22', '2026-02-25 07:14:10', '2026-02-25 07:24:22'),
(2, 'TRF-202602-0002', 1, 2, 2, 1, 4, 2, 3, 356, 1, 99.0000, 44.00, 4356.00, '2026-02-25', '', 'received', 1, 1, '2026-02-25 08:15:14', 1, '2026-02-25 08:31:56', '2026-02-25 07:15:09', '2026-02-25 07:31:56');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `supplier_type_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `supplier_type_id`, `phone`, `email`, `address`, `contact_person`, `contact_phone`, `status`, `created_at`, `updated_at`) VALUES
(1, 'NGENDABANGA Faustin', 1, '0783361499', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-10 23:00:00', '2026-02-10 23:00:00'),
(2, 'MUKAMANA Leoncie', 1, '0784401631', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-11 23:00:00', '2026-02-11 23:00:00'),
(3, 'TUYIHORANE Valentine', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-12 23:00:00', '2026-02-12 23:00:00'),
(4, 'NTAHONKIRIYE Vianney', 1, '0725302751', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-13 23:00:00', '2026-02-13 23:00:00'),
(5, 'NSHIMIRYAYO Asiere', 1, '0723328139', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-14 23:00:00', '2026-02-14 23:00:00'),
(6, 'NKURANYABAHIZI Celestin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-15 23:00:00', '2026-02-15 23:00:00'),
(7, 'NIYIMPA Silas', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-16 23:00:00', '2026-02-16 23:00:00'),
(8, 'NGAMIJE Faustin', 1, '0785224076', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-17 23:00:00', '2026-02-17 23:00:00'),
(9, 'NSHIMIYIMANA Evaliste', 1, '0790732131', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-18 23:00:00', '2026-02-18 23:00:00'),
(10, 'NYIRANZIZA Dorothe', 1, '0781657061', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-19 23:00:00', '2026-02-19 23:00:00'),
(11, 'NSENGIYUMVA Innocent', 1, '0783245373', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-20 23:00:00', '2026-02-20 23:00:00'),
(12, 'HAKIZIMANA Athanase', 1, '0791487530', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-21 23:00:00', '2026-02-21 23:00:00'),
(13, 'BIZIYAREMYE Callixte', 1, '0722410919', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-22 23:00:00', '2026-02-22 23:00:00'),
(14, 'MURINDABIGWI Emmanuel', 1, '078651738', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-23 23:00:00', '2026-02-23 23:00:00'),
(15, 'NYAMWASA Callixte B', 1, '0784710824', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-24 23:00:00', '2026-02-24 23:00:00'),
(16, 'NDIKUMANA Emmanuel', 1, '0792026980', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-25 23:00:00', '2026-02-25 23:00:00'),
(17, 'NDAGIJIMANA Evaliste', 1, '0780115976', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-26 23:00:00', '2026-02-26 23:00:00'),
(18, 'KANYAMAHANGA Charles', 1, '0722866851', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-27 23:00:00', '2026-02-27 23:00:00'),
(19, 'NTAKIRUTIMANA Evaliste', 1, '0783288622', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-02-28 23:00:00', '2026-02-28 23:00:00'),
(20, 'IZABIRIZA Jeanette', 1, '0722462763', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-01 23:00:00', '2026-03-01 23:00:00'),
(21, 'NGENDAHIMANA Nicodemus', 1, '0782614878', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-02 23:00:00', '2026-03-02 23:00:00'),
(22, 'CYURINYANA Odette', 1, '0794070505', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-03 23:00:00', '2026-03-03 23:00:00'),
(23, 'NTITANGURANWA Vincent', 1, '0783415065', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-04 23:00:00', '2026-03-04 23:00:00'),
(24, 'MUGWANEZA Samuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-05 23:00:00', '2026-03-05 23:00:00'),
(25, 'NYIRARUBUMBA Seraphine', 1, '0722456471', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-06 23:00:00', '2026-03-06 23:00:00'),
(26, 'NYANDWI Emmanuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-07 23:00:00', '2026-03-07 23:00:00'),
(27, 'NTURANYABAHIZI', 1, '0783737631', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-08 23:00:00', '2026-03-08 23:00:00'),
(28, 'HABINGABWA Emmanuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-09 23:00:00', '2026-03-09 23:00:00'),
(29, 'IHORIHOZE Josue', 1, '0788200149', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-10 23:00:00', '2026-03-10 23:00:00'),
(30, 'KWASA Gaspard', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-11 23:00:00', '2026-03-11 23:00:00'),
(31, 'NYABYENDA Evaliste', 1, '0781065625', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-12 23:00:00', '2026-03-12 23:00:00'),
(32, 'MUKAMANA Elminate', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-13 23:00:00', '2026-03-13 23:00:00'),
(33, 'MUNYEMBABAZI Augistin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-14 23:00:00', '2026-03-14 23:00:00'),
(34, 'HABIMANA Vianney', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-15 23:00:00', '2026-03-15 23:00:00'),
(35, 'MUKANYANDWI Annociate', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-16 23:00:00', '2026-03-16 23:00:00'),
(36, 'NDAYAMBAJE Jean Claude', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-17 23:00:00', '2026-03-17 23:00:00'),
(37, 'MURERAMANZI Celestin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-18 23:00:00', '2026-03-18 23:00:00'),
(38, 'MANIRIHO Vincent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-19 23:00:00', '2026-03-19 23:00:00'),
(39, 'MUKANKAKA Seraphine', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-20 23:00:00', '2026-03-20 23:00:00'),
(40, 'MUKESHIMANA Agnes', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-21 23:00:00', '2026-03-21 23:00:00'),
(41, 'HABIMANA Samuel', 1, '0781433859', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-22 23:00:00', '2026-03-22 23:00:00'),
(42, 'RUGERABAGANWA Laurent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-23 23:00:00', '2026-03-23 23:00:00'),
(43, 'NDAGIJIMANA Innocent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-24 23:00:00', '2026-03-24 23:00:00'),
(44, 'RUZIGAMANZI Wellars', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-25 23:00:00', '2026-03-25 23:00:00'),
(45, 'RWAMBIKA Evariste', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-26 23:00:00', '2026-03-26 23:00:00'),
(46, 'NDIKUYEZE Innocent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-27 23:00:00', '2026-03-27 23:00:00'),
(47, 'NTAHOBAVUKIYE Syliveste', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-28 23:00:00', '2026-03-28 23:00:00'),
(48, 'RUGENZABATWA Ariel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-29 22:00:00', '2026-03-29 22:00:00'),
(49, 'NDAYISENGA Jean Bosco', 1, '0781704977', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-30 22:00:00', '2026-03-30 22:00:00'),
(50, 'UTABAZI Samuel', 1, '0724744903', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-03-31 22:00:00', '2026-03-31 22:00:00'),
(51, 'NKUNDABAGENZI Wesislas', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-01 22:00:00', '2026-04-01 22:00:00'),
(52, 'NSHIMIRYAYO Evaliste', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-02 22:00:00', '2026-04-02 22:00:00'),
(53, 'NZASABIMANA Evariste', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-03 22:00:00', '2026-04-03 22:00:00'),
(54, 'MUNYANKINDI Fidele', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-04 22:00:00', '2026-04-04 22:00:00'),
(55, 'IRINIGUMUGABO Luarent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-05 22:00:00', '2026-04-05 22:00:00'),
(56, 'NIYINDAMUTSA Pascal', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-06 22:00:00', '2026-04-06 22:00:00'),
(57, 'CYAMINANI Emmanuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-07 22:00:00', '2026-04-07 22:00:00'),
(58, 'GAKWAYA Augistin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-08 22:00:00', '2026-04-08 22:00:00'),
(59, 'MUNYESHEMA Theogene', 1, '0783068997', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-09 22:00:00', '2026-04-09 22:00:00'),
(60, 'GAKWAYA Vincent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-10 22:00:00', '2026-04-10 22:00:00'),
(61, 'BIZIMANA Reverien', 1, '0786811779', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-11 22:00:00', '2026-04-11 22:00:00'),
(62, 'HAKIZIMANA Celestin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-12 22:00:00', '2026-04-12 22:00:00'),
(63, 'NSABIMANA Callixte', 1, '0785876273', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-13 22:00:00', '2026-04-13 22:00:00'),
(64, 'MAFUREBO Sprien', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-14 22:00:00', '2026-04-14 22:00:00'),
(65, 'KABAGEMA Cecile', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-15 22:00:00', '2026-04-15 22:00:00'),
(66, 'MBONABUCYA Jean Baptiste', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-16 22:00:00', '2026-04-16 22:00:00'),
(67, 'NSENGIMANA Innocent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-17 22:00:00', '2026-04-17 22:00:00'),
(68, 'MUKAGASHUGI Seraphine', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-18 22:00:00', '2026-04-18 22:00:00'),
(69, 'NDAYIRAGIJE Emmanuel', 1, '0785757159', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-19 22:00:00', '2026-04-19 22:00:00'),
(70, 'BIZIYAREMYE Celestin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-20 22:00:00', '2026-04-20 22:00:00'),
(71, 'NYANDWI Felicien', 1, '0788217012', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-21 22:00:00', '2026-04-21 22:00:00'),
(72, 'KARENZO Faustin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-22 22:00:00', '2026-04-22 22:00:00'),
(73, 'SHUMBUSHA Etienne', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-23 22:00:00', '2026-04-23 22:00:00'),
(74, 'NSENGIMANA Athanase', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-24 22:00:00', '2026-04-24 22:00:00'),
(75, 'KANDANGA Stephanie', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-25 22:00:00', '2026-04-25 22:00:00'),
(76, 'NDAYISENGA Vianney', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-26 22:00:00', '2026-04-26 22:00:00'),
(77, 'SEBANANI Felecien', 1, '0787286398', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-27 22:00:00', '2026-04-27 22:00:00'),
(78, 'KAMANZI Vianney', 1, '0785876273', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-28 22:00:00', '2026-04-28 22:00:00'),
(79, 'NTEZIMANA Donatille', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-29 22:00:00', '2026-04-29 22:00:00'),
(80, 'NYIRAMANA Donatha', 1, '0736658001', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-04-30 22:00:00', '2026-04-30 22:00:00'),
(81, 'HABINDEMA Vianney', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-01 22:00:00', '2026-05-01 22:00:00'),
(82, 'GASIRABO Felicien', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-02 22:00:00', '2026-05-02 22:00:00'),
(83, 'MUTABAZI Viateur', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-03 22:00:00', '2026-05-03 22:00:00'),
(84, 'NZASABIMFURA Evariste', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-04 22:00:00', '2026-05-04 22:00:00'),
(85, 'RUSINGIZANDEKWE Elisa', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-05 22:00:00', '2026-05-05 22:00:00'),
(86, 'RUHANGINTWARI Damascene', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-06 22:00:00', '2026-05-06 22:00:00'),
(87, 'BUCYANA Vital', 1, '0784856363', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-07 22:00:00', '2026-05-07 22:00:00'),
(88, 'MUKAGASANA Julienne', 1, '0794702887', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-08 22:00:00', '2026-05-08 22:00:00'),
(89, 'RUBADUKA Pierre', 1, '07285575122', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-09 22:00:00', '2026-05-09 22:00:00'),
(90, 'NSANZIMANA Laurent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-10 22:00:00', '2026-05-10 22:00:00'),
(91, 'NIBAKURE Beatrice', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-11 22:00:00', '2026-05-11 22:00:00'),
(92, 'NYANDWI Vestine', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-12 22:00:00', '2026-05-12 22:00:00'),
(93, 'MINANI Frederic', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-13 22:00:00', '2026-05-13 22:00:00'),
(94, 'NZANYWAYIMANA Triphine', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-14 22:00:00', '2026-05-14 22:00:00'),
(95, 'HABIMANA Gaspard', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-15 22:00:00', '2026-05-15 22:00:00'),
(96, 'NSANZUMUHIRE Faustin', 1, '0781145494', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-16 22:00:00', '2026-05-16 22:00:00'),
(97, 'HAKIZIMANA Vincent', 1, '0786122987', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-17 22:00:00', '2026-05-17 22:00:00'),
(98, 'RWANDEKWE Emmanuel', 1, '0786062130', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-18 22:00:00', '2026-05-18 22:00:00'),
(99, 'MUKARWEGO Anastasie', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-19 22:00:00', '2026-05-19 22:00:00'),
(100, 'NIYAKIRE Claudine', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-20 22:00:00', '2026-05-20 22:00:00'),
(101, 'MUGANINTWARI Vincent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-21 22:00:00', '2026-05-21 22:00:00'),
(102, 'UWIHAYE Gerard', 1, '0786644367', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-22 22:00:00', '2026-05-22 22:00:00'),
(103, 'MURINDABIGWI Charles', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-23 22:00:00', '2026-05-23 22:00:00'),
(104, 'NYIRAKAMANA Vestine', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-24 22:00:00', '2026-05-24 22:00:00'),
(105, 'MUNYENSHUTI Marc', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-25 22:00:00', '2026-05-25 22:00:00'),
(106, 'NDINDABAHIZI Augistin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-26 22:00:00', '2026-05-26 22:00:00'),
(107, 'KAMBANDA Jean', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-27 22:00:00', '2026-05-27 22:00:00'),
(108, 'NDABAKURANYE Celestin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-28 22:00:00', '2026-05-28 22:00:00'),
(109, 'MUNYENTORE Vianney', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-29 22:00:00', '2026-05-29 22:00:00'),
(110, 'RUSINE Celestin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-30 22:00:00', '2026-05-30 22:00:00'),
(111, 'HABIMANA Daniel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-05-31 22:00:00', '2026-05-31 22:00:00'),
(112, 'UWIHOREYE Grace', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-01 22:00:00', '2026-06-01 22:00:00'),
(113, 'KAYIBANDA Innocent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-02 22:00:00', '2026-06-02 22:00:00'),
(114, 'HATUNGIMANA Claver', 1, '0782390573', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-03 22:00:00', '2026-06-03 22:00:00'),
(115, 'BICAMUMPAKA Joseph', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-04 22:00:00', '2026-06-04 22:00:00'),
(116, 'NYANDWI Innocent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-05 22:00:00', '2026-06-05 22:00:00'),
(117, 'NTAMUNOZA Jean Marie Vianney', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-06 22:00:00', '2026-06-06 22:00:00'),
(118, 'NDARISEKANA Fabien', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-07 22:00:00', '2026-06-07 22:00:00'),
(119, 'NZABIRINDA Anastase', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-08 22:00:00', '2026-06-08 22:00:00'),
(120, 'MURENGERANTWARI Callixte', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-09 22:00:00', '2026-06-09 22:00:00'),
(121, 'BARAYAVUGA Dominique', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-10 22:00:00', '2026-06-10 22:00:00'),
(122, 'BIMENYIMANA Augustave', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-11 22:00:00', '2026-06-11 22:00:00'),
(123, 'HABYARIMANA Juvenal', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-12 22:00:00', '2026-06-12 22:00:00'),
(124, 'MUTANGANA Revelien', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-13 22:00:00', '2026-06-13 22:00:00'),
(125, 'BAGANINEZA Evaliste', 1, '0781973941', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-14 22:00:00', '2026-06-14 22:00:00'),
(126, 'COOPERATIVE BANDEBEREHO', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-15 22:00:00', '2026-06-15 22:00:00'),
(127, 'HAKIZIMANA Dismas', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-16 22:00:00', '2026-06-16 22:00:00'),
(128, 'RUGERERO Ephurem', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-17 22:00:00', '2026-06-17 22:00:00'),
(129, 'HABIMANA Emmanuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-18 22:00:00', '2026-06-18 22:00:00'),
(130, 'HAKUZIYAREMYE Oliveir', 1, '0795224900', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-19 22:00:00', '2026-06-19 22:00:00'),
(131, 'BIZIMANA Emmanuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-20 22:00:00', '2026-06-20 22:00:00'),
(132, 'BIZIMANA Emmanuel b', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-21 22:00:00', '2026-06-21 22:00:00'),
(133, 'MURENGERANTWARI Emmanuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-22 22:00:00', '2026-06-22 22:00:00'),
(134, 'BUDURI Daniel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-23 22:00:00', '2026-06-23 22:00:00'),
(135, 'BATIRAGWA Fabien', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-24 22:00:00', '2026-06-24 22:00:00'),
(136, 'HANYURWIMFURA Alphonsine', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-25 22:00:00', '2026-06-25 22:00:00'),
(137, 'MUHIRE Pascal', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-26 22:00:00', '2026-06-26 22:00:00'),
(138, 'NUBAHIMFURA Seraphine', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-27 22:00:00', '2026-06-27 22:00:00'),
(139, 'AKIMANA Ester', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-28 22:00:00', '2026-06-28 22:00:00'),
(140, 'SIBOMANA Athanasie', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-29 22:00:00', '2026-06-29 22:00:00'),
(141, 'MURWANASHYAKA Yeledi', 1, '0789829871', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-06-30 22:00:00', '2026-06-30 22:00:00'),
(142, 'NKURUNZIZA Vianney', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-01 22:00:00', '2026-07-01 22:00:00'),
(143, 'MAZIMPAKA Celestin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-02 22:00:00', '2026-07-02 22:00:00'),
(144, 'HARERIMANA Viateur', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-03 22:00:00', '2026-07-03 22:00:00'),
(145, 'NYIRARUNYANGE Saverine', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-04 22:00:00', '2026-07-04 22:00:00'),
(146, 'SEBANANI  Innocent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-05 22:00:00', '2026-07-05 22:00:00'),
(147, 'MUKANDANGA Josephine', 1, '0781492019', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-06 22:00:00', '2026-07-06 22:00:00'),
(148, 'RUZIGAMANZI Celestin', 1, '0782586632', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-07 22:00:00', '2026-07-07 22:00:00'),
(149, 'HITIMANA Celestin', 1, '0786756913', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-08 22:00:00', '2026-07-08 22:00:00'),
(150, 'BEMBEREZA Narcisse', 1, '0788457071', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-09 22:00:00', '2026-07-09 22:00:00'),
(151, 'NAMBAZIMANA Felecite', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-10 22:00:00', '2026-07-10 22:00:00'),
(152, 'KAMUZINZI Michel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-11 22:00:00', '2026-07-11 22:00:00'),
(153, 'NDAYISABA Jean Marie Vianney', 1, '0781145563', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-12 22:00:00', '2026-07-12 22:00:00'),
(154, 'DUSABE Daphrose', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-13 22:00:00', '2026-07-13 22:00:00'),
(155, 'DUSABE Pascasie', 1, '0794012235', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-14 22:00:00', '2026-07-14 22:00:00'),
(156, 'MUKANKUSI Venancie', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-15 22:00:00', '2026-07-15 22:00:00'),
(157, 'NDEMEYE Emmanuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-16 22:00:00', '2026-07-16 22:00:00'),
(158, 'KURUFASHE Diedonne', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-17 22:00:00', '2026-07-17 22:00:00'),
(159, 'NYIRANSAGUYE Judithe', 1, '0781690990', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-18 22:00:00', '2026-07-18 22:00:00'),
(160, 'BICAMUMPAKA Flogen', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-19 22:00:00', '2026-07-19 22:00:00'),
(161, 'NYIRANSAGUYE Pentronille', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-20 22:00:00', '2026-07-20 22:00:00'),
(162, 'KAMANZI Laurent', 1, '0783151502', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-21 22:00:00', '2026-07-21 22:00:00'),
(163, 'HARINDINTWARI Thomas', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-22 22:00:00', '2026-07-22 22:00:00'),
(164, 'SEMINEGA Innocent', 1, '0787706438', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-23 22:00:00', '2026-07-23 22:00:00'),
(165, 'KABAGEMA Venancie', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-24 22:00:00', '2026-07-24 22:00:00'),
(166, 'AKAGARIKA Rutobwe', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-25 22:00:00', '2026-07-25 22:00:00'),
(167, 'MUKANDANGA Felecite', 1, '0798554250', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-26 22:00:00', '2026-07-26 22:00:00'),
(168, 'NGAYABERURA Emmanuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-27 22:00:00', '2026-07-27 22:00:00'),
(169, 'NZABIRINDA Vianney', 1, '0780272168', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-28 22:00:00', '2026-07-28 22:00:00'),
(170, 'NZABIRINDA Celestin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-29 22:00:00', '2026-07-29 22:00:00'),
(171, 'KAYIGIRE Callixte', 1, '07831517663', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-30 22:00:00', '2026-07-30 22:00:00'),
(172, 'NSANZABARINDA Laurent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-07-31 22:00:00', '2026-07-31 22:00:00'),
(173, 'KALIMUNDA Celestin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-01 22:00:00', '2026-08-01 22:00:00'),
(174, 'NDIZERA Afrodis', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-02 22:00:00', '2026-08-02 22:00:00'),
(175, 'NZABIRINDA Felicien', 1, '0786550095', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-03 22:00:00', '2026-08-03 22:00:00'),
(176, 'NKURANYABAHIZI Nason', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-04 22:00:00', '2026-08-04 22:00:00'),
(177, 'HAKIZIMANA Daniel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-05 22:00:00', '2026-08-05 22:00:00'),
(178, 'ZIKAMABAHARI Pascal', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-06 22:00:00', '2026-08-06 22:00:00'),
(179, 'NYIRINKWAYA Phillippe', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-07 22:00:00', '2026-08-07 22:00:00'),
(180, 'MUTABAZI Marc', 1, '0789407960', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-08 22:00:00', '2026-08-08 22:00:00'),
(181, 'MBANZABAGABO Samuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-09 22:00:00', '2026-08-09 22:00:00'),
(182, 'NZEYIMANA Domicien', 1, '0786742614', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-10 22:00:00', '2026-08-10 22:00:00'),
(183, 'MURINDABIGWI Evariste', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-11 22:00:00', '2026-08-11 22:00:00'),
(184, 'HAKIZIMANA Innocent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-12 22:00:00', '2026-08-12 22:00:00'),
(185, 'MINANI Jean Claude', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-13 22:00:00', '2026-08-13 22:00:00'),
(186, 'MUKABUTERA Immaculee', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-14 22:00:00', '2026-08-14 22:00:00'),
(187, 'NKURANYABAHIZI Daniel', 1, '0783737631', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-15 22:00:00', '2026-08-15 22:00:00'),
(188, 'UWIRAGIYE Angelique', 1, '0785071490', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-16 22:00:00', '2026-08-16 22:00:00'),
(189, 'SEBUGABO Faustin', 1, '0783784660', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-17 22:00:00', '2026-08-17 22:00:00'),
(190, 'COOPERATIVE ABAKUNDA KAWA A', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-18 22:00:00', '2026-08-18 22:00:00'),
(191, 'SHUMBUSHA Valentin', 1, '0780002260', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-19 22:00:00', '2026-08-19 22:00:00'),
(192, 'BIZUMUREMYI Innocent', 1, '0726197293', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-20 22:00:00', '2026-08-20 22:00:00'),
(193, 'MUKANYARWAYA Annonciatha', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-21 22:00:00', '2026-08-21 22:00:00'),
(194, 'NGAMIJE Athanase', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-22 22:00:00', '2026-08-22 22:00:00'),
(195, 'NSABABAGANWA Samuel', 1, '0783551690', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-23 22:00:00', '2026-08-23 22:00:00'),
(196, 'NYANDWI Annonciate', 1, '0787834517', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-24 22:00:00', '2026-08-24 22:00:00'),
(197, 'HABUMUREMYI Jacques', 1, '0782876716', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-25 22:00:00', '2026-08-25 22:00:00'),
(198, 'BIZIMANA Innocent', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-26 22:00:00', '2026-08-26 22:00:00'),
(199, 'NDINDABAHIZI Evariste', 1, '0786812276', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-27 22:00:00', '2026-08-27 22:00:00'),
(200, 'NTIGIRINZIGO Charles', 1, '0723575283', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-28 22:00:00', '2026-08-28 22:00:00'),
(201, 'HABARUREMA Celestin', 1, '0782037013', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-29 22:00:00', '2026-08-29 22:00:00'),
(202, 'COOPERATIVE ABAKUNDA KAWA B', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-30 22:00:00', '2026-08-30 22:00:00'),
(203, 'GAKWAYA Athanse', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-08-31 22:00:00', '2026-08-31 22:00:00'),
(204, 'Hosco ltd', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-01 22:00:00', '2026-09-01 22:00:00'),
(205, 'COOPERATIVE KURA UZIGAMA', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-02 22:00:00', '2026-09-02 22:00:00'),
(206, 'NYABENDA Dorocella', 1, '0794970374', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-03 22:00:00', '2026-09-03 22:00:00'),
(207, 'NYAMWASA Callixte A', 1, '0783129869', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-04 22:00:00', '2026-09-04 22:00:00'),
(208, 'NDINDABAGABO Emmanuel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-05 22:00:00', '2026-09-05 22:00:00'),
(209, 'MURINDABIGWI Celestin', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-06 22:00:00', '2026-09-06 22:00:00'),
(210, 'COOPERATIVE ABAHINZI BOROZI', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-07 22:00:00', '2026-09-07 22:00:00'),
(211, 'KAGENZA Jean Claude', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-08 22:00:00', '2026-09-08 22:00:00'),
(212, 'HAKUZWEYEZU Bosco', 1, '0794884059', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-09 22:00:00', '2026-09-09 22:00:00'),
(213, 'NSENGIYAREMYE Emmanuel', 1, '0798034755', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-10 22:00:00', '2026-09-10 22:00:00'),
(214, 'RUDURI Daniel', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-11 22:00:00', '2026-09-11 22:00:00'),
(215, 'NSENGIMANA Emmanuel', 1, '0783420739', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-12 22:00:00', '2026-09-12 22:00:00'),
(216, 'NYABYENDA Evariste c', 1, '', NULL, 'Cyahinda cws', NULL, NULL, 'active', '2026-09-13 22:00:00', '2026-09-13 22:00:00'),
(217, 'NIYONSHIMA Alphonse', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-14 22:00:00', '2026-09-14 22:00:00'),
(218, 'NIYOKWIZERWA John', 1, '0788964094', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-15 22:00:00', '2026-09-15 22:00:00'),
(219, 'TWAGIRUMUKAMA Emmanuel', 1, ' 0789725542', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-16 22:00:00', '2026-09-16 22:00:00'),
(220, 'MANIRAKIZA Emmanuel', 1, '0783546875', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-17 22:00:00', '2026-09-17 22:00:00'),
(221, 'HABIMANA David', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-18 22:00:00', '2026-09-18 22:00:00'),
(222, 'TWAGIRAYEZU Noel', 1, '0784142062', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-19 22:00:00', '2026-09-19 22:00:00'),
(223, 'BIZIYAREMYE Evariste', 1, '0788682225', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-20 22:00:00', '2026-09-20 22:00:00'),
(224, 'NYIRASIRIKARE Patricie', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-21 22:00:00', '2026-09-21 22:00:00'),
(225, 'NSANZABAGANWA Vianney', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-22 22:00:00', '2026-09-22 22:00:00'),
(226, 'RUGUNGIZA Appolinaire', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-23 22:00:00', '2026-09-23 22:00:00'),
(227, ' UWIHOREYE Frasie', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-24 22:00:00', '2026-09-24 22:00:00'),
(228, ' NDAGIJIMANA Patricie', 1, '0785484188', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-25 22:00:00', '2026-09-25 22:00:00'),
(229, 'GATOYA Gabriel', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-26 22:00:00', '2026-09-26 22:00:00'),
(230, 'UHIRIWE Israel', 1, '0795024163', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-27 22:00:00', '2026-09-27 22:00:00'),
(231, 'RUTARINDWA', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-28 22:00:00', '2026-09-28 22:00:00'),
(232, 'UMUTESI Georgette', 1, '0785205889', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-29 22:00:00', '2026-09-29 22:00:00'),
(233, 'MUJYANAMA Augistin', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-09-30 22:00:00', '2026-09-30 22:00:00'),
(234, 'NZABONIMPA Elyse', 1, '07820689479', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-01 22:00:00', '2026-10-01 22:00:00'),
(235, 'MUKAMENYIBYISI Beatha', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-02 22:00:00', '2026-10-02 22:00:00'),
(236, 'NDAGIJIMANA Jean Baptiste', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-03 22:00:00', '2026-10-03 22:00:00'),
(237, 'NTAKIRUTIMANA Girbert', 1, '0780222023', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-04 22:00:00', '2026-10-04 22:00:00'),
(238, 'SHIRIMPUMU Faustin', 1, '0786643819', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-05 22:00:00', '2026-10-05 22:00:00'),
(239, 'NSHIMIYIMANA Damascene', 1, '0786671368', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-06 22:00:00', '2026-10-06 22:00:00'),
(240, 'DUKUZUMUREMYI Silidio', 1, '0737031153', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-07 22:00:00', '2026-10-07 22:00:00'),
(241, 'MUTARUGERA Ferdinand', 1, '0785119810', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-08 22:00:00', '2026-10-08 22:00:00'),
(242, 'NDAHIMANA Athanase', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-09 22:00:00', '2026-10-09 22:00:00'),
(243, 'NTIRENGANYA Aminadab', 1, '0736190422', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-10 22:00:00', '2026-10-10 22:00:00'),
(244, 'MUNYANEZA Methoucella', 1, '0784919396', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-11 22:00:00', '2026-10-11 22:00:00'),
(245, 'MANIRAREBA Silidio', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-12 22:00:00', '2026-10-12 22:00:00'),
(246, 'KUBWIMANA Jean Pierre', 1, '0786213499', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-13 22:00:00', '2026-10-13 22:00:00'),
(247, 'KANYANGE Epiphanie', 1, '0780593597', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-14 22:00:00', '2026-10-14 22:00:00'),
(248, 'KANZIGA Therese', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-15 22:00:00', '2026-10-15 22:00:00'),
(249, 'BAGARAGAZA', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-16 22:00:00', '2026-10-16 22:00:00'),
(250, 'MUKARUSHEMA Pascasie', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-17 22:00:00', '2026-10-17 22:00:00'),
(251, 'TUYIZERE Olivier', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-18 22:00:00', '2026-10-18 22:00:00'),
(252, 'NDAYISABA Jean Damascene', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-19 22:00:00', '2026-10-19 22:00:00'),
(253, 'MUJAWAMARIYA Agripine', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-20 22:00:00', '2026-10-20 22:00:00'),
(254, 'MANIRAKIZA Damille', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-21 22:00:00', '2026-10-21 22:00:00'),
(255, 'UWIZEYIMANA Eugen', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-22 22:00:00', '2026-10-22 22:00:00'),
(256, 'MUJAMARIYA Florence', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-23 22:00:00', '2026-10-23 22:00:00'),
(257, 'NKOMEJEGUSABA Damascene', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-24 22:00:00', '2026-10-24 22:00:00'),
(258, 'UWAYEZU Emmanuel', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-25 23:00:00', '2026-10-25 23:00:00'),
(259, 'HITIYAREMYE Pierre', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-26 23:00:00', '2026-10-26 23:00:00'),
(260, 'MAZIMPAKA Alex', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-27 23:00:00', '2026-10-27 23:00:00'),
(261, 'NSENGIYUMVA Theogene', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-28 23:00:00', '2026-10-28 23:00:00'),
(262, 'MUSANABAGENI Verani', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-29 23:00:00', '2026-10-29 23:00:00'),
(263, 'NYIRINGANGO Athanase', 1, '0782226466', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-30 23:00:00', '2026-10-30 23:00:00'),
(264, 'MUKANOHELI Claudine', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-10-31 23:00:00', '2026-10-31 23:00:00'),
(265, 'UWIMANA Jeanette', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-01 23:00:00', '2026-11-01 23:00:00'),
(266, 'UWIZEYIMANA Janvier', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-02 23:00:00', '2026-11-02 23:00:00'),
(267, 'HAGUMINSHUTI Pierre', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-03 23:00:00', '2026-11-03 23:00:00'),
(268, 'HATEGEKIMANA Wellars', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-04 23:00:00', '2026-11-04 23:00:00'),
(269, 'NSHIMIYIMANA Bosco', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-05 23:00:00', '2026-11-05 23:00:00'),
(270, 'NGABOYIMBERE Vedaste', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-06 23:00:00', '2026-11-06 23:00:00'),
(271, 'HABIMANA Alphonse', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-07 23:00:00', '2026-11-07 23:00:00'),
(272, 'IRAKIZA Lonard', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-08 23:00:00', '2026-11-08 23:00:00'),
(273, 'MUKAMUZUNGU Domicien', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-09 23:00:00', '2026-11-09 23:00:00'),
(274, 'MUKANKUSI Gloria', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-10 23:00:00', '2026-11-10 23:00:00'),
(275, 'BIMENYIMANA Joseph', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-11 23:00:00', '2026-11-11 23:00:00'),
(276, 'DUSENGIMANA Jean', 1, '0788651007', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-12 23:00:00', '2026-11-12 23:00:00'),
(277, 'MPAKANIYE Louis', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-13 23:00:00', '2026-11-13 23:00:00'),
(278, 'BAVUGE NDARUHUTSE', 1, '0785311374', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-14 23:00:00', '2026-11-14 23:00:00'),
(279, 'DUSHIMINA Bosco', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-15 23:00:00', '2026-11-15 23:00:00'),
(280, 'BAJENEZA Emmanuel', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-16 23:00:00', '2026-11-16 23:00:00'),
(281, 'NZIMENYERA Evariste', 1, '0781033523', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-17 23:00:00', '2026-11-17 23:00:00'),
(282, 'NDARUHUTSE Bernard', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-18 23:00:00', '2026-11-18 23:00:00'),
(283, 'MUKAMUNEZA Jacqueline', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-19 23:00:00', '2026-11-19 23:00:00'),
(284, 'KALISA Donath', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-20 23:00:00', '2026-11-20 23:00:00'),
(285, 'KANKINDI Costasia', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-21 23:00:00', '2026-11-21 23:00:00'),
(286, 'MUGARAGU Andre', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-22 23:00:00', '2026-11-22 23:00:00'),
(287, 'CYUBAHIRO Vedaste', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-23 23:00:00', '2026-11-23 23:00:00'),
(288, 'IYAKAREMYE Germain', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-24 23:00:00', '2026-11-24 23:00:00'),
(289, 'HABIYAREMYE Daniel', 1, '0784338036', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-25 23:00:00', '2026-11-25 23:00:00'),
(290, 'NTAHOBARI Evariste', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-26 23:00:00', '2026-11-26 23:00:00'),
(291, 'BAMENYUBWABO Donatien', 1, '0787028694', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-27 23:00:00', '2026-11-27 23:00:00'),
(292, 'GASHEMA Ferdinand', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-28 23:00:00', '2026-11-28 23:00:00'),
(293, 'BICAMUMPAKA Thadeo', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-29 23:00:00', '2026-11-29 23:00:00'),
(294, 'BASEKA Thomas', 1, '0782900340', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-11-30 23:00:00', '2026-11-30 23:00:00'),
(295, 'BUTERA Stanslus', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-01 23:00:00', '2026-12-01 23:00:00'),
(296, 'HABIYAKARE Emmanuel', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-02 23:00:00', '2026-12-02 23:00:00'),
(297, 'TWIZERIMANA Venuste', 1, '0788705125', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-03 23:00:00', '2026-12-03 23:00:00'),
(298, 'NGEZAHIMANA Caleb', 1, '0783302444', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-04 23:00:00', '2026-12-04 23:00:00'),
(299, 'NKURIKIYIMANA Innocent', 1, '0788584079', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-05 23:00:00', '2026-12-05 23:00:00'),
(300, 'SHUMBUSHO Joseph', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-06 23:00:00', '2026-12-06 23:00:00'),
(301, 'NDAYISABA Tumayine', 1, '0783162332', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-07 23:00:00', '2026-12-07 23:00:00'),
(302, 'NAMUGIZE Filomena', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-08 23:00:00', '2026-12-08 23:00:00'),
(303, 'AHISHAKIYE Emmanuel', 1, '0788196698', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-09 23:00:00', '2026-12-09 23:00:00'),
(304, 'BAMPORIKI Jean Damascene', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-10 23:00:00', '2026-12-10 23:00:00'),
(305, 'HABINTWARI Jean Paul', 1, '0785447047', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-11 23:00:00', '2026-12-11 23:00:00'),
(306, 'RYAVUZEBOSE Amiel', 1, '0790048949', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-12 23:00:00', '2026-12-12 23:00:00'),
(307, 'NGABONZIZA Audace', 1, '0788777063', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-13 23:00:00', '2026-12-13 23:00:00'),
(308, 'NZEYIMANA Siliro', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-14 23:00:00', '2026-12-14 23:00:00'),
(309, 'MUKANEZA Jacqueline', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-15 23:00:00', '2026-12-15 23:00:00'),
(310, 'MUMPAKA Faustin', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-16 23:00:00', '2026-12-16 23:00:00'),
(311, 'TWAGIRAYEZU Vincent', 1, '0789261813', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-17 23:00:00', '2026-12-17 23:00:00'),
(312, 'COOPERATIVE GAHARA', 1, '', NULL, 'Cyamabuye cws', NULL, NULL, 'active', '2026-12-18 23:00:00', '2026-12-18 23:00:00'),
(313, 'NSABAMUNGU Eugen', 1, '0725502085', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-19 23:00:00', '2026-12-19 23:00:00'),
(314, 'NIYIGABA Elaste', 1, '0784145529', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-20 23:00:00', '2026-12-20 23:00:00'),
(315, 'MUNYABURANGA Caetan', 1, '0724372354', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-21 23:00:00', '2026-12-21 23:00:00'),
(316, 'HITIMANA Jean', 1, '0796431040', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-22 23:00:00', '2026-12-22 23:00:00'),
(317, 'NZARAMYIMANA XXX', 1, '0783223128', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-23 23:00:00', '2026-12-23 23:00:00'),
(318, 'NSHIMIYIMANA theophille ', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-24 23:00:00', '2026-12-24 23:00:00'),
(319, 'SIBORUREMA Muhammed', 1, '0732600187', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-25 23:00:00', '2026-12-25 23:00:00'),
(320, 'BIZUMUREMYI Felix', 1, '0726877601', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-26 23:00:00', '2026-12-26 23:00:00'),
(321, ' NSAGUYE Sylvan', 1, '0788736296', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-27 23:00:00', '2026-12-27 23:00:00'),
(322, ' URAYENEZA Claude', 1, '0782865481', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-28 23:00:00', '2026-12-28 23:00:00'),
(323, 'HAGUMA Joel', 1, '0788668423', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-29 23:00:00', '2026-12-29 23:00:00'),
(324, 'MUKANYARWASA Philomene', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-30 23:00:00', '2026-12-30 23:00:00'),
(325, ' BIMENYIMANA Jonas', 1, '0781615171', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2026-12-31 23:00:00', '2026-12-31 23:00:00'),
(326, 'NTAGUNGIRA Deny ', 1, '0732143474', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-01 23:00:00', '2027-01-01 23:00:00'),
(327, 'KAMANA Froduard', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-02 23:00:00', '2027-01-02 23:00:00'),
(328, 'BARUTWANAYO Pascal', 1, '0783156307', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-03 23:00:00', '2027-01-03 23:00:00'),
(329, 'BARABWIRIZA Jean Baptiste', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-04 23:00:00', '2027-01-04 23:00:00'),
(330, ' UWIRAGIYE Denyse', 1, '072442845', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-05 23:00:00', '2027-01-05 23:00:00'),
(331, ' NIYONSABA Gerard', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-06 23:00:00', '2027-01-06 23:00:00'),
(332, 'NIYONSABA Celestin', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-07 23:00:00', '2027-01-07 23:00:00'),
(333, ' MUKARUGABIRO Claudine', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-08 23:00:00', '2027-01-08 23:00:00'),
(334, ' UWAMARIYA Philomene', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-09 23:00:00', '2027-01-09 23:00:00'),
(335, ' MUGISHA Alexis', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-10 23:00:00', '2027-01-10 23:00:00'),
(336, ' MUKANGENDO Josepha', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-11 23:00:00', '2027-01-11 23:00:00'),
(337, 'MVUNABANDI Athanase', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-12 23:00:00', '2027-01-12 23:00:00'),
(338, 'MUKAMUGEMA Marie', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-13 23:00:00', '2027-01-13 23:00:00'),
(339, 'SIBOMANA Jean Bosco', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-14 23:00:00', '2027-01-14 23:00:00'),
(340, 'UWITONZE Daniel', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-15 23:00:00', '2027-01-15 23:00:00'),
(341, ' KANYESHYAMBA Damascene', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-16 23:00:00', '2027-01-16 23:00:00'),
(342, 'MUKAGATARE Domitille', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-17 23:00:00', '2027-01-17 23:00:00'),
(343, 'MUKANDUTIYE Venatie', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-18 23:00:00', '2027-01-18 23:00:00'),
(344, 'NSAGUYE Bosco', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-19 23:00:00', '2027-01-19 23:00:00'),
(345, 'NIZEYIMANA Pierre', 1, '0723039968', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-20 23:00:00', '2027-01-20 23:00:00'),
(346, ' BASOMINGERA Andre', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-21 23:00:00', '2027-01-21 23:00:00'),
(347, 'KAYUMBA Fidel', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-22 23:00:00', '2027-01-22 23:00:00'),
(348, ' MUKAMASABO Drocelle', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-23 23:00:00', '2027-01-23 23:00:00'),
(349, 'MINANI', 1, '0793071560', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-24 23:00:00', '2027-01-24 23:00:00'),
(350, 'NDAYISABA Emmanuel ', 1, '0726890304', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-25 23:00:00', '2027-01-25 23:00:00'),
(351, 'MANISHIMWE Regis', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-26 23:00:00', '2027-01-26 23:00:00'),
(352, ' NGABONZIZA Audace', 1, '0788777063', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-27 23:00:00', '2027-01-27 23:00:00'),
(353, ' KAGABO Andre', 1, '0789647176', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-28 23:00:00', '2027-01-28 23:00:00'),
(354, ' UZABAKIRIHO Cyprien', 1, '0787691103', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-29 23:00:00', '2027-01-29 23:00:00'),
(355, 'NIYIGENA Dominique', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-30 23:00:00', '2027-01-30 23:00:00'),
(356, ' MUKANTAGANDA Marie Beatrice ', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-01-31 23:00:00', '2027-01-31 23:00:00'),
(357, 'MUGENZI James', 1, '0784938032', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-01 23:00:00', '2027-02-01 23:00:00'),
(358, 'UWAMAHORO Christine', 1, '0783740951', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-02 23:00:00', '2027-02-02 23:00:00'),
(359, ' MUHIRWA Canisius', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-03 23:00:00', '2027-02-03 23:00:00'),
(360, 'NTAWIGENERA Edmond ', 1, '0723950021', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-04 23:00:00', '2027-02-04 23:00:00'),
(361, 'MUKARUSINE Florida', 1, '0726600741', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-05 23:00:00', '2027-02-05 23:00:00'),
(362, ' MUSABYIMANA Immacule', 1, '0726463530', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-06 23:00:00', '2027-02-06 23:00:00'),
(363, 'KABAGWIRA Veneranda', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-07 23:00:00', '2027-02-07 23:00:00'),
(364, 'BIMENYIMANA Ernest', 1, '0733848720', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-08 23:00:00', '2027-02-08 23:00:00'),
(365, ' MUKABUGINGO Pelagie', 1, '0787690635', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-09 23:00:00', '2027-02-09 23:00:00'),
(366, 'KUBWIMANA Jean Baptiste', 1, '0781730739', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-10 23:00:00', '2027-02-10 23:00:00'),
(367, ' BIHOYIKI Jeanne', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-11 23:00:00', '2027-02-11 23:00:00'),
(368, 'MUKAGATETE Clarisse', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-12 23:00:00', '2027-02-12 23:00:00'),
(369, 'NYIRAREKAYABO Selaphine', 1, '0788998935', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-13 23:00:00', '2027-02-13 23:00:00'),
(370, 'BUKOMBE Jean Damascene', 1, '0789652766', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-14 23:00:00', '2027-02-14 23:00:00'),
(371, 'NYIRABAGWIZA Veneranda', 1, '0783138383', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-15 23:00:00', '2027-02-15 23:00:00'),
(372, 'MBARUBUKEYE Claude', 1, '0781851020', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-16 23:00:00', '2027-02-16 23:00:00'),
(373, 'MUKARUSAGARA Alphonsine', 1, ' 0798236012', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-17 23:00:00', '2027-02-17 23:00:00'),
(374, ' NYIRAMANA Evelyne', 1, '0738952521 ', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-18 23:00:00', '2027-02-18 23:00:00'),
(375, 'HABIYAKARE David', 1, '0726890292', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-19 23:00:00', '2027-02-19 23:00:00'),
(376, 'UWABABYEYI GELARDINE', 1, '0738952521', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-20 23:00:00', '2027-02-20 23:00:00'),
(377, 'RUTAGANDA Jean  Pierre', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-21 23:00:00', '2027-02-21 23:00:00'),
(378, 'BARAVUGA Gerard', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-22 23:00:00', '2027-02-22 23:00:00'),
(379, 'NDAYISABA Callixte', 1, '0788592433 ', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-23 23:00:00', '2027-02-23 23:00:00'),
(380, 'MUKANKUSI Aloysie', 1, ' 0725155977', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-24 23:00:00', '2027-02-24 23:00:00'),
(381, 'RUTAYISIRE Mathieu', 1, '0723066010', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-25 23:00:00', '2027-02-25 23:00:00'),
(382, ' NIYOMANDWA Bertilde', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-26 23:00:00', '2027-02-26 23:00:00'),
(383, ' BIRAMUKA J.marie', 1, '0729335368', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-27 23:00:00', '2027-02-27 23:00:00'),
(384, 'MUKABAGABO Charlotte', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-02-28 23:00:00', '2027-02-28 23:00:00'),
(385, 'MUTUYIMANA Madaleine', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-01 23:00:00', '2027-03-01 23:00:00'),
(386, 'NZINDUKUYIMANA Eric', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-02 23:00:00', '2027-03-02 23:00:00'),
(387, 'UWIMANA Eugenie', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-03 23:00:00', '2027-03-03 23:00:00'),
(388, 'NDAYISABA Fabrice ', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-04 23:00:00', '2027-03-04 23:00:00');
INSERT INTO `suppliers` (`id`, `name`, `supplier_type_id`, `phone`, `email`, `address`, `contact_person`, `contact_phone`, `status`, `created_at`, `updated_at`) VALUES
(389, 'MBONIMPAYE Marceline', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-05 23:00:00', '2027-03-05 23:00:00'),
(390, 'KAYIBANDA Theogene', 1, '0728597294', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-06 23:00:00', '2027-03-06 23:00:00'),
(391, ' NDUNGUTSE Martin ', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-07 23:00:00', '2027-03-07 23:00:00'),
(392, 'NKERAMUGABA Diogene', 1, '0784145529', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-08 23:00:00', '2027-03-08 23:00:00'),
(393, 'KARISA Vianney', 1, '0794160401', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-09 23:00:00', '2027-03-09 23:00:00'),
(394, ' IRADUKUNDA Elyse', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-10 23:00:00', '2027-03-10 23:00:00'),
(395, 'NGENDAHIMANA Samuel', 1, '0781700817', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-11 23:00:00', '2027-03-11 23:00:00'),
(396, 'MUREKEZI Anastase', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-12 23:00:00', '2027-03-12 23:00:00'),
(397, 'MUKARUGOMWA Esperance', 1, '07896321045', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-13 23:00:00', '2027-03-13 23:00:00'),
(398, 'NSANGANDE Jean de Dieu', 1, '0795696488', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-14 23:00:00', '2027-03-14 23:00:00'),
(399, 'NKERAMIHIGO Jonathan', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-15 23:00:00', '2027-03-15 23:00:00'),
(400, 'NDAGIJIMANA Athanase', 1, '0782955141', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-16 23:00:00', '2027-03-16 23:00:00'),
(401, 'HABIMANA Gilbert', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-17 23:00:00', '2027-03-17 23:00:00'),
(402, ' KABAGEMA Tharcisse', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-18 23:00:00', '2027-03-18 23:00:00'),
(403, 'MUNYAKAYANZA Celestin', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-19 23:00:00', '2027-03-19 23:00:00'),
(404, 'MUJAWAYEZU Florance', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-20 23:00:00', '2027-03-20 23:00:00'),
(405, 'HABIMANA Boniface', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-21 23:00:00', '2027-03-21 23:00:00'),
(406, 'KARASANYI Gedeon', 1, '0788795515', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-22 23:00:00', '2027-03-22 23:00:00'),
(407, 'BANKUNDIYE Gorethe', 1, '0735346767', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-23 23:00:00', '2027-03-23 23:00:00'),
(408, 'HABUMUGISHA Dany', 1, '0783244820', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-24 23:00:00', '2027-03-24 23:00:00'),
(409, 'MUKANGANGO Domina', 1, '0727045555', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-25 23:00:00', '2027-03-25 23:00:00'),
(410, 'MUKARUGINA Beatrice', 1, '0732438410', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-26 23:00:00', '2027-03-26 23:00:00'),
(411, 'NSENGIMANA Benjamin', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-27 23:00:00', '2027-03-27 23:00:00'),
(412, 'NIYOMWUNGERI Philemon', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-28 22:00:00', '2027-03-28 22:00:00'),
(413, 'SIBOBUGINGO Jean D\'Amour', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-29 22:00:00', '2027-03-29 22:00:00'),
(414, 'HAKIZIMANA Enock', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-30 22:00:00', '2027-03-30 22:00:00'),
(415, 'NTAWUYIRUSHINTEGE Gerald', 1, '0785244666', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-03-31 22:00:00', '2027-03-31 22:00:00'),
(416, 'KABANO Francois', 1, '0785404601', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-01 22:00:00', '2027-04-01 22:00:00'),
(417, 'HABYARIMANA Jean Pierre', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-02 22:00:00', '2027-04-02 22:00:00'),
(418, 'NDAHIMANA Andre', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-03 22:00:00', '2027-04-03 22:00:00'),
(419, 'MUKANTABANA Julie', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-04 22:00:00', '2027-04-04 22:00:00'),
(420, 'UWIHOREYE Maurice', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-05 22:00:00', '2027-04-05 22:00:00'),
(421, 'NYIRAMINANI Marie Rose', 1, '0726511009', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-06 22:00:00', '2027-04-06 22:00:00'),
(422, 'RUZINDANA Cosma', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-07 22:00:00', '2027-04-07 22:00:00'),
(423, 'ISHIMWE Wellars', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-08 22:00:00', '2027-04-08 22:00:00'),
(424, 'GASHIRABAKE Janvier', 1, '0784305325', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-09 22:00:00', '2027-04-09 22:00:00'),
(425, 'NDAGIJIMANA Osiel', 1, '0783163022', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-10 22:00:00', '2027-04-10 22:00:00'),
(426, 'MANIRAREBA Jacqueline', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-11 22:00:00', '2027-04-11 22:00:00'),
(427, 'MUNYEMANA Jean Claude', 1, '0721578558', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-12 22:00:00', '2027-04-12 22:00:00'),
(428, 'NDAYISENGA Emmanuel', 1, '0726747065', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-13 22:00:00', '2027-04-13 22:00:00'),
(429, 'MUREBWAYIRE Cecile', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-14 22:00:00', '2027-04-14 22:00:00'),
(430, 'HABIYAREMYE Jean', 1, '0786387033', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-15 22:00:00', '2027-04-15 22:00:00'),
(431, 'MUSHIMIYIMANA Jeanne', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-16 22:00:00', '2027-04-16 22:00:00'),
(432, 'MURWANASHYAKA Erneste', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-17 22:00:00', '2027-04-17 22:00:00'),
(433, 'NSHIMIYIMANA Valens', 1, '0782128979', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-18 22:00:00', '2027-04-18 22:00:00'),
(434, 'HAKIZIMANA Vital', 1, '0783012844', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-19 22:00:00', '2027-04-19 22:00:00'),
(435, 'UWIMANA Theogene', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-20 22:00:00', '2027-04-20 22:00:00'),
(436, 'MUGISHA Olivier', 1, '0785503094', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-21 22:00:00', '2027-04-21 22:00:00'),
(437, 'NYIRANSABIMANA Denyse', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-22 22:00:00', '2027-04-22 22:00:00'),
(438, 'AKIMANA Nadine', 1, '0789138272', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-23 22:00:00', '2027-04-23 22:00:00'),
(439, 'KAYINAMURA Isae', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-24 22:00:00', '2027-04-24 22:00:00'),
(440, 'BASOMINGERA Theogene', 1, '073461194', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-25 22:00:00', '2027-04-25 22:00:00'),
(441, 'NIYOMUGABO Thadeo', 1, '0793395305', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-26 22:00:00', '2027-04-26 22:00:00'),
(442, 'NYANDWI Faustin', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-27 22:00:00', '2027-04-27 22:00:00'),
(443, 'MUKAMANA Alphonsine', 1, '0731215683', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-28 22:00:00', '2027-04-28 22:00:00'),
(444, 'KAMANA ', 1, '0732115683', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-29 22:00:00', '2027-04-29 22:00:00'),
(445, 'NKURUNZIZA Ephurahim', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-04-30 22:00:00', '2027-04-30 22:00:00'),
(446, 'KAREMANGINGO Juvenal', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-01 22:00:00', '2027-05-01 22:00:00'),
(447, 'KARANGWA Etienne', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-02 22:00:00', '2027-05-02 22:00:00'),
(448, 'KAYIHURA Private', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-03 22:00:00', '2027-05-03 22:00:00'),
(449, 'IGIRIMPUHWE Honore', 1, '0793807803', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-04 22:00:00', '2027-05-04 22:00:00'),
(450, 'NTIHINYURWA Emmanuel', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-05 22:00:00', '2027-05-05 22:00:00'),
(451, 'NIYITEGEKA Charlotte', 1, '0781368068', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-06 22:00:00', '2027-05-06 22:00:00'),
(452, 'UWINGENEYE Valentine', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-07 22:00:00', '2027-05-07 22:00:00'),
(453, 'ICYIZANYE Epaphrodite', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-08 22:00:00', '2027-05-08 22:00:00'),
(454, 'MUKANYANGEZI Alice', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-09 22:00:00', '2027-05-09 22:00:00'),
(455, 'UZAMUKUNDA Monique', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-10 22:00:00', '2027-05-10 22:00:00'),
(456, 'KATABOGAMA Therese', 1, '0780960765', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-11 22:00:00', '2027-05-11 22:00:00'),
(457, 'MUKATABARO Venancie', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-12 22:00:00', '2027-05-12 22:00:00'),
(458, 'MUKAKARANGWA', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-13 22:00:00', '2027-05-13 22:00:00'),
(459, 'KAMAYANA Albert', 1, '0786913119', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-14 22:00:00', '2027-05-14 22:00:00'),
(460, 'NYABYENDA Jean Damascene', 1, '0784337007', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-15 22:00:00', '2027-05-15 22:00:00'),
(461, 'MUSONI Modeste', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-16 22:00:00', '2027-05-16 22:00:00'),
(462, 'RWESA Patrick', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-17 22:00:00', '2027-05-17 22:00:00'),
(463, 'NTAWANGAHEZA Jean Paul', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-18 22:00:00', '2027-05-18 22:00:00'),
(464, 'MUGABOWINDEKWE Protogene', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-19 22:00:00', '2027-05-19 22:00:00'),
(465, 'MUKANYANDWI Adeline', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-20 22:00:00', '2027-05-20 22:00:00'),
(466, 'KARUGAMBA Drocella', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-21 22:00:00', '2027-05-21 22:00:00'),
(467, 'NDAHAYO Jean Claude', 1, '0726958619', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-22 22:00:00', '2027-05-22 22:00:00'),
(468, 'KARANGWA Jerome', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-23 22:00:00', '2027-05-23 22:00:00'),
(469, 'MBARAGA Emmanuel', 1, '0780528042', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-24 22:00:00', '2027-05-24 22:00:00'),
(470, 'MUSABYIMANA Alphonsine', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-25 22:00:00', '2027-05-25 22:00:00'),
(471, 'YANKURIJE Francine', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-26 22:00:00', '2027-05-26 22:00:00'),
(472, 'NGEZAHAYO Damascene', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-27 22:00:00', '2027-05-27 22:00:00'),
(473, 'MUHIRWA Justin', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-28 22:00:00', '2027-05-28 22:00:00'),
(474, 'MUNYARUBIBI J.Nepomuscene', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-29 22:00:00', '2027-05-29 22:00:00'),
(475, 'MBYAYINGABO Egide', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-30 22:00:00', '2027-05-30 22:00:00'),
(476, 'NTEZIMANA Jean De Dien', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-05-31 22:00:00', '2027-05-31 22:00:00'),
(477, 'KABERA Japhet', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-01 22:00:00', '2027-06-01 22:00:00'),
(478, 'GASARASI Jean Baptise', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-02 22:00:00', '2027-06-02 22:00:00'),
(479, 'NZAYISENGA Emmanuel', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-03 22:00:00', '2027-06-03 22:00:00'),
(480, 'NIKUZE Annonciata', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-04 22:00:00', '2027-06-04 22:00:00'),
(481, 'MUKASHYAKA Therese', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-05 22:00:00', '2027-06-05 22:00:00'),
(482, 'MUKANDUTIYE Thacienne', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-06 22:00:00', '2027-06-06 22:00:00'),
(483, 'NYANDWI Silas', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-07 22:00:00', '2027-06-07 22:00:00'),
(484, 'KAGENZI Denis', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-08 22:00:00', '2027-06-08 22:00:00'),
(485, 'SINDAYIGAYA Ezechias', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-09 22:00:00', '2027-06-09 22:00:00'),
(486, 'NAYITURIKI Lea', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-10 22:00:00', '2027-06-10 22:00:00'),
(487, 'HAVUGIMANA Joseph', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-11 22:00:00', '2027-06-11 22:00:00'),
(488, 'MAJYAMBERE Hodard', 1, '0721374812', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-12 22:00:00', '2027-06-12 22:00:00'),
(489, 'BAZIZANE Beatrice', 1, '', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-13 22:00:00', '2027-06-13 22:00:00'),
(490, 'HABUMUGISHA Danny', 1, '0783244820', NULL, 'Kibirizi cws', NULL, NULL, 'active', '2027-06-14 22:00:00', '2027-06-14 22:00:00'),
(491, 'NISHIMWE Jeanne', 1, '0786843562', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-15 22:00:00', '2027-06-15 22:00:00'),
(492, 'NIYIBIKORA Zabron', 1, '0788213041', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-16 22:00:00', '2027-06-16 22:00:00'),
(493, 'TUYISENGE Tharcisse', 1, '0788815375', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-17 22:00:00', '2027-06-17 22:00:00'),
(494, 'DUSHIMIMANA Samuel', 1, '0789702742', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-18 22:00:00', '2027-06-18 22:00:00'),
(495, 'MASASU Innocent', 1, '0785188308', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-19 22:00:00', '2027-06-19 22:00:00'),
(496, 'DUSHIMIMANA Daniel', 1, '0782236600', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-20 22:00:00', '2027-06-20 22:00:00'),
(497, 'NDAYISABA Fidele', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-21 22:00:00', '2027-06-21 22:00:00'),
(498, 'KARANGWA Sauveur', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-22 22:00:00', '2027-06-22 22:00:00'),
(499, 'HABIMANA Martine', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-23 22:00:00', '2027-06-23 22:00:00'),
(500, 'UWANYIRAKURU Dorothea', 1, '078594454', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-24 22:00:00', '2027-06-24 22:00:00'),
(501, 'MUNYURANGABO Leonard', 1, '0735733268', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-25 22:00:00', '2027-06-25 22:00:00'),
(502, 'BIZUWITEKA Jean', 1, '0788937864', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-26 22:00:00', '2027-06-26 22:00:00'),
(503, 'MUKANTAGENGWA Cecile', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-27 22:00:00', '2027-06-27 22:00:00'),
(504, 'NSENGIMANA Samuel', 1, '0788625988', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-28 22:00:00', '2027-06-28 22:00:00'),
(505, 'DUSABE Etienne', 1, '0783662165', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-29 22:00:00', '2027-06-29 22:00:00'),
(506, 'Protogene', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-06-30 22:00:00', '2027-06-30 22:00:00'),
(507, 'MUKAKAYUMBA Odette', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-01 22:00:00', '2027-07-01 22:00:00'),
(508, 'GASHUMBA Simmo Pierre A', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-02 22:00:00', '2027-07-02 22:00:00'),
(509, 'NIYOYITA Vincent', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-03 22:00:00', '2027-07-03 22:00:00'),
(510, 'MINANI Eduard', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-04 22:00:00', '2027-07-04 22:00:00'),
(511, 'COOPERATIVE NYAMIYAGA', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-05 22:00:00', '2027-07-05 22:00:00'),
(512, 'MUSABYIMANA Augistin', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-06 22:00:00', '2027-07-06 22:00:00'),
(513, 'KASINE', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-07 22:00:00', '2027-07-07 22:00:00'),
(514, 'TWAHIRWA Augistin', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-08 22:00:00', '2027-07-08 22:00:00'),
(515, 'NYIRAMAJANGWE', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-09 22:00:00', '2027-07-09 22:00:00'),
(516, 'MUGENZI Eduard', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-10 22:00:00', '2027-07-10 22:00:00'),
(517, 'MUHAWENIMANA Donatha', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-11 22:00:00', '2027-07-11 22:00:00'),
(518, 'NTEZIRYAYO Daniel', 1, '0789229330', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-12 22:00:00', '2027-07-12 22:00:00'),
(519, 'Theobard', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-13 22:00:00', '2027-07-13 22:00:00'),
(520, 'MUKAMANA Valerie', 1, '0784565667', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-14 22:00:00', '2027-07-14 22:00:00'),
(521, 'NZARAMBA Jean', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-15 22:00:00', '2027-07-15 22:00:00'),
(522, 'UWIDUHAYE Claude', 1, '0781663394', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-16 22:00:00', '2027-07-16 22:00:00'),
(523, 'KAREKEZI Alex B', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-17 22:00:00', '2027-07-17 22:00:00'),
(524, 'NSENGIMANA Jean Claude B', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-18 22:00:00', '2027-07-18 22:00:00'),
(525, 'MUNYAKAZI', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-19 22:00:00', '2027-07-19 22:00:00'),
(526, 'GATSINZI Jean Claude', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-20 22:00:00', '2027-07-20 22:00:00'),
(527, 'MURERA Jean Damascene', 1, '0785995245', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-21 22:00:00', '2027-07-21 22:00:00'),
(528, 'MASENGESHO Felicien', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-22 22:00:00', '2027-07-22 22:00:00'),
(529, 'NSHIMIYIMANA Alphonse', 1, '0783272441', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-23 22:00:00', '2027-07-23 22:00:00'),
(530, 'MUNYAMPETA Jean Pierre', 1, '0785188342', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-24 22:00:00', '2027-07-24 22:00:00'),
(531, 'RURANGWA Jean Bosco', 1, '0783469509', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-25 22:00:00', '2027-07-25 22:00:00'),
(532, 'UZABUMUGABO Celestin', 1, '0789946544', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-26 22:00:00', '2027-07-26 22:00:00'),
(533, 'RINGUYENEZA Isaie', 1, '07394508907', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-27 22:00:00', '2027-07-27 22:00:00'),
(534, 'NYIRANZABAHIMANA Anastasie', 1, '0785096370', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-28 22:00:00', '2027-07-28 22:00:00'),
(535, 'NYANDWI Aron', 1, '0788431532', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-29 22:00:00', '2027-07-29 22:00:00'),
(536, 'SEMABUMBA Tharcisse', 1, '0783493293', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-30 22:00:00', '2027-07-30 22:00:00'),
(537, 'NYIRANSENGIMANA Simonia', 1, '0784598821', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-07-31 22:00:00', '2027-07-31 22:00:00'),
(538, 'AFAZARI Patrick', 1, '0786826536', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-01 22:00:00', '2027-08-01 22:00:00'),
(539, 'NIYOKWIRINGIRWA Vedaste', 1, '0785665818', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-02 22:00:00', '2027-08-02 22:00:00'),
(540, 'MUKANGIRIYE Virginie', 1, '0722200734', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-03 22:00:00', '2027-08-03 22:00:00'),
(541, 'KALISA Zachee', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-04 22:00:00', '2027-08-04 22:00:00'),
(542, 'TWAGIRAYEZU Evariste', 1, '0787678445', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-05 22:00:00', '2027-08-05 22:00:00'),
(543, 'MUNYANEZA Vincent', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-06 22:00:00', '2027-08-06 22:00:00'),
(544, 'NIYOYITA Vital', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-07 22:00:00', '2027-08-07 22:00:00'),
(545, 'IRUMVA Brayan', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-08 22:00:00', '2027-08-08 22:00:00'),
(546, 'MURENZI Faustin', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-09 22:00:00', '2027-08-09 22:00:00'),
(547, 'UBARIJORO Francois', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-10 22:00:00', '2027-08-10 22:00:00'),
(548, 'UWAMAHORO Laurence', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-11 22:00:00', '2027-08-11 22:00:00'),
(549, 'IYAMUREMYE Concorde', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-12 22:00:00', '2027-08-12 22:00:00'),
(550, 'NYIRANSABIMANA Claire', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-13 22:00:00', '2027-08-13 22:00:00'),
(551, 'SEBAHUTU Francois', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-14 22:00:00', '2027-08-14 22:00:00'),
(552, 'TWAGIRIMANA Innocent', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-15 22:00:00', '2027-08-15 22:00:00'),
(553, 'HAKIZIMANA Celestin B', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-16 22:00:00', '2027-08-16 22:00:00'),
(554, 'RIBERAKURORA Theonese', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-17 22:00:00', '2027-08-17 22:00:00'),
(555, 'KAMANYIRE Emerita', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-18 22:00:00', '2027-08-18 22:00:00'),
(556, 'MUKAKANYEMERA Clementine', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-19 22:00:00', '2027-08-19 22:00:00'),
(557, 'HATEGEKIMANA Jean Baptiste', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-20 22:00:00', '2027-08-20 22:00:00'),
(558, 'MUKASINE Gertulde', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-21 22:00:00', '2027-08-21 22:00:00'),
(559, 'KANAMUGIRE Aimable', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-22 22:00:00', '2027-08-22 22:00:00'),
(560, 'NTIDENDEREZA Xavier', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-23 22:00:00', '2027-08-23 22:00:00'),
(561, 'KARAMUKA Erneste B', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-24 22:00:00', '2027-08-24 22:00:00'),
(562, 'MUKAYISENGE', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-25 22:00:00', '2027-08-25 22:00:00'),
(563, 'NYIRAMBARUSHIMANA Ester', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-26 22:00:00', '2027-08-26 22:00:00'),
(564, 'NDAHIMANA Bosco', 1, '0786741388', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-27 22:00:00', '2027-08-27 22:00:00'),
(565, 'DUSABUMUREMYI Augistin', 1, '0788537982', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-28 22:00:00', '2027-08-28 22:00:00'),
(566, 'HABIMANA Augistin', 1, '0786948837', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-29 22:00:00', '2027-08-29 22:00:00'),
(567, 'YANKURIJE Donathille', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-30 22:00:00', '2027-08-30 22:00:00'),
(568, 'MUGENZI Fabien', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-08-31 22:00:00', '2027-08-31 22:00:00'),
(569, 'NZABONIMPA Evariste', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-09-01 22:00:00', '2027-09-01 22:00:00'),
(570, 'SEGAHUTU Francois', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-09-02 22:00:00', '2027-09-02 22:00:00'),
(571, 'KANANI', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-09-03 22:00:00', '2027-09-03 22:00:00'),
(572, 'TWAGIRIMANA Jean Damascene', 1, '', NULL, 'Mbizi cws', NULL, NULL, 'active', '2027-09-04 22:00:00', '2027-09-04 22:00:00'),
(573, 'MUHRULA Leblanc', 1, '0795461572', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-05 22:00:00', '2027-09-05 22:00:00'),
(574, 'NKUNDABAGENZI Theophile', 1, '0786425071', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-06 22:00:00', '2027-09-06 22:00:00'),
(575, 'MUSEMAKWERI Vedaste', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-07 22:00:00', '2027-09-07 22:00:00'),
(576, 'URIMUBENSHI Daniel', 1, '0788257821', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-08 22:00:00', '2027-09-08 22:00:00'),
(577, 'MUTAGANDA Venuste', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-09 22:00:00', '2027-09-09 22:00:00'),
(578, 'MUKANDAYISABYE Vestine', 1, ' 0780186852', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-10 22:00:00', '2027-09-10 22:00:00'),
(579, 'HABIYAMBERE Antoine', 1, '0782083389', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-11 22:00:00', '2027-09-11 22:00:00'),
(580, 'HABYARIMANA Faustin', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-12 22:00:00', '2027-09-12 22:00:00'),
(581, 'HARERIMANA Jean Claude', 1, '0783078045', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-13 22:00:00', '2027-09-13 22:00:00'),
(582, 'NTAKIRUTIMANA Mathieu', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-14 22:00:00', '2027-09-14 22:00:00'),
(583, 'NDAYAZI Venuste', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-15 22:00:00', '2027-09-15 22:00:00'),
(584, 'BYUKUSENGE Elizabeth', 1, '0783604092', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-16 22:00:00', '2027-09-16 22:00:00'),
(585, 'SAFARI Innocent', 1, '243837798781', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-17 22:00:00', '2027-09-17 22:00:00'),
(586, 'MATAYO Innocent', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-18 22:00:00', '2027-09-18 22:00:00'),
(587, 'TUYISENGE Manasse', 1, '0784377489', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-19 22:00:00', '2027-09-19 22:00:00'),
(588, 'MATATA Innocent', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-20 22:00:00', '2027-09-20 22:00:00'),
(589, 'MUKAKARIBANYA Anna', 1, '0798245822', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-21 22:00:00', '2027-09-21 22:00:00'),
(590, 'HABIMANA Faustin', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-22 22:00:00', '2027-09-22 22:00:00'),
(591, 'NYIRAHABIMANA Peragie', 1, '0783871862', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-23 22:00:00', '2027-09-23 22:00:00'),
(592, 'NSENGIYUMVA Theophille', 1, '0789911871', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-24 22:00:00', '2027-09-24 22:00:00'),
(593, 'BISENGIMANA Emmanuel', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-25 22:00:00', '2027-09-25 22:00:00'),
(594, 'NYABARATA Bennuer', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-26 22:00:00', '2027-09-26 22:00:00'),
(595, 'NDORIMANA Jerome', 1, '0783007020', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-27 22:00:00', '2027-09-27 22:00:00'),
(596, 'MUKANKUSI Adella', 1, '0782380105', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-28 22:00:00', '2027-09-28 22:00:00'),
(597, 'NYIRAZANINKA Oliver', 1, '0789967403', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-29 22:00:00', '2027-09-29 22:00:00'),
(598, 'NZABIPFURA Fidele', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-09-30 22:00:00', '2027-09-30 22:00:00'),
(599, 'HABINSHUTI Roger', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-01 22:00:00', '2027-10-01 22:00:00'),
(600, 'HABIYAREMYE Jerome', 1, '0789616823', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-02 22:00:00', '2027-10-02 22:00:00'),
(601, 'NSABIMANA Pascal', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-03 22:00:00', '2027-10-03 22:00:00'),
(602, 'SIBOMANA Emmanuel', 1, '0789616823', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-04 22:00:00', '2027-10-04 22:00:00'),
(603, 'HATEGEKIMANA Jean Marie Vianney', 1, '07841125200', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-05 22:00:00', '2027-10-05 22:00:00'),
(604, 'MUKANDANGA Venancie', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-06 22:00:00', '2027-10-06 22:00:00'),
(605, 'MUNYANEZA Fiacre', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-07 22:00:00', '2027-10-07 22:00:00'),
(606, 'NSENGUMUREMYI Audace', 1, '0798935454', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-08 22:00:00', '2027-10-08 22:00:00'),
(607, 'HABAMUNGU Pascasie', 1, '0783871862', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-09 22:00:00', '2027-10-09 22:00:00'),
(608, 'RUHUMURIZA Chrisostome', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-10 22:00:00', '2027-10-10 22:00:00'),
(609, 'NYIRAHABYARIMANA Felicitee', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-11 22:00:00', '2027-10-11 22:00:00'),
(610, 'MUNYEKAWA Epimaque', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-12 22:00:00', '2027-10-12 22:00:00'),
(611, 'INGABIRE Francine', 1, ' 0783485759', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-13 22:00:00', '2027-10-13 22:00:00'),
(612, 'NTAWIHA Martin', 1, '0789622444', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-14 22:00:00', '2027-10-14 22:00:00'),
(613, 'NYIRANEZA Josepha', 1, '0789138218', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-15 22:00:00', '2027-10-15 22:00:00'),
(614, 'SEKANABO Alfred', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-16 22:00:00', '2027-10-16 22:00:00'),
(615, 'HABINEZA Theophile', 1, ' 0786164663', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-17 22:00:00', '2027-10-17 22:00:00'),
(616, 'MUKANKUNDIYE Bernadette', 1, '0780587367', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-18 22:00:00', '2027-10-18 22:00:00'),
(617, 'BAHATI Patrick', 1, '243997758623', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-19 22:00:00', '2027-10-19 22:00:00'),
(618, 'NDAKEBUKA Paul', 1, '0786034163', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-20 22:00:00', '2027-10-20 22:00:00'),
(619, 'MUREKATETE Julienne', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-21 22:00:00', '2027-10-21 22:00:00'),
(620, 'GASHEMA Theogene', 1, '0781699362', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-22 22:00:00', '2027-10-22 22:00:00'),
(621, 'BAKARERE Marie Chantal', 1, '0785546770', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-23 22:00:00', '2027-10-23 22:00:00'),
(622, 'NYIRAHARIBUTSA Claudenatte', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-24 22:00:00', '2027-10-24 22:00:00'),
(623, 'MUKAMUSONI Marie', 1, '0786787963', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-25 22:00:00', '2027-10-25 22:00:00'),
(624, 'HABYARIMANA Samuel', 1, '0783428145', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-26 22:00:00', '2027-10-26 22:00:00'),
(625, 'TURIKUMWE Emmanuel', 1, '0783894676', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-27 22:00:00', '2027-10-27 22:00:00'),
(626, 'NDERERA Joseph', 1, '0786034163', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-28 22:00:00', '2027-10-28 22:00:00'),
(627, 'NYIRABUNYENZI Thaciana', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-29 22:00:00', '2027-10-29 22:00:00'),
(628, 'NIYIBIZI Jean Damascene', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-30 22:00:00', '2027-10-30 22:00:00'),
(629, 'UWINGABIRE Donatha', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-10-31 23:00:00', '2027-10-31 23:00:00'),
(630, 'GATERA Narcisse', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-01 23:00:00', '2027-11-01 23:00:00'),
(631, 'FURAHA Francine', 1, '0793875326', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-02 23:00:00', '2027-11-02 23:00:00'),
(632, 'NZASABIPFURA Fidel', 1, '0791439418', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-03 23:00:00', '2027-11-03 23:00:00'),
(633, 'NZANYWAYIMANA Emmanuel', 1, '07850722272', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-04 23:00:00', '2027-11-04 23:00:00'),
(634, 'NAMABAJE Eugene', 1, '0789263632', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-05 23:00:00', '2027-11-05 23:00:00'),
(635, 'UZAMUKUNDA Petronille', 1, '0787271306', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-06 23:00:00', '2027-11-06 23:00:00'),
(636, 'TWAGIRAMUNGU Jean Pierre', 1, '0782728432', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-07 23:00:00', '2027-11-07 23:00:00'),
(637, 'NIYONSABA SOLINA', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-08 23:00:00', '2027-11-08 23:00:00'),
(638, 'KANAKUZE Bernadette', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-09 23:00:00', '2027-11-09 23:00:00'),
(639, 'NGABONZIZA Deo', 1, '0783023200', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-10 23:00:00', '2027-11-10 23:00:00'),
(640, 'NTAMABYARIRO Felecien', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-11 23:00:00', '2027-11-11 23:00:00'),
(641, 'BIRAMUKA Pascal', 1, '0784383675', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-12 23:00:00', '2027-11-12 23:00:00'),
(642, 'NDWANIYE Helloman', 1, '0783773108', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-13 23:00:00', '2027-11-13 23:00:00'),
(643, 'RUGOMEZA Calliste', 1, ' 0786824292', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-14 23:00:00', '2027-11-14 23:00:00'),
(644, 'MUSABYEMARIYA Marie Fortune', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-15 23:00:00', '2027-11-15 23:00:00'),
(645, 'SINZATUMA Consoratta', 1, '0787775919', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-16 23:00:00', '2027-11-16 23:00:00'),
(646, 'RWIGEMA Prosper', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-17 23:00:00', '2027-11-17 23:00:00'),
(647, 'TURINABO Valerie', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-18 23:00:00', '2027-11-18 23:00:00'),
(648, 'MUKANDORI Adria', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-19 23:00:00', '2027-11-19 23:00:00'),
(649, 'MUKABAHIZI Thacian', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-20 23:00:00', '2027-11-20 23:00:00'),
(650, 'MUKARURANGWA Christine', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-21 23:00:00', '2027-11-21 23:00:00'),
(651, 'MUKANKUSI Janette', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-22 23:00:00', '2027-11-22 23:00:00'),
(652, 'KAMANUTSE Callixte', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-23 23:00:00', '2027-11-23 23:00:00'),
(653, 'NYANDWI Consolatte', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-24 23:00:00', '2027-11-24 23:00:00'),
(654, 'NYIRAHABIMANA Maria', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-25 23:00:00', '2027-11-25 23:00:00'),
(655, 'BISENGIMANA Joseph', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-26 23:00:00', '2027-11-26 23:00:00'),
(656, 'KANZEGUHERA Felix', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-27 23:00:00', '2027-11-27 23:00:00'),
(657, 'YAKAREMYE Saide', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-28 23:00:00', '2027-11-28 23:00:00'),
(658, 'ICYIMPAYE Suzane', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-29 23:00:00', '2027-11-29 23:00:00'),
(659, 'MURENZI Janvier', 1, '0787569829', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-11-30 23:00:00', '2027-11-30 23:00:00'),
(660, 'UNITED FAST SERVICE', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-01 23:00:00', '2027-12-01 23:00:00'),
(661, 'MUKANDINDA Venancia', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-02 23:00:00', '2027-12-02 23:00:00'),
(662, 'SINAYOBYE Martin', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-03 23:00:00', '2027-12-03 23:00:00'),
(663, 'MUKAMURENZI Francine', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-04 23:00:00', '2027-12-04 23:00:00'),
(664, 'Melecian', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-05 23:00:00', '2027-12-05 23:00:00'),
(665, 'NYIRANGWABIJE Felecitte', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-06 23:00:00', '2027-12-06 23:00:00'),
(666, 'MUKAMUGEMA Lorance', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-07 23:00:00', '2027-12-07 23:00:00'),
(667, 'NYIRANGWABIJE Benitha', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-08 23:00:00', '2027-12-08 23:00:00'),
(668, 'MUTUYIMANA Odette', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-09 23:00:00', '2027-12-09 23:00:00'),
(669, 'NIYOGISUBIZO Jean', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-10 23:00:00', '2027-12-10 23:00:00'),
(670, 'MUKANDEZI Fortine', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-11 23:00:00', '2027-12-11 23:00:00'),
(671, 'MUKANKUNDIYE Alexiane', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-12 23:00:00', '2027-12-12 23:00:00'),
(672, 'MUKANKUREBA Thansilla', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-13 23:00:00', '2027-12-13 23:00:00'),
(673, 'MUGANGA John', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-14 23:00:00', '2027-12-14 23:00:00'),
(674, 'NYIRANDIMUBENSHI BELENCILLA', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-15 23:00:00', '2027-12-15 23:00:00'),
(675, 'MAZIMPAKA Alphaile', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-16 23:00:00', '2027-12-16 23:00:00'),
(676, 'HABAMUNGU Moise', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-17 23:00:00', '2027-12-17 23:00:00'),
(677, 'NTUYAHAGA Frederic', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-18 23:00:00', '2027-12-18 23:00:00'),
(678, 'KANEZA Innocent', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-19 23:00:00', '2027-12-19 23:00:00'),
(679, 'MUNYABARATA Bonheur', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-20 23:00:00', '2027-12-20 23:00:00'),
(680, 'NTIHINYURWA Alfred', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-21 23:00:00', '2027-12-21 23:00:00'),
(681, 'NSENGUMUREMYI Bernard', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-22 23:00:00', '2027-12-22 23:00:00'),
(682, 'HAGENIMANA Jelleman', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-23 23:00:00', '2027-12-23 23:00:00'),
(683, 'TWAGIRIMANA Emmanuel', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-24 23:00:00', '2027-12-24 23:00:00'),
(684, 'MUNYAKAYANDA Jean Bosco', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-25 23:00:00', '2027-12-25 23:00:00'),
(685, 'HAVUGIMANA Venuste', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-26 23:00:00', '2027-12-26 23:00:00'),
(686, 'NYIRANEZA Pascasia', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-27 23:00:00', '2027-12-27 23:00:00'),
(687, 'TWAGIRABUDUWE Antoine', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-28 23:00:00', '2027-12-28 23:00:00'),
(688, 'NYIRANDORIMANA Theresie', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-29 23:00:00', '2027-12-29 23:00:00'),
(689, 'MUKANDORI Donatha', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-30 23:00:00', '2027-12-30 23:00:00'),
(690, 'NTAKIYIMANA Thadeo', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2027-12-31 23:00:00', '2027-12-31 23:00:00'),
(691, 'MUKARUGEMA Domitille', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-01 23:00:00', '2028-01-01 23:00:00'),
(692, 'SEMAVENE Callixte', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-02 23:00:00', '2028-01-02 23:00:00'),
(693, 'MAZIMPAKA Emmanuel', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-03 23:00:00', '2028-01-03 23:00:00'),
(694, 'MUHAWENIMANA Vestine', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-04 23:00:00', '2028-01-04 23:00:00'),
(695, 'MUHIMPUNDU Syliver', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-05 23:00:00', '2028-01-05 23:00:00'),
(696, 'MUKANKURIYE Speciose', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-06 23:00:00', '2028-01-06 23:00:00'),
(697, 'NYIRAMUGWERA Rachel', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-07 23:00:00', '2028-01-07 23:00:00'),
(698, 'HAKIZIMANA Claude', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-08 23:00:00', '2028-01-08 23:00:00'),
(699, 'MUKAMBANDA Ferdinand', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-09 23:00:00', '2028-01-09 23:00:00'),
(700, 'NZEYIMANA Daniel', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-10 23:00:00', '2028-01-10 23:00:00'),
(701, 'NTWARI Antoine', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-11 23:00:00', '2028-01-11 23:00:00'),
(702, 'NAMBAJE Eugenie', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-12 23:00:00', '2028-01-12 23:00:00'),
(703, 'GASINZINGWA Gaspal', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-13 23:00:00', '2028-01-13 23:00:00'),
(704, 'MUKANDAYISABYE Vestine B', 1, '', NULL, 'Muhari cws', NULL, NULL, 'active', '2028-01-14 23:00:00', '2028-01-14 23:00:00'),
(705, 'BIRORI Andre', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-15 23:00:00', '2028-01-15 23:00:00'),
(706, 'KANANI Emmanuel', 1, '0795807437', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-16 23:00:00', '2028-01-16 23:00:00'),
(707, 'MURERA Jean', 1, '0795807437', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-17 23:00:00', '2028-01-17 23:00:00'),
(708, 'MUTABAZI Filbert', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-18 23:00:00', '2028-01-18 23:00:00'),
(709, 'NGENDO SYLVESTRE', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-19 23:00:00', '2028-01-19 23:00:00'),
(710, 'SERINDWI Vital', 1, '0780837250', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-20 23:00:00', '2028-01-20 23:00:00'),
(711, 'HABUMUREMYI John', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-21 23:00:00', '2028-01-21 23:00:00'),
(712, 'TUYISABE Felix', 1, '0788414594', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-22 23:00:00', '2028-01-22 23:00:00'),
(713, 'MUHIRE Jean Pierre', 1, '0794701393', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-23 23:00:00', '2028-01-23 23:00:00'),
(714, 'MUKAMFIZI Concilie', 1, '0784771249', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-24 23:00:00', '2028-01-24 23:00:00'),
(715, 'BIMENYIMANA Jean Pierre', 1, '0788753311', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-25 23:00:00', '2028-01-25 23:00:00'),
(716, 'NGENDAHIMANA Zache', 1, '0785250450', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-26 23:00:00', '2028-01-26 23:00:00'),
(717, 'NSENGIMANA Jean Claude', 1, '0788812385', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-27 23:00:00', '2028-01-27 23:00:00'),
(718, 'GAFARANGA Venuste', 1, '0785989058', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-28 23:00:00', '2028-01-28 23:00:00'),
(719, 'MBARUSHIMANA John', 1, '0786518370', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-29 23:00:00', '2028-01-29 23:00:00'),
(720, 'TWAGIRA Vicent', 1, '0789261813', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-30 23:00:00', '2028-01-30 23:00:00'),
(721, 'KAREKEZI Alex', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-01-31 23:00:00', '2028-01-31 23:00:00'),
(722, 'UFITINEMA Etienne', 1, '0781733264', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-01 23:00:00', '2028-02-01 23:00:00'),
(723, 'UWAMAHORO Jacqueline', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-02 23:00:00', '2028-02-02 23:00:00'),
(724, 'SIBOMANA Athanase', 1, '0786292770', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-03 23:00:00', '2028-02-03 23:00:00'),
(725, 'NZABAHIRANYA Eraste', 1, '072554100', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-04 23:00:00', '2028-02-04 23:00:00'),
(726, 'NIYOMUGABO Emmanuel', 1, '0726302347', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-05 23:00:00', '2028-02-05 23:00:00'),
(727, 'NSABIMANA Evariste', 1, '0789952430', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-06 23:00:00', '2028-02-06 23:00:00'),
(728, 'MBERABAHIZI Jean Paul', 1, '079115465', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-07 23:00:00', '2028-02-07 23:00:00'),
(729, 'DUSHIME Emmanuel', 1, '0726245645', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-08 23:00:00', '2028-02-08 23:00:00'),
(730, 'NYIRAMATAMA Marlene', 1, '0783213527', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-09 23:00:00', '2028-02-09 23:00:00'),
(731, 'KAYUMBA SYLVER', 1, '0726097607', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-10 23:00:00', '2028-02-10 23:00:00'),
(732, 'NGABO  RUBANZANA Lionel', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-11 23:00:00', '2028-02-11 23:00:00'),
(733, 'TUYISENGE Bertin', 1, '0788510333', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-12 23:00:00', '2028-02-12 23:00:00'),
(734, 'KARAMUKA Erneste', 1, '0788375473', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-13 23:00:00', '2028-02-13 23:00:00'),
(735, 'NZAGAHIMANA Claude', 1, '0783477221', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-14 23:00:00', '2028-02-14 23:00:00'),
(736, 'NTEZIRYAYO Antoine', 1, '0788367775', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-15 23:00:00', '2028-02-15 23:00:00'),
(737, 'DUSHIMUMUREMYI Eric', 1, '072575375817', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-16 23:00:00', '2028-02-16 23:00:00'),
(738, 'SAFARI Jean', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-17 23:00:00', '2028-02-17 23:00:00'),
(739, 'MUKAKAREGA Goderva', 1, '0782458785', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-18 23:00:00', '2028-02-18 23:00:00'),
(740, 'RUGIRA Ferdinand', 1, '0781264337', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-19 23:00:00', '2028-02-19 23:00:00'),
(741, 'KAMANZI Gratien', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-20 23:00:00', '2028-02-20 23:00:00'),
(742, 'UWIMANA Jean Pierre', 1, '0725367399', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-21 23:00:00', '2028-02-21 23:00:00'),
(743, 'MANIRARORA Vincent', 1, '079532644', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-22 23:00:00', '2028-02-22 23:00:00'),
(744, 'BAYAVUGE Evaliste', 1, '0781355351', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-23 23:00:00', '2028-02-23 23:00:00'),
(745, 'KAMBERUKA Therese', 1, '0794757987', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-24 23:00:00', '2028-02-24 23:00:00'),
(746, 'MBARUBUKEYE Elias', 1, '0788814324', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-25 23:00:00', '2028-02-25 23:00:00'),
(747, 'NYANZIRA Sarah', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-26 23:00:00', '2028-02-26 23:00:00'),
(748, 'UWANYIRIGIRA Agnes', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-27 23:00:00', '2028-02-27 23:00:00'),
(749, 'HATEGEKIMANA Innocent', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-28 23:00:00', '2028-02-28 23:00:00'),
(750, 'GASANZWE Evaliste', 1, '0791414560', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-02-29 23:00:00', '2028-02-29 23:00:00'),
(751, 'NIYIVUGA Geriade', 1, '072879797701', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-01 23:00:00', '2028-03-01 23:00:00'),
(752, 'VUGUZIGIRE Egide', 1, '0785286684', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-02 23:00:00', '2028-03-02 23:00:00'),
(753, 'BARIGIRA Paul', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-03 23:00:00', '2028-03-03 23:00:00'),
(754, 'MUSABYIMANA Theogene', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-04 23:00:00', '2028-03-04 23:00:00'),
(755, 'HAKIZIMANA Gaspard', 1, '0723685100', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-05 23:00:00', '2028-03-05 23:00:00'),
(756, 'BYUKUSENGE Pierre Celestin', 1, '0783110423', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-06 23:00:00', '2028-03-06 23:00:00'),
(757, 'ISINGIZWE Jean Pierre', 1, '0795385423', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-07 23:00:00', '2028-03-07 23:00:00'),
(758, 'MUKANKUSI Providencia', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-08 23:00:00', '2028-03-08 23:00:00'),
(759, 'SHUMBUSHO Theogene', 1, '0795519999', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-09 23:00:00', '2028-03-09 23:00:00'),
(760, 'BAYIZERE Sifa', 1, '0798652714', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-10 23:00:00', '2028-03-10 23:00:00'),
(761, 'MUKAMURIGO Gaudance', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-11 23:00:00', '2028-03-11 23:00:00'),
(762, 'GASHUMBA Simmo Pierre', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-12 23:00:00', '2028-03-12 23:00:00'),
(763, 'HAKUZIMANA Bosco', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-13 23:00:00', '2028-03-13 23:00:00'),
(764, 'SIBOMANA Alphonse', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-14 23:00:00', '2028-03-14 23:00:00'),
(765, 'MUHIRE Eric', 1, '0788594801', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-15 23:00:00', '2028-03-15 23:00:00'),
(766, 'KANANI Charles', 1, '0781659711', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-16 23:00:00', '2028-03-16 23:00:00'),
(767, 'RWAKAZUNGU Andre', 1, '0726316605', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-17 23:00:00', '2028-03-17 23:00:00'),
(768, 'SEMABUMBA Erneste', 1, '0723685651', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-18 23:00:00', '2028-03-18 23:00:00'),
(769, 'MURWANASHYAKA Emmanuel', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-19 23:00:00', '2028-03-19 23:00:00'),
(770, 'TUYIZERE Ruth', 1, '0785461040', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-20 23:00:00', '2028-03-20 23:00:00'),
(771, 'MUHIRE Valens', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-21 23:00:00', '2028-03-21 23:00:00'),
(772, 'GASHUGI Jean', 1, '0725850811', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-22 23:00:00', '2028-03-22 23:00:00'),
(773, 'HABAMENSHI Valens', 1, '0723786438', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-23 23:00:00', '2028-03-23 23:00:00'),
(774, 'RUKUNDO SYLVESTRE', 1, '0791910006', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-24 23:00:00', '2028-03-24 23:00:00'),
(775, 'HATEGEKIMANA Emmanuel', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-25 23:00:00', '2028-03-25 23:00:00'),
(776, 'HATANGIMBABAZI Consessa', 1, '0798744906', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-26 22:00:00', '2028-03-26 22:00:00'),
(777, 'MURINDAHABI Valens', 1, '07905619165', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-27 22:00:00', '2028-03-27 22:00:00'),
(778, 'MUKAKANANI Esperance', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-28 22:00:00', '2028-03-28 22:00:00'),
(779, 'NSENGIYAREMYE Jean Marie Vianey', 1, '0785448979', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-29 22:00:00', '2028-03-29 22:00:00'),
(780, 'KAREMERA Callixte', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-30 22:00:00', '2028-03-30 22:00:00'),
(781, 'SEZIBERA Alphonse', 1, '0793314728', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-03-31 22:00:00', '2028-03-31 22:00:00');
INSERT INTO `suppliers` (`id`, `name`, `supplier_type_id`, `phone`, `email`, `address`, `contact_person`, `contact_phone`, `status`, `created_at`, `updated_at`) VALUES
(782, 'MUKANDAMAGE Heralie', 1, '0787971538', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-04-01 22:00:00', '2028-04-01 22:00:00'),
(783, 'NZIRAGUSESWA Francois', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-04-02 22:00:00', '2028-04-02 22:00:00'),
(784, 'RUTERANA Maurice', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-04-03 22:00:00', '2028-04-03 22:00:00'),
(785, 'MUKANYANDWI Sylvie', 1, '0725263127', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-04-04 22:00:00', '2028-04-04 22:00:00'),
(786, 'GASHUMBA Jean', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-04-05 22:00:00', '2028-04-05 22:00:00'),
(787, 'HABUMUGISHA Emmanuel', 1, '0783644485', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-04-06 22:00:00', '2028-04-06 22:00:00'),
(788, 'NGABONZIZA Audace', 1, '0788777063', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-04-07 22:00:00', '2028-04-07 22:00:00'),
(789, 'MUKARUZAYE Immacule', 1, '', NULL, 'Mugina cws', NULL, NULL, 'active', '2028-04-08 22:00:00', '2028-04-08 22:00:00'),
(790, 'UWITONZE Manasse', 1, '0788410913', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-09 22:00:00', '2028-04-09 22:00:00'),
(791, 'TWAGIRAYEZU Vincent', 1, '0789261813', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-10 22:00:00', '2028-04-10 22:00:00'),
(792, 'MUKANEZA Jacqueline', 1, '0785736907', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-11 22:00:00', '2028-04-11 22:00:00'),
(793, 'BAVUGE NDARUHUTSE', 1, '0785311374', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-12 22:00:00', '2028-04-12 22:00:00'),
(794, 'NIYIBIZI Christophe', 1, '0788215400', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-13 22:00:00', '2028-04-13 22:00:00'),
(795, 'SIBORUREMA Pascal', 1, '0780789849', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-14 22:00:00', '2028-04-14 22:00:00'),
(796, 'NSENGIMANA Issa', 1, '0788944092', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-15 22:00:00', '2028-04-15 22:00:00'),
(797, 'AYOBANGIRA Hamduni', 1, '0727086547', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-16 22:00:00', '2028-04-16 22:00:00'),
(798, 'KARUMUGABO Abdurkhalim', 1, '0782216805', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-17 22:00:00', '2028-04-17 22:00:00'),
(799, 'NTAKIRUTIMANA Gilbert', 1, '0780222023', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-18 22:00:00', '2028-04-18 22:00:00'),
(800, 'IHORIHOZE Thomas', 1, '0783124889', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-19 22:00:00', '2028-04-19 22:00:00'),
(801, 'NYIRINKINDI Jean Claude', 1, '0785261610', NULL, 'Nasho cws', NULL, NULL, 'active', '2028-04-20 22:00:00', '2028-04-20 22:00:00'),
(802, 'ABAKUNDAKAWA', 2, '', NULL, '', NULL, NULL, 'active', '2028-04-21 22:00:00', '2028-04-21 22:00:00'),
(803, 'ALBERT', 2, '0788507620', NULL, '', NULL, NULL, 'active', '2028-04-22 22:00:00', '2028-04-22 22:00:00'),
(804, 'BAHO COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-04-23 22:00:00', '2028-04-23 22:00:00'),
(805, 'BEAUTIFUL COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-04-24 22:00:00', '2028-04-24 22:00:00'),
(806, 'BENDER EXPORTS', 2, '', NULL, '', NULL, NULL, 'active', '2028-04-25 22:00:00', '2028-04-25 22:00:00'),
(807, 'BEST FARMER', 2, '', NULL, '', NULL, NULL, 'active', '2028-04-26 22:00:00', '2028-04-26 22:00:00'),
(808, 'BOY', 2, '', NULL, '', NULL, NULL, 'active', '2028-04-27 22:00:00', '2028-04-27 22:00:00'),
(809, 'BURUNDI EXPORTS', 2, '', NULL, '', NULL, NULL, 'active', '2028-04-28 22:00:00', '2028-04-28 22:00:00'),
(810, 'BUTARA MOUNTAIN COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-04-29 22:00:00', '2028-04-29 22:00:00'),
(811, 'CAGEYO BUSINESS COMPANY', 2, '', NULL, '', NULL, NULL, 'active', '2028-04-30 22:00:00', '2028-04-30 22:00:00'),
(812, 'CCD TRADE CO LTD', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-01 22:00:00', '2028-05-01 22:00:00'),
(813, 'CHAMPION HUMANITY TRADING', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-02 22:00:00', '2028-05-02 22:00:00'),
(814, 'CHARLES', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-03 22:00:00', '2028-05-03 22:00:00'),
(815, 'COCAHU', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-04 22:00:00', '2028-05-04 22:00:00'),
(816, 'CONGO EXPORTS 2024', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-05 22:00:00', '2028-05-05 22:00:00'),
(817, 'CYAHINDA CWS', 2, '0788918690', NULL, '', NULL, NULL, 'active', '2028-05-06 22:00:00', '2028-05-06 22:00:00'),
(818, 'CYAMABUYE CWS', 2, '0784601144', NULL, '', NULL, NULL, 'active', '2028-05-07 22:00:00', '2028-05-07 22:00:00'),
(819, 'DAVID&FAMILY', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-08 22:00:00', '2028-05-08 22:00:00'),
(820, 'EGIDE NYAKIZU COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-09 22:00:00', '2028-05-09 22:00:00'),
(821, 'ESTHER', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-10 22:00:00', '2028-05-10 22:00:00'),
(822, 'FIFI MATHILDE', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-11 22:00:00', '2028-05-11 22:00:00'),
(823, 'GASHUMBA CBC', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-12 22:00:00', '2028-05-12 22:00:00'),
(824, 'GIC LTD', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-13 22:00:00', '2028-05-13 22:00:00'),
(825, 'GITOBE CWS', 2, '0786984913', NULL, '', NULL, NULL, 'active', '2028-05-14 22:00:00', '2028-05-14 22:00:00'),
(826, 'GREENLAND COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-15 22:00:00', '2028-05-15 22:00:00'),
(827, 'HILLOCK COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-16 22:00:00', '2028-05-16 22:00:00'),
(828, 'HIMBA', 2, '0788499045', NULL, '', NULL, NULL, 'active', '2028-05-17 22:00:00', '2028-05-17 22:00:00'),
(829, 'HOME AWAY Josias', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-18 22:00:00', '2028-05-18 22:00:00'),
(830, 'HORIZON SUPREME COFFEE', 2, '0788232292', NULL, '', NULL, NULL, 'active', '2028-05-19 22:00:00', '2028-05-19 22:00:00'),
(831, 'IMPEXCOR', 2, '0788296133', NULL, '', NULL, NULL, 'active', '2028-05-20 22:00:00', '2028-05-20 22:00:00'),
(832, 'IWACU COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-21 22:00:00', '2028-05-21 22:00:00'),
(833, 'JEAN LUC', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-22 22:00:00', '2028-05-22 22:00:00'),
(834, 'JERIKA LTD', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-23 22:00:00', '2028-05-23 22:00:00'),
(835, 'JOB COFFEE', 2, '0788561919', NULL, '', NULL, NULL, 'active', '2028-05-24 22:00:00', '2028-05-24 22:00:00'),
(836, 'JURU COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-25 22:00:00', '2028-05-25 22:00:00'),
(837, 'KABILA COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-26 22:00:00', '2028-05-26 22:00:00'),
(838, 'KAZUNGU', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-27 22:00:00', '2028-05-27 22:00:00'),
(839, 'KIBIRIZI CWS', 2, '0781615171', NULL, '', NULL, NULL, 'active', '2028-05-28 22:00:00', '2028-05-28 22:00:00'),
(840, 'KIGASALI COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-29 22:00:00', '2028-05-29 22:00:00'),
(841, 'KIVU KAWA LTD', 2, '', NULL, '', NULL, NULL, 'active', '2028-05-30 22:00:00', '2028-05-30 22:00:00'),
(842, 'KIYOMBE MOUNTAIN COFFEE/PAPA KETHY', 2, '0788538792', NULL, '', NULL, NULL, 'active', '2028-05-31 22:00:00', '2028-05-31 22:00:00'),
(843, 'LETSEQUOIA COFFEE LTD', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-01 22:00:00', '2028-06-01 22:00:00'),
(844, 'MASHA JUC LTD', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-02 22:00:00', '2028-06-02 22:00:00'),
(845, 'MIG LTD ROGER', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-03 22:00:00', '2028-06-03 22:00:00'),
(846, 'MILLENIUM COFFEE CO.LTD', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-04 22:00:00', '2028-06-04 22:00:00'),
(847, 'MOISE', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-05 22:00:00', '2028-06-05 22:00:00'),
(848, 'MONA COFFEE LTD', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-06 22:00:00', '2028-06-06 22:00:00'),
(849, 'MOUNTAIN COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-07 22:00:00', '2028-06-07 22:00:00'),
(850, 'MUBUGA COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-08 22:00:00', '2028-06-08 22:00:00'),
(851, 'MUGINA CWS', 2, '0785385423', NULL, '', NULL, NULL, 'active', '2028-06-09 22:00:00', '2028-06-09 22:00:00'),
(852, 'MURAHO TRADING COMPANY', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-10 22:00:00', '2028-06-10 22:00:00'),
(853, 'MURENZI', 2, '0788801060', NULL, '', NULL, NULL, 'active', '2028-06-11 22:00:00', '2028-06-11 22:00:00'),
(854, 'NAEB', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-12 22:00:00', '2028-06-12 22:00:00'),
(855, 'NGABONZIZA AUDACE FICHE', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-13 22:00:00', '2028-06-13 22:00:00'),
(856, 'NGABONZIZA CFR CWS', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-14 22:00:00', '2028-06-14 22:00:00'),
(857, 'NKUNZIMANA JDD FICHE', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-15 22:00:00', '2028-06-15 22:00:00'),
(858, 'NKURUNZIZA MARC BURUNDI', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-16 22:00:00', '2028-06-16 22:00:00'),
(859, 'NSHUTI COFFEE LTD', 2, '0788569364', NULL, '', NULL, NULL, 'active', '2028-06-17 22:00:00', '2028-06-17 22:00:00'),
(860, 'NSHUTI CFR NASHO 2024', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-18 22:00:00', '2028-06-18 22:00:00'),
(861, 'NYAKABANDE CWS 2025', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-19 22:00:00', '2028-06-19 22:00:00'),
(862, 'PACO', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-20 22:00:00', '2028-06-20 22:00:00'),
(863, 'PARCHMENT NGABO', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-21 22:00:00', '2028-06-21 22:00:00'),
(864, 'PETER', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-22 22:00:00', '2028-06-22 22:00:00'),
(865, 'RICHARD', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-23 22:00:00', '2028-06-23 22:00:00'),
(866, 'RRA', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-24 22:00:00', '2028-06-24 22:00:00'),
(867, 'RTC&FIDELE', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-25 22:00:00', '2028-06-25 22:00:00'),
(868, 'RUSATIRA NGORORERO', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-26 22:00:00', '2028-06-26 22:00:00'),
(869, 'RUSATIRA NO EBM', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-27 22:00:00', '2028-06-27 22:00:00'),
(870, 'RWAMATAMU GASTON', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-28 22:00:00', '2028-06-28 22:00:00'),
(871, 'RWASHOSCO', 2, '', NULL, '', NULL, NULL, 'active', '2028-06-29 22:00:00', '2028-06-29 22:00:00'),
(872, 'SAVANNAH COFFEE', 2, '0783559014', NULL, '', NULL, NULL, 'active', '2028-06-30 22:00:00', '2028-06-30 22:00:00'),
(873, 'NASHO CWS', 2, '0788614586', NULL, '', NULL, NULL, 'active', '2028-07-01 22:00:00', '2028-07-01 22:00:00'),
(874, 'SIMBI COFFEE INVESTMENT', 2, '', NULL, '', NULL, NULL, 'active', '2028-07-02 22:00:00', '2028-07-02 22:00:00'),
(875, 'THIERRY KOPAKAKI', 2, '', NULL, '', NULL, NULL, 'active', '2028-07-03 22:00:00', '2028-07-03 22:00:00'),
(876, 'TROPIC COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-07-04 22:00:00', '2028-07-04 22:00:00'),
(877, 'TUMBA', 2, '', NULL, '', NULL, NULL, 'active', '2028-07-05 22:00:00', '2028-07-05 22:00:00'),
(878, 'UGANDA CERISES', 2, '', NULL, '', NULL, NULL, 'active', '2028-07-06 22:00:00', '2028-07-06 22:00:00'),
(879, 'UMURAVA COFFEE LTD', 2, '', NULL, '', NULL, NULL, 'active', '2028-07-07 22:00:00', '2028-07-07 22:00:00'),
(880, 'UNITED 2', 2, '', NULL, '', NULL, NULL, 'active', '2028-07-08 22:00:00', '2028-07-08 22:00:00'),
(881, 'UNITED FAST SERVICE', 2, '0789217995', NULL, '', NULL, NULL, 'active', '2028-07-09 22:00:00', '2028-07-09 22:00:00'),
(882, 'UWIMANA ROSE', 2, '0784830337', NULL, '', NULL, NULL, 'active', '2028-07-10 22:00:00', '2028-07-10 22:00:00'),
(883, 'VALENS&GUSTAVE', 2, '', NULL, '', NULL, NULL, 'active', '2028-07-11 22:00:00', '2028-07-11 22:00:00'),
(884, 'WOMEN COFFEE EXTENSION', 2, '', NULL, '', NULL, NULL, 'active', '2028-07-12 22:00:00', '2028-07-12 22:00:00'),
(885, 'YOUTH COFFEE', 2, '', NULL, '', NULL, NULL, 'active', '2028-07-13 22:00:00', '2028-07-13 22:00:00'),
(886, 'gakenke cws', 2, NULL, NULL, NULL, NULL, NULL, 'active', '2026-02-25 07:24:22', '2026-02-25 07:24:22');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_advances`
--

CREATE TABLE `supplier_advances` (
  `id` int(11) NOT NULL,
  `advance_number` varchar(50) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `advance_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','approved','settled','cancelled') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_identifiers`
--

CREATE TABLE `supplier_identifiers` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `identifier_id` int(11) NOT NULL,
  `value` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_identifiers`
--

INSERT INTO `supplier_identifiers` (`id`, `supplier_id`, `identifier_id`, `value`, `created_at`, `updated_at`) VALUES
(2, 1, 2, '123456', '2026-02-05 09:46:05', '2026-02-05 09:46:05');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_payables`
--

CREATE TABLE `supplier_payables` (
  `id` int(11) NOT NULL,
  `payable_number` varchar(20) NOT NULL,
  `stock_receive_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid_amount` decimal(15,2) DEFAULT 0.00,
  `status` enum('pending','partial','paid') DEFAULT 'pending',
  `due_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_payables`
--

INSERT INTO `supplier_payables` (`id`, `payable_number`, `stock_receive_id`, `supplier_id`, `location_id`, `amount`, `paid_amount`, `status`, `due_date`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(2, 'PAY-202602-9194', 1, 402, 2, 2783327.00, 0.00, 'pending', NULL, NULL, 1, '2026-02-24 09:25:01', '2026-02-24 09:25:01'),
(3, 'PAY-202602-7057', 2, 335, 2, 9.00, 0.00, 'pending', NULL, NULL, 1, '2026-02-24 09:26:01', '2026-02-24 09:26:01'),
(4, 'PAY-202602-1407', 4, 356, 2, 9913.00, 0.00, 'pending', NULL, NULL, 1, '2026-02-24 09:47:57', '2026-02-24 09:47:57');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_types`
--

CREATE TABLE `supplier_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_types`
--

INSERT INTO `supplier_types` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Farmer', 'Farmer', 'active', '2026-02-05 08:59:32', '2026-02-05 09:15:02'),
(2, 'Supplier', 'Supplier', 'active', '2026-02-05 09:00:01', '2026-02-05 09:15:02');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_type_identifiers`
--

CREATE TABLE `supplier_type_identifiers` (
  `id` int(11) NOT NULL,
  `supplier_type_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_required` tinyint(1) DEFAULT 0,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `supplier_type_identifiers`
--

INSERT INTO `supplier_type_identifiers` (`id`, `supplier_type_id`, `name`, `is_required`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Tin Number', 1, 'active', '2026-02-05 09:33:31', '2026-02-05 09:33:31'),
(2, 2, 'Tin Number', 0, 'active', '2026-02-05 09:33:54', '2026-02-05 09:33:54');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_certification`
--

CREATE TABLE `tbl_certification` (
  `cert_id` int(11) NOT NULL,
  `cert_name` varchar(50) NOT NULL,
  `cert_desc` varchar(50) DEFAULT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_certification`
--

INSERT INTO `tbl_certification` (`cert_id`, `cert_name`, `cert_desc`, `status`) VALUES
(1, 'Certuif', 'dfvhb', 1),
(2, 'test server', 'desc', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_certification_transactions`
--

CREATE TABLE `tbl_certification_transactions` (
  `trans_id` int(11) NOT NULL,
  `cert_id` int(11) NOT NULL,
  `done_by` int(11) NOT NULL,
  `date_done` date NOT NULL,
  `due_date` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `payment_accounts` text NOT NULL,
  `coments` varchar(100) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_certification_transactions`
--

INSERT INTO `tbl_certification_transactions` (`trans_id`, `cert_id`, `done_by`, `date_done`, `due_date`, `payment_accounts`, `coments`, `status`, `amount`) VALUES
(1, 1, 3, '2026-02-23', '2026-02-23 08:18:00.000000', 'tyujk,', 'ghjkl', 0, 567),
(2, 1, 3, '2026-02-23', '2026-02-23 08:30:57.135719', '[{\"account_id\":7,\"amount\":9000}]', 'yujk', 1, 9000),
(3, 1, 1, '2026-02-23', '2026-02-23 10:23:36.245907', '[{\"account_id\":1,\"amount\":1}]', '1', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_currency_type`
--

CREATE TABLE `tbl_currency_type` (
  `currency_id` int(11) NOT NULL,
  `curre_name` varchar(50) NOT NULL,
  `sign` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_currency_type`
--

INSERT INTO `tbl_currency_type` (`currency_id`, `curre_name`, `sign`) VALUES
(1, 'Rwandan Francs', 'Rwf'),
(2, 'Dollar', '$'),
(7, 'Euro', '€');

-- --------------------------------------------------------

--
-- Table structure for table `currency_exchange_rates`
--

CREATE TABLE `currency_exchange_rates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_currency_id` int(11) NOT NULL,
  `to_currency_id` int(11) NOT NULL,
  `exchange_rate` decimal(18,8) NOT NULL,
  `effective_date` date NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_from_currency` (`from_currency_id`),
  KEY `idx_to_currency` (`to_currency_id`),
  KEY `idx_effective_date` (`effective_date`),
  KEY `idx_active` (`is_active`),
  UNIQUE KEY `unique_currency_pair_date` (`from_currency_id`, `to_currency_id`, `effective_date`),
  FOREIGN KEY (`from_currency_id`) REFERENCES `tbl_currency_type` (`currency_id`),
  FOREIGN KEY (`to_currency_id`) REFERENCES `tbl_currency_type` (`currency_id`),
  FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currency_exchange_rates`
--

INSERT INTO `currency_exchange_rates` (`id`, `from_currency_id`, `to_currency_id`, `exchange_rate`, `effective_date`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 2, 0.00075000, '2026-02-26', 1, 1, '2026-02-26 08:00:00', '2026-02-26 08:00:00'),
(2, 2, 1, 1333.33330000, '2026-02-26', 1, 1, '2026-02-26 08:00:00', '2026-02-26 08:00:00'),
(3, 1, 7, 0.00060000, '2026-02-26', 1, 1, '2026-02-26 08:00:00', '2026-02-26 08:00:00'),
(4, 7, 1, 1666.66670000, '2026-02-26', 1, 1, '2026-02-26 08:00:00', '2026-02-26 08:00:00'),
(5, 2, 7, 0.80000000, '2026-02-26', 1, 1, '2026-02-26 08:00:00', '2026-02-26 08:00:00'),
(6, 7, 2, 1.25000000, '2026-02-26', 1, 1, '2026-02-26 08:00:00', '2026-02-26 08:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_expensecategories`
--

CREATE TABLE `tbl_expensecategories` (
  `categ_id` int(11) NOT NULL,
  `categ_name` varchar(50) NOT NULL,
  `description` varchar(50) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_expensecategories`
--

INSERT INTO `tbl_expensecategories` (`categ_id`, `categ_name`, `description`, `status`) VALUES
(2, 'Transport', 'Transport', 1),
(3, 'waterR', 'water', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_expenseconsume`
--

CREATE TABLE `tbl_expenseconsume` (
  `con_id` int(11) NOT NULL,
  `expense_id` int(11) NOT NULL,
  `station_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `pay_mode` longtext NOT NULL,
  `trans_id` varchar(25) DEFAULT NULL,
  `payer_name` int(11) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `pay_date` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `recorded_date` date NOT NULL,
  `receipt_type` int(11) DEFAULT NULL,
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_expenseconsume`
--

INSERT INTO `tbl_expenseconsume` (`con_id`, `expense_id`, `station_id`, `amount`, `pay_mode`, `trans_id`, `payer_name`, `description`, `pay_date`, `recorded_date`, `receipt_type`, `status`) VALUES
(1, 8, 3, 2, '[{\"account_id\":3,\"amount\":2}]', 'EXP202602071452164167', 2, 'wert', '2026-02-07 13:52:16.000000', '2026-02-07', 1, 1),
(2, 5, 3, 10, '[{\"account_id\":1,\"amount\":5},{\"account_id\":3,\"amount\":5}]', 'EX260214131727733', 2, 'ertyu', '2026-02-14 12:17:27.000000', '2026-02-14', 1, 1),
(3, 8, 3, 3, '[{\"account_id\":1,\"amount\":14}]', 'EX260218110113318', 2, 'ddd', '2026-02-18 10:01:13.000000', '2026-02-18', 1, 1),
(4, 9, 3, 4, '[{\"account_id\":2,\"amount\":9}]', 'EX260223103825215', 2, '55', '2026-02-23 09:38:25.000000', '2026-02-23', 1, 1),
(5, 5, 3, 21, '[{\"account_id\":1,\"amount\":21}]', 'EX260225161500906', 2, '', '2026-02-25 15:15:00.000000', '2026-02-25', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_expenseconsumer`
--

CREATE TABLE `tbl_expenseconsumer` (
  `cons_id` int(11) NOT NULL,
  `cons_name` varchar(50) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `sts` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_expenseconsumer`
--

INSERT INTO `tbl_expenseconsumer` (`cons_id`, `cons_name`, `phone`, `sts`) VALUES
(1, 'ABAKARANI NKUBIRI', '456789', 1),
(2, 'ABAKARANI NAEB', '+250788644687', 1),
(4, 'Christianhh', '0789736352', 1),
(5, 'Roger', '0726719457', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_expenses`
--

CREATE TABLE `tbl_expenses` (
  `expense_id` int(11) NOT NULL,
  `categ_id` int(11) DEFAULT NULL,
  `expense_name` varchar(50) NOT NULL,
  `description` varchar(55) DEFAULT NULL,
  `expense_status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_expenses`
--

INSERT INTO `tbl_expenses` (`expense_id`, `categ_id`, `expense_name`, `description`, `expense_status`) VALUES
(1, NULL, 'Transaport', NULL, 1),
(2, 2, 'transport', NULL, 1),
(3, NULL, 'comunication', NULL, 0),
(5, 3, 'SOCIAL CORPORATE', 'hh', 1),
(6, 2, 'tes22', '2erfg', 1),
(8, 6, 'Loading & offloading', 'Profit and loss account', 1),
(9, 8, 'Rente', 'Rente', 1),
(10, 3, 'TEST', 'TGG', 1),
(11, 3, 'water', 'qwetyu', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_expense_conume_details`
--

CREATE TABLE `tbl_expense_conume_details` (
  `det_id` int(11) NOT NULL,
  `trans_code` varchar(50) NOT NULL,
  `amount` int(11) NOT NULL,
  `charges` int(11) NOT NULL,
  `created_by` int(11) NOT NULL,
  `account_id` int(11) DEFAULT NULL,
  `action` enum('AMOUNT','CHARGES','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_expense_conume_details`
--

INSERT INTO `tbl_expense_conume_details` (`det_id`, `trans_code`, `amount`, `charges`, `created_by`, `account_id`, `action`) VALUES
(1, 'EX260214110122401', 12, 0, 3, NULL, 'AMOUNT'),
(2, 'EX260214110122401', 0, 1, 3, 2, 'CHARGES'),
(3, 'EX260214110122401', 0, 1, 3, 4, 'CHARGES'),
(4, 'EX260214131727733', 10, 0, 1, NULL, 'AMOUNT'),
(5, 'EX260214131727733', 0, 1, 1, 1, 'CHARGES'),
(6, 'EX260214131727733', 0, 2, 1, 3, 'CHARGES'),
(7, 'EX260218110113318', 3, 0, 1, NULL, 'AMOUNT'),
(8, 'EX260218110113318', 0, 11, 1, NULL, 'CHARGES'),
(9, 'EX260223103825215', 4, 0, 1, NULL, 'AMOUNT'),
(10, 'EX260223103825215', 0, 5, 1, NULL, 'CHARGES'),
(11, 'EX260225161500906', 21, 0, 1, NULL, 'AMOUNT');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_loan_disbursment`
--

CREATE TABLE `tbl_loan_disbursment` (
  `dis_id` int(11) NOT NULL,
  `l_id` int(11) NOT NULL,
  `pay_accounts` text NOT NULL,
  `amount` int(11) NOT NULL,
  `charges` int(11) NOT NULL DEFAULT 0,
  `duedate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `worker_account` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_loan_disbursment`
--

INSERT INTO `tbl_loan_disbursment` (`dis_id`, `l_id`, `pay_accounts`, `amount`, `charges`, `duedate`, `worker_account`) VALUES
(1, 4, '[{\"account_id\":\"1\",\"account_name\":\"Bank of Kigali\",\"amount\":1},{\"account_id\":\"3\",\"account_name\":\"Bank of Kigali\",\"amount\":1},{\"account_id\":\"4\",\"account_name\":\"Cash\",\"amount\":5}]', 7, 1, '2026-02-10 11:32:45', '345tg'),
(2, 6, '[{\"account_id\":\"1\",\"account_name\":\"Bank of Kigali\",\"amount\":1,\"charges\":1},{\"account_id\":\"3\",\"account_name\":\"Bank of Kigali\",\"amount\":2,\"charges\":1},{\"account_id\":\"1\",\"account_name\":\"Bank of Kigali\",\"amount\":2,\"charges\":1}]', 5, 3, '2026-02-11 07:16:58', 'cash'),
(3, 7, '[{\"account_id\":\"1\",\"account_name\":\"Bank of Kigali\",\"amount\":44,\"charges\":7},{\"account_id\":\"3\",\"account_name\":\"Bank of Kigali\",\"amount\":5,\"charges\":0},{\"account_id\":\"4\",\"account_name\":\"Cash\",\"amount\":40,\"charges\":7}]', 89, 14, '2026-02-11 10:01:16', '55gg'),
(4, 7, '[{\"account_id\":\"1\",\"account_name\":\"Bank of Kigali\",\"amount\":80,\"charges\":1},{\"account_id\":\"4\",\"account_name\":\"Cash\",\"amount\":9,\"charges\":0}]', 89, 1, '2026-02-11 10:03:30', '345tg');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_nonexploitable_transactions`
--

CREATE TABLE `tbl_nonexploitable_transactions` (
  `trans_id` int(11) NOT NULL,
  `eploi_id` int(11) NOT NULL,
  `done by` int(11) NOT NULL,
  `date_done` date NOT NULL,
  `due_date` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `payment_accounts` text NOT NULL,
  `coments` varchar(100) DEFAULT NULL,
  `status` int(11) NOT NULL,
  `amount` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_nonexploitable_transactions`
--

INSERT INTO `tbl_nonexploitable_transactions` (`trans_id`, `eploi_id`, `done by`, `date_done`, `due_date`, `payment_accounts`, `coments`, `status`, `amount`) VALUES
(2, 2, 3, '2026-02-20', '2026-02-20 12:16:41.195452', '[{\"account_id\":7,\"amount\":10}]', 'drrr', 1, 10),
(3, 3, 1, '2026-02-23', '2026-02-23 09:32:05.367846', '[{\"account_id\":1,\"amount\":4},{\"account_id\":2,\"amount\":1}]', 'ttttt', 1, 5);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_proforma_invoice`
--

CREATE TABLE `tbl_proforma_invoice` (
  `id` int(11) NOT NULL,
  `code` varchar(50) NOT NULL,
  `client_id` int(11) NOT NULL,
  `currency_id` int(11) NOT NULL,
  `invoice_type` varchar(50) NOT NULL,
  `prepared_by` int(11) NOT NULL,
  `total_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_proforma_invoice`
--

INSERT INTO `tbl_proforma_invoice` (`id`, `code`, `client_id`, `currency_id`, `invoice_type`, `prepared_by`, `total_amount`, `created_at`, `updated_at`) VALUES
(2, 'GIHACHASHTAG2-2026-02-14', 1, 1, 'Proforma', 3, 67.00, '2026-02-14 13:29:43', '2026-02-14 13:29:43'),
(3, 'GIHACHASHTAG3-2026-02-14', 1, 2, 'Proforma', 3, 78.00, '2026-02-14 14:10:37', '2026-02-14 14:10:37'),
(4, 'GIHACHASHTAG4-2026-02-16', 1, 2, 'Proforma', 1, 2.00, '2026-02-16 06:48:25', '2026-02-16 06:48:25');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_proforma_invoice_items`
--

CREATE TABLE `tbl_proforma_invoice_items` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(150) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `quantity` decimal(18,2) NOT NULL,
  `price` decimal(18,2) NOT NULL,
  `total` decimal(18,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_proforma_invoice_items`
--

INSERT INTO `tbl_proforma_invoice_items` (`id`, `invoice_id`, `product_id`, `product_name`, `unit_id`, `quantity`, `price`, `total`) VALUES
(1, 2, 2, 'Floatant', 3, 4.00, 8.00, 32.00),
(2, 2, 1, 'Non Floatant', 3, 3.00, 9.00, 27.00),
(3, 2, 3, 'A', 3, 4.00, 2.00, 8.00),
(4, 3, 2, 'Floatant', 3, 22.00, 1.00, 22.00),
(5, 3, 3, 'A', 3, 7.00, 8.00, 56.00),
(6, 4, 2, 'Floatant', 3, 2.00, 1.00, 2.00);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_properties`
--

CREATE TABLE `tbl_properties` (
  `property_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `property_name` varchar(255) NOT NULL,
  `created_at` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6),
  `created_by` int(1) NOT NULL,
  `value_amount` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `type_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_properties`
--

INSERT INTO `tbl_properties` (`property_id`, `location_id`, `property_name`, `created_at`, `created_by`, `value_amount`, `status`, `description`, `type_id`) VALUES
(1, 1, 'rfgb', '2026-02-20 15:10:52.000000', 3, 34567, 1, '4tyt', 1),
(2, 4, 'ello test', '2026-02-20 15:13:07.000000', 3, 444, 1, 'rtghn', 1),
(3, 1, 'kia', '2026-02-23 12:22:11.000000', 1, 333, 1, '333', 2);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_property_type`
--

CREATE TABLE `tbl_property_type` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) NOT NULL,
  `description` varchar(255) NOT NULL,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_property_type`
--

INSERT INTO `tbl_property_type` (`type_id`, `type_name`, `description`, `status`) VALUES
(1, 'AMAZU', 'AMAZU', 1),
(2, 'imodok', '', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_receipttype`
--

CREATE TABLE `tbl_receipttype` (
  `rec_id` int(11) NOT NULL,
  `rec_name` varchar(50) NOT NULL,
  `rec_desc` varchar(50) NOT NULL,
  `sts` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_receipttype`
--

INSERT INTO `tbl_receipttype` (`rec_id`, `rec_name`, `rec_desc`, `sts`) VALUES
(1, 'Has EBM', 'This expense Has EBM', 1),
(2, 'No EBM Receipt', 'This expense Has no EBM', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_recharge_history`
--

CREATE TABLE `tbl_recharge_history` (
  `rech_id` int(11) NOT NULL,
  `acc_id` int(11) NOT NULL,
  `amount` int(11) NOT NULL,
  `in_id` int(11) DEFAULT NULL,
  `to_account` int(11) DEFAULT NULL,
  `due_date` timestamp(6) NOT NULL DEFAULT current_timestamp(6) ON UPDATE current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_recharge_history`
--

INSERT INTO `tbl_recharge_history` (`rech_id`, `acc_id`, `amount`, `in_id`, `to_account`, `due_date`) VALUES
(1, 1, 44, NULL, NULL, '2026-03-07 14:09:01.000000'),
(2, 1, 11, NULL, NULL, '2026-03-07 14:09:48.000000'),
(3, 3, 1, NULL, NULL, '2026-03-07 14:28:04.000000'),
(4, 1, 33, NULL, NULL, '2026-03-08 08:13:22.000000'),
(5, 1, 22, NULL, NULL, '2026-03-08 08:21:40.000000'),
(6, 1, 2, NULL, 2, '2026-02-06 08:21:53.000000'),
(7, 1, 10, NULL, NULL, '2026-03-08 08:43:38.000000'),
(8, 3, 10, NULL, NULL, '2026-03-08 08:43:48.000000'),
(9, 2, 10, NULL, NULL, '2026-03-08 08:43:55.000000'),
(10, 4, 10, NULL, NULL, '2026-03-08 08:44:02.000000'),
(11, 3, 5, NULL, 1, '2026-02-06 08:44:52.000000'),
(12, 1, 111, NULL, NULL, '2026-03-09 10:13:06.000000'),
(13, 2, 100, NULL, 4, '2026-02-07 10:17:52.000000'),
(14, 1, 5, NULL, NULL, '2026-03-09 13:32:50.000000'),
(15, 2, 10, NULL, 3, '2026-02-07 13:34:00.000000'),
(16, 5, 500, NULL, NULL, '2026-03-15 14:47:16.000000'),
(17, 2, 10, NULL, 5, '2026-02-13 14:49:50.000000'),
(18, 2, 5, NULL, 4, '2026-02-18 07:41:19.000000'),
(19, 1, 3333333, 2, NULL, '2026-03-27 15:11:52.000000');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_source_of_income`
--

CREATE TABLE `tbl_source_of_income` (
  `in_id` int(11) NOT NULL,
  `in_name` varchar(50) NOT NULL,
  `in_descr` varchar(100) DEFAULT NULL,
  `in_status` int(11) NOT NULL,
  `reg_date` timestamp(6) NOT NULL DEFAULT current_timestamp(6)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_source_of_income`
--

INSERT INTO `tbl_source_of_income` (`in_id`, `in_name`, `in_descr`, `in_status`, `reg_date`) VALUES
(1, 'sells', '345yu', 1, '2026-02-25 11:46:56.045526'),
(2, 'donnations', 'donnations', 1, '2026-02-25 11:48:59.616803'),
(3, 'welcwishers', '2ergb', 1, '2026-02-25 12:50:49.177117');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_worker_loan`
--

CREATE TABLE `tbl_worker_loan` (
  `l_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `request_amount` int(11) NOT NULL,
  `payed_amount` int(11) NOT NULL,
  `description` text NOT NULL,
  `status` enum('pending','outstanding','disbursed','rejected','paid') NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_worker_loan`
--

INSERT INTO `tbl_worker_loan` (`l_id`, `user_id`, `request_amount`, `payed_amount`, `description`, `status`, `created_at`) VALUES
(1, 3, 55678, 0, 'ruiko', 'pending', NULL),
(2, 3, 7, 0, 'hk', 'rejected', NULL),
(3, 3, 6, 0, '678o', 'outstanding', NULL),
(4, 3, 7, 0, 'f', 'disbursed', NULL),
(5, 3, 444, 0, 'ffgggggggggg', 'rejected', NULL),
(6, 3, 5, 0, '555555555', 'disbursed', NULL),
(7, 1, 89, 19, 'hjhhhh', 'disbursed', '2026-02-11 09:54:12.000000'),
(8, 1, 5000, 0, 'kdfernfjbjjdnfn', 'outstanding', '2026-02-13 14:51:29.000000');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_worker_loan_payment`
--

CREATE TABLE `tbl_worker_loan_payment` (
  `p_id` int(11) NOT NULL,
  `pay_account` int(11) NOT NULL,
  `loan_id` int(11) NOT NULL,
  `payed_amount` int(11) NOT NULL,
  `due_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `description` varchar(255) DEFAULT NULL,
  `debited_account` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_worker_loan_payment`
--

INSERT INTO `tbl_worker_loan_payment` (`p_id`, `pay_account`, `loan_id`, `payed_amount`, `due_date`, `description`, `debited_account`) VALUES
(1, 444, 7, 9, '2026-02-13 10:52:50', 'rrr', 2),
(2, 3456, 7, 10, '2026-02-13 11:00:13', 'eeee', 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default-avatar.png',
  `role_id` int(11) NOT NULL DEFAULT 1,
  `status` enum('active','inactive','suspended','pending') DEFAULT 'pending',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL,
  `location_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `uuid`, `first_name`, `last_name`, `email`, `password`, `phone`, `avatar`, `role_id`, `status`, `email_verified_at`, `last_login_at`, `created_at`, `updated_at`, `deleted_at`, `location_id`) VALUES
(1, 'fd7e9368-01c1-11f1-9e24-c8348e2a75f3', 'Admin', 'User', 'admin@gihangacoffee.com', '$2y$12$zAjvK19eGDUsC8gJnHHCO.EpIW8dVzZeElgTnKIkwVg21ZMVarJve', '', NULL, 1, 'active', '2026-02-04 12:06:51', '2026-02-26 06:54:43', '2026-02-04 12:06:51', '2026-02-26 06:54:43', NULL, 1),
(2, 'ebd73031-e439-42fe-a531-d8f6b7694cae', 'Uwimana', 'Christian', 'uwimanachris4@gmail.com', '$2y$12$aowMEy.CEM5O9eW3qEBJs.Nff4HbdwK9O9WDT1wDgqsLzgCTB2Ut.', '0783093055', 'default-avatar.png', 1, 'active', NULL, '2026-02-05 11:30:09', '2026-02-04 13:44:43', '2026-02-23 09:28:22', NULL, 1),
(3, 'eb6f69f7-4ce5-4a34-923e-6cff4abf6661', 'wertyu', 'wertgh', 'uwayezujean2001@gmail.com', '$2y$12$BbpphTiKxy/hy9g2Qpa6w.vhxTVf2ivasgvcAcAvg943Vd7kCUKgC', NULL, 'default-avatar.png', 1, 'active', NULL, '2026-02-25 08:55:41', '2026-02-16 07:16:57', '2026-02-25 08:55:41', NULL, 1),
(4, '35bd71da-3034-437b-a96a-f1ac0d8522ea', 'UWAYEZU', 'Jean Felix', 'wedplannnner@gmail.com', '$2y$12$2H0hvGIiXuyrFTNHVqvWx.sk/koIIjjV9wxogqm6SQgO4ozKJ9E1a', '0789136352', 'default-avatar.png', 3, 'active', NULL, NULL, '2026-02-20 08:23:22', '2026-02-20 08:23:22', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_activity_logs`
--

CREATE TABLE `user_activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_activity_logs`
--

INSERT INTO `user_activity_logs` (`id`, `user_id`, `action`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 12:07:27'),
(2, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 12:07:45'),
(3, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 12:38:16'),
(4, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 12:38:30'),
(5, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:04:47'),
(6, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:04:57'),
(7, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:09:15'),
(8, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:09:26'),
(9, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:12:26'),
(10, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:13:18'),
(11, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:22:01'),
(12, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:22:21'),
(13, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:24:35'),
(14, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:24:45'),
(15, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:26:57'),
(16, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:27:10'),
(17, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:29:09'),
(18, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:29:25'),
(19, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 15:15:15'),
(20, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:04:10'),
(21, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 11:29:45'),
(22, 1, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 11:30:02'),
(23, 2, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 11:30:09'),
(24, 2, 'logout', 'User logged out', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 11:31:43'),
(25, 1, 'login', 'User logged in successfully', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:22:37'),
(26, 1, 'login', 'User logged in successfully', '196.12.148.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:09:45'),
(27, 1, 'login', 'User logged in successfully', '196.12.148.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:10:32'),
(28, 1, 'logout', 'User logged out', '196.12.148.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:17:24'),
(29, 1, 'login', 'User logged in successfully', '196.12.148.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:18:39'),
(30, 1, 'login', 'User logged in successfully', '196.12.148.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 07:51:49'),
(31, 1, 'login', 'User logged in successfully', '196.12.148.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 08:36:54'),
(32, 1, 'login', 'User logged in successfully', '196.12.148.240', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-07 12:06:19'),
(33, 1, 'login', 'User logged in successfully', '105.178.32.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 06:37:04'),
(34, 1, 'logout', 'User logged out', '105.178.32.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 06:38:04'),
(35, 1, 'login', 'User logged in successfully', '105.178.32.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 06:38:28'),
(36, 1, 'logout', 'User logged out', '105.178.32.144', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 06:38:35'),
(37, 1, 'login', 'User logged in successfully', '41.216.103.149', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-11 07:59:59'),
(38, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 08:00:34'),
(39, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 09:25:21'),
(40, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-13 12:04:58'),
(41, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 12:08:19'),
(42, 1, 'login', 'User logged in successfully', '41.216.118.153', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-13 13:39:07'),
(43, 1, 'login', 'User logged in successfully', '41.216.118.153', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-13 13:40:36'),
(44, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-14 07:56:46'),
(45, 1, 'logout', 'User logged out', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-14 07:56:56'),
(46, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-14 07:57:02'),
(47, 1, 'logout', 'User logged out', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-14 07:57:09'),
(48, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-14 07:58:23'),
(49, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 08:04:48'),
(50, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-14 10:59:27'),
(51, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-14 11:52:34'),
(52, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 12:01:56'),
(53, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-14 13:46:26'),
(54, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-14 14:08:28'),
(55, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-14 14:34:31'),
(56, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 06:15:04'),
(57, 1, 'logout', 'User logged out', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 06:51:32'),
(58, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 07:15:58'),
(59, 3, 'register', 'User registered successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-02-16 07:16:57'),
(60, 3, 'logout', 'User logged out', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-02-16 07:17:37'),
(61, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 07:27:34'),
(62, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 07:39:18'),
(63, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 09:35:08'),
(64, 1, 'logout', 'User logged out', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 09:37:36'),
(65, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 09:37:39'),
(66, 1, 'login', 'User logged in successfully', '41.186.136.234', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 10:20:41'),
(67, 1, 'login', 'User logged in successfully', '41.186.134.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 10:24:01'),
(68, 1, 'login', 'User logged in successfully', '41.186.134.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 10:25:37'),
(69, 1, 'logout', 'User logged out', '41.186.139.112', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 10:51:08'),
(70, 1, 'login', 'User logged in successfully', '41.186.139.112', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-16 10:51:12'),
(71, 1, 'login', 'User logged in successfully', '41.186.134.41', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 12:37:23'),
(72, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-16 13:32:44'),
(73, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-17 13:12:41'),
(74, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-17 13:59:29'),
(75, 1, 'login', 'User logged in successfully', '41.173.249.104', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-18 06:37:32'),
(76, 1, 'login', 'User logged in successfully', '41.186.139.9', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-02-18 08:45:33'),
(77, 1, 'login', 'User logged in successfully', '41.186.134.82', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-18 09:00:13'),
(78, 1, 'login', 'User logged in successfully', '41.216.112.25', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-20 07:55:45'),
(79, 1, 'login', 'User logged in successfully', '41.216.122.227', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 08:35:23'),
(80, 1, 'logout', 'User logged out', '41.216.122.227', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 09:28:36'),
(81, 1, 'login', 'User logged in successfully', '41.216.122.227', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 09:28:43'),
(82, 1, 'login', 'User logged in successfully', '41.216.122.227', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 12:10:53'),
(83, 1, 'login', 'User logged in successfully', '41.216.122.227', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-23 13:22:50'),
(84, 1, 'login', 'User logged in successfully', '41.216.122.227', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 08:25:43'),
(85, 1, 'login', 'User logged in successfully', '41.216.122.227', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-24 13:19:04'),
(86, 1, 'login', 'User logged in successfully', '41.173.252.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 07:12:53'),
(87, 3, 'login', 'User logged in successfully', '41.173.252.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-02-25 08:55:41'),
(88, 1, 'login', 'User logged in successfully', '41.173.252.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 12:32:05'),
(89, 1, 'login', 'User logged in successfully', '41.173.252.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-25 14:11:27'),
(90, 1, 'login', 'User logged in successfully', '41.173.252.201', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', '2026-02-26 06:54:43');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_processing`
--

CREATE TABLE `warehouse_processing` (
  `id` int(11) NOT NULL,
  `processing_number` varchar(50) NOT NULL,
  `location_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `category_type_id` int(11) NOT NULL,
  `measurement_unit_id` int(11) NOT NULL,
  `input_quantity` decimal(15,4) NOT NULL,
  `output_quantity` decimal(15,4) DEFAULT NULL,
  `processing_step` varchar(100) DEFAULT NULL,
  `to_processing_step_id` int(11) DEFAULT NULL,
  `output_category_type_id` int(11) DEFAULT NULL,
  `output_measurement_unit_id` int(11) DEFAULT NULL,
  `processing_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `created_by` int(11) NOT NULL,
  `completed_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouse_processing`
--

INSERT INTO `warehouse_processing` (`id`, `processing_number`, `location_id`, `supplier_id`, `category_type_id`, `measurement_unit_id`, `input_quantity`, `output_quantity`, `processing_step`, `to_processing_step_id`, `output_category_type_id`, `output_measurement_unit_id`, `processing_date`, `notes`, `status`, `created_by`, `completed_by`, `created_at`, `completed_at`) VALUES
(1, 'WP202602110001', 3, NULL, 3, 3, 15.0000, 15.0000, NULL, 17, NULL, NULL, '2026-02-11', '', 'completed', 1, 1, '2026-02-11 11:15:59', '2026-02-11 11:18:41'),
(2, 'WP202602110002', 3, NULL, 3, 3, 20.0000, 20.0000, NULL, 17, NULL, NULL, '2026-02-11', '', 'completed', 1, 1, '2026-02-11 11:26:58', '2026-02-11 12:19:59'),
(3, 'WP202602130001', 5, NULL, 7, 3, 20.0000, 20.0000, NULL, 17, NULL, NULL, '2026-02-13', '', 'completed', 1, 1, '2026-02-13 13:33:23', '2026-02-13 13:34:09'),
(4, 'WP202602130002', 5, NULL, 8, 3, 15.0000, 10.0000, NULL, 18, NULL, NULL, '2026-02-13', '', 'completed', 1, 1, '2026-02-13 13:35:15', '2026-02-13 13:35:55'),
(5, 'WP202602250001', 1, NULL, 4, 3, 3.0000, 2.0000, NULL, 17, NULL, NULL, '2026-02-25', '', 'completed', 1, 1, '2026-02-25 09:18:52', '2026-02-25 10:19:54');

-- --------------------------------------------------------

--
-- Table structure for table `warehouse_processing_items`
--

CREATE TABLE `warehouse_processing_items` (
  `id` int(11) NOT NULL,
  `processing_id` int(11) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `from_processing_step_id` int(11) DEFAULT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `quantity_in_base` decimal(15,4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `warehouse_processing_items`
--

INSERT INTO `warehouse_processing_items` (`id`, `processing_id`, `supplier_id`, `from_processing_step_id`, `quantity`, `quantity_in_base`, `created_at`) VALUES
(1, 1, 3, 16, 5.0000, 5000.0000, '2026-02-11 11:15:59'),
(2, 1, 4, 16, 10.0000, 10000.0000, '2026-02-11 11:15:59'),
(3, 2, 3, 16, 10.0000, 10000.0000, '2026-02-11 11:26:58'),
(4, 2, 4, 16, 10.0000, 10000.0000, '2026-02-11 11:26:58'),
(5, 3, 6, 16, 20.0000, 20000.0000, '2026-02-13 13:33:23'),
(6, 4, 6, 17, 15.0000, 15000.0000, '2026-02-13 13:35:15'),
(7, 5, 886, 16, 3.0000, 3000.0000, '2026-02-25 09:18:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_account` (`location_id`,`payment_mode_id`,`account_number`),
  ADD KEY `location_type_id` (`location_type_id`),
  ADD KEY `payment_mode_id` (`payment_mode_id`);

--
-- Indexes for table `account_transactions`
--
ALTER TABLE `account_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_number` (`transaction_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_account` (`account_id`),
  ADD KEY `idx_reference` (`reference_type`,`reference_id`),
  ADD KEY `idx_date` (`transaction_date`);

--
-- Indexes for table `category_types`
--
ALTER TABLE `category_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `category_type_units`
--
ALTER TABLE `category_type_units`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_type_unit` (`category_type_id`,`measurement_unit_id`),
  ADD KEY `measurement_unit_id` (`measurement_unit_id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `client_identifier_values`
--
ALTER TABLE `client_identifier_values`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_client_identifier` (`client_id`,`identifier_id`),
  ADD KEY `identifier_id` (`identifier_id`);

--
-- Indexes for table `client_types`
--
ALTER TABLE `client_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `identifier` (`identifier`);

--
-- Indexes for table `client_type_identifiers`
--
ALTER TABLE `client_type_identifiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_type_id` (`client_type_id`);

--
-- Indexes for table `company`
--
ALTER TABLE `company`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jwt_tokens`
--
ALTER TABLE `jwt_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_token_hash` (`token_hash`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`location_type_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `location_types`
--
ALTER TABLE `location_types`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `location_type_categories`
--
ALTER TABLE `location_type_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_location_category` (`location_type_id`,`product_category_id`),
  ADD KEY `product_category_id` (`product_category_id`);

--
-- Indexes for table `measurement_units`
--
ALTER TABLE `measurement_units`
  ADD PRIMARY KEY (`id`),
  ADD KEY `base_unit_id` (`base_unit_id`);

--
-- Indexes for table `non_exploitable_types`
--
ALTER TABLE `non_exploitable_types`
  ADD PRIMARY KEY (`eploi_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_token` (`token`);

--
-- Indexes for table `payment_modes`
--
ALTER TABLE `payment_modes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_name` (`name`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `processing_steps`
--
ALTER TABLE `processing_steps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `production`
--
ALTER TABLE `production`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `production_number` (`production_number`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `input_category_type_unit_id` (`input_category_type_unit_id`),
  ADD KEY `output_category_type_unit_id` (`output_category_type_unit_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `completed_by` (`completed_by`),
  ADD KEY `idx_supplier` (`supplier_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `idx_name` (`name`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sale_number` (`sale_number`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `confirmed_by` (`confirmed_by`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_category_id` (`product_category_id`),
  ADD KEY `category_type_unit_id` (`category_type_unit_id`),
  ADD KEY `processing_step_id` (`processing_step_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_key` (`setting_key`);

--
-- Indexes for table `stock_receives`
--
ALTER TABLE `stock_receives`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_summary`
--
ALTER TABLE `stock_summary`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_type_id` (`supplier_type_id`);

--
-- Indexes for table `supplier_advances`
--
ALTER TABLE `supplier_advances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `advance_number` (`advance_number`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `account_id` (`account_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `supplier_identifiers`
--
ALTER TABLE `supplier_identifiers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_supplier_identifier` (`supplier_id`,`identifier_id`),
  ADD KEY `identifier_id` (`identifier_id`);

--
-- Indexes for table `supplier_payables`
--
ALTER TABLE `supplier_payables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payable_number` (`payable_number`),
  ADD KEY `stock_receive_id` (`stock_receive_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `supplier_types`
--
ALTER TABLE `supplier_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_type_identifiers`
--
ALTER TABLE `supplier_type_identifiers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_type_id` (`supplier_type_id`);

--
-- Indexes for table `tbl_certification`
--
ALTER TABLE `tbl_certification`
  ADD PRIMARY KEY (`cert_id`);

--
-- Indexes for table `tbl_certification_transactions`
--
ALTER TABLE `tbl_certification_transactions`
  ADD PRIMARY KEY (`trans_id`);

--
-- Indexes for table `tbl_currency_type`
--
ALTER TABLE `tbl_currency_type`
  ADD PRIMARY KEY (`currency_id`);

--
-- Indexes for table `tbl_expensecategories`
--
ALTER TABLE `tbl_expensecategories`
  ADD PRIMARY KEY (`categ_id`);

--
-- Indexes for table `tbl_expenseconsume`
--
ALTER TABLE `tbl_expenseconsume`
  ADD PRIMARY KEY (`con_id`);

--
-- Indexes for table `tbl_expenseconsumer`
--
ALTER TABLE `tbl_expenseconsumer`
  ADD PRIMARY KEY (`cons_id`);

--
-- Indexes for table `tbl_expenses`
--
ALTER TABLE `tbl_expenses`
  ADD PRIMARY KEY (`expense_id`);

--
-- Indexes for table `tbl_expense_conume_details`
--
ALTER TABLE `tbl_expense_conume_details`
  ADD PRIMARY KEY (`det_id`);

--
-- Indexes for table `tbl_loan_disbursment`
--
ALTER TABLE `tbl_loan_disbursment`
  ADD PRIMARY KEY (`dis_id`);

--
-- Indexes for table `tbl_nonexploitable_transactions`
--
ALTER TABLE `tbl_nonexploitable_transactions`
  ADD PRIMARY KEY (`trans_id`);

--
-- Indexes for table `tbl_proforma_invoice`
--
ALTER TABLE `tbl_proforma_invoice`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `currency_id` (`currency_id`);

--
-- Indexes for table `tbl_proforma_invoice_items`
--
ALTER TABLE `tbl_proforma_invoice_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invoice_id` (`invoice_id`);

--
-- Indexes for table `tbl_properties`
--
ALTER TABLE `tbl_properties`
  ADD PRIMARY KEY (`property_id`);

--
-- Indexes for table `tbl_property_type`
--
ALTER TABLE `tbl_property_type`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `tbl_receipttype`
--
ALTER TABLE `tbl_receipttype`
  ADD PRIMARY KEY (`rec_id`);

--
-- Indexes for table `tbl_recharge_history`
--
ALTER TABLE `tbl_recharge_history`
  ADD PRIMARY KEY (`rech_id`);

--
-- Indexes for table `tbl_source_of_income`
--
ALTER TABLE `tbl_source_of_income`
  ADD PRIMARY KEY (`in_id`);

--
-- Indexes for table `tbl_worker_loan`
--
ALTER TABLE `tbl_worker_loan`
  ADD PRIMARY KEY (`l_id`);

--
-- Indexes for table `tbl_worker_loan_payment`
--
ALTER TABLE `tbl_worker_loan_payment`
  ADD PRIMARY KEY (`p_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uuid` (`uuid`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_user_role` (`role_id`);

--
-- Indexes for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `warehouse_processing`
--
ALTER TABLE `warehouse_processing`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `processing_number` (`processing_number`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `category_type_id` (`category_type_id`),
  ADD KEY `measurement_unit_id` (`measurement_unit_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `completed_by` (`completed_by`),
  ADD KEY `to_processing_step_id` (`to_processing_step_id`);

--
-- Indexes for table `warehouse_processing_items`
--
ALTER TABLE `warehouse_processing_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `processing_id` (`processing_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `from_processing_step_id` (`from_processing_step_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `account_transactions`
--
ALTER TABLE `account_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `category_types`
--
ALTER TABLE `category_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `category_type_units`
--
ALTER TABLE `category_type_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `client_identifier_values`
--
ALTER TABLE `client_identifier_values`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `client_types`
--
ALTER TABLE `client_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `client_type_identifiers`
--
ALTER TABLE `client_type_identifiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `company`
--
ALTER TABLE `company`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jwt_tokens`
--
ALTER TABLE `jwt_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `location_types`
--
ALTER TABLE `location_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `location_type_categories`
--
ALTER TABLE `location_type_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `measurement_units`
--
ALTER TABLE `measurement_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `non_exploitable_types`
--
ALTER TABLE `non_exploitable_types`
  MODIFY `eploi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_modes`
--
ALTER TABLE `payment_modes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `processing_steps`
--
ALTER TABLE `processing_steps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `production`
--
ALTER TABLE `production`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1071;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `stock_receives`
--
ALTER TABLE `stock_receives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `stock_summary`
--
ALTER TABLE `stock_summary`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `stock_transfers`
--
ALTER TABLE `stock_transfers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=887;

--
-- AUTO_INCREMENT for table `supplier_advances`
--
ALTER TABLE `supplier_advances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_identifiers`
--
ALTER TABLE `supplier_identifiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `supplier_payables`
--
ALTER TABLE `supplier_payables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `supplier_types`
--
ALTER TABLE `supplier_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `supplier_type_identifiers`
--
ALTER TABLE `supplier_type_identifiers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_certification`
--
ALTER TABLE `tbl_certification`
  MODIFY `cert_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_certification_transactions`
--
ALTER TABLE `tbl_certification_transactions`
  MODIFY `trans_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_currency_type`
--
ALTER TABLE `tbl_currency_type`
  MODIFY `currency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_expensecategories`
--
ALTER TABLE `tbl_expensecategories`
  MODIFY `categ_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_expenseconsume`
--
ALTER TABLE `tbl_expenseconsume`
  MODIFY `con_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_expenseconsumer`
--
ALTER TABLE `tbl_expenseconsumer`
  MODIFY `cons_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_expenses`
--
ALTER TABLE `tbl_expenses`
  MODIFY `expense_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_expense_conume_details`
--
ALTER TABLE `tbl_expense_conume_details`
  MODIFY `det_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `tbl_loan_disbursment`
--
ALTER TABLE `tbl_loan_disbursment`
  MODIFY `dis_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_nonexploitable_transactions`
--
ALTER TABLE `tbl_nonexploitable_transactions`
  MODIFY `trans_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_proforma_invoice`
--
ALTER TABLE `tbl_proforma_invoice`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_proforma_invoice_items`
--
ALTER TABLE `tbl_proforma_invoice_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tbl_properties`
--
ALTER TABLE `tbl_properties`
  MODIFY `property_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_property_type`
--
ALTER TABLE `tbl_property_type`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_receipttype`
--
ALTER TABLE `tbl_receipttype`
  MODIFY `rec_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_recharge_history`
--
ALTER TABLE `tbl_recharge_history`
  MODIFY `rech_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tbl_source_of_income`
--
ALTER TABLE `tbl_source_of_income`
  MODIFY `in_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_worker_loan`
--
ALTER TABLE `tbl_worker_loan`
  MODIFY `l_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_worker_loan_payment`
--
ALTER TABLE `tbl_worker_loan_payment`
  MODIFY `p_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `warehouse_processing`
--
ALTER TABLE `warehouse_processing`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `warehouse_processing_items`
--
ALTER TABLE `warehouse_processing_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`location_type_id`) REFERENCES `location_types` (`id`),
  ADD CONSTRAINT `accounts_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `accounts_ibfk_3` FOREIGN KEY (`payment_mode_id`) REFERENCES `payment_modes` (`id`);

--
-- Constraints for table `account_transactions`
--
ALTER TABLE `account_transactions`
  ADD CONSTRAINT `account_transactions_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `account_transactions_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `category_types`
--
ALTER TABLE `category_types`
  ADD CONSTRAINT `category_types_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `category_type_units`
--
ALTER TABLE `category_type_units`
  ADD CONSTRAINT `category_type_units_ibfk_1` FOREIGN KEY (`category_type_id`) REFERENCES `category_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `category_type_units_ibfk_2` FOREIGN KEY (`measurement_unit_id`) REFERENCES `measurement_units` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_identifier_values`
--
ALTER TABLE `client_identifier_values`
  ADD CONSTRAINT `client_identifier_values_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_identifier_values_ibfk_2` FOREIGN KEY (`identifier_id`) REFERENCES `client_type_identifiers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `client_type_identifiers`
--
ALTER TABLE `client_type_identifiers`
  ADD CONSTRAINT `client_type_identifiers_ibfk_1` FOREIGN KEY (`client_type_id`) REFERENCES `client_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jwt_tokens`
--
ALTER TABLE `jwt_tokens`
  ADD CONSTRAINT `jwt_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `locations`
--
ALTER TABLE `locations`
  ADD CONSTRAINT `locations_ibfk_1` FOREIGN KEY (`location_type_id`) REFERENCES `location_types` (`id`);

--
-- Constraints for table `location_type_categories`
--
ALTER TABLE `location_type_categories`
  ADD CONSTRAINT `location_type_categories_ibfk_1` FOREIGN KEY (`location_type_id`) REFERENCES `location_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `location_type_categories_ibfk_2` FOREIGN KEY (`product_category_id`) REFERENCES `product_categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `measurement_units`
--
ALTER TABLE `measurement_units`
  ADD CONSTRAINT `measurement_units_ibfk_1` FOREIGN KEY (`base_unit_id`) REFERENCES `measurement_units` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `production`
--
ALTER TABLE `production`
  ADD CONSTRAINT `production_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `production_ibfk_2` FOREIGN KEY (`input_category_type_unit_id`) REFERENCES `category_type_units` (`id`),
  ADD CONSTRAINT `production_ibfk_3` FOREIGN KEY (`output_category_type_unit_id`) REFERENCES `category_type_units` (`id`),
  ADD CONSTRAINT `production_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `production_ibfk_5` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD CONSTRAINT `suppliers_ibfk_1` FOREIGN KEY (`supplier_type_id`) REFERENCES `supplier_types` (`id`);

--
-- Constraints for table `supplier_advances`
--
ALTER TABLE `supplier_advances`
  ADD CONSTRAINT `supplier_advances_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`),
  ADD CONSTRAINT `supplier_advances_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`),
  ADD CONSTRAINT `supplier_advances_ibfk_3` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `supplier_advances_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `supplier_identifiers`
--
ALTER TABLE `supplier_identifiers`
  ADD CONSTRAINT `supplier_identifiers_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `supplier_identifiers_ibfk_2` FOREIGN KEY (`identifier_id`) REFERENCES `supplier_type_identifiers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_type_identifiers`
--
ALTER TABLE `supplier_type_identifiers`
  ADD CONSTRAINT `supplier_type_identifiers_ibfk_1` FOREIGN KEY (`supplier_type_id`) REFERENCES `supplier_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_proforma_invoice_items`
--
ALTER TABLE `tbl_proforma_invoice_items`
  ADD CONSTRAINT `tbl_proforma_invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `tbl_proforma_invoice` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `user_activity_logs`
--
ALTER TABLE `user_activity_logs`
  ADD CONSTRAINT `user_activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
