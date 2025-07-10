<?php
include 'includes/config.php';
include 'includes/header.php';
require_once 'init.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

try {
    // Get booking details
    $stmt = $pdo->prepare("SELECT b.*, 
                          CASE 
                            WHEN b.booking_type = 'hotels' THEN h.name
                            WHEN b.booking_type = 'tours' THEN t.title
                            WHEN b.booking_type = 'cars' THEN c.model
                            WHEN b.booking_type = 'flights' THEN CONCAT(f.airline, ' ', f.flight_number)
                          END AS item_name,
                          CASE 
                            WHEN b.booking_type = 'hotels' THEN h.location
                            WHEN b.booking_type = 'tours' THEN t.location
                            WHEN b.booking_type = 'cars' THEN c.location
                            WHEN b.booking_type = 'flights' THEN CONCAT(f.departure_city, ' to ', f.arrival_city)
                          END AS item_location,
                          CASE 
                            WHEN b.booking_type = 'hotels' THEN h.image_path
                            WHEN b.booking_type = 'tours' THEN t.image_path
                            WHEN b.booking_type = 'cars' THEN c.image_path
                            WHEN b.booking_type = 'flights' THEN NULL
                          END AS item_image
                          FROM bookings b
                          LEFT JOIN hotels h ON b.booking_type = 'hotels' AND b.item_id = h.id
                          LEFT JOIN tours t ON b.booking_type = 'tours' AND b.item_id = t.id
                          LEFT JOIN cars c ON b.booking_type = 'cars' AND b.item_id = c.id
                          LEFT JOIN flights f ON b.booking_type = 'flights' AND b.item_id = f.id
                          WHERE b.id = ? AND b.user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$booking) {
        throw new Exception("Booking not found or you don't have permission to view it");
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<div class="container mt-5">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <h2>Booking #<?php echo $booking['id']; ?></h2>
                <p class="text-muted">Booked on <?php echo date('M d, Y H:i', strtotime($booking['booking_date'])); ?></p>
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0"><?php echo ucfirst($booking['booking_type']); ?> Details</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php if ($booking['item_image']): ?>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <img src="<?php echo $booking['item_image']; ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($booking['item_name']); ?>">
                                </div>
                                <div class="col-md-8">
                            <?php else: ?>
                                <div class="col-12">
                            <?php endif; ?>
                                <h5><?php echo htmlspecialchars($booking['item_name']); ?></h5>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($booking['item_location']); ?></p>
                                
                                <?php if ($booking['start_date']): ?>
                                    <p>
                                        <i class="fas fa-calendar-alt"></i> 
                                        <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                        <?php if ($booking['end_date']): ?>
                                            to <?php echo date('M d, Y', strtotime($booking['end_date'])); ?>
                                        <?php endif; ?>
                                    </p>
                                <?php endif; ?>
                                
                                <?php if ($booking['booking_type'] === 'hotel'): ?>
                                    <?php 
                                    $nights = (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24);
                                    $price_per_night = $booking['total_price'] / $nights;
                                    ?>
                                    <p><i class="fas fa-moon"></i> <?php echo $nights; ?> nights</p>
                                    <p><i class="fas fa-dollar-sign"></i> $<?php echo number_format($price_per_night, 2); ?> per night</p>
                                    
                                <?php elseif ($booking['booking_type'] === 'car'): ?>
                                    <?php 
                                    $days = (strtotime($booking['end_date']) - strtotime($booking['start_date'])) / (60 * 60 * 24);
                                    $price_per_day = $booking['total_price'] / $days;
                                    ?>
                                    <p><i class="fas fa-clock"></i> <?php echo $days; ?> days</p>
                                    <p><i class="fas fa-dollar-sign"></i> $<?php echo number_format($price_per_day, 2); ?> per day</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Booking Status</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <h5>Booking Status</h5>
                                <span class="badge 
                                    <?php echo $booking['status'] === 'confirmed' ? 'bg-success' : 
                                          ($booking['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning'); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </div>
                            <div class="col-md-6">
                                <h5>Payment Status</h5>
                                <span class="badge 
                                    <?php echo $booking['payment_status'] === 'paid' ? 'bg-success' : 
                                          ($booking['payment_status'] === 'refunded' ? 'bg-info' : 'bg-danger'); ?>">
                                    <?php echo ucfirst($booking['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($booking['total_price'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Taxes & Fees:</span>
                            <span>$<?php echo number_format($booking['total_price'] * 0.1, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold">
                            <span>Total:</span>
                            <span>$<?php echo number_format($booking['total_price'] * 1.1, 2); ?></span>
                        </div>
                        
                        <?php if ($booking['payment_status'] === 'unpaid' && $booking['status'] !== 'cancelled'): ?>
                            <hr>
                            <button class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                Make Payment
                            </button>
                        <?php endif; ?>
                        
                        <?php if ($booking['status'] === 'pending'): ?>
                            <hr>
                            <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-danger w-100">
                                Cancel Booking
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Modal -->
        <div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Make Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="paymentForm" action="process_payment.php" method="post">
                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                            <input type="hidden" name="amount" value="<?php echo $booking['total_price'] * 1.1; ?>">
                            
                            <div class="mb-3">
                                <label for="cardNumber" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="cardNumber" placeholder="1234 5678 9012 3456" required>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="expiryDate" class="form-label">Expiry Date</label>
                                    <input type="text" class="form-control" id="expiryDate" placeholder="MM/YY" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" placeholder="123" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="cardName" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="cardName" placeholder="John Doe" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" form="paymentForm" class="btn btn-primary">Pay $<?php echo number_format($booking['total_price'] * 1.1, 2); ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>