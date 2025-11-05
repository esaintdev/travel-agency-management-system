<?php
require_once 'config.php';
requireLogin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create':
                $visa_type = sanitizeInput($_POST['visa_type']);
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $requirements = sanitizeInput($_POST['requirements']);
                $process = sanitizeInput($_POST['process']);
                $processing_time = sanitizeInput($_POST['processing_time']);
                $validity = sanitizeInput($_POST['validity']);
                $fees = sanitizeInput($_POST['fees']);
                
                // Handle image upload
                $image_path = null;
                if (isset($_FILES['visa_image']) && $_FILES['visa_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/visa-images/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    // Validate file type using both extension and MIME type
                    $file_extension = strtolower(pathinfo($_FILES['visa_image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
                    $allowed_mime_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                    
                    $file_mime_type = mime_content_type($_FILES['visa_image']['tmp_name']);
                    
                    if (in_array($file_extension, $allowed_extensions) && in_array($file_mime_type, $allowed_mime_types)) {
                        // Check file size (max 5MB)
                        if ($_FILES['visa_image']['size'] <= 5 * 1024 * 1024 && $_FILES['visa_image']['size'] > 0) {
                            // Validate that it's actually an image
                            $image_info = getimagesize($_FILES['visa_image']['tmp_name']);
                            if ($image_info !== false) {
                                // Generate unique filename to avoid conflicts
                                $filename = $visa_type . '_' . time() . '.' . $file_extension;
                                $upload_path = $upload_dir . $filename;
                                
                                // Remove any existing image for this visa type
                                $existing_files = glob($upload_dir . $visa_type . '_*.*');
                                foreach ($existing_files as $existing_file) {
                                    if (file_exists($existing_file)) {
                                        unlink($existing_file);
                                    }
                                }
                                
                                if (move_uploaded_file($_FILES['visa_image']['tmp_name'], $upload_path)) {
                                    $image_path = $upload_path;
                                    // Set proper permissions
                                    chmod($upload_path, 0644);
                                    
                                    // Verify the uploaded file is not corrupted
                                    if (!file_exists($upload_path) || filesize($upload_path) == 0) {
                                        handleError("Image upload failed: File appears to be corrupted.");
                                    }
                                } else {
                                    handleError("Failed to upload image. Please check directory permissions.");
                                }
                            } else {
                                handleError("Invalid image file. The file appears to be corrupted or is not a valid image.");
                            }
                        } else {
                            handleError("Image file is too large. Maximum size is 5MB.");
                        }
                    } else {
                        handleError("Invalid image format. Please use JPG, JPEG, PNG, or WEBP files only.");
                    }
                } elseif (isset($_FILES['visa_image']) && $_FILES['visa_image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    // Handle upload errors
                    $upload_errors = [
                        UPLOAD_ERR_INI_SIZE => 'File is too large (server limit)',
                        UPLOAD_ERR_FORM_SIZE => 'File is too large (form limit)',
                        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
                        UPLOAD_ERR_NO_TMP_DIR => 'No temporary directory',
                        UPLOAD_ERR_CANT_WRITE => 'Cannot write to disk',
                        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
                    ];
                    $error_message = isset($upload_errors[$_FILES['visa_image']['error']]) 
                        ? $upload_errors[$_FILES['visa_image']['error']] 
                        : 'Unknown upload error';
                    handleError("Image upload failed: " . $error_message);
                }
                
                try {
                    $sql = "INSERT INTO visa_content (visa_type, title, description, requirements, process, processing_time, validity, fees, image_path) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$visa_type, $title, $description, $requirements, $process, $processing_time, $validity, $fees, $image_path]);
                    
                    logActivity($_SESSION['admin_id'], null, 'CREATE', "Created visa content for {$title}", $db);
                    handleSuccess("Visa content created successfully!", "admin-visa-content.php");
                } catch (PDOException $e) {
                    handleError("Error creating visa content: " . $e->getMessage());
                }
                break;
                
            case 'update':
                $id = (int)$_POST['id'];
                $title = sanitizeInput($_POST['title']);
                $description = sanitizeInput($_POST['description']);
                $requirements = sanitizeInput($_POST['requirements']);
                $process = sanitizeInput($_POST['process']);
                $processing_time = sanitizeInput($_POST['processing_time']);
                $validity = sanitizeInput($_POST['validity']);
                $fees = sanitizeInput($_POST['fees']);
                $status = sanitizeInput($_POST['status']);
                
                // Get current visa info for image handling
                $stmt = $db->prepare("SELECT visa_type, image_path FROM visa_content WHERE id = ?");
                $stmt->execute([$id]);
                $current_visa = $stmt->fetch();
                
                $image_path = $current_visa['image_path']; // Keep existing image by default
                
                // Handle new image upload
                if (isset($_FILES['visa_image']) && $_FILES['visa_image']['error'] === UPLOAD_ERR_OK) {
                    $upload_dir = 'uploads/visa-images/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $file_extension = strtolower(pathinfo($_FILES['visa_image']['name'], PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
                    
                    if (in_array($file_extension, $allowed_extensions)) {
                        // Delete old image if exists
                        if ($image_path && file_exists($image_path)) {
                            unlink($image_path);
                        }
                        
                        $filename = $current_visa['visa_type'] . '.' . $file_extension;
                        $upload_path = $upload_dir . $filename;
                        
                        if (move_uploaded_file($_FILES['visa_image']['tmp_name'], $upload_path)) {
                            $image_path = $upload_path;
                        }
                    }
                }
                
                try {
                    $sql = "UPDATE visa_content SET title=?, description=?, requirements=?, process=?, 
                            processing_time=?, validity=?, fees=?, status=?, image_path=? WHERE id=?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$title, $description, $requirements, $process, $processing_time, $validity, $fees, $status, $image_path, $id]);
                    
                    logActivity($_SESSION['admin_id'], null, 'UPDATE', "Updated visa content for {$title}", $db);
                    handleSuccess("Visa content updated successfully!", "admin-visa-content.php");
                } catch (PDOException $e) {
                    handleError("Error updating visa content: " . $e->getMessage());
                }
                break;
                
            case 'delete':
                $id = (int)$_POST['id'];
                
                try {
                    // Get visa title for logging
                    $stmt = $db->prepare("SELECT title FROM visa_content WHERE id = ?");
                    $stmt->execute([$id]);
                    $visa = $stmt->fetch();
                    
                    $sql = "DELETE FROM visa_content WHERE id = ?";
                    $stmt = $db->prepare($sql);
                    $stmt->execute([$id]);
                    
                    logActivity($_SESSION['admin_id'], null, 'DELETE', "Deleted visa content for {$visa['title']}", $db);
                    handleSuccess("Visa content deleted successfully!", "admin-visa-content.php");
                } catch (PDOException $e) {
                    handleError("Error deleting visa content: " . $e->getMessage());
                }
                break;
        }
    }
}

