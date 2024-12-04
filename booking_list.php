<?php
session_start(); // Start the session

// Ensure the user is logged in before accessing this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

// Include necessary files for DB connection and booking logic
require 'config/db_config.php';

// Get the user ID from the session
$user_id = $_SESSION['user_id'];

try {
    // Prepare the SQL query to fetch bookings for the logged-in user
    $query = "SELECT * FROM bookings WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    
    // Bind the user_id to the query
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    
    // Execute the query
    $stmt->execute();
    
    // Fetch the results
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Handle any errors that occur during the query
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bookings</title>
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@latest/css/pico.min.css">
</head>
<body>
    <h2>Your Booking Information</h2>
    <table>
        <thead>
            <tr>
                <th>Room Number</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Duration</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($bookings) {
                foreach ($bookings as $row) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['room_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['end_time']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['duration']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No bookings found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>

<?php
// Close the database connection
$stmt = null;
$pdo = null;
?>
