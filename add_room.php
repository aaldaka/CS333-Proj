<?php
//Executes the requested action in the
require 'config/db_config.php';

// Get the raw POST data
$data = json_decode(file_get_contents('php://input'), true);

// Extract the room details
$name = $data['name'];
$capacity = $data['capacity'];
$equipment = $data['equipment'] ?? null;

// Insert the new room into the database
$stmt = $pdo->prepare("INSERT INTO rooms (name, capacity, equipment) VALUES (?, ?, ?)");
$stmt->execute([$name, $capacity, $equipment]);

// Return a response
echo json_encode(['success' => true]);
?>
