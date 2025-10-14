<?php
session_start();
require_once '../connect.php';

// Test borrow request submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simulate form data
    $_POST = [
        'name' => 'Test Guest User',
        'date_borrowed' => date('Y-m-d'),
        'schedule_return' => date('Y-m-d', strtotime('+7 days')),
        'contact' => '09123456789',
        'barangay' => 'Test Barangay',
        'submit' => '1'
    ];

    // Add some test assets to cart
    $_SESSION['borrow_cart'] = [
        [
            'asset_id' => 1,
            'description' => 'Test Asset 1',
            'inventory_tag' => 'TAG001',
            'property_no' => 'PROP001',
            'category_name' => 'Test Category'
        ],
        [
            'asset_id' => 2,
            'description' => 'Test Asset 2',
            'inventory_tag' => 'TAG002',
            'property_no' => 'PROP002',
            'category_name' => 'Test Category'
        ]
    ];

    // Set guest session
    $_SESSION['is_guest'] = true;
    $_SESSION['guest_email'] = 'guest@pilar.gov.ph';

    // Include the submission logic
    include 'borrow.php';

    echo "<h2>Test Results:</h2>";
    echo "<p>Form submission logic executed. Check borrowing_history.php for results.</p>";
    echo "<a href='borrowing_history.php'>View Borrowing History</a>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Borrow Submission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Test Borrow Request Submission</h2>
        <p>This will simulate submitting a borrow request with test data.</p>

        <div class="alert alert-info">
            <strong>Test Data:</strong>
            <ul>
                <li>Name: Test Guest User</li>
                <li>Contact: 09123456789</li>
                <li>Barangay: Test Barangay</li>
                <li>Assets: 2 test assets</li>
            </ul>
        </div>

        <form method="POST">
            <button type="submit" class="btn btn-primary">Run Test Submission</button>
            <a href="borrowing_history.php" class="btn btn-secondary ms-2">View History</a>
        </form>
    </div>
</body>
</html>
