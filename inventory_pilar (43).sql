-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 14, 2025 at 09:08 AM
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
  `supplier` varchar(255) DEFAULT NULL,
  `ics_id` int(11) DEFAULT NULL,
  `par_id` int(11) DEFAULT NULL,
  `ris_id` int(11) DEFAULT NULL,
  `asset_new_id` int(11) DEFAULT NULL,
  `inventory_tag` varchar(255) DEFAULT NULL,
  `additional_images` text DEFAULT NULL COMMENT 'JSON array storing paths to up to 4 additional images for the asset',
  `enable_batch_tracking` tinyint(1) DEFAULT 0,
  `default_batch_size` int(11) DEFAULT 1,
  `batch_expiry_required` tinyint(1) DEFAULT 0,
  `batch_manufacturer_required` tinyint(1) DEFAULT 0,
  `current_borrowing_request_id` int(11) DEFAULT NULL,
  `guest_borrowing_request_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_name`, `category`, `description`, `quantity`, `added_stock`, `unit`, `status`, `acquisition_date`, `office_id`, `employee_id`, `end_user`, `red_tagged`, `last_updated`, `value`, `qr_code`, `type`, `image`, `serial_no`, `code`, `property_no`, `model`, `brand`, `supplier`, `ics_id`, `par_id`, `ris_id`, `asset_new_id`, `inventory_tag`, `additional_images`, `enable_batch_tracking`, `default_batch_size`, `batch_expiry_required`, `batch_manufacturer_required`, `current_borrowing_request_id`, `guest_borrowing_request_id`) VALUES
