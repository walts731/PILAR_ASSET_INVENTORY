<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$template_id = $_POST['template_id'] ?? null;
if (!$template_id) die("Invalid template ID.");

// Fetch existing logo paths
$stmt = $conn->prepare("SELECT left_logo_path, right_logo_path FROM report_templates WHERE id = ?");
$stmt->bind_param("i", $template_id);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$stmt->close();

$existing_left_logo_path = $existing['left_logo_path'];
$existing_right_logo_path = $existing['right_logo_path'];

$template_name = $_POST['template_name'] ?? '';
$header_html = $_POST['header_html'] ?? '';
$subheader_html = $_POST['subheader_html'] ?? '';
$footer_html = $_POST['footer_html'] ?? '';

$remove_left_logo = $_POST['remove_left_logo'] ?? '0';
$remove_right_logo = $_POST['remove_right_logo'] ?? '0';

$left_logo_path = $existing_left_logo_path;
$right_logo_path = $existing_right_logo_path;

// Remove left logo if flagged
if ($remove_left_logo === '1') {
    if (!empty($existing_left_logo_path) && file_exists('../' . $existing_left_logo_path)) {
        unlink('../' . $existing_left_logo_path);
    }
    $left_logo_path = '';
}

// Remove right logo if flagged
if ($remove_right_logo === '1') {
    if (!empty($existing_right_logo_path) && file_exists('../' . $existing_right_logo_path)) {
        unlink('../' . $existing_right_logo_path);
    }
    $right_logo_path = '';
}

// Upload new left logo if provided
if (isset($_FILES['left_logo']) && $_FILES['left_logo']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['left_logo']['tmp_name'];
    $filename = '../uploads/' . time() . '_' . basename($_FILES['left_logo']['name']);
    move_uploaded_file($tmp_name, '../' . $filename);
    $left_logo_path = $filename;

    if (!empty($existing_left_logo_path) && file_exists('../' . $existing_left_logo_path)) {
        unlink('../' . $existing_left_logo_path);
    }
}

// Upload new right logo if provided
if (isset($_FILES['right_logo']) && $_FILES['right_logo']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['right_logo']['tmp_name'];
    $filename = '../uploads/' . time() . '_' . basename($_FILES['right_logo']['name']);
    move_uploaded_file($tmp_name, '../' . $filename);
    $right_logo_path = $filename;

    if (!empty($existing_right_logo_path) && file_exists('../' . $existing_right_logo_path)) {
        unlink('../' . $existing_right_logo_path);
    }
}

// Final Update
$stmt = $conn->prepare("UPDATE report_templates SET 
    template_name = ?, 
    header_html = ?, 
    subheader_html = ?, 
    footer_html = ?, 
    left_logo_path = ?, 
    right_logo_path = ? 
    WHERE id = ?");

$stmt->bind_param(
    "ssssssi",
    $template_name,
    $header_html,
    $subheader_html,
    $footer_html,
    $left_logo_path,
    $right_logo_path,
    $template_id
);

if ($stmt->execute()) {
    header("Location: templates.php?update=success");
} else {
    echo "Error updating template: " . $stmt->error;
}

$stmt->close();
$conn->close();
