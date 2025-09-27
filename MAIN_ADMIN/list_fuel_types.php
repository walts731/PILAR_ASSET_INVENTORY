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
