-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2024 at 10:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `room_booking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_actions`
--

CREATE TABLE `admin_actions` (
  `action_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action_description` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `duration` int(11) NOT NULL,
  `status` enum('booked','cancelled') DEFAULT 'booked'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_id`, `user_id`, `room_id`, `start_time`, `end_time`, `duration`, `status`) VALUES
(1, 3, 1, '2024-12-12 08:00:00', '2024-12-12 08:50:00', 50, 'cancelled'),
(2, 3, 1, '2024-12-11 08:00:00', '2024-12-11 08:50:00', 50, 'cancelled'),
(3, 3, 1, '2024-12-19 14:00:00', '2024-12-19 14:50:00', 50, 'cancelled'),
(4, 3, 1, '2024-12-26 08:00:00', '2024-12-26 09:15:00', 75, 'booked'),
(5, 3, 1, '2024-12-26 12:00:00', '2024-12-26 12:50:00', 50, 'booked'),
(6, 3, 1, '2024-12-12 09:00:00', '2024-12-12 09:50:00', 50, 'booked'),
(7, 3, 1, '2024-12-12 08:00:00', '2024-12-12 08:50:00', 50, 'booked'),
(8, 3, 1, '2024-12-10 08:00:00', '2024-12-10 08:50:00', 50, 'cancelled'),
(9, 3, 3, '2024-12-10 08:00:00', '2024-12-10 08:50:00', 50, 'booked'),
(10, 3, 3, '2025-01-09 08:00:00', '2025-01-09 08:50:00', 50, 'booked'),
(11, 3, 3, '2025-01-08 17:00:00', '2025-01-08 17:50:00', 50, 'booked'),
(12, 3, 4, '2025-01-02 08:00:00', '2025-01-02 08:50:00', 50, 'booked'),
(13, 3, 3, '2024-12-09 08:00:00', '2024-12-09 08:50:00', 50, 'booked'),
(14, 3, 3, '2024-12-18 08:00:00', '2024-12-18 09:40:00', 100, 'booked'),
(15, 3, 4, '2024-12-24 14:00:00', '2024-12-24 15:15:00', 75, 'booked'),
(16, 3, 1, '2024-12-12 10:00:00', '2024-12-12 10:50:00', 50, 'booked'),
(17, 3, 1, '2024-12-19 08:00:00', '2024-12-19 08:50:00', 50, 'cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `equipment` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `name`, `capacity`, `equipment`, `created_at`) VALUES
(1, '111', 23, 'Projector', '2024-12-03 15:41:08'),
(3, '3', 90, 'Projector', '2024-12-04 09:42:05'),
(4, '002', 23, 'SmartB', '2024-12-04 09:57:59');

-- --------------------------------------------------------

--
-- Table structure for table `room_schedules`
--

CREATE TABLE `room_schedules` (
  `schedule_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'default.jpg',
  `user_type` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gender` enum('Male','Female') DEFAULT NULL,
  `major` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `profile_picture`, `user_type`, `created_at`, `gender`, `major`) VALUES
(1, 'Hawra Fadhel', '202212345@stu.uob.edu.bh', '$2y$10$SMcrfzIaudgWuzjKF20aLOrnygVWNp92gCa5T9nCjHF4t7pMLlF6i', '6755865486597-hhh.jpg', 'user', '2024-12-04 11:16:45', 'Female', 'Cybersecurity'),
(2, 'alya', '202212345@uob.edu.bh', '$2y$10$sdaFAajMHNOhBWb4KfwPIOMEEBhuSTLjY18PTz7Mrhhd17AIfaQ4C', 'default.jpg', 'admin', '2024-12-08 14:26:07', 'Male', 'Information Systems'),
(3, 'khaireya', '202208539@stu.uob.edu.bh', '$2y$10$9MiYD7ODyEc.wsAG2q0tRey8UGu3ftoKkWoiDVNKnB4nrJc7sCjfe', 'default.jpg', 'user', '2024-12-08 23:04:38', '', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD PRIMARY KEY (`action_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `room_schedules`
--
ALTER TABLE `room_schedules`
  ADD PRIMARY KEY (`schedule_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_actions`
--
ALTER TABLE `admin_actions`
  MODIFY `action_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `room_schedules`
--
ALTER TABLE `room_schedules`
  MODIFY `schedule_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_actions`
--
ALTER TABLE `admin_actions`
  ADD CONSTRAINT `admin_actions_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);

--
-- Constraints for table `room_schedules`
--
ALTER TABLE `room_schedules`
  ADD CONSTRAINT `room_schedules_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
