<?php
// Check PHP upload configuration
echo "<h2>PHP Upload Configuration Check</h2>";

echo "<table border='1' cellpadding='5' cellspacing='0'>";
echo "<tr><th>Setting</th><th>Value</th><th>Status</th></tr>";

$settings = [
    'file_uploads' => ini_get('file_uploads'),
    'upload_max_filesize' => ini_get('upload_max_filesize'),
    'post_max_size' => ini_get('post_max_size'),
    'max_execution_time' => ini_get('max_execution_time'),
    'memory_limit' => ini_get('memory_limit'),
    'upload_tmp_dir' => ini_get('upload_tmp_dir') ?: 'Default system temp dir'
];

foreach ($settings as $setting => $value) {
    $status = 'OK';
    if ($setting == 'file_uploads' && !$value) {
        $status = '<span style="color: red;">DISABLED</span>';
    }
    echo "<tr><td>$setting</td><td>$value</td><td>$status</td></tr>";
}

echo "</table>";

// Check upload directory permissions
echo "<h3>Directory Permissions</h3>";
$upload_dir = 'uploads/visa-images/';
echo "<p><strong>Upload Directory:</strong> $upload_dir</p>";
echo "<p><strong>Exists:</strong> " . (is_dir($upload_dir) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Writable:</strong> " . (is_writable($upload_dir) ? 'YES' : 'NO') . "</p>";
echo "<p><strong>Permissions:</strong> " . substr(sprintf('%o', fileperms($upload_dir)), -4) . "</p>";

// Check for existing files
if (is_dir($upload_dir)) {
    $files = glob($upload_dir . '*');
    echo "<p><strong>Files in directory:</strong> " . count($files) . "</p>";
    foreach ($files as $file) {
        echo "<p>- " . basename($file) . " (" . filesize($file) . " bytes)</p>";
    }
}

echo "<hr>";
echo "<p><a href='admin-visa-content.php'>← Back to Admin Panel</a></p>";
?>
