<?php
/**
 * Dashboard Statistics Diagnostic Script
 * This script will help identify why dashboard statistics are showing 0
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .stat-box { display: inline-block; margin: 10px; padding: 20px; background: #f8f9fa; border-radius: 10px; text-align: center; min-width: 150px; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007bff; }
        .stat-label { color: #6c757d; margin-top: 5px; }
        code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔍 Dashboard Statistics Diagnostic</h1>";
echo "<p>This diagnostic will help identify why your dashboard statistics are showing 0.</p>";

// Step 1: Test Database Connection
echo "<h2>Step 1: Database Connection Test</h2>";

try {
    // Try to include config.php
    if (file_exists('config.php')) {
        require_once 'config.php';
        echo "<div class='success'>✅ Config file found and loaded</div>";
        
        // Test database connection
        if (isset($db) && $db instanceof PDO) {
            echo "<div class='success'>✅ Database connection established</div>";
            
            // Get database info
            $stmt = $db->query("SELECT DATABASE() as db_name");
            $db_info = $stmt->fetch();
            echo "<div class='info'>📊 Connected to database: <strong>" . $db_info['db_name'] . "</strong></div>";
            
        } else {
            echo "<div class='error'>❌ Database connection failed - \$db variable not set or not a PDO instance</div>";
            
            // Try manual connection with SiteGround credentials
            echo "<h3>Attempting manual connection with SiteGround credentials...</h3>";
            
            $host = 'localhost';
            $dbname = 'dbwceop89t7wf2';
            $username = 'uelcgzv3nvbgs';
            $password = 'd25xmrcdznvf';
            
            try {
                $db = new PDO(
                    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                    $username,
                    $password,
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                echo "<div class='success'>✅ Manual SiteGround connection successful</div>";
            } catch (PDOException $e) {
                echo "<div class='error'>❌ Manual connection failed: " . $e->getMessage() . "</div>";
                echo "<div class='warning'>⚠️ Please check your SiteGround database credentials</div>";
                exit;
            }
        }
        
    } else {
        echo "<div class='error'>❌ Config file not found</div>";
        exit;
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error loading config: " . $e->getMessage() . "</div>";
    exit;
}

// Step 2: Check Tables
echo "<h2>Step 2: Database Tables Check</h2>";

try {
    // Show all tables
    $stmt = $db->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<div class='success'>✅ Found " . count($tables) . " tables: " . implode(', ', $tables) . "</div>";
        
        // Check if clients table exists
        if (in_array('clients', $tables)) {
            echo "<div class='success'>✅ Clients table exists</div>";
        } else {
            echo "<div class='error'>❌ Clients table missing</div>";
            echo "<div class='warning'>⚠️ The 'clients' table is required for dashboard statistics</div>";
        }
        
    } else {
        echo "<div class='error'>❌ No tables found in database</div>";
        echo "<div class='warning'>⚠️ Database appears to be empty. You need to import the database structure.</div>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error checking tables: " . $e->getMessage() . "</div>";
}

// Step 3: Check Clients Table Structure
if (in_array('clients', $tables)) {
    echo "<h2>Step 3: Clients Table Structure</h2>";
    
    try {
        $stmt = $db->query("DESCRIBE clients");
        $columns = $stmt->fetchAll();
        
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        
        $field_names = [];
        foreach ($columns as $column) {
            $field_names[] = $column['Field'];
            echo "<tr>";
            echo "<td>" . htmlspecialchars($column['Field']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Type']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Null']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Key']) . "</td>";
            echo "<td>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        // Check required fields for dashboard queries
        $required_fields = ['reference_id', 'submitted_date', 'status', 'deposit_paid'];
        $missing_fields = [];
        
        foreach ($required_fields as $field) {
            if (!in_array($field, $field_names)) {
                $missing_fields[] = $field;
            }
        }
        
        if (empty($missing_fields)) {
            echo "<div class='success'>✅ All required fields for dashboard statistics are present</div>";
        } else {
            echo "<div class='error'>❌ Missing required fields: " . implode(', ', $missing_fields) . "</div>";
            echo "<div class='warning'>";
            echo "<h4>🔧 Fix Required</h4>";
            echo "<p>Your database is missing columns needed for dashboard statistics.</p>";
            echo "<a href='fix-missing-columns.php' class='btn' style='background: #28a745; color: white; text-decoration: none; padding: 10px 20px; border-radius: 5px; display: inline-block; margin: 5px;'>Fix Missing Columns</a>";
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error checking table structure: " . $e->getMessage() . "</div>";
    }
}

// Step 4: Test Dashboard Queries
if (in_array('clients', $tables)) {
    echo "<h2>Step 4: Dashboard Queries Test</h2>";
    
    try {
        // Test each dashboard query individually
        
        // 1. Total clients
        echo "<h3>Query 1: Total Clients</h3>";
        echo "<code>SELECT COUNT(*) as total FROM clients</code><br>";
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients");
        $stmt->execute();
        $total_clients = $stmt->fetch()['total'];
        echo "<div class='stat-box'><div class='stat-number'>$total_clients</div><div class='stat-label'>Total Clients</div></div>";
        
        // 2. New clients this month
        echo "<h3>Query 2: New Clients This Month</h3>";
        echo "<code>SELECT COUNT(*) as total FROM clients WHERE MONTH(submitted_date) = MONTH(CURRENT_DATE()) AND YEAR(submitted_date) = YEAR(CURRENT_DATE())</code><br>";
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients WHERE MONTH(submitted_date) = MONTH(CURRENT_DATE()) AND YEAR(submitted_date) = YEAR(CURRENT_DATE())");
        $stmt->execute();
        $new_clients_month = $stmt->fetch()['total'];
        echo "<div class='stat-box'><div class='stat-number'>$new_clients_month</div><div class='stat-label'>New This Month</div></div>";
        
        // 3. Pending applications
        echo "<h3>Query 3: Pending Applications</h3>";
        echo "<code>SELECT COUNT(*) as total FROM clients WHERE status = 'Active'</code><br>";
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients WHERE status = 'Active'");
        $stmt->execute();
        $pending_applications = $stmt->fetch()['total'];
        echo "<div class='stat-box'><div class='stat-number'>$pending_applications</div><div class='stat-label'>Pending Applications</div></div>";
        
        // 4. Total revenue
        echo "<h3>Query 4: Total Revenue</h3>";
        echo "<code>SELECT SUM(deposit_paid) as total FROM clients</code><br>";
        try {
            $stmt = $db->prepare("SELECT SUM(deposit_paid) as total FROM clients");
            $stmt->execute();
            $total_revenue = $stmt->fetch()['total'] ?? 0;
            echo "<div class='stat-box'><div class='stat-number'>$total_revenue</div><div class='stat-label'>Total Revenue</div></div>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'deposit_paid') !== false) {
                echo "<div class='error'>❌ Column 'deposit_paid' missing. <a href='fix-missing-columns.php'>Click here to fix</a></div>";
                $total_revenue = 0;
            } else {
                throw $e;
            }
        }
        
        // Summary
        echo "<h3>Summary</h3>";
        if ($total_clients == 0) {
            echo "<div class='warning'>⚠️ No client records found in database. This explains why dashboard shows 0.</div>";
            echo "<div class='info'>💡 Solution: You need to have client registrations in your database for statistics to show.</div>";
        } else {
            echo "<div class='success'>✅ Found $total_clients client records. Dashboard should show these statistics.</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error running dashboard queries: " . $e->getMessage() . "</div>";
    }
}

// Step 5: Check Recent Data
if (in_array('clients', $tables) && $total_clients > 0) {
    echo "<h2>Step 5: Recent Client Data Sample</h2>";
    
    try {
        // First try with all columns
        try {
            $stmt = $db->prepare("SELECT reference_id, full_name, client_email, visa_type, submitted_date, status, deposit_paid FROM clients ORDER BY submitted_date DESC LIMIT 5");
            $stmt->execute();
            $recent_clients = $stmt->fetchAll();
            $has_deposit_column = true;
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'deposit_paid') !== false) {
                // Try without deposit_paid column
                $stmt = $db->prepare("SELECT reference_id, full_name, client_email, visa_type, submitted_date, status FROM clients ORDER BY submitted_date DESC LIMIT 5");
                $stmt->execute();
                $recent_clients = $stmt->fetchAll();
                $has_deposit_column = false;
                echo "<div class='warning'>⚠️ Note: deposit_paid column missing, showing data without it</div>";
            } else {
                throw $e;
            }
        }
        
        if (!empty($recent_clients)) {
            echo "<table>";
            if ($has_deposit_column) {
                echo "<tr><th>Reference ID</th><th>Name</th><th>Email</th><th>Visa Type</th><th>Submitted Date</th><th>Status</th><th>Deposit</th></tr>";
            } else {
                echo "<tr><th>Reference ID</th><th>Name</th><th>Email</th><th>Visa Type</th><th>Submitted Date</th><th>Status</th></tr>";
            }
            
            foreach ($recent_clients as $client) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($client['reference_id']) . "</td>";
                echo "<td>" . htmlspecialchars($client['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($client['client_email']) . "</td>";
                echo "<td>" . htmlspecialchars($client['visa_type'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($client['submitted_date'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($client['status'] ?? 'N/A') . "</td>";
                if ($has_deposit_column) {
                    echo "<td>" . htmlspecialchars($client['deposit_paid'] ?? '0') . "</td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error fetching recent data: " . $e->getMessage() . "</div>";
    }
}

// Step 6: Recommendations
echo "<h2>Step 6: Recommendations & Next Steps</h2>";

if ($total_clients == 0) {
    echo "<div class='warning'>";
    echo "<h3>🎯 Issue Identified: Empty Database</h3>";
    echo "<p>Your dashboard shows 0 because there are no client records in your database yet.</p>";
    echo "<h4>Solutions:</h4>";
    echo "<ol>";
    echo "<li><strong>Test the registration form:</strong> Try registering a test client through your website</li>";
    echo "<li><strong>Import existing data:</strong> If you have client data, import it into the database</li>";
    echo "<li><strong>Check registration process:</strong> Ensure the client registration form is working properly</li>";
    echo "</ol>";
    echo "</div>";
} else {
    echo "<div class='success'>";
    echo "<h3>✅ Database Has Data</h3>";
    echo "<p>Your database contains client records, so the dashboard should be showing statistics.</p>";
    echo "<h4>If dashboard still shows 0:</h4>";
    echo "<ol>";
    echo "<li>Clear browser cache and refresh the dashboard</li>";
    echo "<li>Check if there are any PHP errors in the dashboard</li>";
    echo "<li>Verify the config.php file has correct database credentials</li>";
    echo "</ol>";
    echo "</div>";
}

echo "<div class='info'>";
echo "<h3>📋 Quick Actions</h3>";
echo "<ul>";
echo "<li><a href='admin-dashboard.php'>Go to Admin Dashboard</a></li>";
echo "<li><a href='client-registration.php'>Test Client Registration</a></li>";
echo "<li><a href='check-database.php'>Run Database Structure Check</a></li>";
echo "</ul>";
echo "</div>";

echo "</div></body></html>";
?>
