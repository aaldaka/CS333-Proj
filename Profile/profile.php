<?php

require '../config/db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../LoginandRegistration/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, user_type , email, profile_picture, gender, major FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Use default picture if none exists
$profilePicture = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.jpg';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <link rel="stylesheet" href="../config/styles.css">
    <link rel="stylesheet" href="profile.css">
</head>

<body>
<div class="sidebar">
    <ul class="sidebar-links">
        <li><a href="../Rooms/home.php">HOME</a></li>
        <li><a href="../Rooms/rooms.php">ROOMS</a></li>
        <li><a href="../bookingSystem/bookings.php">BOOKINGS</a></li>
        <li><a href="../ReportingSystem/ReportingAndAnalytics.php">REPORTING</a></li>
        <!-- Only display Admin link if user_type is admin -->
        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
            <li><a href="../AdminFuncs/admin.php">ADMIN</a></li>
        <?php endif; ?>
        <li><a href="../LoginandRegistration/login.php">LOGOUT</a></li>
    </ul>
</div>

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Toggle Button -->
        <button class="toggle-btn" onclick="toggleSidebar()">&#9776;</button>
    </div>
    <main class="container">
        <h1>Your Profile</h1>
        <img src="../uploads/<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture"
            style="width: 180px; height: 180px;">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Privileges:</strong> <?php echo htmlspecialchars($user['user_type']); ?></p>
        <p><strong>Gender:</strong> <?php echo htmlspecialchars($user['gender'] ?? '________'); ?></p>
        <p><strong>Major:</strong> <?php echo htmlspecialchars($user['major'] ?? '________'); ?></p>
        <a href="edit_profile.php" role="button" class="secondary">Edit Profile</a>
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
