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
  <title>Scan QR</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="css/dashboard.css" />

  <style>
    #reader {
      width: 100%;
      max-width: 700px;
      margin: auto;
      border: 3px solid #0d6efd;
      border-radius: 15px;
      padding: 5px;
      background: #ffffff;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      position: relative;
      min-height: 500px;
    }
    
    /* Force the video to be large and visible */
    #reader video {
      width: 100% !important;
      height: auto !important;
      min-height: 400px !important;
      max-height: 500px !important;
      border-radius: 10px;
      object-fit: cover;
      display: block !important;
    }
    
    /* Override html5-qrcode default styles */
    #reader > div {
      width: 100% !important;
      height: auto !important;
    }
    
    #reader__scan_region {
      width: 100% !important;
      height: auto !important;
      min-height: 400px !important;
    }
    
    #reader__scan_region video {
      width: 100% !important;
      height: auto !important;
      min-height: 400px !important;
      max-height: 500px !important;
      object-fit: cover !important;
    }
    
    #reader__dashboard_section {
      display: none !important;
    }
    
    #reader__camera_selection {
      display: none !important;
    }
    
    #scan-result {
      text-align: center;
      font-size: 1.2rem;
      margin-top: 1rem;
      min-height: 30px;
      padding: 10px;
      border-radius: 8px;
    }
    
    .scanner-controls {
      text-align: center;
      margin: 20px 0;
    }
    
    .scanner-status {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 10px;
      margin: 15px 0;
      font-size: 14px;
    }
    
    .status-indicator {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      display: inline-block;
    }
    
    .status-active { background-color: #28a745; }
    .status-inactive { background-color: #dc3545; }
    .status-scanning { background-color: #ffc107; animation: pulse 1s infinite; }
    
    @keyframes pulse {
      0% { opacity: 1; }
      50% { opacity: 0.5; }
      100% { opacity: 1; }
    }
    
    .scan-overlay {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 300px;
      height: 300px;
      border: 3px solid #0d6efd;
      border-radius: 15px;
      pointer-events: none;
      z-index: 10;
      box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.3);
    }
    
    .scan-corners {
      position: absolute;
      width: 40px;
      height: 40px;
      border: 4px solid #0d6efd;
    }
    
    .corner-tl { top: -4px; left: -4px; border-right: none; border-bottom: none; }
    .corner-tr { top: -4px; right: -4px; border-left: none; border-bottom: none; }
    .corner-bl { bottom: -4px; left: -4px; border-right: none; border-top: none; }
    .corner-br { bottom: -4px; right: -4px; border-left: none; border-top: none; }
    
    .quick-actions {
      display: flex;
      justify-content: center;
      gap: 10px;
      margin: 20px 0;
      flex-wrap: wrap;
    }
    
    .loading-spinner {
      display: none;
      text-align: center;
      margin: 20px 0;
    }
    
    .scanner-tips {
      background: #e3f2fd;
      border: 1px solid #2196f3;
      border-radius: 8px;
      padding: 15px;
      margin: 20px 0;
      font-size: 14px;
    }
    
    .success-animation {
      animation: successPulse 0.6s ease-in-out;
    }
    
    @keyframes successPulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
  </style>
</head>

<body>
<?php include 'includes/sidebar.php'; ?>

<div class="main">
  <?php include 'includes/topbar.php'; ?>

  <div class="container py-4">
    <div class="row justify-content-center">
      <div class="col-md-10 col-lg-8 text-center">
        <h3 class="mb-3"><i class="bi bi-qr-code-scan"></i> QR Code Scanner</h3>
        
        <!-- Scanner Status -->
        <div class="scanner-status">
          <span class="status-indicator" id="statusIndicator"></span>
          <span id="statusText">Initializing camera...</span>
        </div>
        
        <!-- Scanner Container -->
        <div style="position: relative; display: inline-block;">
          <div id="reader"></div>
          <div class="scan-overlay" id="scanOverlay" style="display: none;">
            <div class="scan-corners corner-tl"></div>
            <div class="scan-corners corner-tr"></div>
            <div class="scan-corners corner-bl"></div>
            <div class="scan-corners corner-br"></div>
          </div>
        </div>
        
        <!-- Scanner Controls -->
        <div class="scanner-controls">
          <button id="toggleScanBtn" class="btn btn-primary me-2" disabled>
            <i class="bi bi-play-fill"></i> Start Scanning
          </button>
          <button id="switchCameraBtn" class="btn btn-outline-secondary me-2" style="display: none;">
            <i class="bi bi-camera-reels"></i> Switch Camera
          </button>
          <button id="resetScanBtn" class="btn btn-outline-warning me-2">
            <i class="bi bi-arrow-clockwise"></i> Reset
          </button>
          <button id="retryInitBtn" class="btn btn-outline-info me-2" style="display: none;">
            <i class="bi bi-bootstrap-reboot"></i> Retry Camera
          </button>
          <button id="reinitScanBtn" class="btn btn-outline-success" onclick="reinitializeScanner()">
            <i class="bi bi-arrow-repeat"></i> Reinitialize
          </button>
        </div>
        
        <!-- Scan Result -->
        <div id="scan-result" class="fw-bold"></div>
        
        <!-- Loading Spinner -->
        <div class="loading-spinner" id="loadingSpinner">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Processing scanned asset...</p>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
          <button class="btn btn-sm btn-outline-info" onclick="showScannerTips()">
            <i class="bi bi-info-circle"></i> Scanning Tips
          </button>
          <button class="btn btn-sm btn-outline-secondary" onclick="toggleFullscreen()">
            <i class="bi bi-fullscreen"></i> Fullscreen
          </button>
        </div>
        
        <!-- Scanner Tips -->
        <div class="scanner-tips" id="scannerTips" style="display: none;">
          <h6><i class="bi bi-lightbulb"></i> Scanning Tips & Troubleshooting:</h6>
          <div class="row">
            <div class="col-md-6">
              <strong>Scanning Tips:</strong>
              <ul class="text-start">
                <li>Hold the QR code steady within the scanning area</li>
                <li>Ensure good lighting for better detection</li>
                <li>Keep the QR code flat and unobstructed</li>
                <li>Try different distances if scanning fails</li>
                <li>Use keyboard shortcut: <kbd>Space</kbd> to toggle scanning</li>
              </ul>
            </div>
            <div class="col-md-6">
              <strong>Camera Issues:</strong>
              <ul class="text-start">
                <li>Make sure you're using <strong>HTTPS</strong> (not HTTP)</li>
                <li>Allow camera permission when prompted</li>
                <li>Close other apps using the camera</li>
                <li>Try refreshing the page</li>
                <li>Check if camera works in other apps</li>
                <li>Try a different browser (Chrome/Firefox)</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Asset Details (after scan) -->
    <?php
    if (isset($_GET['asset_id']) && is_numeric($_GET['asset_id'])):
      $asset_id = $_GET['asset_id'];

      $stmt = $conn->prepare("
        SELECT a.*, c.category_name, o.office_name, e.name AS employee_name,
               ics.ics_no, par.par_no
        FROM assets a
        LEFT JOIN categories c ON a.category = c.id
        LEFT JOIN offices o ON a.office_id = o.id
        LEFT JOIN employees e ON a.employee_id = e.employee_id
        LEFT JOIN ics_form ics ON a.ics_id = ics.id
        LEFT JOIN par_form par ON a.par_id = par.id
        WHERE a.id = ?
      ");
      $stmt->bind_param("i", $asset_id);
      $stmt->execute();
      $result = $stmt->get_result();

      if ($row = $result->fetch_assoc()):
    ?>
      <?php
        // Determine which form number to display based on asset value
        $asset_value = (float)$row['value'];
        $form_number = '';
        $form_type = '';
        
        if ($asset_value < 50000) {
          // Show ICS number for assets below ₱50,000
          if (!empty($row['ics_no'])) {
            $form_number = $row['ics_no'];
            $form_type = 'ICS No.';
          }
        } else {
          // Show PAR number for assets ₱50,000 and above
          if (!empty($row['par_no'])) {
            $form_number = $row['par_no'];
            $form_type = 'PAR No.';
          }
        }
      ?>
      
      <div class="card mt-4 shadow-sm mx-auto" style="max-width: 800px;">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0"><i class="bi bi-box-seam"></i> Asset Information</h5>
        </div>
        <div class="card-body">
          <div class="row g-4">
            <!-- Main Asset Information -->
            <div class="col-lg-8">
              <div class="row g-3">
                <!-- Basic Information -->
                <div class="col-12">
                  <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-info-circle"></i> Basic Information
                  </h6>
                  <div class="row g-2">
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Asset Name</small>
                        <strong><?= htmlspecialchars($row['asset_name'] ?? $row['description']) ?></strong>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Category</small>
                        <strong><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></strong>
                      </div>
                    </div>
                    <div class="col-12">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Description</small>
                        <strong><?= htmlspecialchars($row['description']) ?></strong>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Status and Value -->
                <div class="col-12">
                  <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-clipboard-check"></i> Status & Value
                  </h6>
                  <div class="row g-2">
                    <div class="col-md-4">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-<?= $row['status'] === 'available' ? 'success' : ($row['status'] === 'borrowed' ? 'warning' : 'secondary') ?> fs-6">
                          <?= $row['red_tagged'] ? 'Red-Tagged' : ucfirst($row['status']) ?>
                        </span>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Value</small>
                        <strong class="text-success">₱<?= number_format($asset_value, 2) ?></strong>
                      </div>
                    </div>
                    <div class="col-md-4">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Quantity</small>
                        <strong><?= (int)$row['quantity'] ?> <?= htmlspecialchars($row['unit']) ?></strong>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Form and Property Information -->
                <div class="col-12">
                  <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-file-earmark-text"></i> Form & Property Details
                  </h6>
                  <div class="row g-2">
                    <?php if ($form_number): ?>
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block"><?= $form_type ?></small>
                        <strong class="text-info"><?= htmlspecialchars($form_number) ?></strong>
                      </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($row['property_no'])): ?>
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Property No.</small>
                        <strong><?= htmlspecialchars($row['property_no']) ?></strong>
                      </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($row['inventory_tag'])): ?>
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Inventory Tag</small>
                        <strong><?= htmlspecialchars($row['inventory_tag']) ?></strong>
                      </div>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>

                <!-- Technical Specifications -->
                <?php if (!empty($row['brand']) || !empty($row['model']) || !empty($row['serial_no']) || !empty($row['type'])): ?>
                <div class="col-12">
                  <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-gear"></i> Technical Specifications
                  </h6>
                  <div class="row g-2">
                    <?php if (!empty($row['brand'])): ?>
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Brand</small>
                        <strong><?= htmlspecialchars($row['brand']) ?></strong>
                      </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($row['model'])): ?>
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Model</small>
                        <strong><?= htmlspecialchars($row['model']) ?></strong>
                      </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($row['serial_no'])): ?>
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Serial No.</small>
                        <strong><?= htmlspecialchars($row['serial_no']) ?></strong>
                      </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($row['type'])): ?>
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Type</small>
                        <strong><?= htmlspecialchars($row['type']) ?></strong>
                      </div>
                    </div>
                    <?php endif; ?>
                  </div>
                </div>
                <?php endif; ?>

                <!-- Assignment Information -->
                <div class="col-12">
                  <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-person-badge"></i> Assignment Information
                  </h6>
                  <div class="row g-2">
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Office</small>
                        <strong><?= htmlspecialchars($row['office_name'] ?? 'Not Assigned') ?></strong>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Person Accountable</small>
                        <strong><?= htmlspecialchars($row['employee_name'] ?? 'Not Assigned') ?></strong>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Date Information -->
                <div class="col-12">
                  <h6 class="text-primary border-bottom pb-2 mb-3">
                    <i class="bi bi-calendar"></i> Date Information
                  </h6>
                  <div class="row g-2">
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Date Acquired</small>
                        <strong><?= $row['acquisition_date'] ? date('F j, Y', strtotime($row['acquisition_date'])) : 'Not Available' ?></strong>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="p-2 bg-light rounded">
                        <small class="text-muted d-block">Last Updated</small>
                        <strong><?= $row['last_updated'] ? date('F j, Y g:i A', strtotime($row['last_updated'])) : 'Not Available' ?></strong>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Images Section -->
            <div class="col-lg-4">
              <h6 class="text-primary border-bottom pb-2 mb-3">
                <i class="bi bi-images"></i> Asset Images
              </h6>
              <div class="text-center">
                <?php if (!empty($row['image'])): ?>
                  <div class="mb-3">
                    <small class="text-muted d-block mb-2">Asset Photo</small>
                    <img src="../img/<?= htmlspecialchars($row['image']) ?>" alt="Asset Image" 
                         class="img-fluid border rounded shadow-sm" style="max-height: 200px; object-fit: contain;">
                  </div>
                <?php endif; ?>
                
                <?php if (!empty($row['qr_code'])): ?>
                  <div class="mb-3">
                    <small class="text-muted d-block mb-2">QR Code</small>
                    <img src="../img/<?= htmlspecialchars($row['qr_code']) ?>" alt="QR Code" 
                         class="img-fluid border rounded shadow-sm" style="max-height: 150px; object-fit: contain;">
                  </div>
                <?php endif; ?>
                
                <?php if (empty($row['image']) && empty($row['qr_code'])): ?>
                  <div class="text-muted p-4 border rounded bg-light">
                    <i class="bi bi-image display-4 d-block mb-2"></i>
                    <small>No images available</small>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <hr>
          <div class="d-flex justify-content-between flex-wrap gap-2 mt-3">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#transferAssetModal">
              <i class="bi bi-arrow-left-right"></i> Transfer
            </button>
            <a href="borrow_asset.php?id=<?= $row['id'] ?>" class="btn btn-outline-warning btn-sm rounded-pill">
              <i class="bi bi-box-arrow-in-right"></i> Borrow
            </a>
            <a href="return_asset.php?id=<?= $row['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-pill">
              <i class="bi bi-box-arrow-in-left"></i> Return
            </a>
            <?php if ($row['red_tagged'] == 1): ?>
              <button type="button" class="btn btn-danger btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#alreadyRedTaggedModal">
                <i class="bi bi-tag-fill"></i> Already Red Tagged
              </button>
            <?php else: ?>
              <a href="forms.php?id=7&asset_id=<?= $row['id'] ?>&asset_description=<?= urlencode($row['description']) ?>&inventory_tag=<?= urlencode($row['inventory_tag'] ?? $row['property_no'] ?? '') ?>" class="btn btn-danger btn-sm rounded-pill">
                <i class="bi bi-tag"></i> Red Tag
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php
        // Preload employees for modal selection
        $emp_res = $conn->query("SELECT employee_id, name FROM employees ORDER BY name ASC");
        $employees = $emp_res ? $emp_res->fetch_all(MYSQLI_ASSOC) : [];
        $inventory_tag_value = $row['inventory_tag'] ?? ($row['property_no'] ?? '');
      ?>

      <!-- Transfer Asset Modal -->
      <div class="modal fade" id="transferAssetModal" tabindex="-1" aria-labelledby="transferAssetModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="transferAssetModalLabel">Transfer Asset to New Person Accountable</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="transfer_asset.php">
              <div class="modal-body">
                <input type="hidden" name="asset_id" value="<?= (int)$row['id'] ?>">
                <input type="hidden" name="inventory_tag" value="<?= htmlspecialchars($inventory_tag_value) ?>">

                <div class="mb-3">
                  <label for="newEmployee" class="form-label">Select New Person Accountable</label>
                  <input list="employeeList" class="form-control" id="newEmployee" name="new_employee" placeholder="Type to search... (e.g., 12 - Juan Dela Cruz)" required>
                  <datalist id="employeeList">
                    <?php foreach ($employees as $emp): ?>
                      <option value="<?= (int)$emp['employee_id'] . ' - ' . htmlspecialchars($emp['name']) ?>"></option>
                    <?php endforeach; ?>
                  </datalist>
                  <div class="form-text">Format required: "employee_id - employee name"</div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Confirm Transfer</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- Already Red Tagged Modal -->
      <div class="modal fade" id="alreadyRedTaggedModal" tabindex="-1" aria-labelledby="alreadyRedTaggedModalLabel" aria-hidden="true">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-warning">
              <h5 class="modal-title" id="alreadyRedTaggedModalLabel">
                <i class="bi bi-exclamation-triangle"></i> Asset Already Red Tagged
              </h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <div class="alert alert-warning mb-3">
                <i class="bi bi-tag-fill"></i> <strong>Notice:</strong> This asset is already red tagged.
              </div>
              <p><strong>Asset:</strong> <?= htmlspecialchars($row['description']) ?></p>
              <p><strong>Inventory Tag:</strong> <?= htmlspecialchars($row['inventory_tag'] ?? $row['property_no'] ?? 'N/A') ?></p>
              <p class="mb-3">This asset has already been marked as unserviceable and red tagged in the system.</p>
              
              <div class="d-grid gap-2">
                <a href="red_tag.php" class="btn btn-outline-primary">
                  <i class="bi bi-tags"></i> View All Red Tags
                </a>
                <a href="saved_iirup.php?id=7" class="btn btn-outline-secondary">
                  <i class="bi bi-folder-check"></i> View IIRUP Records
                </a>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
          </div>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-warning mt-4 text-center">No asset found with ID <?= htmlspecialchars($asset_id) ?>.</div>
    <?php endif; $stmt->close(); endif; ?>
  </div>
