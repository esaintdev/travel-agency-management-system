<?php
/**
 * Client Documents Page - Integrated with authentication system
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

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $client_id && isset($_POST['upload_document'])) {
    try {
        $document_type = sanitizeInput($_POST['document_type']);
        $document_name = sanitizeInput($_POST['document_name']);
        
        if (empty($document_type) || empty($document_name)) {
            throw new Exception("Please fill in all required fields.");
        }
        
        if (!isset($_FILES['document_file']) || $_FILES['document_file']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Please select a file to upload.");
        }
        
        $file = $_FILES['document_file'];
        $file_size = $file['size'];
        $file_name = $file['name'];
        $file_tmp = $file['tmp_name'];
        $file_type = $file['type'];
        
        // Validate file size (5MB max)
        if ($file_size > MAX_FILE_SIZE) {
            throw new Exception("File size too large. Maximum size is 5MB.");
        }
        
        // Validate file extension
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
            throw new Exception("Invalid file type. Allowed types: " . implode(', ', ALLOWED_EXTENSIONS));
        }
        
        // Create upload directory if it doesn't exist
        $upload_dir = UPLOAD_DIR . 'clients/' . $client_data['reference_id'] . '/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // Generate unique filename
        $new_filename = $document_type . '_' . time() . '_' . uniqid() . '.' . $file_extension;
        $file_path = $upload_dir . $new_filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file_tmp, $file_path)) {
            throw new Exception("Failed to upload file. Please try again.");
        }
        
        // Save to database
        $stmt = $db->prepare("INSERT INTO client_documents (client_id, document_type, document_name, original_filename, file_path, file_size, mime_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $client_id,
            $document_type,
            $document_name,
            $file_name,
            $file_path,
            $file_size,
            $file_type
        ]);
        
        // Update client documents status
        $stmt = $db->prepare("UPDATE clients SET documents_uploaded = TRUE WHERE id = ?");
        $stmt->execute([$client_id]);
        
        $_SESSION['success'] = "Document uploaded successfully!";
        
    } catch (Exception $e) {
        error_log("Document upload error: " . $e->getMessage());
        $_SESSION['error'] = $e->getMessage();
    }
    
    header("Location: client-documents.php?ref=" . urlencode($reference_id) . "&email=" . urlencode($email));
    exit();
}

// Get document requirements for client's visa type
$document_requirements = [];
$uploaded_documents = [];

if ($client_data) {
    try {
        // Get requirements
        $stmt = $db->prepare("SELECT * FROM document_requirements WHERE visa_type = ? ORDER BY is_required DESC, document_type");
        $stmt->execute([$client_data['visa_type']]);
        $document_requirements = $stmt->fetchAll();
        
        // Get uploaded documents
        $stmt = $db->prepare("SELECT * FROM client_documents WHERE client_id = ? ORDER BY upload_date DESC");
        $stmt->execute([$client_id]);
        $uploaded_documents = $stmt->fetchAll();
        
    } catch (Exception $e) {
        error_log("Document requirements error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Document Upload - M25 Travel & Tour Agency</title>
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
        .document-container {
            background: #f8f9fa;
            min-height: 100vh;
            padding: 50px 0;
        }
        .document-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .client-info {
            background: linear-gradient(135deg, #13357B, #FEA116);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        .requirement-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
        }
        .requirement-required {
            border-left: 4px solid #dc3545;
        }
        .requirement-optional {
            border-left: 4px solid #28a745;
        }
        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        .upload-area:hover {
            border-color: #FEA116;
            background: #fff;
        }
        .upload-area.dragover {
            border-color: #13357B;
            background: #e3f2fd;
        }
        .document-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
            background: white;
        }
        .status-pending { color: #ffc107; }
        .status-approved { color: #28a745; }
        .status-rejected { color: #dc3545; }
        .file-icon {
            font-size: 24px;
            margin-right: 10px;
        }
        .document-card-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 15px;
            background: #fff;
            transition: all 0.3s ease;
            height: 100%;
        }
        .document-card-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .document-header {
            text-align: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #f8f9fa;
        }
        .document-meta {
            padding-top: 15px;
        }
        .document-item-list {
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            background: #fff;
            transition: all 0.3s ease;
        }
        .document-item-list:hover {
            background: #f8f9fa;
            border-color: #13357B;
        }
        .document-notes {
            background: #f8f9fa;
            border-radius: 5px;
            padding: 8px;
        }
        .navbar-brand h1 {
            font-size: 1.5rem;
        }
        @media (max-width: 768px) {
            .navbar-brand h1 {
                font-size: 1.2rem;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation Start -->
    <div class="container-fluid bg-primary px-5 d-none d-lg-block">
        <div class="row gx-0 align-items-center">
            <div class="col-lg-5 text-center text-lg-start mb-lg-0">
                <div class="d-flex">
                    <a href="mailto:info@m25travelagency.com" class="text-muted me-4"><i class="fas fa-envelope text-secondary me-2"></i>info@m25travelagency.com</a>
                    <a href="tel:+233592605752" class="text-muted me-0"><i class="fas fa-phone-alt text-secondary me-2"></i>+233 592 605 752</a>
                </div>
            </div>
            <div class="col-lg-7 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <small class="me-3 text-light"><i class="fa fa-user me-1"></i>Welcome, <?php echo htmlspecialchars($client_data['full_name']); ?></small>
                    <a href="client-logout.php" class="btn btn-sm btn-outline-light"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
                </div>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top px-4 px-lg-5 py-lg-0">
        <a href="/" class="navbar-brand p-0">
            <h1 class="text-primary m-0"><i class="fas fa-globe-americas me-3"></i>M25 Travel & Tours Agency</h1>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
            <span class="fa fa-bars"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarCollapse">
            <div class="navbar-nav ms-auto py-0">
                <a href="client-dashboard.php" class="nav-item nav-link"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
                <a href="client-documents.php" class="nav-item nav-link active"><i class="fas fa-file-upload me-1"></i>Documents</a>
                <a href="client-profile.php" class="nav-item nav-link"><i class="fas fa-user me-1"></i>Profile</a>
                <a href="client-status.php" class="nav-item nav-link"><i class="fas fa-info-circle me-1"></i>Application Status</a>
                <div class="nav-item dropdown">
                    <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-cog me-1"></i>Settings</a>
                    <div class="dropdown-menu m-0">
                        <a href="client-profile-edit.php" class="dropdown-item">Edit Profile</a>
                        <a href="client-change-password.php" class="dropdown-item">Change Password</a>
                        <hr class="dropdown-divider">
                        <a href="client-logout.php" class="dropdown-item">Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- Navigation End -->

    <div class="document-container">
        <div class="container">
            <?php if (isset($error_message)): ?>
                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="alert alert-danger text-center">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <h4>Access Denied</h4>
                            <p><?php echo $error_message; ?></p>
                            <a href="index.html" class="btn btn-primary">Back to Home</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                
                <!-- Client Information -->
                <div class="client-info">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3><i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($client_data['full_name']); ?></h3>
                            <p class="mb-2">
                                <strong>Reference ID:</strong> <?php echo htmlspecialchars($client_data['reference_id']); ?>
                            </p>
                            <p class="mb-0">
                                <strong>Visa Type:</strong> <?php echo htmlspecialchars($client_data['visa_type']); ?> | 
                                <strong>Country:</strong> <?php echo htmlspecialchars($client_data['country']); ?>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-file-upload fa-3x"></i>
                        </div>
                    </div>
                </div>
                
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
                
                <div class="row">
                    <!-- Document Requirements -->
                    <div class="col-md-6">
                        <div class="document-card">
                            <h4 class="mb-4"><i class="fas fa-list-check me-2"></i>Required Documents</h4>
                            
                            <?php if (empty($document_requirements)): ?>
                                <p class="text-muted">No specific requirements found for your visa type.</p>
                            <?php else: ?>
                                <?php foreach ($document_requirements as $req): ?>
                                    <div class="requirement-item <?php echo $req['is_required'] ? 'requirement-required' : 'requirement-optional'; ?>">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-2">
                                                    <?php echo ucfirst(str_replace('_', ' ', $req['document_type'])); ?>
                                                    <?php if ($req['is_required']): ?>
                                                        <span class="badge bg-danger ms-2">Required</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-success ms-2">Optional</span>
                                                    <?php endif; ?>
                                                </h6>
                                                <p class="mb-0 text-muted"><?php echo htmlspecialchars($req['description']); ?></p>
                                            </div>
                                            <button class="btn btn-sm btn-primary" onclick="uploadDocument('<?php echo $req['document_type']; ?>')">
                                                <i class="fas fa-upload"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Upload Form & Uploaded Documents -->
                    <div class="col-md-6">
                        <!-- Upload Form -->
                        <div class="document-card">
                            <h4 class="mb-4"><i class="fas fa-cloud-upload-alt me-2"></i>Upload Document</h4>
                            
                            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                                <div class="mb-3">
                                    <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="document_type" id="documentType" required>
                                        <option value="">Select Document Type</option>
                                        <option value="passport">Passport</option>
                                        <option value="photo">Passport Photo</option>
                                        <option value="bank_statement">Bank Statement</option>
                                        <option value="employment_letter">Employment Letter</option>
                                        <option value="invitation_letter">Invitation Letter</option>
                                        <option value="travel_itinerary">Travel Itinerary</option>
                                        <option value="accommodation_proof">Accommodation Proof</option>
                                        <option value="financial_proof">Financial Proof</option>
                                        <option value="medical_certificate">Medical Certificate</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Document Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="document_name" required 
                                           placeholder="e.g., Passport Copy, Bank Statement March 2024">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Select File <span class="text-danger">*</span></label>
                                    <div class="upload-area" id="uploadArea">
                                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                        <h5>Drag & Drop your file here</h5>
                                        <p class="text-muted">or click to browse</p>
                                        <input type="file" class="form-control" name="document_file" id="documentFile" required 
                                               accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" style="display: none;">
                                        <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('documentFile').click()">
                                            <i class="fas fa-folder-open me-2"></i>Browse Files
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">
                                        Allowed formats: JPG, PNG, PDF, DOC, DOCX | Max size: 5MB
                                    </small>
                                </div>
                                
                                <div id="filePreview" class="mb-3" style="display: none;">
                                    <div class="alert alert-info">
                                        <i class="fas fa-file me-2"></i>
                                        <span id="fileName"></span>
                                        <span id="fileSize" class="text-muted ms-2"></span>
                                    </div>
                                </div>
                                
                                <button type="submit" name="upload_document" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-upload me-2"></i>Upload Document
                                </button>
                            </form>
                        </div>
                        
                        <!-- Uploaded Documents -->
                        <div class="document-card">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="mb-0"><i class="fas fa-files me-2"></i>Your Documents (<?php echo count($uploaded_documents); ?>)</h4>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary active" onclick="showView('grid')">
                                        <i class="fas fa-th-large"></i>
                                    </button>
                                    <button class="btn btn-outline-primary" onclick="showView('list')">
                                        <i class="fas fa-list"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <?php if (empty($uploaded_documents)): ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No documents uploaded yet</h5>
                                    <p class="text-muted">Upload your first document using the form above</p>
                                </div>
                            <?php else: ?>
                                <div id="gridView" class="document-grid">
                                    <div class="row">
                                        <?php foreach ($uploaded_documents as $doc): ?>
                                            <div class="col-md-6 mb-3">
                                                <div class="document-card-item">
                                                    <div class="document-header">
                                                        <?php
                                                        $file_ext = strtolower(pathinfo($doc['original_filename'], PATHINFO_EXTENSION));
                                                        $icon_class = in_array($file_ext, ['jpg', 'jpeg', 'png']) ? 'fa-image' : 
                                                                     ($file_ext === 'pdf' ? 'fa-file-pdf' : 'fa-file-alt');
                                                        $icon_color = in_array($file_ext, ['jpg', 'jpeg', 'png']) ? 'text-success' : 
                                                                     ($file_ext === 'pdf' ? 'text-danger' : 'text-info');
                                                        ?>
                                                        <i class="fas <?php echo $icon_class; ?> fa-2x <?php echo $icon_color; ?> mb-2"></i>
                                                        <h6 class="mb-1"><?php echo htmlspecialchars($doc['document_name']); ?></h6>
                                                        <small class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $doc['document_type'])); ?></small>
                                                    </div>
                                                    
                                                    <div class="document-meta">
                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar me-1"></i>
                                                                <?php echo formatDate($doc['upload_date'], 'M d, Y'); ?>
                                                            </small>
                                                            <small class="text-muted">
                                                                <i class="fas fa-weight me-1"></i>
                                                                <?php echo number_format($doc['file_size'] / 1024, 1); ?> KB
                                                            </small>
                                                        </div>
                                                        
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="badge bg-<?php echo $doc['status'] == 'approved' ? 'success' : ($doc['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                                <i class="fas fa-<?php echo $doc['status'] == 'approved' ? 'check' : ($doc['status'] == 'rejected' ? 'times' : 'clock'); ?> me-1"></i>
                                                                <?php echo ucfirst($doc['status']); ?>
                                                            </span>
                                                            <div class="btn-group btn-group-sm">
                                                                <button class="btn btn-outline-primary" onclick="viewDocument('<?php echo $doc['id']; ?>')" title="View">
                                                                    <i class="fas fa-eye"></i>
                                                                </button>
                                                                <button class="btn btn-outline-success" onclick="downloadDocument('<?php echo $doc['id']; ?>')" title="Download">
                                                                    <i class="fas fa-download"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <?php if (!empty($doc['admin_notes'])): ?>
                                                        <div class="document-notes mt-2">
                                                            <small class="text-muted">
                                                                <i class="fas fa-sticky-note me-1"></i>
                                                                <strong>Admin Notes:</strong> <?php echo htmlspecialchars($doc['admin_notes']); ?>
                                                            </small>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <div id="listView" class="document-list" style="display: none;">
                                    <?php foreach ($uploaded_documents as $doc): ?>
                                        <div class="document-item-list">
                                            <div class="row align-items-center">
                                                <div class="col-md-1 text-center">
                                                    <?php
                                                    $file_ext = strtolower(pathinfo($doc['original_filename'], PATHINFO_EXTENSION));
                                                    $icon_class = in_array($file_ext, ['jpg', 'jpeg', 'png']) ? 'fa-image' : 
                                                                 ($file_ext === 'pdf' ? 'fa-file-pdf' : 'fa-file-alt');
                                                    $icon_color = in_array($file_ext, ['jpg', 'jpeg', 'png']) ? 'text-success' : 
                                                                 ($file_ext === 'pdf' ? 'text-danger' : 'text-info');
                                                    ?>
                                                    <i class="fas <?php echo $icon_class; ?> fa-2x <?php echo $icon_color; ?>"></i>
                                                </div>
                                                <div class="col-md-5">
                                                    <h6 class="mb-1"><?php echo htmlspecialchars($doc['document_name']); ?></h6>
                                                    <small class="text-muted">
                                                        <?php echo ucfirst(str_replace('_', ' ', $doc['document_type'])); ?> | 
                                                        <?php echo number_format($doc['file_size'] / 1024, 1); ?> KB
                                                    </small>
                                                </div>
                                                <div class="col-md-3">
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar me-1"></i>
                                                        <?php echo formatDate($doc['upload_date'], 'M d, Y H:i'); ?>
                                                    </small>
                                                </div>
                                                <div class="col-md-2">
                                                    <span class="badge bg-<?php echo $doc['status'] == 'approved' ? 'success' : ($doc['status'] == 'rejected' ? 'danger' : 'warning'); ?>">
                                                        <i class="fas fa-<?php echo $doc['status'] == 'approved' ? 'check' : ($doc['status'] == 'rejected' ? 'times' : 'clock'); ?> me-1"></i>
                                                        <?php echo ucfirst($doc['status']); ?>
                                                    </span>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" onclick="viewDocument('<?php echo $doc['id']; ?>')" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-success" onclick="downloadDocument('<?php echo $doc['id']; ?>')" title="Download">
                                                            <i class="fas fa-download"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php if (!empty($doc['admin_notes'])): ?>
                                                <div class="row mt-2">
                                                    <div class="col-md-11 offset-md-1">
                                                        <small class="text-muted">
                                                            <i class="fas fa-sticky-note me-1"></i>
                                                            <strong>Admin Notes:</strong> <?php echo htmlspecialchars($doc['admin_notes']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Help Section -->
                <div class="document-card">
                    <h4 class="mb-4"><i class="fas fa-question-circle me-2"></i>Need Help?</h4>
                    <div class="row">
                        <div class="col-md-4">
                            <h6><i class="fas fa-file-alt me-2"></i>Document Guidelines</h6>
                            <ul class="list-unstyled">
                                <li>• Ensure documents are clear and readable</li>
                                <li>• Use high-quality scans or photos</li>
                                <li>• Documents should be recent and valid</li>
                                <li>• File size should not exceed 5MB</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-shield-alt me-2"></i>Security</h6>
                            <ul class="list-unstyled">
                                <li>• Your documents are encrypted and secure</li>
                                <li>• Only authorized staff can access them</li>
                                <li>• We follow strict privacy policies</li>
                                <li>• Documents are deleted after processing</li>
                            </ul>
                        </div>
                        <div class="col-md-4">
                            <h6><i class="fas fa-headset me-2"></i>Support</h6>
                            <ul class="list-unstyled">
                                <li>• <i class="fas fa-phone me-1"></i> +233 59 260 5752</li>
                                <li>• <i class="fas fa-envelope me-1"></i> info@m25travelagency.com</li>
                                <li>• <i class="fas fa-clock me-1"></i> Mon-Fri: 9AM-6PM</li>
                                <li>• <i class="fas fa-comment me-1"></i> Live chat available</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
            <?php endif; ?>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // File upload functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('documentFile');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        
        // Drag and drop functionality
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                fileInput.files = files;
                showFilePreview(files[0]);
            }
        });
        
        // File input change
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                showFilePreview(e.target.files[0]);
            }
        });
        
        function showFilePreview(file) {
            fileName.textContent = file.name;
            fileSize.textContent = '(' + (file.size / 1024).toFixed(1) + ' KB)';
            filePreview.style.display = 'block';
        }
        
        function uploadDocument(docType) {
            document.getElementById('documentType').value = docType;
            document.querySelector('input[name="document_name"]').focus();
        }
        
        // Form validation
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('documentFile');
            const file = fileInput.files[0];
            
            if (!file) {
                e.preventDefault();
                alert('Please select a file to upload.');
                return;
            }
            
            // Check file size (5MB = 5242880 bytes)
            if (file.size > 5242880) {
                e.preventDefault();
                alert('File size too large. Maximum size is 5MB.');
                return;
            }
            
            // Check file type
            const allowedTypes = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            
            if (!allowedTypes.includes(fileExtension)) {
                e.preventDefault();
                alert('Invalid file type. Allowed types: ' + allowedTypes.join(', '));
                return;
            }
        });
        
        // View toggle functions
        function showView(viewType) {
            const gridView = document.getElementById('gridView');
            const listView = document.getElementById('listView');
            const buttons = document.querySelectorAll('.btn-group .btn');
            
            // Remove active class from all buttons
            buttons.forEach(btn => btn.classList.remove('active'));
            
            if (viewType === 'grid') {
                gridView.style.display = 'block';
                listView.style.display = 'none';
                buttons[0].classList.add('active');
            } else {
                gridView.style.display = 'none';
                listView.style.display = 'block';
                buttons[1].classList.add('active');
            }
        }
        
        // Document actions
        function viewDocument(docId) {
            // Open document in new window/tab
            window.open('view-document.php?id=' + docId, '_blank');
        }
        
        function downloadDocument(docId) {
            // Download document
            window.location.href = 'download-document.php?id=' + docId;
        }
        
        // Upload document with specific type
        function uploadDocument(docType) {
            const select = document.getElementById('documentType');
            select.value = docType;
            select.scrollIntoView({ behavior: 'smooth' });
            select.focus();
        }
    </script>
</body>
</html>
