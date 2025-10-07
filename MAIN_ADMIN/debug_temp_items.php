<?php
session_start();
require_once '../connect.php';

echo "Debug: Temp Items Fetching\n";
echo "==========================\n\n";

// Check session
echo "Session Check:\n";
echo "- user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "\n";
echo "- session_id: " . session_id() . "\n";
echo "- id: " . (isset($_SESSION['id']) ? $_SESSION['id'] : 'NOT SET') . "\n\n";

// Include helper functions
include 'load_temp_iirup_items.php';

// Test the function
echo "Testing getTempIIRUPItems function:\n";
$temp_items = getTempIIRUPItems($conn);

echo "- Found " . count($temp_items) . " temp items\n\n";

if (!empty($temp_items)) {
    echo "Temp Items Data:\n";
    echo "----------------\n";
    foreach ($temp_items as $index => $item) {
        echo "Item " . ($index + 1) . ":\n";
        foreach ($item as $key => $value) {
            echo "  $key: " . ($value ?? 'NULL') . "\n";
        }
        echo "\n";
    }
} else {
    echo "No temp items found. Possible reasons:\n";
    echo "1. No records in temp_iirup_items table\n";
    echo "2. Session user_id or id not set\n";
    echo "3. Records don't match current user/session\n\n";
    
    // Check if table exists and has data
    echo "Checking temp_iirup_items table:\n";
    $result = $conn->query("SELECT COUNT(*) as total FROM temp_iirup_items");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "- Total records in table: " . $row['total'] . "\n";
    } else {
        echo "- Error querying table: " . $conn->error . "\n";
    }
}

// Test table generation
echo "\nTesting generateIIRUPTableRows function:\n";
echo "----------------------------------------\n";
$preselected_asset = null; // No preselected asset for this test
$table_html = generateIIRUPTableRows($preselected_asset, $temp_items);

echo "Generated HTML length: " . strlen($table_html) . " characters\n";
echo "Contains <tr> tags: " . (strpos($table_html, '<tr') !== false ? 'YES' : 'NO') . "\n";
echo "Number of rows: " . substr_count($table_html, '<tr') . "\n";
?>
