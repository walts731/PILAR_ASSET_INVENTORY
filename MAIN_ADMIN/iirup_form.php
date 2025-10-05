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
    /* Enhanced IIRUP Table Styling */
    .excel-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 11px;
        text-align: center;
        table-layout: auto;
        background-color: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: visible;
        margin: 20px 0;
        position: relative;
        z-index: 1;
    }

    .excel-table th,
    .excel-table td {
        border: 1px solid #ddd;
        padding: 4px 3px;
        vertical-align: middle;
        position: relative;
        overflow: visible;
        font-size: 10px;
        line-height: 1.2;
    }

    /* Header Styling */
    .excel-table thead th {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        font-size: 7px;
        line-height: 1.0;
        border: 1px solid #6c757d;
        padding: 2px 1px;
        height: 20px;
        vertical-align: middle;
    }

    /* Section Headers with Different Colors */
    .excel-table thead tr:first-child th:nth-child(1) {
        background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
        color: #1565c0;
        border-color: #1976d2;
    }

    .excel-table thead tr:first-child th:nth-child(2) {
        background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
        color: #7b1fa2;
        border-color: #8e24aa;
    }

    .excel-table thead tr:first-child th:nth-child(3) {
        background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
        color: #2e7d32;
        border-color: #388e3c;
    }

    /* Individual Column Headers */
    .excel-table thead tr:nth-child(2) th {
        background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
        font-size: 6px;
        padding: 1px 1px;
        min-width: 50px;
        line-height: 1.0;
        height: 18px;
        vertical-align: middle;
    }

    /* Specific Column Widths for Better Readability */
    .excel-table th:nth-child(1), .excel-table td:nth-child(1) { min-width: 80px; } /* Date Acquired */
    .excel-table th:nth-child(2), .excel-table td:nth-child(2) { min-width: 150px; } /* Particulars */
    .excel-table th:nth-child(3), .excel-table td:nth-child(3) { min-width: 80px; } /* Property No */
    .excel-table th:nth-child(4), .excel-table td:nth-child(4) { min-width: 40px; }  /* Qty */
    .excel-table th:nth-child(5), .excel-table td:nth-child(5) { min-width: 70px; }  /* Unit Cost */
    .excel-table th:nth-child(6), .excel-table td:nth-child(6) { min-width: 70px; }  /* Total Cost */

    /* Row Styling */
    .excel-table tbody tr {
        transition: background-color 0.2s ease;
        position: relative;
        z-index: 1;
    }

    .excel-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
    }

    .excel-table tbody tr:hover {
        background-color: #e3f2fd;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Ensure tooltip containers can appear above other rows */
    .excel-table tbody tr:has(.tooltip-container:hover) {
        z-index: 9998;
    }

    /* Input Field Styling */
    .excel-table input,
    .excel-table select {
        width: 100%;
        border: 1px solid transparent;
        text-align: center;
        font-size: 9px;
        padding: 2px 1px;
        background-color: transparent;
        border-radius: 2px;
        transition: all 0.2s ease;
        height: 22px;
        min-height: 22px;
    }

    .excel-table input:focus,
    .excel-table select:focus {
        outline: none;
        border-color: #007bff;
        background-color: #fff;
        box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
    }

    .excel-table input[readonly] {
        background-color: #f8f9fa;
        color: #6c757d;
        cursor: not-allowed;
    }

    /* Date Input Styling */
    .excel-table input[type="date"] {
        font-size: 10px;
        padding: 3px;
    }

    /* Number Input Styling */
    .excel-table input[type="number"] {
        text-align: right;
        padding-right: 6px;
    }

    /* Select Dropdown Styling */
    .excel-table select {
        cursor: pointer;
        font-size: 10px;
        padding: 3px;
    }

    /* Button Styling in Table */
    .excel-table .btn {
        padding: 2px 6px;
        font-size: 10px;
        border-radius: 3px;
        margin: 0 1px;
    }

    /* Particulars Column Special Styling */
    .excel-table .particulars {
        text-align: left;
        font-weight: 500;
        color: #495057;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .excel-table {
            font-size: 10px;
            overflow-x: auto;
            display: table;
            width: 100%;
            min-width: 800px;
        }
        
        .excel-table th,
        .excel-table td {
            padding: 4px 2px;
            font-size: 9px;
        }
        
        .excel-table input,
        .excel-table select {
            font-size: 9px;
            padding: 2px 1px;
        }
    }

    /* Print Styling */
    @media print {
        .excel-table {
            font-size: 8px;
            box-shadow: none;
        }
        
        .excel-table th,
        .excel-table td {
            padding: 2px;
        }
        
        .excel-table input,
        .excel-table select {
            border: 1px solid #000 !important;
            font-size: 8px;
        }
    }

    /* Loading Animation for Dynamic Rows */
    .iirup-row {
        animation: fadeIn 0.3s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Enhanced Section Headers */
    .table-section-header {
        position: relative;
    }

    /* Improved Visual Hierarchy */
    .excel-table thead tr:first-child th {
        font-size: 8px;
        font-weight: 700;
        padding: 3px 2px;
        text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        height: 22px;
    }

    /* Fix Form Layout and Prevent Overlapping */
    .form-container {
        clear: both;
        overflow: visible;
    }

    /* Ensure proper spacing between form elements */
    .excel-table + * {
        margin-top: 20px;
        clear: both;
    }

    /* Fix any floating issues */
    .excel-table::after {
        content: "";
        display: table;
        clear: both;
    }

    /* Ensure table doesn't break out of container */
    .excel-table {
        max-width: 100%;
        table-layout: fixed;
    }

    /* Prevent column overflow */
    .excel-table th,
    .excel-table td {
        word-wrap: break-word;
        overflow: hidden;
    }

    /* Tooltip Styles for Input Field Details */
    .tooltip-container {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .excel-table .tooltip-container:hover {
        z-index: 9999;
        position: relative;
    }

    .field-tooltip {
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 500;
        white-space: nowrap;
        z-index: 10000;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        pointer-events: none;
        margin-top: 8px;
    }

    .field-tooltip::after {
        content: '';
        position: absolute;
        bottom: 100%;
        left: 50%;
        transform: translateX(-50%);
        border: 5px solid transparent;
        border-bottom-color: #2c3e50;
    }

    .tooltip-container:hover .field-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(5px);
    }

    /* Enhanced input hover effects */
    .excel-table input:hover,
    .excel-table select:hover {
        border-color: #007bff;
        background-color: #f8f9ff;
        transform: scale(1.02);
        box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    }

    /* Special styling for different field types */
    .tooltip-required {
        background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
    }

    .tooltip-required::after {
        border-bottom-color: #e74c3c;
    }

    .tooltip-calculated {
        background: linear-gradient(135deg, #27ae60 0%, #229954 100%);
    }

    .tooltip-calculated::after {
        border-bottom-color: #27ae60;
    }

    .tooltip-info {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
    }

    .tooltip-info::after {
        border-bottom-color: #3498db;
    }

    /* Responsive tooltip adjustments */
    @media (max-width: 768px) {
        .field-tooltip {
            font-size: 10px;
            padding: 6px 8px;
            white-space: normal;
            max-width: 200px;
            text-align: center;
        }
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
            <th rowspan="2">DATE RECEIVED</th>
            <th rowspan="2">ACTIONS</th>
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
                <td data-label="Date Acquired">
                    <input type="date" name="date_acquired[]" value="<?= date('Y-m-d'); ?>" 
                           title="Date when the asset was originally acquired by the organization">
                </td>
                <td data-label="Particulars/Articles">
                    <div class="d-flex align-items-center">
                        <input type="text" name="particulars[]" list="asset_descriptions" class="particulars flex-grow-1" 
                               value="<?= htmlspecialchars($preselected_description) ?>" placeholder="Select or type asset description"
                               title="Description of the asset/item being inspected for disposal">
                        <button type="button" class="btn btn-sm btn-danger ms-1 remove-asset" 
                                style="display: <?= $show_remove_btn ?>;" title="Remove Asset">
                            <i class="bi bi-x"></i>
                        </button>
                    </div>
                    <input type="hidden" name="asset_id[]" class="asset_id" value="<?= htmlspecialchars($preselected_asset_id) ?>">
                </td>
                <td data-label="Property No">
                    <input type="text" name="property_no[]" value="<?= htmlspecialchars($preselected_property_no) ?>" placeholder="Property Number"
                           title="Official property number or inventory tag assigned to the asset">
                </td>
                <td data-label="Quantity">
                    <input type="number" name="qty[]" min="1" class="qty" max="1" value="<?= $preselected_asset ? '1' : '' ?>" placeholder="Qty"
                           title="Number of units of this asset being inspected">
                </td>
                <td data-label="Unit Cost">
                    <input type="number" step="0.01" name="unit_cost[]" min="1" class="unit_cost" 
                           value="<?= htmlspecialchars($preselected_unit_cost) ?>" placeholder="0.00"
                           title="Original purchase price per unit of the asset">
                </td>
                <td data-label="Total Cost">
                    <input type="number" step="0.01" name="total_cost[]" min="1" readonly 
                           value="<?= $preselected_unit_cost ? $preselected_unit_cost : '' ?>" placeholder="Auto-calculated"
                           title="Automatically calculated: Quantity × Unit Cost">
                </td>
                <td data-label="Accumulated Depreciation">
                    <input type="number" step="0.01" name="accumulated_depreciation[]" min="1" placeholder="0.00"
                           title="Total depreciation accumulated over the asset's useful life">
                </td>
                <td data-label="Accumulated Impairment">
                    <input type="number" step="0.01" name="accumulated_impairment_losses[]" min="1" placeholder="0.00"
                           title="Total impairment losses recognized for this asset">
                </td>
                <td data-label="Carrying Amount">
                    <input type="number" step="0.01" name="carrying_amount[]" min="1" placeholder="0.00"
                           title="Current book value: Cost - Depreciation - Impairment">
                </td>
                <td data-label="Remarks">
                    <select name="remarks[]" class="form-select" title="Current condition status of the asset">
                        <option value="Unserviceable" selected>Unserviceable</option>
                    </select>
                </td>
                <td data-label="Sale">
                    <input type="text" name="sale[]" placeholder="Sale info"
                           title="Details if asset is to be sold (buyer, price, etc.)">
                </td>
                <td data-label="Transfer">
                    <input type="text" name="transfer[]" placeholder="Transfer info"
                           title="Details if asset is to be transferred (recipient, location)">
                </td>
                <td data-label="Destruction">
                    <input type="text" name="destruction[]" placeholder="Destruction info"
                           title="Details if asset is to be destroyed (method, date, reason)">
                </td>
                <td data-label="Others">
                    <input type="text" name="others[]" placeholder="Other disposal"
                           title="Other disposal methods not covered above">
                </td>
                <td data-label="Total">
                    <input type="number" step="0.01" name="total[]" min="1" placeholder="0.00"
                           title="Total disposal value or cost">
                </td>
                <td data-label="Appraised Value">
                    <input type="number" step="0.01" name="appraised_value[]" min="1" placeholder="0.00"
                           title="Current market value as determined by appraisal">
                </td>
                <td data-label="OR Number">
                    <input type="text" name="or_no[]" placeholder="OR Number"
                           title="Official Receipt number for any sales transaction">
                </td>
                <td data-label="Amount">
                    <input type="number" step="0.01" name="amount[]" min="1" placeholder="0.00"
                           title="Amount received from sale or disposal">
                </td>
                <td data-label="Department/Office">
                    <input type="text" name="dept_office[]" class="dept_office" value="<?= htmlspecialchars($preselected_office) ?>" readonly placeholder="Auto-filled"
                           title="Department/Office responsible for this asset">
                </td>
                <td data-label="Code">
                    <input type="text" name="code[]" placeholder="Code"
                           title="Internal classification or reference code">
                </td>
                <td data-label="Date Received">
                    <input type="date" name="date_received[]" value="<?= date('Y-m-d'); ?>"
                           title="Date when IIRUP form was received/processed">
                </td>
                <td data-label="Actions">
                    <button type="button" class="btn btn-sm btn-info edit-row-btn" 
                            title="Edit row details in modal">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </td>
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

<!-- Row Details Modal -->
<div class="modal fade" id="rowDetailsModal" tabindex="-1" aria-labelledby="rowDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rowDetailsModalLabel">
                    <i class="bi bi-pencil-square"></i> Edit Row Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <!-- Basic Information -->
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="bi bi-info-circle"></i> Basic Information
                        </h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Acquired</label>
                        <input type="date" class="form-control" id="modal_date_acquired">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Particulars/Articles</label>
                        <input type="text" class="form-control" id="modal_particulars" list="asset_descriptions" placeholder="Select or type asset description">
                        <input type="hidden" id="modal_asset_id">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Property No.</label>
                        <input type="text" class="form-control" id="modal_property_no" placeholder="Property number">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="modal_qty" min="1" max="1" placeholder="Qty">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Unit Cost</label>
                        <input type="number" class="form-control" id="modal_unit_cost" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Total Cost</label>
                        <input type="number" class="form-control" id="modal_total_cost" step="0.01" readonly placeholder="Auto-calculated">
                    </div>

                    <!-- Financial Information -->
                    <div class="col-12 mt-4">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="bi bi-currency-dollar"></i> Financial Information
                        </h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Accumulated Depreciation</label>
                        <input type="number" class="form-control" id="modal_accumulated_depreciation" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Accumulated Impairment</label>
                        <input type="number" class="form-control" id="modal_accumulated_impairment" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Carrying Amount</label>
                        <input type="number" class="form-control" id="modal_carrying_amount" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Remarks</label>
                        <select class="form-select" id="modal_remarks">
                            <option value="Unserviceable">Unserviceable</option>
                            </select>
                    </div>

                    <!-- Disposal Information -->
                    <div class="col-12 mt-4">
                        <h6 class="text-warning border-bottom pb-2 mb-3">
                            <i class="bi bi-recycle"></i> Disposal Information
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Sale</label>
                        <input type="text" class="form-control" id="modal_sale" placeholder="Sale details">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Transfer</label>
                        <input type="text" class="form-control" id="modal_transfer" placeholder="Transfer details">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Destruction</label>
                        <input type="text" class="form-control" id="modal_destruction" placeholder="Destruction details">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Others</label>
                        <input type="text" class="form-control" id="modal_others" placeholder="Other disposal">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Disposal</label>
                        <input type="number" class="form-control" id="modal_total" step="0.01" placeholder="0.00">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Appraised Value</label>
                        <input type="number" class="form-control" id="modal_appraised_value" step="0.01" placeholder="0.00">
                    </div>

                    <!-- Sales Record -->
                    <div class="col-12 mt-4">
                        <h6 class="text-info border-bottom pb-2 mb-3">
                            <i class="bi bi-receipt"></i> Sales Record
                        </h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">OR Number</label>
                        <input type="text" class="form-control" id="modal_or_no" placeholder="Official Receipt No.">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" id="modal_amount" step="0.01" placeholder="0.00">
                    </div>

                    <!-- Administrative Information -->
                    <div class="col-12 mt-4">
                        <h6 class="text-secondary border-bottom pb-2 mb-3">
                            <i class="bi bi-building"></i> Administrative Information
                        </h6>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Department/Office</label>
                        <input type="text" class="form-control" id="modal_dept_office" readonly placeholder="Auto-filled">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Code</label>
                        <input type="text" class="form-control" id="modal_code" placeholder="Classification code">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date Received</label>
                        <input type="date" class="form-control" id="modal_date_received">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="saveRowDetails">
                    <i class="bi bi-check-circle"></i> Save Changes
                </button>
            </div>
        </div>
    </div>
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
            <td data-label="Date Acquired">
                <input type="date" name="date_acquired[]" value="${today}" 
                       title="Date when the asset was originally acquired by the organization">
            </td>
            <td data-label="Particulars/Articles">
                <div class="d-flex align-items-center">
                    <input type="text" name="particulars[]" list="asset_descriptions" class="particulars flex-grow-1" placeholder="Select or type asset description"
                           title="Description of the asset/item being inspected for disposal">
                    <button type="button" class="btn btn-sm btn-danger ms-1 remove-asset" 
                            style="display: none;" title="Remove Asset">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
                <input type="hidden" name="asset_id[]" class="asset_id">
            </td>
            <td data-label="Property No">
                <input type="text" name="property_no[]" placeholder="Property Number"
                       title="Official property number or inventory tag assigned to the asset">
            </td>
            <td data-label="Quantity">
                <input type="number" name="qty[]" min="1" class="qty" max="1" placeholder="Qty"
                       title="Number of units of this asset being inspected">
            </td>
            <td data-label="Unit Cost">
                <input type="number" step="0.01" name="unit_cost[]" min="1" class="unit_cost" placeholder="0.00"
                       title="Original purchase price per unit of the asset">
            </td>
            <td data-label="Total Cost">
                <input type="number" step="0.01" name="total_cost[]" min="1" readonly placeholder="Auto-calculated"
                       title="Automatically calculated: Quantity × Unit Cost">
            </td>
            <td data-label="Accumulated Depreciation">
                <input type="number" step="0.01" name="accumulated_depreciation[]" min="1" placeholder="0.00"
                       title="Total depreciation accumulated over the asset's useful life">
            </td>
            <td data-label="Accumulated Impairment">
                <input type="number" step="0.01" name="accumulated_impairment_losses[]" min="1" placeholder="0.00"
                       title="Total impairment losses recognized for this asset">
            </td>
            <td data-label="Carrying Amount">
                <input type="number" step="0.01" name="carrying_amount[]" min="1" placeholder="0.00"
                       title="Current book value: Cost - Depreciation - Impairment">
            </td>
            <td data-label="Remarks">
                <select name="remarks[]" class="form-select" title="Current condition status of the asset">
                    <option value="Unserviceable" selected>Unserviceable</option>
                    <option value="Serviceable">Serviceable</option>
                </select>
            </td>
            <td data-label="Sale">
                <input type="text" name="sale[]" placeholder="Sale info"
                       title="Details if asset is to be sold (buyer, price, etc.)">
            </td>
            <td data-label="Transfer">
                <input type="text" name="transfer[]" placeholder="Transfer info"
                       title="Details if asset is to be transferred (recipient, location)">
            </td>
            <td data-label="Destruction">
                <input type="text" name="destruction[]" placeholder="Destruction info"
                       title="Details if asset is to be destroyed (method, date, reason)">
            </td>
            <td data-label="Others">
                <input type="text" name="others[]" placeholder="Other disposal"
                       title="Other disposal methods not covered above">
            </td>
            <td data-label="Total">
                <input type="number" step="0.01" name="total[]" min="1" placeholder="0.00"
                       title="Total disposal value or cost">
            </td>
            <td data-label="Appraised Value">
                <input type="number" step="0.01" name="appraised_value[]" min="1" placeholder="0.00"
                       title="Current market value as determined by appraisal">
            </td>
            <td data-label="OR Number">
                <input type="text" name="or_no[]" placeholder="OR Number"
                       title="Official Receipt number for any sales transaction">
            </td>
            <td data-label="Amount">
                <input type="number" step="0.01" name="amount[]" min="1" placeholder="0.00"
                       title="Amount received from sale or disposal">
            </td>
            <td data-label="Department/Office">
                <input type="text" name="dept_office[]" class="dept_office" readonly placeholder="Auto-filled"
                       title="Department/Office responsible for this asset">
            </td>
            <td data-label="Code">
                <input type="text" name="code[]" placeholder="Code"
                       title="Internal classification or reference code">
            </td>
            <td data-label="Date Received">
                <input type="date" name="date_received[]" value="${today}"
                       title="Date when IIRUP form was received/processed">
            </td>
            <td data-label="Actions">
                <button type="button" class="btn btn-sm btn-info edit-row-btn" 
                        title="Edit row details in modal">
                    <i class="bi bi-pencil-square"></i>
                </button>
            </td>
        `;
        
        tbody.appendChild(newRow);
        
        // Attach event listeners to the new row
        attachRowEventListeners(newRow);
        updateRowVisibility();
        
        // Update titles for the new row
        updateInputTitles();
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
    
    // Dynamic title updates to show current values
    function updateInputTitles() {
        document.querySelectorAll('.excel-table input, .excel-table select').forEach(input => {
            const originalTitle = input.getAttribute('data-original-title');
            if (!originalTitle) {
                // Store original title on first run
                input.setAttribute('data-original-title', input.title);
            }
            
            const baseTitle = input.getAttribute('data-original-title') || '';
            const currentValue = input.value.trim();
            
            if (currentValue) {
                input.title = `${baseTitle}\nCurrent value: ${currentValue}`;
            } else {
                input.title = baseTitle;
            }
        });
    }

    // Update titles on input change
    document.addEventListener('input', function(e) {
        if (e.target.matches('.excel-table input, .excel-table select')) {
            updateInputTitles();
        }
        
        // Existing calculation logic
        if (e.target.name === 'qty[]' || e.target.name === 'unit_cost[]') {
            let row = e.target.closest('tr');
            let qty = parseFloat(row.querySelector('input[name="qty[]"]').value) || 0;
            let unitCost = parseFloat(row.querySelector('input[name="unit_cost[]"]').value) || 0;
            row.querySelector('input[name="total_cost[]"]').value = (qty * unitCost).toFixed(2);
        }
    });

    // Update titles on change (for selects)
    document.addEventListener('change', function(e) {
        if (e.target.matches('.excel-table select')) {
            updateInputTitles();
        }
    });

    // Initialize titles
    updateInputTitles();
    
    // Initialize row visibility
    updateRowVisibility();
    
    // Handle preselected asset from QR scan
    <?php if ($preselected_asset): ?>
        // Add preselected asset to selected set
        selectedAssetIds.add('<?= $preselected_asset['id'] ?>');
        updateDatalist();
        // Update titles after preselection
        setTimeout(updateInputTitles, 100);
    <?php endif; ?>

    // Modal functionality
    let currentEditingRow = null;

    // Handle edit row button clicks
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-row-btn')) {
            currentEditingRow = e.target.closest('tr');
            openRowDetailsModal(currentEditingRow);
        }
    });

    function openRowDetailsModal(row) {
        // Get all input values from the row
        const inputs = row.querySelectorAll('input, select');
        
        // Populate modal fields
        document.getElementById('modal_date_acquired').value = row.querySelector('input[name="date_acquired[]"]').value || '';
        document.getElementById('modal_particulars').value = row.querySelector('input[name="particulars[]"]').value || '';
        document.getElementById('modal_asset_id').value = row.querySelector('input[name="asset_id[]"]').value || '';
        document.getElementById('modal_property_no').value = row.querySelector('input[name="property_no[]"]').value || '';
        document.getElementById('modal_qty').value = row.querySelector('input[name="qty[]"]').value || '';
        document.getElementById('modal_unit_cost').value = row.querySelector('input[name="unit_cost[]"]').value || '';
        document.getElementById('modal_total_cost').value = row.querySelector('input[name="total_cost[]"]').value || '';
        document.getElementById('modal_accumulated_depreciation').value = row.querySelector('input[name="accumulated_depreciation[]"]').value || '';
        document.getElementById('modal_accumulated_impairment').value = row.querySelector('input[name="accumulated_impairment_losses[]"]').value || '';
        document.getElementById('modal_carrying_amount').value = row.querySelector('input[name="carrying_amount[]"]').value || '';
        document.getElementById('modal_remarks').value = 'Unserviceable';
        document.getElementById('modal_sale').value = row.querySelector('input[name="sale[]"]').value || '';
        document.getElementById('modal_transfer').value = row.querySelector('input[name="transfer[]"]').value || '';
        document.getElementById('modal_destruction').value = row.querySelector('input[name="destruction[]"]').value || '';
        document.getElementById('modal_others').value = row.querySelector('input[name="others[]"]').value || '';
        document.getElementById('modal_total').value = row.querySelector('input[name="total[]"]').value || '';
        document.getElementById('modal_appraised_value').value = row.querySelector('input[name="appraised_value[]"]').value || '';
        document.getElementById('modal_or_no').value = row.querySelector('input[name="or_no[]"]').value || '';
        document.getElementById('modal_amount').value = row.querySelector('input[name="amount[]"]').value || '';
        document.getElementById('modal_dept_office').value = row.querySelector('input[name="dept_office[]"]').value || '';
        document.getElementById('modal_code').value = row.querySelector('input[name="code[]"]').value || '';
        document.getElementById('modal_date_received').value = row.querySelector('input[name="date_received[]"]').value || '';

        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('rowDetailsModal'));
        modal.show();
    }

    // Handle save button in modal
    document.getElementById('saveRowDetails').addEventListener('click', function() {
        if (currentEditingRow) {
            // Get previous asset ID to remove from selected set
            const previousAssetId = currentEditingRow.querySelector('input[name="asset_id[]"]').value;
            const newAssetId = document.getElementById('modal_asset_id').value;
            
            // Update selectedAssetIds Set
            if (previousAssetId && previousAssetId !== newAssetId) {
                selectedAssetIds.delete(previousAssetId);
            }
            if (newAssetId && newAssetId !== previousAssetId) {
                selectedAssetIds.add(newAssetId);
            }
            
            // Update row with modal values
            currentEditingRow.querySelector('input[name="date_acquired[]"]').value = document.getElementById('modal_date_acquired').value;
            currentEditingRow.querySelector('input[name="particulars[]"]').value = document.getElementById('modal_particulars').value;
            currentEditingRow.querySelector('input[name="asset_id[]"]').value = document.getElementById('modal_asset_id').value;
            currentEditingRow.querySelector('input[name="property_no[]"]').value = document.getElementById('modal_property_no').value;
            currentEditingRow.querySelector('input[name="qty[]"]').value = document.getElementById('modal_qty').value;
            currentEditingRow.querySelector('input[name="unit_cost[]"]').value = document.getElementById('modal_unit_cost').value;
            currentEditingRow.querySelector('input[name="total_cost[]"]').value = document.getElementById('modal_total_cost').value;
            currentEditingRow.querySelector('input[name="accumulated_depreciation[]"]').value = document.getElementById('modal_accumulated_depreciation').value;
            currentEditingRow.querySelector('input[name="accumulated_impairment_losses[]"]').value = document.getElementById('modal_accumulated_impairment').value;
            currentEditingRow.querySelector('input[name="carrying_amount[]"]').value = document.getElementById('modal_carrying_amount').value;
            currentEditingRow.querySelector('select[name="remarks[]"]').value = document.getElementById('modal_remarks').value;
            currentEditingRow.querySelector('input[name="sale[]"]').value = document.getElementById('modal_sale').value;
            currentEditingRow.querySelector('input[name="transfer[]"]').value = document.getElementById('modal_transfer').value;
            currentEditingRow.querySelector('input[name="destruction[]"]').value = document.getElementById('modal_destruction').value;
            currentEditingRow.querySelector('input[name="others[]"]').value = document.getElementById('modal_others').value;
            currentEditingRow.querySelector('input[name="total[]"]').value = document.getElementById('modal_total').value;
            currentEditingRow.querySelector('input[name="appraised_value[]"]').value = document.getElementById('modal_appraised_value').value;
            currentEditingRow.querySelector('input[name="or_no[]"]').value = document.getElementById('modal_or_no').value;
            currentEditingRow.querySelector('input[name="amount[]"]').value = document.getElementById('modal_amount').value;
            currentEditingRow.querySelector('input[name="dept_office[]"]').value = document.getElementById('modal_dept_office').value;
            currentEditingRow.querySelector('input[name="code[]"]').value = document.getElementById('modal_code').value;
            currentEditingRow.querySelector('input[name="date_received[]"]').value = document.getElementById('modal_date_received').value;

            // Update tooltips after changes
            updateInputTitles();
            
            // Update datalist and remove button visibility
            updateDatalist();
            updateRemoveButtonVisibility(currentEditingRow);

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('rowDetailsModal'));
            modal.hide();
            
            // Reset current editing row
            currentEditingRow = null;
        }
    });

    // Auto-calculate total cost in modal
    document.getElementById('modal_qty').addEventListener('input', calculateModalTotalCost);
    document.getElementById('modal_unit_cost').addEventListener('input', calculateModalTotalCost);

    function calculateModalTotalCost() {
        const qty = parseFloat(document.getElementById('modal_qty').value) || 0;
        const unitCost = parseFloat(document.getElementById('modal_unit_cost').value) || 0;
        document.getElementById('modal_total_cost').value = (qty * unitCost).toFixed(2);
    }

    // Handle asset selection in modal
    document.getElementById('modal_particulars').addEventListener('input', function() {
        const description = this.value.trim();
        if (!description) {
            // Clear fields if description is empty
            document.getElementById('modal_asset_id').value = '';
            document.getElementById('modal_property_no').value = '';
            document.getElementById('modal_unit_cost').value = '';
            document.getElementById('modal_dept_office').value = '';
            document.getElementById('modal_date_acquired').value = '';
            return;
        }

        // Find matching asset from datalist
        const options = document.querySelectorAll('#asset_descriptions option');
        let selectedAsset = null;
        
        for (const option of options) {
            if (option.value === description) {
                selectedAsset = {
                    id: option.getAttribute('data-asset-id'),
                    description: option.value,
                };
                break;
            }
        }

        if (selectedAsset && selectedAsset.id) {
            // Check if asset is already selected in another row
            const currentAssetId = document.getElementById('modal_asset_id').value;
            if (selectedAssetIds.has(selectedAsset.id) && selectedAsset.id !== currentAssetId) {
                alert('This asset is already selected in another row. Please choose a different asset.');
                this.value = '';
                return;
            }
            
            // Fetch asset details via AJAX
            fetchAssetDetailsForModal(selectedAsset.id);
        }
    });

    function fetchAssetDetailsForModal(assetId) {
        // Create a simple AJAX request to get asset details
        fetch('get_asset_details.php?id=' + assetId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Auto-fill modal fields with asset data
                    document.getElementById('modal_asset_id').value = data.asset.id;
                    document.getElementById('modal_property_no').value = data.asset.inventory_tag || '';
                    document.getElementById('modal_unit_cost').value = data.asset.value || '';
                    document.getElementById('modal_qty').value = '1';
                    document.getElementById('modal_dept_office').value = data.asset.office_name || '';
                    
                    // Auto-fill date acquired from asset's acquisition_date
                    if (data.asset.acquisition_date) {
                        document.getElementById('modal_date_acquired').value = data.asset.acquisition_date;
                    }
                    
                    // Calculate total cost
                    calculateModalTotalCost();
                }
            })
            .catch(error => {
                console.error('Error fetching asset details:', error);
            });
    }

    // Update remove button visibility for a specific row
    function updateRemoveButtonVisibility(row) {
        const assetIdInput = row.querySelector('.asset_id');
        const removeBtn = row.querySelector('.remove-asset');
        
        if (removeBtn) {
            if (assetIdInput && assetIdInput.value) {
                removeBtn.style.display = 'inline-block';
            } else {
                removeBtn.style.display = 'none';
            }
        }
    }
</script>