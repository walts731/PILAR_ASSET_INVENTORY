<?php
require_once '../connect.php';
session_start();

if (!isset($_POST)) {
    die("No data submitted.");
}

// Get RIS ID if updating
$ris_id = isset($_POST['ris_id']) ? intval($_POST['ris_id']) : 0;

// Collect form inputs
$division = trim($_POST['division'] ?? '');
$responsibility_center = trim($_POST['responsibility_center'] ?? '');
$ris_no = trim($_POST['ris_no'] ?? '');
$date = trim($_POST['date'] ?? date('Y-m-d'));
$office_id = intval($_POST['office'] ?? 0); // use office_id
$responsibility_code = trim($_POST['responsibility_code'] ?? '');
$sai_no = trim($_POST['sai_no'] ?? '');
$purpose = trim($_POST['purpose'] ?? '');

// Footer fields
$requested_by_name = trim($_POST['requested_by_name'] ?? '');
$approved_by_name = trim($_POST['approved_by_name'] ?? '');
$issued_by_name = trim($_POST['issued_by_name'] ?? '');
$received_by_name = trim($_POST['received_by_name'] ?? '');

$requested_by_designation = trim($_POST['requested_by_designation'] ?? '');
$approved_by_designation = trim($_POST['approved_by_designation'] ?? '');
$issued_by_designation = trim($_POST['issued_by_designation'] ?? '');
$received_by_designation = trim($_POST['received_by_designation'] ?? '');

$requested_by_date = trim($_POST['requested_by_date'] ?? date('Y-m-d'));
$approved_by_date = trim($_POST['approved_by_date'] ?? date('Y-m-d'));
$issued_by_date = trim($_POST['issued_by_date'] ?? date('Y-m-d'));
$received_by_date = trim($_POST['received_by_date'] ?? date('Y-m-d'));

// Handle header image upload
$header_image = null;
if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
    $tmp_name = $_FILES['header_image']['tmp_name'];
    $filename = basename($_FILES['header_image']['name']);
    $target_dir = "../img/";
    $target_file = $target_dir . time() . '_' . $filename;
    if (move_uploaded_file($tmp_name, $target_file)) {
        $header_image = $target_file;
    } else {
        die("Failed to upload header image.");
    }
}

// Decide to INSERT or UPDATE
if ($ris_id > 0) {
    // UPDATE
    $stmt = $conn->prepare("
        UPDATE ris_form SET 
            header_image = COALESCE(?, header_image),
            division=?, responsibility_center=?, ris_no=?, date=?, office_id=?, responsibility_code=?, sai_no=?, purpose=?,
            requested_by_name=?, approved_by_name=?, issued_by_name=?, received_by_name=?,
            requested_by_designation=?, approved_by_designation=?, issued_by_designation=?, received_by_designation=?,
            requested_by_date=?, approved_by_date=?, issued_by_date=?, received_by_date=?
        WHERE id=?
    ");
    $stmt->bind_param("sssssssssssssssssssssi",
        $header_image, $division, $responsibility_center, $ris_no, $date, $office_id, $responsibility_code, $sai_no, $purpose,
        $requested_by_name, $approved_by_name, $issued_by_name, $received_by_name,
        $requested_by_designation, $approved_by_designation, $issued_by_designation, $received_by_designation,
        $requested_by_date, $approved_by_date, $issued_by_date, $received_by_date,
        $ris_id
    );
    $stmt->execute();
    if ($stmt->error) die("Update failed: " . $stmt->error);
    $stmt->close();
    echo "RIS updated successfully!";
} else {
    // INSERT
    $stmt = $conn->prepare("
        INSERT INTO ris_form (
            header_image, division, responsibility_center, ris_no, date, office_id, responsibility_code, sai_no, purpose,
            requested_by_name, approved_by_name, issued_by_name, received_by_name,
            requested_by_designation, approved_by_designation, issued_by_designation, received_by_designation,
            requested_by_date, approved_by_date, issued_by_date, received_by_date
        ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $stmt->bind_param("sssssssssssssssssssss",
        $header_image, $division, $responsibility_center, $ris_no, $date, $office_id, $responsibility_code, $sai_no, $purpose,
        $requested_by_name, $approved_by_name, $issued_by_name, $received_by_name,
        $requested_by_designation, $approved_by_designation, $issued_by_designation, $received_by_designation,
        $requested_by_date, $approved_by_date, $issued_by_date, $received_by_date
    );
    $stmt->execute();
    if ($stmt->error) die("Insert failed: " . $stmt->error);
    $stmt->close();
    echo "RIS saved successfully!";
}

$conn->close();
?>
