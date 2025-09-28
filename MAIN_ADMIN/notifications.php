<?php
require_once __DIR__ . '/../includes/header.php';
require_once '../includes/classes/Notification.php';

$notification = new Notification($conn);
$notifications = $notification->getUserNotifications($_SESSION['user_id'], false, 50);
$unreadCount = $notification->getUnreadCount($_SESSION['user_id']);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Notifications</h5>
                    <div>
                        <?php if ($unreadCount > 0): ?>
                            <button id="markAllRead" class="btn btn-sm btn-outline-primary me-2">
                                <i class="bi bi-check2-all me-1"></i> Mark all as read
                            </button>
                        <?php endif; ?>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-funnel"></i> Filter
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                                <li><a class="dropdown-item filter-item active" href="#" data-filter="all">All Notifications</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="unread">Unread Only</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="low_stock">Low Stock</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="borrow_request">Borrow Requests</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="due_date_reminder">Due Date Reminders</a></li>
                                <li><a class="dropdown-item filter-item" href="#" data-filter="system_alert">System Alerts</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="notificationsList">
                        <?php if (empty($notifications)): ?>
                            <div class="text-center p-5 text-muted">
                                <i class="bi bi-bell-slash" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0">No notifications found</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notif): ?>
                                <a href="#" class="list-group-item list-group-item-action notification-item <?php echo !$notif['is_read'] ? 'unread' : ''; ?>" 
                                   data-id="<?php echo $notif['id']; ?>"
                                   data-type="<?php echo $notif['type']; ?>"
                                   data-entity-type="<?php echo $notif['related_entity_type']; ?>"
                                   data-entity-id="<?php echo $notif['related_entity_id']; ?>">
                                    <div class="d-flex w-100">
                                        <div class="me-3">
                                            <?php
                                            $icon = '';
                                            $iconClass = 'text-primary';
                                            
                                            switch ($notif['type']) {
                                                case 'low_stock':
                                                    $icon = 'exclamation-triangle-fill';
                                                    $iconClass = 'text-warning';
                                                    break;
                                                case 'borrow_request':
                                                    $icon = 'envelope-paper-fill';
                                                    break;
                                                case 'borrow_approved':
                                                    $icon = 'check-circle-fill';
                                                    $iconClass = 'text-success';
                                                    break;
                                                case 'borrow_rejected':
                                                    $icon = 'x-circle-fill';
                                                    $iconClass = 'text-danger';
                                                    break;
                                                case 'due_date_reminder':
                                                case 'overdue_notice':
                                                    $icon = 'clock-fill';
                                                    $iconClass = 'text-warning';
                                                    break;
                                                case 'maintenance_reminder':
                                                    $icon = 'tools';
                                                    $iconClass = 'text-info';
                                                    break;
                                                case 'system_alert':
                                                    $icon = 'exclamation-octagon-fill';
                                                    $iconClass = 'text-danger';
                                                    break;
                                                default:
                                                    $icon = 'bell-fill';
                                            }
                                            ?>
                                            <i class="bi bi-<?php echo $icon; ?> <?php echo $iconClass; ?>" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($notif['title']); ?></h6>
                                                <small class="text-muted"><?php echo $this->formatTimeAgo($notif['created_at']); ?></small>
                                            </div>
                                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($notif['message'])); ?></p>
                                            <?php if (!$notif['is_read']): ?>
                                                <span class="badge bg-primary">New</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">Showing <?php echo count($notifications); ?> notifications</small>
                        <button id="loadMore" class="btn btn-sm btn-outline-primary" style="display: none;">
                            Load More
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notification-item {
    border-left: 3px solid transparent;
    transition: all 0.2s ease;
}
.notification-item:hover {
    background-color: #f8f9fa;
}
.notification-item.unread {
    background-color: #f0f7ff;
    border-left-color: #0d6efd;
}
.notification-item .bi {
    font-size: 1.25rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark notification as read when clicked
    document.querySelectorAll('.notification-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const notificationId = this.dataset.id;
            const entityType = this.dataset.entityType;
            const entityId = this.dataset.entityId;
            
            // Mark as read
            fetch('notification_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_as_read&notification_id=${notificationId}`
            });
            
            // Navigate to the related entity if available
            if (entityType && entityId) {
                const routes = {
                    'asset': 'view_asset.php',
                    'borrow_request': 'view_borrow_request.php',
                    'borrow': 'view_borrow.php'
                };
                
                if (routes[entityType]) {
                    window.location.href = `${routes[entityType]}?id=${entityId}`;
                }
            }
        });
    });
    
    // Mark all as read
    const markAllReadBtn = document.getElementById('markAllRead');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            fetch('notification_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_read'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update UI
                    document.querySelectorAll('.notification-item').forEach(item => {
                        item.classList.remove('unread');
                        const newBadge = item.querySelector('.badge');
                        if (newBadge) newBadge.remove();
                    });
                    
                    // Hide the mark all as read button
                    markAllReadBtn.style.display = 'none';
                    
                    // Update notification count in the topbar if it exists
                    const notificationBadge = document.querySelector('.notification-badge');
                    if (notificationBadge) {
                        notificationBadge.style.display = 'none';
                    }
                }
            });
        });
    }
    
    // Filter notifications
    document.querySelectorAll('.filter-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filter = this.dataset.filter;
            
            // Update active state
            document.querySelectorAll('.filter-item').forEach(i => i.classList.remove('active'));
            this.classList.add('active');
            
            // Show/hide notifications based on filter
            document.querySelectorAll('.notification-item').forEach(notif => {
                const type = notif.dataset.type;
                const isUnread = notif.classList.contains('unread');
                
                if (filter === 'all' || 
                    (filter === 'unread' && isUnread) || 
                    (filter === type)) {
                    notif.style.display = '';
                } else {
                    notif.style.display = 'none';
                }
            });
        });
    });
});

// Format time ago (compatible with the formatTimeAgo function in notifications.js)
function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    const intervals = {
        year: 31536000,
        month: 2592000,
        week: 604800,
        day: 86400,
        hour: 3600,
        minute: 60
    };
    
    for (const [unit, secondsInUnit] of Object.entries(intervals)) {
        const interval = Math.floor(seconds / secondsInUnit);
        if (interval >= 1) {
            return interval === 1 ? `1 ${unit} ago` : `${interval} ${unit}s ago`;
        }
    }
    
    return 'Just now';
}
</script>

<?php require_once 'includes/footer.php'; ?>
