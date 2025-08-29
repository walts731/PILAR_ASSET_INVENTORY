<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Get form_id from the hidden input for redirect only
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 1;

    // Collect form data
    $accountable_officer         = trim($_POST['accountable_officer']);
    $designation                 = trim($_POST['designation']);
    $office                      = trim($_POST['office']);
    $footer_accountable_officer  = trim($_POST['footer_accountable_officer']);
    $footer_authorized_official  = trim($_POST['footer_authorized_official']);
    $footer_designation_officer  = trim($_POST['footer_designation_officer']);
    $footer_designation_official = trim($_POST['footer_designation_official']);

    // Handle header image upload
    $header_image = null; // Default to NULL
    if (!empty($_FILES['header_image']['name'])) {
        $target_dir = "../img/";
        $header_image = time() . '_' . basename($_FILES["header_image"]["name"]);
        $target_file = $target_dir . $header_image;

        if (!move_uploaded_file($_FILES["header_image"]["tmp_name"], $target_file)) {
            die("Failed to upload header image.");
        }
    }

    // Always insert a new record
    $stmt = $conn->prepare("INSERT INTO iirup_form 
        (accountable_officer, designation, office, header_image, 
         footer_accountable_officer, footer_authorized_official, 
         footer_designation_officer, footer_designation_official) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        "ssssssss",
        $accountable_officer,
        $designation,
        $office,
        $header_image,   // filename OR NULL
        $footer_accountable_officer,
        $footer_authorized_official,
        $footer_designation_officer,
        $footer_designation_official
    );
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // ✅ Successfully inserted
        header("Location: view_form.php?id=" . $form_id . "&success=1");
    } else {
        die("Insert failed: " . $stmt->error);
    }

    $stmt->close();

} else {
    header("Location: manage_forms.php");
    exit;
}
?>
