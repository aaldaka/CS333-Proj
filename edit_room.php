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

// Extract the room details
$roomId = $data['room_id'];
$name = $data['name'];
$capacity = $data['capacity'];
$equipment = $data['equipment'] ?? null;

// Validate input
if (empty($name) || empty($capacity)) {
    echo json_encode(['success' => false, 'message' => 'Room name and capacity are required']);
    exit;
}

// Try to update the room in the database
try {
    $stmt = $pdo->prepare("UPDATE rooms SET name = ?, capacity = ?, equipment = ? WHERE room_id = ?");
    $stmt->execute([$name, $capacity, $equipment, $roomId]);

    // Log the admin action
    $stmtLog = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_description, created_at) VALUES (?, ?, NOW())");
    $stmtLog->execute([$adminId, "Edited room with ID: $roomId"]);

    // Return success response
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Return an error message if there's an issue with the database query
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
