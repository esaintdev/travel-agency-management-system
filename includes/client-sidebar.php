<?php
// Get current page to highlight active navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    .client-sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        width: 280px;
        background: linear-gradient(135deg, #13357B 0%, #1e4a8c 100%);
        z-index: 1000;
        transition: all 0.3s ease;
        box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        display: flex;
        flex-direction: column;
    }
    
    .client-sidebar.collapsed {
        width: 70px;
    }
    
    .sidebar-header {
        padding: 20px;
        background: rgba(0,0,0,0.1);
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-brand {
        color: white;
        text-decoration: none;
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .sidebar-brand:hover {
        color: #FEA116;
    }
    
    .sidebar-brand i {
        margin-right: 10px;
        font-size: 24px;
    }
    
    .sidebar-nav {
        padding: 20px 0;
    }
    
    .nav-section {
        margin-bottom: 30px;
    }
    
    .nav-section-title {
        color: #FEA116;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        padding: 0 20px 10px;
        margin-bottom: 10px;
        border-bottom: 1px solid rgba(254, 161, 22, 0.2);
    }
    
    .nav-item {
        margin-bottom: 5px;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 3px solid transparent;
    }
    
    .nav-link:hover {
        color: white;
        background: rgba(255,255,255,0.1);
        border-left-color: #FEA116;
    }
    
    .nav-link.active {
        color: white;
        background: rgba(254, 161, 22, 0.2);
        border-left-color: #FEA116;
    }
    
    .nav-link i {
        width: 20px;
        margin-right: 12px;
        text-align: center;
    }
    
    .sidebar-footer {
        margin-top: auto;
        padding: 20px;
        background: rgba(0,0,0,0.1);
        border-top: 1px solid rgba(255,255,255,0.1);
    }
    
    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        padding: 20px 0;
    }
    
    .client-info {
        color: white;
        font-size: 14px;
        margin-bottom: 15px;
    }
    
    .client-info strong {
        color: #FEA116;
    }
    
    .sidebar-toggle {
        position: fixed;
        top: 20px;
        left: 290px;
        z-index: 1001;
        background: #13357B;
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        transition: all 0.3s ease;
    }
    
    .sidebar-toggle:hover {
        background: #FEA116;
    }
    
    .main-content {
        margin-left: 280px;
        transition: all 0.3s ease;
        min-height: 100vh;
        padding: 20px;
    }
    
    .main-content.expanded {
        margin-left: 70px;
    }
    
    .admin-impersonation-alert {
        background: linear-gradient(135deg, #FEA116 0%, #ff9800 100%);
        color: white;
        padding: 10px 20px;
        margin: 0;
        border: none;
        font-weight: 500;
    }
    
    /* Notification Styles */
    .notification-wrapper {
        position: relative;
    }
    
    .notification-bell {
        background: rgba(255,255,255,0.1);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .notification-bell:hover {
        background: rgba(254, 161, 22, 0.3);
        color: #FEA116;
    }
    
    .notification-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #dc3545;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 11px;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }
    
    .notification-dropdown {
        position: absolute;
        top: 50px;
        left: 0;
        width: 280px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        z-index: 1002;
        display: none;
        max-height: 400px;
        overflow: hidden;
    }
    
    .notification-dropdown.show {
        display: block;
        animation: slideDown 0.3s ease;
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .notification-header {
        padding: 15px 20px;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: between;
        align-items: center;
        background: #f8f9fa;
    }
    
    .notification-header h6 {
        margin: 0;
        color: #333;
        font-weight: 600;
    }
    
    .notification-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .notification-item {
        padding: 15px 20px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background 0.2s ease;
        position: relative;
    }
    
    .notification-item:hover {
        background: #f8f9fa;
    }
    
    .notification-item.unread {
        background: #e3f2fd;
        border-left: 4px solid #2196f3;
    }
    
    .notification-item.unread::before {
        content: '';
        position: absolute;
        top: 20px;
        right: 20px;
        width: 8px;
        height: 8px;
        background: #2196f3;
        border-radius: 50%;
    }
    
    .notification-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 12px;
        font-size: 16px;
    }
    
    .notification-icon.invoice {
        background: #fff3cd;
        color: #856404;
    }
    
    .notification-icon.payment {
        background: #d4edda;
        color: #155724;
    }
    
    .notification-icon.system {
        background: #d1ecf1;
        color: #0c5460;
    }
    
    .notification-content {
        flex: 1;
    }
    
    .notification-title {
        font-weight: 600;
        color: #333;
        margin-bottom: 4px;
        font-size: 14px;
    }
    
    .notification-message {
        color: #666;
        font-size: 13px;
        line-height: 1.4;
        margin-bottom: 4px;
    }
    
    .notification-time {
        color: #999;
        font-size: 11px;
    }
    
    .notification-footer {
        padding: 10px 20px;
        border-top: 1px solid #eee;
        background: #f8f9fa;
    }
    
    .empty-notifications {
        text-align: center;
        padding: 40px 20px;
        color: #666;
    }
    
    .empty-notifications i {
        font-size: 48px;
        color: #ddd;
        margin-bottom: 15px;
    }

    @media (max-width: 768px) {
        .client-sidebar {
            transform: translateX(-100%);
        }
        
        .client-sidebar.mobile-open {
            transform: translateX(0);
        }
        
        .main-content {
            margin-left: 0;
        }
        
        .sidebar-toggle {
            left: 20px;
        }
        
        .notification-dropdown {
            width: 260px;
            left: 10px;
        }
    }
</style>

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle d-lg-none" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
</button>

<!-- Client Sidebar -->
<div class="client-sidebar" id="clientSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="d-flex justify-content-between align-items-center">
            <a href="client-dashboard.php" class="sidebar-brand">
                <i class="fas fa-globe-americas"></i>
                <span>M25 Travel & Tours</span>
            </a>
            
            <!-- Notification Bell -->
            <div class="notification-wrapper">
                <button class="notification-bell" onclick="toggleNotifications()" id="notificationBell">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count" id="notificationCount" style="display: none;">0</span>
                </button>
                
                <!-- Notification Dropdown -->
                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-header">
                        <h6>Notifications</h6>
                        <button class="btn btn-sm btn-link text-primary" onclick="markAllAsRead()">
                            Mark all read
                        </button>
                    </div>
                    <div class="notification-list" id="notificationList">
                        <div class="text-center p-3">
                            <i class="fas fa-spinner fa-spin"></i> Loading...
                        </div>
                    </div>
                    <div class="notification-footer">
                        <a href="#" class="btn btn-sm btn-primary w-100">View All</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Admin Impersonation Alert -->
    <?php if (isset($_SESSION['admin_impersonation'])): ?>
    <div class="admin-impersonation-alert">
        <i class="fas fa-user-shield me-2"></i>
        <strong>Admin View:</strong> <?php echo htmlspecialchars($_SESSION['admin_impersonation']['admin_name']); ?>
    </div>
    <?php endif; ?>
    
    <!-- Sidebar Navigation -->
    <div class="sidebar-nav">
        <!-- Main Navigation -->
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <div class="nav-item">
                <a href="client-dashboard.php" class="nav-link <?php echo $current_page === 'client-dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="client-profile.php" class="nav-link <?php echo $current_page === 'client-profile.php' ? 'active' : ''; ?>">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="client-status.php" class="nav-link <?php echo $current_page === 'client-status.php' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i>
                    <span>Application Status</span>
                </a>
            </div>
        </div>
        
        <!-- Documents -->
        <div class="nav-section">
            <div class="nav-section-title">Documents</div>
            <div class="nav-item">
                <a href="client-documents.php" class="nav-link <?php echo $current_page === 'client-documents.php' ? 'active' : ''; ?>">
                    <i class="fas fa-folder-open"></i>
                    <span>My Documents</span>
                </a>
            </div>
        </div>
        
        <!-- Payments -->
        <div class="nav-section">
            <div class="nav-section-title">Payments</div>
            <div class="nav-item">
                <a href="client-payments.php" class="nav-link <?php echo in_array($current_page, ['client-payments.php', 'client-invoice-view.php']) ? 'active' : ''; ?>">
                    <i class="fas fa-credit-card"></i>
                    <span>Invoices & Payments</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="client-payment-history.php" class="nav-link <?php echo $current_page === 'client-payment-history.php' ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i>
                    <span>Payment History</span>
                </a>
            </div>
        </div>
        
        <!-- Support -->
        <div class="nav-section">
            <div class="nav-section-title">Support</div>
            <div class="nav-item">
                <a href="/" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Main Website</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="mailto:info@m25travelagency.com" class="nav-link">
                    <i class="fas fa-envelope"></i>
                    <span>Contact Support</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="tel:+233592605752" class="nav-link">
                    <i class="fas fa-phone"></i>
                    <span>Call Us</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <?php if (isset($client) && $client): ?>
        <div class="client-info">
            <div><strong>Welcome,</strong></div>
            <div><?php echo htmlspecialchars($client['full_name']); ?></div>
            <div><small><?php echo htmlspecialchars($client['reference_id']); ?></small></div>
        </div>
        <?php endif; ?>
        
        <div class="d-grid gap-2">
            <?php if (isset($_SESSION['admin_impersonation'])): ?>
            <a href="admin-logout-from-client.php" class="btn btn-warning btn-sm">
                <i class="fas fa-user-shield me-1"></i>Return to Admin
            </a>
            <?php endif; ?>
            <a href="client-logout.php" class="btn btn-danger btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i>Logout
            </a>
        </div>
    </div>
</div>

<!-- Main Content Wrapper -->
<div class="main-content" id="mainContent">

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('clientSidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth <= 768) {
        // Mobile behavior
        sidebar.classList.toggle('mobile-open');
    } else {
        // Desktop behavior
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    }
}

// Close mobile sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('clientSidebar');
    const toggle = document.querySelector('.sidebar-toggle');
    
    if (window.innerWidth <= 768 && 
        !sidebar.contains(event.target) && 
        !toggle.contains(event.target) &&
        sidebar.classList.contains('mobile-open')) {
        sidebar.classList.remove('mobile-open');
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('clientSidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth > 768) {
        sidebar.classList.remove('mobile-open');
    }
});

// Notification System
let notificationDropdownOpen = false;

// Load notification count on page load
document.addEventListener('DOMContentLoaded', function() {
    loadNotificationCount();
    // Refresh count every 30 seconds
    setInterval(loadNotificationCount, 30000);
});

function loadNotificationCount() {
    fetch('api/notifications.php?action=count')
        .then(response => response.json())
        .then(data => {
            const countElement = document.getElementById('notificationCount');
            if (data.count > 0) {
                countElement.textContent = data.count > 99 ? '99+' : data.count;
                countElement.style.display = 'flex';
            } else {
                countElement.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error loading notification count:', error);
        });
}

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    
    if (notificationDropdownOpen) {
        dropdown.classList.remove('show');
        notificationDropdownOpen = false;
    } else {
        loadNotifications();
        dropdown.classList.add('show');
        notificationDropdownOpen = true;
    }
}

function loadNotifications() {
    const notificationList = document.getElementById('notificationList');
    notificationList.innerHTML = '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';
    
    fetch('api/notifications.php?action=list&limit=10')
        .then(response => response.json())
        .then(data => {
            displayNotifications(data.notifications);
        })
        .catch(error => {
            console.error('Error loading notifications:', error);
            notificationList.innerHTML = '<div class="text-center p-3 text-danger">Error loading notifications</div>';
        });
}

function displayNotifications(notifications) {
    const notificationList = document.getElementById('notificationList');
    
    if (notifications.length === 0) {
        notificationList.innerHTML = `
            <div class="empty-notifications">
                <i class="fas fa-bell-slash"></i>
                <div>No notifications yet</div>
                <small>You'll see new notifications here</small>
            </div>
        `;
        return;
    }
    
    let html = '';
    notifications.forEach(notification => {
        const unreadClass = !notification.is_read ? 'unread' : '';
        html += `
            <div class="notification-item ${unreadClass} d-flex" onclick="markNotificationAsRead(${notification.id})">
                <div class="notification-icon ${notification.type}">
                    <i class="${notification.icon}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">${notification.time_ago}</div>
                </div>
            </div>
        `;
    });
    
    notificationList.innerHTML = html;
}

function markNotificationAsRead(notificationId) {
    fetch('api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=mark_read&notification_id=${notificationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotificationCount();
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    fetch('api/notifications.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=mark_all_read'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadNotificationCount();
            loadNotifications();
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

// Close notification dropdown when clicking outside
document.addEventListener('click', function(event) {
    const notificationWrapper = document.querySelector('.notification-wrapper');
    const dropdown = document.getElementById('notificationDropdown');
    
    if (notificationDropdownOpen && 
        !notificationWrapper.contains(event.target)) {
        dropdown.classList.remove('show');
        notificationDropdownOpen = false;
    }
});
</script>
