<?php
require_once '../connect.php';

// Fetch the very first row only
$stmt = $conn->prepare("SELECT * FROM ris_form ORDER BY id ASC LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();
$ris_data = $result->fetch_assoc() ?? [];
$stmt->close();
?>


<form method="POST" enctype="multipart/form-data">

  <!-- Header Image -->
  <div class="mb-3 text-center">
    <?php if (!empty($ris_data['header_image'])): ?>
      <img src="../img/<?= htmlspecialchars($ris_data['header_image']) ?>"
        class="img-fluid mb-3"
        style="max-width: 100%; height: auto; object-fit: contain;">
    <?php endif; ?>
  </div>

  <!-- Row 1: Division, Responsibility Center, RIS No., Date -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label for="division" class="form-label fw-semibold">Division</label>
      <input type="text" class="form-control" id="division" name="division"
        value="<?= htmlspecialchars($ris_data['division'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label for="responsibility_center" class="form-label fw-semibold">Responsibility Center</label>
      <input type="text" class="form-control" id="responsibility_center" name="responsibility_center"
        value="<?= htmlspecialchars($ris_data['responsibility_center'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label for="ris_no" class="form-label fw-semibold">RIS No.</label>
      <input type="text" class="form-control" id="ris_no" name="ris_no"
        value="<?= htmlspecialchars($ris_data['ris_no'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label for="date" class="form-label fw-semibold">Date</label>
      <input type="date" class="form-control" id="date" name="date"
        value="<?= htmlspecialchars($ris_data['date'] ?? date('Y-m-d')) ?>">
    </div>
  </div>

  <!-- Row 2: Office, Responsibility Code, SAI No., Reason for Transfer -->
  <div class="row mb-3">
    <div class="col-md-3">
      <label for="office_id" class="form-label fw-semibold">Office/Unit</label>
      <select class="form-select" id="office_id" name="office_id" required>
        <option value="" disabled <?= !isset($ris_data['office_id']) ? 'selected' : '' ?>>Select Office</option>
        <?php
        $office_query = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");
        while ($row = $office_query->fetch_assoc()):
          $selected = (isset($ris_data['office_id']) && $ris_data['office_id'] == $row['id']) ? 'selected' : '';
        ?>
          <option value="<?= $row['id'] ?>" <?= $selected ?>><?= htmlspecialchars($row['office_name']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label for="responsibility_code" class="form-label fw-semibold">Code</label>
      <input type="text" class="form-control" id="responsibility_code" name="responsibility_code"
        value="<?= htmlspecialchars($ris_data['responsibility_code'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label for="sai_no" class="form-label fw-semibold">SAI No.</label>
      <input type="text" class="form-control" id="sai_no" name="sai_no"
        value="<?= htmlspecialchars($ris_data['sai_no'] ?? '') ?>">
    </div>
    <div class="col-md-3">
      <label for="reason_for_transfer" class="form-label fw-semibold">Reason for Transfer</label>
      <input type="text" class="form-control" id="reason_for_transfer" name="reason_for_transfer"
        value="<?= htmlspecialchars($ris_data['reason_for_transfer'] ?? '') ?>">
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
        <th>Quantity</th>
        <th>Signature</th>
        <th>Price</th>
        <th>Total Amount</th>
      </tr>
    </thead>
    <tbody>
      <?php for ($i = 0; $i < 5; $i++): ?>
        <tr>
          <td><input type="text" class="form-control" name="stock_no[]"></td>
          <td>
            <select name="unit[]" class="form-select" required>
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
              <input type="text" class="form-control description-input" name="description[]" autocomplete="off" list="asset_list">
              <button type="button" class="btn btn-link p-0 ms-1 text-danger clear-description">&times;</button>
            </div>
          </td>
          <td><input type="number" class="form-control" name="req_quantity[]" min="1"></td>
          <td><input type="number" class="form-control" name="iss_quantity[]" min="1"></td>
          <td><input type="text" class="form-control" name="signature[]"></td>
          <td><input type="number" step="0.01" class="form-control" name="price[]"></td>
          <td><input type="text" class="form-control total" readonly></td>
        </tr>
      <?php endfor; ?>
      <datalist id="asset_list">
        <?php
        $assets_query = $conn->query("SELECT id, description, quantity, unit, value FROM assets ORDER BY description ASC");
        while ($asset = $assets_query->fetch_assoc()):
        ?>
          <option value="<?= htmlspecialchars($asset['description']) ?>"
            data-id="<?= $asset['id'] ?>"
            data-stock="<?= $asset['quantity'] ?>"
            data-unit="<?= htmlspecialchars($asset['unit']) ?>"
            data-price="<?= $asset['value'] ?>">
          <?php endwhile; ?>
      </datalist>
    </tbody>
  </table>
  <button type="button" id="addRowBtn" class="btn btn-primary mb-3"><i class="bi bi-plus-circle"></i> Add Row</button>

  <!-- Purpose -->
  <div class="mb-3">
    <label for="purpose" class="form-label fw-bold">PURPOSE:</label>
    <textarea class="form-control" name="purpose" id="purpose" rows="2"><?= htmlspecialchars($ris_data['reason_for_transfer'] ?? '') ?></textarea>
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
        <td><input type="text" class="form-control" name="requested_by_name" value="<?= htmlspecialchars($ris_data['requested_by_name'] ?? '') ?>"></td>
        <td><input type="text" class="form-control" name="approved_by_name" value="<?= htmlspecialchars($ris_data['approved_by_name'] ?? '') ?>"></td>
        <td><input type="text" class="form-control" name="issued_by_name" value="<?= htmlspecialchars($ris_data['issued_by_name'] ?? '') ?>"></td>
        <td><input type="text" class="form-control" name="received_by_name" value="<?= htmlspecialchars($ris_data['received_by_name'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Designation:</td>
        <td><input type="text" class="form-control" name="requested_by_designation" value="<?= htmlspecialchars($ris_data['requested_by_designation'] ?? '') ?>"></td>
        <td><input type="text" class="form-control" name="approved_by_designation" value="<?= htmlspecialchars($ris_data['approved_by_designation'] ?? '') ?>"></td>
        <td><input type="text" class="form-control" name="issued_by_designation" value="<?= htmlspecialchars($ris_data['issued_by_designation'] ?? '') ?>"></td>
        <td><input type="text" class="form-control" name="received_by_designation" value="<?= htmlspecialchars($ris_data['received_by_designation'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Date:</td>
        <td><input type="date" class="form-control" name="requested_by_date" value="<?= htmlspecialchars($ris_data['requested_by_date'] ?? date('Y-m-d')) ?>"></td>
        <td><input type="date" class="form-control" name="approved_by_date" value="<?= htmlspecialchars($ris_data['approved_by_date'] ?? date('Y-m-d')) ?>"></td>
        <td><input type="date" class="form-control" name="issued_by_date" value="<?= htmlspecialchars($ris_data['issued_by_date'] ?? date('Y-m-d')) ?>"></td>
        <td><input type="date" class="form-control" name="received_by_date" value="<?= htmlspecialchars($ris_data['received_by_date'] ?? date('Y-m-d')) ?>"></td>
      </tr>
      <tr>
        <td>Footer Date:</td>
        <td colspan="4"><input type="date" class="form-control" name="footer_date" value="<?= htmlspecialchars($ris_data['footer_date'] ?? date('Y-m-d')) ?>"></td>
      </tr>
    </tbody>
  </table>

  <button type="submit" class="btn btn-primary"><i class="bi bi-send-check-fill"></i>Save</button>
</form>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    // Only target rows that have description inputs
    const rows = document.querySelectorAll("tbody tr:has(.description-input)");
    const allOptions = Array.from(document.querySelectorAll("#asset_list option"));

    function updateDatalist() {
      const selectedDescriptions = Array.from(document.querySelectorAll(".description-input"))
        .map(input => input.value.trim())
        .filter(val => val !== "");

      rows.forEach(row => {
        const descInput = row.querySelector(".description-input");
        if (!descInput) return; // skip if no description input

        const listId = "asset_list_" + Math.random().toString(36).substring(2, 9);
        let datalist = document.createElement("datalist");
        datalist.id = listId;

        const optionsHTML = allOptions
          .filter(opt => !selectedDescriptions.includes(opt.value.trim()) || opt.value.trim() === descInput.value.trim())
          .map(opt => `<option value="${opt.value}" 
                        data-id="${opt.dataset.id}" 
                        data-stock="${opt.dataset.stock}" 
                        data-unit="${opt.dataset.unit}"
                        data-price="${opt.dataset.price}"></option>`)
          .join("");

        datalist.innerHTML = optionsHTML;
        document.body.appendChild(datalist);
        descInput.setAttribute("list", listId);
      });
    }

    rows.forEach(row => {
      const descInput = row.querySelector(".description-input");
      const reqQtyInput = row.querySelector("input[name='req_quantity[]']");
      const unitSelect = row.querySelector("select[name='unit[]']");
      const priceInput = row.querySelector("input[name='price[]']");

      if (!descInput) return; // skip footer or other rows

      descInput.addEventListener("input", function() {
        const val = this.value;
        const option = allOptions.find(opt => opt.value === val);

        if (option) {
          // Max quantity
          const maxStock = option.dataset.stock || "";
          if (maxStock) {
            reqQtyInput.max = maxStock;
            reqQtyInput.placeholder = `Max: ${maxStock}`;
          } else {
            reqQtyInput.removeAttribute("max");
            reqQtyInput.placeholder = "";
          }

          // Autofill unit
          const unitName = option.dataset.unit || "";
          if (unitName) {
            const matchOption = Array.from(unitSelect.options)
              .find(opt => opt.text.trim().toLowerCase() === unitName.trim().toLowerCase());
            if (matchOption) {
              unitSelect.value = matchOption.value;
            }
          }

          // Autofill price
          if (priceInput && option.dataset.price) {
            priceInput.value = option.dataset.price;
          }

        } else {
          reqQtyInput.removeAttribute("max");
          reqQtyInput.placeholder = "";
          unitSelect.value = "";
          if (priceInput) priceInput.value = "";
        }

        updateDatalist();
      });
    });

    updateDatalist();
  });

  // Auto-calculate total amount dynamically
  document.addEventListener('input', function(e) {
    if (e.target.name === 'req_quantity[]' || e.target.name === 'price[]') {
      let row = e.target.closest('tr');
      let qty = parseFloat(row.querySelector("input[name='req_quantity[]']").value) || 0;
      let price = parseFloat(row.querySelector("input[name='price[]']").value) || 0;
      let total = qty * price;
      let totalField = row.querySelector('.total');
      if (totalField) {
        totalField.value = total.toFixed(2);
      }
    }
  });

  // Handle click on X to clear description
  document.addEventListener("click", function(e) {
    if (e.target.classList.contains("clear-description")) {
      let row = e.target.closest("tr");
      if (row) {
        let descInput = row.querySelector(".description-input");
        let reqQtyInput = row.querySelector("input[name='req_quantity[]']");
        let unitSelect = row.querySelector("select[name='unit[]']");
        let priceInput = row.querySelector("input[name='price[]']");
        let totalField = row.querySelector(".total");

        // Clear the description
        descInput.value = "";

        // Reset related fields
        reqQtyInput.removeAttribute("max");
        reqQtyInput.placeholder = "";
        reqQtyInput.value = "";
        unitSelect.value = "";
        priceInput.value = "";
        if (totalField) totalField.value = "";

        // Trigger datalist update
        descInput.dispatchEvent(new Event("input"));
      }
    }
  });

  document.addEventListener("DOMContentLoaded", function() {
    const tableBody = document.querySelector("tbody");
    const allOptions = Array.from(document.querySelectorAll("#asset_list option"));

    function updateDatalist() {
      const selectedDescriptions = Array.from(document.querySelectorAll(".description-input"))
        .map(input => input.value.trim())
        .filter(val => val !== "");

      document.querySelectorAll(".description-input").forEach(descInput => {
        const listId = "asset_list_" + Math.random().toString(36).substring(2, 9);
        let datalist = document.createElement("datalist");
        datalist.id = listId;

        const optionsHTML = allOptions
          .filter(opt => !selectedDescriptions.includes(opt.value.trim()) || opt.value.trim() === descInput.value.trim())
          .map(opt => `<option value="${opt.value}" 
                        data-id="${opt.dataset.id}" 
                        data-stock="${opt.dataset.stock}" 
                        data-unit="${opt.dataset.unit}"
                        data-price="${opt.dataset.price}"></option>`)
          .join("");

        datalist.innerHTML = optionsHTML;
        document.body.appendChild(datalist);
        descInput.setAttribute("list", listId);
      });
    }

    function bindRowEvents(row) {
      const descInput = row.querySelector(".description-input");
      const reqQtyInput = row.querySelector("input[name='req_quantity[]']");
      const unitSelect = row.querySelector("select[name='unit[]']");
      const priceInput = row.querySelector("input[name='price[]']");

      if (!descInput) return;

      descInput.addEventListener("input", function() {
        const val = this.value;
        const option = allOptions.find(opt => opt.value === val);

        if (option) {
          const maxStock = option.dataset.stock || "";
          if (maxStock) {
            reqQtyInput.max = maxStock;
            reqQtyInput.placeholder = `Max: ${maxStock}`;
          } else {
            reqQtyInput.removeAttribute("max");
            reqQtyInput.placeholder = "";
          }

          const unitName = option.dataset.unit || "";
          if (unitName) {
            const matchOption = Array.from(unitSelect.options)
              .find(opt => opt.text.trim().toLowerCase() === unitName.trim().toLowerCase());
            if (matchOption) {
              unitSelect.value = matchOption.value;
            }
          }

          if (priceInput && option.dataset.price) {
            priceInput.value = option.dataset.price;
          }
        } else {
          reqQtyInput.removeAttribute("max");
          reqQtyInput.placeholder = "";
          unitSelect.value = "";
          if (priceInput) priceInput.value = "";
        }

        updateDatalist();
      });
    }

    function addRow() {
      const newRow = document.createElement("tr");
      newRow.innerHTML = `
      <td><input type="text" class="form-control" name="stock_no[]"></td>
      <td>
        <select name="unit[]" class="form-select" required>
          <option value="" disabled selected>Select Unit</option>
          ${document.querySelector("select[name='unit[]']").innerHTML}
        </select>
      </td>
      <td style="position: relative;">
        <div class="input-group">
          <input type="text" class="form-control description-input" name="description[]" autocomplete="off">
          <button type="button" class="btn btn-link p-0 ms-1 text-danger clear-description" style="border: none;">&times;</button>
        </div>
      </td>
      <td><input type="number" class="form-control" name="req_quantity[]" min="1"></td>
      <td><input type="number" class="form-control" name="iss_quantity[]" min="1"></td>
      <td><input type="text" class="form-control" name="signature[]"></td>
      <td><input type="number" step="0.01" class="form-control" name="price[]"></td>
      <td><input type="text" class="form-control total" readonly></td>
    `;
      tableBody.appendChild(newRow);
      bindRowEvents(newRow);
      updateDatalist();
    }

    // Initial bind for existing rows
    document.querySelectorAll("tbody tr").forEach(row => bindRowEvents(row));

    // Add Row button click
    document.getElementById("addRowBtn").addEventListener("click", addRow);

    // Handle clear button click
    tableBody.addEventListener("click", function(e) {
      if (e.target.classList.contains("clear-description")) {
        let row = e.target.closest("tr");
        let descInput = row.querySelector(".description-input");
        let reqQtyInput = row.querySelector("input[name='req_quantity[]']");
        let unitSelect = row.querySelector("select[name='unit[]']");
        let priceInput = row.querySelector("input[name='price[]']");
        let totalField = row.querySelector(".total");

        descInput.value = "";
        reqQtyInput.removeAttribute("max");
        reqQtyInput.placeholder = "";
        reqQtyInput.value = "";
        unitSelect.value = "";
        priceInput.value = "";
        if (totalField) totalField.value = "";

        descInput.dispatchEvent(new Event("input"));
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

    updateDatalist();
  });
</script>