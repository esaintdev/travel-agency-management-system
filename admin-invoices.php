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
                case 'create_invoice':
                    $client_id = intval($_POST['client_id']);
                    $invoice_type = sanitizeInput($_POST['invoice_type']);
                    $description = sanitizeInput($_POST['description']);
                    $amount = floatval($_POST['amount']);
                    $currency = sanitizeInput($_POST['currency']);
                    $due_date = $_POST['due_date'];
                    
                    // Get client reference ID
                    $stmt = $db->prepare("SELECT reference_id FROM clients WHERE id = ?");
                    $stmt->execute([$client_id]);
                    $client = $stmt->fetch();
                    
                    if (!$client) {
                        throw new Exception("Client not found.");
                    }
                    
                    // Generate invoice number
                    $stmt = $db->prepare("SELECT COUNT(*) as count FROM invoices WHERE YEAR(created_at) = YEAR(CURDATE())");
                    $stmt->execute();
                    $count = $stmt->fetch()['count'] + 1;
                    $invoice_number = 'INV-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
                    
                    // Create invoice
                    $stmt = $db->prepare("INSERT INTO invoices (invoice_number, client_id, client_reference_id, invoice_type, description, amount, currency, due_date, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$invoice_number, $client_id, $client['reference_id'], $invoice_type, $description, $amount, $currency, $due_date, $_SESSION['admin_id']]);
                    
                    // Update client balance
                    $stmt = $db->prepare("UPDATE clients SET balance_due = balance_due + ?, total_amount_due = total_amount_due + ? WHERE id = ?");
                    $stmt->execute([$amount, $amount, $client_id]);
                    
                    // Log activity
                    logActivity($_SESSION['admin_id'], $client_id, 'Invoice Created', "Created invoice $invoice_number for " . formatCurrencyByCode($amount, $currency), $db);
                    
                    // Create notification for client
                    $invoice_id = $db->lastInsertId();
                    createInvoiceNotification($db, $client_id, $invoice_id, $invoice_number, formatCurrencyByCode($amount, $currency));
                    
                    $_SESSION['success'] = "Invoice $invoice_number created successfully!";
                    break;
                    
                case 'update_status':
                    $invoice_id = intval($_POST['invoice_id']);
                    $status = sanitizeInput($_POST['status']);
                    
                    $stmt = $db->prepare("UPDATE invoices SET status = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$status, $invoice_id]);
                    
                    $_SESSION['success'] = "Invoice status updated successfully!";
                    break;
            }
        }
    } catch (Exception $e) {
        error_log("Invoice management error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: admin-invoices.php');
    exit();
}

// Get invoices with pagination
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
        $where_conditions[] = "(i.invoice_number LIKE ? OR c.full_name LIKE ? OR c.reference_id LIKE ?)";
        $search_param = "%{$search}%";
        $params = array_merge($params, [$search_param, $search_param, $search_param]);
    }
    
    if (!empty($status_filter)) {
        $where_conditions[] = "i.status = ?";
        $params[] = $status_filter;
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM invoices i JOIN clients c ON i.client_id = c.id {$where_clause}";
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($params);
    $total_invoices = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_invoices / $per_page);
    
    // Get invoices
    $sql = "SELECT i.*, c.full_name, c.reference_id as client_ref 
            FROM invoices i 
            JOIN clients c ON i.client_id = c.id 
            {$where_clause} 
            ORDER BY i.created_at DESC 
            LIMIT {$per_page} OFFSET {$offset}";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $invoices = $stmt->fetchAll();
    
    // Get clients for dropdown
    $clients_stmt = $db->prepare("SELECT id, reference_id, full_name FROM clients WHERE status = 'Active' ORDER BY full_name");
    $clients_stmt->execute();
    $clients = $clients_stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Invoice fetch error: " . $e->getMessage());
    $invoices = [];
    $clients = [];
    $total_invoices = 0;
    $total_pages = 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice Management - M25 Travel & Tour Agency</title>
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
        .status-pending { color: #ffc107; }
        .status-paid { color: #28a745; }
        .status-overdue { color: #dc3545; }
        .status-cancelled { color: #6c757d; }
    </style>
</head>

<body>
    <?php include 'includes/admin-sidebar.php'; ?>
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar">
                    <div class="container-fluid">
                        <span class="navbar-brand">Invoice Management</span>
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
                    
                    <!-- Search Form -->
                    <div class="search-form">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Invoice Management</h5>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">
                                <i class="fas fa-plus me-2"></i>Create Invoice
                            </button>
                        </div>
                        
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Search Invoices</label>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Invoice number, client name, or reference ID">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Status Filter</label>
                                <select class="form-select" name="status">
                                    <option value="">All Statuses</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="paid" <?php echo $status_filter === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                    <option value="overdue" <?php echo $status_filter === 'overdue' ? 'selected' : ''; ?>>Overdue</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
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
                                    <a href="admin-invoices.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i>Clear
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Invoices Table -->
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">
                                Invoices 
                                <span class="badge bg-primary"><?php echo number_format($total_invoices); ?> total</span>
                                <?php if (!empty($search) || !empty($status_filter)): ?>
                                    <span class="badge bg-info">Filtered</span>
                                <?php endif; ?>
                            </h5>
                        </div>
                        
                        <?php if (empty($invoices)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No invoices found</h5>
                                <?php if (!empty($search) || !empty($status_filter)): ?>
                                    <p class="text-muted">Try adjusting your search criteria</p>
                                    <a href="admin-invoices.php" class="btn btn-primary">View All Invoices</a>
                                <?php else: ?>
                                    <p class="text-muted">No invoices have been created yet</p>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createInvoiceModal">Create First Invoice</button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Client</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Due Date</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($invoices as $invoice): ?>
                                            <tr>
                                                <td>
                                                    <strong class="text-primary"><?php echo htmlspecialchars($invoice['invoice_number']); ?></strong>
                                                </td>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($invoice['full_name']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($invoice['client_ref']); ?></small>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $invoice['invoice_type'])); ?></span>
                                                </td>
                                                <td>
                                                    <strong><?php echo formatCurrencyByCode($invoice['amount'], $invoice['currency']); ?></strong>
                                                </td>
                                                <td>
                                                    <?php 
                                                    $due_date = new DateTime($invoice['due_date']);
                                                    $today = new DateTime();
                                                    $is_overdue = $due_date < $today && $invoice['status'] === 'pending';
                                                    ?>
                                                    <span class="<?php echo $is_overdue ? 'text-danger' : ''; ?>">
                                                        <?php echo formatDate($invoice['due_date'], 'M d, Y'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php 
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
                                                </td>
                                                <td>
                                                    <small><?php echo formatDate($invoice['created_at'], 'M d, Y'); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="admin-invoice-view.php?id=<?php echo $invoice['id']; ?>" 
                                                           class="btn btn-outline-primary" title="View Invoice (ID: <?php echo $invoice['id']; ?>)">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <?php if ($invoice['status'] === 'pending'): ?>
                                                        <button class="btn btn-outline-success" title="Mark as Paid"
                                                                onclick="updateInvoiceStatus(<?php echo $invoice['id']; ?>, 'paid')">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                        <?php endif; ?>
                                                        <a href="admin-invoice-pdf.php?id=<?php echo $invoice['id']; ?>" 
                                                           class="btn btn-outline-info" title="Download PDF" target="_blank">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Invoice pagination">
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

    <!-- Create Invoice Modal -->
    <div class="modal fade" id="createInvoiceModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Invoice</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create_invoice">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Client *</label>
                                    <select class="form-select" name="client_id" required>
                                        <option value="">Select Client</option>
                                        <?php foreach ($clients as $client): ?>
                                            <option value="<?php echo $client['id']; ?>">
                                                <?php echo htmlspecialchars($client['full_name']); ?> (<?php echo htmlspecialchars($client['reference_id']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Invoice Type *</label>
                                    <select class="form-select" name="invoice_type" required>
                                        <option value="deposit">Deposit</option>
                                        <option value="balance">Balance Payment</option>
                                        <option value="service_fee">Service Fee</option>
                                        <option value="additional">Additional Charges</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Description *</label>
                            <textarea class="form-control" name="description" rows="3" required placeholder="Enter invoice description"></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Amount *</label>
                                    <input type="number" class="form-control" name="amount" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Currency</label>
                                    <select class="form-select" name="currency">
                                        <option value="USD">USD</option>
                                        <option value="GHS">GHS</option>
                                        <option value="EUR">EUR</option>
                                        <option value="GBP">GBP</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Due Date *</label>
                                    <input type="date" class="form-control" name="due_date" required value="<?php echo date('Y-m-d', strtotime('+30 days')); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function updateInvoiceStatus(invoiceId, status) {
            if (confirm('Update invoice status to ' + status + '?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="update_status">
                    <input type="hidden" name="invoice_id" value="${invoiceId}">
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
