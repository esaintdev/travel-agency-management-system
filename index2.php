<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <title>M25 Travel Agency - Visa & Immigration</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="" name="keywords">
        <meta content="" name="description">

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
        
        <!-- Gallery Styles -->
        <style>
            .gallery-item {
                position: relative;
                overflow: hidden;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
            }
            
            .gallery-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }
            
            .gallery-img {
                position: relative;
                overflow: hidden;
                border-radius: 10px;
            }
            
            .gallery-img img {
                transition: transform 0.3s ease;
                height: 250px;
                object-fit: cover;
            }
            
            .gallery-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.7);
                display: flex;
                align-items: center;
                justify-content: center;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .gallery-item:hover .gallery-overlay {
                opacity: 1;
            }
            
            .gallery-item:hover .gallery-img img {
                transform: scale(1.1);
            }
            
            .gallery-content .btn {
                width: 60px;
                height: 60px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 20px;
                transition: all 0.3s ease;
            }
            
            .gallery-content .btn:hover {
                background-color: var(--bs-primary) !important;
                color: white !important;
                transform: scale(1.1);
            }
        </style>
        
        <!-- Lightbox CSS -->
        <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css" rel="stylesheet">

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
                    <!-- <img src="img/logo.png" alt="Logo"> -->
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav ms-auto py-0">
                        <a href="/" class="nav-item nav-link active">Home</a>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">About</a>
                            <div class="dropdown-menu m-0">
                                <a href="about" class="dropdown-item">About</a>
                                <a href="blog" class="dropdown-item">Blog</a>
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
                        <a href="contact" class="nav-item nav-link">Contact</a>
                    </div>
                    <button class="btn btn-primary btn-md-square border-secondary mb-3 mb-md-3 mb-lg-0 me-3" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search"></i></button>
                    <button class="btn btn-secondary btn-md-square border-secondary mb-3 mb-md-3 mb-lg-0 me-3" onclick="shareWebsite()" title="Share Website"><i class="fas fa-share-alt"></i></button>
                    
                    <a href="client-registration" class="btn btn-primary border-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0">Get A Form</a>
                </div>
            </nav>
        </div>
        <!-- Navbar & Hero End -->


        <!-- Carousel Start -->
        <div class="carousel-header">
            <div id="carouselId" class="carousel slide" data-bs-ride="carousel">
                <!-- <ol class="carousel-indicators">
                    <li data-bs-target="#carouselId" data-bs-slide-t o="0" class="active"></li>
                    <li data-bs-target="#carouselId" data-bs-slide-to="1"></li>
                </ol> -->
                <div class="carousel-inner" role="listbox">
                    <div class="carousel-item active">
                        <img src="img/carousel-1.jpg" class="img-fluid" alt="Image">
                        <div class="carousel-caption">
                            <div class="text-center p-4" style="max-width: 900px;">
                                <h4 class="text-white text-uppercase fw-bold mb-3 mb-md-4 wow fadeInUp" data-wow-delay="0.1s">Solution For All Type Of Visas</h4>
                                <h1 class="display-1 text-capitalize text-white mb-3 mb-md-4 wow fadeInUp" data-wow-delay="0.3s">Best Visa & Immigrations Services</h1>
                                <p class="text-white mb-4 mb-md-5 fs-5 wow fadeInUp" data-wow-delay="0.5s">Welcome to M25 Travels & Tour Agency, where we specialize in transforming travel dreams into reality. As your dedicated travel and tour specialist.</p>
                                <a class="btn btn-primary border-secondary rounded-pill text-white py-3 px-5 wow fadeInUp" data-wow-delay="0.7s" href="service">More Details</a>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="carousel-item">
                        <img src="img/carousel-2.jpg" class="img-fluid" alt="Image">
                        <div class="carousel-caption">
                            <div class="text-center p-4" style="max-width: 900px;">
                                <h5 class="text-white text-uppercase fw-bold mb-3 mb-md-4 wow fadeInUp" data-wow-delay="0.1s">Solution For All Type Of Visas</h5>
                                <h1 class="display-1 text-capitalize text-white mb-3 mb-md-4 wow fadeInUp" data-wow-delay="0.3s">Best Visa Immigrations Services</h1>
                                <p class="text-white mb-4 mb-md-5 fs-5 wow fadeInUp" data-wow-delay="0.5s">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, 
                                </p>
                                <a class="btn btn-primary border-secondary rounded-pill text-white py-3 px-5 wow fadeInUp" data-wow-delay="0.7s" href="#">More Details</a>
                            </div>
                        </div>
                    </div> -->
                </div>
                <!-- <button class="carousel-control-prev" type="button" data-bs-target="#carouselId" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon bg-secondary wow fadeInLeft" data-wow-delay="0.2s" aria-hidden="false"></span>
                    <span class="visually-hidden-focusable">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselId" data-bs-slide="next">
                    <span class="carousel-control-next-icon bg-secondary wow fadeInRight" data-wow-delay="0.2s" aria-hidden="false"></span>
                    <span class="visually-hidden-focusable">Next</span>
                </button> -->
            </div>
        </div>
        <!-- Carousel End -->


        <!-- Modal Search Start -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content rounded-0">
                    <div class="modal-header">
                        <h4 class="modal-title text-secondary mb-0" id="exampleModalLabel">Search by keyword</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body d-flex align-items-center">
                        <div class="input-group w-75 mx-auto d-flex">
                            <input type="search" class="form-control p-3" placeholder="keywords" aria-describedby="search-icon-1">
                            <span id="search-icon-1" class="input-group-text p-3"><i class="fa fa-search"></i></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal Search End -->

        <!-- Popup Form Modal Start -->
        <div class="modal fade" id="popupModal" tabindex="-1" aria-labelledby="popupModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title text-white" id="popupModalLabel">
                            <i class="fas fa-plane me-2 text-white">Start Here Get Quick responds</i>
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="popupForm">
                            <div class="mb-3">
                                <label for="popup_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="popup_name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="popup_email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="popup_email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="popup_country" class="form-label">Country You Want to Go <span class="text-danger">*</span></label>
                                <select class="form-select" id="popup_country" name="country" required>
                                    <option value="">Select Country</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="United States">United States</option>
                                    <option value="Canada">Canada</option>
                                    <option value="Australia">Australia</option>
                                    <option value="Germany">Germany</option>
                                    <option value="France">France</option>
                                    <option value="Italy">Italy</option>
                                    <option value="Spain">Spain</option>
                                    <option value="Netherlands">Netherlands</option>
                                    <option value="Sweden">Sweden</option>
                                    <option value="Norway">Norway</option>
                                    <option value="Denmark">Denmark</option>
                                    <option value="Brazil">Brazil</option>
                                    <option value="India">India</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="btn btn-primary btn-lg px-4">
                                    <i class="fas fa-paper-plane me-2"></i>Get Started
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer text-center">
                        <small class="text-muted">
                            <i class="fas fa-shield-alt me-1"></i>Your information is secure and will only be used to contact you about your travel plans.
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <!-- Popup Form Modal End -->



        <!-- About Start -->
        <div class="container-fluid py-5">
            <div class="container py-5">
                <div class="row g-5">
                    <div class="col-xl-5 wow fadeInLeft" data-wow-delay="0.1s">
                        <div class="bg-light rounded">
                            <img src="img/about-2.png" class="img-fluid w-100" style="margin-bottom: -7px;" alt="Image">
                            <img src="img/about-3.jpg" class="img-fluid w-100 border-bottom border-5 border-primary" style="border-top-right-radius: 300px; border-top-left-radius: 300px;" alt="Image">
                        </div>
                    </div>
                    <div class="col-xl-7 wow fadeInRight" data-wow-delay="0.3s">
                        <h5 class="sub-title pe-3">About the company</h5>
                        <h1 class="display-5 mb-4">We’re Trusted Immigration Consultant Agency.</h1>
                        <p class="mb-4">At M25 Travel Agency, we take pride in being your premier travel and tour specialist, committed to providing seamless and expedited Visa processing services. Whether you're planning a business trip, a leisurely vacation, or embarking on an adventure to explore the world, we are your first choice for quality, efficiency, and affordability.</p>
                        <div class="row gy-4 align-items-center">
                            <div class="col-12 col-sm-6 d-flex align-items-center">
                                <i class="fas fa-map-marked-alt fa-3x text-secondary"></i>
                                <h5 class="ms-4">Best Immigration Resources</h5>
                            </div>
                            <div class="col-12 col-sm-6 d-flex align-items-center">
                                <i class="fas fa-passport fa-3x text-secondary"></i>
                                <h5 class="ms-4">Return Visas Availabile</h5>
                            </div>
                            <div class="col-4 col-md-3">
                                <div class="bg-light text-center rounded p-3">
                                    <div class="mb-2">
                                        <i class="fas fa-ticket-alt fa-4x text-primary"></i>
                                    </div>
                                    <h1 class="display-5 fw-bold mb-2">10</h1>
                                    <p class="text-muted mb-0">Years of Experience</p>
                                </div>
                            </div>
                            <div class="col-8 col-md-9">
                                <div class="mb-5">
                                    <p class="text-primary h6 mb-3"><i class="fa fa-check-circle text-secondary me-2"></i> Offer 100 % Genuine Assistance</p>
                                    <p class="text-primary h6 mb-3"><i class="fa fa-check-circle text-secondary me-2"></i> It’s Faster & Reliable Execution</p>
                                    <p class="text-primary h6 mb-3"><i class="fa fa-check-circle text-secondary me-2"></i> Accurate & Expert Advice</p>
                                </div>
                                <div class="d-flex flex-wrap">
                                    <div id="phone-tada" class="d-flex align-items-center justify-content-center me-4">
                                        <a href="tel:+233592605752" class="position-relative wow tada" data-wow-delay=".9s">
                                            <i class="fa fa-phone-alt text-primary fa-3x"></i>
                                            <div class="position-absolute" style="top: 0; left: 25px;">
                                                <span><i class="fa fa-comment-dots text-secondary"></i></span>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <span class="text-primary">Have any questions?</span>
                                        <span class="text-secondary fw-bold fs-5" style="letter-spacing: 2px;">Free: +233 59 260 5752</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- About End -->


        <!-- Counter Facts Start -->
        <div class="container-fluid counter-facts py-5">
            <div class="container py-5">
                <div class="row g-4">
                    <div class="col-12 col-sm-6 col-md-6 col-xl-3 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="counter">
                            <div class="counter-icon">
                                <i class="fas fa-passport"></i>
                            </div>
                            <div class="counter-content">
                                <h3>Visa Categories</h3>
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="counter-value" data-toggle="counter-up">31</span>
                                    <h4 class="text-secondary mb-0" style="font-weight: 600; font-size: 25px;">+</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6 col-xl-3 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="counter">
                            <div class="counter-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="counter-content">
                                <h3>Team Members</h3>
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="counter-value" data-toggle="counter-up">377</span>
                                    <h4 class="text-secondary mb-0" style="font-weight: 600; font-size: 25px;">+</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6 col-xl-3 wow fadeInUp" data-wow-delay="0.5s">
                        <div class="counter">
                            <div class="counter-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="counter-content">
                                <h3>Visa Process</h3>
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="counter-value" data-toggle="counter-up">4.9</span>
                                    <h4 class="text-secondary mb-0" style="font-weight: 600; font-size: 25px;">K</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-md-6 col-xl-3 wow fadeInUp" data-wow-delay="0.7s">
                        <div class="counter">
                            <div class="counter-icon">
                                <i class="fas fa-handshake"></i>
                            </div>
                            <div class="counter-content">
                                <h3>Success Rates</h3>
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="counter-value" data-toggle="counter-up">98</span>
                                    <h4 class="text-secondary mb-0" style="font-weight: 600; font-size: 25px;">%</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Counter Facts End -->

        

        <!-- Services Start -->
        <div class="container-fluid service overflow-hidden pt-5">
            <div class="container py-5">
                <div class="section-title text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="sub-style">
                        <h5 class="sub-title text-primary px-3">Visa Categories</h5>
                    </div>
                    <h1 class="display-5 mb-4">Your Gateway to Global Travel and Unforgettable Journeys!</h1>
                    <p class="mb-0">Navigating the complexities of visa applications can be overwhelming, but with M25 Travel Agency, you're in capable hands. Our streamlined process ensures a hassle-free experience, and we proudly offer a "No Visa, No Fees" guarantee. To get started, simply complete the form below to check the next available travel date</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="service-item">
                            <div class="service-inner">
                                <div class="service-img">
                                    <img src="img/job.png" class="img-fluid w-100 rounded" alt="Image">
                                </div>
                                <div class="service-title">
                                    <div class="service-title-name">
                                        <div class="bg-primary text-center rounded p-3 mx-5 mb-4">
                                            <a href="visa-details.php#job-visa" class="h4 text-white mb-0">Job Visa</a>
                                        </div>
                                        <a class="btn bg-light text-secondary rounded-pill py-3 px-5 mb-4" href="visa-details.php#job-visa">Explore More</a>
                                    </div>
                                    <div class="service-content pb-4">
                                        <a href="visa-details.php#job-visa"><h4 class="text-white mb-4 py-3">Job Visa</h4></a>
                                        <div class="px-4">
                                            <p class="mb-4">Job Visa is designed for individuals who wish to work or engage in employment activities in another country. It allows you to secure a temporary work permit and travel documents to begin your professional journey abroad.</p>
                                            <a class="btn btn-primary border-secondary rounded-pill text-white py-3 px-5" href="visa-details.php#job-visa">Explore More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="service-item">
                            <div class="service-inner">
                                <div class="service-img">
                                    <img src="img/business.png" class="img-fluid w-100 rounded" alt="Image">
                                </div>
                                <div class="service-title">
                                    <div class="service-title-name">
                                        <div class="bg-primary text-center rounded p-3 mx-5 mb-4">
                                            <a href="visa-details.php#business-visa" class="h4 text-white mb-0">Business Visa</a>
                                        </div>
                                        <a class="btn bg-light text-secondary rounded-pill py-3 px-5 mb-4" href="visa-details.php#business-visa">Explore More</a>
                                    </div>
                                    <div class="service-content pb-4">
                                        <a href="visa-details.php#business-visa"><h4 class="text-white mb-4 py-3">Business Visa</h4></a>
                                        <div class="px-4">
                                            <p class="mb-4">Business Visa is designed for entrepreneurs, company representatives, and professionals who wish to travel abroad for business-related activities such as meetings, conferences, or partnerships.</p>
                                            <a class="btn btn-primary border-secondary rounded-pill text-white py-3 px-5" href="visa-details.php#business-visa">Explore More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.5s">
                        <div class="service-item">
                            <div class="service-inner">
                                <div class="service-img">
                                    <img src="img/diplomatic.png" class="img-fluid w-100 rounded" alt="Image">
                                </div>
                                <div class="service-title">
                                    <div class="service-title-name">
                                        <div class="bg-primary text-center rounded p-3 mx-5 mb-4">
                                            <a href="visa-details.php#diplomatic-visa" class="h4 text-white mb-0">Diplometic Visa</a>
                                        </div>
                                        <a class="btn bg-light text-secondary rounded-pill py-3 px-5 mb-4" href="visa-details.php#diplomatic-visa">Explore More</a>
                                    </div>
                                    <div class="service-content pb-4">
                                        <a href="visa-details.php#diplomatic-visa"><h4 class="text-white mb-4 py-3">Diplometic Visa</h4></a>
                                        <div class="px-4">
                                            <p class="mb-4">Diplomatic Visa is designed for diplomats, government officials, and representatives of international organizations who require travel documents to conduct official business abroad.</p>
                                            <a class="btn btn-primary border-secondary rounded-pill text-white py-3 px-5" href="visa-details.php#diplomatic-visa">Explore More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="service-item">
                            <div class="service-inner">
                                <div class="service-img">
                                    <img src="img/student.png" class="img-fluid w-100 rounded" alt="Image">
                                </div>
                                <div class="service-title">
                                    <div class="service-title-name">
                                        <div class="bg-primary text-center rounded p-3 mx-5 mb-4">
                                            <a href="visa-details.php#student-visa" class="h4 text-white mb-0">Students Visa</a>
                                        </div>
                                        <a class="btn bg-light text-secondary rounded-pill py-3 px-5 mb-4" href="visa-details.php#student-visa">Explore More</a>
                                    </div>
                                    <div class="service-content pb-4">
                                        <a href="visa-details.php#student-visa"><h4 class="text-white mb-4 py-3">Students Visa</h4></a>
                                        <div class="px-4">
                                            <p class="mb-4">Students Visa is designed for individuals who wish to study or pursue higher education in another country. It allows you to secure a temporary study permit and travel documents to begin your academic journey abroad.</p>
                                            <a class="btn btn-primary border-secondary rounded-pill text-white py-3 px-5" href="visa-details.php#student-visa">Explore More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="service-item">
                            <div class="service-inner">
                                <div class="service-img">
                                    <img src="img/relocation.png" class="img-fluid w-100 rounded" alt="Image">
                                </div>
                                <div class="service-title">
                                    <div class="service-title-name">
                                        <div class="bg-primary text-center rounded p-3 mx-5 mb-4">
                                            <a href="visa-details.php#residence-visa" class="h4 text-white mb-0">Relocation Visa</a>
                                        </div>
                                        <a class="btn bg-light text-secondary rounded-pill py-3 px-5 mb-4" href="visa-details.php#residence-visa">Explore More</a>
                                    </div>
                                    <div class="service-content pb-4">
                                        <a href="visa-details.php#residence-visa"><h4 class="text-white mb-4 py-3">Relocation Visa</h4></a>
                                        <div class="px-4">
                                            <p class="mb-4">Relocation Visa is designed for individuals who wish to live permanently in another country. It allows you to secure a permanent residence permit and travel documents to begin your life abroad.</p>
                                            <a class="btn btn-primary border-secondary rounded-pill text-white py-3 px-5" href="visa-details.php#residence-visa">Explore More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-4 wow fadeInUp" data-wow-delay="0.5s">
                        <div class="service-item">
                            <div class="service-inner">
                                <div class="service-img">
                                    <img src="img/tourist.png" class="img-fluid w-100 rounded" alt="Image">
                                </div>
                                <div class="service-title">
                                    <div class="service-title-name">
                                        <div class="bg-primary text-center rounded p-3 mx-5 mb-4">
                                            <a href="visa-details.php#tourist-visa" class="h4 text-white mb-0">Tourist Visa</a>
                                        </div>
                                        <a class="btn bg-light text-secondary rounded-pill py-3 px-5 mb-4" href="visa-details.php#tourist-visa">Explore More</a>
                                    </div>
                                    <div class="service-content pb-4">
                                        <a href="visa-details.php#tourist-visa"><h4 class="text-white mb-4 py-3">Tourist Visa</h4></a>
                                        <div class="px-4">
                                            <p class="mb-4">Tourist Visa is designed for individuals who wish to visit another country for leisure, tourism, or vacation. It allows you to secure a temporary travel permit and travel documents to begin your vacation abroad.</p>
                                            <a class="btn btn-primary border-secondary rounded-pill text-white py-3 px-5" href="visa-details.php#tourist-visa">Explore More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Services End -->



        <!-- Features Start -->
        <div class="container-fluid features overflow-hidden py-5">
            <div class="container">
                <div class="section-title text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="sub-style">
                        <h5 class="sub-title text-primary px-3">Why Choose Us</h5>
                    </div>
                    <h1 class="display-5 mb-4">Offer Tailor Made Services That Our Client Requires</h1>
                    <p class="mb-0">M25 Travel Agency specializes in helping you apply for a UK Visa in Ghana. Our experienced team considers your unique circumstances, providing personalized advice on documentation, application procedures, and potential pitfalls. We aim to save you time, expenses, and emotional stress by offering:</p>
                </div>
                <div class="row g-4 justify-content-center text-center">
                    <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="feature-item text-center p-4">
                            <div class="feature-icon p-3 mb-4">
                                <i class="fas fa-dollar-sign fa-4x text-primary"></i>
                            </div>
                            <div class="feature-content d-flex flex-column">
                                <h5 class="mb-3">Cost-Effective</h5>
                                <p class="mb-3">We offer competitive pricing and flexible payment options to make the visa application process affordable and accessible to everyone.</p>
                                <a class="btn btn-secondary rounded-pill" href="#">Read More<i class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="feature-item text-center p-4">
                            <div class="feature-icon p-3 mb-4">
                                <i class="fab fa-cc-visa fa-4x text-primary"></i>
                            </div>
                            <div class="feature-content d-flex flex-column">
                                <h5 class="mb-3">Visa Assistance</h5>
                                <p class="mb-3">We provide personalized guidance and support throughout the visa application process, ensuring you have a smooth and stress-free experience.</p>
                                <a class="btn btn-secondary rounded-pill" href="#">Read More<i class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.5s">
                        <div class="feature-item text-center p-4">
                            <div class="feature-icon p-3 mb-4">
                                <i class="fas fa-atlas fa-4x text-primary"></i>
                            </div>
                            <div class="feature-content d-flex flex-column">
                                <h5 class="mb-3">Faster Processing</h5>
                                <p class="mb-3">We offer fast and efficient processing of visa applications, ensuring you receive your travel documents in a timely manner.</p>
                                <a class="btn btn-secondary rounded-pill" href="#">Read More<i class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3 wow fadeInUp" data-wow-delay="0.7s">
                        <div class="feature-item text-center p-4">
                            <div class="feature-icon p-3 mb-4">
                                <i class="fas fa-users fa-4x text-primary"></i>
                            </div>
                            <div class="feature-content d-flex flex-column">
                                <h5 class="mb-3">Direct Interviews</h5>
                                <p class="mb-3">We offer direct interviews with visa officers to ensure you receive a favorable decision and secure your visa application.</p>
                                <a class="btn btn-secondary rounded-pill" href="#">Read More<i class="fas fa-arrow-right ms-2"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <a class="btn btn-primary border-secondary rounded-pill py-3 px-5 wow fadeInUp" data-wow-delay="0.1s" href="#">More Features</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Features End -->

        <!-- Gallery Start -->
        <div class="container-fluid gallery overflow-hidden py-5">
            <div class="container py-5">
                <div class="section-title text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="sub-style">
                        <h5 class="sub-title text-primary px-3">OUR GALLERY</h5>
                    </div>
                    <h1 class="display-5 mb-4">Capturing Moments of Success</h1>
                    <p class="mb-0">Take a glimpse into the success stories and memorable moments of our clients as they embark on their travel journeys with M25 Travel & Tour Agency.</p>
                </div>
                <div class="row g-4">
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="gallery-item">
                            <div class="gallery-img">
                                <img src="img/gallery-1.jpg" class="img-fluid w-100 rounded" alt="Gallery Image">
                                <div class="gallery-overlay">
                                    <div class="gallery-content">
                                        <a href="img/gallery-1.jpg" data-lightbox="gallery" class="btn btn-light btn-lg-square rounded-circle">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="gallery-item">
                            <div class="gallery-img">
                                <img src="img/gallery-2.jpg" class="img-fluid w-100 rounded" alt="Gallery Image">
                                <div class="gallery-overlay">
                                    <div class="gallery-content">
                                        <a href="img/gallery-2.jpg" data-lightbox="gallery" class="btn btn-light btn-lg-square rounded-circle">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.5s">
                        <div class="gallery-item">
                            <div class="gallery-img">
                                <img src="img/gallery-3.jpg" class="img-fluid w-100 rounded" alt="Gallery Image">
                                <div class="gallery-overlay">
                                    <div class="gallery-content">
                                        <a href="img/gallery-3.jpg" data-lightbox="gallery" class="btn btn-light btn-lg-square rounded-circle">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.7s">
                        <div class="gallery-item">
                            <div class="gallery-img">
                                <img src="img/gallery-4.jpg" class="img-fluid w-100 rounded" alt="Gallery Image">
                                <div class="gallery-overlay">
                                    <div class="gallery-content">
                                        <a href="img/gallery-4.jpg" data-lightbox="gallery" class="btn btn-light btn-lg-square rounded-circle">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="0.9s">
                        <div class="gallery-item">
                            <div class="gallery-img">
                                <img src="img/gallery-5.jpg" class="img-fluid w-100 rounded" alt="Gallery Image">
                                <div class="gallery-overlay">
                                    <div class="gallery-content">
                                        <a href="img/gallery-5.jpg" data-lightbox="gallery" class="btn btn-light btn-lg-square rounded-circle">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 wow fadeInUp" data-wow-delay="1.1s">
                        <div class="gallery-item">
                            <div class="gallery-img">
                                <img src="img/gallery-6.jpg" class="img-fluid w-100 rounded" alt="Gallery Image">
                                <div class="gallery-overlay">
                                    <div class="gallery-content">
                                        <a href="img/gallery-6.jpg" data-lightbox="gallery" class="btn btn-light btn-lg-square rounded-circle">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Gallery End -->

        <!-- Countries We Offer Start -->
        <div class="container-fluid country overflow-hidden py-5">
            <div class="container">
                <div class="section-title text-center wow fadeInUp" data-wow-delay="0.1s" style="margin-bottom: 70px;">
                    <div class="sub-style">
                        <h5 class="sub-title text-primary px-3">COUNTRIES WE OFFER</h5>
                    </div>
                    <h1 class="display-5 mb-4">Immigration & visa services following Countries</h1>
                    <p class="mb-0">Comprehensive case evaluation Guidance on the necessary documentation Timely follow-ups with relevant authorities Application form completion and submission Online visa application appointments</p>
                </div>
                <div class="row g-4 text-center">
                    <div class="col-lg-6 col-xl-3 mb-5 mb-xl-0 wow fadeInUp" data-wow-delay="0.1s">
                        <div class="country-item">
                            <div class="rounded overflow-hidden">
                                <img src="img/country-1.jpg" class="img-fluid w-100 rounded" alt="Image">
                            </div>
                            <div class="country-flag">
                                <img src="img/brazil.jpg" class="img-fluid rounded-circle" alt="Image">
                            </div>
                            <div class="country-name">
                                <a href="countries" class="text-white fs-4">Brazil</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-3 mb-5 mb-xl-0 wow fadeInUp" data-wow-delay="0.3s">
                        <div class="country-item">
                            <div class="rounded overflow-hidden">
                                <img src="img/country-2.jpg" class="img-fluid w-100 rounded" alt="Image">
                            </div>
                            <div class="country-flag">
                                <img src="img/india.jpg" class="img-fluid rounded-circle" alt="Image">
                            </div>
                            <div class="country-name">
                                <a href="countries" class="text-white fs-4">India</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-3 mb-5 mb-xl-0 wow fadeInUp" data-wow-delay="0.5s">
                        <div class="country-item">
                            <div class="rounded overflow-hidden">
                                <img src="img/country-3.jpg" class="img-fluid w-100 rounded" alt="Image">
                            </div>
                            <div class="country-flag">
                                <img src="img/usa.jpg" class="img-fluid rounded-circle" alt="Image">
                            </div>
                            <div class="country-name">
                                <a href="countries" class="text-white fs-4">United States</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 col-xl-3 mb-5 mb-xl-0 wow fadeInUp" data-wow-delay="0.7s">
                        <div class="country-item">
                            <div class="rounded overflow-hidden">
                                <img src="img/country-4.jpg" class="img-fluid w-100 rounded" alt="Image">
                            </div>
                            <div class="country-flag">
                                <img src="img/italy.jpg" class="img-fluid rounded-circle" alt="Image">
                            </div>
                            <div class="country-name">
                                <a href="countries" class="text-white fs-4">Italy</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <a class="btn btn-primary border-secondary rounded-pill py-3 px-5 wow fadeInUp" data-wow-delay="0.1s" href="countries">More Countries</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Countries We Offer End -->


        <!-- Testimonial Start -->
        <div class="container-fluid testimonial overflow-hidden pb-5">
            <div class="container py-5">
                <div class="section-title text-center mb-5 wow fadeInUp" data-wow-delay="0.1s">
                    <div class="sub-style">
                        <h5 class="sub-title text-primary px-3">OUR CLIENTS REVIEWS</h5>
                    </div>
                    <h1 class="display-5 mb-4">What Our Clients Say</h1>
                    <p class="mb-0">Read what our satisfied clients have to say about their experience with M25 Travel and Tour Agency.</p>
                </div>
                <div class="owl-carousel testimonial-carousel wow zoomInDown" data-wow-delay="0.2s">
                    <div class="testimonial-item">
                        <div class="testimonial-content p-4 mb-5">
                            <h6 class="text-primary mb-2">Excellent Visa Support Service!</h6>
                            <p class="fs-5 mb-0">I had been struggling with my UK student visa application until I came across M25 Travel and Tour Agency. Their team guided me through every step, reviewed my documents, and ensured everything was perfectly arranged. Within two weeks, my visa was approved without stress. I'll definitely recommend them to anyone looking for professional visa support.</p>
                            <div class="d-flex justify-content-end">
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                                <img class="img-fluid rounded-circle" src="https://images.unsplash.com/photo-1494790108755-2616b612b786?w=200&h=200&fit=crop&crop=face" alt="Sarah Johnson">
                            </div>
                            <div class="my-auto">
                                <h5>Sarah Johnson</h5>
                                <p class="mb-0">Student</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-content p-4 mb-5">
                            <h6 class="text-primary mb-2">Smooth Immigration Process</h6>
                            <p class="fs-5 mb-0">M25 Travel and Tour made my immigration journey so much easier. They handled my paperwork, provided updates, and were always available to answer my questions. What I loved most was their honesty and transparency. I can now start a new life abroad confidently, thanks to their excellent service.</p>
                            <div class="d-flex justify-content-end">
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                                <img class="img-fluid rounded-circle" src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&h=200&fit=crop&crop=face" alt="Michael Chen">
                            </div>
                            <div class="my-auto">
                                <h5>Michael Chen</h5>
                                <p class="mb-0">Engineer</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-content p-4 mb-5">
                            <h6 class="text-primary mb-2">Perfect Family Holiday Arrangement</h6>
                            <p class="fs-5 mb-0">Our family trip to Dubai was unforgettable, all thanks to M25 Travel and Tour Agency. From flight booking to hotel reservations and tour activities, everything was organised to perfection. They even helped us with travel insurance and airport transfers. We'll definitely book with them again.</p>
                            <div class="d-flex justify-content-end">
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                                <img class="img-fluid rounded-circle" src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=200&h=200&fit=crop&crop=face" alt="Emma Williams">
                            </div>
                            <div class="my-auto">
                                <h5>Emma Williams</h5>
                                <p class="mb-0">Marketing Manager</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-content p-4 mb-5">
                            <h6 class="text-primary mb-2">Reliable Diplomatic Assistance</h6>
                            <p class="fs-5 mb-0">As a business traveller, I needed help with my diplomatic travel documents. M25 handled it quickly and efficiently. Their attention to detail and respect for confidentiality was impressive. I truly appreciate how professional they were from start to finish.</p>
                            <div class="d-flex justify-content-end">
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                                <img class="img-fluid rounded-circle" src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&h=200&fit=crop&crop=face" alt="James Rodriguez">
                            </div>
                            <div class="my-auto">
                                <h5>James Rodriguez</h5>
                                <p class="mb-0">Business Executive</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-content p-4 mb-5">
                            <h6 class="text-primary mb-2">Professional and Trustworthy Agency</h6>
                            <p class="fs-5 mb-0">I've used many travel agencies before, but none compare to M25. They supported me through my Canadian visa application and made the entire experience stress-free. The team is professional, responsive, and genuinely cares about your success.</p>
                            <div class="d-flex justify-content-end">
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                                <img class="img-fluid rounded-circle" src="https://images.unsplash.com/photo-1544725176-7c40e5a71c5e?w=200&h=200&fit=crop&crop=face" alt="Aisha Patel">
                            </div>
                            <div class="my-auto">
                                <h5>Aisha Patel</h5>
                                <p class="mb-0">Software Developer</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-content p-4 mb-5">
                            <h6 class="text-primary mb-2">Affordable Holiday Packages</h6>
                            <p class="fs-5 mb-0">M25 Travel and Tour helped me plan my honeymoon trip to Zanzibar, and it was beyond perfect. The resort, itinerary, and activities were all well-chosen within my budget. It felt like a luxury experience without breaking the bank. Highly recommend!</p>
                            <div class="d-flex justify-content-end">
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                                <img class="img-fluid rounded-circle" src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&h=200&fit=crop&crop=face" alt="David Thompson">
                            </div>
                            <div class="my-auto">
                                <h5>David Thompson</h5>
                                <p class="mb-0">Teacher</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-content p-4 mb-5">
                            <h6 class="text-primary mb-2">Excellent Customer Service</h6>
                            <p class="fs-5 mb-0">What impressed me most about M25 Travel and Tour was their communication. They kept me updated every step of the way, from visa processing to flight booking. Even after my arrival, they checked in to ensure everything was fine. That personal touch made all the difference.</p>
                            <div class="d-flex justify-content-end">
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                                <img class="img-fluid rounded-circle" src="https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=200&h=200&fit=crop&crop=face" alt="Lisa Anderson">
                            </div>
                            <div class="my-auto">
                                <h5>Lisa Anderson</h5>
                                <p class="mb-0">Nurse</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-content p-4 mb-5">
                            <h6 class="text-primary mb-2">Fast and Reliable Visa Processing</h6>
                            <p class="fs-5 mb-0">I needed a Schengen visa urgently for a conference, and M25 delivered faster than I expected. They handled all the documentation and appointment scheduling perfectly. Their professionalism and reliability saved me time and stress.</p>
                            <div class="d-flex justify-content-end">
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                                <img class="img-fluid rounded-circle" src="https://images.unsplash.com/photo-1519345182560-3f2917c472ef?w=200&h=200&fit=crop&crop=face" alt="Robert Kim">
                            </div>
                            <div class="my-auto">
                                <h5>Robert Kim</h5>
                                <p class="mb-0">Consultant</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-content p-4 mb-5">
                            <h6 class="text-primary mb-2">Best Travel Experience Ever</h6>
                            <p class="fs-5 mb-0">I joined one of M25's group tours to Europe, and it was the best decision I made this year. The itinerary was well-organised, the tour guide was amazing, and the overall experience was smooth from start to finish. I'm already planning my next trip with them.</p>
                            <div class="d-flex justify-content-end">
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                                <img class="img-fluid rounded-circle" src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&h=200&fit=crop&crop=face" alt="Grace Okonkwo">
                            </div>
                            <div class="my-auto">
                                <h5>Grace Okonkwo</h5>
                                <p class="mb-0">Accountant</p>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial-item">
                        <div class="testimonial-content p-4 mb-5">
                            <h6 class="text-primary mb-2">Outstanding Immigration and Diplomatic Service</h6>
                            <p class="fs-5 mb-0">M25 Travel and Tour helped me secure my relocation documents and guided me through the diplomatic process with ease. Their staff were polite, knowledgeable, and always available. They made what could have been a stressful process feel effortless.</p>
                            <div class="d-flex justify-content-end">
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                                <i class="fas fa-star text-secondary"></i>
                            </div>
                        </div>
                        <div class="d-flex">
                            <div class="rounded-circle me-4" style="width: 100px; height: 100px;">
                                <img class="img-fluid rounded-circle" src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=200&h=200&fit=crop&crop=face" alt="Samuel Mensah">
                            </div>
                            <div class="my-auto">
                                <h5>Samuel Mensah</h5>
                                <p class="mb-0">Doctor</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Testimonial End -->



        <!-- Training Start -->
        <!-- Training End -->


        <!-- Contact Start -->
        <!-- Contact End -->


        <!-- Footer Start -->
        <div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
            <div class="container py-5">
                <div class="row g-5">
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="footer-item d-flex flex-column">
                            <h4 class="text-secondary mb-4">Contact Info</h4>
                            <a href="#"><i class="fa fa-map-marker-alt me-2"></i> Winneba Street -Mallam Weija, Gbawe, Greater Accra, GS03301053</a>
                            <a href="mailto:info@m25travelagency.com"><i class="fas fa-envelope me-2"></i> info@m25travelagency.com</a>
                            <a href="tel:+233592605752"><i class="fas fa-phone me-2"></i> +233 59 260 5752</a>
                            <a href="tel:+233592605752" class="mb-3"><i class="fas fa-print me-2"></i> +233 59 260 5752</a>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-share fa-2x text-secondary me-2"></i>
                                <a class="btn mx-1" href=""><i class="fab fa-facebook-f"></i></a>
                                <a class="btn mx-1" href=""><i class="fab fa-twitter"></i></a>
                                <a class="btn mx-1" href=""><i class="fab fa-instagram"></i></a>
                                <a class="btn mx-1" href=""><i class="fab fa-linkedin-in"></i></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="footer-item d-flex flex-column">
                            <h4 class="text-secondary mb-4">Opening Time</h4>
                            <div class="mb-3">
                                <h6 class="text-muted mb-0">Mon - Friday:</h6>
                                <p class="text-white mb-0">24 Hours Working</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-muted mb-0">Saturday:</h6>
                                <p class="text-white mb-0">24 Hours Working</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-muted mb-0">Vacation:</h6>
                                <p class="text-white mb-0">24 Hours Working</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="footer-item d-flex flex-column">
                            <h4 class="text-secondary mb-4">Our Services</h4>
                            <a href="#" class=""><i class="fas fa-angle-right me-2"></i> Business</a>
                            <a href="#" class=""><i class="fas fa-angle-right me-2"></i> Evaluation</a>
                            <a href="#" class=""><i class="fas fa-angle-right me-2"></i> Migrate</a>
                            <a href="#" class=""><i class="fas fa-angle-right me-2"></i> Study</a>
                            <a href="#" class=""><i class="fas fa-angle-right me-2"></i> Counselling</a>
                            <a href="#" class=""><i class="fas fa-angle-right me-2"></i> Work / Career</a>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="footer-item">
                            <h4 class="text-secondary mb-4">Newsletter</h4>
                            <p class="text-white mb-3">Ready to embark on your next journey? Contact M25 Travel Agency, where we transform your travel dreams into reality. </p>
                            <div class="position-relative mx-auto rounded-pill">
                                <input class="form-control border-0 rounded-pill w-100 py-3 ps-4 pe-5" type="text" placeholder="Enter your email">
                                <button type="button" class="btn btn-primary rounded-pill position-absolute top-0 end-0 py-2 mt-2 me-2">SignUp</button>
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
                        <span class="text-white"><a href="#" class="border-bottom text-white"><i class="fas fa-copyright text-light me-2"></i>M25 Travel Agency</a>, All right reserved.</span>
                    </div>
                    <div class="col-md-6 text-center text-md-end text-white">
                        Designed By <a class="border-bottom text-white" href="wa.me/2348121855275">Esaint Mjay</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Copyright End -->


        <!-- Back to Top -->
        <a href="#" class="btn btn-primary btn-lg-square back-to-top"><i class="fa fa-arrow-up"></i></a>   

        
    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="lib/wow/wow.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    
    <!-- Lightbox JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    
    <!-- Translation and Popup Form Script -->
    <script>
        // Translation function
        function translatePage(lang) {
            // Show feedback to user
            showTranslationFeedback(lang);
            
            // Function to attempt translation
            function attemptTranslation() {
                var selectField = document.querySelector("select.goog-te-combo");
                if (selectField) {
                    selectField.value = lang;
                    selectField.dispatchEvent(new Event('change'));
                    return true;
                }
                return false;
            }
            
            // Try immediate translation
            if (attemptTranslation()) {
                return;
            }
            
            // If Google Translate isn't ready, wait and try again
            var attempts = 0;
            var maxAttempts = 20; // Try for up to 10 seconds
            
            var interval = setInterval(function() {
                attempts++;
                
                if (attemptTranslation()) {
                    clearInterval(interval);
                    return;
                }
                
                if (attempts >= maxAttempts) {
                    clearInterval(interval);
                    console.log('Google Translate not ready. Please try again in a moment.');
                    
                    // Fallback: Try to trigger Google Translate initialization
                    if (typeof google !== 'undefined' && google.translate) {
                        google.translate.TranslateElement({
                            pageLanguage: 'en',
                            includedLanguages: 'en,fr,es,de,it,pt,ar,zh,hi,ja,ko,ru,nl,sv,no,da',
                            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
                            autoDisplay: false
                        }, 'google_translate_element');
                        
                        // Try again after re-initialization
                        setTimeout(function() {
                            attemptTranslation();
                        }, 2000);
                    }
                }
            }, 500);
        }

        // Add visual feedback for translation
        function showTranslationFeedback(language) {
            // Create a temporary notification
            var notification = document.createElement('div');
            notification.innerHTML = '<i class="fas fa-language me-2"></i>Translating to ' + getLanguageName(language) + '...';
            notification.style.cssText = 'position:fixed;top:20px;right:20px;background:#28a745;color:white;padding:10px 20px;border-radius:5px;z-index:9999;font-size:14px;';
            document.body.appendChild(notification);
            
            setTimeout(function() {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }
        
        function getLanguageName(code) {
            var languages = {
                'en': 'English',
                'fr': 'French',
                'es': 'Spanish', 
                'de': 'German',
                'it': 'Italian',
                'pt': 'Portuguese',
                'ar': 'Arabic',
                'zh': 'Chinese'
            };
            return languages[code] || code.toUpperCase();
        }

        // Share website function
        function shareWebsite() {
            if (navigator.share) {
                navigator.share({
                    title: 'M25 Travel Agency - Visa & Immigration Services',
                    text: 'Your trusted partner for visa and immigration services. Get professional assistance for your travel needs.',
                    url: window.location.href
                }).then(() => {
                    console.log('Thanks for sharing!');
                }).catch(console.error);
            } else {
                // Fallback for browsers that don't support Web Share API
                const shareData = {
                    title: 'M25 Travel Agency - Visa & Immigration Services',
                    text: 'Your trusted partner for visa and immigration services. Get professional assistance for your travel needs.',
                    url: window.location.href
                };
                
                // Create share modal
                const shareModal = `
                    <div class="modal fade" id="shareModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Share M25 Travel Agency</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <p class="mb-4">Share this website with your friends and family</p>
                                    <div class="d-flex justify-content-center gap-3">
                                        <a href="https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareData.url)}" target="_blank" class="btn btn-primary">
                                            <i class="fab fa-facebook-f me-2"></i>Facebook
                                        </a>
                                        <a href="https://twitter.com/intent/tweet?text=${encodeURIComponent(shareData.text)}&url=${encodeURIComponent(shareData.url)}" target="_blank" class="btn btn-info">
                                            <i class="fab fa-twitter me-2"></i>Twitter
                                        </a>
                                        <a href="https://wa.me/?text=${encodeURIComponent(shareData.text + ' ' + shareData.url)}" target="_blank" class="btn btn-success">
                                            <i class="fab fa-whatsapp me-2"></i>WhatsApp
                                        </a>
                                        <button onclick="copyToClipboard('${shareData.url}')" class="btn btn-secondary">
                                            <i class="fas fa-copy me-2"></i>Copy Link
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove existing modal if any
                const existingModal = document.getElementById('shareModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Add modal to body
                document.body.insertAdjacentHTML('beforeend', shareModal);
                
                // Show modal
                const modal = new bootstrap.Modal(document.getElementById('shareModal'));
                modal.show();
            }
        }

        // Copy to clipboard function
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('Link copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }

        // Show popup after 3 seconds
        setTimeout(function() {
            // Check if user hasn't already seen the popup in this session
            if (!sessionStorage.getItem('popupShown')) {
                var popupModal = new bootstrap.Modal(document.getElementById('popupModal'));
                popupModal.show();
                sessionStorage.setItem('popupShown', 'true');
            }
        }, 3000);

        // Handle popup form submission
        document.getElementById('popupForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const name = formData.get('name');
            const email = formData.get('email');
            const country = formData.get('country');
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Sending...';
            submitBtn.disabled = true;
            
            // Send data via AJAX
            fetch('process-popup-form.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                if (data.success) {
                    // Show success message
                    alert(data.message);
                    
                    // Close modal
                    var popupModal = bootstrap.Modal.getInstance(document.getElementById('popupModal'));
                    popupModal.hide();
                    
                    // Optional: Redirect to registration form
                    setTimeout(function() {
                        window.location.href = 'client-registration';
                    }, 1000);
                } else {
                    // Show error message but still close modal (user experience)
                    alert(data.message);
                    
                    // Close modal
                    var popupModal = bootstrap.Modal.getInstance(document.getElementById('popupModal'));
                    popupModal.hide();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                
                // Reset button
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                // Show fallback message
                alert('Thank you ' + name + '! We have received your information and will contact you soon about your travel plans to ' + country + '.');
                
                // Close modal
                var popupModal = bootstrap.Modal.getInstance(document.getElementById('popupModal'));
                popupModal.hide();
                
                // Optional: Redirect to registration form
                setTimeout(function() {
                    window.location.href = 'client-registration';
                }, 1000);
            });
        });
    </script>
    </body>

</html>