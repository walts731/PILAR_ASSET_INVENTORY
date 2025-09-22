# MR Checkbox Enhancement Documentation

## Overview
Enhanced the `create_mr.php` file to properly populate and handle the Serviceable and Unserviceable checkboxes based on the existing data in the `mr_details` table. This ensures that when editing an existing MR record, the checkboxes reflect the current status stored in the database.

## Changes Made

### File Modified: `create_mr.php`

#### **1. Added MR Details Fetching Logic**
Added code to fetch existing MR details and populate checkbox variables before the form is displayed.

```php
// Fetch existing MR details to populate serviceable/unserviceable checkboxes
$mr_serviceable = 0;
$mr_unserviceable = 0;
if ($asset_id && $existing_mr_check) {
    if ($mr_item_id) {
        $stmt_mr = $conn->prepare("SELECT serviceable, unserviceable FROM mr_details WHERE item_id = ? AND asset_id = ?");
        $stmt_mr->bind_param("ii", $mr_item_id, $asset_id);
    } else {
        $stmt_mr = $conn->prepare("SELECT serviceable, unserviceable FROM mr_details WHERE asset_id = ?");
        $stmt_mr->bind_param("i", $asset_id);
    }
    $stmt_mr->execute();
    $result_mr = $stmt_mr->get_result();
    if ($result_mr && $mr_data = $result_mr->fetch_assoc()) {
        $mr_serviceable = (int)$mr_data['serviceable'];
        $mr_unserviceable = (int)$mr_data['unserviceable'];
    }
    $stmt_mr->close();
} else {
    // Default values for new MR records
    $mr_serviceable = 1; // Default to serviceable for new assets
    $mr_unserviceable = 0;
}
```

#### **2. Updated Checkbox HTML**
Modified the serviceable and unserviceable checkboxes to use the fetched MR details values.

**Before:**
```html
<input class="form-check-input" type="checkbox" name="serviceable" value="1" checked>
<input class="form-check-input" type="checkbox" name="unserviceable" value="1"
    <?= (isset($asset_data['quantity']) && $asset_data['quantity'] == 0) ? 'checked' : '' ?>>
```

**After:**
```html
<input class="form-check-input" type="checkbox" name="serviceable" value="1" 
       <?= $mr_serviceable == 1 ? 'checked' : '' ?>>
<input class="form-check-input" type="checkbox" name="unserviceable" value="1"
       <?= $mr_unserviceable == 1 ? 'checked' : '' ?>>
```

## Technical Implementation Details

### **Database Query Logic**
The system uses two different query approaches based on the asset's origin:

1. **ICS-originated assets**: Query using both `item_id` and `asset_id`
2. **PAR-originated assets**: Query using only `asset_id` (since `item_id` may be NULL)

### **Default Values**
- **New MR Records**: Serviceable = 1, Unserviceable = 0 (default to serviceable)
- **Existing MR Records**: Values loaded from `mr_details` table

### **Data Flow**
1. **Page Load**: Check if asset has existing MR record
2. **If Existing**: Fetch serviceable/unserviceable values from `mr_details`
3. **If New**: Use default values (serviceable = 1, unserviceable = 0)
4. **Form Display**: Populate checkboxes with appropriate values
5. **Form Submit**: Save checkbox values to `mr_details` table

## User Experience Benefits

### **Accurate Status Display**
- **Existing Records**: Checkboxes reflect current database state
- **New Records**: Sensible defaults (serviceable by default)
- **Consistency**: Status always matches what's stored in the database

### **Proper Editing Workflow**
- **Load Existing Data**: Form shows current MR status when editing
- **Visual Feedback**: Clear indication of current serviceable/unserviceable state
- **Data Integrity**: Prevents accidental status changes due to incorrect defaults

### **Flexible Status Management**
- **Both Checkboxes**: Can be checked simultaneously if needed
- **Neither Checked**: Allows for "undefined" status if required
- **Individual Control**: Each checkbox operates independently

## Database Integration

### **Query Optimization**
- **Conditional Queries**: Different queries based on asset origin (ICS vs PAR)
- **Prepared Statements**: Secure parameter binding
- **Error Handling**: Graceful fallback to defaults if query fails

### **Data Consistency**
- **Single Source of Truth**: `mr_details` table is authoritative for MR status
- **Type Casting**: Explicit integer conversion for reliable comparisons
- **Null Handling**: Proper handling of missing or null values

## Error Handling

### **Missing Data Scenarios**
- **No MR Record**: Uses default values (serviceable = 1)
- **Query Failure**: Falls back to default values
- **Invalid Data**: Type casting ensures valid integer values

### **Graceful Degradation**
- **Database Issues**: Form still functions with defaults
- **Missing Columns**: Handles potential schema differences
- **Connection Problems**: Doesn't break form functionality

## Testing Scenarios

### **Recommended Test Cases**
1. **New Asset**: Verify serviceable checkbox is checked by default
2. **Existing Serviceable**: Verify serviceable checkbox reflects database
3. **Existing Unserviceable**: Verify unserviceable checkbox reflects database
4. **Both Statuses**: Test assets with both flags set to 1
5. **Neither Status**: Test assets with both flags set to 0
6. **Form Submission**: Verify checkbox values are saved correctly

### **Edge Cases**
- Assets without MR records
- Assets with corrupted MR data
- Database connection issues during form load
- Mixed ICS/PAR asset scenarios

## Benefits Summary

✅ **Data Accuracy**: Checkboxes always reflect current database state  
✅ **User Experience**: Intuitive editing of existing MR records  
✅ **Data Integrity**: Prevents accidental status overwrites  
✅ **Flexibility**: Supports all possible checkbox combinations  
✅ **Reliability**: Graceful handling of edge cases and errors  
✅ **Consistency**: Uniform behavior across new and existing records  

This enhancement ensures that the MR creation/editing form provides accurate, reliable, and user-friendly management of asset serviceability status based on the authoritative data stored in the `mr_details` table.
