<?php
// SiteGround Database Connection Test
$host = 'localhost';
$dbname = 'dbwceop89t7wf2';
$username = 'uelcgzv3nvbgs';
$password = 'd25xmrcdznvf';

echo "<h2>SiteGround Database Connection Test</h2>";
echo "<p>Testing connection to database: <strong>$dbname</strong></p>";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "<div style='color: green;'>✅ SUCCESS: Database connected!</div>";
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (count($tables) > 0) {
        echo "<p>Tables found: " . implode(', ', $tables) . "</p>";
    } else {
        echo "<div style='color: orange;'>⚠️ No tables found. Import database.sql</div>";
    }
    
} catch (PDOException $e) {
    echo "<div style='color: red;'>❌ ERROR: " . $e->getMessage() . "</div>";
    echo "<h3>Check:</h3>";
    echo "<ol>";
    echo "<li>Database name is correct in SiteGround</li>";
    echo "<li>Username is correct in SiteGround</li>";
    echo "<li>Password is correct</li>";
    echo "<li>User is assigned to database with ALL PRIVILEGES</li>";
    echo "</ol>";
}
?>