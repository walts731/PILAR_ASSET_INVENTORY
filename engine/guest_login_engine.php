<?php
session_start();

// Minimal guest session
$_SESSION['is_guest'] = true;
$_SESSION['user_id'] = 0; // sentinel for guest
$_SESSION['username'] = 'Guest';
$_SESSION['role'] = 'guest';
$_SESSION['guest_email'] = 'guest@pilar.gov.ph'; // Default guest email for borrowing system
// Optional: choose a default office_id or leave unset; set to 0 to avoid null issues
$_SESSION['office_id'] = $_SESSION['office_id'] ?? 0;

// Redirect to GUEST dashboard
header('Location: ../GUEST/guest_dashboard.php');
exit();
