<?php
// Include database configuration
include('db_config.php');

if (isset($_GET['room_id'])) {
    $room_id = $_GET['room_id'];

    try {
        // Fetch the room details
        $query = "SELECT * FROM rooms WHERE room_id = :room_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
        $stmt->execute();
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fetch the available timeslots for this room
        $timeslotsQuery = "SELECT * FROM timeslots WHERE room_id = :room_id";
        $timeslotsStmt = $pdo->prepare($timeslotsQuery);
        $timeslotsStmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
        $timeslotsStmt->execute();
        $timeslots = $timeslotsStmt->fetchAll(PDO::FETCH_ASSOC);
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
    <!-- the css pico style  -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <!-- Link to the external styles.css file -->
    <link rel="stylesheet" href="styles.css">

    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        height: 100vh;
        text-align: center;
    }

    .main-content {
        width: 80%;
        max-width: 800px;
        margin-top: 50px; /* Adjust as needed for spacing */
    }

    header h1 {
        font-size: 2em;
        margin-bottom: 20px;
    }

    section {
        margin-top: 30px;
    }

    p {
        font-size: 1.2em;
        margin: 10px 0;
    }

    .error-message {
        color: red;
        font-weight: bold;
    }
</style>

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

    <header>
        <h1>Room Details</h1>
    </header>

    <main>
        <section>
            <?php if (!empty($room)): ?>
                <h2><?php echo htmlspecialchars($room['name']); ?></h2>
                <p><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']); ?></p>
                <p><strong>Equipment:</strong> <?php echo htmlspecialchars($room['equipment']); ?></p>
                <p><strong>End Date:</strong> <?php echo htmlspecialchars($room['end_date']); ?></p>
                <p><strong>Duration:</strong> <?php echo htmlspecialchars($room['duration']); ?></p>
                <p><strong>Created At:</strong> <?php echo htmlspecialchars($room['created_at']); ?></p>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($room['date']); ?></p>

                <h3>Available Timeslots</h3>
                <?php if (!empty($timeslots)): ?>
                    <ul>
                        <?php foreach ($timeslots as $timeslot): ?>
                            <li><?php echo htmlspecialchars($timeslot['start_time'] . ' - ' . $timeslot['end_time']); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No available timeslots for this room.</p>
                <?php endif; ?>
            <?php elseif (!empty($error)): ?>
                <p><?php echo $error; ?></p>
            <?php else: ?>
                <p>Room not found.</p>
            <?php endif; ?>
        </section>
    </main>
</div>

<script>
    // JavaScript for toggle functionality
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
        document.querySelector('.main-content').classList.toggle('shifted');
    }
</script>

</body>
</html>
