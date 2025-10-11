<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ✅ Get form_id from the hidden input for redirect only
    $form_id = isset($_POST['form_id']) ? intval($_POST['form_id']) : 1;
    $ics_row_id = isset($_POST['ics_row_id']) ? intval($_POST['ics_row_id']) : null;

    $entity_name = trim($_POST['entity_name']);
    $fund_cluster = trim($_POST['fund_cluster']);
    $ics_no = trim($_POST['ics_no']);
    $received_from_name = trim($_POST['received_from_name']);
    $received_from_position = trim($_POST['received_from_position']);
    $received_by_name = trim($_POST['received_by_name']);
    $received_by_position = trim($_POST['received_by_position']);

    // Determine existing row to update (if any)
    $existing = null;
    if ($ics_row_id) {
        $stmt_chk = $conn->prepare("SELECT id, header_image FROM ics_form WHERE id = ? LIMIT 1");
        $stmt_chk->bind_param('i', $ics_row_id);
        $stmt_chk->execute();
        $existing = $stmt_chk->get_result()->fetch_assoc();
        $stmt_chk->close();
    }
    if (!$existing) {
        // fallback to latest row
        $res_latest = $conn->query("SELECT id, header_image FROM ics_form ORDER BY id DESC LIMIT 1");
        if ($res_latest && $res_latest->num_rows > 0) {
            $existing = $res_latest->fetch_assoc();
            $ics_row_id = (int)$existing['id'];
        }
    }

    // Handle image upload
    $header_image = '';
    if (!empty($_FILES['header_image']['name'])) {
        $target_dir = "../img/";
        if (!is_dir($target_dir)) {
            @mkdir($target_dir, 0777, true);
        }
        $header_image = time() . '_' . basename($_FILES["header_image"]["name"]);
        $target_file = $target_dir . $header_image;

        if (!move_uploaded_file($_FILES["header_image"]["tmp_name"], $target_file)) {
            die("Failed to upload image.");
        }
    } else if (!empty($existing['header_image'])) {
        // preserve previous header image if no new upload
        $header_image = $existing['header_image'];
    }

    // ✅ Update existing row or insert new if none exists
    if ($existing) {
        if (!empty($header_image)) {
            $stmt = $conn->prepare("UPDATE ics_form 
                SET header_image = ?, entity_name = ?, fund_cluster = ?, ics_no = ?, 
                    received_from_name = ?, received_from_position = ?, 
                    received_by_name = ?, received_by_position = ?, created_at = NOW() 
                WHERE id = ?");
            $stmt->bind_param(
                "ssssssssi",
                $header_image,
                $entity_name,
                $fund_cluster,
                $ics_no,
                $received_from_name,
                $received_from_position,
                $received_by_name,
                $received_by_position,
                $ics_row_id
            );
        } else {
            $stmt = $conn->prepare("UPDATE ics_form 
                SET entity_name = ?, fund_cluster = ?, ics_no = ?, 
                    received_from_name = ?, received_from_position = ?, 
                    received_by_name = ?, received_by_position = ?, created_at = NOW() 
                WHERE id = ?");
            $stmt->bind_param(
                "sssssssi",
                $entity_name,
                $fund_cluster,
                $ics_no,
                $received_from_name,
                $received_from_position,
                $received_by_name,
                $received_by_position,
                $ics_row_id
            );
        }
    } else {
        // Insert new row
        $stmt = $conn->prepare("INSERT INTO ics_form 
            (header_image, entity_name, fund_cluster, ics_no, received_from_name, received_from_position, received_by_name, received_by_position, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param(
            "ssssssss",
            $header_image,
            $entity_name,
            $fund_cluster,
            $ics_no,
            $received_from_name,
            $received_from_position,
            $received_by_name,
            $received_by_position
        );
    }

    if ($stmt->execute()) {
        // ✅ Redirect using the original form_id (not forced to 1)
        header("Location: view_form.php?id=" . $form_id . "&success=1");
        exit;
    } else {
        die("Failed to update ICS data: " . $stmt->error);
    }
} else {
    header("Location: ics_form.php");
    exit;
}
