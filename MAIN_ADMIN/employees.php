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
                  <th>Date Added</th>
                  <th>Image</th>
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
                    <td><?= date("F d, Y", strtotime($emp['date_added'])) ?></td>
                    <td>
                      <?php if (!empty($emp['image'])): ?>
                        <img src="../img/<?= htmlspecialchars($emp['image']) ?>"
                          alt="Employee Image"
                          width="50" height="50"
                          class="rounded-circle">
                      <?php else: ?>
                        <span class="text-muted">No image</span>
                      <?php endif; ?>
                    </td>

                    <td>
                      <button type="button" class="btn btn-sm btn-outline-primary view-assets"
                        data-id="<?= $emp['employee_id'] ?>"
                        data-name="<?= htmlspecialchars($emp['name']) ?>">
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
    <div class="modal-dialog modal-md">
      <div class="modal-content shadow border border-dark">
        <div class="modal-body p-4" style="font-family: 'Courier New', Courier, monospace;">
          <div class="border border-2 border-dark rounded p-3">

            <!-- Header: Logo, QR, GOV LABEL -->
            <div class="d-flex justify-content-between align-items-center mb-2">
              <img id="municipalLogoImg" src="<?= htmlspecialchars($systemLogo) ?>" alt="Municipal Logo" style="height: 70px;">
              <div class="text-center flex-grow-1">
                <h6 class="m-0 text-uppercase fw-bold">Government Property</h6>
                <p class="m-0 small"><strong>Inventory Tag:</strong> <span id="viewInventoryTag"></span></p>
              </div>
              <img id="viewQrCode" src="" alt="QR Code" style="height: 70px;">
            </div>

            <hr class="border-dark">

            <!-- Description -->
            <div class="mb-3">
              <p class="mb-1"><strong>Description:</strong> <span id="viewDescription"></span></p>
            </div>

            <!-- Asset Image + Info -->
            <div class="row">
              <div class="col-5 text-center">
                <label class="form-label fw-bold">Asset Image</label>
                <img id="viewAssetImage" src="" alt="Asset Image"
                  class="img-fluid border border-dark rounded"
                  style="max-height: 150px; object-fit: contain;">
              </div>
              <div class="col-7">
                <p class="mb-1"><strong>Office:</strong> <span id="viewOfficeName"></span></p>
                <p class="mb-1"><strong>Category:</strong> <span id="viewCategoryName"></span></p>
                <p class="mb-1"><strong>Type:</strong> <span id="viewType"></span></p>
                <p class="mb-1"><strong>Status:</strong> <span id="viewStatus"></span></p>
                <p class="mb-1"><strong>Quantity:</strong> <span id="viewQuantity"></span></p>
                <p class="mb-1"><strong>Unit:</strong> <span id="viewUnit"></span></p>
                <p class="mb-1"><strong>Serial No:</strong> <span id="viewSerialNo"></span></p>
                <p class="mb-1"><strong>Property No:</strong> <span id="viewPropertyNo"></span></p>
                <p class="mb-1"><strong>Code:</strong> <span id="viewCode"></span></p>
                <p class="mb-1"><strong>Brand:</strong> <span id="viewBrand"></span></p>
              </div>
            </div>

            <hr class="border-dark">

            <!-- Dates + Value -->
            <div class="mt-3">
              <p class="mb-1"><strong>Acquisition Date:</strong> <span id="viewAcquisitionDate"></span></p>
              <p class="mb-1"><strong>Last Updated:</strong> <span id="viewLastUpdated"></span></p>
              <p class="mb-1"><strong>Unit Cost:</strong> ₱ <span id="viewValue"></span></p>
              <p class="mb-1"><strong>Total Value:</strong> ₱ <span id="viewTotalValue"></span></p>
            </div>

          </div>
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

        $('#employeeName').text(empName);
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
  $('#employeesList option').each(function () {
    if ($(this).val().startsWith(currentEmpId + " -")) {
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

          <label for="new_employee" class="form-label">Select New Employee</label>
          <input class="form-control" list="employeesList" name="new_employee" id="new_employee"
                 placeholder="Type to search employee..." required>

          <datalist id="employeesList">
            <?php
              // load all employees (exclude later in JS)
              $empRes = $conn->query("SELECT employee_id, name FROM employees");
              while ($emp = $empRes->fetch_assoc()) {
                echo "<option value='{$emp['employee_id']} - {$emp['name']}'>";
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





