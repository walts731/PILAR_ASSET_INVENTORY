-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: Sep 22, 2025 at 03:25 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4
=======
-- Generation Time: Sep 22, 2025 at 08:45 AM
-- Server version: 10.6.15-MariaDB
-- PHP Version: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

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
  `status` enum('available','borrowed','in use','damaged','disposed','unserviceable','unavailable','lost','pending') NOT NULL DEFAULT 'available',
  `acquisition_date` date DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
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
  `inventory_tag` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_name`, `category`, `description`, `quantity`, `added_stock`, `unit`, `status`, `acquisition_date`, `office_id`, `employee_id`, `red_tagged`, `last_updated`, `value`, `qr_code`, `type`, `image`, `serial_no`, `code`, `property_no`, `model`, `brand`, `ics_id`, `par_id`, `asset_new_id`, `inventory_tag`) VALUES
(1, 'Office Table – Wooden', 2, 'Office Table – Wooden', 1, 0, 'pcs', 'available', '2025-09-19', 4, 1, 0, '2025-09-19 14:23:56', 3500.00, '1.png', 'asset', '', '', '', 'MR-2025-00001', '', '', 17, NULL, 1, 'No. PS-5S-03-F02-01'),
(3, 'Mouse', 2, 'Mouse', 1, 0, 'pcs', 'available', '2025-09-19', 4, 1, 0, '2025-09-19 14:56:07', 350.00, '3.png', 'asset', 'asset_3_1758293767.jpg', '', '', 'MR-2025-00003', '', '', 18, NULL, 2, 'No. PS-5S-03-F02-03'),
(5, 'Printer Epson', 1, 'Printer Epson', 1, 0, 'pcs', 'available', '2025-09-19', 4, 1, 0, '2025-09-19 15:05:22', 4593.00, '5.png', 'asset', '', '', '', 'MR-2025-00005', '', '', 19, NULL, 3, 'No. PS-5S-03-F02-05'),
(6, 'Printer Epson', 1, 'Printer Epson', 1, 0, 'pcs', 'available', '2025-09-19', 4, 1, 0, '2025-09-19 15:24:55', 4593.00, '6.png', 'asset', '', '', '', 'MR-2025-00006', '', '', 19, NULL, 3, 'No. PS-5S-03-F02-06'),
(15, 'Blue Chair', 2, 'Uratex', 3, 0, 'pcs', 'available', '2025-04-04', 4, NULL, 0, '2025-06-13 08:39:23', 30000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(16, 'eagle', 1, 'eagle', 1, 0, 'box', 'available', '2025-09-19', 49, NULL, 0, '2025-09-19 14:08:11', 345.00, '21.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
<<<<<<< HEAD
(17, 'Van', 4, 'Van', 6, 0, 'unit', 'available', '2025-09-18', 49, NULL, 0, '2025-09-22 13:11:02', 49999.99, '54.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'Cellphone', 1, 'Cellphone', 1, 0, 'pcs', 'available', '2025-09-21', 4, 1, 0, '2025-09-21 08:12:19', 5678.00, '19.png', 'asset', '', '', '', 'MR-2025-00019', '', '', 25, NULL, 8, 'No. PS-5S-03-F02-19'),
(20, 'Cellphone', 1, 'Cellphone', 1, 0, 'pcs', 'available', '2025-09-21', 4, 2, 0, '2025-09-21 13:16:38', 5678.00, '20.png', 'asset', '', '', '', 'MR-2025-00020', '', '', 25, NULL, 8, 'No. PS-5S-03-F02-20'),
(21, 'Ballpen', NULL, 'Ballpen', 2, 2, 'box', 'available', '2025-09-21', 3, NULL, 0, '2025-09-21 09:03:46', 345.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, ''),
(22, 'Ballpen', NULL, 'Ballpen', 2, 2, 'box', 'available', '2025-09-21', 3, NULL, 0, '2025-09-21 09:27:23', 234.01, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, ''),
(23, 'Dell Unit', 1, 'Dell Unit', 1, 0, 'unit', 'available', '2025-09-21', 4, 1, 0, '2025-09-21 13:15:16', 99000.00, '23.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 9, NULL),
(24, 'Dell Unit', NULL, 'Dell Unit', 1, 0, 'unit', 'available', '2025-09-21', 4, NULL, 0, '2025-09-21 13:14:32', 99000.00, '24.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 9, NULL),
(27, 'Jetski', 1, 'Jetski', 1, 0, 'unit', 'available', '2025-09-21', 4, 2, 0, '2025-09-21 13:25:39', 96780.00, '27.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 12, NULL),
(28, 'Jetski', NULL, 'Jetski', 1, 0, 'unit', 'available', '2025-09-21', 4, NULL, 0, '2025-09-21 13:25:03', 96780.00, '28.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 12, NULL),
(29, 'HIlux', NULL, 'HIlux', 1, 0, 'roll', 'available', '2025-09-21', 4, NULL, 0, '2025-09-21 13:28:12', 1000000.00, '29.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 13, NULL),
(30, 'Car', NULL, 'Car', 1, 0, 'unit', 'available', '2025-09-21', 4, NULL, 0, '2025-09-21 13:33:35', 4500000.00, '30.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 14, NULL);
=======
(17, 'Van', 1, 'Van', 6, 0, 'unit', 'available', '2025-09-18', 49, NULL, 0, '2025-09-19 03:04:12', 49999.99, '54.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(19, 'Cellphone', 1, 'Cellphone', 1, 0, 'pcs', 'unserviceable', '2025-09-21', 4, 1, 1, '2025-09-22 00:40:33', 5678.00, '19.png', 'asset', '', '', '', 'MR-2025-00019', '', '', 25, NULL, 8, 'No. PS-5S-03-F02-19'),
(20, 'Cellphone', 1, 'Cellphone', 1, 0, 'pcs', 'unserviceable', '2025-09-21', 4, 2, 1, '2025-09-22 04:55:47', 5678.00, '20.png', 'asset', '', '', '', 'MR-2025-00020', '', '', 25, NULL, 8, 'No. PS-5S-03-F02-20'),
(21, 'Ballpen', NULL, 'Ballpen', 2, 2, 'box', 'available', '2025-09-21', 3, NULL, 0, '2025-09-21 09:03:46', 345.00, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, ''),
(22, 'Ballpen', NULL, 'Ballpen', 2, 2, 'box', 'available', '2025-09-21', 3, NULL, 0, '2025-09-21 09:27:23', 234.01, '', 'consumable', '', '', '', '', '', '', NULL, NULL, NULL, ''),
(23, 'Dell Unit', 1, 'Dell Unit', 1, 0, 'unit', 'unserviceable', '2025-09-21', 4, 1, 1, '2025-09-22 00:54:21', 99000.00, '23.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 9, NULL),
(24, 'Dell Unit', NULL, 'Dell Unit', 1, 0, 'unit', 'available', '2025-09-21', 4, NULL, 0, '2025-09-21 13:14:32', 99000.00, '24.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 9, NULL),
(27, 'Jetski', 1, 'Jetski', 1, 0, 'unit', 'unserviceable', '2025-09-21', 4, 2, 1, '2025-09-22 00:51:24', 96780.00, '27.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 12, NULL),
(28, 'Jetski', NULL, 'Jetski', 1, 0, 'unit', 'available', '2025-09-21', 4, NULL, 0, '2025-09-21 13:25:03', 96780.00, '28.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 12, NULL),
(29, 'HIlux', NULL, 'HIlux', 1, 0, 'roll', 'available', '2025-09-21', 4, NULL, 0, '2025-09-21 13:28:12', 1000000.00, '29.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 13, NULL),
(30, 'Car', NULL, 'Car', 1, 0, 'unit', 'unserviceable', '2025-09-21', 4, NULL, 1, '2025-09-22 00:57:11', 4500000.00, '30.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 14, NULL),
(31, 'Mio Soul i', 1, 'Mio Soul i', 1, 0, 'unit', 'available', '2025-09-21', 4, 2, 0, '2025-09-21 13:41:27', 75000.00, '31.png', 'asset', '', '', '', NULL, '', '', NULL, 44, 15, NULL),
(32, 'Honda', 1, 'Honda Click 125', 1, 0, 'unit', 'unserviceable', '0000-00-00', 7, 2, 1, '2025-09-22 06:28:52', 75000.00, '32.png', 'asset', '', '', '', '', '', '', NULL, 45, 16, 'No. PS-5S-03-F02-32'),
(33, 'Hilux Van', 1, 'Hilux Van', 1, 0, 'unit', 'available', '2025-09-21', 4, 3, 0, '2025-09-21 21:57:07', 7600000.00, '33.png', 'asset', '', '', '', 'MR-2025-00033', '', '', NULL, 46, 17, 'No. PS-5S-03-F02-33'),
(34, 'Hilux van black', 2, 'Hilux van black', 1, 0, 'unit', 'unserviceable', '2025-09-22', 4, 8, 1, '2025-09-22 00:30:09', 2300000.00, '34.png', 'asset', '', '', 'EQP-001', 'MR-2025-00034', '', '', NULL, 47, 18, 'No. PS-5S-03-F02-34'),
(35, 'Lenovo AMD Ryzen 7', NULL, 'Lenovo AMD Ryzen 7', 1, 0, 'unit', 'unserviceable', '2025-09-22', 4, NULL, 1, '2025-09-22 00:52:03', 75000.00, '35.png', 'asset', '', '', '', NULL, '', '', NULL, 49, 19, NULL),
(36, 'Lenovo AMD Ryzen 7', NULL, 'Lenovo AMD Ryzen 7', 1, 0, 'unit', 'available', '2025-09-22', 4, NULL, 0, '2025-09-21 22:07:07', 75000.00, '36.png', 'asset', '', '', '', NULL, '', '', NULL, 49, 19, NULL);
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

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
<<<<<<< HEAD
(14, 'Car', 1, 4500000.00, 'unit', 4, NULL, NULL, '2025-09-21 18:33:35');

-- --------------------------------------------------------

--
-- Table structure for table `asset_items`
--

CREATE TABLE `asset_items` (
  `item_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `status` enum('available','borrowed','maintenance','lost') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
