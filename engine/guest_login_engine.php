<?php
require_once '../connect.php';
session_start();

// Generate or retrieve persistent guest ID
$guest_id = isset($_COOKIE['guest_id']) ? $_COOKIE['guest_id'] : null;

if (!$guest_id) {
    // Generate a new persistent guest ID
    $guest_id = bin2hex(random_bytes(32)); // 64 character hex string

    // Set cookie that lasts for 1 year
    setcookie('guest_id', $guest_id, time() + (365 * 24 * 60 * 60), '/', '', false, true);
}

// Check if guest exists in database, if not create them
$stmt = $conn->prepare("SELECT id, email, name, contact, barangay, first_login FROM guests WHERE guest_id = ?");
$stmt->bind_param("s", $guest_id);
$stmt->execute();
$result = $stmt->get_result();

$needs_profile_setup = false;
$guest_record = null;

if ($result->num_rows === 0) {
    // Create new guest record with default values
    $insert_stmt = $conn->prepare("INSERT INTO guests (guest_id, email, last_login) VALUES (?, 'guest@pilar.gov.ph', NOW())");
    $insert_stmt->bind_param("s", $guest_id);
    $insert_stmt->execute();
    $insert_stmt->close();

    // New guest needs profile setup
    $needs_profile_setup = true;
} else {
    $guest_record = $result->fetch_assoc();

    // Check if guest needs to complete profile
    // Either first_login is null (never completed setup) OR any required fields are empty
    $has_empty_required_fields = (
        empty($guest_record['email']) ||
        empty($guest_record['name']) ||
        empty($guest_record['contact']) ||
        empty($guest_record['barangay']) ||
        $guest_record['email'] === 'guest@pilar.gov.ph'
    );

    if ($guest_record['first_login'] === null || $has_empty_required_fields) {
        $needs_profile_setup = true;
    }
}

$stmt->close();

// Set session variables
$_SESSION['is_guest'] = true;
$_SESSION['user_id'] = 0; // sentinel for guest
$_SESSION['username'] = 'Guest';
$_SESSION['role'] = 'guest';
$_SESSION['guest_id'] = $guest_id; // Store persistent guest ID in session

// Set additional session variables (will be empty for new guests or guests without profiles)
$_SESSION['guest_email'] = '';
$_SESSION['guest_name'] = '';
$_SESSION['guest_contact'] = '';
$_SESSION['guest_barangay'] = '';

// Update last login and session count
$update_stmt = $conn->prepare("UPDATE guests SET last_login = NOW(), session_count = session_count + 1 WHERE guest_id = ?");
$update_stmt->bind_param("s", $guest_id);
$update_stmt->execute();
$update_stmt->close();

// Optional: choose a default office_id or leave unset; set to 0 to avoid null issues
$_SESSION['office_id'] = $_SESSION['office_id'] ?? 0;

// Redirect based on profile completion status
if ($needs_profile_setup) {
    // Redirect to profile setup page
    header('Location: ../GUEST/guest_profile_setup.php');
} else {
    // Set additional session variables from guest record
    $_SESSION['guest_email'] = $guest_record['email'];
    $_SESSION['guest_name'] = $guest_record['name'] ?? '';
    $_SESSION['guest_contact'] = $guest_record['contact'] ?? '';
    $_SESSION['guest_barangay'] = $guest_record['barangay'] ?? '';

    // Redirect to GUEST dashboard
    header('Location: ../GUEST/guest_dashboard.php');
}

exit();
