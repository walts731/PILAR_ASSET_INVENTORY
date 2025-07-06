<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

function extractDocxText($filePath) {
    $zip = new ZipArchive;
    if ($zip->open($filePath) === TRUE) {
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        preg_match_all('/<w:t[^>]*>(.*?)<\/w:t>/', $xml, $matches);
        return html_entity_decode(implode("", $matches[1]));
    }
    return '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['template_file'])) {
    $file = $_FILES['template_file'];
    $fileName = basename($file['name']);
    $fileTmpPath = $file['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['html', 'txt', 'docx'];

    if (!in_array($fileExtension, $allowedExtensions)) {
        die("Unsupported file type.");
    }

    switch ($fileExtension) {
        case 'txt':
        case 'html':
            $content = file_get_contents($fileTmpPath);
            break;
        case 'docx':
            $content = extractDocxText($fileTmpPath);
            break;
        default:
            die("Unsupported file type.");
    }

    $header_html = $subheader_html = $footer_html = '';

    if (preg_match('/<!-- HEADER_START -->(.*?)<!-- HEADER_END -->/is', $content, $matches)) {
        $header_html = trim($matches[1]);
    }
    if (preg_match('/<!-- SUBHEADER_START -->(.*?)<!-- SUBHEADER_END -->/is', $content, $matches)) {
        $subheader_html = trim($matches[1]);
    }
    if (preg_match('/<!-- FOOTER_START -->(.*?)<!-- FOOTER_END -->/is', $content, $matches)) {
        $footer_html = trim($matches[1]);
    }

    if (empty($header_html) && empty($subheader_html) && empty($footer_html)) {
        echo "<pre>" . htmlentities($content) . "</pre>";
        die("❌ Your template is missing required comment markers.");
    }

    $left_logo_path = $right_logo_path = '';
    $uploadDir = '../uploads/logos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (isset($_FILES['left_logo']) && $_FILES['left_logo']['error'] === UPLOAD_ERR_OK) {
        $leftLogoName = uniqid('left_') . '_' . basename($_FILES['left_logo']['name']);
        $leftLogoPath = $uploadDir . $leftLogoName;
        if (move_uploaded_file($_FILES['left_logo']['tmp_name'], $leftLogoPath)) {
            $left_logo_path = $leftLogoPath;
        }
    }

    if (isset($_FILES['right_logo']) && $_FILES['right_logo']['error'] === UPLOAD_ERR_OK) {
        $rightLogoName = uniqid('right_') . '_' . basename($_FILES['right_logo']['name']);
        $rightLogoPath = $uploadDir . $rightLogoName;
        if (move_uploaded_file($_FILES['right_logo']['tmp_name'], $rightLogoPath)) {
            $right_logo_path = $rightLogoPath;
        }
    }

    // Insert into database (with uploaded_at and uploaded_by)
    $stmt = $conn->prepare("INSERT INTO report_templates 
        (template_name, header_html, subheader_html, footer_html, left_logo_path, right_logo_path, created_by, updated_at, updated_by) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");

    $template_name = pathinfo($fileName, PATHINFO_FILENAME);
    $user_id = $_SESSION['user_id'];

    $stmt->bind_param("ssssssii", $template_name, $header_html, $subheader_html, $footer_html, $left_logo_path, $right_logo_path, $user_id, $user_id);

    if ($stmt->execute()) {
        header("Location: templates.php?upload=success");
        exit();
    } else {
        echo "Database error: " . $stmt->error;
    }
    $stmt->close();
} else {
    echo "No file uploaded.";
}
?>
