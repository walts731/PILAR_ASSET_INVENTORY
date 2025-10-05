<?php
require_once '../connect.php';
session_start();

// Auth check
if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo 'Unauthorized';
  exit;
}

// Get category ID and validate
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
if ($category_id <= 0) {
  http_response_code(400);
  echo 'Invalid category ID';
  exit;
}

// Get selected asset IDs if provided
$selected_assets = [];
if (isset($_GET['selected_assets']) && !empty($_GET['selected_assets'])) {
  $selected_assets = array_map('intval', explode(',', $_GET['selected_assets']));
}

// Get filter parameters
$filter_type = $_GET['filter_type'] ?? 'all';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';

// Fetch category details
$category = null;
$stmt = $conn->prepare("SELECT id, category_name FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  $category = $result->fetch_assoc();
}
$stmt->close();

if (!$category) {
  http_response_code(404);
  echo 'Category not found';
  exit;
}

// Build filename with filter info
$category_name_safe = preg_replace('/[^a-zA-Z0-9_-]/', '_', $category['category_name']);
$filter_suffix = '';
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $filter_suffix = '_' . str_replace(['-', ' '], ['', '_'], $filter_type) . '_' . $from_date . '_to_' . $to_date;
}
$filename = 'category_' . $category_name_safe . '_export' . $filter_suffix . '_' . date('Ymd_His') . '.csv';

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=' . $filename);
header('Pragma: no-cache');
header('Expires: 0');

$out = fopen('php://output', 'w');

// CSV column headers
fputcsv($out, [
  'ICS No',
  'Description',
  'Category',
  'Quantity',
  'Unit',
  'Unit Cost',
  'Total Value',
  'Date Created',
  'Office'
]);

// Build SQL query
$sql = "
  SELECT 
    an.id AS an_id,
    an.description,
    an.quantity,
    an.unit,
    an.unit_cost,
    an.date_created,
    COALESCE((
      SELECT c.category_name
      FROM assets a
      LEFT JOIN categories c ON a.category = c.id
      WHERE a.asset_new_id = an.id
      ORDER BY a.id ASC
      LIMIT 1
    ), 'Uncategorized') AS category_name,
    f.ics_no AS ics_no,
    COALESCE(o.office_name, 'Outside LGU') AS office_name
  FROM assets_new an
  LEFT JOIN ics_form f ON f.id = an.ics_id
  LEFT JOIN offices o ON o.id = an.office_id
  WHERE EXISTS (
    SELECT 1 FROM assets ax WHERE ax.asset_new_id = an.id AND ax.category = ?
  )";

$params = [$category_id];
$types = 'i';

// Add selected assets filter if provided
if (!empty($selected_assets)) {
  $placeholders = str_repeat('?,', count($selected_assets) - 1) . '?';
  $sql .= " AND an.id IN ($placeholders)";
  $params = array_merge($params, $selected_assets);
  $types .= str_repeat('i', count($selected_assets));
}

// Add date filtering if specified
if ($filter_type !== 'all' && !empty($from_date) && !empty($to_date)) {
  $sql .= " AND DATE(an.date_created) >= ? AND DATE(an.date_created) <= ?";
  $params[] = $from_date;
  $params[] = $to_date;
  $types .= 'ss';
}

$sql .= " ORDER BY an.date_created DESC";

// Execute query
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$total_records = 0;
$total_quantity = 0;
$total_value = 0;

if ($res) {
  while ($r = $res->fetch_assoc()) {
    $unit_cost = (float)($r['unit_cost'] ?? 0);
    $quantity = (int)($r['quantity'] ?? 0);
    $item_total = $unit_cost * $quantity;
    
    $row = [
      $r['ics_no'] ?? '',
      $r['description'] ?? '',
      $r['category_name'] ?? '',
      $quantity,
      $r['unit'] ?? '',
      number_format($unit_cost, 2, '.', ''),
      number_format($item_total, 2, '.', ''),
      $r['date_created'] ?? '',
      $r['office_name'] ?? ''
    ];
    fputcsv($out, $row);
    
    $total_records++;
    $total_quantity += $quantity;
    $total_value += $item_total;
  }
}

// Add summary row
fputcsv($out, []);
fputcsv($out, ['SUMMARY']);
fputcsv($out, ['Total Records:', $total_records]);
fputcsv($out, ['Total Quantity:', $total_quantity]);
fputcsv($out, ['Total Value:', number_format($total_value, 2, '.', '')]);
fputcsv($out, ['Category:', $category['category_name']]);
fputcsv($out, ['Generated:', date('Y-m-d H:i:s')]);

$stmt->close();

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
  error_log("Failed to insert category CSV export into generated_reports: " . $e->getMessage());
}

fclose($out);
exit;
?>
