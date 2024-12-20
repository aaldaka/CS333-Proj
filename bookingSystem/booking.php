<?php
session_start();
include '../config/db_config.php';
// Check if the user is logged in and redirect to the login page if not logged in
if (!isset($_SESSION['user_id'])) {
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
        <p>Please choose the time and date for <strong><?php echo $room_name; ?></strong>.</p>
        <!-- Display error message if present -->
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message" style="color: red;">
                <?php echo htmlspecialchars($_GET['error']); ?>
            </div>
        <?php endif; ?>
        <form action="book_room.php" method="POST">
            <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
            <input type="hidden" name="room_name" value="<?php echo $room_name; ?>">
            <label for="date">Choose Date</label>
            <input type="date" id="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>
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
                            updateTimeSlots(); 
                        }
                    });
                </script> 

            <label for="duration">Choose Duration</label>
            <select id="duration" name="duration" required>
                <option value="50">50 minutes</option>
                <option value="75">75 minutes</option>
                <option value="100">100 minutes</option>
            </select>
            <label for="start_time">Available Time Slots</label>
            <select id="start_time" name="start_time" required>
                <!-- Time slots will be dynamically generated -->
            </select>
            <button type="submit">Book Room</button>
        </form>
    </main>
    <script>
        // Admin-defined schedules from PHP
        const roomSchedules = <?php echo $roomSchedulesJson; ?>;

        // Function to dynamically update time slots
        function updateTimeSlots() {
            const dateInput = document.getElementById('date');
            const durationInput = document.getElementById('duration');
            const startTimeSelect = document.getElementById('start_time');
            const selectedDate = new Date(dateInput.value);
            const duration = parseInt(durationInput.value);

            // Clear existing time slots
            startTimeSelect.innerHTML = '';

            // Ensure a valid date is selected
            if (!selectedDate || isNaN(selectedDate.getTime())) {
                return;
            }

            // Get the current date and time
            const currentDate = new Date();
            const isToday = selectedDate.toDateString() === currentDate.toDateString();

            // Get the day of the week (e.g., "Monday")
            const dayOfWeek = selectedDate.toLocaleString('en-US', { weekday: 'long' });

            // Filter schedules for the selected day
            const schedulesForDay = roomSchedules.filter(schedule => schedule.day_of_week === dayOfWeek);

            // Function to generate time slots based on duration and start/end times
            const generateTimeSlots = (startHour, startMinute, endHour, endMinute) => {
                let currentHour = startHour;
                let currentMinute = startMinute;

                while (currentHour < endHour || (currentHour === endHour && currentMinute < endMinute)) {
                    // Create the start and end time for the current time slot
                    const startTime = `${String(currentHour).padStart(2, '0')}:${String(currentMinute).padStart(2, '0')}`;
                    const startDateTime = new Date(`1970-01-01T${startTime}:00`);
                    const endDateTime = new Date(startDateTime.getTime() + duration * 60000);

                    const endTimeHour = endDateTime.getHours();
                    const endTimeMinute = endDateTime.getMinutes();

                    // Ensure the end time fits within the schedule
                    if (endTimeHour < endHour || (endTimeHour === endHour && endTimeMinute <= endMinute)) {
                        // Check if the time slot is in the past (only for today's date)
                        if (isToday) {
                            const now = new Date();
                            const slotTime = new Date(selectedDate);
                            slotTime.setHours(currentHour, currentMinute, 0, 0);

                            if (slotTime <= now) {
                                // Skip this time slot if it's in the past
                                currentMinute = endTimeMinute;
                                currentHour = endTimeHour;
                                continue;
                            }
                        }

                        // Format time for display
                        const startTimeDisplay = startDateTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                        const endTimeDisplay = endDateTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                        const totalTimeDisplay = `${startTimeDisplay} - ${endTimeDisplay}`;

                        // Add the time slot to the dropdown
                        const option = document.createElement('option');
                        option.value = startTime;
                        option.textContent = totalTimeDisplay;
                        startTimeSelect.appendChild(option);

                        // Move to the next time slot
                        currentMinute = endTimeMinute;
                        currentHour = endTimeHour;

                        // For 50 mins: Align the next slot to the hour
                        if (duration === 50) {
                            if (currentMinute !== 0) {
                                currentMinute = 0;
                                currentHour++;
                            }
                        }

                        // For 75 mins: Add a 15-minute gap between slots
                        else if (duration === 75) {
                            currentMinute += 15; // Add 15-minute gap
                            if (currentMinute >= 60) {
                                currentMinute -= 60;
                                currentHour++;
                            }
                        }

                        // For 100 mins: Leave a gap of 20 minutes between slots
                        else if (duration === 100) {
                            currentMinute += 20;
                            if (currentMinute >= 60) {
                                currentMinute -= 60;
                                currentHour++;
                            }
                        }
                    } else {
                        break;
                    }
                }
            };

            if (schedulesForDay.length > 0) {
                // Use admin-defined schedules for the selected day
                schedulesForDay.forEach(schedule => {
                    const [startHour, startMinute] = schedule.start_time.split(':').map(Number);
                    const [endHour, endMinute] = schedule.end_time.split(':').map(Number);

                    generateTimeSlots(startHour, startMinute, endHour, endMinute);
                });
            } else {
                // If no schedules are defined for the selected day, use default timings (08:00 AM - 08:00 PM)
                generateTimeSlots(8, 0, 20, 0);
            }
        }

        // Function to clear error message
        function clearErrorMessage() {
            const errorMessage = document.querySelector('.error-message');
            if (errorMessage) {
                errorMessage.style.display = 'none';
            }
        }

        // Attach event listeners for date and duration changes
        document.getElementById('date').addEventListener('change', () => {
            clearErrorMessage();
            updateTimeSlots();
        });

        document.getElementById('duration').addEventListener('change', () => {
            clearErrorMessage();
            updateTimeSlots();
        });

        // Initialize time slots on page load
        window.onload = updateTimeSlots;
    </script>
</body>
</html>
