<?php
require_once '../connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: ../index.php");
  exit();
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
  <?php include 'includes/sidebar.php'; ?>

  <div class="main">
    <?php include 'includes/topbar.php'; ?>

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

          <!-- Asset Details (after scan) -->
          <?php
          if (isset($_GET['asset_id']) && is_numeric($_GET['asset_id'])):
            $asset_id = $_GET['asset_id'];

            $stmt = $conn->prepare("
              SELECT a.*, c.category_name, o.office_name, e.name as employee_name,
                     CASE WHEN rt.id IS NOT NULL THEN 1 ELSE 0 END as has_red_tag,
                     ii.iirup_id,
                     md.end_user
              FROM assets a
              LEFT JOIN categories c ON a.category = c.id
              LEFT JOIN offices o ON a.office_id = o.id
              LEFT JOIN employees e ON a.employee_id = e.employee_id
              LEFT JOIN red_tags rt ON rt.asset_id = a.id
              LEFT JOIN iirup_items ii ON ii.asset_id = a.id
              LEFT JOIN mr_details md ON a.id = md.asset_id
              WHERE a.id = ?
            ");
            $stmt->bind_param("i", $asset_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()):
          ?>
            <!-- Professional Asset Details Card -->
            <div class="row justify-content-center mt-4">
              <div class="col-lg-10">
                <div class="card shadow-lg border-0" style="border-radius: 20px; overflow: hidden;">
                  <!-- Header with Gradient -->
                  <div class="card-header text-white position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 2rem;">
                    <div class="row align-items-center">
                      <div class="col">
                        <h4 class="mb-1 fw-bold">
                          <i class="bi bi-box-seam me-2"></i>
                          Asset Information
                        </h4>
                        <p class="mb-0 opacity-75">Complete asset details and specifications</p>
                      </div>
                      <div class="col-auto">
                        <div class="badge bg-white text-primary px-3 py-2 fs-6">
                          <i class="bi bi-qr-code me-1"></i>
                          ID: <?= htmlspecialchars($asset_id) ?>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="card-body p-4">
                    <div class="row g-4">
                      <!-- Asset Images Section -->
                      <div class="col-lg-4">
                        <div class="card border-0 bg-light h-100" style="border-radius: 15px;">
                          <div class="card-header bg-transparent border-0 pb-2">
                            <h6 class="mb-0 text-primary fw-semibold">
                              <i class="bi bi-images me-2"></i>Asset Gallery
                            </h6>
                          </div>
                          <div class="card-body pt-0">
                            <?php if (!empty($row['image'])): ?>
                              <div class="mb-3">
                                <div class="position-relative">
                                  <img src="../img/assets/<?= htmlspecialchars($row['image']) ?>" 
                                       alt="Asset Image" 
                                       class="img-fluid rounded shadow-sm w-100" 
                                       style="height: 200px; object-fit: cover; cursor: pointer;"
                                       onclick="showImageModal('../img/assets/<?= htmlspecialchars($row['image']) ?>', 'Main Asset Image')">
                                  <div class="position-absolute top-0 start-0 m-2">
                                    <span class="badge bg-primary shadow-sm">
                                      <i class="bi bi-image me-1"></i>Main
                                    </span>
                                  </div>
                                </div>
                              </div>
                            <?php endif; ?>

                            <?php 
                            // Process additional images (stored as JSON)
                            $additional_images = [];
                            if (!empty($row['additional_images'])) {
                                $additional_images = json_decode($row['additional_images'], true);
                                if (!is_array($additional_images)) {
                                    $additional_images = [];
                                }
                            }
                            ?>
                            
                            <?php if (!empty($additional_images)): ?>
                              <div class="row g-2">
                                <?php foreach ($additional_images as $index => $imageName): ?>
                                  <div class="col-6">
                                    <div class="position-relative">
                                      <img src="../img/assets/<?= htmlspecialchars($imageName) ?>" 
                                           alt="Additional Image <?= $index + 1 ?>" 
                                           class="img-fluid rounded shadow-sm w-100" 
                                           style="height: 80px; object-fit: cover; cursor: pointer;"
                                           onclick="showImageModal('../img/assets/<?= htmlspecialchars($imageName) ?>', 'Additional Image <?= $index + 1 ?>')">
                                      <div class="position-absolute top-0 start-0 m-1">
                                        <span class="badge bg-info shadow-sm" style="font-size: 0.7rem;">
                                          <?= $index + 1 ?>
                                        </span>
                                      </div>
                                    </div>
                                  </div>
                                <?php endforeach; ?>
                              </div>
                            <?php endif; ?>

                            <?php if (empty($row['image']) && empty($additional_images)): ?>
                              <div class="text-center py-4">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                                <p class="text-muted mt-2 mb-0">No images available</p>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>

                      <!-- Asset Details Section -->
                      <div class="col-lg-8">
                        <div class="row g-4">
                          <!-- Basic Information -->
                          <div class="col-md-6">
                            <div class="card border-0 bg-light h-100" style="border-radius: 15px;">
                              <div class="card-header bg-transparent border-0 pb-2">
                                <h6 class="mb-0 text-primary fw-semibold">
                                  <i class="bi bi-info-circle me-2"></i>Basic Information
                                </h6>
                              </div>
                              <div class="card-body pt-0">
                                <div class="row g-2">
                                  <div class="col-12">
                                    <small class="text-muted">Category</small>
                                    <p class="mb-2 fw-semibold"><?= htmlspecialchars($row['category_name']) ?></p>
                                  </div>
                                  <div class="col-12">
                                    <small class="text-muted">Description</small>
                                    <p class="mb-2"><?= htmlspecialchars($row['description']) ?></p>
                                  </div>
                                  <div class="col-6">
                                    <small class="text-muted">Brand</small>
                                    <p class="mb-2 fw-semibold"><?= htmlspecialchars($row['brand'] ?? 'Not Specified') ?></p>
                                  </div>
                                  <div class="col-6">
                                    <small class="text-muted">Model</small>
                                    <p class="mb-2 fw-semibold"><?= htmlspecialchars($row['model'] ?? 'Not Specified') ?></p>
                                  </div>
                                  <div class="col-6">
                                    <small class="text-muted">Serial Number</small>
                                    <p class="mb-2"><?= htmlspecialchars($row['serial_no'] ?? 'Not Specified') ?></p>
                                  </div>
                                  <div class="col-6">
                                    <small class="text-muted">Code</small>
                                    <p class="mb-2"><?= htmlspecialchars($row['code'] ?? 'Not Specified') ?></p>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- Asset Status & Details -->
                          <div class="col-md-6">
                            <div class="card border-0 bg-light h-100" style="border-radius: 15px;">
                              <div class="card-header bg-transparent border-0 pb-2">
                                <h6 class="mb-0 text-primary fw-semibold">
                                  <i class="bi bi-gear me-2"></i>Asset Status & Details
                                </h6>
                              </div>
                              <div class="card-body pt-0">
                                <div class="row g-2">
                                  <div class="col-12">
                                    <small class="text-muted">Status</small>
                                    <p class="mb-2">
                                      <span class="badge bg-<?= $row['status'] === 'available' ? 'success' : ($row['status'] === 'borrowed' ? 'warning' : ($row['status'] === 'unserviceable' ? 'danger' : 'secondary')) ?> px-3 py-2">
                                        <i class="bi bi-<?= $row['status'] === 'available' ? 'check-circle' : ($row['status'] === 'borrowed' ? 'clock' : ($row['status'] === 'unserviceable' ? 'x-circle' : 'question-circle')) ?> me-1"></i>
                                        <?= $row['has_red_tag'] ? 'Red-Tagged' : ucfirst($row['status']) ?>
                                      </span>
                                    </p>
                                  </div>
                                  <div class="col-6">
                                    <small class="text-muted">Quantity</small>
                                    <p class="mb-2 fw-semibold"><?= $row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?></p>
                                  </div>
                                  <div class="col-6">
                                    <small class="text-muted">Value</small>
                                    <p class="mb-2 fw-semibold text-success">₱<?= number_format($row['value'], 2) ?></p>
                                  </div>
                                  <div class="col-12">
                                    <small class="text-muted">Property Number</small>
                                    <p class="mb-2"><?= htmlspecialchars($row['property_no'] ?? 'Not Assigned') ?></p>
                                  </div>
                                  <div class="col-12">
                                    <small class="text-muted">Inventory Tag</small>
                                    <p class="mb-2"><?= htmlspecialchars($row['inventory_tag'] ?? 'Not Assigned') ?></p>
                                  </div>
                                  <div class="col-12">
                                    <small class="text-muted">Acquired On</small>
                                    <p class="mb-2"><?= date('F j, Y', strtotime($row['acquisition_date'])) ?></p>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>

                          <!-- Assignment Information -->
                          <div class="col-12">
                            <div class="card border-0 bg-light" style="border-radius: 15px;">
                              <div class="card-header bg-transparent border-0 pb-2">
                                <h6 class="mb-0 text-primary fw-semibold">
                                  <i class="bi bi-people me-2"></i>Assignment Information
                                </h6>
                              </div>
                              <div class="card-body pt-0">
                                <div class="row g-3">
                                  <div class="col-md-4">
                                    <small class="text-muted">Office</small>
                                    <p class="mb-0 fw-semibold"><?= htmlspecialchars($row['office_name']) ?></p>
                                  </div>
                                  <div class="col-md-4">
                                    <small class="text-muted">Person Accountable</small>
                                    <p class="mb-0 fw-semibold"><?= htmlspecialchars($row['employee_name'] ?? 'Not Assigned') ?></p>
                                  </div>
                                  <div class="col-md-4">
                                    <small class="text-muted">End User</small>
                                    <p class="mb-0 fw-semibold"><?= htmlspecialchars($row['end_user'] ?? 'Not Assigned') ?></p>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Action Buttons -->
                  <div class="card-footer bg-white border-0 pt-0">
                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                      <!-- Primary Actions -->
                      <a href="inventory.php" class="btn btn-outline-primary btn-lg px-4">
                        <i class="bi bi-list-ul me-2"></i>View in Inventory
                      </a>
                      
                      <?php if ($row['status'] === 'available'): ?>
                        <a href="create_mr.php?asset_id=<?= $asset_id ?>" class="btn btn-success btn-lg px-4">
                          <i class="bi bi-file-earmark-plus me-2"></i>Create MR
                        </a>
                        
                        <!-- Transfer Button -->
                        <button class="btn btn-info btn-lg px-4 transfer-asset" 
                                data-asset-id="<?= $asset_id ?>" 
                                data-inventory-tag="<?= htmlspecialchars($row['inventory_tag'] ?? '') ?>" 
                                data-current-employee-id="<?= $row['employee_id'] ?? '' ?>">
                          <i class="bi bi-arrow-left-right me-2"></i>Transfer Asset
                        </button>
                      <?php endif; ?>

                      <?php if ($row['status'] === 'available' && !$row['has_red_tag']): ?>
                        <a href="forms.php?id=7&asset_id=<?= $asset_id ?>" class="btn btn-warning btn-lg px-4">
                          <i class="bi bi-exclamation-triangle me-2"></i>Create IIRUP
                        </a>
                      <?php endif; ?>

                      <?php if ($row['has_red_tag']): ?>
                        <span class="btn btn-danger btn-lg px-4 disabled">
                          <i class="bi bi-tag me-2"></i>Red Tagged
                        </span>
                      <?php elseif ($row['status'] === 'unserviceable' && !empty($row['iirup_id'])): ?>
                        <a href="create_red_tag.php?asset_id=<?= $asset_id ?>&iirup_id=<?= $row['iirup_id'] ?>" class="btn btn-danger btn-lg px-4">
                          <i class="bi bi-tag me-2"></i>Red Tag
                        </a>
                      <?php endif; ?>

                      <!-- Secondary Actions -->
                      <a href="scan_qr.php" class="btn btn-outline-secondary btn-lg px-4">
                        <i class="bi bi-qr-code-scan me-2"></i>Scan Another
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            </div>

          <?php else: ?>
            <div class="alert alert-warning text-center">
              <i class="bi bi-exclamation-triangle me-2"></i>
              No asset found with ID <?= htmlspecialchars($asset_id) ?>.
            </div>
          <?php endif; $stmt->close(); endif; ?>

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
        
        this.updateStatus(`✓ Scanned Asset ID: ${assetId} - Redirecting...`, 'success');
        
        // Add success animation
        this.elements.reader.style.transform = 'scale(1.05)';
        setTimeout(() => {
          this.elements.reader.style.transform = 'scale(1)';
        }, 200);

        // Stop scanning and redirect
        this.stopScanning();
        setTimeout(() => {
          window.location.href = `scan_qr.php?asset_id=${assetId}`;
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

  <script>
    // Transfer Asset functionality
    $(document).ready(function() {
      $(document).on('click', '.transfer-asset', function (e) {
        e.preventDefault();
        console.log('Transfer button clicked'); // Debug log
        
        // Get asset data
        const assetId = $(this).data('asset-id');
        const inventoryTag = $(this).data('inventory-tag');
        const currentEmployeeId = $(this).data('current-employee-id');
        
        console.log('Asset data:', { assetId, inventoryTag, currentEmployeeId }); // Debug log
        
        // Redirect to forms.php with ITR form ID 9 and asset parameters
        // URL-encode values to be safe
        const ITR_FORM_ID = 9;
        const url = `forms.php?id=${ITR_FORM_ID}`
          + `&asset_id=${encodeURIComponent(assetId)}`
          + `&inventory_tag=${encodeURIComponent(inventoryTag || '')}`
          + `&current_employee_id=${encodeURIComponent(currentEmployeeId || '')}`;
        console.log('Redirecting to:', url); // Debug log
        
        window.location.href = url;
      });
    });
  </script>
</body>
</html>
