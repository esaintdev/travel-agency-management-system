<?php
/**
 * Fixed Registration Processing Script
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session
session_start();

// Check if config exists
if (!file_exists('config.php')) {
    $_SESSION['error_message'] = "System not configured. Please run the installer first.";
    header('Location: simple_install.php');
    exit();
}

try {
    require_once 'config.php';
} catch (Exception $e) {
    $_SESSION['error_message'] = "Configuration error: " . $e->getMessage();
    header('Location: client-registration.html');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: client-registration.html');
    exit();
}

try {
    // Test database connection
    if (!isset($db)) {
        throw new Exception("Database connection not available");
    }
    
    // Validate required fields
    $required_fields = ['full_name', 'gender', 'date_of_birth', 'country', 'mobile_number', 'client_email', 'password', 'visa_type', 'address'];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Please fill in all required fields: " . str_replace('_', ' ', ucfirst($field)));
        }
    }
    
    // Validate email format
    if (!filter_var($_POST['client_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id FROM clients WHERE client_email = ?");
    $stmt->execute([$_POST['client_email']]);
    if ($stmt->fetch()) {
        throw new Exception("An account with this email address already exists.");
    }
    
    // Generate unique reference ID
    do {
        $reference_id = 'M25-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $db->prepare("SELECT id FROM clients WHERE reference_id = ?");
        $stmt->execute([$reference_id]);
    } while ($stmt->fetch());
    
    // Hash password
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Prepare client data (only include fields that exist in the table)
    $client_data = [
        'reference_id' => $reference_id,
        'full_name' => trim($_POST['full_name']),
        'gender' => $_POST['gender'],
        'date_of_birth' => $_POST['date_of_birth'],
        'country' => trim($_POST['country']),
        'mobile_number' => trim($_POST['mobile_number']),
        'client_email' => trim($_POST['client_email']),
        'password_hash' => $password_hash,
        'visa_type' => $_POST['visa_type'],
        'address' => trim($_POST['address'])
    ];
    
    // Add optional fields if they exist and are not empty
    $optional_fields = [
        'our_number_for_client', 'our_email', 'ghana_card_number', 'momo_number', 
        'passport_number', 'work_type', 'date_contract_started', 'visa_application_hold',
        'visa_denial_appeal', 'immigration_history', 'result_outcome', 'deposit_paid',
        'balance_due', 'bank_name', 'bank_branch', 'account_name', 'account_number',
        'visa_pin', 'spouse_name', 'spouse_dob', 'spouse_address', 'spouse_status',
        'father_name', 'father_dob', 'father_address', 'father_status',
        'mother_name', 'mother_dob', 'mother_address', 'mother_status',
        'child1_name', 'child1_dob', 'child1_address', 'child1_status',
        'child2_name', 'child2_dob', 'child2_address', 'child2_status',
        'child3_name', 'child3_dob', 'child3_address', 'child3_status'
    ];
    
    foreach ($optional_fields as $field) {
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            if (strpos($field, '_dob') !== false || $field === 'date_contract_started') {
                // Date fields
                $client_data[$field] = $_POST[$field] ?: null;
            } elseif (in_array($field, ['deposit_paid', 'balance_due'])) {
                // Numeric fields
                $client_data[$field] = floatval($_POST[$field]);
            } else {
                // Text fields
                $client_data[$field] = trim($_POST[$field]);
            }
        }
    }
    
    // Start transaction
    $db->beginTransaction();
    
    // Build and execute insert query
    $columns = implode(', ', array_keys($client_data));
    $placeholders = ':' . implode(', :', array_keys($client_data));
    
    $sql = "INSERT INTO clients ({$columns}) VALUES ({$placeholders})";
    $stmt = $db->prepare($sql);
    
    // Execute the insert
    if (!$stmt->execute($client_data)) {
        throw new Exception("Failed to save registration data");
    }
    
    $client_id = $db->lastInsertId();
    
    // Commit transaction
    $db->commit();
    
    // Log successful registration
    error_log("New client registered: ID $client_id, Reference: $reference_id, Email: {$client_data['client_email']}");
    
    // Set success message
    $_SESSION['success_message'] = "Registration successful! Your reference ID is: {$reference_id}";
    $_SESSION['reference_id'] = $reference_id;
    $_SESSION['client_name'] = $client_data['full_name'];
    
    // Try to send email (don't fail if email fails)
    try {
        $email_subject = "Registration Confirmation - M25 Travel & Tour Agency";
        $email_message = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2 style='color: #13357B;'>Welcome to M25 Travel & Tour Agency!</h2>
            <p>Dear {$client_data['full_name']},</p>
            <p>Thank you for registering with us. Your application has been successfully submitted.</p>
            <div style='background: #f8f9fa; padding: 15px; margin: 20px 0; border-left: 4px solid #FEA116;'>
                <h3 style='margin: 0; color: #13357B;'>Your Reference ID: {$reference_id}</h3>
            </div>
            <p>Please keep this reference ID for future correspondence.</p>
            <p>Our team will review your application and contact you soon.</p>
            <hr>
            <p><strong>Application Details:</strong></p>
            <ul>
                <li>Visa Type: {$client_data['visa_type']}</li>
                <li>Country: {$client_data['country']}</li>
                <li>Submitted: " . date('Y-m-d H:i:s') . "</li>
            </ul>
            <p>Best regards,<br>M25 Travel & Tour Agency Team</p>
        </body>
        </html>";
        
        // Simple mail function (you can enhance this later)
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: M25 Travel & Tour Agency <info@m25travelagency.com>\r\n";
        
        mail($client_data['client_email'], $email_subject, $email_message, $headers);
        
    } catch (Exception $e) {
        error_log("Email sending failed: " . $e->getMessage());
        // Don't fail the registration if email fails
    }
    
    // Redirect to success page
    header('Location: registration-success.php');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction if it was started
    if (isset($db)) {
        try {
            $db->rollback();
        } catch (Exception $rollback_error) {
            // Ignore rollback errors
        }
    }
    
    // Log the error
    error_log("Registration error: " . $e->getMessage());
    error_log("POST data: " . print_r($_POST, true));
    
    // Set error message and redirect back
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: client-registration.html');
    exit();
}
?>
