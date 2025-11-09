<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: admin-login.php?timeout=1');
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_seo_config'])) {
    try {
        $settings = [
            'focus_keyword_weight' => (float)$_POST['focus_keyword_weight'],
            'content_length_weight' => (float)$_POST['content_length_weight'],
            'readability_weight' => (float)$_POST['readability_weight'],
            'meta_optimization_weight' => (float)$_POST['meta_optimization_weight'],
            'heading_structure_weight' => (float)$_POST['heading_structure_weight'],
            'image_optimization_weight' => (float)$_POST['image_optimization_weight'],
            'min_content_length' => (int)$_POST['min_content_length'],
            'max_content_length' => (int)$_POST['max_content_length'],
            'optimal_keyword_density' => (float)$_POST['optimal_keyword_density'],
            'max_keyword_density' => (float)$_POST['max_keyword_density'],
            'meta_title_min_length' => (int)$_POST['meta_title_min_length'],
            'meta_title_max_length' => (int)$_POST['meta_title_max_length'],
            'meta_description_min_length' => (int)$_POST['meta_description_min_length'],
            'meta_description_max_length' => (int)$_POST['meta_description_max_length'],
            'enable_schema_markup' => isset($_POST['enable_schema_markup']) ? 'true' : 'false',
            'enable_og_tags' => isset($_POST['enable_og_tags']) ? 'true' : 'false',
            'enable_twitter_cards' => isset($_POST['enable_twitter_cards']) ? 'true' : 'false',
            'default_og_image' => trim($_POST['default_og_image']),
            'site_name' => trim($_POST['site_name']),
            'organization_type' => trim($_POST['organization_type']),
            'contact_phone' => trim($_POST['contact_phone']),
            'contact_email' => trim($_POST['contact_email'])
        ];
        
        $stmt = $db->prepare("UPDATE seo_config SET setting_value = ?, updated_at = NOW() WHERE setting_name = ?");
        
        foreach ($settings as $name => $value) {
            $stmt->execute([$value, $name]);
        }
        
        $success_message = "SEO configuration updated successfully!";
        
    } catch (PDOException $e) {
        $error_message = "Error updating configuration: " . $e->getMessage();
    }
}

