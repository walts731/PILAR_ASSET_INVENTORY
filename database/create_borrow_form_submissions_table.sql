-- Borrow Form Submissions Table
-- Simple table to store complete borrow form submissions

CREATE TABLE IF NOT EXISTS `borrow_form_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_number` varchar(50) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `date_borrowed` date NOT NULL,
  `schedule_return` date NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `releasing_officer` varchar(255) NOT NULL,
  `approved_by` varchar(255) NOT NULL,
  `items` JSON NOT NULL COMMENT 'JSON array of borrowed items with thing, qty, remarks',
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `submission_number` (`submission_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
