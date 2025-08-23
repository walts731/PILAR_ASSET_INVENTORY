<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form_id from the hidden input for redirect only
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 1;

    $accountable_officer = trim($_POST['accountable_officer']);
    $destination = trim($_POST['destination']);
    $agency_office = trim($_POST['agency_office']);
    $member_inventory = trim($_POST['member_inventory']);
    $chairman_inventory = trim($_POST['chairman_inventory']);
    $mayor = trim($_POST['mayor']);

    // Handle header image upload
    $header_image = '';
    if (!empty($_FILES['header_image']['name'])) {
        $target_dir = "../img/";
        $header_image = time() . '_' . basename($_FILES["header_image"]["name"]);
        $target_file = $target_dir . $header_image;

        if (!move_uploaded_file($_FILES["header_image"]["tmp_name"], $target_file)) {
            die("Failed to upload header image.");
        }
    }

    // Check if there's at least one row in rpcppe_form
    $result = $conn->query("SELECT id FROM rpcppe_form ORDER BY id ASC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $record_id = $row['id'];

        // Update the first record
        if (!empty($header_image)) {
            $stmt = $conn->prepare("UPDATE rpcppe_form 
                SET accountable_officer = ?, destination = ?, agency_office = ?, 
                    member_inventory = ?, chairman_inventory = ?, mayor = ?, header_image = ? 
                WHERE id = ?");
            $stmt->bind_param(
                "sssssssi",
                $accountable_officer,
                $destination,
                $agency_office,
                $member_inventory,
                $chairman_inventory,
                $mayor,
                $header_image,
                $record_id
            );
        } else {
            $stmt = $conn->prepare("UPDATE rpcppe_form 
                SET accountable_officer = ?, destination = ?, agency_office = ?, 
                    member_inventory = ?, chairman_inventory = ?, mayor = ? 
                WHERE id = ?");
            $stmt->bind_param(
                "ssssssi",
                $accountable_officer,
                $destination,
                $agency_office,
                $member_inventory,
                $chairman_inventory,
                $mayor,
                $record_id
            );
        }
        $stmt->execute();
        $stmt->close();

    } else {
        // Insert new record if table is empty
        $stmt = $conn->prepare("INSERT INTO rpcppe_form 
            (accountable_officer, destination, agency_office, member_inventory, chairman_inventory, mayor, header_image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "sssssss",
            $accountable_officer,
            $destination,
            $agency_office,
            $member_inventory,
            $chairman_inventory,
            $mayor,
            $header_image
        );
        $stmt->execute();
        $stmt->close();
    }

    // Redirect using the original form_id
    header("Location: view_form.php?id=" . $form_id . "&success=1");
    exit;

} else {
    header("Location: manage_forms.php");
    exit;
}
?>
