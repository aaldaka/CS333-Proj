<?php
require 'db_config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, profile_picture, gender, major FROM users WHERE user_id = :user_id");
$stmt->execute(['user_id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

// List of majors
$majors = ['Computer Science', 'Information Systems', 'Network Engineering', 'Cybersecurity'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
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
            max-width: 700px; /* Horizontally larger card */
            width: 100%;
            text-align: left;
            position: relative;
            border: 3px solid var(--secondary-color);
        }

        h1 {
            color: var(--primary-color);
            margin-bottom: 20px;
            font-size: 2.4rem;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }

        form label {
            display: block;
            margin-bottom: 15px;
            color: var(--primary-color);
            font-weight: 500;
        }

        form input, form select, form button {
            width: 100%;
            margin-top: 5px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid var(--primary-color);
            font-size: 1rem;
        }

        form button {
            background-color: var(--primary-color);
            color: white;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.3s;
            padding: 12px;
            margin-top: 20px;
        }

        form button:hover {
            background-color: var(--accent-color);
            transform: scale(1.05);
        }

        /* Adjusting the layout of the input fields */
        .input-group {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: space-between;
        }

        .input-group label {
            width: 48%; /* Two items per row with some space */
        }

        .input-group input, .input-group select {
            width: 100%;
        }
    </style>
</head>

<body>
    <main class="container">
        <h1>Edit Profile</h1>
        <form action="update_profile.php" method="POST" enctype="multipart/form-data">
            <div class="input-group">
                <label for="name">
                    Full Name:
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </label>
                <label for="profile_picture">
                    Profile Picture:
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/*">
                </label>
            </div>
            <div class="input-group">
                <label for="gender">
                    Gender:
                    <select id="gender" name="gender">
                        <option value="">Select</option>
                        <option value="Male" <?php echo $user['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $user['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </label>
                <label for="major">
                    Major:
                    <select id="major" name="major">
                        <option value="">Select</option>
                        <?php foreach ($majors as $major): ?>
                            <option value="<?php echo $major; ?>" <?php echo $user['major'] == $major ? 'selected' : ''; ?>>
                                <?php echo $major; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>
            <button type="submit" role="primary">Save Changes</button>
        </form>
    </main>
</body>

</html>