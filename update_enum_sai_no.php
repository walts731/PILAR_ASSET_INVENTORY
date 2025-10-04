<?php
/**
 * Update enum values to include sai_no in tag_formats and tag_counters tables
 */

require_once 'connect.php';

try {
    // Update tag_formats table enum
    $sql1 = "ALTER TABLE tag_formats MODIFY COLUMN tag_type ENUM('red_tag','ics_no','itr_no','par_no','ris_no','inventory_tag','asset_code','serial_no','sai_no') DEFAULT NULL";
    
    if ($conn->query($sql1)) {
        echo "Successfully updated tag_formats enum to include sai_no.\n";
    } else {
        echo "Error updating tag_formats enum: " . $conn->error . "\n";
    }
    
    // Update tag_counters table enum
    $sql2 = "ALTER TABLE tag_counters MODIFY COLUMN tag_type ENUM('red_tag','ics_no','itr_no','par_no','ris_no','inventory_tag','asset_code','serial_no','sai_no') DEFAULT NULL";
    
    if ($conn->query($sql2)) {
        echo "Successfully updated tag_counters enum to include sai_no.\n";
    } else {
        echo "Error updating tag_counters enum: " . $conn->error . "\n";
    }
    
    echo "Database schema updated successfully!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
