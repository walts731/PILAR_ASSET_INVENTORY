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
    $header_image = null; // NULL by default
    if (!empty($_FILES['header_image']['name'])) {
        $target_dir = "../img/";
        $header_image = time() . '_' . basename($_FILES["header_image"]["name"]);
        $target_file = $target_dir . $header_image;

        if (!move_uploaded_file($_FILES["header_image"]["tmp_name"], $target_file)) {
            die("Failed to upload header image.");
        }
    }

    // Check if there's at least one row in iirup_form (latest row)
    $result = $conn->query("SELECT id, header_image FROM iirup_form ORDER BY id DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $record_id = $row['id'];

        // If no new image uploaded → keep old one
        if ($header_image === null) {
            $header_image = $row['header_image'];
        }

        // Update latest record
        $stmt = $conn->prepare("UPDATE iirup_form 
            SET accountable_officer = ?, designation = ?, office = ?, header_image = ?, 
                footer_accountable_officer = ?, footer_authorized_official = ?, 
                footer_designation_officer = ?, footer_designation_official = ? 
            WHERE id = ?");
        $stmt->bind_param(
            "ssssssssi",
            $accountable_officer,
            $designation,
            $office,
            $header_image,
            $footer_accountable_officer,
            $footer_authorized_official,
            $footer_designation_officer,
            $footer_designation_official,
            $record_id
        );
        $stmt->execute();
        $stmt->close();

    } else {
        // Insert new record if table is empty
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
            $header_image,   // NULL or uploaded filename
            $footer_accountable_officer,
            $footer_authorized_official,
            $footer_designation_officer,
            $footer_designation_official
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
