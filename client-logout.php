<?php
/**
 * Client Logout Script
 */

// Start session
session_start();

// Include authentication helper
require_once 'includes/client-auth.php';

// Log the logout attempt
if (isset($_SESSION['client_id'])) {
    error_log("Client logout: ID {$_SESSION['client_id']}, Reference: {$_SESSION['client_reference_id']}, Email: {$_SESSION['client_email']}");
}

// Destroy client session
destroyClientSession();

// Set success message
$_SESSION['success_message'] = "You have been successfully logged out.";

// Redirect to login page
header('Location: client-login.php');
exit();
?>
