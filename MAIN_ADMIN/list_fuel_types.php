<?php
require_once '../connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// Create fuel_types table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS fuel_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  is_active TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Seed default types if table is empty
$defaults = ['Diesel', 'Kerosene', 'Unleaded', 'Premium'];
$res = $conn->query("SELECT COUNT(*) AS c FROM fuel_types");
if ($res && ($row = $res->fetch_assoc()) && (int)$row['c'] === 0) {
  $stmt = $conn->prepare("INSERT INTO fuel_types (name) VALUES (?), (?), (?), (?)");
  $stmt->bind_param('ssss', $defaults[0], $defaults[1], $defaults[2], $defaults[3]);
  $stmt->execute();
  $stmt->close();
}

$out = [];
$r = $conn->query("SELECT id, name, is_active FROM fuel_types WHERE is_active = 1 ORDER BY name ASC");
if ($r) {
  while ($row = $r->fetch_assoc()) { $out[] = $row; }
}

echo json_encode(['success' => true, 'types' => $out]);
