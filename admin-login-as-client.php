<?php
/**
 * Admin Login as Client - Allows admin to impersonate client accounts
 */

require_once 'config.php';

// Check if user is logged in as admin
requireLogin();

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: admin-login.html?timeout=1');
    exit();
}

// Only allow Super Admin and Admin roles to login as clients
if (!in_array($_SESSION['admin_role'], ['Super Admin', 'Admin'])) {
    $_SESSION['error'] = "Access denied. Only Super Admins and Admins can login as clients.";
    header('Location: admin-dashboard.php');
    exit();
}

// Check if client reference ID is provided
if (!isset($_GET['client_id']) && !isset($_POST['client_id'])) {
    $_SESSION['error'] = "No client specified.";
    header('Location: admin-clients.php');
    exit();
}

$client_id = $_GET['client_id'] ?? $_POST['client_id'];

try {
    // Get client data by reference ID
    $stmt = $db->prepare("SELECT * FROM clients WHERE reference_id = ? AND status = 'Active'");
    $stmt->execute([$client_id]);
    $client = $stmt->fetch();
    
    if (!$client) {
        throw new Exception("Client not found or inactive.");
    }
    
    // Store current admin session data before switching
    $admin_session_backup = [
        'admin_id' => $_SESSION['admin_id'],
        'admin_username' => $_SESSION['admin_username'],
        'admin_email' => $_SESSION['admin_email'],
        'admin_name' => $_SESSION['admin_name'],
        'admin_role' => $_SESSION['admin_role'],
        'last_activity' => $_SESSION['last_activity']
    ];
    
    // Log the admin impersonation activity
    logActivity($_SESSION['admin_id'], $client['id'], 'Admin Impersonation', "Admin logged in as client: {$client['reference_id']} ({$client['full_name']})", $db);
    
    // Set client session variables
    $_SESSION['client_logged_in'] = true;
    $_SESSION['client_id'] = $client['id'];
    $_SESSION['client_reference_id'] = $client['reference_id'];
    $_SESSION['client_name'] = $client['full_name'];
    $_SESSION['client_email'] = $client['client_email'];
    $_SESSION['client_login_time'] = time();
    
    // Store admin session backup for later restoration
    $_SESSION['admin_impersonation'] = $admin_session_backup;
    $_SESSION['admin_impersonation_start'] = time();
    
    // Clear admin session variables (but keep the backup)
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_email']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_role']);
    unset($_SESSION['last_activity']);
    
    // Set success message
    $_SESSION['success_message'] = "You are now logged in as {$client['full_name']} ({$client['reference_id']}).";
    
    // Redirect to client dashboard
    header('Location: client-dashboard.php');
    exit();
    
} catch (Exception $e) {
    error_log("Admin login as client error: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: admin-clients.php');
    exit();
}
?>
