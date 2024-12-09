<?php
require '../config/db_config.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied: Admin privileges required']);
    exit;
}

// Get the admin ID from the session
$adminId = $_SESSION['user_id'];

// Get the room ID to be deleted from the POST request
$data = json_decode(file_get_contents("php://input"));
$roomId = $data->room_id;

// Check if the room ID exists in the database
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM rooms WHERE room_id = ?");
$stmtCheck->execute([$roomId]);
$roomExists = $stmtCheck->fetchColumn();

if ($roomExists == 0) {
    echo json_encode(['success' => false, 'message' => 'Room not found']);
    exit;
}

// Delete related bookings first
$stmtDeleteBookings = $pdo->prepare("DELETE FROM bookings WHERE room_id = ?");
$stmtDeleteBookings->execute([$roomId]);

// Delete related schedules
$stmtDeleteSchedules = $pdo->prepare("DELETE FROM room_schedules WHERE room_id = ?");
$stmtDeleteSchedules->execute([$roomId]);

// Delete the room from the database
$stmtDeleteRoom = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
$stmtDeleteRoom->execute([$roomId]);

// Log the admin action
$stmtLog = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_description, created_at) VALUES (?, ?, NOW())");
$stmtLog->execute([$adminId, "Deleted room with ID: $roomId and its associated bookings"]);

// Return success response
echo json_encode(['success' => true, 'message' => 'Room and related bookings deleted successfully']);
?>
