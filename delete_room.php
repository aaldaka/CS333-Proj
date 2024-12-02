<?php
require 'config/db_config.php';

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Check if data is valid JSON
if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
    exit;
}

// Extract the room ID
$roomId = $data['room_id'];

// Try to delete the room from the database
try {
    $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
    $stmt->execute([$roomId]);

    // Return success response
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    // Return an error message if there's an issue with the database query
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
