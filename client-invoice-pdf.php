<?php
/**
 * Client Invoice PDF - Generate PDF view for client invoices
 */

// Start session and include required files
session_start();
require_once 'config.php';
require_once 'includes/client-auth.php';

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

// Get invoice ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "Invoice ID is required.";
    header('Location: client-payments.php');
    exit();
}

$invoice_id = intval($_GET['id']);

try {
    // Get invoice details (ensure it belongs to current client)
    $stmt = $db->prepare("
        SELECT i.*, a.full_name as created_by_name
        FROM invoices i 
        LEFT JOIN admin_users a ON i.created_by = a.id
        WHERE i.id = ? AND i.client_id = ?
    ");
    $stmt->execute([$invoice_id, $client['id']]);
    $invoice = $stmt->fetch();
    
    if (!$invoice) {
        $_SESSION['error_message'] = "Invoice not found or access denied.";
        header('Location: client-payments.php');
        exit();
    }
    
    // Get payment history for this invoice
    $stmt = $db->prepare("
        SELECT p.*
        FROM payments p 
        WHERE p.invoice_id = ? AND p.client_id = ? AND p.payment_status = 'completed'
        ORDER BY p.payment_date ASC
    ");
    $stmt->execute([$invoice_id, $client['id']]);
    $payments = $stmt->fetchAll();
    
    // Calculate totals
    $total_paid = 0;
    foreach ($payments as $payment) {
        $total_paid += $payment['amount'];
    }
    $balance_remaining = $invoice['amount'] - $total_paid;
    
} catch (Exception $e) {
    error_log("Client invoice PDF error: " . $e->getMessage());
    $_SESSION['error_message'] = "Error loading invoice details.";
    header('Location: client-payments.php');
    exit();
}

// Set content type for PDF display
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice <?php echo htmlspecialchars($invoice['invoice_number']); ?> - M25 Travel & Tour Agency</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
            .invoice-container { box-shadow: none; margin: 0; }
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            color: #333;
        }
        
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        
        .invoice-header {
            border-bottom: 3px solid #13357B;
            padding-bottom: 30px;
            margin-bottom: 30px;
        }
        
        .company-info {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .company-logo {
            font-size: 32px;
            font-weight: bold;
            color: #13357B;
            margin-bottom: 10px;
        }
        
        .company-details {
            color: #666;
            line-height: 1.6;
        }
        
        .invoice-title {
            text-align: center;
            font-size: 36px;
            font-weight: bold;
            color: #13357B;
            margin: 30px 0;
        }
        
        .invoice-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
        }
        
        .invoice-details, .client-details {
            width: 48%;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #13357B;
            margin-bottom: 15px;
            border-bottom: 2px solid #FEA116;
            padding-bottom: 5px;
        }
        
        .detail-row {
            margin-bottom: 8px;
            display: flex;
        }
        
        .detail-label {
            font-weight: bold;
            width: 120px;
            color: #555;
        }
        
        .detail-value {
            flex: 1;
        }
        
        .invoice-items {
            margin: 40px 0;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        
        .items-table th {
            background: #13357B;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .items-table tr:nth-child(even) {
            background: #f9f9f9;
        }
        
        .total-section {
            text-align: right;
            margin-top: 30px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .total-row.final {
            border-bottom: 3px solid #13357B;
            font-size: 20px;
            font-weight: bold;
            color: #13357B;
        }
        
        .payment-history {
            margin-top: 40px;
        }
        
        .payment-item {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        
        .footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #eee;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-overdue { background: #f8d7da; color: #721c24; }
        .status-cancelled { background: #e2e3e5; color: #383d41; }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #13357B;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(19, 53, 123, 0.3);
            transition: all 0.3s ease;
        }
        
        .print-button:hover {
            background: #0f2a5f;
            transform: translateY(-2px);
        }
        
        .client-copy {
            background: linear-gradient(135deg, #FEA116 0%, #13357B 100%);
            color: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .invoice-meta {
                flex-direction: column;
            }
            
            .invoice-details, .client-details {
                width: 100%;
                margin-bottom: 20px;
            }
            
            .detail-row {
                flex-direction: column;
            }
            
            .detail-label {
                width: auto;
                margin-bottom: 5px;
            }
        }
    </style>
</head>

<body>
    <button class="print-button no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Invoice
    </button>
    
    <div class="invoice-container">
        <!-- Client Copy Notice -->
        <div class="client-copy no-print">
            📄 CLIENT COPY - Invoice for <?php echo htmlspecialchars($client['full_name']); ?>
        </div>
        
        <!-- Company Header -->
        <div class="company-info">
            <div class="company-logo">
                🌍 M25 Travel & Tour Agency
            </div>
            <div class="company-details">
                Professional Visa & Immigration Services<br>
                Email: info@m25travelagency.com | Phone: +233 59 260 5752<br>
                Website: www.m25travelagency.com
            </div>
        </div>
        
        <!-- Invoice Title -->
        <div class="invoice-title">INVOICE</div>
        
        <!-- Invoice Meta Information -->
        <div class="invoice-meta">
            <div class="invoice-details">
                <div class="section-title">Invoice Details</div>
                <div class="detail-row">
                    <span class="detail-label">Invoice #:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($invoice['invoice_number']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Issue Date:</span>
                    <span class="detail-value"><?php echo formatDate($invoice['created_at'], 'F d, Y'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due Date:</span>
                    <span class="detail-value"><?php echo formatDate($invoice['due_date'], 'F d, Y'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value">
                        <span class="status-badge status-<?php echo $invoice['status']; ?>">
                            <?php echo ucfirst($invoice['status']); ?>
                        </span>
                    </span>
                </div>
                <?php if ($invoice['created_by_name']): ?>
                <div class="detail-row">
                    <span class="detail-label">Created by:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($invoice['created_by_name']); ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="client-details">
                <div class="section-title">Bill To</div>
                <div class="detail-row">
                    <span class="detail-label">Client:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($client['full_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Reference ID:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($client['reference_id']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($client['client_email']); ?></span>
                </div>
                <?php if ($client['phone_number']): ?>
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($client['phone_number']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($client['visa_type']): ?>
                <div class="detail-row">
                    <span class="detail-label">Visa Type:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($client['visa_type']); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($client['address']): ?>
                <div class="detail-row">
                    <span class="detail-label">Address:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($client['address']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Invoice Items -->
        <div class="invoice-items">
            <div class="section-title">Invoice Items</div>
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th>Type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['description']); ?></td>
                        <td><?php echo ucfirst(str_replace('_', ' ', $invoice['invoice_type'])); ?></td>
                        <td><?php echo formatCurrencyByCode($invoice['amount'], $invoice['currency']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <!-- Totals -->
        <div class="total-section">
            <div style="width: 300px; margin-left: auto;">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span><?php echo formatCurrencyByCode($invoice['amount'], $invoice['currency']); ?></span>
                </div>
                <div class="total-row">
                    <span>Tax:</span>
                    <span><?php echo formatCurrencyByCode(0, $invoice['currency']); ?></span>
                </div>
                <div class="total-row final">
                    <span>Total Amount:</span>
                    <span><?php echo formatCurrencyByCode($invoice['amount'], $invoice['currency']); ?></span>
                </div>
                
                <?php if ($total_paid > 0): ?>
                <div class="total-row" style="color: #28a745;">
                    <span>Amount Paid:</span>
                    <span><?php echo formatCurrencyByCode($total_paid, $invoice['currency']); ?></span>
                </div>
                <div class="total-row" style="color: <?php echo $balance_remaining > 0 ? '#dc3545' : '#28a745'; ?>;">
                    <span>Balance Due:</span>
                    <span><?php echo formatCurrencyByCode($balance_remaining, $invoice['currency']); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Payment History -->
        <?php if (!empty($payments)): ?>
        <div class="payment-history">
            <div class="section-title">Payment History</div>
            <?php foreach ($payments as $payment): ?>
            <div class="payment-item">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                    <strong>Payment: <?php echo htmlspecialchars($payment['payment_reference']); ?></strong>
                    <strong><?php echo formatCurrencyByCode($payment['amount'], $payment['currency']); ?></strong>
                </div>
                <div style="font-size: 14px; color: #666;">
                    <div>Method: <?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></div>
                    <div>Date: <?php echo formatDate($payment['payment_date'], 'F d, Y'); ?></div>
                    <?php if ($payment['transaction_id']): ?>
                        <div>Transaction ID: <?php echo htmlspecialchars($payment['transaction_id']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <!-- Payment Instructions -->
        <?php if ($invoice['status'] === 'pending' && $balance_remaining > 0): ?>
        <div style="margin-top: 40px; background: #fff3cd; padding: 20px; border-radius: 10px; border-left: 4px solid #ffc107;">
            <div class="section-title" style="color: #856404; border-color: #856404;">💳 Payment Instructions</div>
            <p style="line-height: 1.6; color: #856404; margin-bottom: 15px;">
                To pay this invoice, please log in to your client portal at our website or contact us directly:
            </p>
            <ul style="color: #856404; line-height: 1.8;">
                <li><strong>Online:</strong> Visit your client dashboard to make secure payments</li>
                <li><strong>Phone:</strong> Call us at +233 59 260 5752</li>
                <li><strong>Email:</strong> Send payment confirmation to payments@m25travelagency.com</li>
                <li><strong>Reference:</strong> Always include invoice number <?php echo htmlspecialchars($invoice['invoice_number']); ?></li>
            </ul>
        </div>
        <?php endif; ?>
        
        <!-- Terms and Notes -->
        <div style="margin-top: 40px;">
            <div class="section-title">Payment Terms</div>
            <p style="line-height: 1.6; color: #666;">
                Payment is due within 30 days of invoice date. Late payments may incur additional charges. 
                Please include the invoice number with your payment. For any questions regarding this invoice, 
                please contact us at info@m25travelagency.com or +233 59 260 5752.
            </p>
            
            <?php if ($invoice['notes']): ?>
            <div class="section-title">Additional Notes</div>
            <p style="line-height: 1.6; color: #666; white-space: pre-wrap;"><?php echo htmlspecialchars($invoice['notes']); ?></p>
            <?php endif; ?>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for choosing M25 Travel & Tour Agency!</strong></p>
            <p>Your trusted partner for visa and immigration services</p>
            <p style="margin-top: 20px; font-size: 12px;">
                This invoice was generated on <?php echo date('F d, Y \a\t g:i A'); ?><br>
                Client Reference: <?php echo htmlspecialchars($client['reference_id']); ?>
            </p>
        </div>
    </div>
    
    <script>
        // Auto-focus for printing
        window.onload = function() {
            // Add print styles
            const style = document.createElement('style');
            style.textContent = `
                @media print {
                    body { margin: 0; padding: 0; }
                    .invoice-container { margin: 0; padding: 20px; box-shadow: none; }
                }
            `;
            document.head.appendChild(style);
        };
        
        // Keyboard shortcut for printing
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>
