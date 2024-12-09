let isAddingRoom = false;

function addRoom() {
    const tableBody = document.querySelector('#manage-rooms tbody');

    if (isAddingRoom) {
        // If adding mode, save the room
        const newRow = tableBody.querySelector('.new-room-row');
        const name = newRow.querySelector('.room-name').value;
        const capacity = newRow.querySelector('.room-capacity').value;
        const equipment = Array.from(newRow.querySelectorAll('.room-equipment input:checked')).map(e => e.value);

        if(name <=0 || name>299 || capacity <=0 || capacity>45 || equipment.length > 5) {
            alert("You've exceeded the range for either: Room no. , capacity or equipments.")
            return;
        }
        if (!name || !capacity || equipment.length === 0) {
            alert("Please fill all fields and select at least one equipment.");
            return;
        }
        
        fetch('add_room.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ name, capacity, equipment })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Room added successfully!");
                newRow.innerHTML = `
                    <td>${name}</td>
                    <td>${capacity}</td>
                    <td>${equipment.join(', ')}</td>
                    <td>
                        <button class="button is-info is-small" onclick="editRoom(${data.room_id})">Edit</button>
                        <button class="button is-danger is-small" onclick="deleteRoom(${data.room_id})">Delete</button>
                    </td>
                `;
                isAddingRoom = false;
            } else {
                alert("Failed to add room: " + data.message);
            }
        });
    } else {
        // Create a new row for adding a room
        const newRow = document.createElement('tr');
        newRow.classList.add('new-room-row');
        newRow.innerHTML = `
            <td><input type="text" class="input room-name" placeholder="Room Name"></td>
            <td><input type="text" class="input room-capacity" placeholder="Capacity"></td>
            <td>
                <div class="room-equipment">
                    <label><input type="checkbox" value="Projector"> Projector</label>
                    <label><input type="checkbox" value="Whiteboard"> Whiteboard</label>
                    <label><input type="checkbox" value="SmartBoard"> Smart Board</label>
                    <label><input type="checkbox" value="NE Equipment"> Networking Equipment</label>
                    <label><input type="checkbox" value="PCS"> Computers</label>
                </div>
            </td>
            <td>
                <button class="button is-primary is-small" onclick="addRoom()">Save</button>
                <button class="button is-warning is-small" onclick="cancelAddRoom(this)">Cancel</button>
            </td>
        `;
        tableBody.appendChild(newRow);
        isAddingRoom = true;
    }
}

function cancelAddRoom(button) {
    button.closest('tr').remove();
    isAddingRoom = false;
}

function editRoom(roomId) {
    const row = document.getElementById(`room-row-${roomId}`);
    const name = row.querySelector('.room-name').innerText;
    const capacity = row.querySelector('.room-capacity').innerText;
    const equipment = row.querySelector('.room-equipment').innerText.split(', ');  // Split the comma-separated equipment list

    row.innerHTML = `
        <td><input class="input room-name" value="${name}"></td>
        <td><input class="input room-capacity" value="${capacity}"></td>
        <td>
            <div class="room-equipment">
                <label><input type="checkbox" value="Projector" ${equipment.includes('Projector') ? 'checked' : ''}> Projector</label>
                <label><input type="checkbox" value="Whiteboard" ${equipment.includes('Whiteboard') ? 'checked' : ''}> Whiteboard</label>
                <label><input type="checkbox" value="SmartBoard" ${equipment.includes('SmartBoard') ? 'checked' : ''}> Smart Board</label>
                <label><input type="checkbox" value="NE Equipment" ${equipment.includes('NE Equipment') ? 'checked' : ''}> Networking Equipment</label>
                <label><input type="checkbox" value="PCS" ${equipment.includes('PCS') ? 'checked' : ''}> Computers</label>
            </div>
        </td>
        <td>
            <button class="button is-success is-small" onclick="saveRoom(${roomId})">Save</button>
            <button class="button is-warning is-small" onclick="location.reload()">Cancel</button>
        </td>
    `;
}

