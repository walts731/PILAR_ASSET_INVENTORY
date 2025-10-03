-- Create tag_formats table for managing automatic tag generation
CREATE TABLE IF NOT EXISTS tag_formats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_type ENUM('red_tag', 'ics_no', 'itr_no', 'par_no', 'ris_no', 'inventory_tag') NOT NULL UNIQUE,
    format_template VARCHAR(255) NOT NULL COMMENT 'Template like PAR-{YYYY}-{####}',
    current_number INT DEFAULT 1 COMMENT 'Current increment number',
    prefix VARCHAR(100) DEFAULT '' COMMENT 'Static prefix part',
    suffix VARCHAR(100) DEFAULT '' COMMENT 'Static suffix part',
    increment_digits INT DEFAULT 4 COMMENT 'Number of digits for increment (e.g., 4 = 0001)',
    date_format VARCHAR(50) DEFAULT 'YYYY' COMMENT 'Date format in template (YYYY, MM, DD)',
    reset_on_change BOOLEAN DEFAULT TRUE COMMENT 'Reset counter when prefix/format changes',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default tag formats (prefix + digits only)
INSERT INTO tag_formats (tag_type, format_template, prefix, increment_digits, date_format) VALUES
('red_tag', 'RT-{####}', 'RT-', 4, ''),
('ics_no', 'ICS-{####}', 'ICS-', 4, ''),
('itr_no', 'ITR-{####}', 'ITR-', 4, ''),
('par_no', 'PAR-{####}', 'PAR-', 4, ''),
('ris_no', 'RIS-{####}', 'RIS-', 4, ''),
('inventory_tag', 'INV-{####}', 'INV-', 4, '');

-- Create tag_counters table to track increments per format and year
CREATE TABLE IF NOT EXISTS tag_counters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_type ENUM('red_tag', 'ics_no', 'itr_no', 'par_no', 'ris_no', 'inventory_tag') NOT NULL,
    year_period VARCHAR(10) NOT NULL COMMENT 'Year or period (e.g., 2025)',
    prefix_hash VARCHAR(32) NOT NULL COMMENT 'MD5 hash of current prefix for reset detection',
    current_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tag_year_prefix (tag_type, year_period, prefix_hash)
);
