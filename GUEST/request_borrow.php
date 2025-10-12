<?php
session_start();
require_once "../connect.php";
require_once "../includes/classes/GuestBorrowing.php";

// Check if user is a guest
if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] !== true) {
    header("Location: ../index.php");
    exit();
}

// Initialize GuestBorrowing
$guestBorrowing = new GuestBorrowing($conn);

// Check if assets are selected
$selected_assets = [];
if (isset($_GET['selected_assets'])) {
    $asset_ids = explode(',', $_GET['selected_assets']);
    $placeholders = str_repeat('?,', count($asset_ids) - 1) . '?';
    $query = "SELECT * FROM assets WHERE id IN ($placeholders)";
    
    $stmt = $conn->prepare($query);
    $types = str_repeat('i', count($asset_ids));
    $stmt->bind_param($types, ...$asset_ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_assets = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle form submission
$error = '';
$success = false;
$request_number = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate required fields
    $required = ['guest_name', 'guest_contact', 'guest_organization', 'purpose', 'needed_by_date', 'expected_return_date'];
    $missing = [];
    $data = [];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            $missing[] = $field;
        } else {
            $data[$field] = trim($_POST[$field]);
        }
    }
    
    // Validate asset selection
    if (empty($_POST['asset_ids']) || !is_array($_POST['asset_ids'])) {
        $missing[] = 'assets';
    } else {
        $data['items'] = [];
        foreach ($_POST['asset_ids'] as $asset_id) {
            $data['items'][] = [
                'asset_id' => intval($asset_id),
                'quantity' => 1 // Default quantity to 1 for now
            ];
        }
    }
    
    if (!empty($missing)) {
        $error = 'Please fill in all required fields and select at least one asset.';
    } else {
        // Add guest email from session
        $data['guest_email'] = $_SESSION['guest_email'];
        
        // Create the borrowing request
        $result = $guestBorrowing->createRequest($data);
        
        if ($result['success']) {
            $success = true;
            $request_number = $result['request_number'];
            
            // Store success message in session for the history page
            $_SESSION['borrow_success'] = [
                'message' => 'Your borrowing request has been submitted successfully!',
                'request_number' => $request_number
            ];
            
            // Redirect to history page
            header("Location: borrowing_history.php");
            exit();
        } else {
            $error = 'Failed to submit borrowing request: ' . ($result['error'] ?? 'Unknown error');
        }
    }
}

// Fetch system settings for branding
$system = [
    'logo' => 'default-logo.png',
    'system_title' => 'Inventory System'
];