=======
(14, 'Car', 1, 4500000.00, 'unit', 4, NULL, NULL, '2025-09-21 18:33:35'),
(15, 'Mio Soul i', 1, 75000.00, 'unit', 4, 44, NULL, '2025-09-21 18:40:55'),
(16, 'Honda Click 125', 1, 75000.00, 'unit', 4, 45, NULL, '2025-09-21 18:51:18'),
(17, 'Hilux Van', 1, 7600000.00, 'unit', 4, 46, NULL, '2025-09-22 02:56:51'),
(18, 'Hilux van black', 1, 2300000.00, 'unit', 4, 47, NULL, '2025-09-22 03:04:09'),
(19, 'Lenovo AMD Ryzen 7', 2, 75000.00, 'unit', 4, 49, NULL, '2025-09-22 03:07:07');
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

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
-- Table structure for table `borrow_requests`
--

CREATE TABLE `borrow_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
<<<<<<< HEAD
  `quantity` int(11) NOT NULL,
  `status` enum('pending','approved','rejected','borrowed','returned','cancelled') NOT NULL DEFAULT 'pending',
  `requested_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `approved_at` datetime DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `due_date` date DEFAULT NULL,
=======
  `status` enum('pending','approved','rejected','borrowed','returned') NOT NULL DEFAULT 'pending',
  `requested_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `return_remarks` text DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

<<<<<<< HEAD
-- --------------------------------------------------------

--
-- Table structure for table `borrow_request_items`
--

CREATE TABLE `borrow_request_items` (
  `id` int(11) NOT NULL,
  `borrow_request_id` int(11) NOT NULL,
  `asset_item_id` int(11) NOT NULL,
  `status` enum('assigned','returned') NOT NULL DEFAULT 'assigned',
  `returned_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
