<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$category_filter = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

// Handle bulk actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'])) {
    $action = $_POST['bulk_action'];
    $selected_posts = $_POST['selected_posts'] ?? [];
    
    if (!empty($selected_posts) && in_array($action, ['publish', 'draft', 'archive', 'delete'])) {
        try {
            $placeholders = str_repeat('?,', count($selected_posts) - 1) . '?';
            
            switch ($action) {
                case 'publish':
                    $stmt = $db->prepare("UPDATE blog_posts SET status = 'Published', published_at = NOW() WHERE id IN ($placeholders)");
                    $stmt->execute($selected_posts);
                    $_SESSION['success'] = count($selected_posts) . " posts published successfully.";
                    break;
                case 'draft':
                    $stmt = $db->prepare("UPDATE blog_posts SET status = 'Draft' WHERE id IN ($placeholders)");
                    $stmt->execute($selected_posts);
                    $_SESSION['success'] = count($selected_posts) . " posts moved to draft.";
                    break;
                case 'archive':
                    $stmt = $db->prepare("UPDATE blog_posts SET status = 'Archived' WHERE id IN ($placeholders)");
                    $stmt->execute($selected_posts);
                    $_SESSION['success'] = count($selected_posts) . " posts archived.";
                    break;
                case 'delete':
                    $stmt = $db->prepare("DELETE FROM blog_posts WHERE id IN ($placeholders)");
                    $stmt->execute($selected_posts);
                    $_SESSION['success'] = count($selected_posts) . " posts deleted successfully.";
                    break;
            }
            
            logActivity($_SESSION['admin_id'], null, 'Blog Management', "Bulk action: $action on " . count($selected_posts) . " posts", $db);
        } catch (Exception $e) {
            $_SESSION['error'] = "Error performing bulk action: " . $e->getMessage();
        }
    }
    
    header('Location: admin-blog.php');
    exit();
}

// Build query conditions
$where_conditions = [];
$params = [];

if ($status_filter !== 'all') {
    $where_conditions[] = "bp.status = ?";
    $params[] = $status_filter;
}

