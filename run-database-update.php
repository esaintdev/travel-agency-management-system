<?php
/**
 * Database Update Script for New Client Fields
 * Run this once to update the database schema
 */

require_once 'config.php';

try {
    // Read the SQL update file
    $sql_file = 'update-client-fields.sql';
    if (!file_exists($sql_file)) {
        throw new Exception("SQL update file not found: {$sql_file}");
    }
    
    $sql_content = file_get_contents($sql_file);
    if ($sql_content === false) {
        throw new Exception("Failed to read SQL update file");
    }
    
    // Split SQL commands (basic splitting by semicolon)
    $sql_commands = array_filter(array_map('trim', explode(';', $sql_content)));
    
    echo "<h2>M25 Travel Agency - Database Update</h2>\n";
    echo "<p>Running database updates...</p>\n";
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($sql_commands as $sql) {
        // Skip empty commands and comments
        if (empty($sql) || strpos($sql, '--') === 0 || strpos($sql, 'USE ') === 0) {
            continue;
        }
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $success_count++;
            echo "<p style='color: green;'>✓ Executed: " . substr($sql, 0, 50) . "...</p>\n";
        } catch (PDOException $e) {
            $error_count++;
            // Some errors are expected (like adding columns that already exist)
            if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                echo "<p style='color: orange;'>⚠ Column already exists: " . substr($sql, 0, 50) . "...</p>\n";
            } else {
                echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>\n";
                echo "<p style='color: red;'>SQL: " . substr($sql, 0, 100) . "...</p>\n";
            }
        }
    }
    
    echo "<hr>\n";
    echo "<h3>Update Summary</h3>\n";
    echo "<p>Successful commands: {$success_count}</p>\n";
    echo "<p>Errors/Warnings: {$error_count}</p>\n";
    
    if ($error_count === 0) {
        echo "<p style='color: green; font-weight: bold;'>✓ Database update completed successfully!</p>\n";
    } else {
        echo "<p style='color: orange; font-weight: bold;'>⚠ Database update completed with some warnings. Please review the errors above.</p>\n";
    }
    
    // Test the updated table structure
    try {
        $stmt = $db->prepare("DESCRIBE clients");
        $stmt->execute();
        $columns = $stmt->fetchAll();
        
        echo "<h3>Updated Table Structure</h3>\n";
        echo "<p>Total columns: " . count($columns) . "</p>\n";
        
        // Check for new columns
        $new_columns = [
            'house_number', 'street_name', 'location', 'digital_address', 'postal_address',
            'employment_letter_details', 'employment_letter_path', 'payslips_path',
            'university', 'graduation_year', 'bachelor_degree', 'master_degree',
            'educational_certificates_path', 'other_qualifications',
            'marriage_certificate_path', 'birth_certificates_path',
            'account_holder_name', 'average_monthly_balance', 'bank_statements_path',
            'financial_evidence_path', 'financial_declaration', 'estimated_trip_budget', 'funding_source'
        ];
        
        $existing_columns = array_column($columns, 'Field');
        $missing_columns = array_diff($new_columns, $existing_columns);
        
        if (empty($missing_columns)) {
            echo "<p style='color: green;'>✓ All new columns have been added successfully!</p>\n";
        } else {
            echo "<p style='color: red;'>✗ Missing columns: " . implode(', ', $missing_columns) . "</p>\n";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error checking table structure: " . $e->getMessage() . "</p>\n";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Fatal Error: " . $e->getMessage() . "</p>\n";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Update - M25 Travel Agency</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h2 { color: #13357B; }
        h3 { color: #FEA116; }
        p { margin: 5px 0; }
        hr { margin: 20px 0; }
    </style>
</head>
<body>
    <p><a href="client-registration.php">← Back to Registration Form</a></p>
    <p><strong>Note:</strong> This script should only be run once. After running successfully, you can delete this file for security.</p>
</body>
</html>
