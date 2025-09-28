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

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? 'all';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

// Build filename with filter info
$filter_suffix = '';
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $filter_suffix = '_' . str_replace(['-', ' '], ['', '_'], $filter_type) . '_' . $from_date . '_to_' . $to_date;
}
$filename = 'fuel_out_export' . $filter_suffix . '_' . date('Ymd_His') . '.csv';

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

// Build SQL query with date filtering
$sql = "SELECT fo_date, fo_time_in, fo_fuel_type, fo_fuel_no, fo_plate_no, fo_request, fo_liters, fo_vehicle_type, fo_receiver, fo_time_out FROM fuel_out";
$params = [];
$types = '';

// Add date filtering if specified
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $sql .= " WHERE fo_date >= ? AND fo_date <= ?";
  $params[] = $from_date;
  $params[] = $to_date;
  $types = 'ss';
}

$sql .= " ORDER BY fo_date DESC, id DESC";

// Execute query with or without parameters
if (!empty($params)) {
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $res = $stmt->get_result();
} else {
  $res = $conn->query($sql);
}

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

// Close prepared statement if used
if (isset($stmt)) {
  $stmt->close();
}

// Insert record into generated_reports table for tracking
$user_id = $_SESSION['user_id'];
$office_id = $_SESSION['office_id'] ?? null;

try {
  $insert_stmt = $conn->prepare("INSERT INTO generated_reports (user_id, office_id, filename, generated_at) VALUES (?, ?, ?, NOW())");
  $insert_stmt->bind_param("iis", $user_id, $office_id, $filename);
  $insert_stmt->execute();
  $insert_stmt->close();
} catch (Exception $e) {
  // Log error but don't interrupt the export
  error_log("Failed to insert fuel out CSV export into generated_reports: " . $e->getMessage());
}

fclose($out);
exit;
