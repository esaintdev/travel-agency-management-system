<?php
/**
 * Client Invoice View - View invoice details from client perspective
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

// Get invoice ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Invoice ID is required.";
    header('Location: client-payments.php');
    exit();
}

$invoice_id = intval($_GET['id']);

try {
    // Get invoice details (ensure it belongs to current client)
    $stmt = $db->prepare("
        SELECT i.*, a.full_name as created_by_name
        FROM invoices i 
        LEFT JOIN admin_users a ON i.created_by = a.id
        WHERE i.id = ? AND i.client_id = ?
    ");
    $stmt->execute([$invoice_id, $client['id']]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        $_SESSION['error_message'] = "Invoice not found or access denied.";
        header('Location: client-payments.php');
        exit();
    }
    
    // Get payment history for this invoice
    $stmt = $db->prepare("
        SELECT p.*
        FROM payments p 
        WHERE p.invoice_id = ? AND p.client_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$invoice_id, $client['id']]);
    $payments = $stmt->fetchAll();
    
    // Calculate totals
    $total_paid = 0;
    foreach ($payments as $payment) {
        if ($payment['payment_status'] === 'completed') {
            $total_paid += $payment['amount'];
        }
    }
    $balance_remaining = $invoice['amount'] - $total_paid;
    
} catch (Exception $e) {
    error_log("Client invoice view error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error loading invoice details.";
    header('Location: client-payments.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice Details - M25 Travel & Tour Agency</title>
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
        
        .invoice-header {
            background: linear-gradient(135deg, #13357B 0%, #FEA116 100%);
            color: white;
            padding: 40px 0;
        }
        
        .invoice-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .invoice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .info-row {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .status-badge {
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
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
        
        .pay-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .pay-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
    </style>
</head>

<body>
    <?php include 'includes/client-sidebar.php'; ?>

    <!-- Invoice Header -->
    <div class="invoice-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-6 mb-2">Invoice Details</h1>
                    <p class="lead mb-0">Invoice <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
                    <p class="mb-0">Reference ID: <strong><?php echo htmlspecialchars($client['reference_id']); ?></strong></p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-block bg-white bg-opacity-20 rounded-pill px-4 py-2">
                        <i class="fas fa-file-invoice me-2"></i>
                        Invoice View
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Invoice Content -->
    <div class="container py-5">
        <div class="row">
            <!-- Invoice Details -->
            <div class="col-lg-8">
                <div class="invoice-card card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>Invoice Information</h3>
                            <span class="status-badge bg-<?php 
                                echo match($invoice['status']) {
                                    'pending' => 'warning',
                                    'paid' => 'success',
                                    'overdue' => 'danger',
                                    'cancelled' => 'secondary',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo ucfirst($invoice['status']); ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Invoice Number:</strong></div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($invoice['invoice_number']); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Type:</strong></div>
                                <div class="col-sm-8">
                                    <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $invoice['invoice_type'])); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Description:</strong></div>
                                <div class="col-sm-8"><?php echo htmlspecialchars($invoice['description']); ?></div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Amount:</strong></div>
                                <div class="col-sm-8">
                                    <h4 class="text-primary mb-0"><?php echo formatCurrencyForUser($invoice['amount'], $db, 'client', $client['id']); ?></h4>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Due Date:</strong></div>
                                <div class="col-sm-8">
                                    <?php 
                                    $due_date = new DateTime($invoice['due_date']);
                                    $today = new DateTime();
                                    $is_overdue = $due_date < $today && $invoice['status'] === 'pending';
                                    ?>
                                    <span class="<?php echo $is_overdue ? 'text-danger' : ''; ?>">
                                        <?php echo formatClientDate($invoice['due_date'], 'F d, Y'); ?>
                                        <?php if ($is_overdue): ?>
                                            <i class="fas fa-exclamation-triangle ms-2"></i>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Created:</strong></div>
                                <div class="col-sm-8">
                                    <?php echo formatClientDate($invoice['created_at'], 'F d, Y g:i A'); ?>
                                    <?php if ($invoice['created_by_name']): ?>
                                        <br><small class="text-muted">by <?php echo htmlspecialchars($invoice['created_by_name']); ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($invoice['paid_at']): ?>
                        <div class="info-row">
                            <div class="row">
                                <div class="col-sm-4"><strong>Paid Date:</strong></div>
                                <div class="col-sm-8 text-success">
                                    <i class="fas fa-check-circle me-2"></i>
                                    <?php echo formatClientDate($invoice['paid_at'], 'F d, Y g:i A'); ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Payment History -->
                <div class="invoice-card card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h3 class="mb-0"><i class="fas fa-history me-2 text-primary"></i>Payment History</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($payments)): ?>
                            <div class="text-center py-4">
                                <i class="fas fa-receipt fa-2x text-muted mb-3"></i>
                                <p class="text-muted mb-0">No payments recorded for this invoice yet.</p>
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
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <strong><?php echo htmlspecialchars($payment['payment_reference']); ?></strong>
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
                                    
                                    <div class="row text-sm">
                                        <div class="col-sm-4">
                                            <strong>Amount:</strong> <?php echo formatCurrencyForUser($payment['amount'], $db, 'client', $client['id']); ?>
                                        </div>
                                        <div class="col-sm-4">
                                            <strong>Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?>
                                        </div>
                                        <div class="col-sm-4">
                                            <strong>Date:</strong> <?php echo formatClientDate($payment['created_at'], 'M d, Y'); ?>
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
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Actions & Summary -->
            <div class="col-lg-4">
                <!-- Payment Summary -->
                <div class="invoice-card card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="mb-0"><i class="fas fa-calculator me-2 text-primary"></i>Payment Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Invoice Amount:</span>
                                <strong><?php echo formatCurrencyForUser($invoice['amount'], $db, 'client', $client['id']); ?></strong>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Total Paid:</span>
                                <strong class="text-success"><?php echo formatCurrencyForUser($total_paid, $db, 'client', $client['id']); ?></strong>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="mb-0">
                            <div class="d-flex justify-content-between">
                                <span><strong>Balance Due:</strong></span>
                                <strong class="<?php echo $balance_remaining > 0 ? 'text-danger' : 'text-success'; ?>">
                                    <?php echo formatCurrencyForUser($balance_remaining, $db, 'client', $client['id']); ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="invoice-card card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h5 class="mb-0"><i class="fas fa-cogs me-2 text-primary"></i>Actions</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-3">
                            <?php if ($invoice['status'] === 'pending' && $balance_remaining > 0): ?>
                            <button class="btn pay-btn" onclick="payInvoice(<?php echo $invoice['id']; ?>, '<?php echo htmlspecialchars($invoice['invoice_number']); ?>', <?php echo $balance_remaining; ?>)">
                                <i class="fas fa-credit-card me-2"></i>Pay Now
                            </button>
                            <?php endif; ?>
                            
                            <a href="client-invoice-pdf.php?id=<?php echo $invoice['id']; ?>" class="btn btn-outline-primary" target="_blank">
                                <i class="fas fa-download me-2"></i>Download PDF
                            </a>
                            
                            <a href="client-payments.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Payments
                            </a>
                            
                            <a href="client-dashboard.php" class="btn btn-outline-info">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </div>
                        
                        <?php if ($invoice['status'] === 'pending'): ?>
                        <div class="mt-4 p-3 bg-light rounded">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Need Help?</strong><br>
                                Contact us at +233 59 260 5752 or 
                                <a href="mailto:support@m25travelagency.com">support@m25travelagency.com</a>
                                for payment assistance.
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Make Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-credit-card fa-3x text-primary mb-3"></i>
                        <h4>Invoice: <span id="paymentInvoiceNumber"></span></h4>
                        <h3 class="text-primary">Amount: <span id="paymentAmount"></span></h3>
                    </div>
                    
                    <form id="paymentForm" method="POST" action="process-payment.php">
                        <input type="hidden" id="invoiceId" name="invoice_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" name="payment_method" required>
                                <option value="">Select Payment Method</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="credit_card">Credit Card</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="paypal">PayPal</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Payment Notes (Optional)</label>
                            <textarea class="form-control" name="payment_notes" rows="3" placeholder="Any additional notes about this payment"></textarea>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Payment Instructions:</strong><br>
                            After submitting this form, you will receive payment instructions via email. 
                            Your payment will be processed within 24-48 hours after we receive it.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="paymentForm" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Submit Payment Request
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
        
        function payInvoice(invoiceId, invoiceNumber, amount) {
            document.getElementById('invoiceId').value = invoiceId;
            document.getElementById('paymentInvoiceNumber').textContent = invoiceNumber;
            document.getElementById('paymentAmount').textContent = '<?php echo formatCurrencyForUser("' + amount + '", $db, "client", $client["id"]); ?>';
            
            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        }
    </script>
    <?php include 'includes/client-sidebar-close.php'; ?>
</body>
</html>
