<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Handle export request
if (isset($_POST['export_type'])) {
    $export_type = $_POST['export_type'];
    $date_from = $_POST['date_from'] ?? '';
    $date_to = $_POST['date_to'] ?? '';
    $visa_type = $_POST['visa_type'] ?? '';
    $status = $_POST['status'] ?? '';
    
    try {
        // Build query with filters
        $sql = "SELECT * FROM clients WHERE 1=1";
        $params = [];
        
        if ($date_from) {
            $sql .= " AND submitted_date >= ?";
            $params[] = $date_from;
        }
        
        if ($date_to) {
            $sql .= " AND submitted_date <= ?";
            $params[] = $date_to . ' 23:59:59';
        }
        
        if ($visa_type) {
            $sql .= " AND visa_type = ?";
            $params[] = $visa_type;
        }
        
        if ($status) {
            $sql .= " AND status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY submitted_date DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $clients = $stmt->fetchAll();
        
        if ($export_type === 'csv') {
            exportToCSV($clients);
        } elseif ($export_type === 'excel') {
            exportToExcel($clients);
        }
        
    } catch (Exception $e) {
        error_log("Export error: " . $e->getMessage());
        $_SESSION['error'] = "Export failed. Please try again.";
    }
}

function exportToCSV($clients) {
    $filename = 'M25_Clients_Export_' . date('Y-m-d_H-i-s') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    $output = fopen('php://output', 'w');
    
    // CSV Headers
    $headers = [
        'Reference ID', 'Full Name', 'Gender', 'Date of Birth', 'Country', 'Mobile Number',
        'Client Email', 'Visa Type', 'Work Type', 'Submitted Date', 'Status',
        'Address', 'Bank Name', 'Account Name', 'Account Number',
        'Spouse Name', 'Father Name', 'Mother Name', 'Deposit Paid', 'Balance Due'
    ];
    
    fputcsv($output, $headers);
    
    // Data rows
    foreach ($clients as $client) {
        $row = [
            $client['reference_id'],
            $client['full_name'],
            $client['gender'],
            $client['date_of_birth'],
            $client['country'],
            $client['mobile_number'],
            $client['client_email'],
            $client['visa_type'],
            $client['work_type'],
            $client['submitted_date'],
            $client['status'],
            $client['address'],
            $client['bank_name'],
            $client['account_name'],
            $client['account_number'],
            $client['spouse_name'],
            $client['father_name'],
            $client['mother_name'],
            $client['deposit_paid'],
            $client['balance_due']
        ];
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit();
}

function exportToExcel($clients) {
    $filename = 'M25_Clients_Export_' . date('Y-m-d_H-i-s') . '.xls';
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    
    echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><title>M25 Clients Export</title></head>';
    echo '<body>';
    echo '<table border="1">';
    
    // Headers
    echo '<tr style="background-color: #13357B; color: white; font-weight: bold;">';
    echo '<td>Reference ID</td><td>Full Name</td><td>Gender</td><td>Date of Birth</td><td>Country</td>';
    echo '<td>Mobile Number</td><td>Client Email</td><td>Visa Type</td><td>Work Type</td><td>Submitted Date</td>';
    echo '<td>Status</td><td>Address</td><td>Bank Name</td><td>Account Name</td><td>Account Number</td>';
    echo '<td>Spouse Name</td><td>Father Name</td><td>Mother Name</td><td>Deposit Paid</td><td>Balance Due</td>';
    echo '</tr>';
    
    // Data rows
    foreach ($clients as $client) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($client['reference_id']) . '</td>';
        echo '<td>' . htmlspecialchars($client['full_name']) . '</td>';
        echo '<td>' . htmlspecialchars($client['gender']) . '</td>';
        echo '<td>' . htmlspecialchars($client['date_of_birth']) . '</td>';
        echo '<td>' . htmlspecialchars($client['country']) . '</td>';
        echo '<td>' . htmlspecialchars($client['mobile_number']) . '</td>';
        echo '<td>' . htmlspecialchars($client['client_email']) . '</td>';
        echo '<td>' . htmlspecialchars($client['visa_type']) . '</td>';
        echo '<td>' . htmlspecialchars($client['work_type']) . '</td>';
        echo '<td>' . htmlspecialchars($client['submitted_date']) . '</td>';
        echo '<td>' . htmlspecialchars($client['status']) . '</td>';
        echo '<td>' . htmlspecialchars($client['address']) . '</td>';
        echo '<td>' . htmlspecialchars($client['bank_name']) . '</td>';
        echo '<td>' . htmlspecialchars($client['account_name']) . '</td>';
        echo '<td>' . htmlspecialchars($client['account_number']) . '</td>';
        echo '<td>' . htmlspecialchars($client['spouse_name']) . '</td>';
        echo '<td>' . htmlspecialchars($client['father_name']) . '</td>';
        echo '<td>' . htmlspecialchars($client['mother_name']) . '</td>';
        echo '<td>' . number_format($client['deposit_paid'], 2) . '</td>';
        echo '<td>' . number_format($client['balance_due'], 2) . '</td>';
        echo '</tr>';
    }
    
    echo '</table>';
    echo '</body></html>';
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Export Data - M25 Travel & Tour Agency</title>
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
        .export-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .top-navbar {
            background: #13357B !important;
        }
        .btn-export {
            padding: 15px 30px;
            font-size: 16px;
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
                    <a class="nav-link" href="admin-dashboard.php">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                    <a class="nav-link" href="admin-clients.php">
                        <i class="fas fa-users me-2"></i>All Clients
                    </a>
                    <a class="nav-link" href="admin-search.php">
                        <i class="fas fa-search me-2"></i>Search Clients
                    </a>
                    <a class="nav-link" href="admin-visa-content.php">
                        <i class="fas fa-passport me-2"></i>Visa Content
                    </a>
                    <a class="nav-link active" href="admin-export.php">
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
                        <span class="navbar-brand">Export Data</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Export Content -->
                <div class="container-fluid p-4">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <div class="export-card">
                        <h4 class="mb-4"><i class="fas fa-download me-2"></i>Export Client Data</h4>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date From</label>
                                    <input type="date" class="form-control" name="date_from">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Date To</label>
                                    <input type="date" class="form-control" name="date_to">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Visa Type</label>
                                    <select class="form-select" name="visa_type">
                                        <option value="">All Types</option>
                                        <option value="Visit">Visit</option>
                                        <option value="Work">Work</option>
                                        <option value="Study">Study</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status">
                                        <option value="">All Statuses</option>
                                        <option value="Active">Active</option>
                                        <option value="Completed">Completed</option>
                                        <option value="Inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <button type="submit" name="export_type" value="csv" class="btn btn-success btn-export w-100">
                                        <i class="fas fa-file-csv me-2"></i>Export as CSV
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" name="export_type" value="excel" class="btn btn-primary btn-export w-100">
                                        <i class="fas fa-file-excel me-2"></i>Export as Excel
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Export Information -->
                    <div class="export-card">
                        <h5 class="mb-3">Export Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>CSV Format</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Compatible with Excel, Google Sheets</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Smaller file size</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Easy to import into databases</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Excel Format</h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-check text-success me-2"></i>Native Excel format</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Preserves formatting</li>
                                    <li><i class="fas fa-check text-success me-2"></i>Ready for analysis</li>
                                </ul>
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
</body>
</html>
