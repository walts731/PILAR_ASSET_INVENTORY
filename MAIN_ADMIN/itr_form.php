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

// Build assets datalist with person accountable
$assets = [];
$assets_q = $conn->query("SELECT a.id, a.description, a.inventory_tag, a.property_no, a.quantity, a.value, e.name AS person_accountable, a.employee_id FROM assets a LEFT JOIN employees e ON a.employee_id = e.employee_id ORDER BY a.description ASC");
while ($r = $assets_q->fetch_assoc()) {
  $assets[] = $r;
}
?>

  

      <form id="itrItemsForm" method="POST" action="save_itr_items.php" class="mb-4">
        <input type="hidden" name="itr_id" value="<?= (int)$itr_id ?>">

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
                    <th style="min-width:260px;">Asset (Select from list)</th>
                    <th style="min-width:140px;">Date Acquired</th>
                    <th style="min-width:140px;">Property No</th>
                    <th>Description</th>
                    <th style="min-width:120px;">Amount</th>
                    <th style="min-width:160px;">Condition of PPE</th>
                    <th style="min-width:160px;">Person Accountable</th>
                    <th style="min-width:90px;">Qty</th>
                    <th class="no-print" style="min-width:100px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (!empty($items)): ?>
                    <?php foreach ($items as $it): ?>
                      <?php
                        // Try to fetch asset data for display-only fields (qty, person)
                        $assetInfo = ['quantity' => '', 'person_accountable' => '', 'inventory_tag' => ''];
                        if (!empty($it['asset_id'])) {
                          $aid = (int)$it['asset_id'];
                          $a = $conn->query("SELECT quantity, inventory_tag, (SELECT name FROM employees e WHERE e.employee_id = assets.employee_id) AS person_accountable FROM assets WHERE id = {$aid} LIMIT 1");
                          if ($a && $a->num_rows > 0) { $assetInfo = $a->fetch_assoc(); }
                        }
                      ?>
                      <tr>
                        <td>
                          <input type="hidden" name="items[<?= (int)$it['item_id'] ?>][item_id]" value="<?= (int)$it['item_id'] ?>">
                          <input type="hidden" name="items[<?= (int)$it['item_id'] ?>][asset_id]" class="asset-id-input" value="<?= (int)$it['asset_id'] ?>">
                          <input type="text" class="form-control asset-selector" list="assetsList" placeholder="Type to search..." value="">
                          <div class="form-text">Select an asset to auto-fill fields.</div>
                        </td>
                        <td><input type="date" name="items[<?= (int)$it['item_id'] ?>][date_acquired]" class="form-control text-center" value="<?= htmlspecialchars($it['date_acquired']) ?>"></td>
                        <td><input type="text" name="items[<?= (int)$it['item_id'] ?>][property_no]" class="form-control" value="<?= htmlspecialchars($it['property_no']) ?>"></td>
                        <td><input type="text" name="items[<?= (int)$it['item_id'] ?>][description]" class="form-control" value="<?= htmlspecialchars($it['description']) ?>"></td>
                        <td><input type="number" step="0.01" min="0" name="items[<?= (int)$it['item_id'] ?>][amount]" class="form-control text-end" value="<?= htmlspecialchars($it['amount']) ?>"></td>
                        <td><input type="text" name="items[<?= (int)$it['item_id'] ?>][condition_of_PPE]" class="form-control" value="<?= htmlspecialchars($it['condition_of_PPE']) ?>"></td>
                        <td><input type="text" class="form-control person-accountable" value="<?= htmlspecialchars($assetInfo['person_accountable'] ?? '') ?>" readonly></td>
                        <td><input type="number" class="form-control text-center quantity-display" value="<?= htmlspecialchars($assetInfo['quantity'] ?? '') ?>" readonly></td>
                        <td class="no-print text-center">
                          <button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-x-circle"></i></button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr class="template-row">
                      <td>
                        <input type="hidden" name="items[new_1][item_id]" value="">
                        <input type="hidden" name="items[new_1][asset_id]" class="asset-id-input" value="">
                        <input type="text" class="form-control asset-selector" list="assetsList" placeholder="Type to search...">
                        <div class="form-text">Select an asset to auto-fill fields.</div>
                      </td>
                      <td><input type="date" name="items[new_1][date_acquired]" class="form-control text-center" value=""></td>
                      <td><input type="text" name="items[new_1][property_no]" class="form-control" value=""></td>
                      <td><input type="text" name="items[new_1][description]" class="form-control" value=""></td>
                      <td><input type="number" step="0.01" min="0" name="items[new_1][amount]" class="form-control text-end" value=""></td>
                      <td><input type="text" name="items[new_1][condition_of_PPE]" class="form-control" value=""></td>
                      <td><input type="text" class="form-control person-accountable" value="" readonly></td>
                      <td><input type="number" class="form-control text-center quantity-display" value="" readonly></td>
                      <td class="no-print text-center"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-x-circle"></i></button></td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="text-start mt-3 no-print">
          <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Save Items</button>
        </div>
      </form>
  

  <datalist id="assetsList">
    <?php
      // Build JS map and datalist values
      $jsAssets = [];
      foreach ($assets as $a) {
        $label = trim(($a['inventory_tag'] ? $a['inventory_tag'].' - ' : '').$a['description']);
        $person = $a['person_accountable'] ?: 'N/A';
        $display = $label . ' [' . $person . ']';
        echo '<option value="'. htmlspecialchars($display) .'"></option>';
        $jsAssets[] = [
          'id' => (int)$a['id'],
          'label' => $display,
          'description' => $a['description'],
          'property_no' => $a['property_no'],
          'quantity' => $a['quantity'],
          'value' => $a['value'],
          'person' => $person
        ];
      }
    ?>
  </datalist>

  <script>
    const assetsIndex = new Map();
    <?php
      foreach ($jsAssets as $j) {
        echo 'assetsIndex.set('. json_encode($j['label']) .', '. json_encode($j) .');\n';
      }
    ?>

    function onAssetSelected(input) {
      const row = input.closest('tr');
      const val = input.value;
      const data = assetsIndex.get(val);
      const assetIdEl = row.querySelector('.asset-id-input');
      const descEl = row.querySelector('input[name$="[description]"]');
      const propEl = row.querySelector('input[name$="[property_no]"]');
      const amtEl = row.querySelector('input[name$="[amount]"]');
      const personEl = row.querySelector('.person-accountable');
      const qtyEl = row.querySelector('.quantity-display');
      if (data) {
        if (assetIdEl) assetIdEl.value = data.id;
        if (descEl) descEl.value = data.description || '';
        if (propEl) propEl.value = data.property_no || '';
        if (amtEl) amtEl.value = (parseFloat(data.value||0)).toFixed(2);
        if (personEl) personEl.value = data.person || '';
        if (qtyEl) qtyEl.value = data.quantity || '';
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
          <td>
            <input type="hidden" name="items[${uid}][item_id]" value="">
            <input type="hidden" name="items[${uid}][asset_id]" class="asset-id-input" value="">
            <input type="text" class="form-control asset-selector" list="assetsList" placeholder="Type to search...">
            <div class="form-text">Select an asset to auto-fill fields.</div>
          </td>
          <td><input type="date" name="items[${uid}][date_acquired]" class="form-control text-center" value=""></td>
          <td><input type="text" name="items[${uid}][property_no]" class="form-control" value=""></td>
          <td><input type="text" name="items[${uid}][description]" class="form-control" value=""></td>
          <td><input type="number" step="0.01" min="0" name="items[${uid}][amount]" class="form-control text-end" value=""></td>
          <td><input type="text" name="items[${uid}][condition_of_PPE]" class="form-control" value=""></td>
          <td><input type="text" class="form-control person-accountable" value="" readonly></td>
          <td><input type="number" class="form-control text-center quantity-display" value="" readonly></td>
          <td class="no-print text-center"><button type="button" class="btn btn-sm btn-danger remove-row"><i class="bi bi-x-circle"></i></button></td>
        `;
        document.querySelector('#itrItemsTable tbody').appendChild(tr);
      }
    });

    document.addEventListener('input', function(e){
      if (e.target && e.target.classList.contains('asset-selector')) {
        onAssetSelected(e.target);
      }
    });

    document.addEventListener('click', function(e){
      if (e.target && (e.target.classList.contains('remove-row') || e.target.closest('.remove-row'))) {
        const btn = e.target.closest('.remove-row');
        const tr = btn.closest('tr');
        tr.parentNode.removeChild(tr);
      }
    });
  </script>
