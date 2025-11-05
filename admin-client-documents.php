<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Get client ID from URL
$client_reference = sanitizeInput($_GET['ref'] ?? '');
$client_data = null;
$client_documents = [];

if ($client_reference) {
    try {
        // Get client data
        $stmt = $db->prepare("SELECT * FROM clients WHERE reference_id = ?");
        $stmt->execute([$client_reference]);
        $client_data = $stmt->fetch();
        
        if ($client_data) {
            // Get client documents
            $stmt = $db->prepare("SELECT * FROM client_documents WHERE client_id = ? ORDER BY upload_date DESC");
            $stmt->execute([$client_data['id']]);
            $client_documents = $stmt->fetchAll();
        }
    } catch (Exception $e) {
        error_log("Client documents error: " . $e->getMessage());
        $_SESSION['error'] = "Failed to load client data.";
    }
}

// Handle document actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $client_data) {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'approve_document':
                    $doc_id = intval($_POST['document_id']);
                    $notes = sanitizeInput($_POST['notes'] ?? '');
                    
                    $stmt = $db->prepare("UPDATE client_documents SET status = 'approved', notes = ? WHERE id = ? AND client_id = ?");
                    $stmt->execute([$notes, $doc_id, $client_data['id']]);
                    
                    $_SESSION['success'] = "Document approved successfully!";
                    logActivity($_SESSION['admin_id'], $client_data['id'], 'Document Management', "Approved document ID: $doc_id", $db);
                    break;
                    
                case 'reject_document':
                    $doc_id = intval($_POST['document_id']);
                    $notes = sanitizeInput($_POST['notes'] ?? '');
                    
                    if (empty($notes)) {
                        throw new Exception("Please provide a reason for rejection.");
                    }
                    
                    $stmt = $db->prepare("UPDATE client_documents SET status = 'rejected', notes = ? WHERE id = ? AND client_id = ?");
                    $stmt->execute([$notes, $doc_id, $client_data['id']]);
                    
                    $_SESSION['success'] = "Document rejected with feedback.";
                    logActivity($_SESSION['admin_id'], $client_data['id'], 'Document Management', "Rejected document ID: $doc_id", $db);
                    break;
                    
                case 'delete_document':
                    $doc_id = intval($_POST['document_id']);
                    
                    // Get document info for file deletion
                    $stmt = $db->prepare("SELECT file_path FROM client_documents WHERE id = ? AND client_id = ?");
                    $stmt->execute([$doc_id, $client_data['id']]);
                    $doc_info = $stmt->fetch();
                    
                    if ($doc_info) {
                        // Delete file from filesystem
                        if (file_exists($doc_info['file_path'])) {
                            unlink($doc_info['file_path']);
                        }
                        
                        // Delete from database
                        $stmt = $db->prepare("DELETE FROM client_documents WHERE id = ? AND client_id = ?");
                        $stmt->execute([$doc_id, $client_data['id']]);
                        
                        $_SESSION['success'] = "Document deleted successfully!";
                        logActivity($_SESSION['admin_id'], $client_data['id'], 'Document Management', "Deleted document ID: $doc_id", $db);
                    }
                    break;
                    
                case 'update_client_status':
                    $documents_verified = isset($_POST['documents_verified']) ? 1 : 0;
                    $documents_notes = sanitizeInput($_POST['documents_notes'] ?? '');
                    
                    $stmt = $db->prepare("UPDATE clients SET documents_verified = ?, documents_notes = ? WHERE id = ?");
                    $stmt->execute([$documents_verified, $documents_notes, $client_data['id']]);
                    
                    $_SESSION['success'] = "Client document status updated!";
                    logActivity($_SESSION['admin_id'], $client_data['id'], 'Document Management', "Updated client document status", $db);
                    break;
            }
        }
        
    } catch (Exception $e) {
        error_log("Document management error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: admin-client-documents.php?ref=" . urlencode($client_reference));
    exit();
}

