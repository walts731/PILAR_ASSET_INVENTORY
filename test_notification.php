<?php
// Include database connection
require_once 'connect.php';

// Include Notification class
require_once 'includes/classes/Notification.php';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if we can connect to the database
if (!$conn) {
    die("Database connection failed: " . $conn->connect_error);
}

echo "Testing notification system...\n";

// Create a new notification instance
$notification = new Notification($conn);

// Get the first user from the database
$result = $conn->query("SELECT id, username FROM users ORDER BY id LIMIT 1");
if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $userId = $user['id'];
    $username = $user['username'];
    
    echo "Found user: {$username} (ID: {$userId})\n";
    
    // Create a test notification
    $title = 'Test Notification';
    $message = 'This is a test notification to verify the notification system is working correctly.';
    
    echo "Creating test notification...\n";
    
    $notificationId = $notification->create(
        'system_alert',
        $title,
        $message,
        null,
        null,
        $userId,
        1 // Expires in 1 day
    );
    
    if ($notificationId) {
        echo "✅ Test notification created successfully with ID: {$notificationId}\n";
        
        // Try to retrieve the notification
        $notifications = $notification->getUserNotifications($userId, false, 1);
        if (!empty($notifications)) {
            echo "✅ Successfully retrieved the test notification.\n";
            echo "Title: " . $notifications[0]['title'] . "\n";
            echo "Message: " . $notifications[0]['message'] . "\n";
        } else {
            echo "⚠️  Failed to retrieve the test notification.\n";
        }
    } else {
        echo "❌ Failed to create test notification. Check error logs for details.\n";
    }
} else {
    echo "❌ No users found in the database. Please create a user first.\n";
}

// Close the database connection
$conn->close();

echo "Test completed.\n";
?>
