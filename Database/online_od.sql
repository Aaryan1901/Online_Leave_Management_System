-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 08, 2025 at 06:32 PM
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
-- Database: `online_od`
--

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `enrollment` varchar(50) NOT NULL,
  `department` varchar(100) NOT NULL,
  `year_of_study` varchar(50) NOT NULL,
  `programme` varchar(50) NOT NULL,
  `branch` varchar(100) NOT NULL,
  `class` varchar(50) NOT NULL,
  `leave_type` varchar(50) NOT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `days_availed` int(11) DEFAULT NULL,
  `parent_sign` varchar(255) DEFAULT NULL,
  `student_sign` varchar(255) DEFAULT NULL,
  `advisor_letter` varchar(255) DEFAULT NULL,
  `hod_letter` varchar(255) DEFAULT NULL,
  `submission_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(20) DEFAULT 'Pending',
  `email` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_applications`
--

INSERT INTO `leave_applications` (`id`, `name`, `enrollment`, `department`, `year_of_study`, `programme`, `branch`, `class`, `leave_type`, `from_date`, `to_date`, `reason`, `days_availed`, `parent_sign`, `student_sign`, `advisor_letter`, `hod_letter`, `submission_time`, `status`, `email`) VALUES
(1, 'AARYAN M', '2201112003', 'IT', '3rd Year', 'B.Tech', 'CC', 'a', 'duty', '2025-03-20', '2025-03-22', 'Stress', 2, 'uploads/download.jpeg', 'uploads/images (1).png', 'uploads/Domain.pdf', 'uploads/pdfcoffee.com_web-technology-7-pdf-free.pdf', '2025-03-06 12:09:47', 'Approved', 'aaryan.m299@ptuniv.edu.in'),
(3, 'Adesh', '2201112004', 'IT', '3rd Year', 'B.Tech', 'CC', 'a', 'duty', '2025-03-07', '2025-03-08', 'Sports', 2, 'uploads/Adesh_2201112004/download.jpeg', 'uploads/Adesh_2201112004/images (1).png', 'uploads/Adesh_2201112004/pdfcoffee.com_web-technology-7-pdf-free.pdf', 'uploads/Adesh_2201112004/Domain.pdf', '2025-03-06 16:59:44', 'Approved', 'mohanaryan21@gmail.com'),
(4, 'Sarvesh', '3456789456', 'IT', '2nd Year', 'B.Tech', 'Full Stack', 'a', 'duty', '2025-03-10', '2025-03-12', 'Sports', 2, 'uploads/Sarvesh_3456789456/images (1).png', 'uploads/Sarvesh_3456789456/download.jpeg', 'uploads/Sarvesh_3456789456/APPLICATION LAYER PROTOCOLS.pdf', 'uploads/Sarvesh_3456789456/Domain.pdf', '2025-03-07 13:44:02', 'Approved', 'sarweshsasi220@gmail.com'),
(5, 'Adesh', '2201112004', 'IT', '3rd Year', 'B.Tech', 'CC', 'a', 'duty', '2025-03-22', '2025-03-24', 'Arupadai tour', 2, 'uploads/Adesh_2201112004/images (1).png', 'uploads/Adesh_2201112004/download.jpeg', 'uploads/Adesh_2201112004/Domain.pdf', 'uploads/Adesh_2201112004/Domain.pdf', '2025-03-08 13:50:36', 'Approved', 'adesh.saminathan998@ptuniv.edu.in');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `registration_number` varchar(50) NOT NULL,
  `role` enum('student','dean','hod','vc') NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `registration_number`, `role`, `email`, `password`, `department`, `name`) VALUES
(1, '12345', 'student', 'mohanaryan21@gmail.com', 'aari', NULL, 'Gold'),
(2, '67890', 'dean', 'divyamohan554@gmail.com', 'aari', NULL, 'Dean'),
(3, '54321', 'hod', 'aaryan.m299@ptuniv.edu.in', 'aari', NULL, ''),
(4, '98765', 'vc', 'aaryan.m299@ptuniv.edu.in', 'aari', NULL, 'MR.MOHAN'),
(5, '221', 'hod', 'aaryan.m299@ptuniv.edu.in', 'aari', 'CSE', ''),
(6, 'hod_ece', 'hod', 'aaryan.m299@ptuniv.edu.in', 'aari', 'ECE', ''),
(7, 'hod_eee', 'hod', 'hod_eee@ptuniv.edu.in', 'hashed_password', 'EEE', ''),
(8, 'hod_mech', 'hod', 'hod_mech@ptuniv.edu.in', 'hashed_password', 'MECH', ''),
(9, 'hod_civil', 'hod', 'hod_civil@ptuniv.edu.in', 'hashed_password', 'CIVIL', ''),
(10, '220', 'hod', 'aaryan.m299@ptuniv.edu.in', 'aari', 'IT', 'IT HOD'),
(11, 'hod_chem', 'hod', 'hod_chem@ptuniv.edu.in', 'hashed_password', 'CHEM', ''),
(12, '2301110043', 'student', 'sarweshsasi220@gmail.com', 'sarvesh', 'CSE', ''),
(13, '2201112003', 'student', 'aaryan.m299@ptuniv.edu.in', 'aari', 'IT', 'AARYAN M'),
(14, '2201112004', 'student', 'adesh.saminathan998@ptuniv.edu.in', 'adesh', 'IT', 'Adesh');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `registration_number` (`registration_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
