<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch system settings for title/logo if needed
$system = [
  'logo' => '../img/default-logo.png',
  'system_title' => 'Inventory System'
];
$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
  $system = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Fuel Inventory</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link href="css/dashboard.css" rel="stylesheet" />
  <style>
    .page-header { background: linear-gradient(135deg, #f8f9fa 0%, #eef3ff 100%); border: 1px solid #e9ecef; border-radius: .75rem; }
    .page-header .title { font-weight: 600; }
  </style>
</head>
<body>

  <?php include 'includes/sidebar.php'; ?>

  <div class="main">
    <?php include 'includes/topbar.php'; ?>

    <div class="container-fluid px-0 mb-3">
      <div class="page-header p-3 p-sm-4 d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-3">
          <div class="rounded-circle d-flex align-items-center justify-content-center bg-white border" style="width:48px;height:48px;">
            <i class="bi bi-fuel-pump text-primary fs-4"></i>
          </div>
          <div>
            <div class="h4 mb-0 title">Fuel Inventory</div>
            <div class="text-muted small">Manage fuel stocks, receipts, and usage</div>
          </div>
        </div>
      </div>
    </div>

    <div class="container-fluid">
      <ul class="nav nav-tabs mb-3" id="fuelTabs" role="tablist">
        <li class="nav-item" role="presentation">
          <button class="nav-link active" id="fuel-log-tab" data-bs-toggle="tab" data-bs-target="#fuel-log" type="button" role="tab">
            <i class="bi bi-journal-plus me-1"></i> Fuel Log
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="fuel-report-tab" data-bs-toggle="tab" data-bs-target="#fuel-report" type="button" role="tab">
            <i class="bi bi-bar-chart-line me-1"></i> Reports
          </button>
        </li>
      </ul>

      <div class="tab-content" id="fuelTabsContent">
        <!-- Fuel Log Tab -->
        <div class="tab-pane fade show active" id="fuel-log" role="tabpanel">
          <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
              <strong><i class="bi bi-fuel-pump me-1"></i> Fuel Records</strong>
              <div class="d-flex gap-2 align-items-center">
                <input type="text" id="fuelSearch" class="form-control form-control-sm" placeholder="Search..." />
                <button class="btn btn-sm btn-outline-secondary" id="exportCsvBtn" title="Export CSV"><i class="bi bi-filetype-csv"></i></button>
                <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFuelModal">
                  <i class="bi bi-plus-circle me-1"></i> Add Fuel
                </button>
              </div>
            </div>
            <div class="card-body table-responsive">
              <table class="table table-striped align-middle" id="fuelTable">
                <thead class="table-light">
                  <tr>
                    <th>Date & Time</th>
                    <th>Fuel Type</th>
                    <th>Quantity (L)</th>
                    <th>Unit Price</th>
                    <th>Total Cost</th>
                    <th>Storage</th>
                    <th>DR No.</th>
                    <th>Supplier</th>
                    <th>Received By</th>
                    <th>Remarks</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Client-side inserted rows for now -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Reports Placeholder -->
        <div class="tab-pane fade" id="fuel-report" role="tabpanel">
          <div class="card shadow-sm">
            <div class="card-body">
              <p class="text-muted mb-0">Reports and analytics coming soon.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include 'includes/footer.php'; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Add Fuel Modal -->
  <div class="modal fade" id="addFuelModal" tabindex="-1" aria-labelledby="addFuelModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addFuelModalLabel"><i class="bi bi-fuel-pump me-2"></i>Add Fuel Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="fuelForm" novalidate>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="date_time" class="form-label">Date & Time</label>
                <input type="datetime-local" id="date_time" name="date_time" class="form-control" required />
              </div>
              <div class="col-12 col-md-6">
                <label for="fuel_type" class="form-label">Fuel Type</label>
                <select id="fuel_type" name="fuel_type" class="form-select" required>
                  <option value="" selected disabled>Choose type...</option>
                  <option>Diesel</option>
                  <option>Gasoline 91</option>
                  <option>Gasoline 95</option>
                  <option>Gasoline 97</option>
                  <option>Other</option>
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label for="quantity" class="form-label">Quantity (Liters)</label>
                <input type="number" step="0.01" min="0" id="quantity" name="quantity" class="form-control" required />
              </div>
              <div class="col-12 col-md-4">
                <label for="unit_price" class="form-label">Unit Price</label>
                <div class="input-group">
                  <span class="input-group-text">₱</span>
                  <input type="number" step="0.01" min="0" id="unit_price" name="unit_price" class="form-control" required />
                </div>
              </div>
              <div class="col-12 col-md-4">
                <label for="total_cost" class="form-label">Total Cost</label>
                <div class="input-group">
                  <span class="input-group-text">₱</span>
                  <input type="number" step="0.01" min="0" id="total_cost" name="total_cost" class="form-control" readonly />
                </div>
                <div class="form-text">Automatically calculated</div>
              </div>
              <div class="col-12 col-md-6">
                <label for="storage_location" class="form-label">Storage Location</label>
                <input type="text" id="storage_location" name="storage_location" class="form-control" required />
              </div>
              <div class="col-12 col-md-6">
                <label for="delivery_receipt" class="form-label">Delivery Receipt</label>
                <input type="text" id="delivery_receipt" name="delivery_receipt" class="form-control" />
              </div>
              <div class="col-12 col-md-6">
                <label for="supplier_name" class="form-label">Supplier Name</label>
                <input type="text" id="supplier_name" name="supplier_name" class="form-control" required />
              </div>
              <div class="col-12 col-md-6">
                <label for="received_by" class="form-label">Received By</label>
                <input type="text" id="received_by" name="received_by" class="form-control" required />
              </div>
              <div class="col-12">
                <label for="remarks" class="form-label">Remarks</label>
                <textarea id="remarks" name="remarks" rows="2" class="form-control" placeholder="Optional notes..."></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" id="saveFuelBtn" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save</button>
        </div>
      </div>
    </div>
  </div>
  <script>
    // Auto-calc total cost
    function updateTotal() {
      const q = parseFloat(document.getElementById('quantity').value) || 0;
      const p = parseFloat(document.getElementById('unit_price').value) || 0;
      const total = (q * p);
      document.getElementById('total_cost').value = total ? total.toFixed(2) : '';
    }
    document.addEventListener('input', function(e){
      if (e.target && (e.target.id === 'quantity' || e.target.id === 'unit_price')) {
        updateTotal();
      }
    });

    // Helpers
    const fuelTableBody = document.querySelector('#fuelTable tbody');
    const fuelSearch = document.getElementById('fuelSearch');
    const addFuelModalEl = document.getElementById('addFuelModal');
    const addFuelModal = new bootstrap.Modal(addFuelModalEl);
    const peso = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' });

    function renderRow(rec) {
      const tr = document.createElement('tr');
      const dt = rec.date_time ? new Date(rec.date_time) : null;
      tr.innerHTML = `
        <td>${dt ? dt.toLocaleString() : ''}</td>
        <td>${rec.fuel_type || ''}</td>
        <td>${Number(rec.quantity || 0).toFixed(2)}</td>
        <td>${peso.format(Number(rec.unit_price || 0))}</td>
        <td>${peso.format(Number(rec.total_cost || 0))}</td>
        <td>${rec.storage_location || ''}</td>
        <td>${rec.delivery_receipt || ''}</td>
        <td>${rec.supplier_name || ''}</td>
        <td>${rec.received_by || ''}</td>
        <td>${rec.remarks || ''}</td>
      `;
      return tr;
    }

    async function loadFuelRecords() {
      try {
        const res = await fetch('list_fuel_records.php', { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Failed to load records');
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load');
        fuelTableBody.innerHTML = '';
        data.records.forEach(r => fuelTableBody.appendChild(renderRow(r)));
      } catch (err) {
        alert('Unable to load fuel records.');
        console.error(err);
      }
    }

    // initial load
    document.addEventListener('DOMContentLoaded', loadFuelRecords);

    document.getElementById('saveFuelBtn').addEventListener('click', async function() {
      const fuelForm = document.getElementById('fuelForm');
      if (!fuelForm.checkValidity()) { fuelForm.classList.add('was-validated'); return; }
      const btn = this;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
      try {
        const formData = new FormData(fuelForm);
        const res = await fetch('save_fuel_record.php', { method: 'POST', body: formData, credentials: 'same-origin' });
        if (!res.ok) throw new Error('Failed to save');
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to save');
        fuelTableBody.prepend(renderRow(data.record));
        fuelForm.reset();
        document.getElementById('total_cost').value = '';
        fuelForm.classList.remove('was-validated');
        addFuelModal.hide();
      } catch (err) {
        alert('Unable to save fuel record.');
        console.error(err);
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Save';
      }
    });

    // Basic search filter
    fuelSearch.addEventListener('input', function() {
      const q = this.value.toLowerCase();
      [...fuelTableBody.querySelectorAll('tr')].forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });

    // Export CSV of current rows
    document.getElementById('exportCsvBtn').addEventListener('click', function() {
      const rows = [['Date & Time','Fuel Type','Quantity (L)','Unit Price','Total Cost','Storage','DR No.','Supplier','Received By','Remarks']];
      fuelTableBody.querySelectorAll('tr').forEach(tr => {
        const cols = [...tr.children].map(td => '"' + td.textContent.replace(/"/g,'""') + '"');
        rows.push(cols);
      });
      const csv = rows.map(r => r.join(',')).join('\n');
      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = 'fuel_records.csv';
      a.click();
      URL.revokeObjectURL(url);
    });
  </script>
</body>
</html>
