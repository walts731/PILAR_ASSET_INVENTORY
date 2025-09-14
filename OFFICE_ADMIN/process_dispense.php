<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = (int) $_POST['asset_id'];
    $quantity_consumed = (int) $_POST['quantity_consumed'];
    $recipient_user_id = (int) $_POST['recipient_user_id'];
    $remarks = $_POST['remarks'] ?? '';
    $dispensed_by_user_id = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();
    try {
        // Get current stock
        $check_sql = "SELECT quantity FROM assets WHERE id = $asset_id FOR UPDATE";
        $result = $conn->query($check_sql);
        $asset = $result->fetch_assoc();
        $current_stock = $asset['quantity'];

        if ($quantity_consumed <= 0 || $quantity_consumed > $current_stock) {
            throw new Exception("Invalid quantity.");
        }

        // Update assets table
        $update_sql = "UPDATE assets 
                       SET quantity = quantity - $quantity_consumed, last_updated = NOW() 
                       WHERE id = $asset_id";
        $conn->query($update_sql);

        // Insert log
        $log_sql = "INSERT INTO consumption_log 
                    (asset_id, quantity_consumed, recipient_user_id, dispensed_by_user_id, remarks, consumption_date) 
                    VALUES ($asset_id, $quantity_consumed, $recipient_user_id, $dispensed_by_user_id, '$remarks', NOW())";
        $conn->query($log_sql);

        $conn->commit();
        $_SESSION['success_message'] = "Item dispensed successfully.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Error dispensing item: " . $e->getMessage();
    }

    header("Location: inventory.php#consumables"); // go back to dashboard
    exit();
}
?>
