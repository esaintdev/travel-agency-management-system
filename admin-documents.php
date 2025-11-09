<?php
/**
 * Admin Documents Management - View all client documents
 */

require_once 'config.php';

// Check if user is logged in
requireLogin();

// Get all documents with client information
$documents = [];
$search_query = '';
$status_filter = '';

try {
    // Handle search and filters
    if (isset($_GET['search'])) {
        $search_query = sanitizeInput($_GET['search']);
    }
    if (isset($_GET['status'])) {
        $status_filter = sanitizeInput($_GET['status']);
    }
    
    // Build query
    $sql = "SELECT cd.*, c.full_name, c.reference_id, c.client_email, c.country 
            FROM client_documents cd 
            JOIN clients c ON cd.client_id = c.id 
            WHERE 1=1";
    $params = [];
    
    if (!empty($search_query)) {
        $sql .= " AND (c.full_name LIKE ? OR c.reference_id LIKE ? OR cd.document_name LIKE ?)";
        $search_param = "%$search_query%";
        $params[] = $search_param;
        $params[] = $search_param;
        $params[] = $search_param;
    }
    
    if (!empty($status_filter)) {
        $sql .= " AND cd.status = ?";
        $params[] = $status_filter;
    }
    
    $sql .= " ORDER BY cd.upload_date DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $documents = $stmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Admin documents error: " . $e->getMessage());
    $_SESSION['error'] = "Failed to load documents.";
}

