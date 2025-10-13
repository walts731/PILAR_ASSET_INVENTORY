<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php?error=unauthorized');
    exit;
}

// Set content type to HTML for redirect
header('Content-Type: text/html; charset=utf-8');

// Check if file was uploaded
if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    header('Location: inventory.php?import=error&message=' . urlencode('Please select a valid CSV file.'));
    exit;
}

$file = $_FILES['csv_file']['tmp_name'];
$handle = fopen($file, 'r');

if ($handle === false) {
    header('Location: inventory.php?import=error&message=' . urlencode('Failed to open the uploaded file.'));
    exit;
}

// Get header row
$header = fgetcsv($handle);
if ($header === false) {
    header('Location: inventory.php?import=error&message=' . urlencode('Invalid CSV format. Could not read headers.'));
    exit;
}

$header = array_map('trim', $header);
$header = array_map('strtolower', $header);

// Required fields - office_name is required (like asset import)
$requiredFields = ['description', 'quantity', 'unit', 'unit_price'];
$officeFields = ['office_name']; // Must be office_name column
$headerMap = [];

// Map headers to database columns
$mapping = [
    'description' => 'description',
    'quantity' => 'quantity',
    'unit' => 'unit',
    'unit_price' => 'value',
    'office_name' => 'office_name',
    'acquisition_date' => 'acquisition_date'
];

// Function to get office ID by name
function getOfficeId($conn, $officeName) {
    $stmt = $conn->prepare("SELECT id FROM offices WHERE office_name = ? LIMIT 1");
    $stmt->bind_param('s', $officeName);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        return $row['id'];
    }
    return 0; // Return 0 if office not found
}

// Check if all required fields are present
foreach ($requiredFields as $field) {
    if (!in_array($field, $header)) {
        fclose($handle);
        header('Location: inventory.php?import=error&message=' . urlencode("Missing required field in CSV: $field"));
        exit;
    }
    $headerMap[$field] = array_search($field, $header);
}

// Check if office_name field is present (required for office lookup)
$officeFieldPresent = null;
foreach ($officeFields as $field) {
    if (in_array($field, $header)) {
        $officeFieldPresent = $field;
        $headerMap[$field] = array_search($field, $header);
        break;
    }
}

if ($officeFieldPresent === null) {
    fclose($handle);
    header('Location: inventory.php?import=error&message=' . urlencode("Missing required field in CSV: office_name"));
    exit;
}

// Map optional fields
foreach ($mapping as $dbField => $csvField) {
    if (!in_array($csvField, $requiredFields) && in_array($csvField, $header)) {
        $headerMap[$dbField] = array_search($csvField, $header);
    }
}

$conn->begin_transaction();
$imported = 0;
$errors = [];
$rowNumber = 1; // Start from 1 to account for header

// Process each row
while (($data = fgetcsv($handle)) !== false) {
    $rowNumber++;
    
    // Skip empty rows
    if (empty(array_filter($data))) {
        continue;
    }

    // Get office ID from office name (like asset import)
    $officeName = trim($data[$headerMap['office_name']] ?? '');
    $officeId = getOfficeId($conn, $officeName);

    if ($officeId <= 0) {
        $errors[] = "Row $rowNumber: Office '{$officeName}' not found in system";
        continue;
    }

    $rowData = [
        'description' => $data[$headerMap['description']] ?? '',
        'quantity' => (int)($data[$headerMap['quantity']] ?? 0),
        'unit' => $data[$headerMap['unit']] ?? '',
        'value' => (float)($data[$headerMap['unit_price']] ?? 0),
        'office_id' => $officeId,
        'type' => 'consumable',
        'status' => 'available',
        'acquisition_date' => !empty($data[$headerMap['acquisition_date']]) ? 
            date('Y-m-d', strtotime($data[$headerMap['acquisition_date']])) : date('Y-m-d'),
        'last_updated' => date('Y-m-d H:i:s')
    ];

    // Validate required fields
    if (empty($rowData['description']) || $rowData['quantity'] <= 0 || empty($rowData['unit']) || 
        $rowData['value'] < 0) {
        $errors[] = "Row $rowNumber: Missing or invalid required fields";
        continue;
    }

    // Insert into database
    try {
        $fields = array_keys($rowData);
        $placeholders = array_fill(0, count($fields), '?');
        $types = str_repeat('s', count($fields));
        
        $sql = "INSERT INTO assets (" . implode(', ', $fields) . 
               ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...array_values($rowData));
        
        if ($stmt->execute()) {
            $imported++;
        } else {
            $errors[] = "Row $rowNumber: " . $conn->error;
        }
        $stmt->close();
    } catch (Exception $e) {
        $errors[] = "Row $rowNumber: " . $e->getMessage();
    }
}

fclose($handle);

if (!empty($errors)) {
    $conn->rollback();
    $errorMessage = 'Some rows could not be imported. ' . implode(' ', array_slice($errors, 0, 3));
    if (count($errors) > 3) {
        $errorMessage .= ' (and ' . (count($errors) - 3) . ' more)';
    }
    header('Location: inventory.php?import=error&imported=' . $imported . '&message=' . urlencode($errorMessage));
    exit;
}

$conn->commit();
header('Location: inventory.php?import=success&imported=' . $imported . '&message=' . urlencode("Successfully imported $imported consumables"));
exit;