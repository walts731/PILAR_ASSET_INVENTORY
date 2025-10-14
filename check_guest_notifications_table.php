<?php
// check_guest_notifications_table.php
require_once 'connect.php';

$result = $conn->query("SHOW TABLES LIKE 'guest_notifications'");
if ($result->num_rows > 0) {
    echo "✓ guest_notifications table exists!\n";

    // Check table structure
    $result = $conn->query("DESCRIBE guest_notifications");
    if ($result) {
        echo "Table structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- {$row['Field']}: {$row['Type']} ({$row['Key']})\n";
        }
    }
} else {
    echo "✗ guest_notifications table does not exist\n";
}

$conn->close();
?>
