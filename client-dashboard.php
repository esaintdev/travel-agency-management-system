<?php
/**
 * Client Dashboard - Main dashboard for logged-in clients
 */

// Start session and include required files
session_start();
require_once 'config.php';
require_once 'includes/client-auth.php';

// Check if client is logged in
requireClientLogin();
checkClientSessionTimeout();

// Get current client data
$client = getCurrentClient($db);
if (!$client) {
    destroyClientSession();
    $_SESSION['error_message'] = "Unable to load your account. Please log in again.";
    header('Location: client-login.php');
    exit();
}

// Get client progress data
$progress_data = getClientProgress($db, $client['id']);
$progress_percentage = calculateProgressPercentage($client);
$status_text = getProgressStatusText($progress_percentage, $client['result_outcome']);
$status_color = getProgressColorClass($progress_percentage, $client['result_outcome']);

// Get recent documents
$recent_documents = [];
try {
    $stmt = $db->prepare("SELECT * FROM client_documents WHERE client_id = ? ORDER BY upload_date DESC LIMIT 5");
    $stmt->execute([$client['id']]);
    $recent_documents = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Dashboard documents error: " . $e->getMessage());
}

// Get client's preferred currency
$client_currency = getUserPreferredCurrency($db, 'client', $client['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Dashboard - M25 Travel & Tour Agency</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #13357B 0%, #FEA116 100%);
            color: white;
            padding: 40px 0;
        }
        
        .dashboard-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .card-header {
            background: transparent;
            border-bottom: 2px solid #f8f9fa;
            padding: 20px 25px;
        }
        
        .card-body {
            padding: 25px;
        }
        
        .progress-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
            background: conic-gradient(#FEA116 0deg, #FEA116 calc(var(--progress) * 3.6deg), #e9ecef calc(var(--progress) * 3.6deg));
        }
        
        .progress-circle::before {
            content: '';
            width: 90px;
            height: 90px;
            border-radius: 50%;
            background: white;
            position: absolute;
        }
        
        .progress-text {
            position: relative;
            z-index: 1;
            font-weight: bold;
            font-size: 18px;
            color: #13357B;
        }
        
        .stat-card {
            text-align: center;
            padding: 30px 20px;
        }
        
        .stat-card .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .document-preview {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s ease;
            height: 100%;
            background: #fff;
        }
        
        .document-preview:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-color: #13357B;
        }
        
        .document-icon {
            margin-bottom: 10px;
        }
        
        .document-info h6 {
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .document-status {
            margin-top: 10px;
        }
        
        .navbar-brand {
            font-weight: 600;
        }
        
        .navbar-nav .nav-link {
            font-weight: 500;
            padding: 10px 15px;
        }
        
        .btn-logout {
            background: #dc3545;
            border: none;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-logout:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .timeline {
            position: relative;
            padding: 20px 0;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 30px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #e9ecef;
        }
        
        .timeline-item {
            position: relative;
            padding-left: 70px;
            margin-bottom: 30px;
        }
        
        .timeline-marker {
            position: absolute;
            left: 20px;
            top: 0;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background: #FEA116;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #FEA116;
        }
        
        .timeline-content {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
            border-left: 4px solid #FEA116;
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
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
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
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top px-4 px-lg-5 shadow-sm">
        <a href="client-dashboard.php" class="navbar-brand d-flex align-items-center">
            <h4 class="mb-0 text-primary"><i class="fas fa-globe-americas me-2"></i>M25 Travel & Tours Agency</h4>
        </a>
        <button type="button" class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto py-0">
                <a href="client-dashboard.php" class="nav-item nav-link active">Dashboard</a>
                <a href="client-profile.php" class="nav-item nav-link">Profile</a>
                <a href="client-documents.php" class="nav-item nav-link">Documents</a>
                <a href="/" class="nav-item nav-link">Home</a>
                <a href="client-logout.php" class="btn btn-logout ms-3">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-6 mb-2">Welcome back, <?php echo htmlspecialchars($client['full_name']); ?>!</h1>
                    <p class="lead mb-0">Reference ID: <strong><?php echo htmlspecialchars($client['reference_id']); ?></strong></p>
                    <p class="mb-0">Visa Type: <strong><?php echo htmlspecialchars($client['visa_type']); ?></strong></p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-block bg-white bg-opacity-20 rounded-pill px-4 py-2">
                        <i class="fas fa-calendar me-2"></i>
                        Member since <?php echo formatClientDate($client['created_at'], 'M Y'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Dashboard Content -->
    <div class="container py-5">
        <!-- Application Progress -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="dashboard-card card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i>Application Progress</h3>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-lg-3 text-center">
                                <div class="progress-circle" style="--progress: <?php echo $progress_percentage; ?>">
                                    <div class="progress-text"><?php echo $progress_percentage; ?>%</div>
                                </div>
                                <h5 class="text-<?php echo $status_color; ?>"><?php echo $status_text; ?></h5>
                            </div>
                            <div class="col-lg-9">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Application Submitted</h6>
                                            <p class="mb-0 text-muted"><?php echo formatClientDate($client['submitted_date']); ?></p>
                                        </div>
                                    </div>
                                    
                                    <?php if ($client['deposit_paid'] > 0): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Payment Received</h6>
                                            <p class="mb-0 text-muted">Deposit: <?php echo formatCurrencyForUser($client['deposit_paid'], $db, 'client', $client['id']); ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($client['result_outcome'])): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">Status Update</h6>
                                            <p class="mb-0 text-muted"><?php echo htmlspecialchars($client['result_outcome']); ?></p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card card">
                    <div class="card-body stat-card">
                        <div class="stat-icon bg-primary text-white">
                            <i class="fas fa-passport"></i>
                        </div>
                        <h4><?php echo htmlspecialchars($client['visa_type']); ?></h4>
                        <p class="text-muted mb-0">Visa Type</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card card">
                    <div class="card-body stat-card">
                        <div class="stat-icon bg-success text-white">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h4><?php echo formatCurrencyForUser($client['deposit_paid'], $db, 'client', $client['id']); ?></h4>
                        <p class="text-muted mb-0">Deposit Paid</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card card">
                    <div class="card-body stat-card">
                        <div class="stat-icon bg-warning text-white">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <h4><?php echo formatCurrencyForUser($client['balance_due'], $db, 'client', $client['id']); ?></h4>
                        <p class="text-muted mb-0">Balance Due</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="dashboard-card card">
                    <div class="card-body stat-card">
                        <div class="stat-icon bg-info text-white">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h4><?php echo ucfirst($client['status']); ?></h4>
                        <p class="text-muted mb-0">Account Status</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Documents Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="dashboard-card card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0"><i class="fas fa-files me-2 text-primary"></i>Recent Documents</h3>
                        <a href="client-documents.php" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i>Upload New
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_documents)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No documents uploaded yet</h5>
                                <p class="text-muted mb-3">Upload your required documents to proceed with your application</p>
                                <a href="client-documents.php" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>Upload Documents
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach ($recent_documents as $doc): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="document-preview">
                                            <div class="document-icon">
                                                <?php
                                                $file_ext = strtolower(pathinfo($doc['original_filename'], PATHINFO_EXTENSION));
                                                $icon_class = in_array($file_ext, ['jpg', 'jpeg', 'png']) ? 'fa-image' : 
                                                             ($file_ext === 'pdf' ? 'fa-file-pdf' : 'fa-file-alt');
                                                $icon_color = in_array($file_ext, ['jpg', 'jpeg', 'png']) ? 'text-success' : 
                                                             ($file_ext === 'pdf' ? 'text-danger' : 'text-info');
                                                ?>
                                                <i class="fas <?php echo $icon_class; ?> fa-2x <?php echo $icon_color; ?>"></i>
                                            </div>
                                            <div class="document-info">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($doc['document_name']); ?></h6>
                                                <small class="text-muted d-block"><?php echo ucfirst(str_replace('_', ' ', $doc['document_type'])); ?></small>
                                                <small class="text-muted"><?php echo formatDate($doc['upload_date'], 'M d, Y'); ?></small>
                                            </div>
                                            <div class="document-status">
                                                <span class="badge bg-<?php echo $doc['status'] == 'approved' ? 'success' : ($doc['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                    <i class="fas fa-<?php echo $doc['status'] == 'approved' ? 'check' : ($doc['status'] == 'rejected' ? 'times' : 'clock'); ?> me-1"></i>
                                                    <?php echo ucfirst($doc['status']); ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="text-center mt-3">
                                <a href="client-documents.php" class="btn btn-outline-primary">
                                    <i class="fas fa-eye me-2"></i>View All Documents (<?php echo count($recent_documents); ?>)
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions & Information -->
        <div class="row">
            <div class="col-lg-8">
                <div class="dashboard-card card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-info-circle me-2 text-primary"></i>Application Details</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Personal Information</h6>
                                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($client['full_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($client['client_email']); ?></p>
                                <p><strong>Mobile:</strong> <?php echo htmlspecialchars($client['mobile_number']); ?></p>
                                <p><strong>Country:</strong> <?php echo htmlspecialchars($client['country']); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Application Information</h6>
                                <p><strong>Visa Type:</strong> <?php echo htmlspecialchars($client['visa_type']); ?></p>
                                <?php if (!empty($client['work_type'])): ?>
                                <p><strong>Work Type:</strong> <?php echo htmlspecialchars($client['work_type']); ?></p>
                                <?php endif; ?>
                                <?php if (!empty($client['passport_number'])): ?>
                                <p><strong>Passport:</strong> <?php echo htmlspecialchars($client['passport_number']); ?></p>
                                <?php endif; ?>
                                <p><strong>Submitted:</strong> <?php echo formatClientDate($client['submitted_date']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="dashboard-card card">
                    <div class="card-header">
                        <h3 class="mb-0"><i class="fas fa-tasks me-2 text-primary"></i>Quick Actions</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <a href="client-profile.php" class="btn btn-outline-primary">
                                <i class="fas fa-user me-2"></i>View Profile
                            </a>
                            <a href="client-documents.php" class="btn btn-outline-secondary">
                                <i class="fas fa-file-alt me-2"></i>Manage Documents
                            </a>
                            <a href="contact.php" class="btn btn-outline-info">
                                <i class="fas fa-phone me-2"></i>Contact Support
                            </a>
                            <?php if ($client['balance_due'] > 0): ?>
                            <a href="#" class="btn btn-warning">
                                <i class="fas fa-credit-card me-2"></i>Make Payment
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Currency Preferences -->
                <div class="dashboard-card card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2 text-primary"></i>Currency Preference</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="change-currency.php" id="currencyForm">
                            <input type="hidden" name="user_type" value="client">
                            <div class="mb-3">
                                <label class="form-label">Preferred Currency</label>
                                <select class="form-select" name="currency" id="currencySelect" onchange="confirmCurrencyChange()">
                                    <option value="USD" <?php echo $client_currency === 'USD' ? 'selected' : ''; ?>>🇺🇸 US Dollar (USD)</option>
                                    <option value="GHS" <?php echo $client_currency === 'GHS' ? 'selected' : ''; ?>>🇬🇭 Ghanaian Cedi (GHS)</option>
                                    <option value="NGN" <?php echo $client_currency === 'NGN' ? 'selected' : ''; ?>>🇳🇬 Nigerian Naira (NGN)</option>
                                    <option value="GBP" <?php echo $client_currency === 'GBP' ? 'selected' : ''; ?>>🇬🇧 British Pound (GBP)</option>
                                    <option value="EUR" <?php echo $client_currency === 'EUR' ? 'selected' : ''; ?>>🇪🇺 Euro (EUR)</option>
                                    <option value="CAD" <?php echo $client_currency === 'CAD' ? 'selected' : ''; ?>>🇨🇦 Canadian Dollar (CAD)</option>
                                    <option value="AUD" <?php echo $client_currency === 'AUD' ? 'selected' : ''; ?>>🇦🇺 Australian Dollar (AUD)</option>
                                    <option value="ZAR" <?php echo $client_currency === 'ZAR' ? 'selected' : ''; ?>>🇿🇦 South African Rand (ZAR)</option>
                                    <option value="KES" <?php echo $client_currency === 'KES' ? 'selected' : ''; ?>>🇰🇪 Kenyan Shilling (KES)</option>
                                    <option value="UGX" <?php echo $client_currency === 'UGX' ? 'selected' : ''; ?>>🇺🇬 Ugandan Shilling (UGX)</option>
                                    <option value="TZS" <?php echo $client_currency === 'TZS' ? 'selected' : ''; ?>>🇹🇿 Tanzanian Shilling (TZS)</option>
                                    <option value="JPY" <?php echo $client_currency === 'JPY' ? 'selected' : ''; ?>>🇯🇵 Japanese Yen (JPY)</option>
                                    <option value="CHF" <?php echo $client_currency === 'CHF' ? 'selected' : ''; ?>>🇨🇭 Swiss Franc (CHF)</option>
                                    <option value="SEK" <?php echo $client_currency === 'SEK' ? 'selected' : ''; ?>>🇸🇪 Swedish Krona (SEK)</option>
                                    <option value="NOK" <?php echo $client_currency === 'NOK' ? 'selected' : ''; ?>>🇳🇴 Norwegian Krone (NOK)</option>
                                    <option value="DKK" <?php echo $client_currency === 'DKK' ? 'selected' : ''; ?>>🇩🇰 Danish Krone (DKK)</option>
                                    <option value="INR" <?php echo $client_currency === 'INR' ? 'selected' : ''; ?>>🇮🇳 Indian Rupee (INR)</option>
                                    <option value="CNY" <?php echo $client_currency === 'CNY' ? 'selected' : ''; ?>>🇨🇳 Chinese Yuan (CNY)</option>
                                    <option value="BRL" <?php echo $client_currency === 'BRL' ? 'selected' : ''; ?>>🇧🇷 Brazilian Real (BRL)</option>
                                    <option value="MXN" <?php echo $client_currency === 'MXN' ? 'selected' : ''; ?>>🇲🇽 Mexican Peso (MXN)</option>
                                </select>
                            </div>
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Current: <?php echo formatCurrencyByCode(1000, $client_currency); ?>
                                </small>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Support Information -->
                <div class="dashboard-card card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-headset me-2 text-primary"></i>Need Help?</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-3">Our support team is here to help you.</p>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-phone text-primary me-2"></i>
                            <span>+233 59 260 5752</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-envelope text-primary me-2"></i>
                            <span>info@m25travelagency.com</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Currency Change Confirmation Modal -->
    <div class="modal fade" id="currencyConfirmModal" tabindex="-1" aria-labelledby="currencyConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title" id="currencyConfirmModalLabel">
                        <i class="fas fa-money-bill-wave text-primary me-2"></i>Change Currency Preference
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <div class="currency-icon mb-3">
                            <i class="fas fa-exchange-alt fa-3x text-primary"></i>
                        </div>
                        <h6 class="mb-2">Confirm Currency Change</h6>
                        <p class="text-muted mb-0">Change your preferred currency to <strong id="selectedCurrencyName"></strong>?</p>
                    </div>
                    <div class="alert alert-info border-0" style="background-color: #e3f2fd;">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            This will update how amounts are displayed throughout your account.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmCurrencyChange">
                        <i class="fas fa-check me-1"></i>Change Currency
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="js/main.js"></script>
    
    <script>
        // Initialize animations
        new WOW().init();
        
        // Auto-refresh page every 5 minutes to check for updates
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 minutes
        
        // Currency change confirmation
        let previousCurrencyIndex = <?php echo array_search($client_currency, ['USD', 'GHS', 'NGN', 'GBP', 'EUR', 'CAD', 'AUD', 'ZAR', 'KES', 'UGX', 'TZS', 'JPY', 'CHF', 'SEK', 'NOK', 'DKK', 'INR', 'CNY', 'BRL', 'MXN']); ?>;
        
        function confirmCurrencyChange() {
            const select = document.getElementById('currencySelect');
            const selectedOption = select.options[select.selectedIndex];
            const currencyName = selectedOption.text;
            
            // Update modal content
            document.getElementById('selectedCurrencyName').textContent = currencyName;
            
            // Show custom modal
            const modal = new bootstrap.Modal(document.getElementById('currencyConfirmModal'));
            modal.show();
        }
        
        // Handle confirmation button click
        document.getElementById('confirmCurrencyChange').addEventListener('click', function() {
            // Submit the form
            document.getElementById('currencyForm').submit();
        });
        
        // Handle modal close/cancel - reset to previous value
        document.getElementById('currencyConfirmModal').addEventListener('hidden.bs.modal', function () {
            const select = document.getElementById('currencySelect');
            if (!document.getElementById('currencyForm').submitted) {
                select.selectedIndex = previousCurrencyIndex;
            }
        });
        
        // Mark form as submitted to prevent reset
        document.getElementById('currencyForm').addEventListener('submit', function() {
            this.submitted = true;
        });
    </script>
</body>
</html>
