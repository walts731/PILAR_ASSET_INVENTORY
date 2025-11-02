<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Permission guard: allow admin/office_admin or explicit fuel_inventory permission
function user_has_fuel_permission(mysqli $conn, int $user_id): bool {
  $role = null;
  if ($stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1")) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) { $role = $row['role'] ?? null; }
    $stmt->close();
  }
  if ($role === 'admin' || $role === 'user') return true;
  if ($stmt2 = $conn->prepare("SELECT 1 FROM user_permissions WHERE user_id = ? AND permission = 'fuel_inventory' LIMIT 1")) {
    $stmt2->bind_param('i', $user_id);
    $stmt2->execute();
    $stmt2->store_result();
    $ok = $stmt2->num_rows > 0;
    $stmt2->close();
    return $ok;
  }
  return false;
}

if (!user_has_fuel_permission($conn, (int)$_SESSION['user_id'])) {
  // Redirect unauthorized users away
  header("Location: admin_dashboard.php?error=forbidden");
  exit();
}

// Fetch system settings for title/logo if needed
$system = [
  'logo' => '../img/default-logo.png',
  'system_title' => 'Inventory System'
];
$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
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

  <!-- Add Fuel Out Modal -->
  <div class="modal fade" id="addFuelOutModal" tabindex="-1" aria-labelledby="addFuelOutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addFuelOutModalLabel"><i class="bi bi-truck me-2"></i>Add Fuel Out Record</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="fuelOutAlert" class="alert alert-danger alert-dismissible fade show d-none" role="alert">
            <span class="fuel-out-alert-text">Insufficient stock for the selected fuel type. Please reduce the number of liters or update Main Inventory stock, then try again.</span>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <form id="fuelOutForm" novalidate>
            <div class="row g-3">
              <div class="col-12 col-md-6">
                <label for="fo_fuel_type" class="form-label">Fuel Type</label>
                <select id="fo_fuel_type" name="fo_fuel_type" class="form-select" required>
                  <option value="" selected disabled>Loading...</option>
                </select>
              </div>
              <div class="col-12 col-md-4">
                <label for="fo_date" class="form-label">Date</label>
                <input type="date" id="fo_date" name="fo_date" class="form-control" required />
              </div>
              <div class="col-12 col-md-4">
                <label for="fo_time_in" class="form-label">Time In</label>
                <input type="time" id="fo_time_in" name="fo_time_in" class="form-control" required />
              </div>
              <div class="col-12 col-md-4">
                <label for="fo_fuel_no" class="form-label">Fuel No</label>
                <input type="text" id="fo_fuel_no" name="fo_fuel_no" class="form-control" />
              </div>
              <div class="col-12 col-md-4">
                <label for="fo_plate_no" class="form-label">Plate No</label>
                <input type="text" id="fo_plate_no" name="fo_plate_no" class="form-control" />
              </div>
              <div class="col-12 col-md-8">
                <label for="fo_request" class="form-label">Request</label>
                <input type="text" id="fo_request" name="fo_request" class="form-control" />
              </div>
              <div class="col-12 col-md-4">
                <label for="fo_liters" class="form-label">No. of Liters</label>
                <input type="number" step="0.01" min="0" id="fo_liters" name="fo_liters" class="form-control" required />
              </div>
              <div class="col-12 col-md-4">
                <label for="fo_vehicle_type" class="form-label">Vehicle Type</label>
                <input type="text" id="fo_vehicle_type" name="fo_vehicle_type" class="form-control" />
              </div>
              <div class="col-12 col-md-4">
                <label for="fo_receiver" class="form-label">Receiver Name</label>
                <input type="text" id="fo_receiver" name="fo_receiver" class="form-control" required />
              </div>
              <div class="col-12 col-md-4">
                <label for="fo_time_out" class="form-label">Time Out</label>
                <input type="time" id="fo_time_out" name="fo_time_out" class="form-control" />
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" id="saveFuelOutBtn" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save</button>
        </div>
      </div>
    </div>
  </div>
  
  <!-- Add Fuel Type Modal -->
  <div class="modal fade" id="addFuelTypeModal" tabindex="-1" aria-labelledby="addFuelTypeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addFuelTypeModalLabel"><i class="bi bi-gear me-2"></i>Add Fuel Type</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="new_fuel_type" class="form-label">Fuel Type Name</label>
            <input type="text" id="new_fuel_type" class="form-control" placeholder="e.g., Diesel" required />
            <div class="form-text">This will appear in the Fuel Type dropdown.</div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" id="saveFuelTypeBtn" class="btn btn-primary"><i class="bi bi-save me-1"></i> Save Type</button>
        </div>
      </div>
    </div>
  </div>
          <div>
            <div class="h4 mb-0 title">Fuel Inventory</div>
            <div class="text-muted small">Manage fuel stocks, receipts, and usage</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Alert Container for Success Messages -->
    <div id="alertContainer" class="container-fluid mb-3" style="display: none;">
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>
        <span id="alertMessage"></span>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
          <button class="nav-link" id="fuel-out-tab" data-bs-toggle="tab" data-bs-target="#fuel-out" type="button" role="tab">
            <i class="bi bi-truck me-1"></i> Fuel Out
          </button>
        </li>
        <li class="nav-item" role="presentation">
          <button class="nav-link" id="fuel-main-tab" data-bs-toggle="tab" data-bs-target="#fuel-main" type="button" role="tab">
            <i class="bi bi-collection me-1"></i> Main Inventory
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
            <div class="card-header">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong><i class="bi bi-fuel-pump me-1"></i> Fuel Records</strong>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <div class="d-flex align-items-center gap-2">
                    <label for="fuelLogDateFilter" class="form-label mb-0 small">Filter:</label>
                    <select id="fuelLogDateFilter" class="form-select form-select-sm" style="min-width: 120px;">
                      <option value="all">All Records</option>
                      <option value="current_month">Current Month</option>
                      <option value="current_quarter">Current Quarter</option>
                      <option value="current_year">Current Year</option>
                      <option value="last_month">Last Month</option>
                      <option value="last_quarter">Last Quarter</option>
                      <option value="last_year">Last Year</option>
                      <option value="custom">Custom Range</option>
                    </select>
                  </div>
                  <div id="customDateRangeLog" class="d-flex align-items-center gap-2" style="display: none;">
                    <input type="date" id="fuelLogFromDate" class="form-control form-control-sm" />
                    <span class="small">to</span>
                    <input type="date" id="fuelLogToDate" class="form-control form-control-sm" />
                    <button class="btn btn-sm btn-outline-primary" id="applyCustomFilterLog" title="Apply Filter">
                      <i class="bi bi-funnel"></i>
                    </button>
                  </div>
                  <input type="text" id="fuelSearch" class="form-control form-control-sm" placeholder="Search..." />
                  <button class="btn btn-sm btn-outline-secondary" id="fuelLogRefreshBtn" title="Refresh">
                    <i class="bi bi-arrow-clockwise"></i>
                  </button>
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary" id="fuelLogExportCsvBtn" title="Export CSV">
                      <i class="bi bi-filetype-csv"></i> CSV
                    </button>
                    <button class="btn btn-sm btn-outline-danger" id="fuelLogExportPdfBtn" title="Export PDF">
                      <i class="bi bi-filetype-pdf"></i> PDF
                    </button>
                  </div>
                  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFuelModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Fuel
                  </button>
                </div>
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
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- Client-side inserted rows for now -->
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Fuel Out Tab -->
        <div class="tab-pane fade" id="fuel-out" role="tabpanel">
          <div class="card shadow-sm">
            <div class="card-header">
              <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <strong><i class="bi bi-truck me-1"></i> Fuel Out Records</strong>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                  <div class="d-flex align-items-center gap-2">
                    <label for="fuelOutDateFilter" class="form-label mb-0 small">Filter:</label>
                    <select id="fuelOutDateFilter" class="form-select form-select-sm" style="min-width: 120px;">
                      <option value="all">All Records</option>
                      <option value="current_month">Current Month</option>
                      <option value="current_quarter">Current Quarter</option>
                      <option value="current_year">Current Year</option>
                      <option value="last_month">Last Month</option>
                      <option value="last_quarter">Last Quarter</option>
                      <option value="last_year">Last Year</option>
                      <option value="custom">Custom Range</option>
                    </select>
                  </div>
                  <div id="customDateRange" class="d-flex align-items-center gap-2" style="display: none;">
                    <input type="date" id="fuelOutFromDate" class="form-control form-control-sm" />
                    <span class="small">to</span>
                    <input type="date" id="fuelOutToDate" class="form-control form-control-sm" />
                    <button class="btn btn-sm btn-outline-primary" id="applyCustomFilter" title="Apply Filter">
                      <i class="bi bi-funnel"></i>
                    </button>
                  </div>
                  <input type="text" id="fuelOutSearch" class="form-control form-control-sm" placeholder="Search..." />
                  <button class="btn btn-sm btn-outline-secondary" id="fuelOutRefreshBtn" title="Refresh">
                    <i class="bi bi-arrow-clockwise"></i>
                  </button>
                  <div class="btn-group" role="group">
                    <button class="btn btn-sm btn-outline-secondary" id="fuelOutExportCsvBtn" title="Export CSV">
                      <i class="bi bi-filetype-csv"></i> CSV
                    </button>
                    <button class="btn btn-sm btn-outline-danger" id="fuelOutExportPdfBtn" title="Export PDF">
                      <i class="bi bi-filetype-pdf"></i> PDF
                    </button>
                  </div>
                  <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addFuelOutModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Fuel Out
                  </button>
                </div>
              </div>
            </div>
            <div class="card-body table-responsive">
              <table class="table table-striped align-middle" id="fuelOutTable">
                <thead class="table-light">
                  <tr>
                    <th>Date</th>
                    <th>Time In</th>
                    <th>Fuel Type</th>
                    <th>Fuel No</th>
                    <th>Plate No</th>
                    <th>Request</th>
                    <th>No. of Liters</th>
                    <th>Vehicle Type</th>
                    <th>Receiver</th>
                    <th>Time Out</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Reports Tab -->
        <div class="tab-pane fade" id="fuel-report" role="tabpanel">
          <div class="card shadow-sm">
            <div class="card-header d-flex flex-wrap gap-2 align-items-end justify-content-between">
              <div class="d-flex flex-wrap gap-2 align-items-end">
                <div>
                  <label class="form-label mb-1" for="rep_from">From</label>
                  <input type="date" id="rep_from" class="form-control form-control-sm" />
                </div>
                <div>
                  <label class="form-label mb-1" for="rep_to">To</label>
                  <input type="date" id="rep_to" class="form-control form-control-sm" />
                </div>
                <div>
                  <label class="form-label mb-1" for="rep_group_by">Group By</label>
                  <select id="rep_group_by" class="form-select form-select-sm">
                    <option value="fo_request" selected>Request</option>
                    <option value="fo_plate_no">Plate No</option>
                    <option value="fo_fuel_type">Fuel Type</option>
                    <option value="fo_receiver">Receiver</option>
                    <option value="fo_vehicle_type">Vehicle Type</option>
                  </select>
                </div>
                <div>
                  <label class="form-label mb-1" for="rep_search">Search</label>
                  <input type="text" id="rep_search" class="form-control form-control-sm" placeholder="Filter results..." />
                </div>
                <button class="btn btn-sm btn-outline-secondary" id="rep_refresh"><i class="bi bi-arrow-clockwise"></i></button>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" id="rep_export_csv"><i class="bi bi-filetype-csv"></i> Export</button>
                <button class="btn btn-sm btn-outline-danger" id="rep_export_pdf"><i class="bi bi-filetype-pdf"></i> PDF</button>
              </div>
            </div>
            <div class="card-body">
              <!-- Charts Row -->
              <div class="row g-3 mb-3">
                <div class="col-12 col-lg-8">
                  <div class="card h-100">
                    <div class="card-header py-2">
                      <strong><i class="bi bi-graph-up-arrow me-1"></i> Top by Total Liters</strong>
                    </div>
                    <div class="card-body">
                      <canvas id="rep_bar_chart" height="140"></canvas>
                    </div>
                  </div>
                </div>
                <div class="col-12 col-lg-4">
                  <div class="card h-100">
                    <div class="card-header py-2">
                      <strong><i class="bi bi-pie-chart me-1"></i> Share by Group</strong>
                    </div>
                    <div class="card-body">
                      <canvas id="rep_pie_chart" height="140"></canvas>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Timeseries Row -->
              <div class="row g-3 mb-3">
                <div class="col-12">
                  <div class="card">
                    <div class="card-header py-2">
                      <strong><i class="bi bi-activity me-1"></i> Daily Liters Over Time</strong>
                    </div>
                    <div class="card-body">
                      <canvas id="rep_line_chart" height="120"></canvas>
                    </div>
                  </div>
                </div>
              </div>
              <div class="table-responsive">
              <table class="table table-striped align-middle" id="fuelReportTable">
                <thead class="table-light">
                  <tr>
                    <th data-sort="group_key" class="rep-sort">Group</th>
                    <th data-sort="total_liters" class="rep-sort text-end">Total Liters</th>
                    <th data-sort="trips" class="rep-sort text-end">Trips</th>
                    <th data-sort="unique_plates" class="rep-sort text-end">Unique Plates</th>
                    <th>Fuel Types</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Inventory Tab -->
        <div class="tab-pane fade" id="fuel-main" role="tabpanel">
          <!-- Stock Alerts Container -->
          <div id="stockAlertsContainer"></div>
          
          <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
              <strong><i class="bi bi-collection me-1"></i> Fuel Stock</strong>
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addFuelTypeModal">
                <i class="bi bi-plus-circle me-1"></i> Add Fuel Type
              </button>
            </div>
            <div class="card-body table-responsive">
              <table class="table table-striped align-middle" id="fuelStockTable">
                <thead class="table-light">
                  <tr>
                    <th>Fuel Type</th>
                    <th>Quantity (L)</th>
                    <th>Last Updated</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include 'includes/footer.php'; ?>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  
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
                <div class="input-group">
                  <select id="fuel_type" name="fuel_type" class="form-select" required>
                    <option value="" selected disabled>Loading...</option>
                  </select>
                  <button class="btn btn-outline-secondary" type="button" id="manageTypesBtn" title="Add Fuel Type" data-bs-toggle="modal" data-bs-target="#addFuelTypeModal"><i class="bi bi-gear"></i></button>
                </div>
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

    // Fuel Out helpers and handlers
    const fuelOutTbody = document.querySelector('#fuelOutTable tbody');
    const fuelOutSearch = document.getElementById('fuelOutSearch');
    const addFuelOutModalEl = document.getElementById('addFuelOutModal');
    const addFuelOutModal = addFuelOutModalEl ? new bootstrap.Modal(addFuelOutModalEl) : null;

    function setFuelOutDefaults() {
      const d = document.getElementById('fo_date');
      const ti = document.getElementById('fo_time_in');
      if (d) {
        const now = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        d.value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}`;
      }
      if (ti) {
        const now = new Date();
        const pad = (n) => String(n).padStart(2, '0');
        ti.value = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
      }
    }

    function renderFuelOutRow(r) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r.fo_date || ''}</td>
        <td>${r.fo_time_in || ''}</td>
        <td>${r.fo_fuel_type || ''}</td>
        <td>${r.fo_fuel_no || ''}</td>
        <td>${r.fo_plate_no || ''}</td>
        <td>${r.fo_request || ''}</td>
        <td>${Number(r.fo_liters || 0).toFixed(2)}</td>
        <td>${r.fo_vehicle_type || ''}</td>
        <td>${r.fo_receiver || ''}</td>
        <td>${r.fo_time_out || ''}</td>
        <td>
          <button type="button" class="btn btn-sm btn-outline-danger deleteFuelOutBtn" data-id="${r.id}" title="Delete Record">
            <i class="bi bi-trash"></i>
          </button>
        </td>
      `;
      return tr;
    }

    async function loadFuelOutRecords() {
      try {
        const res = await fetch('list_fuel_out.php', { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Failed to load fuel out');
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load');
        fuelOutTbody.innerHTML = '';
        data.records.forEach(r => fuelOutTbody.appendChild(renderFuelOutRow(r)));
        // Ensure any previous search filter is reset so all rows are visible
        const searchInput = document.getElementById('fuelOutSearch');
        if (searchInput) searchInput.value = '';
      } catch (e) {
        console.error(e);
      }
    }

    if (addFuelOutModalEl) {
      addFuelOutModalEl.addEventListener('show.bs.modal', () => {
        setFuelOutDefaults();
        // Clear any previous error alert (preserve close button and default message span)
        const alertBox = document.getElementById('fuelOutAlert');
        if (alertBox) {
          alertBox.classList.add('d-none');
          const textSpan = alertBox.querySelector('.fuel-out-alert-text');
          if (textSpan) textSpan.textContent = '';
        }
      });
    }

    function showFuelOutError(msg) {
      const alertBox = document.getElementById('fuelOutAlert');
      if (!alertBox) { window.alert(msg); return; }
      const textSpan = alertBox.querySelector('.fuel-out-alert-text');
      if (textSpan) textSpan.textContent = msg || 'Unable to save fuel out record.';
      alertBox.classList.remove('d-none');
      // Ensure alert is visible in modal viewport
      alertBox.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    document.getElementById('saveFuelOutBtn').addEventListener('click', async function() {
      const form = document.getElementById('fuelOutForm');
      if (!form.checkValidity()) { form.classList.add('was-validated'); return; }
      const btn = this;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
      try {
        const formData = new FormData(form);
        const res = await fetch('save_fuel_out.php', { method: 'POST', body: formData, credentials: 'same-origin' });
        // Try to surface server-provided error (e.g., insufficient stock)
        if (!res.ok) {
          let serverMsg = 'Failed to save';
          try {
            const errJson = await res.json();
            if (errJson && errJson.error) serverMsg = errJson.error;
          } catch (_) { /* ignore parse errors */ }
          throw new Error(serverMsg);
        }
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to save');
        fuelOutTbody.prepend(renderFuelOutRow(data.record));
        await loadFuelStock();
        form.reset();
        form.classList.remove('was-validated');
        if (addFuelOutModal) addFuelOutModal.hide();
      } catch (err) {
        // Show error message inside modal as Bootstrap alert
        showFuelOutError((err && err.message) ? err.message : 'Unable to save fuel out record.');
        console.error(err);
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-save me-1"></i> Save';
      }
    });

    if (fuelOutSearch) {
      fuelOutSearch.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        [...fuelOutTbody.querySelectorAll('tr')].forEach(tr => {
          tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
      });
    }

    // Refresh button for Fuel Out to always display what's on the table
    const fuelOutRefreshBtn = document.getElementById('fuelOutRefreshBtn');
    if (fuelOutRefreshBtn) {
      fuelOutRefreshBtn.addEventListener('click', async () => {
        await loadFuelOutRecords();
      });
    }

    // Date filter functionality for Fuel Out
    const fuelOutDateFilter = document.getElementById('fuelOutDateFilter');
    const customDateRange = document.getElementById('customDateRange');
    const fuelOutFromDate = document.getElementById('fuelOutFromDate');
    const fuelOutToDate = document.getElementById('fuelOutToDate');
    const applyCustomFilter = document.getElementById('applyCustomFilter');

    // Show/hide custom date range inputs
    if (fuelOutDateFilter) {
      fuelOutDateFilter.addEventListener('change', function() {
        if (this.value === 'custom') {
          customDateRange.style.display = 'flex';
        } else {
          customDateRange.style.display = 'none';
          // Auto-apply filter for predefined ranges
          applyDateFilter();
        }
      });
    }

    // Apply custom date filter
    if (applyCustomFilter) {
      applyCustomFilter.addEventListener('click', applyDateFilter);
    }

    // Function to get date range based on filter selection
    function getDateRange(filterType) {
      const now = new Date();
      const currentYear = now.getFullYear();
      const currentMonth = now.getMonth();
      const currentQuarter = Math.floor(currentMonth / 3);
      
      switch (filterType) {
        case 'current_month':
          return {
            from: new Date(currentYear, currentMonth, 1).toISOString().split('T')[0],
            to: new Date(currentYear, currentMonth + 1, 0).toISOString().split('T')[0]
          };
        case 'current_quarter':
          const quarterStart = currentQuarter * 3;
          return {
            from: new Date(currentYear, quarterStart, 1).toISOString().split('T')[0],
            to: new Date(currentYear, quarterStart + 3, 0).toISOString().split('T')[0]
          };
        case 'current_year':
          return {
            from: new Date(currentYear, 0, 1).toISOString().split('T')[0],
            to: new Date(currentYear, 11, 31).toISOString().split('T')[0]
          };
        case 'last_month':
          const lastMonth = currentMonth === 0 ? 11 : currentMonth - 1;
          const lastMonthYear = currentMonth === 0 ? currentYear - 1 : currentYear;
          return {
            from: new Date(lastMonthYear, lastMonth, 1).toISOString().split('T')[0],
            to: new Date(lastMonthYear, lastMonth + 1, 0).toISOString().split('T')[0]
          };
        case 'last_quarter':
          const lastQuarter = currentQuarter === 0 ? 3 : currentQuarter - 1;
          const lastQuarterYear = currentQuarter === 0 ? currentYear - 1 : currentYear;
          const lastQuarterStart = lastQuarter * 3;
          return {
            from: new Date(lastQuarterYear, lastQuarterStart, 1).toISOString().split('T')[0],
            to: new Date(lastQuarterYear, lastQuarterStart + 3, 0).toISOString().split('T')[0]
          };
        case 'last_year':
          return {
            from: new Date(currentYear - 1, 0, 1).toISOString().split('T')[0],
            to: new Date(currentYear - 1, 11, 31).toISOString().split('T')[0]
          };
        case 'custom':
          return {
            from: fuelOutFromDate.value,
            to: fuelOutToDate.value
          };
        default:
          return null;
      }
    }

    // Apply date filter to table
    function applyDateFilter() {
      const filterType = fuelOutDateFilter.value;
      const dateRange = getDateRange(filterType);
      
      if (!dateRange || filterType === 'all') {
        // Show all rows
        [...fuelOutTbody.querySelectorAll('tr')].forEach(tr => {
          tr.style.display = '';
        });
        return;
      }

      const fromDate = new Date(dateRange.from);
      const toDate = new Date(dateRange.to);

      [...fuelOutTbody.querySelectorAll('tr')].forEach(tr => {
        const dateCell = tr.querySelector('td:first-child');
        if (dateCell) {
          const rowDate = new Date(dateCell.textContent);
          const isInRange = rowDate >= fromDate && rowDate <= toDate;
          tr.style.display = isInRange ? '' : 'none';
        }
      });
    }

    // Export CSV for Fuel Out with date filtering
    const fuelOutExportBtn = document.getElementById('fuelOutExportCsvBtn');
    if (fuelOutExportBtn) {
      fuelOutExportBtn.addEventListener('click', () => {
        const filterType = fuelOutDateFilter.value;
        const dateRange = getDateRange(filterType);
        
        let exportUrl = 'export_fuel_out_csv.php';
        
        if (dateRange && filterType !== 'all') {
          const params = new URLSearchParams({
            filter_type: filterType,
            from_date: dateRange.from,
            to_date: dateRange.to
          });
          exportUrl += '?' + params.toString();
        }
        
        // Direct download of CSV (server will stream the file)
        window.location.href = exportUrl;
      });
    }

    // Export PDF for Fuel Out with date filtering
    const fuelOutExportPdfBtn = document.getElementById('fuelOutExportPdfBtn');
    if (fuelOutExportPdfBtn) {
      fuelOutExportPdfBtn.addEventListener('click', () => {
        const filterType = fuelOutDateFilter.value;
        const dateRange = getDateRange(filterType);
        
        let exportUrl = 'export_fuel_out_pdf.php';
        
        if (dateRange && filterType !== 'all') {
          const params = new URLSearchParams({
            filter_type: filterType,
            from_date: dateRange.from,
            to_date: dateRange.to
          });
          exportUrl += '?' + params.toString();
        }
        
        // Open PDF in new tab for viewing/downloading
        window.open(exportUrl, '_blank');
      });
    }

    // Auto-reload Fuel Out list whenever the Fuel Out tab becomes active
    const fuelOutTabBtn = document.getElementById('fuel-out-tab');
    if (fuelOutTabBtn) {
      fuelOutTabBtn.addEventListener('shown.bs.tab', async () => {
        await loadFuelOutRecords();
      });
    }

    // Load/refresh Reports when the Reports tab becomes active
    const fuelReportTabBtn = document.getElementById('fuel-report-tab');
    if (fuelReportTabBtn) {
      fuelReportTabBtn.addEventListener('shown.bs.tab', async () => {
        if (typeof loadFuelConsumption === 'function') {
          await loadFuelConsumption();
        }
      });
    }

    // Periodic refresh while Fuel Out tab is active
    let fuelOutInterval = null;
    function startFuelOutAutoRefresh() {
      if (fuelOutInterval) return;
      fuelOutInterval = setInterval(async () => {
        // Only refresh if Fuel Out tab is currently active
        const fuelOutPane = document.getElementById('fuel-out');
        if (fuelOutPane && fuelOutPane.classList.contains('active')) {
          await loadFuelOutRecords();
        }
      }, 10000); // every 10 seconds
    }
    function stopFuelOutAutoRefresh() {
      if (fuelOutInterval) {
        clearInterval(fuelOutInterval);
        fuelOutInterval = null;
      }
    }

    // Start auto-refresh once DOM is ready
    document.addEventListener('DOMContentLoaded', () => {
      startFuelOutAutoRefresh();
    });

    // Also refresh when page becomes visible and Fuel Out is active
    document.addEventListener('visibilitychange', async () => {
      if (document.visibilityState === 'visible') {
        const fuelOutPane = document.getElementById('fuel-out');
        if (fuelOutPane && fuelOutPane.classList.contains('active')) {
          await loadFuelOutRecords();
        }
      }
    });

    // Save new fuel type
    const saveFuelTypeBtn = document.getElementById('saveFuelTypeBtn');
    if (saveFuelTypeBtn) {
      saveFuelTypeBtn.addEventListener('click', async () => {
        const input = document.getElementById('new_fuel_type');
        const name = (input.value || '').trim();
        if (!name) { input.focus(); return; }
        saveFuelTypeBtn.disabled = true;
        saveFuelTypeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        try {
          const res = await fetch('save_fuel_type.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ name }),
            credentials: 'same-origin'
          });
          const data = await res.json();
          if (!res.ok || !data.success) throw new Error(data.error || 'Failed to save type');
          // refresh dropdown and stock table
          await Promise.all([loadFuelTypes(name), loadFuelStock()]);
          input.value = '';
          if (addFuelTypeModal) addFuelTypeModal.hide();
        } catch (err) {
          alert('Unable to save fuel type.');
          console.error(err);
        } finally {
          saveFuelTypeBtn.disabled = false;
          saveFuelTypeBtn.innerHTML = '<i class="bi bi-save me-1"></i> Save Type';
        }
      });
    }

    // Helpers
    const fuelTableBody = document.querySelector('#fuelTable tbody');
    const fuelSearch = document.getElementById('fuelSearch');
    const addFuelModalEl = document.getElementById('addFuelModal');
    const addFuelModal = new bootstrap.Modal(addFuelModalEl);
    const peso = new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' });
    const fuelTypeSelect = document.getElementById('fuel_type');
    const fuelOutTypeSelect = document.getElementById('fo_fuel_type');
    const fuelStockTbody = document.querySelector('#fuelStockTable tbody');
    const addFuelTypeModalEl = document.getElementById('addFuelTypeModal');
    // Guard against missing modal element to avoid script error
    const addFuelTypeModal = addFuelTypeModalEl ? new bootstrap.Modal(addFuelTypeModalEl) : null;

    // Function to show Bootstrap success alert
    function showSuccessAlert(message) {
      const alertContainer = document.getElementById('alertContainer');
      const alertMessage = document.getElementById('alertMessage');
      
      if (alertContainer && alertMessage) {
        alertMessage.textContent = message;
        alertContainer.style.display = 'block';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
          const alert = alertContainer.querySelector('.alert');
          if (alert) {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
            alertContainer.style.display = 'none';
          }
        }, 5000);
        
        // Scroll to top to ensure alert is visible
        window.scrollTo({ top: 0, behavior: 'smooth' });
      }
    }

    // Set default Date & Time to current local datetime (minutes precision)
    function setTodayDefaultDateTime() {
      const dt = document.getElementById('date_time');
      if (!dt) return;
      const now = new Date();
      now.setSeconds(0, 0);
      const pad = (n) => String(n).padStart(2, '0');
      const value = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
      dt.value = value;
    }

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
        <td>
          <button type="button" class="btn btn-sm btn-outline-danger deleteFuelBtn" data-id="${rec.id}">
            <i class="bi bi-trash"></i>
          </button>
        </td>
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

    async function loadFuelTypes(selected = '') {
      try {
        const res = await fetch('list_fuel_types.php', { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load types');
        // Populate Add Fuel modal select
        if (fuelTypeSelect) {
          fuelTypeSelect.innerHTML = '<option value="" disabled>Choose type...</option>';
          data.types.forEach(t => {
            const opt = document.createElement('option');
            opt.value = t.name;
            opt.textContent = t.name;
            if (t.name === selected) opt.selected = true;
            fuelTypeSelect.appendChild(opt);
          });
          if (!fuelTypeSelect.value && fuelTypeSelect.options.length > 1) fuelTypeSelect.selectedIndex = 1;
        }
        // Populate Fuel Out modal select
        if (fuelOutTypeSelect) {
          fuelOutTypeSelect.innerHTML = '<option value="" disabled>Choose type...</option>';
          data.types.forEach(t => {
            const opt2 = document.createElement('option');
            opt2.value = t.name;
            opt2.textContent = t.name;
            fuelOutTypeSelect.appendChild(opt2);
          });
          if (!fuelOutTypeSelect.value && fuelOutTypeSelect.options.length > 1) fuelOutTypeSelect.selectedIndex = 1;
        }
      } catch (e) {
        console.error(e);
        if (fuelTypeSelect) fuelTypeSelect.innerHTML = '<option value="" disabled>Error loading types</option>';
        if (fuelOutTypeSelect) fuelOutTypeSelect.innerHTML = '<option value="" disabled>Error loading types</option>';
      }
    }

    async function loadFuelStock() {
      try {
        const res = await fetch('list_fuel_stock.php', { credentials: 'same-origin' });
        const data = await res.json();
        if (!data.success) throw new Error(data.error || 'Failed to load stock');
        fuelStockTbody.innerHTML = '';
        
        let outOfStockItems = [];
        let lowStockItems = [];
        
        data.stock.forEach(s => {
          const quantity = Number(s.quantity);
          const tr = document.createElement('tr');
          
          // Determine stock status and styling
          let stockStatus = '';
          let rowClass = '';
          
          if (quantity <= 0) {
            stockStatus = '<span class="badge bg-danger ms-2">OUT OF STOCK</span>';
            rowClass = 'table-danger';
            outOfStockItems.push(s.name);
          } else if (quantity <= 50) { // Low stock threshold
            stockStatus = '<span class="badge bg-warning ms-2">LOW STOCK</span>';
            rowClass = 'table-warning';
            lowStockItems.push(s.name);
          }
          
          tr.className = rowClass;
          tr.innerHTML = `
            <td>${s.name}${stockStatus}</td>
            <td>${quantity.toFixed(2)}</td>
            <td>${s.updated_at ? new Date(s.updated_at).toLocaleString() : ''}</td>
          `;
          fuelStockTbody.appendChild(tr);
        });
        
        // Update stock alerts
        updateStockAlerts(outOfStockItems, lowStockItems);
        
      } catch (e) {
        console.error(e);
      }
    }

    // Function to update stock alerts
    function updateStockAlerts(outOfStockItems, lowStockItems) {
      const container = document.getElementById('stockAlertsContainer');
      if (!container) return;
      
      container.innerHTML = ''; // Clear existing alerts
      
      // Out of stock alert
      if (outOfStockItems.length > 0) {
        const outOfStockAlert = document.createElement('div');
        outOfStockAlert.className = 'alert alert-danger alert-dismissible fade show mb-3';
        outOfStockAlert.innerHTML = `
          <i class="bi bi-exclamation-triangle-fill me-2"></i>
          <strong>Out of Stock Alert!</strong> The following fuel types are completely out of stock: 
          <strong>${outOfStockItems.join(', ')}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        container.appendChild(outOfStockAlert);
      }
      
      // Low stock alert
      if (lowStockItems.length > 0) {
        const lowStockAlert = document.createElement('div');
        lowStockAlert.className = 'alert alert-warning alert-dismissible fade show mb-3';
        lowStockAlert.innerHTML = `
          <i class="bi bi-exclamation-circle-fill me-2"></i>
          <strong>Low Stock Warning!</strong> The following fuel types are running low (≤50L): 
          <strong>${lowStockItems.join(', ')}</strong>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        container.appendChild(lowStockAlert);
      }
      
      // All good message
      if (outOfStockItems.length === 0 && lowStockItems.length === 0) {
        const goodStockAlert = document.createElement('div');
        goodStockAlert.className = 'alert alert-success alert-dismissible fade show mb-3';
        goodStockAlert.innerHTML = `
          <i class="bi bi-check-circle-fill me-2"></i>
          <strong>All fuel types are adequately stocked!</strong> No immediate restocking required.
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        container.appendChild(goodStockAlert);
      }
    }

    // initial load
    document.addEventListener('DOMContentLoaded', async () => {
      // Ensure date/time defaults to today on first load
      setTodayDefaultDateTime();
      await Promise.all([loadFuelTypes(), loadFuelRecords(), loadFuelStock(), loadFuelOutRecords()]);
      // Initialize and load Reports (current month by default)
      if (typeof initReportDefaults === 'function') {
        initReportDefaults();
      }
      if (typeof loadFuelConsumption === 'function') {
        await loadFuelConsumption();
      }
    });

    // Whenever the Add Fuel modal is opened, ensure the date defaults to today
    if (addFuelModalEl) {
      addFuelModalEl.addEventListener('show.bs.modal', () => {
        setTodayDefaultDateTime();
      });
    }

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
        await loadFuelStock();
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

    // Delete fuel record handler (event delegation)
    document.addEventListener('click', async (e) => {
      const btn = e.target.closest('.deleteFuelBtn');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      if (!id) return;
      if (!confirm('Delete this fuel record? This will adjust stock accordingly.')) return;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
      try {
        const res = await fetch('delete_fuel_record.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ id }),
          credentials: 'same-origin'
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Failed to delete');
        // Remove row
        const row = btn.closest('tr');
        if (row) row.remove();
        await loadFuelStock();
      } catch (err) {
        alert('Unable to delete fuel record.');
        console.error(err);
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash"></i>';
      }
    });

        // Delete fuel out record handler (event delegation)
        document.addEventListener('click', async (e) => {
      const btn = e.target.closest('.deleteFuelOutBtn');
      if (!btn) return;
      const id = btn.getAttribute('data-id');
      if (!id) return;
      if (!confirm('Delete this fuel out record? This will add the fuel quantity back to stock.')) return;
      btn.disabled = true;
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
      try {
        const res = await fetch('delete_fuel_out.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ id }),
          credentials: 'same-origin'
        });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Failed to delete');
        // Remove row
        const row = btn.closest('tr');
        if (row) row.remove();
        await loadFuelStock(); // Refresh stock display
        
        // Show success message with Bootstrap alert
        showSuccessAlert('Fuel out record deleted successfully! The fuel quantity has been added back to stock.');
      } catch (err) {
        alert('Unable to delete fuel out record.');
        console.error(err);
      } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-trash"></i>';
      }
    });
    
    // Date filter functionality for Fuel Log
    const fuelLogDateFilter = document.getElementById('fuelLogDateFilter');
    const customDateRangeLog = document.getElementById('customDateRangeLog');
    const fuelLogFromDate = document.getElementById('fuelLogFromDate');
    const fuelLogToDate = document.getElementById('fuelLogToDate');
    const applyCustomFilterLog = document.getElementById('applyCustomFilterLog');

    // Show/hide custom date range inputs for fuel log
    if (fuelLogDateFilter) {
      fuelLogDateFilter.addEventListener('change', function() {
        if (this.value === 'custom') {
          customDateRangeLog.style.display = 'flex';
        } else {
          customDateRangeLog.style.display = 'none';
          // Auto-apply filter for predefined ranges
          applyDateFilterLog();
        }
      });
    }

    // Apply custom date filter for fuel log
    if (applyCustomFilterLog) {
      applyCustomFilterLog.addEventListener('click', applyDateFilterLog);
    }

    // Function to get date range based on filter selection (reuse from fuel out)
    function getDateRangeLog(filterType) {
      const now = new Date();
      const currentYear = now.getFullYear();
      const currentMonth = now.getMonth();
      const currentQuarter = Math.floor(currentMonth / 3);
      
      switch (filterType) {
        case 'current_month':
          return {
            from: new Date(currentYear, currentMonth, 1).toISOString().split('T')[0],
            to: new Date(currentYear, currentMonth + 1, 0).toISOString().split('T')[0]
          };
        case 'current_quarter':
          const quarterStart = currentQuarter * 3;
          return {
            from: new Date(currentYear, quarterStart, 1).toISOString().split('T')[0],
            to: new Date(currentYear, quarterStart + 3, 0).toISOString().split('T')[0]
          };
        case 'current_year':
          return {
            from: new Date(currentYear, 0, 1).toISOString().split('T')[0],
            to: new Date(currentYear, 11, 31).toISOString().split('T')[0]
          };
        case 'last_month':
          const lastMonth = currentMonth === 0 ? 11 : currentMonth - 1;
          const lastMonthYear = currentMonth === 0 ? currentYear - 1 : currentYear;
          return {
            from: new Date(lastMonthYear, lastMonth, 1).toISOString().split('T')[0],
            to: new Date(lastMonthYear, lastMonth + 1, 0).toISOString().split('T')[0]
          };
        case 'last_quarter':
          const lastQuarter = currentQuarter === 0 ? 3 : currentQuarter - 1;
          const lastQuarterYear = currentQuarter === 0 ? currentYear - 1 : currentYear;
          const lastQuarterStart = lastQuarter * 3;
          return {
            from: new Date(lastQuarterYear, lastQuarterStart, 1).toISOString().split('T')[0],
            to: new Date(lastQuarterYear, lastQuarterStart + 3, 0).toISOString().split('T')[0]
          };
        case 'last_year':
          return {
            from: new Date(currentYear - 1, 0, 1).toISOString().split('T')[0],
            to: new Date(currentYear - 1, 11, 31).toISOString().split('T')[0]
          };
        case 'custom':
          return {
            from: fuelLogFromDate.value,
            to: fuelLogToDate.value
          };
        default:
          return null;
      }
    }

    // Apply date filter to fuel log table
    function applyDateFilterLog() {
      const filterType = fuelLogDateFilter.value;
      const dateRange = getDateRangeLog(filterType);
      
      if (!dateRange || filterType === 'all') {
        // Show all rows
        [...fuelTableBody.querySelectorAll('tr')].forEach(tr => {
          tr.style.display = '';
        });
        return;
      }

      const fromDate = new Date(dateRange.from);
      const toDate = new Date(dateRange.to);

      [...fuelTableBody.querySelectorAll('tr')].forEach(tr => {
        const dateCell = tr.querySelector('td:first-child');
        if (dateCell) {
          // Parse the datetime string (format: YYYY-MM-DD HH:MM:SS)
          const dateTimeText = dateCell.textContent.trim();
          const datePart = dateTimeText.split(' ')[0]; // Get just the date part
          const rowDate = new Date(datePart);
          const isInRange = rowDate >= fromDate && rowDate <= toDate;
          tr.style.display = isInRange ? '' : 'none';
        }
      });
    }

    // Basic search filter
    fuelSearch.addEventListener('input', function() {
      const q = this.value.toLowerCase();
      [...fuelTableBody.querySelectorAll('tr')].forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });

    // Refresh button for Fuel Log
    const fuelLogRefreshBtn = document.getElementById('fuelLogRefreshBtn');
    if (fuelLogRefreshBtn) {
      fuelLogRefreshBtn.addEventListener('click', async () => {
        await loadFuelRecords();
      });
    }

    // Export CSV for Fuel Log with date filtering
    const fuelLogExportCsvBtn = document.getElementById('fuelLogExportCsvBtn');
    if (fuelLogExportCsvBtn) {
      fuelLogExportCsvBtn.addEventListener('click', () => {
        const filterType = fuelLogDateFilter.value;
        const dateRange = getDateRangeLog(filterType);
        
        let exportUrl = 'export_fuel_log_csv.php';
        
        if (dateRange && filterType !== 'all') {
          const params = new URLSearchParams({
            filter_type: filterType,
            from_date: dateRange.from,
            to_date: dateRange.to
          });
          exportUrl += '?' + params.toString();
        }
        
        // Direct download of CSV (server will stream the file)
        window.location.href = exportUrl;
      });
    }

    // Export PDF for Fuel Log with date filtering
    const fuelLogExportPdfBtn = document.getElementById('fuelLogExportPdfBtn');
    if (fuelLogExportPdfBtn) {
      fuelLogExportPdfBtn.addEventListener('click', () => {
        const filterType = fuelLogDateFilter.value;
        const dateRange = getDateRangeLog(filterType);
        
        let exportUrl = 'export_fuel_log_pdf.php';
        
        if (dateRange && filterType !== 'all') {
          const params = new URLSearchParams({
            filter_type: filterType,
            from_date: dateRange.from,
            to_date: dateRange.to
          });
          exportUrl += '?' + params.toString();
        }
        
        // Open PDF in new tab for viewing/downloading
        window.open(exportUrl, '_blank');
      });
    }

    // =====================
    // Reports JS
    // =====================
    const repFrom = document.getElementById('rep_from');
    const repTo = document.getElementById('rep_to');
    const repGroupBy = document.getElementById('rep_group_by');
    const repSearch = document.getElementById('rep_search');
    const repRefreshBtn = document.getElementById('rep_refresh');
    const repExportBtn = document.getElementById('rep_export_csv');
    const repExportPdfBtn = document.getElementById('rep_export_pdf');
    const repTbody = document.querySelector('#fuelReportTable tbody');

    let repData = [];
    let repSort = { key: 'total_liters', dir: 'desc' };

    function initReportDefaults() {
      const now = new Date();
      const first = new Date(now.getFullYear(), now.getMonth(), 1);
      const last = new Date(now.getFullYear(), now.getMonth() + 1, 0);
      const pad = (n) => String(n).padStart(2, '0');
      if (repFrom) repFrom.value = `${first.getFullYear()}-${pad(first.getMonth()+1)}-${pad(first.getDate())}`;
      if (repTo) repTo.value = `${last.getFullYear()}-${pad(last.getMonth()+1)}-${pad(last.getDate())}`;
    }

    function renderReportRow(r) {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${r.group_key || ''}</td>
        <td class="text-end">${Number(r.total_liters || 0).toFixed(2)}</td>
        <td class="text-end">${r.trips || 0}</td>
        <td class="text-end">${r.unique_plates || 0}</td>
        <td>${r.fuel_types || ''}</td>
      `;
      return tr;
    }

    function applyReportSearch() {
      const q = (repSearch?.value || '').toLowerCase();
      [...repTbody.querySelectorAll('tr')].forEach(tr => {
        tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    }

    function sortReportData() {
      const { key, dir } = repSort;
      repData.sort((a,b) => {
        const av = a[key];
        const bv = b[key];
        if (key === 'group_key' || key === 'fuel_types') {
          const cmp = String(av||'').localeCompare(String(bv||''));
          return dir === 'asc' ? cmp : -cmp;
        }
        const na = Number(av || 0);
        const nb = Number(bv || 0);
        return dir === 'asc' ? (na - nb) : (nb - na);
      });
    }

    // Charts state
    let repBarChart = null;
    let repPieChart = null;
    let repLineChart = null;

    function getPalette(n) {
      const base = [
        '#4e79a7','#f28e2b','#e15759','#76b7b2','#59a14f','#edc949','#af7aa1','#ff9da7','#9c755f','#bab0ab'
      ];
      const colors = [];
      for (let i = 0; i < n; i++) colors.push(base[i % base.length]);
      return colors;
    }

    function updateBarChart(data) {
      const ctx = document.getElementById('rep_bar_chart');
      if (!ctx) return;
      const labels = data.map(d => d.group_key || '(blank)');
      const values = data.map(d => Number(d.total_liters || 0));
      const colors = getPalette(data.length || 1);
      if (repBarChart) { repBarChart.destroy(); }
      repBarChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels,
          datasets: [{
            label: 'Total Liters',
            data: values,
            backgroundColor: colors,
            borderColor: colors,
            borderWidth: 1,
            borderRadius: 6,
            maxBarThickness: 28,
            categoryPercentage: 0.55,
            barPercentage: 0.55,
            borderSkipped: false
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true,
              title: {
                display: true,
                text: 'Total Liters ℹ',
                color: '#6c757d',
                font: { size: 12, weight: '600' }
              },
              ticks: {
                callback: (value) => Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 })
              },
              grid: { color: 'rgba(108,117,125,0.12)' }
            },
            x: {
              grid: { display: false },
              ticks: {
                maxRotation: 45,
                minRotation: 0
              }
            }
          },
          plugins: {
            legend: {
              display: true,
              position: 'top',
              labels: {
                boxWidth: 12,
                usePointStyle: true,
                font: { size: 11 }
              }
            }
          }
        }
      });
    }

    function updatePieChart(data) {
      const ctx = document.getElementById('rep_pie_chart');
      if (!ctx) return;
      const top = data.slice(0, 10);
      const labels = top.map(d => d.group_key || '(blank)');
      const values = top.map(d => Number(d.total_liters || 0));
      const colors = getPalette(top.length);
      if (repPieChart) { repPieChart.destroy(); }
      repPieChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
          labels,
          datasets: [{
            data: values,
            backgroundColor: colors,
            borderColor: '#ffffff',
            borderWidth: 1,
            hoverOffset: 6
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          cutout: '58%',
          plugins: {
            legend: {
              display: true,
              position: 'bottom',
              labels: {
                boxWidth: 12,
                usePointStyle: true,
                font: { size: 11 }
              }
            },
            title: {
              display: true,
              text: 'Share by Group ℹ',
              color: '#6c757d',
              padding: { top: 0, bottom: 6 },
              font: { size: 13, weight: '600' }
            },
            tooltip: {
              callbacks: {
                label: (context) => {
                  const label = context.label || '';
                  const value = Number(context.parsed || 0);
                  const total = context.chart._metasets[0]?.total || 0;
                  const percentage = total ? ((value / total) * 100).toFixed(1) : '0.0';
                  return `${label}: ${value.toLocaleString(undefined, { maximumFractionDigits: 2 })} L (${percentage}%)`;
                }
              }
            }
          }
        }
      });
    }

    async function loadFuelTimeseries() {
      try {
        const params = new URLSearchParams();
        if (repFrom?.value) params.append('from', repFrom.value);
        if (repTo?.value) params.append('to', repTo.value);
        const res = await fetch('list_fuel_timeseries.php?' + params.toString(), { credentials: 'same-origin' });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Failed to load timeseries');
        const labels = (data.records || []).map(r => r.date);
        const values = (data.records || []).map(r => Number(r.total_liters || 0));
        const ctx = document.getElementById('rep_line_chart');
        if (!ctx) return;
        if (repLineChart) { repLineChart.destroy(); }
        repLineChart = new Chart(ctx, {
          type: 'line',
          data: {
            labels,
            datasets: [{
              label: 'Daily Total Liters',
              data: values,
              fill: {
                target: 'origin',
                above: 'rgba(78, 121, 167, 0.12)'
              },
              borderColor: '#4e79a7',
              backgroundColor: 'rgba(78, 121, 167, 0.12)',
              borderWidth: 2,
              tension: 0.35,
              pointRadius: 3,
              pointHoverRadius: 5,
              pointBackgroundColor: '#ffffff',
              pointBorderColor: '#4e79a7',
              pointBorderWidth: 2
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Total Liters ℹ',
                  color: '#6c757d',
                  font: { size: 12, weight: '600' }
                },
                ticks: {
                  callback: (value) => Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 })
                },
                grid: { color: 'rgba(108,117,125,0.12)' }
              },
              x: {
                grid: { color: 'rgba(108,117,125,0.08)' },
                ticks: {
                  maxRotation: 45,
                  minRotation: 0
                }
              }
            },
            plugins: {
              legend: {
                display: true,
                position: 'top',
                labels: {
                  boxWidth: 12,
                  usePointStyle: true,
                  font: { size: 11 }
                }
              }
            }
          }
        });
      } catch (e) {
        console.error(e);
      }
    }

    async function loadFuelConsumption() {
      try {
        const params = new URLSearchParams();
        if (repFrom?.value) params.append('from', repFrom.value);
        if (repTo?.value) params.append('to', repTo.value);
        if (repGroupBy?.value) params.append('group_by', repGroupBy.value);
        const res = await fetch('list_fuel_consumption.php?' + params.toString(), { credentials: 'same-origin' });
        const data = await res.json();
        if (!res.ok || !data.success) throw new Error(data.error || 'Failed to load report');
        repData = data.records || [];
        sortReportData();
        repTbody.innerHTML = '';
        repData.forEach(r => repTbody.appendChild(renderReportRow(r)));
        applyReportSearch();
        // Update charts
        updateBarChart(repData.slice(0, 12));
        updatePieChart(repData);
        await loadFuelTimeseries();
      } catch (e) {
        console.error(e);
        repTbody.innerHTML = '<tr><td colspan="5" class="text-danger">Unable to load report.</td></tr>';
      }
    }

    // Header sort handlers
    document.querySelectorAll('#fuelReportTable thead th.rep-sort').forEach(th => {
      th.style.cursor = 'pointer';
      th.addEventListener('click', () => {
        const key = th.getAttribute('data-sort');
        if (repSort.key === key) {
          repSort.dir = repSort.dir === 'asc' ? 'desc' : 'asc';
        } else {
          repSort.key = key;
          repSort.dir = key === 'group_key' ? 'asc' : 'desc';
        }
        sortReportData();
        repTbody.innerHTML = '';
        repData.forEach(r => repTbody.appendChild(renderReportRow(r)));
        applyReportSearch();
      });
    });

    // Filter handlers
    [repFrom, repTo, repGroupBy].forEach(el => {
      if (!el) return;
      el.addEventListener('change', async () => {
        await loadFuelConsumption();
      });
    });
    if (repSearch) repSearch.addEventListener('input', applyReportSearch);
    if (repRefreshBtn) repRefreshBtn.addEventListener('click', loadFuelConsumption);

    // Export current report table to CSV (client side)
    if (repExportBtn) {
      repExportBtn.addEventListener('click', () => {
        const rows = [['Group','Total Liters','Trips','Unique Plates','Fuel Types']];
        // Use filtered DOM rows to reflect current search filter
        const visibleRows = [...repTbody.querySelectorAll('tr')].filter(tr => tr.style.display !== 'none');
        visibleRows.forEach(tr => {
          const cols = [...tr.children].map(td => '"' + td.textContent.replace(/"/g,'""') + '"');
          rows.push(cols);
        });
        const csv = rows.map(r => r.join(',')).join('\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'fuel_consumption_report.csv';
        a.click();
        URL.revokeObjectURL(url);
      });
    }

    // Export current report (server-generated PDF)
    if (repExportPdfBtn) {
      repExportPdfBtn.addEventListener('click', () => {
        const params = new URLSearchParams();
        if (repFrom?.value) params.append('from', repFrom.value);
        if (repTo?.value) params.append('to', repTo.value);
        if (repGroupBy?.value) params.append('group_by', repGroupBy.value);
        const url = 'export_fuel_consumption_pdf.php' + (params.toString() ? ('?' + params.toString()) : '');
        window.open(url, '_blank');
      });
    }
  </script>
</body>
</html>
