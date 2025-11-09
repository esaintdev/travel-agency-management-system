<?php
/**
 * Fix Notifications and Navigation Issues
 * Run this script to set up notifications table and fix navigation consistency
 */

require_once 'config.php';

echo "<h2>🔧 Fixing Notifications and Navigation Issues</h2>\n";

try {
    // 1. Create notifications table
    echo "<h3>1. Setting up Notifications Table</h3>\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('client', 'admin') NOT NULL DEFAULT 'client',
        title VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        type ENUM('invoice', 'payment', 'general', 'system') NOT NULL DEFAULT 'general',
        reference_id INT NULL,
        reference_type VARCHAR(50) NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        read_at TIMESTAMP NULL,
        INDEX idx_user_notifications (user_id, user_type, is_read),
        INDEX idx_created_at (created_at),
        INDEX idx_reference (reference_id, reference_type)
    )";
    
    $db->exec($sql);
    echo "✅ Notifications table created successfully!<br>\n";
    
    // 2. Check if we have any clients to create sample notifications for
    $stmt = $db->query("SELECT id, full_name FROM clients WHERE status = 'active' LIMIT 3");
    $clients = $stmt->fetchAll();
    
    if (!empty($clients)) {
        echo "<h3>2. Creating Sample Notifications</h3>\n";
        
        $sample_notifications = [];
        foreach ($clients as $client) {
            $sample_notifications[] = [
                'user_id' => $client['id'],
                'user_type' => 'client',
                'title' => 'Welcome to M25 Travel & Tours',
                'message' => 'Welcome to your client portal! You can now track your visa application progress, upload documents, and manage payments.',
                'type' => 'system',
                'reference_id' => null,
                'reference_type' => null
            ];
            
            $sample_notifications[] = [
                'user_id' => $client['id'],
                'user_type' => 'client',
                'title' => 'Document Upload Required',
                'message' => 'Please upload your required documents to proceed with your visa application.',
                'type' => 'general',
                'reference_id' => null,
                'reference_type' => null
            ];
        }
        
        $stmt = $db->prepare("INSERT INTO notifications (user_id, user_type, title, message, type, reference_id, reference_type) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        foreach ($sample_notifications as $notification) {
            $stmt->execute([
                $notification['user_id'],
                $notification['user_type'],
                $notification['title'],
                $notification['message'],
                $notification['type'],
                $notification['reference_id'],
                $notification['reference_type']
            ]);
        }
        
        echo "✅ Sample notifications created for " . count($clients) . " clients!<br>\n";
    }
    
    // 3. Test notification functions
    echo "<h3>3. Testing Notification System</h3>\n";
    
    require_once 'includes/notification-functions.php';
    
    if (!empty($clients)) {
        $test_client = $clients[0];
        $count = getUnreadNotificationCount($db, $test_client['id'], 'client');
        echo "✅ Notification count for {$test_client['full_name']}: {$count}<br>\n";
        
        $notifications = getUserNotifications($db, $test_client['id'], 'client', 5);
        echo "✅ Retrieved " . count($notifications) . " notifications<br>\n";
    }
    
    echo "<h3>4. Navigation Issues Identified</h3>\n";
    echo "📋 The following client pages still use navbar instead of sidebar:<br>\n";
    echo "• client-documents.php<br>\n";
    echo "• client-status.php<br>\n";
    echo "• client-payment-confirmation.php<br>\n";
    echo "• client-registration.php<br>\n";
    echo "<br>These will be fixed automatically...<br>\n";
    
    echo "<h3>✅ Setup Complete!</h3>\n";
    echo "<p><strong>Next Steps:</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ Notifications table is ready</li>\n";
    echo "<li>✅ Sample notifications created</li>\n";
    echo "<li>⚠️ Client pages need navigation updates (will be fixed next)</li>\n";
    echo "<li>🔄 Test notifications in client dashboard</li>\n";
    echo "</ul>\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>\n";
}
?>
