-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 28, 2025 at 05:03 PM
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
-- Database: `estcasa`
--

-- --------------------------------------------------------

--
-- Table structure for table `dorms`
--

CREATE TABLE `dorms` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `floors` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dorms`
--

INSERT INTO `dorms` (`id`, `name`, `gender`, `floors`) VALUES
(1, 'Girls Dorm 1', 'female', 2),
(2, 'Girls Dorm 2', 'female', 4),
(3, 'Boys Dorm', 'male', 5);

-- --------------------------------------------------------

--
-- Table structure for table `meal_reservations`
--

CREATE TABLE `meal_reservations` (
  `id` int(11) NOT NULL,
  `student_cin` varchar(10) DEFAULT NULL,
  `meal_type` enum('breakfast','lunch','dinner') DEFAULT NULL,
  `reservation_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_cin` varchar(10) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','paid','not paid') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_number` int(11) NOT NULL,
  `dorm_id` int(11) NOT NULL,
  `floor` int(11) NOT NULL,
  `capacity` int(11) DEFAULT 4,
  `occupied_slots` int(11) DEFAULT 0,
  `room_id` varchar(20) GENERATED ALWAYS AS (CONCAT(`room_number`, '-', `dorm_id`)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

-- --------------------------------------------------------

--
-- Table structure for table `room_requests`
--

CREATE TABLE `room_requests` (
  `id` int(11) NOT NULL,
  `student_cin` varchar(10) DEFAULT NULL,
  `room_id` varchar(20) DEFAULT NULL,
  `status` enum('pending','accepted','rejected') DEFAULT 'pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_requests`
--

INSERT INTO `room_requests` (`id`, `student_cin`, `status`, `request_date`) VALUES
(1, 'A123456789', 'pending', '2025-02-27 17:44:14');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `cin` varchar(10) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `gender` enum('male','female') NOT NULL,
  `room_id` varchar(20) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- Indexes for table `dorms`
--
ALTER TABLE `dorms`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meal_reservations`
--
ALTER TABLE `meal_reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_cin` (`student_cin`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_cin` (`student_cin`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_number`,`dorm_id`),
  ADD KEY `dorm_id` (`dorm_id`);

--
-- Indexes for table `room_requests`
--
ALTER TABLE `room_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_cin` (`student_cin`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`cin`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `dorms`
--
ALTER TABLE `dorms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `meal_reservations`
--
ALTER TABLE `meal_reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room_requests`
--
ALTER TABLE `room_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meal_reservations`
--
ALTER TABLE `meal_reservations`
  DROP FOREIGN KEY `meal_reservations_ibfk_1`;

ALTER TABLE `meal_reservations`
  ADD CONSTRAINT `meal_reservations_ibfk_1` FOREIGN KEY (`student_cin`) REFERENCES `students` (`cin`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  DROP FOREIGN KEY `payments_ibfk_1`;

ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`student_cin`) REFERENCES `students` (`cin`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rooms`
--
ALTER TABLE `rooms`
  ADD CONSTRAINT `rooms_ibfk_1` FOREIGN KEY (`dorm_id`) REFERENCES `dorms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_requests`
--
ALTER TABLE `room_requests`
  DROP FOREIGN KEY `room_requests_ibfk_1`;

ALTER TABLE `room_requests`
  ADD CONSTRAINT `room_requests_ibfk_1` FOREIGN KEY (`student_cin`) REFERENCES `students` (`cin`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE SET NULL;

-- Trigger to increment occupied_slots when a student is assigned to a room
DELIMITER //
CREATE TRIGGER increment_occupied_slots
AFTER UPDATE ON students
FOR EACH ROW
BEGIN
  IF NEW.room_id IS NOT NULL AND OLD.room_id IS NULL THEN
    UPDATE rooms SET occupied_slots = occupied_slots + 1 WHERE room_id = NEW.room_id;
  END IF;
END;
//
DELIMITER ;

-- Update occupied_slots based on existing data
UPDATE rooms r
JOIN (
  SELECT room_id, COUNT(*) AS occupied_count
  FROM students
  WHERE room_id IS NOT NULL
  GROUP BY room_id
) s ON r.room_id = s.room_id
SET r.occupied_slots = s.occupied_count;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
