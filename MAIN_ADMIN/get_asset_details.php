<?php
require_once '../connect.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];

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
      a.type,
      a.serial_no,       -- optional field
      a.code,            -- optional field
      a.property_no,     -- optional field
      a.model,           -- optional field
      a.brand,           -- optional field
      a.inventory_tag,   -- optional field
      c.category_name,
      c.type AS category_type,
      o.office_name,
      o.icon AS office_icon,
      s.logo AS system_logo,
      e.name AS employee_name   -- fetch employee name instead of ID
    FROM assets a
    LEFT JOIN categories c ON a.category = c.id
    LEFT JOIN offices o ON a.office_id = o.id
    LEFT JOIN system s ON s.id = 1
    LEFT JOIN employees e ON a.employee_id = e.employee_id
    WHERE a.id = ?
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    // Fetch item-level rows for this asset
    $items = [];
    $sqlItems = "SELECT item_id, asset_id, office_id, qr_code, inventory_tag, serial_no, status, date_acquired, created_at, updated_at FROM asset_items WHERE asset_id = ? ORDER BY item_id ASC";
    if ($stmt2 = $conn->prepare($sqlItems)) {
      $stmt2->bind_param("i", $id);
      $stmt2->execute();
      $res2 = $stmt2->get_result();
      while ($it = $res2->fetch_assoc()) { $items[] = $it; }
      $stmt2->close();
    }
    $row['items'] = $items;
    echo json_encode($row);
  } else {
    echo json_encode(['error' => 'Asset not found']);
  }
}
?>
