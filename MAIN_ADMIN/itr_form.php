<?php
require_once '../connect.php';


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

// Always start with a BLANK items table for new ITR entries.
// Do NOT load items from the most recent ITR submission.
// If an asset_id is provided via GET (from Transfer), preselection logic below will add a single row.
$items = [];

// Handle asset pre-selection from URL parameters (from employee transfer)
$preselected_asset = null;
if (isset($_GET['asset_id']) && !empty($_GET['asset_id'])) {
  $asset_id = intval($_GET['asset_id']);
  $stmt = $conn->prepare("SELECT id, description, property_no, value, acquisition_date FROM assets WHERE id = ? LIMIT 1");
  $stmt->bind_param('i', $asset_id);
  $stmt->execute();
  $result = $stmt->get_result();
  if ($row = $result->fetch_assoc()) {
    $preselected_asset = $row;
    // If no existing items, add the preselected asset as first item
    if (empty($items)) {
      $items[] = [
        'item_id' => 0,
        'itr_id' => $itr_id,
        'date_acquired' => $row['acquisition_date'] ?? '',
        'property_no' => $row['property_no'] ?? '',
        'asset_id' => $row['id'],
        'description' => $row['description'] . ' (' . $row['property_no'] . ')',
        'amount' => $row['value'] ?? '',
        'condition_of_PPE' => ''
      ];
    }
  }
  $stmt->close();
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
          <input type="text" id="from_accountable_officer" name="from_accountable_officer" class="form-control shadow" value="<?= htmlspecialchars($itr['from_accountable_officer']) ?>" required>
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
        </div>

        <!-- Transfer type radios -->
        <div class="col-md-6">
          <label class="form-label d-block">Transfer Type <span style="color: red;">*</span></label>
          <?php
          // Determine selected transfer type; legacy values may be comma-separated, use first
          $raw_transfer = (string)$itr['transfer_type'];
          $parts = array_map('trim', array_filter(explode(',', $raw_transfer)));
          $selectedType = $parts[0] ?? '';
          $known = ['Donation', 'Reassignment', 'Relocation'];
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
          <label for="itr_no" class="form-label">ITR No. <span style="color: red;">*</span></label>
          <input type="text" id="itr_no" name="itr_no" class="form-control shadow" placeholder="Enter ITR number" required>
        </div>
        
        <!-- End User Field -->
        <div class="col-md-12">
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
            <th style="width:20%">Property No</th>
            <th style="width:35%">Description / Property No</th>
            <th style="width:15%">Amount</th>
            <th style="width:10%">Condition of PPE</th>
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
              <td><input type="text" name="property_no[]" class="form-control property-search shadow" value="<?= htmlspecialchars($item['property_no']) ?>" required></td>
              <td>
                <input type="text" name="description[]" class="form-control asset-search shadow" list="assetsList" value="<?= htmlspecialchars($item['description']) ?>" placeholder="Search description or property no" required>
              </td>
              <td><input type="number" step="0.01" name="amount[]" class="form-control shadow" value="<?= htmlspecialchars($item['amount']) ?>" required></td>
              <td><input type="text" name="condition_of_PPE[]" class="form-control shadow" value="<?= htmlspecialchars($item['condition_of_PPE']) ?>" required></td>
              <td>
                <button type="button" class="btn btn-sm btn-danger clear-row">Clear</button>
                <button type="button" class="btn btn-sm btn-danger remove-asset-btn ms-1" style="display: none;" title="Remove Asset">
                  <i class="bi bi-x"></i>
                </button>
              </td>
            </tr>
          <?php endfor; ?>
        </tbody>
      </table>
      <button type="button" id="addRow" class="btn btn-secondary btn-sm mt-2"><i class="bi bi-plus"></i> Add Row</button>

      <!-- Asset datalist for search -->
      <datalist id="assetsList">
        <?php
        $assets_q = $conn->query("SELECT id, description, property_no, acquisition_date, value FROM assets WHERE type='asset' AND employee_id IS NOT NULL ORDER BY description ASC");
        while ($a = $assets_q->fetch_assoc()):
          $display = htmlspecialchars($a['description'] . ' (' . $a['property_no'] . ')');
        ?>
          <option
            data-id="<?= $a['id'] ?>"
            data-property_no="<?= htmlspecialchars($a['property_no']) ?>"
            data-acquisition_date="<?= htmlspecialchars($a['acquisition_date']) ?>"
            data-value="<?= htmlspecialchars($a['value']) ?>"
            value="<?= $display ?>">
          </option>
        <?php endwhile; ?>
      </datalist>
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
            <input type="text" name="<?= $role ?>_by" class="form-control shadow" value="<?= htmlspecialchars($itr[$role . '_by']) ?>" required>
            <label class="form-label mt-2"><?= ucfirst($role) ?> Designation <span style="color: red;">*</span></label>
            <input type="text" name="<?= $role ?>_designation" class="form-control shadow" value="<?= htmlspecialchars($itr[$role . '_designation']) ?>" required>
            <label class="form-label mt-2"><?= ucfirst($role) ?> Date</label>
            <input type="date" name="<?= $role ?>_date" class="form-control shadow" value="<?= htmlspecialchars($itr[$role . '_date']) ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Single Save Button -->
  <div class="d-flex justify-content-end gap-2 mt-3">
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save </button>
  </div>
</form>

<script>
  document.addEventListener('DOMContentLoaded', function() {
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

    // Clear function for To Accountable Officer
    const clearBtn = document.getElementById('clear_to_accountable');
    const toOfficerInput = document.getElementById('to_accountable_officer');
    if (clearBtn && toOfficerInput) {
      clearBtn.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        toOfficerInput.value = '';
        toOfficerInput.focus();
      });
    }

    // Table logic
    const table = document.getElementById('itrItemsTable').querySelector('tbody');
    const selectedAssetIds = new Set(); // Track selected assets to prevent duplicates

    // Handle preselected asset from URL parameters
    <?php if ($preselected_asset): ?>
    const preselectedAssetId = '<?= $preselected_asset['id'] ?>';
    selectedAssetIds.add(preselectedAssetId);
    const preselectedOption = document.querySelector(`#assetsList option[data-id="${preselectedAssetId}"]`);
    if (preselectedOption) preselectedOption.style.display = 'none';
    const firstRow = table.querySelector('tr');
    if (firstRow) {
      const descriptionInput = firstRow.querySelector('input[name="description[]"]');
      const removeBtn = firstRow.querySelector('.remove-asset-btn');
      if (descriptionInput) descriptionInput.dataset.selectedAssetId = preselectedAssetId;
      if (removeBtn) removeBtn.style.display = 'inline-block';
    }
    <?php endif; ?>

    function clearAssetRow(row) {
      const descriptionInput = row.querySelector('input[name="description[]"]');
      const removeBtn = row.querySelector('.remove-asset-btn');
      const assetId = descriptionInput && descriptionInput.dataset.selectedAssetId;
      if (assetId) {
        selectedAssetIds.delete(assetId);
        const option = document.querySelector(`#assetsList option[data-id="${assetId}"]`);
        if (option) option.style.display = '';
        delete descriptionInput.dataset.selectedAssetId;
      }
      row.querySelectorAll('input').forEach(input => input.value = '');
      if (removeBtn) removeBtn.style.display = 'none';
    }

    function onDescriptionSelected(input) {
      const val = input.value;
      const option = Array.from(document.getElementById('assetsList').options).find(opt => opt.value === val);
      if (option) {
        const assetId = option.dataset.id;
        if (selectedAssetIds.has(assetId)) {
          alert('This asset has already been selected in another row.');
          input.value = '';
          return;
        }
        const previousAssetId = input.dataset.selectedAssetId;
        if (previousAssetId && previousAssetId !== assetId) {
          selectedAssetIds.delete(previousAssetId);
          const prevOption = document.querySelector(`#assetsList option[data-id="${previousAssetId}"]`);
          if (prevOption) prevOption.style.display = '';
        }
        selectedAssetIds.add(assetId);
        input.dataset.selectedAssetId = assetId;
        option.style.display = 'none';
        const row = input.closest('tr');
        row.querySelector('input[name="date_acquired[]"]').value = option.dataset.acquisition_date || '';
        row.querySelector('input[name="property_no[]"]').value = option.dataset.property_no || '';
        row.querySelector('input[name="amount[]"]').value = option.dataset.value || '';
        const removeBtn = row.querySelector('.remove-asset-btn');
        if (removeBtn) removeBtn.style.display = 'inline-block';
      }
    }

    document.getElementById('addRow').addEventListener('click', function() {
      const newRow = document.createElement('tr');
      newRow.innerHTML = `
        <td><input type="date" name="date_acquired[]" class="form-control shadow"></td>
        <td><input type="text" name="property_no[]" class="form-control property-search shadow"></td>
        <td><input type="text" name="description[]" class="form-control asset-search shadow" list="assetsList" placeholder="Search description or property no"></td>
        <td><input type="number" step="0.01" name="amount[]" class="form-control shadow"></td>
        <td><input type="text" name="condition_of_PPE[]" class="form-control shadow"></td>
        <td>
          <button type="button" class="btn btn-sm btn-danger clear-row">Clear</button>
          <button type="button" class="btn btn-sm btn-danger remove-asset-btn ms-1" style="display: none;" title="Remove Asset">
            <i class="bi bi-x"></i>
          </button>
        </td>
      `;
      table.appendChild(newRow);
    });

    table.addEventListener('click', function(e) {
      const row = e.target.closest('tr');
      if (e.target.classList.contains('clear-row')) {
        clearAssetRow(row);
      } else if (e.target.classList.contains('remove-asset-btn') || e.target.closest('.remove-asset-btn')) {
        clearAssetRow(row);
      }
    });

    table.addEventListener('input', function(e) {
      if (e.target.classList.contains('asset-search')) {
        onDescriptionSelected(e.target);
      }
    });
  });
</script>