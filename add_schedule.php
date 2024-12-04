<?php
// Add schedule details for a room
$data = json_decode(file_get_contents("php://input"));
$room_id = $data->room_id;
$day_of_week = $data->day_of_week;
$start_time = $data->start_time;
$end_time = $data->end_time;

$query = "INSERT INTO room_schedules (room_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("isss", $room_id, $day_of_week, $start_time, $end_time);
if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to add schedule']);
}
?>
