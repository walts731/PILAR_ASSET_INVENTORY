<?php
require_once 'C:\xampp\htdocs\PILAR_ASSET_INVENTORY\connect.php';

$sql = "
ALTER TABLE `borrow_form_submissions`
ADD COLUMN `guest_session_id` VARCHAR(255) NOT NULL AFTER `submission_number`,
ADD COLUMN `guest_email` VARCHAR(255) DEFAULT NULL AFTER `guest_session_id`,
ADD KEY `guest_session_id` (`guest_session_id`),
ADD KEY `guest_email` (`guest_email`)
";

if ($conn->query($sql) === TRUE) {
    echo "Table 'borrow_form_submissions' altered successfully! Added guest identification fields.<br>";
} else {
    echo "Error altering table: " . $conn->error . "<br>";
}

$conn->close();
?>
