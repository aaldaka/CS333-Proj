<?php
require 'config/db_config.php';

// Fetch rooms
$stmt = $pdo->prepare("SELECT * FROM rooms");
$stmt->execute();
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch schedules
$stmt = $pdo->prepare("SELECT rs.*, r.name AS room_name FROM room_schedules rs JOIN rooms r ON rs.room_id = r.room_id");
$stmt->execute();
$schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch upcoming bookings
$stmt = $pdo->prepare("SELECT b.*, u.name AS user_name, r.name AS room_name FROM bookings b 
                        JOIN users u ON b.user_id = u.user_id 
                        JOIN rooms r ON b.room_id = r.room_id 
                        WHERE b.start_time >= NOW() 
                        ORDER BY b.start_time ASC");
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <script defer src="scripts.js"></script>
</head>
<body>
<header>
    <h1>Admin Dashboard</h1>
    <nav>
        <ul id="main-nav">
            <li><a href="#manage-rooms">Manage Rooms</a></li>
            <li><a href="#manage-schedules">Manage Schedules</a></li>
            <li><a href="#admin-logs">Admin Logs</a></li>
        </ul>
    </nav>
</header>

<main>
<!-- Room Management -->
<section id="manage-rooms">
    <h2>Manage Rooms</h2>
    <table>
        <thead>
            <tr>
                <th>Room Name</th>
                <th>Capacity</th>
                <th>Equipment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $room): ?>
                <tr id="room-row-<?= $room['room_id'] ?>">
                    <td class="room-name"><?= htmlspecialchars($room['name']) ?></td>
                    <td class="room-capacity"><?= htmlspecialchars($room['capacity']) ?></td>
                    <td class="room-equipment"><?= htmlspecialchars($room['equipment']) ?></td>
                    <td>
                        <button class="edit-btn" onclick="editRoom(<?= $room['room_id'] ?>)">Edit</button>
                        <button onclick="deleteRoom(<?= $room['room_id'] ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button onclick="addRoom()">Add Room</button>
</section>

    <!-- Schedule Management Section -->
<section id="manage-schedules">
    <h2>Manage Room Schedules</h2>
    <table>
        <thead>
            <tr>
                <th>Room Name</th>
                <th>Day</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($schedules as $schedule): ?>
                <tr>
                    <td><?= htmlspecialchars($schedule['room_name']) ?></td>
                    <td><?= htmlspecialchars($schedule['day_of_week']) ?></td>
                    <td><?= htmlspecialchars($schedule['start_time']) ?></td>
                    <td><?= htmlspecialchars($schedule['end_time']) ?></td>
                    <td>
                        <button onclick="editScheduleRow(this.closest('tr'), <?= $schedule['schedule_id'] ?>)">Edit</button>
                        <button onclick="deleteSchedule(<?= $schedule['schedule_id'] ?>)">Delete</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <button onclick="addScheduleRow()">Add Schedule</button>
</section>

    <!-- Upcoming Bookings -->
    <section id="admin-logs">
        <h2>Upcoming Bookings</h2>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>User</th>
                    <th>Room</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $booking): ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['booking_id']) ?></td>
                        <td><?= htmlspecialchars($booking['user_name']) ?></td>
                        <td><?= htmlspecialchars($booking['room_name']) ?></td>
                        <td><?= htmlspecialchars($booking['start_time']) ?></td>
                        <td><?= htmlspecialchars($booking['end_time']) ?></td>
                        <td><?= htmlspecialchars($booking['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
