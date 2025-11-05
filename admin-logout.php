<?php
require_once 'config.php';

// Log logout activity if user is logged in
if (isLoggedIn()) {
    try {
        logActivity($_SESSION['admin_id'], null, 'Logout', 'Admin logged out', $db);
    } catch (Exception $e) {
        error_log("Logout logging error: " . $e->getMessage());
    }
}

// Clear all session variables
session_unset();

// Destroy the session
session_destroy();

// Clear remember me cookie if it exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Redirect to login page with logout confirmation
header('Location: admin-login.php?logout=1');
exit();
?>
