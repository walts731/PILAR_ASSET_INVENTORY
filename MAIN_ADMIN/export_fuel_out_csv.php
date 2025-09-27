<?php
require_once '../connect.php';
session_start();

// Auth check
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo 'Unauthorized';
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
  if ($role === 'admin' || $role === 'office_admin' || $role === 'user') return true;
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
  echo 'Forbidden';
  exit;
}

// Prepare CSV headers
$filename = 'fuel_out_export_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// CSV column headers (match table columns in fuel_out tab)
fputcsv($out, [
  'Date',
  'Time In',
  'Fuel Type',
  'Fuel No',
  'Plate No',
  'Request',
  'No. of Liters',
  'Vehicle Type',
  'Receiver',
  'Time Out'
]);

$sql = "SELECT fo_date, fo_time_in, fo_fuel_type, fo_fuel_no, fo_plate_no, fo_request, fo_liters, fo_vehicle_type, fo_receiver, fo_time_out FROM fuel_out ORDER BY fo_date DESC, id DESC";
$res = $conn->query($sql);
if ($res) {
  while ($r = $res->fetch_assoc()) {
    // Ensure formatting similar to UI
    $row = [
      $r['fo_date'] ?? '',
      $r['fo_time_in'] ?? '',
      $r['fo_fuel_type'] ?? '',
      $r['fo_fuel_no'] ?? '',
      $r['fo_plate_no'] ?? '',
      $r['fo_request'] ?? '',
      number_format((float)($r['fo_liters'] ?? 0), 2, '.', ''),
      $r['fo_vehicle_type'] ?? '',
      $r['fo_receiver'] ?? '',
      $r['fo_time_out'] ?? ''
    ];
    fputcsv($out, $row);
  }
}

fclose($out);
exit;
