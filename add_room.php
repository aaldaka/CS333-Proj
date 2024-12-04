<?php
// Executes the requested action in the database
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

// Extract the room details
$name = $data['name'];
$capacity = $data['capacity'];
$equipment = isset($data['equipment']) ? implode(', ', $data['equipment']) : null; // Convert the equipment array to a string

// Validate input (make sure name and capacity are provided)
if (empty($name) || empty($capacity)) {
    echo json_encode(['success' => false, 'message' => 'Room name and capacity are required']);
    exit;
}

try {
    // Insert the new room into the database
    $stmt = $pdo->prepare("INSERT INTO rooms (name, capacity, equipment) VALUES (?, ?, ?)");
    $stmt->execute([$name, $capacity, $equipment]);

    // Log the admin action
    $stmtLog = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_description, created_at) VALUES (?, ?, NOW())");
    $stmtLog->execute([$adminId, "Added new room: $name"]);

    // Return success response
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Return an error message if there's an issue with the database query
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
