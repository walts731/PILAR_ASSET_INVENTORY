<?php
session_start();
require_once "../connect.php";

header('Content-Type: application/json');

// Check if logged in and is super_admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

// Collect POST data
$document_type   = $_POST['document_type'] ?? null;
$title           = $_POST['title'] ?? null;
$version         = $_POST['version'] ?? null;
$effective_date  = $_POST['effective_date'] ?? null;
$content         = $_POST['content'] ?? null;
$updated_by      = $_SESSION['user_id']; // ✅ get the logged-in user

if (!$document_type || !$title || !$version || !$effective_date || !$content) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit();
}

$conn->begin_transaction();
try {
    // Deactivate previous versions
    $stmt = $conn->prepare("UPDATE legal_documents SET is_active = 0 WHERE document_type = ?");
    $stmt->bind_param("s", $document_type);
    $stmt->execute();
    $stmt->close();

    // Insert new version
    $stmt = $conn->prepare("INSERT INTO legal_documents 
        (document_type, title, version, effective_date, content, is_active, created_at, last_updated, updated_by) 
        VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW(), ?)");
    $stmt->bind_param("sssssi", $document_type, $title, $version, $effective_date, $content, $updated_by);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Document saved successfully']);
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
