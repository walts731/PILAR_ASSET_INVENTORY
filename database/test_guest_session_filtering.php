<?php
// Test script to verify borrowing_history.php filtering by guest session
session_start();
require_once 'C:\xampp\htdocs\PILAR_ASSET_INVENTORY\connect.php';

// Simulate guest session
$_SESSION['is_guest'] = true;
$_SESSION['guest_email'] = 'guest@pilar.gov.ph';

echo "<h2>Testing Guest Session-Based Filtering</h2>";
echo "<p>Current Session ID: " . session_id() . "</p>";
echo "<p>Guest Email: " . $_SESSION['guest_email'] . "</p>";

// First, insert a test submission with the current session ID
$test_items = [
    [
        'asset_id' => 1,
        'thing' => 'MacBook Pro',
        'inventory_tag' => 'MBP-001',
        'property_no' => 'PROP-MBP',
        'category' => 'Computer Equipment',
        'qty' => '1',
        'remarks' => 'For development work'
    ]
];

$sql = "INSERT INTO borrow_form_submissions
        (submission_number, guest_session_id, guest_email, guest_name, date_borrowed, schedule_return, barangay, contact,
         releasing_officer, approved_by, items, status, submitted_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())";

$stmt = $conn->prepare($sql);
$submission_number = 'BFS-' . date('Ymd') . '-SESS-' . rand(100, 999);
$guest_session_id = session_id();
$guest_email = $_SESSION['guest_email'];
$guest_name = 'Session Test User';
$date_borrowed = date('Y-m-d');
$schedule_return = date('Y-m-d', strtotime('+5 days'));
$barangay = 'Test Barangay';
$contact = '09123456789';
$releasing_officer = 'Session Officer';
$approved_by = 'Session Approver';
$items_json = json_encode($test_items);

$stmt->bind_param('sssssssssss',
    $submission_number,
    $guest_session_id,
    $guest_email,
    $guest_name,
    $date_borrowed,
    $schedule_return,
    $barangay,
    $contact,
    $releasing_officer,
    $approved_by,
    $items_json
);

if ($stmt->execute()) {
    echo "<p>✅ Test submission inserted with Session ID: $guest_session_id</p>";
    echo "<p>Submission Number: $submission_number</p>";
} else {
    echo "<p>❌ Error inserting test data: " . $stmt->error . "</p>";
}
$stmt->close();

// Now test the getBorrowingHistory function
function getBorrowingHistory($conn) {
    $history = [];

    // Get current guest session ID
    $guest_session_id = session_id();

    echo "<p>🔍 Filtering by Session ID: $guest_session_id</p>";

    // Query submissions only for the current guest session
    $sql = "SELECT * FROM borrow_form_submissions WHERE guest_session_id = ? ORDER BY submitted_at DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $guest_session_id);
    $stmt->execute();
    $result = $stmt->get_result();

    echo "<p>📊 Found " . $result->num_rows . " submissions for this session.</p>";

    if ($result->num_rows > 0) {
        while ($submission = $result->fetch_assoc()) {
            $items = json_decode($submission['items'], true);
            echo "<h3>📄 Submission: " . $submission['submission_number'] . "</h3>";
            echo "<p>👤 Guest: " . $submission['guest_name'] . "</p>";
            echo "<p>📦 Items: " . count($items) . "</p>";

            // Create a separate history item for each asset in the submission
            foreach ($items as $index => $item) {
                $history_item = [
                    'id' => $submission['id'] . '_' . $index,
                    'submission_number' => $submission['submission_number'],
                    'asset_name' => $item['thing'],
                    'status' => $submission['status']
                ];
                $history[] = $history_item;
            }
        }
    }

    $stmt->close();
    return $history;
}

// Test the function
$borrowingHistory = getBorrowingHistory($conn);

echo "<h3>📋 Borrowing History Items: " . count($borrowingHistory) . "</h3>";
foreach ($borrowingHistory as $item) {
    echo "- 🏷️ " . $item['asset_name'] . " (Request: " . $item['submission_number'] . ", Status: " . $item['status'] . ")<br>";
}

$conn->close();
echo "<br><a href='../GUEST/borrowing_history.php'>👁️ View in Borrowing History Page</a>";
?>
