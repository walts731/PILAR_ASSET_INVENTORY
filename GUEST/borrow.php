<?php
// borrow.php
// Single-file fillable borrow slip using Bootstrap 5
// Save this file to a PHP-enabled server (e.g. XAMPP htdocs) and open in browser.

session_start();
require_once '../connect.php';

$system = [
    'logo' => '../img/default-logo.png',
    'system_title' => 'Inventory System'
];

// Fetch system settings
$result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
if ($result && $result->num_rows > 0) {
    $system = $result->fetch_assoc();
}

function h($s){ return htmlspecialchars($s ?? ''); }

// Default values for demo
$defaults = [
    'name' => 'Engr. Ocmor',
    'date_borrowed' => date('Y-m-d'),
    'schedule_return' => date('Y-m-d', strtotime('+7 days')),
    'contact' => '',
    'barangay' => '',
    'releasing_officer' => 'IVAN CHRISTOPHER R. MILLABAS',
    'releasing_officer_title' => 'PARK MAINTENANCE FOREMAN',
    'approved_by' => 'CAROLYN C. S. - RONEL',
    'approved_by_title' => 'MUN. ADMIN'
];

// If form posted, read values
$data = array_merge($defaults, $_POST ?? []);

// Check if borrow cart has items to pre-populate form
$cart_items = [];
if (isset($_SESSION['borrow_cart']) && !empty($_SESSION['borrow_cart'])) {
    $cart_items = $_SESSION['borrow_cart'];
} elseif (isset($_GET['asset_id']) && !empty($_GET['asset_id'])) {
    // Legacy support: if asset_id is provided directly, fetch and add to cart
    $asset_id = intval($_GET['asset_id']);
    
    // Fetch asset details
    $asset_sql = "SELECT a.id, a.description, a.inventory_tag, a.property_no, a.category, c.category_name 
                  FROM assets a 
                  LEFT JOIN categories c ON a.category = c.id 
                  WHERE a.id = ? AND a.status != 'disposed'";
    
    $asset_stmt = $conn->prepare($asset_sql);
    $asset_stmt->bind_param('i', $asset_id);
    $asset_stmt->execute();
    $asset_result = $asset_stmt->get_result();
    
    if ($asset_result->num_rows > 0) {
        $asset = $asset_result->fetch_assoc();
        $cart_items[] = [
            'asset_id' => $asset['id'],
            'description' => $asset['description'],
            'inventory_tag' => $asset['inventory_tag'],
            'property_no' => $asset['property_no'],
            'category_name' => $asset['category_name']
        ];
    }
    $asset_stmt->close();
}

// Items come from arrays in POST (things[], qty[], remarks[])
$items = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $things = $_POST['things'] ?? [];
    $qtys = $_POST['qty'] ?? [];
    $remarks = $_POST['remarks'] ?? [];
    for ($i = 0; $i < max(count($things), count($qtys), count($remarks)); $i++) {
        $t = trim($things[$i] ?? '');
        $q = trim($qtys[$i] ?? '');
        $r = trim($remarks[$i] ?? '');
        if ($t === '' && $q === '' && $r === '') continue; // skip empty rows
        $items[] = ['thing' => $t, 'qty' => $q, 'remarks' => $r];
    }
} elseif (!empty($cart_items)) {
    // Pre-populate with cart items if no POST data
    foreach ($cart_items as $cart_item) {
        $items[] = [
            'thing' => $cart_item['description'],
            'qty' => '1',
            'remarks' => ''  // Leave remarks blank
        ];
    }
}

// If the user clicked "Print Slip" we will render a printable version below (same file)
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Borrow Slip - Fillable</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a class="nav-link" href="guest_dashboard.php">
                            <i class="bi bi-house-door me-1"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="browse_assets.php">
                            <i class="bi bi-grid-3x3-gap me-1"></i> Browse Assets
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="borrowing_history.php">
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
                    <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#contactModal">
                            <i class="bi bi-question-circle me-1"></i> Help
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="../logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Cart Status -->
    <?php if (!empty($cart_items)): ?>
    <div class="container py-2">
        <div class="alert alert-info d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-cart-check me-2"></i>
                <strong>Borrow Cart:</strong> <?= count($cart_items) ?> asset(s) ready to borrow
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-danger me-2" onclick="clearBorrowCart()">
                    <i class="bi bi-trash me-1"></i>Clear Cart
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.location.href='browse_assets.php'">
                    <i class="bi bi-plus-circle me-1"></i>Add More Assets
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

