<?php
include 'includes/config.php';
include 'includes/header.php';
require_once 'init.php';

// Validate type and ID
$valid_types = ['hotels', 'tours', 'cars', 'flights'];
$type = isset($_GET['type']) && in_array($_GET['type'], $valid_types) ? $_GET['type'] : null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$type || $id <= 0) {
    header("Location: index.php");
    exit();
}

try {
    switch($type) {
        case 'hotels':
            $stmt = $pdo->prepare("SELECT * FROM hotels WHERE id = ?");
            $table = 'hotels';
            $not_found_msg = "Hotel not found";
            break;
            
        case 'tours':
            $stmt = $pdo->prepare("SELECT * FROM tours WHERE id = ?");
            $table = 'tours';
            $not_found_msg = "Tour not found";
            break;
            
        case 'cars':
            $stmt = $pdo->prepare("SELECT * FROM cars WHERE id = ?");
            $table = 'cars';
            $not_found_msg = "Car not found";
            break;
            
        case 'flights':
            $stmt = $pdo->prepare("SELECT * FROM flights WHERE id = ?");
            $table = 'flights';
            $not_found_msg = "Flight not found";
            break;
    }
    
    $stmt->execute([$id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$item) {
        throw new Exception($not_found_msg);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Handle booking form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    // session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?redirect=details.php?type=$type&id=$id");
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $quantity = (int)($_POST['quantity'] ?? 1);
    $special_requests = $_POST['special_requests'] ?? '';
    $check_in_date = $_POST['check_in_date'] ?? null;
    $check_out_date = $_POST['check_out_date'] ?? null;

    try {
        // Validate availability and calculate price based on type
        switch($type) {
            case 'hotels':
                // Validate dates
                if (empty($check_in_date) || empty($check_out_date)) {
                    throw new Exception("Please select check-in and check-out dates");
                }
                
                // Calculate nights
                 $nights = (strtotime($check_out_date) - strtotime($check_in_date)) / (60 * 60 * 24);
    
                // Validate night count
                if ($nights <= 0) {
                    throw new Exception("Check-out date must be after check-in date");
                }
                
                // Check room availability
                $stmt = $pdo->prepare("SELECT rooms_available FROM hotels WHERE id = ?");
                $stmt->execute([$id]);
                $available = $stmt->fetchColumn();
                
                if ($available < $quantity) {
                    throw new Exception("Only $available rooms left");
                }
                
                $total_price = $item['price_per_night'] * $nights * $quantity;
                break;

            case 'cars':
                // Validate dates
                if (empty($start_date) || empty($end_date)) {
                    throw new Exception("Please select pickup and drop-off dates");
                }
                
                // Calculate days
                $days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24);
                if ($days <= 0) {
                    throw new Exception("Invalid date range");
                }
                
                // Check car availability
                $stmt = $pdo->prepare("SELECT available FROM cars WHERE id = ? AND available = 1");
                $stmt->execute([$id]);
                $available = $stmt->fetchColumn();
                
                if (!$available) {
                    throw new Exception("This car is no longer available");
                }
                
                $total_price = $item['price_per_day'] * $days;
                break;

            case 'tours':
                // Check tour availability
                $stmt = $pdo->prepare("SELECT max_participants FROM tours WHERE id = ?");
                $stmt->execute([$id]);
                $max_participants = $stmt->fetchColumn();
                
                if ($quantity > $max_participants) {
                    throw new Exception("Maximum $max_participants participants allowed");
                }
                
                $total_price = $item['price'] * $quantity;
                $start_date = $item['start_date']; // Use tour's fixed start date
                $end_date = $item['end_date'];     // Use tour's fixed end date
                break;

            case 'flights':
                // Check seat availability
                $stmt = $pdo->prepare("SELECT seats_available FROM flights WHERE id = ?");
                $stmt->execute([$id]);
                $available_seats = $stmt->fetchColumn();
                
                if ($quantity > $available_seats) {
                    throw new Exception("Only $available_seats seats left");
                }
                
                // Get price based on selected class
                $selected_class = $_POST['travel_class'] ?? 'economy';
                $price_per_passenger = $item['price']; // Default to economy
                
                if ($selected_class === 'business' && isset($item['business_price'])) {
                    $price_per_passenger = $item['business_price'];
                } elseif ($selected_class === 'first' && isset($item['first_class_price'])) {
                    $price_per_passenger = $item['first_class_price'];
                }
                
                $total_price = $price_per_passenger * $quantity;
                $start_date = $item['departure_date']; // Use flight's departure date
                break;
        }

        // Create booking record
        $stmt = $pdo->prepare("INSERT INTO bookings 
                              (user_id, booking_type, item_id, check_in_date, check_out_date, start_date, end_date, 
                               quantity, travel_class, total_price, special_requests) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $user_id,
            $type,
            $id,
            $check_in_date ?? null,
            $check_out_date ?? null,
            $start_date,
            $end_date,
            $quantity,
            $selected_class ?? null,
            $total_price,
            $special_requests
        ]);

        // Update inventory based on booking type
        switch($type) {
            case 'hotels':
                $pdo->query("UPDATE hotels SET rooms_available = rooms_available - $quantity WHERE id = $id");
                break;
            case 'cars':
                $pdo->query("UPDATE cars SET available = 0 WHERE id = $id");
                break;
            case 'tours':
                // Tours might not need inventory update if they have unlimited capacity
                break;
            case 'flights':
                $pdo->query("UPDATE flights SET seats_available = seats_available - $quantity WHERE id = $id");
                break;
        }

        $booking_id = $pdo->lastInsertId();
        $success = "Booking successful! Your booking ID is #$booking_id";

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

?>

<div class="container mt-5">
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php else: ?>
        <div class="row">
            <div class="col-md-8">
                <h2>
                    <?php 
                    echo htmlspecialchars(
                        $item['name'] ?? 
                        $item['title'] ?? 
                        $item['model'] ?? 
                        $item['airline'] . ' ' . $item['flight_number']
                    ); 
                    ?>
                </h2>
                
                <?php if (isset($item['image_path']) && !empty($item['image_path'])): ?>
                    <img src="<?php echo $item['image_path']; ?>" class="img-fluid mb-4" alt="<?php echo htmlspecialchars($item['name'] ?? $item['title'] ?? $item['model'] ?? $item['airline']); ?>">
                <?php endif; ?>
                
                <div class="mb-4">
                    <?php if ($type === 'hotels'): ?>
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                        <p><i class="fas fa-bed"></i> <strong>Amenities:</strong> <?php echo htmlspecialchars($item['amenities']); ?></p>
                        <p><i class="fas fa-info-circle"></i> <strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
                        <?php if ($item['rating']): ?>
                            <p>
                                <strong>Rating:</strong>
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= $item['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                <?php endfor; ?>
                            </p>
                        <?php endif; ?>
                        
                    <?php elseif ($type === 'tours'): ?>
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                        <p><i class="fas fa-calendar-alt"></i> <strong>Dates:</strong> <?php echo date('M d, Y', strtotime($item['start_date'])); ?> - <?php echo date('M d, Y', strtotime($item['end_date'])); ?></p>
                        <p><i class="fas fa-clock"></i> <strong>Duration:</strong> <?php echo $item['duration_days']; ?> days</p>
                        <p><i class="fas fa-users"></i> <strong>Max Participants:</strong> <?php echo $item['max_participants']; ?></p>
                        <p><i class="fas fa-info-circle"></i> <strong>Description:</strong> <?php echo htmlspecialchars($item['description']); ?></p>
                        
                    <?php elseif ($type === 'cars'): ?>
                        <p><i class="fas fa-car"></i> <strong>Type:</strong> <?php echo ucfirst($item['type']); ?></p>
                        <p><i class="fas fa-building"></i> <strong>Rental Company:</strong> <?php echo htmlspecialchars($item['rental_company']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <strong>Pickup Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                        <p><i class="fas fa-info-circle"></i> <strong>Features:</strong> <?php echo htmlspecialchars($item['features']); ?></p>
                        
                    <?php else: ?>
                        <p><i class="fas fa-plane-departure"></i> <strong>Departure:</strong> <?php echo htmlspecialchars($item['departure_city']); ?> at <?php echo date('M d, Y H:i', strtotime($item['departure_date'])); ?></p>
                        <p><i class="fas fa-plane-arrival"></i> <strong>Arrival:</strong> <?php echo htmlspecialchars($item['arrival_city']); ?> at <?php echo date('M d, Y H:i', strtotime($item['arrival_date'])); ?></p>
                        <p><i class="fas fa-clock"></i> <strong>Duration:</strong> <?php $duration = strtotime($item['arrival_date']) - strtotime($item['departure_date']);echo floor($duration / 3600) . 'h ' . floor(($duration % 3600) / 60) . 'm';?></p>
                        <p><i class="fas fa-chair"></i> <strong>Class:</strong> <?php echo ucfirst($item['class']); ?></p>
                        <p><i class="fas fa-suitcase"></i> <strong>Baggage Allowance:</strong> <?php echo $item['baggage_allowance'] ?? '20kg'; ?></p>
                        <p><i class="fas fa-users"></i> <strong>Seats Available:</strong> <?php echo $item['seats_available']; ?></p>
                        <p><i class="fas fa-utensils"></i> <strong>Meal:</strong> <?php echo $item['meal_included'] ? 'Included' : 'Not included'; ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Booking Information</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php else: ?>
                            <form method="post" action="details.php?type=<?php echo $type; ?>&id=<?php echo $id; ?>">
                                <?php if ($type === 'hotels'): ?>
                                    <div class="mb-3">
                                        <label for="check_in_date" class="form-label">Check-in Date</label>
                                        <input type="date" class="form-control" id="check_in_date" name="check_in_date" required min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="check_out_date" class="form-label">Check-out Date</label>
                                        <input type="date" class="form-control" id="check_out_date" name="check_out_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="rooms" class="form-label">Number of Rooms</label>
                                        <input type="number" class="form-control" id="rooms" name="quantity" 
                                            min="1" max="<?= $item['rooms_available'] ?>" value="1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="special_requests" class="form-label">Special Requests</label>
                                        <textarea class="form-control" id="special_requests" name="special_requests" rows="3"></textarea>
                                    </div>
                                    <p class="h4 text-center my-4">
                                        $<?php echo number_format($item['price_per_night'], 2); ?> <small class="text-muted">/night</small>
                                    </p>
                                    
                                <?php elseif ($type === 'tours'): ?>
                                    <div class="mb-3">
                                        <label for="participants" class="form-label">Number of Participants</label>
                                        <input type="number" class="form-control" id="participants" name="quantity" min="1" max="<?php echo $item['max_participants']; ?>" value="1" required>
                                    </div>
                                    <div class="mb-3">
                                        <p class="mb-1"><strong>Tour Dates:</strong></p>
                                        <p><?= date('M d, Y', strtotime($item['start_date'])) ?> to <?= date('M d, Y', strtotime($item['end_date'])) ?></p>
                                    </div>
                                    <div class="mb-3">
                                        <label for="special_requests" class="form-label">Special Requests</label>
                                        <textarea class="form-control" id="special_requests" name="special_requests" rows="3"></textarea>
                                    </div>
                                    <p class="h4 text-center my-4">
                                        $<?php echo number_format($item['price'], 2); ?>
                                    </p>
                                    
                                <?php elseif ($type === 'cars'): ?>
                                    <div class="mb-3">
                                        <label for="pickup_date" class="form-label">Pickup Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" required min="<?php echo date('Y-m-d'); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="dropoff_date" class="form-label">Drop-off Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="special_requests" class="form-label">Special Requests</label>
                                        <textarea class="form-control" id="special_requests" name="special_requests" rows="3"></textarea>
                                    </div>
                                    <p class="h4 text-center my-4">
                                        $<?php echo number_format($item['price_per_day'], 2); ?> <small class="text-muted">/day</small>
                                    </p>
                                    
                                <?php else: ?>
                                    <div class="mb-3">
                                        <label for="passengers" class="form-label">Number of Passengers</label>
                                        <input type="number" class="form-control" id="passengers" name="quantity" min="1" max="<?php echo $item['seats_available']; ?>" value="1" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Travel Class</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="travel_class" 
                                                id="economyClass" value="economy" checked>
                                            <label class="form-check-label" for="economyClass">
                                                Economy (<?php echo '$' . number_format($item['price'], 2); ?>)
                                            </label>
                                        </div>
                                        <?php if (isset($item['business_price'])): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="travel_class" 
                                                id="businessClass" value="business">
                                            <label class="form-check-label" for="businessClass">
                                                Business (<?php echo '$' . number_format($item['business_price'], 2); ?>)
                                            </label>
                                        </div>
                                        <?php endif; ?>
                                        <?php if (isset($item['first_class_price'])): ?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="travel_class" 
                                                id="firstClass" value="first">
                                            <label class="form-check-label" for="firstClass">
                                                First Class (<?php echo '$' . number_format($item['first_class_price'], 2); ?>)
                                            </label>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="special_requests" class="form-label">Special Requests</label>
                                        <textarea class="form-control" id="special_requests" name="special_requests" rows="3"></textarea>
                                    </div>
                                    <p class="h4 text-center my-4">
                                        $<?php echo number_format($item['price'], 2); ?>
                                    </p>
                                <?php endif; ?>
                                
                                <button type="submit" name="book_now" class="btn btn-primary w-100">Book Now</button>
                            </form>
                            <script>
                                // Dynamic price calculation
                                document.querySelectorAll('input[name="travel_class"]').forEach(radio => {
                                    radio.addEventListener('change', updateTotalPrice);
                                });
                                document.getElementById('passengers').addEventListener('input', updateTotalPrice);
                                
                                function updateTotalPrice() {
                                    const passengers = parseInt(document.getElementById('passengers').value) || 1;
                                    const selectedClass = document.querySelector('input[name="travel_class"]:checked');
                                    const pricePerPassenger = parseFloat(selectedClass.dataset.price);
                                    const total = (pricePerPassenger * passengers).toFixed(2);
                                    
                                    document.getElementById('totalPrice').textContent = '$' + total;
                                }
                            </script>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>