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
    $title = $_POST['title'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $duration_days = $_POST['duration_days'];
    $price = $_POST['price'];
    $max_participants = $_POST['max_participants'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Handle image upload
    $image_path = null;
    if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload = handleImageUpload($_FILES['image'], 'tour');
        if($upload['success']) {
            $image_path = $upload['path'];
        } else {
            $error = $upload['error'];
        }
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO tours (title, location, description, duration_days, price, max_participants, start_date, end_date, image_path) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $location, $description, $duration_days, $price, $max_participants, $start_date, $end_date, $image_path]);
        
        $success = "Tour added successfully!";
    } catch (PDOException $e) {
        $error = "Error adding tour: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Tour | TravelEase Admin</title>
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
                    <h2 class="h3">Add New Tour</h2>
                    <a href="manage-tours.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Tours
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
                                    <label for="title" class="form-label">Tour Title</label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" required>
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                                </div>
                                <div class="col-md-4">
                                    <label for="duration_days" class="form-label">Duration (days)</label>
                                    <input type="number" class="form-control" id="duration_days" name="duration_days" min="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="price" class="form-label">Price</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="max_participants" class="form-label">Max Participants</label>
                                    <input type="number" class="form-control" id="max_participants" name="max_participants" min="1" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" required>
                                </div>
                                <div class="col-12">
                                    <label for="image" class="form-label">Tour Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <small class="text-muted">Show the main attraction of the tour</small>
                                    <div id="image-preview-container" class="mt-2"></div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Add Tour</button>
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
        
        // Calculate end date based on duration and start date
        document.getElementById('duration_days').addEventListener('change', updateEndDate);
        document.getElementById('start_date').addEventListener('change', updateEndDate);
        
        function updateEndDate() {
            const startDate = document.getElementById('start_date').value;
            const duration = parseInt(document.getElementById('duration_days').value);
            
            if(startDate && duration) {
                const start = new Date(startDate);
                const end = new Date(start);
                end.setDate(start.getDate() + duration);
                
                // Format to YYYY-MM-DD
                const endDateStr = end.toISOString().split('T')[0];
                document.getElementById('end_date').value = endDateStr;
            }
        }
    </script>
</body>
</html>