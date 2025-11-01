<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';


if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit();
}

// Get form ID from URL
$form_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch latest ITR header or create default values
$itr = null;
$itr_sql = "SELECT itr_id, header_image, entity_name, fund_cluster, from_accountable_officer, to_accountable_officer, itr_no, `date`, transfer_type, reason_for_transfer, approved_by, approved_designation, approved_date, released_by, released_designation, released_date, received_by, received_designation, received_date FROM itr_form ORDER BY itr_id DESC LIMIT 1";
$res = $conn->query($itr_sql);
if ($res && $res->num_rows > 0) {
  $itr = $res->fetch_assoc();
} else {
  // Create default ITR structure if none exists
  $itr = [
    'itr_id' => 0,
    'header_image' => '',
    'entity_name' => '',
    'fund_cluster' => '',
    'from_accountable_officer' => '',
    'to_accountable_officer' => '',
    'itr_no' => '',
    'date' => date('Y-m-d'),
    'transfer_type' => '',
    'reason_for_transfer' => '',
    'approved_by' => '',
    'approved_designation' => '',
    'approved_date' => '',
    'released_by' => '',
    'released_designation' => '',
    'released_date' => '',
    'received_by' => '',
    'received_designation' => '',
    'received_date' => ''
  ];
}

$itr_id = (int)$itr['itr_id'];

// Fetch active ITR template for dynamic preview
$itr_template = '';
if ($st_fmt = $conn->prepare("SELECT format_template FROM tag_formats WHERE tag_type = 'itr_no' AND is_active = 1 LIMIT 1")) {
  $st_fmt->execute();
  $rs_fmt = $st_fmt->get_result();
  if ($rs_fmt && ($r = $rs_fmt->fetch_assoc())) { $itr_template = $r['format_template'] ?? ''; }
  $st_fmt->close();
}

// Always start with a BLANK items table for new ITR entries.
// Do NOT load items from the most recent ITR submission.
// If an asset_id is provided via GET (from Transfer), preselection logic below will add a single row.
$items = [];

// Handle asset pre-selection from URL parameters (from employee transfer)
$preselected_asset = null;
$preselected_employee_name = '';

// Helper function to map status to PPE condition
function getConditionFromStatus($status)
{
  switch (strtolower($status)) {
    case 'available':
    case 'serviceable':
      return 'Serviceable';
    case 'unserviceable':
      return 'Unserviceable';
    case 'borrowed':
      return 'On Loan';
    case 'red_tagged':
      return 'For Disposal';
    default:
      return 'Fair';
  }
}

if (isset($_GET['asset_id']) && !empty($_GET['asset_id'])) {
  $asset_id = intval($_GET['asset_id']);
  
  // Get additional parameters from URL if available
  $url_description = $_GET['description'] ?? '';
  $url_acquisition_date = $_GET['acquisition_date'] ?? '';
  $url_property_no = $_GET['property_no'] ?? '';
  $url_unit_price = $_GET['unit_price'] ?? '';
  $url_status = $_GET['status'] ?? '';
  $preselected_employee_name = $_GET['employee_name'] ?? '';
  
  // Use URL parameters if available, otherwise fetch from database
  if (!empty($url_description) && !empty($url_acquisition_date)) {
    // Use data from URL parameters (from QR scan)
    $preselected_asset = [
      'id' => $asset_id,
      'description' => $url_description,
      'property_no' => $url_property_no,
      'value' => $url_unit_price,
      'acquisition_date' => $url_acquisition_date,
      'status' => $url_status
    ];
  } else {
    // Fallback to database query
    $stmt = $conn->prepare("SELECT id, description, property_no, value, acquisition_date, status FROM assets WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $asset_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
      $preselected_asset = $row;
    }
    $stmt->close();
  }
  
  // Add the preselected asset as first item if we have asset data
  if ($preselected_asset && empty($items)) {
    // Use ICS/PAR number instead of property number if available
    $display_property_no = $preselected_asset['property_no'] ?? '';
    
    // Determine if this should be ICS or PAR based on value
    $asset_value = floatval($preselected_asset['value'] ?? 0);
    $ics_par_display = '';
    if ($asset_value >= 50000) {
      $ics_par_display = 'PAR: ' . $display_property_no;
    } else {
      $ics_par_display = 'ICS: ' . $display_property_no;
    }
    
    $items[] = [
      'item_id' => 0,
      'itr_id' => $itr_id,
      'date_acquired' => $preselected_asset['acquisition_date'] ?? '',
      'property_no' => $ics_par_display, // Use ICS/PAR format instead of property_no
      'asset_id' => $preselected_asset['id'],
      'description' => $preselected_asset['description'],
      'amount' => $preselected_asset['value'] ?? '',
      'condition_of_PPE' => getConditionFromStatus($preselected_asset['status'] ?? '')
    ];
  }
  
  // Pre-populate the "From Accountable Officer" field if employee name is provided
  if (!empty($preselected_employee_name)) {
    $itr['from_accountable_officer'] = $preselected_employee_name;
  }
}

