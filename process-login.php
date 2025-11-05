<?php
require_once 'config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin-login.html');
    exit();
}

try {
    // Get form data
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember_me = isset($_POST['remember_me']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        throw new Exception("Please fill in all fields.");
    }
    
    // Check user credentials
    $stmt = $db->prepare("SELECT id, username, email, password_hash, full_name, role, status FROM admin_users WHERE username = ? AND status = 'Active'");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if (!$user || !verifyPassword($password, $user['password_hash'])) {
        // Log failed login attempt
        error_log("Failed login attempt for username: $username from IP: " . $_SERVER['REMOTE_ADDR']);
        
        // Add delay to prevent brute force attacks
        sleep(2);
        
        header('Location: admin-login.html?error=1');
        exit();
    }
    
    // Update last login
    $stmt = $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Set session variables
    $_SESSION['admin_id'] = $user['id'];
    $_SESSION['admin_username'] = $user['username'];
    $_SESSION['admin_email'] = $user['email'];
    $_SESSION['admin_name'] = $user['full_name'];
    $_SESSION['admin_role'] = $user['role'];
    $_SESSION['last_activity'] = time();
    
    // Set remember me cookie if requested
    if ($remember_me) {
        $token = bin2hex(random_bytes(32));
        setcookie('remember_token', $token, time() + (30 * 24 * 60 * 60), '/', '', false, true); // 30 days
        
        // Store token in database (you might want to create a remember_tokens table)
        // For now, we'll just use the session
    }
    
    // Log successful login
    logActivity($user['id'], null, 'Login', 'Admin logged in successfully', $db);
    
    // Redirect to dashboard
    header('Location: admin-dashboard.php');
    exit();
    
} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    header('Location: admin-login.html?error=1');
    exit();
}
?>
