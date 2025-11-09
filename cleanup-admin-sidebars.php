<?php
/**
 * Cleanup Script for Admin Sidebars
 * This script helps identify pages that still have old sidebar content
 */

$admin_pages = [
    'admin-search.php',
    'admin-documents.php', 
    'admin-settings.php',
    'admin-export.php',
    'admin-visa-content.php',
    'admin-client-view.php'
];

echo "<h2>Admin Sidebar Cleanup Status</h2>";

foreach ($admin_pages as $page) {
    $file_path = "/Applications/XAMPP/xamppfiles/htdocs/sension/{$page}";
    
    if (file_exists($file_path)) {
        $content = file_get_contents($file_path);
        
        // Check for old sidebar patterns
        $has_old_sidebar = false;
        $issues = [];
        
        if (strpos($content, '<div class="col-md-3 col-lg-2 sidebar">') !== false) {
            $has_old_sidebar = true;
            $issues[] = "Old sidebar div found";
        }
        
        if (strpos($content, '<div class="col-md-9 col-lg-10 main-content">') !== false) {
            $has_old_sidebar = true;
            $issues[] = "Old main-content div found";
        }
        
        if (strpos($content, '<nav class="nav flex-column">') !== false) {
            $has_old_sidebar = true;
            $issues[] = "Old nav flex-column found";
        }
        
        // Check for new sidebar
        $has_new_sidebar = strpos($content, "<?php include 'includes/admin-sidebar.php'; ?>") !== false;
        $has_sidebar_close = strpos($content, "<?php include 'includes/admin-sidebar-close.php'; ?>") !== false;
        
        echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd; border-radius: 5px;'>";
        echo "<h3>{$page}</h3>";
        
        if ($has_new_sidebar && $has_sidebar_close && !$has_old_sidebar) {
            echo "<span style='color: green;'>✅ CLEAN - New sidebar implemented correctly</span>";
        } elseif ($has_new_sidebar && !$has_old_sidebar) {
            echo "<span style='color: orange;'>⚠️ PARTIAL - New sidebar added but missing close include</span>";
        } elseif ($has_old_sidebar) {
            echo "<span style='color: red;'>❌ NEEDS CLEANUP - Old sidebar content found:</span><br>";
            foreach ($issues as $issue) {
                echo "- {$issue}<br>";
            }
        } else {
            echo "<span style='color: red;'>❌ NO SIDEBAR - Neither old nor new sidebar found</span>";
        }
        
        echo "</div>";
    } else {
        echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
        echo "<h3>{$page}</h3>";
        echo "<span style='color: gray;'>File not found</span>";
        echo "</div>";
    }
}

echo "<hr>";
echo "<h3>Manual Cleanup Instructions:</h3>";
echo "<p>For pages marked as needing cleanup, you need to:</p>";
echo "<ol>";
echo "<li>Remove old sidebar divs and navigation</li>";
echo "<li>Ensure only the new sidebar includes remain</li>";
echo "<li>Check that the page layout works correctly</li>";
echo "</ol>";
?>
