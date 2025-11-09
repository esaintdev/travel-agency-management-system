<?php
require_once 'config.php';

try {
    // Create notifications table
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
    echo "✅ Notifications table created successfully!\n";
    
    // Add sample notifications for testing
    $sample_notifications = [
        [
            'user_id' => 1,
            'user_type' => 'client',
            'title' => 'New Invoice Created',
            'message' => 'A new invoice has been created for your visa application. Please review and make payment.',
            'type' => 'invoice',
            'reference_id' => 1,
            'reference_type' => 'invoice'
        ],
        [
            'user_id' => 1,
            'user_type' => 'admin',
            'title' => 'Payment Received',
            'message' => 'Client has made a payment for invoice #INV-001. Please review and confirm.',
            'type' => 'payment',
            'reference_id' => 1,
            'reference_type' => 'payment'
        ]
    ];
    
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
    
    echo "✅ Sample notifications added successfully!\n";
    echo "🎉 Notification system setup complete!\n";
    
} catch (PDOException $e) {
    echo "❌ Error creating notifications table: " . $e->getMessage() . "\n";
}
?>
