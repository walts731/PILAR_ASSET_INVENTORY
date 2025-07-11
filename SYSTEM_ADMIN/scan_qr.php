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
        SELECT a.*, c.category_name, o.office_name 
        FROM assets a
        LEFT JOIN categories c ON a.category = c.id
        LEFT JOIN offices o ON a.office_id = o.id
        WHERE a.id = ?
      ");
      $stmt->bind_param("i", $asset_id);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($row = $result->fetch_assoc()):
    ?>
      <div class="card mt-4 shadow-sm mx-auto" style="max-width: 500px;">
        <div class="card-header">
          <h5 class="mb-0"><i class="bi bi-box-seam"></i> Asset Details</h5>
        </div>
        <div class="card-body">
          <p><strong>Name:</strong> <?= htmlspecialchars($row['asset_name']) ?></p>
          <p><strong>Category:</strong> <?= htmlspecialchars($row['category_name']) ?></p>
          <p><strong>Description:</strong> <?= htmlspecialchars($row['description']) ?></p>
          <p><strong>Quantity:</strong> <?= $row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?></p>
          <p><strong>Status:</strong>
            <span class="badge bg-<?= $row['status'] === 'available' ? 'success' : ($row['status'] === 'borrowed' ? 'warning' : 'secondary') ?>">
              <?= $row['red_tagged'] ? 'Red-Tagged' : ucfirst($row['status']) ?>
            </span>
          </p>
          <p><strong>Value:</strong> &#8369;<?= number_format($row['value'], 2) ?></p>
          <p><strong>Acquired On:</strong> <?= date('F j, Y', strtotime($row['acquisition_date'])) ?></p>
          <p><strong>Last Updated:</strong> <?= date('F j, Y', strtotime($row['last_updated'])) ?></p>
          <p><strong>Office:</strong> <?= htmlspecialchars($row['office_name']) ?></p>

          <hr>
          <div class="d-flex justify-content-between flex-wrap gap-2 mt-3">
            <a href="transfer_asset.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary btn-sm rounded-pill">
              <i class="bi bi-arrow-left-right"></i> Transfer
            </a>
            <a href="borrow_asset.php?id=<?= $row['id'] ?>" class="btn btn-outline-warning btn-sm rounded-pill">
              <i class="bi bi-box-arrow-in-right"></i> Borrow
            </a>
            <a href="release_asset.php?id=<?= $row['id'] ?>" class="btn btn-outline-info btn-sm rounded-pill">
              <i class="bi bi-box-arrow-up"></i> Release
            </a>
            <a href="return_asset.php?id=<?= $row['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-pill">
              <i class="bi bi-box-arrow-in-left"></i> Return
            </a>
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
