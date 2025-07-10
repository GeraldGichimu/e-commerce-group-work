<?php
include 'includes/config.php';
require_once 'init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($booking_id <= 0) {
    header("Location: bookings.php");
    exit();
}

try {
    // Verify the booking belongs to the user
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        $_SESSION['error'] = "Booking not found or you don't have permission to cancel it";
        header("Location: bookings.php");
        exit();
    }
    
    // Check if booking can be cancelled
    if ($booking['status'] !== 'pending') {
        $_SESSION['error'] = "Only pending bookings can be cancelled";
        header("Location: booking_details.php?id=$booking_id");
        exit();
    }
    
    // Update booking status
    $update_stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $update_stmt->execute([$booking_id]);
    
    // Update availability if needed
    if ($booking['booking_type'] === 'car') {
        $pdo->query("UPDATE cars SET available = 1 WHERE id = {$booking['item_id']}");
    } elseif ($booking['booking_type'] === 'flight') {
        // Need to determine how many seats to return
        $pdo->query("UPDATE flights SET seats_available = seats_available + 1 WHERE id = {$booking['item_id']}");
    }
    
    $_SESSION['success'] = "Booking #$booking_id has been cancelled successfully";
    header("Location: booking_details.php?id=$booking_id");
    exit();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error cancelling booking: " . $e->getMessage();
    header("Location: booking_details.php?id=$booking_id");
    exit();
}
?>