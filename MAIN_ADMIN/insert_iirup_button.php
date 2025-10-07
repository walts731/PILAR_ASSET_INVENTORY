<?php
require_once '../connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Get asset_id from POST data
$asset_id = isset($_POST['asset_id']) ? intval($_POST['asset_id']) : 0;

if ($asset_id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid asset ID']);
    exit();
}

try {
    // Fetch asset details
    $stmt = $conn->prepare("SELECT a.*, o.office_name 
                           FROM assets a 
                           LEFT JOIN offices o ON a.office_id = o.id 
                           WHERE a.id = ?");
    $stmt->bind_param('i', $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Asset not found']);
        exit();
    }
    
    $asset = $result->fetch_assoc();
    $stmt->close();
    
    // Check if asset is already in temp table
    $check_stmt = $conn->prepare("SELECT id FROM temp_iirup_items WHERE asset_id = ?");
    $check_stmt->bind_param('i', $asset_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Asset already added to IIRUP list']);
        exit();
    }
    $check_stmt->close();
    
    // Insert asset into temp_iirup_items table
    $insert_stmt = $conn->prepare("INSERT INTO temp_iirup_items 
                                  (asset_id, date_acquired, particulars, property_no, quantity, unit, unit_cost, office, code) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $date_acquired = $asset['acquisition_date'];
    $particulars = $asset['description'];
    $property_no = $asset['property_no'];
    $quantity = $asset['quantity'];
    $unit = $asset['unit'];
    $unit_cost = $asset['value'];
    $office = $asset['office_name'];
    $code = $asset['code']; // Empty as requested
    
    $insert_stmt->bind_param('isssissss', 
        $asset_id, 
        $date_acquired, 
        $particulars, 
        $property_no, 
        $quantity, 
        $unit, 
        $unit_cost, 
        $office, 
        $code
    );
    
    if ($insert_stmt->execute()) {
        // Redirect to IIRUP form after successful insertion
        echo json_encode([
            'success' => true, 
            'message' => 'Asset added to IIRUP list successfully',
            'redirect' => 'forms.php?id=7'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add asset to IIRUP list']);
    }
    
    $insert_stmt->close();
    
} catch (Exception $e) {
    error_log("Error in insert_iirup_button.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while adding asset']);
}

$conn->close();
?>
