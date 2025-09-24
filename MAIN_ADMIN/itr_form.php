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

// Fetch existing ITR items
$items = [];
$stmt = $conn->prepare("SELECT item_id, itr_id, date_acquired, property_no, asset_id, description, amount, condition_of_PPE FROM itr_items WHERE itr_id = ? ORDER BY item_id ASC");
$stmt->bind_param('i', $itr_id);
$stmt->execute();
$items_res = $stmt->get_result();
while ($row = $items_res->fetch_assoc()) {
  $items[] = $row;
}
$stmt->close();

// Build assets list for description datalist (for auto-fill)
$assets = [];
$assets_q = $conn->query("SELECT a.id, a.description, a.property_no, a.value, a.acquisition_date FROM assets a WHERE a.type='asset' ORDER BY a.description ASC");
while ($r = $assets_q->fetch_assoc()) {
  $assets[] = $r;
}
// Build employees list for To Accountable Officer datalist
$employees = [];
$emp_q = $conn->query("SELECT name FROM employees ORDER BY name ASC");
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
        <div class="mt-3 text-start">
          <label for="header_image_file" class="form-label fw-semibold">Replace Header Image</label>
          <input type="file" class="form-control" id="header_image_file" name="header_image_file" accept="image/*">
          <div class="form-text">Optional. Upload a new header image (JPG, PNG, or WEBP). This will replace the current image.</div>
        </div>
      </div>


      <div class="row g-3 mt-2">
        <div class="col-md-4">
          <label for="entity_name" class="form-label">Entity Name</label>
          <input type="text" id="entity_name" name="entity_name" class="form-control" value="<?= htmlspecialchars($itr['entity_name']) ?>">
        </div>
        <div class="col-md-4">
          <label for="fund_cluster" class="form-label">Fund Cluster</label>
          <input type="text" id="fund_cluster" name="fund_cluster" class="form-control" value="<?= htmlspecialchars($itr['fund_cluster']) ?>">
        </div>
        <div class="col-md-4">
          <label for="date" class="form-label">Date</label>
          <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($itr['date']) ?>">
        </div>
        <div class="col-md-6">
          <label for="from_accountable_officer" class="form-label">From Accountable Officer</label>
          <input type="text" id="from_accountable_officer" name="from_accountable_officer" class="form-control" value="<?= htmlspecialchars($itr['from_accountable_officer']) ?>">
        </div>
        <div class="col-md-6">
          <label for="to_accountable_officer" class="form-label">To Accountable Officer</label>
          <input type="text" id="to_accountable_officer" name="to_accountable_officer" class="form-control" list="employeesList" placeholder="Type to search employees..." value="<?= htmlspecialchars($itr['to_accountable_officer']) ?>">
          <datalist id="employeesList">
            <?php foreach ($employees as $ename): ?>
              <option value="<?= htmlspecialchars($ename) ?>"></option>
            <?php endforeach; ?>
          </datalist>
        </div>

        <!-- Transfer type checkboxes -->
        <div class="col-md-6">
          <label class="form-label d-block">Transfer Type</label>
          <?php
          $transfer_selected = array_map('trim', array_filter(explode(',', (string)$itr['transfer_type'])));
          $known = ['Donation', 'Reassignment', 'Relocation'];
          $otherValue = '';
          foreach ($transfer_selected as $t) {
            if (!in_array($t, $known, true) && $t !== '') {
              $otherValue = $t;
              break;
            }
          }
          ?>
          <?php foreach ($known as $k): ?>
            <div class="form-check form-check-inline">
              <input class="form-check-input transfer-type" type="checkbox" name="transfer_type[]" value="<?= $k ?>" <?= in_array($k, $transfer_selected, true) ? 'checked' : '' ?>>
              <label class="form-check-label"><?= $k ?></label>
            </div>
          <?php endforeach; ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" id="tt_others" name="transfer_type[]" value="Others" <?= $otherValue !== '' ? 'checked' : '' ?>>
            <label class="form-check-label" for="tt_others">Others</label>
          </div>
          <div id="transfer_type_other_wrap" class="mt-2" style="display: <?= $otherValue !== '' ? 'block' : 'none' ?>;">
            <input type="text" id="transfer_type_other" name="transfer_type_other" class="form-control" placeholder="Specify other transfer type" value="<?= htmlspecialchars($otherValue) ?>">
          </div>
        </div>

        <div class="col-md-6">
          <label for="itr_no" class="form-label">ITR No.</label>
          <input type="text" id="itr_no" name="itr_no" class="form-control" value="<?= htmlspecialchars($itr['itr_no']) ?>">
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <h5>ITR Items</h5>
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
          $rowCount = max(5, count($items)); // at least 5 rows
          for ($i = 0; $i < $rowCount; $i++):
            $item = $items[$i] ?? ['date_acquired' => '', 'property_no' => '', 'description' => '', 'amount' => '', 'condition_of_PPE' => ''];
          ?>
            <tr>
              <td><input type="date" name="date_acquired[]" class="form-control" value="<?= htmlspecialchars($item['date_acquired']) ?>"></td>
              <td><input type="text" name="property_no[]" class="form-control property-search" value="<?= htmlspecialchars($item['property_no']) ?>"></td>
              <td>
                <input type="text" name="description[]" class="form-control asset-search" list="assetsList" value="<?= htmlspecialchars($item['description']) ?>" placeholder="Search description or property no">
              </td>
              <td><input type="number" step="0.01" name="amount[]" class="form-control" value="<?= htmlspecialchars($item['amount']) ?>"></td>
              <td><input type="text" name="condition_of_PPE[]" class="form-control" value="<?= htmlspecialchars($item['condition_of_PPE']) ?>"></td>
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
      <label for="reason_for_transfer" class="form-label">Reason for Transfer</label>
      <textarea id="reason_for_transfer" name="reason_for_transfer" class="form-control" rows="3" placeholder="Enter reason for transfer..."><?= htmlspecialchars($itr['reason_for_transfer']) ?></textarea>
    </div>
  </div>

  <!-- Footer -->
  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <div class="row g-3">
        <?php foreach (['approved', 'released', 'received'] as $role): ?>
          <div class="col-md-4">
            <label class="form-label"><?= ucfirst($role) ?> By</label>
            <input type="text" name="<?= $role ?>_by" class="form-control" value="<?= htmlspecialchars($itr[$role . '_by']) ?>">
            <label class="form-label mt-2"><?= ucfirst($role) ?> Designation</label>
            <input type="text" name="<?= $role ?>_designation" class="form-control" value="<?= htmlspecialchars($itr[$role . '_designation']) ?>">
            <label class="form-label mt-2"><?= ucfirst($role) ?> Date</label>
            <input type="date" name="<?= $role ?>_date" class="form-control" value="<?= htmlspecialchars($itr[$role . '_date']) ?>">
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Single Save Button -->
  <div class="d-flex justify-content-end gap-2 mt-3">
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save ITR</button>
  </div>
