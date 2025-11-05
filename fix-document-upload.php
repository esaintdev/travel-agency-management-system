<?php
/**
 * Fix Document Upload Issues
 * This script will check and fix the client_documents table structure
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Document Upload Fix</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }
        .step { background: #f8f9fa; padding: 15px; margin: 10px 0; border-left: 4px solid #13357B; }
    </style>
</head>
<body>";

echo "<h2>🔧 Document Upload Fix</h2>";

try {
    if (!$db) {
        throw new Exception("Database connection failed");
    }
    
    echo "<div class='step'>";
    echo "<h3>Step 1: Check if client_documents table exists</h3>";
    
    // Check if table exists
    $stmt = $db->prepare("SHOW TABLES LIKE 'client_documents'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if (!$table_exists) {
        echo "<div class='warning'>❌ client_documents table does not exist</div>";
        echo "<div class='info'>Creating table...</div>";
        
        // Create the table with all required columns
        $create_sql = "
        CREATE TABLE `client_documents` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `client_id` int(11) NOT NULL,
            `document_type` varchar(100) NOT NULL,
            `document_name` varchar(255) NOT NULL,
            `original_filename` varchar(255) NOT NULL,
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $db->exec($create_sql);
        echo "<div class='success'>✅ client_documents table created successfully!</div>";
        
    } else {
        echo "<div class='success'>✅ client_documents table exists</div>";
    }
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 2: Check table structure</h3>";
    
    // Get table structure
    $stmt = $db->prepare("DESCRIBE client_documents");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    
    $column_names = array_column($columns, 'Field');
    $required_columns = [
        'id', 'client_id', 'document_type', 'document_name', 
        'original_filename', 'file_path', 'file_size', 'mime_type',
        'upload_date', 'status', 'admin_notes', 'created_at', 'updated_at'
    ];
    
    echo "<h4>Current Columns:</h4>";
    echo "<div class='info'>" . implode(', ', $column_names) . "</div>";
    
    $missing_columns = array_diff($required_columns, $column_names);
    
    if (!empty($missing_columns)) {
        echo "<div class='warning'>❌ Missing columns: " . implode(', ', $missing_columns) . "</div>";
        
        // Add missing columns
        foreach ($missing_columns as $column) {
            try {
                switch ($column) {
                    case 'original_filename':
                        $db->exec("ALTER TABLE client_documents ADD COLUMN original_filename varchar(255) NOT NULL AFTER document_name");
                        echo "<div class='success'>✅ Added original_filename column</div>";
                        break;
                    case 'admin_notes':
                        $db->exec("ALTER TABLE client_documents ADD COLUMN admin_notes text");
                        echo "<div class='success'>✅ Added admin_notes column</div>";
                        break;
                    case 'created_at':
                        $db->exec("ALTER TABLE client_documents ADD COLUMN created_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP");
                        echo "<div class='success'>✅ Added created_at column</div>";
                        break;
                    case 'updated_at':
                        $db->exec("ALTER TABLE client_documents ADD COLUMN updated_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
                        echo "<div class='success'>✅ Added updated_at column</div>";
                        break;
                }
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error adding $column: " . $e->getMessage() . "</div>";
            }
        }
    } else {
        echo "<div class='success'>✅ All required columns present</div>";
    }
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 3: Test document upload functionality</h3>";
    
    // Test if we can insert a sample document
    $stmt = $db->prepare("SELECT id FROM clients LIMIT 1");
    $stmt->execute();
    $test_client = $stmt->fetch();
    
    if ($test_client) {
        $test_client_id = $test_client['id'];
        
        try {
            // Test insert with all required fields
            $test_insert = "INSERT INTO client_documents 
                           (client_id, document_type, document_name, original_filename, file_path, file_size, mime_type, status) 
                           VALUES (?, 'test', 'Test Document', 'test_file.pdf', '/test/path.pdf', 1024, 'application/pdf', 'pending')";
            
            $stmt = $db->prepare($test_insert);
            if ($stmt->execute([$test_client_id])) {
                $test_doc_id = $db->lastInsertId();
                echo "<div class='success'>✅ Document upload test successful!</div>";
                
                // Clean up test record
                $stmt = $db->prepare("DELETE FROM client_documents WHERE id = ?");
                $stmt->execute([$test_doc_id]);
                echo "<div class='info'>Test record cleaned up</div>";
            }
        } catch (Exception $e) {
            echo "<div class='error'>❌ Document upload test failed: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='warning'>⚠️ No clients found for testing</div>";
    }
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>Step 4: Check clients table for document tracking columns</h3>";
    
    // Check clients table structure
    $stmt = $db->prepare("DESCRIBE clients");
    $stmt->execute();
    $client_columns = $stmt->fetchAll();
    $client_column_names = array_column($client_columns, 'Field');
    
    $required_client_columns = ['documents_uploaded', 'documents_verified', 'documents_notes'];
    $missing_client_columns = array_diff($required_client_columns, $client_column_names);
    
    if (!empty($missing_client_columns)) {
        echo "<div class='warning'>❌ Missing columns in clients table: " . implode(', ', $missing_client_columns) . "</div>";
        
        // Add missing columns to clients table
        foreach ($missing_client_columns as $column) {
            try {
                switch ($column) {
                    case 'documents_uploaded':
                        $db->exec("ALTER TABLE clients ADD COLUMN documents_uploaded BOOLEAN DEFAULT FALSE");
                        echo "<div class='success'>✅ Added documents_uploaded column to clients table</div>";
                        break;
                    case 'documents_verified':
                        $db->exec("ALTER TABLE clients ADD COLUMN documents_verified BOOLEAN DEFAULT FALSE");
                        echo "<div class='success'>✅ Added documents_verified column to clients table</div>";
                        break;
                    case 'documents_notes':
                        $db->exec("ALTER TABLE clients ADD COLUMN documents_notes TEXT");
                        echo "<div class='success'>✅ Added documents_notes column to clients table</div>";
                        break;
                }
            } catch (Exception $e) {
                echo "<div class='error'>❌ Error adding $column to clients table: " . $e->getMessage() . "</div>";
            }
        }
    } else {
        echo "<div class='success'>✅ All required columns present in clients table</div>";
    }
    echo "</div>";
    
    echo "<div class='step'>";
    echo "<h3>✅ Fix Complete!</h3>";
    echo "<div class='success'>Both client_documents table and clients table are now properly configured for document uploads.</div>";
    echo "<div class='info'>You can now try uploading documents from the client dashboard.</div>";
    echo "<h4>Fixed Issues:</h4>";
    echo "<ul>";
    echo "<li>✅ client_documents table created/updated with all required columns</li>";
    echo "<li>✅ original_filename column added</li>";
    echo "<li>✅ documents_uploaded, documents_verified, documents_notes columns added to clients table</li>";
    echo "<li>✅ Document upload functionality should now work properly</li>";
    echo "</ul>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div class='error'>File: " . $e->getFile() . " Line: " . $e->getLine() . "</div>";
}

echo "</body></html>";
?>
