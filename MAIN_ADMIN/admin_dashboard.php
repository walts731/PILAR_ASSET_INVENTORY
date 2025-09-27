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

// ✅ Fetch Most Consumed Items
$consumedData = ["labels" => [], "data" => []];
$res = $conn->query("
    SELECT description, SUM(quantity) AS total_consumed
    FROM ris_items
    GROUP BY description
    ORDER BY total_consumed DESC
    LIMIT 10
");
while ($row = $res->fetch_assoc()) {
    $consumedData['labels'][] = $row['description'];
    $consumedData['data'][]   = (int)$row['total_consumed'];
}

// ✅ Fetch 5 Most Recent Activities with fullname
$auditRows = [];
$activityError = '';
try {
  $auditSql = "
    SELECT al.id, al.action, al.module, al.details, al.created_at, u.fullname
    FROM audit_logs al
    LEFT JOIN users u ON u.id = al.user_id
    ORDER BY al.created_at DESC
    LIMIT 5
  ";
  if ($ares = $conn->query($auditSql)) {
    while ($a = $ares->fetch_assoc()) { $auditRows[] = $a; }
    $ares->close();
  } else {
    $activityError = 'Recent activity is unavailable.';
  }
} catch (Throwable $e) {
  $activityError = 'Recent activity is unavailable.';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Inventory Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/dashboard.css" />
  <style>
    body {
      background-color: #f8f9fb;
      font-family: 'Poppins', system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
    }
    .card {
      border-radius: 12px;
    }
    .card-header {
      background-color: #fff;
    }
    .stat-icon {
      font-size: 1.75rem;
      color: #0d6efd;
    }
    .text-muted-small {
      font-size: .85rem;
      color: #6c757d;
    }
    .list-compact .list-group-item {
      padding: 0.75rem 1rem;
    }
    .table thead th {
      font-weight: 600;
      color: #495057;
    }
    .table td,
    .table th {
      vertical-align: middle;
    }
    .text-truncate-ellipsis {
      max-width: 260px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    @media (max-width: 767.98px) {
      .card-header h5 {
        font-size: 1rem;
      }
      .text-truncate-ellipsis {
        max-width: 140px;
      }
    }
  </style>
</head>

<body>
  <?php include 'includes/sidebar.php' ?>
  <div class="main">
    <?php include 'includes/topbar.php' ?>

    <div class="container-fluid mt-4">
      <div class="row">
        <!-- Assets Summary -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="bi bi-box-seam me-2"></i>Assets Summary</h5>
            </div>
            <div class="card-body">
              <?php
              $total = $active = $borrowed = $red_tagged = 0;
              $res = $conn->query("SELECT status, red_tagged FROM assets WHERE type = 'asset' AND quantity > 0");
              while ($r = $res->fetch_assoc()) {
                $total++;
                if ($r['status'] === 'available') $active++;
                if ($r['status'] === 'borrowed') $borrowed++;
                if ($r['red_tagged']) $red_tagged++;
              }
              ?>
              <div class="row">
                <div class="col-sm-6 mb-3">
                  <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <div>
                        <h6>Total</h6>
                        <h4><?= $total ?></h4>
                      </div>
                      <i class="bi bi-box stat-icon"></i>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 mb-3">
                  <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <div>
                        <h6>Available</h6>
                        <h4><?= $active ?></h4>
                      </div>
                      <i class="bi bi-check-circle stat-icon"></i>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 mb-3">
                  <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <div>
                        <h6>Borrowed</h6>
                        <h4><?= $borrowed ?></h4>
                      </div>
                      <i class="bi bi-arrow-left-right stat-icon"></i>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 mb-3">
                  <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <div>
                        <h6>Red-Tagged</h6>
                        <h4><?= $red_tagged ?></h4>
                      </div>
                      <i class="bi bi-exclamation-triangle stat-icon"></i>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Consumables Summary -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="bi bi-droplet-half me-2"></i>Consumables Summary</h5>
            </div>
            <div class="card-body">
              <?php
              $ctotal = $cactive = $clow_stock = $cunavailable = 0;
              $threshold = 5;
              $cres = $conn->query("SELECT status, quantity FROM assets WHERE type = 'consumable'");
              while ($r = $cres->fetch_assoc()) {
                $ctotal++;
                if ($r['status'] === 'available') $cactive++;
                if ($r['status'] === 'unavailable') $cunavailable++;
                if ((int)$r['quantity'] <= $threshold) $clow_stock++;
              }
              ?>
              <div class="row">
                <div class="col-sm-6 mb-3">
                  <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <div>
                        <h6>Total</h6>
                        <h4><?= $ctotal ?></h4>
                      </div>
                      <i class="bi bi-droplet stat-icon"></i>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 mb-3">
                  <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <div>
                        <h6>Available</h6>
                        <h4><?= $cactive ?></h4>
                      </div>
                      <i class="bi bi-check-circle stat-icon"></i>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 mb-3">
                  <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <div>
                        <h6>Low Stock (&le; <?= $threshold ?>)</h6>
                        <h4><?= $clow_stock ?></h4>
                      </div>
                      <i class="bi bi-exclamation-circle stat-icon"></i>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6 mb-3">
                  <div class="card shadow-sm h-100 border-0">
                    <div class="card-body d-flex justify-content-between align-items-center">
                      <div>
                        <h6>Unavailable</h6>
                        <h4><?= $cunavailable ?></h4>
                      </div>
                      <i class="bi bi-slash-circle stat-icon"></i>
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
        <!-- Most Consumed Items (Dynamic Bar Chart) -->
        <div class="col-md-6 mb-4">
          <div class="card shadow-sm border-0 overflow-hidden">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
              <div>
                <h5 class="mb-1 fw-semibold d-flex align-items-center">
                  <i class="bi bi-bar-chart-line me-2 text-primary"></i>Most Consumed Items
                </h5>
                <small class="text-muted">Top requested consumables by total quantity issued</small>
              </div>
              <div class="d-flex align-items-center gap-2">
                <label for="consumedTopN" class="form-label mb-0 small text-nowrap">Top</label>
                <select id="consumedTopN" class="form-select form-select-sm" style="width: 80px;">
                  <option value="5">5</option>
                  <option value="10" selected>10</option>
                  <option value="15">15</option>
                </select>
              </div>
            </div>
            <div class="card-body pt-2">
              <div class="position-relative" style="min-height: 260px;">
                <canvas id="consumedChart" height="220"></canvas>
              </div>
              <div class="mt-2 d-flex align-items-center">
                <span class="badge rounded-pill bg-light text-secondary border">No office data shown</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Most Borrowed Items (Line Chart - still sample) -->
        <div class="col-md-6 mb-1">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3">
              <h5 class="mb-0"><i class="bi bi-graph-up me-2"></i>Most Borrowed Items</h5>
            </div>
            <div class="card-body">
              <canvas id="borrowedChart" height="200"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Activity + Fuel Inventory Section -->
    <div class="container-fluid mt-1">
      <div class="row">
        <!-- Recent Activity (Left) -->
        <div class="col-lg-8 mb-4">
          <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
              <div>
                <h5 class="mb-1 fw-semibold d-flex align-items-center">
                  <i class="bi bi-activity me-2 text-primary"></i>Recent Activity
                </h5>
                <small class="text-muted">Latest actions across the system</small>
              </div>
            </div>
            <div class="card-body pt-2">
              <?php if (!empty($activityError) && empty($auditRows)): ?>
                <div class="alert alert-light border text-muted mb-0"><i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($activityError) ?></div>
              <?php elseif (empty($auditRows)): ?>
                <div class="alert alert-light border text-muted mb-0"><i class="bi bi-info-circle me-2"></i>No recent activity.</div>
              <?php else: ?>
                <ul class="list-group list-group-flush">
                  <?php foreach ($auditRows as $row): ?>
                    <?php
                      $fullname = $row['fullname'] ?? 'Unknown User';
                      $actionTxt = strtoupper((string)($row['action'] ?? ''));
                      $module = $row['module'] ?? '';
                      $details = $row['details'] ?? '';
                      $dt = !empty($row['created_at']) ? date('M d, Y h:i A', strtotime($row['created_at'])) : '';
                      $badge = 'bg-secondary';
                      if ($actionTxt === 'LOGIN') $badge = 'bg-success';
                      elseif ($actionTxt === 'LOGOUT') $badge = 'bg-secondary';
                      elseif ($actionTxt === 'CREATE') $badge = 'bg-primary';
                      elseif ($actionTxt === 'UPDATE') $badge = 'bg-info text-dark';
                      elseif ($actionTxt === 'DELETE') $badge = 'bg-danger';
                    ?>
                    <li class="list-group-item d-flex align-items-start justify-content-between">
                      <div class="me-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                          <span class="fw-semibold"><?= htmlspecialchars($fullname) ?></span>
                          <span class="badge <?= $badge ?>"><?= htmlspecialchars($actionTxt) ?></span>
                          <span class="badge bg-light text-dark border"><?= htmlspecialchars($module) ?></span>
                        </div>
                        <div class="text-muted-small text-truncate-ellipsis" title="<?= htmlspecialchars($details) ?>"><?= htmlspecialchars($details) ?></div>
                        <div class="text-muted-small"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($dt) ?></div>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
              </div>
          </div>
        </div>

        <!-- Fuel Inventory (Right) -->
        <div class="col-lg-4 mb-4">
          <div class="card shadow-sm border-0 overflow-hidden h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
              <div>
                <h5 class="mb-1 fw-semibold d-flex align-items-center">
                  <i class="bi bi-fuel-pump me-2 text-primary"></i>Fuel Inventory
                </h5>
                <small class="text-muted">Current stock by fuel type</small>
              </div>
              <a href="fuel_inventory.php" class="btn btn-sm btn-outline-primary">Manage</a>
            </div>
            <div class="card-body pt-2">
              <div class="position-relative" style="min-height: 260px;">
                <canvas id="fuelStockChart" height="220"></canvas>
              </div>
              <div id="fuelStockNote" class="mt-2 text-muted-small"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/dashboard.js"></script>

    <script>
      // ✅ Dynamic Data for Most Consumed Items
      const consumedData = <?= json_encode($consumedData, JSON_NUMERIC_CHECK); ?>;

      // Build a processed view with Top N selection (client-side slice)
      const topNSelect = document.getElementById('consumedTopN');
      const consumedCtx = document.getElementById('consumedChart').getContext('2d');

      // Gradient fill for a professional look
      const gradient = consumedCtx.createLinearGradient(0, 0, 0, 300);
      gradient.addColorStop(0, 'rgba(13, 110, 253, 0.9)');
      gradient.addColorStop(1, 'rgba(13, 110, 253, 0.2)');

      function buildTopNData(n) {
        const labels = consumedData.labels.slice(0, n);
        const data = consumedData.data.slice(0, n);
        return { labels, data };
      }

      function formatNumber(x) {
        try { return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ','); } catch { return x; }
      }

      let chartInstance;
      function renderConsumedChart(n) {
        const view = buildTopNData(Number(n));
        const config = {
          type: 'bar',
          data: {
            labels: view.labels,
            datasets: [{
              label: 'Quantity Issued',
              data: view.data,
              backgroundColor: gradient,
              borderColor: 'rgba(13, 110, 253, 1)',
              borderWidth: 1,
              borderRadius: 8,
              maxBarThickness: 32,
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                backgroundColor: 'rgba(33, 37, 41, 0.9)',
                titleColor: '#fff',
                bodyColor: '#fff',
                padding: 12,
                cornerRadius: 8,
                displayColors: false,
                callbacks: {
                  title: (items) => items[0]?.label || '',
                  label: (ctx) => `Total: ${formatNumber(ctx.parsed.y)}`,
                }
              }
            },
            scales: {
              x: {
                ticks: {
                  color: '#6c757d',
                  font: { size: 12 },
                  callback: function(value, index) {
                    const label = this.getLabelForValue(index);
                    return label.length > 18 ? label.slice(0, 18) + '…' : label;
                  }
                },
                grid: { display: false }
              },
              y: {
                beginAtZero: true,
                ticks: {
                  color: '#6c757d',
                  font: { size: 12 },
                  callback: (v) => formatNumber(v)
                },
                grid: { color: 'rgba(0,0,0,0.05)' }
              }
            },
            animation: {
              duration: 900,
              easing: 'easeOutQuart'
            }
          }
        };

        if (chartInstance) {
          chartInstance.destroy();
        }
        chartInstance = new Chart(consumedCtx, config);
      }

      // Initial render and event binding
      renderConsumedChart(topNSelect.value);
      topNSelect.addEventListener('change', (e) => renderConsumedChart(e.target.value));

      // DataTables init for Recent Activity
      $(function() {
        if (!$('#recentActivityTable').length) return;

        const table = $('#recentActivityTable').DataTable({
          pageLength: 10,
          lengthChange: false,
          order: [[0, 'desc']],
          columnDefs: [
            { targets: [5, 9, 11], orderable: false }, // details, user agent, actions not sortable
          ]
        });

        // Global search
        $('#actSearch').on('keyup', function() {
          table.search(this.value).draw();
        });

        // Action filter (exact match)
        $('#actFilterAction').on('change', function() {
          const val = this.value;
          if (!val) {
            table.column(3).search('').draw();
          } else {
            table.column(3).search('^' + val + '$', true, false).draw();
          }
        });

        // Module filter (contains)
        $('#actFilterModule').on('keyup change', function() {
          table.column(4).search(this.value).draw();
        });

        // Date range filter using data-ts (seconds) in .act-date span
        $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
          if (settings.nTable !== document.getElementById('recentActivityTable')) return true;
          const fromVal = $('#actFromDate').val();
          const toVal = $('#actToDate').val();
          if (!fromVal && !toVal) return true;

          const rowNode = table.row(dataIndex).node();
          const tsSec = parseInt($(rowNode).find('.act-date').data('ts')) || 0;
          if (!tsSec) return false;
          const fromTs = fromVal ? Math.floor(new Date(fromVal + 'T00:00:00').getTime() / 1000) : null;
          const toTs = toVal ? Math.floor(new Date(toVal + 'T23:59:59').getTime() / 1000) : null;

          if (fromTs !== null && tsSec < fromTs) return false;
          if (toTs !== null && tsSec > toTs) return false;
          return true;
        });

        $('#actFromDate, #actToDate').on('change', function() {
          table.draw();
        });

        // Reset filters
        $('#actResetFilters').on('click', function() {
          $('#actSearch').val('');
          $('#actFilterAction').val('');
          $('#actFilterModule').val('');
          $('#actFromDate').val('');
          $('#actToDate').val('');
          table.search('');
          table.columns().search('');
          table.draw();
        });

        // View details modal
        $(document).on('click', '.btn-view-details', function() {
          const md = new bootstrap.Modal(document.getElementById('activityDetailsModal'));
          $('#mdUsername').val($(this).data('username') || '');
          $('#mdAction').val($(this).data('action') || '');
          $('#mdModule').val($(this).data('module') || '');
          $('#mdDetails').val($(this).data('details') || '');
          $('#mdTable').val($(this).data('table') || '');
          $('#mdAffected').val($(this).data('affected') || '');
          $('#mdIp').val($(this).data('ip') || '');
          $('#mdAgent').val($(this).data('agent') || '');
          $('#mdDate').val($(this).data('date') || '');
          md.show();
        });
      });

      // ✅ Sample Data for Most Borrowed Items (static for now)
      const borrowedCtx = document.getElementById('borrowedChart').getContext('2d');
      // Create subtle gradients for better aesthetics
      const grad1 = borrowedCtx.createLinearGradient(0, 0, 0, 200);
      grad1.addColorStop(0, 'rgba(13, 110, 253, 0.35)');
      grad1.addColorStop(1, 'rgba(13, 110, 253, 0.05)');
      const grad2 = borrowedCtx.createLinearGradient(0, 0, 0, 200);
      grad2.addColorStop(0, 'rgba(25, 135, 84, 0.35)');
      grad2.addColorStop(1, 'rgba(25, 135, 84, 0.05)');
      const grad3 = borrowedCtx.createLinearGradient(0, 0, 0, 200);
      grad3.addColorStop(0, 'rgba(255, 193, 7, 0.35)');
      grad3.addColorStop(1, 'rgba(255, 193, 7, 0.05)');

      new Chart(borrowedCtx, {
        type: 'line',
        data: {
          labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
          datasets: [
            {
              label: 'Chairs',
              data: [5, 10, 8, 12],
              borderColor: 'rgba(13, 110, 253, 1)',
              backgroundColor: grad1,
              pointBackgroundColor: '#fff',
              pointBorderColor: 'rgba(13, 110, 253, 1)',
              pointRadius: 3,
              fill: true,
              tension: 0.35
            },
            {
              label: 'Projectors',
              data: [2, 6, 4, 7],
              borderColor: 'rgba(25, 135, 84, 1)',
              backgroundColor: grad2,
              pointBackgroundColor: '#fff',
              pointBorderColor: 'rgba(25, 135, 84, 1)',
              pointRadius: 3,
              fill: true,
              tension: 0.35
            },
            {
              label: 'Laptops',
              data: [3, 4, 6, 9],
              borderColor: 'rgba(255, 193, 7, 1)',
              backgroundColor: grad3,
              pointBackgroundColor: '#fff',
              pointBorderColor: 'rgba(255, 193, 7, 1)',
              pointRadius: 3,
              fill: true,
              tension: 0.35
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              backgroundColor: 'rgba(33, 37, 41, 0.9)',
              titleColor: '#fff',
              bodyColor: '#fff',
              padding: 10,
              cornerRadius: 8,
              displayColors: false
            }
          },
          scales: {
            x: {
              ticks: { color: '#6c757d', font: { size: 12 } },
              grid: { display: false }
            },
            y: {
              beginAtZero: true,
              ticks: { color: '#6c757d', font: { size: 12 } },
              grid: { color: 'rgba(0,0,0,0.05)' }
            }
          },
          animation: { duration: 800, easing: 'easeOutQuart' }
        }
      });

      // Fuel Inventory Chart (Doughnut)
      (function() {
        const canvas = document.getElementById('fuelStockChart');
        if (!canvas) return; // Only on this page
        const ctx = canvas.getContext('2d');
        const noteEl = document.getElementById('fuelStockNote');

        function setNote(msg, isError=false) {
          if (noteEl) {
            noteEl.className = 'mt-2 ' + (isError ? 'text-danger' : 'text-muted-small');
            noteEl.innerHTML = msg || '';
          }
        }

        fetch('list_fuel_stock.php', { credentials: 'same-origin' })
          .then(async (res) => {
            if (!res.ok) {
              let err = 'Unable to load fuel stock';
              try { const j = await res.json(); if (j.error) err = j.error; } catch { /* ignore */ }
              throw new Error(err);
            }
            return res.json();
          })
          .then((data) => {
            const rows = (data && data.stock) ? data.stock : [];
            if (!rows.length) {
              setNote('<span class="badge rounded-pill bg-light text-secondary border">No fuel types configured</span>');
              return;
            }

            const labels = rows.map(r => r.name);
            const values = rows.map(r => parseFloat(r.quantity || '0'));
            const total = values.reduce((a,b)=>a+b,0);

            // Nice color palette
            const palette = [
              '#0d6efd','#6f42c1','#20c997','#ffc107','#dc3545',
              '#198754','#fd7e14','#0dcaf0','#6610f2','#6c757d'
            ];
            const bg = labels.map((_,i)=> palette[i % palette.length]);

            new Chart(ctx, {
              type: 'doughnut',
              data: {
                labels,
                datasets: [{
                  data: values,
                  backgroundColor: bg,
                  borderWidth: 0
                }]
              },
              options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                  legend: { position: 'bottom', labels: { boxWidth: 12 } },
                  tooltip: {
                    callbacks: {
                      label: (ctx) => {
                        const v = ctx.parsed;
                        const pct = total ? ((v/total)*100).toFixed(1) : 0;
                        return `${ctx.label}: ${v.toLocaleString()} L (${pct}%)`;
                      }
                    }
                  }
                },
                cutout: '60%'
              }
            });

            setNote(`<span class="badge rounded-pill bg-light text-secondary border">Total: ${total.toLocaleString()} L</span>`);
          })
          .catch((err) => {
            console.error(err);
            setNote('<i class="bi bi-exclamation-triangle me-1"></i>' + (err && err.message ? err.message : 'Unable to load fuel stock'), true);
          });
      })();
    </script>
</body>
</html>
