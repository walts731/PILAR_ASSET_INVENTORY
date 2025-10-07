-- Migration to update role_permissions table to use role_id instead of role enum
-- This ensures better referential integrity with the roles table

-- First, check if role_id column exists, if not add it
SET @dbname = DATABASE();
SET @tablename = 'role_permissions';
SET @columnname = 'role_id';

-- Add role_id column if it doesn't exist
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
        WHERE (TABLE_SCHEMA = @dbname)
        AND (TABLE_NAME = @tablename)
        AND (COLUMN_NAME = @columnname)
    ) = 0,
    'ALTER TABLE `role_permissions` ADD COLUMN `role_id` INT NULL AFTER `id`;',
    'SELECT ''Column role_id already exists'' AS message;'
));

PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Add index on role_id if it doesn't exist
SET @indexname = 'idx_role_permissions_role_id';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = @tablename)
     AND (INDEX_NAME = @indexname)) = 0,
    'ALTER TABLE `role_permissions` ADD INDEX `idx_role_permissions_role_id` (`role_id`);',
    'SELECT ''Index idx_role_permissions_role_id already exists'' AS message;'
));

PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

-- Update existing role_permissions with the correct role_id if not already set
SET @preparedStatement = 'UPDATE `role_permissions` rp '
    'JOIN `roles` r ON rp.`role` = r.`name` '
    'SET rp.`role_id` = r.`id` '
    'WHERE rp.`role_id` IS NULL;';

PREPARE updateRoleIds FROM @preparedStatement;
EXECUTE updateRoleIds;
DEALLOCATE PREPARE updateRoleIds;

-- Add foreign key constraint if it doesn't exist
SET @constraint_name = 'fk_role_permissions_role_id';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.TABLE_CONSTRAINTS 
     WHERE (CONSTRAINT_SCHEMA = @dbname)
     AND (TABLE_NAME = 'role_permissions')
     AND (CONSTRAINT_NAME = @constraint_name)) = 0,
    'ALTER TABLE `role_permissions` ADD CONSTRAINT `fk_role_permissions_role_id` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE;',
    'SELECT ''Foreign key constraint already exists'' AS message;'
));

PREPARE fkIfNotExists FROM @preparedStatement;
EXECUTE fkIfNotExists;
DEALLOCATE PREPARE fkIfNotExists;

-- Make role_id NOT NULL if it's not already and all records have been updated
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM `role_permissions` WHERE `role_id` IS NULL) = 0,
    'ALTER TABLE `role_permissions` MODIFY COLUMN `role_id` INT NOT NULL;',
    'SELECT ''Cannot set role_id to NOT NULL: there are NULL values'' AS message;'
));

PREPARE alterIfNullable FROM @preparedStatement;
EXECUTE alterIfNullable;
DEALLOCATE PREPARE alterIfNullable;

-- Drop the old unique index on (role, permission_id) if it exists
SET @old_indexname = 'unique_role_permission';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = 'role_permissions')
     AND (INDEX_NAME = @old_indexname)) > 0,
    'ALTER TABLE `role_permissions` DROP INDEX `unique_role_permission`;',
    'SELECT ''Index unique_role_permission does not exist'' AS message;'
));

PREPARE dropIndexIfExists FROM @preparedStatement;
EXECUTE dropIndexIfExists;
DEALLOCATE PREPARE dropIndexIfExists;

-- Create new unique index with role_id and permission_id if it doesn't exist
SET @new_indexname = 'unique_role_permission';
SET @preparedStatement = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE (TABLE_SCHEMA = @dbname)
     AND (TABLE_NAME = 'role_permissions')
     AND (INDEX_NAME = @new_indexname)) = 0,
    'ALTER TABLE `role_permissions` ADD UNIQUE INDEX `unique_role_permission` (`role_id`, `permission_id`);',
    'SELECT ''Index unique_role_permission already exists'' AS message;'
));

PREPARE addIndexIfNotExists FROM @preparedStatement;
EXECUTE addIndexIfNotExists;
DEALLOCATE PREPARE addIndexIfNotExists;

-- Drop the old role column (after ensuring everything works, you can uncomment this line)
-- ALTER TABLE `role_permissions` DROP COLUMN `role`;

-- Update the roles_manager.php to use the new schema
-- The following is just for reference, the actual PHP code will be updated separately
-- UPDATE `roles_manager.php` SET query = '...' WHERE ...;

-- Update the update_role.php to use the new schema
-- The following is just for reference, the actual PHP code will be updated separately
-- UPDATE `update_role.php` SET query = '...' WHERE ...;

-- Add a comment to document the change
ALTER TABLE `role_permissions`
COMMENT = 'Updated to use role_id instead of role enum for better referential integrity. Migration applied on 2023-10-07.';
