<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

$post_id = $_GET['id'] ?? null;
$is_edit = !empty($post_id);

// Initialize variables
$post = [
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'featured_image' => '',
    'category_id' => '',
    'status' => 'Draft',
    'visibility' => 'Public',
    'password' => '',
    'published_at' => '',
    'allow_comments' => true,
    'is_featured' => false
];

$seo_data = [
    'meta_title' => '',
    'meta_description' => '',
    'meta_keywords' => '',
    'focus_keyword' => '',
    'canonical_url' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug: Log POST data
        error_log("POST data received: " . print_r($_POST, true));
        
        // Check if required functions exist
        if (!function_exists('sanitizeInput')) {
            throw new Exception("sanitizeInput function not found. Please check config.php");
        }
        
        // Sanitize inputs
        $title = sanitizeInput($_POST['title'] ?? '');
        $slug = sanitizeInput($_POST['slug'] ?? '');
        $excerpt = sanitizeInput($_POST['excerpt'] ?? '');
        $content = $_POST['content'] ?? ''; // Don't sanitize content as it may contain HTML
        $category_id = intval($_POST['category_id'] ?? 0) ?: null;
        $status = sanitizeInput($_POST['status'] ?? 'Draft');
        $visibility = sanitizeInput($_POST['visibility'] ?? 'Public');
        $password = sanitizeInput($_POST['password'] ?? '');
        $published_at = $_POST['published_at'] ?? null;
        $allow_comments = isset($_POST['allow_comments']);
        $is_featured = isset($_POST['is_featured']);
        
        // SEO data
        $meta_title = sanitizeInput($_POST['meta_title'] ?? '');
        $meta_description = sanitizeInput($_POST['meta_description'] ?? '');
        $meta_keywords = sanitizeInput($_POST['meta_keywords'] ?? '');
        $focus_keyword = sanitizeInput($_POST['focus_keyword'] ?? '');
        
        // Debug: Log sanitized data
        error_log("Sanitized data - Title: $title, Content length: " . strlen($content));
        
        // Validation
        if (empty($title)) {
            throw new Exception("Title is required.");
        }
        if (empty($content)) {
            throw new Exception("Content is required.");
        }
        
        // Generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            $slug = preg_replace('/-+/', '-', $slug); // Replace multiple hyphens with single hyphen
        }
        
        // Check if slug exists
        $slug_check_sql = "SELECT id FROM blog_posts WHERE slug = ?";
        if ($is_edit) {
            $slug_check_sql .= " AND id != ?";
        }
        $stmt = $db->prepare($slug_check_sql);
        $params = [$slug];
        if ($is_edit) {
            $params[] = $post_id;
        }
        $stmt->execute($params);
        if ($stmt->fetch()) {
            $slug .= '-' . time();
        }
        
        // Calculate reading time (average 200 words per minute)
        $word_count = str_word_count(strip_tags($content));
        $reading_time = max(1, ceil($word_count / 200));
        
        // Debug: Check database connection
        if (!$db) {
            throw new Exception("Database connection not available");
        }
        
        // Debug: Check admin session
        if (!isset($_SESSION['admin_id'])) {
            throw new Exception("Admin session not found. Please login again.");
        }
        
        error_log("About to save post - Title: $title, Slug: $slug, Status: $status");
        
        if ($is_edit) {
            // Update existing post
            $sql = "UPDATE blog_posts SET 
                    title = ?, slug = ?, excerpt = ?, content = ?, category_id = ?, 
                    status = ?, visibility = ?, password = ?, published_at = ?, 
                    allow_comments = ?, is_featured = ?, reading_time = ?, updated_at = NOW()
                    WHERE id = ?";
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare update statement: " . $db->errorInfo()[2]);
            }
            
            $result = $stmt->execute([
                $title, $slug, $excerpt, $content, $category_id,
                $status, $visibility, $password, $published_at,
                $allow_comments, $is_featured, $reading_time, $post_id
            ]);
            
            if (!$result) {
                throw new Exception("Failed to update post: " . $stmt->errorInfo()[2]);
            }
            
            error_log("Post updated successfully: $title");
            logActivity($_SESSION['admin_id'], null, 'Blog Management', "Updated blog post: $title", $db);
        } else {
            // Create new post
            $sql = "INSERT INTO blog_posts 
                    (title, slug, excerpt, content, category_id, author_id, status, visibility, 
                     password, published_at, allow_comments, is_featured, reading_time) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Failed to prepare insert statement: " . $db->errorInfo()[2]);
            }
            
            $result = $stmt->execute([
                $title, $slug, $excerpt, $content, $category_id, $_SESSION['admin_id'],
                $status, $visibility, $password, $published_at, $allow_comments, $is_featured, $reading_time
            ]);
            
            if (!$result) {
                throw new Exception("Failed to create post: " . $stmt->errorInfo()[2]);
            }
            
            $post_id = $db->lastInsertId();
            error_log("Post created successfully: $title (ID: $post_id)");
            logActivity($_SESSION['admin_id'], null, 'Blog Management', "Created blog post: $title", $db);
        }
        
        // Update SEO data
        error_log("Updating SEO data for post ID: $post_id");
        $seo_sql = "INSERT INTO blog_seo_data 
                    (post_id, meta_title, meta_description, meta_keywords, focus_keyword, canonical_url) 
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    meta_title = VALUES(meta_title),
                    meta_description = VALUES(meta_description),
                    meta_keywords = VALUES(meta_keywords),
                    focus_keyword = VALUES(focus_keyword),
                    canonical_url = VALUES(canonical_url),
                    updated_at = NOW()";
        
        $canonical_url = "https://yourdomain.com/blog/" . $slug;
        $stmt = $db->prepare($seo_sql);
        if (!$stmt) {
            throw new Exception("Failed to prepare SEO statement: " . $db->errorInfo()[2]);
        }
        
        $seo_result = $stmt->execute([$post_id, $meta_title, $meta_description, $meta_keywords, $focus_keyword, $canonical_url]);
        if (!$seo_result) {
            error_log("SEO data update failed, but continuing: " . $stmt->errorInfo()[2]);
        }
        
        $_SESSION['success'] = $is_edit ? "Post updated successfully!" : "Post created successfully!";
        error_log("Redirecting to edit page for post ID: $post_id");
        header("Location: admin-blog-edit.php?id=$post_id");
        exit();
        
    } catch (Exception $e) {
        error_log("Error in post creation: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        $_SESSION['error'] = "Error: " . $e->getMessage();
    } catch (PDOException $e) {
        error_log("Database error in post creation: " . $e->getMessage());
        $_SESSION['error'] = "Database error: " . $e->getMessage();
    } catch (Error $e) {
        error_log("Fatal error in post creation: " . $e->getMessage());
        $_SESSION['error'] = "System error: " . $e->getMessage();
    }
}

