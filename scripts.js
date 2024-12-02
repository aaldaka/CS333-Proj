// Add a new room
function addRoom() {
    alert("Button Clicked")
    const roomName = prompt("Enter the room name:");
    const capacity = prompt("Enter the room capacity:");
    const equipment = prompt("Enter the equipment details (comma-separated):");

    if (roomName && capacity) {
        fetch('add_room.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: roomName,
                capacity: capacity,
                equipment: equipment
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Room added successfully!");
                    location.reload();
                } else {
                    alert("Failed to add room: " + data.message);
                }
            });
    }
}

// Edit an existing room
function editRoom(roomId) {
    const newName = prompt("Enter the new room name:");
    const newCapacity = prompt("Enter the new capacity:");
    const newEquipment = prompt("Enter the updated equipment details (comma-separated):");

    if (newName && newCapacity) {
        fetch('edit_room.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                room_id: roomId,
                name: newName,
                capacity: newCapacity,
                equipment: newEquipment
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Room updated successfully!");
                    location.reload();
                } else {
                    alert("Failed to update room: " + data.message);
                }
            });
    }
}

// Delete a room
function deleteRoom(roomId) {
    if (confirm("Are you sure you want to delete this room?")) {
        fetch('delete_room.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ room_id: roomId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Room deleted successfully!");
                    location.reload();
                } else {
                    alert("Failed to delete room: " + data.message);
                }
            });
    }
}

// Add a new schedule
function addSchedule() {
    const roomId = prompt("Enter the Room ID for the schedule:");
    const dayOfWeek = prompt("Enter the day of the week:");
    const startTime = prompt("Enter the start time (HH:MM):");
    const endTime = prompt("Enter the end time (HH:MM):");

    if (roomId && dayOfWeek && startTime && endTime) {
        fetch('add_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                room_id: roomId,
                day_of_week: dayOfWeek,
                start_time: startTime,
                end_time: endTime
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Schedule added successfully!");
                    location.reload();
                } else {
                    alert("Failed to add schedule: " + data.message);
                }
            });
    }
}

// Edit an existing schedule
function editSchedule(scheduleId) {
    const newDay = prompt("Enter the new day of the week:");
    const newStartTime = prompt("Enter the new start time (HH:MM):");
    const newEndTime = prompt("Enter the new end time (HH:MM):");

    if (newDay && newStartTime && newEndTime) {
        fetch('edit_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                schedule_id: scheduleId,
                day_of_week: newDay,
                start_time: newStartTime,
                end_time: newEndTime
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Schedule updated successfully!");
                    location.reload();
                } else {
                    alert("Failed to update schedule: " + data.message);
                }
            });
    }
}

// Delete a schedule
function deleteSchedule(scheduleId) {
    if (confirm("Are you sure you want to delete this schedule?")) {
        fetch('delete_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ schedule_id: scheduleId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Schedule deleted successfully!");
                    location.reload();
                } else {
                    alert("Failed to delete schedule: " + data.message);
                }
            });
    }
}
