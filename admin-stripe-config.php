<?php
/**
 * Admin Stripe Configuration Page
 * Allows admins to configure Stripe API keys and settings
 */

require_once 'config.php';

// Check if user is logged in and has proper permissions
requireLogin();

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: admin-login.html?timeout=1');
    exit();
}

// Only Super Admin can access Stripe configuration
if ($_SESSION['admin_role'] !== 'Super Admin') {
    $_SESSION['error'] = "Access denied. Only Super Admin can configure payment settings.";
    header('Location: admin-dashboard.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    try {
        switch ($_POST['action']) {
            case 'update_stripe_config':
                $publishable_key = trim($_POST['publishable_key']);
                $secret_key = trim($_POST['secret_key']);
                $webhook_secret = trim($_POST['webhook_secret']);
                $environment = $_POST['environment'];
                $default_currency = $_POST['default_currency'];
                
                // Validate keys format
                if (!empty($publishable_key) && !preg_match('/^pk_(test_|live_)/', $publishable_key)) {
                    throw new Exception("Invalid publishable key format. Must start with pk_test_ or pk_live_");
                }
                
                if (!empty($secret_key) && !preg_match('/^sk_(test_|live_)/', $secret_key)) {
                    throw new Exception("Invalid secret key format. Must start with sk_test_ or sk_live_");
                }
                
                if (!empty($webhook_secret) && !preg_match('/^whsec_/', $webhook_secret)) {
                    throw new Exception("Invalid webhook secret format. Must start with whsec_");
                }
                
                // Check if keys match environment
                if ($environment === 'live') {
                    if (!empty($publishable_key) && !str_starts_with($publishable_key, 'pk_live_')) {
                        throw new Exception("Live environment requires live publishable key (pk_live_)");
                    }
                    if (!empty($secret_key) && !str_starts_with($secret_key, 'sk_live_')) {
                        throw new Exception("Live environment requires live secret key (sk_live_)");
                    }
                } else {
                    if (!empty($publishable_key) && !str_starts_with($publishable_key, 'pk_test_')) {
                        throw new Exception("Test environment requires test publishable key (pk_test_)");
                    }
                    if (!empty($secret_key) && !str_starts_with($secret_key, 'sk_test_')) {
                        throw new Exception("Test environment requires test secret key (sk_test_)");
                    }
                }
                
                // Create or update stripe_config table
                $db->exec("
                    CREATE TABLE IF NOT EXISTS stripe_config (
                        id INT PRIMARY KEY AUTO_INCREMENT,
                        publishable_key TEXT,
                        secret_key TEXT,
                        webhook_secret TEXT,
                        environment ENUM('test', 'live') DEFAULT 'test',
                        default_currency VARCHAR(3) DEFAULT 'USD',
                        is_active BOOLEAN DEFAULT TRUE,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        updated_by INT,
                        FOREIGN KEY (updated_by) REFERENCES admin_users(id)
                    )
                ");
                
                // Check if config exists
                $stmt = $db->prepare("SELECT id FROM stripe_config WHERE is_active = 1 LIMIT 1");
                $stmt->execute();
                $existing_config = $stmt->fetch();
                
                if ($existing_config) {
                    // Update existing config
                    $stmt = $db->prepare("
                        UPDATE stripe_config 
                        SET publishable_key = ?, secret_key = ?, webhook_secret = ?, 
                            environment = ?, default_currency = ?, updated_by = ?, updated_at = NOW()
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $publishable_key, $secret_key, $webhook_secret, 
                        $environment, $default_currency, $_SESSION['admin_id'], 
                        $existing_config['id']
                    ]);
                } else {
                    // Insert new config
                    $stmt = $db->prepare("
                        INSERT INTO stripe_config 
                        (publishable_key, secret_key, webhook_secret, environment, default_currency, updated_by)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $publishable_key, $secret_key, $webhook_secret, 
                        $environment, $default_currency, $_SESSION['admin_id']
                    ]);
                }
                
                // Log activity
                logActivity($_SESSION['admin_id'], null, 'Stripe Config Updated', 
                           "Stripe configuration updated to {$environment} environment", $db);
                
                $_SESSION['success'] = "Stripe configuration updated successfully!";
                break;
                
            case 'test_connection':
                // Test Stripe connection with provided keys
                $test_pk = trim($_POST['test_publishable_key']);
                $test_sk = trim($_POST['test_secret_key']);
                
                if (empty($test_sk)) {
                    throw new Exception("Secret key is required for testing connection");
                }
                
                // Simple test - try to retrieve account info
                require_once 'vendor/autoload.php';
                \Stripe\Stripe::setApiKey($test_sk);
                
                try {
                    $account = \Stripe\Account::retrieve();
                    $message = "Connection successful! Account: " . ($account->display_name ?: $account->id);
                    $_SESSION['success'] = $message;
                } catch (\Stripe\Exception\AuthenticationException $e) {
                    throw new Exception("Authentication failed: Invalid API key");
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    throw new Exception("Stripe API Error: " . $e->getMessage());
                }
                break;
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    // Redirect to prevent form resubmission
    header('Location: admin-stripe-config.php');
    exit();
}

// Get current Stripe configuration
$stripe_config = null;
try {
    $stmt = $db->prepare("SELECT * FROM stripe_config WHERE is_active = 1 ORDER BY updated_at DESC LIMIT 1");
    $stmt->execute();
    $stripe_config = $stmt->fetch();
} catch (Exception $e) {
    // Table might not exist yet
    $stripe_config = null;
}

// Available currencies
$currencies = [
    'USD' => 'US Dollar ($)',
    'NGN' => 'Nigerian Naira (₦)',
    'GHS' => 'Ghana Cedis (GH₵)',
    'EUR' => 'Euro (€)',
    'GBP' => 'British Pound (£)',
    'CAD' => 'Canadian Dollar (C$)',
    'AUD' => 'Australian Dollar (A$)',
    'ZAR' => 'South African Rand (R)',
    'KES' => 'Kenyan Shilling (KSh)',
    'EGP' => 'Egyptian Pound (E£)'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Stripe Configuration - M25 Travel & Tour Agency</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .config-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .config-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .key-input {
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        
        .environment-badge {
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
            font-weight: 600;
        }
        
        .test-badge {
            background: #fff3cd;
            color: #856404;
        }
        
        .live-badge {
            background: #d4edda;
            color: #155724;
        }
        
        .security-warning {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
        }
    </style>
</head>

<body>
    <?php include 'includes/admin-sidebar.php'; ?>

    <!-- Page Header -->
    <div class="container-fluid bg-primary py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-6 text-white mb-2">
                        <i class="fab fa-stripe me-3"></i>Stripe Configuration
                    </h1>
                    <p class="text-white-50 mb-0">Configure Stripe payment processing settings</p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="d-inline-block bg-white bg-opacity-20 rounded-pill px-4 py-2">
                        <i class="fas fa-shield-alt me-2"></i>Super Admin Only
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Security Warning -->
        <div class="security-warning">
            <div class="row align-items-center">
                <div class="col-lg-1">
                    <i class="fas fa-shield-alt fa-2x"></i>
                </div>
                <div class="col-lg-11">
                    <h5 class="mb-2">🔒 Security Notice</h5>
                    <p class="mb-0">
                        Stripe API keys are sensitive credentials. Never share them publicly or store them in unsecured locations.
                        Always use test keys during development and switch to live keys only in production.
                    </p>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Current Configuration -->
            <div class="col-lg-4">
                <div class="config-card card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-primary"></i>Current Configuration
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if ($stripe_config): ?>
                            <div class="mb-3">
                                <strong>Environment:</strong>
                                <span class="environment-badge <?php echo $stripe_config['environment'] === 'live' ? 'live-badge' : 'test-badge'; ?>">
                                    <?php echo strtoupper($stripe_config['environment']); ?>
                                </span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Default Currency:</strong>
                                <span class="badge bg-info"><?php echo $stripe_config['default_currency']; ?></span>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Publishable Key:</strong><br>
                                <small class="text-muted font-monospace">
                                    <?php 
                                    if ($stripe_config['publishable_key']) {
                                        echo substr($stripe_config['publishable_key'], 0, 20) . '...';
                                    } else {
                                        echo 'Not configured';
                                    }
                                    ?>
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Secret Key:</strong><br>
                                <small class="text-muted font-monospace">
                                    <?php 
                                    if ($stripe_config['secret_key']) {
                                        echo substr($stripe_config['secret_key'], 0, 20) . '...';
                                    } else {
                                        echo 'Not configured';
                                    }
                                    ?>
                                </small>
                            </div>
                            
                            <div class="mb-3">
                                <strong>Last Updated:</strong><br>
                                <small class="text-muted"><?php echo formatDate($stripe_config['updated_at'], 'M d, Y g:i A'); ?></small>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-exclamation-circle fa-2x text-warning mb-3"></i>
                                <p class="text-muted">No Stripe configuration found.<br>Please configure your API keys.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Configuration Form -->
            <div class="col-lg-8">
                <div class="config-card card">
                    <div class="card-header bg-transparent border-0">
                        <h5 class="mb-0">
                            <i class="fas fa-cog me-2 text-primary"></i>Stripe Settings
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_stripe_config">
                            
                            <!-- Environment Selection -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-server me-2"></i>Environment
                                </label>
                                <select class="form-select" name="environment" required>
                                    <option value="test" <?php echo ($stripe_config && $stripe_config['environment'] === 'test') ? 'selected' : ''; ?>>
                                        Test Mode (Development)
                                    </option>
                                    <option value="live" <?php echo ($stripe_config && $stripe_config['environment'] === 'live') ? 'selected' : ''; ?>>
                                        Live Mode (Production)
                                    </option>
                                </select>
                                <div class="form-text">
                                    Use Test Mode during development. Switch to Live Mode only when ready for production.
                                </div>
                            </div>
                            
                            <!-- Publishable Key -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-key me-2"></i>Publishable Key
                                </label>
                                <input type="text" class="form-control key-input" name="publishable_key" 
                                       value="<?php echo htmlspecialchars($stripe_config['publishable_key'] ?? ''); ?>"
                                       placeholder="pk_test_... or pk_live_...">
                                <div class="form-text">
                                    Starts with pk_test_ (test) or pk_live_ (production). Safe to use in frontend code.
                                </div>
                            </div>
                            
                            <!-- Secret Key -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-lock me-2"></i>Secret Key
                                </label>
                                <input type="password" class="form-control key-input" name="secret_key" 
                                       value="<?php echo htmlspecialchars($stripe_config['secret_key'] ?? ''); ?>"
                                       placeholder="sk_test_... or sk_live_...">
                                <div class="form-text">
                                    Starts with sk_test_ (test) or sk_live_ (production). Keep this secure and private.
                                </div>
                            </div>
                            
                            <!-- Webhook Secret -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-webhook me-2"></i>Webhook Secret (Optional)
                                </label>
                                <input type="password" class="form-control key-input" name="webhook_secret" 
                                       value="<?php echo htmlspecialchars($stripe_config['webhook_secret'] ?? ''); ?>"
                                       placeholder="whsec_...">
                                <div class="form-text">
                                    Used to verify webhook authenticity. Get this from your Stripe webhook settings.
                                </div>
                            </div>
                            
                            <!-- Default Currency -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="fas fa-money-bill me-2"></i>Default Currency
                                </label>
                                <select class="form-select" name="default_currency" required>
                                    <?php foreach ($currencies as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" 
                                            <?php echo ($stripe_config && $stripe_config['default_currency'] === $code) ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Save Configuration
                                </button>
                                
                                <button type="button" class="btn btn-outline-info" onclick="testConnection()">
                                    <i class="fas fa-plug me-2"></i>Test Connection
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Connection Modal -->
    <div class="modal fade" id="testConnectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Stripe Connection</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="test_connection">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Publishable Key</label>
                            <input type="text" class="form-control key-input" name="test_publishable_key" 
                                   placeholder="pk_test_... or pk_live_...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Secret Key</label>
                            <input type="password" class="form-control key-input" name="test_secret_key" 
                                   placeholder="sk_test_... or sk_live_..." required>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This will test the connection to Stripe using the provided keys.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Test Connection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function testConnection() {
            const modal = new bootstrap.Modal(document.getElementById('testConnectionModal'));
            modal.show();
        }
        
        // Auto-fill test modal with current form values
        document.getElementById('testConnectionModal').addEventListener('show.bs.modal', function() {
            const pubKey = document.querySelector('input[name="publishable_key"]').value;
            const secKey = document.querySelector('input[name="secret_key"]').value;
            
            document.querySelector('input[name="test_publishable_key"]').value = pubKey;
            document.querySelector('input[name="test_secret_key"]').value = secKey;
        });
    </script>
    <?php include 'includes/admin-sidebar-close.php'; ?>
</body>
</html>
