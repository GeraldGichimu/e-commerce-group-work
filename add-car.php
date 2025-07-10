<?php
session_start();
include '../includes/config.php';
include 'includes/upload.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $model = $_POST['model'];
    $type = $_POST['type'];
    $rental_company = $_POST['rental_company'];
    $location = $_POST['location'];
    $price_per_day = $_POST['price_per_day'];
    $features = $_POST['features'] ?? '';

    // Handle image upload
    $image_path = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload = handleImageUpload($_FILES['image']);
        
        if($upload['success']) {
            $image_path = $upload['path'];
        } else {
            $error = $upload['error'];
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO cars (model, type, rental_company, location, price_per_day, features, image_path) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$model, $type, $rental_company, $location, $price_per_day, $features, $image_path]);
        
        $success = "Car added successfully!";
    } catch (PDOException $e) {
        $error = "Error adding car: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Car | TravelEase Admin</title>
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
                    <h2 class="h3">Add New Car</h2>
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
                                    <input type="text" class="form-control" id="model" name="model" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="type" class="form-label">Car Type</label>
                                    <select class="form-select" id="type" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="economy">Economy</option>
                                        <option value="compact">Compact</option>
                                        <option value="midsize">Midsize</option>
                                        <option value="suv">SUV</option>
                                        <option value="luxury">Luxury</option>
                                        <option value="van">Van</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="rental_company" class="form-label">Rental Company</label>
                                    <input type="text" class="form-control" id="rental_company" name="rental_company" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="price_per_day" class="form-label">Price Per Day</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price_per_day" name="price_per_day" min="0" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="features" class="form-label">Features</label>
                                    <input type="text" class="form-control" id="features" name="features" 
                                           placeholder="Automatic, A/C, Bluetooth, etc.">
                                </div>
                                <div class="col-12">
                                    <label for="image" class="form-label">Car Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <small class="text-muted">Show the car from a good angle</small>
                                    <div id="image-preview-container" class="mt-2"></div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Add Car</button>
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