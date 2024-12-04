// Add or submit room
let isAddingRoom = false;

function addRoom() {
    const tableBody = document.querySelector('#manage-rooms tbody');
    
    // Check if the input row already exists
    const existingRow = tableBody.querySelector('.new-room-row');

    if (isAddingRoom) {
        // If input row exists, submit the room details
        const row = existingRow;
        const name = row.querySelector('.room-name').value;
        const capacity = row.querySelector('.room-capacity').value;

        // Collect selected equipment
        const selectedEquipments = [];
        row.querySelectorAll('.room-equipment input:checked').forEach(checkbox => {
            selectedEquipments.push(checkbox.value);
        });

        // Validate the data
        if (name.trim() === '' || name.trim()>299 || name.trim()<1){
            alert("Room name cannot be empty")
        } else if(name.trim()>299 || name.trim()<1){
            alert("Room range is 001-299 only") 
        } else if (selectedEquipments.length === 0) {
            alert("You need to select at least one equipment.");
        } else if (selectedEquipments.length > 3) {
            alert("You can only select a maximum of 3 equipments.");
        } else if (name && capacity) {
            fetch('add_room.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    name: name,
                    capacity: capacity,
                    equipment: selectedEquipments
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // After successful save, update the table row with the entered data
                    row.innerHTML = `
                        <td>${name}</td>
                        <td>${capacity}</td>
                        <td>${selectedEquipments.join(', ')}</td>
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
            <td>
                <div class="room-equipment">
                    <label><input type="checkbox" value="Projector"> Projector</label><br>
                    <label><input type="checkbox" value="Whiteboard"> Whiteboard</label><br>
                    <label><input type="checkbox" value="SmartB"> Smart Board</label><br>
                    <label><input type="checkbox" value="NE Equipments"> Networking Equipment</label><br>
                    <label><input type="checkbox" value="PCS"> Computers</label><br>
                </div>
            </td>
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

// Add a new schedule (via table)
function addScheduleRow() {
    const table = document.querySelector("#manage-schedules tbody");
    const newRow = document.createElement("tr");

    newRow.innerHTML = `
        <td>
            <select id="new-room-id">
                ${Array.from(document.querySelectorAll(".room-name")).map(room => {
                    const roomId = room.parentElement.id.split("-")[2];
                    return `<option value="${roomId}">${room.innerText}</option>`;
                }).join('')}
            </select>
        </td>
        <td><input type="text" id="new-day" placeholder="Day of Week"></td>
        <td><input type="time" id="new-start-time"></td>
        <td><input type="time" id="new-end-time"></td>
        <td>
            <button onclick="saveNewSchedule()">Save</button>
            <button onclick="this.closest('tr').remove()">Cancel</button>
        </td>
    `;
    table.appendChild(newRow);
}

// Save the new schedule
function saveNewSchedule() {
    const roomId = document.querySelector("#new-room-id").value;
    const day = document.querySelector("#new-day").value;
    const startTime = document.querySelector("#new-start-time").value;
    const endTime = document.querySelector("#new-end-time").value;

    if (roomId && day && startTime && endTime) {
        fetch('add_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                room_id: roomId,
                day_of_week: day,
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
                alert("Error adding schedule: " + data.message);
            }
        });
    } else {
        alert("Please fill in all fields.");
    }
}

// Edit an existing schedule
function editScheduleRow(row, scheduleId) {
    const cells = row.querySelectorAll("td");
    const day = cells[1].innerText;
    const startTime = cells[2].innerText;
    const endTime = cells[3].innerText;

    cells[1].innerHTML = `<input type="text" value="${day}">`;
    cells[2].innerHTML = `<input type="time" value="${startTime}">`;
    cells[3].innerHTML = `<input type="time" value="${endTime}">`;

    cells[4].innerHTML = `
        <button onclick="saveEditedSchedule(${scheduleId}, this.closest('tr'))">Save</button>
        <button onclick="location.reload()">Cancel</button>
    `;
}

// Save edited schedule
function saveEditedSchedule(scheduleId, row) {
    const day = row.querySelector("td:nth-child(2) input").value;
    const startTime = row.querySelector("td:nth-child(3) input").value;
    const endTime = row.querySelector("td:nth-child(4) input").value;

    if (day && startTime && endTime) {
        fetch('edit_schedule.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                schedule_id: scheduleId,
                day_of_week: day,
                start_time: startTime,
                end_time: endTime
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("Schedule updated successfully!");
                location.reload();
            } else {
                alert("Error updating schedule: " + data.message);
            }
        });
    } else {
        alert("Please fill in all fields.");
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
