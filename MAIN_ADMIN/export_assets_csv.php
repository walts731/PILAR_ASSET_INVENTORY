<?php
require_once '../connect.php';
session_start();

// Auth check
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo 'Unauthorized';
  exit;
}

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? 'all';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$office_filter = $_GET['office'] ?? 'all';
$category_filter = $_GET['category'] ?? 'all';
$status_filter = $_GET['status'] ?? 'all';

// Build filename with filter info
$filter_suffix = '';
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $filter_suffix = '_' . str_replace(['-', ' '], ['', '_'], $filter_type) . '_' . $from_date . '_to_' . $to_date;
}
$filename = 'assets_export' . $filter_suffix . '_' . date('Ymd_His') . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// CSV column headers (match table columns in asset tab)
fputcsv($out, [
  'Inventory Tag',
  'Description',
  'Category',
  'Office',
  'Status',
  'Employee',
  'End User',
  'Quantity',
  'Unit',
  'Unit Cost',
  'Total Value',
  'Date Created',
  'Last Updated'
]);

// Build SQL query with date and office filtering
$sql = "SELECT 
          a.inventory_tag,
          a.description,
          c.category_name,
          o.office_name,
          a.status,
          e.name as employee_name,
          md.end_user,
          a.quantity,
          a.unit,
          a.value as unit_cost,
          (a.quantity * a.value) as total_value,
          a.acquisition_date as date_created,
          a.last_updated
        FROM assets a
        LEFT JOIN categories c ON a.category = c.id
        LEFT JOIN offices o ON a.office_id = o.id
        LEFT JOIN employees e ON a.employee_id = e.employee_id
        LEFT JOIN mr_details md ON a.id = md.asset_id
        WHERE a.type = 'asset' AND a.quantity > 0";

$params = [];
$types = '';

// Add office filtering if specified
if ($office_filter !== 'all') {
  $sql .= " AND a.office_id = ?";
  $params[] = $office_filter;
  $types .= 'i';
}

// Add category filtering if specified (expects category id)
if ($category_filter !== 'all' && ctype_digit((string)$category_filter)) {
  $sql .= " AND a.category = ?";
  $params[] = (int)$category_filter;
  $types .= 'i';
}

// Add status filtering if specified
if ($status_filter !== 'all') {
  if ($status_filter === 'red_tagged') {
    $sql .= " AND a.red_tagged = 1";
  } else {
    $sql .= " AND a.status = ?";
    $params[] = $status_filter;
    $types .= 's';
  }
}

// Add date filtering if specified
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $sql .= " AND DATE(a.acquisition_date) >= ? AND DATE(a.acquisition_date) <= ?";
  $params[] = $from_date;
  $params[] = $to_date;
  $types .= 'ss';
}

$sql .= " ORDER BY a.acquisition_date DESC, a.id DESC";

// Execute query with or without parameters
if (!empty($params)) {
  $stmt = $conn->prepare($sql);
  $stmt->bind_param($types, ...$params);
  $stmt->execute();
  $res = $stmt->get_result();
} else {
  $res = $conn->query($sql);
}

if ($res) {
  while ($r = $res->fetch_assoc()) {
    // Ensure formatting similar to UI
    $row = [
      $r['inventory_tag'] ?? '',
      $r['description'] ?? '',
      $r['category_name'] ?? '',
      $r['office_name'] ?? '',
      ucfirst($r['status'] ?? ''),
      $r['employee_name'] ?? '',
      $r['end_user'] ?? '',
      number_format((float)($r['quantity'] ?? 0), 0),
      $r['unit'] ?? '',
      number_format((float)($r['unit_cost'] ?? 0), 2, '.', ''),
      number_format((float)($r['total_value'] ?? 0), 2, '.', ''),
      $r['date_created'] ?? '',
      $r['last_updated'] ?? ''
    ];
    fputcsv($out, $row);
  }
}

// Close prepared statement if used
if (isset($stmt)) {
  $stmt->close();
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
  error_log("Failed to insert assets CSV export into generated_reports: " . $e->getMessage());
}

fclose($out);
exit;
?>
