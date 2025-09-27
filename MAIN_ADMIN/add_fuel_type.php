<?php
require_once '../connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// Ensure permissions table exists (first-run safety)
$conn->query("CREATE TABLE IF NOT EXISTS user_permissions (
  user_id INT NOT NULL,
  permission VARCHAR(100) NOT NULL,
  PRIMARY KEY (user_id, permission),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

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
