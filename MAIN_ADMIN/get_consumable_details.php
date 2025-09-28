<?php
require_once '../connect.php';

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);

  $sql = "
    SELECT 
      a.id,
      a.asset_name,
      a.description,
      a.quantity,
      a.unit,
      a.status,
      a.acquisition_date,
      a.last_updated,
      a.value,
      a.image,
      a.additional_images,
      a.type,
      a.property_no,
      a.added_stock,
      c.category_name,
      o.office_name,
      o.icon AS office_icon
    FROM assets a
    LEFT JOIN categories c ON a.category = c.id
    LEFT JOIN offices o ON a.office_id = o.id
    WHERE a.id = ? AND a.type = 'consumable'
  ";

  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($row = $result->fetch_assoc()) {
    // Calculate total value
    $row['total_value'] = $row['quantity'] * $row['value'];
    
    // Format dates
    if ($row['acquisition_date']) {
      $row['acquisition_date_formatted'] = date('M d, Y', strtotime($row['acquisition_date']));
    } else {
      $row['acquisition_date_formatted'] = 'N/A';
    }
    
    if ($row['last_updated']) {
      $row['last_updated_formatted'] = date('M d, Y g:i A', strtotime($row['last_updated']));
    } else {
      $row['last_updated_formatted'] = 'N/A';
    }
    
    // Handle additional images
    if ($row['additional_images']) {
      $row['additional_images_array'] = explode(',', $row['additional_images']);
    } else {
      $row['additional_images_array'] = [];
    }
    
    // Return direct data format (not wrapped)
    echo json_encode($row);
  } else {
    echo json_encode(['error' => 'Consumable not found']);
  }
  
  $stmt->close();
} else {
  echo json_encode(['error' => 'No ID provided']);
}

$conn->close();
?>
