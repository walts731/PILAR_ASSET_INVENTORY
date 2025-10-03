-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 03, 2025 at 08:14 AM
-- Server version: 10.6.15-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `inventory_pilar`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_borrowing_stats`
-- (See below for the actual view)
--
CREATE TABLE `active_borrowing_stats` (
`office_name` varchar(100)
,`total_borrowed` bigint(21)
,`total_quantity_borrowed` decimal(32,0)
,`unique_borrowers` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `module` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`log_id`, `user_id`, `activity`, `timestamp`, `module`) VALUES
(6, 1, 'Added 20 IT Equipment to inventory', '2025-04-02 02:05:00', 'Inventory Management'),
(7, 1, 'Requested 15 Office Supplies', '2025-04-02 03:10:00', 'Inventory Management'),
(8, 1, 'Borrowed 5 IT Equipment', '2025-04-02 04:15:00', 'Inventory Management'),
(9, 1, 'Transferred 10 Office Supplies to Admin', '2025-04-02 05:20:00', 'Inventory Management'),
(10, 1, 'Added 30 IT Equipment to inventory', '2025-04-02 06:25:00', 'Inventory Management');

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `key` varchar(64) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `archives`
--

CREATE TABLE `archives` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `filter_status` varchar(50) DEFAULT NULL,
  `filter_office` varchar(50) DEFAULT NULL,
  `filter_category` varchar(50) DEFAULT NULL,
  `filter_start_date` date DEFAULT NULL,
  `filter_end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_name` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `archives`
--

INSERT INTO `archives` (`id`, `user_id`, `action_type`, `filter_status`, `filter_office`, `filter_category`, `filter_start_date`, `filter_end_date`, `created_at`, `file_name`) VALUES
(1, 12, 'Export CSV', '', '4', '', '0000-00-00', '0000-00-00', '2025-04-16 09:39:18', 'asset_report_20250416_113918.csv'),
(2, 12, 'Export CSV', '', '4', '', '0000-00-00', '0000-00-00', '2025-04-21 11:55:23', 'asset_report_20250421_135523.csv'),
(3, 1, 'Export PDF', '', NULL, NULL, '0000-00-00', '0000-00-00', '2025-04-21 11:58:08', 'assets_report_20250421_135808.pdf'),
(4, 1, 'Export CSV', '', '', '', '0000-00-00', '0000-00-00', '2025-04-21 11:58:09', 'asset_report_20250421_135809.csv'),
(5, 1, 'Export PDF', '', NULL, NULL, '0000-00-00', '0000-00-00', '2025-04-21 11:58:21', 'assets_report_20250421_135821.pdf'),
(6, 12, 'Export PDF', '', NULL, NULL, '0000-00-00', '0000-00-00', '2025-04-21 11:59:41', 'assets_report_20250421_135941.pdf'),
(7, 12, 'Export CSV', '', '4', '', '0000-00-00', '0000-00-00', '2025-04-21 12:05:58', 'asset_report_20250421_140558.csv'),
(8, 12, 'Export PDF', '', NULL, NULL, '0000-00-00', '0000-00-00', '2025-04-21 12:06:05', 'assets_report_20250421_140605.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `id` int(11) NOT NULL,
  `asset_name` varchar(100) NOT NULL,
  `category` int(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `added_stock` int(11) DEFAULT 0,
  `unit` varchar(20) NOT NULL,
  `status` enum('available','borrowed','in use','damaged','disposed','unserviceable','unavailable','lost','pending','serviceable') NOT NULL DEFAULT 'serviceable',
  `acquisition_date` date DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `end_user` varchar(255) DEFAULT NULL,
  `red_tagged` tinyint(1) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `value` decimal(10,2) NOT NULL DEFAULT 0.00,
  `qr_code` varchar(255) DEFAULT NULL,
  `type` enum('asset','consumable') NOT NULL DEFAULT 'asset',
  `image` varchar(255) DEFAULT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `property_no` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `ics_id` int(11) DEFAULT NULL,
  `par_id` int(11) DEFAULT NULL,
  `asset_new_id` int(11) DEFAULT NULL,
  `inventory_tag` varchar(255) DEFAULT NULL,
  `additional_images` text DEFAULT NULL COMMENT 'JSON array storing paths to up to 4 additional images for the asset',
  `enable_batch_tracking` tinyint(1) DEFAULT 0,
  `default_batch_size` int(11) DEFAULT 1,
  `batch_expiry_required` tinyint(1) DEFAULT 0,
  `batch_manufacturer_required` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_name`, `category`, `description`, `quantity`, `added_stock`, `unit`, `status`, `acquisition_date`, `office_id`, `employee_id`, `end_user`, `red_tagged`, `last_updated`, `value`, `qr_code`, `type`, `image`, `serial_no`, `code`, `property_no`, `model`, `brand`, `ics_id`, `par_id`, `asset_new_id`, `inventory_tag`, `additional_images`, `enable_batch_tracking`, `default_batch_size`, `batch_expiry_required`, `batch_manufacturer_required`) VALUES
