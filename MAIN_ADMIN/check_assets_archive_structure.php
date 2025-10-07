<?php
require_once '../connect.php';

echo "Checking assets_archive table structure...\n\n";

// Check if assets_archive table exists
$check_table = $conn->query("SHOW TABLES LIKE 'assets_archive'");
if ($check_table->num_rows == 0) {
    echo "assets_archive table does not exist. Creating it...\n";
    
    // Create assets_archive table based on assets table structure
    $create_sql = "CREATE TABLE assets_archive LIKE assets";
    if ($conn->query($create_sql)) {
        echo "assets_archive table created successfully.\n";
        
        // Add archive-specific columns
        $alter_sql = "ALTER TABLE assets_archive 
                     ADD COLUMN archived_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                     ADD COLUMN deletion_reason VARCHAR(255),
                     ADD COLUMN archived_by INT";
        
        if ($conn->query($alter_sql)) {
            echo "Archive-specific columns added.\n";
        } else {
            echo "Error adding archive columns: " . $conn->error . "\n";
        }
    } else {
        echo "Error creating assets_archive table: " . $conn->error . "\n";
    }
} else {
    echo "assets_archive table exists.\n";
}

// Show current structure
echo "\nCurrent assets_archive table structure:\n";
$result = $conn->query("DESCRIBE assets_archive");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error describing table: " . $conn->error . "\n";
}

// Show current assets table structure for comparison
echo "\nCurrent assets table structure:\n";
$result = $conn->query("DESCRIBE assets");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Error describing assets table: " . $conn->error . "\n";
}

$conn->close();
?>
