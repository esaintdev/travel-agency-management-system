<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Visa Details - M25 Travel & Tour Agency</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="" name="keywords">
    <meta content="" name="description">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet"> 

    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link rel="stylesheet" href="lib/animate/animate.min.css"/>
    <link href="lib/lightbox/css/lightbox.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        .visa-section {
            padding: 60px 0;
            margin-bottom: 40px;
        }
        .visa-content {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-bottom: 40px;
        }
        .visa-header {
            background: linear-gradient(135deg, #13357B, #1e4a8c);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .visa-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .visa-body {
            padding: 40px;
        }
        .visa-description {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #555;
            margin-bottom: 30px;
            text-align: justify;
        }
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 25px;
            border-left: 4px solid #13357B;
        }
        .info-card h6 {
            color: #13357B;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }
        .requirements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .process-steps {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        .process-step {
            background: #fff;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            text-align: center;
        }
        .process-step:hover {
            border-color: #13357B;
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .step-number {
            width: 40px;
            height: 40px;
            background: #13357B;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin: 0 auto 15px;
        }
        .quick-info {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 10px;
            padding: 25px;
            margin: 30px 0;
        }
        .quick-info-item {
            text-align: center;
            padding: 15px;
        }
        .quick-info-item .value {
            font-size: 1.2rem;
            font-weight: 600;
            color: #13357B;
            display: block;
        }
        .quick-info-item .label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .cta-section {
            background: linear-gradient(135deg, #13357B, #1e4a8c);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin-top: 40px;
        }
        .visa-image-container {
            margin-bottom: 30px;
            overflow: hidden;
            border-radius: 15px 15px 0 0;
        }
        .visa-image-container img {
            width: 100%;
            height: 250px;
            object-fit: cover;
            transition: transform 0.3s ease;
            border-radius: 0;
        }
        .visa-image-container img:hover {
            transform: scale(1.02);
        }
        .placeholder-image {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 0;
        }
        .smooth-scroll {
            scroll-behavior: smooth;
        }
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: #13357B;
            color: white;
            border: none;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: none;
            z-index: 1000;
        }
    </style>
</head>

<body class="smooth-scroll">

    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Topbar Start -->
    <div class="container-fluid topbar bg-light px-5 d-none d-lg-block">
        <div class="row gx-0 align-items-center">
            <div class="col-lg-8 text-center text-lg-start mb-2 mb-lg-0">
                <div class="d-flex flex-wrap">
                    <a href="#" class="text-muted small me-4"><i class="fas fa-map-marker-alt text-primary me-2"></i>Find A Location</a>
                    <a href="tel:+01234567890" class="text-muted small me-4"><i class="fas fa-phone-alt text-primary me-2"></i>+233 59 260 5752</a>
                    <a href="mailto:example@gmail.com" class="text-muted small me-0"><i class="fas fa-envelope text-primary me-2"></i>info@m25travelagency.com</a>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-flex align-items-center justify-content-end">
                    <a href="#" class="btn btn-sm btn-outline-primary btn-sm-square rounded-circle me-2"><i class="fab fa-twitter fw-normal"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-primary btn-sm-square rounded-circle me-2"><i class="fab fa-facebook-f fw-normal"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-primary btn-sm-square rounded-circle me-2"><i class="fab fa-linkedin-in fw-normal"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-primary btn-sm-square rounded-circle me-2"><i class="fab fa-instagram fw-normal"></i></a>
                    <a href="#" class="btn btn-sm btn-outline-primary btn-sm-square rounded-circle"><i class="fab fa-youtube fw-normal"></i></a>
                </div>
            </div>
        </div>
    </div>
    <!-- Topbar End -->

    <!-- Navbar & Hero Start -->
    <div class="container-fluid position-relative p-0">
        <nav class="navbar navbar-expand-lg navbar-light px-4 px-lg-5 py-3 py-lg-0">
            <a href="index.php" class="navbar-brand p-0">
                <h1 class="text-primary"><i class="fas fa-search-dollar me-3"></i>M25 Travel</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="index.php" class="nav-item nav-link">Home</a>
                    <a href="about.html" class="nav-item nav-link">About</a>
                    <a href="visa-details.php" class="nav-item nav-link active">Visa Details</a>
                    <a href="service.html" class="nav-item nav-link">Services</a>
                    <a href="contact.html" class="nav-item nav-link">Contact</a>
                </div>
                <a href="#" class="btn btn-primary rounded-pill py-2 px-4 my-3 my-lg-0 flex-shrink-0">Get Started</a>
            </div>
        </nav>

        <!-- Header Start -->
        <div class="container-fluid bg-breadcrumb">
            <div class="container text-center py-5" style="max-width: 900px;">
                <h3 class="text-white display-3 mb-4">Visa Details</h3>
                <p class="fs-5 text-white mb-4">Comprehensive information about all our visa services</p>
                <ol class="breadcrumb justify-content-center mb-0">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item active text-white">Visa Details</li>
                </ol>
            </div>
        </div>
        <!-- Header End -->
    </div>
    <!-- Navbar & Hero End -->

    <!-- Quick Navigation -->
    <div class="container-fluid py-4 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h5 class="text-center mb-3">Quick Navigation</h5>
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <a href="#job-visa" class="btn btn-outline-primary btn-sm">Job Visa</a>
                        <a href="#business-visa" class="btn btn-outline-primary btn-sm">Business Visa</a>
                        <a href="#diplomatic-visa" class="btn btn-outline-primary btn-sm">Diplomatic Visa</a>
                        <a href="#student-visa" class="btn btn-outline-primary btn-sm">Student Visa</a>
                        <a href="#residence-visa" class="btn btn-outline-primary btn-sm">Residence Visa</a>
                        <a href="#tourist-visa" class="btn btn-outline-primary btn-sm">Tourist Visa</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // Database connection
    require_once 'config.php';
    
    // Fetch visa content from database
    $visas = [];
    try {
        $stmt = $db->prepare("SELECT * FROM visa_content WHERE status = 'active' ORDER BY id");
        $stmt->execute();
        while ($row = $stmt->fetch()) {
            $visas[$row['visa_type']] = $row;
        }
    } catch (PDOException $e) {
        // If table doesn't exist, use default content
        $visas = [
            'job-visa' => [
                'title' => 'Job Visa',
                'description' => 'Job Visa is designed for individuals who wish to work or engage in employment activities in another country. It allows you to secure a temporary work permit and travel documents to begin your professional journey abroad.',
                'requirements' => 'Valid passport, Job offer letter, Educational certificates, Medical examination, Police clearance certificate, Proof of financial support',
                'process' => 'Submit application, Document verification, Interview scheduling, Medical examination, Visa approval, Travel preparation',
                'processing_time' => '2-4 weeks',
                'validity' => '1-3 years (renewable)',
                'fees' => '$200 - $500'
            ],
            'business-visa' => [
                'title' => 'Business Visa',
                'description' => 'Business Visa is designed for entrepreneurs, company representatives, and professionals who wish to travel abroad for business-related activities such as meetings, conferences, or partnerships.',
                'requirements' => 'Valid passport, Business invitation letter, Company registration documents, Financial statements, Travel itinerary, Hotel bookings',
                'process' => 'Application submission, Document review, Business verification, Interview (if required), Visa issuance, Travel clearance',
                'processing_time' => '1-2 weeks',
                'validity' => '6 months - 1 year',
                'fees' => '$150 - $300'
            ],
            'diplomatic-visa' => [
                'title' => 'Diplomatic Visa',
                'description' => 'Diplomatic Visa is designed for diplomats, government officials, and representatives of international organizations who require travel documents to conduct official business abroad.',
                'requirements' => 'Diplomatic passport, Official letter from government, Mission assignment documents, Diplomatic note, Identity verification, Security clearance',
                'process' => 'Official application, Government verification, Diplomatic clearance, Security check, Visa approval, Official travel authorization',
                'processing_time' => '3-7 days',
                'validity' => 'Duration of assignment',
                'fees' => 'Usually waived'
            ],
            'student-visa' => [
                'title' => 'Student Visa',
                'description' => 'Student Visa is designed for individuals who wish to study or pursue higher education in another country. It allows you to secure a temporary study permit and travel documents to begin your academic journey abroad.',
                'requirements' => 'Valid passport, Admission letter from institution, Academic transcripts, Financial proof, Medical examination, English proficiency test',
                'process' => 'Application submission, Document verification, Academic review, Financial assessment, Medical check, Visa approval',
                'processing_time' => '4-8 weeks',
                'validity' => 'Duration of study program',
                'fees' => '$300 - $600'
            ],
            'residence-visa' => [
                'title' => 'Residence Visa',
                'description' => 'Residence Visa is designed for individuals who wish to live permanently in another country. It allows you to secure a permanent residence permit and travel documents to begin your life abroad.',
                'requirements' => 'Valid passport, Sponsorship documents, Financial proof, Medical examination, Police clearance, Integration test, Housing proof',
                'process' => 'Initial application, Document review, Background check, Interview process, Medical examination, Final approval',
                'processing_time' => '6-12 months',
                'validity' => 'Permanent (renewable)',
                'fees' => '$500 - $1500'
            ],
            'tourist-visa' => [
                'title' => 'Tourist Visa',
                'description' => 'Tourist Visa is designed for individuals who wish to visit another country for leisure, tourism, or vacation. It allows you to secure a temporary travel permit and travel documents to begin your vacation abroad.',
                'requirements' => 'Valid passport, Travel itinerary, Hotel bookings, Financial proof, Return ticket, Travel insurance, Passport photos',
                'process' => 'Online application, Document upload, Payment processing, Review and verification, Visa approval, Travel authorization',
                'processing_time' => '3-10 days',
                'validity' => '30-90 days',
                'fees' => '$50 - $200'
            ]
        ];
    }
    ?>

    <!-- Visa Details Content -->
    <div class="container-fluid py-5">
        <div class="container">
            <?php foreach ($visas as $type => $visa): ?>
            <section id="<?php echo $type; ?>" class="visa-section">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-12">
                            <article class="visa-content">
                                <!-- Visa Header -->
                                <header class="visa-header">
                                    <div class="visa-icon">
                                        <i class="fas fa-passport text-white fa-2x"></i>
                                    </div>
                                    <h1 class="display-5 mb-3 text-white"><?php echo htmlspecialchars($visa['title']); ?></h1>
                                    <p class="lead mb-0">Complete Guide & Application Process</p>
                                </header>

                                <!-- Image Section -->
                                <div class="visa-image-container">
                                    <?php 
                                    $image_exists = false;
                                    $image_src = '';
                                    
                                    if (!empty($visa['image_path'])) {
                                        // Debug information (remove in production)
                                        $debug_mode = isset($_GET['debug']) && $_GET['debug'] == '1';
                                        
                                        // Check if it's a relative path and convert to absolute for file_exists check
                                        $absolute_path = $_SERVER['DOCUMENT_ROOT'] . '/sension/' . $visa['image_path'];
                                        
                                        if ($debug_mode) {
                                            echo "<!-- DEBUG: Image path: " . htmlspecialchars($visa['image_path']) . " -->";
                                            echo "<!-- DEBUG: Absolute path: " . htmlspecialchars($absolute_path) . " -->";
                                            echo "<!-- DEBUG: File exists (relative): " . (file_exists($visa['image_path']) ? 'YES' : 'NO') . " -->";
                                            echo "<!-- DEBUG: File exists (absolute): " . (file_exists($absolute_path) ? 'YES' : 'NO') . " -->";
                                        }
                                        
                                        if (file_exists($absolute_path)) {
                                            $image_exists = true;
                                            $image_src = $visa['image_path'];
                                        } elseif (file_exists($visa['image_path'])) {
                                            $image_exists = true;
                                            $image_src = $visa['image_path'];
                                        }
                                    }
                                    ?>
                                    
                                    <?php if ($image_exists): ?>
                                        <img src="<?php echo htmlspecialchars($image_src); ?>" alt="<?php echo htmlspecialchars($visa['title']); ?>">
                                    <?php else: ?>
                                        <div class="placeholder-image d-flex align-items-center justify-content-center">
                                            <i class="fas fa-passport text-muted fa-4x"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Visa Body -->
                                <div class="visa-body">
                                    <!-- Description -->
                                    <div class="visa-description">
                                        <?php echo nl2br(htmlspecialchars($visa['description'])); ?>
                                    </div>

                                    <!-- Requirements & Process Grid -->
                                    <div class="requirements-grid">
                                        <!-- Requirements Card -->
                                        <div class="info-card">
                                            <h6><i class="fas fa-list-check me-2"></i>Required Documents</h6>
                                            <ul class="list-unstyled mb-0">
                                                <?php 
                                                $requirements = explode(',', $visa['requirements']);
                                                foreach ($requirements as $req): 
                                                ?>
                                                <li class="mb-2">
                                                    <i class="fas fa-check text-success me-2"></i>
                                                    <?php echo trim(htmlspecialchars($req)); ?>
                                                </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </div>

                                    </div>


                                    <!-- Call to Action -->
                                    <div class="cta-section">
                                        <h4 class="mb-3 text-white">Ready to Start Your Application?</h4>
                                        <p class="mb-4">Join thousands of satisfied clients who have successfully obtained their visas through M25 Travel & Tour Agency.</p>
                                        <div class="d-flex flex-wrap justify-content-center gap-3">
                                            <a href="client-registration.php" class="btn btn-light btn-lg rounded-pill px-5">
                                                <i class="fas fa-paper-plane me-2"></i>Apply Now
                                            </a>
                                            <a href="contact.php" class="btn btn-outline-light btn-lg rounded-pill px-5">
                                                <i class="fas fa-phone me-2"></i>Get Consultation
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </section>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button class="back-to-top" onclick="scrollToTop()">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Footer Start -->
    <div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <h4 class="text-white mb-4">M25 Travel</h4>
                        <p>Your trusted partner for all visa and travel needs.</p>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-share fa-2x text-white me-2"></i>
                            <a class="btn-square btn btn-primary rounded-circle mx-1" href=""><i class="fab fa-facebook-f"></i></a>
                            <a class="btn-square btn btn-primary rounded-circle mx-1" href=""><i class="fab fa-twitter"></i></a>
                            <a class="btn-square btn btn-primary rounded-circle mx-1" href=""><i class="fab fa-instagram"></i></a>
                            <a class="btn-square btn btn-primary rounded-circle mx-1" href=""><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <h4 class="text-white mb-4">Quick Links</h4>
                        <a href="index.php"><i class="fas fa-angle-right me-2"></i> Home</a>
                        <a href="about.html"><i class="fas fa-angle-right me-2"></i> About</a>
                        <a href="visa-details.php"><i class="fas fa-angle-right me-2"></i> Visa Details</a>
                        <a href="service.html"><i class="fas fa-angle-right me-2"></i> Services</a>
                        <a href="contact.html"><i class="fas fa-angle-right me-2"></i> Contact</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <h4 class="text-white mb-4">Contact Info</h4>
                        <a href=""><i class="fa fa-map-marker-alt me-2"></i> Winneba Street -Mallam Weija, Gbawe, Greater Accra, GS03301053</a>
                        <a href="mailto:info@m25travelagency.com"><i class="fas fa-envelope me-2"></i> info@m25travelagency.com</a>
                        <a href="tel:+233592605752"><i class="fas fa-phone me-2"></i> +233 59 260 5752</a>
                        <a href="tel:+233592605752"><i class="fas fa-print me-2"></i> +233 59 260 5752</a>
                    </div>
                </div>
                <div class="col-md-6 col-lg-6 col-xl-3">
                    <div class="footer-item d-flex flex-column">
                        <h4 class="text-white mb-4">Newsletter</h4>
                        <p>Subscribe to our newsletter for updates</p>
                        <div class="position-relative mx-auto rounded-pill">
                            <input class="form-control rounded-pill w-100 py-3 ps-4 pe-5" type="text" placeholder="Enter your email">
                            <button type="button" class="btn btn-primary rounded-pill position-absolute top-0 end-0 py-2 mt-2 me-2">Subscribe</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Copyright Start -->
    <div class="container-fluid copyright py-4">
        <div class="container">
            <div class="row g-4 align-items-center">
                <div class="col-md-6 text-center text-md-start mb-md-0">
                    <span class="text-white"><a href="#"><i class="fas fa-copyright text-light me-2"></i>M25 Travel</a>, All right reserved.</span>
                </div>
                <div class="col-md-6 text-center text-md-end text-white">
                    Designed By <a class="border-bottom" href="#">M25 Travel</a>
                </div>
            </div>
        </div>
    </div>
    <!-- Copyright End -->

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/lightbox/js/lightbox.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Back to top button functionality
        window.addEventListener('scroll', function() {
            const backToTop = document.querySelector('.back-to-top');
            if (window.pageYOffset > 300) {
                backToTop.style.display = 'block';
            } else {
                backToTop.style.display = 'none';
            }
        });

        function scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        // Auto-scroll to specific section if hash is present in URL
        window.addEventListener('load', function() {
            if (window.location.hash) {
                const target = document.querySelector(window.location.hash);
                if (target) {
                    setTimeout(() => {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }, 100);
                }
            }
        });
    </script>

</body>

</html>
