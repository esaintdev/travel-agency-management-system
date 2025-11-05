<?php
/**
 * Client Authentication Helper Functions
 */

// Check if client is logged in
function isClientLoggedIn() {
    return isset($_SESSION['client_logged_in']) && 
           $_SESSION['client_logged_in'] === true && 
           isset($_SESSION['client_id']) && 
           isset($_SESSION['client_reference_id']);
}

// Require client login - redirect to login page if not logged in
function requireClientLogin() {
    if (!isClientLoggedIn()) {
        $_SESSION['error_message'] = "Please log in to access your dashboard.";
        header('Location: client-login.php');
        exit();
    }
}

// Check client session timeout (optional - 2 hours)
function checkClientSessionTimeout() {
    $timeout = 7200; // 2 hours in seconds
    
    if (isset($_SESSION['client_login_time']) && 
        (time() - $_SESSION['client_login_time'] > $timeout)) {
        
        // Session expired
        destroyClientSession();
        $_SESSION['error_message'] = "Your session has expired. Please log in again.";
        header('Location: client-login.php');
        exit();
    }
    
    // Update last activity time
    $_SESSION['client_login_time'] = time();
    return true;
}

// Destroy client session (logout)
function destroyClientSession() {
    // Remove client-specific session variables
    unset($_SESSION['client_logged_in']);
    unset($_SESSION['client_id']);
    unset($_SESSION['client_reference_id']);
    unset($_SESSION['client_name']);
    unset($_SESSION['client_email']);
    unset($_SESSION['client_login_time']);
    unset($_SESSION['remember_me']);
    
    // Remove remember me cookie if it exists
    if (isset($_COOKIE['client_remember_token'])) {
        setcookie('client_remember_token', '', time() - 3600, '/', '', false, true);
    }
}

// Get current client data
function getCurrentClient($db) {
    if (!isClientLoggedIn()) {
        return null;
    }
    
    try {
        $stmt = $db->prepare("SELECT * FROM clients WHERE id = ? AND status = 'Active'");
        $stmt->execute([$_SESSION['client_id']]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching current client: " . $e->getMessage());
        return null;
    }
}

// Get client progress/status information
function getClientProgress($db, $client_id) {
    try {
        $stmt = $db->prepare("
            SELECT 
                visa_type,
                result_outcome,
                deposit_paid,
                balance_due,
                visa_application_hold,
                visa_denial_appeal,
                status,
                submitted_date,
                updated_at
            FROM clients 
            WHERE id = ?
        ");
        $stmt->execute([$client_id]);
        return $stmt->fetch();
    } catch (Exception $e) {
        error_log("Error fetching client progress: " . $e->getMessage());
        return null;
    }
}

// Calculate application progress percentage
function calculateProgressPercentage($client_data) {
    $progress = 0;
    
    // Basic information submitted
    if (!empty($client_data['full_name'])) $progress += 20;
    
    // Documents/details provided
    if (!empty($client_data['passport_number'])) $progress += 15;
    if (!empty($client_data['address'])) $progress += 15;
    
    // Payment status
    if ($client_data['deposit_paid'] > 0) $progress += 25;
    
    // Application status
    if (!empty($client_data['result_outcome'])) {
        if (strtolower($client_data['result_outcome']) === 'approved') {
            $progress = 100;
        } elseif (strtolower($client_data['result_outcome']) === 'pending') {
            $progress += 15;
        } elseif (strtolower($client_data['result_outcome']) === 'under review') {
            $progress += 10;
        }
    } else {
        $progress += 10; // Application submitted
    }
    
    return min($progress, 100);
}

// Get progress status text
function getProgressStatusText($progress_percentage, $result_outcome = null) {
    if (!empty($result_outcome)) {
        switch (strtolower($result_outcome)) {
            case 'approved':
                return 'Application Approved';
            case 'rejected':
            case 'denied':
                return 'Application Denied';
            case 'pending':
                return 'Under Review';
            case 'interview scheduled':
                return 'Interview Scheduled';
            default:
                return ucfirst($result_outcome);
        }
    }
    
    if ($progress_percentage >= 100) {
        return 'Application Complete';
    } elseif ($progress_percentage >= 75) {
        return 'Final Review';
    } elseif ($progress_percentage >= 50) {
        return 'Processing';
    } elseif ($progress_percentage >= 25) {
        return 'Documentation Review';
    } else {
        return 'Application Submitted';
    }
}

// Get progress color class for Bootstrap
function getProgressColorClass($progress_percentage, $result_outcome = null) {
    if (!empty($result_outcome)) {
        switch (strtolower($result_outcome)) {
            case 'approved':
                return 'success';
            case 'rejected':
            case 'denied':
                return 'danger';
            case 'pending':
                return 'warning';
            default:
                return 'info';
        }
    }
    
    if ($progress_percentage >= 100) {
        return 'success';
    } elseif ($progress_percentage >= 75) {
        return 'info';
    } elseif ($progress_percentage >= 50) {
        return 'primary';
    } else {
        return 'warning';
    }
}

// Format currency for display
function formatClientCurrency($amount) {
    return 'GHS ' . number_format($amount, 2);
}

// Format date for display
function formatClientDate($date, $format = 'M d, Y') {
    if (empty($date) || $date === '0000-00-00') {
        return 'Not specified';
    }
    
    try {
        return date($format, strtotime($date));
    } catch (Exception $e) {
        return 'Invalid date';
    }
}
?>