if ($category_filter !== 'all') {
    $where_conditions[] = "bp.category_id = ?";
    $params[] = $category_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(bp.title LIKE ? OR bp.content LIKE ? OR au.full_name LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

try {
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total 
                  FROM blog_posts bp 
                  LEFT JOIN admin_users au ON bp.author_id = au.id 
                  LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
                  $where_clause";
    $stmt = $db->prepare($count_sql);
    $stmt->execute($params);
    $total_posts = $stmt->fetch()['total'];
    $total_pages = ceil($total_posts / $per_page);

    // Get posts
    $sql = "SELECT bp.*, au.full_name as author_name, bc.name as category_name, bc.color as category_color,
                   (SELECT COUNT(*) FROM blog_comments WHERE post_id = bp.id AND status = 'Approved') as comment_count
            FROM blog_posts bp 
            LEFT JOIN admin_users au ON bp.author_id = au.id 
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
            $where_clause
            ORDER BY bp.created_at DESC 
            LIMIT $per_page OFFSET $offset";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    // Get categories for filter
    $stmt = $db->prepare("SELECT * FROM blog_categories WHERE status = 'Active' ORDER BY name");
    $stmt->execute();
    $categories = $stmt->fetchAll();

    // Get statistics
    $stmt = $db->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Published' THEN 1 ELSE 0 END) as published,
        SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) as draft,
        SUM(CASE WHEN status = 'Scheduled' THEN 1 ELSE 0 END) as scheduled,
        SUM(views) as total_views
        FROM blog_posts");
    $stmt->execute();
    $stats = $stmt->fetch();

} catch (Exception $e) {
    error_log("Blog management error: " . $e->getMessage());
    $posts = [];
    $categories = [];
    $stats = ['total' => 0, 'published' => 0, 'draft' => 0, 'scheduled' => 0, 'total_views' => 0];
    $_SESSION['error'] = "Failed to load blog posts.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Blog Management - M25 Travel & Tour Agency</title>
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
    
    <style>
        .status-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        .category-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            color: white;
            border-radius: 0.25rem;
        }
        .post-thumbnail {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 0.25rem;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
            border-left: 4px solid;
        }
        .stats-card.total { border-left-color: #007bff; }
        .stats-card.published { border-left-color: #28a745; }
        .stats-card.draft { border-left-color: #ffc107; }
        .stats-card.views { border-left-color: #17a2b8; }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        .stat-number {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }
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
    </style>
</head>
<body>
    <?php include 'includes/admin-sidebar.php'; ?>
    
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg top-navbar">
        <div class="container-fluid">
            <span class="navbar-brand">Blog Management</span>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </span>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container-fluid p-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-blog"></i> Blog Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="admin-blog-create.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Post
                        </a>
                    </div>
                </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card total">
                    <div class="stat-icon text-primary">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-number text-primary"><?php echo number_format($stats['total']); ?></div>
                    <div class="text-muted">Total Posts</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card published">
                    <div class="stat-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number text-success"><?php echo number_format($stats['published']); ?></div>
                    <div class="text-muted">Published</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card draft">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-edit"></i>
                    </div>
                    <div class="stat-number text-warning"><?php echo number_format($stats['draft']); ?></div>
                    <div class="text-muted">Drafts</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card views">
                    <div class="stat-icon text-info">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-number text-info"><?php echo number_format($stats['total_views']); ?></div>
                    <div class="text-muted">Total Views</div>
                </div>
            </div>
        </div>

                <!-- Alerts -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

        <!-- Filters -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="table-card">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="Published" <?php echo $status_filter === 'Published' ? 'selected' : ''; ?>>Published</option>
                                <option value="Draft" <?php echo $status_filter === 'Draft' ? 'selected' : ''; ?>>Draft</option>
                                <option value="Scheduled" <?php echo $status_filter === 'Scheduled' ? 'selected' : ''; ?>>Scheduled</option>
                                <option value="Archived" <?php echo $status_filter === 'Archived' ? 'selected' : ''; ?>>Archived</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="all">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo $category_filter == $category['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search posts..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary d-block">Filter</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Posts Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">Blog Posts (<?php echo number_format($total_posts); ?>)</h5>
                        <form method="POST" id="bulkForm" class="d-flex gap-2">
                            <select name="bulk_action" class="form-select form-select-sm" style="width: auto;">
                                <option value="">Bulk Actions</option>
                                <option value="publish">Publish</option>
                                <option value="draft">Move to Draft</option>
                                <option value="archive">Archive</option>
                                <option value="delete">Delete</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary" onclick="return confirmBulkAction()">Apply</button>
                        </form>
                    </div>
                        <?php if (empty($posts)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-blog fa-3x text-muted mb-3"></i>
                                <h5>No blog posts found</h5>
                                <p class="text-muted">Start creating engaging content for your audience.</p>
                                <a href="admin-blog-create.php" class="btn btn-primary">Create Your First Post</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="30">
                                                <input type="checkbox" id="selectAll" class="form-check-input">
                                            </th>
                                            <th width="80">Image</th>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Views</th>
                                            <th>Comments</th>
                                            <th>Date</th>
                                            <th width="120">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($posts as $post): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" name="selected_posts[]" value="<?php echo $post['id']; ?>" class="form-check-input post-checkbox">
                                                </td>
                                                <td>
                                                    <?php if ($post['featured_image']): ?>
                                                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="Post thumbnail" class="post-thumbnail">
                                                    <?php else: ?>
                                                        <div class="post-thumbnail bg-light d-flex align-items-center justify-content-center">
                                                            <i class="fas fa-image text-muted"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div>
                                                        <h6 class="mb-1">
                                                            <a href="admin-blog-edit.php?id=<?php echo $post['id']; ?>" class="text-decoration-none">
                                                                <?php echo htmlspecialchars($post['title']); ?>
                                                            </a>
                                                            <?php if ($post['is_featured']): ?>
                                                                <i class="fas fa-star text-warning" title="Featured Post"></i>
                                                            <?php endif; ?>
                                                        </h6>
                                                        <?php if ($post['excerpt']): ?>
                                                            <small class="text-muted"><?php echo htmlspecialchars(substr($post['excerpt'], 0, 100)) . '...'; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($post['author_name']); ?></td>
                                                <td>
                                                    <?php if ($post['category_name']): ?>
                                                        <span class="category-badge" style="background-color: <?php echo $post['category_color']; ?>">
                                                            <?php echo htmlspecialchars($post['category_name']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted">Uncategorized</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_classes = [
                                                        'Published' => 'bg-success',
                                                        'Draft' => 'bg-warning',
                                                        'Scheduled' => 'bg-info',
                                                        'Archived' => 'bg-secondary'
                                                    ];
                                                    $status_class = $status_classes[$post['status']] ?? 'bg-secondary';
                                                    ?>
                                                    <span class="badge status-badge <?php echo $status_class; ?>">
                                                        <?php echo $post['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <i class="fas fa-eye text-muted"></i>
                                                    <?php echo number_format($post['views']); ?>
                                                </td>
                                                <td>
                                                    <i class="fas fa-comments text-muted"></i>
                                                    <?php echo number_format($post['comment_count']); ?>
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        <?php echo formatDate($post['created_at'], 'M j, Y'); ?><br>
                                                        <?php echo formatDate($post['created_at'], 'g:i A'); ?>
                                                    </small>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="admin-blog-edit.php?id=<?php echo $post['id']; ?>" class="btn btn-outline-primary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($post['status'] === 'Published'): ?>
                                                            <a href="blog-post.php?slug=<?php echo $post['slug']; ?>" target="_blank" class="btn btn-outline-info" title="View">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-danger" onclick="deletePost(<?php echo $post['id']; ?>)" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="mt-4">
                            <nav aria-label="Posts pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>&category=<?php echo $category_filter; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Select all functionality
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.post-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Update select all when individual checkboxes change
        document.querySelectorAll('.post-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const allCheckboxes = document.querySelectorAll('.post-checkbox');
                const checkedCheckboxes = document.querySelectorAll('.post-checkbox:checked');
                const selectAllCheckbox = document.getElementById('selectAll');
                
                if (checkedCheckboxes.length === 0) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = false;
                } else if (checkedCheckboxes.length === allCheckboxes.length) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = true;
                } else {
                    selectAllCheckbox.indeterminate = true;
                }
            });
        });

        function confirmBulkAction() {
            const selectedPosts = document.querySelectorAll('.post-checkbox:checked');
            const action = document.querySelector('select[name="bulk_action"]').value;
            
            if (selectedPosts.length === 0) {
                alert('Please select at least one post.');
                return false;
            }
            
            if (!action) {
                alert('Please select an action.');
                return false;
            }
            
            const actionText = action === 'delete' ? 'delete' : action;
            return confirm(`Are you sure you want to ${actionText} ${selectedPosts.length} selected post(s)?`);
        }

        function deletePost(postId) {
            if (confirm('Are you sure you want to delete this post? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="bulk_action" value="delete">
                    <input type="hidden" name="selected_posts[]" value="${postId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
