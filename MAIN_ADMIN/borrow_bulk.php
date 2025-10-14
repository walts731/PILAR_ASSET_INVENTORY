<?php
require_once '../connect.php';
require_once '../includes/lifecycle_helper.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

if (!isset($_GET['ids'])) {
  die("No assets selected.");
}

$ids = explode(',', $_GET['ids']);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$types = str_repeat('i', count($ids));

// Example: Update status to "borrowed"
$stmt = $conn->prepare("UPDATE assets SET status = 'borrowed' WHERE id IN ($placeholders)");
$stmt->bind_param($types, ...$ids);
$stmt->execute();
$stmt->close();

// Log lifecycle events for bulk borrowed assets
$office = $_GET['office'] ?? '';
foreach ($ids as $asset_id) {
    logLifecycleEvent(
        $asset_id,
        'BORROWED',
        null, // no specific table reference for bulk operations
        null, // no specific record ID
        null, // from_employee_id (admin processing)
        null, // to_employee_id (bulk borrowing)
        null, // from_office_id
        null, // to_office_id
        "Asset borrowed via bulk operation" . (!empty($office) ? " to {$office}" : "")
    );
}

header("Location: inventory.php?bulk=success&office=" . urlencode($office));
exit();

