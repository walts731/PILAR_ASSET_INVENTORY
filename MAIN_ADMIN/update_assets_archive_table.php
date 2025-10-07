<?php
require_once '../connect.php';

echo "Updating assets_archive table structure to match assets table...\n\n";

// List of columns that should be added to assets_archive
$columns_to_add = [
    'employee_id' => 'INT(11)',
    'end_user' => 'VARCHAR(255)',
    'image' => 'VARCHAR(255)',
    'serial_no' => 'VARCHAR(255)',
    'code' => 'VARCHAR(255)',
    'property_no' => 'VARCHAR(255)',
    'model' => 'VARCHAR(255)',
    'brand' => 'VARCHAR(255)',
    'inventory_tag' => 'VARCHAR(255)',
    'asset_new_id' => 'INT(11)',
    'additional_images' => 'TEXT',
    'deletion_reason' => 'VARCHAR(255)'
];

foreach ($columns_to_add as $column => $type) {
    // Check if column exists
    $check_sql = "SHOW COLUMNS FROM assets_archive LIKE '$column'";
    $result = $conn->query($check_sql);
    
    if ($result->num_rows == 0) {
        // Column doesn't exist, add it
        $alter_sql = "ALTER TABLE assets_archive ADD COLUMN $column $type";
        if ($conn->query($alter_sql)) {
            echo "✓ Added column: $column ($type)\n";
        } else {
            echo "✗ Error adding column $column: " . $conn->error . "\n";
        }
    } else {
        echo "- Column $column already exists\n";
    }
}

echo "\nUpdated assets_archive table structure:\n";
$result = $conn->query("DESCRIBE assets_archive");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error describing table: " . $conn->error . "\n";
}

$conn->close();
echo "\nAssets archive table update completed.\n";
?>
