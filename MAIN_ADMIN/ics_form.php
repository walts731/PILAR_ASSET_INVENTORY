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
    if ($rs_fmt && ($r = $rs_fmt->fetch_assoc())) { $ics_template = $r['format_template'] ?? ''; }
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

?>
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
<!-- Top-right button -->
<div class="d-flex justify-content-end mb-3">
    <a href="saved_ics.php?id=<?= htmlspecialchars($form_id) ?>" class="btn btn-info">
        <i class="bi bi-folder-check"></i> View Saved ICS
    </a>
</div>
<div class="card mt-4">
    <div class="card-body">
        <!-- Inventory Custodian Slip Heading -->

        <form method="post" action="save_ics_items.php" enctype="multipart/form-data">
            <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">
            <!-- ICS Form ID from database -->
            <input type="hidden" name="ics_id" value="<?= htmlspecialchars($ics_data['id'] ?? '') ?>">
            <div class="mb-3 text-center">
                <?php if (!empty($ics_data['header_image'])): ?>
                    <img src="../img/<?= htmlspecialchars($ics_data['header_image']) ?>"
                         class="img-fluid mb-2"
                         style="max-width: 100%; height: auto; object-fit: contain;">

                    <!-- Hidden input ensures it gets submitted -->
                    <input type="hidden" name="header_image" value="<?= htmlspecialchars($ics_data['header_image']) ?>">
                <?php else: ?>
                    <p class="text-muted">No header image available</p>
                <?php endif; ?>
                <!-- Hidden file input to keep field available without visible UI -->
                <input type="file" id="headerImageFile" name="header_image_file" accept="image/*" style="display:none;" hidden>
            </div>

            <div class="row mb-3">
                <!-- ENTITY NAME -->
                <div class="col-6">
                    <label class="form-label fw-semibold">ENTITY NAME</label>
                    <input type="text" class="form-control shadow" name="entity_name" id="entityName">
                </div>

                <!-- OFFICE -->
                <div class="col-6">
                    <label class="form-label fw-semibold">
                        DESTINATION <span style="color: red;">*</span>
                    </label>
                    <select class="form-select shadow" name="office_id" id="destinationOffice" required>
                        <option value="" disabled selected>Select office</option>
                        <option value="outside_lgu">Outside LGU</option>
                        <?php foreach ($office_options as $office): ?>
                            <option value="<?= htmlspecialchars($office['id']) ?>">
                                <?= htmlspecialchars($office['office_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row">
                <!-- FUND CLUSTER -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">FUND CLUSTER <span style="color: red;">*</span></label>
                    <input type="text" class="form-control shadow" name="fund_cluster" required>
                </div>

                <!-- ICS NO -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ICS NO. (Auto-generated)</label>
                    <div class="input-group">
                        <input type="text" class="form-control shadow" id="icsNoField" name="ics_no" value="<?= previewTag('ics_no') ?>" readonly>
                        <span class="input-group-text">
                            <i class="bi bi-magic" title="Auto-generated"></i>
                        </span>
                    </div>
                    <small class="text-muted">This number will be automatically assigned when you save the form.</small>
                </div>
            </div>

            <!-- Items Table -->
            <table class="table table-bordered text-center align-middle mt-3" id="icsTable">
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
                            <td><input type="number" class="form-control quantity-field shadow" name="quantity[]" min="1" required></td>
                            <td>
                                <select class="form-select shadow" name="unit[]" required>
                                    <option value="" disabled>Select unit</option>
                                    <?php foreach ($unit_options as $unit): ?>
                                        <option value="<?= htmlspecialchars($unit) ?>" <?= (strtolower($unit) === 'unit') ? 'selected' : '' ?>><?= htmlspecialchars($unit) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="position: relative;">
                                <span style="
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    pointer-events: none;
    color: inherit;
    font-size: 1rem;">₱</span>
                                <input type="number" class="form-control text-end shadow" step="0.01" name="unit_cost[]" max="<?= htmlspecialchars(number_format($ics_max, 2, '.', '')) ?>" style="padding-left: 1.5rem;" required>
                            </td>
                            <td style="position: relative;">
                                <span style="
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    pointer-events: none;
    color: inherit;
    font-size: 1rem;">₱</span>
                                <input type="number" class="form-control total_cost text-end shadow" name="total_cost[]" step="0.01" readonly style="padding-left: 1.5rem;">
                            </td>

                            <td style="position: relative;">
                                <input type="text" class="form-control description-field shadow"
                                    name="description[]"
                                    placeholder="Type description..."
                                    style="padding-right: 2rem;" required>
                                <button type="button"
                                    class="clear-description"
                                    style="
        position: absolute;
        right: 5px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        font-weight: bold;
        font-size: 1rem;
        line-height: 1;
        color: #888;
        cursor: pointer;
    ">&times;</button>
                            </td>

                            <td><input type="text" class="form-control shadow item-no-field" name="item_no[]" value="1" readonly required></td>
                            <td><input type="text" class="form-control shadow" name="estimated_useful_life[]" required></td>
                            <td><!-- No remove button for first row --></td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end fw-bold"></td>
                        <td style="position: relative;">
                            <span style="
    position: absolute;
    top: 50%;
    left: 10px;
    transform: translateY(-50%);
    pointer-events: none;
    color: inherit;
    font-size: 1rem;">₱</span>
                            <input type="number" id="grandTotal" class="form-control fw-bold text-end shadow" readonly style="padding-left: 1.5rem;">
                        </td>

                        <td colspan="3" class="text-start">
                            <button type="button" id="addRowBtn" class="btn btn-primary btn-sm">+ Add Row</button>
                        </td>
                    </tr>
                </tfoot>
            </table>

            <!-- Footer Section -->
            <table class="table table-borderless mt-5" style="width:100%; text-align:center; border-collapse: collapse;">
                <tr>
                    <td style="width:50%; text-align:left; font-weight:bold;">Received from:</td>
                    <td style="width:50%; text-align:left; font-weight:bold;">Received by:</td>
                </tr>
                <tr>
                    <td style="text-align:center;">
                        <input type="text" name="received_from_name"
                            class="form-control text-center fw-bold shadow"
                            value="<?= htmlspecialchars($ics_data['received_from_name']) ?>"
                            placeholder="Enter name"
                            style="text-decoration:underline;">
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="received_by_name"
                            class="form-control text-center fw-bold shadow"
                            value="<?= htmlspecialchars($ics_data['received_by_name']) ?>"
                            placeholder="Enter name"
                            style="text-decoration:underline;">
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center;">
                        <input type="text" name="received_from_position"
                            class="form-control text-center shadow"
                            value="<?= htmlspecialchars($ics_data['received_from_position']) ?>"
                            placeholder="Enter position">
                    </td>
                    <td style="text-align:center;">
                        <input type="text" name="received_by_position"
                            class="form-control text-center shadow"
                            value="<?= htmlspecialchars($ics_data['received_by_position']) ?>"
                            placeholder="Enter position">
                    </td>
                </tr>
                <tr>
                    <td style="height:30px;"></td>
                    <td></td>
                </tr>
                <tr>
                    <td style="text-align:center;">
                        <input type="date" name="received_from_date"
                            class="form-control text-center shadow"
                            value="">
                    </td>
                    <td style="text-align:center;">
                        <input type="date" name="received_by_date"
                            class="form-control text-center shadow"
                            value="">
                    </td>
                </tr>
            </table>

            <button type="submit" class="btn btn-primary mt-3"><i class="bi bi-send-check-fill"></i>Save</button>
        </form>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dynamic ICS No preview
        const ICS_TEMPLATE = <?= json_encode($ics_template) ?>;
        function deriveOfficeAcronym(name) {
            if (!name) return 'OFFICE';
            const parts = String(name).trim().toUpperCase().split(/\s+/);
            let ac = parts.map(p => (p[0] || '').replace(/[^A-Z0-9]/g,'')).join('');
            if (!ac) ac = String(name).replace(/[^A-Z0-9]/g,'').toUpperCase();
            return ac || 'OFFICE';
        }
        function replaceDatePlaceholdersLocal(tpl){
            const now = new Date();
            const Y = now.getFullYear().toString();
            const M = String(now.getMonth()+1).padStart(2,'0');
            const D = String(now.getDate()).padStart(2,'0');
            return tpl
                .replace(/\{YYYY\}|YYYY/g, Y)
                .replace(/\{YY\}|YY/g, Y.slice(-2))
                .replace(/\{MM\}|MM/g, M)
                .replace(/\{DD\}|DD/g, D)
                .replace(/\{YYYYMM\}|YYYYMM/g, Y+M)
                .replace(/\{YYYYMMDD\}|YYYYMMDD/g, Y+M+D);
        }
        function padDigitsForPreview(tpl){
            return tpl.replace(/\{(#+)\}/g, (m, hashes)=>{
                const w = hashes.length; return '0'.repeat(Math.max(0,w-1)) + '1';
            });
        }
        function computeIcsPreview(){
            const field = document.getElementById('icsNoField');
            if (!field) return;
            // Determine OFFICE display
            const sel = document.getElementById('destinationOffice');
            let officeDisp = 'OFFICE';
            if (sel) {
                const opt = sel.options[sel.selectedIndex];
                const txt = opt ? (opt.text || '') : '';
                if (sel.value && sel.value !== 'outside_lgu') {
                    officeDisp = (txt || '').trim() || 'OFFICE';
                } else {
                    const en = document.getElementById('entityName');
                    officeDisp = (en && en.value.trim()) ? en.value.trim() : 'OFFICE';
                }
            }
            // Preserve digits; only update OFFICE tokens in current value
            const current = field.value || '';
            const updated = current.replace(/\bOFFICE\b|\{OFFICE\}/g, officeDisp);
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
                    const cap = ICS_MAX.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
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
                entityNameInput.value = '';
                entityNameInput.readOnly = false;
                entityNameInput.required = true;
                entityNameInput.placeholder = 'Enter external entity name';
                entityNameInput.focus();
            } else if (val) {
                entityNameInput.value = selectedText;
                entityNameInput.readOnly = true;
                entityNameInput.required = false;
                entityNameInput.placeholder = '';
            } else {
                entityNameInput.readOnly = false;
                entityNameInput.required = false;
                entityNameInput.placeholder = '';
            }
        }

        if (destinationOffice) {
            destinationOffice.addEventListener('change', handleDestinationChange);
            handleDestinationChange(); // initialize on load for prefilled states
        }
        // Recompute ICS preview when entity name changes (for Outside LGU)
        if (entityNameInput) {
            entityNameInput.addEventListener('input', computeIcsPreview);
        }
    });
</script>
