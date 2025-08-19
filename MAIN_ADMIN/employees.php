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
$stmt->fetch();
$stmt->close();

// Fetch employees
$employees = [];
$result = $conn->query("SELECT employee_id, employee_no, name, status, date_added, image FROM employees ORDER BY date_added DESC");
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $employees[] = $row;
  }
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
        <div class="card-header bg-light">
          <h5 class="mb-0"><i class="bi bi-people-fill"></i> Employees</h5>
        </div>
        <div class="card-body">
          <table id="employeeTable" class="table table-striped table-bordered">
            <thead class="table-light">
              <tr>
                <th>Employee No</th>
                <th>Name</th>
                <th>Status</th>
                <th>Date Added</th>
                <th>Image</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($employees as $emp): ?>
                <tr>
                  <td><?= htmlspecialchars($emp['employee_no']) ?></td>
                  <td><?= htmlspecialchars($emp['name']) ?></td>
                  <td>
                    <span class="badge 
                      <?= $emp['status'] == 'permanent' ? 'bg-success' : 
                         ($emp['status'] == 'contractual' ? 'bg-warning text-dark' : 
                         ($emp['status'] == 'resigned' ? 'bg-secondary' : 'bg-info')) ?>">
                      <?= htmlspecialchars(ucfirst($emp['status'])) ?>
                    </span>
                  </td>
                  <td><?= htmlspecialchars($emp['date_added']) ?></td>
                  <td>
                    <?php if (!empty($emp['image'])): ?>
                      <img src="uploads/employees/<?= htmlspecialchars($emp['image']) ?>" alt="Employee Image" width="50" height="50" class="rounded-circle">
                    <?php else: ?>
                      <span class="text-muted">No image</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <button class="btn btn-sm btn-primary view-assets" data-id="<?= $emp['employee_id'] ?>" data-name="<?= htmlspecialchars($emp['name']) ?>">
                      <i class="bi bi-eye"></i> View
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal -->
  <div class="modal fade" id="assetsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title"><i class="bi bi-box"></i> Assets MR to <span id="employeeName"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <table class="table table-bordered">
            <thead>
              <tr>
                <th>Asset Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Serial No</th>
                <th>Property No</th>
              </tr>
            </thead>
            <tbody id="assetsTableBody">
              <tr>
                <td colspan="5" class="text-center text-muted">Select an employee to view assets.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#employeeTable').DataTable();

      // Load assets when view button clicked
      $('.view-assets').click(function() {
        let empId = $(this).data('id');
        let empName = $(this).data('name');

        $('#employeeName').text(empName);
        $('#assetsTableBody').html('<tr><td colspan="5" class="text-center">Loading...</td></tr>');
        $('#assetsModal').modal('show');

        $.ajax({
          url: 'fetch_employee_assets.php',
          method: 'GET',
          data: { employee_id: empId },
          success: function(response) {
            $('#assetsTableBody').html(response);
          },
          error: function() {
            $('#assetsTableBody').html('<tr><td colspan="5" class="text-danger text-center">Failed to load assets.</td></tr>');
          }
        });
      });
    });
  </script>
</body>
</html>

// fetch_employees_assets.php
<?php
require_once '../connect.php';

if (isset($_GET['employee_id'])) {
    $employee_id = intval($_GET['employee_id']);

    $stmt = $conn->prepare("SELECT asset_name, description, status, serial_no, property_no 
                            FROM assets 
                            WHERE employee_id = ?");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>" . htmlspecialchars($row['asset_name']) . "</td>
                    <td>" . htmlspecialchars($row['description']) . "</td>
                    <td>" . htmlspecialchars($row['status']) . "</td>
                    <td>" . htmlspecialchars($row['serial_no']) . "</td>
                    <td>" . htmlspecialchars($row['property_no']) . "</td>
                  </tr>";
        }
    } else {
        echo "<tr><td colspan='5' class='text-center text-muted'>No assets assigned.</td></tr>";
    }

    $stmt->close();
}
?>

