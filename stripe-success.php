<?php
/**
 * Stripe Payment Success Handler
 * Processes successful Stripe payments and updates database
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

// Validate session ID
if (!isset($_GET['session_id']) || empty($_GET['session_id'])) {
    $_SESSION['error_message'] = "Invalid payment session.";
    header('Location: client-payments.php');
    exit();
}

$session_id = $_GET['session_id'];

try {
    // Check if Stripe is configured
    if (!defined('STRIPE_SECRET_KEY') || empty(STRIPE_SECRET_KEY) || STRIPE_SECRET_KEY === 'sk_test_your_secret_key_here') {
        $_SESSION['error_message'] = "Payment processing is not configured. Please contact support.";
        header('Location: client-payments.php');
        exit();
    }
    
    // Retrieve the Stripe session via cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/checkout/sessions/' . $session_id);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . STRIPE_SECRET_KEY
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code !== 200) {
        error_log("Stripe Session Retrieval Error: HTTP $http_code - $response");
        $_SESSION['error_message'] = "Unable to verify payment. Please contact support.";
        header('Location: client-payments.php');
        exit();
    }
    
    $session = json_decode($response, true);
    
    if (!$session || $session['payment_status'] !== 'paid') {
        $_SESSION['error_message'] = "Payment was not successful. Please try again.";
        header('Location: client-payments.php');
        exit();
    }
    
    // Extract metadata
    $invoice_id = $session['metadata']['invoice_id'];
    $client_reference = $session['metadata']['client_reference'];
    $invoice_number = $session['metadata']['invoice_number'];
    $original_amount = $session['metadata']['original_amount'];
    $currency = $session['metadata']['currency'];
    
    // Verify this payment belongs to the current client
    if ($client_reference !== $client['reference_id']) {
        $_SESSION['error_message'] = "Payment verification failed.";
        header('Location: client-payments.php');
        exit();
    }
    
    // Check if payment has already been processed
    $stmt = $db->prepare("
        SELECT id FROM payments 
        WHERE transaction_id = ? AND payment_status = 'completed'
    ");
    $stmt->execute([$session_id]);
    
    if ($stmt->fetch()) {
        // Payment already processed, redirect to success
        $_SESSION['success_message'] = "Payment has already been processed successfully!";
        header('Location: client-payment-confirmation.php?payment_id=' . $stmt->fetch()['id']);
        exit();
    }
    
    // Update the pending payment record
    $stmt = $db->prepare("
        UPDATE payments 
        SET payment_status = 'completed',
            payment_date = NOW(),
            transaction_id = ?,
            notes = CONCAT(COALESCE(notes, ''), '\nStripe payment completed. Payment Intent: ', ?)
        WHERE transaction_id = ? AND client_id = ? AND payment_status = 'pending'
    ");
    
    $stmt->execute([
        $payment_intent->id,
        $payment_intent->id,
        $session_id,
        $client['id']
    ]);
    
    if ($stmt->rowCount() === 0) {
        // Create new payment record if pending record not found
        $payment_reference = 'PAY-' . date('Y') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        $stmt = $db->prepare("
            INSERT INTO payments (
                payment_reference, invoice_id, client_id, client_reference_id,
                amount, currency, payment_method, payment_status,
                transaction_id, payment_gateway, payment_date, notes, created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW())
        ");
        
        $stmt->execute([
            $payment_reference,
            $invoice_id,
            $client['id'],
            $client['reference_id'],
            $original_amount,
            $currency,
            'stripe_card',
            'completed',
            $payment_intent->id,
            'stripe',
            'Stripe payment completed successfully. Reference: ' . $client_reference
        ]);
        
        $payment_id = $db->lastInsertId();
    } else {
        // Get the updated payment ID
        $stmt = $db->prepare("SELECT id FROM payments WHERE transaction_id = ?");
        $stmt->execute([$payment_intent->id]);
        $payment_id = $stmt->fetch()['id'];
    }
    
    // Check if invoice is now fully paid
    $stmt = $db->prepare("
        SELECT i.amount, COALESCE(SUM(p.amount), 0) as total_paid
        FROM invoices i
        LEFT JOIN payments p ON i.id = p.invoice_id AND p.payment_status = 'completed'
        WHERE i.id = ?
        GROUP BY i.id, i.amount
    ");
    $stmt->execute([$invoice_id]);
    $invoice_data = $stmt->fetch();
    
    if ($invoice_data && $invoice_data['total_paid'] >= $invoice_data['amount']) {
        // Mark invoice as paid
        $stmt = $db->prepare("
            UPDATE invoices 
            SET status = 'paid', paid_at = NOW(), updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$invoice_id]);
        
        // Log activity
        logActivity(null, $client['id'], 'Invoice Paid via Stripe', 
                   "Invoice {$invoice_number} fully paid by client {$client_reference} via Stripe", $db);
    }
    
    // Update client payment totals
    $stmt = $db->prepare("
        UPDATE clients 
        SET total_paid = (
            SELECT COALESCE(SUM(amount), 0) 
            FROM payments 
            WHERE client_id = ? AND payment_status = 'completed'
        ),
        last_payment_date = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$client['id'], $client['id']]);
    
    // Log successful payment
    logActivity(null, $client['id'], 'Stripe Payment Completed', 
               "Client {$client_reference} completed payment for invoice {$invoice_number} via Stripe. Amount: " . 
               formatCurrencyByCode($original_amount, $currency), $db);
    
    // Redirect to confirmation page
    $_SESSION['success_message'] = "Payment completed successfully!";
    header('Location: client-payment-confirmation.php?payment_id=' . $payment_id . '&stripe=1');
    exit();
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    error_log("Stripe API Error in success handler: " . $e->getMessage());
    $_SESSION['error_message'] = "Payment verification failed. Please contact support with your payment confirmation.";
    header('Location: client-payments.php');
    exit();
    
} catch (Exception $e) {
    error_log("Payment success handler error: " . $e->getMessage());
    $_SESSION['error_message'] = "An error occurred while confirming your payment. Please contact support.";
    header('Location: client-payments.php');
    exit();
}
?>
