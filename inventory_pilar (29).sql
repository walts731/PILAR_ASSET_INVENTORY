-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2025 at 03:49 PM
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
(1, 'Office Table – Wooden', 2, 'Office Table – Wooden', 1, 0, 'pcs', 'unserviceable', '2025-09-19', 4, 1, NULL, 1, '2025-09-22 10:04:13', 3500.00, '1.png', 'asset', '', '', '', 'MR-2025-00001', '', '', 17, NULL, 1, 'No. PS-5S-03-F02-01', '[]', 0, 1, 0, 0),
(3, 'Mouse', 2, 'Mouse', 1, 0, 'pcs', 'unserviceable', '2025-09-19', 4, 1, NULL, 0, '2025-09-24 05:34:31', 350.00, '3.png', 'asset', 'asset_3_1758293767.jpg', '', '', 'MR-2025-00003', '', '', 18, NULL, 2, 'No. PS-5S-03-F02-03', '[]', 0, 1, 0, 0),
(5, 'Printer Epson', 1, 'Printer Epson', 1, 0, 'pcs', 'unserviceable', '2025-09-19', 4, 1, NULL, 1, '2025-09-22 11:32:35', 4593.00, '5.png', 'asset', '', '', '', 'MR-2025-00005', '', '', 19, NULL, 3, 'No. PS-5S-03-F02-05', '[\"asset_5_1758540755_0.jpg\"]', 0, 1, 0, 0),
(6, 'Printer Epson', 1, 'Printer Epson', 1, 0, 'pcs', 'available', '2025-09-19', 4, 1, NULL, 0, '2025-09-22 10:04:13', 4593.00, '6.png', 'asset', '', '', '', 'MR-2025-00006', '', '', 19, NULL, 3, 'No. PS-5S-03-F02-06', '[]', 0, 1, 0, 0),
(15, 'Blue Chair', 2, 'Uratex', 3, 0, 'pcs', 'available', '2025-04-04', 4, NULL, NULL, 0, '2025-09-22 10:04:13', 30000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', 0, 1, 0, 0),
(16, 'eagle', 1, 'eagle', 1, 0, 'box', 'available', '2025-09-19', 49, NULL, NULL, 0, '2025-09-22 10:04:13', 345.00, '21.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', 0, 1, 0, 0),
(17, 'Van', 1, 'Van', 6, 0, 'unit', 'available', '2025-09-18', 49, NULL, NULL, 0, '2025-09-22 10:04:13', 49999.99, '54.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '[]', 0, 1, 0, 0),
(19, 'Cellphone', 1, 'Cellphone', 1, 0, 'pcs', 'unserviceable', '2025-09-21', 4, 1, NULL, 1, '2025-09-22 10:04:13', 5678.00, '19.png', 'asset', '', '', '', 'MR-2025-00019', '', '', 25, NULL, 8, 'No. PS-5S-03-F02-19', '[]', 0, 1, 0, 0),
(20, 'Cellphone', 1, 'Cellphone', 1, 0, 'pcs', 'unserviceable', '2025-09-21', 4, 2, NULL, 1, '2025-09-22 10:04:13', 5678.00, '20.png', 'asset', '', '', '', 'MR-2025-00020', '', '', 25, NULL, 8, 'No. PS-5S-03-F02-20', '[]', 0, 1, 0, 0),
(23, 'Dell Unit', 1, 'Dell Unit', 1, 0, 'unit', 'unserviceable', '2025-09-21', 4, 1, NULL, 1, '2025-09-22 10:04:13', 99000.00, '23.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 9, NULL, '[]', 0, 1, 0, 0),
(24, 'Dell Unit', NULL, 'Dell Unit', 1, 0, 'unit', 'available', '2025-09-21', 4, NULL, NULL, 0, '2025-09-22 10:04:13', 99000.00, '24.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 9, NULL, '[]', 0, 1, 0, 0),
(27, 'Jetski', 1, 'Jetski', 1, 0, 'unit', 'unserviceable', '2025-09-21', 4, 2, NULL, 1, '2025-09-22 10:04:13', 96780.00, '27.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 12, NULL, '[]', 0, 1, 0, 0),
(28, 'Jetski', NULL, 'Jetski', 1, 0, 'unit', 'available', '2025-09-21', 4, NULL, NULL, 0, '2025-09-22 10:04:13', 96780.00, '28.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 12, NULL, '[]', 0, 1, 0, 0),
(29, 'HIlux', NULL, 'HIlux', 1, 0, 'roll', 'available', '2025-09-21', 4, NULL, NULL, 0, '2025-09-22 10:04:13', 1000000.00, '29.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 13, NULL, '[]', 0, 1, 0, 0),
(30, 'Car', NULL, 'Car', 1, 0, 'unit', 'unserviceable', '2025-09-21', 4, NULL, NULL, 1, '2025-09-22 10:04:13', 4500000.00, '30.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 14, NULL, '[]', 0, 1, 0, 0),
(31, 'Mio Soul i', 1, 'Mio Soul i', 1, 0, 'unit', 'available', '2025-09-21', 4, 2, NULL, 0, '2025-09-22 10:04:13', 75000.00, '31.png', 'asset', '', '', '', NULL, '', '', NULL, 44, 15, NULL, '[]', 0, 1, 0, 0),
(32, 'Honda', 1, 'Honda Click 125', 1, 0, 'unit', 'unserviceable', '0000-00-00', 7, 2, NULL, 1, '2025-09-22 10:04:13', 75000.00, '32.png', 'asset', '', '', '', '', '', '', NULL, 45, 16, 'No. PS-5S-03-F02-32', '[]', 0, 1, 0, 0),
(33, 'Hilux Van', 1, 'Hilux Van', 1, 0, 'unit', 'unserviceable', '2025-09-21', 4, 71, NULL, 1, '2025-09-24 13:58:37', 7600000.00, '33.png', 'asset', '', '', '', 'MR-2025-00033', '', '', NULL, 46, 17, 'No. PS-5S-03-F02-33', '[\"asset_33_1758536017_0.jpg\"]', 0, 1, 0, 0),
(34, 'Hilux van black', 2, 'Hilux van black', 1, 0, 'unit', 'unserviceable', '2025-09-22', 4, 8, NULL, 1, '2025-09-22 10:04:13', 2300000.00, '34.png', 'asset', '', '', 'EQP-001', 'MR-2025-00034', '', '', NULL, 47, 18, 'No. PS-5S-03-F02-34', '[]', 0, 1, 0, 0),
(36, 'Lenovo AMD Ryzen 7', NULL, 'Lenovo AMD Ryzen 7', 1, 0, 'unit', 'available', '2025-09-22', 4, NULL, NULL, 0, '2025-09-22 10:04:13', 75000.00, '36.png', 'asset', '', '', '', NULL, '', '', NULL, 49, 19, NULL, '[]', 0, 1, 0, 0),
(40, 'Computer', 1, 'Computer', 1, 0, 'unit', 'unserviceable', '2025-09-22', 4, 2, NULL, 1, '2025-09-23 10:17:04', 36500.00, '40.png', 'asset', '', '', '', 'MR-2025-00040', '', '', 27, NULL, 21, 'No. PS-5S-03-F02-40', NULL, 0, 1, 0, 0),
(41, 'Computer', 1, 'Computer', 1, 0, 'unit', 'unserviceable', '2025-09-22', 4, 2, NULL, 1, '2025-09-24 05:25:32', 36500.00, '41.png', 'asset', '', '', '', 'MR-2025-00041', '', '', 27, NULL, 21, 'No. PS-5S-03-F02-41', '[\"asset_41_1758691097_0.jpg\"]', 0, 1, 0, 0),
(44, 'Desktop', NULL, 'Desktop', 1, 0, 'unit', 'available', '2025-09-24', NULL, NULL, NULL, 0, '2025-09-24 01:07:52', 54000.00, '44.png', 'asset', '', '', '', NULL, '', '', NULL, 51, 22, NULL, NULL, 0, 1, 0, 0),
(45, 'Desktop', NULL, 'Desktop', 1, 0, 'unit', 'available', '2025-09-24', NULL, NULL, NULL, 0, '2025-09-24 01:07:52', 54000.00, '45.png', 'asset', '', '', '', NULL, '', '', NULL, 51, 22, NULL, NULL, 0, 1, 0, 0),
(46, 'Laptop', NULL, 'Laptop', 1, 0, 'unit', 'available', '2025-09-24', NULL, NULL, NULL, 0, '2025-09-24 01:07:52', 57945.00, '46.png', 'asset', '', '', '', NULL, '', '', NULL, 51, 23, NULL, NULL, 0, 1, 0, 0),
(47, 'Lenovo', NULL, 'Lenovo', 1, 0, 'unit', 'available', '2025-09-24', NULL, NULL, NULL, 0, '2025-09-24 01:55:27', 56042.00, '47.png', 'asset', '', '', '', NULL, '', '', NULL, 52, 24, NULL, NULL, 0, 1, 0, 0),
(48, 'Computer', NULL, 'Computer', 1, 0, 'unit', 'available', '2025-09-24', NULL, NULL, NULL, 0, '2025-09-24 01:57:16', 56098.00, '48.png', 'asset', '', '', '', NULL, '', '', NULL, 53, 25, NULL, NULL, 0, 1, 0, 0),
(49, 'Mouse', NULL, 'Mouse', 1, 0, 'unit', 'available', '2025-09-24', 4, NULL, NULL, 0, '2025-09-24 02:47:53', 450.00, '49.png', 'asset', '', '', '', NULL, '', '', 28, NULL, 26, NULL, NULL, 0, 1, 0, 0),
(50, 'Mouse', NULL, 'Mouse', 1, 0, 'unit', 'available', '2025-09-24', 4, NULL, NULL, 0, '2025-09-24 02:47:53', 450.00, '50.png', 'asset', '', '', '', NULL, '', '', 28, NULL, 26, NULL, NULL, 0, 1, 0, 0),
(51, 'Mouse', NULL, 'Mouse', 1, 0, 'unit', 'available', '2025-09-24', 4, NULL, NULL, 0, '2025-09-24 02:47:53', 450.00, '51.png', 'asset', '', '', '', NULL, '', '', 28, NULL, 26, NULL, NULL, 0, 1, 0, 0),
(52, 'Mouse', NULL, 'Mouse', 1, 0, 'unit', 'available', '2025-09-24', 4, NULL, NULL, 0, '2025-09-24 02:47:53', 450.00, '52.png', 'asset', '', '', '', NULL, '', '', 28, NULL, 26, NULL, NULL, 0, 1, 0, 0),
(53, 'Mouse', NULL, 'Mouse', 1, 0, 'unit', 'available', '2025-09-24', 4, NULL, NULL, 0, '2025-09-24 02:47:53', 450.00, '53.png', 'asset', '', '', '', NULL, '', '', 28, NULL, 26, NULL, NULL, 0, 1, 0, 0),
(54, 'mouse pad', 1, 'mouse pad', 1, 0, 'pcs', 'unserviceable', '2025-09-24', 4, 12, 'Roberto Cruz', 0, '2025-09-25 04:07:28', 345.00, '54.png', 'asset', '', '', '2025-ECE-0001', 'PN-2019-05-02-0001-01', '', '', 29, NULL, 27, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(55, 'mouse pad', 6, 'mouse pad', 1, 0, 'pcs', 'available', '2025-09-24', 4, NULL, 'Roberto Cruz', 0, '2025-09-24 05:23:00', 345.00, '55.png', 'asset', '', '', '', 'PN-2019-05-02-0001-01', '', '', 29, NULL, 27, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(56, 'mouse pad', NULL, 'mouse pad', 1, 0, 'pcs', 'available', '2025-09-24', 4, NULL, NULL, 0, '2025-09-24 02:57:07', 345.00, '56.png', 'asset', '', '', '', NULL, '', '', 29, NULL, 27, NULL, NULL, 0, 1, 0, 0),
(57, 'mouse pad', NULL, 'mouse pad', 1, 0, 'pcs', 'available', '2025-09-24', 4, NULL, NULL, 0, '2025-09-24 02:57:07', 345.00, '57.png', 'asset', '', '', '', NULL, '', '', 29, NULL, 27, NULL, NULL, 0, 1, 0, 0),
(58, 'mouse pad', NULL, 'mouse pad', 1, 0, 'pcs', 'available', '2025-09-24', 4, NULL, NULL, 0, '2025-09-24 02:57:07', 345.00, '58.png', 'asset', '', '', '', NULL, '', '', 29, NULL, 27, NULL, NULL, 0, 1, 0, 0),
(59, 'Mouse', NULL, 'Mouse', 1, 0, 'unit', 'available', '2025-09-24', NULL, NULL, NULL, 0, '2025-09-24 07:15:42', 564.00, '59.png', 'asset', '', '', '', NULL, '', '', 30, NULL, 28, NULL, NULL, 0, 1, 0, 0),
(60, 'Mouse', NULL, 'Mouse', 1, 0, 'unit', 'available', '2025-09-24', NULL, NULL, NULL, 0, '2025-09-24 07:15:42', 564.00, '60.png', 'asset', '', '', '', NULL, '', '', 30, NULL, 28, NULL, NULL, 0, 1, 0, 0),
(61, 'Mouse', NULL, 'Mouse', 1, 0, 'unit', 'available', '2025-09-24', 4, NULL, NULL, 0, '2025-09-24 07:16:25', 453.00, '61.png', 'asset', '', '', '', NULL, '', '', 31, NULL, 29, NULL, NULL, 0, 1, 0, 0),
(68, 'Trash Can', 2, 'Trash Can', 1, 0, 'pcs', 'available', '2025-09-25', 4, 10, 'Roberto Cruz', 0, '2025-09-25 04:34:20', 350.00, '68.png', 'asset', 'asset_68_1758758166.jpg', '', '2025-FUR-0001', 'PN-2019-05-02-0001-01', '', '', 32, NULL, 32, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(69, 'Laptop', NULL, 'Laptop', 1, 0, 'unit', 'serviceable', '2025-01-09', 3, NULL, 'John Smith', 0, '2025-09-26 14:31:43', 45000.00, '69.png', 'asset', '', 'SN-ABC123', 'CODE-01', 'PROP-123', 'XPS 15', 'Dell', NULL, NULL, 33, 'INV-0001', NULL, 0, 1, 0, 0),
(70, 'Laptop', NULL, 'Laptop', 1, 0, 'unit', 'serviceable', '2025-01-09', 3, NULL, 'John Smith', 0, '2025-09-26 14:31:43', 45000.00, '70.png', 'asset', '', 'SN-ABC123', 'CODE-01', 'PROP-123', 'XPS 15', 'Dell', NULL, NULL, 33, 'INV-0001', NULL, 0, 1, 0, 0),
(71, 'Laptop', NULL, 'Laptop', 1, 0, 'unit', 'serviceable', '2025-01-09', 3, NULL, 'John Smith', 0, '2025-09-26 14:31:43', 45000.00, '71.png', 'asset', '', 'SN-ABC123', 'CODE-01', 'PROP-123', 'XPS 15', 'Dell', NULL, NULL, 33, 'INV-0001', NULL, 0, 1, 0, 0),
(72, 'Laptop', NULL, 'Laptop', 1, 0, 'unit', 'serviceable', '2025-01-09', 3, 10, 'John Smith', 0, '2025-09-26 14:44:24', 45000.00, '72.png', 'asset', '', 'SN-ABC123', 'CODE-01', 'PROP-123', 'XPS 15', 'Dell', NULL, NULL, 34, 'INV-0001', NULL, 0, 1, 0, 0),
(73, 'Laptop', NULL, 'Laptop', 1, 0, 'unit', 'serviceable', '2025-01-09', 3, 10, 'John Smith', 0, '2025-09-26 14:44:24', 45000.00, '73.png', 'asset', '', 'SN-ABC123', 'CODE-01', 'PROP-123', 'XPS 15', 'Dell', NULL, NULL, 34, 'INV-0001', NULL, 0, 1, 0, 0),
(74, 'Laptop', NULL, 'Laptop', 1, 0, 'unit', 'serviceable', '2025-01-09', 3, 10, 'John Smith', 0, '2025-09-26 14:44:24', 45000.00, '74.png', 'asset', '', 'SN-ABC123', 'CODE-01', 'PROP-123', 'XPS 15', 'Dell', NULL, NULL, 34, 'INV-0001', NULL, 0, 1, 0, 0),
(75, 'AIRCON \\r\\n', NULL, 'AIRCON \\r\\n', 1, 0, 'unit', 'serviceable', '2025-09-26', 4, 22, NULL, 0, '2025-09-26 15:19:39', 18437.00, '75.png', 'asset', 'asset_1758899979_dcf9f1.png', '', '', 'PN-2019-05-02-0001-01', 'SPLIT TYPE', 'TCL', NULL, NULL, 36, 'INV-0001', NULL, 0, 1, 0, 0),
(76, 'AIRCON \\r\\n', NULL, 'AIRCON \\r\\n', 1, 0, 'unit', 'serviceable', '2025-09-26', 4, 22, NULL, 0, '2025-09-26 15:19:39', 18437.00, '76.png', 'asset', 'asset_1758899979_dcf9f1.png', '', '', 'PN-2019-05-02-0001-01', 'SPLIT TYPE', 'TCL', NULL, NULL, 36, 'INV-0001', NULL, 0, 1, 0, 0),
(82, 'Ambi Pur (spray)', NULL, 'Ambi Pur (spray)', 0, 0, 'bottle', 'available', '2025-09-27', 3, NULL, NULL, 0, '2025-09-27 13:01:55', 390.00, '', 'consumable', '', '', '', '1', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(86, 'Ambi pur', NULL, 'Ambi pur', 0, 0, 'bottle', 'available', '2025-09-27', 3, NULL, NULL, 0, '2025-09-28 05:57:52', 300.00, '', 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0),
(91, 'Office Table', NULL, 'Office Table', 1, 0, 'set', 'serviceable', '2025-09-27', 4, 45, NULL, 0, '2025-09-27 15:14:51', 3577.65, '91.png', 'asset', 'asset_1758986091_7e935e.jpg', '', '', 'PN-2019-05-02-0001-01', 'Mesh Back', 'Fursys', NULL, NULL, 37, 'INV-0001', NULL, 0, 1, 0, 0),
(92, 'Office Table', NULL, 'Office Table', 1, 0, 'set', 'serviceable', '2025-09-27', 4, 45, NULL, 0, '2025-09-27 15:14:51', 3577.65, '92.png', 'asset', 'asset_1758986091_7e935e.jpg', '', '', 'PN-2019-05-02-0001-01', 'Mesh Back', 'Fursys', NULL, NULL, 37, 'INV-0001', NULL, 0, 1, 0, 0),
(93, 'Office Table', NULL, 'Office Table', 1, 0, 'set', 'serviceable', '2025-09-27', 4, 45, NULL, 0, '2025-09-27 15:14:51', 3577.65, '93.png', 'asset', 'asset_1758986091_7e935e.jpg', '', '', 'PN-2019-05-02-0001-01', 'Mesh Back', 'Fursys', NULL, NULL, 37, 'INV-0001', NULL, 0, 1, 0, 0),
(94, 'Inventory Box', 2, 'Inventory Box', 1, 0, 'unit', 'serviceable', '2025-09-28', 4, 56, 'Jack Robertson', 0, '2025-09-28 00:43:54', 3569.00, '94.png', 'asset', NULL, 'SN-DC-2025-0001', '2025-FUR-0001', 'PN-2019-05-02-0001-01', 'Mesh Back', 'Fursys', NULL, NULL, 38, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(95, 'Inventory Box', NULL, 'Inventory Box', 1, 0, 'unit', 'serviceable', '2025-09-28', 4, 56, NULL, 0, '2025-09-28 00:41:40', 3569.00, '95.png', 'asset', NULL, 'SN-DC-2025-0001', '2025-FUR-010', 'PN-2019-05-02-0001-01', 'Mesh Back', 'Fursys', NULL, NULL, 38, 'INV-0001', NULL, 0, 1, 0, 0),
(96, 'Office Table', 2, 'Office Table', 1, 0, 'unit', 'serviceable', '2025-09-28', 4, 25, NULL, 0, '2025-09-28 00:53:22', 2355.00, '96.png', 'asset', NULL, 'SN-DC-2025-0001', 'FUR-011', 'PN-2019-05-02-0001-01', 'Mesh Back', 'Fursys', NULL, NULL, 40, 'INV-0001', NULL, 0, 1, 0, 0),
(97, 'Office Table', 2, 'Office Table', 1, 0, 'unit', 'serviceable', '2025-09-28', 4, 25, NULL, 0, '2025-09-28 00:53:22', 2355.00, '97.png', 'asset', NULL, 'SN-DC-2025-0001', 'FUR-011', 'PN-2019-05-02-0001-01', 'Mesh Back', 'Fursys', NULL, NULL, 40, 'INV-0001', NULL, 0, 1, 0, 0),
(98, 'Office Chair', 2, 'Office Chair', 1, 0, 'unit', 'serviceable', '2025-09-28', 4, 60, NULL, 0, '2025-09-28 00:55:28', 964.00, '98.png', 'asset', NULL, 'SN-DC-2025-0002', 'FUR-012', 'PN-2019-05-02-0001-01', 'Mesh Back', 'Fursys', NULL, NULL, 41, 'INV-0001', NULL, 0, 1, 0, 0),
(99, 'Aircon Split Type', 1, 'Aircon Split Type', 1, 0, 'unit', 'serviceable', '2025-09-28', 4, 19, 'Jake Paul', 0, '2025-09-28 01:00:28', 18305.00, '99.png', 'asset', NULL, 'SN-DC-2025-0001', 'EQP-001', 'PN-2019-05-02-0001-03', 'SPLIT TYPE', 'TCL', NULL, NULL, 42, 'INV-0001', NULL, 0, 1, 0, 0),
(100, 'Power Generator', 5, 'Power Generator', 1, 0, 'unit', 'serviceable', '2025-09-28', 4, 83, 'Angela Rizal', 0, '2025-09-28 01:20:20', 23984.00, '100.png', 'asset', 'asset_1759022024_0b8a78.jpg', 'SN-ABC123456', 'EQP-001', 'PN-2019-05-02-0001-05', '', 'Mikata', NULL, NULL, 43, 'PS-5S-03-F02-01-01', NULL, 0, 1, 0, 0),
(104, 'Ambi pur', NULL, 'Ambi pur', 2, 2, 'bottle', 'available', '2025-09-27', 3, NULL, NULL, 0, '2025-09-28 06:00:06', 300.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, '', NULL, 0, 1, 0, 0),
(106, 'Air freshener (Ambi pur)', NULL, 'Air freshener (Ambi pur)', 2, 0, 'bottle', 'available', '2025-09-28', 4, NULL, NULL, 0, '2025-09-28 06:01:41', 300.00, '', 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0);

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
(9, 90, '0', NULL, 'Desktop Computer (Core i5)', 1, 'unit', 'available', '2025-09-24', 4, 0, '2025-09-24 07:18:16', 546740.00, '65.png', 'asset', '2025-09-28 06:32:58');

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
(1, 'Office Table – Wooden', 1, 3500.00, 'pcs', 4, NULL, NULL, '2025-09-19 19:20:38'),
(2, 'Mouse', 1, 350.00, 'pcs', 4, NULL, NULL, '2025-09-19 19:36:01'),
(3, 'Printer Epson', 2, 4593.00, 'pcs', 4, NULL, 19, '2025-09-19 20:04:07'),
(4, 'Air Conditioner (2.5 HP, LG Inverter)', 0, 38000.00, 'unit', 4, NULL, 20, '2025-09-20 04:20:32'),
(5, 'Desktop Computer – Intel i5, 8GB RAM, 256GB SSD', 0, 4573.98, 'pcs', 4, NULL, 22, '2025-09-20 04:43:50'),
(6, 'Office Table – Wooden', 0, 3500.00, 'pcs', 49, NULL, 23, '2025-09-20 14:30:22'),
(7, 'Office Table – Wooden', 0, 3500.00, 'pcs', 49, NULL, 24, '2025-09-20 14:30:54'),
(8, 'Cellphone', 2, 5678.00, 'pcs', 4, NULL, 25, '2025-09-21 13:11:37'),
(9, 'Dell Unit', 2, 99000.00, 'unit', 4, NULL, NULL, '2025-09-21 18:14:32'),
(10, 'Ergonomic Office Chair', 2, 51000.00, 'unit', 4, NULL, 39, '2025-09-21 18:21:59'),
(11, 'Jetski', 2, 96780.00, 'unit', 4, NULL, 40, '2025-09-21 18:22:37'),
(12, 'Jetski', 2, 96780.00, 'unit', 4, NULL, NULL, '2025-09-21 18:25:03'),
(13, 'HIlux', 1, 1000000.00, 'roll', 4, NULL, NULL, '2025-09-21 18:28:12'),
(14, 'Car', 1, 4500000.00, 'unit', 4, NULL, NULL, '2025-09-21 18:33:35'),
(15, 'Mio Soul i', 1, 75000.00, 'unit', 4, 44, NULL, '2025-09-21 18:40:55'),
(16, 'Honda Click 125', 1, 75000.00, 'unit', 4, 45, NULL, '2025-09-21 18:51:18'),
(17, 'Hilux Van', 1, 7600000.00, 'unit', 4, 46, NULL, '2025-09-22 02:56:51'),
(18, 'Hilux van black', 1, 2300000.00, 'unit', 4, 47, NULL, '2025-09-22 03:04:09'),
(19, 'Lenovo AMD Ryzen 7', 1, 75000.00, 'unit', 4, 49, NULL, '2025-09-22 03:07:07'),
(20, 'Stylus', 0, 450.00, 'unit', 4, NULL, 26, '2025-09-22 17:31:24'),
(21, 'Computer', 2, 36500.00, 'unit', 4, NULL, 27, '2025-09-22 17:44:10'),
(22, 'Desktop', 2, 54000.00, 'unit', 0, 51, NULL, '2025-09-24 09:07:52'),
(23, 'Laptop', 1, 57945.00, 'unit', 0, 51, NULL, '2025-09-24 09:07:52'),
(24, 'Lenovo', 1, 56042.00, 'unit', 0, 52, NULL, '2025-09-24 09:55:27'),
(25, 'Computer', 1, 56098.00, 'unit', 0, 53, NULL, '2025-09-24 09:57:16'),
(26, 'Mouse', 5, 450.00, 'unit', 4, NULL, 28, '2025-09-24 10:47:53'),
(27, 'mouse pad', 5, 345.00, 'pcs', 4, NULL, 29, '2025-09-24 10:57:07'),
(28, 'Mouse', 2, 564.00, 'unit', 0, NULL, 30, '2025-09-24 15:15:41'),
(29, 'Mouse', 1, 453.00, 'unit', 4, NULL, 31, '2025-09-24 15:16:25'),
(30, 'Desktop Computer (Core i5)', 0, 546740.00, 'unit', 4, 55, NULL, '2025-09-24 15:18:16'),
(31, 'Lenovo', 0, 56000.00, 'unit', 0, 56, NULL, '2025-09-24 15:18:48'),
(32, 'Trash Can', 1, 350.00, 'pcs', 4, NULL, 32, '2025-09-25 07:54:48'),
(33, 'Laptop', 3, 45000.00, 'unit', 3, NULL, NULL, '2025-09-26 22:31:43'),
(34, 'Laptop', 3, 45000.00, 'unit', 3, NULL, NULL, '2025-09-26 22:44:24'),
(35, 'AIRCON ', 2, 18414.00, 'unit', 4, NULL, NULL, '2025-09-26 23:16:57'),
(36, 'AIRCON \\r\\n', 2, 18437.00, 'unit', 4, NULL, NULL, '2025-09-26 23:19:39'),
(37, 'Office Table', 3, 3577.65, 'set', 4, NULL, NULL, '2025-09-27 20:14:51'),
(38, 'Inventory Box', 2, 3569.00, 'unit', 4, NULL, NULL, '2025-09-28 05:41:40'),
(39, 'Office Table', 2, 2355.00, 'unit', 4, NULL, NULL, '2025-09-28 05:51:49'),
(40, 'Office Table', 2, 2355.00, 'unit', 4, NULL, NULL, '2025-09-28 05:53:22'),
(41, 'Office Chair', 1, 964.00, 'unit', 4, NULL, NULL, '2025-09-28 05:55:28'),
(42, 'Aircon Split Type', 1, 18305.00, 'unit', 4, NULL, NULL, '2025-09-28 06:00:28'),
(43, 'Power Generator', 1, 23984.00, 'unit', 4, NULL, NULL, '2025-09-28 06:13:44');

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
(266, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:48:41');

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
  `status` enum('pending','approved','rejected','borrowed','returned') NOT NULL DEFAULT 'pending',
  `requested_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `return_remarks` text DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `batch_id` int(11) DEFAULT NULL,
  `batch_item_id` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_requests`
--

INSERT INTO `borrow_requests` (`id`, `user_id`, `asset_id`, `office_id`, `status`, `requested_at`, `approved_at`, `return_remarks`, `returned_at`, `quantity`, `created_at`, `updated_at`, `batch_id`, `batch_item_id`, `expiry_date`) VALUES
(2, 19, 13, 4, 'returned', '2025-07-12 15:40:35', '2025-07-14 21:13:26', 'NEVER BEEN USED', '2025-07-14 21:13:47', 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(3, 19, 14, 4, 'pending', '2025-07-12 15:40:35', NULL, NULL, NULL, 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(4, 19, 2, 9, 'returned', '2025-07-12 15:42:36', '2025-07-14 09:54:28', 'slightly used', '2025-07-14 19:55:48', 0, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(5, 17, 2, 9, 'pending', '2025-07-13 15:15:18', NULL, NULL, NULL, 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(6, 17, 2, 9, 'returned', '2025-07-13 15:24:25', '2025-07-13 20:45:54', 'All goods', '2025-07-13 20:58:56', 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(7, 17, 2, 9, 'returned', '2025-07-14 04:23:59', '2025-07-14 21:00:24', 'Good condition', '2025-07-14 21:02:14', 0, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(8, 17, 13, 4, 'returned', '2025-07-14 14:49:24', '2025-07-14 19:50:05', 'Neve used', '2025-07-14 21:05:50', 0, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(9, 17, 3, 2, 'pending', '2025-08-20 08:09:14', NULL, NULL, NULL, 5, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(10, 17, 64, 9, 'pending', '2025-08-20 08:17:57', NULL, NULL, NULL, 3, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(11, 12, 64, 9, 'pending', '2025-08-20 08:24:23', NULL, NULL, NULL, 3, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(12, 17, 64, 9, 'pending', '2025-08-29 15:24:45', NULL, NULL, NULL, 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31', NULL, NULL, NULL),
(13, 17, 3, 4, 'pending', '2025-09-22 10:01:54', NULL, NULL, NULL, 1, '2025-09-22 08:01:54', '2025-09-22 08:01:54', NULL, NULL, NULL),
(14, 17, 5, 4, 'pending', '2025-09-22 10:01:54', NULL, NULL, NULL, 1, '2025-09-22 08:01:54', '2025-09-22 08:01:54', NULL, NULL, NULL),
(15, 17, 15, 4, 'pending', '2025-09-22 10:01:54', NULL, NULL, NULL, 3, '2025-09-22 08:01:54', '2025-09-22 08:01:54', NULL, NULL, NULL);

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
(1, 'Electronics', 'ECE', 1, 'asset'),
(2, 'Furniture', 'FUR', 1, 'asset'),
(3, 'Office Supplies', NULL, 1, 'consumables'),
(4, 'Vehicle', NULL, 1, 'asset'),
(5, 'Power Equipment', NULL, 1, 'asset'),
(6, 'IT Equipment', 'ICT', 1, 'asset'),
(7, 'Security Equipment', NULL, 1, 'asset');

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

--
-- Dumping data for table `consumption_log`
--

INSERT INTO `consumption_log` (`id`, `asset_id`, `office_id`, `quantity_consumed`, `recipient_user_id`, `dispensed_by_user_id`, `consumption_date`, `remarks`) VALUES
(4, 18, 0, 1, 24, 24, '2024-09-04 13:32:57', ''),
(5, 18, 0, 1, 24, 24, '2025-09-14 13:33:10', ''),
(6, 18, 0, 1, 24, 24, '2025-09-14 13:36:02', ''),
(7, 18, 0, 1, 24, 24, '2025-09-14 13:36:41', ''),
(8, 14, 3, 1, 24, 24, '2025-09-14 14:48:21', ''),
(9, 15, 3, 1, 24, 24, '2024-09-16 01:50:41', ''),
(10, 18, 3, 1, 24, 24, '2025-09-15 01:51:05', '');

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
(3, 'PROPERTY ACKNOWLEDGEMENT RECEIPT', 'PAR', 'par_form.php', '2025-08-05 02:17:00'),
(4, 'INVENTORY CUSTODIAN SLIP', 'ICS', 'ics_form.php', '2025-08-05 02:17:00'),
(6, 'REQUISITION & INVENTORY SLIP', 'RIS', 'ris_form.php', '2025-08-05 02:17:00'),
(7, 'INVENTORY & INSPECTION REPORT OF UNSERVICEABLE PROPERTY', 'IIRUP', 'iirup_form.php', '2025-08-12 12:53:40'),
(9, 'INVENTORY TRANFER RECEIPT', 'ITR', 'itr_form.php\r\n', '2025-09-24 08:32:02');

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
(104, 17, 4, 'Employee_MR_Report_20250928_113351.pdf', 0, '2025-09-28 14:33:52');

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
(1, '1758263261_Screenshot 2025-09-19 112710.png', '', '', 'ICS-2025-0001', '', '', '', '', '2025-09-19 06:27:41', 49),
(2, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0002', '', '', '', '', '2025-09-19 06:28:30', 49),
(3, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0003', '', '', '', '', '2025-09-19 06:31:22', 49),
(4, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0004', '', '', '', '', '2025-09-19 06:35:22', 4),
(5, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0004', '', '', '', '', '2025-09-19 06:36:05', 4),
(6, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0005', '', '', '', '', '2025-09-19 06:46:32', 3),
(7, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0006', '', '', '', '', '2025-09-19 06:51:23', 49),
(8, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0007', '', '', '', '', '2025-09-19 06:55:15', 49),
(9, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0008', '', '', '', '', '2025-09-19 11:33:16', 49),
(10, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0009', '', '', '', '', '2025-09-19 11:40:35', 49),
(11, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0010', '', '', '', '', '2025-09-19 11:45:41', 49),
(12, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0011', '', '', '', '', '2025-09-19 12:02:40', 49),
(13, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0012', '', '', '', '', '2025-09-19 12:59:12', 49),
(14, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0012', '', '', '', '', '2025-09-19 13:00:46', 49),
(15, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0012', '', '', '', '', '2025-09-19 13:01:05', 49),
(16, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0013', '', '', '', '', '2025-09-19 13:41:33', 49),
(17, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0014', '', '', '', '', '2025-09-19 14:20:38', 4),
(18, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0015', '', '', '', '', '2025-09-19 14:36:01', 4),
(19, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0016', '', '', '', '', '2025-09-19 15:04:07', 4),
(20, '1758263261_Screenshot 2025-09-19 112710.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0017', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-19 23:20:32', 4),
(21, '', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0017', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-19 23:24:36', 0),
(22, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0018', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-19 23:43:50', 4),
(23, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0019', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-20 09:30:22', 49),
(24, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0020', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-20 09:30:54', 49),
(25, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0021', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-21 08:11:37', 4),
(26, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'fc-001', 'ICS-2025-0022', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-22 12:31:24', 4),
(27, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'fc-001', 'ICS-2025-0023', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-22 12:44:10', 4),
(28, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'fc-001', 'ICS-2025-0023', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-24 02:47:53', 4),
(29, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'fc-001', 'ICS-2024-7564', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-24 02:57:07', 4),
(30, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'fc-001', 'ICS-2024-7564', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-24 07:15:41', 0),
(31, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'fc-001', 'ICS-2024-7564', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-24 07:16:25', 4),
(32, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'fc-001', 'ICS-2024-7564', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-24 23:54:48', 4);

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
(1, 17, 1, 'ICS-2025-0014', 2, 'pcs', 3500.00, 7000.00, 'Office Table – Wooden', '', '', '2025-09-19 14:20:38'),
(3, 18, 3, 'ICS-2025-0015', 2, 'pcs', 350.00, 700.00, 'Mouse', '', '', '2025-09-19 14:36:01'),
(4, 19, 5, 'ICS-2025-0016', 2, 'pcs', 4593.00, 9186.00, 'Printer Epson', '', '', '2025-09-19 15:04:07'),
(5, 19, 6, 'ICS-2025-0016', 1, 'pcs', 4593.00, 4593.00, 'Printer Epson', '', '', '2025-09-19 15:24:55'),
(7, 22, 8, 'ICS-2025-0018', 1, 'pcs', 4573.98, 4573.98, 'Desktop Computer – Intel i5, 8GB RAM, 256GB SSD', '', '', '2025-09-19 23:43:50'),
(10, 25, 19, 'ICS-2025-0021', 2, 'pcs', 5678.00, 11356.00, 'Cellphone', '', '', '2025-09-21 08:11:37'),
(11, 25, 20, 'ICS-2025-0021', 1, 'pcs', 5678.00, 5678.00, 'Cellphone', '', '', '2025-09-21 13:16:38'),
(12, 26, 37, 'ICS-2025-0022', 2, 'unit', 450.00, 900.00, 'Stylus', '', '', '2025-09-22 12:31:24'),
(13, 27, 40, 'ICS-2025-0023', 2, 'unit', 36500.00, 73000.00, 'Computer', '', '', '2025-09-22 12:44:10'),
(14, 27, 41, 'ICS-2025-0023', 1, 'unit', 36500.00, 36500.00, 'Computer', '', '', '2025-09-22 13:23:20'),
(15, 28, 49, 'ICS-2025-0023', 5, 'unit', 450.00, 2250.00, 'Mouse', 'ITM-55-1', '', '2025-09-24 02:47:53'),
(16, 29, 54, 'ICS-2024-7564', 5, 'pcs', 345.00, 1725.00, 'mouse pad', '', '', '2025-09-24 02:57:07'),
(17, 29, 55, 'ICS-2024-7564', 1, 'pcs', 345.00, 345.00, 'mouse pad', '', '', '2025-09-24 05:23:00'),
(18, 30, 59, 'ICS-2024-7564', 2, 'unit', 564.00, 1128.00, 'Mouse', '', '', '2025-09-24 07:15:42'),
(19, 31, 61, 'ICS-2024-7564', 2, 'unit', 453.00, 906.00, 'Mouse', '', '', '2025-09-24 07:16:25'),
(20, 32, 68, 'ICS-2024-7564', 1, 'pcs', 350.00, 350.00, 'Trash Can', '', '', '2025-09-24 23:54:48');

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
(1, '1755934207_Screenshot 2025-08-23 141806.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-08-13 04:55:42'),
(2, NULL, 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-08-29 13:48:25'),
(3, NULL, 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-08-29 13:49:18'),
(4, NULL, 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-08-29 13:50:45'),
(5, '1756475584_Screenshot 2025-08-29 204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-08-29 13:53:04'),
(7, NULL, 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-09-22 00:39:27'),
(8, NULL, 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-09-22 00:40:33'),
(9, NULL, 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-09-22 00:44:04'),
(10, 'iirup_header_1758502284_4daaf8e5_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-09-22 00:51:24'),
(11, NULL, 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-09-22 00:52:03'),
(12, 'iirup_header_1758502461_f55f217d_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-09-22 00:54:21'),
(13, NULL, 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-09-22 00:54:27'),
(14, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-09-22 00:57:00'),
(15, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-09-22 00:57:11'),
(16, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-22 01:01:17'),
(17, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-22 04:39:04'),
(18, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-22 04:55:47'),
(19, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-22 06:08:16'),
(20, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-22 07:29:25'),
(21, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-22 07:34:16'),
(22, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-22 10:41:47'),
(23, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-22 15:08:52'),
(24, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-23 03:27:29'),
(25, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-23 10:17:39'),
(26, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-24 05:34:31'),
(27, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-25 04:07:28');

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
(1, NULL, 34, '2025-09-22', 'Hilux van black', '', 1, 2300000.00, 2300000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 00:30:09'),
(2, 7, NULL, '2025-09-22', 'Hilux Van', '', 1, 7600000.00, 7600000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, '', '', '', '2025-09-22', '2025-09-22 00:39:27'),
(3, 8, 19, '2025-09-22', 'Cellphone', '', 1, 5678.00, 5678.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 00:40:33'),
(4, 9, 32, '2025-09-22', 'Honda Click 125', '', 1, 75000.00, 75000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 00:44:04'),
(5, 10, 27, '2025-09-22', 'Jetski', '', 1, 96780.00, 96780.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 00:51:24'),
(6, 11, 35, '2025-09-22', 'Lenovo AMD Ryzen 7', '', 1, 75000.00, 75000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 00:52:03'),
(7, 12, 23, '2025-09-22', 'Dell Unit', '', 1, 99000.00, 99000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 00:54:21'),
(8, 13, 35, '2025-09-22', 'Lenovo AMD Ryzen 7', '', 1, 75000.00, 75000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 00:54:27'),
(9, 14, 19, '2025-09-22', 'Cellphone', '', 1, 5678.00, 5678.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 00:57:00'),
(10, 15, 30, '2025-09-22', 'Car', '', 1, 4500000.00, 4500000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 00:57:11'),
(11, 16, 19, '2025-09-22', 'Cellphone', '', 1, 5678.00, 5678.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 01:01:17'),
(12, 17, 32, '2025-09-22', 'Honda Click 125', '', 1, 75000.00, 75000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 04:39:04'),
(13, 18, 20, '2025-09-22', 'Cellphone', '', 1, 5678.00, 5678.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 04:55:47'),
(14, 19, 32, '2025-09-22', 'Honda Click 125', 'No. PS-5S-03-F02-32', 1, 75000.00, 75000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 06:08:16'),
(15, 20, 1, '2025-09-22', 'Office Table – Wooden', '', 1, 3500.00, 3500.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 07:29:25'),
(16, 21, 33, '2025-09-22', 'Hilux Van', '', 1, 7600000.00, 7600000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 07:34:16'),
(17, 22, 5, '2025-09-22', 'Printer Epson', '', 1, 4593.00, 4593.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 10:41:47'),
(18, 23, 40, '2025-09-22', 'Computer', '', 1, 36500.00, 36500.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 15:08:52'),
(19, 24, 40, '2025-09-23', 'Computer', '', 1, 36500.00, 36500.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-23', '2025-09-23 03:27:29'),
(20, 25, 41, '2025-09-23', 'Computer', '', 1, 36500.00, 36500.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-23', '2025-09-23 10:17:39'),
(21, 26, 3, '2025-09-24', 'Mouse', '', 1, 350.00, 350.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-24', '2025-09-24 05:34:31'),
(22, 27, 54, '2025-09-25', 'mouse pad', 'PS-5S-03-F02-01-01', 1, 345.00, 345.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-25', '2025-09-25 04:07:28');

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
(1, '1758715006_Screenshot_2025-09-24_195610.png', '', '', 'MARK JAYSON NAMIA', 'Benjamin Thompson', '', '2025-09-24', 'reassignment', '', 'CAROLYN SY-REYES', 'OFFICE', '0000-00-00', 'MARK JAYSON NAMIA', 'OFFICE', '0000-00-00', 'BENJAMIN THOMPSON', 'OFFICE', '0000-00-00'),
(4, 'itr_header_1758722317_31784f28.png', '', '', 'MARK JAYSON NAMIA', 'Aurora Henderson', 'ITR-2025-001', '2025-09-24', 'reassignment', '', 'CAROLYN SY-REYES', 'OFFICE', '0000-00-00', 'MARK JAYSON NAMIA', 'OFFICE', '0000-00-00', 'BENJAMIN THOMPSON', 'OFFICE', '0000-00-00'),
(6, 'itr_header_1758722317_31784f28.png', '', '', 'MARK JAYSON NAMIA', 'Aurora Henderson', 'ITR-2025-001', '2025-09-24', 'reassignment', '', 'CAROLYN SY-REYES', 'OFFICE', '0000-00-00', 'MARK JAYSON NAMIA', 'OFFICE', '0000-00-00', 'BENJAMIN THOMPSON', 'OFFICE', '0000-00-00');

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
(3, 1, '2025-09-21', 'MR-2025-00033', 33, 'Hilux Van (MR-2025-00033)', 7600000.00, ''),
(4, 4, '2025-09-21', 'MR-2025-00033', 33, 'Hilux Van (MR-2025-00033)', 7600000.00, ''),
(5, 6, '2025-09-21', 'MR-2025-00033', 33, 'Hilux Van (MR-2025-00033)', 7600000.00, ''),
(6, 6, '2025-09-21', 'MR-2025-00033', 33, 'Hilux Van (MR-2025-00033)', 7600000.00, ''),
(7, 6, '2025-09-21', 'MR-2025-00033', 33, 'Hilux Van (MR-2025-00033)', 7600000.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `legal_documents`
--

CREATE TABLE `legal_documents` (
  `id` int(11) NOT NULL,
  `document_type` enum('privacy_policy','terms_of_service') NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
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
(1, 'privacy_policy', 'Privacy Policy', '<h6 class=\"fw-bold text-primary mb-3\">1. Information We Collect</h6>\r\n<p>When you use the PILAR Asset Inventory System, we collect the following information:</p>\r\n<ul>\r\n    <li><strong>Account Information:</strong> Username, full name, email address, and role assignments</li>\r\n    <li><strong>System Usage Data:</strong> Login times, asset management activities, and audit logs</li>\r\n    <li><strong>Technical Information:</strong> IP addresses, browser type, and session data for security purposes</li>\r\n    <li><strong>Asset Data:</strong> Information about assets you manage within the system</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. How We Use Your Information</h6>\r\n<p>We use your information to:</p>\r\n<ul>\r\n    <li>Provide and maintain the asset inventory management system</li>\r\n    <li>Authenticate users and maintain account security</li>\r\n    <li>Track asset movements and maintain audit trails</li>\r\n    <li>Send important system notifications and updates</li>\r\n    <li>Improve system functionality and user experience</li>\r\n    <li>Comply with legal and regulatory requirements</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. Information Sharing</h6>\r\n<p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>\r\n<ul>\r\n    <li>With authorized personnel within your organization</li>\r\n    <li>When required by law or legal process</li>\r\n    <li>To protect the security and integrity of our systems</li>\r\n    <li>With your explicit consent</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Data Security</h6>\r\n<p>We implement appropriate security measures to protect your information:</p>\r\n<ul>\r\n    <li>Encrypted password storage using industry-standard hashing</li>\r\n    <li>Secure session management with timeout controls</li>\r\n    <li>Regular security audits and monitoring</li>\r\n    <li>Access controls based on user roles and permissions</li>\r\n    <li>Secure data transmission using HTTPS protocols</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\r\n<p>If you have questions about this Privacy Policy or our data practices, please contact:</p>\r\n<div class=\"bg-light p-3 rounded\">\r\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\r\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\r\n    Phone: +1 (555) 123-4567<br>\r\n    Address: [Your Organization Address]\r\n</div>', '1.0', '2025-09-28', '2025-09-28 13:40:44', 1, 1, '2025-09-28 13:40:44'),
(2, 'terms_of_service', 'Terms of Service', '<h6 class=\"fw-bold text-primary mb-3\">1. Acceptance of Terms</h6>\r\n<p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. System Description</h6>\r\n<p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p>\r\n<ul>\r\n    <li>Track and manage organizational assets</li>\r\n    <li>Maintain detailed asset records and histories</li>\r\n    <li>Provide role-based access controls</li>\r\n    <li>Generate reports and analytics</li>\r\n    <li>Ensure compliance with asset management policies</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. User Accounts and Responsibilities</h6>\r\n<p><strong>Account Security:</strong></p>\r\n<ul>\r\n    <li>You are responsible for maintaining the confidentiality of your login credentials</li>\r\n    <li>You must notify administrators immediately of any unauthorized access</li>\r\n    <li>You agree to use strong passwords and enable security features when available</li>\r\n    <li>You are liable for all activities that occur under your account</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Prohibited Activities</h6>\r\n<p>You agree not to:</p>\r\n<ul>\r\n    <li>Attempt to gain unauthorized access to any part of the System</li>\r\n    <li>Interfere with or disrupt the System operation</li>\r\n    <li>Use the System for any illegal or unauthorized purpose</li>\r\n    <li>Reverse engineer, decompile, or disassemble any part of the System</li>\r\n    <li>Introduce viruses, malware, or other harmful code</li>\r\n</ul>\r\n\r\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\r\n<p>For questions about these Terms of Service, please contact:</p>\r\n<div class=\"bg-light p-3 rounded\">\r\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\r\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\r\n    Phone: +1 (555) 123-4567<br>\r\n    Address: [Your Organization Address]\r\n</div>', '1.0', '2025-09-28', '2025-09-28 13:40:44', 1, 1, '2025-09-28 13:40:44'),
(3, 'privacy_policy', 'Privacy Policy', '<h6 class=\"fw-bold text-primary mb-3\">1. Information We Collect</h6>\n<p>When you use the PILAR Asset Inventory System, we collect the following information:</p>\n<ul>\n    <li><strong>Account Information:</strong> Username, full name, email address, and role assignments</li>\n    <li><strong>System Usage Data:</strong> Login times, asset management activities, and audit logs</li>\n    <li><strong>Technical Information:</strong> IP addresses, browser type, and session data for security purposes</li>\n    <li><strong>Asset Data:</strong> Information about assets you manage within the system</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. How We Use Your Information</h6>\n<p>We use your information to:</p>\n<ul>\n    <li>Provide and maintain the asset inventory management system</li>\n    <li>Authenticate users and maintain account security</li>\n    <li>Track asset movements and maintain audit trails</li>\n    <li>Send important system notifications and updates</li>\n    <li>Improve system functionality and user experience</li>\n    <li>Comply with legal and regulatory requirements</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. Information Sharing</h6>\n<p>We do not sell, trade, or rent your personal information to third parties. We may share information only in the following circumstances:</p>\n<ul>\n    <li>With authorized personnel within your organization</li>\n    <li>When required by law or legal process</li>\n    <li>To protect the security and integrity of our systems</li>\n    <li>With your explicit consent</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Data Security</h6>\n<p>We implement appropriate security measures to protect your information:</p>\n<ul>\n    <li>Encrypted password storage using industry-standard hashing</li>\n    <li>Secure session management with timeout controls</li>\n    <li>Regular security audits and monitoring</li>\n    <li>Access controls based on user roles and permissions</li>\n    <li>Secure data transmission using HTTPS protocols</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\n<p>If you have questions about this Privacy Policy or our data practices, please contact:</p>\n<div class=\"bg-light p-3 rounded\">\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n    Phone: +1 (555) 123-4567<br>\n    Address: [Your Organization Address]\n</div>', '1.0', '2025-09-28', '2025-09-28 13:43:48', 1, 1, '2025-09-28 13:43:48'),
(4, 'terms_of_service', 'Terms of Service', '<h6 class=\"fw-bold text-primary mb-3\">1. Acceptance of Terms</h6>\n<p>By accessing and using the PILAR Asset Inventory System (\"the System\"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using the System.</p>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">2. System Description</h6>\n<p>The PILAR Asset Inventory System is a comprehensive asset management platform designed to:</p>\n<ul>\n    <li>Track and manage organizational assets</li>\n    <li>Maintain detailed asset records and histories</li>\n    <li>Provide role-based access controls</li>\n    <li>Generate reports and analytics</li>\n    <li>Ensure compliance with asset management policies</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">3. User Accounts and Responsibilities</h6>\n<p><strong>Account Security:</strong></p>\n<ul>\n    <li>You are responsible for maintaining the confidentiality of your login credentials</li>\n    <li>You must notify administrators immediately of any unauthorized access</li>\n    <li>You agree to use strong passwords and enable security features when available</li>\n    <li>You are liable for all activities that occur under your account</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">4. Prohibited Activities</h6>\n<p>You agree not to:</p>\n<ul>\n    <li>Attempt to gain unauthorized access to any part of the System</li>\n    <li>Interfere with or disrupt the System operation</li>\n    <li>Use the System for any illegal or unauthorized purpose</li>\n    <li>Reverse engineer, decompile, or disassemble any part of the System</li>\n    <li>Introduce viruses, malware, or other harmful code</li>\n</ul>\n\n<h6 class=\"fw-bold text-primary mb-3 mt-4\">5. Contact Information</h6>\n<p>For questions about these Terms of Service, please contact:</p>\n<div class=\"bg-light p-3 rounded\">\n    <strong>PILAR Asset Inventory System Administrator</strong><br>\n    Email: <a href=\"mailto:admin@pilar-system.com\">admin@pilar-system.com</a><br>\n    Phone: +1 (555) 123-4567<br>\n    Address: [Your Organization Address]\n</div>', '1.0', '2025-09-28', '2025-09-28 13:43:48', 1, 1, '2025-09-28 13:43:48');

-- --------------------------------------------------------

--
-- Table structure for table `legal_document_history`
--

CREATE TABLE `legal_document_history` (
  `id` int(11) NOT NULL,
  `document_id` int(11) NOT NULL,
  `document_type` enum('privacy_policy','terms_of_service') NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
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
(1, 1, 'Supply Office', 'Office Table – Wooden', '', '', 0, 0, 1.00, 'pcs', '2025-09-19', 3500.00, 'Juan A. Dela Cruz', NULL, '0000-00-00', '0000-00-00', '2025-09-19 14:23:56', 1, 'No. PS-5S-03-F02-01'),
(3, 3, 'Supply Office', 'Mouse', '', '', 0, 1, 1.00, 'pcs', '2025-09-19', 350.00, 'Juan A. Dela Cruz', NULL, '0000-00-00', '0000-00-00', '2025-09-19 14:36:44', 3, 'No. PS-5S-03-F02-03'),
(4, 4, 'Supply Office', 'Printer Epson', '', '', 0, 1, 1.00, 'pcs', '2025-09-19', 4593.00, 'Juan A. Dela Cruz', NULL, '0000-00-00', '0000-00-00', '2025-09-19 15:05:22', 5, 'No. PS-5S-03-F02-05'),
(5, 5, 'Supply Office', 'Printer Epson', '', '', 0, 0, 1.00, 'pcs', '2025-09-19', 4593.00, 'Juan A. Dela Cruz', NULL, '0000-00-00', '0000-00-00', '2025-09-19 15:24:55', 6, 'No. PS-5S-03-F02-06'),
(8, 10, 'Supply Office', 'Cellphone', '', '', 0, 0, 1.00, 'pcs', '2025-09-21', 5678.00, 'Juan A. Dela Cruz', NULL, '0000-00-00', '0000-00-00', '2025-09-21 08:12:19', 19, 'No. PS-5S-03-F02-19'),
(9, 11, 'Supply Office', 'Cellphone', '', '', 0, 0, 1.00, 'pcs', '2025-09-21', 5678.00, 'Maria Santos', NULL, '0000-00-00', '0000-00-00', '2025-09-21 13:16:38', 20, 'No. PS-5S-03-F02-20'),
(11, NULL, 'Supply Office', 'Honda', '', '', 0, 0, 1.00, 'unit', '2025-09-21', 75000.00, 'Maria Santos', NULL, '0000-00-00', '0000-00-00', '2025-09-21 13:53:13', 32, 'No. PS-5S-03-F02-32'),
(12, NULL, 'Supply Office', 'Hilux Van', '', '', 0, 0, 1.00, 'unit', '2025-09-21', 7600000.00, 'Aurora Henderson', 'Angela Rizal', '0000-00-00', '0000-00-00', '2025-09-21 21:57:07', 33, 'No. PS-5S-03-F02-33'),
(13, NULL, 'Supply Office', 'Hilux van black', '', '', 0, 0, 1.00, 'unit', '2025-09-22', 2300000.00, 'Ryan Bang', NULL, '0000-00-00', '0000-00-00', '2025-09-21 22:04:25', 34, 'No. PS-5S-03-F02-34'),
(14, 13, 'Supply Office', 'Computer', '', '', 0, 1, 1.00, 'unit', '2025-09-22', 36500.00, 'Maria Santos', NULL, '0000-00-00', '0000-00-00', '2025-09-22 13:04:03', 40, 'No. PS-5S-03-F02-40'),
(15, 14, 'Supply Office', 'Computer', '', '', 0, 1, 1.00, 'unit', '2025-09-22', 36500.00, 'Maria Santos', NULL, '0000-00-00', '0000-00-00', '2025-09-22 13:23:20', 41, 'No. PS-5S-03-F02-41'),
(16, 16, 'Supply Office', 'mouse pad', '', '', 0, 1, 1.00, 'pcs', '2025-09-24', 345.00, 'Daniel Wilson', 'Roberto Cruz', '0000-00-00', '0000-00-00', '2025-09-24 05:17:52', 54, 'PS-5S-03-F02-01-01'),
(17, 17, 'Supply Office', 'mouse pad', '', '', 1, 0, 1.00, 'pcs', '2025-09-24', 345.00, 'Juan A. Dela Cruz', 'Roberto Cruz', '0000-00-00', '0000-00-00', '2025-09-24 05:23:00', 55, 'PS-5S-03-F02-01-01'),
(18, 20, 'Supply Office', 'Trash Can', '', '', 1, 0, 1.00, 'pcs', '2025-09-25', 350.00, 'Emily Johnson', 'Roberto Cruz', '0000-00-00', '0000-00-00', '2025-09-24 23:56:06', 68, 'PS-5S-03-F02-01-01'),
(19, NULL, 'OMASS', 'Laptop', 'XPS 15', 'SN-ABC123', 1, 0, 1.00, 'unit', '2025-01-09', 45000.00, 'Emily Johnson', 'John Smith', '2025-01-09', '2025-01-09', '2025-09-26 14:44:24', 72, 'INV-0001'),
(20, NULL, 'OMASS', 'Laptop', 'XPS 15', 'SN-ABC123', 1, 0, 1.00, 'unit', '2025-01-09', 45000.00, 'Emily Johnson', 'John Smith', '2025-01-09', '2025-01-09', '2025-09-26 14:44:24', 73, 'INV-0001'),
(21, NULL, 'OMASS', 'Laptop', 'XPS 15', 'SN-ABC123', 1, 0, 1.00, 'unit', '2025-01-09', 45000.00, 'Emily Johnson', 'John Smith', '2025-01-09', '2025-01-09', '2025-09-26 14:44:24', 74, 'INV-0001'),
(22, NULL, 'Supply Office', 'AIRCON \\r\\n', 'SPLIT TYPE', '', 1, 0, 1.00, 'unit', '2025-09-26', 18437.00, 'Benjamin Thompson', 'Angela Rizal', '2025-09-26', '2025-09-26', '2025-09-26 15:19:39', 75, 'INV-0001'),
(23, NULL, 'Supply Office', 'AIRCON \\r\\n', 'SPLIT TYPE', '', 1, 0, 1.00, 'unit', '2025-09-26', 18437.00, 'Benjamin Thompson', 'Angela Rizal', '2025-09-26', '2025-09-26', '2025-09-26 15:19:39', 76, 'INV-0001'),
(24, NULL, 'Supply Office', 'Office Table', 'Mesh Back', '', 1, 0, 1.00, 'set', '2025-09-27', 3577.65, 'Aria Edwards', 'John Legend', '2025-09-27', '2025-09-27', '2025-09-27 15:14:51', 91, 'INV-0001'),
(25, NULL, 'Supply Office', 'Office Table', 'Mesh Back', '', 1, 0, 1.00, 'set', '2025-09-27', 3577.65, 'Aria Edwards', 'John Legend', '2025-09-27', '2025-09-27', '2025-09-27 15:14:51', 92, 'INV-0001'),
(26, NULL, 'Supply Office', 'Office Table', 'Mesh Back', '', 1, 0, 1.00, 'set', '2025-09-27', 3577.65, 'Aria Edwards', 'John Legend', '2025-09-27', '2025-09-27', '2025-09-27 15:14:51', 93, 'INV-0001'),
(27, NULL, 'Supply Office', 'Inventory Box', 'Mesh Back', 'SN-DC-2025-0001', 1, 0, 1.00, 'unit', '2025-09-28', 3569.00, 'Aurora Rivera', 'Jack Robertson', '0000-00-00', '0000-00-00', '2025-09-28 00:41:40', 94, 'PS-5S-03-F02-01-01'),
(28, NULL, 'Supply Office', 'Inventory Box', 'Mesh Back', 'SN-DC-2025-0001', 1, 0, 1.00, 'unit', '2025-09-28', 3569.00, 'Aurora Rivera', 'Jack Robertson', '2025-09-28', '2025-09-28', '2025-09-28 00:41:40', 95, 'INV-0001'),
(29, NULL, 'Supply Office', 'Office Table', 'Mesh Back', 'SN-DC-2025-0001', 1, 0, 1.00, 'unit', '2025-09-28', 2355.00, 'Amelia Lewis', 'Roberto Cruz', '2025-09-28', '2025-09-28', '2025-09-28 00:53:22', 96, 'INV-0001'),
(30, NULL, 'Supply Office', 'Office Table', 'Mesh Back', 'SN-DC-2025-0001', 1, 0, 1.00, 'unit', '2025-09-28', 2355.00, 'Amelia Lewis', 'Roberto Cruz', '2025-09-28', '2025-09-28', '2025-09-28 00:53:22', 97, 'INV-0001'),
(31, NULL, 'Supply Office', 'Office Chair', 'Mesh Back', 'SN-DC-2025-0002', 1, 0, 1.00, 'unit', '2025-09-28', 964.00, 'Brooklyn Torres', 'John Legend', '2025-09-28', '2025-09-28', '2025-09-28 00:55:28', 98, 'INV-0001'),
(32, NULL, 'Supply Office', 'Aircon Split Type', 'SPLIT TYPE', 'SN-DC-2025-0001', 1, 0, 1.00, 'unit', '2025-09-28', 18305.00, 'Ava White', 'Jake Paul', '2025-09-28', '2025-09-28', '2025-09-28 01:00:28', 99, 'INV-0001'),
(33, NULL, 'IT Office', 'Power Generator', '', 'SN-ABC123456', 1, 0, 1.00, 'unit', '2025-09-28', 23984.00, 'Brayden Gonzales', 'Angela Rizal', '0000-00-00', '0000-00-00', '2025-09-28 01:13:44', 100, 'PS-5S-03-F02-01-01');

-- --------------------------------------------------------

--
-- Table structure for table `offices`
--

CREATE TABLE `offices` (
  `id` int(11) NOT NULL,
  `office_name` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `offices`
--

INSERT INTO `offices` (`id`, `office_name`, `icon`) VALUES
(1, 'MPDC', NULL),
(2, 'IT Office', NULL),
(3, 'OMASS', NULL),
(4, 'Supply Office', NULL),
(5, 'OMAD', NULL),
(7, 'RHU Office', NULL),
(9, 'Main', NULL),
(11, 'OMSWD', NULL),
(13, 'OBAC', NULL),
(14, 'COA', NULL),
(15, 'COMELEC', NULL),
(16, 'CSOLAR', NULL),
(17, 'DILG', NULL),
(18, 'MENRU', NULL),
(19, 'GAD', NULL),
(20, 'GS-Motorpool', NULL),
(21, 'ABC', NULL),
(22, 'SEF-DEPED', NULL),
(23, 'HRMO', NULL),
(24, 'KALAHI', NULL),
(25, 'LIBRARY', NULL),
(26, 'OMAC', NULL),
(27, 'OMA', NULL),
(28, 'OMBO', NULL),
(29, 'MCR', NULL),
(30, 'MDRRMO', NULL),
(31, 'OME', NULL),
(32, 'MHO', NULL),
(33, 'OMM', NULL),
(34, 'MTC', NULL),
(35, 'MTO-PORT-MARKET', NULL),
(36, 'NCDC', NULL),
(37, 'OSCA', NULL),
(38, 'PAO', NULL),
(39, 'PiCC', NULL),
(40, 'PIHC', NULL),
(41, 'PIO-PESO', NULL),
(42, 'PNP', NULL),
(43, 'SB', NULL),
(44, 'SB-SEC', NULL),
(45, 'SK', NULL),
(46, 'TOURISM', NULL),
(47, 'OVM', NULL),
(48, 'BPLO', NULL),
(49, '7K', NULL);

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
(3, 0, 3, NULL, NULL, '', '', NULL, 'DepEd', 'FC-2025-001', 'PAR-0001', '2025-09-15 14:26:34', '0000-00-00', '0000-00-00'),
(4, 0, 3, NULL, NULL, '', '', NULL, 'DepEd', 'FC-2025-001', 'PAR-0001', '2025-09-15 14:31:12', '0000-00-00', '0000-00-00'),
(5, 0, 3, NULL, NULL, 'ivan christoper millabas', 'mark jayson namia', NULL, 'LGU', 'FC-2025-001', 'PAR-0002', '2025-09-15 14:35:52', NULL, NULL),
(6, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', NULL, 'LGU', 'FC-2025-001', 'PAR-0003', '2025-09-15 14:47:50', NULL, NULL),
(7, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', NULL, 'LGU', 'FC-2025-001', 'PAR-0003', '2025-09-15 14:48:18', NULL, NULL),
(8, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', NULL, 'LGU', 'FC-2025-001', 'PAR-0004', '2025-09-15 15:10:33', NULL, NULL),
(10, 3, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0005', '2025-09-16 02:34:56', NULL, NULL),
(11, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0006', '2025-09-16 09:04:15', NULL, NULL),
(12, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0007', '2025-09-16 09:08:48', '2025-09-16', '2025-09-16'),
(13, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0008', '2025-09-16 09:10:44', '2025-09-16', '2025-09-16'),
(14, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0009', '2025-09-16 09:43:27', '2025-09-16', '2025-09-16'),
(15, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0009', '2025-09-16 09:48:35', '2025-09-16', '2025-09-16'),
(16, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0010', '2025-09-16 09:48:44', '2025-09-16', '2025-09-16'),
(17, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0010', '2025-09-16 09:49:27', '2025-09-16', '2025-09-16'),
(18, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0010', '2025-09-16 09:50:18', '2025-09-16', '2025-09-16'),
(19, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0011', '2025-09-16 09:51:55', '2025-09-16', '2025-09-16'),
(20, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0012', '2025-09-16 09:57:03', '2025-09-16', '2025-09-16'),
(21, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0012', '2025-09-16 09:58:10', '2025-09-16', '2025-09-16'),
(22, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0013', '2025-09-16 10:10:23', '2025-09-16', '2025-09-16'),
(23, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0014', '2025-09-16 10:12:34', '2025-09-16', '2025-09-16'),
(24, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0015', '2025-09-16 10:16:11', '2025-09-16', '2025-09-16'),
(27, 0, 3, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0016', '2025-09-16 10:28:47', '2025-09-16', '2025-09-16'),
(34, 0, NULL, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0017', '2025-09-16 10:36:33', '2025-09-16', '2025-09-16'),
(35, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0018', '2025-09-16 11:46:43', '2025-09-16', '2025-09-16'),
(36, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0019', '2025-09-21 12:56:37', '2025-09-21', '2025-09-21'),
(37, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0020', '2025-09-21 12:59:18', '2025-09-21', '2025-09-21'),
(38, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0021', '2025-09-21 13:14:32', '2025-09-21', '2025-09-21'),
(39, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0022', '2025-09-21 13:21:59', '2025-09-21', '2025-09-21'),
(40, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0022', '2025-09-21 13:22:37', '2025-09-21', '2025-09-21'),
(41, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0022', '2025-09-21 13:25:03', '2025-09-21', '2025-09-21'),
(42, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0023', '2025-09-21 13:28:12', '2025-09-21', '2025-09-21'),
(43, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0024', '2025-09-21 13:33:35', '2025-09-21', '2025-09-21'),
(44, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0025', '2025-09-21 13:40:55', '2025-09-21', '2025-09-21'),
(45, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0026', '2025-09-21 13:51:18', '2025-09-21', '2025-09-21'),
(46, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0027', '2025-09-21 21:56:51', '2025-09-21', '2025-09-21'),
(47, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0028', '2025-09-21 22:04:09', '2025-09-22', '2025-09-22'),
(48, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0029', '2025-09-21 22:06:40', '2025-09-22', '2025-09-22'),
(49, 0, 4, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0030', '2025-09-21 22:07:07', '2025-09-22', '2025-09-22'),
(50, 0, NULL, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'LGU-PAR-2025-0001', '2025-09-24 01:05:11', '2025-09-24', '2025-09-24'),
(51, 0, NULL, NULL, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'LGU-PAR-2025-0001', '2025-09-24 01:07:52', '2025-09-24', '2025-09-24'),
(52, 0, NULL, '', '', 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'LGU-PAR-2025-0001', '2025-09-24 01:55:27', '2025-09-24', '2025-09-24'),
(53, 0, NULL, 'MARK JAYSON NAMIA', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'LGU-PAR-2025-0001', '2025-09-24 01:57:16', '2025-09-24', '2025-09-24'),
(54, 0, NULL, 'MARK JAYSON NAMIA', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'LGU-PAR-2025-0001', '2025-09-24 07:17:18', '2025-09-24', '2025-09-24'),
(55, 0, 4, 'MARK JAYSON NAMIA', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'LGU-PAR-2025-0001', '2025-09-24 07:18:16', '2025-09-24', '2025-09-24'),
(56, 0, NULL, 'MARK JAYSON NAMIA', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'LGU-PAR-2025-0001', '2025-09-24 07:18:48', '2025-09-24', '2025-09-24');

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
(1, 5, 4, 1, 'pcs', 'Ergonomic Office Chair', 'PROP-0003', '2025-09-01', 6500.00, 6500.00),
(2, 6, 6, 1, 'pcs', 'Desktop Computer – Intel i5, 8GB RAM, 256GB SSD', 'PROP-0005', '2025-09-06', 35000.00, 35000.00),
(3, 7, 6, 1, 'pcs', 'Desktop Computer – Intel i5, 8GB RAM, 256GB SSD', 'PROP-0005', '2025-09-06', 35000.00, 35000.00),
(4, 8, 27, 1, 'unit', 'Lenovo', 'STOCK-0017', '2025-09-15', 52000.00, 52000.00),
(5, 11, 27, 1, 'unit', 'Lenovo', 'STOCK-0017', '2025-09-15', 52000.00, 52000.00),
(6, 12, 27, 1, 'unit', 'Lenovo', 'STOCK-0017', '2025-09-15', 52000.00, 52000.00),
(7, 13, 27, 1, 'unit', 'Lenovo', 'STOCK-0017', '2025-09-15', 52000.00, 52000.00),
(8, 14, 27, 1, 'unit', 'Lenovo', 'STOCK-0017', '2025-09-15', 52000.00, 52000.00),
(9, 18, 29, 1, 'unit', 'Lenovo', 'STOCK-0017', '2025-09-15', 52000.00, 0.00),
(10, 19, 29, 1, 'unit', 'Lenovo', 'STOCK-0017', '2025-09-15', 52000.00, 52000.00),
(11, 24, 31, 1, 'unit', 'Desktop Computer (Core i5)', 'STOCK-0017', '2025-09-16', 55000.00, 55000.00),
(12, 27, 31, 1, 'unit', 'Desktop Computer (Core i5)', 'STOCK-0017', '2025-09-16', 55000.00, 55000.00),
(13, 34, 33, 1, 'unit', 'Air Conditioner 2HP Split', 'STOCK-0017', '2025-09-16', 51000.00, 51000.00),
(14, 35, 30, 1, 'unit', 'Desktop Computer (Core i5)', 'STOCK-0017', '2025-09-16', 55000.00, 55000.00),
(15, 38, 23, 2, 'unit', 'Dell Unit', '', '0000-00-00', 99000.00, 198000.00),
(16, 41, 27, 2, 'unit', 'Jetski', '', '0000-00-00', 96780.00, 193560.00),
(17, 42, 29, 1, 'roll', 'HIlux', '', '0000-00-00', 1000000.00, 1000000.00),
(18, 43, 30, 1, 'unit', 'Car', '', '0000-00-00', 4500000.00, 4500000.00),
(19, 44, 31, 1, 'unit', 'Mio Soul i', '', '0000-00-00', 75000.00, 75000.00),
(20, 45, 32, 1, 'unit', 'Honda Click 125', '', '0000-00-00', 75000.00, 75000.00),
(21, 46, 33, 1, 'unit', 'Hilux Van', '', '0000-00-00', 7600000.00, 7600000.00),
(22, 47, 34, 1, 'unit', 'Hilux van black', '', '0000-00-00', 2300000.00, 2300000.00),
(23, 49, 35, 2, 'unit', 'Lenovo AMD Ryzen 7', '', '0000-00-00', 75000.00, 150000.00),
(24, 51, 44, 2, 'unit', 'Desktop', 'LGU-2025-ICT-0001', '0000-00-00', 54000.00, 108000.00),
(25, 51, 46, 1, 'unit', 'Laptop', 'LGU-2025-ICT-0002', '0000-00-00', 57945.00, 57945.00),
(26, 52, 47, 1, 'unit', 'Lenovo', 'LGU-2025-ICT-0001', '0000-00-00', 56042.00, 56042.00),
(27, 53, 48, 1, 'unit', 'Computer', 'LGU-2025-ICT-0001', '0000-00-00', 56098.00, 56098.00),
(28, 55, 63, 3, 'unit', 'Desktop Computer (Core i5)', '', '0000-00-00', 546740.00, 1640220.00),
(29, 56, 66, 2, 'unit', 'Lenovo', '', '0000-00-00', 56000.00, 112000.00);

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
(1, 'PS-5S-03-F01-01-01', 19, 16, '2025-09-22', 17, 'Supply Offices', 'Cellphone (MR-2025-00019)', 'Broken', 'For Disposal', 'Pending', '2025-09-22 02:48:14', '2025-09-22 03:23:20'),
(2, 'PS-5S-03-F01-01-02', 20, 18, '2025-09-22', 17, 'Supply Office', 'Cellphone (MR-2025-00020)', 'Marupok', 'For Donation', 'Pending', '2025-09-22 05:10:09', '2025-09-22 05:10:09'),
(3, 'PS-5S-03-F01-01-03', 33, 21, '2025-09-22', 17, 'Garage', 'Hilux Van (MR-2025-00033)', 'Not in use', 'For Relocation', 'Pending', '2025-09-22 07:35:32', '2025-09-22 07:35:32'),
(4, 'PS-5S-03-F01-01-04', 5, 22, '2025-09-22', 17, 'Supply Office', 'Printer Epson (MR-2025-00005)', 'Not in use', 'For Disposal', 'Pending', '2025-09-22 11:32:35', '2025-09-22 11:32:35'),
(5, 'PS-5S-03-F01-01-05', 40, 23, '2025-09-23', 17, 'Garage', 'Computer (MR-2025-00040)', 'Unnecessary', 'For Disposal', 'Pending', '2025-09-23 10:17:04', '2025-09-23 10:17:04'),
(7, 'PS-5S-03-F01-01-01', 41, 25, '2025-09-24', 17, 'Supply Office', 'Computer (MR-2025-00041)', 'Unnecessary', 'For Disposal', 'Pending', '2025-09-24 05:25:32', '2025-09-24 05:25:32');

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
(3, 6, 11, '1755867841_Screenshot 2025-08-22 103403.png', 'v', '', '', 'ris-001', 'sAI-001', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-08-22 12:58:03', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(7, 0, 11, NULL, 'v', '', '', 'RIS-2025-0002', 'SAI-2025-0002', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-09-08 13:34:18', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(8, 0, 11, NULL, 'v', '', '', 'RIS-2025-0003', 'SAI-2025-0003', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-09-08 13:41:03', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(9, 6, 11, NULL, 'v', '', '', 'ris-001', 'sAI-001', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-09-08 13:46:38', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(10, 6, 11, NULL, 'v', '', '', 'ris-001', 'sAI-001', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-09-08 13:55:45', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(11, 6, 11, NULL, 'v', '', '', 'ris-001', 'sAI-001', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-09-08 13:58:45', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(12, 6, 11, NULL, 'v', '', '', 'ris-001', 'sAI-001', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-09-08 14:10:16', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(13, 6, 11, '', 'v', '', '', 'ris-001', 'sAI-001', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-09-08 14:22:20', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(14, 6, 11, NULL, 'v', '', '', 'ris-001', 'sAI-001', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-09-08 14:35:25', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(15, 6, 11, NULL, 'v', '', '', 'ris-001', 'sAI-001', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-09-08 14:37:32', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(16, 6, 11, NULL, 'v', '', '', 'ris-001', 'sAI-001', '2025-08-22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'ROY L. RICACHO', 'CLERK', '2025-08-22', '0000-00-00', '', '2025-09-08 14:41:03', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-08-22', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-08-22'),
(17, 6, 11, NULL, 'v', '', '', 'RIS-2025-0012', 'SAI-2025-0012', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-08', 'ROY L. RICACHO', 'CLERK', '2025-09-08', '0000-00-00', '', '2025-09-08 14:52:40', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-08', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-08'),
(18, 6, 11, NULL, 'v', '', '', 'RIS-2025-0013', 'SAI-2025-0013', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-08', 'ROY L. RICACHO', 'CLERK', '2025-09-08', '0000-00-00', '', '2025-09-08 14:53:02', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-08', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-08'),
(19, 6, 11, NULL, 'v', '', '', 'RIS-2025-0014', 'SAI-2025-0014', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-08', 'ROY L. RICACHO', 'CLERK', '2025-09-08', '0000-00-00', '', '2025-09-08 14:58:44', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-08', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-08'),
(20, 6, 11, NULL, 'v', '', '', 'RIS-2025-0015', 'SAI-2025-0015', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'ROY L. RICACHO', 'CLERK', '2025-09-09', '0000-00-00', '', '2025-09-09 00:38:29', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-09'),
(21, 6, 11, NULL, 'v', '', '', 'RIS-2025-0016', 'SAI-2025-0016', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'ROY L. RICACHO', 'CLERK', '2025-09-09', '0000-00-00', '', '2025-09-09 00:44:29', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-09'),
(22, 6, 11, '1757378730_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0017', 'SAI-2025-0017', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'ROY L. RICACHO', 'CLERK', '2025-09-09', '0000-00-00', '', '2025-09-09 00:45:30', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-09'),
(23, 6, 11, NULL, 'v', '', '', 'RIS-2025-0018', 'SAI-2025-0018', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'ROY L. RICACHO', 'CLERK', '2025-09-09', '0000-00-00', '', '2025-09-09 00:48:25', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-09'),
(24, 6, 11, '1757378941_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0019', 'SAI-2025-0019', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'ROY L. RICACHO', 'CLERK', '2025-09-09', '0000-00-00', '', '2025-09-09 00:49:01', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-09'),
(25, 6, 11, NULL, 'v', '', '', 'RIS-2025-0020', 'SAI-2025-0020', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'ROY L. RICACHO', 'CLERK', '2025-09-09', '0000-00-00', '', '2025-09-09 00:51:25', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-09'),
(26, 6, 11, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0021', 'SAI-2025-0021', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'ROY L. RICACHO', 'CLERK', '2025-09-09', '0000-00-00', '', '2025-09-09 00:59:32', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-09'),
(27, 6, 11, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0022', 'SAI-2025-0022', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'ROY L. RICACHO', 'CLERK', '2025-09-09', '0000-00-00', '', '2025-09-09 00:59:37', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-09'),
(28, 6, 11, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0023', 'SAI-2025-0023', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'ROY L. RICACHO', 'CLERK', '2025-09-09', '0000-00-00', '', '2025-09-09 01:12:53', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-09', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-09'),
(29, 6, 11, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0024', 'SAI-2025-0024', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 07:56:45', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(30, 6, 11, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0025', 'SAI-2025-0025', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 08:00:56', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(31, 6, 11, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0026', 'SAI-2025-0026', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 08:01:41', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(32, 6, 11, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0027', 'SAI-2025-0027', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 08:02:03', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(33, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0028', 'SAI-2025-0028', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 08:04:17', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(34, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0029', 'SAI-2025-0029', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 14:18:13', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(35, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0030', 'SAI-2025-0030', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 14:32:26', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(36, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0031', 'SAI-2025-0031', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 14:35:03', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(37, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0032', 'SAI-2025-0032', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 14:35:32', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(38, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0033', 'SAI-2025-0033', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 14:44:31', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(39, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0033', 'SAI-2025-0033', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 14:46:12', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(40, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0035', 'SAI-2025-0035', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 14:46:24', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(41, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0035', 'SAI-2025-0035', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 14:47:33', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(42, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0035', 'SAI-2025-0035', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'ROY L. RICACHO', 'CLERK', '2025-09-10', '0000-00-00', '', '2025-09-10 15:03:20', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-10', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-10'),
(43, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0038', 'SAI-2025-0038', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'ROY L. RICACHO', 'CLERK', '2025-09-11', '0000-00-00', '', '2025-09-11 14:09:00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-11'),
(44, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0038', 'SAI-2025-0038', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'ROY L. RICACHO', 'CLERK', '2025-09-11', '0000-00-00', '', '2025-09-11 14:10:34', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-11'),
(45, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0038', 'SAI-2025-0038', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'ROY L. RICACHO', 'CLERK', '2025-09-11', '0000-00-00', '', '2025-09-11 14:15:43', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-11'),
(46, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0038', 'SAI-2025-0038', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'ROY L. RICACHO', 'CLERK', '2025-09-11', '0000-00-00', '', '2025-09-11 14:28:42', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-11'),
(47, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0042', 'SAI-2025-0042', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'ROY L. RICACHO', 'CLERK', '2025-09-11', '0000-00-00', '', '2025-09-11 14:29:29', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-11'),
(48, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0043', 'SAI-2025-0043', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'ROY L. RICACHO', 'CLERK', '2025-09-11', '0000-00-00', '', '2025-09-11 14:35:45', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-11'),
(49, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0044', 'SAI-2025-0044', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'ROY L. RICACHO', 'CLERK', '2025-09-11', '0000-00-00', '', '2025-09-11 14:49:23', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-11'),
(50, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0045', 'SAI-2025-0045', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'ROY L. RICACHO', 'CLERK', '2025-09-11', '0000-00-00', 'For printing', '2025-09-11 15:45:53', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-11', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-11'),
(51, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0046', 'SAI-2025-0046', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-12', 'ROY L. RICACHO', 'CLERK', '2025-09-12', '0000-00-00', 'For printing', '2025-09-12 00:15:55', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-12', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-12'),
(52, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0047', 'SAI-2025-0047', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-12', 'ROY L. RICACHO', 'CLERK', '2025-09-12', '0000-00-00', 'For printing', '2025-09-12 00:18:25', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-12', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-12'),
(53, 6, 3, '1757379572_Screenshot_2025-08-29_204458.png', 'v', '', '', 'RIS-2025-0048', 'SAI-2025-0048', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-12', 'ROY L. RICACHO', 'CLERK', '2025-09-12', '0000-00-00', 'For printing', '2025-09-12 00:22:58', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-12', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-12'),
(54, 6, 33, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', 'RIS-2025-0049', 'SAI-2025-0049', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 05:22:17', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(55, 6, 33, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', 'RIS-2025-0050', 'SAI-2025-0050', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 05:22:37', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(56, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', 'RIS-2025-0051', 'SAI-2025-0051', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 05:24:22', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(57, 6, 29, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', 'RIS-2025-0052', 'SAI-2025-0052', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 05:33:34', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(58, 6, 29, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', 'RIS-2025-0052', 'SAI-2025-0052', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 05:34:10', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(59, 6, 29, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', 'RIS-2025-0052', 'SAI-2025-0052', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 05:35:52', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(60, 6, 49, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', 'RIS-2025-0055', 'SAI-2025-0055', '0000-00-00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 07:12:12', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(61, 6, 21, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', '0', 'SAI-2025-0056', '2025-09-13', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 07:36:03', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(62, 6, 48, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', '0', 'SAI-2025-0057', '2025-09-13', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 07:37:10', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(63, 6, 14, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', 'RIS-2025-0058', 'SAI-2025-0058', '2025-09-13', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 07:39:13', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(64, 6, 14, '1757740937_Screenshot_2025-09-13_132057.png', 'v', '', '', 'RIS-2025-0059', 'SAI-2025-0059', '2025-09-13', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 13:05:03', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(65, 6, 48, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0060', 'SAI-2025-0060', '2025-09-13', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'ROY L. RICACHO', 'CLERK', '2025-09-13', '0000-00-00', 'For printing', '2025-09-13 13:07:46', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-13', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-13'),
(66, 6, 48, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0061', 'SAI-2025-0061', '2025-09-14', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-14', 'ROY L. RICACHO', 'CLERK', '2025-09-14', '0000-00-00', 'For printing', '2025-09-14 13:23:14', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-14', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-14'),
(67, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0062', 'SAI-2025-0062', '2025-09-14', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-14', 'ROY L. RICACHO', 'CLERK', '2025-09-14', '0000-00-00', 'For printing', '2025-09-14 13:23:30', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-14', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-14'),
(68, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0063', 'SAI-2025-0063', '2025-09-14', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-14', 'ROY L. RICACHO', 'CLERK', '2025-09-14', '0000-00-00', 'For printing', '2025-09-14 13:24:17', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-14', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-14'),
(69, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0064', 'SAI-2025-0064', '2025-09-14', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-14', 'ROY L. RICACHO', 'CLERK', '2025-09-14', '0000-00-00', 'For printing', '2025-09-14 13:27:45', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-14', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-14'),
(70, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0065', 'SAI-2025-0065', '2025-09-20', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-20', 'ROY L. RICACHO', 'CLERK', '2025-09-20', '0000-00-00', 'For printing', '2025-09-20 00:23:54', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-20', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-20'),
(71, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0066', 'SAI-2025-0066', '2025-09-20', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-20', 'ROY L. RICACHO', 'CLERK', '2025-09-20', '0000-00-00', 'For printing', '2025-09-20 00:45:24', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-20', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-20'),
(72, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0067', 'SAI-2025-0067', '2025-09-20', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-20', 'ROY L. RICACHO', 'CLERK', '2025-09-20', '0000-00-00', 'For printing', '2025-09-20 01:24:02', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-20', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-20'),
(73, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0068', 'SAI-2025-0068', '2025-09-20', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-20', 'ROY L. RICACHO', 'CLERK', '2025-09-20', '0000-00-00', 'For printing', '2025-09-20 14:40:24', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-20', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-20'),
(74, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0069', 'SAI-2025-0069', '2025-09-21', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-21', 'ROY L. RICACHO', 'CLERK', '2025-09-21', '0000-00-00', 'For printing', '2025-09-21 09:03:46', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-21', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-21'),
(75, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0070', 'SAI-2025-0070', '2025-09-21', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-21', 'ROY L. RICACHO', 'CLERK', '2025-09-21', '0000-00-00', 'For printing', '2025-09-21 09:27:23', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-21', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-21'),
(76, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0071', 'SAI-2025-0071', '2025-09-23', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-23', 'ROY L. RICACHO', 'CLERK', '2025-09-23', '0000-00-00', 'For printing', '2025-09-23 03:42:36', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-23', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-23'),
(77, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0072', 'SAI-2025-0072', '2025-09-23', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-23', 'ROY L. RICACHO', 'CLERK', '2025-09-23', '0000-00-00', 'For printing', '2025-09-23 06:03:11', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-23', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-23'),
(78, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0073', 'SAI-2025-0073', '2025-09-27', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'ROY L. RICACHO', 'CLERK', '2025-09-27', '0000-00-00', 'For printing', '2025-09-27 11:51:45', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-27'),
(79, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0074', 'SAI-2025-0074', '2025-09-27', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'ROY L. RICACHO', 'CLERK', '2025-09-27', '0000-00-00', 'For printing', '2025-09-27 12:27:04', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-27'),
(80, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0075', 'SAI-2025-0075', '2025-09-27', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'ROY L. RICACHO', 'CLERK', '2025-09-27', '0000-00-00', 'For printing', '2025-09-27 12:28:26', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-27'),
(81, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0076', 'SAI-2025-0076', '2025-09-27', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'ROY L. RICACHO', 'CLERK', '2025-09-27', '0000-00-00', 'For printing', '2025-09-27 12:31:29', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-27'),
(82, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0077', 'SAI-2025-0077', '2025-09-27', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'ROY L. RICACHO', 'CLERK', '2025-09-27', '0000-00-00', 'For printing', '2025-09-27 12:32:37', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-27'),
(83, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-00779', 'SAI-2025-0078', '2025-09-27', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'ROY L. RICACHO', 'CLERK', '2025-09-27', '0000-00-00', 'For printing', '2025-09-27 12:37:35', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-27'),
(84, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-00770', 'SAI-2025-0079', '2025-09-27', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'ROY L. RICACHO', 'CLERK', '2025-09-27', '0000-00-00', 'For printing', '2025-09-27 13:03:50', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-27'),
(85, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-00770', 'SAI-2025-0080', '2025-09-27', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'ROY L. RICACHO', 'CLERK', '2025-09-27', '0000-00-00', 'For printing', '2025-09-27 13:36:16', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-27'),
(86, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-00770', 'SAI-2025-0081', '2025-09-27', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'ROY L. RICACHO', 'CLERK', '2025-09-27', '0000-00-00', 'For printing', '2025-09-27 13:37:43', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-27'),
(87, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-00770', 'SAI-2025-0082', '2025-09-27', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'ROY L. RICACHO', 'CLERK', '2025-09-27', '0000-00-00', 'For printing', '2025-09-27 13:41:51', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-27', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-27'),
(88, 6, 4, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-00770', 'SAI-2025-0083', '2025-09-28', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-28', 'ROY L. RICACHO', 'CLERK', '2025-09-28', '0000-00-00', 'For printing', '2025-09-28 05:57:40', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-28', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-28'),
(89, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-00770', 'SAI-2025-0083', '2025-09-28', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-28', 'ROY L. RICACHO', 'CLERK', '2025-09-28', '0000-00-00', 'For printing', '2025-09-28 05:57:52', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-28', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-28'),
(90, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-00770', 'SAI-2025-0083', '2025-09-28', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-28', 'ROY L. RICACHO', 'CLERK', '2025-09-28', '0000-00-00', 'For printing', '2025-09-28 05:58:52', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-28', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-28'),
(91, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-00770', 'SAI-2025-0083', '2025-09-28', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-28', 'ROY L. RICACHO', 'CLERK', '2025-09-28', '0000-00-00', 'For printing', '2025-09-28 06:00:06', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-28', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-28'),
(92, 6, 4, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-00770', 'SAI-2025-0087', '2025-09-28', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-28', 'ROY L. RICACHO', 'CLERK', '2025-09-28', '0000-00-00', 'For printing', '2025-09-28 06:01:41', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-28', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-28');

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
(1, 7, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 0, 60.00, 0.00),
(2, 8, 'STOCK-0002', '1', 'Ballpen (Blue Ink)', 0, 15.00, 0.00),
(3, 9, 'STOCK-0002', '1', 'Ballpen (Blue Ink)', 0, 15.00, 0.00),
(4, 11, 'STOCK-0002', '1', 'Ballpen (Blue Ink)', 0, 15.00, 0.00),
(5, 12, 'STOCK-0002', '1', 'Ballpen (Blue Ink)', 0, 15.00, 0.00),
(6, 13, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 0, 60.00, 0.00),
(7, 14, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 0, 60.00, 0.00),
(8, 17, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 0, 60.00, 0.00),
(9, 28, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 0.00),
(10, 29, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 2, 60.00, 0.00),
(11, 30, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(12, 31, 'STOCK-0002', '1', 'Ballpen (Blue Ink)', 2, 15.00, 30.00),
(13, 32, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 10, 60.00, 600.00),
(14, 33, '', '1', 'Bond paper', 2, 250.00, 500.00),
(15, 34, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(16, 35, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(17, 36, 'STOCK-0002', '1', 'Ballpen (Blue Ink)', 1, 15.00, 15.00),
(18, 37, 'STOCK-0002', '1', 'Ballpen (Blue Ink)', 1, 15.00, 15.00),
(19, 38, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(20, 39, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(21, 40, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(22, 41, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(23, 42, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(24, 43, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(25, 44, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(26, 45, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(27, 46, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(28, 47, 'STOCK-0002', '1', 'Ballpen (Blue Ink)', 1, 15.00, 15.00),
(29, 48, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml)', 1, 60.00, 60.00),
(30, 49, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml) (Supply Office)', 1, 60.00, 60.00),
(31, 50, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 1, 300.00, 300.00),
(32, 51, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml) (OMASS)', 1, 60.00, 60.00),
(33, 52, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 1, 300.00, 300.00),
(34, 53, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 1, 300.00, 300.00),
(35, 54, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 1, 300.00, 300.00),
(36, 55, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 1, 300.00, 300.00),
(37, 57, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 1, 300.00, 300.00),
(38, 58, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 1, 300.00, 300.00),
(39, 59, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 1, 300.00, 300.00),
(40, 60, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml) (Supply Office)', 1, 60.00, 60.00),
(41, 61, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml) (Supply Office)', 1, 60.00, 60.00),
(42, 62, 'STOCK-0002', '1', 'Ballpen (Blue Ink) (Supply Office)', 1, 15.00, 15.00),
(43, 63, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml) (Supply Office)', 1, 60.00, 60.00),
(44, 64, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml) (Supply Office)', 3, 60.00, 0.00),
(45, 64, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 2, 300.00, 600.00),
(46, 65, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml) (Supply Office)', 2, 60.00, 120.00),
(47, 65, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml) (Supply Office)', 2, 60.00, 120.00),
(48, 66, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml) (Supply Office)', 1, 60.00, 60.00),
(49, 67, 'STOCK-0004', '17', 'Alcohol 70% Solution (500ml) (Supply Office)', 1, 60.00, 60.00),
(50, 68, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 1, 300.00, 300.00),
(51, 69, 'STOCK-0003', '1', 'Printer Ink Cartridge (Black) (Supply Office)', 1, 300.00, 300.00),
(52, 70, '', '1', 'Ink', 5, 250.00, 1250.00),
(53, 71, '', '1', 'Ballpen', 100, 7.50, 750.00),
(54, 72, '', '22', 'bond paper', 6, 350.00, 1400.00),
(55, 73, '', '2', 'ink', 2, 340.00, 680.00),
(56, 74, '', '2', 'Ballpen', 2, 345.00, 690.00),
(57, 75, '', '2', 'Ballpen', 2, 234.01, 468.02),
(58, 76, '', '1', 'ballpen', 100, 7.50, 750.00),
(59, 77, '1', '1', 'ballpen panda', 20, 7.00, 140.00),
(60, 78, '1', '17', 'Air Freshener (Ambi pur)', 2, 300.00, 600.00),
(61, 78, '2', 'bottle', 'Air Freshener (Spray)', 3, 390.00, 780.00),
(62, 79, '1', '17', 'Ambi Pur (spray)', 2, 390.00, 780.00),
(63, 80, '1', '17', 'Ambi pur (spray)', 2, 390.00, 780.00),
(64, 81, '1', '21', 'Ambi Pur', 2, 390.00, 780.00),
(65, 82, '1', 'bottle', 'Ambi Pur (spray)', 0, 390.00, 780.00),
(66, 83, '1', '17', 'Ambi Pur (spray) (OMASS)', 2, 390.00, 780.00),
(67, 84, '', 'bottle', 'air freshener (ambipur)', 22, 390.00, 780.00),
(68, 85, '', '17', 'Ambi pur', 2, 300.00, 600.00),
(69, 86, '', '17', 'Ambi pur', 1, 300.00, 300.00),
(70, 87, '', '17', 'Ambi pur', 2, 300.00, 600.00),
(71, 88, '', '17', 'Ambi pur (spray)', 2, 390.00, 780.00),
(72, 89, '', '17', 'Ambi pur (spray)', 2, 390.00, 780.00),
(73, 90, '', '17', 'Ambi pur (spray)', 2, 390.00, 780.00),
(74, 91, '', '17', 'Ambi pur (spray)', 2, 390.00, 780.00),
(75, 92, '', '17', 'Air freshener (Ambi pur)', 2, 300.00, 600.00);

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
(1, '1755868631_158e7711-e186-42d4-ad9f-547bffbad174.jpg', 'Pilar Inventory Management System', 'PilarINVENTORY@1');

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
(19, 'geely', 'Geely Mitsubishi', 'waltielappy123@gmail.com', '$2y$10$uVrAvdjC3GsGheiqmZSuF.r.oBbcHdOceQaV.E5LChrNNc/p20/FC', 'user', 'active', '2025-06-24 06:54:34', NULL, NULL, 4, 'default_profile.png', 1800),
(21, 'miki', 'Miki Matsubara', 'mikimat@gmail.com', '$2y$10$hE2SgXv.RQahXlmHCv4MEeBfBLqkaY7/w9OVyZbnuy83LMMPrFDHa', 'user', 'active', '2025-06-24 07:01:30', NULL, NULL, NULL, 'default_profile.png', 1800),
(22, 'Toyoki', 'Toyota Suzuki', 'toyoki@gmail.com', '$2y$10$dLNw4hqEJbKpB5Hc7Mmhr.AjH4dOiMIUg9BqGDkiLnnx3rw89KBfS', 'user', 'active', '2025-06-24 07:23:43', NULL, NULL, NULL, 'default_profile.png', 1800),
(23, 'jet', 'Jet Kawasaki', 'kawaisaki@gmail.com', '$2y$10$JmxsfOnmMH/nJbxWUbuSqODWoHTMx8RZn/Zxg38EFpGlvhqCtP3b6', 'user', 'active', '2025-06-24 07:24:56', NULL, NULL, NULL, 'default_profile.png', 1800),
(24, 'juan', 'Juan A. Dela Cruz', 'juandelacruz@gmail.com', '$2y$10$NO/J3fBNaHSu/5HNM2vp/.hbb.u1NRzLSo8AQWh55P/TmnkUUv.Xe', 'office_admin', 'active', '2025-09-14 02:29:57', NULL, NULL, 3, 'default_profile.png', 1800),
(26, 'waltielappy@gmail.com', 'Seynatour Kiimon', 'wjll2022-2920-98466@bicol-u.edu.ph', '$2y$10$UcjNuBTMzbToTt2gi4Dr2Oc/93pdffaCkrp3U2zZ8JvtE1nYHKRry', 'user', 'active', '2025-09-27 04:39:35', NULL, NULL, 49, 'default_profile.png', 1800),
(28, 'matt', 'Matt Monro', 'matt@gmail.com', '$2y$10$FJP03nb6a4qqz4PRArmV2.8hlWtzT.shXDt4In8f8jBXhNbAhno56', 'user', 'deleted', '2025-09-27 04:41:23', NULL, NULL, 4, 'default_profile.png', 1800),
(29, 'jack', 'Jack Daniels', 'jack@gmail.com', '$2y$10$Gta6jYCePQr3UXEDOKWvdOtmznoA5.v/rIUxCw0vg5x5WH7bOYiZW', 'user', 'active', '2025-09-27 04:59:33', NULL, NULL, 4, 'default_profile.png', 1800),
(30, 'mike', 'Mike Tyson', 'mike@gmail.com', '$2y$10$FyofC5mTLdO.LAPel/wN0u.cR.BcYxanPhlfkt5n9CCT.JtJ.yTmW', 'user', 'active', '2025-09-27 06:24:00', NULL, NULL, 4, 'default_profile.png', 1800),
(31, 'walton', 'Walter Loneza', 'waltonloneza@gmail.com', '$2y$10$7jucgW6Qw9cQEq/aYJp7cOEHWrF/T2VA9o9QlCzYapek./Pl91snW', 'user', 'active', '2025-09-28 13:04:03', NULL, NULL, 49, 'default_profile.png', 1800);

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
  ADD KEY `fk_borrow_batch_item` (`batch_item_id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `document_type` (`document_type`),
  ADD KEY `is_active` (`is_active`),
  ADD KEY `updated_by` (`updated_by`),
  ADD KEY `idx_legal_docs_type_active` (`document_type`,`is_active`);

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
-- Indexes for table `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `office_name` (`office_name`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `assets_archive`
--
ALTER TABLE `assets_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `assets_new`
--
ALTER TABLE `assets_new`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `asset_requests`
--
ALTER TABLE `asset_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=267;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `ics_form`
--
ALTER TABLE `ics_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `ics_items`
--
ALTER TABLE `ics_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `iirup_form`
--
ALTER TABLE `iirup_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `iirup_items`
--
ALTER TABLE `iirup_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `infrastructure_inventory`
--
ALTER TABLE `infrastructure_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inventory_actions`
--
ALTER TABLE `inventory_actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `itr_form`
--
ALTER TABLE `itr_form`
  MODIFY `itr_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `itr_items`
--
ALTER TABLE `itr_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `legal_documents`
--
ALTER TABLE `legal_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `legal_document_history`
--
ALTER TABLE `legal_document_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mr_details`
--
ALTER TABLE `mr_details`
  MODIFY `mr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `par_form`
--
ALTER TABLE `par_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `par_items`
--
ALTER TABLE `par_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `red_tags`
--
ALTER TABLE `red_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- AUTO_INCREMENT for table `ris_items`
--
ALTER TABLE `ris_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

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
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fuel_stock`
--
ALTER TABLE `fuel_stock`
  ADD CONSTRAINT `fuel_stock_ibfk_1` FOREIGN KEY (`fuel_type_id`) REFERENCES `fuel_types` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_permissions`
--
ALTER TABLE `user_permissions`
  ADD CONSTRAINT `user_permissions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
