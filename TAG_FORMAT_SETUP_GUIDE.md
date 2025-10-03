# Manage Tag Format System - Setup Guide

## Overview
The Manage Tag Format system provides automatic tag generation for all forms in the PILAR Asset Inventory system. It replaces manual tag entry with intelligent, customizable automatic numbering.

## Installation Steps

### 1. Database Setup
Run the SQL script to create required tables:

```sql
-- Execute this in your MySQL database
SOURCE create_tag_formats_table.sql;
```

Or manually run:
```sql
-- Create tag_formats table
CREATE TABLE IF NOT EXISTS tag_formats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_type ENUM('red_tag', 'ics_no', 'itr_no', 'par_no', 'ris_no', 'inventory_tag') NOT NULL UNIQUE,
    format_template VARCHAR(255) NOT NULL,
    current_number INT DEFAULT 1,
    prefix VARCHAR(100) DEFAULT '',
    suffix VARCHAR(100) DEFAULT '',
    increment_digits INT DEFAULT 4,
    date_format VARCHAR(50) DEFAULT 'YYYY',
    reset_on_change BOOLEAN DEFAULT TRUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create tag_counters table
CREATE TABLE IF NOT EXISTS tag_counters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_type ENUM('red_tag', 'ics_no', 'itr_no', 'par_no', 'ris_no', 'inventory_tag') NOT NULL,
    year_period VARCHAR(10) NOT NULL,
    prefix_hash VARCHAR(32) NOT NULL,
    current_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_tag_year_prefix (tag_type, year_period, prefix_hash)
);

-- Insert default formats
INSERT INTO tag_formats (tag_type, format_template, prefix, increment_digits, date_format) VALUES
('red_tag', 'RT-{YYYY}-{####}', 'RT-', 4, 'YYYY'),
('ics_no', 'ICS-{YYYY}-{####}', 'ICS-', 4, 'YYYY'),
('itr_no', 'ITR-{YYYY}-{####}', 'ITR-', 4, 'YYYY'),
('par_no', 'PAR-{YYYY}-{####}', 'PAR-', 4, 'YYYY'),
('ris_no', 'RIS-{YYYY}-{####}', 'RIS-', 4, 'YYYY'),
('inventory_tag', 'INV-{YYYY}-{####}', 'INV-', 4, 'YYYY');
```

### 2. File Structure
Ensure these files are in place:

```
PILAR_ASSET_INVENTORY/
├── includes/
│   └── tag_format_helper.php          # Core helper functions
├── SYSTEM_ADMIN/
│   └── manage_tag_format.php          # Admin interface
├── create_tag_formats_table.sql       # Database setup
├── example_itr_integration.php        # Integration examples
└── TAG_FORMAT_SETUP_GUIDE.md         # This guide
```

### 3. Integration with Forms

#### A. Include Helper Functions
Add this to the top of your form processing files:

```php
require_once '../includes/tag_format_helper.php';
```

#### B. Replace Manual Tag Input
**OLD CODE (Remove):**
```php
$itr_no = $_POST['itr_no']; // Manual input
```

**NEW CODE (Use):**
```php
$itr_no = generateTag('itr_no'); // Automatic generation
```

#### C. Update Form Fields
**OLD HTML (Remove):**
```html
<input type="text" name="itr_no" class="form-control" placeholder="Enter ITR number" required>
```

**NEW HTML (Use):**
```html
<div class="form-group">
    <label class="form-label">ITR No. (Auto-generated)</label>
    <div class="input-group">
        <input type="text" class="form-control" value="<?= previewTag('itr_no') ?>" readonly>
        <span class="input-group-text">
            <i class="bi bi-magic" title="Auto-generated"></i>
        </span>
    </div>
    <small class="text-muted">This number will be automatically assigned when you save the form.</small>
</div>
```

## Form Integration Examples

### ITR Form Integration
File: `save_itr_items.php`

```php
// Replace manual ITR number with automatic generation
$itr_no = generateTag('itr_no');

// Insert ITR with auto-generated number
$stmt = $conn->prepare("INSERT INTO itr_form (itr_no, entity_name, ...) VALUES (?, ?, ...)");
$stmt->bind_param("ss...", $itr_no, $entity_name, ...);
```

### PAR Form Integration
File: `save_par_items.php`

```php
// Generate PAR number automatically
$par_no = generateTag('par_no');

// Insert PAR with auto-generated number
$stmt = $conn->prepare("INSERT INTO par_form (par_no, entity_name, ...) VALUES (?, ?, ...)");
$stmt->bind_param("ss...", $par_no, $entity_name, ...);
```

### ICS Form Integration
File: `save_ics_items.php`

```php
// Generate ICS number automatically
$ics_no = generateTag('ics_no');

// Insert ICS with auto-generated number
$stmt = $conn->prepare("INSERT INTO ics_form (ics_no, entity_name, ...) VALUES (?, ?, ...)");
$stmt->bind_param("ss...", $ics_no, $entity_name, ...);
```

### RIS Form Integration
File: `save_ris_items.php`

```php
// Generate RIS number automatically
$ris_no = generateTag('ris_no');

// Insert RIS with auto-generated number
$stmt = $conn->prepare("INSERT INTO ris_form (ris_no, entity_name, ...) VALUES (?, ?, ...)");
$stmt->bind_param("ss...", $ris_no, $entity_name, ...);
```

