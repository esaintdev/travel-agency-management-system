<?php
/**
 * Add Sample Client Data for Testing Dashboard
 * This script will add sample client data to test dashboard statistics
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Add Sample Data</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🧪 Add Sample Client Data</h1>";
echo "<p>This will add sample client data to test your dashboard statistics.</p>";

try {
    require_once 'config.php';
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "<div class='success'>✅ Database connected</div>";
    
    // Check if clients table exists
    $stmt = $db->prepare("SHOW TABLES LIKE 'clients'");
    $stmt->execute();
    
    if (!$stmt->fetch()) {
        echo "<div class='error'>❌ Clients table does not exist. Please run the installer first.</div>";
        exit;
    }
    
    // Check if we should add data
    if (isset($_POST['add_sample_data'])) {
        
        echo "<h2>Adding Sample Data...</h2>";
        
        // Sample client data
        $sample_clients = [
            [
                'reference_id' => 'M25-' . date('Y') . '-001',
                'full_name' => 'John Smith',
                'gender' => 'Male',
                'date_of_birth' => '1985-06-15',
                'country' => 'Ghana',
                'mobile_number' => '+233241234567',
                'client_email' => 'john.smith@example.com',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'visa_type' => 'Tourist Visa',
                'address' => '123 Main Street, Accra, Ghana',
                'submitted_date' => date('Y-m-d H:i:s'),
                'status' => 'Active',
                'deposit_paid' => 500.00
            ],
            [
                'reference_id' => 'M25-' . date('Y') . '-002',
                'full_name' => 'Mary Johnson',
                'gender' => 'Female',
                'date_of_birth' => '1990-03-22',
                'country' => 'Nigeria',
                'mobile_number' => '+234801234567',
                'client_email' => 'mary.johnson@example.com',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'visa_type' => 'Business Visa',
                'address' => '456 Lagos Street, Lagos, Nigeria',
                'submitted_date' => date('Y-m-d H:i:s', strtotime('-5 days')),
                'status' => 'Active',
                'deposit_paid' => 750.00
            ],
            [
                'reference_id' => 'M25-' . date('Y') . '-003',
                'full_name' => 'David Wilson',
                'gender' => 'Male',
                'date_of_birth' => '1988-11-08',
                'country' => 'Kenya',
                'mobile_number' => '+254701234567',
                'client_email' => 'david.wilson@example.com',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'visa_type' => 'Student Visa',
                'address' => '789 Nairobi Road, Nairobi, Kenya',
                'submitted_date' => date('Y-m-d H:i:s', strtotime('-2 weeks')),
                'status' => 'Completed',
                'deposit_paid' => 1000.00
            ],
            [
                'reference_id' => 'M25-' . date('Y') . '-004',
                'full_name' => 'Sarah Brown',
                'gender' => 'Female',
                'date_of_birth' => '1992-07-30',
                'country' => 'South Africa',
                'mobile_number' => '+27821234567',
                'client_email' => 'sarah.brown@example.com',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'visa_type' => 'Work Visa',
                'address' => '321 Cape Town Avenue, Cape Town, South Africa',
                'submitted_date' => date('Y-m-d H:i:s', strtotime('-1 month')),
                'status' => 'Active',
                'deposit_paid' => 1200.00
            ],
            [
                'reference_id' => 'M25-' . date('Y') . '-005',
                'full_name' => 'Ahmed Hassan',
                'gender' => 'Male',
                'date_of_birth' => '1987-12-12',
                'country' => 'Egypt',
                'mobile_number' => '+201234567890',
                'client_email' => 'ahmed.hassan@example.com',
                'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
                'visa_type' => 'Tourist Visa',
                'address' => '654 Cairo Street, Cairo, Egypt',
                'submitted_date' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'status' => 'Active',
                'deposit_paid' => 600.00
            ]
        ];
        
        $success_count = 0;
        $error_count = 0;
        
        foreach ($sample_clients as $client) {
            try {
                // Check if client already exists
                $check_stmt = $db->prepare("SELECT reference_id FROM clients WHERE reference_id = ? OR client_email = ?");
                $check_stmt->execute([$client['reference_id'], $client['client_email']]);
                
                if ($check_stmt->fetch()) {
                    echo "<div class='warning'>⚠️ Skipped {$client['full_name']} - already exists</div>";
                    continue;
                }
                
                // Insert client
                $insert_stmt = $db->prepare("
                    INSERT INTO clients (
                        reference_id, full_name, gender, date_of_birth, country, 
                        mobile_number, client_email, password_hash, visa_type, 
                        address, submitted_date, status, deposit_paid, created_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
                ");
                
                $insert_stmt->execute([
                    $client['reference_id'],
                    $client['full_name'],
                    $client['gender'],
                    $client['date_of_birth'],
                    $client['country'],
                    $client['mobile_number'],
                    $client['client_email'],
                    $client['password_hash'],
                    $client['visa_type'],
                    $client['address'],
                    $client['submitted_date'],
                    $client['status'],
                    $client['deposit_paid']
                ]);
                
                echo "<div class='success'>✅ Added: {$client['full_name']} ({$client['reference_id']})</div>";
                $success_count++;
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error adding {$client['full_name']}: " . $e->getMessage() . "</div>";
                $error_count++;
            }
        }
        
        echo "<h3>Summary</h3>";
        echo "<div class='info'>Successfully added: $success_count clients</div>";
        if ($error_count > 0) {
            echo "<div class='error'>Errors: $error_count</div>";
        }
        
        echo "<div class='success'>";
        echo "<h3>🎉 Sample Data Added!</h3>";
        echo "<p>You can now check your dashboard to see the statistics.</p>";
        echo "<a href='admin-dashboard.php' class='btn'>View Dashboard</a>";
        echo "<a href='diagnose-dashboard-issue.php' class='btn'>Run Diagnostic</a>";
        echo "</div>";
        
    } else {
        
        // Show current data count
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM clients");
        $stmt->execute();
        $current_count = $stmt->fetch()['count'];
        
        echo "<div class='info'>Current client records in database: <strong>$current_count</strong></div>";
        
        if ($current_count == 0) {
            echo "<div class='warning'>⚠️ Your database is empty. Adding sample data will help test the dashboard.</div>";
        } else {
            echo "<div class='success'>✅ You already have client data. Adding sample data is optional.</div>";
        }
        
        echo "<h2>Sample Data Preview</h2>";
        echo "<p>This will add 5 sample clients with different statuses and dates to test your dashboard statistics:</p>";
        echo "<ul>";
        echo "<li>3 Active applications (will show in 'Pending Applications')</li>";
        echo "<li>1 Completed application</li>";
        echo "<li>2 applications from this month (will show in 'New This Month')</li>";
        echo "<li>Total revenue of $4,050</li>";
        echo "</ul>";
        
        echo "<form method='POST'>";
        echo "<input type='hidden' name='add_sample_data' value='1'>";
        echo "<button type='submit' class='btn'>Add Sample Data</button>";
        echo "</form>";
        
        echo "<h3>Other Options</h3>";
        echo "<a href='admin-dashboard.php' class='btn'>View Dashboard</a>";
        echo "<a href='diagnose-dashboard-issue.php' class='btn'>Run Diagnostic</a>";
        echo "<a href='client-registration.php' class='btn'>Test Registration Form</a>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
