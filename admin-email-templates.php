<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'create':
                    $stmt = $db->prepare("INSERT INTO email_templates (template_name, template_type, subject, body_html, body_text, variables, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([
                        sanitizeInput($_POST['template_name']),
                        sanitizeInput($_POST['template_type']),
                        sanitizeInput($_POST['subject']),
                        $_POST['body_html'],
                        $_POST['body_text'],
                        $_POST['variables'],
                        $_SESSION['admin_id']
                    ]);
                    $_SESSION['success'] = "Email template created successfully!";
                    break;
                    
                case 'update':
                    $stmt = $db->prepare("UPDATE email_templates SET template_name = ?, template_type = ?, subject = ?, body_html = ?, body_text = ?, variables = ? WHERE id = ?");
                    $stmt->execute([
                        sanitizeInput($_POST['template_name']),
                        sanitizeInput($_POST['template_type']),
                        sanitizeInput($_POST['subject']),
                        $_POST['body_html'],
                        $_POST['body_text'],
                        $_POST['variables'],
                        intval($_POST['template_id'])
                    ]);
                    $_SESSION['success'] = "Email template updated successfully!";
                    break;
                    
                case 'toggle_status':
                    $stmt = $db->prepare("UPDATE email_templates SET is_active = NOT is_active WHERE id = ?");
                    $stmt->execute([intval($_POST['template_id'])]);
                    $_SESSION['success'] = "Template status updated!";
                    break;
                    
                case 'delete':
                    $stmt = $db->prepare("DELETE FROM email_templates WHERE id = ? AND template_name NOT IN ('client_registration_confirmation', 'admin_new_registration')");
                    $stmt->execute([intval($_POST['template_id'])]);
                    $_SESSION['success'] = "Template deleted successfully!";
                    break;
            }
        }
        
        logActivity($_SESSION['admin_id'], null, 'Email Template Management', $_POST['action'] ?? 'unknown', $db);
        
    } catch (Exception $e) {
        error_log("Email template error: " . $e->getMessage());
        $_SESSION['error'] = "Operation failed: " . $e->getMessage();
    }
    
    header('Location: admin-email-templates.php');
    exit();
}

// Get all templates
try {
    $stmt = $db->prepare("SELECT * FROM email_templates ORDER BY template_type, template_name");
    $stmt->execute();
    $templates = $stmt->fetchAll();
} catch (Exception $e) {
    $templates = [];
    $_SESSION['error'] = "Failed to load templates.";
}

