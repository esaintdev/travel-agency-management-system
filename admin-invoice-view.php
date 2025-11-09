<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: admin-login.html?timeout=1');
    exit();
}

// Get invoice ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "Invoice ID is required.";
    header('Location: admin-invoices.php');
    exit();
}

$invoice_id = intval($_GET['id']);

// Debug: Check if invoice ID is valid
if ($invoice_id <= 0) {
    $_SESSION['error'] = "Invalid invoice ID: " . $_GET['id'];
    header('Location: admin-invoices.php');
    exit();
}

try {
    // Debug: First check if invoices table exists
    $check_table = $db->query("SHOW TABLES LIKE 'invoices'");
    if ($check_table->rowCount() == 0) {
        $_SESSION['error'] = "Invoices table does not exist. Please run the setup script first.";
        header('Location: admin-invoices.php');
        exit();
    }
    
    // Debug: Check if invoice exists
    $count_stmt = $db->prepare("SELECT COUNT(*) as count FROM invoices WHERE id = ?");
    $count_stmt->execute([$invoice_id]);
    $count_result = $count_stmt->fetch();
    
    if ($count_result['count'] == 0) {
        $_SESSION['error'] = "Invoice with ID {$invoice_id} does not exist.";
        header('Location: admin-invoices.php');
        exit();
    }
    
    // Get invoice details with client information
    $stmt = $db->prepare("
        SELECT i.*, c.full_name, c.reference_id as client_ref, c.client_email,
               c.address, c.visa_type, a.full_name as created_by_name
        FROM invoices i 
        JOIN clients c ON i.client_id = c.id 
        LEFT JOIN admin_users a ON i.created_by = a.id
        WHERE i.id = ?
    ");
    $stmt->execute([$invoice_id]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        $_SESSION['error'] = "Invoice found but failed to join with client data. Check if client_id {$invoice_id} exists in clients table.";
        header('Location: admin-invoices.php');
        exit();
    }
    
    // Get payment history for this invoice
    $stmt = $db->prepare("
        SELECT p.*, a.full_name as processed_by_name
        FROM payments p 
        LEFT JOIN admin_users a ON p.processed_by = a.id
        WHERE p.invoice_id = ? 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$invoice_id]);
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
    error_log("Invoice view error: " . $e->getMessage());
    $_SESSION['error'] = "Error loading invoice details: " . $e->getMessage();
    header('Location: admin-invoices.php');
    exit();
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'update_status':
                $new_status = trim(htmlspecialchars($_POST['status']));
                $stmt = $db->prepare("UPDATE invoices SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$new_status, $invoice_id]);
                
                logActivity($_SESSION['admin_id'], $invoice['client_id'], 'Invoice Status Updated', "Invoice {$invoice['invoice_number']} status changed to {$new_status}", $db);
                $_SESSION['success'] = "Invoice status updated successfully!";
                break;
                
            case 'add_note':
                $note = trim(htmlspecialchars($_POST['note']));
                $current_notes = $invoice['notes'] ?? '';
                $new_notes = $current_notes . "\n[" . date('Y-m-d H:i:s') . " - " . $_SESSION['admin_name'] . "] " . $note;
                
                $stmt = $db->prepare("UPDATE invoices SET notes = ? WHERE id = ?");
                $stmt->execute([$new_notes, $invoice_id]);
                
                $_SESSION['success'] = "Note added successfully!";
                break;
        }
        
        header("Location: admin-invoice-view.php?id={$invoice_id}");
        exit();
        
    } catch (Exception $e) {
        error_log("Invoice update error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }
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
        .invoice-header {
            background: linear-gradient(135deg, #13357B 0%, #FEA116 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .status-badge {
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 20px;
        }
        .info-row {
            border-bottom: 1px solid #eee;
            padding: 15px 0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .payment-item {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
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
    </style>
</head>

<body>
    <?php include 'includes/admin-sidebar.php'; ?>
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar">
                    <div class="container-fluid">
                        <span class="navbar-brand">Invoice Details</span>
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
                    
                    <!-- Back Button -->
                    <div class="mb-3">
                        <a href="admin-invoices.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Invoices
                        </a>
                    </div>
                    
                    <!-- Invoice Header -->
                    <div class="invoice-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="mb-2">Invoice <?php echo htmlspecialchars($invoice['invoice_number']); ?></h2>
                                <p class="mb-0">
                                    <strong>Client:</strong> <?php echo htmlspecialchars($invoice['full_name']); ?> 
                                    (<?php echo htmlspecialchars($invoice['client_ref']); ?>)
                                </p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <div class="mb-2">
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
                                <h3 class="mb-0"><?php echo formatCurrencyByCode($invoice['amount'], $invoice['currency']); ?></h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Invoice Details -->
                        <div class="col-lg-8">
                            <div class="content-card">
                                <h4 class="mb-4"><i class="fas fa-file-invoice me-2 text-primary"></i>Invoice Information</h4>
                                
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
                                            <h5 class="text-primary mb-0"><?php echo formatCurrencyByCode($invoice['amount'], $invoice['currency']); ?></h5>
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
                                                <?php echo formatDate($invoice['due_date'], 'F d, Y'); ?>
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
                                            <?php echo formatDate($invoice['created_at'], 'F d, Y g:i A'); ?>
                                            <?php if ($invoice['created_by_name']): ?>
                                                <br><small class="text-muted">by <?php echo htmlspecialchars($invoice['created_by_name']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Last Updated:</strong></div>
                                        <div class="col-sm-8"><?php echo formatDate($invoice['updated_at'], 'F d, Y g:i A'); ?></div>
                                    </div>
                                </div>
                                
                                <?php if ($invoice['paid_at']): ?>
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Paid Date:</strong></div>
                                        <div class="col-sm-8 text-success">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <?php echo formatDate($invoice['paid_at'], 'F d, Y g:i A'); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <?php if ($invoice['notes']): ?>
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Notes:</strong></div>
                                        <div class="col-sm-8">
                                            <pre class="mb-0" style="white-space: pre-wrap; font-family: inherit;"><?php echo htmlspecialchars($invoice['notes']); ?></pre>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Client Information -->
                            <div class="content-card">
                                <h4 class="mb-4"><i class="fas fa-user me-2 text-primary"></i>Client Information</h4>
                                
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Full Name:</strong></div>
                                        <div class="col-sm-8"><?php echo htmlspecialchars($invoice['full_name']); ?></div>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Reference ID:</strong></div>
                                        <div class="col-sm-8">
                                            <a href="admin-client-view.php?id=<?php echo urlencode($invoice['client_ref']); ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($invoice['client_ref']); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Email:</strong></div>
                                        <div class="col-sm-8">
                                            <a href="mailto:<?php echo htmlspecialchars($invoice['client_email']); ?>">
                                                <?php echo htmlspecialchars($invoice['client_email']); ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                
                                
                                <?php if ($invoice['visa_type']): ?>
                                <div class="info-row">
                                    <div class="row">
                                        <div class="col-sm-4"><strong>Visa Type:</strong></div>
                                        <div class="col-sm-8"><?php echo htmlspecialchars($invoice['visa_type']); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Actions & Payment History -->
                        <div class="col-lg-4">
                            <!-- Actions -->
                            <div class="content-card">
                                <h5 class="mb-3"><i class="fas fa-cogs me-2 text-primary"></i>Actions</h5>
                                
                                <div class="d-grid gap-2">
                                    <a href="admin-invoice-pdf.php?id=<?php echo $invoice['id']; ?>" class="btn btn-primary" target="_blank">
                                        <i class="fas fa-download me-2"></i>Download PDF
                                    </a>
                                    
                                    <?php if ($invoice['status'] === 'pending'): ?>
                                    <button class="btn btn-success" onclick="updateStatus('paid')">
                                        <i class="fas fa-check me-2"></i>Mark as Paid
                                    </button>
                                    
                                    <button class="btn btn-warning" onclick="updateStatus('overdue')">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Mark as Overdue
                                    </button>
                                    
                                    <button class="btn btn-secondary" onclick="updateStatus('cancelled')">
                                        <i class="fas fa-times me-2"></i>Cancel Invoice
                                    </button>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                        <i class="fas fa-sticky-note me-2"></i>Add Note
                                    </button>
                                    
                                    <a href="admin-client-view.php?id=<?php echo urlencode($invoice['client_ref']); ?>" class="btn btn-outline-secondary">
                                        <i class="fas fa-user me-2"></i>View Client
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Payment Summary -->
                            <div class="content-card">
                                <h5 class="mb-3"><i class="fas fa-calculator me-2 text-primary"></i>Payment Summary</h5>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Invoice Amount:</span>
                                        <strong><?php echo formatCurrencyByCode($invoice['amount'], $invoice['currency']); ?></strong>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <span>Total Paid:</span>
                                        <strong class="text-success"><?php echo formatCurrencyByCode($total_paid, $invoice['currency']); ?></strong>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="mb-0">
                                    <div class="d-flex justify-content-between">
                                        <span><strong>Balance:</strong></span>
                                        <strong class="<?php echo $balance_remaining > 0 ? 'text-danger' : 'text-success'; ?>">
                                            <?php echo formatCurrencyByCode($balance_remaining, $invoice['currency']); ?>
                                        </strong>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment History -->
                            <div class="content-card">
                                <h5 class="mb-3"><i class="fas fa-history me-2 text-primary"></i>Payment History</h5>
                                
                                <?php if (empty($payments)): ?>
                                    <p class="text-muted mb-0">No payments recorded for this invoice.</p>
                                <?php else: ?>
                                    <?php foreach ($payments as $payment): ?>
                                        <div class="payment-item payment-<?php echo $payment['payment_status']; ?>">
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
                                            
                                            <div class="small text-muted">
                                                <div><strong>Amount:</strong> <?php echo formatCurrencyByCode($payment['amount'], $payment['currency']); ?></div>
                                                <div><strong>Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></div>
                                                <div><strong>Date:</strong> <?php echo formatDate($payment['created_at'], 'M d, Y'); ?></div>
                                                <?php if ($payment['processed_by_name']): ?>
                                                    <div><strong>Processed by:</strong> <?php echo htmlspecialchars($payment['processed_by_name']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Note Modal -->
    <div class="modal fade" id="addNoteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Note to Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_note">
                        <div class="mb-3">
                            <label class="form-label">Note</label>
                            <textarea class="form-control" name="note" rows="4" required placeholder="Enter your note here..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function updateStatus(status) {
            if (confirm('Update invoice status to ' + status + '?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="status" value="${status}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <?php include 'includes/admin-sidebar-close.php'; ?>
</body>
</html>
