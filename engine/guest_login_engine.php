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
$stmt = $conn->prepare("SELECT id FROM guests WHERE guest_id = ?");
$stmt->bind_param("s", $guest_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Create new guest record
    $insert_stmt = $conn->prepare("INSERT INTO guests (guest_id, email, last_login) VALUES (?, 'guest@pilar.gov.ph', NOW())");
    $insert_stmt->bind_param("s", $guest_id);
    $insert_stmt->execute();
    $insert_stmt->close();
} else {
    // Update last login
    $update_stmt = $conn->prepare("UPDATE guests SET last_login = NOW(), session_count = session_count + 1 WHERE guest_id = ?");
    $update_stmt->bind_param("s", $guest_id);
    $update_stmt->execute();
    $update_stmt->close();
}

$stmt->close();

// Set session variables
$_SESSION['is_guest'] = true;
$_SESSION['user_id'] = 0; // sentinel for guest
$_SESSION['username'] = 'Guest';
$_SESSION['role'] = 'guest';
$_SESSION['guest_email'] = 'guest@pilar.gov.ph';
$_SESSION['guest_id'] = $guest_id; // Store persistent guest ID in session
// Optional: choose a default office_id or leave unset; set to 0 to avoid null issues
$_SESSION['office_id'] = $_SESSION['office_id'] ?? 0;

// Redirect to GUEST dashboard
header('Location: ../GUEST/guest_dashboard.php');
exit();
