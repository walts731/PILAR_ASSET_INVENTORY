<?php
require_once '../connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'error' => 'Unauthorized']);
  exit;
}

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
  echo json_encode(['success' => false, 'error' => 'Forbidden']);
  exit;
}

$allowed_group = ['fo_request', 'fo_plate_no', 'fo_fuel_type', 'fo_receiver', 'fo_vehicle_type'];
$group_by = isset($_GET['group_by']) && in_array($_GET['group_by'], $allowed_group, true)
  ? $_GET['group_by']
  : 'fo_request';

$from = isset($_GET['from']) && $_GET['from'] !== '' ? $_GET['from'] : null; // YYYY-MM-DD
$to   = isset($_GET['to']) && $_GET['to'] !== '' ? $_GET['to'] : null;     // YYYY-MM-DD

$where = [];
$params = [];
$types = '';
if ($from) { $where[] = 'fo_date >= ?'; $params[] = $from; $types .= 's'; }
if ($to)   { $where[] = 'fo_date <= ?'; $params[] = $to;   $types .= 's'; }

$where_sql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT ".$group_by." AS group_key,
               COALESCE(SUM(fo_liters),0) AS total_liters,
               COUNT(*) AS trips,
               COUNT(DISTINCT NULLIF(TRIM(fo_plate_no),'')) AS unique_plates,
               GROUP_CONCAT(DISTINCT NULLIF(TRIM(fo_fuel_type),'') ORDER BY fo_fuel_type SEPARATOR ', ') AS fuel_types
        FROM fuel_out
        $where_sql
        GROUP BY ".$group_by.
       " ORDER BY total_liters DESC, trips DESC";

try {
  if ($stmt = $conn->prepare($sql)) {
    if (!empty($params)) {
      $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $records = [];
    while ($row = $res->fetch_assoc()) {
      $records[] = [
        'group_key' => $row['group_key'] ?? '',
        'total_liters' => (float)($row['total_liters'] ?? 0),
        'trips' => (int)($row['trips'] ?? 0),
        'unique_plates' => (int)($row['unique_plates'] ?? 0),
        'fuel_types' => $row['fuel_types'] ?? ''
      ];
    }
    $stmt->close();
    echo json_encode(['success' => true, 'records' => $records]);
    exit;
  } else {
    throw new Exception('Query prepare failed');
  }
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
  exit;
}
