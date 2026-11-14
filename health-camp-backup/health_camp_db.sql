-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 26, 2026 at 05:53 PM
-- Server version: 10.11.16-MariaDB
-- PHP Version: 8.3.36

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `health_camp_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `health_camps`
--

CREATE TABLE `health_camps` (
  `camp_id` int(11) NOT NULL,
  `camp_name` varchar(150) NOT NULL,
  `location` varchar(200) NOT NULL,
  `camp_date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `organizer` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Planned'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `health_camps`
--

INSERT INTO `health_camps` (`camp_id`, `camp_name`, `location`, `camp_date`, `start_time`, `end_time`, `organizer`, `description`, `status`) VALUES
(1, 'General Health Check-up Camp', 'Community Hall, Puttur', '2026-11-15', '09:00:00', '13:00:00', 'Health Camp Team', 'General health check-up and consultation', 'Active'),
(2, 'eye camp', 'nagara', '2026-10-28', '09:30:00', '17:30:00', 'dr.xyz', '', 'Planned');

-- --------------------------------------------------------

--
-- Table structure for table `medicines`
--

CREATE TABLE `medicines` (
  `medicine_id` int(11) NOT NULL,
  `medicine_name` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `expiry_date` date NOT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medicines`
--

INSERT INTO `medicines` (`medicine_id`, `medicine_name`, `quantity`, `expiry_date`, `updated_at`) VALUES
(1, 'xuv', 20, '2030-11-30', '2026-10-26 14:49:36');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `patient_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`patient_id`, `full_name`, `age`, `gender`, `phone`) VALUES
(1, 'xxxx', 50, 'Female', '9663230248'),
(2, 'yyyyy', 75, 'Male', '9865434576'),
(3, 'test patient', 25, 'Female', '900000001');

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registration_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `camp_id` int(11) NOT NULL,
  `token_number` int(11) NOT NULL,
  `registration_type` enum('Online','On-Spot') NOT NULL DEFAULT 'Online',
  `queue_status` enum('Waiting','In Consultation','Completed') NOT NULL DEFAULT 'Waiting',
  `registered_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registration_id`, `patient_id`, `camp_id`, `token_number`, `registration_type`, `queue_status`, `registered_at`) VALUES
(1, 1, 1, 1, 'Online', 'Completed', '2026-10-26 11:25:22'),
(2, 2, 1, 2, 'Online', 'In Consultation', '2026-10-26 11:25:54'),
(3, 3, 1, 3, 'Online', 'Waiting', '2026-10-26 16:04:34');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Doctor','Volunteer') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `password_hash`, `role`, `created_at`) VALUES
(1, 'xxxx', 'mmmm', '$2y$10$0Tnnndr9o8l3k0wu59NhIeKxeU0q6eqduz.PXhFbhW7YK.zKyazyW', 'Admin', '2026-10-26 11:47:01'),
(2, 'zzz', 'm_1234', '$2y$10$WlDpBU5urm1anfn6wP.I6ephgLAottuWMWUXSa/0WAif54.hjPhlG', 'Doctor', '2026-10-26 14:35:06'),
(3, 'mmm', 'k_1234', '$2y$10$agUmytcHte3bwYmtpkhwtOK/9bORD9cLaMVoX/Ly2yWjBAjVZPYAC', 'Volunteer', '2026-10-26 14:35:51');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `health_camps`
--
ALTER TABLE `health_camps`
  ADD PRIMARY KEY (`camp_id`);

--
-- Indexes for table `medicines`
--
ALTER TABLE `medicines`
  ADD PRIMARY KEY (`medicine_id`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`patient_id`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registration_id`),
  ADD UNIQUE KEY `camp_id` (`camp_id`,`token_number`),
  ADD KEY `patient_id` (`patient_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `health_camps`
--
ALTER TABLE `health_camps`
  MODIFY `camp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `medicines`
--
ALTER TABLE `medicines`
  MODIFY `medicine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `patient_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registration_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`patient_id`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`camp_id`) REFERENCES `health_camps` (`camp_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
