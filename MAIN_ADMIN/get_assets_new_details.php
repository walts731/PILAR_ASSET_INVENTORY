<?php
require_once '../connect.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  echo json_encode(['error' => 'Invalid id']);
  exit;
}

// Fetch header/system logo
$system_logo = '';
if ($res = $conn->query("SELECT logo FROM system LIMIT 1")) {
  if ($row = $res->fetch_assoc()) { $system_logo = $row['logo'] ?? ''; }
}

// Fetch assets_new record with office name
$sql = "
  SELECT an.id, an.description, an.quantity, an.unit, an.unit_cost, an.office_id, an.date_created,
         o.office_name
  FROM assets_new an
  LEFT JOIN offices o ON o.id = an.office_id
  WHERE an.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$head = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$head) {
  echo json_encode(['error' => 'Record not found']);
  exit;
}

// Determine a representative category from linked assets (first by id)
$cat_name = 'Uncategorized';
$cat_type = '';
$stmtCat = $conn->prepare("SELECT c.category_name, c.type
                           FROM assets a
                           LEFT JOIN categories c ON a.category = c.id
                           WHERE a.asset_new_id = ?
                           ORDER BY a.id ASC
                           LIMIT 1");
$stmtCat->bind_param('i', $id);
$stmtCat->execute();
$resCat = $stmtCat->get_result();
if ($resCat && ($cr = $resCat->fetch_assoc())) {
  $cat_name = $cr['category_name'] ?? 'Uncategorized';
  $cat_type = $cr['type'] ?? '';
}
$stmtCat->close();

// Fetch item-level assets linked to this assets_new id
$items = [];
$sqlItems = "
  SELECT id AS item_id, property_no, inventory_tag, serial_no, status, acquisition_date AS date_acquired
  FROM assets
  WHERE asset_new_id = ?
  ORDER BY id ASC
";
$stmt2 = $conn->prepare($sqlItems);
$stmt2->bind_param('i', $id);
$stmt2->execute();
$res2 = $stmt2->get_result();
while ($r = $res2->fetch_assoc()) { $items[] = $r; }
$stmt2->close();

$out = [
  'system_logo'   => $system_logo,
  'office_name'   => $head['office_name'] ?? '',
  'category_name' => $cat_name,
  'category_type' => $cat_type,
  'type'          => 'asset',
  'status'        => '',
  'quantity'      => (int)$head['quantity'],
  'unit'          => $head['unit'],
  'description'   => $head['description'],
  'acquisition_date' => $head['date_created'],
  'last_updated'  => $head['date_created'],
  'value'         => (float)$head['unit_cost'],
  'serial_no'     => '',
  'code'          => '',
  'property_no'   => '',
  'model'         => '',
  'brand'         => '',
  'inventory_tag' => '',
  'image'         => '',
  'items'         => $items,
];

echo json_encode($out);
