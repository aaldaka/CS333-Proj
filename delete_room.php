<?php
require 'config/db_config.php';

// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied: Admin privileges required']);
    exit;
}

// Get the admin ID from the session
$adminId = $_SESSION['user_id'];

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if data is valid JSON
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Extract the room ID
$roomId = $data['room_id'];

try {
    // Delete the room from the database
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
    $stmt->execute([$roomId]);

    // Log the admin action
    $stmtLog = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_description, created_at) VALUES (?, ?, NOW())");
    $stmtLog->execute([$adminId, "Deleted room with ID: $roomId"]);

    // Return success response
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Return an error message if there's an issue with the database query
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
