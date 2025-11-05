<?php
/**
 * Email Service Class for M25 Travel & Tour Agency
 * Handles SMTP email sending with proper templates
 */

require_once 'PHPMailer.php';

class EmailService {
    private $smtp_host;
    private $smtp_port;
    private $smtp_username;
    private $smtp_password;
    private $smtp_encryption;
    private $from_email;
    private $from_name;
    
    public function __construct() {
        // Load SMTP configuration from config.php constants
        $this->smtp_host = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
        $this->smtp_port = defined('SMTP_PORT') ? SMTP_PORT : 587;
        $this->smtp_username = defined('SMTP_USERNAME') ? SMTP_USERNAME : '';
        $this->smtp_password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
        $this->smtp_encryption = defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls';
        $this->from_email = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'noreply@m25travelagency.com';
        $this->from_name = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : 'M25 Travel & Tour Agency';
    }
    
    /**
     * Send registration confirmation email
     */
    public function sendRegistrationConfirmation($clientData, $referenceId) {
        try {
            $mail = new PHPMailer();
            
            // SMTP Configuration
            $mail->Host = $this->smtp_host;
            $mail->Port = $this->smtp_port;
            $mail->SMTPAuth = $this->smtp_username ? true : false;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = $this->smtp_encryption;
            $mail->From = $this->from_email;
            $mail->FromName = $this->from_name;
            
            // Email content
            $mail->addAddress($clientData['client_email'], $clientData['full_name']);
            $mail->Subject = "Registration Confirmation - " . APP_NAME;
            $mail->IsHTML = true;
            $mail->Body = $this->getRegistrationTemplate($clientData, $referenceId);
            
            $result = $mail->send();
            
            if ($result) {
                error_log("Registration confirmation email sent to: " . $clientData['client_email']);
                return true;
            } else {
                error_log("Failed to send registration confirmation email to: " . $clientData['client_email']);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("SMTP email service error: " . $e->getMessage());
            // Fallback to PHP mail() function
            return $this->sendWithPHPMail($clientData, $referenceId, 'registration');
        }
    }
    
    /**
     * Send admin notification email
     */
    public function sendAdminNotification($clientData, $referenceId) {
        try {
            $mail = new PHPMailer();
            
            // SMTP Configuration
            $mail->Host = $this->smtp_host;
            $mail->Port = $this->smtp_port;
            $mail->SMTPAuth = $this->smtp_username ? true : false;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = $this->smtp_encryption;
            $mail->From = $this->from_email;
            $mail->FromName = $this->from_name;
            
            // Email content
            $mail->addAddress(ADMIN_EMAIL, 'Admin');
            $mail->Subject = "New Client Registration - " . $referenceId;
            $mail->IsHTML = true;
            $mail->Body = $this->getAdminNotificationTemplate($clientData, $referenceId);
            
            $result = $mail->send();
            
            if ($result) {
                error_log("Admin notification email sent for registration: " . $referenceId);
                return true;
            } else {
                error_log("Failed to send admin notification email for: " . $referenceId);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("SMTP admin notification email error: " . $e->getMessage());
            // Fallback to PHP mail() function
            return $this->sendWithPHPMail($clientData, $referenceId, 'admin');
        }
    }
    
    /**
     * Get registration confirmation email template
     */
    private function getRegistrationTemplate($clientData, $referenceId) {
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Registration Confirmation</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #13357B; color: white; padding: 20px; text-align: center; }
                .content { background: #f8f9fa; padding: 30px; }
                .reference-box { background: white; border-left: 4px solid #FEA116; padding: 15px; margin: 20px 0; }
                .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .details-table th, .details-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                .details-table th { background: #f1f1f1; }
                .footer { background: #13357B; color: white; padding: 20px; text-align: center; font-size: 12px; }
                .btn { display: inline-block; background: #FEA116; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>Welcome to M25 Travel & Tour Agency!</h1>
                    <p>Your Trusted Partner for Visa & Immigration Services</p>
                </div>
                
                <div class="content">
                    <h2>Dear ' . htmlspecialchars($clientData['full_name']) . ',</h2>
                    
                    <p>Thank you for choosing M25 Travel & Tour Agency for your visa and immigration needs. Your registration has been successfully completed and we have received your application.</p>
                    
                    <div class="reference-box">
                        <h3 style="margin: 0; color: #13357B;">Your Reference ID: <strong>' . htmlspecialchars($referenceId) . '</strong></h3>
                        <p style="margin: 5px 0 0 0; color: #666;">Please keep this reference ID for all future correspondence.</p>
                    </div>
                    
                    <h3>Application Summary:</h3>
                    <table class="details-table">
                        <tr><th>Full Name</th><td>' . htmlspecialchars($clientData['full_name']) . '</td></tr>
                        <tr><th>Email</th><td>' . htmlspecialchars($clientData['client_email']) . '</td></tr>
                        <tr><th>Mobile Number</th><td>' . htmlspecialchars($clientData['mobile_number']) . '</td></tr>
                        <tr><th>Country</th><td>' . htmlspecialchars($clientData['country']) . '</td></tr>
                        <tr><th>Visa Type</th><td>' . htmlspecialchars($clientData['visa_type']) . '</td></tr>
                        <tr><th>Submission Date</th><td>' . date('F j, Y \a\t g:i A') . '</td></tr>
                    </table>
                    
                    <h3>What Happens Next?</h3>
                    <ul>
                        <li>Our experienced team will review your application within 24-48 hours</li>
                        <li>We will contact you via email or phone to discuss the next steps</li>
                        <li>You may be asked to provide additional documents if required</li>
                        <li>We will guide you through the entire visa application process</li>
                    </ul>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . APP_URL . '/client-documents.php?ref=' . urlencode($referenceId) . '" class="btn">View Your Application</a>
                    </div>
                    
                    <h3>Need Help?</h3>
                    <p>If you have any questions or need assistance, please don\'t hesitate to contact us:</p>
                    <ul>
                        <li><strong>Email:</strong> info@m25travelagency.com</li>
                        <li><strong>Phone:</strong> +233 59 260 5752</li>
                        <li><strong>Website:</strong> <a href="' . APP_URL . '">' . APP_URL . '</a></li>
                    </ul>
                </div>
                
                <div class="footer">
                    <p><strong>M25 Travel & Tour Agency</strong><br>
                    Your Trusted Partner for Visa & Immigration Services</p>
                    <p>This is an automated message. Please do not reply to this email.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * Get admin notification email template
     */
    private function getAdminNotificationTemplate($clientData, $referenceId) {
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>New Client Registration</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #13357B; color: white; padding: 20px; text-align: center; }
                .content { background: #f8f9fa; padding: 30px; }
                .alert { background: #FEA116; color: white; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .details-table th, .details-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                .details-table th { background: #f1f1f1; }
                .btn { display: inline-block; background: #13357B; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>New Client Registration Alert</h1>
                </div>
                
                <div class="content">
                    <div class="alert">
                        <strong>Action Required:</strong> A new client has registered and requires review.
                    </div>
                    
                    <h3>Client Details:</h3>
                    <table class="details-table">
                        <tr><th>Reference ID</th><td><strong>' . htmlspecialchars($referenceId) . '</strong></td></tr>
                        <tr><th>Full Name</th><td>' . htmlspecialchars($clientData['full_name']) . '</td></tr>
                        <tr><th>Email</th><td>' . htmlspecialchars($clientData['client_email']) . '</td></tr>
                        <tr><th>Mobile Number</th><td>' . htmlspecialchars($clientData['mobile_number']) . '</td></tr>
                        <tr><th>Country</th><td>' . htmlspecialchars($clientData['country']) . '</td></tr>
                        <tr><th>Visa Type</th><td>' . htmlspecialchars($clientData['visa_type']) . '</td></tr>
                        <tr><th>Registration Date</th><td>' . date('F j, Y \a\t g:i A') . '</td></tr>
                    </table>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="' . APP_URL . '/admin-client-view.php?id=' . urlencode($referenceId) . '" class="btn">Review Application</a>
                    </div>
                    
                    <p><strong>Next Steps:</strong></p>
                    <ul>
                        <li>Review the client\'s application details</li>
                        <li>Contact the client within 24-48 hours</li>
                        <li>Request additional documents if needed</li>
                        <li>Update the application status accordingly</li>
                    </ul>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * Test SMTP connection
     */
    public function testConnection() {
        try {
            $mail = new PHPMailer();
            $mail->Host = $this->smtp_host;
            $mail->Port = $this->smtp_port;
            $mail->SMTPAuth = $this->smtp_username ? true : false;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = $this->smtp_encryption;
            
            // Try to establish connection
            $connection = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, 10);
            
            if ($connection) {
                fclose($connection);
                return array('success' => true, 'message' => 'SMTP connection successful');
            } else {
                return array('success' => false, 'message' => "Connection failed: $errstr ($errno)");
            }
            
        } catch (Exception $e) {
            return array('success' => false, 'message' => 'Connection error: ' . $e->getMessage());
        }
    }
    
    /**
     * Send popup form notification email to admin
     */
    public function sendPopupFormNotification($formData) {
        try {
            $mail = new PHPMailer();
            
            // SMTP Configuration
            $mail->Host = $this->smtp_host;
            $mail->Port = $this->smtp_port;
            $mail->SMTPAuth = $this->smtp_username ? true : false;
            $mail->Username = $this->smtp_username;
            $mail->Password = $this->smtp_password;
            $mail->SMTPSecure = $this->smtp_encryption;
            $mail->From = $this->from_email;
            $mail->FromName = $this->from_name;
            
            // Email content
            $mail->addAddress(ADMIN_EMAIL, 'Admin');
            $mail->Subject = "New Travel Inquiry - " . $formData['name'];
            $mail->IsHTML = true;
            $mail->Body = $this->getPopupFormTemplate($formData);
            
            $result = $mail->send();
            
            if ($result) {
                error_log("Popup form notification email sent for: " . $formData['name']);
                return true;
            } else {
                error_log("Failed to send popup form notification email for: " . $formData['name']);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("SMTP popup form notification error: " . $e->getMessage());
            // Fallback to PHP mail() function
            return $this->sendPopupFormWithPHPMail($formData);
        }
    }
    
    /**
     * Fallback email sending using PHP mail() function
     */
    private function sendWithPHPMail($clientData, $referenceId, $type = 'registration') {
        try {
            if ($type === 'registration') {
                $to = $clientData['client_email'];
                $subject = "Registration Confirmation - " . APP_NAME;
                $message = $this->getRegistrationTemplate($clientData, $referenceId);
            } else {
                $to = ADMIN_EMAIL;
                $subject = "New Client Registration - " . $referenceId;
                $message = $this->getAdminNotificationTemplate($clientData, $referenceId);
            }
            
            // Headers for HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: " . $this->from_name . " <" . $this->from_email . ">" . "\r\n";
            $headers .= "Reply-To: " . $this->from_email . "\r\n";
            
            // Send email using PHP mail() function
            $result = mail($to, $subject, $message, $headers);
            
            if ($result) {
                error_log("Fallback email sent successfully using PHP mail() to: " . $to);
                return true;
            } else {
                error_log("Failed to send fallback email using PHP mail() to: " . $to);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("PHP mail() fallback error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get popup form notification email template
     */
    private function getPopupFormTemplate($formData) {
        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>New Travel Inquiry</title>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #13357B; color: white; padding: 20px; text-align: center; }
                .content { background: #f8f9fa; padding: 30px; }
                .alert { background: #FEA116; color: white; padding: 15px; margin: 20px 0; border-radius: 5px; }
                .details-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .details-table th, .details-table td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
                .details-table th { background: #f1f1f1; }
                .btn { display: inline-block; background: #13357B; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>New Travel Inquiry Alert</h1>
                    <p>Someone is interested in your travel services!</p>
                </div>
                
                <div class="content">
                    <div class="alert">
                        <strong>Action Required:</strong> A potential client has expressed interest in travel services.
                    </div>
                    
                    <h3>Inquiry Details:</h3>
                    <table class="details-table">
                        <tr><th>Full Name</th><td>' . htmlspecialchars($formData['name']) . '</td></tr>
                        <tr><th>Email Address</th><td>' . htmlspecialchars($formData['email']) . '</td></tr>
                        <tr><th>Destination Country</th><td>' . htmlspecialchars($formData['country']) . '</td></tr>
                        <tr><th>Inquiry Date</th><td>' . htmlspecialchars($formData['submitted_at']) . '</td></tr>
                        <tr><th>IP Address</th><td>' . htmlspecialchars($formData['ip_address']) . '</td></tr>
                    </table>
                    
                    <h3>Recommended Actions:</h3>
                    <ul>
                        <li>Contact the client within 24 hours for best response rates</li>
                        <li>Provide information about visa requirements for ' . htmlspecialchars($formData['country']) . '</li>
                        <li>Send them a personalized travel package proposal</li>
                        <li>Follow up if no response within 3-5 days</li>
                    </ul>
                    
                    <div style="text-align: center; margin: 30px 0;">
                        <a href="mailto:' . htmlspecialchars($formData['email']) . '?subject=Your Travel Inquiry to ' . htmlspecialchars($formData['country']) . '" class="btn">Reply to Client</a>
                    </div>
                    
                    <p><strong>Note:</strong> This inquiry came from the popup form on your website homepage. The client showed immediate interest by filling out the form within seconds of visiting your site.</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $template;
    }
    
    /**
     * Fallback popup form email using PHP mail() function
     */
    private function sendPopupFormWithPHPMail($formData) {
        try {
            $to = ADMIN_EMAIL;
            $subject = "New Travel Inquiry - " . $formData['name'];
            $message = $this->getPopupFormTemplate($formData);
            
            // Headers for HTML email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= "From: " . $this->from_name . " <" . $this->from_email . ">" . "\r\n";
            $headers .= "Reply-To: " . $formData['email'] . "\r\n";
            
            // Send email using PHP mail() function
            $result = mail($to, $subject, $message, $headers);
            
            if ($result) {
                error_log("Popup form fallback email sent successfully to: " . $to);
                return true;
            } else {
                error_log("Failed to send popup form fallback email to: " . $to);
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Popup form PHP mail() fallback error: " . $e->getMessage());
            return false;
        }
    }
}
?>
