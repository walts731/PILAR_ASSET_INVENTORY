<?php
require_once '../connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit;
}

// Ensure tables exist
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

$sql = "SELECT t.id, t.name, COALESCE(s.quantity, 0) AS quantity, s.updated_at
        FROM fuel_types t
        LEFT JOIN fuel_stock s ON s.fuel_type_id = t.id
        WHERE t.is_active = 1
        ORDER BY t.name ASC";
$res = $conn->query($sql);
$rows = [];
if ($res) {
  while ($r = $res->fetch_assoc()) {
    $r['quantity'] = number_format((float)$r['quantity'], 2, '.', '');
    $rows[] = $r;
  }
}

echo json_encode(['success' => true, 'stock' => $rows]);
