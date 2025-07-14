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

        // Fetch current borrow details
        $stmt = $conn->prepare("SELECT asset_id, quantity FROM borrow_requests WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $request_id, $user_id);
        $stmt->execute();
        $stmt->bind_result($asset_id, $borrowed_qty);
        if ($stmt->fetch()) {
            $stmt->close();

            if ($qty_to_return > 0 && $qty_to_return <= $borrowed_qty) {
                // Update asset stock
                $conn->query("UPDATE assets SET quantity = quantity + $qty_to_return WHERE id = $asset_id");

                if ($qty_to_return == $borrowed_qty) {
                    // Full return
                    $stmt = $conn->prepare("UPDATE borrow_requests SET quantity = 0, status = 'returned', return_remarks = ?, returned_at = NOW() WHERE id = ?");
                    $stmt->bind_param("si", $remark, $request_id);
                } else {
                    // Partial return
                    $remaining_qty = $borrowed_qty - $qty_to_return;
                    $stmt = $conn->prepare("UPDATE borrow_requests SET quantity = ?, status = 'borrowed', return_remarks = ?, returned_at = NOW() WHERE id = ?");
                    $stmt->bind_param("isi", $remaining_qty, $remark, $request_id);
                }

                $stmt->execute();
                $stmt->close();
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
