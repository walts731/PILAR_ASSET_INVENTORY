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

// Fetch offices for dropdown
$offices_result = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");
$offices = $offices_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= $ris_data ? htmlspecialchars($ris_data['ris_no'] ?? 'RIS Form') : 'Form Viewer' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>

    <?php include 'includes/sidebar.php'; ?>
    <div class="main">
        <?php include 'includes/topbar.php'; ?>

        <a href="saved_ris.php?id=<?php echo $ris_form_id ?>" class="btn btn-secondary mb-3">
            <i class="bi bi-arrow-left"></i> Back to Saved RIS
        </a>

        <div class="mb-3 text-center">
            <?php if (!empty($ris_data['header_image'])): ?>
                <img src="../img/<?= htmlspecialchars($ris_data['header_image']) ?>" class="img-fluid mb-3" style="max-width: 100%; height: auto; object-fit: contain;">
            <?php endif; ?>
        </div>

        <form method="post" action="update_ris.php">
            <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Division</label>
                    <input type="text" class="form-control" name="division" value="<?= htmlspecialchars($ris_data['division'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Responsibility Center</label>
                    <input type="text" class="form-control" name="responsibility_center" value="<?= htmlspecialchars($ris_data['responsibility_center'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">RIS No.</label>
                    <input type="text" class="form-control" name="ris_no" value="<?= htmlspecialchars($ris_data['ris_no'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control" name="date" value="<?= htmlspecialchars($ris_data['date'] ?? '') ?>">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Office/Unit</label>
                    <select class="form-select" name="office_id">
                        <?php foreach ($offices as $office): ?>
                            <option value="<?= $office['id'] ?>" <?= ($office['id'] == $ris_data['office_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($office['office_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-semibold">Code</label>
                    <input type="text" class="form-control" name="responsibility_code" value="<?= htmlspecialchars($ris_data['responsibility_code'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">SAI No.</label>
                    <input type="text" class="form-control" name="sai_no" value="<?= htmlspecialchars($ris_data['sai_no'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control" name="sai_date" value="<?= htmlspecialchars($ris_data['date'] ?? '') ?>">
                </div>
            </div>

            <!-- ITEMS TABLE (READONLY) -->
            <table class="table table-bordered align-middle text-center">
                <thead>
                    <tr class="table-secondary">
                        <th colspan="4">REQUISITION</th>
                        <th colspan="3">ISSUANCE</th>
                    </tr>
                    <tr class="table-light">
                        <th>Stock No</th>
                        <th>Unit</th>
                        <th style="width: 30%;">Description</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ris_items)): ?>
                        <?php foreach ($ris_items as $item): ?>
                            <tr>
                                <td><input type="text" class="form-control" value="<?= htmlspecialchars($item['stock_no']) ?>" readonly></td>
                                <td><input type="text" class="form-control" value="<?= htmlspecialchars($item['unit']) ?>" readonly></td>
                                <td><input type="text" class="form-control" value="<?= htmlspecialchars($item['description']) ?>" readonly></td>
                                <td><input type="number" class="form-control" value="<?= htmlspecialchars($item['quantity']) ?>" readonly></td>
                                <td><input type="number" step="0.01" class="form-control" value="<?= htmlspecialchars($item['price']) ?>" readonly></td>
                                <td><input type="number" step="0.01" class="form-control" value="<?= htmlspecialchars($item['total']) ?>" readonly></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="col-md-12 mb-3">
                <label class="form-label fw-semibold">Purpose</label>
                <input type="text" class="form-control" name="reason_for_transfer" value="<?= htmlspecialchars($ris_data['reason_for_transfer'] ?? '') ?>">
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
                        <td><input type="text" class="form-control" name="requested_by_name" value="<?= htmlspecialchars($ris_data['requested_by_name'] ?? '') ?>"></td>
                        <td><input type="text" class="form-control" name="approved_by_name" value="<?= htmlspecialchars($ris_data['approved_by_name'] ?? '') ?>"></td>
                        <td><input type="text" class="form-control" name="issued_by_name" value="<?= htmlspecialchars($ris_data['issued_by_name'] ?? '') ?>"></td>
                        <td><input type="text" class="form-control" name="received_by_name" value="<?= htmlspecialchars($ris_data['received_by_name'] ?? '') ?>"></td>
                    </tr>
                    <tr>
                        <td>Designation:</td>
                        <td><input type="text" class="form-control" name="requested_by_designation" value="<?= htmlspecialchars($ris_data['requested_by_designation'] ?? '') ?>"></td>
                        <td><input type="text" class="form-control" name="approved_by_designation" value="<?= htmlspecialchars($ris_data['approved_by_designation'] ?? '') ?>"></td>
                        <td><input type="text" class="form-control" name="issued_by_designation" value="<?= htmlspecialchars($ris_data['issued_by_designation'] ?? '') ?>"></td>
                        <td><input type="text" class="form-control" name="received_by_designation" value="<?= htmlspecialchars($ris_data['received_by_designation'] ?? '') ?>"></td>
                    </tr>
                    <tr>
                        <td>Date:</td>
                        <td><input type="date" class="form-control" name="requested_by_date" value="<?= htmlspecialchars($ris_data['requested_by_date'] ?? '') ?>"></td>
                        <td><input type="date" class="form-control" name="approved_by_date" value="<?= htmlspecialchars($ris_data['approved_by_date'] ?? '') ?>"></td>
                        <td><input type="date" class="form-control" name="issued_by_date" value="<?= htmlspecialchars($ris_data['issued_by_date'] ?? '') ?>"></td>
                        <td><input type="date" class="form-control" name="received_by_date" value="<?= htmlspecialchars($ris_data['received_by_date'] ?? '') ?>"></td>
                    </tr>
                </tbody>
            </table>

            <div class="mb-5">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save Changes
                </button>
                <a href="generate_ris_pdf.php?id=<?= $ris_data['id'] ?>" class="btn btn-info">
                    <i class="bi bi-printer"></i> Print / Export PDF
                </a>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>