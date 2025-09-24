<?php
require_once '../connect.php';


if (!isset($_SESSION['user_id'])) {
  header('Location: ../index.php');
  exit();
}

// Fetch latest ITR header
$itr = null;
$itr_sql = "SELECT itr_id, header_image, entity_name, fund_cluster, from_accountable_officer, to_accountable_officer, itr_no, `date`, transfer_type, reason_for_transfer, approved_by, approved_designation, approved_date, released_by, released_designation, released_date, received_by, received_designation, received_date FROM itr_form ORDER BY itr_id DESC LIMIT 1";
$res = $conn->query($itr_sql);
if ($res && $res->num_rows > 0) {
  $itr = $res->fetch_assoc();
}

if (!$itr) {
  // Stop early with a simple message to avoid extra HTML wrappers
  exit('No ITR header found. Please configure the ITR header in SYSTEM_ADMIN first.');
}

$itr_id = (int)$itr['itr_id'];

// Fetch existing ITR items
$items = [];
$stmt = $conn->prepare("SELECT item_id, itr_id, date_acquired, property_no, asset_id, description, amount, condition_of_PPE FROM itr_items WHERE itr_id = ? ORDER BY item_id ASC");
$stmt->bind_param('i', $itr_id);
$stmt->execute();
$items_res = $stmt->get_result();
while ($row = $items_res->fetch_assoc()) { $items[] = $row; }
$stmt->close();

// Build assets list for description datalist (for auto-fill)
$assets = [];
$assets_q = $conn->query("SELECT a.id, a.description, a.property_no, a.value, a.acquisition_date FROM assets a WHERE a.type='asset' ORDER BY a.description ASC");
while ($r = $assets_q->fetch_assoc()) { $assets[] = $r; }
// Build employees list for To Accountable Officer datalist
$employees = [];
$emp_q = $conn->query("SELECT name FROM employees ORDER BY name ASC");
if ($emp_q) {
  while ($er = $emp_q->fetch_assoc()) { $employees[] = $er['name']; }
}
// Build header image URL (stored as filename in itr_form; file saved to ../img/)
$headerImgUrl = '';
if (!empty($itr['header_image'])) {
  $img = trim($itr['header_image']);
  if (preg_match('#^https?://#', $img) || substr($img, 0, 1) === '/') {
    $headerImgUrl = $img;
  } else {
    $headerImgUrl = '../img/' . $img;
  }
}

