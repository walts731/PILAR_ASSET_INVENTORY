<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';

// Start session similar to ICS form (for flash messages, etc.)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Get form_id from URL (for consistency with ICS form)
$form_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Default data
$par_data = [
    'header_image' => '',
    'entity_name' => '',
    'fund_cluster' => '',
    'par_no' => '',
    'office_id' => '',
    'position_office_left' => '',
    'position_office_right' => '',
    'date_received_left' => date('Y-m-d'),
    'date_received_right' => date('Y-m-d')
];

// ✅ Always fetch the latest record and prefill PAR No (editable, no auto-increment)
$latest = $conn->query("SELECT * FROM par_form ORDER BY id DESC LIMIT 1");
if ($latest && $latest->num_rows > 0) {
    $par_data = $latest->fetch_assoc();
    // Keep par_no exactly as stored
}

// Fetch offices for dropdown
$offices = [];
$office_query = $conn->query("SELECT id, office_name FROM offices");
while ($row = $office_query->fetch_assoc()) {
    $offices[] = $row;
}

// Fetch description + unit cost + quantity from assets 
// PAR rule: allow inserting assets with VALUE > 50,000 (strictly greater) and available quantity
$description_details = [];
$result = $conn->query("
    SELECT a.id, a.description, a.value AS unit_cost, a.quantity, a.acquisition_date, 
           a.unit, a.property_no, o.office_name
    FROM assets a
    LEFT JOIN offices o ON a.office_id = o.id
    WHERE a.type = 'asset' AND a.quantity > 0 AND a.value > 50000
");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $desc = $row['description'];
        $description_details[$desc] = [
            'id' => $row['id'],
            'unit_cost' => $row['unit_cost'],
            'quantity' => $row['quantity'],
            'acquisition_date' => $row['acquisition_date'],
            'unit' => $row['unit'],
            'property_no' => $row['property_no'],
            'office_name' => $row['office_name'] // ✅ include office
        ];
    }
}

// Fetch units for dropdown
$units = [];
$unit_query = $conn->query("SELECT id, unit_name FROM unit");
while ($row = $unit_query->fetch_assoc()) {
    $units[] = $row;
}

