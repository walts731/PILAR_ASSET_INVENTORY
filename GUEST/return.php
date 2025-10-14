<?php
// return.php
// Return slip form for guests to return borrowed items

session_start();
require_once '../connect.php';
require_once '../includes/lifecycle_helper.php';
require_once '../includes/classes/Notification.php';

// Check if user is a guest
if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Get system settings for branding
$system = [
    'logo' => '../img/default-logo.png',
    'system_title' => 'Inventory System'
];

$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
    $system = $result->fetch_assoc();
}

function h($s){ return htmlspecialchars($s ?? ''); }

// Default values for return form
$defaults = [
    'name' => '',
    'date_returned' => date('Y-m-d'),
    'contact' => '',
    'barangay' => '',
    'condition' => 'good',
    'notes' => ''
];

// If form posted, read values
$data = array_merge($defaults, $_POST ?? []);

// Get submission_id from URL parameter
$submission_id = isset($_GET['submission_id']) ? intval($_GET['submission_id']) : 0;

// Check if submission_id is provided
if (!$submission_id) {
    header("Location: borrowing_history.php");
    exit();
}

// Fetch submission details if submission_id is provided
$submission = null;
$return_items = [];

if ($submission_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM borrow_form_submissions WHERE id = ? AND guest_id = ? AND status IN ('approved', 'borrowed')");
    $stmt->bind_param('is', $submission_id, $_SESSION['guest_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Submission not found or not authorized
        header("Location: borrowing_history.php");
        exit();
    }

    $submission = $result->fetch_assoc();
    $items = json_decode($submission['items'], true);

    // Convert borrow items to return items format
    if ($items && is_array($items)) {
        foreach ($items as $index => $item) {
            $return_items[] = [
                'asset_id' => $item['asset_id'] ?? '',
                'thing' => $item['thing'] ?? '',
                'inventory_tag' => $item['inventory_tag'] ?? '',
                'property_no' => $item['property_no'] ?? '',
                'category' => $item['category'] ?? '',
                'quantity' => $item['qty'] ?? '',
                'condition' => 'good',
                'notes' => ''
            ];
        }
    }
    $stmt->close();
} else {
    // No valid submission_id
    header("Location: borrowing_history.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    processReturnSubmission($conn);
}

// Function to process return submission
function processReturnSubmission($conn) {
    $submission_id = intval($_POST['submission_id'] ?? 0);
    $guest_name = trim($_POST['name'] ?? '');
    $date_returned = $_POST['date_returned'] ?? '';
    $contact = trim($_POST['contact'] ?? '');
    $barangay = trim($_POST['barangay'] ?? '');
    $condition = $_POST['condition'] ?? 'good';
    $notes = trim($_POST['notes'] ?? '');

    // Validate required fields
    $errors = [];
    if (empty($guest_name)) $errors[] = "Name is required";
    if (empty($date_returned)) $errors[] = "Date returned is required";
    if (empty($contact)) $errors[] = "Contact number is required";
    if (empty($barangay)) $errors[] = "Barangay is required";
    if (!$submission_id) $errors[] = "Invalid submission ID";

    if (!empty($errors)) {
        $_SESSION['return_errors'] = $errors;
        header("Location: return.php?submission_id=" . $submission_id);
        exit();
    }

    // Verify submission belongs to current guest and is returnable
    $stmt = $conn->prepare("SELECT id, items FROM borrow_form_submissions WHERE id = ? AND guest_id = ? AND status IN ('approved', 'borrowed')");
    $stmt->bind_param('is', $submission_id, $_SESSION['guest_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $_SESSION['return_errors'] = ["Return request not found or not authorized"];
        header("Location: return.php?submission_id=" . $submission_id);
        exit();
    }

    $submission = $result->fetch_assoc();
    $items = json_decode($submission['items'], true);
    $stmt->close();

    // Update submission status to 'returned'
    $update_stmt = $conn->prepare("UPDATE borrow_form_submissions SET status = 'returned', updated_at = NOW() WHERE id = ?");
    $update_stmt->bind_param('i', $submission_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Update asset statuses to 'serviceable'
    if ($items && is_array($items)) {
        $asset_ids = [];
        foreach ($items as $item) {
            if (isset($item['asset_id']) && !empty($item['asset_id'])) {
                $asset_ids[] = (int)$item['asset_id'];
            }
        }

        if (!empty($asset_ids)) {
            $placeholders = str_repeat('?,', count($asset_ids) - 1) . '?';
            $update_assets_sql = "UPDATE assets SET status = 'serviceable', last_updated = NOW() WHERE id IN ($placeholders)";
            $update_assets_stmt = $conn->prepare($update_assets_sql);

            if ($update_assets_stmt) {
                $types = str_repeat('i', count($asset_ids));
                $update_assets_stmt->bind_param($types, ...$asset_ids);
                $update_assets_stmt->execute();
                $update_assets_stmt->close();
            }

            // Log lifecycle events for returned assets
            $borrower_name = $_SESSION['guest_name'] ?? 'Unknown Guest';
            foreach ($asset_ids as $asset_id) {
                logLifecycleEvent(
                    $asset_id,
                    'RETURNED',
                    'borrow_form_submissions',
                    $submission_id,
                    null, // from_employee_id (guest returning)
                    null, // to_employee_id (returning to inventory)
                    null, // from_office_id
                    null, // to_office_id
                    "Asset returned by {$borrower_name} (Submission #{$submission_id})"
                );
            }

            // Send notification to MAIN_ADMIN users
            $notification = new Notification($conn);
            $title = "Asset Return Notification";
            $message = "Guest {$borrower_name} has returned assets. Submission #{$submission['submission_number']}";
            $notification->create(
                'asset_returned',
                $title,
                $message,
                'borrow_form_submissions',
                $submission_id,
                null, // Send to all admins
                7 // Expires in 7 days
            );
        }
    }

    // Add success message
    $_SESSION['return_success'] = [
        'message' => 'Items returned successfully!',
        'submission_number' => $submission['submission_number']
    ];

    // Redirect to borrowing history
    header("Location: borrowing_history.php");
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Return Slip - Fillable</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://code.jquery.com/jquery-3.7.0.min.js">
  <style>
    body{background:#f7f7f9}
    .slip-card{max-width:900px;margin:20px auto;padding:18px;background:#fff;border:1px solid #ddd}
    .seal{width:72px;height:72px;border-radius:50%;object-fit:cover;}
    .document-code{font-size:12px}
    .table-fixed td, .table-fixed th { vertical-align: middle; }
    @media print{
      .no-print{display:none}
      body{background:white}
      .slip-card{box-shadow:none;border:0}
    }

     :root {
            --primary-color: #0b5ed7;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }

        .guest-badge {
            background: linear-gradient(45deg, #28a745, #20c997);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .main-container {
            padding: 2rem 0;
        }

        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .stats-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .action-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }

        .scan-icon {
            background: linear-gradient(45deg, var(--primary-color), #0056b3);
            color: white;
        }

        .browse-icon {
            background: linear-gradient(45deg, var(--success-color), #146c43);
            color: white;
        }

        .history-icon {
            background: linear-gradient(45deg, var(--info-color), #0a58ca);
            color: white;
        }

        .help-icon {
            background: linear-gradient(45deg, var(--warning-color), #e0a800);
            color: white;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .btn-logout {
            background: linear-gradient(45deg, var(--danger-color), #b02a37);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
            color: white;
        }

        .feature-highlight {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0;
            }

            .action-card {
                margin-bottom: 1rem;
            }
        }
  </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="guest_dashboard.php">
                <?php if (!empty($system['logo'])): ?>
                    <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo" height="40" class="me-2">
                <?php endif; ?>
                <span class="fw-bold"><?= htmlspecialchars($system['system_title']) ?></span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="scan_qr.php">
                            <i class="bi bi-qr-code-scan me-1"></i> QR Scanner
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="browse_assets.php">
                            <i class="bi bi-grid-3x3-gap me-1"></i> Browse Assets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="borrowing_history.php">
                            <i class="bi bi-clock-history me-1"></i> My Requests
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link position-relative" href="borrow.php">
                            <i class="bi bi-cart me-1"></i> Borrow Cart
                            <?php if (isset($_SESSION['borrow_cart']) && count($_SESSION['borrow_cart']) > 0): ?>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    <?= count($_SESSION['borrow_cart']) ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <?php include 'notification_bell.php'; ?>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

<div class="container py-4">
  <div class="slip-card">

    <!-- Display Success/Error Messages -->
    <?php if (isset($_SESSION['return_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php
            if (is_array($_SESSION['return_success'])) {
                echo htmlspecialchars($_SESSION['return_success']['message']) . ' Request #' . htmlspecialchars($_SESSION['return_success']['submission_number']);
            } else {
                echo htmlspecialchars($_SESSION['return_success']);
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['return_success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['return_errors']) && !empty($_SESSION['return_errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach ($_SESSION['return_errors'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['return_errors']); ?>
    <?php endif; ?>

    <form id="returnForm" method="post">
      <input type="hidden" name="submission_id" value="<?= $submission_id ?>">

      <div class="row align-items-center mb-3">
        <div class="col-auto">
          <!-- Municipal Logo -->
          <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Municipal Logo" class="seal">
        </div>
        <div class="col">
          <div class="text-center">
            <div style="font-weight:700">Republic of the Philippines</div>
            <div style="font-weight:700">Province of Sorsogon</div>
            <div style="font-size:18px;font-weight:800">LOCAL GOVERNMENT UNIT OF PILAR</div>
          </div>
        </div>
        <div class="col-auto text-end document-code">
          <div>Document Code:</div>
          <div><strong>PS-DIT-01-F03-01-02</strong></div>
          <div class="mt-2">Effective Date:</div>
          <div><strong>22 May 2023</strong></div>
        </div>
      </div>

      <hr>

      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <label class="form-label">Name <span style="color: red;">*</span></label>
          <input type="text" name="name" class="form-control" value="<?= h($submission ? $submission['guest_name'] : '') ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Date Returned <span style="color: red;">*</span></label>
          <input type="date" name="date_returned" class="form-control" value="<?php echo h($data['date_returned']); ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label">Condition</label>
          <select name="condition" class="form-control">
            <option value="excellent" <?= $data['condition'] === 'excellent' ? 'selected' : '' ?>>Excellent</option>
            <option value="good" <?= $data['condition'] === 'good' ? 'selected' : '' ?>>Good</option>
            <option value="fair" <?= $data['condition'] === 'fair' ? 'selected' : '' ?>>Fair</option>
            <option value="poor" <?= $data['condition'] === 'poor' ? 'selected' : '' ?>>Poor</option>
            <option value="damaged" <?= $data['condition'] === 'damaged' ? 'selected' : '' ?>>Damaged</option>
          </select>
        </div>
      </div>

      <div class="row g-2 align-items-end mb-3">

        <div class="col-md-6">
          <label class="form-label">Barangay <span style="color: red;">*</span></label>
          <input type="text" name="barangay" class="form-control" value="<?= h($submission ? $submission['barangay'] : '') ?>" required>
        </div>

        <div class="col-md-6">
          <label class="form-label">Contact No. <span style="color: red;">*</span></label>
          <input type="text" name="contact" class="form-control" value="<?= h($submission ? $submission['contact'] : '') ?>" required>
        </div>
      </div>
      <hr>

<!-- Return Slip Title -->
<div class="text-center mb-3">
  <h5 style="font-weight: 700; text-decoration: underline;">RETURN SLIP</h5>
</div>


      <div class="table-responsive mb-3">
        <table class="table table-bordered table-fixed">
          <thead class="table-light text-center align-middle">
            <tr>
              <th style="width:50%">ITEMS BEING RETURNED</th>
              <th style="width:15%">QTY</th>
              <th style="width:20%">CONDITION</th>
              <th style="width:15%">NOTES</th>
            </tr>
          </thead>
          <tbody id="returnItemsTable">
            <?php
            // Display return items
            $minRows = max(1, count($return_items));
            for ($i=0;$i<$minRows;$i++){
                $item = $return_items[$i] ?? [];
                $thing = $item['thing'] ?? '';
                $qty = $item['quantity'] ?? '';
                $condition = $item['condition'] ?? 'good';
                $notes = $item['notes'] ?? '';
                echo "<tr>";
                echo "<td><input type=\"text\" name=\"things[]\" class=\"form-control\" value=\"".h($thing)."\" readonly></td>";
                echo "<td><input type=\"text\" name=\"qty[]\" class=\"form-control\" value=\"".h($qty)."\" readonly></td>";
                echo "<td>
                        <select name=\"conditions[]\" class=\"form-control\">
                          <option value=\"excellent\" ".($condition === 'excellent' ? 'selected' : '').">Excellent</option>
                          <option value=\"good\" ".($condition === 'good' ? 'selected' : '').">Good</option>
                          <option value=\"fair\" ".($condition === 'fair' ? 'selected' : '').">Fair</option>
                          <option value=\"poor\" ".($condition === 'poor' ? 'selected' : '').">Poor</option>
                          <option value=\"damaged\" ".($condition === 'damaged' ? 'selected' : '').">Damaged</option>
                        </select>
                      </td>";
                echo "<td><input type=\"text\" name=\"notes[]\" class=\"form-control\" value=\"".h($notes)."\" placeholder=\"Optional notes\"></td>";
                echo "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

      <div class="row mb-3">
        <div class="col-12">
          <label class="form-label">Additional Notes</label>
          <textarea name="notes" class="form-control" rows="3" placeholder="Any additional notes about the return..."><?= h($data['notes']) ?></textarea>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mb-3">
         <span style="color: red;">*</span> Required fields
        <button type="submit" name="submit" class="btn btn-success">Submit Return</button>
        <a href="borrowing_history.php" class="btn btn-secondary">Cancel</a>
      </div>
    </form>

</body>
</html>
