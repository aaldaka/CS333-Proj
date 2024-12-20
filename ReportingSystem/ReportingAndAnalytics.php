<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$user_id = $_SESSION['user_id'];

require '../config/db_config.php';

// This query for the room usage
$stmt = $pdo->prepare("SELECT 
        r.name AS room_name, 
        COUNT(b.booking_id) AS total_bookings, 
        COALESCE(SUM(b.duration), 0) AS total_min
    FROM rooms r
    LEFT JOIN bookings b ON r.room_id = b.room_id
    WHERE b.status = 'booked'
    GROUP BY r.room_id
    ORDER BY total_min DESC
");

try {
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
} finally {
    $stmt->closeCursor();
}


// This querys for upcoming and past bookings
// Query for past bookings
$stmtPast = $pdo->prepare("SELECT
    b.booking_id,
    r.name AS room_name,
    b.start_time,
    b.end_time
FROM bookings b
LEFT JOIN rooms r ON b.room_id = r.room_id
WHERE 
    b.user_id = :user_id
    AND b.end_time < NOW()
    AND b.status = 'booked'
ORDER BY b.start_time DESC
");

$stmtPast->bindParam(':user_id', $user_id, PDO::PARAM_INT);

try {
    $stmtPast->execute();
    $pastBookings = $stmtPast->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
} finally {
    $stmtPast->closeCursor();
}



// Query for upcoming bookings
$stmtUpcoming = $pdo->prepare("SELECT
    b.booking_id,
    r.name AS room_name, 
    b.start_time,
    b.end_time
FROM bookings b
LEFT JOIN rooms r ON b.room_id = r.room_id  
WHERE 
    b.user_id = :user_id
    AND b.status = 'booked'
    AND b.start_time >= NOW()
ORDER BY b.start_time ASC
");


$stmtUpcoming->bindParam(':user_id', $user_id, PDO::PARAM_INT);

try {
    $stmtUpcoming->execute();
    $upcomingBookings = $stmtUpcoming->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
} finally {
    $stmtUpcoming->closeCursor(); 
}

?>

<html>
<head>
    <title>Reporting and Analytics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <link rel="stylesheet" href="Reporting.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> 
</head>
<body>
   <!-- Sidebar -->
<div class="sidebar">
    <ul class="sidebar-links">
    <li><a href="../Rooms/home.php"><i class="fas fa-home"></i> HOME</a></li>
    <li><a href="../Rooms/rooms.php"><i class="fas fa-door-open"></i> ROOMS</a></li>
        <li><a href="../bookingSystem/booking_list.php"><i class="fas fa-calendar-check"></i> BOOKINGS</a></li>        <li><a href="../Profile/profile.php"><i class="fas fa-user"></i> PROFILE</a></li>
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
  
<h2 class="main">Room Usage Summary</h2>

<!-- creat the pie chart for the total hours and a bar chart for the number of booking -->
<div class="chart-container">
    <canvas id="roomUsagePieChart"></canvas>
    <canvas id="roomBookingsBarChart"></canvas>
</div>

<script>
    const roomUsageData = <?= json_encode($rooms) ?>;

    if (!roomUsageData || roomUsageData.length === 0) {
        console.error("No data available for the charts.");
    } else {
        const roomNames = roomUsageData.map(room => room.room_name);
        const total_min = roomUsageData.map(room => room.total_min);

        const colors = [
            '#3B1E54',   // Dark purple
            '#9B7EBD',   // Light purple
            '#D4BEE4',   // Soft lavender
            '#EEEEEE',   // Light gray
        ];

        const backgroundColors = roomNames.map((_, index) => colors[index % colors.length]);

        // Create the pie chart for total hours
        const ctx = document.getElementById('roomUsagePieChart').getContext('2d');
        new Chart(ctx, {
            type: 'pie',
            data: {
                labels: roomNames,
                datasets: [{
                    label: 'Total Hours Booked',
                    data: total_min,
                    backgroundColor: backgroundColors,
                    borderColor: 'rgba(255, 255, 255, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let total = context.dataset.data.reduce((sum, value) => sum + parseFloat(value), 0);
                                let percentage = ((context.raw / total) * 100).toFixed(2);
                                return `${context.label}: ${context.raw} mins (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

    // Bar chart for the number of bookings
    const bookingCounts = roomUsageData.map(room => room.total_bookings);

    const ctxBar = document.getElementById('roomBookingsBarChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: roomNames, 
            datasets: [{
                label: 'Number of Bookings',
                data: bookingCounts, 
                backgroundColor: backgroundColors, 
                borderColor: 'rgba(0, 0, 0, 0.1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false 
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw} bookings`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Rooms'
                    }
                },
                y: {
                    title: {
                        display: true,
                        text: 'Number of Bookings'
                    },
                    beginAtZero: true
                }
            }
        }
    }
    );
}

</script>

<!-- Display the room usage summary in a table -->
<table class="table">
    <thead>
        <tr>
            <th>Room Name</th>
            <th>Total Bookings</th>
            <th>Total Minutes Booked</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($rooms)): ?>
            <!-- Loop through each room and display its details -->
            <?php foreach ($rooms as $room): ?>
                <tr>
                    <td><?= htmlspecialchars($room['room_name']) ?></td>
                    <td><?= htmlspecialchars($room['total_bookings']) ?></td>
                    <td><?= htmlspecialchars($room['total_min']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Display a message if no data is available -->
            <tr>
                <td colspan="3" class="no-data">No data available</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>


<h2>Upcoming Bookings</h2>

<!-- Table to display upcoming bookings -->
<table class="table">
    <thead>
        <tr>
            <th>Booking ID</th>
            <th>Room Name</th>
            <th>Start Time</th>
            <th>End Time</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($upcomingBookings)): ?>
            <!-- Loop through each upcoming booking -->
            <?php foreach ($upcomingBookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                    <td><?= htmlspecialchars($booking['room_name']) ?></td>
                    <td><?= htmlspecialchars($booking['start_time']) ?></td>
                    <td><?= htmlspecialchars($booking['end_time']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- If no upcoming bookings are found, display a message -->
            <tr>
                <td colspan="4" class="no-data">No upcoming bookings found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<h2>Past Bookings</h2>

<!-- Table to display past bookings -->
<table class="table">
    <thead>
        <tr>
            <th>Booking ID</th>
            <th>Room Name</th>
            <th>Start Time</th>
            <th>End Time</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($pastBookings)): ?>
            <!-- Loop through each past booking -->
            <?php foreach ($pastBookings as $booking): ?>
                <tr>
                    <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                    <td><?= htmlspecialchars($booking['room_name']) ?></td>
                    <td><?= htmlspecialchars($booking['start_time']) ?></td>
                    <td><?= htmlspecialchars($booking['end_time']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- If no past bookings are found, display a message -->
            <tr>
                <td colspan="4" class="no-data">No past bookings found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
    // JavaScript for toggle functionality
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
    }
</script>
</body>
</html>
