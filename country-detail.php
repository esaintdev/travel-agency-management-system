<?php
// Include necessary files
require_once 'config.php';
require_once 'includes/CountryAPI.php';

// Get country code from URL
$countryCode = isset($_GET['code']) ? strtoupper($_GET['code']) : null;

if (!$countryCode) {
    header('Location: countries.php');
    exit;
}

// Initialize API
$countryAPI = new CountryAPI($db ?? null);

// Get country details
$country = $countryAPI->getCountryDetails($countryCode);

if (!$country) {
    header('Location: countries.php?error=country_not_found');
    exit;
}

// Extract country information
$countryName = $country['name']['common'];
$officialName = $country['name']['official'] ?? $countryName;
$capital = isset($country['capital']) ? implode(', ', $country['capital']) : 'N/A';
$population = isset($country['population']) ? number_format($country['population']) : 'N/A';
$region = $country['region'] ?? 'Unknown';
$subregion = $country['subregion'] ?? 'Unknown';
$area = isset($country['area']) ? number_format($country['area']) . ' km²' : 'N/A';
$languages = isset($country['languages']) ? implode(', ', $country['languages']) : 'N/A';
$currencies = isset($country['currencies']) ? implode(', ', array_column($country['currencies'], 'name')) : 'N/A';
$flag = $country['flags']['png'] ?? 'https://via.placeholder.com/300x200?text=Flag';
$coatOfArms = $country['coatOfArms']['png'] ?? null;
$maps = $country['maps']['googleMaps'] ?? '#';
$description = $country['description'] ?? "Discover the beauty and culture of {$countryName}.";
$attractions = $country['attractions'] ?? [];
$images = $country['images'] ?? [];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title><?php echo htmlspecialchars($countryName); ?> - Country Details | M25 Travel & Tour Agency</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="<?php echo htmlspecialchars($countryName); ?>, travel, tourism, visa, attractions" name="keywords">
    <meta content="Explore <?php echo htmlspecialchars($countryName); ?> with M25 Travel & Tour Agency. Discover tourist attractions, cultural information, and visa services." name="description">

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
        .country-hero {
            background: linear-gradient(135deg, rgba(19, 53, 123, 0.9), rgba(30, 74, 140, 0.9));
            padding: 100px 0;
            position: relative;
            overflow: hidden;
        }
        
        .country-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('<?php echo $flag; ?>') center/cover;
            opacity: 0.1;
            z-index: -1;
        }
        
        .country-flag-hero {
            width: 120px;
            height: 90px;
            object-fit: cover;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border: 3px solid white;
        }
        
        .info-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border-left: 5px solid #13357B;
            transition: transform 0.3s ease;
        }
        
        .info-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #13357B;
            display: block;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        
        .attraction-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            margin-bottom: 30px;
        }
        
        .attraction-card:hover {
            transform: translateY(-5px);
        }
        
        .image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .gallery-item {
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: scale(1.02);
        }
        
        .gallery-item img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .back-btn {
            background: rgba(255,255,255,0.2);
            border: 2px solid white;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: white;
            color: #13357B;
        }
        
        .cta-section {
            background: linear-gradient(135deg, #13357B, #1e4a8c);
            color: white;
            padding: 60px 0;
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
                        <a class="btn btn-sm btn-outline-light btn-square rounded-circle me-2" href="https://www.instagram.com/mtravelandtouragency/"><i class="fab fa-instagram-in fw-normal text-secondary"></i></a>
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
                <h1 class="text-primary"><i class="fas fa-search-dollar me-3"></i>M25 Travel and Tours Agency</h1>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarCollapse">
                <span class="fa fa-bars"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarCollapse">
                <div class="navbar-nav ms-auto py-0">
                    <a href="index.php" class="nav-item nav-link">Home</a>
                    <a href="about.php" class="nav-item nav-link">About</a>
                    <a href="visa-details.php" class="nav-item nav-link">Visa Details</a>
                    <a href="countries.php" class="nav-item nav-link active">Countries</a>
                    <a href="contact.php" class="nav-item nav-link">Contact</a>
                </div>
                <a href="client-registration.php" class="btn btn-primary rounded-pill py-2 px-4 my-3 my-lg-0 flex-shrink-0">Get Started</a>
            </div>
        </nav>

        <!-- Country Hero Section -->
        <div class="country-hero">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-8">
                        <div class="d-flex align-items-center mb-4">
                            <img src="<?php echo htmlspecialchars($flag); ?>" class="country-flag-hero me-4" alt="<?php echo htmlspecialchars($countryName); ?> Flag">
                            <div>
                                <h1 class="display-4 text-white mb-2"><?php echo htmlspecialchars($countryName); ?></h1>
                                <p class="text-white-50 mb-0"><?php echo htmlspecialchars($officialName); ?></p>
                            </div>
                        </div>
                        <p class="lead text-white mb-4"><?php echo htmlspecialchars($description); ?></p>
                        <a href="countries.php" class="back-btn me-3">
                            <i class="fas fa-arrow-left me-2"></i>Back to Countries
                        </a>
                        <a href="<?php echo htmlspecialchars($maps); ?>" target="_blank" class="back-btn">
                            <i class="fas fa-map-marked-alt me-2"></i>View on Map
                        </a>
                    </div>
                    <div class="col-lg-4 text-center">
                        <?php if ($coatOfArms): ?>
                        <img src="<?php echo htmlspecialchars($coatOfArms); ?>" class="img-fluid" style="max-width: 200px; opacity: 0.8;" alt="Coat of Arms">
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Navbar & Hero End -->

    <!-- Country Information Section -->
    <div class="container-fluid py-5">
        <div class="container">
            <!-- Quick Stats -->
            <div class="row mb-5">
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo htmlspecialchars($capital); ?></span>
                        <div class="stat-label">Capital City</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $population; ?></span>
                        <div class="stat-label">Population</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo $area; ?></span>
                        <div class="stat-label">Area</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-item">
                        <span class="stat-value"><?php echo htmlspecialchars($region); ?></span>
                        <div class="stat-label">Region</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Country Details -->
                <div class="col-lg-8">
                    <div class="info-card">
                        <h3 class="mb-4"><i class="fas fa-info-circle text-primary me-2"></i>Country Information</h3>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Official Name:</strong> <?php echo htmlspecialchars($officialName); ?></p>
                                <p><strong>Region:</strong> <?php echo htmlspecialchars($region); ?></p>
                                <p><strong>Subregion:</strong> <?php echo htmlspecialchars($subregion); ?></p>
                                <p><strong>Capital:</strong> <?php echo htmlspecialchars($capital); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Population:</strong> <?php echo $population; ?></p>
                                <p><strong>Area:</strong> <?php echo $area; ?></p>
                                <p><strong>Languages:</strong> <?php echo htmlspecialchars($languages); ?></p>
                                <p><strong>Currencies:</strong> <?php echo htmlspecialchars($currencies); ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Tourist Attractions -->
                    <div class="info-card">
                        <h3 class="mb-4"><i class="fas fa-map-marked-alt text-primary me-2"></i>Popular Destinations</h3>
                        <?php if (!empty($attractions)): ?>
                            <?php foreach ($attractions as $category => $items): ?>
                            <div class="mb-4">
                                <h5 class="text-primary"><?php echo htmlspecialchars($category); ?></h5>
                                <div class="row">
                                    <?php foreach ($items as $item): ?>
                                    <div class="col-md-6 mb-2">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                            <span><?php echo htmlspecialchars($item); ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                        <p class="text-muted">Tourist attraction information will be available soon. Contact us for detailed travel planning assistance.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Image Gallery -->
                    <?php if (!empty($images)): ?>
                    <div class="info-card">
                        <h3 class="mb-4"><i class="fas fa-images text-primary me-2"></i>Photo Gallery</h3>
                        <div class="image-gallery">
                            <?php foreach (array_slice($images, 0, 6) as $image): ?>
                            <div class="gallery-item">
                                <a href="<?php echo htmlspecialchars($image['urls']['regular']); ?>" data-lightbox="country-gallery">
                                    <img src="<?php echo htmlspecialchars($image['urls']['regular']); ?>" alt="<?php echo htmlspecialchars($image['alt_description'] ?? $countryName); ?>">
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Visa Services -->
                    <div class="info-card">
                        <h4 class="mb-3"><i class="fas fa-passport text-primary me-2"></i>Visa Services</h4>
                        <p class="mb-3">Planning to visit <?php echo htmlspecialchars($countryName); ?>? We provide comprehensive visa services to make your travel smooth and hassle-free.</p>
                        <div class="d-grid gap-2">
                            <a href="visa-details.php" class="btn btn-primary">
                                <i class="fas fa-file-alt me-2"></i>Visa Requirements
                            </a>
                            <a href="client-registration.php" class="btn btn-outline-primary">
                                <i class="fas fa-paper-plane me-2"></i>Apply Now
                            </a>
                        </div>
                    </div>

                    <!-- Travel Tips -->
                    <div class="info-card">
                        <h4 class="mb-3"><i class="fas fa-lightbulb text-primary me-2"></i>Travel Tips</h4>
                        <ul class="list-unstyled">
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Check visa requirements early</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Research local customs and culture</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Get travel insurance</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Learn basic local phrases</li>
                            <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Check weather conditions</li>
                        </ul>
                    </div>

                    <!-- Contact Info -->
                    <div class="info-card">
                        <h4 class="mb-3"><i class="fas fa-phone text-primary me-2"></i>Need Help?</h4>
                        <p class="mb-3">Our travel experts are here to help you plan your perfect trip to <?php echo htmlspecialchars($countryName); ?>.</p>
                        <div class="contact-info">
                            <p class="mb-2"><i class="fas fa-phone text-primary me-2"></i>+233 59 260 5752</p>
                            <p class="mb-2"><i class="fas fa-envelope text-primary me-2"></i>info@m25travelagency.com</p>
                            <a href="contact.php" class="btn btn-outline-primary btn-sm">Contact Us</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="cta-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <h2 class="mb-4">Ready to Explore <?php echo htmlspecialchars($countryName); ?>?</h2>
                    <p class="lead mb-4">Let M25 Travel & Tour Agency help you plan your perfect journey. From visa processing to travel arrangements, we've got you covered.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="client-registration.php" class="btn btn-light btn-lg rounded-pill px-5">
                            <i class="fas fa-paper-plane me-2"></i>Start Your Application
                        </a>
                        <a href="contact.php" class="btn btn-outline-light btn-lg rounded-pill px-5">
                            <i class="fas fa-phone me-2"></i>Get Consultation
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                        <a href="about.php"><i class="fas fa-angle-right me-2"></i> About</a>
                        <a href="visa-details.php"><i class="fas fa-angle-right me-2"></i> Visa Details</a>
                        <a href="countries.php"><i class="fas fa-angle-right me-2"></i> Countries</a>
                        <a href="contact.php"><i class="fas fa-angle-right me-2"></i> Contact</a>
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

    <!-- Back to Top -->
    <a href="#" class="btn btn-primary btn-lg-square back-to-top"><i class="fa fa-arrow-up"></i></a>

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

</body>

</html>
