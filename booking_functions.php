<?php
include 'config/db_config.php';  


// Function to ensure no conflict booking for the user
function checkUserConflict($pdo, $user_id, $start_time, $end_time) {
  // Check if the user has any conflicting bookings in the time range
  $query = "SELECT * FROM bookings 
            WHERE user_id = :user_id 
            AND status = 'booked' 
            AND (start_time < :end_time AND end_time > :start_time)";

  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->bindParam(':start_time', $start_time, PDO::PARAM_STR);
  $stmt->bindParam(':end_time', $end_time, PDO::PARAM_STR);
  $stmt->execute();

  return $stmt->fetchAll(PDO::FETCH_ASSOC); // Returns conflicting bookings, if any
}

// Function to check room availability
function checkRoomAvailability($pdo, $room_id, $start_time, $end_time) {
  // Check if the room is already booked within the time range
  $query = "SELECT * FROM bookings 
            WHERE room_id = :room_id 
            AND status = 'booked' 
            AND (start_time < :end_time AND end_time > :start_time)";

  $stmt = $pdo->prepare($query);
  $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
  $stmt->bindParam(':start_time', $start_time, PDO::PARAM_STR);
  $stmt->bindParam(':end_time', $end_time, PDO::PARAM_STR);
  $stmt->execute();

  return $stmt->fetchAll(PDO::FETCH_ASSOC); // Returns conflicting bookings, if any
}

// Function to book a room
function bookRoom($pdo, $user_id, $room_id, $start_time, $duration) {
    // Calculate the end time based on the duration (in minutes)
    $end_time = date('Y-m-d H:i:s', strtotime($start_time . ' + ' . $duration . ' minutes'));

    // Insert the booking into the database
    $insert_query = "INSERT INTO bookings (user_id, room_id, start_time, end_time, duration) 
                     VALUES (:user_id, :room_id, :start_time, :end_time, :duration)";
    
    $stmt = $pdo->prepare($insert_query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->bindParam(':start_time', $start_time, PDO::PARAM_STR);
    $stmt->bindParam(':end_time', $end_time, PDO::PARAM_STR);
    $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        return "Booking successful!";
    } else {
        return "Error: " . implode(", ", $stmt->errorInfo());
    }
}
?>