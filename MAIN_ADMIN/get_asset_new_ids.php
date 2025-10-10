<?php
require_once '../connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'Method Not Allowed']);
  exit();
}

$ids = $_POST['ids'] ?? [];
if (!is_array($ids) || empty($ids)) {
  http_response_code(400);
  echo json_encode(['error' => 'No asset IDs provided']);
  exit();
}

// Sanitize to integers and remove invalids
$ids = array_values(array_filter(array_map(function($v){
  $n = (int)$v;
  return $n > 0 ? $n : null;
}, $ids)));

if (empty($ids)) {
  http_response_code(400);
  echo json_encode(['error' => 'No valid asset IDs provided']);
  exit();
}

try {
  // Build placeholders for IN clause
  $placeholders = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));

  $sql = "SELECT id, COALESCE(NULLIF(asset_new_id, 0), NULL) AS asset_new_id FROM assets WHERE id IN ($placeholders)";
  $stmt = $conn->prepare($sql);
  if (!$stmt) { throw new Exception('Prepare failed: ' . $conn->error); }
  $stmt->bind_param($types, ...$ids);
  if (!$stmt->execute()) { throw new Exception('Execute failed: ' . $stmt->error); }
  $res = $stmt->get_result();

  $map = [];
  $unique = [];
  while ($row = $res->fetch_assoc()) {
    $aid = (int)$row['id'];
    $anid = isset($row['asset_new_id']) ? (int)$row['asset_new_id'] : null;
    $map[$aid] = $anid ?: null;
    if ($anid) { $unique[$anid] = true; }
  }
  $stmt->close();

  echo json_encode([
    'ok' => true,
    'map' => $map,
    'unique_asset_new_ids' => array_map('intval', array_keys($unique)),
    'unique_count' => count($unique)
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
