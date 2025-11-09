<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: admin-login.html?timeout=1');
    exit();
}

// Pagination settings
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

try {
    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(full_name LIKE ? OR client_email LIKE ? OR reference_id LIKE ? OR mobile_number LIKE ?)";
        $search_param = "%{$search}%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if (!empty($status_filter)) {
        $where_conditions[] = "status = ?";
        $params[] = $status_filter;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM clients {$where_clause}";
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($params);
    $total_clients = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_clients / $per_page);
    
    // Get clients for current page
    $sql = "SELECT * FROM clients {$where_clause} ORDER BY submitted_date DESC LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $clients = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Admin clients error: " . $e->getMessage());
    $clients = [];
    $total_clients = 0;
    $total_pages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>All Clients - M25 Travel & Tour Agency</title>
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
        }
        .top-navbar {
            background: #13357B !important;
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        .badge-status {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        .search-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body>
    <?php include 'includes/admin-sidebar.php'; ?>
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar">
                    <div class="container-fluid">
                        <span class="navbar-brand">All Clients</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="container-fluid p-4">
                    
                    <!-- Search Form -->
                    <div class="search-form">
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search Clients</label>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Name, email, reference ID, or phone">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status Filter</label>
                                <select class="form-select" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Processing" <?php echo $status_filter === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                    <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?php echo $status_filter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="Completed" <?php echo $status_filter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <a href="admin-clients.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </a>
                                    <a href="client-registration.php" class="btn btn-success">
                                        <i class="fas fa-plus me-2"></i>New Client
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Results Summary -->
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">
                                Client List 
                                <span class="badge bg-primary"><?php echo number_format($total_clients); ?> total</span>
                                <?php if (!empty($search) || !empty($status_filter)): ?>
                                    <span class="badge bg-info">Filtered</span>
                                <?php endif; ?>
                            </h5>
                            <div>
                                <a href="admin-export.php" class="btn btn-outline-success">
                                    <i class="fas fa-download me-2"></i>Export
                                </a>
                            </div>
                        </div>
                        
                        <?php if (empty($clients)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No clients found</h5>
                                <?php if (!empty($search) || !empty($status_filter)): ?>
                                    <p class="text-muted">Try adjusting your search criteria</p>
                                    <a href="admin-clients.php" class="btn btn-primary">View All Clients</a>
                                <?php else: ?>
                                    <p class="text-muted">No clients have registered yet</p>
                                    <a href="client-registration.php" class="btn btn-primary">Add First Client</a>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            
                            <!-- Clients Table -->
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Reference ID</th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Visa Type</th>
                                            <th>Status</th>
                                            <th>Submitted</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($clients as $client): ?>
                                            <tr>
                                                <td>
                                                    <strong class="text-primary"><?php echo htmlspecialchars($client['reference_id']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($client['full_name']); ?></td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($client['client_email']); ?></small>
                                                </td>
                                                <td>
                                                    <small><?php echo htmlspecialchars($client['mobile_number']); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo htmlspecialchars($client['visa_type']); ?></span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = match($client['status']) {
                                                        'Pending' => 'bg-warning text-dark',
                                                        'Processing' => 'bg-info',
                                                        'Approved' => 'bg-success',
                                                        'Rejected' => 'bg-danger',
                                                        'Completed' => 'bg-success',
                                                        default => 'bg-secondary'
                                                    };
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($client['status']); ?></span>
                                                </td>
                                                <td>
                                                    <small><?php echo formatDate($client['submitted_date'], 'M d, Y'); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="admin-client-view.php?id=<?php echo urlencode($client['reference_id']); ?>" 
                                                           class="btn btn-outline-primary" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="admin-client-edit.php?id=<?php echo urlencode($client['reference_id']); ?>" 
                                                           class="btn btn-outline-secondary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if (in_array($_SESSION['admin_role'], ['Super Admin', 'Admin'])): ?>
                                                        <a href="admin-login-as-client.php?client_id=<?php echo urlencode($client['reference_id']); ?>" 
                                                           class="btn btn-outline-success" title="Login as Client"
                                                           onclick="return confirm('Login as <?php echo htmlspecialchars($client['full_name']); ?>?\n\nThis will switch you to their client dashboard.')">
                                                            <i class="fas fa-sign-in-alt"></i>
                                                        </a>
                                                        <?php endif; ?>
                                                        <button class="btn btn-outline-danger" title="Delete" 
                                                                onclick="confirmDelete('<?php echo htmlspecialchars($client['reference_id']); ?>', '<?php echo htmlspecialchars($client['full_name']); ?>')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Clients pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmDelete(referenceId, clientName) {
            if (confirm(`Are you sure you want to delete client "${clientName}" (${referenceId})?\n\nThis action cannot be undone.`)) {
                // Create a form and submit it
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'admin-client-delete.php';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'reference_id';
                idInput.value = referenceId;
                
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <?php include 'includes/admin-sidebar-close.php'; ?>
</body>
</html>
