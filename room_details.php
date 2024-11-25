<?php
//ths will show the details of the rooms
// db_config.php for database connection
include('db_config.php');

// Get the room ID from the query parameter
$room_id = isset($_GET['room_id']) ? (int) $_GET['room_id'] : 0;

if ($room_id == 0) {
    die("Invalid room ID.");
}

// Fetch room details from the database
$query = "SELECT * FROM rooms WHERE room_id = :room_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
$stmt->execute();
$room = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$room) {
    die("Room not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking System - Room Details</title>
</head>
<body>

    <h1>Room Details</h1>

    <div class="room-details">
        <h2><?php echo htmlspecialchars($room['name']); ?></h2>
        <p><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']); ?></p>
        <p><strong>Equipment:</strong> <?php echo htmlspecialchars($room['equipment']); ?></p>

        <!-- Display Room's Schedule (if available) -->
        <?php
        $schedule_query = "SELECT * FROM room_schedules WHERE room_id = :room_id";
        $schedule_stmt = $pdo->prepare($schedule_query);
        $schedule_stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
        $schedule_stmt->execute();
        $schedules = $schedule_stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($schedules) {
            echo "<h3>Room Schedule</h3>";
            foreach ($schedules as $schedule) {
                echo "<p><strong>" . htmlspecialchars($schedule['day_of_week']) . ":</strong> " . htmlspecialchars($schedule['start_time']) . " - " . htmlspecialchars($schedule['end_time']) . "</p>";
            }
        }
        ?>

        <h3>Book This Room</h3>
        <!-- Booking form goes here (optional for now) -->

    </div>

</body>
</html>
