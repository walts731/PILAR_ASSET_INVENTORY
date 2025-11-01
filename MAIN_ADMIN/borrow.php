<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

$page = 'borrow-form';
$pageTitle = 'Borrow Assets Form';

$assets = [];
$assetResult = $conn->query("SELECT id, asset_name, property_no, inventory_tag, description, quantity FROM assets WHERE status = 'available' AND quantity > 0 ORDER BY asset_name ASC");
if ($assetResult) {
    $assets = $assetResult->fetch_all(MYSQLI_ASSOC);
    $assetResult->free();
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        body { background: #f5f7fb; font-family: 'Poppins', sans-serif; }
        .borrow-wrapper { padding: 2rem 0 3rem; }
        .borrow-card { background: #fff; border-radius: 16px; box-shadow: 0 20px 45px rgba(15, 23, 42, 0.12); overflow: hidden; }
        .borrow-header { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; padding: 1.75rem; }
        .borrow-header h1 { font-size: 1.6rem; font-weight: 700; margin: 0; }
        .borrow-body { padding: 2rem 2.5rem; }
        .section-title { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.14em; color: #64748b; font-weight: 700; margin: 2.5rem 0 1rem; }
        .form-control, .form-select { border-radius: 10px; padding: 0.65rem 0.85rem; }
        .form-control:focus, .form-select:focus { border-color: #2563eb; box-shadow: 0 0 0 0.15rem rgba(37, 99, 235, 0.15); }
        .items-table thead th { background: #f8fafc; font-size: 0.8rem; color: #334155; text-transform: uppercase; letter-spacing: 0.08em; }
        .items-table tbody td { vertical-align: middle; }
        .items-table input, .items-table select { font-size: 0.85rem; }
        .items-table .btn { border-radius: 50%; width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; }
        .summary-card { border: 1px dashed #cbd5f5; border-radius: 14px; padding: 1.5rem; background: #f8fbff; }
        .summary-card h5 { color: #1d4ed8; font-weight: 700; }
        .submit-btn { padding: 0.75rem 2.6rem; border-radius: 999px; font-weight: 600; }
        .info-badge { font-size: 0.75rem; letter-spacing: 0.06em; }
        .required::after { content: " *"; color: #dc2626; }
        @media (max-width: 991.98px) {
            .borrow-body { padding: 1.75rem 1.4rem; }
            .section-title { margin-top: 2rem; }
        }
    </style>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container borrow-wrapper">
        <div class="borrow-card">
            <div class="borrow-header d-flex flex-column flex-md-row justify-content-between gap-3 align-items-start align-items-md-center">
                <div>
                    <p class="text-white-50 mb-2 text-uppercase fw-semibold" style="letter-spacing: 0.2em;">Borrowing</p>
                    <h1 class="mb-1">Borrow Assets Request Form</h1>
                    <p class="mb-0 text-white-75">Submit a borrow slip request for accountable assets. Pending requests will appear in the Borrowing Submissions list.</p>
                </div>
                <div class="text-start text-md-end">
                    <span class="badge bg-light text-primary fw-semibold info-badge">Today: <?= date('M d, Y') ?></span>
                    <div class="mt-2 text-white-75">Requestor: <?= htmlspecialchars($_SESSION['fullname'] ?? 'User') ?></div>
                    <div class="text-white-75">Office: <?= htmlspecialchars($_SESSION['office_name'] ?? 'N/A') ?></div>
                </div>
            </div>

            <form id="borrowForm" method="POST" action="submit_borrow_form.php">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <div class="borrow-body">
                    <?php if (!empty($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['success']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <div class="row g-4">
                        <div class="col-lg-4">
                            <label class="form-label required">Borrower Name</label>
                            <input type="text" class="form-control" name="guest_name" value="<?= htmlspecialchars($_SESSION['fullname'] ?? '') ?>" required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label required">Barangay</label>
                            <input type="text" class="form-control" name="barangay" placeholder="e.g. San Roque" required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label required">Contact Number</label>
                            <input type="tel" class="form-control" name="contact" placeholder="09XX-XXX-XXXX" pattern="^09[0-9]{9}$" required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label required">Date Borrowed</label>
                            <input type="date" class="form-control" name="date_borrowed" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label required">Schedule of Return</label>
                            <input type="date" class="form-control" name="schedule_return" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label required">Releasing Officer</label>
                            <input type="text" class="form-control" name="releasing_officer" placeholder="Name of releasing officer" required>
                        </div>
                        <div class="col-lg-4">
                            <label class="form-label required">Approved By</label>
                            <input type="text" class="form-control" name="approved_by" placeholder="Name of approving authority" required>
                        </div>
                    </div>

                    <div class="section-title">Borrowed Items</div>
                    <div class="table-responsive">
                        <table class="table table-bordered items-table" id="borrowItemsTable">
                            <thead>
                                <tr>
                                    <th style="width: 28%;">Asset</th>
                                    <th>Description</th>
                                    <th style="width: 12%;">Available</th>
                                    <th style="width: 14%;">Quantity</th>
                                    <th style="width: 18%;">Remarks</th>
                                    <th style="width: 8%;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <select name="items[0][asset_id]" class="form-select asset-select" required>
                                            <option value="" disabled selected>Select asset...</option>
                                            <?php foreach ($assets as $asset): ?>
                                                <option value="<?= (int)$asset['id'] ?>"
                                                        data-available="<?= (int)$asset['quantity'] ?>"
                                                        data-description="<?= htmlspecialchars($asset['description']) ?>"
                                                        data-property="<?= htmlspecialchars($asset['property_no'] ?? 'N/A') ?>"
                                                        data-tag="<?= htmlspecialchars($asset['inventory_tag'] ?? '') ?>">
                                                    <?= htmlspecialchars($asset['asset_name']) ?>
                                                    <?php if (!empty($asset['property_no'])): ?>
                                                        (<?= htmlspecialchars($asset['property_no']) ?>)
                                                    <?php elseif (!empty($asset['inventory_tag'])): ?>
                                                        (<?= htmlspecialchars($asset['inventory_tag']) ?>)
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                    <td><textarea class="form-control description-field" name="items[0][description]" rows="2" placeholder="Auto-filled from asset but editable"></textarea></td>
                                    <td class="text-center"><span class="badge bg-primary available-badge">0</span></td>
                                    <td><input type="number" name="items[0][quantity]" class="form-control quantity-field" min="1" value="1" required></td>
                                    <td><input type="text" name="items[0][remarks]" class="form-control" placeholder="Optional"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger remove-row" title="Remove row"><i class="bi bi-x-lg"></i></button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-between flex-wrap gap-3 mt-3">
                        <button type="button" class="btn btn-outline-primary" id="addRowBtn"><i class="bi bi-plus-circle"></i> Add Another Item</button>
                        <div class="summary-card">
                            <h5 class="mb-1">Borrow Summary</h5>
                            <p class="mb-2 text-muted">Total Items: <span id="totalItems">1</span></p>
                            <p class="mb-0 text-muted">Ensure all requested items are available prior to submission.</p>
                        </div>
                    </div>

                    <div class="section-title">Acknowledgement</div>
                    <div class="alert alert-info d-flex align-items-start gap-3" role="alert">
                        <i class="bi bi-info-circle-fill fs-4"></i>
                        <div>
                            <strong>Reminder:</strong> Borrowed assets must be returned on or before the expected return date in good and serviceable condition. Any damage or loss should be reported immediately.
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" value="1" id="confirmPolicy" required>
                        <label class="form-check-label" for="confirmPolicy">
                            I confirm that I understand the borrowing policies and agree to comply with all guidelines.
                        </label>
                    </div>

                    <div class="d-flex justify-content-end gap-3">
                        <a href="borrowing.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary submit-btn"><i class="bi bi-send-check me-2"></i>Submit Request</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(function() {
    function updateRowAvailability(row, available) {
        row.find('.available-badge').text(available);
        const quantityInput = row.find('.quantity-field');
        const currentVal = parseInt(quantityInput.val(), 10) || 1;
        if (currentVal > available) {
            quantityInput.val(available > 0 ? available : 1);
        }
        quantityInput.attr('max', available > 0 ? available : 1);
    }

    function renumberRows() {
        $('#borrowItemsTable tbody tr').each(function(index) {
            $(this).find('select, textarea, input').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const updated = name.replace(/items\[(\d+)\]/, 'items[' + index + ']');
                    $(this).attr('name', updated);
                }
            });
        });
        $('#totalItems').text($('#borrowItemsTable tbody tr').length);
    }

    $(document).on('change', '.asset-select', function() {
        const row = $(this).closest('tr');
        const selected = $(this).find('option:selected');
        const available = parseInt(selected.data('available'), 10) || 0;
        const description = selected.data('description') || '';
        const propertyNo = selected.data('property');
        const inventoryTag = selected.data('tag');
        const assetLabel = selected.text().trim();

        let composed = description || assetLabel;
        if (inventoryTag) {
            composed += `\nInventory Tag: ${inventoryTag}`;
        }
        if (propertyNo && propertyNo !== 'N/A') {
            composed += `\nProperty No: ${propertyNo}`;
        }

        row.find('.description-field').val(composed.trim());
        updateRowAvailability(row, available);
    });

    $('#addRowBtn').on('click', function() {
        const rowCount = $('#borrowItemsTable tbody tr').length;
        const newRow = $(`
            <tr>
                <td>
                    <select name="items[${rowCount}][asset_id]" class="form-select asset-select" required>
                        <option value="" disabled selected>Select asset...</option>
                        <?php foreach ($assets as $asset): ?>
                            <option value="<?= (int)$asset['id'] ?>"
                                    data-available="<?= (int)$asset['quantity'] ?>"
                                    data-description="<?= htmlspecialchars($asset['description']) ?>"
                                    data-property="<?= htmlspecialchars($asset['property_no'] ?? 'N/A') ?>"
                                    data-tag="<?= htmlspecialchars($asset['inventory_tag'] ?? '') ?>">
                                <?= htmlspecialchars($asset['asset_name']) ?>
                                <?php if (!empty($asset['property_no'])): ?>
                                    (<?= htmlspecialchars($asset['property_no']) ?>)
                                <?php elseif (!empty($asset['inventory_tag'])): ?>
                                    (<?= htmlspecialchars($asset['inventory_tag']) ?>)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><textarea class="form-control description-field" name="items[${rowCount}][description]" rows="2" placeholder="Auto-filled from asset but editable"></textarea></td>
                <td class="text-center"><span class="badge bg-primary available-badge">0</span></td>
                <td><input type="number" name="items[${rowCount}][quantity]" class="form-control quantity-field" min="1" value="1" required></td>
                <td><input type="text" name="items[${rowCount}][remarks]" class="form-control" placeholder="Optional"></td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger remove-row" title="Remove row"><i class="bi bi-x-lg"></i></button>
                </td>
            </tr>
        `);
        $('#borrowItemsTable tbody').append(newRow);
        renumberRows();
    });

    $(document).on('click', '.remove-row', function() {
        const rows = $('#borrowItemsTable tbody tr');
        if (rows.length === 1) {
            alert('At least one item is required.');
            return;
        }
        $(this).closest('tr').remove();
        renumberRows();
    });

    $('#borrowForm').on('submit', function(e) {
        const rows = $('#borrowItemsTable tbody tr');
        let valid = true;
        rows.each(function() {
            const select = $(this).find('.asset-select');
            const available = parseInt(select.find('option:selected').data('available'), 10) || 0;
            const quantity = parseInt($(this).find('.quantity-field').val(), 10) || 0;
            if (!select.val()) {
                valid = false;
                select.addClass('is-invalid');
            } else {
                select.removeClass('is-invalid');
            }
            if (quantity === 0 || quantity > available) {
                valid = false;
                $(this).find('.quantity-field').addClass('is-invalid');
            } else {
                $(this).find('.quantity-field').removeClass('is-invalid');
            }
        });

        if (!valid) {
            e.preventDefault();
            alert('Please review the borrowed items and ensure quantities do not exceed availability.');
        }
    });
});
</script>
</body>
</html>