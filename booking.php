<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Booking</title>
    <link rel="stylesheet" href="https://unpkg.com/@picocss/pico@latest/css/pico.min.css">
</head>
<body>
    <main class="container">
        <h1>IT College Booking Rooms Service</h1>
        <form action="book_room.php" method="POST">
            <label for="room_id">Select Room</label>
            <select name="room_id" id="room_id" required>
                <?php
                // Database connection
                include 'config/db_config.php';  // Make sure this file sets up the $pdo connection
                try {
                    // Fetch all room names and IDs from the rooms table
                    $stmt = $pdo->query("SELECT id, name FROM rooms ORDER BY name ASC");
                    $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Loop through each room and create an option tag
                    foreach ($rooms as $room) {
                        echo "<option value=\"{$room['id']}\">" . htmlspecialchars($room['name']) . "</option>";
                    }
                } catch (PDOException $e) {
                    echo "<option disabled>Error loading rooms</option>";
                }
                ?>
            
            <label for="date">Date</label>
            <input type="date" name="date" id="date" required>

            <?php
                // Default duration to 50 minutes if it's not posted yet
                $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 50;

                // Your existing slot generation code would follow here...
                ?>
            <label for="duration">Choose Duration</label>
            <select name="duration" id="duration" onchange="this.form.submit()">
                <option value="50" <?php if ($duration == 50) echo 'selected'; ?>>50 minutes</option>
                <option value="75" <?php if ($duration == 75) echo 'selected'; ?>>75 minutes</option>
                <option value="100" <?php if ($duration == 100) echo 'selected'; ?>>100 minutes</option>
            </select>

            <label for="start_time">Start Time</label>
            <select name="start_time" id="start_time" required>
            <?php
                $startHour = 8; // Start at 8:00 AM
                $endHour = 20; // End at 8:00 PM
                
                // Get the selected duration (50, 75, or 100 minutes)
                $duration = isset($_POST['duration']) ? (int)$_POST['duration'] : 50;
                
                // Define interval in minutes
                $intervalMinutes = 0;
                
                if ($duration == 50) {
                    $intervalMinutes = 60; // Treat 50 minutes as 1-hour intervals
                } elseif ($duration == 75) {
                    $intervalMinutes = 90; // Treat 75 minutes as 1.5-hour intervals
                } elseif ($duration == 100) {
                    $intervalMinutes = 120; // Treat 100 minutes as 2-hour intervals
                }
                
                // Generate time slots dynamically
                $currentHour = $startHour;
                $currentMinute = 0;
                
                while ($currentHour < $endHour) {
                    // Calculate start time
                    $startTime = sprintf('%02d:%02d', $currentHour, $currentMinute);
                
                    // Calculate end time based on interval
                    $endTime = date('H:i', strtotime("+{$duration} minutes", strtotime($startTime)));
                
                    // Convert to 12-hour format
                    $startTimeDisplay = date('g:i A', strtotime($startTime));
                    $endTimeDisplay = date('g:i A', strtotime($endTime));
                
                    // Generate display string
                    $totalTimeDisplay = "$startTimeDisplay - $endTimeDisplay";
                
                    // Output the option tag
                    echo "<option value=\"$startTime\">$totalTimeDisplay</option>";
                
                    // Increment time by the interval
                    $currentHour += intdiv($intervalMinutes, 60); // Add hours
                    $currentMinute += $intervalMinutes % 60; // Add remaining minutes
                
                    // Handle overflow of minutes
                    if ($currentMinute >= 60) {
                        $currentMinute -= 60;
                        $currentHour++;
                    }
                }
                
                ?>
            </select>

            <p id="errorMessage" style="color:red;"></p>
            
            <button type="submit">Book Room</button>
        </form>
    </main>
</body>
</html>

<script>
    document.querySelector('form').addEventListener('submit', function (e) {
        e.preventDefault(); // Prevent the default form submission

        const formData = new FormData(this);

        fetch('book_room.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'error') {
                alert(data.message); // Show error as an alert
            } else if (data.status === 'success') {
                alert(data.message); // Show success message as an alert
                // Optionally, you can redirect the user or update the page
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An unexpected error occurred.');
        });
    });
</script>

