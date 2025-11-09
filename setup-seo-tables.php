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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup_seo_tables'])) {
    try {
        // Create SEO configuration table
        $sql_seo_config = "CREATE TABLE IF NOT EXISTS seo_config (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_name VARCHAR(100) NOT NULL UNIQUE,
            setting_value TEXT,
            setting_type ENUM('text', 'textarea', 'number', 'boolean', 'json') DEFAULT 'text',
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        $db->exec($sql_seo_config);
        
        // Create SEO analysis table for storing page analysis results
        $sql_seo_analysis = "CREATE TABLE IF NOT EXISTS seo_analysis (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_type ENUM('blog_post', 'page', 'category', 'tag') NOT NULL,
            page_id INT NOT NULL,
            focus_keyword VARCHAR(255),
            seo_score INT DEFAULT 0,
            readability_score INT DEFAULT 0,
            keyword_density DECIMAL(5,2) DEFAULT 0.00,
            word_count INT DEFAULT 0,
            meta_title VARCHAR(255),
            meta_description TEXT,
            meta_keywords TEXT,
            og_title VARCHAR(255),
            og_description TEXT,
            og_image VARCHAR(500),
            twitter_title VARCHAR(255),
            twitter_description TEXT,
            twitter_image VARCHAR(500),
            schema_markup JSON,
            analysis_data JSON,
            suggestions JSON,
            last_analyzed TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_page_type_id (page_type, page_id)
        )";
        
        $db->exec($sql_seo_analysis);
        
        // Create SEO keywords table for tracking keyword performance
        $sql_seo_keywords = "CREATE TABLE IF NOT EXISTS seo_keywords (
            id INT AUTO_INCREMENT PRIMARY KEY,
            keyword VARCHAR(255) NOT NULL,
            page_type ENUM('blog_post', 'page', 'category', 'tag') NOT NULL,
            page_id INT NOT NULL,
            density DECIMAL(5,2) DEFAULT 0.00,
            prominence_score INT DEFAULT 0,
            in_title BOOLEAN DEFAULT FALSE,
            in_meta_description BOOLEAN DEFAULT FALSE,
            in_headings BOOLEAN DEFAULT FALSE,
            in_url BOOLEAN DEFAULT FALSE,
            in_alt_text BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_keyword (keyword),
            INDEX idx_page_type_id (page_type, page_id)
        )";
        
        $db->exec($sql_seo_keywords);
        
        // Create SEO audit log table
        $sql_seo_audit_log = "CREATE TABLE IF NOT EXISTS seo_audit_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            page_type ENUM('blog_post', 'page', 'category', 'tag') NOT NULL,
            page_id INT NOT NULL,
            audit_type ENUM('full', 'quick', 'keyword', 'technical') DEFAULT 'full',
            issues_found JSON,
            recommendations JSON,
            score_before INT DEFAULT 0,
            score_after INT DEFAULT 0,
            performed_by VARCHAR(100),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_page_type_id (page_type, page_id)
        )";
        
        $db->exec($sql_seo_audit_log);
        
        // Insert default SEO configuration settings
        $default_settings = [
            ['focus_keyword_weight', '25', 'number', 'Weight for focus keyword in SEO score calculation'],
            ['content_length_weight', '15', 'number', 'Weight for content length in SEO score calculation'],
            ['readability_weight', '20', 'number', 'Weight for readability in SEO score calculation'],
            ['meta_optimization_weight', '20', 'number', 'Weight for meta tags optimization in SEO score calculation'],
            ['heading_structure_weight', '10', 'number', 'Weight for heading structure in SEO score calculation'],
            ['image_optimization_weight', '10', 'number', 'Weight for image optimization in SEO score calculation'],
            ['min_content_length', '300', 'number', 'Minimum recommended content length in words'],
            ['max_content_length', '2500', 'number', 'Maximum recommended content length in words'],
            ['optimal_keyword_density', '1.5', 'number', 'Optimal keyword density percentage'],
            ['max_keyword_density', '3.0', 'number', 'Maximum recommended keyword density percentage'],
            ['meta_title_min_length', '30', 'number', 'Minimum meta title length'],
            ['meta_title_max_length', '60', 'number', 'Maximum meta title length'],
            ['meta_description_min_length', '120', 'number', 'Minimum meta description length'],
            ['meta_description_max_length', '160', 'number', 'Maximum meta description length'],
            ['enable_schema_markup', 'true', 'boolean', 'Enable automatic schema markup generation'],
            ['enable_og_tags', 'true', 'boolean', 'Enable Open Graph meta tags'],
            ['enable_twitter_cards', 'true', 'boolean', 'Enable Twitter Card meta tags'],
            ['default_og_image', '', 'text', 'Default Open Graph image URL'],
            ['site_name', 'M25 Travel & Tour Agency', 'text', 'Site name for schema markup'],
            ['organization_type', 'TravelAgency', 'text', 'Schema.org organization type'],
            ['contact_phone', '', 'text', 'Contact phone for schema markup'],
            ['contact_email', '', 'text', 'Contact email for schema markup'],
            ['social_profiles', '[]', 'json', 'Social media profiles for schema markup']
        ];
        
        $stmt = $db->prepare("INSERT IGNORE INTO seo_config (setting_name, setting_value, setting_type, description) VALUES (?, ?, ?, ?)");
        
        foreach ($default_settings as $setting) {
            $stmt->execute($setting);
        }
        
        $success_message = "SEO tables and default configuration created successfully!";
        
    } catch (PDOException $e) {
        $error_message = "Error creating SEO tables: " . $e->getMessage();
    }
}

