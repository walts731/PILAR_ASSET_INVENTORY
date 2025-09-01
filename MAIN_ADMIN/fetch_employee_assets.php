<?php
require_once '../connect.php';

if (isset($_GET['employee_id'])) {
  $employee_id = intval($_GET['employee_id']);

  // include inventory_tag for transfer
  $stmt = $conn->prepare("
    SELECT id, asset_name, description, status, serial_no, property_no, inventory_tag
    FROM assets
    WHERE employee_id = ?
  ");
  $stmt->bind_param("i", $employee_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo "<tr>
              <td>" . htmlspecialchars($row['description']) . "</td>
              <td>" . htmlspecialchars($row['status']) . "</td>
              <td>" . htmlspecialchars($row['serial_no']) . "</td>
              <td>" . htmlspecialchars($row['property_no']) . "</td>
              <td>
                <button class='btn btn-sm btn-primary view-asset-details' data-id='". (int)$row['id'] ."'>
                  <i class='bi bi-eye'></i> View
                </button>

                <button class='btn btn-sm btn-info transfer-asset'
                  data-asset-id='". (int)$row['id'] ."'
                  data-inventory-tag='". htmlspecialchars($row['inventory_tag']) ."'
                  data-current-employee-id='". (int)$employee_id ."'>
                  <i class='bi bi-arrow-left-right'></i> Transfer
                </button>
              </td>
            </tr>";
    }
  } else {
    echo "<tr><td colspan='6' class='text-center text-muted'>No assets assigned.</td></tr>";
  }

  $stmt->close();
}