// Debug output (remove in production)
if (isset($_GET['asset_id']) && !empty($_GET['asset_id'])) {
  echo "<!-- DEBUG: Asset ID: " . htmlspecialchars($_GET['asset_id'] ?? 'none') . " -->\n";
  echo "<!-- DEBUG: Employee Name: " . htmlspecialchars($preselected_employee_name) . " -->\n";
  echo "<!-- DEBUG: From Accountable Officer: " . htmlspecialchars($itr['from_accountable_officer']) . " -->\n";
  if (!empty($items)) {
    echo "<!-- DEBUG: First Item Description: " . htmlspecialchars($items[0]['description'] ?? 'none') . " -->\n";
    echo "<!-- DEBUG: First Item Property No: " . htmlspecialchars($items[0]['property_no'] ?? 'none') . " -->\n";
  }
}


// Build assets list for description datalist (for auto-fill)
$assets = [];
$assets_q = $conn->query("SELECT a.id, a.description, a.property_no, a.value, a.acquisition_date FROM assets a WHERE a.type='asset' ORDER BY a.description ASC");
while ($r = $assets_q->fetch_assoc()) {
  $assets[] = $r;
}

// Build employees list for To Accountable Officer datalist (only permanent employees)
$employees = [];
$emp_q = $conn->query("SELECT name FROM employees WHERE status = 'permanent' ORDER BY name ASC");
if ($emp_q) {
  while ($er = $emp_q->fetch_assoc()) {
    $employees[] = $er['name'];
  }
}

// Build employees with MR assets for From Person Accountable datalist
$employees_with_assets = [];
$emp_assets_q = $conn->query("SELECT DISTINCT e.employee_id, e.name FROM employees e INNER JOIN assets a ON a.employee_id = e.employee_id WHERE e.status = 'permanent' AND a.type = 'asset' ORDER BY e.name ASC");
if ($emp_assets_q) {
  while ($ea = $emp_assets_q->fetch_assoc()) {
    $employees_with_assets[] = $ea;
  }
}

// Offices for dropdown to drive Entity Name and {OFFICE} in preview
$itr_offices = [];
$itr_off_res = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name");
if ($itr_off_res && $itr_off_res->num_rows > 0) {
  while ($or = $itr_off_res->fetch_assoc()) { $itr_offices[] = $or; }
}


// Header image handling - simplified like ics_form.php

?>

