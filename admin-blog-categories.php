<?php
require_once 'config.php';

// Check if user is logged in
requireLogin();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        
        switch ($action) {
            case 'create':
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $color = sanitizeInput($_POST['color']);
                
                if (empty($name)) {
                    throw new Exception("Category name is required.");
                }
                
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                
                // Check if name or slug exists
                $stmt = $db->prepare("SELECT id FROM blog_categories WHERE name = ? OR slug = ?");
                $stmt->execute([$name, $slug]);
                if ($stmt->fetch()) {
                    throw new Exception("Category name already exists.");
                }
                
                $stmt = $db->prepare("INSERT INTO blog_categories (name, slug, description, color) VALUES (?, ?, ?, ?)");
                $stmt->execute([$name, $slug, $description, $color]);
                
                $_SESSION['success'] = "Category created successfully!";
                logActivity($_SESSION['admin_id'], null, 'Blog Management', "Created category: $name", $db);
                break;
                
            case 'update':
                $id = intval($_POST['id']);
                $name = sanitizeInput($_POST['name']);
                $description = sanitizeInput($_POST['description']);
                $color = sanitizeInput($_POST['color']);
                $status = sanitizeInput($_POST['status']);
                
                if (empty($name)) {
                    throw new Exception("Category name is required.");
                }
                
                $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
                
                // Check if name or slug exists for other categories
                $stmt = $db->prepare("SELECT id FROM blog_categories WHERE (name = ? OR slug = ?) AND id != ?");
                $stmt->execute([$name, $slug, $id]);
                if ($stmt->fetch()) {
                    throw new Exception("Category name already exists.");
                }
                
                $stmt = $db->prepare("UPDATE blog_categories SET name = ?, slug = ?, description = ?, color = ?, status = ? WHERE id = ?");
                $stmt->execute([$name, $slug, $description, $color, $status, $id]);
                
                $_SESSION['success'] = "Category updated successfully!";
                logActivity($_SESSION['admin_id'], null, 'Blog Management', "Updated category: $name", $db);
                break;
                
            case 'delete':
                $id = intval($_POST['id']);
                
                // Check if category has posts
                $stmt = $db->prepare("SELECT COUNT(*) as count FROM blog_posts WHERE category_id = ?");
                $stmt->execute([$id]);
                $post_count = $stmt->fetch()['count'];
                
                if ($post_count > 0) {
                    throw new Exception("Cannot delete category with existing posts. Please reassign or delete the posts first.");
                }
                
                $stmt = $db->prepare("DELETE FROM blog_categories WHERE id = ?");
                $stmt->execute([$id]);
                
                $_SESSION['success'] = "Category deleted successfully!";
                logActivity($_SESSION['admin_id'], null, 'Blog Management', "Deleted category ID: $id", $db);
                break;
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
    
    header('Location: admin-blog-categories.php');
    exit();
}

