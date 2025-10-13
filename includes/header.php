<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/dark_mode_helper.php';

// Initialize dark mode class
$darkModeClass = isDarkMode() ? 'dark-mode' : '';
?>
<!DOCTYPE html>
<html lang="en" class="<?php echo $darkModeClass; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'PILAR Asset Inventory'; ?></title>
    
    <!-- Bootstrap CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/PILAR_ASSET_INVENTORY/css/dark-mode.css">
    <?php if (file_exists(__DIR__ . '/../css/' . basename($_SERVER['PHP_SELF'], '.php') . '.css')): ?>
        <link rel="stylesheet" href="/PILAR_ASSET_INVENTORY/css/<?php echo basename($_SERVER['PHP_SELF'], '.php'); ?>.css">
    <?php endif; ?>

    <link rel="stylesheet" href="../assets/css/notifications.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables CSS & JS -->
<link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <style>
        /* Additional global dark mode styles */
        body.dark-mode {
            background-color: #121212;
            color: #f8f9fa;
        }
        
        .dark-mode .card {
            background-color: #2d2d2d;
            border-color: #444;
        }
        
        .dark-mode .table {
            color: #f8f9fa;
            background-color: #2d2d2d;
        }
        
        .dark-mode .table th,
        .dark-mode .table td {
            border-color: #444;
        }
    </style>
</head>
<body class="<?php echo $darkModeClass; ?>">
    <script>
    // Initialize dark mode from session
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial dark mode state
        document.body.classList.toggle('dark-mode', <?php echo isDarkMode() ? 'true' : 'false'; ?>);
    });
    </script>
    <script type="module">
        import NotificationManager from '../assets/js/notifications.js';
        
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('notificationDropdown')) {
                window.notificationManager = new NotificationManager({
                    apiUrl: 'get_notifications.php',
                    actionUrl: 'notification_action.php',
                    enableDesktopNotifications: true,
                    pollInterval: 30000,
                    userId: <?php echo $_SESSION['user_id'] ?? 0; ?>
                });
            }
        });
    </script>