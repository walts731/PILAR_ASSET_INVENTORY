<?php
require_once __DIR__ . '/../includes/header.php';
require_once 'includes/topbar.php';
require_once '../includes/classes/Notification.php';

$notification = new Notification($conn);
$userId = $_SESSION['user_id'];

// Get user's current notification preferences
$preferences = [
    'email_notifications' => true,
    'desktop_notifications' => true,
    'sound_alert' => true,
    'notification_types' => [
        'low_stock' => true,
        'borrow_request' => true,
        'borrow_approved' => true,
        'borrow_rejected' => true,
        'due_date_reminder' => true,
        'overdue_notice' => true,
        'maintenance_reminder' => true,
        'system_alert' => true,
        'new_asset_assigned' => true,
        'asset_returned' => true
    ]
];

// Try to get saved preferences from database
$savedPrefs = $notification->getUserPreferences($userId);
if ($savedPrefs) {
    $preferences = array_merge($preferences, $savedPrefs);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPrefs = [
        'email_notifications' => isset($_POST['email_notifications']),
        'desktop_notifications' => isset($_POST['desktop_notifications']),
        'sound_alert' => isset($_POST['sound_alert']),
        'notification_types' => []
    ];

    // Get notification type preferences
    foreach ($preferences['notification_types'] as $type => $default) {
        $newPrefs['notification_types'][$type] = isset($_POST['notification_types'][$type]);
    }

    // Save preferences
    if ($notification->saveUserPreferences($userId, $newPrefs)) {
        $_SESSION['success_message'] = 'Notification preferences saved successfully!';
        $preferences = array_merge($preferences, $newPrefs);
    } else {
        $_SESSION['error_message'] = 'Failed to save notification preferences.';
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Notification Preferences</h5>
                    <p class="text-muted mb-0">Customize how you receive notifications</p>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['success_message'];
                            unset($_SESSION['success_message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php 
                            echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post" id="notificationPreferencesForm">
                        <div class="mb-4">
                            <h6 class="mb-3">Notification Methods</h6>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" 
                                       name="email_notifications" <?php echo $preferences['email_notifications'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="emailNotifications">
                                    <i class="bi bi-envelope me-2"></i> Email Notifications
                                </label>
                                <small class="form-text text-muted d-block">Receive notifications via email</small>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="desktopNotifications" 
                                       name="desktop_notifications" <?php echo $preferences['desktop_notifications'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="desktopNotifications">
                                    <i class="bi bi-bell me-2"></i> Desktop Notifications
                                </label>
                                <small class="form-text text-muted d-block">Show desktop notifications (requires browser permission)</small>
                            </div>
                            
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="soundAlert" 
                                       name="sound_alert" <?php echo $preferences['sound_alert'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="soundAlert">
                                    <i class="bi bi-bell-fill me-2"></i> Sound Alert
                                </label>
                                <small class="form-text text-muted d-block">Play a sound when new notifications arrive</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="mb-3">Notification Types</h6>
                            <p class="text-muted">Select which types of notifications you want to receive:</p>
                            
                            <div class="row">
                                <?php 
                                $notificationTypes = [
                                    'low_stock' => ['icon' => 'exclamation-triangle', 'color' => 'warning', 'label' => 'Low Stock Alerts', 'desc' => 'Get notified when stock levels are low'],
                                    'borrow_request' => ['icon' => 'envelope-paper', 'color' => 'primary', 'label' => 'Borrow Requests', 'desc' => 'Get notified about new borrow requests'],
                                    'borrow_approved' => ['icon' => 'check-circle', 'color' => 'success', 'label' => 'Request Approved', 'desc' => 'Get notified when your borrow requests are approved'],
                                    'borrow_rejected' => ['icon' => 'x-circle', 'color' => 'danger', 'label' => 'Request Rejected', 'desc' => 'Get notified if your borrow request is rejected'],
                                    'due_date_reminder' => ['icon' => 'clock', 'color' => 'info', 'label' => 'Due Date Reminders', 'desc' => 'Get reminders before borrowed items are due'],
                                    'overdue_notice' => ['icon' => 'exclamation-triangle', 'color' => 'warning', 'label' => 'Overdue Notices', 'desc' => 'Get notified when items become overdue'],
                                    'maintenance_reminder' => ['icon' => 'tools', 'color' => 'secondary', 'label' => 'Maintenance Reminders', 'desc' => 'Get reminders for scheduled maintenance'],
                                    'system_alert' => ['icon' => 'exclamation-octagon', 'color' => 'danger', 'label' => 'System Alerts', 'desc' => 'Important system notifications'],
                                    'new_asset_assigned' => ['icon' => 'box-seam', 'color' => 'primary', 'label' => 'New Asset Assigned', 'desc' => 'Get notified when new assets are assigned to you'],
                                    'asset_returned' => ['icon' => 'check2-circle', 'color' => 'success', 'label' => 'Asset Returned', 'desc' => 'Get notified when assets are returned']
                                ];

                                foreach ($notificationTypes as $type => $info): 
                                    $isChecked = $preferences['notification_types'][$type] ?? true;
                                ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="notification_types[<?php echo $type; ?>]" 
                                                           id="notif_<?php echo $type; ?>"
                                                           <?php echo $isChecked ? 'checked' : ''; ?>>
                                                    <label class="form-check-label d-flex align-items-center" for="notif_<?php echo $type; ?>">
                                                        <i class="bi bi-<?php echo $info['icon']; ?> text-<?php echo $info['color']; ?> me-2"></i>
                                                        <div>
                                                            <div class="fw-semibold"><?php echo $info['label']; ?></div>
                                                            <small class="text-muted"><?php echo $info['desc']; ?></small>
                                                        </div>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="notifications.php" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Back to Notifications
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-2"></i>Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-check-input:checked {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
}

.card {
    transition: all 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
}

.form-check-label {
    cursor: pointer;
    width: 100%;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Request notification permission if desktop notifications are enabled
    const desktopNotifications = document.getElementById('desktopNotifications');
    if (desktopNotifications) {
        desktopNotifications.addEventListener('change', function() {
            if (this.checked && Notification.permission !== 'granted') {
                Notification.requestPermission().then(permission => {
                    if (permission !== 'granted') {
                        this.checked = false;
                        alert('Please enable notifications in your browser settings to receive desktop notifications.');
                    }
                });
            }
        });
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