(1, 'Air freshener (Spray)', NULL, 'Air freshener (Spray)', 2, 2, 'bottle', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 10:59:42', 390.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(2, 'Air freshener (Ambi Pur)', NULL, 'Air freshener (Ambi Pur)', 2, 2, 'bottle', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 11:53:18', 390.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(3, 'Alcohol', NULL, 'Alcohol', 2, 2, 'bottle', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 11:56:18', 250.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(4, 'Ballpen HBW Matrix 50\'s black', NULL, 'Ballpen HBW Matrix 50\'s black', 3, 3, 'box', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 11:57:55', 223.63, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(5, 'Battey AA', NULL, 'Battey AA', 9, 9, 'pcs', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:01:38', 85.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(6, 'Battery AAA', NULL, 'Battery AAA', 9, 9, 'pcs', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:01:38', 75.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(7, 'Bond paper long S-20', NULL, 'Bond paper long S-20', 22, 22, 'reams', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:01:38', 330.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(8, 'Wheel Chair', 1, 'Wheel Chair', 1, 0, 'unit', 'unserviceable', '2025-09-29', 4, 65, 'John Kenneth Litana', 0, '2025-10-02 09:16:07', 6650.00, '8.png', 'asset', '', '', '2025-OFFEQ-0001', 'PN-2019-05-02-0001-01', '', '', 1, NULL, 1, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(9, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '9.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(10, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '10.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(11, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '11.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(12, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '12.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(13, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '13.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(14, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '14.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(15, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '15.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(16, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '16.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(17, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '17.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(18, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '18.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(19, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '19.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(20, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '20.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(21, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '21.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(22, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '22.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(23, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '23.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(24, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '24.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(25, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '25.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(26, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '26.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(27, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '27.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(28, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '28.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(29, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '29.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(30, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '30.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(31, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '31.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(32, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '32.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(33, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '33.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(34, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '34.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(35, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '35.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(36, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '36.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(37, 'Wheel Chair', NULL, 'Wheel Chair', 1, 0, 'unit', 'available', '2025-09-29', 4, NULL, NULL, 0, '2025-09-29 12:15:52', 6650.00, '37.png', 'asset', '', '', '', NULL, '', '', 1, NULL, 1, NULL, NULL, 0, 1, 0, 0),
(38, 'Notebook i7', 6, 'Notebook i7', 1, 0, 'unit', 'available', '2025-09-29', 4, 13, 'Angela Rizal', 0, '2025-09-30 02:23:42', 80245.00, '38.png', 'asset', '', '', '2025-ICT-0001', 'PN-2019-05-02-0001-01', '', '', NULL, 2, 2, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(39, 'Laptop AMD Ryzen', 6, 'Laptop AMD Ryzen', 1, 0, 'unit', 'unserviceable', '2025-09-30', 4, 12, 'Roberto Cruz', 1, '2025-09-30 03:04:15', 45000.00, '39.png', 'asset', '', 'SN-DC-2025-0001', '2025-ICT-0001', 'PN-2019-05-02-0001-01', 'Latitude 5430', 'Lenovo', 2, NULL, 3, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(40, 'Ballpen ', NULL, 'Ballpen ', 3, 3, 'box', 'available', '2025-09-30', 4, NULL, NULL, 0, '2025-09-30 03:09:02', 340.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(41, 'Bond paper', NULL, 'Bond paper', 2, 2, 'reams', 'available', '2025-09-30', 4, NULL, NULL, 0, '2025-09-30 03:09:02', 300.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(42, 'Tissue', NULL, 'Tissue', 3, 3, 'pcs', 'available', '2025-09-30', 4, NULL, NULL, 0, '2025-09-30 03:09:02', 50.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(43, 'Laptop Acer', 6, 'Laptop Acer Aspire 7', 1, 0, 'unit', 'serviceable', '2025-09-30', 4, 86, 'John Kenneth Litana', 0, '2025-09-30 05:09:58', 50000.00, '43.png', 'asset', 'asset_43_1759208840.jpg', 'SN-A7-2025-00001', '2025-ICT-0001', 'PN-2025-0001', 'Aspire 7', 'Acer', NULL, NULL, 4, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(44, 'Grass cutter', 5, 'Grass cutter', 1, 0, 'unit', 'available', '2025-09-30', 4, 15, 'John Kenneth Litana', 0, '2025-10-01 11:14:57', 18000.00, '44.png', 'asset', '', '', '2025-MACH-0001', 'PN-2019-05-02-0001-01', '', '', 3, NULL, 5, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(45, 'Grass cutter', NULL, 'Grass cutter', 1, 0, 'unit', 'available', '2025-09-30', 4, NULL, NULL, 0, '2025-09-30 15:34:51', 18000.00, '45.png', 'asset', '', '', '', NULL, '', '', 3, NULL, 5, NULL, NULL, 0, 1, 0, 0),
(46, 'Power Drill (corded)', 5, 'Power Drill (corded)', 1, 0, 'unit', 'available', '2025-09-30', 4, 61, 'John Legend', 0, '2025-10-03 05:56:37', 3500.00, '46.png', 'asset', '', '', '2025-MACH-0001', 'PN-2019-05-02-0001-01', '', '', 3, NULL, 6, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(47, 'Printer Epson', NULL, 'Printer Epson', 1, 0, 'unit', 'available', '2025-10-01', 34, NULL, NULL, 0, '2025-10-01 04:00:32', 4500.00, '47.png', 'asset', '', '', '', NULL, '', '', 5, NULL, 7, NULL, NULL, 0, 1, 0, 0),
(48, 'Ink', NULL, 'Ink', 10, 10, 'pcs', 'available', '2025-10-01', 4, NULL, NULL, 0, '2025-10-01 05:53:42', 58.00, '', 'consumable', '', '', '', '1', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(50, 'HP Pavilion', NULL, 'HP Pavilion', 1, 0, 'unit', 'available', '2025-10-01', 7, NULL, NULL, 0, '2025-10-01 15:00:32', 53000.00, '50.png', 'asset', '', '', '', NULL, '', '', NULL, 3, 8, NULL, NULL, 0, 1, 0, 0),
(51, 'Medical X-Ray Machine', NULL, 'Medical X-Ray Machine', 1, 0, 'unit', 'available', '2025-10-01', 7, NULL, NULL, 0, '2025-10-01 15:00:32', 120000.00, '51.png', 'asset', '', '', '', NULL, '', '', NULL, 3, 9, NULL, NULL, 0, 1, 0, 0),
(52, 'Conference Table', 2, 'Conference Table', 1, 0, 'unit', 'available', '2025-10-02', 33, 14, 'John Legend', 0, '2025-10-02 10:41:40', 42000.00, '52.png', 'asset', '', 'SN-DC-2025-0001324347', '2025-FUR-0002', 'PN-2019-05-02-0001-01', 'Mesh Back', 'Fursys', 6, NULL, 10, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(53, 'conference table', 2, 'conference table', 1, 0, 'unit', 'unserviceable', '2025-10-02', 33, 25, 'Jake Paul', 1, '2025-10-02 11:23:27', 10000.00, '53.png', 'asset', '', 'SN-DC-2025-00013243', '2025-FUR-0001', 'PN-2019-05-02-0001-01', 'Mesh Back', '', 7, NULL, 11, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(54, 'Laptop i7', NULL, 'Laptop i7', 1, 0, 'unit', 'available', '2025-10-02', 7, NULL, NULL, 0, '2025-10-02 10:02:16', 70000.00, '54.png', 'asset', '', '', '', NULL, '', '', NULL, 4, 12, NULL, NULL, 0, 1, 0, 0),
(55, 'computer desktop', NULL, 'computer desktop', 1, 0, 'unit', 'available', '2025-10-02', 19, NULL, NULL, 0, '2025-10-02 12:21:33', 52000.00, '55.png', 'asset', '', '', '', NULL, '', '', 8, NULL, 13, NULL, NULL, 0, 1, 0, 0),
(56, 'Lappy', NULL, 'Lappy', 1, 0, 'unit', 'available', '2025-10-02', 24, NULL, NULL, 0, '2025-10-02 12:25:57', 54000.00, '56.png', 'asset', '', '', '', NULL, '', '', 9, NULL, 14, NULL, NULL, 0, 1, 0, 0),
(57, 'Hilux Van', NULL, 'Hilux Van', 1, 0, 'unit', 'available', '2025-10-02', 7, NULL, NULL, 0, '2025-10-02 12:37:37', 2000000.00, '57.png', 'asset', '', '', '', NULL, '', '', NULL, 5, 15, NULL, NULL, 0, 1, 0, 0);

--
-- Triggers `assets`
--
DELIMITER $$
CREATE TRIGGER `tr_assets_update_validation` BEFORE UPDATE ON `assets` FOR EACH ROW BEGIN
    -- Validate quantity is not negative
    IF NEW.quantity < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Asset quantity cannot be negative';
    END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_assets_validation` BEFORE INSERT ON `assets` FOR EACH ROW BEGIN
    -- Validate quantity is not negative
    IF NEW.quantity < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Asset quantity cannot be negative';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `assets_archive`
--

CREATE TABLE `assets_archive` (
  `archive_id` int(11) NOT NULL,
  `id` int(11) DEFAULT NULL,
  `asset_name` varchar(100) DEFAULT NULL,
  `category` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  `acquisition_date` date DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `red_tagged` tinyint(1) DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `value` decimal(10,2) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets_archive`
--

INSERT INTO `assets_archive` (`archive_id`, `id`, `asset_name`, `category`, `description`, `quantity`, `unit`, `status`, `acquisition_date`, `office_id`, `red_tagged`, `last_updated`, `value`, `qr_code`, `type`, `archived_at`) VALUES
(6, 89, '0', NULL, 'Desktop Computer (Core i5)', 1, 'unit', 'available', '2025-09-24', 4, 0, '2025-09-24 07:18:16', 546740.00, '64.png', 'asset', '2025-09-27 15:01:48'),
(7, 62, '0', NULL, 'Mouse', 1, 'unit', 'available', '2025-09-24', 4, 0, '2025-09-24 07:16:25', 453.00, '62.png', 'asset', '2025-09-27 15:03:27'),
(8, 35, '0', NULL, 'Lenovo AMD Ryzen 7', 1, 'unit', 'unserviceable', '2025-09-22', 4, 1, '2025-09-22 10:04:13', 75000.00, '35.png', 'asset', '2025-09-27 15:04:51'),
(9, 90, '0', NULL, 'Desktop Computer (Core i5)', 1, 'unit', 'available', '2025-09-24', 4, 0, '2025-09-24 07:18:16', 546740.00, '65.png', 'asset', '2025-09-28 06:32:58'),
(31, 49, '0', NULL, 'HP Pavilion', 1, 'unit', 'available', '2025-10-01', 7, 0, '2025-10-01 15:00:32', 53000.00, '49.png', 'asset', '2025-10-02 01:08:08');

-- --------------------------------------------------------

--
-- Table structure for table `assets_new`
--

CREATE TABLE `assets_new` (
  `id` int(10) UNSIGNED NOT NULL,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(50) NOT NULL,
  `office_id` int(11) NOT NULL DEFAULT 0,
  `par_id` int(11) DEFAULT NULL,
  `ics_id` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `assets_new`
--

INSERT INTO `assets_new` (`id`, `description`, `quantity`, `unit_cost`, `unit`, `office_id`, `par_id`, `ics_id`, `date_created`) VALUES
(1, 'Wheel Chair', 30, 6650.00, 'unit', 4, NULL, 1, '2025-09-29 17:15:52'),
(2, 'Notebook i7', 1, 80245.00, 'unit', 4, 2, NULL, '2025-09-29 18:24:35'),
(3, 'Laptop AMD Ryzen', 1, 45000.00, 'unit', 4, NULL, 2, '2025-09-30 10:46:36'),
(4, 'Laptop Acer', 1, 50000.00, 'unit', 4, NULL, NULL, '2025-09-30 13:06:19'),
(5, 'Grass cutter', 2, 18000.00, 'unit', 4, NULL, 3, '2025-09-30 23:34:51'),
(6, 'Power Drill (corded)', 1, 3500.00, 'unit', 4, NULL, 3, '2025-09-30 23:34:51'),
(7, 'Printer Epson', 1, 4500.00, 'unit', 34, NULL, 5, '2025-10-01 12:00:32'),
(8, 'HP Pavilion', 1, 53000.00, 'unit', 7, 3, NULL, '2025-10-01 23:00:32'),
(9, 'Medical X-Ray Machine', 1, 120000.00, 'unit', 7, 3, NULL, '2025-10-01 23:00:32'),
(10, 'Conference Table', 1, 42000.00, 'unit', 33, NULL, 6, '2025-10-02 17:55:12'),
(11, 'conference table', 1, 10000.00, 'unit', 33, NULL, 7, '2025-10-02 18:00:30'),
(12, 'Laptop i7', 1, 70000.00, 'unit', 7, 4, NULL, '2025-10-02 18:02:16'),
(13, 'computer desktop', 1, 52000.00, 'unit', 19, NULL, 8, '2025-10-02 20:21:33'),
(14, 'Lappy', 1, 54000.00, 'unit', 24, NULL, 9, '2025-10-02 20:25:57'),
(15, 'Hilux Van', 1, 2000000.00, 'unit', 7, 5, NULL, '2025-10-02 20:37:37');

-- --------------------------------------------------------

--
-- Table structure for table `asset_lifecycle_events`
--

CREATE TABLE `asset_lifecycle_events` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `event_type` enum('ACQUIRED','ASSIGNED','TRANSFERRED','DISPOSAL_LISTED','DISPOSED','RED_TAGGED') NOT NULL,
  `ref_table` varchar(64) DEFAULT NULL,
  `ref_id` int(11) DEFAULT NULL,
  `from_employee_id` int(11) DEFAULT NULL,
  `to_employee_id` int(11) DEFAULT NULL,
  `from_office_id` int(11) DEFAULT NULL,
  `to_office_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_lifecycle_events`
--

INSERT INTO `asset_lifecycle_events` (`id`, `asset_id`, `event_type`, `ref_table`, `ref_id`, `from_employee_id`, `to_employee_id`, `from_office_id`, `to_office_id`, `notes`, `created_at`) VALUES
(1, 53, 'ACQUIRED', 'ics_form', 7, NULL, NULL, NULL, 33, 'ICS ICS-2025-0009; Qty 1; UnitCost ₱10000.00; Total ₱10000.00', '2025-10-02 10:00:30'),
(2, 54, 'ACQUIRED', 'par_form', 4, NULL, NULL, NULL, 7, 'PAR LGU-PAR-2025-0009; Qty 1; UnitCost ₱70000.00; Amount ₱70000.00', '2025-10-02 10:02:16'),
(3, 53, 'ASSIGNED', 'mr_details', NULL, NULL, 16, 33, NULL, 'MR create; PA: James Taylor; InvTag: PS-5S-03-F02-01-01', '2025-10-02 10:03:43'),
(4, 52, 'ASSIGNED', 'mr_details', NULL, NULL, 14, 33, NULL, 'MR create; PA: David Anderson; InvTag: PS-5S-03-F02-01-01', '2025-10-02 10:41:40'),
(5, 53, 'TRANSFERRED', 'itr_form', 16, 16, 25, NULL, NULL, 'ITR ITR-2025-0011; Reason: reass; To: Amelia Lewis', '2025-10-02 11:15:05'),
(6, 53, 'DISPOSAL_LISTED', 'iirup_form', 3, NULL, NULL, NULL, NULL, 'IIRUP #3; Remarks: Unserviceable; Method: N/A', '2025-10-02 11:16:35'),
(7, 53, 'RED_TAGGED', 'red_tags', 2, NULL, NULL, NULL, NULL, 'Removal: Broken; Action: For Disposal; Location: Supply Office', '2025-10-02 11:23:27'),
(8, 55, 'ACQUIRED', 'ics_form', 8, NULL, NULL, NULL, 19, 'ICS ICS-2025-00041; Qty 1; UnitCost ₱52000.00; Total ₱52000.00', '2025-10-02 12:21:33'),
(9, 56, 'ACQUIRED', 'ics_form', 9, NULL, NULL, NULL, 24, 'ICS ICS-2025-00022; Qty 1; UnitCost ₱54000.00; Total ₱54000.00', '2025-10-02 12:25:57'),
(10, 57, 'ACQUIRED', 'par_form', 5, NULL, NULL, NULL, 7, 'PAR PAR-00051; Qty 1; UnitCost ₱2000000.00; Amount ₱2000000.00', '2025-10-02 12:37:37'),
(11, 46, 'TRANSFERRED', 'itr_form', 17, 2, 61, NULL, NULL, 'ITR ITR-2025-00113; Reason: Reason; To: Leo Peterson', '2025-10-03 05:56:37');

-- --------------------------------------------------------

--
-- Table structure for table `asset_requests`
--

CREATE TABLE `asset_requests` (
  `request_id` int(11) NOT NULL,
  `asset_name` varchar(2555) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `office_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `asset_requests`
--

INSERT INTO `asset_requests` (`request_id`, `asset_name`, `user_id`, `status`, `request_date`, `quantity`, `unit`, `description`, `office_id`) VALUES
(1, '1', 2, 'pending', '2025-04-03 04:46:35', 10, 'pieces', 'Office chairs for new hires', 1),
(2, '2', 3, 'approved', '2025-04-03 04:46:35', 5, 'boxes', 'Laptop docking stations', 2),
(3, '3', 4, 'rejected', '2025-04-03 04:46:35', 3, 'units', 'Projector for conference room', 1),
(4, '4', 5, 'approved', '2025-04-03 04:46:35', 2, 'sets', 'Conference table sets for meeting room', 3),
(5, '5', 6, 'rejected', '2025-04-03 04:46:35', 15, 'pieces', 'Keyboard and mouse sets', 2),
(7, 'Mouse', 12, 'pending', '2025-04-20 06:08:40', 3, 'pcs', 'For my office', 4),
(8, 'Van', 12, 'pending', '2025-04-21 06:04:59', 1, 'unit', 'For our service vehicle.', 4);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `affected_table` varchar(50) DEFAULT NULL,
  `affected_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `module`, `details`, `affected_table`, `affected_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2025-0023 - INVENTORY (Destination: Supply Office)', 'ics_form', 27, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 12:44:10'),
(2, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2025-0023: Computer (Qty: 2, Unit Cost: ₱36,500.00, Total: ₱73,000.00)', 'ics_items', 13, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 12:44:10'),
(3, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 13:40:03'),
(4, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 13:40:11'),
(5, 17, 'Mark Jayson Namia', 'ACTIVATE', 'User Management', 'ACTIVATE user: josh (Full Name: Joshua Escano, Status changed to: active)', 'users', 15, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 13:40:29'),
(6, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 2 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 13:40:54'),
(7, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-22 14:32:08'),
(8, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 01:30:41'),
(9, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 02:25:39'),
(10, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 3 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 03:07:50'),
(11, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 2 items (Red Tags)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 03:31:34'),
(12, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0023, Entity: INVENTORY', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 03:34:15'),
(13, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 05:31:56'),
(14, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 09:09:47'),
(15, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 10:15:18'),
(16, 17, 'Mark Jayson Namia', 'CREATE', 'Red Tags', 'Created Red Tag: PS-5S-03-F01-01-05 for asset: Computer (Reason: Unnecessary, Action: For Disposal)', 'red_tags', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 10:17:04'),
(17, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 11:01:56'),
(18, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 14:39:55'),
(19, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 16:18:56'),
(20, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 16:19:06'),
(21, 1, 'Mark Jayson Namia', 'BACKUP_FAILED', 'System', 'Manual backup failed: mysqldump failed. Return code: 1', 'backups', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 17:02:38'),
(22, 1, 'Mark Jayson Namia', 'BACKUP_FAILED', 'System', 'Manual backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', 'backups', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 17:05:45'),
(23, 1, 'Mark Jayson Namia', 'BACKUP_FAILED', 'System', 'Manual backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', 'backups', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 17:08:21'),
(24, 1, 'Mark Jayson Namia', 'BACKUP_FAILED', 'System', 'Manual backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', 'backups', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 17:09:21'),
(25, 1, 'Mark Jayson Namia', 'BACKUP_FAILED', 'System', 'Manual backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', 'backups', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 17:15:08'),
(26, 1, 'Mark Jayson Namia', 'BACKUP_DOWNLOAD', 'System', 'Downloaded simple SQL backup: inventory_pilar_simple_backup_20250923_191520.sql', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 17:15:20'),
(27, 1, 'Mark Jayson Namia', 'BACKUP_FAILED', 'System', 'Manual cloud backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', 'backups', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 17:34:37'),
(28, 1, 'Mark Jayson Namia', 'BACKUP_FAILED', 'System', 'Manual cloud backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', 'backups', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 17:36:02'),
(29, 1, 'Mark Jayson Namia', 'BACKUP_FAILED', 'System', 'Manual cloud backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', 'backups', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 17:41:25'),
(30, 1, 'Mark Jayson Namia', 'BACKUP_SUCCESS', 'System', 'Local monthly backup created: inventory_pilar_auto_backup_20250923_194709.sql', 'backups', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-23 17:47:09'),
(31, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 00:09:29'),
(32, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 00:32:14'),
(33, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 00:32:19'),
(34, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 00:39:33'),
(35, NULL, 'ompdc', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'ompdc\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 00:39:41'),
(36, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 00:39:51'),
(37, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 00:41:15'),
(38, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 00:41:20'),
(39, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 00:52:21'),
(40, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0023, Entity: INVENTORY', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 01:29:10'),
(41, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: LGU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 01:34:03'),
(42, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: LGU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 01:35:10'),
(43, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: LGU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 01:39:51'),
(44, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: LGU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 01:58:28'),
(45, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: LGU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:04:00'),
(46, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: LGU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:17:44'),
(47, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: LGU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:22:24'),
(48, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: LGU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:27:23'),
(49, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: LGU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:32:18'),
(50, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: LGU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:34:23'),
(51, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2025-0023 - INVENTORY (Destination: Supply Office)', 'ics_form', 28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:47:53'),
(52, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2025-0023: Mouse (Qty: 5, Unit Cost: ₱450.00, Total: ₱2,250.00)', 'ics_items', 15, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:47:53'),
(53, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2024-7564 - INVENTORY (Destination: Supply Office)', 'ics_form', 29, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:57:07'),
(54, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2024-7564: mouse pad (Qty: 5, Unit Cost: ₱345.00, Total: ₱1,725.00)', 'ics_items', 16, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:57:07'),
(55, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:57:55'),
(56, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:58:00'),
(57, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:58:07'),
(58, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 02:58:14'),
(59, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 03:11:20'),
(60, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 03:11:29'),
(61, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 03:12:16'),
(62, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 03:12:22'),
(63, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 03:14:18'),
(64, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 03:14:28'),
(65, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 03:39:03'),
(66, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 03:39:16'),
(67, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 03:55:42'),
(68, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 03:55:57'),
(69, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:14:52'),
(70, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:14:56'),
(71, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:24:48'),
(72, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:24:54'),
(73, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:25:09'),
(74, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:25:12'),
(75, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:27:09'),
(76, NULL, 'ompdc', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'ompdc\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:27:16'),
(77, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:27:24'),
(78, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:28:12'),
(79, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:28:16'),
(80, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:40:43'),
(81, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:45:34'),
(82, NULL, 'ompdc', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'ompdc\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:45:42'),
(83, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:45:50'),
(84, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:47:05'),
(85, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 04:47:08'),
(86, 17, 'Mark Jayson Namia', 'CREATE', 'Red Tags', 'Created Red Tag: PS-5S-03-F01-01-01 for asset: Computer (Reason: Unnecessary, Action: For Disposal)', 'red_tags', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 05:25:32'),
(87, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 05:42:28'),
(88, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 05:42:35'),
(89, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 05:49:38'),
(90, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 05:49:44'),
(91, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 05:49:51'),
(92, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 07:14:54'),
(93, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2024-7564 - INVENTORY (Destination: Outside LGU)', 'ics_form', 30, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 07:15:41'),
(94, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2024-7564: Mouse (Qty: 2, Unit Cost: ₱564.00, Total: ₱1,128.00)', 'ics_items', 18, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 07:15:42'),
(95, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2024-7564 - INVENTORY (Destination: Supply Office)', 'ics_form', 31, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 07:16:25'),
(96, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2024-7564: Mouse (Qty: 2, Unit Cost: ₱453.00, Total: ₱906.00)', 'ics_items', 19, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 07:16:25'),
(97, 17, 'Mark Jayson Namia', 'DEACTIVATE', 'User Management', 'DEACTIVATE user: josh (Full Name: Joshua Escano, Status changed to: inactive)', 'users', 15, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 07:32:55'),
(98, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 08:32:50'),
(99, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 08:33:01'),
(100, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 09:05:20'),
(101, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 09:05:24'),
(102, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 10:01:30'),
(103, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 11:35:59'),
(104, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 11:56:19'),
(105, NULL, 'ompdc', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'ompdc\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 11:56:25'),
(106, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 11:56:33'),
(107, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 11:56:51'),
(108, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 11:56:55'),
(109, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 11:58:38'),
(110, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 12:14:01'),
(111, 17, 'Mark Jayson Namia', 'UPDATE', 'ITR Form', 'Updated ITR form:  - ', 'itr_form', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 13:31:32'),
(112, 17, 'Mark Jayson Namia', 'UPDATE', 'ITR Form', 'Updated ITR form:  - ', 'itr_form', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 13:32:42'),
(113, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR : Hilux Van (MR-2025-00033) (Property No: MR-2025-00033, Amount: ₱7,600,000.00)', 'itr_items', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 13:32:42'),
(114, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Transferred asset ID 33 to employee: Benjamin Thompson via ITR ', 'assets', 33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 13:32:42'),
(115, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 13:32:48'),
(116, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Form', 'Created new ITR form: ITR-2025-001 - ', 'itr_form', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 13:58:37'),
(117, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR ITR-2025-001: Hilux Van (MR-2025-00033) (Property No: MR-2025-00033, Amount: ₱7,600,000.00)', 'itr_items', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 13:58:37'),
(118, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Transferred asset ID 33 to employee: Aurora Henderson via ITR ITR-2025-001', 'assets', 33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 13:58:37'),
(119, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Form', 'Created new ITR form: ITR-2025-001 - ', 'itr_form', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:31:06'),
(120, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR ITR-2025-001: Hilux Van (MR-2025-00033) (Property No: MR-2025-00033, Amount: ₱7,600,000.00)', 'itr_items', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:31:06'),
(121, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Transferred asset ID 33 to employee: Aurora Henderson via ITR ITR-2025-001', 'assets', 33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:31:06'),
(122, 17, 'Mark Jayson Namia', 'UPDATE', 'MR Details', 'Updated end_user to \'Angela Rizal\' for asset ID 33 via ITR ITR-2025-001', 'mr_details', 33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:31:06'),
(123, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Form', 'Created new ITR form: ITR-2025-001 - ', 'itr_form', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:36:50'),
(124, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR ITR-2025-001: Hilux Van (MR-2025-00033) (Property No: MR-2025-00033, Amount: ₱7,600,000.00)', 'itr_items', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:36:50'),
(125, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Transferred asset ID 33 to employee: Aurora Henderson via ITR ITR-2025-001', 'assets', 33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:36:50'),
(126, 17, 'Mark Jayson Namia', 'UPDATE', 'MR Details', 'Updated person_accountable to \'Aurora Henderson\' and end_user to \'Roberto Cruz\' for asset ID 33 via ITR ITR-2025-001', 'mr_details', 33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:36:50'),
(127, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 14:57:51'),
(128, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Form', 'Created new ITR form: ITR-2025-001 - ', 'itr_form', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:05:00'),
(129, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR ITR-2025-001: Hilux Van (MR-2025-00033) (Property No: MR-2025-00033, Amount: ₱7,600,000.00)', 'itr_items', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:05:00'),
(130, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Transferred asset ID 33 to employee: Aurora Henderson via ITR ITR-2025-001', 'assets', 33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:05:00'),
(131, 17, 'Mark Jayson Namia', 'UPDATE', 'MR Details', 'Updated person_accountable to \'Aurora Henderson\' and end_user to \'Angela Rizal\' for asset ID 33 via ITR ITR-2025-001', 'mr_details', 33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 15:05:00'),
(132, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 22:33:39'),
(133, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 23:51:52'),
(134, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2024-7564 - INVENTORY (Destination: Supply Office)', 'ics_form', 32, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 23:54:48'),
(135, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2024-7564: Trash Can (Qty: 1, Unit Cost: ₱350.00, Total: ₱350.00)', 'ics_items', 20, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-24 23:54:48'),
(136, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 03:27:16'),
(137, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 03:27:21'),
(138, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 06:28:47'),
(139, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 07:05:58'),
(140, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 07:06:03'),
(141, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 07:06:08'),
(142, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 07:32:05'),
(143, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 07:33:35'),
(144, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-25 10:42:23'),
(145, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 10:11:10'),
(146, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 11:44:15'),
(147, 17, 'Mark Jayson Namia', 'BULK_IMPORT', 'Bulk Operations', 'Bulk IMPORT: 1 items (CSV/Excel Assets from file: sample inventory import.csv)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 14:31:43'),
(148, 17, 'Mark Jayson Namia', 'BULK_IMPORT', 'Bulk Operations', 'Bulk IMPORT: 1 items (CSV/Excel Assets from file: sample inventory import.csv)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 14:44:24'),
(149, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 14:47:29'),
(150, 17, 'Mark Jayson Namia', 'CREATE', 'Assets', 'CREATE asset: AIRCON \\r\\n (Qty: 2, Value: ₱18,437.00, Office: Supply Office)', 'assets', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 15:19:39'),
(151, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 23:25:15'),
(152, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-26 23:58:22'),
(153, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:06:50'),
(154, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:09:05'),
(155, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:10:49'),
(156, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:10:56'),
(157, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:13:25'),
(158, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:13:38'),
(159, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:13:42'),
(160, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:13:48'),
(161, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:13:51'),
(162, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:14:35'),
(163, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:33:35'),
(164, 1, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:35:29'),
(165, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 00:35:35'),
(166, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 02:15:35'),
(167, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 02:15:41'),
(168, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:23:39'),
(169, 17, 'Mark Jayson Namia', 'CREATE', 'User Management', 'CREATE user: waltielappy@gmail.com (Role: user, Office: 7K, Email: wjll2022-2920-98466@bicol-u.edu.ph, Status: active, Perms: fuel_inventory)', 'users', 26, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:39:35'),
(170, 17, 'Mark Jayson Namia', 'CREATE', 'User Management', 'CREATE user: matt (Role: user, Office: Supply Office, Email: matt@gmail.com, Status: active, Perms: fuel_inventory)', 'users', 28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:41:23'),
(171, 17, 'Mark Jayson Namia', 'DEACTIVATE', 'User Management', 'DEACTIVATE user: matt (Full Name: Matt Monro, Status changed to: inactive)', 'users', 28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:41:32'),
(172, 17, 'Mark Jayson Namia', 'ACTIVATE', 'User Management', 'ACTIVATE user: matt (Full Name: Matt Monro, Status changed to: active)', 'users', 28, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:41:36'),
(173, 17, 'Mark Jayson Namia', 'CREATE', 'User Management', 'CREATE user: jack (Role: user, Office: Supply Office, Email: jack@gmail.com, Status: active, Perms: fuel_inventory)', 'users', 29, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:59:33'),
(174, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 04:59:40'),
(175, 29, 'jack', 'LOGIN', 'Authentication', 'User \'jack\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:00:19'),
(176, 29, 'Jack Daniels', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:01:02'),
(177, 29, 'jack', 'LOGIN', 'Authentication', 'User \'jack\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:01:17'),
(178, 29, 'Jack Daniels', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:07:06'),
(179, 29, 'jack', 'LOGIN', 'Authentication', 'User \'jack\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:07:21'),
(180, 29, 'Jack Daniels', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:14:04'),
(181, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:14:32'),
(182, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:14:37'),
(183, 29, 'jack', 'LOGIN', 'Authentication', 'User \'jack\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:14:48'),
(184, 29, 'Jack Daniels', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:15:02');
INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `module`, `details`, `affected_table`, `affected_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(185, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:15:09'),
(186, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:28:28'),
(187, 29, 'jack', 'LOGIN', 'Authentication', 'User \'jack\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:28:37'),
(188, 29, 'Jack Daniels', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:51:42'),
(189, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:51:48'),
(190, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 06:09:11'),
(191, 29, 'jack', 'LOGIN', 'Authentication', 'User \'jack\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 06:10:06'),
(192, 29, 'Jack Daniels', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 06:12:25'),
(193, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 06:12:39'),
(194, 17, 'Mark Jayson Namia', 'CREATE', 'User Management', 'CREATE user: mike (Role: user, Office: Supply Office, Email: mike@gmail.com, Status: active, Perms: restrict_user_management)', 'users', 30, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 06:24:00'),
(195, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 06:24:03'),
(196, 30, 'mike', 'LOGIN', 'Authentication', 'User \'mike\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 06:24:20'),
(197, 30, 'Mike Tyson', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 06:26:04'),
(198, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 06:26:11'),
(199, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 08:30:19'),
(200, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 08:45:48'),
(201, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 09:29:25'),
(202, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 09:54:00'),
(203, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 10:50:23'),
(204, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 11:41:54'),
(205, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables', 'Error in Consumables: Failed to delete consumable: Ambi pur (ID: 85) - Unknown column \'image\' in \'field list\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 14:17:10'),
(206, 17, 'Mark Jayson Namia', 'DELETE_CONSUMABLE', 'Assets', 'DELETE_CONSUMABLE asset: Ambi pur (Qty: 3, Value: ₱300.00, Office: OMASS, Category: No Category)', 'assets', 85, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 14:21:21'),
(207, 17, 'Mark Jayson Namia', 'DELETE', 'Assets', 'DELETE asset: Desktop Computer (Core i5) (Qty: 1, Value: ₱546,740.00, Office: Supply Office, Category: No Category, Source: No Property Tag Tab)', 'assets', 64, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 14:54:50'),
(208, 17, 'Mark Jayson Namia', 'DELETE', 'Assets', 'DELETE asset: Desktop Computer (Core i5) (Qty: 1, Value: ₱546,740.00, Office: Supply Office, Category: No Category, Source: No Property Tag Tab)', 'assets', 65, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 14:55:47'),
(209, 17, 'Mark Jayson Namia', 'DELETE', 'Assets', 'DELETE asset: Desktop Computer (Core i5) (Qty: 1, Value: ₱546,740.00, Office: Supply Office, Category: No Category, Source: No Property Tag Tab)', 'assets', 87, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 14:56:11'),
(210, 17, 'Mark Jayson Namia', 'DELETE', 'Assets', 'DELETE asset: Desktop Computer (Core i5) (Qty: 1, Value: ₱546,740.00, Office: Supply Office, Category: No Category, Source: No Property Tag Tab)', 'assets', 88, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 14:56:32'),
(211, 17, 'Mark Jayson Namia', 'DELETE', 'Assets', 'DELETE asset: Desktop Computer (Core i5) (Qty: 1, Value: ₱546,740.00, Office: Supply Office, Category: No Category, Source: No Property Tag Tab)', 'assets', 89, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 15:01:48'),
(212, 17, 'Mark Jayson Namia', 'DELETE', 'Assets', 'DELETE asset: Mouse (Qty: 1, Value: ₱453.00, Office: Supply Office, Category: No Category, Source: No Property Tag Tab)', 'assets', 62, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 15:03:27'),
(213, 17, 'Mark Jayson Namia', 'DELETE', 'Assets', 'DELETE asset: Lenovo AMD Ryzen 7 (Qty: 1, Value: ₱75,000.00, Office: Supply Office, Category: No Category, Source: No Property Tag Tab)', 'assets', 35, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 15:04:51'),
(214, 17, 'Mark Jayson Namia', 'CREATE', 'Assets', 'CREATE asset: Office Table (Qty: 3, Value: ₱3,577.65, Office: Supply Office)', 'assets', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 15:14:51'),
(215, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 00:24:06'),
(216, 17, 'Mark Jayson Namia', 'CREATE', 'Assets', 'CREATE asset: Inventory Box (Qty: 2, Value: ₱3,569.00, Office: Supply Office)', 'assets', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 00:41:40'),
(217, 17, 'Mark Jayson Namia', 'CREATE', 'Assets', 'CREATE asset: Office Table (Qty: 2, Value: ₱2,355.00, Office: Supply Office)', 'assets', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 00:53:22'),
(218, 17, 'Mark Jayson Namia', 'CREATE', 'Assets', 'CREATE asset: Office Chair (Qty: 1, Value: ₱964.00, Office: Supply Office)', 'assets', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 00:55:28'),
(219, 17, 'Mark Jayson Namia', 'CREATE', 'Assets', 'CREATE asset: Aircon Split Type (Qty: 1, Value: ₱18,305.00, Office: Supply Office)', 'assets', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 01:00:28'),
(220, 17, 'Mark Jayson Namia', 'CREATE', 'Assets', 'CREATE asset: Power Generator (Qty: 1, Value: ₱23,984.00, Office: Supply Office)', 'assets', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 01:13:44'),
(221, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 4 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 01:15:43'),
(222, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 05:54:53'),
(223, 17, 'Mark Jayson Namia', 'DELETE', 'Assets', 'DELETE asset: Desktop Computer (Core i5) (Qty: 1, Value: ₱546,740.00, Office: Supply Office, Category: No Category, Source: No Property Tag Tab)', 'assets', 90, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:32:58'),
(224, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 104 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:44:53'),
(225, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 104 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:44:54'),
(226, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 104 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:44:54'),
(227, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 104 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:44:54'),
(228, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:44:57'),
(229, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:44:58'),
(230, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:06'),
(231, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:07'),
(232, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:07'),
(233, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:07'),
(234, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:08'),
(235, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:08'),
(236, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:08'),
(237, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:08'),
(238, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:35'),
(239, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:36'),
(240, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:36'),
(241, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:37'),
(242, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:45:37'),
(243, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:48:07'),
(244, 17, 'Mark Jayson Namia', 'ERROR', 'Consumables Enhanced Delete', 'Error in Consumables Enhanced Delete: Enhanced consumable deletion failed for ID: 105 - Unknown column \'asset_id\' in \'where clause\'', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:48:15'),
(245, 17, 'Mark Jayson Namia', 'DELETE_CONSUMABLE_ENHANCED', 'Assets', 'DELETE_CONSUMABLE_ENHANCED asset: Air freshener (Ambi pur) (Consumable Deletion - Qty: 2, Unit Value: ₱300.00, Total Value: ₱600.00, Office: Supply Office, Category: Uncategorized, Status: Available, Source: Enhanced Delete System)', 'assets', 105, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 06:49:43'),
(246, 17, 'Mark Jayson Namia', 'LOGOUT', 'Authentication', 'User logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 08:06:49'),
(247, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 09:33:00'),
(248, 17, 'nami', 'LOGIN_WITH_REMEMBER', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:44:25'),
(249, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:44:29'),
(250, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:44:50'),
(251, 17, 'nami', 'LOGIN_WITH_REMEMBER', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:47:44'),
(252, 17, 'nami', 'AUTO_LOGIN', 'Authentication', 'User \'nami\' auto-logged in via remember token (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:51:01'),
(253, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:51:11'),
(254, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:51:19'),
(255, 17, 'nami', 'LOGIN_WITH_REMEMBER', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:51:39'),
(256, 17, 'nami', 'AUTO_LOGIN', 'Authentication', 'User \'nami\' auto-logged in via remember token (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:51:45'),
(257, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:56:02'),
(258, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 12:56:38'),
(259, 17, 'Mark Jayson Namia', 'CREATE', 'User Management', 'CREATE user: walton (Role: user, Office: 7K, Email: waltonloneza@gmail.com, Status: active, Perms: none, Email sent)', 'users', 31, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:04:08'),
(260, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:15:45'),
(261, 31, 'walton', 'PASSWORD_RESET_REQUESTED', 'Authentication', 'Password reset link sent to user: walton', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:16:03'),
(262, 31, 'walton', 'PASSWORD_RESET_REQUESTED', 'Authentication', 'Password reset link sent to user: walton', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:16:42'),
(263, 10, 'walt', 'PASSWORD_RESET_REQUESTED', 'Authentication', 'Password reset link sent to user: walt', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:18:40'),
(264, 31, 'walton', 'PASSWORD_RESET_REQUESTED', 'Authentication', 'Password reset link sent to user: walton', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:31:34'),
(265, 31, 'walton', 'PASSWORD_RESET_COMPLETED', 'Authentication', 'Password successfully reset for user: walton', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:32:15'),
(266, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:48:41'),
(267, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:53:56'),
(268, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:54:09'),
(269, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:57:16'),
(270, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 14:31:46'),
(271, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 14:32:24'),
(272, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 14:48:18'),
(273, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 14:48:35'),
(274, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 15:13:16'),
(275, 1, 'ompdc', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'ompdc\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 15:14:55'),
(276, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 15:15:04'),
(277, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 15:19:00'),
(278, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 15:39:28'),
(279, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 01:12:22'),
(280, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 01:31:53'),
(281, NULL, 'ompdc', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'ompdc\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 01:33:11'),
(282, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 01:33:26'),
(283, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 02:00:22'),
(284, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 02:11:23'),
(285, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 03:04:17'),
(286, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 03:04:51'),
(287, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 03:06:01'),
(288, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 03:06:51'),
(289, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 03:07:30'),
(290, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 03:10:13'),
(291, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 03:12:28'),
(292, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 03:12:51'),
(293, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:13:21'),
(294, 17, 'Mark Jayson Namia', 'CREATE', 'User Management', 'CREATE user: michael (Role: office_admin, Office: Supply Office, Email: notlawsfins@gmail.com, Status: active, Perms: none, Email sent)', 'users', 32, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:16:02'),
(295, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:16:24'),
(296, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:16:42'),
(297, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:44:52'),
(298, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:45:16'),
(299, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:57:04'),
(300, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:11:32'),
(301, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:11:40'),
(302, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:11:49'),
(303, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:22:00'),
(304, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:22:10'),
(305, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:22:32'),
(306, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:22:57'),
(307, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:24:25'),
(308, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:24:37'),
(309, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:50:56'),
(310, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 10:52:24'),
(311, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form:  - LGU PILAR (Destination: Supply Office)', 'ics_form', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 12:15:52'),
(312, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS : Wheel Chair (Qty: 30, Unit Cost: ₱6,650.00, Total: ₱199,500.00)', 'ics_items', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 12:15:52'),
(313, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Updated ICS form: ICS-2025-0001 - LGU PILAR', 'ics_form', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 12:18:13'),
(314, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 12:18:16'),
(315, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 12:54:18'),
(316, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 12:54:28'),
(317, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 13:06:49'),
(318, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 13:07:04'),
(319, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: PAR-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 13:32:11'),
(320, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 13:36:25'),
(321, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 13:36:43'),
(322, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 13:37:16'),
(323, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 13:37:24'),
(324, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: PAR-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 13:41:11'),
(325, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: PAR-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 13:44:08'),
(326, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:11:34'),
(327, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:12:06'),
(328, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:12:55'),
(329, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:13:59'),
(330, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:16:54'),
(331, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:17:06'),
(332, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:21:33'),
(333, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:21:39'),
(334, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:22:11'),
(335, NULL, 'jack', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'jack\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:22:20'),
(336, 29, 'jack', 'LOGIN', 'Authentication', 'User \'jack\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:22:28'),
(337, 29, 'jack', 'LOGOUT', 'Authentication', 'User \'jack\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:22:46'),
(338, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:30:00'),
(339, 17, 'Mark Jayson Namia', 'CREATE', 'User Management', 'CREATE user: joshua (Role: user, Office: 7K, Email: joshuamarifrancis@gmail.com, Status: active, Perms: none, Email sent)', 'users', 33, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:32:44'),
(340, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:33:29'),
(341, 33, 'joshua', 'LOGIN', 'Authentication', 'User \'joshua\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:33:45'),
(342, 33, 'joshua', 'LOGOUT', 'Authentication', 'User \'joshua\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:35:13'),
(343, 33, 'joshua', 'LOGIN', 'Authentication', 'User \'joshua\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:35:23'),
(344, 33, 'joshua', 'LOGOUT', 'Authentication', 'User \'joshua\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:36:59'),
(345, NULL, 'joshua', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'joshua\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:38:36'),
(346, NULL, 'joshua', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'joshua\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:38:47'),
(347, 33, 'joshua', 'LOGIN', 'Authentication', 'User \'joshua\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:38:58'),
(348, 33, 'joshua', 'LOGOUT', 'Authentication', 'User \'joshua\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:45:04'),
(349, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:45:13'),
(350, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 14:47:58'),
(352, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 15:04:04'),
(353, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 15:05:23'),
(355, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 15:14:37'),
(357, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 02:19:50'),
(358, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2025-0001 - LGU PILAR (Destination: Supply Office)', 'ics_form', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 02:46:36'),
(359, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2025-0001: Laptop AMD Ryzen (Qty: 1, Unit Cost: ₱45,000.00, Total: ₱45,000.00)', 'ics_items', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 02:46:36'),
(360, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Updated ICS form: ICS-2025-0001 - LGU PILAR', 'ics_form', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 02:47:19'),
(361, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 3 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 02:49:47'),
(362, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated IIRUP PDF report with filters: IIRUP ID: 2, Office: Supply Office', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 02:59:04'),
(363, 17, 'Mark Jayson Namia', 'CREATE', 'Red Tags', 'Created Red Tag: PS-5S-03-F01-01-01 for asset: Laptop AMD Ryzen (Reason: Broken, Action: For Disposal)', 'red_tags', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:04:15'),
(364, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Form', 'Created new ITR form: ITR-2025-001 - LGU PILAR', 'itr_form', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:41:24');
INSERT INTO `audit_logs` (`id`, `user_id`, `username`, `action`, `module`, `details`, `affected_table`, `affected_id`, `ip_address`, `user_agent`, `created_at`) VALUES
(365, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR ITR-2025-001: Wheel Chair (PN-2019-05-02-0001-01) (Property No: PN-2019-05-02-0001-01, Amount: ₱6,650.00)', 'itr_items', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:41:24'),
(366, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Transferred asset ID 8 to employee: Grace Mitchell via ITR ITR-2025-001', 'assets', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:41:24'),
(367, 17, 'Mark Jayson Namia', 'UPDATE', 'MR Details', 'Updated person_accountable to \'Grace Mitchell\' and end_user to \'Roberto Cruz\' for asset ID 8 via ITR ITR-2025-001', 'mr_details', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:41:24'),
(368, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Form', 'Created new ITR form: ITR-2025-001 - LGU PILAR', 'itr_form', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:48:27'),
(369, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR ITR-2025-001: Laptop AMD Ryzen (PN-2019-05-02-0001-01) (Property No: PN-2019-05-02-0001-01, Amount: ₱45,000.00)', 'itr_items', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:48:27'),
(370, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Transferred asset ID 8 to employee: Grace Mitchell via ITR ITR-2025-001', 'assets', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:48:27'),
(371, 17, 'Mark Jayson Namia', 'UPDATE', 'MR Details', 'Updated person_accountable to \'Grace Mitchell\' and end_user to \'Angela Rizal\' for asset ID 8 via ITR ITR-2025-001', 'mr_details', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:48:27'),
(372, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 04:01:33'),
(373, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 04:01:41'),
(374, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 04:11:42'),
(375, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 04:11:49'),
(376, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 04:57:14'),
(377, 17, 'Mark Jayson Namia', 'CREATE', 'Assets', 'CREATE asset: Laptop Acer (Qty: 1, Value: ₱50,000.00, Office: Supply Office)', 'assets', 0, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 05:06:19'),
(378, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 05:47:40'),
(379, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 14:18:54'),
(380, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 14:42:36'),
(381, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 14:42:49'),
(382, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 14:44:53'),
(383, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 14:45:03'),
(384, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 14:58:58'),
(385, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 14:59:28'),
(386, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 14:59:36'),
(387, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 15:04:43'),
(388, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 15:08:01'),
(389, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2025-0001 - LGU PILAR (Destination: Supply Office)', 'ics_form', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 15:34:51'),
(390, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2025-0001: Grass cutter (Qty: 2, Unit Cost: ₱18,000.00, Total: ₱36,000.00)', 'ics_items', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 15:34:51'),
(391, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2025-0001: Power Drill (corded) (Qty: 1, Unit Cost: ₱3,500.00, Total: ₱3,500.00)', 'ics_items', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 15:34:51'),
(392, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2025-0001 - LGU PILAR (Destination: MDRRMO)', 'ics_form', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 15:45:59'),
(393, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:31:12'),
(394, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:31:21'),
(395, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:31:33'),
(396, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:35:03'),
(397, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:35:11'),
(398, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:36:39'),
(399, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:36:45'),
(400, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:46:47'),
(401, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:47:37'),
(402, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Updated ICS form: ICS-2025-0001 - LGU PILAR', 'ics_form', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:47:49'),
(403, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:47:51'),
(404, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 01:51:55'),
(405, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:15:18'),
(406, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:17:12'),
(407, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:17:17'),
(408, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:19:11'),
(409, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:19:38'),
(410, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:21:23'),
(411, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:21:34'),
(412, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:23:03'),
(413, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:26:38'),
(414, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:29:18'),
(415, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:32:10'),
(416, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:37:49'),
(417, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:38:42'),
(418, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:43:06'),
(419, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:43:23'),
(420, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF (Alt) report with filters: ICS: ICS-2025-0001 (Alt Format)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:50:53'),
(421, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF (Alt) report with filters: ICS: ICS-2025-0001 (Alt Format)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:51:55'),
(422, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 02:53:01'),
(423, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 03:04:12'),
(424, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 03:08:00'),
(425, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 03:08:25'),
(426, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 03:10:16'),
(427, NULL, 'System', 'GENERATE', 'Reports', 'Generated ICS PDF report with filters: ICS: ICS-2025-0001, Entity: LGU PILAR', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 03:35:02'),
(428, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 03:45:29'),
(429, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2025-0002 - LGU PILAR (Destination: MTC)', 'ics_form', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 04:00:32'),
(430, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2025-0002: Printer Epson (Qty: 1, Unit Cost: ₱4,500.00, Total: ₱4,500.00)', 'ics_items', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 04:00:32'),
(431, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 04:15:51'),
(432, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 04:16:02'),
(433, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 04:17:09'),
(434, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 04:17:17'),
(435, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 05:28:10'),
(436, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 10:44:49'),
(437, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 14:18:00'),
(438, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: LGU-PAR-2025-0001, Entity: RHU', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-01 15:57:28'),
(439, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 01:05:05'),
(440, 17, 'Mark Jayson Namia', 'DELETE', 'Assets', 'DELETE asset: HP Pavilion (Qty: 1, Value: ₱53,000.00, Office: RHU Office, Category: No Category, Source: No Property Tag Tab)', 'assets', 49, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 01:08:08'),
(441, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 01:17:47'),
(442, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 01:20:33'),
(443, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated IIRUP PDF report with filters: IIRUP ID: 2, Office: Supply Office', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 02:15:51'),
(444, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated IIRUP PDF report with filters: IIRUP ID: 2, Office: Supply Office', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 02:57:06'),
(445, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated IIRUP PDF report with filters: IIRUP ID: 2, Office: Supply Office', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 03:01:33'),
(446, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated IIRUP PDF report with filters: IIRUP ID: 2, Office: Supply Office', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 03:11:48'),
(447, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated IIRUP PDF report with filters: IIRUP ID: 2, Office: Supply Office', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 03:12:29'),
(448, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 04:31:32'),
(449, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Form', 'Created new ITR form: ITR-2025-001 - LGU PILAR', 'itr_form', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:08:32'),
(450, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR ITR-2025-001: Power Drill (corded) (PN-2019-05-02-0001-01) (Property No: PN-2019-05-02-0001-01, Amount: ₱3,500.00)', 'itr_items', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:08:32'),
(451, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Updated asset ID 8 via ITR ITR-2025-001: employee_id to 73 (Scarlett Jenkins) end_user to \'Jack Robertson\'', 'assets', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:08:32'),
(452, 17, 'Mark Jayson Namia', 'UPDATE', 'MR Details', 'Updated person_accountable to \'Scarlett Jenkins\' and end_user to \'Jack Robertson\' for asset ID 8 via ITR ITR-2025-001', 'mr_details', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:08:32'),
(453, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Form', 'Created new ITR form: ITR-2025-002 - LGU PILAR', 'itr_form', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:15:08'),
(454, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR ITR-2025-002: Power Drill (corded) (PN-2019-05-02-0001-01) (Property No: PN-2019-05-02-0001-01, Amount: ₱3,500.00)', 'itr_items', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:15:08'),
(455, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Updated asset ID 8 via ITR ITR-2025-002: employee_id to 41 (Hannah Phillips) end_user to \'Roberto Cruz\'', 'assets', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:15:08'),
(456, 17, 'Mark Jayson Namia', 'UPDATE', 'MR Details', 'Updated person_accountable to \'Hannah Phillips\' and end_user to \'Roberto Cruz\' for asset ID 8 via ITR ITR-2025-002', 'mr_details', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:15:08'),
(457, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Form', 'Created new ITR form: ITR-2025-001 - LGU PILAR', 'itr_form', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:27:01'),
(458, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR ITR-2025-001: Notebook i7 (PN-2019-05-02-0001-01) (Property No: PN-2019-05-02-0001-01, Amount: ₱80,245.00)', 'itr_items', 5, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:27:01'),
(459, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Updated asset ID 8 via ITR ITR-2025-001: employee_id to 9 (John Smith) end_user to \'Jake Paul\'', 'assets', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:27:01'),
(460, 17, 'Mark Jayson Namia', 'UPDATE', 'MR Details', 'Updated person_accountable to \'John Smith\' and end_user to \'Jake Paul\' for asset ID 8 via ITR ITR-2025-001', 'mr_details', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:27:01'),
(461, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Form', 'Created new ITR form: ITR-2025-002 - INVENTORY', 'itr_form', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:35:44'),
(462, 17, 'Mark Jayson Namia', 'CREATE', 'ITR Items', 'Added item to ITR ITR-2025-002: Power Drill (corded) (PN-2019-05-02-0001-01) (Property No: PN-2019-05-02-0001-01, Amount: ₱3,500.00)', 'itr_items', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:35:44'),
(463, 17, 'Mark Jayson Namia', 'UPDATE', 'Assets', 'Transferred asset ID 8 to employee: Leo Peterson via ITR ITR-2025-002', 'assets', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:35:44'),
(464, 17, 'Mark Jayson Namia', 'UPDATE', 'MR Details', 'Updated person_accountable to \'Leo Peterson\' and end_user to \'Angela Rizal\' for asset ID 8 via ITR ITR-2025-002', 'mr_details', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 05:35:44'),
(465, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 07:14:26'),
(466, 17, 'nami', 'LOGIN_WITH_REMEMBER', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 07:31:55'),
(467, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 07:32:36'),
(468, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 07:39:58'),
(469, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2025-0006 - OMM (Destination: OMM)', 'ics_form', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 09:55:12'),
(470, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2025-0006: Conference Table (Qty: 1, Unit Cost: ₱42,000.00, Total: ₱42,000.00)', 'ics_items', 6, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 09:55:13'),
(471, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2025-0009 - OMM (Destination: OMM)', 'ics_form', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 10:00:30'),
(472, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2025-0009: conference table (Qty: 1, Unit Cost: ₱10,000.00, Total: ₱10,000.00)', 'ics_items', 7, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 10:00:30'),
(473, 17, 'nami', 'AUTO_LOGIN', 'Authentication', 'User \'nami\' auto-logged in via remember token (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 10:38:55'),
(474, 17, 'Mark Jayson Namia', 'CREATE', 'Red Tags', 'Created Red Tag: PS-5S-03-F01-01-01 for asset: conference table (Reason: Broken, Action: For Disposal)', 'red_tags', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 11:23:27'),
(475, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 12:16:48'),
(476, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 12:17:04'),
(477, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 12:17:59'),
(478, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 12:18:06'),
(479, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2025-00041 - GAD (Destination: GAD)', 'ics_form', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 12:21:33'),
(480, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2025-00041: computer desktop (Qty: 1, Unit Cost: ₱52,000.00, Total: ₱52,000.00)', 'ics_items', 8, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 12:21:33'),
(481, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: ICS-2025-00022 - KALAHI (Destination: KALAHI)', 'ics_form', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 12:25:57'),
(482, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS ICS-2025-00022: Lappy (Qty: 1, Unit Cost: ₱54,000.00, Total: ₱54,000.00)', 'ics_items', 9, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-02 12:25:57'),
(483, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 00:59:20'),
(484, 17, 'Mark Jayson Namia', 'DEACTIVATE', 'User Management', 'DEACTIVATE user: geely (Full Name: Geely Mitsubishi, Status changed to: inactive)', 'users', 19, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 02:12:38'),
(485, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 04:46:01'),
(486, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:07:04'),
(487, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:07:14'),
(488, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:08:33'),
(490, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:09:07'),
(491, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:09:14'),
(492, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:09:20'),
(493, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:19:21'),
(494, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:19:27'),
(495, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:21:16'),
(496, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:21:24'),
(497, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:21:29'),
(498, NULL, 'ompdc', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'ompdc\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:21:36'),
(499, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:21:46'),
(500, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:21:54'),
(501, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:22:30'),
(502, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 05:22:36'),
(503, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 06:02:46'),
(504, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 06:02:53'),
(505, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 06:03:13'),
(506, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 06:03:20'),
(507, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-10-03 06:03:26');

-- --------------------------------------------------------

--
-- Table structure for table `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `path` text NOT NULL,
  `size_bytes` bigint(20) DEFAULT NULL,
  `storage` enum('local','cloud','both') DEFAULT 'local',
  `status` enum('success','failed') DEFAULT 'success',
  `triggered_by` enum('manual','scheduled') DEFAULT 'manual',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `backups`
--

INSERT INTO `backups` (`id`, `filename`, `path`, `size_bytes`, `storage`, `status`, `triggered_by`, `error_message`, `created_at`) VALUES
(1, 'inventory_pilar_backup_20250923_184748.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_184748.sql', 0, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1', '2025-09-23 16:47:48'),
(2, 'inventory_pilar_backup_20250923_184752.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_184752.sql', 0, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1', '2025-09-23 16:47:52'),
(3, 'inventory_pilar_backup_20250923_185747.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_185747.sql', 0, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1', '2025-09-23 16:57:47'),
(4, 'inventory_pilar_backup_20250923_190238.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_190238.sql', 0, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1', '2025-09-23 17:02:38'),
(5, 'inventory_pilar_backup_20250923_190545.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_190545.sql', NULL, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', '2025-09-23 17:05:45'),
(6, 'inventory_pilar_backup_20250923_190821.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_190821.sql', NULL, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', '2025-09-23 17:08:21'),
(7, 'inventory_pilar_backup_20250923_190921.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_190921.sql', NULL, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', '2025-09-23 17:09:21'),
(8, 'inventory_pilar_backup_20250923_191508.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_191508.sql', NULL, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', '2025-09-23 17:15:08'),
(9, 'inventory_pilar_backup_20250923_193437.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_193437.sql', NULL, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', '2025-09-23 17:34:37'),
(10, 'inventory_pilar_backup_20250923_193602.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_193602.sql', NULL, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', '2025-09-23 17:36:02'),
(11, 'inventory_pilar_backup_20250923_194125.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_194125.sql', NULL, 'local', 'failed', 'manual', 'mysqldump failed. Return code: 1; Output: The system cannot find the path specified.', '2025-09-23 17:41:25'),
(12, 'inventory_pilar_auto_backup_20250923_194709.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\backups\\inventory_pilar_auto_backup_20250923_194709.sql', 254421, 'local', 'success', 'manual', NULL, '2025-09-23 17:47:09');

-- --------------------------------------------------------

--
-- Table structure for table `batches`
--

CREATE TABLE `batches` (
  `id` int(11) NOT NULL,
  `batch_number` varchar(50) NOT NULL,
  `batch_name` varchar(255) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `supplier` varchar(255) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `manufacture_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `production_date` date DEFAULT NULL,
  `lot_number` varchar(100) DEFAULT NULL,
  `batch_size` int(11) NOT NULL DEFAULT 1,
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `total_value` decimal(12,2) DEFAULT NULL,
  `storage_location` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `quality_status` enum('pending','approved','rejected','quarantined') DEFAULT 'pending',
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batch_items`
--

CREATE TABLE `batch_items` (
  `id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `item_number` varchar(50) NOT NULL,
  `serial_number` varchar(255) DEFAULT NULL,
  `qr_code` varchar(255) DEFAULT NULL,
  `inventory_tag` varchar(255) DEFAULT NULL,
  `status` enum('available','borrowed','in_use','damaged','disposed','expired','quarantined') DEFAULT 'available',
  `current_location` varchar(255) DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `condition_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batch_receipts`
--

CREATE TABLE `batch_receipts` (
  `id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `receipt_number` varchar(100) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `received_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `received_by` int(11) DEFAULT NULL,
  `quantity_received` int(11) NOT NULL,
  `quantity_accepted` int(11) NOT NULL,
  `quantity_rejected` int(11) DEFAULT 0,
  `rejection_reason` text DEFAULT NULL,
  `quality_check_status` enum('pending','passed','failed') DEFAULT 'pending',
  `quality_check_date` date DEFAULT NULL,
  `quality_check_by` int(11) DEFAULT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `batch_transactions`
--

CREATE TABLE `batch_transactions` (
  `id` int(11) NOT NULL,
  `batch_id` int(11) NOT NULL,
  `batch_item_id` int(11) DEFAULT NULL,
  `transaction_type` enum('received','issued','borrowed','returned','transferred','disposed','expired','damaged','quarantined','released') NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `from_location` varchar(255) DEFAULT NULL,
  `to_location` varchar(255) DEFAULT NULL,
  `from_office_id` int(11) DEFAULT NULL,
  `to_office_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `recipient_user_id` int(11) DEFAULT NULL,
  `reference_id` varchar(100) DEFAULT NULL,
  `reference_type` enum('borrow_request','transfer','disposal','adjustment') DEFAULT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expiry_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow_requests`
--

CREATE TABLE `borrow_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','borrowed','returned','pending_approval') NOT NULL DEFAULT 'pending',
  `requested_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `due_date` datetime DEFAULT NULL,
  `return_remarks` text DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `batch_id` int(11) DEFAULT NULL,
  `batch_item_id` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `is_inter_department` tinyint(1) NOT NULL DEFAULT 0,
  `source_office_id` int(11) DEFAULT NULL,
  `requested_by_user_id` int(11) DEFAULT NULL,
  `requested_for_office_id` int(11) DEFAULT NULL,
  `approved_by_office_head` tinyint(1) NOT NULL DEFAULT 0,
  `approved_by_source_office` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_requests`
--

INSERT INTO `borrow_requests` (`id`, `user_id`, `asset_id`, `office_id`, `status`, `requested_at`, `approved_at`, `due_date`, `return_remarks`, `returned_at`, `quantity`, `created_at`, `updated_at`, `batch_id`, `batch_item_id`, `expiry_date`, `is_inter_department`, `source_office_id`, `requested_by_user_id`, `requested_for_office_id`, `approved_by_office_head`, `approved_by_source_office`) VALUES
(2, 19, 13, 4, 'returned', '2025-07-12 15:40:35', '2025-07-14 21:13:26', NULL, 'NEVER BEEN USED', '2025-07-14 21:13:47', 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(3, 19, 14, 4, 'pending', '2025-07-12 15:40:35', NULL, NULL, NULL, NULL, 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(4, 19, 2, 9, 'returned', '2025-07-12 15:42:36', '2025-07-14 09:54:28', NULL, 'slightly used', '2025-07-14 19:55:48', 0, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(5, 17, 2, 9, 'pending', '2025-07-13 15:15:18', NULL, NULL, NULL, NULL, 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(6, 17, 2, 9, 'returned', '2025-07-13 15:24:25', '2025-07-13 20:45:54', NULL, 'All goods', '2025-07-13 20:58:56', 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(7, 17, 2, 9, 'returned', '2025-07-14 04:23:59', '2025-07-14 21:00:24', NULL, 'Good condition', '2025-07-14 21:02:14', 0, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(8, 17, 13, 4, 'returned', '2025-07-14 14:49:24', '2025-07-14 19:50:05', NULL, 'Neve used', '2025-07-14 21:05:50', 0, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(9, 17, 3, 2, 'pending', '2025-08-20 08:09:14', NULL, NULL, NULL, NULL, 5, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(10, 17, 64, 9, 'pending', '2025-08-20 08:17:57', NULL, NULL, NULL, NULL, 3, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(11, 12, 64, 9, 'pending', '2025-08-20 08:24:23', NULL, NULL, NULL, NULL, 3, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(12, 17, 64, 9, 'pending', '2025-08-29 15:24:45', NULL, NULL, NULL, NULL, 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(13, 17, 3, 4, 'pending', '2025-09-22 10:01:54', NULL, NULL, NULL, NULL, 1, '2025-09-22 08:01:54', '2025-09-22 08:01:54', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(14, 17, 5, 4, 'pending', '2025-09-22 10:01:54', NULL, NULL, NULL, NULL, 1, '2025-09-22 08:01:54', '2025-09-22 08:01:54', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0),
(15, 17, 15, 4, 'pending', '2025-09-22 10:01:54', NULL, NULL, NULL, NULL, 3, '2025-09-22 08:01:54', '2025-09-22 08:01:54', NULL, NULL, NULL, 0, NULL, NULL, NULL, 0, 0);

--
-- Triggers `borrow_requests`
--
DELIMITER $$
CREATE TRIGGER `tr_borrow_requests_update_validation` BEFORE UPDATE ON `borrow_requests` FOR EACH ROW BEGIN
    -- Validate quantity is positive
    IF NEW.quantity <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantity must be greater than 0';
    END IF;
    
    -- Validate status values
    IF NEW.status NOT IN ('pending', 'borrowed', 'returned', 'rejected') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid status value';
    END IF;
    
    -- Update the updated_at timestamp
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `tr_borrow_requests_validation` BEFORE INSERT ON `borrow_requests` FOR EACH ROW BEGIN
    -- Validate quantity is positive
    IF NEW.quantity <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantity must be greater than 0';
    END IF;
    
    -- Validate status values
    IF NEW.status NOT IN ('pending', 'borrowed', 'returned', 'rejected') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid status value';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `category_code` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `type` enum('asset','consumables') NOT NULL DEFAULT 'asset'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `category_code`, `status`, `type`) VALUES
(1, 'Office Equipments', 'OFFEQ', 1, 'asset'),
(2, 'Furnitures & Fixtures', 'FUR', 1, 'asset'),
(4, 'Vehicles', 'VEH', 1, 'asset'),
(5, 'Machinery & Equipment', 'MACH', 1, 'asset'),
(6, 'Information & Communication Technology', 'ICT', 1, 'asset');

-- --------------------------------------------------------

--
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `consumption_log`
--

CREATE TABLE `consumption_log` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `quantity_consumed` int(11) NOT NULL,
  `recipient_user_id` int(11) NOT NULL,
  `dispensed_by_user_id` int(11) NOT NULL,
  `consumption_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doc_no`
--

CREATE TABLE `doc_no` (
  `doc_id` int(11) NOT NULL,
  `document_number` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doc_no`
--

INSERT INTO `doc_no` (`doc_id`, `document_number`) VALUES
(1, 'GS21A-003'),
(2, 'GS21A-005'),
(3, 'GS22A-001'),
(4, 'GSP-2024-08-0001-1'),
(5, 'MO21A-012');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `employee_no` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('permanent','casual','contractual','job_order','probationary','resigned','retired') NOT NULL,
  `clearance_status` enum('cleared','uncleared') DEFAULT 'uncleared',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `employee_no`, `name`, `status`, `clearance_status`, `date_added`, `image`, `office_id`) VALUES
(1, 'EMP0001', 'Juan A. Dela Cruz', 'permanent', 'uncleared', '2025-08-31 14:25:29', 'emp_68b45b59bbe19.jpg', 2),
(2, 'EMP0002', 'Maria Santos', 'permanent', 'uncleared', '2025-09-01 01:39:29', 'emp_68b4f95154506.jpg', 7),
(3, 'EMP0003', 'Pedro Reyes', 'contractual', 'uncleared', '2025-09-01 01:50:43', 'emp_68b4fbf33d3ad.jpg', 2),
(8, 'EMP0004', 'Ryan Bang', 'permanent', 'uncleared', '2025-09-20 12:03:27', NULL, 7),
(9, 'EMP0005', 'John Smith', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(10, 'EMP0006', 'Emily Johnson', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(11, 'EMP0007', 'Jessica Davis', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(12, 'EMP0008', 'Daniel Wilson', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 15),
(13, 'EMP0009', 'Sophia Martinez', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(14, 'EMP0010', 'David Anderson', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(15, 'EMP0011', 'Olivia Thomas', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(16, 'EMP0012', 'James Taylor', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 21),
(17, 'EMP0013', 'Emma Moore', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(18, 'EMP0014', 'William Jackson', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(19, 'EMP0015', 'Ava White', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(20, 'EMP0016', 'Alexander Harris', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(21, 'EMP0017', 'Isabella Martin', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(22, 'EMP0018', 'Benjamin Thompson', 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(23, 'EMP0019', 'Mia Garcia', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(24, 'EMP0020', 'Ethan Martinez', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 21),
(25, 'EMP0021', 'Amelia Lewis', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(26, 'EMP0022', 'Harper Walker', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(27, 'EMP0023', 'Lucas Hall', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(28, 'EMP0024', 'Evelyn Allen', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(29, 'EMP0025', 'Mason Young', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(30, 'EMP0026', 'Abigail King', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(31, 'EMP0027', 'James Scott', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(32, 'EMP0028', 'Ella Green', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 15),
(33, 'EMP0029', 'Henry Adams', 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(34, 'EMP0030', 'Sebastian Gonzalez', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(35, 'EMP0031', 'Victoria Nelson', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(36, 'EMP0032', 'Jackson Carter', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(37, 'EMP0033', 'Grace Mitchell', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(38, 'EMP0034', 'Owen Perez', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(39, 'EMP0035', 'Lily Roberts', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(40, 'EMP0036', 'Jacob Turner', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(41, 'EMP0037', 'Hannah Phillips', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(42, 'EMP0038', 'Samuel Campbell', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 21),
(43, 'EMP0039', 'Zoe Parker', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(44, 'EMP0040', 'Mateo Evans', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(45, 'EMP0041', 'Aria Edwards', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 38),
(46, 'EMP0042', 'Levi Collins', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 38),
(47, 'EMP0043', 'Nora Stewart', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(48, 'EMP0044', 'Wyatt Sanchez', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(49, 'EMP0045', 'Camila Morris', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(50, 'EMP0046', 'Carter Rogers', 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(51, 'EMP0047', 'Penelope Reed', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(52, 'EMP0048', 'Julian Cook', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(53, 'EMP0049', 'Riley Morgan', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 15),
(54, 'EMP0050', 'Nathan Bell', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(55, 'EMP0051', 'Lillian Murphy', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(56, 'EMP0052', 'Aurora Rivera', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(57, 'EMP0053', 'Isaac Cooper', 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(58, 'EMP0054', 'Violet Richardson', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(59, 'EMP0055', 'Stella Howard', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(60, 'EMP0056', 'Brooklyn Torres', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(61, 'EMP0057', 'Leo Peterson', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(62, 'EMP0058', 'Hannah Gray', 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(63, 'EMP0059', 'Anthony Ramirez', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(64, 'EMP0060', 'Addison James', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(65, 'EMP0061', 'Madison Brooks', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(66, 'EMP0062', 'Joshua Kelly', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(67, 'EMP0063', 'Eli Price', 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(68, 'EMP0064', 'Paisley Bennett', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 38),
(69, 'EMP0065', 'Gabriel Wood', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(70, 'EMP0066', 'Caleb Ross', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(71, 'EMP0067', 'Aurora Henderson', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 38),
(72, 'EMP0068', 'Ryan Coleman', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(73, 'EMP0069', 'Scarlett Jenkins', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(74, 'EMP0070', 'Luke Perry', 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(75, 'EMP0071', 'Nora Powell', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 15),
(76, 'EMP0072', 'Hannah Patterson', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(77, 'EMP0073', 'Cameron Hughes', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(78, 'EMP0074', 'Violet Flores', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(79, 'EMP0075', 'Connor Washington', 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 21),
(80, 'EMP0076', 'Grace Butler', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 15),
(81, 'EMP0077', 'Wyatt Simmons', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(82, 'EMP0078', 'Lillian Foster', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(83, 'EMP0079', 'Brayden Gonzales', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(84, 'EMP0080', 'Elena Bryant', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(85, 'EMP0081', 'Zoe Russell', 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(86, 'EMP0082', 'Aaron Griffin', 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(87, 'EMP0083', 'Hazel Diaz', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(88, 'EMP0084', 'Charles Hayes', 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(89, 'EMP0085', 'Aurora Myers', 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(90, 'EMP0086', 'Thomas Ford', 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 3);

-- --------------------------------------------------------

--
-- Table structure for table `forms`
--

CREATE TABLE `forms` (
  `id` int(11) NOT NULL,
  `form_title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forms`
--

INSERT INTO `forms` (`id`, `form_title`, `category`, `file_path`, `created_at`) VALUES
(3, 'Property Acknowledgement Receipt (PAR)', 'PAR', 'par_form.php', '2025-08-05 02:17:00'),
(4, 'Inventory Custodian Slip (ICS)', 'ICS', 'ics_form.php', '2025-08-05 02:17:00'),
(6, 'Requisition & Issue Slip (RIS)', 'RIS', 'ris_form.php', '2025-08-05 02:17:00'),
(7, 'Inventory & Inspection Report of Unserviceable Property (IIRUP)', 'IIRUP', 'iirup_form.php', '2025-08-12 12:53:40'),
(9, 'Inventory Transfer Receipt (ITR)', 'ITR', 'itr_form.php\r\n', '2025-09-24 08:32:02');

-- --------------------------------------------------------

--
-- Table structure for table `form_thresholds`
--

CREATE TABLE `form_thresholds` (
  `id` int(11) NOT NULL,
  `ics_max` decimal(15,2) NOT NULL DEFAULT 50000.00,
  `par_min` decimal(15,2) NOT NULL DEFAULT 50000.00,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `form_thresholds`
--

INSERT INTO `form_thresholds` (`id`, `ics_max`, `par_min`, `updated_at`) VALUES
(1, 50000.00, 50000.00, '2025-10-03 06:03:10');

-- --------------------------------------------------------

--
-- Table structure for table `fuel_out`
--

CREATE TABLE `fuel_out` (
  `id` int(11) NOT NULL,
  `fo_date` date NOT NULL,
  `fo_time_in` time NOT NULL,
  `fo_fuel_no` varchar(100) DEFAULT NULL,
  `fo_plate_no` varchar(100) DEFAULT NULL,
  `fo_request` varchar(255) DEFAULT NULL,
  `fo_liters` decimal(12,2) NOT NULL DEFAULT 0.00,
  `fo_fuel_type` varchar(100) DEFAULT NULL,
  `fo_vehicle_type` varchar(100) DEFAULT NULL,
  `fo_receiver` varchar(255) NOT NULL,
  `fo_time_out` time DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel_out`
--

INSERT INTO `fuel_out` (`id`, `fo_date`, `fo_time_in`, `fo_fuel_no`, `fo_plate_no`, `fo_request`, `fo_liters`, `fo_fuel_type`, `fo_vehicle_type`, `fo_receiver`, `fo_time_out`, `created_by`, `created_at`) VALUES
(5, '2025-09-27', '15:42:00', 'SEP27-364', 'YAWA B75', 'MOTORPOOL', 3.00, 'Diesel', 'BACKHOE', 'J.LUMIBAO', '15:42:00', 17, '2025-09-27 10:42:43'),
(6, '2025-02-19', '07:30:00', 'FEB25-363', '1135', 'MOTORPOOL', 30.00, 'Unleaded', 'SINOTRUCK', 'A.LOBRIGO', NULL, 17, '2025-09-28 02:33:50');

-- --------------------------------------------------------

--
-- Table structure for table `fuel_records`
--

CREATE TABLE `fuel_records` (
  `id` int(11) NOT NULL,
  `date_time` datetime NOT NULL,
  `fuel_type` varchar(50) NOT NULL,
  `quantity` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `storage_location` varchar(255) NOT NULL,
  `delivery_receipt` varchar(100) DEFAULT NULL,
  `supplier_name` varchar(255) NOT NULL,
  `received_by` varchar(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel_records`
--

INSERT INTO `fuel_records` (`id`, `date_time`, `fuel_type`, `quantity`, `unit_price`, `total_cost`, `storage_location`, `delivery_receipt`, `supplier_name`, `received_by`, `remarks`, `created_by`, `created_at`) VALUES
(2, '2025-09-27 11:26:00', 'Diesel', 2.00, 55.00, 110.00, 'Storage Room', '34567568977578800', 'Elton John Moises', 'Roy Ricacho', 'For vehicles', 17, '2025-09-27 03:26:39'),
(3, '2025-09-27 13:38:00', 'Diesel', 2.00, 55.00, 110.00, 'Storage Room', '34526567709800', 'Elton John Moises', 'Roy Ricacho', 'For Ambulance', 17, '2025-09-27 08:39:02'),
(4, '2025-09-28 07:21:00', 'Kerosene', 30.00, 56.00, 1680.00, 'Storage Room', '34910046678231', 'James Smith', 'Roy Ricacho', 'Reserve Stock', 17, '2025-09-28 02:23:22'),
(5, '2025-09-28 07:24:00', 'Premium', 60.00, 54.00, 3240.00, 'Storage Room', '236588177450900', 'Mark Levi', 'Roy Ricacho', 'Reserve Stock', 17, '2025-09-28 02:25:43'),
(6, '2025-09-28 07:26:00', 'Unleaded', 100.00, 52.00, 5200.00, 'Storage Room', '23454676878445', 'Elton John Moises', 'Roy Ricacho', 'Reserve Stock', 17, '2025-09-28 02:27:24'),
(7, '2025-09-28 07:28:00', 'Diesel', 50.00, 52.00, 2600.00, 'Storage Room', '3233511774566', 'Jake Paul', 'Roy Ricacho', 'Restock', 17, '2025-09-28 02:29:40');

-- --------------------------------------------------------

--
-- Table structure for table `fuel_stock`
--

CREATE TABLE `fuel_stock` (
  `fuel_type_id` int(11) NOT NULL,
  `quantity` decimal(14,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel_stock`
--

INSERT INTO `fuel_stock` (`fuel_type_id`, `quantity`, `updated_at`) VALUES
(1, 51.00, '2025-09-28 02:29:40'),
(2, 30.00, '2025-09-28 02:23:22'),
(3, 70.00, '2025-09-28 02:33:50'),
(4, 60.00, '2025-09-28 02:25:43');

-- --------------------------------------------------------

--
-- Table structure for table `fuel_types`
--

CREATE TABLE `fuel_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fuel_types`
--

INSERT INTO `fuel_types` (`id`, `name`, `is_active`) VALUES
(1, 'Diesel', 1),
(2, 'Kerosene', 1),
(3, 'Unleaded', 1),
(4, 'Premium', 1);

-- --------------------------------------------------------

--
-- Table structure for table `generated_reports`
--

CREATE TABLE `generated_reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `template_id` int(11) NOT NULL,
  `generated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `generated_reports`
--

INSERT INTO `generated_reports` (`id`, `user_id`, `office_id`, `filename`, `template_id`, `generated_at`) VALUES
(3, 17, 0, 'Inventory_Report_20250709_082630.pdf', 0, '2025-07-09 11:26:30'),
(4, 17, 0, 'Inventory_Report_20250709_083052.pdf', 3, '2025-07-09 11:30:53'),
(5, 17, 0, 'Inventory_Report_20250709_083104.pdf', 2, '2025-07-09 11:31:05'),
(6, 17, 0, 'Inventory_Report_20250709_083747.pdf', 2, '2025-07-09 11:37:48'),
(7, 17, 0, 'Inventory_Report_20250709_083756.pdf', 5, '2025-07-09 11:37:57'),
(8, 17, 0, 'Inventory_Report_20250709_084028.pdf', 5, '2025-07-09 11:40:29'),
(9, 17, 0, 'Inventory_Report_20250709_084118.pdf', 5, '2025-07-09 11:41:19'),
(10, 17, 0, 'Inventory_Report_20250709_084201.pdf', 5, '2025-07-09 11:42:02'),
(11, 17, 0, 'Inventory_Report_20250709_084352.pdf', 2, '2025-07-09 11:43:52'),
(12, 17, 0, 'Inventory_Report_20250709_084609.pdf', 5, '2025-07-09 11:46:10'),
(13, 17, 0, 'Inventory_Report_20250709_143428.pdf', 3, '2025-07-09 17:34:29'),
(14, 17, 0, 'Inventory_Report_20250709_143918.pdf', 3, '2025-07-09 17:39:19'),
(15, 17, 0, 'Inventory_Report_20250709_144102.pdf', 28, '2025-07-09 17:41:02'),
(16, 17, 0, 'Inventory_Report_20250709_144323.pdf', 28, '2025-07-09 17:43:23'),
(17, 17, 0, 'Inventory_Report_20250709_144400.pdf', 28, '2025-07-09 17:44:00'),
(18, 17, 0, 'Inventory_Report_20250709_144416.pdf', 27, '2025-07-09 17:44:16'),
(19, 17, 0, 'Inventory_Report_20250709_144544.pdf', 27, '2025-07-09 17:45:44'),
(20, 17, 0, 'Inventory_Report_20250709_144700.pdf', 27, '2025-07-09 17:47:00'),
(21, 17, 0, 'Inventory_Report_20250709_145032.pdf', 26, '2025-07-09 17:50:36'),
(22, 17, 0, 'Inventory_Report_20250709_150358.pdf', 26, '2025-07-09 18:03:59'),
(23, 17, 0, 'Inventory_Report_20250709_151145.pdf', 26, '2025-07-09 18:11:45'),
(24, 17, 0, 'Inventory_Report_20250709_152847.pdf', 28, '2025-07-09 18:28:50'),
(25, 17, 0, 'Inventory_Report_20250709_152941.pdf', 29, '2025-07-09 18:29:41'),
(26, 17, 0, 'Inventory_Report_20250709_153917.pdf', 2, '2025-07-09 18:39:17'),
(27, 17, 0, 'Inventory_Report_20250709_153924.pdf', 30, '2025-07-09 18:39:24'),
(28, 17, 0, 'Inventory_Report_20250710_035858.pdf', 30, '2025-07-10 06:59:03'),
(29, 17, 0, 'Inventory_Report_20250710_144534.pdf', 30, '2025-07-10 17:45:38'),
(30, 17, 0, 'Inventory_Report_20250711_091238.pdf', 33, '2025-07-11 12:12:43'),
(31, 17, 0, 'Inventory_Report_20250711_091547.pdf', 33, '2025-07-11 12:15:48'),
(32, 17, 0, 'Inventory_Report_20250712_075945.pdf', 33, '2025-07-12 10:59:50'),
(33, 17, 0, 'Inventory_Report_20250712_080048.pdf', 33, '2025-07-12 11:00:48'),
(34, 17, 0, 'Inventory_Report_20250714_041546.pdf', 4, '2025-07-14 09:15:51'),
(35, 17, 0, 'Inventory_Report_20250801_073031.pdf', 3, '2025-08-01 12:30:36'),
(36, 17, 0, 'Inventory_Report_20250801_093122.pdf', 3, '2025-08-01 14:31:23'),
(37, 17, 0, 'Inventory_Report_20250802_141907.pdf', 0, '2025-08-02 20:19:11'),
(38, 17, 0, 'Inventory_Report_20250804_015832.pdf', 0, '2025-08-04 06:58:37'),
(39, 17, 0, 'Inventory_Report_20250804_020323.pdf', 0, '2025-08-04 07:03:24'),
(40, 17, 0, 'Inventory_Report_20250804_020424.pdf', 0, '2025-08-04 07:04:25'),
(41, 17, 0, 'Inventory_Report_20250819_070930.pdf', 0, '2025-08-19 13:09:35'),
(42, 17, 0, 'Inventory_Report_20250829_131934.pdf', 0, '2025-08-29 18:19:35'),
(43, 17, 0, 'Inventory_Report_20250901_033647.pdf', 0, '2025-09-01 08:36:48'),
(44, 17, 0, 'Inventory_Report_20250911_080604.pdf', 0, '2025-09-11 14:06:06'),
(45, 24, 0, 'Inventory_Report_20250914_054735.pdf', 0, '2025-09-14 11:47:39'),
(46, 24, 0, 'Inventory_Report_20250914_054912.pdf', 0, '2025-09-14 11:49:13'),
(47, 24, 0, 'Inventory_Report_20250914_055349.pdf', 0, '2025-09-14 11:53:49'),
(50, 24, 3, 'Inventory_Report_20250914_133109.pdf', 0, '2025-09-14 19:31:10'),
(52, 17, 9, 'Inventory_Report_20250914_133854.pdf', 0, '2025-09-14 19:38:54'),
(53, 17, 3, 'Consumption_Report_20250915_042223.pdf', 0, '2025-09-15 07:22:27'),
(54, 17, 19, 'Consumption_Report_20250915_042438.pdf', 0, '2025-09-15 07:24:39'),
(56, 17, 4, 'Consumption_Report_20250915_043214.pdf', 0, '2025-09-15 07:32:14'),
(57, 17, 4, 'Consumption_Report_20250915_043650.pdf', 0, '2025-09-15 07:36:50'),
(58, 17, 4, 'Consumption_Report_20250915_043802.pdf', 0, '2025-09-15 07:38:04'),
(59, 17, 4, 'Consumption_Report_20250915_044043.pdf', 0, '2025-09-15 07:40:44'),
(60, 17, 4, 'Consumption_Report_20250915_044153.pdf', 0, '2025-09-15 07:41:55'),
(61, 17, 4, 'Consumption_Report_20250915_044355.pdf', 0, '2025-09-15 07:43:56'),
(62, 17, 4, 'Consumption_Report_20250915_044413.pdf', 0, '2025-09-15 07:44:14'),
(63, 17, 4, 'Consumption_Report_20250915_044528.pdf', 0, '2025-09-15 07:45:30'),
(64, 17, 4, 'Consumption_Report_20250915_044635.pdf', 0, '2025-09-15 07:46:37'),
(65, 17, 4, 'Consumption_Report_20250915_044700.pdf', 0, '2025-09-15 07:47:02'),
(66, 17, 4, 'Consumption_Report_20250915_044709.pdf', 0, '2025-09-15 07:47:12'),
(67, 17, 4, 'Consumption_Report_20250915_044853.pdf', 0, '2025-09-15 07:48:55'),
(68, 24, 3, 'Consumption_Report_20250915_050138.pdf', 0, '2025-09-15 08:01:40'),
(69, 17, 4, 'Inventory_Report_20250920_040655.pdf', 0, '2025-09-20 07:06:56'),
(70, 17, 4, 'Inventory_Report_20250920_041253.pdf', 0, '2025-09-20 07:12:54'),
(71, 17, 4, 'Inventory_Report_20250920_041307.pdf', 0, '2025-09-20 07:13:08'),
(72, 17, 4, 'Inventory_Report_20250920_041334.pdf', 0, '2025-09-20 07:13:35'),
(73, 17, 4, 'Inventory_Report_20250920_041356.pdf', 0, '2025-09-20 07:13:57'),
(74, 17, 4, 'Consumption_Report_20250920_050259.pdf', 0, '2025-09-20 08:03:01'),
(75, 17, 4, 'Inventory_Report_20250920_113856.pdf', 0, '2025-09-20 14:38:57'),
(76, 17, 4, 'Employee_MR_Report_20250920_114949.pdf', 0, '2025-09-20 14:49:49'),
(77, 17, 4, 'Employee_MR_Report_20250920_133944.pdf', 0, '2025-09-20 16:39:48'),
(78, 17, 4, 'Employee_MR_Report_20250920_140306.pdf', 0, '2025-09-20 17:03:07'),
(79, 17, 4, 'Employee_MR_Report_20250921_103219.pdf', 0, '2025-09-21 13:32:23'),
(80, 17, 4, 'Inventory_Report_20250921_103829.pdf', 0, '2025-09-21 13:38:30'),
(81, 17, 4, 'Consumption_Report_20250921_104456.pdf', 0, '2025-09-21 13:44:57'),
(82, 17, 4, 'Consumption_Report_20250921_104505.pdf', 0, '2025-09-21 13:45:05'),
(83, 17, 4, 'Inventory_Report_20250921_112526.pdf', 0, '2025-09-21 14:25:27'),
(84, 17, 4, 'Category_Inventory_Report_<br_/>\r\n<b>Warning</b>:__Undefined_array_key__2025-09-22_10-04-38.pdf', 0, '2025-09-22 13:04:39'),
(85, 17, 4, 'Category_Inventory_Report_Unknown_Category_2025-09-22_10-06-23.pdf', 0, '2025-09-22 13:06:23'),
(86, 17, 4, 'Inventory_Report_20250922_100930.pdf', 0, '2025-09-22 13:09:30'),
(87, 17, 4, 'Inventory_Report_20250922_101005.pdf', 0, '2025-09-22 13:10:06'),
(88, 17, 4, 'Unserviceable_Inventory_Report_2024-09_20250922_173512.pdf', 0, '2025-09-22 20:35:15'),
(89, 17, 4, 'Unserviceable_Inventory_Report_2025-09_20250922_173601.pdf', 0, '2025-09-22 20:36:02'),
(90, 17, 4, 'Unserviceable_Inventory_Report_2025-09_20250922_174512.pdf', 0, '2025-09-22 20:45:13'),
(91, 17, 4, 'Unserviceable_Inventory_Report_2025-09_20250922_174913.pdf', 0, '2025-09-22 20:49:13'),
(92, 17, 4, 'Unserviceable_Inventory_Report_2025-09_20250922_174918.pdf', 0, '2025-09-22 20:49:19'),
(93, 17, 4, 'Employee_MR_Report_20250922_180210.pdf', 0, '2025-09-22 21:02:11'),
(94, 17, 4, 'Inventory_Report_20250928_052258.pdf', 0, '2025-09-28 08:22:59'),
(95, 17, 4, 'Inventory_Report_20250928_052308.pdf', 0, '2025-09-28 08:23:09'),
(96, 17, 4, 'fuel_log_report_20250928_093013.pdf', 0, '2025-09-28 12:30:14'),
(97, 17, 4, 'fuel_log_export_20250928_093025.csv', 0, '2025-09-28 12:30:25'),
(98, 17, 4, 'fuel_out_report_20250928_093515.pdf', 0, '2025-09-28 12:35:15'),
(99, 17, 4, 'assets_report_20250928_093956.pdf', 0, '2025-09-28 12:39:56'),
(100, 17, 4, 'consumables_report_20250928_095309.pdf', 0, '2025-09-28 12:53:10'),
(101, 17, 4, 'consumables_export_20250928_095324.csv', 0, '2025-09-28 12:53:24'),
(102, 17, 4, 'unserviceable_report_20250928_100359.pdf', 0, '2025-09-28 13:04:00'),
(103, 17, 4, 'unserviceable_report_20250928_100413.pdf', 0, '2025-09-28 13:04:13'),
(104, 17, 4, 'Employee_MR_Report_20250928_113351.pdf', 0, '2025-09-28 14:33:52'),
(105, 17, 4, 'fuel_log_export_20250930_070737.csv', 0, '2025-09-30 13:07:37'),
(106, 17, 4, 'fuel_log_report_20250930_070740.pdf', 0, '2025-09-30 13:07:40'),
(107, 17, 4, 'fuel_log_export_20250930_074817.csv', 0, '2025-09-30 13:48:17'),
(108, 17, 4, 'fuel_log_report_20250930_074821.pdf', 0, '2025-09-30 13:48:23'),
(109, 17, 4, 'assets_report_20251001_044350.pdf', 0, '2025-10-01 10:43:51');

-- --------------------------------------------------------

--
-- Table structure for table `google_drive_settings`
--

CREATE TABLE `google_drive_settings` (
  `id` tinyint(4) NOT NULL DEFAULT 1,
  `client_id` text DEFAULT NULL,
  `client_secret` text DEFAULT NULL,
  `redirect_uri` text DEFAULT NULL,
  `folder_id` varchar(128) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `google_drive_settings`
--

INSERT INTO `google_drive_settings` (`id`, `client_id`, `client_secret`, `redirect_uri`, `folder_id`, `created_at`, `updated_at`) VALUES
(1, '', '', 'http://localhost/PILAR_ASSET_INVENTORY/SYSTEM_ADMIN/drive_oauth_callback.php', '', '2025-09-23 18:05:27', '2025-09-27 00:34:18');

-- --------------------------------------------------------

--
-- Table structure for table `google_drive_tokens`
--

CREATE TABLE `google_drive_tokens` (
  `id` tinyint(4) NOT NULL DEFAULT 1,
  `refresh_token` text DEFAULT NULL,
  `access_token` text DEFAULT NULL,
  `expires_at` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `google_drive_tokens`
--

INSERT INTO `google_drive_tokens` (`id`, `refresh_token`, `access_token`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, NULL, '2025-09-23 18:05:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ics_form`
--

CREATE TABLE `ics_form` (
  `id` int(11) NOT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `entity_name` varchar(255) DEFAULT NULL,
  `fund_cluster` varchar(100) DEFAULT NULL,
  `ics_no` varchar(100) DEFAULT NULL,
  `received_from_name` varchar(255) NOT NULL,
  `received_from_position` varchar(255) NOT NULL,
  `received_by_name` varchar(255) NOT NULL,
  `received_by_position` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `office_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ics_form`
--

INSERT INTO `ics_form` (`id`, `header_image`, `entity_name`, `fund_cluster`, `ics_no`, `received_from_name`, `received_from_position`, `received_by_name`, `received_by_position`, `created_at`, `office_id`) VALUES
(1, 'ics_header_1759148152_257b5989.png', 'LGU PILAR', '', 'ICS-2025-0001', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-29 12:15:52', 4),
(2, 'ics_header_1759148152_257b5989.png', 'LGU PILAR', 'FC-2025-001', 'ICS-2025-0001', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-30 02:46:36', 4),
(3, 'ics_header_1759148152_257b5989.png', 'LGU PILAR', 'FC-2025-002', 'ICS-2025-0001', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-30 15:34:51', 4),
(4, 'ics_header_1759148152_257b5989.png', 'LGU PILAR', 'FC-2025-002', 'ICS-2025-0001', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-30 15:45:59', 30),
(5, 'ics_header_1759148152_257b5989.png', 'LGU PILAR', 'FC-2025-002', 'ICS-2025-0002', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-10-01 04:00:32', 34),
(6, 'ics_header_1759148152_257b5989.png', 'OMM', 'FC-2025-002', 'ICS-2025-0006', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-10-02 09:55:12', 33),
(7, 'ics_header_1759148152_257b5989.png', 'OMM', 'FC-2025-001', 'ICS-2025-0009', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-10-02 10:00:30', 33),
(8, 'ics_header_1759148152_257b5989.png', 'GAD', 'FC-2025-0011', 'ICS-2025-00041', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-10-02 12:21:33', 19),
(9, 'ics_header_1759148152_257b5989.png', 'KALAHI', 'FC-2025-001', 'ICS-2025-00022', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-10-02 12:25:57', 24);

-- --------------------------------------------------------

--
-- Table structure for table `ics_items`
--

CREATE TABLE `ics_items` (
  `item_id` int(11) NOT NULL,
  `ics_id` int(11) NOT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `ics_no` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `unit_cost` decimal(12,2) NOT NULL,
  `total_cost` decimal(12,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `item_no` varchar(50) DEFAULT NULL,
  `estimated_useful_life` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ics_items`
--

INSERT INTO `ics_items` (`item_id`, `ics_id`, `asset_id`, `ics_no`, `quantity`, `unit`, `unit_cost`, `total_cost`, `description`, `item_no`, `estimated_useful_life`, `created_at`) VALUES
(1, 1, 8, '', 30, 'unit', 6650.00, 199500.00, 'Wheel Chair', '', '2 years', '2025-09-29 12:15:52'),
(2, 2, 39, 'ICS-2025-0001', 2, 'unit', 45000.00, 90000.00, 'Laptop AMD Ryzen', '', '3 years', '2025-09-30 02:46:36'),
(3, 3, 44, 'ICS-2025-0001', 2, 'unit', 18000.00, 36000.00, 'Grass cutter', '', '2 years', '2025-09-30 15:34:51'),
(4, 3, 46, 'ICS-2025-0001', 1, 'unit', 3500.00, 3500.00, 'Power Drill (corded)', '', '3 years', '2025-09-30 15:34:51'),
(5, 5, 47, 'ICS-2025-0002', 1, 'unit', 4500.00, 4500.00, 'Printer Epson', '1', '2 years', '2025-10-01 04:00:32'),
(6, 6, 52, 'ICS-2025-0006', 1, 'unit', 42000.00, 42000.00, 'Conference Table', 'ITM-55-1', '5 years', '2025-10-02 09:55:13'),
(7, 7, 53, 'ICS-2025-0009', 1, 'unit', 10000.00, 10000.00, 'conference table', '2022-0003', '5 years', '2025-10-02 10:00:30'),
(8, 8, 55, 'ICS-2025-00041', 1, 'unit', 52000.00, 52000.00, 'computer desktop', 'PROP-00031', '3 years', '2025-10-02 12:21:33'),
(9, 9, 56, 'ICS-2025-00022', 1, 'unit', 54000.00, 54000.00, 'Lappy', 'STOCK-00172', '3 years', '2025-10-02 12:25:57');

-- --------------------------------------------------------

--
-- Table structure for table `iirup_form`
--

CREATE TABLE `iirup_form` (
  `id` int(11) NOT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `accountable_officer` varchar(100) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `office` varchar(100) NOT NULL,
  `footer_accountable_officer` varchar(100) NOT NULL,
  `footer_authorized_official` varchar(100) NOT NULL,
  `footer_designation_officer` varchar(100) NOT NULL,
  `footer_designation_official` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iirup_form`
--

INSERT INTO `iirup_form` (`id`, `header_image`, `accountable_officer`, `designation`, `office`, `footer_accountable_officer`, `footer_authorized_official`, `footer_designation_officer`, `footer_designation_official`, `created_at`) VALUES
(1, 'iirup_header_1759149310_f782115c_iirup.png', 'IVAN CHRISTOPER MILLABAS', 'OFFICE', 'Supply Office', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-29 12:35:10'),
(2, 'iirup_header_1759149310_f782115c_iirup.png', 'IVAN CHRISTOPER MILLABAS', 'OFFICE', 'Supply Office', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-30 02:56:39'),
(3, 'iirup_header_1759149310_f782115c_iirup.png', 'IVAN CHRISTOPER MILLABAS', 'OFFICE', 'Supply Office', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-10-02 11:16:35');

-- --------------------------------------------------------

--
-- Table structure for table `iirup_items`
--

CREATE TABLE `iirup_items` (
  `item_id` int(11) NOT NULL,
  `iirup_id` int(11) DEFAULT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `particulars` varchar(255) DEFAULT NULL,
  `property_no` varchar(255) DEFAULT NULL,
  `qty` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `accumulated_depreciation` decimal(12,2) DEFAULT NULL,
  `accumulated_impairment_losses` decimal(12,2) DEFAULT NULL,
  `carrying_amount` decimal(12,2) DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL,
  `sale` varchar(255) DEFAULT NULL,
  `transfer` varchar(255) DEFAULT NULL,
  `destruction` varchar(255) DEFAULT NULL,
  `others` varchar(255) DEFAULT NULL,
  `total` decimal(12,2) DEFAULT NULL,
  `appraised_value` decimal(12,2) DEFAULT NULL,
  `or_no` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `dept_office` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `red_tag` varchar(255) DEFAULT NULL,
  `date_received` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `iirup_items`
--

INSERT INTO `iirup_items` (`item_id`, `iirup_id`, `asset_id`, `date_acquired`, `particulars`, `property_no`, `qty`, `unit_cost`, `total_cost`, `accumulated_depreciation`, `accumulated_impairment_losses`, `carrying_amount`, `remarks`, `sale`, `transfer`, `destruction`, `others`, `total`, `appraised_value`, `or_no`, `amount`, `dept_office`, `code`, `red_tag`, `date_received`, `created_at`) VALUES
(1, 1, 8, '2025-09-29', 'Wheel Chair', '', 1, 6650.00, 6650.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-29', '2025-09-29 12:35:10'),
(2, 2, 39, '2025-09-30', 'Laptop AMD Ryzen', '', 1, 45000.00, 45000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-30', '2025-09-30 02:56:39'),
(3, 3, 53, '2025-10-02', 'conference table', 'PS-5S-03-F02-01-01', 1, 10000.00, 10000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'OMM', '', '', '2025-10-02', '2025-10-02 11:16:35');

-- --------------------------------------------------------

--
-- Table structure for table `infrastructure_inventory`
--

CREATE TABLE `infrastructure_inventory` (
  `inventory_id` int(11) NOT NULL,
  `classification_type` varchar(255) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `nature_occupancy` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date_constructed_acquired_manufactured` date DEFAULT NULL,
  `property_no_or_reference` varchar(100) DEFAULT NULL,
  `acquisition_cost` decimal(15,2) DEFAULT NULL,
  `market_appraisal_insurable_interest` decimal(15,2) DEFAULT NULL,
  `date_of_appraisal` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `image_1` varchar(255) DEFAULT NULL,
  `image_2` varchar(255) DEFAULT NULL,
  `image_3` varchar(255) DEFAULT NULL,
  `image_4` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `infrastructure_inventory`
--

INSERT INTO `infrastructure_inventory` (`inventory_id`, `classification_type`, `item_description`, `nature_occupancy`, `location`, `date_constructed_acquired_manufactured`, `property_no_or_reference`, `acquisition_cost`, `market_appraisal_insurable_interest`, `date_of_appraisal`, `remarks`, `image_1`, `image_2`, `image_3`, `image_4`) VALUES
(1, 'BUILDING', 'Multi Purpose bldg.', 'Gymnasium', 'LGU-Complex', '2025-09-04', 'BLDNG22-32', 6792388.00, 777406.50, '2025-09-04', '', 'uploads/1756949402_397369.jpg', 'uploads/1756949402_ChatGPT Image Jul 17, 2025, 08_24_14 AM.png', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inter_department_approvals`
--

CREATE TABLE `inter_department_approvals` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `approver_id` int(11) NOT NULL,
  `approval_type` enum('office_head','source_office') NOT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `comments` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_actions`
--

CREATE TABLE `inventory_actions` (
  `action_id` int(11) NOT NULL,
  `action_name` varchar(255) NOT NULL,
  `office_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `itr_form`
--

CREATE TABLE `itr_form` (
  `itr_id` int(10) UNSIGNED NOT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `entity_name` varchar(150) NOT NULL,
  `fund_cluster` varchar(100) DEFAULT NULL,
  `from_accountable_officer` varchar(150) NOT NULL,
  `to_accountable_officer` varchar(150) NOT NULL,
  `itr_no` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `transfer_type` enum('donation','reassignment','relocate','others') NOT NULL,
  `reason_for_transfer` text DEFAULT NULL,
  `approved_by` varchar(150) DEFAULT NULL,
  `approved_designation` varchar(150) DEFAULT NULL,
  `approved_date` date DEFAULT NULL,
  `released_by` varchar(150) DEFAULT NULL,
  `released_designation` varchar(150) DEFAULT NULL,
  `released_date` date DEFAULT NULL,
  `received_by` varchar(150) DEFAULT NULL,
  `received_designation` varchar(150) DEFAULT NULL,
  `received_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `itr_form`
--

INSERT INTO `itr_form` (`itr_id`, `header_image`, `entity_name`, `fund_cluster`, `from_accountable_officer`, `to_accountable_officer`, `itr_no`, `date`, `transfer_type`, `reason_for_transfer`, `approved_by`, `approved_designation`, `approved_date`, `released_by`, `released_designation`, `released_date`, `received_by`, `received_designation`, `received_date`) VALUES
(1, '1759292224_ITR.png', 'LGU PILAR', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Madison Brooks', 'ITR-2025-002', '2025-09-30', 'reassignment', 'unserviceable', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Madison Brooks', 'OFFICE', '0000-00-00'),
(2, '1759292224_ITR.png', 'LGU PILAR', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Nora Powell', 'ITR-2025-002', '2025-09-30', 'reassignment', 'reassignment', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Nora Powell', 'OFFICE', '0000-00-00'),
(3, '1759292224_ITR.png', 'LGU PILAR', 'FC-2025-001', 'MARK JAYSON NAMIA', 'John Smith', 'ITR-2025-002', '2025-09-30', 'reassignment', 'reassignment', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'John Smith', 'OFFICE', '0000-00-00'),
(4, '1759292224_ITR.png', 'RHU', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Mason Young', 'ITR-2025-002', '2025-09-30', 'reassignment', 'reassignment', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Mason Young', 'OFFICE', '0000-00-00'),
(5, '1759292224_ITR.png', 'LGU PILAR', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Madison Brooks', 'ITR-2025-001', '2025-09-30', 'reassignment', 'reassignment', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Madison Brooks', 'OFFICE', '0000-00-00'),
(6, '1759292224_ITR.png', 'LGU PILAR', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Maria Santos', 'ITR-2025-001', '2025-09-30', 'reassignment', 'to person accountable', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Maria Santos', 'OFFICE', '0000-00-00'),
(7, '1759292224_ITR.png', 'RHU', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Nora Powell', 'ITR-2025-002', '2025-09-30', 'reassignment', 'Reason', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Nora Powell', 'OFFICE', '0000-00-00'),
(8, '1759292224_ITR.png', 'LGU PILAR', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Madison Brooks', 'ITR-2025-002', '2025-09-30', 'reassignment', 'reason', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Madison Brooks', 'OFFICE', '0000-00-00'),
(9, '1759292224_ITR.png', 'LGU PILAR', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Leo Peterson', 'ITR-2025-002', '2025-09-30', 'reassignment', 'reeason', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Leo Peterson', 'OFFICE', '0000-00-00'),
(11, '1759292224_ITR.png', 'DepEd', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Mason Young', 'ITR-2025-001', '2025-09-30', 'reassignment', 'reason', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Mason Young', 'OFFICE', '0000-00-00'),
(12, '1759292224_ITR.png', 'RHU', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Owen Perez', 'ITR-2025-002', '2025-09-30', 'reassignment', 're', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Owen Perez', 'OFFICE', '0000-00-00'),
(13, '1759292224_ITR.png', 'RHU', 'fc-001', 'MARK JAYSON NAMIA', 'Lillian Foster', 'ITR-2025-002', '2025-09-30', 'reassignment', 're', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Lillian Foster', 'OFFICE', '0000-00-00'),
(14, '1759292224_ITR.png', 'LGU', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Madison Brooks', 'ITR-2025-001', '2025-09-30', 'reassignment', 're', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Madison Brooks', 'OFFICE', '0000-00-00'),
(15, '1759292224_ITR.png', 'INVENTORY', 'FC-2025-001', 'MARK JAYSON NAMIA', 'Maria Santos', 'ITR-2025-002', '2025-09-30', 'reassignment', 're', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Maria Santos', 'OFFICE', '0000-00-00'),
(16, '1759468945_Screenshot_2025-10-03_132112.png', 'LGU PILAR', 'FC-2025-0011', 'MARK JAYSON NAMIA', 'Amelia Lewis', 'ITR-2025-0011', '2025-09-30', 'reassignment', 'reass', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Amelia Lewis', 'OFFICE', '0000-00-00'),
(17, '1759468945_Screenshot_2025-10-03_132112.png', 'INVENTORY', 'fc-001', 'Maria Santos', 'Leo Peterson', 'ITR-2025-00113', '2025-09-30', 'reassignment', 'Reason', 'CAROLYN SY-REYES', 'OFFICE', '2025-09-30', 'MARK JAYSON NAMIA', 'OFFICE', '2025-09-30', 'Leo Peterson', 'OFFICE', '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `itr_items`
--

CREATE TABLE `itr_items` (
  `item_id` int(11) NOT NULL,
  `itr_id` int(10) UNSIGNED NOT NULL,
  `date_acquired` date DEFAULT NULL,
  `property_no` varchar(100) DEFAULT NULL,
  `asset_id` int(11) NOT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `condition_of_PPE` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `itr_items`
--

INSERT INTO `itr_items` (`item_id`, `itr_id`, `date_acquired`, `property_no`, `asset_id`, `description`, `amount`, `condition_of_PPE`) VALUES
(8, 1, '2025-09-29', 'PN-2019-05-02-0001-01', 8, 'Notebook i7 (PN-2019-05-02-0001-01)', 80245.00, 'Serviceable'),
(9, 2, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Grass cutter (PN-2019-05-02-0001-01)', 18000.00, 'Serviceable'),
(10, 3, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(11, 4, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(12, 5, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(13, 6, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(14, 7, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(15, 8, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(16, 9, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(17, 11, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(18, 12, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(19, 13, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(20, 14, '2025-09-30', 'PN-2019-05-02-0001-01', 8, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(21, 15, '2025-09-30', 'PN-2019-05-02-0001-01', 46, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable'),
(22, 16, '2025-10-02', 'PN-2019-05-02-0001-01', 53, 'conference table (PN-2019-05-02-0001-01)', 10000.00, 'Serviceable'),
(23, 17, '2025-09-30', 'PN-2019-05-02-0001-01', 46, 'Power Drill (corded) (PN-2019-05-02-0001-01)', 3500.00, 'Serviceable');

-- --------------------------------------------------------

--
-- Table structure for table `legal_documents`
--

CREATE TABLE `legal_documents` (
  `id` int(11) NOT NULL,
  `document_type` enum('privacy_policy','terms_of_service') NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `version` varchar(50) NOT NULL DEFAULT '1.0',
  `effective_date` date NOT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `updated_by` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `legal_documents`
--

INSERT INTO `legal_documents` (`id`, `document_type`, `title`, `content`, `version`, `effective_date`, `last_updated`, `updated_by`, `is_active`, `created_at`) VALUES
(1, 'privacy_policy', 'Privacy Policy', '<h6 class=\"fw-bold text-primary mb-3\">1. Information We Collect</h6>\n<p>When you use the PILAR Asset Inventory System, we collect the following information:</p>\n<ul>\n    <li><strong>Account Information:</strong> Username, full name, email address, and role assignments</li>\n    <li><strong>System Usage Data:</strong> Login times, asset management activities, and audit logs</li>\n    <li><strong>Technical Information:</strong> IP addresses, browser type, and session data for security purposes</li>\n    <li><strong>Asset Data:</strong> Information about assets you manage within the system</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. How We Use Your Information</h6>\n<p>We use your information to:</p>\n<ul>\n    <li>Provide and maintain the asset inventory management system</li>\n    <li>Authenticate users and maintain account security</li>\n    <li>Track asset movements and maintain audit trails</li>\n    <li>Send important system notifications and updates</li>\n    <li>Improve system functionality and user experience</li>\n    <li>Comply with legal and regulatory requirements</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. Information Sharing</h6>\n<p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>\n<ul>\n    <li>With authorized personnel within your organization</li>\n    <li>When required by law or legal process</li>\n    <li>To protect the security and integrity of our systems</li>\n    <li>With your explicit consent</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Data Security</h6>\n<p>We implement appropriate security measures to protect your information:</p>\n<ul>\n    <li>Encrypted password storage using industry-standard hashing</li>\n    <li>Secure session management with timeout controls</li>\n    <li>Regular security audits and monitoring</li>\n    <li>Access controls based on user roles and permissions</li>\n    <li>Secure data transmission using HTTPS protocols</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\n<p>If you have questions about this Privacy Policy or our data practices, please contact:</p>\n<div class=\"bg-light p-3 rounded\">\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n    Phone: +1 (555) 123-4567<br>\n    Address: [Your Organization Address]\n</div>', '1.0', '2025-09-28', '2025-09-29 02:56:50', 1, 0, '2025-09-28 13:40:44'),
(2, 'terms_of_service', 'Terms of Service', '<h6 class=\"fw-bold text-primary mb-3\">1. Acceptance of Terms</h6>\r\n<p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. System Description</h6>\r\n<p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p>\r\n<ul>\r\n    <li>Track and manage organizational assets</li>\r\n    <li>Maintain detailed asset records and histories</li>\r\n    <li>Provide role-based access controls</li>\r\n    <li>Generate reports and analytics</li>\r\n    <li>Ensure compliance with asset management policies</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. User Accounts and Responsibilities</h6>\r\n<p><strong>Account Security:</strong></p>\r\n<ul>\r\n    <li>You are responsible for maintaining the confidentiality of your login credentials</li>\r\n    <li>You must notify administrators immediately of any unauthorized access</li>\r\n    <li>You agree to use strong passwords and enable security features when available</li>\r\n    <li>You are liable for all activities that occur under your account</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Prohibited Activities</h6>\r\n<p>You agree not to:</p>\r\n<ul>\r\n    <li>Attempt to gain unauthorized access to any part of the System</li>\r\n    <li>Interfere with or disrupt the System operation</li>\r\n    <li>Use the System for any illegal or unauthorized purpose</li>\r\n    <li>Reverse engineer, decompile, or disassemble any part of the System</li>\r\n    <li>Introduce viruses, malware, or other harmful code</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\r\n<p>For questions about these Terms of Service, please contact:</p>\r\n<div class=\"bg-light p-3 rounded\">\r\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\r\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\r\n    Phone: +1 (555) 123-4567<br>\r\n    Address: [Your Organization Address]\r\n</div>', '1.0', '2025-09-28', '2025-09-29 03:03:58', 1, 0, '2025-09-28 13:40:44'),
(3, 'privacy_policy', 'Privacy Policy', '<h6 class=\"fw-bold text-primary mb-3\">1. Information We Collect</h6>\n<p>When you use the PILAR Asset Inventory System, we collect the following information:</p>\n<ul>\n    <li><strong>Account Information:</strong> Username, full name, email address, and role assignments</li>\n    <li><strong>System Usage Data:</strong> Login times, asset management activities, and audit logs</li>\n    <li><strong>Technical Information:</strong> IP addresses, browser type, and session data for security purposes</li>\n    <li><strong>Asset Data:</strong> Information about assets you manage within the system</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. How We Use Your Information</h6>\n<p>We use your information to:</p>\n<ul>\n    <li>Provide and maintain the asset inventory management system</li>\n    <li>Authenticate users and maintain account security</li>\n    <li>Track asset movements and maintain audit trails</li>\n    <li>Send important system notifications and updates</li>\n    <li>Improve system functionality and user experience</li>\n    <li>Comply with legal and regulatory requirements</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. Information Sharing</h6>\n<p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>\n<ul>\n    <li>With authorized personnel within your organization</li>\n    <li>When required by law or legal process</li>\n    <li>To protect the security and integrity of our systems</li>\n    <li>With your explicit consent</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Data Security</h6>\n<p>We implement appropriate security measures to protect your information:</p>\n<ul>\n    <li>Encrypted password storage using industry-standard hashing</li>\n    <li>Secure session management with timeout controls</li>\n    <li>Regular security audits and monitoring</li>\n    <li>Access controls based on user roles and permissions</li>\n    <li>Secure data transmission using HTTPS protocols</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\n<p>If you have questions about this Privacy Policy or our data practices, please contact:</p>\n<div class=\"bg-light p-3 rounded\">\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n    Phone: +1 (555) 123-4567<br>\n    Address: [Your Organization Address]\n</div>', '1.0', '2025-09-28', '2025-09-29 02:56:50', 1, 0, '2025-09-28 13:43:48'),
(4, 'terms_of_service', 'Terms of Service', '<h6 class=\"fw-bold text-primary mb-3\">1. Acceptance of Terms</h6>\n<p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. System Description</h6>\n<p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p>\n<ul>\n    <li>Track and manage organizational assets</li>\n    <li>Maintain detailed asset records and histories</li>\n    <li>Provide role-based access controls</li>\n    <li>Generate reports and analytics</li>\n    <li>Ensure compliance with asset management policies</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. User Accounts and Responsibilities</h6>\n<p><strong>Account Security:</strong></p>\n<ul>\n    <li>You are responsible for maintaining the confidentiality of your login credentials</li>\n    <li>You must notify administrators immediately of any unauthorized access</li>\n    <li>You agree to use strong passwords and enable security features when available</li>\n    <li>You are liable for all activities that occur under your account</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Prohibited Activities</h6>\n<p>You agree not to:</p>\n<ul>\n    <li>Attempt to gain unauthorized access to any part of the System</li>\n    <li>Interfere with or disrupt the System operation</li>\n    <li>Use the System for any illegal or unauthorized purpose</li>\n    <li>Reverse engineer, decompile, or disassemble any part of the System</li>\n    <li>Introduce viruses, malware, or other harmful code</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\n<p>For questions about these Terms of Service, please contact:</p>\n<div class=\"bg-light p-3 rounded\">\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n    Phone: +1 (555) 123-4567<br>\n    Address: [Your Organization Address]\n</div>', '1.0', '2025-09-28', '2025-09-29 03:03:58', 1, 0, '2025-09-28 13:43:48'),
(5, 'privacy_policy', 'Privacy Policy', '<div class=\"privacy-content\">\n    <p class=\"text-muted mb-4\">\n        <strong>Effective Date:</strong> <?= date(\'F j, Y\'); ?><br>\n        <strong>Last Updated:</strong> <?= date(\'F j, Y\'); ?>\n    </p>\n\n    <h6 class=\"fw-bold text-primary mb-3\">1. Information We Collect</h6>\n    <p>When you use the PILAR Asset Inventory System, we collect the following information:</p>\n    <ul>\n        <li><strong>Account Information:</strong> Username, full name, email address, and role assignments</li>\n        <li><strong>System Usage Data:</strong> Login times, asset management activities, and audit logs</li>\n        <li><strong>Technical Information:</strong> IP addresses, browser type, and session data for security purposes</li>\n        <li><strong>Asset Data:</strong> Information about assets you manage within the system</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">2. How We Use Your Information</h6>\n    <p>We use your information to:</p>\n    <ul>\n        <li>Provide and maintain the asset inventory management system</li>\n        <li>Authenticate users and maintain account security</li>\n        <li>Track asset movements and maintain audit trails</li>\n        <li>Send important system notifications and updates</li>\n        <li>Improve system functionality and user experience</li>\n        <li>Comply with legal and regulatory requirements</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">3. Information Sharing</h6>\n    <p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>\n    <ul>\n        <li>With authorized personnel within your organization</li>\n        <li>When required by law or legal process</li>\n        <li>To protect the security and integrity of our systems</li>\n        <li>With your explicit consent</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Data Security</h6>\n    <p>We implement appropriate security measures to protect your information:</p>\n    <ul>\n        <li>Encrypted password storage using industry-standard hashing</li>\n        <li>Secure session management with timeout controls</li>\n        <li>Regular security audits and monitoring</li>\n        <li>Access controls based on user roles and permissions</li>\n        <li>Secure data transmission using HTTPS protocols</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Data Retention</h6>\n    <p>We retain your information for as long as necessary to:</p>\n    <ul>\n        <li>Provide the services you\'ve requested</li>\n        <li>Maintain audit trails as required by regulations</li>\n        <li>Comply with legal obligations</li>\n        <li>Resolve disputes and enforce agreements</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">6. Your Rights</h6>\n    <p>You have the right to:</p>\n    <ul>\n        <li>Access and review your personal information</li>\n        <li>Request corrections to inaccurate data</li>\n        <li>Request deletion of your account (subject to legal requirements)</li>\n        <li>Receive information about data breaches that may affect you</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">7. Cookies and Tracking</h6>\n    <p>We use cookies and similar technologies to:</p>\n    <ul>\n        <li>Maintain your login session</li>\n        <li>Remember your preferences</li>\n        <li>Provide \"Remember Me\" functionality</li>\n        <li>Analyze system usage for improvements</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">8. Changes to This Policy</h6>\n    <p>We may update this Privacy Policy from time to time. We will notify users of any material changes by:</p>\n    <ul>\n        <li>Posting the updated policy on this page</li>\n        <li>Sending email notifications for significant changes</li>\n        <li>Updating the \"Last Updated\" date at the top of this policy</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">9. Contact Information</h6>\n    <p>If you have questions about this Privacy Policy or our data practices, please contact:</p>\n    <div class=\"bg-light p-3 rounded\">\n        <strong>PILAR Asset Inventory System Administrator</strong><br>\n        Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n        Phone: +1 (555) 123-4567<br>\n        Address: [Your Organization Address]\n    </div>\n</div>', '1', '0000-00-00', '2025-09-29 02:56:50', 1, 0, '2025-09-28 14:12:18'),
(6, 'terms_of_service', 'Terms of Service', '<div class=\"terms-content\">\n    <p class=\"text-muted mb-4\">\n        <strong>Effective Date:</strong> <?= date(\'F j, Y\'); ?><br>\n        <strong>Last Updated:</strong> <?= date(\'F j, Y\'); ?>\n    </p>\n\n    <h6 class=\"fw-bold text-primary mb-3\">1. Acceptance of Terms</h6>\n    <p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">2. System Description</h6>\n    <p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p>\n    <ul>\n        <li>Track and manage organizational assets</li>\n        <li>Maintain detailed asset records and histories</li>\n        <li>Provide role-based access controls</li>\n        <li>Generate reports and analytics</li>\n        <li>Ensure compliance with asset management policies</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">3. User Accounts and Responsibilities</h6>\n    <p><strong>Account Security:</strong></p>\n    <ul>\n        <li>You are responsible for maintaining the confidentiality of your login credentials</li>\n        <li>You must notify administrators immediately of any unauthorized access</li>\n        <li>You agree to use strong passwords and enable security features when available</li>\n        <li>You are liable for all activities that occur under your account</li>\n    </ul>\n    \n    <p><strong>Authorized Use:</strong></p>\n    <ul>\n        <li>Access is granted only to authorized personnel</li>\n        <li>You may only access data and functions appropriate to your assigned role</li>\n        <li>Sharing of login credentials is strictly prohibited</li>\n        <li>You must comply with your organization\'s asset management policies</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Prohibited Activities</h6>\n    <p>You agree not to:</p>\n    <ul>\n        <li>Attempt to gain unauthorized access to any part of the System</li>\n        <li>Interfere with or disrupt the System\'s operation</li>\n        <li>Use the System for any illegal or unauthorized purpose</li>\n        <li>Reverse engineer, decompile, or disassemble any part of the System</li>\n        <li>Introduce viruses, malware, or other harmful code</li>\n        <li>Access or attempt to access accounts belonging to other users</li>\n        <li>Export or share sensitive asset data without proper authorization</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Data Accuracy and Integrity</h6>\n    <p>Users are responsible for:</p>\n    <ul>\n        <li>Ensuring the accuracy of data entered into the System</li>\n        <li>Promptly updating asset information when changes occur</li>\n        <li>Reporting discrepancies or errors to system administrators</li>\n        <li>Following established procedures for asset management</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">6. System Availability</h6>\n    <p>While we strive to maintain continuous service:</p>\n    <ul>\n        <li>The System may be temporarily unavailable for maintenance</li>\n        <li>We do not guarantee 100% uptime or availability</li>\n        <li>Scheduled maintenance will be announced in advance when possible</li>\n        <li>Emergency maintenance may occur without prior notice</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">7. Intellectual Property</h6>\n    <p>The PILAR Asset Inventory System and its contents are protected by intellectual property laws:</p>\n    <ul>\n        <li>All software, designs, and documentation remain our property</li>\n        <li>You receive a limited license to use the System for its intended purpose</li>\n        <li>You may not copy, modify, or distribute any part of the System</li>\n        <li>Your organization retains ownership of data entered into the System</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">8. Privacy and Data Protection</h6>\n    <p>Your privacy is important to us:</p>\n    <ul>\n        <li>Please review our Privacy Policy for details on data handling</li>\n        <li>We implement security measures to protect your information</li>\n        <li>You consent to data processing as described in our Privacy Policy</li>\n        <li>We comply with applicable data protection regulations</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">9. Limitation of Liability</h6>\n    <p>To the maximum extent permitted by law:</p>\n    <ul>\n        <li>We provide the System \"as is\" without warranties</li>\n        <li>We are not liable for indirect, incidental, or consequential damages</li>\n        <li>Our total liability is limited to the amount paid for System access</li>\n        <li>You agree to indemnify us against claims arising from your use of the System</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">10. Termination</h6>\n    <p>These terms remain in effect until terminated:</p>\n    <ul>\n        <li>Your access may be suspended or terminated for violations of these terms</li>\n        <li>You may request account termination by contacting administrators</li>\n        <li>Upon termination, you must cease all use of the System</li>\n        <li>Certain provisions of these terms survive termination</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">11. Changes to Terms</h6>\n    <p>We reserve the right to modify these terms:</p>\n    <ul>\n        <li>Changes will be posted on this page with an updated effective date</li>\n        <li>Continued use after changes constitutes acceptance</li>\n        <li>Material changes will be communicated to users</li>\n        <li>You should review these terms periodically</li>\n    </ul>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">12. Governing Law</h6>\n    <p>These terms are governed by applicable local and federal laws. Any disputes will be resolved through appropriate legal channels in the jurisdiction where the System is operated.</p>\n\n    <h6 class=\"fw-bold text-primary mb-3 mt-4\">13. Contact Information</h6>\n    <p>For questions about these Terms of Service, please contact:</p>\n    <div class=\"bg-light p-3 rounded\">\n        <strong>PILAR Asset Inventory System Administrator</strong><br>\n        Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n        Phone: +1 (555) 123-4567<br>\n        Address: [Your Organization Address]\n    </div>\n\n    <div class=\"alert alert-info mt-4\">\n        <i class=\"bi bi-info-circle me-2\"></i>\n        <strong>Important:</strong> By using the PILAR Asset Inventory System, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy.\n    </div>\n</div>', '1', '0000-00-00', '2025-09-29 03:03:58', 1, 0, '2025-09-28 14:12:18'),
(14, 'terms_of_service', 'Terms of Service', '<p><strong>Effective Date:</strong> </p><p><strong>Last Updated:</strong> </p><h6>1. Acceptance of Terms</h6><p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p><p><br></p><h6>2. System Description</h6><p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p><ul><li>Track and manage organizational assets</li><li>Maintain detailed asset records and histories</li><li>Provide role-based access controls</li><li>Generate reports and analytics</li><li>Ensure compliance with asset management policies</li></ul><h6>3. User Accounts and Responsibilities</h6><p><strong>Account Security:</strong></p><ul><li>You are responsible for maintaining the confidentiality of your login credentials</li><li>You must notify administrators immediately of any unauthorized access</li><li>You agree to use strong passwords and enable security features when available</li><li>You are liable for all activities that occur under your account</li></ul><p><strong>Authorized Use:</strong></p><ul><li>Access is granted only to authorized personnel</li><li>You may only access data and functions appropriate to your assigned role</li><li>Sharing of login credentials is strictly prohibited</li><li>You must comply with your organization\'s asset management policies</li></ul><h6>4. Prohibited Activities</h6><p>You agree not to:</p><ul><li>Attempt to gain unauthorized access to any part of the System</li><li>Interfere with or disrupt the System\'s operation</li><li>Use the System for any illegal or unauthorized purpose</li><li>Reverse engineer, decompile, or disassemble any part of the System</li><li>Introduce viruses, malware, or other harmful code</li><li>Access or attempt to access accounts belonging to other users</li><li>Export or share sensitive asset data without proper authorization</li></ul><h6>5. Data Accuracy and Integrity</h6><p>Users are responsible for:</p><ul><li>Ensuring the accuracy of data entered into the System</li><li>Promptly updating asset information when changes occur</li><li>Reporting discrepancies or errors to system administrators</li><li>Following established procedures for asset management</li></ul><h6>6. System Availability</h6><p>While we strive to maintain continuous service:</p><ul><li>The System may be temporarily unavailable for maintenance</li><li>We do not guarantee 100% uptime or availability</li><li>Scheduled maintenance will be announced in advance when possible</li><li>Emergency maintenance may occur without prior notice</li></ul><h6>7. Intellectual Property</h6><p>The PILAR Asset Inventory System and its contents are protected by intellectual property laws:</p><ul><li>All software, designs, and documentation remain our property</li><li>You receive a limited license to use the System for its intended purpose</li><li>You may not copy, modify, or distribute any part of the System</li><li>Your organization retains ownership of data entered into the System</li></ul><h6>8. Privacy and Data Protection</h6><p>Your privacy is important to us:</p><ul><li>Please review our Privacy Policy for details on data handling</li><li>We implement security measures to protect your information</li><li>You consent to data processing as described in our Privacy Policy</li><li>We comply with applicable data protection regulations</li></ul><h6>9. Limitation of Liability</h6><p>To the maximum extent permitted by law:</p><ul><li>We provide the System \"as is\" without warranties</li><li>We are not liable for indirect, incidental, or consequential damages</li><li>Our total liability is limited to the amount paid for System access</li><li>You agree to indemnify us against claims arising from your use of the System</li></ul><h6>10. Termination</h6><p>These terms remain in effect until terminated:</p><ul><li>Your access may be suspended or terminated for violations of these terms</li><li>You may request account termination by contacting administrators</li><li>Upon termination, you must cease all use of the System</li><li>Certain provisions of these terms survive termination</li></ul><h6>11. Changes to Terms</h6><p>We reserve the right to modify these terms:</p><ul><li>Changes will be posted on this page with an updated effective date</li><li>Continued use after changes constitutes acceptance</li><li>Material changes will be communicated to users</li><li>You should review these terms periodically</li></ul><h6>12. Governing Law</h6><p>These terms are governed by applicable local and federal laws. Any disputes will be resolved through appropriate legal channels in the jurisdiction where the System is operated.</p><p><br></p><h6>13. Contact Information</h6><p>For questions about these Terms of Service, please contact:</p><p><strong>PILAR Asset Inventory System Administrator</strong></p><p> Email: <a href=\"mailto:admin@pilar-system.com\" target=\"_blank\">admin@pilar-system.com</a></p><p> Phone: +1 (555) 123-4567</p><p> Address: [Your Organization Address]calongay</p><p><strong>Important:</strong> By using the PILAR Asset Inventory System, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy.</p>', '1', '2025-09-29', '2025-09-29 03:05:27', 1, 0, '2025-09-29 03:03:58'),
(15, 'terms_of_service', 'Terms of Service', '<p><strong>Effective Date:</strong></p><p><strong>Last Updated:</strong></p><h6>1. Acceptance of Terms</h6><p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p><p><br></p><h6>2. System Description</h6><p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p><ul><li>Track and manage organizational assets</li><li>Maintain detailed asset records and histories</li><li>Provide role-based access controls</li><li>Generate reports and analytics</li><li>Ensure compliance with asset management policies</li></ul><h6>3. User Accounts and Responsibilities</h6><p><strong>Account Security:</strong></p><ul><li>You are responsible for maintaining the confidentiality of your login credentials</li><li>You must notify administrators immediately of any unauthorized access</li><li>You agree to use strong passwords and enable security features when available</li><li>You are liable for all activities that occur under your account</li></ul><p><strong>Authorized Use:</strong></p><ul><li>Access is granted only to authorized personnel</li><li>You may only access data and functions appropriate to your assigned role</li><li>Sharing of login credentials is strictly prohibited</li><li>You must comply with your organization\'s asset management policies</li></ul><h6>4. Prohibited Activities</h6><p>You agree not to:</p><ul><li>Attempt to gain unauthorized access to any part of the System</li><li>Interfere with or disrupt the System\'s operation</li><li>Use the System for any illegal or unauthorized purpose</li><li>Reverse engineer, decompile, or disassemble any part of the System</li><li>Introduce viruses, malware, or other harmful code</li><li>Access or attempt to access accounts belonging to other users</li><li>Export or share sensitive asset data without proper authorization</li></ul><h6>5. Data Accuracy and Integrity</h6><p>Users are responsible for:</p><ul><li>Ensuring the accuracy of data entered into the System</li><li>Promptly updating asset information when changes occur</li><li>Reporting discrepancies or errors to system administrators</li><li>Following established procedures for asset management</li></ul><h6>6. System Availability</h6><p>While we strive to maintain continuous service:</p><ul><li>The System may be temporarily unavailable for maintenance</li><li>We do not guarantee 100% uptime or availability</li><li>Scheduled maintenance will be announced in advance when possible</li><li>Emergency maintenance may occur without prior notice</li></ul><h6>7. Intellectual Property</h6><p>The PILAR Asset Inventory System and its contents are protected by intellectual property laws:</p><ul><li>All software, designs, and documentation remain our property</li><li>You receive a limited license to use the System for its intended purpose</li><li>You may not copy, modify, or distribute any part of the System</li><li>Your organization retains ownership of data entered into the System</li></ul><h6>8. Privacy and Data Protection</h6><p>Your privacy is important to us:</p><ul><li>Please review our Privacy Policy for details on data handling</li><li>We implement security measures to protect your information</li><li>You consent to data processing as described in our Privacy Policy</li><li>We comply with applicable data protection regulations</li></ul><h6>9. Limitation of Liability</h6><p>To the maximum extent permitted by law:</p><ul><li>We provide the System \"as is\" without warranties</li><li>We are not liable for indirect, incidental, or consequential damages</li><li>Our total liability is limited to the amount paid for System access</li><li>You agree to indemnify us against claims arising from your use of the System</li></ul><h6>10. Termination</h6><p>These terms remain in effect until terminated:</p><ul><li>Your access may be suspended or terminated for violations of these terms</li><li>You may request account termination by contacting administrators</li><li>Upon termination, you must cease all use of the System</li><li>Certain provisions of these terms survive termination</li></ul><h6>11. Changes to Terms</h6><p>We reserve the right to modify these terms:</p><ul><li>Changes will be posted on this page with an updated effective date</li><li>Continued use after changes constitutes acceptance</li><li>Material changes will be communicated to users</li><li>You should review these terms periodically</li></ul><h6>12. Governing Law</h6><p>These terms are governed by applicable local and federal laws. Any disputes will be resolved through appropriate legal channels in the jurisdiction where the System is operated.</p><p><br></p><h6>13. Contact Information</h6><p>For questions about these Terms of Service, please contact:</p><p><strong>PILAR Asset Inventory System Administrator</strong></p><p>Email: <a href=\"mailto:admin@pilar-system.com\" target=\"_blank\">admin@pilar-system.com</a></p><p>Phone: +1 (555) 123-4567</p><p>Address: Calongay </p><p><strong>Important:</strong> By using the PILAR Asset Inventory System, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy.</p>', '1', '2025-09-29', '2025-09-29 03:12:21', 1, 0, '2025-09-29 03:05:27'),
(16, 'privacy_policy', 'Privacy Policy', '<p><strong>Effective Date:</strong>&nbsp;September 29, 2025</p><p><strong>Last Updated:</strong>&nbsp;September 29, 2025</p><h6>1. Information We Collect</h6><p>When you use the PILAR Asset Inventory System, we collect the following information:</p><ul><li><strong>Account Information:</strong>&nbsp;Username, full name, email address, and role assignments</li><li><strong>System Usage Data:</strong>&nbsp;Login times, asset management activities, and audit logs</li><li><strong>Technical Information:</strong>&nbsp;IP addresses, browser type, and session data for security purposes</li><li><strong>Asset Data:</strong>&nbsp;Information about assets you manage within the system</li></ul><h6>2. How We Use Your Information</h6><p>We use your information to:</p><ul><li>Provide and maintain the asset inventory management system</li><li>Authenticate users and maintain account security</li><li>Track asset movements and maintain audit trails</li><li>Send important system notifications and updates</li><li>Improve system functionality and user experience</li><li>Comply with legal and regulatory requirements</li></ul><h6>3. Information Sharing</h6><p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p><ul><li>With authorized personnel within your organization</li><li>When required by law or legal process</li><li>To protect the security and integrity of our systems</li><li>With your explicit consent</li></ul><h6>4. Data Security</h6><p>We implement appropriate security measures to protect your information:</p><ul><li>Encrypted password storage using industry-standard hashing</li><li>Secure session management with timeout controls</li><li>Regular security audits and monitoring</li><li>Access controls based on user roles and permissions</li><li>Secure data transmission using HTTPS protocols</li></ul><h6>5. Data Retention</h6><p>We retain your information for as long as necessary to:</p><ul><li>Provide the services you\'ve requested</li><li>Maintain audit trails as required by regulations</li><li>Comply with legal obligations</li><li>Resolve disputes and enforce agreements</li></ul><h6>6. Your Rights</h6><p>You have the right to:</p><ul><li>Access and review your personal information</li><li>Request corrections to inaccurate data</li><li>Request deletion of your account (subject to legal requirements)</li><li>Receive information about data breaches that may affect you</li></ul><h6>7. Cookies and Tracking</h6><p>We use cookies and similar technologies to:</p><ul><li>Maintain your login session</li><li>Remember your preferences</li><li>Provide \"Remember Me\" functionality</li><li>Analyze system usage for improvements</li></ul><h6>8. Changes to This Policy</h6><p>We may update this Privacy Policy from time to time. We will notify users of any material changes by:</p><ul><li>Posting the updated policy on this page</li><li>Sending email notifications for significant changes</li><li>Updating the \"Last Updated\" date at the top of this policy</li></ul><h6>9. Contact Information</h6><p>If you have questions about this Privacy Policy or our data practices, please contact:</p><p><strong>PILAR Asset Inventory System Administrator</strong></p><p>Email:&nbsp;<a href=\"mailto:admin@pilar-system.com\" target=\"_blank\" style=\"color: rgb(13, 110, 253);\">admin@pilar-system.com</a></p><p>Phone: +1 (555) 123-4567</p><p>Address: Calongay</p>', '1.0', '2025-09-29', '2025-09-29 03:11:28', 1, 0, '2025-09-29 03:07:05'),
(17, 'privacy_policy', 'Privacy Policy', '<p><strong>Effective Date:</strong>&nbsp;September 29, 2025</p><p><strong>Last Updated:</strong>&nbsp;September 29, 2025</p><h6>1. Information We Collect</h6><p>When you use the PILAR Asset Inventory System, we collect the following information:</p><ul><li><strong>Account Information:</strong>&nbsp;Username, full name, email address, and role assignments</li><li><strong>System Usage Data:</strong>&nbsp;Login times, asset management activities, and audit logs</li><li><strong>Technical Information:</strong>&nbsp;IP addresses, browser type, and session data for security purposes</li><li><strong>Asset Data:</strong>&nbsp;Information about assets you manage within the system</li></ul><h6>2. How We Use Your Information</h6><p>We use your information to:</p><ul><li>Provide and maintain the asset inventory management system</li><li>Authenticate users and maintain account security</li><li>Track asset movements and maintain audit trails</li><li>Send important system notifications and updates</li><li>Improve system functionality and user experience</li><li>Comply with legal and regulatory requirements</li></ul><h6>3. Information Sharing</h6><p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p><ul><li>With authorized personnel within your organization</li><li>When required by law or legal process</li><li>To protect the security and integrity of our systems</li><li>With your explicit consent</li></ul><h6>4. Data Security</h6><p>We implement appropriate security measures to protect your information:</p><ul><li>Encrypted password storage using industry-standard hashing</li><li>Secure session management with timeout controls</li><li>Regular security audits and monitoring</li><li>Access controls based on user roles and permissions</li><li>Secure data transmission using HTTPS protocols</li></ul><h6>5. Data Retention</h6><p>We retain your information for as long as necessary to:</p><ul><li>Provide the services you\'ve requested</li><li>Maintain audit trails as required by regulations</li><li>Comply with legal obligations</li><li>Resolve disputes and enforce agreements</li></ul><h6>6. Your Rights</h6><p>You have the right to:</p><ul><li>Access and review your personal information</li><li>Request corrections to inaccurate data</li><li>Request deletion of your account (subject to legal requirements)</li><li>Receive information about data breaches that may affect you</li></ul><h6>7. Cookies and Tracking</h6><p>We use cookies and similar technologies to:</p><ul><li>Maintain your login session</li><li>Remember your preferences</li><li>Provide \"Remember Me\" functionality</li><li>Analyze system usage for improvements</li></ul><h6>8. Changes to This Policy</h6><p>We may update this Privacy Policy from time to time. We will notify users of any material changes by:</p><ul><li>Posting the updated policy on this page</li><li>Sending email notifications for significant changes</li><li>Updating the \"Last Updated\" date at the top of this policy</li></ul><h6>9. Contact Information</h6><p>If you have questions about this Privacy Policy or our data practices, please contact:</p><p><strong>PILAR Asset Inventory System Administrator</strong></p><p>Email:&nbsp;<span style=\"color: rgb(0, 29, 53);\">pilarsor.mayor@gmail.com</span></p><p>Phone: <span style=\"color: rgb(0, 29, 53);\">0909 899 6012</span></p><p>Address: <span style=\"color: rgb(0, 29, 53);\">LGU-Pilar Complex, Calongay, Pilar, Sorsogon</span></p>', '1.0', '2025-09-29', '2025-09-29 03:11:28', 1, 1, '2025-09-29 03:11:28'),
(18, 'terms_of_service', 'Terms of Service', '<p><strong>Effective Date:</strong></p><p><strong>Last Updated:</strong></p><h6>1. Acceptance of Terms</h6><p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p><p><br></p><h6>2. System Description</h6><p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p><ul><li>Track and manage organizational assets</li><li>Maintain detailed asset records and histories</li><li>Provide role-based access controls</li><li>Generate reports and analytics</li><li>Ensure compliance with asset management policies</li></ul><h6>3. User Accounts and Responsibilities</h6><p><strong>Account Security:</strong></p><ul><li>You are responsible for maintaining the confidentiality of your login credentials</li><li>You must notify administrators immediately of any unauthorized access</li><li>You agree to use strong passwords and enable security features when available</li><li>You are liable for all activities that occur under your account</li></ul><p><strong>Authorized Use:</strong></p><ul><li>Access is granted only to authorized personnel</li><li>You may only access data and functions appropriate to your assigned role</li><li>Sharing of login credentials is strictly prohibited</li><li>You must comply with your organization\'s asset management policies</li></ul><h6>4. Prohibited Activities</h6><p>You agree not to:</p><ul><li>Attempt to gain unauthorized access to any part of the System</li><li>Interfere with or disrupt the System\'s operation</li><li>Use the System for any illegal or unauthorized purpose</li><li>Reverse engineer, decompile, or disassemble any part of the System</li><li>Introduce viruses, malware, or other harmful code</li><li>Access or attempt to access accounts belonging to other users</li><li>Export or share sensitive asset data without proper authorization</li></ul><h6>5. Data Accuracy and Integrity</h6><p>Users are responsible for:</p><ul><li>Ensuring the accuracy of data entered into the System</li><li>Promptly updating asset information when changes occur</li><li>Reporting discrepancies or errors to system administrators</li><li>Following established procedures for asset management</li></ul><h6>6. System Availability</h6><p>While we strive to maintain continuous service:</p><ul><li>The System may be temporarily unavailable for maintenance</li><li>We do not guarantee 100% uptime or availability</li><li>Scheduled maintenance will be announced in advance when possible</li><li>Emergency maintenance may occur without prior notice</li></ul><h6>7. Intellectual Property</h6><p>The PILAR Asset Inventory System and its contents are protected by intellectual property laws:</p><ul><li>All software, designs, and documentation remain our property</li><li>You receive a limited license to use the System for its intended purpose</li><li>You may not copy, modify, or distribute any part of the System</li><li>Your organization retains ownership of data entered into the System</li></ul><h6>8. Privacy and Data Protection</h6><p>Your privacy is important to us:</p><ul><li>Please review our Privacy Policy for details on data handling</li><li>We implement security measures to protect your information</li><li>You consent to data processing as described in our Privacy Policy</li><li>We comply with applicable data protection regulations</li></ul><h6>9. Limitation of Liability</h6><p>To the maximum extent permitted by law:</p><ul><li>We provide the System \"as is\" without warranties</li><li>We are not liable for indirect, incidental, or consequential damages</li><li>Our total liability is limited to the amount paid for System access</li><li>You agree to indemnify us against claims arising from your use of the System</li></ul><h6>10. Termination</h6><p>These terms remain in effect until terminated:</p><ul><li>Your access may be suspended or terminated for violations of these terms</li><li>You may request account termination by contacting administrators</li><li>Upon termination, you must cease all use of the System</li><li>Certain provisions of these terms survive termination</li></ul><h6>11. Changes to Terms</h6><p>We reserve the right to modify these terms:</p><ul><li>Changes will be posted on this page with an updated effective date</li><li>Continued use after changes constitutes acceptance</li><li>Material changes will be communicated to users</li><li>You should review these terms periodically</li></ul><h6>12. Governing Law</h6><p>These terms are governed by applicable local and federal laws. Any disputes will be resolved through appropriate legal channels in the jurisdiction where the System is operated.</p><p><br></p><h6>13. Contact Information</h6><p>For questions about these Terms of Service, please contact:</p><p><strong>PILAR Asset Inventory System Administrator</strong></p><p>Email: <span style=\"color: rgb(0, 29, 53);\">pilarsor.mayor@gmail.com</span></p><p>Phone: <span style=\"color: rgb(0, 29, 53);\">0909 899 6012</span></p><p>Address: <strong>LGU-Pilar Complex, Calongay, Pilar, Sorsogon</strong></p><p><br></p><p><strong>Important:</strong> By using the PILAR Asset Inventory System, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service and our Privacy Policy.</p>', '1', '2025-09-29', '2025-09-29 03:12:21', 1, 1, '2025-09-29 03:12:21');

-- --------------------------------------------------------

--
-- Table structure for table `legal_document_history`
--

CREATE TABLE `legal_document_history` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `document_type` enum('privacy_policy','terms_of_service') NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext DEFAULT NULL,
  `version` varchar(50) NOT NULL,
  `effective_date` date NOT NULL,
  `updated_by` int(11) NOT NULL,
  `change_reason` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `mr_details`
--

CREATE TABLE `mr_details` (
  `mr_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `office_location` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `model_no` varchar(100) DEFAULT NULL,
  `serial_no` varchar(100) DEFAULT NULL,
  `serviceable` tinyint(1) DEFAULT 0,
  `unserviceable` tinyint(1) DEFAULT 0,
  `unit_quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `acquisition_date` date NOT NULL,
  `acquisition_cost` decimal(12,2) NOT NULL,
  `person_accountable` varchar(255) DEFAULT NULL,
  `end_user` varchar(255) DEFAULT NULL,
  `acquired_date` date DEFAULT NULL,
  `counted_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `asset_id` int(11) DEFAULT NULL,
  `inventory_tag` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mr_details`
--

INSERT INTO `mr_details` (`mr_id`, `item_id`, `office_location`, `description`, `model_no`, `serial_no`, `serviceable`, `unserviceable`, `unit_quantity`, `unit`, `acquisition_date`, `acquisition_cost`, `person_accountable`, `end_user`, `acquired_date`, `counted_date`, `created_at`, `asset_id`, `inventory_tag`) VALUES
(1, 1, 'Supply Office', 'Wheel Chair', '', '', 0, 1, 1.00, 'unit', '2025-09-29', 6650.00, 'Madison Brooks', 'John Kenneth Litana', '2025-09-29', '2025-09-29', '2025-09-29 12:23:42', 8, 'PS-5S-03-F02-01-01'),
(2, NULL, 'Supply Office', 'Notebook i7', '', '', 1, 0, 1.00, 'unit', '2025-09-29', 80245.00, 'Sophia Martinez', 'Angela Rizal', '0000-00-00', '0000-00-00', '2025-09-30 02:23:42', 38, 'PS-5S-03-F02-01-01'),
(3, 2, 'Supply Office', 'Laptop AMD Ryzen', 'Latitude 5430', 'SN-DC-2025-0001', 0, 1, 1.00, 'unit', '2025-09-30', 45000.00, 'Daniel Wilson', 'Roberto Cruz', '2025-09-30', '2025-09-30', '2025-09-30 02:48:41', 39, 'PS-5S-03-F02-01-01'),
(4, NULL, 'Supply Office', 'Laptop Acer Aspire 7', 'Aspire 7', 'SN-A7-2025-00001', 1, 0, 1.00, 'unit', '2025-09-30', 50000.00, 'Aaron Griffin', 'John Kenneth Litana', '0000-00-00', '0000-00-00', '2025-09-30 05:06:19', 43, 'PS-5S-03-F02-01-01'),
(5, 3, 'Supply Office', 'Grass cutter', '', '', 1, 0, 1.00, 'unit', '2025-09-30', 18000.00, 'Olivia Thomas', 'John Kenneth Litana', '2025-10-01', '2025-10-01', '2025-10-01 11:14:57', 44, 'PS-5S-03-F02-01-01'),
(6, 4, 'Supply Office', 'Power Drill (corded)', '', '', 1, 0, 1.00, 'unit', '2025-09-30', 3500.00, 'Leo Peterson', 'John Legend', '2025-10-02', '2025-10-02', '2025-10-02 05:06:55', 46, 'PS-5S-03-F02-01-01'),
(7, 7, 'OMM', 'conference table', 'Mesh Back', 'SN-DC-2025-00013243', 0, 1, 1.00, 'unit', '2025-10-02', 10000.00, 'Amelia Lewis', 'Jake Paul', '2025-10-02', '2025-10-02', '2025-10-02 10:03:43', 53, 'PS-5S-03-F02-01-01'),
(8, 6, 'OMM', 'Conference Table', 'Mesh Back', 'SN-DC-2025-0001324347', 1, 0, 1.00, 'unit', '2025-10-02', 42000.00, 'David Anderson', 'John Legend', '2025-10-02', '2025-10-02', '2025-10-02 10:41:40', 52, 'PS-5S-03-F02-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `message` text NOT NULL,
  `related_entity_type` varchar(50) DEFAULT NULL,
  `related_entity_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_types`
--

CREATE TABLE `notification_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` int(11) NOT NULL,
  `office_name` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `head_user_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`id`, `office_name`, `icon`, `head_user_id`, `description`, `created_at`, `updated_at`) VALUES
(1, 'MPDC', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(2, 'IT Office', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(3, 'OMASS', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(4, 'Supply Office', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(5, 'OMAD', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(7, 'RHU Office', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(9, 'Main', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(11, 'OMSWD', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(13, 'OBAC', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(14, 'COA', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(15, 'COMELEC', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(16, 'CSOLAR', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(17, 'DILG', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(18, 'MENRU', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(19, 'GAD', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(20, 'GS-Motorpool', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(21, 'ABC', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(22, 'SEF-DEPED', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(23, 'HRMO', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(24, 'KALAHI', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(25, 'LIBRARY', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(26, 'OMAC', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(27, 'OMA', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(28, 'OMBO', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(29, 'MCR', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(30, 'MDRRMO', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(31, 'OME', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(32, 'MHO', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(33, 'OMM', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(34, 'MTC', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(35, 'MTO-PORT-MARKET', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(36, 'NCDC', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(37, 'OSCA', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(38, 'PAO', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(39, 'PiCC', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(40, 'PIHC', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(41, 'PIO-PESO', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(42, 'PNP', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(43, 'SB', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(44, 'SB-SEC', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(45, 'SK', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(46, 'TOURISM', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(47, 'OVM', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(48, 'BPLO', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32'),
(49, '7K', NULL, NULL, NULL, '2025-09-29 15:03:32', '2025-09-29 15:03:32');

-- --------------------------------------------------------

--
-- Stand-in structure for view `overdue_items`
-- (See below for the actual view)
--
CREATE TABLE `overdue_items` (
`id` int(11)
,`borrower_name` varchar(100)
,`asset_name` varchar(100)
,`quantity` int(11)
,`approved_at` datetime
,`days_borrowed` int(7)
,`office_name` varchar(100)
);

-- --------------------------------------------------------

--
-- Table structure for table `par_form`
--

CREATE TABLE `par_form` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `office_id` int(11) DEFAULT NULL,
  `received_by_name` varchar(255) DEFAULT NULL,
  `issued_by_name` varchar(255) DEFAULT NULL,
  `position_office_left` varchar(100) DEFAULT NULL,
  `position_office_right` varchar(100) DEFAULT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `entity_name` varchar(255) NOT NULL,
  `fund_cluster` varchar(100) NOT NULL,
  `par_no` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_received_left` date DEFAULT NULL,
  `date_received_right` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `par_form`
--

INSERT INTO `par_form` (`id`, `form_id`, `office_id`, `received_by_name`, `issued_by_name`, `position_office_left`, `position_office_right`, `header_image`, `entity_name`, `fund_cluster`, `par_no`, `created_at`, `date_received_left`, `date_received_right`) VALUES
(1, 3, 33, NULL, NULL, '', '', '1759151204_Screenshot 2025-09-29 180628.png', '', '', 'PAR-0001', '2025-09-29 13:06:44', NULL, NULL),
(2, 0, 4, 'MICHAEL JACKSON', 'MARK JAYSON NAMIA', 'OFFICER', 'PROPERTY CUSTODIAN', '1759151204_Screenshot 2025-09-29 180628.png', 'LGU PILAR', '', 'PAR-2025-0001', '2025-09-29 13:24:35', '2025-09-29', '2025-09-29'),
(3, 0, 7, 'MICHAEL JACKSON', 'MARK JAYSON NAMIA', 'OFFICER', 'PROPERTY CUSTODIAN', '1759151204_Screenshot 2025-09-29 180628.png', 'RHU', 'FC-2025-001', 'LGU-PAR-2025-0001', '2025-10-01 15:00:32', '0000-00-00', '0000-00-00'),
(4, 0, 7, 'MICHAEL JACKSON', 'MARK JAYSON NAMIA', 'OFFICER', 'PROPERTY CUSTODIAN', '1759151204_Screenshot 2025-09-29 180628.png', 'RHU', 'FC-2025-002', 'LGU-PAR-2025-0009', '2025-10-02 10:02:16', '0000-00-00', '0000-00-00'),
(5, 0, 7, 'MICHAEL JACKSON', 'MARK JAYSON NAMIA', 'OFFICER', 'PROPERTY CUSTODIAN', '1759151204_Screenshot 2025-09-29 180628.png', 'LGU PILAR', 'FC-2025-0021', 'PAR-00051', '2025-10-02 12:37:37', '0000-00-00', '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `par_items`
--

CREATE TABLE `par_items` (
  `item_id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `property_no` varchar(100) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `par_items`
--

INSERT INTO `par_items` (`item_id`, `form_id`, `asset_id`, `quantity`, `unit`, `description`, `property_no`, `date_acquired`, `unit_price`, `amount`) VALUES
(1, 2, 38, 1, 'unit', 'Notebook i7', 'SPO21A-2', '2025-09-29', 80245.00, 80245.00),
(2, 3, 49, 2, 'unit', 'HP Pavilion', 'LGU-2025-ICT-0001', '2025-10-01', 53000.00, 106000.00),
(3, 3, 51, 1, 'unit', 'Medical X-Ray Machine', 'XR-0078', '2025-10-01', 120000.00, 120000.00),
(4, 4, 54, 1, 'unit', 'Laptop i7', 'PROP-0009', '2025-10-02', 70000.00, 70000.00),
(5, 5, 57, 1, 'unit', 'Hilux Van', 'LGU-2025-ICT-00013', '2025-10-02', 2000000.00, 2000000.00);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permission_audit_log`
--

CREATE TABLE `permission_audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL COMMENT 'User whose permissions changed',
  `changed_by` int(11) NOT NULL COMMENT 'Admin who made the change',
  `action` enum('GRANT','REVOKE','ROLE_CHANGE') NOT NULL,
  `permission_id` int(11) DEFAULT NULL COMMENT 'Permission that was changed',
  `old_value` varchar(100) DEFAULT NULL,
  `new_value` varchar(100) DEFAULT NULL,
  `reason` text DEFAULT NULL COMMENT 'Reason for permission change',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Audit trail for permission changes';

-- --------------------------------------------------------

--
-- Table structure for table `permission_levels`
--

CREATE TABLE `permission_levels` (
  `id` int(11) NOT NULL,
  `level_name` varchar(50) NOT NULL COMMENT 'Level name (none, view, edit, delete, approve, manage)',
  `level_weight` int(11) NOT NULL COMMENT 'Weight for hierarchy (1=lowest, 5=highest)',
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Permission level hierarchy';

--
-- Dumping data for table `permission_levels`
--

INSERT INTO `permission_levels` (`id`, `level_name`, `level_weight`, `description`, `created_at`) VALUES
(1, 'none', 0, 'No access to module', '2025-10-03 02:25:48'),
(2, 'view', 1, 'Can view/read data only', '2025-10-03 02:25:48'),
(3, 'edit', 2, 'Can view and create/edit data', '2025-10-03 02:25:48'),
(4, 'delete', 3, 'Can view, edit, and delete data', '2025-10-03 02:25:48'),
(5, 'approve', 4, 'Can approve/reject actions (for workflows)', '2025-10-03 02:25:48'),
(6, 'manage', 5, 'Full control including permissions management', '2025-10-03 02:25:48');

-- --------------------------------------------------------

--
-- Table structure for table `red_tags`
--

CREATE TABLE `red_tags` (
  `id` int(11) NOT NULL,
  `red_tag_number` varchar(20) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `iirup_id` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `tagged_by` int(11) NOT NULL,
  `item_location` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `removal_reason` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `status` enum('Pending','Completed','Cancelled') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `red_tags`
--

INSERT INTO `red_tags` (`id`, `red_tag_number`, `asset_id`, `iirup_id`, `date_received`, `tagged_by`, `item_location`, `description`, `removal_reason`, `action`, `status`, `created_at`, `updated_at`) VALUES
(1, 'PS-5S-03-F01-01-01', 39, 2, '2025-09-30', 17, 'Supply Office', 'Laptop AMD Ryzen (PN-2019-05-02-0001-01)', 'Broken', 'For Disposal', 'Pending', '2025-09-30 03:04:15', '2025-09-30 03:04:15'),
(2, 'PS-5S-03-F01-01-01', 53, 3, '2025-10-02', 17, 'Supply Office', 'conference table (PN-2019-05-02-0001-01)', 'Broken', 'For Disposal', 'Pending', '2025-10-02 11:23:27', '2025-10-02 11:23:27');

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_used` timestamp NULL DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `report_generation_settings`
--

CREATE TABLE `report_generation_settings` (
  `id` int(11) NOT NULL,
  `frequency` enum('weekly','monthly','daily') NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `day_of_month` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_generation_settings`
--

INSERT INTO `report_generation_settings` (`id`, `frequency`, `day_of_week`, `day_of_month`) VALUES
(1, 'weekly', 'Monday', 3),
(16, 'weekly', 'Monday', 3);

-- --------------------------------------------------------

--
-- Table structure for table `report_templates`
--

CREATE TABLE `report_templates` (
  `id` int(11) NOT NULL,
  `template_name` varchar(255) NOT NULL,
  `header_html` text DEFAULT NULL,
  `subheader_html` text DEFAULT NULL,
  `footer_html` text DEFAULT NULL,
  `left_logo_path` varchar(255) DEFAULT NULL,
  `right_logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `report_templates`
--

INSERT INTO `report_templates` (`id`, `template_name`, `header_html`, `subheader_html`, `footer_html`, `left_logo_path`, `right_logo_path`, `created_at`, `updated_at`, `created_by`, `updated_by`) VALUES
(2, 'Inventory Custodian Slip', '<div style=\"font-family:\"Times New Roman\"; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:;\"=\"\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:left;\"=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:;\"=\"\">Hello World<div><b>inventory report</b></div><div><i>as of&nbsp;$dynamic_month&nbsp;$dynamic_year</i></div></div></div></div></div></div>', '<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:;\"=\"\"><div style=\"font-family:; font-size:; text-align:left;\"><div style=\"font-family:; font-size:; text-align:;\">name:&nbsp;[blank]&nbsp; position:&nbsp;[blank]</div></div></div></div></div>', '<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:left;\"><div style=\"font-family:; font-size:; text-align:;\">signature:&nbsp;[blank]</div></div></div></div></div>', '../uploads/6867dfb04e6d4_Laptop Dell XPS 15_QR (1).png', NULL, '2025-07-04 14:05:36', '2025-07-08 03:25:01', 17, 17),
(3, 'Property Acknowledgement Receipt', '<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Arial; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:16px;=\"\" text-align:start;\"=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" \"=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" \"=\"\">REPUBLIC OF THE PHILIPPINES<div><b>PROPERTY ACKNOWLEDGEMENT RECEIPT</b></div><div><i>As of&nbsp;$dynamic_month&nbsp;$dynamic_year</i></div></div></div></div></div></div>', '<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Poppins, sans-serif; font-size:16px; text-align:start;\"><div style=\"  \"><div style=\"  \">name:&nbsp;[blank]&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;subject:&nbsp;[blank]</div></div></div></div></div>', '<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Poppins, sans-serif; font-size:16px; text-align:start;\"><div style=\"  \"><div style=\"  \">signature:&nbsp;[blank]<div>property:&nbsp;[blank]</div></div></div></div></div></div>', '../uploads/68693f21f1b44_logo.jpg', '../uploads/68693f21f245d_logo.jpg', '2025-07-05 15:05:05', '2025-07-08 01:50:38', 17, 17),
(4, 'Inventory Transfer Report', '<div style=\"\"><div style=\"\"><div style=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" \"=\"\">REPUBLIC OF THE PHILIPPINES<div><b>INVENTORY TRANSFER REPORT</b></div><div><i>As of&nbsp;</i>&nbsp;$dynamic_month&nbsp;$dynamic_year</div></div></div></div></div>', '<div style=\"\"><div style=\"\"><div style=\"\"><div style=\"  \">name:&nbsp;[blank]&nbsp;</div></div></div></div>', '<div style=\"\"><div style=\"\"><div style=\"\"><div style=\"  \">signature:&nbsp;[blank]</div></div></div></div>', '../uploads/686942fdd82cc_logo.jpg', '../uploads/right_1752067662_37.png', '2025-07-05 15:21:33', '2025-07-07 12:54:56', 17, 17),
(5, 'Memorandum Report', '<div style=\"font-family:Tahoma; font-size:16px; text-align:center;\">\n    <b>Republic of the Philippines</b><br>\n    Municipality of Pilar\n</div>\n', '<div style=\"font-size:12px; text-align:right;\">\n    Prepared: $DYNAMIC_MONTH $DYNAMIC_YEAR\n</div>\n\\', '<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Tahoma; font-size:12px; text-align:left;\">signature:&nbsp;[blank]</div></div>', '../uploads/686944de212f8_logo.jpg', '../uploads/686944de218f6_logo.jpg', '2025-07-05 15:29:34', '2025-07-08 03:25:29', 17, 17),
(30, 'sample 3', '<div style=\"font-size: 14px;\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"font-family: Tahoma;\"><div style=\"\">Republic of the Philippines<div style=\"\"><div style=\"font-family:; font-size:; text-align:;\"></div></div><div><b>Municipality of Pilar</b></div><div>Province of Sorsogon</div></div></div></div></div></div></div></div>', '<div style=\"font-size: 18px; font-family: Georgia;\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"text-align: left; font-family: Georgia; font-size: 12px;\"><div>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Annex A.3</div>Entity name:<u>LGU-PILAR/OMSWD</u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Fund Cluster<div>From Acountable Officer/Agency Fund Cluster MARK JAYSON NAMIA/LGU-PILAR-OMPDC/OFFICE SUPPLY&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;ITR No: 24-09-1</div><div>To Accountable&nbsp; Offices/Agency/Fund Cluster: VLADIMIR ABOGADO&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Date: 3/12/25&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</div><div>Transfer Type: (Check only)</div><div>[blank]donation&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;[blank]relocate</div><div>[blank]reaasignment&nbsp;[blank]others (specify)[blank]<br><div style=\"\"><div style=\"font-family:; font-size:; text-align:;\"></div></div><div><u><br></u></div></div></div></div></div></div></div></div></div>', '<div style=\"font-family: Georgia; font-size: 18px;\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"text-align: left;\"><div style=\"\"><div style=\"font-size: 12px;\">[blank][blank]&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; [blank]&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;[blank]<br><div style=\"\"><div style=\"font-family:; font-size:; text-align:;\"></div></div><div>&nbsp; &nbsp; &nbsp; name of accountable officer&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (designation)&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; department office&nbsp; &nbsp; &nbsp;</div></div></div></div></div></div></div></div>', '../uploads/686f1e3ad26b5_PILAR LOGO TRANSPARENT.png', '', '2025-07-09 13:35:40', '2025-07-09 13:35:40', 17, 17),
(31, 'sample 4', '<div style=\"font-family:; font-size:; text-align:;\">yuebobceuob</div>', '<div style=\"font-family:Arial; font-size:12px; text-align:left;\"><table class=\"table table-bordered\"><tbody><tr><td>hibicbocjsclsjcdkjdcdjcbjdbcdjb</td><td>hellolcdnckdckdcndkcndlkcndlnc</td><td>xsjhcbsjkcb</td></tr><tr><td>[blank]</td><td>[blank]</td><td>kjsbcjscjcsc</td></tr></tbody></table></div>', '<div style=\"font-family:; font-size:; text-align:;\"><br><table class=\"table table-bordered\"><tbody><tr><td>[blank]kcnckdnckna;cndk</td><td><br></td><td>helljdowidwio</td></tr><tr><td>gievi bcsjbs</td><td>[blank]</td><td>[blank]</td></tr></tbody></table></div>', '../uploads/686fdcf7c9274_logo.jpg', '../uploads/686fdcf7c9cf8_38.png', '2025-07-10 15:32:07', '2025-07-10 15:32:07', 17, 17),
(32, 'SAMPLE 5 BORDER', '<div style=\"font-family:; font-size:; text-align:;\">HEADER</div>', '<div style=\"font-family:; font-size:; text-align:;\">HELLO<table class=\"table\"><tbody><tr><td>[blank]NAME NO BORDER</td><td>[blank]</td></tr></tbody></table></div>', '<div style=\"font-family:; font-size:; text-align:;\">HELLO WITH BORDER</div>', NULL, NULL, '2025-07-10 15:35:29', '2025-07-10 15:35:29', 17, 17),
(33, 'sample 6', '<div style=\"font-family:\"Times New Roman\"; font-size:; text-align:;\">republic of the philippines</div>', '<div style=\"font-family:; font-size:12px; text-align:;\"><table class=\"table\"><tbody><tr><td>name[blank]</td><td>date[blank]</td></tr></tbody></table></div>', '<div style=\"font-family:; font-size:12px; text-align:;\"><table class=\"table\"><tbody><tr><td>signature[blank]</td><td>date[blank]</td></tr></tbody></table></div>', '../uploads/6870b954756b1_logo.jpg', '../uploads/6870b95475f65_PILAR LOGO TRANSPARENT.png', '2025-07-11 07:12:20', '2025-07-11 07:12:20', 17, 17);

-- --------------------------------------------------------

--
-- Table structure for table `returned_assets`
--

CREATE TABLE `returned_assets` (
  `id` int(11) NOT NULL,
  `borrow_request_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `return_date` datetime NOT NULL,
  `condition_on_return` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `office_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `returned_assets`
--

INSERT INTO `returned_assets` (`id`, `borrow_request_id`, `asset_id`, `user_id`, `return_date`, `condition_on_return`, `remarks`, `office_id`) VALUES
(7, 13, 18, 17, '2025-04-20 19:58:58', 'Good', 'Returned', 9),
(8, 1, 2, 12, '2025-04-20 20:28:03', 'Good', 'Returned', 4);

-- --------------------------------------------------------

--
-- Table structure for table `ris_form`
--

CREATE TABLE `ris_form` (
  `id` int(11) NOT NULL,
  `form_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `division` varchar(255) NOT NULL,
  `responsibility_center` varchar(255) NOT NULL,
  `responsibility_code` varchar(255) DEFAULT NULL,
  `ris_no` varchar(100) NOT NULL,
  `sai_no` varchar(100) NOT NULL,
  `date` date NOT NULL,
  `approved_by_name` varchar(255) NOT NULL,
  `approved_by_designation` varchar(255) NOT NULL,
  `approved_by_date` date NOT NULL,
  `received_by_name` varchar(255) NOT NULL,
  `received_by_designation` varchar(255) NOT NULL,
  `received_by_date` date NOT NULL,
  `footer_date` date NOT NULL,
  `reason_for_transfer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `requested_by_name` varchar(255) DEFAULT NULL,
  `requested_by_designation` varchar(255) DEFAULT NULL,
  `requested_by_date` date DEFAULT NULL,
  `issued_by_name` varchar(255) DEFAULT NULL,
  `issued_by_designation` varchar(255) DEFAULT NULL,
  `issued_by_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ris_form`
--

INSERT INTO `ris_form` (`id`, `form_id`, `office_id`, `header_image`, `division`, `responsibility_center`, `responsibility_code`, `ris_no`, `sai_no`, `date`, `approved_by_name`, `approved_by_designation`, `approved_by_date`, `received_by_name`, `received_by_designation`, `received_by_date`, `footer_date`, `reason_for_transfer`, `created_at`, `requested_by_name`, `requested_by_designation`, `requested_by_date`, `issued_by_name`, `issued_by_designation`, `issued_by_date`) VALUES
(1, 6, 4, '1759143582_Screenshot_2025-09-19_112710.png', '', '', '', 'RIS-2025-0001', 'SAI-2025-0001', '2025-09-29', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-29', 'MICHAEL JACKSON', 'CLERK', '2025-09-29', '2025-09-29', 'For air freshening in supply office', '2025-09-29 10:59:42', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-29', 'MARK JAYSON NAMIA', 'SUPPLY OFFICER', '2025-09-29'),
(2, 6, 4, '1759143582_Screenshot_2025-09-19_112710.png', '', '', '', 'RIS-2025-0001', 'SAI-2025-0002', '2025-09-29', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-29', 'MICHAEL JACKSON', 'CLERK', '2025-09-29', '2025-09-29', 'For air freshening in supply office', '2025-09-29 11:53:18', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-29', 'MARK JAYSON NAMIA', 'SUPPLY OFFICER', '2025-09-29'),
(3, 6, 4, '1759143582_Screenshot_2025-09-19_112710.png', '', '', '', 'RIS-2025-0001', 'SAI-2025-0003', '2025-09-29', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-29', 'MICHAEL JACKSON', 'CLERK', '2025-09-29', '2025-09-29', 'For air freshening in supply office', '2025-09-29 11:56:18', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-29', 'MARK JAYSON NAMIA', 'SUPPLY OFFICER', '2025-09-29'),
(4, 6, 4, '1759143582_Screenshot_2025-09-19_112710.png', '', '', '', 'RIS-2025-0001', 'SAI-2025-0004', '2025-09-29', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-29', 'MICHAEL JACKSON', 'CLERK', '2025-09-29', '2025-09-29', 'For air freshening in supply office', '2025-09-29 11:57:55', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-29', 'MARK JAYSON NAMIA', 'SUPPLY OFFICER', '2025-09-29'),
(5, 6, 4, '1759143582_Screenshot_2025-09-19_112710.png', '', '', '', 'RIS-2025-0001', 'SAI-2025-0005', '2025-09-29', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-29', 'MICHAEL JACKSON', 'CLERK', '2025-09-29', '2025-09-29', 'For air freshening in supply office', '2025-09-29 12:01:38', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-29', 'MARK JAYSON NAMIA', 'SUPPLY OFFICER', '2025-09-29'),
(6, 6, 4, '1759143582_Screenshot_2025-09-19_112710.png', '', '', '', 'RIS-2025-0001', 'SAI-2025-0006', '2025-09-30', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-30', 'MICHAEL JACKSON', 'CLERK', '2025-09-30', '2025-09-29', 'For supplies in supply office', '2025-09-30 03:09:02', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-30', 'MARK JAYSON NAMIA', 'SUPPLY OFFICER', '2025-09-30'),
(7, 6, 4, '1759143582_Screenshot_2025-09-19_112710.png', '', '', '', 'RIS-2025-0001', 'SAI-2025-0007', '2025-10-01', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-10-01', 'MICHAEL JACKSON', 'CLERK', '2025-10-01', '2025-09-29', 'For supplies in supply office', '2025-10-01 05:53:42', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-10-01', 'MARK JAYSON NAMIA', 'SUPPLY OFFICER', '2025-10-01');

-- --------------------------------------------------------

--
-- Table structure for table `ris_items`
--

CREATE TABLE `ris_items` (
  `id` int(11) NOT NULL,
  `ris_form_id` int(11) NOT NULL,
  `stock_no` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ris_items`
--

INSERT INTO `ris_items` (`id`, `ris_form_id`, `stock_no`, `unit`, `description`, `quantity`, `price`, `total`) VALUES
(1, 1, '', '17', 'Air freshener (Spray)', 2, 390.00, 780.00),
(2, 2, '', '17', 'Air freshener (Ambi Pur)', 2, 390.00, 780.00),
(3, 3, '', '17', 'Alcohol', 2, 250.00, 500.00),
(4, 4, '', '2', 'Ballpen HBW Matrix 50\'s black', 3, 223.63, 670.89),
(5, 5, '', '1', 'Battey AA', 9, 85.00, 765.00),
(6, 5, '', '1', 'Battery AAA', 9, 75.00, 675.00),
(7, 5, '', '22', 'Bond paper long S-20', 22, 330.00, 7260.00),
(8, 6, '', '2', 'Ballpen ', 3, 340.00, 1020.00),
(9, 6, '', '22', 'Bond paper', 2, 300.00, 600.00),
(10, 6, '', '1', 'Tissue', 3, 50.00, 150.00),
(11, 7, '1', '1', 'Ink', 10, 58.00, 580.00);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'SYSTEM_ADMIN', 'Has full access to all system features and configurations', '2025-10-03 05:05:48', '2025-10-03 05:05:48'),
(2, 'MAIN_ADMIN', 'Can manage assets, users, and basic system settings', '2025-10-03 05:05:48', '2025-10-03 05:05:48'),
(3, 'MAIN_EMPLOYEE', 'Can view and borrow assets', '2025-10-03 05:05:48', '2025-10-03 05:05:48'),
(4, 'MAIN_USER', 'Basic user with limited access', '2025-10-03 05:05:48', '2025-10-03 05:05:48');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role` enum('MAIN_ADMIN','SYSTEM_ADMIN','OFFICE_ADMIN','MAIN_USER') NOT NULL COMMENT 'Role name',
  `permission_id` int(11) NOT NULL COMMENT 'Permission ID',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Default permissions for each role';

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role`, `permission_id`, `created_at`) VALUES
(1, 'MAIN_ADMIN', 47, '2025-10-03 02:25:48'),
(2, 'MAIN_ADMIN', 48, '2025-10-03 02:25:48'),
(3, 'MAIN_ADMIN', 49, '2025-10-03 02:25:48'),
(4, 'MAIN_ADMIN', 50, '2025-10-03 02:25:48'),
(5, 'MAIN_ADMIN', 1, '2025-10-03 02:25:48'),
(6, 'MAIN_ADMIN', 2, '2025-10-03 02:25:48'),
(7, 'MAIN_ADMIN', 3, '2025-10-03 02:25:48'),
(8, 'MAIN_ADMIN', 4, '2025-10-03 02:25:48'),
(9, 'MAIN_ADMIN', 5, '2025-10-03 02:25:48'),
(10, 'MAIN_ADMIN', 51, '2025-10-03 02:25:48'),
(11, 'MAIN_ADMIN', 52, '2025-10-03 02:25:48'),
(12, 'MAIN_ADMIN', 53, '2025-10-03 02:25:48'),
(13, 'MAIN_ADMIN', 6, '2025-10-03 02:25:48'),
(14, 'MAIN_ADMIN', 7, '2025-10-03 02:25:48'),
(15, 'MAIN_ADMIN', 8, '2025-10-03 02:25:48'),
(16, 'MAIN_ADMIN', 9, '2025-10-03 02:25:48'),
(17, 'MAIN_ADMIN', 10, '2025-10-03 02:25:48'),
(18, 'MAIN_ADMIN', 59, '2025-10-03 02:25:48'),
(19, 'MAIN_ADMIN', 60, '2025-10-03 02:25:48'),
(20, 'MAIN_ADMIN', 21, '2025-10-03 02:25:48'),
(21, 'MAIN_ADMIN', 22, '2025-10-03 02:25:48'),
(22, 'MAIN_ADMIN', 23, '2025-10-03 02:25:48'),
(23, 'MAIN_ADMIN', 24, '2025-10-03 02:25:48'),
(24, 'MAIN_ADMIN', 29, '2025-10-03 02:25:48'),
(25, 'MAIN_ADMIN', 30, '2025-10-03 02:25:48'),
(26, 'MAIN_ADMIN', 31, '2025-10-03 02:25:48'),
(27, 'MAIN_ADMIN', 32, '2025-10-03 02:25:48'),
(28, 'MAIN_ADMIN', 33, '2025-10-03 02:25:48'),
(29, 'MAIN_ADMIN', 11, '2025-10-03 02:25:48'),
(30, 'MAIN_ADMIN', 12, '2025-10-03 02:25:48'),
(31, 'MAIN_ADMIN', 13, '2025-10-03 02:25:48'),
(32, 'MAIN_ADMIN', 14, '2025-10-03 02:25:48'),
(33, 'MAIN_ADMIN', 15, '2025-10-03 02:25:48'),
(34, 'MAIN_ADMIN', 61, '2025-10-03 02:25:48'),
(35, 'MAIN_ADMIN', 62, '2025-10-03 02:25:48'),
(36, 'MAIN_ADMIN', 25, '2025-10-03 02:25:48'),
(37, 'MAIN_ADMIN', 26, '2025-10-03 02:25:48'),
(38, 'MAIN_ADMIN', 27, '2025-10-03 02:25:48'),
(39, 'MAIN_ADMIN', 28, '2025-10-03 02:25:48'),
(40, 'MAIN_ADMIN', 44, '2025-10-03 02:25:48'),
(41, 'MAIN_ADMIN', 45, '2025-10-03 02:25:48'),
(42, 'MAIN_ADMIN', 46, '2025-10-03 02:25:48'),
(43, 'MAIN_ADMIN', 34, '2025-10-03 02:25:48'),
(44, 'MAIN_ADMIN', 35, '2025-10-03 02:25:48'),
(45, 'MAIN_ADMIN', 36, '2025-10-03 02:25:48'),
(46, 'MAIN_ADMIN', 37, '2025-10-03 02:25:48'),
(47, 'MAIN_ADMIN', 38, '2025-10-03 02:25:48'),
(48, 'MAIN_ADMIN', 39, '2025-10-03 02:25:48'),
(49, 'MAIN_ADMIN', 40, '2025-10-03 02:25:48'),
(50, 'MAIN_ADMIN', 41, '2025-10-03 02:25:48'),
(51, 'MAIN_ADMIN', 42, '2025-10-03 02:25:48'),
(52, 'MAIN_ADMIN', 43, '2025-10-03 02:25:48'),
(53, 'MAIN_ADMIN', 54, '2025-10-03 02:25:48'),
(54, 'MAIN_ADMIN', 55, '2025-10-03 02:25:48'),
(55, 'MAIN_ADMIN', 56, '2025-10-03 02:25:48'),
(56, 'MAIN_ADMIN', 57, '2025-10-03 02:25:48'),
(57, 'MAIN_ADMIN', 58, '2025-10-03 02:25:48'),
(58, 'MAIN_ADMIN', 16, '2025-10-03 02:25:48'),
(59, 'MAIN_ADMIN', 17, '2025-10-03 02:25:48'),
(60, 'MAIN_ADMIN', 18, '2025-10-03 02:25:48'),
(61, 'MAIN_ADMIN', 19, '2025-10-03 02:25:48'),
(62, 'MAIN_ADMIN', 20, '2025-10-03 02:25:48'),
(64, 'SYSTEM_ADMIN', 52, '2025-10-03 02:25:48'),
(65, 'SYSTEM_ADMIN', 53, '2025-10-03 02:25:48'),
(66, 'SYSTEM_ADMIN', 51, '2025-10-03 02:25:48'),
(67, 'SYSTEM_ADMIN', 60, '2025-10-03 02:25:48'),
(68, 'SYSTEM_ADMIN', 59, '2025-10-03 02:25:48'),
(69, 'SYSTEM_ADMIN', 62, '2025-10-03 02:25:48'),
(70, 'SYSTEM_ADMIN', 61, '2025-10-03 02:25:48'),
(71, 'SYSTEM_ADMIN', 27, '2025-10-03 02:25:48'),
(72, 'SYSTEM_ADMIN', 26, '2025-10-03 02:25:48'),
(73, 'SYSTEM_ADMIN', 28, '2025-10-03 02:25:48'),
(74, 'SYSTEM_ADMIN', 25, '2025-10-03 02:25:48'),
(75, 'SYSTEM_ADMIN', 41, '2025-10-03 02:25:48'),
(76, 'SYSTEM_ADMIN', 39, '2025-10-03 02:25:48'),
(77, 'SYSTEM_ADMIN', 56, '2025-10-03 02:25:48'),
(78, 'SYSTEM_ADMIN', 55, '2025-10-03 02:25:48'),
(79, 'SYSTEM_ADMIN', 58, '2025-10-03 02:25:48'),
(80, 'SYSTEM_ADMIN', 57, '2025-10-03 02:25:48'),
(81, 'SYSTEM_ADMIN', 54, '2025-10-03 02:25:48'),
(82, 'SYSTEM_ADMIN', 18, '2025-10-03 02:25:48'),
(83, 'SYSTEM_ADMIN', 17, '2025-10-03 02:25:48'),
(84, 'SYSTEM_ADMIN', 20, '2025-10-03 02:25:48'),
(85, 'SYSTEM_ADMIN', 19, '2025-10-03 02:25:48'),
(86, 'SYSTEM_ADMIN', 16, '2025-10-03 02:25:48'),
(95, 'OFFICE_ADMIN', 49, '2025-10-03 02:25:48'),
(96, 'OFFICE_ADMIN', 48, '2025-10-03 02:25:48'),
(97, 'OFFICE_ADMIN', 47, '2025-10-03 02:25:48'),
(98, 'OFFICE_ADMIN', 3, '2025-10-03 02:25:48'),
(99, 'OFFICE_ADMIN', 2, '2025-10-03 02:25:48'),
(100, 'OFFICE_ADMIN', 4, '2025-10-03 02:25:48'),
(101, 'OFFICE_ADMIN', 1, '2025-10-03 02:25:48'),
(102, 'OFFICE_ADMIN', 8, '2025-10-03 02:25:48'),
(103, 'OFFICE_ADMIN', 7, '2025-10-03 02:25:48'),
(104, 'OFFICE_ADMIN', 9, '2025-10-03 02:25:48'),
(105, 'OFFICE_ADMIN', 6, '2025-10-03 02:25:48'),
(106, 'OFFICE_ADMIN', 60, '2025-10-03 02:25:48'),
(107, 'OFFICE_ADMIN', 59, '2025-10-03 02:25:48'),
(108, 'OFFICE_ADMIN', 23, '2025-10-03 02:25:48'),
(109, 'OFFICE_ADMIN', 22, '2025-10-03 02:25:48'),
(110, 'OFFICE_ADMIN', 21, '2025-10-03 02:25:48'),
(111, 'OFFICE_ADMIN', 32, '2025-10-03 02:25:48'),
(112, 'OFFICE_ADMIN', 31, '2025-10-03 02:25:48'),
(113, 'OFFICE_ADMIN', 30, '2025-10-03 02:25:48'),
(114, 'OFFICE_ADMIN', 29, '2025-10-03 02:25:48'),
(115, 'OFFICE_ADMIN', 12, '2025-10-03 02:25:48'),
(116, 'OFFICE_ADMIN', 14, '2025-10-03 02:25:48'),
(117, 'OFFICE_ADMIN', 11, '2025-10-03 02:25:48'),
(118, 'OFFICE_ADMIN', 61, '2025-10-03 02:25:48'),
(119, 'OFFICE_ADMIN', 46, '2025-10-03 02:25:48'),
(120, 'OFFICE_ADMIN', 45, '2025-10-03 02:25:48'),
(121, 'OFFICE_ADMIN', 44, '2025-10-03 02:25:48'),
(122, 'OFFICE_ADMIN', 36, '2025-10-03 02:25:48'),
(123, 'OFFICE_ADMIN', 35, '2025-10-03 02:25:48'),
(124, 'OFFICE_ADMIN', 37, '2025-10-03 02:25:48'),
(125, 'OFFICE_ADMIN', 34, '2025-10-03 02:25:48'),
(126, 'OFFICE_ADMIN', 41, '2025-10-03 02:25:48'),
(127, 'OFFICE_ADMIN', 40, '2025-10-03 02:25:48'),
(128, 'OFFICE_ADMIN', 39, '2025-10-03 02:25:48'),
(158, 'MAIN_USER', 1, '2025-10-03 02:25:48'),
(159, 'MAIN_USER', 6, '2025-10-03 02:25:48'),
(160, 'MAIN_USER', 59, '2025-10-03 02:25:48'),
(161, 'MAIN_USER', 29, '2025-10-03 02:25:48'),
(162, 'MAIN_USER', 61, '2025-10-03 02:25:48'),
(163, 'MAIN_USER', 45, '2025-10-03 02:25:48'),
(164, 'MAIN_USER', 44, '2025-10-03 02:25:48');

-- --------------------------------------------------------

--
-- Table structure for table `rpcppe_form`
--

CREATE TABLE `rpcppe_form` (
  `id` int(11) NOT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `accountable_officer` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `agency_office` varchar(255) NOT NULL,
  `member_inventory` varchar(255) NOT NULL,
  `chairman_inventory` varchar(255) NOT NULL,
  `mayor` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rpcppe_form`
--

INSERT INTO `rpcppe_form` (`id`, `header_image`, `accountable_officer`, `destination`, `agency_office`, `member_inventory`, `chairman_inventory`, `mayor`, `created_at`) VALUES
(1, 'header_1755919258.png', '', '', 'OMAD Office', '', '', '', '2025-08-14 14:52:25');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `system_name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system`
--

CREATE TABLE `system` (
  `id` int(11) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `system_title` varchar(255) NOT NULL,
  `default_user_password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system`
--

INSERT INTO `system` (`id`, `logo`, `system_title`, `default_user_password`) VALUES
(1, '1759282594_logo.png', 'Pilar Inventory Management System', 'PilarINVENTORY@1');

-- --------------------------------------------------------

--
-- Table structure for table `system_info`
--

CREATE TABLE `system_info` (
  `id` int(11) NOT NULL,
  `system_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `developer_name` varchar(255) NOT NULL,
  `developer_email` varchar(255) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `credits` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_info`
--

INSERT INTO `system_info` (`id`, `system_name`, `description`, `developer_name`, `developer_email`, `version`, `credits`, `created_at`) VALUES
(1, 'Web-based Asset Inventory Management System', 'This system manages and tracks assets across different offices. It supports inventory categorization, QR code tracking, report generation, and user role-based access.', 'Walton Loneza', 'waltonloneza@example.com', '1.0', 'Developed by BU Polangui Capstone Team for the Municipality of Pilar, Sorsogon.', '2025-08-03 10:30:56');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `module` varchar(100) NOT NULL,
  `action` text NOT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `datetime` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`id`, `user_id`, `office_id`, `module`, `action`, `ip_address`, `datetime`) VALUES
(1, 4, 4, 'Asset Management', 'Added new asset: HP Laptop (ID: 4), Category: Electronics', '::1', '2025-04-06 17:48:07'),
(2, 12, 4, 'Assets', 'Added asset: Desktop Computer Set', '::1', '2025-04-21 06:35:28'),
(3, 12, 4, 'Categories', 'Added new category: Luminaires', '::1', '2025-04-21 13:28:02'),
(4, 12, 4, 'Assets', 'Added asset: Generator', '::1', '2025-04-21 14:01:52'),
(5, 12, 4, 'Categories', 'Added new category: Luminaires', '::1', '2025-04-21 14:02:08');

-- --------------------------------------------------------

--
-- Table structure for table `tag_formats`
--

CREATE TABLE `tag_formats` (
  `id` int(11) NOT NULL,
  `tag_type` varchar(50) NOT NULL,
  `format_code` varchar(100) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tag_formats`
--

INSERT INTO `tag_formats` (`id`, `tag_type`, `format_code`, `created_by`, `created_at`) VALUES
(1, 'Red Tag', 'PS-5S-03-F01-01-01', 1, '2025-09-23 16:19:49'),
(2, 'Property Tag', 'PS-5S-03-F02-01-01', 1, '2025-09-24 00:41:02'),
(3, 'Property No', 'PN-2019-05-02-0001-01', 1, '2025-09-24 04:12:50'),
(4, 'Code', 'YYYY-CODE-XXXX', 1, '2025-09-24 04:14:42');

-- --------------------------------------------------------

--
-- Table structure for table `unit`
--

CREATE TABLE `unit` (
  `id` int(11) NOT NULL,
  `unit_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `unit`
--

INSERT INTO `unit` (`id`, `unit_name`) VALUES
(1, 'pcs'),
(2, 'box'),
(3, 'set'),
(4, 'pack'),
(5, 'dozen'),
(6, 'liter'),
(7, 'milliliter'),
(8, 'kilogram'),
(9, 'gram'),
(10, 'meter'),
(11, 'centimeter'),
(12, 'inch'),
(13, 'foot'),
(14, 'yard'),
(15, 'gallon'),
(16, 'tablet'),
(17, 'bottle'),
(18, 'roll'),
(19, 'can'),
(20, 'tube'),
(21, 'unit'),
(22, 'reams');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('super_admin','admin','user','office_user','office_admin') NOT NULL DEFAULT 'user',
  `status` enum('active','inactive','deleted') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT 'default_profile.png',
  `session_timeout` int(11) DEFAULT 1800
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `fullname`, `email`, `password`, `role`, `status`, `created_at`, `reset_token`, `reset_token_expiry`, `office_id`, `profile_picture`, `session_timeout`) VALUES
(1, 'OMPDC', 'Mark Jayson Namia', 'waltielappy67@gmail.com', '$2y$10$PjQBLH0.VE3gnzvEqc9YXOhDu.wuUFpAYK1Ze/NnGOi6S3DcIdaGm', 'super_admin', 'active', '2025-04-01 13:01:47', 'f1a3abf461035dcc73348aa1789b454af62eb176488d01831ab5e0bb7d00a65b', '2025-09-28 19:27:22', NULL, 'default_profile.png', 1800),
(2, 'user2', 'Mark John', 'john2@example.com', 'hashed_password', 'user', 'active', '2025-04-03 04:31:57', NULL, NULL, 1, 'default_profile.png', 1800),
(4, 'user4', 'Steve Jobs', 'mark4@example.com', 'hashed_password', 'user', 'active', '2025-04-03 04:31:57', NULL, NULL, 3, 'default_profile.png', 1800),
(5, 'johndoe', 'Elon Musk', 'johndoe@example.com', 'password123', 'admin', 'inactive', '2025-04-03 04:45:50', NULL, NULL, 1, 'default_profile.png', 1800),
(6, 'janesmith', 'Mark Zuckerberg', 'janesmith@example.com', 'password123', 'admin', 'active', '2025-04-03 04:45:50', NULL, NULL, 2, 'default_profile.png', 1800),
(7, 'tomgreen', 'Tom Jones', 'tomgreen@example.com', 'password123', 'admin', 'active', '2025-04-03 04:45:50', NULL, NULL, 1, 'default_profile.png', 1800),
(8, 'marybrown', 'Ed Caluag', 'marybrown@example.com', 'password123', 'office_user', 'active', '2025-04-03 04:45:50', NULL, NULL, 3, 'default_profile.png', 1800),
(9, 'peterwhite', 'Peter White', 'peterwhite@example.com', 'password123', 'admin', 'active', '2025-04-03 04:45:50', NULL, NULL, 2, 'default_profile.png', 1800),
(10, 'walt', 'Walton Loneza', 'waltielappy@gmail.com', '$2y$10$j5gUPrRPP0w0REknIdYrce.l5ZItK3c5WJXX3eC2OSQHtJ/YchHey', 'admin', 'active', '2025-04-04 01:31:30', 'b5cc3402f531db55aa9a15e82108f7c5079c41eab242c994f2d78720638d13da', '2025-09-28 16:18:36', NULL, 'default_profile.png', 1800),
(12, 'walts', 'Walton Loneza', 'wjll@bicol-u.edu.ph', '$2y$10$tsOlFU9fjwi/DLRKdGkqL.aIXhKnlFxnNbA8ZoXeMbEiAhoe.sg/i', 'office_admin', 'inactive', '2025-04-07 14:13:29', NULL, NULL, 4, 'WIN_20240930_21_49_09_Pro.jpg', 1800),
(15, 'josh', 'Joshua Escano', 'jmfte@gmail.com', '$2y$10$IFmIX3WZ0YOxdf41EYzX6.IF51IKEg0bL0kmyORCI8dod42v.JeN6', 'office_user', 'inactive', '2025-04-09 00:49:07', '5a8b600a59a80f2bf5028ae258b3aae8', '2025-04-09 09:49:07', 4, 'josh.jpg', 1800),
(16, 'elton', 'Elton John B. Moises', 'ejbm@bicol-u.edu.ph', '$2y$10$Botz5wCa9biZrVT7IdEDau.uVBcw3ByoD75pX2BYYe7dtutigluY.', 'user', 'inactive', '2025-04-13 06:01:46', NULL, NULL, 9, 'profile_16_1749816479.jpg', 600),
(17, 'nami', 'Mark Jayson Namia', 'mjn@gmail.com', '$2y$10$2MIZlmP380wS0sj/cOfqbe20HkPz234S49cJEj2omrrTjBasHVqyO', 'admin', 'active', '2025-04-13 15:43:51', NULL, NULL, 4, 'default_profile.png', 1800),
(18, 'kiimon', 'Seynatour Kiimon', 'sk@gmail.com', '$2y$10$UGpyMRA79O2OKhKfZDEf5O9CyXkMFlhDsVpWdELXMYnMtdFIV0mSC', 'office_user', 'deleted', '2025-04-20 21:36:04', '6687598406441374aeffbc338a60f728', '2025-04-21 06:36:04', 4, 'default_profile.png', 1800),
(19, 'geely', 'Geely Mitsubishi', 'waltielappy123@gmail.com', '$2y$10$uVrAvdjC3GsGheiqmZSuF.r.oBbcHdOceQaV.E5LChrNNc/p20/FC', 'user', 'inactive', '2025-06-24 06:54:34', NULL, NULL, 4, 'default_profile.png', 1800),
(21, 'miki', 'Miki Matsubara', 'mikimat@gmail.com', '$2y$10$hE2SgXv.RQahXlmHCv4MEeBfBLqkaY7/w9OVyZbnuy83LMMPrFDHa', 'user', 'active', '2025-06-24 07:01:30', NULL, NULL, NULL, 'default_profile.png', 1800),
(22, 'Toyoki', 'Toyota Suzuki', 'toyoki@gmail.com', '$2y$10$dLNw4hqEJbKpB5Hc7Mmhr.AjH4dOiMIUg9BqGDkiLnnx3rw89KBfS', 'user', 'active', '2025-06-24 07:23:43', NULL, NULL, NULL, 'default_profile.png', 1800),
(23, 'jet', 'Jet Kawasaki', 'kawaisaki@gmail.com', '$2y$10$JmxsfOnmMH/nJbxWUbuSqODWoHTMx8RZn/Zxg38EFpGlvhqCtP3b6', 'user', 'active', '2025-06-24 07:24:56', NULL, NULL, NULL, 'default_profile.png', 1800),
(24, 'juan', 'Juan A. Dela Cruz', 'juandelacruz@gmail.com', '$2y$10$NO/J3fBNaHSu/5HNM2vp/.hbb.u1NRzLSo8AQWh55P/TmnkUUv.Xe', 'office_admin', 'active', '2025-09-14 02:29:57', NULL, NULL, 3, 'default_profile.png', 1800),
(26, 'waltielappy@gmail.com', 'Seynatour Kiimon', 'wjll2022-2920-98466@bicol-u.edu.ph', '$2y$10$UcjNuBTMzbToTt2gi4Dr2Oc/93pdffaCkrp3U2zZ8JvtE1nYHKRry', 'user', 'active', '2025-09-27 04:39:35', NULL, NULL, 49, 'default_profile.png', 1800),
(28, 'matt', 'Matt Monro', 'matt@gmail.com', '$2y$10$FJP03nb6a4qqz4PRArmV2.8hlWtzT.shXDt4In8f8jBXhNbAhno56', 'user', 'deleted', '2025-09-27 04:41:23', NULL, NULL, 4, 'default_profile.png', 1800),
(29, 'jack', 'Jack Daniels', 'jack@gmail.com', '$2y$10$Gta6jYCePQr3UXEDOKWvdOtmznoA5.v/rIUxCw0vg5x5WH7bOYiZW', 'user', 'active', '2025-09-27 04:59:33', NULL, NULL, 4, 'default_profile.png', 1800),
(30, 'mike', 'Mike Tyson', 'mike@gmail.com', '$2y$10$FyofC5mTLdO.LAPel/wN0u.cR.BcYxanPhlfkt5n9CCT.JtJ.yTmW', 'user', 'active', '2025-09-27 06:24:00', NULL, NULL, 4, 'default_profile.png', 1800),
(31, 'walton', 'Walter Loneza', 'waltonloneza@gmail.com', '$2y$10$7jucgW6Qw9cQEq/aYJp7cOEHWrF/T2VA9o9QlCzYapek./Pl91snW', 'user', 'active', '2025-09-28 13:04:03', NULL, NULL, 49, 'default_profile.png', 1800),
(32, 'michael', 'Michael Jackson', 'notlawsfinds@gmail.com', '$2y$10$2pPV8VpaXhFzQIIDfeJHQOZsu/Ffijefruuw.Ve80FP3iKW9/Ea4y', 'office_admin', 'active', '2025-09-29 05:15:58', NULL, NULL, 4, 'default_profile.png', 1800),
(33, 'joshua', 'Joshua Mari Escano', 'joshuamarifrancis@gmail.com', '$2y$10$EfJTyR7xOmi5v9sylVRq7O4S/lHyFxuexWktQcnkvrImulAL.UzZq', 'user', 'active', '2025-09-29 14:32:40', NULL, NULL, 49, 'default_profile.png', 1800);

-- --------------------------------------------------------

--
-- Table structure for table `user_notifications`
--

CREATE TABLE `user_notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `notification_id` int(11) NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `read_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_notification_preferences`
--

CREATE TABLE `user_notification_preferences` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `email_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `in_app_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_permissions`
--

CREATE TABLE `user_permissions` (
  `user_id` int(11) NOT NULL,
  `permission` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_permissions`
--

INSERT INTO `user_permissions` (`user_id`, `permission`) VALUES
(26, 'fuel_inventory'),
(28, 'fuel_inventory'),
(29, 'fuel_inventory'),
(30, 'restrict_user_management');

-- --------------------------------------------------------

--
-- Structure for view `active_borrowing_stats`
--
DROP TABLE IF EXISTS `active_borrowing_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_borrowing_stats`  AS SELECT `o`.`office_name` AS `office_name`, count(`br`.`id`) AS `total_borrowed`, sum(`br`.`quantity`) AS `total_quantity_borrowed`, count(distinct `br`.`user_id`) AS `unique_borrowers` FROM (`borrow_requests` `br` join `offices` `o` on(`br`.`office_id` = `o`.`id`)) WHERE `br`.`status` = 'borrowed' GROUP BY `o`.`id`, `o`.`office_name` ;

-- --------------------------------------------------------

--
-- Structure for view `overdue_items`
--
DROP TABLE IF EXISTS `overdue_items`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `overdue_items`  AS SELECT `br`.`id` AS `id`, `u`.`fullname` AS `borrower_name`, `a`.`asset_name` AS `asset_name`, `br`.`quantity` AS `quantity`, `br`.`approved_at` AS `approved_at`, to_days(current_timestamp()) - to_days(`br`.`approved_at`) AS `days_borrowed`, `o`.`office_name` AS `office_name` FROM (((`borrow_requests` `br` join `users` `u` on(`br`.`user_id` = `u`.`id`)) join `assets` `a` on(`br`.`asset_id` = `a`.`id`)) join `offices` `o` on(`br`.`office_id` = `o`.`id`)) WHERE `br`.`status` = 'borrowed' AND to_days(current_timestamp()) - to_days(`br`.`approved_at`) > 30 ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `archives`
--
ALTER TABLE `archives`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category` (`category`),
  ADD KEY `idx_assets_office_status` (`office_id`,`status`),
  ADD KEY `idx_assets_status` (`status`),
  ADD KEY `idx_assets_ics_id` (`ics_id`),
  ADD KEY `idx_assets_asset_new_id` (`asset_new_id`),
  ADD KEY `idx_assets_par_id` (`par_id`),
  ADD KEY `idx_assets_batch_tracking` (`enable_batch_tracking`);

--
-- Indexes for table `assets_archive`
--
ALTER TABLE `assets_archive`
  ADD PRIMARY KEY (`archive_id`);

--
-- Indexes for table `assets_new`
--
ALTER TABLE `assets_new`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_description` (`description`),
  ADD KEY `idx_assets_new_office_id` (`office_id`),
  ADD KEY `idx_assets_new_par_id` (`par_id`);

--
-- Indexes for table `asset_lifecycle_events`
--
ALTER TABLE `asset_lifecycle_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_asset` (`asset_id`),
  ADD KEY `idx_type` (`event_type`),
  ADD KEY `idx_ref` (`ref_table`,`ref_id`);

--
-- Indexes for table `asset_requests`
--
ALTER TABLE `asset_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `asset_id` (`asset_name`(768)),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_office` (`office_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_module` (`module`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `batches`
--
ALTER TABLE `batches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `batch_number` (`batch_number`),
  ADD KEY `idx_batch_number` (`batch_number`),
  ADD KEY `idx_asset_id` (`asset_id`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_expiry_date` (`expiry_date`),
  ADD KEY `idx_manufacture_date` (`manufacture_date`),
  ADD KEY `idx_quality_status` (`quality_status`);

--
-- Indexes for table `batch_items`
--
ALTER TABLE `batch_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_batch_item` (`batch_id`,`item_number`),
  ADD KEY `idx_batch_id` (`batch_id`),
  ADD KEY `idx_serial_number` (`serial_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_office_id` (`office_id`);

--
-- Indexes for table `batch_receipts`
--
ALTER TABLE `batch_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_receipt_batch` (`batch_id`,`receipt_number`),
  ADD KEY `idx_receipt_number` (`receipt_number`),
  ADD KEY `idx_delivery_date` (`delivery_date`),
  ADD KEY `idx_received_by` (`received_by`);

--
-- Indexes for table `batch_transactions`
--
ALTER TABLE `batch_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_batch_id` (`batch_id`),
  ADD KEY `idx_batch_item_id` (`batch_item_id`),
  ADD KEY `idx_transaction_type` (`transaction_type`),
  ADD KEY `idx_transaction_date` (`transaction_date`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_reference_id` (`reference_id`);

--
-- Indexes for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_borrow_requests_user_id` (`user_id`),
  ADD KEY `idx_borrow_requests_asset_id` (`asset_id`),
  ADD KEY `idx_borrow_requests_office_id` (`office_id`),
  ADD KEY `idx_borrow_requests_status` (`status`),
  ADD KEY `idx_borrow_requests_requested_at` (`requested_at`),
  ADD KEY `fk_borrow_batch` (`batch_id`),
  ADD KEY `fk_borrow_batch_item` (`batch_item_id`),
  ADD KEY `fk_borrow_requests_source_office` (`source_office_id`),
  ADD KEY `fk_borrow_requests_requested_by` (`requested_by_user_id`),
  ADD KEY `fk_borrow_requests_requested_for_office` (`requested_for_office_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `consumption_log`
--
ALTER TABLE `consumption_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `recipient_user_id` (`recipient_user_id`),
  ADD KEY `dispensed_by_user_id` (`dispensed_by_user_id`),
  ADD KEY `fk_consumption_log_office` (`office_id`);

--
-- Indexes for table `doc_no`
--
ALTER TABLE `doc_no`
  ADD PRIMARY KEY (`doc_id`),
  ADD UNIQUE KEY `document_number` (`document_number`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `employee_no` (`employee_no`),
  ADD KEY `fk_employees_office` (`office_id`);

--
-- Indexes for table `forms`
--
ALTER TABLE `forms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `form_thresholds`
--
ALTER TABLE `form_thresholds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `fuel_out`
--
ALTER TABLE `fuel_out`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fo_date` (`fo_date`);

--
-- Indexes for table `fuel_records`
--
ALTER TABLE `fuel_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `date_time` (`date_time`);

--
-- Indexes for table `fuel_stock`
--
ALTER TABLE `fuel_stock`
  ADD UNIQUE KEY `fuel_type_id` (`fuel_type_id`);

--
-- Indexes for table `fuel_types`
--
ALTER TABLE `fuel_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `generated_reports`
--
ALTER TABLE `generated_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `google_drive_settings`
--
ALTER TABLE `google_drive_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `google_drive_tokens`
--
ALTER TABLE `google_drive_tokens`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ics_form`
--
ALTER TABLE `ics_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `ics_items`
--
ALTER TABLE `ics_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `ics_id` (`ics_id`);

--
-- Indexes for table `iirup_form`
--
ALTER TABLE `iirup_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `iirup_items`
--
ALTER TABLE `iirup_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_iirup_id` (`iirup_id`);

--
-- Indexes for table `infrastructure_inventory`
--
ALTER TABLE `infrastructure_inventory`
  ADD PRIMARY KEY (`inventory_id`);

--
-- Indexes for table `inter_department_approvals`
--
ALTER TABLE `inter_department_approvals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `approver_id` (`approver_id`);

--
-- Indexes for table `inventory_actions`
--
ALTER TABLE `inventory_actions`
  ADD PRIMARY KEY (`action_id`),
  ADD KEY `office_id` (`office_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `itr_form`
--
ALTER TABLE `itr_form`
  ADD PRIMARY KEY (`itr_id`);

--
-- Indexes for table `itr_items`
--
ALTER TABLE `itr_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_itr_id` (`itr_id`),
  ADD KEY `idx_asset_id` (`asset_id`);

--
-- Indexes for table `legal_documents`
--
ALTER TABLE `legal_documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `legal_document_history`
--
ALTER TABLE `legal_document_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_id` (`document_id`),
  ADD KEY `document_type` (`document_type`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_legal_history_doc_version` (`document_id`,`version`);

--
-- Indexes for table `mr_details`
--
ALTER TABLE `mr_details`
  ADD PRIMARY KEY (`mr_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `related_entity` (`related_entity_type`,`related_entity_id`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `is_archived` (`is_archived`);

--
-- Indexes for table `notification_types`
--
ALTER TABLE `notification_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `office_name` (`office_name`),
  ADD KEY `fk_offices_head_user` (`head_user_id`);

--
-- Indexes for table `par_form`
--
ALTER TABLE `par_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `par_items`
--
ALTER TABLE `par_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `form_id` (`form_id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `permission_audit_log`
--
ALTER TABLE `permission_audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `permission_id` (`permission_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_changed_by` (`changed_by`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `permission_levels`
--
ALTER TABLE `permission_levels`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `level_name` (`level_name`),
  ADD UNIQUE KEY `unique_level_weight` (`level_weight`);

--
-- Indexes for table `red_tags`
--
ALTER TABLE `red_tags`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `iirup_id` (`iirup_id`),
  ADD KEY `tagged_by` (`tagged_by`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `report_generation_settings`
--
ALTER TABLE `report_generation_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `report_templates`
--
ALTER TABLE `report_templates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_created_by` (`created_by`),
  ADD KEY `fk_updated_by` (`updated_by`);

--
-- Indexes for table `returned_assets`
--
ALTER TABLE `returned_assets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrow_request_id` (`borrow_request_id`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `ris_form`
--
ALTER TABLE `ris_form`
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `ris_items`
--
ALTER TABLE `ris_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ris_form_id` (`ris_form_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`),
  ADD KEY `idx_role` (`role`);

--
-- Indexes for table `rpcppe_form`
--
ALTER TABLE `rpcppe_form`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system`
--
ALTER TABLE `system`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_info`
--
ALTER TABLE `system_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `tag_formats`
--
ALTER TABLE `tag_formats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `format_code` (`format_code`);

--
-- Indexes for table `unit`
--
ALTER TABLE `unit`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `office_id` (`office_id`);

--
-- Indexes for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_notification` (`user_id`,`notification_id`),
  ADD KEY `notification_id` (`notification_id`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_type` (`user_id`,`type_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD PRIMARY KEY (`user_id`,`permission`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `archives`
--
ALTER TABLE `archives`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `assets_archive`
--
ALTER TABLE `assets_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `assets_new`
--
ALTER TABLE `assets_new`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `asset_lifecycle_events`
--
ALTER TABLE `asset_lifecycle_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `asset_requests`
--
ALTER TABLE `asset_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=508;

--
-- AUTO_INCREMENT for table `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `batches`
--
ALTER TABLE `batches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batch_items`
--
ALTER TABLE `batch_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batch_receipts`
--
ALTER TABLE `batch_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `batch_transactions`
--
ALTER TABLE `batch_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `consumption_log`
--
ALTER TABLE `consumption_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doc_no`
--
ALTER TABLE `doc_no`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91;

--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `form_thresholds`
--
ALTER TABLE `form_thresholds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fuel_out`
--
ALTER TABLE `fuel_out`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `fuel_records`
--
ALTER TABLE `fuel_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `fuel_types`
--
ALTER TABLE `fuel_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `generated_reports`
--
ALTER TABLE `generated_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;

--
-- AUTO_INCREMENT for table `ics_form`
--
ALTER TABLE `ics_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `ics_items`
--
ALTER TABLE `ics_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `iirup_form`
--
ALTER TABLE `iirup_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `iirup_items`
--
ALTER TABLE `iirup_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `infrastructure_inventory`
--
ALTER TABLE `infrastructure_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inter_department_approvals`
--
ALTER TABLE `inter_department_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_actions`
--
ALTER TABLE `inventory_actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `itr_form`
--
ALTER TABLE `itr_form`
  MODIFY `itr_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `itr_items`
--
ALTER TABLE `itr_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `legal_documents`
--
ALTER TABLE `legal_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `legal_document_history`
--
ALTER TABLE `legal_document_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mr_details`
--
ALTER TABLE `mr_details`
  MODIFY `mr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_types`
--
ALTER TABLE `notification_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `par_form`
--
ALTER TABLE `par_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `par_items`
--
ALTER TABLE `par_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_audit_log`
--
ALTER TABLE `permission_audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permission_levels`
--
ALTER TABLE `permission_levels`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `red_tags`
--
ALTER TABLE `red_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `report_generation_settings`
--
ALTER TABLE `report_generation_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `report_templates`
--
ALTER TABLE `report_templates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `returned_assets`
--
ALTER TABLE `returned_assets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `ris_form`
--
ALTER TABLE `ris_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ris_items`
--
ALTER TABLE `ris_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `rpcppe_form`
--
ALTER TABLE `rpcppe_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `system_info`
--
ALTER TABLE `system_info`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tag_formats`
--
ALTER TABLE `tag_formats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `unit`
--
ALTER TABLE `unit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `user_notifications`
--
ALTER TABLE `user_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `assets`
--
ALTER TABLE `assets`
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `assets_ibfk_2` FOREIGN KEY (`category`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `fk_assets_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assets_ics` FOREIGN KEY (`ics_id`) REFERENCES `ics_form` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_assets_par` FOREIGN KEY (`par_id`) REFERENCES `par_form` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `assets_new`
--
ALTER TABLE `assets_new`
  ADD CONSTRAINT `fk_assets_new_par` FOREIGN KEY (`par_id`) REFERENCES `par_form` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `asset_lifecycle_events`
--
ALTER TABLE `asset_lifecycle_events`
  ADD CONSTRAINT `fk_lifecycle_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `fk_borrow_requests_requested_by` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_borrow_requests_requested_for_office` FOREIGN KEY (`requested_for_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_borrow_requests_source_office` FOREIGN KEY (`source_office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fuel_stock`
--
ALTER TABLE `fuel_stock`
  ADD CONSTRAINT `fuel_stock_ibfk_1` FOREIGN KEY (`fuel_type_id`) REFERENCES `fuel_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `inter_department_approvals`
--
ALTER TABLE `inter_department_approvals`
  ADD CONSTRAINT `fk_ida_approver` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ida_request` FOREIGN KEY (`request_id`) REFERENCES `borrow_requests` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `itr_items`
--
ALTER TABLE `itr_items`
  ADD CONSTRAINT `fk_itr_items_assets` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_itr_items_itr_form` FOREIGN KEY (`itr_id`) REFERENCES `itr_form` (`itr_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `legal_documents`
--
ALTER TABLE `legal_documents`
  ADD CONSTRAINT `legal_documents_ibfk_1` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `legal_document_history`
--
ALTER TABLE `legal_document_history`
  ADD CONSTRAINT `legal_document_history_ibfk_1` FOREIGN KEY (`document_id`) REFERENCES `legal_documents` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `legal_document_history_ibfk_2` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `notification_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `offices`
--
ALTER TABLE `offices`
  ADD CONSTRAINT `fk_offices_head_user` FOREIGN KEY (`head_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `permission_audit_log`
--
ALTER TABLE `permission_audit_log`
  ADD CONSTRAINT `permission_audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_audit_log_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permission_audit_log_ibfk_3` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notifications`
--
ALTER TABLE `user_notifications`
  ADD CONSTRAINT `user_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_notifications_ibfk_2` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_notification_preferences`
--
ALTER TABLE `user_notification_preferences`
  ADD CONSTRAINT `user_notification_preferences_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_notification_preferences_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `notification_types` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
