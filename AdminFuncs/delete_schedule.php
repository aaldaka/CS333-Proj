<?php
require '../config/db_config.php';
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['schedule_id'])) {
    $stmt = $pdo->prepare("DELETE FROM room_schedules WHERE schedule_id = ?");
    $result = $stmt->execute([$data['schedule_id']]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete schedule.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
}
?>
