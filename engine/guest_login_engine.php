<?php
session_start();

// Minimal guest session
$_SESSION['is_guest'] = true;
$_SESSION['user_id'] = 0; // sentinel for guest
$_SESSION['username'] = 'Guest';
$_SESSION['role'] = 'guest';
// Optional: choose a default office_id or leave unset; set to 0 to avoid null issues
$_SESSION['office_id'] = $_SESSION['office_id'] ?? 0;

// Redirect to MAIN_USER dashboard
header('Location: ../MAIN_USER/user_dashboard.php?guest=1');
exit();
