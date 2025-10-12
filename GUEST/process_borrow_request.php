<?php
require_once '../connect.php';
require_once '../includes/classes/GuestBorrowing.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    exit('Method not allowed');
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is a guest
if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    exit('Access denied');
}

// Get POST data
$guestName = filter_input(INPUT_POST, 'guest_name', FILTER_SANITIZE_STRING);
$guestEmail = filter_input(INPUT_POST, 'guest_email', FILTER_SANITIZE_EMAIL);
$guestContact = filter_input(INPUT_POST, 'guest_contact', FILTER_SANITIZE_STRING);
$guestOrg = filter_input(INPUT_POST, 'guest_organization', FILTER_SANITIZE_STRING);
$purpose = filter_input(INPUT_POST, 'purpose', FILTER_SANITIZE_STRING);
$neededByDate = filter_input(INPUT_POST, 'needed_by_date', FILTER_SANITIZE_STRING);
$returnDate = filter_input(INPUT_POST, 'return_date', FILTER_SANITIZE_STRING);
$items = isset($_POST['items']) ? $_POST['items'] : [];

// Validate required fields
$errors = [];

if (empty($guestName)) {
    $errors[] = 'Full name is required';
}

if (!filter_var($guestEmail, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}

if (empty($purpose)) {
    $errors[] = 'Purpose of borrowing is required';
}

if (empty($returnDate)) {
    $errors[] = 'Expected return date is required';
} else if (strtotime($returnDate) < strtotime('today')) {
    $errors[] = 'Return date must be in the future';
}

if (empty($items)) {
    $errors[] = 'At least one item is required';
} else {
    // Validate items
    foreach ($items as $item) {
        if (empty($item['asset_id']) || !is_numeric($item['asset_id'])) {
            $errors[] = 'Invalid asset selected';
            break;
        }
        if (empty($item['quantity']) || !is_numeric($item['quantity']) || $item['quantity'] < 1) {
            $errors[] = 'Invalid quantity for one or more items';
            break;
        }
    }
}

// If there are validation errors, return them
if (!empty($errors)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'errors' => $errors
    ]);
    exit;
}

// If validation passes, process the request
try {
    // Initialize GuestBorrowing
    $guestBorrowing = new GuestBorrowing($conn);
    
    // Prepare data for the request
    $requestData = [
        'guest_name' => $guestName,
        'guest_email' => $guestEmail,
        'guest_contact' => $guestContact,
        'guest_organization' => $guestOrg,
        'purpose' => $purpose,
        'needed_by_date' => $neededByDate ?: date('Y-m-d'),
        'expected_return_date' => $returnDate,
        'items' => array_map(function($item) {
            return [
                'asset_id' => (int)$item['asset_id'],
                'quantity' => (int)$item['quantity']
            ];
        }, $items)
    ];
    
    // Create the request
    $result = $guestBorrowing->createRequest($requestData);
    
    if ($result['success']) {
        // Update session with success message
        $_SESSION['borrow_success'] = [
            'request_number' => $result['request_number'],
            'message' => 'Your borrowing request has been submitted successfully.'
        ];
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'redirect' => 'borrowing_history.php',
            'request_number' => $result['request_number']
        ]);
    } else {
        throw new Exception($result['error'] ?? 'Failed to process borrowing request');
    }
    
} catch (Exception $e) {
    // Log the error
    error_log('Borrowing request error: ' . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'errors' => ['An error occurred while processing your request. Please try again.']
    ]);
}
