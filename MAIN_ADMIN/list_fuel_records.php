<?php
require_once '../connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// Ensure permissions table exists
$conn->query("CREATE TABLE IF NOT EXISTS user_permissions (
  user_id INT NOT NULL,
  permission VARCHAR(100) NOT NULL,
  PRIMARY KEY (user_id, permission),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Permission guard: allow admin/office_admin or explicit fuel_inventory permission
function user_has_fuel_permission(mysqli $conn, int $user_id): bool {
  // Check role
  $role = null;
  if ($stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1")) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) { $role = $row['role'] ?? null; }
    $stmt->close();
  }
  if ($role === 'admin' || $role === 'user') return true;
  // Check permission mapping
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

$uid = (int)$_SESSION['user_id'];
if (!user_has_fuel_permission($conn, $uid)) {
  http_response_code(403);
  echo json_encode(['error' => 'Forbidden: insufficient permission']);
  exit;
}

// Ensure table exists (same structure as save endpoint)
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

$records = [];
$sql = "SELECT id, date_time, fuel_type, quantity, unit_price, total_cost, storage_location, delivery_receipt, supplier_name, received_by, remarks FROM fuel_records ORDER BY date_time DESC, id DESC";
$res = $conn->query($sql);
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $r['quantity'] = number_format((float)$r['quantity'], 2, '.', '');
    $r['unit_price'] = number_format((float)$r['unit_price'], 2, '.', '');
    $r['total_cost'] = number_format((float)$r['total_cost'], 2, '.', '');
    $records[] = $r;
  }
}

echo json_encode(['success' => true, 'records' => $records]);
