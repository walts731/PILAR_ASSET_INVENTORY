<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';

// Start session to access flash messages
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Get form_id from URL, default to null if not provided
$form_id = isset($_GET['id']) ? intval($_GET['id']) : null;

// Always fetch the latest ICS data for header/footer information
$sql = "SELECT id, header_image, entity_name, fund_cluster, ics_no, 
               received_from_name, received_from_position, 
               received_by_name, received_by_position, created_at 
        FROM ics_form 
        ORDER BY id DESC 
        LIMIT 1";
$result = $conn->query($sql);

// Default values for new forms
$ics_data = [
    'id' => null,
    'header_image' => '',
    'entity_name' => '',
    'fund_cluster' => '',
    'ics_no' => '',
    'received_from_name' => '',
    'received_from_position' => '',
    'received_by_name' => '',
    'received_by_position' => '',
    'created_at' => ''
];

// If we have previous records, use the latest for header/footer defaults and prefill ics_no
if ($result && $result->num_rows > 0) {
    $latest_record = $result->fetch_assoc();
    $ics_data = [
        'id' => null, // Always null for new forms
        'header_image' => $latest_record['header_image'],
        'entity_name' => $latest_record['entity_name'],
        'fund_cluster' => $latest_record['fund_cluster'],
        'ics_no' => $latest_record['ics_no'], // Prefill latest ICS no (editable)
        'received_from_name' => $latest_record['received_from_name'],
        'received_from_position' => $latest_record['received_from_position'],
        'received_by_name' => $latest_record['received_by_name'],
        'received_by_position' => $latest_record['received_by_position'],
        'created_at' => ''
    ];
}

// Fetch unit options
$unit_options = [];
$result = $conn->query("SELECT unit_name FROM unit");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $unit_options[] = $row['unit_name'];
    }
}

// Fetch office options
$office_options = [];
$result = $conn->query("SELECT id, office_name FROM offices");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $office_options[] = $row;
    }
}

// Fetch active ICS template
$ics_template = '';
if ($st_fmt = $conn->prepare("SELECT format_template FROM tag_formats WHERE tag_type = 'ics_no' AND is_active = 1 LIMIT 1")) {
    $st_fmt->execute();
    $rs_fmt = $st_fmt->get_result();
    if ($rs_fmt && ($r = $rs_fmt->fetch_assoc())) {
        $ics_template = $r['format_template'] ?? '';
    }
    $st_fmt->close();
}

// Load ICS max threshold for client-side hints/validation
$ics_max = 50000.00; // default fallback
$thrRes = $conn->query("SELECT ics_max FROM form_thresholds ORDER BY id ASC LIMIT 1");
if ($thrRes && $thrRes->num_rows > 0) {
    $thrRow = $thrRes->fetch_assoc();
    if (isset($thrRow['ics_max'])) {
        $ics_max = (float)$thrRow['ics_max'];
    }
}

// Determine if current user is MAIN ADMIN (for editable entity name behavior)
$is_main_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'MAIN_ADMIN';

