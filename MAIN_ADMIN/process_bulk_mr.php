<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

require_once '../connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Log incoming data for debugging
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    
    // Start transaction
    $conn->begin_transaction();

    // Get common details
    $accountable_person = intval($_POST['accountable_person']);
    $office = intval($_POST['office']);
    $category = intval($_POST['category']);
    $date_received = $_POST['date_received'];
    $supplier = trim($_POST['supplier'] ?? '');
    $user_id = $_SESSION['user_id'];

    // Validate required fields
    if (empty($accountable_person) || empty($office) || empty($category) || empty($date_received)) {
        throw new Exception('Missing required common details');
    }

    // Get assets data
    $assets = $_POST['assets'] ?? [];
    if (empty($assets)) {
        throw new Exception('No assets provided');
    }

    $created_count = 0;
    $errors = [];

    // Process each asset
    foreach ($assets as $index => $asset) {
        try {
            // Validate asset data
            $asset_id = intval($asset['asset_id']);
            $property_no = trim($asset['property_no']);
            $inventory_tag = trim($asset['inventory_tag']);
            $asset_code = trim($asset['asset_code']);
            $serial_no = trim($asset['serial_no'] ?? '');
            $model = trim($asset['model'] ?? '');
            $brand = trim($asset['brand'] ?? '');
            $end_user = trim($asset['end_user'] ?? '');
            $description = trim($asset['description']);
            $value = floatval($asset['value']);
            $quantity = intval($asset['quantity']);
            $unit = trim($asset['unit']);

            // Handle image upload
            $image_filename = '';
            if (isset($_FILES['assets']['name'][$index]['image']) && !empty($_FILES['assets']['name'][$index]['image'])) {
                $image_filename = handleImageUpload($index, $asset_id);
                if ($image_filename === false) {
                    $errors[] = "Asset {$index}: Failed to upload image";
                    // Continue processing without image
                    $image_filename = '';
                }
            }

            if (empty($asset_id) || empty($property_no) || empty($inventory_tag)) {
                $errors[] = "Asset {$index}: Missing required data";
                continue;
            }

            // Check if asset exists and has no property tag
            $check_stmt = $conn->prepare("SELECT id FROM assets WHERE id = ? AND (property_no IS NULL OR property_no = '') AND (inventory_tag IS NULL OR inventory_tag = '')");
            $check_stmt->bind_param('i', $asset_id);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            
            if ($check_result->num_rows === 0) {
                $errors[] = "Asset {$index}: Asset not found or already has property tag";
                $check_stmt->close();
                continue;
            }
            $check_stmt->close();

            // Generate MR number
            $mr_number = generateMRNumber($conn);

            // Insert into mr_details table - match actual table structure
            $mr_stmt = $conn->prepare("INSERT INTO mr_details (
                item_id, asset_id, office_location, description, model_no, serial_no, 
                serviceable, unit_quantity, unit, acquisition_date, acquisition_cost, 
                person_accountable, end_user, acquired_date, counted_date, inventory_tag, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW())");

            if (!$mr_stmt) {
                $errors[] = "Asset {$index}: Failed to prepare MR statement - " . $conn->error;
                continue;
            }

            // Map our data to the actual table structure
            $serviceable_status = 1; // Default to serviceable
            $office_location = $office; // Use office ID as location
            
            $mr_stmt->bind_param('iisssssisssssss', 
                $asset_id,        // item_id (using asset_id)
                $asset_id,        // asset_id
                $office_location, // office_location
                $description,     // description
                $model,          // model_no
                $serial_no,      // serial_no
                $serviceable_status, // serviceable (1 = yes)
                $quantity,       // unit_quantity
                $unit,           // unit
                $date_received,  // acquisition_date
                $value,          // acquisition_cost
                $accountable_person, // person_accountable
                $end_user,       // end_user
                $date_received,  // acquired_date
                $inventory_tag   // inventory_tag
            );

            if (!$mr_stmt->execute()) {
                $errors[] = "Asset {$index}: Failed to create MR record - " . $mr_stmt->error;
                $mr_stmt->close();
                continue;
            }
            $mr_stmt->close();

            // Check which columns exist in assets table before updating
            $columns_to_update = ['property_no', 'inventory_tag', 'employee_id', 'office_id', 'status'];
            $values_to_bind = [$property_no, $inventory_tag, $accountable_person, $office, 'serviceable'];
            $bind_types = 'ssiis';
            
            // Check if optional columns exist
            $optional_columns = [
                'category' => [$category, 'i'],
                'model' => [$model, 's'],
                'brand' => [$brand, 's'],
                'serial_no' => [$serial_no, 's'],
                'code' => [$asset_code, 's'],
                'end_user' => [$end_user, 's'],
                'image' => [$image_filename, 's']
            ];
            
            foreach ($optional_columns as $column => $data) {
                $check_column = $conn->query("SHOW COLUMNS FROM assets LIKE '$column'");
                if ($check_column && $check_column->num_rows > 0) {
                    $columns_to_update[] = $column;
                    $values_to_bind[] = $data[0];
                    $bind_types .= $data[1];
                }
            }
            
            // Build dynamic UPDATE query
            $set_clause = implode(' = ?, ', $columns_to_update) . ' = ?, last_updated = NOW()';
            $update_query = "UPDATE assets SET $set_clause WHERE id = ?";
            $values_to_bind[] = $asset_id;
            $bind_types .= 'i';
            
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param($bind_types, ...$values_to_bind);

            if (!$update_stmt->execute()) {
                $errors[] = "Asset {$index}: Failed to update asset - " . $update_stmt->error;
                $update_stmt->close();
                continue;
            }
            $update_stmt->close();

            // Update tag counters for next generation
            updateTagCounter($conn, 'property_no');
            updateTagCounter($conn, 'inventory_tag');
            updateTagCounter($conn, 'asset_code');

            $created_count++;

        } catch (Exception $e) {
            $errors[] = "Asset {$index}: " . $e->getMessage();
        }
    }

    // Check if any assets were processed successfully
    if ($created_count === 0) {
        throw new Exception('No assets were processed successfully. Errors: ' . implode(', ', $errors));
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    $response = [
        'success' => true,
        'count' => $created_count,
        'message' => "Successfully created property tags for {$created_count} assets"
    ];

    if (!empty($errors)) {
        $response['warnings'] = $errors;
        $response['message'] .= '. Some assets had errors: ' . implode(', ', array_slice($errors, 0, 3));
        if (count($errors) > 3) {
            $response['message'] .= ' and ' . (count($errors) - 3) . ' more.';
        }
    }

    echo json_encode($response);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error for debugging
    error_log("Bulk MR creation error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'debug' => $e->getTraceAsString()
    ]);
} catch (Error $e) {
    // Catch fatal errors
    $conn->rollback();
    
    error_log("Bulk MR creation fatal error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage(),
        'debug' => $e->getTraceAsString()
    ]);
}

