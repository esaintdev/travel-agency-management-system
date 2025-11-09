<?php
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

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_agreement') {
    if (isset($_FILES['signed_agreement']) && $_FILES['signed_agreement']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/agreements/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['signed_agreement']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['pdf', 'jpg', 'jpeg', 'png'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $file_name = 'agreement_' . $client['reference_id'] . '_' . date('Y-m-d_H-i-s') . '.' . $file_extension;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['signed_agreement']['tmp_name'], $file_path)) {
                // Update client record with agreement file
                try {
                    $stmt = $db->prepare("UPDATE clients SET signed_agreement_file = ?, agreement_signed_date = NOW() WHERE id = ?");
                    $stmt->execute([$file_path, $client['id']]);
                    
                    // Log activity
                    logActivity(null, $client['id'], 'Service Agreement Uploaded', 
                               "Client {$client['reference_id']} uploaded signed service agreement", $db);
                    
                    $_SESSION['success_message'] = "Service Agreement uploaded successfully! Our team will review it shortly.";
                    
                    // TODO: Send notification to admin team
                    
                } catch (Exception $e) {
                    error_log("Error updating agreement file: " . $e->getMessage());
                    $_SESSION['error_message'] = "Error saving agreement information.";
                }
            } else {
                $_SESSION['error_message'] = "Error uploading file. Please try again.";
            }
        } else {
            $_SESSION['error_message'] = "Invalid file type. Please upload PDF, JPG, JPEG, or PNG files only.";
        }
    } else {
        $_SESSION['error_message'] = "Please select a file to upload.";
    }
    
    header('Location: client-service-agreement.php');
    exit();
}

// Check if client has already uploaded agreement
$has_signed_agreement = !empty($client['signed_agreement_file']) && file_exists($client['signed_agreement_file']);
$agreement_date = $client['agreement_signed_date'] ?? null;

