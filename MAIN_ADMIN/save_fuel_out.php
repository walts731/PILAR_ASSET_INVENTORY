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

$required = ['fo_date', 'fo_time_in', 'fo_liters', 'fo_receiver'];
foreach ($required as $f) {
  if (!isset($_POST[$f]) || $_POST[$f] === '') {
    http_response_code(400);
    echo json_encode(['error' => "Missing field: $f"]);
    exit;
  }
}

$fo_date = $_POST['fo_date'];
$fo_time_in = $_POST['fo_time_in'];
$fo_fuel_no = isset($_POST['fo_fuel_no']) ? trim($_POST['fo_fuel_no']) : null;
$fo_plate_no = isset($_POST['fo_plate_no']) ? trim($_POST['fo_plate_no']) : null;
$fo_request = isset($_POST['fo_request']) ? trim($_POST['fo_request']) : null;
$fo_liters = (float)$_POST['fo_liters'];
if ($fo_liters < 0) {
  http_response_code(400);
  echo json_encode(['error' => 'Liters cannot be negative']);
  exit;
}
$fo_vehicle_type = isset($_POST['fo_vehicle_type']) ? trim($_POST['fo_vehicle_type']) : null;
$fo_receiver = trim($_POST['fo_receiver']);
$fo_time_out = isset($_POST['fo_time_out']) && $_POST['fo_time_out'] !== '' ? $_POST['fo_time_out'] : null;
$created_by = (int)$_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO fuel_out (fo_date, fo_time_in, fo_fuel_no, fo_plate_no, fo_request, fo_liters, fo_vehicle_type, fo_receiver, fo_time_out, created_by)
VALUES (?,?,?,?,?,?,?,?,?,?)");
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to prepare statement']);
  exit;
}
$stmt->bind_param('sssssdsssi', $fo_date, $fo_time_in, $fo_fuel_no, $fo_plate_no, $fo_request, $fo_liters, $fo_vehicle_type, $fo_receiver, $fo_time_out, $created_by);
if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(['error' => 'Failed to save record']);
  exit;
}
$id = $stmt->insert_id;
$stmt->close();

echo json_encode([
  'success' => true,
  'record' => [
    'id' => $id,
    'fo_date' => $fo_date,
    'fo_time_in' => $fo_time_in,
    'fo_fuel_no' => $fo_fuel_no,
    'fo_plate_no' => $fo_plate_no,
    'fo_request' => $fo_request,
    'fo_liters' => number_format($fo_liters, 2, '.', ''),
    'fo_vehicle_type' => $fo_vehicle_type,
    'fo_receiver' => $fo_receiver,
    'fo_time_out' => $fo_time_out,
  ],
]);