function saveRoom(roomId) {
    const row = document.getElementById(`room-row-${roomId}`);
    const name = row.querySelector('.room-name').value;
    const capacity = row.querySelector('.room-capacity').value;

    // Get selected equipment
    const equipment = Array.from(row.querySelectorAll('input[type="checkbox"]:checked'))
                            .map(checkbox => checkbox.value);

    if (!name || !capacity) {
        alert("Name and capacity are required.");
        return;
    }

    fetch('edit_room.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ room_id: roomId, name, capacity, equipment })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Room updated successfully!");
            row.innerHTML = `
                <td class="room-name">${name}</td>
                <td class="room-capacity">${capacity}</td>
                <td><button class="button is-info is-small" onclick="editRoom(${roomId})">Edit</button>
                <button class="button is-danger is-small" onclick="deleteRoom(${roomId})">Delete</button></td>
            `;
        } else {
            alert("Failed to update room: " + data.message);
        }
    });
}

function deleteRoom(roomId) {
    if (confirm("Are you sure you want to delete this room? This action cannot be undone.")) {
        fetch('delete_room.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ room_id: roomId })
        })
        .then(response => response.json())  // Parse JSON response
        .then(data => {
            if (data.success) {
                alert("Room and related bookings deleted successfully!");
                const row = document.getElementById(`room-row-${roomId}`);
                if (row) {
                    row.remove();
                }
            } else {
                alert("Failed to delete room: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error deleting room:", error);
            alert("An error occurred. Please try again.");
        });
    }
}

//Schedules-----------------------------------------------

function addSchedule() {
    const roomId = document.querySelector('select[name="room_id"]').value;
    const dayOfWeek = document.querySelector('select[name="day_of_week"]').value;
    const startTime = document.querySelector('input[name="start_time"]').value;
    const endTime = document.querySelector('input[name="end_time"]').value;

    // Create a data object to send to PHP
    const scheduleData = {
        room_id: roomId,
        day_of_week: dayOfWeek,
        start_time: startTime,
        end_time: endTime
    };

    // Make an AJAX request using fetch
    fetch('add_schedule.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(scheduleData)  // Send the data as JSON
    })
    .then(response => response.json())  // Parse the JSON response
    .then(data => {
        if (data.success) {
            alert('Schedule added successfully!');
            location.reload();
        } else {
            alert('Failed to add schedule: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

function updateSchedule(scheduleId) {
    const row = document.querySelector(`tr[data-schedule-id="${scheduleId}"]`);
    const startTime = row.querySelector('input[type="time"]').value;
    const endTime = row.querySelectorAll('input[type="time"]')[1].value;

    if (!startTime || !endTime || startTime >= endTime) {
        alert("Proper start and end times are required.");
        return;
    }

    console.log("Updating schedule with:", { schedule_id: scheduleId, start_time: startTime, end_time: endTime });

    fetch('update_schedule.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ schedule_id: scheduleId, start_time: startTime, end_time: endTime })
    })
    .then(response => {
        console.log("Response received:", response);
        return response.json(); // Ensure you return the parsed JSON here-promise purposes
    })
    .then(data => {
        console.log("Parsed response data:", data);

        if (data.success) {
            alert("Schedule updated successfully!");
            row.querySelector('input[type="time"]').setAttribute('data-original-value', startTime);
            row.querySelectorAll('input[type="time"]')[1].setAttribute('data-original-value', endTime);
        } else {
            alert("Failed to update schedule: " + data.message);
        }
    })
    .catch(error => {
        console.error("Error updating schedule:", error);
        alert("An error occurred while updating the schedule.");
    });
}

//Deletes a schedule
function deleteSchedule(scheduleId) {
    if (confirm("Are you sure you want to delete this schedule? This action cannot be undone.")) {
        fetch('delete_schedule.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ schedule_id: scheduleId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Schedule deleted successfully!");
                const row = document.querySelector(`tr[data-schedule-id="${scheduleId}"]`);
                if (row) {
                    row.remove();
                }
            } else {
                alert("Failed to delete schedule: " + data.message);
            }
        })
        .catch(error => {
            console.error("Error deleting schedule:", error);
            alert("An error occurred while deleting the schedule.");
        });
    }
}

