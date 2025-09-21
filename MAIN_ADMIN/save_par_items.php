<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo 'Method Not Allowed';
  exit();
}

$par_id = isset($_POST['existing_par_id']) ? (int)$_POST['existing_par_id'] : 0;
$form_id = $_POST['form_id'] ?? '';
if ($par_id <= 0) {
  $_SESSION['flash'] = [ 'type' => 'danger', 'message' => 'Invalid PAR reference.' ];
  header('Location: saved_par.php');
  exit();
}

// Begin transaction for safety
$conn->begin_transaction();
try {
  // Update PAR header fields
  $entity_name = trim($_POST['entity_name'] ?? '');
  $fund_cluster = trim($_POST['fund_cluster'] ?? '');
  $par_no = trim($_POST['par_no'] ?? '');
  $position_office_left = trim($_POST['position_office_left'] ?? '');
  $position_office_right = trim($_POST['position_office_right'] ?? '');
  $date_received_left = $_POST['date_received_left'] ?? null;
  $date_received_right = $_POST['date_received_right'] ?? null;

  $stmt_hdr = $conn->prepare("UPDATE par_form SET entity_name = ?, fund_cluster = ?, par_no = ?, position_office_left = ?, position_office_right = ?, date_received_left = ?, date_received_right = ? WHERE id = ?");
  $stmt_hdr->bind_param(
    'sssssssi',
    $entity_name,
    $fund_cluster,
    $par_no,
    $position_office_left,
    $position_office_right,
    $date_received_left,
    $date_received_right,
    $par_id
  );
  if (!$stmt_hdr->execute()) {
    throw new Exception('Failed to update PAR header: ' . $stmt_hdr->error);
  }
  $stmt_hdr->close();

  // Update items
  $items = $_POST['items'] ?? [];
  if (!is_array($items)) { $items = []; }

  $stmt_upd_item = $conn->prepare("UPDATE par_items SET quantity = ?, unit = ?, description = ?, property_no = ?, date_acquired = ?, unit_price = ?, amount = ? WHERE item_id = ? AND form_id = ?");
  if (!$stmt_upd_item) { throw new Exception('Prepare par_items update failed: ' . $conn->error); }

  // Prepared statement to update assets_new aggregate rows
  $stmt_upd_assets_new = $conn->prepare("UPDATE assets_new SET description = ?, quantity = ?, unit_cost = ?, unit = ? WHERE id = ?");
  if (!$stmt_upd_assets_new) { throw new Exception('Prepare assets_new update failed: ' . $conn->error); }

  $warnings = [];
  foreach ($items as $item_id => $data) {
    $item_id = (int)$item_id;
    $quantity = isset($data['quantity']) ? (float)$data['quantity'] : 0;
    $unit = trim($data['unit'] ?? '');
    $description = trim($data['description'] ?? '');
    $property_no = trim($data['property_no'] ?? '');
    $date_acquired = $data['date_acquired'] ?? null;
    $unit_price = isset($data['unit_price']) ? (float)$data['unit_price'] : 0.0;
    $asset_id = isset($data['asset_id']) ? (int)$data['asset_id'] : 0;

    if ($quantity < 0) { $quantity = 0; }

    // Enforce PAR rule on edit as well: unit price must be > 50,000
    if ($unit_price <= 50000) {
      // Keep previous amount from DB to avoid zeroing, but mark warning later
      // Fetch current item to compute amount if needed
      $res_prev = $conn->query("SELECT amount FROM par_items WHERE item_id = " . (int)$item_id . " AND form_id = " . (int)$par_id . " LIMIT 1");
      $row_prev = $res_prev ? $res_prev->fetch_assoc() : null;
      $amount = $row_prev ? (float)$row_prev['amount'] : ($quantity * $unit_price);
      $warnings[] = "Item #$item_id has unit price <= 50,000. Enforced rule kept previous amount.";
    } else {
      $amount = $quantity * $unit_price;
    }

    $stmt_upd_item->bind_param(
      'issssddii',
      $quantity,
      $unit,
      $description,
      $property_no,
      $date_acquired,
      $unit_price,
      $amount,
      $item_id,
      $par_id
    );
    if (!$stmt_upd_item->execute()) {
      throw new Exception('Failed to update PAR item #' . $item_id . ': ' . $stmt_upd_item->error);
    }

    // Sync core fields to item-level asset (if available)
    if ($asset_id > 0) {
      $stmt = $conn->prepare("UPDATE assets SET description = ?, unit = ?, acquisition_date = ?, value = ?, property_no = ? WHERE id = ?");
      if (!$stmt) { throw new Exception('Prepare assets update failed: ' . $conn->error); }
      $stmt->bind_param('sssdsi', $description, $unit, $date_acquired, $unit_price, $property_no, $asset_id);
      if (!$stmt->execute()) { throw new Exception('Failed to sync asset #' . $asset_id . ': ' . $stmt->error); }
      $stmt->close();

      // Also sync to assets_new via assets.asset_new_id
      $stmt_lookup = $conn->prepare("SELECT asset_new_id FROM assets WHERE id = ? LIMIT 1");
      if ($stmt_lookup) {
        $stmt_lookup->bind_param('i', $asset_id);
        if ($stmt_lookup->execute()) {
          $res_lookup = $stmt_lookup->get_result();
          $row_lookup = $res_lookup ? $res_lookup->fetch_assoc() : null;
          $asset_new_id = $row_lookup ? (int)$row_lookup['asset_new_id'] : 0;
          if ($asset_new_id > 0) {
            // Update description, quantity (aggregate), unit_cost (unit price), and unit
            $qty_int = (int)$quantity;
            $stmt_upd_assets_new->bind_param('sidsi', $description, $qty_int, $unit_price, $unit, $asset_new_id);
            if (!$stmt_upd_assets_new->execute()) {
              throw new Exception('Failed to sync assets_new #' . $asset_new_id . ': ' . $stmt_upd_assets_new->error);
            }
          }
        }
        $stmt_lookup->close();
      }
    }
  }
  $stmt_upd_item->close();
  $stmt_upd_assets_new->close();

  $conn->commit();
  $_SESSION['flash'] = [ 'type' => empty($warnings) ? 'success' : 'warning', 'message' => empty($warnings) ? 'PAR updated successfully.' : ('PAR updated with notices: ' . implode(' ', $warnings)) ];
  header('Location: view_par.php?id=' . $par_id . '&form_id=' . urlencode((string)$form_id) . '&success=1');
  exit();
} catch (Throwable $e) {
  $conn->rollback();
  $_SESSION['flash'] = [ 'type' => 'danger', 'message' => 'Failed to update PAR: ' . $e->getMessage() ];
  header('Location: view_par.php?id=' . $par_id . '&form_id=' . urlencode((string)$form_id));
  exit();
}
