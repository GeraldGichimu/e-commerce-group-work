<?php
session_start();
include '../includes/config.php';
include 'includes/upload.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

$flight_id = $_GET['id'] ?? 0;

// Get flight details
try {
    $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
    $stmt->execute([$flight_id]);
    $flight = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$flight) {
        header("Location: manage-flights.php");
        exit();
    }
} catch (PDOException $e) {
    $error = "Error fetching flight: " . $e->getMessage();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $airline = $_POST['airline'];
    $flight_number = $_POST['flight_number'];
    $departure_city = $_POST['departure_city'];
    $arrival_city = $_POST['arrival_city'];
    $departure_date = $_POST['departure_date'];
    $arrival_date = $_POST['arrival_date'];
    $price = $_POST['price'];
    $seats_available = $_POST['seats_available'];

    $image_path = $flight['image_path'] ?? null; // Preserve existing if not changed
    
    // Handle image upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload = handleImageUpload($_FILES['image'], 'flight');
        if($upload['success']) {
            // Delete old image if exists
            if($flight['image_path'] && file_exists('../' . $flight['image_path'])) {
                unlink('../' . $flight['image_path']);
            }
            $image_path = $upload['path'];
        } else {
            $error = $upload['error'];
        }
    } elseif(isset($_POST['remove_image']) && $_POST['remove_image']) {
        // Remove image if checkbox is checked
        if($flight['image_path'] && file_exists('../' . $flight['image_path'])) {
            unlink('../' . $flight['image_path']);
        }
        $image_path = null;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE flights SET 
                              airline = ?, 
                              flight_number = ?, 
                              departure_city = ?, 
                              arrival_city = ?, 
                              departure_date = ?, 
                              arrival_date = ?, 
                              price = ?, 
                              seats_available = ?,
                              image_path = ? 
                              WHERE id = ?");
        $stmt->execute([$airline, $flight_number, $departure_city, $arrival_city, $departure_date, $arrival_date, $price, $seats_available, $flight_id, $image_path]);
        
        $success = "Flight updated successfully!";
        // Refresh flight data
        $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
        $stmt->execute([$flight_id]);
        $flight = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error updating flight: " . $e->getMessage();
    }
}

// $departure_date = $_POST['departure_date'];
// $duration_hours = $_POST['duration_hours'];

// Calculate arrival date automatically
// $departure = new DateTime($departure_date);
// $interval = new DateInterval('PT' . (int)$duration_hours . 'H');
// $arrival = $departure->add($interval);
// $arrival_date = $arrival->format('Y-m-d H:i:s');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Flight | TravelEase Admin</title>
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
        .image-preview {
            max-height: 200px;
            width: auto;
            display: block;
            margin-bottom: 10px;
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
                    <h2 class="h3">Edit Flight #<?php echo $flight_id; ?></h2>
                    <a href="manage-flights.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Flights
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
                        <form method="post" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="airline" class="form-label">Airline</label>
                                    <input type="text" class="form-control" id="airline" name="airline" 
                                           value="<?php echo htmlspecialchars($flight['airline']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="flight_number" class="form-label">Flight Number</label>
                                    <input type="text" class="form-control" id="flight_number" name="flight_number" 
                                           value="<?php echo htmlspecialchars($flight['flight_number']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="departure_city" class="form-label">Departure City</label>
                                    <input type="text" class="form-control" id="departure_city" name="departure_city" 
                                           value="<?php echo htmlspecialchars($flight['departure_city']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="arrival_city" class="form-label">Arrival City</label>
                                    <input type="text" class="form-control" id="arrival_city" name="arrival_city" 
                                           value="<?php echo htmlspecialchars($flight['arrival_city']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="departure_date" class="form-label">Departure Date & Time</label>
                                    <input type="datetime-local" class="form-control" id="departure_date" name="departure_date" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($flight['departure_date'])); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="arrival_date" class="form-label">Arrival Date & Time</label>
                                    <input type="datetime-local" class="form-control" id="arrival_date" name="arrival_date" 
                                           value="<?php echo date('Y-m-d\TH:i', strtotime($flight['arrival_date'])); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="price" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" 
                                               value="<?php echo $flight['price']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="seats_available" class="form-label">Seats Available</label>
                                    <input type="number" class="form-control" id="seats_available" name="seats_available" min="1" 
                                           value="<?php echo $flight['seats_available']; ?>" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="duration_hours" class="form-label">Duration (hours)</label>
                                    <input type="number" class="form-control" id="duration_hours" name="duration_hours" min="1" step="0.5">
                                </div>
                                <div class="col-12">
                                    <label for="image" class="form-label">Flight Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <?php if(isset($flight) && $flight['image_path']): ?>
                                        <div class="mt-2">
                                            <img src="../<?php echo $flight['image_path']; ?>" alt="Current Flight Image" class="image-preview">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image">
                                                <label class="form-check-label" for="remove_image">Remove current image</label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Update Flight</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- JavaScript to auto-calculate arrival time -->
    <script>
        // Image preview functionality
        document.addEventListener('DOMContentLoaded', function() {
            const imageInputs = document.querySelectorAll('input[type="file"][accept="image/*"]');
            
            imageInputs.forEach(input => {
                const containerId = input.id + '-preview-container';
                const container = document.getElementById(containerId) || 
                                input.closest('.col-12').querySelector('#image-preview-container');
                
                input.addEventListener('change', function(e) {
                    if(container) container.innerHTML = '';
                    
                    if(this.files && this.files[0]) {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'image-preview';
                            if(container) container.appendChild(img);
                        }
                        
                        reader.readAsDataURL(this.files[0]);
                    }
                });
            });
        });
        
        document.getElementById('departure_date').addEventListener('change', calculateArrival);
        document.getElementById('duration_hours').addEventListener('change', calculateArrival);

        function calculateArrival() {
            const departureInput = document.getElementById('departure_date');
            const durationInput = document.getElementById('duration_hours');
            const arrivalInput = document.getElementById('arrival_date');
            
            if(departureInput.value && durationInput.value) {
                const departure = new Date(departureInput.value);
                const durationHours = parseFloat(durationInput.value);
                const durationMs = durationHours * 60 * 60 * 1000;
                const arrival = new Date(departure.getTime() + durationMs);
                
                // Format to datetime-local format (YYYY-MM-DDTHH:MM)
                const year = arrival.getFullYear();
                const month = String(arrival.getMonth() + 1).padStart(2, '0');
                const day = String(arrival.getDate()).padStart(2, '0');
                const hours = String(arrival.getHours()).padStart(2, '0');
                const minutes = String(arrival.getMinutes()).padStart(2, '0');
                
                arrivalInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
            }
        }
    </script>
</body>
</html>