if (!$client_data) {
    $_SESSION['error'] = "Client not found.";
    header('Location: admin-clients.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Client Documents - M25 Travel & Tour Agency</title>
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
        .document-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .top-navbar {
            background: #13357B !important;
        }
        .client-header {
            background: linear-gradient(135deg, #13357B, #FEA116);
            color: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
        }
        .document-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
        }
        .document-preview {
            max-width: 100px;
            max-height: 100px;
            border-radius: 5px;
        }
        .status-pending { color: #ffc107; }
        .status-approved { color: #28a745; }
        .status-rejected { color: #dc3545; }
        .file-icon {
            font-size: 48px;
            color: #6c757d;
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
                    <a class="nav-link" href="admin-email-templates.php">
                        <i class="fas fa-envelope me-2"></i>Email Templates
                    </a>
                    <a class="nav-link" href="admin-users.php">
                        <i class="fas fa-user-cog me-2"></i>Admin Users
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
                        <span class="navbar-brand">Client Documents</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i><?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Content -->
                <div class="container-fluid p-4">
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
                    
                    <!-- Client Header -->
                    <div class="client-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($client_data['full_name']); ?></h4>
                                <p class="mb-2">
                                    <strong>Reference ID:</strong> <?php echo htmlspecialchars($client_data['reference_id']); ?> | 
                                    <strong>Email:</strong> <?php echo htmlspecialchars($client_data['client_email']); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Visa Type:</strong> <?php echo htmlspecialchars($client_data['visa_type']); ?> | 
                                    <strong>Country:</strong> <?php echo htmlspecialchars($client_data['country']); ?> | 
                                    <strong>Status:</strong> <?php echo htmlspecialchars($client_data['status']); ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <a href="admin-clients.php" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Clients
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <!-- Documents List -->
                        <div class="col-md-8">
                            <div class="document-card">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h4><i class="fas fa-files me-2"></i>Uploaded Documents (<?php echo count($client_documents); ?>)</h4>
                                    <a href="client-documents.php?ref=<?php echo urlencode($client_data['reference_id']); ?>&email=<?php echo urlencode($client_data['client_email']); ?>" 
                                       class="btn btn-primary" target="_blank">
                                        <i class="fas fa-external-link-alt me-2"></i>Client View
                                    </a>
                                </div>
                                
                                <?php if (empty($client_documents)): ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">No documents uploaded yet</h5>
                                        <p class="text-muted">Client hasn't uploaded any documents</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($client_documents as $doc): ?>
                                        <div class="document-item">
                                            <div class="row">
                                                <div class="col-md-2 text-center">
                                                    <?php
                                                    $file_ext = strtolower(pathinfo($doc['original_filename'], PATHINFO_EXTENSION));
                                                    if (in_array($file_ext, ['jpg', 'jpeg', 'png'])):
                                                    ?>
                                                        <img src="<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                                             class="document-preview" alt="Document preview">
                                                    <?php else: ?>
                                                        <i class="fas fa-<?php echo $file_ext === 'pdf' ? 'file-pdf' : 'file-alt'; ?> file-icon"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-6">
                                                    <h6 class="mb-2"><?php echo htmlspecialchars($doc['document_name']); ?></h6>
                                                    <p class="mb-1">
                                                        <span class="badge bg-info"><?php echo ucfirst(str_replace('_', ' ', $doc['document_type'])); ?></span>
                                                        <span class="badge bg-<?php echo $doc['status'] == 'approved' ? 'success' : ($doc['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                            <?php echo ucfirst($doc['status']); ?>
                                                        </span>
                                                    </p>
                                                    <small class="text-muted">
                                                        <i class="fas fa-file me-1"></i><?php echo htmlspecialchars($doc['original_filename']); ?> |
                                                        <i class="fas fa-weight me-1"></i><?php echo number_format($doc['file_size'] / 1024, 1); ?> KB |
                                                        <i class="fas fa-clock me-1"></i><?php echo formatDate($doc['upload_date'], 'M d, Y H:i'); ?>
                                                    </small>
                                                    <?php if ($doc['notes']): ?>
                                                        <div class="mt-2">
                                                            <small class="text-muted">
                                                                <strong>Notes:</strong> <?php echo htmlspecialchars($doc['notes']); ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <div class="btn-group mb-2">
                                                        <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                        <a href="<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                                           class="btn btn-sm btn-outline-secondary" download>
                                                            <i class="fas fa-download"></i> Download
                                                        </a>
                                                    </div>
                                                    <br>
                                                    <div class="btn-group">
                                                        <?php if ($doc['status'] !== 'approved'): ?>
                                                            <button class="btn btn-sm btn-success" onclick="approveDocument(<?php echo $doc['id']; ?>)">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                        <?php endif; ?>
                                                        <?php if ($doc['status'] !== 'rejected'): ?>
                                                            <button class="btn btn-sm btn-warning" onclick="rejectDocument(<?php echo $doc['id']; ?>)">
                                                                <i class="fas fa-times"></i> Reject
                                                            </button>
                                                        <?php endif; ?>
                                                        <button class="btn btn-sm btn-danger" onclick="deleteDocument(<?php echo $doc['id']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Document Status & Actions -->
                        <div class="col-md-4">
                            <div class="document-card">
                                <h5><i class="fas fa-clipboard-check me-2"></i>Document Status</h5>
                                
                                <form method="POST" action="">
                                    <input type="hidden" name="action" value="update_client_status">
                                    
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="documents_verified" 
                                                   id="documentsVerified" <?php echo $client_data['documents_verified'] ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="documentsVerified">
                                                <strong>Documents Verified</strong>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Internal Notes</label>
                                        <textarea class="form-control" name="documents_notes" rows="4" 
                                                  placeholder="Add notes about document verification..."><?php echo htmlspecialchars($client_data['documents_notes'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="fas fa-save me-2"></i>Update Status
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Document Statistics -->
                            <div class="document-card">
                                <h5><i class="fas fa-chart-pie me-2"></i>Statistics</h5>
                                
                                <?php
                                $total_docs = count($client_documents);
                                $approved_docs = count(array_filter($client_documents, function($doc) { return $doc['status'] === 'approved'; }));
                                $rejected_docs = count(array_filter($client_documents, function($doc) { return $doc['status'] === 'rejected'; }));
                                $pending_docs = count(array_filter($client_documents, function($doc) { return $doc['status'] === 'pending'; }));
                                ?>
                                
                                <div class="row text-center">
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h4 class="text-primary mb-1"><?php echo $total_docs; ?></h4>
                                            <small class="text-muted">Total</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="border rounded p-3">
                                            <h4 class="text-success mb-1"><?php echo $approved_docs; ?></h4>
                                            <small class="text-muted">Approved</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-3">
                                            <h4 class="text-warning mb-1"><?php echo $pending_docs; ?></h4>
                                            <small class="text-muted">Pending</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="border rounded p-3">
                                            <h4 class="text-danger mb-1"><?php echo $rejected_docs; ?></h4>
                                            <small class="text-muted">Rejected</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Document Action Modals -->
    <div class="modal fade" id="approveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="approve_document">
                        <input type="hidden" name="document_id" id="approveDocId">
                        
                        <div class="mb-3">
                            <label class="form-label">Approval Notes (Optional)</label>
                            <textarea class="form-control" name="notes" rows="3" 
                                      placeholder="Add any notes about the approval..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Approve Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="">
                    <div class="modal-header">
                        <h5 class="modal-title">Reject Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject_document">
                        <input type="hidden" name="document_id" id="rejectDocId">
                        
                        <div class="mb-3">
                            <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="notes" rows="3" required
                                      placeholder="Please provide a clear reason for rejection..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning">Reject Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function approveDocument(docId) {
            document.getElementById('approveDocId').value = docId;
            new bootstrap.Modal(document.getElementById('approveModal')).show();
        }
        
        function rejectDocument(docId) {
            document.getElementById('rejectDocId').value = docId;
            new bootstrap.Modal(document.getElementById('rejectModal')).show();
        }
        
        function deleteDocument(docId) {
            if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_document">
                    <input type="hidden" name="document_id" value="${docId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
