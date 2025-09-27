<?php
require_once '../connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// Ensure tables exist (safety)
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

$conn->query("CREATE TABLE IF NOT EXISTS fuel_records (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Invalid record id']);
  exit;
}

// Fetch record to know fuel type and quantity
$rec = null;
$stmt = $conn->prepare('SELECT fuel_type, quantity FROM fuel_records WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res && ($row = $res->fetch_assoc())) {
  $rec = $row;
}
$stmt->close();

if (!$rec) {
  http_response_code(404);
  echo json_encode(['error' => 'Record not found']);
  exit;
}

$fuel_type_name = $rec['fuel_type'];
$qty = (float)$rec['quantity'];

$conn->begin_transaction();

// Get or create fuel_type_id
$fuel_type_id = null;
$insType = $conn->prepare("INSERT INTO fuel_types (name) VALUES (?) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)");
$insType->bind_param('s', $fuel_type_name);
if (!$insType->execute()) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to resolve fuel type']);
  exit;
}
$fuel_type_id = $conn->insert_id;
$insType->close();

// Ensure stock row exists
$ensure = $conn->prepare('INSERT IGNORE INTO fuel_stock (fuel_type_id, quantity) VALUES (?, 0)');
$ensure->bind_param('i', $fuel_type_id);
if (!$ensure->execute()) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to ensure stock row']);
  exit;
}
$ensure->close();

// Decrement stock, not below zero
$upd = $conn->prepare('UPDATE fuel_stock SET quantity = GREATEST(quantity - ?, 0) WHERE fuel_type_id = ?');
$upd->bind_param('di', $qty, $fuel_type_id);
if (!$upd->execute()) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to update stock']);
  exit;
}
$upd->close();

// Delete the record
$del = $conn->prepare('DELETE FROM fuel_records WHERE id = ?');
$del->bind_param('i', $id);
if (!$del->execute()) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['error' => 'Failed to delete record']);
  exit;
}
$del->close();

$conn->commit();

echo json_encode(['success' => true]);
