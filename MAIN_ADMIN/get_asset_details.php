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
      a.serial_no,       -- NEW optional field
      a.code,            -- NEW optional field
      a.property_no,     -- NEW optional field
      a.model,           -- NEW optional field
      a.brand,           -- NEW optional field
      c.category_name,
      c.type AS category_type,
      o.office_name,
      o.icon AS office_icon,
      s.logo AS system_logo
    FROM assets a
    LEFT JOIN categories c ON a.category = c.id
    LEFT JOIN offices o ON a.office_id = o.id
    LEFT JOIN system s ON s.id = 1
    WHERE a.id = ?
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
  } else {
    echo json_encode(['error' => 'Asset not found']);
  }
}
?>
