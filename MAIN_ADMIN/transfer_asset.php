<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = intval($_POST['asset_id']);
    $inventory_tag = $_POST['inventory_tag'];
    $new_employee_data = $_POST['new_employee'];

    // Extract employee_id from "id - name"
    $new_employee_id = intval(explode(" - ", $new_employee_data)[0]);

    // Fetch employee name + office_id from employees table
    $stmt0 = $conn->prepare("SELECT name, office_id FROM employees WHERE employee_id = ?");
    $stmt0->bind_param("i", $new_employee_id);
    $stmt0->execute();
    $stmt0->bind_result($employee_name, $new_office_id);
    $stmt0->fetch();
    $stmt0->close();

    if (!$employee_name) {
        // fallback error if employee not found
        header("Location: employees.php?error=Employee not found");
        exit;
    }

    // Update assets table with new employee_id and office_id
    $stmt1 = $conn->prepare("UPDATE assets SET employee_id = ?, office_id = ? WHERE id = ?");
    $stmt1->bind_param("iii", $new_employee_id, $new_office_id, $asset_id);
    $stmt1->execute();
    $stmt1->close();

    // Update mr_details with employee name using inventory_tag
    $stmt2 = $conn->prepare("UPDATE mr_details SET person_accountable = ? WHERE inventory_tag = ?");
    $stmt2->bind_param("ss", $employee_name, $inventory_tag);
    $stmt2->execute();
    $stmt2->close();

    header("Location: employees.php?success=Asset transferred");
    exit;
}
?>
