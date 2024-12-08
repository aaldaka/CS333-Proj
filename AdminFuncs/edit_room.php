<?php
require '../config/db_config.php';

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

// Extract and validate input
$roomId = $data['room_id'];
$roomNumber = $data['name'];
$capacity = $data['capacity'];
$equipment = isset($data['equipment']) ? implode(', ', $data['equipment']) : null;

if (empty($roomId) || empty($roomNumber) || empty($capacity)) {
    echo json_encode(['success' => false, 'message' => 'Room ID, number, and capacity are required']);
    exit;
}

try {
    // Fetch the current details of the room
    $stmtFetch = $pdo->prepare("SELECT name, capacity, equipment FROM rooms WHERE room_id = ?");
    $stmtFetch->execute([$roomId]);
    $existingRoom = $stmtFetch->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingRoom) {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
        exit;
    }

    // Check if the new data is the same as the existing data
    if (
        $existingRoom['name'] === $roomNumber &&
        $existingRoom['capacity'] == $capacity && // Compare as integer or string
        $existingRoom['equipment'] === $equipment
    ) {
        echo json_encode(['success' => false, 'message' => 'No changes were made']);
        exit;
    }

    // Update the room details in the database
    $stmt = $pdo->prepare("UPDATE rooms SET name = ?, capacity = ?, equipment = ? WHERE room_id = ?");
    $stmt->execute([$roomNumber, $capacity, $equipment, $roomId]);

    // Log the admin action
    $stmtLog = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_description, created_at) VALUES (?, ?, NOW())");
    $stmtLog->execute([$adminId, "Edited room ID: $roomId, Number: $roomNumber, Capacity: $capacity, Equipment: $equipment"]);

    // Return success response
    echo json_encode(['success' => true, 'message' => 'Room updated successfully']);
} catch (Exception $e) {
    // Handle database errors
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
