<?php
require_once '../connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit();
}

// Check for CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid CSRF token']);
    exit();
}

// Validate required fields
$required = ['purpose', 'due_date'];
foreach ($required as $field) {
    if (empty($_POST[$field])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => "Missing required field: $field"]);
        exit();
    }
}

// Validate due date is in the future
$due_date = DateTime::createFromFormat('Y-m-d', $_POST['due_date']);
$today = new DateTime();
if (!$due_date || $due_date < $today) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Due date must be in the future']);
    exit();
}

// Check if cart has items
if (!isset($_SESSION['borrow_cart']) || empty($_SESSION['borrow_cart'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'No items in cart']);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Create borrow request
    $stmt = $conn->prepare("
        INSERT INTO borrow_requests 
        (user_id, office_id, purpose, due_date, status, requested_at) 
        VALUES (?, ?, ?, ?, 'pending', NOW())
    ");
    
    $stmt->bind_param(
        'iiss',
        $_SESSION['user_id'],
        $_SESSION['office_id'],
        $_POST['purpose'],
        $_POST['due_date']
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to create borrow request: " . $stmt->error);
    }
    
    $request_id = $conn->insert_id;
    
    // Process each item in cart
    foreach ($_SESSION['borrow_cart'] as $asset_id => $item) {
        // Get available asset items
        $item_stmt = $conn->prepare("
            SELECT item_id 
            FROM asset_items 
            WHERE asset_id = ? AND status = 'available' 
            LIMIT ?
            FOR UPDATE
        ");
        $item_stmt->bind_param('ii', $asset_id, $item['quantity']);
        $item_stmt->execute();
        $items = $item_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        if (count($items) < $item['quantity']) {
            throw new Exception("Not enough available items for asset ID: $asset_id");
        }
        
        // Add to borrow request items
        $item_stmt = $conn->prepare("
            INSERT INTO borrow_request_items 
            (borrow_request_id, asset_item_id, status) 
            VALUES (?, ?, 'assigned')
        ");
        
        foreach ($items as $asset_item) {
            $item_stmt->bind_param('ii', $request_id, $asset_item['item_id']);
            if (!$item_stmt->execute()) {
                throw new Exception("Failed to add item to borrow request: " . $item_stmt->error);
            }
            
            // Update asset item status
            $update_stmt = $conn->prepare("
                UPDATE asset_items 
                SET status = 'reserved' 
                WHERE item_id = ?
            ");
            $update_stmt->bind_param('i', $asset_item['item_id']);
            if (!$update_stmt->execute()) {
                throw new Exception("Failed to update asset item status: " . $update_stmt->error);
            }
        }
    }
    
    // Update asset quantities
    foreach ($_SESSION['borrow_cart'] as $asset_id => $item) {
        $update_asset = $conn->prepare("
            UPDATE assets 
            SET quantity = quantity - ? 
            WHERE id = ? AND quantity >= ?
        ");
        $update_asset->bind_param('iii', $item['quantity'], $asset_id, $item['quantity']);
        if (!$update_asset->execute() || $update_asset->affected_rows === 0) {
            throw new Exception("Failed to update asset quantity for asset ID: $asset_id");
        }
    }
    
    // Clear cart
    unset($_SESSION['borrow_cart']);
    
    // Commit transaction
    $conn->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success', 
        'message' => 'Borrow request submitted successfully',
        'request_id' => $request_id
    ]);
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}