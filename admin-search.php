<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Initialize search parameters
$search_results = [];
$total_results = 0;
$search_performed = false;

// Handle search request
if (isset($_GET['search']) || isset($_POST['search'])) {
    $search_performed = true;
    
    // Get search parameters
    $search_term = sanitizeInput($_GET['search_term'] ?? $_POST['search_term'] ?? '');
    $visa_type = sanitizeInput($_GET['visa_type'] ?? $_POST['visa_type'] ?? '');
    $status = sanitizeInput($_GET['status'] ?? $_POST['status'] ?? '');
    $country = sanitizeInput($_GET['country'] ?? $_POST['country'] ?? '');
    $date_from = $_GET['date_from'] ?? $_POST['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? $_POST['date_to'] ?? '';
    $gender = sanitizeInput($_GET['gender'] ?? $_POST['gender'] ?? '');
    $work_type = sanitizeInput($_GET['work_type'] ?? $_POST['work_type'] ?? '');
    $deposit_min = floatval($_GET['deposit_min'] ?? $_POST['deposit_min'] ?? 0);
    $deposit_max = floatval($_GET['deposit_max'] ?? $_POST['deposit_max'] ?? 0);
    
    try {
        // Build dynamic search query
        $sql = "SELECT * FROM clients WHERE 1=1";
        $params = [];
        
        // Text search across multiple fields
        if ($search_term) {
            $sql .= " AND (full_name LIKE ? OR client_email LIKE ? OR reference_id LIKE ? OR mobile_number LIKE ? OR passport_number LIKE ?)";
            $search_pattern = "%{$search_term}%";
            $params = array_merge($params, [$search_pattern, $search_pattern, $search_pattern, $search_pattern, $search_pattern]);
        }
        
        // Visa type filter
        if ($visa_type) {
            $sql .= " AND visa_type = ?";
            $params[] = $visa_type;
        }
        
        // Status filter
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        // Country filter
        if ($country) {
            $sql .= " AND country LIKE ?";
            $params[] = "%{$country}%";
        }
        
        // Date range filter
        if ($date_from) {
            $sql .= " AND submitted_date >= ?";
            $params[] = $date_from;
        }
        
        if ($date_to) {
            $sql .= " AND submitted_date <= ?";
            $params[] = $date_to . ' 23:59:59';
        }
        
        // Gender filter
        if ($gender) {
            $sql .= " AND gender = ?";
            $params[] = $gender;
        }
        
        // Work type filter
        if ($work_type) {
            $sql .= " AND work_type LIKE ?";
            $params[] = "%{$work_type}%";
        }
        
        // Deposit range filter
        if ($deposit_min > 0) {
            $sql .= " AND deposit_paid >= ?";
            $params[] = $deposit_min;
        }
        
        if ($deposit_max > 0) {
            $sql .= " AND deposit_paid <= ?";
            $params[] = $deposit_max;
        }
        
        // Get total count
        $count_sql = str_replace("SELECT *", "SELECT COUNT(*)", $sql);
        $count_stmt = $db->prepare($count_sql);
        $count_stmt->execute($params);
        $total_results = $count_stmt->fetch()['COUNT(*)'];
        
        // Add pagination and sorting
        $page = intval($_GET['page'] ?? 1);
        $per_page = 20;
        $offset = ($page - 1) * $per_page;
        
        $sort_by = sanitizeInput($_GET['sort_by'] ?? 'submitted_date');
        $sort_order = sanitizeInput($_GET['sort_order'] ?? 'DESC');
        
        // Validate sort parameters
        $allowed_sort_fields = ['reference_id', 'full_name', 'client_email', 'visa_type', 'submitted_date', 'status', 'deposit_paid'];
        if (!in_array($sort_by, $allowed_sort_fields)) {
            $sort_by = 'submitted_date';
        }
        
        if (!in_array(strtoupper($sort_order), ['ASC', 'DESC'])) {
            $sort_order = 'DESC';
        }
        
        $sql .= " ORDER BY {$sort_by} {$sort_order} LIMIT {$per_page} OFFSET {$offset}";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $search_results = $stmt->fetchAll();
        
        // Log search activity
        logActivity($_SESSION['admin_id'], null, 'Search', "Searched for: {$search_term}", $db);
        
    } catch (Exception $e) {
        error_log("Search error: " . $e->getMessage());
        $_SESSION['error'] = "Search failed. Please try again.";
    }
}

// Get unique countries for filter dropdown
try {
    $countries_stmt = $db->prepare("SELECT DISTINCT country FROM clients WHERE country IS NOT NULL AND country != '' ORDER BY country");
    $countries_stmt->execute();
    $countries = $countries_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $countries = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Advanced Search - M25 Travel & Tour Agency</title>
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
        .search-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .top-navbar {
            background: #13357B !important;
        }
        .filter-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .search-stats {
            background: linear-gradient(135deg, #13357B, #FEA116);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .result-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
            transition: all 0.3s;
        }
        .result-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .pagination-wrapper {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <?php include 'includes/admin-sidebar.php'; ?>
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar">
                    <div class="container-fluid">
                        <span class="navbar-brand">Advanced Search</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Search Content -->
                <div class="container-fluid p-4">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Search Form -->
                    <div class="search-card">
                        <h4 class="mb-4"><i class="fas fa-search me-2"></i>Advanced Client Search</h4>
                        
                        <form method="GET" action="" id="searchForm">
                            <!-- Quick Search -->
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <label class="form-label">Quick Search</label>
                                    <input type="text" class="form-control form-control-lg" name="search_term" 
                                           placeholder="Search by name, email, reference ID, phone, or passport..." 
                                           value="<?php echo htmlspecialchars($_GET['search_term'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <button type="submit" name="search" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Advanced Filters -->
                            <div class="filter-section">
                                <h6 class="mb-3">
                                    <i class="fas fa-filter me-2"></i>Advanced Filters
                                    <button type="button" class="btn btn-sm btn-outline-secondary ms-2" onclick="toggleFilters()">
                                        <i class="fas fa-chevron-down" id="filterToggleIcon"></i>
                                    </button>
                                </h6>
                                
                                <div id="advancedFilters" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Visa Type</label>
                                            <select class="form-select" name="visa_type">
                                                <option value="">All Types</option>
                                                <option value="Visit" <?php echo ($_GET['visa_type'] ?? '') == 'Visit' ? 'selected' : ''; ?>>Visit</option>
                                                <option value="Work" <?php echo ($_GET['visa_type'] ?? '') == 'Work' ? 'selected' : ''; ?>>Work</option>
                                                <option value="Study" <?php echo ($_GET['visa_type'] ?? '') == 'Study' ? 'selected' : ''; ?>>Study</option>
                                                <option value="Other" <?php echo ($_GET['visa_type'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" name="status">
                                                <option value="">All Statuses</option>
                                                <option value="Active" <?php echo ($_GET['status'] ?? '') == 'Active' ? 'selected' : ''; ?>>Active</option>
                                                <option value="Completed" <?php echo ($_GET['status'] ?? '') == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                                <option value="Inactive" <?php echo ($_GET['status'] ?? '') == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Country</label>
                                            <select class="form-select" name="country">
                                                <option value="">All Countries</option>
                                                <?php foreach ($countries as $country_option): ?>
                                                    <option value="<?php echo htmlspecialchars($country_option); ?>" 
                                                            <?php echo ($_GET['country'] ?? '') == $country_option ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($country_option); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Gender</label>
                                            <select class="form-select" name="gender">
                                                <option value="">All Genders</option>
                                                <option value="Male" <?php echo ($_GET['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                                                <option value="Female" <?php echo ($_GET['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                                                <option value="Other" <?php echo ($_GET['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Date From</label>
                                            <input type="date" class="form-control" name="date_from" 
                                                   value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Date To</label>
                                            <input type="date" class="form-control" name="date_to" 
                                                   value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Min Deposit</label>
                                            <input type="number" class="form-control" name="deposit_min" step="0.01" 
                                                   value="<?php echo htmlspecialchars($_GET['deposit_min'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Max Deposit</label>
                                            <input type="number" class="form-control" name="deposit_max" step="0.01" 
                                                   value="<?php echo htmlspecialchars($_GET['deposit_max'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <button type="submit" name="search" class="btn btn-primary me-2">
                                                <i class="fas fa-search me-2"></i>Apply Filters
                                            </button>
                                            <a href="admin-search.php" class="btn btn-outline-secondary">
                                                <i class="fas fa-times me-2"></i>Clear All
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <?php if ($search_performed): ?>
                        <!-- Search Statistics -->
                        <div class="search-stats">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h5 class="mb-0">
                                        <i class="fas fa-chart-bar me-2"></i>Search Results: <?php echo number_format($total_results); ?> clients found
                                    </h5>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="btn-group">
                                        <a href="admin-export.php?<?php echo http_build_query($_GET); ?>" class="btn btn-light btn-sm">
                                            <i class="fas fa-download me-2"></i>Export Results
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sorting Options -->
                        <div class="search-card">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <h6 class="mb-0">Sort Results:</h6>
                                </div>
                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <select class="form-select form-select-sm" name="sort_by" onchange="updateSort()">
                                                <option value="submitted_date" <?php echo ($_GET['sort_by'] ?? 'submitted_date') == 'submitted_date' ? 'selected' : ''; ?>>Date Submitted</option>
                                                <option value="full_name" <?php echo ($_GET['sort_by'] ?? '') == 'full_name' ? 'selected' : ''; ?>>Name</option>
                                                <option value="reference_id" <?php echo ($_GET['sort_by'] ?? '') == 'reference_id' ? 'selected' : ''; ?>>Reference ID</option>
                                                <option value="visa_type" <?php echo ($_GET['sort_by'] ?? '') == 'visa_type' ? 'selected' : ''; ?>>Visa Type</option>
                                                <option value="deposit_paid" <?php echo ($_GET['sort_by'] ?? '') == 'deposit_paid' ? 'selected' : ''; ?>>Deposit Amount</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <select class="form-select form-select-sm" name="sort_order" onchange="updateSort()">
                                                <option value="DESC" <?php echo ($_GET['sort_order'] ?? 'DESC') == 'DESC' ? 'selected' : ''; ?>>Descending</option>
                                                <option value="ASC" <?php echo ($_GET['sort_order'] ?? '') == 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Search Results -->
                        <?php if (empty($search_results)): ?>
                            <div class="search-card text-center">
                                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No clients found matching your search criteria</h5>
                                <p class="text-muted">Try adjusting your search terms or filters</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($search_results as $client): ?>
                                <div class="result-item">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="mb-2">
                                                <span class="text-primary"><?php echo htmlspecialchars($client['reference_id']); ?></span>
                                                - <?php echo htmlspecialchars($client['full_name']); ?>
                                            </h6>
                                            <p class="mb-2">
                                                <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($client['client_email']); ?>
                                                <i class="fas fa-phone ms-3 me-2"></i><?php echo htmlspecialchars($client['mobile_number']); ?>
                                            </p>
                                            <p class="mb-0">
                                                <span class="badge bg-info me-2"><?php echo htmlspecialchars($client['visa_type']); ?></span>
                                                <span class="badge bg-secondary me-2"><?php echo htmlspecialchars($client['country']); ?></span>
                                                <?php
                                                $status_class = $client['status'] == 'Active' ? 'bg-warning' : 
                                                              ($client['status'] == 'Completed' ? 'bg-success' : 'bg-secondary');
                                                ?>
                                                <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($client['status']); ?></span>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <p class="mb-2">
                                                <strong>Submitted:</strong> <?php echo formatDate($client['submitted_date'], 'M d, Y'); ?>
                                            </p>
                                            <p class="mb-3">
                                                <strong>Deposit:</strong> <?php echo formatCurrencyForUser($client['deposit_paid'], $db, 'admin'); ?>
                                            </p>
                                            <div class="btn-group">
                                                <a href="admin-client-view.php?id=<?php echo $client['reference_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                <a href="admin-client-edit.php?id=<?php echo $client['reference_id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <!-- Pagination -->
                            <?php
                            $current_page = intval($_GET['page'] ?? 1);
                            $per_page = 20;
                            $total_pages = ceil($total_results / $per_page);
                            
                            if ($total_pages > 1):
                            ?>
                                <div class="pagination-wrapper">
                                    <nav>
                                        <ul class="pagination justify-content-center mb-0">
                                            <?php if ($current_page > 1): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page - 1])); ?>">
                                                        <i class="fas fa-chevron-left"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                            
                                            <?php for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++): ?>
                                                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <?php if ($current_page < $total_pages): ?>
                                                <li class="page-item">
                                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $current_page + 1])); ?>">
                                                        <i class="fas fa-chevron-right"></i>
                                                    </a>
                                                </li>
                                            <?php endif; ?>
                                        </ul>
                                    </nav>
                                    
                                    <div class="text-center mt-3">
                                        <small class="text-muted">
                                            Showing <?php echo (($current_page - 1) * $per_page) + 1; ?> to 
                                            <?php echo min($current_page * $per_page, $total_results); ?> of 
                                            <?php echo number_format($total_results); ?> results
                                        </small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleFilters() {
            const filters = document.getElementById('advancedFilters');
            const icon = document.getElementById('filterToggleIcon');
            
            if (filters.style.display === 'none') {
                filters.style.display = 'block';
                icon.classList.remove('fa-chevron-down');
                icon.classList.add('fa-chevron-up');
            } else {
                filters.style.display = 'none';
                icon.classList.remove('fa-chevron-up');
                icon.classList.add('fa-chevron-down');
            }
        }
        
        function updateSort() {
            const sortBy = document.querySelector('select[name="sort_by"]').value;
            const sortOrder = document.querySelector('select[name="sort_order"]').value;
            
            const url = new URL(window.location);
            url.searchParams.set('sort_by', sortBy);
            url.searchParams.set('sort_order', sortOrder);
            url.searchParams.set('page', '1'); // Reset to first page
            
            window.location = url.toString();
        }
        
        // Show advanced filters if any are set
        <?php if (!empty($_GET['visa_type']) || !empty($_GET['status']) || !empty($_GET['country']) || !empty($_GET['date_from'])): ?>
            toggleFilters();
        <?php endif; ?>
    </script>
    <?php include 'includes/admin-sidebar-close.php'; ?>
</body>
</html>
