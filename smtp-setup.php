<?php
/**
 * SMTP Configuration Helper for SiteGround Hosting
 * This script helps configure SMTP settings for M25 Travel & Tour Agency
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $smtp_host = $_POST['smtp_host'] ?? '';
    $smtp_port = $_POST['smtp_port'] ?? '';
    $smtp_username = $_POST['smtp_username'] ?? '';
    $smtp_password = $_POST['smtp_password'] ?? '';
    $smtp_encryption = $_POST['smtp_encryption'] ?? '';
    $from_email = $_POST['from_email'] ?? '';
    $from_name = $_POST['from_name'] ?? '';
    
    try {
        // Read current config.php
        $configPath = 'config.php';
        if (!file_exists($configPath)) {
            throw new Exception("config.php file not found!");
        }
        
        $configContent = file_get_contents($configPath);
        
        // Update SMTP settings
        $configContent = preg_replace(
            "/define\('SMTP_HOST', '[^']*'\);/",
            "define('SMTP_HOST', '" . addslashes($smtp_host) . "');",
            $configContent
        );
        
        $configContent = preg_replace(
            "/define\('SMTP_PORT', [^)]*\);/",
            "define('SMTP_PORT', " . intval($smtp_port) . ");",
            $configContent
        );
        
        $configContent = preg_replace(
            "/define\('SMTP_USERNAME', '[^']*'\);/",
            "define('SMTP_USERNAME', '" . addslashes($smtp_username) . "');",
            $configContent
        );
        
        $configContent = preg_replace(
            "/define\('SMTP_PASSWORD', '[^']*'\);/",
            "define('SMTP_PASSWORD', '" . addslashes($smtp_password) . "');",
            $configContent
        );
        
        $configContent = preg_replace(
            "/define\('SMTP_ENCRYPTION', '[^']*'\);/",
            "define('SMTP_ENCRYPTION', '" . addslashes($smtp_encryption) . "');",
            $configContent
        );
        
        $configContent = preg_replace(
            "/define\('SMTP_FROM_EMAIL', '[^']*'\);/",
            "define('SMTP_FROM_EMAIL', '" . addslashes($from_email) . "');",
            $configContent
        );
        
        $configContent = preg_replace(
            "/define\('SMTP_FROM_NAME', '[^']*'\);/",
            "define('SMTP_FROM_NAME', '" . addslashes($from_name) . "');",
            $configContent
        );
        
        // Write updated config
        if (file_put_contents($configPath, $configContent)) {
            $message = "SMTP configuration updated successfully!";
            $messageType = "success";
        } else {
            throw new Exception("Failed to write configuration file.");
        }
        
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
        $messageType = "danger";
    }
}

// Load current settings
$current_settings = [];
if (file_exists('config.php')) {
    include 'config.php';
    $current_settings = [
        'smtp_host' => defined('SMTP_HOST') ? SMTP_HOST : '',
        'smtp_port' => defined('SMTP_PORT') ? SMTP_PORT : 587,
        'smtp_username' => defined('SMTP_USERNAME') ? SMTP_USERNAME : '',
        'smtp_password' => defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '',
        'smtp_encryption' => defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls',
        'from_email' => defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : '',
        'from_name' => defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'M25 Travel & Tour Agency'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMTP Setup - M25 Travel & Tour Agency</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; padding: 20px; }
        .container { max-width: 800px; }
        .card { box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .card-header { background: #13357B; color: white; }
        .btn-primary { background: #13357B; border-color: #13357B; }
        .btn-primary:hover { background: #FEA116; border-color: #FEA116; }
        .preset-btn { margin: 5px; }
        .info-box { background: #e7f3ff; border: 1px solid #b8daff; padding: 15px; border-radius: 5px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3 class="mb-0"><i class="fas fa-cog"></i> SMTP Configuration Setup</h3>
            </div>
            <div class="card-body">
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $messageType; ?>">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                
                <div class="info-box">
                    <h5>Quick Setup Presets for SiteGround:</h5>
                    <button type="button" class="btn btn-outline-primary btn-sm preset-btn" onclick="setSiteGroundSMTP()">
                        SiteGround SMTP
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm preset-btn" onclick="setSiteGroundLocal()">
                        SiteGround Localhost
                    </button>
                    <button type="button" class="btn btn-outline-info btn-sm preset-btn" onclick="setGmailSMTP()">
                        Gmail SMTP
                    </button>
                </div>
                
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="smtp_host" class="form-label">SMTP Host:</label>
                                <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                       value="<?php echo htmlspecialchars($current_settings['smtp_host'] ?? ''); ?>" 
                                       placeholder="smtp.siteground.com" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="smtp_port" class="form-label">Port:</label>
                                <input type="number" class="form-control" id="smtp_port" name="smtp_port" 
                                       value="<?php echo htmlspecialchars($current_settings['smtp_port'] ?? '587'); ?>" 
                                       placeholder="587" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="smtp_username" class="form-label">SMTP Username (Email):</label>
                                <input type="email" class="form-control" id="smtp_username" name="smtp_username" 
                                       value="<?php echo htmlspecialchars($current_settings['smtp_username'] ?? ''); ?>" 
                                       placeholder="noreply@wp.m25travelagency.com">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="smtp_encryption" class="form-label">Encryption:</label>
                                <select class="form-select" id="smtp_encryption" name="smtp_encryption">
                                    <option value="tls" <?php echo ($current_settings['smtp_encryption'] ?? '') === 'tls' ? 'selected' : ''; ?>>TLS</option>
                                    <option value="ssl" <?php echo ($current_settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                    <option value="" <?php echo ($current_settings['smtp_encryption'] ?? '') === '' ? 'selected' : ''; ?>>None</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="smtp_password" class="form-label">SMTP Password:</label>
                        <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                               value="<?php echo htmlspecialchars($current_settings['smtp_password'] ?? ''); ?>" 
                               placeholder="Your email password">
                        <div class="form-text">Leave empty if using localhost without authentication.</div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="from_email" class="form-label">From Email Address:</label>
                                <input type="email" class="form-control" id="from_email" name="from_email" 
                                       value="<?php echo htmlspecialchars($current_settings['from_email'] ?? ''); ?>" 
                                       placeholder="noreply@wp.m25travelagency.com" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="from_name" class="form-label">From Name:</label>
                                <input type="text" class="form-control" id="from_name" name="from_name" 
                                       value="<?php echo htmlspecialchars($current_settings['from_name'] ?? 'M25 Travel & Tour Agency'); ?>" 
                                       placeholder="M25 Travel & Tour Agency" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Configuration
                        </button>
                        <a href="test-email.php" class="btn btn-success">
                            <i class="fas fa-envelope"></i> Test Email
                        </a>
                    </div>
                </form>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5>SiteGround SMTP Settings:</h5>
                        <ul>
                            <li><strong>Host:</strong> smtp.siteground.com</li>
                            <li><strong>Port:</strong> 587 (TLS) or 465 (SSL)</li>
                            <li><strong>Authentication:</strong> Required</li>
                            <li><strong>Username:</strong> Your full email address</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h5>SiteGround Localhost Settings:</h5>
                        <ul>
                            <li><strong>Host:</strong> localhost</li>
                            <li><strong>Port:</strong> 25</li>
                            <li><strong>Authentication:</strong> Not required</li>
                            <li><strong>Encryption:</strong> None</li>
                        </ul>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setSiteGroundSMTP() {
            document.getElementById('smtp_host').value = 'smtp.siteground.com';
            document.getElementById('smtp_port').value = '587';
            document.getElementById('smtp_encryption').value = 'tls';
            document.getElementById('smtp_username').value = 'noreply@wp.m25travelagency.com';
            document.getElementById('from_email').value = 'noreply@wp.m25travelagency.com';
        }
        
        function setSiteGroundLocal() {
            document.getElementById('smtp_host').value = 'localhost';
            document.getElementById('smtp_port').value = '25';
            document.getElementById('smtp_encryption').value = '';
            document.getElementById('smtp_username').value = '';
            document.getElementById('smtp_password').value = '';
            document.getElementById('from_email').value = 'noreply@wp.m25travelagency.com';
        }
        
        function setGmailSMTP() {
            document.getElementById('smtp_host').value = 'smtp.gmail.com';
            document.getElementById('smtp_port').value = '587';
            document.getElementById('smtp_encryption').value = 'tls';
            document.getElementById('smtp_username').value = '';
            document.getElementById('from_email').value = '';
        }
    </script>
</body>
</html>
