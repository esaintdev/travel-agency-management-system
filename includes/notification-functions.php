<?php
// Notification Functions for M25 Travel & Tour

/**
 * Create a new notification
 */
function createNotification($db, $user_id, $user_type, $title, $message, $type = 'general', $reference_id = null, $reference_type = null) {
    try {
        $stmt = $db->prepare("INSERT INTO notifications (user_id, user_type, title, message, type, reference_id, reference_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$user_id, $user_type, $title, $message, $type, $reference_id, $reference_type]);
    } catch (Exception $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get unread notification count for a user
 */
function getUnreadNotificationCount($db, $user_id, $user_type = 'client') {
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND user_type = ? AND is_read = FALSE");
        $stmt->execute([$user_id, $user_type]);
        return $stmt->fetchColumn();
    } catch (Exception $e) {
        error_log("Error getting notification count: " . $e->getMessage());
        return 0;
    }
}

/**
 * Get notifications for a user
 */
function getUserNotifications($db, $user_id, $user_type = 'client', $limit = 10, $unread_only = false) {
    try {
        $sql = "SELECT * FROM notifications WHERE user_id = ? AND user_type = ?";
        $params = [$user_id, $user_type];
        
        if ($unread_only) {
            $sql .= " AND is_read = FALSE";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;
        
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        return [];
    }
}

/**
 * Mark notification as read
 */
function markNotificationAsRead($db, $notification_id, $user_id, $user_type = 'client') {
    try {
        $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE, read_at = NOW() WHERE id = ? AND user_id = ? AND user_type = ?");
        return $stmt->execute([$notification_id, $user_id, $user_type]);
    } catch (Exception $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Mark all notifications as read for a user
 */
function markAllNotificationsAsRead($db, $user_id, $user_type = 'client') {
    try {
        $stmt = $db->prepare("UPDATE notifications SET is_read = TRUE, read_at = NOW() WHERE user_id = ? AND user_type = ? AND is_read = FALSE");
        return $stmt->execute([$user_id, $user_type]);
    } catch (Exception $e) {
        error_log("Error marking all notifications as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Create invoice notification for client
 */
function createInvoiceNotification($db, $client_id, $invoice_id, $invoice_number, $amount) {
    $title = "New Invoice Created";
    $message = "A new invoice #{$invoice_number} for ${$amount} has been created for your visa application. Please review and make payment.";
    return createNotification($db, $client_id, 'client', $title, $message, 'invoice', $invoice_id, 'invoice');
}

/**
 * Create payment notification for admin
 */
function createPaymentNotification($db, $admin_id, $payment_id, $client_name, $amount) {
    $title = "Payment Received";
    $message = "Payment of ${$amount} received from {$client_name}. Please review and confirm the payment.";
    return createNotification($db, $admin_id, 'admin', $title, $message, 'payment', $payment_id, 'payment');
}

/**
 * Create payment confirmation notification for client
 */
function createPaymentConfirmationNotification($db, $client_id, $payment_id, $invoice_number, $amount) {
    $title = "Payment Confirmed";
    $message = "Your payment of ${$amount} for invoice #{$invoice_number} has been confirmed. Thank you!";
    return createNotification($db, $client_id, 'client', $title, $message, 'payment', $payment_id, 'payment');
}

/**
 * Delete old notifications (cleanup function)
 */
function cleanupOldNotifications($db, $days = 30) {
    try {
        $stmt = $db->prepare("DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
        return $stmt->execute([$days]);
    } catch (Exception $e) {
        error_log("Error cleaning up notifications: " . $e->getMessage());
        return false;
    }
}

/**
 * Get notification icon based on type
 */
function getNotificationIcon($type) {
    switch ($type) {
        case 'invoice':
            return 'fas fa-file-invoice-dollar';
        case 'payment':
            return 'fas fa-credit-card';
        case 'system':
            return 'fas fa-cog';
        default:
            return 'fas fa-bell';
    }
}

/**
 * Get notification color based on type
 */
function getNotificationColor($type) {
    switch ($type) {
        case 'invoice':
            return 'warning';
        case 'payment':
            return 'success';
        case 'system':
            return 'info';
        default:
            return 'primary';
    }
}

/**
 * Format notification time
 */
function formatNotificationTime($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M j, Y', $time);
    }
}
?>
