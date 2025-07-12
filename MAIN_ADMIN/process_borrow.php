<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

$redirect_office_id = $_GET['office_id'] ?? ''; // capture the filtered office_id if available

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_assets'])) {
  $user_id = $_SESSION['user_id'];
  $borrow_date = date('Y-m-d H:i:s');

  foreach ($_POST['selected_assets'] as $entry) {
    list($asset_id, $office_id) = explode('|', $entry);
    
    $stmt = $conn->prepare("INSERT INTO borrow_requests (user_id, asset_id, office_id, status, requested_at) VALUES (?, ?, ?, 'pending', ?)");
    $stmt->bind_param("iiis", $user_id, $asset_id, $office_id, $borrow_date);
    $stmt->execute();

    // Update redirect office_id to the last processed asset's office_id
    $redirect_office_id = $office_id;
  }

  $_SESSION['success_message'] = "Borrow request submitted for selected assets.";
} else {
  $_SESSION['error_message'] = "No assets selected.";
}

// Redirect with office_id filter preserved
header("Location: borrow.php" . ($redirect_office_id ? "?office_id=$redirect_office_id" : ""));
exit();
