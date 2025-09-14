<?php
require_once '../connect.php';
session_start();

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Set office_id if not set
if (!isset($_SESSION['office_id'])) {
  $user_id = $_SESSION['user_id'];
  $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
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

$office_id = $_SESSION['office_id'];

// Get category ID from URL
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Fetch category details
$category = null;
$stmt = $conn->prepare("SELECT id, category_name FROM categories WHERE id = ?");
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
  $category = $result->fetch_assoc();
}
$stmt->close();

// Fetch assets for this category and office
$assets = [];
if ($category) {
  $stmt = $conn->prepare("
    SELECT a.id, a.asset_name, c.category_name, a.description, a.quantity, a.unit,
           a.status, a.acquisition_date, o.office_name, a.red_tagged, a.last_updated,
           a.value, a.qr_code, a.type, a.image, a.serial_no, a.code, a.property_no
    FROM assets a
    JOIN categories c ON a.category = c.id
    LEFT JOIN offices o ON a.office_id = o.id
    WHERE a.category = ? AND a.office_id = ? AND a.quantity > 0
  ");
  $stmt->bind_param("ii", $category_id, $office_id);
  $stmt->execute();
  $result = $stmt->get_result();
  while ($row = $result->fetch_assoc()) {
    $assets[] = $row;
  }
  $stmt->close();
}

// Get system logo
$systemSql = "SELECT logo FROM system LIMIT 1";
$systemResult = $conn->query($systemSql);
$system = $systemResult->fetch_assoc();
$systemLogo = !empty($system['logo']) ? '../img/' . $system['logo'] : '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $category ? htmlspecialchars($category['category_name']) : 'Category Not Found' ?> - Inventory</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>

<body>

  <?php include 'includes/sidebar.php' ?>

  <div class="main">

    <?php include 'includes/topbar.php' ?>

    <div class="container-fluid mt-4">
      <?php if ($category): ?>

        <?php if (count($assets) > 0): ?>
          <div class="card shadow-sm">

            <div class="card-body">
              <div class="table-responsive">
                <table id="inventoryTable" class="table table-hover table-sm align-middle table-borderless">
                  <thead class="table-light">
                    <tr>
                      <th>Description</th>
                      <th>Category</th>
                      <th>Quantity</th>
                      <th>Unit</th>
                      <th>Status</th>
                      <th>Value</th>
                      <th>Total Value</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($assets as $asset): ?>
                      <?php $totalValue = $asset['quantity'] * $asset['value']; ?>
                      <tr>
                        <td><?= htmlspecialchars($asset['description']) ?></td>
                        <td><?= htmlspecialchars($asset['category_name']) ?></td>
                        <td><?= $asset['quantity'] ?></td>
                        <td><?= htmlspecialchars($asset['unit']) ?></td>
                        <td><?= htmlspecialchars($asset['status']) ?></td>
                        <td>₱<?= number_format($asset['value'], 2) ?></td>
                        <td>₱<?= number_format($totalValue, 2) ?></td>
                        <td class="text-center">
                          <button
                            class="btn btn-sm btn-primary viewAssetBtn"
                            data-bs-toggle="modal"
                            data-bs-target="#viewAssetModal"
                            data-id="<?= $asset['id'] ?>">
                            <i class="bi bi-eye"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                  <?php include 'modals/inventory_category_modal.php'; ?>
                </table>
              </div>
            </div>
          </div>

        <?php else: ?>
          <div class="alert alert-warning">No assets found in this category for your office.</div>
        <?php endif; ?>
      <?php else: ?>
        <div class="alert alert-danger">Category not found.</div>
      <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>

  <script>
    const systemLogo = "<?= $systemLogo ?>"; // logo from system table

    document.querySelectorAll('.viewAssetBtn').forEach(button => {
      button.addEventListener('click', function() {
        const assetId = this.getAttribute('data-id');

        fetch(`get_asset_details.php?id=${assetId}`)
          .then(response => response.json())
          .then(data => {
            if (data.error) {
              alert(data.error);
              return;
            }

            // Fill modal fields
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
            document.getElementById('viewBrand').textContent = data.brand;
            document.getElementById('viewModel').textContent = data.model;
            document.getElementById('viewInventoryTag').textContent = data.inventory_tag;
            document.getElementById('viewEmployeeName').textContent = data.employee_name ?? '';

            // Compute total
            const totalValue = parseFloat(data.value) * parseInt(data.quantity);
            document.getElementById('viewTotalValue').textContent = totalValue.toFixed(2);

            // Images
            document.getElementById('viewAssetImage').src = '../img/assets/' + data.image;
            document.getElementById('municipalLogoImg').src = systemLogo;
            document.getElementById('viewQrCode').src = '../img/' + data.qr_code;
          });
      });
    });

    $(document).ready(function() {
      $('#inventoryTable').DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        language: {
          search: "Filter records:",
          lengthMenu: "Show _MENU_ entries",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          paginate: { previous: "Prev", next: "Next" }
        }
      });
    });
  </script>

</body>
</html>
