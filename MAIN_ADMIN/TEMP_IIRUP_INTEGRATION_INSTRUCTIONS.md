# IIRUP Form Temporary Items Integration Instructions

## Overview
The IIRUP form now needs to fetch content from the `temp_iirup_items` table and display it in the table while preserving existing pre-selection functionality.

## Files Created
1. `load_temp_iirup_items.php` - Contains helper functions for temp items functionality

## Integration Steps

### Step 1: Add temp items fetching to iirup_form.php
Add this code after the existing preselected asset fetching logic (around line 76):

```php
// Fetch temporary IIRUP items for current user/session - ADD THIS
include 'load_temp_iirup_items.php';
$temp_items = getTempIIRUPItems($conn);
```

### Step 2: Add alert banner for temp items
The alert banner code has already been added to show temporary items count with Load/Clear buttons.

### Step 3: Replace table body generation
Replace the existing table body generation loop (around line 565) with:

```php
<tbody>
    <?php echo generateIIRUPTableRows($preselected_asset, $temp_items); ?>
</tbody>
```

### Step 4: Add JavaScript functions
Add this before the closing `</script>` tag in iirup_form.php:

```php
<?php echo getTempItemsJavaScript($temp_items); ?>
```

## Key Features
- **Preserves QR Code Pre-selection**: Existing QR code functionality continues to work
- **Displays Temp Items**: All temporary items from temp_iirup_items table are shown
- **Load Items Button**: Users can load temp items into the form
- **Clear Items Button**: Users can clear all temporary items
- **Auto-population**: Temp items automatically populate form fields
- **Duplicate Prevention**: Maintains existing selectedAssetIds functionality

## User Experience
1. User adds assets to temp table from asset detail views
2. User opens IIRUP form
3. Alert shows "X items ready to be loaded"
4. User clicks "Load Items" - all temp items populate the form
5. User can clear temp items or proceed with form submission
6. QR code pre-selection still works independently

## Technical Benefits
- Non-intrusive integration
- Preserves all existing functionality
- Uses helper functions for clean code organization
- Maintains data integrity
- Compatible with existing JavaScript
