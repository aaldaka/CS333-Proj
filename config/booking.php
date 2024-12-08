<?php
session_start();
include 'db_config.php';

// Ensure room_id is provided
if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];

    // Fetch room details from the database
    $query = "SELECT room_name FROM rooms WHERE id = :room_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
    $stmt->execute();
    $room = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($room) {
        $room_name = htmlspecialchars($room['room_name']); // Escape for security
    } else {
        die("Room not found.");
    }
} else {
    die("Room ID not provided.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking</title>
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@latest/css/pico.min.css">
    <link rel="stylesheet" href="styles2.css">

    <script>
        // Update time slots based on selected duration
        function updateTimeSlots() {
            const duration = parseInt(document.getElementById('duration').value);
            const startTimeSelect = document.getElementById('start_time');

            // Clear existing time slots
            startTimeSelect.innerHTML = '';

            // Define interval in minutes based on duration
            let intervalMinutes = 0;
            if (duration === 50) {
                intervalMinutes = 60; // Treat 50 minutes as 1-hour intervals
            } else if (duration === 75) {
                intervalMinutes = 90; // Treat 75 minutes as 1.5-hour intervals
            } else if (duration === 100) {
                intervalMinutes = 120; // Treat 100 minutes as 2-hour intervals
            }

            // Generate time slots dynamically
            const startHour = 8; // Start at 8:00 AM
            const endHour = 20; // End at 8:00 PM
            let currentHour = startHour;
            let currentMinute = 0;

            while (currentHour < endHour) {
                // Calculate start time
                let startTime = `${String(currentHour).padStart(2, '0')}:${String(currentMinute).padStart(2, '0')}`;
                
                // Calculate end time based on interval
                let endTime = new Date(new Date(`1970-01-01T${startTime}:00Z`).getTime() + duration * 60000);
                let endTimeString = endTime.toISOString().substr(11, 5);
                
                // Convert to 12-hour format
                const startTimeDisplay = new Date(`1970-01-01T${startTime}:00Z`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                const endTimeDisplay = new Date(`1970-01-01T${endTimeString}:00Z`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

                // Generate display string
                const totalTimeDisplay = `${startTimeDisplay} - ${endTimeDisplay}`;

                // Append new option
                const option = document.createElement('option');
                option.value = startTime;
                option.textContent = totalTimeDisplay;
                startTimeSelect.appendChild(option);

                // Increment time by the interval
                currentHour += Math.floor(intervalMinutes / 60); // Add hours
                currentMinute += intervalMinutes % 60; // Add remaining minutes

                // Handle overflow of minutes
                if (currentMinute >= 60) {
                    currentMinute -= 60;
                    currentHour++;
                }
            }
        }
    </script>
</head>
<body>
    <main class="container">
        <h1>Book Room: <?php echo $room_name; ?></h1>
        <p>Please choose the time and date for <strong><?php echo $room_name; ?></strong>.</p>
        <form action="book_room.php" method="POST">
            <!-- Pass room_id as a hidden input -->
            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">

            <label for="date">Date</label>
            <input type="date" name="date" id="date" required>

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

            <label for="start_time">Start Time</label>
            <select name="start_time" id="start_time" required>
                <!-- Time slots will be generated here dynamically -->
            </select>

            <p id="errorMessage" style="color:red;"></p>

            <button type="submit">Book Room</button>
        </form>
    </main>

    <script>
        // Initialize time slots when the page loads
        window.onload = function() {
            updateTimeSlots();
        }
    </script>
</body>
</html>
