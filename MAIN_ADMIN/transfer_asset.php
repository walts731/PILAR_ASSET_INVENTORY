<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $asset_id = intval($_POST['asset_id']);
    $inventory_tag = $_POST['inventory_tag'];
    $new_employee_data = $_POST['new_employee'];

    // extract employee_id from "id - name"
    $new_employee_id = intval(explode(" - ", $new_employee_data)[0]);

    // fetch employee name from employees table
    $stmt0 = $conn->prepare("SELECT name FROM employees WHERE id = ?");
    $stmt0->bind_param("i", $new_employee_id);
    $stmt0->execute();
    $stmt0->bind_result($employee_name);
    $stmt0->fetch();
    $stmt0->close();

    // update assets table with employee_id
    $stmt1 = $conn->prepare("UPDATE assets SET employee_id = ? WHERE id = ?");
    $stmt1->bind_param("ii", $new_employee_id, $asset_id);
    $stmt1->execute();
    $stmt1->close();

    // update mr_details with employee name using inventory_tag
    $stmt2 = $conn->prepare("UPDATE mr_details SET person_accountable = ? WHERE inventory_tag = ?");
    $stmt2->bind_param("ss", $employee_name, $inventory_tag);
    $stmt2->execute();
    $stmt2->close();

    header("Location: employees.php?success=Asset transferred");
    exit;
}
?>
