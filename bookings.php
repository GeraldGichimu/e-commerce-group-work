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

try {
    // Get user's bookings
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
                          END AS item_location
                          FROM bookings b
                          LEFT JOIN hotels h ON b.booking_type = 'hotels' AND b.item_id = h.id
                          LEFT JOIN tours t ON b.booking_type = 'tours' AND b.item_id = t.id
                          LEFT JOIN cars c ON b.booking_type = 'cars' AND b.item_id = c.id
                          LEFT JOIN flights f ON b.booking_type = 'flights' AND b.item_id = f.id
                          WHERE b.user_id = ?
                          ORDER BY b.booking_date DESC");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching bookings: " . $e->getMessage();
}
?>

<div class="container mt-5">
    <h2 class="mb-4">My Bookings</h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif (empty($bookings)): ?>
        <div class="alert alert-info">You have no bookings yet.</div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Type</th>
                        <th>Item</th>
                        <th>Location</th>
                        <th>Dates</th>
                        <th>Total Price</th>
                        <th>Status</th>
                        <th>Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#<?php echo $booking['id']; ?></td>
                            <td><?php echo ucfirst($booking['booking_type']); ?></td>
                            <td><?php echo htmlspecialchars($booking['item_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['item_location']); ?></td>
                            <td>
                                <?php if ($booking['start_date']): ?>
                                    <?php echo date('M d, Y', strtotime($booking['start_date'])); ?>
                                    <?php if ($booking['end_date']): ?>
                                        - <?php echo date('M d, Y', strtotime($booking['end_date'])); ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </td>
                            <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                            <td>
                                <span class="badge 
                                    <?php echo $booking['status'] === 'confirmed' ? 'bg-success' : 
                                          ($booking['status'] === 'cancelled' ? 'bg-danger' : 'bg-warning'); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge 
                                    <?php echo $booking['payment_status'] === 'paid' ? 'bg-success' : 
                                          ($booking['payment_status'] === 'refunded' ? 'bg-info' : 'bg-danger'); ?>">
                                    <?php echo ucfirst($booking['payment_status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-primary">Details</a>
                                <?php if ($booking['status'] === 'pending'): ?>
                                    <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-danger">Cancel</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>