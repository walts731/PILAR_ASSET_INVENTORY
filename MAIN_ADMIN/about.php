<?php
require_once '../connect.php';
session_start();
$page = 'about';

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
}

// Fetch system info
$info = [];
$result = $conn->query("SELECT * FROM system_info LIMIT 1");
if ($result && $result->num_rows > 0) {
  $info = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>About | <?= htmlspecialchars($info['system_name'] ?? 'System') ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
    .about-hero {
      background: linear-gradient(90deg, #0d6efd, #0dcaf0);
      color: white;
      padding: 2rem;
      border-radius: 12px;
    }

    .about-hero h2 {
      font-weight: 700;
    }

    .info-card {
      border-left: 6px solid #0dcaf0;
      background-color: #f8f9fa;
      border-radius: 12px;
      box-shadow: 0 0 12px rgba(0, 0, 0, 0.05);
    }

    .info-icon {
      font-size: 2rem;
      color: #0dcaf0;
    }

    .section-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-top: 1.5rem;
    }

    .badge-version {
      font-size: 1rem;
      background-color: #0dcaf0;
    }
  </style>
</head>

<body>

  <?php include 'includes/sidebar.php' ?>

  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container my-4">

      <div class="about-hero mb-4">
        <h2><i class="bi bi-info-circle-fill"></i> About the System</h2>
        <p class="mb-0"><?= htmlspecialchars($info['system_name']) ?> — Version <span class="badge bg-light text-dark"><?= htmlspecialchars($info['version']) ?></span></p>
      </div>

      <div class="row g-4">
        <div class="col-lg-6">
          <div class="p-4 info-card h-100">
            <div class="d-flex align-items-start mb-3">
              <i class="bi bi-body-text info-icon me-3"></i>
              <div>
                <div class="section-title">System Description</div>
                <p><?= nl2br(htmlspecialchars($info['description'])) ?></p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="p-4 info-card h-100">
            <div class="d-flex align-items-start mb-3">
              <i class="bi bi-person-fill info-icon me-3"></i>
              <div>
                <div class="section-title">Developer</div>
                <p class="mb-2"><strong>Name:</strong><br><?= nl2br(htmlspecialchars($info['developer_name'])) ?></p>
                <p><strong>Email:</strong><br><?= nl2br(htmlspecialchars($info['developer_email'])) ?></p>
              </div>
            </div>

            <div class="d-flex align-items-start">
              <i class="bi bi-code-slash info-icon me-3"></i>
              <div>
                <div class="section-title">System Version</div>
                <p><span class="badge badge-version"><?= htmlspecialchars($info['version']) ?></span></p>
              </div>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="p-4 info-card">
            <div class="d-flex align-items-start">
              <i class="bi bi-award-fill info-icon me-3"></i>
              <div>
                <div class="section-title">Credits</div>
                <p><?= nl2br(htmlspecialchars($info['credits'])) ?></p>
              </div>
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

</body>
</html>
