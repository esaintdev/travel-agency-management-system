<?php
/**
 * Payment Confirmation - Show payment instructions to client
 */

session_start();
require_once 'config.php';
require_once 'includes/client-auth.php';

// Check if client is logged in
requireClientLogin();

// Check if we have payment data
if (!isset($_SESSION['payment_reference'])) {
    header('Location: client-payments.php');
    exit();
}

// Get payment data from session
$payment_reference = $_SESSION['payment_reference'];
$payment_method = $_SESSION['payment_method'];
$invoice_number = $_SESSION['invoice_number'];
$payment_amount = $_SESSION['payment_amount'];
$payment_currency = $_SESSION['payment_currency'];

// Get current client data
$client = getCurrentClient($db);

// Clear payment session data
unset($_SESSION['payment_reference']);
unset($_SESSION['payment_method']);
unset($_SESSION['invoice_number']);
unset($_SESSION['payment_amount']);
unset($_SESSION['payment_currency']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment Confirmation - M25 Travel & Tour Agency</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <!-- Google Web Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600;700&family=Poppins:wght@200;300;400;500;600&display=swap" rel="stylesheet">
    
    <!-- Icon Font Stylesheet -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.4.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <!-- Libraries Stylesheet -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    
    <style>
        .confirmation-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 60px 0;
        }
        
        .confirmation-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: -50px;
            background: white;
        }
        
        .success-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 30px;
            color: white;
        }
        
        .payment-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .instruction-card {
            border: 1px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .bank-details {
            background: #e3f2fd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
        }
        
        .copy-btn {
            background: #13357B;
            border: none;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <?php include 'includes/client-sidebar.php'; ?>

    <!-- Confirmation Header -->
    <div class="confirmation-header">
        <div class="container text-center">
            <h1 class="display-5 mb-3">Payment Request Submitted!</h1>
            <p class="lead">Your payment request has been received and is being processed</p>
        </div>
    </div>

    <!-- Confirmation Content -->
    <div class="container-fluid pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="confirmation-card card">
                    <div class="card-body p-5 text-center">
                        <div class="success-icon">
                            <i class="fas fa-check"></i>
                        </div>
                        
                        <h3 class="mb-4">Payment Request Confirmed</h3>
                        
                        <div class="payment-details">
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Payment Reference:</strong><br>
                                    <span class="text-primary fs-5"><?php echo htmlspecialchars($payment_reference); ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Invoice Number:</strong><br>
                                    <span class="text-primary"><?php echo htmlspecialchars($invoice_number); ?></span>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>Amount:</strong><br>
                                    <span class="fs-4 text-success"><?php echo formatCurrencyByCode($payment_amount, $payment_currency); ?></span>
                                </div>
                                <div class="col-md-6">
                                    <strong>Payment Method:</strong><br>
                                    <?php echo ucfirst(str_replace('_', ' ', $payment_method)); ?>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mt-4 mb-3">Payment Instructions</h5>
                        
                        <?php if ($payment_method === 'bank_transfer'): ?>
                        <div class="instruction-card text-start">
                            <h6><i class="fas fa-university me-2 text-primary"></i>Bank Transfer Instructions</h6>
                            <p>Please transfer the exact amount to our bank account using the details below:</p>
                            
                            <div class="bank-details">
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Bank Name:</strong> ABC Bank<br>
                                        <strong>Account Name:</strong> M25 Travel & Tours Agency<br>
                                        <strong>Account Number:</strong> 1234567890
                                        <button class="copy-btn ms-2" onclick="copyToClipboard('1234567890')">Copy</button>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Sort Code:</strong> 12-34-56<br>
                                        <strong>SWIFT Code:</strong> ABCXGHAC<br>
                                        <strong>Reference:</strong> <?php echo htmlspecialchars($payment_reference); ?>
                                        <button class="copy-btn ms-2" onclick="copyToClipboard('<?php echo htmlspecialchars($payment_reference); ?>')">Copy</button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning mt-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Important:</strong> Please use the payment reference <strong><?php echo htmlspecialchars($payment_reference); ?></strong> 
                                when making the transfer to ensure proper processing.
                            </div>
                        </div>
                        
                        <?php elseif ($payment_method === 'mobile_money'): ?>
                        <div class="instruction-card text-start">
                            <h6><i class="fas fa-mobile-alt me-2 text-primary"></i>Mobile Money Instructions</h6>
                            <p>Send money to our mobile money account:</p>
                            
                            <div class="bank-details">
                                <strong>Mobile Money Number:</strong> +233 24 123 4567<br>
                                <strong>Name:</strong> M25 Travel Agency<br>
                                <strong>Network:</strong> MTN Mobile Money<br>
                                <strong>Reference:</strong> <?php echo htmlspecialchars($payment_reference); ?>
                            </div>
                            
                            <div class="alert alert-info mt-3">
                                <i class="fas fa-info-circle me-2"></i>
                                After sending, please screenshot the confirmation and email it to payments@m25travelagency.com
                            </div>
                        </div>
                        
                        <?php elseif ($payment_method === 'credit_card'): ?>
                        <div class="instruction-card text-start">
                            <h6><i class="fas fa-credit-card me-2 text-primary"></i>Credit Card Payment</h6>
                            <p>We will send you a secure payment link via email within 2 hours.</p>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-envelope me-2"></i>
                                Check your email <strong><?php echo htmlspecialchars($client['client_email']); ?></strong> 
                                for the payment link.
                            </div>
                        </div>
                        
                        <?php elseif ($payment_method === 'paypal'): ?>
                        <div class="instruction-card text-start">
                            <h6><i class="fab fa-paypal me-2 text-primary"></i>PayPal Payment</h6>
                            <p>Send payment to our PayPal account:</p>
                            
                            <div class="bank-details">
                                <strong>PayPal Email:</strong> payments@m25travelagency.com<br>
                                <strong>Amount:</strong> <?php echo formatCurrencyByCode($payment_amount, $payment_currency); ?><br>
                                <strong>Reference:</strong> <?php echo htmlspecialchars($payment_reference); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="alert alert-success mt-4">
                            <h6><i class="fas fa-clock me-2"></i>What Happens Next?</h6>
                            <ul class="list-unstyled mb-0 text-start">
                                <li>• We will verify your payment within 24-48 hours</li>
                                <li>• You will receive an email confirmation once payment is processed</li>
                                <li>• Your invoice status will be updated to "Paid"</li>
                                <li>• You can track payment status in your dashboard</li>
                            </ul>
                        </div>
                        
                        <div class="mt-4">
                            <a href="client-payments.php" class="btn btn-primary me-3">
                                <i class="fas fa-arrow-left me-2"></i>Back to Payments
                            </a>
                            <a href="client-dashboard.php" class="btn btn-outline-primary">
                                <i class="fas fa-home me-2"></i>Go to Dashboard
                            </a>
                        </div>
                        
                        <div class="mt-4 pt-4 border-top">
                            <p class="text-muted mb-0">
                                <i class="fas fa-phone me-2"></i>Need help? Contact us at +233 59 260 5752 or 
                                <a href="mailto:support@m25travelagency.com">support@m25travelagency.com</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // Show success message
                const toast = document.createElement('div');
                toast.className = 'alert alert-success position-fixed';
                toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 200px;';
                toast.innerHTML = '<i class="fas fa-check me-2"></i>Copied to clipboard!';
                document.body.appendChild(toast);
                
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 3000);
            }).catch(function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
    <?php include 'includes/client-sidebar-close.php'; ?>
</body>
</html>
