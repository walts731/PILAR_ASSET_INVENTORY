-- Simple SQL Backup for inventory_pilar
-- Generated at: 2025-09-23T19:47:09+02:00

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';

--
-- Structure for table `activity_log`
--
DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE `activity_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `activity` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `module` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`log_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `activity_log` (5 rows)
--
INSERT INTO `activity_log` (`log_id`,`user_id`,`activity`,`timestamp`,`module`) VALUES ('6','1','Added 20 IT Equipment to inventory','2025-04-02 10:05:00','Inventory Management');
INSERT INTO `activity_log` (`log_id`,`user_id`,`activity`,`timestamp`,`module`) VALUES ('7','1','Requested 15 Office Supplies','2025-04-02 11:10:00','Inventory Management');
INSERT INTO `activity_log` (`log_id`,`user_id`,`activity`,`timestamp`,`module`) VALUES ('8','1','Borrowed 5 IT Equipment','2025-04-02 12:15:00','Inventory Management');
INSERT INTO `activity_log` (`log_id`,`user_id`,`activity`,`timestamp`,`module`) VALUES ('9','1','Transferred 10 Office Supplies to Admin','2025-04-02 13:20:00','Inventory Management');
INSERT INTO `activity_log` (`log_id`,`user_id`,`activity`,`timestamp`,`module`) VALUES ('10','1','Added 30 IT Equipment to inventory','2025-04-02 14:25:00','Inventory Management');

--
-- Structure for table `archives`
--
DROP TABLE IF EXISTS `archives`;
CREATE TABLE `archives` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `filter_status` varchar(50) DEFAULT NULL,
  `filter_office` varchar(50) DEFAULT NULL,
  `filter_category` varchar(50) DEFAULT NULL,
  `filter_start_date` date DEFAULT NULL,
  `filter_end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `archives` (8 rows)
--
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('1','12','Export CSV','','4','','0000-00-00','0000-00-00','2025-04-16 17:39:18','asset_report_20250416_113918.csv');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('2','12','Export CSV','','4','','0000-00-00','0000-00-00','2025-04-21 19:55:23','asset_report_20250421_135523.csv');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('3','1','Export PDF','',NULL,NULL,'0000-00-00','0000-00-00','2025-04-21 19:58:08','assets_report_20250421_135808.pdf');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('4','1','Export CSV','','','','0000-00-00','0000-00-00','2025-04-21 19:58:09','asset_report_20250421_135809.csv');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('5','1','Export PDF','',NULL,NULL,'0000-00-00','0000-00-00','2025-04-21 19:58:21','assets_report_20250421_135821.pdf');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('6','12','Export PDF','',NULL,NULL,'0000-00-00','0000-00-00','2025-04-21 19:59:41','assets_report_20250421_135941.pdf');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('7','12','Export CSV','','4','','0000-00-00','0000-00-00','2025-04-21 20:05:58','asset_report_20250421_140558.csv');
INSERT INTO `archives` (`id`,`user_id`,`action_type`,`filter_status`,`filter_office`,`filter_category`,`filter_start_date`,`filter_end_date`,`created_at`,`file_name`) VALUES ('8','12','Export PDF','',NULL,NULL,'0000-00-00','0000-00-00','2025-04-21 20:06:05','assets_report_20250421_140605.pdf');

--
-- Structure for table `asset_requests`
--
DROP TABLE IF EXISTS `asset_requests`;
CREATE TABLE `asset_requests` (
  `request_id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_name` varchar(2555) NOT NULL,
  `user_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(11) NOT NULL,
  `unit` varchar(50) NOT NULL,
  `description` text NOT NULL,
  `office_id` int(11) NOT NULL,
  PRIMARY KEY (`request_id`),
  KEY `asset_id` (`asset_name`(768)),
  KEY `user_id` (`user_id`),
  KEY `fk_office` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `asset_requests` (7 rows)
--
INSERT INTO `asset_requests` (`request_id`,`asset_name`,`user_id`,`status`,`request_date`,`quantity`,`unit`,`description`,`office_id`) VALUES ('1','1','2','pending','2025-04-03 12:46:35','10','pieces','Office chairs for new hires','1');
INSERT INTO `asset_requests` (`request_id`,`asset_name`,`user_id`,`status`,`request_date`,`quantity`,`unit`,`description`,`office_id`) VALUES ('2','2','3','approved','2025-04-03 12:46:35','5','boxes','Laptop docking stations','2');
INSERT INTO `asset_requests` (`request_id`,`asset_name`,`user_id`,`status`,`request_date`,`quantity`,`unit`,`description`,`office_id`) VALUES ('3','3','4','rejected','2025-04-03 12:46:35','3','units','Projector for conference room','1');
INSERT INTO `asset_requests` (`request_id`,`asset_name`,`user_id`,`status`,`request_date`,`quantity`,`unit`,`description`,`office_id`) VALUES ('4','4','5','approved','2025-04-03 12:46:35','2','sets','Conference table sets for meeting room','3');
INSERT INTO `asset_requests` (`request_id`,`asset_name`,`user_id`,`status`,`request_date`,`quantity`,`unit`,`description`,`office_id`) VALUES ('5','5','6','rejected','2025-04-03 12:46:35','15','pieces','Keyboard and mouse sets','2');
INSERT INTO `asset_requests` (`request_id`,`asset_name`,`user_id`,`status`,`request_date`,`quantity`,`unit`,`description`,`office_id`) VALUES ('7','Mouse','12','pending','2025-04-20 14:08:40','3','pcs','For my office','4');
INSERT INTO `asset_requests` (`request_id`,`asset_name`,`user_id`,`status`,`request_date`,`quantity`,`unit`,`description`,`office_id`) VALUES ('8','Van','12','pending','2025-04-21 14:04:59','1','unit','For our service vehicle.','4');

--
-- Structure for table `assets`
--
DROP TABLE IF EXISTS `assets`;
CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `inventory_tag` varchar(255) DEFAULT NULL,
  `additional_images` text DEFAULT NULL COMMENT 'JSON array storing paths to up to 4 additional images for the asset',
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `fk_assets_employee` (`employee_id`),
  KEY `idx_assets_office_status` (`office_id`,`status`),
  KEY `idx_assets_status` (`status`),
  KEY `idx_assets_ics_id` (`ics_id`),
  KEY `idx_assets_asset_new_id` (`asset_new_id`),
  KEY `idx_assets_par_id` (`par_id`),
  CONSTRAINT `assets_ibfk_1` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  CONSTRAINT `assets_ibfk_2` FOREIGN KEY (`category`) REFERENCES `categories` (`id`),
  CONSTRAINT `fk_assets_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_assets_ics` FOREIGN KEY (`ics_id`) REFERENCES `ics_form` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_assets_par` FOREIGN KEY (`par_id`) REFERENCES `par_form` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `assets` (27 rows)
--
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('1','Office Table – Wooden','2','Office Table – Wooden','1','0','pcs','unserviceable','2025-09-19','4','1','1','2025-09-22 18:04:13','3500.00','1.png','asset','','','','MR-2025-00001','','','17',NULL,'1','No. PS-5S-03-F02-01','[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('3','Mouse','2','Mouse','1','0','pcs','available','2025-09-19','4','1','0','2025-09-22 18:04:13','350.00','3.png','asset','asset_3_1758293767.jpg','','','MR-2025-00003','','','18',NULL,'2','No. PS-5S-03-F02-03','[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('5','Printer Epson','1','Printer Epson','1','0','pcs','unserviceable','2025-09-19','4','1','1','2025-09-22 19:32:35','4593.00','5.png','asset','','','','MR-2025-00005','','','19',NULL,'3','No. PS-5S-03-F02-05','[\"asset_5_1758540755_0.jpg\"]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('6','Printer Epson','1','Printer Epson','1','0','pcs','available','2025-09-19','4','1','0','2025-09-22 18:04:13','4593.00','6.png','asset','','','','MR-2025-00006','','','19',NULL,'3','No. PS-5S-03-F02-06','[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('15','Blue Chair','2','Uratex','3','0','pcs','available','2025-04-04','4',NULL,'0','2025-09-22 18:04:13','30000.00','QR.png','asset',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('16','eagle','1','eagle','1','0','box','available','2025-09-19','49',NULL,'0','2025-09-22 18:04:13','345.00','21.png','asset',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('17','Van','1','Van','6','0','unit','available','2025-09-18','49',NULL,'0','2025-09-22 18:04:13','49999.99','54.png','asset',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('19','Cellphone','1','Cellphone','1','0','pcs','unserviceable','2025-09-21','4','1','1','2025-09-22 18:04:13','5678.00','19.png','asset','','','','MR-2025-00019','','','25',NULL,'8','No. PS-5S-03-F02-19','[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('20','Cellphone','1','Cellphone','1','0','pcs','unserviceable','2025-09-21','4','2','1','2025-09-22 18:04:13','5678.00','20.png','asset','','','','MR-2025-00020','','','25',NULL,'8','No. PS-5S-03-F02-20','[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('21','Ballpen',NULL,'Ballpen','2','2','box','available','2025-09-21','3',NULL,'0','2025-09-22 18:04:13','345.00','','consumable','','','','','','',NULL,NULL,NULL,'','[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('22','Ballpen',NULL,'Ballpen','2','2','box','available','2025-09-21','3',NULL,'0','2025-09-22 18:04:13','234.01','','consumable','','','','','','',NULL,NULL,NULL,'','[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('23','Dell Unit','1','Dell Unit','1','0','unit','unserviceable','2025-09-21','4','1','1','2025-09-22 18:04:13','99000.00','23.png','asset','','','',NULL,'','',NULL,NULL,'9',NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('24','Dell Unit',NULL,'Dell Unit','1','0','unit','available','2025-09-21','4',NULL,'0','2025-09-22 18:04:13','99000.00','24.png','asset','','','',NULL,'','',NULL,NULL,'9',NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('27','Jetski','1','Jetski','1','0','unit','unserviceable','2025-09-21','4','2','1','2025-09-22 18:04:13','96780.00','27.png','asset','','','',NULL,'','',NULL,NULL,'12',NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('28','Jetski',NULL,'Jetski','1','0','unit','available','2025-09-21','4',NULL,'0','2025-09-22 18:04:13','96780.00','28.png','asset','','','',NULL,'','',NULL,NULL,'12',NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('29','HIlux',NULL,'HIlux','1','0','roll','available','2025-09-21','4',NULL,'0','2025-09-22 18:04:13','1000000.00','29.png','asset','','','',NULL,'','',NULL,NULL,'13',NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('30','Car',NULL,'Car','1','0','unit','unserviceable','2025-09-21','4',NULL,'1','2025-09-22 18:04:13','4500000.00','30.png','asset','','','',NULL,'','',NULL,NULL,'14',NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('31','Mio Soul i','1','Mio Soul i','1','0','unit','available','2025-09-21','4','2','0','2025-09-22 18:04:13','75000.00','31.png','asset','','','',NULL,'','',NULL,'44','15',NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('32','Honda','1','Honda Click 125','1','0','unit','unserviceable','0000-00-00','7','2','1','2025-09-22 18:04:13','75000.00','32.png','asset','','','','','','',NULL,'45','16','No. PS-5S-03-F02-32','[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('33','Hilux Van','1','Hilux Van','1','0','unit','unserviceable','2025-09-21','4','3','1','2025-09-22 18:13:37','7600000.00','33.png','asset','','','','MR-2025-00033','','',NULL,'46','17','No. PS-5S-03-F02-33','[\"asset_33_1758536017_0.jpg\"]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('34','Hilux van black','2','Hilux van black','1','0','unit','unserviceable','2025-09-22','4','8','1','2025-09-22 18:04:13','2300000.00','34.png','asset','','','EQP-001','MR-2025-00034','','',NULL,'47','18','No. PS-5S-03-F02-34','[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('35','Lenovo AMD Ryzen 7',NULL,'Lenovo AMD Ryzen 7','1','0','unit','unserviceable','2025-09-22','4',NULL,'1','2025-09-22 18:04:13','75000.00','35.png','asset','','','',NULL,'','',NULL,'49','19',NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('36','Lenovo AMD Ryzen 7',NULL,'Lenovo AMD Ryzen 7','1','0','unit','available','2025-09-22','4',NULL,'0','2025-09-22 18:04:13','75000.00','36.png','asset','','','',NULL,'','',NULL,'49','19',NULL,'[]');
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('40','Computer','1','Computer','1','0','unit','unserviceable','2025-09-22','4','2','1','2025-09-23 18:17:04','36500.00','40.png','asset','','','','MR-2025-00040','','','27',NULL,'21','No. PS-5S-03-F02-40',NULL);
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('41','Computer','1','Computer','1','0','unit','unserviceable','2025-09-22','4','2','0','2025-09-23 18:17:39','36500.00','41.png','asset','','','','MR-2025-00041','','','27',NULL,'21','No. PS-5S-03-F02-41',NULL);
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('42','ballpen',NULL,'ballpen','100','100','pcs','available','2025-09-23','3',NULL,'0','2025-09-23 11:42:36','7.50','','consumable','','','','','','',NULL,NULL,NULL,'',NULL);
INSERT INTO `assets` (`id`,`asset_name`,`category`,`description`,`quantity`,`added_stock`,`unit`,`status`,`acquisition_date`,`office_id`,`employee_id`,`red_tagged`,`last_updated`,`value`,`qr_code`,`type`,`image`,`serial_no`,`code`,`property_no`,`model`,`brand`,`ics_id`,`par_id`,`asset_new_id`,`inventory_tag`,`additional_images`) VALUES ('43','ballpen panda',NULL,'ballpen panda','20','20','pcs','available','2025-09-23','3',NULL,'0','2025-09-23 14:03:11','7.00','','consumable','','','','1','','',NULL,NULL,NULL,'',NULL);

--
-- Structure for table `assets_archive`
--
DROP TABLE IF EXISTS `assets_archive`;
CREATE TABLE `assets_archive` (
  `archive_id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`archive_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `assets_new`
--
DROP TABLE IF EXISTS `assets_new`;
CREATE TABLE `assets_new` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `description` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit_cost` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit` varchar(50) NOT NULL,
  `office_id` int(11) NOT NULL DEFAULT 0,
  `par_id` int(11) DEFAULT NULL,
  `ics_id` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_description` (`description`),
  KEY `idx_assets_new_office_id` (`office_id`),
  KEY `idx_assets_new_par_id` (`par_id`),
  CONSTRAINT `fk_assets_new_par` FOREIGN KEY (`par_id`) REFERENCES `par_form` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Data for table `assets_new` (21 rows)
