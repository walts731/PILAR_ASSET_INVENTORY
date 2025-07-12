<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['request_id'], $_POST['action'])) {
  header("Location: incoming_borrow_requests.php");
  exit();
}

$request_id = intval($_POST['request_id']);
$action = $_POST['action'];
$now = date('Y-m-d H:i:s');

if ($action === 'accept') {
  $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'approved', approved_at = ? WHERE id = ?");
  $stmt->bind_param("si", $now, $request_id);
} elseif ($action === 'reject') {
  $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE id = ?");
  $stmt->bind_param("i", $request_id);
} else {
  header("Location: incoming_borrow_requests.php");
  exit();
}

$stmt->execute();
$stmt->close();

header("Location: incoming_borrow_requests.php");
exit();
