<?php
session_start();

include 'db_config.php';  // Include the database connection
include 'booking_functions.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: config/login.php');
    exit();
}

try {
    // Retrieve the user ID from the session
    $user_id = $_SESSION['user_id'];

    // Check if the form data is set
    if (!isset($_POST['room_id'], $_POST['start_time'], $_POST['duration'])) {
        echo json_encode(['status' => 'error', 'message' => 'Missing required form data.']);
        exit();
    }

    // Collect the form data
    $room_id = $_POST['room_id'];
    $start_time = date('Y-m-d H:i:s', strtotime($_POST['start_time']));
    $duration = $_POST['duration'];  // Duration in minutes

    // Check if the room exists
    $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = :room_id");
    $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() === 0) {
        // Room does not exist
        echo json_encode(['status' => 'error', 'message' => 'The selected room does not exist.']);
        exit();
    }

    // Calculate the end time based on the duration
    $end_time = date('Y-m-d H:i:s', strtotime($start_time . ' + ' . $duration . ' minutes'));

    // Check room availability
    $room_conflicts = checkRoomAvailability($pdo, $room_id, $start_time, $end_time);
    if (count($room_conflicts) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'This room is already booked for the selected time.']);
        exit();
    }

    // Check for any conflicting user bookings
    $user_conflicts = checkUserConflict($pdo, $user_id, $start_time, $end_time);
    if (count($user_conflicts) > 0) {
        echo json_encode(['status' => 'error', 'message' => 'You already have a booking for the selected time.']);
        exit();
    }

    // Proceed with booking the room
    $booking_result = bookRoom($pdo, $user_id, $room_id, $start_time, $duration);
    echo json_encode(['status' => 'success', 'message' => $booking_result]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>