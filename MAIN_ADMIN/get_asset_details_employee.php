<?php
require_once '../connect.php';
header('Content-Type: application/json');

$assetId = isset($_GET['asset_id']) ? (int)$_GET['asset_id'] : 0;
$employeeId = isset($_GET['employee_id']) ? (int)$_GET['employee_id'] : 0;

if ($assetId <= 0 || $employeeId <= 0) {
  echo json_encode(['error' => 'Invalid parameters.']);
  exit;
}

$sql = "
  SELECT 
    a.id,
    a.asset_name,
    a.description,
    a.quantity,
    a.unit,
    a.status,
    a.acquisition_date,
    a.red_tagged,
    a.last_updated,
    a.value,
    a.qr_code,
    a.image,
    a.additional_images,
    a.type,
    a.serial_no,
    a.code,
    a.property_no,
    a.model,
    a.brand,
    a.inventory_tag,
    c.category_name,
    c.type AS category_type,
    o.office_name,
    o.icon AS office_icon,
    s.logo AS system_logo,
    e.name AS employee_name
  FROM assets a
  LEFT JOIN categories c ON a.category = c.id
  LEFT JOIN offices o ON a.office_id = o.id
  LEFT JOIN system s ON s.id = 1
  LEFT JOIN employees e ON a.employee_id = e.employee_id
  WHERE a.id = ? AND a.employee_id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $assetId, $employeeId);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
  // Return direct object to match employees.php modal expectations
  echo json_encode($row);
} else {
  echo json_encode(['error' => 'Asset not found for this employee.']);
}

$stmt->close();
