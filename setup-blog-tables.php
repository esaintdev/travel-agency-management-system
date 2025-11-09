<?php
require_once 'config.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['admin_id']) || $_SESSION['admin_role'] !== 'Super Admin') {
    die('Access denied. Only Super Admins can run this setup.');
}

try {
    // Create blog_categories table
    $sql = "CREATE TABLE IF NOT EXISTS blog_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        color VARCHAR(7) DEFAULT '#007bff',
        status ENUM('Active', 'Inactive') DEFAULT 'Active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "✓ Blog categories table created successfully.<br>";

    // Create blog_tags table
    $sql = "CREATE TABLE IF NOT EXISTS blog_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        slug VARCHAR(50) NOT NULL UNIQUE,
        color VARCHAR(7) DEFAULT '#6c757d',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $db->exec($sql);
    echo "✓ Blog tags table created successfully.<br>";

    // Create blog_posts table
    $sql = "CREATE TABLE IF NOT EXISTS blog_posts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) NOT NULL UNIQUE,
        excerpt TEXT,
        content LONGTEXT NOT NULL,
        featured_image VARCHAR(255),
        category_id INT,
        author_id INT NOT NULL,
        status ENUM('Draft', 'Published', 'Scheduled', 'Archived') DEFAULT 'Draft',
        visibility ENUM('Public', 'Private', 'Password Protected') DEFAULT 'Public',
        password VARCHAR(255) NULL,
        published_at TIMESTAMP NULL,
        views INT DEFAULT 0,
        likes INT DEFAULT 0,
        allow_comments BOOLEAN DEFAULT TRUE,
        is_featured BOOLEAN DEFAULT FALSE,
        reading_time INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE SET NULL,
        FOREIGN KEY (author_id) REFERENCES admin_users(id) ON DELETE CASCADE,
        INDEX idx_status (status),
        INDEX idx_published_at (published_at),
        INDEX idx_category (category_id),
        INDEX idx_author (author_id),
        INDEX idx_slug (slug)
    )";
    $db->exec($sql);
    echo "✓ Blog posts table created successfully.<br>";

    // Create blog_post_tags table (many-to-many relationship)
    $sql = "CREATE TABLE IF NOT EXISTS blog_post_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        tag_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE,
        UNIQUE KEY unique_post_tag (post_id, tag_id)
    )";
    $db->exec($sql);
    echo "✓ Blog post tags table created successfully.<br>";

    // Create blog_seo_data table
    $sql = "CREATE TABLE IF NOT EXISTS blog_seo_data (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL UNIQUE,
        meta_title VARCHAR(60),
        meta_description VARCHAR(160),
        meta_keywords TEXT,
        og_title VARCHAR(60),
        og_description VARCHAR(160),
        og_image VARCHAR(255),
        twitter_title VARCHAR(60),
        twitter_description VARCHAR(160),
        twitter_image VARCHAR(255),
        canonical_url VARCHAR(255),
        robots_index BOOLEAN DEFAULT TRUE,
        robots_follow BOOLEAN DEFAULT TRUE,
        schema_markup JSON,
        focus_keyword VARCHAR(100),
        seo_score INT DEFAULT 0,
        readability_score INT DEFAULT 0,
        ai_suggestions JSON,
        last_analyzed TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE
    )";
    $db->exec($sql);
    echo "✓ Blog SEO data table created successfully.<br>";

    // Create blog_comments table
    $sql = "CREATE TABLE IF NOT EXISTS blog_comments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        parent_id INT NULL,
        author_name VARCHAR(100) NOT NULL,
        author_email VARCHAR(255) NOT NULL,
        author_website VARCHAR(255) NULL,
        content TEXT NOT NULL,
        status ENUM('Pending', 'Approved', 'Spam', 'Trash') DEFAULT 'Pending',
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
        FOREIGN KEY (parent_id) REFERENCES blog_comments(id) ON DELETE CASCADE,
        INDEX idx_post_status (post_id, status),
        INDEX idx_parent (parent_id)
    )";
    $db->exec($sql);
    echo "✓ Blog comments table created successfully.<br>";

    // Create blog_analytics table
    $sql = "CREATE TABLE IF NOT EXISTS blog_analytics (
        id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT NOT NULL,
        date DATE NOT NULL,
        views INT DEFAULT 0,
        unique_views INT DEFAULT 0,
        bounce_rate DECIMAL(5,2) DEFAULT 0,
        avg_time_on_page INT DEFAULT 0,
        referrer_domain VARCHAR(255),
        search_keywords TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES blog_posts(id) ON DELETE CASCADE,
        UNIQUE KEY unique_post_date (post_id, date),
        INDEX idx_date (date),
        INDEX idx_post_date (post_id, date)
    )";
    $db->exec($sql);
    echo "✓ Blog analytics table created successfully.<br>";

    // Insert default categories
    $default_categories = [
        ['Travel Tips', 'travel-tips', 'Helpful tips and advice for travelers', '#28a745'],
        ['Visa Information', 'visa-information', 'Latest visa requirements and updates', '#007bff'],
        ['Destinations', 'destinations', 'Featured travel destinations and guides', '#fd7e14'],
        ['Company News', 'company-news', 'Updates and news from M25 Travel & Tour', '#6f42c1'],
        ['Travel Stories', 'travel-stories', 'Real travel experiences and stories', '#e83e8c']
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO blog_categories (name, slug, description, color) VALUES (?, ?, ?, ?)");
    foreach ($default_categories as $category) {
        $stmt->execute($category);
    }
    echo "✓ Default blog categories inserted successfully.<br>";

    // Insert default tags
    $default_tags = [
        ['Travel', 'travel'],
        ['Visa', 'visa'],
        ['Tourism', 'tourism'],
        ['Adventure', 'adventure'],
        ['Business Travel', 'business-travel'],
        ['Family Travel', 'family-travel'],
        ['Budget Travel', 'budget-travel'],
        ['Luxury Travel', 'luxury-travel'],
        ['Solo Travel', 'solo-travel'],
        ['Group Travel', 'group-travel']
    ];

    $stmt = $db->prepare("INSERT IGNORE INTO blog_tags (name, slug) VALUES (?, ?)");
    foreach ($default_tags as $tag) {
        $stmt->execute($tag);
    }
    echo "✓ Default blog tags inserted successfully.<br>";

    echo "<br><strong>Blog system database setup completed successfully!</strong><br>";
    echo "<a href='admin-blog.php'>Go to Blog Management</a>";

} catch (PDOException $e) {
    echo "Error creating blog tables: " . $e->getMessage();
}
?>
