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

// Get guest borrowing statistics
$stats = [
    'available_assets' => 0,
    'borrowed_assets' => 0,
    'pending_returns' => 0
];

if (isset($conn) && $conn instanceof mysqli) {
    // Available assets for borrowing
    $result = $conn->query("SELECT COUNT(*) as count FROM assets WHERE status = 'available' AND type = 'asset'");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['available_assets'] = $row['count'];
    }
    
    // Borrowed assets (you might need to adjust this based on your borrowing table structure)
    $result = $conn->query("SELECT COUNT(*) as count FROM assets WHERE status = 'borrowed'");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['borrowed_assets'] = $row['count'];
    }
    
    // Pending returns (assets needing maintenance or unserviceable)
    $result = $conn->query("SELECT COUNT(*) as count FROM assets WHERE status IN ('maintenance', 'unserviceable')");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['pending_returns'] = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guest Dashboard - <?= htmlspecialchars($system['system_title']) ?></title>
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Intro.js CSS -->
    <link href="https://unpkg.com/intro.js@7.2.0/minified/introjs.min.css" rel="stylesheet">
    
    <style>
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
                <img src="../img/<?= htmlspecialchars($system['logo']) ?>" alt="Logo" width="32" height="32" class="me-2">
                <?= htmlspecialchars($system['system_title']) ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="guest_dashboard.php">
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
        <!-- Welcome Section -->
        <div class="row mb-4" data-intro="Welcome to your guest dashboard! This is your starting point for all asset borrowing activities." data-step="1">
            <div class="col-12">
                <div class="card welcome-card">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="card-title mb-2">
                                    <i class="bi bi-hand-thumbs-up me-2"></i>
                                    Welcome, <?php echo htmlspecialchars($_SESSION['guest_name'] ?? 'Guest'); ?>!
                                </h2>
                                <p class="card-text mb-3">
                                    You can browse and borrow available assets using our QR scanner or asset browser. 
                                    All borrowing activities are tracked for inventory management.
                                </p>
                                <div class="feature-highlight">
                                    <h6><i class="bi bi-qr-code-scan me-2"></i>Quick Start:</h6>
                                    <p class="mb-2 small">Scan any asset QR code to view details and request borrowing, or browse our available assets catalog.</p>
                                    <button onclick="startTutorial()" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-play-circle me-1"></i>Take a Tour
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <i class="bi bi-box-seam" style="font-size: 4rem; opacity: 0.7;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="row mb-4" data-intro="Here you can see an overview of asset availability in the system." data-step="2">
            <div class="col-md-4 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center p-4">
                        <div class="stat-icon bg-success text-white mx-auto mb-3">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <h3 class="text-success mb-1"><?= number_format($stats['available_assets']) ?></h3>
                        <p class="text-muted mb-0">Available Assets</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center p-4">
                        <div class="stat-icon bg-primary text-white mx-auto mb-3">
                            <i class="bi bi-box-arrow-right"></i>
                        </div>
                        <h3 class="text-primary mb-1"><?= number_format($stats['borrowed_assets']) ?></h3>
                        <p class="text-muted mb-0">Currently Borrowed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stats-card">
                    <div class="card-body text-center p-4">
                        <div class="stat-icon bg-warning text-white mx-auto mb-3">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h3 class="text-warning mb-1"><?= number_format($stats['pending_returns']) ?></h3>
                        <p class="text-muted mb-0">Needs Attention</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Cards -->
        <div class="row" data-intro="These are the main actions you can perform in the system." data-step="3">
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card action-card h-100" onclick="location.href='scan_qr.php'" data-intro="Use the QR scanner to quickly find and borrow assets by scanning QR codes." data-step="4">
                    <div class="card-body text-center p-4">
                        <div class="action-icon scan-icon">
                            <i class="bi bi-qr-code-scan"></i>
                        </div>
                        <h5 class="card-title">Scan QR Code</h5>
                        <p class="card-text text-muted">
                            Scan asset QR codes to view details and request borrowing
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-primary">Quick Access</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card action-card h-100" onclick="location.href='browse_assets.php'" data-intro="Browse through all available assets with search and filter options." data-step="5">
                    <div class="card-body text-center p-4">
                        <div class="action-icon browse-icon">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </div>
                        <h5 class="card-title">Browse Assets</h5>
                        <p class="card-text text-muted">
                            View all available assets and their details
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-success">Catalog</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card action-card h-100" onclick="location.href='borrowing_history.php'" data-intro="Track your borrowing requests and return history." data-step="6">
                    <div class="card-body text-center p-4">
                        <div class="action-icon history-icon">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <h5 class="card-title">Borrowing History</h5>
                        <p class="card-text text-muted">
                            View your borrowing history and current loans
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-info">Track</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card action-card h-100" onclick="location.href='help.php'" data-intro="Get help with borrowing procedures, policies, and contact information." data-step="7">
                    <div class="card-body text-center p-4">
                        <div class="action-icon help-icon">
                            <i class="bi bi-question-circle"></i>
                        </div>
                        <h5 class="card-title">Help & Support</h5>
                        <p class="card-text text-muted">
                            Get help with borrowing process and policies
                        </p>
                        <div class="mt-3">
                            <span class="badge bg-warning">Support</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Footer -->
        <div class="row mt-4" data-intro="Quick tips and easy access to start scanning assets." data-step="8">
            <div class="col-12">
                <div class="card" style="background: rgba(255, 255, 255, 0.9); border: none; border-radius: 15px;">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h6 class="mb-2"><i class="bi bi-lightbulb me-2 text-warning"></i>Quick Tips</h6>
                                <ul class="mb-0 small text-muted">
                                    <li>Use the QR scanner for fastest asset lookup</li>
                                    <li>Check asset availability before requesting</li>
                                    <li>Return borrowed items on time to maintain access</li>
                                </ul>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="scan_qr.php" class="btn btn-primary" data-intro="Click here to start scanning QR codes and borrow assets quickly!" data-step="9">
                                    <i class="bi bi-qr-code-scan me-2"></i>Start Scanning
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Intro.js JS -->
    <script src="https://unpkg.com/intro.js@7.2.0/minified/intro.min.js"></script>
    
    <script>
        // Walkthrough Tutorial Function
        function startTutorial() {
            const intro = introJs();
            
            intro.setOptions({
                steps: [
                    {
                        element: document.querySelector('[data-step="1"]'),
                        intro: "Welcome to your guest dashboard! This is your starting point for all asset borrowing activities."
                    },
                    {
                        element: document.querySelector('[data-step="2"]'),
                        intro: "Here you can see an overview of asset availability in the system - how many assets are available, currently borrowed, or need attention."
                    },
                    {
                        element: document.querySelector('[data-step="3"]'),
                        intro: "These are the main actions you can perform in the system. Each card represents a different way to interact with assets."
                    },
                    {
                        element: document.querySelector('[data-step="4"]'),
                        intro: "Use the QR scanner to quickly find and borrow assets by scanning QR codes. This is the fastest way to locate and request assets!"
                    },
                    {
                        element: document.querySelector('[data-step="5"]'),
                        intro: "Browse through all available assets with search and filter options. Perfect for exploring what's available without knowing specific asset details."
                    },
                    {
                        element: document.querySelector('[data-step="6"]'),
                        intro: "Track your borrowing requests and return history. Here you can see the status of your current requests and past borrowing activity."
                    },
                    {
                        element: document.querySelector('[data-step="7"]'),
                        intro: "Get help with borrowing procedures, policies, and contact information. If you need assistance, this is your go-to resource."
                    },
                    {
                        element: document.querySelector('[data-step="8"]'),
                        intro: "Quick tips and easy access to start scanning assets. Remember these helpful reminders for the best borrowing experience."
                    },
                    {
                        element: document.querySelector('[data-step="9"]'),
                        intro: "Click here to start scanning QR codes and borrow assets quickly! This is the recommended way to begin borrowing."
                    },
                    {
                        element: document.querySelector('[data-step="10"]'),
                        intro: "Click here to safely log out of the system when you're done. Always log out to protect your session."
                    }
                ],
                showProgress: true,
                showBullets: true,
                exitOnOverlayClick: true,
                exitOnEsc: true,
                nextLabel: 'Next',
                prevLabel: 'Previous',
                skipLabel: 'Skip Tour',
                doneLabel: 'Finish Tour'
            });

            intro.start();
        }

        // Add click animations
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 150);
            });
        });

        // Add loading states for navigation
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                const icon = this.querySelector('.action-icon i');
                const originalClass = icon.className;
                icon.className = 'bi bi-hourglass-split';
                
                setTimeout(() => {
                    icon.className = originalClass;
                }, 1000);
            });
        });
    </script>

    <!-- Logout Modal -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to log out?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="../logout.php" class="btn btn-danger">Logout</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
