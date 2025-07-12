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
    // 1. Update borrow_requests
    $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'approved', approved_at = ? WHERE id = ?");
    $stmt->bind_param("si", $now, $request_id);
    $stmt->execute();
    $stmt->close();

    // 2. Get asset_id from this borrow request
    $stmt = $conn->prepare("SELECT asset_id FROM borrow_requests WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->bind_result($asset_id);
    if ($stmt->fetch()) {
        $stmt->close();

        // 3. Update asset status to 'Borrowed'
        $stmt = $conn->prepare("UPDATE assets SET status = 'Borrowed' WHERE id = ?");
        $stmt->bind_param("i", $asset_id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt->close();
    }

} elseif ($action === 'reject') {
    // Only update the borrow request status
    $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: incoming_borrow_requests.php");
exit();
