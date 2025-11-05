<?php
$db_host = 'localhost';
$db_name = 'dbwceop89t7wf2';
$db_user = 'uelcgzv3nvbgs';
$db_pass = 'd25xmrcdznvf';

try {
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ Database connection successful!";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}