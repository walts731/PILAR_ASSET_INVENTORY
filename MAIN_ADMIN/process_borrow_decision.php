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

    // Get borrow request details
    $stmt = $conn->prepare("
        SELECT br.asset_id, br.quantity, a.quantity AS available_quantity
        FROM borrow_requests br
        JOIN assets a ON br.asset_id = a.id
        WHERE br.id = ?
    ");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->bind_result($asset_id, $borrow_quantity, $available_quantity);
    $stmt->fetch();
    $stmt->close();

    if ($action === 'accept') {
        // Validate available quantity
        if ($available_quantity >= $borrow_quantity) {
            // Deduct quantity from assets table
            $new_quantity = $available_quantity - $borrow_quantity;

            $updateAsset = $conn->prepare("UPDATE assets SET quantity = ?, status = ? WHERE id = ?");
            $status = ($new_quantity <= 0) ? 'borrowed' : 'Available';
            $updateAsset->bind_param("isi", $new_quantity, $status, $asset_id);
            $updateAsset->execute();
            $updateAsset->close();

            // Update borrow request status
            $updateRequest = $conn->prepare("UPDATE borrow_requests SET status = 'approved', approved_at = NOW() WHERE id = ?");
            $updateRequest->bind_param("i", $request_id);
            $updateRequest->execute();
            $updateRequest->close();

            $_SESSION['success_message'] = "Borrow request approved successfully.";
        } else {
            $_SESSION['error_message'] = "Insufficient asset quantity.";
        }

    } elseif ($action === 'reject') {
        $updateRequest = $conn->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE id = ?");
        $updateRequest->bind_param("i", $request_id);
        $updateRequest->execute();
        $updateRequest->close();

        $_SESSION['success_message'] = "Borrow request rejected.";
    }
}

header("Location: incoming_borrow_requests.php");
exit();
