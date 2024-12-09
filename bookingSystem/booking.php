<?php
session_start();
include '../config/db_config.php';  

// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get booking ID from URL and ensure it's valid
if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];

    // Check if the booking belongs to the current user
    $sql = "SELECT user_id FROM bookings WHERE booking_id = :booking_id AND user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        // Mark the booking as cancelled
        $sql = "UPDATE bookings SET status = 'cancelled' WHERE booking_id = :booking_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':booking_id', $booking_id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirect to the bookings page with a success message
        header('Location: booking_list.php?message=Booking cancelled successfully');
        exit();
    } else {
        // Redirect with an error if booking doesn't belong to the user
        header('Location: booking_list.php?error=Invalid booking');
        exit();
    }
} else {
    // Redirect with an error if no booking ID is provided
    header('Location: booking_list.php?error=No booking specified');
    exit();
}
?>
