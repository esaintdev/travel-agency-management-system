<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: admin-login.html?timeout=1');
    exit();
}

// Get dashboard statistics
try {
    // Total clients
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients");
    $stmt->execute();
    $total_clients = $stmt->fetch()['total'];
    
    // New clients this month
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients WHERE MONTH(submitted_date) = MONTH(CURRENT_DATE()) AND YEAR(submitted_date) = YEAR(CURRENT_DATE())");
    $stmt->execute();
    $new_clients_month = $stmt->fetch()['total'];
    
    // Pending applications
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients WHERE status = 'Active'");
    $stmt->execute();
    $pending_applications = $stmt->fetch()['total'];
    
    // Total revenue
    $stmt = $db->prepare("SELECT SUM(deposit_paid) as total FROM clients");
    $stmt->execute();
    $total_revenue = $stmt->fetch()['total'] ?? 0;
    
    // Recent clients
    $stmt = $db->prepare("SELECT reference_id, full_name, client_email, visa_type, submitted_date, status FROM clients ORDER BY submitted_date DESC LIMIT 10");
    $stmt->execute();
    $recent_clients = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $total_clients = $new_clients_month = $pending_applications = $total_revenue = 0;
    $recent_clients = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - M25 Travel & Tour Agency</title>
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
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
        }
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        .top-navbar {
            background: #13357B !important;
        }
        .badge-status {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        /* Custom Modal Styles */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #13357B 0%, #1e4a8c 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-header .btn-close {
            filter: invert(1);
        }
        
        .currency-icon {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        
        .modal-footer .btn {
            border-radius: 25px;
            padding: 8px 20px;
            font-weight: 500;
        }
        
        .modal-body .alert {
            border-radius: 10px;
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
                    <a class="nav-link active" href="admin-dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="admin-clients.php">
                        <i class="fas fa-users me-2"></i>All Clients
                    </a>
                    <a class="nav-link" href="admin-search.php">
                        <i class="fas fa-search me-2"></i>Search Clients
                    </a>
                    <a class="nav-link" href="admin-documents.php">
                        <i class="fas fa-files me-2"></i>All Documents
                    </a>
                    <a class="nav-link" href="admin-visa-content.php">
                        <i class="fas fa-passport me-2"></i>Visa Content
                    </a>
                    <a class="nav-link" href="admin-export.php">
                        <i class="fas fa-download me-2"></i>Export Data
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
                        <span class="navbar-brand">Dashboard</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Dashboard Content -->
                <div class="container-fluid p-4">
                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-primary">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stat-number text-primary"><?php echo number_format($total_clients); ?></div>
                                <div class="text-muted">Total Clients</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-success">
                                    <i class="fas fa-user-plus"></i>
                                </div>
                                <div class="stat-number text-success"><?php echo number_format($new_clients_month); ?></div>
                                <div class="text-muted">New This Month</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-number text-warning"><?php echo number_format($pending_applications); ?></div>
                                <div class="text-muted">Pending Applications</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-info">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="stat-number text-info"><?php echo formatCurrencyForUser($total_revenue, $db, 'admin'); ?></div>
                                <div class="text-muted">Total Revenue</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Clients Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="table-card">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="mb-0">Recent Client Registrations</h5>
                                    <a href="admin-clients.php" class="btn btn-primary">
                                        <i class="fas fa-eye me-2"></i>View All
                                    </a>
                                </div>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Reference ID</th>
                                                <th>Full Name</th>
                                                <th>Email</th>
                                                <th>Visa Type</th>
                                                <th>Submitted Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recent_clients)): ?>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-4">
                                                        <i class="fas fa-inbox fa-2x mb-3"></i><br>
                                                        No client registrations yet
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recent_clients as $client): ?>
                                                    <tr>
                                                        <td>
                                                            <strong class="text-primary"><?php echo htmlspecialchars($client['reference_id']); ?></strong>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($client['full_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($client['client_email']); ?></td>
                                                        <td>
                                                            <span class="badge bg-info"><?php echo htmlspecialchars($client['visa_type']); ?></span>
                                                        </td>
                                                        <td><?php echo formatDate($client['submitted_date'], 'M d, Y'); ?></td>
                                                        <td>
                                                            <?php
                                                            $status_class = $client['status'] == 'Active' ? 'bg-warning' : 
                                                                          ($client['status'] == 'Completed' ? 'bg-success' : 'bg-secondary');
                                                            ?>
                                                            <span class="badge <?php echo $status_class; ?>"><?php echo htmlspecialchars($client['status']); ?></span>
                                                        </td>
                                                        <td>
                                                            <a href="admin-client-view.php?id=<?php echo $client['reference_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="admin-client-edit.php?id=<?php echo $client['reference_id']; ?>" class="btn btn-sm btn-outline-secondary">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="table-card">
                                <h5 class="mb-4">Quick Actions</h5>
                                <div class="row">
                                    <div class="col-md-3 mb-3">
                                        <a href="client-registration" class="btn btn-primary w-100 py-3">
                                            <i class="fas fa-plus-circle fa-2x mb-2"></i><br>
                                            New Registration
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="admin-search.php" class="btn btn-info w-100 py-3">
                                            <i class="fas fa-search fa-2x mb-2"></i><br>
                                            Search Clients
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="admin-export.php" class="btn btn-success w-100 py-3">
                                            <i class="fas fa-download fa-2x mb-2"></i><br>
                                            Export Data
                                        </a>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <a href="admin-settings.php" class="btn btn-secondary w-100 py-3">
                                            <i class="fas fa-cog fa-2x mb-2"></i><br>
                                            Settings
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Currency Preferences -->
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="table-card">
                                <h5 class="mb-3"><i class="fas fa-money-bill-wave me-2 text-primary"></i>Currency Preference</h5>
                                <form method="POST" action="change-currency.php" id="adminCurrencyForm">
                                    <input type="hidden" name="user_type" value="admin">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label class="form-label">Preferred Currency for Display</label>
                                            <select class="form-select" name="currency" id="adminCurrencySelect" onchange="confirmAdminCurrencyChange()">
                                                <?php 
                                                $admin_currency = getUserPreferredCurrency($db, 'admin');
                                                $currencies = [
                                                    'USD' => '🇺🇸 US Dollar (USD)',
                                                    'GHS' => '🇬🇭 Ghanaian Cedi (GHS)',
                                                    'NGN' => '🇳🇬 Nigerian Naira (NGN)',
                                                    'GBP' => '🇬🇧 British Pound (GBP)',
                                                    'EUR' => '🇪🇺 Euro (EUR)',
                                                    'CAD' => '🇨🇦 Canadian Dollar (CAD)',
                                                    'AUD' => '🇦🇺 Australian Dollar (AUD)',
                                                    'ZAR' => '🇿🇦 South African Rand (ZAR)',
                                                    'KES' => '🇰🇪 Kenyan Shilling (KES)',
                                                    'UGX' => '🇺🇬 Ugandan Shilling (UGX)',
                                                    'TZS' => '🇹🇿 Tanzanian Shilling (TZS)',
                                                    'JPY' => '🇯🇵 Japanese Yen (JPY)',
                                                    'CHF' => '🇨🇭 Swiss Franc (CHF)',
                                                    'SEK' => '🇸🇪 Swedish Krona (SEK)',
                                                    'NOK' => '🇳🇴 Norwegian Krone (NOK)',
                                                    'DKK' => '🇩🇰 Danish Krone (DKK)',
                                                    'INR' => '🇮🇳 Indian Rupee (INR)',
                                                    'CNY' => '🇨🇳 Chinese Yuan (CNY)',
                                                    'BRL' => '🇧🇷 Brazilian Real (BRL)',
                                                    'MXN' => '🇲🇽 Mexican Peso (MXN)'
                                                ];
                                                foreach ($currencies as $code => $name): ?>
                                                    <option value="<?php echo $code; ?>" <?php echo $admin_currency === $code ? 'selected' : ''; ?>><?php echo $name; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4 d-flex align-items-end">
                                            <div class="text-center w-100">
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-info-circle me-1"></i>Current Format
                                                </small>
                                                <strong class="text-primary"><?php echo formatCurrencyByCode(1000, $admin_currency); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <div class="mt-3">
                                    <small class="text-muted">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        This affects how monetary amounts are displayed in the admin panel. Client amounts will still show in their preferred currency.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Currency Change Confirmation Modal -->
    <div class="modal fade" id="adminCurrencyConfirmModal" tabindex="-1" aria-labelledby="adminCurrencyConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="adminCurrencyConfirmModalLabel">
                        <i class="fas fa-cog text-primary me-2"></i>Change Admin Currency Display
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <div class="currency-icon mb-3">
                            <i class="fas fa-chart-line fa-3x text-primary"></i>
                        </div>
                        <h6 class="mb-2">Confirm Currency Display Change</h6>
                        <p class="text-muted mb-0">Change admin panel currency display to <strong id="selectedAdminCurrencyName"></strong>?</p>
                    </div>
                    <div class="alert alert-warning border-0" style="background-color: #fff3cd;">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            This affects how monetary amounts are displayed in the admin panel. Client amounts will still show in their preferred currency.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmAdminCurrencyChange">
                        <i class="fas fa-check me-1"></i>Update Display
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-refresh dashboard every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
        
        // Show welcome message on first login
        <?php if (isset($_SESSION['first_login'])): ?>
            alert('Welcome to M25 Travel & Tour Agency Admin Panel!\n\nFor security, please change your password in Settings.');
            <?php unset($_SESSION['first_login']); ?>
        <?php endif; ?>
        
        // Admin currency change confirmation
        let previousAdminCurrency = '<?php echo $admin_currency; ?>';
        
        function confirmAdminCurrencyChange() {
            const select = document.getElementById('adminCurrencySelect');
            const selectedOption = select.options[select.selectedIndex];
            const currencyName = selectedOption.text;
            
            // Update modal content
            document.getElementById('selectedAdminCurrencyName').textContent = currencyName;
            
            // Show custom modal
            const modal = new bootstrap.Modal(document.getElementById('adminCurrencyConfirmModal'));
            modal.show();
        }
        
        // Handle admin confirmation button click
        document.getElementById('confirmAdminCurrencyChange').addEventListener('click', function() {
            // Submit the form
            document.getElementById('adminCurrencyForm').submit();
        });
        
        // Handle admin modal close/cancel - reset to previous value
        document.getElementById('adminCurrencyConfirmModal').addEventListener('hidden.bs.modal', function () {
            const select = document.getElementById('adminCurrencySelect');
            if (!document.getElementById('adminCurrencyForm').submitted) {
                select.value = previousAdminCurrency;
            }
        });
        
        // Mark admin form as submitted to prevent reset
        document.getElementById('adminCurrencyForm').addEventListener('submit', function() {
            this.submitted = true;
        });
    </script>
</body>
</html>
