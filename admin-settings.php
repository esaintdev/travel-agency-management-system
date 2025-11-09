<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: admin-login.html?timeout=1');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'change_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        try {
            // Get current admin details
            $stmt = $db->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
            $stmt->execute([$_SESSION['admin_id']]);
            $admin = $stmt->fetch();
            
            if (!$admin || !verifyPassword($current_password, $admin['password_hash'])) {
                throw new Exception("Current password is incorrect.");
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match.");
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception("New password must be at least 6 characters long.");
            }
            
            // Update password
            $new_hash = generateSecurePassword($new_password);
            $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$new_hash, $_SESSION['admin_id']]);
            
            $_SESSION['success'] = "Password changed successfully.";
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    }
}

// Get system statistics
try {
    $stats = [];
    
    // Total clients
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM clients");
    $stmt->execute();
    $stats['total_clients'] = $stmt->fetch()['count'];
    
    // Total admins
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM admin_users");
    $stmt->execute();
    $stats['total_admins'] = $stmt->fetch()['count'];
    
    // Database size
    $stmt = $db->prepare("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'DB Size in MB' FROM information_schema.tables WHERE table_schema = ?");
    $stmt->execute([DB_NAME]);
    $stats['db_size'] = $stmt->fetch()['DB Size in MB'] ?? 0;
    
} catch (Exception $e) {
    $stats = ['total_clients' => 0, 'total_admins' => 0, 'db_size' => 0];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Settings - M25 Travel & Tour Agency</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Libraries Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .sidebar {
            background: #13357B;
            min-height: 100vh;
            padding: 0;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #FEA116;
            color: white;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .top-navbar {
            background: #13357B !important;
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <?php include 'includes/admin-sidebar.php'; ?>
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar">
                    <div class="container-fluid">
                        <span class="navbar-brand">Settings</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="container-fluid p-4">
                    
                    <!-- Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['success']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <!-- System Statistics -->
                    <div class="content-card">
                        <h5 class="mb-4"><i class="fas fa-chart-bar me-2"></i>System Statistics</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="stat-number text-primary"><?php echo number_format($stats['total_clients']); ?></div>
                                    <div class="text-muted">Total Clients</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="stat-number text-success"><?php echo number_format($stats['total_admins']); ?></div>
                                    <div class="text-muted">Admin Users</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="stat-card">
                                    <div class="stat-number text-info"><?php echo $stats['db_size']; ?> MB</div>
                                    <div class="text-muted">Database Size</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Change Password -->
                    <div class="content-card">
                        <h5 class="mb-4"><i class="fas fa-lock me-2"></i>Change Password</h5>
                        
                        <form method="POST" class="row g-3">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="col-md-6">
                                <label class="form-label">Current Password</label>
                                <input type="password" class="form-control" name="current_password" required>
                            </div>
                            <div class="col-md-6"></div>
                            
                            <div class="col-md-6">
                                <label class="form-label">New Password</label>
                                <input type="password" class="form-control" name="new_password" required minlength="6">
                                <small class="form-text text-muted">Minimum 6 characters</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" name="confirm_password" required minlength="6">
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- System Information -->
                    <div class="content-card">
                        <h5 class="mb-4"><i class="fas fa-info-circle me-2"></i>System Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Application Name:</strong></td>
                                        <td><?php echo APP_NAME; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>PHP Version:</strong></td>
                                        <td><?php echo PHP_VERSION; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Database Host:</strong></td>
                                        <td><?php echo DB_HOST; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Database Name:</strong></td>
                                        <td><?php echo DB_NAME; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Server Time:</strong></td>
                                        <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Timezone:</strong></td>
                                        <td><?php echo date_default_timezone_get(); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Upload Directory:</strong></td>
                                        <td><?php echo UPLOAD_DIR; ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Max File Size:</strong></td>
                                        <td><?php echo number_format(MAX_FILE_SIZE / 1024 / 1024, 1); ?> MB</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="content-card">
                        <h5 class="mb-4"><i class="fas fa-tools me-2"></i>Quick Actions</h5>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="admin-export.php" class="btn btn-success w-100 py-3">
                                    <i class="fas fa-download fa-2x mb-2"></i><br>
                                    Export All Data
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="check_setup.php" class="btn btn-info w-100 py-3" target="_blank">
                                    <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                    System Check
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="admin-users.php" class="btn btn-warning w-100 py-3">
                                    <i class="fas fa-user-shield fa-2x mb-2"></i><br>
                                    Manage Admins
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="admin-logout.php" class="btn btn-danger w-100 py-3">
                                    <i class="fas fa-sign-out-alt fa-2x mb-2"></i><br>
                                    Logout
                                </a>
                            </div>
                        </div>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Password confirmation validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.querySelector('input[name="new_password"]').value;
            const confirmPassword = document.querySelector('input[name="confirm_password"]').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('New passwords do not match!');
                return false;
            }
        });
    </script>
    <?php include 'includes/admin-sidebar-close.php'; ?>
</body>
</html>
