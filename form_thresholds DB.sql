CREATE TABLE `form_thresholds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `form_type` varchar(50) NOT NULL COMMENT 'e.g., ics, par, ris, etc.',
  `max_amount` decimal(10,2) DEFAULT NULL COMMENT 'Maximum amount threshold for this form type',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_type` (`form_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insert default thresholds if needed
INSERT INTO `form_thresholds` (`form_type`, `max_amount`) VALUES
('ics', 50000.00),
('par', 50000.00),
('ris', 50000.00),
('iirup', 50000.00);