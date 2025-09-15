<?php
require_once '../connect.php';
session_start();

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Collect header data
$entity_name        = $_POST['entity_name'] ?? '';
$fund_cluster       = $_POST['fund_cluster'] ?? '';
$par_no             = $_POST['par_no'] ?? '';
$office_id          = $_POST['office_id'] ?? '';
$position_left      = $_POST['position_office_left'] ?? '';
$position_right     = $_POST['position_office_right'] ?? '';
$date_received_left = $_POST['date_received'] ?? null;
$date_received_right= $_POST['date_received'] ?? null;
$main_form_id       = $_POST['form_id'] ?? null; // for edits

// Handle file upload (if header image uploaded)
$header_image = null;
if (isset($_FILES['header_image']) && $_FILES['header_image']['error'] === UPLOAD_ERR_OK) {
    $filename = time() . "_" . basename($_FILES['header_image']['name']);
    $targetPath = "../SYSTEM_ADMIN/img/" . $filename;
    if (move_uploaded_file($_FILES['header_image']['tmp_name'], $targetPath)) {
        $header_image = $filename;
    }
}

if (empty($_POST['par_no'])) {
    $latestPar = $conn->query("SELECT par_no FROM par_form ORDER BY form_id DESC LIMIT 1");
    if ($latestPar && $latestPar->num_rows > 0) {
        $last = $latestPar->fetch_assoc()['par_no'];
        if (preg_match('/PAR-(\d+)/', $last, $matches)) {
            $nextNum = str_pad(((int)$matches[1] + 1), 4, '0', STR_PAD_LEFT);
            $_POST['par_no'] = "PAR-" . $nextNum;
        } else {
            $_POST['par_no'] = "PAR-0001";
        }
    } else {
        $_POST['par_no'] = "PAR-0001";
    }
}


// --- Always INSERT par_form (header + footer) ---
$sql = "INSERT INTO par_form 
            (entity_name, fund_cluster, par_no, office_id, 
             position_office_left, position_office_right,
             date_received_left, date_received_right, header_image) 
        VALUES (?,?,?,?,?,?,?,?,?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", $entity_name, $fund_cluster, $par_no, $office_id, 
    $position_left, $position_right, $date_received_left, $date_received_right, 
    $header_image);
$stmt->execute();
$form_id = $stmt->insert_id; // get new form_id
$stmt->close();

// --- Insert items linked to this new form ---
if (!empty($_POST['items'])) {
    $sql = "INSERT INTO par_items 
            (form_id, asset_id, quantity, unit, description, property_no, date_acquired, unit_price, amount) 
            VALUES (?,?,?,?,?,?,?,?,?)";
    $stmt = $conn->prepare($sql);

    foreach ($_POST['items'] as $item) {
        if (empty($item['description'])) continue;

        $asset_id     = $item['asset_id'] ?? null;
        $quantity     = $item['quantity'] ?? 0;
        $unit         = $item['unit'] ?? '';
        $description  = $item['description'] ?? '';
        $property_no  = $item['property_no'] ?? '';
        $date_acquired= $item['date_acquired'] ?? null;
        $unit_price   = $item['unit_price'] ?? 0;
        $amount       = $item['amount'] ?? 0;

        $stmt->bind_param("iisssssdd", 
            $form_id, $asset_id, $quantity, $unit, $description, $property_no, 
            $date_acquired, $unit_price, $amount
        );
        $stmt->execute();
    }
    $stmt->close();
}


// Redirect to view page
header("Location: forms.php?id=" . $main_form_id . "&success=1");
exit();
?>
