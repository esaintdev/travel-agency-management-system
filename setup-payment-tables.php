<?php
/**
 * Setup Payment and Invoice Tables
 * Run this script to create the necessary database tables for payment functionality
 */

require_once 'config.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Payment Tables - M25 Travel Agency</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #13357B; border-bottom: 3px solid #FEA116; padding-bottom: 10px; }
        h2 { color: #13357B; margin-top: 30px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ffc107; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        code { background: #f8f9fa; padding: 2px 5px; border-radius: 3px; font-family: 'Courier New', monospace; }
        .btn { display: inline-block; padding: 10px 20px; background: #13357B; color: white; text-decoration: none; border-radius: 5px; margin: 10px 5px 0 0; }
        .btn:hover { background: #0f2a5f; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏦 Payment System Database Setup</h1>
        <p>This script will create the necessary database tables for the payment and invoice system.</p>

<?php
try {
    echo "<h2>Creating Payment Tables</h2>";
    
    // Create invoices table
    $invoices_sql = "
    CREATE TABLE IF NOT EXISTS invoices (
        id INT AUTO_INCREMENT PRIMARY KEY,
        invoice_number VARCHAR(50) UNIQUE NOT NULL,
        client_id INT NOT NULL,
        client_reference_id VARCHAR(50) NOT NULL,
        invoice_type ENUM('deposit', 'balance', 'service_fee', 'additional') DEFAULT 'deposit',
        description TEXT,
        amount DECIMAL(10,2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'USD',
        status ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
        due_date DATE,
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        paid_at TIMESTAMP NULL,
        notes TEXT,
        INDEX idx_client_id (client_id),
        INDEX idx_client_reference (client_reference_id),
        INDEX idx_status (status),
        INDEX idx_due_date (due_date),
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($invoices_sql);
    echo "<div class='success'>✅ Created 'invoices' table successfully</div>";
    
    // Create payments table
    $payments_sql = "
    CREATE TABLE IF NOT EXISTS payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        payment_reference VARCHAR(50) UNIQUE NOT NULL,
        invoice_id INT,
        client_id INT NOT NULL,
        client_reference_id VARCHAR(50) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        currency VARCHAR(3) DEFAULT 'USD',
        payment_method ENUM('bank_transfer', 'credit_card', 'paypal', 'cash', 'mobile_money', 'other') DEFAULT 'bank_transfer',
        payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
        transaction_id VARCHAR(100),
        payment_gateway VARCHAR(50),
        payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        processed_by INT,
        notes TEXT,
        receipt_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_client_id (client_id),
        INDEX idx_client_reference (client_reference_id),
        INDEX idx_invoice_id (invoice_id),
        INDEX idx_payment_status (payment_status),
        INDEX idx_payment_date (payment_date),
        FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
        FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($payments_sql);
    echo "<div class='success'>✅ Created 'payments' table successfully</div>";
    
    // Create payment_items table for detailed payment breakdown
    $payment_items_sql = "
    CREATE TABLE IF NOT EXISTS payment_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        payment_id INT NOT NULL,
        invoice_id INT,
        item_type ENUM('deposit', 'balance', 'service_fee', 'additional', 'refund') DEFAULT 'deposit',
        description VARCHAR(255),
        amount DECIMAL(10,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_payment_id (payment_id),
        INDEX idx_invoice_id (invoice_id),
        FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
        FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $db->exec($payment_items_sql);
    echo "<div class='success'>✅ Created 'payment_items' table successfully</div>";
    
    // Check if balance_due column exists in clients table
    $stmt = $db->query("DESCRIBE clients");
    $columns = $stmt->fetchAll();
    $existing_columns = array_column($columns, 'Field');
    
    if (!in_array('balance_due', $existing_columns)) {
        $db->exec("ALTER TABLE clients ADD COLUMN balance_due DECIMAL(10,2) DEFAULT 0.00 COMMENT 'Outstanding balance amount'");
        echo "<div class='success'>✅ Added 'balance_due' column to clients table</div>";
    } else {
        echo "<div class='info'>ℹ️ 'balance_due' column already exists in clients table</div>";
    }
    
    // Add payment-related columns to clients table if they don't exist
    $payment_columns = [
        'total_amount_due' => 'DECIMAL(10,2) DEFAULT 0.00 COMMENT "Total amount due for services"',
        'total_paid' => 'DECIMAL(10,2) DEFAULT 0.00 COMMENT "Total amount paid by client"',
        'last_payment_date' => 'TIMESTAMP NULL COMMENT "Date of last payment received"'
    ];
    
    foreach ($payment_columns as $column_name => $column_definition) {
        if (!in_array($column_name, $existing_columns)) {
            $db->exec("ALTER TABLE clients ADD COLUMN $column_name $column_definition");
            echo "<div class='success'>✅ Added '$column_name' column to clients table</div>";
        } else {
            echo "<div class='info'>ℹ️ '$column_name' column already exists in clients table</div>";
        }
    }
    
    echo "<h2>Creating Sample Data</h2>";
    
    // Create sample invoice for testing (only if no invoices exist)
    $stmt = $db->query("SELECT COUNT(*) as count FROM invoices");
    $invoice_count = $stmt->fetch()['count'];
    
    if ($invoice_count == 0) {
        // Get a sample client for testing
        $stmt = $db->query("SELECT id, reference_id FROM clients LIMIT 1");
        $sample_client = $stmt->fetch();
        
        if ($sample_client) {
            $invoice_number = 'INV-' . date('Y') . '-' . str_pad(1, 4, '0', STR_PAD_LEFT);
            $sample_invoice_sql = "
            INSERT INTO invoices (invoice_number, client_id, client_reference_id, invoice_type, description, amount, due_date, created_by)
            VALUES (?, ?, ?, 'deposit', 'Initial deposit for visa application services', 500.00, DATE_ADD(CURDATE(), INTERVAL 30 DAY), 1)";
            
            $stmt = $db->prepare($sample_invoice_sql);
            $stmt->execute([$invoice_number, $sample_client['id'], $sample_client['reference_id']]);
            
            echo "<div class='success'>✅ Created sample invoice: $invoice_number</div>";
        } else {
            echo "<div class='warning'>⚠️ No clients found to create sample invoice</div>";
        }
    } else {
        echo "<div class='info'>ℹ️ Invoices already exist, skipping sample data creation</div>";
    }
    
    echo "<div class='success'>";
    echo "<h3>🎉 Payment System Setup Complete!</h3>";
    echo "<p>All payment tables have been created successfully. You can now:</p>";
    echo "<ul>";
    echo "<li>Generate invoices for clients</li>";
    echo "<li>Process payments</li>";
    echo "<li>View payment history</li>";
    echo "<li>Track outstanding balances</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error setting up payment tables: " . $e->getMessage() . "</div>";
    error_log("Payment setup error: " . $e->getMessage());
}
?>

        <h2>Next Steps</h2>
        <div class="info">
            <p>Now that the payment tables are set up, you can access:</p>
            <a href="admin-dashboard.php" class="btn">Admin Dashboard</a>
            <a href="admin-invoices.php" class="btn">Manage Invoices</a>
            <a href="admin-payments.php" class="btn">Payment History</a>
        </div>
    </div>
</body>
</html>
