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

<!-- Header with Saved ITR Button -->
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Inventory Transfer Receipt (ITR) Form</h4>
  <a href="saved_itr.php" class="btn btn-outline-primary">
    <i class="bi bi-file-earmark-text me-2"></i>Saved ITR
  </a>
</div>

<form id="itrItemsForm" method="POST" action="save_itr_items.php" enctype="multipart/form-data" class="mb-4">
  <input type="hidden" name="itr_id" value="<?= (int)$itr_id ?>">
  <input type="hidden" name="form_id" value="<?= (int)$form_id ?>">

  <!-- ITR Header -->
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <!-- ITR Header Image Display -->
      <div class="mb-3 text-center">
        <?php if (!empty($itr['header_image'])): ?>
          <img src="../img/<?= htmlspecialchars($itr['header_image']) ?>"
            class="img-fluid mb-2"
            style="max-width: 100%; height: auto; object-fit: contain;">

          <!-- Hidden input ensures it gets submitted -->
          <input type="hidden" name="header_image" value="<?= htmlspecialchars($itr['header_image']) ?>">
        <?php else: ?>
          <p class="text-muted">No header image available</p>
        <?php endif; ?>
      </div>


      <div class="row g-3 mt-2">
        <div class="col-md-4">
          <label for="itr_office" class="form-label">Office (sets Entity Name)</label>
          <select id="itr_office" class="form-select shadow">
            <option value="">Select office...</option>
            <?php foreach ($itr_offices as $o): ?>
              <option value="<?= (int)$o['id'] ?>"><?= htmlspecialchars($o['office_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <div class="form-text">Changing this fills the Entity Name and ITR No preview.</div>
        </div>
        <div class="col-md-4">
          <label for="entity_name" class="form-label">Entity Name <span style="color: red;">*</span></label>
          <input type="text" id="entity_name" name="entity_name" class="form-control shadow" placeholder="Enter Entity Name" required>
        </div>
        <div class="col-md-4">
          <label for="fund_cluster" class="form-label">Fund Cluster <span style="color: red;">*</span></label>
          <input type="text" id="fund_cluster" name="fund_cluster" class="form-control shadow" placeholder="Enter Fund Cluster" required>
        </div>
        <div class="col-md-4">
          <label for="date" class="form-label">Date</label>
          <input type="date" id="date" name="date" class="form-control shadow" value="<?= htmlspecialchars($itr['date']) ?>">
        </div>
        <div class="col-md-6">
          <label for="from_accountable_officer" class="form-label">From Accountable Officer <span style="color: red;">*</span></label>
          <div class="input-group">
            <input type="text" id="from_accountable_officer" name="from_accountable_officer" class="form-control shadow" list="employeesWithAssetsList" placeholder="Select employee with MR assets..." value="<?= htmlspecialchars($itr['from_accountable_officer']) ?>" required>
            <button type="button" class="btn btn-outline-secondary" id="clear_from_accountable" title="Clear field" aria-label="Clear From Accountable Officer">
              <i class="bi bi-x-circle"></i>
            </button>
          </div>
          <datalist id="employeesWithAssetsList">
            <?php foreach ($employees_with_assets as $emp): ?>
              <option value="<?= htmlspecialchars($emp['name']) ?>" data-employee-id="<?= $emp['employee_id'] ?>"></option>
            <?php endforeach; ?>
          </datalist>
          <div class="form-text">
            <small class="text-muted">Only employees with MR assets are shown. Assets datalist will filter based on selection.</small>
          </div>
        </div>
        <div class="col-md-6">
          <label for="to_accountable_officer" class="form-label">To Accountable Officer <span style="color: red;">*</span></label>
          <div class="input-group">
            <input type="text" id="to_accountable_officer" name="to_accountable_officer" class="form-control shadow" list="employeesList" placeholder="Type to search employees..." required>
            <button type="button" class="btn btn-outline-secondary" id="clear_to_accountable" title="Clear field" aria-label="Clear To Accountable Officer">
              <i class="bi bi-x-circle"></i>
            </button>
          </div>
          <datalist id="employeesList">
            <?php foreach ($employees as $ename): ?>
              <option value="<?= htmlspecialchars($ename) ?>"></option>
            <?php endforeach; ?>
          </datalist>
          <div class="form-text">
            <small class="text-muted">Will auto-fill "Received By" field below</small>
          </div>
        </div>

        <!-- Transfer type radios -->
        <div class="col-md-6">
          <label class="form-label d-block">Transfer Type <span style="color: red;">*</span></label>
          <?php
          // Determine selected transfer type; legacy values may be comma-separated, use first
          $raw_transfer = (string)$itr['transfer_type'];
          $parts = array_map('trim', array_filter(explode(',', $raw_transfer)));
          $selectedType = $parts[0] ?? 'Reassignment'; // Default to Reassignment
          $known = ['Donation', 'Reassignment', 'Relocate'];
          $otherValue = '';
          if ($selectedType !== '' && !in_array($selectedType, $known, true)) {
            $otherValue = $selectedType; // Legacy/custom value
            $selectedType = 'Others';
          }
          ?>
          <?php foreach ($known as $k): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input transfer-type" type="radio" name="transfer_type" id="tt_<?= strtolower($k) ?>" value="<?= $k ?>" <?= ($selectedType === $k) ? 'checked' : '' ?>>
              <label class="form-check-label" for="tt_<?= strtolower($k) ?>"><?= $k ?></label>
            </div>
          <?php endforeach; ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="radio" id="tt_others" name="transfer_type" value="Others" <?= ($selectedType === 'Others') ? 'checked' : '' ?>>
            <label class="form-check-label" for="tt_others">Others</label>
          </div>
          <div id="transfer_type_other_wrap" class="mt-2" style="display: <?= ($selectedType === 'Others') ? 'block' : 'none' ?>;">
            <input type="text" id="transfer_type_other" name="transfer_type_other" class="form-control shadow" placeholder="Specify other transfer type" value="<?= htmlspecialchars($otherValue) ?>">
          </div>
        </div>

        <div class="col-md-6">
          <label for="itr_no" class="form-label">ITR No. (Auto-generated)</label>
          <div class="input-group">
            <input type="text" id="itr_no" name="itr_no" class="form-control shadow" value="<?= previewTag('itr_no') ?>" readonly>
            <span class="input-group-text">
              <i class="bi bi-magic" title="Auto-generated"></i>
            </span>
          </div>
          <small class="text-muted">This number will be automatically assigned when you save the form.</small>
        </div>

        <!-- End User Field -->
        <div class="col-md-6">
          <label for="end_user" class="form-label">End User <span style="color: red;">*</span></label>
          <input type="text" id="end_user" name="end_user" class="form-control shadow" placeholder="Enter end user name..." value="" required>
          <div class="form-text">This will update the end user for all assets being transferred in this ITR.</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <table class="table table-bordered" id="itrItemsTable">
        <thead>
          <tr>
            <th style="width:15%">Date Acquired</th>
            <th style="width:10%">Item No.</th>
            <th style="width:20%">ICS & PAR No./Date</th>
            <th style="width:35%">Description</th>
            <th style="width:15%">Unit Price</th>
            <th style="width:10%">Condition of Inventory</th>
            <th style="width:5%">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $rowCount = max(1, count($items)); // at least 1 row
          for ($i = 0; $i < $rowCount; $i++):
            $item = $items[$i] ?? ['date_acquired' => '', 'property_no' => '', 'description' => '', 'amount' => '', 'condition_of_PPE' => ''];
          ?>
            <tr>
              <td><input type="date" name="date_acquired[]" class="form-control shadow" value="<?= htmlspecialchars($item['date_acquired']) ?>" required></td>
              <td><input type="text" name="item_no[]" class="form-control shadow" value="1" required></td>
              <td><input type="text" name="property_no[]" class="form-control property-search shadow" value="<?= htmlspecialchars($item['property_no']) ?>" required></td>
              <td>
                <input type="hidden" name="asset_id[]" value="<?= htmlspecialchars($item['asset_id'] ?? '') ?>">
                <input type="text" name="description[]"
                  class="form-control asset-search shadow"
                  list="assetsList"
                  placeholder="Search description or property no" 
                  value="<?= htmlspecialchars($item['description']) ?>" required>
              </td>

              <td><input type="number" step="0.01" name="amount[]" class="form-control shadow" value="<?= htmlspecialchars($item['amount']) ?>" required></td>
              <td><input type="text" name="condition_of_PPE[]" class="form-control shadow" value="<?= htmlspecialchars($item['condition_of_PPE']) ?>" required></td>
              <td>
                <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Row">
                  <i class="bi bi-trash"></i>
                </button>
                <button type="button" class="btn btn-sm btn-warning clear-row ms-1" title="Clear Fields">
                  <i class="bi bi-eraser"></i>
                </button>
              </td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>
      <button type="button" id="addRow" class="btn btn-secondary btn-sm mt-2"><i class="bi bi-plus"></i> Add Row</button>

      <!-- Asset datalist for search - will be populated dynamically based on selected employee -->
      <datalist id="assetsList">
        <!-- Options will be populated by JavaScript based on selected From Accountable Officer -->
      </datalist>

      <!-- Hidden script data for all assets -->
      <script type="application/json" id="allAssetsData">
        <?php
        $all_assets = [];
        $assets_q = $conn->query("SELECT id, description, property_no, acquisition_date, value, status, employee_id FROM assets WHERE type='asset' AND employee_id IS NOT NULL ORDER BY description ASC");
        while ($a = $assets_q->fetch_assoc()) {
          $all_assets[] = $a;
        }
        echo json_encode($all_assets);
        ?>
      </script>
    </div>
  </div>



  <!-- Reason for Transfer -->
  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <label for="reason_for_transfer" class="form-label">Reason for Transfer <span style="color: red;">*</span></label>
      <textarea id="reason_for_transfer" name="reason_for_transfer" class="form-control shadow" rows="3" placeholder="Enter reason for transfer..." required></textarea>
    </div>
  </div>

  <!-- Footer -->
  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <div class="row g-3">
        <?php foreach (['approved', 'released', 'received'] as $role): ?>
          <div class="col-md-4">
            <label class="form-label"><?= ucfirst($role) ?> By <span style="color: red;">*</span></label>
            <?php if ($role === 'received'): ?>
              <div class="input-group">
                <input type="text" id="<?= $role ?>_by" name="<?= $role ?>_by" class="form-control shadow" list="employeesList" value="<?= htmlspecialchars($itr[$role . '_by']) ?>" required>
                <button type="button" class="btn btn-outline-secondary" id="clear_received_by" title="Clear field" aria-label="Clear Received By">
                  <i class="bi bi-x-circle"></i>
                </button>
              </div>
              <div class="form-text">
                <small class="text-muted">Auto-fills from "To Accountable Officer" selection</small>
              </div>
            <?php else: ?>
              <input type="text" name="<?= $role ?>_by" class="form-control shadow" value="<?= htmlspecialchars($itr[$role . '_by']) ?>" required>
            <?php endif; ?>
            <label class="form-label mt-2"><?= ucfirst($role) ?> Designation <span style="color: red;">*</span></label>
            <input type="text" name="<?= $role ?>_designation" class="form-control shadow" value="<?= htmlspecialchars($itr[$role . '_designation']) ?>" required>
            <label class="form-label mt-2"><?= ucfirst($role) ?> Date</label>
            <input type="date" name="<?= $role ?>_date" class="form-control shadow" value="<?= htmlspecialchars($itr[$role . '_date']) ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <p class="small mt-4"> <span style="color: red;">*</span> Required fields</p>


  <!-- Single Save Button -->
  <div class="d-flex justify-content-end gap-2 mt-3">
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save </button>
  </div>

</form>

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
      if (!ITR_TEMPLATE) return;
      const dateInput = document.getElementById('date');
      const dateVal = dateInput ? dateInput.value : '';
      let out = formatFromDate(ITR_TEMPLATE, dateVal);
      const sel = document.getElementById('itr_office');
      let officeName = 'OFFICE';
      if (sel) {
        const opt = sel.options[sel.selectedIndex];
        const txt = opt ? (opt.text || '') : '';
        if (sel.value) officeName = (txt || '').trim() || 'OFFICE';
      }
      out = out.replace(/\{OFFICE\}|OFFICE/g, officeName).replace(/--+/g,'-').replace(/^-|-$/g,'');
      field.value = out;
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
    const selectedAssetIds = new Set(); // Track selected assets to prevent duplicates

    // Handle preselected asset from URL parameters
    <?php if ($preselected_asset): ?>
      console.log('DEBUG: Preselected asset found:', '<?= $preselected_asset['id'] ?>');
      const preselectedAssetId = '<?= $preselected_asset['id'] ?>';
      selectedAssetIds.add(preselectedAssetId);
      const preselectedOption = document.querySelector(`#assetsList option[data-id="${preselectedAssetId}"]`);
      if (preselectedOption) preselectedOption.style.display = 'none';
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
      const descriptionInput = row.querySelector('input[name="description[]"]');
      const assetId = descriptionInput && descriptionInput.dataset.selectedAssetId;
      if (assetId) {
        selectedAssetIds.delete(assetId);
        const option = document.querySelector(`#assetsList option[data-id="${assetId}"]`);
        if (option) option.style.display = '';
        delete descriptionInput.dataset.selectedAssetId;
      }
      row.querySelectorAll('input').forEach(input => {
        if (input.name === 'item_no[]') return; // keep item numbering
        input.value = '';
      });
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
        const assetIdInput = row.querySelector('input[name="asset_id[]"]');
        if (assetIdInput) assetIdInput.value = assetId;

        // Fill other fields from selected option
        const dateInput = row.querySelector('input[name="date_acquired[]"]');
        const propInput = row.querySelector('input[name="property_no[]"]');
        const amountInput = row.querySelector('input[name="amount[]"]');
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
        row.querySelector('input[name="condition_of_PPE[]"]').value = getConditionFromStatus(option.dataset.status);

        // Track selected
        input.dataset.selectedAssetId = assetId;
        option.style.display = 'none';
        selectedAssetIds.add(assetId);
      }
    }

    // Renumber Item No. based on current row order (1-based)
    function renumberItemNos() {
      const rows = table.querySelectorAll('tr');
      rows.forEach((row, index) => {
        const input = row.querySelector('input[name="item_no[]"]');
        if (input) input.value = String(index + 1);
      });
    }

    // Initial numbering
    renumberItemNos();

    document.getElementById('addRow').addEventListener('click', function() {
      const newRow = document.createElement('tr');
      newRow.innerHTML = `
        <td><input type="date" name="date_acquired[]" class="form-control shadow"></td>
        <td><input type="text" name="item_no[]" class="form-control shadow" value=""></td>
        <td><input type="text" name="property_no[]" class="form-control property-search shadow"></td>
        <td>
          <input type="hidden" name="asset_id[]" value="">
          <input type="text" name="description[]" class="form-control asset-search shadow" list="assetsList" placeholder="Search description or property no">
        </td>
        <td><input type="number" step="0.01" name="amount[]" class="form-control shadow"></td>
        <td><input type="text" name="condition_of_PPE[]" class="form-control shadow"></td>
        <td>
          <button type="button" class="btn btn-sm btn-danger remove-row" title="Remove Row">
            <i class="bi bi-trash"></i>
          </button>
          <button type="button" class="btn btn-sm btn-warning clear-row ms-1" title="Clear Fields">
            <i class="bi bi-eraser"></i>
          </button>
        </td>
      `;
      table.appendChild(newRow);
      renumberItemNos();
    });

    table.addEventListener('click', function(e) {
      const row = e.target.closest('tr');
      if (e.target.classList.contains('clear-row') || e.target.closest('.clear-row')) {
        clearAssetRow(row);
      } else if (e.target.classList.contains('remove-row') || e.target.closest('.remove-row')) {
        // Remove row functionality - ensure at least one row remains
        const allRows = table.querySelectorAll('tr');
        if (allRows.length > 1) {
          // Clear any selected asset data before removing
          clearAssetRow(row);
          row.remove();
          renumberItemNos();
        } else {
          // If it's the last row, just clear it instead of removing
          clearAssetRow(row);
          alert('Cannot remove the last row. At least one row is required.');
        }
      }
    });

    table.addEventListener('input', function(e) {
      if (e.target.classList.contains('asset-search')) {
        onDescriptionSelected(e.target);
      }
    });
  });
</script>