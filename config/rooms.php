<?php
// Include database configuration
include('db_config.php');

try {
    // Fetch all rooms from the database
    $query = "SELECT * FROM rooms";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $rooms = [];
    $error = "Error fetching rooms: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Browsing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <!-- Link to the external styles.css file --> 
    <link rel="stylesheet" href="styles2.css">
    
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <ul class="sidebar-links">
        <li><a href="home.php">HOME</a></li>
        <li><a href="bookings.php">BOOKINGS</a></li>
        <li><a href="profile.php">PROFILE</a></li>
        <li><a href="login.php">LOGOUT</a></li>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">&#9776;</button>

    <!-- Centered Heading and Content Box -->
    <div class="content-wrapper">
        <header>
            <h1>Available Rooms</h1>
        </header>

        <div class="content-box">
            <div class="container">
                <?php if (!empty($rooms)): ?>
                    <div class="room-list">
                        <?php foreach ($rooms as $room): ?>
                            <div class="room-box">
                                <a href="room_details.php?room_id=<?php echo $room['room_id']; ?>" class="room-link">
                                    <h2><?php echo htmlspecialchars($room['name']); ?></h2>
                                    <p><strong>Capacity:</strong> <?php echo htmlspecialchars($room['capacity']); ?></p>
                                    <p><strong>Equipment:</strong> <?php echo htmlspecialchars($room['equipment']); ?></p>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>No rooms are available at the moment.</p>
                    <?php if (!empty($error)) echo "<p>$error</p>"; ?>
                <?php endif; ?>
            </div>
        </div>
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
