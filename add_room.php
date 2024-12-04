<?php
// Executes the requested action in the database
require 'config/db_config.php';

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

// Insert the new room into the database
$stmt = $pdo->prepare("INSERT INTO rooms (name, capacity, equipment) VALUES (?, ?, ?)");
$stmt->execute([$name, $capacity, $equipment]);


// Return a response
echo json_encode(['success' => true]);
?>
