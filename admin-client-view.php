<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: admin-login.html?timeout=1');
    exit();
}

// Get client ID from URL
$client_id = $_GET['id'] ?? '';

if (empty($client_id)) {
    header('Location: admin-clients.php');
    exit();
}

try {
    // Get client details
    $stmt = $db->prepare("SELECT * FROM clients WHERE reference_id = ?");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
    
    if (!$client) {
        $_SESSION['error'] = "Client not found.";
        header('Location: admin-clients.php');
        exit();
    }
    
} catch (Exception $e) {
    error_log("Admin client view error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading client details.";
    header('Location: admin-clients.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>View Client - <?php echo htmlspecialchars($client['full_name']); ?> - M25 Travel & Tour Agency</title>
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
        .info-row {
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #13357B;
        }
        .section-header {
            background: #f8f9fa;
            padding: 15px 20px;
            margin: -30px -30px 20px -30px;
            border-radius: 10px 10px 0 0;
            border-bottom: 3px solid #13357B;
        }
        .status-badge {
            font-size: 14px;
            padding: 8px 16px;
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
                    <a class="nav-link active" href="admin-clients.php">
                        <i class="fas fa-users me-2"></i>All Clients
                    </a>
                    <a class="nav-link" href="admin-search.php">
                        <i class="fas fa-search me-2"></i>Search Clients
                    </a>
                    <a class="nav-link" href="admin-documents.php">
                        <i class="fas fa-files me-2"></i>All Documents
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
                        <span class="navbar-brand">Client Details</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="container-fluid p-4">
                    
                    <!-- Action Buttons -->
                    <div class="mb-4">
                        <a href="admin-clients.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Clients
                        </a>
                        <a href="admin-client-documents.php?ref=<?php echo urlencode($client['reference_id']); ?>" class="btn btn-info">
                            <i class="fas fa-files me-2"></i>View Documents
                        </a>
                        <a href="admin-client-edit.php?id=<?php echo urlencode($client['reference_id']); ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Client
                        </a>
                        <button class="btn btn-danger" onclick="confirmDelete('<?php echo htmlspecialchars($client['reference_id']); ?>', '<?php echo htmlspecialchars($client['full_name']); ?>')">
                            <i class="fas fa-trash me-2"></i>Delete Client
                        </button>
                    </div>
                    
                    <!-- Client Information -->
                    <div class="content-card">
                        <div class="section-header">
                            <h4 class="mb-0">
                                <i class="fas fa-user me-2"></i>Client Information
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
                                <span class="badge <?php echo $status_class; ?> status-badge float-end"><?php echo htmlspecialchars($client['status']); ?></span>
                            </h4>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Reference ID</div>
                                    <div><strong class="text-primary"><?php echo htmlspecialchars($client['reference_id']); ?></strong></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Full Name</div>
                                    <div><?php echo htmlspecialchars($client['full_name']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Gender</div>
                                    <div><?php echo htmlspecialchars($client['gender']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Date of Birth</div>
                                    <div><?php echo formatDate($client['date_of_birth'], 'M d, Y'); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Country</div>
                                    <div><?php echo htmlspecialchars($client['country']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Email</div>
                                    <div><a href="mailto:<?php echo htmlspecialchars($client['client_email']); ?>"><?php echo htmlspecialchars($client['client_email']); ?></a></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Mobile Number</div>
                                    <div><a href="tel:<?php echo htmlspecialchars($client['mobile_number']); ?>"><?php echo htmlspecialchars($client['mobile_number']); ?></a></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Visa Type</div>
                                    <div><span class="badge bg-info"><?php echo htmlspecialchars($client['visa_type']); ?></span></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Submitted Date</div>
                                    <div><?php echo formatDate($client['submitted_date'], 'M d, Y H:i'); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Last Updated</div>
                                    <div><?php echo formatDate($client['updated_at'], 'M d, Y H:i'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Personal Details -->
                    <div class="content-card">
                        <div class="section-header">
                            <h5 class="mb-0"><i class="fas fa-address-card me-2"></i>Personal & Document Details</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Physical Address</div>
                                    <div><?php echo nl2br(htmlspecialchars($client['address'])); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">House Number</div>
                                    <div><?php echo htmlspecialchars($client['house_number']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Street Name</div>
                                    <div><?php echo htmlspecialchars($client['street_name']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Location</div>
                                    <div><?php echo htmlspecialchars($client['location']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Passport Number</div>
                                    <div><?php echo htmlspecialchars($client['passport_number']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Digital Address</div>
                                    <div><?php echo htmlspecialchars($client['digital_address']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Postal Address</div>
                                    <div><?php echo htmlspecialchars($client['postal_address']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Educational Information -->
                    <div class="content-card">
                        <div class="section-header">
                            <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Educational Information</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">University/Institution</div>
                                    <div><?php echo htmlspecialchars($client['university']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Graduation Year</div>
                                    <div><?php echo $client['graduation_year'] ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Bachelor's Degree</div>
                                    <div><?php echo htmlspecialchars($client['bachelor_degree']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Master's Degree</div>
                                    <div><?php echo htmlspecialchars($client['master_degree']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Other Qualifications</div>
                                    <div><?php echo nl2br(htmlspecialchars($client['other_qualifications'])) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Employment Details -->
                    <div class="content-card">
                        <div class="section-header">
                            <h5 class="mb-0"><i class="fas fa-briefcase me-2"></i>Employment Details</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Work Type</div>
                                    <div><?php echo htmlspecialchars($client['work_type']) ?: '<em class="text-muted">Not specified</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Contract Start Date</div>
                                    <div><?php echo $client['date_contract_started'] ? formatDate($client['date_contract_started'], 'M d, Y') : '<em class="text-muted">Not specified</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Application Hold</div>
                                    <div><span class="badge <?php echo $client['visa_application_hold'] === 'Yes' ? 'bg-warning text-dark' : 'bg-success'; ?>"><?php echo htmlspecialchars($client['visa_application_hold']); ?></span></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Denial Appeal</div>
                                    <div><span class="badge <?php echo $client['visa_denial_appeal'] === 'Yes' ? 'bg-warning text-dark' : 'bg-success'; ?>"><?php echo htmlspecialchars($client['visa_denial_appeal']); ?></span></div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($client['immigration_history'])): ?>
                        <div class="info-row">
                            <div class="info-label">Immigration History</div>
                            <div><?php echo nl2br(htmlspecialchars($client['immigration_history'])); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Financial Information -->
                    <div class="content-card">
                        <div class="section-header">
                            <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Financial Information</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Bank Name</div>
                                    <div><?php echo htmlspecialchars($client['bank_name']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Account Name</div>
                                    <div><?php echo htmlspecialchars($client['account_name']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Account Holder Name</div>
                                    <div><?php echo htmlspecialchars($client['account_holder_name']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Average Monthly Balance</div>
                                    <div><?php echo $client['average_monthly_balance'] ? formatCurrencyForUser($client['average_monthly_balance'], $db, 'admin') : '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Bank Branch</div>
                                    <div><?php echo htmlspecialchars($client['bank_branch']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Account Number</div>
                                    <div><?php echo htmlspecialchars($client['account_number']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Estimated Trip Budget</div>
                                    <div><?php echo $client['estimated_trip_budget'] ? formatCurrencyForUser($client['estimated_trip_budget'], $db, 'admin') : '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Funding Source</div>
                                    <div><?php echo htmlspecialchars($client['funding_source']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($client['financial_declaration'])): ?>
                        <div class="info-row">
                            <div class="info-label">Financial Declaration</div>
                            <div><?php echo nl2br(htmlspecialchars($client['financial_declaration'])); ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Family Information -->
                    <div class="content-card">
                        <div class="section-header">
                            <h5 class="mb-0"><i class="fas fa-users me-2"></i>Family Information</h5>
                        </div>
                        
                        <!-- Spouse -->
                        <?php if (!empty($client['spouse_name'])): ?>
                        <h6 class="text-primary mb-3">Spouse</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Name</div>
                                    <div><?php echo htmlspecialchars($client['spouse_name']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Date of Birth</div>
                                    <div><?php echo $client['spouse_dob'] ? formatDate($client['spouse_dob'], 'M d, Y') : '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Status</div>
                                    <div><?php echo htmlspecialchars($client['spouse_status']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Address</div>
                                    <div><?php echo htmlspecialchars($client['spouse_address']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Parents -->
                        <div class="row">
                            <?php if (!empty($client['father_name'])): ?>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Father</h6>
                                <div class="info-row">
                                    <div class="info-label">Name</div>
                                    <div><?php echo htmlspecialchars($client['father_name']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Date of Birth</div>
                                    <div><?php echo $client['father_dob'] ? formatDate($client['father_dob'], 'M d, Y') : '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Status</div>
                                    <div><?php echo htmlspecialchars($client['father_status']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($client['mother_name'])): ?>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Mother</h6>
                                <div class="info-row">
                                    <div class="info-label">Name</div>
                                    <div><?php echo htmlspecialchars($client['mother_name']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Date of Birth</div>
                                    <div><?php echo $client['mother_dob'] ? formatDate($client['mother_dob'], 'M d, Y') : '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Status</div>
                                    <div><?php echo htmlspecialchars($client['mother_status']) ?: '<em class="text-muted">Not provided</em>'; ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Children -->
                        <?php 
                        $children = [];
                        for ($i = 1; $i <= 3; $i++) {
                            if (!empty($client["child{$i}_name"])) {
                                $children[] = [
                                    'name' => $client["child{$i}_name"],
                                    'dob' => $client["child{$i}_dob"],
                                    'address' => $client["child{$i}_address"],
                                    'status' => $client["child{$i}_status"]
                                ];
                            }
                        }
                        ?>
                        
                        <?php if (!empty($children)): ?>
                        <h6 class="text-primary mb-3 mt-4">Children</h6>
                        <div class="row">
                            <?php foreach ($children as $index => $child): ?>
                            <div class="col-md-4 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <h6 class="card-title">Child <?php echo $index + 1; ?></h6>
                                        <p class="card-text">
                                            <strong><?php echo htmlspecialchars($child['name']); ?></strong><br>
                                            <small class="text-muted">
                                                <?php echo $child['dob'] ? formatDate($child['dob'], 'M d, Y') : 'DOB not provided'; ?><br>
                                                <?php echo htmlspecialchars($child['status']) ?: 'Status not provided'; ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (empty($client['spouse_name']) && empty($client['father_name']) && empty($client['mother_name']) && empty($children)): ?>
                        <p class="text-muted text-center py-4">
                            <i class="fas fa-info-circle me-2"></i>No family information provided
                        </p>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Documents Status -->
                    <div class="content-card">
                        <div class="section-header">
                            <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Documents Status</h5>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="info-row">
                                    <div class="info-label">Documents Uploaded</div>
                                    <div>
                                        <span class="badge <?php echo $client['documents_uploaded'] ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                            <?php echo $client['documents_uploaded'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-row">
                                    <div class="info-label">Documents Verified</div>
                                    <div>
                                        <span class="badge <?php echo $client['documents_verified'] ? 'bg-success' : 'bg-warning text-dark'; ?>">
                                            <?php echo $client['documents_verified'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="info-row">
                                    <div class="info-label">Result Outcome</div>
                                    <div><?php echo htmlspecialchars($client['result_outcome']) ?: '<em class="text-muted">Pending</em>'; ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($client['documents_notes'])): ?>
                        <div class="info-row">
                            <div class="info-label">Documents Notes</div>
                            <div><?php echo nl2br(htmlspecialchars($client['documents_notes'])); ?></div>
                        </div>
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
</body>
</html>