// Get all visa content
$visas = [];
$table_missing = false;
try {
    $sql = "SELECT * FROM visa_content ORDER BY visa_type";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $visas = $stmt->fetchAll();
} catch (PDOException $e) {
    // Log the actual error for debugging
    error_log("Visa content query error: " . $e->getMessage());
    
    // Check if table doesn't exist
    if (strpos($e->getMessage(), "doesn't exist") !== false || strpos($e->getMessage(), "Table") !== false) {
        $table_missing = true;
    } else {
        // Show the actual error for debugging
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    }
}

// Get specific visa for editing
$editVisa = null;
if (isset($_GET['edit']) && !$table_missing) {
    $editId = (int)$_GET['edit'];
    try {
        $stmt = $db->prepare("SELECT * FROM visa_content WHERE id = ?");
        $stmt->execute([$editId]);
        $editVisa = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Edit visa query error: " . $e->getMessage());
        $_SESSION['error'] = "Error loading visa for editing: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Visa Content Management - <?php echo APP_NAME; ?></title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    
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
        .top-navbar {
            background: #13357B;
            padding: 15px 0;
        }
        .navbar-brand {
            color: white !important;
            font-weight: 600;
        }
        .card {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: none;
            border-radius: 10px;
        }
        .table-responsive {
            border-radius: 0.375rem;
        }
        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
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
                    <a class="nav-link" href="admin-clients.php">
                        <i class="fas fa-users me-2"></i>All Clients
                    </a>
                    <a class="nav-link" href="admin-search.php">
                        <i class="fas fa-search me-2"></i>Search Clients
                    </a>
                    <a class="nav-link active" href="admin-visa-content.php">
                        <i class="fas fa-passport me-2"></i>Visa Content
                    </a>
                    <a class="nav-link" href="admin-export.php">
                        <i class="fas fa-download me-2"></i>Export Data
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
                        <span class="navbar-brand">Visa Content Management</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- Page Content -->
                <div class="container-fluid p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="mb-0">Manage Visa Content</h4>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVisaModal">
                            <i class="fas fa-plus me-2"></i>Add New Visa Content
                        </button>
                    </div>

                <!-- Flash Messages -->
                <?php if ($error = getFlashMessage('error')): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success = getFlashMessage('success')): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Table Missing Warning -->
                <?php if (isset($table_missing) && $table_missing): ?>
                    <div class="alert alert-warning" role="alert">
                        <h5><i class="fas fa-exclamation-triangle me-2"></i>Database Setup Required</h5>
                        <p class="mb-3">The visa content table hasn't been created yet. Please run the database setup first.</p>
                        <div class="d-flex gap-2">
                            <a href="install-visa-content.php" class="btn btn-primary">
                                <i class="fas fa-database me-2"></i>Run Database Setup
                            </a>
                            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#sqlInstructions">
                                <i class="fas fa-code me-2"></i>Manual SQL Instructions
                            </button>
                        </div>
                        <div class="collapse mt-3" id="sqlInstructions">
                            <div class="card card-body">
                                <h6>Run this SQL in phpMyAdmin:</h6>
                                <pre class="bg-light p-3 rounded"><code>ALTER TABLE visa_content ADD COLUMN image_path VARCHAR(255) DEFAULT NULL;</code></pre>
                                <small class="text-muted">If you get a "table doesn't exist" error, you need to run the full schema first.</small>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Visa Content Table -->
                <?php if (!isset($table_missing) || !$table_missing): ?>
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">All Visa Content</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($visas)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-passport fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No visa content found</h5>
                                <p class="text-muted">Click "Add New Visa Content" to get started.</p>
                            </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Visa Type</th>
                                        <th>Title</th>
                                        <th>Processing Time</th>
                                        <th>Fees</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($visas as $visa): ?>
                                    <tr>
                                        <td><?php echo $visa['id']; ?></td>
                                        <td><span class="badge bg-secondary"><?php echo htmlspecialchars($visa['visa_type']); ?></span></td>
                                        <td><?php echo htmlspecialchars($visa['title']); ?></td>
                                        <td><?php echo htmlspecialchars($visa['processing_time']); ?></td>
                                        <td><?php echo htmlspecialchars($visa['fees']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $visa['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($visa['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button type="button" class="btn btn-sm btn-info" onclick="viewVisa(<?php echo $visa['id']; ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="?edit=<?php echo $visa['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger" onclick="deleteVisa(<?php echo $visa['id']; ?>, '<?php echo htmlspecialchars($visa['title']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add/Edit Visa Modal -->
    <div class="modal fade" id="addVisaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $editVisa ? 'Edit' : 'Add New'; ?> Visa Content</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="<?php echo $editVisa ? 'update' : 'create'; ?>">
                        <?php if ($editVisa): ?>
                            <input type="hidden" name="id" value="<?php echo $editVisa['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="visa_type" class="form-label">Visa Type (URL Slug)</label>
                                    <input type="text" class="form-control" id="visa_type" name="visa_type" 
                                           value="<?php echo $editVisa ? htmlspecialchars($editVisa['visa_type']) : ''; ?>" 
                                           <?php echo $editVisa ? 'readonly' : 'required'; ?>>
                                    <small class="form-text text-muted">e.g., job-visa, business-visa</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Title</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?php echo $editVisa ? htmlspecialchars($editVisa['title']) : ''; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description (400+ words recommended)</label>
                            <textarea class="form-control" id="description" name="description" rows="8" required placeholder="Write a comprehensive description about this visa type. Include details about eligibility, benefits, and important information that applicants should know..."><?php echo $editVisa ? htmlspecialchars($editVisa['description']) : ''; ?></textarea>
                            <small class="form-text text-muted">This content will be displayed in a blog-like format. You can use line breaks for paragraphs.</small>
                        </div>

                        <div class="mb-3">
                            <label for="requirements" class="form-label">Requirements (comma-separated)</label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="3" required><?php echo $editVisa ? htmlspecialchars($editVisa['requirements']) : ''; ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="process" class="form-label">Process Steps (comma-separated)</label>
                            <textarea class="form-control" id="process" name="process" rows="3" required><?php echo $editVisa ? htmlspecialchars($editVisa['process']) : ''; ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="processing_time" class="form-label">Processing Time</label>
                                    <input type="text" class="form-control" id="processing_time" name="processing_time" 
                                           value="<?php echo $editVisa ? htmlspecialchars($editVisa['processing_time']) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="validity" class="form-label">Validity</label>
                                    <input type="text" class="form-control" id="validity" name="validity" 
                                           value="<?php echo $editVisa ? htmlspecialchars($editVisa['validity']) : ''; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="fees" class="form-label">Fees</label>
                                    <input type="text" class="form-control" id="fees" name="fees" 
                                           value="<?php echo $editVisa ? htmlspecialchars($editVisa['fees']) : ''; ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="visa_image" class="form-label">Visa Image</label>
                            <input type="file" class="form-control" id="visa_image" name="visa_image" accept="image/*">
                            <small class="form-text text-muted">Supported formats: JPG, JPEG, PNG, WEBP. Max size: 5MB</small>
                            <?php if ($editVisa && $editVisa['image_path']): ?>
                                <div class="mt-2">
                                    <small class="text-muted">Current image:</small><br>
                                    <img src="<?php echo htmlspecialchars($editVisa['image_path']); ?>" class="img-thumbnail" style="max-width: 150px; max-height: 100px;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($editVisa): ?>
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" <?php echo $editVisa['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $editVisa['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary"><?php echo $editVisa ? 'Update' : 'Create'; ?> Visa Content</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Visa Modal -->
    <div class="modal fade" id="viewVisaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Visa Content Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="visaDetails">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the visa content "<span id="deleteVisaTitle"></span>"?</p>
                    <p class="text-danger"><strong>This action cannot be undone.</strong></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteVisaId">
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Show modal if editing
        <?php if ($editVisa): ?>
        document.addEventListener('DOMContentLoaded', function() {
            var modal = new bootstrap.Modal(document.getElementById('addVisaModal'));
            modal.show();
        });
        <?php endif; ?>

        function viewVisa(id) {
            // Fetch visa details via AJAX
            fetch(`admin-visa-content.php?view=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('visaDetails').innerHTML = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Visa Type:</h6>
                                <p>${data.visa_type}</p>
                                <h6>Title:</h6>
                                <p>${data.title}</p>
                                <h6>Processing Time:</h6>
                                <p>${data.processing_time}</p>
                                <h6>Validity:</h6>
                                <p>${data.validity}</p>
                                <h6>Fees:</h6>
                                <p>${data.fees}</p>
                                <h6>Status:</h6>
                                <p><span class="badge bg-${data.status === 'active' ? 'success' : 'warning'}">${data.status}</span></p>
                            </div>
                            <div class="col-md-6">
                                <h6>Description:</h6>
                                <p>${data.description}</p>
                                <h6>Requirements:</h6>
                                <p>${data.requirements}</p>
                                <h6>Process:</h6>
                                <p>${data.process}</p>
                            </div>
                        </div>
                    `;
                    var modal = new bootstrap.Modal(document.getElementById('viewVisaModal'));
                    modal.show();
                });
        }

        function deleteVisa(id, title) {
            document.getElementById('deleteVisaId').value = id;
            document.getElementById('deleteVisaTitle').textContent = title;
            var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
    </script>
</body>
</html>

<?php
// Handle AJAX request for viewing visa details
if (isset($_GET['view'])) {
    $id = (int)$_GET['view'];
    $stmt = $db->prepare("SELECT * FROM visa_content WHERE id = ?");
    $stmt->execute([$id]);
    $visa = $stmt->fetch();
    
    header('Content-Type: application/json');
    echo json_encode($visa);
    exit;
}
?>
