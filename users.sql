-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2026 at 10:38 AM
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
-- Database: `nhs_booking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `nhs_number` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `role` enum('patient','admin') NOT NULL DEFAULT 'patient',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `nhs_number`, `first_name`, `last_name`, `email`, `password_hash`, `phone`, `date_of_birth`, `role`, `created_at`) VALUES
(5, '9999999999', 'Admin', 'Test', 'Admin@test.com', '$2y$10$1Gvg0EO1m8H2mW1PRrS/h./oHq1BQ.UBrPNllKqyy11FrNRrtHzie', '07000000000', '2000-01-01', 'admin', '2026-04-05 07:53:49'),
(6, '1111111111', 'Test', 'User', 'test1@test.com', '$2y$10$xiqh/Fc3ccDbHlBxWSv1fe8iMl4lgNaOs0Ose2RaxFWY9.gu2OL1q', '07000000000', '2000-01-01', 'patient', '2026-04-05 08:10:32'),
(7, '2222222222', 'Test1', 'User', 'test2@test.com', '$2y$10$BbWG3WWxgXmFuAZeyrx0TejDOb44Rp3bpWqSOddcwtqTjTTC48P1K', '00000000007', '2000-01-01', 'patient', '2026-04-05 08:15:53'),
(8, '0000000001', 'Test3', 'User', 'test3@test.com', '$2y$10$nwqSPWJnEA7mx4QJlaFtguoSeoSjDXNv6u5XGXfnbdl7CQ9dC1.bi', '00000000007', '2000-01-01', 'patient', '2026-04-05 08:17:54'),
(9, '0000000002', 'Test4', 'User', 'test4@test.com', '$2y$10$sJupxSxqB8rGEwEztfKKhe12whwIPOtrl.R2xYrQssTKtHq63t6ZS', '00000000007', '1900-01-01', 'patient', '2026-04-05 08:19:25'),
(10, '0000000003', 'Test5', 'User', 'test5@test.com', '$2y$10$xTSSWzvUvMz6PoU9JypgcexBDwa.fPz4c6qO6EXrjy62zArPH8QNm', '00000000007', '1000-01-01', 'patient', '2026-04-05 08:19:52');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `nhs_number` (`nhs_number`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
