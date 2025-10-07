<?php
require_once '../connect.php';

echo "Checking temp_iirup_items table structure\n";
echo "==========================================\n\n";

// Check if table exists
$result = $conn->query("SHOW TABLES LIKE 'temp_iirup_items'");
if ($result->num_rows == 0) {
    echo "❌ Table 'temp_iirup_items' does not exist!\n";
    echo "You need to create it first.\n\n";
    
    echo "CREATE TABLE SQL:\n";
    echo "-----------------\n";
    echo "CREATE TABLE temp_iirup_items (\n";
    echo "    id INT AUTO_INCREMENT PRIMARY KEY,\n";
    echo "    user_id INT NOT NULL,\n";
    echo "    session_id VARCHAR(255) NOT NULL,\n";
    echo "    asset_id INT NOT NULL,\n";
    echo "    date_acquired DATE,\n";
    echo "    particulars TEXT,\n";
    echo "    property_no VARCHAR(255),\n";
    echo "    quantity INT DEFAULT 1,\n";
    echo "    unit_cost DECIMAL(10,2),\n";
    echo "    office VARCHAR(255),\n";
    echo "    code VARCHAR(255),\n";
    echo "    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n";
    echo "    INDEX idx_user_session (user_id, session_id),\n";
    echo "    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE\n";
    echo ");\n";
    exit;
}

// Show table structure
echo "✓ Table exists. Structure:\n";
echo "---------------------------\n";
$result = $conn->query("DESCRIBE temp_iirup_items");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo sprintf("%-20s %-20s %-10s %-10s %-20s %s\n", 
            $row['Field'], 
            $row['Type'], 
            $row['Null'], 
            $row['Key'], 
            $row['Default'], 
            $row['Extra']
        );
    }
} else {
    echo "Error describing table: " . $conn->error . "\n";
}

echo "\n";

// Show sample data
echo "Sample data (first 3 rows):\n";
echo "----------------------------\n";
$result = $conn->query("SELECT * FROM temp_iirup_items LIMIT 3");
if ($result && $result->num_rows > 0) {
    $first = true;
    while ($row = $result->fetch_assoc()) {
        if ($first) {
            echo "Columns: " . implode(", ", array_keys($row)) . "\n";
            echo str_repeat("-", 80) . "\n";
            $first = false;
        }
        echo "ID: {$row['id']}\n";
        foreach ($row as $key => $value) {
            if ($key != 'id') {
                echo "  $key: " . ($value ?? 'NULL') . "\n";
            }
        }
        echo "\n";
    }
} else {
    echo "No data found or error: " . $conn->error . "\n";
}

// Count total records
$result = $conn->query("SELECT COUNT(*) as total FROM temp_iirup_items");
if ($result) {
    $row = $result->fetch_assoc();
    echo "Total records: " . $row['total'] . "\n";
}
?>
