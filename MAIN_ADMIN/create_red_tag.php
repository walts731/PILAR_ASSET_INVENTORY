<?php
require_once '../connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Fetch the municipal logo from the system table
$logo_path = '';
$stmt_logo = $conn->prepare("SELECT logo FROM system WHERE id = 1");
$stmt_logo->execute();
$result_logo = $stmt_logo->get_result();

if ($result_logo->num_rows > 0) {
    $logo_data = $result_logo->fetch_assoc();
    $logo_path = '../img/' . $logo_data['logo']; // Path to the logo image
}

$stmt_logo->close();

// Get asset and IIRUP IDs from URL
$asset_id = isset($_GET['asset_id']) ? intval($_GET['asset_id']) : 0;
$iirup_id = isset($_GET['iirup_id']) ? intval($_GET['iirup_id']) : 0;

if ($asset_id <= 0 || $iirup_id <= 0) {
    die('Invalid request parameters.');
}

// Get current user info
$user_id = $_SESSION['user_id'];
$user_query = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$user_query->bind_param('i', $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_query->close();

// Check if Red Tag already exists for this asset and IIRUP
$red_tag_exists = false;
$red_tag_id = null;
$check_red_tag = $conn->prepare("SELECT id FROM red_tags WHERE asset_id = ? AND iirup_id = ?");
$check_red_tag->bind_param('ii', $asset_id, $iirup_id);
$check_red_tag->execute();
$red_tag_result = $check_red_tag->get_result();
if ($red_tag_result->num_rows > 0) {
    $red_tag_exists = true;
    $red_tag_row = $red_tag_result->fetch_assoc();
    $red_tag_id = $red_tag_row['id'];
}
$check_red_tag->close();

// Get asset details
$asset_query = $conn->prepare("
    SELECT a.id, a.property_no, a.description, a.acquisition_date, a.office_id, 
           i.particulars, i.dept_office
    FROM assets a
    LEFT JOIN iirup_items i ON a.id = i.asset_id AND i.iirup_id = ?
    WHERE a.id = ?
");
$asset_query->bind_param('ii', $iirup_id, $asset_id);
$asset_query->execute();
$asset_result = $asset_query->get_result();
$asset = $asset_result->fetch_assoc();
$asset_query->close();

if (!$asset) {
    die('Asset not found.');
}

// Generate Red Tag number
$red_tag_query = $conn->query("SELECT COALESCE(MAX(SUBSTRING_INDEX(red_tag_number, '-', -1)), 0) + 1 as next_num FROM red_tags");
$next_num = str_pad($red_tag_query->fetch_assoc()['next_num'], 2, '0', STR_PAD_LEFT);
$red_tag_number = "PS-5S-03-F01-01-" . $next_num;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_location = $conn->real_escape_string($_POST['item_location']);
    $removal_reason = $conn->real_escape_string($_POST['removal_reason']);
    $action = $conn->real_escape_string($_POST['action']);
    $tagged_by = $user_id;
    
    $description = $asset['description'] . ' (' . $asset['property_no'] . ')';
    
    $insert_query = $conn->prepare("
        INSERT INTO red_tags (
            red_tag_number, asset_id, iirup_id, date_received, 
            tagged_by, item_location, description, removal_reason, 
            action, status
        ) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?, 'Pending')
    ");
    
    $insert_query->bind_param(
        'siisssss',
        $red_tag_number,
        $asset_id,
        $iirup_id,
        $tagged_by,
        $item_location,
        $description,
        $removal_reason,
        $action
    );
    
    if ($insert_query->execute()) {
        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Red Tag created successfully!'
        ];
        header("Location: view_iirup.php?id=" . $iirup_id);
        exit();
    } else {
        $error = "Error creating Red Tag: " . $conn->error;
    }
    $insert_query->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Red Tag</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />
    <style>
        .red-tag-form {
            max-width: 800px;
            margin: 20px auto;
            border: 1px solid #ddd;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #dc3545;
            padding-bottom: 10px;
        }
        .logo {
            max-height: 80px;
        }
        .title {
            text-align: center;
            flex-grow: 1;
        }
        .title h3 {
            color: #dc3545;
            margin: 0;
        }
        .red-tag-no {
            text-align: right;
            font-weight: bold;
        }
        .form-label {
            font-weight: 500;
        }
        .required:after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <?php include 'includes/sidebar.php' ?>
    <div class="main">
        <?php include 'includes/topbar.php' ?>
        
        <div class="container py-4">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" class="red-tag-form">
                <!-- Header -->
                <div class="header">
                    <div class="logo">
                        <img id="municipalLogoImg" src="<?= $logo_path ?>" alt="Municipal Logo" style="height: 70px;">
                    </div>
                    <div class="title">
                        <h3>5S RED TAG</h3>
                        <div>Republic of the Philippines</div>
                        <div>Province of Sorsogon</div>
                        <div>Municipality of Pilar</div>
                    </div>
                    <div class="red-tag-no">
                        <div>Red Tag No.:</div>
                        <div><?= htmlspecialchars($red_tag_number) ?></div>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Control No.:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($red_tag_number) ?>" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date Received:</label>
                        <input type="date" class="form-control" value="<?= date('Y-m-d') ?>" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tagged By:</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($user['fullname'] ?? '') ?>" readonly>
                </div>

                <div class="mb-3">
                    <label for="item_location" class="form-label required">Item Location:</label>
                    <input type="text" class="form-control" id="item_location" name="item_location" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Description:</label>
                    <textarea class="form-control" rows="3" readonly><?= htmlspecialchars($asset['description'] . ' (' . $asset['property_no'] . ')') ?></textarea>
                </div>

                <div class="mb-3">
                    <label for="removal_reason" class="form-label required">Removal Reason:</label>
                    <select class="form-select" id="removal_reason" name="removal_reason" required>
                        <option value="">-- Select Reason --</option>
                        <option value="Unnecessary">Unnecessary</option>
                        <option value="Broken">Broken</option>
                        <option value="Obsolete">Obsolete</option>
                        <option value="Not in use">Not in use</option>
                        <option value="Other">Other (specify in Action)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="action" class="form-label required">Action:</label>
                    <select class="form-select" id="action" name="action" required>
                        <option value="">-- Select Action --</option>
                        <option value="For Disposal">For Disposal</option>
                        <option value="For Repair">For Repair</option>
                        <option value="For Relocation">For Relocation</option>
                        <option value="For Donation">For Donation</option>
                        <option value="Other">Other (specify)</option>
                    </select>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <?php if ($red_tag_exists): ?>
                        <a href="print_red_tag.php?id=<?= $red_tag_id ?>" target="_blank" class="btn btn-primary me-2">
                            <i class="bi bi-printer"></i> Print Red Tag
                        </a>
                        <a href="edit_red_tag.php?id=<?= $red_tag_id ?>" class="btn btn-warning me-2">
                            <i class="bi bi-pencil"></i> Edit Red Tag
                        </a>
                        <a href="view_iirup.php?id=<?= $iirup_id ?>" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to IIRUP
                        </a>
                    <?php else: ?>
                        <a href="view_iirup.php?id=<?= $iirup_id ?>" class="btn btn-secondary me-2">
                            <i class="bi bi-x-circle"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-tag"></i> Create Red Tag
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  <script src="js/dashboard.js"></script>
    <script>
        // Auto-select the first option in select elements if they're required and have options
        document.addEventListener('DOMContentLoaded', function() {
            const selectElements = document.querySelectorAll('select[required]');
            selectElements.forEach(select => {
                if (select.value === '' && select.options.length > 0) {
                    select.selectedIndex = 0;
                }
            });
        });
    </script>
</body>
</html>
