<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';
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
    $input = json_decode(file_get_contents('php://input'), true);
    
    $assetCount = intval($input['assetCount'] ?? 0);
    $categoryCode = trim($input['categoryCode'] ?? '');
    
    if ($assetCount <= 0) {
        throw new Exception('Invalid asset count');
    }
    
    // Initialize TagFormatHelper
    $tagHelper = new TagFormatHelper($conn);
    
    $generatedNumbers = [];
    
    // Generate numbers for each asset
    for ($i = 0; $i < $assetCount; $i++) {
        $numbers = [];
        
        // Generate Property Number using TagFormatHelper (configurable via tag_formats), with fallback
        $propertyNumber = $tagHelper->generateNextTag('property_no');
        if ($propertyNumber === false || $propertyNumber === null || $propertyNumber === '') {
            // Fallback to legacy simple incremental when no active format is set
            $propertyNumber = generatePropertyNumber($conn, $i);
        }
        $numbers['property_no'] = $propertyNumber;
        
        // Generate Inventory Tag using TagFormatHelper with PROPERTY_NO replacement support
        $inventoryTag = $tagHelper->generateNextTag('inventory_tag', [ 'PROPERTY_NO' => $propertyNumber ]);
        $numbers['inventory_tag'] = $inventoryTag ?: generateFallbackInventoryTag($conn, $i);
        
        // Generate Asset Code using TagFormatHelper with category code
        if (!empty($categoryCode)) {
            $assetCode = $tagHelper->generateNextTag('asset_code', ['CODE' => $categoryCode]);
            $numbers['asset_code'] = $assetCode ?: generateFallbackAssetCode($conn, $categoryCode, $i);
        } else {
            $numbers['asset_code'] = generateFallbackAssetCode($conn, 'MISC', $i);
        }
        
        // Generate Serial Number using TagFormatHelper
        $serialNumber = $tagHelper->generateNextTag('serial_no');
        $numbers['serial_no'] = $serialNumber ?: generateFallbackSerialNumber($conn, $i);
        
        $generatedNumbers[] = $numbers;
    }
    
    echo json_encode([
        'success' => true,
        'numbers' => $generatedNumbers
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

function generatePropertyNumber($conn, $index) {
    $year = date('Y');
    
    // Get the next sequence number for property numbers
    $stmt = $conn->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(property_no, '-', -1) AS UNSIGNED)), 0) + 1 + ? as next_seq 
                           FROM assets 
                           WHERE property_no LIKE ?");
    $pattern = "{$year}-%";
    $stmt->bind_param('is', $index, $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $next_seq = $row['next_seq'];
    $stmt->close();
    
    return sprintf("%s-%04d", $year, $next_seq);
}

function generateFallbackInventoryTag($conn, $index) {
    $year = date('Y');
    
    // Get the next sequence number for inventory tags
    $stmt = $conn->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(inventory_tag, '-', -1) AS UNSIGNED)), 0) + 1 + ? as next_seq 
                           FROM assets 
                           WHERE inventory_tag LIKE ?");
    $pattern = "INV-{$year}-%";
    $stmt->bind_param('is', $index, $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $next_seq = $row['next_seq'];
    $stmt->close();
    
    return sprintf("INV-%s-%04d", $year, $next_seq);
}

function generateFallbackAssetCode($conn, $categoryCode, $index) {
    $year = date('Y');
    
    // Get the next sequence number for asset codes with this category
    $stmt = $conn->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(code, '-', -1) AS UNSIGNED)), 0) + 1 + ? as next_seq 
                           FROM assets 
                           WHERE code LIKE ?");
    $pattern = "{$year}-{$categoryCode}-%";
    $stmt->bind_param('is', $index, $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $next_seq = $row['next_seq'];
    $stmt->close();
    
    return sprintf("%s-%s-%04d", $year, $categoryCode, $next_seq);
}

function generateFallbackSerialNumber($conn, $index) {
    $year = date('Y');
    
    // Get the next sequence number for serial numbers
    $stmt = $conn->prepare("SELECT COALESCE(MAX(CAST(SUBSTRING_INDEX(serial_no, '-', -1) AS UNSIGNED)), 0) + 1 + ? as next_seq 
                           FROM assets 
                           WHERE serial_no LIKE ?");
    $pattern = "SN-{$year}-%";
    $stmt->bind_param('is', $index, $pattern);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $next_seq = $row['next_seq'];
    $stmt->close();
    
    return sprintf("SN-%s-%04d", $year, $next_seq);
}
?>
