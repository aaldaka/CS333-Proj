<?php
// Start session to access session variables
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../LoginandRegistration/login.php");
    exit;
}
// Include database configuration
include('../config/db_config.php');

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
    // Get day of week for the given date
    $day_of_week = date('l', strtotime($date));
    
    // First check if the room is scheduled for this day
    $schedule_query = "SELECT start_time, end_time FROM room_schedules 
                      WHERE room_id = :room_id AND day_of_week = :day_of_week";
    $schedule_stmt = $pdo->prepare($schedule_query);
    $schedule_stmt->execute(['room_id' => $room_id, 'day_of_week' => $day_of_week]);
    $schedule = $schedule_stmt->fetch(PDO::FETCH_ASSOC);
    
    // Check if any schedules exist for this room
    $check_room_query = "SELECT COUNT(*) FROM room_schedules WHERE room_id = :room_id";
    $check_stmt = $pdo->prepare($check_room_query);
    $check_stmt->execute(['room_id' => $room_id]);
    $has_any_schedule = $check_stmt->fetchColumn() > 0;

    // If room has schedules but none for this day, return empty
    if ($has_any_schedule && !$schedule) {
        return [];
    }
    
    // Use schedule times if available, otherwise use default times
    if ($schedule) {
        $operating_hours_start = new DateTime("$date " . $schedule['start_time']);
        $operating_hours_end = new DateTime("$date " . $schedule['end_time']);
    } else {
        $operating_hours_start = new DateTime("$date 08:00:00");
        $operating_hours_end = new DateTime("$date 17:00:00");
    }
    
    $slot_duration = 50; // Slot duration in minutes

    // Rest of the existing getAvailableSlots function remains the same
    $query = "SELECT start_time, duration, end_time FROM bookings 
              WHERE room_id = :room_id AND DATE(start_time) = :date AND status = 'booked'";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['room_id' => $room_id, 'date' => $date]);
    $booked_slots = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Initialize array to store slots of different durations
    $all_duration_slots = [
        '50' => [],
        '75' => [],
        '100' => []
    ];
    
    // Array of slot durations to check
    $slot_durations = [50, 75, 100];

    foreach ($slot_durations as $slot_duration) {
        // Generate slots for each duration
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
                    ($slot_start >= $booked_start && $slot_start < $booked_end) ||
                    ($slot_end > $booked_start && $slot_end <= $booked_end) ||
                    ($slot_start <= $booked_start && $slot_end >= $booked_end)
                ) {
                    $is_available = false;
                    break;
                }
            }

            // Only add slot if it ends before or at operating hours end
            if ($is_available && $slot_end <= $operating_hours_end) {
                $all_duration_slots[$slot_duration][] = [
                    'time' => $slot_start->format('H:i') . ' - ' . $slot_end->format('H:i'),
                    'duration' => $slot_duration
                ];
            }

            // Move to the next slot
            $current_time->modify("+$slot_duration minutes");
        }
    }

    return $all_duration_slots;
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
    
    <!-- Bulma CSS -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../Rooms/styles2.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <ul class="sidebar-links">
    <li><a href="home.php"><i class="fas fa-home"></i> HOME</a></li>
    <li><a href="rooms.php"><i class="fas fa-door-open"></i> ROOMS</a></li>
        <li><a href="../bookingSystem/booking_list.php"><i class="fas fa-calendar-check"></i> BOOKINGS</a></li>
        <li><a href="../ReportingSystem/ReportingAndAnalytics.php"><i class="fas fa-chart-line"></i> REPORTING</a></li>
        <li><a href="../Profile/profile.php"><i class="fas fa-user"></i> PROFILE</a></li>
        <!-- Only display Admin link if user_type is admin -->
        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
            <li><a href="../AdminFuncs/admin.php"><i class="fas fa-user-shield"></i> ADMIN</a></li>
        <?php endif; ?>
        <li><a href="../LoginandRegistration/login.php"><i class="fas fa-sign-out-alt"></i> LOGOUT</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Toggle Button -->
    <button class="sidebar-toggle" onclick="toggleSidebar()">&#9776;</button>

    <div class="content-wrapper">
    <header class="header">
        <h1>Room Details</h1>
    </header>

    <main>
 <div class="container">
    <div class="content-box">
       
            <?php if (!empty($room)): ?>
                
                    
                <div class="room-details" id="roomDetails">
                    
                    <h2><?php echo htmlspecialchars($room['name']); ?></h2>
                    <p><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']); ?></p>
                    <p><strong>Equipment:</strong> <?php echo htmlspecialchars($room['equipment']); ?></p>
                    <p><strong>Created At:</strong> <?php echo htmlspecialchars($room['created_at']); ?></p>

                    <!-- Date Selection Form -->
                    <h3>Select Dates to View Available Slots</h3>
                    <form method="POST" action="room_details.php?room_id=<?php echo $room_id; ?>">
                        <div class="date-inputs-container">
                            <div class="date-input-group">
                                <label for="start_date">Start Date:</label>
                                <input type="date" id="start_date" name="start_date" required value="<?php echo $start_date; ?>">
                            </div>

                            <div class="date-input-group">
                                <label for="end_date">End Date:</label>
                                <input type="date" id="end_date" name="end_date" required value="<?php echo $end_date; ?>">
                            </div>
                        </div>

                        <button type="submit" class="button">View Available Slots</button>
                    </form>

                    <h3>Available Timeslots from <?php echo $start_date; ?> to <?php echo $end_date; ?></h3>
<?php if (!empty($available_slots_for_days)): ?>
    <?php foreach ($available_slots_for_days as $date => $durations): ?>
        <h4>Available Slots for <?php echo htmlspecialchars($date); ?>:</h4>
        <?php foreach ($durations as $duration => $slots): ?>
            <h5><?php echo $duration; ?> Minutes Duration:</h5>
            <div class="timeslot-container">
                <?php if (!empty($slots)): ?>
                    <?php foreach ($slots as $slot): ?>
                        <span class="timeslot"><?php echo htmlspecialchars($slot['time']); ?> (<?php echo $slot['duration']; ?> mins)</span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No available <?php echo $duration; ?>-minute slots for this duration.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php else: ?>
    <p>No available slots for this room.</p>
<?php endif; ?>


                    <!-- Booking button -->
                    <a href="../bookingSystem/booking.php?room_id=<?php echo urlencode($room['room_id']); ?>&room_name=<?php echo urlencode($room['name']); ?>" class="button">
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
        </div>
    </main>
    </div>
</div>



<script>
    // JavaScript for toggle functionality
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
    }
</script>
</body>
</html>
