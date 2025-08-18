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
                            data-id="<?= $asset['id'] ?>"
                            data-description="<?= htmlspecialchars($asset['description']) ?>"
                            data-category="<?= htmlspecialchars($asset['category_name']) ?>"
                            data-qty="<?= $asset['quantity'] ?>"
                            data-unit="<?= htmlspecialchars($asset['unit']) ?>"
                            data-status="<?= htmlspecialchars($asset['status']) ?>"
                            data-value="₱<?= number_format($asset['value'], 2) ?>"
                            data-total="₱<?= number_format($totalValue, 2) ?>"
                            data-serial="<?= htmlspecialchars($asset['serial_no']) ?>"
                            data-code="<?= htmlspecialchars($asset['code']) ?>"
                            data-property="<?= htmlspecialchars($asset['property_no']) ?>"
                            data-type="<?= htmlspecialchars($asset['type']) ?>"
                            data-office="<?= htmlspecialchars($asset['office_name'] ?? 'N/A') ?>"
                            data-acquisition="<?= htmlspecialchars($asset['acquisition_date']) ?>"
                            data-image="<?= htmlspecialchars($asset['image']) ?>"
                            data-qrcode="<?= htmlspecialchars($asset['qr_code']) ?>">
                            <i class="bi bi-eye"></i>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>

                </table>
              </div>
            </div>
          </div>



          <div class="modal fade" id="viewAssetModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Asset Details</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <table class="table table-bordered">
                    <tr>
                      <th>Description</th>
                      <td id="modalDescription"></td>
                    </tr>
                    <tr>
                      <th>Category</th>
                      <td id="modalCategory"></td>
                    </tr>
                    <tr>
                      <th>Quantity</th>
                      <td id="modalQty"></td>
                    </tr>
                    <tr>
                      <th>Unit</th>
                      <td id="modalUnit"></td>
                    </tr>
                    <tr>
                      <th>Status</th>
                      <td id="modalStatus"></td>
                    </tr>
                    <tr>
                      <th>Value</th>
                      <td id="modalValue"></td>
                    </tr>
                    <tr>
                      <th>Total Value</th>
                      <td id="modalTotal"></td>
                    </tr>
                    <tr>
                      <th>Serial No</th>
                      <td id="modalSerial"></td>
                    </tr>
                    <tr>
                      <th>Code</th>
                      <td id="modalCode"></td>
                    </tr>
                    <tr>
                      <th>Property No</th>
                      <td id="modalProperty"></td>
                    </tr>
                    <tr>
                      <th>Type</th>
                      <td id="modalType"></td>
                    </tr>
                    <tr>
                      <th>Office</th>
                      <td id="modalOffice"></td>
                    </tr>
                    <tr>
                      <th>Acquisition Date</th>
                      <td id="modalAcquisition"></td>
                    </tr>
                    <tr>
                      <th>QR Code</th>
                      <td id="modalQRCode"></td>
                    </tr>
                    <tr>
                      <th>Image</th>
                      <td id="modalImage"></td>
                    </tr>
                  </table>
                </div>
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

  <script>
    $(document).ready(function() {
      $('#inventoryTable').DataTable();

      // Handle eye button click
      $(document).on("click", ".viewAssetBtn", function() {
        $("#modalDescription").text($(this).data("description"));
        $("#modalCategory").text($(this).data("category"));
        $("#modalQty").text($(this).data("qty"));
        $("#modalUnit").text($(this).data("unit"));
        $("#modalStatus").text($(this).data("status"));
        $("#modalValue").text($(this).data("value"));
        $("#modalTotal").text($(this).data("total")); // new line
        $("#modalSerial").text($(this).data("serial"));
        $("#modalCode").text($(this).data("code"));
        $("#modalProperty").text($(this).data("property"));
        $("#modalType").text($(this).data("type"));
        $("#modalOffice").text($(this).data("office"));
        $("#modalAcquisition").text($(this).data("acquisition"));

        // QR Code
        let qrCode = $(this).data("qrcode");
        if (qrCode) {
          $("#modalQRCode").html('<img src="../qrcodes/' + qrCode + '" width="100">');
        } else {
          $("#modalQRCode").text("N/A");
        }

        // Image
        let image = $(this).data("image");
        if (image) {
          $("#modalImage").html('<a href="../uploads/' + image + '" target="_blank"><img src="../uploads/' + image + '" width="100"></a>');
        } else {
          $("#modalImage").text("N/A");
        }
      });
    });
  </script>

</body>

</html>