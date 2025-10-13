<?php
require_once '../connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 403 Forbidden');
    die('Unauthorized access');
}

try {
    // Validate required fields
    $required = ['description', 'quantity', 'unit', 'value'];
    $missing = [];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        throw new Exception('Required fields missing: ' . implode(', ', $missing));
    }
    
    // Sanitize and validate input
    $description = trim($_POST['description']);
    $quantity = (float)$_POST['quantity'];
    $unit = trim($_POST['unit']);
    $value = (float)$_POST['value'];
    $office_id = !empty($_POST['office_id']) ? (int)$_POST['office_id'] : null;
    
    if ($quantity < 0) {
        throw new Exception('Quantity cannot be negative');
    }
    
    if ($value < 0) {
        throw new Exception('Unit price cannot be negative');
    }
    
    // Prepare SQL statement
    $sql = "INSERT INTO assets (
        description, 
        quantity, 
        unit, 
        value, 
        office_id,
        type,
        status,
        acquisition_date,
        last_updated
    ) VALUES (?, ?, ?, ?, ?, 'consumable', 'available', NOW(), NOW())";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        'sdsdi', 
        $description, 
        $quantity, 
        $unit, 
        $value, 
        $office_id
    );
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Consumable added successfully';
        header('Location: inventory.php?success=1');
        exit();
    } else {
        throw new Exception('Failed to add consumable: ' . $conn->error);
    }
    
} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: inventory.php?error=1');
    exit();
}