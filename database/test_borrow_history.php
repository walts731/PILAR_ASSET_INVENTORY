<?php
// Test script to verify borrowing_history.php works with the new borrow_form_submissions table
require_once 'C:\xampp\htdocs\PILAR_ASSET_INVENTORY\connect.php';

// Insert a test submission with multiple items
$test_items = [
    [
        'asset_id' => 1,
        'thing' => 'Dell XPS 15 Laptop',
        'inventory_tag' => 'DELL-001',
        'property_no' => 'PROP-001',
        'category' => 'Computer Equipment',
        'qty' => '1',
        'remarks' => 'For office work'
    ],
    [
        'asset_id' => 2,
        'thing' => 'HP LaserJet Printer',
        'inventory_tag' => 'HP-002',
        'property_no' => 'PROP-002',
        'category' => 'Office Equipment',
        'qty' => '1',
        'remarks' => 'Color printing required'
    ]
];

$sql = "INSERT INTO borrow_form_submissions
        (submission_number, guest_name, date_borrowed, schedule_return, barangay, contact,
         releasing_officer, approved_by, items, status, submitted_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())";

$stmt = $conn->prepare($sql);
$submission_number = 'BFS-' . date('Ymd') . '-999';
$guest_name = 'Test Guest User';
$date_borrowed = date('Y-m-d');
$schedule_return = date('Y-m-d', strtotime('+7 days'));
$barangay = 'Test Barangay';
$contact = '09123456789';
$releasing_officer = 'Test Officer';
$approved_by = 'Test Approver';
$items_json = json_encode($test_items);
$status = 'pending';

$stmt->bind_param('sssssssss',
    $submission_number, $guest_name, $date_borrowed, $schedule_return,
    $barangay, $contact, $releasing_officer, $approved_by, $items_json
);

if ($stmt->execute()) {
    echo "Test submission inserted successfully!<br>";
    echo "Submission Number: $submission_number<br>";
    echo "Items: " . count($test_items) . "<br><br>";
    echo "<a href='borrowing_history.php'>View in Borrowing History</a><br>";
    echo "<a href='database/view_borrow_submissions.php'>View Raw Data</a>";
} else {
    echo "Error inserting test data: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
