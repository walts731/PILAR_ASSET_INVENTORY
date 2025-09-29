ALTER TABLE `borrow_requests` 
ADD COLUMN `is_inter_department` TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN `source_office_id` INT(11) NULL,
ADD COLUMN `requested_by_user_id` INT(11) NULL,
ADD COLUMN `requested_for_office_id` INT(11) NULL,
ADD COLUMN `approved_by_office_head` TINYINT(1) NOT NULL DEFAULT 0,
ADD COLUMN `approved_by_source_office` TINYINT(1) NOT NULL DEFAULT 0,
ADD CONSTRAINT `fk_borrow_requests_source_office` FOREIGN KEY (`source_office_id`) REFERENCES `offices`(`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_borrow_requests_requested_by` FOREIGN KEY (`requested_by_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
ADD CONSTRAINT `fk_borrow_requests_requested_for_office` FOREIGN KEY (`requested_for_office_id`) REFERENCES `offices`(`id`) ON DELETE SET NULL;