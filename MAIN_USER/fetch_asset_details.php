<?php
require_once '../connect.php';

if (isset($_GET['asset_id'])) {
    $asset_id = intval($_GET['asset_id']);

    $stmt = $conn->prepare("SELECT id, asset_name, category, description, quantity, unit, status, acquisition_date, 
                                   office_id, employee_id, red_tagged, last_updated, value, qr_code, type, image, 
                                   serial_no, code, property_no 
                            FROM assets 
                            WHERE id = ?");
    $stmt->bind_param("i", $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        foreach ($row as $key => $value) {
            echo "<tr>
                    <th style='width:30%; text-transform:capitalize'>" . htmlspecialchars(str_replace("_"," ",$key)) . "</th>
                    <td>" . (!empty($value) ? htmlspecialchars($value) : "<span class='text-muted'>N/A</span>") . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='2' class='text-center text-danger'>Asset not found.</td></tr>";
    }

    $stmt->close();
}
?>
