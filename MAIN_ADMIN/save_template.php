<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle file uploads
function handleUpload($inputName, $uploadDir = '../uploads/')
{
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

$template_name = $_POST['template_name'];
$header_content = $_POST['header_content'];
$subheader_content = $_POST['subheader_content'];
$footer_content = $_POST['footer_content'];

$left_logo_path = handleUpload('left_logo');
$right_logo_path = handleUpload('right_logo');

$stmt = $conn->prepare("
    INSERT INTO report_templates 
    (template_name, header_html, subheader_html, footer_html, left_logo_path, right_logo_path, created_by, updated_by, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
");

$stmt->bind_param(
    "ssssssii",
    $template_name,
    $header_content,
    $subheader_content,
    $footer_content,
    $left_logo_path,
    $right_logo_path,
    $user_id,
    $user_id
);

if ($stmt->execute()) {
    header("Location: templates.php?success=1");
    exit();
} else {
    echo "Error saving template: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