// Get current date for agreement
$current_date = date('F j, Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Agreement - M25 Travel & Tour</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .agreement-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .agreement-header {
            background: linear-gradient(135deg, #13357B 0%, #1e4a8c 100%);
            color: white;
            padding: 30px;
            border-radius: 15px 15px 0 0;
            text-align: center;
        }
        .agreement-content {
            padding: 40px;
            line-height: 1.8;
            color: #333;
        }
        .agreement-content h3 {
            color: #13357B;
            margin-top: 30px;
            margin-bottom: 20px;
            font-weight: 700;
        }
        .agreement-content h4 {
            color: #2c5aa0;
            margin-top: 25px;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .signature-section {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            margin: 30px 0;
            border: 2px dashed #13357B;
        }
        .action-buttons {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 15px 15px;
            text-align: center;
        }
        .btn-action {
            margin: 10px;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 25px;
            transition: all 0.3s;
        }
        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .upload-section {
            background: #e3f2fd;
            border: 2px dashed #2196f3;
            border-radius: 10px;
            padding: 30px;
            margin: 30px 0;
            text-align: center;
        }
        .upload-success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .client-info {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .terms-highlight {
            background: #f8d7da;
            border: 1px solid #dc3545;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        @media print {
            .no-print { display: none !important; }
            .agreement-container { box-shadow: none; border: 1px solid #ddd; }
            .action-buttons { display: none !important; }
            body { background: white; }
        }
        .watermark {
            position: relative;
            overflow: hidden;
        }
        .watermark::before {
            content: "M25 TRAVEL & TOUR";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(19, 53, 123, 0.05);
            font-weight: bold;
            z-index: 1;
            pointer-events: none;
        }
        .content-overlay {
            position: relative;
            z-index: 2;
        }
    </style>
</head>

<body>
    <?php include 'includes/client-sidebar.php'; ?>
    
    <div class="container-fluid p-4">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="agreement-container watermark">
                    <div class="content-overlay">
                        <div class="agreement-header">
                            <h1><i class="fas fa-file-contract me-3"></i>SERVICE AGREEMENT</h1>
                            <h3>M25 Travel & Tour Global Services</h3>
                            <p class="mb-0">Professional Travel Advisory & Visa Consultation Services</p>
                        </div>

                        <div class="agreement-content" id="agreement-content">
                            <div class="text-center mb-4">
                                <h2><strong>M25 TRAVEL & TOUR CLIENT SERVICE AGREEMENT</strong></h2>
                            </div>

                            <p>This Agreement ("Agreement") is entered into by and between:</p>
                            
                            <p><strong>M25 Travel & Tour</strong> ("M25", "we", "our", "us")<br>
                            and<br>
                            <strong>Client Name:</strong> <u><?php echo htmlspecialchars($client['full_name']); ?></u> ("Client", "you", "your")</p>
                            
                            <p><strong>Effective Date:</strong> <u><?php echo $current_date; ?></u></p>
                            
                            <p>This Agreement governs the terms of service, payment, obligations, and responsibilities for visa processing advisory and related travel consultation services provided by M25 Travel & Tour globally.</p>

                            <h3>1. Scope of Services</h3>
                            <p>M25 Travel & Tour will provide:</p>
                            <ul>
                                <li>Global visa application guidance (tourist, study, work, business, family visit, or other legal visa categories).</li>
                                <li>Review and advice on supporting documents required by the relevant embassy/consulate.</li>
                                <li>Assistance with travel itinerary, bookings, and documentation preparation as requested.</li>
                                <li>Guidance for obtaining required documents; third-party service providers may be involved and M25 does not assume responsibility for their service quality or charges.</li>
                                <li>Professional consultation and communication throughout the visa process.</li>
                            </ul>
                            
                            <h4>Service Timeframe:</h4>
                            <p>Service duration varies depending on client responsiveness and document availability. M25 will begin processing only when the client provides all required documents or authorizations.</p>

                            <h3>2. Fees and Payment Terms</h3>
                            <p><strong>Total Service Fee:</strong> USD 3,000 per visa application.</p>
                            
                            <h4>Payment Schedule:</h4>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Payment Type</th>
                                            <th>Amount</th>
                                            <th>Conditions</th>
                                            <th>Refundability</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>Deposit / Commitment Fee</td>
                                            <td>USD 500</td>
                                            <td>Paid upfront to start processing</td>
                                            <td><strong>Non-refundable</strong></td>
                                        </tr>
                                        <tr>
                                            <td>Final Balance</td>
                                            <td>USD 2,500</td>
                                            <td>Paid only after visa approval confirmation</td>
                                            <td>N/A</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h4>No Visa – No Fee:</h4>
                            <p>If the visa is denied, the client is not required to pay the remaining balance. Only the deposit is retained as a commitment to the service.</p>

                            <div class="terms-highlight">
                                <h4>Deposit Clarification:</h4>
                                <ul>
                                    <li>The $500 deposit secures your place in our processing queue.</li>
                                    <li><strong>The deposit is non-refundable under any circumstances</strong>, including client withdrawal, change of mind, delay in providing documents, or embassy refusal.</li>
                                    <li>Payment of deposit constitutes acceptance of this Agreement and agreement to proceed under its terms.</li>
                                </ul>
                            </div>

                            <div class="client-info">
                                <h4>Final Balance Clarification:</h4>
                                <ul>
                                    <li>The remaining balance of USD 2,500 is due immediately upon visa approval notification.</li>
                                    <li>Failure to pay the balance within 5 business days of approval may result in account suspension and legal remedies for recovery.</li>
                                </ul>
                            </div>

                            <h3>3. Client Responsibilities</h3>
                            <p>The Client agrees to:</p>
                            <ul>
                                <li>Provide accurate, complete, and truthful information and documents for visa processing.</li>
                                <li>Respond promptly to requests for documents, clarifications, or authorizations.</li>
                                <li>Ensure all required personal, financial, and travel documentation is genuine.</li>
                                <li>Understand that visa approval is at the sole discretion of the embassy/consulate, not M25.</li>
                                <li>Accept responsibility for all government, embassy, and third-party charges (biometrics, courier fees, document fees, flight booking, travel insurance, etc.).</li>
                                <li>Understand that if M25 assists with document procurement via third parties, M25 is not responsible for third-party service quality or charges. We only provide advice and guidance on how to obtain these documents.</li>
                            </ul>

                            <h3>4. Limitation of Liability</h3>
                            <p>M25 provides professional guidance and document review, but visa approval is solely determined by the embassy/consulate.</p>
                            
                            <p><strong>M25 is not liable for:</strong></p>
                            <ul>
                                <li>Visa denial or delay</li>
                                <li>Embassy processing errors</li>
                                <li>Immigration authority decisions at entry</li>
                                <li>Third-party service errors or charges</li>
                                <li>Any direct or indirect loss, including financial, resulting from the visa application process</li>
                            </ul>
                            
                            <p>The Client agrees that the deposit is non-refundable, and by paying it, acknowledges acceptance of all risks related to visa outcomes.</p>

                            <h3>5. Termination</h3>
                            <p>This Agreement automatically terminates upon:</p>
                            <ul>
                                <li>Payment of full fees following visa approval</li>
                                <li>Withdrawal by client after deposit payment (deposit remains non-refundable)</li>
                            </ul>
                            <p>M25 may terminate the Agreement if the client fails to provide documents, misrepresents information, or violates any terms. Deposit remains non-refundable.</p>

                            <h3>6. Governing Law & Jurisdiction</h3>
                            <p>This Agreement is governed by international commercial law principles and, where applicable, the jurisdiction of the country where M25 Travel & Tour is registered.</p>
                            <p>Any disputes will be resolved in accordance with the governing law and through amicable negotiation before any legal action.</p>

                            <h3>7. Entire Agreement</h3>
                            <p>This Agreement constitutes the entire understanding between M25 Travel & Tour and the Client regarding visa processing services.</p>
                            <p>Any prior agreements, promises, or representations are superseded by this document.</p>
                            <p>No modifications or amendments are valid unless in writing and signed by both parties.</p>

                            <h3>8. Client Acknowledgment</h3>
                            <p>By signing or submitting the deposit, the Client acknowledges and agrees to:</p>
                            <ul>
                                <li>The non-refundable deposit policy</li>
                                <li>Payment structure for final balance upon approval</li>
                                <li>Risks associated with visa approval</li>
                                <li>M25's limitation of liability</li>
                                <li>Responsibility for timely submission of accurate documents</li>
                                <li>Terms regarding third-party services</li>
                            </ul>

                            <div class="signature-section">
                                <h3><i class="fas fa-signature me-2"></i>Signatures</h3>
                                <div class="row mt-5">
                                    <div class="col-md-6">
                                        <p><strong>Client Name:</strong> <?php echo htmlspecialchars($client['full_name']); ?></p>
                                        <p><strong>Signature:</strong></p>
                                        <div style="border-bottom: 2px solid #333; height: 60px; margin: 20px 0;"></div>
                                        <p><strong>Date:</strong> _________________</p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>M25 Travel & Tour Representative:</strong></p>
                                        <p><strong>Signature:</strong></p>
                                        <div style="border-bottom: 2px solid #333; height: 60px; margin: 20px 0;"></div>
                                        <p><strong>Date:</strong> <?php echo $current_date; ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <hr>
                                <p><strong>M25 Travel & Tour Global Services</strong></p>
                                <p>Email: info@m25travelagency.com | Phone: +233 59 260 5752</p>
                                <p>Website: www.m25travelagency.com</p>
                            </div>
                        </div>

                        <div class="action-buttons no-print">
                            <h4 class="mb-4"><i class="fas fa-tools me-2"></i>Agreement Actions</h4>
                            
                            <button onclick="downloadPDF()" class="btn btn-primary btn-action">
                                <i class="fas fa-download me-2"></i>Download PDF
                            </button>
                            
                            <button onclick="window.print()" class="btn btn-success btn-action">
                                <i class="fas fa-print me-2"></i>Print Agreement
                            </button>
                            
                            <a href="client-dashboard.php" class="btn btn-secondary btn-action">
                                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Upload Section -->
                <div class="agreement-container no-print">
                    <div class="content-overlay">
                        <?php if ($has_signed_agreement): ?>
                        <div class="upload-section upload-success">
                            <h4><i class="fas fa-check-circle me-2"></i>Agreement Uploaded Successfully!</h4>
                            <p>You have successfully uploaded your signed service agreement on <?php echo date('F j, Y', strtotime($agreement_date)); ?>.</p>
                            <p>Our team will review your agreement and contact you if any additional information is needed.</p>
                            <div class="mt-3">
                                <i class="fas fa-file-check fa-3x text-success"></i>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="upload-section">
                            <h4><i class="fas fa-cloud-upload-alt me-2"></i>Upload Signed Agreement</h4>
                            <p>After printing and signing the agreement above, please upload the signed document here.</p>
                            
                            <form method="POST" enctype="multipart/form-data" class="mt-4">
                                <input type="hidden" name="action" value="upload_agreement">
                                
                                <div class="mb-3">
                                    <label for="signed_agreement" class="form-label">
                                        <i class="fas fa-file-upload me-2"></i>Select Signed Agreement File
                                    </label>
                                    <input type="file" class="form-control" id="signed_agreement" name="signed_agreement" 
                                           accept=".pdf,.jpg,.jpeg,.png" required>
                                    <div class="form-text">
                                        Accepted formats: PDF, JPG, JPEG, PNG (Max size: 10MB)
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-action">
                                    <i class="fas fa-upload me-2"></i>Upload Signed Agreement
                                </button>
                            </form>
                            
                            <div class="mt-4">
                                <small class="text-muted">
                                    <i class="fas fa-shield-alt me-1"></i>
                                    Your uploaded documents are securely stored and protected according to our Privacy Policy.
                                </small>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    
    <script>
        // Disable right-click context menu on agreement content
        document.getElementById('agreement-content').addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        // Disable text selection on agreement content
        document.getElementById('agreement-content').style.userSelect = 'none';
        document.getElementById('agreement-content').style.webkitUserSelect = 'none';
        document.getElementById('agreement-content').style.mozUserSelect = 'none';
        document.getElementById('agreement-content').style.msUserSelect = 'none';

        // Download PDF function
        function downloadPDF() {
            const { jsPDF } = window.jspdf;
            const element = document.getElementById('agreement-content');
            
            // Show loading
            const btn = event.target;
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Generating PDF...';
            btn.disabled = true;
            
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                allowTaint: true
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const pdf = new jsPDF('p', 'mm', 'a4');
                const imgWidth = 210;
                const pageHeight = 295;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                pdf.save('M25_Service_Agreement_<?php echo $client['reference_id']; ?>.pdf');
                
                // Reset button
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(error => {
                console.error('Error generating PDF:', error);
                alert('Error generating PDF. Please try again or use the print option.');
                
                // Reset button
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        // File upload validation
        document.getElementById('signed_agreement').addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    alert('File size must be less than 10MB');
                    this.value = '';
                    return;
                }
                
                const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png'];
                if (!allowedTypes.includes(file.type)) {
                    alert('Please select a PDF, JPG, JPEG, or PNG file');
                    this.value = '';
                    return;
                }
            }
        });
    </script>
    
    <?php include 'includes/client-sidebar-close.php'; ?>
</body>
</html>