(1, 'Laptop AMD Ryzen', 6, 'Laptop AMD Ryzen', 1, 0, 'unit', 'borrowed', '2025-10-12', 23, 91, 'Angela Rizal', 1, '2025-10-14 03:44:56', 45000.00, '1.png', 'asset', 'asset_1_1760240852.jpg', '25-SN-000118', 'FUR-0018-10', 'OMM-05-030-ITS-03', '', 'Lenovo', NULL, 2, NULL, NULL, 1, 'PS-5S-03-F02-01-125-125', NULL, 0, 1, 0, 0, NULL, NULL),
(2, 'Notebook i7', NULL, 'Notebook i7', 1, 0, 'unit', 'borrowed', '2025-10-12', 2, NULL, NULL, 0, '2025-10-14 03:44:56', 70000.00, '2.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 2, NULL, 2, NULL, NULL, 0, 1, 0, 0, NULL, NULL),
(3, 'Notebook i7', NULL, 'Notebook i7', 1, 0, 'unit', 'pending', '2025-10-12', 2, NULL, NULL, 0, '2025-10-12 03:52:04', 70000.00, '3.png', 'asset', '', '', '', NULL, '', '', NULL, NULL, 2, NULL, 2, NULL, NULL, 0, 1, 0, 0, NULL, NULL),
(4, 'Notebook i7', NULL, 'Notebook i7', 1, 0, 'unit', 'pending', '2025-10-12', 23, NULL, NULL, 0, '2025-10-12 05:00:13', 70000.00, '4.png', 'asset', '', '', '', '{OFFICE}-2025-10-001', '', '', NULL, NULL, 3, NULL, 3, NULL, NULL, 0, 1, 0, 0, NULL, NULL),
(5, 'Notebook i7', NULL, 'Notebook i7', 1, 0, 'unit', 'pending', '2025-10-12', 23, NULL, NULL, 0, '2025-10-12 05:01:10', 70000.00, '5.png', 'asset', '', '', '', '{OFFICE}-2025-10-002', '', '', NULL, NULL, 4, NULL, 4, NULL, NULL, 0, 1, 0, 0, NULL, NULL),
(6, 'PC', 6, 'PC', 1, 0, 'unit', 'unserviceable', '2025-10-12', 23, 41, 'Jack Robertson', 0, '2025-10-12 10:29:36', 56000.00, '6.png', 'asset', '', '25-SN-000130', 'FUR-0026-10', 'HRMO-2025-10-003', '', '', NULL, NULL, 5, NULL, 5, 'PS-5S-03-F02-01-138-138', NULL, 0, 1, 0, 0, NULL, NULL),
(7, 'Mouse', NULL, 'Mouse', 1, 0, 'unit', 'pending', '2025-10-12', 19, NULL, NULL, 0, '2025-10-12 05:51:58', 450.00, '7.png', 'asset', '', '', '', NULL, '', '', NULL, 3, NULL, NULL, 6, NULL, NULL, 0, 1, 0, 0, NULL, NULL),
(8, 'Mouse', NULL, 'Mouse', 1, 0, 'unit', 'pending', '2025-10-12', 19, NULL, NULL, 0, '2025-10-12 05:51:58', 450.00, '8.png', 'asset', '', '', '', NULL, '', '', NULL, 3, NULL, NULL, 6, NULL, NULL, 0, 1, 0, 0, NULL, NULL),
(9, 'Laptop AMD Ryzen', 6, 'Laptop AMD Ryzen', 1, 0, 'unit', 'borrowed', '2025-10-12', 24, 91, 'Elton John Moises', 0, '2025-10-14 06:02:30', 45000.00, '9.png', 'asset', '', '25-SN-000159', 'FUR-0055-10', '2025-KALAHI-0045', '', '', NULL, 4, NULL, NULL, 7, 'PS-5S-03-F02-01-169-169', NULL, 0, 1, 0, 0, NULL, NULL),
(10, 'Desktop Computer (Core i5)', 6, 'Desktop Computer (Core i5)', 1, 0, 'unit', 'serviceable', '2025-10-12', 17, 91, 'Jack Robertson', 0, '2025-10-14 06:58:48', 56000.00, '10.png', 'asset', '', '25-SN-000161', 'FUR-0057-10', 'DILG-2025-10-004', '', '', NULL, NULL, 6, NULL, 8, 'PS-5S-03-F02-01-172-172', NULL, 0, 1, 0, 0, NULL, NULL),
(11, 'Bond paper', NULL, 'Bond paper', 2, 2, 'reams', 'unavailable', '2025-10-12', 18, NULL, NULL, 0, '2025-10-12 11:23:39', 340.00, '', 'consumable', '1760268219_paperone.jpg', '', '', '1', '', '', NULL, NULL, NULL, 2, NULL, '', NULL, 0, 1, 0, 0, NULL, NULL),
(12, 'Ballpen ', NULL, 'Ballpen ', 1, 1, 'box', 'available', '2025-10-12', 29, NULL, NULL, 0, '2025-10-12 07:14:00', 350.00, '', 'consumable', '', '', '', '1', '', '', NULL, NULL, NULL, 3, NULL, '', NULL, 0, 1, 0, 0, NULL, NULL),
(13, 'bond paper', NULL, 'bond paper', 20, 20, 'reams', 'available', '2025-10-12', 4, NULL, NULL, 0, '2025-10-13 11:36:24', 400.00, '', 'consumable', '1760269345_paperone.jpg', '', '', '1', '', '', NULL, NULL, NULL, 7, NULL, '', NULL, 0, 1, 0, 0, NULL, NULL),
(14, 'Toyota Service Vehicle', 4, 'Toyota Service Vehicle', 1, 0, 'unit', 'borrowed', '2025-10-12', 4, 91, 'Elton John Moises', 0, '2025-10-14 05:36:28', 750000.00, '14.png', 'asset', 'asset_14_1760276423.jpg', '25-SN-000153', 'FUR-0049-10', 'Supply Office-2025-10-005', 'Vios 1.3 XE', 'Toyota', 'Toyota Quezon Avenue', NULL, 7, NULL, 9, 'PS-5S-03-F02-01-162-162', NULL, 0, 1, 0, 0, NULL, NULL),
(18, '', NULL, 'Printer Paper A4', 10, 0, 'ream', 'available', '1970-01-01', 23, NULL, NULL, 0, '2025-10-12 21:38:02', 250.00, NULL, 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, NULL, NULL),
(19, '', NULL, 'Ballpen Black', 50, 0, 'piece', 'available', '1970-01-01', 23, NULL, NULL, 0, '2025-10-12 21:38:02', 15.00, NULL, 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 1, 0, 0, NULL, NULL);

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
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `employee_id` int(11) DEFAULT NULL,
  `end_user` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `code` varchar(255) DEFAULT NULL,
  `property_no` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `inventory_tag` varchar(255) DEFAULT NULL,
  `asset_new_id` int(11) DEFAULT NULL,
  `additional_images` text DEFAULT NULL,
  `deletion_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets_archive`
--

INSERT INTO `assets_archive` (`archive_id`, `id`, `asset_name`, `category`, `description`, `quantity`, `unit`, `status`, `acquisition_date`, `office_id`, `red_tagged`, `last_updated`, `value`, `qr_code`, `type`, `archived_at`, `employee_id`, `end_user`, `image`, `serial_no`, `code`, `property_no`, `model`, `brand`, `inventory_tag`, `asset_new_id`, `additional_images`, `deletion_reason`) VALUES
(1, 17, '', NULL, 'paper', 5, 'reams', 'available', '2025-10-13', 18, 0, '2025-10-13 01:57:08', 340.00, NULL, 'consumable', '2025-10-13 01:57:18', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, 16, '', NULL, 'ink', 15, 'pcs', 'available', '2025-10-13', 4, 0, '2025-10-13 01:52:23', 340.00, NULL, 'consumable', '2025-10-13 01:57:24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, 15, '', NULL, 'ballpen panda', 10, 'box', 'available', '2025-10-13', 4, 0, '2025-10-13 01:49:25', 100.00, NULL, 'consumable', '2025-10-13 01:57:30', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL);

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
(1, 'Laptop AMD Ryzen', 1, 45000.00, 'unit', 33, NULL, 2, '2025-10-12 10:18:04'),
(2, 'Notebook i7', 2, 70000.00, 'unit', 2, 2, NULL, '2025-10-12 10:52:04'),
(3, 'Notebook i7', 1, 70000.00, 'unit', 23, 3, NULL, '2025-10-12 12:00:13'),
(4, 'Notebook i7', 1, 70000.00, 'unit', 23, 4, NULL, '2025-10-12 12:01:10'),
(5, 'PC', 1, 56000.00, 'unit', 23, 5, NULL, '2025-10-12 12:22:14'),
(6, 'Mouse', 2, 450.00, 'unit', 19, NULL, 3, '2025-10-12 12:51:58'),
(7, 'Laptop AMD Ryzen', 1, 45000.00, 'unit', 24, NULL, 4, '2025-10-12 13:33:12'),
(8, 'Desktop Computer (Core i5)', 1, 56000.00, 'unit', 17, 6, NULL, '2025-10-12 13:53:50'),
(9, 'Toyota Service Vehicle', 1, 750000.00, 'unit', 4, 7, NULL, '2025-10-12 20:35:47');

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
(1, 1, 'ACQUIRED', 'ics_form', 2, NULL, NULL, NULL, 33, 'ICS OMM-039; Qty 1; UnitCost ₱45000.00; Total ₱45000.00', '2025-10-12 03:18:04'),
(2, 1, 'ASSIGNED', 'mr_details', NULL, NULL, 91, 33, NULL, 'MR create; PA: Walton Loneza; InvTag: PS-5S-03-F02-01-125-125', '2025-10-12 03:47:32'),
(3, 2, 'ACQUIRED', 'par_form', 2, NULL, NULL, NULL, 2, 'PAR IO-002; Qty 2; UnitCost ₱70000.00; Amount ₱140000.00', '2025-10-12 03:52:04'),
(4, 5, 'ACQUIRED', 'par_form', 4, NULL, NULL, NULL, 23, 'PAR H-004; Qty 1; UnitCost ₱70000.00; Amount ₱70000.00', '2025-10-12 05:01:10'),
(5, 6, 'ACQUIRED', 'par_form', 5, NULL, NULL, NULL, 23, 'PAR H-005; Qty 1; UnitCost ₱56000.00; Amount ₱56000.00', '2025-10-12 05:22:14'),
(6, 6, 'ASSIGNED', 'mr_details', NULL, NULL, 41, 23, NULL, 'MR create; PA: Hannah Phillips; InvTag: PS-5S-03-F02-01-138-138', '2025-10-12 05:47:20'),
(7, 7, 'ACQUIRED', 'ics_form', 3, NULL, NULL, NULL, 19, 'ICS GAD-040; Qty 2; UnitCost ₱450.00; Total ₱900.00', '2025-10-12 05:51:58'),
(8, 9, 'ACQUIRED', 'ics_form', 4, NULL, NULL, NULL, 24, 'ICS KALAHI-041; Qty 1; UnitCost ₱45000.00; Total ₱45000.00', '2025-10-12 06:33:12'),
(9, 1, 'TRANSFERRED', 'itr_form', 2, 91, 92, 19, 19, 'ITR ITR-0010-GAD-10; Reason: reassignement; To: Juan Dela Cruz', '2025-10-12 06:48:28'),
(10, 1, 'TRANSFERRED', 'itr_form', 3, 92, 92, 19, 19, 'ITR ITR-0011-GAD-11; Reason: reassignement; To: Juan Dela Cruz', '2025-10-12 06:48:36'),
(11, 1, 'TRANSFERRED', 'itr_form', 4, 92, 92, 19, 19, 'ITR ITR-0012-GAD-12; Reason: reassignement; To: Juan Dela Cruz', '2025-10-12 06:48:43'),
(12, 1, 'TRANSFERRED', 'itr_form', 5, 92, 92, 19, 19, 'ITR ITR-0013-GAD-13; Reason: reassignement; To: Juan Dela Cruz', '2025-10-12 06:48:51'),
(13, 1, 'TRANSFERRED', 'itr_form', 6, 92, 92, 19, 19, 'ITR ITR-0014-GAD-14; Reason: reassignement; To: Juan Dela Cruz', '2025-10-12 06:48:58'),
(14, 1, 'TRANSFERRED', 'itr_form', 7, 92, 92, 19, 19, 'ITR ITR-0015-GAD-15; Reason: reassignement; To: Juan Dela Cruz', '2025-10-12 06:49:05'),
(15, 1, 'TRANSFERRED', 'itr_form', 8, 92, 92, 19, 19, 'ITR ITR-0016-GAD-16; Reason: reassignement; To: Juan Dela Cruz', '2025-10-12 06:49:12'),
(16, 1, 'TRANSFERRED', 'itr_form', 9, 92, 9, 19, 19, 'ITR ITR-0017-GAD-17; Reason: reassignement; To: John Smith', '2025-10-12 06:49:18'),
(17, 1, 'TRANSFERRED', 'itr_form', 10, 9, 91, 23, 23, 'ITR ITR-0018-HRMO-18; Reason: Reassignment; To: Walton Loneza', '2025-10-12 06:52:23'),
(18, 10, 'ACQUIRED', 'par_form', 6, NULL, NULL, NULL, 17, 'PAR DILG-006; Qty 1; UnitCost ₱56000.00; Amount ₱56000.00', '2025-10-12 06:53:50'),
(19, 1, 'DISPOSAL_LISTED', 'iirup_form', 2, NULL, NULL, NULL, NULL, 'IIRUP #2; Remarks: Unserviceable; Method: N/A', '2025-10-12 07:19:33'),
(20, 1, 'RED_TAGGED', 'red_tags', 1, NULL, NULL, NULL, NULL, 'Removal: Broken; Action: For Disposal; Location: Supply Office', '2025-10-12 07:20:08'),
(21, 6, 'DISPOSAL_LISTED', 'iirup_form', 3, NULL, NULL, NULL, NULL, 'IIRUP #3; Remarks: Unserviceable; Method: N/A', '2025-10-12 10:29:36'),
(22, 14, 'ACQUIRED', 'par_form', 7, NULL, NULL, NULL, 4, 'PAR Supply Office-007; Qty 1; UnitCost ₱750000.00; Amount ₱750000.00', '2025-10-12 13:35:47'),
(23, 14, 'ASSIGNED', 'mr_details', NULL, NULL, 91, 4, NULL, 'MR create; PA: Walton Loneza; InvTag: PS-5S-03-F02-01-162-162', '2025-10-12 13:40:23'),
(24, 9, 'ASSIGNED', 'mr_details', NULL, NULL, 91, 24, NULL, 'MR create; PA: Walton Loneza; InvTag: PS-5S-03-F02-01-169-169', '2025-10-12 15:12:13'),
(25, 10, 'ASSIGNED', 'mr_details', NULL, NULL, 91, 17, NULL, 'MR create; PA: Walton Loneza; InvTag: PS-5S-03-F02-01-172-172', '2025-10-12 15:12:50'),
(26, 14, '', 'borrow_form_submissions', 10, NULL, NULL, NULL, NULL, 'Asset borrowed by Walton Loneza (Submission #10)', '2025-10-14 05:36:28'),
(27, 9, '', 'borrow_form_submissions', 17, NULL, NULL, NULL, NULL, 'Asset borrowed by Walton loneza (Submission #17)', '2025-10-14 05:42:46'),
(28, 9, '', 'borrow_form_submissions', 17, NULL, NULL, NULL, NULL, 'Asset returned by Walton loneza (Submission #17)', '2025-10-14 05:49:28'),
(29, 9, '', 'borrow_form_submissions', 18, NULL, NULL, NULL, NULL, 'Asset borrowed by Walton loneza (Submission #18)', '2025-10-14 06:02:30'),
(30, 10, '', 'borrow_form_submissions', 19, NULL, NULL, NULL, NULL, 'Asset borrowed by Walton loneza (Submission #19)', '2025-10-14 06:25:56'),
(31, 10, '', 'borrow_form_submissions', 19, NULL, NULL, NULL, NULL, 'Asset returned by  (Submission #19)', '2025-10-14 06:37:57'),
(32, 10, '', 'borrow_form_submissions', 21, NULL, NULL, NULL, NULL, 'Asset borrowed by Walton loneza (Submission #21)', '2025-10-14 06:56:58'),
(33, 10, '', 'borrow_form_submissions', 21, NULL, NULL, NULL, NULL, 'Asset returned by Walton loneza (Submission #21)', '2025-10-14 06:58:48');

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
(1, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 14:03:44'),
(2, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 14:04:07'),
(3, 1, 'Mark Jayson Namia', 'BACKUP_SUCCESS', 'System', 'Local monthly backup created: inventory_pilar_auto_backup_20251011_160413.sql', 'backups', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 14:04:13'),
(4, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 14:19:55'),
(5, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 14:21:09'),
(6, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 16:45:31'),
(7, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 16:55:48'),
(8, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 16:56:02'),
(9, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 17:29:24'),
(10, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 17:29:31'),
(11, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 17:29:36'),
(12, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 17:29:44'),
(13, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 17:46:49'),
(14, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-11 17:46:54'),
(15, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 01:21:43'),
(16, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 01:46:39'),
(17, NULL, 'michael', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'michael\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 01:49:29'),
(18, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 01:50:11'),
(19, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:35:07'),
(20, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:35:16'),
(21, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:36:59'),
(23, NULL, 'michael', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'michael\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:37:52'),
(24, NULL, 'michael', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'michael\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:38:07'),
(25, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:38:24'),
(26, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:55:07'),
(27, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:55:12'),
(28, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:55:32'),
(29, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:55:47'),
(30, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:56:19'),
(31, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 02:56:24'),
(32, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: {OFFICE}-039 - LGU-PILAR/OMM (Destination: OMM)', 'ics_form', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 03:18:04'),
(33, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS {OFFICE}-039: Laptop AMD Ryzen (Qty: 1, Unit Cost: ₱45,000.00, Total: ₱45,000.00)', 'ics_items', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 03:18:04'),
(34, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 04:09:10'),
(35, NULL, 'ompdc', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'ompdc\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 04:09:19'),
(36, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 04:09:47'),
(37, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 04:20:27'),
(38, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 04:20:32'),
(39, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: {OFFICE}-040 - GAD (Destination: GAD)', 'ics_form', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 05:51:58'),
(40, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS {OFFICE}-040: Mouse (Qty: 2, Unit Cost: ₱450.00, Total: ₱900.00)', 'ics_items', 2, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 05:51:58'),
(41, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Form', 'Created new ICS form: KALAHI-041 - KALAHI (Destination: KALAHI)', 'ics_form', 4, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 06:33:12'),
(42, 17, 'Mark Jayson Namia', 'CREATE', 'ICS Items', 'Added item to ICS KALAHI-041: Laptop AMD Ryzen (Qty: 1, Unit Cost: ₱45,000.00, Total: ₱45,000.00)', 'ics_items', 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 06:33:12'),
(43, 17, 'Mark Jayson Namia', 'CREATE', 'Red Tags', 'Created Red Tag: RT-0029 for asset: Laptop AMD Ryzen (Reason: Broken, Action: For Disposal)', 'red_tags', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:20:08'),
(44, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:23:07'),
(45, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:23:14'),
(46, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:25:12'),
(48, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:26:23'),
(49, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:27:58'),
(50, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:28:09'),
(51, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:41:12'),
(52, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:41:21'),
(53, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:42:28'),
(54, NULL, 'walts', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'walts\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:42:45'),
(55, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:42:57'),
(56, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:44:22'),
(57, 30, 'mike', 'LOGIN', 'Authentication', 'User \'mike\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:44:31'),
(58, 30, 'mike', 'LOGOUT', 'Authentication', 'User \'mike\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:49:36'),
(59, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:49:53'),
(60, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:51:50'),
(61, 30, 'mike', 'LOGIN', 'Authentication', 'User \'mike\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 07:51:58'),
(62, 30, 'mike', 'LOGOUT', 'Authentication', 'User \'mike\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 08:34:16'),
(63, NULL, 'michael', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'michael\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 08:34:25'),
(64, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 08:34:34'),
(65, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 08:35:17'),
(66, 30, 'mike', 'LOGIN', 'Authentication', 'User \'mike\' logged in successfully (Role: user)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 08:35:29'),
(67, 30, 'mike', 'LOGOUT', 'Authentication', 'User \'mike\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 08:38:11'),
(68, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 09:40:28'),
(69, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:26:21'),
(70, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:26:35'),
(71, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:33:18'),
(72, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:33:29'),
(73, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:34:17'),
(74, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:34:34'),
(75, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:41:41'),
(76, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:41:47'),
(77, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:43:40'),
(78, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:43:52'),
(79, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:45:09'),
(80, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:45:15'),
(81, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:55:42'),
(82, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:55:51'),
(83, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:55:51'),
(84, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:56:00'),
(85, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 11:57:05'),
(86, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 12:04:24'),
(87, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 12:04:35'),
(88, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 12:38:17'),
(89, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 12:38:24'),
(90, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 14:19:38'),
(91, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 14:26:39'),
(92, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 15:23:38'),
(94, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 15:25:51'),
(95, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 15:26:15'),
(96, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 22:48:12'),
(97, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 22:57:21'),
(98, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-12 22:57:28'),
(99, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 01:26:18'),
(100, 17, 'Mark Jayson Namia', 'DELETE_CONSUMABLE_ENHANCED', 'Assets', 'DELETE_CONSUMABLE_ENHANCED asset: paper (Consumable Deletion - Qty: 5, Unit Value: ₱340.00, Total Value: ₱1,700.00, Office: MENRU, Category: Uncategorized, Status: Available, Source: Enhanced Delete System)', 'assets', 17, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 01:57:18'),
(101, 17, 'Mark Jayson Namia', 'DELETE_CONSUMABLE_ENHANCED', 'Assets', 'DELETE_CONSUMABLE_ENHANCED asset: ink (Consumable Deletion - Qty: 15, Unit Value: ₱340.00, Total Value: ₱5,100.00, Office: Supply Office, Category: Uncategorized, Status: Available, Source: Enhanced Delete System)', 'assets', 16, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 01:57:24'),
(102, 17, 'Mark Jayson Namia', 'DELETE_CONSUMABLE_ENHANCED', 'Assets', 'DELETE_CONSUMABLE_ENHANCED asset: ballpen panda (Consumable Deletion - Qty: 10, Unit Value: ₱100.00, Total Value: ₱1,000.00, Office: Supply Office, Category: Uncategorized, Status: Available, Source: Enhanced Delete System)', 'assets', 15, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 01:57:30'),
(103, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 02:38:59'),
(104, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 02:39:09'),
(105, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:21:19'),
(106, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:21:29'),
(107, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:22:35'),
(108, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:22:40'),
(109, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:24:18'),
(110, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:24:23'),
(111, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:31:25'),
(112, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:31:32'),
(113, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:32:35'),
(114, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:32:45'),
(115, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:37:25'),
(117, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 03:58:34'),
(118, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 04:30:47'),
(119, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 04:30:55'),
(120, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 11:24:08'),
(121, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 11:26:49'),
(122, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 11:26:58'),
(123, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 11:35:10'),
(124, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 11:35:16'),
(125, 17, 'Mark Jayson Namia', 'GENERATE', 'Reports', 'Generated PAR PDF report with filters: PAR: Supply Office-007, Entity: Supply Office', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 12:00:17'),
(126, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 12:35:49'),
(127, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 12:35:57'),
(128, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 12:47:11'),
(129, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 12:47:17'),
(130, 17, 'Mark Jayson Namia', 'DELETE', 'Assets', 'DELETE asset: Multi Purpose bldg. (Classification: BUILDING, Location: LGU-Complex)', 'assets', 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 13:38:32'),
(131, 17, 'Mark Jayson Namia', 'BULK_IMPORT', 'Bulk Operations', 'Bulk IMPORT: 1 items (CSV/Excel Infrastructure from file: infra test.csv)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 13:54:43'),
(132, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 13:55:59'),
(134, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:20:30'),
(135, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 1 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:22:02'),
(136, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 1 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:23:53'),
(137, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 1 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:26:44'),
(138, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 1 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:28:08'),
(139, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 1 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:30:01'),
(140, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 1 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:31:54'),
(141, 17, 'Mark Jayson Namia', 'BULK_PRINT', 'Bulk Operations', 'Bulk PRINT: 1 items (MR Records)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:33:12'),
(142, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:34:06'),
(143, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:34:13'),
(144, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:42:49'),
(146, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 18_5 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.5 Mobile/15E148 Safari/604.1', '2025-10-13 14:49:00'),
(147, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 14:54:13'),
(150, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:39:20'),
(151, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:39:48'),
(152, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:39:58'),
(153, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:50:23'),
(154, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:50:30'),
(155, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:51:54'),
(156, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:52:03'),
(157, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:52:28'),
(158, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:52:33'),
(159, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:52:40'),
(160, 32, 'michael', 'LOGIN', 'Authentication', 'User \'michael\' logged in successfully (Role: office_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:52:50'),
(161, 32, 'michael', 'LOGOUT', 'Authentication', 'User \'michael\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:53:46'),
(162, 1, 'ompdc', 'LOGIN', 'Authentication', 'User \'ompdc\' logged in successfully (Role: super_admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:53:55'),
(163, 1, 'OMPDC', 'LOGOUT', 'Authentication', 'User \'OMPDC\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:54:30'),
(164, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:54:35'),
(165, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-13 22:55:41'),
(167, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 01:50:47'),
(168, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 01:50:54'),
(169, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 03:52:28'),
(172, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 04:22:52'),
(173, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 04:23:20'),
(177, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 04:34:33'),
(178, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 04:35:12'),
(180, NULL, 'nami', 'LOGIN_FAILED', 'Authentication', 'Failed login attempt for username \'nami\' - incorrect password', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 04:42:11'),
(182, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 04:58:02'),
(183, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 05:03:23'),
(185, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 05:07:15'),
(186, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 05:11:27'),
(189, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 05:32:06'),
(190, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 05:36:57'),
(192, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 05:42:21'),
(193, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 05:44:49'),
(195, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 05:49:38'),
(196, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 05:53:17'),
(198, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 05:57:54'),
(199, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 06:07:16'),
(201, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 06:25:37'),
(202, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 06:26:06'),
(204, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 06:38:08'),
(205, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 06:38:37'),
(209, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 06:52:38'),
(210, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 06:53:05'),
(212, 17, 'nami', 'LOGIN', 'Authentication', 'User \'nami\' logged in successfully (Role: admin)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 06:56:47'),
(213, 17, 'nami', 'LOGOUT', 'Authentication', 'User \'nami\' logged out successfully', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/141.0.0.0 Safari/537.36', '2025-10-14 06:58:12');

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
(1, 'inventory_pilar_auto_backup_20251011_160413.sql', 'C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\backups\\inventory_pilar_auto_backup_20251011_160413.sql', 198229, 'local', 'success', 'scheduled', NULL, '2025-10-11 14:04:13');

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
-- Table structure for table `borrow_form_submissions`
--

CREATE TABLE `borrow_form_submissions` (
  `id` int(11) NOT NULL,
  `submission_number` varchar(50) NOT NULL,
  `guest_session_id` varchar(255) NOT NULL,
  `guest_id` varchar(64) DEFAULT NULL,
  `guest_email` varchar(255) DEFAULT NULL,
  `guest_name` varchar(255) NOT NULL,
  `date_borrowed` date NOT NULL,
  `schedule_return` date NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `releasing_officer` varchar(255) NOT NULL,
  `approved_by` varchar(255) NOT NULL,
  `items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL COMMENT 'JSON array of borrowed items with thing, qty, remarks' CHECK (json_valid(`items`)),
  `status` enum('pending','approved','rejected','completed','cancelled','returned') NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_form_submissions`
--

INSERT INTO `borrow_form_submissions` (`id`, `submission_number`, `guest_session_id`, `guest_id`, `guest_email`, `guest_name`, `date_borrowed`, `schedule_return`, `barangay`, `contact`, `releasing_officer`, `approved_by`, `items`, `status`, `submitted_at`, `updated_at`) VALUES
(1, 'BFS-20251014-001', '', NULL, NULL, 'Test Guest', '2025-10-14', '2025-10-21', 'Test Barangay', '09123456789', 'Test Officer', 'Test Approver', '[{\"asset_id\":1,\"thing\":\"Test Asset\",\"inventory_tag\":\"TAG001\",\"property_no\":\"PROP001\",\"category\":\"Test Category\",\"qty\":\"1\",\"remarks\":\"Test remarks\"}]', 'pending', '2025-10-14 01:14:59', '2025-10-14 01:14:59'),
(2, 'BFS-20251014-002', '', NULL, NULL, 'Walton Loneza', '2025-10-14', '2025-10-21', 'Centro Occidental', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. S. - RONEL', '[{\"asset_id\":14,\"thing\":\"Toyota Service Vehicle\",\"inventory_tag\":\"PS-5S-03-F02-01-162-162\",\"property_no\":\"Supply Office-2025-10-005\",\"category\":\"Vehicles\",\"qty\":\"1\",\"remarks\":\"\"},{\"asset_id\":9,\"thing\":\"Laptop AMD Ryzen\",\"inventory_tag\":\"PS-5S-03-F02-01-169-169\",\"property_no\":\"2025-KALAHI-0045\",\"category\":\"Information & Communication Technology\",\"qty\":\"1\",\"remarks\":\"\"}]', 'rejected', '2025-10-14 01:15:36', '2025-10-14 05:36:28'),
(3, 'BFS-20251014-999', '', NULL, NULL, 'Test Guest User', '2025-10-14', '2025-10-21', 'Test Barangay', '09123456789', 'Test Officer', 'Test Approver', '[{\"asset_id\":1,\"thing\":\"Dell XPS 15 Laptop\",\"inventory_tag\":\"DELL-001\",\"property_no\":\"PROP-001\",\"category\":\"Computer Equipment\",\"qty\":\"1\",\"remarks\":\"For office work\"},{\"asset_id\":2,\"thing\":\"HP LaserJet Printer\",\"inventory_tag\":\"HP-002\",\"property_no\":\"PROP-002\",\"category\":\"Office Equipment\",\"qty\":\"1\",\"remarks\":\"Color printing required\"}]', 'pending', '2025-10-14 01:18:38', '2025-10-14 01:18:38'),
(5, 'BFS-20251014-SESS', '', NULL, 'guest@pilar.gov.ph', 'Test Guest User', '2025-10-14', '2025-10-21', 'Test Barangay', '09123456789', 'Test Officer', 'Test Approver', '[{\"asset_id\":1,\"thing\":\"Dell XPS 15 Laptop\",\"inventory_tag\":\"DELL-001\",\"property_no\":\"PROP-001\",\"category\":\"Computer Equipment\",\"qty\":\"1\",\"remarks\":\"For office work\"},{\"asset_id\":2,\"thing\":\"HP LaserJet Printer\",\"inventory_tag\":\"HP-002\",\"property_no\":\"PROP-002\",\"category\":\"Office Equipment\",\"qty\":\"1\",\"remarks\":\"Color printing required\"}]', 'approved', '2025-10-14 01:31:36', '2025-10-14 03:44:56'),
(6, 'BFS-20251014-SESS-223', '66k7hb0stv426m5i253qqpnk0n', NULL, 'guest@pilar.gov.ph', 'Session Test User', '2025-10-14', '2025-10-19', 'Test Barangay', '09123456789', 'Session Officer', 'Session Approver', '[{\"asset_id\":1,\"thing\":\"MacBook Pro\",\"inventory_tag\":\"MBP-001\",\"property_no\":\"PROP-MBP\",\"category\":\"Computer Equipment\",\"qty\":\"1\",\"remarks\":\"For development work\"}]', 'pending', '2025-10-14 01:32:19', '2025-10-14 01:32:19'),
(7, 'BFS-20251014-SESS-708', '8g6h30u78onm252us3ai7hvkte', NULL, 'guest@pilar.gov.ph', 'Session Test User', '2025-10-14', '2025-10-19', 'Test Barangay', '09123456789', 'Session Officer', 'Session Approver', '[{\"asset_id\":1,\"thing\":\"MacBook Pro\",\"inventory_tag\":\"MBP-001\",\"property_no\":\"PROP-MBP\",\"category\":\"Computer Equipment\",\"qty\":\"1\",\"remarks\":\"For development work\"}]', 'pending', '2025-10-14 01:34:54', '2025-10-14 01:34:54'),
(8, 'BFS-20251014-SESS-676', '9g8bs0r1tjgvf5k10de81bq478', NULL, 'guest@pilar.gov.ph', 'Session Test User', '2025-10-14', '2025-10-19', 'Test Barangay', '09123456789', 'Session Officer', 'Session Approver', '[{\"asset_id\":1,\"thing\":\"MacBook Pro\",\"inventory_tag\":\"MBP-001\",\"property_no\":\"PROP-MBP\",\"category\":\"Computer Equipment\",\"qty\":\"1\",\"remarks\":\"For development work\"}]', 'pending', '2025-10-14 01:35:06', '2025-10-14 01:35:06'),
(9, 'BFS-20251014-003', 'h9smokgmimpcghjfk3mjhkr9ge', NULL, 'guest@pilar.gov.ph', 'Test User', '2025-10-14', '2025-10-21', 'Test Barangay', '09123456789', 'Test Officer', 'Test Approver', '[{\"asset_id\":1,\"thing\":\"Test Laptop\",\"inventory_tag\":\"TEST-001\",\"property_no\":\"PROP-TEST\",\"category\":\"Computer Equipment\",\"qty\":\"1\",\"remarks\":\"\"}]', 'approved', '2025-10-14 01:35:57', '2025-10-14 03:44:23'),
(10, 'BFS-20251014-004', 'unicrlnq3htrrfkaikopfr5vcv', NULL, NULL, 'Walton Loneza', '2025-10-14', '2025-10-21', 'ALCALA', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. S. - RONEL', '[{\"asset_id\":14,\"thing\":\"Toyota Service Vehicle\",\"inventory_tag\":\"PS-5S-03-F02-01-162-162\",\"property_no\":\"Supply Office-2025-10-005\",\"category\":\"Vehicles\",\"qty\":\"1\",\"remarks\":\"\"}]', 'approved', '2025-10-14 01:36:12', '2025-10-14 05:36:28'),
(11, 'BFS-20251014-005', 'unicrlnq3htrrfkaikopfr5vcv', NULL, NULL, 'Walton Loneza', '2025-10-14', '2025-10-21', 'ALCALA', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. S. - RONEL', '[{\"asset_id\":14,\"thing\":\"Toyota Service Vehicle\",\"inventory_tag\":\"PS-5S-03-F02-01-162-162\",\"property_no\":\"Supply Office-2025-10-005\",\"category\":\"Vehicles\",\"qty\":\"1\",\"remarks\":\"\"}]', 'approved', '2025-10-14 01:37:05', '2025-10-14 03:32:20'),
(12, 'BFS-20251014-SESS-001', 'chuvdi11eageuqjngvjhe1ee3k', NULL, 'guest@pilar.gov.ph', 'Session Test Guest', '2025-10-14', '2025-10-21', 'Session Barangay', '09123456789', 'Session Officer', 'Session Approver', '[{\"asset_id\":1,\"thing\":\"Test Laptop for Session\",\"inventory_tag\":\"TEST-SESS\",\"property_no\":\"PROP-SESS\",\"category\":\"Computer Equipment\",\"qty\":\"1\",\"remarks\":\"\"}]', 'rejected', '2025-10-14 01:39:45', '2025-10-14 03:31:53'),
(13, 'BFS-20251014-DEMO-001', 'flg5la3qhj20alfomb4gke7ib9', NULL, 'guest@pilar.gov.ph', 'Demo Guest User', '2025-10-14', '2025-10-21', 'Demo Barangay', '09123456789', 'Demo Officer', 'Demo Approver', '[{\"asset_id\":1,\"thing\":\"Demo Laptop for Complete Test\",\"inventory_tag\":\"DEMO-001\",\"property_no\":\"PROP-DEMO\",\"category\":\"Computer Equipment\",\"qty\":\"1\",\"remarks\":\"\"}]', 'approved', '2025-10-14 01:47:55', '2025-10-14 03:06:12'),
(14, 'BFS-20251014-006', 'skirguf0gm1tsalo1e1g548lfa', NULL, 'guest@pilar.gov.ph', 'Walton Loneza', '2025-10-14', '2025-10-21', 'ALCALA', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. S. - RONEL', '[{\"asset_id\":14,\"thing\":\"Toyota Service Vehicle\",\"inventory_tag\":\"PS-5S-03-F02-01-162-162\",\"property_no\":\"Supply Office-2025-10-005\",\"category\":\"Vehicles\",\"qty\":\"1\",\"remarks\":\"\"}]', '', '2025-10-14 03:54:29', '2025-10-14 04:18:16'),
(15, 'BFS-20251014-007', 'skirguf0gm1tsalo1e1g548lfa', NULL, 'guest@pilar.gov.ph', 'Walton Loneza', '2025-10-14', '2025-10-21', 'ALCALA', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. S. - RONEL', '[{\"asset_id\":14,\"thing\":\"Toyota Service Vehicle\",\"inventory_tag\":\"PS-5S-03-F02-01-162-162\",\"property_no\":\"Supply Office-2025-10-005\",\"category\":\"Vehicles\",\"qty\":\"1\",\"remarks\":\"\"}]', 'cancelled', '2025-10-14 03:56:11', '2025-10-14 04:19:11'),
(16, 'BFS-20251014-008', '4danssmrp0uugee3432n4gme85', 'c51ad83fb94dc8374cc2cab32930d37ba5e8aa64704234f9b6969e08320d86f5', 'guest@pilar.gov.ph', 'Walton Loneza', '2025-10-14', '2025-10-21', 'ALCALA', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. S. - RONEL', '[{\"asset_id\":10,\"thing\":\"Desktop Computer (Core i5)\",\"inventory_tag\":\"PS-5S-03-F02-01-172-172\",\"property_no\":\"DILG-2025-10-004\",\"category\":\"Information & Communication Technology\",\"qty\":\"1\",\"remarks\":\"\"}]', 'returned', '2025-10-14 04:33:40', '2025-10-14 04:46:32'),
(17, 'BFS-20251014-009', 'kti0emv89b0d84ff7ob4dp7ene', 'c51ad83fb94dc8374cc2cab32930d37ba5e8aa64704234f9b6969e08320d86f5', 'waltonloneza@gmail.com', 'Walton loneza', '2025-10-14', '2025-10-21', 'ALCALA', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. S. - RONEL', '[{\"asset_id\":9,\"thing\":\"Laptop AMD Ryzen\",\"inventory_tag\":\"PS-5S-03-F02-01-169-169\",\"property_no\":\"2025-KALAHI-0045\",\"category\":\"Information & Communication Technology\",\"qty\":\"1\",\"remarks\":\"\"}]', 'returned', '2025-10-14 05:41:49', '2025-10-14 05:49:28'),
(18, 'BFS-20251014-010', '1rgad3g8ron3vt2telaabgjo3n', 'c51ad83fb94dc8374cc2cab32930d37ba5e8aa64704234f9b6969e08320d86f5', 'waltonloneza@gmail.com', 'Walton loneza', '2025-10-14', '2025-10-21', 'ALCALA', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. SY-REYES', '[{\"asset_id\":9,\"thing\":\"Laptop AMD Ryzen\",\"inventory_tag\":\"PS-5S-03-F02-01-169-169\",\"property_no\":\"2025-KALAHI-0045\",\"category\":\"Information & Communication Technology\",\"qty\":\"1\",\"remarks\":\"\"}]', 'approved', '2025-10-14 05:57:43', '2025-10-14 06:02:30'),
(19, 'BFS-20251014-011', 'dbuvrgeou4rlfsv4vlqciftu63', 'c51ad83fb94dc8374cc2cab32930d37ba5e8aa64704234f9b6969e08320d86f5', 'waltonloneza@gmail.com', 'Walton loneza', '2025-10-14', '2025-10-21', 'ALCALA', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. SY-REYES', '[{\"asset_id\":10,\"thing\":\"Desktop Computer (Core i5)\",\"inventory_tag\":\"PS-5S-03-F02-01-172-172\",\"property_no\":\"DILG-2025-10-004\",\"category\":\"Information & Communication Technology\",\"qty\":\"1\",\"remarks\":\"\"}]', 'returned', '2025-10-14 06:25:13', '2025-10-14 06:37:57'),
(20, 'BFS-20251014-012', 'cv0hoq1r9jm09fqo7cudlgr8p4', 'c51ad83fb94dc8374cc2cab32930d37ba5e8aa64704234f9b6969e08320d86f5', 'waltonloneza@gmail.com', 'Walton loneza', '2025-10-14', '2025-10-21', 'ALCALA', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. SY-REYES', '[{\"asset_id\":10,\"thing\":\"Desktop Computer (Core i5)\",\"inventory_tag\":\"PS-5S-03-F02-01-172-172\",\"property_no\":\"DILG-2025-10-004\",\"category\":\"Information & Communication Technology\",\"qty\":\"1\",\"remarks\":\"\"}]', 'cancelled', '2025-10-14 06:38:54', '2025-10-14 06:39:16'),
(21, 'BFS-20251014-013', 't2e3c9irc9deci8stctcffpcmn', 'c51ad83fb94dc8374cc2cab32930d37ba5e8aa64704234f9b6969e08320d86f5', 'waltonloneza@gmail.com', 'Walton loneza', '2025-10-14', '2025-10-21', 'PANOYPOY', '09107171456', 'IVAN CHRISTOPHER R. MILLABAS', 'CAROLYN C. SY-REYES', '[{\"asset_id\":10,\"thing\":\"Desktop Computer (Core i5)\",\"inventory_tag\":\"PS-5S-03-F02-01-172-172\",\"property_no\":\"DILG-2025-10-004\",\"category\":\"Information & Communication Technology\",\"qty\":\"1\",\"remarks\":\"\"}]', 'returned', '2025-10-14 06:53:30', '2025-10-14 06:58:48');

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
  `purpose` varchar(255) NOT NULL,
  `requested_return_date` date DEFAULT NULL COMMENT 'Expected return date for inter-department borrow requests',
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
(1, 'Office Equipments', 'OFFEQ', 0, 'asset'),
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

--
-- Dumping data for table `consumption_log`
--

INSERT INTO `consumption_log` (`id`, `asset_id`, `office_id`, `quantity_consumed`, `recipient_user_id`, `dispensed_by_user_id`, `consumption_date`, `remarks`) VALUES
(1, 13, 4, 2, 32, 32, '2025-10-12 11:35:52', 'consumed'),
(2, 13, 4, 1, 32, 32, '2025-10-09 17:00:00', ''),
(3, 13, 4, 24, 32, 32, '2025-10-12 17:00:00', '');

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
-- Table structure for table `email_notifications`
--

CREATE TABLE `email_notifications` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `recipient_email` varchar(255) DEFAULT NULL,
  `recipient_name` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `error_message` text DEFAULT NULL,
  `related_asset_id` int(11) DEFAULT NULL,
  `related_mr_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `email_notifications`
--

INSERT INTO `email_notifications` (`id`, `type`, `recipient_email`, `recipient_name`, `subject`, `body`, `status`, `error_message`, `related_asset_id`, `related_mr_id`, `created_at`) VALUES
(1, 'MR_CREATED', 'waltonloneza@gmail.com', 'Walton Loneza', 'New Material Receipt (MR) Assignment Notification', 'Hello Walton Loneza,<br><br>You have been set as the Person Accountable for an item in the PILAR Asset Inventory system.<br><ul><li><strong>Office:</strong> OMM</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 2, '2025-10-12 03:47:36'),
(2, 'MR_CREATED', NULL, 'Hannah Phillips', 'New Material Receipt (MR) Assignment Notification', 'Hello Hannah Phillips,<br><br>You have been set as the Person Accountable for an item in the PILAR Asset Inventory system.<br><ul><li><strong>Office:</strong> HRMO</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-138-138</li><li><strong>Description:</strong> PC</li><li><strong>Property No.:</strong> HRMO-2025-10-003</li><li><strong>Serial No.:</strong> 25-SN-000130</li></ul>If this was not expected, please contact your system administrator.', 'no_email', NULL, 6, 6, '2025-10-12 05:47:20'),
(3, 'ITR_TRANSFER', 'waltonloneza@gmail.com', 'Walton Loneza', 'ITR Asset Transfer Notification (FROM)', 'Hello Walton Loneza,<br><br>An asset has been transferred from you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0010-{OFFICE}-10</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>To Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 2, '2025-10-12 06:48:32'),
(4, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (TO)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred to you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0010-{OFFICE}-10</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>From Accountable Officer:</strong> Walton Loneza</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 2, '2025-10-12 06:48:36'),
(5, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (FROM)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred from you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0011-{OFFICE}-11</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>To Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 3, '2025-10-12 06:48:39'),
(6, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (TO)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred to you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0011-{OFFICE}-11</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>From Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 3, '2025-10-12 06:48:43'),
(7, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (FROM)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred from you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0012-{OFFICE}-12</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>To Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 4, '2025-10-12 06:48:46'),
(8, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (TO)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred to you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0012-{OFFICE}-12</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>From Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 4, '2025-10-12 06:48:51'),
(9, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (FROM)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred from you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0013-{OFFICE}-13</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>To Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 5, '2025-10-12 06:48:54'),
(10, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (TO)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred to you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0013-{OFFICE}-13</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>From Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 5, '2025-10-12 06:48:58'),
(11, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (FROM)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred from you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0014-{OFFICE}-14</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>To Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 6, '2025-10-12 06:49:01'),
(12, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (TO)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred to you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0014-{OFFICE}-14</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>From Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 6, '2025-10-12 06:49:05'),
(13, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (FROM)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred from you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0015-{OFFICE}-15</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>To Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 7, '2025-10-12 06:49:08'),
(14, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (TO)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred to you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0015-{OFFICE}-15</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>From Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 7, '2025-10-12 06:49:12'),
(15, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (FROM)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred from you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0016-{OFFICE}-16</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>To Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 8, '2025-10-12 06:49:15'),
(16, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (TO)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred to you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0016-{OFFICE}-16</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>From Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 8, '2025-10-12 06:49:18'),
(17, 'ITR_TRANSFER', 'waltielappy@gmail.com', 'Juan Dela Cruz', 'ITR Asset Transfer Notification (FROM)', 'Hello Juan Dela Cruz,<br><br>An asset has been transferred from you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0017-{OFFICE}-17</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>To Accountable Officer:</strong> John Smith</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 9, '2025-10-12 06:49:22'),
(18, 'ITR_TRANSFER', NULL, 'John Smith', 'ITR Asset Transfer Notification (TO)', 'Hello John Smith,<br><br>An asset has been transferred to you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0017-{OFFICE}-17</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> reassignement</li><li><strong>From Accountable Officer:</strong> Juan Dela Cruz</li><li><strong>Office:</strong> GAD</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'no_email', NULL, 1, 9, '2025-10-12 06:49:22'),
(19, 'ITR_TRANSFER', NULL, 'John Smith', 'ITR Asset Transfer Notification (FROM)', 'Hello John Smith,<br><br>An asset has been transferred from you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0018-HRMO-18</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> Reassignment</li><li><strong>To Accountable Officer:</strong> Walton Loneza</li><li><strong>Office:</strong> HRMO</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'no_email', NULL, 1, 10, '2025-10-12 06:52:23'),
(20, 'ITR_TRANSFER', 'waltonloneza@gmail.com', 'Walton Loneza', 'ITR Asset Transfer Notification (TO)', 'Hello Walton Loneza,<br><br>An asset has been transferred to you via ITR.<br><ul><li><strong>ITR No.:</strong> ITR-0018-HRMO-18</li><li><strong>Date:</strong> 2025-10-11</li><li><strong>Reason:</strong> Reassignment</li><li><strong>From Accountable Officer:</strong> John Smith</li><li><strong>Office:</strong> HRMO</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-125-125</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> ICS: OMM-05-030-ITS-03</li><li><strong>Serial No.:</strong> 25-SN-000118</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 1, 10, '2025-10-12 06:52:27'),
(21, 'MR_CREATED', 'waltonloneza@gmail.com', 'Walton Loneza', 'New Material Receipt (MR) Assignment Notification', 'Hello Walton Loneza,<br><br>You have been set as the Person Accountable for an item in the PILAR Asset Inventory system.<br><ul><li><strong>Office:</strong> Supply Office</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-162-162</li><li><strong>Description:</strong> Toyota Service Vehicle</li><li><strong>Property No.:</strong> Supply Office-2025-10-005</li><li><strong>Serial No.:</strong> 25-SN-000153</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 14, 23, '2025-10-12 13:40:28'),
(22, 'MR_CREATED', 'waltonloneza@gmail.com', 'Walton Loneza', 'New Material Receipt (MR) Assignment Notification', 'Hello Walton Loneza,<br><br>You have been set as the Person Accountable for an item in the PILAR Asset Inventory system.<br><ul><li><strong>Office:</strong> Supply Office</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-162-162</li><li><strong>Description:</strong> Toyota Service Vehicle</li><li><strong>Property No.:</strong> Supply Office-2025-10-005</li><li><strong>Serial No.:</strong> 25-SN-000153</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 14, 3, '2025-10-12 14:17:57'),
(23, 'MR_CREATED', 'waltonloneza@gmail.com', 'Walton Loneza', 'New Material Receipt (MR) Assignment Notification', 'Hello Walton Loneza,<br><br>You have been set as the Person Accountable for an item in the PILAR Asset Inventory system.<br><ul><li><strong>Office:</strong> KALAHI</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-169-169</li><li><strong>Description:</strong> Laptop AMD Ryzen</li><li><strong>Property No.:</strong> 2025-KALAHI-0045</li><li><strong>Serial No.:</strong> 25-SN-000159</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 9, 24, '2025-10-12 15:12:16'),
(24, 'MR_CREATED', 'waltonloneza@gmail.com', 'Walton Loneza', 'New Material Receipt (MR) Assignment Notification', 'Hello Walton Loneza,<br><br>You have been set as the Person Accountable for an item in the PILAR Asset Inventory system.<br><ul><li><strong>Office:</strong> DILG</li><li><strong>Inventory Tag:</strong> PS-5S-03-F02-01-172-172</li><li><strong>Description:</strong> Desktop Computer (Core i5)</li><li><strong>Property No.:</strong> DILG-2025-10-004</li><li><strong>Serial No.:</strong> 25-SN-000161</li></ul>If this was not expected, please contact your system administrator.', 'sent', NULL, 10, 25, '2025-10-12 15:12:54');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `employee_no` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `status` enum('permanent','casual','contractual','job_order','probationary','resigned','retired') NOT NULL,
  `clearance_status` enum('cleared','uncleared') DEFAULT 'uncleared',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `employee_no`, `name`, `email`, `status`, `clearance_status`, `date_added`, `image`, `office_id`) VALUES
(1, 'EMP0001', 'Juan A. Dela Cruz', NULL, 'permanent', 'uncleared', '2025-08-31 14:25:29', 'emp_68b45b59bbe19.jpg', 2),
(2, 'EMP0002', 'Maria Santos', NULL, 'permanent', 'uncleared', '2025-09-01 01:39:29', 'emp_68b4f95154506.jpg', 7),
(3, 'EMP0003', 'Pedro Reyes', NULL, 'contractual', 'uncleared', '2025-09-01 01:50:43', 'emp_68b4fbf33d3ad.jpg', 2),
(8, 'EMP0004', 'Ryan Bang', NULL, 'permanent', 'uncleared', '2025-09-20 12:03:27', NULL, 7),
(9, 'EMP0005', 'John Smith', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(10, 'EMP0006', 'Emily Johnson', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(11, 'EMP0007', 'Jessica Davis', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(12, 'EMP0008', 'Daniel Wilson', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 15),
(13, 'EMP0009', 'Sophia Martinez', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(14, 'EMP0010', 'David Anderson', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(15, 'EMP0011', 'Olivia Thomas', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(16, 'EMP0012', 'James Taylor', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 21),
(17, 'EMP0013', 'Emma Moore', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(18, 'EMP0014', 'William Jackson', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(19, 'EMP0015', 'Ava White', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(20, 'EMP0016', 'Alexander Harris', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(21, 'EMP0017', 'Isabella Martin', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(22, 'EMP0018', 'Benjamin Thompson', NULL, 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(23, 'EMP0019', 'Mia Garcia', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(24, 'EMP0020', 'Ethan Martinez', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 21),
(25, 'EMP0021', 'Amelia Lewis', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(26, 'EMP0022', 'Harper Walker', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(27, 'EMP0023', 'Lucas Hall', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(28, 'EMP0024', 'Evelyn Allen', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(29, 'EMP0025', 'Mason Young', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(30, 'EMP0026', 'Abigail King', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(31, 'EMP0027', 'James Scott', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(32, 'EMP0028', 'Ella Green', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 15),
(33, 'EMP0029', 'Henry Adams', NULL, 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(34, 'EMP0030', 'Sebastian Gonzalez', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(35, 'EMP0031', 'Victoria Nelson', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(36, 'EMP0032', 'Jackson Carter', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(37, 'EMP0033', 'Grace Mitchell', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(38, 'EMP0034', 'Owen Perez', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(39, 'EMP0035', 'Lily Roberts', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(40, 'EMP0036', 'Jacob Turner', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(41, 'EMP0037', 'Hannah Phillips', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(42, 'EMP0038', 'Samuel Campbell', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 21),
(43, 'EMP0039', 'Zoe Parker', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(44, 'EMP0040', 'Mateo Evans', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(45, 'EMP0041', 'Aria Edwards', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 38),
(46, 'EMP0042', 'Levi Collins', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 38),
(47, 'EMP0043', 'Nora Stewart', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(48, 'EMP0044', 'Wyatt Sanchez', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(49, 'EMP0045', 'Camila Morris', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(50, 'EMP0046', 'Carter Rogers', NULL, 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(51, 'EMP0047', 'Penelope Reed', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(52, 'EMP0048', 'Julian Cook', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(53, 'EMP0049', 'Riley Morgan', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 15),
(54, 'EMP0050', 'Nathan Bell', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(55, 'EMP0051', 'Lillian Murphy', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(56, 'EMP0052', 'Aurora Rivera', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(57, 'EMP0053', 'Isaac Cooper', NULL, 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(58, 'EMP0054', 'Violet Richardson', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(59, 'EMP0055', 'Stella Howard', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(60, 'EMP0056', 'Brooklyn Torres', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(61, 'EMP0057', 'Leo Peterson', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(62, 'EMP0058', 'Hannah Gray', NULL, 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(63, 'EMP0059', 'Anthony Ramirez', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(64, 'EMP0060', 'Addison James', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(65, 'EMP0061', 'Madison Brooks', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(66, 'EMP0062', 'Joshua Kelly', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 2),
(67, 'EMP0063', 'Eli Price', NULL, 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(68, 'EMP0064', 'Paisley Bennett', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 38),
(69, 'EMP0065', 'Gabriel Wood', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(70, 'EMP0066', 'Caleb Ross', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(71, 'EMP0067', 'Aurora Henderson', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 38),
(72, 'EMP0068', 'Ryan Coleman', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(73, 'EMP0069', 'Scarlett Jenkins', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(74, 'EMP0070', 'Luke Perry', NULL, 'retired', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(75, 'EMP0071', 'Nora Powell', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 15),
(76, 'EMP0072', 'Hannah Patterson', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(77, 'EMP0073', 'Cameron Hughes', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 33),
(78, 'EMP0074', 'Violet Flores', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(79, 'EMP0075', 'Connor Washington', NULL, 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 21),
(80, 'EMP0076', 'Grace Butler', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 15),
(81, 'EMP0077', 'Wyatt Simmons', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(82, 'EMP0078', 'Lillian Foster', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(83, 'EMP0079', 'Brayden Gonzales', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(84, 'EMP0080', 'Elena Bryant', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(85, 'EMP0081', 'Zoe Russell', NULL, 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(86, 'EMP0082', 'Aaron Griffin', NULL, 'resigned', 'uncleared', '2025-09-24 07:41:21', NULL, 47),
(87, 'EMP0083', 'Hazel Diaz', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 4),
(88, 'EMP0084', 'Charles Hayes', NULL, 'contractual', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(89, 'EMP0085', 'Aurora Myers', NULL, 'job_order', 'uncleared', '2025-09-24 07:41:21', NULL, 14),
(90, 'EMP0086', 'Thomas Ford', NULL, 'permanent', 'uncleared', '2025-09-24 07:41:21', NULL, 3),
(91, 'EMP0087', 'Walton Loneza', 'waltonloneza@gmail.com', 'permanent', 'uncleared', '2025-10-09 02:25:57', NULL, 33),
(92, 'EMP0088', 'Juan Dela Cruz', 'waltielappy@gmail.com', 'permanent', 'uncleared', '2025-10-09 02:40:19', NULL, 33),
(93, 'EMP0089', 'Maria Clara', '', 'contractual', 'uncleared', '2025-10-09 02:40:19', NULL, 33),
(94, 'EMP0090', 'Pedro Santos', '', 'resigned', 'uncleared', '2025-10-09 02:40:19', NULL, 33),
(95, 'EMP0091', 'tin marcellana', 'llenaresascristine61@gmail.com', 'permanent', 'uncleared', '2025-10-09 08:32:33', NULL, 33);

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
(6, '2025-02-19', '07:30:00', 'FEB25-363', '1135', 'MOTORPOOL', 30.00, 'Unleaded', 'SINOTRUCK', 'A.LOBRIGO', NULL, 17, '2025-09-28 02:33:50'),
(7, '2025-10-12', '11:36:00', 'OCT25-370', '37334', 'MOTORPOOL', 58.03, 'Diesel', 'TRUCK', 'JOHN SMITH', '11:38:00', 17, '2025-10-12 04:38:38');

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
(7, '2025-09-28 07:28:00', 'Diesel', 50.00, 52.00, 2600.00, 'Storage Room', '3233511774566', 'Jake Paul', 'Roy Ricacho', 'Restock', 17, '2025-09-28 02:29:40'),
(8, '2025-10-12 11:35:00', 'Diesel', 50.00, 54.00, 2700.00, 'Storage Room', '7958437485938422300', 'Elton John Moises', 'John Smith', 'Refill', 17, '2025-10-12 04:36:35');

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
(1, 42.97, '2025-10-12 04:38:38'),
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
(1, 17, 4, 'fuel_consumption_report_from_2024-01-01_to_2025-10-31_group_fo_request_20251012_051135.pdf', 0, '2025-10-12 10:11:35'),
(2, 17, 4, 'fuel_consumption_report_from_2024-01-01_to_2025-10-31_group_fo_request_20251012_051223.pdf', 0, '2025-10-12 10:12:23'),
(3, 17, 4, 'fuel_consumption_report_from_2024-01-01_to_2025-10-31_group_fo_request_20251012_051338.pdf', 0, '2025-10-12 10:13:38'),
(4, 17, 4, 'fuel_log_report_20251012_051430.pdf', 0, '2025-10-12 10:14:30'),
(5, 17, 4, 'fuel_out_report_20251012_051443.pdf', 0, '2025-10-12 10:14:44'),
(6, 17, 4, 'fuel_consumption_report_from_2024-01-01_to_2025-10-31_group_fo_request_20251012_051518.pdf', 0, '2025-10-12 10:15:18'),
(7, 17, 4, 'fuel_consumption_report_from_2024-01-01_to_2025-10-31_group_fo_request_20251012_063932.pdf', 0, '2025-10-12 11:39:33'),
(8, 17, 4, 'fuel_out_report_20251012_063946.pdf', 0, '2025-10-12 11:39:46'),
(9, 17, 4, 'fuel_log_report_20251012_063957.pdf', 0, '2025-10-12 11:39:57'),
(10, 30, 0, 'Inventory_Report_20251012_103543.pdf', 0, '2025-10-12 15:35:43'),
(11, 17, 4, 'consumables_export_20251013_040307.csv', 0, '2025-10-13 09:03:07'),
(12, 32, 4, 'assets_report_20251013_053655.pdf', 0, '2025-10-13 10:36:55'),
(13, 32, 4, 'consumables_report_20251013_133350.pdf', 0, '2025-10-13 18:33:51'),
(14, 32, 4, 'consumables_report_custom_2025-10-13_to_2025-10-13_20251013_133418.pdf', 0, '2025-10-13 18:34:18'),
(15, 32, 4, 'consumables_report_custom_2025-10-13_to_2025-10-13_20251013_133425.pdf', 0, '2025-10-13 18:34:26'),
(16, 32, 4, 'consumables_export_custom_2025-10-13_to_2025-10-13_20251013_133454.csv', 0, '2025-10-13 18:34:54'),
(17, 17, 4, 'consumables_report_20251013_133832.pdf', 0, '2025-10-13 18:38:32'),
(18, 17, 4, 'consumables_report_20251013_133858.pdf', 0, '2025-10-13 18:38:58'),
(19, 17, 4, 'consumables_report_20251013_134106.pdf', 0, '2025-10-13 18:41:06'),
(20, 17, 4, 'Inventory_Report_20251013_134445.pdf', 0, '2025-10-13 18:44:46'),
(21, 17, 4, 'assets_report_20251013_134524.pdf', 0, '2025-10-13 18:45:24'),
(22, 17, 4, 'Unserviceable_Inventory_Report_2025-10_20251013_135201.pdf', 0, '2025-10-13 18:52:02'),
(23, 17, 4, 'unserviceable_report_20251013_135209.pdf', 0, '2025-10-13 18:52:09'),
(24, 17, 4, 'Unserviceable_Inventory_Report_2025-10_20251013_135410.pdf', 0, '2025-10-13 18:54:11'),
(25, 17, 4, 'infrastructure_inventory_report_20251013_154443.pdf', 0, '2025-10-13 20:44:43'),
(26, 17, 4, 'infrastructure_inventory_export_20251013_154450.csv', 0, '2025-10-13 20:44:50'),
(27, 17, 4, 'infrastructure_inventory_report_20251013_154625.pdf', 0, '2025-10-13 20:46:26'),
(28, 17, 4, 'infrastructure_inventory_report_20251013_154821.pdf', 0, '2025-10-13 20:48:21'),
(29, 17, 4, 'infrastructure_inventory_export_20251013_155323.csv', 0, '2025-10-13 20:53:23'),
(30, 17, 4, 'infrastructure_inventory_export_20251013_155342.csv', 0, '2025-10-13 20:53:42');

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
-- Table structure for table `guests`
--

CREATE TABLE `guests` (
  `id` int(11) NOT NULL,
  `guest_id` varchar(64) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `barangay` varchar(255) DEFAULT NULL,
  `first_login` datetime DEFAULT current_timestamp(),
  `last_login` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `session_count` int(11) DEFAULT 1,
  `total_borrow_requests` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guests`
--

INSERT INTO `guests` (`id`, `guest_id`, `email`, `name`, `contact`, `barangay`, `first_login`, `last_login`, `session_count`, `total_borrow_requests`) VALUES
(1, 'c51ad83fb94dc8374cc2cab32930d37ba5e8aa64704234f9b6969e08320d86f5', 'waltonloneza@gmail.com', 'Walton loneza', '09107171456', 'PANOYPOY', '2025-10-14 13:52:02', '2025-10-14 13:58:16', 17, 0),
(2, 'test_guest_1760424673', '', NULL, NULL, NULL, '2025-10-14 13:51:13', '2025-10-14 13:51:13', 1, 0),
(4, 'test_guest_1760425469', 'test@example.com', 'Test User', '09123456789', 'Test Barangay', '2025-10-14 14:04:29', '2025-10-14 14:04:29', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `guest_borrowing_history`
--

CREATE TABLE `guest_borrowing_history` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `performed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guest_borrowing_items`
--

CREATE TABLE `guest_borrowing_items` (
  `id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `returned_quantity` int(11) DEFAULT 0,
  `condition_before` text DEFAULT NULL,
  `condition_after` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guest_borrowing_requests`
--

CREATE TABLE `guest_borrowing_requests` (
  `id` int(11) NOT NULL,
  `request_number` varchar(50) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_email` varchar(255) NOT NULL,
  `guest_contact` varchar(50) DEFAULT NULL,
  `guest_organization` varchar(255) DEFAULT NULL,
  `purpose` text NOT NULL,
  `request_date` datetime NOT NULL,
  `needed_by_date` date DEFAULT NULL,
  `expected_return_date` date NOT NULL,
  `actual_return_date` datetime DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled','in_progress','ready_for_pickup','in_transit','completed','returned','overdue','damaged','lost') NOT NULL DEFAULT 'pending',
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `guest_notifications`
--

CREATE TABLE `guest_notifications` (
  `id` int(11) NOT NULL,
  `guest_id` int(11) NOT NULL COMMENT 'Guest ID from guests table',
  `notification_type` enum('borrow_approved','borrow_rejected','borrow_return_reminder') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_entity_type` enum('borrow_form_submission','borrow_request') DEFAULT NULL,
  `related_entity_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notifications for guest users';

--
-- Dumping data for table `guest_notifications`
--

INSERT INTO `guest_notifications` (`id`, `guest_id`, `notification_type`, `title`, `message`, `related_entity_type`, `related_entity_id`, `is_read`, `read_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 0, 'borrow_approved', 'Borrow Request Approved', 'Your borrow request has been approved by Test Admin', 'borrow_form_submission', 999999, 1, '2025-10-14 14:02:52', '2025-10-28 09:02:52', '2025-10-14 07:02:52', '2025-10-14 07:02:52'),
(2, 0, 'borrow_rejected', 'Borrow Request Rejected', 'Your borrow request has been rejected by Test Admin', 'borrow_form_submission', 999999, 0, NULL, '2025-10-28 09:02:52', '2025-10-14 07:02:52', '2025-10-14 07:02:52'),
(3, 0, 'borrow_approved', 'Borrow Request Approved', 'Your borrow request has been approved by Test Admin', 'borrow_form_submission', 22, 0, NULL, '2025-10-28 09:07:26', '2025-10-14 07:07:26', '2025-10-14 07:07:26'),
(4, 0, 'borrow_rejected', 'Borrow Request Rejected', 'Your borrow request has been rejected by Test Admin', 'borrow_form_submission', 23, 0, NULL, '2025-10-28 09:07:29', '2025-10-14 07:07:29', '2025-10-14 07:07:29');

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
(1, '1760204624_ICS HEADER.png', '', '', '', '', '', '', '', '2025-10-11 17:43:44', NULL),
(2, '1760204624_ICS HEADER.png', 'LGU-PILAR/OMM', 'COMPUTERIZATION', '{OFFICE}-039', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'ROY L. RICACHO', 'CLERK', '2025-10-12 03:18:04', 33),
(3, '1760204624_ICS HEADER.png', 'GAD', 'COMPUTERIZATION', '{OFFICE}-040', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-10-12 05:51:58', 19),
(4, '1760204624_ICS HEADER.png', 'KALAHI', 'COMPUTERIZATION', 'KALAHI-041', 'IVAN CHRISTOPHER R. MILLABAS', 'DESIGNATE-SUPPLY OFFICER/OMM', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-10-12 06:33:12', 24);

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
(1, 2, 1, '{OFFICE}-039', 1, 'unit', 45000.00, 45000.00, 'Laptop AMD Ryzen', '1', '3 years', '2025-10-12 03:18:04'),
(2, 3, 7, '{OFFICE}-040', 2, 'unit', 450.00, 900.00, 'Mouse', '1', '5 years', '2025-10-12 05:51:58'),
(3, 4, 9, 'KALAHI-041', 1, 'unit', 45000.00, 45000.00, 'Laptop AMD Ryzen', '1', '3 years', '2025-10-12 06:33:12');

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
(1, '1760191598_IIRUP HEADER.png', '', '', '', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-10-11 14:06:38'),
(2, '1760191598_IIRUP HEADER.png', 'IVAN CHRISTOPER MILLABAS', 'SUPPLY OFFICE', 'MDRRMO', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-10-12 07:19:33'),
(3, '1760191598_IIRUP HEADER.png', 'IVAN CHRISTOPER MILLABAS', 'SUPPLY OFFICE', 'MENRU', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-10-12 10:29:36');

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
(1, 2, 1, '2025-10-12', 'Laptop AMD Ryzen', 'OMM-05-030-ITS-03', 1, 45000.00, 45000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'HRMO', 'FUR-0018-10', '', '2025-10-12', '2025-10-12 07:19:33'),
(2, 3, 6, '2025-10-12', 'PC', 'HRMO-2025-10-003', 1, 56000.00, 56000.00, 0.00, 0.00, 0.00, 'Unserviceable', '', '', '', '', 0.00, 0.00, '', 0.00, 'HRMO', 'FUR-0026-10', '', '2025-10-12', '2025-10-12 10:29:36');

-- --------------------------------------------------------

--
-- Table structure for table `iirup_temp_storage`
--

CREATE TABLE `iirup_temp_storage` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_id` varchar(255) NOT NULL,
  `form_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`form_data`)),
  `asset_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`asset_data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT (current_timestamp() + interval 7 day)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `additional_image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_image`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `infrastructure_inventory`
--

INSERT INTO `infrastructure_inventory` (`inventory_id`, `classification_type`, `item_description`, `nature_occupancy`, `location`, `date_constructed_acquired_manufactured`, `property_no_or_reference`, `acquisition_cost`, `market_appraisal_insurable_interest`, `date_of_appraisal`, `remarks`, `additional_image`) VALUES
(2, 'BUILDING', 'Pilar Gymnasium', 'Gymnasium', 'LGU-Complex', '2025-10-13', 'BLDNG22-32', 2345456.00, 2343453.00, '2025-10-13', 'test', '[\"uploads\\/1760360685_1_pilar gym.jpg\"]'),
(4, 'Building', 'School Building A', 'schools', 'Poblacion, Pilar', '2025-10-13', 'PROP-INFRA-001', 5000000.00, 5500000.00, '2025-10-13', 'Well-maintained school building', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `infrastructure_inventory_archive`
--

CREATE TABLE `infrastructure_inventory_archive` (
  `archive_id` int(11) NOT NULL,
  `inventory_id` int(11) NOT NULL,
  `classification_type` varchar(255) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `nature_occupancy` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `date_constructed_acquired_manufactured` date DEFAULT NULL,
  `property_no_or_reference` varchar(255) DEFAULT NULL,
  `acquisition_cost` decimal(15,2) DEFAULT NULL,
  `market_appraisal_insurable_interest` decimal(15,2) DEFAULT NULL,
  `date_of_appraisal` date DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `additional_image` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`additional_image`)),
  `archived_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `archived_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `infrastructure_inventory_archive`
--

INSERT INTO `infrastructure_inventory_archive` (`archive_id`, `inventory_id`, `classification_type`, `item_description`, `nature_occupancy`, `location`, `date_constructed_acquired_manufactured`, `property_no_or_reference`, `acquisition_cost`, `market_appraisal_insurable_interest`, `date_of_appraisal`, `remarks`, `additional_image`, `archived_at`, `archived_by`) VALUES
(1, 3, 'BUILDING', 'Multi Purpose bldg.', 'Offices', 'LGU-Complex', '2025-10-13', '0', 2342345325.00, 22343253252.00, '2025-10-13', 'test', '[\"uploads\\/1760361171_1_pilar gym inside.jpg\",\"uploads\\/1760361171_2_pilar gym.jpg\"]', '2025-10-13 13:22:30', 17);

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
(1, '1760191892_ITR_HEADER.png', '', '', '', '', '', '2025-10-11', 'donation', '', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '0000-00-00', 'ROY RICACHO', 'CLERK', '0000-00-00', '', '', '0000-00-00'),
(2, '1760191892_ITR_HEADER.png', 'GAD', 'COMPUTERIZATION', 'Walton Loneza', 'Juan Dela Cruz', 'ITR-0010-{OFFICE}-10', '2025-10-11', 'reassignment', 'reassignement', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '0000-00-00', 'ROY RICACHO', 'CLERK', '0000-00-00', 'Juan Dela Cruz', 'OFFICE', '0000-00-00'),
(3, '1760191892_ITR_HEADER.png', 'GAD', 'COMPUTERIZATION', 'Walton Loneza', 'Juan Dela Cruz', 'ITR-0011-{OFFICE}-11', '2025-10-11', 'reassignment', 'reassignement', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '0000-00-00', 'ROY RICACHO', 'CLERK', '0000-00-00', 'Juan Dela Cruz', 'OFFICE', '0000-00-00'),
(4, '1760191892_ITR_HEADER.png', 'GAD', 'COMPUTERIZATION', 'Walton Loneza', 'Juan Dela Cruz', 'ITR-0012-{OFFICE}-12', '2025-10-11', 'reassignment', 'reassignement', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '0000-00-00', 'ROY RICACHO', 'CLERK', '0000-00-00', 'Juan Dela Cruz', 'OFFICE', '0000-00-00'),
(5, '1760191892_ITR_HEADER.png', 'GAD', 'COMPUTERIZATION', 'Walton Loneza', 'Juan Dela Cruz', 'ITR-0013-{OFFICE}-13', '2025-10-11', 'reassignment', 'reassignement', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '0000-00-00', 'ROY RICACHO', 'CLERK', '0000-00-00', 'Juan Dela Cruz', 'OFFICE', '0000-00-00'),
(6, '1760191892_ITR_HEADER.png', 'GAD', 'COMPUTERIZATION', 'Walton Loneza', 'Juan Dela Cruz', 'ITR-0014-{OFFICE}-14', '2025-10-11', 'reassignment', 'reassignement', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '0000-00-00', 'ROY RICACHO', 'CLERK', '0000-00-00', 'Juan Dela Cruz', 'OFFICE', '0000-00-00'),
(7, '1760191892_ITR_HEADER.png', 'GAD', 'COMPUTERIZATION', 'Walton Loneza', 'Juan Dela Cruz', 'ITR-0015-{OFFICE}-15', '2025-10-11', 'reassignment', 'reassignement', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '0000-00-00', 'ROY RICACHO', 'CLERK', '0000-00-00', 'Juan Dela Cruz', 'OFFICE', '0000-00-00'),
(8, '1760191892_ITR_HEADER.png', 'GAD', 'COMPUTERIZATION', 'Walton Loneza', 'Juan Dela Cruz', 'ITR-0016-{OFFICE}-16', '2025-10-11', 'reassignment', 'reassignement', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '0000-00-00', 'ROY RICACHO', 'CLERK', '0000-00-00', 'Juan Dela Cruz', 'OFFICE', '0000-00-00'),
(9, '1760191892_ITR_HEADER.png', 'GAD', 'COMPUTERIZATION', 'Walton Loneza', 'John Smith', 'ITR-0017-{OFFICE}-17', '2025-10-11', 'reassignment', 'reassignement', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '0000-00-00', 'ROY RICACHO', 'CLERK', '0000-00-00', 'John Smith', 'OFFICE', '0000-00-00'),
(10, '1760191892_ITR_HEADER.png', 'HRMO', 'COMPUTERIZATION', 'John Smith', 'Walton Loneza', 'ITR-0018-HRMO-18', '2025-10-11', 'reassignment', 'Reassignment', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '0000-00-00', 'ROY RICACHO', 'CLERK', '0000-00-00', 'Walton Loneza', 'OFFICE', '0000-00-00');

-- --------------------------------------------------------

--
-- Table structure for table `itr_items`
--

CREATE TABLE `itr_items` (
  `item_id` int(11) NOT NULL,
  `itr_id` int(10) UNSIGNED NOT NULL,
  `item_no` int(11) DEFAULT 1,
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

INSERT INTO `itr_items` (`item_id`, `itr_id`, `item_no`, `date_acquired`, `property_no`, `asset_id`, `description`, `amount`, `condition_of_PPE`) VALUES
(1, 2, 1, '2025-10-12', 'ICS: OMM-05-030-ITS-03', 1, 'Laptop AMD Ryzen', 45000.00, 'Serviceable'),
(2, 3, 1, '2025-10-12', 'ICS: OMM-05-030-ITS-03', 1, 'Laptop AMD Ryzen', 45000.00, 'Serviceable'),
(3, 4, 1, '2025-10-12', 'ICS: OMM-05-030-ITS-03', 1, 'Laptop AMD Ryzen', 45000.00, 'Serviceable'),
(4, 5, 1, '2025-10-12', 'ICS: OMM-05-030-ITS-03', 1, 'Laptop AMD Ryzen', 45000.00, 'Serviceable'),
(5, 6, 1, '2025-10-12', 'ICS: OMM-05-030-ITS-03', 1, 'Laptop AMD Ryzen', 45000.00, 'Serviceable'),
(6, 7, 1, '2025-10-12', 'ICS: OMM-05-030-ITS-03', 1, 'Laptop AMD Ryzen', 45000.00, 'Serviceable'),
(7, 8, 1, '2025-10-12', 'ICS: OMM-05-030-ITS-03', 1, 'Laptop AMD Ryzen', 45000.00, 'Serviceable'),
(8, 9, 1, '2025-10-12', 'ICS: OMM-05-030-ITS-03', 1, 'Laptop AMD Ryzen', 45000.00, 'Serviceable'),
(9, 10, 1, '2025-10-12', 'ICS: OMM-05-030-ITS-03', 1, 'Laptop AMD Ryzen', 45000.00, 'Serviceable');

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
(1, 1, 'OMM', 'Laptop AMD Ryzen', '', '25-SN-000118', 0, 1, 1.00, 'unit', '2025-10-12', 45000.00, 'Walton Loneza', 'Angela Rizal', '2025-10-12', '2025-10-12', '2025-10-12 03:47:32', 1, 'PS-5S-03-F02-01-125-125'),
(2, NULL, 'HRMO', 'PC', '', '25-SN-000130', 0, 1, 1.00, 'unit', '2025-10-12', 56000.00, 'Hannah Phillips', 'Jack Robertson', '2025-10-12', '2025-10-12', '2025-10-12 05:47:20', 6, 'PS-5S-03-F02-01-137-137'),
(3, NULL, 'Supply Office', 'Toyota Service Vehicle', 'Vios 1.3 XE', '25-SN-000153', 1, 0, 1.00, 'unit', '2025-10-12', 750000.00, 'Walton Loneza', 'Elton John Moises', '0000-00-00', '0000-00-00', '2025-10-12 13:40:23', 14, 'PS-5S-03-F02-01-162-162'),
(4, 3, 'KALAHI', 'Laptop AMD Ryzen', '', '25-SN-000159', 1, 0, 1.00, 'unit', '2025-10-12', 45000.00, 'Walton Loneza', 'Elton John Moises', '0000-00-00', '0000-00-00', '2025-10-12 15:12:13', 9, 'PS-5S-03-F02-01-168-168'),
(5, NULL, 'DILG', 'Desktop Computer (Core i5)', '', '25-SN-000161', 1, 0, 1.00, 'unit', '2025-10-12', 56000.00, 'Walton Loneza', 'Jack Robertson', '0000-00-00', '0000-00-00', '2025-10-12 15:12:50', 10, 'PS-5S-03-F02-01-171-171');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `type_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `related_entity_type` varchar(50) DEFAULT NULL,
  `related_entity_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `type_id`, `title`, `message`, `related_entity_type`, `related_entity_id`, `created_by`, `created_at`, `expires_at`) VALUES
(1, 10, 'Asset Return Notification', 'Guest  has returned assets. Submission #', 'borrow_form_submissions', 19, NULL, '2025-10-14 06:37:57', '2025-10-21 01:37:57'),
(2, 10, 'Asset Return Notification', 'Guest Walton loneza has returned assets. Submission #', 'borrow_form_submissions', 21, NULL, '2025-10-14 06:58:48', '2025-10-21 01:58:48');

-- --------------------------------------------------------

--
-- Table structure for table `notification_types`
--

CREATE TABLE `notification_types` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification_types`
--

INSERT INTO `notification_types` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'low_stock', 'Low stock alert for inventory items', '2025-10-14 04:14:47'),
(2, 'borrow_request', 'New borrow request received', '2025-10-14 04:14:47'),
(3, 'borrow_approved', 'Borrow request approved', '2025-10-14 04:14:47'),
(4, 'borrow_rejected', 'Borrow request rejected', '2025-10-14 04:14:47'),
(5, 'due_date_reminder', 'Due date reminder for borrowed items', '2025-10-14 04:14:47'),
(6, 'overdue_notice', 'Overdue notice for borrowed items', '2025-10-14 04:14:47'),
(7, 'maintenance_reminder', 'Maintenance reminder for assets', '2025-10-14 04:14:47'),
(8, 'system_alert', 'System alert or notification', '2025-10-14 04:14:47'),
(9, 'new_asset_assigned', 'New asset assigned to you', '2025-10-14 04:14:47'),
(10, 'asset_returned', 'Asset has been returned', '2025-10-14 04:14:47');

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
(1, 3, 33, NULL, NULL, '', '', '1760192036_PAR HEADER.png', '', '', 'PAR-0001', '2025-10-11 14:13:56', NULL, NULL),
(2, 0, 2, 'ROY L. RICACHO', 'MARK JAYSON NAMIA', 'CLERK', 'PROPERTY CUSTODIAN', '1760192036_PAR HEADER.png', 'IT Office', 'COMPUTERIZATION', 'IO-002', '2025-10-12 03:52:04', '0000-00-00', '0000-00-00'),
(3, 0, 23, 'ROY L. RICACHO', 'MARK JAYSON NAMIA', 'CLERK', 'PROPERTY CUSTODIAN', '1760192036_PAR HEADER.png', 'HRMO', 'COMPUTERIZATION', 'H-003', '2025-10-12 05:00:13', '0000-00-00', '0000-00-00'),
(4, 0, 23, 'ROY L. RICACHO', 'MARK JAYSON NAMIA', 'CLERK', 'PROPERTY CUSTODIAN', '1760192036_PAR HEADER.png', 'HRMO', 'COMPUTERIZATION', 'H-004', '2025-10-12 05:01:10', '0000-00-00', '0000-00-00'),
(5, 0, 23, 'ROY L. RICACHO', 'MARK JAYSON NAMIA', 'OFFICER', 'PROPERTY CUSTODIAN', '1760192036_PAR HEADER.png', 'HRMO', 'COMPUTERIZATION', 'H-005', '2025-10-12 05:22:14', '0000-00-00', '0000-00-00'),
(6, 0, 17, 'ROY L. RICACHO', 'MARK JAYSON NAMIA', 'OFFICER', 'PROPERTY CUSTODIAN', '1760192036_PAR HEADER.png', 'DILG', 'COMPUTERIZATION', 'DILG-006', '2025-10-12 06:53:50', '0000-00-00', '0000-00-00'),
(7, 0, 4, 'ROY L. RICACHO', 'MARK JAYSON NAMIA', 'CLERK', 'PROPERTY CUSTODIAN', '1760192036_PAR HEADER.png', 'Supply Office', 'TRANSPORTATION', 'Supply Office-007', '2025-10-12 13:35:47', '0000-00-00', '0000-00-00');

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
(1, 2, 2, 2, 'unit', 'Notebook i7', 'ITS-05-030-04', '2025-10-12', 70000.00, 140000.00),
(2, 4, 5, 1, 'unit', 'Notebook i7', '{OFFICE}-2025-10-002', '2025-10-12', 70000.00, 70000.00),
(3, 5, 6, 1, 'unit', 'PC', '{OFFICE}-2025-10-003', '2025-10-12', 56000.00, 56000.00),
(4, 6, 10, 1, 'unit', 'Desktop Computer (Core i5)', '{OFFICE}-2025-10-004', '2025-10-12', 56000.00, 56000.00),
(5, 7, 14, 1, 'unit', 'Toyota Service Vehicle', '{OFFICE}-2025-10-005', '2025-10-12', 750000.00, 750000.00);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `description`, `category`, `created_at`) VALUES
(1, 'view_dashboard', 'View the dashboard', NULL, '2025-10-10 03:29:03'),
(2, 'view_users', 'View users', NULL, '2025-10-10 03:29:03'),
(3, 'view_roles', 'View roles', NULL, '2025-10-10 03:29:03'),
(4, 'view_permissions', 'View permissions', NULL, '2025-10-10 03:29:03'),
(5, 'view_assets', 'View assets', NULL, '2025-10-10 03:29:03'),
(6, 'view_categories', 'View categories', NULL, '2025-10-10 03:29:03'),
(7, 'view_locations', 'View locations', NULL, '2025-10-10 03:29:03'),
(8, 'view_suppliers', 'View suppliers', NULL, '2025-10-10 03:29:03'),
(9, 'view_status', 'View status', NULL, '2025-10-10 03:29:03'),
(10, 'view_types', 'View types', NULL, '2025-10-10 03:29:03'),
(11, 'view_users_create', 'Create users', NULL, '2025-10-10 03:29:03'),
(12, 'view_users_edit', 'Edit users', NULL, '2025-10-10 03:29:03'),
(13, 'view_users_delete', 'Delete users', NULL, '2025-10-10 03:29:03'),
(14, 'view_roles_create', 'Create roles', NULL, '2025-10-10 03:29:03'),
(15, 'view_roles_edit', 'Edit roles', NULL, '2025-10-10 03:29:03'),
(16, 'view_roles_delete', 'Delete roles', NULL, '2025-10-10 03:29:03'),
(17, 'view_permissions_create', 'Create permissions', NULL, '2025-10-10 03:29:03'),
(18, 'view_permissions_edit', 'Edit permissions', NULL, '2025-10-10 03:29:03'),
(19, 'view_permissions_delete', 'Delete permissions', NULL, '2025-10-10 03:29:03'),
(20, 'view_assets_create', 'Create assets', NULL, '2025-10-10 03:29:03'),
(21, 'view_assets_edit', 'Edit assets', NULL, '2025-10-10 03:29:03'),
(22, 'view_assets_delete', 'Delete assets', NULL, '2025-10-10 03:29:03'),
(23, 'view_categories_create', 'Create categories', NULL, '2025-10-10 03:29:03'),
(24, 'view_categories_edit', 'Edit categories', NULL, '2025-10-10 03:29:03'),
(25, 'view_categories_delete', 'Delete categories', NULL, '2025-10-10 03:29:03'),
(26, 'view_locations_create', 'Create locations', NULL, '2025-10-10 03:29:03'),
(27, 'view_locations_edit', 'Edit locations', NULL, '2025-10-10 03:29:03'),
(28, 'view_locations_delete', 'Delete locations', NULL, '2025-10-10 03:29:03'),
(29, 'view_suppliers_create', 'Create suppliers', NULL, '2025-10-10 03:29:03'),
(30, 'view_suppliers_edit', 'Edit suppliers', NULL, '2025-10-10 03:29:03'),
(31, 'view_suppliers_delete', 'Delete suppliers', NULL, '2025-10-10 03:29:03'),
(32, 'view_status_create', 'Create status', NULL, '2025-10-10 03:29:03'),
(33, 'view_status_edit', 'Edit status', NULL, '2025-10-10 03:29:03'),
(34, 'view_status_delete', 'Delete status', NULL, '2025-10-10 03:29:03'),
(35, 'view_types_create', 'Create types', NULL, '2025-10-10 03:29:03'),
(36, 'view_types_edit', 'Edit types', NULL, '2025-10-10 03:29:03'),
(37, 'view_types_delete', 'Delete types', NULL, '2025-10-10 03:29:03'),
(38, 'manage_users', 'Can manage users', 'User Management', '2025-10-14 00:17:47'),
(39, 'manage_assets', 'Can manage assets', 'Asset Management', '2025-10-14 00:17:47'),
(40, 'manage_categories', 'Can manage categories', 'Asset Management', '2025-10-14 00:17:47'),
(41, 'manage_offices', 'Can manage offices', 'System', '2025-10-14 00:17:47'),
(42, 'manage_backups', 'Can manage backups', 'System', '2025-10-14 00:17:47'),
(43, 'system_settings', 'Can change system settings', 'System', '2025-10-14 00:17:47'),
(44, 'view_reports', 'Can view system reports', NULL, '2025-10-14 01:07:08'),
(45, 'generate_reports', 'Can generate and export reports', NULL, '2025-10-14 01:07:08'),
(46, 'manage_roles', 'Can manage user roles and permissions', NULL, '2025-10-14 01:07:08'),
(47, 'borrow_assets', 'Can borrow assets', NULL, '2025-10-14 01:07:08'),
(48, 'approve_borrow_requests', 'Can approve or reject borrow requests', NULL, '2025-10-14 01:07:08');

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
  `control_no` varchar(50) DEFAULT NULL,
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

INSERT INTO `red_tags` (`id`, `red_tag_number`, `control_no`, `asset_id`, `iirup_id`, `date_received`, `tagged_by`, `item_location`, `description`, `removal_reason`, `action`, `status`, `created_at`, `updated_at`) VALUES
(1, 'RT-0029', 'CTRL-2025-0026', 1, 2, '2025-10-12', 17, 'Supply Office', 'Laptop AMD Ryzen', 'Broken', 'For Disposal', 'Pending', '2025-10-12 07:20:08', '2025-10-12 07:20:08');

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
(1, 6, 33, '1760204781_RIS HEADER.png', '', '', '', '', '', '2025-10-11', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '2025-10-11', 'ROY L. RICACHO', 'CLERK', '2025-10-11', '2025-10-11', '', '2025-10-11 17:46:21', '', '', '2025-10-11', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '2025-10-11'),
(2, 6, 18, '1760204781_RIS HEADER.png', '', '', '', '2025-008-{OFFICE}', 'SAI-2025-0009', '2025-10-12', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'ROY L. RICACHO', 'CLERK', '0000-00-00', '2025-10-11', 'FOR PRINTING', '2025-10-12 07:10:25', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '0000-00-00'),
(3, 6, 29, '1760204781_RIS HEADER.png', '', '', '', '2025-009-MCR', 'SAI-2025-0010', '2025-10-12', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'ROY L. RICACHO', 'CLERK', '0000-00-00', '2025-10-11', 'for writing', '2025-10-12 07:14:00', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '0000-00-00'),
(4, 6, 4, '1760204781_RIS HEADER.png', '', '', '', '2025-010-Supply Office', 'SAI-2025-0011', '2025-10-12', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'ROY L. RICACHO', 'CLERK', '0000-00-00', '2025-10-11', 'for printing', '2025-10-12 11:34:14', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '0000-00-00'),
(5, 6, 4, '1760204781_RIS HEADER.png', '', '', '', '2025-011-Supply Office', 'SAI-2025-0012', '2025-10-13', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'ROY L. RICACHO', 'CLERK', '0000-00-00', '2025-10-11', 'for printing', '2025-10-13 04:18:10', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '0000-00-00'),
(6, 6, 4, '1760204781_RIS HEADER.png', '', '', '', '2025-012-Supply Office', 'SAI-2025-0013', '2025-10-13', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'ROY L. RICACHO', 'CLERK', '0000-00-00', '2025-10-11', 'for printing', '2025-10-13 04:21:29', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '0000-00-00'),
(7, 6, 4, '1760204781_RIS HEADER.png', '', '', '', '2025-013-Supply Office', 'SAI-2025-0014', '2025-10-13', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'ROY L. RICACHO', 'CLERK', '0000-00-00', '2025-10-11', 'for printing', '2025-10-13 11:36:24', 'CAROLYN C. SY-REYES', 'MUNICIPAL MAYOR', '0000-00-00', 'IVAN CHRISTOPHER R. MILLABAS', 'SUPPLY OFFICER', '0000-00-00');

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
(1, 2, '1', '22', 'Bond paper', 2, 340.00, 680.00),
(2, 3, '1', '2', 'Ballpen ', 1, 350.00, 350.00),
(3, 4, '1', '22', 'bond paper', 3, 400.00, 1200.00),
(4, 5, '1', '22', 'bond paper', 12, 400.00, 4800.00),
(5, 6, '1', '22', 'bond paper', 24, 400.00, 9600.00),
(6, 7, '1', '22', 'bond paper', 20, 400.00, 8000.00);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `color` varchar(7) DEFAULT '#99AAB5',
  `is_hoisted` tinyint(1) DEFAULT 0,
  `position` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `color`, `is_hoisted`, `position`, `created_at`, `updated_at`) VALUES
(1, 'SYSTEM_ADMIN', 'Has full access to all system features and configurations', '#FF0000', 1, 100, '2025-10-03 05:05:48', '2025-10-07 01:54:41'),
(2, 'MAIN_ADMIN', 'Can manage assets, users, and basic system settings', '#3498DB', 1, 80, '2025-10-03 05:05:48', '2025-10-07 01:54:41'),
(3, 'MAIN_EMPLOYEE', 'Can view and borrow assets', '#2ECC71', 0, 60, '2025-10-03 05:05:48', '2025-10-07 01:54:41'),
(4, 'MAIN_USER', 'Basic user with limited access', '#99AAB5', 0, 40, '2025-10-03 05:05:48', '2025-10-07 01:54:41'),
(5, 'OFFICE_ADMIN', 'Manages office-specific assets, users, and requests within their assigned office.', '#99AAB5', 0, 0, '2025-10-13 14:51:56', '2025-10-13 14:51:56'),
(6, 'USER', 'Regular user with basic access', '#99AAB5', 0, 30, '2025-10-13 15:00:04', '2025-10-13 15:00:04');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `role` enum('MAIN_ADMIN','SYSTEM_ADMIN','OFFICE_ADMIN','MAIN_USER','MAIN_EMPLOYEE','USER') NOT NULL COMMENT 'Role name',
  `permission_id` int(11) NOT NULL COMMENT 'Permission ID',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Updated to use role_id instead of role enum for better referential integrity. Migration applied on 2023-10-07.';

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `role`, `permission_id`, `created_at`) VALUES
(1, 1, 'SYSTEM_ADMIN', 5, '2025-10-10 03:29:24'),
(2, 1, 'SYSTEM_ADMIN', 20, '2025-10-10 03:29:24'),
(3, 1, 'SYSTEM_ADMIN', 22, '2025-10-10 03:29:24'),
(4, 1, 'SYSTEM_ADMIN', 21, '2025-10-10 03:29:24'),
(5, 1, 'SYSTEM_ADMIN', 6, '2025-10-10 03:29:24'),
(6, 1, 'SYSTEM_ADMIN', 23, '2025-10-10 03:29:24'),
(7, 1, 'SYSTEM_ADMIN', 25, '2025-10-10 03:29:24'),
(8, 1, 'SYSTEM_ADMIN', 24, '2025-10-10 03:29:24'),
(9, 1, 'SYSTEM_ADMIN', 1, '2025-10-10 03:29:24'),
(10, 1, 'SYSTEM_ADMIN', 7, '2025-10-10 03:29:24'),
(11, 1, 'SYSTEM_ADMIN', 26, '2025-10-10 03:29:24'),
(12, 1, 'SYSTEM_ADMIN', 28, '2025-10-10 03:29:24'),
(13, 1, 'SYSTEM_ADMIN', 27, '2025-10-10 03:29:24'),
(14, 1, 'SYSTEM_ADMIN', 4, '2025-10-10 03:29:24'),
(15, 1, 'SYSTEM_ADMIN', 17, '2025-10-10 03:29:24'),
(16, 1, 'SYSTEM_ADMIN', 19, '2025-10-10 03:29:24'),
(17, 1, 'SYSTEM_ADMIN', 18, '2025-10-10 03:29:24'),
(18, 1, 'SYSTEM_ADMIN', 3, '2025-10-10 03:29:24'),
(19, 1, 'SYSTEM_ADMIN', 14, '2025-10-10 03:29:24'),
(20, 1, 'SYSTEM_ADMIN', 16, '2025-10-10 03:29:24'),
(21, 1, 'SYSTEM_ADMIN', 15, '2025-10-10 03:29:24'),
(22, 1, 'SYSTEM_ADMIN', 9, '2025-10-10 03:29:24'),
(23, 1, 'SYSTEM_ADMIN', 32, '2025-10-10 03:29:24'),
(24, 1, 'SYSTEM_ADMIN', 34, '2025-10-10 03:29:24'),
(25, 1, 'SYSTEM_ADMIN', 33, '2025-10-10 03:29:24'),
(26, 1, 'SYSTEM_ADMIN', 8, '2025-10-10 03:29:24'),
(27, 1, 'SYSTEM_ADMIN', 29, '2025-10-10 03:29:24'),
(28, 1, 'SYSTEM_ADMIN', 31, '2025-10-10 03:29:24'),
(29, 1, 'SYSTEM_ADMIN', 30, '2025-10-10 03:29:24'),
(30, 1, 'SYSTEM_ADMIN', 10, '2025-10-10 03:29:24'),
(31, 1, 'SYSTEM_ADMIN', 35, '2025-10-10 03:29:24'),
(32, 1, 'SYSTEM_ADMIN', 37, '2025-10-10 03:29:24'),
(33, 1, 'SYSTEM_ADMIN', 36, '2025-10-10 03:29:24'),
(34, 1, 'SYSTEM_ADMIN', 2, '2025-10-10 03:29:24'),
(35, 1, 'SYSTEM_ADMIN', 11, '2025-10-10 03:29:24'),
(36, 1, 'SYSTEM_ADMIN', 13, '2025-10-10 03:29:24'),
(37, 1, 'SYSTEM_ADMIN', 12, '2025-10-10 03:29:24'),
(64, 2, 'MAIN_ADMIN', 5, '2025-10-10 03:29:24'),
(65, 2, 'MAIN_ADMIN', 6, '2025-10-10 03:29:24'),
(66, 2, 'MAIN_ADMIN', 1, '2025-10-10 03:29:24'),
(67, 2, 'MAIN_ADMIN', 7, '2025-10-10 03:29:24'),
(68, 2, 'MAIN_ADMIN', 10, '2025-10-10 03:29:24'),
(69, 2, 'MAIN_ADMIN', 2, '2025-10-10 03:29:24'),
(78, 4, 'MAIN_USER', 5, '2025-10-10 03:29:24'),
(79, 4, 'MAIN_USER', 6, '2025-10-10 03:29:24'),
(80, 4, 'MAIN_USER', 1, '2025-10-10 03:29:24'),
(81, 4, 'MAIN_USER', 7, '2025-10-10 03:29:24'),
(82, 4, 'MAIN_USER', 10, '2025-10-10 03:29:24'),
(83, 3, 'MAIN_ADMIN', 5, '2025-10-13 03:21:44'),
(84, 3, 'MAIN_ADMIN', 20, '2025-10-13 03:21:44'),
(85, 3, 'MAIN_ADMIN', 22, '2025-10-13 03:21:44'),
(86, 3, 'MAIN_ADMIN', 21, '2025-10-13 03:21:44'),
(87, 3, 'MAIN_ADMIN', 6, '2025-10-13 03:21:44'),
(88, 3, 'MAIN_ADMIN', 1, '2025-10-13 03:21:44'),
(89, 3, 'MAIN_ADMIN', 7, '2025-10-13 03:21:44'),
(90, 3, 'MAIN_ADMIN', 10, '2025-10-13 03:21:44'),
(94, 5, 'OFFICE_ADMIN', 5, '2025-10-13 23:56:06'),
(95, 5, 'OFFICE_ADMIN', 1, '2025-10-13 23:56:06'),
(96, 5, 'OFFICE_ADMIN', 2, '2025-10-13 23:56:06'),
(97, 1, 'MAIN_ADMIN', 48, '2025-10-14 01:07:08'),
(98, 1, 'MAIN_ADMIN', 47, '2025-10-14 01:07:08'),
(99, 1, 'MAIN_ADMIN', 45, '2025-10-14 01:07:08'),
(100, 1, 'MAIN_ADMIN', 39, '2025-10-14 01:07:08'),
(101, 1, 'MAIN_ADMIN', 42, '2025-10-14 01:07:08'),
(102, 1, 'MAIN_ADMIN', 40, '2025-10-14 01:07:08'),
(103, 1, 'MAIN_ADMIN', 41, '2025-10-14 01:07:08'),
(104, 1, 'MAIN_ADMIN', 46, '2025-10-14 01:07:08'),
(105, 1, 'MAIN_ADMIN', 38, '2025-10-14 01:07:08'),
(106, 1, 'MAIN_ADMIN', 43, '2025-10-14 01:07:08'),
(107, 1, 'MAIN_ADMIN', 44, '2025-10-14 01:07:08'),
(112, 2, 'MAIN_ADMIN', 48, '2025-10-14 01:07:08'),
(113, 2, 'MAIN_ADMIN', 47, '2025-10-14 01:07:08'),
(114, 2, 'MAIN_ADMIN', 45, '2025-10-14 01:07:08'),
(115, 2, 'MAIN_ADMIN', 39, '2025-10-14 01:07:08'),
(116, 2, 'MAIN_ADMIN', 40, '2025-10-14 01:07:08'),
(117, 2, 'MAIN_ADMIN', 38, '2025-10-14 01:07:08'),
(118, 2, 'MAIN_ADMIN', 44, '2025-10-14 01:07:08'),
(119, 3, 'MAIN_ADMIN', 47, '2025-10-14 01:07:08'),
(120, 5, 'MAIN_ADMIN', 48, '2025-10-14 01:07:08'),
(121, 5, 'MAIN_ADMIN', 47, '2025-10-14 01:07:08'),
(122, 5, 'MAIN_ADMIN', 39, '2025-10-14 01:07:08'),
(123, 5, 'MAIN_ADMIN', 44, '2025-10-14 01:07:08'),
(127, 6, 'MAIN_ADMIN', 1, '2025-10-14 01:07:08');

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
(1, '1760396065_new_logo.png', 'Pilar Inventory Management System', 'PilarINVENTORY@1');

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
(1, 'Pilar Asset Inventory Management System', 'This system manages and tracks assets across different offices. It supports inventory categorization, QR code tracking, report generation, and user role-based access.', 'Walton John Loneza \r\nJoshua Mari Francis Escano \r\nElton John B. Moises', 'waltonloneza@example.com', '1.0', 'Developed by BU Polangui Capstone Team for the Municipality of Pilar, Sorsogon.', '2025-08-03 10:30:00');

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
-- Table structure for table `tag_counters`
--

CREATE TABLE `tag_counters` (
  `id` int(11) NOT NULL,
  `tag_type` enum('red_tag','ics_no','itr_no','par_no','ris_no','inventory_tag','asset_code','serial_no','sai_no','control_no','property_no') DEFAULT NULL,
  `year_period` varchar(10) NOT NULL COMMENT 'Year or period (e.g., 2025)',
  `prefix_hash` varchar(32) NOT NULL COMMENT 'MD5 hash of current prefix for reset detection',
  `current_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tag_counters`
--

INSERT INTO `tag_counters` (`id`, `tag_type`, `year_period`, `prefix_hash`, `current_count`, `created_at`, `updated_at`) VALUES
(1, 'red_tag', '2025', 'bdff4249657cb5c4a8513161435a8a8e', 0, '2025-10-03 08:46:29', '2025-10-03 08:46:29'),
(2, 'ics_no', '2025', 'e5d9346982a55968ea3b22a01dac13b5', 0, '2025-10-03 08:46:29', '2025-10-03 08:46:29'),
(3, 'itr_no', '2025', 'f5f9c7790bcc99b43b568aa63986e2c0', 0, '2025-10-03 08:46:29', '2025-10-03 08:46:29'),
(5, 'ris_no', '2025', 'b601fa317a2a3d4f7860d95d06fe6c6b', 0, '2025-10-03 08:46:29', '2025-10-03 08:46:29'),
(7, 'red_tag', 'global', '73debfcaea0af900d0b2d69faba25d93', 29, '2025-10-03 08:56:22', '2025-10-12 07:20:08'),
(8, 'ics_no', 'global', '059d8400ee29e46df1145dbdff55eafa', 41, '2025-10-03 08:56:22', '2025-10-12 06:33:12'),
(9, 'itr_no', 'global', '4cc6d846d5327e1355419fc2767b8bbf', 18, '2025-10-03 08:56:22', '2025-10-12 06:52:23'),
(11, 'ris_no', 'global', 'b222f0296897fbec7d2f734d844e887b', 13, '2025-10-03 08:56:22', '2025-10-13 11:36:24'),
(16, '', 'global', '08054846bbc9933fd0395f8be516a9f9', 0, '2025-10-03 12:18:28', '2025-10-03 12:18:28'),
(17, 'serial_no', 'global', '92666505ce75444ee14be2ebc2f10a60', 163, '2025-10-03 12:32:17', '2025-10-13 14:24:16'),
(19, 'inventory_tag', 'global', 'b64d0fc24a6aed24f5297319a28b91bd', 173, '2025-10-03 12:48:04', '2025-10-13 14:24:16'),
(20, '', 'global', 'efc1ef8c2b016e45c48cf5aaf93bb11f', 0, '2025-10-04 01:36:33', '2025-10-04 01:36:33'),
(21, 'sai_no', 'global', 'efc1ef8c2b016e45c48cf5aaf93bb11f', 14, '2025-10-04 01:38:12', '2025-10-13 11:36:24'),
(22, 'control_no', 'global', '8114336b915d05a8b429543dfe9ef9fb', 26, '2025-10-04 02:12:20', '2025-10-12 07:20:08'),
(28, '', '', '', 26, '2025-10-07 05:38:13', '2025-10-10 06:26:23'),
(29, 'inventory_tag', '', '', 26, '2025-10-07 05:38:13', '2025-10-10 06:26:23'),
(95, 'asset_code', 'global', 'd41d8cd98f00b204e9800998ecf8427e', 0, '2025-10-10 00:29:56', '2025-10-10 00:29:56'),
(96, 'par_no', 'global', 'd41d8cd98f00b204e9800998ecf8427e', 7, '2025-10-10 00:31:27', '2025-10-12 13:35:47'),
(97, 'asset_code', 'global', '8dfebf110ea9d91ef8bb29a0dda4c7a1', 59, '2025-10-10 04:56:21', '2025-10-13 14:24:16'),
(98, 'asset_code', 'global', '523669b89db5b71820e7d690fbd15a34', 6, '2025-10-10 05:42:54', '2025-10-12 04:08:58'),
(101, 'asset_code', '', '', 4, '2025-10-10 06:26:22', '2025-10-10 06:26:23'),
(111, '', 'global', 'a4a24afe6fff2aef5fbeaa1b5a7eac9d', 0, '2025-10-12 04:14:55', '2025-10-12 04:14:55'),
(112, 'property_no', 'global', 'a4a24afe6fff2aef5fbeaa1b5a7eac9d', 5, '2025-10-12 04:19:39', '2025-10-12 13:35:47');

-- --------------------------------------------------------

--
-- Table structure for table `tag_formats`
--

CREATE TABLE `tag_formats` (
  `id` int(11) NOT NULL,
  `tag_type` enum('red_tag','ics_no','itr_no','par_no','ris_no','inventory_tag','asset_code','serial_no','sai_no','control_no','property_no') DEFAULT NULL,
  `format_template` varchar(255) NOT NULL COMMENT 'Template like PAR-{YYYY}-{####}',
  `current_number` int(11) DEFAULT 1 COMMENT 'Current increment number',
  `prefix` varchar(100) DEFAULT '' COMMENT 'Static prefix part',
  `suffix` varchar(100) DEFAULT '' COMMENT 'Static suffix part',
  `increment_digits` int(11) DEFAULT 4 COMMENT 'Number of digits for increment (e.g., 4 = 0001)',
  `date_format` varchar(50) DEFAULT 'YYYY' COMMENT 'Date format in template (YYYY, MM, DD)',
  `reset_on_change` tinyint(1) DEFAULT 1 COMMENT 'Reset counter when prefix/format changes',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tag_formats`
--

INSERT INTO `tag_formats` (`id`, `tag_type`, `format_template`, `current_number`, `prefix`, `suffix`, `increment_digits`, `date_format`, `reset_on_change`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'red_tag', 'RT-{####}', 1, 'RT-', '', 4, '', 1, 1, '2025-10-03 08:23:18', '2025-10-03 08:53:59'),
(2, 'ics_no', '{OFFICE}-{###}', 1, 'ICS-', '', 5, 'YY', 1, 1, '2025-10-03 08:23:18', '2025-10-10 01:23:28'),
(3, 'itr_no', 'ITR-{####}-{OFFICE}-{#}', 1, 'ITR-', '', 4, '', 1, 1, '2025-10-03 08:23:18', '2025-10-10 01:04:45'),
(4, 'par_no', '{OFFICE}-{###}', 1, NULL, '', 0, NULL, 1, 1, '2025-10-03 08:23:18', '2025-10-10 00:31:27'),
(5, 'ris_no', '{YYYY}-{###}-{OFFICE}', 1, 'RIS-', '', 4, 'YY', 1, 1, '2025-10-03 08:23:18', '2025-10-10 01:24:31'),
(6, 'inventory_tag', 'PS-5S-03-F02-01-{##}-{##}', 1, 'PS-5S-03-F02-01', '', 3, '', 1, 1, '2025-10-03 08:23:18', '2025-10-03 12:58:27'),
(9, 'asset_code', '{CODE}-{####}-{MM}', 1, NULL, '', 0, NULL, 1, 1, '2025-10-03 12:04:46', '2025-10-10 00:29:56'),
(11, 'serial_no', '{YY}-SN-{######}', 1, 'SN', '', 6, 'YY', 1, 1, '2025-10-03 12:24:32', '2025-10-03 12:32:57'),
(12, 'sai_no', 'SAI-{YYYY}-{####}', 1, 'SAI-', '', 4, 'YYYY', 1, 1, '2025-10-04 01:36:33', '2025-10-10 00:55:25'),
(15, 'control_no', 'CTRL-{YYYY}-{####}', 1, 'CTRL-', '', 4, 'YYYY', 1, 1, '2025-10-04 02:12:20', '2025-10-04 02:12:20'),
(16, 'property_no', '{OFFICE}-{YYYY}-{MM}-{###}', 1, 'PROP', '', 4, '', 1, 1, '2025-10-12 04:14:55', '2025-10-12 04:20:22');

-- --------------------------------------------------------

--
-- Table structure for table `temp_iirup_items`
--

CREATE TABLE `temp_iirup_items` (
  `id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `date_acquired` date DEFAULT NULL,
  `particulars` text DEFAULT NULL,
  `property_no` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `unit` varchar(50) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `office` varchar(255) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(12, 'walts', 'Walton Loneza', 'wjll@bicol-u.edu.ph', '$2y$10$tsOlFU9fjwi/DLRKdGkqL.aIXhKnlFxnNbA8ZoXeMbEiAhoe.sg/i', 'user', 'inactive', '2025-04-07 14:13:29', NULL, NULL, 4, 'WIN_20240930_21_49_09_Pro.jpg', 1800),
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
(31, 'walton', 'Walter Loneza', 'waltonloneza@gmail.com', '$2y$10$7jucgW6Qw9cQEq/aYJp7cOEHWrF/T2VA9o9QlCzYapek./Pl91snW', 'user', 'active', '2025-09-28 13:04:03', NULL, NULL, 49, 'default_profile.png', 1800),
(32, 'michael', 'Michael Jackson', 'notlawsfinds@gmail.com', '$2y$10$2pPV8VpaXhFzQIIDfeJHQOZsu/Ffijefruuw.Ve80FP3iKW9/Ea4y', 'office_admin', 'active', '2025-09-29 05:15:58', NULL, NULL, 4, 'default_profile.png', 1800),
(33, 'joshua', 'Joshua Mari Escano', 'joshuamarifrancis@gmail.com', '$2y$10$EfJTyR7xOmi5v9sylVRq7O4S/lHyFxuexWktQcnkvrImulAL.UzZq', 'user', 'active', '2025-09-29 14:32:40', NULL, NULL, 49, 'default_profile.png', 1800),
(34, 'bumblebee', 'Bumblebee', 'bumblee25@gmail.com', '$2y$10$K3uMNyl/0NUXRaBxNm9Gr.udTa8r2a0JwdtmAJHYcG/eNXyPfznQe', 'user', 'active', '2025-10-05 07:29:38', NULL, NULL, 49, 'default_profile.png', 1800),
(35, 'kara', 'karabasa', 'karabasa601@gmail.com', '$2y$10$DFjWpW.I3pg/TL4z61AYsORyanD.hWnMsTCCcyA/k/7H4PNa4V1Pe', 'user', 'active', '2025-10-05 13:25:10', NULL, NULL, 49, 'default_profile.png', 1800);

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
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_details`
--

CREATE TABLE `vehicle_details` (
  `asset_id` int(11) NOT NULL,
  `plate_number` varchar(50) DEFAULT NULL,
  `engine_number` varchar(100) DEFAULT NULL,
  `chassis_number` varchar(100) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `vehicle_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `type` varchar(100) DEFAULT NULL,
  `or_cr_number` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_details`
--

INSERT INTO `vehicle_details` (`asset_id`, `plate_number`, `engine_number`, `chassis_number`, `color`, `vehicle_type`, `created_at`, `updated_at`, `type`, `or_cr_number`) VALUES
(14, 'ABC 1234', '1NR-FE123456', 'NCP150-1234567', 'Silver', NULL, '2025-10-12 14:17:53', '2025-10-12 14:17:53', 'Sedan', 'OR#2021-56789 / CR#12345678');

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
  ADD KEY `idx_assets_batch_tracking` (`enable_batch_tracking`),
  ADD KEY `idx_assets_ris_id` (`ris_id`),
  ADD KEY `guest_borrowing_request_id` (`guest_borrowing_request_id`);

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
-- Indexes for table `borrow_form_submissions`
--
ALTER TABLE `borrow_form_submissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `submission_number` (`submission_number`),
  ADD KEY `guest_session_id` (`guest_session_id`),
  ADD KEY `guest_email` (`guest_email`);

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
-- Indexes for table `email_notifications`
--
ALTER TABLE `email_notifications`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `guests`
--
ALTER TABLE `guests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `guest_id` (`guest_id`);

--
-- Indexes for table `guest_borrowing_history`
--
ALTER TABLE `guest_borrowing_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `guest_borrowing_items`
--
ALTER TABLE `guest_borrowing_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_id` (`request_id`),
  ADD KEY `asset_id` (`asset_id`);

--
-- Indexes for table `guest_borrowing_requests`
--
ALTER TABLE `guest_borrowing_requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `request_number` (`request_number`),
  ADD KEY `guest_email` (`guest_email`),
  ADD KEY `status` (`status`),
  ADD KEY `approved_by` (`approved_by`);

--
-- Indexes for table `guest_notifications`
--
ALTER TABLE `guest_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guest_id` (`guest_id`),
  ADD KEY `notification_type` (`notification_type`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `expires_at` (`expires_at`),
  ADD KEY `related_entity` (`related_entity_type`,`related_entity_id`);

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
-- Indexes for table `iirup_temp_storage`
--
ALTER TABLE `iirup_temp_storage`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `infrastructure_inventory`
--
ALTER TABLE `infrastructure_inventory`
  ADD PRIMARY KEY (`inventory_id`);

--
-- Indexes for table `infrastructure_inventory_archive`
--
ALTER TABLE `infrastructure_inventory_archive`
  ADD PRIMARY KEY (`archive_id`),
  ADD KEY `idx_inventory_id` (`inventory_id`),
  ADD KEY `idx_archived_at` (`archived_at`);

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
  ADD KEY `created_by` (`created_by`),
  ADD KEY `expires_at` (`expires_at`);

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
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_role_permissions_role_id` (`role_id`);

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
-- Indexes for table `tag_counters`
--
ALTER TABLE `tag_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_tag_year_prefix` (`tag_type`,`year_period`,`prefix_hash`);

--
-- Indexes for table `tag_formats`
--
ALTER TABLE `tag_formats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `tag_type` (`tag_type`);

--
-- Indexes for table `temp_iirup_items`
--
ALTER TABLE `temp_iirup_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `asset_id` (`asset_id`);

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
  ADD KEY `is_read` (`is_read`),
  ADD KEY `deleted_at` (`deleted_at`);

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
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `assigned_by` (`assigned_by`);

--
-- Indexes for table `vehicle_details`
--
ALTER TABLE `vehicle_details`
  ADD PRIMARY KEY (`asset_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `assets_archive`
--
ALTER TABLE `assets_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assets_new`
--
ALTER TABLE `assets_new`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `asset_lifecycle_events`
--
ALTER TABLE `asset_lifecycle_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `asset_requests`
--
ALTER TABLE `asset_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=214;

--
-- AUTO_INCREMENT for table `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
-- AUTO_INCREMENT for table `borrow_form_submissions`
--
ALTER TABLE `borrow_form_submissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `consumption_log`
--
ALTER TABLE `consumption_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `doc_no`
--
ALTER TABLE `doc_no`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `email_notifications`
--
ALTER TABLE `email_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `fuel_records`
--
ALTER TABLE `fuel_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `fuel_types`
--
ALTER TABLE `fuel_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `generated_reports`
--
ALTER TABLE `generated_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `guests`
--
ALTER TABLE `guests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `guest_borrowing_history`
--
ALTER TABLE `guest_borrowing_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guest_borrowing_items`
--
ALTER TABLE `guest_borrowing_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guest_borrowing_requests`
--
ALTER TABLE `guest_borrowing_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `guest_notifications`
--
ALTER TABLE `guest_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ics_form`
--
ALTER TABLE `ics_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `ics_items`
--
ALTER TABLE `ics_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `iirup_form`
--
ALTER TABLE `iirup_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `iirup_items`
--
ALTER TABLE `iirup_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `iirup_temp_storage`
--
ALTER TABLE `iirup_temp_storage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `infrastructure_inventory`
--
ALTER TABLE `infrastructure_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `infrastructure_inventory_archive`
--
ALTER TABLE `infrastructure_inventory_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `inter_department_approvals`
--
ALTER TABLE `inter_department_approvals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory_actions`
--
ALTER TABLE `inventory_actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `itr_form`
--
ALTER TABLE `itr_form`
  MODIFY `itr_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `itr_items`
--
ALTER TABLE `itr_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

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
  MODIFY `mr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notification_types`
--
ALTER TABLE `notification_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `par_form`
--
ALTER TABLE `par_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `par_items`
--
ALTER TABLE `par_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

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
-- AUTO_INCREMENT for table `tag_counters`
--
ALTER TABLE `tag_counters`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;

--
-- AUTO_INCREMENT for table `tag_formats`
--
ALTER TABLE `tag_formats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `temp_iirup_items`
--
ALTER TABLE `temp_iirup_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `unit`
--
ALTER TABLE `unit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

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
  ADD CONSTRAINT `assets_ibfk_guest_borrowing` FOREIGN KEY (`guest_borrowing_request_id`) REFERENCES `guest_borrowing_requests` (`id`) ON DELETE SET NULL,
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
-- Constraints for table `guest_borrowing_history`
--
ALTER TABLE `guest_borrowing_history`
  ADD CONSTRAINT `guest_borrowing_history_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `guest_borrowing_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guest_borrowing_history_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `guest_borrowing_items`
--
ALTER TABLE `guest_borrowing_items`
  ADD CONSTRAINT `guest_borrowing_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `guest_borrowing_requests` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `guest_borrowing_items_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`);

--
-- Constraints for table `guest_borrowing_requests`
--
ALTER TABLE `guest_borrowing_requests`
  ADD CONSTRAINT `guest_borrowing_requests_ibfk_1` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `notification_types` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `notifications_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

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
  ADD CONSTRAINT `fk_role_permissions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `temp_iirup_items`
--
ALTER TABLE `temp_iirup_items`
  ADD CONSTRAINT `temp_iirup_items_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;

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

--
-- Constraints for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `vehicle_details`
--
ALTER TABLE `vehicle_details`
  ADD CONSTRAINT `fk_vehicle_details_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
