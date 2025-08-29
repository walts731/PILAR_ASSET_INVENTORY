-- SQL script to update the database schema for the borrowing and returning process

-- 1. Update the `borrow_requests` table to include 'borrowed' status
ALTER TABLE `borrow_requests`
  MODIFY `status` ENUM('pending', 'approved', 'rejected', 'borrowed', 'returned') NOT NULL DEFAULT 'pending';

-- 2. Add a constraint to prevent quantity <= 0 in borrow requests
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `check_quantity` CHECK (`quantity` > 0);

-- 3. Add foreign key constraints where missing
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrow_requests_ibfk_3` FOREIGN KEY (`office_id`) REFERENCES `offices` (`id`) ON DELETE CASCADE;

-- 4. Ensure that the `assets` table has a proper status enum
ALTER TABLE `assets`
  MODIFY `status` ENUM('available', 'borrowed', 'in use', 'damaged', 'disposed', 'unserviceable', 'unavailable', 'lost', 'pending') NOT NULL DEFAULT 'available';

