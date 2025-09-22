<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch full name for topbar display
$fullname = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Scan QR</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />

  <style>
    #reader {
      width: 100%;
      max-width: 400px;
      margin: auto;
      border: 2px solid #0d6efd;
      border-radius: 10px;
      padding: 10px;
      background: #f8f9fa;
    }
    #scan-result {
      text-align: center;
      font-size: 1.1rem;
      margin-top: 1rem;
    }
  </style>
</head>

<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main">
  <?php include 'includes/topbar.php'; ?>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6 text-center">
        <h3 class="mb-4">Scan QR Code</h3>
        <div id="reader"></div>
        <div id="scan-result" class="text-success fw-bold"></div>
      </div>
    </div>

    <!-- Asset Details (after scan) -->
    <?php
    if (isset($_GET['asset_id']) && is_numeric($_GET['asset_id'])):
      $asset_id = $_GET['asset_id'];

      $stmt = $conn->prepare("
        SELECT a.*, c.category_name, o.office_name, e.name AS employee_name
        FROM assets a
        LEFT JOIN categories c ON a.category = c.id
        LEFT JOIN offices o ON a.office_id = o.id
        LEFT JOIN employees e ON a.employee_id = e.employee_id
        WHERE a.id = ?
      ");
      $stmt->bind_param("i", $asset_id);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($row = $result->fetch_assoc()):
    ?>
      <div class="card mt-4 shadow-sm mx-auto" style="max-width: 700px;">
        <div class="card-header">
          <h5 class="mb-0"><i class="bi bi-box-seam"></i> Asset Details</h5>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-8">
              <p class="mb-1"><strong>Asset ID:</strong> <?= (int)$row['id'] ?></p>
              <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($row['asset_name'] ?? $row['description']) ?></p>
              <p class="mb-1"><strong>Description:</strong> <?= htmlspecialchars($row['description']) ?></p>
              <p class="mb-1"><strong>Category:</strong> <?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></p>
              <p class="mb-1"><strong>Type:</strong> <?= htmlspecialchars($row['type']) ?></p>
              <p class="mb-1"><strong>Quantity / Unit:</strong> <?= (int)$row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?></p>
              <p class="mb-1"><strong>Status:</strong>
                <span class="badge bg-<?= $row['status'] === 'available' ? 'success' : ($row['status'] === 'borrowed' ? 'warning' : 'secondary') ?>">
                  <?= $row['red_tagged'] ? 'Red-Tagged' : ucfirst($row['status']) ?>
                </span>
              </p>
              <p class="mb-1"><strong>Value:</strong> &#8369;<?= number_format((float)$row['value'], 2) ?></p>
              <p class="mb-1"><strong>Office:</strong> <?= htmlspecialchars($row['office_name'] ?? '—') ?></p>
              <p class="mb-1"><strong>Person Accountable:</strong> <?= htmlspecialchars($row['employee_name'] ?? '—') ?></p>
              <p class="mb-1"><strong>Property No.:</strong> <?= htmlspecialchars($row['property_no'] ?? '') ?></p>
              <p class="mb-1"><strong>Serial No.:</strong> <?= htmlspecialchars($row['serial_no'] ?? '') ?></p>
              <p class="mb-1"><strong>Model:</strong> <?= htmlspecialchars($row['model'] ?? '') ?></p>
              <p class="mb-1"><strong>Brand:</strong> <?= htmlspecialchars($row['brand'] ?? '') ?></p>
              <p class="mb-1"><strong>Code:</strong> <?= htmlspecialchars($row['code'] ?? '') ?></p>
              <p class="mb-1"><strong>ICS ID:</strong> <?= htmlspecialchars($row['ics_id'] ?? '') ?></p>
              <p class="mb-1"><strong>Acquired On:</strong> <?= $row['acquisition_date'] ? date('F j, Y', strtotime($row['acquisition_date'])) : '—' ?></p>
              <p class="mb-1"><strong>Last Updated:</strong> <?= $row['last_updated'] ? date('F j, Y', strtotime($row['last_updated'])) : '—' ?></p>
            </div>
            <div class="col-md-4 text-center">
              <?php if (!empty($row['image'])): ?>
                <img src="../img/<?= htmlspecialchars($row['image']) ?>" alt="Asset Image" class="img-fluid border rounded mb-2" style="max-height: 160px; object-fit: contain;">
              <?php endif; ?>
              <?php if (!empty($row['qr_code'])): ?>
                <img src="../img/<?= htmlspecialchars($row['qr_code']) ?>" alt="QR Code" class="img-fluid border rounded" style="max-height: 160px; object-fit: contain;">
              <?php endif; ?>
            </div>
          </div>

          <hr>
          <div class="d-flex justify-content-between flex-wrap gap-2 mt-3">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#transferAssetModal">
              <i class="bi bi-arrow-left-right"></i> Transfer
            </button>
            <a href="borrow_asset.php?id=<?= $row['id'] ?>" class="btn btn-outline-warning btn-sm rounded-pill">
              <i class="bi bi-box-arrow-in-right"></i> Borrow
            </a>
            <a href="return_asset.php?id=<?= $row['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-pill">
              <i class="bi bi-box-arrow-in-left"></i> Return
            </a>
            <a href="iirup_form.php?asset_id=<?= $row['id'] ?>&asset_description=<?= urlencode($row['description']) ?>&inventory_tag=<?= urlencode($row['inventory_tag'] ?? $row['property_no'] ?? '') ?>" class="btn btn-danger btn-sm rounded-pill">
              <i class="bi bi-tag"></i> Red Tag
            </a>
          </div>
        </div>
      </div>

      <?php
        // Preload employees for modal selection
        $emp_res = $conn->query("SELECT employee_id, name FROM employees ORDER BY name ASC");
        $employees = $emp_res ? $emp_res->fetch_all(MYSQLI_ASSOC) : [];
        $inventory_tag_value = $row['inventory_tag'] ?? ($row['property_no'] ?? '');
      ?>

      <!-- Transfer Asset Modal -->
      <div class="modal fade" id="transferAssetModal" tabindex="-1" aria-labelledby="transferAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="transferAssetModalLabel">Transfer Asset to New Person Accountable</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="transfer_asset.php">
              <div class="modal-body">
                <input type="hidden" name="asset_id" value="<?= (int)$row['id'] ?>">
                <input type="hidden" name="inventory_tag" value="<?= htmlspecialchars($inventory_tag_value) ?>">

                <div class="mb-3">
                  <label for="newEmployee" class="form-label">Select New Person Accountable</label>
                  <input list="employeeList" class="form-control" id="newEmployee" name="new_employee" placeholder="Type to search... (e.g., 12 - Juan Dela Cruz)" required>
                  <datalist id="employeeList">
                    <?php foreach ($employees as $emp): ?>
                      <option value="<?= (int)$emp['employee_id'] . ' - ' . htmlspecialchars($emp['name']) ?>"></option>
                    <?php endforeach; ?>
                  </datalist>
                  <div class="form-text">Format required: "employee_id - employee name"</div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Transfer</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-warning mt-4 text-center">No asset found with ID <?= htmlspecialchars($asset_id) ?>.</div>
    <?php endif; $stmt->close(); endif; ?>
  </div>
</div>

<!-- QR Code Scanner Script -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
  function onScanSuccess(decodedText, decodedResult) {
    const assetId = decodedText.trim();
    if (!/^\d+$/.test(assetId)) {
      document.getElementById('scan-result').innerHTML = `<span class="text-danger">Invalid QR code: ${assetId}</span>`;
      return;
    }

    document.getElementById('scan-result').textContent = `Scanned: ${assetId}`;
    setTimeout(() => {
      window.location.href = `scan_qr.php?asset_id=${assetId}`;
    }, 1000);
  }

  function onScanError(errorMessage) {
    console.warn(`QR scan error: ${errorMessage}`);
  }

  const html5QrCode = new Html5Qrcode("reader");
  Html5Qrcode.getCameras().then(cameras => {
    if (cameras && cameras.length) {
      html5QrCode.start(
        cameras[0].id,
        { fps: 10, qrbox: 250 },
        onScanSuccess,
        onScanError
      );
    }
  }).catch(err => {
    document.getElementById('scan-result').textContent = "Camera not accessible.";
    console.error("Camera init error:", err);
  });
</script>

<!-- Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</body>
</html>
