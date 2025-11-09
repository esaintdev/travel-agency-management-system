<?php
// Get current date for "Last Updated"
$current_date = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Legal Disclaimer - M25 Travel & Tour</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="M25 Travel & Tour Legal Disclaimer - Important legal information about our travel advisory services" name="description">
    <meta content="legal disclaimer, travel advisory, visa consultation, M25 Travel Tour, liability limitation" name="keywords">

    <!-- Favicon -->
    <link href="img/favicon.ico" rel="icon">

    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Heebo:wght@400;500;600&family=Nunito:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- Icon Font Stylesheet -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">
    <link href="lib/tempusdominus/css/tempusdominus-bootstrap-4.min.css" rel="stylesheet" />

    <!-- Customized Bootstrap Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- Template Stylesheet -->
    <link href="css/style.css" rel="stylesheet">

    <style>
        .disclaimer-content {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .disclaimer-section {
            margin-bottom: 35px;
        }
        .disclaimer-section h3 {
            color: #13357B;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .disclaimer-section h4 {
            color: #2c5aa0;
            font-weight: 600;
            margin-bottom: 15px;
            margin-top: 25px;
        }
        .disclaimer-section p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 15px;
        }
        .disclaimer-section ul {
            margin-bottom: 20px;
        }
        .disclaimer-section li {
            margin-bottom: 8px;
            color: #555;
            line-height: 1.6;
        }
        .last-updated {
            background: #f8f9fa;
            padding: 15px 20px;
            border-radius: 10px;
            border-left: 4px solid #13357B;
            margin-bottom: 30px;
        }
        .contact-info {
            background: linear-gradient(135deg, #13357B 0%, #1e4a8c 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
        }
        .contact-info h4 {
            color: white;
            margin-bottom: 20px;
        }
        .contact-info a {
            color: #fff;
            text-decoration: none;
        }
        .contact-info a:hover {
            color: #ffd700;
        }
        .toc {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .toc h4 {
            color: #13357B;
            margin-bottom: 15px;
        }
        .toc ul {
            list-style: none;
            padding-left: 0;
        }
        .toc li {
            margin-bottom: 8px;
        }
        .toc a {
            color: #555;
            text-decoration: none;
            transition: color 0.3s;
        }
        .toc a:hover {
            color: #13357B;
        }
        .critical-warning {
            background: #dc3545;
            color: white;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 5px solid #721c24;
        }
        .critical-warning .fas {
            color: white;
            margin-right: 10px;
        }
        .warning-box {
            background: #f8d7da;
            border: 1px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .warning-box .fas {
            color: #721c24;
            margin-right: 10px;
        }
        .info-box {
            background: #d1ecf1;
            border: 1px solid #17a2b8;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .info-box .fas {
            color: #0c5460;
            margin-right: 10px;
        }
        .legal-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .legal-notice .fas {
            color: #856404;
            margin-right: 10px;
        }
        .intro-disclaimer {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
    </style>
</head>

<body>
    <!-- Spinner Start -->
    <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
        <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <!-- Spinner End -->

    <!-- Topbar Start -->
    <div class="container-fluid bg-dark px-5 d-none d-lg-block">
        <div class="row gx-0">
            <div class="col-lg-8 text-center text-lg-start mb-2 mb-lg-0">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <small class="me-3 text-light"><i class="fa fa-map-marker-alt me-2"></i>Accra, Ghana</small>
                    <small class="me-3 text-light"><i class="fa fa-phone-alt me-2"></i>+233 59 260 5752</small>
                    <small class="text-light"><i class="fa fa-envelope-open me-2"></i>info@m25travelagency.com</small>
                </div>
            </div>
            <div class="col-lg-4 text-center text-lg-end">
                <div class="d-inline-flex align-items-center" style="height: 45px;">
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-twitter fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-facebook-f fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-linkedin-in fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle me-2" href=""><i class="fab fa-instagram fw-normal"></i></a>
                    <a class="btn btn-sm btn-outline-light btn-sm-square rounded-circle" href=""><i class="fab fa-youtube fw-normal"></i></a>
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
                        </div>
                    </div>
                    <a href="contact" class="nav-item nav-link">Contact</a>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle active" data-bs-toggle="dropdown">Legal</a>
                        <div class="dropdown-menu m-0">
                            <a href="privacy-policy" class="dropdown-item">Privacy Policy</a>
                            <a href="terms-conditions" class="dropdown-item">Terms & Conditions</a>
                            <a href="legal-disclaimer" class="dropdown-item active">Legal Disclaimer</a>
                        </div>
                    </div>
                </div>
                <button class="btn btn-primary btn-md-square border-secondary mb-3 mb-md-3 mb-lg-0 me-3" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search"></i></button>
                <button class="btn btn-secondary btn-md-square border-secondary mb-3 mb-md-3 mb-lg-0 me-3" onclick="shareWebsite()" title="Share Website"><i class="fas fa-share-alt"></i></button>
                
                <a href="client-registration" class="btn btn-primary border-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0">Get A Form</a>
            </div>
        </nav>
    </div>

    <!-- Header Start -->
    <div class="container-fluid bg-primary py-5 mb-5 hero-header">
        <div class="container py-5">
            <div class="row justify-content-center py-5">
                <div class="col-lg-10 pt-lg-5 mt-lg-5 text-center">
                    <h1 class="display-3 text-white animated slideInDown">Legal Disclaimer</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Legal Disclaimer</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- Legal Disclaimer Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="disclaimer-content">
                        <div class="text-center mb-5">
                            <h1 class="mb-4">M25 TRAVEL & TOUR – LEGAL DISCLAIMER</h1>
                            <div class="last-updated">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <strong>Last Updated:</strong> <?php echo $current_date; ?>
                            </div>
                        </div>

                        <div class="intro-disclaimer">
                            <h4><i class="fas fa-exclamation-triangle me-2"></i>IMPORTANT LEGAL NOTICE</h4>
                            <p class="mb-0">Please read this disclaimer carefully before using our services. This disclaimer contains important legal information about the limitations of our services.</p>
                        </div>

                        <div class="disclaimer-section">
                            <p>The information provided by M25 Travel & Tour on this website, social media platforms, consultation platforms, communication channels and all other digital/printed material is for <strong>general informational and educational travel guidance purposes only</strong>.</p>
                            
                            <div class="legal-notice">
                                <i class="fas fa-balance-scale"></i>
                                <strong>Legal Notice:</strong> All information is provided in good faith. However we make no legal guarantee, warranty or representation of any kind regarding the accuracy, validity, reliability or completeness of any information.
                            </div>
                        </div>

                        <div class="disclaimer-section" id="embassy-representation">
                            <h3>1. No Embassy Representation / No Government Authority</h3>
                            <div class="critical-warning">
                                <i class="fas fa-ban"></i>
                                <strong>Critical Notice:</strong> M25 Travel & Tour is not a Government, Embassy, Consulate, Immigration Authority or Recruitment Agency.
                            </div>
                            <p>We are a <strong>private travel advisory and visa preparation consulting service</strong>.</p>
                        </div>

                        <div class="disclaimer-section" id="visa-outcomes">
                            <h3>2. Visa Outcomes Are Not Guaranteed</h3>
                            <div class="warning-box">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>No Guarantees:</strong> All visa outcomes are exclusively determined by the Embassy or Government Authority. We do not guarantee visa approvals, fast approvals, easy approvals or automatic approvals.
                            </div>
                            <p><em>Any promotional content must not be interpreted as approval assurance or guarantee of success.</em></p>
                        </div>

                        <div class="disclaimer-section" id="legal-advice">
                            <h3>3. No Legal Immigration Advice</h3>
                            <div class="info-box">
                                <i class="fas fa-gavel"></i>
                                <strong>Legal Limitation:</strong> Information provided by M25 Travel & Tour is not legal immigration advice.
                            </div>
                            <p>Clients must seek licensed immigration lawyers, where required, especially in countries where immigration law practice requires licensing.</p>
                        </div>

                        <div class="disclaimer-section" id="third-party">
                            <h3>4. Third Party Products / Links / Services</h3>
                            <p>The website may contain links to third-party websites, flight providers, hotel service platforms, tourism partners or external resources.</p>
                            
                            <div class="warning-box">
                                <i class="fas fa-external-link-alt"></i>
                                <strong>Third-Party Disclaimer:</strong> M25 Travel & Tour is not responsible for the content, service quality or policies of any external third-party providers.
                            </div>
                            
                            <p><strong>Use of third-party services is at your own risk.</strong></p>
                        </div>

                        <div class="disclaimer-section" id="personal-responsibility">
                            <h3>5. Personal Responsibility</h3>
                            <p>By using this website and services, you agree:</p>
                            <ul>
                                <li>You are responsible for verifying all required documents</li>
                                <li>You will submit genuine, truthful and valid information</li>
                                <li>You understand that fraudulent documents can result in permanent bans or criminal action from authorities</li>
                                <li>You follow legal international travel rules</li>
                            </ul>
                            
                            <div class="critical-warning">
                                <i class="fas fa-user-shield"></i>
                                <strong>Client Responsibility:</strong> M25 Travel & Tour will not be responsible for false information a client submits.
                            </div>
                        </div>

                        <div class="disclaimer-section" id="professional-use">
                            <h3>6. Professional Use Disclaimer</h3>
                            <p>Any examples, success stories, testimonials or previous approvals displayed are for <strong>illustration only</strong> and do not imply future guaranteed results.</p>
                        </div>

                        <div class="disclaimer-section" id="liability-limitation">
                            <h3>7. Liability Limitation</h3>
                            <p>M25 Travel & Tour is not liable for:</p>
                            <ul>
                                <li>Embassy refusal decisions</li>
                                <li>Application delays</li>
                                <li>Border control decisions</li>
                                <li>Airline cancellations or losses</li>
                                <li>Personal or financial losses from travel outcomes</li>
                                <li>Misuse of information by users</li>
                            </ul>
                        </div>

                        <div class="disclaimer-section" id="policy-changes">
                            <h3>8. Policy Changes</h3>
                            <p>This Disclaimer may be updated without notice. When updated, new version will be posted on this page.</p>
                        </div>

                        <div class="disclaimer-section" id="contact">
                            <h3>Contact Information</h3>
                            <p>For any legal or compliance related inquiries:</p>
                            
                            <div class="contact-info">
                                <h4><i class="fas fa-envelope me-2"></i>Legal Inquiries</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><i class="fas fa-envelope me-2"></i><strong>Email:</strong><br>
                                        <a href="mailto:info@m25travelagency.com">info@m25travelagency.com</a></p>
                                        
                                        <p><i class="fas fa-globe me-2"></i><strong>Website:</strong><br>
                                        <a href="https://www.m25travelagency.com" target="_blank">www.m25travelagency.com</a></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><i class="fas fa-phone me-2"></i><strong>Phone / WhatsApp:</strong><br>
                                        <a href="tel:+233592605752">+233 59 260 5752</a></p>
                                        
                                        <p><i class="fas fa-map-marker-alt me-2"></i><strong>Location:</strong><br>
                                        Accra, Ghana</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table of Contents Sidebar -->
                <div class="col-lg-4">
                    <div class="toc sticky-top" style="top: 100px;">
                        <h4><i class="fas fa-list me-2"></i>Disclaimer Sections</h4>
                        <ul>
                            <li><a href="#embassy-representation">1. No Embassy Representation</a></li>
                            <li><a href="#visa-outcomes">2. Visa Outcomes Not Guaranteed</a></li>
                            <li><a href="#legal-advice">3. No Legal Immigration Advice</a></li>
                            <li><a href="#third-party">4. Third Party Services</a></li>
                            <li><a href="#personal-responsibility">5. Personal Responsibility</a></li>
                            <li><a href="#professional-use">6. Professional Use Disclaimer</a></li>
                            <li><a href="#liability-limitation">7. Liability Limitation</a></li>
                            <li><a href="#policy-changes">8. Policy Changes</a></li>
                            <li><a href="#contact">Contact Information</a></li>
                        </ul>
                        
                        <div class="mt-4 p-3 bg-danger bg-opacity-10 rounded">
                            <h6><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Critical Notice</h6>
                            <p class="small mb-2 text-dark">We are NOT a government agency. No visa guarantees provided.</p>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6><i class="fas fa-balance-scale me-2"></i>Legal Documents</h6>
                            <p class="small mb-2">Complete legal framework:</p>
                            <a href="privacy-policy" class="btn btn-outline-primary btn-sm me-1 mb-2">Privacy Policy</a>
                            <a href="terms-conditions" class="btn btn-outline-primary btn-sm mb-2">Terms & Conditions</a>
                            <a href="contact" class="btn btn-primary btn-sm w-100">Contact Legal Team</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Legal Disclaimer End -->

    <!-- Footer Start -->
    <div class="container-fluid bg-dark text-light footer pt-5 mt-5 wow fadeIn" data-wow-delay="0.1s">
        <div class="container py-5">
            <div class="row g-5">
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-white mb-3">Company</h4>
                    <a class="btn btn-link" href="about">About Us</a>
                    <a class="btn btn-link" href="contact">Contact Us</a>
                    <a class="btn btn-link" href="privacy-policy">Privacy Policy</a>
                    <a class="btn btn-link" href="terms-conditions">Terms & Conditions</a>
                    <a class="btn btn-link" href="legal-disclaimer">Legal Disclaimer</a>
                    <a class="btn btn-link" href="faq">FAQ</a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-white mb-3">Contact</h4>
                    <p class="mb-2"><i class="fa fa-map-marker-alt me-3"></i>Accra, Ghana</p>
                    <p class="mb-2"><i class="fa fa-phone-alt me-3"></i>+233 59 260 5752</p>
                    <p class="mb-2"><i class="fa fa-envelope me-3"></i>info@m25travelagency.com</p>
                    <div class="d-flex pt-2">
                        <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-youtube"></i></a>
                        <a class="btn btn-outline-light btn-social" href=""><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-white mb-3">Services</h4>
                    <a class="btn btn-link" href="service">Visa Consultation</a>
                    <a class="btn btn-link" href="countries">Country Information</a>
                    <a class="btn btn-link" href="service">Travel Planning</a>
                    <a class="btn btn-link" href="service">Document Review</a>
                </div>
                <div class="col-lg-3 col-md-6">
                    <h4 class="text-white mb-3">Quick Links</h4>
                    <a class="btn btn-link" href="client-registration">Get Started</a>
                    <a class="btn btn-link" href="client-login">Client Login</a>
                    <a class="btn btn-link" href="testimonial">Testimonials</a>
                    <a class="btn btn-link" href="feature">Features</a>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="copyright">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
                        &copy; <a class="border-bottom" href="#">M25 Travel & Tour</a>, All Right Reserved.
                    </div>
                    <div class="col-md-6 text-center text-md-end">
                        <div class="footer-menu">
                            <a href="/">Home</a>
                            <a href="privacy-policy">Privacy</a>
                            <a href="terms-conditions">Terms</a>
                            <a href="legal-disclaimer">Disclaimer</a>
                            <a href="faq">Help</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <!-- Back to Top -->
    <a href="#" class="btn btn-lg btn-primary btn-lg-square back-to-top"><i class="bi bi-arrow-up"></i></a>

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/tempusdominus/js/moment.min.js"></script>
    <script src="lib/tempusdominus/js/moment-timezone.min.js"></script>
    <script src="lib/tempusdominus/js/tempusdominus-bootstrap-4.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <!-- Smooth Scrolling for TOC Links -->
    <script>
        document.querySelectorAll('.toc a[href^="#"]').forEach(anchor => {
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

        // Share Website Function
        function shareWebsite() {
            if (navigator.share) {
                navigator.share({
                    title: 'M25 Travel & Tour - Legal Disclaimer',
                    text: 'Check out M25 Travel & Tour\'s Legal Disclaimer - Important legal information about our services',
                    url: window.location.href
                });
            } else {
                // Fallback for browsers that don't support Web Share API
                const url = encodeURIComponent(window.location.href);
                const text = encodeURIComponent('M25 Travel & Tour - Legal Disclaimer');
                window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
            }
        }
    </script>
</body>

</html>