<?php
// Show flash message if present
if (!empty($_SESSION['flash'])) {
  $flash = $_SESSION['flash'];
  unset($_SESSION['flash']);
  $type = htmlspecialchars($flash['type'] ?? 'info');
  $message = htmlspecialchars($flash['message'] ?? '');
  echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">'
    . $message
    . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
?>

<style>
    body { background: #f4f6f9; }
    .itr-page-wrapper { padding: 2.5rem 0 3.5rem; background: linear-gradient(135deg, rgba(226,232,240,0.5), rgba(148,163,184,0.25)); }
    .itr-paper { position: relative; max-width: 1050px; margin: 0 auto; background: #fff; border: 1px solid #d9dee6; border-radius: 14px; padding: 2.75rem 3rem; box-shadow: 0 18px 45px rgba(15,23,42,0.15); }
    .itr-paper::before { content: ""; position: absolute; inset: 14px; border: 1px solid rgba(148,163,184,0.25); border-radius: 10px; pointer-events: none; }
    .itr-paper .form-control, .itr-paper .form-select { padding: 0.35rem 0.55rem; font-size: 0.85rem; min-height: 2.1rem; border-radius: 6px; }
    .itr-section-title { font-size: 0.82rem; letter-spacing: 0.12em; text-transform: uppercase; color: #6c757d; font-weight: 700; margin-bottom: 0.85rem; }
    .itr-divider { margin: 1.75rem 0; border: none; border-top: 2px solid rgba(100,116,139,0.35); }
    .itr-table-wrapper { border: 1px solid #ced4da; border-radius: 10px; overflow: hidden; background: #ffffff; }
    .itr-table-wrapper table { margin-bottom: 0; font-size: 0.74rem; }
    .itr-table-wrapper thead th { background: #f8fafc; font-size: 0.72rem; vertical-align: middle; padding: 0.45rem 0.35rem; color: #334155; }
    .itr-table-wrapper tbody td, .itr-table-wrapper tfoot td { padding: 0.35rem 0.35rem; }
    .itr-table-wrapper input.form-control, .itr-table-wrapper select.form-select { padding: 0.22rem 0.4rem; min-height: 1.7rem; font-size: 0.72rem; }
    .itr-table-wrapper .btn, .itr-table-wrapper .input-group-text { font-size: 0.72rem; padding: 0.2rem 0.45rem; }
    .itr-signature-table input.form-control { border: none; border-radius: 0; border-bottom: 1px solid #adb5bd; background: transparent; }
    .itr-signature-table input.form-control:focus { box-shadow: none; border-color: #495057; }
    @media (max-width: 991.98px) { .itr-page-wrapper { padding: 1.75rem 1rem 2.5rem; } .itr-paper { padding: 2rem 1.6rem; border-radius: 10px; } .itr-paper::before { inset: 10px; border-radius: 8px; } }
    @media print { body { background: #ffffff !important; } .itr-page-wrapper { padding: 0; background: transparent; } .itr-paper { max-width: 100%; padding: 20mm; border-radius: 0; border: none; box-shadow: none; } .itr-paper::before, .navigation-controls, .alert, .btn { display: none !important; } }
</style>

<div class="itr-page-wrapper">
    <div class="navigation-controls d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Inventory Transfer Receipt (ITR) Form</h4>
        <a href="saved_itr.php" class="btn btn-info">
            <i class="bi bi-folder-check"></i> View Saved ITR
        </a>
    </div>

    <div class="itr-paper">
        <form id="itrItemsForm" method="POST" action="save_itr_items.php" enctype="multipart/form-data" class="w-100" onsubmit="return checkItrDuplicateDescriptions()">
            <input type="hidden" name="itr_id" value="<?= (int)$itr_id ?>">
            <input type="hidden" name="form_id" value="<?= (int)$form_id ?>">

            <div class="mb-4 text-center">
                <?php if (!empty($itr['header_image'])): ?>
                    <img src="../img/<?= htmlspecialchars($itr['header_image']) ?>" class="img-fluid mb-2" style="max-width: 100%; height: auto; object-fit: contain;">
                    <input type="hidden" name="header_image" value="<?= htmlspecialchars($itr['header_image']) ?>">
                <?php else: ?>
                    <p class="text-muted mb-0">No header image available</p>
                <?php endif; ?>
            </div>

            <hr class="itr-divider">

            <div class="itr-section-title">ITR Header Details</div>
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Office <span class="text-danger">*</span></label>
                    <select id="itr_office" name="office_id" class="form-select shadow-sm">
                        <option value="" disabled selected>Select office...</option>
                        <?php foreach ($itr_offices as $o): ?>
                            <option value="<?= (int)$o['id'] ?>" <?= (isset($itr['office_id']) && (string)$itr['office_id'] === (string)$o['id']) ? 'selected' : '' ?>><?= htmlspecialchars($o['office_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Applies to entity name and tag preview.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Entity Name <span class="text-danger">*</span></label>
                    <input type="text" id="entity_name" name="entity_name" class="form-control shadow-sm" value="<?= htmlspecialchars($itr['entity_name'] ?? '') ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Fund Cluster <span class="text-danger">*</span></label>
                    <input type="text" id="fund_cluster" name="fund_cluster" class="form-control shadow-sm" value="<?= htmlspecialchars($_GET['fund_cluster'] ?? ($itr['fund_cluster'] ?? '')) ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Date Prepared</label>
                    <input type="date" id="date" name="date" class="form-control shadow-sm" value="<?= htmlspecialchars($itr['date']) ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">From Accountable Officer <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="from_accountable_officer" name="from_accountable_officer" class="form-control shadow-sm" list="employeesWithAssetsList" placeholder="Select employee with MR assets..." value="<?= htmlspecialchars($itr['from_accountable_officer']) ?>" required>
                        <button type="button" class="btn btn-outline-secondary" id="clear_from_accountable" title="Clear field" aria-label="Clear From Accountable Officer"><i class="bi bi-x-circle"></i></button>
                    </div>
                    <datalist id="employeesWithAssetsList">
                        <?php foreach ($employees_with_assets as $emp): ?>
                            <option value="<?= htmlspecialchars($emp['name']) ?>" data-employee-id="<?= $emp['employee_id'] ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <small class="text-muted">Assets list filters to this officer.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">To Accountable Officer <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" id="to_accountable_officer" name="to_accountable_officer" class="form-control shadow-sm" list="employeesList" placeholder="Type to search employees..." value="<?= htmlspecialchars($itr['to_accountable_officer']) ?>" required>
                        <button type="button" class="btn btn-outline-secondary" id="clear_to_accountable" title="Clear field" aria-label="Clear To Accountable Officer"><i class="bi bi-x-circle"></i></button>
                    </div>
                    <datalist id="employeesList">
                        <?php foreach ($employees as $ename): ?>
                            <option value="<?= htmlspecialchars($ename) ?>"></option>
                        <?php endforeach; ?>
                    </datalist>
                    <small class="text-muted">Auto-fills Received By.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">ITR No. (Auto-generated)</label>
                    <div class="input-group shadow-sm">
                        <input type="text" id="itr_no" name="itr_no" class="form-control border-0" value="<?= previewTag('itr_no') ?>" readonly>
                        <span class="input-group-text bg-light border-0"><i class="bi bi-magic" title="Auto-generated"></i></span>
                    </div>
                    <small class="text-muted">Assigned automatically on save.</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">End User <span class="text-danger">*</span></label>
                    <input type="text" id="end_user" name="end_user" class="form-control shadow-sm" placeholder="Enter end user name..." value="<?= htmlspecialchars($itr['end_user'] ?? '') ?>" required>
                    <small class="text-muted">Will update all transferred assets.</small>
                </div>
                <div class="col-md-8">
                    <label class="form-label fw-semibold d-block">Transfer Type <span class="text-danger">*</span></label>
                    <div class="d-flex flex-wrap gap-3">
                        <?php
                        $transferOptions = ['Donation', 'Reassignment', 'Relocate', 'Others'];
                        $selectedType = in_array($itr['transfer_type'] ?? '', $transferOptions, true) ? $itr['transfer_type'] : 'Reassignment';
                        foreach ($transferOptions as $option): ?>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="transfer_type" id="tt_<?= strtolower($option) ?>" value="<?= $option ?>" <?= ($selectedType === $option) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="tt_<?= strtolower($option) ?>"><?= $option ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div id="transfer_type_other_wrap" class="mt-2" style="display: <?= ($selectedType === 'Others') ? 'block' : 'none' ?>;">
                        <input type="text" id="transfer_type_other" name="transfer_type_other" class="form-control shadow-sm" placeholder="Specify other transfer type" value="<?= htmlspecialchars($itr['transfer_type_other'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <div class="itr-section-title mt-5">Transferred Assets</div>
            <div class="itr-table-wrapper mb-4">
                <table class="table table-bordered text-center align-middle" id="itrItemsTable">
                    <thead>
                        <tr>
                            <th>Date Acquired</th>
                            <th>Item No.</th>
                            <th>ICS / PAR No.</th>
                            <th style="width: 32%;">Description</th>
                            <th>Unit Price</th>
                            <th>Condition</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="itr-items-body">
                        <?php
                        $rowCount = max(1, count($items));
                        for ($i = 0; $i < $rowCount; $i++):
                            $item = $items[$i] ?? ['date_acquired' => '', 'property_no' => '', 'description' => '', 'amount' => '', 'condition_of_PPE' => ''];
                        ?>
                            <tr data-row-index="<?= $i ?>">
                                <td><input type="date" name="date_acquired[]" class="form-control shadow-sm" value="<?= htmlspecialchars($item['date_acquired']) ?>" required></td>
                                <td><input type="text" name="item_no[]" class="form-control shadow-sm item-no-field" value="<?= $i + 1 ?>" readonly></td>
                                <td><input type="text" name="property_no[]" class="form-control shadow-sm property-field" value="<?= htmlspecialchars($item['property_no']) ?>" readonly></td>
                                <td class="position-relative">
                                    <input type="hidden" name="asset_id[]" value="<?= htmlspecialchars($item['asset_id'] ?? '') ?>" class="asset-id-field">
                                    <input type="text" name="description[]" class="form-control shadow-sm description-field" list="assetsList" placeholder="Search description or property number" value="<?= htmlspecialchars($item['description']) ?>" required>
                                    <button type="button" class="clear-description" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: transparent; border: none; font-weight: bold; font-size: 1rem; color: #888; cursor: pointer;">&times;</button>
                                </td>
                                <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" step="0.01" name="amount[]" class="form-control shadow-sm amount-field text-end" value="<?= htmlspecialchars($item['amount']) ?>" style="padding-left: 1.5rem;" required></td>
                                <td><input type="text" name="condition_of_PPE[]" class="form-control shadow-sm condition-field" value="<?= htmlspecialchars($item['condition_of_PPE']) ?>" required></td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <?php if ($i === 0): ?>
                                            <button type="button" class="btn btn-sm btn-warning clear-row" title="Clear Row"><i class="bi bi-eraser"></i></button>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Row"><i class="bi bi-trash"></i></button>
                                            <button type="button" class="btn btn-sm btn-warning clear-row" title="Clear Row"><i class="bi bi-eraser"></i></button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endfor; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end fw-semibold">Total Amount:</td>
                            <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" id="grandTotal" class="form-control text-end fw-bold shadow-sm" style="padding-left: 1.5rem;" readonly></td>
                            <td colspan="2" class="text-start"><button type="button" id="addRowBtn" class="btn btn-primary btn-sm">+ Add Row</button></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <datalist id="assetsList"></datalist>
            <script type="application/json" id="allAssetsData">
                <?php
                $all_assets = [];
                $assets_sql = "
                    SELECT a.id, a.description, a.property_no, a.acquisition_date, a.value, a.status, a.employee_id,
                           a.ics_id, a.par_id,
                           f.fund_cluster AS ics_fund_cluster,
                           p.fund_cluster AS par_fund_cluster
                    FROM assets a
                    LEFT JOIN ics_form f ON a.ics_id = f.id
                    LEFT JOIN par_form p on a.par_id = p.id
                    WHERE a.type='asset' AND a.employee_id IS NOT NULL
                    ORDER BY a.description ASC
                ";
                if ($assets_q = $conn->query($assets_sql)) {
                    while ($a = $assets_q->fetch_assoc()) {
                        $all_assets[] = $a;
                    }
                }
                echo json_encode($all_assets);
                ?>
            </script>

            <div class="itr-section-title mt-5">Reason for Transfer</div>
            <div class="mb-4">
                <textarea id="reason_for_transfer" name="reason_for_transfer" class="form-control shadow-sm" rows="3" placeholder="Enter reason for transfer..." required><?= htmlspecialchars($itr['reason_for_transfer'] ?? '') ?></textarea>
            </div>

            <div class="itr-section-title mb-3">Signatories</div>
            <table class="table table-borderless itr-signature-table" style="width: 100%; text-align: center;">
                <thead class="text-muted text-uppercase small fw-semibold">
                    <tr>
                        <td style="width: 25%; text-align: left;">Approved By</td>
                        <td style="width: 25%; text-align: left;">Released By</td>
                        <td style="width: 25%; text-align: left;">Received By</td>
                        <td style="width: 25%; text-align: left;">Designation & Date</td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="px-2">
                            <input type="text" name="approved_by" class="form-control text-center fw-semibold" value="<?= htmlspecialchars($itr['approved_by'] ?? '') ?>" required>
                            <input type="text" name="approved_designation" class="form-control text-center" value="<?= htmlspecialchars($itr['approved_designation'] ?? '') ?>" placeholder="Designation" required>
                            <input type="date" name="approved_date" class="form-control text-center" value="<?= htmlspecialchars($itr['approved_date'] ?? '') ?>">
                        </td>
                        <td class="px-2">
                            <input type="text" name="released_by" class="form-control text-center fw-semibold" value="<?= htmlspecialchars($itr['released_by'] ?? '') ?>" required>
                            <input type="text" name="released_designation" class="form-control text-center" value="<?= htmlspecialchars($itr['released_designation'] ?? '') ?>" placeholder="Designation" required>
                            <input type="date" name="released_date" class="form-control text-center" value="<?= htmlspecialchars($itr['released_date'] ?? '') ?>">
                        </td>
                        <td class="px-2">
                            <input type="text" id="received_by" name="received_by" class="form-control text-center fw-semibold" list="employeesList" value="<?= htmlspecialchars($itr['received_by'] ?? '') ?>" required>
                            <input type="text" name="received_designation" class="form-control text-center" value="<?= htmlspecialchars($itr['received_designation'] ?? '') ?>" placeholder="Designation" required>
                            <input type="date" name="received_date" class="form-control text-center" value="<?= htmlspecialchars($itr['received_date'] ?? '') ?>">
                        </td>
                        <td class="px-2 align-middle">
                            <small class="text-muted">Ensure designation and dates are filled for each signer.</small>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <small class="text-muted"><span class="text-danger">*</span> Required fields</small>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>

<?php include 'modals/par_duplicate_modal.php'; ?>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Dynamic ITR No preview based on Date field
    const ITR_TEMPLATE = <?= json_encode($itr_template) ?>;
    function formatFromDate(tpl, dateStr){
      const d = dateStr ? new Date(dateStr) : new Date();
      const Y = d.getFullYear().toString();
      const M = String(d.getMonth()+1).padStart(2,'0');
      const D = String(d.getDate()).padStart(2,'0');
      let out = (tpl||'');
      out = out.replace(/\{YYYY\}|YYYY/g, Y)
               .replace(/\{YY\}|YY/g, Y.slice(-2))
               .replace(/\{MM\}|MM/g, M)
               .replace(/\{DD\}|DD/g, D)
               .replace(/\{YYYYMM\}|YYYYMM/g, Y+M)
               .replace(/\{YYYYMMDD\}|YYYYMMDD/g, Y+M+D);
      // pad digits as preview next (1)
      out = out.replace(/\{(#+)\}/g, (m, hashes)=>{ const w=hashes.length; return '0'.repeat(Math.max(0,w-1))+'1'; });
      return out.replace(/--+/g,'-').replace(/^-|-$/g,'');
    }
    function computeItrPreview(){
      const field = document.getElementById('itr_no');
      if (!field) return;
      // Determine office display from dropdown or typed entity name
      const sel = document.getElementById('itr_office');
      const en = document.getElementById('entity_name');
      let officeName = (en && en.value.trim()) ? en.value.trim() : 'OFFICE';
      if ((!officeName || officeName === 'OFFICE') && sel) {
        const opt = sel.options[sel.selectedIndex];
        const txt = opt ? (opt.text || '') : '';
        if (sel.value) officeName = (txt || '').trim() || 'OFFICE';
      }
      // Build base from template and selected date, then replace OFFICE tokens
      const dateInput = document.getElementById('date');
      const base = formatFromDate(ITR_TEMPLATE || (field.value||''), dateInput ? dateInput.value : '');
      let updated = String(base).replace(/\bOFFICE\b|\{OFFICE\}/g, officeName);
      updated = updated.replace(/[{}]/g,'');
      field.value = updated;
    }
    const dateInput = document.getElementById('date');
    if (dateInput) dateInput.addEventListener('change', computeItrPreview);
    computeItrPreview();
    // Office dropdown: autofill entity name and update preview
    const itrOfficeSel = document.getElementById('itr_office');
    const entityNameInput = document.getElementById('entity_name');
    if (itrOfficeSel) {
      itrOfficeSel.addEventListener('change', function(){
        const opt = this.options[this.selectedIndex];
        const txt = opt ? (opt.text || '') : '';
        if (entityNameInput) entityNameInput.value = txt;
        computeItrPreview();
      });
    }
    if (entityNameInput) {
      entityNameInput.addEventListener('input', computeItrPreview);
    }
    // Toggle other transfer type field (radio-based)
    const ttOthers = document.getElementById('tt_others');
    const ttOtherWrap = document.getElementById('transfer_type_other_wrap');
    const ttOtherInput = document.getElementById('transfer_type_other');

    function updateOtherVisibility() {
      if (!ttOthers || !ttOtherWrap) return;
      const show = ttOthers.checked;
      ttOtherWrap.style.display = show ? 'block' : 'none';
      if (!show && ttOtherInput) ttOtherInput.value = '';
    }
    if (ttOthers) {
      document.querySelectorAll('input[name="transfer_type"]').forEach(r => r.addEventListener('change', updateOtherVisibility));
      updateOtherVisibility();
    }

    // Get all assets data from JSON
    const allAssetsData = JSON.parse(document.getElementById('allAssetsData').textContent);
    const assetsDatalist = document.getElementById('assetsList');
    const fromAccountableInput = document.getElementById('from_accountable_officer');
    const employeesWithAssetsDatalist = document.getElementById('employeesWithAssetsList');

    // Function to update assets datalist based on selected employee
    function updateAssetsDatalist(selectedEmployeeId) {
      // Clear existing options
      assetsDatalist.innerHTML = '';
      
      if (!selectedEmployeeId) {
        // No employee selected, show no assets
        return;
      }

      // Filter assets by employee_id and populate datalist
      const employeeAssets = allAssetsData.filter(asset => 
        asset.employee_id && asset.employee_id == selectedEmployeeId
      );

      employeeAssets.forEach(asset => {
        const option = document.createElement('option');
        option.value = asset.description; // Only description for insertion
        option.label = `${asset.description} (${asset.property_no || 'No Property No'})`; // Display text with property no
        option.dataset.id = asset.id;
        option.dataset.property_no = asset.property_no || '';
        option.dataset.acquisition_date = asset.acquisition_date || '';
        option.dataset.value = asset.value || '';
        option.dataset.status = asset.status || '';
        // Pass through ICS/PAR fund clusters for auto-fill (prefer PAR when present)
        option.dataset.icsFundCluster = asset.ics_fund_cluster || '';
        option.dataset.parFundCluster = asset.par_fund_cluster || '';
        option.dataset.displayText = `${asset.description} (${asset.property_no || 'No Property No'})`; // For display reference
        assetsDatalist.appendChild(option);
      });

      console.log(`Updated assets datalist with ${employeeAssets.length} assets for employee ID: ${selectedEmployeeId}`);
    }

    // Listen for changes in From Accountable Officer field
    if (fromAccountableInput) {
      fromAccountableInput.addEventListener('input', function() {
        const selectedName = this.value.trim();
        
        // Find matching employee in datalist to get employee_id
        const matchingOption = Array.from(employeesWithAssetsDatalist.options).find(option =>
          option.value === selectedName
        );

        if (matchingOption) {
          const employeeId = matchingOption.dataset.employeeId;
          updateAssetsDatalist(employeeId);
          // Clear any existing asset selections when changing employee
          clearAllAssetSelections();
        } else {
          // Clear assets if no valid employee selected
          updateAssetsDatalist(null);
          clearAllAssetSelections();
        }
      });

      fromAccountableInput.addEventListener('change', function() {
        const selectedName = this.value.trim();
        
        // Find matching employee in datalist to get employee_id
        const matchingOption = Array.from(employeesWithAssetsDatalist.options).find(option =>
          option.value === selectedName
        );

        if (matchingOption) {
          const employeeId = matchingOption.dataset.employeeId;
          updateAssetsDatalist(employeeId);
          // Clear any existing asset selections when changing employee
          clearAllAssetSelections();
        } else {
          // Clear assets if no valid employee selected
          updateAssetsDatalist(null);
          clearAllAssetSelections();
        }
      });
    }

    // Clear function for From Accountable Officer
    const clearFromBtn = document.getElementById('clear_from_accountable');
    if (clearFromBtn && fromAccountableInput) {
      clearFromBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        fromAccountableInput.value = '';
        // Clear assets datalist when clearing from accountable officer
        updateAssetsDatalist(null);
        clearAllAssetSelections();
        fromAccountableInput.focus();
      });
    }

    // Clear function for To Accountable Officer
    const clearBtn = document.getElementById('clear_to_accountable');
    const toOfficerInput = document.getElementById('to_accountable_officer');
    const receivedByInput = document.getElementById('received_by');

    if (clearBtn && toOfficerInput) {
      clearBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toOfficerInput.value = '';
        // Also clear received by field when clearing to accountable officer
        if (receivedByInput) {
          receivedByInput.value = '';
        }
        toOfficerInput.focus();
      });
    }

    // Clear function for Received By
    const clearReceivedBtn = document.getElementById('clear_received_by');
    if (clearReceivedBtn && receivedByInput) {
      clearReceivedBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        receivedByInput.value = '';
        receivedByInput.focus();
      });
    }

    // Auto-fill Received By when To Accountable Officer is selected
    if (toOfficerInput && receivedByInput) {
      // Listen for input changes (typing, selection from datalist)
      toOfficerInput.addEventListener('input', function() {
        const selectedName = this.value.trim();

        // Check if the entered value matches an option in the datalist
        const datalistOptions = document.querySelectorAll('#employeesList option');
        const matchingOption = Array.from(datalistOptions).find(option =>
          option.value === selectedName
        );

        if (matchingOption && selectedName) {
          // Auto-fill received by field
          receivedByInput.value = selectedName;

          // Show brief success indicator
          const successIndicator = document.createElement('span');
          successIndicator.className = 'text-success ms-2';
          successIndicator.innerHTML = '<i class="bi bi-check-circle-fill"></i>';
          successIndicator.style.fontSize = '0.875rem';

          // Remove any existing indicators
          const existingIndicator = receivedByInput.parentNode.querySelector('.text-success');
          if (existingIndicator) {
            existingIndicator.remove();
          }

          // Add new indicator
          receivedByInput.parentNode.appendChild(successIndicator);

          // Remove indicator after 2 seconds
          setTimeout(() => {
            if (successIndicator.parentNode) {
              successIndicator.remove();
            }
          }, 2000);
        }
      });

      // Also listen for change event (when user selects from datalist)
      toOfficerInput.addEventListener('change', function() {
        const selectedName = this.value.trim();
        if (selectedName) {
          receivedByInput.value = selectedName;
        }
      });
    }

    // Table logic
    const table = document.getElementById('itrItemsTable').querySelector('tbody');
    const addRowBtn = document.getElementById('addRowBtn');
    const selectedAssetIds = new Set(); // Track selected assets to prevent duplicates
    const grandTotalField = document.getElementById('grandTotal');
    const duplicateModalEl = document.getElementById('duplicateModal');
    const duplicateModal = duplicateModalEl ? new bootstrap.Modal(duplicateModalEl) : null;

    // Handle preselected asset from URL parameters
    <?php if ($preselected_asset): ?>
      console.log('DEBUG: Preselected asset found:', '<?= $preselected_asset['id'] ?>');
      const preselectedAssetId = '<?= $preselected_asset['id'] ?>';
      selectedAssetIds.add(preselectedAssetId);
      const preselectedOption = document.querySelector(`#assetsList option[data-id="${preselectedAssetId}"]`);
      if (preselectedOption) {
        preselectedOption.style.display = 'none';
        // If fund cluster is empty, attempt to auto-fill from preselected asset's datasets
        const fundClusterInput = document.getElementById('fund_cluster');
        if (fundClusterInput && !fundClusterInput.value) {
          const parFC = preselectedOption.dataset.parFundCluster || '';
          const icsFC = preselectedOption.dataset.icsFundCluster || '';
          const chosenFC = parFC || icsFC;
          if (chosenFC) {
            fundClusterInput.value = chosenFC;
            // Mark that fund cluster was set by this asset id
            fundClusterInput.dataset.setByAssetId = preselectedAssetId;
          }
        }
      }
      const firstRow = table.querySelector('tr');
      if (firstRow) {
        const descriptionInput = firstRow.querySelector('input[name="description[]"]');
        if (descriptionInput) {
          console.log('DEBUG: Description input value before:', descriptionInput.value);
          descriptionInput.dataset.selectedAssetId = preselectedAssetId;
          console.log('DEBUG: Description input value after:', descriptionInput.value);
        }
      }
    <?php endif; ?>
    
    // Debug: Check From Accountable Officer field
    const fromAccountableDebugInput = document.getElementById('from_accountable_officer');
    if (fromAccountableDebugInput) {
      console.log('DEBUG: From Accountable Officer value:', fromAccountableDebugInput.value);
    }

    function clearAssetRow(row) {
      const descriptionInput = row.querySelector('.description-field');
      const assetId = descriptionInput && descriptionInput.dataset.selectedAssetId;
      if (assetId) {
        selectedAssetIds.delete(assetId);
        const option = document.querySelector(`#assetsList option[data-id="${assetId}"]`);
        if (option) option.style.display = '';
        delete descriptionInput.dataset.selectedAssetId;

        // If Fund Cluster was auto-set by this asset, clear it as well
        const fundClusterInput = document.getElementById('fund_cluster');
        if (fundClusterInput && fundClusterInput.dataset.setByAssetId === String(assetId)) {
          fundClusterInput.value = '';
          delete fundClusterInput.dataset.setByAssetId;
        }
      }
      row.querySelectorAll('input').forEach(input => {
        if (input.name === 'item_no[]') return; // keep item numbering
        input.value = '';
      });
      row.querySelector('.condition-field')?.setAttribute('placeholder', '');

      // If no assets remain selected anywhere in the table, clear Fund Cluster regardless of origin
      if (selectedAssetIds.size === 0) {
        const fundClusterInput = document.getElementById('fund_cluster');
        if (fundClusterInput) {
          fundClusterInput.value = '';
          if (fundClusterInput.dataset.setByAssetId) {
            delete fundClusterInput.dataset.setByAssetId;
          }
        }
      }
    }

    // Function to clear all asset selections in the table
    function clearAllAssetSelections() {
      const rows = table.querySelectorAll('tr');
      
      rows.forEach(row => {
        clearAssetRow(row);
      });
      
      // Clear the selectedAssetIds set
      selectedAssetIds.clear();
      
      console.log('Cleared all asset selections');
    }

    // Helper function to map asset status to PPE condition
    function getConditionFromStatus(status) {
      const s = (status || '').toLowerCase();
      switch (s) {
        case 'available':
        case 'serviceable':
          return 'Serviceable';
        case 'unserviceable':
          return 'Unserviceable';
        case 'borrowed':
          return 'On Loan';
        case 'red_tagged':
          return 'For Disposal';
        default:
          return 'Fair';
      }
    }

    function onDescriptionSelected(input) {
      const val = input.value.trim();
      const options = Array.from(document.getElementById('assetsList').options);
      
      // Try exact match first (description only)
      let option = options.find(opt => opt.value === val);
      
      // If no exact match, try to find by display text (description + property no)
      if (!option) {
        option = options.find(opt => opt.dataset.displayText === val);
      }
      
      // If still no match, try partial match on description
      if (!option) {
        option = options.find(opt => opt.value.toLowerCase().includes(val.toLowerCase()));
      }
      
      if (option) {
        const assetId = option.dataset.id;

        // Prevent duplicate selection
        if (selectedAssetIds.has(assetId)) {
          alert('This asset has already been selected in another row.');
          input.value = '';
          return;
        }

        // Set the input value to just the description (clean)
        input.value = option.value;

        // Update hidden input with asset_id
        const row = input.closest('tr');
        const assetIdInput = row.querySelector('.asset-id-field');
        if (assetIdInput) assetIdInput.value = assetId;

        // Fill other fields from selected option
        const dateInput = row.querySelector('input[name="date_acquired[]"]');
        const propInput = row.querySelector('.property-field');
        const amountInput = row.querySelector('.amount-field');
        if (dateInput) dateInput.value = option.dataset.acquisition_date || '';
        
        // Format property number as ICS/PAR based on value
        if (propInput) {
          const assetValue = parseFloat(option.dataset.value || 0);
          const propertyNo = option.dataset.property_no || '';
          if (propertyNo) {
            const icsParFormat = assetValue >= 50000 ? `PAR: ${propertyNo}` : `ICS: ${propertyNo}`;
            propInput.value = icsParFormat;
          }
        }
        
        if (amountInput) amountInput.value = option.dataset.value || '';

        // Set condition based on asset status
        const conditionField = row.querySelector('.condition-field');
        if (conditionField) conditionField.value = getConditionFromStatus(option.dataset.status);

        // Auto-fill ITR header Fund Cluster if empty (prefer PAR's fund cluster, else ICS)
        const fundClusterInput = document.getElementById('fund_cluster');
        if (fundClusterInput && !fundClusterInput.value) {
          const parFC = option.dataset.parFundCluster || '';
          const icsFC = option.dataset.icsFundCluster || '';
          const chosenFC = parFC || icsFC;
          if (chosenFC) {
            fundClusterInput.value = chosenFC;
            // Track which asset set the Fund Cluster
            fundClusterInput.dataset.setByAssetId = String(assetId);
          }
        }

        // Track selected
        input.dataset.selectedAssetId = assetId;
        option.style.display = 'none';
        selectedAssetIds.add(assetId);
        updateGrandTotal();
      }
    }

    table.addEventListener('input', function(e) {
      if (e.target.classList.contains('description-field')) {
        onDescriptionSelected(e.target);
      }
      if (e.target.classList.contains('amount-field')) {
        updateGrandTotal();
      }
    });

    window.checkItrDuplicateDescriptions = function() {
      const descriptions = Array.from(table.querySelectorAll('.description-field'))
        .map(input => input.value.trim().toLowerCase())
        .filter(Boolean);
      const seen = new Set();
      for (const value of descriptions) {
        if (seen.has(value)) {
          if (duplicateModal) duplicateModal.show();
          return false;
        }
        seen.add(value);
      }
      return true;
    };

    function buildRowTemplate() {
      return `
        <td><input type="date" name="date_acquired[]" class="form-control shadow-sm" required></td>
        <td><input type="text" name="item_no[]" class="form-control shadow-sm item-no-field" readonly></td>
        <td><input type="text" name="property_no[]" class="form-control shadow-sm property-field" readonly></td>
        <td class="position-relative">
            <input type="hidden" name="asset_id[]" value="" class="asset-id-field">
            <input type="text" name="description[]" class="form-control shadow-sm description-field" list="assetsList" placeholder="Search description or property number" required>
            <button type="button" class="clear-description" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); background: transparent; border: none; font-weight: bold; font-size: 1rem; color: #888; cursor: pointer;">&times;</button>
        </td>
        <td class="position-relative"><span class="position-absolute top-50 start-0 translate-middle-y ps-2">₱</span><input type="number" step="0.01" name="amount[]" class="form-control shadow-sm amount-field text-end" style="padding-left: 1.5rem;" required></td>
        <td><input type="text" name="condition_of_PPE[]" class="form-control shadow-sm condition-field" required></td>
        <td class="text-center">
            <div class="d-inline-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Row"><i class="bi bi-trash"></i></button>
                <button type="button" class="btn btn-sm btn-warning clear-row" title="Clear Row"><i class="bi bi-eraser"></i></button>
            </div>
        </td>`;
    }

    function updateGrandTotal() {
      if (!grandTotalField) return;
      let sum = 0;
      table.querySelectorAll('.amount-field').forEach(input => {
        sum += parseFloat(input.value) || 0;
      });
      grandTotalField.value = sum.toFixed(2);
    }

    function renumberItemNos() {
      const rows = table.querySelectorAll('tr');
      rows.forEach((row, index) => {
        const itemNoInput = row.querySelector('.item-no-field');
        if (itemNoInput) itemNoInput.value = index + 1;
      });
    }

    if (addRowBtn) {
      addRowBtn.addEventListener('click', function() {
        const newRow = document.createElement('tr');
        newRow.innerHTML = buildRowTemplate();
        table.appendChild(newRow);
        renumberItemNos();
      });
    }

    table.addEventListener('click', function(e) {
      const row = e.target.closest('tr');
      if (e.target.classList.contains('clear-row') || e.target.closest('.clear-row')) {
        clearAssetRow(row);
        updateGrandTotal();
      } else if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
        // Remove row functionality - ensure at least one row remains
        const allRows = table.querySelectorAll('tr');
        if (allRows.length > 1) {
          // Clear any selected asset data before removing
          clearAssetRow(row);
          row.remove();
          renumberItemNos();
          updateGrandTotal();
        } else {
          // If it's the last row, just clear it instead of removing
          clearAssetRow(row);
          alert('Cannot remove the last row. At least one row is required.');
        }
      } else if (e.target.classList.contains('clear-description')) {
        e.preventDefault();
        clearAssetRow(row);
        renumberItemNos();
        updateGrandTotal();
      }
    });

    updateGrandTotal();
  });
</script>