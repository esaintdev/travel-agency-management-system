<?php
/**
 * Database Structure Check
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Check</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>";

echo "<h2>Database Structure Check</h2>";

try {
    require_once 'config.php';
    
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "<h3 class='success'>✅ Database Connected Successfully</h3>";
    echo "Database Name: " . DB_NAME . "<br><br>";
    
    // Check if clients table exists
    $stmt = $db->prepare("SHOW TABLES LIKE 'clients'");
    $stmt->execute();
    
    if ($stmt->fetch()) {
        echo "<h3 class='success'>✅ Clients Table Exists</h3>";
        
        // Get table structure
        $stmt = $db->prepare("DESCRIBE clients");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        echo "<h4>Table Structure:</h4>";
        echo "<table>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
        
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "<td>" . $column['Default'] . "</td>";
            echo "<td>" . $column['Extra'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
        
        // Check for required fields
        $required_fields = ['reference_id', 'full_name', 'gender', 'date_of_birth', 'country', 'mobile_number', 'client_email', 'password_hash', 'visa_type', 'address'];
        $existing_fields = array_column($columns, 'Field');
        
        echo "<h4>Required Fields Check:</h4>";
        foreach ($required_fields as $field) {
            if (in_array($field, $existing_fields)) {
                echo "<span class='success'>✅ $field</span><br>";
            } else {
                echo "<span class='error'>❌ Missing: $field</span><br>";
            }
        }
        
        // Count existing records
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM clients");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<br><h4>Existing Records: " . $result['count'] . "</h4>";
        
        // Show recent records
        if ($result['count'] > 0) {
            $stmt = $db->prepare("SELECT reference_id, full_name, client_email, created_at FROM clients ORDER BY created_at DESC LIMIT 5");
            $stmt->execute();
            $recent = $stmt->fetchAll();
            
            echo "<h4>Recent Registrations:</h4>";
            echo "<table>";
            echo "<tr><th>Reference ID</th><th>Name</th><th>Email</th><th>Created</th></tr>";
            foreach ($recent as $record) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($record['reference_id']) . "</td>";
                echo "<td>" . htmlspecialchars($record['full_name']) . "</td>";
                echo "<td>" . htmlspecialchars($record['client_email']) . "</td>";
                echo "<td>" . htmlspecialchars($record['created_at'] ?? 'N/A') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
        
    } else {
        echo "<h3 class='error'>❌ Clients Table Does Not Exist</h3>";
        echo "<p>The clients table needs to be created. Please run the installer or create the table manually.</p>";
    }
    
} catch (Exception $e) {
    echo "<h3 class='error'>❌ Database Error</h3>";
    echo "Error: " . $e->getMessage() . "<br>";
}

echo "</body></html>";
?>
