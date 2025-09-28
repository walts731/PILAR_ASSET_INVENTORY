<?php
session_start();
require_once "../connect.php";

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in and is super admin
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit();
}

try {
    // Get form data
    $document_type = $_POST['document_type'] ?? '';
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $version = $_POST['version'] ?? '1.0';
    $effective_date = $_POST['effective_date'] ?? date('Y-m-d');
    $current_version = $_POST['current_version'] ?? '1.0';
    $user_id = $_SESSION['user_id'];
    
    // Validate required fields
    if (empty($document_type) || empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    // Validate document type
    if (!in_array($document_type, ['privacy_policy', 'terms_of_service'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid document type']);
        exit();
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    // Check if there's an existing active document
    $check_stmt = $conn->prepare("SELECT id, version, content FROM legal_documents WHERE document_type = ? AND is_active = 1");
    $check_stmt->bind_param("s", $document_type);
    $check_stmt->execute();
    $existing = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();
    
    if ($existing) {
        // Archive the existing document by setting is_active to 0
        $archive_stmt = $conn->prepare("UPDATE legal_documents SET is_active = 0 WHERE id = ?");
        $archive_stmt->bind_param("i", $existing['id']);
        $archive_stmt->execute();
        $archive_stmt->close();
        
        // Create history record
        $history_stmt = $conn->prepare("
            INSERT INTO legal_document_history 
            (document_id, document_type, title, content, version, effective_date, last_updated, updated_by, created_at) 
            SELECT id, document_type, title, content, version, effective_date, last_updated, updated_by, NOW() 
            FROM legal_documents WHERE id = ?
        ");
        $history_stmt->bind_param("i", $existing['id']);
        $history_stmt->execute();
        $history_stmt->close();
    }
    
    // Insert new document
    $insert_stmt = $conn->prepare("
        INSERT INTO legal_documents 
        (document_type, title, content, version, effective_date, last_updated, updated_by, is_active, created_at) 
        VALUES (?, ?, ?, ?, ?, NOW(), ?, 1, NOW())
    ");
    $insert_stmt->bind_param("sssssi", $document_type, $title, $content, $version, $effective_date, $user_id);
    
    if ($insert_stmt->execute()) {
        $new_document_id = $conn->insert_id;
        $insert_stmt->close();
        
        // Commit transaction
        $conn->commit();
        
        // Log the activity
        if (file_exists('../includes/audit_helper.php')) {
            require_once '../includes/audit_helper.php';
            $document_name = $document_type === 'privacy_policy' ? 'Privacy Policy' : 'Terms of Service';
            if (function_exists('logUserActivity')) {
                logUserActivity('UPDATE', 'Legal Documents', "Updated {$document_name} (Version: {$version})", 'legal_documents', $new_document_id);
            }
        }
        
        echo json_encode([
            'success' => true, 
            'message' => ucfirst(str_replace('_', ' ', $document_type)) . ' saved successfully',
            'document_id' => $new_document_id,
            'version' => $version
        ]);
    } else {
        throw new Exception("Failed to save document: " . $conn->error);
    }
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    error_log("Error saving legal document: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred while saving the document']);
}

$conn->close();
?>
