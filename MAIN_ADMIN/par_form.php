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
?>

<div class="d-flex justify-content-end mb-3">
    <a href="saved_par.php?id=<?= htmlspecialchars($form_id) ?>" class="btn btn-info">
        <i class="bi bi-folder-check"></i> View Saved PAR
    </a>
</div>
<div class="container mt-3">
    <?php if (!empty($_SESSION['flash'])): ?>
        <?php
        $flash = $_SESSION['flash'];
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


    <form method="post" action="save_par_form.php" enctype="multipart/form-data" onsubmit="return checkDuplicates()">
        <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">


        <div class="mb-3 text-center">
            <?php if (!empty($par_data['header_image'])): ?>
                <img src="../img/<?= htmlspecialchars($par_data['header_image']) ?>"
                     class="img-fluid mb-3"
                     style="max-width: 100%; height: auto; object-fit: contain;">
                <!-- ✅ Hidden input so header_image is included when saving -->
                <input type="hidden" name="header_image" value="<?= htmlspecialchars($par_data['header_image']) ?>">
            <?php endif; ?>
        </div>


        <table class="table table-bordered align-middle text-start" style="table-layout: fixed;">
            <tbody>
                <!-- Office/Location Row -->
                <tr>
                    <td colspan="2">
                        <div class="row">
                            <div class="col-md-3"></div>
                            <div class="col-md-6 text-center">
                                <label class="form-label fw-semibold mb-0">Office/Location</label>
                                <select name="office_id" class="form-select text-center shadow" required>
                                    <option value="">Select Office</option>
                                    <option value="outside_lgu">Outside LGU</option>
                                    <?php foreach ($offices as $office): ?>
                                        <option value="<?= htmlspecialchars($office['id']) ?>"
                                            <?= ($office['id'] == $par_data['office_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($office['office_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3"></div>
                        </div>
                    </td>
                </tr>

                <!-- Entity Name and Blank -->
                <tr>
                    <td>
                        <label class="form-label fw-semibold mb-0">Entity Name <span style="color: red;">*</span></label>
                        <input type="text" name="entity_name" class="form-control shadow" required>
                    </td>
                    <td>
                        <!-- Blank right cell -->
                    </td>
                </tr>

                <!-- Fund Cluster and PAR No. -->
                <tr>
                    <td>
                        <label class="form-label fw-semibold mb-0">Fund Cluster <span style="color: red;">*</span></label>
                        <input type="text" name="fund_cluster" class="form-control shadow" required>
                    </td>
                    <td>
                        <label class="form-label fw-semibold mb-0">PAR No. (Auto-generated)</label>
                        <div class="input-group">
                            <input type="text" name="par_no" class="form-control shadow" value="<?= previewTag('par_no') ?>" readonly>
                            <span class="input-group-text">
                                <i class="bi bi-magic" title="Auto-generated"></i>
                            </span>
                        </div>
                        <small class="text-muted">This number will be automatically assigned when you save the form.</small>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- ITEM TABLE -->
        <table class="table align-middle text-center mt-4" style="table-layout: fixed; ">
            <thead>
                <tr>
                    <th>QUANTITY</th>
                    <th>UNIT</th>
                    <th style="width: 30%;">DESCRIPTION</th>
                    <th>PROPERTY NO</th>
                    <th>DATE ACQUIRED</th>
                    <th>UNIT PRICE</th>
                    <th>AMOUNT</th>
                    <th><!-- Remove column header --></th>
                </tr>
            </thead>
            <tbody id="itemTableBody">
                <?php for ($i = 0; $i < 1; $i++): ?>
                    <tr>
                        <td><input type="number" name="items[<?= $i ?>][quantity]" class="form-control text-end shadow" id="qtyInput<?= $i ?>" min="1" required></td>
                        <td>
                            <select name="items[<?= $i ?>][unit]" class="form-select text-center shadow">
                                <option value="">Select Unit</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= htmlspecialchars($unit['unit_name']) ?>" <?= (strtolower($unit['unit_name']) === 'unit') ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($unit['unit_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="position-relative" style="width: 30%;">
                            <div class="input-group">
                                <input type="text" name="items[<?= $i ?>][description]" class="form-control form-control-lg shadow" id="descInput<?= $i ?>" placeholder="Type description..." required>
                                <input type="hidden" name="items[<?= $i ?>][asset_id]" id="assetId<?= $i ?>">
                                <button type="button" class="btn p-0 m-0 border-0 bg-transparent" onclick="clearDescription(<?= $i ?>)" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                    <span class="badge rounded-circle bg-secondary text-white" style="font-size: 0.75rem;">×</span>
                                </button>
                            </div>
                        </td>
                        <td><input type="text" name="items[<?= $i ?>][property_no]" class="form-control shadow" required></td>
                        <td><input type="date" name="items[<?= $i ?>][date_acquired]" class="form-control shadow" id="acqDate<?= $i ?>" required></td>
                        <td style="position: relative;">
                            <span style="
                                    position: absolute;
                                    top: 50%;
                                    left: 10px;
                                    transform: translateY(-50%);
                                    pointer-events: none;
                                    color: inherit;
                                    font-size: 1rem;
                                ">₱</span>
                            <input
                                type="number"
                                name="items[<?= $i ?>][unit_price]"
                                class="form-control text-end shadow"
                                step="0.01"
                                id="unitCost<?= $i ?>"
                                style="padding-left: 1.5rem;" required>
                        </td>
                        <td style="position: relative;">
                            <span style="
                                    position: absolute;
                                    top: 50%;
                                    left: 10px;
                                    transform: translateY(-50%);
                                    pointer-events: none;
                                    color: inherit;
                                    font-size: 1rem;
                                ">₱</span>
                            <input
                                type="number"
                                name="items[<?= $i ?>][amount]"
                                class="form-control text-end shadow"
                                step="0.01"
                                id="amount<?= $i ?>"
                                readonly
                                style="padding-left: 1.5rem;" required>
                        </td>
                        <td><!-- No remove button for first row --></td>
                    </tr>
                <?php endfor; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" class="text-end fw-bold">Total:</td>
                    <td style="position: relative;">
                        <span style="
                                position: absolute;
                                top: 50%;
                                left: 10px;
                                transform: translateY(-50%);
                                pointer-events: none;
                                color: inherit;
                                font-size: 1rem;
                            ">₱</span>
                        <input
                            type="text"
                            id="totalAmount"
                            class="form-control text-end fw-bold shadow"
                            readonly
                            style="padding-left: 1.5rem; width: 150px;">
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
        <button type="button" class="btn btn-outline-primary mt-2" onclick="addRow()">+ Add Item</button>

        <hr class="mt-5 mb-4">

        <div class="row mt-4 mb-3">
            <!-- Left: Received by -->
            <div class="col-md-6 text-center">
                <p class="fw-semibold">Received by: <span style="color: red;">*</span></p>
                <input type="text" class="form-control text-center fw-semibold shadow" name="received_by_name"
                    placeholder="Signature over Printed Name" required>
                <small class="text-muted">Signature over Printed Name – Received By</small>

                <div class="mt-3">
                    <label for="position_office_left">Position / Office: <span style="color: red;">*</span></label>
                    <input type="text" class="form-control text-center shadow" name="position_office_left" placeholder="Enter Position/Office" required>
                </div>
                <div class="mt-3">
                    <label>Date:</label>
                    <input type="date" name="date_received_left"
                        class="form-control shadow">
                </div>
            </div>

            <!-- Right: Issued by -->
            <div class="col-md-6 text-center">
                <p class="fw-semibold">Issued by: <span style="color: red;">*</span></p>
                <input type="text" class="form-control text-center fw-semibold shadow" name="issued_by_name"
                    placeholder="Signature over Printed Name"
                    value="<?= htmlspecialchars($par_data['issued_by_name'] ?? '') ?>">
                <small class="text-muted">Signature over Printed Name – Issued By</small>

                <div class="mt-3">
                    <label for="position_office_right">Position / Office: <span style="color: red;">*</span></label>
                    <input type="text" class="form-control text-center shadow" name="position_office_right"
                        value="<?= htmlspecialchars($par_data['position_office_right'] ?? '') ?>">
                </div>
                <div class="mt-3">
                    <label>Date:</label>
                    <input type="date" name="date_received_right"
                        class="form-control shadow">
                </div>
            </div>
        </div>

        <small class="text-muted"><span style="color: red;">*</span> Required Fields</small>

        <button type="submit" class="btn btn-primary"><i class="bi bi-send-check-fill"></i>Save</button>
    </form>
</div>

<?php include 'modals/par_duplicate_modal.php' ?>
<script>
    let rowIndex = 1; // Start after the initial 1 row
    let selectedDescriptions = new Set(); // Track selected asset descriptions

    function addRow() {
        const tbody = document.getElementById('itemTableBody');
        const newRow = document.createElement('tr');

        newRow.innerHTML = `
        <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control text-end shadow" id="qtyInput${rowIndex}" min="1" required></td>
        <td>
            <select name="items[${rowIndex}][unit]" class="form-select text-center shadow" required>
                <option value="">Select Unit</option>
                <?php foreach ($units as $unit): ?>
                    <option value="<?= htmlspecialchars($unit['unit_name']) ?>" <?= (strtolower($unit['unit_name']) === 'unit') ? 'selected' : '' ?>>
                        <?= htmlspecialchars($unit['unit_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="position-relative">
            <div class="input-group">
                <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-lg shadow" id="descInput${rowIndex}" placeholder="Type description..." required>
                <input type="hidden" name="items[${rowIndex}][asset_id]" id="assetId${rowIndex}">
                <button type="button" class="btn p-0 m-0 border-0 bg-transparent" onclick="clearDescription(${rowIndex})" style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                    <span class="badge rounded-circle bg-secondary text-white" style="font-size: 0.75rem;">×</span>
                </button>
            </div>
        </td>
        <td><input type="text" name="items[${rowIndex}][property_no]" class="form-control shadow" required></td>
        <td><input type="date" name="items[${rowIndex}][date_acquired]" class="form-control shadow" id="acqDate${rowIndex}" required></td>
        <td style="position: relative; width: 150px;">
            <span style="
                position: absolute;
                top: 50%;
                left: 10px;
                transform: translateY(-50%);
                pointer-events: none;
                color: inherit;
                font-size: 1rem;">₱</span>
            <input
                type="number"
                name="items[${rowIndex}][unit_price]"
                class="form-control text-end shadow"
                step="0.01"
                id="unitCost${rowIndex}"
                style="padding-left: 1.5rem; width: 100%;" required>
        </td>
        <td style="position: relative; width: 150px;">
            <span style="
                position: absolute;
                top: 50%;
                left: 10px;
                transform: translateY(-50%);
                pointer-events: none;
                color: inherit;
                font-size: 1rem;">₱</span>
            <input
                type="number"
                name="items[${rowIndex}][amount]"
                class="form-control text-end fw-semibold shadow"
                step="0.01"
                id="amount${rowIndex}"
                readonly
                style="padding-left: 1.5rem; width: 100%;" required>
        </td>
        <td>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">Remove</button>
        </td>
        `;

        tbody.appendChild(newRow);

        attachRowEvents(rowIndex);
        updateTotalAmount();
        rowIndex++;
    }

    function attachRowEvents(i) {
        const descInput = document.getElementById('descInput' + i);
        const unitCostInput = document.getElementById('unitCost' + i);
        const qtyInput = document.getElementById('qtyInput' + i);
        const acqDateInput = document.getElementById('acqDate' + i);
        const amountInput = document.getElementById('amount' + i);
        const assetIdInput = document.getElementById('assetId' + i);

        // Auto-calculate amount
        qtyInput.addEventListener('input', calculateAmount);
        unitCostInput.addEventListener('input', calculateAmount);

        function calculateAmount() {
            const qty = parseFloat(qtyInput.value) || 0;
            const unitPrice = parseFloat(unitCostInput.value) || 0;
            const total = qty * unitPrice;
            amountInput.value = total.toFixed(2);
            updateTotalAmount();
        }

        descInput.addEventListener('change', function() {
            const val = descInput.value.trim();
            assetIdInput.value = ""; // reset first

            // Check all other description inputs for duplicates (by id prefix)
            const descInputs = document.querySelectorAll('[id^="descInput"]');
            let duplicateFound = false;
            descInputs.forEach((input) => {
                if (input !== descInput && input.value.trim() === val && val !== '') {
                    duplicateFound = true;
                }
            });
            if (duplicateFound) {
                showDuplicateModal();
                descInput.value = '';
                return;
            }
            updateTotalAmount();
        });
    }

    // Attach to initial rows
    for (let i = 0; i < rowIndex; i++) {
        const desc = document.getElementById('descInput' + i)?.value;
        if (desc) selectedDescriptions.add(desc); // Track existing selections
        attachRowEvents(i);
    }

    function updateTotalAmount() {
        let total = 0;
        for (let i = 0; i < rowIndex; i++) {
            const amountInput = document.getElementById('amount' + i);
            if (amountInput && amountInput.value) {
                total += parseFloat(amountInput.value) || 0;
            }
        }
        document.getElementById('totalAmount').value = total.toFixed(2);
    }

    // Remove row except first
    function removeRow(button) {
        const row = button.closest('tr');
        if (row) {
            row.remove();
            updateTotalAmount();
        }
    }

    // Final duplicate check on form submission
    function checkDuplicates() {
        const descInputs = document.querySelectorAll('[id^="descInput"]');
        const seen = new Set();

        for (let input of descInputs) {
            const val = input.value.trim();
            if (val !== '') {
                if (seen.has(val)) {
                    alert("Duplicate asset descriptions found.");
                    return false;
                }
                seen.add(val);
            }
        }
        return true;
    }

    function showDuplicateModal() {
        const duplicateModal = new bootstrap.Modal(document.getElementById('duplicateModal'));
        duplicateModal.show();
    }

    function clearDescription(index) {
        const descInput = document.getElementById('descInput' + index);
        const value = descInput.value.trim();

        if (value && selectedDescriptions.has(value)) {
            selectedDescriptions.delete(value);
        }

        descInput.value = '';

        // Also clear related fields
        const qtyInput = document.getElementById('qtyInput' + index);
        const unitCostInput = document.getElementById('unitCost' + index);
        const acqDateInput = document.getElementById('acqDate' + index);
        const amountInput = document.getElementById('amount' + index);

        if (qtyInput) qtyInput.value = '';
        if (unitCostInput) unitCostInput.value = '';
        if (acqDateInput) acqDateInput.value = '';
        if (amountInput) amountInput.value = '';

        updateTotalAmount();
    }
</script>
