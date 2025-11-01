<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';

$form_id = $_GET['id'] ?? '';

// Fetch the latest row only
$stmt = $conn->prepare("SELECT * FROM ris_form ORDER BY id DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$ris_data = $result->fetch_assoc() ?? [];
$stmt->close();


// Note: RIS No. should NOT be auto-generated. It will be fetched from the latest record (if any)
// via $ris_data above and shown as an editable field.

?>
<?php
// Fetch active templates for dynamic previews
$ris_template = '';
$sai_template = '';
if ($st1 = $conn->prepare("SELECT format_template FROM tag_formats WHERE tag_type = 'ris_no' AND is_active = 1 LIMIT 1")) {
  $st1->execute();
  $r1 = $st1->get_result();
  if ($r1 && ($row = $r1->fetch_assoc())) { $ris_template = $row['format_template'] ?? ''; }
  $st1->close();
}
if ($st2 = $conn->prepare("SELECT format_template FROM tag_formats WHERE tag_type = 'sai_no' AND is_active = 1 LIMIT 1")) {
  $st2->execute();
  $r2 = $st2->get_result();
  if ($r2 && ($row2 = $r2->fetch_assoc())) { $sai_template = $row2['format_template'] ?? ''; }
  $st2->close();
}
?>
<?php if (isset($_GET['add']) && $_GET['add'] === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <strong>Success!</strong> RIS Form &amp; items saved successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<style>
    body { background: #f4f6f9; }
    .ris-page-wrapper { padding: 2.5rem 0 3.5rem; background: linear-gradient(135deg, rgba(226,232,240,0.5), rgba(148,163,184,0.25)); }
    .ris-paper { position: relative; max-width: 1024px; margin: 0 auto; background: #fff; border: 1px solid #d9dee6; border-radius: 14px; padding: 2.75rem 3rem; box-shadow: 0 18px 45px rgba(15,23,42,0.15); }
    .ris-paper::before { content: ""; position: absolute; inset: 14px; border: 1px solid rgba(148,163,184,0.25); border-radius: 10px; pointer-events: none; }
    .ris-paper .form-control, .ris-paper .form-select { padding: 0.35rem 0.55rem; font-size: 0.85rem; min-height: 2.1rem; border-radius: 6px; }
    .ris-paper .form-control.text-center, .ris-paper .form-control.text-end { padding-right: 0.55rem; padding-left: 0.55rem; }
    .ris-section-title { font-size: 0.82rem; letter-spacing: 0.12em; text-transform: uppercase; color: #6c757d; font-weight: 700; margin-bottom: 0.85rem; }
    .ris-heading-divider { margin: 1.75rem 0; border: none; border-top: 2px solid rgba(100,116,139,0.35); }
    .ris-table-wrapper { border: 1px solid #ced4da; border-radius: 10px; overflow: hidden; background: #ffffff; }
    .ris-table-wrapper table { margin-bottom: 0; font-size: 0.74rem; }
    .ris-table-wrapper thead th { background: #f8fafc; font-size: 0.72rem; vertical-align: middle; padding: 0.45rem 0.35rem; color: #334155; }
    .ris-table-wrapper tbody td, .ris-table-wrapper tfoot td { padding: 0.35rem 0.35rem; }
    .ris-table-wrapper input.form-control, .ris-table-wrapper select.form-select { padding: 0.22rem 0.4rem; min-height: 1.7rem; font-size: 0.72rem; }
    .ris-table-wrapper .btn, .ris-table-wrapper .input-group-text { font-size: 0.72rem; padding: 0.2rem 0.45rem; }
    .ris-signature-table input.form-control { border: none; border-radius: 0; border-bottom: 1px solid #adb5bd; background: transparent; }
    .ris-signature-table input.form-control:focus { box-shadow: none; border-color: #495057; }
    @media (max-width: 991.98px) { .ris-page-wrapper { padding: 1.75rem 1rem 2.5rem; } .ris-paper { padding: 2rem 1.6rem; border-radius: 10px; } .ris-paper::before { inset: 10px; border-radius: 8px; } }
    @media print { body { background: #ffffff !important; } .ris-page-wrapper { padding: 0; background: transparent; } .ris-paper { max-width: 100%; padding: 20mm; border-radius: 0; border: none; box-shadow: none; } .ris-paper::before, .navigation-controls, .alert, .btn { display: none !important; } }
</style>

<div class="ris-page-wrapper">
    <div class="navigation-controls d-flex justify-content-end mb-3">
        <a href="saved_ris.php?id=<?= urlencode($form_id) ?>" class="btn btn-info">
            <i class="bi bi-folder-check"></i> View Saved RIS
        </a>
    </div>

    <div class="ris-paper">
        <form method="POST" action="save_ris.php" enctype="multipart/form-data" class="w-100" onsubmit="return checkDuplicateDescriptions()">
            <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">

            <div class="mb-4 text-center">
                <?php $header_image = $ris_data['header_image'] ?? 'PILAR LOGO TRANSPARENT.png'; ?>
                <img src="../img/<?= htmlspecialchars($header_image) ?>" class="img-fluid mb-2" style="max-width: 100%; height: auto; object-fit: contain;">
                <input type="hidden" name="existing_header_image" value="<?= htmlspecialchars($header_image) ?>">
            </div>

            <hr class="ris-heading-divider">

            <div class="ris-section-title">Requisition &amp; Issue Slip Details</div>
            <div class="row g-4">
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Division</label>
                    <input type="text" class="form-control shadow-sm" id="division" name="division" placeholder="Enter Division" value="<?= htmlspecialchars($ris_data['division'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Responsibility Center</label>
                    <input type="text" class="form-control shadow-sm" id="responsibility_center" name="responsibility_center" placeholder="Enter Responsibility Center" value="<?= htmlspecialchars($ris_data['responsibility_center'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">RIS No. (Auto-generated)</label>
                    <div class="input-group shadow-sm">
                        <input type="text" class="form-control border-0" id="risNoField" name="ris_no" value="<?= previewTag('ris_no') ?>" readonly>
                        <span class="input-group-text bg-light border-0"><i class="bi bi-magic" title="Auto-generated"></i></span>
                    </div>
                    <small class="text-muted">Automatically assigned on save.</small>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date</label>
                    <input type="date" class="form-control shadow-sm" id="risDate" name="date" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Office / Unit <span class="text-danger">*</span></label>
                    <select class="form-select shadow-sm" id="office_id" name="office_id" required>
                        <option value="" disabled selected>Select Office</option>
                        <?php
                        $office_query = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");
                        while ($row = $office_query->fetch_assoc()):
                        ?>
                            <option value="<?= $row['id'] ?>" <?= (isset($ris_data['office_id']) && (string)$ris_data['office_id'] === (string)$row['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['office_name']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Responsibility Code</label>
                    <input type="text" class="form-control shadow-sm" id="responsibility_code" name="responsibility_code" placeholder="Enter Code" value="<?= htmlspecialchars($ris_data['responsibility_code'] ?? '') ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">SAI No. (Auto-generated)</label>
                    <div class="input-group shadow-sm">
                        <input type="text" class="form-control border-0" id="saiNoField" name="sai_no" value="<?= previewTag('sai_no') ?>" readonly>
                        <span class="input-group-text bg-light border-0"><i class="bi bi-magic" title="Auto-generated"></i></span>
                    </div>
                    <small class="text-muted">Automatically assigned on save.</small>
                </div>
            </div>

            <div class="ris-section-title mt-5">Inventory Line Items</div>
            <div class="ris-table-wrapper mb-4">
                <table class="table table-bordered text-center align-middle" id="risItemsTable">
                    <thead>
                        <tr class="table-light text-uppercase small">
                            <th colspan="4" class="fw-semibold">Requisition</th>
                            <th colspan="3" class="fw-semibold">Issuance</th>
                        </tr>
                        <tr>
                            <th>Stock / Property No.</th>
                            <th>Unit</th>
                            <th style="width: 28%;">Description</th>
                            <th>Quantity Requested</th>
                            <th>Unit Price</th>
                            <th>Total Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="ris-items-body">
                        <tr data-row-index="0">
                            <input type="hidden" name="asset_id[]" class="asset-id-field">
                            <td><input type="text" class="form-control shadow-sm stock-field" name="stock_no[]" value="1" readonly></td>
                            <td>
                                <select name="unit[]" class="form-select shadow-sm" required>
                                    <option value="" disabled selected>Select Unit</option>
                                    <?php
                                    $unit_query = $conn->query("SELECT id, unit_name FROM unit ORDER BY unit_name ASC");
                                    while ($row = $unit_query->fetch_assoc()):
                                    ?>
                                        <option value="<?= htmlspecialchars($row['id']) ?>"><?= htmlspecialchars($row['unit_name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </td>
                            <td class="position-relative">
                                <input type="text" class="form-control description-input shadow-sm" name="description[]" autocomplete="off" list="consumableDatalist" placeholder="Type description..." style="padding-right: 2rem;" required>
                                <button type="button" class="clear-description" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: transparent; border: none; font-weight: bold; font-size: 1rem; line-height: 1; color: #888; cursor: pointer;">&times;</button>
                            </td>
                            <td><input type="number" class="form-control text-end shadow-sm quantity-field" name="req_quantity[]" min="1" required></td>
                            <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" step="0.01" class="form-control text-end shadow-sm price-field" name="price[]" style="padding-left: 1.5rem;" required></td>
                            <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="text" class="form-control text-end shadow-sm total-field" name="total[]" style="padding-left: 1.5rem;" readonly></td>
                            <td></td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-semibold">Total Amount:</td>
                            <td colspan="2" class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" id="grandTotal" class="form-control text-end fw-bold shadow-sm" style="padding-left: 1.5rem;" readonly></td>
                            <td class="text-start"><button type="button" id="addRowBtn" class="btn btn-primary btn-sm">+ Add Row</button></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="ris-section-title mb-3">Purpose</div>
            <div class="mb-4">
                <textarea class="form-control shadow-sm" name="purpose" id="purpose" rows="3" placeholder="Describe the purpose for this requisition" required><?= htmlspecialchars($ris_data['reason_for_transfer'] ?? '') ?></textarea>
            </div>

            <div class="ris-section-title mb-3">Signatories</div>
            <table class="table table-borderless ris-signature-table" style="width: 100%; text-align: center;">
                <thead class="text-muted text-uppercase small fw-semibold">
                    <tr>
                        <td style="width: 10%;"></td>
                        <td style="width: 22%; text-align: left;">Requested By</td>
                        <td style="width: 22%; text-align: left;">Approved By</td>
                        <td style="width: 22%; text-align: left;">Issued By</td>
                        <td style="width: 24%; text-align: left;">Received By</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-start"><span class="text-danger">*</span> Printed Name</td>
                        <td class="px-2"><input type="text" class="form-control text-center fw-semibold" name="requested_by_name" value="<?= htmlspecialchars($ris_data['requested_by_name'] ?? '') ?>" required></td>
                        <td class="px-2"><input type="text" class="form-control text-center fw-semibold" name="approved_by_name" value="<?= htmlspecialchars($ris_data['approved_by_name'] ?? '') ?>" required></td>
                        <td class="px-2"><input type="text" class="form-control text-center fw-semibold" name="issued_by_name" value="<?= htmlspecialchars($ris_data['issued_by_name'] ?? '') ?>" required></td>
                        <td class="px-2"><input type="text" class="form-control text-center fw-semibold" name="received_by_name" value="<?= htmlspecialchars($ris_data['received_by_name'] ?? '') ?>" required></td>
                    </tr>
                    <tr>
                        <td class="text-start"><span class="text-danger">*</span> Designation</td>
                        <td class="px-2"><input type="text" class="form-control text-center" name="requested_by_designation" value="<?= htmlspecialchars($ris_data['requested_by_designation'] ?? '') ?>" required></td>
                        <td class="px-2"><input type="text" class="form-control text-center" name="approved_by_designation" value="<?= htmlspecialchars($ris_data['approved_by_designation'] ?? '') ?>" required></td>
                        <td class="px-2"><input type="text" class="form-control text-center" name="issued_by_designation" value="<?= htmlspecialchars($ris_data['issued_by_designation'] ?? '') ?>" required></td>
                        <td class="px-2"><input type="text" class="form-control text-center" name="received_by_designation" value="<?= htmlspecialchars($ris_data['received_by_designation'] ?? '') ?>" required></td>
                    </tr>
                    <tr>
                        <td class="text-start">Date</td>
                        <td class="px-2"><input type="date" class="form-control text-center" name="requested_by_date" value="<?= htmlspecialchars($ris_data['requested_by_date'] ?? '') ?>"></td>
                        <td class="px-2"><input type="date" class="form-control text-center" name="approved_by_date" value="<?= htmlspecialchars($ris_data['approved_by_date'] ?? '') ?>"></td>
                        <td class="px-2"><input type="date" class="form-control text-center" name="issued_by_date" value="<?= htmlspecialchars($ris_data['issued_by_date'] ?? '') ?>"></td>
                        <td class="px-2"><input type="date" class="form-control text-center" name="received_by_date" value="<?= htmlspecialchars($ris_data['received_by_date'] ?? '') ?>"></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="4"><input type="hidden" name="footer_date" value="<?= htmlspecialchars($ris_data['footer_date'] ?? date('Y-m-d')) ?>"></td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <small class="text-muted"><span class="text-danger">*</span> Required fields</small>
                <button type="submit" class="btn btn-primary"><i class="bi bi-send-check-fill"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<?php include 'modals/par_duplicate_modal.php'; ?>
<datalist id="consumableDatalist"></datalist>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const RIS_TEMPLATE = <?= json_encode($ris_template) ?>;
        const SAI_TEMPLATE = <?= json_encode($sai_template) ?>;
        const risNoField = document.getElementById('risNoField');
        const saiNoField = document.getElementById('saiNoField');
        const officeSelect = document.getElementById('office_id');
        const risDateInput = document.getElementById('risDate');
        const risItemsBody = document.getElementById('ris-items-body');
        const addRowBtn = document.getElementById('addRowBtn');
        const grandTotalField = document.getElementById('grandTotal');
        const datalist = document.getElementById('consumableDatalist');
        const duplicateModalElement = document.getElementById('duplicateModal');
        const duplicateModal = duplicateModalElement ? new bootstrap.Modal(duplicateModalElement) : null;
        let rowIndex = risItemsBody ? risItemsBody.querySelectorAll('tr').length : 0;

        function buildOfficeDisplay() {
            if (!officeSelect) return 'OFFICE';
            const val = officeSelect.value;
            const text = officeSelect.options[officeSelect.selectedIndex]?.text?.trim() || '';
            return val ? (text || 'OFFICE') : 'OFFICE';
        }

        function replaceDateTokens(template, dateStr) {
            const d = dateStr ? new Date(dateStr) : new Date();
            const Y = d.getFullYear().toString();
            const M = String(d.getMonth() + 1).padStart(2, '0');
            const D = String(d.getDate()).padStart(2, '0');
            return (template || '')
                .replace(/\{YYYY\}|YYYY/g, Y)
                .replace(/\{YY\}|YY/g, Y.slice(-2))
                .replace(/\{MM\}|MM/g, M)
                .replace(/\{DD\}|DD/g, D)
                .replace(/\{YYYYMM\}|YYYYMM/g, Y + M)
                .replace(/\{YYYYMMDD\}|YYYYMMDD/g, Y + M + D);
        }

        function computePreview(field, template) {
            if (!field) return;
            const officeDisp = buildOfficeDisplay();
            const dateValue = risDateInput?.value || null;
            if (template) {
                let processed = replaceDateTokens(template, dateValue);
                processed = processed.replace(/\{OFFICE\}|OFFICE/g, officeDisp);
                processed = processed.replace(/\{(#+)\}/g, (match, hashes) => '1'.padStart(hashes.length, '0'));
                field.value = processed.replace(/[{}]/g, '');
            } else if (field.value) {
                field.value = field.value.replace(/\bOFFICE\b|\{OFFICE\}/g, officeDisp).replace(/[{}]/g, '');
            }
        }

        function updatePreviews() {
            computePreview(risNoField, RIS_TEMPLATE);
            computePreview(saiNoField, SAI_TEMPLATE);
        }

        function updateGrandTotal() {
            if (!risItemsBody || !grandTotalField) return;
            let sum = 0;
            risItemsBody.querySelectorAll('.total-field').forEach(input => sum += parseFloat(input.value) || 0);
            grandTotalField.value = sum.toFixed(2);
        }

        function updateStockNumbers() {
            if (!risItemsBody) return;
            risItemsBody.querySelectorAll('.stock-field').forEach((input, idx) => {
                input.value = idx + 1;
            });
        }

        function handleDescriptionSelection(input) {
            const value = input.value;
            if (!value || !datalist) return;
            const option = Array.from(datalist.options).find(opt => opt.value === value);
            if (!option) return;
            const row = input.closest('tr');
            const unitSelect = row.querySelector('select[name="unit[]"]');
            const priceInput = row.querySelector('.price-field');
            const assetIdInput = row.querySelector('.asset-id-field');

            if (option.dataset.unit && unitSelect) {
                const matchingOption = Array.from(unitSelect.options).find(opt => opt.text.trim() === option.dataset.unit);
                if (matchingOption) unitSelect.value = matchingOption.value;
            }
            if (option.dataset.value && priceInput) {
                priceInput.value = parseFloat(option.dataset.value).toFixed(2);
            }
            if (option.dataset.id && assetIdInput) {
                assetIdInput.value = option.dataset.id;
            }
            computeRowTotal(row);
        }

        function computeRowTotal(row) {
            if (!row) return;
            const qtyField = row.querySelector('.quantity-field');
            const priceField = row.querySelector('.price-field');
            const totalField = row.querySelector('.total-field');
            const qty = parseFloat(qtyField?.value) || 0;
            const price = parseFloat(priceField?.value) || 0;
            if (totalField) totalField.value = (qty * price).toFixed(2);
            updateGrandTotal();
        }

        function clearRow(row) {
            if (!row) return;
            row.querySelectorAll('input, select').forEach(input => {
                if (input.classList.contains('stock-field')) return;
                if (input.tagName.toLowerCase() === 'select') {
                    input.value = '';
                } else if (!input.readOnly || input.classList.contains('total-field')) {
                    input.value = '';
                }
                if (input.name === 'asset_id[]') input.value = '';
            });
            computeRowTotal(row);
        }

        function removeRow(row) {
            if (!row || !risItemsBody) return;
            const rows = risItemsBody.querySelectorAll('tr');
            if (rows.length === 1) {
                clearRow(row);
                return;
            }
            row.remove();
            updateStockNumbers();
            updateGrandTotal();
        }

        function createRow(index) {
            const wrapper = document.createElement('tbody');
            const unitSelect = risItemsBody.querySelector('select[name="unit[]"]');
            const unitOptions = unitSelect ? unitSelect.innerHTML : '<option value="" disabled selected>Select Unit</option>';
            wrapper.innerHTML = `
                <tr data-row-index="${index}">
                    <input type="hidden" name="asset_id[]" class="asset-id-field">
                    <td><input type="text" class="form-control shadow-sm stock-field" name="stock_no[]" readonly></td>
                    <td>
                        <select name="unit[]" class="form-select shadow-sm" required>
                            ${unitOptions}
                        </select>
                    </td>
                    <td class="position-relative">
                        <input type="text" class="form-control description-input shadow-sm" name="description[]" autocomplete="off" list="consumableDatalist" placeholder="Type description..." style="padding-right: 2rem;" required>
                        <button type="button" class="clear-description" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: transparent; border: none; font-weight: bold; font-size: 1rem; line-height: 1; color: #888; cursor: pointer;">&times;</button>
                    </td>
                    <td><input type="number" class="form-control text-end shadow-sm quantity-field" name="req_quantity[]" min="1" required></td>
                    <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" step="0.01" class="form-control text-end shadow-sm price-field" name="price[]" style="padding-left: 1.5rem;" required></td>
                    <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="text" class="form-control text-end shadow-sm total-field" name="total[]" style="padding-left: 1.5rem;" readonly></td>
                    <td><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></td>
                </tr>`;
            return wrapper.firstElementChild;
        }

        function loadConsumablesForOffice() {
            if (!officeSelect || !datalist) return;
            const officeId = officeSelect.value;
            datalist.innerHTML = '';
            if (!officeId) return;
            fetch(`get_consumables_by_office.php?office_id=${officeId}`)
                .then(res => res.json())
                .then(data => {
                    (data || []).forEach(item => {
                        const option = document.createElement('option');
                        option.value = item.description;
                        option.dataset.id = item.id;
                        option.dataset.quantity = item.quantity;
                        option.dataset.value = item.value;
                        option.dataset.unit = item.unit;
                        datalist.appendChild(option);
                    });
                })
                .catch(() => { datalist.innerHTML = ''; });
        }

        function checkDuplicateDescriptions() {
            if (!risItemsBody) return true;
            const descriptions = risItemsBody.querySelectorAll('.description-input');
            const seen = new Set();
            for (const input of descriptions) {
                const value = input.value.trim().toLowerCase();
                if (!value) continue;
                if (seen.has(value)) {
                    if (duplicateModal) duplicateModal.show();
                    return false;
                }
                seen.add(value);
            }
            return true;
        }

        window.checkDuplicateDescriptions = checkDuplicateDescriptions;

        if (officeSelect) {
            officeSelect.addEventListener('change', () => {
                loadConsumablesForOffice();
                updatePreviews();
            });
        }

        if (risDateInput) risDateInput.addEventListener('change', updatePreviews);

        if (risItemsBody) {
            risItemsBody.addEventListener('input', event => {
                if (event.target.matches('.quantity-field') || event.target.matches('.price-field')) {
                    computeRowTotal(event.target.closest('tr'));
                } else if (event.target.classList.contains('description-input')) {
                    handleDescriptionSelection(event.target);
                }
            });

            risItemsBody.addEventListener('click', event => {
                if (event.target.closest('.clear-description')) {
                    clearRow(event.target.closest('tr'));
                } else if (event.target.closest('.remove-row')) {
                    removeRow(event.target.closest('tr'));
                }
            });
        }

        if (addRowBtn) {
            addRowBtn.addEventListener('click', () => {
                const newRow = createRow(rowIndex);
                risItemsBody.appendChild(newRow);
                rowIndex++;
                updateStockNumbers();
            });
        }

        updatePreviews();
        updateGrandTotal();
        loadConsumablesForOffice();
    });
</script>