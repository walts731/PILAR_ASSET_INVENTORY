<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Validate template ID
$template_id = $_POST['template_id'] ?? null;
if (!$template_id) die("Invalid template ID.");

// Handle file uploads
function handleUpload($inputName, $uploadDir = '../uploads/') {
    if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
        $filename = basename($_FILES[$inputName]['name']);
        $targetPath = $uploadDir . uniqid() . '_' . $filename;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetPath)) {
            return $targetPath;
        }
    }
    return null;
}

// Fetch existing logo paths
$stmt = $conn->prepare("SELECT left_logo_path, right_logo_path FROM report_templates WHERE id = ?");
$stmt->bind_param("i", $template_id);
$stmt->execute();
$result = $stmt->get_result();
$existing = $result->fetch_assoc();
$stmt->close();

$existing_left_logo_path = $existing['left_logo_path'];
$existing_right_logo_path = $existing['right_logo_path'];

// Form inputs
$template_name = $_POST['template_name'] ?? '';
$header_html = $_POST['header_html'] ?? '';
$subheader_html = $_POST['subheader_html'] ?? '';
$footer_html = $_POST['footer_html'] ?? '';
$remove_left_logo = $_POST['remove_left_logo'] ?? '0';
$remove_right_logo = $_POST['remove_right_logo'] ?? '0';

$left_logo_path = $existing_left_logo_path;
$right_logo_path = $existing_right_logo_path;

// Remove left logo
if ($remove_left_logo === '1') {
    if (!empty($existing_left_logo_path) && file_exists('../' . $existing_left_logo_path)) {
        unlink('../' . $existing_left_logo_path);
    }
    $left_logo_path = '';
}

// Remove right logo
if ($remove_right_logo === '1') {
    if (!empty($existing_right_logo_path) && file_exists('../' . $existing_right_logo_path)) {
        unlink('../' . $existing_right_logo_path);
    }
    $right_logo_path = '';
}

// Upload new logos if provided
$new_left_logo = handleUpload('left_logo');
if ($new_left_logo) {
    if (!empty($existing_left_logo_path) && file_exists('../' . $existing_left_logo_path)) {
        unlink('../' . $existing_left_logo_path);
    }
    $left_logo_path = $new_left_logo;
}

$new_right_logo = handleUpload('right_logo');
if ($new_right_logo) {
    if (!empty($existing_right_logo_path) && file_exists('../' . $existing_right_logo_path)) {
        unlink('../' . $existing_right_logo_path);
    }
    $right_logo_path = $new_right_logo;
}

// Update the database
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
?>
