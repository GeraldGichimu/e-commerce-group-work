<?php
session_start();
include '../includes/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit();
}

$type = $_GET['type'] ?? 'flights';

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="' . $type . '_export_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');

switch($type) {
    case 'flights':
        fputcsv($output, ['ID', 'Airline', 'Flight Number', 'Departure City', 'Arrival City', 'Departure', 'Arrival', 'Price', 'Seats']);
        $stmt = $pdo->query("SELECT * FROM flights");
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            fputcsv($output, [
                $row['id'],
                $row['airline'],
                $row['flight_number'],
                $row['departure_city'],
                $row['arrival_city'],
                $row['departure_date'],
                $row['arrival_date'],
                $row['price'],
                $row['seats_available']
            ]);
        }
        break;
        
    // Add cases for other types similarly
}

fclose($output);
exit();
?>