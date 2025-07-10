<?php
session_start();
include '../includes/config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Get counts for dashboard
try {
    // Total bookings
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings");
    $total_bookings = $stmt->fetchColumn();
    
    // Recent bookings (last 7 days)
    $stmt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $recent_bookings = $stmt->fetchColumn();
    
    // Total revenue
    $stmt = $pdo->query("SELECT SUM(total_price) FROM bookings WHERE status = 'confirmed'");
    $total_revenue = $stmt->fetchColumn() ?? 0;
    
    // Booking types
    $stmt = $pdo->query("SELECT booking_type, COUNT(*) as count FROM bookings GROUP BY booking_type");
    $booking_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching dashboard data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | TravelEase</title>
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
        .card-dashboard {
            transition: transform 0.3s;
        }
        .card-dashboard:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar bg-dark">
                <div class="text-center py-4">
                    <h4 class="text-white">TravelEase Admin</h4>
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-bookings.php">
                            <i class="fas fa-calendar-check me-2"></i>Bookings
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-flights.php">
                            <i class="fas fa-plane me-2"></i>Flights
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-hotels.php">
                            <i class="fas fa-hotel me-2"></i>Hotels
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-cars.php">
                            <i class="fas fa-car me-2"></i>Car Rentals
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-tours.php">
                            <i class="fas fa-route me-2"></i>Tours
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contacts.php">
                            <i class="fas fa-phone me-2"></i>Contacts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="edit_page.php?page=terms">
                            <i class="fas fa-pen me-2"></i>Terms
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="edit_page.php?page=privacy">
                            <i class="fas fa-lock me-2"></i>Privacy
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage-admins.php">
                            <i class="fas fa-cogs me-2"></i>Manage Admins
                        </a>
                    </li>
                    <li class="nav-item mt-3">
                        <a class="nav-link text-danger" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <h2 class="h3 mb-4">Dashboard Overview</h2>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="card card-dashboard bg-primary text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Bookings</h5>
                                <h2 class="display-6"><?php echo $total_bookings; ?></h2>
                                <p class="card-text">All-time bookings</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-dashboard bg-success text-white">
                            <div class="card-body">
                                <h5 class="card-title">Recent Bookings</h5>
                                <h2 class="display-6"><?php echo $recent_bookings; ?></h2>
                                <p class="card-text">Last 7 days</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card card-dashboard bg-info text-white">
                            <div class="card-body">
                                <h5 class="card-title">Total Revenue</h5>
                                <h2 class="display-6">$<?php echo number_format($total_revenue, 2); ?></h2>
                                <p class="card-text">Confirmed bookings</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Recent Bookings</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $stmt = $pdo->query("SELECT b.*, u.name as user_name 
                                                     FROM bookings b 
                                                     JOIN users u ON b.user_id = u.id 
                                                     ORDER BY b.booking_date DESC 
                                                     LIMIT 10");
                                $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>User</th>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($recent_bookings as $booking): ?>
                                            <tr>
                                                <td><?php echo $booking['id']; ?></td>
                                                <td><?php echo $booking['user_name']; ?></td>
                                                <td><?php echo ucfirst($booking['booking_type']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></td>
                                                <td>$<?php echo number_format($booking['total_price'], 2); ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?php echo $booking['status'] == 'confirmed' ? 'bg-success' : 
                                                              ($booking['status'] == 'pending' ? 'bg-warning' : 'bg-danger'); ?>">
                                                        <?php echo ucfirst($booking['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Booking Types</h5>
                            </div>
                            <div class="card-body">
                                <canvas id="bookingTypesChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Booking types chart
        const ctx = document.getElementById('bookingTypesChart').getContext('2d');
        const bookingTypesChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($booking_types, 'booking_type')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($booking_types, 'count')); ?>,
                    backgroundColor: [
                        '#4e73df',
                        '#1cc88a',
                        '#36b9cc',
                        '#f6c23e'
                    ]
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>