h<?php
require_once 'config.php';

echo "<h2>Installing Visa Content Management System</h2>";

try {
    // Read and execute the SQL file
    $sql = file_get_contents('visa-content-schema.sql');
    
    // Split the SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->prepare($statement)->execute();
        }
    }
    
    echo "<div style='color: green; padding: 10px; border: 1px solid green; margin: 10px 0;'>";
    echo "<strong>✓ Success!</strong> Visa content management system has been installed successfully.<br>";
    echo "• Database table 'visa_content' created<br>";
    echo "• Default visa content inserted<br>";
    echo "• You can now manage visa content through the admin panel<br><br>";
    echo "<a href='admin-visa-content.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Go to Visa Content Management</a>";
    echo "</div>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; padding: 10px; border: 1px solid red; margin: 10px 0;'>";
    echo "<strong>✗ Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<br><a href='visa-details.php'>View Visa Details Page</a> | <a href='index.php'>Back to Home</a>";
?>
