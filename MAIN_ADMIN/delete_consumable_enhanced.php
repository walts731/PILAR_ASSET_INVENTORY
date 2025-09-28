<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

// Set content type for JSON response
header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON input']);
    exit();
}

$consumable_id = isset($input['id']) ? (int)$input['id'] : 0;
$office = isset($input['office']) && $input['office'] !== '' ? $input['office'] : 'all';

if ($consumable_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid consumable ID']);
    exit();
}

// Begin transaction
$conn->begin_transaction();

try {
    // Lock and fetch the consumable with all related information
    $stmt = $conn->prepare("
        SELECT a.*, 
               COALESCE(c.category_name, 'Uncategorized') AS category_name,
               COALESCE(o.office_name, 'No Office') AS office_name
        FROM assets a 
        LEFT JOIN categories c ON a.category = c.id 
        LEFT JOIN offices o ON a.office_id = o.id
        WHERE a.id = ? AND a.type = 'consumable' 
        FOR UPDATE
    ");
    $stmt->bind_param('i', $consumable_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $consumable = $result->fetch_assoc();
    $stmt->close();

    if (!$consumable) {
        throw new Exception('Consumable not found or is not a consumable type');
    }

    // Validate that this is indeed a consumable
    if ($consumable['type'] !== 'consumable') {
        throw new Exception('Asset is not a consumable item');
    }

    // Archive the consumable to assets_archive table before deletion
    $archive_query = $conn->prepare("
        INSERT INTO assets_archive 
        (id, asset_name, category, description, quantity, unit, status, acquisition_date, 
         office_id, red_tagged, last_updated, value, qr_code, type, archived_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    // Prepare archive data with proper type casting
    $id = (int)$consumable['id'];
    $asset_name = $consumable['asset_name'] ?? null;
    $category = isset($consumable['category']) ? (int)$consumable['category'] : null;
    $description = $consumable['description'] ?? null;
    $quantity = isset($consumable['quantity']) ? (int)$consumable['quantity'] : 0;
    $unit = $consumable['unit'] ?? null;
    $status = $consumable['status'] ?? 'available';
    $acquisition_date = $consumable['acquisition_date'] ?? null;
    $office_id = isset($consumable['office_id']) ? (int)$consumable['office_id'] : null;
    $red_tagged = isset($consumable['red_tagged']) ? (int)$consumable['red_tagged'] : 0;
    $last_updated = $consumable['last_updated'] ?? null;
    $value = isset($consumable['value']) ? (float)$consumable['value'] : 0.00;
    $qr_code = $consumable['qr_code'] ?? null;
    $type = $consumable['type'] ?? 'consumable';

    // Bind parameters for archive
    $archive_query->bind_param(
        'isisisssiisdss',
        $id,
        $asset_name,
        $category,
        $description,
        $quantity,
        $unit,
        $status,
        $acquisition_date,
        $office_id,
        $red_tagged,
        $last_updated,
        $value,
        $qr_code,
        $type
    );

    if (!$archive_query->execute()) {
        throw new Exception('Failed to archive consumable: ' . $archive_query->error);
    }
    $archive_query->close();

    // Delete related records first (if any)
    // Use try-catch for each cleanup to avoid stopping on missing tables/columns
    
    // Clean up mr_details (most likely to exist)
    try {
        $cleanup_mr = $conn->prepare("DELETE FROM mr_details WHERE asset_id = ?");
        if ($cleanup_mr) {
            $cleanup_mr->bind_param('i', $consumable_id);
            $cleanup_mr->execute();
            $cleanup_mr->close();
        }
    } catch (Exception $e) {
        // Ignore if table/column doesn't exist
        error_log("Cleanup mr_details failed (safe to ignore): " . $e->getMessage());
    }

    // Clean up other potential dependencies (consumables are less likely to be in these)
    $cleanup_tables = [
        'ics_items' => 'asset_id',
        'par_items' => 'asset_id', 
        'ris_items' => 'asset_id',
        'asset_items' => 'asset_id'
    ];

    foreach ($cleanup_tables as $table => $column) {
        try {
            $cleanup_stmt = $conn->prepare("DELETE FROM $table WHERE $column = ?");
            if ($cleanup_stmt) {
                $cleanup_stmt->bind_param('i', $consumable_id);
                $cleanup_stmt->execute();
                $cleanup_stmt->close();
            }
        } catch (Exception $e) {
            // Ignore if table/column doesn't exist - this is expected for some tables
            error_log("Cleanup $table failed (safe to ignore): " . $e->getMessage());
        }
    }

    // Temporarily disable foreign key checks for deletion
    $conn->query('SET FOREIGN_KEY_CHECKS=0');

    // Delete the main consumable record
    $delete_query = $conn->prepare('DELETE FROM assets WHERE id = ?');
    $delete_query->bind_param('i', $consumable_id);
    
    if (!$delete_query->execute()) {
        throw new Exception('Failed to delete consumable: ' . $delete_query->error);
    }
    $delete_query->close();

    // Re-enable foreign key checks
    $conn->query('SET FOREIGN_KEY_CHECKS=1');

    // Calculate total value for logging
    $total_value = $quantity * $value;

    // Create comprehensive audit log
    $deletion_context = sprintf(
        'Consumable Deletion - Qty: %d, Unit Value: ₱%s, Total Value: ₱%s, Office: %s, Category: %s, Status: %s, Source: Enhanced Delete System',
        $quantity,
        number_format($value, 2),
        number_format($total_value, 2),
        $consumable['office_name'],
        $consumable['category_name'],
        ucfirst($status)
    );

    // Log the deletion activity
    logAssetActivity(
        'DELETE_CONSUMABLE_ENHANCED', 
        $description ?? 'Consumable Item', 
        $consumable_id, 
        $deletion_context
    );

    // Commit the transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'success' => true, 
        'message' => 'Consumable deleted and archived successfully',
        'data' => [
            'id' => $consumable_id,
            'description' => $description,
            'quantity' => $quantity,
            'total_value' => $total_value,
            'office' => $consumable['office_name'],
            'category' => $consumable['category_name']
        ]
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log the error
    $error_message = 'Enhanced consumable deletion failed for ID: ' . $consumable_id . ' - ' . $e->getMessage();
    logErrorActivity('Consumables Enhanced Delete', $error_message);
    
    // Return error response
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to delete consumable: ' . $e->getMessage(),
        'error_code' => 'DELETE_FAILED'
    ]);
}

// Close database connection
$conn->close();
?>
