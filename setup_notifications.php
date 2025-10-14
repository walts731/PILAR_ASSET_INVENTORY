<?php
/**
 * Setup script for Notifications functionality
 * This script creates the necessary database tables and stored procedures
 */

require_once 'connect.php';

try {
    // Read and execute the SQL file
    $sql_file = __DIR__ . '/database/create_notification_tables.sql';
    
    if (!file_exists($sql_file)) {
        throw new Exception("SQL file not found: " . $sql_file);
    }
    
    // Read the SQL file
    $sql_content = file_get_contents($sql_file);
    
    // Split SQL statements by semicolon and execute each one
    $statements = array_filter(
        array_map('trim', explode(';', $sql_content)),
        function($stmt) {
            return !empty($stmt) && !preg_match('/^--/', $stmt);
        }
    );
    
    // Start transaction
    $conn->begin_transaction();
    
    // Execute each statement
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            if (!$conn->query($statement)) {
                throw new Exception("Error executing statement: " . $conn->error . "\nStatement: " . $statement);
            }
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    echo "Notification tables and procedures created successfully!\n";
    
} catch (Exception $e) {
    // Rollback transaction on error
    if (isset($conn) && $conn->in_transaction) {
        $conn->rollback();
    }
    
    die("Error setting up notifications: " . $e->getMessage() . "\n");
}

// Test the notification system
try {
    require_once 'includes/classes/Notification.php';
    $notification = new Notification($conn);
    
    // Test creating a notification
    $testTitle = 'System Notification';
    $testMessage = 'The notification system has been successfully set up!';
    
    // Get an admin user ID to send the test notification to
    $result = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $userId = $admin['id'];
        
        // Create a test notification
        $notificationId = $notification->create(
            'system_alert',
            $testTitle,
            $testMessage,
            null,
            null,
            $userId,
            1 // Expires in 1 day
        );
        
        if ($notificationId) {
            echo "Test notification created successfully with ID: $notificationId\n";
            echo "You should see this notification in the notification dropdown.\n";
        } else {
            echo "Failed to create test notification. Check error logs for details.\n";
        }
    } else {
        echo "No admin user found to send test notification to.\n";
    }
    
} catch (Exception $e) {
    echo "Error testing notification system: " . $e->getMessage() . "\n";
}

// Check if there are any existing notifications
$result = $conn->query("SELECT COUNT(*) as count FROM notifications");
if ($result) {
    $count = $result->fetch_assoc()['count'];
    echo "Total notifications in the system: $count\n";
}

// Provide next steps
echo "\nNext steps:\n";
echo "1. The notification system is now set up.\n";
echo "2. You can now use the notification dropdown in the top navigation.\n";
echo "3. To test the system, try creating a borrow request or other actions that should trigger notifications.\n\n";
