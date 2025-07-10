<?php
include_once 'init.php'; // Initialize session and other settings
require_once 'includes/config.php';

// Fetch terms content from database (if you want to manage it via admin panel)
try {
    $stmt = $pdo->query("SELECT content FROM site_pages WHERE page_name = 'terms' LIMIT 1");
    $terms_content = $stmt->fetchColumn();
} catch (PDOException $e) {
    $terms_content = ''; // Fallback content
}

// Default terms if not found in database
if (empty($terms_content)) {
    $terms_content = <<<HTML
    <h2>Terms and Conditions</h2>
    <p>Last updated: January 1, 2023</p>
    
    <h3>1. Introduction</h3>
    <p>Welcome to TravelEase! These terms and conditions outline the rules and regulations for the use of our website and services.</p>
    
    <h3>2. Bookings and Payments</h3>
    <p>All bookings are subject to availability. Prices are subject to change without notice until a booking is confirmed.</p>
    
    <h3>3. Cancellations and Refunds</h3>
    <p>Cancellation policies vary by service provider. Please review the specific cancellation policy for your booking.</p>
    
    <h3>4. Privacy</h3>
    <p>Your privacy is important to us. Please review our <a href="privacy.php">Privacy Policy</a>.</p>
    
    <h3>5. Limitation of Liability</h3>
    <p>TravelEase acts as an intermediary between you and service providers. We are not liable for any damages resulting from services provided by third parties.</p>
    
    <h3>6. Changes to Terms</h3>
    <p>We reserve the right to modify these terms at any time. Your continued use of the site constitutes acceptance of the modified terms.</p>
HTML;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terms and Conditions - TravelEase</title>
    <?php include 'includes/header.php'; ?>
    <style>
        .terms-content {
            background-color: #f8f9fa;
            padding: 2rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }
        .terms-content h2, .terms-content h3 {
            color: #4C1B24;
            margin-top: 1.5rem;
        }
        .terms-content p {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    
    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-12">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Terms and Conditions</li>
                    </ol>
                </nav>
                
                <div class="terms-content">
                    <?= $terms_content ?>
                </div>
                
                <div class="alert alert-info">
                    <p>If you have any questions about these Terms, please contact us at <a href="contact.php">contact@travelease.com</a>.</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>