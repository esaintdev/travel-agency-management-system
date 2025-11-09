<?php
require_once 'config.php';
require_once 'includes/notification-functions.php';

// Check if user is logged in
requireLogin();

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: admin-login.html?timeout=1');
    exit();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'confirm_payment':
                    $payment_id = intval($_POST['payment_id']);
                    $transaction_id = sanitizeInput($_POST['transaction_id'] ?? '');
                    
                    // Get payment details
                    $stmt = $db->prepare("SELECT p.*, i.invoice_number FROM payments p LEFT JOIN invoices i ON p.invoice_id = i.id WHERE p.id = ?");
                    $stmt->execute([$payment_id]);
                    $payment = $stmt->fetch();
                    
                    if (!$payment) {
                        throw new Exception("Payment not found.");
                    }
                    
                    // Update payment status
                    $stmt = $db->prepare("UPDATE payments SET payment_status = 'completed', transaction_id = ?, processed_by = ?, payment_date = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$transaction_id, $_SESSION['admin_id'], $payment_id]);
                    
                    // Update invoice status if linked
                    if ($payment['invoice_id']) {
                        $stmt = $db->prepare("UPDATE invoices SET status = 'paid', paid_at = CURRENT_TIMESTAMP WHERE id = ?");
                        $stmt->execute([$payment['invoice_id']]);
                    }
                    
                    // Update client balances
                    $stmt = $db->prepare("UPDATE clients SET total_paid = total_paid + ?, balance_due = balance_due - ?, last_payment_date = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$payment['amount'], $payment['amount'], $payment['client_id']]);
                    
                    // Log activity
                    logActivity($_SESSION['admin_id'], $payment['client_id'], 'Payment Confirmed', "Confirmed payment {$payment['payment_reference']} for " . formatCurrencyByCode($payment['amount'], $payment['currency']), $db);
                    
                    // Create notification for client
                    createPaymentConfirmationNotification($db, $payment['client_id'], $payment_id, $payment['invoice_number'], formatCurrencyByCode($payment['amount'], $payment['currency']));
                    
                    $_SESSION['success'] = "Payment confirmed successfully!";
                    break;
                    
                case 'reject_payment':
                    $payment_id = intval($_POST['payment_id']);
                    $rejection_reason = sanitizeInput($_POST['rejection_reason']);
                    
                    $stmt = $db->prepare("UPDATE payments SET payment_status = 'failed', notes = CONCAT(COALESCE(notes, ''), '\nRejected: ', ?), processed_by = ? WHERE id = ?");
                    $stmt->execute([$rejection_reason, $_SESSION['admin_id'], $payment_id]);
                    
                    $_SESSION['success'] = "Payment rejected.";
                    break;
            }
        }
    } catch (Exception $e) {
        error_log("Payment management error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: admin-payments.php');
    exit();
}

// Get payments with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

try {
    // Build WHERE clause
    $where_conditions = [];
    $params = [];
    
    if (!empty($search)) {
        $where_conditions[] = "(p.payment_reference LIKE ? OR c.full_name LIKE ? OR c.reference_id LIKE ? OR i.invoice_number LIKE ?)";
        $search_param = "%{$search}%";
        $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    }
    
    if (!empty($status_filter)) {
        $where_conditions[] = "p.payment_status = ?";
        $params[] = $status_filter;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM payments p JOIN clients c ON p.client_id = c.id LEFT JOIN invoices i ON p.invoice_id = i.id {$where_clause}";
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($params);
    $total_payments = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_payments / $per_page);
    
    // Get payments
    $sql = "SELECT p.*, c.full_name, c.reference_id as client_ref, i.invoice_number, a.full_name as processed_by_name
            FROM payments p 
            JOIN clients c ON p.client_id = c.id 
            LEFT JOIN invoices i ON p.invoice_id = i.id
            LEFT JOIN admin_users a ON p.processed_by = a.id
            {$where_clause} 
            ORDER BY p.created_at DESC 
            LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $payments = $stmt->fetchAll();
    
    // Get payment statistics
    $stats_stmt = $db->prepare("
        SELECT 
            COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as pending_count,
            COUNT(CASE WHEN payment_status = 'completed' THEN 1 END) as completed_count,
            COUNT(CASE WHEN payment_status = 'failed' THEN 1 END) as failed_count,
            SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_received,
            SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END) as total_pending
        FROM payments
    ");
    $stats_stmt->execute();
    $payment_stats = $stats_stmt->fetch();
    
} catch (Exception $e) {
    error_log("Payment fetch error: " . $e->getMessage());
    $payments = [];
    $total_payments = 0;
    $total_pages = 0;
    $payment_stats = [
        'pending_count' => 0,
        'completed_count' => 0,
        'failed_count' => 0,
        'total_received' => 0,
        'total_pending' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Management - M25 Travel & Tour Agency</title>
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
        }
        .top-navbar {
            background: #13357B !important;
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        .search-form {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 20px;
        }
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    <?php include 'includes/admin-sidebar.php'; ?>
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar">
                    <div class="container-fluid">
                        <span class="navbar-brand">Payment Management</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="container-fluid p-4">
                    
                    <!-- Success/Error Messages -->
                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Payment Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-number text-warning"><?php echo number_format($payment_stats['pending_count']); ?></div>
                                <div class="text-muted">Pending Payments</div>
                                <small class="text-muted"><?php echo formatCurrencyForUser($payment_stats['total_pending'], $db, 'admin'); ?></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-number text-success"><?php echo number_format($payment_stats['completed_count']); ?></div>
                                <div class="text-muted">Completed</div>
                                <small class="text-muted"><?php echo formatCurrencyForUser($payment_stats['total_received'], $db, 'admin'); ?></small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-danger">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stat-number text-danger"><?php echo number_format($payment_stats['failed_count']); ?></div>
                                <div class="text-muted">Failed/Rejected</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="stat-icon text-info">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                                <div class="stat-number text-info"><?php echo formatCurrencyForUser($payment_stats['total_received'], $db, 'admin'); ?></div>
                                <div class="text-muted">Total Received</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search Form -->
                    <div class="search-form">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Payment History</h5>
                        </div>
                        
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search Payments</label>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Payment ref, client name, or invoice number">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status Filter</label>
                                <select class="form-select" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                    <option value="failed" <?php echo $status_filter === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-2"></i>Search
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">&nbsp;</label>
                                <div>
                                    <a href="admin-payments.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Payments Table -->
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">
                                Payment Records 
                                <span class="badge bg-primary"><?php echo number_format($total_payments); ?> total</span>
                                <?php if (!empty($search) || !empty($status_filter)): ?>
                                    <span class="badge bg-info">Filtered</span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        
                        <?php if (empty($payments)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No payments found</h5>
                                <?php if (!empty($search) || !empty($status_filter)): ?>
                                    <p class="text-muted">Try adjusting your search criteria</p>
                                    <a href="admin-payments.php" class="btn btn-primary">View All Payments</a>
                                <?php else: ?>
                                    <p class="text-muted">No payment records available yet</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Payment Ref</th>
                                            <th>Client</th>
                                            <th>Invoice</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($payments as $payment): ?>
                                            <tr>
                                                <td>
                                                    <strong class="text-primary"><?php echo htmlspecialchars($payment['payment_reference']); ?></strong>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($payment['full_name']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($payment['client_ref']); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php if ($payment['invoice_number']): ?>
                                                        <a href="admin-invoice-view.php?id=<?php echo $payment['invoice_id']; ?>" class="text-decoration-none">
                                                            <?php echo htmlspecialchars($payment['invoice_number']); ?>
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <strong><?php echo formatCurrencyByCode($payment['amount'], $payment['currency']); ?></strong>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
                                                        echo match($payment['payment_status']) {
                                                            'pending' => 'warning',
                                                            'completed' => 'success',
                                                            'failed' => 'danger',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst($payment['payment_status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <small><?php echo formatDate($payment['created_at'], 'M d, Y'); ?></small>
                                                    <?php if ($payment['processed_by_name']): ?>
                                                        <br><small class="text-muted">by <?php echo htmlspecialchars($payment['processed_by_name']); ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($payment['payment_status'] === 'pending'): ?>
                                                        <button class="btn btn-outline-success" title="Confirm Payment"
                                                                onclick="confirmPayment(<?php echo $payment['id']; ?>, '<?php echo htmlspecialchars($payment['payment_reference']); ?>')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" title="Reject Payment"
                                                                onclick="rejectPayment(<?php echo $payment['id']; ?>, '<?php echo htmlspecialchars($payment['payment_reference']); ?>')">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        <button class="btn btn-outline-info" title="View Details"
                                                                onclick="viewPaymentDetails(<?php echo $payment['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Payment pagination">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Previous</a>
                                            </li>
                                        <?php endif; ?>
                                        
                                        <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        
                                        <?php if ($page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>">Next</a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                            
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Payment Modal -->
    <div class="modal fade" id="confirmPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="confirmPaymentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="confirm_payment">
                        <input type="hidden" name="payment_id" id="confirmPaymentId">
                        
                        <p>Confirm payment: <strong id="confirmPaymentRef"></strong></p>
                        
                        <div class="mb-3">
                            <label class="form-label">Transaction ID (Optional)</label>
                            <input type="text" class="form-control" name="transaction_id" placeholder="Enter transaction/reference ID">
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This will mark the payment as completed and update the client's balance.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Confirm Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Payment Modal -->
    <div class="modal fade" id="rejectPaymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reject Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="rejectPaymentForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject_payment">
                        <input type="hidden" name="payment_id" id="rejectPaymentId">
                        
                        <p>Reject payment: <strong id="rejectPaymentRef"></strong></p>
                        
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason *</label>
                            <textarea class="form-control" name="rejection_reason" rows="3" required placeholder="Enter reason for rejection"></textarea>
                        </div>
                        
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This will mark the payment as failed. The client will need to resubmit payment.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmPayment(paymentId, paymentRef) {
            document.getElementById('confirmPaymentId').value = paymentId;
            document.getElementById('confirmPaymentRef').textContent = paymentRef;
            
            const modal = new bootstrap.Modal(document.getElementById('confirmPaymentModal'));
            modal.show();
        }
        
        function rejectPayment(paymentId, paymentRef) {
            document.getElementById('rejectPaymentId').value = paymentId;
            document.getElementById('rejectPaymentRef').textContent = paymentRef;
            
            const modal = new bootstrap.Modal(document.getElementById('rejectPaymentModal'));
            modal.show();
        }
        
        function viewPaymentDetails(paymentId) {
            // This could open a detailed view modal or redirect to a details page
            window.open('admin-payment-view.php?id=' + paymentId, '_blank');
        }
    </script>
    <?php include 'includes/admin-sidebar-close.php'; ?>
</body>
</html>
