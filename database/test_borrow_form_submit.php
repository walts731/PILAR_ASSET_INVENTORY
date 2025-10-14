<?php
// Quick test to verify borrow form submission works
session_start();
require_once 'C:\xampp\htdocs\PILAR_ASSET_INVENTORY\connect.php';

// Simulate guest session
$_SESSION['is_guest'] = true;
$_SESSION['guest_email'] = 'guest@pilar.gov.ph';

// Add test data to cart
$_SESSION['borrow_cart'] = [
    [
        'asset_id' => 1,
        'description' => 'Test Laptop',
        'inventory_tag' => 'TEST-001',
        'property_no' => 'PROP-TEST',
        'category_name' => 'Computer Equipment'
    ]
];

// Simulate POST data
$_POST = [
    'submit' => '1',
    'name' => 'Test User',
    'date_borrowed' => '2025-10-14',
    'schedule_return' => '2025-10-21',
    'barangay' => 'Test Barangay',
    'contact' => '09123456789',
    'releasing_officer' => 'Test Officer',
    'approved_by' => 'Test Approver'
];

// Include and test the borrow form processing
include 'C:\xampp\htdocs\PILAR_ASSET_INVENTORY\GUEST\borrow.php';

echo "<h2>Borrow Form Submission Test</h2>";
echo "<p>✅ If you see this message, the form submission completed without fatal errors!</p>";
echo "<p>Check the borrowing history to see if the submission was recorded.</p>";
echo "<a href='borrowing_history.php'>View Borrowing History</a>";
?>