// Load current configuration
$config = [];
try {
    $stmt = $db->query("SELECT setting_name, setting_value FROM seo_config");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $config[$row['setting_name']] = $row['setting_value'];
    }
} catch (PDOException $e) {
    $error_message = "Error loading configuration: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>SEO Configuration - M25 Travel Agency Admin</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    
    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">
</head>

<body>
    <div class="container-fluid position-relative d-flex p-0">
        <!-- Sidebar Start -->
        <?php include 'includes/admin-sidebar.php'; ?>
        <!-- Sidebar End -->

        <!-- Content Start -->
        <div class="content">
            <!-- Navbar Start -->
            <nav class="navbar navbar-expand bg-secondary navbar-dark sticky-top px-4 py-0">
                <a href="admin-dashboard" class="navbar-brand d-flex d-lg-none me-4">
                    <h2 class="text-primary mb-0"><i class="fa fa-user-edit"></i></h2>
                </a>
                <a href="#" class="sidebar-toggler flex-shrink-0">
                    <i class="fa fa-bars"></i>
                </a>
                <div class="navbar-nav align-items-center ms-auto">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <img class="rounded-circle me-lg-2" src="img/user.jpg" alt="" style="width: 40px; height: 40px;">
                            <span class="d-none d-lg-inline-flex">Admin</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end bg-secondary border-0 rounded-0 rounded-bottom m-0">
                            <a href="#" class="dropdown-item">My Profile</a>
                            <a href="#" class="dropdown-item">Settings</a>
                            <a href="admin-logout" class="dropdown-item">Log Out</a>
                        </div>
                    </div>
                </div>
            </nav>
            <!-- Navbar End -->

            <!-- SEO Config Content Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-secondary rounded h-100 p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">SEO Configuration</h6>
                                <div>
                                    <a href="setup-seo-tables" class="btn btn-outline-info me-2">Setup Tables</a>
                                    <a href="admin-dashboard" class="btn btn-outline-primary">Back to Dashboard</a>
                                </div>
                            </div>
                            
                            <?php if ($success_message): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="fa fa-check-circle me-2"></i><?php echo $success_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($error_message): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="fa fa-exclamation-triangle me-2"></i><?php echo $error_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>
                            
                            <form method="POST" class="needs-validation" novalidate>
                                <!-- SEO Score Weights -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-weight-hanging me-2"></i>SEO Score Weights</h6>
                                        <small class="text-muted">Configure how different factors contribute to the overall SEO score (total should equal 100%)</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Focus Keyword Weight (%)</label>
                                                    <input type="number" class="form-control" name="focus_keyword_weight" 
                                                           value="<?php echo $config['focus_keyword_weight'] ?? 25; ?>" 
                                                           min="0" max="100" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Content Length Weight (%)</label>
                                                    <input type="number" class="form-control" name="content_length_weight" 
                                                           value="<?php echo $config['content_length_weight'] ?? 15; ?>" 
                                                           min="0" max="100" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Readability Weight (%)</label>
                                                    <input type="number" class="form-control" name="readability_weight" 
                                                           value="<?php echo $config['readability_weight'] ?? 20; ?>" 
                                                           min="0" max="100" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Meta Optimization Weight (%)</label>
                                                    <input type="number" class="form-control" name="meta_optimization_weight" 
                                                           value="<?php echo $config['meta_optimization_weight'] ?? 20; ?>" 
                                                           min="0" max="100" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Heading Structure Weight (%)</label>
                                                    <input type="number" class="form-control" name="heading_structure_weight" 
                                                           value="<?php echo $config['heading_structure_weight'] ?? 10; ?>" 
                                                           min="0" max="100" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Image Optimization Weight (%)</label>
                                                    <input type="number" class="form-control" name="image_optimization_weight" 
                                                           value="<?php echo $config['image_optimization_weight'] ?? 10; ?>" 
                                                           min="0" max="100" required>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Current Total: <span id="totalWeight">100</span>%</strong>
                                            <span id="weightWarning" class="text-warning ms-2" style="display: none;">
                                                ⚠️ Total should equal 100%
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Content Analysis Settings -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Content Analysis Settings</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Minimum Content Length (words)</label>
                                                    <input type="number" class="form-control" name="min_content_length" 
                                                           value="<?php echo $config['min_content_length'] ?? 300; ?>" 
                                                           min="50" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Maximum Content Length (words)</label>
                                                    <input type="number" class="form-control" name="max_content_length" 
                                                           value="<?php echo $config['max_content_length'] ?? 2500; ?>" 
                                                           min="500" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Optimal Keyword Density (%)</label>
                                                    <input type="number" step="0.1" class="form-control" name="optimal_keyword_density" 
                                                           value="<?php echo $config['optimal_keyword_density'] ?? 1.5; ?>" 
                                                           min="0.1" max="5" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Maximum Keyword Density (%)</label>
                                                    <input type="number" step="0.1" class="form-control" name="max_keyword_density" 
                                                           value="<?php echo $config['max_keyword_density'] ?? 3.0; ?>" 
                                                           min="1" max="10" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Meta Tags Settings -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Meta Tags Settings</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Meta Title Min Length</label>
                                                    <input type="number" class="form-control" name="meta_title_min_length" 
                                                           value="<?php echo $config['meta_title_min_length'] ?? 30; ?>" 
                                                           min="10" max="100" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Meta Title Max Length</label>
                                                    <input type="number" class="form-control" name="meta_title_max_length" 
                                                           value="<?php echo $config['meta_title_max_length'] ?? 60; ?>" 
                                                           min="30" max="100" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Meta Description Min Length</label>
                                                    <input type="number" class="form-control" name="meta_description_min_length" 
                                                           value="<?php echo $config['meta_description_min_length'] ?? 120; ?>" 
                                                           min="50" max="200" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Meta Description Max Length</label>
                                                    <input type="number" class="form-control" name="meta_description_max_length" 
                                                           value="<?php echo $config['meta_description_max_length'] ?? 160; ?>" 
                                                           min="100" max="200" required>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Schema & Social Settings -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="mb-0"><i class="fas fa-share-alt me-2"></i>Schema & Social Media Settings</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="enable_schema_markup" 
                                                               <?php echo ($config['enable_schema_markup'] ?? 'true') === 'true' ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Enable Schema Markup</label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="enable_og_tags" 
                                                               <?php echo ($config['enable_og_tags'] ?? 'true') === 'true' ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Enable Open Graph Tags</label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="enable_twitter_cards" 
                                                               <?php echo ($config['enable_twitter_cards'] ?? 'true') === 'true' ? 'checked' : ''; ?>>
                                                        <label class="form-check-label">Enable Twitter Cards</label>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Default OG Image URL</label>
                                                    <input type="url" class="form-control" name="default_og_image" 
                                                           value="<?php echo $config['default_og_image'] ?? ''; ?>" 
                                                           placeholder="https://example.com/default-image.jpg">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Site Name</label>
                                                    <input type="text" class="form-control" name="site_name" 
                                                           value="<?php echo $config['site_name'] ?? 'M25 Travel & Tour Agency'; ?>" 
                                                           required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Organization Type</label>
                                                    <select class="form-control" name="organization_type">
                                                        <option value="TravelAgency" <?php echo ($config['organization_type'] ?? 'TravelAgency') === 'TravelAgency' ? 'selected' : ''; ?>>Travel Agency</option>
                                                        <option value="Organization" <?php echo ($config['organization_type'] ?? '') === 'Organization' ? 'selected' : ''; ?>>Organization</option>
                                                        <option value="LocalBusiness" <?php echo ($config['organization_type'] ?? '') === 'LocalBusiness' ? 'selected' : ''; ?>>Local Business</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Contact Phone</label>
                                                    <input type="tel" class="form-control" name="contact_phone" 
                                                           value="<?php echo $config['contact_phone'] ?? ''; ?>" 
                                                           placeholder="+1-234-567-8900">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Contact Email</label>
                                                    <input type="email" class="form-control" name="contact_email" 
                                                           value="<?php echo $config['contact_email'] ?? ''; ?>" 
                                                           placeholder="contact@example.com">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="update_seo_config" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Save Configuration
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- SEO Config Content End -->
        </div>
        <!-- Content End -->
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/chart/chart.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    
    <script>
        // Calculate total weight
        function calculateTotalWeight() {
            const weights = [
                'focus_keyword_weight',
                'content_length_weight', 
                'readability_weight',
                'meta_optimization_weight',
                'heading_structure_weight',
                'image_optimization_weight'
            ];
            
            let total = 0;
            weights.forEach(function(weight) {
                const value = parseFloat(document.querySelector(`input[name="${weight}"]`).value) || 0;
                total += value;
            });
            
            document.getElementById('totalWeight').textContent = total;
            const warning = document.getElementById('weightWarning');
            
            if (total !== 100) {
                warning.style.display = 'inline';
            } else {
                warning.style.display = 'none';
            }
        }
        
        // Add event listeners to weight inputs
        document.addEventListener('DOMContentLoaded', function() {
            const weightInputs = document.querySelectorAll('input[name$="_weight"]');
            weightInputs.forEach(function(input) {
                input.addEventListener('input', calculateTotalWeight);
            });
            
            // Calculate initial total
            calculateTotalWeight();
        });
    </script>
</body>
</html>
