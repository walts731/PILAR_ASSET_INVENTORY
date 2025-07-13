<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'], $_POST['action'])) {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];

    // Get current borrow request and asset data
    $stmt = $conn->prepare("
        SELECT br.asset_id, br.quantity, br.status AS current_status, a.quantity AS available_quantity
        FROM borrow_requests br
        JOIN assets a ON br.asset_id = a.id
        WHERE br.id = ?
    ");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->bind_result($asset_id, $borrow_quantity, $current_status, $available_quantity);
    $stmt->fetch();
    $stmt->close();

    if ($action === 'accept') {
        if ($available_quantity >= $borrow_quantity) {
            // Deduct asset quantity
            $new_quantity = $available_quantity - $borrow_quantity;
            $status = ($new_quantity <= 0) ? 'borrowed' : 'Available';

            $updateAsset = $conn->prepare("UPDATE assets SET quantity = ?, status = ? WHERE id = ?");
            $updateAsset->bind_param("isi", $new_quantity, $status, $asset_id);
            $updateAsset->execute();
            $updateAsset->close();

            // Update request status
            $updateRequest = $conn->prepare("UPDATE borrow_requests SET status = 'approved', approved_at = NOW() WHERE id = ?");
            $updateRequest->bind_param("i", $request_id);
            $updateRequest->execute();
            $updateRequest->close();

            $_SESSION['success_message'] = "Borrow request approved successfully.";
        } else {
            $_SESSION['error_message'] = "Insufficient asset quantity.";
        }

    } elseif ($action === 'reject') {
        // If previously approved, restore quantity back to assets
        if ($current_status === 'approved') {
            $restored_quantity = $available_quantity + $borrow_quantity;

            $updateAsset = $conn->prepare("UPDATE assets SET quantity = ?, status = 'Available' WHERE id = ?");
            $updateAsset->bind_param("ii", $restored_quantity, $asset_id);
            $updateAsset->execute();
            $updateAsset->close();
        }

        $updateRequest = $conn->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE id = ?");
        $updateRequest->bind_param("i", $request_id);
        $updateRequest->execute();
        $updateRequest->close();

        $_SESSION['success_message'] = "Borrow request rejected.";
    }
}

header("Location: incoming_borrow_requests.php");
exit();
