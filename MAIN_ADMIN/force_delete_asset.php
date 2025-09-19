<?php
require_once '../connect.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit();
}

$asset_id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
if ($asset_id <= 0) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid asset id']);
  exit();
}

$conn->begin_transaction();
try {
  // Get asset info including parent aggregate id
  $stmt = $conn->prepare("SELECT id, asset_new_id FROM assets WHERE id = ? FOR UPDATE");
  $stmt->bind_param('i', $asset_id);
  $stmt->execute();
  $res = $stmt->get_result();
  $asset = $res->fetch_assoc();
  $stmt->close();

  if (!$asset) {
    throw new Exception('Asset not found');
  }

  $asset_new_id = (int)($asset['asset_new_id'] ?? 0);

  // Best-effort cleanup of dependent records
  // 1) MR details
  $stmt = $conn->prepare("DELETE FROM mr_details WHERE asset_id = ?");
  $stmt->bind_param('i', $asset_id);
  $stmt->execute();
  $stmt->close();

  // 2) ICS items mapping (decrement quantity and total_cost, or delete if becomes zero)
  $stmt = $conn->prepare("SELECT item_id, quantity, unit_cost FROM ics_items WHERE asset_id = ?");
  $stmt->bind_param('i', $asset_id);
  $stmt->execute();
  $resItems = $stmt->get_result();
  $items = [];
  while ($row = $resItems->fetch_assoc()) { $items[] = $row; }
  $stmt->close();

  foreach ($items as $it) {
    $itemId = (int)$it['item_id'];
    $qty = (int)$it['quantity'];
    $unitCost = (float)$it['unit_cost'];
    if ($qty > 1) {
      $newQty = $qty - 1;
      $newTotal = $unitCost * $newQty;
      $stmtU = $conn->prepare("UPDATE ics_items SET quantity = ?, total_cost = ? WHERE item_id = ?");
      $stmtU->bind_param('idi', $newQty, $newTotal, $itemId);
      $stmtU->execute();
      $stmtU->close();
    } else {
      $stmtD = $conn->prepare("DELETE FROM ics_items WHERE item_id = ?");
      $stmtD->bind_param('i', $itemId);
      $stmtD->execute();
      $stmtD->close();
    }
  }

  // 3) Disable FK checks as last resort within this transaction scope
  $conn->query('SET FOREIGN_KEY_CHECKS=0');

  // 4) Delete the asset
  $stmt = $conn->prepare("DELETE FROM assets WHERE id = ?");
  $stmt->bind_param('i', $asset_id);
  if (!$stmt->execute()) {
    $stmt->close();
    throw new Exception('Failed to delete asset: ' . $conn->error);
  }
  $stmt->close();

  // 5) Re-enable FK checks
  $conn->query('SET FOREIGN_KEY_CHECKS=1');

  // 6) Decrement the parent assets_new quantity, if applicable
  if ($asset_new_id > 0) {
    $stmt = $conn->prepare("UPDATE assets_new SET quantity = CASE WHEN quantity > 0 THEN quantity - 1 ELSE 0 END WHERE id = ?");
    $stmt->bind_param('i', $asset_new_id);
    if (!$stmt->execute()) {
      $stmt->close();
      throw new Exception('Failed to update assets_new quantity');
    }
    $stmt->close();
  }

  $conn->commit();
  echo json_encode(['success' => true]);
} catch (Exception $e) {
  $conn->rollback();
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