<div class="container py-4">
  <div class="slip-card">
    <form id="borrowForm" method="post">
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
          <div><strong>PS-DIT-01-F03-01-01</strong></div>
          <div class="mt-2">Effective Date:</div>
          <div><strong>22 May 2023</strong></div>
        </div>
      </div>

      <hr>

      <div class="row g-2 mb-2">
        <div class="col-md-6">
          <label class="form-label">Name</label>
          <input type="text" name="name" class="form-control" value="">
        </div>
        <div class="col-md-3">
          <label class="form-label">Date Borrowed</label>
          <input type="date" name="date_borrowed" class="form-control" value="<?php echo h($data['date_borrowed']); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Schedule of Return</label>
          <input type="date" name="schedule_return" class="form-control" value="<?php echo h($data['schedule_return']); ?>">
        </div>
        
      </div>

      <div class="row g-2 align-items-end mb-3">
        
        <div class="col-md-6">
          <label class="form-label">Barangay</label>
          <input type="text" name="barangay" class="form-control" value="<?php echo h($data['barangay']); ?>">
        </div>

        <div class="col-md-6">
          <label class="form-label">Contact No.</label>
          <input type="text" name="contact" class="form-control" value="<?php echo h($data['contact']); ?>">
        </div>
       
      </div>
      <hr>

<!-- Borrow Slip Title -->
<div class="text-center mb-3">
  <h5 style="font-weight: 700; text-decoration: underline;">BORROW SLIP</h5>
</div>


      <div class="table-responsive mb-3">
        <table class="table table-bordered table-fixed">
          <thead class="table-light text-center align-middle">
            <tr>
              <th style="width:60%">THINGS BORROWED</th>
              <th style="width:10%">QTY</th>
              <th style="width:30%">REMARKS</th>
            </tr>
          </thead>
          <tbody id="itemsTable">
            <?php
            // At least 5 rows by default for fillable form
            $minRows = max(1, count($items));
            for ($i=0;$i<$minRows;$i++){
                $thing = $items[$i]['thing'] ?? '';
                $qty = $items[$i]['qty'] ?? '';
                $remark = $items[$i]['remarks'] ?? '';
                echo "<tr>";
                echo "<td><input type=\"text\" name=\"things[]\" class=\"form-control\" value=\"".h($thing)."\"></td>";
                echo "<td><input type=\"text\" name=\"qty[]\" class=\"form-control\" value=\"".h($qty)."\"></td>";
                echo "<td><input type=\"text\" name=\"remarks[]\" class=\"form-control\" value=\"".h($remark)."\"></td>";
                echo "</tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

       <div class="col-md-4 text-start">
          <button type="button" class="btn btn-sm btn-outline-secondary mt-2 no-print" onclick="addRow()">Add Item Row</button>
          <button type="button" class="btn btn-sm btn-outline-danger mt-2 no-print" onclick="removeRow()">Remove Row</button>
        </div>

      <div class="row mb-3">
        <div class="col-md-6 text-center">
          <label class="form-label">Releasing Officer</label>
          <input type="text" name="releasing_officer" class="form-control" value="<?php echo h($data['releasing_officer']); ?>">
          <small class="text-muted"><?php echo h($data['releasing_officer_title']); ?></small>
        </div>
        <div class="col-md-6 text-center">
          <label class="form-label">Approved by</label>
          <input type="text" name="approved_by" class="form-control" value="<?php echo h($data['approved_by']); ?>">
          <small class="text-muted"><?php echo h($data['approved_by_title']); ?></small>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mb-3">
        <button type="submit" class="btn btn-success">Submit</button>
        <button type="reset" class="btn btn-secondary">Reset</button>
      </div>
    </form>

<script>
// Clear borrow cart function
function clearBorrowCart() {
    if (confirm('Are you sure you want to clear all assets from the borrow cart?')) {
        $.post('borrow_cart_manager.php', {
            action: 'clear'
        })
        .done(function(response) {
            const data = typeof response === 'string' ? JSON.parse(response) : response;
            if (data.success) {
                location.reload(); // Reload to update the UI
            } else {
                alert('Failed to clear borrow cart');
            }
        })
        .fail(function() {
            alert('An error occurred while clearing the borrow cart');
        });
    }
}

// Small helpers to add/remove rows in the items table
function addRow(){
  const tbody = document.getElementById('itemsTable');
  const tr = document.createElement('tr');
  tr.innerHTML = `
    <td><input type="text" name="things[]" class="form-control"></td>
    <td><input type="text" name="qty[]" class="form-control"></td>
    <td><input type="text" name="remarks[]" class="form-control"></td>
  `;
  tbody.appendChild(tr);
}
function removeRow(){
  const tbody = document.getElementById('itemsTable');
  if (tbody.rows.length > 1) tbody.deleteRow(-1);
}
</script>

</body>
</html>
