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

    foreach ($return_ids as $request_id) {
        $remark = $remarks[$request_id] ?? '';
        $qty_to_return = intval($return_qty[$request_id] ?? 0);

        // Fetch asset ID and borrowed quantity
        $stmt = $conn->prepare("SELECT asset_id, quantity FROM borrow_requests WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $request_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($asset_id, $borrowed_qty);
        if ($stmt->fetch()) {
            $stmt->close();

            if ($qty_to_return > 0 && $qty_to_return <= $borrowed_qty) {
                // Update asset stock only
                $updateAsset = $conn->prepare("UPDATE assets SET quantity = quantity + ? WHERE id = ?");
                $updateAsset->bind_param("ii", $qty_to_return, $asset_id);
                $updateAsset->execute();
                $updateAsset->close();

                // If full quantity returned, mark as returned
                if ($qty_to_return == $borrowed_qty) {
                    $updateBorrow = $conn->prepare("UPDATE borrow_requests SET status = 'returned', return_remarks = ?, returned_at = NOW() WHERE id = ?");
                    $updateBorrow->bind_param("si", $remark, $request_id);
                } else {
                    // If partial return, leave status as 'borrowed' but log remarks and return time
                    $updateBorrow = $conn->prepare("UPDATE borrow_requests SET return_remarks = ?, returned_at = NOW() WHERE id = ?");
                    $updateBorrow->bind_param("si", $remark, $request_id);
                }

                $updateBorrow->execute();
                $updateBorrow->close();
            }
        } else {
            $stmt->close();
        }
    }

    $_SESSION['success_message'] = count($return_ids) . " asset(s) processed.";
} else {
    $_SESSION['error_message'] = "No assets selected for return.";
}

header("Location: borrowed_assets.php");
exit();
