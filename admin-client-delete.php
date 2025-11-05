<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Check session timeout
if (!checkSessionTimeout()) {
    header('Location: admin-login.php?timeout=1');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['reference_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header('Location: admin-clients.php');
    exit();
}

$reference_id = $_POST['reference_id'];

try {
    // Check if client exists
    $stmt = $db->prepare("SELECT full_name FROM clients WHERE reference_id = ?");
    $stmt->execute([$reference_id]);
    $client = $stmt->fetch();
    
    if (!$client) {
        $_SESSION['error'] = "Client not found.";
        header('Location: admin-clients.php');
        exit();
    }
    
    // Delete the client
    $stmt = $db->prepare("DELETE FROM clients WHERE reference_id = ?");
    $stmt->execute([$reference_id]);
    
    // Log the activity
    logActivity($_SESSION['admin_id'] ?? 1, null, 'DELETE_CLIENT', "Deleted client: {$client['full_name']} ({$reference_id})", $db);
    
    $_SESSION['success'] = "Client '{$client['full_name']}' has been deleted successfully.";
    
} catch (Exception $e) {
    error_log("Delete client error: " . $e->getMessage());
    $_SESSION['error'] = "Error deleting client. Please try again.";
}

header('Location: admin-clients.php');
exit();
?>
