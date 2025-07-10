<?php
session_start();
include '../includes/config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

$booking_id = $_GET['id'] ?? 0;

// Get booking details
try {
    $stmt = $pdo->prepare("SELECT b.*, u.name as user_name, u.email as user_email, u.phone as user_phone 
                           FROM bookings b 
                           JOIN users u ON b.user_id = u.id 
                           WHERE b.id = ?");
    $stmt->execute([$booking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$booking) {
        header("Location: manage-bookings.php");
        exit();
    }
    
    // Get item details based on booking type
    $item = [];
    switch($booking['booking_type']) {
        case 'flight':
            $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
            $stmt->execute([$booking['item_id']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            $item_type = 'Flight';
            break;
            
        case 'hotel':
            $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
            $stmt->execute([$booking['item_id']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            $item_type = 'Hotel';
            break;
            
        case 'car':
            $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
            $stmt->execute([$booking['item_id']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            $item_type = 'Car Rental';
            break;
            
        case 'tour':
            $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
            $stmt->execute([$booking['item_id']]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            $item_type = 'Tour';
            break;
    }
} catch (PDOException $e) {
    $error = "Error fetching booking details: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Details | TravelEase Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background: #343a40;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.75);
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h2 class="h3">Booking Details #<?php echo $booking_id; ?></h2>
                    <a href="manage-bookings.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Bookings
                    </a>
                </div>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Customer Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Name:</th>
                                        <td><?php echo $booking['user_name']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Email:</th>
                                        <td><?php echo $booking['user_email']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Phone:</th>
                                        <td><?php echo $booking['user_phone'] ?? 'N/A'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header">
                                <h5>Booking Information</h5>
                            </div>
                            <div class="card-body">
                                <table class="table table-sm">
                                    <tr>
                                        <th>Booking ID:</th>
                                        <td><?php echo $booking['id']; ?></td>
                                    </tr>
                                    <tr>
                                        <th>Booking Type:</th>
                                        <td><?php echo ucfirst($booking['booking_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Booking Date:</th>
                                        <td><?php echo date('M d, Y H:i', strtotime($booking['booking_date'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status:</th>
                                        <td>
                                            <span class="badge 
                                                <?php echo $booking['status'] == 'confirmed' ? 'bg-success' : 
                                                      ($booking['status'] == 'pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                                <?php echo ucfirst($booking['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Total Amount:</th>
                                        <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h5><?php echo $item_type; ?> Details</h5>
                            </div>
                            <div class="card-body">
                                <?php if($booking['booking_type'] == 'flight'): ?>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Airline:</th>
                                            <td><?php echo $item['airline']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Flight Number:</th>
                                            <td><?php echo $item['flight_number']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Departure:</th>
                                            <td>
                                                <?php echo $item['departure_city']; ?> at 
                                                <?php echo date('M d, Y H:i', strtotime($item['departure_date'])); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Arrival:</th>
                                            <td>
                                                <?php echo $item['arrival_city']; ?> at 
                                                <?php echo date('M d, Y H:i', strtotime($item['arrival_date'])); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Price:</th>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        </tr>
                                    </table>
                                
                                <?php elseif($booking['booking_type'] == 'hotel'): ?>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Hotel Name:</th>
                                            <td><?php echo $item['name']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Location:</th>
                                            <td><?php echo $item['location']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Check-in:</th>
                                            <td><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Check-out:</th>
                                            <td><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Price per night:</th>
                                            <td>$<?php echo number_format($item['price_per_night'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Nights:</th>
                                            <td>
                                                <?php 
                                                $nights = (strtotime($booking['check_out_date']) - strtotime($booking['check_in_date'])) / (60 * 60 * 24);
                                                echo $nights;
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                
                                <?php elseif($booking['booking_type'] == 'car'): ?>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Car Model:</th>
                                            <td><?php echo $item['model']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Type:</th>
                                            <td><?php echo ucfirst($item['type']); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Rental Company:</th>
                                            <td><?php echo $item['rental_company']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Pick-up Date:</th>
                                            <td><?php echo date('M d, Y', strtotime($booking['check_in_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Drop-off Date:</th>
                                            <td><?php echo date('M d, Y', strtotime($booking['check_out_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Price per day:</th>
                                            <td>$<?php echo number_format($item['price_per_day'], 2); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Total Days:</th>
                                            <td>
                                                <?php 
                                                $days = (strtotime($booking['check_out_date']) - strtotime($booking['check_in_date'])) / (60 * 60 * 24);
                                                echo $days;
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                
                                <?php elseif($booking['booking_type'] == 'tour'): ?>
                                    <table class="table table-sm">
                                        <tr>
                                            <th>Tour Title:</th>
                                            <td><?php echo $item['title']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Location:</th>
                                            <td><?php echo $item['location']; ?></td>
                                        </tr>
                                        <tr>
                                            <th>Start Date:</th>
                                            <td><?php echo date('M d, Y', strtotime($item['start_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>End Date:</th>
                                            <td><?php echo date('M d, Y', strtotime($item['end_date'])); ?></td>
                                        </tr>
                                        <tr>
                                            <th>Duration:</th>
                                            <td><?php echo $item['duration_days']; ?> days</td>
                                        </tr>
                                        <tr>
                                            <th>Price:</th>
                                            <td>$<?php echo number_format($item['price'], 2); ?></td>
                                        </tr>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>