<?php
require_once '../connect.php';
header('Content-Type: application/json');

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get red tag ID from URL
$red_tag_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($red_tag_id <= 0) {
    echo json_encode(['error' => 'Invalid red tag ID']);
    exit();
}

try {
    // Fetch red tag details
    $stmt = $conn->prepare("SELECT id, asset_id, iirup_id, red_tag_number, removal_reason, action, condition_assessment, tagged_by, date_tagged FROM red_tags WHERE id = ?");
    $stmt->bind_param('i', $red_tag_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['error' => 'Red tag not found']);
        exit();
    }
    
    $red_tag = $result->fetch_assoc();
    $stmt->close();
    
    // Return red tag details
    echo json_encode([
        'id' => (int)$red_tag['id'],
        'asset_id' => (int)$red_tag['asset_id'],
        'iirup_id' => (int)$red_tag['iirup_id'],
        'red_tag_number' => $red_tag['red_tag_number'],
        'removal_reason' => $red_tag['removal_reason'],
        'action' => $red_tag['action'],
        'condition_assessment' => $red_tag['condition_assessment'],
        'tagged_by' => $red_tag['tagged_by'],
        'date_tagged' => $red_tag['date_tagged']
    ]);
    
} catch (Exception $e) {
    error_log('get_red_tag_details error: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error']);
}

$conn->close();
?>
