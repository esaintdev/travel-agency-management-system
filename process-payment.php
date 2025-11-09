<?php
/**
 * Process Payment - Handle payment submissions from clients
 */

session_start();
require_once 'config.php';
require_once 'includes/client-auth.php';
require_once 'includes/notification-functions.php';

// Check if client is logged in
requireClientLogin();

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: client-payments.php');
    exit();
}

try {
    // Get current client data
    $client = getCurrentClient($db);
    if (!$client) {
        throw new Exception("Unable to load your account. Please log in again.");
    }
    
    // Validate required fields
    if (empty($_POST['invoice_id']) || empty($_POST['payment_method'])) {
        throw new Exception("Please fill in all required fields.");
    }
    
    $invoice_id = intval($_POST['invoice_id']);
    $payment_method = sanitizeInput($_POST['payment_method']);
    $payment_notes = sanitizeInput($_POST['payment_notes'] ?? '');
    
    // Get invoice details
    $stmt = $db->prepare("SELECT * FROM invoices WHERE id = ? AND client_id = ? AND status = 'pending'");
    $stmt->execute([$invoice_id, $client['id']]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        throw new Exception("Invoice not found or already paid.");
    }
    
    // Generate payment reference
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM payments WHERE YEAR(created_at) = YEAR(CURDATE())");
    $stmt->execute();
    $count = $stmt->fetch()['count'] + 1;
    $payment_reference = 'PAY-' . date('Y') . '-' . str_pad($count, 6, '0', STR_PAD_LEFT);
    
    // Create payment record
    $stmt = $db->prepare("
        INSERT INTO payments (
            payment_reference, invoice_id, client_id, client_reference_id, 
            amount, currency, payment_method, payment_status, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?)
    ");
    
    $stmt->execute([
        $payment_reference,
        $invoice_id,
        $client['id'],
        $client['reference_id'],
        $invoice['amount'],
        $invoice['currency'],
        $payment_method,
        $payment_notes
    ]);
    
    $payment_id = $db->lastInsertId();
    
    // Create payment item
    $stmt = $db->prepare("
        INSERT INTO payment_items (payment_id, invoice_id, item_type, description, amount)
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $payment_id,
        $invoice_id,
        $invoice['invoice_type'],
        $invoice['description'],
        $invoice['amount']
    ]);
    
    // Log activity
    logActivity(null, $client['id'], 'Payment Submitted', "Payment request submitted for invoice {$invoice['invoice_number']} - {$payment_reference}", $db);
    
    // Create notification for admin (send to admin user ID 1 - you may want to make this configurable)
    createPaymentNotification($db, 1, $payment_id, $client['full_name'], formatCurrencyByCode($invoice['amount'], $invoice['currency']));
    
    // Set success message with payment instructions
    $_SESSION['success_message'] = "Payment request submitted successfully! Reference: {$payment_reference}";
    $_SESSION['payment_reference'] = $payment_reference;
    $_SESSION['payment_method'] = $payment_method;
    $_SESSION['invoice_number'] = $invoice['invoice_number'];
    $_SESSION['payment_amount'] = $invoice['amount'];
    $_SESSION['payment_currency'] = $invoice['currency'];
    
    // Redirect to payment confirmation
    header('Location: client-payment-confirmation.php');
    exit();
    
} catch (Exception $e) {
    error_log("Payment processing error: " . $e->getMessage());
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: client-payments.php');
    exit();
}
?>
