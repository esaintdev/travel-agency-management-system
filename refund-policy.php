<?php
// Get current date for "Last Updated"
$current_date = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Refund & Cancellation Policy - M25 Travel & Tour</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="M25 Travel & Tour Refund & Cancellation Policy - Service fees and payment terms" name="description">
    <meta content="refund policy, cancellation policy, service fees, M25 Travel Tour, payment terms" name="keywords">

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
        .refund-content {
            background: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .refund-section {
            margin-bottom: 35px;
        }
        .refund-section h3 {
            color: #13357B;
            font-weight: 700;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .refund-section h4 {
            color: #2c5aa0;
            font-weight: 600;
            margin-bottom: 15px;
            margin-top: 25px;
        }
        .refund-section p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 15px;
        }
        .refund-section ul {
            margin-bottom: 20px;
        }
        .refund-section li {
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
        .policy-highlight {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .policy-highlight .fas {
            color: #856404;
            margin-right: 10px;
        }
        .intro-policy {
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
                            <a href="legal-disclaimer" class="dropdown-item">Legal Disclaimer</a>
                            <a href="refund-policy" class="dropdown-item active">Refund Policy</a>
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
                    <h1 class="display-3 text-white animated slideInDown">Refund & Cancellation Policy</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="/">Home</a></li>
                            <li class="breadcrumb-item text-white active" aria-current="page">Refund Policy</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Header End -->

    <!-- Refund Policy Start -->
    <div class="container-xxl py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="refund-content">
                        <div class="text-center mb-5">
                            <h1 class="mb-4">M25 TRAVEL & TOUR – REFUND & CANCELLATION POLICY</h1>
                            <div class="last-updated">
                                <i class="fas fa-calendar-alt me-2"></i>
                                <strong>Last Updated:</strong> November 5, 2025
                            </div>
                        </div>

                        <div class="intro-policy">
                            <h4><i class="fas fa-ban me-2"></i>IMPORTANT PAYMENT POLICY</h4>
                            <p class="mb-0">This Refund & Cancellation Policy applies to all service payments made to M25 Travel & Tour worldwide. By using our services, you acknowledge that you have read, understood and accepted these refund conditions.</p>
                        </div>

                        <div class="refund-section" id="service-nature">
                            <h3>1. Service Nature</h3>
                            <p>M25 Travel & Tour provides advisory, consultation, document guidance and travel support services.</p>
                            
                            <div class="info-box">
                                <i class="fas fa-info-circle"></i>
                                <strong>Service Basis:</strong> Our services are based on time spent, professional review, expertise and preparation—not visa outcome.
                            </div>
                        </div>

                        <div class="refund-section" id="visa-rejection">
                            <h3>2. No Refund for Visa Rejection</h3>
                            <div class="critical-warning">
                                <i class="fas fa-ban"></i>
                                <strong>Non-Refundable Policy:</strong> All service fees paid to M25 Travel & Tour are non-refundable, whether or not a visa is approved.
                            </div>
                            <p>Visa outcome is 100% Embassy decision and cannot be guaranteed by any agency.</p>
                        </div>

                        <div class="refund-section" id="service-fees">
                            <h3>3. Non-Refundable Service Fees</h3>
                            <h4>Service fees cover:</h4>
                            <ul>
                                <li>Initial consultation</li>
                                <li>Profile evaluation</li>
                                <li>Document review</li>
                                <li>Advisory time</li>
                                <li>Processing guidance</li>
                                <li>Preparation work done by consultants</li>
                            </ul>
                            
                            <div class="warning-box">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Important:</strong> Once service has started or information has been provided, fees cannot be reversed or refunded.
                            </div>
                        </div>

                        <div class="refund-section" id="cancellation">
                            <h3>4. Cancellation Policy</h3>
                            <p>If a client decides to cancel the service after payment:</p>
                            <ul>
                                <li><strong>No refund will be issued.</strong></li>
                                <li>Service credit cannot be transferred to another person without approval.</li>
                            </ul>
                        </div>

                        <div class="refund-section" id="third-party-fees">
                            <h3>5. Government / Embassy / Third-Party Fees</h3>
                            <p>M25 Travel & Tour is not responsible for refunds involving:</p>
                            <ul>
                                <li>Embassy visa fees</li>
                                <li>Biometrics fees</li>
                                <li>Courier fees</li>
                                <li>Travel Insurance fees</li>
                                <li>Flight ticket fees</li>
                                <li>Hotel booking / tour booking costs</li>
                                <li>Third-party platform charges</li>
                            </ul>
                            
                            <div class="policy-highlight">
                                <i class="fas fa-external-link-alt"></i>
                                <strong>External Control:</strong> These are controlled by external entities, not M25 Travel & Tour.
                            </div>
                        </div>

                        <div class="refund-section" id="disputes">
                            <h3>6. Payment Disputes / Chargebacks</h3>
                            <div class="warning-box">
                                <i class="fas fa-gavel"></i>
                                <strong>Legal Warning:</strong> Attempting chargeback or false refund claims may lead to legal action and permanent service termination.
                            </div>
                        </div>

                        <div class="refund-section" id="interruptions">
                            <h3>7. Service Interruption Situations</h3>
                            <p>M25 Travel & Tour will not be held responsible for cancellations or delays caused by:</p>
                            <ul>
                                <li>Embassy policy changes</li>
                                <li>Immigration law or regulation changes</li>
                                <li>Government shutdown or strike</li>
                                <li>War / Natural disasters / Global restrictions</li>
                                <li>Client incomplete documents / No response / Failure to cooperate</li>
                            </ul>
                        </div>

                        <div class="refund-section" id="modifications">
                            <h3>8. Modification to Policy</h3>
                            <p>This Refund & Cancellation Policy may be updated anytime without notice. When updated, new version will be posted on this page.</p>
                        </div>

                        <div class="refund-section" id="contact">
                            <h3>Contact for Billing Queries</h3>
                            <p>For any billing support or clarification:</p>
                            
                            <div class="contact-info">
                                <h4><i class="fas fa-envelope me-2"></i>Billing Support</h4>
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
                        <h4><i class="fas fa-list me-2"></i>Policy Sections</h4>
                        <ul>
                            <li><a href="#service-nature">1. Service Nature</a></li>
                            <li><a href="#visa-rejection">2. No Refund for Visa Rejection</a></li>
                            <li><a href="#service-fees">3. Non-Refundable Service Fees</a></li>
                            <li><a href="#cancellation">4. Cancellation Policy</a></li>
                            <li><a href="#third-party-fees">5. Third-Party Fees</a></li>
                            <li><a href="#disputes">6. Payment Disputes</a></li>
                            <li><a href="#interruptions">7. Service Interruptions</a></li>
                            <li><a href="#modifications">8. Policy Modifications</a></li>
                            <li><a href="#contact">Billing Contact</a></li>
                        </ul>
                        
                        <div class="mt-4 p-3 bg-danger bg-opacity-10 rounded">
                            <h6><i class="fas fa-ban me-2 text-danger"></i>No Refunds</h6>
                            <p class="small mb-2 text-dark">All service fees are non-refundable regardless of visa outcome.</p>
                        </div>
                        
                        <div class="mt-3 p-3 bg-light rounded">
                            <h6><i class="fas fa-file-contract me-2"></i>Legal Framework</h6>
                            <p class="small mb-2">Complete policy documents:</p>
                            <a href="privacy-policy" class="btn btn-outline-primary btn-sm me-1 mb-1">Privacy</a>
                            <a href="terms-conditions" class="btn btn-outline-primary btn-sm me-1 mb-1">Terms</a>
                            <a href="legal-disclaimer" class="btn btn-outline-primary btn-sm mb-1">Disclaimer</a>
                            <a href="contact" class="btn btn-primary btn-sm w-100 mt-2">Contact Billing</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Refund Policy End -->

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
                    <a class="btn btn-link" href="refund-policy">Refund Policy</a>
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
                            <a href="refund-policy">Refund Policy</a>
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
                    title: 'M25 Travel & Tour - Refund & Cancellation Policy',
                    text: 'Check out M25 Travel & Tour\'s Refund & Cancellation Policy',
                    url: window.location.href
                });
            } else {
                const url = encodeURIComponent(window.location.href);
                const text = encodeURIComponent('M25 Travel & Tour - Refund Policy');
                window.open(`https://twitter.com/intent/tweet?url=${url}&text=${text}`, '_blank');
            }
        }
    </script>
</body>

</html>
