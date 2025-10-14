<?php
// Test the processBorrowSubmission function directly
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

// Test the function directly
function processBorrowSubmission($conn) {
    // Get form data
    $guest_name = trim($_POST['name'] ?? '');
    $date_borrowed = $_POST['date_borrowed'] ?? '';
    $schedule_return = $_POST['schedule_return'] ?? '';
    $contact = trim($_POST['contact'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $releasing_officer = trim($_POST['releasing_officer'] ?? '');
    $approved_by = trim($_POST['approved_by'] ?? '');

    // Validate required fields
    $errors = [];
    if (empty($guest_name)) $errors[] = "Name is required";
    if (empty($date_borrowed)) $errors[] = "Date borrowed is required";
    if (empty($schedule_return)) $errors[] = "Schedule of return is required";
    if (empty($contact)) $errors[] = "Contact number is required";
    if (empty($barangay)) $errors[] = "Barangay is required";
    if (empty($releasing_officer)) $errors[] = "Releasing officer is required";
    if (empty($approved_by)) $errors[] = "Approved by is required";

    // Get cart items for asset validation
    $cart_items = $_SESSION['borrow_cart'] ?? [];
    if (empty($cart_items)) $errors[] = "No assets selected for borrowing";

    if (!empty($errors)) {
        echo "Validation errors: " . implode(', ', $errors) . "<br>";
        return;
    }

    // Generate submission number
    $date = date('Ymd');
    $counter = 1;
    do {
        $submission_number = sprintf("BFS-%s-%03d", $date, $counter);
        $counter++;
    } while ($conn->query("SELECT id FROM borrow_form_submissions WHERE submission_number = '$submission_number'")->num_rows > 0);

    // Prepare items data from cart
    $items = [];
    foreach ($cart_items as $cart_item) {
        $items[] = [
            'asset_id' => $cart_item['asset_id'],
            'thing' => $cart_item['description'],
            'inventory_tag' => $cart_item['inventory_tag'] ?? '',
            'property_no' => $cart_item['property_no'] ?? '',
            'category' => $cart_item['category_name'] ?? '',
            'qty' => '1',
            'remarks' => ''
        ];
    }

    // Insert into borrow_form_submissions table
    $sql = "INSERT INTO borrow_form_submissions
            (submission_number, guest_session_id, guest_email, guest_name, date_borrowed, schedule_return, barangay, contact,
             releasing_officer, approved_by, items, status, submitted_at, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), NOW())";

    $stmt = $conn->prepare($sql);
    $items_json = json_encode($items);
    $guest_session_id = session_id();
    $guest_email = $_SESSION['guest_email'] ?? null;

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

    if (!$stmt->execute()) {
        echo "Database error: " . $stmt->error . "<br>";
        $stmt->close();
        return;
    }

    $submission_id = $conn->insert_id;
    $stmt->close();

    echo "✅ SUCCESS: Borrow form submitted successfully!<br>";
    echo "Submission Number: $submission_number<br>";
    echo "Session ID: $guest_session_id<br>";
    echo "Items submitted: " . count($items) . "<br>";
}

// Run the test
processBorrowSubmission($conn);
$conn->close();
?>
