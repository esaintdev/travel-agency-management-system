<?php
/**
 * Client Login Processing Script
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Check if config exists
if (!file_exists('config.php')) {
    $_SESSION['error_message'] = "System not configured. Please contact support.";
    header('Location: client-login.php');
    exit();
}

try {
    require_once 'config.php';
} catch (Exception $e) {
    $_SESSION['error_message'] = "Configuration error: " . $e->getMessage();
    header('Location: client-login.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: client-login.php');
    exit();
}

try {
    // Test database connection
    if (!isset($db)) {
        throw new Exception("Database connection not available");
    }
    
    // Validate required fields
    if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception("Please enter both email and password.");
    }
    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }
    
    // Check if client exists and get their data
    $stmt = $db->prepare("SELECT id, reference_id, full_name, client_email, password_hash, status FROM clients WHERE client_email = ?");
    $stmt->execute([$email]);
    $client = $stmt->fetch();
    
    if (!$client) {
        throw new Exception("Invalid email or password.");
    }
    
    // Check if account is active
    if ($client['status'] !== 'Active') {
        throw new Exception("Your account is inactive. Please contact support.");
    }
    
    // Verify password
    if (!password_verify($password, $client['password_hash'])) {
        throw new Exception("Invalid email or password.");
    }
    
    // Login successful - create session
    $_SESSION['client_logged_in'] = true;
    $_SESSION['client_id'] = $client['id'];
    $_SESSION['client_reference_id'] = $client['reference_id'];
    $_SESSION['client_name'] = $client['full_name'];
    $_SESSION['client_email'] = $client['client_email'];
    $_SESSION['client_login_time'] = time();
    
    // Handle "Remember Me" functionality
    if (isset($_POST['remember_me']) && $_POST['remember_me'] == 'on') {
        // Set a cookie that expires in 30 days
        $remember_token = bin2hex(random_bytes(32));
        setcookie('client_remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
        
        // Store the token in database (you might want to create a remember_tokens table)
        // For now, we'll just extend the session
        $_SESSION['remember_me'] = true;
    }
    
    // Log successful login
    error_log("Client login successful: ID {$client['id']}, Reference: {$client['reference_id']}, Email: {$email}");
    
    // Update last login time (optional - you might want to add this field to the database)
    try {
        $stmt = $db->prepare("UPDATE clients SET updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$client['id']]);
    } catch (Exception $e) {
        // Don't fail login if update fails
        error_log("Failed to update last login time: " . $e->getMessage());
    }
    
    // Redirect to dashboard
    header('Location: client-dashboard.php');
    exit();
    
} catch (Exception $e) {
    // Log the error
    error_log("Client login error: " . $e->getMessage());
    error_log("Login attempt for email: " . ($_POST['email'] ?? 'not provided'));
    
    // Set error message and redirect back
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: client-login.php');
    exit();
}
?>
