<?php
// Simple test version to isolate the issue
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test basic functionality
echo json_encode([
    'success' => true,
    'message' => 'Basic PHP script is working',
    'post_data' => $_POST,
    'files_data' => $_FILES
]);
exit;
?>
