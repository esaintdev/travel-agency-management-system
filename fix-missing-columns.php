<?php
/**
 * Fix Missing Database Columns
 * This script will add missing columns to the clients table
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Missing Columns</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #28a745; background: #d4edda; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .info { color: #0c5460; background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .btn { display: inline-block; padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #0056b3; }
        code { background: #f8f9fa; padding: 2px 4px; border-radius: 3px; font-family: monospace; }
    </style>
</head>
<body>
<div class='container'>";

echo "<h1>🔧 Fix Missing Database Columns</h1>";
echo "<p>This script will add missing columns to your clients table.</p>";

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
    
    echo "<div class='success'>✅ Clients table exists</div>";
    
    // Get current table structure
    $stmt = $db->query("DESCRIBE clients");
    $columns = $stmt->fetchAll();
    $existing_columns = array_column($columns, 'Field');
    
    echo "<h2>Current Table Structure</h2>";
    echo "<div class='info'>Existing columns: " . implode(', ', $existing_columns) . "</div>";
    
    // Define required columns with their SQL definitions
    $required_columns = [
        'deposit_paid' => 'DECIMAL(10,2) DEFAULT 0.00 COMMENT "Amount paid as deposit"',
        'submitted_date' => 'DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT "Date when application was submitted"',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT "Record creation timestamp"',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT "Record update timestamp"'
    ];
    
    $missing_columns = [];
    $added_columns = [];
    
    echo "<h2>Checking Required Columns</h2>";
    
    foreach ($required_columns as $column_name => $column_definition) {
        if (!in_array($column_name, $existing_columns)) {
            $missing_columns[] = $column_name;
            echo "<div class='warning'>⚠️ Missing: $column_name</div>";
        } else {
            echo "<div class='success'>✅ Found: $column_name</div>";
        }
    }
    
    if (empty($missing_columns)) {
        echo "<div class='success'><h3>🎉 All Required Columns Present!</h3><p>No columns need to be added.</p></div>";
    } else {
        echo "<h2>Adding Missing Columns</h2>";
        
        foreach ($missing_columns as $column_name) {
            try {
                $sql = "ALTER TABLE clients ADD COLUMN $column_name " . $required_columns[$column_name];
                echo "<div class='info'>Executing: <code>$sql</code></div>";
                
                $db->exec($sql);
                $added_columns[] = $column_name;
                echo "<div class='success'>✅ Added column: $column_name</div>";
                
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error adding $column_name: " . $e->getMessage() . "</div>";
            }
        }
        
        if (!empty($added_columns)) {
            echo "<div class='success'>";
            echo "<h3>🎉 Columns Added Successfully!</h3>";
            echo "<p>Added " . count($added_columns) . " columns: " . implode(', ', $added_columns) . "</p>";
            echo "</div>";
        }
    }
    
    // Update existing records if deposit_paid was added
    if (in_array('deposit_paid', $added_columns)) {
        echo "<h2>Updating Existing Records</h2>";
        
        try {
            // Set default deposit_paid values for existing records
            $stmt = $db->prepare("UPDATE clients SET deposit_paid = 0.00 WHERE deposit_paid IS NULL");
            $stmt->execute();
            $updated_rows = $stmt->rowCount();
            
            if ($updated_rows > 0) {
                echo "<div class='success'>✅ Updated $updated_rows existing records with default deposit_paid value</div>";
            } else {
                echo "<div class='info'>ℹ️ No existing records needed updating</div>";
            }
            
        } catch (Exception $e) {
            echo "<div class='error'>❌ Error updating existing records: " . $e->getMessage() . "</div>";
        }
    }
    
    // Verify the fixes
    echo "<h2>Verification</h2>";
    
    try {
        // Test the dashboard queries
        echo "<h3>Testing Dashboard Queries</h3>";
        
        // Total clients
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients");
        $stmt->execute();
        $total_clients = $stmt->fetch()['total'];
        echo "<div class='success'>✅ Total clients query: $total_clients</div>";
        
        // Total revenue (now should work)
        $stmt = $db->prepare("SELECT SUM(deposit_paid) as total FROM clients");
        $stmt->execute();
        $total_revenue = $stmt->fetch()['total'] ?? 0;
        echo "<div class='success'>✅ Total revenue query: $total_revenue</div>";
        
        // New clients this month
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients WHERE MONTH(submitted_date) = MONTH(CURRENT_DATE()) AND YEAR(submitted_date) = YEAR(CURRENT_DATE())");
        $stmt->execute();
        $new_clients_month = $stmt->fetch()['total'];
        echo "<div class='success'>✅ New clients this month query: $new_clients_month</div>";
        
        // Pending applications
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM clients WHERE status = 'Active'");
        $stmt->execute();
        $pending_applications = $stmt->fetch()['total'];
        echo "<div class='success'>✅ Pending applications query: $pending_applications</div>";
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error testing queries: " . $e->getMessage() . "</div>";
    }
    
    // Show updated table structure
    echo "<h2>Updated Table Structure</h2>";
    $stmt = $db->query("DESCRIBE clients");
    $updated_columns = $stmt->fetchAll();
    
    echo "<table style='width: 100%; border-collapse: collapse; margin: 10px 0;'>";
    echo "<tr style='background: #f2f2f2;'><th style='border: 1px solid #ddd; padding: 8px;'>Field</th><th style='border: 1px solid #ddd; padding: 8px;'>Type</th><th style='border: 1px solid #ddd; padding: 8px;'>Null</th><th style='border: 1px solid #ddd; padding: 8px;'>Default</th></tr>";
    
    foreach ($updated_columns as $column) {
        $is_new = in_array($column['Field'], $added_columns);
        $row_style = $is_new ? "background: #d4edda;" : "";
        
        echo "<tr style='$row_style'>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($column['Field']) . ($is_new ? " <strong>(NEW)</strong>" : "") . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($column['Type']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($column['Null']) . "</td>";
        echo "<td style='border: 1px solid #ddd; padding: 8px;'>" . htmlspecialchars($column['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div class='success'>";
    echo "<h3>✅ Database Fix Complete!</h3>";
    echo "<p>Your database structure has been updated. The dashboard should now work properly.</p>";
    echo "</div>";
    
    echo "<h3>Next Steps</h3>";
    echo "<a href='admin-dashboard.php' class='btn'>Test Dashboard</a>";
    echo "<a href='diagnose-dashboard-issue.php' class='btn'>Run Diagnostic Again</a>";
    echo "<a href='add-sample-data.php' class='btn'>Add Sample Data</a>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>
