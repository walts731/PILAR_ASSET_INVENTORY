<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['return_ids'])) {
  $user_id = $_SESSION['user_id'];
  $return_ids = $_POST['return_ids'];
  $remarks = $_POST['remarks'] ?? [];

  foreach ($return_ids as $request_id) {
    $remark = $remarks[$request_id] ?? '';

    // Update borrow_requests status
    $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'returned', return_remarks = ?, returned_at = NOW() WHERE id = ? AND user_id = ?");
    $stmt->bind_param("sii", $remark, $request_id, $user_id);
    $stmt->execute();
    $stmt->close();
  }

  $_SESSION['success_message'] = count($return_ids) . " asset(s) returned successfully.";
} else {
  $_SESSION['error_message'] = "No assets selected for return.";
}

header("Location: borrowed_assets.php");
exit();
