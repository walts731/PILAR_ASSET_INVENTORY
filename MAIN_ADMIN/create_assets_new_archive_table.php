<?php
require_once '../connect.php';

// Create assets_new_archive table if it doesn't exist
$create_table_sql = "
CREATE TABLE IF NOT EXISTS assets_new_archive (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    unit VARCHAR(50) NOT NULL,
    office_id INT,
    ics_id INT,
    par_id INT,
    date_created DATETIME,
    archived_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    deletion_reason VARCHAR(255),
    archived_by INT,
    INDEX idx_original_id (id),
    INDEX idx_archived_at (archived_at),
    INDEX idx_office_id (office_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

try {
    if ($conn->query($create_table_sql)) {
        echo "assets_new_archive table created successfully or already exists.\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

// Also check if assets_archive table has the deletion_reason column
$check_column_sql = "SHOW COLUMNS FROM assets_archive LIKE 'deletion_reason'";
$result = $conn->query($check_column_sql);

if ($result->num_rows == 0) {
    // Add deletion_reason column to assets_archive if it doesn't exist
    $add_column_sql = "ALTER TABLE assets_archive ADD COLUMN deletion_reason VARCHAR(255) AFTER archived_at";
    try {
        if ($conn->query($add_column_sql)) {
            echo "deletion_reason column added to assets_archive table.\n";
        } else {
            echo "Error adding column: " . $conn->error . "\n";
        }
    } catch (Exception $e) {
        echo "Exception adding column: " . $e->getMessage() . "\n";
    }
} else {
    echo "deletion_reason column already exists in assets_archive table.\n";
}

$conn->close();
echo "Database setup completed.\n";
?>
