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
    header('Location: client-registration.php');
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: client-registration.php');
    exit();
}

try {
    // Test database connection
    if (!isset($db)) {
        throw new Exception("Database connection not available");
    }
    
    // Validate required fields with better error messages
    $required_fields = [
        'full_name' => 'Full Name',
        'gender' => 'Gender',
        'date_of_birth' => 'Date of Birth',
        'country' => 'Country',
        'country_code' => 'Country Code',
        'mobile_number' => 'Mobile Number',
        'client_email' => 'Email Address',
        'password' => 'Password',
        'visa_type' => 'Visa Type',
        'address' => 'Physical Address'
    ];
    
    $missing_fields = [];
    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $label;
        }
    }
    
    if (!empty($missing_fields)) {
        if (count($missing_fields) == 1) {
            throw new Exception("Please fill in the required field: " . $missing_fields[0]);
        } else {
            throw new Exception("Please fill in the following required fields: " . implode(', ', $missing_fields));
        }
    }
    
    // Validate email format
    if (!filter_var($_POST['client_email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter a valid email address.");
    }
    
    // Validate mobile number (basic validation)
    $mobile_number = trim($_POST['mobile_number']);
    if (strlen($mobile_number) < 7 || strlen($mobile_number) > 15) {
        throw new Exception("Please enter a valid mobile number (7-15 digits).");
    }
    
    if (!preg_match('/^[0-9]+$/', $mobile_number)) {
        throw new Exception("Mobile number should contain only numbers (without country code).");
    }
    
    // Validate password strength
    $password = $_POST['password'];
    if (strlen($password) < 6) {
        throw new Exception("Password must be at least 6 characters long.");
    }
    
    // Validate date of birth
    $dob = $_POST['date_of_birth'];
    $dob_timestamp = strtotime($dob);
    $min_age_timestamp = strtotime('-100 years');
    $max_age_timestamp = strtotime('-16 years'); // Minimum 16 years old
    
    if ($dob_timestamp === false) {
        throw new Exception("Please enter a valid date of birth.");
    }
    
    if ($dob_timestamp > $max_age_timestamp) {
        throw new Exception("You must be at least 16 years old to register.");
    }
    
    if ($dob_timestamp < $min_age_timestamp) {
        throw new Exception("Please enter a valid date of birth.");
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT id, full_name FROM clients WHERE client_email = ?");
    $stmt->execute([$_POST['client_email']]);
    $existing_client = $stmt->fetch();
    if ($existing_client) {
        throw new Exception("Registration failed: An account with the email address '" . $_POST['client_email'] . "' already exists. If this is your account, please use the login page instead. If you forgot your password, please contact us at info@m25travelagency.com for assistance.");
    }
    
    // Generate unique reference ID
    do {
        $reference_id = 'M25-' . date('Y') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $stmt = $db->prepare("SELECT id FROM clients WHERE reference_id = ?");
        $stmt->execute([$reference_id]);
    } while ($stmt->fetch());
    
    // Hash password
    $password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Combine country code with mobile number
    $full_mobile_number = trim($_POST['country_code']) . trim($_POST['mobile_number']);
    
    // Prepare client data (only include fields that exist in the table)
    $client_data = [
        'reference_id' => $reference_id,
        'full_name' => trim($_POST['full_name']),
        'gender' => $_POST['gender'],
        'date_of_birth' => $_POST['date_of_birth'],
        'country' => trim($_POST['country']),
        'mobile_number' => $full_mobile_number,
        'client_email' => trim($_POST['client_email']),
        'password_hash' => $password_hash,
        'visa_type' => $_POST['visa_type'],
        'address' => trim($_POST['address'])
    ];
    
    // Add optional fields if they exist and are not empty
    $optional_fields = [
        'passport_number', 'work_type', 'date_contract_started', 'visa_application_hold',
        'visa_denial_appeal', 'immigration_history', 'result_outcome',
        'bank_name', 'bank_branch', 'account_name', 'account_number',
        'visa_pin', 'spouse_name', 'spouse_dob', 'spouse_address', 'spouse_status',
        'father_name', 'father_dob', 'father_address', 'father_status',
        'mother_name', 'mother_dob', 'mother_address', 'mother_status',
        'child1_name', 'child1_dob', 'child2_name', 'child2_dob',
        'child3_name', 'child3_dob', 'child4_name', 'child4_dob',
        // New address fields
        'house_number', 'street_name', 'location', 'digital_address', 'postal_address',
        // New employment fields
        'employment_letter_details',
        // New educational fields
        'university', 'graduation_year', 'bachelor_degree', 'master_degree', 'other_qualifications',
        // New financial fields
        'account_holder_name', 'average_monthly_balance', 'financial_declaration', 
        'estimated_trip_budget', 'funding_source'
    ];
    
    foreach ($optional_fields as $field) {
        if (isset($_POST[$field]) && !empty($_POST[$field])) {
            if (strpos($field, '_dob') !== false || $field === 'date_contract_started') {
                // Date fields
                $client_data[$field] = $_POST[$field] ?: null;
            } elseif (in_array($field, ['average_monthly_balance', 'estimated_trip_budget'])) {
                // Numeric fields
                $client_data[$field] = floatval($_POST[$field]);
            } elseif ($field === 'graduation_year') {
                // Integer fields
                $client_data[$field] = !empty($_POST[$field]) ? intval($_POST[$field]) : null;
            } else {
                // Text fields
                $client_data[$field] = trim($_POST[$field]);
            }
        }
    }
    
    // Handle file uploads
    $upload_dir = 'uploads/clients/' . $reference_id . '/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Define file upload fields
    $file_fields = [
        'employment_letter' => 'employment_letter_path',
        'payslips' => 'payslips_path',
        'educational_certificates' => 'educational_certificates_path',
        'marriage_certificate' => 'marriage_certificate_path',
        'birth_certificates' => 'birth_certificates_path',
        'bank_statements' => 'bank_statements_path',
        'financial_evidence' => 'financial_evidence_path'
    ];
    
    foreach ($file_fields as $file_field => $db_field) {
        if (isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] === UPLOAD_ERR_OK) {
            $uploaded_files = [];
            
            // Handle multiple files
            if (is_array($_FILES[$file_field]['name'])) {
                for ($i = 0; $i < count($_FILES[$file_field]['name']); $i++) {
                    if ($_FILES[$file_field]['error'][$i] === UPLOAD_ERR_OK) {
                        $file_name = $_FILES[$file_field]['name'][$i];
                        $file_tmp = $_FILES[$file_field]['tmp_name'][$i];
                        $file_size = $_FILES[$file_field]['size'][$i];
                        
                        // Validate file
                        if ($file_size > MAX_FILE_SIZE) {
                            throw new Exception("File {$file_name} is too large. Maximum size is 5MB.");
                        }
                        
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                            throw new Exception("File type {$file_ext} is not allowed for {$file_name}.");
                        }
                        
                        // Generate unique filename
                        $new_filename = $file_field . '_' . time() . '_' . $i . '.' . $file_ext;
                        $file_path = $upload_dir . $new_filename;
                        
                        if (move_uploaded_file($file_tmp, $file_path)) {
                            $uploaded_files[] = $file_path;
                        }
                    }
                }
            } else {
                // Handle single file
                $file_name = $_FILES[$file_field]['name'];
                $file_tmp = $_FILES[$file_field]['tmp_name'];
                $file_size = $_FILES[$file_field]['size'];
                
                // Validate file
                if ($file_size > MAX_FILE_SIZE) {
                    throw new Exception("File {$file_name} is too large. Maximum size is 5MB.");
                }
                
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                if (!in_array($file_ext, ALLOWED_EXTENSIONS)) {
                    throw new Exception("File type {$file_ext} is not allowed for {$file_name}.");
                }
                
                // Generate unique filename
                $new_filename = $file_field . '_' . time() . '.' . $file_ext;
                $file_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($file_tmp, $file_path)) {
                    $uploaded_files[] = $file_path;
                }
            }
            
            // Store file paths in client data
            if (!empty($uploaded_files)) {
                $client_data[$db_field] = implode(',', $uploaded_files);
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
    
    // Send confirmation emails using SMTP
    try {
        require_once 'includes/EmailService.php';
        $emailService = new EmailService();
        
        // Send registration confirmation email to client
        $emailSent = $emailService->sendRegistrationConfirmation($client_data, $reference_id);
        
        if ($emailSent) {
            error_log("Registration confirmation email sent successfully to: {$client_data['client_email']}");
        } else {
            error_log("Failed to send registration confirmation email to: {$client_data['client_email']}");
        }
        
        // Send admin notification email
        $adminEmailSent = $emailService->sendAdminNotification($client_data, $reference_id);
        
        if ($adminEmailSent) {
            error_log("Admin notification email sent successfully for registration: {$reference_id}");
        } else {
            error_log("Failed to send admin notification email for registration: {$reference_id}");
        }
        
    } catch (Exception $e) {
        error_log("Email service error: " . $e->getMessage());
        // Don't fail the registration if email fails - just log the error
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
    header('Location: client-registration.php');
    exit();
}
?>