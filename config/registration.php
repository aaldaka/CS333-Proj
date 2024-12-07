<?php
session_start();
require 'db_config.php'; // Include your database configuration

$error_messages = [];
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate form input
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_messages[] = "All fields are required!";
    } 
    if (!preg_match("/^(\\d{9}@stu\\.uob\\.edu\\.bh|[a-zA-Z0-9._%+-]+@uob\\.edu\\.bh)$/", $email)) {
        $error_messages[] = "Please use a UOB email.";
    }
    if ($password !== $confirm_password) {
        $error_messages[] = "Passwords do not match.";
    }

    if (empty($error_messages)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            // Check if the email already exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->rowCount() > 0) {
                $error_messages[] = "Email is already registered!";
            } else {
                // Prepare and execute the insert statement
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$name, $email, $hashed_password]);
                $success_message = "Registration successful! You can now log in.";
                // Redirect to login page after successful registration
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $error_messages[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        body {
            background-color: rgb(238, 238, 238);
        }

        .card {
        background-color: rgb(212, 190, 228);
        border: 1px solid rgb(155, 126, 189);
        border-radius: 10px;
        padding: 20px;
        max-width: 500px; 
        margin: 0 auto; 
        }

        .alert {
            color: red;
        }
        .success {
            color: green;
        }

        form {
        width: 100%; 
        max-width: 400px; 
        margin: 0 auto; 
        padding: 20px; 
        }

        input {
        width: 100%; 
        padding: 10px; 
        font-size: 16px; 
        border-radius: 5px;
        border: 1px solid rgb(155, 126, 189);
        }

        h2{
            text-align: center;
            color: black;
            font-style: italic;
        }

        label{
            color: black;
            font-weight: bold;
        }
        .inputspc{
            background-color: rgb(59,30,84);
        }

    </style>
</head>
<body>
    <main class="container">
        <div class="grid">
            <div class="col-4">
                <div class="card">
                    <h2>User  Registration</h2>

                    <?php foreach ($error_messages as $error): ?>
                        <div class="alert"><?php echo $error; ?></div>
                    <?php endforeach; ?>

                    <?php if ($success_message): ?>
                        <div class="success"><?php echo $success_message; ?></div>
                    <?php endif; ?>

                    <form method="post" action="">
                        <label for="name">Name:</label>
                        <input type="text" name="name" required><br>

                        <label for="email">Email:</label>
                        <input type="email" name="email" required><br>

                        <label for="password">Password:</label>
                        <input type="password" name="password" required><br>

                        <label for="confirm_password">Confirm Password:</label>
                        <input type="password" name="confirm_password" required><br>

                        <input class = "inputspc" type="submit" value="Register">
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>