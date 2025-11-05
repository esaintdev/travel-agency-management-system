<?php
/**
 * Registration Test & Debug Script
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>M25 Registration Debug Test</h2>";

// Test 1: Check if config.php exists
echo "<h3>1. Config File Check</h3>";
if (file_exists('config.php')) {
    echo "✅ config.php exists<br>";
    try {
        require_once 'config.php';
        echo "✅ config.php loaded successfully<br>";
    } catch (Exception $e) {
        echo "❌ Error loading config.php: " . $e->getMessage() . "<br>";
    }
} else {
    echo "❌ config.php not found - Run installer first!<br>";
    echo "<a href='simple_install.php'>Run Simple Installer</a><br>";
    exit();
}

// Test 2: Database connection
echo "<h3>2. Database Connection Test</h3>";
try {
    if (isset($db)) {
        echo "✅ Database connection exists<br>";
        
        // Test query
        $stmt = $db->query("SELECT COUNT(*) as count FROM clients");
        $result = $stmt->fetch();
        echo "✅ Database query successful - {$result['count']} clients in database<br>";
        
    } else {
        echo "❌ Database connection not found<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// Test 3: Check tables exist
echo "<h3>3. Database Tables Check</h3>";
try {
    $tables = ['clients', 'admin_users', 'activity_logs'];
    foreach ($tables as $table) {
        $stmt = $db->query("SHOW TABLES LIKE '$table'");
        if ($stmt->fetch()) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' missing<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Table check error: " . $e->getMessage() . "<br>";
}

// Test 4: Test registration with sample data
echo "<h3>4. Test Registration Process</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_registration'])) {
    try {
        // Generate test reference ID
        $reference_id = 'M25-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Test data
        $test_data = [
            'reference_id' => $reference_id,
            'full_name' => 'Test User',
            'gender' => 'Male',
            'date_of_birth' => '1990-01-01',
            'country' => 'Ghana',
            'mobile_number' => '+233592605752',
            'client_email' => 'test@m25travelagency.com',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'visa_type' => 'Visit',
            'address' => 'Test Address, Accra'
        ];
        
        // Insert test client
        $columns = implode(', ', array_keys($test_data));
        $placeholders = ':' . implode(', :', array_keys($test_data));
        $sql = "INSERT INTO clients ({$columns}) VALUES ({$placeholders})";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($test_data);
        
        echo "✅ Test registration successful! Reference ID: $reference_id<br>";
        echo "✅ Client ID: " . $db->lastInsertId() . "<br>";
        
    } catch (Exception $e) {
        echo "❌ Test registration failed: " . $e->getMessage() . "<br>";
        echo "SQL Error Info: " . print_r($stmt->errorInfo(), true) . "<br>";
    }
}

// Test form
echo "<h3>5. Test Registration Form</h3>";
echo '<form method="POST">
    <button type="submit" name="test_registration" class="btn btn-primary">Test Registration Process</button>
</form>';

// Test 6: Check recent registrations
echo "<h3>6. Recent Registrations</h3>";
try {
    $stmt = $db->query("SELECT * FROM clients ORDER BY submitted_date DESC LIMIT 5");
    $clients = $stmt->fetchAll();
    
    if (empty($clients)) {
        echo "No registrations found in database<br>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr><th>Reference ID</th><th>Name</th><th>Email</th><th>Date</th></tr>";
        foreach ($clients as $client) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($client['reference_id']) . "</td>";
            echo "<td>" . htmlspecialchars($client['full_name']) . "</td>";
            echo "<td>" . htmlspecialchars($client['client_email']) . "</td>";
            echo "<td>" . htmlspecialchars($client['submitted_date']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
} catch (Exception $e) {
    echo "❌ Error fetching registrations: " . $e->getMessage() . "<br>";
}

// Test 7: Check form processing
echo "<h3>7. Form Processing Test</h3>";
echo "Registration form should POST to: process-registration.php<br>";
if (file_exists('process-registration.php')) {
    echo "✅ process-registration.php exists<br>";
} else {
    echo "❌ process-registration.php missing<br>";
}

echo "<h3>8. Quick Links</h3>";
echo "<a href='client-registration.html'>Client Registration Form</a> | ";
echo "<a href='admin-login.html'>Admin Login</a> | ";
echo "<a href='check_setup.php'>Setup Checker</a>";

?>
<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #13357B; }
.btn { background: #13357B; color: white; padding: 10px 20px; text-decoration: none; border: none; cursor: pointer; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background: #f2f2f2; }
</style>
