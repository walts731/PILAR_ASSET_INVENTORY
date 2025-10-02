<?php
// Fetch latest iirup_form record (including header_image)
$sql = "SELECT accountable_officer, designation, office, header_image 
        FROM iirup_form 
        ORDER BY id DESC 
        LIMIT 1";
$result = $conn->query($sql);

$accountable_officer = "";
$designation = "";
$office = "";
$header_image = "";

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $accountable_officer = htmlspecialchars($row['accountable_officer']);
    $designation = htmlspecialchars($row['designation']);
    $office = htmlspecialchars($row['office']);
    $header_image = htmlspecialchars($row['header_image']);
}


// Fetch office list
$offices = [];
$sql_office = "SELECT id, office_name FROM offices ORDER BY office_name ASC";
$result_office = $conn->query($sql_office);
if ($result_office && $result_office->num_rows > 0) {
    while ($row_office = $result_office->fetch_assoc()) {
        $offices[] = $row_office;
    }
}

// Handle pre-selected asset from QR scan
$preselected_asset = null;
if (isset($_GET['asset_id']) && is_numeric($_GET['asset_id'])) {
    $asset_id = intval($_GET['asset_id']);
    $stmt_preselect = $conn->prepare("
        SELECT a.id, a.description, a.quantity, a.value, a.office_id, a.inventory_tag, o.office_name,
               CASE WHEN rt.id IS NOT NULL THEN 1 ELSE 0 END as has_red_tag
        FROM assets a
        LEFT JOIN offices o ON o.id = a.office_id
        LEFT JOIN red_tags rt ON rt.asset_id = a.id
        WHERE a.id = ? AND a.type = 'asset'
    ");
    $stmt_preselect->bind_param("i", $asset_id);
    $stmt_preselect->execute();
    $result_preselect = $stmt_preselect->get_result();
    if ($result_preselect->num_rows > 0) {
        $preselected_asset = $result_preselect->fetch_assoc();
    }
    $stmt_preselect->close();
}

// Fetch asset data for datalist and JS - only assets where red_tagged = 0 and check for red tag status
$assets_data = [];
$sql_assets = "SELECT a.id, a.description, a.quantity, a.value, a.office_id, a.inventory_tag, o.office_name,
                      CASE WHEN rt.id IS NOT NULL THEN 1 ELSE 0 END as has_red_tag
               FROM assets a
               LEFT JOIN offices o ON o.id = a.office_id
               LEFT JOIN red_tags rt ON rt.asset_id = a.id
               WHERE a.type = 'asset' AND a.inventory_tag IS NOT NULL AND a.inventory_tag <> ''
                 AND (a.red_tagged = 0 OR a.red_tagged IS NULL)
               ORDER BY a.description ASC";
$result_assets = $conn->query($sql_assets);
if ($result_assets && $result_assets->num_rows > 0) {
    while ($row_asset = $result_assets->fetch_assoc()) {
        $assets_data[] = $row_asset;
    }
}

?>



<!-- IIRUP FORM HEADER -->
<div class="d-flex justify-content-end mb-2">
    <a href="saved_iirup.php?id=<?= isset($_GET['id']) ? intval($_GET['id']) : 7 ?>" class="btn btn-outline-info btn-sm">
        <i class="bi bi-folder-check"></i> View Saved IIRUP
    </a>
&nbsp;
</div>
<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
    <?php $savedCount = isset($_GET['count']) ? (int)$_GET['count'] : 0; ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> IIRUP form and <?= htmlspecialchars((string)$savedCount) ?> item(s) were saved successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (!empty($header_image)): ?>
    <div style="text-align: center; margin-bottom: 15px;">
        <img src="../img/<?= $header_image ?>" 
             alt="Header Image" 
             style="max-height: 120px; display: block; margin: 0 auto;">
        <div style="font-size: 12px; color: gray; margin-top: 5px;">
            As of <?= date("F, Y") ?>
        </div>
    </div>
<?php endif; ?>

<form method="POST" action="save_iirup_items.php">
    <!-- Hidden input to always include header_image in submission -->
    <input type="hidden" name="header_image" value="<?= htmlspecialchars($header_image) ?>">

<div style="display: flex; justify-content: space-between; text-align: center; margin-top: 10px;" class="mb-3">
    <div style="flex: 1; margin: 0 5px;">
        <input type="text" name="accountable_officer" value="<?= $accountable_officer ?>"
            style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
        <br>
        <small><em>(Name of Accountable Officer)</em></small>
    </div>
    <div style="flex: 1; margin: 0 5px;">
        <input type="text" name="designation" value="<?= $designation ?>"
            style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
        <br>
        <small><em>(Designation)</em></small>
    </div>
    <div style="flex: 1; margin: 0 5px;">
        <select name="office" style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
            <option value="">-- Select Office --</option>
            <?php foreach ($offices as $o): ?>
                <option value="<?= htmlspecialchars($o['office_name']) ?>"
                    <?= ($office == $o['office_name']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($o['office_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br>
        <small><em>(Department/Office)</em></small>
    </div>
</div>

<style>
    .excel-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 10px;
        text-align: center;
        table-layout: fixed;
    }

    .excel-table th,
    .excel-table td {
        border: 1px solid #000;
        padding: 2px 3px;
        vertical-align: middle;
    }

    .excel-table thead th {
        background-color: #fff;
        font-weight: bold;
    }

    .excel-table input {
        width: 100%;
        border: none;
        text-align: center;
        font-size: 10px;
        padding: 0;
    }
</style>

<table class="excel-table">

    <thead>
        <tr>
            <th colspan="10">INVENTORY</th>
            <th colspan="6">INSPECTION and DISPOSAL</th>
            <th colspan="2">RECORD OF SALES</th>
            <th rowspan="2">DEPT/OFFICE</th>
            <th rowspan="2">CODE</th>
            <th rowspan="2">RED TAG</th>
            <th rowspan="2">DATE RECEIVED</th>
        </tr>
        <tr>
            <th>Date Acquired<br>(1)</th>
            <th>Particulars/ Articles<br>(2)</th>
            <th>Property No.<br>(3)</th>
            <th>Qty<br>(4)</th>
            <th>Unit Cost<br>(5)</th>
            <th>Total Cost<br>(6)</th>
            <th>Accumulated Depreciation<br>(7)</th>
            <th>Accumulated Impairment Losses<br>(8)</th>
            <th>Carrying Amount<br>(9)</th>
            <th>Remarks<br>(10)</th>
            <th>Sale<br>(11)</th>
            <th>Transfer<br>(12)</th>
            <th>Destruction<br>(13)</th>
            <th>Others (Specify)<br>(14)</th>
            <th>Total<br>(15)</th>
            <th>Appraised Value<br>(16)</th>
            <th>OR No.<br>(17)</th>
            <th>Amount<br>(18)</th>
        </tr>
    </thead>
    <tbody>
        <?php for ($i = 0; $i < 1; $i++): ?>
            <?php 
                // Pre-populate first row if asset is selected from QR scan
                $is_first_row = ($i === 0);
                $preselected_description = ($is_first_row && $preselected_asset) ? $preselected_asset['description'] : '';
                $preselected_asset_id = ($is_first_row && $preselected_asset) ? $preselected_asset['id'] : '';
                $preselected_property_no = ($is_first_row && $preselected_asset) ? ($preselected_asset['inventory_tag'] ?? '') : '';
                $preselected_unit_cost = ($is_first_row && $preselected_asset) ? $preselected_asset['value'] : '';
                $preselected_office = ($is_first_row && $preselected_asset) ? $preselected_asset['office_name'] : '';
                $show_remove_btn = ($is_first_row && $preselected_asset) ? 'inline-block' : 'none';
            ?>
            <tr class="iirup-row">
                <td><input type="date" name="date_acquired[]" value="<?= date('Y-m-d'); ?>"></td>
                <td>
                    <div class="d-flex align-items-center">
                        <input type="text" name="particulars[]" list="asset_descriptions" class="particulars flex-grow-1" 
                               value="<?= htmlspecialchars($preselected_description) ?>">
                        <button type="button" class="btn btn-sm btn-danger ms-1 remove-asset" 
                                style="display: <?= $show_remove_btn ?>;" title="Remove Asset">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <input type="hidden" name="asset_id[]" class="asset_id" value="<?= htmlspecialchars($preselected_asset_id) ?>">
                </td>
                <td><input type="text" name="property_no[]" value="<?= htmlspecialchars($preselected_property_no) ?>"></td>
                <td><input type="number" name="qty[]" min="1" class="qty" max="1" value="<?= $preselected_asset ? '1' : '' ?>"></td>
                <td><input type="number" step="0.01" name="unit_cost[]" min="1" class="unit_cost" 
                           value="<?= htmlspecialchars($preselected_unit_cost) ?>"></td>
                <td>
                    <input type="number" step="0.01" name="total_cost[]" min="1" readonly 
                           value="<?= $preselected_unit_cost ? $preselected_unit_cost : '' ?>">
                </td>
                <td><input type="number" step="0.01" name="accumulated_depreciation[]" min="1"></td>
                <td><input type="number" step="0.01" name="accumulated_impairment_losses[]" min="1"></td>
                <td><input type="number" step="0.01" name="carrying_amount[]" min="1"></td>
                <td>
                    <select name="remarks[]">
                        <option value="Unserviceable" selected>Unserviceable</option>
                        <option value="Serviceable">Serviceable</option>
                    </select>
                </td>
                <td><input type="text" name="sale[]"></td>
                <td><input type="text" name="transfer[]"></td>
                <td><input type="text" name="destruction[]"></td>
                <td><input type="text" name="others[]"></td>
                <td><input type="number" step="0.01" name="total[]" min="1"></td>
                <td><input type="number" step="0.01" name="appraised_value[]" min="1"></td>
                <td><input type="text" name="or_no[]"></td>
                <td><input type="number" step="0.01" name="amount[]" min="1"></td>
                <td>
                    <input type="text" name="dept_office[]" class="dept_office" value="<?= htmlspecialchars($preselected_office) ?>" readonly>
                </td>
                <td><input type="text" name="code[]"></td>
                <td><input type="text" name="red_tag[]"></td>
                <td><input type="date" name="date_received[]" value="<?= date('Y-m-d'); ?>"></td>
            </tr>
        <?php endfor; ?>
    </tbody>
</table>

<!-- Row Management Buttons -->
<div class="d-flex justify-content-start gap-2 mt-2 mb-3">
    <button type="button" id="addRowBtn" class="btn btn-success btn-sm">
        <i class="bi bi-plus-circle"></i> Add Row
    </button>
    <button type="button" id="removeRowBtn" class="btn btn-danger btn-sm">
        <i class="bi bi-dash-circle"></i> Remove Last Row
    </button>
</div>

<datalist id="asset_descriptions">
    <?php foreach ($assets_data as $asset): ?>
        <?php 
            $label = ($asset['inventory_tag'] ?? '') . ' - ' . $asset['description'];
            if ($asset['has_red_tag']) {
                $label .= ' [RED TAGGED]';
            }
        ?>
        <option 
            value="<?= htmlspecialchars($asset['description']) ?>"
            data-asset-id="<?= $asset['id'] ?>"
            label="<?= htmlspecialchars($label) ?>">
        </option>
    <?php endforeach; ?>
</datalist>

<!-- (Single-submit) Keep form open; footer is below -->

<?php
// Handle footer save/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_footer'])) {
    $footer_accountable_officer = $_POST['footer_accountable_officer'] ?? '';
    $footer_authorized_official = $_POST['footer_authorized_official'] ?? '';
    $footer_designation_officer = $_POST['footer_designation_officer'] ?? '';
    $footer_designation_official = $_POST['footer_designation_official'] ?? '';

    // Check if there's already a row in the table
    $check = $conn->query("SELECT id FROM iirup_form ORDER BY id DESC LIMIT 1");
    if ($check && $check->num_rows > 0) {
        $row = $check->fetch_assoc();
        $latest_id = $row['id'];
        // Update latest row
        $stmt = $conn->prepare("UPDATE iirup_form SET 
            footer_accountable_officer = ?, 
            footer_authorized_official = ?, 
            footer_designation_officer = ?, 
            footer_designation_official = ? 
            WHERE id = ?");
        $stmt->bind_param("ssssi", $footer_accountable_officer, $footer_authorized_official, $footer_designation_officer, $footer_designation_official, $latest_id);
        $stmt->execute();
    } else {
        // Insert new if no row exists
        $stmt = $conn->prepare("INSERT INTO iirup_form (footer_accountable_officer, footer_authorized_official, footer_designation_officer, footer_designation_official) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $footer_accountable_officer, $footer_authorized_official, $footer_designation_officer, $footer_designation_official);
        $stmt->execute();
    }
}

// Fetch latest footer data from iirup_form
$sql_footer = "SELECT footer_accountable_officer, footer_authorized_official, 
                      footer_designation_officer, footer_designation_official
               FROM iirup_form 
               ORDER BY id DESC 
               LIMIT 1";
$result_footer = $conn->query($sql_footer);

$footer_accountable_officer = "";
$footer_authorized_official = "";
$footer_designation_officer = "";
$footer_designation_official = "";

if ($result_footer && $result_footer->num_rows > 0) {
    $row_footer = $result_footer->fetch_assoc();
    $footer_accountable_officer = htmlspecialchars($row_footer['footer_accountable_officer']);
    $footer_authorized_official = htmlspecialchars($row_footer['footer_authorized_official']);
    $footer_designation_officer = htmlspecialchars($row_footer['footer_designation_officer']);
    $footer_designation_official = htmlspecialchars($row_footer['footer_designation_official']);
}
?>

<!-- FOOTER SECTION -->
<div style="margin-top: 30px; font-size: 12px; line-height: 1.5;">
    <table style="width: 100%; border-collapse: collapse; text-align: center;">
        <tr>
            <td colspan="2" style="padding: 5px; text-align: left;">
                I HEREBY request inspection and disposition, pursuant to Section 79 of PD 1445, 
                of the property enumerated above.
            </td>
            <td style="padding: 5px; text-align: left;">
                I CERTIFY that I have inspected each and every article enumerated in this report, 
                and that the disposition made thereof was, in my judgment, the best for the public interest.
            </td>
            <td style="padding: 5px; text-align: left;">
                I CERTIFY that I have witnessed the disposition of the articles enumerated on this report 
                this ____ day of _____________, _____.
            </td>
        </tr>
        <tr><td colspan="4" style="height: 30px;"></td></tr>
        <tr>
            <td>Requested by:</td>
            <td>Approved by:</td>
            <td>(Signature over Printed Name of Inspection Officer)</td>
            <td>(Signature over Printed Name of Witness)</td>
        </tr>
        <tr><td colspan="4" style="height: 50px;"></td></tr>
        <tr>
            <td>
                <input type="text" name="footer_accountable_officer" value="<?= $footer_accountable_officer ?>" 
                       style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
            </td>
            <td>
                <input type="text" name="footer_authorized_official" value="<?= $footer_authorized_official ?>" 
                       style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
            </td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>(Signature over Printed Name of Accountable Officer)</td>
            <td>(Signature over Printed Name of Authorized Official)</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>
                <input type="text" name="footer_designation_officer" value="<?= $footer_designation_officer ?>" 
                       style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
                <br>(Designation of Accountable Officer)
            </td>
            <td>
                <input type="text" name="footer_designation_official" value="<?= $footer_designation_official ?>" 
                       style="width: 100%; border: none; border-bottom: 1px solid black; text-align: center;">
                <br>(Designation of Authorized Official)
            </td>
            <td></td>
            <td></td>
        </tr>
    </table>
    <small class="text-muted">Ensure details are correct before submitting.</small>
    <br>
</div>

<div class="d-flex justify-content-end gap-2" style="margin-top:10px;">
    <a href="saved_iirup.php?id=<?= isset($_GET['id']) ? intval($_GET['id']) : 7 ?>" class="btn btn-success">
        <i class="bi bi-folder-check"></i> View Saved IIRUP
    </a>
    <button type="submit" name="save_iirup" class="btn btn-primary">Submit IIRUP</button>
    
</div>

</form>

<script>
    const assetsData = <?= json_encode($assets_data) ?>;
    let selectedAssetIds = new Set();

    function updateDatalist() {
        const datalist = document.getElementById('asset_descriptions');
        const options = datalist.querySelectorAll('option');
        
        options.forEach(option => {
            const assetId = option.getAttribute('data-asset-id');
            if (selectedAssetIds.has(assetId)) {
                option.style.display = 'none';
            } else {
                option.style.display = 'block';
            }
        });
    }

    function clearAssetRow(row) {
        const particularInput = row.querySelector('.particulars');
        const assetIdInput = row.querySelector('.asset_id');
        const qtyInput = row.querySelector('.qty');
        const unitCostInput = row.querySelector('.unit_cost');
        const totalCostInput = row.querySelector('input[name="total_cost[]"]');
        const deptInput = row.querySelector('.dept_office');
        const removeBtn = row.querySelector('.remove-asset');
        
        // Remove from selected set if it was selected
        if (assetIdInput.value) {
            selectedAssetIds.delete(assetIdInput.value);
        }
        
        // Clear all inputs
        particularInput.value = '';
        assetIdInput.value = '';
        if (qtyInput) qtyInput.value = '';
        if (unitCostInput) unitCostInput.value = '';
        if (totalCostInput) totalCostInput.value = '';
        if (deptInput) deptInput.value = '';
        
        // Hide remove button
        removeBtn.style.display = 'none';
        
        // Update datalist
        updateDatalist();
    }

    document.querySelectorAll('.particulars').forEach((particularInput, index) => {
        particularInput.addEventListener('input', function() {
            const selected = assetsData.find(a => a.description === this.value);
            const row = this.closest('tr');
            const qtyInput = row.querySelector('.qty');
            const unitCostInput = row.querySelector('.unit_cost');
            const idInput = row.querySelector('.asset_id');
            const deptInput = row.querySelector('.dept_office');
            const removeBtn = row.querySelector('.remove-asset');
            
            // Clear previous selection from set
            if (idInput.value) {
                selectedAssetIds.delete(idInput.value);
            }

            if (selected) {
                // Check if asset is already selected
                if (selectedAssetIds.has(selected.id.toString())) {
                    alert('This asset has already been selected. Please choose a different asset.');
                    this.value = '';
                    return;
                }
                
                // Add to selected set
                selectedAssetIds.add(selected.id.toString());
                
                // Set hidden asset id
                if (idInput) idInput.value = selected.id || '';
                // Set max quantity based on DB
                if (qtyInput) {
                    qtyInput.max = selected.quantity;
                    qtyInput.value = 1;
                }
                // Autofill unit cost based on DB
                if (unitCostInput) unitCostInput.value = selected.value;
                // Autofill department/office name (read-only)
                if (deptInput) deptInput.value = selected.office_name || '';
                
                // Show remove button
                removeBtn.style.display = 'inline-block';
            } else {
                // Clear when no matching asset
                if (idInput.value) {
                    selectedAssetIds.delete(idInput.value);
                }
                if (idInput) idInput.value = '';
                if (deptInput) deptInput.value = '';
                removeBtn.style.display = 'none';
            }
            
            // Update datalist
            updateDatalist();
        });
    });

    // Handle remove asset buttons
    document.querySelectorAll('.remove-asset').forEach(button => {
        button.addEventListener('click', function() {
            const row = this.closest('tr');
            clearAssetRow(row);
        });
    });

    document.addEventListener('input', function(e) {
        if (e.target.name === 'qty[]' || e.target.name === 'unit_cost[]') {
            let row = e.target.closest('tr');
            let qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            let unitCost = parseFloat(row.querySelector('input[name="unit_cost[]"]').value) || 0;
            row.querySelector('input[name="total_cost[]"]').value = (qty * unitCost).toFixed(2);
        }
    });
    
    // Add Row Functionality
    function addNewRow() {
        const tbody = document.querySelector('.excel-table tbody');
        const newRow = document.createElement('tr');
        newRow.className = 'iirup-row';
        
        const today = new Date().toISOString().split('T')[0];
        
        newRow.innerHTML = `
            <td><input type="date" name="date_acquired[]" value="${today}"></td>
            <td>
                <div class="d-flex align-items-center">
                    <input type="text" name="particulars[]" list="asset_descriptions" class="particulars flex-grow-1">
                    <button type="button" class="btn btn-sm btn-danger ms-1 remove-asset" 
                            style="display: none;" title="Remove Asset">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <input type="hidden" name="asset_id[]" class="asset_id">
            </td>
            <td><input type="text" name="property_no[]"></td>
            <td><input type="number" name="qty[]" min="1" class="qty" max="1"></td>
            <td><input type="number" step="0.01" name="unit_cost[]" min="1" class="unit_cost"></td>
            <td><input type="number" step="0.01" name="total_cost[]" min="1" readonly></td>
            <td><input type="number" step="0.01" name="accumulated_depreciation[]" min="1"></td>
            <td><input type="number" step="0.01" name="accumulated_impairment_losses[]" min="1"></td>
            <td><input type="number" step="0.01" name="carrying_amount[]" min="1"></td>
            <td>
                <select name="remarks[]">
                    <option value="Unserviceable" selected>Unserviceable</option>
                    <option value="Serviceable">Serviceable</option>
                </select>
            </td>
            <td><input type="text" name="sale[]"></td>
            <td><input type="text" name="transfer[]"></td>
            <td><input type="text" name="destruction[]"></td>
            <td><input type="text" name="others[]"></td>
            <td><input type="number" step="0.01" name="total[]" min="1"></td>
            <td><input type="number" step="0.01" name="appraised_value[]" min="1"></td>
            <td><input type="text" name="or_no[]"></td>
            <td><input type="number" step="0.01" name="amount[]" min="1"></td>
            <td><input type="text" name="dept_office[]" class="dept_office" readonly></td>
            <td><input type="text" name="code[]"></td>
            <td><input type="text" name="red_tag[]"></td>
            <td><input type="date" name="date_received[]" value="${today}"></td>
        `;
        
        tbody.appendChild(newRow);
        
        // Attach event listeners to the new row
        attachRowEventListeners(newRow);
        updateRowVisibility();
    }
    
    function removeLastRow() {
        const rows = document.querySelectorAll('.iirup-row');
        if (rows.length > 1) {
            const lastRow = rows[rows.length - 1];
            const assetIdInput = lastRow.querySelector('.asset_id');
            
            // Remove from selected set if it was selected
            if (assetIdInput && assetIdInput.value) {
                selectedAssetIds.delete(assetIdInput.value);
                updateDatalist();
            }
            
            lastRow.remove();
            updateRowVisibility();
        }
    }
    
    
    function updateRowVisibility() {
        const rows = document.querySelectorAll('.iirup-row');
        
        // Update remove last row button
        const removeRowBtn = document.getElementById('removeRowBtn');
        if (removeRowBtn) {
            removeRowBtn.disabled = rows.length <= 1;
        }
    }
    
    function attachRowEventListeners(row) {
        // Attach particulars input listener
        const particularInput = row.querySelector('.particulars');
        if (particularInput) {
            particularInput.addEventListener('input', function() {
                const selected = assetsData.find(a => a.description === this.value);
                const qtyInput = row.querySelector('.qty');
                const unitCostInput = row.querySelector('.unit_cost');
                const idInput = row.querySelector('.asset_id');
                const deptInput = row.querySelector('.dept_office');
                const removeBtn = row.querySelector('.remove-asset');
                
                // Clear previous selection from set
                if (idInput.value) {
                    selectedAssetIds.delete(idInput.value);
                }

                if (selected) {
                    // Check if asset is already selected
                    if (selectedAssetIds.has(selected.id.toString())) {
                        alert('This asset has already been selected. Please choose a different asset.');
                        this.value = '';
                        return;
                    }
                    
                    // Add to selected set
                    selectedAssetIds.add(selected.id.toString());
                    
                    // Set hidden asset id
                    if (idInput) idInput.value = selected.id || '';
                    // Set max quantity based on DB
                    if (qtyInput) {
                        qtyInput.max = selected.quantity;
                        qtyInput.value = 1;
                    }
                    // Autofill unit cost based on DB
                    if (unitCostInput) unitCostInput.value = selected.value;
                    // Autofill department/office name (read-only)
                    if (deptInput) deptInput.value = selected.office_name || '';
                    
                    // Show remove button
                    removeBtn.style.display = 'inline-block';
                } else {
                    // Clear when no matching asset
                    if (idInput.value) {
                        selectedAssetIds.delete(idInput.value);
                    }
                    if (idInput) idInput.value = '';
                    if (deptInput) deptInput.value = '';
                    removeBtn.style.display = 'none';
                }
                
                // Update datalist
                updateDatalist();
            });
        }
        
        // Attach remove asset button listener
        const removeAssetBtn = row.querySelector('.remove-asset');
        if (removeAssetBtn) {
            removeAssetBtn.addEventListener('click', function() {
                clearAssetRow(row);
            });
        }
        
    }
    
    // Event listeners for add/remove row buttons
    document.getElementById('addRowBtn').addEventListener('click', addNewRow);
    document.getElementById('removeRowBtn').addEventListener('click', removeLastRow);
    
    // Initialize row visibility
    updateRowVisibility();
    
    // Handle preselected asset from QR scan
    <?php if ($preselected_asset): ?>
        // Add preselected asset to selected set
        selectedAssetIds.add('<?= $preselected_asset['id'] ?>');
        updateDatalist();
    <?php endif; ?>
</script>