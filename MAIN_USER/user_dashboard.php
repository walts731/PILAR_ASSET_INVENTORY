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
      background-color: #eaf4fc;
      margin: 0;
    }

    .sidebar {
      height: 100vh;
      background-color: #2196f3;
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
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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

    .table th,
    .table td {
      vertical-align: middle;
    }

    .table-hover tbody tr:hover {
      background-color: #f1f9ff;
    }
  </style>
</head>

<body>

  <div class="sidebar">
    <h5>
      <img src="../img/logo.jpg" alt="Logo" style="width: 30px; height: 30px; margin-right: 10px;" />
      Pilar Inventory
    </h5>
    <hr />
    <a href="user_dashboard"><i class="bi bi-box-seam"></i> Inventory</a>
    <a href="#"><i class="bi bi-bar-chart-line"></i> Reports</a>
    <a href="#"><i class="bi bi-gear"></i> Settings</a>
    <a href="../logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
  </div>


  <div class="main">
    <div class="topbar d-flex justify-content-between align-items-center p-2">
  <h3 class="m-0">Inventory Dashboard</h3>

  <!-- Right-side Icons + DateTime -->
  <div class="d-flex align-items-center gap-3">

    <!-- Date and Time -->
    <div id="datetime" class="text-end text-dark small fw-semibold"></div>

    <!-- Scan QR Icon -->
    <a href="scan_qr.php" class="text-dark text-decoration-none" title="Scan QR">
      <i class="bi bi-qr-code-scan" style="font-size: 1.8rem;"></i>
    </a>

    <!-- Profile Menu -->
    <div class="dropdown">
      <a href="#" class="d-flex align-items-center text-dark text-decoration-none" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-person-circle" style="font-size: 1.8rem;"></i>
      </a>
      <ul class="dropdown-menu dropdown-menu-end text-center" aria-labelledby="profileDropdown" style="min-width: 200px;">
        <li class="dropdown-header fw-bold text-dark"><?php echo htmlspecialchars($fullname); ?></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item d-flex align-items-center" href="view_profile.php"><i class="bi bi-person me-2"></i> Profile</a></li>
        <li><a class="dropdown-item d-flex align-items-center" href="manage_password.php"><i class="bi bi-key me-2"></i> Manage Password</a></li>
        <li><hr class="dropdown-divider"></li>
        <li><a class="dropdown-item d-flex align-items-center text-danger" href="../logout.php"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
      </ul>
    </div>
  </div>
</div>



    <!-- Tab Navigation -->
    <ul class="nav nav-tabs mb-4" id="inventoryTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="assets-tab" data-bs-toggle="tab" data-bs-target="#assets" type="button" role="tab">Assets</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="consumables-tab" data-bs-toggle="tab" data-bs-target="#consumables" type="button" role="tab">Consumables</button>
      </li>
    </ul>

    <div class="tab-content" id="inventoryTabsContent">
      <!-- Assets Tab -->
      <div class="tab-pane fade show active" id="assets" role="tabpanel">
        <?php
        $total = $active = $borrowed = $red_tagged = 0;
        $res = $conn->query("SELECT status, red_tagged FROM assets WHERE type = 'asset'");
        while ($r = $res->fetch_assoc()) {
          $total++;
          if ($r['status'] === 'available') $active++;
          if ($r['status'] === 'borrowed') $borrowed++;
          if ($r['red_tagged']) $red_tagged++;
        }
        ?>
        <div class="row mb-4">
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Total</h5>
                <h3><?= $total ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Available</h5>
                <h3><?= $active ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Borrowed</h5>
                <h3><?= $borrowed ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Red-Tagged</h5>
                <h3><?= $red_tagged ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm mb-4">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Asset List</h5>
            <a href="generate_report.php?type=asset" class="btn btn-outline-secondary btn-sm">
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
                  <th>Qty</th>
                  <th>Unit</th>
                  <th>Status</th>
                  <th>Value</th>
                  <th>Acquired</th>
                  <th>Updated</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $stmt = $conn->query("SELECT a.*, c.category_name FROM assets a JOIN categories c ON a.category = c.id WHERE a.type = 'asset'");
                while ($row = $stmt->fetch_assoc()):
                ?>
                  <tr>
                    <td><img src="../img/<?= $row['qr_code'] ?>" width="50"></td>
                    <td><?= htmlspecialchars($row['asset_name']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= $row['unit'] ?></td>
                    <td>
                      <?php
                      $status_class = match ($row['status']) {
                        'available' => 'success',
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

      <!-- Consumables Tab -->
      <div class="tab-pane fade" id="consumables" role="tabpanel">
        <?php
        $ctotal = $cactive = $cred_tagged = 0;
        $cres = $conn->query("SELECT status, red_tagged FROM assets WHERE type = 'consumable'");
        while ($r = $cres->fetch_assoc()) {
          $ctotal++;
          if ($r['status'] === 'available') $cactive++;
          if ($r['red_tagged']) $cred_tagged++;
        }
        ?>
        <div class="row mb-4">
          <div class="col-md-4">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Total</h5>
                <h3><?= $ctotal ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Available</h5>
                <h3><?= $cactive ?></h3>
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="card shadow-sm">
              <div class="card-body">
                <h5>Red-Tagged</h5>
                <h3><?= $cred_tagged ?></h3>
              </div>
            </div>
          </div>
        </div>

        <div class="card shadow-sm">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Consumable List</h5>
            <a href="generate_report.php?type=consumable" class="btn btn-outline-secondary btn-sm">
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
                  <th>Qty</th>
                  <th>Unit</th>
                  <th>Status</th>
                  <th>Value</th>
                  <th>Acquired</th>
                  <th>Updated</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $stmt = $conn->query("SELECT a.*, c.category_name FROM assets a JOIN categories c ON a.category = c.id WHERE a.type = 'consumable'");
                while ($row = $stmt->fetch_assoc()):
                ?>
                  <tr>
                    <td><img src="<?= $row['qr_code'] ?>" width="50"></td>
                    <td><?= htmlspecialchars($row['asset_name']) ?></td>
                    <td><?= htmlspecialchars($row['category_name']) ?></td>
                    <td><?= htmlspecialchars($row['description']) ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td><?= $row['unit'] ?></td>
                    <td>
                      <?php
                      $status_class = $row['red_tagged'] ? 'danger' : 'success';
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
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  function updateDateTime() {
    const now = new Date();
    const formatted = now.toLocaleString('en-US', {
      weekday: 'short',
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: 'numeric',
      minute: '2-digit',
      second: '2-digit',
      hour12: true
    });
    document.getElementById('datetime').textContent = formatted;
  }
  setInterval(updateDateTime, 1000);
  updateDateTime(); // Initial call
</script>

</body>

</html>