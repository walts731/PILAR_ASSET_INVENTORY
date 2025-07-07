<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Helper: Upload image and return new relative path (e.g., "img/logo123.png")
function uploadLogo($file, $oldPath = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return $oldPath;
    }

    $uploadDir = '../img/';
    $filename = time() . '_' . basename($file['name']);
    $targetPath = $uploadDir . $filename;
    $relativePath = 'img/' . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Optional: Delete old logo file
        if ($oldPath && file_exists('../' . $oldPath)) {
            unlink('../' . $oldPath);
        }
        return $relativePath;
    }

    return $oldPath;
}

// Get form data
$template_id     = $_POST['template_id'];
$template_name   = $_POST['template_name'];
$header_html     = $_POST['header_html'];
$subheader_html  = $_POST['subheader_html'];
$footer_html     = $_POST['footer_html'];

// Fetch current logo paths
$stmt = $conn->prepare("SELECT left_logo_path, right_logo_path FROM report_templates WHERE id = ?");
$stmt->bind_param("i", $template_id);
$stmt->execute();
$result = $stmt->get_result();
$template = $result->fetch_assoc();
$stmt->close();

if (!$template) {
    die("Template not found.");
}

// Handle uploaded logos
$newLeftLogo  = uploadLogo($_FILES['left_logo'], $template['left_logo_path']);
$newRightLogo = uploadLogo($_FILES['right_logo'], $template['right_logo_path']);

// Update template
$updateStmt = $conn->prepare("UPDATE report_templates SET template_name = ?, header_html = ?, subheader_html = ?, footer_html = ?, left_logo_path = ?, right_logo_path = ?, updated_by = ?, updated_at = NOW() WHERE id = ?");
$updated_by = $_SESSION['user_id'];
$updateStmt->bind_param("ssssssii", $template_name, $header_html, $subheader_html, $footer_html, $newLeftLogo, $newRightLogo, $updated_by, $template_id);

if ($updateStmt->execute()) {
    $updateStmt->close();
    header("Location: templates.php?update=success");
    exit();
} else {
    echo "Failed to update template: " . $conn->error;
    $updateStmt->close();
}
?>
