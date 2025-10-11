<?php
require_once '../connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and assign variables
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 0;
    $office_id = isset($_POST['office_id']) ? intval($_POST['office_id']) : null;

    $division = trim($_POST['division'] ?? '');
    $responsibility_center = trim($_POST['responsibility_center'] ?? '');
    $responsibility_code = trim($_POST['responsibility_code'] ?? null);
    $ris_no = trim($_POST['ris_no'] ?? '');
    $sai_no = trim($_POST['sai_no'] ?? '');
    $date = trim($_POST['date'] ?? null);

    $requested_by_name = trim($_POST['requested_by_name'] ?? '');
    $requested_by_designation = trim($_POST['requested_by_designation'] ?? '');
    $requested_by_date = trim($_POST['requested_by_date'] ?? null);

    $approved_by_name = trim($_POST['approved_by_name'] ?? '');
    $approved_by_designation = trim($_POST['approved_by_designation'] ?? '');
    $approved_by_date = trim($_POST['approved_by_date'] ?? null);

    $issued_by_name = trim($_POST['issued_by_name'] ?? '');
    $issued_by_designation = trim($_POST['issued_by_designation'] ?? '');
    $issued_by_date = trim($_POST['issued_by_date'] ?? null);

    $received_by_name = trim($_POST['received_by_name'] ?? '');
    $received_by_designation = trim($_POST['received_by_designation'] ?? '');
    $received_by_date = trim($_POST['received_by_date'] ?? null);

    $footer_date = trim($_POST['footer_date'] ?? null);
    $reason_for_transfer = trim($_POST['reason_for_transfer'] ?? '');

    // ✅ Handle header image upload
    $header_image = null;
    if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = "../img/";
        $file_name = time() . "_" . basename($_FILES['header_image']['name']);
        $target_path = $upload_dir . $file_name;

        if (move_uploaded_file($_FILES['header_image']['tmp_name'], $target_path)) {
            $header_image = $file_name;
        }
    }

    // ✅ Validate office_id exists
    if ($office_id) {
        $stmt = $conn->prepare("SELECT id FROM offices WHERE id = ?");
        $stmt->bind_param("i", $office_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            die("Error: Invalid office_id. Please select a valid office.");
        }
    } else {
        die("Error: office_id is required.");
    }

    // ✅ Check if record already exists for this form_id
    $stmt = $conn->prepare("SELECT id FROM ris_form WHERE form_id = ?");
    $stmt->bind_param("i", $form_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // ✅ Update existing record
        $sql = "UPDATE ris_form SET 
                    office_id=?, header_image=COALESCE(?, header_image),
                    division=?, responsibility_center=?, responsibility_code=?, 
                    ris_no=?, sai_no=?, date=?, 
                    requested_by_name=?, requested_by_designation=?, requested_by_date=?, 
                    approved_by_name=?, approved_by_designation=?, approved_by_date=?, 
                    issued_by_name=?, issued_by_designation=?, issued_by_date=?, 
                    received_by_name=?, received_by_designation=?, received_by_date=?, 
                    footer_date=?, reason_for_transfer=? 
                WHERE form_id=?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssssssssssssssssssssi", 
            $office_id, $header_image,
            $division, $responsibility_center, $responsibility_code,
            $ris_no, $sai_no, $date,
            $requested_by_name, $requested_by_designation, $requested_by_date,
            $approved_by_name, $approved_by_designation, $approved_by_date,
            $issued_by_name, $issued_by_designation, $issued_by_date,
            $received_by_name, $received_by_designation, $received_by_date,
            $footer_date, $reason_for_transfer, $form_id
        );
        $stmt->execute();

    } else {
        // ✅ Insert new record
        $sql = "INSERT INTO ris_form (
                    form_id, office_id, header_image, division, responsibility_center, responsibility_code, 
                    ris_no, sai_no, date,
                    requested_by_name, requested_by_designation, requested_by_date,
                    approved_by_name, approved_by_designation, approved_by_date,
                    issued_by_name, issued_by_designation, issued_by_date,
                    received_by_name, received_by_designation, received_by_date,
                    footer_date, reason_for_transfer
                ) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $conn->prepare($sql);
        // Types: form_id (i), office_id (i), then 21 strings
        $stmt->bind_param("iisssssssssssssssssssss", 
            $form_id, $office_id, $header_image, $division, $responsibility_center, $responsibility_code,
            $ris_no, $sai_no, $date,
            $requested_by_name, $requested_by_designation, $requested_by_date,
            $approved_by_name, $approved_by_designation, $approved_by_date,
            $issued_by_name, $issued_by_designation, $issued_by_date,
            $received_by_name, $received_by_designation, $received_by_date,
            $footer_date, $reason_for_transfer
        );
        $stmt->execute();
    }

    // ✅ Redirect back to RIS form with success flag
    header("Location: view_form.php?id=" . $form_id . "&success=1");
    exit;
} else {
    die("Invalid request.");
}
?>
