<?php
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and validate input
$input = json_decode(file_get_contents('php://input'), true);
$userId = $input['userId'] ?? null;
$notification = $input['notification'] ?? null;

if (!$userId || !$notification) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

// In a real implementation, you would verify the user has permission to send notifications
// and validate the notification data

try {
    // Connect to the WebSocket server
    $socket = @stream_socket_client('tcp://127.0.0.1:8080', $errno, $errstr, 1);
    
    if (!$socket) {
        throw new Exception("Could not connect to WebSocket server: $errstr ($errno)");
    }
    
    // Send the notification
    $message = json_encode([
        'type' => 'send_to_user',
        'userId' => $userId,
        'notification' => $notification
    ]);
    
    fwrite($socket, $message);
    fclose($socket);
    
    echo json_encode(['success' => true, 'message' => 'Notification sent']);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to send notification',
        'error' => $e->getMessage()
    ]);
}
