<?php
session_start(); // Start the session

// Ensure the user is logged in before accessing this page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../config/login.php"); 
    exit();
}

// Include necessary files for DB connection and booking logic
require '../config/db_config.php';

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

try {
    // Query to fetch bookings with room names, ordered by start_time
    $query = "
    SELECT 
        b.booking_id, 
        r.name, 
        DATE(b.start_time) AS booking_date, 
        b.start_time, 
        b.end_time, 
        CONCAT(
            TIME_FORMAT(b.start_time, '%h:%i %p'), ' - ', TIME_FORMAT(b.end_time, '%h:%i %p')
        ) AS time_slot, 
        b.duration, 
        b.status 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    WHERE b.user_id = :user_id
    ORDER BY b.start_time ASC
    ";
    $stmt = $pdo->prepare($query);
    
    // Bind the user_id to the query
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch the results
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle any errors that occur during the query
    echo "Error: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <link rel="stylesheet" href="bookings.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="sidebar">
    <ul class="sidebar-links">
    <li><a href="../Rooms/home.php"><i class="fas fa-home"></i> HOME</a></li>
    <li><a href="../Rooms/rooms.php"><i class="fas fa-door-open"></i> ROOMS</a></li>
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
        <button class="toggle-btn" onclick="toggleSidebar()">&#9776;</button>
    </div>
    <main class="container">
        <h2>Your Booking Information</h2>
        <table class="table is-fullwidth is-striped">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Booking Date</th>
                    <th>Time Slot</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Action</th> 
                </tr>
            </thead>
            <tbody>
                <?php
                if ($bookings) {
                    foreach ($bookings as $row) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['booking_date']) . "</td>"; 
                        echo "<td>" . htmlspecialchars($row['time_slot']) . "</td>"; 
                        echo "<td>" . htmlspecialchars($row['duration']) . " minutes</td>";

                        // Check if the booking's start date is in the past
                        $current_date = new DateTime(); // Current date and time
                        $start_date = !empty($row['start_time']) ? new DateTime($row['start_time']) : null; // Booking start date and time

                        if (strtolower($row['status']) === 'booked') {
                            echo "<td>Booked</td>";
                            
                            if ($start_date && $start_date < $current_date) {
                                // Start date is in the past, disable the cancel button
                                echo "<td>
                                        <button disabled title='Cannot cancel bookings starting in the past' 
                                                class='button is-light is-danger is-disabled'>
                                             Cancel
                                        </button>
                                      </td>";
                            } else {
                                // Start date is in the future, allow cancellation
                                echo "<td>
                                        <a href='cancel_booking.php?id=" . htmlspecialchars($row['booking_id']) . "' 
                                           onclick='return confirm(\"Are you sure you want to cancel this booking?\");' 
                                           class='button is-danger'>Cancel</a>
                                      </td>";
                            }
                        } else {
                            // Booking is already cancelled
                            echo "<td>Cancelled</td>";
                            echo "<td>---</td>"; // No action available for cancelled bookings
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No bookings found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </main>
    <script>
        // JavaScript for toggle functionality
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.main-content').classList.toggle('shifted');
        }
    </script>
</body>
</html>
<?php
// Close the database connection
$stmt = null;
$pdo = null;
?>
