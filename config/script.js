<?php
// Include database configuration
include('db_config.php');

// Define the function for fetching available slots for multiple days
function getAvailableSlotsForMultipleDays($room_id, $start_date, $end_date, $pdo) {
    // Initialize an array to hold available slots for each day
    $available_slots_for_days = [];

    // Convert start date and end date to DateTime objects
    $current_date = new DateTime($start_date);
    $end_date = new DateTime($end_date);

    // Loop through each day in the date range
    while ($current_date <= $end_date) {
        $date = $current_date->format('Y-m-d');

        // Call the function to get available slots for this specific date
        $available_slots_for_days[$date] = getAvailableSlots($room_id, $date, $pdo);

        // Move to the next day
        $current_date->modify("+1 day");
    }

    return $available_slots_for_days;
}

// Define the function for fetching available slots for a single day (same as before)
function getAvailableSlots($room_id, $date, $pdo) {
    // Define operating hours and slot duration
    $operating_hours_start = new DateTime("$date 09:00:00"); // Start time of room
    $operating_hours_end = new DateTime("$date 17:00:00"); // End time of room
    $slot_duration = 60; // Slot duration in minutes

    // Fetch all booked slots for the room on the given date
    $query = "SELECT start_time, duration, end_time FROM bookings 
              WHERE room_id = :room_id AND DATE(start_time) = :date AND status = 'booked'";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['room_id' => $room_id, 'date' => $date]);
    $booked_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate all possible slots
    $available_slots = [];
    $current_time = clone $operating_hours_start;

    while ($current_time < $operating_hours_end) {
        $slot_start = clone $current_time;
        $slot_end = clone $current_time;
        $slot_end->modify("+$slot_duration minutes");

        // Check if this slot overlaps with any booked slot
        $is_available = true;
        foreach ($booked_slots as $booked) {
            $booked_start = new DateTime($booked['start_time']);
            $booked_end = new DateTime($booked['end_time']);

            if (
                ($slot_start >= $booked_start && $slot_start < $booked_end) || // Slot start in booked range
                ($slot_end > $booked_start && $slot_end <= $booked_end) ||     // Slot end in booked range
                ($slot_start <= $booked_start && $slot_end >= $booked_end)    // Slot fully overlaps a booked range
            ) {
                $is_available = false;
                break;
            }
        }

        if ($is_available) {
            $available_slots[] = $slot_start->format('H:i') . ' - ' . $slot_end->format('H:i');
        }

        // Move to the next slot
        $current_time->modify("+$slot_duration minutes");
    }

    return $available_slots;
}

// Fetch the room details and booked slots
if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];

    // Handle the dates from the form input
    if (isset($_POST['start_date']) && isset($_POST['end_date'])) {
        $start_date = $_POST['start_date']; // Start date from the form
        $end_date = $_POST['end_date'];     // End date from the form
    } else {
        // Default to showing a single day's slots if no date is provided
        $start_date = '2024-12-01'; // Default start date
        $end_date = '2024-12-01';   // Default end date
    }

    try {
        // Fetch the room details
        $query = "SELECT * FROM rooms WHERE room_id = :room_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
        $stmt->execute();
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch available slots for the room from start date to end date
        $available_slots_for_days = getAvailableSlotsForMultipleDays($room_id, $start_date, $end_date, $pdo);

    } catch (Exception $e) {
        $error = "Error fetching room details: " . $e->getMessage();
    }
} else {
    $error = "No room ID provided.";
}
?>

<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Details</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles2.css">

    
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <ul class="sidebar-links">
        <li><a href="home.php">HOME</a></li>
        <li><a href="rooms.php">ROOMS</a></li>
        <li><a href="bookings.php">BOOKINGS</a></li>
        <li><a href="profile.php">PROFILE</a></li>
        <li><a href="login.php">LOGOUT</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">&#9776;</button>

    <header class="header">
        <h1>Room Details</h1>
    </header>

    <main>
        <section>
            <?php if (!empty($room)): ?>
                <div class="room-details">
                    <h2><?php echo htmlspecialchars($room['name']); ?></h2>
                    <p><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']); ?></p>
                    <p><strong>Equipment:</strong> <?php echo htmlspecialchars($room['equipment']); ?></p>
                    <p><strong>Created At:</strong> <?php echo htmlspecialchars($room['created_at']); ?></p>

                    <!-- Date Selection Form -->
                    <h3>Select Dates to View Available Slots</h3>
                    <form method="POST" action="room_details.php?room_id=<?php echo $room_id; ?>">
                        <label for="start_date">Start Date:</label>
                        <input type="date" id="start_date" name="start_date" required value="<?php echo $start_date; ?>">

                        <label for="end_date">End Date:</label>
                        <input type="date" id="end_date" name="end_date" required value="<?php echo $end_date; ?>">

                        <button type="submit">View Available Slots</button>
                    </form>

                    <h3>Available Timeslots from <?php echo $start_date; ?> to <?php echo $end_date; ?></h3>
                    <?php if (!empty($available_slots_for_days)): ?>
                        <?php foreach ($available_slots_for_days as $date => $slots): ?>
                            <h4>Available Slots for <?php echo htmlspecialchars($date); ?>:</h4>
                            <?php if (!empty($slots)): ?>
                                <ul class="timeslots">
                                    <?php foreach ($slots as $slot): ?>
                                        <li><?php echo htmlspecialchars($slot); ?> (Available)</li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php else: ?>
                                <p>No available timeslots for this day.</p>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No available slots for this room.</p>
                    <?php endif; ?>

                    <!-- Booking button -->
                    <a href="booking.php?room_id=<?php echo urlencode($room['room_id']); ?>&room_name=<?php echo urlencode($room['name']); ?>" class="button">
                        Book This Room
                    </a>
                    <br>
                    <a href="rooms.php" class="button">Back to Rooms</a>
                </div>
            <?php elseif (!empty($error)): ?>
                <div class="error-message">
                    <p><?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php else: ?>
                <div class="error-message">
                    <p>Room not found.</p>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>


<script>
    // JavaScript for toggle functionality
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
    }
</script>
</body>
</html>