</div>

<!-- QR Code Scanner Script -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
  let html5QrCode;
  let cameras = [];
  let currentCameraIndex = 0;
  let isScanning = false;
  let scanCount = 0;
  let lastScanTime = 0;
  
  // Optimized scanner configuration
  const scannerConfig = {
    fps: 30, // Increased FPS for faster detection
    qrbox: function(viewfinderWidth, viewfinderHeight) {
      // Make the scan box responsive to the actual video size
      let minEdgePercentage = 0.5; // 50% of the smaller edge
      let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
      let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
      return {
        width: Math.max(qrboxSize, 250),
        height: Math.max(qrboxSize, 250)
      };
    },
    aspectRatio: 1.777778, // 16:9 aspect ratio for better video display
    disableFlip: false,
    videoConstraints: {
      facingMode: "environment", // Use back camera by default
      width: { ideal: 1280, min: 640 },
      height: { ideal: 720, min: 480 },
      advanced: [{
        focusMode: "continuous",
        exposureMode: "continuous",
        whiteBalanceMode: "continuous"
      }]
    },
    rememberLastUsedCamera: true,
    supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA]
  };
  
  function updateStatus(text, type = 'info') {
    const statusText = document.getElementById('statusText');
    const statusIndicator = document.getElementById('statusIndicator');
    
    statusText.textContent = text;
    statusIndicator.className = `status-indicator status-${type}`;
  }
  
  function showScanResult(message, type = 'success') {
    const resultDiv = document.getElementById('scan-result');
    resultDiv.className = `fw-bold text-${type === 'success' ? 'success' : 'danger'}`;
    resultDiv.textContent = message;
    
    if (type === 'success') {
      resultDiv.classList.add('success-animation');
      setTimeout(() => resultDiv.classList.remove('success-animation'), 600);
    }
  }
  
  // Force video to display at full size
  function forceVideoDisplay() {
    const videos = document.querySelectorAll('#reader video');
    videos.forEach(video => {
      video.style.width = '100%';
      video.style.height = 'auto';
      video.style.minHeight = '400px';
      video.style.maxHeight = '500px';
      video.style.objectFit = 'cover';
      video.style.display = 'block';
      video.style.borderRadius = '10px';
    });
    
    // Also force the container divs
    const scanRegions = document.querySelectorAll('#reader__scan_region, #reader > div');
    scanRegions.forEach(region => {
      region.style.width = '100%';
      region.style.height = 'auto';
      region.style.minHeight = '400px';
    });
    
    // Hide unwanted elements
    const dashboards = document.querySelectorAll('#reader__dashboard_section, #reader__camera_selection');
    dashboards.forEach(dashboard => {
      dashboard.style.display = 'none';
    });
  }
  
  function onScanSuccess(decodedText, decodedResult) {
    const currentTime = Date.now();
    
    // Prevent duplicate scans within 2 seconds
    if (currentTime - lastScanTime < 2000) {
      return;
    }
    
    lastScanTime = currentTime;
    scanCount++;
    
    const assetId = decodedText.trim();
    
    // Validate asset ID format
    if (!/^\d+$/.test(assetId)) {
      showScanResult(`Invalid QR code format: ${assetId}`, 'error');
      return;
    }
    
    // Stop scanning and show success
    html5QrCode.stop().then(() => {
      isScanning = false;
      updateToggleButton();
      updateStatus('Scan successful! Processing...', 'active');
    });
    
    showScanResult(`✓ Asset ID: ${assetId} (Scan #${scanCount})`, 'success');
    
    // Show loading spinner
    document.getElementById('loadingSpinner').style.display = 'block';
    
    // Redirect with slight delay for better UX
    setTimeout(() => {
      window.location.href = `scan_qr.php?asset_id=${assetId}`;
    }, 800);
  }
  
  function onScanError(errorMessage) {
    // Reduce console spam by only logging important errors
    if (errorMessage.includes('NotFoundException') === false) {
      console.warn(`QR scan error: ${errorMessage}`);
    }
  }
  
  async function startScanning() {
    if (cameras.length === 0) {
      updateStatus('No cameras available', 'inactive');
      return;
    }
    
    updateStatus('Starting camera...', 'scanning');
    
    try {
      await html5QrCode.start(
        cameras[currentCameraIndex].id,
        scannerConfig,
        onScanSuccess,
        onScanError
      );
      
      isScanning = true;
      updateStatus('Ready to scan', 'scanning');
      document.getElementById('scanOverlay').style.display = 'block';
      updateToggleButton();
      
      // Force video display immediately
      forceVideoDisplay();
      
    } catch (err) {
      console.error('Camera start error:', err);
      
      // Quick fallback attempt
      try {
        await html5QrCode.start(
          cameras[currentCameraIndex].id,
          { fps: 20, qrbox: 250 },
          onScanSuccess,
          onScanError
        );
        
        isScanning = true;
        updateStatus('Ready to scan (fallback mode)', 'scanning');
        document.getElementById('scanOverlay').style.display = 'block';
        updateToggleButton();
        forceVideoDisplay();
        
      } catch (fallbackErr) {
        let errorMessage = 'Failed to start camera';
        if (err.name === 'NotAllowedError') {
          errorMessage = 'Camera permission denied';
          showCameraPermissionHelp();
        } else if (err.name === 'NotFoundError') {
          errorMessage = 'Camera not found';
        } else if (err.name === 'NotReadableError') {
          errorMessage = 'Camera in use by another app';
        }
        
        updateStatus(errorMessage, 'inactive');
      }
    }
  }
  
  
  function stopScanning() {
    if (!isScanning) return;
    
    html5QrCode.stop().then(() => {
      isScanning = false;
      updateStatus('Scanner stopped', 'inactive');
      document.getElementById('scanOverlay').style.display = 'none';
      updateToggleButton();
    }).catch(err => {
      console.error('Stop scanning error:', err);
    });
  }
  
  function updateToggleButton() {
    const btn = document.getElementById('toggleScanBtn');
    if (isScanning) {
      btn.innerHTML = '<i class="bi bi-stop-fill"></i> Stop Scanning';
      btn.className = 'btn btn-danger me-2';
    } else {
      btn.innerHTML = '<i class="bi bi-play-fill"></i> Start Scanning';
      btn.className = 'btn btn-primary me-2';
    }
    btn.disabled = false;
  }
  
  function switchCamera() {
    if (cameras.length <= 1) return;
    
    stopScanning();
    currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
    
    setTimeout(() => {
      startScanning();
    }, 500);
  }
  
  function resetScanner() {
    console.log('Manual scanner reset requested');
    resetScannerState();
    
    // Reinitialize after reset
    setTimeout(() => {
      initializeScanner();
    }, 500);
    
    // Update scan counter display
    updateScanCounter();
  }
  
  function showScannerTips() {
    const tips = document.getElementById('scannerTips');
    tips.style.display = tips.style.display === 'none' ? 'block' : 'none';
  }
  
  function toggleFullscreen() {
    if (!document.fullscreenElement) {
      document.documentElement.requestFullscreen();
    } else {
      document.exitFullscreen();
    }
  }
  
  // Optimized fast camera permission request
  async function requestCameraPermission() {
    try {
      updateStatus('Accessing camera...', 'scanning');
      
      // Fast permission check - try environment camera first
      const stream = await navigator.mediaDevices.getUserMedia({ 
        video: { 
          facingMode: "environment",
          width: { ideal: 1280 },
          height: { ideal: 720 }
        } 
      });
      
      // Quick validation and cleanup
      if (stream.getVideoTracks().length === 0) {
        stream.getTracks().forEach(track => track.stop());
        throw new Error('No video tracks available');
      }
      
      // Stop immediately - we just needed permission
      stream.getTracks().forEach(track => track.stop());
      
      updateStatus('Camera ready', 'active');
      return true;
      
    } catch (err) {
      // Fast fallback - try any camera
      try {
        const fallbackStream = await navigator.mediaDevices.getUserMedia({ video: true });
        fallbackStream.getTracks().forEach(track => track.stop());
        updateStatus('Camera ready', 'active');
        return true;
      } catch (fallbackErr) {
        console.error('Camera permission error:', err);
        
        if (err.name === 'NotAllowedError') {
          updateStatus('Camera permission denied. Please allow camera access.', 'inactive');
          showCameraPermissionHelp();
        } else if (err.name === 'NotFoundError') {
          updateStatus('No camera found on this device', 'inactive');
        } else {
          updateStatus('Camera access failed', 'inactive');
        }
        
        return false;
      }
    }
  }
  
  // Show camera permission help
  function showCameraPermissionHelp() {
    const helpDiv = document.createElement('div');
    helpDiv.className = 'alert alert-warning mt-3';
    helpDiv.innerHTML = `
      <h6><i class="bi bi-exclamation-triangle"></i> Camera Permission Required</h6>
      <p class="mb-2">To scan QR codes, please:</p>
      <ol class="mb-2">
        <li>Click the camera icon in your browser's address bar</li>
        <li>Select "Allow" for camera access</li>
        <li>Refresh this page</li>
      </ol>
      <button class="btn btn-sm btn-primary" onclick="location.reload()">
        <i class="bi bi-arrow-clockwise"></i> Refresh Page
      </button>
      <button class="btn btn-sm btn-outline-secondary ms-2" onclick="this.parentElement.remove()">
        <i class="bi bi-x"></i> Dismiss
      </button>
    `;
    
    document.querySelector('.scanner-controls').after(helpDiv);
  }
  
  // Fast scanner initialization
  async function initializeScanner() {
    try {
      updateStatus('Starting scanner...', 'scanning');
      
      // Quick browser support check
      if (!navigator.mediaDevices?.getUserMedia) {
        throw new Error('Camera not supported in this browser');
      }
      
      // Fast camera permission check
      const hasPermission = await requestCameraPermission();
      if (!hasPermission) {
        throw new Error('Camera permission required');
      }
      
      // Quick cleanup of existing instance
      if (html5QrCode) {
        try {
          await html5QrCode.stop();
        } catch (e) {
          // Ignore cleanup errors
        }
      }
      
      // Fast scanner initialization
      html5QrCode = new Html5Qrcode("reader", { verbose: false });
      
      // Quick camera detection
      const detectedCameras = await Html5Qrcode.getCameras();
      cameras = detectedCameras;
      
      if (cameras.length === 0) {
        throw new Error('No cameras found');
      }
      
      updateStatus(`Ready - ${cameras.length} camera(s) found`, 'active');
      
      // Fast camera selection - prefer back camera
      currentCameraIndex = 0;
      for (let i = 0; i < cameras.length; i++) {
        const label = cameras[i].label.toLowerCase();
        if (label.includes('back') || label.includes('rear') || label.includes('environment')) {
          currentCameraIndex = i;
          break;
        }
      }
      
      // Show controls
      if (cameras.length > 1) {
        document.getElementById('switchCameraBtn').style.display = 'inline-block';
      }
      document.getElementById('retryInitBtn').style.display = 'none';
      
      // Start scanning immediately
      setTimeout(startScanning, 300);
      
    } catch (err) {
      console.error('Scanner initialization error:', err);
      
      let errorMessage = 'Scanner initialization failed';
      if (err.message.includes('permission') || err.name === 'NotAllowedError') {
        errorMessage = 'Camera permission denied';
        showCameraPermissionHelp();
      } else if (err.message.includes('No cameras') || err.name === 'NotFoundError') {
        errorMessage = 'No camera found';
      } else if (err.message.includes('not supported')) {
        errorMessage = 'Camera not supported in this browser';
      }
      
      updateStatus(errorMessage, 'inactive');
      document.getElementById('retryInitBtn').style.display = 'inline-block';
    }
  }
  
  // Reset scanner state completely
  function resetScannerState() {
    console.log('Resetting scanner state...');
    
    // Stop any existing scanner
    if (html5QrCode && isScanning) {
      try {
        html5QrCode.stop().catch(() => {});
      } catch (e) {
        // Ignore stop errors
      }
    }
    
    // Clear scanner instance
    if (html5QrCode) {
      try {
        html5QrCode.clear().catch(() => {});
      } catch (e) {
        // Ignore clear errors
      }
      html5QrCode = null;
    }
    
    // Reset all state variables
    cameras = [];
    currentCameraIndex = 0;
    isScanning = false;
    scanCount = 0;
    lastScanTime = 0;
    
    // Reset UI elements
    document.getElementById('scanOverlay').style.display = 'none';
    document.getElementById('switchCameraBtn').style.display = 'none';
    document.getElementById('retryInitBtn').style.display = 'none';
    
    // Clear any existing help messages
    const existingHelp = document.querySelector('.alert-warning');
    if (existingHelp) {
      existingHelp.remove();
    }
    
    // Reset button states
    updateToggleButton();
    
    // Clear scan results
    const resultDiv = document.getElementById('scan-result');
    if (resultDiv) {
      resultDiv.textContent = '';
      resultDiv.className = 'fw-bold';
    }
    
    updateStatus('Resetting scanner...', 'scanning');
  }
  
  // Retry camera initialization
  function retryInitialization() {
    resetScannerState();
    setTimeout(initializeScanner, 500);
  }
  
  // Manual reinitialize function for the button
  function reinitializeScanner() {
    console.log('Manual reinitialize requested');
    resetScannerState();
    setTimeout(initializeScanner, 300);
  }
  
  // Event listeners
  document.getElementById('toggleScanBtn').addEventListener('click', () => {
    if (isScanning) {
      stopScanning();
    } else {
      startScanning();
    }
  });
  
  document.getElementById('switchCameraBtn').addEventListener('click', switchCamera);
  document.getElementById('resetScanBtn').addEventListener('click', resetScanner);
  document.getElementById('retryInitBtn').addEventListener('click', retryInitialization);
  
  // Keyboard shortcuts
  document.addEventListener('keydown', (e) => {
    if (e.code === 'Space') {
      e.preventDefault();
      if (isScanning) {
        stopScanning();
      } else {
        startScanning();
      }
    } else if (e.code === 'KeyR' && e.ctrlKey) {
      e.preventDefault();
      resetScanner();
    } else if (e.code === 'KeyC' && e.ctrlKey) {
      e.preventDefault();
      switchCamera();
    }
  });
  
  // Fast page load initialization
  function startImmediately() {
    // Check if this is a navigation return (like back button)
    if (performance.navigation && performance.navigation.type === 2) {
      console.log('Page loaded from navigation - doing full reset');
      resetScannerState();
    }
    
    // Start as soon as possible
    initializeScanner();
  }
  
  // Immediate initialization - don't wait
  if (document.readyState === 'loading') {
    // DOM still loading - wait for it
    document.addEventListener('DOMContentLoaded', startImmediately);
  } else {
    // DOM ready - start immediately
    startImmediately();
  }
  
  // Handle browser back/forward navigation
  window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
      // Page was loaded from cache (back/forward navigation)
      console.log('Page loaded from cache - reinitializing scanner');
      setTimeout(() => {
        resetScannerState();
        initializeScanner();
      }, 100);
    }
  });
  
  
  // Lightweight periodic check for video health
  setInterval(() => {
    if (isScanning) {
      forceVideoDisplay();
    }
  }, 10000); // Check every 10 seconds instead of 5
  
  // Cleanup on page unload
  window.addEventListener('beforeunload', () => {
    if (isScanning && html5QrCode) {
      try {
        html5QrCode.stop();
      } catch (e) {
        console.log('Cleanup error:', e.message);
      }
    }
  });
  
  // Enhanced page focus/blur handling for proper re-initialization
  window.addEventListener('focus', () => {
    console.log('Page focused - checking scanner state');
    
    // Always attempt to reinitialize when page regains focus
    setTimeout(() => {
      if (!isScanning) {
        console.log('Scanner not running, reinitializing...');
        resetScannerState();
        initializeScanner();
      } else {
        // Scanner is running, but verify it's actually working
        const videos = document.querySelectorAll('#reader video');
        if (videos.length === 0) {
          console.log('Scanner running but no video found, reinitializing...');
          resetScannerState();
          initializeScanner();
        }
      }
    }, 500);
  });
  
  window.addEventListener('blur', () => {
    // Clean up when page loses focus to prevent resource conflicts
    console.log('Page lost focus - cleaning up scanner');
    if (isScanning && html5QrCode) {
      try {
        html5QrCode.stop().catch(() => {
          // Ignore stop errors when page is losing focus
        });
        isScanning = false;
      } catch (e) {
        // Ignore cleanup errors
      }
    }
  });
  
  // Handle page visibility changes (when user switches tabs or minimizes)
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      // Page hidden - pause scanner
      if (isScanning && html5QrCode) {
        try {
          html5QrCode.stop().catch(() => {});
          isScanning = false;
          updateStatus('Scanner paused', 'inactive');
        } catch (e) {
          // Ignore errors
        }
      }
    } else {
      // Page visible - reinitialize scanner
      console.log('Page became visible - reinitializing scanner');
      setTimeout(() => {
        resetScannerState();
        initializeScanner();
      }, 300);
    }
  });
</script>

<!-- Dependencies -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
</body>
</html>
