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

    // Start transaction for data consistency
    $conn->begin_transaction();

    try {
        // Get current borrow request and asset data
        $stmt = $conn->prepare("
            SELECT br.asset_id, br.quantity, br.status AS current_status, 
                   a.quantity AS available_quantity, a.asset_name
            FROM borrow_requests br
            JOIN assets a ON br.asset_id = a.id
            WHERE br.id = ?
        ");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->bind_result($asset_id, $borrow_quantity, $current_status, $available_quantity, $asset_name);
        if (!$stmt->fetch()) {
            throw new Exception("Borrow request not found.");
        }
        $stmt->close();

        if ($action === 'accept') {
            if ($current_status !== 'pending') {
                throw new Exception("Cannot approve a request that is not pending.");
            }

            if ($available_quantity < $borrow_quantity) {
                throw new Exception("Insufficient quantity for $asset_name. Available: $available_quantity, Requested: $borrow_quantity");
            }

            // Deduct asset quantity
            $new_quantity = $available_quantity - $borrow_quantity;
            $asset_status = ($new_quantity <= 0) ? 'borrowed' : 'available';

            $updateAsset = $conn->prepare("UPDATE assets SET quantity = ?, status = ? WHERE id = ?");
            $updateAsset->bind_param("isi", $new_quantity, $asset_status, $asset_id);
            if (!$updateAsset->execute()) {
                throw new Exception("Failed to update asset quantity.");
            }
            $updateAsset->close();

            // Update request status to 'borrowed' (not 'approved')
            $updateRequest = $conn->prepare("UPDATE borrow_requests SET status = 'borrowed', approved_at = NOW() WHERE id = ?");
            $updateRequest->bind_param("i", $request_id);
            if (!$updateRequest->execute()) {
                throw new Exception("Failed to update borrow request status.");
            }
            $updateRequest->close();

            $_SESSION['success_message'] = "Borrow request approved successfully. Asset status updated.";

        } elseif ($action === 'reject') {
            if ($current_status !== 'pending') {
                throw new Exception("Cannot reject a request that is not pending.");
            }

            $updateRequest = $conn->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE id = ?");
            $updateRequest->bind_param("i", $request_id);
            if (!$updateRequest->execute()) {
                throw new Exception("Failed to reject borrow request.");
            }
            $updateRequest->close();

            $_SESSION['success_message'] = "Borrow request rejected.";
        }

        // Commit transaction
        $conn->commit();

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "Invalid request parameters.";
}

header("Location: incoming_borrow_requests.php");
exit();
