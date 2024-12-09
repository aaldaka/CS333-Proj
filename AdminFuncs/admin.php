<?php
require '../config/db_config.php';
// Start the session
session_start();

// Ensure the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo "Access denied: Admin privileges required.";
    exit;
}

// Fetch rooms
$stmt = $pdo->prepare("SELECT * FROM rooms");
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch schedules with user and room information
$stmt = $pdo->prepare("
    SELECT 
        rs.schedule_id, 
        r.name AS room_name, 
        rs.day_of_week, 
        rs.start_time, 
        rs.end_time, 
        u.name AS user_name
    FROM room_schedules rs
    JOIN rooms r ON rs.room_id = r.room_id
    LEFT JOIN bookings b ON rs.schedule_id = b.booking_id
    LEFT JOIN users u ON b.user_id = u.user_id
");
$stmt->execute();
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch admin logs
$stmt = $pdo->prepare("SELECT al.*, u.name AS admin_name FROM admin_actions al 
                        JOIN users u ON al.admin_id = u.user_id 
                        ORDER BY al.created_at DESC");
$stmt->execute();
$adminLogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script defer src="scripts.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="custom.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>

<div class="sidebar">
    <ul class="sidebar-links">
        <li><a href="../Rooms/home.php"><i class='bx bxs-home-smile'></i>HOME</a></li>
        <li><a href="../Rooms/rooms.php">ROOMS</a></li>
        <li><a href="../bookingSystem/bookings.php">BOOKINGS</a></li>
        <li><a href="../Profile/profile.php">PROFILE</a></li>
        <li><a href="../LoginandRegister/login.php">LOGOUT</a></li>
        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin'): ?>
            <li><a href="admin.php">ADMIN</a></li>
        <?php endif; ?>
    </ul>
</div>


<div class="main-content" id="mainContent">
    <button class="toggle-btn" onclick="toggleSidebar()">&#9776;</button>
</div>

<section class="hero is-small is-primary">
    <div class="hero-body">
        <p class="title">Admin Dashboard</p>
        <nav class="breadcrumb is-medium" aria-label="breadcrumbs">
            <ul>
                <li><a href="#manage-rooms"><i class="fas fa-home"></i> Rooms</a></li>
                <li><a href="#manage-schedules"><i class="fas fa-calendar"></i> Schedule</a></li>
                <li><a href="#admin-logs"><i class="fas fa-clipboard-list"></i> Logs</a></li>
            </ul>
        </nav>
    </div>
</section>

<main class="container">
    <!-- Manage Rooms -->
    <section id="manage-rooms" class="section">
        <h2 class="title">Manage Rooms</h2>
        <div class="table-container">
            <table class="table is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>Room No.</th>
                        <th>Capacity</th>
                        <th>Equipment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $room): ?>
                        <tr id="room-row-<?= $room['room_id'] ?>">
                            <td class="room-name"><?= htmlspecialchars($room['name']) ?></td>
                            <td class="room-capacity"><?= htmlspecialchars($room['capacity']) ?></td>
                            <td class="room-equipment"><?= htmlspecialchars($room['equipment']) ?></td>
                            <td>
                                <button class="button is-link is-outlined is-small" onclick="editRoom(<?= $room['room_id'] ?>)">Edit</button>
                                <button class="button is-danger is-outlined is-small" onclick="deleteRoom(<?= $room['room_id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <button class="button is-primary" onclick="addRoom()">Add Room</button>
    </section>

    <!-- Manage Room Schedules -->
    <section id="manage-schedules" class="section">  
        <h2 class="title">Manage Schedules</h2>
        <div class="box">
            <h3 class="subtitle">Add New Schedule</h3>
            <form method="POST" action="">
                <div class="field">
                    <label class="label" for="room_id">Room</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="room_id" required>
                                <option value="">Select a room</option>
                                <?php foreach ($rooms as $room): ?>
                                    <option value="<?= $room['room_id'] ?>"><?= htmlspecialchars($room['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="day_of_week">Day of the Week</label>
                    <div class="control">
                        <div class="select is-fullwidth">
                            <select name="day_of_week" required>
                                <option value="">Select a day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="start_time">Start Time</label>
                    <div class="control">
                        <input class="input" type="time" name="start_time" required>
                    </div>
                </div>

                <div class="field">
                    <label class="label" for="end_time">End Time</label>
                    <div class="control">
                        <input class="input" type="time" name="end_time" required>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <button class="button is-primary" type="button" onclick="addSchedule()">Add Schedule</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Room Schedules Table -->
        <div class="table-container">
            <table class="table is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>Room No.</th>
                        <th>Day</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr data-schedule-id="<?= $schedule['schedule_id'] ?>">
                            <td><?= htmlspecialchars($schedule['room_name']) ?></td>
                            <td><?= htmlspecialchars($schedule['day_of_week']) ?></td>
                            <td>
                                <input type="time" class="input is-small" value="<?= htmlspecialchars($schedule['start_time']) ?>" data-original-value="<?= htmlspecialchars($schedule['start_time']) ?>">
                            </td>
                            <td>
                                <input type="time" class="input is-small" value="<?= htmlspecialchars($schedule['end_time']) ?>" data-original-value="<?= htmlspecialchars($schedule['end_time']) ?>">
                            </td>
                            <td>
                                <button class="button is-info is-small" onclick="updateSchedule(<?= $schedule['schedule_id'] ?>)">Update</button>
                                <button class="button is-danger is-small" onclick="deleteSchedule(<?= $schedule['schedule_id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- Admin Action Logs -->
    <section id="admin-logs" class="section">
        <h2 class="title">Admin Logs</h2>
        <div class="table-container">
            <table class="table is-striped is-hoverable is-fullwidth">
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Action</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($adminLogs as $log): ?>
                        <tr>
                            <td><?= htmlspecialchars($log['admin_name']) ?></td>
                            <td><?= htmlspecialchars($log['action_description']) ?></td>
                            <td><?= htmlspecialchars($log['created_at']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <script>
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('open');
        document.querySelector('.main-content').classList.toggle('shifted');
    }
</script>


</main>

</body>
</html>
