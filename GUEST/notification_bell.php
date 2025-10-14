<?php
// notification_bell.php
// Notification bell component for GUEST pages

// This should be included after session_start() and database connection
?>

<!-- Notification Bell -->
<li class="nav-item dropdown">
    <a class="nav-link position-relative" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="bi bi-bell me-1"></i>
        <span id="notificationBadge" class="badge bg-danger rounded-pill d-none" style="font-size: 0.6rem;">0</span>
    </a>
    <ul class="dropdown-menu dropdown-menu-end notification-dropdown" aria-labelledby="notificationDropdown" style="min-width: 350px; max-width: 400px;">
        <li>
            <h6 class="dropdown-header">
                <i class="bi bi-bell me-2"></i>Notifications
                <button class="btn btn-sm btn-link float-end p-0" onclick="markAllNotificationsRead()">
                    <small>Mark all read</small>
                </button>
            </h6>
        </li>
        <li><hr class="dropdown-divider"></li>
        <div id="notificationList">
            <li>
                <div class="text-center py-3">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <small class="text-muted d-block mt-1">Loading notifications...</small>
                </div>
            </li>
        </div>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item text-center" href="borrowing_history.php">
                <small><i class="bi bi-clock-history me-1"></i>View All Notifications</small>
            </a>
        </li>
    </ul>
</li>

<style>
.notification-dropdown {
    max-height: 400px;
    overflow-y: auto;
    z-index: 99999 !important; /* Extremely high z-index */
    position: absolute;
}

.notification-dropdown .dropdown-menu {
    z-index: 99999 !important;
    position: absolute;
    top: 100%;
    right: 0;
    min-width: 350px;
    max-width: 400px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
}

/* Ensure the dropdown appears above cards and other elements */
.dropdown-menu {
    z-index: 99999 !important;
}

.nav-item.dropdown .dropdown-menu {
    z-index: 99999 !important;
}

.nav-item.dropdown.show .dropdown-menu {
    z-index: 99999 !important;
    display: block !important;
}

/* Force dropdown positioning */
.dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    left: auto;
    right: 0;
    z-index: 99999;
    min-width: 350px;
    max-width: 400px;
}

/* Ensure dropdown doesn't get clipped by parent containers */
.navbar {
    overflow: visible !important;
    position: relative !important;
    z-index: 1000 !important;
}

.nav-item {
    position: static; /* Allow dropdown to position relative to navbar */
}

/* Specific override for notification dropdown */
#notificationDropdown + .dropdown-menu {
    z-index: 99999 !important;
    position: absolute !important;
    top: 100% !important;
    right: 0 !important;
}

.notification-item {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.notification-item .notification-title {
    font-weight: 600;
    font-size: 0.9rem;
    color: #333;
    margin-bottom: 0.25rem;
}

.notification-item .notification-message {
    font-size: 0.8rem;
    color: #666;
    margin-bottom: 0.25rem;
    line-height: 1.3;
}

.notification-item .notification-time {
    font-size: 0.7rem;
    color: #999;
}

.notification-item .notification-type {
    display: inline-block;
    padding: 0.15rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.7rem;
    font-weight: 500;
    text-transform: uppercase;
}

.notification-type.approved {
    background-color: #d4edda;
    color: #155724;
}

.notification-type.rejected {
    background-color: #f8d7da;
    color: #721c24;
}

.notification-type.returned {
    background-color: #d1ecf1;
    color: #0c5460;
}

.notification-type.reminder {
    background-color: #fff3cd;
    color: #856404;
}

#notificationBadge {
    position: absolute;
    top: -8px;
    right: -8px;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<script>
// Notification system for guests
let notificationCheckInterval;

function initNotifications() {
    // Load notifications on page load
    loadNotifications();

    // Set up periodic checking for new notifications
    notificationCheckInterval = setInterval(loadNotifications, 30000); // Check every 30 seconds

    // Clear interval when page is hidden
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            clearInterval(notificationCheckInterval);
        } else {
            loadNotifications();
            notificationCheckInterval = setInterval(loadNotifications, 30000);
        }
    });
}

function loadNotifications() {
    fetch('get_guest_notifications.php?action=fetch')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateNotificationBadge(data.unread_count);
                renderNotifications(data.notifications);
            } else {
                console.error('Failed to load notifications:', data.message);
            }
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
        });
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.classList.remove('d-none');
    } else {
        badge.classList.add('d-none');
    }
}

function renderNotifications(notifications) {
    const container = document.getElementById('notificationList');

    if (!notifications || notifications.length === 0) {
        container.innerHTML = `
            <li>
                <div class="text-center py-3">
                    <i class="bi bi-bell-slash text-muted" style="font-size: 2rem;"></i>
                    <small class="text-muted d-block mt-1">No notifications</small>
                </div>
            </li>
        `;
        return;
    }

    let html = '';
    notifications.slice(0, 10).forEach(notification => {
        const isUnread = !notification.is_read;
        const typeClass = getNotificationTypeClass(notification.type);
        const timeAgo = getTimeAgo(notification.created_at);

        html += `
            <li class="notification-item ${isUnread ? 'unread' : ''}" onclick="markNotificationRead(${notification.id})">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="notification-title">${escapeHtml(notification.title)}</div>
                        <div class="notification-message">${escapeHtml(notification.message)}</div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="notification-time">${timeAgo}</small>
                            <span class="notification-type ${typeClass}">${getNotificationTypeLabel(notification.type)}</span>
                        </div>
                    </div>
                    ${isUnread ? '<i class="bi bi-circle-fill text-primary ms-2" style="font-size: 0.5rem;"></i>' : ''}
                </div>
            </li>
        `;
    });

    container.innerHTML = html;
}

function getNotificationTypeClass(type) {
    const classes = {
        'borrow_approved': 'approved',
        'borrow_rejected': 'rejected',
        'asset_returned': 'returned',
        'due_date_reminder': 'reminder'
    };
    return classes[type] || '';
}

function getNotificationTypeLabel(type) {
    const labels = {
        'borrow_approved': 'Approved',
        'borrow_rejected': 'Rejected',
        'asset_returned': 'Returned',
        'due_date_reminder': 'Reminder'
    };
    return labels[type] || type;
}

function getTimeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)}d ago`;

    return date.toLocaleDateString();
}

function markNotificationRead(notificationId) {
    fetch('get_guest_notifications.php?action=mark_read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'notification_id=' + notificationId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications(); // Reload notifications
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllNotificationsRead() {
    fetch('get_guest_notifications.php?action=mark_all_read', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotifications(); // Reload notifications
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize notifications when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initNotifications();
    
    // Force dropdown z-index after Bootstrap initializes
    setTimeout(function() {
        const dropdowns = document.querySelectorAll('.notification-dropdown .dropdown-menu');
        dropdowns.forEach(dropdown => {
            dropdown.style.zIndex = '99999';
            dropdown.style.position = 'absolute';
        });
    }, 100);
});

// Clean up interval when page unloads
window.addEventListener('beforeunload', function() {
    if (notificationCheckInterval) {
        clearInterval(notificationCheckInterval);
    }
});
</script>
