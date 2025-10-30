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
// Include related offices to resolve {OFFICE} placeholder for ICS & PAR numbers
$sql = "
  SELECT 
    a.id,
    a.description,
    a.inventory_tag,
    a.property_no,
    a.serial_no,
    c.category_name,
    f.ics_no,
    f.office_id AS ics_office_id,
    o_ics.office_name AS ics_office_name,
    p.par_no,
    p.office_id AS par_office_id,
    o_par.office_name AS par_office_name,
    o_asset.office_name AS asset_office_name
  FROM assets a
  LEFT JOIN categories c ON a.category = c.id
  LEFT JOIN offices o_asset ON a.office_id = o_asset.id
  LEFT JOIN ics_form f ON a.ics_id = f.id
  LEFT JOIN offices o_ics ON f.office_id = o_ics.id
  LEFT JOIN par_form p ON a.par_id = p.id
  LEFT JOIN offices o_par ON p.office_id = o_par.id
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
  // Resolve office names for ICS/PAR; prefer form office, fallback to asset office
  $icsOfficeName = $row['ics_office_name'] ?: $row['asset_office_name'] ?: '';
  $parOfficeName = $row['par_office_name'] ?: $row['asset_office_name'] ?: '';

  // Apply {OFFICE} dynamic replacement using full office name
  $icsNo = $row['ics_no'];
  if ($icsNo !== null && $icsNo !== '') {
    $icsNo = preg_replace('/\{OFFICE\}|OFFICE/', $icsOfficeName, $icsNo);
  }

  $parNo = $row['par_no'];
  if ($parNo !== null && $parNo !== '') {
    $parNo = preg_replace('/\{OFFICE\}|OFFICE/', $parOfficeName, $parNo);
  }

  $rows[] = [
    'type' => 'asset',
    'id' => (int)$row['id'],
    'description' => $row['description'],
    'inventory_tag' => $row['inventory_tag'],
    'property_no' => $row['property_no'],
    'serial_no' => $row['serial_no'],
    'category_name' => $row['category_name'],
    'ics_no' => $icsNo,
    'par_no' => $parNo,
  ];
}
$stmt->close();

// Search employees (name, employee no, office)
$empSql = "
  SELECT 
    e.employee_id,
    e.employee_no,
    e.name,
    e.status,
    e.email,
    e.date_added,
    o.office_name,
    CASE 
      WHEN EXISTS (
        SELECT 1
        FROM mr_details m
        JOIN assets a2 ON a2.id = m.asset_id
        WHERE m.person_accountable = e.name
          AND (a2.status IS NULL OR LOWER(a2.status) <> 'unserviceable')
      ) THEN 'uncleared'
      ELSE 'cleared'
    END AS clearance_status
  FROM employees e
  LEFT JOIN offices o ON e.office_id = o.id
  WHERE 
    e.name LIKE ?
    OR e.employee_no LIKE ?
    OR o.office_name LIKE ?
  ORDER BY e.name ASC
  LIMIT 10
";

if ($empStmt = $conn->prepare($empSql)) {
  $empStmt->bind_param('sss', $like, $like, $like);
  $empStmt->execute();
  $empRes = $empStmt->get_result();
  while ($row = $empRes->fetch_assoc()) {
    $rows[] = [
      'type' => 'employee',
      'id' => (int)$row['employee_id'],
      'name' => $row['name'],
      'employee_no' => $row['employee_no'],
      'office_name' => $row['office_name'],
      'status' => $row['status'],
      'clearance_status' => $row['clearance_status'],
      'email' => $row['email'],
      'date_added' => $row['date_added'],
    ];
  }
  $empStmt->close();
}

echo json_encode(['results' => $rows]);
