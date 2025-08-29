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

    // Start transaction for data consistency
    $conn->begin_transaction();

    try {
        foreach ($_POST['selected_assets'] as $entry) {
            list($asset_id, $office_id) = explode('|', $entry);
            $asset_id = intval($asset_id);
            $office_id = intval($office_id);
            $quantity_requested = intval($quantities[$asset_id] ?? 0);

            // Validate quantity
            $check = $conn->prepare("SELECT quantity, asset_name FROM assets WHERE id = ?");
            $check->bind_param("i", $asset_id);
            $check->execute();
            $check->bind_result($available_quantity, $asset_name);
            $check->fetch();
            $check->close();

            if ($quantity_requested < 1) {
                $errors[] = "Quantity must be at least 1 for $asset_name.";
                continue;
            }

            if ($quantity_requested > $available_quantity) {
                $errors[] = "Requested quantity ($quantity_requested) exceeds available quantity ($available_quantity) for $asset_name.";
                continue;
            }

            // Insert into borrow_requests
            $stmt = $conn->prepare("INSERT INTO borrow_requests (user_id, asset_id, office_id, quantity, status, requested_at) VALUES (?, ?, ?, ?, 'pending', ?)");
            $stmt->bind_param("iiiis", $user_id, $asset_id, $office_id, $quantity_requested, $borrow_date);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create borrow request for asset ID $asset_id: " . $stmt->error);
            }
            
            $stmt->close();

            $redirect_office_id = $office_id;
            $success_count++;
        }

        // Commit transaction if all operations succeeded
        $conn->commit();

        if ($success_count > 0) {
            $_SESSION['success_message'] = "$success_count borrow request(s) submitted successfully.";
        }

        if (!empty($errors)) {
            $_SESSION['error_message'] = implode('<br>', $errors);
        }

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = "Error processing borrow requests: " . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "No assets selected for borrowing.";
}

// Redirect with office_id filter preserved
header("Location: borrow.php" . ($redirect_office_id ? "?office_id=$redirect_office_id" : ""));
exit();
