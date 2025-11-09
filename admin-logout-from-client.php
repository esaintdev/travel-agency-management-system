<?php
/**
 * Admin Logout from Client - Returns admin to their original session
 */

session_start();
require_once 'config.php';

// Check if admin is currently impersonating a client
if (!isset($_SESSION['admin_impersonation']) || !isset($_SESSION['client_logged_in'])) {
    // Not impersonating, redirect to appropriate login
    if (isset($_SESSION['client_logged_in'])) {
        header('Location: client-logout.php');
    } else {
        header('Location: admin-login.html');
    }
    exit();
}

try {
    // Get current client info for logging
    $client_reference = $_SESSION['client_reference_id'] ?? 'Unknown';
    $client_name = $_SESSION['client_name'] ?? 'Unknown';
    
    // Restore admin session from backup
    $admin_backup = $_SESSION['admin_impersonation'];
    
    // Clear client session variables
    unset($_SESSION['client_logged_in']);
    unset($_SESSION['client_id']);
    unset($_SESSION['client_reference_id']);
    unset($_SESSION['client_name']);
    unset($_SESSION['client_email']);
    unset($_SESSION['client_login_time']);
    unset($_SESSION['remember_me']);
    
    // Restore admin session variables
    $_SESSION['admin_id'] = $admin_backup['admin_id'];
    $_SESSION['admin_username'] = $admin_backup['admin_username'];
    $_SESSION['admin_email'] = $admin_backup['admin_email'];
    $_SESSION['admin_name'] = $admin_backup['admin_name'];
    $_SESSION['admin_role'] = $admin_backup['admin_role'];
    $_SESSION['last_activity'] = time(); // Update to current time
    
    // Calculate impersonation duration
    $impersonation_duration = time() - ($_SESSION['admin_impersonation_start'] ?? time());
    $duration_minutes = round($impersonation_duration / 60, 1);
    
    // Log the end of impersonation
    logActivity($admin_backup['admin_id'], null, 'Admin Impersonation End', "Admin returned from client session: {$client_reference} ({$client_name}). Duration: {$duration_minutes} minutes", $db);
    
    // Clear impersonation tracking
    unset($_SESSION['admin_impersonation']);
    unset($_SESSION['admin_impersonation_start']);
    
    // Set success message
    $_SESSION['success'] = "Successfully returned to admin session from client: {$client_name} ({$client_reference}).";
    
    // Redirect to admin dashboard
    header('Location: admin-dashboard.php');
    exit();
    
} catch (Exception $e) {
    error_log("Admin logout from client error: " . $e->getMessage());
    
    // Force clear all sessions and redirect to admin login
    session_unset();
    session_destroy();
    header('Location: admin-login.html?error=session_error');
    exit();
}
?>
