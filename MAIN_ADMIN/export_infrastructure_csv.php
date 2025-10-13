<?php
require_once '../connect.php';
session_start();

// Auth check
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo 'Unauthorized';
  exit;
}

$filename = 'infrastructure_inventory_export_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// CSV column headers (match table columns in infrastructure inventory)
fputcsv($out, [
  'Classification/Type',
  'Item Description',
  'Nature of Occupancy',
  'Location',
  'Date Constructed/Acquired/Manufactured',
  'Property No./Other Reference',
  'Acquisition Cost',
  'Market Appraisal/Insurable Interest',
  'Date of Appraisal',
  'Remarks'
]);

// Build SQL query
$sql = "SELECT
    inventory_id,
    classification_type,
    item_description,
    nature_occupancy,
    location,
    date_constructed_acquired_manufactured,
    property_no_or_reference,
    acquisition_cost,
    market_appraisal_insurable_interest,
    date_of_appraisal,
    remarks
FROM infrastructure_inventory
ORDER BY inventory_id DESC";

$res = $conn->query($sql);

if ($res) {
  while ($r = $res->fetch_assoc()) {
    // Format dates for display
    $date_constructed = '';
    if ($r['date_constructed_acquired_manufactured']) {
      $date_constructed = date("M-Y", strtotime($r['date_constructed_acquired_manufactured']));
    }

    $date_appraisal = '';
    if ($r['date_of_appraisal']) {
      $date_appraisal = date("M d, Y", strtotime($r['date_of_appraisal']));
    }

    // Format currency values
    $acquisition_cost = '';
    if ($r['acquisition_cost']) {
      $acquisition_cost = '₱' . number_format($r['acquisition_cost'], 2, '.', ',');
    }

    $market_appraisal = '';
    if ($r['market_appraisal_insurable_interest']) {
      $market_appraisal = '₱' . number_format($r['market_appraisal_insurable_interest'], 2, '.', ',');
    }

    $row = [
      $r['classification_type'] ?? '',
      $r['item_description'] ?? '',
      $r['nature_occupancy'] ?? '',
      $r['location'] ?? '',
      $date_constructed,
      $r['property_no_or_reference'] ?? '',
      $acquisition_cost,
      $market_appraisal,
      $date_appraisal,
      $r['remarks'] ?? ''
    ];
    fputcsv($out, $row);
  }
}

// Insert record into generated_reports table for tracking
$user_id = $_SESSION['user_id'];
$office_id = $_SESSION['office_id'] ?? null;

try {
  $insert_stmt = $conn->prepare("INSERT INTO generated_reports (user_id, office_id, filename, generated_at) VALUES (?, ?, ?, NOW())");
  $insert_stmt->bind_param("iis", $user_id, $office_id, $filename);
  $insert_stmt->execute();
  $insert_stmt->close();
} catch (Exception $e) {
  // Log error but don't interrupt the export
  error_log("Failed to insert infrastructure CSV export into generated_reports: " . $e->getMessage());
}

fclose($out);
exit;
?>
