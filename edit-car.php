<?php
session_start();
include '../includes/config.php';
include 'includes/upload.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

$car_id = $_GET['id'] ?? 0;

// Get car details
try {
    $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
    $stmt->execute([$car_id]);
    $car = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$car) {
        header("Location: manage-cars.php");
        exit();
    }
} catch (PDOException $e) {
    $error = "Error fetching car: " . $e->getMessage();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $model = $_POST['model'];
    $type = $_POST['type'];
    $rental_company = $_POST['rental_company'];
    $location = $_POST['location'];
    $price_per_day = $_POST['price_per_day'];
    $features = $_POST['features'] ?? '';
    $available = isset($_POST['available']) ? 1 : 0;

    $image_path = $car['image_path'] ?? null; // Preserve existing if not changed

    // Handle image upload
    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload = handleImageUpload($_FILES['image'], 'car');
        if($upload['success']) {
            // Delete old image if exists
            if($car['image_path'] && file_exists('../' . $car['image_path'])) {
                unlink('../' . $car['image_path']);
            }
            $image_path = $upload['path'];
        } else {
            $error = $upload['error'];
        }
    } elseif(isset($_POST['remove_image']) && $_POST['remove_image']) {
        // Remove image if checkbox is checked
        if($car['image_path'] && file_exists('../' . $car['image_path'])) {
            unlink('../' . $car['image_path']);
        }
        $image_path = null;
    }
    
    try {
        $stmt = $pdo->prepare("UPDATE cars SET 
                              model = ?, 
                              type = ?, 
                              rental_company = ?, 
                              location = ?, 
                              price_per_day = ?, 
                              features = ?,
                              available = ?,
                              image_path = ?
                              WHERE id = ?");
        $stmt->execute([$model, $type, $rental_company, $location, $price_per_day, $features, $available, $car_id, $image_path]);
        
        $success = "Car updated successfully!";
        // header("Location: manage-cars.php?success=1");
        // exit();
        // Refresh car data
        $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
        $stmt->execute([$car_id]);
        $car = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error = "Error updating car: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car | TravelEase Admin</title>
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
                    <h2 class="h3">Edit Car #<?php echo $car_id; ?></h2>
                    <a href="manage-cars.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Cars
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
                                    <label for="model" class="form-label">Car Model</label>
                                    <input type="text" class="form-control" id="model" name="model" 
                                           value="<?php echo htmlspecialchars($car['model']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="type" class="form-label">Car Type</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="economy" <?php echo $car['type'] == 'economy' ? 'selected' : ''; ?>>Economy</option>
                                        <option value="compact" <?php echo $car['type'] == 'compact' ? 'selected' : ''; ?>>Compact</option>
                                        <option value="midsize" <?php echo $car['type'] == 'midsize' ? 'selected' : ''; ?>>Midsize</option>
                                        <option value="suv" <?php echo $car['type'] == 'suv' ? 'selected' : ''; ?>>SUV</option>
                                        <option value="luxury" <?php echo $car['type'] == 'luxury' ? 'selected' : ''; ?>>Luxury</option>
                                        <option value="van" <?php echo $car['type'] == 'van' ? 'selected' : ''; ?>>Van</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="rental_company" class="form-label">Rental Company</label>
                                    <input type="text" class="form-control" id="rental_company" name="rental_company" 
                                           value="<?php echo htmlspecialchars($car['rental_company']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?php echo htmlspecialchars($car['location']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="price_per_day" class="form-label">Price Per Day</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price_per_day" name="price_per_day" min="0" step="0.01" 
                                               value="<?php echo $car['price_per_day']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="features" class="form-label">Features</label>
                                    <input type="text" class="form-control" id="features" name="features" 
                                           value="<?php echo htmlspecialchars($car['features']); ?>"
                                           placeholder="Automatic, A/C, Bluetooth, etc.">
                                </div>
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="available" name="available" 
                                               <?php echo $car['available'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="available">
                                            Available for rental
                                        </label>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="image" class="form-label">Car Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <?php if(isset($car) && $car['image_path']): ?>
                                        <div class="mt-2">
                                            <img src="../<?php echo $car['image_path']; ?>" alt="Current Car Image" class="image-preview">
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image">
                                                <label class="form-check-label" for="remove_image">Remove current image</label>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Update Car</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    </script>
</body>
</html>