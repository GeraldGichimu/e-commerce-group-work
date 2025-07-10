<?php 
include 'includes/config.php'; // Include database connection and configuration
include 'includes/header.php';

// Fetch featured content from database
try {
    // Featured Hotels
    $hotels_stmt = $pdo->query("SELECT * FROM hotels ORDER BY RAND() LIMIT 4");
    $featured_hotels = $hotels_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Featured Tours
    $tours_stmt = $pdo->query("SELECT * FROM tours WHERE start_date >= CURDATE() ORDER BY RAND() LIMIT 4");
    $featured_tours = $tours_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Featured Cars
    $cars_stmt = $pdo->query("SELECT * FROM cars WHERE available = 1 ORDER BY RAND() LIMIT 4");
    $featured_cars = $cars_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Featured Flights
    $flights_stmt = $pdo->query("SELECT * FROM flights WHERE departure_date >= NOW() ORDER BY RAND() LIMIT 4");
    $featured_flights = $flights_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = "Error fetching featured content: " . $e->getMessage();
}

function getPlaceholderImage($type) {
    $placeholders = [
        'hotel' => 'assets/images/placeholder-hotel.jpg',
        'tour' => 'assets/images/placeholder-tour.jpg',
        'car' => 'assets/images/placeholder-car.jpg',
        'flight' => 'assets/images/placeholder-flight.jpg'
    ];
    return $placeholders[$type] ?? 'assets/images/placeholder-generic.jpg';
}
?>

<div class="hero-section mb-5">
    <div class="hero-text text-center text-white">
        <h1 class="display-4">Explore the World with Ease</h1>
        <p class="lead">Find and book your perfect trip with just a few clicks</p>
    </div>
</div>

