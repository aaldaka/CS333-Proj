<?php
// Delete a schedule for a room
$data = json_decode(file_get_contents("php://input"));
$schedule_id = $data->schedule_id;

$query = "DELETE FROM room_schedules WHERE schedule_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $schedule_id);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to delete schedule']);
}
?>
