<?php
// guest_profile_check.php
// Include this file at the top of guest pages to ensure profile completion

// Check if user is a guest and has a guest_id
if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] !== true || !isset($_SESSION['guest_id'])) {
    header("Location: ../index.php");
    exit();
}

$guest_id = $_SESSION['guest_id'];

// Check if guest has completed profile setup
$guest_stmt = $conn->prepare("SELECT email, name, contact, barangay FROM guests WHERE guest_id = ?");
$guest_stmt->bind_param("s", $guest_id);
$guest_stmt->execute();
$guest_result = $guest_stmt->get_result();

if ($guest_result->num_rows > 0) {
    $guest_data = $guest_result->fetch_assoc();

    // Check if any required fields are empty
    if (empty($guest_data['email']) || empty($guest_data['name']) || empty($guest_data['contact']) || empty($guest_data['barangay']) || $guest_data['email'] === 'guest@pilar.gov.ph') {
        header("Location: guest_profile_setup.php");
        exit();
    }
}
$guest_stmt->close();
?>
