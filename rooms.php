<?php
//this will list all available rooms
// db_config.php for database connection
include('db_config.php');

// Fetch all rooms from the database
$query = "SELECT * FROM rooms";
$stmt = $pdo->prepare($query);
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking System - Browse Rooms</title>
</head>
<body>

    <h1>Available Rooms</h1>

    <div class="room-list">
        <?php foreach ($rooms as $room): ?>
            <div class="room">
                <h2><?php echo htmlspecialchars($room['name']); ?></h2>
                <p><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']); ?></p>
                <p><strong>Equipment:</strong> <?php echo htmlspecialchars($room['equipment']); ?></p>
                <a href="room_details.php?room_id=<?php echo $room['room_id']; ?>">View Details</a>
            </div>
        <?php endforeach; ?>
    </div>

</body>
</html>
