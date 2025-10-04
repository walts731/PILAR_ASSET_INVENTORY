<?php
require_once '../connect.php';
require_once '../includes/tag_format_helper.php';

$form_id = $_GET['id'] ?? '';

// Fetch the latest row only
$stmt = $conn->prepare("SELECT * FROM ris_form ORDER BY id DESC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$ris_data = $result->fetch_assoc() ?? [];
$stmt->close();


// Note: RIS No. should NOT be auto-generated. It will be fetched from the latest record (if any)
// via $ris_data above and shown as an editable field.

?>
<?php if (isset($_GET['add']) && $_GET['add'] === 'success'): ?>
  <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
    <strong>Success!</strong> RIS Form & Items saved successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>
<!-- Navigate to Saved RIS (floated right) -->
<a href="saved_ris.php?id=<?= urlencode($form_id) ?>" class="btn btn-info float-end mb-3">
  <i class="bi bi-archive-fill"></i> View Saved RIS
</a>

<form method="POST" action="save_ris.php" enctype="multipart/form-data">
  <input type="hidden" name="form_id" value="<?= htmlspecialchars($form_id) ?>">

  <!-- Header Image (display only) -->
  <div class="mb-3 text-center">
    <?php if (!empty($ris_data['header_image'])): ?>
      <img src="../img/<?= htmlspecialchars($ris_data['header_image']) ?>"
           class="img-fluid mb-2"
           style="max-width: 100%; height: auto; object-fit: contain;">
      <!-- Submit existing header image with the form -->
      <input type="hidden" name="header_image" value="<?= htmlspecialchars($ris_data['header_image']) ?>">
    <?php else: ?>
      <p class="text-muted">No header image available</p>
    <?php endif; ?>
  </div>


  <!-- Row 1: Division, Responsibility Center, RIS No., Date -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label for="division" class="form-label fw-semibold">Division</label>
      <input type="text" class="form-control shadow" id="division" name="division" placeholder="Enter Division">
    </div>
    <div class="col-md-3">
      <label for="responsibility_center" class="form-label fw-semibold">Responsibility Center</label>
      <input type="text" class="form-control shadow" id="responsibility_center" name="responsibility_center" placeholder="Enter Responsibility Center">
    </div>
    <div class="col-md-3">
      <label for="ris_no" class="form-label fw-semibold">RIS No. (Auto-generated)</label>
      <div class="input-group">
        <input type="text" class="form-control shadow" id="ris_no" name="ris_no" value="<?= previewTag('ris_no') ?>" readonly>
        <span class="input-group-text">
          <i class="bi bi-magic" title="Auto-generated"></i>
        </span>
      </div>
      <small class="text-muted">This number will be automatically assigned when you save the form.</small>
    </div>
    <div class="col-md-3">
      <label for="date" class="form-label fw-semibold">Date</label>
      <input type="date" class="form-control shadow" id="date" name="date"
        value="<?= date('Y-m-d') ?>">
    </div>
  </div>

  <!-- Row 2: Office, Responsibility Code, SAI No., Reason for Transfer -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label for="office_id" class="form-label fw-semibold">Office/Unit</label>
      <select class="form-select shadow" id="office_id" name="office_id" required>
        <option value="" disabled selected>Select Office</option>
        <?php
        $office_query = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");
        while ($row = $office_query->fetch_assoc()):
        ?>
          <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['office_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label for="responsibility_code" class="form-label fw-semibold">Code</label>
      <input type="text" class="form-control shadow" id="responsibility_code" name="responsibility_code" placeholder="Enter Code">
    </div>
    <div class="col-md-3">
      <label for="sai_no" class="form-label fw-semibold">SAI No. (Auto-generated)</label>
      <div class="input-group">
        <input type="text" class="form-control shadow" id="sai_no" name="sai_no" value="<?= previewTag('sai_no') ?>" readonly>
        <span class="input-group-text">
          <i class="bi bi-magic" title="Auto-generated"></i>
        </span>
      </div>
      <small class="text-muted">This number will be automatically assigned when you save the form.</small>
    </div>
    <div class="col-md-3">
      <label for="date" class="form-label fw-semibold">Date</label>
      <input type="date" class="form-control shadow" id="date" name="date"
        value="<?= date('Y-m-d') ?>">
    </div>
  </div>

  <!-- Items Table -->
  <table class="table table-bordered align-middle text-center">
    <thead>
      <tr class="table-secondary">
        <th colspan="4">REQUISITION</th>
        <th colspan="4">ISSUANCE</th>
      </tr>
      <tr class="table-light">
        <th>Stock No</th>
        <th>Unit</th>
        <th style="width: 30%;">DESCRIPTION</th>
        <th>Quantity</th>
        <th>Price</th>
        <th>Total Amount</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php for ($i = 0; $i < 1; $i++): ?>
        <tr>
          <input type="hidden" name="asset_id[]">
          <td><input type="text" class="form-control shadow" name="stock_no[]" value="1" readonly></td>
          <td>
            <select name="unit[]" class="form-select shadow">
              <option value="" disabled selected>Select Unit</option>
              <?php
              $unit_query = $conn->query("SELECT id, unit_name FROM unit ORDER BY unit_name ASC");
              while ($row = $unit_query->fetch_assoc()):
              ?>
                <option value="<?= $row['id'] ?>"><?= htmlspecialchars($row['unit_name']) ?></option>
              <?php endwhile; ?>
            </select>
          </td>
          <td style="position: relative;">
            <div class="input-group">
              <input type="text" class="form-control description-input shadow" name="description[]" autocomplete="off">
              <button type="button" class="btn btn-link p-0 ms-1 text-danger clear-description">&times;</button>
            </div>
          </td>
          <td><input type="number" class="form-control shadow" name="req_quantity[]" min="1"></td>
          <td><input type="number" step="0.01" class="form-control shadow" name="price[]"></td>
          <td><input type="text" class="form-control total shadow" name="total[]" readonly></td>
          <td>
            <button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button>
          </td>
        </tr>
      <?php endfor; ?>
    </tbody>
  </table>
  <button type="button" id="addRowBtn" class="btn btn-primary mb-3"><i class="bi bi-plus-circle"></i> Add Row</button>

  <!-- Purpose -->
  <div class="mb-3">
    <label for="purpose" class="form-label fw-bold">PURPOSE:</label>
    <textarea class="form-control shadow" name="purpose" id="purpose" rows="2"></textarea>
  </div>

  <!-- Footer Table -->
  <table class="table table-bordered text-center align-middle">
    <thead class="table-secondary">
      <tr>
        <th></th>
        <th>REQUESTED BY:</th>
        <th>APPROVED BY:</th>
        <th>ISSUED BY:</th>
        <th>RECEIVED BY:</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td>Printed Name:</td>
        <td><input type="text" class="form-control shadow" name="requested_by_name" value="<?= htmlspecialchars($ris_data['requested_by_name'] ?? '') ?>"></td>
        <td><input type="text" class="form-control shadow" name="approved_by_name" value="<?= htmlspecialchars($ris_data['approved_by_name'] ?? '') ?>"></td>
        <td><input type="text" class="form-control shadow" name="issued_by_name" value="<?= htmlspecialchars($ris_data['issued_by_name'] ?? '') ?>"></td>
        <td><input type="text" class="form-control shadow" name="received_by_name" ></td>
      </tr>
      <tr>
        <td>Designation:</td>
        <td><input type="text" class="form-control shadow" name="requested_by_designation" value="<?= htmlspecialchars($ris_data['requested_by_designation'] ?? '') ?>"></td>
        <td><input type="text" class="form-control shadow" name="approved_by_designation" value="<?= htmlspecialchars($ris_data['approved_by_designation'] ?? '') ?>"></td>
        <td><input type="text" class="form-control shadow" name="issued_by_designation" value="<?= htmlspecialchars($ris_data['issued_by_designation'] ?? '') ?>"></td>
        <td><input type="text" class="form-control shadow" name="received_by_designation" ></td>
      </tr>
      <tr>
        <td>Date:</td>
        <td><input type="date" class="form-control shadow" name="requested_by_date" value=""></td>
        <td><input type="date" class="form-control shadow" name="approved_by_date" value=""></td>
        <td><input type="date" class="form-control shadow" name="issued_by_date" value=""></td>
        <td><input type="date" class="form-control shadow" name="received_by_date" value=""></td>
      </tr>
      <tr style="display:none;">
  <td>Footer Date:</td>
  <td colspan="4">
    <input type="hidden" name="footer_date" value="<?= htmlspecialchars($ris_data['footer_date'] ?? date('Y-m-d')) ?>">
  </td>
</tr>

    </tbody>
  </table>

  <button type="submit" class="btn btn-primary"><i class="bi bi-send-check-fill"></i>Save</button>
</form>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const tableBody = document.querySelector("tbody");

    // Add Row button click - clones structure consistent with the first row
    function addRow() {
      const newRow = document.createElement("tr");
      
      // Get the unit options from the existing select
      const existingSelect = document.querySelector("select[name='unit[]']");
      const unitOptions = existingSelect ? existingSelect.innerHTML : '<option value="" disabled selected>Select Unit</option>';
      
      newRow.innerHTML = `
        <input type="hidden" name="asset_id[]">
        <td><input type="text" class="form-control shadow" name="stock_no[]" readonly></td>
        <td>
          <select name="unit[]" class="form-select shadow">
            ${unitOptions}
          </select>
        </td>
        <td style="position: relative;">
          <div class="input-group">
            <input type="text" class="form-control description-input shadow" name="description[]" autocomplete="off">
            <button type="button" class="btn btn-link p-0 ms-1 text-danger clear-description" style="border: none;">&times;</button>
          </div>
        </td>
        <td><input type="number" class="form-control shadow" name="req_quantity[]" min="1"></td>
        <td><input type="number" step="0.01" class="form-control shadow" name="price[]"></td>
        <td><input type="text" class="form-control total shadow" name="total[]" readonly></td>
        <td><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></td>
      `;
      
      document.querySelector("table tbody").appendChild(newRow);
      
      // Set incremental stock number
      updateStockNumbers();
    }

    // Function to update stock numbers sequentially
    function updateStockNumbers() {
      const stockInputs = document.querySelectorAll("input[name='stock_no[]']");
      stockInputs.forEach((input, index) => {
        input.value = index + 1;
      });
    }

    // Add Row button event listener
    const addBtn = document.getElementById("addRowBtn");
    if (addBtn) addBtn.addEventListener("click", addRow);

    // Handle clear button click
    tableBody.addEventListener("click", function(e) {
      if (e.target.classList.contains("clear-description")) {
        let row = e.target.closest("tr");
        let descInput = row.querySelector(".description-input");
        let reqQtyInput = row.querySelector("input[name='req_quantity[]']");
        let unitSelect = row.querySelector("select[name='unit[]']");
        let priceInput = row.querySelector("input[name='price[]']");
        let stockNoInput = row.querySelector("input[name='stock_no[]']");
        let totalField = row.querySelector(".total");

        descInput.value = "";
        reqQtyInput.removeAttribute("max");
        reqQtyInput.placeholder = "";
        reqQtyInput.value = "";
        unitSelect.value = "";
        priceInput.value = "";
        // Keep stock number as is (incremental)
        if (totalField) totalField.value = "";
      }
    });

    // Handle remove row click - remove if >1 rows, otherwise clear the only row
    tableBody.addEventListener("click", function(e) {
      const btn = e.target.closest('.remove-row');
      if (!btn) return;
      const row = btn.closest('tr');
      if (!row) return;

      const rows = tableBody.querySelectorAll('tr');
      if (rows.length > 1) {
        row.remove();
        // Update stock numbers after removing a row
        updateStockNumbers();
      } else {
        // Clear the single remaining row
        let descInput = row.querySelector('.description-input');
        let reqQtyInput = row.querySelector("input[name='req_quantity[]']");
        let unitSelect = row.querySelector("select[name='unit[]']");
        let priceInput = row.querySelector("input[name='price[]']");
        let stockNoInput = row.querySelector("input[name='stock_no[]']");
        let totalField = row.querySelector('.total');

        if (descInput) descInput.value = '';
        if (reqQtyInput) { reqQtyInput.removeAttribute('max'); reqQtyInput.placeholder=''; reqQtyInput.value = ''; }
        if (unitSelect) unitSelect.value = '';
        if (priceInput) priceInput.value = '';
        if (stockNoInput) stockNoInput.value = '1';
        if (totalField) totalField.value = '';
      }
    });

    // Auto-calc totals
    tableBody.addEventListener("input", function(e) {
      if (e.target.name === 'req_quantity[]' || e.target.name === 'price[]') {
        let row = e.target.closest('tr');
        let qty = parseFloat(row.querySelector("input[name='req_quantity[]']").value) || 0;
        let price = parseFloat(row.querySelector("input[name='price[]']").value) || 0;
        row.querySelector('.total').value = (qty * price).toFixed(2);
      }
    });
  });
</script>