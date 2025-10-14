-- Add guest identification fields to borrow_form_submissions table
ALTER TABLE `borrow_form_submissions`
ADD COLUMN `guest_session_id` VARCHAR(255) NOT NULL AFTER `submission_number`,
ADD COLUMN `guest_email` VARCHAR(255) DEFAULT NULL AFTER `guest_session_id`,
ADD KEY `guest_session_id` (`guest_session_id`),
ADD KEY `guest_email` (`guest_email`);
