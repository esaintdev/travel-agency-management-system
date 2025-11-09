<?php
require_once 'config.php';
requireLogin();
checkSessionTimeout();

// Handle FAQ management
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_faq':
                $faq_content = $_POST['faq_content'];
                // Save to database or file
                file_put_contents('includes/faq-content.php', "<?php\n\$faq_content = " . var_export($faq_content, true) . ";\n?>");
                $_SESSION['success_message'] = "FAQ content updated successfully!";
                break;
        }
    }
}

// Load existing FAQ content
$faq_content = '';
if (file_exists('includes/faq-content.php')) {
    include 'includes/faq-content.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ Management - M25 Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .top-navbar {
            background: linear-gradient(135deg, #13357B 0%, #1e4a8c 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .faq-editor {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .preview-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-top: 30px;
        }
        .faq-section {
            margin-bottom: 40px;
        }
        .faq-section h3 {
            color: #13357B;
            border-bottom: 3px solid #13357B;
            padding-bottom: 10px;
            margin-bottom: 25px;
        }
        .faq-item {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .faq-question {
            font-weight: bold;
            color: #13357B;
            margin-bottom: 10px;
        }
        .faq-answer {
            color: #666;
            line-height: 1.6;
        }
        .cta-section {
            background: linear-gradient(135deg, #13357B 0%, #1e4a8c 100%);
            color: white;
            border-radius: 15px;
            padding: 40px;
            text-align: center;
            margin-top: 40px;
        }
    </style>
</head>

<body>
    <?php include 'includes/admin-sidebar.php'; ?>
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar">
                    <div class="container-fluid">
                        <span class="navbar-brand">FAQ Management</span>
                        <div class="navbar-nav ms-auto">
                            <span class="nav-link text-white">
                                <i class="fas fa-user me-2"></i>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?>
                            </span>
                        </div>
                    </div>
                </nav>
                
                <!-- FAQ Management Content -->
                <div class="container-fluid p-4">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show">
                            <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="faq-editor">
                        <h4 class="mb-4"><i class="fas fa-question-circle me-2"></i>FAQ Content Management</h4>
                        
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_faq">
                            
                            <div class="mb-4">
                                <label class="form-label">FAQ Content (HTML Format)</label>
                                <textarea name="faq_content" class="form-control" rows="20" placeholder="Enter FAQ content in HTML format..."><?php echo htmlspecialchars($faq_content); ?></textarea>
                                <small class="text-muted">You can use HTML tags for formatting. The content will be displayed on the public FAQ page.</small>
                            </div>
                            
                            <div class="d-flex gap-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update FAQ Content
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="loadDefaultContent()">
                                    <i class="fas fa-refresh me-2"></i>Load Default Content
                                </button>
                                <a href="faq.php" target="_blank" class="btn btn-success">
                                    <i class="fas fa-eye me-2"></i>Preview Public Page
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Preview Section -->
                    <div class="preview-section">
                        <h4 class="mb-4"><i class="fas fa-eye me-2"></i>Content Preview</h4>
                        <div id="faqPreview">
                            <?php echo $faq_content ?: '<p class="text-muted">No FAQ content available. Add content above to see preview.</p>'; ?>
                        </div>
                    </div>
                </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function loadDefaultContent() {
            const defaultContent = `<div class="faq-container">
    <div class="intro-section text-center mb-5">
        <h1 class="display-4 text-primary mb-4">Frequently Asked Questions</h1>
        <h2 class="h4 mb-4">M25 Travel & Tour Global Services</h2>
        <p class="lead">Below are the most common international travel, visa processing and client support questions asked by travelers worldwide. M25 Travel & Tour now operates globally assisting clients from any country with visa advisory, document preparation, travel support and consultation services.</p>
    </div>

    <div class="faq-section">
        <h3>General Company Questions</h3>
        
        <div class="faq-item">
            <div class="faq-question">1. What does M25 Travel & Tour do?</div>
            <div class="faq-answer">We assist travelers worldwide with visa documentation guidance, consultation, travel planning, itinerary, accommodation support and application coaching for tourism, work, study, business and family visas.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">2. Do you work globally or only in Ghana?</div>
            <div class="faq-answer">We are now global. Any client from any country can use our services.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">3. Can you guarantee visa approval?</div>
            <div class="faq-answer">No. No agency can guarantee visa approval. Final decision is made only by the Embassy.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">4. Which countries do you support?</div>
            <div class="faq-answer">We support visas for Canada, USA, UK, Europe Schengen, Australia, Dubai UAE, Turkey, Asia and more.</div>
        </div>

        <div class="faq-item">
            <div class="faq-question">5. Do you work with all visa types?</div>
            <div class="faq-answer">Yes — Tourist, Business, Study, Family Visit, Medical, Work Permit, Religious and long stay categories.</div>
        </div>
    </div>

    <div class="cta-section">
        <h3>Ready to Begin Your Visa Process?</h3>
        <p class="mb-4">Click to Start — Get Consultation + Profile Assessment Today.</p>
        <a href="contact.php" class="btn btn-light btn-lg">Start Your Application</a>
    </div>
</div>`;
            
            document.querySelector('textarea[name="faq_content"]').value = defaultContent;
            document.getElementById('faqPreview').innerHTML = defaultContent;
        }

        // Live preview
        document.querySelector('textarea[name="faq_content"]').addEventListener('input', function() {
            document.getElementById('faqPreview').innerHTML = this.value || '<p class="text-muted">No content to preview.</p>';
        });
    </script>
    <?php include 'includes/admin-sidebar-close.php'; ?>
</body>
</html>
