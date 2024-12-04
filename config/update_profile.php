<?php 
require 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$name = $_POST['name'] ?? '';
$gender = $_POST['gender'] ?? null;
$major = $_POST['major'] ?? null;
$profilePicture = $_FILES['profile_picture'] ?? null;

// Validate name
if (empty($name)) {
    die("Name cannot be empty.");
}

// Fetch current profile picture from the database
$stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$currentProfilePicture = $stmt->fetchColumn();

// Default to the current picture if no new picture is uploaded
$profilePicPath = $currentProfilePicture ?: 'default.jpg';

// Handle profile picture upload
if ($profilePicture && $profilePicture['error'] === UPLOAD_ERR_OK) {
    $uploadDir = __DIR__ . '/../uploads/';
    $fileName = uniqid() . "-" . basename($profilePicture['name']);
    $targetFile = $uploadDir . $fileName;

    $allowedTypes = ['jpg', 'jpeg', 'png'];
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if (!in_array($fileType, $allowedTypes)) {
        die("Invalid file type. Only JPG, JPEG, and PNG are allowed.");
    }

    // Ensure the upload directory exists
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Move the uploaded file
    if (move_uploaded_file($profilePicture['tmp_name'], $targetFile)) {
        $profilePicPath = $fileName;

        // Optional: delete the old profile picture to save storage
        if ($currentProfilePicture && $currentProfilePicture !== 'default.jpg') {
            @unlink($uploadDir . $currentProfilePicture);
        }
    } else {
        die("Failed to upload profile picture.");
    }
}

// Update database
$query = "UPDATE users
SET name = :name, profile_picture = :profile_picture, gender = :gender, major = :major
WHERE user_id = :user_id";
$params = [
    'name' => $name,
    'profile_picture' => $profilePicPath,
    'gender' => $gender,
    'major' => $major,
    'user_id' => $userId
];

$stmt = $pdo->prepare($query);
$stmt->execute($params);

header("Location: profile.php");
exit();
