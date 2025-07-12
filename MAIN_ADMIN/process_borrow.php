<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$redirect_office_id = $_GET['office_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_assets'])) {
    $user_id = $_SESSION['user_id'];
    $borrow_date = date('Y-m-d H:i:s');
    $quantities = $_POST['quantities'] ?? [];

    $errors = [];
    $success_count = 0;

    foreach ($_POST['selected_assets'] as $entry) {
        list($asset_id, $office_id) = explode('|', $entry);
        $asset_id = intval($asset_id);
        $office_id = intval($office_id);
        $quantity_requested = intval($quantities[$asset_id] ?? 0);

        // Validate quantity
        $check = $conn->prepare("SELECT quantity FROM assets WHERE id = ?");
        $check->bind_param("i", $asset_id);
        $check->execute();
        $check->bind_result($available_quantity);
        $check->fetch();
        $check->close();

        if ($quantity_requested < 1 || $quantity_requested > $available_quantity) {
            $errors[] = "Invalid quantity for Asset ID $asset_id.";
            continue;
        }

        // Insert into borrow_requests
        $stmt = $conn->prepare("INSERT INTO borrow_requests (user_id, asset_id, office_id, quantity, status, requested_at) VALUES (?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param("iiiis", $user_id, $asset_id, $office_id, $quantity_requested, $borrow_date);
        $stmt->execute();
        $stmt->close();

        $redirect_office_id = $office_id;
        $success_count++;
    }

    if ($success_count > 0) {
        $_SESSION['success_message'] = "$success_count borrow request(s) submitted.";
    }

    if (!empty($errors)) {
        $_SESSION['error_message'] = implode('<br>', $errors);
    }
} else {
    $_SESSION['error_message'] = "No assets selected.";
}

// Redirect with office_id filter preserved
header("Location: borrow.php" . ($redirect_office_id ? "?office_id=$redirect_office_id" : ""));
exit();
