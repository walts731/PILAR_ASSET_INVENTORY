-- Add guest_id column to borrow_form_submissions table
ALTER TABLE borrow_form_submissions ADD COLUMN IF NOT EXISTS guest_id VARCHAR(64) NULL AFTER guest_session_id;

-- Optional: Update existing records to use a default guest_id if needed
-- UPDATE borrow_form_submissions SET guest_id = 'default_guest_id' WHERE guest_id IS NULL;