// Load existing post data if editing
if ($is_edit) {
    try {
        $stmt = $db->prepare("SELECT bp.*, bsd.* FROM blog_posts bp 
                             LEFT JOIN blog_seo_data bsd ON bp.id = bsd.post_id 
                             WHERE bp.id = ?");
        $stmt->execute([$post_id]);
        $data = $stmt->fetch();
        
        if (!$data) {
            $_SESSION['error'] = "Post not found.";
            header('Location: admin-blog.php');
            exit();
        }
        
        $post = array_merge($post, $data);
        $seo_data = array_merge($seo_data, $data);
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Failed to load post data.";
        header('Location: admin-blog.php');
        exit();
    }
}

// Get categories
try {
    $stmt = $db->prepare("SELECT * FROM blog_categories WHERE status = 'Active' ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?php echo $is_edit ? 'Edit' : 'Create'; ?> Blog Post - M25 Travel & Tour Agency</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Libraries Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <style>
        .table-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .top-navbar {
            background: #13357B !important;
        }
        .navbar-brand {
            color: white !important;
            font-weight: bold;
        }
        .seo-score {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
        }
        .seo-score.good { background-color: #28a745; }
        .seo-score.average { background-color: #ffc107; }
        .seo-score.poor { background-color: #dc3545; }
        
        #editor {
            height: 300px;
        }
        
        .ai-suggestion {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <?php include 'includes/admin-sidebar.php'; ?>
    
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg top-navbar">
        <div class="container-fluid">
            <span class="navbar-brand"><?php echo $is_edit ? 'Edit Post' : 'Create New Post'; ?></span>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </span>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container-fluid p-4">
        <form method="POST" id="postForm">
            <div class="row">
                <!-- Main Content -->
                <div class="col-lg-8">
                    <div class="table-card">
                        <h5 class="mb-4">
                            <i class="fas fa-edit"></i> 
                            <?php echo $is_edit ? 'Edit Post' : 'Create New Post'; ?>
                        </h5>
                        
                        <!-- Debug Info -->
                        <?php if (isset($_GET['debug'])): ?>
                            <div class="alert alert-info">
                                <strong>Debug Info:</strong><br>
                                - PHP Version: <?php echo PHP_VERSION; ?><br>
                                - POST Method: <?php echo $_SERVER['REQUEST_METHOD'] ?? 'Not set'; ?><br>
                                - Admin ID: <?php echo $_SESSION['admin_id'] ?? 'Not set'; ?><br>
                                - Database: <?php echo $db ? 'Connected' : 'Not connected'; ?><br>
                                - sanitizeInput function: <?php echo function_exists('sanitizeInput') ? 'Available' : 'Missing'; ?><br>
                                - logActivity function: <?php echo function_exists('logActivity') ? 'Available' : 'Missing'; ?>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Alerts -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <strong>Success!</strong> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <strong>Error!</strong> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                <hr>
                                <small>
                                    <strong>Troubleshooting:</strong><br>
                                    1. Check that all required fields are filled<br>
                                    2. Ensure the blog database tables exist<br>
                                    3. Verify admin login session<br>
                                    4. Add ?debug=1 to URL for more info
                                </small>
                            </div>
                        <?php endif; ?>

                            <!-- Title -->
                            <div class="mb-3">
                                <label class="form-label">Title *</label>
                                <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
                            </div>

                            <!-- Slug -->
                            <div class="mb-3">
                                <label class="form-label">URL Slug</label>
                                <div class="input-group">
                                    <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($post['slug']); ?>" placeholder="Auto-generated from title">
                                    <button type="button" class="btn btn-outline-secondary" onclick="regenerateSlug()" title="Regenerate slug from title">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                                <div class="form-text">Leave empty to auto-generate from title, or click refresh to regenerate</div>
                            </div>

                            <!-- Excerpt -->
                            <div class="mb-3">
                                <label class="form-label">Excerpt</label>
                                <textarea name="excerpt" class="form-control" rows="3" placeholder="Brief description of the post"><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                            </div>

                            <!-- Content Editor -->
                            <div class="mb-3">
                                <label class="form-label">Content *</label>
                                <div id="editor"><?php echo $post['content']; ?></div>
                                <textarea name="content" id="content" style="display:none;"><?php echo htmlspecialchars($post['content']); ?></textarea>
                                <div class="invalid-feedback" id="content-error" style="display:none;">
                                    Content is required.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SEO Section -->
                    <div class="table-card mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0"><i class="fas fa-search"></i> SEO Optimization</h5>
                            <button type="button" class="btn btn-sm btn-primary" onclick="generateAISEO()">
                                <i class="fas fa-robot"></i> AI SEO Optimize
                            </button>
                        </div>
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Focus Keyword</label>
                                        <input type="text" name="focus_keyword" class="form-control" value="<?php echo htmlspecialchars($seo_data['focus_keyword']); ?>" placeholder="Main keyword for this post">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Meta Title</label>
                                        <input type="text" name="meta_title" class="form-control" value="<?php echo htmlspecialchars($seo_data['meta_title']); ?>" maxlength="60" placeholder="SEO title (max 60 characters)">
                                        <div class="form-text">
                                            <span id="meta-title-count">0</span>/60 characters
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Meta Description</label>
                                        <textarea name="meta_description" class="form-control" rows="3" maxlength="160" placeholder="SEO description (max 160 characters)"><?php echo htmlspecialchars($seo_data['meta_description']); ?></textarea>
                                        <div class="form-text">
                                            <span id="meta-desc-count">0</span>/160 characters
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Meta Keywords</label>
                                        <input type="text" name="meta_keywords" class="form-control" value="<?php echo htmlspecialchars($seo_data['meta_keywords']); ?>" placeholder="Comma-separated keywords">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="text-center">
                                        <div class="seo-score good mb-3" id="seo-score">85</div>
                                        <h6>SEO Score</h6>
                                        <div id="seo-suggestions">
                                            <div class="ai-suggestion">
                                                <i class="fas fa-lightbulb mb-2"></i>
                                                <p class="mb-0">AI suggestions will appear here after analysis</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Publish Box -->
                    <div class="table-card">
                        <h6 class="mb-3"><i class="fas fa-paper-plane"></i> Publish</h6>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Draft" <?php echo $post['status'] === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="Published" <?php echo $post['status'] === 'Published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="Scheduled" <?php echo $post['status'] === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                    <option value="Archived" <?php echo $post['status'] === 'Archived' ? 'selected' : ''; ?>>Archived</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-select">
                                    <option value="Public" <?php echo $post['visibility'] === 'Public' ? 'selected' : ''; ?>>Public</option>
                                    <option value="Private" <?php echo $post['visibility'] === 'Private' ? 'selected' : ''; ?>>Private</option>
                                    <option value="Password Protected" <?php echo $post['visibility'] === 'Password Protected' ? 'selected' : ''; ?>>Password Protected</option>
                                </select>
                            </div>

                            <div class="mb-3" id="password-field" style="display: none;">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" value="<?php echo htmlspecialchars($post['password']); ?>">
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" name="allow_comments" class="form-check-input" <?php echo $post['allow_comments'] ? 'checked' : ''; ?>>
                                <label class="form-check-label">Allow Comments</label>
                            </div>

                            <div class="form-check mb-3">
                                <input type="checkbox" name="is_featured" class="form-check-input" <?php echo $post['is_featured'] ? 'checked' : ''; ?>>
                                <label class="form-check-label">Featured Post</label>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 
                                    <?php echo $is_edit ? 'Update Post' : 'Create Post'; ?>
                                </button>
                                <?php if ($is_edit && $post['status'] === 'Published'): ?>
                                    <a href="blog-post.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i> Preview
                                    </a>
                                <?php endif; ?>
                                <a href="admin-blog.php" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Back to Posts
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Category -->
                    <div class="table-card mt-3">
                        <h6 class="mb-3"><i class="fas fa-folder"></i> Category</h6>
                            <select name="category_id" class="form-select">
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $post['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mt-2">
                                <a href="admin-blog-categories.php" class="btn btn-sm btn-outline-primary">Manage Categories</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    <script>
        // Initialize Quill editor
        var quill = new Quill('#editor', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['link', 'image', 'video'],
                    ['clean']
                ]
            }
        });

        // Update hidden textarea when form is submitted and validate content
        document.getElementById('postForm').addEventListener('submit', function(e) {
            // Get the Quill editor content
            const content = quill.root.innerHTML;
            const textContent = quill.getText().trim();
            
            // Update the hidden textarea
            document.getElementById('content').value = content;
            
            // Custom validation for content
            const contentError = document.getElementById('content-error');
            const editorContainer = document.getElementById('editor');
            
            if (!textContent || textContent.length < 10) {
                // Show error
                contentError.style.display = 'block';
                editorContainer.style.border = '1px solid #dc3545';
                
                // Scroll to editor
                editorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Prevent form submission
                e.preventDefault();
                return false;
            } else {
                // Hide error
                contentError.style.display = 'none';
                editorContainer.style.border = '';
            }
        });

        // Clear content validation error when user starts typing
        quill.on('text-change', function() {
            const contentError = document.getElementById('content-error');
            const editorContainer = document.getElementById('editor');
            
            if (contentError.style.display === 'block') {
                contentError.style.display = 'none';
                editorContainer.style.border = '';
            }
        });

        // Auto-generate slug from title
        function generateSlug(text) {
            return text
                .toLowerCase()
                .replace(/[^\w\s-]/g, '') // Remove special characters except spaces and hyphens
                .replace(/\s+/g, '-')     // Replace spaces with hyphens
                .replace(/-+/g, '-')      // Replace multiple hyphens with single hyphen
                .trim('-');               // Remove leading/trailing hyphens
        }

        // Auto-generate slug when title changes (only if slug is empty)
        document.querySelector('input[name="title"]').addEventListener('input', function() {
            const slugField = document.querySelector('input[name="slug"]');
            if (!slugField.value.trim()) { // Only auto-generate if slug field is empty
                slugField.value = generateSlug(this.value);
            }
        });

        // Manual slug regeneration function
        function regenerateSlug() {
            const titleField = document.querySelector('input[name="title"]');
            const slugField = document.querySelector('input[name="slug"]');
            if (titleField.value.trim()) {
                slugField.value = generateSlug(titleField.value);
            }
        }

        // Character counters
        function updateCharCount(input, counter, max) {
            const count = input.value.length;
            document.getElementById(counter).textContent = count;
            
            if (count > max * 0.9) {
                document.getElementById(counter).style.color = '#dc3545';
            } else if (count > max * 0.7) {
                document.getElementById(counter).style.color = '#ffc107';
            } else {
                document.getElementById(counter).style.color = '#28a745';
            }
        }

        document.querySelector('input[name="meta_title"]').addEventListener('input', function() {
            updateCharCount(this, 'meta-title-count', 60);
        });

        document.querySelector('textarea[name="meta_description"]').addEventListener('input', function() {
            updateCharCount(this, 'meta-desc-count', 160);
        });

        // Show/hide password field
        document.querySelector('select[name="visibility"]').addEventListener('change', function() {
            const passwordField = document.getElementById('password-field');
            if (this.value === 'Password Protected') {
                passwordField.style.display = 'block';
            } else {
                passwordField.style.display = 'none';
            }
        });

        // Initialize character counts
        document.addEventListener('DOMContentLoaded', function() {
            updateCharCount(document.querySelector('input[name="meta_title"]'), 'meta-title-count', 60);
            updateCharCount(document.querySelector('textarea[name="meta_description"]'), 'meta-desc-count', 160);
            
            // Show password field if needed
            if (document.querySelector('select[name="visibility"]').value === 'Password Protected') {
                document.getElementById('password-field').style.display = 'block';
            }
        });

        // AI SEO Optimization
        function generateAISEO() {
            const title = document.querySelector('input[name="title"]').value;
            const content = quill.getText();
            
            if (!title || !content) {
                alert('Please enter a title and content first.');
                return;
            }
            
            // Simulate AI SEO suggestions
            const suggestions = [
                "Consider adding the focus keyword to the first paragraph",
                "Meta description could be more compelling with action words",
                "Add internal links to related content",
                "Include alt text for images",
                "Consider adding subheadings for better readability"
            ];
            
            const suggestionsHtml = suggestions.map(suggestion => 
                `<div class="ai-suggestion">
                    <i class="fas fa-lightbulb mb-2"></i>
                    <p class="mb-0">${suggestion}</p>
                </div>`
            ).join('');
            
            document.getElementById('seo-suggestions').innerHTML = suggestionsHtml;
            
            // Auto-generate meta title and description
            if (!document.querySelector('input[name="meta_title"]').value) {
                document.querySelector('input[name="meta_title"]').value = title.substring(0, 60);
            }
            
            if (!document.querySelector('textarea[name="meta_description"]').value) {
                const excerpt = content.substring(0, 160).trim();
                document.querySelector('textarea[name="meta_description"]').value = excerpt;
            }
            
            updateCharCount(document.querySelector('input[name="meta_title"]'), 'meta-title-count', 60);
            updateCharCount(document.querySelector('textarea[name="meta_description"]'), 'meta-desc-count', 160);
        }
    </script>
</body>
</html>
