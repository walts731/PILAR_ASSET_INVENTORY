<?php
require_once '../connect.php';

if (isset($_GET['employee_id'])) {
    $employee_id = intval($_GET['employee_id']);

    $stmt = $conn->prepare("SELECT asset_name, description, status, serial_no, property_no 
                            FROM assets 
                            WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['asset_name']) . "</td>
                    <td>" . htmlspecialchars($row['description']) . "</td>
                    <td>" . htmlspecialchars($row['status']) . "</td>
                    <td>" . htmlspecialchars($row['serial_no']) . "</td>
                    <td>" . htmlspecialchars($row['property_no']) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5' class='text-center text-muted'>No assets assigned.</td></tr>";
    }

    $stmt->close();
}
?>
