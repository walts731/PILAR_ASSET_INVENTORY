<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $entity_name = trim($_POST['entity_name']);
    $fund_cluster = trim($_POST['fund_cluster']);
    $ics_no = trim($_POST['ics_no']);
    $received_from_name = trim($_POST['received_from_name']);
    $received_from_position = trim($_POST['received_from_position']);
    $received_by_name = trim($_POST['received_by_name']);
    $received_by_position = trim($_POST['received_by_position']);

    // Handle image upload
    $header_image = ''; // default empty
    if (!empty($_FILES['header_image']['name'])) {
        $target_dir = "../img/";
        $header_image = time() . '_' . basename($_FILES["header_image"]["name"]);
        $target_file = $target_dir . $header_image;

        if (!move_uploaded_file($_FILES["header_image"]["tmp_name"], $target_file)) {
            die("Failed to upload image.");
        }
    }

    // Insert ICS record
    $stmt = $conn->prepare("INSERT INTO ics_form 
        (header_image, entity_name, fund_cluster, ics_no, received_from_name, 
        received_from_position, received_by_name, received_by_position, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");

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

    if ($stmt->execute()) {
        // Redirect back to the form page after saving
        header("Location: ics_form.php?success=1");
        exit;
    } else {
        die("Failed to save ICS data: " . $stmt->error);
    }
} else {
    header("Location: ics_form.php"); // prevent direct access
    exit;
}
