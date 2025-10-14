<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
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

// Fetch full name for topbar display
$fullname = '';
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>QR Code Scanner - PILAR Asset Inventory</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />

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
    .scanner-container {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 20px;
      padding: 2rem;
      box-shadow: 0 15px 35px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
    }

    #reader {
      width: 100%;
      max-width: 500px;
      margin: auto;
      border: 3px solid #fff;
      border-radius: 15px;
      background: #fff;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      overflow: hidden;
    }


    @keyframes pulse {
      0% { border-color: #ff6b6b; box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.7); }
      50% { border-color: #4ecdc4; box-shadow: 0 0 0 10px rgba(78, 205, 196, 0); }
      100% { border-color: #ff6b6b; box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
    }

    .status-indicator {
      padding: 1rem;
      border-radius: 10px;
      margin: 1rem 0;
      font-weight: 600;
      text-align: center;
      transition: all 0.3s ease;
    }

    .status-ready { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
    .status-scanning { background: #fff3cd; color: #856404; border: 2px solid #ffeaa7; }
    .status-success { background: #d1ecf1; color: #0c5460; border: 2px solid #bee5eb; }
    .status-error { background: #f8d7da; color: #721c24; border: 2px solid #f5c6cb; }

    .control-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
      margin: 1.5rem 0;
    }

    .btn-scanner {
      padding: 0.75rem 1.5rem;
      border-radius: 25px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      border: none;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }

    .btn-scanner:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(0,0,0,0.3);
    }

    .asset-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.3s ease;
    }

    .asset-card:hover {
      transform: translateY(-5px);
    }

    .asset-actions {
      display: flex;
      gap: 0.5rem;
      justify-content: center;
      flex-wrap: wrap;
      padding: 1rem;
      background: #f8f9fa;
    }

    .btn-action {
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      border: none;
    }

    .scan-tips {
      background: rgba(255,255,255,0.1);
      border-radius: 10px;
      padding: 1rem;
      margin-top: 1rem;
      color: white;
    }

    .tips-content {
      font-size: 0.9rem;
      line-height: 1.6;
    }

    .scan-counter {
      background: rgba(255,255,255,0.2);
      border-radius: 50px;
      padding: 0.5rem 1rem;
      color: white;
      font-weight: 600;
      display: inline-block;
      margin-bottom: 1rem;
    }

    @media (max-width: 768px) {
      .scanner-container { padding: 1rem; }
      #reader { max-width: 100%; }
      .control-buttons { flex-direction: column; align-items: center; }
      .asset-actions { flex-direction: column; }
    }

    /* Accessibility improvements */
    .btn:focus, .btn-scanner:focus {
      outline: 3px solid #007bff;
      outline-offset: 2px;
    }

    .sr-only {
      position: absolute;
      width: 1px;
      height: 1px;
      padding: 0;
      margin: -1px;
      overflow: hidden;
      clip: rect(0,0,0,0);
      white-space: nowrap;
      border: 0;
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
                        <a class="nav-link active" href="scan_qr.php">
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
  <div class="main">
   

    <div class="container-fluid py-4">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          
          <!-- Scanner Section -->
          <div class="scanner-container text-center">
            <h2 class="text-white mb-3">
              <i class="bi bi-qr-code-scan me-2"></i>
              QR Code Scanner
            </h2>
            
            <div class="scan-counter" id="scanCounter">
              <i class="bi bi-check-circle me-1"></i>
              Scans: <span id="scanCount">0</span>
            </div>

            <div id="reader"></div>
            
            <!-- Simple targeting guide -->
            <div class="text-white mt-2">
              <small><i class="bi bi-bullseye me-1"></i>Center the QR code in the camera view</small>
            </div>

            <!-- Status Display -->
            <div id="status" class="status-indicator status-ready">
              <i class="bi bi-camera me-2"></i>
              Ready to scan - Point camera at QR code
            </div>

            <!-- Control Buttons -->
            <div class="control-buttons">
              <button id="startBtn" class="btn btn-success btn-scanner">
                <i class="bi bi-play-fill me-1"></i>
                Start Scanner
              </button>
              <button id="stopBtn" class="btn btn-danger btn-scanner" style="display: none;">
                <i class="bi bi-stop-fill me-1"></i>
                Stop Scanner
              </button>
              <button id="switchCameraBtn" class="btn btn-info btn-scanner" style="display: none;">
                <i class="bi bi-camera-reels me-1"></i>
                Switch Camera
              </button>
              <button id="resetBtn" class="btn btn-warning btn-scanner">
                <i class="bi bi-arrow-clockwise me-1"></i>
                Reset
              </button>
            </div>

            <!-- Scanning Tips -->
            <div class="scan-tips">
              <h6 class="text-white mb-2">
                <i class="bi bi-lightbulb me-1"></i>
                Scanning Tips
              </h6>
              <div class="tips-content">
                • Hold device steady and ensure good lighting<br>
                • Keep QR code within the red targeting box<br>
                • Move closer or farther to focus properly<br>
                • Use <kbd>Space</kbd> to toggle scanner, <kbd>Ctrl+R</kbd> to reset
              </div>
            </div>
          </div>


        </div>
      </div>
    </div>
  </div>

  <!-- Loading Spinner -->
  <div id="loadingSpinner" class="position-fixed top-50 start-50 translate-middle" style="display: none; z-index: 9999;">
    <div class="spinner-border text-primary" role="status">
      <span class="visually-hidden">Loading...</span>
    </div>
  </div>

  <!-- Simple QR Scanner using ZXing -->
  <script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
  <script>
    class SimpleQRScanner {
      constructor() {
        this.codeReader = null;
        this.stream = null;
        this.isScanning = false;
        this.scanCount = 0;
        this.lastScanTime = 0;
        this.scanCooldown = 2000;
        
        this.initializeElements();
        this.setupEventListeners();
        this.initializeScanner();
      }

      initializeElements() {
        this.elements = {
          reader: document.getElementById('reader'),
          status: document.getElementById('status'),
          startBtn: document.getElementById('startBtn'),
          stopBtn: document.getElementById('stopBtn'),
          switchCameraBtn: document.getElementById('switchCameraBtn'),
          resetBtn: document.getElementById('resetBtn'),
          scanCount: document.getElementById('scanCount'),
          loadingSpinner: document.getElementById('loadingSpinner')
        };

        // Create video element
        this.video = document.createElement('video');
        this.video.style.width = '100%';
        this.video.style.height = '300px';
        this.video.style.objectFit = 'cover';
        this.video.autoplay = true;
        this.video.muted = true;
        this.video.playsInline = true;
        this.elements.reader.appendChild(this.video);
      }

      setupEventListeners() {
        this.elements.startBtn.addEventListener('click', () => this.startScanning());
        this.elements.stopBtn.addEventListener('click', () => this.stopScanning());
        this.elements.resetBtn.addEventListener('click', () => this.resetScanner());

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
          if (e.code === 'Space') {
            e.preventDefault();
            this.toggleScanning();
          } else if (e.ctrlKey && e.code === 'KeyR') {
            e.preventDefault();
            this.resetScanner();
          }
        });
      }

      async initializeScanner() {
        try {
          this.updateStatus('Initializing scanner...', 'scanning');
          this.showLoading(true);

          // Initialize ZXing code reader
          this.codeReader = new ZXing.BrowserQRCodeReader();
          
          this.updateStatus('Scanner ready - Click Start to begin', 'ready');
        } catch (error) {
          console.error('Scanner initialization error:', error);
          this.updateStatus('Scanner initialization failed', 'error');
        } finally {
          this.showLoading(false);
        }
      }

      async startScanning() {
        if (this.isScanning) return;

        try {
          this.updateStatus('Starting camera...', 'scanning');
          this.showLoading(true);

          // Get user media with optimized constraints
          const constraints = {
            video: {
              facingMode: 'environment',
              width: { ideal: 480, max: 640 },
              height: { ideal: 360, max: 480 },
              frameRate: { ideal: 15, max: 20 }
            }
          };

          this.stream = await navigator.mediaDevices.getUserMedia(constraints);
          this.video.srcObject = this.stream;
          
          await this.video.play();

          // Start scanning
          this.isScanning = true;
          this.updateUI();
          this.updateStatus('Scanning... Point camera at QR code', 'scanning');
          
          // Wait a bit for video to stabilize before starting scan loop
          setTimeout(() => {
            if (this.isScanning) {
              this.scanLoop();
            }
          }, 500);
          
        } catch (error) {
          console.error('Camera access error:', error);
          this.updateStatus('Camera access denied. Please allow camera permissions.', 'error');
        } finally {
          this.showLoading(false);
        }
      }

      async scanLoop() {
        if (!this.isScanning) return;

        try {
          // Only scan if video is ready and playing
          if (this.video.readyState >= 2 && !this.video.paused) {
            const result = await this.codeReader.decodeOnceFromVideoDevice(undefined, this.video);
            
            if (result) {
              this.onScanSuccess(result.text);
              return; // Stop scanning after successful scan
            }
          }
        } catch (error) {
          // Continue scanning - most errors are just "no QR code found"
        }

        // Continue scanning with longer interval to reduce CPU usage
        if (this.isScanning) {
          requestAnimationFrame(() => {
            setTimeout(() => this.scanLoop(), 300); // Reduced frequency to 300ms
          });
        }
      }

      async stopScanning() {
        if (!this.isScanning) return;

        this.isScanning = false;
        
        // Stop video stream
        if (this.stream) {
          this.stream.getTracks().forEach(track => track.stop());
          this.stream = null;
        }

        // Clear video
        this.video.srcObject = null;

        this.updateUI();
        this.updateStatus('Scanner stopped', 'ready');
      }

      async resetScanner() {
        await this.stopScanning();
        this.scanCount = 0;
        this.elements.scanCount.textContent = '0';
        this.updateStatus('Scanner reset - Ready to scan', 'ready');
      }

      toggleScanning() {
        if (this.isScanning) {
          this.stopScanning();
        } else {
          this.startScanning();
        }
      }

      onScanSuccess(decodedText) {
        const now = Date.now();
        if (now - this.lastScanTime < this.scanCooldown) {
          return; // Prevent duplicate scans
        }

        this.lastScanTime = now;
        const assetId = decodedText.trim();

        // Validate QR code format
        if (!/^\d+$/.test(assetId)) {
          this.updateStatus(`Invalid QR code: ${assetId}`, 'error');
          setTimeout(() => {
            if (this.isScanning) {
              this.updateStatus('Scanning... Point camera at QR code', 'scanning');
            }
          }, 2000);
          return;
        }

        this.scanCount++;
        this.elements.scanCount.textContent = this.scanCount;
        
        this.updateStatus(`✓ Scanned Asset ID: ${assetId} - Opening asset details...`, 'success');
        
        // Add success animation
        this.elements.reader.style.transform = 'scale(1.05)';
        setTimeout(() => {
          this.elements.reader.style.transform = 'scale(1)';
        }, 200);

        // Stop scanning and redirect to view asset details
        this.stopScanning();
        setTimeout(() => {
          window.location.href = `view_asset_details.php?id=${assetId}`;
        }, 1500);
      }

      updateStatus(message, type) {
        this.elements.status.textContent = message;
        this.elements.status.className = `status-indicator status-${type}`;
      }

      updateUI() {
        this.elements.startBtn.style.display = this.isScanning ? 'none' : 'inline-block';
        this.elements.stopBtn.style.display = this.isScanning ? 'inline-block' : 'none';
        this.elements.switchCameraBtn.style.display = 'none'; // Simplified - no camera switching
      }

      showLoading(show) {
        this.elements.loadingSpinner.style.display = show ? 'block' : 'none';
      }
    }

    // Initialize scanner when page loads
    document.addEventListener('DOMContentLoaded', () => {
      window.qrScanner = new SimpleQRScanner();
    });

    // Handle page navigation
    window.addEventListener('beforeunload', () => {
      if (window.qrScanner && window.qrScanner.isScanning) {
        window.qrScanner.stopScanning();
      }
    });

    // NOTE: Transfer Asset click handler is initialized after jQuery is loaded below.
    // We intentionally do not bind here to avoid referencing $ before it is available.

    // Image Modal functionality
    function showImageModal(imageSrc, imageTitle) {
      // Create modal if it doesn't exist
      if (!document.getElementById('imageModal')) {
        const modalHTML = `
          <div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="imageModalTitle">Asset Image</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                  <img id="modalImage" src="" alt="Asset Image" class="img-fluid rounded shadow">
                </div>
              </div>
            </div>
          </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
      }
      
      // Update modal content
      document.getElementById('imageModalTitle').textContent = imageTitle;
      document.getElementById('modalImage').src = imageSrc;
      
      // Show modal
      const modal = new bootstrap.Modal(document.getElementById('imageModal'));
      modal.show();
    }

    // Make showImageModal globally available
    window.showImageModal = showImageModal;
  </script>

  <!-- Dependencies -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

</body>
</html>
