<?php
/**
 * Setup script for the notification system
 * This script will create the necessary database tables and initialize default notification types
 */

// Include database connection
require_once 'connect.php';

// Include Notification class
require_once 'includes/classes/Notification.php';

// Check if we can connect to the database
if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "Starting notification system setup...\n";

// Read the SQL file
$sqlFile = __DIR__ . '/database/create_notification_tables.sql';
if (!file_exists($sqlFile)) {
    die("Error: SQL file not found at $sqlFile\n");
}

$sql = file_get_contents($sqlFile);

// Split into individual queries
$queries = explode(';', $sql);
$success = true;
$executed = 0;

// Execute each query
foreach ($queries as $query) {
    $query = trim($query);
    if (!empty($query)) {
        try {
            if ($conn->query($query) === TRUE) {
                $executed++;
            } else {
                echo "Error executing query: " . $conn->error . "\n";
                $success = false;
                break;
            }
        } catch (Exception $e) {
            // Ignore "table already exists" errors
            if (strpos($e->getMessage(), 'already exists') === false) {
                echo "Error: " . $e->getMessage() . "\n";
                $success = false;
                break;
            }
        }
    }
}

if ($success) {
    echo "Successfully executed $executed SQL queries.\n";
    
    // Initialize notification types
    $notification = new Notification($conn);
    if ($notification->initializeNotificationTypes()) {
        echo "Successfully initialized notification types.\n";
        
        // Create a test notification
        $testTitle = 'System Notification';
        $testMessage = 'The notification system has been successfully set up!';
        
        // Get any user ID to send the test notification to (not just admin)
        $result = $conn->query("SELECT id FROM users ORDER BY id LIMIT 1");
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $notificationId = $notification->create(
                'system_alert',
                $testTitle,
                $testMessage,
                null,
                null,
                $user['id'],
                1 // Expires in 1 day
            );
            
            if ($notificationId) {
                echo "✅ Test notification created successfully with ID: $notificationId\n";
            } else {
                echo "⚠️  Failed to create test notification. This might be because the notification type already exists or there was an error.\n";
                echo "You can safely ignore this warning if the notification system is already set up.\n";
            }
        } else {
            echo "ℹ️  No users found in the database. Please create a user first to receive test notifications.\n";
            echo "You can still use the notification system, but you won't receive any notifications until you create a user.\n";
        }
        
        echo "\n✅ Notification system setup completed successfully!\n";
        echo "You can now use the notification dropdown in the top navigation.\n";
        echo "To test the notification system, log in as a user and check the notification bell icon.\n";
    } else {
        echo "Failed to initialize notification types. Check error logs for details.\n";
    }
} else {
    echo "There were errors during the setup process. Please check the error messages above.\n";
}

// Close the database connection
$conn->close();
