<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get ICS main info
    $ics_id = intval($_POST['ics_id'] ?? 0);   // from hidden input (ics_form.id)
    $form_id = intval($_POST['form_id'] ?? 0); // from hidden input (URL-based ref)
    $entity_name = $_POST['entity_name'] ?? '';
    $fund_cluster = $_POST['fund_cluster'] ?? '';
    $ics_no = $_POST['ics_no'] ?? '';
    $received_from_name = $_POST['received_from_name'] ?? '';
    $received_from_position = $_POST['received_from_position'] ?? '';
    $received_by_name = $_POST['received_by_name'] ?? '';
    $received_by_position = $_POST['received_by_position'] ?? '';
    $updated_at = date('Y-m-d H:i:s');

    if ($ics_id > 0) {
        // Update existing ICS form
        $stmt = $conn->prepare("UPDATE ics_form SET 
            entity_name = ?, 
            fund_cluster = ?, 
            ics_no = ?, 
            received_from_name = ?, 
            received_from_position = ?, 
            received_by_name = ?, 
            received_by_position = ?, 
            created_at = ? 
            WHERE id = ?");
        $stmt->bind_param("ssssssssi", $entity_name, $fund_cluster, $ics_no, $received_from_name, $received_from_position, $received_by_name, $received_by_position, $updated_at, $ics_id);
        $stmt->execute();
        $stmt->close();

        $id = $ics_id; // IMPORTANT: keep using the existing ICS ID
    } else {
        // Insert new ICS form
        $stmt = $conn->prepare("INSERT INTO ics_form 
            (entity_name, fund_cluster, ics_no, received_from_name, received_from_position, received_by_name, received_by_position, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $entity_name, $fund_cluster, $ics_no, $received_from_name, $received_from_position, $received_by_name, $received_by_position, $updated_at);
        $stmt->execute();
        $id = $conn->insert_id; // get new ICS id for items
        $stmt->close();
    }

    // Get ICS items data
    $quantities = $_POST['quantity'] ?? [];
    $units = $_POST['unit'] ?? [];
    $unit_costs = $_POST['unit_cost'] ?? [];
    $total_costs = $_POST['total_cost'] ?? [];
    $descriptions = $_POST['description'] ?? [];
    $item_nos = $_POST['item_no'] ?? [];
    $estimated_lives = $_POST['estimated_useful_life'] ?? [];

    // Prepare statements
    $stmt_items = $conn->prepare("INSERT INTO ics_items 
        (ics_id, quantity, unit, unit_cost, total_cost, description, item_no, estimated_useful_life, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_update_assets = $conn->prepare("UPDATE assets SET quantity = quantity - ? WHERE description = ?");

    for ($i = 0; $i < count($descriptions); $i++) {
        $quantity = isset($quantities[$i]) ? floatval($quantities[$i]) : 0;
        $unit = $units[$i] ?? '';
        $unit_cost = isset($unit_costs[$i]) ? floatval($unit_costs[$i]) : 0;
        $total_cost = isset($total_costs[$i]) ? floatval($total_costs[$i]) : 0;
        $description = $descriptions[$i] ?? '';
        $item_no = $item_nos[$i] ?? '';
        $estimated_life = $estimated_lives[$i] ?? '';

        if (empty($description) || $quantity <= 0) continue; // skip empty rows

        // Insert into ICS items
        $stmt_items->bind_param("idddsssss", $id, $quantity, $unit, $unit_cost, $total_cost, $description, $item_no, $estimated_life, $updated_at);
        $stmt_items->execute();

        // Subtract quantity from assets
        $stmt_update_assets->bind_param("ds", $quantity, $description);
        $stmt_update_assets->execute();
    }

    $stmt_items->close();
    $stmt_update_assets->close();

    // Redirect to forms.php with the ICS form id
    header("Location: forms.php?id=" . $form_id);
    exit();
}
?>