--
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('1','Office Table – Wooden','1','3500.00','pcs','4',NULL,NULL,'2025-09-19 19:20:38');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('2','Mouse','1','350.00','pcs','4',NULL,NULL,'2025-09-19 19:36:01');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('3','Printer Epson','2','4593.00','pcs','4',NULL,'19','2025-09-19 20:04:07');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('4','Air Conditioner (2.5 HP, LG Inverter)','0','38000.00','unit','4',NULL,'20','2025-09-20 04:20:32');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('5','Desktop Computer – Intel i5, 8GB RAM, 256GB SSD','0','4573.98','pcs','4',NULL,'22','2025-09-20 04:43:50');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('6','Office Table – Wooden','0','3500.00','pcs','49',NULL,'23','2025-09-20 14:30:22');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('7','Office Table – Wooden','0','3500.00','pcs','49',NULL,'24','2025-09-20 14:30:54');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('8','Cellphone','2','5678.00','pcs','4',NULL,'25','2025-09-21 13:11:37');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('9','Dell Unit','2','99000.00','unit','4',NULL,NULL,'2025-09-21 18:14:32');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('10','Ergonomic Office Chair','2','51000.00','unit','4',NULL,'39','2025-09-21 18:21:59');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('11','Jetski','2','96780.00','unit','4',NULL,'40','2025-09-21 18:22:37');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('12','Jetski','2','96780.00','unit','4',NULL,NULL,'2025-09-21 18:25:03');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('13','HIlux','1','1000000.00','roll','4',NULL,NULL,'2025-09-21 18:28:12');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('14','Car','1','4500000.00','unit','4',NULL,NULL,'2025-09-21 18:33:35');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('15','Mio Soul i','1','75000.00','unit','4','44',NULL,'2025-09-21 18:40:55');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('16','Honda Click 125','1','75000.00','unit','4','45',NULL,'2025-09-21 18:51:18');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('17','Hilux Van','1','7600000.00','unit','4','46',NULL,'2025-09-22 02:56:51');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('18','Hilux van black','1','2300000.00','unit','4','47',NULL,'2025-09-22 03:04:09');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('19','Lenovo AMD Ryzen 7','2','75000.00','unit','4','49',NULL,'2025-09-22 03:07:07');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('20','Stylus','0','450.00','unit','4',NULL,'26','2025-09-22 17:31:24');
INSERT INTO `assets_new` (`id`,`description`,`quantity`,`unit_cost`,`unit`,`office_id`,`par_id`,`ics_id`,`date_created`) VALUES ('21','Computer','2','36500.00','unit','4',NULL,'27','2025-09-22 17:44:10');

