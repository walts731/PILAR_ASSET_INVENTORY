<?php
require_once '../connect.php';
session_start();

// Check if user is logged in and has the right permissions
if (!isset($_SESSION['user_id']) || !in_array(strtolower($_SESSION['role']), ['super_admin', 'system_admin'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if user ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

$userId = (int)$_GET['id'];

try {
    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT id, fullname, username, email, role, status FROM users WHERE id = ?");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    $user = $result->fetch_assoc();
    
    // Return the user data
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => [
            'id' => $user['id'],
            'fullname' => $user['fullname'],
            'username' => $user['username'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status']
        ]
    ]);
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log('Error fetching user data: ' . $e->getMessage());
    
    // Return a generic error message
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred while fetching user data.'
    ]);
}

$stmt->close();
$conn->close();
?>
