# Multiple Images Upload Implementation for Assets

## Overview
This implementation adds support for uploading up to 4 additional images per asset in the PILAR Asset Inventory system through the `create_red_tag.php` form.

## Database Changes

### New Column Added to `assets` Table
```sql
ALTER TABLE assets 
ADD COLUMN additional_images TEXT NULL 
COMMENT 'JSON array storing paths to up to 4 additional images for the asset';

UPDATE assets SET additional_images = '[]' WHERE additional_images IS NULL;
```

**Note:** Run the SQL script `add_images_column.sql` to add this column to your database.

## Files Modified/Created

### 1. **create_red_tag.php** - Enhanced with Multiple Image Upload
**Key Features Added:**
- File upload input supporting multiple images (up to 4)
- Real-time image preview with numbered indicators
- Server-side validation for file types, size, and count
- Display of existing images with remove functionality
- Proper error handling and user feedback

**Validation Rules:**
- **File Types:** JPEG, JPG, PNG, GIF, WebP only
- **File Size:** Maximum 5MB per image
- **File Count:** Maximum 4 images total
- **Unique Naming:** `asset_{asset_id}_{timestamp}_{index}.{extension}`

### 2. **remove_asset_image.php** - New AJAX Handler
**Purpose:** Handle removal of existing images
**Features:**
- Secure asset ownership validation
- Database update (removes from JSON array)
- Physical file deletion
- JSON response for AJAX calls

### 3. **get_asset_details.php** - Enhanced Data Retrieval
**Changes:**
- Added `additional_images` column to SELECT query
- Returns additional images data for frontend display

### 4. **modals/view_asset_modal.php** - Enhanced Asset Viewing
**New Features:**
- Dedicated "Asset Images" section
- Main image display (existing `image` column)
- Additional images gallery with thumbnails
- Click-to-enlarge functionality

### 5. **inventory.php** - Enhanced JavaScript
**New Features:**
- Dynamic image display logic
- Additional images parsing and rendering
- Image modal for full-size viewing
- Responsive image gallery layout

## Technical Implementation Details

### Image Storage Structure
```json
{
  "additional_images": [
    "asset_123_1640995200_0.jpg",
    "asset_123_1640995200_1.png",
    "asset_123_1640995200_2.gif"
  ]
}
```

### File Upload Process
1. **Validation:** Check file type, size, and count
2. **Naming:** Generate unique filename with asset ID and timestamp
3. **Storage:** Save to `../img/assets/` directory
4. **Database:** Update `additional_images` JSON array
5. **Feedback:** Display success/error messages

### Image Display Logic
1. **Main Image:** Uses existing `image` column
2. **Additional Images:** Parses JSON array from `additional_images`
3. **Responsive Layout:** Adapts to screen size
4. **Modal View:** Click thumbnails for full-size view

## User Interface Features

### Upload Form
- **Multiple File Selection:** Native HTML5 multiple file input
- **Live Preview:** Shows selected images before upload
- **Progress Indicators:** Numbered thumbnails
- **Validation Messages:** Real-time feedback

### Existing Images Management
- **Thumbnail Gallery:** Shows current additional images
- **Remove Buttons:** Individual image removal
- **Confirmation Dialogs:** Prevent accidental deletion

### Asset Viewing
- **Organized Layout:** Separate sections for main and additional images
- **Responsive Design:** Works on all screen sizes
- **Full-Size Viewing:** Modal popup for detailed inspection

## Security Features

### File Upload Security
- **Type Validation:** Server-side MIME type checking
- **Size Limits:** 5MB maximum per file
- **Path Sanitization:** Prevents directory traversal
- **Unique Naming:** Prevents filename conflicts

### Access Control
- **Session Validation:** User must be logged in
- **Asset Ownership:** Only authorized users can modify
- **CSRF Protection:** Form-based submissions only

## Error Handling

### Upload Errors
- Invalid file types
- File size exceeded
- Upload failures
- Directory permissions

### Display Errors
- Missing images
- Corrupted JSON data
- Network failures
- Permission issues

## Performance Considerations

### Optimizations
- **Lazy Loading:** Images loaded on demand
- **Thumbnail Generation:** Consistent sizing
- **JSON Storage:** Efficient data structure
- **Minimal Queries:** Single database update

### Limitations
- **File Count:** Maximum 4 additional images
- **File Size:** 5MB per image limit
- **Storage:** Local filesystem only
- **Formats:** Common web formats only

## Usage Instructions

### For Users
1. **Navigate** to Red Tag creation form
2. **Select Images** using the file input (up to 4)
3. **Preview** selected images before submission
4. **Submit Form** to upload and save
5. **Manage Images** using remove buttons if needed

### For Administrators
1. **Run SQL Script** to add database column
2. **Set Permissions** on upload directory (755)
3. **Monitor Storage** space usage
4. **Backup Images** regularly

## Troubleshooting

### Common Issues
1. **Upload Fails:** Check directory permissions
2. **Images Not Showing:** Verify file paths
3. **Large Files:** Reduce image size
4. **Browser Issues:** Clear cache and cookies

### Debug Steps
1. Check PHP error logs
2. Verify database column exists
3. Test file permissions
4. Validate JSON structure

## Future Enhancements

### Potential Improvements
- **Image Compression:** Automatic resize/compress
- **Cloud Storage:** AWS S3 or similar integration
- **Bulk Upload:** Multiple assets at once
- **Image Editing:** Basic crop/rotate functionality
- **Metadata Extraction:** EXIF data capture

This implementation provides a robust, secure, and user-friendly solution for managing multiple images per asset in the PILAR Asset Inventory system.