// Get all categories
try {
    $stmt = $db->prepare("SELECT bc.*, COUNT(bp.id) as post_count 
                         FROM blog_categories bc 
                         LEFT JOIN blog_posts bp ON bc.id = bp.category_id 
                         GROUP BY bc.id 
                         ORDER BY bc.name");
    $stmt->execute();
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
    $_SESSION['error'] = "Failed to load categories.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Blog Categories - M25 Travel & Tour Agency</title>
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
            <span class="navbar-brand">Blog Categories</span>
            <div class="navbar-nav ms-auto">
                <span class="nav-link text-white">
                    <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                </span>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container-fluid p-4">

        <div class="row">
            <div class="col-md-8">
                <div class="table-card">
                    <h5 class="mb-4"><i class="fas fa-folder"></i> Blog Categories</h5>
                        <!-- Alerts -->
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (empty($categories)): ?>
                            <div class="text-center py-5">
                                <i class="fas fa-folder fa-3x text-muted mb-3"></i>
                                <h5>No categories found</h5>
                                <p class="text-muted">Create your first category to organize your blog posts.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Posts</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($categories as $category): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2" style="width: 20px; height: 20px; background-color: <?php echo $category['color']; ?>; border-radius: 3px;"></div>
                                                        <strong><?php echo htmlspecialchars($category['name']); ?></strong>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($category['description']); ?></td>
                                                <td>
                                                    <span class="badge bg-secondary"><?php echo $category['post_count']; ?></span>
                                                </td>
                                                <td>
                                                    <span class="badge <?php echo $category['status'] === 'Active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                        <?php echo $category['status']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-primary" onclick="editCategory(<?php echo htmlspecialchars(json_encode($category)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger" onclick="deleteCategory(<?php echo $category['id']; ?>, '<?php echo htmlspecialchars($category['name']); ?>', <?php echo $category['post_count']; ?>)">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="table-card">
                    <h6 class="mb-3" id="form-title"><i class="fas fa-plus"></i> Add New Category</h6>
                        <form method="POST" id="categoryForm">
                            <input type="hidden" name="action" value="create" id="form-action">
                            <input type="hidden" name="id" id="category-id">
                            
                            <div class="mb-3">
                                <label class="form-label">Name *</label>
                                <input type="text" name="name" id="category-name" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="category-description" class="form-control" rows="3"></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">Color</label>
                                <input type="color" name="color" id="category-color" class="form-control form-control-color" value="#007bff">
                            </div>
                            
                            <div class="mb-3" id="status-field" style="display: none;">
                                <label class="form-label">Status</label>
                                <select name="status" id="category-status" class="form-select">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" id="submit-btn">
                                    <i class="fas fa-save"></i> Create Category
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetForm()" id="cancel-btn" style="display: none;">
                                    <i class="fas fa-times"></i> Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete the category "<strong id="delete-category-name"></strong>"?</p>
                    <div id="delete-warning" class="alert alert-warning" style="display: none;">
                        This category has <strong id="post-count"></strong> post(s). Please reassign or delete the posts first.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="delete-category-id">
                        <button type="submit" class="btn btn-danger" id="confirm-delete-btn">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editCategory(category) {
            document.getElementById('form-title').innerHTML = '<i class="fas fa-edit"></i> Edit Category';
            document.getElementById('form-action').value = 'update';
            document.getElementById('category-id').value = category.id;
            document.getElementById('category-name').value = category.name;
            document.getElementById('category-description').value = category.description;
            document.getElementById('category-color').value = category.color;
            document.getElementById('category-status').value = category.status;
            document.getElementById('status-field').style.display = 'block';
            document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save"></i> Update Category';
            document.getElementById('cancel-btn').style.display = 'block';
        }

        function resetForm() {
            document.getElementById('form-title').innerHTML = '<i class="fas fa-plus"></i> Add New Category';
            document.getElementById('form-action').value = 'create';
            document.getElementById('categoryForm').reset();
            document.getElementById('category-color').value = '#007bff';
            document.getElementById('status-field').style.display = 'none';
            document.getElementById('submit-btn').innerHTML = '<i class="fas fa-save"></i> Create Category';
            document.getElementById('cancel-btn').style.display = 'none';
        }

        function deleteCategory(id, name, postCount) {
            document.getElementById('delete-category-name').textContent = name;
            document.getElementById('delete-category-id').value = id;
            document.getElementById('post-count').textContent = postCount;
            
            if (postCount > 0) {
                document.getElementById('delete-warning').style.display = 'block';
                document.getElementById('confirm-delete-btn').disabled = true;
                document.getElementById('confirm-delete-btn').textContent = 'Cannot Delete';
            } else {
                document.getElementById('delete-warning').style.display = 'none';
                document.getElementById('confirm-delete-btn').disabled = false;
                document.getElementById('confirm-delete-btn').textContent = 'Delete';
            }
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
