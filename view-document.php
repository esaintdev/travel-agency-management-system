<?php
/**
 * View Document - Display document in browser
 */

session_start();
require_once 'config.php';
require_once 'includes/client-auth.php';

// Check if client is logged in
requireClientLogin();

$client = getCurrentClient($db);
if (!$client) {
    http_response_code(403);
    die('Access denied');
}

$doc_id = $_GET['id'] ?? '';
if (empty($doc_id)) {
    http_response_code(400);
    die('Document ID required');
}

try {
    // Get document info and verify ownership
    $stmt = $db->prepare("SELECT * FROM client_documents WHERE id = ? AND client_id = ?");
    $stmt->execute([$doc_id, $client['id']]);
    $document = $stmt->fetch();
    
    if (!$document) {
        http_response_code(404);
        die('Document not found');
    }
    
    $file_path = $document['file_path'];
    
    // Check if file exists
    if (!file_exists($file_path)) {
        http_response_code(404);
        die('File not found on server');
    }
    
    // Get file info
    $file_size = filesize($file_path);
    $mime_type = $document['mime_type'] ?: 'application/octet-stream';
    $original_filename = $document['original_filename'];
    
    // Set headers for viewing in browser
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . $file_size);
    header('Content-Disposition: inline; filename="' . $original_filename . '"');
    header('Cache-Control: private, max-age=3600');
    header('Pragma: public');
    
    // Output file
    readfile($file_path);
    
} catch (Exception $e) {
    error_log("Document view error: " . $e->getMessage());
    http_response_code(500);
    die('Error loading document');
}
?>
