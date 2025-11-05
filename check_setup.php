<?php
/**
 * M25 Travel & Tour Agency - Setup Checker
 * Quick diagnostic tool to verify installation
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>M25 Setup Checker</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .check-item { margin-bottom: 15px; padding: 15px; border-radius: 8px; }
        .check-pass { background: #d4edda; border: 1px solid #c3e6cb; }
        .check-fail { background: #f8d7da; border: 1px solid #f5c6cb; }
        .check-warn { background: #fff3cd; border: 1px solid #ffeaa7; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-check-circle me-2"></i>M25 Setup Checker</h4>
                    </div>
                    <div class="card-body">
                        
                        <?php
                        $checks = [];
                        $overall_status = true;
                        
                        // Check PHP version
                        $php_version = PHP_VERSION;
                        $php_ok = version_compare($php_version, '7.4.0') >= 0;
                        $checks[] = [
                            'name' => 'PHP Version',
                            'status' => $php_ok,
                            'message' => "PHP $php_version " . ($php_ok ? '(OK)' : '(Requires 7.4+)'),
                            'icon' => 'fa-code'
                        ];
                        if (!$php_ok) $overall_status = false;
                        
                        // Check required extensions
                        $extensions = ['pdo', 'pdo_mysql', 'json', 'session', 'filter'];
                        foreach ($extensions as $ext) {
                            $ext_ok = extension_loaded($ext);
                            $checks[] = [
                                'name' => "PHP Extension: $ext",
                                'status' => $ext_ok,
                                'message' => $ext_ok ? 'Available' : 'Missing',
                                'icon' => 'fa-puzzle-piece'
                            ];
                            if (!$ext_ok) $overall_status = false;
                        }
                        
                        // Check file permissions
                        $writable = is_writable('.');
                        $checks[] = [
                            'name' => 'Directory Permissions',
                            'status' => $writable,
                            'message' => $writable ? 'Writable' : 'Not writable',
                            'icon' => 'fa-folder'
                        ];
                        
                        // Check if config exists
                        $config_exists = file_exists('config.php');
                        $checks[] = [
                            'name' => 'Configuration File',
                            'status' => $config_exists,
                            'message' => $config_exists ? 'Found' : 'Missing (run install.php)',
                            'icon' => 'fa-cog'
                        ];
                        
                        // Check database connection if config exists
                        if ($config_exists) {
                            try {
                                include_once 'config.php';
                                $db_ok = true;
                                $db_message = 'Connected successfully';
                            } catch (Exception $e) {
                                $db_ok = false;
                                $db_message = 'Connection failed: ' . $e->getMessage();
                                $overall_status = false;
                            }
                            
                            $checks[] = [
                                'name' => 'Database Connection',
                                'status' => $db_ok,
                                'message' => $db_message,
                                'icon' => 'fa-database'
                            ];
                        }
                        
                        // Check uploads directory
                        $uploads_exists = file_exists('uploads');
                        $uploads_writable = $uploads_exists && is_writable('uploads');
                        $checks[] = [
                            'name' => 'Uploads Directory',
                            'status' => $uploads_writable,
                            'message' => $uploads_writable ? 'Ready' : ($uploads_exists ? 'Not writable' : 'Missing'),
                            'icon' => 'fa-upload'
                        ];
                        
                        // Check key files
                        $key_files = [
                            'index.php' => 'Homepage',
                            'client-registration.php' => 'Client Registration',
                            'admin-login.php' => 'Admin Login',
                            'admin-dashboard.php' => 'Admin Dashboard',
                            'database.sql' => 'Database Schema'
                        ];
                        
                        foreach ($key_files as $file => $name) {
                            $file_exists = file_exists($file);
                            $checks[] = [
                                'name' => $name,
                                'status' => $file_exists,
                                'message' => $file_exists ? 'Found' : 'Missing',
                                'icon' => 'fa-file'
                            ];
                            if (!$file_exists) $overall_status = false;
                        }
                        
                        // Display results
                        foreach ($checks as $check) {
                            $class = $check['status'] ? 'check-pass' : 'check-fail';
                            $icon_color = $check['status'] ? 'text-success' : 'text-danger';
                            $status_icon = $check['status'] ? 'fa-check' : 'fa-times';
                            
                            echo "<div class='check-item $class'>";
                            echo "<div class='d-flex justify-content-between align-items-center'>";
                            echo "<div>";
                            echo "<i class='fas {$check['icon']} me-2'></i>";
                            echo "<strong>{$check['name']}</strong>";
                            echo "</div>";
                            echo "<div>";
                            echo "<span class='me-2'>{$check['message']}</span>";
                            echo "<i class='fas $status_icon $icon_color'></i>";
                            echo "</div>";
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>
                        
                        <div class="mt-4 p-3 rounded <?php echo $overall_status ? 'bg-success text-white' : 'bg-danger text-white'; ?>">
                            <h5>
                                <i class="fas fa-<?php echo $overall_status ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                                Overall Status: <?php echo $overall_status ? 'READY' : 'NEEDS ATTENTION'; ?>
                            </h5>
                        </div>
                        
                        <?php if ($overall_status): ?>
                            <div class="mt-4 text-center">
                                <h6>🎉 Your system is ready!</h6>
                                <div class="btn-group mt-3">
                                    <a href="/" class="btn btn-success">
                                        <i class="fas fa-home me-2"></i>Visit Homepage
                                    </a>
                                    <a href="admin-login" class="btn btn-primary">
                                        <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                                    </a>
                                    <a href="client-registration" class="btn btn-info">
                                        <i class="fas fa-user-plus me-2"></i>Client Registration
                                    </a>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="mt-4 text-center">
                                <h6>⚠️ Setup Required</h6>
                                <p>Please resolve the issues above before using the system.</p>
                                <?php if (!$config_exists): ?>
                                    <a href="install.php" class="btn btn-warning">
                                        <i class="fas fa-cog me-2"></i>Run Installation Wizard
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mt-4 text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                For detailed setup instructions, see SETUP_GUIDE.md or QUICK_START.md
                            </small>
                        </div>
                        
                    </div>
                </div>
                
                <!-- System Information -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6><i class="fas fa-info me-2"></i>System Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <small>
                                    <strong>PHP Version:</strong> <?php echo PHP_VERSION; ?><br>
                                    <strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?><br>
                                    <strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?>
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small>
                                    <strong>Current Directory:</strong> <?php echo getcwd(); ?><br>
                                    <strong>Date/Time:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
                                    <strong>Timezone:</strong> <?php echo date_default_timezone_get(); ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