</form>

<script>
  // Toggle other transfer type field
  const ttOthers = document.getElementById('tt_others');
  const ttOtherWrap = document.getElementById('transfer_type_other_wrap');
  if (ttOthers) {
    ttOthers.addEventListener('change', function() {
      ttOtherWrap.style.display = this.checked ? 'block' : 'none';
      if (!this.checked) {
        document.getElementById('transfer_type_other').value = '';
      }
    });
  }

  document.addEventListener('DOMContentLoaded', function() {
    const table = document.getElementById('itrItemsTable').querySelector('tbody');
    const selectedAssetIds = new Set(); // Track selected assets to prevent duplicates

    // Function to clear asset row
    function clearAssetRow(row) {
      const descriptionInput = row.querySelector('input[name="description[]"]');
      const propertyInput = row.querySelector('input[name="property_no[]"]');
      const removeBtn = row.querySelector('.remove-asset-btn');
      
      // Get asset ID from data attribute to remove from selected set
      const assetId = descriptionInput.dataset.selectedAssetId;
      if (assetId) {
        selectedAssetIds.delete(assetId);
        // Make asset available in datalist again
        const option = document.querySelector(`#assetsList option[data-id="${assetId}"]`);
        if (option) {
          option.style.display = '';
        }
        delete descriptionInput.dataset.selectedAssetId;
      }
      
      // Clear all inputs in the row
      row.querySelectorAll('input').forEach(input => input.value = '');
      
      // Hide remove button
      if (removeBtn) {
        removeBtn.style.display = 'none';
      }
    }

    // Function to handle asset selection
    function onDescriptionSelected(input) {
      const val = input.value;
      const option = Array.from(document.getElementById('assetsList').options)
        .find(opt => opt.value === val);
      
      if (option) {
        const assetId = option.dataset.id;
        
        // Check for duplicates
        if (selectedAssetIds.has(assetId)) {
          alert('This asset has already been selected in another row.');
          input.value = '';
          return;
        }
        
        // Clear previous selection from this row
        const previousAssetId = input.dataset.selectedAssetId;
        if (previousAssetId && previousAssetId !== assetId) {
          selectedAssetIds.delete(previousAssetId);
          const prevOption = document.querySelector(`#assetsList option[data-id="${previousAssetId}"]`);
          if (prevOption) {
            prevOption.style.display = '';
          }
        }
        
        // Add new selection
        selectedAssetIds.add(assetId);
        input.dataset.selectedAssetId = assetId;
        
        // Hide this option from datalist
        option.style.display = 'none';
        
        // Auto-fill other fields
        const row = input.closest('tr');
        row.querySelector('input[name="date_acquired[]"]').value = option.dataset.acquisition_date || '';
        row.querySelector('input[name="property_no[]"]').value = option.dataset.property_no || '';
        row.querySelector('input[name="amount[]"]').value = option.dataset.value || '';
        
        // Show remove button
        const removeBtn = row.querySelector('.remove-asset-btn');
        if (removeBtn) {
          removeBtn.style.display = 'inline-block';
        }
      }
    }

    // Add new row function
    document.getElementById('addRow').addEventListener('click', function() {
      const newRow = document.createElement('tr');
      newRow.innerHTML = `
      <td><input type="date" name="date_acquired[]" class="form-control"></td>
      <td><input type="text" name="property_no[]" class="form-control property-search"></td>
      <td><input type="text" name="description[]" class="form-control asset-search" list="assetsList" placeholder="Search description or property no"></td>
      <td><input type="number" step="0.01" name="amount[]" class="form-control"></td>
      <td><input type="text" name="condition_of_PPE[]" class="form-control"></td>
      <td>
        <button type="button" class="btn btn-sm btn-danger clear-row">Clear</button>
        <button type="button" class="btn btn-sm btn-danger remove-asset-btn ms-1" style="display: none;" title="Remove Asset">
          <i class="bi bi-x"></i>
        </button>
      </td>
    `;
      table.appendChild(newRow);
    });

    // Event delegation for table interactions
    table.addEventListener('click', function(e) {
      const row = e.target.closest('tr');
      
      if (e.target.classList.contains('clear-row')) {
        clearAssetRow(row);
      } else if (e.target.classList.contains('remove-asset-btn') || e.target.closest('.remove-asset-btn')) {
        clearAssetRow(row);
      }
    });

    // Auto-fill on asset selection
    table.addEventListener('input', function(e) {
      if (e.target.classList.contains('asset-search')) {
        onDescriptionSelected(e.target);
      }
    });
  });
</script>