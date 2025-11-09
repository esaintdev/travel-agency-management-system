<?php
/**
 * Client Application Status Page
 */

// Start session and include required files
session_start();
require_once 'config.php';
require_once 'includes/client-auth.php';

// Check if client is logged in
requireClientLogin();
checkClientSessionTimeout();

// Get current client data
$client_data = getCurrentClient($db);
if (!$client_data) {
    destroyClientSession();
    $_SESSION['error_message'] = "Unable to load your account. Please log in again.";
    header('Location: client-login.php');
    exit();
}

$client_id = $client_data['id'];

// Get client's preferred currency
$client_currency = getUserPreferredCurrency($db, 'client', $client_id);

// Get client progress data
$progress_data = getClientProgress($db, $client_data['id']);
$progress_percentage = calculateProgressPercentage($client_data);
$status_text = getProgressStatusText($progress_percentage, $client_data['result_outcome']);
$status_color = getProgressColorClass($progress_percentage, $client_data['result_outcome']);

// Get application timeline events
$timeline_events = [];
try {
    // Add application submission
    if ($client_data['submitted_date']) {
        $timeline_events[] = [
            'date' => $client_data['submitted_date'],
            'title' => 'Application Submitted',
            'description' => 'Your visa application has been successfully submitted and is under review.',
            'status' => 'completed',
            'icon' => 'fa-paper-plane'
        ];
    }
    
    // Add payment if made
    if ($client_data['deposit_paid'] > 0) {
        $timeline_events[] = [
            'date' => $client_data['payment_date'] ?: $client_data['submitted_date'],
            'title' => 'Payment Received',
            'description' => 'Deposit payment of ' . formatCurrencyForUser($client_data['deposit_paid'], $db, 'client', $client_id) . ' has been received.',
            'status' => 'completed',
            'icon' => 'fa-credit-card'
        ];
    }
    
    // Add document verification if applicable
    if ($client_data['documents_uploaded']) {
        $timeline_events[] = [
            'date' => $client_data['updated_at'],
            'title' => 'Documents Under Review',
            'description' => 'Your uploaded documents are being reviewed by our team.',
            'status' => $client_data['documents_verified'] ? 'completed' : 'pending',
            'icon' => 'fa-file-check'
        ];
    }
    
    // Add current status
    $current_status_desc = '';
    switch ($client_data['status']) {
        case 'Pending':
            $current_status_desc = 'Your application is pending initial review.';
            break;
        case 'Processing':
            $current_status_desc = 'Your application is currently being processed by the relevant authorities.';
            break;
        case 'Approved':
            $current_status_desc = 'Congratulations! Your visa application has been approved.';
            break;
        case 'Rejected':
            $current_status_desc = 'Unfortunately, your visa application has been rejected. Please contact us for more information.';
            break;
        case 'Completed':
            $current_status_desc = 'Your visa application process has been completed successfully.';
            break;
        default:
            $current_status_desc = 'Your application status is being updated.';
    }
    
    $timeline_events[] = [
        'date' => $client_data['updated_at'],
        'title' => 'Current Status: ' . $client_data['status'],
        'description' => $current_status_desc,
        'status' => in_array($client_data['status'], ['Approved', 'Completed']) ? 'completed' : 'current',
        'icon' => 'fa-flag-checkered'
    ];
    
    // Sort timeline by date
    usort($timeline_events, function($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    
} catch (Exception $e) {
    error_log("Timeline error: " . $e->getMessage());
}

// Get recent documents
$recent_documents = [];
try {
    $stmt = $db->prepare("SELECT * FROM client_documents WHERE client_id = ? ORDER BY upload_date DESC LIMIT 3");
    $stmt->execute([$client_id]);
    $recent_documents = $stmt->fetchAll();
} catch (Exception $e) {
    error_log("Documents error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Application Status - M25 Travel & Tours Agency</title>
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
        .status-container {
            background: linear-gradient(135deg, #13357B 0%, #1e4a8c 100%);
            min-height: 100vh;
        }
        
        .status-header {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            padding: 40px 0;
            color: white;
        }
        
        .status-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border: none;
        }
        
        .progress-circle {
            width: 150px;
            height: 150px;
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
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: white;
            position: absolute;
        }
        
        .progress-text {
            position: relative;
            z-index: 2;
            font-weight: bold;
            font-size: 24px;
            color: #13357B;
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
            width: 3px;
            background: linear-gradient(to bottom, #13357B, #FEA116);
        }
        
        .timeline-item {
            position: relative;
            padding-left: 80px;
            margin-bottom: 40px;
        }
        
        .timeline-marker {
            position: absolute;
            left: 18px;
            top: 0;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 0 3px #13357B;
        }
        
        .timeline-marker.completed {
            background: #28a745;
            box-shadow: 0 0 0 3px #28a745;
        }
        
        .timeline-marker.pending {
            background: #ffc107;
            box-shadow: 0 0 0 3px #ffc107;
        }
        
        .timeline-marker.current {
            background: #FEA116;
            box-shadow: 0 0 0 3px #FEA116;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .timeline-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #13357B;
        }
        
        .timeline-icon {
            position: absolute;
            left: 12px;
            top: 2px;
            color: white;
            font-size: 12px;
        }
        
        .status-badge {
            font-size: 1.1rem;
            padding: 10px 20px;
            border-radius: 25px;
        }
        
        .document-mini-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .document-mini-card:hover {
            border-color: #13357B;
            transform: translateY(-2px);
        }
        
        /* Sidebar layout adjustments */
        .status-container {
            margin-left: 0;
            padding-left: 0;
        }
        
        @media (max-width: 768px) {
            .progress-circle {
                width: 120px;
                height: 120px;
            }
            .progress-circle::before {
                width: 90px;
                height: 90px;
            }
            .progress-text {
                font-size: 18px;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/client-sidebar.php'; ?>

    <div class="status-container">
        <!-- Status Header -->
        <div class="status-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <h1 class="display-6 mb-2">Application Status</h1>
                        <p class="lead mb-0">Track your visa application progress</p>
                        <p class="mb-0">Reference ID: <strong><?php echo htmlspecialchars($client_data['reference_id']); ?></strong></p>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <div class="d-inline-block bg-white bg-opacity-20 rounded-pill px-4 py-2">
                            <i class="fas fa-calendar me-2"></i>
                            Submitted: <?php echo formatDate($client_data['submitted_date'], 'M d, Y'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container py-5">
            <div class="row">
                <!-- Progress Overview -->
                <div class="col-lg-4">
                    <div class="status-card text-center">
                        <h5 class="mb-4"><i class="fas fa-chart-pie me-2 text-primary"></i>Progress Overview</h5>
                        
                        <div class="progress-circle" style="--progress: <?php echo $progress_percentage; ?>">
                            <div class="progress-text"><?php echo $progress_percentage; ?>%</div>
                        </div>
                        
                        <h4 class="text-<?php echo $status_color; ?> mb-3"><?php echo $status_text; ?></h4>
                        
                        <span class="badge bg-<?php echo $status_color; ?> status-badge">
                            <?php echo ucfirst($client_data['status']); ?>
                        </span>
                        
                        <?php if (!empty($client_data['result_outcome'])): ?>
                            <div class="mt-3">
                                <small class="text-muted">
                                    <strong>Latest Update:</strong><br>
                                    <?php echo htmlspecialchars($client_data['result_outcome']); ?>
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Stats -->
                    <div class="status-card">
                        <h6 class="mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Application Details</h6>
                        
                        <div class="row text-center">
                            <div class="col-6 mb-3">
                                <div class="border-end">
                                    <h6 class="text-primary mb-1"><?php echo htmlspecialchars($client_data['visa_type']); ?></h6>
                                    <small class="text-muted">Visa Type</small>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <h6 class="text-success mb-1"><?php echo formatCurrencyForUser($client_data['deposit_paid'], $db, 'client', $client_id); ?></h6>
                                <small class="text-muted">Paid</small>
                            </div>
                            <div class="col-6">
                                <div class="border-end">
                                    <h6 class="text-warning mb-1"><?php echo formatCurrencyForUser($client_data['balance_due'], $db, 'client', $client_id); ?></h6>
                                    <small class="text-muted">Balance</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <h6 class="text-info mb-1"><?php echo count($recent_documents); ?></h6>
                                <small class="text-muted">Documents</small>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Timeline -->
                <div class="col-lg-8">
                    <div class="status-card">
                        <h5 class="mb-4"><i class="fas fa-history me-2 text-primary"></i>Application Timeline</h5>
                        
                        <div class="timeline">
                            <?php foreach ($timeline_events as $event): ?>
                                <div class="timeline-item">
                                    <div class="timeline-marker <?php echo $event['status']; ?>">
                                        <i class="fas <?php echo $event['icon']; ?> timeline-icon"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="mb-0"><?php echo htmlspecialchars($event['title']); ?></h6>
                                            <small class="text-muted"><?php echo formatDate($event['date'], 'M d, Y'); ?></small>
                                        </div>
                                        <p class="text-muted mb-0"><?php echo htmlspecialchars($event['description']); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Recent Documents -->
                    <?php if (!empty($recent_documents)): ?>
                    <div class="status-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h6 class="mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Recent Documents</h6>
                            <a href="client-documents.php" class="btn btn-sm btn-outline-primary">View All</a>
                        </div>
                        
                        <?php foreach ($recent_documents as $doc): ?>
                            <div class="document-mini-card">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-center">
                                            <?php
                                            $file_ext = strtolower(pathinfo($doc['original_filename'], PATHINFO_EXTENSION));
                                            $icon_class = in_array($file_ext, ['jpg', 'jpeg', 'png']) ? 'fa-image text-success' : 
                                                         ($file_ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file-alt text-info');
                                            ?>
                                            <i class="fas <?php echo $icon_class; ?> me-3"></i>
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($doc['document_name']); ?></h6>
                                                <small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $doc['document_type'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <span class="badge bg-<?php echo $doc['status'] == 'approved' ? 'success' : ($doc['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                            <?php echo ucfirst($doc['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="status-card text-center">
                        <h6 class="mb-4">Need Help or Have Questions?</h6>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="client-documents.php" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-upload mb-2 d-block"></i>
                                    Upload Documents
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="client-profile.php" class="btn btn-outline-info w-100">
                                    <i class="fas fa-user mb-2 d-block"></i>
                                    Update Profile
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="mailto:info@m25travelagency.com" class="btn btn-outline-success w-100">
                                    <i class="fas fa-envelope mb-2 d-block"></i>
                                    Contact Support
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="client-dashboard.php" class="btn btn-primary w-100">
                                    <i class="fas fa-tachometer-alt mb-2 d-block"></i>
                                    Back to Dashboard
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
        // Auto-refresh page every 2 minutes to check for status updates
        setTimeout(function() {
            location.reload();
        }, 120000); // 2 minutes
        
        // Smooth scroll for timeline items
        $(document).ready(function() {
            $('.timeline-item').each(function(index) {
                $(this).delay(index * 200).fadeIn(500);
            });
        });
    </script>
    <?php include 'includes/client-sidebar-close.php'; ?>
</body>
</html>