// Handle document actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        $doc_id = intval($_POST['document_id']);
        $notes = sanitizeInput($_POST['notes'] ?? '');
        
        switch ($_POST['action']) {
            case 'approve':
                $stmt = $db->prepare("UPDATE client_documents SET status = 'approved', admin_notes = ? WHERE id = ?");
                $stmt->execute([$notes, $doc_id]);
                $_SESSION['success'] = "Document approved successfully!";
                break;
                
            case 'reject':
                $stmt = $db->prepare("UPDATE client_documents SET status = 'rejected', admin_notes = ? WHERE id = ?");
                $stmt->execute([$notes, $doc_id]);
                $_SESSION['success'] = "Document rejected successfully!";
                break;
                
            case 'pending':
                $stmt = $db->prepare("UPDATE client_documents SET status = 'pending', admin_notes = ? WHERE id = ?");
                $stmt->execute([$notes, $doc_id]);
                $_SESSION['success'] = "Document status updated to pending!";
                break;
        }
        
        // Refresh page to show updated status
        header('Location: admin-documents.php?' . http_build_query($_GET));
        exit();
        
    } catch (Exception $e) {
        error_log("Document action error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to update document status.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Document Management - M25 Travel & Tours Agency Admin</title>
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
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            z-index: 1000;
        }
        .sidebar .nav-link {
            color: #fff;
            padding: 15px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: #FEA116;
            color: #fff;
        }
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        .top-navbar {
            background: #13357B !important;
        }
        .content-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .document-card {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .document-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-color: #13357B;
        }
        .file-icon {
            font-size: 2rem;
            margin-right: 15px;
        }
        .status-badge {
            font-size: 0.85rem;
            padding: 6px 12px;
        }
        .action-buttons .btn {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/admin-sidebar.php'; ?>
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar">
                    <div class="container-fluid">
                        <span class="navbar-brand">Document Management</span>
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
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Search and Filter -->
                    <div class="content-card">
                        <h5 class="mb-3"><i class="fas fa-filter me-2"></i>Search & Filter Documents</h5>
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Search</label>
                                <input type="text" class="form-control" name="search" value="<?php echo htmlspecialchars($search_query); ?>" 
                                       placeholder="Search by client name, reference ID, or document name">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <select class="form-select" name="status">
                                    <option value="">All Status</option>
                                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Search
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Documents List -->
                    <div class="content-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0"><i class="fas fa-files me-2"></i>All Documents (<?php echo count($documents); ?>)</h5>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary active" onclick="showView('cards')">
                                    <i class="fas fa-th-large"></i> Cards
                                </button>
                                <button class="btn btn-outline-primary" onclick="showView('table')">
                                    <i class="fas fa-table"></i> Table
                                </button>
                            </div>
                        </div>
                        
                        <?php if (empty($documents)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No documents found</h5>
                                <p class="text-muted">No documents match your search criteria</p>
                            </div>
                        <?php else: ?>
                            
                            <!-- Cards View -->
                            <div id="cardsView">
                                <?php foreach ($documents as $doc): ?>
                                    <div class="document-card">
                                        <div class="row align-items-center">
                                            <div class="col-md-1 text-center">
                                                <?php
                                                $file_ext = strtolower(pathinfo($doc['original_filename'], PATHINFO_EXTENSION));
                                                $icon_class = in_array($file_ext, ['jpg', 'jpeg', 'png']) ? 'fa-image text-success' : 
                                                             ($file_ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file-alt text-info');
                                                ?>
                                                <i class="fas <?php echo $icon_class; ?> file-icon"></i>
                                            </div>
                                            <div class="col-md-4">
                                                <h6 class="mb-1"><?php echo htmlspecialchars($doc['document_name']); ?></h6>
                                                <small class="text-muted">
                                                    <strong><?php echo htmlspecialchars($doc['full_name']); ?></strong><br>
                                                    Ref: <?php echo htmlspecialchars($doc['reference_id']); ?><br>
                                                    Type: <?php echo ucfirst(str_replace('_', ' ', $doc['document_type'])); ?>
                                                </small>
                                            </div>
                                            <div class="col-md-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    <?php echo formatDate($doc['upload_date'], 'M d, Y'); ?><br>
                                                    <i class="fas fa-weight me-1"></i>
                                                    <?php echo number_format($doc['file_size'] / 1024, 1); ?> KB
                                                </small>
                                            </div>
                                            <div class="col-md-2">
                                                <span class="badge bg-<?php echo $doc['status'] == 'approved' ? 'success' : ($doc['status'] == 'rejected' ? 'danger' : 'warning'); ?> status-badge">
                                                    <i class="fas fa-<?php echo $doc['status'] == 'approved' ? 'check' : ($doc['status'] == 'rejected' ? 'times' : 'clock'); ?> me-1"></i>
                                                    <?php echo ucfirst($doc['status']); ?>
                                                </span>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="action-buttons">
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewDocument('<?php echo $doc['id']; ?>')">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewClient('<?php echo $doc['reference_id']; ?>')">
                                                        <i class="fas fa-user"></i> Client
                                                    </button>
                                                    <button class="btn btn-sm btn-outline-secondary" onclick="manageDocument('<?php echo $doc['id']; ?>', '<?php echo htmlspecialchars($doc['document_name']); ?>', '<?php echo $doc['status']; ?>', '<?php echo htmlspecialchars($doc['admin_notes']); ?>')">
                                                        <i class="fas fa-cog"></i> Manage
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($doc['admin_notes'])): ?>
                                            <div class="row mt-2">
                                                <div class="col-md-11 offset-md-1">
                                                    <div class="alert alert-info alert-sm mb-0">
                                                        <small><strong>Admin Notes:</strong> <?php echo htmlspecialchars($doc['admin_notes']); ?></small>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Table View -->
                            <div id="tableView" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Document</th>
                                                <th>Client</th>
                                                <th>Type</th>
                                                <th>Upload Date</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($documents as $doc): ?>
                                                <tr>
                                                    <td>
                                                        <?php
                                                        $file_ext = strtolower(pathinfo($doc['original_filename'], PATHINFO_EXTENSION));
                                                        $icon_class = in_array($file_ext, ['jpg', 'jpeg', 'png']) ? 'fa-image text-success' : 
                                                                     ($file_ext === 'pdf' ? 'fa-file-pdf text-danger' : 'fa-file-alt text-info');
                                                        ?>
                                                        <i class="fas <?php echo $icon_class; ?> me-2"></i>
                                                        <?php echo htmlspecialchars($doc['document_name']); ?>
                                                        <br><small class="text-muted"><?php echo number_format($doc['file_size'] / 1024, 1); ?> KB</small>
                                                    </td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($doc['full_name']); ?></strong>
                                                        <br><small class="text-muted"><?php echo htmlspecialchars($doc['reference_id']); ?></small>
                                                    </td>
                                                    <td><?php echo ucfirst(str_replace('_', ' ', $doc['document_type'])); ?></td>
                                                    <td><?php echo formatDate($doc['upload_date'], 'M d, Y H:i'); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $doc['status'] == 'approved' ? 'success' : ($doc['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                            <?php echo ucfirst($doc['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group btn-group-sm">
                                                            <button class="btn btn-outline-primary" onclick="viewDocument('<?php echo $doc['id']; ?>')">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                            <button class="btn btn-outline-info" onclick="viewClient('<?php echo $doc['reference_id']; ?>')">
                                                                <i class="fas fa-user"></i>
                                                            </button>
                                                            <button class="btn btn-outline-secondary" onclick="manageDocument('<?php echo $doc['id']; ?>', '<?php echo htmlspecialchars($doc['document_name']); ?>', '<?php echo $doc['status']; ?>', '<?php echo htmlspecialchars($doc['admin_notes']); ?>')">
                                                                <i class="fas fa-cog"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Document Management Modal -->
    <div class="modal fade" id="documentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Manage Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="document_id" id="modalDocumentId">
                        
                        <div class="mb-3">
                            <label class="form-label">Document Name</label>
                            <input type="text" class="form-control" id="modalDocumentName" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="action" id="modalStatus" required>
                                <option value="">Select Status</option>
                                <option value="pending">Pending Review</option>
                                <option value="approve">Approve</option>
                                <option value="reject">Reject</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea class="form-control" name="notes" id="modalNotes" rows="3" 
                                      placeholder="Add notes about this document..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // View toggle functions
        function showView(viewType) {
            const cardsView = document.getElementById('cardsView');
            const tableView = document.getElementById('tableView');
            const buttons = document.querySelectorAll('.btn-group .btn');
            
            buttons.forEach(btn => btn.classList.remove('active'));
            
            if (viewType === 'cards') {
                cardsView.style.display = 'block';
                tableView.style.display = 'none';
                buttons[0].classList.add('active');
            } else {
                cardsView.style.display = 'none';
                tableView.style.display = 'block';
                buttons[1].classList.add('active');
            }
        }
        
        // Document actions
        function viewDocument(docId) {
            window.open('admin-view-document.php?id=' + docId, '_blank');
        }
        
        function viewClient(referenceId) {
            window.location.href = 'admin-client-view.php?id=' + referenceId;
        }
        
        function manageDocument(docId, docName, status, notes) {
            document.getElementById('modalDocumentId').value = docId;
            document.getElementById('modalDocumentName').value = docName;
            document.getElementById('modalNotes').value = notes;
            
            // Set status based on current status
            const statusSelect = document.getElementById('modalStatus');
            statusSelect.value = '';
            
            const modal = new bootstrap.Modal(document.getElementById('documentModal'));
            modal.show();
        }
    </script>
    <?php include 'includes/admin-sidebar-close.php'; ?>
</body>
</html>
