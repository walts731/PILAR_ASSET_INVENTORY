-- ================================================================
-- Database improvements for borrowing and returning process
-- Run this script to add constraints, indexes, and improve data integrity
-- ================================================================

-- Add foreign key constraints for referential integrity (drop if exists first)
ALTER TABLE borrow_requests DROP FOREIGN KEY IF EXISTS fk_borrow_user;
ALTER TABLE borrow_requests 
ADD CONSTRAINT fk_borrow_user 
FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE borrow_requests DROP FOREIGN KEY IF EXISTS fk_borrow_asset;
ALTER TABLE borrow_requests 
ADD CONSTRAINT fk_borrow_asset 
FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE borrow_requests DROP FOREIGN KEY IF EXISTS fk_borrow_office;
ALTER TABLE borrow_requests 
ADD CONSTRAINT fk_borrow_office 
FOREIGN KEY (office_id) REFERENCES offices(id) ON DELETE CASCADE ON UPDATE CASCADE;

-- Add indexes for better query performance (drop if exists first)
DROP INDEX IF EXISTS idx_borrow_requests_user_id ON borrow_requests;
CREATE INDEX idx_borrow_requests_user_id ON borrow_requests(user_id);

DROP INDEX IF EXISTS idx_borrow_requests_asset_id ON borrow_requests;
CREATE INDEX idx_borrow_requests_asset_id ON borrow_requests(asset_id);

DROP INDEX IF EXISTS idx_borrow_requests_office_id ON borrow_requests;
CREATE INDEX idx_borrow_requests_office_id ON borrow_requests(office_id);

DROP INDEX IF EXISTS idx_borrow_requests_status ON borrow_requests;
CREATE INDEX idx_borrow_requests_status ON borrow_requests(status);

DROP INDEX IF EXISTS idx_borrow_requests_requested_at ON borrow_requests;
CREATE INDEX idx_borrow_requests_requested_at ON borrow_requests(requested_at);

DROP INDEX IF EXISTS idx_assets_office_status ON assets;
CREATE INDEX idx_assets_office_status ON assets(office_id, status);

DROP INDEX IF EXISTS idx_assets_status ON assets;
CREATE INDEX idx_assets_status ON assets(status);

-- Add timestamp columns for better tracking (before triggers)
ALTER TABLE borrow_requests 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

ALTER TABLE borrow_requests 
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add triggers for data validation instead of CHECK constraints
DELIMITER $$

-- Drop existing triggers if they exist
DROP TRIGGER IF EXISTS tr_borrow_requests_validation$$
DROP TRIGGER IF EXISTS tr_borrow_requests_update_validation$$
DROP TRIGGER IF EXISTS tr_assets_validation$$
DROP TRIGGER IF EXISTS tr_assets_update_validation$$

-- Trigger for borrow_requests validation
CREATE TRIGGER tr_borrow_requests_validation
BEFORE INSERT ON borrow_requests
FOR EACH ROW
BEGIN
    -- Validate quantity is positive
    IF NEW.quantity <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Quantity must be greater than 0';
    END IF;
    
    -- Validate status values
    IF NEW.status NOT IN ('pending', 'borrowed', 'returned', 'rejected') THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Invalid status value';
    END IF;
END$$

-- Trigger for borrow_requests updates
CREATE TRIGGER tr_borrow_requests_update_validation
BEFORE UPDATE ON borrow_requests
FOR EACH ROW
BEGIN
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
END$$

-- Trigger for assets validation
CREATE TRIGGER tr_assets_validation
BEFORE INSERT ON assets
FOR EACH ROW
BEGIN
    -- Validate quantity is not negative
    IF NEW.quantity < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Asset quantity cannot be negative';
    END IF;
END$$

-- Trigger for assets updates
CREATE TRIGGER tr_assets_update_validation
BEFORE UPDATE ON assets
FOR EACH ROW
BEGIN
    -- Validate quantity is not negative
    IF NEW.quantity < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Asset quantity cannot be negative';
    END IF;
END$$

DELIMITER ;


-- Create view for active borrowing statistics
CREATE OR REPLACE VIEW active_borrowing_stats AS
SELECT 
    o.office_name,
    COUNT(br.id) as total_borrowed,
    SUM(br.quantity) as total_quantity_borrowed,
    COUNT(DISTINCT br.user_id) as unique_borrowers
FROM borrow_requests br
JOIN offices o ON br.office_id = o.id
WHERE br.status = 'borrowed'
GROUP BY o.id, o.office_name;

-- Create view for overdue items (30+ days borrowed)
CREATE OR REPLACE VIEW overdue_items AS
SELECT 
    br.id,
    u.fullname as borrower_name,
    a.asset_name,
    br.quantity,
    br.approved_at,
    DATEDIFF(NOW(), br.approved_at) as days_borrowed,
    o.office_name
FROM borrow_requests br
JOIN users u ON br.user_id = u.id
JOIN assets a ON br.asset_id = a.id
JOIN offices o ON br.office_id = o.id
WHERE br.status = 'borrowed' 
AND DATEDIFF(NOW(), br.approved_at) > 30;
