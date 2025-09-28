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
$filename = 'fuel_log_export' . $filter_suffix . '_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// CSV column headers (match table columns in fuel log tab)
fputcsv($out, [
  'Date & Time',
  'Fuel Type',
  'Quantity (L)',
  'Unit Price',
  'Total Cost',
  'Storage',
  'DR No.',
  'Supplier',
  'Received By',
  'Remarks'
]);

// Build SQL query with date filtering
$sql = "SELECT date_time, fuel_type, quantity, unit_price, total_cost, storage_location, delivery_receipt, supplier_name, received_by, remarks FROM fuel_records";
$params = [];
$types = '';

// Add date filtering if specified
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $sql .= " WHERE DATE(date_time) >= ? AND DATE(date_time) <= ?";
  $params[] = $from_date;
  $params[] = $to_date;
  $types = 'ss';
}

$sql .= " ORDER BY date_time DESC, id DESC";

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
      $r['date_time'] ?? '',
      $r['fuel_type'] ?? '',
      number_format((float)($r['quantity'] ?? 0), 2, '.', ''),
      number_format((float)($r['unit_price'] ?? 0), 2, '.', ''),
      number_format((float)($r['total_cost'] ?? 0), 2, '.', ''),
      $r['storage_location'] ?? '',
      $r['delivery_receipt'] ?? '',
      $r['supplier_name'] ?? '',
      $r['received_by'] ?? '',
      $r['remarks'] ?? ''
    ];
    fputcsv($out, $row);
  }
}

// Close prepared statement if used
if (isset($stmt)) {
  $stmt->close();
}

fclose($out);
exit;
?>
