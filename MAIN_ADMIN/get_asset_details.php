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
    a.serial_no,
    a.code,
    a.property_no,
    a.model,
    a.brand,
    a.inventory_tag,
    a.end_user,                 -- add this if it's a text/varchar column
    a.ics_id,                   -- ICS form ID
    a.par_id,                   -- PAR form ID
    c.category_name,
    c.type AS category_type,
    o.office_name,
    o.icon AS office_icon,
    s.logo AS system_logo,
    e.name AS employee_name,    -- person accountable
    f.ics_no AS ics_no,         -- join ICS
    p.par_no AS par_no          -- join PAR
  FROM assets a
  LEFT JOIN categories c ON a.category = c.id
  LEFT JOIN offices o ON a.office_id = o.id
  LEFT JOIN system s ON s.id = 1
  LEFT JOIN employees e ON a.employee_id = e.employee_id
  LEFT JOIN ics_form f ON a.ics_id = f.id
  LEFT JOIN par_form p ON a.par_id = p.id
  WHERE a.id = ?
";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    // Return the asset data in the expected format
    echo json_encode([
      'success' => true,
      'asset' => $row
    ]);
  } else {
    echo json_encode([
      'success' => false,
      'message' => 'Asset not found'
    ]);
  }
}
?>
