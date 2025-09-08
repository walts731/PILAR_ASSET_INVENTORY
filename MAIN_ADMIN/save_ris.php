<?php
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // HEADER + FOOTER (same as before)
    $division = $_POST['division'] ?? '';
    $responsibility_center = $_POST['responsibility_center'] ?? '';
    $ris_no = $_POST['ris_no'] ?? '';
    $date = $_POST['date'] ?? date('Y-m-d');
    $office_id = $_POST['office_id'] ?? '';
    $responsibility_code = $_POST['responsibility_code'] ?? '';
    $sai_no = $_POST['sai_no'] ?? '';
    $purpose = $_POST['purpose'] ?? '';

    // Footer
    $requested_by_name = $_POST['requested_by_name'] ?? '';
    $requested_by_designation = $_POST['requested_by_designation'] ?? '';
    $requested_by_date = $_POST['requested_by_date'] ?? null;

    $approved_by_name = $_POST['approved_by_name'] ?? '';
    $approved_by_designation = $_POST['approved_by_designation'] ?? '';
    $approved_by_date = $_POST['approved_by_date'] ?? null;

    $issued_by_name = $_POST['issued_by_name'] ?? '';
    $issued_by_designation = $_POST['issued_by_designation'] ?? '';
    $issued_by_date = $_POST['issued_by_date'] ?? null;

    $received_by_name = $_POST['received_by_name'] ?? '';
    $received_by_designation = $_POST['received_by_designation'] ?? '';
    $received_by_date = $_POST['received_by_date'] ?? null;

    $footer_date = $_POST['footer_date'] ?? null;

    $form_id = isset($_POST['form_id']) ? (int)$_POST['form_id'] : 0;

    

    // Insert RIS header
    $stmt = $conn->prepare("
    INSERT INTO ris_form (
        form_id,
        division, responsibility_center, ris_no, date, office_id, responsibility_code, sai_no, reason_for_transfer,
        requested_by_name, requested_by_designation, requested_by_date,
        approved_by_name, approved_by_designation, approved_by_date,
        issued_by_name, issued_by_designation, issued_by_date,
        received_by_name, received_by_designation, received_by_date,
        footer_date
    ) VALUES (?,?,?,?,?,?,?,?, ?,?,?, ?,?,?, ?,?,?, ?,?,?, ?,?)
");

    $stmt->bind_param(
        "issssissssssssssssssss",
        $form_id,
        $division,
        $responsibility_center,
        $ris_no,
        $date,
        $office_id,
        $responsibility_code,
        $sai_no,
        $purpose,
        $requested_by_name,
        $requested_by_designation,
        $requested_by_date,
        $approved_by_name,
        $approved_by_designation,
        $approved_by_date,
        $issued_by_name,
        $issued_by_designation,
        $issued_by_date,
        $received_by_name,
        $received_by_designation,
        $received_by_date,
        $footer_date
    );


    if ($stmt->execute()) {
        // Get the inserted RIS Form ID
        $ris_form_id = $stmt->insert_id;
        $stmt->close();

        // Now insert RIS items
        if (!empty($_POST['description'])) {
            $stock_nos   = $_POST['stock_no'] ?? [];
            $units       = $_POST['unit'] ?? [];
            $descriptions = $_POST['description'] ?? [];
            $quantities  = $_POST['quantity'] ?? [];
            $prices      = $_POST['price'] ?? [];
            $totals      = $_POST['total'] ?? [];

            $item_stmt = $conn->prepare("
                INSERT INTO ris_items (ris_form_id, stock_no, unit, description, quantity, price, total)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($descriptions as $i => $desc) {
                if (trim($desc) === '') continue; // skip empty rows

                $stock = $stock_nos[$i] ?? '';
                $unit = $units[$i] ?? '';
                $qty = (int)($quantities[$i] ?? 0);
                $price = (float)($prices[$i] ?? 0);
                $total = (float)($totals[$i] ?? 0);

                $item_stmt->bind_param("isssidd", $ris_form_id, $stock, $unit, $desc, $qty, $price, $total);
                $item_stmt->execute();
            }
            $item_stmt->close();
        }

        echo "<script>
    alert('RIS Form & Items saved successfully!');
    window.location='forms.php?id={$form_id}';
</script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