if (isset($conn) && $conn instanceof mysqli) {
    $result = $conn->query("SELECT logo, system_title FROM system LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $system = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request to Borrow - <?= htmlspecialchars($system['system_title']) ?></title>
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0b5ed7;
            --success-color: #198754;
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

        .main-container {
            padding: 2rem 0;
        }

        .borrow-form-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .asset-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
        }

        .btn-submit {
            background: linear-gradient(45deg, var(--primary-color), #0a58ca);
            border: none;
            padding: 10px 30px;
            font-weight: 600;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(11, 94, 215, 0.3);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(11, 94, 215, 0.25);
        }

        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #0a58ca;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="guest_dashboard.php">
                <?php if (!empty($system['logo'])): ?>
                    <img src="../uploads/logos/<?= htmlspecialchars($system['logo']) ?>" alt="Logo" height="40" class="me-2">
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

    <div class="container main-container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="browse_assets.php" class="back-link">
                        <i class="bi bi-arrow-left me-1"></i> Back to Browse
                    </a>
                    <h2 class="mb-0">
                        <i class="bi bi-cart-plus me-2"></i>Request to Borrow
                    </h2>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($selected_assets)): ?>
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        No assets selected. Please go back and select assets to borrow.
                    </div>
                    <div class="text-center mt-4">
                        <a href="browse_assets.php" class="btn btn-primary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Browse
                        </a>
                    </div>
                <?php else: ?>
                    <div class="borrow-form-card">
                        <form method="POST" action="" id="borrowForm">
                            <!-- Hidden fields for asset IDs -->
                            <?php foreach ($selected_assets as $asset): ?>
                                <input type="hidden" name="asset_ids[]" value="<?= $asset['id'] ?>">
                            <?php endforeach; ?>
                            
                            <h5 class="mb-4">
                                <i class="bi bi-person-lines-fill me-2"></i>Your Information
                            </h5>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label for="guest_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="guest_name" name="guest_name" required 
                                           value="<?= isset($_POST['guest_name']) ? htmlspecialchars($_POST['guest_name']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="guest_contact" class="form-label">Contact Number <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="guest_contact" name="guest_contact" required
                                           value="<?= isset($_POST['guest_contact']) ? htmlspecialchars($_POST['guest_contact']) : '' ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="guest_organization" class="form-label">Organization/Institution <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="guest_organization" name="guest_organization" required
                                           value="<?= isset($_POST['guest_organization']) ? htmlspecialchars($_POST['guest_organization']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="needed_by_date" class="form-label">Needed By Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="needed_by_date" name="needed_by_date" required
                                           min="<?= date('Y-m-d') ?>" 
                                           value="<?= isset($_POST['needed_by_date']) ? htmlspecialchars($_POST['needed_by_date']) : '' ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="expected_return_date" class="form-label">Expected Return Date <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="expected_return_date" name="expected_return_date" required
                                           min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                                           value="<?= isset($_POST['expected_return_date']) ? htmlspecialchars($_POST['expected_return_date']) : '' ?>">
                                </div>
                                <div class="col-12 mb-3">
                                    <label for="purpose" class="form-label">Purpose of Borrowing <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="purpose" name="purpose" rows="3" required><?= isset($_POST['purpose']) ? htmlspecialchars($_POST['purpose']) : '' ?></textarea>
                                    <div class="form-text">Please provide details about how you'll be using the items.</div>
                                </div>
                            </div>
                            
                            <h5 class="mb-3 mt-5">
                                <i class="bi bi-box-seam-fill me-2"></i>Selected Items
                            </h5>
                            
                            <div class="mb-4">
                                <?php foreach ($selected_assets as $asset): ?>
                                    <div class="asset-item">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($asset['asset_name'] ?: 'Unnamed Asset') ?></h6>
                                                <p class="mb-1 text-muted small">
                                                    <?php if (!empty($asset['inventory_tag'])): ?>
                                                        <span class="me-2">#<?= htmlspecialchars($asset['inventory_tag']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($asset['brand'])): ?>
                                                        <span class="me-2"><?= htmlspecialchars($asset['brand']) ?></span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($asset['model'])): ?>
                                                        <span><?= htmlspecialchars($asset['model']) ?></span>
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <span class="badge bg-primary">Qty: 1</span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="terms_agreement" required>
                                <label class="form-check-label" for="terms_agreement">
                                    I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">terms and conditions</a> of borrowing.
                                    <span class="text-danger">*</span>
                                </label>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="browse_assets.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Back
                                </a>
                                <button type="submit" class="btn btn-primary btn-submit">
                                    <i class="bi bi-send-check me-1"></i> Submit Request
                                </button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Borrowing Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>1. General Terms</h6>
                    <p>By borrowing items, you agree to the following terms and conditions:</p>
                    <ul>
                        <li>Items must be returned by the agreed-upon return date.</li>
                        <li>You are responsible for the care and safe return of all borrowed items.</li>
                        <li>Any damage or loss must be reported immediately.</li>
                    </ul>
                    
                    <h6 class="mt-4">2. Liability</h6>
                    <p>You are responsible for any damage, loss, or theft of borrowed items while they are in your possession.</p>
                    
                    <h6 class="mt-4">3. Late Returns</h6>
                    <p>Late returns may result in penalties or suspension of borrowing privileges.</p>
                    
                    <h6 class="mt-4">4. Condition of Items</h6>
                    <p>Items must be returned in the same condition as when borrowed, accounting for normal wear and tear.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I Understand</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Help Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">Need Help?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>If you need assistance with your borrowing request, please contact our support team:</p>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-envelope me-2"></i> support@example.com</li>
                        <li><i class="bi bi-telephone me-2"></i> (123) 456-7890</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('borrowForm');
            const neededByDate = document.getElementById('needed_by_date');
            const returnDate = document.getElementById('expected_return_date');
            
            // Set minimum dates
            const today = new Date().toISOString().split('T')[0];
            neededByDate.min = today;
            
            // Update return date min when needed by date changes
            neededByDate.addEventListener('change', function() {
                returnDate.min = this.value;
                if (returnDate.value && returnDate.value < this.value) {
                    returnDate.value = this.value;
                }
            });
            
            // Form submission
            form.addEventListener('submit', function(e) {
                const termsAgreement = document.getElementById('terms_agreement');
                
                if (!termsAgreement.checked) {
                    e.preventDefault();
                    alert('Please agree to the terms and conditions.');
                    termsAgreement.focus();
                }
            });
        });
    </script>
</body>
</html>
