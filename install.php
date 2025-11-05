<?php
/**
 * M25 Travel & Tour Agency - Installation Script
 * This script helps set up the database and initial configuration
 */

// Check if already installed
if (file_exists('config.php')) {
    $config_content = file_get_contents('config.php');
    if (strpos($config_content, 'INSTALLATION_COMPLETE') !== false) {
        die('System already installed. Delete config.php to reinstall.');
    }
}

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 2) {
        // Database connection test
        $host = $_POST['db_host'] ?? 'localhost';
        $username = $_POST['db_user'] ?? 'root';
        $password = $_POST['db_pass'] ?? '';
        $database = $_POST['db_name'] ?? 'm25_travel_agency';
        
        try {
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database if it doesn't exist
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $pdo->exec("USE `$database`");
            
            // Store connection details in session
            session_start();
            $_SESSION['db_config'] = [
                'host' => $host,
                'user' => $username,
                'pass' => $password,
                'name' => $database
            ];
            
            $success = "Database connection successful!";
            $step = 3;
            
        } catch (PDOException $e) {
            $error = "Database connection failed: " . $e->getMessage();
        }
    } elseif ($step == 3) {
        // Import database schema
        session_start();
        $db_config = $_SESSION['db_config'];
        
        try {
            $pdo = new PDO("mysql:host={$db_config['host']};dbname={$db_config['name']}", 
                          $db_config['user'], $db_config['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Function to execute SQL file in chunks
            function executeSQLFile($pdo, $filename) {
                if (!file_exists($filename)) {
                    return false;
                }
                
                $sql = file_get_contents($filename);
                
                // Remove comments and split by semicolon
                $sql = preg_replace('/--.*$/m', '', $sql);
                $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement)) {
                        $pdo->exec($statement);
                    }
                }
                return true;
            }
            
            // Import schemas one by one
            $imported = [];
            
            if (executeSQLFile($pdo, 'database.sql')) {
                $imported[] = 'Main database schema';
            }
            
            if (executeSQLFile($pdo, 'email-templates-schema.sql')) {
                $imported[] = 'Email templates';
            }
            
            if (executeSQLFile($pdo, 'documents-schema.sql')) {
                $imported[] = 'Document upload system';
            }
            
            if (empty($imported)) {
                throw new Exception("No SQL files found to import");
            }
            
            $success = "Database schema imported successfully! (" . implode(', ', $imported) . ")";
            $step = 4;
            
        } catch (Exception $e) {
            $error = "Schema import failed: " . $e->getMessage();
        }
    } elseif ($step == 4) {
        // Create config file and finish installation
        session_start();
        $db_config = $_SESSION['db_config'];
        
        $admin_email = $_POST['admin_email'] ?? 'admin@m25travel.com';
        $company_name = $_POST['company_name'] ?? 'M25 Travel & Tour Agency';
        
        $config_content = "<?php
// M25 Travel & Tour Agency - Database Configuration
session_start();

// Database configuration
define('DB_HOST', '{$db_config['host']}');
define('DB_NAME', '{$db_config['name']}');
define('DB_USER', '{$db_config['user']}');
define('DB_PASS', '{$db_config['pass']}');

// Application configuration
define('APP_NAME', '$company_name');
define('APP_URL', 'http://localhost/visa-immigration-website-template/');
define('ADMIN_EMAIL', '$admin_email');

// Security configuration
define('ENCRYPTION_KEY', '" . bin2hex(random_bytes(32)) . "');
define('SESSION_TIMEOUT', 3600); // 1 hour in seconds

// File upload configuration
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Email configuration (for notifications)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Change this
define('SMTP_PASSWORD', 'your-app-password'); // Change this
define('SMTP_FROM_EMAIL', 'noreply@m25travel.com');
define('SMTP_FROM_NAME', '$company_name');

// Installation marker
define('INSTALLATION_COMPLETE', true);

// Database connection class
class Database {
    private \$connection;
    
    public function __construct() {
        try {
            \$this->connection = new PDO(
                \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException \$e) {
            error_log(\"Database connection failed: \" . \$e->getMessage());
            die(\"Database connection failed. Please try again later.\");
        }
    }
    
    public function getConnection() {
        return \$this->connection;
    }
    
    public function prepare(\$sql) {
        return \$this->connection->prepare(\$sql);
    }
    
    public function lastInsertId() {
        return \$this->connection->lastInsertId();
    }
    
    public function beginTransaction() {
        return \$this->connection->beginTransaction();
    }
    
    public function commit() {
        return \$this->connection->commit();
    }
    
    public function rollback() {
        return \$this->connection->rollback();
    }
}

// Utility functions
function sanitizeInput(\$data) {
    return htmlspecialchars(strip_tags(trim(\$data)), ENT_QUOTES, 'UTF-8');
}

function validateEmail(\$email) {
    return filter_var(\$email, FILTER_VALIDATE_EMAIL);
}

function generateSecurePassword(\$password) {
    return password_hash(\$password, PASSWORD_DEFAULT);
}

function verifyPassword(\$password, \$hash) {
    return password_verify(\$password, \$hash);
}

function generateReferenceId() {
    \$year = date('Y');
    \$random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    return \"M25-{\$year}-{\$random}\";
}

function logActivity(\$admin_id, \$client_id, \$action, \$description, \$db) {
    \$sql = \"INSERT INTO activity_logs (admin_id, client_id, action, description, ip_address) 
            VALUES (?, ?, ?, ?, ?)\";
    \$stmt = \$db->prepare(\$sql);
    \$stmt->execute([\$admin_id, \$client_id, \$action, \$description, \$_SERVER['REMOTE_ADDR']]);
}

function isLoggedIn() {
    return isset(\$_SESSION['admin_id']) && isset(\$_SESSION['admin_username']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: admin-login.html');
        exit();
    }
}

function checkSessionTimeout() {
    if (isset(\$_SESSION['last_activity']) && (time() - \$_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        session_unset();
        session_destroy();
        return false;
    }
    \$_SESSION['last_activity'] = time();
    return true;
}

function sendEmail(\$to, \$subject, \$message, \$isHTML = true) {
    // Basic email function - you can integrate with PHPMailer or similar
    \$headers = \"From: \" . SMTP_FROM_NAME . \" <\" . SMTP_FROM_EMAIL . \">\\r\\n\";
    \$headers .= \"Reply-To: \" . SMTP_FROM_EMAIL . \"\\r\\n\";
    
    if (\$isHTML) {
        \$headers .= \"Content-Type: text/html; charset=UTF-8\\r\\n\";
    }
    
    return mail(\$to, \$subject, \$message, \$headers);
}

function formatCurrency(\$amount) {
    return 'GHS ' . number_format(\$amount, 2);
}

function formatDate(\$date, \$format = 'Y-m-d') {
    if (\$date) {
        return date(\$format, strtotime(\$date));
    }
    return '';
}

// Error handling
function handleError(\$message, \$redirect = null) {
    error_log(\$message);
    \$_SESSION['error'] = \$message;
    
    if (\$redirect) {
        header(\"Location: \$redirect\");
        exit();
    }
}

function handleSuccess(\$message, \$redirect = null) {
    \$_SESSION['success'] = \$message;
    
    if (\$redirect) {
        header(\"Location: \$redirect\");
        exit();
    }
}

// Get flash messages
function getFlashMessage(\$type) {
    if (isset(\$_SESSION[\$type])) {
        \$message = \$_SESSION[\$type];
        unset(\$_SESSION[\$type]);
        return \$message;
    }
    return null;
}

// Initialize database connection
try {
    \$db = new Database();
} catch (Exception \$e) {
    error_log(\"Failed to initialize database: \" . \$e->getMessage());
    die(\"System temporarily unavailable. Please try again later.\");
}
?>";

        // Write config file
        if (file_put_contents('config.php', $config_content)) {
            // Create uploads directory
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            if (!file_exists('uploads/clients')) {
                mkdir('uploads/clients', 0755, true);
            }
            
            $success = "Installation completed successfully!";
            $step = 5;
            
            // Clear session
            session_destroy();
        } else {
            $error = "Failed to create configuration file. Check file permissions.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>M25 Travel & Tour Agency - Installation</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <style>
        body { background: linear-gradient(135deg, #13357B, #FEA116); min-height: 100vh; }
        .install-container { max-width: 600px; margin: 50px auto; }
        .install-card { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .step-indicator { display: flex; justify-content: center; margin-bottom: 30px; }
        .step { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 10px; }
        .step.active { background: #13357B; color: white; }
        .step.completed { background: #28a745; color: white; }
        .step.pending { background: #e9ecef; color: #6c757d; }
    </style>
</head>
<body>
    <div class="install-container">
        <div class="install-card">
            <div class="text-center mb-4">
                <h2 class="text-primary">M25 Travel & Tour Agency</h2>
                <p class="text-muted">Installation Wizard</p>
            </div>
            
            <!-- Step Indicator -->
            <div class="step-indicator">
                <div class="step <?php echo $step >= 1 ? ($step > 1 ? 'completed' : 'active') : 'pending'; ?>">1</div>
                <div class="step <?php echo $step >= 2 ? ($step > 2 ? 'completed' : 'active') : 'pending'; ?>">2</div>
                <div class="step <?php echo $step >= 3 ? ($step > 3 ? 'completed' : 'active') : 'pending'; ?>">3</div>
                <div class="step <?php echo $step >= 4 ? ($step > 4 ? 'completed' : 'active') : 'pending'; ?>">4</div>
                <div class="step <?php echo $step >= 5 ? 'completed' : 'pending'; ?>">5</div>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($step == 1): ?>
                <!-- Step 1: Welcome -->
                <div class="text-center">
                    <i class="fas fa-rocket fa-3x text-primary mb-3"></i>
                    <h4>Welcome to M25 Installation</h4>
                    <p>This wizard will help you set up your visa and immigration management system.</p>
                    
                    <h6 class="mt-4 mb-3">Prerequisites Check:</h6>
                    <div class="list-group">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            PHP Version (7.4+)
                            <span class="badge bg-<?php echo version_compare(PHP_VERSION, '7.4.0') >= 0 ? 'success' : 'danger'; ?>">
                                <?php echo PHP_VERSION; ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            MySQL Extension
                            <span class="badge bg-<?php echo extension_loaded('pdo_mysql') ? 'success' : 'danger'; ?>">
                                <?php echo extension_loaded('pdo_mysql') ? 'Available' : 'Missing'; ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            File Permissions
                            <span class="badge bg-<?php echo is_writable('.') ? 'success' : 'warning'; ?>">
                                <?php echo is_writable('.') ? 'Writable' : 'Check Permissions'; ?>
                            </span>
                        </div>
                    </div>
                    
                    <a href="?step=2" class="btn btn-primary btn-lg mt-4">
                        <i class="fas fa-arrow-right me-2"></i>Start Installation
                    </a>
                </div>
                
            <?php elseif ($step == 2): ?>
                <!-- Step 2: Database Configuration -->
                <h4><i class="fas fa-database me-2"></i>Database Configuration</h4>
                <p>Enter your database connection details:</p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Database Host</label>
                        <input type="text" class="form-control" name="db_host" value="localhost" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Database Username</label>
                        <input type="text" class="form-control" name="db_user" value="root" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Database Password</label>
                        <input type="password" class="form-control" name="db_pass" placeholder="Leave empty for XAMPP default">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Database Name</label>
                        <input type="text" class="form-control" name="db_name" value="m25_travel_agency" required>
                        <small class="form-text text-muted">Database will be created if it doesn't exist</small>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plug me-2"></i>Test Connection
                    </button>
                </form>
                
            <?php elseif ($step == 3): ?>
                <!-- Step 3: Import Schema -->
                <h4><i class="fas fa-download me-2"></i>Import Database Schema</h4>
                <p>Ready to import the database structure and default data.</p>
                
                <div class="list-group mb-4">
                    <div class="list-group-item">
                        <i class="fas fa-table me-2"></i>Main database schema (clients, admin_users, activity_logs)
                    </div>
                    <div class="list-group-item">
                        <i class="fas fa-envelope me-2"></i>Email templates system
                    </div>
                    <div class="list-group-item">
                        <i class="fas fa-file me-2"></i>Document upload system
                    </div>
                    <div class="list-group-item">
                        <i class="fas fa-user-shield me-2"></i>Default admin account (admin/admin123)
                    </div>
                </div>
                
                <form method="POST">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-database me-2"></i>Import Database Schema
                    </button>
                </form>
                
            <?php elseif ($step == 4): ?>
                <!-- Step 4: Configuration -->
                <h4><i class="fas fa-cog me-2"></i>System Configuration</h4>
                <p>Configure your system settings:</p>
                
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Company Name</label>
                        <input type="text" class="form-control" name="company_name" value="M25 Travel & Tour Agency" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Email</label>
                        <input type="email" class="form-control" name="admin_email" value="admin@m25travel.com" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i>Complete Installation
                    </button>
                </form>
                
            <?php elseif ($step == 5): ?>
                <!-- Step 5: Complete -->
                <div class="text-center">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4>Installation Complete!</h4>
                    <p>Your M25 Travel & Tour Agency system is ready to use.</p>
                    
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6>Admin Panel</h6>
                                    <p class="small">Username: admin<br>Password: admin123</p>
                                    <a href="admin-login.html" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h6>Client Portal</h6>
                                    <p class="small">Registration & Documents</p>
                                    <a href="/" class="btn btn-success btn-sm">
                                        <i class="fas fa-home me-2"></i>Visit Site
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-warning mt-4">
                        <strong>Security Note:</strong> Please change the default admin password and configure email settings for production use.
                    </div>
                    
                    <p class="mt-3">
                        <small class="text-muted">
                            You can delete this install.php file for security.
                        </small>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
