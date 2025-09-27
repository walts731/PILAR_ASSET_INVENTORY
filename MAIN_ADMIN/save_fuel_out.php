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
  if ($role === 'admin' || $role === 'office_admin') return true;
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

// Ensure required tables exist
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

// Ensure fuel_out has fo_fuel_type column and exists
$conn->query("CREATE TABLE IF NOT EXISTS fuel_out (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fo_date DATE NOT NULL,
  fo_time_in TIME NOT NULL,
  fo_fuel_type VARCHAR(100) NOT NULL,
  fo_fuel_no VARCHAR(100) DEFAULT NULL,
  fo_plate_no VARCHAR(100) DEFAULT NULL,
  fo_request VARCHAR(255) DEFAULT NULL,
  fo_liters DECIMAL(12,2) NOT NULL DEFAULT 0,
  fo_vehicle_type VARCHAR(100) DEFAULT NULL,
  fo_receiver VARCHAR(255) NOT NULL,
  fo_time_out TIME DEFAULT NULL,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX(fo_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Safely add fo_fuel_type column if missing
$colCheckSql = "SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'fuel_out' AND COLUMN_NAME = 'fo_fuel_type'";
$colRes = $conn->query($colCheckSql);
if ($colRes && $colRes->num_rows === 0) {
  // Add with DEFAULT '' to avoid strict mode errors
  $conn->query("ALTER TABLE fuel_out ADD COLUMN fo_fuel_type VARCHAR(100) NOT NULL DEFAULT '' AFTER fo_time_in");
}

$required = ['fo_date', 'fo_time_in', 'fo_liters', 'fo_receiver', 'fo_fuel_type'];
foreach ($required as $f) {
  if (!isset($_POST[$f]) || $_POST[$f] === '') {
    http_response_code(400);
    echo json_encode(['error' => "Missing field: $f"]);
    exit;
  }
}

$fo_date = $_POST['fo_date'];
$fo_time_in = $_POST['fo_time_in'];
$fo_fuel_type = trim($_POST['fo_fuel_type']);
$fo_fuel_no = isset($_POST['fo_fuel_no']) ? trim($_POST['fo_fuel_no']) : null;
$fo_plate_no = isset($_POST['fo_plate_no']) ? trim($_POST['fo_plate_no']) : null;
$fo_request = isset($_POST['fo_request']) ? trim($_POST['fo_request']) : null;
$fo_liters = (float)$_POST['fo_liters'];
if ($fo_liters < 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Liters cannot be negative']);
  exit;
}
$fo_vehicle_type = isset($_POST['fo_vehicle_type']) ? trim($_POST['fo_vehicle_type']) : null;
$fo_receiver = trim($_POST['fo_receiver']);
$fo_time_out = isset($_POST['fo_time_out']) && $_POST['fo_time_out'] !== '' ? $_POST['fo_time_out'] : null;
$created_by = (int)$_SESSION['user_id'];

// Begin transaction for atomic stock decrement + record insert
$conn->begin_transaction();

// Resolve or create fuel_type id
$fuel_type_id = null;
$insType = $conn->prepare("INSERT INTO fuel_types (name) VALUES (?) ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
$insType->bind_param('s', $fo_fuel_type);
if (!$insType->execute()) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to resolve fuel type']);
  exit;
}
$fuel_type_id = $conn->insert_id; // existing id if duplicate
$insType->close();

// Ensure stock row exists for the fuel type
$ensureStock = $conn->prepare("INSERT INTO fuel_stock (fuel_type_id, quantity) VALUES (?, 0) ON DUPLICATE KEY UPDATE fuel_type_id = fuel_type_id");
$ensureStock->bind_param('i', $fuel_type_id);
if (!$ensureStock->execute()) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to ensure stock row']);
  exit;
}
$ensureStock->close();

// Attempt atomic decrement with constraint (avoid negative)
$dec = $conn->prepare("UPDATE fuel_stock SET quantity = quantity - ? WHERE fuel_type_id = ? AND quantity >= ?");
$dec->bind_param('did', $fo_liters, $fuel_type_id, $fo_liters);
if (!$dec->execute()) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to update stock']);
  exit;
}
if ($dec->affected_rows === 0) {
  $conn->rollback();
  http_response_code(400);
  echo json_encode(['error' => 'Insufficient stock for selected fuel type']);
  exit;
}
$dec->close();

// Insert fuel out record
$stmt = $conn->prepare("INSERT INTO fuel_out (fo_date, fo_time_in, fo_fuel_type, fo_fuel_no, fo_plate_no, fo_request, fo_liters, fo_vehicle_type, fo_receiver, fo_time_out, created_by)
VALUES (?,?,?,?,?,?,?,?,?,?,?)");
if (!$stmt) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to prepare statement']);
  exit;
}
$stmt->bind_param('ssssssdsssi', $fo_date, $fo_time_in, $fo_fuel_type, $fo_fuel_no, $fo_plate_no, $fo_request, $fo_liters, $fo_vehicle_type, $fo_receiver, $fo_time_out, $created_by);
if (!$stmt->execute()) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to save record']);
  exit;
}
$id = $stmt->insert_id;
$stmt->close();

// Commit
$conn->commit();

  echo json_encode([
    'success' => true,
    'record' => [
      'id' => $id,
      'fo_date' => $fo_date,
      'fo_time_in' => $fo_time_in,
      'fo_fuel_type' => $fo_fuel_type,
      'fo_fuel_no' => $fo_fuel_no,
      'fo_plate_no' => $fo_plate_no,
      'fo_request' => $fo_request,
      'fo_liters' => number_format($fo_liters, 2, '.', ''),
      'fo_vehicle_type' => $fo_vehicle_type,
      'fo_receiver' => $fo_receiver,
      'fo_time_out' => $fo_time_out,
    ],
  ]);
