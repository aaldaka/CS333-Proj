<?php
session_start();
require 'db_config.php';

$error_message = '';

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
        header("Location: home.php"); // Redirect to another page
        exit;
    } else {
        $error_message = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Cube Factory - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <style>
        body {
            background-color: rgb(238, 238, 238);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .left-half,
        .right-half {
            width: 50%;
            height: 100%;
            display: inline-block;
        }

        .left-half {
            background-color: rgb(212, 190, 228);
            padding: 20px;
            border: 1px solid rgb(155, 126, 189);
            border-radius: 10px;
        }

        .right-half {
            background-image: url('loginpage.JPG');
            background-size: cover;
            background-position: center;
        }

        .form-container {
            max-width: 300px;
            margin: 0 auto;
        }

        .alert {
            color: red;
        }

        a {
            text-decoration: none;
            color: rgb(59, 30, 84);
        }

        input {
            background-color: white;
            color: black;
        }
        .small-text {
            font-size: 0.8em; /* Smaller font size */
            display: inline; /* Keep them on the same line */
        }

        .remember-forgot {
            display: flex; /* Use flexbox to align items */
            justify-content: space-between; /* Space between items */
            align-items: center; /* Center vertically */
        }
    </style>
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
                <input type="email" id="email" name="email" placeholder="xxxxxxxx@stu.uob.edu.bh" required>

                <label for="password" style="color:black;">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" required>

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