<?php
session_start();
require '../config/db_config.php';

$error_message = '';
$stored_email = '';
$stored_password = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user data from the database
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_type'] = $user['user_type'];
        if (isset($_POST['remember'])) {
            // Set cookies for email and password if "Remember Me" is checked
            setcookie('email', $email, time() + (30 * 24 * 60 * 60)); // Expires in 30 days
            setcookie('password', $password, time() + (30 * 24 * 60 * 60)); // Expires in 30 days
        } else {
            // Unset the cookies if "Remember Me" is not checked
            setcookie('email', '', time() - 3600); // Expire the email cookie
            setcookie('password', '', time() - 3600); // Expire the password cookie
        }

        header("Location: ../Rooms/home.php"); // Redirect to the home page
        exit;
    } else {
        $error_message = "Invalid email or password.";
    }
}

$stored_email = isset($_COOKIE['email']) ? $_COOKIE['email'] : '';
$stored_password = isset($_COOKIE['password']) ? $_COOKIE['password'] : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking System - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="LoginStyles.css">

</head>
<body>
    <div class="left-half">
        <div class="form-container">
            <h1 style="color:black;">Welcome</h1>
            <p style="color:gray;">Please enter your details</p>
            <?php if ($error_message): ?>
                <div class="alert"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <form action="" method="post">
                <label for="email" style="color:black;">Email address</label>
                <input type="email" id="email" name="email" placeholder="xxxxxxxx@stu.uob.edu.bh" value="<?php echo $stored_email; ?>" required>

                <label for="password" style="color:black;">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" value="<?php echo $stored_password; ?>" required>
                <div class="remember-forgot">
                    <label class="small-text"  style="color: rgb(59,30,84);">
                        <input type="checkbox" id="remember" name="remember"> Remember ME
                    </label>
                </div>
                <button type="submit" style="background-color: rgb(59,30,84);">Sign in</button>
            </form>
            <p style="color:gray;">Don't have an account? <a href="registration.php" class="link">Sign up</a></p>
        </div>
    </div>
    <div class="right-half"></div>
</body>
</html>
