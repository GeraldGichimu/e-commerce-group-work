<?php
session_start();
include '../includes/config.php';
include '../includes/upload.php';

// Check if admin is logged in
if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

$imported = 0;
$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csv_file'])) {
    if($_FILES['csv_file']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $type = $_POST['import_type'];
        
        if(($handle = fopen($file, "r")) !== FALSE) {
            // Skip header row
            fgetcsv($handle);
            
            while(($data = fgetcsv($handle)) !== FALSE) {
                try {
                    switch($type) {
                        case 'flights':
                            // Format: Airline,FlightNumber,DepartureCity,ArrivalCity,DepartureDateTime,ArrivalDateTime,Price,Seats
                            $departure = DateTime::createFromFormat('Y-m-d H:i', $data[4]);
                            $arrival = DateTime::createFromFormat('Y-m-d H:i', $data[5]);
                            
                            $stmt = $pdo->prepare("INSERT INTO flights 
                                (airline, flight_number, departure_city, arrival_city, departure_date, arrival_date, price, seats_available) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $data[0], $data[1], $data[2], $data[3], 
                                $departure->format('Y-m-d H:i:s'), 
                                $arrival->format('Y-m-d H:i:s'), 
                                $data[6], $data[7]
                            ]);
                            break;
                            
                        case 'hotels':
                            // Format: Name,Location,Description,PricePerNight,RoomsAvailable,Rating,Amenities
                            $stmt = $pdo->prepare("INSERT INTO hotels 
                                (name, location, description, price_per_night, rooms_available, rating, amenities) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $stmt->execute([
                                $data[0], $data[1], $data[2], $data[3], $data[4], $data[5], $data[6]
                            ]);
                            break;
                            
                        // Add cases for cars and tours similarly
                    }
                    $imported++;
                } catch (PDOException $e) {
                    $errors[] = "Error importing row: " . implode(',', $data) . " - " . $e->getMessage();
                }
            }
            fclose($handle);
            $success = "Successfully imported $imported records!";
        }
    } else {
        $error = "Error uploading file.";
    }
}
?>

<!-- Add to your HTML form -->
<div class="card">
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="import_type" class="form-label">Import Type</label>
                    <select class="form-select" id="import_type" name="import_type" required>
                        <option value="">Select Type</option>
                        <option value="flights">Flights</option>
                        <option value="hotels">Hotels</option>
                        <option value="cars">Cars</option>
                        <option value="tours">Tours</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="csv_file" class="form-label">CSV File</label>
                    <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv" required>
                    <small class="text-muted">Download <a href="sample_import.csv">sample CSV format</a></small>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </div>
        </form>
        
        <?php if(!empty($errors)): ?>
            <div class="mt-4">
                <h5>Import Errors:</h5>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>