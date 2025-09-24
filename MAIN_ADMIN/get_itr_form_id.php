<?php
require_once '../connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

try {
    // Query to find ITR form ID from forms table
    $stmt = $conn->prepare("SELECT id FROM forms WHERE category = 'itr' OR category = 'ITR' LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'form_id' => (int)$row['id']
        ]);
    } else {
        // If no ITR form found, return error
        echo json_encode([
            'success' => false,
            'error' => 'ITR form not found in database'
        ]);
    }
    
    $stmt->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
