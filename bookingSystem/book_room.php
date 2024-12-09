<?php
session_start();
// Include necessary files
include '../config/db_config.php';  // Include the database connection
include 'booking_functions.php';  // Include helper functions

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../config/login.php');
    exit();
}

// Check if required POST data is set
if (!isset($_POST['room_id'], $_POST['room_name'], $_POST['date'], $_POST['duration'], $_POST['start_time'])) {
    redirectWithError('Booking failed: Missing required form data.', 'booking.php');
}

try {
    // Retrieve and sanitize input data
    $user_id = $_SESSION['user_id'];
    $room_id = htmlspecialchars($_POST['room_id']);
    $room_name = htmlspecialchars($_POST['room_name']);
    $date = htmlspecialchars($_POST['date']);
    $start_time = htmlspecialchars($_POST['start_time']);
    $duration = (int)$_POST['duration'];

    // Combine the date and time into a full datetime string
    $start_datetime = $date . ' ' . $start_time . ':00';  // e.g., '2024-12-08 08:00:00'
    echo $start_datetime;

    // Calculate the end time based on the duration
    $end_time = date('Y-m-d H:i:s', strtotime($start_datetime . ' + ' . $duration . ' minutes'));

    // Get the current date and time
    $current_datetime = new DateTime();
    $current_datetime->setTime($current_datetime->format('H'), 0, 0); // Round to the start of the current hour

    $start_datetime_obj = new DateTime($start_datetime);

    // **Validation: Prevent past bookings**
    if ($start_datetime_obj <= $current_datetime) {
        redirectWithError("Booking failed: You cannot select a past date or time.", 'booking.php', $room_id, $room_name);
    }



    // Check if the room exists
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_id = :room_id");
    $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() === 0) {
        redirectWithError('Booking failed: The selected room does not exist.', 'booking.php', $room_id, $room_name);
    }

    // Check room availability
    $room_conflicts = checkRoomAvailability($pdo, $room_id, $start_datetime, $end_time);
    if (count($room_conflicts) > 0) {
        redirectWithError('Booking failed: This room is already booked for the selected time.', 'booking.php', $room_id, $room_name);
    }

    // Check for any conflicting user bookings
    $user_conflicts = checkUserConflict($pdo, $user_id, $start_datetime, $end_time);
    if (count($user_conflicts) > 0) {
        redirectWithError('Booking failed: You already have a booking for the selected time.', 'booking.php', $room_id, $room_name);
    }

    // Proceed with booking the room
    $stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, start_time, end_time, duration, status) 
                           VALUES (:user_id, :room_id, :start_time, :end_time, :duration, 'booked')");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->bindParam(':start_time', $start_datetime, PDO::PARAM_STR);
    $stmt->bindParam(':end_time', $end_time, PDO::PARAM_STR);
    $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
    $stmt->execute();

    // Redirect with success message
    header("Location: booking_list.php?message=" . urlencode("Booking successful!"));
    exit();
} catch (PDOException $e) {
    // Handle database errors gracefully
    redirectWithError('Booking failed: ' . $e->getMessage(), 'booking.php', $room_id, $room_name);
}

/**
 * Redirects to a specified page with an error message
 */
function redirectWithError($message, $location, $room_id = null, $room_name = null) {
    // Store the error message as a URL parameter
    $error_message = urlencode($message);
    // Append room_id and room_name to the URL if they are provided
    if ($room_id && $room_name) {
        $location .= "?room_id=$room_id&room_name=" . urlencode($room_name) . "&error=$error_message";
    } else {
        $location .= "?error=$error_message";
    }
    // Redirect to the location
    header("Location: $location");
    exit();
}
?>