// Fetch active format templates for par_no and property_no
$par_template = '';
if ($st_fmt = $conn->prepare("SELECT format_template FROM tag_formats WHERE tag_type = 'par_no' AND is_active = 1 LIMIT 1")) {
    $st_fmt->execute();
    $rs_fmt = $st_fmt->get_result();
    if ($rs_fmt && ($r = $rs_fmt->fetch_assoc())) { $par_template = $r['format_template'] ?? ''; }
    $st_fmt->close();
}
$property_template = '';
if ($st_prop = $conn->prepare("SELECT format_template FROM tag_formats WHERE tag_type = 'property_no' AND is_active = 1 LIMIT 1")) {
    $st_prop->execute();
    $rs_prop = $st_prop->get_result();
    if ($rs_prop && ($rp = $rs_prop->fetch_assoc())) { $property_template = $rp['format_template'] ?? ''; }
    $st_prop->close();
}
?>
<style>
    body { background: #f4f6f9; }
    .par-page-wrapper { padding: 2.5rem 0 3.5rem; background: linear-gradient(135deg, rgba(226,232,240,0.5), rgba(148,163,184,0.25)); }
    .par-paper { position: relative; max-width: 960px; margin: 0 auto; background: #fff; border: 1px solid #d9dee6; border-radius: 14px; padding: 2.75rem 3rem; box-shadow: 0 18px 45px rgba(15,23,42,0.15); }
    .par-paper::before { content: ""; position: absolute; inset: 14px; border: 1px solid rgba(148,163,184,0.25); border-radius: 10px; pointer-events: none; }
    .par-paper .form-control, .par-paper .form-select { padding: 0.35rem 0.55rem; font-size: 0.85rem; min-height: 2.1rem; border-radius: 6px; }
    .par-paper .form-control.text-center, .par-paper .form-control.text-end { padding-right: 0.55rem; padding-left: 0.55rem; }
    .par-form-section-title { font-size: 0.82rem; letter-spacing: 0.12em; text-transform: uppercase; color: #6c757d; font-weight: 700; margin-bottom: 0.85rem; }
    .par-heading-divider { margin: 1.75rem 0; border: none; border-top: 2px solid rgba(100,116,139,0.35); }
    .par-table-wrapper { border: 1px solid #ced4da; border-radius: 10px; overflow: hidden; background: #fff; }
    .par-table-wrapper table { margin-bottom: 0; font-size: 0.72rem; }
    .par-table-wrapper thead th { background: #f8fafc; font-size: 0.72rem; vertical-align: middle; padding: 0.4rem 0.32rem; color: #334155; }
    .par-table-wrapper tbody td, .par-table-wrapper tfoot td { padding: 0.35rem 0.32rem; }
    .par-table-wrapper input.form-control, .par-table-wrapper select.form-select { padding: 0.2rem 0.4rem; min-height: 1.7rem; font-size: 0.72rem; }
    .par-table-wrapper .input-group-text, .par-table-wrapper .btn { font-size: 0.72rem; padding: 0.2rem 0.45rem; }
    .par-signature-table input.form-control { border: none; border-radius: 0; border-bottom: 1px solid #adb5bd; background: transparent; }
    .par-signature-table input.form-control:focus { box-shadow: none; border-color: #495057; }
    @media (max-width: 991.98px) { .par-page-wrapper { padding: 1.75rem 1rem 2.5rem; } .par-paper { padding: 2rem 1.6rem; border-radius: 10px; } .par-paper::before { inset: 10px; border-radius: 8px; } }
    @media print { body { background: #fff !important; } .par-page-wrapper { padding: 0; background: transparent; } .par-paper { max-width: 100%; padding: 20mm; border-radius: 0; border: none; box-shadow: none; } .par-paper::before, .navigation-controls, .alert, .btn { display: none !important; } }
</style>
<?php if (!empty($_SESSION['flash'])): ?>
    <?php
    $flash = $_SESSION['flash'];
    $type = isset($flash['type']) ? strtolower($flash['type']) : 'info';
    $allowed = ['primary','secondary','success','danger','warning','info','light','dark'];
    if (!in_array($type, $allowed, true)) { $type = 'info'; }
    ?>
    <div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message'] ?? 'Action completed.') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>

<div class="par-page-wrapper">
    <div class="navigation-controls d-flex justify-content-end mb-3">
        <a href="saved_par.php?id=<?= htmlspecialchars($form_id) ?>" class="btn btn-info">
            <i class="bi bi-folder-check"></i> View Saved PAR
        </a>
    </div>

    <div class="par-paper">
        <form method="post" action="save_par_form.php" enctype="multipart/form-data" class="w-100" onsubmit="return checkDuplicates()">
            <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">

            <div class="mb-4 text-center">
                <?php if (!empty($par_data['header_image'])): ?>
                    <img src="../img/<?= htmlspecialchars($par_data['header_image']) ?>" class="img-fluid mb-2" style="max-width: 100%; height: auto; object-fit: contain;">
                    <input type="hidden" name="header_image" value="<?= htmlspecialchars($par_data['header_image']) ?>">
                <?php else: ?>
                    <p class="text-muted mb-0">No header image available</p>
                <?php endif; ?>
                <input type="file" id="headerImageFile" name="header_image_file" accept="image/*" hidden>
            </div>

            <hr class="par-heading-divider">

            <div class="par-form-section-title">Property Acknowledgment Receipt Details</div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Entity Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control shadow-sm" name="entity_name" id="entityName" value="<?= htmlspecialchars($par_data['entity_name'] ?? '') ?>" placeholder="Enter entity name" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Office / Location <span class="text-danger">*</span></label>
                    <select class="form-select shadow-sm" name="office_id" id="destinationOffice" required>
                        <option value="" disabled <?= empty($par_data['office_id']) ? 'selected' : '' ?>>Select office</option>
                        <option value="outside_lgu">Outside LGU</option>
                        <?php foreach ($offices as $office): ?>
                            <option value="<?= htmlspecialchars($office['id']) ?>" <?= (isset($par_data['office_id']) && (string)$par_data['office_id'] === (string)$office['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($office['office_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Fund Cluster <span class="text-danger">*</span></label>
                    <input type="text" class="form-control shadow-sm" name="fund_cluster" value="<?= htmlspecialchars($par_data['fund_cluster'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">PAR No. (Auto-generated)</label>
                    <div class="input-group shadow-sm">
                        <input type="text" class="form-control border-0" id="parNoField" name="par_no" value="<?= previewTag('par_no') ?>" readonly>
                        <span class="input-group-text bg-light border-0"><i class="bi bi-magic" title="Auto-generated"></i></span>
                    </div>
                    <small class="text-muted">This number will be automatically assigned when you save the form.</small>
                </div>
            </div>

            <div class="par-form-section-title mt-5">Inventory Line Items</div>
            <div class="par-table-wrapper mb-4">
                <table class="table table-bordered text-center align-middle" id="parItemsTable">
                    <thead>
                        <tr>
                            <th>QUANTITY</th>
                            <th>UNIT</th>
                            <th style="width: 28%;">DESCRIPTION</th>
                            <th>PROPERTY NO.</th>
                            <th>DATE ACQUIRED</th>
                            <th>UNIT PRICE</th>
                            <th>AMOUNT</th>
                            <th>ACTIONS</th>
                        </tr>
                    </thead>
                    <tbody id="par-items-body">
                        <tr data-row-index="0">
                            <td><input type="number" class="form-control quantity-field text-end shadow-sm" name="items[0][quantity]" min="1" required></td>
                            <td>
                                <select name="items[0][unit]" class="form-select text-center shadow-sm" required>
                                    <option value="" disabled selected>Select unit</option>
                                    <?php foreach ($units as $unit): ?>
                                        <option value="<?= htmlspecialchars($unit['unit_name']) ?>" <?= (strtolower($unit['unit_name']) === 'unit') ? 'selected' : '' ?>><?= htmlspecialchars($unit['unit_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="position-relative">
                                <input type="text" class="form-control description-field shadow-sm" name="items[0][description]" placeholder="Type description..." style="padding-right: 2rem;" required>
                                <button type="button" class="clear-description" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: transparent; border: none; font-weight: bold; font-size: 1rem; line-height: 1; color: #888; cursor: pointer;">&times;</button>
                                <input type="hidden" name="items[0][asset_id]" value="" class="asset-id-field">
                            </td>
                            <td><input type="text" class="form-control prop-no-input shadow-sm" name="items[0][property_no]" placeholder="Auto-generated on save" readonly></td>
                            <td><input type="date" class="form-control shadow-sm" name="items[0][date_acquired]" value="<?= htmlspecialchars($par_data['date_received_left'] ?? '') ?>" required></td>
                            <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" class="form-control unit-cost-field text-end shadow-sm" name="items[0][unit_price]" step="0.01" style="padding-left: 1.5rem;" required></td>
                            <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" class="form-control amount-field text-end shadow-sm" name="items[0][amount]" step="0.01" style="padding-left: 1.5rem;" readonly required></td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5" class="text-end fw-bold">Total:</td>
                            <td colspan="2" class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" id="grandTotal" class="form-control text-end fw-bold shadow-sm" style="padding-left: 1.5rem;" readonly></td>
                            <td class="text-start"><button type="button" id="addRowBtn" class="btn btn-primary btn-sm">+ Add Row</button></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="par-form-section-title mb-3">Signatories</div>
            <table class="table table-borderless par-signature-table" style="width: 100%; text-align: center; border-collapse: collapse;">
                <tr class="fw-semibold text-uppercase text-muted small">
                    <td style="width: 50%; text-align: left;">Received by <span class="text-danger">*</span></td>
                    <td style="width: 50%; text-align: left;">Issued by <span class="text-danger">*</span></td>
                </tr>
                <tr>
                    <td class="px-3">
                        <input type="text" name="received_by_name" class="form-control text-center fw-bold" value="<?= htmlspecialchars($par_data['received_by_name'] ?? '') ?>" placeholder="Signature over Printed Name" required>
                        <small class="text-muted d-block mt-1">Signature over Printed Name – Received By</small>
                    </td>
                    <td class="px-3">
                        <input type="text" name="issued_by_name" class="form-control text-center fw-bold" value="<?= htmlspecialchars($par_data['issued_by_name'] ?? '') ?>" placeholder="Signature over Printed Name" required>
                        <small class="text-muted d-block mt-1">Signature over Printed Name – Issued By</small>
                    </td>
                </tr>
                <tr class="pt-3">
                    <td class="px-3"><input type="text" name="position_office_left" class="form-control text-center" value="<?= htmlspecialchars($par_data['position_office_left'] ?? '') ?>" placeholder="Position / Office" required></td>
                    <td class="px-3"><input type="text" name="position_office_right" class="form-control text-center" value="<?= htmlspecialchars($par_data['position_office_right'] ?? '') ?>" placeholder="Position / Office" required></td>
                </tr>
                <tr>
                    <td class="px-3 pt-4"><input type="date" name="date_received_left" class="form-control text-center" value="<?= htmlspecialchars($par_data['date_received_left'] ?? '') ?>"></td>
                    <td class="px-3 pt-4"><input type="date" name="date_received_right" class="form-control text-center" value="<?= htmlspecialchars($par_data['date_received_right'] ?? '') ?>"></td>
                </tr>
            </table>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <small class="text-muted"><span class="text-danger">*</span> Required fields</small>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send-check-fill"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<?php include 'modals/par_duplicate_modal.php'; ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const SERVER_PAR_PREVIEW = <?= json_encode(previewTag('par_no')) ?>;
        const PROPERTY_TEMPLATE = <?= json_encode($property_template) ?>;
        const PROPERTY_PREVIEW_NEXT = <?= json_encode(previewTag('property_no')) ?>;

        const destinationOffice = document.getElementById('destinationOffice');
        const entityNameInput = document.getElementById('entityName');
        const parNoField = document.getElementById('parNoField');
        const tableBody = document.getElementById('par-items-body');
        const addRowBtn = document.getElementById('addRowBtn');
        const grandTotalField = document.getElementById('grandTotal');
        const duplicateModalElement = document.getElementById('duplicateModal');
        const duplicateModal = duplicateModalElement ? new bootstrap.Modal(duplicateModalElement) : null;
        let rowIndex = tableBody ? tableBody.querySelectorAll('tr').length : 0;

        function showDuplicateModal() {
            if (duplicateModal) duplicateModal.show();
        }

        function replaceDatePlaceholdersBraced(tpl) {
            const now = new Date();
            const Y = now.getFullYear().toString();
            const M = String(now.getMonth() + 1).padStart(2, '0');
            const D = String(now.getDate()).padStart(2, '0');
            return (tpl || '')
                .replace(/\{YYYY\}/g, Y)
                .replace(/\{YY\}/g, Y.slice(-2))
                .replace(/\{MM\}/g, M)
                .replace(/\{DD\}/g, D)
                .replace(/\{YYYYMM\}/g, Y + M)
                .replace(/\{YYYYMMDD\}/g, Y + M + D);
        }

        function buildOfficeDisplay() {
            if (!destinationOffice) return 'OFFICE';
            const val = destinationOffice.value;
            const selectedText = destinationOffice.options[destinationOffice.selectedIndex]?.text?.trim() || '';
            if (val === 'outside_lgu') {
                return entityNameInput && entityNameInput.value.trim() ? entityNameInput.value.trim() : 'Outside LGU';
            }
            if (entityNameInput && entityNameInput.value.trim()) {
                return entityNameInput.value.trim();
            }
            return val ? (selectedText || 'OFFICE') : 'OFFICE';
        }

        function computeParPreview() {
            if (!parNoField) return;
            const officeDisp = buildOfficeDisplay();
            let updated = String(SERVER_PAR_PREVIEW || '').replace(/\bOFFICE\b|\{OFFICE\}/g, officeDisp);
            updated = updated.replace(/[{}]/g, '');
            parNoField.readOnly = true;
            parNoField.required = false;
            parNoField.placeholder = '';
            parNoField.value = updated;
        }

        function computePropertyPreviews() {
            const inputs = tableBody ? tableBody.querySelectorAll('.prop-no-input') : [];
            if (!inputs.length) return;
            if (!PROPERTY_TEMPLATE) {
                inputs.forEach(input => { if (!input.value) input.placeholder = 'Auto-generated on save'; });
                return;
            }
            const officeDisp = buildOfficeDisplay();
            let processedTpl = replaceDatePlaceholdersBraced(PROPERTY_TEMPLATE).replace(/\bOFFICE\b|\{OFFICE\}/g, officeDisp);
            const seqMatch = processedTpl.match(/\{(#+)\}/);
            if (!seqMatch) {
                const rendered = processedTpl.replace(/[{}]/g, '');
                inputs.forEach(inp => { inp.value = rendered; });
                return;
            }
            const seqToken = seqMatch[0];
            const seqWidth = seqMatch[1].length;
            const parts = processedTpl.split(seqToken);
            const clean = s => (s || '').replace(/[{}]/g, '');
            const prefix = clean(parts[0] || '');
            const suffix = clean(parts[1] || '');

            let startNum = 1;
            const preview = String(PROPERTY_PREVIEW_NEXT || '').trim();
            if (preview) {
                const runs = Array.from(preview.matchAll(/\d+/g));
                let pick = null;
                for (let i = runs.length - 1; i >= 0; i--) {
                    if (runs[i][0].length >= seqWidth) { pick = runs[i]; break; }
                }
                if (!pick && runs.length) pick = runs[runs.length - 1];
                if (pick) startNum = parseInt(pick[0], 10) || 1;
            }

            inputs.forEach((input, idx) => {
                const seq = String(startNum + idx).padStart(seqWidth, '0');
                input.value = `${prefix}${seq}${suffix}`;
            });
        }

        function updateRowAmount(row) {
            if (!row) return;
            const quantity = parseFloat(row.querySelector('input[name$="[quantity]"]')?.value) || 0;
            const unitCost = parseFloat(row.querySelector('input[name$="[unit_price]"]')?.value) || 0;
            const amountField = row.querySelector('input[name$="[amount]"]');
            if (amountField) amountField.value = (quantity * unitCost).toFixed(2);
            updateGrandTotal();
        }

        function updateGrandTotal() {
            if (!tableBody || !grandTotalField) return;
            let sum = 0;
            tableBody.querySelectorAll('input[name$="[amount]"]').forEach(input => { sum += parseFloat(input.value) || 0; });
            grandTotalField.value = sum.toFixed(2);
        }

        function clearRow(row) {
            if (!row) return;
            row.querySelectorAll('input').forEach(input => {
                if (input.classList.contains('prop-no-input')) {
                    input.value = '';
                } else if (!input.readOnly || input.classList.contains('amount-field')) {
                    input.value = '';
                }
            });
            row.querySelectorAll('select').forEach(select => { if (select.options.length) select.selectedIndex = 0; });
            updateRowAmount(row);
            computePropertyPreviews();
        }

        function removeRow(row) {
            if (!row) return;
            if (tableBody.querySelectorAll('tr').length === 1) {
                clearRow(row);
                return;
            }
            row.remove();
            updateGrandTotal();
            computePropertyPreviews();
        }

        function handleDescriptionChange(input) {
            const val = input.value.trim();
            if (!val) return;
            const duplicate = Array.from(tableBody.querySelectorAll('.description-field')).some(field => field !== input && field.value.trim().toLowerCase() === val.toLowerCase());
            if (duplicate) {
                showDuplicateModal();
                input.value = '';
                updateRowAmount(input.closest('tr'));
            }
        }

        function createRow(index) {
            const wrapper = document.createElement('tbody');
            wrapper.innerHTML = `
                <tr data-row-index="${index}">
                    <td><input type="number" class="form-control quantity-field text-end shadow-sm" name="items[${index}][quantity]" min="1" required></td>
                    <td>
                        <select name="items[${index}][unit]" class="form-select text-center shadow-sm" required>
                            <option value="" disabled selected>Select unit</option>
                            <?php foreach ($units as $unit): ?>
                                <option value="<?= htmlspecialchars($unit['unit_name']) ?>" <?= (strtolower($unit['unit_name']) === 'unit') ? 'selected' : '' ?>><?= htmlspecialchars($unit['unit_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td class="position-relative">
                        <input type="text" class="form-control description-field shadow-sm" name="items[${index}][description]" placeholder="Type description..." style="padding-right: 2rem;" required>
                        <button type="button" class="clear-description" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: transparent; border: none; font-weight: bold; font-size: 1rem; line-height: 1; color: #888; cursor: pointer;">&times;</button>
                        <input type="hidden" name="items[${index}][asset_id]" value="" class="asset-id-field">
                    </td>
                    <td><input type="text" class="form-control prop-no-input shadow-sm" name="items[${index}][property_no]" placeholder="Auto-generated on save" readonly></td>
                    <td><input type="date" class="form-control shadow-sm" name="items[${index}][date_acquired]" required></td>
                    <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" class="form-control unit-cost-field text-end shadow-sm" name="items[${index}][unit_price]" step="0.01" style="padding-left: 1.5rem;" required></td>
                    <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" class="form-control amount-field text-end shadow-sm" name="items[${index}][amount]" step="0.01" style="padding-left: 1.5rem;" readonly required></td>
                    <td><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></td>
                </tr>`;
            return wrapper.firstElementChild;
        }

        if (tableBody) {
            tableBody.addEventListener('input', function (event) {
                if (event.target.matches('input[name$="[quantity]"], input[name$="[unit_price]"]')) {
                    updateRowAmount(event.target.closest('tr'));
                }
            });
            tableBody.addEventListener('change', function (event) {
                if (event.target.classList.contains('description-field')) handleDescriptionChange(event.target);
            });
            tableBody.addEventListener('click', function (event) {
                if (event.target.closest('.clear-description')) {
                    clearRow(event.target.closest('tr'));
                } else if (event.target.closest('.remove-row')) {
                    removeRow(event.target.closest('tr'));
                }
            });
        }

        if (addRowBtn) {
            addRowBtn.addEventListener('click', function () {
                const newRow = createRow(rowIndex);
                tableBody.appendChild(newRow);
                rowIndex++;
                computePropertyPreviews();
            });
        }

        function handleDestinationChange() {
            if (!destinationOffice || !entityNameInput) return;
            const val = destinationOffice.value;
            const selectedText = destinationOffice.options[destinationOffice.selectedIndex]?.text?.trim() || '';
            if (val === 'outside_lgu') {
                entityNameInput.readOnly = false;
                entityNameInput.required = true;
                if (!entityNameInput.value.trim()) entityNameInput.placeholder = 'Enter external entity name';
            } else if (val) {
                entityNameInput.readOnly = false;
                entityNameInput.required = false;
                entityNameInput.placeholder = '';
                entityNameInput.value = selectedText;
            } else {
                entityNameInput.readOnly = false;
                entityNameInput.required = false;
                entityNameInput.placeholder = '';
            }
            computeParPreview();
            computePropertyPreviews();
        }

        if (destinationOffice) destinationOffice.addEventListener('change', handleDestinationChange);
        if (entityNameInput) entityNameInput.addEventListener('input', function () {
            computeParPreview();
            computePropertyPreviews();
        });

        computeParPreview();
        computePropertyPreviews();
        updateGrandTotal();

        window.checkDuplicates = function () {
            if (!tableBody) return true;
            const seen = new Set();
            const descriptions = tableBody.querySelectorAll('.description-field');
            for (const input of descriptions) {
                const value = input.value.trim().toLowerCase();
                if (!value) continue;
                if (seen.has(value)) {
                    showDuplicateModal();
                    return false;
                }
                seen.add(value);
            }
            return true;
        };
    });
</script>
