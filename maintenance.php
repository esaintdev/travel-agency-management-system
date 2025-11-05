<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>M25 Travel Agency - Under Maintenance</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta content="M25 Travel Agency maintenance, visa services, immigration" name="keywords">
    <meta content="M25 Travel Agency is currently under maintenance. We'll be back soon with improved services." name="description">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom Maintenance Stylesheet -->
    <link href="css/maintenance.css" rel="stylesheet">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="img/favicon.ico">
</head>

<body>
    <div class="maintenance-container">
        <div class="maintenance-content">
            <!-- Logo Section -->
            <div class="logo-section text-center mb-5">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <img src="img/brand-logo.png" class="img-fluid me-3" alt="M25 Logo" style="max-height: 60px;">
                    <h1 class="brand-name mb-0">M25 Travel Agency</h1>
                </div>
                <p class="brand-tagline">Visa & Immigration Services</p>
            </div>
            
            <!-- Main Maintenance Message -->
            <div class="maintenance-message text-center">
                <div class="maintenance-icon mb-4">
                    <i class="fas fa-tools"></i>
                </div>
                
                <h2 class="maintenance-title mb-4">We're Currently Under Maintenance</h2>
                
                <p class="maintenance-description mb-5">
                    We're working hard to improve our services and will be back online shortly. 
                    Thank you for your patience while we make some exciting updates to serve you better.
                </p>
                
                <!-- Estimated Time -->
                <div class="estimated-time mb-5">
                    <div class="time-box">
                        <i class="fas fa-clock me-2"></i>
                        <span>Estimated completion: <strong id="estimated-time">2-4 hours</strong></span>
                    </div>
                </div>
                
                <!-- Contact Information -->
                <div class="contact-info">
                    <h4 class="contact-title mb-3">Need Immediate Assistance?</h4>
                    <div class="contact-methods">
                        <div class="contact-item">
                            <i class="fas fa-phone"></i>
                            <span>Emergency Hotline: <a href="tel:+233592605752">+233 59 260 5752</a></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-envelope"></i>
                            <span>Email: <a href="mailto:info@m25travelagency.com">info@m25travelagency.com</a></span>
                        </div>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Winneba Street - Mallam Weija, Gbawe, Greater Accra</span>
                        </div>
                    </div>
                </div>
                
                <!-- Social Media Links -->
                <div class="social-links mt-5">
                    <h5 class="social-title mb-3">Stay Connected</h5>
                    <div class="social-icons">
                        <a href="#" class="social-icon" aria-label="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-icon" aria-label="Twitter">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="social-icon" aria-label="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                        <a href="#" class="social-icon" aria-label="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="progress-section mt-5">
                    <h6 class="progress-title mb-2">Maintenance Progress</h6>
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: 65%" 
                             aria-valuenow="65" 
                             aria-valuemin="0" 
                             aria-valuemax="100">
                            65%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Background Animation -->
        <div class="background-animation">
            <div class="floating-icon" style="--delay: 0s; --duration: 6s;">
                <i class="fas fa-passport"></i>
            </div>
            <div class="floating-icon" style="--delay: 2s; --duration: 8s;">
                <i class="fas fa-plane"></i>
            </div>
            <div class="floating-icon" style="--delay: 4s; --duration: 7s;">
                <i class="fas fa-globe"></i>
            </div>
            <div class="floating-icon" style="--delay: 1s; --duration: 9s;">
                <i class="fas fa-suitcase"></i>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="maintenance-footer">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> M25 Travel Agency. All rights reserved.</p>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script>
        // Auto-refresh page every 30 minutes
        setTimeout(function() {
            location.reload();
        }, 1800000); // 30 minutes
        
        // Update estimated time dynamically (optional)
        function updateEstimatedTime() {
            const now = new Date();
            const hours = now.getHours();
            
            // Example logic to update estimated time based on current time
            if (hours >= 9 && hours <= 17) {
                document.getElementById('estimated-time').textContent = '1-2 hours';
            } else {
                document.getElementById('estimated-time').textContent = '2-4 hours';
            }
        }
        
        // Call on page load
        updateEstimatedTime();
    </script>
</body>
</html>
