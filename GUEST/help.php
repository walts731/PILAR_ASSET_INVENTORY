<?php
session_start();
require_once "../connect.php";

// Check if user is a guest
if (!isset($_SESSION['is_guest']) || $_SESSION['is_guest'] !== true) {
    header("Location: ../index.php");
    exit();
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
    <title>Help & Support - <?= htmlspecialchars($system['system_title']) ?></title>
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0b5ed7;
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

        .main-container {
            padding: 2rem 0;
        }

        .help-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .faq-item {
            border-bottom: 1px solid #e9ecef;
            padding: 1rem 0;
        }

        .faq-item:last-child {
            border-bottom: none;
        }

        .faq-question {
            font-weight: 600;
            color: var(--primary-color);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .faq-question:hover {
            color: #0056b3;
        }

        .faq-answer {
            margin-top: 0.5rem;
            color: #6c757d;
            line-height: 1.6;
        }

        .step-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
            transition: transform 0.3s ease;
        }

        .step-card:hover {
            transform: translateY(-2px);
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary-color), #0056b3);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 1rem;
        }

        .contact-card {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: white;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .policy-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .main-container {
                padding: 1rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="guest_dashboard.php">
                <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo" width="32" height="32" class="me-2">
                <?= htmlspecialchars($system['system_title']) ?>
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

    <!-- Main Content -->
    <div class="container main-container">
        <!-- Page Header -->
        <div class="text-center mb-4">
            <h2 class="text-white mb-2">
                <i class="bi bi-question-circle me-2"></i>Help & Support
            </h2>
            <p class="text-white-50">Get help with borrowing assets and using the system</p>
        </div>

        <div class="row">
            <!-- Quick Start Guide -->
            <div class="col-lg-8 mb-4">
                <div class="help-card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="bi bi-play-circle me-2 text-primary"></i>Quick Start Guide
                        </h4>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="step-card">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="step-number">1</div>
                                        <div>
                                            <h6 class="mb-1">Scan QR Code</h6>
                                            <p class="mb-0 small text-muted">Use the QR scanner to quickly find assets</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="step-card">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="step-number">2</div>
                                        <div>
                                            <h6 class="mb-1">Browse Assets</h6>
                                            <p class="mb-0 small text-muted">Or browse the asset catalog by category</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="step-card">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="step-number">3</div>
                                        <div>
                                            <h6 class="mb-1">Request Borrowing</h6>
                                            <p class="mb-0 small text-muted">Click "Request Borrowing" on available assets</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="step-card">
                                    <div class="card-body d-flex align-items-center">
                                        <div class="step-number">4</div>
                                        <div>
                                            <h6 class="mb-1">Wait for Approval</h6>
                                            <p class="mb-0 small text-muted">Track your request status in borrowing history</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FAQ Section -->
                <div class="help-card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="bi bi-question-diamond me-2 text-primary"></i>Frequently Asked Questions
                        </h4>
                        
                        <div class="faq-item">
                            <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="bi bi-chevron-right me-2"></i>How do I borrow an asset?
                            </div>
                            <div class="collapse faq-answer" id="faq1">
                                To borrow an asset, you can either scan its QR code or browse the asset catalog. Once you find the asset you need, click "Request Borrowing" if it's available. Your request will be reviewed and you'll be contacted for approval and pickup details.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="bi bi-chevron-right me-2"></i>How long can I borrow an asset?
                            </div>
                            <div class="collapse faq-answer" id="faq2">
                                Borrowing periods vary depending on the type of asset and its demand. Typically, equipment can be borrowed for 1-7 days. The specific return date will be communicated when your request is approved.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="bi bi-chevron-right me-2"></i>What if I need to extend my borrowing period?
                            </div>
                            <div class="collapse faq-answer" id="faq3">
                                If you need to extend your borrowing period, contact the asset administrator before the return date. Extensions are subject to availability and approval.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="bi bi-chevron-right me-2"></i>What happens if I return an asset late?
                            </div>
                            <div class="collapse faq-answer" id="faq4">
                                Late returns may affect your future borrowing privileges. Please return assets on time or contact us if you anticipate a delay. Repeated late returns may result in temporary suspension of borrowing access.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <i class="bi bi-chevron-right me-2"></i>What if an asset is damaged while I'm using it?
                            </div>
                            <div class="collapse faq-answer" id="faq5">
                                Report any damage immediately to the asset administrator. Minor wear and tear is expected, but significant damage may require repair costs. Always handle assets with care and follow any provided usage instructions.
                            </div>
                        </div>

                        <div class="faq-item">
                            <div class="faq-question" data-bs-toggle="collapse" data-bs-target="#faq6">
                                <i class="bi bi-chevron-right me-2"></i>Can I cancel a borrowing request?
                            </div>
                            <div class="collapse faq-answer" id="faq6">
                                Yes, you can cancel pending requests from your borrowing history page. Once a request is approved and you've picked up the asset, you cannot cancel it but must return the asset by the agreed date.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Borrowing Policies -->
                <div class="help-card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="bi bi-shield-check me-2 text-primary"></i>Borrowing Policies
                        </h4>
                        
                        <div class="policy-section">
                            <h6><i class="bi bi-clock me-2"></i>Borrowing Hours</h6>
                            <p class="mb-0">Assets can be picked up and returned during office hours: Monday-Friday, 8:00 AM - 5:00 PM</p>
                        </div>

                        <div class="policy-section">
                            <h6><i class="bi bi-person-check me-2"></i>Identification Required</h6>
                            <p class="mb-0">Valid ID is required for asset pickup. The person picking up must be the same person who made the request.</p>
                        </div>

                        <div class="policy-section">
                            <h6><i class="bi bi-shield-exclamation me-2"></i>Responsibility</h6>
                            <p class="mb-0">Borrowers are responsible for the safe keeping and proper use of borrowed assets. Any loss or damage may result in replacement costs.</p>
                        </div>

                        <div class="policy-section">
                            <h6><i class="bi bi-arrow-return-left me-2"></i>Returns</h6>
                            <p class="mb-0">Assets must be returned in the same condition as borrowed, clean and with all accessories included.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Support -->
            <div class="col-lg-4">
                <div class="contact-card">
                    <div class="card-body text-center">
                        <i class="bi bi-headset" style="font-size: 3rem; margin-bottom: 1rem;"></i>
                        <h4>Need More Help?</h4>
                        <p class="mb-4">Contact our support team for assistance with borrowing requests or technical issues.</p>
                        
                        <div class="mb-3">
                            <h6><i class="bi bi-telephone me-2"></i>Phone Support</h6>
                            <p class="mb-0">(02) 123-4567</p>
                            <small>Mon-Fri, 8:00 AM - 5:00 PM</small>
                        </div>

                        <div class="mb-3">
                            <h6><i class="bi bi-envelope me-2"></i>Email Support</h6>
                            <p class="mb-0">support@inventory.gov.ph</p>
                            <small>Response within 24 hours</small>
                        </div>

                        <div class="mb-4">
                            <h6><i class="bi bi-geo-alt me-2"></i>Office Location</h6>
                            <p class="mb-0">Asset Management Office</p>
                            <small>Ground Floor, Main Building</small>
                        </div>

                        <a href="mailto:support@inventory.gov.ph" class="btn btn-light">
                            <i class="bi bi-envelope me-2"></i>Send Email
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="help-card mt-4">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bi bi-link-45deg me-2 text-primary"></i>Quick Links
                        </h5>
                        
                        <div class="d-grid gap-2">
                            <a href="borrowing_history.php" class="btn btn-outline-info me-2">
                                <i class="bi bi-clock-history me-1"></i>Browse
                            </a>
                            <a href="guest_dashboard.php" class="btn btn-outline-primary me-2">
                                <i class="bi bi-house me-1"></i>Dashboard
                            </a>
                            <?php include 'notification_bell.php'; ?>
                            <a href="../logout.php" class="btn btn-outline-danger">
                                <i class="bi bi-box-arrow-right me-1"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="help-card mt-4">
                    <div class="card-body text-center">
                        <h6 class="card-title">
                            <i class="bi bi-activity me-2 text-success"></i>System Status
                        </h6>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>QR Scanner</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Asset Database</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Borrowing System</span>
                            <span class="badge bg-success">Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // FAQ toggle functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function() {
                const icon = this.querySelector('i');
                const target = this.getAttribute('data-bs-target');
                const collapse = document.querySelector(target);
                
                // Toggle icon
                if (collapse.classList.contains('show')) {
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                } else {
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                }
            });
        });

        // Auto-collapse other FAQs when one is opened
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(trigger => {
            trigger.addEventListener('click', function() {
                const targetId = this.getAttribute('data-bs-target');
                document.querySelectorAll('.collapse.show').forEach(openCollapse => {
                    if ('#' + openCollapse.id !== targetId) {
                        bootstrap.Collapse.getInstance(openCollapse)?.hide();
                    }
                });
            });
        });
    </script>
</body>
</html>
