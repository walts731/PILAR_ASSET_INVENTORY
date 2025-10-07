<?php
require_once '../connect.php';

// Create temp_iirup_items table
$sql = "CREATE TABLE IF NOT EXISTS temp_iirup_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    asset_id INT NOT NULL,
    date_acquired DATE,
    particulars TEXT,
    property_no VARCHAR(255),
    quantity INT DEFAULT 1,
    unit VARCHAR(50),
    unit_cost DECIMAL(10,2),
    office VARCHAR(255),
    code VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE
)";

if ($conn->query($sql) === TRUE) {
    echo "Table temp_iirup_items created successfully or already exists.";
} else {
    echo "Error creating table: " . $conn->error;
}

$conn->close();
?>
