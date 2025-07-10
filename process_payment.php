<?php
include 'includes/config.php';

// Check if user is logged in
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;
$amount = isset($_POST['amount']) ? (float)$_POST['amount'] : 0;

if ($booking_id <= 0 || $amount <= 0) {
    $_SESSION['error'] = "Invalid payment request";
    header("Location: bookings.php");
    exit();
}

try {
    // Verify the booking belongs to the user and is unpaid
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND payment_status = 'unpaid'");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        $_SESSION['error'] = "Booking not found, already paid, or you don't have permission";
        header("Location: bookings.php");
        exit();
    }
    
    // Here you would typically integrate with a payment gateway
    // For this example, we'll just simulate a successful payment
    
    // Update payment status
    $update_stmt = $pdo->prepare("UPDATE bookings SET payment_status = 'paid', status = 'confirmed' WHERE id = ?");
    $update_stmt->execute([$booking_id]);
    
    // Record payment transaction
    $transaction_stmt = $pdo->prepare("INSERT INTO transactions 
                                     (booking_id, amount, payment_method, transaction_date) 
                                     VALUES (?, ?, 'credit_card', NOW())");
    $transaction_stmt->execute([$booking_id, $amount]);
    
    $_SESSION['success'] = "Payment of $" . number_format($amount, 2) . " for booking #$booking_id was successful!";
    header("Location: booking_details.php?id=$booking_id");
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error processing payment: " . $e->getMessage();
    header("Location: booking_details.php?id=$booking_id");
    exit();
}
?>