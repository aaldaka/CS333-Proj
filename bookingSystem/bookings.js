//Dynamically update time slots based on selected date and duration

function updateTimeSlots() {
  const duration = parseInt(document.getElementById('duration').value); 
  const startTimeSelect = document.getElementById('start_time'); 
  const dateInput = document.getElementById('date'); 
  const selectedDate = new Date(dateInput.value); 

  // Clear existing time slots
  startTimeSelect.innerHTML = '';

  // Define interval in minutes based on duration
  let intervalMinutes = 0;
  if (duration === 50) {
      intervalMinutes = 60; // Treat 50 minutes as 1-hour intervals
  } else if (duration === 75) {
      intervalMinutes = 90; // Treat 75 minutes as 1.5-hour intervals
  } else if (duration === 100) {
      intervalMinutes = 120; // Treat 100 minutes as 2-hour intervals
  }

  // Get the day of the week 
  const dayOfWeek = selectedDate.toLocaleString('en-US', { weekday: 'long' });

  // Filter schedules for the selected day
  const schedulesForDay = roomSchedules.filter(schedule => schedule.day_of_week === dayOfWeek);

  // If there are admin-defined schedules for the day, use them
  if (schedulesForDay.length > 0) {
      schedulesForDay.forEach(schedule => {
          const [startHour, startMinute] = schedule.start_time.split(':').map(Number); 
          const [endHour, endMinute] = schedule.end_time.split(':').map(Number); 
          let currentHour = startHour;
          let currentMinute = startMinute;

          while (currentHour < endHour || (currentHour === endHour && currentMinute < endMinute)) {
              const startTime = `${String(currentHour).padStart(2, '0')}:${String(currentMinute).padStart(2, '0')}`;
              const endTime = new Date(new Date(`1970-01-01T${startTime}:00`).getTime() + duration * 60000);
              const endTimeHour = endTime.getHours();
              const endTimeMinute = endTime.getMinutes();

              if (
                  endTimeHour < endHour ||
                  (endTimeHour === endHour && endTimeMinute <= endMinute)
              ) {
                  const startTimeDisplay = new Date(`1970-01-01T${startTime}:00`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                  const endTimeDisplay = new Date(`1970-01-01T${String(endTimeHour).padStart(2, '0')}:${String(endTimeMinute).padStart(2, '0')}:00`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
                  const totalTimeDisplay = `${startTimeDisplay} - ${endTimeDisplay}`;
                  const option = document.createElement('option');
                  option.value = startTime;
                  option.textContent = totalTimeDisplay;
                  startTimeSelect.appendChild(option);
              }

              currentMinute += intervalMinutes % 60;
              currentHour += Math.floor(intervalMinutes / 60);
              if (currentMinute >= 60) {
                  currentMinute -= 60;
                  currentHour++;
              }
          }
      });
  } else {
      // Default time range
      const startHour = 8; // Default start at 8:00 AM
      const endHour = 20; // Default end at 8:00 PM
      let currentHour = startHour;
      let currentMinute = 0;

      while (currentHour < endHour || (currentHour === endHour && currentMinute < 60)) {
          const startTime = `${String(currentHour).padStart(2, '0')}:${String(currentMinute).padStart(2, '0')}`;
          const endTime = new Date(new Date(`1970-01-01T${startTime}:00`).getTime() + duration * 60000);
          const startTimeDisplay = new Date(`1970-01-01T${startTime}:00`).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
          const endTimeDisplay = endTime.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });
          const totalTimeDisplay = `${startTimeDisplay} - ${endTimeDisplay}`;
          const option = document.createElement('option');
          option.value = startTime;
          option.textContent = totalTimeDisplay;
          startTimeSelect.appendChild(option);

          currentMinute += intervalMinutes % 60;
          currentHour += Math.floor(intervalMinutes / 60);
          if (currentMinute >= 60) {
              currentMinute -= 60;
              currentHour++;
          }
      }
  }
}

function refreshTimeSlots() {
  const selectedDate = new Date(document.getElementById('date').value); // Get the selected date
  const now = new Date(); // Get the current date and time

  const startTimeSelect = document.getElementById('start_time'); // Get the time dropdown
  const options = startTimeSelect.options; // Get all time options

  // Get the duration in minutes (from the duration dropdown)
  const duration = parseInt(document.getElementById('duration').value, 10); // e.g., 50, 75, 100, etc.

  // **Step 1: Reset all options to be enabled**
  for (let i = 0; i < options.length; i++) {
      options[i].disabled = false; // Reset all options to enabled
  }

  // **Step 2: Only apply logic if the date is valid**
  if (!isNaN(selectedDate)) {
      // Check if the selected date is today
      if (selectedDate.toDateString() === now.toDateString()) {
          // Loop through each time option
          for (let i = 0; i < options.length; i++) {
              const optionTime = new Date(`1970-01-01T${options[i].value}:00`); // Parse the time option
              const currentTime = new Date(`1970-01-01T${now.getHours()}:${now.getMinutes()}:00`); // Current time as a Date object

              // Calculate the end time of the selected slot based on the duration
              const optionEndTime = new Date(optionTime.getTime() + duration * 60 * 1000); // Add duration in milliseconds

              // Disable options for past times or those that don't fit the duration within the same day
              if (optionTime <= currentTime || optionEndTime.getDate() > optionTime.getDate()) {
                  options[i].disabled = true; // Disable past or invalid options
              }
          }
      }
  }
}

// Add event listener to the date input
document.getElementById('date').addEventListener('change', refreshTimeSlots);

// Add event listener to the duration input
document.getElementById('duration').addEventListener('change', refreshTimeSlots);
//clear the error message everytime changing the input of time
document.getElementById('date').addEventListener('change', () => {
  document.getElementById('error_message').style.display = 'none';
});
document.getElementById('duration').addEventListener('change', () => {
  document.getElementById('error_message').style.display = 'none';
});

//initialize time slots
window.onload = function () {
  updateTimeSlots();
};
