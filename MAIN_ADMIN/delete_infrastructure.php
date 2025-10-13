<?php
require_once '../connect.php';
require_once '../includes/audit_helper.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
  $inventory_id = (int)$_GET['id'];

  if ($inventory_id <= 0) {
    header("Location: infrastructure_inventory.php?delete=invalid");
    exit();
  }

  $conn->begin_transaction();
  try {
    // Lock and fetch infrastructure record
    $stmt = $conn->prepare("SELECT * FROM infrastructure_inventory WHERE inventory_id = ? FOR UPDATE");
    $stmt->bind_param('i', $inventory_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $infrastructure = $res->fetch_assoc();
    $stmt->close();

    if (!$infrastructure) {
      throw new Exception('Infrastructure record not found');
    }

    // Delete the infrastructure record
    $stmt = $conn->prepare("DELETE FROM infrastructure_inventory WHERE inventory_id = ?");
    $stmt->bind_param('i', $inventory_id);
    if (!$stmt->execute()) {
      throw new Exception('Failed to delete infrastructure record: ' . $stmt->error);
    }
    $stmt->close();

    // Log the deletion activity
    $description = $infrastructure['item_description'] ?? 'Unknown Infrastructure';
    $context = "Classification: {$infrastructure['classification_type']}, Location: {$infrastructure['location']}";
    logAssetActivity('DELETE', $description, $inventory_id, $context);

    $conn->commit();

    header("Location: infrastructure_inventory.php?delete=success");
    exit();

  } catch (Exception $e) {
    $conn->rollback();

    // Log deletion failure
    $description = $infrastructure['item_description'] ?? 'Unknown Infrastructure';
    logErrorActivity('Infrastructure', "Failed to delete infrastructure: {$description} (ID: {$inventory_id}) - " . $e->getMessage());

    header("Location: infrastructure_inventory.php?delete=failed&msg=" . urlencode($e->getMessage()));
    exit();
  }
} else {
  echo "Invalid request.";
}
?>
