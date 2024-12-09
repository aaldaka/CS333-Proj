<?php
include '../config/db_config.php';  
// Function to ensure no conflict booking for the user
function checkUserConflict($pdo, $user_id, $start_datetime, $end_time) {
  $stmt = $pdo->prepare("SELECT * FROM bookings 
                         WHERE user_id = :user_id 
                         AND status = 'booked'
                         AND ((start_time < :end_time AND end_time > :start_datetime))");
  $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
  $stmt->bindParam(':start_datetime', $start_datetime, PDO::PARAM_STR);
  $stmt->bindParam(':end_time', $end_time, PDO::PARAM_STR);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Function to check room availability
function checkRoomAvailability($pdo, $room_id, $start_datetime, $end_time) {
  $stmt = $pdo->prepare("SELECT * FROM bookings 
                         WHERE room_id = :room_id 
                         AND status = 'booked'
                         AND ((start_time < :end_time AND end_time > :start_datetime))");
  $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
  $stmt->bindParam(':start_datetime', $start_datetime, PDO::PARAM_STR);
  $stmt->bindParam(':end_time', $end_time, PDO::PARAM_STR);
  $stmt->execute();
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
// Function to book a room
function bookRoom($pdo, $user_id, $room_id, $start_time, $duration) {
  echo "Raw User Input: <br>";
    echo "Start Time (user input) = $start_time<br>";
    echo "Duration = $duration minutes<br>";
    // Calculate the end time based on the duration (in minutes)
    $end_time = date('Y-m-d H:i:s', strtotime($start_time . ' + ' . $duration . ' minutes'));
    // Insert the booking into the database
    $insert_query = "INSERT INTO bookings (user_id, room_id, start_time, end_time, duration, status, date) 
                     VALUES (:user_id, :room_id, :start_time, :end_time, :duration, 'booked', :date)";
    
    $stmt = $pdo->prepare($insert_query);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->bindParam(':start_time', $start_time, PDO::PARAM_STR);
    $stmt->bindParam(':end_time', $end_time, PDO::PARAM_STR);
    $stmt->bindParam(':duration', $duration, PDO::PARAM_INT);
    echo "Raw User Input: <br>";
    echo "Start Time (user input) = $start_time<br>";
    echo "Duration = $duration minutes<br>";
    
    if ($stmt->execute()) {
        return "Booking successful!";
    } else {
        return "Error: " . implode(", ", $stmt->errorInfo());
    }
}
?>
