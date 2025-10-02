<?php
require_once '../connect.php';
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
header('Content-Type: application/json');

// Auth guard (basic): ensure user is logged in
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['results' => [], 'message' => 'Unauthorized']);
  exit;
}

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if ($q === '' || mb_strlen($q) < 2) {
  echo json_encode(['results' => []]);
  exit;
}

// Build LIKE pattern safely
$like = '%' . $conn->real_escape_string($q) . '%';

// Search main assets table for items (type='asset')
$sql = "
  SELECT 
    a.id,
    a.description,
    a.inventory_tag,
    a.property_no,
    a.serial_no,
    c.category_name,
    f.ics_no,
    p.par_no
  FROM assets a
  LEFT JOIN categories c ON a.category = c.id
  LEFT JOIN ics_form f ON a.ics_id = f.id
  LEFT JOIN par_form p ON a.par_id = p.id
  WHERE a.type = 'asset'
    AND a.quantity > 0
    AND (
      a.description LIKE ?
      OR a.inventory_tag LIKE ?
      OR a.property_no LIKE ?
      OR a.serial_no LIKE ?
    )
  ORDER BY a.last_updated DESC
  LIMIT 20
";

$stmt = $conn->prepare($sql);
if (!$stmt) {
  http_response_code(500);
  echo json_encode(['results' => [], 'message' => 'Failed to prepare statement']);
  exit;
}

$stmt->bind_param('ssss', $like, $like, $like, $like);
$stmt->execute();
$res = $stmt->get_result();
$rows = [];
while ($row = $res->fetch_assoc()) {
  $rows[] = [
    'id' => (int)$row['id'],
    'description' => $row['description'],
    'inventory_tag' => $row['inventory_tag'],
    'property_no' => $row['property_no'],
    'serial_no' => $row['serial_no'],
    'category_name' => $row['category_name'],
    'ics_no' => $row['ics_no'],
    'par_no' => $row['par_no'],
  ];
}
$stmt->close();

echo json_encode(['results' => $rows]);
