<?php
// Get current page to highlight active navigation
$current_page = basename($_SERVER['PHP_SELF']);
?>

<style>
    .admin-sidebar {
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
        overflow-y: auto;
    }
    
    .admin-sidebar.collapsed {
        width: 70px;
    }
    
    .admin-sidebar.collapsed .sidebar-brand span,
    .admin-sidebar.collapsed .nav-link span,
    .admin-sidebar.collapsed .nav-section-title,
    .admin-sidebar.collapsed .admin-info,
    .admin-sidebar.collapsed .role-badge {
        display: none;
    }
    
    .admin-sidebar.collapsed .nav-link {
        justify-content: center;
        padding: 12px;
        position: relative;
    }
    
    .admin-sidebar.collapsed .nav-link i {
        margin-right: 0;
    }
    
    .admin-sidebar.collapsed .nav-link:hover::after {
        content: attr(data-tooltip);
        position: absolute;
        left: 70px;
        top: 50%;
        transform: translateY(-50%);
        background: rgba(0,0,0,0.8);
        color: white;
        padding: 8px 12px;
        border-radius: 6px;
        font-size: 12px;
        white-space: nowrap;
        z-index: 1000;
        pointer-events: none;
    }
    
    .admin-sidebar.collapsed .nav-link:hover::before {
        content: '';
        position: absolute;
        left: 65px;
        top: 50%;
        transform: translateY(-50%);
        border: 5px solid transparent;
        border-right-color: rgba(0,0,0,0.8);
        z-index: 1000;
        pointer-events: none;
    }
    
    .admin-sidebar.collapsed .sidebar-header {
        text-align: center;
        padding: 20px 10px;
    }
    
    .admin-sidebar.collapsed .sidebar-footer {
        text-align: center;
        padding: 20px 10px;
    }
    
    .sidebar-header {
        padding: 20px;
        background: rgba(0,0,0,0.1);
        border-bottom: 1px solid rgba(255,255,255,0.1);
        flex-shrink: 0;
    }
    
    .sidebar-brand {
        color: white;
        text-decoration: none;
        font-size: 18px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }
    
    .sidebar-brand i {
        margin-right: 10px;
        font-size: 20px;
    }
    
    .sidebar-nav {
        flex: 1;
        overflow-y: auto;
        padding: 20px 0;
    }
    
    .nav-section {
        margin-bottom: 30px;
        padding: 0 20px;
    }
    
    .nav-section-title {
        color: rgba(255,255,255,0.7);
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 15px;
        padding-left: 15px;
    }
    
    .nav-item {
        margin-bottom: 5px;
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: rgba(255,255,255,0.8);
        text-decoration: none;
        border-radius: 10px;
        transition: all 0.3s ease;
        font-size: 14px;
        font-weight: 500;
    }
    
    .nav-link:hover {
        background: rgba(255,255,255,0.1);
        color: white;
        transform: translateX(5px);
    }
    
    .nav-link.active {
        background: linear-gradient(135deg, #FEA116 0%, #ff9800 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(254, 161, 22, 0.3);
        border-left: 4px solid white;
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
        flex-shrink: 0;
    }
    
    .admin-info {
        color: white;
        font-size: 14px;
        margin-bottom: 15px;
    }
    
    .admin-info strong {
        color: #FEA116;
    }
    
    .sidebar-toggle {
        position: fixed;
        top: 20px;
        left: 290px;
        background: #13357B;
        color: white;
        border: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1001;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        cursor: pointer;
    }
    
    .sidebar-toggle.collapsed {
        left: 80px;
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
    
    .role-badge {
        background: linear-gradient(135deg, #FEA116 0%, #ff9800 100%);
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .admin-sidebar {
            transform: translateX(-100%);
        }
        
        .admin-sidebar.show {
            transform: translateX(0);
        }
        
        .main-content {
            margin-left: 0;
        }
        
        .sidebar-toggle {
            left: 20px;
        }
    }
</style>

<!-- Admin Sidebar -->
<div class="admin-sidebar" id="adminSidebar">
    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <a href="admin-dashboard.php" class="sidebar-brand">
            <i class="fas fa-tachometer-alt"></i>
            <span>Admin Panel</span>
        </a>
    </div>
    
    <!-- Sidebar Navigation -->
    <div class="sidebar-nav">
        <!-- Main Navigation -->
        <div class="nav-section">
            <div class="nav-section-title">Main</div>
            <div class="nav-item">
                <a href="admin-dashboard.php" class="nav-link <?php echo ($current_page === 'admin-dashboard.php') ? 'active' : ''; ?>" data-tooltip="Dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-clients.php" class="nav-link <?php echo ($current_page === 'admin-clients.php') ? 'active' : ''; ?>" data-tooltip="All Clients">
                    <i class="fas fa-users"></i>
                    <span>All Clients</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-search.php" class="nav-link <?php echo ($current_page === 'admin-search.php') ? 'active' : ''; ?>" data-tooltip="Search Clients">
                    <i class="fas fa-search"></i>
                    <span>Search Clients</span>
                </a>
            </div>
        </div>
        
        <!-- Financial Management -->
        <div class="nav-section">
            <div class="nav-section-title">Financial</div>
            <div class="nav-item">
                <a href="admin-invoices.php" class="nav-link <?php echo ($current_page === 'admin-invoices.php' || $current_page === 'admin-invoice-view.php') ? 'active' : ''; ?>" data-tooltip="Invoices">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Invoices</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-payments.php" class="nav-link <?php echo ($current_page === 'admin-payments.php') ? 'active' : ''; ?>" data-tooltip="Payments">
                    <i class="fas fa-credit-card"></i>
                    <span>Payments</span>
                </a>
            </div>
        </div>
        
        <!-- Content Management -->
        <div class="nav-section">
            <div class="nav-section-title">Content</div>
            <div class="nav-item">
                <a href="admin-documents.php" class="nav-link <?php echo ($current_page === 'admin-documents.php') ? 'active' : ''; ?>" data-tooltip="All Documents">
                    <i class="fas fa-files"></i>
                    <span>All Documents</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-visa-content.php" class="nav-link <?php echo ($current_page === 'admin-visa-content.php') ? 'active' : ''; ?>" data-tooltip="Visa Content">
                    <i class="fas fa-passport"></i>
                    <span>Visa Content</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-faq.php" class="nav-link <?php echo ($current_page === 'admin-faq.php') ? 'active' : ''; ?>" data-tooltip="FAQ Management">
                    <i class="fas fa-question-circle"></i>
                    <span>FAQ Management</span>
                </a>
            </div>
        </div>
        
        <!-- Blog Management -->
        <div class="nav-section">
            <div class="nav-section-title">Blog</div>
            <div class="nav-item">
                <a href="admin-blog.php" class="nav-link <?php echo (in_array($current_page, ['admin-blog.php', 'admin-blog-create.php', 'admin-blog-edit.php'])) ? 'active' : ''; ?>" data-tooltip="Blog Posts">
                    <i class="fas fa-blog"></i>
                    <span>Blog Posts</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-blog-categories.php" class="nav-link <?php echo ($current_page === 'admin-blog-categories.php') ? 'active' : ''; ?>" data-tooltip="Categories">
                    <i class="fas fa-folder"></i>
                    <span>Categories</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-blog-comments.php" class="nav-link <?php echo ($current_page === 'admin-blog-comments.php') ? 'active' : ''; ?>" data-tooltip="Comments">
                    <i class="fas fa-comments"></i>
                    <span>Comments</span>
                </a>
            </div>
        </div>
        
        <!-- SEO Management -->
        <div class="nav-section">
            <div class="nav-section-title">SEO</div>
            <div class="nav-item">
                <a href="setup-seo-tables.php" class="nav-link <?php echo ($current_page === 'setup-seo-tables.php') ? 'active' : ''; ?>" data-tooltip="SEO Setup">
                    <i class="fas fa-rocket"></i>
                    <span>SEO Setup</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-seo-config.php" class="nav-link <?php echo ($current_page === 'admin-seo-config.php') ? 'active' : ''; ?>" data-tooltip="SEO Configuration">
                    <i class="fas fa-cogs"></i>
                    <span>SEO Config</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-seo-analytics.php" class="nav-link <?php echo ($current_page === 'admin-seo-analytics.php') ? 'active' : ''; ?>" data-tooltip="SEO Analytics">
                    <i class="fas fa-chart-line"></i>
                    <span>SEO Analytics</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-seo-audit.php" class="nav-link <?php echo ($current_page === 'admin-seo-audit.php') ? 'active' : ''; ?>" data-tooltip="SEO Audit">
                    <i class="fas fa-search"></i>
                    <span>SEO Audit</span>
                </a>
            </div>
        </div>
        
        <!-- System Management -->
        <div class="nav-section">
            <div class="nav-section-title">System</div>
            <div class="nav-item">
                <a href="admin-export.php" class="nav-link <?php echo ($current_page === 'admin-export.php') ? 'active' : ''; ?>" data-tooltip="Export Data">
                    <i class="fas fa-download"></i>
                    <span>Export Data</span>
                </a>
            </div>
            <div class="nav-item">
                <a href="admin-settings.php" class="nav-link <?php echo ($current_page === 'admin-settings.php') ? 'active' : ''; ?>" data-tooltip="Settings">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
            </div>
            <?php if (isset($_SESSION['admin_role']) && $_SESSION['admin_role'] === 'Super Admin'): ?>
            <div class="nav-item">
                <a href="admin-stripe-config.php" class="nav-link <?php echo ($current_page === 'admin-stripe-config.php') ? 'active' : ''; ?>" data-tooltip="Stripe Config">
                    <i class="fab fa-stripe"></i>
                    <span>Stripe Config</span>
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Quick Actions -->
        <div class="nav-section">
            <div class="nav-section-title">Quick Actions</div>
            <div class="nav-item">
                <a href="/" class="nav-link" target="_blank">
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
                    <span>Call Support</span>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <?php if (isset($_SESSION['admin_name']) && isset($_SESSION['admin_role'])): ?>
        <div class="admin-info">
            <div><strong>Welcome,</strong></div>
            <div><?php echo htmlspecialchars($_SESSION['admin_name']); ?></div>
            <div class="mt-2">
                <span class="role-badge"><?php echo htmlspecialchars($_SESSION['admin_role']); ?></span>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="d-grid gap-2">
            <a href="admin-logout.php" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt me-2"></i>Logout
            </a>
        </div>
    </div>
</div>

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
    <i class="fas fa-chevron-left"></i>
</button>

<!-- Main Content Wrapper -->
<div class="main-content" id="mainContent">

<script>
function toggleSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const mainContent = document.getElementById('mainContent');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
    toggleBtn.classList.toggle('collapsed');
    
    // Update toggle button icon
    const icon = toggleBtn.querySelector('i');
    if (sidebar.classList.contains('collapsed')) {
        icon.className = 'fas fa-chevron-right';
    } else {
        icon.className = 'fas fa-chevron-left';
    }
}

// Mobile sidebar toggle
function toggleMobileSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    sidebar.classList.toggle('show');
}

// Handle mobile responsiveness
window.addEventListener('resize', function() {
    const sidebar = document.getElementById('adminSidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (window.innerWidth <= 768) {
        sidebar.classList.remove('collapsed');
        mainContent.classList.remove('expanded');
    }
});

// Close mobile sidebar when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('adminSidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    
    if (window.innerWidth <= 768 && 
        !sidebar.contains(event.target) && 
        !toggleBtn.contains(event.target)) {
        sidebar.classList.remove('show');
    }
});
</script>
