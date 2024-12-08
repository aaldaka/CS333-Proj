<?php
require '../config/db_config.php';
session_start();  // Start the session at the top

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Access denied: Admin privileges required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['schedule_id']) || empty($data['start_time']) || empty($data['end_time'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Check if the schedule exists before updating
    $stmtCheck = $pdo->prepare("SELECT start_time, end_time FROM room_schedules WHERE schedule_id = ?");
    $stmtCheck->execute([$data['schedule_id']]);
    
    if ($stmtCheck->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'No schedule found with that ID.']);
        exit;
    }

    // Fetch the current start and end time for the schedule
    $schedule = $stmtCheck->fetch(PDO::FETCH_ASSOC);
    $currentStartTime = $schedule['start_time'];
    $currentEndTime = $schedule['end_time'];

    // Check if the new start and end times are the same as the current ones
    if ($currentStartTime === $data['start_time'] && $currentEndTime === $data['end_time']) {
        echo json_encode(['success' => false, 'message' => 'No changes detected. The schedule times are the same as before.']);
        exit;
    }

    // Prepare the update statement
    $stmt = $pdo->prepare("UPDATE room_schedules SET start_time = ?, end_time = ? WHERE schedule_id = ?");
    $stmt->execute([$data['start_time'], $data['end_time'], $data['schedule_id']]);

    // Log the action
    $adminId = $_SESSION['user_id'];
    $stmtLog = $pdo->prepare("INSERT INTO admin_actions (admin_id, action_description, created_at) VALUES (?, ?, NOW())");
    $actionDescription = "Modified schedule for schedule ID: " . $data['schedule_id'];
    $stmtLog->execute([$adminId, $actionDescription]);

    $pdo->commit();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to update schedule.']);
}
?>
