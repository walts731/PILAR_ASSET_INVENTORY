<?php
require_once '../connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// Permission guard: admin/office_admin or explicit fuel_inventory permission
function user_has_fuel_permission(mysqli $conn, int $user_id): bool {
  $role = null;
  if ($stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1")) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) { $role = $row['role'] ?? null; }
    $stmt->close();
  }
  if ($role === 'admin' || $role === 'user') return true;
  if ($stmt2 = $conn->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = 'fuel_inventory' LIMIT 1")) {
    $stmt2->bind_param('i', $user_id);
    $stmt2->execute();
    $stmt2->store_result();
    $ok = $stmt2->num_rows > 0;
    $stmt2->close();
    return $ok;
  }
  return false;
}

if (!user_has_fuel_permission($conn, (int)$_SESSION['user_id'])) {
  http_response_code(403);
  echo json_encode(['error' => 'Forbidden: insufficient permission']);
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

// Ensure fuel_types and fuel_stock tables exist
$conn->query("CREATE TABLE IF NOT EXISTS fuel_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$conn->query("CREATE TABLE IF NOT EXISTS fuel_stock (
  fuel_type_id INT NOT NULL UNIQUE,
  quantity DECIMAL(14,2) NOT NULL DEFAULT 0,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (fuel_type_id) REFERENCES fuel_types(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

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

// Begin transaction
$conn->begin_transaction();

// Resolve or create fuel_type id
$fuel_type_id = null;
$insType = $conn->prepare("INSERT INTO fuel_types (name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
$insType->bind_param('s', $fuel_type);
if (!$insType->execute()) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to resolve fuel type']);
  exit;
}
$fuel_type_id = $conn->insert_id; // existing id if duplicate
$insType->close();

// Insert fuel record
$stmt = $conn->prepare("INSERT INTO fuel_records (date_time, fuel_type, quantity, unit_price, total_cost, storage_location, delivery_receipt, supplier_name, received_by, remarks, created_by) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to prepare statement']);
  exit;
}
$stmt->bind_param('ssdddsssssi', $date_time, $fuel_type, $quantity, $unit_price, $total_cost, $storage_location, $delivery_receipt, $supplier_name, $received_by, $remarks, $created_by);
$ok = $stmt->execute();
if (!$ok) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to save record']);
  exit;
}
$id = $stmt->insert_id;
$stmt->close();

// Upsert stock increment
$upsertStock = $conn->prepare("INSERT INTO fuel_stock (fuel_type_id, quantity) VALUES (?, ?) ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)");
$upsertStock->bind_param('id', $fuel_type_id, $quantity);
if (!$upsertStock->execute()) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to update stock']);
  exit;
}
$upsertStock->close();

// Commit transaction
$conn->commit();

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
