<?php
session_start();
include '../includes/config.php';
include 'includes/upload.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

$hotel_id = $_GET['id'] ?? 0;

// Get hotel details
try {
    $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
    $stmt->execute([$hotel_id]);
    $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$hotel) {
        header("Location: manage-hotels.php");
        exit();
    }
    
    // Convert amenities string to array
    $hotel['amenities_array'] = $hotel['amenities'] ? explode(', ', $hotel['amenities']) : [];
} catch (PDOException $e) {
    $error = "Error fetching hotel: " . $e->getMessage();
}

// Handle form submission
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $price_per_night = floatval($_POST['price_per_night']);
    $rooms_available = intval($_POST['rooms_available']);
    $rating = isset($_POST['rating']) ? floatval($_POST['rating']) : null;
    $amenities = isset($_POST['amenities']) ? implode(', ', $_POST['amenities']) : '';
    $image_path = $hotel['image_path']; // Default to existing image
    
    // Validate required fields
    if(empty($name) || empty($location) || empty($price_per_night) || empty($rooms_available)) {
        $error = "Please fill in all required fields.";
    } else {
        // Handle image upload
        if(isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
            $upload = handleImageUpload($_FILES['image']);
            
            if($upload['success']) {
                // Delete old image if exists
                if($hotel['image_path'] && file_exists('../' . $hotel['image_path'])) {
                    unlink('../' . $hotel['image_path']);
                }
                $image_path = $upload['path'];
            } else {
                $error = $upload['error'];
            }
        } elseif(isset($_POST['remove_image']) && $_POST['remove_image']) {
            // Remove image if checkbox is checked
            if($hotel['image_path'] && file_exists('../' . $hotel['image_path'])) {
                unlink('../' . $hotel['image_path']);
            }
            $image_path = null;
        }
        
        // Only proceed if no errors
        if(empty($error)) {
            try {
                $stmt = $pdo->prepare("UPDATE hotels SET 
                    name = ?, 
                    location = ?, 
                    description = ?, 
                    price_per_night = ?, 
                    rooms_available = ?, 
                    rating = ?, 
                    amenities = ?,
                    image_path = ?
                    WHERE id = ?");
                
                $stmt->execute([
                    $name,
                    $location,
                    $description,
                    $price_per_night,
                    $rooms_available,
                    $rating,
                    $amenities,
                    $image_path,
                    $hotel_id
                ]);
                
                $success = "Hotel updated successfully!";
                
                // Refresh hotel data
                $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
                $stmt->execute([$hotel_id]);
                $hotel = $stmt->fetch(PDO::FETCH_ASSOC);
                $hotel['amenities_array'] = $hotel['amenities'] ? explode(', ', $hotel['amenities']) : [];
                
            } catch (PDOException $e) {
                $error = "Error updating hotel: " . $e->getMessage();
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
    <title>Edit Hotel | TravelEase Admin</title>
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
        .star-rating {
            font-size: 1.5rem;
        }
        .star-rating .fas {
            color: #ccc;
        }
        .star-rating .fas.active {
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
                    <h2 class="h3">Edit Hotel #<?php echo $hotel_id; ?></h2>
                    <a href="manage-hotels.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to Hotels
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
                                    <label for="name" class="form-label">Hotel Name</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($hotel['name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label">Location</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?php echo htmlspecialchars($hotel['location']); ?>" required>
                                </div>
                                <div class="col-12">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control" id="description" name="description" rows="3"><?php 
                                        echo htmlspecialchars($hotel['description']); 
                                    ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label for="price_per_night" class="form-label">Price Per Night</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price_per_night" name="price_per_night" min="0" step="0.01" 
                                               value="<?php echo $hotel['price_per_night']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="rooms_available" class="form-label">Rooms Available</label>
                                    <input type="number" class="form-control" id="rooms_available" name="rooms_available" min="1" 
                                           value="<?php echo $hotel['rooms_available']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Rating</label>
                                    <div class="star-rating">
                                        <input type="hidden" name="rating" id="rating-value" value="<?php echo $hotel['rating']; ?>">
                                        <i class="fas fa-star <?php echo $hotel['rating'] >= 1 ? 'active' : ''; ?>" data-rating="1"></i>
                                        <i class="fas fa-star <?php echo $hotel['rating'] >= 2 ? 'active' : ''; ?>" data-rating="2"></i>
                                        <i class="fas fa-star <?php echo $hotel['rating'] >= 3 ? 'active' : ''; ?>" data-rating="3"></i>
                                        <i class="fas fa-star <?php echo $hotel['rating'] >= 4 ? 'active' : ''; ?>" data-rating="4"></i>
                                        <i class="fas fa-star <?php echo $hotel['rating'] >= 5 ? 'active' : ''; ?>" data-rating="5"></i>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Amenities</label>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Free WiFi" id="amenity1"
                                                    <?php echo in_array('Free WiFi', $hotel['amenities_array']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="amenity1">Free WiFi</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Pool" id="amenity2"
                                                    <?php echo in_array('Pool', $hotel['amenities_array']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="amenity2">Pool</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Gym" id="amenity3"
                                                    <?php echo in_array('Gym', $hotel['amenities_array']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="amenity3">Gym</label>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Restaurant" id="amenity4"
                                                    <?php echo in_array('Restaurant', $hotel['amenities_array']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="amenity4">Restaurant</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Spa" id="amenity5"
                                                    <?php echo in_array('Spa', $hotel['amenities_array']) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="amenity5">Spa</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="amenities[]" value="Parking" id="amenity6"
                                                    <?php echo in_array('Parking', $hotel['amenities_array']) ? 'checked' : ''; ?>>
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
                                    <button type="submit" class="btn btn-primary">Update Hotel</button>
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
        
        // Star rating functionality
        document.querySelectorAll('.star-rating i').forEach(star => {
            star.addEventListener('click', function() {
                const rating = this.getAttribute('data-rating');
                document.getElementById('rating-value').value = rating;
                
                // Highlight selected stars
                document.querySelectorAll('.star-rating i').forEach(s => {
                    if(s.getAttribute('data-rating') <= rating) {
                        s.classList.add('active');
                    } else {
                        s.classList.remove('active');
                    }
                });
            });
        });
    </script>
</body>
</html>