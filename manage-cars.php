<?php
session_start();
include '../includes/config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Handle car deletion
if(isset($_GET['delete'])) {
    $car_id = $_GET['delete'];
    
    try {
        // First check if there are any bookings for this car
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_type = 'car' AND item_id = ?");
        $stmt->execute([$car_id]);
        $booking_count = $stmt->fetchColumn();
        
        if($booking_count > 0) {
            $error = "Cannot delete car with existing bookings. Cancel the bookings first.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM cars WHERE id = ?");
            $stmt->execute([$car_id]);
            $success = "Car deleted successfully";
        }
    } catch (PDOException $e) {
        $error = "Error deleting car: " . $e->getMessage();
    }
}

// Get all cars
try {
    $stmt = $pdo->query("SELECT * FROM cars ORDER BY model");
    $cars = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching cars: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars | TravelEase Admin</title>
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
                    <h2 class="h3">Manage Car Rentals</h2>
                    <a href="add-car.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Car
                    </a>
                </div>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Model</th>
                                        <th>Type</th>
                                        <th>Rental Company</th>
                                        <th>Location</th>
                                        <th>Price/Day</th>
                                        <th>Available</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($cars as $car): ?>
                                    <tr>
                                        <td><?php echo $car['id']; ?></td>
                                        <td><?php echo $car['model']; ?></td>
                                        <td><?php echo ucfirst($car['type']); ?></td>
                                        <td><?php echo $car['rental_company']; ?></td>
                                        <td><?php echo $car['location']; ?></td>
                                        <td>$<?php echo number_format($car['price_per_day'], 2); ?></td>
                                        <td>
                                            <?php if($car['available']): ?>
                                                <span class="badge bg-success">Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="edit-car.php?id=<?php echo $car['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage-cars.php?delete=<?php echo $car['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this car?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>