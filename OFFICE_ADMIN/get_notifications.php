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

    // Build the query to get user notifications with notification details
    $query = "SELECT 
                n.id, 
                n.title, 
                n.message, 
                nt.name as type,
                n.related_entity_type,
                n.related_entity_id,
                un.is_read,
                n.created_at
              FROM user_notifications un
              JOIN notifications n ON un.notification_id = n.id
              JOIN notification_types nt ON n.type_id = nt.id
              WHERE un.user_id = ? 
              AND un.deleted_at IS NULL";
    
    $params = [$userId];
    $types = "i";
    
    if ($unreadOnly) {
        $query .= " AND un.is_read = 0";
    }
    
    $query .= " ORDER BY n.created_at DESC";
    
    if ($limit > 0) {
        $query .= " LIMIT ?";
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
            'related_entity' => [
                'type' => $row['related_entity_type'],
                'id' => $row['related_entity_id']
            ]
        ];
    }
    
    // Mark notifications as read if this is a check for unread
    if ($unreadOnly && !empty($notifications)) {
        $notificationIds = array_column($notifications, 'id');
        $placeholders = rtrim(str_repeat('?,', count($notificationIds)), ',');
        
        $updateStmt = $conn->prepare("
            UPDATE user_notifications 
            SET is_read = 1, 
                read_at = NOW() 
            WHERE user_id = ? 
            AND notification_id IN ($placeholders)
        ");
        
        $updateParams = array_merge([$userId], $notificationIds);
        $updateStmt->bind_param(str_repeat('i', count($updateParams)), ...$updateParams);
        $updateStmt->execute();
    }
    
    $response['success'] = true;
    $response['notifications'] = $notifications;
    
} catch (Exception $e) {
    // Log the error for debugging
    error_log("Error in get_notifications.php: " . $e->getMessage());
    
    $response['message'] = 'An error occurred while fetching notifications.';
    http_response_code(500);
}

// Return JSON response
echo json_encode($response);
?>
