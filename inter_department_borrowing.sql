-- Drop existing constraints if they exist
ALTER TABLE `borrow_requests`
DROP CONSTRAINT IF EXISTS `fk_borrow_requests_source_office`,
DROP CONSTRAINT IF EXISTS `fk_borrow_requests_requested_by`,
DROP CONSTRAINT IF EXISTS `fk_borrow_requests_requested_for_office`;

-- Add the foreign key constraints
ALTER TABLE `borrow_requests`
ADD CONSTRAINT `fk_borrow_requests_source_office` 
FOREIGN KEY (`source_office_id`) REFERENCES `offices`(`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_borrow_requests_requested_by` 
FOREIGN KEY (`requested_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_borrow_requests_requested_for_office` 
FOREIGN KEY (`requested_for_office_id`) REFERENCES `offices`(`id`) ON DELETE SET NULL;

-- Update the status enum to include 'pending_approval'
ALTER TABLE `borrow_requests` 
MODIFY COLUMN `status` ENUM('pending','approved','rejected','borrowed','returned','pending_approval') 
NOT NULL DEFAULT 'pending';

-- Create the inter_department_approvals table if it doesn't exist
CREATE TABLE IF NOT EXISTS `inter_department_approvals` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_id` INT(11) NOT NULL,
  `approver_id` INT(11) NOT NULL,
  `approval_type` ENUM('office_head','source_office') NOT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `comments` TEXT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  KEY `request_id` (`request_id`),
  KEY `approver_id` (`approver_id`),
  CONSTRAINT `fk_ida_request` FOREIGN KEY (`request_id`) REFERENCES `borrow_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ida_approver` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;