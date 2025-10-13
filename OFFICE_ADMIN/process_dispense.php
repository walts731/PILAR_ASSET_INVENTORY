<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['office_id'])) {
    exit('Unauthorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = (int) $_POST['asset_id'];
    $quantity_consumed = (int) $_POST['quantity_consumed'];
    $recipient_user_id = (int) $_POST['recipient_user_id'];
    $remarks = $_POST['remarks'] ?? '';
    $consumption_date = $_POST['consumption_date'] ?? '';
    $dispensed_by_user_id = $_SESSION['user_id'];
    $office_id = (int) $_SESSION['office_id']; // ✅ include office_id

    // Validate consumption date
    if (empty($consumption_date)) {
        $_SESSION['error_message'] = "Consumption date is required.";
        header("Location: inventory.php#consumables");
        exit();
    }

    // Validate date format
    $date_obj = DateTime::createFromFormat('Y-m-d', $consumption_date);
    if (!$date_obj || $date_obj->format('Y-m-d') !== $consumption_date) {
        $_SESSION['error_message'] = "Invalid consumption date format.";
        header("Location: inventory.php#consumables");
        exit();
    }

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

        // Insert log with office_id
        $log_sql = "INSERT INTO consumption_log 
                    (asset_id, office_id, quantity_consumed, recipient_user_id, dispensed_by_user_id, remarks, consumption_date) 
                    VALUES ($asset_id, $office_id, $quantity_consumed, $recipient_user_id, $dispensed_by_user_id, '$remarks', '$consumption_date')";
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
