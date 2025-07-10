<?php
session_start();
include '../includes/config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Handle flight deletion
if(isset($_GET['delete'])) {
    $flight_id = $_GET['delete'];
    
    try {
        // First check if there are any bookings for this flight
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_type = 'flight' AND item_id = ?");
        $stmt->execute([$flight_id]);
        $booking_count = $stmt->fetchColumn();
        
        if($booking_count > 0) {
            $error = "Cannot delete flight with existing bookings. Cancel the bookings first.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM flights WHERE id = ?");
            $stmt->execute([$flight_id]);
            $success = "Flight deleted successfully";
        }
    } catch (PDOException $e) {
        $error = "Error deleting flight: " . $e->getMessage();
    }
}

// Get all flights
try {
    $stmt = $pdo->query("SELECT * FROM flights ORDER BY departure_date DESC");
    $flights = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching flights: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Flights | TravelEase Admin</title>
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
                    <h2 class="h3">Manage Flights</h2>
                    <a href="add-flight.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Flight
                    </a>
                    <!-- <a href="export.php?type=flights" class="btn btn-success ms-2">
                        <i class="fas fa-file-export me-1"></i> Export CSV
                    </a> -->
                </div>
                
                <?php if(isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <?php if(isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <form method="post" action="bulk-actions.php">
                        <!-- <div class="d-flex justify-content-between mb-3">
                            <div>
                                <select class="form-select" name="bulk_action">
                                    <option value="">Bulk Actions</option>
                                    <option value="delete">Delete Selected</option>
                                    <option value="activate">Mark as Active</option>
                                    <option value="deactivate">Mark as Inactive</option>
                                </select>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-secondary">Apply</button>
                            </div>
                        </div> -->

                        <!-- Add checkbox to first column of your table -->
                    </form>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>Airline</th>
                                        <th>Flight Number</th>
                                        <th>Route</th>
                                        <th>Departure</th>
                                        <th>Arrival</th>
                                        <th>Price</th>
                                        <th>Seats</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($flights as $flight): ?>
                                    <tr>
                                        <td><input type="checkbox" name="bulk_ids[]" value="<?php echo $flight['id']; ?>"></td>
                                        <td><?php echo $flight['airline']; ?></td>
                                        <td><?php echo $flight['flight_number']; ?></td>
                                        <td>
                                            <?php echo $flight['departure_city']; ?> 
                                            <i class="fas fa-arrow-right mx-2"></i>
                                            <?php echo $flight['arrival_city']; ?>
                                        </td>
                                        <td><?php echo date('M d, Y H:i', strtotime($flight['departure_date'])); ?></td>
                                        <td><?php echo date('M d, Y H:i', strtotime($flight['arrival_date'])); ?></td>
                                        <td>$<?php echo number_format($flight['price'], 2); ?></td>
                                        <td><?php echo $flight['seats_available']; ?></td>
                                        <td>
                                            <a href="edit-flight.php?id=<?php echo $flight['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage-flights.php?delete=<?php echo $flight['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this flight?')">
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