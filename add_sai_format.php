<?php
/**
 * Add SAI No. format to tag_formats system
 * This script adds the SAI number format for RIS forms
 */

require_once 'connect.php';

try {
    // Check if sai_no format already exists
    $check_stmt = $conn->prepare("SELECT id FROM tag_formats WHERE tag_type = 'sai_no'");
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();
    
    if ($existing) {
        echo "SAI No. format already exists in tag_formats table.\n";
        exit();
    }
    
    // Add sai_no format to tag_formats table
    $insert_stmt = $conn->prepare("
        INSERT INTO tag_formats 
        (tag_type, format_template, current_number, prefix, suffix, increment_digits, date_format, reset_on_change, is_active, created_at, updated_at) 
        VALUES 
        ('sai_no', 'SAI-{YYYY}-{####}', 1, 'SAI-', '', 4, 'YYYY', 1, 1, NOW(), NOW())
    ");
    
    if ($insert_stmt->execute()) {
        echo "Successfully added SAI No. format to tag_formats table.\n";
        echo "Format: SAI-{YYYY}-{####} (e.g., SAI-2025-0001)\n";
        
        // Also add to tag_counters table for proper tracking
        $counter_stmt = $conn->prepare("
            INSERT INTO tag_counters 
            (tag_type, year_period, prefix_hash, current_count, created_at, updated_at) 
            VALUES 
            ('sai_no', 'global', ?, 0, NOW(), NOW())
        ");
        
        $prefix_hash = md5('SAI-');
        $counter_stmt->bind_param("s", $prefix_hash);
        
        if ($counter_stmt->execute()) {
            echo "Successfully initialized SAI No. counter.\n";
        } else {
            echo "Warning: Failed to initialize SAI No. counter: " . $counter_stmt->error . "\n";
        }
        
        $counter_stmt->close();
        
    } else {
        echo "Error adding SAI No. format: " . $insert_stmt->error . "\n";
    }
    
    $insert_stmt->close();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

$conn->close();
?>
