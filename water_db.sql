-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2026 at 05:48 AM
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
-- Database: `water_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `agent_assignments`
--

CREATE TABLE `agent_assignments` (
  `id` int(11) NOT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `area` varchar(100) DEFAULT NULL,
  `total_households` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `agent_assignments`
--

INSERT INTO `agent_assignments` (`id`, `agent_id`, `area`, `total_households`, `created_at`) VALUES
(1, 3, 'Diwa', NULL, '2026-04-25 13:01:43'),
(2, 8, 'Alauli', NULL, '2026-04-26 03:22:14');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `target_type` enum('everyone','barangay') DEFAULT 'everyone',
  `posted_by` int(11) DEFAULT NULL,
  `announcement_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `barangay`, `target_type`, `posted_by`, `announcement_date`, `created_at`) VALUES
(1, 'hello', 'wassup', 'Ala-Uli', 'barangay', 1, '2026-04-25', '2026-04-25 15:46:40');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subject` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `status` enum('open','in_progress','resolved') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `replied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `user_id`, `subject`, `message`, `reply`, `status`, `created_at`, `replied_at`) VALUES
(1, 6, 'Others', 'kulay matcha yung tubig doi', 'awit sah may flavor, aucn k bukas sah', 'in_progress', '2026-04-25 15:49:38', '2026-04-25 23:53:16');

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `reading_id` int(11) DEFAULT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `consumption` int(11) DEFAULT NULL,
  `rate` decimal(10,2) DEFAULT 10.00,
  `amount` decimal(10,2) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `status` enum('unpaid','pending_verification','paid','overdue') DEFAULT 'unpaid',
  `issued_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `user_id`, `reading_id`, `invoice_no`, `consumption`, `rate`, `amount`, `due_date`, `status`, `issued_by`, `created_at`) VALUES
(1, 6, 1, 'INV-20260425-3121', 123, 10.00, 1230.00, '2026-05-10', 'paid', 1, '2026-04-25 15:47:36');

-- --------------------------------------------------------

--
-- Table structure for table `meter_readings`
--

CREATE TABLE `meter_readings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `agent_id` int(11) DEFAULT NULL,
  `previous_reading` int(11) DEFAULT 0,
  `current_reading` int(11) DEFAULT NULL,
  `consumption` int(11) DEFAULT NULL,
  `reading_date` date DEFAULT NULL,
  `status` enum('pending','completed') DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `meter_readings`
--

INSERT INTO `meter_readings` (`id`, `user_id`, `agent_id`, `previous_reading`, `current_reading`, `consumption`, `reading_date`, `status`, `created_at`) VALUES
(1, 6, 3, 0, 123, 123, '2026-04-25', 'completed', '2026-04-25 15:43:58'),
(2, 0, 3, 0, 125, 125, '2026-04-26', 'completed', '2026-04-26 02:48:06'),
(3, 0, 3, 125, 456, 331, '2026-04-26', 'completed', '2026-04-26 02:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_target` varchar(50) DEFAULT 'admin',
  `title` varchar(150) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `status` enum('unread','read') DEFAULT 'unread',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `type` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `role_target`, `title`, `message`, `status`, `created_at`, `type`) VALUES
(1, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:47:39', 'reading'),
(2, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:48:08', 'reading'),
(3, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:48:29', 'complaint'),
(4, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:49:00', 'complaint'),
(5, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:49:54', 'reading'),
(6, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:50:20', 'reading'),
(7, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:50:50', 'reading'),
(8, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:11', 'reading'),
(9, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:14', 'reading'),
(10, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:16', 'reading'),
(11, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:17', 'reading'),
(12, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:17', 'reading'),
(13, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:17', 'reading'),
(14, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:17', 'reading'),
(15, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:17', 'reading'),
(16, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:18', 'reading'),
(17, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:18', 'reading'),
(18, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:18', 'reading'),
(19, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:18', 'reading'),
(20, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 02:51:18', 'reading'),
(21, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:36', 'complaint'),
(22, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:42', 'complaint'),
(23, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:43', 'complaint'),
(24, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:44', 'complaint'),
(25, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:44', 'complaint'),
(26, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'unread', '2026-04-26 02:51:44', 'complaint'),
(27, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'read', '2026-04-26 02:51:46', 'complaint'),
(28, 6, 'admin', 'New Complaint', 'A new complaint has been submitted.', 'read', '2026-04-26 02:51:49', 'complaint'),
(29, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 03:16:56', 'reading'),
(30, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 03:22:40', 'reading'),
(31, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 03:22:57', 'reading'),
(32, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 03:23:17', 'reading'),
(33, 3, 'admin', 'Meter Reading Completed', 'Field agent completed assigned reading.', 'unread', '2026-04-26 03:23:21', 'reading');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `transaction_no` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `verified_by` int(11) DEFAULT NULL,
  `paid_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `invoice_id`, `user_id`, `transaction_no`, `amount`, `payment_method`, `reference_no`, `status`, `verified_by`, `paid_at`) VALUES
(1, 1, 6, 'TRX-20260425-3581', 1230.00, 'Bank Transfer', 'TRX-20260425-3581', 'verified', 2, '2026-04-25 15:49:14');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`id`, `payment_id`, `receipt_no`, `issued_at`) VALUES
(1, 1, 'REC-20260425-2218', '2026-04-25 15:50:27');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin','accountant','agent') DEFAULT 'user',
  `meter_number` varchar(50) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `street` varchar(100) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_code` varchar(50) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.png'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `contact_no`, `password`, `role`, `meter_number`, `barangay`, `street`, `status`, `created_at`, `user_code`, `avatar`) VALUES
(1, 'Admin', 'User', 'admin@gmail.com', '09111111111', '123', 'admin', NULL, NULL, NULL, 'active', '2026-04-25 13:01:43', NULL, 'default.png'),
(2, 'Accountant', 'User', 'accountant@gmail.com', '09222222222', '123', 'accountant', NULL, NULL, NULL, 'active', '2026-04-25 13:01:43', NULL, 'default.png'),
(3, 'Field', 'Agent', 'agent@gmail.com', '09333333333', '123', 'agent', NULL, 'Barangay 1', 'Area A', 'active', '2026-04-25 13:01:43', NULL, 'default.png'),
(6, 'Jewel', 'Baciles', 'jkmcbaciles24@bpsu.edu.ph', '03483022253', '159asd', 'user', '24-02397', 'Ala-Uli', 'ewan', 'active', '2026-04-25 14:59:51', NULL, 'default.png'),
(7, 'Kier', 'Baciles', 'bacilesjewel10@gmail.com', '09279554204', '753qwe', 'user', '24-03385', 'Diwa', 'wiwoo', 'active', '2026-04-25 23:30:48', 'USR-20260426-7233', 'default.png'),
(8, 'budoy', 'buday', 'agent2@gmail.com', '1235789510', '147', 'agent', '', '', '', 'active', '2026-04-26 03:21:52', NULL, 'default.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `agent_assignments`
--
ALTER TABLE `agent_assignments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meter_readings`
--
ALTER TABLE `meter_readings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `agent_assignments`
--
ALTER TABLE `agent_assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `meter_readings`
--
ALTER TABLE `meter_readings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
