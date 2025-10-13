-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2025 at 10:06 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cab_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `availability`
--

CREATE TABLE `availability` (
  `id` int(11) NOT NULL,
  `cab_id` int(11) NOT NULL,
  `status` enum('available','not available','on duty','in maintenance') NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `pickup_location` varchar(255) NOT NULL,
  `dropoff_location` varchar(255) NOT NULL,
  `booking_status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `booking_date` timestamp NULL DEFAULT current_timestamp(),
  `cash` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cabs`
--

CREATE TABLE `cabs` (
  `id` int(11) NOT NULL,
  `driver_id` int(11) NOT NULL,
  `model` varchar(255) NOT NULL,
  `plate_number` varchar(20) NOT NULL,
  `capacity` int(11) NOT NULL,
  `fuel_type` varchar(20) DEFAULT NULL,
  `price_per_km` decimal(10,2) NOT NULL,
  `availability` enum('On duty','On leave','maintenance') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cabs`
--

INSERT INTO `cabs` (`id`, `driver_id`, `model`, `plate_number`, `capacity`, `fuel_type`, `price_per_km`, `availability`, `created_at`) VALUES
(27, 86, 'DILUX', '7777X', 12, 'petrol', 899.00, 'On duty', '2025-01-19 15:34:28'),
(35, 86, 'KAJJAA', '121212', 11, 'disael', 11212.00, '', '2025-01-20 04:35:54');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `address`, `created_at`, `updated_at`, `username`, `password`) VALUES
(79, 'Prakash thapa', 'prakash123@gmail.com', '9876787656', 'butwal', '2025-01-19 13:23:23', '2025-01-19 13:23:23', 'prakash12333', '00000000o'),
(80, 'Ayush kharel', 'ayush133@gmail.com', '9865746799', 'nayamil', '2025-01-19 13:27:43', '2025-01-19 13:31:55', 'ayush1111', '11111111q'),
(89, 'Minraj gyawali', 'minraj12@gmail.com', '9857463547', 'Butwal', '2025-01-20 01:03:52', '2025-01-20 01:03:52', 'minraj123', 'minraj123'),
(90, 'Bot First', 'bot1@gmail.com', '9857464637', 'Butwal - 10 , Kalikanagar', '2025-09-27 17:20:13', '2025-09-27 17:20:13', 'dumdum123', 'dumdum123'),
(91, 'John doe', 'jonhdoe123@gmail.com', '9857463257', 'Kathmandu-12', '2025-10-13 08:05:11', '2025-10-13 08:05:11', 'johndoe12', 'johndoe12@gmail');

-- --------------------------------------------------------

--
-- Table structure for table `drivers`
--

CREATE TABLE `drivers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `license_number` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `drivers`
--

INSERT INTO `drivers` (`id`, `name`, `email`, `phone`, `license_number`, `username`, `password`) VALUES
(86, 'Kaushal Yadav', 'kaushal12@gmail.com', '9875643553', '7777888', 'kaushal1', 'kaushal12345'),
(87, 'Raja sunar', 'Raja12@gmail.com', '9857645362', '888997', 'raja2', 'raja12345');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','driver','renter','customer') NOT NULL,
  `local_area` varchar(255) DEFAULT NULL,
  `gender` enum('male','female','other') DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `local_area`, `gender`, `created_at`) VALUES
(6, 'admin', 'admin123', 'admin', 'Admin Area', 'male', '2024-10-12 09:48:04'),
(79, 'bot 1', '00000000o', 'customer', 'butwal-nepal', 'male', '2025-01-19 13:23:23'),
(80, 'ayushpro', '11111111q', 'customer', 'nepal', 'male', '2025-01-19 13:27:43'),
(86, 'bot 2', 'kaushal12345', 'driver', 'Drivertole', 'male', '2025-01-19 15:32:57'),
(87, 'bot 3', 'raja12345', 'driver', 'Milan Chowk', 'male', '2025-01-19 15:48:00'),
(89, 'Bot 4', 'minraj12345', 'customer', 'Butwal', 'male', '2025-01-20 01:03:52'),
(90, 'dumdum123', 'dumdum123', 'customer', NULL, 'male', '2025-09-27 17:20:13'),
(91, 'johndoe12', 'johndoe12@gmail', 'customer', NULL, 'male', '2025-10-13 08:05:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `availability`
--
ALTER TABLE `availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cab_id` (`cab_id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `cabs`
--
ALTER TABLE `cabs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_number` (`plate_number`),
  ADD KEY `driver_id` (`driver_id`);

--
-- Indexes for table `drivers`
--
ALTER TABLE `drivers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `availability`
--
ALTER TABLE `availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cabs`
--
ALTER TABLE `cabs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=92;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `availability`
--
ALTER TABLE `availability`
  ADD CONSTRAINT `availability_ibfk_1` FOREIGN KEY (`cab_id`) REFERENCES `cabs` (`id`);

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