function generateMRNumber($conn) {
    // Since mr_details table doesn't have mr_no column, 
    // we'll generate a simple incremental number based on mr_id
    $year = date('Y');
    
    // Get the next sequence number based on existing records
    $stmt = $conn->prepare("SELECT COALESCE(MAX(mr_id), 0) + 1 as next_seq FROM mr_details");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $next_seq = $row['next_seq'];
    $stmt->close();
    
    return sprintf("MR-%s-%04d", $year, $next_seq);
}

function updateTagCounter($conn, $tag_type) {
    // Update or insert tag counter
    $stmt = $conn->prepare("INSERT INTO tag_counters (tag_type, current_count) VALUES (?, 1) 
                           ON DUPLICATE KEY UPDATE current_count = current_count + 1");
    $stmt->bind_param('s', $tag_type);
    $stmt->execute();
    $stmt->close();
}

function handleImageUpload($index, $asset_id) {
    // Check if file was uploaded
    if (!isset($_FILES['assets']['tmp_name'][$index]['image']) || 
        $_FILES['assets']['error'][$index]['image'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    $file = [
        'name' => $_FILES['assets']['name'][$index]['image'],
        'type' => $_FILES['assets']['type'][$index]['image'],
        'tmp_name' => $_FILES['assets']['tmp_name'][$index]['image'],
        'error' => $_FILES['assets']['error'][$index]['image'],
        'size' => $_FILES['assets']['size'][$index]['image']
    ];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        return false;
    }
    
    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return false;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'asset_' . $asset_id . '_' . time() . '_' . $index . '.' . $extension;
    
    // Create upload directory if it doesn't exist
    $upload_dir = '../img/assets/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $upload_path = $upload_dir . $filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $filename;
    }
    
    return false;
}
?>
