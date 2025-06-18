<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch full name for display
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
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
    </div>
  </div>

  <!-- QR Code Scanner Script -->
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
  <script>
    function onScanSuccess(decodedText, decodedResult) {
      document.getElementById('scan-result').textContent = `Scanned: ${decodedText}`;

      // Optional: redirect to asset or user page
      if (decodedText.startsWith("http")) {
        setTimeout(() => window.location.href = decodedText, 1500);
      }
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
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
</html>
