<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include necessary files
require_once 'config.php';
require_once 'includes/CountryAPI.php';

// Initialize API
$countryAPI = new CountryAPI($db ?? null);

// Create cache table if it doesn't exist
if (isset($db)) {
    $countryAPI->createCacheTable();
}

// Get all countries
$countries = $countryAPI->getAllCountries();

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 12;
$offset = ($page - 1) * $perPage;
$totalCountries = count($countries);
$totalPages = ceil($totalCountries / $perPage);
$paginatedCountries = array_slice($countries, $offset, $perPage);
?>
<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="utf-8">
        <title>Countries - M25 Travel & Tour Agency</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">
        <meta content="travel, visa, countries, immigration, tourism" name="keywords">
        <meta content="Explore countries worldwide with M25 Travel & Tour Agency. Get visa services and travel information for destinations around the globe." name="description">

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
    </head>

    <body>

        <!-- Spinner Start -->
        <div id="spinner" class="show bg-white position-fixed translate-middle w-100 vh-100 top-50 start-50 d-flex align-items-center justify-content-center">
            <div class="spinner-border text-secondary" style="width: 3rem; height: 3rem;" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
        <!-- Spinner End -->


        <!-- Topbar Start -->
        <div class="container-fluid bg-primary px-5 d-none d-lg-block">
            <div class="row gx-0 align-items-center">
                <div class="col-lg-5 text-center text-lg-start mb-lg-0">
                    <div class="d-flex">
                        <a href="#" class="text-muted me-4"><i class="fas fa-envelope text-secondary me-2"></i>info@m25travelagency.com</a>
                        <a href="#" class="text-muted me-0"><i class="fas fa-phone-alt text-secondary me-2"></i>+233 59 260 5752</a>
                    </div>
                </div>
                <div class="col-lg-3 row-cols-1 text-center mb-2 mb-lg-0">
                    <div class="d-inline-flex align-items-center" style="height: 45px;">
                        <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2" href=" https://x.com/M25And42551"><i class="fab fa-twitter fw-normal text-secondary"></i></a>
                        <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2" href="https://www.facebook.com/profile.php?id=61560365438024"><i class="fab fa-facebook-f fw-normal text-secondary"></i></a>
                        <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2" href="https://www.instagram.com/mtravelandtouragency/"><i class="fab fa-instagram-in fw-normal text-secondary"></i></a>
                        <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2" href=" https://www.tiktok.com/@m25travelandtour"><i class="fab fa-tiktok fw-normal text-secondary"></i></a>
                        <a class="btn btn-sm btn-outline-light btn-square rounded-circle" href="https://www.youtube.com/@M25TravelandTourAgency"><i class="fab fa-youtube fw-normal text-secondary"></i></a>
                    </div>
                </div>
                <div class="col-lg-4 text-center text-lg-end">
                    <div class="d-inline-flex align-items-center" style="height: 45px;">
                        <a href="#" class="text-muted me-2"> Help</a><small> / </small>
                        <a href="#" class="text-muted mx-2"> Support</a><small> / </small>
                        <a href="#" class="text-muted ms-2"> Contact</a>
                    </div>
                </div>
            </div>
        </div>
        <!-- Topbar End -->

        <!-- Navbar & Hero Start -->
        <div class="container-fluid nav-bar p-0">
            <nav class="navbar navbar-expand-lg navbar-light bg-white px-4 px-lg-5 py-3 py-lg-0">
                <a href="" class="navbar-brand p-0">
                    <h4 class="text-secondary m-0"><img src="img/brand-logo.png" class="img-fluid" alt="">M25 Travel and Tours Agency</h4>
                    <!-- <img src="img/logo.png" alt="Logo"> -->
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                    <span class="fa fa-bars"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarCollapse">
                    <div class="navbar-nav ms-auto py-0">
                        <a href="index.php" class="nav-item nav-link">Home</a>
                        <a href="about.php" class="nav-item nav-link">About</a>
                        <a href="service.php" class="nav-item nav-link">Service</a>
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link active" data-bs-toggle="dropdown"><span class="dropdown-toggle">Pages</span></a>
                            <div class="dropdown-menu m-0">
                                <a href="feature" class="dropdown-item">Feature</a>
                                <a href="countries" class="dropdown-item active">Countries</a>
                                <a href="testimonial" class="dropdown-item">Testimonial</a>
                                <a href="training" class="dropdown-item">Training</a>
                                <a href="404" class="dropdown-item">404 Page</a>
                            </div>
                        </div>
                        <a href="contact" class="nav-item nav-link">Contact</a>
                    </div>
                    <button class="btn btn-primary btn-md-square border-secondary mb-3 mb-md-3 mb-lg-0 me-3" data-bs-toggle="modal" data-bs-target="#searchModal"><i class="fas fa-search"></i></button>
                    <a href="client-registration" class="btn btn-primary border-secondary rounded-pill py-2 px-4 px-lg-3 mb-3 mb-md-3 mb-lg-0">Get A Form</a>
                </div>
            </nav>
        </div>
        <!-- Navbar & Hero End -->

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

        <!-- Header Start -->
        <div class="container-fluid bg-breadcrumb">
            <div class="container text-center py-5" style="max-width: 900px;">
                <h3 class="text-white display-3 mb-4 wow fadeInDown" data-wow-delay="0.1s">Explore Countries Worldwide</h3>
                <p class="fs-5 text-white mb-4">Discover amazing destinations and get visa services for countries around the globe</p>
                <ol class="breadcrumb justify-content-center text-white mb-0 wow fadeInDown" data-wow-delay="0.3s">
                    <li class="breadcrumb-item"><a href="index.php" class="text-white">Home</a></li>
                    <li class="breadcrumb-item"><a href="#" class="text-white">Pages</a></li>
                    <li class="breadcrumb-item active text-secondary">Countries</li>
                </ol>    
            </div>
        </div>
        <!-- Header End -->

        <!-- Search and Filter Section -->
        <div class="container-fluid py-4 bg-light">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 mx-auto">
                        <div class="input-group">
                            <input type="text" id="countrySearch" class="form-control form-control-lg" placeholder="Search countries...">
                            <button class="btn btn-primary" type="button" id="searchBtn">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-region="all">All Regions</button>
                            <button type="button" class="btn btn-outline-primary" data-region="Africa">Africa</button>
                            <button type="button" class="btn btn-outline-primary" data-region="Americas">Americas</button>
                            <button type="button" class="btn btn-outline-primary" data-region="Asia">Asia</button>
                            <button type="button" class="btn btn-outline-primary" data-region="Europe">Europe</button>
                            <button type="button" class="btn btn-outline-primary" data-region="Oceania">Oceania</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Countries Grid Start -->
        <div class="container-fluid country overflow-hidden py-5">
            <div class="container py-5">
                <div class="section-title text-center wow fadeInUp" data-wow-delay="0.1s" style="margin-bottom: 70px;">
                    <div class="sub-style">
                        <h5 class="sub-title text-primary px-3">COUNTRIES WORLDWIDE</h5>
                    </div>
                    <h1 class="display-5 mb-4">Explore <?php echo number_format($totalCountries); ?> Countries & Their Attractions</h1>
                    <p class="mb-0">Discover amazing destinations, learn about different cultures, and get comprehensive visa services for countries around the globe.</p>
                </div>
                
                <?php if (!empty($paginatedCountries)): ?>
                <!-- Debug: Show first country data structure (remove this after testing) -->
                <?php if (isset($_GET['debug']) && $_GET['debug'] == '1' && !empty($paginatedCountries)): ?>
                <div class="alert alert-info">
                    <h5>Debug - First Country Data:</h5>
                    <pre><?php print_r(array_slice($paginatedCountries[0], 0, 10)); ?></pre>
                </div>
                <?php endif; ?>
                
                <div class="row g-4 text-center" id="countriesGrid">
                    <?php 
                    $delay = 0.1;
                    foreach ($paginatedCountries as $country): 
                        $countryName = $country['name']['common'];
                        $countryCode = strtolower($country['cca2']);
                        // Use reliable flag sources - prioritize FlagCDN for better loading
                        $countryCode2 = strtolower($country['cca2']);
                        $flag = 'https://flagcdn.com/w320/' . $countryCode2 . '.png';
                        $capital = isset($country['capital']) && is_array($country['capital']) && count($country['capital']) > 0 ? $country['capital'][0] : 'N/A';
                        $population = isset($country['population']) ? number_format($country['population']) : 'N/A';
                        $region = $country['region'] ?? 'Unknown';
                    ?>
                    <div class="col-lg-6 col-xl-3 mb-4 country-card" data-region="<?php echo $region; ?>" data-name="<?php echo strtolower($countryName); ?>" data-wow-delay="<?php echo $delay; ?>s">
                        <div class="country-item h-100">
                            <div class="country-content">
                                <div class="country-flag-large mb-3">
                                    <img src="<?php echo htmlspecialchars($flag); ?>" 
                                         class="img-fluid country-flag" 
                                         alt="<?php echo htmlspecialchars($countryName); ?> Flag" 
                                         style="width: 80px; height: 60px; object-fit: cover; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"
                                         onerror="this.onerror=null; this.src='https://flagcdn.com/w320/<?php echo strtolower($country['cca2']); ?>.png';"
                                         loading="lazy">
                                </div>
                                <h4 class="country-title mb-3"><?php echo htmlspecialchars($countryName); ?></h4>
                                <div class="country-info">
                                    <p class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i><strong>Capital:</strong> <?php echo htmlspecialchars($capital); ?></p>
                                    <p class="mb-2"><i class="fas fa-users text-primary me-2"></i><strong>Population:</strong> <?php echo $population; ?></p>
                                    <p class="mb-3"><i class="fas fa-globe text-primary me-2"></i><strong>Region:</strong> <?php echo htmlspecialchars($region); ?></p>
                                </div>
                                <div class="country-actions">
                                    <a href="country-detail.php?code=<?php echo $countryCode; ?>" class="btn btn-primary btn-sm me-2">
                                        <i class="fas fa-eye me-1"></i>Explore
                                    </a>
                                    <a href="visa-details.php" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-passport me-1"></i>Visa Info
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php 
                    $delay += 0.1;
                    if ($delay > 0.8) $delay = 0.1;
                    endforeach; 
                    ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="row">
                    <div class="col-12">
                        <nav aria-label="Countries pagination">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php else: ?>
                <div class="row">
                    <div class="col-12 text-center">
                        <div class="alert alert-warning">
                            <h4>Unable to load countries</h4>
                            <p>We're having trouble connecting to our country database. Please try again later.</p>
                            <a href="?" class="btn btn-primary">Retry</a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <!-- Countries Grid End -->

        <!-- Counter Facts Start -->
        <div class="container-fluid counter-facts overflow-hidden py-5">
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

        <!-- Training Start -->
        
        <!-- Training End -->

        <!-- Footer Start -->
        <div class="container-fluid footer py-5 wow fadeIn" data-wow-delay="0.2s">
            <div class="container py-5">
                <div class="row g-5">
                    <div class="col-md-6 col-lg-6 col-xl-3">
                        <div class="footer-item d-flex flex-column">
                            <h4 class="text-secondary mb-4">Contact Info</h4>
                            <a href=""><i class="fa fa-map-marker-alt me-2"></i> Winneba Street -Mallam Weija, Gbawe, Greater Accra, GS03301053</a>
                            <a href=""><i class="fas fa-envelope me-2"></i> info@m25travelagency.com</a>
                            <a href=""><i class="fas fa-phone me-2"></i> +233 59 260 5752</a>
                            <a href="" class="mb-3"><i class="fas fa-print me-2"></i> +233 59 260 5752</a>
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
                                <p class="text-white mb-0">24/7</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-muted mb-0">Satday:</h6>
                                <p class="text-white mb-0">24/7</p>
                            </div>
                            <div class="mb-3">
                                <h6 class="text-muted mb-0">Vacation:</h6>
                                <p class="text-white mb-0">24/7</p>
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
                            <p class="text-white mb-3">Dolor amet sit justo amet elitr clita ipsum elitr est.Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
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
                        <span class="text-white"><a href="#" class="border-bottom text-white"><i class="fas fa-copyright text-light me-2"></i>Your Site Name</a>, All right reserved.</span>
                    </div>
                    <div class="col-md-6 text-center text-md-end text-white">
                        <!--/*** This template is free as long as you keep the below author’s credit link/attribution link/backlink. ***/-->
                        <!--/*** If you'd like to use the template without the below author’s credit link/attribution link/backlink, ***/-->
                        <!--/*** you can purchase the Credit Removal License from "https://htmlcodex.com/credit-removal". ***/-->
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
    

    <!-- Template Javascript -->
    <script src="js/main.js"></script>
    
    <!-- Countries Page Custom Scripts -->
    <script>
    $(document).ready(function() {
        // Search functionality
        $('#countrySearch').on('keyup', function() {
            filterCountries();
        });
        
        $('#searchBtn').on('click', function() {
            filterCountries();
        });
        
        // Region filter
        $('[data-region]').on('click', function() {
            $('[data-region]').removeClass('active');
            $(this).addClass('active');
            filterCountries();
        });
        
        function filterCountries() {
            const searchTerm = $('#countrySearch').val().toLowerCase();
            const selectedRegion = $('[data-region].active').data('region');
            
            $('.country-card').each(function() {
                const countryName = $(this).data('name');
                const countryRegion = $(this).data('region');
                
                const matchesSearch = countryName.includes(searchTerm);
                const matchesRegion = selectedRegion === 'all' || countryRegion === selectedRegion;
                
                if (matchesSearch && matchesRegion) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
        
        // Add loading state for country links
        $('.country-item a').on('click', function() {
            $(this).html('<i class="fas fa-spinner fa-spin me-1"></i>Loading...');
        });
        
        // Enhanced image error handling
        $('.country-flag').on('error', function() {
            const countryCode = $(this).closest('.country-card').find('a[href*="country-detail"]').attr('href').split('code=')[1];
            if (countryCode && !this.src.includes('flagcdn.com')) {
                this.src = 'https://flagcdn.com/w320/' + countryCode.toLowerCase() + '.png';
            } else if (countryCode) {
                // Final fallback - create a text-based flag
                $(this).replaceWith('<div class="country-flag-fallback" style="width: 80px; height: 60px; background: linear-gradient(135deg, #13357B, #1e4a8c); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.9rem; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">' + countryCode.toUpperCase() + '</div>');
            }
        });
    });
    </script>
    
    <style>
    .country-item {
        background: white;
        border-radius: 15px;
        padding: 30px 20px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }
    
    .country-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        border-color: #13357B;
    }
    
    .country-title {
        color: #13357B;
        font-weight: 600;
        font-size: 1.3rem;
    }
    
    .country-info {
        text-align: left;
        margin: 20px 0;
    }
    
    .country-info p {
        margin-bottom: 8px;
        color: #666;
        font-size: 0.9rem;
    }
    
    .country-actions {
        margin-top: 20px;
        position: relative;
        z-index: 10;
    }
    
    .country-actions .btn {
        position: relative;
        z-index: 11;
        pointer-events: auto;
    }
    
    .btn-group .btn {
        border-radius: 25px;
        margin: 0 5px;
    }
    
    .pagination .page-link {
        border-radius: 25px;
        margin: 0 2px;
        border: 1px solid #13357B;
        color: #13357B;
    }
    
    .pagination .page-item.active .page-link {
        background-color: #13357B;
        border-color: #13357B;
    }
    
    .country-card {
        transition: all 0.3s ease;
    }
    
    .country-flag {
        background: #f8f9fa;
        border: 1px solid #e9ecef;
    }
    
    .country-flag:not([src]), .country-flag[src=""] {
        background: linear-gradient(135deg, #13357B, #1e4a8c);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 0.8rem;
    }
    
    @media (max-width: 768px) {
        .country-item {
            padding: 20px 15px;
        }
        
        .btn-group {
            flex-wrap: wrap;
        }
        
        .btn-group .btn {
            margin: 2px;
            font-size: 0.8rem;
        }
    }
    </style>
    </body>

</html>