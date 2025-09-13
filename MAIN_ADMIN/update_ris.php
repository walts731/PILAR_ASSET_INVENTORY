<?php
require_once '../connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Ensure it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request.");
}

// Get and sanitize form ID
$form_id = intval($_POST['form_id'] ?? 0);
if ($form_id <= 0) {
    die("Invalid form ID.");
}

// Sanitize office ID
$office_id = intval($_POST['office_id'] ?? 0);

// Escape all input values
$division = $conn->real_escape_string($_POST['division'] ?? '');
$responsibility_center = $conn->real_escape_string($_POST['responsibility_center'] ?? '');
$ris_no = $conn->real_escape_string($_POST['ris_no'] ?? '');
$date = $conn->real_escape_string($_POST['date'] ?? '');
$responsibility_code = $conn->real_escape_string($_POST['responsibility_code'] ?? '');
$sai_no = $conn->real_escape_string($_POST['sai_no'] ?? '');
$reason_for_transfer = $conn->real_escape_string($_POST['reason_for_transfer'] ?? '');
$requested_by_name = $conn->real_escape_string($_POST['requested_by_name'] ?? '');
$requested_by_designation = $conn->real_escape_string($_POST['requested_by_designation'] ?? '');
$requested_by_date = $conn->real_escape_string($_POST['requested_by_date'] ?? '');
$approved_by_name = $conn->real_escape_string($_POST['approved_by_name'] ?? '');
$approved_by_designation = $conn->real_escape_string($_POST['approved_by_designation'] ?? '');
$approved_by_date = $conn->real_escape_string($_POST['approved_by_date'] ?? '');
$issued_by_name = $conn->real_escape_string($_POST['issued_by_name'] ?? '');
$issued_by_designation = $conn->real_escape_string($_POST['issued_by_designation'] ?? '');
$issued_by_date = $conn->real_escape_string($_POST['issued_by_date'] ?? '');
$received_by_name = $conn->real_escape_string($_POST['received_by_name'] ?? '');
$received_by_designation = $conn->real_escape_string($_POST['received_by_designation'] ?? '');
$received_by_date = $conn->real_escape_string($_POST['received_by_date'] ?? '');

// Build the SQL query
$sql = "
    UPDATE ris_form SET
        division = '$division',
        responsibility_center = '$responsibility_center',
        ris_no = '$ris_no',
        date = '$date',
        office_id = $office_id,
        responsibility_code = '$responsibility_code',
        sai_no = '$sai_no',
        reason_for_transfer = '$reason_for_transfer',
        requested_by_name = '$requested_by_name',
        requested_by_designation = '$requested_by_designation',
        requested_by_date = '$requested_by_date',
        approved_by_name = '$approved_by_name',
        approved_by_designation = '$approved_by_designation',
        approved_by_date = '$approved_by_date',
        issued_by_name = '$issued_by_name',
        issued_by_designation = '$issued_by_designation',
        issued_by_date = '$issued_by_date',
        received_by_name = '$received_by_name',
        received_by_designation = '$received_by_designation',
        received_by_date = '$received_by_date'
    WHERE id = $form_id
";

// Execute query
if (!$conn->query($sql)) {
    die("Update failed: " . $conn->error);
}

// Redirect back to view with success
header("Location: view_ris.php?id=" . $form_id . "&updated=1");
exit();
?>
