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
$conn->query("CREATE TABLE IF NOT EXISTS fuel_out (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fo_date DATE NOT NULL,
  fo_time_in TIME NOT NULL,
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

$rows = [];
$sql = "SELECT id, fo_date, fo_time_in, fo_fuel_no, fo_plate_no, fo_request, fo_liters, fo_vehicle_type, fo_receiver, fo_time_out
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
