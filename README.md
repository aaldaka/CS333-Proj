
# Responsive Web-Based IT College Room Booking System
The Room Booking System is a web-based application designed for the IT College that allows students, faculty, and administrators to browse, book, and manage room schedules. It also provides insightful analytics for users to view room usage trends.

## Authors
- Malak Jamal Almari , 202103282
- Hawra Fadhel Abbas, 202208944
- Maryam Ali Hasan , 202209427
- Khaireya Husain Alhaiki , 202208539
- Alya Hasan , 202208622
- Noora Salah Salem , 202209541
## Table of Contents
    1. How to run
    2. User Registration and Login
    3. User Profile Management
    4. Room Browsing and Details
    5. Booking System
    6. Admin Panel
    7. Reporting and Analytics


## How to run
- You must start both "Apache" and "MySQL" from the XAMPP Control Panel.
- Once it's on, enter this URL http://localhost/CS333-Proj/.
- Make sure you create an account with the right access privileges "stu.uob.edu.bh" for students and "uob.edu.bh" for faculty.
- Enjoy your booking trip!
## User Registration and Login
- In the login.php script, user login authentication is processed by verifying input credentials, fetching user data from the database, and utilizing password_verify for password validation. Upon successful login, user IDs and types are stored in sessions, with an option to remember login credentials securely through cookies. Users are redirected to the home page post-login, while error messages are displayed for incorrect login attempts.

- The registration.php script manages the registration process by validating user input fields, ensuring the correct format of UOB email addresses, and confirming password consistency. Valid input data is then hashed for password security using password_hash. The script also checks for existing email registrations in the database and provides appropriate error messages in case of issues. After successful registration, users are directed to the login page to access the room booking system.

## User Profile Management
- In this project, I was responsible for developing the User Profile Management system for the web-based room booking system. My tasks included designing and implementing the user profile page, where users can view and edit their profile information, such as name, email, gender, major, and profile picture. I also created functionality for users to upload and manage their profile pictures.
## Room Browsing and Details
- my part , that take the data from the database that was related to the rooms and IDs.
- Clear organizing to the data visibility for each room and important details.
- At each room that will incload the components that inside each room and display only the available rooms.
- List the all availabe slots that take the data for it from the database schedules and when we don't find it that will set as defult for booking rooms.
## Booking System
- I had to implement a full booking system, where I focused on allowing users to make bookings easily and efficiently and store them in MySQL database.
- Designed the system to handle user inputs, like selecting dates and times. Moreover, ensured the data was stored correctly and  ensured valid user booking with functionalities such as checking for room availability and user conflicts.
- A cancel feature was implemented so users can cancel their upcoming bookings easily!
## Admin Panel
- In my part, I was responsible for ensuring the logged in user was indeed an admin/faculty member.
- Based on that, they get privileges that allows them to add,delete and modify rooms and their schedules.
- Admin logs were also added, this ensures nonrepudation and keeps track of processes.
## Reporting and Analytics
I developed a Reporting and Analytics system to provide general view into room usage and booking trends. The system includes visualizations such as a pie chart displaying total hours for each room and a bar chart showing total bookings per room, displayd using JavaScript and designed using CSS and powered by SQL queries for retrieving data from booking and room tables in the database. It also displays detailed room information, including the room name, total bookings, and total minutes booked. Additionally, users can view past and upcoming bookings in a table format, showing booking ID, room name, start time, and end time, with data integrated using PHP and styled via an external CSS file.
## Additional Concepts
- We used Bulma framework for CSS, and utilized JavaScript to serve different purposes in our project, such as calculation, display, alerts, and toggles.
## Conclusion
The Room Booking System simplifies room reservations with features like real-time analytics, secure user accounts, and role-based access. It offers a clear and easy-to-use interface, and ensures smooth booking management. 
