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
    $stmt = $conn->prepare("SELECT office_id FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($office_id);
    if ($stmt->fetch()) {
        $_SESSION['office_id'] = $office_id;
    }
    $stmt->close();
}

$office_id = $_SESSION['office_id'];

// Fetch full name
$user_name = '';
$stmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($fullname);
$stmt->fetch();
$stmt->close();

// ✅ Fetch Most Consumed Items (office-based)
$consumedData = ["labels" => [], "data" => []];
$stmt = $conn->prepare("
    SELECT description, SUM(quantity) AS total_consumed
    FROM ris_items
    WHERE id = ?
    GROUP BY description
    ORDER BY total_consumed DESC
    LIMIT 10
");
$stmt->bind_param("i", $office_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $consumedData['labels'][] = $row['description'];
    $consumedData['data'][]   = (int)$row['total_consumed'];
}
$stmt->close();

// ✅ Fetch Most Borrowed Items (office-based)
$borrowedData = ["labels" => [], "datasets" => []];

// Get distinct items borrowed
$itemRes = $conn->prepare("
    SELECT description, SUM(quantity) AS total_borrowed
    FROM assets
    WHERE status = 'borrowed' AND office_id = ?
    GROUP BY description
    ORDER BY total_borrowed DESC
    LIMIT 10
");
$itemRes->bind_param("i", $office_id);
$itemRes->execute();
$itemResult = $itemRes->get_result();
$borrowedLabels = [];
$borrowedQty = [];
while ($row = $itemResult->fetch_assoc()) {
    $borrowedLabels[] = $row['description'];
    $borrowedQty[] = (int)$row['total_borrowed'];
}
$itemRes->close();
$borrowedData['labels'] = $borrowedLabels;
$borrowedData['datasets'][] = [
    "label" => "Borrowed Quantity",
    "data" => $borrowedQty,
    "borderColor" => 'rgba(255, 99, 132, 1)',
    "backgroundColor" => 'rgba(255, 99, 132, 0.2)',
    "fill" => true,
    "tension" => 0.3
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Inventory Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
<link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
<?php include 'includes/sidebar.php' ?>
<div class="main">
<?php include 'includes/topbar.php' ?>

<div class="container-fluid mt-4">
  <div class="row">
    <!-- Assets Summary -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Assets Summary</h5>
        </div>
        <div class="card-body">
          <?php
          $total = $active = $borrowed = $red_tagged = 0;
          $stmt = $conn->prepare("SELECT status, red_tagged FROM assets WHERE type = 'asset' AND quantity > 0 AND office_id = ?");
          $stmt->bind_param("i", $office_id);
          $stmt->execute();
          $result = $stmt->get_result();
          while ($r = $result->fetch_assoc()) {
              $total++;
              if ($r['status'] === 'available') $active++;
              if ($r['status'] === 'borrowed') $borrowed++;
              if ($r['red_tagged']) $red_tagged++;
          }
          $stmt->close();
          ?>
          <div class="row">
            <div class="col-sm-6 mb-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div><h6>Total</h6><h4><?= $total ?></h4></div>
                  <i class="bi bi-box text-primary fs-2"></i>
                </div>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div><h6>Available</h6><h4><?= $active ?></h4></div>
                  <i class="bi bi-check-circle text-info fs-2"></i>
                </div>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div><h6>Borrowed</h6><h4><?= $borrowed ?></h4></div>
                  <i class="bi bi-arrow-left-right text-info fs-2"></i>
                </div>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div><h6>Red-Tagged</h6><h4><?= $red_tagged ?></h4></div>
                  <i class="bi bi-exclamation-triangle text-primary fs-2"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Consumables Summary -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0"><i class="bi bi-droplet-half me-2"></i>Consumables Summary</h5>
        </div>
        <div class="card-body">
          <?php
          $ctotal = $cactive = $clow_stock = $cunavailable = 0;
          $threshold = 5;
          $stmt = $conn->prepare("SELECT status, quantity FROM assets WHERE type = 'consumable' AND office_id = ?");
          $stmt->bind_param("i", $office_id);
          $stmt->execute();
          $result = $stmt->get_result();
          while ($r = $result->fetch_assoc()) {
              $ctotal++;
              if ($r['status'] === 'available') $cactive++;
              if ($r['status'] === 'unavailable') $cunavailable++;
              if ((int)$r['quantity'] <= $threshold) $clow_stock++;
          }
          $stmt->close();
          ?>
          <div class="row">
            <div class="col-sm-6 mb-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div><h6>Total</h6><h4><?= $ctotal ?></h4></div>
                  <i class="bi bi-droplet text-primary fs-2"></i>
                </div>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div><h6>Available</h6><h4><?= $cactive ?></h4></div>
                  <i class="bi bi-check-circle text-info fs-2"></i>
                </div>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div><h6>Low Stock (&le; <?= $threshold ?>)</h6><h4><?= $clow_stock ?></h4></div>
                  <i class="bi bi-exclamation-circle text-info fs-2"></i>
                </div>
              </div>
            </div>
            <div class="col-sm-6 mb-3">
              <div class="card shadow-sm h-100">
                <div class="card-body d-flex justify-content-between align-items-center">
                  <div><h6>Unavailable</h6><h4><?= $cunavailable ?></h4></div>
                  <i class="bi bi-slash-circle text-primary fs-2"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Section -->
<div class="container-fluid mt-1">
  <div class="row">
    <!-- Most Consumed Items Chart -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0"><i class="bi bi-bar-chart-line me-2"></i>Most Consumed Items</h5>
        </div>
        <div class="card-body">
          <canvas id="consumedChart" height="200"></canvas>
        </div>
      </div>
    </div>

    <!-- Most Borrowed Items Chart -->
    <div class="col-md-6 mb-1">
      <div class="card shadow-sm">
        <div class="card-header">
          <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Most Borrowed Items</h5>
        </div>
        <div class="card-body">
          <canvas id="borrowedChart" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="js/dashboard.js"></script>

<script>
  // Consumed Items Chart
  const consumedData = <?= json_encode($consumedData, JSON_NUMERIC_CHECK); ?>;
  const consumedCtx = document.getElementById('consumedChart').getContext('2d');
  new Chart(consumedCtx, {
    type: 'bar',
    data: {
      labels: consumedData.labels,
      datasets: [{
        label: 'Quantity Consumed',
        data: consumedData.data,
        backgroundColor: 'rgba(75, 192, 192, 0.6)',
        borderColor: 'rgba(75, 192, 192, 1)',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: { y: { beginAtZero: true } }
    }
  });

  // Borrowed Items Chart
  const borrowedData = <?= json_encode($borrowedData, JSON_NUMERIC_CHECK); ?>;
  const borrowedCtx = document.getElementById('borrowedChart').getContext('2d');
  new Chart(borrowedCtx, {
    type: 'line',
    data: borrowedData,
    options: {
      responsive: true,
      plugins: { legend: { position: 'top' } },
      scales: { y: { beginAtZero: true } }
    }
  });
</script>
</body>
</html>
