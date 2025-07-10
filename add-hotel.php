<?php
session_start();
include '../includes/config.php';
include 'includes/upload.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

// Initialize variables
$error = '';
$success = '';
$image_path = null;

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $price_per_night = floatval($_POST['price_per_night']);
    $rooms_available = intval($_POST['rooms_available']);
    $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : null;
    $amenities = isset($_POST['amenities']) ? implode(', ', $_POST['amenities']) : '';
    
    // Validate required fields
    if(empty($name) || empty($location) || empty($price_per_night) || empty($rooms_available)) {
        $error = "Please fill in all required fields.";
    } else {
        // Handle image upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload = handleImageUpload($_FILES['image']);
            
            if($upload['success']) {
                $image_path = $upload['path'];
            } else {
                $error = $upload['error'];
            }
        }
        
        // Only proceed if no errors
        if(empty($error)) {
            try {
                $stmt = $pdo->prepare("INSERT INTO hotels 
                    (name, location, description, price_per_night, rooms_available, rating, amenities, image_path) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $name,
                    $location,
                    $description,
                    $price_per_night,
                    $rooms_available,
                    $rating,
                    $amenities,
                    $image_path
                ]);
                
                $success = "Hotel added successfully!";
                
                // Clear form if needed
                if($success) {
                    $name = $location = $description = '';
                    $price_per_night = $rooms_available = 0;
                    $rating = null;
                    $amenities = [];
                    $image_path = null;
                }
            } catch (PDOException $e) {
                // Delete uploaded image if database insert failed
                if($image_path && file_exists('../' . $image_path)) {
                    unlink('../' . $image_path);
                }
                $error = "Error adding hotel: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Hotel | TravelEase Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .star-rating {
            font-size: 1.5rem;
            cursor: pointer;
        }
        .star-rating .fas {
            color: #ccc;
        }
        .star-rating .fas.active, .star-rating .fas.hover {
            color: #ffc107;
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
                    <h2 class="h3">Add New Hotel</h2>
                    <a href="manage-hotels.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Hotels
                    </a>
                </div>
                
                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if(!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Hotel Name*</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Location*</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?php echo htmlspecialchars($location ?? ''); ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                        echo htmlspecialchars($description ?? ''); 
                                    ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="price_per_night" class="form-label">Price Per Night*</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price_per_night" name="price_per_night" 
                                               min="0" step="0.01" value="<?php echo $price_per_night ?? ''; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="rooms_available" class="form-label">Rooms Available*</label>
                                    <input type="number" class="form-control" id="rooms_available" name="rooms_available" 
                                           min="1" value="<?php echo $rooms_available ?? ''; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rating</label>
                                    <div class="star-rating">
                                        <input type="hidden" name="rating" id="rating-value" value="<?php echo $rating ?? ''; ?>">
                                        <i class="fas fa-star" data-rating="1"></i>
                                        <i class="fas fa-star" data-rating="2"></i>
                                        <i class="fas fa-star" data-rating="3"></i>
                                        <i class="fas fa-star" data-rating="4"></i>
                                        <i class="fas fa-star" data-rating="5"></i>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Amenities</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Free WiFi" id="amenity1"
                                                    <?php echo (isset($amenities) && in_array('Free WiFi', $amenities)) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="amenity1">Free WiFi</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Pool" id="amenity2"
                                                    <?php echo (isset($amenities) && in_array('Pool', $amenities) ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="amenity2">Pool</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Gym" id="amenity3"
                                                    <?php echo (isset($amenities) && in_array('Gym', $amenities) ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="amenity3">Gym</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Restaurant" id="amenity4"
                                                    <?php echo (isset($amenities) && in_array('Restaurant', $amenities) ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="amenity4">Restaurant</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Spa" id="amenity5"
                                                    <?php echo (isset($amenities) && in_array('Spa', $amenities) ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="amenity5">Spa</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Parking" id="amenity6"
                                                    <?php echo (isset($amenities) && in_array('Parking', $amenities) ? 'checked' : ''); ?>>
                                                <label class="form-check-label" for="amenity6">Parking</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <label for="image" class="form-label">Hotel Image</label>
                                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                                    <small class="text-muted">Max size: 5MB (JPEG, PNG, GIF, WebP)</small>
                                    <div id="image-preview-container" class="mt-2"></div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Add Hotel</button>
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
        // Star rating functionality
        document.querySelectorAll('.star-rating i').forEach(star => {
            // Set initial rating if exists
            const initialRating = document.getElementById('rating-value').value;
            if(initialRating) {
                highlightStars(initialRating);
            }
            
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                document.getElementById('rating-value').value = rating;
                highlightStars(rating);
            });
            
            star.addEventListener('mouseover', function() {
                const rating = this.getAttribute('data-rating');
                highlightStars(rating, true);
            });
            
            star.addEventListener('mouseout', function() {
                const currentRating = document.getElementById('rating-value').value;
                highlightStars(currentRating);
            });
        });
        
        function highlightStars(rating, isHover = false) {
            document.querySelectorAll('.star-rating i').forEach(s => {
                if(s.getAttribute('data-rating') <= rating) {
                    s.classList.add(isHover ? 'hover' : 'active');
                    s.classList.remove(isHover ? 'active' : 'hover');
                } else {
                    s.classList.remove('active', 'hover');
                }
            });
        }
        
        // Image preview functionality
        document.getElementById('image').addEventListener('change', function(e) {
            const container = document.getElementById('image-preview-container');
            container.innerHTML = '';
            
            if(this.files && this.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'image-preview';
                    container.appendChild(img);
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>