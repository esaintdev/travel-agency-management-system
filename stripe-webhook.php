<?php
/**
 * Stripe Webhook Handler
 * Handles Stripe webhook events for additional payment verification
 */

require_once 'config.php';
require_once 'stripe-config.php';

// Initialize Stripe
require_once 'vendor/autoload.php';
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

// Get the raw POST data
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

$event = null;

try {
    // Verify webhook signature
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, STRIPE_WEBHOOK_SECRET
    );
} catch(\UnexpectedValueException $e) {
    // Invalid payload
    error_log('Stripe webhook invalid payload: ' . $e->getMessage());
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Invalid signature
    error_log('Stripe webhook invalid signature: ' . $e->getMessage());
    http_response_code(400);
    exit();
}

// Handle the event
try {
    switch ($event['type']) {
        case 'checkout.session.completed':
            $session = $event['data']['object'];
            
            // Log the successful checkout
            error_log('Stripe checkout completed: ' . $session['id']);
            
            // Additional processing can be done here
            // The main processing is handled in stripe-success.php
            break;
            
        case 'payment_intent.succeeded':
            $payment_intent = $event['data']['object'];
            
            // Extract metadata
            $invoice_id = $payment_intent['metadata']['invoice_id'] ?? null;
            $client_reference = $payment_intent['metadata']['client_reference'] ?? null;
            
            if ($invoice_id && $client_reference) {
                // Double-check payment status in database
                $stmt = $db->prepare("
                    SELECT p.*, i.invoice_number 
                    FROM payments p
                    JOIN invoices i ON p.invoice_id = i.id
                    WHERE p.transaction_id = ? OR p.transaction_id = ?
                ");
                $stmt->execute([$payment_intent['id'], $session['id'] ?? '']);
                $payment = $stmt->fetch();
                
                if ($payment && $payment['payment_status'] !== 'completed') {
                    // Update payment status via webhook
                    $stmt = $db->prepare("
                        UPDATE payments 
                        SET payment_status = 'completed',
                            payment_date = NOW(),
                            notes = CONCAT(COALESCE(notes, ''), '\nWebhook confirmation received.')
                        WHERE id = ?
                    ");
                    $stmt->execute([$payment['id']]);
                    
                    error_log("Webhook updated payment {$payment['id']} for client {$client_reference}");
                }
            }
            break;
            
        case 'payment_intent.payment_failed':
            $payment_intent = $event['data']['object'];
            
            // Handle failed payment
            $invoice_id = $payment_intent['metadata']['invoice_id'] ?? null;
            $client_reference = $payment_intent['metadata']['client_reference'] ?? null;
            
            if ($invoice_id && $client_reference) {
                // Update payment status to failed
                $stmt = $db->prepare("
                    UPDATE payments 
                    SET payment_status = 'failed',
                        notes = CONCAT(COALESCE(notes, ''), '\nPayment failed via webhook.')
                    WHERE transaction_id = ?
                ");
                $stmt->execute([$payment_intent['id']]);
                
                error_log("Webhook marked payment as failed for client {$client_reference}");
            }
            break;
            
        default:
            error_log('Received unknown Stripe event type: ' . $event['type']);
    }
    
    // Return success response
    http_response_code(200);
    echo json_encode(['status' => 'success']);
    
} catch (Exception $e) {
    error_log('Stripe webhook processing error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Webhook processing failed']);
}
?>
