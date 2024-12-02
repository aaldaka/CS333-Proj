let isAddingRoom = false;

// Add or submit room
function addRoom() {
    const tableBody = document.querySelector('#manage-rooms tbody');
    
    // Check if the input row already exists
    const existingRow = tableBody.querySelector('.new-room-row');

    if (isAddingRoom) {
        // If input row exists, submit the room details
        const row = existingRow;
        const name = row.querySelector('.room-name').value;
        const capacity = row.querySelector('.room-capacity').value;
        const equipment = row.querySelector('.room-equipment').value;

        // Validate the data
        if (name && capacity) {
            fetch('add_room.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: name,
                    capacity: capacity,
                    equipment: equipment
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // After successful save, update the table row with the entered data
                    row.innerHTML = `
                        <td>${name}</td>
                        <td>${capacity}</td>
                        <td>${equipment}</td>
                        <td>
                            <button onclick="editRoom(${data.room_id})">Edit</button>
                            <button onclick="deleteRoom(${data.room_id})">Delete</button>
                        </td>
                    `;
                    // Reset the "add" mode
                    isAddingRoom = false;
                    row.classList.remove('new-room-row');
                } else {
                    alert("Failed to add room: " + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while adding the room.');
            });
        } else {
            alert("Room name and capacity are required.");
        }
    } else {
        // If no row exists, create a new row with input fields for adding a room
        const row = document.createElement('tr');
        row.classList.add('new-room-row');
        
        row.innerHTML = `
            <td><input type="text" class="room-name" placeholder="Room Name" /></td>
            <td><input type="text" class="room-capacity" placeholder="Capacity" /></td>
            <td><input type="text" class="room-equipment" placeholder="Equipment" /></td>
            <td>
                <button onclick="addRoom()">Submit</button>
                <button onclick="cancelAddRoom(this)">Cancel</button>
            </td>
        `;
        
        // Append the new row to the table body
        tableBody.appendChild(row);
        
        // Set the flag to indicate we are in adding mode
        isAddingRoom = true;
    }
}

// Cancel adding a new room
function cancelAddRoom(button) {
    const row = button.closest('tr');
    row.remove(); // Remove the row from the table
    isAddingRoom = false; // Reset the adding mode
}

// Edit a room directly in the table
function editRoom(roomId) {
    const row = document.getElementById('room-row-' + roomId);
    const nameCell = row.querySelector('.room-name');
    const capacityCell = row.querySelector('.room-capacity');
    const equipmentCell = row.querySelector('.room-equipment');

    // Make the cells editable
    nameCell.innerHTML = `<input type="text" value="${nameCell.innerText}" />`;
    capacityCell.innerHTML = `<input type="text" value="${capacityCell.innerText}" />`;
    equipmentCell.innerHTML = `<input type="text" value="${equipmentCell.innerText}" />`;

    // Replace Edit button with Save button
    row.querySelector('.edit-btn').innerHTML = 'Save';
    row.querySelector('.edit-btn').setAttribute('onclick', `saveRoom(${roomId})`);
}

// Save edited room details
function saveRoom(roomId) {
    const row = document.getElementById('room-row-' + roomId);
    const name = row.querySelector('.room-name input').value;
    const capacity = row.querySelector('.room-capacity input').value;
    const equipment = row.querySelector('.room-equipment input').value;

    if (name && capacity) {
        fetch('edit_room.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                room_id: roomId,
                name: name,
                capacity: capacity,
                equipment: equipment
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the table with the new values
                    row.querySelector('.room-name').innerText = name;
                    row.querySelector('.room-capacity').innerText = capacity;
                    row.querySelector('.room-equipment').innerText = equipment;

                    // Replace Save button with Edit button
                    row.querySelector('.edit-btn').innerHTML = 'Edit';
                    row.querySelector('.edit-btn').setAttribute('onclick', `editRoom(${roomId})`);
                } else {
                    alert("Failed to update room: " + data.message);
                }
            });
    } else {
        alert("Room name and capacity are required.");
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
                    document.getElementById('room-row-' + roomId).remove(); // Remove row from table
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
