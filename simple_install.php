<?php
/**
 * M25 Travel & Tour Agency - Simple Installation Script
 * Alternative installer that creates database schema directly in PHP
 */

$error = '';
$success = '';
$step = $_GET['step'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($step == 1) {
        // Database setup
        $host = $_POST['db_host'] ?? 'localhost';
        $username = $_POST['db_user'] ?? 'root';
        $password = $_POST['db_pass'] ?? '';
        $database = $_POST['db_name'] ?? 'm25_travel_agency';
        
        try {
            // Connect and create database
            $pdo = new PDO("mysql:host=$host", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $pdo->exec("USE `$database`");
            
            // Create tables directly in PHP
            
            // 1. Clients table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS clients (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    reference_id VARCHAR(20) UNIQUE NOT NULL,
                    full_name VARCHAR(255) NOT NULL,
                    gender ENUM('Male', 'Female', 'Other') NOT NULL,
                    date_of_birth DATE NOT NULL,
                    country VARCHAR(100) NOT NULL,
                    mobile_number VARCHAR(20) NOT NULL,
                    our_number_for_client VARCHAR(50),
                    client_email VARCHAR(255) NOT NULL UNIQUE,
                    our_email VARCHAR(255),
                    password_hash VARCHAR(255) NOT NULL,
                    ghana_card_number VARCHAR(50),
                    momo_number VARCHAR(20),
                    passport_number VARCHAR(50),
                    visa_type ENUM('Visit', 'Work', 'Study', 'Other') NOT NULL,
                    work_type VARCHAR(100),
                    submitted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    date_contract_started DATE,
                    visa_application_hold ENUM('Yes', 'No') DEFAULT 'No',
                    visa_denial_appeal ENUM('Yes', 'No') DEFAULT 'No',
                    immigration_history TEXT,
                    result_outcome VARCHAR(255),
                    deposit_paid DECIMAL(10,2) DEFAULT 0.00,
                    balance_due DECIMAL(10,2) DEFAULT 0.00,
                    address TEXT NOT NULL,
                    bank_name VARCHAR(100),
                    bank_branch VARCHAR(100),
                    account_name VARCHAR(255),
                    account_number VARCHAR(50),
                    visa_pin VARCHAR(20),
                    spouse_name VARCHAR(255),
                    spouse_dob DATE,
                    spouse_address TEXT,
                    spouse_status VARCHAR(100),
                    father_name VARCHAR(255),
                    father_dob DATE,
                    father_address TEXT,
                    father_status VARCHAR(100),
                    mother_name VARCHAR(255),
                    mother_dob DATE,
                    mother_address TEXT,
                    mother_status VARCHAR(100),
                    child1_name VARCHAR(255),
                    child1_dob DATE,
                    child1_address TEXT,
                    child1_status VARCHAR(100),
                    child2_name VARCHAR(255),
                    child2_dob DATE,
                    child2_address TEXT,
                    child2_status VARCHAR(100),
                    child3_name VARCHAR(255),
                    child3_dob DATE,
                    child3_address TEXT,
                    child3_status VARCHAR(100),
                    status ENUM('Pending', 'Processing', 'Approved', 'Rejected', 'Completed') DEFAULT 'Pending',
                    documents_uploaded BOOLEAN DEFAULT FALSE,
                    documents_verified BOOLEAN DEFAULT FALSE,
                    documents_notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // 2. Admin users table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS admin_users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    email VARCHAR(255) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    full_name VARCHAR(255) NOT NULL,
                    role ENUM('Super Admin', 'Admin', 'Staff') DEFAULT 'Staff',
                    status ENUM('Active', 'Inactive') DEFAULT 'Active',
                    last_login TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // 3. Activity logs table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS activity_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    admin_id INT,
                    client_id INT,
                    action VARCHAR(100) NOT NULL,
                    description TEXT,
                    ip_address VARCHAR(45),
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE SET NULL,
                    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
                )
            ");
            
            // 4. Email templates table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS email_templates (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    template_name VARCHAR(100) UNIQUE NOT NULL,
                    template_type ENUM('client_registration', 'admin_notification', 'status_update', 'reminder', 'custom') NOT NULL,
                    subject VARCHAR(255) NOT NULL,
                    body_html TEXT NOT NULL,
                    body_text TEXT,
                    variables TEXT COMMENT 'JSON array of available variables',
                    is_active BOOLEAN DEFAULT TRUE,
                    created_by INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE SET NULL
                )
            ");
            
            // 5. Email logs table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS email_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    template_id INT,
                    recipient_email VARCHAR(255) NOT NULL,
                    recipient_name VARCHAR(255),
                    subject VARCHAR(255) NOT NULL,
                    body_html TEXT,
                    body_text TEXT,
                    status ENUM('pending', 'sent', 'failed') DEFAULT 'pending',
                    sent_at TIMESTAMP NULL,
                    error_message TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (template_id) REFERENCES email_templates(id) ON DELETE SET NULL
                )
            ");
            
            // 6. Client documents table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS client_documents (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    client_id INT NOT NULL,
                    document_type ENUM('passport', 'photo', 'bank_statement', 'employment_letter', 'invitation_letter', 'travel_itinerary', 'accommodation_proof', 'financial_proof', 'medical_certificate', 'other') NOT NULL,
                    document_name VARCHAR(255) NOT NULL,
                    original_filename VARCHAR(255) NOT NULL,
                    file_path VARCHAR(500) NOT NULL,
                    file_size INT NOT NULL,
                    mime_type VARCHAR(100) NOT NULL,
                    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    uploaded_by_admin INT,
                    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
                    notes TEXT,
                    is_required BOOLEAN DEFAULT FALSE,
                    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
                    FOREIGN KEY (uploaded_by_admin) REFERENCES admin_users(id) ON DELETE SET NULL
                )
            ");
            
            // 7. Document requirements table
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS document_requirements (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    visa_type ENUM('Visit', 'Work', 'Study', 'Other') NOT NULL,
                    document_type ENUM('passport', 'photo', 'bank_statement', 'employment_letter', 'invitation_letter', 'travel_itinerary', 'accommodation_proof', 'financial_proof', 'medical_certificate', 'other') NOT NULL,
                    is_required BOOLEAN DEFAULT TRUE,
                    description TEXT,
                    max_file_size INT DEFAULT 5242880,
                    allowed_extensions VARCHAR(255) DEFAULT 'jpg,jpeg,png,pdf,doc,docx',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
            
            // Insert default admin user
            $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
            $pdo->exec("
                INSERT IGNORE INTO admin_users (username, email, password_hash, full_name, role) 
                VALUES ('admin', 'admin@m25travel.com', '$admin_password', 'System Administrator', 'Super Admin')
            ");
            
            // Insert default email templates
            $pdo->exec("
                INSERT IGNORE INTO email_templates (template_name, template_type, subject, body_html, body_text, variables) VALUES
                ('client_registration_confirmation', 'client_registration', 'Registration Confirmation - M25 Travel & Tour Agency', 
                '<h2>Welcome {{client_name}}!</h2><p>Your reference ID is: <strong>{{reference_id}}</strong></p>', 
                'Welcome {{client_name}}! Your reference ID is: {{reference_id}}', 
                '[\"client_name\", \"reference_id\", \"visa_type\", \"country\"]'),
                ('admin_new_registration', 'admin_notification', 'New Client Registration - {{reference_id}}', 
                '<h3>New client registered</h3><p>Name: {{client_name}}<br>Email: {{client_email}}<br>Reference: {{reference_id}}</p>', 
                'New client registered: {{client_name}} ({{reference_id}})', 
                '[\"client_name\", \"client_email\", \"reference_id\", \"visa_type\"]')
            ");
            
            // Insert default document requirements
            $requirements = [
                ['Visit', 'passport', 1, 'Valid passport with at least 6 months validity'],
                ['Visit', 'photo', 1, 'Recent passport-size photograph'],
                ['Visit', 'bank_statement', 1, 'Bank statement for last 3 months'],
                ['Work', 'passport', 1, 'Valid passport with at least 6 months validity'],
                ['Work', 'employment_letter', 1, 'Employment contract or job offer letter'],
                ['Study', 'passport', 1, 'Valid passport with at least 6 months validity'],
                ['Study', 'financial_proof', 1, 'Proof of financial support for studies']
            ];
            
            foreach ($requirements as $req) {
                $pdo->exec("
                    INSERT IGNORE INTO document_requirements (visa_type, document_type, is_required, description) 
                    VALUES ('{$req[0]}', '{$req[1]}', {$req[2]}, '{$req[3]}')
                ");
            }
            
            // Create config.php
            $config_content = "<?php
session_start();

// Database configuration
define('DB_HOST', '$host');
define('DB_NAME', '$database');
define('DB_USER', '$username');
define('DB_PASS', '$password');

// Application configuration
define('APP_NAME', 'M25 Travel & Tour Agency');
define('APP_URL', 'http://localhost/visa-immigration-website-template/');
define('ADMIN_EMAIL', 'admin@m25travel.com');

// Security configuration
define('ENCRYPTION_KEY', '" . bin2hex(random_bytes(32)) . "');
define('SESSION_TIMEOUT', 3600);

// File upload configuration
define('UPLOAD_DIR', 'uploads/');
define('MAX_FILE_SIZE', 5242880);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx']);

// Database connection
try {
    \$db = new PDO(\"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=utf8mb4\", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException \$e) {
    die('Database connection failed');
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

function formatDate(\$date, \$format = 'Y-m-d') {
    return \$date ? date(\$format, strtotime(\$date)) : '';
}

function isLoggedIn() {
    return isset(\$_SESSION['admin_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: admin-login.html');
        exit();
    }
}

function logActivity(\$admin_id, \$client_id, \$action, \$description, \$db) {
    \$stmt = \$db->prepare(\"INSERT INTO activity_logs (admin_id, client_id, action, description, ip_address) VALUES (?, ?, ?, ?, ?)\");
    \$stmt->execute([\$admin_id, \$client_id, \$action, \$description, \$_SERVER['REMOTE_ADDR']]);
}
?>";
            
            file_put_contents('config.php', $config_content);
            
            // Create uploads directory
            if (!file_exists('uploads')) mkdir('uploads', 0755, true);
            if (!file_exists('uploads/clients')) mkdir('uploads/clients', 0755, true);
            
            $success = "Installation completed successfully! Database created with all tables and default data.";
            $step = 2;
            
        } catch (Exception $e) {
            $error = "Installation failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>M25 Simple Installer</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <style>
        body { background: linear-gradient(135deg, #13357B, #FEA116); min-height: 100vh; padding: 20px; }
        .install-card { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); max-width: 600px; margin: 0 auto; }
    </style>
</head>
<body>
    <div class="install-card">
        <div class="text-center mb-4">
            <h2 class="text-primary">M25 Simple Installer</h2>
            <p class="text-muted">Quick setup for XAMPP</p>
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
            <h4><i class="fas fa-database me-2"></i>Database Setup</h4>
            <p>Enter your XAMPP database details:</p>
            
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
                    <input type="password" class="form-control" name="db_pass" placeholder="Leave empty for XAMPP">
                </div>
                <div class="mb-3">
                    <label class="form-label">Database Name</label>
                    <input type="text" class="form-control" name="db_name" value="m25_travel_agency" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-rocket me-2"></i>Install Everything
                </button>
            </form>
            
        <?php elseif ($step == 2): ?>
            <div class="text-center">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h4>Installation Complete!</h4>
                
                <div class="alert alert-info mt-4">
                    <strong>Default Admin Login:</strong><br>
                    Username: <code>admin</code><br>
                    Password: <code>admin123</code>
                </div>
                
                <div class="d-grid gap-2 mt-4">
                    <a href="admin-login.html" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Admin Login
                    </a>
                    <a href="index.html" class="btn btn-success">
                        <i class="fas fa-home me-2"></i>Visit Homepage
                    </a>
                    <a href="check_setup.php" class="btn btn-info">
                        <i class="fas fa-check me-2"></i>Verify Setup
                    </a>
                </div>
                
                <p class="mt-3 text-muted">
                    <small>You can delete this simple_install.php file now.</small>
                </p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
