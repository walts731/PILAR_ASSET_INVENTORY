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
$conn->query("CREATE TABLE IF NOT EXISTS fuel_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
if ($name === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Fuel type name is required']);
  exit;
}

// Insert or reactivate existing type
$stmt = $conn->prepare("INSERT INTO fuel_types (name, is_active) VALUES (?, 1)
  ON DUPLICATE KEY UPDATE is_active = VALUES(is_active)");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to prepare statement']);
  exit;
}
$stmt->bind_param('s', $name);
$ok = $stmt->execute();
if (!$ok) {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to save fuel type']);
  exit;
}
$type_id = $conn->insert_id; // if duplicate, this will be 0
$stmt->close();

// If duplicate, fetch the existing id
if ($type_id === 0) {
  $q = $conn->prepare('SELECT id FROM fuel_types WHERE name = ? LIMIT 1');
  $q->bind_param('s', $name);
  $q->execute();
  $res = $q->get_result();
  if ($res && ($row = $res->fetch_assoc())) {
    $type_id = (int)$row['id'];
  }
  $q->close();
}

echo json_encode(['success' => true, 'id' => $type_id, 'name' => $name]);
