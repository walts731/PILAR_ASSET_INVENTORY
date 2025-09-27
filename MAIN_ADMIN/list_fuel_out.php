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

// Ensure table exists with fo_fuel_type column
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
// Best-effort add column in case table existed before without it
$conn->query("ALTER TABLE fuel_out ADD COLUMN fo_fuel_type VARCHAR(100) NOT NULL AFTER fo_time_in");

$rows = [];
$sql = "SELECT id, fo_date, fo_time_in, fo_fuel_type, fo_fuel_no, fo_plate_no, fo_request, fo_liters, fo_vehicle_type, fo_receiver, fo_time_out
        FROM fuel_out
        ORDER BY fo_date DESC, id DESC";
$res = $conn->query($sql);
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $r['fo_liters'] = number_format((float)$r['fo_liters'], 2, '.', '');
    $rows[] = $r;
  }
}

echo json_encode(['success' => true, 'records' => $rows]);
