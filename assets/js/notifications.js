class NotificationManager {
    constructor() {
        this.notificationDropdown = document.getElementById('notificationDropdown');
        this.notificationBadge = document.querySelector('.notification-badge');
        this.notificationList = document.querySelector('.notification-list');
        this.markAllAsReadBtn = document.getElementById('markAllAsRead');
        this.viewAllLink = document.getElementById('viewAllNotifications');
        this.pollingInterval = 60000; // 1 minute
        this.isOpen = false;
        
        this.initialize();
    }
    
    initialize() {
        // Load notifications when dropdown is shown
        $(this.notificationDropdown).on('show.bs.dropdown', () => {
            this.isOpen = true;
            this.loadNotifications();
        });
        
        // Mark as read when clicking on a notification
        this.notificationList?.addEventListener('click', (e) => {
            const notificationItem = e.target.closest('.notification-item');
            if (notificationItem) {
                const notificationId = notificationItem.dataset.id;
                this.markAsRead(notificationId);
            }
        });
        
        // Mark all as read
        this.markAllAsReadBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            this.markAllAsRead();
        });
        
        // Start polling for new notifications
        this.startPolling();
    }
    
    async loadNotifications() {
        try {
            const response = await fetch('get_notifications.php?unread_only=false&limit=10');
            const data = await response.json();
            
            if (data.success) {
                this.renderNotifications(data.notifications);
                this.updateBadge(data.unread_count);
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }
    
    renderNotifications(notifications) {
        if (!this.notificationList) return;
        
        if (notifications.length === 0) {
            this.notificationList.innerHTML = `
                <li class="dropdown-item text-muted text-center">
                    No notifications
                </li>
            `;
            return;
        }
        
        this.notificationList.innerHTML = notifications.map(notification => `
            <li class="dropdown-item notification-item ${!notification.is_read ? 'unread' : ''}" 
                data-id="${notification.id}"
                data-entity-type="${notification.related_entity_type}"
                data-entity-id="${notification.related_entity_id}">
                <div class="d-flex align-items-center">
                    <div class="notification-icon me-2">
                        ${this.getNotificationIcon(notification.type)}
                    </div>
                    <div class="notification-content flex-grow-1">
                        <div class="fw-bold">${this.escapeHtml(notification.title)}</div>
                        <div class="small text-muted">${this.escapeHtml(notification.message)}</div>
                        <div class="text-end small">
                            ${this.formatTimeAgo(notification.created_at)}
                        </div>
                    </div>
                </div>
            </li>
        `).join('');
        
        // Add click handlers to notification items
        document.querySelectorAll('.notification-item').forEach(item => {
            item.addEventListener('click', (e) => {
                const entityType = item.dataset.entityType;
                const entityId = item.dataset.entityId;
                
                if (entityType && entityId) {
                    e.preventDefault();
                    this.handleNotificationClick(entityType, entityId);
                }
            });
        });
    }
    
    getNotificationIcon(type) {
        const icons = {
            'low_stock': '<i class="bi bi-exclamation-triangle-fill text-warning"></i>',
            'borrow_request': '<i class="bi bi-envelope-paper-fill text-primary"></i>',
            'borrow_approved': '<i class="bi bi-check-circle-fill text-success"></i>',
            'borrow_rejected': '<i class="bi bi-x-circle-fill text-danger"></i>',
            'due_date_reminder': '<i class="bi bi-clock-fill text-warning"></i>',
            'overdue_notice': '<i class="bi bi-exclamation-octagon-fill text-danger"></i>',
            'maintenance_reminder': '<i class="bi bi-tools text-info"></i>',
            'system_alert': '<i class="bi bi-bell-fill text-secondary"></i>',
            'new_asset_assigned': '<i class="bi bi-box-seam-fill text-success"></i>',
            'asset_returned': '<i class="bi bi-arrow-return-left text-primary"></i>'
        };
        
        return icons[type] || '<i class="bi bi-bell-fill"></i>';
    }
    
    async markAsRead(notificationId) {
        try {
            const response = await fetch('notification_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=mark_as_read&notification_id=${notificationId}`
            });
            
            const data = await response.json();
            if (data.success) {
                this.updateBadge(data.unread_count);
                // Update UI to show notification as read
                const item = document.querySelector(`.notification-item[data-id="${notificationId}"]`);
                if (item) {
                    item.classList.remove('unread');
                }
            }
            return data.success;
        } catch (error) {
            console.error('Error marking notification as read:', error);
            return false;
        }
    }
    
    async markAllAsRead() {
        try {
            const response = await fetch('notification_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=mark_all_read'
            });
            
            const data = await response.json();
            if (data.success) {
                this.updateBadge(0);
                // Update UI to show all notifications as read
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.classList.remove('unread');
                });
            }
            return data.success;
        } catch (error) {
            console.error('Error marking all notifications as read:', error);
            return false;
        }
    }
    
    updateBadge(count) {
        if (this.notificationBadge) {
            if (count > 0) {
                this.notificationBadge.textContent = count > 9 ? '9+' : count;
                this.notificationBadge.style.display = 'inline-block';
            } else {
                this.notificationBadge.style.display = 'none';
            }
        }
    }
    
    startPolling() {
        // Initial load
        this.checkForNewNotifications();
        
        // Set up polling
        setInterval(() => {
            if (!this.isOpen) {
                this.checkForNewNotifications();
            }
        }, this.pollingInterval);
    }
    
    async checkForNewNotifications() {
        try {
            const response = await fetch('get_notifications.php?unread_only=true&limit=1');
            const data = await response.json();
            
            if (data.success) {
                this.updateBadge(data.unread_count);
                
                // Show desktop notification for new unread notifications
                if (data.unread_count > 0 && document.visibilityState === 'visible') {
                    this.showDesktopNotification(data.notifications[0]);
                }
            }
        } catch (error) {
            console.error('Error checking for new notifications:', error);
        }
    }
    
    showDesktopNotification(notification) {
        // Check if browser supports notifications
        if (!('Notification' in window)) {
            return;
        }
        
        // Check if notification permission is already granted
        if (Notification.permission === 'granted') {
            this.createNotification(notification);
        } 
        // Otherwise, ask the user for permission
        else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    this.createNotification(notification);
                }
            });
        }
    }
    
    createNotification(notification) {
        const notificationObj = new Notification(notification.title, {
            body: notification.message,
            icon: '/path/to/notification-icon.png', // Update this path
            tag: `notification-${notification.id}`
        });
        
        notificationObj.onclick = () => {
            window.focus();
            this.handleNotificationClick(notification.related_entity_type, notification.related_entity_id);
            notificationObj.close();
        };
    }
    
    handleNotificationClick(entityType, entityId) {
        // Define routes for different entity types
        const routes = {
            'asset': 'view_asset.php',
            'borrow_request': 'view_borrow_request.php',
            'borrow': 'view_borrow.php'
            // Add more routes as needed
        };
        
        if (routes[entityType]) {
            window.location.href = `${routes[entityType]}?id=${entityId}`;
        }
    }
    
    formatTimeAgo(dateString) {
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
    
    escapeHtml(unsafe) {
        return unsafe
            .toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('notificationDropdown')) {
        window.notificationManager = new NotificationManager();
    }
});
