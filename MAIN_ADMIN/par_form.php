<?php
require_once '../connect.php';

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

// ✅ Always fetch the latest record
$latest = $conn->query("SELECT * FROM par_form ORDER BY id DESC LIMIT 1");
if ($latest && $latest->num_rows > 0) {
    $par_data = $latest->fetch_assoc();

    // Always auto-generate a new PAR number
    if (preg_match('/PAR-(\d+)/', $par_data['par_no'], $matches)) {
        $nextNum = str_pad(((int)$matches[1] + 1), 4, '0', STR_PAD_LEFT);
        $par_data['par_no'] = "PAR-" . $nextNum;
    } else {
        $par_data['par_no'] = "PAR-0001";
    }
} else {
    // No previous record → start fresh
    $par_data['par_no'] = "PAR-0001";
}

// Fetch offices for dropdown
$offices = [];
$office_query = $conn->query("SELECT id, office_name FROM offices");
while ($row = $office_query->fetch_assoc()) {
    $offices[] = $row;
}

// Fetch description + unit cost + quantity from assets 
// (only type = 'asset', quantity > 0, and value >= 50,000)
$description_details = [];
$result = $conn->query("
    SELECT a.id, a.description, a.value AS unit_cost, a.quantity, a.acquisition_date, 
           a.unit, a.property_no, o.office_name
    FROM assets a
    LEFT JOIN offices o ON a.office_id = o.id
    WHERE a.type = 'asset' AND a.quantity > 0 AND a.value >= 50000
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


<div class="container mt-3">
    <?php
    if (isset($_GET['success']) && $_GET['success'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
            PAR Form has been successfully saved!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form method="post" action="save_par_form.php" enctype="multipart/form-data">
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
                                <select name="office_id" class="form-select text-center" required>
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
                        <label class="form-label fw-semibold mb-0">Entity Name</label>
                        <input type="text" name="entity_name" class="form-control"
                            value="<?= htmlspecialchars($par_data['entity_name']) ?>" required>
                    </td>
                    <td>
                        <!-- Blank right cell -->
                    </td>
                </tr>

                <!-- Fund Cluster and PAR No. -->
                <tr>
                    <td>
                        <label class="form-label fw-semibold mb-0">Fund Cluster</label>
                        <input type="text" name="fund_cluster" class="form-control"
                            value="<?= htmlspecialchars($par_data['fund_cluster']) ?>" required>
                    </td>
                    <td>
                        <label class="form-label fw-semibold mb-0">PAR No.</label>
                        <input type="text" name="par_no" class="form-control"
                            value="<?= htmlspecialchars($par_data['par_no']) ?>" readonly>

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
                </tr>
            </thead>
            <tbody id="itemTableBody">
                <?php for ($i = 0; $i < 5; $i++): ?>
                    <tr>
                        <td><input type="number" name="items[<?= $i ?>][quantity]" class="form-control text-end" id="qtyInput<?= $i ?>" min="1"></td>
                        <td>
                            <select name="items[<?= $i ?>][unit]" class="form-select text-center">
                                <option value="">Select Unit</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?= htmlspecialchars($unit['unit_name']) ?>"><?= htmlspecialchars($unit['unit_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="position-relative" style="width: 30%;">
                            <div class="input-group">
                                <input type="text" name="items[<?= $i ?>][description]" class="form-control form-control-lg" list="descriptionList" id="descInput<?= $i ?>">
                                <input type="hidden" name="items[<?= $i ?>][asset_id]" id="assetId<?= $i ?>">
                                <button type="button"
                                    class="btn p-0 m-0 border-0 bg-transparent"
                                    onclick="clearDescription(<?= $i ?>)"
                                    style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
                                    <span class="badge rounded-circle bg-secondary text-white" style="font-size: 0.75rem;">×</span>
                                </button>
                            </div>
                        </td>

                        <td><input type="text" name="items[<?= $i ?>][property_no]" class="form-control"></td>
                        <td><input type="date" name="items[<?= $i ?>][date_acquired]" class="form-control" id="acqDate<?= $i ?>"></td>
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
                                class="form-control text-end"
                                step="0.01"
                                id="unitCost<?= $i ?>"
                                style="padding-left: 1.5rem;">
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
                                class="form-control text-end"
                                step="0.01"
                                id="amount<?= $i ?>"
                                readonly
                                style="padding-left: 1.5rem;">
                        </td>

                        <datalist id="descriptionList">
                            <?php foreach ($description_details as $desc => $details): ?>
                                <option
                                    value="<?= htmlspecialchars($desc) ?>"
                                    label="<?= htmlspecialchars($details['office_name']) ?>"
                                    data-asset-id="<?= htmlspecialchars($details['id']) ?>"
                                    data-unit-cost="<?= htmlspecialchars($details['unit_cost']) ?>"
                                    data-quantity="<?= htmlspecialchars($details['quantity']) ?>"
                                    data-date="<?= htmlspecialchars($details['acquisition_date']) ?>"
                                    data-unit="<?= htmlspecialchars($details['unit']) ?>"
                                    data-property-no="<?= htmlspecialchars($details['property_no']) ?>">
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                    </tr>
                <?php endfor; ?>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-end fw-bold">Total:</td>
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
                            class="form-control text-end fw-bold"
                            readonly
                            style="padding-left: 1.5rem; width: 150px;">
                    </td>
                </tr>

            </tfoot>

            </tbody>

        </table>
        <button type="button" class="btn btn-outline-primary mt-2" onclick="addRow()">+ Add Item</button>

        <hr class="mt-5 mb-4">

        <div class="row mt-4 mb-3">
            <!-- Left: Received by -->
            <div class="col-md-6 text-center">
                <p class="fw-semibold">Received by:</p>
                <br><br>
                <p class="fw-semibold border-bottom border-dark d-inline-block" style="min-width: 250px;"></p>
                <p>Signature over Printed Name</p>

                <label for="position_office_left">Position / Office:</label>
                <input type="text" class="form-control text-center" name="position_office_left"
                    value="<?= htmlspecialchars($par_data['position_office_left'] ?? '') ?>">
                <br>
                <label>Date:</label>
                <input type="date" name="date_received_left"
                    value="<?= date('Y-m-d') ?>"
                    class="form-control">
            </div>

            <!-- Right: Issued by -->
            <div class="col-md-6 text-center">
                <p class="fw-semibold">Issued by:</p>
                <br><br>
                <p class="fw-semibold border-bottom border-dark d-inline-block" style="min-width: 250px;"></p>
                <p>Signature over Printed Name</p>

                <label for="position_office_right">Position / Office:</label>
                <input type="text" class="form-control text-center" name="position_office_right"
                    value="<?= htmlspecialchars($par_data['position_office_right'] ?? '') ?>">
                <br>
                <label>Date:</label>
                <input type="date" name="date_received_right"
                    value="<?= date('Y-m-d') ?>"
                    class="form-control">
            </div>
        </div>

        <button type="submit" class="btn btn-primary"><i class="bi bi-send-check-fill"></i>Save</button>
    </form>
</div>

<?php include 'modals/par_duplicate_modal.php' ?>
<script>
    let rowIndex = 5; // Start after the initial 5 rows
    let selectedDescriptions = new Set(); // 🔁 Track selected asset descriptions

    function addRow() {
        const tbody = document.getElementById('itemTableBody');
        const newRow = document.createElement('tr');

        newRow.innerHTML = `
        <td><input type="number" name="items[${rowIndex}][quantity]" class="form-control text-end" id="qtyInput${rowIndex}"></td>
        <td>
            <select name="items[${rowIndex}][unit]" class="form-select text-center" required>
                <option value="">Select Unit</option>
                <?php foreach ($units as $unit): ?>
                    <option value="<?= htmlspecialchars($unit['unit_name']) ?>"><?= htmlspecialchars($unit['unit_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="position-relative">
    <div class="input-group">
        <input type="text" name="items[${rowIndex}][description]" class="form-control form-control-lg" list="descriptionList" id="descInput${rowIndex}">
        <input type="hidden" name="items[<?= $i ?>][asset_id]" id="assetId<?= $i ?>">
        <button type="button"
                class="btn p-0 m-0 border-0 bg-transparent"
                onclick="clearDescription(${rowIndex})"
                style="width: 30px; height: 30px; display: flex; align-items: center; justify-content: center;">
            <span class="badge rounded-circle bg-secondary text-white" style="font-size: 0.75rem;">×</span>
        </button>
    </div>
</td>

        <td><input type="text" name="items[${rowIndex}][property_no]" class="form-control"></td>
        <td><input type="date" name="items[${rowIndex}][date_acquired]" class="form-control" id="acqDate${rowIndex}"></td>
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
        class="form-control text-end"
        step="0.01"
        id="unitCost${rowIndex}"
        style="padding-left: 1.5rem; width: 100%;">
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
        class="form-control text-end fw-semibold"
        step="0.01"
        id="amount${rowIndex}"
        readonly
        style="padding-left: 1.5rem; width: 100%;">
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
        const dataList = document.getElementById('descriptionList');
        const assetIdInput = document.getElementById('assetId' + i);
        const propertyNoInput = document.querySelector(`[name="items[${i}][property_no]"]`);

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

            // Check all other description inputs for duplicates
            const descInputs = document.querySelectorAll('input[list="descriptionList"]');
            let duplicateFound = false;

            descInputs.forEach((input, idx) => {
                if (input !== descInput && input.value.trim() === val) {
                    duplicateFound = true;
                }
            });

            if (duplicateFound) {
                showDuplicateModal();
                descInput.value = '';
                return;
            }

            const options = dataList.options;
            for (let j = 0; j < options.length; j++) {
                if (options[j].value === val) {
                    const assetId = options[j].getAttribute('data-asset-id');
                    const unitCost = options[j].getAttribute('data-unit-cost');
                    const maxQty = options[j].getAttribute('data-quantity');
                    const acqDate = options[j].getAttribute('data-date');
                    const unit = options[j].getAttribute('data-unit');
                    const propertyNo = options[j].getAttribute('data-property-no');

                    assetIdInput.value = assetId;
                    unitCostInput.value = unitCost;
                    qtyInput.max = maxQty;
                    acqDateInput.value = acqDate;

                    if (propertyNoInput) {
                        propertyNoInput.value = propertyNo; // ✅ autofill property no
                    }

                    const unitSelect = document.querySelector(`[name="items[${i}][unit]"]`);
                    if (unitSelect && unit) {
                        for (let opt of unitSelect.options) {
                            if (opt.value === unit) {
                                unitSelect.value = unit;
                                break;
                            }
                        }
                    }

                    if (parseInt(qtyInput.value) > parseInt(maxQty)) {
                        qtyInput.value = '';
                        alert("Quantity exceeds available stock: " + maxQty);
                    }

                    calculateAmount();
                    break;
                }
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

    // Optional: Final duplicate check on form submission
    function checkDuplicates() {
        const descInputs = document.querySelectorAll('input[list="descriptionList"]');
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


    // Add this to your submit button: onclick="return checkDuplicates()"
</script>