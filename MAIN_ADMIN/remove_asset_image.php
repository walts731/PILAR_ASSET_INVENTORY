<?php
require_once '../connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

// Check if required parameters are provided
if (!isset($_POST['asset_id']) || !isset($_POST['image_name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
    exit();
}

$asset_id = intval($_POST['asset_id']);
$image_name = $_POST['image_name'];

// Validate asset_id
if ($asset_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid asset ID']);
    exit();
}

// Sanitize image name to prevent directory traversal
$image_name = basename($image_name);

try {
    // Get current additional images
    $stmt = $conn->prepare("SELECT additional_images FROM assets WHERE id = ?");
    $stmt->bind_param('i', $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $asset_data = $result->fetch_assoc();
    $stmt->close();
    
    if (!$asset_data) {
        echo json_encode(['success' => false, 'error' => 'Asset not found']);
        exit();
    }
    
    $current_images = [];
    if (!empty($asset_data['additional_images'])) {
        $current_images = json_decode($asset_data['additional_images'], true) ?: [];
    }
    
    // Check if image exists in the array
    $image_index = array_search($image_name, $current_images);
    if ($image_index === false) {
        echo json_encode(['success' => false, 'error' => 'Image not found in asset records']);
        exit();
    }
    
    // Remove image from array
    unset($current_images[$image_index]);
    $current_images = array_values($current_images); // Re-index array
    
    // Update database
    $update_stmt = $conn->prepare("UPDATE assets SET additional_images = ? WHERE id = ?");
    $images_json = json_encode($current_images);
    $update_stmt->bind_param('si', $images_json, $asset_id);
    
    if ($update_stmt->execute()) {
        $update_stmt->close();
        
        // Delete physical file
        $file_path = '../img/assets/' . $image_name;
        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                echo json_encode(['success' => true, 'message' => 'Image removed successfully']);
            } else {
                echo json_encode(['success' => true, 'message' => 'Database updated but failed to delete physical file']);
            }
        } else {
            echo json_encode(['success' => true, 'message' => 'Image removed from database (physical file not found)']);
        }
    } else {
        $update_stmt->close();
        echo json_encode(['success' => false, 'error' => 'Failed to update database']);
    }
    
} catch (Exception $e) {
    error_log("Error removing asset image: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'An error occurred while removing the image']);
}
?>
