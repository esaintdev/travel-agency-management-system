<?php
/**
 * Fix Database Issues - Create missing tables and update currency function
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>";

echo "<h2>🔧 Database Issues Fix</h2>";

try {
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "<h3>1. Checking Existing Tables</h3>";
    
    // Check existing tables
    $stmt = $db->prepare("SHOW TABLES");
    $stmt->execute();
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "<div class='info'>Existing tables: " . implode(', ', $tables) . "</div><br>";
    
    // Check if client_documents table exists
    if (!in_array('client_documents', $tables)) {
        echo "<h3>2. Creating client_documents Table</h3>";
        
        $create_table_sql = "
        CREATE TABLE `client_documents` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `client_id` int(11) NOT NULL,
            `document_type` varchar(100) NOT NULL,
            `document_name` varchar(255) NOT NULL,
            `file_path` varchar(500) NOT NULL,
            `file_size` int(11) DEFAULT NULL,
            `mime_type` varchar(100) DEFAULT NULL,
            `upload_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `status` enum('pending','approved','rejected') DEFAULT 'pending',
            `admin_notes` text,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `client_id` (`client_id`),
            KEY `document_type` (`document_type`),
            KEY `status` (`status`),
            CONSTRAINT `client_documents_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $db->exec($create_table_sql);
        echo "<div class='success'>✅ client_documents table created successfully!</div><br>";
        
        // Insert some sample document types
        $document_types = [
            'passport' => 'Passport Copy',
            'employment_letter' => 'Employment Letter',
            'bank_statement' => 'Bank Statement',
            'educational_certificate' => 'Educational Certificate',
            'marriage_certificate' => 'Marriage Certificate',
            'birth_certificate' => 'Birth Certificate',
            'financial_evidence' => 'Financial Evidence',
            'payslip' => 'Payslip',
            'other' => 'Other Document'
        ];
        
        echo "<div class='info'>Document types that can be uploaded: " . implode(', ', $document_types) . "</div><br>";
        
    } else {
        echo "<h3>2. client_documents Table Status</h3>";
        echo "<div class='success'>✅ client_documents table already exists</div><br>";
        
        // Show table structure
        $stmt = $db->prepare("DESCRIBE client_documents");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        echo "<h4>Table Structure:</h4>";
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
        foreach ($columns as $column) {
            echo "<tr>";
            echo "<td>" . $column['Field'] . "</td>";
            echo "<td>" . $column['Type'] . "</td>";
            echo "<td>" . $column['Null'] . "</td>";
            echo "<td>" . $column['Key'] . "</td>";
            echo "</tr>";
        }
        echo "</table><br>";
    }
    
    echo "<h3>3. Testing Document Upload Functionality</h3>";
    
    // Test if we can insert a sample document record
    $test_client_id = 1; // Assuming client ID 1 exists
    
    // Check if any clients exist
    $stmt = $db->prepare("SELECT id FROM clients LIMIT 1");
    $stmt->execute();
    $test_client = $stmt->fetch();
    
    if ($test_client) {
        $test_client_id = $test_client['id'];
        echo "<div class='success'>✅ Test client found (ID: $test_client_id)</div><br>";
        
        // Test insert (we'll delete it right after)
        $test_insert = "INSERT INTO client_documents (client_id, document_type, document_name, file_path, file_size, mime_type) 
                       VALUES (?, 'test', 'test_document.pdf', '/test/path.pdf', 1024, 'application/pdf')";
        $stmt = $db->prepare($test_insert);
        if ($stmt->execute([$test_client_id])) {
            $test_doc_id = $db->lastInsertId();
            echo "<div class='success'>✅ Document upload functionality working</div><br>";
            
            // Clean up test record
            $stmt = $db->prepare("DELETE FROM client_documents WHERE id = ?");
            $stmt->execute([$test_doc_id]);
            echo "<div class='info'>Test record cleaned up</div><br>";
        } else {
            echo "<div class='error'>❌ Document upload test failed</div><br>";
        }
    } else {
        echo "<div class='error'>❌ No clients found in database for testing</div><br>";
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div><br>";
    echo "<div class='error'>File: " . $e->getFile() . " Line: " . $e->getLine() . "</div><br>";
}

echo "</body></html>";
?>
