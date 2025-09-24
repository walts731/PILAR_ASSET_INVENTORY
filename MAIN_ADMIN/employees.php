<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Handle delete selected employees (must run before any HTML output)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_selected'])) {
  $ids = isset($_POST['selected_employees']) && is_array($_POST['selected_employees']) ? $_POST['selected_employees'] : [];
  // sanitize ids to integers
  $ids = array_values(array_filter(array_map('intval', $ids), function($v){ return $v > 0; }));

  if (!empty($ids)) {
    // Build placeholders
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    // Determine which employees have MR assets (cannot delete)
    $sql = $conn->prepare("SELECT e.employee_id, e.name, EXISTS(SELECT 1 FROM mr_details m WHERE m.person_accountable = e.name) AS has_mr FROM employees e WHERE e.employee_id IN ($placeholders)");
    $sql->bind_param($types, ...$ids);
    $sql->execute();
    $res = $sql->get_result();
    $deletable = [];
    $blocked = [];
    while ($row = $res->fetch_assoc()) {
      if ((int)$row['has_mr'] === 0) $deletable[] = (int)$row['employee_id'];
      else $blocked[] = (int)$row['employee_id'];
    }
    $sql->close();

    $deletedCount = 0;
    if (!empty($deletable)) {
      $ph = implode(',', array_fill(0, count($deletable), '?'));
      $t = str_repeat('i', count($deletable));
      $del = $conn->prepare("DELETE FROM employees WHERE employee_id IN ($ph)");
      $del->bind_param($t, ...$deletable);
      $del->execute();
      $deletedCount = $del->affected_rows;
      $del->close();
    }

    $blockedCount = count($blocked);
    header("Location: employees.php?deleted=$deletedCount&blocked=$blockedCount");
    exit();
  } else {
    header("Location: employees.php?deleted=0&blocked=0");
    exit();
  }
}

// Set office_id if not set
if (!isset($_SESSION['office_id'])) {
  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT office_id FROM users WHERE user_id = ?");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $stmt->bind_result($office_id);
  if ($stmt->fetch()) {
    $_SESSION['office_id'] = $office_id;
  }
  $stmt->close();
}

// Fetch full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
if ($stmt->fetch()) {
  $user_name = $fullname;
}
$stmt->close();

// Get system logo
$systemSql = "SELECT logo FROM system LIMIT 1";
$systemResult = $conn->query($systemSql);
$system = $systemResult->fetch_assoc();
$systemLogo = !empty($system['logo']) ? '../img/' . $system['logo'] : '';

