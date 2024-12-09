<?php
session_start();
include '../config/db_config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: ../config/login.php");
    exit();
}

// Ensure room_id is provided
if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];
    // Fetch room details from the database
    $query = "SELECT name FROM rooms WHERE room_id = :room_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->execute();
    $room = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($room) {
        $room_name = htmlspecialchars($room['name']); // Escape for security
    } else {
        die("Room not found.");
    }
    // Fetch admin-defined schedules for the room
    $scheduleQuery = "SELECT day_of_week, start_time, end_time FROM room_schedules WHERE room_id = :room_id";
    $scheduleStmt = $pdo->prepare($scheduleQuery);
    $scheduleStmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $scheduleStmt->execute();
    $roomSchedules = $scheduleStmt->fetchAll(PDO::FETCH_ASSOC);
    // Convert schedules to JSON for use in JavaScript
    $roomSchedulesJson = json_encode($roomSchedules);
} else {
    die("Room ID not provided.");
}

// Check if there's an error message in the URL
$error_message = isset($_GET['error']) ? htmlspecialchars(urldecode($_GET['error'])) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <link rel="stylesheet" href="bookings.css">
    
</head>

<body>
    
    <main class="container">
        <h1>Book Room: <?php echo $room_name; ?></h1>

        <?php if ($error_message): ?>
            <div id="error_message" style="color: red;"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="book_room.php" method="POST">
            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
            <input type="hidden" name="room_name" value="<?php echo $room_name; ?>">
            <label for="date">Date</label>
            <input type="date" name="date" id="date" min="<?php echo date('Y-m-d'); ?>" required>
                <script>
                    const dateInput = document.getElementById('date');
                    dateInput.addEventListener('input', function () {
                        const selectedDate = new Date(this.value);
                        const dayOfWeek = selectedDate.getUTCDay(); // 5 = Friday
                        // Prevent weekends (Friday = 5)
                        if (dayOfWeek === 5 ) {
                            this.value = ''; // Clear the input
                            alert('Bookings are not allowed on Fridays!');
                        } else {
                            updateTimeSlots(); // Call updateTimeSlots if the date is valid
                        }
                    });
                </script> 
            <?php
                // Default duration to 50 minutes if it's not posted yet
                $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 50;
            ?>
            <label for="duration">Choose Duration</label>
            <select name="duration" id="duration" onchange="updateTimeSlots()">
                <option value="50" <?php if ($duration == 50) echo 'selected'; ?>>50 minutes</option>
                <option value="75" <?php if ($duration == 75) echo 'selected'; ?>>75 minutes</option>
                <option value="100" <?php if ($duration == 100) echo 'selected'; ?>>100 minutes</option>
            </select>
            <label for="start_time">Time Slots</label>
            <select name="start_time" id="start_time" required>
                <!-- Time slots will be generated here dynamically -->
            </select>
            <button type="submit">Book Room</button>
        </form>
    </main>
     <!-- Pass PHP data to JavaScript -->
    <script>
        const roomSchedules = <?php echo $roomSchedulesJson ?: '[]'; ?>;
    </script>

    <!-- Include the external JavaScript file -->
    <script src="booking.js"></script>
        
</body>
</html>
