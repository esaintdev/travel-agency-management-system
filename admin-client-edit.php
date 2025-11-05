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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->beginTransaction();
        
        // Update client data
        $update_data = [
            'full_name' => sanitizeInput($_POST['full_name']),
            'gender' => sanitizeInput($_POST['gender']),
            'date_of_birth' => $_POST['date_of_birth'],
            'country' => sanitizeInput($_POST['country']),
            'mobile_number' => sanitizeInput($_POST['mobile_number']),
            'client_email' => sanitizeInput($_POST['client_email']),
            'visa_type' => sanitizeInput($_POST['visa_type']),
            'work_type' => sanitizeInput($_POST['work_type'] ?? ''),
            'address' => sanitizeInput($_POST['address']),
            'status' => sanitizeInput($_POST['status']),
            'result_outcome' => sanitizeInput($_POST['result_outcome'] ?? ''),
            // New address fields
            'house_number' => sanitizeInput($_POST['house_number'] ?? ''),
            'street_name' => sanitizeInput($_POST['street_name'] ?? ''),
            'location' => sanitizeInput($_POST['location'] ?? ''),
            'digital_address' => sanitizeInput($_POST['digital_address'] ?? ''),
            'postal_address' => sanitizeInput($_POST['postal_address'] ?? ''),
            // Educational fields
            'university' => sanitizeInput($_POST['university'] ?? ''),
            'graduation_year' => !empty($_POST['graduation_year']) ? intval($_POST['graduation_year']) : null,
            'bachelor_degree' => sanitizeInput($_POST['bachelor_degree'] ?? ''),
            'master_degree' => sanitizeInput($_POST['master_degree'] ?? ''),
            'other_qualifications' => sanitizeInput($_POST['other_qualifications'] ?? ''),
            // Financial fields
            'account_holder_name' => sanitizeInput($_POST['account_holder_name'] ?? ''),
            'average_monthly_balance' => !empty($_POST['average_monthly_balance']) ? floatval($_POST['average_monthly_balance']) : 0,
            'estimated_trip_budget' => !empty($_POST['estimated_trip_budget']) ? floatval($_POST['estimated_trip_budget']) : 0,
            'funding_source' => sanitizeInput($_POST['funding_source'] ?? ''),
            'financial_declaration' => sanitizeInput($_POST['financial_declaration'] ?? '')
        ];
        
        $sql = "UPDATE clients SET 
                full_name = ?, gender = ?, date_of_birth = ?, country = ?, mobile_number = ?, 
                client_email = ?, visa_type = ?, work_type = ?, address = ?, status = ?,
                result_outcome = ?, house_number = ?, street_name = ?, location = ?, 
                digital_address = ?, postal_address = ?, university = ?, graduation_year = ?,
                bachelor_degree = ?, master_degree = ?, other_qualifications = ?,
                account_holder_name = ?, average_monthly_balance = ?, estimated_trip_budget = ?,
                funding_source = ?, financial_declaration = ?, updated_at = CURRENT_TIMESTAMP
                WHERE reference_id = ?";
        
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $update_data['full_name'], $update_data['gender'], $update_data['date_of_birth'],
            $update_data['country'], $update_data['mobile_number'], $update_data['client_email'],
            $update_data['visa_type'], $update_data['work_type'], $update_data['address'],
            $update_data['status'], $update_data['result_outcome'], 
            $update_data['house_number'], $update_data['street_name'], $update_data['location'],
            $update_data['digital_address'], $update_data['postal_address'], 
            $update_data['university'], $update_data['graduation_year'],
            $update_data['bachelor_degree'], $update_data['master_degree'], $update_data['other_qualifications'],
            $update_data['account_holder_name'], $update_data['average_monthly_balance'], 
            $update_data['estimated_trip_budget'], $update_data['funding_source'], 
            $update_data['financial_declaration'], $client_id
        ]);
        
        $db->commit();
        
        // Log the activity
        logActivity($_SESSION['admin_id'] ?? 1, null, 'UPDATE_CLIENT', "Updated client: {$update_data['full_name']} ({$client_id})", $db);
        
        $_SESSION['success'] = "Client updated successfully.";
        header("Location: admin-client-view.php?id=" . urlencode($client_id));
        exit();
        
    } catch (Exception $e) {
        $db->rollback();
        error_log("Update client error: " . $e->getMessage());
        $_SESSION['error'] = "Error updating client: " . $e->getMessage();
    }
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
    error_log("Admin client edit error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading client details.";
    header('Location: admin-clients.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Client - <?php echo htmlspecialchars($client['full_name']); ?> - M25 Travel & Tour Agency</title>
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
        .section-header {
            background: #f8f9fa;
            padding: 15px 20px;
            margin: -30px -30px 20px -30px;
            border-radius: 10px 10px 0 0;
            border-bottom: 3px solid #13357B;
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
                        <span class="navbar-brand">Edit Client</span>
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
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="mb-4">
                        <a href="admin-client-view.php?id=<?php echo urlencode($client['reference_id']); ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to View
                        </a>
                        <a href="admin-clients.php" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>All Clients
                        </a>
                    </div>
                    
                    <form method="POST">
                        <!-- Basic Information -->
                        <div class="content-card">
                            <div class="section-header">
                                <h5 class="mb-0"><i class="fas fa-user me-2"></i>Basic Information</h5>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Reference ID</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($client['reference_id']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="Pending" <?php echo $client['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Processing" <?php echo $client['status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                        <option value="Approved" <?php echo $client['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                        <option value="Rejected" <?php echo $client['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        <option value="Completed" <?php echo $client['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" name="full_name" value="<?php echo htmlspecialchars($client['full_name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender *</label>
                                    <select class="form-select" name="gender" required>
                                        <option value="Male" <?php echo $client['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $client['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo $client['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth *</label>
                                    <input type="date" class="form-control" name="date_of_birth" value="<?php echo $client['date_of_birth']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Country *</label>
                                    <input type="text" class="form-control" name="country" value="<?php echo htmlspecialchars($client['country']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mobile Number *</label>
                                    <input type="tel" class="form-control" name="mobile_number" value="<?php echo htmlspecialchars($client['mobile_number']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="client_email" value="<?php echo htmlspecialchars($client['client_email']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Visa Type *</label>
                                    <select class="form-select" name="visa_type" required>
                                        <option value="Visit" <?php echo $client['visa_type'] === 'Visit' ? 'selected' : ''; ?>>Visit</option>
                                        <option value="Work" <?php echo $client['visa_type'] === 'Work' ? 'selected' : ''; ?>>Work</option>
                                        <option value="Study" <?php echo $client['visa_type'] === 'Study' ? 'selected' : ''; ?>>Study</option>
                                        <option value="Other" <?php echo $client['visa_type'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Work Type</label>
                                    <input type="text" class="form-control" name="work_type" value="<?php echo htmlspecialchars($client['work_type']); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address *</label>
                                    <textarea class="form-control" name="address" rows="2" required><?php echo htmlspecialchars($client['address']); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Financial Information -->
                        <div class="content-card">
                            <div class="section-header">
                                <h5 class="mb-0"><i class="fas fa-money-bill-wave me-2"></i>Financial Information</h5>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Deposit Paid (GHS)</label>
                                    <input type="number" class="form-control" name="deposit_paid" value="<?php echo $client['deposit_paid']; ?>" step="0.01" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Balance Due (GHS)</label>
                                    <input type="number" class="form-control" name="balance_due" value="<?php echo $client['balance_due']; ?>" step="0.01" min="0">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Result Outcome</label>
                                    <input type="text" class="form-control" name="result_outcome" value="<?php echo htmlspecialchars($client['result_outcome']); ?>">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Documents Status -->
                        <div class="content-card">
                            <div class="section-header">
                                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Documents Status</h5>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="documents_uploaded" id="documents_uploaded" <?php echo $client['documents_uploaded'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="documents_uploaded">
                                            Documents Uploaded
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="documents_verified" id="documents_verified" <?php echo $client['documents_verified'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="documents_verified">
                                            Documents Verified
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Documents Notes</label>
                                    <textarea class="form-control" name="documents_notes" rows="3"><?php echo htmlspecialchars($client['documents_notes']); ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Submit Buttons -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-save me-2"></i>Update Client
                            </button>
                            <a href="admin-client-view.php?id=<?php echo urlencode($client['reference_id']); ?>" class="btn btn-secondary btn-lg px-5 ms-3">
                                <i class="fas fa-times me-2"></i>Cancel
                            </a>
                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
