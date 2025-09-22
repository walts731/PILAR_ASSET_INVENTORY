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
      a.additional_images,
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
    // Return the asset itself as the only item entry so the modal lists assets instead of asset_items
    $row['items'] = [[
      'item_id' => $row['id'],
      'asset_id' => $row['id'],
      'qr_code' => $row['qr_code'],
      'inventory_tag' => $row['inventory_tag'],
      'serial_no' => $row['serial_no'],
      'property_no' => $row['property_no'],
      'status' => $row['status'],
      'date_acquired' => $row['acquisition_date']
    ]];
    echo json_encode($row);
  } else {
    echo json_encode(['error' => 'Asset not found']);
  }
}
?>
