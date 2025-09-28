<?php
require_once '../connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_SESSION['office_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Initialize cart if it doesn't exist
if (!isset($_SESSION['inter_dept_cart'])) {
    $_SESSION['inter_dept_cart'] = [];
}

// Get POST data
$asset_id = intval($_POST['asset_id'] ?? 0);
$asset_name = trim($_POST['asset_name'] ?? '');
$source_office_id = intval($_POST['source_office_id'] ?? 0);
$source_office_name = trim($_POST['source_office_name'] ?? '');
$quantity = intval($_POST['quantity'] ?? 0);
$purpose = trim($_POST['purpose'] ?? '');
$requested_return_date = trim($_POST['requested_return_date'] ?? '');

// Validate input
if ($asset_id <= 0 || empty($asset_name) || $source_office_id <= 0 || empty($source_office_name) || 
    $quantity <= 0 || empty($purpose) || empty($requested_return_date)) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

// Check if the asset already exists in the cart
$item_key = $asset_id . '_' . $source_office_id;
$cart_item = [
    'asset_id' => $asset_id,
    'asset_name' => $asset_name,
    'source_office_id' => $source_office_id,
    'source_office_name' => $source_office_name,
    'quantity' => $quantity,
    'purpose' => $purpose,
    'requested_return_date' => $requested_return_date,
    'added_at' => date('Y-m-d H:i:s')
];

// Add or update item in cart
$_SESSION['inter_dept_cart'][$item_key] = $cart_item;

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Item added to cart successfully',
    'cart_count' => count($_SESSION['inter_dept_cart'])
]);
?>
