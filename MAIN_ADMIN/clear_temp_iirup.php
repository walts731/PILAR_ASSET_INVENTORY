<?php
session_start();
require_once '../connect.php';

header('Content-Type: application/json');

try {
    // Count items before deletion
    $count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM temp_iirup_items");
    $count_stmt->execute();
    $count_result = $count_stmt->get_result();
    $count_row = $count_result->fetch_assoc();
    $item_count = $count_row['count'];
    $count_stmt->close();

    // Delete all temporary items (since table doesn't have user/session filtering)
    $delete_stmt = $conn->prepare("DELETE FROM temp_iirup_items");
    $delete_stmt->execute();
    $delete_stmt->close();

    echo json_encode([
        'success' => true,
        'count' => $item_count,
        'message' => "Cleared $item_count temporary items successfully"
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
