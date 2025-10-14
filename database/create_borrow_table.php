<?php
require_once 'C:\xampp\htdocs\PILAR_ASSET_INVENTORY\connect.php';

$sql = "
CREATE TABLE IF NOT EXISTS `borrow_form_submissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `submission_number` varchar(50) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `date_borrowed` date NOT NULL,
  `schedule_return` date NOT NULL,
  `barangay` varchar(255) NOT NULL,
  `contact` varchar(50) NOT NULL,
  `releasing_officer` varchar(255) NOT NULL,
  `approved_by` varchar(255) NOT NULL,
  `items` JSON NOT NULL COMMENT 'JSON array of borrowed items with thing, qty, remarks',
  `status` enum('pending','approved','rejected','completed') NOT NULL DEFAULT 'pending',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `submission_number` (`submission_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
";

if ($conn->query($sql) === TRUE) {
    echo "Table 'borrow_form_submissions' created successfully!<br>";
} else {
    echo "Error creating table: " . $conn->error . "<br>";
}

// Test the table by inserting a sample record
$test_sql = "INSERT INTO borrow_form_submissions
             (submission_number, guest_name, date_borrowed, schedule_return, barangay, contact,
              releasing_officer, approved_by, items, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($test_sql);
$submission_number = 'BFS-' . date('Ymd') . '-001';
$guest_name = 'Test Guest';
$date_borrowed = date('Y-m-d');
$schedule_return = date('Y-m-d', strtotime('+7 days'));
$barangay = 'Test Barangay';
$contact = '09123456789';
$releasing_officer = 'Test Officer';
$approved_by = 'Test Approver';
$items = json_encode([
    [
        'asset_id' => 1,
        'thing' => 'Test Asset',
        'inventory_tag' => 'TAG001',
        'property_no' => 'PROP001',
        'category' => 'Test Category',
        'qty' => '1',
        'remarks' => 'Test remarks'
    ]
]);
$status = 'pending';

$stmt->bind_param('ssssssssss',
    $submission_number, $guest_name, $date_borrowed, $schedule_return,
    $barangay, $contact, $releasing_officer, $approved_by, $items, $status
);

if ($stmt->execute()) {
    echo "Test record inserted successfully! Submission number: $submission_number<br>";
} else {
    echo "Error inserting test record: " . $stmt->error . "<br>";
}

$stmt->close();
$conn->close();

echo "<br><a href='../GUEST/borrow.php'>Go to Borrow Form</a>";
?>
