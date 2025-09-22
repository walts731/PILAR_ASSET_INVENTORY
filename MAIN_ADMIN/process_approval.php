<?php
require_once '../connect.php';
session_start();

// Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['super_admin', 'admin', 'office_admin'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: incoming_borrow_requests.php");
    exit();
}

$request_id = intval($_POST['request_id']);
$decision = $_POST['decision']; // 'approve' or 'reject'
$remarks = trim($_POST['remarks'] ?? '');

$conn->begin_transaction();

try {
    if ($decision === 'approve') {
        $selected_items = $_POST['selected_items'] ?? [];
        $quantity_requested = intval($_POST['quantity_requested']);
        $asset_id = intval($_POST['asset_id']);

        if (count($selected_items) !== $quantity_requested) {
            throw new Exception("The number of selected items does not match the quantity requested.");
        }

        // 1. Update the main borrow request
        $stmt_br = $conn->prepare("UPDATE borrow_requests SET status = 'approved', approved_at = NOW() WHERE id = ?");
        $stmt_br->bind_param("i", $request_id);
        $stmt_br->execute();
        $stmt_br->close();

        // 2. Insert into borrow_request_items and update asset_items status
        $stmt_bri = $conn->prepare("INSERT INTO borrow_request_items (borrow_request_id, asset_item_id) VALUES (?, ?)");
        $stmt_ai = $conn->prepare("UPDATE asset_items SET status = 'in-use' WHERE item_id = ?");

        foreach ($selected_items as $item_id) {
            $item_id = intval($item_id);
            $stmt_bri->bind_param("ii", $request_id, $item_id);
            $stmt_bri->execute();

            $stmt_ai->bind_param("i", $item_id);
            $stmt_ai->execute();
        }
        $stmt_bri->close();
        $stmt_ai->close();

        // 3. Decrement the quantity in the main assets table
        $stmt_a = $conn->prepare("UPDATE assets SET quantity = quantity - ? WHERE id = ?");
        $stmt_a->bind_param("ii", $quantity_requested, $asset_id);
        $stmt_a->execute();
        $stmt_a->close();

        $_SESSION['success_message'] = "Request #$request_id has been approved.";

    } elseif ($decision === 'reject') {
        $stmt = $conn->prepare("UPDATE borrow_requests SET status = 'rejected', remarks = ? WHERE id = ?");
        $stmt->bind_param("si", $remarks, $request_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['success_message'] = "Request #$request_id has been rejected.";

    } else {
        throw new Exception("Invalid decision.");
    }

    $conn->commit();
    header("Location: incoming_borrow_requests.php");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    header("Location: approve_request.php?request_id=" . $request_id);
    exit();
}
?>
