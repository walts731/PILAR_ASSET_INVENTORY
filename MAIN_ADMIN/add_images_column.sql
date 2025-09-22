-- Add additional_images column to assets table
-- This column will store JSON array of image paths for up to 4 additional images

ALTER TABLE assets 
ADD COLUMN additional_images TEXT NULL 
COMMENT 'JSON array storing paths to up to 4 additional images for the asset';

-- Update existing records to have empty JSON array
UPDATE assets SET additional_images = '[]' WHERE additional_images IS NULL;
