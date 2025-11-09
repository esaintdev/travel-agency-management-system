<?php
/**
 * Setup Document Requirements Table
 * Run this script to create the document_requirements table and populate it with default data
 */

require_once 'config.php';

echo "<h2>🔧 Setting Up Document Requirements</h2>\n";

try {
    // 1. Create document_requirements table
    echo "<h3>1. Creating document_requirements table</h3>\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS document_requirements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        visa_type ENUM('Visit', 'Work', 'Study', 'Other') NOT NULL,
        document_type ENUM('passport', 'photo', 'bank_statement', 'employment_letter', 'invitation_letter', 'travel_itinerary', 'accommodation_proof', 'financial_proof', 'medical_certificate', 'other') NOT NULL,
        is_required BOOLEAN DEFAULT TRUE,
        description TEXT,
        max_file_size INT DEFAULT 5242880,
        allowed_extensions VARCHAR(255) DEFAULT 'jpg,jpeg,png,pdf,doc,docx',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $db->exec($sql);
    echo "✅ Document requirements table created successfully!<br>\n";
    
    // 2. Check if data already exists
    $stmt = $db->query("SELECT COUNT(*) FROM document_requirements");
    $count = $stmt->fetchColumn();
    
    if ($count == 0) {
        echo "<h3>2. Inserting default document requirements</h3>\n";
        
        // Insert default requirements
        $requirements = [
            // Visit Visa Requirements
            ['Visit', 'passport', TRUE, 'Valid passport with at least 6 months validity'],
            ['Visit', 'photo', TRUE, 'Recent passport-size photograph (white background)'],
            ['Visit', 'bank_statement', TRUE, 'Bank statement for last 3 months showing sufficient funds'],
            ['Visit', 'invitation_letter', FALSE, 'Invitation letter from host (if applicable)'],
            ['Visit', 'travel_itinerary', TRUE, 'Flight bookings and detailed travel itinerary'],
            ['Visit', 'accommodation_proof', TRUE, 'Hotel bookings or accommodation confirmation'],
            
            // Work Visa Requirements
            ['Work', 'passport', TRUE, 'Valid passport with at least 6 months validity'],
            ['Work', 'photo', TRUE, 'Recent passport-size photograph (white background)'],
            ['Work', 'employment_letter', TRUE, 'Employment contract or job offer letter from employer'],
            ['Work', 'bank_statement', TRUE, 'Bank statement for last 6 months'],
            ['Work', 'medical_certificate', TRUE, 'Medical fitness certificate from approved clinic'],
            ['Work', 'financial_proof', TRUE, 'Proof of financial capacity and salary details'],
            
            // Study Visa Requirements
            ['Study', 'passport', TRUE, 'Valid passport with at least 6 months validity'],
            ['Study', 'photo', TRUE, 'Recent passport-size photograph (white background)'],
            ['Study', 'bank_statement', TRUE, 'Bank statement for last 6 months'],
            ['Study', 'financial_proof', TRUE, 'Proof of financial support for studies (scholarship/sponsor letter)'],
            ['Study', 'accommodation_proof', FALSE, 'University accommodation or housing proof'],
            
            // Other Visa Requirements
            ['Other', 'passport', TRUE, 'Valid passport with at least 6 months validity'],
            ['Other', 'photo', TRUE, 'Recent passport-size photograph (white background)'],
            ['Other', 'bank_statement', TRUE, 'Recent bank statement'],
            ['Other', 'other', FALSE, 'Additional documents as required by specific visa type']
        ];
        
        $stmt = $db->prepare("INSERT INTO document_requirements (visa_type, document_type, is_required, description) VALUES (?, ?, ?, ?)");
        
        foreach ($requirements as $req) {
            $stmt->execute($req);
        }
        
        echo "✅ Inserted " . count($requirements) . " document requirements!<br>\n";
    } else {
        echo "<h3>2. Document requirements already exist</h3>\n";
        echo "✅ Found {$count} existing requirements<br>\n";
    }
    
    // 3. Create indexes if they don't exist
    echo "<h3>3. Creating indexes</h3>\n";
    
    try {
        $db->exec("CREATE INDEX IF NOT EXISTS idx_document_requirements_visa_type ON document_requirements(visa_type)");
        echo "✅ Visa type index created<br>\n";
    } catch (Exception $e) {
        echo "ℹ️ Index already exists or error: " . $e->getMessage() . "<br>\n";
    }
    
    // 4. Update clients table if needed
    echo "<h3>4. Updating clients table</h3>\n";
    
    try {
        $db->exec("ALTER TABLE clients ADD COLUMN IF NOT EXISTS documents_uploaded BOOLEAN DEFAULT FALSE");
        $db->exec("ALTER TABLE clients ADD COLUMN IF NOT EXISTS documents_verified BOOLEAN DEFAULT FALSE");
        $db->exec("ALTER TABLE clients ADD COLUMN IF NOT EXISTS documents_notes TEXT");
        echo "✅ Client table columns added<br>\n";
    } catch (Exception $e) {
        echo "ℹ️ Columns may already exist: " . $e->getMessage() . "<br>\n";
    }
    
    // 5. Test the setup
    echo "<h3>5. Testing document requirements</h3>\n";
    
    $stmt = $db->query("SELECT visa_type, COUNT(*) as req_count FROM document_requirements GROUP BY visa_type");
    $results = $stmt->fetchAll();
    
    foreach ($results as $result) {
        echo "✅ {$result['visa_type']} visa: {$result['req_count']} requirements<br>\n";
    }
    
    echo "<h3>✅ Setup Complete!</h3>\n";
    echo "<p><strong>Document requirements are now ready!</strong></p>\n";
    echo "<ul>\n";
    echo "<li>✅ Document requirements table created</li>\n";
    echo "<li>✅ Default requirements populated</li>\n";
    echo "<li>✅ Indexes created for performance</li>\n";
    echo "<li>✅ Client table updated</li>\n";
    echo "</ul>\n";
    echo "<p>Clients will now see specific document requirements based on their visa type!</p>\n";
    
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>\n";
}
?>