// Get template for editing if requested
$edit_template = null;
if (isset($_GET['edit'])) {
    try {
        $stmt = $db->prepare("SELECT * FROM email_templates WHERE id = ?");
        $stmt->execute([intval($_GET['edit'])]);
        $edit_template = $stmt->fetch();
    } catch (Exception $e) {
        $_SESSION['error'] = "Template not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Email Templates - M25 Travel & Tour Agency</title>
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
    
    <!-- Rich Text Editor -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
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
        .template-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .top-navbar {
            background: #13357B !important;
        }
        .template-item {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            background: white;
        }
        .variable-tag {
            background: #e9ecef;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin: 2px;
            display: inline-block;
        }
        .template-preview {
            max-height: 200px;
            overflow-y: auto;
            border: 1px solid #ddd;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
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
                    <a class="nav-link" href="admin-export.php">
                        <i class="fas fa-download me-2"></i>Export Data
                    </a>
                    <a class="nav-link active" href="admin-email-templates.php">
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
                        <span class="navbar-brand">Email Templates</span>
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
                    
                    <!-- Template Form -->
                    <div class="template-card">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4>
                                <i class="fas fa-envelope me-2"></i>
                                <?php echo $edit_template ? 'Edit Email Template' : 'Create New Email Template'; ?>
                            </h4>
                            <?php if ($edit_template): ?>
                                <a href="admin-email-templates.php" class="btn btn-secondary">
                                    <i class="fas fa-times me-2"></i>Cancel Edit
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <form method="POST" action="">
                            <?php if ($edit_template): ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="template_id" value="<?php echo $edit_template['id']; ?>">
                            <?php else: ?>
                                <input type="hidden" name="action" value="create">
                            <?php endif; ?>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Template Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="template_name" required
                                           value="<?php echo htmlspecialchars($edit_template['template_name'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Template Type <span class="text-danger">*</span></label>
                                    <select class="form-select" name="template_type" required>
                                        <option value="">Select Type</option>
                                        <option value="client_registration" <?php echo ($edit_template['template_type'] ?? '') == 'client_registration' ? 'selected' : ''; ?>>Client Registration</option>
                                        <option value="admin_notification" <?php echo ($edit_template['template_type'] ?? '') == 'admin_notification' ? 'selected' : ''; ?>>Admin Notification</option>
                                        <option value="status_update" <?php echo ($edit_template['template_type'] ?? '') == 'status_update' ? 'selected' : ''; ?>>Status Update</option>
                                        <option value="reminder" <?php echo ($edit_template['template_type'] ?? '') == 'reminder' ? 'selected' : ''; ?>>Reminder</option>
                                        <option value="custom" <?php echo ($edit_template['template_type'] ?? '') == 'custom' ? 'selected' : ''; ?>>Custom</option>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Subject <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="subject" required
                                           value="<?php echo htmlspecialchars($edit_template['subject'] ?? ''); ?>"
                                           placeholder="Use {{variable_name}} for dynamic content">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">HTML Body <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="body_html" id="htmlEditor" rows="15" required><?php echo htmlspecialchars($edit_template['body_html'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Plain Text Body</label>
                                    <textarea class="form-control" name="body_text" rows="10"
                                              placeholder="Plain text version (optional)"><?php echo htmlspecialchars($edit_template['body_text'] ?? ''); ?></textarea>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Available Variables</label>
                                    <textarea class="form-control" name="variables" rows="3"
                                              placeholder='["client_name", "reference_id", "visa_type", "country"]'><?php echo htmlspecialchars($edit_template['variables'] ?? ''); ?></textarea>
                                    <small class="form-text text-muted">JSON array of variable names that can be used in the template</small>
                                </div>
                            </div>
                            
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>
                                    <?php echo $edit_template ? 'Update Template' : 'Create Template'; ?>
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Available Variables Reference -->
                    <div class="template-card">
                        <h5><i class="fas fa-info-circle me-2"></i>Available Variables Reference</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Client Variables:</h6>
                                <div class="mb-3">
                                    <span class="variable-tag">{{client_name}}</span>
                                    <span class="variable-tag">{{reference_id}}</span>
                                    <span class="variable-tag">{{client_email}}</span>
                                    <span class="variable-tag">{{mobile_number}}</span>
                                    <span class="variable-tag">{{visa_type}}</span>
                                    <span class="variable-tag">{{country}}</span>
                                    <span class="variable-tag">{{submitted_date}}</span>
                                    <span class="variable-tag">{{status}}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>System Variables:</h6>
                                <div class="mb-3">
                                    <span class="variable-tag">{{admin_url}}</span>
                                    <span class="variable-tag">{{company_name}}</span>
                                    <span class="variable-tag">{{company_email}}</span>
                                    <span class="variable-tag">{{company_phone}}</span>
                                    <span class="variable-tag">{{current_date}}</span>
                                    <span class="variable-tag">{{current_time}}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Existing Templates -->
                    <div class="template-card">
                        <h4 class="mb-4"><i class="fas fa-list me-2"></i>Existing Email Templates</h4>
                        
                        <?php if (empty($templates)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-envelope fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No email templates found</h5>
                                <p class="text-muted">Create your first email template above</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($templates as $template): ?>
                                <div class="template-item">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <h6 class="mb-2">
                                                <?php echo htmlspecialchars($template['template_name']); ?>
                                                <?php if (!$template['is_active']): ?>
                                                    <span class="badge bg-secondary ms-2">Inactive</span>
                                                <?php endif; ?>
                                            </h6>
                                            <p class="mb-2">
                                                <span class="badge bg-info me-2"><?php echo ucfirst(str_replace('_', ' ', $template['template_type'])); ?></span>
                                                <strong>Subject:</strong> <?php echo htmlspecialchars($template['subject']); ?>
                                            </p>
                                            <div class="template-preview">
                                                <?php echo substr(strip_tags($template['body_html']), 0, 200) . '...'; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <p class="mb-2">
                                                <small class="text-muted">
                                                    Updated: <?php echo formatDate($template['updated_at'], 'M d, Y H:i'); ?>
                                                </small>
                                            </p>
                                            <div class="btn-group">
                                                <a href="?edit=<?php echo $template['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="previewTemplate(<?php echo $template['id']; ?>)">
                                                    <i class="fas fa-eye"></i> Preview
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="action" value="toggle_status">
                                                    <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline-warning">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                                <?php if (!in_array($template['template_name'], ['client_registration_confirmation', 'admin_new_registration'])): ?>
                                                    <form method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this template?')">
                                                        <input type="hidden" name="action" value="delete">
                                                        <input type="hidden" name="template_id" value="<?php echo $template['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
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
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Template Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="previewContent">
                    <!-- Preview content will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#htmlEditor',
            height: 400,
            plugins: 'advlist autolink lists link image charmap print preview anchor searchreplace visualblocks code fullscreen insertdatetime media table paste code help wordcount',
            toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
            content_style: 'body { font-family: Arial, sans-serif; font-size: 14px }'
        });
        
        function previewTemplate(templateId) {
            // In a real implementation, you would load the template via AJAX
            // For now, we'll show a placeholder
            document.getElementById('previewContent').innerHTML = '<p>Template preview functionality would be implemented here.</p>';
            new bootstrap.Modal(document.getElementById('previewModal')).show();
        }
    </script>
</body>
</html>
