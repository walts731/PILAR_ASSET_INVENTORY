-- Add head_user_id column to offices table
ALTER TABLE `offices` 
ADD COLUMN `head_user_id` INT(11) NULL,
ADD CONSTRAINT `fk_offices_head_user` 
FOREIGN KEY (`head_user_id`) 
REFERENCES `users`(`id`) 
ON DELETE SET NULL;

-- Also add the other missing columns that might be needed
ALTER TABLE `offices`
ADD COLUMN `description` TEXT NULL,
ADD COLUMN `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP(),
ADD COLUMN `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP() ON UPDATE CURRENT_TIMESTAMP;