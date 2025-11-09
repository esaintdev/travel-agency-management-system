<?php
require_once '../config.php';
require_once '../includes/client-auth.php';
require_once '../includes/notification-functions.php';

header('Content-Type: application/json');

// Check if client is logged in
if (!isClientLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$client = getCurrentClient($db);
if (!$client) {
    http_response_code(401);
    echo json_encode(['error' => 'Invalid session']);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'count':
        // Get unread notification count
        $count = getUnreadNotificationCount($db, $client['id'], 'client');
        echo json_encode(['count' => $count]);
        break;
        
    case 'list':
        // Get notifications list
        $limit = intval($_GET['limit'] ?? 10);
        $unread_only = isset($_GET['unread_only']) && $_GET['unread_only'] === 'true';
        
        $notifications = getUserNotifications($db, $client['id'], 'client', $limit, $unread_only);
        
        // Format notifications for display
        $formatted_notifications = [];
        foreach ($notifications as $notification) {
            $formatted_notifications[] = [
                'id' => $notification['id'],
                'title' => $notification['title'],
                'message' => $notification['message'],
                'type' => $notification['type'],
                'icon' => getNotificationIcon($notification['type']),
                'color' => getNotificationColor($notification['type']),
                'is_read' => (bool)$notification['is_read'],
                'created_at' => $notification['created_at'],
                'time_ago' => formatNotificationTime($notification['created_at']),
                'reference_id' => $notification['reference_id'],
                'reference_type' => $notification['reference_type']
            ];
        }
        
        echo json_encode(['notifications' => $formatted_notifications]);
        break;
        
    case 'mark_read':
        // Mark specific notification as read
        $notification_id = intval($_POST['notification_id'] ?? 0);
        
        if ($notification_id > 0) {
            $success = markNotificationAsRead($db, $notification_id, $client['id'], 'client');
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid notification ID']);
        }
        break;
        
    case 'mark_all_read':
        // Mark all notifications as read
        $success = markAllNotificationsAsRead($db, $client['id'], 'client');
        echo json_encode(['success' => $success]);
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
