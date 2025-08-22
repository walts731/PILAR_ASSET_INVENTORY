<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form_id = intval($_POST['form_id']);
    $office_id = intval($_POST['office_id']);
    $entity_name = trim($_POST['entity_name']);
    $fund_cluster = trim($_POST['fund_cluster']);
    $par_no = trim($_POST['par_no']);
    $position_office_left = trim($_POST['position_office_left']);
    $position_office_right = trim($_POST['position_office_right']);

    // Handle header image upload
    $header_image = null;
    if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . "../img/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_tmp = $_FILES['header_image']['tmp_name'];
        $file_name = time() . "_" . basename($_FILES['header_image']['name']);
        $file_path = $upload_dir . $file_name;

        if (move_uploaded_file($file_tmp, $file_path)) {
            $header_image = $file_name;
        }
    }

    // Check if record exists for this form_id
    $check_stmt = $conn->prepare("SELECT id, header_image FROM par_form WHERE form_id = ?");
    $check_stmt->bind_param("i", $form_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $existing = $check_result->fetch_assoc();
    $check_stmt->close();

    if ($existing) {
        // Update existing record
        if ($header_image) {
            // Replace old image if new uploaded
            if (!empty($existing['header_image']) && file_exists(__DIR__ . "../img/" . $existing['header_image'])) {
                unlink(__DIR__ . "../img/" . $existing['header_image']);
            }
            $stmt = $conn->prepare("UPDATE par_form 
                SET office_id=?, position_office_left=?, position_office_right=?, header_image=?, entity_name=?, fund_cluster=?, par_no=? 
                WHERE form_id=?");
            $stmt->bind_param("issssssi", $office_id, $position_office_left, $position_office_right, $header_image, $entity_name, $fund_cluster, $par_no, $form_id);
        } else {
            // Keep existing image
            $stmt = $conn->prepare("UPDATE par_form 
                SET office_id=?, position_office_left=?, position_office_right=?, entity_name=?, fund_cluster=?, par_no=? 
                WHERE form_id=?");
            $stmt->bind_param("isssssi", $office_id, $position_office_left, $position_office_right, $entity_name, $fund_cluster, $par_no, $form_id);
        }
        $stmt->execute();
        $stmt->close();
    } else {
        // Insert new record
        $stmt = $conn->prepare("INSERT INTO par_form (form_id, office_id, position_office_left, position_office_right, header_image, entity_name, fund_cluster, par_no) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssss", $form_id, $office_id, $position_office_left, $position_office_right, $header_image, $entity_name, $fund_cluster, $par_no);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: view_form.php?id=" . $form_id . "&success=1");
    exit();
} else {
    header("Location: index.php");
    exit();
}
