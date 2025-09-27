<?php
require_once '../connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// Ensure table exists
$createSql = "CREATE TABLE IF NOT EXISTS fuel_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  date_time DATETIME NOT NULL,
  fuel_type VARCHAR(50) NOT NULL,
  quantity DECIMAL(12,2) NOT NULL DEFAULT 0,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_cost DECIMAL(12,2) NOT NULL DEFAULT 0,
  storage_location VARCHAR(255) NOT NULL,
  delivery_receipt VARCHAR(100) DEFAULT NULL,
  supplier_name VARCHAR(255) NOT NULL,
  received_by VARCHAR(255) NOT NULL,
  remarks TEXT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(date_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($createSql);

// Validate required fields
$required = ['date_time','fuel_type','quantity','unit_price','total_cost','storage_location','supplier_name','received_by'];
foreach ($required as $field) {
  if (!isset($_POST[$field]) || $_POST[$field] === '') {
    http_response_code(400);
    echo json_encode(['error' => "Missing field: $field"]);
    exit;
  }
}

// Sanitize and assign
$date_time = $_POST['date_time'];
// Normalize HTML datetime-local (YYYY-MM-DDTHH:MM) to MySQL DATETIME (YYYY-MM-DD HH:MM:SS)
$date_time = str_replace('T', ' ', $date_time);
if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $date_time)) {
  $date_time .= ':00';
}
$fuel_type = trim($_POST['fuel_type']);
$quantity = (float)$_POST['quantity'];
$unit_price = (float)$_POST['unit_price'];
$total_cost = (float)$_POST['total_cost'];
if ($quantity < 0 || $unit_price < 0 || $total_cost < 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Numeric values cannot be negative']);
  exit;
}
$storage_location = trim($_POST['storage_location']);
$delivery_receipt = isset($_POST['delivery_receipt']) ? trim($_POST['delivery_receipt']) : null;
$supplier_name = trim($_POST['supplier_name']);
$received_by = trim($_POST['received_by']);
$remarks = isset($_POST['remarks']) ? trim($_POST['remarks']) : null;
$created_by = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO fuel_records (date_time, fuel_type, quantity, unit_price, total_cost, storage_location, delivery_receipt, supplier_name, received_by, remarks, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to prepare statement']);
  exit;
}
$stmt->bind_param('ssdddsssssi', $date_time, $fuel_type, $quantity, $unit_price, $total_cost, $storage_location, $delivery_receipt, $supplier_name, $received_by, $remarks, $created_by);
$ok = $stmt->execute();
if (!$ok) {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to save record']);
  exit;
}
$id = $stmt->insert_id;
$stmt->close();

// Return the inserted row
echo json_encode([
  'success' => true,
  'record' => [
    'id' => $id,
    'date_time' => $date_time,
    'fuel_type' => $fuel_type,
    'quantity' => number_format($quantity, 2, '.', ''),
    'unit_price' => number_format($unit_price, 2, '.', ''),
    'total_cost' => number_format($total_cost, 2, '.', ''),
    'storage_location' => $storage_location,
    'delivery_receipt' => $delivery_receipt,
    'supplier_name' => $supplier_name,
    'received_by' => $received_by,
    'remarks' => $remarks,
  ]
]);
