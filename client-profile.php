<?php
/**
 * Client Profile Page - View and edit profile information
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>My Profile - M25 Travel & Tour Agency</title>
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
        .profile-header {
            background: linear-gradient(135deg, #13357B 0%, #FEA116 100%);
            color: white;
            padding: 40px 0;
        }
        
        .profile-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #13357B 0%, #FEA116 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto 20px;
        }
        
        .info-row {
            padding: 15px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #13357B;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #6c757d;
        }
        
        .section-title {
            color: #13357B;
            border-bottom: 3px solid #FEA116;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #13357B 0%, #FEA116 100%);
            border: none;
            color: white;
            padding: 10px 30px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            color: white;
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
    </style>
</head>

<body>
    <?php include 'includes/client-sidebar.php'; ?>
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-6 mb-2">My Profile</h1>
                    <p class="lead mb-0">Manage your personal information and application details</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <button class="btn btn-edit" onclick="toggleEditMode()">
                        <i class="fas fa-edit me-2"></i>Edit Profile
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="container py-5">
        <!-- Profile Overview -->
        <div class="row mb-5">
            <div class="col-lg-4">
                <div class="profile-card card">
                    <div class="card-body text-center">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($client['full_name'], 0, 1)); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($client['full_name']); ?></h3>
                        <p class="text-muted mb-2"><?php echo htmlspecialchars($client['client_email']); ?></p>
                        <div class="badge bg-primary fs-6 mb-3"><?php echo htmlspecialchars($client['reference_id']); ?></div>
                        <p class="text-muted">Member since <?php echo formatClientDate($client['created_at'], 'F Y'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-8">
                <div class="profile-card card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-user me-2 text-primary"></i>Personal Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Full Name</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['full_name']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Gender</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['gender']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Date of Birth</div>
                                    <div class="info-value"><?php echo formatClientDate($client['date_of_birth']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Country</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['country']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Email Address</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['client_email']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Mobile Number</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['mobile_number']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Ghana Card Number</div>
                                    <div class="info-value"><?php echo !empty($client['ghana_card_number']) ? htmlspecialchars($client['ghana_card_number']) : 'Not provided'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Passport Number</div>
                                    <div class="info-value"><?php echo !empty($client['passport_number']) ? htmlspecialchars($client['passport_number']) : 'Not provided'; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Application Details -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="profile-card card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-passport me-2 text-primary"></i>Application Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Visa Type</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['visa_type']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Work Type</div>
                                    <div class="info-value"><?php echo !empty($client['work_type']) ? htmlspecialchars($client['work_type']) : 'Not specified'; ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Application Status</div>
                                    <div class="info-value">
                                        <span class="badge bg-<?php echo getProgressColorClass(0, $client['result_outcome']); ?>">
                                            <?php echo !empty($client['result_outcome']) ? htmlspecialchars($client['result_outcome']) : 'Submitted'; ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Submitted Date</div>
                                    <div class="info-value"><?php echo formatClientDate($client['submitted_date']); ?></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-row">
                                    <div class="info-label">Deposit Paid</div>
                                    <div class="info-value text-success"><?php echo formatClientCurrency($client['deposit_paid']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Balance Due</div>
                                    <div class="info-value <?php echo $client['balance_due'] > 0 ? 'text-warning' : 'text-success'; ?>">
                                        <?php echo formatClientCurrency($client['balance_due']); ?>
                                    </div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Contract Start Date</div>
                                    <div class="info-value"><?php echo formatClientDate($client['date_contract_started']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Last Updated</div>
                                    <div class="info-value"><?php echo formatClientDate($client['updated_at'], 'M d, Y g:i A'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Address Information -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="profile-card card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Address Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="info-label">Current Address</div>
                            <div class="info-value"><?php echo nl2br(htmlspecialchars($client['address'])); ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Family Information -->
        <?php if (!empty($client['spouse_name']) || !empty($client['father_name']) || !empty($client['mother_name'])): ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="profile-card card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-users me-2 text-primary"></i>Family Information</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if (!empty($client['spouse_name'])): ?>
                            <div class="col-md-4">
                                <h6 class="section-title">Spouse Details</h6>
                                <div class="info-row">
                                    <div class="info-label">Name</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['spouse_name']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Date of Birth</div>
                                    <div class="info-value"><?php echo formatClientDate($client['spouse_dob']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Status</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['spouse_status']); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($client['father_name'])): ?>
                            <div class="col-md-4">
                                <h6 class="section-title">Father Details</h6>
                                <div class="info-row">
                                    <div class="info-label">Name</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['father_name']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Date of Birth</div>
                                    <div class="info-value"><?php echo formatClientDate($client['father_dob']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Status</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['father_status']); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($client['mother_name'])): ?>
                            <div class="col-md-4">
                                <h6 class="section-title">Mother Details</h6>
                                <div class="info-row">
                                    <div class="info-label">Name</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['mother_name']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Date of Birth</div>
                                    <div class="info-value"><?php echo formatClientDate($client['mother_dob']); ?></div>
                                </div>
                                <div class="info-row">
                                    <div class="info-label">Status</div>
                                    <div class="info-value"><?php echo htmlspecialchars($client['mother_status']); ?></div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="row">
            <div class="col-12">
                <div class="profile-card card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-cogs me-2 text-primary"></i>Account Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <a href="client-dashboard.php" class="btn btn-outline-primary w-100 mb-3">
                                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="client-documents.php" class="btn btn-outline-secondary w-100 mb-3">
                                    <i class="fas fa-file-alt me-2"></i>Documents
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="contact.php" class="btn btn-outline-info w-100 mb-3">
                                    <i class="fas fa-phone me-2"></i>Contact Support
                                </a>
                            </div>
                            <div class="col-md-3">
                                <a href="#" class="btn btn-outline-warning w-100 mb-3" onclick="changePassword()">
                                    <i class="fas fa-key me-2"></i>Change Password
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
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="js/main.js"></script>
    
    <script>
        // Initialize animations
        new WOW().init();
        
        function toggleEditMode() {
            alert('Profile editing feature coming soon! Please contact support to update your information.');
        }
        
        function changePassword() {
            alert('Password change feature coming soon! Please contact support to change your password.');
        }
    </script>
    <?php include 'includes/client-sidebar-close.php'; ?>
</body>
</html>
