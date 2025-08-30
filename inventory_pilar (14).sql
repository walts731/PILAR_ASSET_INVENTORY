-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 30, 2025 at 02:50 PM
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
  `category` int(50) NOT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
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
  `inventory_tag` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`id`, `asset_name`, `category`, `description`, `quantity`, `unit`, `status`, `acquisition_date`, `office_id`, `employee_id`, `red_tagged`, `last_updated`, `value`, `qr_code`, `type`, `image`, `serial_no`, `code`, `property_no`, `model`, `brand`, `inventory_tag`) VALUES
(2, 'Electric Fan', 1, 'Electric Fan Cooling effect with inverter', 1, 'pcs', 'borrowed', '2025-04-06', 9, 1, 0, '2025-08-26 02:30:36', 2500.00, 'QR.png', 'asset', '1754263454_ChatGPTImageJul17202508_24_14AM.png', NULL, NULL, NULL, NULL, NULL, '0'),
(3, 'HP Laptop', 1, 'AMD Ryzen 7, 4 core processor', 3, 'pcs', 'available', '2025-04-06', 2, NULL, 0, '2025-08-26 05:21:51', 200000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(4, 'HP Laptop', 1, 'AMD Ryzen 7', 5, 'pcs', 'available', '2025-04-06', 1, NULL, 0, '2025-07-12 11:14:04', 200000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(10, 'Ballpen', 3, 'Faber Castle', 97, 'pcs', 'unavailable', '2025-04-19', 4, NULL, 0, '2025-08-26 07:29:55', 120.00, 'QR.png', 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(12, 'Printer', 1, 'HP LaserJet Pro MFP M428fdw, color laser printer, scanner, copier, fax', 1, 'pcs', 'available', '2025-04-20', 4, NULL, 0, '2025-08-23 15:11:23', 28000.00, 'QR.png', 'asset', '1754226888_ChatGPTImageJul17202510_05_50AM.png', NULL, NULL, NULL, NULL, NULL, '0'),
(13, 'Delivery Van', 4, 'Toyota Hiace, 2023 Model, Refrigerated Van', 0, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-26 08:09:10', 1500000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(14, 'Generator', 5, '5kW gasoline-powered generator', 0, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-26 05:06:40', 100000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(16, 'Printer', 1, 'Epson EcoTank L3250, All-in-One Inkjet Printer', 0, 'pcs', 'available', '2025-04-20', 4, 20, 0, '2025-08-25 14:41:29', 25000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(17, 'Filing Cabinet', 2, 'Steel filing cabinet with lock, 4 drawers', 3, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-26 07:15:22', 50000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(18, 'Air Conditioner', 1, '1.5-ton window type air conditioning unit', -3, 'pcs', 'available', '2025-04-20', 4, NULL, 0, '2025-08-25 01:52:02', 5000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(19, 'UPS', 6, 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', 4, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-26 07:18:40', 100000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(20, 'Network Switch', 6, 'TP-Link JetStream 24-port Gigabit Ethernet Smart Switch', 0, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-26 07:09:31', 15500.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(21, 'Security Camera', 7, 'Hikvision 4MP IP camera with night vision', 15, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-26 07:07:51', 100000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(22, 'Solar Panel System', 5, '10kW solar photovoltaic system with inverters, batteries, and mounting structures', 15, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-26 05:19:18', 400000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(23, 'Drone', 1, 'DJI Mavic 3 Pro, high-resolution camera, long flight time, obstacle avoidance system', 0, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-25 01:00:04', 50459.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(24, 'Network Router', 6, 'Cisco ASR 1000 Series, high-performance router for internet connectivity and VPNs', 3, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-30 12:09:36', 35674.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(25, 'Data Center Server', 6, 'Dell PowerEdge R750, dual Intel Xeon Gold processors, 256GB RAM, 10TB NVMe SSD', 1, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-25 02:05:49', 56439.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(26, 'Desktop Computer Set', 6, 'Intel i5, 8GB RAM, 1TB HDD', 1, '0', 'available', '2025-04-21', 4, NULL, 0, '2025-06-12 02:24:25', 35000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(27, 'Generator', 5, '15 Liter gasoline generator', 0, '0', 'available', '2025-04-21', 4, NULL, 0, '2025-08-26 05:14:18', 15000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(32, 'Fire Truck', 4, '2023 Model, Red, 1000-gallon water tank, 1000-hp engine, ladder extension, hose reel, emergency lights and sirens', 1, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-07-10 12:55:27', 26500000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(34, 'Office Chair', 3, 'Ergonomic office chair with adjustable height and lumbar support', 4, '0', 'available', '2025-04-20', 4, NULL, 0, '2025-08-25 01:09:43', 5000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(35, 'HP Laptop', 1, 'AMD Ryzen 7, 4 core processor', 3, 'pcs', 'available', '2025-04-06', 2, NULL, 0, '2025-08-26 05:21:51', 200000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(36, 'Oppo A16', 1, '4 GB RAM 64 GB ROM', 1, 'pcs', 'available', '2025-07-01', 3, NULL, 0, '2025-08-30 12:13:50', 16000.00, '36.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(37, 'Everest Aircon', 1, 'R32 Refrigerant, Golden Pins, Easy Clean Filter and Wide Airflow Design.', 1, 'pcs', 'available', '2025-07-01', 2, NULL, 0, '2025-07-01 10:44:42', 9999.00, '37.png', '', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(38, 'Laptop Dell XPS 15', 1, '15-inch Dell XPS laptop, i7 processor, 16GB RAM, 512GB SSD', 1, 'pcs', 'available', '2025-07-01', 2, NULL, 0, '2025-07-01 10:50:31', 15000.00, '38.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(39, 'Blue Chair', 2, 'sdads', 1, 'pcs', 'available', '2025-08-01', 4, NULL, 0, '2025-08-01 05:15:56', 300.00, '39.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(40, '', 1, 'Drawing Pencil drawing sketching, illustrating light fast, durable', 12, 'pcs', 'available', '2025-08-03', 4, NULL, 0, '2025-08-03 13:23:23', 95.00, '40.png', 'consumable', '1754227403_ChatGPTImageJul17202508_14_55AM.png', NULL, NULL, NULL, NULL, NULL, '0'),
(41, '', 5, 'Infirmary Infrastructure', 1, 'yard', 'available', '2025-08-03', 11, NULL, 0, '2025-08-03 12:11:03', 500000.00, '41.png', 'asset', 'asset_1754223063.png', NULL, NULL, NULL, NULL, NULL, '0'),
(42, '', 1, 'Desktop computer for admin use', 4, 'pcs', '', NULL, 2, NULL, 0, '2025-08-25 21:19:31', 25000.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(43, '', 1, 'Filing cabinet with lock', 1, 'pcs', '', NULL, 2, NULL, 0, '2025-08-25 02:41:48', 8000.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(44, '', 1, 'Extension cord 5m', 10, 'pcs', '', NULL, 2, NULL, 0, '2025-08-04 01:03:24', 350.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(45, '', 1, 'Conference table (12-seater)', 1, 'unit', '', NULL, 2, NULL, 0, '2025-08-04 01:03:24', 12000.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(46, '', 1, 'Printer - Inkjet', 3, 'pcs', '', NULL, 2, NULL, 0, '2025-08-04 01:03:24', 4500.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(47, '', 1, 'Desktop computer for admin use', 3, 'pcs', '', NULL, 2, NULL, 0, '2025-08-26 02:19:31', 25000.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(48, '', 1, 'Filing cabinet with lock', 1, 'pcs', '', NULL, 2, NULL, 0, '2025-08-25 02:41:48', 8000.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(49, '', 1, 'Extension cord 5m', 10, 'pcs', '', NULL, 2, NULL, 0, '2025-08-04 01:03:40', 350.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(50, '', 1, 'Conference table (12-seater)', 1, 'unit', '', NULL, 2, NULL, 0, '2025-08-04 01:03:40', 12000.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(51, '', 1, 'Printer - Inkjet', 3, 'pcs', '', NULL, 2, NULL, 0, '2025-08-04 01:03:40', 4500.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(52, '', 1, 'Desktop computer for admin use', 3, 'pcs', '', NULL, 2, NULL, 0, '2025-08-26 02:19:31', 25000.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(53, '', 1, 'Filing cabinet with lock', 1, 'pcs', '', NULL, 2, NULL, 0, '2025-08-25 02:41:48', 8000.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(54, '', 1, 'Extension cord 5m', 10, 'pcs', '', NULL, 2, NULL, 0, '2025-08-04 01:06:57', 350.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(55, '', 1, 'Conference table (12-seater)', 1, 'unit', '', NULL, 2, NULL, 0, '2025-08-04 01:06:57', 12000.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(56, '', 1, 'Printer - Inkjet', 3, 'pcs', '', NULL, 2, NULL, 0, '2025-08-04 01:06:57', 4500.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(57, '', 1, 'Electric Drill', 5, 'pcs', 'available', '2025-08-04', 2, NULL, 0, '2025-08-04 11:31:48', 1200.50, '57.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(58, '', 1, 'Laptop', 9, 'pcs', 'available', '2025-08-04', 2, NULL, 0, '2025-08-26 04:47:12', 45000.00, '58.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(59, '', 1, 'Ballpen Black', 99, 'pcs', 'available', '2025-08-04', 2, NULL, 0, '2025-08-25 01:29:01', 10.50, '59.png', 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(60, '', 1, 'Electric Drill', 5, 'pcs', 'available', '2025-08-04', 2, NULL, 0, '2025-08-04 11:42:16', 1200.50, '60.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(61, '', 1, 'Laptop', 9, 'pcs', 'available', '2025-08-04', 2, NULL, 0, '2025-08-26 04:47:12', 45000.00, '61.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(62, '', 1, 'Ballpen Black', 99, 'pcs', 'available', '2025-08-04', 2, NULL, 0, '2025-08-25 01:29:01', 10.50, '62.png', 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(63, '', 1, 'Wipes gentle clean', 12, 'pcs', 'available', '2025-08-19', 1, NULL, 0, '2025-08-19 04:55:35', 50.00, '63.png', 'asset', 'asset_1755579335.jpg', NULL, NULL, NULL, NULL, NULL, '0'),
(64, '', 1, 'iPhone 16 Pro Max Fully Paid', 7, 'pcs', 'available', '2025-08-20', 9, NULL, 0, '2025-08-26 03:19:51', 98000.00, '64.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(66, '', 1, 'iPhone 16 Pro Max Fully Paid', -14, 'pcs', 'available', '2025-08-20', 4, NULL, 0, '2025-08-26 03:19:51', 98000.00, '64.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(67, '', 1, 'iPhone 16 Pro Max Fully Paid', -13, 'pcs', 'available', '2025-08-20', 7, NULL, 0, '2025-08-26 03:19:51', 98000.00, '64.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(68, '', 1, 'iPhone 16 Pro Max Fully Paid', -12, 'pcs', 'available', '2025-08-20', 10, NULL, 0, '2025-08-26 03:19:51', 98000.00, '64.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(69, '', 1, 'iPhone 16 Pro Max Fully Paid', -11, 'pcs', 'available', '2025-08-20', 3, NULL, 0, '2025-08-26 03:19:51', 98000.00, '64.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(70, 'Printer', 1, 'HP LaserJet Pro MFP M428fdw, color laser printer, scanner, copier, fax', 1, 'pcs', 'available', '2025-04-20', 6, NULL, 0, '2025-08-23 10:11:23', 28000.00, 'QR.png', 'asset', '1754226888_ChatGPTImageJul17202510_05_50AM.png', NULL, NULL, NULL, NULL, NULL, '0'),
(71, '', 1, 'iPhone 16 Pro Max Fully Paid', -10, 'pcs', 'available', '2025-08-20', 5, NULL, 0, '2025-08-26 03:19:51', 98000.00, '64.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(72, '', 1, 'iPhone 16 Pro Max Fully Paid', -9, 'pcs', 'available', '2025-08-20', 2, NULL, 0, '2025-08-26 03:19:51', 98000.00, '64.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(73, '', 1, 'iPhone 16 Pro Max Fully Paid', -8, 'pcs', 'available', '2025-08-20', 6, NULL, 0, '2025-08-26 03:19:51', 98000.00, '64.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(74, '', 1, 'iPhone 16 Pro Max Fully Paid', -1, 'pcs', 'available', '2025-08-20', 1, NULL, 0, '2025-08-26 03:19:51', 98000.00, '64.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(75, 'Solar Panel System', 5, '10kW solar photovoltaic system with inverters, batteries, and mounting structures', -3, '0', 'available', '2025-04-20', 3, NULL, 0, '2025-08-26 05:19:18', 400000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(76, 'Delivery Van', 4, 'Toyota Hiace, 2023 Model, Refrigerated Van', 0, '0', 'available', '2025-04-20', 10, NULL, 0, '2025-08-26 08:09:10', 1500000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(77, 'Printer', 1, 'Epson EcoTank L3250, All-in-One Inkjet Printer', -1, 'pcs', 'available', '2025-04-20', 10, NULL, 0, '2025-08-25 01:13:41', 25000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(78, 'Security Camera', 7, 'Hikvision 4MP IP camera with night vision', -1, '0', 'available', '2025-04-20', 10, NULL, 0, '2025-08-26 07:07:51', 100000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(79, 'Solar Panel System', 5, '10kW solar photovoltaic system with inverters, batteries, and mounting structures', -2, '0', 'available', '2025-04-20', 10, NULL, 0, '2025-08-26 05:19:18', 400000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(80, 'Drone', 1, 'DJI Mavic 3 Pro, high-resolution camera, long flight time, obstacle avoidance system', 2, '0', 'available', '2025-04-20', 10, NULL, 0, '2025-08-24 20:00:04', 50459.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(81, 'Printer', 1, 'Epson EcoTank L3250, All-in-One Inkjet Printer', 1, 'pcs', 'available', '2025-04-20', 11, NULL, 0, '2025-08-24 20:13:41', 25000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(82, 'Air Conditioner', 1, '1.5-ton window type air conditioning unit', -1, 'pcs', 'available', '2025-04-20', 11, NULL, 0, '2025-08-25 01:52:02', 5000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(83, '', 1, 'Desktop computer for admin use', 0, 'pcs', '', NULL, 10, NULL, 0, '2025-08-26 02:19:31', 25000.00, NULL, 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(84, 'Office Chair', 3, 'Ergonomic office chair with adjustable height and lumbar support', 1, '0', 'available', '2025-04-20', 10, NULL, 0, '2025-08-24 20:09:43', 5000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(85, 'Solar Panel System', 5, '10kW solar photovoltaic system with inverters, batteries, and mounting structures', 0, '0', 'available', '2025-04-20', 11, NULL, 0, '2025-08-26 05:19:18', 400000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(86, '', 1, 'Ballpen Black', 1, 'pcs', 'available', '2025-08-04', 11, NULL, 0, '2025-08-24 20:29:01', 10.50, '59.png', 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(87, 'Security Camera', 7, 'Hikvision 4MP IP camera with night vision', 0, '0', 'available', '2025-04-20', 11, NULL, 0, '2025-08-26 07:07:51', 100000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(88, 'HP Laptop', 1, 'AMD Ryzen 7, 4 core processor', 0, 'pcs', 'available', '2025-04-06', 11, NULL, 0, '2025-08-26 05:21:51', 200000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(89, 'UPS', 6, 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', -1, '0', 'available', '2025-04-20', 10, NULL, 0, '2025-08-26 07:18:40', 100000.00, 'QR.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(90, 'Air Conditioner', 1, '1.5-ton window type air conditioning unit', 1, 'pcs', 'available', '2025-04-20', 2, NULL, 0, '2025-08-25 01:52:02', 5000.00, '90.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(91, 'Data Center Server', 6, 'Dell PowerEdge R750, dual Intel Xeon Gold processors, 256GB RAM, 10TB NVMe SSD', 1, '0', 'available', '2025-04-20', 7, NULL, 0, '2025-08-25 02:05:49', 56439.00, '91.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(92, '', 1, 'Filing cabinet with lock', 1, 'pcs', '', NULL, 11, NULL, 0, '2025-08-25 02:41:48', 8000.00, '92.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(93, 'Electric Fan', 1, 'Electric Fan Cooling effect with inverter', 1, 'pcs', 'borrowed', '2025-04-06', 3, NULL, 0, '2025-08-26 02:53:47', 2500.00, '93.png', 'asset', '1754263454_ChatGPTImageJul17202508_24_14AM.png', NULL, NULL, NULL, NULL, NULL, '0'),
(94, 'Network Router', 6, 'Cisco ASR 1000 Series, high-performance router for internet connectivity and VPNs', 0, '0', 'available', '2025-04-20', 2, NULL, 0, '2025-08-30 12:09:36', 35674.00, '94.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(95, '', 1, 'iPhone 16 Pro Max Fully Paid', 1, 'pcs', 'available', '2025-08-20', 8, NULL, 0, '2025-08-26 03:20:04', 98000.00, '95.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(96, 'UPS', 6, 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', 0, '0', 'available', '2025-04-20', 5, NULL, 0, '2025-08-26 07:18:40', 100000.00, '96.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(97, 'Ballpen', 3, 'Faber Castle', -1, 'pcs', 'unavailable', '2025-04-19', 7, NULL, 0, '2025-08-26 07:29:55', 120.00, '97.png', 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(98, '', 1, 'Laptop', 1, 'pcs', 'available', '2025-08-04', 9, NULL, 0, '2025-08-26 04:49:06', 45000.00, '98.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(99, 'Generator', 5, '5kW gasoline-powered generator', 1, '0', 'available', '2025-04-20', 11, NULL, 0, '2025-08-26 05:06:49', 100000.00, '99.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(100, 'Generator', 5, '15 Liter gasoline generator', 1, '0', 'available', '2025-04-21', 5, NULL, 0, '2025-08-26 05:14:32', 15000.00, '100.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(101, 'Solar Panel System', 5, '10kW solar photovoltaic system with inverters, batteries, and mounting structures', 1, '0', 'available', '2025-04-20', 6, NULL, 0, '2025-08-26 05:19:18', 400000.00, '101.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(102, 'HP Laptop', 1, 'AMD Ryzen 7, 4 core processor', 1, 'pcs', 'available', '2025-04-06', 1, NULL, 0, '2025-08-26 05:27:23', 200000.00, '102.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(103, 'Network Switch', 6, 'TP-Link JetStream 24-port Gigabit Ethernet Smart Switch', 0, '0', 'available', '2025-04-20', 10, NULL, 0, '2025-08-26 07:09:31', 15500.00, '103.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(104, 'Filing Cabinet', 2, 'Steel filing cabinet with lock, 4 drawers', 0, '0', 'available', '2025-04-20', 6, NULL, 0, '2025-08-26 07:15:22', 50000.00, '104.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(105, 'Ballpen', 3, 'Faber Castle', 0, 'pcs', 'unavailable', '2025-04-19', 10, NULL, 0, '2025-08-26 07:29:55', 120.00, '105.png', 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(106, 'Security Camera', 7, 'Hikvision 4MP IP camera with night vision', 1, '0', 'available', '2025-04-20', 3, NULL, 0, '2025-08-26 07:08:02', 100000.00, '106.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(107, 'Network Switch', 6, 'TP-Link JetStream 24-port Gigabit Ethernet Smart Switch', 1, '0', 'available', '2025-04-20', 5, NULL, 0, '2025-08-26 07:09:40', 15500.00, '107.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(108, 'Filing Cabinet', 2, 'Steel filing cabinet with lock, 4 drawers', 1, '0', 'available', '2025-04-20', 10, NULL, 0, '2025-08-26 07:15:53', 50000.00, '108.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(109, 'UPS', 6, 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', 1, '0', 'available', '2025-04-20', 2, 3, 0, '2025-08-26 07:22:05', 100000.00, '109.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(110, 'Ballpen', 3, 'Faber Castle', 1, 'pcs', 'unavailable', '2025-04-19', 11, NULL, 0, '2025-08-26 07:30:26', 120.00, '110.png', 'consumable', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(111, 'Delivery Van', 4, 'Toyota Hiace, 2023 Model, Refrigerated Van', 1, '0', 'available', '2025-04-20', 2, 1, 0, '2025-08-26 08:09:19', 1500000.00, '111.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(112, 'Network Router', 6, 'Cisco ASR 1000 Series, high-performance router for internet connectivity and VPNs', 1, '0', 'available', '2025-04-20', 10, 1, 0, '2025-08-30 12:11:41', 35674.00, '112.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, '0'),
(113, 'Oppo A16', 1, '4 GB RAM 64 GB ROM', 1, 'pcs', 'available', '2025-07-01', 11, 22, 0, '2025-08-30 12:45:26', 16000.00, '113.png', 'asset', NULL, NULL, NULL, NULL, NULL, NULL, 'No. PS-5S-03-F02-95');

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
(14, 33, 'Blue Chair', 2, 'Uratex', 3, 'pcs', 'available', '2025-04-04', 4, 0, '2025-06-13 08:39:23', 30000.00, 'QR.png', 'asset', '2025-06-21 12:04:12');

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
  `status` enum('pending','approved','rejected','borrowed','returned') NOT NULL DEFAULT 'pending',
  `requested_at` datetime DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `return_remarks` text DEFAULT NULL,
  `returned_at` datetime DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(13, 'Luminaires', 'asset');

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
  `filename` varchar(255) NOT NULL,
  `template_id` int(11) NOT NULL,
  `generated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `generated_reports`
--

INSERT INTO `generated_reports` (`id`, `user_id`, `filename`, `template_id`, `generated_at`) VALUES
(3, 17, 'Inventory_Report_20250709_082630.pdf', 0, '2025-07-09 11:26:30'),
(4, 17, 'Inventory_Report_20250709_083052.pdf', 3, '2025-07-09 11:30:53'),
(5, 17, 'Inventory_Report_20250709_083104.pdf', 2, '2025-07-09 11:31:05'),
(6, 17, 'Inventory_Report_20250709_083747.pdf', 2, '2025-07-09 11:37:48'),
(7, 17, 'Inventory_Report_20250709_083756.pdf', 5, '2025-07-09 11:37:57'),
(8, 17, 'Inventory_Report_20250709_084028.pdf', 5, '2025-07-09 11:40:29'),
(9, 17, 'Inventory_Report_20250709_084118.pdf', 5, '2025-07-09 11:41:19'),
(10, 17, 'Inventory_Report_20250709_084201.pdf', 5, '2025-07-09 11:42:02'),
(11, 17, 'Inventory_Report_20250709_084352.pdf', 2, '2025-07-09 11:43:52'),
(12, 17, 'Inventory_Report_20250709_084609.pdf', 5, '2025-07-09 11:46:10'),
(13, 17, 'Inventory_Report_20250709_143428.pdf', 3, '2025-07-09 17:34:29'),
(14, 17, 'Inventory_Report_20250709_143918.pdf', 3, '2025-07-09 17:39:19'),
(15, 17, 'Inventory_Report_20250709_144102.pdf', 28, '2025-07-09 17:41:02'),
(16, 17, 'Inventory_Report_20250709_144323.pdf', 28, '2025-07-09 17:43:23'),
(17, 17, 'Inventory_Report_20250709_144400.pdf', 28, '2025-07-09 17:44:00'),
(18, 17, 'Inventory_Report_20250709_144416.pdf', 27, '2025-07-09 17:44:16'),
(19, 17, 'Inventory_Report_20250709_144544.pdf', 27, '2025-07-09 17:45:44'),
(20, 17, 'Inventory_Report_20250709_144700.pdf', 27, '2025-07-09 17:47:00'),
(21, 17, 'Inventory_Report_20250709_145032.pdf', 26, '2025-07-09 17:50:36'),
(22, 17, 'Inventory_Report_20250709_150358.pdf', 26, '2025-07-09 18:03:59'),
(23, 17, 'Inventory_Report_20250709_151145.pdf', 26, '2025-07-09 18:11:45'),
(24, 17, 'Inventory_Report_20250709_152847.pdf', 28, '2025-07-09 18:28:50'),
(25, 17, 'Inventory_Report_20250709_152941.pdf', 29, '2025-07-09 18:29:41'),
(26, 17, 'Inventory_Report_20250709_153917.pdf', 2, '2025-07-09 18:39:17'),
(27, 17, 'Inventory_Report_20250709_153924.pdf', 30, '2025-07-09 18:39:24'),
(28, 17, 'Inventory_Report_20250710_035858.pdf', 30, '2025-07-10 06:59:03'),
(29, 17, 'Inventory_Report_20250710_144534.pdf', 30, '2025-07-10 17:45:38'),
(30, 17, 'Inventory_Report_20250711_091238.pdf', 33, '2025-07-11 12:12:43'),
(31, 17, 'Inventory_Report_20250711_091547.pdf', 33, '2025-07-11 12:15:48'),
(32, 17, 'Inventory_Report_20250712_075945.pdf', 33, '2025-07-12 10:59:50'),
(33, 17, 'Inventory_Report_20250712_080048.pdf', 33, '2025-07-12 11:00:48'),
(34, 17, 'Inventory_Report_20250714_041546.pdf', 4, '2025-07-14 09:15:51'),
(35, 17, 'Inventory_Report_20250801_073031.pdf', 3, '2025-08-01 12:30:36'),
(36, 17, 'Inventory_Report_20250801_093122.pdf', 3, '2025-08-01 14:31:23'),
(37, 17, 'Inventory_Report_20250802_141907.pdf', 0, '2025-08-02 20:19:11'),
(38, 17, 'Inventory_Report_20250804_015832.pdf', 0, '2025-08-04 06:58:37'),
(39, 17, 'Inventory_Report_20250804_020323.pdf', 0, '2025-08-04 07:03:24'),
(40, 17, 'Inventory_Report_20250804_020424.pdf', 0, '2025-08-04 07:04:25'),
(41, 17, 'Inventory_Report_20250819_070930.pdf', 0, '2025-08-19 13:09:35'),
(42, 17, 'Inventory_Report_20250829_131934.pdf', 0, '2025-08-29 18:19:35');

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
(1, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ics-001', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-23 10:30:58', NULL),
(16, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ics-001', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-24 07:18:50', NULL),
(17, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ics-001', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-24 07:22:30', NULL),
(18, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0001', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-24 07:25:15', NULL),
(19, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0002', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-24 07:31:47', NULL),
(20, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0003', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-24 19:01:23', NULL),
(23, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0004', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-24 19:15:45', 10),
(24, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0005', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-24 19:49:43', 10),
(28, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0006', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-24 20:00:04', 10),
(29, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0007', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-25 01:03:01', 11),
(30, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0008', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-25 01:09:43', 10),
(31, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0009', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-25 01:13:41', 11),
(32, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0010', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-25 01:29:01', 11),
(33, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0011', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-25 01:43:59', 10),
(34, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0012', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-25 01:45:40', 11),
(35, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0013', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-25 01:50:28', 2),
(36, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0013', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-25 01:52:02', 2),
(37, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0014', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-25 02:05:49', 7),
(38, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0015', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-25 02:41:48', 11),
(39, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0016', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 02:19:31', 2),
(40, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0017', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 02:30:36', 3),
(41, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0018', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 02:59:17', 1),
(42, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0019', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 03:07:23', 1),
(43, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0020', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 03:12:04', 2),
(44, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0021', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 03:19:51', 8),
(45, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0022', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 03:45:15', 5),
(46, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0023', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 03:51:58', 7),
(47, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0024', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 04:47:12', 9),
(48, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0025', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 05:06:40', 11),
(49, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0026', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 05:14:18', 5),
(50, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0027', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 05:19:18', 6),
(51, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0028', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 05:21:51', 1),
(52, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0029', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 05:23:22', 10),
(53, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0030', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 05:26:37', 6),
(54, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0031', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 06:59:40', 10),
(55, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0032', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 07:07:51', 3),
(56, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0033', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 07:09:31', 5),
(57, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0034', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 07:15:22', 10),
(58, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0035', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 07:18:40', 2),
(59, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0036', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 07:29:55', 11),
(60, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0037', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-26 08:09:10', 2),
(61, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0038', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-30 12:09:36', 10),
(62, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0039', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-30 12:13:34', 11),
(63, '1755859912_Screenshot 2025-08-22 103403.png', 'INVENTORY', 'fc-001', 'ICS-2025-0039', 'IVAN CHRISTOPHER R. MILLABAS', 'OFFICER', 'MARK JAYSON NAMIA', 'PROPERTY CUSTODIAN', '2025-08-30 12:13:50', 11);

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
(1, 1, NULL, NULL, 2, 'pcs', 15500.00, 31000.00, 'TP-Link JetStream 24-port Gigabit Ethernet Smart Switch', '', '2 years', '2025-08-23 08:45:16'),
(2, 1, NULL, NULL, 1, 'pcs', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '2 years', '2025-08-23 08:51:04'),
(3, 1, NULL, NULL, 1, 'can', 100000.00, 100000.00, 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', '', '2 years', '2025-08-23 08:51:36'),
(4, 1, NULL, NULL, 1, 'can', 100000.00, 0.00, 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', '', '2 years', '2025-08-23 08:51:59'),
(5, 1, NULL, NULL, 1, 'pcs', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '2 years', '2025-08-23 08:56:06'),
(6, 1, NULL, NULL, 1, 'pcs', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 08:57:57'),
(8, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '2 years', '2025-08-23 04:04:38'),
(11, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '2 years', '2025-08-23 04:14:35'),
(12, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 04:15:12'),
(20, 1, NULL, NULL, 2, '0', 25000.00, 50000.00, 'Epson EcoTank L3250, All-in-One Inkjet Printer', '', '', '2025-08-23 07:51:49'),
(21, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 07:53:20'),
(22, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 08:51:18'),
(23, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 08:52:12'),
(24, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 08:55:20'),
(25, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 08:58:07'),
(26, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 09:01:16'),
(27, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 09:05:38'),
(28, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 09:08:02'),
(29, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 09:13:30'),
(30, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 09:50:06'),
(31, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:04:02'),
(32, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:05:22'),
(33, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:06:05'),
(34, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:06:38'),
(35, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:08:45'),
(36, 1, NULL, NULL, 1, '0', 28000.00, 28000.00, 'HP LaserJet Pro MFP M428fdw, color laser printer, scanner, copier, fax', '', '', '2025-08-23 10:11:23'),
(37, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:21:22'),
(38, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:24:45'),
(39, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:26:59'),
(40, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:30:04'),
(41, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:30:05'),
(42, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:30:06'),
(43, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:30:06'),
(44, 1, NULL, NULL, 1, '0', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-23 10:30:07'),
(45, 1, NULL, NULL, 1, '0', 400000.00, 400000.00, '10kW solar photovoltaic system with inverters, batteries, and mounting structures', '', '', '2025-08-23 10:30:58'),
(47, 16, NULL, 'ics-001', 1, '98000', 98000.00, 0.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-24 07:18:50'),
(48, 17, NULL, 'ics-001', 1, '100000', 100000.00, 0.00, 'Hikvision 4MP IP camera with night vision', '', '', '2025-08-24 07:22:30'),
(49, 18, NULL, 'ICS-2025-0001', 1, '100000', 100000.00, 0.00, 'Hikvision 4MP IP camera with night vision', '', '', '2025-08-24 07:25:15'),
(50, 19, NULL, 'ICS-2025-0002', 1, '100000', 100000.00, 0.00, 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', '', '', '2025-08-24 07:31:47'),
(51, 19, NULL, 'ICS-2025-0002', 1, '15500', 15500.00, 0.00, 'TP-Link JetStream 24-port Gigabit Ethernet Smart Switch', '', '', '2025-08-24 07:31:47'),
(52, 20, NULL, 'ICS-2025-0003', 1, '25000', 25000.00, 0.00, 'Epson EcoTank L3250, All-in-One Inkjet Printer', '1', '2 years', '2025-08-24 19:01:23'),
(53, 23, NULL, 'ICS-2025-0004', 1, '100000', 0.00, 0.00, 'Hikvision 4MP IP camera with night vision', '1', '2 years', '2025-08-24 19:15:45'),
(54, 24, NULL, 'ICS-2025-0005', 1, 'box', 400000.00, 400000.00, '10kW solar photovoltaic system with inverters, batteries, and mounting structures', '', '', '2025-08-24 19:49:43'),
(55, 28, 23, 'ICS-2025-0006', 2, '0', 50459.00, 100918.00, 'DJI Mavic 3 Pro, high-resolution camera, long flight time, obstacle avoidance system', '1', '2 years', '0000-00-00 00:00:00'),
(56, 29, 16, 'ICS-2025-0007', 1, '0', 25000.00, 25000.00, 'Epson EcoTank L3250, All-in-One Inkjet Printer', '1', '2 years', '0000-00-00 00:00:00'),
(57, 29, 18, 'ICS-2025-0007', 1, '0', 5000.00, 5000.00, '1.5-ton window type air conditioning unit', '1', '2 years', '0000-00-00 00:00:00'),
(58, 30, 42, 'ICS-2025-0008', 1, '0', 25000.00, 25000.00, 'Desktop computer for admin use', '1', '2 years', '2025-08-25 01:09:43'),
(59, 30, 34, 'ICS-2025-0008', 1, '0', 5000.00, 5000.00, 'Ergonomic office chair with adjustable height and lumbar support', '1', '2 years', '2025-08-25 01:09:43'),
(60, 31, 16, 'ICS-2025-0009', 1, '0', 25000.00, 25000.00, 'Epson EcoTank L3250, All-in-One Inkjet Printer', '', '', '2025-08-25 01:13:41'),
(61, 32, 22, 'ICS-2025-0010', 1, '0', 400000.00, 400000.00, '10kW solar photovoltaic system with inverters, batteries, and mounting structures', '', '', '2025-08-25 01:29:01'),
(62, 32, 59, 'ICS-2025-0010', 1, '0', 10.50, 10.50, 'Ballpen Black', '', '', '2025-08-25 01:29:01'),
(63, 32, 21, 'ICS-2025-0010', 1, '0', 100000.00, 100000.00, 'Hikvision 4MP IP camera with night vision', '', '', '2025-08-25 01:29:01'),
(64, 32, 18, 'ICS-2025-0010', 1, '0', 5000.00, 5000.00, '1.5-ton window type air conditioning unit', '', '', '2025-08-25 01:29:01'),
(65, 32, 3, 'ICS-2025-0010', 1, '0', 200000.00, 200000.00, 'AMD Ryzen 7, 4 core processor', '', '', '2025-08-25 01:29:01'),
(66, 33, 19, 'ICS-2025-0011', 0, '1', 100000.00, 100000.00, 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', '', '', '2025-08-25 01:43:59'),
(67, 34, 22, 'ICS-2025-0012', 1, 'tube', 400000.00, 400000.00, '10kW solar photovoltaic system with inverters, batteries, and mounting structures', '', '', '2025-08-25 01:45:40'),
(68, 35, 18, 'ICS-2025-0013', 0, '1', 5000.00, 5000.00, '1.5-ton window type air conditioning unit', '', '', '2025-08-25 01:50:28'),
(69, 36, 18, 'ICS-2025-0013', 1, 'pcs', 5000.00, 5000.00, '1.5-ton window type air conditioning unit', '', '', '2025-08-25 01:52:02'),
(70, 37, 91, 'ICS-2025-0014', 1, 'pcs', 56439.00, 56439.00, 'Dell PowerEdge R750, dual Intel Xeon Gold processors, 256GB RAM, 10TB NVMe SSD', '', '', '2025-08-25 02:05:49'),
(71, 38, 92, 'ICS-2025-0015', 1, 'pcs', 8000.00, 8000.00, 'Filing cabinet with lock', '', '', '2025-08-25 02:41:48'),
(72, 39, 42, 'ICS-2025-0016', 1, 'pcs', 25000.00, 25000.00, 'Desktop computer for admin use', '1', '2 years', '2025-08-26 02:19:31'),
(73, 40, 93, 'ICS-2025-0017', 1, 'pcs', 2500.00, 2500.00, 'Electric Fan Cooling effect with inverter', '1', '2 years', '2025-08-26 02:30:36'),
(74, 41, 74, 'ICS-2025-0018', 1, 'pcs', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '1', '2 years', '2025-08-26 02:59:17'),
(75, 42, 74, 'ICS-2025-0019', 1, 'pcs', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-26 03:07:23'),
(76, 43, 94, 'ICS-2025-0020', 1, 'tube', 35674.00, 35674.00, 'Cisco ASR 1000 Series, high-performance router for internet connectivity and VPNs', '', '', '2025-08-26 03:12:04'),
(77, 44, 95, 'ICS-2025-0021', 1, 'pcs', 98000.00, 98000.00, 'iPhone 16 Pro Max Fully Paid', '', '', '2025-08-26 03:19:51'),
(78, 45, 96, 'ICS-2025-0022', 1, '', 100000.00, 100000.00, 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', '', '', '2025-08-26 03:45:15'),
(79, 46, 97, 'ICS-2025-0023', 1, 'pcs', 120.00, 120.00, 'Faber Castle', '', '', '2025-08-26 03:51:58'),
(80, 47, 98, 'ICS-2025-0024', 1, 'pcs', 45000.00, 45000.00, 'Laptop', '', '', '2025-08-26 04:47:12'),
(81, 48, 99, 'ICS-2025-0025', 1, 'pcs', 100000.00, 100000.00, '5kW gasoline-powered generator', '', '', '2025-08-26 05:06:40'),
(82, 49, 100, 'ICS-2025-0026', 1, 'can', 15000.00, 15000.00, '15 Liter gasoline generator', '', '', '2025-08-26 05:14:18'),
(83, 50, 101, 'ICS-2025-0027', 1, 'box', 400000.00, 400000.00, '10kW solar photovoltaic system with inverters, batteries, and mounting structures', '', '', '2025-08-26 05:19:18'),
(84, 51, 102, 'ICS-2025-0028', 1, 'pcs', 200000.00, 200000.00, 'AMD Ryzen 7, 4 core processor', '', '', '2025-08-26 05:21:51'),
(85, 52, 103, 'ICS-2025-0029', 1, '', 15500.00, 15500.00, 'TP-Link JetStream 24-port Gigabit Ethernet Smart Switch', '', '', '2025-08-26 05:23:22'),
(86, 53, 104, 'ICS-2025-0030', 1, '', 50000.00, 50000.00, 'Steel filing cabinet with lock, 4 drawers', '', '', '2025-08-26 05:26:37'),
(87, 54, 105, 'ICS-2025-0031', 1, 'pcs', 120.00, 120.00, 'Faber Castle', '', '', '2025-08-26 06:59:40'),
(88, 55, 106, 'ICS-2025-0032', 1, 'pcs', 100000.00, 100000.00, 'Hikvision 4MP IP camera with night vision', '', '', '2025-08-26 07:07:51'),
(89, 56, 107, 'ICS-2025-0033', 1, 'can', 15500.00, 15500.00, 'TP-Link JetStream 24-port Gigabit Ethernet Smart Switch', '', '', '2025-08-26 07:09:31'),
(90, 57, 108, 'ICS-2025-0034', 1, '', 50000.00, 50000.00, 'Steel filing cabinet with lock, 4 drawers', '', '', '2025-08-26 07:15:22'),
(91, 58, 109, 'ICS-2025-0035', 1, '', 100000.00, 100000.00, 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', '', '', '2025-08-26 07:18:40'),
(92, 59, 110, 'ICS-2025-0036', 1, 'pcs', 120.00, 120.00, 'Faber Castle', '', '', '2025-08-26 07:29:55'),
(93, 60, 111, 'ICS-2025-0037', 1, '', 1500000.00, 1500000.00, 'Toyota Hiace, 2023 Model, Refrigerated Van', '', '', '2025-08-26 08:09:10'),
(94, 61, 112, 'ICS-2025-0038', 1, 'bottle', 35674.00, 35674.00, 'Cisco ASR 1000 Series, high-performance router for internet connectivity and VPNs', '1', '2 years', '2025-08-30 12:09:36'),
(95, 63, 113, 'ICS-2025-0039', 1, 'pcs', 16000.00, 16000.00, '4 GB RAM 64 GB ROM', '1', '2 years', '2025-08-30 12:13:50');

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
(5, '1756475584_Screenshot 2025-08-29 204458.png', 'WALTON LONEZA', 'OFFICE', 'DILG', 'MA. ANNIE L. PERETE', 'CAROLYN C. SY-REYES', 'Public Information Officer II', 'Municipal Mayor', '2025-08-29 13:53:04');

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
(2, 'BUILDING', 'Multi Purpose bldg.', 'Offices', 'LGU-Complex', '2022-12-21', 'BLDNG22-32', 6792388.00, 777406.50, '2023-01-21', '', NULL, NULL, NULL, NULL),
(3, 'BUILDING', 'Pilar Gymnasium', 'Gymnasium', 'LGU-Complex', '2022-01-21', 'BLDNG22-43', 26000000.00, 14405276.00, '2023-01-21', '', 'uploads/1755784395_397369.jpg', NULL, NULL, NULL),
(4, 'BUILDING', 'picc building A', 'Offices', 'Apad Calongay', '2022-01-21', 'BLDNG22-30', 6890000.00, 16427920.00, '2023-01-21', '', 'uploads/1755784688_summertime wallpaper.png', NULL, NULL, NULL),
(5, 'BUILDING', 'Pilar Gymnasium', 'Gymnasium', 'LGU-Complex', '2025-08-21', 'BLDNG22-32', 6890000.00, 6890000.00, '2025-08-21', '', '1755784792_summertime wallpaper.png', NULL, NULL, NULL),
(6, 'BUILDING', 'Multi Purpose bldg.', 'Offices', 'IT office', '2025-08-21', 'BLDNG22-43', 6890000.00, 6890000.00, '2025-08-21', '', 'uploads/1755785620_397369.jpg', 'uploads/1755785620_summer wallpaper.png', NULL, 'uploads/1755785620_summertime wallpaper.png');

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
(1, 71, 'DSWD', 'Filing cabinet with lock', '', '', 1, 0, 1.00, 'pcs', '2025-08-25', 8000.00, 'Elton John Moises', '0000-00-00', '0000-00-00', '2025-08-25 06:00:10', NULL, ''),
(2, 70, 'RHU', 'Dell PowerEdge R750, dual Intel Xeon Gold processors, 256GB RAM, 10TB NVMe SSD', '', '', 1, 0, 1.00, 'kg', '2025-04-20', 56439.00, 'Elton John Moises', '0000-00-00', '0000-00-00', '2025-08-25 10:32:12', 91, ''),
(3, 66, 'Supply Office', 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', '', '', 0, 1, 6.00, 'kg', '2025-04-20', 100000.00, 'Elton John Moises', '0000-00-00', '0000-00-00', '2025-08-25 12:34:46', 19, ''),
(4, 60, 'Supply Office', 'Epson EcoTank L3250, All-in-One Inkjet Printer', '', '', 1, 0, 0.00, 'pcs', '2025-04-20', 25000.00, 'MARK JAYSON NAMIA', '0000-00-00', '0000-00-00', '2025-08-25 12:44:18', 16, 'No. PS-5S-03-F02-60'),
(5, 57, 'Supply Office', '1.5-ton window type air conditioning unit', '', '', 1, 0, 6.00, 'pcs', '2025-04-20', 5000.00, 'MARK JAYSON NAMIA', '0000-00-00', '0000-00-00', '2025-08-25 13:31:46', 18, 'No. PS-5S-03-F02-57'),
(6, 56, 'Supply Office', 'Epson EcoTank L3250, All-in-One Inkjet Printer', '', '', 1, 0, 1.00, 'pcs', '2025-04-20', 25000.00, '20', '0000-00-00', '0000-00-00', '2025-08-25 14:41:29', 16, 'No. PS-5S-03-F02-56'),
(7, 72, 'IT Office', 'Desktop computer for admin use', '', '', 1, 0, 4.00, 'pcs', '2025-08-26', 25000.00, NULL, '0000-00-00', '0000-00-00', '2025-08-26 02:20:22', 42, 'No. PS-5S-03-F02-72'),
(8, 73, 'OMASS', 'Electric Fan Cooling effect with inverter', '', '', 1, 0, 1.00, 'pcs', '2025-04-06', 2500.00, 'Jude Dwight Oscar Jimenez', '0000-00-00', '0000-00-00', '2025-08-26 02:53:47', 93, 'No. PS-5S-03-F02-73'),
(9, 74, 'OMPDC', 'iPhone 16 Pro Max Fully Paid', '', '', 1, 0, 2.00, 'pcs', '2025-08-20', 98000.00, 'John Kenneth Litana', '0000-00-00', '0000-00-00', '2025-08-26 02:59:37', 74, 'No. PS-5S-03-F02-74'),
(10, 75, 'OMPDC', 'iPhone 16 Pro Max Fully Paid', '', '', 1, 0, 1.00, 'pcs', '2025-08-20', 98000.00, 'Pedro Reyes', '0000-00-00', '0000-00-00', '2025-08-26 03:07:44', 74, 'No. PS-5S-03-F02-75'),
(11, 76, 'IT Office', 'Cisco ASR 1000 Series, high-performance router for internet connectivity and VPNs', '', '', 1, 0, 1.00, 'kg', '2025-04-20', 35674.00, 'Jude Dwight Oscar Jimenez', '0000-00-00', '0000-00-00', '2025-08-26 03:13:35', 94, 'No. PS-5S-03-F02-76'),
(12, 77, 'PNP', 'iPhone 16 Pro Max Fully Paid', '', '', 1, 0, 1.00, 'pcs', '2025-08-20', 98000.00, 'Mark Oliva', '0000-00-00', '0000-00-00', '2025-08-26 03:20:04', 95, 'No. PS-5S-03-F02-77'),
(13, 78, 'Finance Office', 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', '', '', 1, 0, 1.00, 'kg', '2025-04-20', 100000.00, 'Jude Dwight Oscar Jimenez', '0000-00-00', '0000-00-00', '2025-08-26 03:45:36', 96, 'No. PS-5S-03-F02-78'),
(14, 79, 'RHU', 'Faber Castle', '', '', 1, 0, 1.00, 'pcs', '2025-04-19', 120.00, 'Elton John Moises', '0000-00-00', '0000-00-00', '2025-08-26 03:52:17', 97, 'No. PS-5S-03-F02-79'),
(15, 80, 'Main Office', 'Laptop', '', '', 1, 0, 1.00, 'pcs', '2025-08-04', 45000.00, 'John Kenneth Litana', '0000-00-00', '0000-00-00', '2025-08-26 04:49:06', 98, 'No. PS-5S-03-F02-80'),
(16, 81, 'DSWD', '5kW gasoline-powered generator', '', '', 0, 0, 1.00, 'kg', '2025-04-20', 100000.00, 'John Kenneth Litana', '0000-00-00', '0000-00-00', '2025-08-26 05:06:49', 99, 'No. PS-5S-03-F02-81'),
(17, 82, 'Finance Office', '15 Liter gasoline generator', '', '', 0, 0, 1.00, 'kg', '2025-04-21', 15000.00, 'Maria Santos', '0000-00-00', '0000-00-00', '2025-08-26 05:14:32', 100, 'No. PS-5S-03-F02-82'),
(18, 83, 'OMAD Office', '10kW solar photovoltaic system with inverters, batteries, and mounting structures', '', '', 0, 0, 1.00, 'kg', '2025-04-20', 400000.00, 'Pedro Reyes', '0000-00-00', '0000-00-00', '2025-08-26 05:19:44', 101, 'No. PS-5S-03-F02-83'),
(19, 84, 'OMPDC', 'AMD Ryzen 7, 4 core processor', '', '', 0, 0, 1.00, 'pcs', '2025-04-06', 200000.00, 'Juan A. Dela Cruz', '0000-00-00', '0000-00-00', '2025-08-26 05:22:03', 102, 'No. PS-5S-03-F02-84'),
(20, 85, 'DILG', 'TP-Link JetStream 24-port Gigabit Ethernet Smart Switch', '', '', 0, 0, 1.00, 'kg', '2025-04-20', 15500.00, 'Maria Santos', '0000-00-00', '0000-00-00', '2025-08-26 05:23:34', 103, 'No. PS-5S-03-F02-85'),
(21, 86, 'OMAD Office', 'Steel filing cabinet with lock, 4 drawers', '', '', 0, 0, 1.00, 'kg', '2025-04-20', 50000.00, 'Pedro Reyes', '0000-00-00', '0000-00-00', '2025-08-26 05:26:48', 104, 'No. PS-5S-03-F02-86'),
(22, 87, 'DILG', 'Faber Castle', '', '', 1, 0, 1.00, 'pcs', '2025-04-19', 120.00, 'Maria Santos', '0000-00-00', '0000-00-00', '2025-08-26 06:59:50', 105, 'No. PS-5S-03-F02-87'),
(23, 88, 'OMASS', 'Hikvision 4MP IP camera with night vision', '', '', 1, 0, 1.00, 'kg', '2025-04-20', 100000.00, 'Maria Santos', '0000-00-00', '0000-00-00', '2025-08-26 07:08:02', 106, 'No. PS-5S-03-F02-88'),
(24, 89, 'Finance Office', 'TP-Link JetStream 24-port Gigabit Ethernet Smart Switch', '', '', 1, 0, 1.00, 'kg', '2025-04-20', 15500.00, 'Maria Santos', '0000-00-00', '0000-00-00', '2025-08-26 07:09:40', 107, 'No. PS-5S-03-F02-89'),
(25, 90, 'DILG', 'Steel filing cabinet with lock, 4 drawers', '', '', 1, 0, 1.00, 'kg', '2025-04-20', 50000.00, 'Maria Santos', '0000-00-00', '0000-00-00', '2025-08-26 07:15:53', 108, 'No. PS-5S-03-F02-90'),
(26, 91, 'IT Office', 'APC Back-UPS Pro 1500VA, Uninterruptible Power Supply', '', '', 1, 0, 1.00, 'kg', '2025-04-20', 100000.00, 'Pedro Reyes', '0000-00-00', '0000-00-00', '2025-08-26 07:18:50', 109, 'No. PS-5S-03-F02-91'),
(27, 92, 'DSWD', 'Faber Castle', '', '', 1, 0, 1.00, 'pcs', '2025-04-19', 120.00, 'Elton John Moises', '0000-00-00', '0000-00-00', '2025-08-26 07:30:26', 110, 'No. PS-5S-03-F02-92'),
(28, 93, 'IT Office', 'Toyota Hiace, 2023 Model, Refrigerated Van', '', '', 1, 0, 1.00, 'kg', '2025-04-20', 1500000.00, 'Juan A. Dela Cruz', '0000-00-00', '0000-00-00', '2025-08-26 08:09:19', 111, 'No. PS-5S-03-F02-93'),
(29, 94, 'DILG', 'Cisco ASR 1000 Series, high-performance router for internet connectivity and VPNs', '', '', 1, 0, 1.00, 'kg', '2025-04-20', 35674.00, 'Juan A. Dela Cruz', '0000-00-00', '0000-00-00', '2025-08-30 12:11:41', 112, 'No. PS-5S-03-F02-94'),
(30, 95, 'DSWD', '4 GB RAM 64 GB ROM', '', '', 1, 0, 1.00, 'pcs', '2025-07-01', 16000.00, '22', '0000-00-00', '0000-00-00', '2025-08-30 12:14:02', 113, 'No. PS-5S-03-F02-95');

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
(1, 'OMPDC', NULL),
(2, 'IT Office', NULL),
(3, 'OMASS', NULL),
(4, 'Supply Office', NULL),
(5, 'Finance Office', NULL),
(6, 'OMAD Office', NULL),
(7, 'RHU', NULL),
(8, 'PNP', NULL),
(9, 'Main Office', NULL),
(10, 'DILG', 'bi-building'),
(11, 'DSWD', NULL);

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
  `office_id` int(11) NOT NULL,
  `position_office_left` varchar(100) DEFAULT NULL,
  `position_office_right` varchar(100) DEFAULT NULL,
  `header_image` varchar(255) DEFAULT NULL,
  `entity_name` varchar(255) NOT NULL,
  `fund_cluster` varchar(100) NOT NULL,
  `par_no` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `par_form`
--

INSERT INTO `par_form` (`id`, `form_id`, `office_id`, `position_office_left`, `position_office_right`, `header_image`, `entity_name`, `fund_cluster`, `par_no`, `created_at`) VALUES
(1, 3, 10, 'ivan christoper millabas', 'mark jayson namia', '1755831255_Screenshot 2025-08-07 095005.png', 'Walton Loneza', 'fc-001', 'par-0001', '2025-08-22 02:43:09');

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
(3, 6, 11, '1755867841_Screenshot 2025-08-22 103403.png', 'v', '', '', 'ris-001', 'sAI-001', '2025-08-22', '', '', '2025-08-22', '', '', '2025-08-22', '0000-00-00', '', '2025-08-22 12:58:03', '', '', '2025-08-22', '', '', '2025-08-22');

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
(20, 'tube');

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
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
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
(8, 'marybrown', 'Ed Caluag', 'marybrown@example.com', 'password123', 'admin', 'active', '2025-04-03 04:45:50', NULL, NULL, 3, 'default_profile.png', 1800),
(9, 'peterwhite', 'Peter White', 'peterwhite@example.com', 'password123', 'admin', 'active', '2025-04-03 04:45:50', NULL, NULL, 2, 'default_profile.png', 1800),
(10, 'walt', 'Walton Loneza', 'waltielappy@gmail.com', '$2y$10$j5gUPrRPP0w0REknIdYrce.l5ZItK3c5WJXX3eC2OSQHtJ/YchHey', 'admin', 'active', '2025-04-04 01:31:30', NULL, NULL, 8, 'default_profile.png', 1800),
(12, 'walts', 'Walton Loneza', 'wjll@bicol-u.edu.ph', '$2y$10$tsOlFU9fjwi/DLRKdGkqL.aIXhKnlFxnNbA8ZoXeMbEiAhoe.sg/i', 'office_admin', 'inactive', '2025-04-07 14:13:29', NULL, NULL, 4, 'WIN_20240930_21_49_09_Pro.jpg', 1800),
(15, 'josh', 'Joshua Escano', 'jmfte@gmail.com', '$2y$10$IFmIX3WZ0YOxdf41EYzX6.IF51IKEg0bL0kmyORCI8dod42v.JeN6', 'office_user', 'inactive', '2025-04-09 00:49:07', '5a8b600a59a80f2bf5028ae258b3aae8', '2025-04-09 09:49:07', 4, 'josh.jpg', 1800),
(16, 'elton', 'Elton John B. Moises', 'ejbm@bicol-u.edu.ph', '$2y$10$Botz5wCa9biZrVT7IdEDau.uVBcw3ByoD75pX2BYYe7dtutigluY.', 'user', 'inactive', '2025-04-13 06:01:46', NULL, NULL, 9, 'profile_16_1749816479.jpg', 600),
(17, 'nami', 'Mark Jayson Namia', 'mjn@gmail.com', '$2y$10$2MIZlmP380wS0sj/cOfqbe20HkPz234S49cJEj2omrrTjBasHVqyO', 'admin', 'active', '2025-04-13 15:43:51', NULL, NULL, 9, 'default_profile.png', 1800),
(18, 'kiimon', 'Seynatour Kiimon', 'sk@gmail.com', '$2y$10$UGpyMRA79O2OKhKfZDEf5O9CyXkMFlhDsVpWdELXMYnMtdFIV0mSC', 'office_user', 'inactive', '2025-04-20 21:36:04', '6687598406441374aeffbc338a60f728', '2025-04-21 06:36:04', 4, 'default_profile.png', 1800),
(19, 'geely', 'Geely Mitsubishi', 'waltielappy123@gmail.com', '$2y$10$uVrAvdjC3GsGheiqmZSuF.r.oBbcHdOceQaV.E5LChrNNc/p20/FC', 'admin', 'active', '2025-06-24 06:54:34', NULL, NULL, 4, 'default_profile.png', 1800),
(21, 'miki', 'Miki Matsubara', 'mikimat@gmail.com', '$2y$10$hE2SgXv.RQahXlmHCv4MEeBfBLqkaY7/w9OVyZbnuy83LMMPrFDHa', 'user', 'active', '2025-06-24 07:01:30', NULL, NULL, 10, 'default_profile.png', 1800),
(22, 'Toyoki', 'Toyota Suzuki', 'toyoki@gmail.com', '$2y$10$dLNw4hqEJbKpB5Hc7Mmhr.AjH4dOiMIUg9BqGDkiLnnx3rw89KBfS', 'user', 'active', '2025-06-24 07:23:43', NULL, NULL, 10, 'default_profile.png', 1800),
(23, 'jet', 'Jet Kawasaki', 'kawaisaki@gmail.com', '$2y$10$JmxsfOnmMH/nJbxWUbuSqODWoHTMx8RZn/Zxg38EFpGlvhqCtP3b6', 'user', 'active', '2025-06-24 07:24:56', NULL, NULL, 10, 'default_profile.png', 1800);

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
  ADD KEY `idx_assets_office_status` (`office_id`,`status`),
  ADD KEY `idx_assets_status` (`status`);

--
-- Indexes for table `assets_archive`
--
ALTER TABLE `assets_archive`
  ADD PRIMARY KEY (`archive_id`);

--
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
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`),
  ADD KEY `form_id` (`form_id`),
  ADD KEY `office_id` (`office_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `assets_archive`
--
ALTER TABLE `assets_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `asset_requests`
--
ALTER TABLE `asset_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `doc_no`
--
ALTER TABLE `doc_no`
  MODIFY `doc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `forms`
--
ALTER TABLE `forms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `generated_reports`
--
ALTER TABLE `generated_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `ics_form`
--
ALTER TABLE `ics_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT for table `ics_items`
--
ALTER TABLE `ics_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `iirup_form`
--
ALTER TABLE `iirup_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `infrastructure_inventory`
--
ALTER TABLE `infrastructure_inventory`
  MODIFY `inventory_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `inventory_actions`
--
ALTER TABLE `inventory_actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mr_details`
--
ALTER TABLE `mr_details`
  MODIFY `mr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `par_form`
--
ALTER TABLE `par_form`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

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
  ADD CONSTRAINT `fk_assets_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  ADD CONSTRAINT `borrow_requests_ibfk_3` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`),
  ADD CONSTRAINT `fk_borrow_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrow_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_borrow_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `fk_employees_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `ics_form`
--
ALTER TABLE `ics_form`
  ADD CONSTRAINT `fk_ics_form_office` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`);

--
-- Constraints for table `ics_items`
--
ALTER TABLE `ics_items`
  ADD CONSTRAINT `fk_ics_items_asset` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`),
  ADD CONSTRAINT `ics_items_ibfk_1` FOREIGN KEY (`ics_id`) REFERENCES `ics_form` (`id`) ON DELETE CASCADE;

--
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
  ADD CONSTRAINT `par_form_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `par_form_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `report_templates`
--
ALTER TABLE `report_templates`
  ADD CONSTRAINT `fk_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `ris_form`
--
ALTER TABLE `ris_form`
  ADD CONSTRAINT `ris_form_ibfk_1` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`),
  ADD CONSTRAINT `ris_form_ibfk_2` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`);

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

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
