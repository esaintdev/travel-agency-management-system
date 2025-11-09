<?php
/**
 * Client Payments - View and pay invoices
 */

// Start session and include required files
session_start();
require_once 'config.php';
require_once 'includes/client-auth.php';
require_once 'includes/stripe-helper.php';

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

// Check Stripe configuration status
$stripe_status = getStripeStatus($db);
$stripe_configured = ($stripe_status['status'] === 'configured');

try {
    // Get client's invoices
    $stmt = $db->prepare("
        SELECT i.*, p.amount as paid_amount, p.payment_date 
        FROM invoices i 
        LEFT JOIN payments p ON i.id = p.invoice_id AND p.payment_status = 'completed'
        WHERE i.client_id = ? 
        ORDER BY i.created_at DESC
    ");
    $stmt->execute([$client['id']]);
    $invoices = $stmt->fetchAll();
    
    // Get payment summary
    $stmt = $db->prepare("
        SELECT 
            SUM(CASE WHEN status = 'pending' THEN amount ELSE 0 END) as pending_amount,
            SUM(CASE WHEN status = 'paid' THEN amount ELSE 0 END) as paid_amount,
            SUM(CASE WHEN status = 'overdue' THEN amount ELSE 0 END) as overdue_amount,
            COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
            COUNT(CASE WHEN status = 'overdue' THEN 1 END) as overdue_count
        FROM invoices 
        WHERE client_id = ?
    ");
    $stmt->execute([$client['id']]);
    $payment_summary = $stmt->fetch();
    
} catch (Exception $e) {
    error_log("Client payments error: " . $e->getMessage());
    $invoices = [];
    $payment_summary = [
        'pending_amount' => 0,
        'paid_amount' => 0,
        'overdue_amount' => 0,
        'pending_count' => 0,
        'overdue_count' => 0
    ];
}

// Get client's preferred currency
$client_currency = getUserPreferredCurrency($db, 'client', $client['id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payments & Invoices - M25 Travel & Tour Agency</title>
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
        
        .payment-header {
            background: linear-gradient(135deg, #13357B 0%, #FEA116 100%);
            color: white;
            padding: 40px 0;
        }
        
        .payment-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .payment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
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
            margin: 0 auto 15px;
        }
        
        .invoice-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        
        .invoice-item:hover {
            border-color: #13357B;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .invoice-pending {
            border-left: 4px solid #ffc107;
        }
        
        .invoice-paid {
            border-left: 4px solid #28a745;
        }
        
        .invoice-overdue {
            border-left: 4px solid #dc3545;
        }
        
        .pay-btn {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            color: white;
            padding: 10px 25px;
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

    <!-- Payment Header -->
    <div class="payment-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-6 mb-2">Invoices & Payments</h1>
                    <p class="lead mb-0">Manage your payments and view invoice history</p>
                    <p class="mb-0">Reference ID: <strong><?php echo htmlspecialchars($client['reference_id']); ?></strong></p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-block bg-white bg-opacity-20 rounded-pill px-4 py-2">
                        <i class="fas fa-credit-card me-2"></i>
                        Payment Portal
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Content -->
    <div class="container py-5">
        <!-- Payment Summary -->
        <div class="row mb-5">
            <div class="col-lg-3 col-md-6">
                <div class="payment-card card">
                    <div class="card-body stat-card">
                        <div class="stat-icon bg-warning text-white">
                            <i class="fas fa-clock"></i>
                        </div>
                        <h4><?php echo formatCurrencyForUser($payment_summary['pending_amount'] ?? 0, $db, 'client', $client['id']); ?></h4>
                        <p class="text-muted mb-0">Pending Payments</p>
                        <?php if ($payment_summary['pending_count'] > 0): ?>
                            <small class="text-warning"><?php echo $payment_summary['pending_count']; ?> invoice(s)</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="payment-card card">
                    <div class="card-body stat-card">
                        <div class="stat-icon bg-success text-white">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4><?php echo formatCurrencyForUser($payment_summary['paid_amount'] ?? 0, $db, 'client', $client['id']); ?></h4>
                        <p class="text-muted mb-0">Total Paid</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="payment-card card">
                    <div class="card-body stat-card">
                        <div class="stat-icon bg-danger text-white">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h4><?php echo formatCurrencyForUser($payment_summary['overdue_amount'] ?? 0, $db, 'client', $client['id']); ?></h4>
                        <p class="text-muted mb-0">Overdue</p>
                        <?php if ($payment_summary['overdue_count'] > 0): ?>
                            <small class="text-danger"><?php echo $payment_summary['overdue_count']; ?> invoice(s)</small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="payment-card card">
                    <div class="card-body stat-card">
                        <div class="stat-icon bg-info text-white">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                        <h4><?php echo count($invoices); ?></h4>
                        <p class="text-muted mb-0">Total Invoices</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Invoices List -->
        <div class="row">
            <div class="col-12">
                <div class="payment-card card">
                    <div class="card-header bg-transparent border-0 pb-0">
                        <h3 class="mb-0"><i class="fas fa-file-invoice me-2 text-primary"></i>Your Invoices</h3>
                    </div>
                    <div class="card-body">
                        <?php if (empty($invoices)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No invoices found</h5>
                                <p class="text-muted">You don't have any invoices yet. They will appear here when created by our team.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($invoices as $invoice): ?>
                                <?php
                                $due_date = new DateTime($invoice['due_date']);
                                $today = new DateTime();
                                $is_overdue = $due_date < $today && $invoice['status'] === 'pending';
                                $status_class = match($invoice['status']) {
                                    'pending' => $is_overdue ? 'invoice-overdue' : 'invoice-pending',
                                    'paid' => 'invoice-paid',
                                    default => 'invoice-pending'
                                };
                                ?>
                                <div class="invoice-item <?php echo $status_class; ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h5 class="mb-0 text-primary"><?php echo htmlspecialchars($invoice['invoice_number']); ?></h5>
                                                <span class="badge bg-<?php 
                                                    echo match($invoice['status']) {
                                                        'pending' => $is_overdue ? 'danger' : 'warning',
                                                        'paid' => 'success',
                                                        'cancelled' => 'secondary',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo $is_overdue && $invoice['status'] === 'pending' ? 'Overdue' : ucfirst($invoice['status']); ?>
                                                </span>
                                            </div>
                                            
                                            <p class="mb-2"><?php echo htmlspecialchars($invoice['description']); ?></p>
                                            
                                            <div class="row text-sm">
                                                <div class="col-sm-4">
                                                    <strong>Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $invoice['invoice_type'])); ?>
                                                </div>
                                                <div class="col-sm-4">
                                                    <strong>Due Date:</strong> 
                                                    <span class="<?php echo $is_overdue ? 'text-danger' : ''; ?>">
                                                        <?php echo formatClientDate($invoice['due_date'], 'M d, Y'); ?>
                                                    </span>
                                                </div>
                                                <div class="col-sm-4">
                                                    <strong>Created:</strong> <?php echo formatClientDate($invoice['created_at'], 'M d, Y'); ?>
                                                </div>
                                            </div>
                                            
                                            <?php if ($invoice['status'] === 'paid' && $invoice['payment_date']): ?>
                                                <div class="mt-2">
                                                    <small class="text-success">
                                                        <i class="fas fa-check-circle me-1"></i>
                                                        Paid on <?php echo formatClientDate($invoice['payment_date'], 'M d, Y'); ?>
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="col-md-4 text-md-end">
                                            <div class="mb-3">
                                                <h4 class="mb-0"><?php echo formatCurrencyForUser($invoice['amount'], $db, 'client', $client['id']); ?></h4>
                                            </div>
                                            
                                            <div class="btn-group-vertical d-grid gap-2">
                                                <?php if ($invoice['status'] === 'pending'): ?>
                                                    <button class="btn pay-btn" onclick="payInvoice(<?php echo $invoice['id']; ?>, '<?php echo htmlspecialchars($invoice['invoice_number']); ?>', <?php echo $invoice['amount']; ?>)">
                                                        <i class="fas fa-credit-card me-2"></i>Pay Now
                                                    </button>
                                                <?php endif; ?>
                                                
                                                <a href="client-invoice-view.php?id=<?php echo $invoice['id']; ?>" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye me-2"></i>View Details
                                                </a>
                                                
                                                <a href="client-invoice-pdf.php?id=<?php echo $invoice['id']; ?>" class="btn btn-outline-secondary btn-sm" target="_blank">
                                                    <i class="fas fa-download me-2"></i>Download PDF
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment History Link -->
        <div class="row mt-4">
            <div class="col-12 text-center">
                <a href="client-payment-history.php" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-history me-2"></i>View Payment History
                </a>
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
                    
                    <!-- Payment Method Selection -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Choose Payment Method:</h5>
                            
                            <!-- Stripe Payment (Recommended) -->
                            <?php if ($stripe_configured): ?>
                            <div class="payment-method-card mb-3" style="border: 2px solid #28a745; border-radius: 10px; padding: 20px; background: #f8fff9;">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1 text-success">
                                            <i class="fab fa-cc-stripe me-2"></i>Pay with Card (Recommended)
                                        </h6>
                                        <small class="text-muted">Secure, instant payment with credit/debit card</small>
                                        <div class="mt-2">
                                            <i class="fab fa-cc-visa me-1"></i>
                                            <i class="fab fa-cc-mastercard me-1"></i>
                                            <i class="fab fa-cc-amex me-1"></i>
                                            <i class="fab fa-cc-discover me-1"></i>
                                        </div>
                                        <div class="mt-1">
                                            <small class="badge bg-success"><?php echo strtoupper($stripe_status['environment']); ?> Mode</small>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-success" onclick="payWithStripe()">
                                        <i class="fas fa-credit-card me-2"></i>Pay Now
                                    </button>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="payment-method-card mb-3" style="border: 2px solid #ffc107; border-radius: 10px; padding: 20px; background: #fffbf0;">
                                <div class="text-center">
                                    <h6 class="mb-2 text-warning">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Card Payments Unavailable
                                    </h6>
                                    <small class="text-muted">
                                        Card payment processing is currently being configured. 
                                        Please use alternative payment methods below or contact support.
                                    </small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Other Payment Methods -->
                            <div class="payment-method-card" style="border: 1px solid #dee2e6; border-radius: 10px; padding: 20px;">
                                <h6 class="mb-3">
                                    <i class="fas fa-university me-2"></i>Other Payment Methods
                                </h6>
                                
                                <form id="paymentForm" method="POST" action="process-payment.php">
                                    <input type="hidden" id="invoiceId" name="invoice_id">
                                    
                                    <div class="mb-3">
                                        <select class="form-select" name="payment_method" required>
                                            <option value="">Select Payment Method</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="mobile_money">Mobile Money</option>
                                            <option value="paypal">PayPal</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Payment Notes (Optional)</label>
                                        <textarea class="form-control" name="payment_notes" rows="2" placeholder="Any additional notes about this payment"></textarea>
                                    </div>
                                    
                                    <div class="alert alert-warning">
                                        <i class="fas fa-clock me-2"></i>
                                        <small>These methods require manual processing and may take 24-48 hours to confirm.</small>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-paper-plane me-2"></i>Submit Payment Request
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Stripe Payment Form (Hidden) -->
                    <form id="stripePaymentForm" method="POST" action="process-stripe-payment.php" style="display: none;">
                        <input type="hidden" id="stripeInvoiceId" name="invoice_id">
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
        
        let currentInvoiceId = null;
        let currentInvoiceNumber = null;
        let currentAmount = null;
        
        function payInvoice(invoiceId, invoiceNumber, amount) {
            // Store current invoice details
            currentInvoiceId = invoiceId;
            currentInvoiceNumber = invoiceNumber;
            currentAmount = amount;
            
            // Update modal content
            document.getElementById('invoiceId').value = invoiceId;
            document.getElementById('stripeInvoiceId').value = invoiceId;
            document.getElementById('paymentInvoiceNumber').textContent = invoiceNumber;
            document.getElementById('paymentAmount').textContent = formatCurrency(amount);
            
            const modal = new bootstrap.Modal(document.getElementById('paymentModal'));
            modal.show();
        }
        
        function payWithStripe() {
            if (!currentInvoiceId) {
                alert('Please select an invoice first.');
                return;
            }
            
            // Show loading state
            const stripeBtn = document.querySelector('button[onclick="payWithStripe()"]');
            const originalText = stripeBtn.innerHTML;
            stripeBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            stripeBtn.disabled = true;
            
            // Submit Stripe payment form
            document.getElementById('stripePaymentForm').submit();
        }
        
        function formatCurrency(amount) {
            // Simple currency formatting - you can enhance this based on your needs
            return new Intl.NumberFormat('en-US', {
                style: 'currency',
                currency: 'USD' // You can make this dynamic based on invoice currency
            }).format(amount);
        }
        
        // Handle payment method selection
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to payment method cards
            const paymentCards = document.querySelectorAll('.payment-method-card');
            paymentCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.transition = 'all 0.3s ease';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
    <?php include 'includes/client-sidebar-close.php'; ?>
</body>
</html>
