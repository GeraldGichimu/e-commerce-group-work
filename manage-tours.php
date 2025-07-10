<?php
session_start();
include '../includes/config.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Handle tour deletion
if(isset($_GET['delete'])) {
    $tour_id = $_GET['delete'];
    
    try {
        // First check if there are any bookings for this tour
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_type = 'tour' AND item_id = ?");
        $stmt->execute([$tour_id]);
        $booking_count = $stmt->fetchColumn();
        
        if($booking_count > 0) {
            $error = "Cannot delete tour with existing bookings. Cancel the bookings first.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM tours WHERE id = ?");
            $stmt->execute([$tour_id]);
            $success = "Tour deleted successfully";
        }
    } catch (PDOException $e) {
        $error = "Error deleting tour: " . $e->getMessage();
    }
}

// Get all tours
try {
    $stmt = $pdo->query("SELECT * FROM tours ORDER BY start_date DESC");
    $tours = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error fetching tours: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tours | TravelEase Admin</title>
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
                    <h2 class="h3">Manage Tours</h2>
                    <a href="add-tour.php" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i> Add New Tour
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
                                        <th>Title</th>
                                        <th>Location</th>
                                        <th>Duration</th>
                                        <th>Dates</th>
                                        <th>Price</th>
                                        <th>Capacity</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($tours as $tour): ?>
                                    <tr>
                                        <td><?php echo $tour['id']; ?></td>
                                        <td><?php echo $tour['title']; ?></td>
                                        <td><?php echo $tour['location']; ?></td>
                                        <td><?php echo $tour['duration_days']; ?> days</td>
                                        <td>
                                            <?php echo date('M d', strtotime($tour['start_date'])); ?> - 
                                            <?php echo date('M d, Y', strtotime($tour['end_date'])); ?>
                                        </td>
                                        <td>$<?php echo number_format($tour['price'], 2); ?></td>
                                        <td><?php echo $tour['max_participants']; ?></td>
                                        <td>
                                            <a href="edit-tour.php?id=<?php echo $tour['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="manage-tours.php?delete=<?php echo $tour['id']; ?>" class="btn btn-sm btn-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this tour?')">
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