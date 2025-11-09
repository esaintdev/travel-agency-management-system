<?php
/**
 * Stripe Payment Processing
 * Handles Stripe checkout session creation and payment processing
 */

session_start();
require_once 'config.php';
require_once 'includes/client-auth.php';
require_once 'stripe-config.php';

// Check if client is logged in
requireClientLogin();

// Get current client data
$client = getCurrentClient($db);
if (!$client) {
    destroyClientSession();
    $_SESSION['error_message'] = "Unable to load your account. Please log in again.";
    header('Location: client-login.php');
    exit();
}

// Validate input
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error_message'] = "Invalid request method.";
    header('Location: client-payments.php');
    exit();
}

if (!isset($_POST['invoice_id']) || empty($_POST['invoice_id'])) {
    $_SESSION['error_message'] = "Invoice ID is required.";
    header('Location: client-payments.php');
    exit();
}

$invoice_id = intval($_POST['invoice_id']);

try {
    // Get invoice details
    $stmt = $db->prepare("
        SELECT i.*, c.full_name, c.reference_id, c.client_email
        FROM invoices i 
        JOIN clients c ON i.client_id = c.id 
        WHERE i.id = ? AND i.client_id = ? AND i.status = 'pending'
    ");
    $stmt->execute([$invoice_id, $client['id']]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        $_SESSION['error_message'] = "Invoice not found or already paid.";
        header('Location: client-payments.php');
        exit();
    }
    
    // Calculate amount to pay (check for existing payments)
    $stmt = $db->prepare("
        SELECT COALESCE(SUM(amount), 0) as total_paid 
        FROM payments 
        WHERE invoice_id = ? AND payment_status = 'completed'
    ");
    $stmt->execute([$invoice_id]);
    $total_paid = $stmt->fetch()['total_paid'];
    
    $amount_due = $invoice['amount'] - $total_paid;
    
    if ($amount_due <= 0) {
        $_SESSION['error_message'] = "This invoice has already been paid in full.";
        header('Location: client-payments.php');
        exit();
    }
    
    // Check if Stripe is configured
    if (!defined('STRIPE_SECRET_KEY') || empty(STRIPE_SECRET_KEY) || STRIPE_SECRET_KEY === 'sk_test_your_secret_key_here') {
        $_SESSION['error_message'] = "Payment processing is not configured. Please contact support.";
        header('Location: client-payments.php');
        exit();
    }
    
    // Convert currency and amount for Stripe
    $stripe_currency = getStripeCurrency($invoice['currency']);
    $stripe_amount = convertToStripeAmount($amount_due, $stripe_currency);
    
    // Prepare Stripe Checkout Session data
    $base_url = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']);
    
    // Prepare Stripe API data in the correct format
    $post_data = [
        'payment_method_types[0]' => 'card',
        'line_items[0][price_data][currency]' => $stripe_currency,
        'line_items[0][price_data][product_data][name]' => 'Invoice Payment - ' . $invoice['invoice_number'],
        'line_items[0][price_data][product_data][description]' => $invoice['description'],
        'line_items[0][price_data][unit_amount]' => $stripe_amount,
        'line_items[0][quantity]' => 1,
        'mode' => 'payment',
        'success_url' => $base_url . '/stripe-success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => $base_url . '/client-payments.php?cancelled=1',
        'customer_email' => $client['client_email'],
        'metadata[invoice_id]' => (string)$invoice_id,
        'metadata[client_id]' => (string)$client['id'],
        'metadata[client_reference]' => $client['reference_id'],
        'metadata[invoice_number]' => $invoice['invoice_number'],
        'metadata[original_amount]' => (string)$amount_due,
        'metadata[currency]' => $invoice['currency']
    ];
    
    // Create Stripe Checkout Session via cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/checkout/sessions');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY,
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        error_log("Stripe API Error: HTTP $http_code - $response");
        $_SESSION['error_message'] = "Payment processing error. Please try again or contact support.";
        header('Location: client-payments.php');
        exit();
    }
    
    $checkout_session = json_decode($response, true);
    
    if (!$checkout_session || !isset($checkout_session['id'])) {
        error_log("Stripe Session Creation Failed: " . $response);
        $_SESSION['error_message'] = "Unable to create payment session. Please try again.";
        header('Location: client-payments.php');
        exit();
    }
    
    // Create a pending payment record
    $payment_reference = 'PAY-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    
    $stmt = $db->prepare("
        INSERT INTO payments (
            payment_reference, invoice_id, client_id, client_reference_id, 
            amount, currency, payment_method, payment_status, 
            transaction_id, payment_gateway, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->execute([
        $payment_reference,
        $invoice_id,
        $client['id'],
        $client['reference_id'],
        $amount_due,
        $invoice['currency'],
        'stripe_card',
        'pending',
        $checkout_session['id'],
        'stripe',
        'Stripe checkout session created. Reference: ' . $client['reference_id']
    ]);
    
    // Log activity
    logActivity(null, $client['id'], 'Stripe Payment Initiated', 
               "Client {$client['reference_id']} initiated Stripe payment for invoice {$invoice['invoice_number']}", $db);
    
    // Redirect to Stripe Checkout
    header('Location: ' . $checkout_session['url']);
    exit();
    
} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    $_SESSION['error_message'] = "An error occurred while processing your payment. Please try again.";
    header('Location: client-payments.php');
    exit();
}
?>
