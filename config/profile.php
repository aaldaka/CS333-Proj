<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, email, profile_picture, gender, major FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// Use default picture if none exists
$profilePicture = !empty($user['profile_picture']) ? $user['profile_picture'] : 'default.jpg';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@1.5.7/css/pico.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --background-color: rgb(85, 45, 123); /* Deep Purple */
            --primary-color: rgb(112, 66, 153);   /* Muted Purple */
            --secondary-color: rgb(179, 132, 205);/* Soft Lavender */
            --accent-color: rgb(218, 182, 255);   /* Light Lavender */
            --card-color: rgb(242, 232, 255);     /* Very Light Purple */
        }

        body {
            font-family: 'Roboto', sans-serif;
            background: var(--background-color);
            color: white;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        main.container {
            background: var(--card-color);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            padding: 40px;
            max-width: 500px;
            text-align: center;
            position: relative;
            border: 3px solid var(--secondary-color);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 2.4rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        img {
            border-radius: 50%;
            margin-bottom: 20px;
            border: 5px solid var(--secondary-color);
        }

        p {
            margin: 15px 0;
            font-size: 1.1rem;
            color: var(--primary-color);
        }

        a.secondary {
            background-color: var(--primary-color);
            color: var(--card-color);
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            transition: background-color 0.3s, transform 0.3s;
            display: inline-block;
            margin-top: 20px;
        }

        a.secondary:hover {
            background-color: var(--accent-color);
            transform: scale(1.05);
        }
    </style>
</head>

<body>
    <main class="container">
        <h1>Your Profile</h1>
        <img src="../uploads/<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile Picture" style="width: 180px; height: 180px;">
        <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Gender:</strong> <?php echo htmlspecialchars($user['gender'] ?? '________'); ?></p>
        <p><strong>Major:</strong> <?php echo htmlspecialchars($user['major'] ?? '________'); ?></p>
        <a href="edit_profile.php" role="button" class="secondary">Edit Profile</a>
    </main>
</body>

</html>
