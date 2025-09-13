<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

$form_id = $_GET['id'] ?? '';
$ris_form_id = $_GET['form_id'] ?? '';


// Fetch RIS form by ID
$stmt = $conn->prepare("SELECT f.*, o.office_name 
                        FROM ris_form f
                        LEFT JOIN offices o ON f.office_id = o.id
                        WHERE f.id = ?");
$stmt->bind_param("i", $form_id);
$stmt->execute();
$result = $stmt->get_result();
$ris_data = $result->fetch_assoc() ?? [];
$stmt->close();

// Fetch RIS items
$item_stmt = $conn->prepare("SELECT * FROM ris_items WHERE ris_form_id = ?");
$item_stmt->bind_param("i", $form_id);
$item_stmt->execute();
$item_result = $item_stmt->get_result();
$ris_items = $item_result->fetch_all(MYSQLI_ASSOC);
$item_stmt->close();
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $form ? htmlspecialchars($form['form_title']) : 'Form Viewer' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="css/dashboard.css" />

</head>

<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <!-- Back button -->
        <a href="saved_ris.php?id=<?php echo $ris_form_id?>" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Back to Saved RIS
        </a>

        <!-- RIS HEADER -->
        <div class="mb-3 text-center">
            <?php if (!empty($ris_data['header_image'])): ?>
                <img src="../img/<?= htmlspecialchars($ris_data['header_image']) ?>"
                    class="img-fluid mb-3"
                    style="max-width: 100%; height: auto; object-fit: contain;">
            <?php endif; ?>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Division</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($ris_data['division'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Responsibility Center</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($ris_data['responsibility_center'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">RIS No.</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($ris_data['ris_no'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Date</label>
                <input type="text" class="form-control" value="<?= !empty($ris_data['date']) ? date('F d, Y', strtotime($ris_data['date'])) : '' ?>" readonly>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Office/Unit</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($ris_data['office_name'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Code</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($ris_data['responsibility_code'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">SAI No.</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($ris_data['sai_no'] ?? '') ?>" readonly>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Date</label>
                <input type="text" class="form-control" value="<?= !empty($ris_data['date']) ? date('F d, Y', strtotime($ris_data['date'])) : '' ?>" readonly>
            </div>
        </div>

        <!-- ITEMS TABLE -->
        <table class="table table-bordered align-middle text-center">
            <thead>
                <tr class="table-secondary">
                    <th colspan="4">REQUISITION</th>
                    <th colspan="4">ISSUANCE</th>
                </tr>
                <tr class="table-light">
                    <th>Stock No</th>
                    <th>Unit</th>
                    <th style="width: 30%;">Description</th>
                    <th>Quantity</th>
                    <th>Quantity</th>
                    <th>Signature</th>
                    <th>Price</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($ris_items)): ?>
                    <?php foreach ($ris_items as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['stock_no']) ?></td>
                            <td><?= htmlspecialchars($item['unit']) ?></td>
                            <td><?= htmlspecialchars($item['description']) ?></td>
                            <td><?= htmlspecialchars($item['quantity']) ?></td>
                            <td><?= htmlspecialchars($item['quantity_issued'] ?? '') ?></td>
                            <td><?= htmlspecialchars($item['signature'] ?? '') ?></td>
                            <td><?= number_format($item['price'], 2) ?></td>
                            <td><?= number_format($item['total'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">No items found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="col-md-12">
                <label class="form-label fw-semibold">Purpose</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($ris_data['reason_for_transfer'] ?? '') ?>" readonly>
            </div>

        <!-- FOOTER -->
        <table class="table table-bordered text-center align-middle">
            <thead class="table-secondary">
                <tr>
                    <th></th>
                    <th>REQUESTED BY:</th>
                    <th>APPROVED BY:</th>
                    <th>ISSUED BY:</th>
                    <th>RECEIVED BY:</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Printed Name:</td>
                    <td><?= htmlspecialchars($ris_data['requested_by_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ris_data['approved_by_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ris_data['issued_by_name'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ris_data['received_by_name'] ?? '') ?></td>
                </tr>
                <tr>
                    <td>Designation:</td>
                    <td><?= htmlspecialchars($ris_data['requested_by_designation'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ris_data['approved_by_designation'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ris_data['issued_by_designation'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ris_data['received_by_designation'] ?? '') ?></td>
                </tr>
                <tr>
                    <td>Date:</td>
                    <td><?= htmlspecialchars($ris_data['requested_by_date'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ris_data['approved_by_date'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ris_data['issued_by_date'] ?? '') ?></td>
                    <td><?= htmlspecialchars($ris_data['received_by_date'] ?? '') ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="js/dashboard.js"></script>

</body>

</html>