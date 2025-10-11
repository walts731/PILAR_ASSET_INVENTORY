<?php
require_once '../connect.php';
session_start();

// Set JSON header
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'notifications' => []
];

try {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not authenticated');
    }

    // Get parameters
    $unreadOnly = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $userId = $_SESSION['user_id'];

    // Build the query
    $query = "SELECT n.*, u.first_name, u.last_name 
              FROM notifications n 
              LEFT JOIN users u ON n.sender_id = u.id 
              WHERE n.receiver_id = ? ";
    
    $params = [$userId];
    $types = "i";
    
    if ($unreadOnly) {
        $query .= " AND n.is_read = 0 ";
    }
    
    $query .= " ORDER BY n.created_at DESC ";
    
    if ($limit > 0) {
        $query .= " LIMIT ? ";
        $params[] = $limit;
        $types .= "i";
    }
    
    // Prepare and execute the query
    $stmt = $conn->prepare($query);
    
    if ($limit > 0) {
        $stmt->bind_param($types, ...$params);
    } else {
        $stmt->bind_param($types, $userId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Format notifications
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'message' => $row['message'],
            'type' => $row['type'],
            'is_read' => (bool)$row['is_read'],
            'created_at' => $row['created_at'],
            'sender_name' => $row['first_name'] . ' ' . $row['last_name']
        ];
    }
    
    // Mark notifications as read if this is a check for unread
    if ($unreadOnly && !empty($notifications)) {
        $notificationIds = array_column($notifications, 'id');
        $placeholders = rtrim(str_repeat('?,', count($notificationIds)), ',');
        
        $updateStmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id IN ($placeholders)");
        $updateStmt->bind_param(str_repeat('i', count($notificationIds)), ...$notificationIds);
        $updateStmt->execute();
    }
    
    $response['success'] = true;
    $response['notifications'] = $notifications;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    http_response_code(500);
}

// Return JSON response
echo json_encode($response);
?>
