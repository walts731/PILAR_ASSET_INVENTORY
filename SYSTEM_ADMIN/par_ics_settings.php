<?php
require_once '../connect.php';
session_start();

// Auth check
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

// Ensure table exists (simple bootstrap)
$conn->query("CREATE TABLE IF NOT EXISTS form_thresholds (
  id INT PRIMARY KEY AUTO_INCREMENT,
  ics_max DECIMAL(15,2) NOT NULL DEFAULT 50000.00,
  par_min DECIMAL(15,2) NOT NULL DEFAULT 50000.00,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Load current row or create default one if missing
$thr = ['ics_max' => 50000.00, 'par_min' => 50000.00];
$res = $conn->query('SELECT ics_max, par_min FROM form_thresholds ORDER BY id ASC LIMIT 1');
if ($res && $res->num_rows > 0) {
    $thr = $res->fetch_assoc();
} else {
    $conn->query("INSERT INTO form_thresholds (ics_max, par_min) VALUES (50000.00, 50000.00)");
}

$flash = null;
$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ics_max = isset($_POST['ics_max']) ? (float)$_POST['ics_max'] : 50000.00;
    $par_min = isset($_POST['par_min']) ? (float)$_POST['par_min'] : 50000.00;

    if ($ics_max < 0 || $par_min < 0) {
        $error = 'Values must be non-negative.';
    } else {
        // Update the single row
        $stmt = $conn->prepare('UPDATE form_thresholds SET ics_max = ?, par_min = ? ORDER BY id ASC LIMIT 1');
        if ($stmt === false) {
            // Fallback: in case ORDER BY/LIMIT not allowed in UPDATE by server, update first row id
            $res2 = $conn->query('SELECT id FROM form_thresholds ORDER BY id ASC LIMIT 1');
            $row2 = $res2 ? $res2->fetch_assoc() : null;
            if ($row2) {
                $stmt = $conn->prepare('UPDATE form_thresholds SET ics_max = ?, par_min = ? WHERE id = ?');
                $id = (int)$row2['id'];
                $stmt->bind_param('ddi', $ics_max, $par_min, $id);
                $stmt->execute();
                $stmt->close();
            } else {
                $stmtIns = $conn->prepare('INSERT INTO form_thresholds (ics_max, par_min) VALUES (?, ?)');
                $stmtIns->bind_param('dd', $ics_max, $par_min);
                $stmtIns->execute();
                $stmtIns->close();
            }
        } else {
            $stmt->bind_param('dd', $ics_max, $par_min);
            $stmt->execute();
            $stmt->close();
        }
        $flash = 'Thresholds saved successfully.';
        $thr['ics_max'] = number_format($ics_max, 2, '.', '');
        $thr['par_min'] = number_format($par_min, 2, '.', '');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>PAR/ICS Threshold Settings</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
  <?php include 'includes/topbar.php'; ?>

  <div class="container mt-4">
    <div class="card shadow-sm">
      <div class="card-header d-flex align-items-center">
        <h5 class="mb-0"><i class="bi bi-sliders me-2"></i>PAR/ICS Threshold Settings</h5>
      </div>
      <div class="card-body">
        <?php if ($flash): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($flash) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>
        <?php if ($error): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
          </div>
        <?php endif; ?>

        <form method="POST" class="row g-3">
          <div class="col-md-6">
            <label class="form-label">ICS Maximum Unit Cost (≤)</label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" step="0.01" min="0" name="ics_max" class="form-control" value="<?= htmlspecialchars($thr['ics_max']) ?>" required />
            </div>
            <div class="form-text">Items on ICS must have unit cost less than or equal to this amount.</div>
          </div>
          <div class="col-md-6">
            <label class="form-label">PAR Minimum Unit Cost (≥)</label>
            <div class="input-group">
              <span class="input-group-text">₱</span>
              <input type="number" step="0.01" min="0" name="par_min" class="form-control" value="<?= htmlspecialchars($thr['par_min']) ?>" required />
            </div>
            <div class="form-text">Items on PAR must have unit cost greater than or equal to this amount.</div>
          </div>
          <div class="col-12 mt-2">
            <button class="btn btn-primary"><i class="bi bi-save me-1"></i>Save Thresholds</button>
          </div>
        </form>
      </div>
    </div>

    <div class="alert alert-info mt-3" role="alert">
      <div class="d-flex align-items-start">
        <i class="bi bi-info-circle me-2"></i>
        <div>
          <strong>Enforcement:</strong>
          <div>• ICS: items with unit cost above the limit will be skipped with a notice.</div>
          <div>• PAR: items with unit cost below the minimum will be skipped with a notice.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</body>
</html>
