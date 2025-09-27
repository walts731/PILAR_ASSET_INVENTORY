<?php
require_once '../connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

$conn->query("CREATE TABLE IF NOT EXISTS fuel_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
if ($name === '') {
  http_response_code(400);
  echo json_encode(['error' => 'Name is required']);
  exit;
}

// Upsert-like behavior: try insert, if duplicate, just return existing
$stmt = $conn->prepare("INSERT INTO fuel_types (name) VALUES (?)");
if ($stmt && $stmt->bind_param('s', $name) && $stmt->execute()) {
  $id = $stmt->insert_id;
  echo json_encode(['success' => true, 'type' => ['id' => $id, 'name' => $name]]);
  exit;
}

// If duplicate, fetch existing
if ($conn->errno == 1062) { // duplicate entry
  $sel = $conn->prepare("SELECT id, name FROM fuel_types WHERE name = ? LIMIT 1");
  $sel->bind_param('s', $name);
  $sel->execute();
  $res = $sel->get_result();
  if ($res && ($row = $res->fetch_assoc())) {
    echo json_encode(['success' => true, 'type' => $row]);
    exit;
  }
}

http_response_code(500);
echo json_encode(['error' => 'Failed to add fuel type']);
