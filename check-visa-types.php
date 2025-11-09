<?php
/**
 * Check Visa Types in Database
 * This script will show what visa types are currently in use
 */

require_once 'config.php';

echo "<h2>🔍 Checking Visa Types in Database</h2>\n";

try {
    // Check what visa types exist in clients table
    echo "<h3>1. Visa Types in Clients Table</h3>\n";
    
    $stmt = $db->query("SELECT DISTINCT visa_type, COUNT(*) as client_count FROM clients GROUP BY visa_type ORDER BY client_count DESC");
    $visa_types = $stmt->fetchAll();
    
    if (empty($visa_types)) {
        echo "❌ No clients found in database<br>\n";
    } else {
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
        echo "<tr><th style='padding: 8px;'>Visa Type</th><th style='padding: 8px;'>Number of Clients</th></tr>\n";
        
        foreach ($visa_types as $type) {
            echo "<tr><td style='padding: 8px;'>" . htmlspecialchars($type['visa_type']) . "</td><td style='padding: 8px;'>" . $type['client_count'] . "</td></tr>\n";
        }
        echo "</table>\n";
    }
    
    // Check if document_requirements table exists
    echo "<h3>2. Document Requirements Table Status</h3>\n";
    
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM document_requirements");
        $req_count = $stmt->fetchColumn();
        echo "✅ Document requirements table exists with {$req_count} requirements<br>\n";
        
        if ($req_count > 0) {
            echo "<h4>Requirements by Visa Type:</h4>\n";
            $stmt = $db->query("SELECT visa_type, COUNT(*) as req_count FROM document_requirements GROUP BY visa_type");
            $req_types = $stmt->fetchAll();
            
            echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
            echo "<tr><th style='padding: 8px;'>Visa Type</th><th style='padding: 8px;'>Requirements Count</th></tr>\n";
            
            foreach ($req_types as $req) {
                echo "<tr><td style='padding: 8px;'>" . htmlspecialchars($req['visa_type']) . "</td><td style='padding: 8px;'>" . $req['req_count'] . "</td></tr>\n";
            }
            echo "</table>\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Document requirements table does not exist<br>\n";
        echo "Error: " . $e->getMessage() . "<br>\n";
        echo "<strong>👉 You need to run setup-document-requirements.php first!</strong><br>\n";
    }
    
    // Check for mismatches
    echo "<h3>3. Checking for Mismatches</h3>\n";
    
    if (!empty($visa_types)) {
        $client_visa_types = array_column($visa_types, 'visa_type');
        $standard_types = ['Visit', 'Work', 'Study', 'Other'];
        
        echo "<h4>Client Visa Types vs Standard Types:</h4>\n";
        foreach ($client_visa_types as $client_type) {
            if (in_array($client_type, $standard_types)) {
                echo "✅ '{$client_type}' - Matches standard type<br>\n";
            } else {
                echo "⚠️ '{$client_type}' - Non-standard type (will use 'Other' requirements)<br>\n";
            }
        }
    }
    
    echo "<h3>4. Recommendations</h3>\n";
    echo "<ul>\n";
    
    try {
        $stmt = $db->query("SELECT COUNT(*) FROM document_requirements");
        $req_count = $stmt->fetchColumn();
        
        if ($req_count == 0) {
            echo "<li>🔧 <strong>Run setup-document-requirements.php</strong> to create document requirements</li>\n";
        } else {
            echo "<li>✅ Document requirements are set up</li>\n";
        }
    } catch (Exception $e) {
        echo "<li>🔧 <strong>Run setup-document-requirements.php</strong> to create document requirements table</li>\n";
    }
    
    echo "<li>📋 Test the client-documents.php page after setup</li>\n";
    echo "<li>🔍 Check that requirements show up for each visa type</li>\n";
    echo "</ul>\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>\n";
}
?>

<style>
table { border-collapse: collapse; margin: 10px 0; }
th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
th { background-color: #f2f2f2; }
</style>
