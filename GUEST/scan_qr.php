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

// Handle asset lookup if QR code is scanned
$asset_data = null;
$error_message = null;

if (isset($_GET['asset_id']) && !empty($_GET['asset_id'])) {
    $asset_id = intval($_GET['asset_id']);
    
    $stmt = $conn->prepare("
        SELECT a.*, c.category_name, o.office_name, e.name as employee_name
        FROM assets a
        LEFT JOIN categories c ON c.id = a.category
        LEFT JOIN offices o ON o.id = a.office_id
        LEFT JOIN employees e ON e.employee_id = a.employee_id
        WHERE a.id = ?
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $asset_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $asset_data = $result->fetch_assoc();
        } else {
            $error_message = "Asset not found.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - <?= htmlspecialchars($system['system_title']) ?></title>
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0b5ed7;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
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

        .scanner-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 2rem;
            margin: 2rem 0;
        }

        .video-container {
            position: relative;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            margin: 1rem 0;
        }

        #qr-video {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .scanner-overlay {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 200px;
            height: 200px;
            border: 3px solid var(--primary-color);
            border-radius: 10px;
            pointer-events: none;
        }

        .scanner-overlay::before {
            content: '';
            position: absolute;
            top: -3px;
            left: -3px;
            right: -3px;
            bottom: -3px;
            border: 2px solid rgba(11, 94, 215, 0.3);
            border-radius: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        .asset-card {
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-top: 2rem;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-available { background: var(--success-color); color: white; }
        .status-borrowed { background: var(--warning-color); color: white; }
        .status-maintenance { background: var(--danger-color); color: white; }
        .status-unserviceable { background: var(--danger-color); color: white; }

        .btn-borrow {
            background: linear-gradient(45deg, var(--success-color), #146c43);
            border: none;
            color: white;
            border-radius: 25px;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-borrow:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
            color: white;
        }

        .btn-borrow:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .scanner-controls {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin: 1rem 0;
        }

        .control-btn {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .help-text {
            background: rgba(11, 94, 215, 0.1);
            border-left: 4px solid var(--primary-color);
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
        }

        @media (max-width: 768px) {
            .scanner-container {
                margin: 1rem 0;
                padding: 1rem;
            }
            
            #qr-video {
                height: 300px;
            }
            
            .scanner-overlay {
                width: 150px;
                height: 150px;
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
            
            <div class="navbar-nav ms-auto d-flex flex-row align-items-center">
                <a href="guest_dashboard.php" class="btn btn-outline-primary me-2">
                    <i class="bi bi-house me-1"></i>Dashboard
                </a>
                <a href="../logout.php" class="btn btn-outline-danger">
                    <i class="bi bi-box-arrow-right me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="scanner-container">
                    <div class="text-center mb-4">
                        <h2><i class="bi bi-qr-code-scan me-2 text-primary"></i>Asset QR Scanner</h2>
                        <p class="text-muted">Scan asset QR codes to view details and request borrowing</p>
                    </div>

                    <!-- Help Text -->
                    <div class="help-text">
                        <h6><i class="bi bi-info-circle me-2"></i>How to use:</h6>
                        <ul class="mb-0 small">
                            <li>Position the QR code within the blue scanning frame</li>
                            <li>Hold your device steady until the code is detected</li>
                            <li>Asset details will appear below once scanned</li>
                            <li>Click "Request Borrowing" for available assets</li>
                        </ul>
                    </div>

                    <!-- Scanner -->
                    <div class="video-container">
                        <video id="qr-video" autoplay muted playsinline></video>
                        <div class="scanner-overlay"></div>
                    </div>

                    <!-- Scanner Controls -->
                    <div class="scanner-controls">
                        <button id="start-scan" class="btn btn-success control-btn">
                            <i class="bi bi-play-fill me-1"></i>Start Scanner
                        </button>
                        <button id="stop-scan" class="btn btn-danger control-btn" style="display: none;">
                            <i class="bi bi-stop-fill me-1"></i>Stop Scanner
                        </button>
                        <button id="switch-camera" class="btn btn-info control-btn" style="display: none;">
                            <i class="bi bi-camera-reels me-1"></i>Switch Camera
                        </button>
                    </div>

                    <!-- Scanner Status -->
                    <div id="scanner-status" class="text-center text-muted">
                        <i class="bi bi-camera-video-off me-1"></i>Scanner not active
                    </div>
                </div>

                <!-- Asset Details (shown after scanning) -->
                <?php if ($asset_data): ?>
                <div class="asset-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="card-title">
                                    <?= htmlspecialchars($asset_data['description']) ?>
                                    <span class="status-badge status-<?= strtolower($asset_data['status']) ?>">
                                        <?= ucfirst($asset_data['status']) ?>
                                    </span>
                                </h5>
                                
                                <div class="row mt-3">
                                    <div class="col-sm-6">
                                        <p class="mb-1"><strong>Asset ID:</strong> <?= htmlspecialchars($asset_data['id']) ?></p>
                                        <p class="mb-1"><strong>Property No:</strong> <?= htmlspecialchars($asset_data['inventory_tag'] ?? 'N/A') ?></p>
                                        <p class="mb-1"><strong>Category:</strong> <?= htmlspecialchars($asset_data['category_name'] ?? 'N/A') ?></p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="mb-1"><strong>Office:</strong> <?= htmlspecialchars($asset_data['office_name'] ?? 'N/A') ?></p>
                                        <p class="mb-1"><strong>Assigned to:</strong> <?= htmlspecialchars($asset_data['employee_name'] ?? 'Unassigned') ?></p>
                                        <p class="mb-1"><strong>Value:</strong> ₱<?= number_format($asset_data['value'] ?? 0, 2) ?></p>
                                    </div>
                                </div>

                                <?php if (!empty($asset_data['brand']) || !empty($asset_data['model'])): ?>
                                <p class="mt-2 mb-1">
                                    <strong>Brand/Model:</strong> 
                                    <?= htmlspecialchars(trim($asset_data['brand'] . ' ' . $asset_data['model'])) ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="col-md-4 text-center">
                                <?php if ($asset_data['status'] === 'available'): ?>
                                    <button class="btn btn-borrow btn-lg mb-2" onclick="requestBorrowing(<?= $asset_data['id'] ?>)">
                                        <i class="bi bi-box-arrow-right me-2"></i>Request Borrowing
                                    </button>
                                    <p class="small text-success">
                                        <i class="bi bi-check-circle me-1"></i>Available for borrowing
                                    </p>
                                <?php elseif ($asset_data['status'] === 'borrowed'): ?>
                                    <button class="btn btn-borrow btn-lg mb-2" disabled>
                                        <i class="bi bi-clock me-2"></i>Currently Borrowed
                                    </button>
                                    <p class="small text-warning">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Asset is currently on loan
                                    </p>
                                <?php else: ?>
                                    <button class="btn btn-borrow btn-lg mb-2" disabled>
                                        <i class="bi bi-x-circle me-2"></i>Not Available
                                    </button>
                                    <p class="small text-danger">
                                        <i class="bi bi-exclamation-triangle me-1"></i>Asset not available for borrowing
                                    </p>
                                <?php endif; ?>
                                
                                <div class="mt-3">
                                    <a href="scan_qr.php" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-arrow-clockwise me-1"></i>Scan Another
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php elseif ($error_message): ?>
                <div class="alert alert-danger mt-4">
                    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error_message) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- QR Scanner Script -->
    <script src="https://unpkg.com/jsqr/dist/jsQR.js"></script>
    
    <script>
        let video = document.getElementById('qr-video');
        let canvas = document.createElement('canvas');
        let context = canvas.getContext('2d');
        let scanning = false;
        let stream = null;
        let cameras = [];
        let currentCameraIndex = 0;

        const startBtn = document.getElementById('start-scan');
        const stopBtn = document.getElementById('stop-scan');
        const switchBtn = document.getElementById('switch-camera');
        const statusDiv = document.getElementById('scanner-status');

        // Initialize camera list
        async function getCameras() {
            try {
                const devices = await navigator.mediaDevices.enumerateDevices();
                cameras = devices.filter(device => device.kind === 'videoinput');
                
                if (cameras.length > 1) {
                    switchBtn.style.display = 'inline-block';
                }
            } catch (error) {
                console.error('Error getting cameras:', error);
            }
        }

        // Start camera
        async function startCamera() {
            try {
                const constraints = {
                    video: {
                        facingMode: cameras.length > 0 ? undefined : 'environment',
                        deviceId: cameras.length > 0 ? cameras[currentCameraIndex].deviceId : undefined,
                        width: { ideal: 640 },
                        height: { ideal: 480 }
                    }
                };

                stream = await navigator.mediaDevices.getUserMedia(constraints);
                video.srcObject = stream;
                
                video.onloadedmetadata = () => {
                    canvas.width = video.videoWidth;
                    canvas.height = video.videoHeight;
                };

                return true;
            } catch (error) {
                console.error('Error starting camera:', error);
                statusDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-1 text-danger"></i>Camera access denied or not available';
                return false;
            }
        }

        // Stop camera
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            video.srcObject = null;
        }

        // Start scanning
        async function startScanning() {
            await getCameras();
            
            if (await startCamera()) {
                scanning = true;
                startBtn.style.display = 'none';
                stopBtn.style.display = 'inline-block';
                statusDiv.innerHTML = '<i class="bi bi-camera-video me-1 text-success"></i>Scanner active - Position QR code in frame';
                
                scanQRCode();
            }
        }

        // Stop scanning
        function stopScanning() {
            scanning = false;
            stopCamera();
            startBtn.style.display = 'inline-block';
            stopBtn.style.display = 'none';
            switchBtn.style.display = 'none';
            statusDiv.innerHTML = '<i class="bi bi-camera-video-off me-1"></i>Scanner stopped';
        }

        // Switch camera
        async function switchCamera() {
            if (cameras.length > 1) {
                currentCameraIndex = (currentCameraIndex + 1) % cameras.length;
                stopCamera();
                await startCamera();
                statusDiv.innerHTML = '<i class="bi bi-camera-reels me-1 text-info"></i>Switched camera';
            }
        }

        // Scan QR code
        function scanQRCode() {
            if (!scanning) return;

            if (video.readyState === video.HAVE_ENOUGH_DATA) {
                context.drawImage(video, 0, 0, canvas.width, canvas.height);
                const imageData = context.getImageData(0, 0, canvas.width, canvas.height);
                const code = jsQR(imageData.data, imageData.width, imageData.height);

                if (code) {
                    statusDiv.innerHTML = '<i class="bi bi-check-circle me-1 text-success"></i>QR Code detected! Processing...';
                    
                    // Extract asset ID from QR code
                    const qrData = code.data;
                    const assetIdMatch = qrData.match(/asset_id[=:](\d+)/i) || qrData.match(/(\d+)/);
                    
                    if (assetIdMatch) {
                        const assetId = assetIdMatch[1];
                        window.location.href = `scan_qr.php?asset_id=${assetId}`;
                        return;
                    } else {
                        statusDiv.innerHTML = '<i class="bi bi-exclamation-triangle me-1 text-warning"></i>Invalid QR code format';
                        setTimeout(() => {
                            statusDiv.innerHTML = '<i class="bi bi-camera-video me-1 text-success"></i>Scanner active - Position QR code in frame';
                        }, 2000);
                    }
                }
            }

            requestAnimationFrame(scanQRCode);
        }

        // Request borrowing
        function requestBorrowing(assetId) {
            if (confirm('Do you want to request borrowing for this asset?')) {
                // Here you would typically send an AJAX request to handle the borrowing request
                alert('Borrowing request submitted! You will be contacted for approval and pickup details.');
                
                // For now, just redirect back to dashboard
                setTimeout(() => {
                    window.location.href = 'guest_dashboard.php';
                }, 2000);
            }
        }

        // Event listeners
        startBtn.addEventListener('click', startScanning);
        stopBtn.addEventListener('click', stopScanning);
        switchBtn.addEventListener('click', switchCamera);

        // Auto-start scanner if no asset is being displayed
        <?php if (!$asset_data && !$error_message): ?>
        window.addEventListener('load', () => {
            setTimeout(startScanning, 1000);
        });
        <?php endif; ?>

        // Cleanup on page unload
        window.addEventListener('beforeunload', stopScanning);
    </script>
</body>
</html>
