<?php

// Start session to access session variables
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

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
    <!-- the css pico style  -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <!-- Link to the external styles.css file -->
    <link rel="stylesheet" href="styles.css">
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
        <!-- Only display Admin link if user_type is admin -->
        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
            <li><a href="admin.php">ADMIN</a></li>
        <?php endif; ?>
    </ul>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Toggle Button -->
    <button class="toggle-btn" onclick="toggleSidebar()">&#9776;</button>

    <!-- Centered Heading and Content Box -->
    <div class="content-wrapper">
        <header>
            <h1>Welcome to the Room Booking System</h1>
        </header>

        <div class="content-box">
            <div class="container">
                <?php if (!empty($rooms)): ?>
                    <div class="room-list">
                        <?php foreach ($rooms as $room): ?>
                            <div class="room">
                                <!-- Wrap each room in an anchor tag to link to the room details page -->
                                <a href="room_details.php?room_id=<?php echo $room['room_id']; ?>" class="room-box">
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
        document.querySelector('.main-content').classList.toggle('shifted');
    }
</script>

</body>
</html>