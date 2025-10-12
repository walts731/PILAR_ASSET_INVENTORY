<?php
require_once '../connect.php';
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    try {
        $sql = "
            SELECT 
                a.*,
                c.category_name,
                o.office_name,
                o.icon AS municipal_logo_path,
                CONCAT('qrcodes/asset_', a.id, '.png') AS qr_code_path
            FROM assets a
            LEFT JOIN categories c ON a.category = c.id
            LEFT JOIN offices o ON a.office_id = o.id
            WHERE a.id = ?
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Format the response
            $response = [
                'status' => 'success',
                'data' => [
                    'id' => $row['id'],
                    'asset_name' => $row['asset_name'],
                    'description' => $row['description'],
                    'category_name' => $row['category_name'],
                    'office_name' => $row['office_name'],
                    'type' => $row['type'],
                    'status' => $row['status'],
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'],
                    'serial_number' => $row['serial_no'],
                    'code' => $row['code'],
                    'property_no' => $row['property_no'],
                    'model' => $row['model'],
                    'brand' => $row['brand'],
                    'value' => $row['value'],
                    'acquisition_date' => $row['acquisition_date'],
                    'updated_at' => $row['last_updated'],
                    'image_path' => $row['image'],
                    'qr_code_path' => $row['qr_code_path'],
                    'municipal_logo_path' => $row['municipal_logo_path'],
                    'inventory_tag' => $row['inventory_tag']
                ]
            ];
            
            echo json_encode($response);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Asset not found or you do not have permission to view this asset.'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error fetching asset details: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No asset ID provided.'
    ]);
}

$conn->close();
?>
