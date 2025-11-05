<?php
session_start();

// Check if user came from registration process
if (!isset($_SESSION['success_message']) || !isset($_SESSION['reference_id'])) {
    header('Location: client-registration');
    exit();
}

$success_message = $_SESSION['success_message'];
$reference_id = $_SESSION['reference_id'];

// Clear session variables
unset($_SESSION['success_message']);
unset($_SESSION['reference_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Registration Success - M25 Travel & Tour Agency</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Libraries Stylesheet -->
    <link href="lib/animate/animate.min.css" rel="stylesheet">
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .success-container {
            background: linear-gradient(135deg, #13357B 0%, #FEA116 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 50px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 20px;
        }
        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 30px;
        }
        .reference-id {
            background: #f8f9fa;
            border: 2px dashed #13357B;
            padding: 20px;
            border-radius: 10px;
            margin: 30px 0;
            font-size: 24px;
            font-weight: bold;
            color: #13357B;
        }
        .btn-home {
            background: #13357B;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-home:hover {
            background: #FEA116;
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="success-card">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h2 class="text-primary mb-4">Registration Successful!</h2>
            
            <p class="lead mb-4">
                Thank you for registering with M25 Travel & Tour Agency. 
                Your visa application has been successfully submitted.
            </p>
            
            <div class="reference-id">
                <div class="small text-muted mb-2">Your Reference ID</div>
                <?php echo htmlspecialchars($reference_id); ?>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-envelope me-2"></i>
                <strong>Email Confirmation:</strong> A detailed confirmation email with your application details 
                has been sent to your registered email address. Please check your inbox (and spam folder).
            </div>
            
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                <strong>Important:</strong> Please save your Reference ID for all future correspondence with our office.
            </div>
            
            <div class="mt-4">
                <h5 class="text-primary mb-3">What's Next?</h5>
                <ul class="list-unstyled text-start">
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Our team will review your application</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> We'll contact you within 2-3 business days</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> You'll receive updates via email and phone</li>
                    <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Keep your documents ready for verification</li>
                </ul>
            </div>
            
            <div class="mt-5">
                <a href="/" class="btn-home me-3">
                    <i class="fas fa-home me-2"></i>Back to Home
                </a>
                <a href="client-registration" class="btn btn-outline-primary">
                    <i class="fas fa-plus me-2"></i>New Registration
                </a>
            </div>
            
            <div class="mt-4 pt-4 border-top">
                <p class="text-muted mb-0">
                    <i class="fas fa-phone me-2"></i>Need help? Call us at +233 59 260 5752
                    <br>
                    <i class="fas fa-envelope me-2"></i>Email: info@m25travelagency.com
                </p>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-select reference ID for easy copying
        document.querySelector('.reference-id').addEventListener('click', function() {
            const range = document.createRange();
            range.selectNodeContents(this);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            
            // Try to copy to clipboard
            try {
                document.execCommand('copy');
                this.style.background = '#d4edda';
                setTimeout(() => {
                    this.style.background = '#f8f9fa';
                }, 1000);
            } catch (err) {
                console.log('Copy failed');
            }
        });
    </script>
</body>
</html>