?>
<style>
    body {
        background: #f4f6f9;
    }

    .ics-page-wrapper {
        padding: 2.5rem 0 3.5rem;
        background: linear-gradient(135deg, rgba(226, 232, 240, 0.5), rgba(148, 163, 184, 0.25));
    }

    .ics-paper {
        position: relative;
        max-width: 960px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #d9dee6;
        border-radius: 14px;
        padding: 2.75rem 3rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.15);
    }

    .ics-paper .form-control,
    .ics-paper .form-select {
        padding: 0.35rem 0.55rem;
        font-size: 0.85rem;
        min-height: 2.1rem;
        border-radius: 6px;
    }

    .ics-paper .form-control.text-center,
    .ics-paper .form-control.text-end {
        padding-right: 0.55rem;
        padding-left: 0.55rem;
    }

    .ics-paper .input-group-text {
        font-size: 0.85rem;
        padding: 0.35rem 0.55rem;
    }

    .ics-table-wrapper input.form-control,
    .ics-table-wrapper select.form-select {
        padding: 0.2rem 0.4rem;
        min-height: 1.7rem;
        font-size: 0.72rem;
    }

    .ics-table-wrapper .input-group-text,
    .ics-table-wrapper .btn {
        font-size: 0.72rem;
        padding: 0.2rem 0.45rem;
    }

    .ics-table-wrapper .position-absolute.top-50 {
        left: 4px;
        font-size: 0.68rem;
    }

    .ics-paper::before {
        content: "";
        position: absolute;
        inset: 14px;
        border: 1px solid rgba(148, 163, 184, 0.25);
        border-radius: 10px;
        pointer-events: none;
    }

    .ics-form-section-title {
        font-size: 0.82rem;
        letter-spacing: 0.12em;
        text-transform: uppercase;
        color: #6c757d;
        font-weight: 700;
        margin-bottom: 0.85rem;
    }

    .ics-heading-divider {
        margin: 1.75rem 0;
        border: none;
        border-top: 2px solid rgba(100, 116, 139, 0.35);
    }

    .ics-table-wrapper {
        border: 1px solid #ced4da;
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
    }

    .ics-table-wrapper table {
        margin-bottom: 0;
        font-size: 0.72rem;
    }

    .ics-table-wrapper thead th {
        background: #f8fafc;
        font-size: 0.72rem;
        vertical-align: middle;
        padding: 0.4rem 0.32rem;
        color: #334155;
    }

    .ics-table-wrapper tbody td,
    .ics-table-wrapper tfoot td {
        padding: 0.35rem 0.32rem;
    }

    .ics-paper .form-control,
    .ics-paper .form-select {
        padding: 0.35rem 0.55rem;
        border-radius: 0;
        border-bottom: 1px solid #adb5bd;
        background: transparent;
    }

    .ics-signature-table input.form-control {
        border: none;
        border-radius: 0;
        border-bottom: 1px solid #adb5bd;
        background: transparent;
    }

    .ics-signature-table input.form-control:focus {
        box-shadow: none;
        border-color: #495057;
    }

    @media (max-width: 991.98px) {
        .ics-page-wrapper {
            padding: 1.75rem 1rem 2.5rem;
        }

        .ics-paper {
            padding: 2rem 1.6rem;
            border-radius: 10px;
        }

        .ics-paper::before {
            inset: 10px;
            border-radius: 8px;
        }
    }

    @media print {
        body {
            background: #ffffff !important;
        }

        .ics-page-wrapper {
            padding: 0;
            background: transparent;
        }

        .ics-paper {
            max-width: 100%;
            padding: 20mm;
            border-radius: 0;
            border: none;
            box-shadow: none;
        }

        .ics-paper::before,
        .navigation-controls,
        .alert,
        .btn {
            display: none !important;
        }
    }
</style>
<?php if (!empty($_SESSION['flash'])): ?>
    <?php
    $flash = $_SESSION['flash'];
    // Normalize type to Bootstrap alert classes
    $type = isset($flash['type']) ? strtolower($flash['type']) : 'info';
    $allowed = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];
    if (!in_array($type, $allowed, true)) {
        $type = 'info';
    }
    ?>
    <div class="alert alert-<?= htmlspecialchars($type) ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($flash['message'] ?? 'Action completed.') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
