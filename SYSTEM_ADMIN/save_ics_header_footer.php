<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Get form_id from the hidden input for redirect only
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 1;

    $entity_name = trim($_POST['entity_name']);
    $fund_cluster = trim($_POST['fund_cluster']);
    $ics_no = trim($_POST['ics_no']);
    $received_from_name = trim($_POST['received_from_name']);
    $received_from_position = trim($_POST['received_from_position']);
    $received_by_name = trim($_POST['received_by_name']);
    $received_by_position = trim($_POST['received_by_position']);

    // Handle image upload
    $header_image = '';
    if (!empty($_FILES['header_image']['name'])) {
        $target_dir = "../img/";
        $header_image = time() . '_' . basename($_FILES["header_image"]["name"]);
        $target_file = $target_dir . $header_image;

        if (!move_uploaded_file($_FILES["header_image"]["tmp_name"], $target_file)) {
            die("Failed to upload image.");
        }
    }

    // ✅ Always update id=1 in ics_form
    if (!empty($header_image)) {
        $stmt = $conn->prepare("UPDATE ics_form 
            SET header_image = ?, entity_name = ?, fund_cluster = ?, ics_no = ?, 
                received_from_name = ?, received_from_position = ?, 
                received_by_name = ?, received_by_position = ?, created_at = NOW() 
            WHERE id = 1");
        $stmt->bind_param(
            "ssssssss",
            $header_image,
            $entity_name,
            $fund_cluster,
            $ics_no,
            $received_from_name,
            $received_from_position,
            $received_by_name,
            $received_by_position
        );
    } else {
        $stmt = $conn->prepare("UPDATE ics_form 
            SET entity_name = ?, fund_cluster = ?, ics_no = ?, 
                received_from_name = ?, received_from_position = ?, 
                received_by_name = ?, received_by_position = ?, created_at = NOW() 
            WHERE id = 1");
        $stmt->bind_param(
            "sssssss",
            $entity_name,
            $fund_cluster,
            $ics_no,
            $received_from_name,
            $received_from_position,
            $received_by_name,
            $received_by_position
        );
    }

    if ($stmt->execute()) {
        // ✅ Redirect using the original form_id (not forced to 1)
        header("Location: view_form.php?id=" . $form_id . "&success=1");
        exit;
    } else {
        die("Failed to update ICS data: " . $stmt->error);
    }
} else {
    header("Location: ics_form.php");
    exit;
}
