document.addEventListener('DOMContentLoaded', function() {
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationList = document.querySelector('.notification-list');
    const markAllAsReadBtn = document.getElementById('markAllAsRead');
    const notificationBadge = document.querySelector('.notification-badge');

    // Function to load notifications
    async function loadNotifications() {
        try {
            const response = await fetch('includes/actions/get_notifications.php');
            if (!response.ok) {
                throw new Error('Failed to load notifications');
            }
            const data = await response.json();
            
            if (data.success) {
                updateNotificationUI(data.notifications);
            } else {
                showNotificationError(data.message || 'Failed to load notifications');
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            showNotificationError('Failed to load notifications. Please try again.');
        }
    }

    // Function to update the notification UI
    function updateNotificationUI(notifications) {
        if (!notifications || notifications.length === 0) {
            notificationList.innerHTML = `
                <div class="text-center p-4">
                    <i class="bi bi-bell-slash text-muted" style="font-size: 2rem;"></i>
                    <p class="mt-2 mb-0 text-muted">No notifications</p>
                </div>`;
            notificationBadge.style.display = 'none';
            return;
        }

        // Update badge
        const unreadCount = notifications.filter(n => !n.is_read).length;
        if (unreadCount > 0) {
            notificationBadge.textContent = unreadCount;
            notificationBadge.style.display = 'flex';
        } else {
            notificationBadge.style.display = 'none';
        }

        // Update notification list
        let html = '';
        notifications.forEach(notification => {
            const timeAgo = getTimeAgo(notification.created_at);
            const readClass = notification.is_read ? '' : 'bg-light';
            
            html += `
                <a href="${getNotificationLink(notification)}" class="dropdown-item d-flex align-items-center py-2 border-bottom ${readClass} notification-item" 
                     data-id="${notification.id}">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">${notification.title}</h6>
                            <small class="text-muted">${timeAgo}</small>
                        </div>
                        <p class="mb-0 text-muted small">${notification.message}</p>
                    </div>
                </a>`;
        });
        
        notificationList.innerHTML = html;
        
        // Add click handlers for notification items
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', function(e) {
                markAsRead(this.dataset.id);
            });
        });
    }

    // Function to mark a notification as read
    async function markAsRead(notificationId) {
        try {
            const response = await fetch('includes/actions/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `notification_id=${encodeURIComponent(notificationId)}`
            });
            
            if (!response.ok) {
                throw new Error('Failed to mark notification as read');
            }
            
            // Reload notifications after marking as read
            loadNotifications();
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    // Function to mark all notifications as read
    async function markAllAsRead() {
        try {
            const response = await fetch('includes/actions/mark_all_notifications_read.php', {
                method: 'POST'
            });
            
            if (!response.ok) {
                throw new Error('Failed to mark all notifications as read');
            }
            
            // Reload notifications after marking all as read
            loadNotifications();
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
        }
    }

    // Helper function to format time ago
    function getTimeAgo(dateString) {
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
        
        return 'just now';
    }

    // Helper function to get notification link
    function getNotificationLink(notification) {
        if (notification.related_entity_type && notification.related_entity_id) {
            switch (notification.related_entity_type) {
                case 'asset':
                    return `view_asset.php?id=${notification.related_entity_id}`;
                case 'borrow_request':
                    return `view_borrow_request.php?id=${notification.related_entity_id}`;
                // Add more cases as needed
            }
        }
        return 'notifications.php';
    }

    // Helper function to show error message
    function showNotificationError(message) {
        notificationList.innerHTML = `
            <div class="text-center p-4">
                <i class="bi bi-exclamation-triangle text-danger" style="font-size: 2rem;"></i>
                <p class="mt-2 mb-0 text-danger">${message}</p>
            </div>`;
    }

    // Event listeners
    if (notificationDropdown) {
        // Load notifications when dropdown is shown
        notificationDropdown.addEventListener('shown.bs.dropdown', loadNotifications);
        
        // Mark all as read button
        if (markAllAsReadBtn) {
            markAllAsReadBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                markAllAsRead();
            });
        }
        
        // Initial load of notifications
        loadNotifications();
    }
});
