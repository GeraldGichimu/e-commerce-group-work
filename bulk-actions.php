<?php
session_start();
include '../includes/config.php';

if(!isset($_SESSION['admin_logged_in'])) {
    header("Location: index.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['bulk_action']) && !empty($_POST['bulk_ids'])) {
    $ids = $_POST['bulk_ids'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
    try {
        switch($_POST['bulk_action']) {
            case 'delete':
                // Check for bookings first
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE item_id IN ($placeholders)");
                $stmt->execute($ids);
                $booking_count = $stmt->fetchColumn();
                
                if($booking_count > 0) {
                    $_SESSION['error'] = "Cannot delete items with existing bookings.";
                } else {
                    $stmt = $pdo->prepare("DELETE FROM flights WHERE id IN ($placeholders)");
                    $stmt->execute($ids);
                    $_SESSION['success'] = count($ids) . " items deleted successfully.";
                }
                break;
                
            case 'activate':
                $stmt = $pdo->prepare("UPDATE flights SET active = 1 WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $_SESSION['success'] = count($ids) . " items activated.";
                break;
                
            case 'deactivate':
                $stmt = $pdo->prepare("UPDATE flights SET active = 0 WHERE id IN ($placeholders)");
                $stmt->execute($ids);
                $_SESSION['success'] = count($ids) . " items deactivated.";
                break;
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error performing bulk action: " . $e->getMessage();
    }
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>