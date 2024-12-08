<?php
require '../config/db_config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied: Admin privileges required.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['room_id']) || empty($data['day_of_week']) || empty($data['start_time']) || empty($data['end_time'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM room_schedules WHERE room_id = ? AND day_of_week = ? AND start_time = ? AND end_time = ?");
    $stmtCheck->execute([$data['room_id'], $data['day_of_week'], $data['start_time'], $data['end_time']]);
    $scheduleCount = $stmtCheck->fetchColumn();
 
    // Schedule already exists
    if ($scheduleCount > 0) {
        echo json_encode(['success' => false, 'message' => 'Schedule already exists for this room on the selected day and time.']);
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO room_schedules (room_id, day_of_week, start_time, end_time) VALUES (?, ?, ?, ?)");
    $stmt->execute([$data['room_id'], $data['day_of_week'], $data['start_time'], $data['end_time']]);

    // Log the action
    $adminId = $_SESSION['user_id'];
    $stmtLog = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_description, created_at) VALUES (?, ?, NOW())");
    $stmtLog->execute([$adminId, "Added schedule for room " . $data['room_id'] . " on " . $data['day_of_week'] . " from " . $data['start_time'] . " to " . $data['end_time']]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to add schedule: ' . $e->getMessage()]);
}
?>
