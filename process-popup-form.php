<?php
/**
 * Process Popup Form Submission for M25 Travel Agency
 * Sends email notification to admin when popup form is submitted
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
require_once 'config.php';
require_once 'includes/EmailService.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

try {
    // Get form data
    $name = sanitizeInput($_POST['name'] ?? '');
    $email = sanitizeInput($_POST['email'] ?? '');
    $country = sanitizeInput($_POST['country'] ?? '');
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($country)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        exit();
    }
    
    // Validate email
    if (!validateEmail($email)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address']);
        exit();
    }
    
    // Create email service instance
    $emailService = new EmailService();
    
    // Send notification email to admin
    $result = $emailService->sendPopupFormNotification([
        'name' => $name,
        'email' => $email,
        'country' => $country,
        'submitted_at' => date('Y-m-d H:i:s'),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
    ]);
    
    if ($result) {
        // Log the submission
        error_log("Popup form submitted: Name: $name, Email: $email, Country: $country");
        
        echo json_encode([
            'success' => true, 
            'message' => "Thank you $name! We will contact you soon about your travel plans to $country."
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Thank you for your interest! We have received your information and will contact you soon.'
        ]);
    }
    
} catch (Exception $e) {
    error_log("Popup form processing error: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Thank you for your interest! We have received your information and will contact you soon.'
    ]);
}
?>
