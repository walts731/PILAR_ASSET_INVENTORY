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

// Fetch assets for this category
$assets = [];
if ($category) {
  $stmt = $conn->prepare("
    SELECT a.id, a.asset_name, c.category_name, a.description, a.quantity, a.unit,
           a.status, a.acquisition_date, o.office_name, a.red_tagged, a.last_updated,
           a.value, a.qr_code, a.type, a.image, a.serial_no, a.code, a.property_no
    FROM assets a
    JOIN categories c ON a.category = c.id
    LEFT JOIN offices o ON a.office_id = o.id
    WHERE a.category = ?
  ");
  $stmt->bind_param("i", $category_id);
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
          <div class="alert alert-warning">No assets found in this category.</div>
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

  <!-- Asset Modal -->
  <div class="modal fade" id="viewAssetModal" tabindex="-1" aria-labelledby="viewAssetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
      <div class="modal-content shadow border border-dark">
        <div class="modal-body p-4" style="font-family: 'Courier New', Courier, monospace;">
          <div class="border border-2 border-dark rounded p-3">

            <!-- Header: Logo, QR, GOV LABEL -->
            <div class="d-flex justify-content-between align-items-center mb-2">
              <img id="municipalLogoImg" src="" alt="Municipal Logo" style="height: 70px;">
              <div class="text-center flex-grow-1">
                <h6 class="m-0 text-uppercase fw-bold">Government Property</h6>
              </div>
              <img id="viewQrCode" src="" alt="QR Code" style="height: 70px;">
            </div>

            <hr class="border-dark">

            <!-- Description on Top -->
            <div class="mb-3">
              <p class="mb-1"><strong>Description:</strong> <span id="viewDescription"></span></p>
            </div>

            <!-- Asset Image + Info -->
            <div class="row">
              <!-- Asset Image -->
              <div class="col-5 text-center">
                <label class="form-label fw-bold">Asset Image</label>
                <img id="viewAssetImage" src="" alt="Asset Image" 
                     class="img-fluid border border-dark rounded" 
                     style="max-height: 150px; object-fit: contain;">
              </div>

              <!-- Asset Details -->
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

  <script>
    const systemLogo = "<?= $systemLogo ?>"; // logo from system table

    document.querySelectorAll('.viewAssetBtn').forEach(button => {
      button.addEventListener('click', function () {
        const assetId = this.getAttribute('data-id');

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
            document.getElementById('viewCategoryName').textContent = data.category;
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

            // Compute total
            const totalValue = parseFloat(data.value) * parseInt(data.quantity);
            document.getElementById('viewTotalValue').textContent = totalValue.toFixed(2);

            // Images
            document.getElementById('viewAssetImage').src = '../img/assets/' + data.image;
            document.getElementById('municipalLogoImg').src = systemLogo; // always from system table
            document.getElementById('viewQrCode').src = '../img/' + data.qr_code;
          });
      });
    });

    $(document).ready(function () {
    $('#inventoryTable').DataTable({
      paging: true,          // Enable pagination
      searching: true,       // Enable search box
      ordering: true,        // Enable column sorting
      info: true,            // Show table info
      pageLength: 10,        // Default rows per page
      lengthMenu: [5, 10, 25, 50, 100], // Rows per page options
      language: {
        search: "Filter records:", // Customize search label
        lengthMenu: "Show _MENU_ entries",
        info: "Showing _START_ to _END_ of _TOTAL_ entries",
        paginate: {
          previous: "Prev",
          next: "Next"
        }
      }
    });
  });
  </script>

</body>
</html>
