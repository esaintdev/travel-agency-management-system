<?php
/**
 * Client Payment History - View payment transaction history
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

try {
    // Get client's payment history
    $stmt = $db->prepare("
        SELECT p.*, i.invoice_number, i.description as invoice_description
        FROM payments p 
        LEFT JOIN invoices i ON p.invoice_id = i.id
        WHERE p.client_id = ? 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$client['id']]);
    $payments = $stmt->fetchAll();
    
    // Get payment summary by year
    $stmt = $db->prepare("
        SELECT 
            YEAR(payment_date) as year,
            COUNT(*) as payment_count,
            SUM(amount) as total_amount
        FROM payments 
        WHERE client_id = ? AND payment_status = 'completed'
        GROUP BY YEAR(payment_date)
        ORDER BY year DESC
    ");
    $stmt->execute([$client['id']]);
    $yearly_summary = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Client payment history error: " . $e->getMessage());
    $payments = [];
    $yearly_summary = [];
}

// Get client's preferred currency
$client_currency = getUserPreferredCurrency($db, 'client', $client['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment History - M25 Travel & Tour Agency</title>
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
        
        .history-header {
            background: linear-gradient(135deg, #13357B 0%, #FEA116 100%);
            color: white;
            padding: 40px 0;
        }
        
        .history-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .history-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .payment-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .payment-item:hover {
            border-color: #13357B;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .payment-completed {
            border-left: 4px solid #28a745;
        }
        
        .payment-pending {
            border-left: 4px solid #ffc107;
        }
        
        .payment-failed {
            border-left: 4px solid #dc3545;
        }
        
        .summary-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <?php include 'includes/client-sidebar.php'; ?>

    <!-- History Header -->
    <div class="history-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-6 mb-2">Payment History</h1>
                    <p class="lead mb-0">View your complete payment transaction history</p>
                    <p class="mb-0">Reference ID: <strong><?php echo htmlspecialchars($client['reference_id']); ?></strong></p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-block bg-white bg-opacity-20 rounded-pill px-4 py-2">
                        <i class="fas fa-history me-2"></i>
                        Transaction History
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Content -->
    <div class="container py-5">
        
        <!-- Yearly Summary -->
        <?php if (!empty($yearly_summary)): ?>
        <div class="row mb-5">
            <div class="col-12">
                <div class="history-card card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h3 class="mb-0"><i class="fas fa-chart-bar me-2 text-primary"></i>Payment Summary by Year</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($yearly_summary as $summary): ?>
                            <div class="col-md-4 mb-3">
                                <div class="summary-card text-center">
                                    <h4 class="text-primary"><?php echo $summary['year']; ?></h4>
                                    <p class="mb-1"><strong><?php echo $summary['payment_count']; ?></strong> payments</p>
                                    <p class="mb-0 text-success">
                                        <strong><?php echo formatCurrencyForUser($summary['total_amount'], $db, 'client', $client['id']); ?></strong>
                                    </p>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Payment History -->
        <div class="row">
            <div class="col-12">
                <div class="history-card card">
                    <div class="card-header bg-transparent border-0 pb-0 d-flex justify-content-between align-items-center">
                        <h3 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>All Transactions</h3>
                        <a href="client-payments.php" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Make New Payment
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($payments)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No payment history</h5>
                                <p class="text-muted">You haven't made any payments yet. Your payment history will appear here.</p>
                                <a href="client-payments.php" class="btn btn-primary">
                                    <i class="fas fa-credit-card me-2"></i>View Current Invoices
                                </a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($payments as $payment): ?>
                                <?php
                                $status_class = match($payment['payment_status']) {
                                    'completed' => 'payment-completed',
                                    'pending' => 'payment-pending',
                                    'failed' => 'payment-failed',
                                    default => 'payment-pending'
                                };
                                ?>
                                <div class="payment-item <?php echo $status_class; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="mb-0 text-primary"><?php echo htmlspecialchars($payment['payment_reference']); ?></h5>
                                                <span class="badge bg-<?php 
                                                    echo match($payment['payment_status']) {
                                                        'completed' => 'success',
                                                        'pending' => 'warning',
                                                        'failed' => 'danger',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($payment['payment_status']); ?>
                                                </span>
                                            </div>
                                            
                                            <?php if ($payment['invoice_number']): ?>
                                                <p class="mb-2">
                                                    <strong>Invoice:</strong> <?php echo htmlspecialchars($payment['invoice_number']); ?>
                                                    <?php if ($payment['invoice_description']): ?>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($payment['invoice_description']); ?></small>
                                                    <?php endif; ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <div class="row text-sm">
                                                <div class="col-sm-4">
                                                    <strong>Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                                </div>
                                                <div class="col-sm-4">
                                                    <strong>Submitted:</strong> <?php echo formatClientDate($payment['created_at'], 'M d, Y'); ?>
                                                </div>
                                                <div class="col-sm-4">
                                                    <?php if ($payment['payment_status'] === 'completed' && $payment['payment_date']): ?>
                                                        <strong>Processed:</strong> <?php echo formatClientDate($payment['payment_date'], 'M d, Y'); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <?php if ($payment['transaction_id']): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <strong>Transaction ID:</strong> <?php echo htmlspecialchars($payment['transaction_id']); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if ($payment['notes']): ?>
                                                <div class="mt-2">
                                                    <small class="text-muted">
                                                        <strong>Notes:</strong> <?php echo htmlspecialchars($payment['notes']); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-4 text-md-end">
                                            <div class="mb-3">
                                                <h4 class="mb-0"><?php echo formatCurrencyForUser($payment['amount'], $db, 'client', $client['id']); ?></h4>
                                            </div>
                                            
                                            <?php if ($payment['payment_status'] === 'completed'): ?>
                                                <div class="text-success">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    Payment Completed
                                                </div>
                                            <?php elseif ($payment['payment_status'] === 'pending'): ?>
                                                <div class="text-warning">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Processing...
                                                </div>
                                            <?php elseif ($payment['payment_status'] === 'failed'): ?>
                                                <div class="text-danger">
                                                    <i class="fas fa-times-circle me-1"></i>
                                                    Payment Failed
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Payments -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="client-payments.php" class="btn btn-outline-primary btn-lg me-3">
                    <i class="fas fa-arrow-left me-2"></i>Back to Payments
                </a>
                <a href="client-dashboard.php" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-home me-2"></i>Dashboard
                </a>
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
    </script>
    <?php include 'includes/client-sidebar-close.php'; ?>
</body>
</html>
