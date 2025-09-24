<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $status = intval($_POST['status'] ?? -1);

    if ($id > 0 && ($status === 0 || $status === 1)) {
        $stmt = $conn->prepare("UPDATE categories SET status = ? WHERE id = ?");
        $stmt->bind_param('ii', $status, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = $status === 1 ? 'Category activated.' : 'Category deactivated.';
            $_SESSION['message_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Failed to update category status.';
            $_SESSION['message_type'] = 'danger';
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = 'Invalid request.';
        $_SESSION['message_type'] = 'warning';
    }
}

header('Location: manage_categories.php');
exit();