--
-- Structure for table `audit_logs`
--
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `module` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `affected_table` varchar(50) DEFAULT NULL,
  `affected_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_module` (`module`),
  KEY `idx_action` (`action`),
  KEY `idx_created_at` (`created_at`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `audit_logs` (29 rows)
--
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('1','17','Mark Jayson Namia','CREATE','ICS Form','Created new ICS form: ICS-2025-0023 - INVENTORY (Destination: Supply Office)','ics_form','27','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-22 20:44:10');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('2','17','Mark Jayson Namia','CREATE','ICS Items','Added item to ICS ICS-2025-0023: Computer (Qty: 2, Unit Cost: ₱36,500.00, Total: ₱73,000.00)','ics_items','13','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-22 20:44:10');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('3','17','Mark Jayson Namia','LOGOUT','Authentication','User logged out successfully',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-22 21:40:03');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('4','17','nami','LOGIN','Authentication','User \'nami\' logged in successfully (Role: admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-22 21:40:11');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('5','17','Mark Jayson Namia','ACTIVATE','User Management','ACTIVATE user: josh (Full Name: Joshua Escano, Status changed to: active)','users','15','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-22 21:40:29');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('6','17','Mark Jayson Namia','BULK_PRINT','Bulk Operations','Bulk PRINT: 2 items (MR Records)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-22 21:40:54');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('7','17','nami','LOGIN','Authentication','User \'nami\' logged in successfully (Role: admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-22 22:32:08');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('8','17','nami','LOGIN','Authentication','User \'nami\' logged in successfully (Role: admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 09:30:41');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('9','17','nami','LOGIN','Authentication','User \'nami\' logged in successfully (Role: admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 10:25:39');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('10','17','Mark Jayson Namia','BULK_PRINT','Bulk Operations','Bulk PRINT: 3 items (MR Records)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 11:07:50');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('11','17','Mark Jayson Namia','BULK_PRINT','Bulk Operations','Bulk PRINT: 2 items (Red Tags)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 11:31:34');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('12','17','Mark Jayson Namia','GENERATE','Reports','Generated ICS PDF report with filters: ICS: ICS-2025-0023, Entity: INVENTORY',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 11:34:15');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('13','17','nami','LOGIN','Authentication','User \'nami\' logged in successfully (Role: admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 13:31:56');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('14','17','nami','LOGIN','Authentication','User \'nami\' logged in successfully (Role: admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 17:09:47');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('15','17','nami','LOGIN','Authentication','User \'nami\' logged in successfully (Role: admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 18:15:18');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('16','17','Mark Jayson Namia','CREATE','Red Tags','Created Red Tag: PS-5S-03-F01-01-05 for asset: Computer (Reason: Unnecessary, Action: For Disposal)','red_tags','5','::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 18:17:04');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('17','17','nami','LOGIN','Authentication','User \'nami\' logged in successfully (Role: admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 19:01:56');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('18','17','nami','LOGIN','Authentication','User \'nami\' logged in successfully (Role: admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-23 22:39:55');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('19','17','Mark Jayson Namia','LOGOUT','Authentication','User logged out successfully',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 00:18:56');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('20','1','ompdc','LOGIN','Authentication','User \'ompdc\' logged in successfully (Role: super_admin)',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 00:19:06');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('21','1','Mark Jayson Namia','BACKUP_FAILED','System','Manual backup failed: mysqldump failed. Return code: 1','backups',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 01:02:38');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('22','1','Mark Jayson Namia','BACKUP_FAILED','System','Manual backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','backups',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 01:05:45');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('23','1','Mark Jayson Namia','BACKUP_FAILED','System','Manual backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','backups',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 01:08:21');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('24','1','Mark Jayson Namia','BACKUP_FAILED','System','Manual backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','backups',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 01:09:21');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('25','1','Mark Jayson Namia','BACKUP_FAILED','System','Manual backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','backups',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 01:15:08');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('26','1','Mark Jayson Namia','BACKUP_DOWNLOAD','System','Downloaded simple SQL backup: inventory_pilar_simple_backup_20250923_191520.sql',NULL,NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 01:15:20');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('27','1','Mark Jayson Namia','BACKUP_FAILED','System','Manual cloud backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','backups',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 01:34:37');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('28','1','Mark Jayson Namia','BACKUP_FAILED','System','Manual cloud backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','backups',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 01:36:02');
INSERT INTO `audit_logs` (`id`,`user_id`,`username`,`action`,`module`,`details`,`affected_table`,`affected_id`,`ip_address`,`user_agent`,`created_at`) VALUES ('29','1','Mark Jayson Namia','BACKUP_FAILED','System','Manual cloud backup failed: mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','backups',NULL,'::1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36','2025-09-24 01:41:25');

--
-- Structure for table `backups`
--
DROP TABLE IF EXISTS `backups`;
CREATE TABLE `backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `path` text NOT NULL,
  `size_bytes` bigint(20) DEFAULT NULL,
  `storage` enum('local','cloud','both') DEFAULT 'local',
  `status` enum('success','failed') DEFAULT 'success',
  `triggered_by` enum('manual','scheduled') DEFAULT 'manual',
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `backups` (11 rows)
--
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('1','inventory_pilar_backup_20250923_184748.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_184748.sql','0','local','failed','manual','mysqldump failed. Return code: 1','2025-09-24 00:47:48');
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('2','inventory_pilar_backup_20250923_184752.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_184752.sql','0','local','failed','manual','mysqldump failed. Return code: 1','2025-09-24 00:47:52');
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('3','inventory_pilar_backup_20250923_185747.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_185747.sql','0','local','failed','manual','mysqldump failed. Return code: 1','2025-09-24 00:57:47');
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('4','inventory_pilar_backup_20250923_190238.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_190238.sql','0','local','failed','manual','mysqldump failed. Return code: 1','2025-09-24 01:02:38');
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('5','inventory_pilar_backup_20250923_190545.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_190545.sql',NULL,'local','failed','manual','mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','2025-09-24 01:05:45');
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('6','inventory_pilar_backup_20250923_190821.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_190821.sql',NULL,'local','failed','manual','mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','2025-09-24 01:08:21');
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('7','inventory_pilar_backup_20250923_190921.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_190921.sql',NULL,'local','failed','manual','mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','2025-09-24 01:09:21');
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('8','inventory_pilar_backup_20250923_191508.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_191508.sql',NULL,'local','failed','manual','mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','2025-09-24 01:15:08');
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('9','inventory_pilar_backup_20250923_193437.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_193437.sql',NULL,'local','failed','manual','mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','2025-09-24 01:34:37');
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('10','inventory_pilar_backup_20250923_193602.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_193602.sql',NULL,'local','failed','manual','mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','2025-09-24 01:36:02');
INSERT INTO `backups` (`id`,`filename`,`path`,`size_bytes`,`storage`,`status`,`triggered_by`,`error_message`,`created_at`) VALUES ('11','inventory_pilar_backup_20250923_194125.sql','C:\\xampp\\htdocs\\PILAR_ASSET_INVENTORY\\generated_backups\\inventory_pilar_backup_20250923_194125.sql',NULL,'local','failed','manual','mysqldump failed. Return code: 1; Output: The system cannot find the path specified.','2025-09-24 01:41:25');

--
-- Structure for table `borrow_requests`
--
DROP TABLE IF EXISTS `borrow_requests`;
CREATE TABLE `borrow_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY (`id`),
  KEY `idx_borrow_requests_user_id` (`user_id`),
  KEY `idx_borrow_requests_asset_id` (`asset_id`),
  KEY `idx_borrow_requests_office_id` (`office_id`),
  KEY `idx_borrow_requests_status` (`status`),
  KEY `idx_borrow_requests_requested_at` (`requested_at`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `borrow_requests` (14 rows)
--
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('2','19','13','4','returned','2025-07-12 15:40:35','2025-07-14 21:13:26','NEVER BEEN USED','2025-07-14 21:13:47','1','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('3','19','14','4','pending','2025-07-12 15:40:35',NULL,NULL,NULL,'1','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('4','19','2','9','returned','2025-07-12 15:42:36','2025-07-14 09:54:28','slightly used','2025-07-14 19:55:48','0','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('5','17','2','9','pending','2025-07-13 15:15:18',NULL,NULL,NULL,'1','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('6','17','2','9','returned','2025-07-13 15:24:25','2025-07-13 20:45:54','All goods','2025-07-13 20:58:56','1','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('7','17','2','9','returned','2025-07-14 04:23:59','2025-07-14 21:00:24','Good condition','2025-07-14 21:02:14','0','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('8','17','13','4','returned','2025-07-14 14:49:24','2025-07-14 19:50:05','Neve used','2025-07-14 21:05:50','0','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('9','17','3','2','pending','2025-08-20 08:09:14',NULL,NULL,NULL,'5','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('10','17','64','9','pending','2025-08-20 08:17:57',NULL,NULL,NULL,'3','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('11','12','64','9','pending','2025-08-20 08:24:23',NULL,NULL,NULL,'3','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('12','17','64','9','pending','2025-08-29 15:24:45',NULL,NULL,NULL,'1','2025-08-30 11:09:31','2025-08-30 11:09:31');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('13','17','3','4','pending','2025-09-22 10:01:54',NULL,NULL,NULL,'1','2025-09-22 16:01:54','2025-09-22 16:01:54');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('14','17','5','4','pending','2025-09-22 10:01:54',NULL,NULL,NULL,'1','2025-09-22 16:01:54','2025-09-22 16:01:54');
INSERT INTO `borrow_requests` (`id`,`user_id`,`asset_id`,`office_id`,`status`,`requested_at`,`approved_at`,`return_remarks`,`returned_at`,`quantity`,`created_at`,`updated_at`) VALUES ('15','17','15','4','pending','2025-09-22 10:01:54',NULL,NULL,NULL,'3','2025-09-22 16:01:54','2025-09-22 16:01:54');

--
-- Structure for table `categories`
--
DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(255) NOT NULL,
  `type` enum('asset','consumables') NOT NULL DEFAULT 'asset',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `categories` (8 rows)
--
INSERT INTO `categories` (`id`,`category_name`,`type`) VALUES ('1','Electronics','asset');
INSERT INTO `categories` (`id`,`category_name`,`type`) VALUES ('2','Furniture','asset');
INSERT INTO `categories` (`id`,`category_name`,`type`) VALUES ('3','Office Supplies','consumables');
INSERT INTO `categories` (`id`,`category_name`,`type`) VALUES ('4','Vehicle','asset');
INSERT INTO `categories` (`id`,`category_name`,`type`) VALUES ('5','Power Equipment','asset');
INSERT INTO `categories` (`id`,`category_name`,`type`) VALUES ('6','IT Equipment','asset');
INSERT INTO `categories` (`id`,`category_name`,`type`) VALUES ('7','Security Equipment','asset');
INSERT INTO `categories` (`id`,`category_name`,`type`) VALUES ('15','Categories','asset');

--
-- Structure for table `category`
--
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `consumption_log`
--
DROP TABLE IF EXISTS `consumption_log`;
CREATE TABLE `consumption_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `quantity_consumed` int(11) NOT NULL,
  `recipient_user_id` int(11) NOT NULL,
  `dispensed_by_user_id` int(11) NOT NULL,
  `consumption_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `asset_id` (`asset_id`),
  KEY `recipient_user_id` (`recipient_user_id`),
  KEY `dispensed_by_user_id` (`dispensed_by_user_id`),
  KEY `fk_consumption_log_office` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `consumption_log` (7 rows)
--
INSERT INTO `consumption_log` (`id`,`asset_id`,`office_id`,`quantity_consumed`,`recipient_user_id`,`dispensed_by_user_id`,`consumption_date`,`remarks`) VALUES ('4','18','0','1','24','24','2024-09-04 21:32:57','');
INSERT INTO `consumption_log` (`id`,`asset_id`,`office_id`,`quantity_consumed`,`recipient_user_id`,`dispensed_by_user_id`,`consumption_date`,`remarks`) VALUES ('5','18','0','1','24','24','2025-09-14 21:33:10','');
INSERT INTO `consumption_log` (`id`,`asset_id`,`office_id`,`quantity_consumed`,`recipient_user_id`,`dispensed_by_user_id`,`consumption_date`,`remarks`) VALUES ('6','18','0','1','24','24','2025-09-14 21:36:02','');
INSERT INTO `consumption_log` (`id`,`asset_id`,`office_id`,`quantity_consumed`,`recipient_user_id`,`dispensed_by_user_id`,`consumption_date`,`remarks`) VALUES ('7','18','0','1','24','24','2025-09-14 21:36:41','');
INSERT INTO `consumption_log` (`id`,`asset_id`,`office_id`,`quantity_consumed`,`recipient_user_id`,`dispensed_by_user_id`,`consumption_date`,`remarks`) VALUES ('8','14','3','1','24','24','2025-09-14 22:48:21','');
INSERT INTO `consumption_log` (`id`,`asset_id`,`office_id`,`quantity_consumed`,`recipient_user_id`,`dispensed_by_user_id`,`consumption_date`,`remarks`) VALUES ('9','15','3','1','24','24','2024-09-16 09:50:41','');
INSERT INTO `consumption_log` (`id`,`asset_id`,`office_id`,`quantity_consumed`,`recipient_user_id`,`dispensed_by_user_id`,`consumption_date`,`remarks`) VALUES ('10','18','3','1','24','24','2025-09-15 09:51:05','');

--
-- Structure for table `doc_no`
--
DROP TABLE IF EXISTS `doc_no`;
CREATE TABLE `doc_no` (
  `doc_id` int(11) NOT NULL AUTO_INCREMENT,
  `document_number` varchar(50) NOT NULL,
  PRIMARY KEY (`doc_id`),
  UNIQUE KEY `document_number` (`document_number`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `doc_no` (5 rows)
--
INSERT INTO `doc_no` (`doc_id`,`document_number`) VALUES ('1','GS21A-003');
INSERT INTO `doc_no` (`doc_id`,`document_number`) VALUES ('2','GS21A-005');
INSERT INTO `doc_no` (`doc_id`,`document_number`) VALUES ('3','GS22A-001');
INSERT INTO `doc_no` (`doc_id`,`document_number`) VALUES ('4','GSP-2024-08-0001-1');
INSERT INTO `doc_no` (`doc_id`,`document_number`) VALUES ('5','MO21A-012');

--
-- Structure for table `employees`
--
DROP TABLE IF EXISTS `employees`;
CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_no` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `status` enum('permanent','casual','contractual','job_order','probationary','resigned','retired') NOT NULL,
  `clearance_status` enum('cleared','uncleared') DEFAULT 'uncleared',
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `office_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `employee_no` (`employee_no`),
  KEY `fk_employees_office` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `employees` (4 rows)
--
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('1','EMP0001','Juan A. Dela Cruz','permanent','uncleared','2025-08-31 22:25:29','emp_68b45b59bbe19.jpg','2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('2','EMP0002','Maria Santos','permanent','uncleared','2025-09-01 09:39:29','emp_68b4f95154506.jpg','7');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('3','EMP0003','Pedro Reyes','contractual','uncleared','2025-09-01 09:50:43','emp_68b4fbf33d3ad.jpg','2');
INSERT INTO `employees` (`employee_id`,`employee_no`,`name`,`status`,`clearance_status`,`date_added`,`image`,`office_id`) VALUES ('8','EMP0004','Ryan Bang','permanent','uncleared','2025-09-20 20:03:27',NULL,'7');

--
-- Structure for table `forms`
--
DROP TABLE IF EXISTS `forms`;
CREATE TABLE `forms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_title` varchar(255) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `forms` (4 rows)
--
INSERT INTO `forms` (`id`,`form_title`,`category`,`file_path`,`created_at`) VALUES ('3','PROPERTY ACKNOWLEDGEMENT RECEIPT','PAR','par_form.php','2025-08-05 10:17:00');
INSERT INTO `forms` (`id`,`form_title`,`category`,`file_path`,`created_at`) VALUES ('4','INVENTORY CUSTODIAN SLIP','ICS','ics_form.php','2025-08-05 10:17:00');
INSERT INTO `forms` (`id`,`form_title`,`category`,`file_path`,`created_at`) VALUES ('6','REQUISITION & INVENTORY SLIP','RIS','ris_form.php','2025-08-05 10:17:00');
INSERT INTO `forms` (`id`,`form_title`,`category`,`file_path`,`created_at`) VALUES ('7','INVENTORY & INSPECTION REPORT OF UNSERVICEABLE PROPERTY','IIRUP','iirup_form.php','2025-08-12 20:53:40');

--
-- Structure for table `generated_reports`
--
DROP TABLE IF EXISTS `generated_reports`;
CREATE TABLE `generated_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `template_id` int(11) NOT NULL,
  `generated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `generated_reports` (87 rows)
--
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('3','17','0','Inventory_Report_20250709_082630.pdf','0','2025-07-09 11:26:30');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('4','17','0','Inventory_Report_20250709_083052.pdf','3','2025-07-09 11:30:53');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('5','17','0','Inventory_Report_20250709_083104.pdf','2','2025-07-09 11:31:05');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('6','17','0','Inventory_Report_20250709_083747.pdf','2','2025-07-09 11:37:48');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('7','17','0','Inventory_Report_20250709_083756.pdf','5','2025-07-09 11:37:57');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('8','17','0','Inventory_Report_20250709_084028.pdf','5','2025-07-09 11:40:29');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('9','17','0','Inventory_Report_20250709_084118.pdf','5','2025-07-09 11:41:19');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('10','17','0','Inventory_Report_20250709_084201.pdf','5','2025-07-09 11:42:02');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('11','17','0','Inventory_Report_20250709_084352.pdf','2','2025-07-09 11:43:52');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('12','17','0','Inventory_Report_20250709_084609.pdf','5','2025-07-09 11:46:10');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('13','17','0','Inventory_Report_20250709_143428.pdf','3','2025-07-09 17:34:29');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('14','17','0','Inventory_Report_20250709_143918.pdf','3','2025-07-09 17:39:19');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('15','17','0','Inventory_Report_20250709_144102.pdf','28','2025-07-09 17:41:02');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('16','17','0','Inventory_Report_20250709_144323.pdf','28','2025-07-09 17:43:23');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('17','17','0','Inventory_Report_20250709_144400.pdf','28','2025-07-09 17:44:00');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('18','17','0','Inventory_Report_20250709_144416.pdf','27','2025-07-09 17:44:16');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('19','17','0','Inventory_Report_20250709_144544.pdf','27','2025-07-09 17:45:44');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('20','17','0','Inventory_Report_20250709_144700.pdf','27','2025-07-09 17:47:00');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('21','17','0','Inventory_Report_20250709_145032.pdf','26','2025-07-09 17:50:36');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('22','17','0','Inventory_Report_20250709_150358.pdf','26','2025-07-09 18:03:59');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('23','17','0','Inventory_Report_20250709_151145.pdf','26','2025-07-09 18:11:45');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('24','17','0','Inventory_Report_20250709_152847.pdf','28','2025-07-09 18:28:50');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('25','17','0','Inventory_Report_20250709_152941.pdf','29','2025-07-09 18:29:41');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('26','17','0','Inventory_Report_20250709_153917.pdf','2','2025-07-09 18:39:17');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('27','17','0','Inventory_Report_20250709_153924.pdf','30','2025-07-09 18:39:24');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('28','17','0','Inventory_Report_20250710_035858.pdf','30','2025-07-10 06:59:03');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('29','17','0','Inventory_Report_20250710_144534.pdf','30','2025-07-10 17:45:38');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('30','17','0','Inventory_Report_20250711_091238.pdf','33','2025-07-11 12:12:43');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('31','17','0','Inventory_Report_20250711_091547.pdf','33','2025-07-11 12:15:48');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('32','17','0','Inventory_Report_20250712_075945.pdf','33','2025-07-12 10:59:50');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('33','17','0','Inventory_Report_20250712_080048.pdf','33','2025-07-12 11:00:48');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('34','17','0','Inventory_Report_20250714_041546.pdf','4','2025-07-14 09:15:51');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('35','17','0','Inventory_Report_20250801_073031.pdf','3','2025-08-01 12:30:36');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('36','17','0','Inventory_Report_20250801_093122.pdf','3','2025-08-01 14:31:23');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('37','17','0','Inventory_Report_20250802_141907.pdf','0','2025-08-02 20:19:11');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('38','17','0','Inventory_Report_20250804_015832.pdf','0','2025-08-04 06:58:37');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('39','17','0','Inventory_Report_20250804_020323.pdf','0','2025-08-04 07:03:24');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('40','17','0','Inventory_Report_20250804_020424.pdf','0','2025-08-04 07:04:25');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('41','17','0','Inventory_Report_20250819_070930.pdf','0','2025-08-19 13:09:35');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('42','17','0','Inventory_Report_20250829_131934.pdf','0','2025-08-29 18:19:35');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('43','17','0','Inventory_Report_20250901_033647.pdf','0','2025-09-01 08:36:48');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('44','17','0','Inventory_Report_20250911_080604.pdf','0','2025-09-11 14:06:06');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('45','24','0','Inventory_Report_20250914_054735.pdf','0','2025-09-14 11:47:39');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('46','24','0','Inventory_Report_20250914_054912.pdf','0','2025-09-14 11:49:13');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('47','24','0','Inventory_Report_20250914_055349.pdf','0','2025-09-14 11:53:49');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('50','24','3','Inventory_Report_20250914_133109.pdf','0','2025-09-14 19:31:10');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('52','17','9','Inventory_Report_20250914_133854.pdf','0','2025-09-14 19:38:54');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('53','17','3','Consumption_Report_20250915_042223.pdf','0','2025-09-15 07:22:27');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('54','17','19','Consumption_Report_20250915_042438.pdf','0','2025-09-15 07:24:39');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('56','17','4','Consumption_Report_20250915_043214.pdf','0','2025-09-15 07:32:14');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('57','17','4','Consumption_Report_20250915_043650.pdf','0','2025-09-15 07:36:50');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('58','17','4','Consumption_Report_20250915_043802.pdf','0','2025-09-15 07:38:04');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('59','17','4','Consumption_Report_20250915_044043.pdf','0','2025-09-15 07:40:44');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('60','17','4','Consumption_Report_20250915_044153.pdf','0','2025-09-15 07:41:55');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('61','17','4','Consumption_Report_20250915_044355.pdf','0','2025-09-15 07:43:56');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('62','17','4','Consumption_Report_20250915_044413.pdf','0','2025-09-15 07:44:14');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('63','17','4','Consumption_Report_20250915_044528.pdf','0','2025-09-15 07:45:30');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('64','17','4','Consumption_Report_20250915_044635.pdf','0','2025-09-15 07:46:37');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('65','17','4','Consumption_Report_20250915_044700.pdf','0','2025-09-15 07:47:02');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('66','17','4','Consumption_Report_20250915_044709.pdf','0','2025-09-15 07:47:12');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('67','17','4','Consumption_Report_20250915_044853.pdf','0','2025-09-15 07:48:55');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('68','24','3','Consumption_Report_20250915_050138.pdf','0','2025-09-15 08:01:40');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('69','17','4','Inventory_Report_20250920_040655.pdf','0','2025-09-20 07:06:56');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('70','17','4','Inventory_Report_20250920_041253.pdf','0','2025-09-20 07:12:54');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('71','17','4','Inventory_Report_20250920_041307.pdf','0','2025-09-20 07:13:08');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('72','17','4','Inventory_Report_20250920_041334.pdf','0','2025-09-20 07:13:35');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('73','17','4','Inventory_Report_20250920_041356.pdf','0','2025-09-20 07:13:57');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('74','17','4','Consumption_Report_20250920_050259.pdf','0','2025-09-20 08:03:01');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('75','17','4','Inventory_Report_20250920_113856.pdf','0','2025-09-20 14:38:57');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('76','17','4','Employee_MR_Report_20250920_114949.pdf','0','2025-09-20 14:49:49');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('77','17','4','Employee_MR_Report_20250920_133944.pdf','0','2025-09-20 16:39:48');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('78','17','4','Employee_MR_Report_20250920_140306.pdf','0','2025-09-20 17:03:07');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('79','17','4','Employee_MR_Report_20250921_103219.pdf','0','2025-09-21 13:32:23');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('80','17','4','Inventory_Report_20250921_103829.pdf','0','2025-09-21 13:38:30');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('81','17','4','Consumption_Report_20250921_104456.pdf','0','2025-09-21 13:44:57');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('82','17','4','Consumption_Report_20250921_104505.pdf','0','2025-09-21 13:45:05');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('83','17','4','Inventory_Report_20250921_112526.pdf','0','2025-09-21 14:25:27');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('84','17','4','Category_Inventory_Report_<br_/>\r\n<b>Warning</b>:__Undefined_array_key__2025-09-22_10-04-38.pdf','0','2025-09-22 13:04:39');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('85','17','4','Category_Inventory_Report_Unknown_Category_2025-09-22_10-06-23.pdf','0','2025-09-22 13:06:23');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('86','17','4','Inventory_Report_20250922_100930.pdf','0','2025-09-22 13:09:30');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('87','17','4','Inventory_Report_20250922_101005.pdf','0','2025-09-22 13:10:06');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('88','17','4','Unserviceable_Inventory_Report_2024-09_20250922_173512.pdf','0','2025-09-22 20:35:15');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('89','17','4','Unserviceable_Inventory_Report_2025-09_20250922_173601.pdf','0','2025-09-22 20:36:02');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('90','17','4','Unserviceable_Inventory_Report_2025-09_20250922_174512.pdf','0','2025-09-22 20:45:13');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('91','17','4','Unserviceable_Inventory_Report_2025-09_20250922_174913.pdf','0','2025-09-22 20:49:13');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('92','17','4','Unserviceable_Inventory_Report_2025-09_20250922_174918.pdf','0','2025-09-22 20:49:19');
INSERT INTO `generated_reports` (`id`,`user_id`,`office_id`,`filename`,`template_id`,`generated_at`) VALUES ('93','17','4','Employee_MR_Report_20250922_180210.pdf','0','2025-09-22 21:02:11');

--
-- Structure for table `ics_form`
--
DROP TABLE IF EXISTS `ics_form`;
CREATE TABLE `ics_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_image` varchar(255) DEFAULT NULL,
  `entity_name` varchar(255) DEFAULT NULL,
  `fund_cluster` varchar(100) DEFAULT NULL,
  `ics_no` varchar(100) DEFAULT NULL,
  `received_from_name` varchar(255) NOT NULL,
  `received_from_position` varchar(255) NOT NULL,
  `received_by_name` varchar(255) NOT NULL,
  `received_by_position` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `office_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `ics_form` (27 rows)
--
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('1','1758263261_Screenshot 2025-09-19 112710.png','','','ICS-2025-0001','','','','','2025-09-19 14:27:41','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('2','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0002','','','','','2025-09-19 14:28:30','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('3','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0003','','','','','2025-09-19 14:31:22','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('4','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0004','','','','','2025-09-19 14:35:22','4');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('5','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0004','','','','','2025-09-19 14:36:05','4');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('6','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0005','','','','','2025-09-19 14:46:32','3');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('7','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0006','','','','','2025-09-19 14:51:23','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('8','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0007','','','','','2025-09-19 14:55:15','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('9','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0008','','','','','2025-09-19 19:33:16','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('10','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0009','','','','','2025-09-19 19:40:35','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('11','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0010','','','','','2025-09-19 19:45:41','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('12','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0011','','','','','2025-09-19 20:02:40','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('13','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0012','','','','','2025-09-19 20:59:12','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('14','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0012','','','','','2025-09-19 21:00:46','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('15','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0012','','','','','2025-09-19 21:01:05','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('16','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0013','','','','','2025-09-19 21:41:33','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('17','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0014','','','','','2025-09-19 22:20:38','4');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('18','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0015','','','','','2025-09-19 22:36:01','4');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('19','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0016','','','','','2025-09-19 23:04:07','4');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('20','1758263261_Screenshot 2025-09-19 112710.png','INVENTORY','FC-2025-001','ICS-2025-0017','IVAN CHRISTOPHER R. MILLABAS','OFFICER','MARK JAYSON NAMIA','PROPERTY CUSTODIAN','2025-09-20 07:20:32','4');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('21','','INVENTORY','FC-2025-001','ICS-2025-0017','IVAN CHRISTOPHER R. MILLABAS','OFFICER','MARK JAYSON NAMIA','PROPERTY CUSTODIAN','2025-09-20 07:24:36','0');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('22','ics_header_1758325430_e38f060b.png','INVENTORY','FC-2025-001','ICS-2025-0018','IVAN CHRISTOPHER R. MILLABAS','OFFICER LGU','MARK JAYSON NAMIA','PROPERTY CUSTODIAN','2025-09-20 07:43:50','4');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('23','ics_header_1758325430_e38f060b.png','INVENTORY','FC-2025-001','ICS-2025-0019','IVAN CHRISTOPHER R. MILLABAS','OFFICER LGU','MARK JAYSON NAMIA','PROPERTY CUSTODIAN','2025-09-20 17:30:22','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('24','ics_header_1758325430_e38f060b.png','INVENTORY','FC-2025-001','ICS-2025-0020','IVAN CHRISTOPHER R. MILLABAS','OFFICER LGU','MARK JAYSON NAMIA','PROPERTY CUSTODIAN','2025-09-20 17:30:54','49');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('25','ics_header_1758325430_e38f060b.png','INVENTORY','FC-2025-001','ICS-2025-0021','IVAN CHRISTOPHER R. MILLABAS','OFFICER LGU','MARK JAYSON NAMIA','PROPERTY CUSTODIAN','2025-09-21 16:11:37','4');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('26','ics_header_1758325430_e38f060b.png','INVENTORY','fc-001','ICS-2025-0022','IVAN CHRISTOPHER R. MILLABAS','OFFICER LGU','MARK JAYSON NAMIA','PROPERTY CUSTODIAN','2025-09-22 20:31:24','4');
INSERT INTO `ics_form` (`id`,`header_image`,`entity_name`,`fund_cluster`,`ics_no`,`received_from_name`,`received_from_position`,`received_by_name`,`received_by_position`,`created_at`,`office_id`) VALUES ('27','ics_header_1758325430_e38f060b.png','INVENTORY','fc-001','ICS-2025-0023','IVAN CHRISTOPHER R. MILLABAS','OFFICER LGU','MARK JAYSON NAMIA','PROPERTY CUSTODIAN','2025-09-22 20:44:10','4');

--
-- Structure for table `ics_items`
--
DROP TABLE IF EXISTS `ics_items`;
CREATE TABLE `ics_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `ics_id` (`ics_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `ics_items` (10 rows)
--
INSERT INTO `ics_items` (`item_id`,`ics_id`,`asset_id`,`ics_no`,`quantity`,`unit`,`unit_cost`,`total_cost`,`description`,`item_no`,`estimated_useful_life`,`created_at`) VALUES ('1','17','1','ICS-2025-0014','2','pcs','3500.00','7000.00','Office Table – Wooden','','','2025-09-19 22:20:38');
INSERT INTO `ics_items` (`item_id`,`ics_id`,`asset_id`,`ics_no`,`quantity`,`unit`,`unit_cost`,`total_cost`,`description`,`item_no`,`estimated_useful_life`,`created_at`) VALUES ('3','18','3','ICS-2025-0015','2','pcs','350.00','700.00','Mouse','','','2025-09-19 22:36:01');
INSERT INTO `ics_items` (`item_id`,`ics_id`,`asset_id`,`ics_no`,`quantity`,`unit`,`unit_cost`,`total_cost`,`description`,`item_no`,`estimated_useful_life`,`created_at`) VALUES ('4','19','5','ICS-2025-0016','2','pcs','4593.00','9186.00','Printer Epson','','','2025-09-19 23:04:07');
INSERT INTO `ics_items` (`item_id`,`ics_id`,`asset_id`,`ics_no`,`quantity`,`unit`,`unit_cost`,`total_cost`,`description`,`item_no`,`estimated_useful_life`,`created_at`) VALUES ('5','19','6','ICS-2025-0016','1','pcs','4593.00','4593.00','Printer Epson','','','2025-09-19 23:24:55');
INSERT INTO `ics_items` (`item_id`,`ics_id`,`asset_id`,`ics_no`,`quantity`,`unit`,`unit_cost`,`total_cost`,`description`,`item_no`,`estimated_useful_life`,`created_at`) VALUES ('7','22','8','ICS-2025-0018','1','pcs','4573.98','4573.98','Desktop Computer – Intel i5, 8GB RAM, 256GB SSD','','','2025-09-20 07:43:50');
INSERT INTO `ics_items` (`item_id`,`ics_id`,`asset_id`,`ics_no`,`quantity`,`unit`,`unit_cost`,`total_cost`,`description`,`item_no`,`estimated_useful_life`,`created_at`) VALUES ('10','25','19','ICS-2025-0021','2','pcs','5678.00','11356.00','Cellphone','','','2025-09-21 16:11:37');
INSERT INTO `ics_items` (`item_id`,`ics_id`,`asset_id`,`ics_no`,`quantity`,`unit`,`unit_cost`,`total_cost`,`description`,`item_no`,`estimated_useful_life`,`created_at`) VALUES ('11','25','20','ICS-2025-0021','1','pcs','5678.00','5678.00','Cellphone','','','2025-09-21 21:16:38');
INSERT INTO `ics_items` (`item_id`,`ics_id`,`asset_id`,`ics_no`,`quantity`,`unit`,`unit_cost`,`total_cost`,`description`,`item_no`,`estimated_useful_life`,`created_at`) VALUES ('12','26','37','ICS-2025-0022','2','unit','450.00','900.00','Stylus','','','2025-09-22 20:31:24');
INSERT INTO `ics_items` (`item_id`,`ics_id`,`asset_id`,`ics_no`,`quantity`,`unit`,`unit_cost`,`total_cost`,`description`,`item_no`,`estimated_useful_life`,`created_at`) VALUES ('13','27','40','ICS-2025-0023','2','unit','36500.00','73000.00','Computer','','','2025-09-22 20:44:10');
INSERT INTO `ics_items` (`item_id`,`ics_id`,`asset_id`,`ics_no`,`quantity`,`unit`,`unit_cost`,`total_cost`,`description`,`item_no`,`estimated_useful_life`,`created_at`) VALUES ('14','27','41','ICS-2025-0023','1','unit','36500.00','36500.00','Computer','','','2025-09-22 21:23:20');

--
-- Structure for table `iirup_form`
--
DROP TABLE IF EXISTS `iirup_form`;
CREATE TABLE `iirup_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_image` varchar(255) DEFAULT NULL,
  `accountable_officer` varchar(100) NOT NULL,
  `designation` varchar(100) NOT NULL,
  `office` varchar(100) NOT NULL,
  `footer_accountable_officer` varchar(100) NOT NULL,
  `footer_authorized_official` varchar(100) NOT NULL,
  `footer_designation_officer` varchar(100) NOT NULL,
  `footer_designation_official` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `iirup_form` (24 rows)
--
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('1','1755934207_Screenshot 2025-08-23 141806.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-08-13 12:55:42');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('2',NULL,'WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-08-29 21:48:25');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('3',NULL,'WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-08-29 21:49:18');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('4',NULL,'WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-08-29 21:50:45');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('5','1756475584_Screenshot 2025-08-29 204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-08-29 21:53:04');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('7',NULL,'WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-09-22 08:39:27');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('8',NULL,'WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-09-22 08:40:33');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('9',NULL,'WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-09-22 08:44:04');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('10','iirup_header_1758502284_4daaf8e5_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-09-22 08:51:24');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('11',NULL,'WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-09-22 08:52:03');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('12','iirup_header_1758502461_f55f217d_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-09-22 08:54:21');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('13',NULL,'WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-09-22 08:54:27');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('14','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-09-22 08:57:00');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('15','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer II','Municipal Mayor','2025-09-22 08:57:11');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('16','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer III','Municipal Mayor','2025-09-22 09:01:17');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('17','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer III','Municipal Mayor','2025-09-22 12:39:04');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('18','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer III','Municipal Mayor','2025-09-22 12:55:47');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('19','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer III','Municipal Mayor','2025-09-22 14:08:16');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('20','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer III','Municipal Mayor','2025-09-22 15:29:25');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('21','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer III','Municipal Mayor','2025-09-22 15:34:16');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('22','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer III','Municipal Mayor','2025-09-22 18:41:47');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('23','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer III','Municipal Mayor','2025-09-22 23:08:52');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('24','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer III','Municipal Mayor','2025-09-23 11:27:29');
INSERT INTO `iirup_form` (`id`,`header_image`,`accountable_officer`,`designation`,`office`,`footer_accountable_officer`,`footer_authorized_official`,`footer_designation_officer`,`footer_designation_official`,`created_at`) VALUES ('25','iirup_header_1758502620_0acbd277_Screenshot_2025-08-29_204458.png','WALTON LONEZA','OFFICE','DILG','MA. ANNIE L. PERETE','CAROLYN C. SY-REYES','Public Information Officer III','Municipal Mayor','2025-09-23 18:17:39');

--
-- Structure for table `iirup_items`
--
DROP TABLE IF EXISTS `iirup_items`;
CREATE TABLE `iirup_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`item_id`),
  KEY `idx_iirup_id` (`iirup_id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `iirup_items` (20 rows)
--
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('1',NULL,'34','2025-09-22','Hilux van black','','1','2300000.00','2300000.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 08:30:09');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('2','7',NULL,'2025-09-22','Hilux Van','','1','7600000.00','7600000.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','','','','2025-09-22','2025-09-22 08:39:27');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('3','8','19','2025-09-22','Cellphone','','1','5678.00','5678.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 08:40:33');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('4','9','32','2025-09-22','Honda Click 125','','1','75000.00','75000.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 08:44:04');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('5','10','27','2025-09-22','Jetski','','1','96780.00','96780.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 08:51:24');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('6','11','35','2025-09-22','Lenovo AMD Ryzen 7','','1','75000.00','75000.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 08:52:03');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('7','12','23','2025-09-22','Dell Unit','','1','99000.00','99000.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 08:54:21');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('8','13','35','2025-09-22','Lenovo AMD Ryzen 7','','1','75000.00','75000.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 08:54:27');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('9','14','19','2025-09-22','Cellphone','','1','5678.00','5678.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 08:57:00');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('10','15','30','2025-09-22','Car','','1','4500000.00','4500000.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 08:57:11');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('11','16','19','2025-09-22','Cellphone','','1','5678.00','5678.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 09:01:17');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('12','17','32','2025-09-22','Honda Click 125','','1','75000.00','75000.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 12:39:04');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('13','18','20','2025-09-22','Cellphone','','1','5678.00','5678.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 12:55:47');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('14','19','32','2025-09-22','Honda Click 125','No. PS-5S-03-F02-32','1','75000.00','75000.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 14:08:16');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('15','20','1','2025-09-22','Office Table – Wooden','','1','3500.00','3500.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 15:29:25');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('16','21','33','2025-09-22','Hilux Van','','1','7600000.00','7600000.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 15:34:16');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('17','22','5','2025-09-22','Printer Epson','','1','4593.00','4593.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 18:41:47');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('18','23','40','2025-09-22','Computer','','1','36500.00','36500.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-22','2025-09-22 23:08:52');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('19','24','40','2025-09-23','Computer','','1','36500.00','36500.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-23','2025-09-23 11:27:29');
INSERT INTO `iirup_items` (`item_id`,`iirup_id`,`asset_id`,`date_acquired`,`particulars`,`property_no`,`qty`,`unit_cost`,`total_cost`,`accumulated_depreciation`,`accumulated_impairment_losses`,`carrying_amount`,`remarks`,`sale`,`transfer`,`destruction`,`others`,`total`,`appraised_value`,`or_no`,`amount`,`dept_office`,`code`,`red_tag`,`date_received`,`created_at`) VALUES ('20','25','41','2025-09-23','Computer','','1','36500.00','36500.00','0.00','0.00','0.00','Unserviceable','','','','','0.00','0.00','','0.00','Supply Office','','','2025-09-23','2025-09-23 18:17:39');

--
-- Structure for table `infrastructure_inventory`
--
DROP TABLE IF EXISTS `infrastructure_inventory`;
CREATE TABLE `infrastructure_inventory` (
  `inventory_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `image_4` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`inventory_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `infrastructure_inventory` (1 rows)
--
INSERT INTO `infrastructure_inventory` (`inventory_id`,`classification_type`,`item_description`,`nature_occupancy`,`location`,`date_constructed_acquired_manufactured`,`property_no_or_reference`,`acquisition_cost`,`market_appraisal_insurable_interest`,`date_of_appraisal`,`remarks`,`image_1`,`image_2`,`image_3`,`image_4`) VALUES ('1','BUILDING','Multi Purpose bldg.','Gymnasium','LGU-Complex','2025-09-04','BLDNG22-32','6792388.00','777406.50','2025-09-04','','uploads/1756949402_397369.jpg','uploads/1756949402_ChatGPT Image Jul 17, 2025, 08_24_14 AM.png',NULL,NULL);

--
-- Structure for table `inventory_actions`
--
DROP TABLE IF EXISTS `inventory_actions`;
CREATE TABLE `inventory_actions` (
  `action_id` int(11) NOT NULL AUTO_INCREMENT,
  `action_name` varchar(255) NOT NULL,
  `office_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`action_id`),
  KEY `office_id` (`office_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `mr_details`
--
DROP TABLE IF EXISTS `mr_details`;
CREATE TABLE `mr_details` (
  `mr_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `inventory_tag` varchar(50) NOT NULL,
  PRIMARY KEY (`mr_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `mr_details` (11 rows)
--
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('1','1','Supply Office','Office Table – Wooden','','','0','0','1.00','pcs','2025-09-19','3500.00','Juan A. Dela Cruz','0000-00-00','0000-00-00','2025-09-19 22:23:56','1','No. PS-5S-03-F02-01');
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('3','3','Supply Office','Mouse','','','0','0','1.00','pcs','2025-09-19','350.00','Juan A. Dela Cruz','0000-00-00','0000-00-00','2025-09-19 22:36:44','3','No. PS-5S-03-F02-03');
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('4','4','Supply Office','Printer Epson','','','0','1','1.00','pcs','2025-09-19','4593.00','Juan A. Dela Cruz','0000-00-00','0000-00-00','2025-09-19 23:05:22','5','No. PS-5S-03-F02-05');
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('5','5','Supply Office','Printer Epson','','','0','0','1.00','pcs','2025-09-19','4593.00','Juan A. Dela Cruz','0000-00-00','0000-00-00','2025-09-19 23:24:55','6','No. PS-5S-03-F02-06');
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('8','10','Supply Office','Cellphone','','','0','0','1.00','pcs','2025-09-21','5678.00','Juan A. Dela Cruz','0000-00-00','0000-00-00','2025-09-21 16:12:19','19','No. PS-5S-03-F02-19');
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('9','11','Supply Office','Cellphone','','','0','0','1.00','pcs','2025-09-21','5678.00','Maria Santos','0000-00-00','0000-00-00','2025-09-21 21:16:38','20','No. PS-5S-03-F02-20');
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('11',NULL,'Supply Office','Honda','','','0','0','1.00','unit','2025-09-21','75000.00','Maria Santos','0000-00-00','0000-00-00','2025-09-21 21:53:13','32','No. PS-5S-03-F02-32');
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('12',NULL,'Supply Office','Hilux Van','','','0','0','1.00','unit','2025-09-21','7600000.00','Pedro Reyes','0000-00-00','0000-00-00','2025-09-22 05:57:07','33','No. PS-5S-03-F02-33');
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('13',NULL,'Supply Office','Hilux van black','','','0','0','1.00','unit','2025-09-22','2300000.00','Ryan Bang','0000-00-00','0000-00-00','2025-09-22 06:04:25','34','No. PS-5S-03-F02-34');
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('14','13','Supply Office','Computer','','','0','1','1.00','unit','2025-09-22','36500.00','Maria Santos','0000-00-00','0000-00-00','2025-09-22 21:04:03','40','No. PS-5S-03-F02-40');
INSERT INTO `mr_details` (`mr_id`,`item_id`,`office_location`,`description`,`model_no`,`serial_no`,`serviceable`,`unserviceable`,`unit_quantity`,`unit`,`acquisition_date`,`acquisition_cost`,`person_accountable`,`acquired_date`,`counted_date`,`created_at`,`asset_id`,`inventory_tag`) VALUES ('15','14','Supply Office','Computer','','','0','1','1.00','unit','2025-09-22','36500.00','Maria Santos','0000-00-00','0000-00-00','2025-09-22 21:23:20','41','No. PS-5S-03-F02-41');

--
-- Structure for table `offices`
--
DROP TABLE IF EXISTS `offices`;
CREATE TABLE `offices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `office_name` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `office_name` (`office_name`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `offices` (45 rows)
--
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('1','MPDC',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('2','IT Office',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('3','OMASS',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('4','Supply Office',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('5','OMAD',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('7','RHU Office',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('9','Main',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('11','OMSWD',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('13','OBAC',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('14','COA',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('15','COMELEC',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('16','CSOLAR',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('17','DILG',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('18','MENRU',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('19','GAD',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('20','GS-Motorpool',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('21','ABC',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('22','SEF-DEPED',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('23','HRMO',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('24','KALAHI',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('25','LIBRARY',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('26','OMAC',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('27','OMA',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('28','OMBO',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('29','MCR',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('30','MDRRMO',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('31','OME',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('32','MHO',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('33','OMM',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('34','MTC',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('35','MTO-PORT-MARKET',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('36','NCDC',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('37','OSCA',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('38','PAO',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('39','PiCC',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('40','PIHC',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('41','PIO-PESO',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('42','PNP',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('43','SB',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('44','SB-SEC',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('45','SK',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('46','TOURISM',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('47','OVM',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('48','BPLO',NULL);
INSERT INTO `offices` (`id`,`office_name`,`icon`) VALUES ('49','7K',NULL);

--
-- Structure for table `par_form`
--
DROP TABLE IF EXISTS `par_form`;
CREATE TABLE `par_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `date_received_right` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `par_form` (38 rows)
--
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('3','0','3','','',NULL,'DepEd','FC-2025-001','PAR-0001','2025-09-15 22:26:34','0000-00-00','0000-00-00');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('4','0','3','','',NULL,'DepEd','FC-2025-001','PAR-0001','2025-09-15 22:31:12','0000-00-00','0000-00-00');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('5','0','3','ivan christoper millabas','mark jayson namia',NULL,'LGU','FC-2025-001','PAR-0002','2025-09-15 22:35:52',NULL,NULL);
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('6','0','3','OFFICER','PROPERTY CUSTODIAN',NULL,'LGU','FC-2025-001','PAR-0003','2025-09-15 22:47:50',NULL,NULL);
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('7','0','3','OFFICER','PROPERTY CUSTODIAN',NULL,'LGU','FC-2025-001','PAR-0003','2025-09-15 22:48:18',NULL,NULL);
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('8','0','3','OFFICER','PROPERTY CUSTODIAN',NULL,'LGU','FC-2025-001','PAR-0004','2025-09-15 23:10:33',NULL,NULL);
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('10','3','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0005','2025-09-16 10:34:56',NULL,NULL);
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('11','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0006','2025-09-16 17:04:15',NULL,NULL);
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('12','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0007','2025-09-16 17:08:48','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('13','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0008','2025-09-16 17:10:44','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('14','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0009','2025-09-16 17:43:27','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('15','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0009','2025-09-16 17:48:35','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('16','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0010','2025-09-16 17:48:44','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('17','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0010','2025-09-16 17:49:27','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('18','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0010','2025-09-16 17:50:18','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('19','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0011','2025-09-16 17:51:55','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('20','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0012','2025-09-16 17:57:03','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('21','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0012','2025-09-16 17:58:10','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('22','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0013','2025-09-16 18:10:23','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('23','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0014','2025-09-16 18:12:34','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('24','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0015','2025-09-16 18:16:11','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('27','0','3','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0016','2025-09-16 18:28:47','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('34','0',NULL,'OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0017','2025-09-16 18:36:33','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('35','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0018','2025-09-16 19:46:43','2025-09-16','2025-09-16');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('36','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0019','2025-09-21 20:56:37','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('37','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0020','2025-09-21 20:59:18','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('38','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0021','2025-09-21 21:14:32','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('39','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0022','2025-09-21 21:21:59','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('40','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0022','2025-09-21 21:22:37','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('41','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0022','2025-09-21 21:25:03','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('42','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0023','2025-09-21 21:28:12','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('43','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0024','2025-09-21 21:33:35','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('44','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0025','2025-09-21 21:40:55','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('45','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0026','2025-09-21 21:51:18','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('46','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0027','2025-09-22 05:56:51','2025-09-21','2025-09-21');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('47','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0028','2025-09-22 06:04:09','2025-09-22','2025-09-22');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('48','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0029','2025-09-22 06:06:40','2025-09-22','2025-09-22');
INSERT INTO `par_form` (`id`,`form_id`,`office_id`,`position_office_left`,`position_office_right`,`header_image`,`entity_name`,`fund_cluster`,`par_no`,`created_at`,`date_received_left`,`date_received_right`) VALUES ('49','0','4','OFFICER','PROPERTY CUSTODIAN','1757991153_Screenshot 2025-09-16 105218.png','LGU','FC-2025-001','PAR-0030','2025-09-22 06:07:07','2025-09-22','2025-09-22');

--
-- Structure for table `par_items`
--
DROP TABLE IF EXISTS `par_items`;
CREATE TABLE `par_items` (
  `item_id` int(11) NOT NULL AUTO_INCREMENT,
  `form_id` int(11) NOT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `unit` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `property_no` varchar(100) DEFAULT NULL,
  `date_acquired` date DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT 0.00,
  `amount` decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (`item_id`),
  KEY `form_id` (`form_id`),
  KEY `asset_id` (`asset_id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `par_items` (23 rows)
--
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('1','5','4','1','pcs','Ergonomic Office Chair','PROP-0003','2025-09-01','6500.00','6500.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('2','6','6','1','pcs','Desktop Computer – Intel i5, 8GB RAM, 256GB SSD','PROP-0005','2025-09-06','35000.00','35000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('3','7','6','1','pcs','Desktop Computer – Intel i5, 8GB RAM, 256GB SSD','PROP-0005','2025-09-06','35000.00','35000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('4','8','27','1','unit','Lenovo','STOCK-0017','2025-09-15','52000.00','52000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('5','11','27','1','unit','Lenovo','STOCK-0017','2025-09-15','52000.00','52000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('6','12','27','1','unit','Lenovo','STOCK-0017','2025-09-15','52000.00','52000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('7','13','27','1','unit','Lenovo','STOCK-0017','2025-09-15','52000.00','52000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('8','14','27','1','unit','Lenovo','STOCK-0017','2025-09-15','52000.00','52000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('9','18','29','1','unit','Lenovo','STOCK-0017','2025-09-15','52000.00','0.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('10','19','29','1','unit','Lenovo','STOCK-0017','2025-09-15','52000.00','52000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('11','24','31','1','unit','Desktop Computer (Core i5)','STOCK-0017','2025-09-16','55000.00','55000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('12','27','31','1','unit','Desktop Computer (Core i5)','STOCK-0017','2025-09-16','55000.00','55000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('13','34','33','1','unit','Air Conditioner 2HP Split','STOCK-0017','2025-09-16','51000.00','51000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('14','35','30','1','unit','Desktop Computer (Core i5)','STOCK-0017','2025-09-16','55000.00','55000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('15','38','23','2','unit','Dell Unit','','0000-00-00','99000.00','198000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('16','41','27','2','unit','Jetski','','0000-00-00','96780.00','193560.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('17','42','29','1','roll','HIlux','','0000-00-00','1000000.00','1000000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('18','43','30','1','unit','Car','','0000-00-00','4500000.00','4500000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('19','44','31','1','unit','Mio Soul i','','0000-00-00','75000.00','75000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('20','45','32','1','unit','Honda Click 125','','0000-00-00','75000.00','75000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('21','46','33','1','unit','Hilux Van','','0000-00-00','7600000.00','7600000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('22','47','34','1','unit','Hilux van black','','0000-00-00','2300000.00','2300000.00');
INSERT INTO `par_items` (`item_id`,`form_id`,`asset_id`,`quantity`,`unit`,`description`,`property_no`,`date_acquired`,`unit_price`,`amount`) VALUES ('23','49','35','2','unit','Lenovo AMD Ryzen 7','','0000-00-00','75000.00','150000.00');

--
-- Structure for table `red_tags`
--
DROP TABLE IF EXISTS `red_tags`;
CREATE TABLE `red_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `red_tag_number` (`red_tag_number`),
  KEY `asset_id` (`asset_id`),
  KEY `iirup_id` (`iirup_id`),
  KEY `tagged_by` (`tagged_by`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `red_tags` (5 rows)
--
INSERT INTO `red_tags` (`id`,`red_tag_number`,`asset_id`,`iirup_id`,`date_received`,`tagged_by`,`item_location`,`description`,`removal_reason`,`action`,`status`,`created_at`,`updated_at`) VALUES ('1','PS-5S-03-F01-01-01','19','16','2025-09-22','17','Supply Offices','Cellphone (MR-2025-00019)','Broken','For Disposal','Pending','2025-09-22 10:48:14','2025-09-22 11:23:20');
INSERT INTO `red_tags` (`id`,`red_tag_number`,`asset_id`,`iirup_id`,`date_received`,`tagged_by`,`item_location`,`description`,`removal_reason`,`action`,`status`,`created_at`,`updated_at`) VALUES ('2','PS-5S-03-F01-01-02','20','18','2025-09-22','17','Supply Office','Cellphone (MR-2025-00020)','Marupok','For Donation','Pending','2025-09-22 13:10:09','2025-09-22 13:10:09');
INSERT INTO `red_tags` (`id`,`red_tag_number`,`asset_id`,`iirup_id`,`date_received`,`tagged_by`,`item_location`,`description`,`removal_reason`,`action`,`status`,`created_at`,`updated_at`) VALUES ('3','PS-5S-03-F01-01-03','33','21','2025-09-22','17','Garage','Hilux Van (MR-2025-00033)','Not in use','For Relocation','Pending','2025-09-22 15:35:32','2025-09-22 15:35:32');
INSERT INTO `red_tags` (`id`,`red_tag_number`,`asset_id`,`iirup_id`,`date_received`,`tagged_by`,`item_location`,`description`,`removal_reason`,`action`,`status`,`created_at`,`updated_at`) VALUES ('4','PS-5S-03-F01-01-04','5','22','2025-09-22','17','Supply Office','Printer Epson (MR-2025-00005)','Not in use','For Disposal','Pending','2025-09-22 19:32:35','2025-09-22 19:32:35');
INSERT INTO `red_tags` (`id`,`red_tag_number`,`asset_id`,`iirup_id`,`date_received`,`tagged_by`,`item_location`,`description`,`removal_reason`,`action`,`status`,`created_at`,`updated_at`) VALUES ('5','PS-5S-03-F01-01-05','40','23','2025-09-23','17','Garage','Computer (MR-2025-00040)','Unnecessary','For Disposal','Pending','2025-09-23 18:17:04','2025-09-23 18:17:04');

--
-- Structure for table `report_generation_settings`
--
DROP TABLE IF EXISTS `report_generation_settings`;
CREATE TABLE `report_generation_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `frequency` enum('weekly','monthly','daily') NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `day_of_month` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `report_generation_settings` (2 rows)
--
INSERT INTO `report_generation_settings` (`id`,`frequency`,`day_of_week`,`day_of_month`) VALUES ('1','weekly','Monday','3');
INSERT INTO `report_generation_settings` (`id`,`frequency`,`day_of_week`,`day_of_month`) VALUES ('16','weekly','Monday','3');

--
-- Structure for table `report_templates`
--
DROP TABLE IF EXISTS `report_templates`;
CREATE TABLE `report_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) NOT NULL,
  `header_html` text DEFAULT NULL,
  `subheader_html` text DEFAULT NULL,
  `footer_html` text DEFAULT NULL,
  `left_logo_path` varchar(255) DEFAULT NULL,
  `right_logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_created_by` (`created_by`),
  KEY `fk_updated_by` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `report_templates` (8 rows)
--
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('2','Inventory Custodian Slip','<div style=\"font-family:\"Times New Roman\"; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:;\"=\"\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:left;\"=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:;\"=\"\">Hello World<div><b>inventory report</b></div><div><i>as of&nbsp;$dynamic_month&nbsp;$dynamic_year</i></div></div></div></div></div></div>','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:;=\"\" text-align:;\"=\"\"><div style=\"font-family:; font-size:; text-align:left;\"><div style=\"font-family:; font-size:; text-align:;\">name:&nbsp;[blank]&nbsp; position:&nbsp;[blank]</div></div></div></div></div>','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:left;\"><div style=\"font-family:; font-size:; text-align:;\">signature:&nbsp;[blank]</div></div></div></div></div>','../uploads/6867dfb04e6d4_Laptop Dell XPS 15_QR (1).png',NULL,'2025-07-04 22:05:36','2025-07-08 11:25:01','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('3','Property Acknowledgement Receipt','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Arial; font-size:; text-align:;\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" font-size:16px;=\"\" text-align:start;\"=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" \"=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" \"=\"\">REPUBLIC OF THE PHILIPPINES<div><b>PROPERTY ACKNOWLEDGEMENT RECEIPT</b></div><div><i>As of&nbsp;$dynamic_month&nbsp;$dynamic_year</i></div></div></div></div></div></div>','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Poppins, sans-serif; font-size:16px; text-align:start;\"><div style=\"  \"><div style=\"  \">name:&nbsp;[blank]&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;subject:&nbsp;[blank]</div></div></div></div></div>','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Poppins, sans-serif; font-size:16px; text-align:start;\"><div style=\"  \"><div style=\"  \">signature:&nbsp;[blank]<div>property:&nbsp;[blank]</div></div></div></div></div></div>','../uploads/68693f21f1b44_logo.jpg','../uploads/68693f21f245d_logo.jpg','2025-07-05 23:05:05','2025-07-08 09:50:38','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('4','Inventory Transfer Report','<div style=\"\"><div style=\"\"><div style=\"\"><div style=\"font-family:\" times=\"\" new=\"\" roman\";=\"\" \"=\"\">REPUBLIC OF THE PHILIPPINES<div><b>INVENTORY TRANSFER REPORT</b></div><div><i>As of&nbsp;</i>&nbsp;$dynamic_month&nbsp;$dynamic_year</div></div></div></div></div>','<div style=\"\"><div style=\"\"><div style=\"\"><div style=\"  \">name:&nbsp;[blank]&nbsp;</div></div></div></div>','<div style=\"\"><div style=\"\"><div style=\"\"><div style=\"  \">signature:&nbsp;[blank]</div></div></div></div>','../uploads/686942fdd82cc_logo.jpg','../uploads/right_1752067662_37.png','2025-07-05 23:21:33','2025-07-07 20:54:56','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('5','Memorandum Report','<div style=\"font-family:Tahoma; font-size:16px; text-align:center;\">\n    <b>Republic of the Philippines</b><br>\n    Municipality of Pilar\n</div>\n','<div style=\"font-size:12px; text-align:right;\">\n    Prepared: $DYNAMIC_MONTH $DYNAMIC_YEAR\n</div>\n\\','<div style=\"font-family:; font-size:; text-align:;\"><div style=\"font-family:Tahoma; font-size:12px; text-align:left;\">signature:&nbsp;[blank]</div></div>','../uploads/686944de212f8_logo.jpg','../uploads/686944de218f6_logo.jpg','2025-07-05 23:29:34','2025-07-08 11:25:29','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('30','sample 3','<div style=\"font-size: 14px;\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"font-family: Tahoma;\"><div style=\"\">Republic of the Philippines<div style=\"\"><div style=\"font-family:; font-size:; text-align:;\"></div></div><div><b>Municipality of Pilar</b></div><div>Province of Sorsogon</div></div></div></div></div></div></div></div>','<div style=\"font-size: 18px; font-family: Georgia;\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"text-align: left; font-family: Georgia; font-size: 12px;\"><div>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Annex A.3</div>Entity name:<u>LGU-PILAR/OMSWD</u>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; Fund Cluster<div>From Acountable Officer/Agency Fund Cluster MARK JAYSON NAMIA/LGU-PILAR-OMPDC/OFFICE SUPPLY&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;ITR No: 24-09-1</div><div>To Accountable&nbsp; Offices/Agency/Fund Cluster: VLADIMIR ABOGADO&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;Date: 3/12/25&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</div><div>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</div><div>Transfer Type: (Check only)</div><div>[blank]donation&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;&nbsp;[blank]relocate</div><div>[blank]reaasignment&nbsp;[blank]others (specify)[blank]<br><div style=\"\"><div style=\"font-family:; font-size:; text-align:;\"></div></div><div><u><br></u></div></div></div></div></div></div></div></div></div>','<div style=\"font-family: Georgia; font-size: 18px;\"><div style=\"\"><div style=\"\"><div style=\"\"><div style=\"text-align: left;\"><div style=\"\"><div style=\"font-size: 12px;\">[blank][blank]&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; [blank]&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;[blank]<br><div style=\"\"><div style=\"font-family:; font-size:; text-align:;\"></div></div><div>&nbsp; &nbsp; &nbsp; name of accountable officer&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; (designation)&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; department office&nbsp; &nbsp; &nbsp;</div></div></div></div></div></div></div></div>','../uploads/686f1e3ad26b5_PILAR LOGO TRANSPARENT.png','','2025-07-09 21:35:40','2025-07-09 21:35:40','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('31','sample 4','<div style=\"font-family:; font-size:; text-align:;\">yuebobceuob</div>','<div style=\"font-family:Arial; font-size:12px; text-align:left;\"><table class=\"table table-bordered\"><tbody><tr><td>hibicbocjsclsjcdkjdcdjcbjdbcdjb</td><td>hellolcdnckdckdcndkcndlkcndlnc</td><td>xsjhcbsjkcb</td></tr><tr><td>[blank]</td><td>[blank]</td><td>kjsbcjscjcsc</td></tr></tbody></table></div>','<div style=\"font-family:; font-size:; text-align:;\"><br><table class=\"table table-bordered\"><tbody><tr><td>[blank]kcnckdnckna;cndk</td><td><br></td><td>helljdowidwio</td></tr><tr><td>gievi bcsjbs</td><td>[blank]</td><td>[blank]</td></tr></tbody></table></div>','../uploads/686fdcf7c9274_logo.jpg','../uploads/686fdcf7c9cf8_38.png','2025-07-10 23:32:07','2025-07-10 23:32:07','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('32','SAMPLE 5 BORDER','<div style=\"font-family:; font-size:; text-align:;\">HEADER</div>','<div style=\"font-family:; font-size:; text-align:;\">HELLO<table class=\"table\"><tbody><tr><td>[blank]NAME NO BORDER</td><td>[blank]</td></tr></tbody></table></div>','<div style=\"font-family:; font-size:; text-align:;\">HELLO WITH BORDER</div>',NULL,NULL,'2025-07-10 23:35:29','2025-07-10 23:35:29','17','17');
INSERT INTO `report_templates` (`id`,`template_name`,`header_html`,`subheader_html`,`footer_html`,`left_logo_path`,`right_logo_path`,`created_at`,`updated_at`,`created_by`,`updated_by`) VALUES ('33','sample 6','<div style=\"font-family:\"Times New Roman\"; font-size:; text-align:;\">republic of the philippines</div>','<div style=\"font-family:; font-size:12px; text-align:;\"><table class=\"table\"><tbody><tr><td>name[blank]</td><td>date[blank]</td></tr></tbody></table></div>','<div style=\"font-family:; font-size:12px; text-align:;\"><table class=\"table\"><tbody><tr><td>signature[blank]</td><td>date[blank]</td></tr></tbody></table></div>','../uploads/6870b954756b1_logo.jpg','../uploads/6870b95475f65_PILAR LOGO TRANSPARENT.png','2025-07-11 15:12:20','2025-07-11 15:12:20','17','17');

--
-- Structure for table `returned_assets`
--
DROP TABLE IF EXISTS `returned_assets`;
CREATE TABLE `returned_assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `borrow_request_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `return_date` datetime NOT NULL,
  `condition_on_return` varchar(255) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `office_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `borrow_request_id` (`borrow_request_id`),
  KEY `asset_id` (`asset_id`),
  KEY `user_id` (`user_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `returned_assets` (2 rows)
--
INSERT INTO `returned_assets` (`id`,`borrow_request_id`,`asset_id`,`user_id`,`return_date`,`condition_on_return`,`remarks`,`office_id`) VALUES ('7','13','18','17','2025-04-20 19:58:58','Good','Returned','9');
INSERT INTO `returned_assets` (`id`,`borrow_request_id`,`asset_id`,`user_id`,`return_date`,`condition_on_return`,`remarks`,`office_id`) VALUES ('8','1','2','12','2025-04-20 20:28:03','Good','Returned','4');

--
-- Structure for table `ris_form`
--
DROP TABLE IF EXISTS `ris_form`;
CREATE TABLE `ris_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `issued_by_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_id` (`form_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `ris_form` (72 rows)
--
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('3','6','11','1755867841_Screenshot 2025-08-22 103403.png','v','','','ris-001','sAI-001','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-08-22 20:58:03','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('7','0','11',NULL,'v','','','RIS-2025-0002','SAI-2025-0002','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-09-08 21:34:18','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('8','0','11',NULL,'v','','','RIS-2025-0003','SAI-2025-0003','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-09-08 21:41:03','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('9','6','11',NULL,'v','','','ris-001','sAI-001','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-09-08 21:46:38','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('10','6','11',NULL,'v','','','ris-001','sAI-001','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-09-08 21:55:45','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('11','6','11',NULL,'v','','','ris-001','sAI-001','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-09-08 21:58:45','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('12','6','11',NULL,'v','','','ris-001','sAI-001','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-09-08 22:10:16','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('13','6','11','','v','','','ris-001','sAI-001','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-09-08 22:22:20','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('14','6','11',NULL,'v','','','ris-001','sAI-001','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-09-08 22:35:25','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('15','6','11',NULL,'v','','','ris-001','sAI-001','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-09-08 22:37:32','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('16','6','11',NULL,'v','','','ris-001','sAI-001','2025-08-22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','ROY L. RICACHO','CLERK','2025-08-22','0000-00-00','','2025-09-08 22:41:03','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-08-22','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-08-22');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('17','6','11',NULL,'v','','','RIS-2025-0012','SAI-2025-0012','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-08','ROY L. RICACHO','CLERK','2025-09-08','0000-00-00','','2025-09-08 22:52:40','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-08','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-08');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('18','6','11',NULL,'v','','','RIS-2025-0013','SAI-2025-0013','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-08','ROY L. RICACHO','CLERK','2025-09-08','0000-00-00','','2025-09-08 22:53:02','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-08','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-08');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('19','6','11',NULL,'v','','','RIS-2025-0014','SAI-2025-0014','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-08','ROY L. RICACHO','CLERK','2025-09-08','0000-00-00','','2025-09-08 22:58:44','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-08','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-08');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('20','6','11',NULL,'v','','','RIS-2025-0015','SAI-2025-0015','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','ROY L. RICACHO','CLERK','2025-09-09','0000-00-00','','2025-09-09 08:38:29','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-09');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('21','6','11',NULL,'v','','','RIS-2025-0016','SAI-2025-0016','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','ROY L. RICACHO','CLERK','2025-09-09','0000-00-00','','2025-09-09 08:44:29','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-09');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('22','6','11','1757378730_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0017','SAI-2025-0017','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','ROY L. RICACHO','CLERK','2025-09-09','0000-00-00','','2025-09-09 08:45:30','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-09');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('23','6','11',NULL,'v','','','RIS-2025-0018','SAI-2025-0018','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','ROY L. RICACHO','CLERK','2025-09-09','0000-00-00','','2025-09-09 08:48:25','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-09');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('24','6','11','1757378941_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0019','SAI-2025-0019','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','ROY L. RICACHO','CLERK','2025-09-09','0000-00-00','','2025-09-09 08:49:01','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-09');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('25','6','11',NULL,'v','','','RIS-2025-0020','SAI-2025-0020','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','ROY L. RICACHO','CLERK','2025-09-09','0000-00-00','','2025-09-09 08:51:25','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-09');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('26','6','11','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0021','SAI-2025-0021','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','ROY L. RICACHO','CLERK','2025-09-09','0000-00-00','','2025-09-09 08:59:32','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-09');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('27','6','11','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0022','SAI-2025-0022','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','ROY L. RICACHO','CLERK','2025-09-09','0000-00-00','','2025-09-09 08:59:37','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-09');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('28','6','11','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0023','SAI-2025-0023','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','ROY L. RICACHO','CLERK','2025-09-09','0000-00-00','','2025-09-09 09:12:53','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-09','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-09');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('29','6','11','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0024','SAI-2025-0024','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 15:56:45','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('30','6','11','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0025','SAI-2025-0025','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 16:00:56','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('31','6','11','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0026','SAI-2025-0026','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 16:01:41','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('32','6','11','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0027','SAI-2025-0027','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 16:02:03','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('33','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0028','SAI-2025-0028','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 16:04:17','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('34','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0029','SAI-2025-0029','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 22:18:13','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('35','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0030','SAI-2025-0030','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 22:32:26','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('36','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0031','SAI-2025-0031','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 22:35:03','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('37','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0032','SAI-2025-0032','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 22:35:32','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('38','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0033','SAI-2025-0033','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 22:44:31','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('39','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0033','SAI-2025-0033','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 22:46:12','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('40','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0035','SAI-2025-0035','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 22:46:24','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('41','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0035','SAI-2025-0035','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 22:47:33','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('42','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0035','SAI-2025-0035','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','ROY L. RICACHO','CLERK','2025-09-10','0000-00-00','','2025-09-10 23:03:20','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-10','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-10');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('43','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0038','SAI-2025-0038','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','ROY L. RICACHO','CLERK','2025-09-11','0000-00-00','','2025-09-11 22:09:00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-11');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('44','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0038','SAI-2025-0038','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','ROY L. RICACHO','CLERK','2025-09-11','0000-00-00','','2025-09-11 22:10:34','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-11');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('45','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0038','SAI-2025-0038','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','ROY L. RICACHO','CLERK','2025-09-11','0000-00-00','','2025-09-11 22:15:43','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-11');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('46','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0038','SAI-2025-0038','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','ROY L. RICACHO','CLERK','2025-09-11','0000-00-00','','2025-09-11 22:28:42','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-11');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('47','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0042','SAI-2025-0042','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','ROY L. RICACHO','CLERK','2025-09-11','0000-00-00','','2025-09-11 22:29:29','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-11');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('48','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0043','SAI-2025-0043','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','ROY L. RICACHO','CLERK','2025-09-11','0000-00-00','','2025-09-11 22:35:45','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-11');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('49','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0044','SAI-2025-0044','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','ROY L. RICACHO','CLERK','2025-09-11','0000-00-00','','2025-09-11 22:49:23','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-11');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('50','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0045','SAI-2025-0045','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','ROY L. RICACHO','CLERK','2025-09-11','0000-00-00','For printing','2025-09-11 23:45:53','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-11','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-11');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('51','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0046','SAI-2025-0046','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-12','ROY L. RICACHO','CLERK','2025-09-12','0000-00-00','For printing','2025-09-12 08:15:55','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-12','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-12');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('52','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0047','SAI-2025-0047','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-12','ROY L. RICACHO','CLERK','2025-09-12','0000-00-00','For printing','2025-09-12 08:18:25','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-12','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-12');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('53','6','3','1757379572_Screenshot_2025-08-29_204458.png','v','','','RIS-2025-0048','SAI-2025-0048','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-12','ROY L. RICACHO','CLERK','2025-09-12','0000-00-00','For printing','2025-09-12 08:22:58','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-12','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-12');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('54','6','33','1757740937_Screenshot_2025-09-13_132057.png','v','','','RIS-2025-0049','SAI-2025-0049','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 13:22:17','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('55','6','33','1757740937_Screenshot_2025-09-13_132057.png','v','','','RIS-2025-0050','SAI-2025-0050','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 13:22:37','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('56','6','3','1757740937_Screenshot_2025-09-13_132057.png','v','','','RIS-2025-0051','SAI-2025-0051','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 13:24:22','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('57','6','29','1757740937_Screenshot_2025-09-13_132057.png','v','','','RIS-2025-0052','SAI-2025-0052','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 13:33:34','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('58','6','29','1757740937_Screenshot_2025-09-13_132057.png','v','','','RIS-2025-0052','SAI-2025-0052','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 13:34:10','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('59','6','29','1757740937_Screenshot_2025-09-13_132057.png','v','','','RIS-2025-0052','SAI-2025-0052','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 13:35:52','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('60','6','49','1757740937_Screenshot_2025-09-13_132057.png','v','','','RIS-2025-0055','SAI-2025-0055','0000-00-00','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 15:12:12','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('61','6','21','1757740937_Screenshot_2025-09-13_132057.png','v','','','0','SAI-2025-0056','2025-09-13','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 15:36:03','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('62','6','48','1757740937_Screenshot_2025-09-13_132057.png','v','','','0','SAI-2025-0057','2025-09-13','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 15:37:10','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('63','6','14','1757740937_Screenshot_2025-09-13_132057.png','v','','','RIS-2025-0058','SAI-2025-0058','2025-09-13','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 15:39:13','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('64','6','14','1757740937_Screenshot_2025-09-13_132057.png','v','','','RIS-2025-0059','SAI-2025-0059','2025-09-13','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 21:05:03','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('65','6','48','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0060','SAI-2025-0060','2025-09-13','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','ROY L. RICACHO','CLERK','2025-09-13','0000-00-00','For printing','2025-09-13 21:07:46','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-13','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-13');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('66','6','48','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0061','SAI-2025-0061','2025-09-14','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-14','ROY L. RICACHO','CLERK','2025-09-14','0000-00-00','For printing','2025-09-14 21:23:14','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-14','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-14');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('67','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0062','SAI-2025-0062','2025-09-14','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-14','ROY L. RICACHO','CLERK','2025-09-14','0000-00-00','For printing','2025-09-14 21:23:30','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-14','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-14');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('68','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0063','SAI-2025-0063','2025-09-14','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-14','ROY L. RICACHO','CLERK','2025-09-14','0000-00-00','For printing','2025-09-14 21:24:17','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-14','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-14');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('69','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0064','SAI-2025-0064','2025-09-14','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-14','ROY L. RICACHO','CLERK','2025-09-14','0000-00-00','For printing','2025-09-14 21:27:45','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-14','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-14');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('70','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0065','SAI-2025-0065','2025-09-20','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-20','ROY L. RICACHO','CLERK','2025-09-20','0000-00-00','For printing','2025-09-20 08:23:54','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-20','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-20');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('71','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0066','SAI-2025-0066','2025-09-20','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-20','ROY L. RICACHO','CLERK','2025-09-20','0000-00-00','For printing','2025-09-20 08:45:24','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-20','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-20');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('72','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0067','SAI-2025-0067','2025-09-20','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-20','ROY L. RICACHO','CLERK','2025-09-20','0000-00-00','For printing','2025-09-20 09:24:02','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-20','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-20');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('73','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0068','SAI-2025-0068','2025-09-20','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-20','ROY L. RICACHO','CLERK','2025-09-20','0000-00-00','For printing','2025-09-20 22:40:24','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-20','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-20');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('74','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0069','SAI-2025-0069','2025-09-21','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-21','ROY L. RICACHO','CLERK','2025-09-21','0000-00-00','For printing','2025-09-21 17:03:46','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-21','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-21');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('75','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0070','SAI-2025-0070','2025-09-21','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-21','ROY L. RICACHO','CLERK','2025-09-21','0000-00-00','For printing','2025-09-21 17:27:23','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-21','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-21');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('76','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0071','SAI-2025-0071','2025-09-23','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-23','ROY L. RICACHO','CLERK','2025-09-23','0000-00-00','For printing','2025-09-23 11:42:36','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-23','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-23');
INSERT INTO `ris_form` (`id`,`form_id`,`office_id`,`header_image`,`division`,`responsibility_center`,`responsibility_code`,`ris_no`,`sai_no`,`date`,`approved_by_name`,`approved_by_designation`,`approved_by_date`,`received_by_name`,`received_by_designation`,`received_by_date`,`footer_date`,`reason_for_transfer`,`created_at`,`requested_by_name`,`requested_by_designation`,`requested_by_date`,`issued_by_name`,`issued_by_designation`,`issued_by_date`) VALUES ('77','6','3','1757740937_Screenshot_2025-09-13_132057.png','V','','1','RIS-2025-0072','SAI-2025-0072','2025-09-23','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-23','ROY L. RICACHO','CLERK','2025-09-23','0000-00-00','For printing','2025-09-23 14:03:11','CAROLYN C. SY-REYES','MUNICIPAL MAYOR','2025-09-23','IVAN CHRISTOPHER R. MILLABAS','SUPPLY OFFICER','2025-09-23');

--
-- Structure for table `ris_items`
--
DROP TABLE IF EXISTS `ris_items`;
CREATE TABLE `ris_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ris_form_id` int(11) NOT NULL,
  `stock_no` varchar(100) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ris_form_id` (`ris_form_id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `ris_items` (59 rows)
--
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('1','7','STOCK-0004','17','Alcohol 70% Solution (500ml)','0','60.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('2','8','STOCK-0002','1','Ballpen (Blue Ink)','0','15.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('3','9','STOCK-0002','1','Ballpen (Blue Ink)','0','15.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('4','11','STOCK-0002','1','Ballpen (Blue Ink)','0','15.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('5','12','STOCK-0002','1','Ballpen (Blue Ink)','0','15.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('6','13','STOCK-0004','17','Alcohol 70% Solution (500ml)','0','60.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('7','14','STOCK-0004','17','Alcohol 70% Solution (500ml)','0','60.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('8','17','STOCK-0004','17','Alcohol 70% Solution (500ml)','0','60.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('9','28','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('10','29','STOCK-0004','17','Alcohol 70% Solution (500ml)','2','60.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('11','30','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('12','31','STOCK-0002','1','Ballpen (Blue Ink)','2','15.00','30.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('13','32','STOCK-0004','17','Alcohol 70% Solution (500ml)','10','60.00','600.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('14','33','','1','Bond paper','2','250.00','500.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('15','34','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('16','35','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('17','36','STOCK-0002','1','Ballpen (Blue Ink)','1','15.00','15.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('18','37','STOCK-0002','1','Ballpen (Blue Ink)','1','15.00','15.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('19','38','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('20','39','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('21','40','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('22','41','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('23','42','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('24','43','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('25','44','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('26','45','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('27','46','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('28','47','STOCK-0002','1','Ballpen (Blue Ink)','1','15.00','15.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('29','48','STOCK-0004','17','Alcohol 70% Solution (500ml)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('30','49','STOCK-0004','17','Alcohol 70% Solution (500ml) (Supply Office)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('31','50','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','1','300.00','300.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('32','51','STOCK-0004','17','Alcohol 70% Solution (500ml) (OMASS)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('33','52','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','1','300.00','300.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('34','53','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','1','300.00','300.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('35','54','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','1','300.00','300.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('36','55','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','1','300.00','300.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('37','57','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','1','300.00','300.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('38','58','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','1','300.00','300.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('39','59','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','1','300.00','300.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('40','60','STOCK-0004','17','Alcohol 70% Solution (500ml) (Supply Office)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('41','61','STOCK-0004','17','Alcohol 70% Solution (500ml) (Supply Office)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('42','62','STOCK-0002','1','Ballpen (Blue Ink) (Supply Office)','1','15.00','15.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('43','63','STOCK-0004','17','Alcohol 70% Solution (500ml) (Supply Office)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('44','64','STOCK-0004','17','Alcohol 70% Solution (500ml) (Supply Office)','3','60.00','0.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('45','64','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','2','300.00','600.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('46','65','STOCK-0004','17','Alcohol 70% Solution (500ml) (Supply Office)','2','60.00','120.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('47','65','STOCK-0004','17','Alcohol 70% Solution (500ml) (Supply Office)','2','60.00','120.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('48','66','STOCK-0004','17','Alcohol 70% Solution (500ml) (Supply Office)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('49','67','STOCK-0004','17','Alcohol 70% Solution (500ml) (Supply Office)','1','60.00','60.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('50','68','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','1','300.00','300.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('51','69','STOCK-0003','1','Printer Ink Cartridge (Black) (Supply Office)','1','300.00','300.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('52','70','','1','Ink','5','250.00','1250.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('53','71','','1','Ballpen','100','7.50','750.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('54','72','','22','bond paper','6','350.00','1400.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('55','73','','2','ink','2','340.00','680.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('56','74','','2','Ballpen','2','345.00','690.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('57','75','','2','Ballpen','2','234.01','468.02');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('58','76','','1','ballpen','100','7.50','750.00');
INSERT INTO `ris_items` (`id`,`ris_form_id`,`stock_no`,`unit`,`description`,`quantity`,`price`,`total`) VALUES ('59','77','1','1','ballpen panda','20','7.00','140.00');

--
-- Structure for table `rpcppe_form`
--
DROP TABLE IF EXISTS `rpcppe_form`;
CREATE TABLE `rpcppe_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `header_image` varchar(255) DEFAULT NULL,
  `accountable_officer` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `agency_office` varchar(255) NOT NULL,
  `member_inventory` varchar(255) NOT NULL,
  `chairman_inventory` varchar(255) NOT NULL,
  `mayor` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `rpcppe_form` (1 rows)
--
INSERT INTO `rpcppe_form` (`id`,`header_image`,`accountable_officer`,`destination`,`agency_office`,`member_inventory`,`chairman_inventory`,`mayor`,`created_at`) VALUES ('1','header_1755919258.png','','','OMAD Office','','','','2025-08-14 22:52:25');

--
-- Structure for table `settings`
--
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_name` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Structure for table `system`
--
DROP TABLE IF EXISTS `system`;
CREATE TABLE `system` (
  `id` int(11) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `system_title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `system` (1 rows)
--
INSERT INTO `system` (`id`,`logo`,`system_title`) VALUES ('1','1755868631_158e7711-e186-42d4-ad9f-547bffbad174.jpg','Pilar Inventory Management System');

--
-- Structure for table `system_info`
--
DROP TABLE IF EXISTS `system_info`;
CREATE TABLE `system_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `system_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `developer_name` varchar(255) NOT NULL,
  `developer_email` varchar(255) DEFAULT NULL,
  `version` varchar(50) DEFAULT NULL,
  `credits` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `system_info` (1 rows)
--
INSERT INTO `system_info` (`id`,`system_name`,`description`,`developer_name`,`developer_email`,`version`,`credits`,`created_at`) VALUES ('1','Web-based Asset Inventory Management System','This system manages and tracks assets across different offices. It supports inventory categorization, QR code tracking, report generation, and user role-based access.','Walton Loneza','waltonloneza@example.com','1.0','Developed by BU Polangui Capstone Team for the Municipality of Pilar, Sorsogon.','2025-08-03 18:30:56');

--
-- Structure for table `system_logs`
--
DROP TABLE IF EXISTS `system_logs`;
CREATE TABLE `system_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `office_id` int(11) NOT NULL,
  `module` varchar(100) NOT NULL,
  `action` text NOT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `datetime` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `system_logs` (5 rows)
--
INSERT INTO `system_logs` (`id`,`user_id`,`office_id`,`module`,`action`,`ip_address`,`datetime`) VALUES ('1','4','4','Asset Management','Added new asset: HP Laptop (ID: 4), Category: Electronics','::1','2025-04-06 17:48:07');
INSERT INTO `system_logs` (`id`,`user_id`,`office_id`,`module`,`action`,`ip_address`,`datetime`) VALUES ('2','12','4','Assets','Added asset: Desktop Computer Set','::1','2025-04-21 06:35:28');
INSERT INTO `system_logs` (`id`,`user_id`,`office_id`,`module`,`action`,`ip_address`,`datetime`) VALUES ('3','12','4','Categories','Added new category: Luminaires','::1','2025-04-21 13:28:02');
INSERT INTO `system_logs` (`id`,`user_id`,`office_id`,`module`,`action`,`ip_address`,`datetime`) VALUES ('4','12','4','Assets','Added asset: Generator','::1','2025-04-21 14:01:52');
INSERT INTO `system_logs` (`id`,`user_id`,`office_id`,`module`,`action`,`ip_address`,`datetime`) VALUES ('5','12','4','Categories','Added new category: Luminaires','::1','2025-04-21 14:02:08');

--
-- Structure for table `tag_formats`
--
DROP TABLE IF EXISTS `tag_formats`;
CREATE TABLE `tag_formats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tag_type` varchar(50) NOT NULL,
  `format_code` varchar(100) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `format_code` (`format_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `tag_formats` (1 rows)
--
INSERT INTO `tag_formats` (`id`,`tag_type`,`format_code`,`created_by`,`created_at`) VALUES ('1','Red Tag','PS-5S-03-F01-01-01','1','2025-09-24 00:19:49');

--
-- Structure for table `unit`
--
DROP TABLE IF EXISTS `unit`;
CREATE TABLE `unit` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `unit` (22 rows)
--
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('1','pcs');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('2','box');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('3','set');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('4','pack');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('5','dozen');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('6','liter');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('7','milliliter');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('8','kilogram');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('9','gram');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('10','meter');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('11','centimeter');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('12','inch');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('13','foot');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('14','yard');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('15','gallon');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('16','tablet');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('17','bottle');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('18','roll');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('19','can');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('20','tube');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('21','unit');
INSERT INTO `unit` (`id`,`unit_name`) VALUES ('22','reams');

--
-- Structure for table `users`
--
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `session_timeout` int(11) DEFAULT 1800,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `office_id` (`office_id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Data for table `users` (19 rows)
--
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('1','OMPDC','Mark Jayson Namia','waltielappy67@gmail.com','$2y$10$PjQBLH0.VE3gnzvEqc9YXOhDu.wuUFpAYK1Ze/NnGOi6S3DcIdaGm','super_admin','active','2025-04-01 21:01:47',NULL,NULL,NULL,'default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('2','user2','Mark John','john2@example.com','hashed_password','user','active','2025-04-03 12:31:57',NULL,NULL,'1','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('4','user4','Steve Jobs','mark4@example.com','hashed_password','user','active','2025-04-03 12:31:57',NULL,NULL,'3','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('5','johndoe','Elon Musk','johndoe@example.com','password123','admin','inactive','2025-04-03 12:45:50',NULL,NULL,'1','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('6','janesmith','Mark Zuckerberg','janesmith@example.com','password123','admin','active','2025-04-03 12:45:50',NULL,NULL,'2','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('7','tomgreen','Tom Jones','tomgreen@example.com','password123','admin','active','2025-04-03 12:45:50',NULL,NULL,'1','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('8','marybrown','Ed Caluag','marybrown@example.com','password123','office_user','active','2025-04-03 12:45:50',NULL,NULL,'3','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('9','peterwhite','Peter White','peterwhite@example.com','password123','admin','active','2025-04-03 12:45:50',NULL,NULL,'2','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('10','walt','Walton Loneza','waltielappy@gmail.com','$2y$10$j5gUPrRPP0w0REknIdYrce.l5ZItK3c5WJXX3eC2OSQHtJ/YchHey','admin','active','2025-04-04 09:31:30',NULL,NULL,NULL,'default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('12','walts','Walton Loneza','wjll@bicol-u.edu.ph','$2y$10$tsOlFU9fjwi/DLRKdGkqL.aIXhKnlFxnNbA8ZoXeMbEiAhoe.sg/i','office_admin','inactive','2025-04-07 22:13:29',NULL,NULL,'4','WIN_20240930_21_49_09_Pro.jpg','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('15','josh','Joshua Escano','jmfte@gmail.com','$2y$10$IFmIX3WZ0YOxdf41EYzX6.IF51IKEg0bL0kmyORCI8dod42v.JeN6','office_user','active','2025-04-09 08:49:07','5a8b600a59a80f2bf5028ae258b3aae8','2025-04-09 09:49:07','4','josh.jpg','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('16','elton','Elton John B. Moises','ejbm@bicol-u.edu.ph','$2y$10$Botz5wCa9biZrVT7IdEDau.uVBcw3ByoD75pX2BYYe7dtutigluY.','user','inactive','2025-04-13 14:01:46',NULL,NULL,'9','profile_16_1749816479.jpg','600');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('17','nami','Mark Jayson Namia','mjn@gmail.com','$2y$10$2MIZlmP380wS0sj/cOfqbe20HkPz234S49cJEj2omrrTjBasHVqyO','admin','active','2025-04-13 23:43:51',NULL,NULL,'4','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('18','kiimon','Seynatour Kiimon','sk@gmail.com','$2y$10$UGpyMRA79O2OKhKfZDEf5O9CyXkMFlhDsVpWdELXMYnMtdFIV0mSC','office_user','deleted','2025-04-21 05:36:04','6687598406441374aeffbc338a60f728','2025-04-21 06:36:04','4','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('19','geely','Geely Mitsubishi','waltielappy123@gmail.com','$2y$10$uVrAvdjC3GsGheiqmZSuF.r.oBbcHdOceQaV.E5LChrNNc/p20/FC','admin','active','2025-06-24 14:54:34',NULL,NULL,'4','default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('21','miki','Miki Matsubara','mikimat@gmail.com','$2y$10$hE2SgXv.RQahXlmHCv4MEeBfBLqkaY7/w9OVyZbnuy83LMMPrFDHa','user','active','2025-06-24 15:01:30',NULL,NULL,NULL,'default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('22','Toyoki','Toyota Suzuki','toyoki@gmail.com','$2y$10$dLNw4hqEJbKpB5Hc7Mmhr.AjH4dOiMIUg9BqGDkiLnnx3rw89KBfS','user','active','2025-06-24 15:23:43',NULL,NULL,NULL,'default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('23','jet','Jet Kawasaki','kawaisaki@gmail.com','$2y$10$JmxsfOnmMH/nJbxWUbuSqODWoHTMx8RZn/Zxg38EFpGlvhqCtP3b6','user','active','2025-06-24 15:24:56',NULL,NULL,NULL,'default_profile.png','1800');
INSERT INTO `users` (`id`,`username`,`fullname`,`email`,`password`,`role`,`status`,`created_at`,`reset_token`,`reset_token_expiry`,`office_id`,`profile_picture`,`session_timeout`) VALUES ('24','juan','Juan A. Dela Cruz','juandelacruz@gmail.com','$2y$10$NO/J3fBNaHSu/5HNM2vp/.hbb.u1NRzLSo8AQWh55P/TmnkUUv.Xe','office_admin','active','2025-09-14 10:29:57',NULL,NULL,'3','default_profile.png','1800');

SET FOREIGN_KEY_CHECKS=1;
