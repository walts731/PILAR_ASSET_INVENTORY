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
    $return_qty = $_POST['return_qty'] ?? [];

    // Start transaction for data consistency
    $conn->begin_transaction();

    try {
        $processed_count = 0;

        foreach ($return_ids as $request_id) {
            $request_id = intval($request_id);
            $remark = $remarks[$request_id] ?? '';
            $qty_to_return = intval($return_qty[$request_id] ?? 0);

            // Fetch asset ID, borrowed quantity, and current status
            $stmt = $conn->prepare("
                SELECT br.asset_id, br.quantity, br.status, a.asset_name 
                FROM borrow_requests br
                JOIN assets a ON br.asset_id = a.id
                WHERE br.id = ? AND br.user_id = ?
            ");
            $stmt->bind_param("ii", $request_id, $user_id);
            $stmt->execute();
            $stmt->bind_result($asset_id, $borrowed_qty, $current_status, $asset_name);
            
            if (!$stmt->fetch()) {
                $stmt->close();
                throw new Exception("Borrow request not found or unauthorized.");
            }
            $stmt->close();

            // Validate return quantity
            if ($qty_to_return <= 0) {
                throw new Exception("Return quantity must be greater than 0 for $asset_name.");
            }

            if ($qty_to_return > $borrowed_qty) {
                throw new Exception("Return quantity ($qty_to_return) exceeds borrowed quantity ($borrowed_qty) for $asset_name.");
            }

            if ($current_status !== 'borrowed') {
                throw new Exception("Cannot return asset that is not currently borrowed: $asset_name");
            }

            // Update asset quantity
            $updateAsset = $conn->prepare("UPDATE assets SET quantity = quantity + ? WHERE id = ?");
            $updateAsset->bind_param("ii", $qty_to_return, $asset_id);
            if (!$updateAsset->execute()) {
                throw new Exception("Failed to update asset quantity for $asset_name.");
            }
            $updateAsset->close();

            // Update asset status if quantity becomes positive
            $checkAsset = $conn->prepare("SELECT quantity FROM assets WHERE id = ?");
            $checkAsset->bind_param("i", $asset_id);
            $checkAsset->execute();
            $checkAsset->bind_result($new_asset_qty);
            $checkAsset->fetch();
            $checkAsset->close();

            if ($new_asset_qty > 0) {
                $updateAssetStatus = $conn->prepare("UPDATE assets SET status = 'available' WHERE id = ?");
                $updateAssetStatus->bind_param("i", $asset_id);
                $updateAssetStatus->execute();
                $updateAssetStatus->close();
            }

            // Update borrow request
            if ($qty_to_return == $borrowed_qty) {
                // Full return - mark as returned
                $updateBorrow = $conn->prepare("
                    UPDATE borrow_requests 
                    SET status = 'returned', return_remarks = ?, returned_at = NOW(), quantity = ?
                    WHERE id = ?
                ");
                $updateBorrow->bind_param("sii", $remark, $qty_to_return, $request_id);
            } else {
                // Partial return - update quantity and remarks
                $remaining_qty = $borrowed_qty - $qty_to_return;
                $updateBorrow = $conn->prepare("
                    UPDATE borrow_requests 
                    SET quantity = ?, return_remarks = ?, returned_at = NOW() 
                    WHERE id = ?
                ");
                $updateBorrow->bind_param("isi", $remaining_qty, $remark, $request_id);
            }

            if (!$updateBorrow->execute()) {
                throw new Exception("Failed to update borrow request for $asset_name.");
            }
            $updateBorrow->close();

            $processed_count++;
        }

        // Commit transaction
        $conn->commit();
        $_SESSION['success_message'] = "$processed_count asset(s) returned successfully.";

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        $_SESSION['error_message'] = $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = "No assets selected for return.";
}

header("Location: borrowed_assets.php");
exit();