### Red Tag Integration
File: `create_red_tag.php`

```php
// Generate Red Tag number automatically
$red_tag_no = generateTag('red_tag');

// Insert Red Tag with auto-generated number
$stmt = $conn->prepare("INSERT INTO red_tags (tag_no, asset_id, ...) VALUES (?, ?, ...)");
$stmt->bind_param("si...", $red_tag_no, $asset_id, ...);
```

## Available Functions

### Core Functions

#### `generateTag($tagType)`
Generates and increments the next tag number.
```php
$itrNumber = generateTag('itr_no');
// Returns: ITR-2025-0001 (and increments counter)
```

#### `previewTag($tagType)`
Shows what the next tag will be without incrementing.
```php
$nextITR = previewTag('itr_no');
// Returns: ITR-2025-0001 (without incrementing)
```

### Tag Types
- `'red_tag'` - Red Tag numbers
- `'ics_no'` - ICS form numbers
- `'itr_no'` - ITR form numbers
- `'par_no'` - PAR form numbers
- `'ris_no'` - RIS form numbers
- `'inventory_tag'` - Inventory tag numbers

## Format Configuration

### Available Placeholders
- `{YYYY}` - Full year (2025)
- `{YY}` - Short year (25)
- `{MM}` - Month (01-12)
- `{DD}` - Day (01-31)
- `{####}` - Auto-increment (padded with zeros)

### Example Formats
- `PAR-{YYYY}-{####}` → PAR-2025-0001
- `ICS-{YY}{MM}-{###}` → ICS-2501-001
- `RT-{YYYYMMDD}-{##}` → RT-20250103-01

## Admin Interface

Access the management interface at:
```
http://localhost/pilar_asset_inventory/SYSTEM_ADMIN/manage_tag_format.php
```

### Features:
- **Live Preview** - See what the next tag will look like
- **Format Templates** - Customize tag formats with placeholders
- **Increment Control** - Set number of digits (3-6)
- **Date Formats** - Choose date format (YYYY, YY, YYYYMM, etc.)
- **Statistics** - View usage statistics and current counts
- **Reset on Change** - Automatically reset counters when prefix changes

## Migration from Old System

### 1. Backup Current Data
```sql
-- Backup existing tag data
CREATE TABLE backup_tags_2025 AS 
SELECT * FROM your_existing_tag_table;
```

### 2. Remove Old Tag Format Code
- Remove manual tag input fields from forms
- Remove old tag format validation
- Remove old increment logic

### 3. Update Form Processing
- Replace manual tag assignment with `generateTag()` calls
- Update form validation to remove tag number requirements
- Test each form type individually

### 4. Verify Integration
- Test tag generation for each form type
- Verify counters increment correctly
- Check year rollover behavior
- Test prefix change reset functionality

## Troubleshooting

### Common Issues

#### Tags Not Generating
```php
// Check if helper is included
require_once '../includes/tag_format_helper.php';

// Check database connection
if (!$conn) {
    die('Database connection failed');
}

// Check if tag format exists
$result = $conn->query("SELECT * FROM tag_formats WHERE tag_type = 'itr_no'");
if ($result->num_rows == 0) {
    die('Tag format not configured');
}
```

#### Duplicate Tag Numbers
```php
// Check for database transaction issues
$conn->begin_transaction();
try {
    $tag = generateTag('itr_no');
    // ... insert form data
    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
    throw $e;
}
```

#### Counter Not Incrementing
```sql
-- Check counter table
SELECT * FROM tag_counters WHERE tag_type = 'itr_no';

-- Reset counter if needed
DELETE FROM tag_counters WHERE tag_type = 'itr_no';
```

### Error Logging
Check PHP error logs for tag generation issues:
```bash
tail -f /var/log/php_errors.log | grep "Tag generation error"
```

## Security Considerations

### Database Permissions
Ensure the application user has appropriate permissions:
```sql
GRANT SELECT, INSERT, UPDATE ON tag_formats TO 'app_user'@'localhost';
GRANT SELECT, INSERT, UPDATE ON tag_counters TO 'app_user'@'localhost';
```

### Input Validation
Always validate tag types:
```php
$allowedTypes = ['red_tag', 'ics_no', 'itr_no', 'par_no', 'ris_no', 'inventory_tag'];
if (!in_array($tagType, $allowedTypes)) {
    throw new Exception('Invalid tag type');
}
```

## Performance Optimization

### Database Indexes
```sql
-- Add indexes for better performance
CREATE INDEX idx_tag_counters_lookup ON tag_counters (tag_type, year_period, prefix_hash);
CREATE INDEX idx_tag_formats_type ON tag_formats (tag_type, is_active);
```

### Caching
Consider caching format configurations:
```php
// Cache format data to reduce database queries
$formatCache = [];
function getCachedFormat($tagType) {
    global $formatCache;
    if (!isset($formatCache[$tagType])) {
        // Load from database
        $formatCache[$tagType] = loadFormatFromDB($tagType);
    }
    return $formatCache[$tagType];
}
```

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review PHP error logs
3. Verify database table structure
4. Test with simple examples first

## Version History

- **v1.0** - Initial release with basic tag generation
- **v1.1** - Added format templates and admin interface
- **v1.2** - Added statistics and preview functionality
- **v1.3** - Added integration examples and setup guide
