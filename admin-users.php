<?php
require_once 'config.php';

// Check if user is logged in and has admin privileges
requireLogin();

// Check if user has permission (only Super Admin can manage users)
if ($_SESSION['admin_role'] !== 'Super Admin') {
    $_SESSION['error'] = "Access denied. Only Super Admins can manage users.";
    header('Location: admin-dashboard.php');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    // Validate input
                    $username = sanitizeInput($_POST['username']);
                    $email = sanitizeInput($_POST['email']);
                    $full_name = sanitizeInput($_POST['full_name']);
                    $role = sanitizeInput($_POST['role']);
                    $password = $_POST['password'];
                    
                    if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
                        throw new Exception("All fields are required.");
                    }
                    
                    if (!validateEmail($email)) {
                        throw new Exception("Invalid email format.");
                    }
                    
                    if (strlen($password) < 6) {
                        throw new Exception("Password must be at least 6 characters long.");
                    }
                    
                    // Check if username or email already exists
                    $stmt = $db->prepare("SELECT id FROM admin_users WHERE username = ? OR email = ?");
                    $stmt->execute([$username, $email]);
                    if ($stmt->fetch()) {
                        throw new Exception("Username or email already exists.");
                    }
                    
                    // Create user
                    $password_hash = generateSecurePassword($password);
                    $stmt = $db->prepare("INSERT INTO admin_users (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $password_hash, $full_name, $role]);
                    
                    $_SESSION['success'] = "Admin user created successfully!";
                    logActivity($_SESSION['admin_id'], null, 'User Management', "Created user: $username", $db);
                    break;
                    
                case 'update':
                    $user_id = intval($_POST['user_id']);
                    $username = sanitizeInput($_POST['username']);
                    $email = sanitizeInput($_POST['email']);
                    $full_name = sanitizeInput($_POST['full_name']);
                    $role = sanitizeInput($_POST['role']);
                    $status = sanitizeInput($_POST['status']);
                    
                    if (empty($username) || empty($email) || empty($full_name)) {
                        throw new Exception("All fields are required.");
                    }
                    
                    if (!validateEmail($email)) {
                        throw new Exception("Invalid email format.");
                    }
                    
                    // Check if username or email already exists for other users
                    $stmt = $db->prepare("SELECT id FROM admin_users WHERE (username = ? OR email = ?) AND id != ?");
                    $stmt->execute([$username, $email, $user_id]);
                    if ($stmt->fetch()) {
                        throw new Exception("Username or email already exists.");
                    }
                    
                    // Update user
                    $stmt = $db->prepare("UPDATE admin_users SET username = ?, email = ?, full_name = ?, role = ?, status = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $full_name, $role, $status, $user_id]);
                    
                    $_SESSION['success'] = "Admin user updated successfully!";
                    logActivity($_SESSION['admin_id'], null, 'User Management', "Updated user: $username", $db);
                    break;
                    
                case 'change_password':
                    $user_id = intval($_POST['user_id']);
                    $new_password = $_POST['new_password'];
                    $confirm_password = $_POST['confirm_password'];
                    
                    if (empty($new_password) || empty($confirm_password)) {
                        throw new Exception("Password fields are required.");
                    }
                    
                    if ($new_password !== $confirm_password) {
                        throw new Exception("Passwords do not match.");
                    }
                    
                    if (strlen($new_password) < 6) {
                        throw new Exception("Password must be at least 6 characters long.");
                    }
                    
                    // Update password
                    $password_hash = generateSecurePassword($new_password);
                    $stmt = $db->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$password_hash, $user_id]);
                    
                    $_SESSION['success'] = "Password changed successfully!";
                    logActivity($_SESSION['admin_id'], null, 'User Management', "Changed password for user ID: $user_id", $db);
                    break;
                    
                case 'toggle_status':
                    $user_id = intval($_POST['user_id']);
                    
                    // Don't allow disabling own account
                    if ($user_id == $_SESSION['admin_id']) {
                        throw new Exception("You cannot disable your own account.");
                    }
                    
                    $stmt = $db->prepare("UPDATE admin_users SET status = CASE WHEN status = 'Active' THEN 'Inactive' ELSE 'Active' END WHERE id = ?");
                    $stmt->execute([$user_id]);
                    
                    $_SESSION['success'] = "User status updated!";
                    logActivity($_SESSION['admin_id'], null, 'User Management', "Toggled status for user ID: $user_id", $db);
                    break;
                    
                case 'delete':
                    $user_id = intval($_POST['user_id']);
                    
                    // Don't allow deleting own account
                    if ($user_id == $_SESSION['admin_id']) {
                        throw new Exception("You cannot delete your own account.");
                    }
                    
                    // Don't delete if it's the last Super Admin
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM admin_users WHERE role = 'Super Admin' AND status = 'Active'");
                    $stmt->execute();
                    $super_admin_count = $stmt->fetch()['count'];
                    
                    $stmt = $db->prepare("SELECT role FROM admin_users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user_role = $stmt->fetch()['role'];
                    
                    if ($user_role === 'Super Admin' && $super_admin_count <= 1) {
                        throw new Exception("Cannot delete the last Super Admin account.");
                    }
                    
                    $stmt = $db->prepare("DELETE FROM admin_users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    
                    $_SESSION['success'] = "Admin user deleted successfully!";
                    logActivity($_SESSION['admin_id'], null, 'User Management', "Deleted user ID: $user_id", $db);
                    break;
            }
        }
        
    } catch (Exception $e) {
        error_log("Admin user management error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: admin-users.php');
    exit();
}

// Get all admin users
try {
    $stmt = $db->prepare("SELECT id, username, email, full_name, role, status, last_login, created_at FROM admin_users ORDER BY role, full_name");
    $stmt->execute();
    $admin_users = $stmt->fetchAll();
} catch (Exception $e) {
    $admin_users = [];
    $_SESSION['error'] = "Failed to load admin users.";
}

// Get user for editing if requested
$edit_user = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE id = ?");
        $stmt->execute([intval($_GET['edit'])]);
        $edit_user = $stmt->fetch();
    } catch (Exception $e) {
        $_SESSION['error'] = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Users - M25 Travel & Tour Agency</title>
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
        .user-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .top-navbar {
            background: #13357B !important;
        }
        .user-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
        }
        .role-badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .status-active {
            background: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>

<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="p-3 text-center border-bottom border-secondary">
                    <h5 class="text-white mb-0">M25 Admin</h5>
                    <small class="text-light">Travel & Tour Agency</small>
                </div>
                
                <nav class="nav flex-column">
                    <a class="nav-link" href="admin-dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="admin-clients.php">
                        <i class="fas fa-users me-2"></i>All Clients
                    </a>
                    <a class="nav-link" href="admin-search.php">
                        <i class="fas fa-search me-2"></i>Search Clients
                    </a>
                    <a class="nav-link" href="admin-export.php">
                        <i class="fas fa-download me-2"></i>Export Data
                    </a>
                    <a class="nav-link" href="admin-email-templates.php">
                        <i class="fas fa-envelope me-2"></i>Email Templates
                    </a>
                    <a class="nav-link active" href="admin-users.php">
                        <i class="fas fa-user-cog me-2"></i>Admin Users
                    </a>
                    <a class="nav-link" href="admin-settings.php">
                        <i class="fas fa-cog me-2"></i>Settings
                    </a>
                    <a class="nav-link" href="admin-logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </nav>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar">
                    <div class="container-fluid">
                        <span class="navbar-brand">Admin User Management</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="container-fluid p-4">
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- User Form -->
                    <div class="user-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4>
                                <i class="fas fa-user-plus me-2"></i>
                                <?php echo $edit_user ? 'Edit Admin User' : 'Create New Admin User'; ?>
                            </h4>
                            <?php if ($edit_user): ?>
                                <a href="admin-users.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel Edit
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" action="">
                            <?php if ($edit_user): ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="action" value="create">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="username" required
                                           value="<?php echo htmlspecialchars($edit_user['username'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="email" required
                                           value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="full_name" required
                                           value="<?php echo htmlspecialchars($edit_user['full_name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select class="form-select" name="role" required>
                                        <option value="">Select Role</option>
                                        <option value="Super Admin" <?php echo ($edit_user['role'] ?? '') == 'Super Admin' ? 'selected' : ''; ?>>Super Admin</option>
                                        <option value="Admin" <?php echo ($edit_user['role'] ?? '') == 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                        <option value="Staff" <?php echo ($edit_user['role'] ?? '') == 'Staff' ? 'selected' : ''; ?>>Staff</option>
                                    </select>
                                </div>
                                <?php if ($edit_user): ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="Active" <?php echo ($edit_user['status'] ?? '') == 'Active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="Inactive" <?php echo ($edit_user['status'] ?? '') == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                <?php else: ?>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password" required minlength="6">
                                        <small class="form-text text-muted">Minimum 6 characters</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo $edit_user ? 'Update User' : 'Create User'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Existing Users -->
                    <div class="user-card">
                        <h4 class="mb-4"><i class="fas fa-users me-2"></i>Admin Users (<?php echo count($admin_users); ?>)</h4>
                        
                        <?php if (empty($admin_users)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No admin users found</h5>
                            </div>
                        <?php else: ?>
                            <?php foreach ($admin_users as $user): ?>
                                <div class="user-item">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h6 class="mb-2">
                                                <?php echo htmlspecialchars($user['full_name']); ?>
                                                <?php if ($user['id'] == $_SESSION['admin_id']): ?>
                                                    <span class="badge bg-info ms-2">You</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="mb-2">
                                                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($user['username']); ?>
                                                <i class="fas fa-envelope ms-3 me-2"></i><?php echo htmlspecialchars($user['email']); ?>
                                            </p>
                                            <div>
                                                <?php
                                                $role_class = $user['role'] == 'Super Admin' ? 'bg-danger' : 
                                                            ($user['role'] == 'Admin' ? 'bg-warning' : 'bg-info');
                                                ?>
                                                <span class="role-badge <?php echo $role_class; ?> text-white me-2">
                                                    <?php echo htmlspecialchars($user['role']); ?>
                                                </span>
                                                <span class="role-badge <?php echo $user['status'] == 'Active' ? 'status-active' : 'status-inactive'; ?>">
                                                    <?php echo htmlspecialchars($user['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">
                                                <strong>Created:</strong><br>
                                                <?php echo formatDate($user['created_at'], 'M d, Y'); ?>
                                            </small>
                                            <?php if ($user['last_login']): ?>
                                                <br><small class="text-muted">
                                                    <strong>Last Login:</strong><br>
                                                    <?php echo formatDate($user['last_login'], 'M d, Y H:i'); ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-3 text-end">
                                            <div class="btn-group">
                                                <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="changePassword(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                                <?php if ($user['id'] != $_SESSION['admin_id']): ?>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="action" value="toggle_status">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Toggle Status">
                                                            <i class="fas fa-power-off"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div class="modal fade" id="passwordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Change Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="user_id" id="passwordUserId">
                        
                        <div class="mb-3">
                            <label class="form-label">User</label>
                            <input type="text" class="form-control" id="passwordUsername" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="new_password" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" name="confirm_password" required minlength="6">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Change Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function changePassword(userId, username) {
            document.getElementById('passwordUserId').value = userId;
            document.getElementById('passwordUsername').value = username;
            new bootstrap.Modal(document.getElementById('passwordModal')).show();
        }
        
        // Password confirmation validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordModal = document.getElementById('passwordModal');
            if (passwordModal) {
                passwordModal.addEventListener('submit', function(e) {
                    const newPassword = this.querySelector('input[name="new_password"]').value;
                    const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
                    
                    if (newPassword !== confirmPassword) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                    }
                });
            }
        });
    </script>
</body>
</html>
