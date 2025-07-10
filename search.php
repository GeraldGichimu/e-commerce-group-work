<?php
include 'includes/config.php';
include 'includes/header.php';

// Validate search type
$valid_types = ['hotel', 'tours', 'cars', 'flights'];
$type = isset($_GET['type']) && in_array($_GET['type'], $valid_types) ? $_GET['type'] : 'hotels';

try {
    switch($type) {
        case 'hotels':
            // Process hotel search
            $destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
            $check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
            $check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';
            $guests = isset($_GET['guests']) ? (int)$_GET['guests'] : 1;
            
            $query = "SELECT * FROM hotels WHERE location LIKE :destination";
            $params = [':destination' => "%$destination%"];
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'tours':
            // Process tour search
            $destination = isset($_GET['destination']) ? trim($_GET['destination']) : '';
            $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
            $duration = isset($_GET['duration']) ? $_GET['duration'] : '';
            
            $query = "SELECT * FROM tours WHERE location LIKE :destination AND start_date >= CURDATE()";
            $params = [':destination' => "%$destination%"];
            
            if (!empty($start_date)) {
                $query .= " AND start_date >= :start_date";
                $params[':start_date'] = $start_date;
            }
            
            if ($duration === '1-3') {
                $query .= " AND duration_days BETWEEN 1 AND 3";
            } elseif ($duration === '4-7') {
                $query .= " AND duration_days BETWEEN 4 AND 7";
            } elseif ($duration === '8+') {
                $query .= " AND duration_days >= 8";
            }
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'cars':
            // Process car search
            $location = isset($_GET['location']) ? trim($_GET['location']) : '';
            $pickup_date = isset($_GET['pickup_date']) ? $_GET['pickup_date'] : '';
            $dropoff_date = isset($_GET['dropoff_date']) ? $_GET['dropoff_date'] : '';
            $car_type = isset($_GET['car_type']) ? $_GET['car_type'] : 'all';
            
            $query = "SELECT * FROM cars WHERE location LIKE :location AND available = 1";
            $params = [':location' => "%$location%"];
            
            if ($car_type !== 'all') {
                $query .= " AND type = :car_type";
                $params[':car_type'] = $car_type;
            }
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        case 'flights':
            // Process flight search
            $departure = isset($_GET['departure']) ? trim($_GET['departure']) : '';
            $arrival = isset($_GET['arrival']) ? trim($_GET['arrival']) : '';
            $departure_date = isset($_GET['departure_date']) ? $_GET['departure_date'] : '';
            $return_date = isset($_GET['return_date']) ? $_GET['return_date'] : '';
            $passengers = isset($_GET['passengers']) ? (int)$_GET['passengers'] : 1;
            // $class = isset($_GET['class']) ? $_GET['class'] : 'economy';
            
            $query = "SELECT * FROM flights 
                     WHERE departure_city LIKE :departure 
                     AND arrival_city LIKE :arrival 
                     AND departure_date >= :departure_date
                     AND seats_available >= :passengers";
            
            $params = [
                ':departure' => "%$departure%",
                ':arrival' => "%$arrival%",
                ':departure_date' => $departure_date,
                ':passengers' => $passengers
            ];
            
            // if ($class !== 'all') {
            //     $query .= " AND class = :class";
            //     $params[':class'] = $class;
            // }
            
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
    }
} catch (PDOException $e) {
    $error = "Error searching for $type: " . $e->getMessage();
}
?>

<div class="container mt-5">
    <h2 class="mb-4">Search Results for <?php echo ucfirst($type); ?></h2>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php elseif (empty($results)): ?>
        <div class="alert alert-info">No results found for your search criteria.</div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($results as $item): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <?php if (isset($item['image_path']) && !empty($item['image_path'])): ?>
                            <img src="<?php echo $item['image_path']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($item['name'] ?? $item['title'] ?? $item['model'] ?? $item['airline']); ?>" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                <?php if ($type === 'hotels'): ?>
                                    <i class="fas fa-hotel fa-4x text-white"></i>
                                <?php elseif ($type === 'tours'): ?>
                                    <i class="fas fa-route fa-4x text-white"></i>
                                <?php elseif ($type === 'cars'): ?>
                                    <i class="fas fa-car fa-4x text-white"></i>
                                <?php else: ?>
                                    <i class="fas fa-plane fa-4x text-white"></i>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="card-title">
                                <?php 
                                echo htmlspecialchars(
                                    $item['name'] ?? 
                                    $item['title'] ?? 
                                    $item['model'] ?? 
                                    $item['airline'] . ' ' . $item['flight_number']
                                ); 
                                ?>
                            </h5>
                            
                            <div class="card-text">
                                <?php if ($type === 'hotels'): ?>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['location']); ?></p>
                                    <p><i class="fas fa-bed"></i> <?php echo htmlspecialchars($item['amenities']); ?></p>
                                    <?php if ($item['rating']): ?>
                                        <p>
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $item['rating'] ? 'text-warning' : 'text-secondary'; ?>"></i>
                                            <?php endfor; ?>
                                        </p>
                                    <?php endif; ?>
                                    <p class="h5">$<?php echo number_format($item['price_per_night'], 2); ?> <small class="text-muted">/night</small></p>
                                    
                                <?php elseif ($type === 'tours'): ?>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['location']); ?></p>
                                    <p><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($item['start_date'])); ?> - <?php echo date('M d, Y', strtotime($item['end_date'])); ?></p>
                                    <p><i class="fas fa-clock"></i> <?php echo $item['duration_days']; ?> days</p>
                                    <p class="h5">$<?php echo number_format($item['price'], 2); ?></p>
                                    
                                <?php elseif ($type === 'cars'): ?>
                                    <p><i class="fas fa-car"></i> <?php echo ucfirst($item['type']); ?></p>
                                    <p><i class="fas fa-building"></i> <?php echo htmlspecialchars($item['rental_company']); ?></p>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($item['location']); ?></p>
                                    <p class="h5">$<?php echo number_format($item['price_per_day'], 2); ?> <small class="text-muted">/day</small></p>

                                <?php else: ($type === 'flights') ?>
                                    <p><i class="fas fa-plane-departure"></i> <?php echo htmlspecialchars($item['departure_city']); ?> to <i class="fas fa-plane-arrival"></i> <?php echo htmlspecialchars($item['arrival_city']); ?></p>
                                    <p><i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y H:i', strtotime($item['departure_date'])); ?></p>
                                    <p><i class="fas fa-chair"></i> <?php echo ucfirst($item['class']); ?> Class</p>
                                    <p class="h5">$<?php echo number_format($item['price'], 2); ?></p>
                                
                                <?php endif; ?>
                            </div>
                            
                            <a href="details.php?type=<?php echo $type; ?>&id=<?php echo $item['id']; ?>" class="btn btn-primary">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>