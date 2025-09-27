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

$from = isset($_GET['from']) && $_GET['from'] !== '' ? $_GET['from'] : null; // YYYY-MM-DD
$to   = isset($_GET['to']) && $_GET['to'] !== '' ? $_GET['to'] : null;     // YYYY-MM-DD

$where = [];
$params = [];
$types = '';
if ($from) { $where[] = 'fo_date >= ?'; $params[] = $from; $types .= 's'; }
if ($to)   { $where[] = 'fo_date <= ?'; $params[] = $to;   $types .= 's'; }
$where_sql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';

$sql = "SELECT fo_date AS date, COALESCE(SUM(fo_liters),0) AS total_liters
        FROM fuel_out
        $where_sql
        GROUP BY fo_date
        ORDER BY fo_date ASC";

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
        'date' => $row['date'],
        'total_liters' => (float)($row['total_liters'] ?? 0)
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