?>

      <form id="itrItemsForm" method="POST" action="save_itr_items.php" enctype="multipart/form-data" class="mb-4">
        <input type="hidden" name="itr_id" value="<?= (int)$itr_id ?>">

  <!-- ITR Header Display (inside form with image at very top) -->
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="mb-3">
        <?php if (!empty($headerImgUrl)): ?>
          <div class="text-center mb-2">
            <img src="<?= htmlspecialchars($headerImgUrl) ?>" alt="Header" class="img-fluid" style="max-height:120px;">
          </div>
        <?php endif; ?>
        <label for="header_image" class="form-label">Header Image</label>
        <input type="file" id="header_image" name="header_image" class="form-control" accept="image/*">
        <div class="form-text">Upload a new header image to replace the current one.</div>
      </div>

      <div class="row g-3">
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

        <div class="col-md-6">
          <label class="form-label d-block">Transfer Type</label>
          <?php
            $transfer_selected = array_map('trim', array_filter(explode(',', (string)$itr['transfer_type'])));
            $known = ['Donation','Reassignment','Relocation'];
            $hasKnown = array_intersect($transfer_selected, $known);
            $otherValue = '';
            foreach ($transfer_selected as $t) {
              if (!in_array($t, $known, true) && $t !== '') { $otherValue = $t; break; }
            }
          ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input transfer-type" type="checkbox" id="tt_donation" name="transfer_type[]" value="Donation" <?= in_array('Donation', $transfer_selected, true) ? 'checked' : '' ?>>
            <label class="form-check-label" for="tt_donation">Donation</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input transfer-type" type="checkbox" id="tt_reassignment" name="transfer_type[]" value="Reassignment" <?= in_array('Reassignment', $transfer_selected, true) ? 'checked' : '' ?>>
            <label class="form-check-label" for="tt_reassignment">Reassignment</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input transfer-type" type="checkbox" id="tt_relocation" name="transfer_type[]" value="Relocation" <?= in_array('Relocation', $transfer_selected, true) ? 'checked' : '' ?>>
            <label class="form-check-label" for="tt_relocation">Relocation</label>
          </div>
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

        <div class="card shadow-sm">
          <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">Items to Transfer</h6>
            <div class="no-print">
              <button type="button" id="addRowBtn" class="btn btn-sm btn-primary"><i class="bi bi-plus-circle"></i> Add Row</button>
            </div>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-bordered align-middle" id="itrItemsTable">
                <thead class="table-secondary text-center">
                  <tr>
                    <th style="min-width:140px;">Date Acquired</th>
                    <th style="min-width:140px;">Property No</th>
                    <th style="min-width:280px;">Description (select from list)</th>
                    <th style="min-width:120px;">Amount</th>
                    <th style="min-width:160px;">Condition of PPE</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($items)): ?>
                    <?php foreach ($items as $it): ?>
                      <tr>
                        <td><input type="date" name="items[<?= (int)$it['item_id'] ?>][date_acquired]" class="form-control text-center" value="<?= htmlspecialchars($it['date_acquired']) ?>"></td>
                        <td>
                          <input type="hidden" name="items[<?= (int)$it['item_id'] ?>][item_id]" value="<?= (int)$it['item_id'] ?>">
                          <input type="hidden" name="items[<?= (int)$it['item_id'] ?>][asset_id]" class="asset-id-input" value="<?= (int)$it['asset_id'] ?>">
                          <input type="text" name="items[<?= (int)$it['item_id'] ?>][property_no]" class="form-control" value="<?= htmlspecialchars($it['property_no']) ?>">
                        </td>
                        <td>
                          <div class="input-group">
                            <input type="text" name="items[<?= (int)$it['item_id'] ?>][description]" class="form-control asset-description" list="assetDescriptions" placeholder="Type to search description..." value="<?= htmlspecialchars($it['description']) ?>">
                            <button type="button" class="btn btn-outline-secondary clear-auto-fields" title="Clear auto-filled fields">Clear</button>
                          </div>
                          <div class="form-text">Select description to auto-fill Property No, Amount, and Date Acquired.</div>
                        </td>
                        <td><input type="number" step="0.01" min="0" name="items[<?= (int)$it['item_id'] ?>][amount]" class="form-control text-end" value="<?= htmlspecialchars($it['amount']) ?>"></td>
                        <td><input type="text" name="items[<?= (int)$it['item_id'] ?>][condition_of_PPE]" class="form-control" value="<?= htmlspecialchars($it['condition_of_PPE']) ?>"></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr class="template-row">
                      <td><input type="date" name="items[new_1][date_acquired]" class="form-control text-center" value=""></td>
                      <td>
                        <input type="hidden" name="items[new_1][item_id]" value="">
                        <input type="hidden" name="items[new_1][asset_id]" class="asset-id-input" value="">
                        <input type="text" name="items[new_1][property_no]" class="form-control" value="">
                      </td>
                      <td>
                        <div class="input-group">
                          <input type="text" name="items[new_1][description]" class="form-control asset-description" list="assetDescriptions" placeholder="Type to search description..." value="">
                          <button type="button" class="btn btn-outline-secondary clear-auto-fields" title="Clear auto-filled fields">Clear</button>
                        </div>
                        <div class="form-text">Select description to auto-fill Property No, Amount, and Date Acquired.</div>
                      </td>
                      <td><input type="number" step="0.01" min="0" name="items[new_1][amount]" class="form-control text-end" value=""></td>
                      <td><input type="text" name="items[new_1][condition_of_PPE]" class="form-control" value=""></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Reason for Transfer moved below the items table -->
        <div class="card shadow-sm mt-3">
          <div class="card-body">
            <label for="reason_for_transfer" class="form-label">Reason for Transfer</label>
            <textarea id="reason_for_transfer" name="reason_for_transfer" class="form-control" rows="3" placeholder="Enter reason for transfer..."><?= htmlspecialchars($itr['reason_for_transfer']) ?></textarea>
          </div>
        </div>

        <div class="text-start mt-3 no-print">
          <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Items</button>
        </div>
      </form>


  <datalist id="assetDescriptions">
    <?php
      // Build JS map and datalist values for description-based selection
      $jsAssets = [];
      foreach ($assets as $a) {
        $desc = trim((string)$a['description']);
        $pno = trim((string)$a['property_no']);
        $display = $pno !== '' ? ($pno . ' - ' . $desc) : $desc;
        echo '<option value="'. htmlspecialchars($display) .'"></option>';
        $jsAssets[] = [
          'id' => (int)$a['id'],
          'description' => $a['description'],
          'property_no' => $a['property_no'],
          'value' => $a['value'],
          'acquisition_date' => $a['acquisition_date'],
          'display' => $display,
        ];
      }
    ?>
  </datalist>

  <script>
    const assetsIndex = new Map();
    const descriptionIndex = new Map();
    <?php
      // Build a map keyed by combined display (property_no - description)
      foreach ($jsAssets as $j) {
        $key = $j['display'];
        echo 'assetsIndex.set('. json_encode($key) .', '. json_encode($j) .');\n';
      }
      // Also build a map keyed by pure description
      foreach ($jsAssets as $j) {
        $k = $j['description'];
        echo 'if (!descriptionIndex.has('. json_encode($k) .')) descriptionIndex.set('. json_encode($k) .', '. json_encode($j) .');\n';
      }
    ?>

    function onDescriptionSelected(input) {
      const row = input.closest('tr');
      const val = input.value;
      let data = assetsIndex.get(val);
      if (!data) {
        // Try match by pure description
        data = descriptionIndex.get(val);
      }
      const assetIdEl = row.querySelector('.asset-id-input');
      const propEl = row.querySelector('input[name$="[property_no]"]');
      const amtEl = row.querySelector('input[name$="[amount]"]');
      const dateEl = row.querySelector('input[name$="[date_acquired]"]');
      if (data) {
        if (assetIdEl) assetIdEl.value = data.id;
        if (propEl) propEl.value = data.property_no || '';
        if (amtEl) amtEl.value = (parseFloat(data.value||0)).toFixed(2);
        if (dateEl) dateEl.value = data.acquisition_date || '';
        // Set the description input to pure description (strip property_no)
        input.value = data.description || '';
      }
    }

    // Row handling
    document.getElementById('addRowBtn')?.addEventListener('click', function() {
      const tbody = document.querySelector('#itrItemsTable tbody');
      const tmpl = tbody.querySelector('.template-row');
      if (tmpl) {
        const clone = tmpl.cloneNode(true);
        // adjust names to unique keys
        const uid = 'new_' + Math.random().toString(36).slice(2,8);
        clone.querySelectorAll('input[name]').forEach(inp => {
          inp.name = inp.name.replace('new_1', uid);
          if (!/\[item_id\]$/.test(inp.name)) inp.value = '';
          if (inp.classList.contains('asset-id-input')) inp.value = '';
        });
        tbody.appendChild(clone);
      } else {
        // No template (when there were existing rows). Create a fresh row structure
        const uid = 'new_' + Math.random().toString(36).slice(2,8);
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td><input type="date" name="items[${uid}][date_acquired]" class="form-control text-center" value=""></td>
          <td>
            <input type="hidden" name="items[${uid}][item_id]" value="">
            <input type="hidden" name="items[${uid}][asset_id]" class="asset-id-input" value="">
            <input type="text" name="items[${uid}][property_no]" class="form-control" value="">
          </td>
          <td>
            <div class="input-group">
              <input type="text" name="items[${uid}][description]" class="form-control asset-description" list="assetDescriptions" placeholder="Type to search description...">
              <button type="button" class="btn btn-outline-secondary clear-auto-fields" title="Clear auto-filled fields">Clear</button>
            </div>
            <div class="form-text">Select description to auto-fill Property No, Amount, and Date Acquired.</div>
          </td>
          <td><input type="number" step="0.01" min="0" name="items[${uid}][amount]" class="form-control text-end" value=""></td>
          <td><input type="text" name="items[${uid}][condition_of_PPE]" class="form-control" value=""></td>
        `;
        document.querySelector('#itrItemsTable tbody').appendChild(tr);
      }
    });

    document.addEventListener('input', function(e){
      if (e.target && e.target.classList.contains('asset-description')) {
        onDescriptionSelected(e.target);
      }
    });

    // Clear auto-filled fields in the same row
    document.addEventListener('click', function(e){
      const btn = e.target && (e.target.classList.contains('clear-auto-fields') ? e.target : e.target.closest('.clear-auto-fields'));
      if (btn) {
        const row = btn.closest('tr');
        if (!row) return;
        const propEl = row.querySelector('input[name$="[property_no]"]');
        const amtEl = row.querySelector('input[name$="[amount]"]');
        const dateEl = row.querySelector('input[name$="[date_acquired]"]');
        const assetIdEl = row.querySelector('.asset-id-input');
        if (propEl) propEl.value = '';
        if (amtEl) amtEl.value = '';
        if (dateEl) dateEl.value = '';
        if (assetIdEl) assetIdEl.value = '';
      }
    });

    // Note: Row removal column was removed as requested.
  </script>

  <!-- ITR Footer Editable -->
  <div class="card shadow-sm mt-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-4">
          <label for="approved_by" class="form-label">Approved By</label>
          <input type="text" id="approved_by" name="approved_by" class="form-control" value="<?= htmlspecialchars($itr['approved_by']) ?>">
          <label for="approved_designation" class="form-label mt-2">Approved Designation</label>
          <input type="text" id="approved_designation" name="approved_designation" class="form-control" value="<?= htmlspecialchars($itr['approved_designation']) ?>">
          <label for="approved_date" class="form-label mt-2">Approved Date</label>
          <input type="date" id="approved_date" name="approved_date" class="form-control" value="<?= htmlspecialchars($itr['approved_date']) ?>">
        </div>
        <div class="col-md-4">
          <label for="released_by" class="form-label">Released By</label>
          <input type="text" id="released_by" name="released_by" class="form-control" value="<?= htmlspecialchars($itr['released_by']) ?>">
          <label for="released_designation" class="form-label mt-2">Released Designation</label>
          <input type="text" id="released_designation" name="released_designation" class="form-control" value="<?= htmlspecialchars($itr['released_designation']) ?>">
          <label for="released_date" class="form-label mt-2">Released Date</label>
          <input type="date" id="released_date" name="released_date" class="form-control" value="<?= htmlspecialchars($itr['released_date']) ?>">
        </div>
        <div class="col-md-4">
          <label for="received_by" class="form-label">Received By</label>
          <input type="text" id="received_by" name="received_by" class="form-control" value="<?= htmlspecialchars($itr['received_by']) ?>">
          <label for="received_designation" class="form-label mt-2">Received Designation</label>
          <input type="text" id="received_designation" name="received_designation" class="form-control" value="<?= htmlspecialchars($itr['received_designation']) ?>">
          <label for="received_date" class="form-label mt-2">Received Date</label>
          <input type="date" id="received_date" name="received_date" class="form-control" value="<?= htmlspecialchars($itr['received_date']) ?>">
        </div>
      </div>
    </div>
  </div>

  <script>
    // Toggle other transfer type field
    const ttOthers = document.getElementById('tt_others');
    const ttOtherWrap = document.getElementById('transfer_type_other_wrap');
    if (ttOthers) {
      ttOthers.addEventListener('change', function(){
        ttOtherWrap.style.display = this.checked ? 'block' : 'none';
        if (!this.checked) {
          const inp = document.getElementById('transfer_type_other');
          if (inp) inp.value = '';
        }
      });
    }
  </script>
