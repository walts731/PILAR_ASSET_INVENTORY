<?php
require_once '../connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session with error handling
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

// Function to send JSON response
function sendResponse($success, $message, $data = []) {
    $response = array_merge(['success' => $success, 'message' => $message], $data);
    echo json_encode($response);
    exit();
}

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['office_id'])) {
        sendResponse(false, 'Unauthorized access. Please log in.');
    }

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['inter_dept_cart'])) {
        $_SESSION['inter_dept_cart'] = [];
    }

    // Get POST data with validation
    $asset_id = filter_input(INPUT_POST, 'asset_id', FILTER_VALIDATE_INT);
    $asset_name = trim(filter_input(INPUT_POST, 'asset_name', FILTER_SANITIZE_STRING) ?: '');
    $source_office_id = filter_input(INPUT_POST, 'source_office_id', FILTER_VALIDATE_INT);
    $source_office_name = trim(filter_input(INPUT_POST, 'source_office_name', FILTER_SANITIZE_STRING) ?: '');
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    
    // These will be set when submitting the final request
    $purpose = '';
    $requested_return_date = '';

    // Validate input
    $errors = [];
    if (!$asset_id || $asset_id <= 0) $errors[] = 'Invalid asset ID';
    if (empty($asset_name)) $errors[] = 'Asset name is required';
    if (!$source_office_id || $source_office_id <= 0) $errors[] = 'Invalid source office ID';
    if (empty($source_office_name)) $errors[] = 'Source office name is required';
    if (!$quantity || $quantity <= 0) $errors[] = 'Quantity must be greater than 0';

    if (!empty($errors)) {
        sendResponse(false, 'Validation failed: ' . implode(', ', $errors), ['errors' => $errors]);
    }

    // Create cart item
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

    // Save session data
    session_write_close();

    // Return success response
    sendResponse(true, 'Item added to cart successfully', [
        'cart_count' => count($_SESSION['inter_dept_cart']),
        'item' => $cart_item
    ]);

} catch (Exception $e) {
    // Log the error
    error_log('Error in add_to_inter_dept_cart.php: ' . $e->getMessage());
    
    // Send error response
    sendResponse(false, 'An error occurred: ' . $e->getMessage(), [
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
