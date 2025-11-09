<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: blog.php');
    exit();
}

try {
    $post_id = intval($_POST['post_id']);
    $author_name = sanitizeInput($_POST['author_name']);
    $author_email = sanitizeInput($_POST['author_email']);
    $author_website = sanitizeInput($_POST['author_website']);
    $content = sanitizeInput($_POST['content']);
    
    // Validation
    if (empty($author_name) || empty($author_email) || empty($content)) {
        throw new Exception("Name, email, and comment are required.");
    }
    
    if (!filter_var($author_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Invalid email address.");
    }
    
    if (!empty($author_website) && !filter_var($author_website, FILTER_VALIDATE_URL)) {
        throw new Exception("Invalid website URL.");
    }
    
    // Check if post exists and allows comments
    $stmt = $db->prepare("SELECT id, title, allow_comments FROM blog_posts WHERE id = ? AND status = 'Published'");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    
    if (!$post) {
        throw new Exception("Post not found.");
    }
    
    if (!$post['allow_comments']) {
        throw new Exception("Comments are not allowed on this post.");
    }
    
    // Insert comment (pending approval)
    $stmt = $db->prepare("INSERT INTO blog_comments (post_id, author_name, author_email, author_website, content, ip_address, user_agent) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $post_id,
        $author_name,
        $author_email,
        $author_website ?: null,
        $content,
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT'] ?? ''
    ]);
    
    $_SESSION['success'] = "Thank you for your comment! It will be reviewed and published shortly.";
    
} catch (Exception $e) {
    $_SESSION['error'] = $e->getMessage();
}

// Redirect back to the post
$referer = $_SERVER['HTTP_REFERER'] ?? 'blog.php';
header("Location: $referer");
exit();
?>
