<?php
require_once 'config.php';
requireLogin();
checkSessionTimeout();

// Get payment ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Payment ID is required.";
    header('Location: admin-payments.php');
    exit();
}

$payment_id = intval($_GET['id']);

// Fetch payment details with related information
try {
    $stmt = $db->prepare("
        SELECT 
            p.*,
            c.full_name as client_name,
            c.client_email,
            c.reference_id as client_reference,
            c.phone_number,
            i.invoice_number,
            i.description as invoice_description,
            i.amount as invoice_amount,
            i.currency as invoice_currency,
            i.due_date,
            i.status as invoice_status
        FROM payments p
        LEFT JOIN clients c ON p.client_id = c.id
        LEFT JOIN invoices i ON p.invoice_id = i.id
        WHERE p.id = ?
    ");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch();
    
    if (!$payment) {
        $_SESSION['error_message'] = "Payment not found.";
        header('Location: admin-payments.php');
        exit();
    }
    
    // Get payment history for this invoice
    $stmt = $db->prepare("
        SELECT * FROM payments 
        WHERE invoice_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$payment['invoice_id']]);
    $payment_history = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Error fetching payment details: " . $e->getMessage());
    $_SESSION['error_message'] = "Error loading payment details.";
    header('Location: admin-payments.php');
    exit();
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status') {
        $new_status = sanitizeInput($_POST['payment_status']);
        $admin_notes = sanitizeInput($_POST['admin_notes'] ?? '');
        
        try {
            $stmt = $db->prepare("
                UPDATE payments 
                SET payment_status = ?, admin_notes = ?, updated_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$new_status, $admin_notes, $payment_id]);
            
            // Log activity
            logActivity($_SESSION['admin_id'], null, 'Payment Status Updated', 
                       "Payment {$payment['payment_reference']} status changed to {$new_status}", $db);
            
            // If payment is completed, update invoice status
            if ($new_status === 'completed') {
                // Calculate total payments for this invoice
                $stmt = $db->prepare("
                    SELECT COALESCE(SUM(amount), 0) as total_paid 
                    FROM payments 
                    WHERE invoice_id = ? AND payment_status = 'completed'
                ");
                $stmt->execute([$payment['invoice_id']]);
                $total_paid = $stmt->fetch()['total_paid'];
                
                // Update invoice status if fully paid
                if ($total_paid >= $payment['invoice_amount']) {
                    $stmt = $db->prepare("UPDATE invoices SET status = 'paid' WHERE id = ?");
                    $stmt->execute([$payment['invoice_id']]);
                }
            }
            
            $_SESSION['success_message'] = "Payment status updated successfully.";
            header('Location: admin-payment-view.php?id=' . $payment_id);
            exit();
            
        } catch (Exception $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            $_SESSION['error_message'] = "Error updating payment status.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details - M25 Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .top-navbar {
            background: linear-gradient(135deg, #13357B 0%, #1e4a8c 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .payment-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-completed { background: #d1edff; color: #0c5460; }
        .status-failed { background: #f8d7da; color: #721c24; }
        .status-cancelled { background: #e2e3e5; color: #383d41; }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .payment-method-icon {
            width: 40px;
            height: 25px;
            background: #13357B;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 10px;
        }
        .history-item {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            border-left: 4px solid #13357B;
        }
        .btn-action {
            margin: 5px;
        }
        @media print {
            .no-print { display: none !important; }
            .payment-card { box-shadow: none; border: 1px solid #ddd; }
        }
    </style>
</head>

<body>
    <?php include 'includes/admin-sidebar.php'; ?>
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar">
                    <div class="container-fluid">
                        <span class="navbar-brand">Payment Details</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Payment Details Content -->
                <div class="container-fluid p-4">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Action Buttons -->
                    <div class="row mb-4 no-print">
                        <div class="col-12">
                            <a href="admin-payments.php" class="btn btn-secondary btn-action">
                                <i class="fas fa-arrow-left me-2"></i>Back to Payments
                            </a>
                            <button onclick="window.print()" class="btn btn-primary btn-action">
                                <i class="fas fa-print me-2"></i>Print Details
                            </button>
                            <?php if ($payment['invoice_id']): ?>
                            <a href="admin-invoice-view.php?id=<?php echo $payment['invoice_id']; ?>" class="btn btn-info btn-action" target="_blank">
                                <i class="fas fa-file-invoice me-2"></i>View Invoice
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Payment Information -->
                        <div class="col-md-8">
                            <div class="payment-card">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4><i class="fas fa-credit-card me-2 text-primary"></i>Payment Information</h4>
                                    <span class="status-badge status-<?php echo strtolower($payment['payment_status']); ?>">
                                        <?php echo ucfirst($payment['payment_status']); ?>
                                    </span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Payment Reference</span>
                                    <span class="info-value"><strong><?php echo htmlspecialchars($payment['payment_reference']); ?></strong></span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Amount</span>
                                    <span class="info-value">
                                        <strong><?php echo formatCurrencyByCode($payment['amount'], $payment['currency']); ?></strong>
                                    </span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Payment Method</span>
                                    <span class="info-value">
                                        <?php 
                                        $method_icons = [
                                            'stripe_card' => '<i class="fab fa-cc-stripe"></i>',
                                            'bank_transfer' => '<i class="fas fa-university"></i>',
                                            'mobile_money' => '<i class="fas fa-mobile-alt"></i>',
                                            'paypal' => '<i class="fab fa-paypal"></i>'
                                        ];
                                        echo $method_icons[$payment['payment_method']] ?? '<i class="fas fa-credit-card"></i>';
                                        ?>
                                        <?php echo ucwords(str_replace('_', ' ', $payment['payment_method'])); ?>
                                    </span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Payment Gateway</span>
                                    <span class="info-value"><?php echo ucfirst($payment['payment_gateway'] ?? 'Manual'); ?></span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Transaction ID</span>
                                    <span class="info-value"><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Created Date</span>
                                    <span class="info-value"><?php echo formatDate($payment['created_at']); ?></span>
                                </div>
                                
                                <?php if ($payment['updated_at'] && $payment['updated_at'] !== $payment['created_at']): ?>
                                <div class="info-row">
                                    <span class="info-label">Last Updated</span>
                                    <span class="info-value"><?php echo formatDate($payment['updated_at']); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($payment['notes']): ?>
                                <div class="info-row">
                                    <span class="info-label">Notes</span>
                                    <span class="info-value"><?php echo nl2br(htmlspecialchars($payment['notes'])); ?></span>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($payment['admin_notes']): ?>
                                <div class="info-row">
                                    <span class="info-label">Admin Notes</span>
                                    <span class="info-value"><?php echo nl2br(htmlspecialchars($payment['admin_notes'])); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Client Information -->
                            <div class="payment-card">
                                <h4><i class="fas fa-user me-2 text-primary"></i>Client Information</h4>
                                
                                <div class="info-row">
                                    <span class="info-label">Client Name</span>
                                    <span class="info-value"><?php echo htmlspecialchars($payment['client_name']); ?></span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Reference ID</span>
                                    <span class="info-value">
                                        <a href="admin-client-view.php?id=<?php echo $payment['client_id']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($payment['client_reference']); ?>
                                        </a>
                                    </span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Email</span>
                                    <span class="info-value"><?php echo htmlspecialchars($payment['client_email']); ?></span>
                                </div>
                                
                                <?php if ($payment['phone_number']): ?>
                                <div class="info-row">
                                    <span class="info-label">Phone</span>
                                    <span class="info-value"><?php echo htmlspecialchars($payment['phone_number']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Invoice Information -->
                            <?php if ($payment['invoice_id']): ?>
                            <div class="payment-card">
                                <h4><i class="fas fa-file-invoice me-2 text-primary"></i>Invoice Information</h4>
                                
                                <div class="info-row">
                                    <span class="info-label">Invoice Number</span>
                                    <span class="info-value">
                                        <a href="admin-invoice-view.php?id=<?php echo $payment['invoice_id']; ?>" target="_blank">
                                            <?php echo htmlspecialchars($payment['invoice_number']); ?>
                                        </a>
                                    </span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Invoice Amount</span>
                                    <span class="info-value"><?php echo formatCurrencyByCode($payment['invoice_amount'], $payment['invoice_currency']); ?></span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Invoice Status</span>
                                    <span class="info-value">
                                        <span class="badge bg-<?php echo $payment['invoice_status'] === 'paid' ? 'success' : ($payment['invoice_status'] === 'pending' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst($payment['invoice_status']); ?>
                                        </span>
                                    </span>
                                </div>
                                
                                <div class="info-row">
                                    <span class="info-label">Due Date</span>
                                    <span class="info-value"><?php echo formatDate($payment['due_date']); ?></span>
                                </div>
                                
                                <?php if ($payment['invoice_description']): ?>
                                <div class="info-row">
                                    <span class="info-label">Description</span>
                                    <span class="info-value"><?php echo htmlspecialchars($payment['invoice_description']); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Actions Panel -->
                        <div class="col-md-4">
                            <!-- Update Status -->
                            <div class="payment-card no-print">
                                <h5><i class="fas fa-edit me-2 text-primary"></i>Update Payment</h5>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="update_status">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Payment Status</label>
                                        <select name="payment_status" class="form-select" required>
                                            <option value="pending" <?php echo $payment['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="completed" <?php echo $payment['payment_status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="failed" <?php echo $payment['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                            <option value="cancelled" <?php echo $payment['payment_status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Admin Notes</label>
                                        <textarea name="admin_notes" class="form-control" rows="3" placeholder="Add notes about this payment..."><?php echo htmlspecialchars($payment['admin_notes'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save me-2"></i>Update Payment
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Payment History -->
                            <?php if (count($payment_history) > 1): ?>
                            <div class="payment-card">
                                <h5><i class="fas fa-history me-2 text-primary"></i>Payment History</h5>
                                
                                <?php foreach ($payment_history as $history_payment): ?>
                                <div class="history-item">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong><?php echo htmlspecialchars($history_payment['payment_reference']); ?></strong>
                                        <span class="status-badge status-<?php echo strtolower($history_payment['payment_status']); ?>">
                                            <?php echo ucfirst($history_payment['payment_status']); ?>
                                        </span>
                                    </div>
                                    <div class="small text-muted">
                                        <?php echo formatCurrencyByCode($history_payment['amount'], $history_payment['currency']); ?> • 
                                        <?php echo formatDate($history_payment['created_at']); ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'includes/admin-sidebar-close.php'; ?>
</body>
</html>
