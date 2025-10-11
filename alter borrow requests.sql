ALTER TABLE borrow_requests 
ADD COLUMN requested_return_date DATE NULL 
COMMENT 'Expected return date for inter-department borrow requests' 
AFTER purpose;