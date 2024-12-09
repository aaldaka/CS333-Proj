<?php
require '../config/db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../LoginandRegistration/login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, profile_picture, gender, major FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// List of majors
$majors = ['Computer Science', 'Information Systems', 'Network Engineering', 'Cybersecurity'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <link rel="stylesheet" href="../config/styles.css">
    <link rel="stylesheet" href="profile.css">
</head>

<body>
<div class="sidebar">
    <ul class="sidebar-links">
    <li><a href="../Rooms/home.php">HOME</a></li>
        <li><a href="../Rooms/rooms.php">ROOMS</a></li>
        <li><a href="../bookingSystem/booking_list.php">BOOKINGS</a></li>
        <li><a href="../ReportingSystem/ReportingAndAnalytics.php">REPORTING</a></li>
        <li><a href="profile.php">PROFILE</a></li>
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
        <h1>Edit Profile</h1>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="name">
                    Full Name:
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>"
                        required>
                </label>
                <label for="profile_picture">
                    Profile Picture:
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                </label>
            </div>
            <div class="input-group">
                <label for="gender">
                    Gender:
                    <select id="gender" name="gender">
                        <option value="">Select</option>
                        <option value="Male" <?php echo $user['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $user['gender'] == 'Female' ? 'selected' : ''; ?>>Female
                        </option>
                    </select>
                </label>
                <label for="major">
                    Major:
                    <select id="major" name="major">
                        <option value="">Select</option>
                        <?php foreach ($majors as $major): ?>
                            <option value="<?php echo $major; ?>" <?php echo $user['major'] == $major ? 'selected' : ''; ?>>
                                <?php echo $major; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <button type="submit" role="primary">Save Changes</button>
        </form>
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
