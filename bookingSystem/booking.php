<?php
session_start();
include '../config/db_config.php';

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
    // Admin-defined schedules from PHP
    const roomSchedules = <?php echo $roomSchedulesJson; ?>;
    // Function to dynamically update time slots
    function updateTimeSlots() {
        const duration = parseInt(document.getElementById('duration').value); // Get selected duration
        const startTimeSelect = document.getElementById('start_time'); // Dropdown
        const dateInput = document.getElementById('date'); // Date input
        const selectedDate = new Date(dateInput.value); // Selected date
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
        // Get the day of the week (e.g., 'Monday')
        const dayOfWeek = selectedDate.toLocaleString('en-US', { weekday: 'long' });
        // DEBUG: Log selected day and room schedules
        console.log('Selected Day:', dayOfWeek);
        console.log('Room Schedules:', roomSchedules);
        // Filter schedules for the selected day
        const schedulesForDay = roomSchedules.filter(schedule => schedule.day_of_week === dayOfWeek);
        // DEBUG: Log schedules for the selected day
        console.log('Schedules for Day:', schedulesForDay);
        // If there are admin-defined schedules for the day, use them
        if (schedulesForDay.length > 0) {
            schedulesForDay.forEach(schedule => {
                const [startHour, startMinute] = schedule.start_time.split(':').map(Number); // Admin start time
                const [endHour, endMinute] = schedule.end_time.split(':').map(Number); // Admin end time
                let currentHour = startHour;
                let currentMinute = startMinute;
                while (currentHour < endHour || (currentHour === endHour && currentMinute < endMinute)) {
                    // Calculate the start time in HH:MM format
                    const startTime = `${String(currentHour).padStart(2, '0')}:${String(currentMinute).padStart(2, '0')}`;
                    // Calculate the end time based on the interval
                    const endTime = new Date(new Date(`1970-01-01T${startTime}:00`).getTime() + duration * 60000);
                    const endTimeHour = endTime.getHours();
                    const endTimeMinute = endTime.getMinutes();
                    // Ensure end time is within the schedule range
                    if (
                        endTimeHour < endHour ||
                        (endTimeHour === endHour && endTimeMinute <= endMinute)
                    ) {
                        // Convert start and end times to 12-hour format
                        const startTimeDisplay = new Date(`1970-01-01T${startTime}:00`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                        const endTimeDisplay = new Date(`1970-01-01T${String(endTimeHour).padStart(2, '0')}:${String(endTimeMinute).padStart(2, '0')}:00`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                        // Generate the display string
                        const totalTimeDisplay = `${startTimeDisplay} - ${endTimeDisplay}`;
                        // Append the new option to the dropdown
                        const option = document.createElement('option');
                        option.value = startTime; // Use start time as the value
                        option.textContent = totalTimeDisplay; // Display start-end time
                        startTimeSelect.appendChild(option);
                    }
                    // Increment time by the interval
                    currentMinute += intervalMinutes % 60; // Add remaining minutes
                    currentHour += Math.floor(intervalMinutes / 60); // Add hours
                    if (currentMinute >= 60) {
                        currentMinute -= 60;
                        currentHour++;
                    }
                }
            });
        } else {
            // If no admin-defined schedules exist for the day, use the default time range
            console.log('No admin-defined schedules for this day. Using fallback.');
            const startHour = 8; // Default start at 8:00 AM
            const endHour = 20; // Default end at 8:00 PM
            let currentHour = startHour;
            let currentMinute = 0;
            while (currentHour < endHour || (currentHour === endHour && currentMinute < 60)) {
                // Calculate the start time
                const startTime = `${String(currentHour).padStart(2, '0')}:${String(currentMinute).padStart(2, '0')}`;
                const endTime = new Date(new Date(`1970-01-01T${startTime}:00`).getTime() + duration * 60000);
                // Convert start and end times to 12-hour format
                const startTimeDisplay = new Date(`1970-01-01T${startTime}:00`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                const endTimeDisplay = endTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                // Generate the display string
                const totalTimeDisplay = `${startTimeDisplay} - ${endTimeDisplay}`;
                // Append the new option to the dropdown
                const option = document.createElement('option');
                option.value = startTime; // Use start time as the value
                option.textContent = totalTimeDisplay; // Display start-end time
                startTimeSelect.appendChild(option);
                // Increment time by the interval
                currentMinute += intervalMinutes % 60; // Add remaining minutes
                currentHour += Math.floor(intervalMinutes / 60); // Add hours
                if (currentMinute >= 60) {
                    currentMinute -= 60;
                    currentHour++;
                }
            }
        }
    }
</script>
</head>
<body>
    <main class="container">
        <h1>Book Room: <?php echo $room_name; ?></h1>
        <p>Please choose the time and date for <strong><?php echo $room_name; ?></strong>.</p>
          <!-- Display error message if present -->
          <?php if (isset($_GET['error'])): ?>
            <script>
                alert("<?php echo htmlspecialchars($_GET['error']); ?>");
            </script>
            <div class="error-message">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <form action="book_room.php" method="POST">
            <!-- Pass room_id as a hidden input -->
            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
            <!-- Pass room_name as a hidden input -->
            <input type="hidden" name="room_name" value="<?php echo $room_name; ?>">

            <label for="date">Date</label>
            <input type="date" name="date" id="date" min="<?php echo date('Y-m-d'); ?>" required>
                <script>
                    const dateInput = document.getElementById('date');

                    dateInput.addEventListener('input', function () {
                        const selectedDate = new Date(this.value);
                        const dayOfWeek = selectedDate.getUTCDay(); // 5 = Friday, 6 = Saturday

                        // Prevent weekends (Friday = 5, Saturday = 6)
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