=======
--
-- Dumping data for table `borrow_requests`
--

INSERT INTO `borrow_requests` (`id`, `user_id`, `asset_id`, `office_id`, `status`, `requested_at`, `approved_at`, `return_remarks`, `returned_at`, `quantity`, `created_at`, `updated_at`) VALUES
(2, 19, 13, 4, 'returned', '2025-07-12 15:40:35', '2025-07-14 21:13:26', 'NEVER BEEN USED', '2025-07-14 21:13:47', 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31'),
(3, 19, 14, 4, 'pending', '2025-07-12 15:40:35', NULL, NULL, NULL, 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31'),
(4, 19, 2, 9, 'returned', '2025-07-12 15:42:36', '2025-07-14 09:54:28', 'slightly used', '2025-07-14 19:55:48', 0, '2025-08-30 03:09:31', '2025-08-30 03:09:31'),
(5, 17, 2, 9, 'pending', '2025-07-13 15:15:18', NULL, NULL, NULL, 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31'),
(6, 17, 2, 9, 'returned', '2025-07-13 15:24:25', '2025-07-13 20:45:54', 'All goods', '2025-07-13 20:58:56', 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31'),
(7, 17, 2, 9, 'returned', '2025-07-14 04:23:59', '2025-07-14 21:00:24', 'Good condition', '2025-07-14 21:02:14', 0, '2025-08-30 03:09:31', '2025-08-30 03:09:31'),
(8, 17, 13, 4, 'returned', '2025-07-14 14:49:24', '2025-07-14 19:50:05', 'Neve used', '2025-07-14 21:05:50', 0, '2025-08-30 03:09:31', '2025-08-30 03:09:31'),
(9, 17, 3, 2, 'pending', '2025-08-20 08:09:14', NULL, NULL, NULL, 5, '2025-08-30 03:09:31', '2025-08-30 03:09:31'),
(10, 17, 64, 9, 'pending', '2025-08-20 08:17:57', NULL, NULL, NULL, 3, '2025-08-30 03:09:31', '2025-08-30 03:09:31'),
(11, 12, 64, 9, 'pending', '2025-08-20 08:24:23', NULL, NULL, NULL, 3, '2025-08-30 03:09:31', '2025-08-30 03:09:31'),
(12, 17, 64, 9, 'pending', '2025-08-29 15:24:45', NULL, NULL, NULL, 1, '2025-08-30 03:09:31', '2025-08-30 03:09:31');

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
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) NOT NULL,
  `type` enum('asset','consumables') NOT NULL DEFAULT 'asset'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `category_name`, `type`) VALUES
(1, 'Electronics', 'asset'),
(2, 'Furniture', 'asset'),
(3, 'Office Supplies', 'consumables'),
(4, 'Vehicle', 'asset'),
(5, 'Power Equipment', 'asset'),
(6, 'IT Equipment', 'asset'),
(7, 'Security Equipment', 'asset'),
(15, 'Categories', 'asset');

-- --------------------------------------------------------

--
<<<<<<< HEAD
-- Table structure for table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `category`
--

INSERT INTO `category` (`id`, `category`, `created_at`, `updated_at`) VALUES
(1, 'Electronics', '2025-09-22 13:09:39', '2025-09-22 13:09:39'),
(2, 'Furniture', '2025-09-22 13:09:39', '2025-09-22 13:09:39'),
(3, 'Office Equipment', '2025-09-22 13:09:39', '2025-09-22 13:09:39'),
(4, 'IT Equipment', '2025-09-22 13:09:39', '2025-09-22 13:09:39'),
(5, 'Tools', '2025-09-22 13:09:39', '2025-09-22 13:09:39'),
(6, 'Vehicles', '2025-09-22 13:09:39', '2025-09-22 13:09:39'),
(7, 'Other', '2025-09-22 13:09:39', '2025-09-22 13:09:39');

-- --------------------------------------------------------

--
=======
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
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
(8, 'EMP0004', 'Ryan Bang', 'permanent', 'uncleared', '2025-09-20 12:03:27', NULL, 7);

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
(7, 'INVENTORY & INSPECTION REPORT OF UNSERVICEABLE PROPERTY', 'IIRUP', 'iirup_form.php', '2025-08-12 12:53:40');

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
(83, 17, 4, 'Inventory_Report_20250921_112526.pdf', 0, '2025-09-21 14:25:27');

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
(25, 'ics_header_1758325430_e38f060b.png', 'INVENTORY', 'FC-2025-001', 'ICS-2025-0021', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER LGU', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-09-21 08:11:37', 4);

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
(11, 25, 20, 'ICS-2025-0021', 1, 'pcs', 5678.00, 5678.00, 'Cellphone', '', '', '2025-09-21 13:16:38');

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
<<<<<<< HEAD
(5, '1756475584_Screenshot 2025-08-29 204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-08-29 13:53:04');
=======
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
(19, 'iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer III', 'Municipal Mayor', '2025-09-22 06:08:16');

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
(14, 19, 32, '2025-09-22', 'Honda Click 125', 'No. PS-5S-03-F02-32', 1, 75000.00, 75000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'Supply Office', '', '', '2025-09-22', '2025-09-22 06:08:16');
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

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
  `acquired_date` date DEFAULT NULL,
  `counted_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `asset_id` int(11) DEFAULT NULL,
  `inventory_tag` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mr_details`
--

INSERT INTO `mr_details` (`mr_id`, `item_id`, `office_location`, `description`, `model_no`, `serial_no`, `serviceable`, `unserviceable`, `unit_quantity`, `unit`, `acquisition_date`, `acquisition_cost`, `person_accountable`, `acquired_date`, `counted_date`, `created_at`, `asset_id`, `inventory_tag`) VALUES
(1, 1, 'Supply Office', 'Office Table – Wooden', '', '', 0, 0, 1.00, 'pcs', '2025-09-19', 3500.00, 'Juan A. Dela Cruz', '0000-00-00', '0000-00-00', '2025-09-19 14:23:56', 1, 'No. PS-5S-03-F02-01'),
(3, 3, 'Supply Office', 'Mouse', '', '', 0, 0, 1.00, 'pcs', '2025-09-19', 350.00, 'Juan A. Dela Cruz', '0000-00-00', '0000-00-00', '2025-09-19 14:36:44', 3, 'No. PS-5S-03-F02-03'),
(4, 4, 'Supply Office', 'Printer Epson', '', '', 0, 0, 1.00, 'pcs', '2025-09-19', 4593.00, 'Juan A. Dela Cruz', '0000-00-00', '0000-00-00', '2025-09-19 15:05:22', 5, 'No. PS-5S-03-F02-05'),
(5, 5, 'Supply Office', 'Printer Epson', '', '', 0, 0, 1.00, 'pcs', '2025-09-19', 4593.00, 'Juan A. Dela Cruz', '0000-00-00', '0000-00-00', '2025-09-19 15:24:55', 6, 'No. PS-5S-03-F02-06'),
(8, 10, 'Supply Office', 'Cellphone', '', '', 0, 0, 1.00, 'pcs', '2025-09-21', 5678.00, 'Juan A. Dela Cruz', '0000-00-00', '0000-00-00', '2025-09-21 08:12:19', 19, 'No. PS-5S-03-F02-19'),
<<<<<<< HEAD
(9, 11, 'Supply Office', 'Cellphone', '', '', 0, 0, 1.00, 'pcs', '2025-09-21', 5678.00, 'Maria Santos', '0000-00-00', '0000-00-00', '2025-09-21 13:16:38', 20, 'No. PS-5S-03-F02-20');
=======
(9, 11, 'Supply Office', 'Cellphone', '', '', 0, 0, 1.00, 'pcs', '2025-09-21', 5678.00, 'Maria Santos', '0000-00-00', '0000-00-00', '2025-09-21 13:16:38', 20, 'No. PS-5S-03-F02-20'),
(11, NULL, 'Supply Office', 'Honda', '', '', 0, 0, 1.00, 'unit', '2025-09-21', 75000.00, 'Maria Santos', '0000-00-00', '0000-00-00', '2025-09-21 13:53:13', 32, 'No. PS-5S-03-F02-32'),
(12, NULL, 'Supply Office', 'Hilux Van', '', '', 0, 0, 1.00, 'unit', '2025-09-21', 7600000.00, 'Pedro Reyes', '0000-00-00', '0000-00-00', '2025-09-21 21:57:07', 33, 'No. PS-5S-03-F02-33'),
(13, NULL, 'Supply Office', 'Hilux van black', '', '', 0, 0, 1.00, 'unit', '2025-09-22', 2300000.00, 'Ryan Bang', '0000-00-00', '0000-00-00', '2025-09-21 22:04:25', 34, 'No. PS-5S-03-F02-34');
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

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

INSERT INTO `par_form` (`id`, `form_id`, `office_id`, `position_office_left`, `position_office_right`, `header_image`, `entity_name`, `fund_cluster`, `par_no`, `created_at`, `date_received_left`, `date_received_right`) VALUES
(3, 0, 3, '', '', NULL, 'DepEd', 'FC-2025-001', 'PAR-0001', '2025-09-15 14:26:34', '0000-00-00', '0000-00-00'),
(4, 0, 3, '', '', NULL, 'DepEd', 'FC-2025-001', 'PAR-0001', '2025-09-15 14:31:12', '0000-00-00', '0000-00-00'),
(5, 0, 3, 'ivan christoper millabas', 'mark jayson namia', NULL, 'LGU', 'FC-2025-001', 'PAR-0002', '2025-09-15 14:35:52', NULL, NULL),
(6, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', NULL, 'LGU', 'FC-2025-001', 'PAR-0003', '2025-09-15 14:47:50', NULL, NULL),
(7, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', NULL, 'LGU', 'FC-2025-001', 'PAR-0003', '2025-09-15 14:48:18', NULL, NULL),
(8, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', NULL, 'LGU', 'FC-2025-001', 'PAR-0004', '2025-09-15 15:10:33', NULL, NULL),
(10, 3, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0005', '2025-09-16 02:34:56', NULL, NULL),
(11, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0006', '2025-09-16 09:04:15', NULL, NULL),
(12, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0007', '2025-09-16 09:08:48', '2025-09-16', '2025-09-16'),
(13, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0008', '2025-09-16 09:10:44', '2025-09-16', '2025-09-16'),
(14, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0009', '2025-09-16 09:43:27', '2025-09-16', '2025-09-16'),
(15, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0009', '2025-09-16 09:48:35', '2025-09-16', '2025-09-16'),
(16, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0010', '2025-09-16 09:48:44', '2025-09-16', '2025-09-16'),
(17, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0010', '2025-09-16 09:49:27', '2025-09-16', '2025-09-16'),
(18, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0010', '2025-09-16 09:50:18', '2025-09-16', '2025-09-16'),
(19, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0011', '2025-09-16 09:51:55', '2025-09-16', '2025-09-16'),
(20, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0012', '2025-09-16 09:57:03', '2025-09-16', '2025-09-16'),
(21, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0012', '2025-09-16 09:58:10', '2025-09-16', '2025-09-16'),
(22, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0013', '2025-09-16 10:10:23', '2025-09-16', '2025-09-16'),
(23, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0014', '2025-09-16 10:12:34', '2025-09-16', '2025-09-16'),
(24, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0015', '2025-09-16 10:16:11', '2025-09-16', '2025-09-16'),
(27, 0, 3, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0016', '2025-09-16 10:28:47', '2025-09-16', '2025-09-16'),
(34, 0, NULL, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0017', '2025-09-16 10:36:33', '2025-09-16', '2025-09-16'),
(35, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0018', '2025-09-16 11:46:43', '2025-09-16', '2025-09-16'),
(36, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0019', '2025-09-21 12:56:37', '2025-09-21', '2025-09-21'),
(37, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0020', '2025-09-21 12:59:18', '2025-09-21', '2025-09-21'),
(38, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0021', '2025-09-21 13:14:32', '2025-09-21', '2025-09-21'),
(39, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0022', '2025-09-21 13:21:59', '2025-09-21', '2025-09-21'),
(40, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0022', '2025-09-21 13:22:37', '2025-09-21', '2025-09-21'),
(41, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0022', '2025-09-21 13:25:03', '2025-09-21', '2025-09-21'),
(42, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0023', '2025-09-21 13:28:12', '2025-09-21', '2025-09-21'),
<<<<<<< HEAD
(43, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0024', '2025-09-21 13:33:35', '2025-09-21', '2025-09-21');
=======
(43, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0024', '2025-09-21 13:33:35', '2025-09-21', '2025-09-21'),
(44, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0025', '2025-09-21 13:40:55', '2025-09-21', '2025-09-21'),
(45, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0026', '2025-09-21 13:51:18', '2025-09-21', '2025-09-21'),
(46, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0027', '2025-09-21 21:56:51', '2025-09-21', '2025-09-21'),
(47, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0028', '2025-09-21 22:04:09', '2025-09-22', '2025-09-22'),
(48, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0029', '2025-09-21 22:06:40', '2025-09-22', '2025-09-22'),
(49, 0, 4, 'OFFICER', 'PROPERTY CUSTODIAN', '1757991153_Screenshot 2025-09-16 105218.png', 'LGU', 'FC-2025-001', 'PAR-0030', '2025-09-21 22:07:07', '2025-09-22', '2025-09-22');
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

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
<<<<<<< HEAD
(18, 43, 30, 1, 'unit', 'Car', '', '0000-00-00', 4500000.00, 4500000.00);
=======
(18, 43, 30, 1, 'unit', 'Car', '', '0000-00-00', 4500000.00, 4500000.00),
(19, 44, 31, 1, 'unit', 'Mio Soul i', '', '0000-00-00', 75000.00, 75000.00),
(20, 45, 32, 1, 'unit', 'Honda Click 125', '', '0000-00-00', 75000.00, 75000.00),
(21, 46, 33, 1, 'unit', 'Hilux Van', '', '0000-00-00', 7600000.00, 7600000.00),
(22, 47, 34, 1, 'unit', 'Hilux van black', '', '0000-00-00', 2300000.00, 2300000.00),
(23, 49, 35, 2, 'unit', 'Lenovo AMD Ryzen 7', '', '0000-00-00', 75000.00, 150000.00);

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
(2, 'PS-5S-03-F01-01-02', 20, 18, '2025-09-22', 17, 'Supply Office', 'Cellphone (MR-2025-00020)', 'Marupok', 'For Donation', 'Pending', '2025-09-22 05:10:09', '2025-09-22 05:10:09');
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

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
(75, 6, 3, '1757740937_Screenshot_2025-09-13_132057.png', 'V', '', '1', 'RIS-2025-0070', 'SAI-2025-0070', '2025-09-21', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-21', 'ROY L. RICACHO', 'CLERK', '2025-09-21', '0000-00-00', 'For printing', '2025-09-21 09:27:23', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-09-21', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-09-21');

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
(57, 75, '', '2', 'Ballpen', 2, 234.01, 468.02);

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
  `system_title` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system`
--

INSERT INTO `system` (`id`, `logo`, `system_title`) VALUES
(1, '1755868631_158e7711-e186-42d4-ad9f-547bffbad174.jpg', 'Pilar Inventory Management System');

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
(1, 'OMPDC', 'Mark Jayson Namia', 'waltielappy67@gmail.com', '$2y$10$PjQBLH0.VE3gnzvEqc9YXOhDu.wuUFpAYK1Ze/NnGOi6S3DcIdaGm', 'super_admin', 'active', '2025-04-01 13:01:47', NULL, NULL, NULL, 'default_profile.png', 1800),
(2, 'user2', 'Mark John', 'john2@example.com', 'hashed_password', 'user', 'active', '2025-04-03 04:31:57', NULL, NULL, 1, 'default_profile.png', 1800),
(4, 'user4', 'Steve Jobs', 'mark4@example.com', 'hashed_password', 'user', 'active', '2025-04-03 04:31:57', NULL, NULL, 3, 'default_profile.png', 1800),
(5, 'johndoe', 'Elon Musk', 'johndoe@example.com', 'password123', 'admin', 'inactive', '2025-04-03 04:45:50', NULL, NULL, 1, 'default_profile.png', 1800),
(6, 'janesmith', 'Mark Zuckerberg', 'janesmith@example.com', 'password123', 'admin', 'active', '2025-04-03 04:45:50', NULL, NULL, 2, 'default_profile.png', 1800),
(7, 'tomgreen', 'Tom Jones', 'tomgreen@example.com', 'password123', 'admin', 'active', '2025-04-03 04:45:50', NULL, NULL, 1, 'default_profile.png', 1800),
(8, 'marybrown', 'Ed Caluag', 'marybrown@example.com', 'password123', 'office_user', 'active', '2025-04-03 04:45:50', NULL, NULL, 3, 'default_profile.png', 1800),
(9, 'peterwhite', 'Peter White', 'peterwhite@example.com', 'password123', 'admin', 'active', '2025-04-03 04:45:50', NULL, NULL, 2, 'default_profile.png', 1800),
(10, 'walt', 'Walton Loneza', 'waltielappy@gmail.com', '$2y$10$j5gUPrRPP0w0REknIdYrce.l5ZItK3c5WJXX3eC2OSQHtJ/YchHey', 'admin', 'active', '2025-04-04 01:31:30', NULL, NULL, NULL, 'default_profile.png', 1800),
(12, 'walts', 'Walton Loneza', 'wjll@bicol-u.edu.ph', '$2y$10$tsOlFU9fjwi/DLRKdGkqL.aIXhKnlFxnNbA8ZoXeMbEiAhoe.sg/i', 'office_admin', 'inactive', '2025-04-07 14:13:29', NULL, NULL, 4, 'WIN_20240930_21_49_09_Pro.jpg', 1800),
(15, 'josh', 'Joshua Escano', 'jmfte@gmail.com', '$2y$10$IFmIX3WZ0YOxdf41EYzX6.IF51IKEg0bL0kmyORCI8dod42v.JeN6', 'office_user', 'inactive', '2025-04-09 00:49:07', '5a8b600a59a80f2bf5028ae258b3aae8', '2025-04-09 09:49:07', 4, 'josh.jpg', 1800),
(16, 'elton', 'Elton John B. Moises', 'ejbm@bicol-u.edu.ph', '$2y$10$Botz5wCa9biZrVT7IdEDau.uVBcw3ByoD75pX2BYYe7dtutigluY.', 'user', 'inactive', '2025-04-13 06:01:46', NULL, NULL, 9, 'profile_16_1749816479.jpg', 600),
(17, 'nami', 'Mark Jayson Namia', 'mjn@gmail.com', '$2y$10$2MIZlmP380wS0sj/cOfqbe20HkPz234S49cJEj2omrrTjBasHVqyO', 'admin', 'active', '2025-04-13 15:43:51', NULL, NULL, 4, 'default_profile.png', 1800),
(18, 'kiimon', 'Seynatour Kiimon', 'sk@gmail.com', '$2y$10$UGpyMRA79O2OKhKfZDEf5O9CyXkMFlhDsVpWdELXMYnMtdFIV0mSC', 'office_user', 'deleted', '2025-04-20 21:36:04', '6687598406441374aeffbc338a60f728', '2025-04-21 06:36:04', 4, 'default_profile.png', 1800),
(19, 'geely', 'Geely Mitsubishi', 'waltielappy123@gmail.com', '$2y$10$uVrAvdjC3GsGheiqmZSuF.r.oBbcHdOceQaV.E5LChrNNc/p20/FC', 'admin', 'active', '2025-06-24 06:54:34', NULL, NULL, 4, 'default_profile.png', 1800),
(21, 'miki', 'Miki Matsubara', 'mikimat@gmail.com', '$2y$10$hE2SgXv.RQahXlmHCv4MEeBfBLqkaY7/w9OVyZbnuy83LMMPrFDHa', 'user', 'active', '2025-06-24 07:01:30', NULL, NULL, NULL, 'default_profile.png', 1800),
(22, 'Toyoki', 'Toyota Suzuki', 'toyoki@gmail.com', '$2y$10$dLNw4hqEJbKpB5Hc7Mmhr.AjH4dOiMIUg9BqGDkiLnnx3rw89KBfS', 'user', 'active', '2025-06-24 07:23:43', NULL, NULL, NULL, 'default_profile.png', 1800),
(23, 'jet', 'Jet Kawasaki', 'kawaisaki@gmail.com', '$2y$10$JmxsfOnmMH/nJbxWUbuSqODWoHTMx8RZn/Zxg38EFpGlvhqCtP3b6', 'user', 'active', '2025-06-24 07:24:56', NULL, NULL, NULL, 'default_profile.png', 1800),
(24, 'juan', 'Juan A. Dela Cruz', 'juandelacruz@gmail.com', '$2y$10$NO/J3fBNaHSu/5HNM2vp/.hbb.u1NRzLSo8AQWh55P/TmnkUUv.Xe', 'office_admin', 'active', '2025-09-14 02:29:57', NULL, NULL, 3, 'default_profile.png', 1800);

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
  ADD KEY `fk_assets_employee` (`employee_id`),
<<<<<<< HEAD
=======
  ADD KEY `idx_assets_office_status` (`office_id`,`status`),
  ADD KEY `idx_assets_status` (`status`),
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
  ADD KEY `idx_assets_ics_id` (`ics_id`),
  ADD KEY `idx_assets_asset_new_id` (`asset_new_id`),
  ADD KEY `idx_assets_par_id` (`par_id`);

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
<<<<<<< HEAD
-- Indexes for table `asset_items`
--
ALTER TABLE `asset_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_asset_id` (`asset_id`);

--
=======
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
-- Indexes for table `asset_requests`
--
ALTER TABLE `asset_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `asset_id` (`asset_name`(768)),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_office` (`office_id`);

--
-- Indexes for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_borrow_requests_user_id` (`user_id`),
  ADD KEY `idx_borrow_requests_asset_id` (`asset_id`),
  ADD KEY `idx_borrow_requests_office_id` (`office_id`),
  ADD KEY `idx_borrow_requests_status` (`status`),
  ADD KEY `idx_borrow_requests_requested_at` (`requested_at`);

--
<<<<<<< HEAD
-- Indexes for table `borrow_request_items`
--
ALTER TABLE `borrow_request_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_borrow_request_id` (`borrow_request_id`),
  ADD KEY `idx_asset_item_id` (`asset_item_id`);

--
=======
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
<<<<<<< HEAD
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_category` (`category`);

--
=======
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
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
-- Indexes for table `generated_reports`
--
ALTER TABLE `generated_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `office_id` (`office_id`);

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
<<<<<<< HEAD
  ADD KEY `ics_id` (`ics_id`),
  ADD KEY `fk_ics_items_asset` (`asset_id`);
=======
  ADD KEY `ics_id` (`ics_id`);
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

--
-- Indexes for table `iirup_form`
--
ALTER TABLE `iirup_form`
  ADD PRIMARY KEY (`id`);

--
<<<<<<< HEAD
=======
-- Indexes for table `iirup_items`
--
ALTER TABLE `iirup_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `idx_iirup_id` (`iirup_id`);

--
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
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
<<<<<<< HEAD
  ADD PRIMARY KEY (`id`),
  ADD KEY `par_form_ibfk_2` (`office_id`);
=======
  ADD PRIMARY KEY (`id`);
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

--
-- Indexes for table `par_items`
--
ALTER TABLE `par_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `form_id` (`form_id`),
  ADD KEY `asset_id` (`asset_id`);

--
<<<<<<< HEAD
=======
-- Indexes for table `red_tags`
--
ALTER TABLE `red_tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `red_tag_number` (`red_tag_number`),
  ADD KEY `asset_id` (`asset_id`),
  ADD KEY `iirup_id` (`iirup_id`),
  ADD KEY `tagged_by` (`tagged_by`);

--
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
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
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

--
-- AUTO_INCREMENT for table `assets_archive`
--
ALTER TABLE `assets_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assets_new`
--
ALTER TABLE `assets_new`
<<<<<<< HEAD
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `asset_items`
--
ALTER TABLE `asset_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;
=======
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

--
-- AUTO_INCREMENT for table `asset_requests`
--
ALTER TABLE `asset_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrow_request_items`
--
ALTER TABLE `borrow_request_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
<<<<<<< HEAD
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
=======
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
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
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `generated_reports`
--
ALTER TABLE `generated_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `ics_form`
--
ALTER TABLE `ics_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `ics_items`
--
ALTER TABLE `ics_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `iirup_form`
--
ALTER TABLE `iirup_form`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `iirup_items`
--
ALTER TABLE `iirup_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

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
-- AUTO_INCREMENT for table `mr_details`
--
ALTER TABLE `mr_details`
<<<<<<< HEAD
  MODIFY `mr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
=======
  MODIFY `mr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `par_form`
--
ALTER TABLE `par_form`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

--
-- AUTO_INCREMENT for table `par_items`
--
ALTER TABLE `par_items`
<<<<<<< HEAD
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;
=======
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `red_tags`
--
ALTER TABLE `red_tags`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT for table `ris_items`
--
ALTER TABLE `ris_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

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
-- AUTO_INCREMENT for table `unit`
--
ALTER TABLE `unit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

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
<<<<<<< HEAD
=======
  ADD CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
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
<<<<<<< HEAD
-- Constraints for table `asset_items`
--
ALTER TABLE `asset_items`
  ADD CONSTRAINT `asset_items_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
=======
-- Constraints for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  ADD CONSTRAINT `borrow_requests_ibfk_3` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`),
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
  ADD CONSTRAINT `fk_borrow_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrow_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrow_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
<<<<<<< HEAD
-- Constraints for table `borrow_request_items`
--
ALTER TABLE `borrow_request_items`
  ADD CONSTRAINT `borrow_request_items_ibfk_1` FOREIGN KEY (`borrow_request_id`) REFERENCES `borrow_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrow_request_items_ibfk_2` FOREIGN KEY (`asset_item_id`) REFERENCES `asset_items` (`item_id`) ON DELETE CASCADE;

--
=======
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
-- Constraints for table `consumption_log`
--
ALTER TABLE `consumption_log`
  ADD CONSTRAINT `consumption_log_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consumption_log_ibfk_2` FOREIGN KEY (`recipient_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `consumption_log_ibfk_3` FOREIGN KEY (`dispensed_by_user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_consumption_log_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `generated_reports`
--
ALTER TABLE `generated_reports`
  ADD CONSTRAINT `generated_reports_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`);

--
-- Constraints for table `ics_items`
--
ALTER TABLE `ics_items`
  ADD CONSTRAINT `fk_ics_items_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  ADD CONSTRAINT `ics_items_ibfk_1` FOREIGN KEY (`ics_id`) REFERENCES `ics_form` (`id`) ON DELETE CASCADE;

--
<<<<<<< HEAD
=======
-- Constraints for table `iirup_items`
--
ALTER TABLE `iirup_items`
  ADD CONSTRAINT `fk_iirup_items_iirup` FOREIGN KEY (`iirup_id`) REFERENCES `iirup_form` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
-- Constraints for table `inventory_actions`
--
ALTER TABLE `inventory_actions`
  ADD CONSTRAINT `inventory_actions_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`),
  ADD CONSTRAINT `inventory_actions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `mr_details`
--
ALTER TABLE `mr_details`
  ADD CONSTRAINT `mr_details_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `ics_items` (`item_id`);

--
-- Constraints for table `par_form`
--
ALTER TABLE `par_form`
  ADD CONSTRAINT `par_form_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `par_items`
--
ALTER TABLE `par_items`
  ADD CONSTRAINT `par_items_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `par_form` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `par_items_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE SET NULL;

--
<<<<<<< HEAD
=======
-- Constraints for table `red_tags`
--
ALTER TABLE `red_tags`
  ADD CONSTRAINT `red_tags_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `red_tags_ibfk_2` FOREIGN KEY (`iirup_id`) REFERENCES `iirup_form` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `red_tags_ibfk_3` FOREIGN KEY (`tagged_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
-- Constraints for table `report_templates`
--
ALTER TABLE `report_templates`
  ADD CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ris_items`
--
ALTER TABLE `ris_items`
  ADD CONSTRAINT `ris_items_ibfk_1` FOREIGN KEY (`ris_form_id`) REFERENCES `ris_form` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD CONSTRAINT `system_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `system_logs_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL;
COMMIT;

<<<<<<< HEAD
=======
SET FOREIGN_KEY_CHECKS=1;


>>>>>>> 21b584494dbe6c9319494eb8e0d3b76e2c4e3ea6
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
