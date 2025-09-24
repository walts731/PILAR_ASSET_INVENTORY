<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: itr_form.php');
  exit;
}

// Used only for redirect back to view_form.php
$form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
$itr_id  = isset($_POST['itr_id']) ? (int)$_POST['itr_id'] : 0;

// Sanitize inputs
$entity_name = trim($_POST['entity_name'] ?? '');
$fund_cluster = trim($_POST['fund_cluster'] ?? '');
$from_accountable_officer = trim($_POST['from_accountable_officer'] ?? '');
$to_accountable_officer = trim($_POST['to_accountable_officer'] ?? '');
$itr_no = trim($_POST['itr_no'] ?? '');
$date = trim($_POST['date'] ?? '');
$transfer_type = trim($_POST['transfer_type'] ?? '');
$reason_for_transfer = trim($_POST['reason_for_transfer'] ?? '');

$approved_by = trim($_POST['approved_by'] ?? '');
$approved_designation = trim($_POST['approved_designation'] ?? '');
$approved_date = trim($_POST['approved_date'] ?? '');

$released_by = trim($_POST['released_by'] ?? '');
$released_designation = trim($_POST['released_designation'] ?? '');
$released_date = trim($_POST['released_date'] ?? '');

$received_by = trim($_POST['received_by'] ?? '');
$received_designation = trim($_POST['received_designation'] ?? '');
$received_date = trim($_POST['received_date'] ?? '');

// Handle optional header image upload
$header_image = '';
if (!empty($_FILES['header_image']['name'])) {
  $target_dir = "../img/";
  $filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '_', basename($_FILES['header_image']['name']));
  $target_file = $target_dir . $filename;
  if (!move_uploaded_file($_FILES['header_image']['tmp_name'], $target_file)) {
    die('Failed to upload header image.');
  }
  $header_image = $filename;
}

// If itr_id provided, update that record; else update latest if exists; else insert new
if ($itr_id > 0) {
  // Update existing by provided itr_id
  if (!empty($header_image)) {
    $sql = "UPDATE itr_form SET header_image=?, entity_name=?, fund_cluster=?, from_accountable_officer=?, to_accountable_officer=?, itr_no=?, `date`=?, transfer_type=?, reason_for_transfer=?, approved_by=?, approved_designation=?, approved_date=?, released_by=?, released_designation=?, released_date=?, received_by=?, received_designation=?, received_date=? WHERE itr_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
      'ssssssssssssssssssi',
      $header_image,
      $entity_name,
      $fund_cluster,
      $from_accountable_officer,
      $to_accountable_officer,
      $itr_no,
      $date,
      $transfer_type,
      $reason_for_transfer,
      $approved_by,
      $approved_designation,
      $approved_date,
      $released_by,
      $released_designation,
      $released_date,
      $received_by,
      $received_designation,
      $received_date,
      $itr_id
    );
  } else {
    $sql = "UPDATE itr_form SET entity_name=?, fund_cluster=?, from_accountable_officer=?, to_accountable_officer=?, itr_no=?, `date`=?, transfer_type=?, reason_for_transfer=?, approved_by=?, approved_designation=?, approved_date=?, released_by=?, released_designation=?, released_date=?, received_by=?, received_designation=?, received_date=? WHERE itr_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
      'sssssssssssssssssi',
      $entity_name,
      $fund_cluster,
      $from_accountable_officer,
      $to_accountable_officer,
      $itr_no,
      $date,
      $transfer_type,
      $reason_for_transfer,
      $approved_by,
      $approved_designation,
      $approved_date,
      $released_by,
      $released_designation,
      $released_date,
      $received_by,
      $received_designation,
      $received_date,
      $itr_id
    );
  }
} else {
  // Determine if a latest record exists
  $check = $conn->query("SELECT itr_id FROM itr_form ORDER BY itr_id DESC LIMIT 1");
  if ($check && $check->num_rows > 0) {
    $row = $check->fetch_assoc();
    $latest_id = (int)$row['itr_id'];
    // Update latest
    if (!empty($header_image)) {
      $sql = "UPDATE itr_form SET header_image=?, entity_name=?, fund_cluster=?, from_accountable_officer=?, to_accountable_officer=?, itr_no=?, `date`=?, transfer_type=?, reason_for_transfer=?, approved_by=?, approved_designation=?, approved_date=?, released_by=?, released_designation=?, released_date=?, received_by=?, received_designation=?, received_date=? WHERE itr_id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param(
        'ssssssssssssssssssi',
        $header_image,
        $entity_name,
        $fund_cluster,
        $from_accountable_officer,
        $to_accountable_officer,
        $itr_no,
        $date,
        $transfer_type,
        $reason_for_transfer,
        $approved_by,
        $approved_designation,
        $approved_date,
        $released_by,
        $released_designation,
        $released_date,
        $received_by,
        $received_designation,
        $received_date,
        $latest_id
      );
    } else {
      $sql = "UPDATE itr_form SET entity_name=?, fund_cluster=?, from_accountable_officer=?, to_accountable_officer=?, itr_no=?, `date`=?, transfer_type=?, reason_for_transfer=?, approved_by=?, approved_designation=?, approved_date=?, released_by=?, released_designation=?, released_date=?, received_by=?, received_designation=?, received_date=? WHERE itr_id=?";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param(
        'sssssssssssssssssi',
        $entity_name,
        $fund_cluster,
        $from_accountable_officer,
        $to_accountable_officer,
        $itr_no,
        $date,
        $transfer_type,
        $reason_for_transfer,
        $approved_by,
        $approved_designation,
        $approved_date,
        $released_by,
        $released_designation,
        $released_date,
        $received_by,
        $received_designation,
        $received_date,
        $latest_id
      );
    }
  } else {
    // Insert new
    $cols = 'entity_name, fund_cluster, from_accountable_officer, to_accountable_officer, itr_no, `date`, transfer_type, reason_for_transfer, approved_by, approved_designation, approved_date, released_by, released_designation, released_date, received_by, received_designation, received_date';
    $vals = '?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?';
    $types = 'sssssssssssssssss';
    $params = [
      $entity_name,
      $fund_cluster,
      $from_accountable_officer,
      $to_accountable_officer,
      $itr_no,
      $date,
      $transfer_type,
      $reason_for_transfer,
      $approved_by,
      $approved_designation,
      $approved_date,
      $released_by,
      $released_designation,
      $released_date,
      $received_by,
      $received_designation,
      $received_date
    ];

    if (!empty($header_image)) {
      $cols = 'header_image, ' . $cols;
      $vals = '?,'. $vals;
      $types = 's' . $types;
      array_unshift($params, $header_image);
    }

    $sql = "INSERT INTO itr_form ($cols) VALUES ($vals)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
  }
}

if (!$stmt->execute()) {
  die('Failed to save ITR data: ' . $stmt->error);
}

// Redirect back to the view page to reflect changes
$redirect = 'view_form.php';
if ($form_id > 0) {
  $redirect .= '?id=' . $form_id . '&success=1';
}
header('Location: ' . $redirect);
exit;
