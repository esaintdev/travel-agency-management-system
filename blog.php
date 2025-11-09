<?php
require_once 'config.php';

// Get filter parameters
$category_filter = $_GET['category'] ?? '';
$tag_filter = $_GET['tag'] ?? '';
$search = $_GET['search'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 6;
$offset = ($page - 1) * $per_page;

// Build query conditions
$where_conditions = ["bp.status = 'Published'", "bp.visibility = 'Public'"];
$params = [];

if (!empty($category_filter)) {
    $where_conditions[] = "bc.slug = ?";
    $params[] = $category_filter;
}

if (!empty($tag_filter)) {
    $where_conditions[] = "EXISTS (SELECT 1 FROM blog_post_tags bpt 
                                  JOIN blog_tags bt ON bpt.tag_id = bt.id 
                                  WHERE bpt.post_id = bp.id AND bt.slug = ?)";
    $params[] = $tag_filter;
}

if (!empty($search)) {
    $where_conditions[] = "(bp.title LIKE ? OR bp.content LIKE ? OR bp.excerpt LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

try {
    // Get total count for pagination
    $count_sql = "SELECT COUNT(*) as total 
                  FROM blog_posts bp 
                  LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
                  $where_clause";
    $stmt = $db->prepare($count_sql);
    $stmt->execute($params);
    $total_posts = $stmt->fetch()['total'];
    $total_pages = ceil($total_posts / $per_page);

    // Get posts
    $sql = "SELECT bp.*, au.full_name as author_name, bc.name as category_name, bc.slug as category_slug, bc.color as category_color
            FROM blog_posts bp 
            LEFT JOIN admin_users au ON bp.author_id = au.id 
            LEFT JOIN blog_categories bc ON bp.category_id = bc.id 
            $where_clause
            ORDER BY bp.is_featured DESC, bp.published_at DESC 
            LIMIT $per_page OFFSET $offset";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $posts = $stmt->fetchAll();

    // Get categories for filter
    $stmt = $db->prepare("SELECT bc.*, COUNT(bp.id) as post_count 
                         FROM blog_categories bc 
                         LEFT JOIN blog_posts bp ON bc.id = bp.category_id AND bp.status = 'Published' AND bp.visibility = 'Public'
                         WHERE bc.status = 'Active' 
                         GROUP BY bc.id 
                         HAVING post_count > 0 
                         ORDER BY bc.name");
    $stmt->execute();
    $categories = $stmt->fetchAll();

    // Get popular tags
    $stmt = $db->prepare("SELECT bt.*, COUNT(bpt.post_id) as post_count 
                         FROM blog_tags bt 
                         JOIN blog_post_tags bpt ON bt.id = bpt.tag_id 
                         JOIN blog_posts bp ON bpt.post_id = bp.id 
                         WHERE bp.status = 'Published' AND bp.visibility = 'Public'
                         GROUP BY bt.id 
                         ORDER BY post_count DESC 
                         LIMIT 20");
    $stmt->execute();
    $popular_tags = $stmt->fetchAll();

    // Get recent posts for sidebar
    $stmt = $db->prepare("SELECT bp.title, bp.slug, bp.featured_image, bp.published_at 
                         FROM blog_posts bp 
                         WHERE bp.status = 'Published' AND bp.visibility = 'Public'
                         ORDER BY bp.published_at DESC 
                         LIMIT 5");
    $stmt->execute();
    $recent_posts = $stmt->fetchAll();

} catch (Exception $e) {
    error_log("Blog error: " . $e->getMessage());
    $posts = [];
    $categories = [];
    $popular_tags = [];
    $recent_posts = [];
    $total_posts = 0;
    $total_pages = 0;
}

// Get current category name for breadcrumb
$current_category_name = '';
if (!empty($category_filter)) {
    foreach ($categories as $cat) {
        if ($cat['slug'] === $category_filter) {
            $current_category_name = $cat['name'];
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <title>Blog - M25 Travel & Tour Agency</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="travel blog, visa information, destination guides, travel tips" name="keywords">
        <meta content="Travel tips, visa information, and destination guides from M25 Travel & Tour Agency" name="description">

        <!-- Google Web Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet"> 

        <!-- Icon Font Stylesheet -->
        <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

        <!-- Libraries Stylesheet -->
        <link href="lib/animate/animate.min.css" rel="stylesheet">
        <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

        <!-- Customized Bootstrap Stylesheet -->
        <link href="css/bootstrap.min.css" rel="stylesheet">

        <!-- Template Stylesheet -->
        <link href="css/style.css" rel="stylesheet">

        <!-- Google Translate -->
        <script type="text/javascript">
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: 'en,fr,es,de,it,pt,ar,zh,hi,ja,ko,ru,nl,sv,no,da',
                    layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                    autoDisplay: false
                }, 'google_translate_element');
            }
        </script>
        <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
        
        <!-- Blog Specific Styles -->
        <style>
        .blog-card {
            transition: transform 0.2s;
            height: 100%;
        }
        .blog-card:hover {
            transform: translateY(-5px);
        }
        .blog-image {
            height: 200px;
            object-fit: cover;
        }
        .category-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            color: white;
            border-radius: 0.25rem;
            text-decoration: none;
        }
        .category-badge:hover {
            color: white;
            opacity: 0.8;
        }
        .featured-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: linear-gradient(45deg, #ff6b6b, #feca57);
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: bold;
        }
        .tag-cloud a {
            display: inline-block;
            margin: 0.25rem;
            padding: 0.25rem 0.5rem;
            background: #f8f9fa;
            color: #6c757d;
            text-decoration: none;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .tag-cloud a:hover {
            background: #007bff;
            color: white;
        }
        .sidebar-widget {
            background: white;
            border-radius: 0.5rem;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }
    </style>
</head>

<body>

    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-secondary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Hidden Google Translate Element -->
    <div id="google_translate_element" style="display: none;"></div>

    <!-- Topbar Start -->
    <div class="container-fluid bg-primary px-5 d-none d-lg-block">
        <div class="row gx-0 align-items-center">
            <div class="col-lg-5 text-center text-lg-start mb-lg-0">
                <div class="d-flex">
                    <a href="mailto:info@m25travelagency.com" class="text-muted me-4"><i class="fas fa-envelope text-secondary me-2"></i>info@m25travelagency.com</a>
                    <a href="tel:+233592605752" class="text-muted me-0"><i class="fas fa-phone-alt text-secondary me-2"></i>+233 59 260 5752</a>
                </div>
            </div>
            <div class="col-lg-3 row-cols-1 text-center mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2" href="https://x.com/M25And42551"><i class="fab fa-twitter fw-normal text-secondary"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2" href="https://www.facebook.com/profile.php?id=61560365438024"><i class="fab fa-facebook-f fw-normal text-secondary"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2" href="https://www.instagram.com/mtravelandtouragency/"><i class="fab fa-instagram-in fw-normal text-secondary"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2" href="https://www.tiktok.com/@m25travelandtour"><i class="fab fa-tiktok fw-normal text-secondary"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-square rounded-circle" href="https://www.youtube.com/@M25TravelandTourAgency"><i class="fab fa-youtube fw-normal text-secondary"></i></a>
                </div>
            </div>
            
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <div class="dropdown me-3">
                        <button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-globe me-1"></i> Language
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item" href="#" onclick="translatePage('en')"><img src="https://flagcdn.com/16x12/us.png" class="me-2" alt="English">English</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('fr')"><img src="https://flagcdn.com/16x12/fr.png" class="me-2" alt="French">French</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('es')"><img src="https://flagcdn.com/16x12/es.png" class="me-2" alt="Spanish">Spanish</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('de')"><img src="https://flagcdn.com/16x12/de.png" class="me-2" alt="German">German</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('it')"><img src="https://flagcdn.com/16x12/it.png" class="me-2" alt="Italian">Italian</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('pt')"><img src="https://flagcdn.com/16x12/pt.png" class="me-2" alt="Portuguese">Portuguese</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('ar')"><img src="https://flagcdn.com/16x12/sa.png" class="me-2" alt="Arabic">Arabic</a></li>
                            <li><a class="dropdown-item" href="#" onclick="translatePage('zh')"><img src="https://flagcdn.com/16x12/cn.png" class="me-2" alt="Chinese">Chinese</a></li>
                        </ul>
                    </div>
                    <a href="#" class="text-muted me-2"> Help</a><small> / </small>
                    <a href="#" class="text-muted mx-2"> Support</a><small> / </small>
                    <a href="contact" class="text-muted ms-2"> Contact</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar & Hero Start -->
    <div class="container-fluid nav-bar p-0">
        <nav class="navbar navbar-expand-lg navbar-light bg-white px-4 px-lg-5 py-3 py-lg-0">
            <a href="/" class="navbar-brand p-0">
                <h4 class="text-secondary m-0"><img src="img/brand-logo.png" class="img-fluid" alt="">M25 Travel and Tours Agency</h4>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="/" class="nav-item nav-link">Home</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">About</a>
                        <div class="dropdown-menu m-0">
                            <a href="about" class="dropdown-item">About</a>
                            <a href="feature" class="dropdown-item">Features</a>
                        </div>
                    </div>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">Services</a>
                        <div class="dropdown-menu m-0">
                            <a href="service" class="dropdown-item">Service</a>
                            <a href="countries" class="dropdown-item">Countries</a>
                            <a href="testimonial" class="dropdown-item">Testimonial</a>
                            <a href="faq" class="dropdown-item">FAQ</a>
                            <a href="privacy-policy" class="dropdown-item">Privacy Policy</a>
                            <a href="terms-conditions" class="dropdown-item">Terms & Conditions</a>
                            <a href="legal-disclaimer" class="dropdown-item">Legal Disclaimer</a>
                            <a href="refund-policy" class="dropdown-item">Refund Policy</a>
                            <a href="client-service-agreement" class="dropdown-item">Client Service Agreement</a>
                        </div> 
                    </div>
                    <a href="blog" class="nav-item nav-link active">Blog</a>
                    <a href="contact" class="nav-item nav-link">Contact</a>
                </div>
                <button class="btn btn-primary btn-md-square border-secondary mb-3 mb-md-3 mb-lg-0 me-3" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search"></i></button>
                <button class="btn btn-secondary btn-md-square border-secondary mb-3 mb-md-3 mb-lg-0 me-3" onclick="shareWebsite()" title="Share Website"><i class="fas fa-share-alt"></i></button>
                
                <a href="client-registration" class="btn btn-primary border-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0">Get A Form</a>
            </div>
        </nav>
    </div>
    <!-- Navbar & Hero End -->

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold mb-3">Travel Blog</h1>
                    <p class="lead mb-4">Discover amazing destinations, get travel tips, and stay updated with the latest visa information.</p>
                    
                    <!-- Search Form -->
                    <form method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control me-2" placeholder="Search articles..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-light">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
                <div class="col-lg-4 text-center">
                    <i class="fas fa-blog fa-5x opacity-50"></i>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Breadcrumb -->
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item"><a href="blog.php">Blog</a></li>
                        <?php if ($current_category_name): ?>
                            <li class="breadcrumb-item active"><?php echo htmlspecialchars($current_category_name); ?></li>
                        <?php endif; ?>
                    </ol>
                </nav>

                <!-- Active Filters -->
                <?php if (!empty($category_filter) || !empty($tag_filter) || !empty($search)): ?>
                    <div class="alert alert-info d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <strong>Active Filters:</strong>
                            <?php if (!empty($search)): ?>
                                <span class="badge bg-primary me-1">Search: "<?php echo htmlspecialchars($search); ?>"</span>
                            <?php endif; ?>
                            <?php if (!empty($current_category_name)): ?>
                                <span class="badge bg-success me-1">Category: <?php echo htmlspecialchars($current_category_name); ?></span>
                            <?php endif; ?>
                            <?php if (!empty($tag_filter)): ?>
                                <span class="badge bg-warning me-1">Tag: <?php echo htmlspecialchars($tag_filter); ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="blog.php" class="btn btn-sm btn-outline-secondary">Clear All</a>
                    </div>
                <?php endif; ?>

                <!-- Posts Grid -->
                <?php if (empty($posts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h4>No posts found</h4>
                        <p class="text-muted">Try adjusting your search criteria or browse all posts.</p>
                        <a href="blog.php" class="btn btn-primary">View All Posts</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($posts as $post): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card blog-card h-100">
                                    <?php if ($post['featured_image']): ?>
                                    <div class="position-relative">
                                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" alt="<?php echo htmlspecialchars($post['title']); ?>" class="card-img-top blog-image">
                                        
                                        <?php if ($post['is_featured']): ?>
                                            <div class="featured-badge">
                                                <i class="fas fa-star"></i> Featured
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="card-body d-flex flex-column">
                                        <div class="mb-2">
                                            <?php if ($post['category_name']): ?>
                                                <a href="blog.php?category=<?php echo $post['category_slug']; ?>" class="category-badge" style="background-color: <?php echo $post['category_color']; ?>">
                                                    <?php echo htmlspecialchars($post['category_name']); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <h5 class="card-title">
                                            <a href="blog-post.php?slug=<?php echo $post['slug']; ?>" class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars($post['title']); ?>
                                            </a>
                                        </h5>
                                        
                                        <?php if ($post['excerpt']): ?>
                                            <p class="card-text text-muted flex-grow-1">
                                                <?php echo htmlspecialchars(substr($post['excerpt'], 0, 120)) . '...'; ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="mt-auto">
                                            <div class="d-flex justify-content-between align-items-center text-muted small">
                                                <div>
                                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($post['author_name']); ?>
                                                </div>
                                                <div>
                                                    <i class="fas fa-calendar"></i> <?php echo formatDate($post['published_at'], 'M j, Y'); ?>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center text-muted small mt-1">
                                                <div>
                                                    <i class="fas fa-clock"></i> <?php echo $post['reading_time']; ?> min read
                                                </div>
                                                <div>
                                                    <i class="fas fa-eye"></i> <?php echo number_format($post['views']); ?> views
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Blog pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&category=<?php echo $category_filter; ?>&tag=<?php echo $tag_filter; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&category=<?php echo $category_filter; ?>&tag=<?php echo $tag_filter; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&category=<?php echo $category_filter; ?>&tag=<?php echo $tag_filter; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Categories Widget -->
                <?php if (!empty($categories)): ?>
                    <div class="sidebar-widget">
                        <h5 class="mb-3"><i class="fas fa-folder"></i> Categories</h5>
                        <div class="list-group list-group-flush">
                            <?php foreach ($categories as $category): ?>
                                <a href="blog.php?category=<?php echo $category['slug']; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div class="me-2" style="width: 12px; height: 12px; background-color: <?php echo $category['color']; ?>; border-radius: 2px;"></div>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </div>
                                    <span class="badge bg-secondary"><?php echo $category['post_count']; ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Recent Posts Widget -->
                <?php if (!empty($recent_posts)): ?>
                    <div class="sidebar-widget">
                        <h5 class="mb-3"><i class="fas fa-clock"></i> Recent Posts</h5>
                        <?php foreach ($recent_posts as $recent): ?>
                            <div class="d-flex mb-3">
                                <?php if ($recent['featured_image']): ?>
                                    <img src="<?php echo htmlspecialchars($recent['featured_image']); ?>" alt="" class="me-3" style="width: 60px; height: 60px; object-fit: cover; border-radius: 0.25rem;">
                                <?php endif; ?>
                                <div class="<?php echo $recent['featured_image'] ? 'flex-grow-1' : 'w-100'; ?>">
                                    <h6 class="mb-1">
                                        <a href="blog-post.php?slug=<?php echo $recent['slug']; ?>" class="text-decoration-none">
                                            <?php echo htmlspecialchars(substr($recent['title'], 0, 50)) . '...'; ?>
                                        </a>
                                    </h6>
                                    <small class="text-muted">
                                        <i class="fas fa-calendar"></i> <?php echo formatDate($recent['published_at'], 'M j, Y'); ?>
                                    </small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Tags Widget -->
                <?php if (!empty($popular_tags)): ?>
                    <div class="sidebar-widget">
                        <h5 class="mb-3"><i class="fas fa-tags"></i> Popular Tags</h5>
                        <div class="tag-cloud">
                            <?php foreach ($popular_tags as $tag): ?>
                                <a href="blog.php?tag=<?php echo $tag['slug']; ?>">
                                    <?php echo htmlspecialchars($tag['name']); ?>
                                    <small>(<?php echo $tag['post_count']; ?>)</small>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Newsletter Widget -->
                <div class="sidebar-widget bg-primary text-white">
                    <h5 class="mb-3"><i class="fas fa-envelope"></i> Stay Updated</h5>
                    <p>Subscribe to our newsletter for the latest travel tips and updates.</p>
                    <form>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Your email address" required>
                        </div>
                        <button type="submit" class="btn btn-light w-100">Subscribe</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Modal -->
    <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="searchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="searchModalLabel">Search Blog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="GET" action="blog.php">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control form-control-lg" placeholder="Search for articles..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <!-- Translation Functions -->
    <script>
        function translatePage(lang) {
            var translateElement = document.querySelector('.goog-te-combo');
            if (translateElement) {
                translateElement.value = lang;
                translateElement.dispatchEvent(new Event('change'));
            }
        }

        function shareWebsite() {
            if (navigator.share) {
                navigator.share({
                    title: 'M25 Travel & Tour Agency - Blog',
                    text: 'Check out our travel blog for tips and destination guides!',
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                var url = encodeURIComponent(window.location.href);
                var text = encodeURIComponent('Check out M25 Travel & Tour Agency Blog!');
                window.open('https://twitter.com/intent/tweet?url=' + url + '&text=' + text, '_blank');
            }
        }
    </script>
</body>
</html>