// Check if tables exist
$tables_exist = false;
try {
    $result = $db->query("SHOW TABLES LIKE 'seo_config'");
    $tables_exist = $result->rowCount() > 0;
} catch (PDOException $e) {
    // Tables don't exist
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Setup SEO Tables - M25 Travel Agency Admin</title>
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
                            <i class="fa fa-envelope me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Message</span>
                        </a>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fa fa-bell me-lg-2"></i>
                            <span class="d-none d-lg-inline-flex">Notification</span>
                        </a>
                    </div>
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

            <!-- Setup Content Start -->
            <div class="container-fluid pt-4 px-4">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="bg-secondary rounded h-100 p-4">
                            <div class="d-flex align-items-center justify-content-between mb-4">
                                <h6 class="mb-0">SEO System Setup</h6>
                                <a href="admin-dashboard" class="btn btn-outline-primary">Back to Dashboard</a>
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
                            
                            <?php if (!$tables_exist): ?>
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h5 class="card-title mb-0"><i class="fas fa-exclamation-triangle me-2"></i>SEO System Not Installed</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">The SEO system database tables have not been created yet. Click the button below to set up the AI SEO system.</p>
                                        
                                        <h6 class="mt-4 mb-3">Features that will be installed:</h6>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i>AI Content Analysis</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>SEO Score Calculation</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Keyword Optimization</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Readability Analysis</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled">
                                                    <li><i class="fas fa-check text-success me-2"></i>Meta Tags Optimization</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Schema Markup Generation</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Social Media Tags</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>SEO Audit Reports</li>
                                                </ul>
                                            </div>
                                        </div>
                                        
                                        <form method="POST" class="mt-4">
                                            <button type="submit" name="setup_seo_tables" class="btn btn-primary btn-lg">
                                                <i class="fas fa-rocket me-2"></i>Install AI SEO System
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card border-success">
                                    <div class="card-header bg-success text-white">
                                        <h5 class="card-title mb-0"><i class="fas fa-check-circle me-2"></i>SEO System Installed</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="card-text">The AI SEO system is successfully installed and ready to use!</p>
                                        
                                        <div class="row mt-4">
                                            <div class="col-md-4">
                                                <div class="card bg-primary text-white">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-cogs fa-2x mb-2"></i>
                                                        <h6>SEO Configuration</h6>
                                                        <a href="admin-seo-config" class="btn btn-light btn-sm">Configure</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-info text-white">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-chart-line fa-2x mb-2"></i>
                                                        <h6>SEO Analytics</h6>
                                                        <a href="admin-seo-analytics" class="btn btn-light btn-sm">View Reports</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="card bg-warning text-white">
                                                    <div class="card-body text-center">
                                                        <i class="fas fa-search fa-2x mb-2"></i>
                                                        <h6>SEO Audit</h6>
                                                        <a href="admin-seo-audit" class="btn btn-light btn-sm">Run Audit</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Setup Content End -->
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
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
</body>
</html>
