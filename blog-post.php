<?php
require_once 'config.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header('Location: blog.php');
    exit();
}

try {
    // Get post with SEO data
    $stmt = $db->prepare("SELECT bp.*, au.full_name as author_name, bc.name as category_name, bc.slug as category_slug, bc.color as category_color,
                                 bsd.meta_title, bsd.meta_description, bsd.meta_keywords, bsd.canonical_url
                         FROM blog_posts bp 
                         LEFT JOIN admin_users au ON bp.author_id = au.id 
                         LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
                         LEFT JOIN blog_seo_data bsd ON bp.id = bsd.post_id
                         WHERE bp.slug = ? AND bp.status = 'Published' AND bp.visibility = 'Public'");
    $stmt->execute([$slug]);
    $post = $stmt->fetch();

    if (!$post) {
        header('HTTP/1.0 404 Not Found');
        include '404.php';
        exit();
    }

    // Update view count
    $stmt = $db->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
    $stmt->execute([$post['id']]);

    // Get post tags
    $stmt = $db->prepare("SELECT bt.* FROM blog_tags bt 
                         JOIN blog_post_tags bpt ON bt.id = bpt.tag_id 
                         WHERE bpt.post_id = ?");
    $stmt->execute([$post['id']]);
    $tags = $stmt->fetchAll();

    // Get related posts
    $stmt = $db->prepare("SELECT bp.*, bc.name as category_name, bc.color as category_color 
                         FROM blog_posts bp 
                         LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
                         WHERE bp.category_id = ? AND bp.id != ? AND bp.status = 'Published' AND bp.visibility = 'Public'
                         ORDER BY bp.published_at DESC 
                         LIMIT 3");
    $stmt->execute([$post['category_id'], $post['id']]);
    $related_posts = $stmt->fetchAll();

    // Get comments (if enabled)
    $comments = [];
    if ($post['allow_comments']) {
        $stmt = $db->prepare("SELECT * FROM blog_comments 
                             WHERE post_id = ? AND status = 'Approved' AND parent_id IS NULL 
                             ORDER BY created_at ASC");
        $stmt->execute([$post['id']]);
        $comments = $stmt->fetchAll();
    }

} catch (Exception $e) {
    error_log("Blog post error: " . $e->getMessage());
    header('HTTP/1.0 500 Internal Server Error');
    exit();
}

// SEO meta tags
$page_title = $post['meta_title'] ?: $post['title'];
$page_description = $post['meta_description'] ?: ($post['excerpt'] ?: substr(strip_tags($post['content']), 0, 160));
$page_keywords = $post['meta_keywords'] ?: '';
$canonical_url = $post['canonical_url'] ?: "https://yourdomain.com/blog-post.php?slug=" . $post['slug'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($page_title); ?> - M25 Travel & Tour Agency</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($page_description); ?>">
    <?php if ($page_keywords): ?>
        <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords); ?>">
    <?php endif; ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url); ?>">
    
    <!-- Open Graph -->
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <meta property="og:type" content="article">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url); ?>">
    <?php if ($post['featured_image']): ?>
        <meta property="og:image" content="<?php echo htmlspecialchars($post['featured_image']); ?>">
    <?php endif; ?>
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_description); ?>">
    <?php if ($post['featured_image']): ?>
        <meta name="twitter:image" content="<?php echo htmlspecialchars($post['featured_image']); ?>">
    <?php endif; ?>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/themes/prism.min.css" rel="stylesheet">
    <style>
        .article-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 0;
        }
        .article-meta {
            background: rgba(255,255,255,0.1);
            border-radius: 0.5rem;
            padding: 1rem;
            margin-top: 2rem;
        }
        .article-content {
            font-size: 1.1rem;
            line-height: 1.8;
        }
        .article-content h1, .article-content h2, .article-content h3 {
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .article-content img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
            margin: 1rem 0;
        }
        .share-buttons a {
            display: inline-block;
            margin: 0.25rem;
            padding: 0.5rem 1rem;
            border-radius: 0.25rem;
            color: white;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .share-buttons a:hover {
            opacity: 0.8;
            color: white;
        }
        .share-facebook { background: #3b5998; }
        .share-twitter { background: #1da1f2; }
        .share-linkedin { background: #0077b5; }
        .share-whatsapp { background: #25d366; }
        
        .tag-list a {
            display: inline-block;
            margin: 0.25rem 0.25rem 0.25rem 0;
            padding: 0.25rem 0.5rem;
            background: #f8f9fa;
            color: #6c757d;
            text-decoration: none;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .tag-list a:hover {
            background: #007bff;
            color: white;
        }
        
        .comment-form {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 2rem;
        }
        
        .related-post-card {
            transition: transform 0.2s;
        }
        .related-post-card:hover {
            transform: translateY(-3px);
        }
    </style>
</head>
<body>
    <!-- Article Header -->
    <header class="article-header">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto text-center">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="index.php" class="text-white-50">Home</a></li>
                            <li class="breadcrumb-item"><a href="blog.php" class="text-white-50">Blog</a></li>
                            <?php if ($post['category_name']): ?>
                                <li class="breadcrumb-item">
                                    <a href="blog.php?category=<?php echo $post['category_slug']; ?>" class="text-white-50">
                                        <?php echo htmlspecialchars($post['category_name']); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li class="breadcrumb-item active text-white-50">Article</li>
                        </ol>
                    </nav>
                    
                    <!-- Category Badge -->
                    <?php if ($post['category_name']): ?>
                        <div class="mb-3">
                            <a href="blog.php?category=<?php echo $post['category_slug']; ?>" class="badge fs-6 text-decoration-none" style="background-color: <?php echo $post['category_color']; ?>">
                                <?php echo htmlspecialchars($post['category_name']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Title -->
                    <h1 class="display-4 fw-bold mb-4"><?php echo htmlspecialchars($post['title']); ?></h1>
                    
                    <!-- Excerpt -->
                    <?php if ($post['excerpt']): ?>
                        <p class="lead mb-4"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                    <?php endif; ?>
                    
                    <!-- Article Meta -->
                    <div class="article-meta">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <i class="fas fa-user mb-2"></i><br>
                                <strong>Author</strong><br>
                                <?php echo htmlspecialchars($post['author_name']); ?>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-calendar mb-2"></i><br>
                                <strong>Published</strong><br>
                                <?php echo formatDate($post['published_at'], 'M j, Y'); ?>
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-clock mb-2"></i><br>
                                <strong>Reading Time</strong><br>
                                <?php echo $post['reading_time']; ?> min read
                            </div>
                            <div class="col-md-3">
                                <i class="fas fa-eye mb-2"></i><br>
                                <strong>Views</strong><br>
                                <?php echo number_format($post['views']); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Featured Image -->
                <?php if ($post['featured_image']): ?>
                    <div class="text-center mb-5">
                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="img-fluid rounded shadow">
                    </div>
                <?php endif; ?>
                
                <!-- Article Content -->
                <div class="article-content">
                    <?php echo $post['content']; ?>
                </div>
                
                <!-- Tags -->
                <?php if (!empty($tags)): ?>
                    <div class="mt-5 pt-4 border-top">
                        <h5><i class="fas fa-tags"></i> Tags</h5>
                        <div class="tag-list">
                            <?php foreach ($tags as $tag): ?>
                                <a href="blog.php?tag=<?php echo $tag['slug']; ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Share Buttons -->
                <div class="mt-4 pt-4 border-top">
                    <h5><i class="fas fa-share-alt"></i> Share This Article</h5>
                    <div class="share-buttons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode($canonical_url); ?>" target="_blank" class="share-facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode($canonical_url); ?>&text=<?php echo urlencode($post['title']); ?>" target="_blank" class="share-twitter">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?php echo urlencode($canonical_url); ?>" target="_blank" class="share-linkedin">
                            <i class="fab fa-linkedin-in"></i> LinkedIn
                        </a>
                        <a href="https://wa.me/?text=<?php echo urlencode($post['title'] . ' - ' . $canonical_url); ?>" target="_blank" class="share-whatsapp">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>
                
                <!-- Comments Section -->
                <?php if ($post['allow_comments']): ?>
                    <div class="mt-5 pt-4 border-top">
                        <h4><i class="fas fa-comments"></i> Comments (<?php echo count($comments); ?>)</h4>
                        
                        <!-- Comment Form -->
                        <div class="comment-form mt-4">
                            <h5>Leave a Comment</h5>
                            <form method="POST" action="process-comment.php">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Name *</label>
                                        <input type="text" name="author_name" class="form-control" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email *</label>
                                        <input type="email" name="author_email" class="form-control" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Website (Optional)</label>
                                    <input type="url" name="author_website" class="form-control">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Comment *</label>
                                    <textarea name="content" class="form-control" rows="5" required></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Post Comment
                                </button>
                            </form>
                        </div>
                        
                        <!-- Comments List -->
                        <?php if (!empty($comments)): ?>
                            <div class="mt-4">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="card mb-3">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong>
                                                    <?php if ($comment['author_website']): ?>
                                                        <a href="<?php echo htmlspecialchars($comment['author_website']); ?>" target="_blank" class="text-muted ms-2">
                                                            <i class="fas fa-external-link-alt"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                                <small class="text-muted">
                                                    <?php echo formatDate($comment['created_at'], 'M j, Y g:i A'); ?>
                                                </small>
                                            </div>
                                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Related Posts -->
        <?php if (!empty($related_posts)): ?>
            <div class="mt-5 pt-5 border-top">
                <div class="row">
                    <div class="col-12 text-center mb-4">
                        <h3><i class="fas fa-newspaper"></i> Related Articles</h3>
                        <p class="text-muted">You might also be interested in these articles</p>
                    </div>
                </div>
                <div class="row">
                    <?php foreach ($related_posts as $related): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card related-post-card h-100">
                                <?php if ($related['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($related['featured_image']); ?>" alt="<?php echo htmlspecialchars($related['title']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;">
                                <?php else: ?>
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                        <i class="fas fa-image fa-3x text-muted"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="card-body">
                                    <?php if ($related['category_name']): ?>
                                        <div class="mb-2">
                                            <span class="badge" style="background-color: <?php echo $related['category_color']; ?>">
                                                <?php echo htmlspecialchars($related['category_name']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                    <h6 class="card-title">
                                        <a href="blog-post.php?slug=<?php echo $related['slug']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars($related['title']); ?>
                                        </a>
                                    </h6>
                                    <?php if ($related['excerpt']): ?>
                                        <p class="card-text text-muted small">
                                            <?php echo htmlspecialchars(substr($related['excerpt'], 0, 100)) . '...'; ?>
                                        </p>
                                    <?php endif; ?>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> <?php echo formatDate($related['published_at'], 'M j, Y'); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.24.1/plugins/autoloader/prism-autoloader.min.js"></script>
</body>
</html>
