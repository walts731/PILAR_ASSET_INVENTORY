<?php
require_once '../connect.php';
require_once '../vendor/autoload.php';
session_start();

use PhpOffice\PhpWord\IOFactory;

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Extract DOCX content as formatted HTML and extract up to 2 images as logos
function extractDocxText($filePath, &$left_logo_path, &$right_logo_path)
{
    try {
        $phpWord = IOFactory::load($filePath, 'Word2007');
        $writer = IOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $writer->save('php://output');
        $html = ob_get_clean();

        $html = preg_replace('/<!DOCTYPE.+?>/is', '', $html);
        $html = preg_replace('/<html>|<\/html>|<body>|<\/body>/i', '', $html);

        // Handle embedded images
        $uploadDir = '../uploads/logos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        preg_match_all('/<img[^>]+src="([^"]+)"[^>]*>/i', $html, $matches);
        $imageSources = $matches[1] ?? [];
        $storedImagePaths = [];

        foreach ($imageSources as $index => $src) {
            if (strpos($src, 'data:image/') === 0) {
                preg_match('/data:image\/(\w+);base64,/', $src, $typeMatch);
                $imgType = $typeMatch[1];
                $imgData = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $src));
                $imgName = uniqid('logo_') . '.' . $imgType;
                $imgPath = $uploadDir . $imgName;

                file_put_contents($imgPath, $imgData);
                $storedImagePaths[] = $imgPath;

                // Remove image data from HTML
                $html = str_replace($src, '', $html);
            }
        }

        $left_logo_path = $storedImagePaths[0] ?? '';
        $right_logo_path = $storedImagePaths[1] ?? '';

        return trim($html);
    } catch (Exception $e) {
        return '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['template_file'])) {
    $file = $_FILES['template_file'];
    $fileName = basename($file['name']);
    $fileTmpPath = $file['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = ['html', 'txt', 'docx'];

    if (!in_array($fileExtension, $allowedExtensions)) {
        header("Location: templates.php?error=" . urlencode("Unsupported file type."));
        exit();
    }

    $left_logo_path = $right_logo_path = '';

    switch ($fileExtension) {
        case 'txt':
        case 'html':
            $content = file_get_contents($fileTmpPath);
            break;
        case 'docx':
            $content = extractDocxText($fileTmpPath, $left_logo_path, $right_logo_path);
            break;
        default:
            header("Location: templates.php?error=" . urlencode("Unsupported file type."));
            exit();
    }

    $header_html = $subheader_html = $footer_html = '';

    if (preg_match('/&lt;!-- HEADER_START --&gt;(.*?)&lt;!-- HEADER_END --&gt;/is', $content, $matches)) {
        $header_html = html_entity_decode(trim($matches[1]));
        $header_html = preg_replace('/<img[^>]*>/i', '', $header_html);
        $header_html = preg_replace('/margin-left\s*:\s*1in\s*;?/i', 'margin-left: 0in;', $header_html);
    }

    if (preg_match('/&lt;!-- SUBHEADER_START --&gt;(.*?)&lt;!-- SUBHEADER_END --&gt;/is', $content, $matches)) {
        $subheader_html = html_entity_decode(trim($matches[1]));
    }

    if (preg_match('/&lt;!-- FOOTER_START --&gt;(.*?)&lt;!-- FOOTER_END --&gt;/is', $content, $matches)) {
        $footer_html = html_entity_decode(trim($matches[1]));
    }

    if (empty($header_html) && empty($subheader_html) && empty($footer_html)) {
        header("Location: templates.php?error=" . urlencode("Your template is missing required encoded comment markers."));
        exit();
    }

    // Manual logo override
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
        header("Location: templates.php?error=" . urlencode("Database error: " . $stmt->error));
        exit();
    }

    $stmt->close();
} else {
    header("Location: templates.php?error=" . urlencode("No file uploaded."));
    exit();
}
