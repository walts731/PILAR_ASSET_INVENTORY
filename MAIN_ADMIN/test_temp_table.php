<?php
require_once '../connect.php';

// Test if temp_iirup_items table exists and show its structure
$sql = "DESCRIBE temp_iirup_items";
$result = $conn->query($sql);

if ($result) {
    echo "✅ temp_iirup_items table exists!\n\n";
    echo "Table Structure:\n";
    echo "================\n";
    while ($row = $result->fetch_assoc()) {
        echo sprintf("%-15s %-20s %-10s %-10s %-15s %s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'], 
            $row['Extra']
        );
    }
    
    // Check if table is empty
    $count_sql = "SELECT COUNT(*) as count FROM temp_iirup_items";
    $count_result = $conn->query($count_sql);
    $count = $count_result->fetch_assoc()['count'];
    
    echo "\nCurrent records in table: $count\n";
    
} else {
    echo "❌ temp_iirup_items table does not exist!\n";
    echo "Error: " . $conn->error . "\n";
}

$conn->close();
?>