<div class="ics-page-wrapper">
    <div class="navigation-controls d-flex justify-content-end mb-3">
        <a href="saved_ics.php?id=<?= htmlspecialchars($form_id) ?>" class="btn btn-info">
            <i class="bi bi-folder-check"></i> View Saved ICS
        </a>
    </div>

    <div class="ics-paper">
        <form method="post" action="save_ics_items.php" enctype="multipart/form-data" class="w-100">
            <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">
            <input type="hidden" name="ics_id" value="<?= htmlspecialchars($ics_data['id'] ?? '') ?>">

            <div class="mb-4 text-center">
                <?php if (!empty($ics_data['header_image'])): ?>
                    <img src="../img/<?= htmlspecialchars($ics_data['header_image']) ?>"
                        class="img-fluid mb-2"
                        style="max-width: 100%; height: auto; object-fit: contain;">
                    <input type="hidden" name="header_image" value="<?= htmlspecialchars($ics_data['header_image']) ?>">
                <?php else: ?>
                    <p class="text-muted mb-0">No header image available</p>
                <?php endif; ?>
                <input type="file" id="headerImageFile" name="header_image_file" accept="image/*" style="display:none;" hidden>
            </div>

            <hr class="ics-heading-divider">

            <div class="ics-form-section-title">Custodian Slip Details</div>
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ENTITY NAME</label>
                    <input type="text" class="form-control shadow-sm" name="entity_name" id="entityName" value="<?= htmlspecialchars($ics_data['entity_name'] ?? '') ?>" placeholder="Enter entity name">
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        DESTINATION <span class="text-danger">*</span>
                    </label>
                    <select class="form-select shadow-sm" name="office_id" id="destinationOffice" required>
                        <option value="" disabled selected>Select office</option>
                        <option value="outside_lgu">Outside LGU</option>
                        <?php foreach ($office_options as $office): ?>
                            <option value="<?= htmlspecialchars($office['id']) ?>">
                                <?= htmlspecialchars($office['office_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">FUND CLUSTER <span class="text-danger">*</span></label>
                    <input type="text" class="form-control shadow-sm" name="fund_cluster" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ICS NO. (Auto-generated)</label>
                    <div class="input-group shadow-sm">
                        <input type="text" class="form-control border-0" id="icsNoField" name="ics_no" value="<?= previewTag('ics_no') ?>" readonly>
                        <span class="input-group-text bg-light border-0">
                            <i class="bi bi-magic" title="Auto-generated"></i>
                        </span>
                    </div>
                    <small class="text-muted">This number will be automatically assigned when you save the form.</small>
                </div>
            </div>

            <div class="ics-form-section-title mt-5">Inventory Line Items</div>
            <div class="ics-table-wrapper mb-4">
                <table class="table table-bordered text-center align-middle" id="icsTable">
                    <thead>
                        <tr>
                            <th rowspan="2">QUANTITY</th>
                            <th rowspan="2">UNIT</th>
                            <th colspan="2">AMOUNT</th>
                            <th rowspan="2">DESCRIPTION</th>
                            <th rowspan="2">ITEM NO</th>
                            <th rowspan="2">ESTIMATED USEFUL LIFE</th>
                            <th rowspan="2">ACTIONS</th>
                        </tr>
                        <tr>
                            <th>UNIT COST</th>
                            <th>TOTAL COST</th>
                        </tr>
                    </thead>
                    <tbody id="ics-items-body">
                        <?php for ($i = 0; $i < 1; $i++): ?>
                            <tr>
                                <td><input type="number" class="form-control quantity-field shadow-sm" name="quantity[]" min="1" required></td>
                                <td>
                                    <select class="form-select shadow-sm" name="unit[]" required>
                                        <option value="" disabled>Select unit</option>
                                        <?php foreach ($unit_options as $unit): ?>
                                            <option value="<?= htmlspecialchars($unit) ?>" <?= (strtolower($unit) === 'unit') ? 'selected' : '' ?>><?= htmlspecialchars($unit) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="position-relative">
                                    <span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span>
                                    <input type="number" class="form-control text-end shadow-sm" step="0.01" name="unit_cost[]" max="<?= htmlspecialchars(number_format($ics_max, 2, '.', '')) ?>" style="padding-left: 1.5rem;" required>
                                </td>
                                <td class="position-relative">
                                    <span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span>
                                    <input type="number" class="form-control total_cost text-end shadow-sm" name="total_cost[]" step="0.01" readonly style="padding-left: 1.5rem;">
                                </td>
                                <td class="position-relative">
                                    <input type="text" class="form-control description-field shadow-sm"
                                        name="description[]"
                                        placeholder="Type description..."
                                        style="padding-right: 2rem;" required>
                                    <button type="button"
                                        class="clear-description"
                                        style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: transparent; border: none; font-weight: bold; font-size: 1rem; line-height: 1; color: #888; cursor: pointer;">&times;</button>
                                </td>
                                <td><input type="text" class="form-control shadow-sm item-no-field" name="item_no[]" value="1" readonly required></td>
                                <td><input type="text" class="form-control shadow-sm" name="estimated_useful_life[]" required></td>
                                <td><!-- No remove button for first row --></td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold"></td>
                            <td class="position-relative">
                                <span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span>
                                <input type="number" id="grandTotal" class="form-control fw-bold text-end shadow-sm" readonly style="padding-left: 1.5rem;">
                            </td>
                            <td colspan="3" class="text-start">
                                <button type="button" id="addRowBtn" class="btn btn-primary btn-sm">+ Add Row</button>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="ics-form-section-title mb-3">Signatories</div>
            <table class="table table-borderless ics-signature-table" style="width:100%; text-align:center; border-collapse: collapse;">
                <tr class="fw-semibold text-uppercase text-muted small">
                    <td style="width:50%; text-align:left;">Received from <span class="text-danger">*</span></td>
                    <td style="width:50%; text-align:left;">Received by <span class="text-danger">*</span></td>
                </tr>
                <tr>
                    <td class="px-3">
                        <input type="text" name="received_from_name"
                            class="form-control text-center fw-bold"
                            value="<?= htmlspecialchars($ics_data['received_from_name']) ?>"
                            placeholder="Enter name"
                            required>
                    </td>
                    <td class="px-3">
                        <input type="text" name="received_by_name"
                            class="form-control text-center fw-bold"
                            value=""
                            placeholder="Enter name"
                            required>
                    </td>
                </tr>
                <tr class="pt-3">
                    <td class="px-3">
                        <input type="text" name="received_from_position"
                            class="form-control text-center"
                            value="<?= htmlspecialchars($ics_data['received_from_position']) ?>"
                            placeholder="Enter position">
                    </td>
                    <td class="px-3">
                        <input type="text" name="received_by_position"
                            class="form-control text-center"
                            value=""
                            placeholder="Enter position">
                    </td>
                </tr>
                <tr>
                    <td class="px-3 pt-4">
                        <input type="date" name="received_from_date"
                            class="form-control text-center"
                            value="">
                    </td>
                    <td class="px-3 pt-4">
                        <input type="date" name="received_by_date"
                            class="form-control text-center"
                            value="">
                    </td>
                </tr>
            </table>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <small class="text-muted"><span class="text-danger">*</span> Required fields</small>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send-check-fill"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Expose role flag to JS
        const IS_MAIN_ADMIN = <?= json_encode($is_main_admin) ?>;
        // Dynamic ICS No preview
        const ICS_TEMPLATE = <?= json_encode($ics_template) ?>;
        const ICS_PREVIEW_NEXT = <?= json_encode(previewTag('ics_no')) ?>;

        function deriveOfficeAcronym(name) {
            if (!name) return 'OFFICE';
            const parts = String(name).trim().toUpperCase().split(/\s+/);
            let ac = parts.map(p => (p[0] || '').replace(/[^A-Z0-9]/g, '')).join('');
            if (!ac) ac = String(name).replace(/[^A-Z0-9]/g, '').toUpperCase();
            return ac || 'OFFICE';
        }

        function replaceDatePlaceholdersLocal(tpl) {
            const now = new Date();
            const Y = now.getFullYear().toString();
            const M = String(now.getMonth() + 1).padStart(2, '0');
            const D = String(now.getDate()).padStart(2, '0');
            return tpl
                .replace(/\{YYYY\}|YYYY/g, Y)
                .replace(/\{YY\}|YY/g, Y.slice(-2))
                .replace(/\{MM\}|MM/g, M)
                .replace(/\{DD\}|DD/g, D)
                .replace(/\{YYYYMM\}|YYYYMM/g, Y + M)
                .replace(/\{YYYYMMDD\}|YYYYMMDD/g, Y + M + D);
        }

        function padDigitsForPreview(tpl) {
            return tpl.replace(/\{(#+)\}/g, (m, hashes) => {
                const w = hashes.length;
                return '0'.repeat(Math.max(0, w - 1)) + '1';
            });
        }

        function computeIcsPreview() {
            const field = document.getElementById('icsNoField');
            if (!field) return;
            // Determine OFFICE display
            const sel = document.getElementById('destinationOffice');
            let officeDisp = 'OFFICE';
            if (sel) {
                const opt = sel.options[sel.selectedIndex];
                const txt = opt ? (opt.text || '') : '';
                const en = document.getElementById('entityName');
                // Always prefer typed entity name when available, regardless of role
                if (en && en.value.trim()) {
                    officeDisp = en.value.trim();
                } else if (sel.value && sel.value !== 'outside_lgu') {
                    // Fallback to selected office name when internal office is chosen
                    officeDisp = (txt || '').trim() || 'OFFICE';
                } else {
                    officeDisp = 'OFFICE';
                }
            }
            // Build from server-side preview to ensure correct next number
            let base = String(ICS_PREVIEW_NEXT || '');
            let updated = base.replace(/\bOFFICE\b|\{OFFICE\}/g, officeDisp);
            // Clean any leftover braces from preview display
            updated = updated.replace(/[{}]/g, '');
            field.readOnly = true;
            field.required = false;
            field.placeholder = '';
            field.value = updated;
        }

        const destSel = document.getElementById('destinationOffice');
        if (destSel) destSel.addEventListener('change', computeIcsPreview);
        computeIcsPreview();

        const ICS_MAX = parseFloat('<?= htmlspecialchars(number_format($ics_max, 2, '.', '')) ?>');
        const tableBody = document.getElementById('ics-items-body');
        const addRowBtn = document.getElementById('addRowBtn');
        const grandTotalField = document.getElementById('grandTotal');

        function updateGrandTotal() {
            let sum = 0;
            document.querySelectorAll('input[name="total_cost[]"]').forEach(input => {
                sum += parseFloat(input.value) || 0;
            });
            grandTotalField.value = sum.toFixed(2);
        }

        // Input handler (quantities, description selection, unit cost -> total)
        tableBody.addEventListener('input', function(event) {
            const target = event.target;
            const row = target.closest('tr');
            if (!row) return;

            const quantityInput = row.querySelector('input[name="quantity[]"]');
            const unitCostInput = row.querySelector('input[name="unit_cost[]"]');
            const totalCostField = row.querySelector('input[name="total_cost[]"]');
            const descriptionInput = row.querySelector('input[name="description[]"]');

            if (target.name === "unit_cost[]") {
                const val = parseFloat(target.value) || 0;
                if (val > ICS_MAX) {
                    target.value = ICS_MAX;
                    const cap = ICS_MAX.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                    target.setCustomValidity("Unit cost cannot exceed ₱" + cap + ".");
                    target.reportValidity();
                } else {
                    target.setCustomValidity("");
                }
            }

            const quantity = parseFloat(quantityInput?.value) || 0;
            const unitCost = parseFloat(unitCostInput?.value) || 0;
            if (totalCostField) totalCostField.value = (quantity * unitCost).toFixed(2);

            if (quantityInput?.max && quantity > parseFloat(quantityInput.max)) {
                quantityInput.setCustomValidity("Quantity exceeds available stock.");
                quantityInput.reportValidity();
            } else {
                quantityInput?.setCustomValidity("");
            }

            updateGrandTotal();
        });

        // Clear (×) button handler - uses event delegation and clears related fields
        tableBody.addEventListener('click', function(event) {
            if (!event.target.classList.contains('clear-description')) return;

            const btn = event.target;
            const row = btn.closest('tr');
            if (!row) return;

            const descriptionInput = row.querySelector('.description-field');
            const unitCostInput = row.querySelector('input[name="unit_cost[]"]');
            const totalCostField = row.querySelector('input[name="total_cost[]"]');
            const quantityInput = row.querySelector('input[name="quantity[]"]');

            if (descriptionInput) descriptionInput.value = '';
            if (unitCostInput) unitCostInput.value = '';
            if (totalCostField) totalCostField.value = '';
            if (quantityInput) {
                quantityInput.value = '';
                quantityInput.removeAttribute('max');
                quantityInput.placeholder = '';
                quantityInput.setCustomValidity("");
            }

            // Trigger input event to recalculate and keep behavior consistent
            if (descriptionInput) descriptionInput.dispatchEvent(new Event('input'));
            updateGrandTotal();

            // Optional: put focus back into the description field
            if (descriptionInput) descriptionInput.focus();
        });

        // Remove row handler - remove the row if more than one exists; otherwise clear it
        tableBody.addEventListener('click', function(event) {
            const removeBtn = event.target.closest('.remove-row');
            if (!removeBtn) return;

            const row = removeBtn.closest('tr');
            if (!row) return;

            const rows = tableBody.querySelectorAll('tr');
            if (rows.length > 1) {
                row.remove();
            } else {
                // Clear inputs/selects for the last remaining row instead of removing
                row.querySelectorAll('input').forEach(el => {
                    if (el.classList.contains('item-no-field')) {
                        el.value = '1'; // Keep item number as 1 for the first row
                    } else {
                        el.value = '';
                    }
                    el.removeAttribute('max');
                    el.placeholder = '';
                    if (typeof el.setCustomValidity === 'function') el.setCustomValidity('');
                });
                row.querySelectorAll('select').forEach(sel => {
                    if (sel.options.length > 0) sel.selectedIndex = 0;
                });
            }

            // Recalculate totals and update item numbers
            updateGrandTotal();
            updateItemNumbers();
        });

        // Add row (clone)
        addRowBtn.addEventListener('click', function() {
            const firstRow = tableBody.querySelector('tr');
            if (!firstRow) return;

            const newRow = firstRow.cloneNode(true);

            // clear values in cloned inputs/selects
            newRow.querySelectorAll('input, select').forEach(el => {
                if (el.tagName.toLowerCase() === 'select') {
                    let set = false;
                    for (let i = 0; i < el.options.length; i++) {
                        if (el.options[i].value.toLowerCase() === 'unit') {
                            el.selectedIndex = i;
                            set = true;
                            break;
                        }
                    }
                    if (!set) el.selectedIndex = 0;
                } else if (el.classList.contains('item-no-field')) {
                    // Keep item number field readonly and it will be updated by updateItemNumbers()
                    el.readOnly = true;
                    el.value = ''; // Will be set by updateItemNumbers()
                } else {
                    el.value = '';
                }
            });

            // specifically ensure total_cost cleared
            newRow.querySelectorAll('input.total_cost').forEach(i => i.value = '');

            // Ensure the Actions cell contains a Remove button for cloned rows
            const actionCell = newRow.querySelector('td:last-child');
            if (actionCell) {
                actionCell.innerHTML = '<button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button>';
            }

            tableBody.appendChild(newRow);
        });

        // initial total calc
        updateGrandTotal();

        // Function to update item numbers sequentially
        function updateItemNumbers() {
            const itemNoFields = tableBody.querySelectorAll('.item-no-field');
            itemNoFields.forEach((field, index) => {
                field.value = index + 1;
            });
        }

        // Initialize item numbers on page load
        updateItemNumbers();

        // Update item numbers when rows are added
        const originalAddRowHandler = addRowBtn.onclick;
        addRowBtn.addEventListener('click', function() {
            // Small delay to ensure row is added first
            setTimeout(() => {
                updateItemNumbers();
            }, 10);
        });

        // Add destination and entity name handler
        const destinationOffice = document.getElementById('destinationOffice');
        const entityNameInput = document.getElementById('entityName');

        function handleDestinationChange() {
            if (!destinationOffice || !entityNameInput) return;
            const val = destinationOffice.value;
            const selectedText = destinationOffice.options[destinationOffice.selectedIndex]?.text?.trim() || '';

            if (val === 'outside_lgu') {
                // Outside LGU: must type an entity name
                entityNameInput.readOnly = false; // Always editable
                entityNameInput.required = true;
                if (!entityNameInput.value.trim()) {
                    entityNameInput.placeholder = 'Enter external entity name';
                } else {
                    entityNameInput.placeholder = '';
                }
                entityNameInput.focus();
            } else if (val) {
                // Internal office selected: prefill if empty, but keep editable
                entityNameInput.value = selectedText;
                entityNameInput.readOnly = false; // Always editable
                entityNameInput.required = false;
                entityNameInput.placeholder = '';
            } else {
                // No selection
                entityNameInput.readOnly = false; // Always editable
                entityNameInput.required = false;
                entityNameInput.placeholder = '';
            }
        }

        if (destinationOffice) {
            destinationOffice.addEventListener('change', () => {
                handleDestinationChange();
                computeIcsPreview();
            });
            handleDestinationChange(); // initialize on load for prefilled states
        }
        // Recompute ICS preview when entity name changes (for Outside LGU)
        if (entityNameInput) {
            entityNameInput.addEventListener('input', computeIcsPreview);
        }
    });
</script>