<?php
session_start();
require '../config/db_config.php'; // Include your database configuration

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
    else {
        $user_type = strpos($_POST['email'], '@stu') !== false ? 'user' : 'admin';
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
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, user_type) VALUES (?, ?, ?, ?)");
                $stmt->execute([$_POST['name'], $_POST['email'], $hashed_password, $user_type]);
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
    <title>Room Booking System - Registration</title>
    <link rel="stylesheet" href="RegistrationStyles.css">
</head>
<body>
    <div class="form-container">
        <h1>Create Account</h1>
        <p>Join our room booking platform</p>
        
        <?php if (!empty($error_messages)): ?>
            <?php foreach ($error_messages as $error): ?>
                <div class="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="input-group">
                <label for="name">Full Name</label>
                <input type="text" 
                       id="name" 
                       name="name" 
                       placeholder="Enter your full name"
                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                       required>
            </div>

            <div class="input-group">
                <label for="email">Email address</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       placeholder="xxxxxxxx@stu.uob.edu.bh"
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Enter your password"
                       required>
            </div>

            <div class="input-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" 
                       id="confirm_password" 
                       name="confirm_password" 
                       placeholder="Confirm your password"
                       required>
            </div>

            <button type="submit">Create Account</button>
        </form>
    </div>
</body>
</html>
