<?php
require_once '../connect.php';
session_start();

// Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['super_admin', 'admin', 'office_admin'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['item_id'])) {
    header("Location: borrowed_assets.php");
    exit();
}

$item_id = intval($_POST['item_id']);
$condition = $_POST['condition']; // good, damaged, needs_repair
$remarks = trim($_POST['remarks'] ?? '');

// Determine the new status for the asset item based on its returned condition
$new_item_status = 'available'; // Default status
if ($condition === 'damaged') {
    $new_item_status = 'damaged';
} elseif ($condition === 'needs_repair') {
    $new_item_status = 'under_repair';
}

$conn->begin_transaction();

try {
    // 1. Get asset_id and borrow_request_id for the item being returned
    $stmt = $conn->prepare("
        SELECT bri.borrow_request_id, ai.asset_id
        FROM borrow_request_items bri
        JOIN asset_items ai ON bri.asset_item_id = ai.item_id
        WHERE bri.asset_item_id = ? AND bri.status = 'assigned'
    ");
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$result) {
        throw new Exception("Item not found or already returned.");
    }
    $borrow_request_id = $result['borrow_request_id'];
    $asset_id = $result['asset_id'];

    // 2. Update the asset_items table
    $stmt_ai = $conn->prepare("UPDATE asset_items SET status = ? WHERE item_id = ?");
    $stmt_ai->bind_param("si", $new_item_status, $item_id);
    $stmt_ai->execute();
    $stmt_ai->close();

    // 3. Update the borrow_request_items table
    $stmt_bri = $conn->prepare("UPDATE borrow_request_items SET status = 'returned', returned_at = NOW() WHERE asset_item_id = ? AND borrow_request_id = ?");
    $stmt_bri->bind_param("ii", $item_id, $borrow_request_id);
    $stmt_bri->execute();
    $stmt_bri->close();

    // 4. Increment the quantity of the parent asset
    $stmt_a = $conn->prepare("UPDATE assets SET quantity = quantity + 1 WHERE id = ?");
    $stmt_a->bind_param("i", $asset_id);
    $stmt_a->execute();
    $stmt_a->close();

    // 5. Check if all items for this borrow request are returned
    $stmt_check = $conn->prepare("SELECT COUNT(*) FROM borrow_request_items WHERE borrow_request_id = ? AND status = 'assigned'");
    $stmt_check->bind_param("i", $borrow_request_id);
    $stmt_check->execute();
    $remaining_items = $stmt_check->get_result()->fetch_row()[0];
    $stmt_check->close();

    if ($remaining_items == 0) {
        // All items returned, update the main borrow_requests table
        $stmt_br = $conn->prepare("UPDATE borrow_requests SET status = 'returned' WHERE id = ?");
        $stmt_br->bind_param("i", $borrow_request_id);
        $stmt_br->execute();
        $stmt_br->close();
    }

    $conn->commit();
    $_SESSION['success_message'] = "Asset item has been successfully marked as returned.";

} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
}

header("Location: borrowed_assets.php");
exit();
?>
