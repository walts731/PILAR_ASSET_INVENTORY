<?php
// test_guest_notifications.php
// Test script to verify guest notifications are working

require_once 'connect.php';
require_once 'includes/classes/GuestNotification.php';

// Test data
$test_guest_id = 'test_guest_' . time();
$test_submission_id = 999999; // Test submission ID

echo "=== Guest Notification System Test ===\n\n";

// 1. Create a test guest
echo "1. Creating test guest...\n";
$create_guest_sql = "INSERT INTO guests (guest_id, email, name, contact, barangay, first_login) VALUES (?, 'test@example.com', 'Test User', '09123456789', 'Test Barangay', NOW())";
$create_stmt = $conn->prepare($create_guest_sql);
$create_stmt->bind_param("s", $test_guest_id);

if ($create_stmt->execute()) {
    $guest_db_id = $conn->insert_id;
    echo "✓ Test guest created with ID: {$guest_db_id}\n";
} else {
    echo "✗ Failed to create test guest\n";
    exit;
}

// 2. Test sending approval notification
echo "\n2. Testing approval notification...\n";
$notification = new GuestNotification($conn);
$admin_name = 'Test Admin';

$result = $notification->sendBorrowRequestStatusUpdate(
    $test_submission_id,
    $test_guest_id,
    'approved',
    $admin_name
);

if ($result) {
    echo "✓ Approval notification sent successfully (ID: {$result})\n";
} else {
    echo "✗ Failed to send approval notification\n";
}

// 3. Test sending rejection notification
echo "\n3. Testing rejection notification...\n";
$result = $notification->sendBorrowRequestStatusUpdate(
    $test_submission_id,
    $test_guest_id,
    'rejected',
    $admin_name
);

if ($result) {
    echo "✓ Rejection notification sent successfully (ID: {$result})\n";
} else {
    echo "✗ Failed to send rejection notification\n";
}

// 4. Check unread count
echo "\n4. Checking unread notification count...\n";
$unread_count = $notification->getUnreadCount($test_guest_id);
echo "✓ Unread notifications: {$unread_count}\n";

// 5. Get notifications
echo "\n5. Retrieving notifications...\n";
$notifications = $notification->getGuestNotifications($test_guest_id, false, 10);
echo "✓ Total notifications: " . count($notifications) . "\n";

if (!empty($notifications)) {
    echo "Latest notifications:\n";
    foreach ($notifications as $notif) {
        echo "  - {$notif['title']} ({$notif['notification_type']}) - " . ($notif['is_read'] ? 'Read' : 'Unread') . "\n";
    }
}

// 6. Test marking as read
echo "\n6. Testing mark as read...\n";
if (!empty($notifications)) {
    $first_notif = $notifications[0];
    $result = $notification->markAsRead($first_notif['id'], $test_guest_id);
    echo "✓ Mark as read: " . ($result ? 'Success' : 'Failed') . "\n";

    // Check unread count again
    $unread_count = $notification->getUnreadCount($test_guest_id);
    echo "✓ Updated unread count: {$unread_count}\n";
}

// 7. Clean up test data
echo "\n7. Cleaning up test data...\n";
$cleanup_sql = "DELETE FROM guests WHERE guest_id = ?";
$cleanup_stmt = $conn->prepare($cleanup_sql);
$cleanup_stmt->bind_param("s", $test_guest_id);

if ($cleanup_stmt->execute()) {
    echo "✓ Test guest deleted\n";
} else {
    echo "✗ Failed to delete test guest\n";
}

$conn->close();

echo "\n=== Test Complete ===\n";
echo "If all checks show ✓, the guest notification system is working correctly!\n";
echo "\nTo test with actual borrowing approval/rejection:\n";
echo "1. Have a guest submit a borrow request\n";
echo "2. Go to MAIN_ADMIN/borrowing.php\n";
echo "3. Click Accept or Decline on a pending request\n";
echo "4. The guest should see a notification in their notification bell\n";
?>
