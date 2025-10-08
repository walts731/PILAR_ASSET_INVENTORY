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

    // Get raw POST data for debugging
    $input = file_get_contents('php://input');
    $postData = json_decode($input, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $postData = $_POST; // Fallback to regular POST data
    }

    // Log received data for debugging
    error_log('Received POST data: ' . print_r($postData, true));

    // Get and validate required fields
    $required = ['asset_id', 'asset_name', 'source_office_id', 'source_office_name', 'quantity'];
    $missing = [];
    $data = [];
    
    foreach ($required as $field) {
        if (empty($postData[$field])) {
            $missing[] = $field;
        } else {
            $data[$field] = $postData[$field];
        }
    }
    
    if (!empty($missing)) {
        sendResponse(false, 'Missing required fields: ' . implode(', ', $missing), [
            'missing_fields' => $missing,
            'received_data' => $postData
        ]);
    }
    
    // Type validation
    $asset_id = filter_var($data['asset_id'], FILTER_VALIDATE_INT);
    $source_office_id = filter_var($data['source_office_id'], FILTER_VALIDATE_INT);
    $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
    
    if ($asset_id === false || $asset_id <= 0) {
        sendResponse(false, 'Invalid asset ID', ['asset_id' => $data['asset_id']]);
    }
    
    if ($source_office_id === false || $source_office_id <= 0) {
        sendResponse(false, 'Invalid source office ID', ['source_office_id' => $data['source_office_id']]);
    }
    
    if ($quantity === false || $quantity <= 0) {
        sendResponse(false, 'Quantity must be a positive number', ['quantity' => $data['quantity']]);
    }

    // Initialize cart if it doesn't exist
    if (!isset($_SESSION['inter_dept_cart'])) {
        $_SESSION['inter_dept_cart'] = [];
    }

    // Create cart item without purpose and requested_return_date
    $item_key = $asset_id . '_' . $source_office_id;
    $cart_item = [
        'asset_id' => $asset_id,
        'asset_name' => $data['asset_name'],
        'source_office_id' => $source_office_id,
        'source_office_name' => $data['source_office_name'],
        'quantity' => $quantity,
        'added_at' => date('Y-m-d H:i:s')
    ];
    
    // Add optional fields only if they exist in the database
    if (isset($data['purpose'])) {
        $cart_item['purpose'] = $data['purpose'];
    }
    if (isset($data['requested_return_date'])) {
        $cart_item['requested_return_date'] = $data['requested_return_date'];
    }

    // Add or update item in cart
    if (isset($_SESSION['inter_dept_cart'][$item_key])) {
        // Update quantity if item already exists
        $_SESSION['inter_dept_cart'][$item_key]['quantity'] += $quantity;
        
        // Update other fields if they exist in the request
        if (isset($data['purpose'])) {
            $_SESSION['inter_dept_cart'][$item_key]['purpose'] = $data['purpose'];
        }
        if (isset($data['requested_return_date'])) {
            $_SESSION['inter_dept_cart'][$item_key]['requested_return_date'] = $data['requested_return_date'];
        }
    } else {
        // Add new item to cart
        $_SESSION['inter_dept_cart'][$item_key] = $cart_item;
    }

    // Save session data
    session_write_close();
    
    // Return success response
    sendResponse(true, 'Item added to cart successfully', [
        'cart_count' => count($_SESSION['inter_dept_cart']),
        'item' => $cart_item,
        'cart' => $_SESSION['inter_dept_cart']
    ]);

} catch (Exception $e) {
    // Log the error with full details
    $errorDetails = [
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'post_data' => $_POST,
        'session' => $_SESSION
    ];
    
    error_log('Error in add_to_inter_dept_cart.php: ' . print_r($errorDetails, true));
    
    // Send error response
    http_response_code(500);
    sendResponse(false, 'An error occurred while processing your request. Please try again.', [
        'error' => $e->getMessage()
    ]);
}
?>
