<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
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
      <div class="card shadow">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="bi bi-people-fill"></i> Employees</h5>

          <div class="d-flex gap-2">
            <button id="generateMrReportBtn" class="btn btn-sm btn-primary rounded-pill">
              <i class="bi bi-filetype-pdf"></i> Generate MR Report
            </button>

            <button class="btn btn-sm btn-outline-info rounded-pill" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">
              <i class="bi bi-plus-circle"></i> Add Employee
            </button>

            <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#importEmployeeModal">
              <i class="bi bi-file-earmark-arrow-up"></i> Import CSV
            </button>
            <a class="btn btn-sm btn-outline-success rounded-pill" href="employees.php?export_employees=1">
              <i class="bi bi-download"></i> Export CSV
            </a>
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
                      <input type="checkbox" class="emp-checkbox" name="selected_employees[]" value="<?= (int)$emp['employee_id'] ?>" />
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

      // Select All handling
      $('#selectAllEmployees').on('change', function() {
        const checked = $(this).is(':checked');
        $('.emp-checkbox').prop('checked', checked);
      });

      // Keep Select All in sync
      $(document).on('change', '.emp-checkbox', function() {
        const total = $('.emp-checkbox').length;
        const selected = $('.emp-checkbox:checked').length;
        $('#selectAllEmployees').prop('checked', selected === total);
      });

      // Handle Generate MR Report
      $('#generateMrReportBtn').on('click', function(e){
        e.preventDefault();
        const hasSelection = $('.emp-checkbox:checked').length > 0;
        if (!hasSelection) {
          alert('Please select at least one employee. Only employees with MR assets will be included in the report.');
          return;
        }
        $('#employeeReportForm')[0].submit();
      });

      // Load assets for employee
      $('.view-assets').click(function() {
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

        // Employee image
        if (empImage) {
          $('#empInfoImage').attr('src', '../img/' + empImage).show();
        } else {
          $('#empInfoImage').attr('src', 'https://via.placeholder.com/70?text=No+Image');
        }

        // Date Joined
        const empJoined = $(this).data('joined');
        $('#empInfoDateJoined').text(empJoined || '—');

        $('#assetsTableBody').html('<tr><td colspan="6" class="text-center">Loading...</td></tr>');
        $('#assetsModal').modal('show');

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

      // Load asset details (new unified code)
      $(document).on("click", ".view-asset-details", function() {
        const assetId = $(this).data("id");
        $('#assetDetailsModal').modal('show');

        fetch(`get_asset_details.php?id=${assetId}`)
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

      // Bind datalist selection to hidden employee_id for transfer modal
      $(document).on('input', '#new_employee', function() {
        const val = $(this).val();
        const match = $('#employeesList option').filter(function() { return $(this).val() === val; }).first();
        const empId = match.data('emp-id') || '';
        $('#new_employee_id').val(empId);
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
  // pass hidden values
  $('#transfer_asset_id').val($(this).data('asset-id'));
  $('#transfer_inventory_tag').val($(this).data('inventory-tag'));
  $('#transfer_current_employee_id').val($(this).data('current-employee-id'));

  // reset datalist (reload from PHP, avoid duplicates)
  let currentEmpId = $(this).data('current-employee-id');
  $('#new_employee').val('');
  $('#new_employee_id').val('');
  $('#employeesList option').each(function () {
    if ($(this).data('emp-id') == currentEmpId) {
      $(this).remove();
    }
  });

  // show modal
  $('#transferModal').modal('show');
});

  </script>
</body>

</html>

<?php include 'modals/add_employee_modal.php'; ?>
<?php include 'modals/edit_employee_modal.php'; ?>
<?php include 'modals/import_employee_modal.php'; ?>
<?php include 'modals/employee_duplicate_modal.php'; ?>
<?php include 'modals/add_employee_duplicate_modal.php'; ?>
<!-- Transfer Asset Modal -->
<div class="modal fade" id="transferModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="transferForm" method="POST" action="transfer_asset.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Transfer Asset</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <!-- hidden fields -->
          <input type="hidden" name="asset_id" id="transfer_asset_id">
          <input type="hidden" name="inventory_tag" id="transfer_inventory_tag">
          <input type="hidden" name="current_employee_id" id="transfer_current_employee_id">
          <input type="hidden" name="new_employee_id" id="new_employee_id">

          <label for="new_employee" class="form-label">Select New Employee</label>
          <input class="form-control" list="employeesList" name="new_employee" id="new_employee"
                 placeholder="Type to search employee..." required>

          <datalist id="employeesList">
            <?php
              // load all employees (exclude current in JS); show only name and employee_no
              $empRes = $conn->query("SELECT employee_id, employee_no, name FROM employees");
              while ($emp = $empRes->fetch_assoc()) {
                $display = htmlspecialchars($emp['name'] . ' - ' . $emp['employee_no']);
                $empId = (int)$emp['employee_id'];
                echo "<option value=\"{$display}\" data-emp-id=\"{$empId}\"></option>";
              }
            ?>
          </datalist>
        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Confirm Transfer</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </div>
    </form>
  </div>
</div>





