<?php
require_once '../connect.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Add office_id to session if not already set
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventory Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <style>
  body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #eaf4fc; /* very light blue */
    margin: 0;
  }

  .sidebar {
    height: 100vh;
    background-color: #2196f3; /* material blue */
    color: white;
    position: fixed;
    width: 240px;
    padding: 20px;
  }

  .sidebar h4 {
    font-weight: bold;
  }

  .sidebar a {
    color: white;
    text-decoration: none;
    display: block;
    margin: 12px 0;
    font-weight: 500;
    transition: all 0.2s ease-in-out;
  }

  .sidebar a:hover {
    color: #bbdefb;
    padding-left: 10px;
  }

  .main {
    margin-left: 260px;
    padding: 30px;
    background-color: #fdfefe;
    min-height: 100vh;
  }

  .topbar {
    background-color: #e3f2fd;
    padding: 15px 30px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin-bottom: 30px;
    border-radius: 0.5rem;
  }

  .status-badge {
    font-size: 0.75rem;
    padding: 0.3em 0.5em;
  }

  .card {
    border-radius: 0.75rem;
    border: none;
    background-color: #ffffff;
  }

  .card-header {
    background-color: #f0f9ff;
    border-bottom: 1px solid #dee2e6;
    border-top-left-radius: 0.75rem;
    border-top-right-radius: 0.75rem;
  }

  .table th, .table td {
    vertical-align: middle;
  }
</style>

</head>
<body>

<div class="sidebar">
  <h4>Pilar Inventory</h4>
  <hr />
  <a href="user_dashboard">📦 Inventory</a>
  <a href="#">📊 Reports</a>
  <a href="#">⚙ Settings</a>
  <a href="../logout.php">🚪 Logout</a>
</div>

<div class="main">
  <div class="topbar d-flex justify-content-between align-items-center">
    <h3>Inventory Dashboard</h3>
  </div>

  <!-- Inventory Summary -->
  <div class="row mb-4">
    <?php
    $total = $active = $borrowed = $red_tagged = 0;
    $result = $conn->query("SELECT status, red_tagged FROM assets");
    while ($row = $result->fetch_assoc()) {
      $total++;
      if ($row['status'] === 'available') $active++;
      if ($row['status'] === 'borrowed') $borrowed++;
      if ($row['red_tagged']) $red_tagged++;
    }
    ?>
    <div class="col-md-3 mb-3">
      <div class="card  shadow-sm">
        <div class="card-body">
          <h5>Total Inventory</h5>
          <h3><?= $total ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card  shadow-sm">
        <div class="card-body">
          <h5>Available</h5>
          <h3><?= $active ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card  shadow-sm">
        <div class="card-body">
          <h5>Borrowed</h5>
          <h3><?= $borrowed ?></h3>
        </div>
      </div>
    </div>
    <div class="col-md-3 mb-3">
      <div class="card  shadow-sm">
        <div class="card-body">
          <h5>Red-Tagged</h5>
          <h3><?= $red_tagged ?></h3>
        </div>
      </div>
    </div>
  </div>

  <!-- Inventory Table -->
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Inventory Assets</h5>
      <a href="generate_report.php" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-file-earmark-arrow-down"></i> Generate Report
      </a>
    </div>
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>QR</th>
            <th>Name</th>
            <th>Category</th>
            <th>Description</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Status</th>
            <th>Value</th>
            <th>Acquired</th>
            <th>Updated</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $stmt = $conn->query("SELECT a.*, c.category_name FROM assets a JOIN categories c ON a.category = c.id");
          while ($row = $stmt->fetch_assoc()):
          ?>
          <tr>
            <td><img src="<?= $row['qr_code'] ?>" width="50" /></td>
            <td><?= htmlspecialchars($row['asset_name']) ?></td>
            <td><?= htmlspecialchars($row['category_name']) ?></td>
            <td><?= htmlspecialchars($row['description']) ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= $row['unit'] ?></td>
            <td>
              <?php
              $status_class = match($row['status']) {
                'active' => 'success',
                'borrowed' => 'warning',
                default => 'secondary',
              };
              if ($row['red_tagged']) $status_class = 'danger';
              ?>
              <span class="badge bg-<?= $status_class ?> status-badge">
                <?= $row['red_tagged'] ? 'Red-Tagged' : ucfirst($row['status']) ?>
              </span>
            </td>
            <td>&#8369; <?= number_format($row['value'], 2) ?></td>
            <td><?= $row['acquisition_date'] ?></td>
            <td><?= $row['last_updated'] ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
