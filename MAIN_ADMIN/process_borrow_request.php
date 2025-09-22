<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: borrow.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['borrow_cart'] ?? [];

if (empty($cart)) {
    $_SESSION['error_message'] = "Your borrow cart is empty.";
    header("Location: view_cart.php");
    exit();
}

$purpose = trim($_POST['purpose'] ?? '');
$due_date = trim($_POST['due_date'] ?? '');

if (empty($purpose) || empty($due_date)) {
    $_SESSION['error_message'] = "Purpose and return date are required.";
    header("Location: view_cart.php");
    exit();
}

// Start a transaction
$conn->begin_transaction();

try {
    foreach ($cart as $asset_id => $item) {
        $quantity = $item['quantity'];

        // Fetch asset details again to ensure data integrity
        $asset_stmt = $conn->prepare("SELECT office_id, quantity FROM assets WHERE id = ? AND status = 'available'");
        $asset_stmt->bind_param("i", $asset_id);
        $asset_stmt->execute();
        $asset_result = $asset_stmt->get_result();

        if ($asset_result->num_rows === 0) {
            throw new Exception("Asset '" . htmlspecialchars($item['asset_name']) . "' is no longer available.");
        }

        $asset_data = $asset_result->fetch_assoc();
        $office_id = $asset_data['office_id'];
        $max_quantity = $asset_data['quantity'];

        if ($quantity > $max_quantity) {
            throw new Exception("Requested quantity for '" . htmlspecialchars($item['asset_name']) . "' exceeds available stock.");
        }

        // Insert into borrow_requests table
        $stmt = $conn->prepare("
            INSERT INTO borrow_requests (user_id, asset_id, office_id, quantity, purpose, due_date, status, requested_at)
            VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
        ");
        $stmt->bind_param("iiiis", $user_id, $asset_id, $office_id, $quantity, $purpose, $due_date);
        $stmt->execute();
        $stmt->close();
    }

    // If all requests are inserted successfully, commit the transaction
    $conn->commit();

    // Clear the cart
    $_SESSION['borrow_cart'] = [];

    $_SESSION['success_message'] = "Your borrow request has been submitted successfully.";
    header("Location: borrow_requests.php");
    exit();

} catch (Exception $e) {
    // If any error occurs, roll back the transaction
    $conn->rollback();

    $_SESSION['error_message'] = "An error occurred: " . $e->getMessage();
    header("Location: view_cart.php");
    exit();
}
?>
