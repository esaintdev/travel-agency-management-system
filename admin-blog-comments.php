<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Handle comment actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        $comment_id = intval($_POST['comment_id']);
        
        switch ($action) {
            case 'approve':
                $stmt = $db->prepare("UPDATE blog_comments SET status = 'Approved' WHERE id = ?");
                $stmt->execute([$comment_id]);
                $_SESSION['success'] = "Comment approved successfully!";
                break;
                
            case 'reject':
                $stmt = $db->prepare("UPDATE blog_comments SET status = 'Spam' WHERE id = ?");
                $stmt->execute([$comment_id]);
                $_SESSION['success'] = "Comment marked as spam!";
                break;
                
            case 'delete':
                $stmt = $db->prepare("DELETE FROM blog_comments WHERE id = ?");
                $stmt->execute([$comment_id]);
                $_SESSION['success'] = "Comment deleted successfully!";
                break;
        }
        
        logActivity($_SESSION['admin_id'], null, 'Blog Management', "Comment action: $action on comment ID: $comment_id", $db);
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: admin-blog-comments.php');
    exit();
}

// Get comments
$status_filter = $_GET['status'] ?? 'all';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_conditions = [];
$params = [];

if ($status_filter !== 'all') {
    $where_conditions[] = "bc.status = ?";
    $params[] = $status_filter;
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

try {
    // Get total count
    $count_sql = "SELECT COUNT(*) as total FROM blog_comments bc $where_clause";
    $stmt = $db->prepare($count_sql);
    $stmt->execute($params);
    $total_comments = $stmt->fetch()['total'];
    $total_pages = ceil($total_comments / $per_page);

    // Get comments
    $sql = "SELECT bc.*, bp.title as post_title, bp.slug as post_slug 
            FROM blog_comments bc 
            LEFT JOIN blog_posts bp ON bc.post_id = bp.id 
            $where_clause
            ORDER BY bc.created_at DESC 
            LIMIT $per_page OFFSET $offset";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $comments = $stmt->fetchAll();

    // Get comment statistics
    $stmt = $db->prepare("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'Spam' THEN 1 ELSE 0 END) as spam
        FROM blog_comments");
    $stmt->execute();
    $stats = $stmt->fetch();

} catch (Exception $e) {
    $comments = [];
    $stats = ['total' => 0, 'pending' => 0, 'approved' => 0, 'spam' => 0];
    $_SESSION['error'] = "Failed to load comments.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Blog Comments - M25 Travel & Tour Agency</title>
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
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            margin-bottom: 30px;
            border-left: 4px solid;
        }
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
        .border-left-primary { border-left-color: #007bff !important; }
        .border-left-warning { border-left-color: #ffc107 !important; }
        .border-left-success { border-left-color: #28a745 !important; }
        .border-left-danger { border-left-color: #dc3545 !important; }
    </style>
</head>
<body>
    <?php include 'includes/admin-sidebar.php'; ?>
    
    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg top-navbar">
        <div class="container-fluid">
            <span class="navbar-brand">Blog Comments</span>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </span>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container-fluid p-4">

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card border-left-primary">
                    <div class="stat-icon text-primary">
                        <i class="fas fa-comments"></i>
                    </div>
                    <div class="stat-number text-primary"><?php echo number_format($stats['total']); ?></div>
                    <div class="text-muted">Total Comments</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card border-left-warning">
                    <div class="stat-icon text-warning">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-number text-warning"><?php echo number_format($stats['pending']); ?></div>
                    <div class="text-muted">Pending</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card border-left-success">
                    <div class="stat-icon text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-number text-success"><?php echo number_format($stats['approved']); ?></div>
                    <div class="text-muted">Approved</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card border-left-danger">
                    <div class="stat-icon text-danger">
                        <i class="fas fa-ban"></i>
                    </div>
                    <div class="stat-number text-danger"><?php echo number_format($stats['spam']); ?></div>
                    <div class="text-muted">Spam</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0"><i class="fas fa-comments"></i> Blog Comments</h5>
                        
                        <!-- Status Filter -->
                        <form method="GET" class="d-flex gap-2">
                            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="Pending" <?php echo $status_filter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="Approved" <?php echo $status_filter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                <option value="Spam" <?php echo $status_filter === 'Spam' ? 'selected' : ''; ?>>Spam</option>
                            </select>
                        </form>
                    </div>
                <!-- Alerts -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show m-3">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show m-3">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (empty($comments)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                        <h5>No comments found</h5>
                        <p class="text-muted">Comments will appear here when readers engage with your blog posts.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Author</th>
                                    <th>Comment</th>
                                    <th>Post</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($comments as $comment): ?>
                                    <tr>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($comment['author_name']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($comment['author_email']); ?></small>
                                                <?php if ($comment['author_website']): ?>
                                                    <br><a href="<?php echo htmlspecialchars($comment['author_website']); ?>" target="_blank" class="small">
                                                        <i class="fas fa-external-link-alt"></i> Website
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div style="max-width: 300px;">
                                                <?php echo nl2br(htmlspecialchars(substr($comment['content'], 0, 150))); ?>
                                                <?php if (strlen($comment['content']) > 150): ?>
                                                    <span class="text-muted">...</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="blog-post.php?slug=<?php echo $comment['post_slug']; ?>" target="_blank" class="text-decoration-none">
                                                <?php echo htmlspecialchars(substr($comment['post_title'], 0, 50)) . '...'; ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                            $status_classes = [
                                                'Pending' => 'bg-warning',
                                                'Approved' => 'bg-success',
                                                'Spam' => 'bg-danger'
                                            ];
                                            $status_class = $status_classes[$comment['status']] ?? 'bg-secondary';
                                            ?>
                                            <span class="badge <?php echo $status_class; ?>">
                                                <?php echo $comment['status']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo formatDate($comment['created_at'], 'M j, Y'); ?><br>
                                                <?php echo formatDate($comment['created_at'], 'g:i A'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <?php if ($comment['status'] === 'Pending'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="approve">
                                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-success" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;">
                                                        <input type="hidden" name="action" value="reject">
                                                        <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                        <button type="submit" class="btn btn-outline-warning" title="Mark as Spam">
                                                            <i class="fas fa-ban"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="comment_id" value="<?php echo $comment['id']; ?>">
                                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this comment?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="card-footer">
                            <nav aria-label="Comments pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&status=<?php echo $status_filter; ?>">Previous</a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&status=<?php echo $status_filter; ?>">Next</a>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .border-left-primary { border-left: 4px solid #007bff !important; }
        .border-left-warning { border-left: 4px solid #ffc107 !important; }
        .border-left-success { border-left: 4px solid #28a745 !important; }
        .border-left-danger { border-left: 4px solid #dc3545 !important; }
    </style>
</body>
</html>
