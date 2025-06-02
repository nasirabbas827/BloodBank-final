-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 09:35 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bloodbank_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `blood_requests`
--

CREATE TABLE `blood_requests` (
  `request_id` int(11) NOT NULL,
  `requester_id` int(11) NOT NULL,
  `requester_type` enum('hospital','recipient') NOT NULL,
  `patient_name` varchar(100) NOT NULL,
  `blood_type_required` varchar(5) NOT NULL,
  `quantity_required` int(11) NOT NULL,
  `urgency_level` enum('routine','urgent','emergency') NOT NULL,
  `required_by_date` date NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','fulfilled','rejected') DEFAULT 'pending',
  `admin_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_requests`
--

INSERT INTO `blood_requests` (`request_id`, `requester_id`, `requester_type`, `patient_name`, `blood_type_required`, `quantity_required`, `urgency_level`, `required_by_date`, `purpose`, `status`, `admin_notes`, `created_at`, `updated_at`) VALUES
(1, 2, 'hospital', 'Haider', 'B+', 2, 'urgent', '2025-06-12', 'd', 'approved', 'dsasa', '2025-06-02 11:08:42', '2025-06-02 11:57:28'),
(2, 2, 'hospital', 'Haider', 'B+', 2, 'emergency', '2025-06-12', 'd', 'pending', NULL, '2025-06-02 11:09:21', '2025-06-02 11:09:21'),
(3, 2, 'hospital', 'Asad', 'O-', 2, 'routine', '2025-06-27', 'Accident', 'pending', NULL, '2025-06-02 11:37:59', '2025-06-02 11:37:59'),
(4, 3, 'hospital', 'NMew', 'B-', 20, 'urgent', '2025-07-04', 'i want to build my loan', 'pending', NULL, '2025-06-02 11:42:04', '2025-06-02 11:42:04'),
(5, 3, 'hospital', 'NMew', 'B-', 20, 'urgent', '2025-07-04', 'i want to build my loan', 'pending', NULL, '2025-06-02 11:43:35', '2025-06-02 11:43:35'),
(6, 3, 'hospital', 'NMew', 'A-', 20, 'routine', '2025-06-03', 'd', 'approved', 'Yesakndaeaw', '2025-06-02 11:44:04', '2025-06-02 11:57:11'),
(7, 3, 'recipient', 'NMew', 'B-', 20, 'routine', '2025-07-04', 'ds', 'approved', 'admin notes', '2025-06-02 11:45:17', '2025-06-02 11:46:49');

-- --------------------------------------------------------

--
-- Table structure for table `blood_stock`
--

CREATE TABLE `blood_stock` (
  `id` int(11) NOT NULL,
  `blood_type` varchar(5) NOT NULL,
  `units_available` int(11) NOT NULL,
  `last_updated` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `blood_stock`
--

INSERT INTO `blood_stock` (`id`, `blood_type`, `units_available`, `last_updated`) VALUES
(1, 'A+', 10, '2025-06-02 10:14:09'),
(2, 'A-', 8, '2025-06-02 10:14:09'),
(3, 'B+', 12, '2025-06-02 10:14:09'),
(4, 'B-', 6, '2025-06-02 10:14:09'),
(5, 'O+', 15, '2025-06-02 10:14:09'),
(6, 'O-', 7, '2025-06-02 10:14:09'),
(7, 'AB+', 5, '2025-06-02 10:14:09'),
(8, 'AB-', 60, '2025-06-02 12:20:12');

-- --------------------------------------------------------

--
-- Table structure for table `donations`
--

CREATE TABLE `donations` (
  `donation_id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `donation_date` date NOT NULL,
  `donation_type` enum('Whole Blood','Plasma','Platelets') NOT NULL,
  `quantity` int(11) NOT NULL,
  `health_screening_results` text NOT NULL,
  `status` enum('pending','completed','rejected') DEFAULT 'pending',
  `rejection_reason` text DEFAULT NULL,
  `next_eligible_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `donations`
--

INSERT INTO `donations` (`donation_id`, `donor_id`, `donation_date`, `donation_type`, `quantity`, `health_screening_results`, `status`, `rejection_reason`, `next_eligible_date`) VALUES
(2, 1, '2025-06-02', 'Plasma', 20, 'dssd', 'rejected', 'dsds', '2025-06-04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('Blood Recipient','Blood Donor','Hospital Representative') NOT NULL,
  `phone` varchar(20) NOT NULL,
  `location` varchar(255) NOT NULL,
  `blood_type` enum('A+','A-','B+','B-','O+','O-','AB+','AB-') DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `full_address` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `user_type`, `phone`, `location`, `blood_type`, `dob`, `gender`, `full_address`, `status`, `created_at`) VALUES
(1, 'NASIR ABBAS', 'nasiryt.827@gmail.com', '$2y$10$s3P3NoErACmPgGQYlYprOOpkVDzyywe.H9oVQn12L0UAu3iscNymu', 'Blood Donor', '03176526827', 'Multan', 'A+', '2025-06-02', 'Male', 'p/o box basti cheena tehsil jampur district rajanpur\r\nAp04', 'approved', '2025-06-02 05:02:59'),
(2, 'Hospital', 'hospital@gmail.com', '$2y$10$f0qTt7hNgOa17UGTWpPa3.w2ETDVD11.tLz9txTndmpHGmljOn.Um', 'Hospital Representative', '031887895222', 'Multan', 'AB+', NULL, NULL, NULL, 'approved', '2025-06-02 05:03:38'),
(3, 'Recip', 'rec@gmail.com', '$2y$10$I61oGCCQPGHXSYql7KWw2ubEItuwhE/Br2HZ5XNiz4PMI0t0XWIs.', 'Blood Recipient', '31765268278', 'London', 'AB+', '2025-07-11', 'Female', 'Tehsil Jampur District Rajanpur\r\nTehsil Jampur District Rajanpur', 'pending', '2025-06-02 06:40:44');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `fk_requester_user` (`requester_id`);

--
-- Indexes for table `blood_stock`
--
ALTER TABLE `blood_stock`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `donations`
--
ALTER TABLE `donations`
  ADD PRIMARY KEY (`donation_id`),
  ADD KEY `donor_id` (`donor_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `phone` (`phone`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `blood_requests`
--
ALTER TABLE `blood_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `blood_stock`
--
ALTER TABLE `blood_stock`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `donations`
--
ALTER TABLE `donations`
  MODIFY `donation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `blood_requests`
--
ALTER TABLE `blood_requests`
  ADD CONSTRAINT `fk_requester_user` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `donations`
--
ALTER TABLE `donations`
  ADD CONSTRAINT `donations_ibfk_1` FOREIGN KEY (`donor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