<div class="search-box bg-light p-4 rounded shadow mb-5">
    <ul class="nav nav-tabs" id="searchTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="flights-tab" data-bs-toggle="tab" data-bs-target="#flights" type="button">Flights</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="hotels-tab" data-bs-toggle="tab" data-bs-target="#hotels" type="button">Hotels</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cars-tab" data-bs-toggle="tab" data-bs-target="#cars" type="button">Car Rentals</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tours-tab" data-bs-toggle="tab" data-bs-target="#tours" type="button">Tours</button>
        </li>
    </ul>
    
    <div class="tab-content p-3" id="searchTabsContent">
        <!-- Flights Search -->
        <div class="tab-pane fade show active" id="flights" role="tabpanel">
            <form action="search.php" method="get">
                <input type="hidden" name="type" value="flights">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="departure" class="form-label">From</label>
                        <input type="text" class="form-control" id="departure" name="departure" required>
                    </div>
                    <div class="col-md-6">
                        <label for="arrival" class="form-label">To</label>
                        <input type="text" class="form-control" id="arrival" name="arrival" required>
                    </div>
                    <div class="col-md-6">
                        <label for="departure-date" class="form-label">Departure Date</label>
                        <input type="date" class="form-control" id="departure-date" name="departure_date" required>
                    </div>
                    <div class="col-md-6">
                        <label for="return-date" class="form-label">Return Date (Optional)</label>
                        <input type="date" class="form-control" id="return-date" name="return_date">
                    </div>
                    <div class="col-md-4">
                        <label for="passengers" class="form-label">Passengers</label>
                        <select class="form-select" id="passengers" name="passengers">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="class" class="form-label">Class</label>
                        <select class="form-select" id="class" name="class">
                            <option value="economy">Economy</option>
                            <option value="business">Business</option>
                            <option value="first">First Class</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn w-100">Search Flights</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Hotels Search -->
        <div class="tab-pane fade" id="hotels" role="tabpanel">
            <form action="search.php" method="get">
                <input type="hidden" name="type" value="hotels">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="destination" class="form-label">Destination</label>
                        <input type="text" class="form-control" id="destination" name="destination" required>
                    </div>
                    <div class="col-md-3">
                        <label for="check-in" class="form-label">Check-in Date</label>
                        <input type="date" class="form-control" id="check-in" name="check_in" required>
                    </div>
                    <div class="col-md-3">
                        <label for="check-out" class="form-label">Check-out Date</label>
                        <input type="date" class="form-control" id="check-out" name="check_out" required>
                    </div>
                    <div class="col-md-4">
                        <label for="guests" class="form-label">Guests</label>
                        <select class="form-select" id="guests" name="guests">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="rooms" class="form-label">Rooms</label>
                        <select class="form-select" id="rooms" name="rooms">
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search Hotels</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Cars Search -->
        <div class="tab-pane fade" id="cars" role="tabpanel">
            <form action="search.php" method="get">
                <input type="hidden" name="type" value="cars">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="pickup-location" class="form-label">Pick-up Location</label>
                        <input type="text" class="form-control" id="pickup-location" name="location" required>
                    </div>
                    <div class="col-md-3">
                        <label for="pickup-date" class="form-label">Pick-up Date</label>
                        <input type="date" class="form-control" id="pickup-date" name="pickup_date" required>
                    </div>
                    <div class="col-md-3">
                        <label for="dropoff-date" class="form-label">Drop-off Date</label>
                        <input type="date" class="form-control" id="dropoff-date" name="dropoff_date" required>
                    </div>
                    <div class="col-md-4">
                        <label for="car-type" class="form-label">Car Type</label>
                        <select class="form-select" id="car-type" name="car_type">
                            <option value="all">All Types</option>
                            <option value="economy">Economy</option>
                            <option value="compact">Compact</option>
                            <option value="suv">SUV</option>
                            <option value="luxury">Luxury</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search Cars</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Tours Search -->
        <div class="tab-pane fade" id="tours" role="tabpanel">
            <form action="search.php" method="get">
                <input type="hidden" name="type" value="tours">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="tour-destination" class="form-label">Destination</label>
                        <input type="text" class="form-control" id="tour-destination" name="destination" required>
                    </div>
                    <div class="col-md-3">
                        <label for="tour-start-date" class="form-label">Start Date</label>
                        <input type="date" class="form-control" id="tour-start-date" name="start_date">
                    </div>
                    <div class="col-md-3">
                        <label for="tour-duration" class="form-label">Duration (days)</label>
                        <select class="form-select" id="tour-duration" name="duration">
                            <option value="any">Any</option>
                            <option value="1-3">1-3 days</option>
                            <option value="4-7">4-7 days</option>
                            <option value="8+">8+ days</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="tour-type" class="form-label">Tour Type</label>
                        <select class="form-select" id="tour-type" name="tour_type">
                            <option value="all">All Types</option>
                            <option value="adventure">Adventure</option>
                            <option value="cultural">Cultural</option>
                            <option value="wildlife">Wildlife</option>
                            <option value="beach">Beach</option>
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Search Tours</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Featured Hotels Section -->
<div class="featured-section mb-5">
    <h2 class="text-center mb-4">Featured Hotels</h2>
    <div class="row">
        <?php foreach($featured_hotels as $hotel): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <?php if($hotel['image_path']): ?>
                    <img src="<?php echo $hotel['image_path']; ?>" loading="lazy" class="card-img-top" alt="<?php echo htmlspecialchars($hotel['name']); ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="card-img-top bg-secondary" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-hotel fa-4x text-white"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($hotel['name']); ?></h5>
                    <p class="card-text">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($hotel['location']); ?>
                        <?php if($hotel['rating']): ?>
                            <br>
                            <?php for($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?php echo $i <= $hotel['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                            <?php endfor; ?>
                        <?php endif; ?>
                    </p>
                    <p class="h5">$<?php echo number_format($hotel['price_per_night'], 2); ?> <small class="text-muted">/night</small></p>
                    <a href="search.php?type=hotels&destination=<?php echo urlencode($hotel['location']); ?>" class="btn btn-sm btn-outline-primary">View Hotels</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Featured Tours Section -->
<div class="featured-section mb-5">
    <h2 class="text-center mb-4">Featured Tours</h2>
    <div class="row">
        <?php foreach($featured_tours as $tour): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <?php if($tour['image_path']): ?>
                    <img src="<?php echo $tour['image_path']; ?>" loading="lazy" class="card-img-top" alt="<?php echo htmlspecialchars($tour['title']); ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="card-img-top bg-secondary" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-route fa-4x text-white"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($tour['title']); ?></h5>
                    <p class="card-text">
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($tour['location']); ?>
                        <br>
                        <i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($tour['start_date'])); ?> - <?php echo date('M d, Y', strtotime($tour['end_date'])); ?>
                    </p>
                    <p class="h5">$<?php echo number_format($tour['price'], 2); ?></p>
                    <a href="search.php?type=tours&destination=<?php echo urlencode($tour['location']); ?>" class="btn btn-sm btn-outline-primary">View Tours</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Featured Cars Section -->
<div class="featured-section mb-5">
    <h2 class="text-center mb-4">Featured Car Rentals</h2>
    <div class="row">
        <?php foreach($featured_cars as $car): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <?php if($car['image_path']): ?>
                    <img src="<?php echo $car['image_path']; ?>" loading="lazy" class="card-img-top" alt="<?php echo htmlspecialchars($car['model']); ?>" style="height: 200px; object-fit: cover;">
                <?php else: ?>
                    <div class="card-img-top bg-secondary" style="height: 200px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-car fa-4x text-white"></i>
                    </div>
                <?php endif; ?>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($car['model']); ?></h5>
                    <p class="card-text">
                        <span class="badge bg-info"><?php echo ucfirst($car['type']); ?></span>
                        <br>
                        <i class="fas fa-building"></i> <?php echo htmlspecialchars($car['rental_company']); ?>
                        <br>
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($car['location']); ?>
                    </p>
                    <p class="h5">$<?php echo number_format($car['price_per_day'], 2); ?> <small class="text-muted">/day</small></p>
                    <a href="search.php?type=cars&location=<?php echo urlencode($car['location']); ?>" class="btn btn-sm btn-outline-primary">View Cars</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Featured Flights Section -->
<div class="featured-section mb-5">
    <h2 class="text-center mb-4">Featured Flights</h2>
    <div class="row">
        <?php foreach($featured_flights as $flight): ?>
        <div class="col-md-3 mb-4">
            <div class="card h-100">
                <div class="card-img-top text-white p-3" style="height: 200px; display: flex; flex-direction: column; justify-content: center; background-color: #17B890;">
                    <div class="text-center mb-3">
                        <i class="fas fa-plane fa-3x"></i>
                    </div>
                    <h5 class="text-center"><?php echo htmlspecialchars($flight['airline']); ?></h5>
                    <p class="text-center mb-0"><?php echo htmlspecialchars($flight['flight_number']); ?></p>
                </div>
                <div class="card-body">
                    <p class="card-text">
                        <i class="fas fa-plane-departure"></i> <?php echo htmlspecialchars($flight['departure_city']); ?>
                        <br>
                        <small><?php echo date('M d, Y H:i', strtotime($flight['departure_date'])); ?></small>
                        <br><br>
                        <i class="fas fa-plane-arrival"></i> <?php echo htmlspecialchars($flight['arrival_city']); ?>
                        <br>
                        <small><?php echo date('M d, Y H:i', strtotime($flight['arrival_date'])); ?></small>
                    </p>
                    <p class="h5">$<?php echo number_format($flight['price'], 2); ?></p>
                    <a href="search.php?type=flights&departure=<?php echo urlencode($flight['departure_city']); ?>&arrival=<?php echo urlencode($flight['arrival_city']); ?>" class="btn btn-sm btn-outline-primary">View Flights</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="why-choose-us mb-5">
    <h2 class="text-center mb-4">Why Choose TravelEase?</h2>
    <div class="row text-center">
        <div class="col-md-4 mb-4">
            <div class="p-3">
                <i class="fas fa-check-circle fa-3x text-primary mb-3"></i>
                <h4>Easy Booking</h4>
                <p>Simple and intuitive booking process for all your travel needs.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="p-3">
                <i class="fas fa-dollar-sign fa-3x text-primary mb-3"></i>
                <h4>Best Prices</h4>
                <p>We guarantee the best prices for flights, hotels, and more.</p>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="p-3">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h4>24/7 Support</h4>
                <p>Our customer service team is available around the clock.</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>