// Export all employees to CSV (must run before any HTML output)
if (isset($_GET['export_employees']) && $_GET['export_employees'] === '1') {
  // Build filename with timestamp
  $timestamp = date('Ymd_His');
  $filename = "employees_{$timestamp}.csv";

  // Query: match the view table to include office and clearance status
  $exportSql = $conn->query("
    SELECT e.employee_no, e.name, o.office_name,
           e.status,
           CASE 
             WHEN EXISTS (SELECT 1 FROM mr_details m WHERE m.person_accountable = e.name) 
             THEN 'uncleared'
             ELSE 'cleared'
           END AS clearance_status,
           e.date_added
    FROM employees e
    LEFT JOIN offices o ON e.office_id = o.id
    ORDER BY e.date_added DESC
  ");

  // Send headers
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename=' . $filename);
  header('Pragma: no-cache');
  header('Expires: 0');

  $output = fopen('php://output', 'w');
  // CSV header row
  fputcsv($output, ['Employee No', 'Name', 'Office', 'Employment Status', 'Clearance Status', 'Date Added']);

  while ($row = $exportSql->fetch_assoc()) {
    $dateFormatted = '';
    if (!empty($row['date_added'])) {
      $ts = strtotime($row['date_added']);
      $dateFormatted = $ts ? date('Y-m-d', $ts) : $row['date_added'];
    }
    fputcsv($output, [
      $row['employee_no'],
      $row['name'],
      $row['office_name'] ?? 'N/A',
      $row['status'],
      ucfirst($row['clearance_status']),
      $dateFormatted,
    ]);
  }

  fclose($output);
  exit();
}

// Fetch employees
$employees = [];
$result = $conn->query("
  SELECT e.employee_id, e.employee_no, e.name, e.status, e.date_added, e.image,
         e.office_id, o.office_name,
         CASE 
           WHEN EXISTS (SELECT 1 FROM mr_details m WHERE m.person_accountable = e.name) 
           THEN 'uncleared'
           ELSE 'cleared'
         END AS clearance_status
  FROM employees e
  LEFT JOIN offices o ON e.office_id = o.id
  ORDER BY e.date_added DESC
");

while ($row = $result->fetch_assoc()) {
  $employees[] = $row;
}

// Fetch offices for filter dropdown
$officesRes = $conn->query("SELECT id, office_name FROM offices ORDER BY office_name ASC");


?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Employees</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>
  <?php include 'includes/sidebar.php' ?>
  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container mt-4">
      <div id="pageAlerts" class="mb-3"></div>
      <div class="card shadow">
        <div class="card-header bg-light">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <h5 class="mb-0 d-flex align-items-center gap-2">
              <i class="bi bi-people-fill"></i>
              Employees
            </h5>
            <div class="d-flex flex-wrap gap-2 align-items-center">
              <div class="d-flex align-items-center gap-2">
                <label for="officeFilter" class="mb-0 small text-muted">Office:</label>
                <select id="officeFilter" class="form-select form-select-sm w-auto">
                  <option value="">All Offices</option>
                  <?php if ($officesRes && $officesRes->num_rows > 0): ?>
                    <?php while ($off = $officesRes->fetch_assoc()): ?>
                      <option value="<?= htmlspecialchars($off['office_name']) ?>"><?= htmlspecialchars($off['office_name']) ?></option>
                    <?php endwhile; ?>
                  <?php endif; ?>
                </select>
              </div>
              <div class="btn-group" role="group" aria-label="Employee Actions">
                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#addEmployeeModal" title="Add Employee">
                  <i class="bi bi-plus-circle"></i> Add
                </button>
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importEmployeeModal" title="Import CSV">
                  <i class="bi bi-file-earmark-arrow-up"></i> Import
                </button>
                <a class="btn btn-sm btn-outline-success" href="employees.php?export_employees=1" title="Export CSV">
                  <i class="bi bi-download"></i> Export
                </a>
              </div>
              <div class="btn-group" role="group" aria-label="Report & Delete">
                <button id="generateMrReportBtn" class="btn btn-sm btn-primary" title="Generate MR Report">
                  <i class="bi bi-filetype-pdf"></i> MR Report
                </button>
                <button id="deleteEmployeesBtn" class="btn btn-sm btn-danger" title="Delete Selected" disabled>
                  <i class="bi bi-trash"></i> Delete Selected
                </button>
              </div>
            </div>
          </div>
        </div>

        <div class="card-body">
          <form id="employeeReportForm" method="POST" action="generate_employee_mr_report.php" target="_blank">
            <table id="employeeTable" class="table">
              <thead class="table-light">
                <tr>
                  <th style="width:34px;">
                    <input type="checkbox" id="selectAllEmployees" title="Select All" />
                  </th>
                  <th>Employee No</th>
                  <th>Name</th>
                  <th>Office</th>
                  <th>Employment Status</th>
                  <th>Clearance Status</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($employees as $emp): ?>
                  <tr>
                    <td>
                      <input type="checkbox" class="emp-checkbox" name="selected_employees[]" value="<?= (int)$emp['employee_id'] ?>" data-clearance="<?= htmlspecialchars($emp['clearance_status']) ?>" />
                    </td>
                    <td><?= htmlspecialchars($emp['employee_no']) ?></td>
                    <td><?= htmlspecialchars($emp['name']) ?></td>
                    <td><?= htmlspecialchars($emp['office_name'] ?? 'N/A') ?></td>
                    <td>
                      <span class="badge 
                        <?= $emp['status'] == 'permanent' ? 'bg-success' : ($emp['status'] == 'contractual' ? 'bg-warning text-dark' : ($emp['status'] == 'resigned' ? 'bg-secondary' : 'bg-info')) ?>">
                        <?= htmlspecialchars(ucfirst($emp['status'])) ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge <?= $emp['clearance_status'] == 'cleared' ? 'bg-success' : 'bg-danger' ?>">
                        <?= ucfirst($emp['clearance_status']) ?>
                      </span>
                    </td>
                    <td>
                      <button type="button" class="btn btn-sm btn-outline-primary view-assets"
                        data-id="<?= $emp['employee_id'] ?>"
                        data-name="<?= htmlspecialchars($emp['name']) ?>"
                        data-no="<?= htmlspecialchars($emp['employee_no']) ?>"
                        data-office="<?= htmlspecialchars($emp['office_name'] ?? 'N/A') ?>"
                        data-status="<?= htmlspecialchars($emp['status']) ?>"
                        data-clearance="<?= htmlspecialchars($emp['clearance_status']) ?>"
                        data-image="<?= htmlspecialchars($emp['image']) ?>"
                        data-joined="<?= htmlspecialchars(date('F j, Y', strtotime($emp['date_added']))) ?>">
                        <i class="bi bi-eye"></i> View
                      </button>

                      <button type="button" class="btn btn-sm btn-outline-info edit-employee "
                        data-id="<?= $emp['employee_id'] ?>"
                        data-no="<?= htmlspecialchars($emp['employee_no']) ?>"
                        data-name="<?= htmlspecialchars($emp['name']) ?>"
                        data-office="<?= $emp['office_id'] ?>"
                        data-status="<?= $emp['status'] ?>"
                        data-image="<?= htmlspecialchars($emp['image']) ?>">
                        <i class="bi bi-pencil"></i> Edit
                      </button>
                    </td>

                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Employee Assets Modal -->
  <?php include 'modals/employee_asset_modal.php'; ?>

  <!-- Asset Details Modal -->
  <div class="modal fade" id="assetDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content shadow">
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <i class="bi bi-box-seam"></i>
            Asset Details
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-4">
          <!-- Header: Logo, Gov Label, Inventory Tag, QR -->
          <div class="border rounded p-3 mb-3 bg-light">
            <div class="row align-items-center g-3">
              <div class="col-auto">
                <img id="municipalLogoImg" src="<?= htmlspecialchars($systemLogo) ?>" alt="Municipal Logo" style="height: 64px;">
              </div>
              <div class="col text-center">
                <div class="text-uppercase fw-bold">Government Property</div>
                <div class="small text-muted">Inventory Tag: <span id="viewInventoryTag"></span></div>
              </div>
              <div class="col-auto">
                <img id="viewQrCode" src="" alt="QR Code" style="height: 64px;">
              </div>
            </div>
          </div>

          <!-- Description -->
          <div class="mb-3">
            <label class="form-label fw-semibold mb-1">Description</label>
            <div class="p-2 border rounded bg-white"><span id="viewDescription"></span></div>
          </div>

          <!-- Summary Row: Image + Key Facts -->
          <div class="row g-3">
            <div class="col-md-5">
              <div class="text-center">
                <div class="fw-semibold mb-2">Asset Image</div>
                <img id="viewAssetImage" src="" alt="Asset Image" class="img-fluid border rounded" style="max-height: 220px; object-fit: contain; background:#f8f9fa;">
              </div>
            </div>
            <div class="col-md-7">
              <div class="row g-2">
                <div class="col-sm-6"><div class="small text-muted">Office</div><div class="fw-semibold" id="viewOfficeName"></div></div>
                <div class="col-sm-6"><div class="small text-muted">Category</div><div class="fw-semibold" id="viewCategoryName"></div></div>
                <div class="col-sm-6"><div class="small text-muted">Type</div><div class="fw-semibold" id="viewType"></div></div>
                <div class="col-sm-6"><div class="small text-muted">Status</div><div class="fw-semibold" id="viewStatus"></div></div>
                <div class="col-sm-6"><div class="small text-muted">Quantity</div><div class="fw-semibold" id="viewQuantity"></div></div>
                <div class="col-sm-6"><div class="small text-muted">Unit</div><div class="fw-semibold" id="viewUnit"></div></div>
                <div class="col-sm-6"><div class="small text-muted">Serial No</div><div class="fw-semibold" id="viewSerialNo"></div></div>
                <div class="col-sm-6"><div class="small text-muted">Property No</div><div class="fw-semibold" id="viewPropertyNo"></div></div>
                <div class="col-sm-6"><div class="small text-muted">Code</div><div class="fw-semibold" id="viewCode"></div></div>
                <div class="col-sm-6"><div class="small text-muted">Brand</div><div class="fw-semibold" id="viewBrand"></div></div>
              </div>
            </div>
          </div>

          <!-- Dates and Values -->
          <div class="row g-3 mt-2">
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="small text-muted">Acquisition Date</div>
                <div class="fw-semibold" id="viewAcquisitionDate"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="small text-muted">Last Updated</div>
                <div class="fw-semibold" id="viewLastUpdated"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="small text-muted">Unit Cost</div>
                <div class="fw-bold text-success">₱ <span id="viewValue"></span></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <div class="small text-muted">Total Value</div>
                <div class="fw-bold text-primary">₱ <span id="viewTotalValue"></span></div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle text-danger"></i>
            Confirm Deletion
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="mb-2" id="deleteConfirmMessage">Are you sure you want to delete the selected employee(s)?</p>
          <p class="text-muted small mb-0">Selected: <strong id="deleteConfirmCount">0</strong></p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
            <i class="bi bi-trash"></i> Confirm Delete
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
  <script>
    const systemLogo = "<?= $systemLogo ?>";

    $(document).ready(function() {
      const table = $('#employeeTable').DataTable();

      // Office filter dropdown -> filter Office column (index 3)
      const $officeFilter = $('#officeFilter');
      if ($officeFilter.length) {
        $officeFilter.on('change', function() {
          const val = this.value;
          if (!val) {
            // All Offices
            table.column(3).search('').draw();
          } else {
            // Exact match search using regex
            const pattern = '^' + $.fn.dataTable.util.escapeRegex(val) + '$';
            table.column(3).search(pattern, true, false).draw();
          }
        });
      }

      // Deletion feedback from query params (render Bootstrap alert)
      (function(){
        const params = new URLSearchParams(window.location.search);
        const deleted = params.get('deleted');
        const blocked = params.get('blocked');
        if (deleted !== null || blocked !== null) {
          const d = parseInt(deleted || '0', 10);
          const b = parseInt(blocked || '0', 10);
          let html = '';
          if (d > 0) {
            html += `<div class="alert alert-success alert-dismissible fade show" role="alert">
                       <i class=\"bi bi-check-circle\"></i> ${d} employee(s) deleted successfully.
                       <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                     </div>`;
          }
          if (b > 0) {
            html += `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                       <i class=\"bi bi-exclamation-triangle\"></i> ${b} employee(s) could not be deleted because they have MR assets.
                       <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                     </div>`;
          }
          if (!html) {
            html = `<div class="alert alert-info alert-dismissible fade show" role="alert">
                      <i class=\"bi bi-info-circle\"></i> No employees were selected for deletion.
                      <button type=\"button\" class=\"btn-close\" data-bs-dismiss=\"alert\" aria-label=\"Close\"></button>
                    </div>`;
          }
          $('#pageAlerts').html(html);
          // Clean the URL so alert won't repeat on refresh
          const url = new URL(window.location.href);
          url.searchParams.delete('deleted');
          url.searchParams.delete('blocked');
          window.history.replaceState({}, document.title, url.toString());
        }
      })();

      // Select All handling
      $('#selectAllEmployees').on('change', function() {
        const checked = $(this).is(':checked');
        $('.emp-checkbox:not(:disabled)').prop('checked', checked);
      });

      // Keep Select All in sync
      $(document).on('change', '.emp-checkbox', function() {
        const total = $('.emp-checkbox:not(:disabled)').length;
        const selected = $('.emp-checkbox:checked').length;
        $('#selectAllEmployees').prop('checked', selected > 0 && selected === total);
      });

      // Handle Generate MR Report
      $('#generateMrReportBtn').on('click', function(e){
        e.preventDefault();
        const hasSelection = $('.emp-checkbox:checked').length > 0;
        if (!hasSelection) {
          // Show info in the delete modal style but as info only
          $('#deleteConfirmCount').text('0');
          $('#deleteConfirmMessage').text('Please select at least one employee to generate MR report.');
          $('#confirmDeleteBtn').prop('disabled', true).hide();
          $('#deleteConfirmModal').modal('show');
          return;
        }
        $('#employeeReportForm')[0].submit();
      });

      // Enable/disable Delete button based on selection
      function refreshDeleteBtnState() {
        const selected = $('.emp-checkbox:checked').length;
        $('#deleteEmployeesBtn').prop('disabled', selected === 0);
      }
      refreshDeleteBtnState();
      $(document).on('change', '.emp-checkbox', refreshDeleteBtnState);

      // Handle Delete Selected with confirmation modal
      let pendingDeleteForm = null;
      $('#deleteEmployeesBtn').on('click', function(e) {
        e.preventDefault();
        const checked = $('.emp-checkbox:checked');
        const count = checked.length;
        // Determine how many are blocked by MR assets
        let blockedCount = 0;
        checked.each(function(){ if ($(this).data('clearance') !== 'cleared') blockedCount++; });
        const deletableCount = count - blockedCount;

        $('#deleteConfirmCount').text(count);
        if (blockedCount > 0) {
          $('#deleteConfirmMessage').html(`You selected <strong>${count}</strong> employee(s). <strong>${deletableCount}</strong> will be deleted. <strong>${blockedCount}</strong> cannot be deleted because they have MR assets.`);
        } else {
          $('#deleteConfirmMessage').text('Are you sure you want to delete the selected employee(s)? This action cannot be undone.');
        }
        $('#confirmDeleteBtn').prop('disabled', false).show();
        $('#deleteConfirmModal').modal('show');

        // Prepare form but submit only after confirm
        const form = $('<form>', { method: 'POST', action: 'employees.php' });
        form.append($('<input>', { type: 'hidden', name: 'delete_selected', value: '1' }));
        checked.each(function() {
          form.append($('<input>', { type: 'hidden', name: 'selected_employees[]', value: $(this).val() }));
        });
        pendingDeleteForm = form;
      });

      $('#confirmDeleteBtn').on('click', function() {
        if (pendingDeleteForm) {
          $('body').append(pendingDeleteForm);
          pendingDeleteForm.trigger('submit');
          pendingDeleteForm = null;
          $('#deleteConfirmModal').modal('hide');
        }
      });

      // Load assets for employee (delegated binding for DataTables pagination)
      $(document).on('click', '.view-assets', function() {
        let empId = $(this).data('id');
        let empName = $(this).data('name');
        let empNo = $(this).data('no');
        let empOffice = $(this).data('office');
        let empStatus = $(this).data('status');
        let empClearance = $(this).data('clearance');
        let empImage = $(this).data('image');

        // Populate employee info section
        $('#empInfoName').text(empName || '—');
        $('#empInfoNo').text(empNo || '—');
        $('#empInfoOffice').text(empOffice || '—');
        // Status badge styling
        const statusBadge = $('#empInfoStatusBadge');
        statusBadge.removeClass('bg-success bg-warning text-dark bg-secondary bg-info');
        if (empStatus === 'permanent') statusBadge.addClass('bg-success');
        else if (empStatus === 'contractual') statusBadge.addClass('bg-warning text-dark');
        else if (empStatus === 'resigned') statusBadge.addClass('bg-secondary');
        else statusBadge.addClass('bg-info');
        statusBadge.text(empStatus ? empStatus.charAt(0).toUpperCase() + empStatus.slice(1) : '—');

        // Clearance badge styling
        const clearanceBadge = $('#empInfoClearanceBadge');
        clearanceBadge.removeClass('bg-success bg-danger');
        if (empClearance === 'cleared') clearanceBadge.addClass('bg-success');
        else clearanceBadge.addClass('bg-danger');
        clearanceBadge.text(empClearance ? empClearance.charAt(0).toUpperCase() + empClearance.slice(1) : '—');

        // Employee image (fallback to profile icon if empty)
        if (empImage) {
          $('#empInfoImage').attr('src', '../img/' + empImage).show();
        } else {
          $('#empInfoImage').attr('src', 'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/icons/person-circle.svg');
        }

        // Date Joined
        const empJoined = $(this).data('joined');
        $('#empInfoDateJoined').text(empJoined || '—');

        $('#assetsTableBody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
        // Store the current employee id on the modal for later use (asset detail fetching)
        $('#assetsModal').data('employee-id', empId).modal('show');

        $.ajax({
          url: 'fetch_employee_assets.php',
          method: 'GET',
          data: {
            employee_id: empId
          },
          success: function(response) {
            $('#assetsTableBody').html(response);
          },
          error: function() {
            $('#assetsTableBody').html('<tr><td colspan="6" class="text-danger text-center">Failed to load assets.</td></tr>');
          }
        });
      });

      // Load asset details for the selected employee's asset
      $(document).on("click", ".view-asset-details", function() {
        const assetId = $(this).data("id");
        const employeeId = $('#assetsModal').data('employee-id') || '';
        $('#assetDetailsModal').modal('show');

        // Fetch details ensuring the asset belongs to the selected employee
        fetch(`get_asset_details_employee.php?employee_id=${encodeURIComponent(employeeId)}&asset_id=${encodeURIComponent(assetId)}`)
          .then(response => response.json())
          .then(data => {
            if (data.error) {
              alert(data.error);
              return;
            }

            // Text fields
            document.getElementById('viewDescription').textContent = data.description;
            document.getElementById('viewOfficeName').textContent = data.office_name;
            document.getElementById('viewCategoryName').textContent = data.category_name;
            document.getElementById('viewType').textContent = data.type;
            document.getElementById('viewStatus').textContent = data.status;
            document.getElementById('viewQuantity').textContent = data.quantity;
            document.getElementById('viewUnit').textContent = data.unit;
            document.getElementById('viewSerialNo').textContent = data.serial_no;
            document.getElementById('viewPropertyNo').textContent = data.property_no;
            document.getElementById('viewCode').textContent = data.code;
            document.getElementById('viewAcquisitionDate').textContent = data.acquisition_date;
            document.getElementById('viewLastUpdated').textContent = data.last_updated;
            document.getElementById('viewValue').textContent = parseFloat(data.value).toFixed(2);
            document.getElementById('viewInventoryTag').textContent = data.inventory_tag;
            document.getElementById('viewBrand').textContent = data.brand;

            // Compute total
            const totalValue = parseFloat(data.value) * parseInt(data.quantity);
            document.getElementById('viewTotalValue').textContent = totalValue.toFixed(2);

            // Images
            document.getElementById('viewAssetImage').src = '../img/assets/' + data.image;
            document.getElementById('municipalLogoImg').src = systemLogo;
            document.getElementById('viewQrCode').src = '../img/' + data.qr_code;
          })
          .catch(() => {
            alert("Failed to load asset details.");
          });
      });

    });

    // Open Edit Employee Modal and populate fields
    $(document).on("click", ".edit-employee", function() {
      let empId = $(this).data("id");
      let empNo = $(this).data("no");
      let empName = $(this).data("name");
      let empStatus = $(this).data("status");
      let empImage = $(this).data("image");
      let empOfficeId = $(this).data("office");

      $("#editEmployeeId").val(empId);
      $("#editEmployeeNo").val(empNo);
      $("#editEmployeeName").val(empName);
      $("#editStatus").val(empStatus);
      $("#editOfficeId").val(empOfficeId);

      // Show current image
      if (empImage) {
        $("#currentImagePreview").attr("src", "../img/" + empImage).show();
      } else {
        $("#currentImagePreview").hide();
      }

      $("#editEmployeeModal").modal("show");
    });

    $(document).on('click', '.transfer-asset', function () {
  // Get asset data
  const assetId = $(this).data('asset-id');
  const inventoryTag = $(this).data('inventory-tag');
  const currentEmployeeId = $(this).data('current-employee-id');
  
  // Option 1: Use hardcoded ITR form ID (change this to your actual ITR form ID)
  const ITR_FORM_ID = 9; // Change this to the actual ITR form ID from your forms table
  
  // Redirect to forms.php with ITR form ID and asset parameters
  window.location.href = `forms.php?id=${ITR_FORM_ID}&asset_id=${assetId}&inventory_tag=${inventoryTag}&current_employee_id=${currentEmployeeId}`;
  
  // Option 2: Dynamic fetch from database (uncomment below and comment above if you prefer dynamic)
  /*
  $.ajax({
    url: 'get_itr_form_id.php',
    type: 'GET',
    dataType: 'json',
    success: function(response) {
      if (response.success && response.form_id) {
        window.location.href = `forms.php?id=${response.form_id}&asset_id=${assetId}&inventory_tag=${inventoryTag}&current_employee_id=${currentEmployeeId}`;
      } else {
        window.location.href = `itr_form.php?asset_id=${assetId}&inventory_tag=${inventoryTag}&current_employee_id=${currentEmployeeId}`;
      }
    },
    error: function() {
      window.location.href = `itr_form.php?asset_id=${assetId}&inventory_tag=${inventoryTag}&current_employee_id=${currentEmployeeId}`;
    }
  });
  */
});

  </script>
</body>

</html>

<?php include 'modals/add_employee_modal.php'; ?>
<?php include 'modals/edit_employee_modal.php'; ?>
<?php include 'modals/import_employee_modal.php'; ?>
<?php include 'modals/employee_duplicate_modal.php'; ?>
<?php include 'modals/add_employee_duplicate_modal.php'; ?>





